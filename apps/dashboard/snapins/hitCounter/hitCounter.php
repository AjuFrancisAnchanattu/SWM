<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 16/07/2009
 */
class hitCounter extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "hit_counter_n_summary_chart";
	private $chartHeight = 300;
	private $dateArray;
	private $thisMonth;
	private $thisYear;
	private $thisApp;
	private $uniqueHits;
	private $totalHits;
	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		$this->thisMonthName = date("F");
		$this->thisMonthNumber = date("m");
		$this->thisYear = date("Y");
		$this->app = "Home";
	}
	
	public function output()
	{				
		$this->xml .= "<hitCounter>";
		
		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
		$this->xml .= "<monthName>" . $this->thisMonthName . "</monthName>";
		
		$this->getUniqueHits();
		$this->xml .= "<uniqueHits>" . $this->uniqueHits . "</uniqueHits>";
		
		$this->getTotalHits();
		$this->xml .= "<totalHits>" . $this->totalHits . "</totalHits>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_hitcounter"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			/**
			 * HIT COUNTER START
			 * Generate HIT COUNTER report
			 */
			$this->generateHitCounterChart();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
			
		$this->xml .= "</hitCounter>";
		
		return $this->xml;
	}
	
	/**
	 * This is the HIT COUNTER report
	 *
	 */
	private function generateHitCounterChart()
	{		
		$this->dateArray = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
		
		$dateArraySize = count($this->dateArray) - 1;
		
		$counter = 0;
		
		//Create an XML data document in a string variable
		$this->graphXML = "&#60;graph caption='Hit Counter Summary Chart (" . $this->app . ")' exportEnabled='1' exportAtClient='1' showShadow='1' showValues='0' xAxisName='" . $this->thisMonthName . "' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' rotateNames='1' showLegend='1'&#62;";
		
		for($counter = 0; $counter <= $dateArraySize; $counter ++)
		{
			$this->graphXML .= "&#60;set name='" . $this->dateArray[$counter] . "' value='" . $this->getHitsFromDB($this->dateArray[$counter]) . "' /&#62;";
		}
		
		//$this->graphXML .= "&#60;trendLines&#62;";
		//	$this->graphXML .= "&#60;line startValue='895' color='FF0000' displayvalue='Average' toolText='This is the average of homepage hits for the month' dashed='1' /&#62;";
		//$this->graphXML .= "&#60;/trendLines&#62;";

		
		$this->graphXML .= "&#60;/graph&#62;";
			
		return $this->graphXML;
	}
	
	/**
	 * Calculate hits on the index page
	 *
	 * @param int $dateNumber (Calendar date from 1-31)
	 * @return int $hitCounterNumber (Number of hits)
	 */
	private function getHitsFromDB($dateNumber)
	{
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM hitCounter WHERE hitDate = '" . $this->thisYear . "-" . $this->thisMonthNumber . "-" . $dateNumber . "' AND app = '" . $this->app . "'");
		
		if(mysql_num_rows($dataset) > 0)
		{
			$hitCounterNumber = mysql_num_rows($dataset);
		}
		else 
		{
			$hitCounterNumber = "";
		}
		
		return $hitCounterNumber;
	}
	
	private function getUniqueHits()
	{
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT DISTINCT(NTLogon) FROM hitCounter WHERE hitDate BETWEEN '" . $this->thisYear . "-" . $this->thisMonthNumber . "-01' AND '" . $this->thisYear . "-" . $this->thisMonthNumber . "-31' AND app = '" . $this->app . "'");
		
		$this->uniqueHits = number_format(mysql_num_rows($dataset), 0, ".", ",");
		
		return $this->uniqueHits;
	}
	
	private function getTotalHits()
	{
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT id FROM hitCounter WHERE hitDate BETWEEN '" . $this->thisYear . "-" . $this->thisMonthNumber . "-01' AND '" . $this->thisYear . "-" . $this->thisMonthNumber . "-31' AND app = '" . $this->app . "'");
		
		$this->totalHits = number_format(mysql_num_rows($dataset), 0, ".", ",");
		
		return $this->totalHits;
	}
}

?>