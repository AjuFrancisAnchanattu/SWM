<?php

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 19/08/2009
 */
class healthandsafetyRegionLevel extends page
{
	// Graph XML variable
	private $graphXML;

	// Individual chart names.
	private $chartNameLTA = "healthAndSafetyLTARegionTrendChartSummary";
	private $chartNameAcc = "healthAndSafetyAccRegionTrendChartSummary";
	private $chartNameLTD = "healthAndSafetyLTDRegionTrendChartSummary";
	private $chartNameReportable = "healthAndSafetyReportableRegionTrendChartSummary";
	private $chartNameSafetyOpp = "healthAndSafetySafetyOppRegionTrendChartSummary";
	private $chartNameDACR = "healthAndSafetyDACRRegionTrendChartSummary";

	// Page specifics
	private $monthName = array("Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec");
	public $colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	private $currentYear;
	private $currentYearMinusOne;
	private $currentUserRegion;
	private $currentMonth;
	private $m = 0;

	// Chart specifics
	private $chartHeight = 300;
	private $exportType;
	private $allSitesComplete = false;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/healthAndSafetyMenu.xml");

		$this->add_output("<healthAndSafetyRegionLevelHome>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard

		if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
		{
			$snapins_left->register('apps/dashboard', 'dashboardMainHAS', true, true);		//puts the dashboard load snapin in the page
		}

		$snapins_left->register('apps/dashboard', 'dashboardMainHASGroup', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		// Determine what the region is before the page can load with permissions
		if(isset($_REQUEST['region']))
		{
			$this->currentUserRegion = $_REQUEST['region'];
		}
		else
		{
			$this->currentUserRegion = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion();
		}

		// Does the current user have permission to view this dashboard
		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety") && currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_Region" . $this->currentUserRegion . ""))
		//{
			// Set the allowed XSL status to 1 so the page will be displayed
			$this->xml .= "<allowed>1</allowed>";

			// Set class variables
			$this->currentYear = date("Y"); // Get current year in 2009 format
			$this->currentYearMinusOne = date("Y") - 1; // Get year before current in 2009 format
			$this->currentMonth = date("m"); // Get current month in 12 format

			// Display current user region in XSL page
			$this->add_output("<thisUserRegion>" . $this->currentUserRegion . "</thisUserRegion>");

			// If the export type is client set the XSL as client for individual export, otherwise server
			if(isset($_REQUEST['exporttype']) && $_REQUEST['exporttype'] == "client")
			{
				$this->exportType = "client";
				$this->xml .= "<exportType>client</exportType>";
			}
			else
			{
				$this->xml .= "<exportType>server</exportType>";
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

			// Check all sites have updated their details using the current user region
			$this->checkAllSitesComplete($this->currentUserRegion);

		//$this->calculateDACRForRegion("EUROPE");

		//die();

			/**
			 * HealthAndSafetyRegionTrend START
			 * Generate HealthAndSafetyRegionTrend Report
			 */
			$this->xml .= "<healthAndSafetyRegionTrendCharts>";

			// LTA
			$this->generateHealthAndSafetyRegionTrendChart("LTA", "fcExporter1", "lta");
				$this->xml .= "<healthAndSafetyLTARegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTA . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTARegionTrendChart>";

			// Accidents
			$this->generateHealthAndSafetyRegionTrendChart("Accidents", "fcExporter2", "acc4Days");
				$this->xml .= "<healthAndSafetyAccRegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameAcc . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyAccRegionTrendChart>";

			// LTD
			$this->generateHealthAndSafetyRegionTrendChart("LTD", "fcExporter3", "ltd");
				$this->xml .= "<healthAndSafetyLTDRegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTD . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTDRegionTrendChart>";

			// Reportable
			$this->generateHealthAndSafetyRegionTrendChart("Reportable", "fcExporter4", "reportable");
				$this->xml .= "<healthAndSafetyReportableRegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameReportable . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyReportableRegionTrendChart>";

			// Safety Opp
			$this->generateHealthAndSafetyRegionTrendChart("Safety Opp", "fcExporter5", "safetyOpp");
				$this->xml .= "<healthAndSafetySafetyOppRegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameSafetyOpp . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetySafetyOppRegionTrendChart>";

			// DACR
			$this->generateHealthAndSafetyRegionDACRChart($this->currentUserRegion, "DACR", "fcExporter6", "dacr");
				$this->xml .= "<healthAndSafetyDACRRegionTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameDACR . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyDACRRegionTrendChart>";

			$this->xml .= "</healthAndSafetyRegionTrendCharts>";
		//}
		//else
		//{
			// Current user does not have permission to view this page so set XSL allowed status to 0
		//	$this->xml .= "<allowed>0</allowed>";
		//}

		// Add $this->xml data to the standard output type.
		$this->add_output($this->xml);

		// Finish adding sections to the page
		$this->add_output("</healthAndSafetyRegionLevelHome>");
		$this->output('./apps/dashboard/xsl/healthandsafetyRegionLevel.xsl');
	}

