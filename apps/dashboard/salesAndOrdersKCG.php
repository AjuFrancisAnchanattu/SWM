<?php

include("snapins/sao/sao.php");

/**
 * Shows sales and order information for key cusomer groups
 * 
 * @author Rob Markiewka
 * @version 28/04/2010
 *
 */
class salesAndOrdersKCG extends page 
{								
		
	public $saoCalcs;
	public $saoDateCalcs;
	public $saoLib;

	private $numRecords;
	public $page = 1;
	public $perPage = "30";
	
	public $resetPage = false;
	
	public $kcgSearch = '';
	
	private $saoMySqlNo = 0;
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Sales and Orders');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/salesAndOrdersMenu.xml");
		
		// Initialise XML
		$this->xml = "";
		
		// Start XSL Page
		$this->add_output("<salesAndOrdersHome>");
		
		// Display snapins on the page
		$snapins_left = new snapinGroup('dashboard_left');		//creates the snapin group for dashboard
		$snapins_left->register('apps/dashboard', 'dashboardMainSAO', true, true);		//puts the dashboard load snapin in the page
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		// ---------------------------- Start Page Content ----------------------------
		
		// Instantiate the saoCalcs class - the class contains all calculations for the various figures
		$this->saoCalcs = new saoCalcs();
		
		// Instantiate the saoDateCalcs class - the class contains all calculations for the various dates
		$this->saoDateCalcs = new saoDateCalcs();
		
		$this->saoLib = new saoLib();
		
		$this->saoLib->customerAmount = 5000;
		
		// Get Filters
		$this->getFilters();
		
		// Get total number of KCGs to display
		$this->getNumRecords();
		
		$this->xml .= "<thisPage>salesAndOrdersKCG</thisPage>";
				
		// Display the top level table on the page
		$this->displayTopLevelTable();
		
		// ---------------------------- End Page Content ----------------------------
		
		// Display Chart
		$this->xml .= $this->saoLib->displayRMChart();
		$this->xml .= $this->saoLib->displayRYChart();
		$this->xml .= $this->saoLib->displayCustomerFunnel();
		
		// Display Filters
		$this->displayFilters();
		
		// Add any $this->xml xml nodes to the output
		$this->add_output($this->xml);
		
		page::addDebug("SAO MySQL No: " . ($this->saoMySqlNo + $this->saoLib->saoMySqlNo), __FILE__, __LINE__);
				
		// Finish adding sections to the page and output to template
		$this->add_output("</salesAndOrdersHome>");

