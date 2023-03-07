<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 02/08/2010
 */

include_once "apps/dashboard/lib/serviceDesk/serviceDeskChart.php";

class serviceDesk3MonthsReports extends serviceDeskChart
{
	function __construct($controllsDefVal = "1", $exporter = "threeMonthsExporter")
	{
		parent::__construct("serviceDesk_3_months_summary_chart", $exporter, 'Id', $controllsDefVal, "Column2D");
		
		$this->setClass(__CLASS__);
		
		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');

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
	
	protected function prepareId()
	{
		$this->listId[1] = 'By Owner';
		$this->listId[2] = 'By Severity';
		$this->listId[3] = 'By Site';
		$this->listId[4] = 'By S2';
	}

	protected function showId($def = NULL)
	{
		$this->xml .= "<radioControll var='chartId' def='1'/>";
		//values for radio-buttons
		for($i=1; $i<=4; $i++)
		{
			$this->xml .= "<radio val='" . $i . "' disp='" . $this->listId[$i] . "'/>";
		}
	}
	
	/**
	 * @param Integer $chartNo - describing which chart to generate:
	 * 			1-	generateSupportTicketsMonthlyCharts
	 * 			2-	generateSupportTicketsSeverityCharts
	 * 			3-	generateSupportTicketsBySite
	 * 			4-	generateSupportTicketsByS2
	 * 			By default set to 1
	 */
	public function generateChart($chartNo=1)
	{
		$this->graphXML = "";
		
		switch($chartNo)
		{
			case 1:
				$this->generateSupportTicketsMonthlyCharts();
				break;
			case 2:
				$this->generateSupportTicketsSeverityCharts();
				break;
			case 3:
				$this->generateSupportTicketsBySite();
				break;
			case 4:
				$this->generateSupportTicketsByS2();
				break;
		}
		
		return $this->graphXML;
	}
	
	/**
	 * This is the SupportTicketsMonthly report
	 */
	public function generateSupportTicketsMonthlyCharts()
	{
		$fileName = 'Tickets by Owner Over Past 3 Months';
		$this->graphXML .= "&#60;graph caption='Tickets by Owner Over Past 3 Months' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")
				->Execute("SELECT owner AS name, count(id) as data 
						FROM serviceDesk 
						WHERE startDate BETWEEN '" . $this->yearFrom . "-" . $this->monthFrom . "-" . date("d") . "' AND '" . date("Y") . "-" . date("m") . "-31' 
						AND ticketType !='task' 
						GROUP BY owner");

		$count = 1;

		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{

			$datasetURL = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT id FROM bookmarksParent WHERE name = 'tickets_" . $fieldsGlob['name'] . "'");
			$fieldsURL = mysql_fetch_array($datasetURL);
			
			$toolText = usercache::getInstance()->get($fieldsGlob['name'])->getName() ." - " . $fieldsGlob['data'] . " tickets";
			
			
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) 
							. "' link='./searchBookmarks?action=bookmark&amp;bookmarkId=" . $fieldsURL['id'] 
							. "' value='" . $fieldsGlob['data'] 
							. "' toolText='" . $toolText . "'/&#62;";
			
			$count ++;
		}

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
	
	/**
	 * This is the SupportTicketsSeverity report
	 */	
	public function generateSupportTicketsSeverityCharts()
	{
		$fileName = 'Tickets By Severity Over Past 3 Months';
		
		$this->graphXML .= "&#60;graph caption='Tickets By Severity Over Past 3 Months' xAxisName='Severity' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT priority AS name, count(id) as data FROM serviceDesk WHERE ticketType !='task' AND startDate BETWEEN '" . $this->yearFrom . "-" . $this->monthFrom . "-" . date("d") . "' AND '" . date("Y") . "-" . date("m") . "-31' GROUP BY priority");
		
		$count = 1;
		
		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$datasetURL = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT id FROM bookmarksParent WHERE name = 'tickets_" . $fieldsGlob['name'] . "'");
			$fieldsURL = mysql_fetch_array($datasetURL);
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) . "' link='./searchBookmarks?action=bookmark&amp;bookmarkId=" . $fieldsURL['id'] . "' value='" . $fieldsGlob['data'] . "' color='" . $this->colourArray[$count] . "' /&#62;";
			
			$count ++;
		}
		
		$this->graphXML .= "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
	
	/**
	 * This is the SupportTicketsSeverity report
	 */
	public function generateSupportTicketsBySite()
	{
		$fileName = 'Tickets By Site Over Past 3 Months';
		
		$this->graphXML .= "&#60;graph caption='Tickets By Site Over Past 3 Months' xAxisName='Site' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";

		$dataset = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT DISTINCT(membership.employee.site) as name, count(serviceDesk.serviceDesk.id) as value FROM membership.employee INNER JOIN serviceDesk.serviceDesk ON membership.employee.NTLogon = serviceDesk.serviceDesk.owner WHERE serviceDesk.serviceDesk.ticketType !='task' AND serviceDesk.serviceDesk.startDate BETWEEN '" . $this->yearFrom . "-" . $this->monthFrom . "-" . date("d") . "' AND '" . date("Y") . "-" . date("m") . "-31' GROUP BY membership.employee.site");

		$count = 1;

		while($fields = mysql_fetch_array($dataset))
		{
			$this->graphXML .= "&#60;set name='" . $fields['name'] . "' link='' value='" . $fields['value'] . "' color='" . $this->colourArray[$count] . "' /&#62;";

			$count ++;
		}

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}
	
	/**
	 * This is the SupportTicketsMonthly report
	 */	
	public function generateSupportTicketsByS2()
	{
		$fileName = 'Tickets by S2 Over Past 3 Months';
		
		$this->graphXML .= "&#60;graph caption='Tickets by S2 Over Past 3 Months' xAxisName='S2' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $this->chartExporter . "' registerWithJS='1' exportFileName='" . $fileName . "' &#62;";
		
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