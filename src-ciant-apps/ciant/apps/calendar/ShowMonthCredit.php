<?php
namespace ciant\apps\calendar;

use twikilib\runtime\Logger;

use twikilib\fields\TextSection;
use twikilib\runtime\Container;

use twikilib\core\ITopic;
use twikilib\core\ITopicFactory;
use twikilib\core\FilesystemDB;
use twikilib\core\Config;

use \Exception;
use \DateTime;
use \DateInterval;

/**
 * @runnable
 * Computes the month credit for users based on calendar entires in their user profiles.
 * see topic Main.StaffCalendar
 *
 * @author Viliam Simko
 */
class ShowMonthCredit {

	/**
	 * @var Config
	 */
	private $twikiConfig;

	/**
	 * @var ITopicFactory
	 */
	private $topicFactory;

	/**
	 * @var array of string
	 */
	private $usernames;
	private $staffCalSectionName = 'My Staff Calendar';
	private $staffCalYear;
	private $staffCalMonth;

	/**
	 * @var array of string
	 */
	private $extraicons = array(':cut:', ':credit:');

	/**
	 * @var array
	 */
	private $monthPeriods;

	final public function __construct($params) {

		// default period is the current month
		$this->staffCalYear = (integer) date('Y', time());
		$this->staffCalMonth = (integer) date('m', time());

		if(empty($params['users']) || @$params['help'] ) {
			throw new Exception(
				"Parameters: \n".
				"  --help                       (optional)\n".
				"  users='User1,...'            (mandatory)\n".
				"  section='SectionName'        (default: {$this->staffCalSectionName})\n".
				"  year='YYYY'                  (default: {$this->staffCalYear})\n".
				"  month='1..12'                (default: {$this->staffCalMonth})\n".
				"  extraicons='icon1,icon2,...  (default: ".implode(',', $this->extraicons).")\n"
			);
		}

		$this->usernames = preg_split('/,\s*/', $params['users']);

		// year
		if( ! empty($params['year'])) {
			$this->staffCalYear = (integer) $params['year'];
		}

		if($this->staffCalYear < 2000 )
			throw new Exception("Invalid year");

		// month
		if( ! empty($params['month'])) {
			$this->staffCalMonth = (integer) $params['month'];
		}

		if($this->staffCalMonth > 12 || $this->staffCalMonth < 1 )
			throw new Exception("Invalid month");

		// icons shown in the extra column
		if( ! empty($params['extraicons'])) {
			$this->extraicons = preg_split('/[,;|\s]+/', $params['extraicons']);
		}

		$this->twikiConfig = new Config('config.ini');
		$this->topicFactory = new FilesystemDB($this->twikiConfig);

		if(empty($params['topic']))
			throw new Exception("The topic parameter is not defined.");

		$specialPeriodsTopic = $this->topicFactory->loadTopicByName(@$params['topic']);
		assert($specialPeriodsTopic instanceof ITopic);

		$specialPeriods = ShowMonthCredit::getSpecialPeriods($specialPeriodsTopic);
		$this->monthPeriods = ShowMonthCredit::fillMonthSpecialPeriods($this->staffCalYear, $this->staffCalMonth, $specialPeriods);
	}

	/**
	 * Loads definition of special periods from a section.
	 * These are entries that will be rendered in the calendar using a different color.
	 *
	 * REQUIREMENT by Michal Masa 2012-03-13:
	 * V topicu StaffCalendar je sekce „Special Periods“ (z administrativnich duvodu
	 * se nemuze jmenovat Holidays ;), ve které jsou uvedena všechna prázdninová obdobi.
	 * Idealni by bylo je v kalendari nejak zvýraznit – napr. ruzovou bravou pozadi :)
	 *
	 * @param ITopic $topic
	 */
	static private function getSpecialPeriods(ITopic $topic) {
		Logger::log( 'LOADING SPECIAL PERIODS FROM TOPIC : '.$topic->getTopicName() );

		$sectionName = "Special Periods";

		$section = $topic->getTopicTextNode()->getSectionByName($sectionName);
		if( ! $section instanceof TextSection)
			throw new Exception("Could not find section '$sectionName' in topic '{$topic->getTopicName()}'");

		$textToParse = $section->toWikiString();

		$listOfPeriods = array();

		foreach( explode("\n", $textToParse) as $line) {
			if(preg_match('/^   \* (?P<begin>[^-]+)\s*(-\s*(?P<end>[0-9][^-]+)\s*)?-\s*(?P<what>.*)/', $line, $match)) {
				//DEBUG: echo '   * '.$match['begin'].' .. '.$match['end'].':'.$match['what']."\n";

				// end-date is optional
				if(empty($match['end'])) {
					$match['end'] = $match['begin'];
				}

				$listOfPeriods[] = array(
					strtotime($match['begin']),
					strtotime($match['end']),
					$match['what']
				);
			}
		}

		assert( is_array($listOfPeriods) );
		return $listOfPeriods;
	}

