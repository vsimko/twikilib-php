<?php
namespace ciant\factory;

use twikilib\wrap\UnknowTopicTypeException;

use ciant\search\CiantProjects;
use ciant\search\CiantEvents;

use ciant\wrap\CiantWrapFactory;
use ciant\wrap\CiantOrg;
use ciant\wrap\CiantProject;
use ciant\wrap\CiantEvent;
use ciant\wrap\ExtractedEvent;

use twikilib\runtime\Logger;

use twikilib\core\Config;
use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use \Exception;

class EventFilteredException extends \Exception {}

/**
 * @author Viliam Simko
 */
class ExtractedEventsFactory {

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ExtractedEventsFilter
	 */
	private $filter;

	/**
	 * @param ITopicFactory $topicFactory
	 */
	function __construct(Config $twikiConfig, ITopicFactory $topicFactory, ExtractedEventsFilter $eventsFilter) {
		$this->topicFactory = $topicFactory;
		$this->twikiConfig = $twikiConfig;
		$this->filter = $eventsFilter;
	}

	/**
	 * Events will be automatically ordered by projectAcronym and then by beginDate.
	 * @return array of \ciant\wrap\ExtractedEvent
	 */
	final public function getAllExtractedEvents() {
		$extractedEvents = array_merge(
			$this->getCalentriesFromProjects(),
			$this->getEventsFromProjects(),
			$this->getEventsIncludedInProjectForm()
		);

		// sort events by project and then by begin date
		usort($extractedEvents, function(ExtractedEvent $e1, ExtractedEvent $e2) {
			$cmp = strcmp($e1->projectAcronym, $e2->projectAcronym);
			return $cmp == 0 ? strtotime($e1->beginDate) < strtotime($e2->beginDate) : $cmp;
		});
		return $extractedEvents;
	}

	/**
	 * Extract information from topics with attached EventForm.
	 * @return array of \ciant\wrap\ExtractedEvent
	 */
	final public function getEventsFromProjects() {

		$projectFactory = new ProjectFactory($this->topicFactory);
		$lister = new CiantEvents($this->twikiConfig, $this->topicFactory);

		// extracted events are aggregated here
		$extractedEvents = array();

		foreach($lister->getAllEvents() as $event) {
			assert($event instanceof CiantEvent);

			try {
				$project = $projectFactory->getNearestProjectFromTopic($event->getWrappedTopic());
				assert($project instanceof CiantProject);

				if( ! $project->hasStatus( $this->filter->projectStatus ))
					continue;

				$projectAcronym = $project->getAcronym();
			} catch (Exception $e) {
				$projectAcronym = $event->getWrappedTopic()->getTopicInfoNode()->getParentName();
			}
			
			try {
				$extractedEvents[] = $this->mapCiantEventToExtEvent($event, $projectAcronym);
			} catch(EventFilteredException $e) {
				continue;
			}
		}

		return $extractedEvents;
	}
	
	/**
	 * @param CiantEvent $event
	 * @throws UnknowTopicTypeException
	 * @return ExtractedEvent
	 */
	private function mapCiantEventToExtEvent(CiantEvent $event, $projectAcronym) {
		
		assert( !empty($projectAcronym) );

		$myevent = new ExtractedEvent;
		
		$myevent->visibleInCalendar = $event->isVisibleInCalendar();
		$myevent->beginDate = $event->getBeginDate()->getFieldValue();
		
		// use date interval filtering if specified by the user
		if( ! $myevent->isBeginsWithinDateInterval (
				$this->filter->getLowerBoundDate(),
				$this->filter->getUpperBoundDate() ) )
			throw new EventFilteredException("The event is filtered due to the specified date interval");
		
		$myevent->endDate = $event->getEndDate()->getFieldValue();
		$myevent->title = $event->getTitle();
		$myevent->description = $event->getAbstract();
		$myevent->info = 'event';
		
		// resolve the event venue
		try {
			$venueTopic = $event->getVenue();
		
			if(! $venueTopic instanceof ITopic)
				throw new UnknowTopicTypeException();
		
			$wrap = CiantWrapFactory::getWrappedTopic($venueTopic);
		
			if(! $wrap instanceof CiantOrg)
				throw new UnknowTopicTypeException();
		
			assert($wrap instanceof CiantOrg);
			$myevent->venueName = $wrap->getQualifiedName();
			$myevent->venueCity = $wrap->getCity();
			$myevent->venueCountry = $wrap->getCountry();
		
		} catch(UnknowTopicTypeException $e) {
			$myevent->venueName = $event->getVenueAsText();
		}
		
		$myevent->confirmed = $event->isConfirmed();
		
		// use confirmed/unconfirmed filtering
		if($this->filter->ignoreConfirmed && $myevent->confirmed)
			throw new EventFilteredException("The event is filtered because of the request to ignore confirmed events");
		
		if($this->filter->ignoreUnconfirmed && ! $myevent->confirmed)
			throw new EventFilteredException("The event is filtered because of the request to ignore unconfirmed events");;
		
		$myevent->projectAcronym = $projectAcronym;
		$myevent->relatedTopic = $event->getWrappedTopic()->getTopicName();
		
		return $myevent;
	}

