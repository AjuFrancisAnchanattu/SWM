<?php
include("./apps/dashboard/snapins/serviceDeskRegionComparison/serviceDeskRegionComparison.php");
class generateUpdatedChart
{
	function __construct()
	{
		$exporter = $_GET['exporter'];
		
		$region = $_GET['region'];
		
		$chart= new serviceDeskRegionComparison($region, $exporter);
		
		$chart->generateChart($region);
					
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>