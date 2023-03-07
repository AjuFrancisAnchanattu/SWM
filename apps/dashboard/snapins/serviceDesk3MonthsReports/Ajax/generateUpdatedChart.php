<?php
include("./apps/dashboard/snapins/serviceDesk3MonthsReports/serviceDesk3MonthsReports.php");
class generateUpdatedChart
{
	function __construct(){
		
		$exporter = $_GET['exporter'];
		
		$id = (int)$_GET['chartId'];
		
		$chart= new serviceDesk3MonthsReports($id, $exporter);
		
		$chart->generateChart($id);
			
		$xmlTEST= $chart->graphXML;
		
		$xmlTEST= str_replace("&#60;", "<", $xmlTEST);
		$xmlTEST= str_replace("&#62;", ">", $xmlTEST);
		
		echo $xmlTEST;
	}
}

?>