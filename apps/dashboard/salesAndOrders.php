<?php

include("snapins/sao/sao.php");

/**
 * This is the sales and orders class for the homepage
 *
 * @author Rob Markiewka
 * @version 28/04/2010
 *
 */
class salesAndOrders extends page
{

	public $currentSalesPersonName;
	public $currentCustomerName;

	public $saoCalcs;
	public $saoDateCalcs;
	public $saoLib;
	public $letter = '';

	private $columns = 0;

	private $saoMySqlNo = 0;

	private $maxNoOfCustomersToShow = 30;

	function __construct()
	{
		page::redirect("http://scapaconnect/Apps/Dashboards/SAO/");

		parent::__construct();
		page::setDebug(true); // debug at the bottom

		$this->setActivityLocation('Sales and Orders');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/salesAndOrdersMenu.xml");
		$this->xml = "";

		if (!currentuser::getInstance()->hasPermission("admin"))
				{
					//die("This page is currently being updated.  Please check back in 30 minutes.");
		}

		if (!currentuser::getInstance()->hasPermission("dashboard_saoBU"))
		{
			die("You do not have permission to view this page");
		}

		// Start XSL Page
		$this->add_output("<salesAndOrdersHome>");

		// Display snapins on the page
		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		//$snapins_left->register('apps/dashboard', 'dashboardMainSAO', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		// ---------------------------- Start Page Content ----------------------------

		// Instantiate the saoCalcs class - the class contains all calculations for the various figures
		$this->saoCalcs = new saoCalcs();

		// Instantiate the saoDateCalcs class - the class contains all calculations for the various dates
		$this->saoDateCalcs = new saoDateCalcs();

		$this->saoLib = new saoLib();

		// Remove graph link
		$this->saoLib->clickGraph = false;

		// Get Filters
		$this->getFilters();

		$this->xml .= "<thisPage>salesAndOrders</thisPage>";

		// Display the top level table on the page
		$this->displayTable();



		// ---------------------------- End Page Content ----------------------------

		// Display Charts
		//$this->xml .= $this->saoLib->displayRMChart();

		$this->xml .= $this->saoLib->displayRYChart();
		$this->xml .= $this->saoLib->displayYDChart();
		$this->xml .= $this->saoLib->displayRYAChart();
		// Don't display the funnels for Heejae
//		if (!currentuser::getInstance()->hasPermission("ceo"))
//		{
//			$this->xml .= "<ceo>false</ceo>";
//			$this->xml .= $this->saoLib->displayCustomerFunnel();
//			$this->xml .= $this->saoLib->displaySalesPeopleFunnel();
//		}
//		else
//		{
//			$this->xml .= "<ceo>true</ceo>";
//		}

		// Display Filters
		$this->displayFilters();

		// Add any $this->xml xml nodes to the output
		$this->add_output($this->xml);

		page::addDebug("SAO MySQL No: " . ($this->saoMySqlNo + $this->saoLib->saoMySqlNo), __FILE__, __LINE__);

		// Finish adding sections to the page and output to template
		$this->add_output("</salesAndOrdersHome>");

		$this->output('./apps/dashboard/xsl/salesAndOrders.xsl');
	}


//	private function tablesXML($xml)
//	{
//		$this->salesXml .= $xml;
//		$this->salesXml .= $xml;
//	}



	private function displayTable()
	{
		// Display the key date in XSL as e.g. 1st April
		$displayStartDate = date("jS F", $this->saoDateCalcs->startDateAsDate);

		// Display the year from the key date
		$displayYear = date("Y", $this->saoDateCalcs->startDateAsDate);

		// Display the working date (from filter) in XSL e.g. 1st April
		if (!$this->saoDateCalcs->endOfFiscalPeriodReached())
		{
			$displayFromDate = date("jS F", mktime(0,0,0,$this->saoDateCalcs->month,$this->saoDateCalcs->todaysDay - 1,$this->saoDateCalcs->year));
		}
		else
		{
			$displayFromDate = date("jS F", $this->saoDateCalcs->endDateAsDate);
		}


		// Display the XSL - from date to current date, plus the year
		$this->xml .= "<dataTypeToDisplay>" . $this->saoLib->dataType . "</dataTypeToDisplay>";
		$this->xml .= "<monthsToDisplay>" . $displayStartDate . " - " . $displayFromDate . "</monthsToDisplay>";
		$this->xml .= "<yearToDisplay>" . $displayYear . "</yearToDisplay>";

		$this->displayTopLevelTable();
	}


