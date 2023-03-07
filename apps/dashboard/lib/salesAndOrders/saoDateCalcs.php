<?php

/**
 * Contains calculations for dates and fiscal periods
 * 
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 27/08/10
 */
class saoDateCalcs extends page
{
	public $date;
	
	public $fromDate;
	public $fromDateDay;
	public $fromDateMonth;
	public $fromDateYear;
	
	public $toDate;
	public $toDateDay;
	public $toDateMonth;
	public $toDateYear;
	
	public $year;
	public $month;	
	
	public $startDate;
	public $startDateAsDate;
	
	public $endDate;
	public $endDateAsDate;
	
	public $weekCount;
	
	public $fiscalPeriod;
	public $fiscalWeeks;
	public $fiscalWeekDays;
	
	public $todaysDate;
	public $todaysDay;
	public $todaysMonth;
	public $todaysYear;
	
	public $fiscalMonth;
	public $fiscalYear;
	
	public $queryString;
	
	
	function __construct()
	{	
		// Ensure the dates used for calculations are set
		if (!isset($this->date))
		{
			$this->setDates();
		}
	}
	
	
	public function setDates()
	{
		//var_dump("test");
		
		$this->queryString = "";
		
		$this->todaysDate = date("Y-m-d");
		
		$this->todaysDay = date("d");
		$this->todaysMonth = date("m");
		$this->todaysYear = date("Y");

		/** Hard-code date values here for debugging **/	
//		$this->todaysDate = "2011-01-03";
//		$this->todaysDay = "03";
//		$this->todaysMonth = "01";
//		$this->todaysYear = "2011";
		
		// Set the year
		if (isset($_POST['year']))
		{
			$this->year = $_POST['year'];
			$this->queryString .= "&amp;year=" . $this->year;
		}
		elseif (isset($_GET['year']))
		{
			$this->year = $_GET['year'];
			$this->queryString .= "&amp;year=" . $this->year;
		}
		else 
		{
			$this->year = $this->todaysYear;
		}
		
		$this->fiscalYear = $this->presentFiscalYear();
		
		// Set the month
		if (isset($_POST['month']))
		{
			$this->month = $_POST['month'];
			$this->queryString .= "&amp;month=" . $this->month;
			$this->fiscalMonth = $_POST['month'];
		}
		elseif (isset($_GET['month']))
		{
			$this->month = $_GET['month'];
			$this->queryString .= "&amp;month=" . $this->month;
			$this->fiscalMonth = $_GET['month'];
		}
		else 
		{
			$this->month = $this->todaysMonth;
			$this->fiscalMonth = $this->presentFiscalMonthAsMonth("m");
		}
		
		// Set the date up to which data will be calculated and displayed
		if (date("Y-m-d", mktime(0,0,0,$this->month,$this->todaysDay,$this->year)) == $this->todaysDate)
		{
			// set date to today			
			$this->date = $this->todaysDate;
		}
		else 
		{
			// set date to the end of the selected fiscal period
			$this->date = $this->endOfFiscalPeriod(date($this->year . "-" . $this->month . "-" . "15"));
		}
				
		// Calculate the date up to which data will be calculated for
		if (($this->year == $this->todaysYear) && ($this->fiscalMonth == $this->presentFiscalMonthAsMonth("m")))
		{			
			// if date is first working day of the fiscal month, make endDate the last day of the previous fiscal month
			if (!$this->isFirstWorkingDay($this->date))
			{
				$this->endDate = $this->year . "-" . $this->todaysMonth . "-" . $this->todaysDay;
			}
			else 
			{
				$date = $this->year . "-" . ($this->fiscalMonth - 1) . "-" . "15";
				
				$date = $this->endOfFiscalPeriod($date);
				
				$this->endDate = $date;				
			}
		}
		else 
		{
			$date = $this->year . "-" . $this->month . "-" . "15";
			$date = $this->endOfFiscalPeriod($date);
			
			$this->endDate = $date;
		}
		
		// Calculate the date from which data will start to be calculated for
		$this->startDate = $this->startOfFiscalPeriod($this->endDate);

		// Determine the current fiscal period (e.g./ 22010)		
		$this->fiscalPeriod = $this->dateToFiscalPeriod($this->endDate);
						


		// Store end date in date format
		$dateArr = explode("-", $this->endDate);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$this->endDateAsDate = mktime(0,0,0,$month,$day,$year);
		
		// Store start date in date format
		$dateArr = explode("-", $this->startDate);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$this->startDateAsDate = mktime(0,0,0,$month,$day,$year);
		
		// Gives a list of all start and end dates for all weeks in that fiscal period - from saoDateCalcs class
		$this->fiscalWeeks = $this->datesToFiscalWeeks($this->startDate, $this->endDate);

		// Show days for the current week if showing the current fiscal period.  Otherwise just show weeks.
		if ($this->endDate >= $this->todaysDate)	
		{
			$this->fiscalWeekDays = $this->calcFiscalWeekDays($this->fiscalWeeks[(count($this->fiscalWeeks) - 1)], $this->endDate, $this->month, $this->year);
			$this->weekCount = count($this->fiscalWeeks) - 1;
		}
		else 
		{
			$this->weekCount = count($this->fiscalWeeks);
		}
		
		// DEBUG
//		var_dump(
//			"Month: " . $this->month . "<br />" .
//			"Todays Month: " . $this->todaysMonth . "<br />" . 
//			"Year: " . $this->year . "<br />" .
//			"Fiscal Period: " . $this->fiscalPeriod . "<br />" .
//			"Start Date: " . $this->startDate . "<br />" .
//			"End Date: " . $this->endDate . "<br />"
//		);
	}
		
