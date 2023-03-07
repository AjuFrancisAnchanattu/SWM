<?php
include("./apps/dashboard/snapins/serviceDeskSiteReports/serviceDeskSiteReports.php");
class generateUpdatedChart
{
	function __construct()
	{
		$exporter = $_GET['exporter'];
		
		$chart= new serviceDeskSiteReports('EUROPE', $exporter);
		
		$region = $_GET['region'];
		
		$chart->generateChart($region);
					
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}
?>