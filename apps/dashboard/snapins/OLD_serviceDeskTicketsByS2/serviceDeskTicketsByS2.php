<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskTicketsByS2 extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	
	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDeskTicketsByS2_summary_chart";
	private $chartHeight = 300;
	private $colourArray;
	private $yearFrom;
	private $monthFrom;
	
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);
		
		if(date("m") < 3)
		{
			$this->yearFrom = date("Y") - 1;
			
			date("m") == 1 ? $this->monthFrom = 11 : "";
			date("m") == 2 ? $this->monthFrom = 12 : "";
			date("m") == 3 ? $this->monthFrom = 1 : "";
		}
		else 
		{
			$this->yearFrom = date("Y");
			$this->monthFrom = date("m") - 3;
		}
	}
	
	public function output()
	{				
		$this->xml .= "<serviceDeskTicketsByS2>";
		
		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->xml .= "<allowed>1</allowed>";
			
			/**
			 * SupportTicketsByS2 START
			 * Generate SupportTicketsMonthly report
			 */
			$this->generateSupportTicketsByS2();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else 
		{
			$this->xml .= "<allowed>0</allowed>";	
		}
			
		$this->xml .= "</serviceDeskTicketsByS2>";
		
		return $this->xml;
	}
	
	/**
	 * This is the SupportTicketsMonthly report
	 *
	 */	
	public function generateSupportTicketsByS2()
	{
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='Tickets by S2 over past 3 months' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ticketsByS2' registerWithJS='1' exportFileName='ticketsByS2' &#62;";
		
		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT(s2) AS name, (SELECT count(s2) FROM serviceDesk WHERE ticketType !='task' AND s2 = name) AS data FROM serviceDesk WHERE startDate BETWEEN '" . $this->yearFrom . "-" . $this->monthFrom . "-" . date("d") . "' AND '" . date("Y") . "-" . date("m") . "-31' GROUP BY s2");
		
		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$this->graphXML .= "&#60;set name='" . substr($fieldsGlob['name'],0,5) . "...' value='" . $fieldsGlob['data'] . "' toolText='" . $fieldsGlob['name'] . "' /&#62;";
		}
		
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
		
	}
}

?>