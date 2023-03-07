<?php
include("./apps/dashboard/snapins/serviceDeskMonthly/serviceDeskMonthly.php");
class generateUpdatedChart
{
	function __construct(){
	
		$exporter = $_GET['exporter'];
		
		$s1 = $_GET['s1'];
		
		$chart= new serviceDeskMonthly($s1, $exporter);
		
		$chart->generateChart($s1);
			
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>