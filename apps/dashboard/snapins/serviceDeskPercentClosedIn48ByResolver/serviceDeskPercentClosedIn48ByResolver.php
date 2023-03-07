<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskPercentClosedIn48ByResolver extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDesk_closed_48_chart";
	private $chartHeight = 300;
	private $colourArray;
	public $today;
	public $dateMinus2;
	private $serviceDeskExport = "serviceDeskExport1";


	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);

		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
		$this->monthNameArray = array(1 => "January","February","March","April","May","June","July","August","September","October","November","December");
	
		$this->today = date("Y-m-d");
		$this->dateMinus2 = $this->nowDateMinusTwoDays();
			
	}
	public function nowDateMinusTwoDays()
    {
        $dateMinus2 = time() - (24 * 60 * 60 * 2);
	
        return date("Y-m-d", $dateMinus2);
    }
	public function output()
	{
		$this->xml .= "<serviceDeskPercentClosedIn48ByResolver>";

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
			$this->generateServiceDeskPercentClosedIn48ByResolver();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</serviceDeskPercentClosedIn48ByResolver>";

		return $this->xml;
	}

	/**
	 * This is the SupportTicketsMonthly report
	 *
	 */
	public function generateServiceDeskPercentClosedIn48ByResolver()
	{
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='Tickets Closed By Resolver Within 48 Hours' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='closed48ByOwner' registerWithJS='1' exportFileName='serviceDeskPercentClosedIn48ByResolver' &#62;";
		//$this->graphXML .= "&#60;graph caption='Tickets by Owner (April)' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='threeMonthlyTickets' registerWithJS='1' exportFileName='serviceDeskPercentClosedIn48ByResolver' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT owner AS name, COUNT(id) AS data FROM serviceDesk WHERE statusAdmin = 1 AND endDate BETWEEN '". $this->dateMinus2 ." 00:00:00' AND '". $this->today ." 00:00:00' GROUP BY owner");
		//$datasetGlob = mysql::getInstance()->selectDatabase("serviceDeskPercentClosedIn48ByResolver")->Execute("SELECT owner AS name, count(id) as data FROM serviceDeskPercentClosedIn48ByResolver WHERE startDate BETWEEN '2010-04-01' AND '2010-04-31' GROUP BY owner");

		$count = 1;

		while($fieldsGlob = mysql_fetch_array($datasetGlob))
		{
			$datasetURL = mysql::getInstance()->selectDatabase("serviceDesk")->Execute("SELECT id FROM bookmarksParent WHERE name = 'tickets_" . $fieldsGlob['name'] . "'");
			$fieldsURL = mysql_fetch_array($datasetURL);
			$this->graphXML .= "&#60;set name='" . strtoupper(substr($fieldsGlob['name'],0,2)) . "' link='' value='" . $fieldsGlob['data'] . "' /&#62;";
			$count ++;
		}


		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;

	}
}

?>