<?php
include("./apps/dashboard/snapins/serviceDeskClosedIn48ByResolver/serviceDeskClosedIn48ByResolver.php");
class generateUpdatedChart
{
	function __construct(){
		
		$s1 = $_GET['s1'];
		
		$exporter = $_GET['exporter'];
		
		$chart= new serviceDeskClosedIn48ByResolver($s1, $exporter);
		
		$chart->generateChart($s1);
			
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>