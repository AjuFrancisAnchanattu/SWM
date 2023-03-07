<?php

include_once("./apps/dashboard/lib/has/hasLib.php");

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 19/08/2009
 */
class healthandsafetySiteLevel extends page
{
	private $graphXML;

	private $chartNameLTA = "healthAndSafetyLTASiteTrendChartSummary";
	private $chartNameAcc = "healthAndSafetyAccSiteTrendChartSummary";
	private $chartNameLTD = "healthAndSafetyLTDSiteTrendChartSummary";
	private $chartNameReportable = "healthAndSafetyReportableSiteTrendChartSummary";
	private $chartNameSafetyOpp = "healthAndSafetySafetyOppSiteTrendChartSummary";

	private $monthName = array("Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec");
	private $currentYear;
	private $currentUserSite;
	private $currentYearMinusOne;
	private $currentMonth;
	private $m = 0;

	public $colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	private $chartHeight = 300;

	private $exportType;
	private $currenURL;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->hasLib = new hasLib();

		// get filters
		//$this->add_output($this->hasLib->getFilters());

		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/healthAndSafetyMenu.xml");

		$this->add_output("<healthAndSafetyHome>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard

		if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
		{
			$snapins_left->register('apps/dashboard', 'dashboardMainHAS', true, true);		//puts the dashboard load snapin in the page
		}

		$snapins_left->register('apps/dashboard', 'dashboardMainHASGroup', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		// *************************** PAGE STARTS *****************************************

		if(isset($_REQUEST['site']))
		{
			$this->currentUserSite = $_REQUEST['site'];
		}
		else
		{
			$this->currentUserSite = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite();
		}

		// Does the current user have permission to view this dashboard
		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety") && currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_Site" . $this->currentUserSite . ""))
		//{
			$this->xml .= "<allowed>1</allowed>";

			$this->currentMonth = date("m"); // Get current month
			$this->currentYearMinusOne = date("Y") - 1; // Get year before current in 2009 format
			$this->currentYear = date("Y"); // Get current year in 2009 format

			// Determine Current Users Details for the XSL
			$this->add_output("<thisSite>" . $this->currentUserSite . "</thisSite>");

			// Export Type
			if(isset($_GET['exportType']) && $_GET['exportType'] == "client")
			{
				$this->exportType = "client";
				$this->xml .= "<exportType>client</exportType>";

				// If the output type is client then do not put a current link in the XML
				$this->currentURL = "";
				$this->xml .= "<currentURL>" . $this->currentURL . "</currentURL>";
			}
			else
			{
				$this->xml .= "<exportType>server</exportType>";

				// If the output type is server then put a current link in the XML
				$this->currentURL = $_SERVER['REQUEST_URI'];
				$this->xml .= "<currentURL>" . $this->currentURL . "</currentURL>";
			}

			// If the current month is less than 12 then we are showing 2 years so show e.g. 2008 - 2009.  Else show current year e.g. 2009
			if($this->currentMonth < 12)
			{
				$this->xml .= "<thisYear>" . $this->currentYearMinusOne . " - " . $this->currentYear . "</thisYear>";
			}
			else
			{
				$this->xml .= "<thisYear>" . $this->currentYear . "</thisYear>";
			}

			// Display comments for current user site.
			$this->displayComments();

			/**
			 * HealthAndSafetySiteTrend START
			 * Generate HealthAndSafetySiteTrend report
			 */
			$this->xml .= "<healthAndSafetySiteTrendCharts>";

			// LTA
			$this->generateHealthAndSafetySiteTrendChart("LTA", "fcExporter1", "lta");
				$this->xml .= "<healthAndSafetyLTASiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTA . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTASiteTrendChart>";

			// Accidents
			$this->generateHealthAndSafetySiteTrendChart("Accidents", "fcExporter2", "acc4Days");
				$this->xml .= "<healthAndSafetyAccSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameAcc . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyAccSiteTrendChart>";

			// LTD
			$this->generateHealthAndSafetySiteTrendChart("LTD", "fcExporter3", "ltd");
				$this->xml .= "<healthAndSafetyLTDSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTD . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTDSiteTrendChart>";

			// Reportable
			$this->generateHealthAndSafetySiteTrendChart("Reportable", "fcExporter4", "reportable");
				$this->xml .= "<healthAndSafetyReportableSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameReportable . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyReportableSiteTrendChart>";

			// Safety Opp
			$this->generateHealthAndSafetySiteTrendChart("Safety Opp", "fcExporter5", "safetyOpp");
				$this->xml .= "<healthAndSafetySafetyOppSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameSafetyOpp . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetySafetyOppSiteTrendChart>";

			$this->xml .= "</healthAndSafetySiteTrendCharts>";
		//}
		//else
		//{
			// Current user does not have permission to view this page.
			//$this->xml .= "<allowed>0</allowed>";
		//}

		// Add $this->xml data to the standard out type.
		$this->add_output($this->xml);

