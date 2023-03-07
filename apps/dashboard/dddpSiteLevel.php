<?php

require("snapins/zOverdueN/zOverdueN.php");

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 19/08/2009
 */
class dddpSiteLevel extends page
{
	public $graphXML;
	
	private $chartName = "dddpSiteTrendChartSummary";
	private $chartNameZoverduen = "zoverduenSiteTrendChartSummary";
	
	private $monthNumber;
	private $currentYear;
	private $currentUserRegion;
	private $currentUserSite;
	private $currentUserSiteAbr;
	private $siteAbrFactored = "";
	
	private $chartHeight = 400;
	
	private $exportType;
	private $currentURL;
	private $total = array();
	private $total2 = array();
	
	private $startArray = array();
	private $uniqueArray = array();
	private $baseQuery = "";
	private $endQuery = "";
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Dashboard DDDP');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/dddpMenu.xml");
		
		$this->add_output("<dddpHome>");
		
		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		$snapins_left->register('apps/dashboard', 'dashboardMainDDDPGroup', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		// ***** PAGE STARTS *****
		
		// Set all the global variables for the page
		$this->setDDDPGlobalVariables();
		
		// Does the current user have permission to view this dashboard
		if(currentuser::getInstance()->hasPermission("dashboard_dddp") && currentuser::getInstance()->hasPermission("dashboard_dddp_Site" . $this->currentUserSite . ""))
		{			
			// Current user has permission to view this page.
			$this->xml .= "<allowed>1</allowed>";
			
			// Determine Current Users Details for XSL Output
			$this->add_output("<thisSite>" . $this->currentUserSite . "</thisSite>");
			$this->add_output("<thisYear>" . $this->currentYear . "</thisYear>");
			$this->add_output("<thisUserRegion>" . $this->currentUserRegion . "</thisUserRegion>");
			
			// Determine Export Type
			if(isset($_REQUEST['exporttype']) && $_REQUEST['exporttype'] == "client")
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
			
			/**
			 * DDDP Filters Data
			 * The end query used by the page is $this->endQuery
			 */
			$this->xml .= "<dddpFiltersList>";
			
			$this->getDistinctFilters("customerGroup", "LIKE");
				$this->endQuery = $this->baseQuery;
			$this->getDistinctFilters("orderType");
				$this->endQuery .= $this->baseQuery;
			$this->getDistinctFilters("mrpController");
				$this->endQuery .= $this->baseQuery;
			
			//echo "END QUERY: " . $this->endQuery;
			
			$this->xml .= "</dddpFiltersList>";
			
			/**
			 * DDDP START
			 * Generate DDDPSiteTrend report
			 */
			$this->xml .= "<dddpSiteTrendCharts>";
			
			// DDDP Chart 1
			$this->generateDDDPSiteTrendChart();
				$this->xml .= "<dddpSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->graphXML . "</graphChartData>";
				$this->xml .= "</dddpSiteTrendChart>";
				
			$this->xml .= "</dddpSiteTrendCharts>";
			
			/**
			 * ZOVERDUEN START
			 * Generate ZOVERDUEN Report
			 */
			$this->xml .= "<zoverduenSiteTrendCharts>";
			
			// ZOVERDUEN Chart 1
			$this->zoverduen = new zOverdueN();
				$this->xml .= "<zoverduenSiteTrendChart>";
					$this->xml .= "<chartName>" . $this->chartNameZoverduen . "</chartName>";
					$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
					$this->xml .= "<graphChartData>" . $this->zoverduen->generateZOverdueNCharts("GB10") . "</graphChartData>";
				$this->xml .= "</zoverduenSiteTrendChart>";
				
			$this->xml .= "</zoverduenSiteTrendCharts>";
		}
		else 
		{
			// Current user does not have permission to view this page.
			$this->xml .= "<allowed>0</allowed>";	
		}

		// Add $this->xml data to the standard out type.
		$this->add_output($this->xml);
		
		// Finish adding sections to the page
		$this->add_output("</dddpHome>");
		$this->output('./apps/dashboard/xsl/dddp.xsl');
	}
	
	/**
	 * Site Specific Trend Chart
	 *
	 * @return $this->graphXML (Fusion Chart XML)
	 */
	public function generateDDDPSiteTrendChart()
	{	
		$this->graphXML = "";
			
		// ******************************** DAY NUMBER ********************************
		for($arrDataCounter = 0; $arrDataCounter <= 30; $arrDataCounter ++)
		{
			$arrData[$arrDataCounter][1] = $arrDataCounter + 1;
		}
		
		// ******************************** CLIP ********************************
		$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentYear . "-" . $this->monthNumber . "-01' AND '" . $this->currentYear . "-" . $this->monthNumber . "-31' AND mg4 IN ('" . $this->currentUserSiteAbr . "','" . $this->siteAbrFactored . "') " . $this->endQuery);
		
