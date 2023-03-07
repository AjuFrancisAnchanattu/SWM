<?php

include("snapins/sao/sao.php");

/**
 * Shows sales and order information for customers of a particular sales person
 * 
 * @author Rob Markiewka
 * @version 14/05/2010
 *
 */
class salesAndOrdersSalesEmp extends page 
{
	
	private $saoMySqlNo = 0;
	
		
	public function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Sales and Orders');
		common::hitCounter($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/dashboard/xml/salesAndOrdersMenu.xml");
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
		$this->saoLib->customerAmount = 1000;
		
		// Get Filters
		$this->getFilters();
		
		$this->xml .= "<thisPage>salesAndOrdersSalesEmp</thisPage>";
				
		// Display the top level table on the page
		$this->displayTopLevelTable();
		
		// ---------------------------- End Page Content ----------------------------
		
		// Display Chart
		$this->xml .= $this->saoLib->displayRMChart();
		$this->xml .= $this->saoLib->displayRYChart();
		
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
		$this->xml .= "<drillDown>salesEmp</drillDown><saoSalesEmpTable><salesPerson>" . $this->saoLib->salesPersonName . "</salesPerson>";

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
			$sql = $this->getCustomersSQL(); 
			
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			
			while($fields = mysql_fetch_array($dataset))
			{
				// Fetch the row for each Customer
				$this->saoLib->currentCustomerId = $fields['customerId'];
				$this->currentCustomerName = page::xmlentities($fields['customerName']);
				$this->displayRowCustomer();
			}				
			
			// Display totals
			$this->xml .= $this->saoLib->displayTotals("All");			
						
		$this->xml .= "</saoSalesEmpTable>";
	}
	
	
	/**
	 * Get a list of all customers for the selected sales person
	 *
	 * @return string $sql
	 */
	public function getCustomersSQL()
	{	
		// If the customerAmount is zero, get all customers of the sales person who have a budget for the current fiscal period

		if ($this->saoLib->customerAmount == 0)
		{
			$sql = "SELECT DISTINCT(customerNo) as customerId, customerName
				FROM sisData
				WHERE versionNo = '120' 
				AND postingPeriod = '" . $this->saoDateCalcs->fiscalPeriod . "' 
				AND custAccGroup = 1 
				AND salesEmp = " . $this->saoLib->salesPersonId . " 
				GROUP BY customerNo 
				ORDER BY customerName";
		}
		else 
		{
			$sql = "SELECT DISTINCT(customerNo) as customerId, customerName
				FROM sisData
				WHERE versionNo = '000' 
				AND currentDate  BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' 
				AND custAccGroup = 1 
				AND salesEmp = " . $this->saoLib->salesPersonId . " 
				GROUP BY customerNo 
				HAVING sum(salesValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . "
				OR sum(incomingOrderValue" . $this->saoLib->currency . ") >= " . $this->saoLib->customerAmount . "
				ORDER BY customerName";
		}
		
		$this->saoMySqlNo++;
			
		return $sql;
	}
	
	
	/**
	 * Display a row for each customer
	 */
	private function displayRowCustomer()
	{
		$record = "<rowCustomer>";
		$row = "<customer>" . $this->currentCustomerName . "</customer><customerId>" . $this->saoLib->currentCustomerId . "</customerId>";
		
		$this->xml .= $this->saoLib->getData($record, $row);
			
		$this->xml .= "</rowCustomer>";
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
		$this->xml .= $this->saoLib->getSalesPerson();
		
		$this->saoLib->getGraphBu();
		$this->saoLib->getGraphRegion();
		
		$this->xml .= "<queryString>" . $this->saoLib->queryString . "</queryString>";
	}

}

?>