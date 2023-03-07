<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 03/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskTicketsPerPersonMonth extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT,-1,-1", $exporter = "ticketsByOwnerExporter")
	{
		parent::__construct("serviceDesk_owner_month_chart", $exporter, 'S1,Month,Year', $controllsDefVal, "Column2D", "MSColumn2D");
		
		$this->setClass(__CLASS__);
		
		$this->drillDown = 1;
		
		//setting up months to display etc
		$this->yearToShow = (int)date("Y");
		$this->monthToShow = (int)date("m");
	}
	
	/**
	 * @override generateChart - pulling data out from database and displaying 
	 * 							 it on the chart
	 * @param string $s1
	 * @param integer $month
	 * @param integer $year
	 */
	public function generateChart($s1='IT', $month= -1, $year= -1)
	{
		if($month != -1)
			$this->monthToShow = $month;
		if($year != -1)
			$this->yearToShow = $year;
			
		$this->monthName = parent::$MONTH_ARRAY[$this->monthToShow];
		
		$this->graphXML = "";

		$sqlS1Filter= ""; //used in sql query to choose data for appropriate s1

		if( $s1 != 'ALL' && $s1 !='IT')
			$sqlS1Filter = " AND s1='" . $s1 ."' ";
		else if ($s1 == 'IT')
			$sqlS1Filter = " AND s1 IN ('it','Intranet') ";
		else
			$sqlS1Filter = "";
			
		if( $s1 !='IT' )
			$caption = $s1;
		else
			$caption = "IT/Intranet";

		$caption .= " Tickets For ". $this->monthName . " " . $this->yearToShow . " By Owner";
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute("SELECT owner AS name, count(id) as data 
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND MONTH(startDate) = " . $this->monthToShow . "
							AND YEAR(startDate) = " . $this->yearToShow 
							. $sqlS1Filter . " 
							GROUP BY owner");
	
		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$link = 'JavaScript:display_' . $this->chartPath . '_DrillDown("month=' . $this->monthToShow . ',year=' . $this->yearToShow . ',s1=' . $s1 . ',owner=' . $fieldsGlob['name'] .'")';
			
			$toolText = usercache::getInstance()->get($fieldsGlob['name'])->getName() ." - " . $fieldsGlob['data'] . " tickets";
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) 
							. "' value='" . $fieldsGlob['data'] 
							. "' toolText='" . $toolText . "' link='" . $link . "'/&#62;";
		}

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
}

?>