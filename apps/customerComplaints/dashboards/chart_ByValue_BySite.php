<?php

include_once "dashboardsLib.php";

class chart_ByValue_BySite extends dashboardsLib
{
	public function getChartXML($args)
	{
		$period = $this->getFiscalPeriodData($args);
		$startDate = $period["fromDate"];
		$endDate = $period["toDate"];
		$month = $this->months[$period["month"]]["long"];
		$year = $period["year"];
		$sites = $this->getSites();
		
		$xml = "<chart caption='Complaints Value by Site' subcaption='For $month $year' showValues='1' numberPrefix='%A3' decimals='1' formatNumberScale='1'  useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_ByValue_Exporter' registerWithJS='1' exportFileName='ccChart_ValueBySite_$month_$year' >";
		
		$xml_categories = "<categories>";
		
		$xml_total .= "<dataset seriesName='Total (%A3)' >";
		$xml_debited .= "<dataset seriesName='Debited (%A3)' >";
		$xml_credited .= "<dataset seriesName='Credited (%A3)' >";
		
		foreach( $sites as $site )
		{
			$xml_categories .= "<category label='$site' />";
			$xml_categories .= "<vLine color='FF5904' thickness='1' alpha='20'/>";
			
			$value_total = $this->getInvoicesValueForSite_total($site, $period);
			$value_credited = $this->getInvoicesValueForSite_credited($site, $period);
			$value_debited = $this->getInvoicesValueForSite_debited($site, $period);
			
			$display_value = $this->formatMoney($value_credited);
			$tooltext = "%A3$display_value have been credited in $month $year";
			$link = 'JavaScript:chartByValue.chart_ByValue_ByMonth_ForSite("' . $site . '")';
			$xml_credited .= "<set value='$value_credited' toolText='$tooltext' link='$link' />";
			
			$display_value = $this->formatMoney($value_debited);
			$tooltext = "%A3$display_value have been debited in $month $year";
			$link = 'JavaScript:chartByValue.chart_ByValue_ByMonth_ForSite("' . $site . '")';
			$xml_debited .= "<set value='$value_debited' toolText='$tooltext' link='$link' />";
			
			$display_value = $this->formatMoney($value_total);
			$tooltext = "%A3$display_value in $month $year";
			$link = 'JavaScript:chartByValue.chart_ByValue_ByMonth_ForSite("' . $site . '")';
			$xml_total .= "<set value='$value_total' toolText='$tooltext' link='$link' />";
		}
		
		$xml_credited .= "</dataset>";
		$xml_debited .= "</dataset>";
		$xml_total .= "</dataset>";
		
		$xml_categories .= "</categories>";
		
		$xml .= $xml_categories . $xml_credited . $xml_debited . $xml_total;
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	
	private function getInvoicesValueForSite_total($siteName, $period)
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
	
	
	private function getInvoicesValueForSite_credited($siteName, $period)
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
	
	
	private function getInvoicesValueForSite_debited($siteName, $period)
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
				AND openDate >= '$date_from' 
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
	
	private function getSites()
	{
		$sites = array();
		
		//new system
		$sqlSites= "SELECT id, selectionOption 
					FROM selectionOptions 
					WHERE typeId = 2 
					ORDER BY selectionOption ASC";
		$datasetSites = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sqlSites);
		while( $fieldsetSites = mysql_fetch_array($datasetSites) )
		{
			$siteId = $fieldsetSites["id"];
			$siteName = $fieldsetSites["selectionOption"];
			$sites[] = $siteName;
		}
		
		//old system
		$sqlSites= "SELECT DISTINCT(siteAtOrigin) 
					FROM complaint 
					WHERE siteAtOrigin != '' 
					ORDER BY siteAtOrigin ASC";
		$datasetSites = mysql::getInstance()->selectDatabase("complaints")->Execute($sqlSites);
		while( $fieldsetSites = mysql_fetch_array($datasetSites) )
		{
			$siteName = $fieldsetSites["siteAtOrigin"];
			$sites[] = $siteName;
		}
		
		return array_unique( $sites );
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