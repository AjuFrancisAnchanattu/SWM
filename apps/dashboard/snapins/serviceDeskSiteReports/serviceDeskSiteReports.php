<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 09/08/2010
 */
include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDeskSiteReports extends serviceDeskChart
{	
	function __construct($region = 'EUROPE', $exporter = "siteSummaryChart")
	{
		parent::__construct("serviceDesk_site_reports_summary_chart", $exporter, 'Region', $region, "MSColumn2D");
		
		$this->setClass(__CLASS__);
		
		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	}
	
	private function getMonth()
	{
		if(date("m") == 1)
		{
			$month = 12;
		}
		else 
		{
			$month = date("m") - 1;
		}
		
		return $month;
	}
	
	private function getYear()
	{
		if(date("m") == 1)
		{
			$year = date("Y") - 1;
		}
		else 
		{
			$year = date("Y");
		}
		
		return $year;
	}

	
	private function getSQLFromDate()
	{
		$fromDate = $this->getYear() . "-" . $this->getMonth() . "-01 00:00:00";
		
		return $fromDate;
	}
	
	private function getSQLToDate()
	{
		$toDate = $this->getYear() . "-" . $this->getMonth() . "-31 00:00:00";
		
		return $toDate;
	}
	
	private function getTicketCountDataset($fromDate, $toDate, $site, $openClosed)
	{
		$datasetCount = NULL;
		
		$datasetCount = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT id 
									FROM serviceDesk.serviceDesk 
										INNER JOIN membership.employee 
											ON serviceDesk.serviceDesk.owner = membership.employee.NTLogon 
									WHERE serviceDesk.serviceDesk.ticketType !='task' 
									AND membership.employee.site = '" . $site . "' 
									AND serviceDesk.serviceDesk.startDate 
										BETWEEN '" . $fromDate . "' AND '" . $toDate . "' 
									AND serviceDesk.serviceDesk.statusAdmin = " . $openClosed . "");
		
		return mysql_num_rows($datasetCount);
	}
	
	private function getTicketCountDatasetAllOpen($fromDate, $toDate, $site)
	{
		$datasetCount = NULL;
		
		$datasetCount = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT id 
									FROM serviceDesk.serviceDesk 
										INNER JOIN membership.employee 
											ON serviceDesk.serviceDesk.owner = membership.employee.NTLogon 
									WHERE serviceDesk.serviceDesk.ticketType !='task' 
									AND membership.employee.site = '" . $site . "' 
									AND serviceDesk.serviceDesk.startDate 
										BETWEEN '" . $fromDate . "' AND '" . $toDate . "'");
		
		return mysql_num_rows($datasetCount);
	}
	
	private function getTicketCountDatasetOverdue($fromDate, $toDate, $site, $openClosed)
	{
		$datasetCount = NULL;
		
		$datasetCount = mysql::getInstance()->selectDatabase("serviceDesk")
						->Execute("SELECT id 
									FROM serviceDesk.serviceDesk 
										INNER JOIN membership.employee 
											ON serviceDesk.serviceDesk.owner = membership.employee.NTLogon 
									WHERE serviceDesk.serviceDesk.ticketType !='task' 
									AND membership.employee.site = '" . $site . "' 
									AND serviceDesk.serviceDesk.startDate 
										BETWEEN '" . $fromDate . "' AND '" . $toDate . "' 
									AND serviceDesk.serviceDesk.statusAdmin = " . $openClosed . " 
									AND serviceDesk.serviceDesk.dueDate < '" . date("Y-m-d") . "'");
		
		return mysql_num_rows($datasetCount);
	}
	
	
	public function generateChart($region = 'EUROPE')
	{
		if ($region == 'NA')
			$region = 'USA';

		$caption = "Site Report - " . $region . " (" . common::getMonthNameByNumber($this->getMonth()) . ")";
		$fileName = str_ireplace("/","_",$caption);
		
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='" . $caption . "' xAxisName='Site' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")
					->Execute("SELECT name 
								FROM sites 
								WHERE region = '" . $region . "' 
									AND name != 'Carlstadt'
									AND name != 'Iberica'
								ORDER BY name ASC");

		// Show Categories
		$this->graphXML .= "&#60;categories&#62;";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;category label ='" . $fields['name'] . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/categories&#62;";
		
		
		
		// Show Dataset for Open
		$dataset = mysql::getInstance()->selectDatabase("membership")
					->Execute("SELECT name 
								FROM sites 
								WHERE region = '" . $region . "' 
									AND name != 'Carlstadt'
									AND name != 'Iberica'
								ORDER BY name ASC");
		
		$this->graphXML .= "&#60;dataset seriesName ='Open'&#62;";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set name='" . $fields['name'] . "' link='' value='" . $this->getTicketCountDataset($this->getSQLFromDate(), $this->getSQLToDate(), $fields['name'], 0) . "' color='" . $this->colourArray[1] . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/dataset&#62;";
		
		
		
		// Show Dataset for Closed
		$dataset = mysql::getInstance()->selectDatabase("membership")
					->Execute("SELECT name 
								FROM sites 
								WHERE region = '" . $region . "' 
									AND name != 'Carlstadt'
									AND name != 'Iberica'
								ORDER BY name ASC");
		
		$this->graphXML .= "&#60;dataset seriesName ='Closed'&#62;";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set name='" . $fields['name'] . "' link='' value='" . $this->getTicketCountDataset($this->getSQLFromDate(), $this->getSQLToDate(), $fields['name'], 1) . "' color='" . $this->colourArray[2] . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/dataset&#62;";
		
		
		
		// Show Dataset for Opened In Month
		$dataset = mysql::getInstance()->selectDatabase("membership")
					->Execute("SELECT name 
								FROM sites 
								WHERE region = '" . $region . "' 
									AND name != 'Carlstadt'
									AND name != 'Iberica'
								ORDER BY name ASC");
		
		$this->graphXML .= "&#60;dataset seriesName ='Opened In Month'&#62;";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set name='" . $fields['name'] . "' link='' value='" . $this->getTicketCountDatasetAllOpen($this->getSQLFromDate(), $this->getSQLToDate(), $fields['name']) . "' color='" . $this->colourArray[3] . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/dataset&#62;";

		
		
		// Show Dataset for Overdue
		$dataset = mysql::getInstance()->selectDatabase("membership")
					->Execute("SELECT name 
								FROM sites 
								WHERE region = '" . $region . "' 
									AND name != 'Carlstadt'
									AND name != 'Iberica'
								ORDER BY name ASC");
		
		$this->graphXML .= "&#60;dataset seriesName ='Overdue'&#62;";
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set name='" . $fields['name'] . "' link='' value='" . $this->getTicketCountDatasetOverdue($this->getSQLFromDate(), $this->getSQLToDate(), $fields['name'], 0) . "' color='" . $this->colourArray[4] . "' /&#62;";
		}
		
		$this->graphXML .= "&#60;/dataset&#62;";	
		
			
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
		
	}
}

?>