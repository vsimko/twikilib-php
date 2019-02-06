<?php
namespace ciant\factory;

/**
 * @author Viliam Simko
 */
class ExtractedEventsFilter {

	/**
	 * List of values that will be used for filtering projects using the "Status" form field.
	 * Empty list = all projects
	 * @var string e.g. "Past Project"
	 */
	public $projectStatus = "";

	/**
	 * Events whose begin date is farther that N days in future will be ignored.
	 * @var integer Value -1 disables this filter
	 */
	public $limitFutureDays = -1;

	/**
	 * Events whose begin date is older that N days will be ignored.
	 * @var integer Value -1 disables this filter
	 */
	public $limitPastDays = -1;

	public $ignoreConfirmed = false;
	public $ignoreUnconfirmed = false;

	/**
	 * Computes the upper bound date in ISO format suitable for comparison.
	 * (ISO format is YYYY-MM-DD)
	 * @return string Empty string if limit < 0
	 */
	final public function getUpperBoundDate() {
		return $this->limitFutureDays < 0
			? ''
			: date('Y-m-d', time() + $this->limitFutureDays * 86400);
	}

	/**
	 * Computes the lower bound date in ISO format suitable for comparison.
	 * (ISO format is YYYY-MM-DD)
	 * @return string Empty string if limit < 0
	 */
	final public function getLowerBoundDate() {
		return $this->limitPastDays < 0
			? ''
			: date('Y-m-d', time() - $this->limitPastDays * 86400);
	}

	/**
	 * This value helps caching mechanism decide whether to load from cache or compute a new value
	 * which depends on this filter settings.
	 * @return string
	 */
	final public function getCachingSignature() {
		return print_r($this,true);
	}

}