	/**
	 * Region Specific Trend Chart
	 *
	 * @param string $nameOfChart (LTA, LTD, etc)
	 * @param string $numOfExporter (FCExporter1, FCExporter2, etc)
	 * @param string $nameOfField (lta, ltd, etc)
	 * @return $this->graphXML (Fusion Chart XML)
	 */
	public function generateHealthAndSafetyRegionTrendChart($nameOfChart, $numOfExporter, $nameOfField)
	{
		// Set graphXML to blank for each iteration of the current function.
		$this->graphXML = "";

		$nameOfChart == 'Accidents' ? $nameOfChart2 = 'Accidents > 4 Days' : $nameOfChart2 = $nameOfChart;

		// Create an XML data document in a string variable and determine if client or server export type
		if($this->exportType == "client")
		{
			$this->graphXML .= "&#60;graph caption='Health and Safety (" . $nameOfChart2 . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $numOfExporter ."' registerWithJS='1' exportFileName='" . $this->currentUserRegion . "HAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}
		else
		{
			$this->graphXML .= "&#60;graph caption='Health and Safety (" . $nameOfChart2 . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='0' exportAction='save' exportHandler='http://scapanetdev/lib/charts/FCExporter?dashboardAppName=healthandsafety' registerWithJS='1' exportFileName='" . $this->currentUserRegion . "HAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}

		// Reset ltaTotal, xValues and yValues back to 0 for each iteration oof the current function
		$ltaTotal = 0;
		$xValues = array();
		$yValues = array();
		$xPreviousValues = array();

		// Do this if the curernt month is less than 12
		if($this->currentMonth < 12)
		{
			for($i = $this->currentMonth + 1; $i <= 12; $i++)
			{
				$m = $i - 1;

				if($this->currentUserRegion == "EUROPE")
				{
					$datasetLTAAshton = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Ashton' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsAshton = mysql_fetch_array($datasetLTAAshton);

					$ltaTotal = $ltaTotal + $fieldsAshton[$nameOfField];

					$datasetLTABarcelona = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Barcelona' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsBarcelona = mysql_fetch_array($datasetLTABarcelona);

					$ltaTotal = $ltaTotal + $fieldsBarcelona[$nameOfField];

					$datasetLTADunstable = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Dunstable' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsDunstable = mysql_fetch_array($datasetLTADunstable);

					$ltaTotal= $ltaTotal + $fieldsDunstable[$nameOfField];

					$datasetLTAGhislarengo = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Ghislarengo' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsGhislarengo = mysql_fetch_array($datasetLTAGhislarengo);

					$ltaTotal = $ltaTotal + $fieldsGhislarengo[$nameOfField];

					$datasetLTAMannheim = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Mannheim' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsMannheim = mysql_fetch_array($datasetLTAMannheim);

					$ltaTotal = $ltaTotal + $fieldsMannheim[$nameOfField];

					$datasetLTARorschach = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Rorschach' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsRorschach = mysql_fetch_array($datasetLTARorschach);

					$ltaTotal = $ltaTotal + $fieldsRorschach[$nameOfField];

					$datasetLTAValence = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Valence' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsValence = mysql_fetch_array($datasetLTAValence);

					$ltaTotal = $ltaTotal + $fieldsValence[$nameOfField];
				}
				elseif($this->currentUserRegion == "NA")
				{
					$datasetLTACarlstadt = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Carlstadt' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsCarlstadt = mysql_fetch_array($datasetLTACarlstadt);

					$ltaTotal = $ltaTotal + $fieldsCarlstadt[$nameOfField];

					$datasetLTAInglewood = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Inglewood' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsInglewood = mysql_fetch_array($datasetLTAInglewood);

					$ltaTotal = $ltaTotal + $fieldsInglewood[$nameOfField];

					$datasetLTARenfrew = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Renfrew' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsRenfrew = mysql_fetch_array($datasetLTARenfrew);

					$ltaTotal = $ltaTotal + $fieldsRenfrew[$nameOfField];

					$datasetLTASyracuse = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Syracuse' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsSyracuse = mysql_fetch_array($datasetLTASyracuse);

					$ltaTotal = $ltaTotal + $fieldsSyracuse[$nameOfField];

					$datasetLTAWindsor = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Windsor' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsWindsor = mysql_fetch_array($datasetLTAWindsor);

					$ltaTotal = $ltaTotal + $fieldsWindsor[$nameOfField];
				}
				elseif($this->currentUserRegion == "ASIA")
				{
					$datasetLTAKorea = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Korea' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);
					$fieldsKorea = mysql_fetch_array($datasetLTAKorea);

					$ltaTotal = $ltaTotal + $fieldsKorea[$nameOfField];
				}
				else
				{
					$ltaTotal = 0;
				}

				// If all sites have been complete set the link on the graph, else do not provide a link.
				if($this->allSitesComplete == true && currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAddComments?region=" . $this->currentUserRegion . "&amp;monthToBeAdded=" . $i . "&amp;yearToBeAdded=" . $this->currentYearMinusOne . "&amp;regionType=" . $this->currentUserRegion . "' /&#62;";
				}
				else
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' /&#62;";
				}

				// Put y axis results in an array to be used for trend line
				$yValues[] = $ltaTotal;

				// Put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function
				//$xValues[] = $m + 1;
				$xPreviousValues[] = $m;

				// Reset ltaTotal back to 0 at end of logic
				$ltaTotal = 0;
			}
		}

		// Do rest of current year
		for($i = 1; $i <= $this->currentMonth; $i++)
		{
			$m = $i - 1;

			if($this->currentUserRegion == "EUROPE")
			{
				$datasetLTAAshton = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Ashton' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsAshton = mysql_fetch_array($datasetLTAAshton);

				$ltaTotal = $ltaTotal + $fieldsAshton[$nameOfField];

				$datasetLTABarcelona = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Barcelona' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsBarcelona = mysql_fetch_array($datasetLTABarcelona);

				$ltaTotal = $ltaTotal + $fieldsBarcelona[$nameOfField];

				$datasetLTADunstable = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Dunstable' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsDunstable = mysql_fetch_array($datasetLTADunstable);

				$ltaTotal= $ltaTotal + $fieldsDunstable[$nameOfField];

				$datasetLTAGhislarengo = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Ghislarengo' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsGhislarengo = mysql_fetch_array($datasetLTAGhislarengo);

				$ltaTotal = $ltaTotal + $fieldsGhislarengo[$nameOfField];

				$datasetLTAMannheim = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Mannheim' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsMannheim = mysql_fetch_array($datasetLTAMannheim);

				$ltaTotal = $ltaTotal + $fieldsMannheim[$nameOfField];

				$datasetLTARorschach = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Rorschach' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsRorschach = mysql_fetch_array($datasetLTARorschach);

				$ltaTotal = $ltaTotal + $fieldsRorschach[$nameOfField];

				$datasetLTAValence = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Valence' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsValence = mysql_fetch_array($datasetLTAValence);

				$ltaTotal = $ltaTotal + $fieldsValence[$nameOfField];
			}
			elseif($this->currentUserRegion == "NA")
			{
				$datasetLTACarlstadt = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Carlstadt' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsCarlstadt = mysql_fetch_array($datasetLTACarlstadt);

				$ltaTotal = $ltaTotal + $fieldsCarlstadt[$nameOfField];

				$datasetLTAInglewood = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Inglewood' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsInglewood = mysql_fetch_array($datasetLTAInglewood);

				$ltaTotal = $ltaTotal + $fieldsInglewood[$nameOfField];

				$datasetLTARenfrew = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Renfrew' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsRenfrew = mysql_fetch_array($datasetLTARenfrew);

				$ltaTotal = $ltaTotal + $fieldsRenfrew[$nameOfField];

				$datasetLTASyracuse = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Syracuse' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsSyracuse = mysql_fetch_array($datasetLTASyracuse);

				$ltaTotal = $ltaTotal + $fieldsSyracuse[$nameOfField];

				$datasetLTAWindsor = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Windsor' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsWindsor = mysql_fetch_array($datasetLTAWindsor);

				$ltaTotal = $ltaTotal + $fieldsWindsor[$nameOfField];
			}
			elseif($this->currentUserRegion == "ASIA")
			{
				$datasetLTAKorea = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT $nameOfField FROM healthandsafety WHERE site = 'Korea' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);
				$fieldsKorea = mysql_fetch_array($datasetLTAKorea);

				$ltaTotal = $ltaTotal + $fieldsKorea[$nameOfField];
			}
			else
			{
				$ltaTotal = 0;
			}

			// If all sites have been complete set the link on the graph, else do not provide a link.
			if($this->allSitesComplete == true && currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAddComments?region=" . $this->currentUserRegion . "&amp;monthToBeAdded=" . $i . "&amp;yearToBeAdded=" . $this->currentYear . "&amp;regionType=" . $this->currentUserRegion . "' /&#62;";
			}
			else
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' /&#62;";
			}

			// Put y axis results in an array to be used for trend line
			$yValues[] = $ltaTotal;

			// Put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function
			$xValues[] = $m + 1;

			// Reset ltaTotal back to 0 after loop.
			$ltaTotal = 0;
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

		return $this->graphXML;
	}