	/**
	 * Extracts calendar entires located within the topic text.
	 * A calendar entry is a text in the following format:
	 *
	 *    * DD MMM YYYY [TIOPICNAME] DESCRIPTION @ VENUE
	 *
	 * where:
	 *   DD = day
	 *   MMM = month (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)
	 *   YYYY = year
	 *   TYPE = usually a single word or a topic name
	 *   DESRIPTION = some text
	 *   VENUE = some text providing information about the venue
	 *
	 * @return array of \ciant\wrap\ExtractedEvent
	 */
	final public function getCalentriesFromProjects() {

		// the following loops will aggregate extracted events into this array
		$extractedEvents = array();

		// get calendar entries as events
		$lister = new CiantProjects($this->twikiConfig, $this->topicFactory);
		foreach( $lister->getAllProjects() as $projectTopic) {
			assert($projectTopic instanceof CiantProject);

			// skip some projects if the status filter is defined
			if( ! $projectTopic->hasStatus($this->filter->projectStatus) )
				continue;

			$topicName = $projectTopic->getWrappedTopic()->getTopicName();
			assert( is_string($topicName) );
			assert( !empty($topicName) );

			$topicText = $projectTopic->getWrappedTopic()->getTopicTextNode()->toWikiString();

			// pre-filtering
			$matches = array();
			if(preg_match_all('/\n((   )*(   \* (?P<calentry>[a-zA-Z0-9\: \-]+-[^\n]*)))/', $topicText, $matches)) {
				foreach ($matches['calentry'] as $row) {
					if(preg_match('/^(?P<hide>[a-zA-Z \-]*)(?P<begin>[0-3]?[0-9] [A-Za-z]+ [0-9]{4})( (?P<tbegin>[0-9]{2}:[0-9]{2}))? -(( (?P<end>[1-3]?[0-9] [A-Za-z]+ [0-9]{4}))?( (?P<tend>[0-9]{2}:[0-9]{2}))? - )?(\[(?P<related>[a-zA-Z0-9_]+)\] *)?(?P<descr>.*)$/', $row, $m)) {

						$myevent = new ExtractedEvent();
						$myevent->visibleInCalendar = (trim($m['hide']) == '');

						$myevent->beginDate = $m['begin'];
						$myevent->endDate = $m['end'];

						// situation whern the end date is not specified
						if(empty($myevent->endDate))
							$myevent->endDate = $myevent->beginDate;

						if(!empty($m['tbegin']))
							$myevent->beginDate .= ' '.$m['tbegin'];

						if(!empty($m['tend']))
							$myevent->endDate .= ' '.$m['tend'];

						$myevent->description = $m['descr'];

						if(!empty($m['related']))
								$myevent->description = '['.$m['related'].'] '.$myevent->description;

						$myevent->title = $myevent->description;
						$myevent->info = 'calevent';
						$myevent->projectAcronym = $projectTopic->getAcronym();

						// calentries use project's topic name
						$myevent->relatedTopic = $projectTopic->getWrappedTopic()->getTopicName();

						if(preg_match('/@ *(?P<venue>.*)/', $myevent->description, $m2)) {
							$myevent->venueName = $m2['venue'];
						}

						$myevent->confirmed = ! preg_match('/UNCONFIRMED/', $myevent->description);

						// use date interval filtering if specified by the user
						if( ! $myevent->isBeginsWithinDateInterval (
								$this->filter->getLowerBoundDate(),
								$this->filter->getUpperBoundDate() ) ) {

							Logger::log("Event filtered due to the date limit: ".
								"lowerBound=".$this->filter->getLowerBoundDate().", ".
								"upperBound=".$this->filter->getUpperBoundDate().", ".
								"eventDate=".$myevent->beginDate );
							continue;
						}

						if($this->filter->ignoreConfirmed && $myevent->confirmed)
							continue;

						if($this->filter->ignoreUnconfirmed && ! $myevent->confirmed)
							continue;

						// we can now add the event to the list
						$extractedEvents[] = $myevent;

					} else {
						Logger::logWarning("UNKNOWN: $row");
					}
				}
			}
		}

		return $extractedEvents;
	}
	
	/**
	 * @return array of \ciant\wrap\ExtractedEvent
	 */
	final public function getEventsIncludedInProjectForm() {
		
		// the following loops will aggregate extracted events into this array
		$extractedEvents = array();
		
		// get calendar entries as events
		$lister = new CiantProjects($this->twikiConfig, $this->topicFactory);
		
		foreach( $lister->getAllProjects() as $projectTopic) {
			assert($projectTopic instanceof CiantProject);
		
			// skip some projects if the status filter is defined
			if( ! $projectTopic->hasStatus($this->filter->projectStatus) )
				continue;
			
			$projectAcronym = $projectTopic->getAcronym();
			
			foreach($projectTopic->getIncludedEvents() as $event) {
				assert($event instanceof CiantEvent);
				
				try {
					$extractedEvents[] = $this->mapCiantEventToExtEvent($event, $projectAcronym);
				} catch (EventFilteredException $e) {
					continue;
				}
			}
		}
		
		return $extractedEvents;
	}
}