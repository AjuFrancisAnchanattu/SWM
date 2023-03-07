<?php

include("./apps/dashboard/lib/salesAndOrders/saoCalcs.php");
include("./apps/dashboard/lib/salesAndOrders/saoDateCalcs.php");
//include("./apps/dashboard/lib/salesAndOrders/saoChartLib.php");

include("./apps/dashboard/snapins/saoYear/saoYear.php");
include("./apps/dashboard/snapins/saoYearAvg/saoYearAvg.php");
include("./apps/dashboard/snapins/saoYearDifference/saoYearDifference.php");
//include("./apps/dashboard/snapins/saoFunnel/saoFunnel.php");
//include("./apps/dashboard/snapins/saoFunnelSalesPeople/saoFunnelSalesPeople.php");

DEFINE("NA_SALES_ORGS", "'US10', 'CA10'");
DEFINE("EUROPE_SALES_ORGS", "'FR10', 'DE10', 'ES10', 'GB10', 'CH10', 'IT10'");

class saoLib extends page 
{
	public $budgetMargin = "N/A";
	public $budgetRawMargin;
	public $margins;
	public $queryString = "";
	public $bu;
	public $graphBu;
	public $graphRegion;
	public $regionSelected = false;
	public $region;
	public $salesOrgs;
	public $currency = "GBP";
	
	public $charTable = array(
	    'Š'=>'S', 'š'=>'s', '?'=>'Dj', '?'=>'dj', 'Ž'=>'Z', 'ž'=>'z', '?'=>'C', '?'=>'c', '?'=>'C', '?'=>'c',
	    'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
	    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
	    'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
	    'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
	    'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
	    'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
	    'ÿ'=>'y', '?'=>'R', '?'=>'r',
	    );
	
	public $locales = array(array("CANADA", "CAD"),
		array("CHINA", "GBP"),
		array("FRANCE", "EUR"),
		array("GERMANY", "EUR"),
		array("IRELAND", "EUR"),
		array("ITALY", "EUR"),
		array("KOREA", "GBP"),
		array("MALAYSIA", "GBP"),
		array("SPAIN", "EUR"),
		array("SWITZERLAND", "CHF"),
		array("UK", "GBP"),
		array("USA", "USD"));
	
	public $currencyArr = array(array("GBP", "&#8356;", "en_GB.UTF-8"),
								array("USD", "&#36;", "en_US.UTF-8"),
								array("EUR", "&#8364;", "de_DE.UTF-8@EURO"),
								array("CHF", "CHF", "de_CH.UTF-8"),
								array("CAD", "C&#36;", "en_CA.UTF-8"));
	
	public $customerAmount = 5000;
	public $margin = GROUP_MARGIN_PERCENTAGE;
	public $percentageMargin;
	
	public $earliestYear = 2010; // how far back a user can search
		
	private $rmChartName = "sao_summary_rm";
	private $ryChartName = "sao_summary_ry";
	private $ryaChartName = "sao_summary_rya";
	private $ydChartName = "sao_summary_yd";
	private $customerFunnelName = "sao_summary_funnelCustomer";
	private $salesPeopleFunnelName = "sao_summary_funnelSalesPeople";
	private $chartHeight = 500;
	
	public $salesPersonId;
	public $salesPersonName;
	
	public $mtdSalesValueTotal;
	public $mtdOrderValueTotal;
	public $mtdSalesMarginTotal;
	public $mtdOrderMarginTotal;
	
	public $currentRegion;
	public $currentBu;
	public $currentSalesPersonId;
	public $currentSalesOrgs;
	
	public $currentCustomerId;
	public $currentSalesManagerId;
	
	public $currentKCG;
	
	public $filterBy = "Sales Person";
	
	public $saoMySqlNo = 0;
	
	public $clickGraph = true;
	
	public $salesBudgetGreen = 100;
	public $salesBudgetAmber = 95;
	
	public $salesBudgetMarginGreen = 0;
	public $salesBudgetMarginAmber = -1;
	
	public $orderBudgetGreen = 100;
	public $orderBudgetAmber = 95;
	
	public $orderBudgetMarginGreen = 0;
	public $orderBudgetMarginAmber = -1;
	
	public $numWorkingDays;
	