	/**
	 * Returns the latest year that has at least one completed day within the January fiscal period
	 *
	 * @return unknown
	 */
	public function getLatestYear()
	{
		$actualYear = date("Y");
		
		$fiscalPeriod = $this->dateToFiscalPeriod(date("Y-m-d", mktime(0,0,0,date('m'), date('j') - 1, date('Y'))));
		
		$fiscalPeriodYear = substr($fiscalPeriod, 0, 4);
		$fiscalPeriodMonth = substr($fiscalPeriod, 4, strlen($fiscalPeriod) - 4);
		
		if ($actualYear > $fiscalPeriodYear)
		{
			if ($fiscalPeriodMonth >= 10)
			{
				$year = $actualYear;
			}
			else 
			{
				$year = $fiscalPeriodYear;
			}
		}
		else 
		{
			$year = $actualYear;
		}
		
		return $year;
	}
	
	
	/**
	 * Determines whether a date is the first working day of a fiscal period
	 *
	 * @param string $date
	 * @return boolean $isFirstWorkingDay;
	 */
	public function isFirstWorkingDay($date)
	{
		$dateArr = explode("-", $date);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$startDate = $this->startOfFiscalPeriod($date);
		
		$startDateArr = explode("-", $startDate);
		
		$startDay = $startDateArr[2];
		$startMonth = $startDateArr[1];
		$startYear = $startDateArr[0];

		$workingDays = 0;
		
		if ($month == $startMonth)
		{
			for ($i=$startDay; $i<=$day; $i++)
			{
				$testDay = date("D", mktime(0,0,0,$month,$i,$year));
				
				if (($testDay != "Sat") && ($testDay != "Sun"))
				{
					$workingDays++;
				}
			}			
		}
		else 
		{
			for ($i=$startDay; $i<=31; $i++)
			{
				$testDay = date("D", mktime(0,0,0,$startMonth,$i,$startYear));
				
				if (($testDay != "Sat") && ($testDay != "Sun"))
				{
					$workingDays++;
				}
			}
			
			for ($i=1; $i<=$day; $i++)
			{
				$testDay = date("D", mktime(0,0,0,$month,$i,$year));
				
				if (($testDay != "Sat") && ($testDay != "Sun"))
				{
					$workingDays++;
				}
			}
		}	
		
		$isFirstWorkingDay = ($workingDays > 1) ? false : true;
		
		return $isFirstWorkingDay;
	}
	
	
	
