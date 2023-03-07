<?php

/**
 * Updates the month dropdown when the year dropdown is changed.
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 03/09/2010
 */
class saoUpdateMonth extends page 
{
	
	function __construct()
	{
		parent::__construct();
		
		Header("Content-type: text/xml");
		
		// if year is not set then kill the process
		if (!isset($_REQUEST['year']))
		{
			die();
		}
		
		$this->xml = "<container>";
		
			// ensure months before April 2010 are not selectable (we do not have earlier data)
			$monthLowerLimit = ($_REQUEST['year'] == '2010') ? 4 : 1;
		
			// if the year selected is the current year, only show months up to and including the current month (months for which there is data)
			if ($_REQUEST['year'] == date("Y"))
			{		
				$monthUpperLimit = $this->currentFiscalMonthInt();

				for ($i = $monthLowerLimit; $i <= $monthUpperLimit; $i++)
	    		{
	            	$monthName = date("F", mktime(0, 0, 0, $i, 1, 0));
	            	$monthValue = date("n", mktime(0, 0, 0, $i, 1, 0));
	
	               	$this->xml .= "<row name=\"" . $monthValue . "\">" . $monthName . "</row>";	
	    		}
			}
			else 
			{
				for ($i = $monthLowerLimit; $i <= 12; $i++)
	    		{
	            	$monthName = date("F", mktime(0, 0, 0, $i, 1, 0));
	            	$monthValue = date("n", mktime(0, 0, 0, $i, 1, 0));
	
					$this->xml .= "<row name=\"" . $monthValue . "\">" . $monthName . "</row>";
	    		}	
			}
		
		$this->xml .= "</container>";
		
		echo $this->xml;
	}
	
	
	/**
	 * Returns an integer for the current fiscal month
	 *
	 * @return int $month;
	 */
	private function currentFiscalMonthInt()
	{	
		$sql = "SELECT min(fromDate) as startDate 
			FROM fiscalCalendar 
			WHERE toDate >= '" . date("Y-m-d") . "' 
			LIMIT 0, 1";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		
		$date = $fieldset['startDate'];
		$dateArr = explode("-", $date);
		
		$year = $dateArr[0];
		$month = $dateArr[1];
		$day = $dateArr[2] + 15;
		
		// 15 added to the startDate day as some fiscal periods start at the end of a previous fiscal month
		$month = date("n", mktime(0,0,0,$month,$day,$year));
					
		return $month;
	}
	
}

?>