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
class healthandsafetyGroupLevel extends page
{
	// Graph XML variable
	private $graphXML;

	// Individual chart names.
	private $chartNameLTA = "healthAndSafetyLTAGroupTrendChartSummary";
	private $chartNameAcc = "healthAndSafetyAccGroupTrendChartSummary";
	private $chartNameLTD = "healthAndSafetyLTDGroupTrendChartSummary";
	private $chartNameReportable = "healthAndSafetyReportableGroupTrendChartSummary";
	private $chartNameSafetyOpp = "healthAndSafetySafetyOppGroupTrendChartSummary";

	// Page specifics
	private $monthName = array("Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sept", "Oct", "Nov", "Dec");
	public $colourArray = array(1 => 'AFD8F8','F6BD0F','8BBA00','FF8E46','008E8E','D64646','8E468E','588526','B3AA00','008ED6','9D080D','9999CC');
	private $sitesArr = array("Ashton", "Barcelona", "Dunstable", "Ghislarengo", "Mannheim", "Rorschach", "Valence", "Carlstadt", "Inglewood", "Renfrew", "Syracuse", "Windsor", "Korea");
	private $currentYear;
	private $currentYearMinusOne;
	private $currentMonth;
	private $currentUserGroup;
	private $m = 0;

	// Chart specifics
	private $chartHeight = 300;
	private $exportType;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->hasLib = new hasLib();

		$this->setActivityLocation('Dashboard');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/healthAndSafetyMenu.xml");

