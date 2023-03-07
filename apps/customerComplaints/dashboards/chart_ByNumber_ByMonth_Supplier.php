<?php

include_once "dashboardsLib.php";

class chart_ByNumber_ByMonth_Supplier extends dashboardsLib
{
	public function getChartXML()
	{
		$xml = "<chart caption='Complaints Number by Month (Supplier Complaints)' labelDisplay='stagger' showValues='1' decimals='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='ccChart_openClosed_Exporter' registerWithJS='1' exportFileName='ccChart_ByNumber_ByMonth_Customer' >";
			
		$fiscal = $this->getFiscalPeriods();
		
		$xml_categories = "<categories>";
		
		$xml_opened = "<axis title='Opened' titlePos='LEFT' numDivLines='8' >";
		$xml_opened .= "<dataset seriesName='Opened' anchorRadius='5' anchorSides='3' >";
		
		$xml_remained = "<axis title='Remained Open' axisOnLeft='0' titlePos='RIGHT' numDivLines='8' >";
		$xml_remained .= "<dataset seriesName='Remained Open' anchorRadius='5' anchorSides='4' >";

		$xml_valid = "<axis title='Valid' titlePos='LEFT' numDivLines='8' >";
		$xml_valid .= "<dataset seriesName='Valid' anchorRadius='5' >";
		
		foreach( $fiscal as $period )
		{
			$month = $period["month"];
			$month_short = $this->months[$month]["short"];
			$month_long = $this->months[$month]["long"];
			$year = $period["year"];
			$linkPeriod = $period["period"];
			
			$xml_categories .= "<category label='$month_short $year' />";
			$xml_categories .= "<vLine color='FF5904' thickness='1' alpha='20'/>";
			
			$count_opened = $this->getInvoicesCountForPeriod_opened($period);
			$count_remained = $this->getInvoicesCountForPeriod_remained($period);
			$count_valid = $this->getInvoicesCountForPeriod_valid($period);
			
			$tooltext = "$count_opened have been opened in $month_long $year";
			$xml_opened .= "<set value='$count_opened' toolText='$tooltext' />";
			
			$tooltext = "$count_remained remained opened in $month_long $year";
			$xml_remained .= "<set value='$count_remained' toolText='$tooltext' />";
			
			$tooltext = "$count_valid from $month_long $year were valid";
			$xml_valid .= "<set value='$count_valid' toolText='$tooltext' />";
		}
		
		$xml_opened .= "</dataset>";
		$xml_opened .= "</axis>";
		
		$xml_remained .= "</dataset>";
		$xml_remained .= "</axis>";
		
		$xml_valid .= "</dataset>";
		$xml_valid .= "</axis>";
		
		$xml_categories .= "</categories>";
		
		$xml .= $xml_categories . $xml_opened . $xml_valid . $xml_remained;
		
		$xml .= "</chart>";
		
		return $xml;
	}
	
	
	private function getInvoicesCountForPeriod_opened($period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		//new system
		/*
		$sql = "SELECT COUNT(id) AS ccCount
				FROM complaint 
				WHERE submissionDate >= '$date_from' 
				AND closureDate <= '$date_to' 
				AND submissionDate IS NOT NULL";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count = (int)$fieldset["ccCount"];
		*/
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				WHERE typeOfComplaint = 'supplier_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
	}
	
	
	private function getInvoicesCountForPeriod_remained($period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		//new system
		/*
		$sql = "SELECT COUNT(id) AS ccCount
				FROM complaint 
				WHERE submissionDate <= '$date_to' 
				AND (closureDate > '$date_to' 
					OR closureDate IS NULL 
					OR closureDate = ''
					OR closureDate = '0000-00-00')";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count = (int)$fieldset["ccCount"];
		*/
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				WHERE typeOfComplaint = 'supplier_complaint'
				AND openDate <= '$date_to' 
				AND (totalClosureDate > '$date_to' 
					OR totalClosureDate IS NULL 
					OR totalClosureDate = ''
					OR totalClosureDate = '0000-00-00')";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
	}
	
	
	private function getInvoicesCountForPeriod_valid($period)
	{
		$count = 0;
		$date_from = $period["fromDate"];
		$date_to = $period["toDate"];
		
		//new system
		/*
		$sql = "SELECT COUNT(id) AS ccCount
				FROM complaint 
				JOIN evaluation
				ON complaint.id = evaluation.complaintId
				WHERE complaint.submissionDate >= '$date_from' 
				AND complaint.submissionDate <= '$date_to' 
				AND complaint.submissionDate IS NOT NULL
				AND evaluation.complaintJustified = 1";
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count = (int)$fieldset["ccCount"];
		*/
		
		//old system
		$sql = "SELECT COUNT(id) AS ccCount 
				FROM complaint 
				JOIN evaluation 
				ON complaint.id = evaluation.complaintId 
				WHERE typeOfComplaint = 'supplier_complaint'
				AND openDate >= '$date_from' 
				AND openDate <= '$date_to'
				AND complaintJustified = 'YES'";
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);		
		$fieldset = mysql_fetch_array($dataset);
		$count += (int)$fieldset["ccCount"];
		
		return $count;
	}
}

?>