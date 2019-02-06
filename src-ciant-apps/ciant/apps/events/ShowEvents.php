<?php
namespace ciant\apps\events;

use twikilib\core\ResultCache;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

use twikilib\runtime\Logger;

use ciant\factory\ExtractedEventsFactory;
use ciant\factory\ExtractedEventsFilter;

use \Exception;

/**
 * @runnable
 *
 * This application encapsulates multiple ways of rendering extracted events.
 * Rendering is delegated to separate classes implementing the IEventsRenderer interface.
 *
 * At the moment, this application is used in topics (may change in future):
 * - Inventory.DvForm
 * - Main.HelperEventsFromProjects
 * - Main.CiantMinutes
 *
 * @author Viliam Simko
 */
class ShowEvents {

	/**
	 * @var IEventsRenderer
	 */
	private $rendererInstance;

	/**
	 * @var ExtractedEventsFilter
	 */
	private $eventsFilter;

	/**
	 * @var twikilib\core\Config
	 */
	private $twikiConfig;

	/**
	 * @var twikilib\core\ITopicFactory
	 */
	private $topicFactory;

	/**
	 * We just handle service parameters here.
	 * @param array $params
	 * @throws Exception
	 */
	public function __construct($params) {
		Logger::disableLogger();

		$this->twikiConfig = new Config('config.ini');

		if( @$params['nocache'] ) // for debugging
			$this->twikiConfig->disableCaching();

		$this->topicFactory = new FilesystemDB($this->twikiConfig);
		$this->eventsFilter = new ExtractedEventsFilter();
		$eventsFactory = new ExtractedEventsFactory($this->twikiConfig, $this->topicFactory, $this->eventsFilter);

		// handling the 'format' parameter
		$outputFormat = strtolower(@$params['format']);

		switch($outputFormat) {
			case 'list':
				$this->rendererInstance = new RenderEventsAsFieldList($eventsFactory);
				break;
			case 'calentries':
				$this->rendererInstance = new RenderEventsAsCalendarEntries($eventsFactory);
				break;
			case 'csv':
				$this->rendererInstance = new RenderEventsAsCSV($eventsFactory);
				break;
			default:
				throw new Exception("Unknown 'format' parameter specified. Valid options are: list, calentires, csv");
		}

		assert($this->rendererInstance instanceof IEventsRenderer);

		// handling the 'status' parameter
		$this->eventsFilter->projectStatus = @$params['projectStatus'];
		if( preg_match('/^([A-Za-z]+,?)*$/', $this->eventsFilter->projectStatus)) {

			// regular expression e.g. "Project|Proposal"
			$this->eventsFilter->projectStatus =
				str_replace(',', '|', $this->eventsFilter->projectStatus);

		} else
			throw new Exception( "Unsupported value in 'projectStatus' parameter. Please specify a list of values delimited by comma e.g. projectStatus=Project,Proposal");

		// keeps default value if not set
		if( isset($params['limitPastDays']) )
			$this->eventsFilter->limitPastDays = (integer) $params['limitPastDays'];

		// keeps default value if not set
		if( isset($params['limitFutureDays']) )
			$this->eventsFilter->limitFutureDays = (integer) $params['limitFutureDays'];

		if( @$params['ignoreConfirmed'] )
			$this->eventsFilter->ignoreConfirmed = true;

		if( @$params['ignoreUnconfirmed'] )
			$this->eventsFilter->ignoreUnconfirmed = true;
	}

	/**
	 * Runs the selected callback and use caching mechanism to speed up the process.
	 * @return void
	 */
	public function run() {

		$cacheHandler = new ResultCache($this->twikiConfig, $this->topicFactory);

		$renderer = $this->rendererInstance;

		echo $cacheHandler->getCachedData(
			function() use ($renderer){
				assert($renderer instanceof IEventsRenderer);
				ob_start();
				$renderer->render();
				return ob_get_clean();
			},
			$this->eventsFilter->getCachingSignature(), // caching depends on the filter
			get_class($this->rendererInstance)          // and also on type of the renderer
		);
	}
}