	/**
	 * Display the top level table on the page
	 */
	private function displayTopLevelTable()
	{
		// Initialise table totals
		$this->saoLib->initialiseTableTotals();

		// Start the Table
		if ($this->saoLib->regionSelected && isset($this->saoLib->bu))
		{
			$this->xml .= "<drillDown>salesPerson</drillDown>
				<saoSalesPersonTable>";
		}
		else
		{
			$this->xml .= "<saoTopLevelTable>";
		}

			// Start the Fields
			$this->xml .= "<saoFields>";

				// Add the week fields
				if (count($this->saoDateCalcs->fiscalWeeks) > 1)
				{
					for ($i = 1; $i <= $this->saoDateCalcs->weekCount; $i++)
					{
						$j = $i-1;

						$fromDate = common::transformDateForPHP($this->saoDateCalcs->fiscalWeeks[$j][0]);
						$toDate = common::transformDateForPHP($this->saoDateCalcs->fiscalWeeks[$j][1]);

						$this->xml .= "<saoField dateFrom=\"" . $fromDate . " -\" dateTo=\"" . $toDate . "\">WK" . $i . "</saoField>";

						$this->columns++;
					}
				}

				// Add the day fields
				if (isset($this->saoDateCalcs->fiscalWeekDays))
				{
					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
					{
						$this->xml .= "<saoField dateFrom=\"" . common::transformDateForPHP($day[1]) . "\">" . $day[0] .  "</saoField>";

						$this->columns++;
					}
				}

				// Month to day for current month
				$this->xml .= "<saoField>MTD</saoField>";
				$this->columns++;

				$this->xml .= "<saoField>" . translate::getInstance()->translate("sao_budget") . "</saoField>";
				$this->columns++;

			$this->xml .= "</saoFields>";

			$this->columns = ($this->columns * 2) + 2;

			// Drill down to Sales Person or show the Business Units
			if ($this->saoLib->regionSelected && isset($this->saoLib->bu))
			{
				if ($this->saoLib->filterBy == 'Customer')
				{
					$sql = $this->getCustomersSQL();

					$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

					$this->saoLib->currentBu = $this->saoLib->bu;

					$recordsFound = 0;

					while($fields = mysql_fetch_array($dataset))
					{
						// Fetch the row for each Sales Person
						$this->saoLib->currentCustomerId = $fields['customerId'];
						$this->currentCustomerName = page::xmlentities($this->saoLib->normalizeString($fields['customerName']));
						$this->displayRowCustomer();
						$recordsFound++;
					}

					if ($recordsFound == 0)
					{
						$this->xml .= "<noRecordsFound>No records have been found for your search criteria.</noRecordsFound><columns>" . $this->columns . "</columns>";
					}
				}
				else
				{
					$sql = $this->getSalesPeopleSQL();

					$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

					$this->saoLib->currentBu = $this->saoLib->bu;

					$recordsFound = 0;

					while($fields = mysql_fetch_array($dataset))
					{
						// Fetch the row for each Sales Person
						$this->saoLib->currentSalesPersonId = $fields['spId'];
						$this->currentSalesPersonName = page::xmlentities($fields['spName']);
						$this->displayRowSalesPerson();
						$recordsFound++;
					}

					if ($recordsFound == 0)
					{
						$this->xml .= "<noRecordsFound>No records have been found for your search criteria.</noRecordsFound><columns>" . $this->columns . "</columns>";
					}
				}
			}
			else
			{
				// Get all markets and display as rows
				$buArr = $this->saoLib->getBuList();

				foreach ($buArr as $bu)
				{
					// Fetch the row for each BU
					$this->saoLib->currentBu = $bu;
					$this->displayRowBu();
				}
			}

			// Display totals
			if (!$this->saoLib->regionSelected && !isset($this->saoLib->bu))
			{
				if ($this->saoLib->accessToRegion("All"))
				{
					$this->xml .= "<showAllTotals>true</showAllTotals>";
					$this->xml .= $this->saoLib->displaySAOTotals("All");
				}
				else
				{
					$this->xml .= "<showAllTotals>false</showAllTotals>";
				}

				if ($this->saoLib->accessToRegion("NA"))
				{
					$this->xml .= "<showNATotals>true</showNATotals>";
					$this->xml .= $this->saoLib->displaySAOTotals("NA");
				}
				else
				{
					$this->xml .= "<showNATotals>false</showNATotals>";
				}

				if ($this->saoLib->accessToRegion("Europe"))
				{
					$this->xml .= "<showEuropeTotals>true</showEuropeTotals>";
					$this->xml .= $this->saoLib->displaySAOTotals("Europe");
				}
				else
				{
					$this->xml .= "<showEuropeTotals>false</showEuropeTotals>";
				}
			}

		if ($this->saoLib->regionSelected && isset($this->saoLib->bu))
		{
			$this->xml .= "</saoSalesPersonTable>";
		}
		else
		{
			$this->xml .= "</saoTopLevelTable>";
		}
	}