		$CLIPval = 1;
		
		while($CLIPval <= 31)
		{			
			$CLIPisReached = 0;
			
			$CLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM dddp WHERE `actualDate` = '" . $this->currentYear . "-" . $this->monthNumber . "-" . $CLIPval . "' AND mg4 IN ('" . $this->currentUserSiteAbr . "','" . $this->siteAbrFactored . "') " . $this->endQuery);
			
			$CLIPnumOfDDDPForDay = mysql_num_rows($CLIPdataset);
			
			while($CLIPfields = mysql_fetch_array($CLIPdataset))
			{
				if($this->datediff($CLIPfields['actualDate'], $CLIPfields['agreedDate']) <= 7 && $this->datediff($CLIPfields['actualDate'], $CLIPfields['agreedDate']) >= 0)
				{
					$CLIPisReached ++;
				}
			}
			
			if($CLIPnumOfDDDPForDay == 0)
			{
				$this->total[$CLIPval] = $CLIPisReached / 1 * 100;
			}
			else 
			{
				$this->total[$CLIPval] = $CLIPisReached / $CLIPnumOfDDDPForDay * 100;
			}
			
			$CLIPval ++;
		}
		
		// Put CLIP information into the array
		$arrDataForTotal = 0;
		for($arrDataCounter2 = 0; $arrDataCounter2 <= 30; $arrDataCounter2 ++)
		{		
			$arrDataForTotal = $arrDataCounter2 + 1; 
			
			$arrData[$arrDataCounter2][2] = $this->total[$arrDataForTotal] == 0 ? '' : $this->total[$arrDataForTotal];
		}
		
		// ******************************** RLIP ********************************
		$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM dddp WHERE `actualDate` BETWEEN '" . $this->currentYear . "-" . $this->monthNumber . "-01' AND '" . $this->currentYear . "-" . $this->monthNumber . "-31' AND mg4 IN ('" . $this->currentUserSiteAbr . "','" . $this->siteAbrFactored . "') " . $this->endQuery);
		
		$RLIPval = 1;
			
		while($RLIPval <= 31)
		{			
			$RLIPisReached = 0;
			
			$RLIPdataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM dddp WHERE `actualDate` = '" . $this->currentYear . "-" . $this->monthNumber . "-" . $RLIPval . "' AND mg4 IN ('" . $this->currentUserSiteAbr . "','" . $this->siteAbrFactored . "') " . $this->endQuery);
			
			$RLIPnumOfDDDPForDay = mysql_num_rows($RLIPdataset);
			
			while($RLIPfields = mysql_fetch_array($RLIPdataset))
			{
				if($this->datediff($RLIPfields['actualDate'], $RLIPfields['requiredDate']) <= 7 && $this->datediff($RLIPfields['actualDate'], $RLIPfields['requiredDate']) >= 0)
				{
					$RLIPisReached ++;
				}
			}
			
			if($RLIPnumOfDDDPForDay == 0)
			{
				$this->total2[$RLIPval] = $RLIPisReached / 1 * 100;
			}
			else 
			{
				$this->total2[$RLIPval] = $RLIPisReached / $RLIPnumOfDDDPForDay * 100;
			}
			
			$RLIPval ++;
		}
		
		// Put RLIP information into the array
		$arrDataForTotal = 0;
		for($arrDataCounter3 = 0; $arrDataCounter3 <= 30; $arrDataCounter3 ++)
		{		
			$arrDataForTotal = $arrDataCounter3 + 1; 
			
			$arrData[$arrDataCounter3][3] = $this->total2[$arrDataForTotal] == 0 ? '' : $this->total2[$arrDataForTotal];
		}
		
		// ******************************** Initialize <graph> element ********************************
				
		//Create an XML data document in a string variable
		if($this->exportType == "server")
		{
			$this->graphXML = "&#60;graph caption='DDDP (" . $this->currentUserSiteAbr . " / " . $this->siteAbrFactored . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='0' exportAction='save' exportHandler='http://scapanetdev/lib/charts/FCExporter?' registerWithJS='1' exportFileName='" . $this->currentUserSite . "DDDPBarChart' useRoundEdges='1'&#62;";		
		}
		else 
		{
			$this->graphXML = "&#60;graph caption='DDDP (" . $this->currentUserSiteAbr . " / " . $this->siteAbrFactored . ")' xAxisName='Month' yAxisName='Total' decimalPrecision='0' formatNumberScale='0' showvalues='1' rotateNames='1' showLegend='1' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter1' registerWithJS='1' exportFileName='" . $this->currentUserSite . "DDDPBarChart' useRoundEdges='1'&#62;";
		}
		
		// Initialize <categories> element - necessary to generate a multi-series chart
		$strCategories = "&#60;categories&#62;";
		
