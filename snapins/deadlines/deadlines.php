<?php


/**
 * 
 * This is a snapin that allows the admin to keep track of deadlines.  
 * It displays the deadline's name and the number of days until it should be completed.
 * 
 * Deadlines can be added to the snapin in the /snapins/deadlines/deadlines.xml file.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 06/03/2006
 */

class deadlines extends snapin 
{	
	/**
	 * holds the deadline's names and dates
	 *
	 * @var array
	 */
	private $deadlines = array();
	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName("Days Until...");
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<deadlines>";

		$today = date("d-m-Y");
				
		//put the deadlines into the array
		
		$contents = cache::getLocalDocument("./snapins/deadlines/deadlines.xml");
		
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($contents);
		$results = $xmlDoc->getElementsByTagName('item');
		
		foreach ($results as $result)
		{
			$this->deadlines[] = array('name' => $result->getAttribute('name'), 'date' => $result->getAttribute('date') ? $result->getAttribute('date') : $result->getAttribute('name'));
		}
		
	
		foreach ($this->deadlines as $deadline)
		{
			
			$this->xml .= "<deadline>";
			$this->xml .= "<name>" . $deadline['name'] . "</name>\n";
			$this->xml .= "<date>" . $this->daysDiff($today,$deadline['date']) . "</date>\n";
			$this->xml .= "</deadline>";
	
		}

		$this->xml .= "</deadlines>";
		
		return $this->xml;
	}
	
	
	
	
	/**
	 * Takes 2 dates and finds the number of days between them.
	 * 
	 * @param date $beginDate the start date
	 * @param date $endDate the end date
	 * @return int the number of days between the start date and end date
	 */
	function daysDiff($beginDate, $endDate)
	{
		$beginDateArray = explode("-",$beginDate);
		$beginDay = $beginDateArray[0];
		$beginMonth = $beginDateArray[1];
		$beginYear = $beginDateArray[2];
		
		
		$endDateArray = explode("-",$endDate);
		$endDay = $endDateArray[0];
		$endMonth = $endDateArray[1];
		$endYear = $endDateArray[2];
		
		$beginDate = gregoriantojd($beginMonth, $beginDay, $beginYear);
		$endDate = gregoriantojd($endMonth,$endDay,$endYear);
		
		return $endDate - $beginDate;
		
	}
}

?>