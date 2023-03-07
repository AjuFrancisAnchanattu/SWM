<?php
class generateDrillDown
{
	private $graphXML = "";
	private $exporter = "drillDown_monthlySummaryExporter";
	private $month_array = array(1 => "Jan","Feb","Mar","Apr","May","June","July","Aug","Sep","Oct","Nov","Dec");
	
	function __construct()
	{
		$month = $_GET['month'];
		$s1 = $_GET['s1'];
		$this->exporter = $_GET['exporter'];
		
		$this->generateServiceDeskThisMonthsTickets($month, $s1);
		
		$xmlTEST = $this->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
	
	private function generateServiceDeskThisMonthsTickets($month, $s1)
	{
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
			
		$caption .= " Tickets By Day For " . $this->month_array[$month];
		$fileName = str_ireplace("/","_",$caption);
		
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Day' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->exporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		for ($i = 1; $i<=date("t"); $i++)
		{
			$sql = "SELECT id 
					FROM serviceDesk 
					WHERE ticketType !='task' "
					. $sqlS1Filter . " 
					AND DAY(startDate)=" . $i . " 
					AND MONTH(startDate)=" . $month . "
					AND YEAR(startDate)=" . date("Y");
			
			$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute($sql);
						
			$this->graphXML .= "&#60;set name='" . $i . "' value='" . mysql_num_rows($dataset) . "' color='" . sprintf('%02X%02X%02X', 6*$i, 6*$i, 6*$i) .  "' /&#62;";
		}
				
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
}