		// Initiate <dataset> elements
		$strDataCLIP = "&#60;dataset seriesName='CLIP' color='AFD8F8' &#62;";
		$strDataRLIP = "&#60;dataset seriesName='RLIP' color='F6BD0F' &#62;";
		
		// Iterate through the data 
		foreach ($arrData as $arSubData) 
		{	
		  // Append <category name='...' /> to strCategories
		  $strCategories .= "&#60;category name='" . $arSubData[1] . "' /&#62;";
		  
		  // Add <set value='...' /> to both the datasets
		  $strDataCLIP .= "&#60;set value='" . $arSubData[2] . "' link='" . $this->currentUserSite . "' /&#62;";
		  $strDataRLIP .= "&#60;set value='" . $arSubData[3] . "' link='" . $this->currentUserSite . "' /&#62;";
		}
		
		// Close <categories> element
		$strCategories .= "&#60;/categories&#62;";
		
		// Close <dataset> elements
		$strDataCLIP .= "&#60;/dataset&#62;";
		$strDataRLIP .= "&#60;/dataset&#62;";
		
		// Assemble the entire XML now
		$this->graphXML .= $strCategories . $strDataCLIP . $strDataRLIP . "&#60;/graph&#62;";
		
		return $this->graphXML;
	}
	
	/**
	 * Get the difference in days between 2 dates
	 *
	 * @param string $datefrom (2010-01-01, etc)
	 * @param string $dateto (2010-01-31, etc)
	 * @return int $datediff (1,2,3,4,5,etc)
	 */
	public function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
		
		$difference = $dateto - $datefrom; // Difference in seconds
		
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		
		if ($odd_days > 7)
		{
			$days_remainder;
		}
		
		if ($odd_days > 6) 
		{ 
			$days_remainder;
		}
		
		$datediff = ($weeks_difference * 5) + $days_remainder;
		
		return $datediff;
	}
	
	/**
	 * Get Site Abreviation from Full Site Name
	 *
	 * @param string $site (Ashton, Dunstable, etc)
	 * @return string $this->siteAbr (ASH, DUN, etc)
	 */
	public function getSiteAbr($site)
	{
		switch ($site)
		{
			// EUROPE
			case 'Ashton':
				
				$this->siteAbr = "ASH";
				$this->siteAbrFactored = "ASX";
				
				break;
				
			case 'Dunstable':
				
				$this->siteAbr = "DUN";
				$this->siteAbrFactored = "DUX";
				
				break;
				
			case 'Barcelona':
				
				$this->siteAbr = "BAR";
				$this->siteAbrFactored = "BAX";
				
				break;
				
			case 'Valence':
				
				$this->siteAbr = "VAL";
				$this->siteAbrFactored = "VAX";
				
				break;
				
			case 'Mannheim':
				
				$this->siteAbr = "MAN";
				$this->siteAbrFactored = "MAX";
				
				break;
				
			case 'Ghislarengo':
				
				$this->siteAbr = "GHI";
				
				break;
				
			case 'Rorschach':
				
				$this->siteAbr = "ROR";
				
				break;
				
			// NA
			case 'Windsor':
				
				$this->siteAbr = "WIN";
				
				break;
				
			case 'Inglewood':
				
				$this->siteAbr = "CAL";
				$this->siteAbrFactored = "INX";
				
				break;
				
			case 'Carlstadt':
				
				$this->siteAbr = "CAR";
				
				break;
				
			case 'Renfrew':
				
				$this->siteAbr = "REN";
				
				break;
				
			case 'Syracuse':
				
				$this->siteAbr = "SEF";
				
				break;
			
			// DEFAULT
			default:
				
				break;
		}
		
		return $this->siteAbr;
	}
	
	/**
	 * Get the Distinct Filter values for the checkbox filters
	 *
	 * @param string $fieldName (customerGroup, orderType, etc)
	 * @param string $sqlType ("", LIKE, etc)
	 */
	private function getDistinctFilters($fieldName, $sqlType = "")
	{		
		// Reset all variables back to their normal state before continuing with the method.
		$this->startArray = array(); // Contains all array data
		$this->uniqueArray = array(); // Contains on the unique array data
		$value = array(); // Contains the value of the uniqueArray array
		$ess = array(); // Contains the index value of the $_POST array
		$andOr = 0; // Shows whether the AND or OR declaration should be created
		$this->baseQuery = ""; // Contains a string for the SQL query each time the method is called.
		
		// Get a list of the distinct values for the value $fieldName in the dddp table
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(" . $fieldName . ") FROM dddp WHERE " . $fieldName . " IS NOT NULL AND mg4 IN ('" . $this->currentUserSiteAbr . "','" . $this->siteAbrFactored . "') ORDER BY " . $fieldName . " ASC");
		
		if(mysql_num_rows($dataset) > 0)
		{
			$i = 0; 
			
			switch ($fieldName)
			{
				// If the field name is customerGroup put the first character only in the startArray
				case 'customerGroup':
					
					while($fields = mysql_fetch_array($dataset))
					{						
						$this->startArray[$i] = substr($fields[$fieldName], 0, 1);
						
						$i++;
					}
					
					break;
				
				// Otherwise but the whole value into the startArray
				default:
					
					while($fields = mysql_fetch_array($dataset))
					{						
						$this->startArray[$i] = $fields[$fieldName];
						
						$i++;
					}
					
					break;
			}
			
			// Find the unique values in an array and put them in uniqueArray
			$this->uniqueArray = array_unique($this->startArray);
		}
		
		// For each of the unique values in the array determine whether the field is to be checked or not
		foreach($this->uniqueArray as $value)
		{	
			if(isset($_POST[$value]) && $_POST[$value] == "on")
			{
				$this->xml .= "<uniqueDistinct" . $fieldName . "><uniqueValue>" . $value . "</uniqueValue><checked>true</checked></uniqueDistinct" . $fieldName . ">";	
			}
			else 
			{
				$this->xml .= "<uniqueDistinct" . $fieldName . "><uniqueValue>" . $value . "</uniqueValue><checked>false</checked></uniqueDistinct" . $fieldName . ">";	
			}
		}
		
		// If the Run button is clicked carry out the following
		if(isset($_POST["action"]) && $_POST["action"] == "Run")
		{	
			switch($sqlType)
			{
				
				/**
				 * If the query type is LIKE get the index value from the $_POST variable.
				 * If the index value is within the uniqueArray carry out the sql statement.
				 */
				case 'LIKE':
					
					$andOr = 0;
					
					foreach(array_keys($_POST) as $ess)
					{																					
						if(in_array($ess, $this->uniqueArray))
						{							
							if($andOr == 0)
							{
								$this->baseQuery .= " AND " . $fieldName . " LIKE '" . $ess . "%'";
							}
							else 
							{
								$this->baseQuery .= " OR " . $fieldName . " LIKE '" . $ess . "%'";
							}
							
							$andOr++;
						}
					}
					
					break;
				
				/**
				 * If the query type is default get the index value from the $_POST variable.
				 * If the index value is within the uniqueArray carry out the sql statement.
				 */
				default:
					
					$andOr = 0;
					
					foreach(array_keys($_POST) as $ess)
					{															
						if(in_array($ess, $this->uniqueArray))
						{
							if($andOr == 0)
							{
								$this->baseQuery .= " AND " . $fieldName . " = '" . $ess . "'";	
							}
							else 
							{
								$this->baseQuery .= " OR " . $fieldName . " = '" . $ess . "'";
							}
							
							$andOr++;
						}
					}
					
					break;
			}
		}
		else 
		{
			$_POST = array();
		}
	}
	
	/**
	 * Set the DDDP Global Variables
	 * These include: currentUserSite, currentUserSiteAbr, currentUserRegion, monthNumber, currentYear
	 * These values are from the $_REQUEST otherwise the current users profile
	 */
	private function setDDDPGlobalVariables()
	{
		if(isset($_REQUEST['site']))
		{
			$this->currentUserSite = $_REQUEST['site'];
			$this->currentUserSiteAbr = $this->getSiteAbr($_REQUEST['site']);
		}
		else
		{
			$this->currentUserSite = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite();
			$this->currentUserSiteAbr = $this->getSiteAbr(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());
		}
		
		if(isset($_REQUEST['region']))
		{
			$this->currentUserRegion = $_REQUEST['region'];
		}
		else
		{
			$this->currentUserRegion = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion();
		}
		
		if(isset($_REQUEST['month']))
		{			
			if(isset($_REQUEST['month']))
			{
				$this->monthNumber = $_REQUEST['month'];
			}
		}
		else 
		{
			$this->monthNumber = date("m");
		}
		
		if(isset($_REQUEST['year']))
		{
			$this->currentYear = $_REQUEST['year'];
		}
		else
		{
			$this->currentYear = date("Y");
		}
	}
	
	/**
	 * Serialse and save the DDDP Filters
	 *
	 */
	private function saveDDDPCustomSearch()
	{
		$i = 0;
		
		foreach($this->uniqueArray as $value)
		{	
			if(isset($_POST[$value]))
			{
				$this->dddpFilters[$i] = $value;
			}
			
			$i++;
		}
		
		$serialsedDDDPFilters = serialize($this->dddpFilters);
		
		//mysql::getInstance()->selectDatabase("dashboards")->Execute("INSERT INTO dddpSavedReports (NTLogon,savedDate,data) VALUES ('" . currentuser::getInstance()->getNTLogon() . "','" . common::nowDateTimeForMysql() . "','" . $serialsedDDDPFilters . "')");
	}
}

?>