		$this->add_output("<healthAndSafetyGroupLevelHome>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard

		if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafetyAdd"))
		{
			$snapins_left->register('apps/dashboard', 'dashboardMainHAS', true, true);		//puts the dashboard load snapin in the page
		}

		$snapins_left->register('apps/dashboard', 'dashboardMainHASGroup', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		/**
		 * If the user has permission show the Health and Safety Dashboard
		 */
		//if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety") && currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_Group"))
		//{
			$this->xml .= "<allowed>1</allowed>";

			// Class Accessed Variables
			$this->currentYear = date("Y"); // Get current year in 2009 format
			$this->currentYearMinusOne = date("Y") - 1; // Get year before current in 2009 format
			$this->currentMonth = date("m"); // Get current month

			// Determine Current Users Details
			$this->add_output("<thisUserGroup>GROUP</thisUserGroup>");

			// Determine Export Type
			if(isset($_GET['exportType']) && $_GET['exportType'] == "client")
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

			// get month list
			$months = array();

			if($this->currentMonth < 12)
			{
				// Display past years data if current year is not equal to 12
				for($i = $this->currentMonth * 1; $i <= 12; $i++)
				{
					$m = $i - 1;
					array_push($months, array($this->monthName[$m], $i));
				}
			}

			for($i = 1; $i < $this->currentMonth; $i++)
			{
				$m = $i - 1;
				array_push($months, array($this->monthName[$m], $i));
			}

			$months = array_reverse($months);

			foreach($months as $month)
			{
				$this->xml .= "<monthOption monthNum=\"" . $month[1] . "\">" . $month[0] . "</monthOption>";
			}

			/**
			 * HealthAndSafetyGroupTrend START
			 * Generate HealthAndSafetyGroupTrend Report
			 */
			$this->xml .= "<healthAndSafetyGroupTrendCharts>";

			// LTA
			$this->generateHealthAndSafetyGroupTrendChart("LTA", "fcExporter1", "lta");
				$this->xml .= "<healthAndSafetyLTAGroupTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTA . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTAGroupTrendChart>";

			// Accidents
			$this->generateHealthAndSafetyGroupTrendChart("Accidents", "fcExporter2", "acc4Days");
				$this->xml .= "<healthAndSafetyAccGroupTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameAcc . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyAccGroupTrendChart>";

			// LTD
			$this->generateHealthAndSafetyGroupTrendChart("LTD", "fcExporter3", "ltd");
				$this->xml .= "<healthAndSafetyLTDGroupTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameLTD . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyLTDGroupTrendChart>";

			// Reportable
			$this->generateHealthAndSafetyGroupTrendChart("Reportable", "fcExporter4", "reportable");
				$this->xml .= "<healthAndSafetyReportableGroupTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameReportable . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetyReportableGroupTrendChart>";

			// Safety Opp
			$this->generateHealthAndSafetyGroupTrendChart("Safety Opp", "fcExporter5", "safetyOpp");
				$this->xml .= "<healthAndSafetySafetyOppGroupTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameSafetyOpp . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</healthAndSafetySafetyOppGroupTrendChart>";

			$this->xml .= "</healthAndSafetyGroupTrendCharts>";
		//}
		//else
		//{
			// Current user does not have permission to view this page.
			//$this->xml .= "<allowed>0</allowed>";
		//}

		// Add $this->xml data to the standard out type.
		$this->add_output($this->xml);

		// Finish adding sections to the page
		$this->add_output("</healthAndSafetyGroupLevelHome>");
		$this->output('./apps/dashboard/xsl/healthandsafetyGroupLevel.xsl');
	}


	/**
	 * Enter description here...
	 *
	 * @param string $nameOfChart (DACR, etc)
	 * @param string $numOfExporter (FCExporter1)
	 * @param string $nameOfField (darc, etc)
	 * @return $this->graphXML (Fusion Chart XML)
	 */
	public function generateHealthAndSafetyGroupTrendChart($nameOfChart, $numOfExporter, $nameOfField)
	{
		$this->graphXML = "";

		$nameOfChart2 = $this->hasLib->getFullChartName($nameOfChart);

		//Create an XML data document in a string variable
		if($this->exportType == "client")
		{
			$this->graphXML .= "&#60;graph caption='" . $nameOfChart2 . "' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='1' exportHandler='" . $numOfExporter . "' registerWithJS='1' exportFileName='GROUPHAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}
		else
		{
			$this->graphXML .= "&#60;graph caption='" . $nameOfChart2 . "' xAxisName='Month' yAxisName='Total' decimalPrecision='2' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='0' exportAction='save' exportHandler='http://scapanet/lib/charts/FCExporter?dashboardAppName=healthandsafety' registerWithJS='1' exportFileName='GROUPHAS" . $nameOfChart . "BarChart' useRoundEdges='1'&#62;";
		}

		$ltaTotal = 0;
		$xValues = array();
		$yValues = array();
		$xPreviousValues = array();

		// Do this if the curernt month is less than 12
		if($this->currentMonth < 12)
		{
			// Display past years data if current year is not equal to 12
			for($i = $this->currentMonth * 1; $i <= 12; $i++)
			{
				$m = $i - 1;

				$toolTip = '';

				foreach ($this->sitesArr as $site)
				{
					$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT " . $nameOfField . " FROM healthandsafety WHERE site = '" . $site . "' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYearMinusOne);

					$fields = mysql_fetch_array($dataset);

					$ltaTotal = $ltaTotal + $fields[$nameOfField];

					if ($fields[$nameOfField] > 0)
					{
						$toolTip .= $site . ': ' . $fields[$nameOfField] . '\n';
					}
				}

				if ($toolTip == '')
				{
					$toolTipDisplay = " showToolTip='0' ";
				}
				else
				{
					$toolTipDisplay = " tooltext='" . $toolTip . "' ";
				}

				if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_GroupAdmin"))
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAddComments?monthToBeAdded=" . $i . "&amp;yearToBeAdded=" . $this->currentYearMinusOne . "' " . $toolTipDisplay . " /&#62;";
				}
				else
				{
					$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' " . $toolTipDisplay . " /&#62;";
				}

				/*  put y axis results in an array to be used for trend line */
				$yValues[] = $ltaTotal;

				/* put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function */
				//$xValues[] = $m + 1;
				$xPreviousValues[] = $m;

				// Reset Values back to 0
				$ltaTotal = 0;
			}
		}

		// Display this years data up to most recent point
		//for($i = 1; $i <= 12; $i++)
		for($i = 1; $i < $this->currentMonth; $i++)
		{
			$m = $i - 1;

			$toolTip = '';

			foreach ($this->sitesArr as $site)
			{
				$dataset = mysql::getInstance()->selectDatabase("dashboards")->Execute("SELECT " . $nameOfField . " FROM healthandsafety WHERE site = '" . $site . "' AND monthToBeAdded = " . $i . " AND yearToBeAdded = " . $this->currentYear);

				$fields = mysql_fetch_array($dataset);

				$ltaTotal = $ltaTotal + $fields[$nameOfField];

				if ($fields[$nameOfField] > 0)
				{
					$toolTip .= $site . ': ' . $fields[$nameOfField] . '\n';
				}
			}

			if ($toolTip == '')
			{
				$toolTipDisplay = " showToolTip='0' ";
			}
			else
			{
				$toolTipDisplay = " tooltext='" . $toolTip . "' ";
			}

			if(currentuser::getInstance()->hasPermission("dashboard_healthAndSafety_GroupAdmin"))
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' link='healthandsafetyAddComments?&amp;monthToBeAdded=" . $i . "&amp;yearToBeAdded=" . $this->currentYear . "' " . $toolTipDisplay . " /&#62;";
			}
			else
			{
				$this->graphXML .= "&#60;set name='" . $this->monthName[$m] ."' value='" . $ltaTotal . "' color='" . $this->colourArray[$i] . "' " . $toolTipDisplay . " /&#62;";
			}

			/*  put y axis results in an array to be used for trend line */
			$yValues[] = $ltaTotal;

			/* put x axis results in an array to be used for trend line we use use +1 so first value 'x' axis is not zero as this confused the trend function */
			$xValues[] = $m + 1;

			// Reset Values back to 0
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
}

?>