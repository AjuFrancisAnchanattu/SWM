<?php

class dashboards
{
	private static $months = array(	1 => array("short" => "Jan", "long" => "January"),
									2 => array("short" => "Feb", "long" => "February"),
									3 => array("short" => "Mar", "long" => "March"),
									4 => array("short" => "Apr", "long" => "April"),
									5 => array("short" => "May", "long" => "May"),
									6 => array("short" => "Jun", "long" => "June"),
									7 => array("short" => "Jul", "long" => "July"),
									8 => array("short" => "Aug", "long" => "August"),
									9 => array("short" => "Sep", "long" => "September"),
									10 => array("short" => "Oct", "long" => "October"),
									11 => array("short" => "Nov", "long" => "November"),
									12 => array("short" => "Dec", "long" => "December")
								);
	
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
			case "ccChart_ByValue_drilldown":
				echo self::getChart_ByValue_DRILLDOWN_XML($args);
				break;
				
			case "ccChart_ByValue":
				echo self::getChart_ByValue_XML();
				break;
				
			case "ccChart_OpenClosed_customer":
				echo self::getChart_OpenClosed_CUSTOMER_XML();
				break;
				
			case "ccChart_OpenClosed_supplier":
				echo self::getChart_OpenClosed_SUPPLIER_XML();
				break;
		}
	}
	
	private static function getMonths()
	{
		$mArray = array();
		
		$curY = intval(date("Y"));
		$curM = intval(date("n"));
		
		$start = $curM + 1;
		for( $i= $start; $i<= 12; $i++)
		{
			$mArray[] = array("m" => $i, "y" => $curY - 1);
		}
		for( $i= 1; $i<= $curM; $i++)
		{
			$mArray[] = array("m" => $i, "y" => $curY);
		}
		
		return $mArray;
	}
	
	private static function getFiscalPeriods()
	{
		$fiscal = array();
		$todaysDate = date("Y-m-d");
		
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE fromDate <= '$todaysDate' 
			ORDER BY fromDate DESC
			LIMIT 0, 12";
			
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			$fiscal[] = array(	"period" => $fieldset["period"], 
								"fromDate" => $fieldset["fromDate"], 
								"toDate" => $fieldset["toDate"],
								"month" => self::getMonthNumberFromFiscalPeriod($fieldset["period"]),
								"year" => self::getYearNumberFromFiscalPeriod($fieldset["period"]));
		}
		
		return array_reverse($fiscal);
	}
	
	private static function getFiscalPeriodData($period)
	{
		$todaysDate = date("Y-m-d");
		
		$sql = "SELECT * 
			FROM fiscalCalendar 
			WHERE period = '$period'";
			
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		
		$fieldset = mysql_fetch_array($dataset);
		
		return array(	"period" => $fieldset["period"], 
						"fromDate" => $fieldset["fromDate"], 
						"toDate" => $fieldset["toDate"],
						"month" => self::getMonthNumberFromFiscalPeriod($fieldset["period"]),
						"year" => self::getYearNumberFromFiscalPeriod($fieldset["period"]) );
	}
	
	private static function getFiscalStartDate($period)
	{
		$sql = "SELECT fromDate 
				FROM fiscalCalendar 
				WHERE period = $period";
				
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($sql);
		$fieldset = mysql_fetch_array($dataset);
		return $fieldset["fromDate"];
	}
	
	private static function getMonthNumberFromFiscalPeriod($period)
	{
		$period = (int)(substr($period, -2));
		$period += 3;
		if ($period > 12) $period -= 12;
		
		return $period;
	}
	
	private static function getYearNumberFromFiscalPeriod($period)
	{
		return (int)(substr($period, 0, 4));
	}
	
	private static function getChart_OpenClosed_CUSTOMER_XML()
	{
		$fiscal = self::getFiscalPeriods();
		
		$xml = "<chart caption='Opened/Closed Complaints (Customer)' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_openClosed_Exporter' registerWithJS='1' exportFileName='getChart_OpenClosed_CUSTOMER' >";
						
		$xml .= "<categories>";
		foreach( $fiscal as $period )
		{
			$xml .= "<category label='" . self::$months[$period["month"]]["short"] . " " . $period["year"] . "' />";
		}
		$xml .= "</categories>";
		
		$datasets = array( "Opened" => array("new" => "submissionDate", "old" => "openDate"),
						   "Closed" => array("new" => "closureDate", "old" => "totalClosureDate") );
		
		foreach( $datasets AS $display => $dbField )
		{
			$xml .= "<dataset seriesName='" . $display . "'>";
			foreach( $fiscal as $period )
			{
				//new system
				$sql = "SELECT COUNT(id) AS ccCount
						FROM complaint 
						WHERE " . $dbField["new"] . " >= '" . $period["fromDate"] . "' 
						AND " . $dbField["new"] . " <= '" . $period["toDate"] . "'";
						
				$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
				$fieldset = mysql_fetch_array($dataset);
				$ccCount = (int)$fieldset["ccCount"];
				
				//old system
				$sql = "SELECT COUNT(id) AS ccCount 
						FROM complaint 
						WHERE typeOfComplaint = 'customer_complaint'
						AND " . $dbField["old"] . " >= '" . $period["fromDate"] . "' 
						AND " . $dbField["old"] . " <= '" . $period["toDate"] . "'";
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
				$fieldset = mysql_fetch_array($dataset);
				$ccCount += (int)$fieldset["ccCount"];		
				
				$tooltext = $ccCount . " customer complaints have been " . strtolower($display) . " in " . self::$months[$period["month"]]["long"] . " " . $period["year"];
				
				$xml .= "<set value='" . $ccCount . "' toolText='" . $tooltext . "' />";
			}
			$xml .= "</dataset>";
		}
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	private static function getChart_OpenClosed_SUPPLIER_XML()
	{
		$xml = "<chart caption='Opened/Closed Complaints (Supplier)' xAxisName='Month' yAxisName='Quantity' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_openClosed_Exporter' registerWithJS='1' exportFileName='ccChart_openClosed_SUPPLIER' >";
						
		$xml .= "<categories>";
		$fiscal = self::getFiscalPeriods();
		foreach( $fiscal as $period )
		{
			$xml .= "<category label='" . self::$months[$period["month"]]["short"] . " " . $period["year"] . "' />";
		}
		$xml .= "</categories>";
		
		$datasets = array( "Opened" => array("new" => "submissionDate", "old" => "openDate"),
						   "Closed" => array("new" => "closureDate", "old" => "totalClosureDate") );
		
		foreach( $datasets AS $display => $dbField )
		{
			$xml .= "<dataset seriesName='" . $display . "'>";
			foreach( $fiscal as $period )
			{
				//old system
				$sql = "SELECT COUNT(id) AS ccCount 
						FROM complaint 
						WHERE typeOfComplaint = 'supplier_complaint'
						AND " . $dbField["old"] . " >= '" . $period["fromDate"] . "' 
						AND " . $dbField["old"] . " <= '" . $period["toDate"] . "'";
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
				$fieldset = mysql_fetch_array($dataset);
				$ccCount = (int)$fieldset["ccCount"];		
				
				$tooltext = $ccCount . " supplier complaints have been " . strtolower($display) . " in " . self::$months[$period["month"]]["long"] . " " . $period["year"];
				
				$xml .= "<set value='" . $ccCount . "' toolText='" . $tooltext . "' />";
			}
			$xml .= "</dataset>";
		}
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	private static function getChart_ByValue_XML()
	{
		$xml = "<chart caption='Complaints Value by Month' xAxisName='Month' yAxisNAme='Value (GBP)' decimalPrecision='0' formatNumberScale='0' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_ByValue_Exporter' registerWithJS='1' exportFileName='ccChart_ByValue' >";
		
		$fiscal = self::getFiscalPeriods();
			
		foreach( $fiscal as $period )
		{
			$value = 0.00;
			//new system
			$sql = "SELECT IFNULL(SUM(complaintValueGBP),0) AS value
					FROM complaint 
					WHERE submissionDate >= '" . $period["fromDate"] . "' 
					AND submissionDate <= '" . $period["toDate"] . "' 
					AND submissionDate IS NOT NULL";
					
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$value += (double)$fieldset["value"];
			
			//old system
			$sql = "SELECT IFNULL(SUM(gbpComplaintValue_quantity), 0) AS value 
					FROM complaint 
					WHERE typeOfComplaint = 'customer_complaint'
					AND openDate >= '" . $period["fromDate"] . "' 
					AND openDate <= '" . $period["toDate"] . "'";
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$value += (double)$fieldset["value"];		
			
			$tooltext = "Customer complaints for total of " . $value . " GBP have been opened in " . self::$months[$period["month"]]["long"] . " " . $period["year"];
			$label = self::$months[$period["month"]]["short"] . " " . $period["year"];
			$link = 'JavaScript:display_ccChart_ByValue_drilldown("' . $period["period"] . '")';
			
			$xml .= "<set label='$label' value='$value' toolText='$tooltext' link='$link' />";
		}
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	private static function getChart_ByValue_DRILLDOWN_XML($args)
	{
		$period = self::getFiscalPeriodData($args);
		
		$startDate = $period["fromDate"];
		$endDate = $period["toDate"];
		
		$caption = "Complaints Value by Site";
		$subcaption = "For "  . self::$months[$period["month"]]["long"] . " " . $period["year"];
		$fileName = "ccChart_By_Site_Value_" . $period["month"] . "_" . $period["year"];
		
		$xml = "<chart caption='$caption' subcaption='$subcaption' xAxisName='Site' yAxisNAme='Value (GBP)'  decimalPrecision='0' formatNumberScale='0' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_ByValue_Exporter' registerWithJS='1' exportFileName='$fileName' >";
		
		$values = array();
		
		//new system
		$sqlSites = "SELECT id, selectionOption 
				FROM selectionOptions 
				WHERE typeId = 2 
				ORDER BY selectionOption ASC";
		$datasetSites = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sqlSites);
		
		while( $fieldsetSites = mysql_fetch_array($datasetSites) )
		{
			$siteId = $fieldsetSites["id"];
			$siteName = $fieldsetSites["selectionOption"];
			
			$sql = "SELECT IFNULL(SUM(complaintValueGBP),0) AS value
					FROM complaint 
					WHERE submissionDate >= '" . $period["fromDate"] . "' 
					AND submissionDate <= '" . $period["toDate"] . "' 
					AND siteOriginError = $siteId";
					
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$values[$siteName] = (double)$fieldset["value"];
		}
		
		//old system
		$sqlSites = "SELECT DISTINCT(siteAtOrigin) 
				FROM complaint 
				ORDER BY siteAtOrigin ASC";
		$datasetSites = mysql::getInstance()->selectDatabase("complaints")->Execute($sqlSites);
		
		while( $fieldsetSites = mysql_fetch_array($datasetSites) )
		{
			$siteName = $fieldsetSites["siteAtOrigin"];
			
			$sql = "SELECT IFNULL(SUM(gbpComplaintValue_quantity), 0) AS value 
					FROM complaint 
					WHERE typeOfComplaint = 'customer_complaint'
					AND openDate >= '" . $period["fromDate"] . "' 
					AND openDate <= '" . $period["toDate"] . "' 
					AND siteAtOrigin = '$siteName'";
					
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			
			if( isset( $values[$siteName] ) )
			{
				$values[$siteName] += (double)$fieldset["value"];
			}
			else
			{
				$values[$siteName] = (double)$fieldset["value"];
			}
		}
		
		foreach( $values as $site => $value )
		{
			if( $value > 0 )
			{
				$tooltext = "Customer complaints for total of " . $value . " GBP have been opened in " . self::$months[$period["month"]]["long"] . " " . $period["year"] . " in $site";
				$xml .= "<set label='$site' value='$value' toolText='$tooltext' />";
			}
		}
		
		$xml .= "</chart>";
		
		return $xml;
	}
}

?>