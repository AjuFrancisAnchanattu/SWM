<?php

$root = realpath($_SERVER["DOCUMENT_ROOT"]); 

//all by value charts
include_once "$root/apps/customerComplaints/dashboards/chart_ByValue_ByMonth.php";
include_once "$root/apps/customerComplaints/dashboards/chart_ByValue_BySite.php";
include_once "$root/apps/customerComplaints/dashboards/chart_ByValue_ByMonth_ForSite.php";

//all by number charts
//-customer
include_once "$root/apps/customerComplaints/dashboards/chart_ByNumber_ByMonth_Customer.php";
include_once "$root/apps/customerComplaints/dashboards/chart_ByNumber_BySite.php";
include_once "$root/apps/customerComplaints/dashboards/chart_ByNumber_ByMonth_ForSite.php";
//-supplier (no drilldowns)
include_once "$root/apps/customerComplaints/dashboards/chart_ByNumber_ByMonth_Supplier.php";

//all category charts
include_once "$root/apps/customerComplaints/dashboards/chart_ByCategory.php";
include_once "$root/apps/customerComplaints/dashboards/chart_ByCategory_Value.php";


class getDashboard
{
	//this is used only for AJAX calls...
	public function __construct()
	{
		if(isset($_GET["chartName"]))
		{
			$chartName = $_GET["chartName"];
		}
		else
		{
			die("0");
		}
		
		if(isset($_GET["args"]))
		{
			$args = $_GET["args"];
		}
		
		switch($chartName)
		{
			//*******************
			//all by value charts
			case "chart_ByValue_ByMonth_ForSite":
				$chart = new chart_ByValue_ByMonth_ForSite();
				echo $chart->getChartXML($args);
				break;
			
			case "chart_ByValue_BySite":
				$chart = new chart_ByValue_BySite();
				echo $chart->getChartXML($args);
				break;
				
			case "chart_ByValue_ByMonth":
				$chart = new chart_ByValue_ByMonth();
				echo $chart->getChartXML();
				break;
			
			//********************
			//all by number charts
			case "chart_ByNumber_ByMonth_Customer":
				$chart = new chart_ByNumber_ByMonth_Customer();
				echo $chart->getChartXML();
				break;
				
			case "chart_ByNumber_BySite":
				$chart = new chart_ByNumber_BySite();
				echo $chart->getChartXML($args);
				break;
				
			case "chart_ByNumber_ByMonth_ForSite":
				$chart = new chart_ByNumber_ByMonth_ForSite();
				echo $chart->getChartXML($args);
				break;
				
			case "chart_ByNumber_ByMonth_Supplier":
				$chart = new chart_ByNumber_ByMonth_Supplier();
				echo $chart->getChartXML();
				break;
			
			//**********************
			//all by category charts
			case "chart_ByCategory":
				$chart = new chart_ByCategory();
				echo $chart->getChartXML($args);
				break;
				
			case "chart_ByCategory_Value":
				$chart = new chart_ByCategory_Value();
				echo $chart->getChartXML($args);
				break;
		}
	}
}

?>