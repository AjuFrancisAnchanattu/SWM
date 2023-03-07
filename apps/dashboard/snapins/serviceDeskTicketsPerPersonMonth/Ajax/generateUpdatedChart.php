<?php
include("./apps/dashboard/snapins/serviceDeskTicketsPerPersonMonth/serviceDeskTicketsPerPersonMonth.php");
class generateUpdatedChart
{
	function __construct(){
		
		$s1 = $_GET['s1'];
		$month = $_GET['month'];
		$year = $_GET['year'];
		
		$exporter = $_GET['exporter'];
		
		$chart= new serviceDeskTicketsPerPersonMonth("IT,-1,-1", $exporter);
		
		$chart->generateChart($s1, $month, $year);
			
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>