		$this->output('./apps/dashboard/xsl/salesAndOrders.xsl'); 
	}
	
	
	/**
	 * Display the top level table on the page
	 */
	private function displayTopLevelTable()
	{
		// Initialise table totals
		$this->saoLib->initialiseTableTotals();	
		
		// Display the key date in XSL as e.g. 1st April
		$displayStartDate = date("jS F", $this->saoDateCalcs->startDateAsDate);
		
		// Display the year from the key date
		$displayYear = date("Y", $this->saoDateCalcs->startDateAsDate);
		
		// Display the working date (from filter) in XSL e.g. 1st April
		$displayFromDate = date("jS F", $this->saoDateCalcs->endDateAsDate);
		
		// Display the XSL - from date to current date, plus the year
		$this->xml .= "<monthsToDisplay>" . $displayStartDate . " - " . $displayFromDate . "</monthsToDisplay>";
		$this->xml .= "<yearToDisplay>" . $displayYear . "</yearToDisplay>";
		
		// Start the Table
		$this->xml .= "<drillDown>kcg</drillDown><saoKCGTable>";

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
					}
				}							
				
				// Add the day fields	
				if (isset($this->saoDateCalcs->fiscalWeekDays))
				{
					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
					{
						$this->xml .= "<saoField dateFrom=\"" . common::transformDateForPHP($day[1]) . "\">" . $day[0] .  "</saoField>";
					}
				}
				
				// Month to day for current month
				$this->xml .= "<saoField>MTD</saoField>";
				
				$this->xml .= "<saoField>" . translate::getInstance()->translate("sao_budget") . "</saoField>";
			
			$this->xml .= "</saoFields>";
							
			// Get all the sales managers and display as rows
			$sql = $this->getCustomersSQL(false); 
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			while($fields = mysql_fetch_array($dataset))
			{
				// Fetch the row for each Customer 
				
				$this->saoLib->currentKCG = $fields['kcg'];
				$this->displayRowCustomer();
			}				
			
			// Display totals
			$this->xml .= $this->saoLib->displayTotals("All");			
						
		$this->xml .= "</saoKCGTable>";
	}
	
	
	public function getNumRecords()
	{
		$sql = $this->getCustomersSQL(true);

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$numRecords = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$numRecords++;
		}
				
		$this->numRecords = $numRecords;
	}
	
	
	public function getPerPage()
	{
		if (isset($_POST['perPage']))
		{
			$this->perPage = $_POST['perPage'];
			$this->saoLib->queryString .= "&amp;perPage=" . $this->perPage;
			$this->resetPage = true;
		}
		elseif (isset($_GET['perPage']))
		{
			$this->perPage = $_GET['perPage'];
			$this->saoLib->queryString .= "&amp;perPage=" . $this->perPage;
		}
	
		$xml = '<perPage>' . $this->perPage . '</perPage>';
		
		return $xml;
	}
	
	
	public function getPerPageDropdown()
	{						
		$perPageArray = array("10", "30", "50", "70");
		
		$default = $this->perPage;

		$xml = $this->saoLib->getDropdown("select_perPage", "perPage", $perPageArray, $default);
		
		return $xml;
	}
	
	
	public function getKCGSearchFilter()
	{							
		$xml = "<saoFilterDropdowns>";
			
			$xml .= "<translateName>kcg_search</translateName>";
			$xml .= "<kcgSearchName>kcgSearch</kcgSearchName>";
			$xml .= "<kcgSearchValue>" . $this->kcgSearch . "</kcgSearchValue>";
		
		$xml .= "</saoFilterDropdowns>";
		
		return $xml;
	}
	
	public function getKCGSearch()
	{
		if (isset($_POST['kcgSearch']))
		{
			$this->kcgSearch = html_entity_decode($_POST['kcgSearch']);
			$this->saoLib->queryString .= "&amp;kcgSearch=" . page::xmlentities($this->kcgSearch);
		}
		elseif (isset($_GET['kcgSearch']))
		{
			$this->kcgSearch = html_entity_decode($_GET['kcgSearch']);
			$this->saoLib->queryString .= "&amp;kcgSearch=" . page::xmlentities($this->kcgSearch);
		}
	
		$xml = '<kcgSearch>' . page::xmlentities($this->kcgSearch) . '</kcgSearch>';
		
		return $xml;
	}

	
	public function getCustomersSQL($allRecords)
	{	
		if ($allRecords == true)
		{
			$limit = "";
		}
		else 
		{	
			$startPoint = ($this->page - 1) * $this->perPage;
			$limit = " LIMIT " . $startPoint . ", " . $this->perPage;
		}
		
		$sql = "SELECT DISTINCT(kcg) 
			FROM sisData 
			WHERE versionNo = '000' 
			AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' 
			AND custAccGroup = 1
			AND kcg != ''  
			AND kcg LIKE '%" . $this->kcgSearch . "%' 
			GROUP BY kcg 
			HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " 
			OR sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . " 
			ORDER BY kcg " 
			. $limit . "";

		$this->saoMySqlNo++;
			
		return $sql;
	}
	
	
	/**
	 * Display a row for each customer
	 */
	private function displayRowCustomer()
	{
		$record = "<showCustomer>";
		
		$customerProcessed = preg_replace("/[^A-Za-z0-9]/","",$this->saoLib->currentKCG); 
					
		$row = "<customer>" . page::xmlentities($this->saoLib->currentKCG) . "</customer><customerProcessed>" . $customerProcessed . "</customerProcessed>";
		
		$this->xml .= $this->saoLib->getData($record, $row);
			
			$sql = "SELECT DISTINCT(customerNo), customerName, salesEmp, salesEmpName
					FROM sisData
					WHERE versionNo = '000' 
					AND kcg = '" . $this->saoLib->currentKCG . "'
					AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' 
					AND custAccGroup = 1 
					GROUP BY customerNo 
					HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . "
					OR sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount;

			$this->saoMySqlNo++;
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			$drillDownRows = false;
			
			while ($fields = mysql_fetch_array($dataset))
			{
				// Get Customer record for sales person
				$record = "<rowSTP>";
				$row = "<stp>" . page::xmlentities($fields['customerName']) . "</stp>";
				$row .= "<stpId>" . $fields['customerNo'] . "</stpId>";
				$row .= "<salesPerson>" . page::xmlentities($fields['salesEmpName']) . "</salesPerson>";
				$row .= "<salesPersonId>" . $fields['salesEmp'] . "</salesPersonId>";
						
				$this->saoLib->currentCustomerId = $fields['customerNo'];
				
				$this->xml .= $this->saoLib->getData($record, $row);
				
				unset($this->saoLib->currentCustomerId);
				
				$this->xml .= "</rowSTP>";
				
				$drillDownRows = true;
			}
			
			$this->xml .= ($drillDownRows) ? "<drillDownRows>true</drillDownRows>" : "<drillDownRows>false</drillDownRows>";
		
		$this->xml .= "</showCustomer>";
	}
	
	
	public function getPageDropdown()
	{							
		if ($this->numRecords > $this->perPage)
		{
			$numPages = ceil($this->numRecords / $this->perPage);
		}
		else
		{
			$numPages = 1;
		}

		$pageArr = array();
		
		for ($i=1; $i<=$numPages; $i++)
		{
			array_push($pageArr, $i);
		}
		
		$default = $this->page;

		$xml = $this->saoLib->getDropdown("select_page", "page", $pageArr, $default);
		
		return $xml;
	}
	
	
	public function getPage()
	{
		if ($this->resetPage)
		{
			//
		}		
		elseif (isset($_POST['page']))
		{	
			$this->page = $_POST['page'];
			$this->saoLib->queryString .= "&amp;page=" . $this->page;
		}
		elseif (isset($_GET['page']))
		{	
			$this->page = $_GET['page'];
			$this->saoLib->queryString .= "&amp;page=" . $this->page;
		}
		
		$xml = "<pageToDisplay>" . $this->page . "</pageToDisplay>";
		
		return $xml;
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
			$this->xml .= $this->saoLib->getCustomerAmountDropdown();
			$this->xml .= $this->getPerPageDropdown();
								
		$this->xml .= "</displayFilters>";
		
		$this->xml .= "<pageDropdown>";

			$this->xml .= $this->getPageDropdown();
			
		$this->xml .= "</pageDropdown>";
	}
	
	
	/**
	 * Get values from the filters
	 */
	private function getFilters()
	{
		$this->xml .= $this->saoLib->getCurrency();
		$this->xml .= $this->saoLib->getDates();
		$this->xml .= $this->saoLib->getMargin();
		$this->xml .= $this->saoLib->getCustomerAmount();
		$this->xml .= $this->getPerPage();
		$this->xml .= $this->getPage();
		$this->xml .= $this->getKCGSearch();
		
		$this->xml .= "<queryString>" . $this->saoLib->queryString . "</queryString>";
	}

}

?>