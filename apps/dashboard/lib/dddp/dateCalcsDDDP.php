<?php

/**
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Robert Markiewka
 * @version 06/04/10
 */

class dateCalcsDDDP extends page
{
	public $date;
	public $fromDate;
	public $toDate;
	public $fromDateDay;
	public $fromDateMonth;
	public $fromDateYear;
	public $toDateDay;
	public $toDateMonth;
	public $toDateYear;
	
	function __construct()
	{	
		if (isset($_POST['endDate'])) 
		{
			$this->date = $_POST['endDate'];
		}
		else
		{
			// set todays date as the default date
			$this->date = date("Y-m-d");
		}
		
		//$this->date = "2010-03-30";
		//echo $this->dateToMonthFrom($this->date) . " - ";
		//echo $this->dateToMonthTo($this->date);
		// test
		/**$test = $this->datesToFiscalPeriodsByWeek("2010-04-12", "2010-05-12");
		foreach ($test as $row)
		{
			echo $row[0] . " - " . $row[1] . "<br />";
		}
		echo $this->startOfFiscalPeriod('2010-04-5');**/
	}

	/**
	 * Returns 2 dates for the SQL queries
	 *
	 * @param date $date (yyyy-mm-dd)
	 * @return 2 dates ($this->fromDate, $this->toDate)
	 */
	public function dateToMonth() 
	{
		// calculates the rolling month up to a specified date
		
		$dateArr = explode("-", $this->date);
		
		$day = $dateArr[2] - 1;
		$month = $dateArr[1];
		$year = $dateArr[0];
		
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
		
		$this->fromDate = date("Y-m-d", $fromDate);
		$this->toDate = date("Y-m-d", $toDate);
		
		$this->fromDateDay = $previousDay;
		$this->fromDateMonth = $previousMonth;
		$this->fromDateYear = $previousYear;
		$this->toDateDay = $day;
		$this->toDateMonth = $month;
		$this->toDateYear = $year;
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
	 * Returns a number of fiscal periods between 2 dates
	 *
	 * @param date $fromDate
	 * @param date $toDate
	 * @return array $fiscalPeriods
	 */
	public function datesToFiscalPeriods($fromDate, $toDate)
	{	
		$sql = "SELECT * FROM `fiscalCalendar` WHERE (fromDate <= '" . $fromDate . "' AND toDate >= '" . $fromDate . "') 
			OR (fromDate >= '" . $fromDate . "' AND toDate <= '" . $toDate . "') 
			OR (fromDate <= '" . $toDate . "' AND toDate >= '" . $toDate . "')";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
			
		$counter = 0;
		while($fieldset = mysql_fetch_array($dataset))
		{
			// convert values to dates & populate fiscalPeriods array
			$fiscalPeriods[$counter][0] = $fieldset['fromDate'];
			$fiscalPeriods[$counter][1] = $fieldset['toDate'];
			$counter++;
		}
		
		return $fiscalPeriods;
	}
	
	
	
	
	/**
	 * Returns the dates of fiscal weeks between 2 dates
	 *
	 * @param date $fromDate
	 * @param date $toDate
	 * @return array $fiscalPeriods
	 */
	public function datesToFiscalPeriodsByWeek($fromDate, $toDate)
	{
		$sql = "SELECT * FROM `fiscalCalendarByWeek` WHERE (fromDate <= '" . $fromDate . "' AND toDate >= '" . $fromDate . "') 
			OR (fromDate >= '" . $fromDate . "' AND toDate <= '" . $toDate . "') 
			OR (fromDate <= '" . $toDate . "' AND toDate >= '" . $toDate . "')";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		$counter = 0;
		while($fieldset = mysql_fetch_array($dataset))
		{
			// convert values to dates & populate fiscalPeriods array
			$fiscalPeriods[$counter][0] = $fieldset['fromDate'];
			$fiscalPeriods[$counter][1] = $fieldset['toDate'];
			
			$counter++;
		}
		return $fiscalPeriods;
	}
	
	
	/**
	 * Returns the beginning of a fiscal period for a specified date
	 *
	 * @param date $date
	 * @return date $fiscalStart
	 */
	public function startOfFiscalPeriod($date)
	{
		$sql = "SELECT min(fromDate) as startDate FROM `fiscalCalendar` WHERE toDate >= '" . $date . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);

		$fieldset = mysql_fetch_array($dataset);
		$fiscalStart = $fieldset['startDate'];
		
		$dateArr = explode("-", $fiscalStart);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$fiscalStart = date("Y-m-d", mktime(0,0,0,$month,$day,$year));
				
		return $fiscalStart;	
	}
	
	
	/**
	 * Returns the beginning of a fiscal period for a specified date
	 *
	 * @param date $date
	 * @return date $fiscalStart
	 */
	public function endOfFiscalPeriod($date)
	{
		$sql = "SELECT max(toDate) as endDate FROM `fiscalCalendar` WHERE fromDate <= '" . $date . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);

		$fieldset = mysql_fetch_array($dataset);
		$fiscalEnd = $fieldset['endDate'];
		
		$dateArr = explode("-", $fiscalEnd);
		
		$day = $dateArr[2];
		$month = $dateArr[1];
		$year = $dateArr[0];
		
		$fiscalEnd = date("Y-m-d", mktime(0,0,0,$month,$day,$year));

		return $fiscalEnd;	
	}
	
	
	/** 
	 * Returns yesterdays date in SQL format (Y-m-d)
	 * 
	 * @return string $yesterday
	 */
	public function yesterday()
	{
		$yesterday = date("Y-m-d", mktime(0,0,0,date("m"),date("j") - 1,date("Y")));
		
		return $yesterday;
	}
	
	
	/** 
	 * Returns the first day of the current month as a date in SQL format (Y-m-d)
	 * 
	 * @return string $monthDate
	 */
	public function currentMonthStartDate()
	{
		$monthDate = date("Y-m-d", mktime(0,0,0,date("m"),1,date("Y")));
		
		return $monthDate;
	}
	
}