	/**
	 * Generate DACR for regions
	 *
	 * @param string $region
	 * @param string $nameOfChart
	 * @param string $numOfExporter
	 * @param string $nameOfField
	 * @return string $this->xml
	 */
	public function generateHealthAndSafetyRegionDACRChart($region, $nameOfChart, $numOfExporter, $nameOfField)
	{
		// Set graphXML to blank for each iteration of the current function.
		$this->graphXML = "";

		$nameOfChart == 'Accidents' ? $nameOfChart2 = 'Accidents > 4 Days' : $nameOfChart2 = $nameOfChart;

		// Create an XML data document in a string variable and determine if client or server export type
		if($this->exportType == "client")
		{
			$this->graphXML .= "&#60;graph caption='Health and Safety (" . $nameOfChart2 . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $numOfExporter ."' registerWithJS='1' exportFileName='" . $this->currentUserRegion . "HAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}
		else
		{
			$this->graphXML .= "&#60;graph caption='Health and Safety (" . $nameOfChart2 . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='0' exportAction='save' exportHandler='http://scapanetdev/lib/charts/FCExporter?dashboardAppName=healthandsafety' registerWithJS='1' exportFileName='" . $this->currentUserRegion . "HAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}

		if($this->currentMonth == 1)
		{
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT month, dacr FROM healthandsafetyDACR WHERE region = '" . $region ."' AND year = " . $this->currentYearMinusOne . " ORDER BY month ASC");
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT month, dacr FROM healthandsafetyDACR WHERE region = '" . $region ."' AND year = " . $this->currentYear . " ORDER BY month ASC");
		}

		$i = 0;

		while($fields = mysql_fetch_array($dataset))
		{
			// If all sites have been complete set the link on the graph, else do not provide a link.
			if($this->allSitesComplete == true && currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$i] ."' value='" . $fields['dacr'] . "' color='" . $this->colourArray[$fields['month']] . "' link='healthandsafetyAddComments?region=" . $this->currentUserRegion . "&amp;monthToBeAdded=" . $fields['month'] . "&amp;yearToBeAdded=" . $this->currentYear . "&amp;regionType=" . $this->currentUserRegion . "' /&#62;";
			}
			else
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$i] ."' value='" . $fields['dacr'] . "' color='" . $this->colourArray[$fields['month']] . "' /&#62;";
			}

			$i++;
		}