	public function getSalesPeopleSQL()
	{
		$extraWhere = (isset($this->saoLib->region)) ? "AND salesOrg IN(" . $this->saoLib->salesOrgs . ") " : "";

		if ($this->letter == '0')
		{
			$extraWhere .= " AND (salesEmpName LIKE '0%'
				OR salesEmpName LIKE '1%'
				OR salesEmpName LIKE '2%'
				OR salesEmpName LIKE '3%'
				OR salesEmpName LIKE '4%'
				OR salesEmpName LIKE '5%'
				OR salesEmpName LIKE '6%'
				OR salesEmpName LIKE '7%'
				OR salesEmpName LIKE '8%'
				OR salesEmpName LIKE '9%')";
		}
		else
		{
			$extraWhere .= " AND salesEmpName LIKE '" . $this->letter . "%' ";
		}

		if ($this->saoLib->dataType == "Sales")
		{
			$having = " HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}
		else
		{
			$having = " HAVING sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}

		$sql = "SELECT DISTINCT(salesEmp) as spId, salesEmpName as spName
			FROM sisData AS s
			WHERE newMrkt = '" . $this->saoLib->bu . "'
			AND versionNo = '000'
			AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "'
			AND custAccGroup = 1 "
			. $extraWhere . "
			GROUP BY salesEmp "
			. $having . "
			ORDER BY salesEmpName";

		$this->saoMySqlNo++;

		return $sql;
	}


	public function getCustomersSQL()
	{
		$extraWhere = (isset($this->saoLib->region)) ? "AND salesOrg IN(" . $this->saoLib->salesOrgs . ") " : "";

		if ($this->letter == '0')
		{
			$extraWhere .= " AND (customerName LIKE '0%'
				OR customerName LIKE '1%'
				OR customerName LIKE '2%'
				OR customerName LIKE '3%'
				OR customerName LIKE '4%'
				OR customerName LIKE '5%'
				OR customerName LIKE '6%'
				OR customerName LIKE '7%'
				OR customerName LIKE '8%'
				OR customerName LIKE '9%')";
		}
		else
		{
			$extraWhere .= " AND customerName LIKE '" . $this->letter . "%' ";
		}

		if ($this->saoLib->dataType == "Sales")
		{
			$having = " HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}
		else
		{
			$having = " HAVING sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}

		$sql = "SELECT DISTINCT(customerNo) as customerId, customerName as customerName
			FROM sisData AS s
			WHERE newMrkt = '" . $this->saoLib->bu . "'
			AND versionNo = '000'
			AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "'
			AND custAccGroup = 1 "
			. $extraWhere . "
			GROUP BY customerNo "
			. $having . "
			ORDER BY customerName";

		$this->saoMySqlNo++;

		return $sql;
	}


	/**
	 * Display a row for each BU
	 *
	 * @param string $newMrkt
	 */
	private function displayRowBu()
	{
		$record = "<saoRecord>";
		$row = "<bu>" . $this->saoLib->currentBu . "</bu>";

		if ($this->saoLib->accessToRegion("All"))
		{
			$this->xml .= "<noDrillDown>false</noDrillDown>";
			$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);

				// Get BU record for NA
				if ($this->saoLib->accessToRegion("NA"))
				{
					$record = "<regionDrillDown>";
					$row = "<region>NA</region>";

					// Set current region & current salesOrgs to NA
					$this->saoLib->currentRegion = "NA";
					$this->saoLib->currentSalesOrgs = NA_SALES_ORGS;
					$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);
					unset($this->saoLib->currentRegion);
					unset($this->saoLib->currentSalesOrgs);

					$this->xml .= "</regionDrillDown>";
				}

				// Get BU record for Europe
				if ($this->saoLib->accessToRegion("Europe"))
				{
					$record = "<regionDrillDown>";
					$row = "<region>Europe</region>";

					// Set current region & current salesOrgs to Europe
					$this->saoLib->currentRegion = "Europe";
					$this->saoLib->currentSalesOrgs = EUROPE_SALES_ORGS;
					$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);
					unset($this->saoLib->currentRegion);
					unset($this->saoLib->currentSalesOrgs);

					$this->xml .= "</regionDrillDown>";
				}
		}
		else
		{
			if ($this->saoLib->accessToRegion("NA"))
			{
				// Set current region & current salesOrgs to NA

				$this->xml .= "<noDrillDown>true</noDrillDown>";
				$this->xml .= "<region>NA</region>";
				$this->saoLib->currentRegion = "NA";
				$this->saoLib->currentSalesOrgs = NA_SALES_ORGS;
				$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);
				unset($this->saoLib->currentRegion);
				unset($this->saoLib->currentSalesOrgs);
			}
			elseif ($this->saoLib->accessToRegion("Europe"))
			{
				// Set current region & current salesOrgs to Europe

				$this->xml .= "<noDrillDown>true</noDrillDown>";
				$this->xml .= "<region>Europe</region>";
				$this->saoLib->currentRegion = "Europe";
				$this->saoLib->currentSalesOrgs = EUROPE_SALES_ORGS;
				$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);
				unset($this->saoLib->currentRegion);
				unset($this->saoLib->currentSalesOrgs);
			}
			else
			{
				die("You don't have permission to view any region");
			}
		}

		$this->xml .= "</saoRecord>";
	}


	/**
	 * Display a row for each customer
	 *
	 * @param string $salesPerson
	 */
	private function displayRowCustomer()
	{
		$record = "<rowSalesPerson>";
		$row = "<salesPerson>" . $this->currentCustomerName . "</salesPerson><salesPersonId>" . $this->saoLib->currentCustomerId . "</salesPersonId><custId>" . $this->saoLib->currentCustomerId . "</custId>";

		$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);

			if ($this->saoLib->dataType == "Sales")
			{
				$having = " HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
			}
			else
			{
				$having = " HAVING sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
			}

			$sql = "SELECT DISTINCT(salesEmp) as spId, salesEmpName as spName
					FROM sisData AS s
					WHERE newMrkt = '" . $this->saoLib->bu . "'
					AND versionNo = '000'
					AND customerNo = '" . $this->saoLib->currentCustomerId . "'
					AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "'
					AND custAccGroup = 1
					GROUP BY salesEmpName "
					. $having;

			$this->saoMySqlNo++;

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$customerRows = false;

			while ($fields = mysql_fetch_array($dataset))
			{
				// Get Customer record for sales person
				$record = "<rowCustomer>";
				$row = "<customer>" . page::xmlentities($fields['spName']) . "</customer>";
				//$row .= "<customerId>" . page::xmlentities($fields['spId']) . "</customerId>";

				$this->saoLib->currentSalesPersonId = $fields['spId'];

				$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);

				unset($this->saoLib->currentSalesPersonId);

				$this->xml .= "</rowCustomer>";

				$customerRows = true;
			}

			if ($customerRows)
			{
				$this->xml .= "<customerRows>true</customerRows>";
			}

		$this->xml .= "</rowSalesPerson>";
	}


	/**
	 * Display a row for each sales person
	 *
	 * @param string $salesPerson
	 */
	private function displayRowSalesPerson()
	{
		$record = "<rowSalesPerson>";
		$row = "<salesPerson>" . $this->currentSalesPersonName . "</salesPerson><salesPersonId>" . $this->saoLib->currentSalesPersonId . "</salesPersonId>";

		if ($this->saoLib->dataType == "Sales")
		{
			$having = " HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}
		else
		{
			$having = " HAVING sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " ";
		}

		$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);

			$sql = "SELECT DISTINCT(customerNo) as custId, customerName as custName
					FROM sisData AS s
					WHERE newMrkt = '" . $this->saoLib->bu . "'
					AND versionNo = '000'
					AND salesEmp = '" . $this->saoLib->currentSalesPersonId . "'
					AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "'
					AND custAccGroup = 1
					GROUP BY customerName "
					. $having;

			$this->saoMySqlNo++;

			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

			$customerRows = false;

			while ($fields = mysql_fetch_array($dataset))
			{
				// Get Customer record for sales person
				$record = "<rowCustomer>";
				$row = "<customer>" . page::xmlentities($fields['custName']) . "</customer>";
				$row .= "<customerId>" . page::xmlentities($fields['custId']) . "</customerId>";

				$this->saoLib->currentCustomerId = $fields['custId'];

				$this->xml .= ($this->saoLib->dataType == "Sales") ? $this->saoLib->getSalesData($record, $row) : $this->saoLib->getOrderData($record, $row);

				$this->xml .= "</rowCustomer>";

				$customerRows = true;
			}

			if ($customerRows)
			{
				$this->xml .= "<customerRows>true</customerRows>";
			}

			unset($this->saoLib->currentCustomerId);

		$this->xml .= "</rowSalesPerson>";
	}


	/**
	 * Display filters above the table
	 */
	private function displayFilters()
	{
		$this->xml .= "<displayFilters>";

			$this->xml .= $this->saoLib->getYearDropdown();
			$this->xml .= $this->saoLib->getMonthDropdown("select_month", "month");
			$this->xml .= $this->saoLib->getMarginDropdown("select_margin", "margin");
			$this->xml .= $this->saoLib->getCurrencyDropdown();

			if (isset($_REQUEST['bu']))
			{
				$this->xml .= $this->saoLib->getCustomerAmountDropdown();
				$this->xml .= $this->saoLib->getFilterByDropdown();

				if ($this->saoLib->customerAmount < 5000)
				{
					$this->xml .= $this->getLetterSelect();
				}
			}

			$this->xml .= $this->saoLib->getSAORadio();

		$this->xml .= "</displayFilters>";
	}


	/**
	 * Get values from the filters
	 */
	private function getFilters()
	{
		$this->xml .= $this->saoLib->getCurrency();
		$this->xml .= $this->saoLib->getDates();
		$this->xml .= $this->saoLib->getMargin();
		$this->xml .= $this->saoLib->getBu();
		$this->xml .= $this->saoLib->getCustomerAmount();
		$this->xml .= $this->saoLib->getRegion();
		$this->xml .= $this->saoLib->getFilterBy();
		$this->xml .= $this->saoLib->getDataType();

		$this->saoLib->getGraphBu();
		$this->saoLib->getGraphRegion();

		if ((isset($_REQUEST['bu'])) && ($this->saoLib->customerAmount < 5000))
		{
			$this->getLetter();
		}

		$this->xml .= "<queryString>" . $this->saoLib->queryString . "</queryString>";
	}


	public function getLetter()
	{
		if(!isset($_GET['letter']))
		{
			$this->letter = "A";
		}
		else
		{
			$this->letter = $_GET['letter'];
		}

		$this->xml .= "<selectedLetter>" . $this->letter . "</selectedLetter>";
	}


	public function getLetterSelect()
	{
		$xml = "<translateName>Page</translateName>";
		$xml .= "<showLetters>true</showLetters>";
		$xml .= "<letters>";

		$letters = array('0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

		foreach ($letters as $letter)
		{
			$xml .= "<letter>" . $letter . "</letter>";
		}

		$xml .= "</letters>";

		return $xml;
	}

}

?>