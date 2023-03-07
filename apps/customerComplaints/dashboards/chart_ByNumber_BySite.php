<?php

include_once "dashboardsLib.php";

class chart_ByNumber_BySite extends dashboardsLib
{
	public function getChartXML($args)
	{
		$period = $this->getFiscalPeriodData($args);
		$startDate = $period["fromDate"];
		$endDate = $period["toDate"];
		$month = $this->months[$period["month"]]["long"];
		$year = $period["year"];
		$sites = $this->getSites();
		
		$xml = "<chart caption='Complaints Number by Site (Customer Complaints)' subcaption='For $month $year' showValues='1' decimals='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_openClosed_Exporter' registerWithJS='1' exportFileName='ccChart_NumberBySite_$month_$year' >";
		
		$xml_categories = "<categories>";
		
		$xml_opened .= "<dataset seriesName='Opened' >";
		$xml_valid .= "<dataset seriesName='Valid' >";
		$xml_remained .= "<dataset seriesName='Remained Open' >";

		foreach( $sites as $site )
		{
			$xml_categories .= "<category label='$site' />";
			$xml_categories .= "<vLine color='FF5904' thickness='1' alpha='20'/>";
			
			$count_opened = $this->getInvoicesCountForPeriod_opened($site, $period);
			$count_remained = $this->getInvoicesCountForPeriod_remained($site, $period);
			$count_valid = $this->getInvoicesCountForPeriod_valid($site, $period);
			
			$link = 'JavaScript:chartByNumber.chart_ByNumber_ByMonth_ForSite("' . $site . '")';
			
			$tooltext = "$count_opened have been opened in $month $year in $site";
			$xml_opened .= "<set value='$count_opened' toolText='$tooltext' link='$link' />";
			
			$tooltext = "$count_valid from $month $year were valid in $site";
			$xml_valid .= "<set value='$count_valid' toolText='$tooltext' link='$link' />";
			
			$tooltext = "$count_remained remained opened in $month $year in $site";
			$xml_remained .= "<set value='$count_remained' toolText='$tooltext' link='$link' />";
		}
		
		$xml_opened .= "</dataset>";
		$xml_remained .= "</dataset>";
		$xml_valid .= "</dataset>";
		
		$xml_categories .= "</categories>";
		
		$xml .= $xml_categories . $xml_opened . $xml_valid . $xml_remained;
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	
	private function getInvoicesCountForPeriod_opened($siteName, $period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT COUNT(id) AS ccCount
					FROM complaint 
					WHERE submissionDate >= '$date_from' 
					AND submissionDate <= '$date_to' 
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$count = (int)$fieldset["ccCount"];
		}
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
	}
	
	
	private function getInvoicesCountForPeriod_remained($siteName, $period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT COUNT(id) AS ccCount
					FROM complaint 
					WHERE submissionDate <= '$date_to' 
					AND (closureDate > '$date_to' 
						OR closureDate IS NULL 
						OR closureDate = ''
						OR closureDate = '0000-00-00')
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$count = (int)$fieldset["ccCount"];
		}
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate <= '$date_to' 
				AND (totalClosureDate > '$date_to' 
					OR totalClosureDate IS NULL 
					OR totalClosureDate = ''
					OR totalClosureDate = '0000-00-00')
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
	}
	
	
	private function getInvoicesCountForPeriod_valid($siteName, $period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		$siteId = $this->getSiteId($siteName);
		if( $siteId != -1 )
		{
			//new system
			$sql = "SELECT COUNT(id) AS ccCount
					FROM complaint 
					JOIN evaluation
					ON complaint.id = evaluation.complaintId
					WHERE complaint.submissionDate >= '$date_from' 
					AND complaint.submissionDate <= '$date_to' 
					AND complaint.submissionDate IS NOT NULL
					AND evaluation.complaintJustified = 1
					AND siteOriginError = $siteId";
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
			$fieldset = mysql_fetch_array($dataset);
			$count = (int)$fieldset["ccCount"];
		}
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				JOIN evaluation 
				ON complaint.id = evaluation.complaintId 
				WHERE typeOfComplaint = 'customer_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'
				AND complaintJustified = 'YES'
				AND siteAtOrigin = '$siteName'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
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