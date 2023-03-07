<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 06/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskSeverityByMonth extends serviceDeskChart
{	
	function __construct($controllsDefVal = "IT,-1,-1" , $exporter = "severityMonthlyChart")
	{
		parent::__construct("serviceDesk_severity_monthly_chart", $exporter, 'S1,Month,Year', $controllsDefVal, "Column2D");
		
		$this->setClass(__CLASS__);
		
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
	public function generateChart($s1='IT', $month=-1, $year=-1)
	{
		if($month != -1)
			$this->monthToShow = $month;
		if($year != -1)
			$this->yearToShow = $year;
			
		$monthName = parent::$MONTH_ARRAY[$this->monthToShow];
		
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

		$caption .= " Tickets By Severity For " . $monthName . " " . $this->yearToShow;
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Severity' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute("SELECT priority AS name, count(id) as data 
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND MONTH(startDate) = " . $this->monthToShow . "
							AND YEAR(startDate) = " . $this->yearToShow . " " 
							. $sqlS1Filter . " 
							GROUP BY priority");
		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) . "' link='' value='" . $fieldsGlob['data'] . "' /&#62;";
			
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
		
	}
}

?>