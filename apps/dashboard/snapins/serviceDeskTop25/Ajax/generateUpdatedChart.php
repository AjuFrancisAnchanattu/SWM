<?php
include("./apps/dashboard/snapins/serviceDeskTop25/serviceDeskTop25.php");
class generateUpdatedChart
{
	function __construct()
	{
		$exporter = $_GET['exporter'];
		
		$chart= new serviceDeskTop25("IT,-1,-1", $exporter);
		
		$s1 = $_GET['s1'];
		$month = $_GET['month'];
		$year = $_GET['year'];
		
		$chart->generateChart($s1, $month, $year);
					
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>