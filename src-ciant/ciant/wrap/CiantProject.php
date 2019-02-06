<?php
namespace ciant\wrap;

use twikilib\utils\timespan\EmptyTimeSpan;

use twikilib\utils\timespan\TimeSpan;

use twikilib\utils\FeaturedImage;
use twikilib\nodes\TopicFormNode;
use twikilib\wrap\ITopicWrapper;
use twikilib\core\ITopic;
use twikilib\core\MetaSearch;

use twikilib\utils\Encoder;

use twikilib\form\IFormField;
use twikilib\form\fields\DateField;

use ciant\wrap\CiantUser;
use ciant\wrap\CiantEvent;
use ciant\wrap\CiantOrg;

use ciant\factory\ProjectFactory;
use ciant\factory\ParentProjectNotFoundException;
use twikilib\core\TopicNotFoundException;
use twikilib\wrap\UnknowTopicTypeException;

/**
 * Provides access to the projects stored within the TWiki database.
 * @author Viliam Simko
 */
class CiantProject implements ITopicWrapper {

	/**
	 * @var TopicFormNode
	 */
	public $topicFormNode;

	/**
	 * @var twikilib\core\ITopic
	 */
	private $wrappedTopic;

	/**
	 * (non-PHPdoc)
	 * @see twikilib\wrap.ITopicWrapper::getWrappedTopic()
	 */
	final public function getWrappedTopic() {
		return $this->wrappedTopic;
	}

	/**
	 * @param ITopic $topic
	 */
	public function __construct(ITopic $topic) {
		$this->wrappedTopic = $topic;
		$this->topicFormNode = $topic->getTopicFormNode();
	}

	/**
	 * @return twikilib\form\IFormField Name of the project
	 */
	final public function getName() {
		return $this->topicFormNode->getFormField('Name');
	}

