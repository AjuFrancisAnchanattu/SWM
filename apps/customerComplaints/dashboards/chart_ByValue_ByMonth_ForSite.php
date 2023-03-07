<?php

include_once "dashboardsLib.php";

class chart_ByValue_ByMonth_ForSite extends dashboardsLib
{
	public function getChartXML($args)
	{
		$siteName = $args;
		$xml = "<chart caption='Complaints Value by Month' subcaption='For $siteName' labelDisplay='stagger' showValues='1' numberPrefix='%A3' decimals='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_ByValue_Exporter' registerWithJS='1' exportFileName='ccChart_ByValue_$siteName' >";
		
		$fiscal = $this->getFiscalPeriods();
		
		$xml_categories = "<categories>";
		
		$xml_total = "<axis title='Total' numberPrefix='%A3' axisOnLeft='0' titlePos='RIGHT' numDivLines='8' >";
		$xml_total .= "<dataset seriesName='Total (%A3)' anchorRadius='5' anchorSides='3' >";
		
		$xml_debited = "<axis title='Debited' numberPrefix='%A3' titlePos='LEFT' numDivLines='8' >";
		$xml_debited .= "<dataset seriesName='Debited (%A3)' anchorRadius='5' anchorSides='4' >";
		
		$xml_credited = "<axis title='Credited' numberPrefix='%A3' titlePos='LEFT' numDivLines='8' >";
		$xml_credited .= "<dataset seriesName='Credited (%A3)' anchorRadius='5' >";
		
		foreach( $fiscal as $period )
		{
			$month = $period["month"];
			$month_short = $this->months[$month]["short"];
			$month_long = $this->months[$month]["long"];
			$year = $period["year"];
			
			$xml_categories .= "<category label='$month_short $year' />";
			$xml_categories .= "<vLine color='FF5904' thickness='1' alpha='20'/>";
			
			$value_total = $this->getInvoicesValueForPeriod_total($period, $siteName);
			$value_credited = $this->getInvoicesValueForPeriod_credited($period, $siteName);
			$value_debited = $this->getInvoicesValueForPeriod_debited($period, $siteName);
			
			
			$display_value = $this->formatMoney($value_credited);
			$tooltext = "%A3$display_value have been credited in $month_long $year in $siteName";
			$xml_credited .= "<set value='$value_credited' toolText='$tooltext' />";
			
			$display_value = $this->formatMoney($value_debited);
			$tooltext = "%A3$display_value have been debited in $month_long $year in $siteName";
			$xml_debited .= "<set value='$value_debited' toolText='$tooltext' />";
			
			$display_value = $this->formatMoney($value_total);
			$tooltext = "%A3$display_value in $month_long $year in $siteName";
			$xml_total .= "<set value='$value_total' toolText='$tooltext' />";
		}
		
		$xml_credited .= "</dataset>";
		$xml_credited .= "</axis>";
		
		$xml_debited .= "</dataset>";
		$xml_debited .= "</axis>";
		
		$xml_total .= "</dataset>";
		$xml_total .= "</axis>";
		
		$xml_categories .= "</categories>";
		
		$xml .= $xml_categories . $xml_credited . $xml_debited . $xml_total;
		$xml .= "</chart>";
		
		return $xml;
	}
	
	private function getInvoicesValueForPeriod_total($period, $siteName)
	{
		$value = 0.00;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT IFNULL(SUM(complaintValueGBP),0) AS value
					FROM complaint 
					WHERE submissionDate >= '$date_from' 
					AND submissionDate <= '$date_to' 
					AND submissionDate IS NOT NULL
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$value += (double)$fieldset["value"];
		}
		
		//old system
		$sql = "SELECT IFNULL(SUM(gbpComplaintValue_quantity), 0) AS value 
				FROM complaint 
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$value += (double)$fieldset["value"];
		
		return $value;
	}
	
	private function getInvoicesValueForPeriod_credited($period, $siteName)
	{
		$value = 0.00;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT IFNULL(SUM(complaintValueGBP),0) AS value
					FROM complaint 
					INNER JOIN conclusion
					ON complaint.id = conclusion.complaintId
					WHERE complaint.submissionDate >= '$date_from' 
					AND complaint.submissionDate <= '$date_to' 
					AND complaint.submissionDate IS NOT NULL 
					AND creditNoteRequested = 1 
					AND isCreditOrDebitNote = 1
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$value += (double)$fieldset["value"];
		}
		
		//old system
		$sql = "SELECT IFNULL(SUM(gbpComplaintValue_quantity), 0) AS value 
				FROM complaint 
				INNER JOIN conclusion
				ON complaint.id = conclusion.complaintId
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to' 
				AND creditNoteRequested = 'YES'
				AND financeLevelCreditAuthorised = 'Yes' 
				AND isCreditOrDebitNote = 'credit'
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$value += (double)$fieldset["value"];
		
		return $value;
	}
	
	private function getInvoicesValueForPeriod_debited($period, $siteName)
	{
		$value = 0.00;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT IFNULL(SUM(complaintValueGBP),0) AS value
					FROM complaint 
					INNER JOIN conclusion
					ON complaint.id = conclusion.complaintId
					WHERE complaint.submissionDate >= '$date_from ' 
					AND complaint.submissionDate <= '$date_to' 
					AND complaint.submissionDate IS NOT NULL 
					AND creditNoteRequested = 1 
					AND isCreditOrDebitNote = 0 
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$value += (double)$fieldset["value"];
		}
		
		//old system
		$sql = "SELECT IFNULL(SUM(gbpComplaintValue_quantity), 0) AS value 
				FROM complaint 
				INNER JOIN conclusion
				ON complaint.id = conclusion.complaintId
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from ' 
				AND openDate <= '$date_to' 
				AND creditNoteRequested = 'YES'
				AND financeLevelCreditAuthorised = 'Yes' 
				AND isCreditOrDebitNote = 'debit'
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$value += (double)$fieldset["value"];
		
		return $value;
	}
	
	private function getSiteId($siteName)
	{
		$sqlSites= "SELECT id 
					FROM selectionOptions 
					WHERE typeId = 2 
					AND selectionOption = '$siteName'";
		$datasetSites = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sqlSites);			
		if( $fieldsetSites = mysql_fetch_array($datasetSites) )
		{
			return $fieldsetSites["id"];
		}
		else
		{
			return -1;
		}
	}
}

?>