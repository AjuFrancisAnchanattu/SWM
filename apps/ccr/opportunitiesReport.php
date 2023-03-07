<?php

class opportunitiesReport extends page 
{
	//private $header;
	private $filtersForm;
	
	private $materialKey;
	private $priority;
	private $annual_volume;
	private $fiscal_volume;
	private $volume_units;
	private $annual_value;
	private $fiscal_value;
	private $budget_value;
	private $success_chance;
	private $project_start_date;
	private $project_owner;
	private $site;
	private $customer_group;
	private $business_unit;
	private $nextAction;
	private $submit;
	
	private $reportForm;
	private $reportName;
	private $bookmarkReport;


	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/menu.xml");

		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			session::clear();
		}
		
		$this->add_output("<CCR_opportunitiesReport>");
		
		
		$this->defineFiltersForm();
		$this->filtersForm->processPost();
		$this->defineReportForm();
		$this->reportForm->processPost();
		
		
		if (isset($_POST['action']))
		{
			if (substr($_POST['action'],0,13) == 'removeFilter_')
			{
				$this->filtersForm->get("filters")->removeFilter(substr($_POST['action'],13));
				$this->filtersForm->get("filters")->addFilter($this->filtersForm);
			}
		}
		
		
		//adds any filters selected in the combo box (filters)
		
		if (isset($_POST['action']))
		{
			if ($_POST['action'] == 'runReport')
			{
				$this->filtersForm->get("filters")->saveFilters($this->filtersForm);
				header("Location: opportunitiesReport?report=" . $this->filtersForm->get("filters")->getReportID());
				exit();
			}
			
			if ($_POST['action'] == 'addFilter')
			{
				// get anything posted by the form
				
				$this->filtersForm->get("filters")->addFilter($this->filtersForm);
				$this->filters->setValue("");
			}
			
			if ($_POST['action'] == 'bookmarkReport')
			{
				$this->filtersForm->get("filters")->bookmarkReport($_GET['report'],$this->reportName->getValue(),"CCR","bookmarkedReports");	
			}
		}
		
		
		
		if (isset($_REQUEST['orderBy']) && isset($_REQUEST['type']))
		{
			$orderBy = $_REQUEST['orderBy'];
			$type = $_REQUEST['type'];
		}
		else 
		{
			$orderBy = "materialKey";
			$type = "ASC";
		}
		

		
		if (!isset($_POST['action']) || ($_POST['action']=='bookmarkReport'))
		{
			if (isset($_GET['report']))
			{
				$this->add_output("<reportForm>");
				
				$this->add_output($this->reportForm->output());
				
				$this->add_output("</reportForm>");
				
				$this->filtersForm->get("filters")->loadFilters($this->filtersForm,$_GET['report']);
				
				
				$query = $this->filters->generateReportQuery($_GET['report']);
				
								$resultCountData = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT count(*) as resultCount FROM opportunity $query");
				if ($fields = mysql_fetch_array($resultCountData))
				{
					$resultCount = $fields['resultCount'];
					$this->xml .= "<resultCount>" . $resultCount . "</resultCount>";
				}
				
				if (isset($_GET['offset']))
				{
					$resultStart = $_GET['offset'];
				}
				else 
				{
					$resultStart = 0;
				}
				
				if ($resultCount > ($resultStart+20))
				{
					$resultEnd = ($resultStart+20);
				}
				else 
				{
					$resultEnd = $resultCount;
				}
					
				$this->xml .= "<resultStart>" . ($resultStart+1) . "</resultStart>";
				$this->xml .= "<resultEnd>" . ($resultEnd) . "</resultEnd>";
				
				$pageCount = ceil($resultCount / 20);
				
				$this->xml .= "<pageCount>" . $pageCount . "</pageCount>";
				
				for ($clown=1;$clown<=$pageCount;$clown++)
				{
					$this->xml .= "<reportPage>";
					$this->xml .= "<number>" . $clown . "</number>";
					$this->xml .= "<offset>" . ($clown-1)*20 . "</offset>";
					$this->xml .= "<reportID>" . $_GET['report'] . "</reportID>";
					$this->xml .= "<orderBy>" . $orderBy . "</orderBy>";
					$this->xml .= "<type>" . $type . "</type>";
					$this->xml .= "<selected>";
					$this->xml .= ((($clown-1)*20)==$resultStart) ? "yes" : "no";
					$this->xml .= "</selected>";
					$this->xml .= "</reportPage>";
				}
				
					
				
				$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT opportunity.id, materialKey, priority, concat(annual_volume_quantity, ' ', annual_volume_measurement) AS annual_volume, concat(fiscal_volume_quantity, ' ', fiscal_volume_measurement) AS fiscal_volume, concat(annual_value_quantity, ' ', annual_value_measurement) AS annual_value, concat(fiscal_value_quantity, ' ', fiscal_value_measurement) AS fiscal_value, concat(budget_value_quantity, ' ', budget_value_measurement) AS budget_value, success_chance, project_start_date, project_owner, site, customer_group, business_unit, MAX(targetCompletion) AS NEXT_ACTION FROM action RIGHT JOIN opportunity on opportunity.id=parentId $query GROUP BY id ORDER BY $orderBy $type LIMIT $resultStart, 20");
					
				for ($i=1;$i<mysql_numfields($dataset);$i++)	//$i=1 to avoid the opportunity ID
				{
					$this->xml .= "<field>";
					$this->xml .= "<fieldName>" . translate::getInstance()->translate(strtoupper(mysql_field_name($dataset,$i))) . "</fieldName>";
					$this->xml .= "<fieldKey>" . mysql_field_name($dataset,$i) . "</fieldKey>";
					$this->xml .= "<reportID>" . $_GET['report'] . "</reportID>";
					$this->xml .= "<offset>" . $resultStart . "</offset>";
					$this->xml .= "</field>";
				}
				
				$todayDate = date("Ymd");

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<opportunity>";
						$this->xml .= "<id>" . $fields['id'] . "</id>\n";
						$this->xml .= "<materialKey>" . $fields['materialKey'] . "</materialKey>\n";
						$this->xml .= "<priority>" . $fields['priority'] . "</priority>\n";
						$this->xml .= "<annual_volume>" . $fields['annual_volume'] . "</annual_volume>\n";
						$this->xml .= "<fiscal_volume>" . $fields['fiscal_volume'] . "</fiscal_volume>\n";
						$this->xml .= "<annual_value>" . $fields['annual_value'] . "</annual_value>\n";
						$this->xml .= "<fiscal_value>" . $fields['fiscal_value'] . "</fiscal_value>\n";
						$this->xml .= "<budget_value>" . $fields['budget_value'] . "</budget_value>\n";
						$this->xml .= "<success_chance>" . page::xmlentities($fields['success_chance']) . "</success_chance>\n";
						$this->xml .= "<project_start_date>" . page::transformDateForPHP($fields['project_start_date']) . "</project_start_date>\n";
						$this->xml .= "<site>" . page::xmlentities($fields['site']) . "</site>\n";
						$this->xml .= "<project_owner>" . page::xmlentities($fields['project_owner']) . "</project_owner>\n";
						$this->xml .= "<customer_group>" . page::xmlentities($fields['customer_group']) . "</customer_group>\n";
						$this->xml .= "<business_unit>" . page::xmlentities($fields['business_unit']) . "</business_unit>\n";
						
						
						$nextActionDate = str_replace("-","",$fields['NEXT_ACTION']);
						
						$actionStatus = "";
						if (!isset($nextActionDate) || $nextActionDate == "")
						{
							$actionStatus = "NONE";
							$actionColour = "#FFFFFF";
						}
						elseif ($nextActionDate <= $todayDate)
						{
							$actionStatus = "OVERDUE";
							$actionColour = "#FF0000";
						}
						elseif ($nextActionDate <= ($todayDate + 14))
						{
							$actionStatus = "DUE";
							$actionColour = "#FF9933";
						}
						else 
						{
							$actionStatus = "PENDING";#
							$actionColour = "#00FF00";
						}
							                  
						$this->xml .= "<nextAction>" . $actionStatus . "</nextAction>";
						$this->xml .= "<nextActionColour>" . $actionColour . "</nextActionColour>";
						
					$this->xml .= "</opportunity>";
				}
			}
		}
		
		$this->setDebug(true);
		$this->add_output("<filtersForm>");
		$this->add_output($this->filtersForm->output());
		$this->add_output("</filtersForm>");
		
		if (isset($this->xml))
		{
			$this->add_output($this->xml);
		}
		$this->add_output("</CCR_opportunitiesReport>");
		
		$this->output('./apps/ccr/xsl/report.xsl');
		
	}
	
	
	
	
	
	
	
	
	private function defineReportForm()
	{
		$this->reportForm = new form("opportunitiesReport");
		$this->reportForm->setStoreInSession(true);
		$this->reportForm->loadSessionData();
		
		$reportGroup = new group("reportGroup");
		
		$this->reportName = new textbox("reportName");	//report name
		$this->reportName->setRowTitle("Name of Report");
		$this->reportName->setDataType("string");
		$this->reportName->setLength(250);
		$reportGroup->add($this->reportName);
		
		$this->bookmarkReport = new submit("bookmarkReport");
		$this->bookmarkReport->setAction("bookmarkReport");
		$this->bookmarkReport->setDataType("ignore");
		$this->bookmarkReport->setValue("Bookmark Report");
		$reportGroup->add($this->bookmarkReport);
		
		$this->reportForm->add($reportGroup);
	}
	
	private function defineFiltersForm()
	{
		$this->filtersForm = new form("opportunitiesFilters");
		$this->filtersForm->setStoreInSession(true);
		$this->filtersForm->loadSessionData();
		
		$filtersGroup = new group("filtersGroup");
		
		
		$this->filters = new filterControl("filters");
		$this->filters->setDataType("string");
		$this->filters->setLength(250);
		$this->filters->setXMLSource("./apps/ccr/xml/filters.xml");
		$this->filters->setRowTitle("Available Filters");
		$filtersGroup->add($this->filters);
		
		$this->materialKey = new filterList("materialKey");
		$this->materialKey->setSQLSource("CCR","SELECT DISTINCT materialKey AS name, materialKey AS data FROM opportunity ORDER BY name ASC");
		$this->materialKey->setFilterRowTitle("Material Key");
		$filtersGroup->add($this->materialKey);
		
		$this->priority = new filterList("priority");
		$this->priority->setSQLSource("CCR","SELECT DISTINCT priority AS name, priority AS data FROM opportunity ORDER BY name ASC");
		$this->priority->setFilterRowTitle("Priority");
		$filtersGroup->add($this->priority);
		
		$this->annual_volume_quantity = new filterBetweenNumber("annual_volume_quantity", $this->filtersForm);
		$this->annual_volume_quantity->setFilterRowTitle("Annual Volume");
		$filtersGroup->add($this->annual_volume_quantity);
		
		$this->fiscal_volume_quantity = new filterBetweenNumber("fiscal_volume_quantity", $this->filtersForm);
		$this->fiscal_volume_quantity->setFilterRowTitle("Fiscal Volume");
		$filtersGroup->add($this->fiscal_volume_quantity);
		
		$this->annual_value_quantity = new filterBetweenNumber("annual_value_quantity", $this->filtersForm);
		$this->annual_value_quantity->setFilterRowTitle("Annual Value");
		$filtersGroup->add($this->annual_value_quantity);
		
		$this->fiscal_value_quantity = new filterBetweenNumber("fiscal_value_quantity", $this->filtersForm);
		$this->fiscal_value_quantity->setFilterRowTitle("Fiscal Value");
		$filtersGroup->add($this->fiscal_value_quantity);
		
		$this->budget_value_quantity = new filterBetweenNumber("budget_value_quantity", $this->filtersForm);
		$this->budget_value_quantity->setFilterRowTitle("Budget Value");
		$filtersGroup->add($this->budget_value_quantity);
		
		$this->success_chance = new filterBetweenNumber("success_chance", $this->filtersForm);
		$this->success_chance->setFilterRowTitle("Success Chance");
		$filtersGroup->add($this->success_chance);
		
		$this->project_start_date = new filterBetweenDate("project_start_date", $this->filtersForm);
		$this->project_start_date->setFilterRowTitle("Project Start Date");
		$filtersGroup->add($this->project_start_date);
		
		$this->project_owner = new filterList("project_owner");
		$this->project_owner->setFilterRowTitle("Project Owner / Leader");
		$this->project_owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) as name, ntlogon as data from membership.employee, CCR.opportunity where project_owner = ntlogon ORDER BY name ASC");
		$filtersGroup->add($this->project_owner);
		
		$this->site = new filterList("site");
		$this->site->setFilterRowTitle("Site");
		$this->site->setSQLSource("membership","SELECT name, name AS data FROM sites ORDER BY name ASC");
		$filtersGroup->add($this->site);
		
		$this->customer_group = new filterList("customer_group");
		$this->customer_group->setFilterRowTitle("Customer Group");
		$this->customer_group->setSQLSource("CCR","SELECT DISTINCT customer_group AS name, customer_group AS data FROM opportunity ORDER BY name ASC");
		$filtersGroup->add($this->customer_group);
		
		$this->business_unit = new filterList("business_unit");
		$this->business_unit->setFilterRowTitle("Business Unit");
		$this->business_unit->setSQLSource("CCR","SELECT DISTINCT business_unit AS name, business_unit AS data FROM opportunity ORDER BY name ASC");
		$filtersGroup->add($this->business_unit);
		
		//$this->nextAction = new filterList("nextAction");
		//$this->nextAction->setFilterRowTitle("Next Action Status");
		//$this->nextAction->setXMLSource("");
		//$this->filtersForm->add($this->nextAction);
		
		
		$this->nextAction = new filterList("nextAction");
		$this->nextAction->setArraySource(array("PENDING","DUE","OVERDUE","NONE"));
		$this->nextAction->setFilterRowTitle("Next Action Status");
		$filtersGroup->add($this->nextAction);
		
		
		$runReport = new submit("runReport");
		$runReport->setAction("runReport");
		$runReport->setDataType("ignore");
		$runReport->setValue("Run Report");
		$filtersGroup->add($runReport);
		
		$this->filtersForm->add($filtersGroup);
	}

}

?>