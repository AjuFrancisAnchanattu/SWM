<?php
include("./apps/dashboard/snapins/serviceDeskCallsWithCSC/serviceDeskCallsWithCSC.php");
class generateUpdatedChart
{
	function __construct(){
	
		$exporter = $_GET['exporter'];
		
		$chart= new serviceDeskCallsWithCSC("", $exporter);
		
		$chart->generateChart();
			
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}

?>