		// Finish adding sections to the page
		$this->add_output("</healthAndSafetyHome>");
		$this->output('./apps/dashboard/xsl/healthandsafety.xsl');
	}

	/**
	 * Site Specific Trend Chart
	 *
	 * @return $this->graphXML (Fusion Chart XML)
	 */
	//public function generateHealthAndSafetyLTASiteTrendChart()
	public function generateHealthAndSafetySiteTrendChart($nameOfChart, $numOfExporter, $nameOfField)
	{
		$this->graphXML = "";

		$nameOfChart2 = $this->hasLib->getFullChartName($nameOfChart);

		$xValues = array();
		$yValues = array();
		$xPreviousValues = array();

		// Do this if the curernt month is less than 12
		if($this->currentMonth < 12)
		{
			for($i = $this->currentMonth * 1; $i <= 12; $i++)
			{
				$m = $i - 1;

				$datasetLTA = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, $nameOfField FROM healthandsafety WHERE site = '" . $this->currentUserSite ."' AND yearToBeAdded = " . $this->currentYearMinusOne ." AND monthToBeAdded = " . $i . "");
				$fields = mysql_fetch_array($datasetLTA);

				if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $fields[$nameOfField] . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAdd?mode=edit&amp;id=" . $fields['id'] . "' /&#62;";
				}
				else
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $fields[$nameOfField] . "' color='" . $this->colourArray[$i] . "' /&#62;";
				}

				/*  put y axis results in an array to be used for trend line */
				$yValues[] = $fields[$nameOfField];

				/* put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function */
				//$xValues[] = $m + 1;
				$xPreviousValues[] = $m;
			}
		}

		for($i = 1; $i < $this->currentMonth; $i++)
		{
			$m = $i - 1;

			$datasetLTA = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, $nameOfField FROM healthandsafety WHERE site = '" . $this->currentUserSite ."' AND yearToBeAdded = " . $this->currentYear ." AND monthToBeAdded = " . $i . "");
			$fields = mysql_fetch_array($datasetLTA);

			$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $fields[$nameOfField] . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAdd?mode=edit&amp;id=" . $fields['id'] . "' /&#62;";

			/*  put y axis results in an array to be used for trend line */
			$yValues[] = $fields[$nameOfField];

			/* put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function */
			$xValues[] = $m + 1;
		}

		// Reverse the previous year figures to show in reverse order
		$xPreviousValueReverse = array_reverse($xPreviousValues);

		// Add this to the end of the array to fit in with the xValues array
		foreach ($xPreviousValueReverse as $xPVR)
		{
			$xValues[] = $xPVR;
		}

		// due to the counter counting down instead of up, we have to reverse the xAxis array so that the trend function works correctly
		$rev_xValues = array_reverse($xValues);

		//send x & y values to trend function - returns  m = slope, b = y intercept
		$trend = calculateTrend::linear_regression($rev_xValues, $yValues);

		// Calculate Trend Start
		$trendStart = ($trend['m'] * $rev_xValues[0]) + $trend['b'];

		// Calculate Trend End
		$trendEnd = ($trend['m'] * end($rev_xValues)) + $trend['b'];

		// If the Trend End is less than 0 show 0
		if($trendEnd < 0)
		{
			$trendEnd = 0;
		}

		// Generate Trend Line
		$this->graphXML .= "&#60;trendLines&#62;";
		    $this->graphXML .= "&#60;line startValue='" . $trendStart . "' endValue='" . $trendEnd . "' color='999999' thickness='3' displayvalue='" . $nameOfChart . " Trend' valueOnRight ='1' /&#62;";
		$this->graphXML .= "&#60;/trendLines&#62;";

		$this->graphXML .= "&#60;/graph&#62;";

		$uniqueValues = array_unique($yValues);

		$valuesRange = max($uniqueValues) - min($uniqueValues);

		if ($valuesRange < 5)
		{
			$range = " numDivlines='" . ($valuesRange - 1) . "' yaxismaxvalue='" . max($uniqueValues) . "' ";
		}
		else
		{
			$range = "";
		}

		//Create an XML data document in a string variable
		if($this->exportType == "client")
		{
			$exportType = " exportAtClient='1' exportHandler='" . $numOfExporter . "' ";
		}
		else
		{
			$exportType = " exportAtClient='0' exportAction='save' exportHandler='http://scapanet/lib/charts/FCExporter?dashboardAppName=healthandsafety' ";
		}

		$this->graphXML = "&#60;graph caption='" . $nameOfChart2 . "' " . $range . " xAxisName='Month' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' registerWithJS='1' " . $exportType . " exportFileName='" . $this->currentUserSite . "HAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;" . $this->graphXML;

		return $this->graphXML;
	}

	/**
	 * Display Site Specific Comments
	 *
	 * @return $this->xml
	 */
	public function displayComments()
	{
		$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT description, initiator, dateAdded, monthToBeAdded, yearToBeAdded FROM healthandsafety WHERE site = '" . $this->currentUserSite . "' ORDER BY id DESC LIMIT 12");

		$this->xml .= "<healthAndSafetyComments>";

		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<healthAndSafetyComment>";
				$this->xml .= "<comment>" . page::formatAsParagraphs($fields['description']) . "</comment>";
				$this->xml .= "<initiator>" . usercache::getInstance()->get($fields['initiator'])->getName() . "</initiator>";
				$this->xml .= "<dateAdded>" . common::transformDateTimeForPHP($fields['dateAdded']) . "</dateAdded>";
				$this->xml .= "<monthToBeAdded>" . common::getMonthNameByNumber($fields['monthToBeAdded']) . "</monthToBeAdded>";
				$this->xml .= "<yearToBeAdded>" . $fields['yearToBeAdded'] . "</yearToBeAdded>";
			$this->xml .= "</healthAndSafetyComment>";
		}

		$this->xml .= "</healthAndSafetyComments>";

		return $this->xml;
	}
}

?>