	/**
	 * Calculates 2 dates - rolling month if the date is today, otherwise the dates of the fiscal period
	 *
	 * @param date $date (yyyy-mm-dd)
	 * @return 2 dates ($this->fromDate, $this->toDate)
	 */
	public function dateToMonth() 
	{
		$dateArr = explode("-", $this->date);
		
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		if ($this->date == $this->todaysDate)
		{
			// calculates the rolling month up to the current date minus 1
			
			if ($dateArr[2] == 1)
			{
				$day = 31;
				$month = $month - 1;
			}
			else 
			{
				$day = $dateArr[2] - 1;	
			}

			$previousDay = $day;
			
			if ($month == 1)
			{
				$previousMonth = 12;
				$previousYear = $year - 1;
			}
			else
			{
				$previousMonth = $month - 1;
				$previousYear = $year;
			}
			
			// ensure fromDate will be valid
			while (!checkdate($previousMonth,$previousDay,$previousYear))
			{
				$previousDay = $previousDay - 1;				
			}
			
			$fromDate = mktime(0,0,0,$previousMonth,$previousDay,$previousYear);
			$toDate = mktime(0,0,0,$month,$day,$year);
			
			$fromDate = date("Y-m-d", $fromDate);
			$toDate = date("Y-m-d", $toDate);
		}
		else 
		{
			$day = $dateArr[2];
			
			$fromDate = $this->startOfFiscalPeriod($this->date);
	
			$fromDateArr = explode("-", $fromDate);
			
			$previousDay = $fromDateArr[2];
			$previousMonth = $fromDateArr[1];
			$previousYear = $fromDateArr[0];
			
			$toDate = $this->date;
		}
		
		$this->fromDate = $fromDate;
		$this->toDate = $toDate;
		
		$this->fromDateDay = (integer)$previousDay;
		$this->fromDateMonth = (integer)$previousMonth;
		$this->fromDateYear = (integer)$previousYear;
		
		$this->toDateDay = (integer)$day;
		$this->toDateMonth = (integer)$month;
		$this->toDateYear = (integer)$year;
	}
	
	
	public function getFromDate()
	{
		$this->dateToMonth();
		return $this->fromDate;
	}
	
	
	public function getToDate()
	{
		$this->dateToMonth();
		return $this->toDate;
	}
	
	
	public function getFromDateDay()
	{
		$this->dateToMonth();
		return $this->fromDateDay;
	}
	
	
	public function getFromDateMonth()
	{
		$this->dateToMonth();
		return $this->fromDateMonth;
	}
	
	
	public function getFromDateYear()
	{
		$this->dateToMonth();
		return $this->fromDateYear;
	}
	
	
	public function getToDateDay()
	{
		$this->dateToMonth();
		return $this->toDateDay;
	}
	
	
	public function getToDateMonth()
	{
		$this->dateToMonth();
		return $this->toDateMonth;
	}
	
	
	public function getToDateYear()
	{
		$this->dateToMonth();
		return $this->toDateYear;
	}
	
	
	public function dateToMonthFrom() 
	{
		$this->dateToMonth();
		return $this->fromDate;
	}
	
	
	public function dateToMonthTo() 
	{
		$this->dateToMonth();
		return $this->toDate;
	}
	
	
	/**
	 * Returns the from/to dates of fiscal periods between 2 dates
	 *
	 * @param date $fromDate
	 * @param date $toDate
	 * @return array $fiscalPeriods
	 */
	public function datesToFiscalPeriods($fromDate, $toDate)
	{	
		//var_dump($fromDate . $toDate);
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE (fromDate <= '" . $fromDate . "' AND toDate >= '" . $fromDate . "') 
			OR (fromDate >= '" . $fromDate . "' AND toDate <= '" . $toDate . "') 
			OR (fromDate <= '" . $toDate . "' AND toDate >= '" . $toDate . "')";
		//var_dump($sql);
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
			
		$counter = 0;
		
		$fiscalPeriods = array();
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			// convert values to dates & populate fiscalPeriods array
			$fiscalPeriods[$counter][0] = $fieldset['fromDate'];
			$fiscalPeriods[$counter][1] = $fieldset['toDate'];
			
			$counter++;
		}
	
