<?php

include_once("./apps/dashboard/lib/dddp/dddpLib.php");
include("snapins/dddp/dddp.php");
include("snapins/dddpYTD/dddpYTD.php");
include("snapins/dddpAll/dddpAll.php");
include("snapins/dddpAllYTD/dddpAllYTD.php");

/**
 *
 * @package apps
 * @subpackage dashboard
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 26/02/2010
 */
class dddpDrillDown extends page
{
	// Declare Variables
	private $chartName = "dddp_summary";
	private $chartHeight = 500;
	private $thisYear;
	private $monthNumber;
	private $sqlFromDate;
	private $sqlToDate;

	private $clipArray = array();
	private $rlipArray = array();
	private $shippingPointsArray = array();
	private $totalLineItems = array();
	private $totalRLIPLineItems = array();
	private $totalRLIPLinesMissed = array();
	private $totalLineItemsYTD = array();
	private $totalRLIPLineItemsYTD = array();
	private $totalRLIPLinesMissedYTD = array();
	private $rlipDefaultDatePercentage = array();
	private $totalRLIPDefaultDateItems = array();
	private $totalRLIP = array();
	private $dddpChart;
	public $dddpLib;

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('CLIP/RLIP');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/dddpMenu.xml");

		$this->add_output("<dddpHome>");

		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		//$snapins_left->register('apps/dashboard', 'dashboardMainDDDPGroup', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->dddpLib = new dddpLib();

		// Get Filters
		$this->getFilters();

		// Display DDDP Chart
		$this->displayDDDPChart();

		// Display Filters
		$this->displayFilters();

		$this->add_output($this->xml);

