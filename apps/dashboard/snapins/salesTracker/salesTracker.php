<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 17/08/2009
 */
class salesTracker extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	public $graphXML = "";
	private $chartName = "salesTracker_summary";
	private $chartHeight = 300;	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		if(isset($_REQUEST['chartName']) && $_REQUEST['chartName'] == $this->chartName)
		{
			// do something ...
		}
		else 
		{
			// do something ...
		}
	}
	
	public function output()
	{				
		$this->xml .= "<salesTracker>";
		
		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_salesTracker"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			/**
			 * Sales Tracker START
			 * Generate Sales Tracker report
			 */
			$this->generateSalesTrackerChart();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
			
		$this->xml .= "</salesTracker>";
		
		return $this->xml;
	}

	private function generateSalesTrackerChart()
	{		
		$this->dateArray = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		
		$dateArraySize = count($this->dateArray) - 1;
		
		$counter = 0;
		
		//Create an XML data document in a string variable
		$this->graphXML = "&#60;graph caption='Sales Tracker Summary Chart' xAxisName='Month Name' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1'&#62;";
		
//		for($counter = 0; $counter <= $dateArraySize; $counter ++)
//		{
//			$this->graphXML .= "&#60;set name='" . $this->dateArray[$counter] . "' value='" . $this->getHitsFromDB($this->dateArray[$counter]) . "' /&#62;";
//		}
		
		$this->graphXML .= "&#60;/graph&#62;";
			
		return $this->graphXML;
	}
}

?>