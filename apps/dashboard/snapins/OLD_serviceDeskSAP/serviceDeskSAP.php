<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/08/2009
 */
class serviceDeskSAP extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */

	public $graphXML = "";
	private $salesOrganisation;
	private $chartName = "serviceDesk_sap_owner_month_chart";
	private $chartHeight = 300;
	private $colourArray;
	private $currentYear;
	private $lastMonth;
	private $daysInMonth;
	private $serviceDeskExport = "serviceDeskExport1";


	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->chartName));
		$this->setClass(__CLASS__);
		$this->setCanClose(true);

		// global array: sets the colors for the graph columns.
		$this->colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');

		//Leap years array 
		$leapYears = array(2012,2016,2020,2024,2028,2032,2036,2040);
		//Month Name array
		$this->monthNameArray = array(1 => "January","February","March","April","May","June","July","August","September","October","November","December");
		$this->currentYear = date("Y");
		$this->lastMonth = date("n") - 1;
		$this->daysInMonth = 0;
		
		//If the month is January then subtract 1 from the year to make it the previous year
		//This is because we want tickets from the previous month
		if(date("n") == 1)
		{
			$currentYear = $currentYear - 1;
		}
		
		//This code works out how many days are in each month, it also uses the leap year array to work out the correct days in Febuary
		if($this->lastMonth == 4 || $this->lastMonth == 6 || $this->lastMonth == 9 || $this->lastMonth == 11)
			{
				$this->daysInMonth = 30;
			}
			elseif($this->lastMonth == 2) 
			{
				$this->daysInMonth = 28;

				foreach($leapYears as $leapYear)
				{
					if($this->currentYear == $leapYear)
					{
						$this->daysInMonth = 29;
					}
				}				
			}
			else
			{
				$this->daysInMonth = 31;
			}
	}

	public function output()
	{
		$this->xml .= "<serviceDeskSAP>";

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
			$this->generateServiceDeskTicketsPerPersonMonthSAP();
				$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</serviceDeskSAP>";

		return $this->xml;
	}

	/**
	 * This is the SupportTicketsMonthly report
	 *
	 */
	public function generateServiceDeskTicketsPerPersonMonthSAP()
	{
		// this line shows the captions, and holds formatting information.
		$this->graphXML .= "&#60;graph caption='SAP Team Tickets Last Month (". $this->monthNameArray[$this->lastMonth] .") By Owner' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='ticketsPerMonthByOwnerSAP' registerWithJS='1' exportFileName='ticketsPerMonthByOwnerSAP' &#62;";
		//$this->graphXML .= "&#60;graph caption='Tickets by Owner (April)' xAxisName='Owner' yAxisName='Tickets' decimalPrecision='0' formatNumberScale='0' showLegend='1' useRoundEdges='1' rotateNames='1' exportEnabled='1' exportAtClient='1' exportHandler='threeMonthlyTickets' registerWithJS='1' exportFileName='serviceDeskSAP' &#62;";

		$datasetGlob = mysql::getInstance()->selectDatabase("serviceDesk")->
					Execute("SELECT owner AS name, count(id) as data 
							FROM serviceDesk 
							WHERE ticketType !='task' 
							AND s1= 'SAP' 
							AND startDate BETWEEN '" . $this->currentYear . "-" . $this->lastMonth . "-1' 
							AND '" .$this->currentYear . "-" . $this->lastMonth . "-" .$this->daysInMonth ."' 
							GROUP BY owner");

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