<?php

//include("./apps/dashboard/lib/zoverduen/zoverduenLib.php");

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/05/2010
 */
class zOverdueNByDaysChart extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	public $graphXML = "";
	private $chartName = "z_overdue_nByDaysChart_summary_chart";
	private $chartHeight = 300;
	public $zoverduenLib;
	
	public $currentMonthCountToDayNo;
	public $currentMonthCountToMonthNo;
	public $currentMonthCountToYearNo;
	
	public $previousMonthCountToDayNo;
	public $previousMonthCountToMonthNo;
	public $previousMonthCountToYearNo;
	
	public $monthToDisplayInChart;
	
	private $total;
	private $total2;
	private $arrData = array();
	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		$this->setColourScheme("title-box2");
		
		$this->zoverduenLib = new zoverduenLib();
		
		// This will get filters on the page
		$this->zoverduenLib->getFilters();
		
		$this->getSQLDates();
	}
	
	public function output()
	{				
		$this->xml .= "<zOverdueNByDaysChart>";
		
		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		$this->xml .= "<allowed>1</allowed>";
		
		/**
		 * ZOVERDUEN START
		 * Generate ZOVERDUEN report
		 */
		$this->generateZOverdueNByDaysChart();
			$this->xml .= "<graphChartLocation>" . fusionChartsCache::getFusionChartsLocation() . "</graphChartLocation>";
			$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		
		$this->xml .= "</zOverdueNByDaysChart>";
		
		return $this->xml;
	}
	
	public function getSQLDates()
	{
		// thisYear and monthNumber are brought across from getFilters()
		
		date("d") == 1 ? $currentDay = 31 : $currentDay = date("d") - 1;
		
		$currentMonth = $this->zoverduenLib->monthNumber;
		
		$currentYear = $this->zoverduenLib->thisYear;
		
//		if($this->zoverduenLib->monthNumber == 1)
//		{
//			$currentMonth = 12;
//			$currentYear = $this->zoverduenLib->thisYear - 1;
//		}
//		elseif(date("d") == 1 && $this->zoverduenLib->monthNumber != 1)
//		{
//			$currentMonth = $this->zoverduenLib->monthNumber - 1;
//			$currentYear = $this->zoverduenLib->thisYear;
//		}
//		else 
//		{
//			$currentMonth = $this->zoverduenLib->monthNumber;
//			$currentYear = $this->zoverduenLib->thisYear;
//		}
		
		$this->currentMonthCountToDayNo = $currentDay; // This is used in for and while loop in place of 31
		$this->currentMonthCountToMonthNo = $currentMonth; // This is used within the SQL statement
		$this->currentMonthCountToYearNo = $currentYear; // This is used within the SQL statement
		
		//---------
		//date("d") == 1 ? $previousDay = 31 : 
		$previousDay = $currentDay;				
		
		if($this->currentMonthCountToMonthNo == 1)
		{
			$previousMonth = 12;
			$previousYear = $currentYear - 1;
		}
//		elseif($this->currentMonthCountToDayNo == 1 && $this->zoverduenLib->monthNumber != 1)
//		{
//			$previousMonth = $currentMonth - 1;
//			$previousYear = $this->zoverduenLib->thisYear;
//		}
		else 
		{
			$previousMonth = $currentMonth - 1;
			$previousYear = $currentYear;
		}
		
		$this->previousMonthCountToDayNo = $previousDay;
		$this->previousMonthCountToMonthNo = $previousMonth;
		$this->previousMonthCountToYearNo = $previousYear;
		
		if(date("d") == 1)
		{
			$this->monthToDisplayInChart = common::getMonthNameByNumber($this->currentMonthCountToMonthNo);
		}
		else 
		{
			$this->monthToDisplayInChart = common::getMonthNameByNumber($this->previousMonthCountToMonthNo) . " - " . common::getMonthNameByNumber($this->currentMonthCountToMonthNo);	
		}
	}
	
	/**
	 * This is the ZOVERDUEN report by Sales Organisation
	 *
	 * @param string $salesOrganisation (The sales organisation we are searching for)
	 * @param array $filters (| seperated)
	 */
	public function generateZOverdueNByDaysChart()
	{		
		if(!isset($this->zoverduenLib->plantName))
		{
			$plant = "Group";
		}
		else 
		{
			$plant = str_replace("'", "", $this->zoverduenLib->plantName);
		}
		
		if((isset($this->zoverduenLib->bu)) && ($this->zoverduenLib->bu != "All"))
		{
			$bu = $this->zoverduenLib->bu;
		}
		else 
		{
			$bu = "All";
		}
		
		$this->graphXML = "&#60;chart caption='Overdue Order Value By Days Overdue (" . $plant . ") (BU: " . $bu . ")' xAxisName='Days' yAxisName='Value (GBP)' showValues='0' rotateNames='1' useRoundEdges='1' divLineAlpha='100' numVDivLines='31' vDivLineAlpha='0' showAlternateVGridColor='1' alternateVGridAlpha='5' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter2' exportFileName='zoverduenByDaysChart' clickURL='/apps/dashboard/zoverduenDrillDown?' &#62;";
				
		$this->setDataPoints();
		
		$this->graphXML .= "&#60;/chart&#62;";
		   
		return $this->graphXML;
	}
	
	private function setDataPoints()
	{
		foreach($this->zoverduenLib->daysOverdueGroups as $timeDifferences)
		{
//			if($timeDifferences[0] == 1)
//			{
//				$timeOne = 0;
//			}
//			else 
//			{
				$timeOne = $timeDifferences[0];
//			}
			
			if (!isset($this->zoverduenLib->plant))
			{
				$sql = "SELECT sum(openAmount) AS openAmount 
					FROM zoverduen 
					WHERE daysOverdue <= -" . $timeOne . " AND daysOverdue >= -" . $timeDifferences[1] . "
					AND reportDate = '" . date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "' 
					AND openQty != 0
					" . $this->zoverduenLib->businessUnit;
			}
			else
			{
				$sql = "SELECT sum(openAmount) AS openAmount 
					FROM zoverduen 
					WHERE daysOverdue <= -" . $timeOne . " AND daysOverdue >= -" . $timeDifferences[1] . "
					AND reportDate = '" . date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - 1, date("Y"))) . "' 
					AND openQty != 0
					AND plant IN(" . $this->zoverduenLib->plant . ") " . $this->zoverduenLib->businessUnit;
			}
			//var_dump($sql); die();
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			$fields = mysql_fetch_array($dataset);
			
			if($timeDifferences[1] == 99999999999999)
			{
				$this->graphXML .= "&#60;set label='> 14' value='" . $fields['openAmount'] . "' link='j-updateReloadDataDiv(" .$timeDifferences[0] . $timeDifferences[1] . ")' /&#62;";
			}
			else 
			{
				$this->graphXML .= "&#60;set label='" . $timeDifferences[0] . "-" . $timeDifferences[1] . "' value='" . $fields['openAmount'] . "' link='j-updateReloadDataDiv(" .$timeDifferences[0] . $timeDifferences[1] . ")' /&#62;";
			}
		}
	}
}

?>