	/**
	 * If the acronym is empty it will be automagically constructed from project name.
	 * @return twikilib\form\IFormField Acronym of the project
	 */
	final public function getAcronym() {
		$projectAcronym = trim($this->topicFormNode->getFormField('Acronym'));
		if(empty($projectAcronym)) {
			return str_replace(' ', '', $this->getName());
		}
		return $projectAcronym;
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getAbstract() {
		return $this->topicFormNode->getFormField('Abstract');
	}

	/**
	 * @return array of CiantEvent
	 */
	final public function getIncludedEvents() {
		$this->getWrappedTopic()->getConfig()->pushStrictMode(false);
		$includedEvents = $this->topicFormNode->getFormField('Included Events')->getFieldValue();
		$this->getWrappedTopic()->getConfig()->popStrictMode();

		$db = $this->getWrappedTopic()->getTopicFactory();

		$list = array();
		foreach(preg_split('/[:,]\s+/', $includedEvents) as $topicName) {
			try {
				if(empty($topicName))
					continue;
				$topic = $db->loadTopicByName($topicName);
				$eventTopic = CiantWrapFactory::getWrappedTopic($topic);
				assert($eventTopic instanceof CiantEvent);
				$list[] = $eventTopic;
			} catch (\Exception $e) {
				// ANY exception should be silently ignored because
				// if the "Included Events" field contains garbage
				// we want to ignore the field without bothering the user
			}
		}
		return $list;
	}

	/**
	 * @return twikilib\form\fields\DateField
	 */
	final public function getBeginDate() {
		return $this->topicFormNode->getFormField('Begin');
	}

	/**
	 * @return twikilib\form\fields\DateField
	 */
	final public function getEndDate() {
		return $this->topicFormNode->getFormField('End');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getHomepage() {
		return $this->topicFormNode->getFormField('Homepage');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getProjectNumber() {
		return $this->topicFormNode->getFormField('ProjectNr');
	}

	/**
	 * @return twikilib\form\IFormField
	 */
	final public function getFundingProgramme() {
		return $this->topicFormNode->getFormField('FundingProgramme');
	}

	/**
	 * Loaded on-demand.
	 * TODO: this should be later reimplemented using
	 *       a central topic-caching mechanism inside the ITopicFactory
	 * @var CiantUser
	 */
	private $ondemand_Manager;

	/**
	 * @return CiantUser
	 */
	final public function getManager() {
		if( !isset($this->ondemand_Manager) ) {
			$topicName = trim( $this->topicFormNode->getFormField('Manager') );
			$topic = $this->getWrappedTopic()->getTopicFactory()->loadTopicByName($topicName);
			$this->ondemand_Manager = new CiantUser($topic);
		}
		return $this->ondemand_Manager;
	}

	/**
	 * Loaded on-demand.
	 * TODO: this should be later reimplemented using a central topic-caching mechanism inside the ITopicFactory.php
	 * @var CiantOrg
	 */
	private $ondemand_Coordinator;

	/**
	 * @return CiantOrg
	 */
	final public function getCoordinator() {
		if( !isset($this->ondemand_Coordinator) ) {
			$topicName = trim( $this->topicFormNode->getFormField('Coordinator') );
			$topic = $this->getWrappedTopic()->getTopicFactory()->loadTopicByName($topicName);
			$this->ondemand_Coordinator = new CiantOrg($topic);
		}
		return $this->ondemand_Coordinator;
	}

	/**
	 * Loaded on-demand.
	 * TODO: this should be later reimplemented using a central topic-caching mechanism inside the ITopicFactory.php
	 * @var array
	 */
	private $ondemand_Coorganisers;

	/**
	 * @return array
	 *   <p>An array of CiantOrg elements or strings</p>
	 */
	final public function getCoorganisers()
	{
		if( !isset($this->ondemand_Coorganisers) ) {

			$coorganisersFieldText = $this->topicFormNode->getFormField('Coorganisers')->getFieldValue();
			$potentialTopicNames = Encoder::extractWikiNamesFromString($coorganisersFieldText);

			$topicFactory = $this->getWrappedTopic()->getTopicFactory();
			$this->ondemand_Coorganisers = array();

			foreach( $potentialTopicNames as $topicName ) {
				try {
					$topic = $topicFactory->loadTopicByName($topicName);
					$wrappedTopic = CiantWrapFactory::getWrappedTopic($topic);

					if($wrappedTopic instanceof CiantOrg) {
						$this->ondemand_Coorganisers[$topicName] = $wrappedTopic;
					}

				} catch (UnknowTopicTypeException $e) {}
				  catch (TopicNotFoundException   $e) {}
			}

			// now also use texts that are not topic names (delimited by comma)
			$arrayOfNames = explode(',', $coorganisersFieldText);

			foreach( $arrayOfNames as $pname )
			{
				$pname = trim($pname); // normalisation
				if( empty($pname) || isset($this->ondemand_Coorganisers[$pname]) )
					continue;

				$this->ondemand_Coorganisers[] = $pname;
			}
		}

		return $this->ondemand_Coorganisers;
	}

	/**
	 * Loaded on-demand.
	 * TODO: this should be later reimplemented using a central topic-caching mechanism inside the ITopicFactory.php
	 * @var array
	 */
	private $ondemand_AssociatedPartners;

	/**
	 * @return array of CiantOrg
	 */
	final public function getAssociatedPartners()
	{
		if( !isset($this->ondemand_AssociatedPartners) ) {
			$arrayOfNames = explode(',', $this->topicFormNode->getFormField('AssociatedPartners'));

			$topicFactory = $this->getWrappedTopic()->getTopicFactory();
			$this->ondemand_AssociatedPartners = array();
			foreach( $arrayOfNames as $pname )
			{
				$pname = trim($pname); // normalisation
				try {
					$topic = $topicFactory->loadTopicByName($pname);
					$this->ondemand_AssociatedPartners[] = new CiantOrg($topic);
				} catch (TopicNotFoundException $e) {
					$this->ondemand_AssociatedPartners[] = $pname;
				}
			}
		}

		return $this->ondemand_AssociatedPartners;
	}

	/**
	 * @return array of CiantEvent
	 */
	final public function getDirectChildEvents() {
		$search = new MetaSearch( $this->getWrappedTopic()->getConfig() );
		$search->setParentFilter( $this->getWrappedTopic()->getTopicName() );
		$search->setFormNameFilter('EventForm');
		$search->executeQuery();

		$topicFactory = $this->getWrappedTopic()->getTopicFactory();

		$results = array();
		foreach($search->getResults() as $topicName) {
			assert( ! isset($results[$topicName]) );
			$topic = $topicFactory->loadTopicByName($topicName);
			$wrappedEventTopic = new CiantEvent($topic);
			$listEvents[] = $wrappedEventTopic;
		}

		assert( is_array($results) );
		return $results;
	}

	/**
	 * List of all events (topics with EventForm attached) that are located in the parent-child hierarchy
	 * as a descendants of the given topic.
	 * Moreover, the topics listen in the field "Inluded Events" will also be included.
	 *
	 * THIS PROJECT -> Event -> Event ...
	 *
	 * @return array of CiantEvent
	 */
	final public function getAllChildEvents() {

		// generate a list containing all events (CPU/HDD intensive operation)
		$search = new MetaSearch( $this->getWrappedTopic()->getConfig() );
		$search->setFormNameFilter('EventForm');
		$search->executeQuery(); // now we have got TopicNames of all events

		$topicFactory = $this->getWrappedTopic()->getTopicFactory();
		$projectFactory = new ProjectFactory($topicFactory);

		$listEvents = array();
		foreach($search->getResults() as $topicName) {
			try {
				// transitively find the parent project
				$eventProject = $projectFactory->getParentProjectFromTopicName($topicName);
				assert($eventProject instanceof CiantProject);

				if($eventProject->getWrappedTopic() === $this->getWrappedTopic()) {
					// now we know that the event is a child of this project
					// (could be a direct child of another event which is a child of this project)
					$topic = $topicFactory->loadTopicByName($topicName);
					$wrappedEventTopic = new CiantEvent($topic);
					$listEvents[] = $wrappedEventTopic;
				}
			} catch(ParentProjectNotFoundException $e) {}
		}

		// also add event listed in the "Included Events" field
		foreach($this->getIncludedEvents() as $eventTopic) {
			$listEvents[] = $eventTopic;
		}

		// the list $listEvents now contains CiantEvent topics (objects)
		assert( is_array($listEvents) );
		return $listEvents;
	}

	const STATUS_PROPOSAL = 'Proposal';
	const STATUS_PROJECT = 'Project';
	const STATUS_REJECTED = 'Rejected Proposal';
	const STATUS_PAST = 'Past Project';

	/**
	 * @param string $projectStatus Will be used as a part of regular expression
	 */
	final public function hasStatus($projectStatus) {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$statusValue = $this->topicFormNode->getFormField('Status')->getFieldValue();
		$this->wrappedTopic->getConfig()->popStrictMode();

		return preg_match('/(^|,)('.$projectStatus.')/', $statusValue );
	}

	/**
	 * @return string
	 */
	final public function getStatus() {
		return $this->topicFormNode->getFormField('Status');
	}

	/**
	 * - TRUE if the project has started
	 * - FALSE if the project is before start or the project is without a start date
	 * @return boolean
	 */
	final public function isBeforeBegin() {
		$begin = $this->getBeginDate();
		if( $begin == null ) return false;

		assert($begin instanceof DateField);
		if( $begin->isEmpty() ) return false;

		return $begin->getISOFormat() > date('Y-m-d', time());
	}

	/**
     * @deprecated TODO - use getTimeSpan instead so I can delete this method
	 * @return boolean
	 */
	final public function overlapsWith($rangeBegin, $rangeEnd) {
		$begin = $this->getBeginDate();
		if( $begin == null ) return false;

		$end = $this->getEndDate();
		if( $end == null ) return false;

		assert($begin instanceof DateField);
		if( $begin->isEmpty() ) return false;

		assert($end instanceof DateField);
		if( $end->isEmpty() ) return false;

		if ( $end->getISOFormat() < $rangeBegin) return false;

		if ( $begin->getISOFormat() > $rangeEnd) return false;

		return true;
	}

	/**
	 * @return TimeSpan
	 */
	final public function getTimeSpan() {
		try {
			return new TimeSpan(
				$this->getBeginDate()->getFieldValue(),
				$this->getEndDate()->getFieldValue() );
		} catch(\Exception $e) {
			return new EmptyTimeSpan();
		}
		assert(/* should not reach this */);
	}

	/**
	 * - TRUE if the proposal is really after deadline
	 * - FALSE if the proposal is before deadline or the project is without a deadline
	 * @return boolean
	 */
	final public function isAfterDeadline() {
		$deadline = $this->getProposalDeadline();
		if( $deadline == null ) return false;

		assert($deadline instanceof DateField);
		if( $deadline->isEmpty() ) return false;

		return $deadline->getISOFormat() < date('Y-m-d', time());
	}

	/**
	 * - TRUE if the project has been already reported / should have been reported
	 * - FALSE if the current date is before the report deadline or the project is without a deadline
	 * @return boolean
	 */
	final public function isAfterReportDeadline() {
		$deadline = $this->getReportDeadline();
		if( $deadline == null ) return false;

		assert($deadline instanceof DateField);
		if( $deadline->isEmpty() ) return false;

		return $deadline->getISOFormat() < date('Y-m-d', time());
	}

	/**
	 * @return twikilib\form\fields\DateField or null
	 */
	final public function getProposalDeadline() {
		$deadline = $this->topicFormNode->getFormField('ProposalDeadline');
		assert($deadline == null || $deadline instanceof DateField);
		return $deadline;
	}

	/**
	 * @return twikilib\form\fields\DateField or null
	 */
	final public function getReportDeadline() {
		$deadline = $this->topicFormNode->getFormField('FinalReportDeadline');
		assert($deadline == null || $deadline instanceof DateField);
		return $deadline;
	}

	/**
	 * @see definition of the 'Importance' field in Main.ProjectForm and Main.ProposalForm
	 * @return string
	 */
	final public function getImportance() {
		return $this->topicFormNode->getFormField('Importance');
	}

	/**
	 * Uses the 'Option' form field.
	 * @return boolean
	 */
	final public function isAuditOption() {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$optionsFieldValue = (string) $this->topicFormNode->getFormField('Options');
		$this->wrappedTopic->getConfig()->popStrictMode();

		return (boolean) preg_match('/Audit/i', $optionsFieldValue);
	}

	/**
	 * Uses the 'Option' form field.
	 * @return boolean
	 */
	final public function isPublishedOnWebOption() {

		$this->wrappedTopic->getConfig()->pushStrictMode(false);
		$optionsFieldValue = (string) $this->topicFormNode->getFormField('Options');
		$this->wrappedTopic->getConfig()->popStrictMode();

		return (boolean) preg_match('/Published on Web/i', $optionsFieldValue);
	}

	// =========================================================================
	// Methods mapped to FeaturedImage class
	// =========================================================================

	/**
	 * @see twikilib\utils.FeaturedImage::getImageUrl()
	 * @return string
	 */
	final public function getLogoUrl() {
		$featuredImage = new FeaturedImage($this->getWrappedTopic(), '^\s*(logo|featured)\s*$');
		return $featuredImage->getImageUrl();
	}

	/**
	 * @see twikilib\utils.FeaturedImage::getThumbnailUrl()
	 * @return string
	 */
	final public function getThumbnailUrl($cropToFitWidth, $cropToFitHeight=0) {
		$featuredImage = new FeaturedImage($this->getWrappedTopic(), '^\s*(logo|featured)\s*$');
		return $featuredImage->getThumbnailUrl($cropToFitWidth, $cropToFitHeight);
	}
}