<?php
namespace ciant\apps;

use twikilib\core\ITopic;

use twikilib\runtime\Logger;
use twikilib\fields\TextSection;
use twikilib\core\MetaSearch;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

/**
 * @runnable
 *
 * Requested by Michal Masa 2011-06-06:
 * Collect "Deadline" seactions from all topic with FundingProgrammeForm.
 * Store all deadlines to GrantCalendarNew where:
 * - section heading = funding programme name
 * - section content = deadlines as input for CalendarPlugin
 *
 * See topic Main.GrantCalendarNew
 * @author Viliam Simko
 */

class GrantCal2 {

	public function cmp($a, $b)
	{
		if ($a[0] == $b[0]) {
			return 0;
		}
		return ($a[0] < $b[0]) ? -1 : 1;
	}

	public function run() {
		Logger::disableLogger();
		@header('Content-type: text/plain');

		$config = new Config('config.ini');
		$config->pushStrictMode(false);
		$db = new FilesystemDB($config);

		$search = new MetaSearch($config);
		$search->setFormNameFilter('FundingProgrammeForm');
		$search->executeQuery();

		$listOfPeriods = array();

		foreach ($search->getResults() as $topicName) {

			assert( is_string($topicName) ) ;
			$topic = $db->loadTopicByName($topicName);
			assert($topic instanceof ITopic);

			$dlSection = $topic->getTopicTextNode()->getSectionByName('Deadlines');

			if( $dlSection instanceof TextSection && !$dlSection->isEmpty() ) {

				$textToParse = $dlSection->toWikiString();

				$programmeName = $topic->getTopicFormNode()->getFormField('Name')->getFieldValue();

				foreach( explode("\n", $textToParse) as $line) {
					if(preg_match('/^   \* (?P<begin>[^-]+)\s*-\s*(?P<what>.*)/', $line, $match)) {
						$importance = "\xe2\x88\x97";
						if(preg_match('/.*\((?P<importance>(\xe2\x88\x97)+)\).*/', $match['what'], $match2)) {
							$importance = $match2['importance'];
						}
						if(preg_match('/.*\((?P<importance>(&lowast;)+)\).*/', $match['what'], $match2)) {
							$importance = $match2['importance'];
						}

						$listOfPeriods[] = array(
							strtotime($match['begin']),
							$match['begin'],
							$match['what'],
							$programmeName,
							$topicName,
							$importance
						);
					}
				}
			}
		}

echo '
<style>
.style_importance_5 {
	color:White;
	background-color:Red;
}
.style_importance_4 {
	background-color:Yellow;
}
.style_importance_3 {
}
.style_importance_2 {
}
.style_importance_1 {
}
</style>
';

		$now = getdate();
		$year = $now["year"];
		$month = $now["mon"];
		$monthNames = array ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

		foreach ($listOfPeriods as &$value) {
			if (strpos($value[2], "ONETIME") !== false) // ignore ONETIME deadlines
				continue;
			$date = getdate($value[0]);
			$newYear = $date["year"];
			if ($date["year"] < $year) // shift the year to actual year
				$newYear = $year;
			if ($date["year"] == $year && $date["mon"] < $month)
				$newYear =  $year + 1;
			if ($date["year"] != $newYear) { // shifted?
				$date["year"] = $newYear;
				$value[0] = mktime($date["hours"], $date["minutes"], $date["seconds"], $date["mon"], $date["mday"], $date["year"]);
				$value[2] .= " (originally " . $value[1] . ")";
				$value[1] = $date["mday"] . " " . $monthNames[$date["mon"]-1] . " " . $date["year"];
				if ($date["hours"] != 0 || $date["minutes"] != 0) {
					$fill = ($date["minutes"] < 10 ? "0" : "");
					$value[1] .= " " . $date["hours"] . ":" . $fill . $date["minutes"];
				}
			}
		}

		usort($listOfPeriods, array($this, "cmp"));
		for ($i = 0; $i < 12; $i ++) {
			echo "---+++ " . $monthNames[$month-1] . " " . $year . "\n";
			$dateFrom = "1-$month-$year";
			$month ++;
			if ($month > 12) {
				$month = 1;
				$year++;
			}
			$dateTo = "1-$month-$year";
			$dateFrom = strtotime($dateFrom);
			$dateTo = strtotime($dateTo);
			foreach ($listOfPeriods as $value) {
				if ($value[0] >= $dateFrom && $value[0] < $dateTo) {
					if ($value[5][0] == "&")
						$importance = strlen($value[5]) / 8;
					else
						$importance = strlen($value[5]) / 3;
					echo "   * <span class='style_importance_$importance'>$value[1] - [[$value[4]][$value[3]]]: $value[2]</span>\n";
				}
			}
		}
	}
}