	function __construct()
	{		
		//var_dump("444");
		
		$this->saoCalcs = new saoCalcs();
		
		if (!isset($this->saoDateCalcs))
		{
			$this->saoDateCalcs = new saoDateCalcs();
		}
//		$this->saoChartLib = new saoChartLib();
		
		$this->margins = $this->saoCalcs->getMargins();
		
		// Display total figures on the snappin in the correct currency (always £?)
		setlocale(LC_MONETARY, 'en_GB.UTF-8');
	}
	
	
	/**
	 * Returns additional fields that are required in order to calculate the selected margin
	 *
	 * @return string $sql
	 */
	public function marginSQL($margin, $currency)
	{
		switch($margin)
		{
			case VARIABLE_MARGIN:
				$sql = "sum(invoiceVariableCost" . $currency . ") as salesMargin, sum(orderVariableCost" . $currency . ") as orderMargin";
				break;
			case CONTRIBUTION_MARGIN:
				$sql = "sum(invoiceGroupRMC" . $currency . ") as salesMargin, sum(orderGroupRMC" . $currency . ") as orderMargin";
				break;
			case GROUP_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceGroupCost" . $currency . ") as salesMargin, sum(orderGroupCost" . $currency . ") as orderMargin";
				break;
			case VARIABLE_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceVariableCost" . $currency . ") as salesMargin, sum(orderVariableCost" . $currency . ") as orderMargin";			
				break;
			case CONTRIBUTION_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceGroupRMC" . $currency . ") as salesMargin, sum(orderGroupRMC" . $currency . ") as orderMargin";
				break;
			case GROUP_MARGIN:
				$sql = "sum(invoiceGroupCost" . $currency . ") as salesMargin, sum(orderGroupCost" . $currency . ") as orderMargin";
				break;
			default:
				$sql = "";
				break;
		}
		return $sql;
	}
	
	
	/**
	 * Returns additional fields that are required in order to calculate the selected margin
	 *
	 * @return string $sql
	 */
	public function salesMarginSQL($margin, $currency)
	{
		switch($margin)
		{
			case VARIABLE_MARGIN:
				$sql = "sum(invoiceVariableCost" . $currency . ") as salesMargin";
				break;
			case CONTRIBUTION_MARGIN:
				$sql = "sum(invoiceGroupRMC" . $currency . ") as salesMargin";
				break;
			case GROUP_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceGroupCost" . $currency . ") as salesMargin";
				break;
			case VARIABLE_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceVariableCost" . $currency . ") as salesMargin";			
				break;
			case CONTRIBUTION_MARGIN_PERCENTAGE:
				$sql = "sum(invoiceGroupRMC" . $currency . ") as salesMargin";
				break;
			case GROUP_MARGIN:
				$sql = "sum(invoiceGroupCost" . $currency . ") as salesMargin";
				break;
			default:
				$sql = "";
				break;
		}
		return $sql;
	}
	
	
	/**
	 * Returns additional fields that are required in order to calculate the selected margin
	 *
	 * @return string $sql
	 */
	public function orderMarginSQL($margin, $currency)
	{
		switch($margin)
		{
			case VARIABLE_MARGIN:
				$sql = "sum(orderVariableCost" . $currency . ") as orderMargin";
				break;
			case CONTRIBUTION_MARGIN:
				$sql = "sum(orderGroupRMC" . $currency . ") as orderMargin";
				break;
			case GROUP_MARGIN_PERCENTAGE:
				$sql = "sum(orderGroupCost" . $currency . ") as orderMargin";
				break;
			case VARIABLE_MARGIN_PERCENTAGE:
				$sql = "sum(orderVariableCost" . $currency . ") as orderMargin";			
				break;
			case CONTRIBUTION_MARGIN_PERCENTAGE:
				$sql = "sum(orderGroupRMC" . $currency . ") as orderMargin";
				break;
			case GROUP_MARGIN:
				$sql = "sum(orderGroupCost" . $currency . ") as orderMargin";
				break;
			default:
				$sql = "";
				break;
		}
		return $sql;
	}
	
	
	/**
	 * Calculates a budget and budget margin
	 *
	 * @param string $period
	 * @param string $bu
	 * @param string $salesOrgs
	 * @param string $currentSalesPersonId
	 * @param string $currentCustomerID
	 * @param $string $margin
	 * @param $string $currency
	 * @return string $budget
	 */
	public function getBudget($period, $bu, $salesOrgs, $currentSalesPersonId, $currentCustomerID, $margin, $currentSalesManagerId, $kcg, $currency = 'GBP')
	{	
		$this->budgetMargin = "N/A";
		
		$where = ($bu == '') ? " AND newMrkt != 'Interco'" : " AND newMrkt = '" . $bu . "'";
		$where .= ($salesOrgs == '') ? "" : " AND salesOrg IN(" . $salesOrgs . ")";
		$where .= ($currentSalesPersonId == '') ? "" : " AND salesEmp = '" . $currentSalesPersonId . "'";
		$where .= ($currentCustomerID == '') ? "" : " AND customerNo = '" . $currentCustomerID . "'";
		$where .= ($currentSalesManagerId == '') ? "" : " AND id = '" . $currentSalesManagerId . "'";
		$where .= ($kcg == '') ? "" : " AND kcg = '" . $kcg . "'";
		
		if (($margin != VARIABLE_MARGIN) && ($margin != VARIABLE_MARGIN_PERCENTAGE) && ($margin != ''))
		{
			$selectMargin = ", " . $this->marginSQL($margin, $currency) . " ";
		}
		else 
		{
			$selectMargin = "";
		}
		
		$sql = "SELECT sum(salesValue" . $currency . ") as totalSales"
			. $selectMargin . " 
			FROM sisData
			WHERE versionNo = '120' 
			AND custAccGroup = 1
			AND postingPeriod = '" . $period . "'"
			. $where;

		$this->saoMySqlNo++;
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$budget = $fields['totalSales'];
		
		if ($selectMargin != "")
		{
			if (($fields['salesMargin'] != null) && ($fields['salesMargin'] != 'null') && ($fields['salesMargin'] != 0))
			{			
				$this->budgetRawMargin = $fields['salesMargin'];
				
				$this->budgetMargin = $this->saoCalcs->getSalesMargin($margin, $budget, $this->budgetRawMargin);
			}
		}

		$budget = $fields['totalSales'];
		
		return $budget;
	}
	
	
	/**
	 * Calculates a budget and budget margin
	 *
	 * @param string $period
	 * @param string $bu
	 * @param string $salesOrgs
	 * @param string $currentSalesPersonId
	 * @param string $currentCustomerID
	 * @param $string $margin
	 * @param $string $currency
	 * @return string $budget
	 */
	public function getBudgetMultiBus($period, $bus, $salesOrgs, $currentSalesPersonId, $currentCustomerID, $margin, $currentSalesManagerId, $kcg, $currency = 'GBP')
	{
		$this->budgetMargin = "N/A";
			
		foreach ($bus as $bu)
		{
			$buList = (isset($buList)) ? $buList . ", '" . $bu . "'" : "'" . $bu . "'";
		}
		
		$where = " AND newMrkt IN(" . $buList . ")";
		$where .= ($salesOrgs == '') ? "" : " AND salesOrg IN(" . $salesOrgs . ")";
		$where .= ($currentSalesPersonId == '') ? "" : " AND salesEmp = '" . $currentSalesPersonId . "'";
		$where .= ($currentCustomerID == '') ? "" : " AND customerNo = '" . $currentCustomerID . "'";
		$where .= ($currentSalesManagerId == '') ? "" : " AND id = '" . $currentSalesManagerId . "'";
		$where .= ($kcg == '') ? "" : " AND kcg = '" . $kcg . "'";
		
		if (($margin != VARIABLE_MARGIN) && ($margin != VARIABLE_MARGIN_PERCENTAGE) && ($margin != ''))
		{
			$selectMargin = ", " . $this->marginSQL($margin, $currency) . " ";
		}
		else 
		{
			$selectMargin = "";
		}
		
		$sql = "SELECT sum(salesValue" . $currency . ") as totalSales"
			. $selectMargin . " 
			FROM sisData
			WHERE versionNo = '120' 
			AND custAccGroup = 1
			AND postingPeriod = '" . $period . "'"
			. $where;
		
		$this->saoMySqlNo++;
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		$budget = $fields['totalSales'];
		
		if ($selectMargin != "")
		{
			if (($fields['salesMargin'] != null) && ($fields['salesMargin'] != 'null') && ($fields['salesMargin'] != 0))
			{			
				$this->budgetRawMargin = $fields['salesMargin'];
				
				$this->budgetMargin = $this->saoCalcs->getSalesMargin($margin, $budget, $this->budgetRawMargin);
			}
		}

		$budget = $fields['totalSales'];
		
		return $budget;
	}
	
	
	public function getIfPermissions()
	{
		$groupPermission = true;
		
		return $groupPermission;
	}
	
	
	/**
	 * Convert a value to a percentage with 2 decimal places
	 *
	 * @param mixed $value
	 * @return string $percentage
	 */
	public function percentageFormat($value)
	{
		$percentage = round((float)$value, 2) . "%";
		return $percentage;
	}
	
	
	/**
	 * Determine whether to set a value on a filter (if the value was selected by the user, or if the value is a default)
	 *
	 * @param string $fieldName, string $fieldValue, integer $isDefault
	 * @return integer $checked
	 */
	public function isFieldPosted($fieldName, $fieldValue, $isDefault = 0)
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
			if($isDefault == 1)
			{
				$checked = 1;
			}
			else 
			{
				$checked = 0;	
			}
		}
		
		return $checked;
	}
	
	
	public function getBu()
	{
		if (isset($_GET['bu']))
		{
			$this->bu = $_GET['bu'];
			
			$xml = "<buToDisplay>BU: " . $_GET['bu'] . "</buToDisplay>";
			
			$this->queryString .= "&amp;bu=" . $this->bu;
		}
		else 
		{
			$xml = "<buToDisplay>BU: All</buToDisplay>";
		}
		
		return $xml;
	}
	
	
	public function getGraphBu()
	{
		if (isset($_REQUEST['graphBu']))
		{
			$this->graphBu = $_REQUEST['graphBu'];
		}
	}
	
	
	public function getGraphRegion()
	{
		if (isset($_REQUEST['graphRegion']))
		{
			$this->graphRegion = $_REQUEST['graphRegion'];
		}
	}
	
	
	public function getFilterBy()
	{
		if (isset($_POST['filterBy']))
		{
			$filterBy = ($_POST['filterBy'] == "Sales%20Person") ? "Sales Person" : $_POST['filterBy'];
			$this->filterBy = $filterBy;
			$xml = "<filterByToDisplay>" . $this->filterBy . "</filterByToDisplay>";			
			$this->queryString .= "&amp;filterBy=" . $this->filterBy; 
		}
		elseif (isset($_GET['filterBy']))
		{
			$filterBy = ($_GET['filterBy'] == "Sales%20Person") ? "Sales Person" : $_GET['filterBy'];
			$this->filterBy = $filterBy;
			$xml = "<filterByToDisplay>" . $this->filterBy . "</filterByToDisplay>";
			$this->queryString .= "&amp;filterBy=" . $this->filterBy;
		}
		elseif (isset($_REQUEST['bu']))
		{
			$xml = "<filterByToDisplay>Sales Person</filterByToDisplay>";
		}
		else
		{
			$xml = "<filterByToDisplay></filterByToDisplay>";
		}

		return $xml;
	}
	
	
	public function getRegion()
	{
		if (isset($_GET['region']))
		{
			$this->regionSelected = true;
			$this->queryString .= "&amp;region=" . $_GET['region'];
			$xml = "<regionToDisplay>" . $_GET['region'] . "</regionToDisplay>";
			
			if ($_GET['region'] != "All")
			{
				$this->region = $_GET['region'];
				
				if ($_GET['region'] == "NA")
				{
					$this->salesOrgs = NA_SALES_ORGS;
				}
				elseif ($_GET['region'] == "Europe")
				{
					$this->salesOrgs = EUROPE_SALES_ORGS;
				}
			}
		}
		else 
		{
			$xml = "<regionToDisplay></regionToDisplay>";
		}
		
		return $xml;
	}
	
	
	public function getSalesPerson()
	{
		// add in lines to get the id of the salesPerson viewing the page
		
		if (isset($_GET['salesPersonId']))
		{
			$this->salesPersonId = $_GET['salesPersonId'];
			$this->queryString .= "&amp;salesPersonId=" . $_GET['salesPersonId'];
		}
		else
		{
			$sql = "SELECT id FROM salesEmployees WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
		
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
			$fields = mysql_fetch_array($dataset);
			
			if (!empty($fields))
			{
				$this->salesPersonId = $fields['id'];
				$this->queryString .= "&amp;salesPersonId=1";
			}
			else
			{
				echo "You do not have access to this page";
				die();
			}
		}
		
		$this->salesPersonName = $this->getSalesPersonName($this->salesPersonId);
	}
	
	
	public function getSalesPersonName($salesPersonId)
	{
		$sql = "SELECT name FROM salesEmployees WHERE id = '" . $salesPersonId . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		
		return $fields['name'];
	}
	
	
	public function getGraphCurrency()
	{
		if (isset($_POST['currency']))
		{	
			$this->currency = $_POST['currency'];
		}
		elseif (isset($_GET['currency']))
		{	
			$this->currency = $_GET['currency'];
		}
		else 
		{
			$userLocale = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLocale();
			
			foreach ($this->locales as $locale)
			{
				if ($locale[0] == $userLocale)
				{
					$this->currency = $locale[1];
				}
			}
		}
	}
	
	
	public function getDataType()
	{
		if (isset($_POST['dataType']))
		{	
			$this->dataType = $_POST['dataType'];
			$this->queryString .= "&amp;dataType=" . $this->dataType;
		}
		elseif (isset($_GET['dataType']))
		{	
			$this->dataType = $_GET['dataType'];
			$this->queryString .= "&amp;dataType=" . $this->dataType;
		}
		else 
		{
			// Show sales by default
			$this->dataType = "Sales";
		}
	}
	
	
	public function normalizeString($string) 
	{
		$normalizedString = strtr($string, $this->charTable);

	    return $normalizedString;
	}
		
	
	public function getCurrency()
	{
		if (isset($_POST['currency']))
		{	
			$this->currency = $_POST['currency'];
			$this->queryString .= "&amp;currency=" . $this->currency;
		}
		elseif (isset($_GET['currency']))
		{
			$this->currency = $_GET['currency'];
			$this->queryString .= "&amp;currency=" . $this->currency;
		}
		else 
		{
			$userLocale = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLocale();
			
			foreach ($this->locales as $locale)
			{
				if ($locale[0] == $userLocale)
				{
					$this->currency = $locale[1];
				}
			}
		}
		
		$xml = "";
		
		for($i = 0; $i < count($this->currencyArr); $i++)
		{
			if ($this->currency == $this->currencyArr[$i][0])
			{
				setlocale(LC_MONETARY, $this->currencyArr[$i][2]);
				
				$xml .= "<currencyToDisplay>" . $this->currency . "</currencyToDisplay>";
				$xml .= "<currencySymbol>" . $this->currencyArr[$i][1] . "</currencySymbol>";
			}
		}
		
		return $xml;
	}
	
	
	public function getCustomerAmount()
	{
		if (isset($_POST['customerAmount']))
		{
			$this->customerAmount = $_POST['customerAmount'];
			$this->queryString .= "&amp;customerAmount=" . $this->customerAmount;
			
			$caValue = $this->customerAmount;
		}
		elseif (isset($_GET['customerAmount']))
		{
			$this->customerAmount = $_GET['customerAmount'];
			$this->queryString .= "&amp;customerAmount=" . $this->customerAmount;
			
			$caValue = $this->customerAmount;
		}
		elseif (isset($_GET['bu']))
		{
			$caValue = $this->customerAmount;
		}
		else 
		{
			$caValue = "";
		}
	
		$xml = '<customerAmount>' . $caValue . '</customerAmount>';
		$xml .= '<customerAmountToDisplay>' . money_format('%.0n', $this->customerAmount) . '</customerAmountToDisplay>';
		
		return $xml;
	}
	
	
	public function getMargin()
	{
		if(isset($_POST['margin']))
		{
			switch ($_POST['margin'])
			{
				case (GROUP_MARGIN_PERCENTAGE_SHORT):
					$this->margin = GROUP_MARGIN_PERCENTAGE;
					break;
				case (VARIABLE_MARGIN_PERCENTAGE_SHORT):
					$this->margin = VARIABLE_MARGIN_PERCENTAGE;
					break;
				case (CONTRIBUTION_MARGIN_PERCENTAGE_SHORT):
					$this->margin = CONTRIBUTION_MARGIN_PERCENTAGE;
					break;
				case (GROUP_MARGIN_SHORT):
					$this->margin = GROUP_MARGIN;
					break;
				case (VARIABLE_MARGIN_SHORT):
					$this->margin = VARIABLE_MARGIN;
					break;
				case (CONTRIBUTION_MARGIN_SHORT):
					$this->margin = CONTRIBUTION_MARGIN;
					break;
			}

			$this->queryString .= "&amp;margin=" . $_POST['margin'];
		}
		else 
		{
			if(isset($_GET['margin']))
			{
				switch ($_GET['margin'])
				{
					case (GROUP_MARGIN_PERCENTAGE_SHORT):
						$this->margin = GROUP_MARGIN_PERCENTAGE;
						break;
					case (VARIABLE_MARGIN_PERCENTAGE_SHORT):
						$this->margin = VARIABLE_MARGIN_PERCENTAGE;
						break;
					case (CONTRIBUTION_MARGIN_PERCENTAGE_SHORT):
						$this->margin = CONTRIBUTION_MARGIN_PERCENTAGE;
						break;
					case (GROUP_MARGIN_SHORT):
						$this->margin = GROUP_MARGIN;
						break;
					case (VARIABLE_MARGIN_SHORT):
						$this->margin = VARIABLE_MARGIN;
						break;
					case (CONTRIBUTION_MARGIN_SHORT):
						$this->margin = CONTRIBUTION_MARGIN;
						break;
				}
	
				$this->queryString .= "&amp;margin=" . $_GET['margin'];
			}
		}
		
		$xml = "<marginToDisplay>Margin: " . $this->margin . "</marginToDisplay>";
			
		if (($this->margin == GROUP_MARGIN_PERCENTAGE) || 
			($this->margin == VARIABLE_MARGIN_PERCENTAGE) || 
			($this->margin == CONTRIBUTION_MARGIN_PERCENTAGE))
		{
			$this->percentageMargin = True;
		}
		else 
		{
			$this->percentageMargin = False;
		}
		
		return $xml;
	}
	
	
	
	/**
	 * Set the start and end dates of the fiscal month for which data will be calculated for
	 *
	 * @return string year, string month, string day, string date
	 */
	public function getDates()
	{
		$this->queryString .= $this->saoDateCalcs->queryString;
		
		$xml = "<year>" . $this->saoDateCalcs->year . "</year>
				<month>" . $this->saoDateCalcs->month . "</month>
				<fromDate>" . $this->saoDateCalcs->startDate . "</fromDate>
				<toDate>" . $this->saoDateCalcs->endDate . "</toDate>";
		
		return $xml;		
	}
	
	
	public function getYearDropdown()
	{
		$years = array();
		
		for ($i = $this->earliestYear; $i <= $this->saoDateCalcs->getLatestYear(); $i++)
		{
			array_push($years, $i);
		}
		
		$default = $this->saoDateCalcs->year;
		
		$xml = $this->getDropdown("select_year", "year", $years, $default);
		
		return $xml;
	}
		
	
	public function getMonthDropdown($translateName, $selectName)
	{
		$xml = "<saoFilterDropdowns>";
		$xml .= "<translateName>" . $translateName . "</translateName>";
		
			$xml .= "<saoFilterDropdown>";
				$xml .= "<dropdownName>" . $selectName . "</dropdownName>";
				
				if (($this->saoDateCalcs->year == $this->earliestYear) && (date("Y") != $this->saoDateCalcs->year))
				{
					$monthStart = 4;
					$monthLimit = 12;
				}
				elseif ((date("Y") == $this->saoDateCalcs->year) && ($this->saoDateCalcs->year == $this->earliestYear))
				{
					$monthStart = 4;
					$monthLimit = $this->saoDateCalcs->presentFiscalMonthInt();
				}
				elseif ((date("Y") == $this->saoDateCalcs->year) && ($this->saoDateCalcs->year != $this->earliestYear))
				{
					$monthStart = 1;
					$monthLimit = $this->saoDateCalcs->presentFiscalMonthInt();
				}
				else 
				{
					$monthStart = 1;
					$monthLimit = 12;
				}
			
				for ($i = $monthStart; $i <= $monthLimit; $i++)
        		{
	            	$monthDisplay = date("F", mktime(0, 0, 0, $i+1, 0, 0));
	               	$monthValue = date("m", mktime(0, 0, 0, $i+1, 0, 0));  // needs to be "m" format - otherwise today is shown in table
	            	
	               	$currentFiscalMonth = $this->saoDateCalcs->fiscalPeriodToMonth($this->saoDateCalcs->fiscalPeriod);
	               	               	
	               	if ($monthDisplay == $currentFiscalMonth)
	               	{
	               		$default = 1;
	               	}
	               	else
	               	{
	               		$default = 0;
	               	}
	               	
	               	$xml .= "<option>";
						$xml .= "<optionValue>" . $monthValue . "</optionValue>";
						$xml .= "<row name=\"" . $monthDisplay . "\">" . $monthDisplay . "</row>";
						$xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $monthValue, $default) . "</optionSelected>";
					$xml .= "</option>";
        		}
					
			$xml .= "</saoFilterDropdown>";
		
		$xml .= "</saoFilterDropdowns>";
		
		return $xml;
	}
	
	
	public function getMarginDropdown($translateName, $selectName)
	{
		$xml = "<saoFilterDropdowns>";
		$xml .= "<translateName>" . $translateName . "</translateName>";
		
			$xml .= "<saoFilterDropdown>";
				$xml .= "<dropdownName>" . $selectName . "</dropdownName>";
									
				foreach($this->margins as $margin)
				{
					$xml .= "<option>";
						$xml .= "<optionValue>" . $margin[1] . "</optionValue>";
						$xml .= "<row>" . $margin[0] . "</row>";
						
						if ($margin[0] == $this->margin)
						{
							$default = 1;
						}
						else 
						{
							$default = 0;
						}
						
						$xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $margin[1], $default) . "</optionSelected>";
					$xml .= "</option>";
				}
			
			$xml .= "</saoFilterDropdown>";
		
		$xml .= "</saoFilterDropdowns>";
		
		return $xml;
	}
		
	
	public function getCurrencyDropdown()
	{						
		$currencyArray = array();
		
		foreach($this->currencyArr as $currency)
		{
			array_push($currencyArray, $currency[0]);
		}
		
		$default = $this->currency;

		$xml = $this->getDropdown("select_currency", "currency", $currencyArray, $default);
		
		return $xml;
	}
	
	
	public function getSAORadio()
	{						
		$list = array("Sales", "Orders");
		$group = "dataType";
		
		$xml = "<saoFilterRadios>";
		
			$xml .= "<saoFilterRadio>";
									
				foreach($list as $listItem)
				{
					$xml .= "<option>";
						$xml .= "<optionValue>" . $listItem . "</optionValue>";
						$xml .= "<optionGroup>" . $group . "</optionGroup>";
						$selected = ($this->dataType == $listItem) ? 1 : 0;
						$xml .= "<optionSelected>" . $selected . "</optionSelected>";
					$xml .= "</option>";
				}
			
			$xml .= "</saoFilterRadio>";
		
		$xml .= "</saoFilterRadios>";
		
		return $xml;
	}
	
		
	public function getDropdown($translateName, $selectName, $list, $default = "")
	{
		$xml = "<saoFilterDropdowns>";
		$xml .= "<translateName>" . $translateName . "</translateName>";
		
			$xml .= "<saoFilterDropdown>";
				$xml .= "<dropdownName>" . $selectName . "</dropdownName>";
						
				foreach($list as $listItem)
				{
					$xml .= "<option>";
						$xml .= "<optionValue>" . $listItem . "</optionValue>";
						$xml .= "<row name=\"" . $listItem . "\">" . $listItem . "</row>";
						$isDefault = ($default == $listItem) ? 1 : 0;
						$xml .= "<optionSelected>" . $this->isFieldPosted($selectName, $listItem, $isDefault) . "</optionSelected>";
					$xml .= "</option>";
				}
			
			$xml .= "</saoFilterDropdown>";
		
		$xml .= "</saoFilterDropdowns>";
		
		return $xml;
	}
	
	
	public function getFilterByDropdown()
	{
		$filterByArr = array("Sales Person", "Customer");
		
		$xml = $this->getDropdown("filter_totals_by", "filterBy", $filterByArr, $this->filterBy);
		
		return $xml;
	}
	
	
	public function getCustomerAmountDropdown()
	{
		$customerAmountArr = array(0, 1000, 2000, 5000, 10000, 20000, 50000, 100000);
		$default = $this->customerAmount;

		$xml = $this->getDropdown("drilldown_minimum", "customerAmount", $customerAmountArr, $default);
		
		return $xml;
	}
	
	
	public function formatMargin($margin)
	{
		if ($margin != "N/A")
		{
			$margin = ($this->percentageMargin) ? $this->percentageFormat($margin) : money_format('%.0n', (double)$margin);
		}

		return $margin;	
	}
	
	
	public function displayRMChart()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_rm</chartName><thisChartType>graph</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/FusionCharts/MSLine.swf</chartLocation>";
			$xml .= "<chartName>" . $this->rmChartName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>true</overRideChartWidth>";
			
			$this->saoChart = new sao();
						
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			if (isset($this->salesPersonName))
			{
				$salesPerson = $this->salesPersonId;
			}
			else
			{
				$salesPerson = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
			
			$clickUrl = $this->clickGraph;
						
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOChart($bu, $region, $salesPerson, $clickUrl) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	
	public function displayRYChart()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_ry</chartName><thisChartType>graph</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/FusionCharts/MSLine.swf</chartLocation>";
			$xml .= "<chartName>" . $this->ryChartName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>true</overRideChartWidth>";
			
			$this->saoChart = new saoYear();
						
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			
			if (isset($this->salesPersonName))
			{
				$salesPerson = $this->salesPersonId;
			}
			else
			{
				$salesPerson = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
			
			$clickUrl = $this->clickGraph;
						
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOChart($bu, $region, $salesPerson, $clickUrl) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	
	public function displayRYAChart()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_rya</chartName><thisChartType>graph</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/FusionCharts/MSLine.swf</chartLocation>";
			$xml .= "<chartName>" . $this->ryaChartName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>true</overRideChartWidth>";
			
			$this->saoChart = new saoYearAvg();
			
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			
			if (isset($this->salesPersonName))
			{
				$salesPerson = $this->salesPersonId;
			}
			else
			{
				$salesPerson = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
			
			$clickUrl = $this->clickGraph;
						
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOChart($bu, $region, $salesPerson, $clickUrl) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	
	public function displayYDChart()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_yd</chartName><thisChartType>graph</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/FusionCharts/MSColumn3D.swf</chartLocation>";
			$xml .= "<chartName>" . $this->ydChartName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>true</overRideChartWidth>";
			
			$this->saoChart = new saoYearDifference();
						
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
			
			$clickUrl = $this->clickGraph;
						
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOChart($bu, $region, $clickUrl) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	
	public function displayCustomerFunnel()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_funnelCustomer</chartName><thisChartType>funnel</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/Widgets/Funnel.swf</chartLocation>";
			$xml .= "<chartName>" . $this->customerFunnelName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>false</overRideChartWidth>";
			
			$this->saoChart = new saoFunnel();
				
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
												
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOFunnel($bu, $region) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	public function displaySalesPeopleFunnel()
	{
		$xml = "<saoDisplayChart><chartName>sao_chart_funnelSalesPeople</chartName><thisChartType>funnel</thisChartType><saoChart>";
		
		// Does the current user have permission to view this dashboard
		if($this->getIfPermissions()) // allow access to everyone
		{
			$xml .= "<allowed>1</allowed>";
					
			// Format Chart with Height and Name
			$xml .= "<chartLocation>../../lib/charts/Widgets/Funnel.swf</chartLocation>";
			$xml .= "<chartName>" . $this->salesPeopleFunnelName . "</chartName>";
			$xml .= "<chartHeight>" . $this->chartHeight . "</chartHeight>";
			$xml .= "<overRideChartWidth>false</overRideChartWidth>";
			
			$this->saoChart = new saoFunnelSalesPeople();
			
			if (isset($this->bu))
			{
				$bu = $this->bu;
			}
			elseif (isset($this->graphBu))
			{
				$bu = $this->graphBu;
			}
			else 
			{
				$bu = '';
			}
			
			if (isset($this->region))
			{
				$region = $this->region;
			}
			elseif (isset($this->graphRegion))
			{
				$region = $this->graphRegion;
			}
			else 
			{
				$region = '';
			}
												
			$xml .= "<graphChartData>" . $this->saoChart->generateSAOFunnel($bu, $region) . "</graphChartData>";				
		}
		else 
		{
			$xml .= "<allowed>0</allowed>";	
		}
		
		$xml .= "</saoChart></saoDisplayChart>";
		
		return $xml;
	}
	
	
	/**
	 * Gets a list of all Business Units that the current user has permission to see
	 *
	 * @return array $buArr;
	 */
	public function getBuList()
	{
		if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || 
					(currentuser::getInstance()->hasPermission("dashboard_saoNAAll")) ||
					(currentuser::getInstance()->hasPermission("dashboard_saoEuropeAll")))
		{
			$buArr = $this->getAllBuList();
		}
		else
		{
			$allBuArr = $this->getAllBuList();
			$regionArr = array("NA", "Europe");
			
			$buArr = array();
		
			foreach ($allBuArr as $testBu)
			{
				foreach ($regionArr as $testRegion)
				{
					if(currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . $testBu) || 
						currentuser::getInstance()->hasPermission("dashboard_sao" . $testRegion . "All"))
					{
						array_push($buArr, $testBu);			
						break;
					}
				}
			}
			
			if (!count($buArr) > 0)
			{
				die('You do not have permission to view any Business Unit');
			}
		}

		return $buArr;	
	}
	
	
	/**
	 * Gets a distinct list of Business Units (excluding Interco)
	 *
	 * @return array $buArr;
	 */
	public function getAllBuList()
	{	
		$sql = "SELECT DISTINCT(newMrkt) FROM businessUnits WHERE newMrkt != 'Interco' ORDER BY newMrkt ASC";
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$this->saoMySqlNo++;
		
		$buArr = array();
		
		while ($fields = mysql_fetch_array($dataset))
		{
			array_push($buArr, $fields['newMrkt']);
		}

		return $buArr;	
	}
	
	
	private function cellMarginColor($margin, $workingDays)
	{
		if ($this->dataType == "Sales")
		{
			$green = $this->salesBudgetMarginGreen;
			$amber = $this->salesBudgetMarginAmber;
		}
		else 
		{
			$green = $this->orderBudgetMarginGreen;
			$amber = $this->orderBudgetMarginAmber;
		}
		
		if ($this->percentageMargin)
		{
			$budgetMarginAsPercentage = $this->budgetMargin;
		}
		else 
		{
//			$budgetMarginAsPercentage = ($this->budgetMargin / $this->numWorkingDays) * $workingDays;
//			$budgetMarginAsPercentage = ($budgetMarginAsPercentage == 0) ? 1 : $budgetMarginAsPercentage;
//			$budgetMarginAsPercentage = (100 / $budgetMarginAsPercentage) * $margin;
//			$budgetMarginAsPercentage = $budgetMarginAsPercentage - 100;	

			return 'greenCell';
		}
		
		$budgetDifference = $margin - $budgetMarginAsPercentage;
		
		if ($budgetDifference >= $green)
		{
			$color = 'greenCell';
		}
		elseif ($budgetDifference >= $amber)
		{
			$color = 'amberCell';
		}
		else 
		{
			$color = 'redCell';
		}		
		
		return $color;
	}	
	
	
	private function cellValueColor($budget, $total)
	{	
		if ($this->dataType == "Sales")
		{
			$green = $this->salesBudgetGreen;
			$amber = $this->salesBudgetAmber;
		}
		else 
		{
			$green = $this->orderBudgetGreen;
			$amber = $this->orderBudgetAmber;
		}
		
		if ($budget != 0)
		{
			$budgetPercentage = (100 / $budget) * $total;
		}
		else 
		{
			$budgetPercentage = 0;
		}
		
		if ($budgetPercentage >= $green)
		{
			$color = 'greenCell';
		}
		elseif ($budgetPercentage >= $amber)
		{
			$color = 'amberCell';
		}
		else 
		{
			$color = 'redCell';
		}
		
		return $color;
	}
	
//		
//	/**
//	 * Gets XML data for a row in the top level table
//	 *
//	 * @param string string $record, string $row
//	 */
//	public function getData($record, $row)
//	{
//		$this->mtdSalesValueTotal = 0;
//		$this->mtdOrderValueTotal = 0;
//		$this->mtdSalesMarginTotal = 0;
//		$this->mtdOrderMarginTotal = 0;
//		
//		$this->numWorkingDays = $this->saoDateCalcs->numWorkingDaysInPeriod($this->saoDateCalcs->fiscalPeriod);
//				
//		// Work out the budget
//		$bu = (isset($this->currentBu)) ? $this->currentBu : '';
//		$salesOrgs = (isset($this->currentSalesOrgs)) ? $this->currentSalesOrgs : '';
//		$currentSalesPersonId = (isset($this->currentSalesPersonId)) ? $this->currentSalesPersonId : '';
//		$currentCustomerId = (isset($this->currentCustomerId)) ? $this->currentCustomerId : '';
//		$currentSalesManagerId = (isset($this->currentSalesManagerId)) ? $this->currentSalesManagerId : '';
//		$currentKCG = (isset($this->currentKCG)) ? $this->currentKCG : '';
//		
//		$budget = $this->getBudget($this->saoDateCalcs->fiscalPeriod, $bu, $salesOrgs, $currentSalesPersonId, $currentCustomerId, $this->margin, $currentSalesManagerId, $currentKCG, $this->currency);
//					
//		// Calculate the total sales & order values for completed weeks within the current fiscal period
//		for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
//		{
//			$fields = $this->getWeekData($this->saoDateCalcs->fiscalWeeks[$i][0], $this->saoDateCalcs->fiscalWeeks[$i][1]);
//			
//			$j = $i + 1;
//			
//			${"wk{$j}SalesValue"} = money_format('%.0n', $fields['salesValueWk']);
//			
//			
//			${"wk{$j}SalesBudget"} = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->fiscalWeeks[$i][2];
//			
//			// Color the sales value cell	
//			${"wk{$j}SalesBudgetColor"} = $this->salesValueColor(${"wk{$j}SalesBudget"}, $fields['salesValueWk']);
//						
//			${"wk{$j}SalesBudget"} = money_format('%.0n', ${"wk{$j}SalesBudget"});
//			
//			${"wk{$j}OrderValue"} = money_format('%.0n', $fields['orderValueWk']);
//						
//			$salesMargin = $this->saoCalcs->getSalesMargin($this->margin, $fields['salesValueWk'], $fields['salesMargin']);
//						
//			// Color the sales margin cell	
//			${"wk{$j}SalesBudgetMarginColor"} = $this->salesMarginColor($salesMargin, $this->saoDateCalcs->fiscalWeeks[$i][2]);
//						
//			$orderMargin = $this->saoCalcs->getOrderMargin($this->margin, $fields['orderValueWk'], $fields['orderMargin']);
//									
//			${"wk{$j}SalesMargin"} = $salesMargin;
//			${"wk{$j}OrderMargin"} = $orderMargin;
//						
//			// Calculate the total sales & order values for the MTD values - by completed weeks
//			//$this->mtdSalesValueTotal += $fields['salesValueWk'];
//			//$this->mtdOrderValueTotal += $fields['orderValueWk'];
//			
//			// Add the margin data to the margin totals, which will be used to calculate the MTD margins
//			$this->mtdSalesMarginTotal += $fields['salesMargin'];
//			$this->mtdOrderMarginTotal += $fields['orderMargin'];
//			
//			// Accumulate table totals
//			if (isset($this->currentRegion))
//			{
//				$this->{"wk{$j}SalesValue{$this->currentRegion}"} += $fields['salesValueWk'];
//				$this->{"wk{$j}OrderValue{$this->currentRegion}"} += $fields['orderValueWk'];
//				$this->{"wk{$j}SalesMargin{$this->currentRegion}"} += $fields['salesMargin'];
//				$this->{"wk{$j}OrderMargin{$this->currentRegion}"} += $fields['orderMargin'];			
//			}
//			elseif (isset($this->currentSalesPersonId))
//			{
//				// dont accumulate totals
//			}
//			else 
//			{				
//				$this->{"wk{$j}SalesValueAll"} += $fields['salesValueWk'];
//				$this->{"wk{$j}OrderValueAll"} += $fields['orderValueWk'];
//				$this->{"wk{$j}SalesMarginAll"} += $fields['salesMargin'];
//				$this->{"wk{$j}OrderMarginAll"} += $fields['orderMargin'];
//			}			
//		}
//		
//		// Calculate the total sales & order values for completed days within the current week of the current fiscal period
//		if (isset($this->saoDateCalcs->fiscalWeekDays))
//		{
//			foreach($this->saoDateCalcs->fiscalWeekDays as $day)
//			{
//				$fields = $this->getDayData($day[1]);
//				
//				${"{$day[1]}SalesBudget"} = $budget / $this->numWorkingDays;
//				
//				// If the total sales value for the day is zero, display it as N\A
//				if ($fields['salesValueDay'] == "0.00" || $fields['salesValueDay'] == null || $fields['salesValueDay'] == "NULL")
//				{
//					${"{$day[1]}SalesValue"} = "N/A";
//					${"{$day[1]}SalesBudgetColor"} = 'redCell';
//					${"{$day[1]}SalesBudget"} = money_format('%.0n', ${"{$day[1]}SalesBudget"});
//				}
//				else
//				{
//					${"{$day[1]}SalesValue"} = money_format('%.0n', $fields['salesValueDay']);
//					
//					// Color the sales value cell	
//					${"{$day[1]}SalesBudgetColor"} = $this->salesValueColor(${"{$day[1]}SalesBudget"}, $fields['salesValueDay']);
//					
//					${"{$day[1]}SalesBudget"} = money_format('%.0n', ${"{$day[1]}SalesBudget"});
//										
//					// Accumulate table totals
//					if (isset($this->currentRegion))
//					{
//						$this->{"{$day[1]}SalesValue{$this->currentRegion}"} += $fields['salesValueDay'];
//						$this->{"{$day[1]}SalesMargin{$this->currentRegion}"} += $fields['salesMargin'];		
//					}
//					elseif (isset($this->currentSalesPersonId))
//					{
//						// dont accumulate totals
//					}
//					else 
//					{				
//						$this->{"{$day[1]}SalesValueAll"} += $fields['salesValueDay'];
//						$this->{"{$day[1]}SalesMarginAll"} += $fields['salesMargin'];
//					}	
//				}
//				
//				// If the total order value for the day is zero, display it as N\A
//				if ($fields['orderValueDay'] == "0.00" || $fields['orderValueDay'] == null || $fields['orderValueDay'] == "NULL")
//				{
//					${"{$day[1]}OrderValue"} = "N/A";
//				}
//				else
//				{
//					${"{$day[1]}OrderValue"} = money_format('%.0n', $fields['orderValueDay']);
//								
//					// Accumulate table totals
//					if (isset($this->currentRegion))
//					{
//						$this->{"{$day[1]}OrderValue{$this->currentRegion}"} += $fields['orderValueDay'];
//						$this->{"{$day[1]}OrderMargin{$this->currentRegion}"} += $fields['orderMargin'];			
//					}
//					elseif (isset($this->currentSalesPersonId))
//					{
//						// dont accumulate totals
//					}
//					else 
//					{				
//						$this->{"{$day[1]}OrderValueAll"} += $fields['orderValueDay'];
//						$this->{"{$day[1]}OrderMarginAll"} += $fields['orderMargin'];
//					}	
//				}
//				
//				// Calculate the day margins
//				$salesMargin = $this->saoCalcs->getSalesMargin($this->margin, $fields['salesValueDay'], $fields['salesMargin']);
//
//				// Color the sales margin cell			
//				${"{$day[1]}SalesBudgetMarginColor"} = $this->salesMarginColor($salesMargin, 1);
//								
//				$orderMargin = $this->saoCalcs->getOrderMargin($this->margin, $fields['orderValueDay'], $fields['orderMargin']);
//				
//				${"{$day[1]}SalesMargin"} = $salesMargin;
//				${"{$day[1]}OrderMargin"} = $orderMargin;
//			}
//		}
//			
//		// Get MTD values (which include weekends)	
//		
//		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueTotal, sum(incomingOrderValue" . $this->currency . ") as orderValueTotal, "
//			. $this->marginSQL($this->margin, $this->currency) .
//			" FROM sisData AS s
//			WHERE s.currentDate BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' "
//			. $this->getDataWhere();
//		
//		$this->saoMySqlNo++;
//				
//		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
//		$fieldsTotal = mysql_fetch_array($dataset);
//		
//		if (isset($this->currentRegion))
//		{
//			$region = $this->currentRegion;
//		}
//		else 
//		{
//			$region = "All";
//		}
//		
//		// Calculate the total sales & order values for the MTD values - by remaining days
//		$this->mtdSalesValueTotal = $fieldsTotal['salesValueTotal'];
//		$this->mtdOrderValueTotal = $fieldsTotal['orderValueTotal'];
//		
//		// Add the margin data to the margin totals, which will be used to calculate the MTD margins
//		$this->mtdSalesMarginTotal = $fieldsTotal['salesMargin'];
//		$this->mtdOrderMarginTotal = $fieldsTotal['orderMargin'];
//		
//		$this->{"mtdTotalOrders{$region}"} += $this->mtdOrderValueTotal;
//		$this->{"mtdTotalOrdersMargin{$region}"} += $this->mtdOrderMarginTotal;
//		$this->{"mtdTotalSales{$region}"} += $this->mtdSalesValueTotal;
//		$this->{"mtdTotalSalesMargin{$region}"} += $this->mtdSalesMarginTotal;
//
//			
//		// Start a new record
//		$xml = $record;
//
//			// Display the row title (BU/Seg)
//			$xml .= $row;
//		
//			// Display Order Values for each business unit / market segment
//			$xml .= "<saoOrderValues>";
//
//				if (count($this->saoDateCalcs->fiscalWeeks) > 1)
//				{
//					for ($i = 1; $i <= $this->saoDateCalcs->weekCount; $i++)
//					{
//						$xml .= "<saoOrderValue id=\"wk" . $i . "OrderValue\">" . ${"wk{$i}OrderValue"} . "</saoOrderValue>";					
//						$xml .= "<saoOrderValue id=\"wk" . $i . "OrderMargin\">" . $this->formatMargin(${"wk{$i}OrderMargin"}) . "</saoOrderValue>";
//					}
//				}
//				
//				if (isset($this->saoDateCalcs->fiscalWeekDays))
//				{
//					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
//					{					
//						$xml .= "<saoOrderValue id=\"" . $day[1] . "OrderValue\">" . ${"{$day[1]}OrderValue"} . "</saoOrderValue>";
//						
//						// If the total order margin for the day is zero, display it as N\A
//						if ((${"{$day[1]}OrderMargin"}) == "0")
//						{
//							$xml .= "<saoOrderValue id=\"" . $day[1] . "OrderMargin\">N/A</saoOrderValue>";
//						}
//						else 
//						{						
//							$xml .= "<saoOrderValue id=\"" . $day[1] . "OrderMargin\">" . $this->formatMargin(${"{$day[1]}OrderMargin"}) . "</saoOrderValue>";
//						}
//					}
//				}
//	
//				$xml .= "<saoOrderValue id=\"mtdOrderValue\">" . money_format('%.0n', $this->mtdOrderValueTotal) . "</saoOrderValue>";
//
//				// Calculate the MTD Order Margin
//				$mtdOrderMargin = $this->saoCalcs->getOrderMargin($this->margin, $this->mtdOrderValueTotal, $this->mtdOrderMarginTotal);
//							
//				$xml .= "<saoOrderValue id=\"mtdOrderMargin\">" . $this->formatMargin($mtdOrderMargin) . "</saoOrderValue>";
//					
//			
//			$xml .= "</saoOrderValues>";
//			
//			// Display Sales Values for each business unit / market segment
//			$xml .= "<saoSalesValues>";
//			
//				if (count($this->saoDateCalcs->fiscalWeeks) > 1)
//				{
//					for ($i = 1; $i <= $this->saoDateCalcs->weekCount; $i++)
//					{						
//						$xml .= "<saoSalesValue id=\"wk" . $i . "SalesValue\" color=\"" . ${"wk{$i}SalesBudgetColor"} . "\"><saoSalesValue>" . ${"wk{$i}SalesValue"} . "</saoSalesValue><saoSalesBudget>(" . ${"wk{$i}SalesBudget"} . ")</saoSalesBudget></saoSalesValue>";					
//						$xml .= "<saoSalesValue id=\"wk" . $i . "SalesMargin\"  color=\"" . ${"wk{$i}SalesBudgetMarginColor"} . "\"><saoSalesValue>" . $this->formatMargin(${"wk{$i}SalesMargin"}) . "</saoSalesValue></saoSalesValue>";
//					}
//				}
//				
//				if (isset($this->saoDateCalcs->fiscalWeekDays))
//				{
//					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
//					{			
//						$xml .= "<saoSalesValue id=\"" . $day[1] . "SalesValue\" color=\"" . ${"{$day[1]}SalesBudgetColor"} . "\"><saoSalesValue>" . ${"{$day[1]}SalesValue"} . "</saoSalesValue><saoSalesBudget>(" . ${"{$day[1]}SalesBudget"} . ")</saoSalesBudget></saoSalesValue>";
//	
//						// If the total sales margin for the day is zero, display it as N\A
//						if ((${"{$day[1]}SalesMargin"}) == "0")
//						{
//							$xml .= "<saoSalesValue id=\"" . $day[1] . "SalesMargin\" color=\"" . ${"{$day[1]}SalesBudgetMarginColor"} . "\"><saoSalesValue>N/A</saoSalesValue></saoSalesValue>";
//						}
//						else 
//						{	
//							$xml .= "<saoSalesValue id=\"" . $day[1] . "SalesMargin\" color=\"" . ${"{$day[1]}SalesBudgetMarginColor"} . "\"><saoSalesValue>" . $this->formatMargin(${"{$day[1]}SalesMargin"}) . "</saoSalesValue></saoSalesValue>";
//						}
//					}
//				}				
//				
//				$mtdSalesBudget = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->numWorkingDaysMTD();
//							
//				// Color the sales value cell	
//				$mtdSalesBudgetColor = $this->salesValueColor($mtdSalesBudget, $this->mtdSalesValueTotal);
//				
//				$mtdSalesBudget = money_format('%.0n', $mtdSalesBudget);
//				
//				$xml .= "<saoSalesValue id=\"mtdSalesValue\" color=\"" . $mtdSalesBudgetColor . "\"><saoSalesValue>" . money_format('%.0n', $this->mtdSalesValueTotal) . "</saoSalesValue><saoSalesBudget>(" . $mtdSalesBudget . ")</saoSalesBudget></saoSalesValue>";
//				
//				// Calculate the MTD Sales Margin
//				$mtdSalesMargin = $this->saoCalcs->getSalesMargin($this->margin, $this->mtdSalesValueTotal, $this->mtdSalesMarginTotal);
//			
//				// Color the sales margin cell
//				$mtdSalesBudgetMarginColor = $this->salesMarginColor($mtdSalesMargin, $this->saoDateCalcs->numWorkingDaysMTD());
//								
//				$xml .= "<saoSalesValue id=\"mtdSalesMargin\" color=\"" . $mtdSalesBudgetMarginColor . "\"><saoSalesValue>" . $this->formatMargin($mtdSalesMargin) . "</saoSalesValue></saoSalesValue>";
//							
//			$xml .= "</saoSalesValues>";					
//
//			$xml .= "<saoBudget>" . money_format('%.0n', $budget) . "</saoBudget>";
//			$xml .= "<saoBudgetMargin>" . $this->formatMargin($this->budgetMargin) . "</saoBudgetMargin>";
//			
//			$this->{"mtdTotalBudget{$region}"} += $budget;
//			$this->{"mtdTotalBudgetRawMargin{$region}"} += $this->budgetRawMargin;
//			
//		return $xml;
//	}
	
	
	
	
	/**
	 * Gets XML data for a row in the top level table
	 *
	 * @param string string $record, string $row
	 */
	public function getOrderData($record, $row)
	{
		$this->mtdValueTotal = 0;
		$this->mtdMarginTotal = 0;
		
		$this->numWorkingDays = $this->saoDateCalcs->numWorkingDaysInPeriod($this->saoDateCalcs->fiscalPeriod);
		
		// Work out the budget
		$bu = (isset($this->currentBu)) ? $this->currentBu : '';
		$salesOrgs = (isset($this->currentSalesOrgs)) ? $this->currentSalesOrgs : '';
		$currentSalesPersonId = (isset($this->currentSalesPersonId)) ? $this->currentSalesPersonId : '';
		$currentCustomerId = (isset($this->currentCustomerId)) ? $this->currentCustomerId : '';
		$currentSalesManagerId = (isset($this->currentSalesManagerId)) ? $this->currentSalesManagerId : '';
		$currentKCG = (isset($this->currentKCG)) ? $this->currentKCG : '';
		
		$budget = $this->getBudget($this->saoDateCalcs->fiscalPeriod, $bu, $salesOrgs, $currentSalesPersonId, $currentCustomerId, $this->margin, $currentSalesManagerId, $currentKCG, $this->currency);
					
		// Calculate the total sales & order values for completed weeks within the current fiscal period
		for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
		{
			$fields = $this->getWeekData($this->saoDateCalcs->fiscalWeeks[$i][0], $this->saoDateCalcs->fiscalWeeks[$i][1]);
			
			$j = $i + 1;
			
			${"wk{$j}Value"} = money_format('%.0n', $fields['orderValueWk']);
						
			${"wk{$j}Budget"} = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->fiscalWeeks[$i][2];
			
			// Color the sales value cell	
			${"wk{$j}BudgetColor"} = $this->cellValueColor(${"wk{$j}Budget"}, $fields['orderValueWk']);
						
			${"wk{$j}Budget"} = money_format('%.0n', ${"wk{$j}Budget"});
			
			$orderMargin = $this->saoCalcs->getOrderMargin($this->margin, $fields['orderValueWk'], $fields['orderMargin']);
						
			// Color the sales margin cell	
			${"wk{$j}BudgetMarginColor"} = $this->cellMarginColor($orderMargin, $this->saoDateCalcs->fiscalWeeks[$i][2]);
							
			${"wk{$j}Margin"} = $orderMargin;
			
			// Add the margin data to the margin totals, which will be used to calculate the MTD margins
			$this->mtdMarginTotal += $fields['orderMargin'];
			
			// Accumulate table totals
			if (isset($this->currentRegion))
			{
				$this->{"wk{$j}Value{$this->currentRegion}"} += $fields['orderValueWk'];
				$this->{"wk{$j}Margin{$this->currentRegion}"} += $fields['orderMargin'];		
			}
			elseif (isset($this->currentSalesPersonId))
			{
				// dont accumulate totals
			}
			else 
			{				
				$this->{"wk{$j}ValueAll"} += $fields['orderValueWk'];
				$this->{"wk{$j}MarginAll"} += $fields['orderMargin'];
			}			
		}
		
		// Calculate the total sales & order values for completed days within the current week of the current fiscal period
		if (isset($this->saoDateCalcs->fiscalWeekDays))
		{
			foreach($this->saoDateCalcs->fiscalWeekDays as $day)
			{
				$fields = $this->getDayOrderData($day[1]);
				
				${"{$day[1]}Budget"} = $budget / $this->numWorkingDays;
				
				// If the total sales value for the day is zero, display it as N\A
				if ($fields['orderValueDay'] == "0.00" || $fields['orderValueDay'] == null || $fields['orderValueDay'] == "NULL")
				{
					${"{$day[1]}Value"} = "N/A";
					${"{$day[1]}BudgetColor"} = 'redCell';
					${"{$day[1]}Budget"} = money_format('%.0n', ${"{$day[1]}Budget"});
					
					// Accumulate table totals
					if (isset($this->currentRegion))
					{
						$this->{"{$day[1]}Value{$this->currentRegion}"} += 0;
						$this->{"{$day[1]}Margin{$this->currentRegion}"} += 0;		
					}
					elseif (isset($this->currentSalesPersonId))
					{
						// dont accumulate totals
					}
					else 
					{				
						$this->{"{$day[1]}ValueAll"} += 0;
						$this->{"{$day[1]}MarginAll"} += 0;
					}	
				}
				else
				{
					${"{$day[1]}Value"} = money_format('%.0n', $fields['orderValueDay']);
					
					// Color the sales value cell	
					${"{$day[1]}BudgetColor"} = $this->cellValueColor(${"{$day[1]}Budget"}, $fields['orderValueDay']);
					
					${"{$day[1]}Budget"} = money_format('%.0n', ${"{$day[1]}Budget"});
										
					// Accumulate table totals
					if (isset($this->currentRegion))
					{
						$this->{"{$day[1]}Value{$this->currentRegion}"} += $fields['orderValueDay'];
						$this->{"{$day[1]}Margin{$this->currentRegion}"} += $fields['orderMargin'];		
					}
					elseif (isset($this->currentSalesPersonId))
					{
						// dont accumulate totals
					}
					else 
					{				
						$this->{"{$day[1]}ValueAll"} += $fields['orderValueDay'];
						$this->{"{$day[1]}MarginAll"} += $fields['orderMargin'];
					}	
				}
				
				// Calculate the day margins
				$orderMargin = $this->saoCalcs->getOrderMargin($this->margin, $fields['orderValueDay'], $fields['orderMargin']);

				// Color the sales margin cell			
				${"{$day[1]}BudgetMarginColor"} = $this->cellMarginColor($orderMargin, 1);

				${"{$day[1]}Margin"} = $orderMargin;
			}
		}
			
		// Get MTD values (which include weekends)	
		
		$sql = "SELECT sum(incomingOrderValue" . $this->currency . ") as orderValueTotal, "
			. $this->orderMarginSQL($this->margin, $this->currency) .
			" FROM sisData AS s
			WHERE s.currentDate BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' "
			. $this->getDataWhere();
		
		$this->saoMySqlNo++;
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fieldsTotal = mysql_fetch_array($dataset);
		
		if (isset($this->currentRegion))
		{
			$region = $this->currentRegion;
		}
		else 
		{
			$region = "All";
		}
		
		// Calculate the total sales & order values for the MTD values - by remaining days
		$this->mtdValueTotal = $fieldsTotal['orderValueTotal'];
		
		// Add the margin data to the margin totals, which will be used to calculate the MTD margins
		$this->mtdMarginTotal = $fieldsTotal['orderMargin'];

		$this->{"mtdTotal{$region}"} += $this->mtdValueTotal;
		$this->{"mtdTotalMargin{$region}"} += $this->mtdMarginTotal;
			
		// Start a new record
		$xml = $record;

			// Display the row title (BU/Seg)
			$xml .= $row;

			// Display Sales Values for each business unit / market segment
			$xmlValues = "<saoValues>";
			$xmlMargins = "<saoMargins>";
			
				if (count($this->saoDateCalcs->fiscalWeeks) > 1)
				{
					for ($i = 1; $i <= $this->saoDateCalcs->weekCount; $i++)
					{						
						$xmlValues .= "<saoValue id=\"wk" . $i . "Value\" color=\"" . ${"wk{$i}BudgetColor"} . "\"><saoValue>" . ${"wk{$i}Value"} . "</saoValue><saoBudget>" . ${"wk{$i}Budget"} . "</saoBudget></saoValue>";					
						$xmlMargins .= "<saoMargin id=\"wk" . $i . "Margin\"  color=\"" . ${"wk{$i}BudgetMarginColor"} . "\">" . $this->formatMargin(${"wk{$i}Margin"}) . "</saoMargin>";
					}
				}
				
				if (isset($this->saoDateCalcs->fiscalWeekDays))
				{
					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
					{			
						$xmlValues .= "<saoValue id=\"" . $day[1] . "Value\" color=\"" . ${"{$day[1]}BudgetColor"} . "\"><saoValue>" . ${"{$day[1]}Value"} . "</saoValue><saoBudget>" . ${"{$day[1]}Budget"} . "</saoBudget></saoValue>";
	
						// If the total sales margin for the day is zero, display it as N\A
						if ((${"{$day[1]}Margin"}) == "0")
						{
							$xmlMargins .= "<saoMargin id=\"" . $day[1] . "Margin\" color=\"" . ${"{$day[1]}BudgetMarginColor"} . "\">N/A</saoMargin>";
						}
						else 
						{	
							$xmlMargins .= "<saoMargin id=\"" . $day[1] . "Margin\" color=\"" . ${"{$day[1]}BudgetMarginColor"} . "\">" . $this->formatMargin(${"{$day[1]}Margin"}) . "</saoMargin>";
						}
					}
				}				
				
				$mtdBudget = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->numWorkingDaysMTD();
				
				// Color the sales value cell	
				$mtdBudgetColor = $this->cellValueColor($mtdBudget, $this->mtdValueTotal);
				
				$mtdBudget = money_format('%.0n', $mtdBudget);
				
				$xmlValues .= "<saoValue id=\"mtdValue\" color=\"" . $mtdBudgetColor . "\"><saoValue>" . money_format('%.0n', $this->mtdValueTotal) . "</saoValue><saoBudget>" . $mtdBudget . "</saoBudget></saoValue>";
				
				// Calculate the MTD Sales Margin
				$mtdMargin = $this->saoCalcs->getOrderMargin($this->margin, $this->mtdValueTotal, $this->mtdMarginTotal);
			
				// Color the sales margin cell
				$mtdBudgetMarginColor = $this->cellMarginColor($mtdMargin, $this->saoDateCalcs->numWorkingDaysMTD());
								
				$xmlMargins .= "<saoMargin id=\"mtdMargin\" color=\"" . $mtdBudgetMarginColor . "\">" . $this->formatMargin($mtdMargin) . "</saoMargin>";
							
			$xmlValues .= "</saoValues>";
			$xmlMargins .= "</saoMargins>";

			$xml .= $xmlValues . $xmlMargins;		

			$xml .= "<saoBudget>" . money_format('%.0n', $budget) . "</saoBudget>";
			$xml .= "<saoBudgetMargin>" . $this->formatMargin($this->budgetMargin) . "</saoBudgetMargin>";
			
			$this->{"mtdTotalBudget{$region}"} += $budget;
			$this->{"mtdTotalBudgetRawMargin{$region}"} += $this->budgetRawMargin;
			
		return $xml;
	}
	
	
	
	
	/**
	 * Gets XML data for a row in the top level table
	 *
	 * @param string string $record, string $row
	 */
	public function getSalesData($record, $row)
	{
		$this->mtdValueTotal = 0;
		$this->mtdMarginTotal = 0;
		
		$this->numWorkingDays = $this->saoDateCalcs->numWorkingDaysInPeriod($this->saoDateCalcs->fiscalPeriod);
		
		// Work out the budget
		$bu = (isset($this->currentBu)) ? $this->currentBu : '';
		$salesOrgs = (isset($this->currentSalesOrgs)) ? $this->currentSalesOrgs : '';
		$currentSalesPersonId = (isset($this->currentSalesPersonId)) ? $this->currentSalesPersonId : '';
		$currentCustomerId = (isset($this->currentCustomerId)) ? $this->currentCustomerId : '';
		$currentSalesManagerId = (isset($this->currentSalesManagerId)) ? $this->currentSalesManagerId : '';
		$currentKCG = (isset($this->currentKCG)) ? $this->currentKCG : '';
		
		$budget = $this->getBudget($this->saoDateCalcs->fiscalPeriod, $bu, $salesOrgs, $currentSalesPersonId, $currentCustomerId, $this->margin, $currentSalesManagerId, $currentKCG, $this->currency);
				
		// Calculate the total sales & order values for completed weeks within the current fiscal period
		for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
		{
			$fields = $this->getWeekSalesData($this->saoDateCalcs->fiscalWeeks[$i][0], $this->saoDateCalcs->fiscalWeeks[$i][1]);
			
			$j = $i + 1;
			
			${"wk{$j}Value"} = money_format('%.0n', $fields['salesValueWk']);
						
			${"wk{$j}Budget"} = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->fiscalWeeks[$i][2];
					
			// Color the sales value cell	
			${"wk{$j}BudgetColor"} = $this->cellValueColor(${"wk{$j}Budget"}, $fields['salesValueWk']);
			
			${"wk{$j}Budget"} = money_format('%.0n', ${"wk{$j}Budget"});
			
			$salesMargin = $this->saoCalcs->getSalesMargin($this->margin, $fields['salesValueWk'], $fields['salesMargin']);
						
			// Color the sales margin cell	
			${"wk{$j}BudgetMarginColor"} = $this->cellMarginColor($salesMargin, $this->saoDateCalcs->fiscalWeeks[$i][2]);
							
			${"wk{$j}Margin"} = $salesMargin;
			
			// Add the margin data to the margin totals, which will be used to calculate the MTD margins
			$this->mtdMarginTotal += $fields['salesMargin'];
			
			// Accumulate table totals
			if (isset($this->currentRegion))
			{
				$this->{"wk{$j}Value{$this->currentRegion}"} += $fields['salesValueWk'];
				$this->{"wk{$j}Margin{$this->currentRegion}"} += $fields['salesMargin'];		
			}
			elseif (isset($this->currentSalesPersonId))
			{
				// dont accumulate totals
			}
			else 
			{				
				$this->{"wk{$j}ValueAll"} += $fields['salesValueWk'];
				$this->{"wk{$j}MarginAll"} += $fields['salesMargin'];
			}			
		}
		
		// Calculate the total sales & order values for completed days within the current week of the current fiscal period
		if (isset($this->saoDateCalcs->fiscalWeekDays))
		{
			foreach($this->saoDateCalcs->fiscalWeekDays as $day)
			{
				$fields = $this->getDaySalesData($day[1]);
				
				${"{$day[1]}Budget"} = $budget / $this->numWorkingDays;
				
				// If the total sales value for the day is zero, display it as N\A
				if ($fields['salesValueDay'] == "0.00" || $fields['salesValueDay'] == null || $fields['salesValueDay'] == "NULL")
				{
					${"{$day[1]}Value"} = "N/A";
					${"{$day[1]}BudgetColor"} = 'redCell';
					${"{$day[1]}Budget"} = money_format('%.0n', ${"{$day[1]}Budget"});
					
					// Accumulate table totals
					if (isset($this->currentRegion))
					{
						$this->{"{$day[1]}Value{$this->currentRegion}"} += 0;
						$this->{"{$day[1]}Margin{$this->currentRegion}"} += 0;		
					}
					elseif (isset($this->currentSalesPersonId))
					{
						// dont accumulate totals
					}
					else 
					{				
						$this->{"{$day[1]}ValueAll"} += 0;
						$this->{"{$day[1]}MarginAll"} += 0;
					}	
				}
				else
				{
					${"{$day[1]}Value"} = money_format('%.0n', $fields['salesValueDay']);
					
					// Color the sales value cell	
					${"{$day[1]}BudgetColor"} = $this->cellValueColor(${"{$day[1]}Budget"}, $fields['salesValueDay']);
					
					${"{$day[1]}Budget"} = money_format('%.0n', ${"{$day[1]}Budget"});
										
					// Accumulate table totals
					if (isset($this->currentRegion))
					{
						$this->{"{$day[1]}Value{$this->currentRegion}"} += $fields['salesValueDay'];
						$this->{"{$day[1]}Margin{$this->currentRegion}"} += $fields['salesMargin'];		
					}
					elseif (isset($this->currentSalesPersonId))
					{
						// dont accumulate totals
					}
					else 
					{				
						$this->{"{$day[1]}ValueAll"} += $fields['salesValueDay'];
						$this->{"{$day[1]}MarginAll"} += $fields['salesMargin'];
					}	
				}
				
				// Calculate the day margins
				$salesMargin = $this->saoCalcs->getSalesMargin($this->margin, $fields['salesValueDay'], $fields['salesMargin']);

				// Color the sales margin cell			
				${"{$day[1]}BudgetMarginColor"} = $this->cellMarginColor($salesMargin, 1);

				${"{$day[1]}Margin"} = $salesMargin;
			}
		}
			
		// Get MTD values (which include weekends)	
		
		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueTotal, "
			. $this->salesMarginSQL($this->margin, $this->currency) .
			" FROM sisData AS s
			WHERE s.currentDate BETWEEN '" . $this->saoDateCalcs->startDate . "' AND '" . $this->saoDateCalcs->endDate . "' "
			. $this->getDataWhere();
		
		$this->saoMySqlNo++;
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fieldsTotal = mysql_fetch_array($dataset);
		
		if (isset($this->currentRegion))
		{
			$region = $this->currentRegion;
		}
		else 
		{
			$region = "All";
		}
		
		// Calculate the total sales & order values for the MTD values - by remaining days
		$this->mtdValueTotal = $fieldsTotal['salesValueTotal'];
		
		// Add the margin data to the margin totals, which will be used to calculate the MTD margins
		$this->mtdMarginTotal = $fieldsTotal['salesMargin'];

		$this->{"mtdTotal{$region}"} += $this->mtdValueTotal;
		$this->{"mtdTotalMargin{$region}"} += $this->mtdMarginTotal;
			
		// Start a new record
		$xml = $record;

			// Display the row title (BU/Seg)
			$xml .= $row;

			// Display Sales Values for each business unit / market segment
			$xmlValues = "<saoValues>";
			$xmlMargins = "<saoMargins>";
			
				if (count($this->saoDateCalcs->fiscalWeeks) > 1)
				{
					for ($i = 1; $i <= $this->saoDateCalcs->weekCount; $i++)
					{						
						$xmlValues .= "<saoValue id=\"wk" . $i . "SalesValue\" color=\"" . ${"wk{$i}BudgetColor"} . "\"><saoValue>" . ${"wk{$i}Value"} . "</saoValue><saoBudget>" . ${"wk{$i}Budget"} . "</saoBudget></saoValue>";					
						$xmlMargins .= "<saoMargin id=\"wk" . $i . "SalesMargin\"  color=\"" . ${"wk{$i}BudgetMarginColor"} . "\">" . $this->formatMargin(${"wk{$i}Margin"}) . "</saoMargin>";
					}
				}
				
				if (isset($this->saoDateCalcs->fiscalWeekDays))
				{
					foreach($this->saoDateCalcs->fiscalWeekDays as $day)
					{			
						$xmlValues .= "<saoValue id=\"" . $day[1] . "SalesValue\" color=\"" . ${"{$day[1]}BudgetColor"} . "\"><saoValue>" . ${"{$day[1]}Value"} . "</saoValue><saoBudget>" . ${"{$day[1]}Budget"} . "</saoBudget></saoValue>";
	
						// If the total sales margin for the day is zero, display it as N\A
						if ((${"{$day[1]}Margin"}) == "0")
						{
							$xmlMargins .= "<saoMargin id=\"" . $day[1] . "SalesMargin\" color=\"" . ${"{$day[1]}BudgetMarginColor"} . "\">N/A</saoMargin>";
						}
						else 
						{	
							$xmlMargins .= "<saoMargin id=\"" . $day[1] . "SalesMargin\" color=\"" . ${"{$day[1]}BudgetMarginColor"} . "\">" . $this->formatMargin(${"{$day[1]}Margin"}) . "</saoMargin>";
						}
					}
				}				
				
				$mtdBudget = ($budget / $this->numWorkingDays) * $this->saoDateCalcs->numWorkingDaysMTD();
				
				// Color the sales value cell	
				$mtdBudgetColor = $this->cellValueColor($mtdBudget, $this->mtdValueTotal);
				
				$mtdBudget = money_format('%.0n', $mtdBudget);
				
				$xmlValues .= "<saoValue id=\"mtdSalesValue\" color=\"" . $mtdBudgetColor . "\"><saoValue>" . money_format('%.0n', $this->mtdValueTotal) . "</saoValue><saoBudget>" . $mtdBudget . "</saoBudget></saoValue>";
				
				// Calculate the MTD Sales Margin
				$mtdMargin = $this->saoCalcs->getSalesMargin($this->margin, $this->mtdValueTotal, $this->mtdMarginTotal);
			
				// Color the sales margin cell
				$mtdBudgetMarginColor = $this->cellMarginColor($mtdMargin, $this->saoDateCalcs->numWorkingDaysMTD());
								
				$xmlMargins .= "<saoMargin id=\"mtdSalesMargin\" color=\"" . $mtdBudgetMarginColor . "\">" . $this->formatMargin($mtdMargin) . "</saoMargin>";
							
			$xmlValues .= "</saoValues>";
			$xmlMargins .= "</saoMargins>";

			$xml .= $xmlValues . $xmlMargins;		

			$xml .= "<saoBudget>" . money_format('%.0n', $budget) . "</saoBudget>";
			$xml .= "<saoBudgetMargin>" . $this->formatMargin($this->budgetMargin) . "</saoBudgetMargin>";
			
			$this->{"mtdTotalBudget{$region}"} += $budget;
			$this->{"mtdTotalBudgetRawMargin{$region}"} += $this->budgetRawMargin;
			
		return $xml;
	}
	
	
	
	
	
	/**
	 * Determine whether the current user can view data for a particular region
	 *
	 * @param string $region
	 * @return boolean $access;
	 */
	public function accessToRegion($region)
	{
		if ($region == "All")
		{
			if ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || ((currentuser::getInstance()->hasPermission("dashboard_saoEurope")) && 
				(currentuser::getInstance()->hasPermission("dashboard_saoNA"))))
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
		
		elseif ((currentuser::getInstance()->hasPermission("dashboard_saoGroup")) || 
			currentuser::getInstance()->hasPermission("dashboard_sao" . $region . "All"))
		{
			return true;
		}
		else
		{
			$buArr = $this->getBuList();
		
			foreach ($buArr as $bu)
			{
				if(currentuser::getInstance()->hasPermission("dashboard_sao" . $region . $bu))
				{
					return true;
				}
			}
			
			// if no permissions found, user doesn't have access
			return false;
		}
	}
	
		
	public function initialiseTableTotals()
	{
		$regions = array("All", "NA", "Europe");
		
		foreach ($regions as $region)
		{
			for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
			{	
				$j = $i + 1;
				$this->{"wk{$j}OrderValue{$region}"} = 0;
				$this->{"wk{$j}OrderMargin{$region}"} = 0;
				$this->{"wk{$j}SalesValue{$region}"} = 0;
				$this->{"wk{$j}SalesMargin{$region}"} = 0;
			}
			
			if (isset($this->saoDateCalcs->fiscalWeekDays))			
			{
				foreach($this->saoDateCalcs->fiscalWeekDays as $day)
				{
					$this->{"{$day[1]}OrderValue{$region}"} = 0;
					$this->{"{$day[1]}OrderMargin{$region}"} = 0;
					$this->{"{$day[1]}SalesValue{$region}"} = 0;
					$this->{"{$day[1]}SalesMargin{$region}"} = 0;
				}
			}
		}
	}
	
	
	public function displaySalesTotals($region)
	{
		$this->{"mtdTotalValue{$region}"} = (isset($this->{"mtdTotalSales{$region}"})) ? $this->{"mtdTotalSales{$region}"} : 0;
		$this->{"mtdTotalMargin{$region}"} = (isset($this->{"mtdTotalSalesMargin{$region}"})) ? $this->{"mtdTotalSalesMargin{$region}"} : 0;
	
		$xml = "<totals" . $region . ">";
		
			for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
			{			
				$j = $i + 1;
				
				$xml .= "<totalValue id=\"totalSalesWeek" . $j . "\">" . money_format('%.0n', $this->{"wk{$j}SalesValue{$region}"}) . "</totalValue>";
				$xml .= "<totalMargin id=\"totalSalesMarginWeek" . $j . "\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"wk{$j}SalesValue{$region}"}, $this->{"wk{$j}SalesMargin{$region}"})) . "</totalMargin>";
			}
			
			if (isset($this->saoDateCalcs->fiscalWeekDays))
			{
				foreach($this->saoDateCalcs->fiscalWeekDays as $day)
				{
					$xml .= "<totalValue id=\"totalSales" . $day[1] . "\">" . money_format('%.0n', $this->{"{$day[1]}SalesValue{$region}"}) . "</totalValue>";
					$xml .= "<totalMargin id=\"totalSalesMargin" . $day[1] . "\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"{$day[1]}SalesValue{$region}"}, $this->{"{$day[1]}SalesMargin{$region}"})) . "</totalMargin>";
				}	
			}
			
			$xml .= "<totalValue id=\"totalSalesMTD\">" . money_format('%.0n', $this->{"mtdTotalSales{$region}"}) . "</totalValue>";
			$xml .= "<totalMargin id=\"totalSalesMarginMTD\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotalSales{$region}"}, $this->{"mtdTotalSalesMargin{$region}"})) . "</totalMargin>";

			if (isset($this->{"mtdTotalBudget{$region}"}))
			{
				$budget = $this->{"mtdTotalBudget{$region}"};
				$budgetMargin = $this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotalBudget{$region}"}, $this->{"mtdTotalBudgetRawMargin{$region}"});	
			}
			else 
			{
				$budget = 0;
				$budgetMargin = 0;
			}

			$xml .= "<totalBudget>" . money_format('%.0n', $budget) . "</totalBudget>";
			$xml .= "<totalBudgetMargin>" . $this->formatMargin($budgetMargin) . "</totalBudgetMargin>";
		
		$xml .= "</totals" . $region . ">";
		
		return $xml;
	}	
	
	
	public function displaySAOTotals($region)
	{
		$this->{"mtdTotalValue{$region}"} = (isset($this->{"mtdTotal{$region}"})) ? $this->{"mtdTotal{$region}"} : 0;
		$this->{"mtdTotalMargin{$region}"} = (isset($this->{"mtdTotalMargin{$region}"})) ? $this->{"mtdTotalMargin{$region}"} : 0;
	
		$xml = "<totals" . $region . ">";
		
			for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
			{			
				$j = $i + 1;
				
				$xml .= "<totalValue id=\"totalWeek" . $j . "\">" . money_format('%.0n', $this->{"wk{$j}Value{$region}"}) . "</totalValue>";
				
				$xml .= "<totalMargin id=\"totalMarginWeek" . $j . "\">";
				
					if ($this->dataType == "Sales")
					{
						$xml .= $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"wk{$j}Value{$region}"}, $this->{"wk{$j}Margin{$region}"}));
					}
					else 
					{
						$xml .= $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"wk{$j}Value{$region}"}, $this->{"wk{$j}Margin{$region}"}));
					}
					
				$xml .= "</totalMargin>";
			}
			
			if (isset($this->saoDateCalcs->fiscalWeekDays))
			{
				foreach($this->saoDateCalcs->fiscalWeekDays as $day)
				{
					$xml .= "<totalValue id=\"total" . $day[1] . "\">" . money_format('%.0n', $this->{"{$day[1]}Value{$region}"}) . "</totalValue>";
					
					$xml .= "<totalMargin id=\"totalMargin" . $day[1] . "\">";
						
						if ($this->dataType == "Sales")
						{
							$xml .= $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"{$day[1]}Value{$region}"}, $this->{"{$day[1]}Margin{$region}"}));
						}
						else 
						{
							$xml .= $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"{$day[1]}Value{$region}"}, $this->{"{$day[1]}Margin{$region}"}));						
						}
					
					$xml .= "</totalMargin>";
				}	
			}
			
			$xml .= "<totalValue id=\"totalMTD\">" . money_format('%.0n', $this->{"mtdTotal{$region}"}) . "</totalValue>";
			
			$xml .= "<totalMargin id=\"totalMarginMTD\">";
			
				if ($this->dataType == "Sales")
				{
					$xml .= $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotal{$region}"}, $this->{"mtdTotalMargin{$region}"}));
				}
				else 
				{
					$xml .= $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"mtdTotal{$region}"}, $this->{"mtdTotalMargin{$region}"}));
				}
			
			$xml .= "</totalMargin>";

			if (isset($this->{"mtdTotalBudget{$region}"}))
			{
				$budget = $this->{"mtdTotalBudget{$region}"};
				
				// The budget is always for sales data, so we work out the budget margin as a sales margin
				$budgetMargin = $this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotalBudget{$region}"}, $this->{"mtdTotalBudgetRawMargin{$region}"});	
			}
			else 
			{
				$budget = 0;
				$budgetMargin = 0;
			}

			$xml .= "<totalBudget>" . money_format('%.0n', $budget) . "</totalBudget>";
			$xml .= "<totalBudgetMargin>" . $this->formatMargin($budgetMargin) . "</totalBudgetMargin>";
		
		$xml .= "</totals" . $region . ">";
		
		return $xml;
	}	
	
	
	public function displayTotals($region)
	{
		$this->{"mtdTotalOrders{$region}"} = (isset($this->{"mtdTotalOrders{$region}"})) ? $this->{"mtdTotalOrders{$region}"} : 0;
		$this->{"mtdTotalOrdersMargin{$region}"} = (isset($this->{"mtdTotalOrdersMargin{$region}"})) ? $this->{"mtdTotalOrdersMargin{$region}"} : 0;
		$this->{"mtdTotalSales{$region}"} = (isset($this->{"mtdTotalSales{$region}"})) ? $this->{"mtdTotalSales{$region}"} : 0;
		$this->{"mtdTotalSalesMargin{$region}"} = (isset($this->{"mtdTotalSalesMargin{$region}"})) ? $this->{"mtdTotalSalesMargin{$region}"} : 0;

	
		$xml = "<totals" . $region . ">";
		
			for ($i = 0; $i < $this->saoDateCalcs->weekCount; ++$i)
			{			
				$j = $i + 1;
	
				$xml .= "<totalOrders id=\"totalOrdersWeek" . $j . "\">" . money_format('%.0n', $this->{"wk{$j}OrderValue{$region}"}) . "</totalOrders>";
				$xml .= "<totalOrders id=\"totalOrdersMarginWeek" . $j . "\">" . $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"wk{$j}OrderValue{$region}"}, $this->{"wk{$j}OrderMargin{$region}"})) . "</totalOrders>";
				$xml .= "<totalSales id=\"totalSalesWeek" . $j . "\">" . money_format('%.0n', $this->{"wk{$j}SalesValue{$region}"}) . "</totalSales>";
				$xml .= "<totalSales id=\"totalSalesMarginWeek" . $j . "\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"wk{$j}SalesValue{$region}"}, $this->{"wk{$j}SalesMargin{$region}"})) . "</totalSales>";
			}
			
			if (isset($this->saoDateCalcs->fiscalWeekDays))
			{
				foreach($this->saoDateCalcs->fiscalWeekDays as $day)
				{
					$xml .= "<totalOrders id=\"totalOrders" . $day[1] . "\">" . money_format('%.0n', $this->{"{$day[1]}OrderValue{$region}"}) . "</totalOrders>";
					$xml .= "<totalOrders id=\"totalOrdersMargin" . $day[1] . "\">" . $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"{$day[1]}OrderValue{$region}"}, $this->{"{$day[1]}OrderMargin{$region}"})) . "</totalOrders>";
					$xml .= "<totalSales id=\"totalSales" . $day[1] . "\">" . money_format('%.0n', $this->{"{$day[1]}SalesValue{$region}"}) . "</totalSales>";
					$xml .= "<totalSales id=\"totalSalesMargin" . $day[1] . "\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"{$day[1]}SalesValue{$region}"}, $this->{"{$day[1]}SalesMargin{$region}"})) . "</totalSales>";
				}	
			}
			
			$xml .= "<totalOrders id=\"totalOrdersMTD\">" . money_format('%.0n', $this->{"mtdTotalOrders{$region}"}) . "</totalOrders>";
			$xml .= "<totalOrders id=\"totalOrdersMarginMTD\">" . $this->formatMargin($this->saoCalcs->getOrderMargin($this->margin, $this->{"mtdTotalOrders{$region}"}, $this->{"mtdTotalOrdersMargin{$region}"})) . "</totalOrders>";
			$xml .= "<totalSales id=\"totalSalesMTD\">" . money_format('%.0n', $this->{"mtdTotalSales{$region}"}) . "</totalSales>";
			$xml .= "<totalSales id=\"totalSalesMarginMTD\">" . $this->formatMargin($this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotalSales{$region}"}, $this->{"mtdTotalSalesMargin{$region}"})) . "</totalSales>";

			
//			switch ($region)
//			{
//				case "All":
//					$salesOrgs = "";
//					break;
//				case "NA":
//					$salesOrgs = NA_SALES_ORGS;
//					break;
//				case "Europe":
//					$salesOrgs = EUROPE_SALES_ORGS;
//			}
//			
//			
//			$bu = (isset($this->currentBu)) ? $this->currentBu : '';
//			$salesOrgs = (isset($this->currentSalesOrgs)) ? $this->currentSalesOrgs : '';
//			$currentSalesPersonId = (isset($this->currentSalesPersonId)) ? $this->currentSalesPersonId : '';
//			$currentCustomerId = (isset($this->currentCustomerId)) ? $this->currentCustomerId : '';
//			$currentSalesManagerId = (isset($this->currentSalesManagerId)) ? $this->currentSalesManagerId : '';
//			$currentKCG = (isset($this->currentKCG)) ? $this->currentKCG : '';
//			
//			$budget = $this->getBudget($this->saoDateCalcs->fiscalPeriod, '', $salesOrgs, '', '', $this->margin, '', '', $this->currency);
			
			if (isset($this->{"mtdTotalBudget{$region}"}))
			{
				$budget = $this->{"mtdTotalBudget{$region}"};
				$budgetMargin = $this->saoCalcs->getSalesMargin($this->margin, $this->{"mtdTotalBudget{$region}"}, $this->{"mtdTotalBudgetRawMargin{$region}"});	
			}
			else 
			{
				$budget = 0;
				$budgetMargin = 0;
			}

			$xml .= "<totalBudget>" . money_format('%.0n', $budget) . "</totalBudget>";
			$xml .= "<totalBudgetMargin>" . $this->formatMargin($budgetMargin) . "</totalBudgetMargin>";
		
		$xml .= "</totals" . $region . ">";
		
		return $xml;
	}	
	
	
	/**
	 * Returns a dataset of values for a completed week in the current fiscal period
	 *
	 * @param string $fromDate, string $toDate
	 * @return array $fields
	 */
	public function getWeekData($fromDate, $toDate)
	{
		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueWk, sum(incomingOrderValue" . $this->currency . ") as orderValueWk, " 
					. $this->marginSQL($this->margin, $this->currency) 
					. " 	FROM sisData AS s
					WHERE s.currentDate  BETWEEN '" . $fromDate . "' AND '" . $toDate . "' "
					. $this->getDataWhere();

		$this->saoMySqlNo++;
					
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);		
		
		$this->saomysqlno++;
		
		return $fields;
	}
	
	
	/**
	 * Returns a dataset of values for a completed week in the current fiscal period
	 *
	 * @param string $fromDate, string $toDate
	 * @return array $fields
	 */
	public function getWeekOrderData($fromDate, $toDate)
	{
		$sql = "SELECT sum(incomingOrderValue" . $this->currency . ") as orderValueWk, " 
			. $this->orderMarginSQL($this->margin, $this->currency) 
			. " 	FROM sisData AS s
			WHERE s.currentDate  BETWEEN '" . $fromDate . "' AND '" . $toDate . "' "
			. $this->getDataWhere();

		$this->saoMySqlNo++;
					
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);		
		
		$this->saomysqlno++;
		
		return $fields;
	}
	
	
	/**
	 * Returns a dataset of values for a completed week in the current fiscal period
	 *
	 * @param string $fromDate, string $toDate
	 * @return array $fields
	 */
	public function getWeekSalesData($fromDate, $toDate)
	{
		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueWk, " 
			. $this->salesMarginSQL($this->margin, $this->currency) 
			. " 	FROM sisData AS s
			WHERE s.currentDate  BETWEEN '" . $fromDate . "' AND '" . $toDate . "' "
			. $this->getDataWhere();

		$this->saoMySqlNo++;
					
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);		
		
		$this->saomysqlno++;
		
		return $fields;
	}
	
	
	/**
	 * Returns a dataset of values for a day in the current week of the current fiscal period
	 *
	 * @param string $day
	 * @return array $fields
	 */
	public function getDayData($day)
	{
		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueDay, sum(incomingOrderValue" . $this->currency . ") as orderValueDay, "
				. $this->marginSQL($this->margin, $this->currency) .
				" 	FROM sisData AS s
				WHERE s.currentDate  = '" . $day . "' "
				. $this->getDataWhere();
			
		$this->saoMySqlNo++;
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		return $fields;
	}
	
	
	/**
	 * Returns a dataset of values for a day in the current week of the current fiscal period
	 *
	 * @param string $day
	 * @return array $fields
	 */
	public function getDayOrderData($day)
	{
		$sql = "SELECT sum(incomingOrderValue" . $this->currency . ") as orderValueDay, "
				. $this->orderMarginSQL($this->margin, $this->currency) .
				" FROM sisData AS s
				WHERE s.currentDate  = '" . $day . "' "
				. $this->getDataWhere();
			
		$this->saoMySqlNo++;
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		return $fields;
	}
	
	
	/**
	 * Returns a dataset of values for a day in the current week of the current fiscal period
	 *
	 * @param string $day
	 * @return array $fields
	 */
	public function getDaySalesData($day)
	{
		$sql = "SELECT sum(salesValue" . $this->currency . ") as salesValueDay, "
				. $this->salesMarginSQL($this->margin, $this->currency) .
				" FROM sisData AS s
				WHERE s.currentDate  = '" . $day . "' "
				. $this->getDataWhere();
			
		$this->saoMySqlNo++;
				
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		$fields = mysql_fetch_array($dataset);
		return $fields;
	}
	
	
	/**
	 * Gets the where clause for data in the table
	 *
	 * @return string $where
	 */
	public function getDataWhere()
	{
		$where = "	AND s.custAccGroup = 1 
					AND s.versionNo = '000' ";
			
		$where .= (isset($this->currentBu)) ? " AND s.newMrkt = '" . $this->currentBu . "'" :  " AND s.newMrkt != 'Interco'";
		$where .= (isset($this->currentRegion)) ? " AND s.salesOrg IN(" . $this->currentSalesOrgs . ")" : "";
		$where .= (isset($this->currentSalesPersonId)) ? " AND s.salesEmp = '" . $this->currentSalesPersonId . "'" : "";
		$where .= (isset($this->currentSalesManagerId)) ? " AND sm.id = '" . $this->currentSalesManagerId . "'" : "";
		$where .= (isset($this->currentCustomerId)) ? " AND s.customerNo = '" . $this->currentCustomerId . "'" : "";
		$where .= (isset($this->currentKCG)) ? " AND s.kcg = '" . $this->currentKCG . "'" : "";
		
		return $where;
	}
	
}

?>