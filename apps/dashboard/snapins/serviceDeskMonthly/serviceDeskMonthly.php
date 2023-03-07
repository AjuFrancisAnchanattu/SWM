<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 03/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskMonthly extends serviceDeskChart
{
	function __construct($controllsDefVal = "IT", $exporter = "monthlySummaryExporter")
	{
		parent::__construct("serviceDesk_Monthly_summary_chart", $exporter, 'S1', $controllsDefVal, "Column2D");
		
		$this->drillDown = 1;
		
		$this->setClass(__CLASS__);
	}	

	/**
	 * @override generateChart - pulling data out from database and displaying 
	 * 							 it on the chart
	 * @param string $s1
	 */
	public function generateChart($s1='IT')
	{
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
		
		$caption .= " Tickets By Month (" . date("Y") . ")";
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$monthArray = array(1 => "Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		
		$count = 1;
		foreach ($monthArray as $val => $key)
		{
			$sql = "SELECT id 
					FROM serviceDesk 
					WHERE ticketType !='task' "
					. $sqlS1Filter . " 
					AND MONTH(`startDate`)= " . $val . " 
					AND YEAR(`startDate`) = " . date("Y");
							
			$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute($sql);
			
			$link = 'JavaScript:display_' . $this->chartPath . '_DrillDown("month=' . $val . ',s1=' . $s1 .'")';
			
			$this->graphXML .= "&#60;set name='" . $key . "' value='" . mysql_num_rows($dataset) . "' link=' " . $link . "' /&#62;";
			$count++;
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}

?>