	/**
	 * @return array
	 */
	static private function fillMonthSpecialPeriods($year, $month, array $listOfPeriods) {
		assert($year > 2000);
		assert($month >= 1 && $month <= 12 );

		$time = new DateTime("$year-$month-01");
		$daysInMonth = (integer) $time->format('t');

		$monthData = array_fill(0, $daysInMonth, "");

		$monthBegin = $time->getTimestamp();
		$time->add(new DateInterval("P1M")); // end of the month
		$monthEnd = $time->getTimestamp()-1;

		foreach($listOfPeriods as $periodData) {
			list($periodBegin, $periodEnd, $periodText) = $periodData;

			$overlapBegin = max($monthBegin, $periodBegin);
			$overlapEnd = min($monthEnd, $periodEnd);

			for($i = $overlapBegin; $i <= $overlapEnd; $i += 86400) {
				$dayNum = (integer) date('d', $i);
				$monthData[$dayNum - 1] = $periodText;
			}
		}

		assert( is_array($monthData));
		assert( count($monthData) >= 28 );
		return $monthData;
	}

	public function run() {

		//echo "Computing credit for month={$this->staffCalMonth}, year={$this->staffCalYear}\n";

		$numDaysInThisMonth = date('t', strtotime("{$this->staffCalYear}-{$this->staffCalMonth}-01"));
		//echo "* Number of days: $numDaysInThisMonth\n";

		echo Container::getTemplate( 'ciant/apps/calendar/tpl/year-month-selector.tpl.php',
			'YEAR', $this->staffCalYear,
			'MONTH', $this->staffCalMonth
		);

		// print table header
		echo "<table class='staffCalendarTable'><tr class='header'><th class='username'>Name</th>";
		for($i=1; $i <= $numDaysInThisMonth; ++$i) {
			$date = new DateTime();
			$date->setDate($this->staffCalYear, $this->staffCalMonth, $i);
			$dayName = $date->format('D');
			$dayName2 = substr($dayName, 0, 2);
			echo "<th class='$dayName'>$i<br/>$dayName2</th>";
		}
		echo "<th class='extra'>Extra</th>\n";
		echo "<th class='monthCredit'>Credit<br/>(month)</th>\n";
		echo "<th class='yearCredit'>Credit<br/>(year)</th>\n";
		echo "</tr>\n";


		// print table rows
		foreach($this->usernames as $username) {
			// days will be stored here
			$monthData = array_fill(0, $numDaysInThisMonth, "");
			$monthTooltip = array_fill(0, $numDaysInThisMonth, "");

			$topic = $this->topicFactory->loadTopicByName($username);
			assert($topic instanceof ITopic);

			$topicName = $topic->getTopicName();
			$formName = $topic->getTopicFormNode()->getFormName();

			if($formName != 'UserForm')
				throw new Exception("Not a user profile: ".$topic->getTopicName() );

			$section = $topic->getTopicTextNode()->getSectionByName( $this->staffCalSectionName );
			if($section == null) {
				//echo "   * Missing section *{$this->staffCalSectionName}* in user's profile: $topicName\n";
				echo "<tr>";
				echo "<th class='username'> $topicName </th>";
				echo "<td colspan='$numDaysInThisMonth'>Missing section *{$this->staffCalSectionName}* in user's profile</td>";
				echo "<td colspan='2'>?</td>";
				echo "</tr>\n";
				continue;
			}

			$textToParse = $section->toWikiString();

			// errors will be collected here
			$errors = array();

			// REQUIREMENT by Michal Masa:
			// Extra sloupecek pro  (1. leden) a  (konec mesice) (jsou to dulezite informace,
			// ktere nutne potrebuji vlastni sloupecek, aby nezapadly ve zmeti smajliku)
			$extraColumn = array();
			$extraColumnTooltip = array();

			// used for filtering entires outside this interval
			$thisDate = new DateTime("{$this->staffCalYear}-{$this->staffCalMonth}-01");
			$periodBegin = $thisDate->getTimestamp();

			$thisDate->add( new DateInterval("P1M") ); // end of the month
			$periodEnd = $thisDate->getTimestamp();
//			echo "<b>END: {$thisDate->format('Y-m-d H:i:s')}</b>";

			// used for filtering entires (all in the current year)
			$yearPeriodBegin = strtotime("{$this->staffCalYear}-01-01");
			$yearPeriodEnd = strtotime( ($this->staffCalYear+1)."-01-01") - 1;

			// the credit for the given user will be computed here
			$computedCredit = 0;
			$computedYearCredit = 0;

			foreach( explode("\n", $textToParse) as $line) {
				if(preg_match('/^   \* (?P<begin>[^-]+)( -\s*(?P<end>[0-9][^-]+))? - (?P<who>[^-]+) - (?P<what>.+) - (?P<icon>.*)/', $line, $match)) {
					//echo "MATCHED: $line\n";
					// users must always specify staff calendar entries with their username
					$who = $this->twikiConfig->normalizeTopicName($match['who']);
					if($who != $topicName) {
						$errors[] = "Using different user name as a staff calendar entry: topic={$topicName}, entry={$who}";
					}

					// now use unix timestamp to filter entires by datetime
					$beginTime = strtotime($match['begin']);

					if(empty($match['end'])) {
						$endTime = $beginTime + 3600*23 - 1; // one day (fixed the previous wrong value 86400)
					} else {
						$endTime = strtotime($match['end']) + 3600*23 - 1; //one day (fixed the previous wrong value 86400)
					}

					// credit amount encoded within the given calendar entry
					//echo "\n CREDIT: $match[what]\n";
					if(preg_match('/\(\s*(?P<sign>[+-]?)((?P<days>[0-9]+(\.[0-9]+)?)d)?((?P<hours>[0-9]+(\.[0-9]+)?)h\s*)?\)/', $match['what'], $m2) ) {
						$creditWithinEntry = (@$m2['days']*8 + @$m2['hours']) * (@$m2['sign'] == '-' ? -1 : 1);
					} else {
						$creditWithinEntry = 0;
					}

					//echo "MATCHED: $creditWithinEntry $line\n";

					// TODO: isWithinInterval
					if($beginTime >= $yearPeriodBegin && $endTime <= $yearPeriodEnd) {
						$computedYearCredit += $creditWithinEntry;
					}

					// the filtering happens here

					// REQUIREMENT by Michal Masa 2012-03-10:
					// casovy usek přes hranici mesice (napr.: 20 Mar 2012 – 5 Apr 2012)
					// se v kalendari neukaze. Nicmene kredit se zapocitava do year credit.
					// Navrhuji kredit zapocitavat i do month credit, nejlepe do toho mesice,
					// ve kterem usek konci, jelikoz nejsme schopni ten udaj v zavorce
					// smysluplne rozdelit do dvou mesicu. Casovy usek pres hranici roku
					// se budeme snazit eliminovat


					// TODO: isEndingInInterval
					if(	$endTime >= $periodBegin && $endTime <= $periodEnd ) {
						$computedCredit += $creditWithinEntry;
						$computedCreditNote = '';
					} else {
						$computedCreditNote = " (credit moved to the next month)";
					}

					// TODO: isOverlappingInterval
					if($beginTime < $periodEnd && $endTime > $periodBegin) {

						// fill days with icons
						for($i=$beginTime; $i < $endTime; $i += 86400) {
							if($i >= $periodBegin && $i < $periodEnd ) { // date interval intersection
								if( in_array($match['icon'], $this->extraicons) ) {
									$extraColumn[] = $match['icon'];
									$extraColumnTooltip[] = " $match[icon] $match[what] ";
								} else {
									$day = date('d', $i) - 1;

									// REQUIREMENT by Michal Masa 2012-04-20
									// an entry with less than 8 hours is considered to be a part-time work
									// it should be automatically rendered as a half-size icon.
									$icon = abs($creditWithinEntry) < 8 && $creditWithinEntry != 0
										? "<span class='part'> $match[icon] </span>"
										: " $match[icon] ";

									$monthData[$day][] = $icon;
									$monthTooltip[$day][] = $icon.$match['what'].$computedCreditNote;
								}
							}
						}
					} else {
//						echo "<br/>DEBUG: $beginTime < $periodBegin | $endTime > $periodEnd\n";
					}

				} elseif(preg_match('/^   \*/', $line))
					$errors[] = "Unmatched list entry: topic={$topicName}, entry={$line}";
			}

			echo "<tr>";
				echo "<th class='username'> $topicName </th>";
				foreach($monthData as $i=>$dayData) {
					$day = $i+1;
					$dayName = date("D", strtotime("{$this->staffCalYear}-{$this->staffCalMonth}-{$day}"));

					if( ! empty($this->monthPeriods[$i]) ) {
						$monthTooltip[$i][] = $this->monthPeriods[$i];
						$specialPeriodCssClass = 'specialperiod';
					} else {
						$specialPeriodCssClass = '';
					}

					$tooltip = '';
					if(is_array($monthTooltip[$i]))
						$tooltip = "<span class='tooltip'> ".implode(' <br/> ', $monthTooltip[$i])." </span>";

// 					if(is_array($dayData))
// 						$dayData = implode(' ', $dayData);

					$dayhtml = '';
					if(is_array($dayData)) {
						$dayhtml = '<ul class="dayicons">';
						foreach ($dayData as $dd) {
							$dayhtml .= "<li> $dd </li>";
						}
						$dayhtml .= '</ul>';
					}

					echo "<td class='$dayName $specialPeriodCssClass'> $tooltip $dayhtml </td>";
				}

				// extra column tooltip
				$tooltip = empty($extraColumnTooltip)
					? ''
					: "<span class='tooltip'> ".implode(' <br/> ', $extraColumnTooltip)." </span>";

				echo "<td>$tooltip<ul class='dayicons'>";
				foreach ($extraColumn as $columnData) {
					echo "<li> $columnData </li>";
				}
				echo "</ul></td>";

				// month creadit
				$cssClass = $this->getCreditCss($computedCredit);
				$creditStr = $this->getCreditString($computedCredit);
				echo "<th class='monthCredit $cssClass'>$creditStr</th>";


				// total creadit (from January until this month)
				$cssClass = $this->getCreditCss($computedYearCredit);
				$creditStr = $this->getCreditString($computedYearCredit);
				echo "<th class='yearCredit $cssClass'>$creditStr</th>";

			echo "</tr>\n";

			// render errors
			if( ! empty($errors) ) {
				echo "<tr>";
				echo "<td class='error' colspan='".($numDaysInThisMonth + 3)."'>\n";
				foreach($errors as $errText) {
					echo "   * $topicName ERROR: $errText<br/>\n";
				}
				echo "</td>\n";
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	}

	/**
	 * @param double $creditValue
	 * @return string
	 */
	private function getCreditString($creditValue) {
		if($creditValue == 0)
			return '--';

		$creditSign = $creditValue < 0 ? '-' : '';
		$creditDays  = (integer) abs($creditValue / 8);
		$creditHours = abs($creditValue) - ($creditDays*8);

		// now creating the output string
		assert($creditValue != 0);
		$output = $creditSign; // always include the minus sign

		if($creditDays > 0)
			$output .= $creditDays.'d';

		if($creditHours > 0)
			$output .= $creditHours.'h';

		return $output;
	}

	/**
	 * @param double $creditValue
	 * @return string
	 */
	private function getCreditCss($creditValue) {
		if($creditValue < 0)
			return 'negative';

		if($creditValue > 0)
			return 'positive';

		assert($creditValue == 0);
		return 'zero';
	}

}