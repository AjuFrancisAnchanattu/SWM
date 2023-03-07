<?php

include_once "dashboardsLib.php";

class chart_ByCategory extends dashboardsLib
{
	public function getChartXML($args)
	{
		if( $args == "-1" )
		{
			$fiscal = $this->getFiscalPeriods(2);
			$period = $fiscal[0];
		}
		else
		{
			$period = $this->getFiscalPeriodData($args);
		}
		$month = $period["month"];
		$month_short = $this->months[$month]["short"];
		$month_long = $this->months[$month]["long"];
		$year = $period["year"];
		
		$xml = "<chart caption='Complaints Number By Category' subcaption='For $month_long $year' showPercentValues='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_ByCategory_Exporter' registerWithJS='1' exportFileName='ccChart_ByCategory' >";
		
		$categories = $this->getCategoriesAndCount( $period );
		
		foreach( $categories as $category => $count )
		{
			$toolText = "$count \"$category\" complaint(s) have been submitted in $month_long $year";
			$xml .= "<set label='$category' value='$count' toolText='$toolText'/>";
		}
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	private function getCategoriesAndCount( $period )
	{
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$categories = array();
		
		//new system
		$sql = "SELECT COUNT(complaint.id) AS Rows, SUBSTRING( selectionOption, 1, 1) AS Category
				FROM selectionOptions 
				JOIN complaint
				ON complaint.categoryId = selectionOptions.id
				WHERE typeId = 3 
				AND submissionDate >= '$date_from' 
				AND submissionDate <= '$date_to'
				GROUP BY SUBSTRING( selectionOption, 1, 1)
				ORDER BY SUBSTRING( selectionOption, 1, 1)";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		while( $fields = mysql_fetch_array($dataset))
		{
			$cat = $fields["Category"];
			$count = $fields["Rows"];
			$categories[$cat] = $count;
		}
		
		//old system
		$sql = "SELECT COUNT(*) AS Rows, SUBSTRING(category,1,1) AS Category 
				FROM complaint 
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'
				GROUP BY SUBSTRING(category,1,1) 
				ORDER BY SUBSTRING(category,1,1)";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		while( $fields = mysql_fetch_array($dataset) )
		{
			$cat = $fields["Category"];
			$count = $fields["Rows"];
			if( isset( $categories[$cat] ) )
			{
				$categories[$cat] += $count;
			}
			else
			{
				$categories[$cat] = $count;
			}
		}
		
		return $categories;
	}
}

?>