<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDesk extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDesk_summary_chart";
	private $chartHeight = 300;
	private $colourArray;
	private $yearFrom;
	private $monthFrom;
	private $serviceDeskExport = "serviceDeskExport1";


	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);

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

	public function output()
	{
		$this->xml .= "<serviceDesk>";

		// Format Chart with Height and Name
		$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
		$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_serviceDesk"))
		{
			$this->xml .= "<allowed>1</allowed>";

			/**
			 * SupportTicketsMonthly START
			 * Generate SupportTicketsMonthly report
			 */
			$this->generateSupportTicketsMonthlyCharts();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</serviceDesk>";

		return $this->xml;
	}

	/**
	 * This is the SupportTicketsMonthly report
	 *
	 */
	public function generateSupportTicketsMonthlyCharts()
	{
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='Tickets by Owner over past 3 months' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='threeMonthlyTickets' registerWithJS='1' exportFileName='ticketsByOwner' &#62;";
		//$this->graphXML .= "&#60;graph caption='Tickets by Owner (April)' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='threeMonthlyTickets' registerWithJS='1' exportFileName='serviceDesk' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")
				->Execute("SELECT owner AS name, count(id) as data 
						FROM serviceDesk 
						WHERE startDate BETWEEN '" . $this->yearFrom . "-" . $this->monthFrom . "-" . date("d") . "' AND '" . date("Y") . "-" . date("m") . "-31' 
						AND ticketType !='task' 
						GROUP BY owner");
		//$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT owner AS name, count(id) as data FROM serviceDesk WHERE startDate BETWEEN '2010-04-01' AND '2010-04-31' GROUP BY owner");

		$count = 1;

		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{

			$datasetURL = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT id FROM bookmarksParent WHERE name = 'tickets_" . $fieldsGlob['name'] . "'");
			$fieldsURL = mysql_fetch_array($datasetURL);
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) . "' link='./searchBookmarks?action=bookmark&amp;bookmarkId=" . $fieldsURL['id'] . "' value='" . $fieldsGlob['data'] . "' /&#62;";
			$count ++;
		}


		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;

	}
}

?>