		return $fiscalPeriods;
	}
	
	
	
	public function numWorkingDaysMTD()
	{
		$numDays = ($this->date == date("Y-m-d")) ? -1 : 0;
		
		$numDays += $this->numWorkingDaysBetweenDates($this->startDate, $this->date);

		return $numDays;
	}
	
	
	/**
	 * Returns the from/to dates of fiscal weeks between 2 dates
	 *
	 * @param date $fromDate
	 * @param date $toDate
	 * @return array $fiscalWeeks
	 */
	public function datesToFiscalWeeks($fromDate, $toDate)
	{
		$sql = "SELECT * 
			FROM fiscalCalendarByWeek 
			WHERE (fromDate <= '" . $fromDate . "' AND toDate >= '" . $fromDate . "') 
			OR (fromDate >= '" . $fromDate . "' AND toDate <= '" . $toDate . "') 
			OR (fromDate <= '" . $toDate . "' AND toDate >= '" . $toDate . "') ORDER BY weekNo";
		//var_dump($fromDate . " - " . $toDate . " - " . $sql);
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		$counter = 0;
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			// convert values to dates & populate fiscalPeriods array
			$fiscalWeeks[$counter][0] = $fieldset['fromDate'];
			$fiscalWeeks[$counter][1] = $fieldset['toDate'];
			
			// add the number of working days during the week to the array
			$numWorkingDays = $this->numWorkingDaysBetweenDates($fieldset['fromDate'], $fieldset['toDate']);
			
			$fiscalWeeks[$counter][2] = $numWorkingDays;
			
			$counter++;
		}

		return $fiscalWeeks;
	}
	
	
	public function numWorkingDaysBetweenDates($startDate, $endDate)
	{		
		$numDays = 0;
		
		$startDateArr = explode("-", $startDate);
		
		$startDay = (int)$startDateArr[2];
		$startMonth = (int)$startDateArr[1];
		$startYear = (int)$startDateArr[0];
		
		$endDateArr = explode("-", $endDate);
		
		$endDay = (int)$endDateArr[2];
		$endMonth = (int)$endDateArr[1];
		$endYear = (int)$endDateArr[0];
		
		if ($startMonth == $endMonth)
		{
			for($i = $startDay; $i <= $endDay; $i++)
			{
				$date = mktime(0,0,0,$startMonth,$i,$startYear);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$numDays++;
				}
			}
		}
		else
		{
			while (checkdate($startMonth,$startDay,$startYear))
			{
				$date = mktime(0,0,0,$startMonth,$startDay,$startYear);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$numDays++;
				}

				$startDay++;
			}
			
			if (($startMonth + 1) != $endMonth)
			{
				if (($startMonth + 1) == 13)
				{
					$nextMonth = 1;
					$nextYear = $endYear;
				}
				else 
				{
					$nextMonth = $startMonth + 1;
					$nextYear = $startYear;
				}		
				
				$nextDay = 1;

				while (checkdate($nextMonth,$nextDay,$nextYear) && $nextDay <= 31)
				{
					$date = mktime(0,0,0,$nextMonth,$nextDay,$nextYear);
					
					if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
					{
						$numDays++;
					}
	
					$nextDay++;
				}

			}
			
			$day = 1;
			
			while (checkdate($endMonth,$day,$endYear) && $day <= $endDay)
			{
				$date = mktime(0,0,0,$endMonth,$day,$endYear);
				
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$numDays++;
				}

				$day++;
			}
		}

		return $numDays;
	}
	
	
	
	/**
	 * Returns the beginning of a fiscal period for a specified date
	 *
	 * @param date $date
	 * @return date $fiscalStart
	 */
	public function startOfFiscalPeriod($date)
	{
		$sql = "SELECT min(fromDate) as startDate 
			FROM `fiscalCalendar` 
			WHERE toDate >= '" . $date . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$fiscalStart = $fieldset['startDate'];
				
		return $fiscalStart;	
	}
	
	
	public function presentFiscalYear()
	{
		$sql = "SELECT period 
			FROM fiscalCalendar 
			WHERE toDate >= '" . $this->todaysDate . "' 
			LIMIT 0, 1";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$period = $fieldset['period'];
		
		$year = (int)(substr($period, 0, 4));
		$year += 1;

		return $year;
	}
	
	
	public function presentFiscalMonthAsMonth($monthFormat)
	{
		$sql = "SELECT period 
			FROM fiscalCalendar 
			WHERE toDate >= '" . $this->todaysDate . "' 
			LIMIT 0, 1";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$period = $fieldset['period'];
		
		$period = (int)(substr($period, -2, 2));
		
		$period += 3;
		
		if ($period > 12)
		{
			$period -= 12;
		}
		
		$month = date($monthFormat, mktime(0,0,0,$period,1,$this->year));
					
		return $month;
	}	
		
	
	public function presentFiscalMonthInt()
	{	
		$sql = "SELECT min(fromDate) as startDate 
			FROM fiscalCalendar 
			WHERE toDate >= '" . $this->todaysDate . "' 
			LIMIT 0, 1";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$date = $fieldset['startDate'];
		$dateArr = explode("-", $date);
		
		$year = $dateArr[0];
		$month = $dateArr[1];
		$day = $dateArr[2] + 15;
		
		$month = date("n", mktime(0,0,0,$month,$day,$year));
					
		return $month;
	}
	
	
	public function getPeriodLastYear($period)
	{
		$year = substr($period, 0, 4);
		$month = substr($period, 4, 2);
		
		$previousPeriod = ($year - 1) . $month;
		
		return $previousPeriod;
	}

	
	public function fiscalPeriodStartDate($period)
	{
		$sql = "SELECT fromDate
			FROM fiscalCalendar
			WHERE period = " . $period;
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$periodStart = $fieldset['fromDate'];
				
		return $periodStart;	
	}
	
	
	public function fiscalPeriodEndDate($period)
	{
		$sql = "SELECT toDate
			FROM fiscalCalendar
			WHERE period = " . $period;
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$periodEnd = $fieldset['toDate'];
				
		return $periodEnd;	
	}
	
	
	/**
	 * Returns a list of fiscal periods between 2 dates
	 *
	 * @param date $fromDate, date $toDate
	 * @return date $fiscalPeriodsAsPeriods
	 */
	public function datesToFiscalPeriodsAsPeriods($fromDate, $toDate)
	{
		$fiscalPeriodsAsDates = $this->datesToFiscalPeriods($fromDate, $toDate);
		
		$fiscalPeriodsAsPeriods = array();
		
		foreach ($fiscalPeriodsAsDates as $period)
		{
			$thisPeriod = $this->dateToFiscalPeriod($period[0]);
			
			array_push($fiscalPeriodsAsPeriods, $thisPeriod);
		}
		
		return $fiscalPeriodsAsPeriods;
	}
	

	/**
	 * Calculates the fiscal period for a given date
	 *
	 * @param string $date
	 * @return string $fiscalPeriod
	 */
	public function dateToFiscalPeriod($toDate)
	{
		$sql = "SELECT fromDate as startDate, period 
			FROM fiscalCalendar 
			WHERE toDate >= '" . $toDate . "' 
			GROUP BY period ASC
			LIMIT 0, 1";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$fiscalPeriod = $fieldset['period'];
							
		return $fiscalPeriod;
	}
	
	
	/**
	 * Calculates the fiscal month for a given date e.g./ Apr
	 *
	 * @param string $date
	 * @return string $fiscalPeriod
	 */
	public function fiscalPeriodToShortMonth($fiscalPeriod)
	{
		$sql = "SELECT fromDate 
			FROM fiscalCalendar 
			WHERE period >= '" . $fiscalPeriod . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$date = $fieldset['fromDate'];
		
		$dateArr = explode("-", $date);
		
		$month = $dateArr[1];
		$day = $dateArr[2] + 15;
		
		$month = date("M", mktime(0,0,0,$month,$day,$this->todaysYear));
					
		return $month;
	}
	
	
	/**
	 * Calculates the fiscal month for a given date e.g./ April
	 *
	 * @param string $date
	 * @return string $fiscalPeriod
	 */
	public function fiscalPeriodToMonth($fiscalPeriod)
	{
		$sql = "SELECT fromDate 
			FROM fiscalCalendar 
			WHERE period >= '" . $fiscalPeriod . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$date = $fieldset['fromDate'];
		
		$dateArr = explode("-", $date);
		
		$month = $dateArr[1];
		$day = $dateArr[2] + 15;
		
		$month = date("F", mktime(0,0,0,$month,$day,$this->todaysYear));
					
		return $month;
	}
	
	
	/**
	 * Calculates the number of working days for a fiscal period
	 *
	 * @param string $fiscalPeriod
	 * @return integer $numDays
	 */
	public function numWorkingDaysInPeriod($fiscalPeriod)
	{
		$sql = "SELECT toDate, fromDate 
			FROM fiscalCalendar 
			WHERE period = '" . $fiscalPeriod . "'";

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$startDate = $fieldset['fromDate'];
		$endDate = $fieldset['toDate'];
		
		$numDays = 0;
		
		$startDateArr = explode("-", $startDate);
		
		$startDay = $startDateArr[2];
		$startMonth = $startDateArr[1];
		$startYear = $startDateArr[0];
		
		$endDateArr = explode("-", $endDate);
		
		$endDay = $endDateArr[2];
		$endMonth = $endDateArr[1];
		$endYear = $endDateArr[0];
		
		for($i = $startDay; $i <= 31; $i++)
		{
			$date = mktime(0,0,0,$startMonth,$i,$startYear);
			
			if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
			{
				$numDays++;
			}
		}
		
		// if the fiscal period spans 3 seperate months, find working days in the entire second month
		if (($startMonth != $endMonth) && ($startMonth != 12 && $endMonth != 1) &&
			($startMonth != ($endMonth - 1)))
		{
			for ($i = 1; $i <= 31; $i++)
			{
				$month = $endMonth - 1;
				
				// correct month if at year boundary
				if ($month <= 0)
				{
					$month += 12;
				}
			
				$date = mktime(0,0,0,$month,$i,$endYear);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$numDays++;
				}
			}
		}
		
		if (($startMonth != $endMonth))
		{
			for ($i = 1; $i <= $endDay; $i++)
			{
				$date = mktime(0,0,0,$endMonth,$i,$endYear);
				if ((date("D", $date) != "Sat") && (date("D", $date) != "Sun"))
				{
					$numDays++;
				}
			}
		}		
		
		return $numDays;
	}
	
	
	/**
	 * Returns the end of a fiscal period as a date in SQL format, for a specified date
	 *
	 * @param date $date
	 * @return date $fiscalEndDate
	 */
	public function endOfFiscalPeriod($date)
	{
		$sql = "SELECT max(toDate) as endDate 
			FROM fiscalCalendar 
			WHERE fromDate <= '" . $date . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$fiscalEndDate = $fieldset['endDate'];
		
		return $fiscalEndDate;	
	}
	
	
	/** 
	 * Returns yesterdays date in SQL format (Y-m-d)
	 * 
	 * @return string $yesterday
	 */
	public function yesterday()
	{
		$yesterday = date("Y-m-d", mktime(0,0,0,$this->todaysMonth,$this->todaysDay - 1,$this->todaysYear));
		
		return $yesterday;
	}
	
	
	public function endOfFiscalPeriodReached()
	{
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE toDate = '" . $this->endDate . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		$end = false;
		
		while ($fieldset = mysql_fetch_array($dataset))
		{
			$end = true;
		}
				
		return $end;	
	}
	
	
	/** 
	 * Returns the last working date in SQL format (Y-m-d)
	 * 
	 * @return string $yesterday
	 */
	public function lastWorkingDate()
	{
		$date = "";
		$counter = 1;
		
		while ($date == "")
		{
			$previousDate = date("D", mktime(0,0,0,$this->todaysMonth,$this->todaysDay - $counter,$this->todaysYear));
			
			if (($previousDate != "Sat") && ($previousDate != "Sun"))
			{
				$date = date("Y-m-d", mktime(0,0,0,$this->todaysMonth,$this->todaysDay - $counter,$this->todaysYear));
			}
			
			$counter++;
		}	
		
		return $date;
	}
	
	
	/**
	 * Takes a date in SQL format and returns it in a short format e.g./ 3rd Jan
	 *
	 * @param string $sqlDate
	 * @return unknown
	 */
	public function formatShortDate($sqlDate)
	{
		$dateArr = explode("-", $sqlDate);
		
		$date = date("jS M",mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]));
		
		return $date;
	}
	
	
	/** 
	 * Returns the first day of the current month as a date in SQL format (Y-m-d)
	 * 
	 * @return string $monthDate
	 */
	public function currentMonthStartDate()
	{
		$monthDate = date("Y-m-d", mktime(0,0,0,$this->todaysMonth,1,$this->todaysYear));
		
		return $monthDate;
	}
	
	
	/**
	 * Shows the days minus 1 from the start of the current week
	 * 2-dimensional array, stored as "'Mon', '2010-04-12'"
	 */
	public function calcFiscalWeekDays($currentFiscalWeek, $endDate, $passedMonth, $passedYear)
	{			
		$dateArr = explode("-", $currentFiscalWeek[0]);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$fiscalWeekStart = mktime(0,0,0,$month,$day,$year);
		
		$fiscalWeekDays = array();

		$endDate = $this->endDateAsDate;
	
		while ($fiscalWeekStart <= $endDate)
		{
			$newDay = (date("D", $fiscalWeekStart));
			
			if ($newDay != "Sat" && $newDay != "Sun")
			{
				array_push($fiscalWeekDays, array($newDay, date("Y-m-d", $fiscalWeekStart)));
			}
			
			$day += 1;
			
			$fiscalWeekStart = mktime(0,0,0,$month,$day,$year);
		}	
		
		// remove the current day
		array_pop($fiscalWeekDays);
		
		return $fiscalWeekDays;		
	}
	
}