		$this->graphXML .= "&#60;/graph&#62;";

		return $this->graphXML;
	}

	/**
	 * Check if all sites for a region have completed their sections for the required month.
	 * If this is the case set the allSiteComplete to true.
	 *
	 * @param string $region (Europe, Asia, NA)
	 */
	private function checkAllSitesComplete($region)
	{
		// Make Region string all upper case
		$region = strtoupper($region);

		// Run query for current year, month and region.  This will determine what route to go down.
		//$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, region, monthToBeAdded, yearToBeAdded FROM healthandsafety WHERE region = '" . $region ."' AND monthToBeAdded = "  . $this->currentMonth . " AND yearToBeAdded = " . $this->currentYear . " AND authorised = 1");

		if($this->currentMonth == 1)
		{
			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, region, monthToBeAdded, yearToBeAdded FROM healthandsafety WHERE region = '" . $region ."' AND monthToBeAdded = "  . $this->currentMonth . " AND yearToBeAdded = " . $this->currentYearMinusOne . " AND authorised = 1");
		}
		else
		{
			$this->currentMonth = $this->currentMonth - 1;

			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT id, region, monthToBeAdded, yearToBeAdded FROM healthandsafety WHERE region = '" . $region ."' AND monthToBeAdded = 2 AND yearToBeAdded = " . $this->currentYear . " AND authorised = 1");
		}

		if($region == "ASIA")
		{
			if(mysql_num_rows($dataset) == 2)
			{
				$this->allSitesComplete = true;
			}
		}
		elseif($region == "NA")
		{
			if(mysql_num_rows($dataset) == 5)
			{
				$this->allSitesComplete = true;
			}
		}
		else
		{
			if(mysql_num_rows($dataset) == 6)
			{
				$this->allSitesComplete = true;
			}
		}
	}

	private function calculateDACRForRegion($region, $month = 12)
	{
		// Make Region string all upper case
		$region = strtoupper($region);

		// Do previous month
		if($this->currentMonth < 12)
		{
			for($i = $this->currentMonth + 1; $i <= 12; $i++)
			{
				$m = $i - 1;

				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafetyDACR WHERE region = '" . $region . "' AND year = '" . $this->currentYearMinusOne . "' AND month = " . $m . "");

				$fields = mysql_fetch_array($dataset);

				// If all sites have been complete set the link on the graph, else do not provide a link.
				if($this->allSitesComplete == true && currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $fields['dacr'] . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAddComments?region=" . $this->currentUserRegion . "&amp;monthToBeAdded=" . $i . "&amp;yearToBeAdded=" . $this->currentYear . "&amp;regionType=" . $this->currentUserRegion . "' /&#62;";
				}
				else
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $fields['dacr'] . "' color='" . $this->colourArray[$i] . "' /&#62;";
				}


			}
		}

		// Do rest of current year
		for($i = 1; $i <= $this->currentMonth; $i++)
		{
			$m = $i - 1;

			$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT * FROM healthandsafetyDACR WHERE region = '" . $region . "' AND year = '" . $this->currentYearMinusOne . "' AND month = " . $m . "");

			if(mysql_num_rows($dataset) != 0)
			{

			}
		}
	}
}

?>