		// Finish adding sections to the page
		$this->add_output("</dddpHome>");
		$this->output('./apps/dashboard/xsl/dddp.xsl');
	}

	private function getFilters()
	{
		// Determine whether to display monthly or yearly data
		$chartFormatArr = array("MTD", "YTD");

		if(isset($_POST['chartFormat']))
		{
			if (!in_array($_POST['chartFormat'], $chartFormatArr))
			{
				die("no chart format is set");
			}
			else
			{
				$chartFormat = $_POST['chartFormat'];
			}
		}
		else
		{
			if(isset($_GET['chartFormat']))
			{
				if (!in_array($_GET['chartFormat'], $chartFormatArr))
				{
					die("no chart format is set");
				}
				else
				{
					$chartFormat = $_GET['chartFormat'];
				}
			}
			else
			{
				$chartFormat = 'MTD';
			}
		}

		if ($chartFormat == 'YTD')
		{
			$this->dddpChart = (isset($_REQUEST['CLIPandRLIP'])) ? new dddpAllYTD() : new dddpYTD();

			$this->showYTDDataOnTable();
		}
		else
		{
			$this->dddpChart = (isset($_REQUEST['CLIPandRLIP'])) ? new dddpAll() : new dddp();

			$this->showMTDDataOnTable();
		}

		$this->xml .= '<chartFormat>' . $chartFormat . '</chartFormat>';


		if(isset($_POST['businessUnit']))
		{
			$this->xml .= "<buToDisplay> (BU: " . $_POST['businessUnit'] . ")</buToDisplay>";

			if ($_POST['businessUnit'] != 'ALL')
			{
				$this->xml .= '<businessUnit>' . $_POST['businessUnit'] . '</businessUnit>';
			}
		}
		elseif(isset($_GET['businessUnit']))
		{
			$this->xml .= "<buToDisplay> (BU: " . $_GET['businessUnit'] . ")</buToDisplay>";

			if ($_GET['businessUnit'] != 'ALL')
			{
				$this->xml .= '<businessUnit>' . $_GET['businessUnit'] . '</businessUnit>';
			}
		}
		else
		{
			$this->xml .= "<buToDisplay> (BU: All)</buToDisplay>";
		}

		if(isset($_REQUEST['site']))
		{
			if(isset($_POST['CLIPandRLIP']))
			{
				$this->xml .= "<siteToDisplay>GROUP</siteToDisplay>";
			}
			else
			{
				$this->xml .= "<siteToDisplay>" . $_REQUEST['site'] . "</siteToDisplay>";
			}
		}
		else
		{
			if(isset($_POST['CLIPandRLIP']))
			{
				$this->xml .= "<siteToDisplay>GROUP</siteToDisplay>";
			}
			else
			{
				$this->xml .= "<siteToDisplay>" . $this->dddpChart->dddpLib->shippingPointName . "</siteToDisplay>";
			}
		}
	}

	/**
	 * Get the English Ordinal (th, st, rd) from a timestamp (MM, DD, YYYY)
	 *
	 * @param int $month
	 * @param int $day
	 * @param int $year
	 * @return string
	 */
	private function getEnglishOrdinalFromTimestamp($month, $day, $year)
	{
		if($month == 2)
		{
			if($day == 29 || $day = 30)
			{
				return "th";
			}
			elseif($day == 31)
			{
				return "st";
			}
			else
			{
				return date("S", mktime(0, 0, 0, $month, $day, $year));
			}
		}
		else
		{
			return date("S", mktime(0, 0, 0, $month, $day, $year));
		}
	}

	private function showMTDDataOnTable()
	{
		if(date("d") == 1)
		{
			$this->sqlFromDate = $this->dddpChart->previousMonthCountToYearNo . "-" . $this->dddpChart->previousMonthCountToMonthNo . "-" . $this->dddpChart->previousMonthCountToDayNo;
			$this->sqlToDate = $this->dddpChart->currentMonthCountToYearNo . "-" . $this->dddpChart->currentMonthCountToMonthNo . "-" . $this->dddpChart->currentMonthCountToDayNo;
		}
		else
		{
			$this->sqlFromDate = $this->dddpChart->previousMonthCountToYearNo . "-" . $this->dddpChart->previousMonthCountToMonthNo . "-" . $this->dddpChart->previousMonthCountToDayNo;
			$this->sqlToDate = $this->dddpChart->currentMonthCountToYearNo . "-" . $this->dddpChart->currentMonthCountToMonthNo . "-" . $this->dddpChart->currentMonthCountToDayNo;
		}

		//echo $this->dddpChart->previousMonthCountToYearNo . " - " . $this->dddpChart->currentMonthCountToMonthNo . " - " . $this->dddpChart->previousMonthCountToDayNo;

		//if($this->dddpChart->currentMonthCountToDayNo == 1)
		if(date("d") == 1)
		{
			$this->monthToDisplayInChart = common::getMonthNameByNumber($this->dddpChart->currentMonthCountToMonthNo);
			//$this->monthToDisplayInChart = common::getMonthNameByNumber($this->dddpChart->previousMonthCountToMonthNo);
		}
		else
		{
			$this->monthToDisplayInChart = $this->dddpChart->previousMonthCountToDayNo . $this->getEnglishOrdinalFromTimestamp($this->dddpChart->previousMonthCountToMonthNo, $this->dddpChart->previousMonthCountToDayNo, $this->dddpChart->previousMonthCountToYearNo) . " " . common::getMonthNameByNumber($this->dddpChart->previousMonthCountToMonthNo) . " - " . $this->dddpChart->currentMonthCountToDayNo . $this->getEnglishOrdinalFromTimestamp($this->dddpChart->currentMonthCountToMonthNo, $this->dddpChart->currentMonthCountToDayNo, $this->dddpChart->currentMonthCountToYearNo) . " " . common::getMonthNameByNumber($this->dddpChart->currentMonthCountToMonthNo);
		}
		$this->xml .= "<monthToDisplay>" . $this->monthToDisplayInChart . "</monthToDisplay>";

		if($this->dddpChart->currentMonthCountToMonthNo == 1)
		{
			$this->yearToDisplayInChart = $this->dddpChart->previousMonthCountToYearNo . " - " . $this->dddpChart->currentMonthCountToYearNo;
		}
		else
		{
			$this->yearToDisplayInChart = $this->dddpChart->currentMonthCountToYearNo;
		}
		$this->xml .= "<yearToDisplay>" . $this->yearToDisplayInChart . "</yearToDisplay>";

		// Display DDDP Top Level Table
		$this->displayTopLevelTable();
	}

	private function showYTDDataOnTable()
	{
		$this->sqlFromDate = $this->dddpChart->previousMonthCountToYearNo . "-" . $this->dddpChart->previousMonthCountToMonthNo . "-01";
		$this->sqlToDate = $this->dddpChart->currentMonthCountToYearNo . "-" . $this->dddpChart->currentMonthCountToMonthNo . "-31";

		if($this->dddpChart->currentMonthCountToMonthNo == 1)
		{
			$this->yearToDisplayInChart = $this->dddpChart->previousMonthCountToYearNo;
		}
		else
		{
			$this->yearToDisplayInChart = $this->dddpChart->previousMonthCountToYearNo . " - " . $this->dddpChart->currentMonthCountToYearNo;
		}
		$this->xml .= "<yearToDisplay>" . $this->yearToDisplayInChart . "</yearToDisplay>";

		// Display DDDP Top Level Table
		$this->displayTopLevelTableYTD();
	}

	private function displayTopLevelTable()
	{
		$this->xml .= "<dddpTopLevelTable>";
			$this->xml .= "<mtdTable>mtdTable</mtdTable>";

		// Does the current user have permission to view this dashboard
		if($this->dddpLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";

			$this->xml .= "<rlipTarget>" . $this->dddpLib->getTarget("RLIP") . "</rlipTarget>";
			$this->xml .= "<clipTarget>" . $this->dddpLib->getTarget("CLIP") . "</clipTarget>";

			$this->getCLIPByShippingPoint();
			$this->getRLIPByShippingPoint();

			for($i = 0; $i < count($this->shippingPointsArray); $i++)
			{
				$this->xml .= "<shippingPointItem>";
					$this->xml .= "<shippingPointName>" . $this->shippingPointsArray[$i] . "</shippingPointName>";
					$this->xml .= "<clipValue>" . $this->clipArray[$i] . "</clipValue>";
					$this->xml .= "<clipFromObjective>" . $this->dddpLib->getCLIPToTarget($this->clipArray[$i]) . "</clipFromObjective>";

					if($this->dddpLib->getCLIPToTarget($this->clipArray[$i]) < 0)
					{
						$this->xml .= "<formatClipPosition>0</formatClipPosition>";
					}
					else
					{
						$this->xml .= "<formatClipPosition>1</formatClipPosition>";
					}


					$this->xml .= "<rlipValue>" . $this->rlipArray[$i] . "</rlipValue>";
					$this->xml .= "<rlipFromObjective>" . $this->dddpLib->getRLIPToTarget($this->rlipArray[$i]) . "</rlipFromObjective>";

					if($this->dddpLib->getRLIPToTarget($this->rlipArray[$i]) < 0)
					{
						$this->xml .= "<formatRlipPosition>0</formatRlipPosition>";
					}
					else
					{
						$this->xml .= "<formatRlipPosition>1</formatRlipPosition>";
					}

					// Display the total number of line items for each site
					$this->xml .= "<totalLineItems>" . $this->totalLineItems[$i] . "</totalLineItems>";

					// Display the total number of missed RLIP lines for each site
					$rlipMissed = 0;
					$rlipMissed = $this->totalRLIPLinesMissed[$i];
					$this->xml .= "<totalRLIPLinesMissed>" . $rlipMissed . "</totalRLIPLinesMissed>";

					// Display the total RLIP Opportunity for each site
					$rlipOpp = 0;
					$rlipOpp = ($this->totalRLIPLinesMissed[$i] / array_sum($this->totalRLIPLineItems)) * 100;
					$this->xml .= "<totalRLIPOpp>" . number_format($rlipOpp, 2) . "</totalRLIPOpp>";

					// RLIP Lines default date percentage
					$this->xml .= "<rlipLinesPercentage>" . number_format($this->rlipDefaultDatePercentage[$i], 2) . "</rlipLinesPercentage>";

				$this->xml .= "</shippingPointItem>";
			}

			// Display the group totals at the bottom of the table
			$this->displayTopLevelTableGroup();
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</dddpTopLevelTable>";
	}

	public function displayTopLevelTableGroup()
	{
		$this->xml .= "<groupShippingPointItem>";

			// CLIP
			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "'" . $this->dddpChart->dddpLib->businessUnit);
			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			// Find the total clip line items which meet the criteria
			$datasetCLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND aoIndent = 'X'" . $this->dddpChart->dddpLib->businessUnit);
			$totalCLIPItems = mysql_num_rows($datasetCLIP);

			if($totalCLIPItems == 0 || $totalDDDPItems == 0)
			{
				$totalCLIP = 0;
			}
			else
			{
				$totalCLIP = ($totalCLIPItems / $totalDDDPItems) * 100;
			}

			$this->xml .= "<groupCLIP>" . number_format($totalCLIP, 2) . "</groupCLIP>";
			//$this->xml .= "<formatCLIPPositionGroup>" . $this->dddpLib->getCLIPToTarget($totalCLIP) . "</formatCLIPPositionGroup>";

			if($this->dddpLib->getCLIPToTarget($totalCLIP) < 0)
			{
				$this->xml .= "<formatClipPositionGroup>0</formatClipPositionGroup>";
			}
			else
			{
				$this->xml .= "<formatClipPositionGroup>1</formatClipPositionGroup>";
			}
			
			// RLIP
			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			// Find the total rlip line items which meet the criteria
			$datasetRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND roIdent = 'X' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
			$totalRLIPItems = mysql_num_rows($datasetRLIP);

			if($totalRLIPItems == 0 || $totalDDDPItems == 0)
			{
				$totalRLIP = 0;
			}
			else
			{
				$totalRLIP = ($totalRLIPItems / $totalDDDPItems) * 100;
			}

			$this->xml .= "<groupRLIP>" . number_format($totalRLIP, 2) . "</groupRLIP>";
			//$this->xml .= "<formatRLIPPositionGroup>" . $this->dddpLib->getRLIPToTarget($totalRLIP) . "</formatRLIPPositionGroup>";

			if($this->dddpLib->getRLIPToTarget($totalRLIP) < 0)
			{
				$this->xml .= "<formatRlipPositionGroup>0</formatRlipPositionGroup>";
			}
			else
			{
				$this->xml .= "<formatRlipPositionGroup>1</formatRlipPositionGroup>";
			}			
			
			// Display the total number of line items for each site
			$this->xml .= "<totalLineItems>" . array_sum($this->totalLineItems) . "</totalLineItems>";

			// Display the total number of missed RLIP lines for each site
			$rlipMissed = 0;
			$rlipMissed = array_sum($this->totalRLIPLinesMissed);
			$this->xml .= "<totalRLIPLinesMissed>" . $rlipMissed . "</totalRLIPLinesMissed>";

			// Display the total RLIP Opportunity for each site
			$rlipOpp = 0;
			$rlipOpp = (array_sum($this->totalRLIPLinesMissed) / array_sum($this->totalRLIPLineItems)) * 100;
			$this->xml .= "<totalRLIPOpp>" . number_format($rlipOpp, 2) . "</totalRLIPOpp>";

			$totalPercentage = (array_sum($this->totalRLIPDefaultDateItems) / array_sum($this->totalRLIP)) * 100;

			// RLIP Lines default date percentage
			$this->xml .= "<rlipLinesPercentage>" . number_format($totalPercentage, 2) . "</rlipLinesPercentage>";

		$this->xml .= "</groupShippingPointItem>";
	}

	private function getCLIPByShippingPoint()
	{
		$datasetShippingPoints = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id, name FROM shippingPoints ORDER BY name ASC");

		$total = 0;

		while($fieldsShippingPoints = mysql_fetch_array($datasetShippingPoints))
		{
			// Put the shipping point name into an array
			array_push($this->shippingPointsArray, $fieldsShippingPoints['name']);

			// Find the total line items
			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND shippingPoint IN('" . $fieldsShippingPoints['id'] . "')" . $this->dddpChart->dddpLib->businessUnit);
			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			array_push($this->totalLineItems, $totalDDDPItems);

			// Mielec (0015) is always 100%
			if ($fieldsShippingPoints['id'] != '0015')
			{
				// Find the total clip line items which meet the criteria
				$datasetCLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND aoIndent = 'X' AND shippingPoint IN('" . $fieldsShippingPoints['id'] . "')" . $this->dddpChart->dddpLib->businessUnit);
				$totalCLIPItems = mysql_num_rows($datasetCLIP);

				if($totalCLIPItems == 0 || $totalDDDPItems == 0)
				{
					$total = 0;
				}
				else
				{
					$total = ($totalCLIPItems / $totalDDDPItems) * 100;
				}
			}
			else
			{
				$total = 100;
			}

			// Push value to array
			array_push($this->clipArray, number_format($total, 2));

			$total = 0;
		}
	}

	private function getRLIPByShippingPoint()
	{
		$datasetShippingPoints = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id, name FROM shippingPoints ORDER BY name ASC");

		$total = 0;

		while($fieldsShippingPoints = mysql_fetch_array($datasetShippingPoints))
		{
			// Find the total line items
			$sql = "SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit;

			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			array_push($this->totalRLIPLineItems, $totalDDDPItems);



			// Mielec (0015) is always 100%
			if ($fieldsShippingPoints['id'] != '0015')
			{
				// Find the total rlip line items which meet the criteria
				$datasetRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND roIdent = 'X' AND shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
				$totalRLIPItems = mysql_num_rows($datasetRLIP);

				// Find the total rlip line items having the default date
				$sql = "SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND defaultGIDate = 'X'" . $this->dddpChart->dddpLib->businessUnit;
				$xDateRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

				$totalRLIPDefaultDateItems = mysql_num_rows($xDateRLIP);

				array_push($this->totalRLIPDefaultDateItems, $totalRLIPDefaultDateItems);

				$totalRLIP = $totalRLIPDefaultDateItems + $totalDDDPItems;

				array_push($this->totalRLIP, $totalRLIP);

				$rlipDefaultDatePercentage = ($totalRLIP != 0) ? ($totalRLIPDefaultDateItems / $totalRLIP) * 100 : 0;

				array_push($this->rlipDefaultDatePercentage, $rlipDefaultDatePercentage);

				$totalRLIPLinesMissed = 0;
				$totalRLIPLinesMissed = $totalDDDPItems - $totalRLIPItems;
				array_push($this->totalRLIPLinesMissed, $totalRLIPLinesMissed);

				if($totalRLIPItems == 0 || $totalDDDPItems == 0)
				{
					$total = 0;
				}
				else
				{
					$total = ($totalRLIPItems / $totalDDDPItems) * 100;
				}
			}
			else
			{
				$total = 100;
				array_push($this->totalRLIPDefaultDateItems, $totalDDDPItems);
				array_push($this->totalRLIPLinesMissed, 0);
				array_push($this->totalRLIP, 0);
				array_push($this->rlipDefaultDatePercentage, 0);
			}

			// Push value to array
			array_push($this->rlipArray, number_format($total, 2));

			$total = 0;
		}
	}

	private function displayTopLevelTableYTD()
	{
		$this->xml .= "<dddpTopLevelTable>";

		// Does the current user have permission to view this dashboard
		if($this->dddpLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";

			$this->xml .= "<rlipTarget>" . $this->dddpLib->getTarget("RLIP") . "</rlipTarget>";
			$this->xml .= "<clipTarget>" . $this->dddpLib->getTarget("CLIP") . "</clipTarget>";

			$this->getCLIPByShippingPointYTD();
			$this->getRLIPByShippingPointYTD();

			for($i = 0; $i < count($this->shippingPointsArray); $i++)
			{
				$this->xml .= "<shippingPointItem>";
					$this->xml .= "<shippingPointName>" . $this->shippingPointsArray[$i] . "</shippingPointName>";
					$this->xml .= "<clipValue>" . $this->clipArray[$i] . "</clipValue>";
					$this->xml .= "<clipFromObjective>" . $this->dddpLib->getCLIPToTarget($this->clipArray[$i]) . "</clipFromObjective>";

					if($this->dddpLib->getCLIPToTarget($this->clipArray[$i]) < 0)
					{
						$this->xml .= "<formatClipPosition>0</formatClipPosition>";
					}
					else
					{
						$this->xml .= "<formatClipPosition>1</formatClipPosition>";
					}


					$this->xml .= "<rlipValue>" . $this->rlipArray[$i] . "</rlipValue>";
					$this->xml .= "<rlipFromObjective>" . $this->dddpLib->getRLIPToTarget($this->rlipArray[$i]) . "</rlipFromObjective>";

					if($this->dddpLib->getRLIPToTarget($this->rlipArray[$i]) < 0)
					{
						$this->xml .= "<formatRlipPosition>0</formatRlipPosition>";
					}
					else
					{
						$this->xml .= "<formatRlipPosition>1</formatRlipPosition>";
					}

					// Display the total number of line items for each site
					//$this->xml .= "<totalLineItems>" . $this->totalLineItemsYTD[$i] . "</totalLineItems>";

					// Display the total number of missed RLIP lines for each site
					//$rlipMissed = 0;
					//$rlipMissed = $this->totalRLIPLinesMissedYTD[$i];
					//$this->xml .= "<totalRLIPLinesMissed>" . $rlipMissed . "</totalRLIPLinesMissed>";

					// Display the total RLIP Opportunity for each site
					//$rlipOpp = 0;
					//$rlipOpp = ($this->totalRLIPLinesMissedYTD[$i] / array_sum($this->totalRLIPLineItemsYTD)) * 100;
					//$this->xml .= "<totalRLIPOpp>" . number_format($rlipOpp, 2) . "</totalRLIPOpp>";

				$this->xml .= "</shippingPointItem>";
			}

			$this->displayTopLevelTableYTDGroup();
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</dddpTopLevelTable>";
	}

	public function displayTopLevelTableYTDGroup()
	{
		$this->xml .= "<groupShippingPointItem>";

			// CLIP
			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "'" . $this->dddpChart->dddpLib->businessUnit);
			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			// Find the total clip line items which meet the criteria
			$datasetCLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND aoIndent = 'X'" . $this->dddpChart->dddpLib->businessUnit);
			$totalCLIPItems = mysql_num_rows($datasetCLIP);

			if($totalCLIPItems == 0 || $totalDDDPItems == 0)
			{
				$totalCLIP = 0;
			}
			else
			{
				$totalCLIP = ($totalCLIPItems / $totalDDDPItems) * 100;
			}

			$this->xml .= "<groupCLIP>" . number_format($totalCLIP, 2) . "</groupCLIP>";
			//$this->xml .= "<formatCLIPPositionGroup>" . $this->dddpLib->getCLIPToTarget($totalCLIP) . "</formatCLIPPositionGroup>";

			if($this->dddpLib->getCLIPToTarget($totalCLIP) < 0)
			{
				$this->xml .= "<formatClipPositionGroup>0</formatClipPositionGroup>";
			}
			else
			{
				$this->xml .= "<formatClipPositionGroup>1</formatClipPositionGroup>";
			}
			
			// RLIP
			$datasetDDDPItems = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
			$totalDDDPItems = mysql_num_rows($datasetDDDPItems);

			// Find the total rlip line items which meet the criteria
			$datasetRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT id FROM dddp WHERE `actualDate` BETWEEN '" . $this->sqlFromDate . "' AND '" . $this->sqlToDate . "' AND roIdent = 'X' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
			$totalRLIPItems = mysql_num_rows($datasetRLIP);

			if($totalRLIPItems == 0 || $totalDDDPItems == 0)
			{
				$totalRLIP = 0;
			}
			else
			{
				$totalRLIP = ($totalRLIPItems / $totalDDDPItems) * 100;
			}

			$this->xml .= "<groupRLIP>" . number_format($totalRLIP, 2) . "</groupRLIP>";
			//$this->xml .= "<formatRLIPPositionGroup>" . $this->dddpLib->getRLIPToTarget($totalRLIP) . "</formatRLIPPositionGroup>";

			if($this->dddpLib->getRLIPToTarget($totalRLIP) < 0)
			{
				$this->xml .= "<formatRlipPositionGroup>0</formatRlipPositionGroup>";
			}
			else
			{
				$this->xml .= "<formatRlipPositionGroup>1</formatRlipPositionGroup>";
			}
			
			// Display the total number of line items for each site
			//$this->xml .= "<totalLineItems>" . array_sum($this->totalLineItemsYTD) . "</totalLineItems>";

			// Display the total number of missed RLIP lines for each site
			//$rlipMissed = 0;
			//$rlipMissed = array_sum($this->totalRLIPLinesMissedYTD);
			//$this->xml .= "<totalRLIPLinesMissed>" . $rlipMissed . "</totalRLIPLinesMissed>";

			// Display the total RLIP Opportunity for each site
			//$rlipOpp = 0;
			//$rlipOpp = (array_sum($this->totalRLIPLinesMissedYTD) / array_sum($this->totalRLIPLineItemsYTD)) * 100;
			//$this->xml .= "<totalRLIPOpp>" . number_format($rlipOpp, 2) . "</totalRLIPOpp>";

		$this->xml .= "</groupShippingPointItem>";
	}

	private function getCLIPByShippingPointYTD()
	{
		$datasetShippingPoints = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM shippingPoints ORDER BY name ASC");

		while($fieldsShippingPoints = mysql_fetch_array($datasetShippingPoints))
		{
			// Put the shipping point name into an array
			array_push($this->shippingPointsArray, $fieldsShippingPoints['name']);

			// Reset all variables
			$total = 0;
			$clipPercentageArray = array();
			$clipPercentage = 0;

			$CLIPval = $this->dddpChart->currentMonthCountToMonthNo + 1;

			$siteSpecificCLIP = array();

			while($CLIPval <= 12)
			{
				$datasetDDDP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $CLIPval . "-31'" . $this->dddpChart->dddpLib->businessUnit);
				$numOfDDDPItems = mysql_num_rows($datasetDDDP);

				array_push($this->totalLineItemsYTD, $numOfDDDPItems);

				$datasetCLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $CLIPval . "-31' AND aoIndent = 'X'" . $this->dddpChart->dddpLib->businessUnit);
				$numOfCLIPItems = mysql_num_rows($datasetCLIP);

				if($numOfCLIPItems == 0)
				{
					$clipPercentage = $clipPercentage + 0;
				}
				else
				{
					$clipPercentage = $numOfCLIPItems / $numOfDDDPItems;
				}

				// Add total for individual to the array
				array_push($clipPercentageArray, $clipPercentage);

				$CLIPval ++;
			}

			$CLIPval = 1;

			while($CLIPval <= $this->dddpChart->currentMonthCountToMonthNo)
			{
				$datasetDDDP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $CLIPval . "-31'" . $this->dddpChart->dddpLib->businessUnit);
				$numOfDDDPItems = mysql_num_rows($datasetDDDP);

				$datasetCLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $CLIPval . "-01' AND '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $CLIPval . "-31' AND aoIndent = 'X'" . $this->dddpChart->dddpLib->businessUnit);
				$numOfCLIPItems = mysql_num_rows($datasetCLIP);

				if($numOfCLIPItems == 0)
				{
					$clipPercentage = $clipPercentage + 0;
				}
				else
				{
					$clipPercentage = ($numOfCLIPItems / $numOfDDDPItems);
				}

				// Add total for individual to the array
				array_push($clipPercentageArray, $clipPercentage);

				$CLIPval ++;
			}

			// Calculate the totals by finding an average
			$totalInClipPercentageArray = 0;
			$totalInClipPercentageArray = count($clipPercentageArray);

			$value = 0;
			$valueTotal = 0;

			foreach($clipPercentageArray as $arrayValue)
			{
				$value = $value + $arrayValue;
			}

			$valueTotal = $value / $totalInClipPercentageArray;

			$valueTotal = $valueTotal * 100;

			array_push($this->clipArray, number_format($valueTotal, 2));
		}
	}

	private function getRLIPByShippingPointYTD()
	{
		$datasetShippingPoints = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM shippingPoints ORDER BY name ASC");

		$total = 0;

		while($fieldsShippingPoints = mysql_fetch_array($datasetShippingPoints))
		{
			// Reset all variables
			$total = 0;
			$rlipPercentageArray = array();
			$rlipPercentage = 0;

			$RLIPval = $this->dddpChart->currentMonthCountToMonthNo + 1;

			$siteSpecificRLIP = array();

			while($RLIPval <= 12)
			{
				$datasetDDDP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $RLIPval . "-31' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
				$numOfDDDPItems = mysql_num_rows($datasetDDDP);

				array_push($this->totalRLIPLineItemsYTD, $numOfDDDPItems);

				$datasetRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->dddpChart->previousMonthCountToYearNo . "-" . $RLIPval . "-31' AND roIdent = 'X' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
				$numOfRLIPItems = mysql_num_rows($datasetRLIP);

				$totalRLIPLinesMissed = 0;
				$totalRLIPLinesMissed = $numOfDDDPItems - $numOfRLIPItems;
				array_push($this->totalRLIPLinesMissedYTD, $totalRLIPLinesMissed);

				if($numOfRLIPItems == 0)
				{
					$rlipPercentage = $rlipPercentage + 0;
				}
				else
				{
					$rlipPercentage = $numOfRLIPItems / $numOfDDDPItems;
				}

				// Add total for individual to the array
				array_push($rlipPercentageArray, $rlipPercentage);

				$RLIPval ++;
			}

			$RLIPval = 1;

			while($RLIPval <= $this->dddpChart->currentMonthCountToMonthNo)
			{
				$datasetDDDP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $RLIPval . "-31' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
				$numOfDDDPItems = mysql_num_rows($datasetDDDP);

				$datasetRLIP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT agreedDate,actualDate FROM dddp WHERE shippingPoint IN('" . $fieldsShippingPoints['id'] . "') AND actualDate BETWEEN '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $RLIPval . "-01' AND '" . $this->dddpChart->currentMonthCountToYearNo . "-" . $RLIPval . "-31' AND roIdent = 'X' AND defaultGIDate IS NULL" . $this->dddpChart->dddpLib->businessUnit);
				$numOfRLIPItems = mysql_num_rows($datasetRLIP);

				if($numOfRLIPItems == 0)
				{
					$rlipPercentage = $rlipPercentage + 0;
				}
				else
				{
					$rlipPercentage = ($numOfRLIPItems / $numOfDDDPItems);
				}

				// Add total for individual to the array
				array_push($rlipPercentageArray, $rlipPercentage);

				$RLIPval ++;
			}

			// Calculate the totals by finding an average
			$totalInRlipPercentageArray = 0;
			$totalInRlipPercentageArray = count($rlipPercentageArray);

			$value = 0;
			$valueTotal = 0;

			foreach($rlipPercentageArray as $arrayValue)
			{
				$value = $value + $arrayValue;
			}

			$valueTotal = $value / $totalInRlipPercentageArray;

			$valueTotal = $valueTotal * 100;

			array_push($this->rlipArray, number_format($valueTotal, 2));
		}
	}

	private function displayDDDPChart()
	{
		$this->xml .= "<dddpChart>";

		// Does the current user have permission to view this dashboard
		if($this->dddpLib->getIfPermissions())
		{
			$this->xml .= "<allowed>1</allowed>";

			// Format Chart with Height and Name
			$this->xml .= "<chartName>" . $this->chartName . "</chartName>";
			$this->xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";

			$this->xml .= "<graphChartData>" . $this->dddpChart->generateDDDPChart() . "</graphChartData>";
		}
		else
		{
			$this->xml .= "<allowed>0</allowed>";
		}

		$this->xml .= "</dddpChart>";
	}

	private function displayFilters()
	{
		$this->xml .= "<displayFilters>";

			$this->getRadioButtons("MTD", "chartFormat", "rolling_month", 1);
			$this->getRadioButtons("YTD", "chartFormat", "year_to_date", 0);

			$this->getBUDropdown("select_business_unit", "businessUnit");
			//$this->getOtherDropdown("select_month", "selectMonth");
			//$this->getOtherDropdown("select_year", "selectYear");

			$this->getTickBoxes();

		$this->xml .= "</displayFilters>";
	}

	private function getRadioButtons($value, $name, $translateText, $isDefault)
	{
		$this->xml .= "<dddpRadioButton>";
			$this->xml .= "<radioButtonValue>" . $value . "</radioButtonValue>";
			$this->xml .= "<radioButtonName>" . $name . "</radioButtonName>";
			$this->xml .= "<radioChecked>" . $this->isFieldPosted($name, $value, $isDefault) . "</radioChecked>";
			$this->xml .= "<radioTranslate>" . $translateText . "</radioTranslate>";
		$this->xml .= "</dddpRadioButton>";
	}

	private function getBUDropdown($translateName, $selectName)
	{
		$datasetBU = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(newMrkt) FROM businessUnits ORDER BY newMrkt ASC");

		$this->xml .= "<dddpFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<dddpFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$this->xml .= "<option>";
					$this->xml .= "<optionValue>ALL</optionValue>";
					$this->xml .= "<optionDisplayValue>All</optionDisplayValue>";
					$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, "ALL") . "</optionSelected>";
				$this->xml .= "</option>";

				while($fields = mysql_fetch_array($datasetBU))
				{
					$this->xml .= "<option>";
						$this->xml .= "<optionValue>" . $fields['newMrkt'] . "</optionValue>";
						$this->xml .= "<optionDisplayValue>" . $fields['newMrkt'] . "</optionDisplayValue>";
						$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $fields['newMrkt']) . "</optionSelected>";
					$this->xml .= "</option>";
				}

			$this->xml .= "</dddpFilterDropdown>";

		$this->xml .= "</dddpFilterDropdowns>";
	}

	private function getOtherDropdown($translateName, $selectName)
	{
		$this->xml .= "<dddpFilterDropdowns>";
		$this->xml .= "<translateName>" . $translateName . "</translateName>";

			$this->xml .= "<dddpFilterDropdown>";
				$this->xml .= "<dropdownName>" . $selectName . "</dropdownName>";

				$this->xml .= "<option>";
					$this->xml .= "<optionValue>ALL</optionValue>";
					$this->xml .= "<optionDisplayValue>All</optionDisplayValue>";
					$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, "ALL") . "</optionSelected>";
				$this->xml .= "</option>";

				$this->xml .= "<option>";
					$this->xml .= "<optionValue>1</optionValue>";
					$this->xml .= "<optionDisplayValue>April</optionDisplayValue>";
					$this->xml .= "<optionSelected>" . $this->isFieldPosted($selectName, 1) . "</optionSelected>";
				$this->xml .= "</option>";

			$this->xml .= "</dddpFilterDropdown>";

		$this->xml .= "</dddpFilterDropdowns>";
	}

	private function getTickBoxes()
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT(name) FROM shippingPoints ORDER BY name ASC");

		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<plantsToShow>";

				$this->xml .= "<plantName>" . $fields['name'] . "</plantName>";
				$this->xml .= "<tickBoxSelectedCLIP>" . $this->isFieldPosted($fields['name'] . "CLIP", 0) . "</tickBoxSelectedCLIP>";
				$this->xml .= "<tickBoxSelectedRLIP>" . $this->isFieldPosted($fields['name'] . "RLIP", 0) . "</tickBoxSelectedRLIP>";

			$this->xml .= "</plantsToShow>";
		}

		if(isset($_POST['CLIPandRLIP']))
		{
			if($_POST['CLIPandRLIP'] == "on")
			{
				$this->xml .= "<tickBoxSelectedCLIPandRLIP>1</tickBoxSelectedCLIPandRLIP>";
			}
			else
			{
				$this->xml .= "<tickBoxSelectedCLIPandRLIP>0</tickBoxSelectedCLIPandRLIP>";
			}
		}
		else
		{
			$this->xml .= "<tickBoxSelectedCLIPandRLIP>0</tickBoxSelectedCLIPandRLIP>";
		}
	}

	private function isFieldPosted($fieldName, $fieldValue, $isDefault = 0)
	{
		if(isset($_POST[$fieldName]))
		{
			if(isset($_POST[$fieldName]) && $_POST[$fieldName] == $fieldValue)
			{
				$checked = 1;
			}
			else
			{
				$checked = 0;
			}
		}
		else
		{
			if(isset($_GET[$fieldName]))
			{
				if(isset($_GET[$fieldName]) && $_GET[$fieldName] == $fieldValue)
				{
					$checked = 1;
				}
				else
				{
					$checked = 0;
				}
			}
			else
			{
				if($isDefault == 1)
				{
					$checked = 1;
				}
				else
				{
					$checked = 0;
				}
			}
		}

		return $checked;
	}
}

?>