<?php

class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	function __construct()
	{
		
		parent::__construct();
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
			
		
		
		
		
		
		
		
		
		
		/*$this->report = new searchclass("ccrReport");
		$this->report->setAvailableFilters("./apps/ccr/xml/reportFilters.xml");
		//$this->report->setDefaultColumns("./apps/ccr/xml/reportColumns.xml");
		$this->report->setDatabase("CCR");
		$this->report->setTable("report");
		$this->defineReportFilters();
		$this->defineColumns();
		*/
		
		$this->defineChooseReportForm();
		
		$this->chooseReportForm->loadSessionData();
		
		$this->chooseReportForm->processPost();
		$this->chooseReportForm->validate();
		
		
		
		$this->defineSelectedFilters();
		
		$this->selectedFilters->form->loadSessionData();
		$this->defineAddFiltersForm();
		$this->selectedFilters->processPost();
		//$this->selectedFilters->form->putValuesInSession();
		
		
		
		
		
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view')
		{
			if (!isset($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]))
			{
				$this->redirect("search?");
			}
			
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]); $i++)
			{
				$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][$i])->setVisible(true);
			}
			
			$this->showResults();
		}
		else 
		{
			
			$this->add_output("<CCRsearch>");
			
			
			
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'run')
			{
				if ($this->selectedFilters->form->validate())
				{
					$this->redirect("search?action=view");
				}
				else 
				{
					$this->add_output("<error />");
				}
			}
			
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'changeReportType')
			{
				$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array();
			}
			
			
			
			if (!isset($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]))
			{
				$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array();
			}
			
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'addFilter')
			{
				$this->addFiltersForm->processPost();
				
				$filters = explode("||", $this->addFiltersForm->get('filters')->getValue());
				
				for ($i=0; $i < count($filters); $i++)
				{
					if (!in_array($filters[$i], $_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]) && $filters[$i] != "")
					{
						$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][] = $filters[$i];
					}
				}
				
				$this->addFiltersForm->get('filters')->setValue("");
			}
			
			if (isset($_REQUEST['action']) && strstr($_REQUEST['action'], 'removeFilter'))
			{
				$remove = substr($_REQUEST['action'], 13, strlen($_REQUEST['action']) - 13);
				
				page::addDebug("remove $remove", __FILE__, __LINE__);
				
				$selectedFilters = $_SESSION['apps'][$GLOBALS['app']]["selectedFilters"];
				
				$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array();
				
				for ($i=0; $i < count($selectedFilters); $i++)
				{
					if ($remove != $selectedFilters[$i])
					{
						$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][] = $selectedFilters[$i];
					}
				}
			}
			
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]); $i++)
			{
				$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][$i])->setVisible(true);
			}
			
			// edit
			$snapins = new snapinGroup('ccr_search');
			$snapins->register('apps/ccr', 'bookmarks', true, true);
	
			
			
			
			
			
			$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
			
			
			
			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");
			$this->add_output("<selectedFilters>");
			$this->add_output($this->selectedFilters->getOutput());
			$this->add_output("</selectedFilters>");
			
			$this->add_output("</CCRsearch>");
				
			
			
			
			// if post
			
			// save, redirect to view			
			
		}
		
		

		$this->output('./apps/ccr/xsl/search.xsl');	
	}
	
	
	private function showResults()
	{
		if (isset($_REQUEST['load']))
		{
			// load saved stuff from db into session...
			
			$this->redirect("search?action=view");
		}
		
		
		
		
		//$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM action WHERE parentId = " . $this->id . " AND type='ccr' ORDER BY id");
		
		
		
		$results = new searchResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("CCR");
		
		$results->setBaseQuery("SELECT * FROM report");
		$results->setOrderBy("id");
		
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary_direct':
				
				$results->setBaseQuery("SELECT * FROM report WHERE (report.typeOfCustomer != 'customer_distributor')");
				$results->setOrderBy("id");
				
				$results->addColumn(new ccrIDColumn("report.`id`", "id", "id", true));
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "owner", "report_owner", true));
				$results->addColumn(new column("report.`sapNumber`", "sapNumber", "sap_account_number", true));
				$results->addColumn(new column("report.`directCustomerName`", "directCustomerName", "customer", true));
				$results->addColumn(new column("report.`directCustomerCountry`", "directCustomerCountry", "country", true));
				$results->addColumn(new ccrDateColumn("report.`contactDate`", "contactDate", "contactDate", true));
				$results->addColumn(new ccrTranslateColumn("report.`contactType`", "contactType", "contactType", true));
				//$results->addColumn(new ccrMaterialColumn("CONCAT (SELECT material.`id` AS materialId, material.`materialKey` FROM material WHERE ccrId = report.id)", "materialKey", "material_group", true));
				
				break;
				
			case 'summary_indirect':

				$results->setBaseQuery("SELECT * FROM report INNER JOIN material ON report.id = material.ccrId WHERE (report.typeOfCustomer = 'customer_distributor')");
				$results->setOrderBy("id");				
							
				$results->addColumn(new ccrIDColumn("report.`id`", "id", "id", true));
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "owner", "report_owner", true));
				$results->addColumn(new column("report.`sapNumber`", "sapNumber", "sap_account_number", true));
				$results->addColumn(new column("report.`directCustomerName`", "directCustomerName", "direct_customer", true));
				$results->addColumn(new column("report.`directCustomerCountry`", "directCustomerCountry", "direct_customer_country", true));
				$results->addColumn(new column("report.`name`", "name", "indirect_customer", true));
				$results->addColumn(new column("report.`country`", "country", "country", true));
				$results->addColumn(new ccrDateColumn("report.`contactDate`", "contactDate", "contactDate", true));
				$results->addColumn(new ccrTranslateColumn("report.`contactType`", "contactType", "contactType", true));
				
				break;
				
			case 'analysis_material':
				
				
				$results->setBaseQuery("SELECT * FROM material INNER JOIN report ON report.id = material.ccrId");
				
				$results->addColumn(new ccrIDColumn("report.`id`", "id", "id", true));
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "owner", "report_owner", true));
				$results->addColumn(new column("report.`sapNumber`", "sapNumber", "sap_account_number", true));
				$results->addColumn(new column("report.`directCustomerName`", "directCustomerName", "direct_customer", true));
				$results->addColumn(new column("report.`directCustomerCountry`", "directCustomerCountry", "country", true));
				$results->addColumn(new column("material.`productFamily`", "productFamily", "product_family", true));
				$results->addColumn(new ccrMaterialColumn("material.`id` AS materialId, material.`materialKey`", "materialKey", "material_group", true));
				$results->addColumn(new column("material.`incomeQuantity`", "incomeQuantity", "annual_potential", true));
				$results->addColumn(new column("material.`volume_quantity`", "volumeQuantity", "annual_potential_volume", true));
				$results->addColumn(new column("material.`volume_measurement`", "volumeMeasurement", "annual_potential_volume_unit", true));
				$results->addColumn(new column("material.`competitorName`", "competitorName", "competitor_name", true));
				$results->addColumn(new column("material.`competitorProductCode`", "competitorProductCode", "competitor_Product", true));
				break;
				
			case 'customer_survey':
				
				$results->addColumn(new ccrIDColumn("report.`id`", "id", "id", true));
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "owner", "report_owner", true));
				$results->addColumn(new column("report.`sapNumber`", "sapNumber", "sap_account_number", true));
				$results->addColumn(new column("report.`name`", "name", "indirect_customer", true));
				$results->addColumn(new column("report.`country`", "country", "country", true));
				$results->addColumn(new column("material.`productFamily`", "productFamily", "product_family", true));
				$results->addColumn(new ccrMaterialColumn("material.`id` AS materialId, material.`materialKey`", "materialKey", "material_group", true));
				$results->addColumn(new column("material.`incomeQuantity`", "incomeQuantity", "annual_potential", true));
				$results->addColumn(new column("material.`volume_quantity`", "volumeQuantity", "annual_potential_volume", true));
				$results->addColumn(new column("material.`volume_measurement`", "volumeMeasurement", "annual_potential_volume_unit", true));
				break;
				
			case 'activity':
				/*
				SELECT owner AS `report.owner`, COUNT(id) As num,
(SELECT COUNT(id) FROM report r WHERE contactType='personal' AND owner = `report.owner`) AS personal,
(SELECT COUNT(id) FROM report r WHERE contactType='phone_call' AND owner = `report.owner`) AS phone_call,
(SELECT COUNT(id) FROM report r WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`) AS other
FROM report r GROUP BY owner*/
				
				$results->setBaseQuery("SELECT * FROM report GROUP BY owner");
				
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "report.owner", "sales_employee", true));
				$results->addColumn(new column("COUNT(id)", "num_of_reports", "num_of_reports", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='personal' AND owner = `report.owner`)", "num_of_personal", "num_of_personal", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='phone_call' AND owner = `report.owner`)", "num_of_phone", "num_of_phone", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`)", "num_of_other", "num_of_other", true));
				break;
		}
		
		
		
		
		
		$results->performQuery();
			
		$this->add_output($results->getOutput());
	}
	
	
	
	private function defineChooseReportForm()
	{
		$this->chooseReportForm = new form("chooseReportForm");
		$this->chooseReportForm->setStoreInSession(true);
		$default = new group("default");
		
		$reportType = new radio("reportType");
		//$reportType->setTable("customer");
		$reportType->setDataType("string");
		$reportType->setLength(50);
		$reportType->setRequired(true);
		
		$data = array(
			array('value' => 'summary_direct', 'display' => 'Summary of contacts with direct customers'),
			array('value' => 'summary_indirect', 'display' => 'Summary of contacts with indirect customers')
			
		);
		
		if (currentuser::getInstance()->hasPermission("admin") || currentuser::getInstance()->hasPermission("ccr_admin")) {
			$data[] = array('value' => 'analysis_material', 'display' => 'Analysis by Material Group');
			$data[] = array('value' => 'customer_survey', 'display' => 'Customer Survery reports');
			$data[] = array('value' => 'activity', 'display' => 'Activity report');
		}
		
		
		$reportType->setArraySource($data);
		$reportType->setValue("summary_direct");
		$reportType->setRowTitle("report_type");
		$reportType->setPostBack("changeReportType");
		$default->add($reportType);
		
		$this->chooseReportForm->add($default);
	}
	

	
	private function defineAddFiltersForm()
	{
		$this->addFiltersForm = new form("addFilters");
		$default = new group("default");
		
		$reportType = new availableFiltersList("filters");
		$reportType->setDataType("string");
		$reportType->setLength(50);
		$reportType->setRequired(true);
				
		/*$reportType->setArraySource(array(
			array('name' => 'analysis_period', 'value' => 'Analysis Period'),
			array('name' => 'sales_employee', 'value' => 'Sales Employee'),
			array('name' => 'sap', 'value' => 'SAP Customer'),
			array('name' => 'bla', 'value' => 'Bla')
			
		));*/
		
		$reportType->setFilterObject($this->selectedFilters);
		
		$reportType->setRowTitle("filter_name");
		$default->add($reportType);
		
		$this->addFiltersForm->add($default);
	}
	
	
	private function defineSelectedFilters()
	{
		$reportType = $this->chooseReportForm->get('reportType')->getValue();
		
		$this->selectedFilters = new selectedFiltersList();
		
		$analysisPeriod = new filterDateRange("analysisPeriod");
		
		if ($reportType == 'activity') 
		{
			$analysisPeriod->setRowType("row");
			$analysisPeriod->setVisible(true);
		}
		
		//$analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, CCR.report WHERE owner = ntlogon ORDER BY name ASC");
		$analysisPeriod->setField("report.contactDate");
		$analysisPeriod->setRowTitle("analysis_Period");
		$analysisPeriod->setRequired(true);
		$this->selectedFilters->add($analysisPeriod);
		
		
		
		if ($reportType == 'summary_direct')
		{
			$where = "(report.typeOfCustomer != 'customer_distributor')";
		}
		else 
		{
			$where = "(report.typeOfCustomer = 'customer_distributor')";
		}
		
		
		
		
		
		
		$owner = new filterCombo("owner");
		$owner->setField("report.owner");
		$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, CCR.report WHERE $where AND owner = ntlogon ORDER BY name ASC");
		$owner->setRowTitle("sales_employee");
		$this->selectedFilters->add($owner);
		
		
		
		
		
		
		if ($reportType == 'summary_direct' || $reportType == 'summary_indirect' || $reportType == 'analysis_material' || $reportType == 'customer_survey')
		{
			$sapNumber = new filterCombo("sapNumber");
			$sapNumber->setField("report.sapNumber");
			$sapNumber->setSQLSource("CCR","SELECT DISTINCT sapNumber AS name, sapNumber AS data FROM report WHERE $where AND sapNumber > 0 ORDER BY name ASC");
			
			if ($reportType == 'summary_indirect')
			{
				$sapNumber->setRowTitle("distributor_sap_account_number");
			}
			else 
			{
				$sapNumber->setRowTitle("sap_account_number");
			}
			
			$this->selectedFilters->add($sapNumber);
			
			if ( $reportType == 'summary_indirect')
			{
				$name = new filterCombo("direct_name");
				$name->setField("report.directCustomerName");
				$name->setSQLSource("CCR","SELECT DISTINCT directCustomerName AS name, directCustomerName AS data FROM report WHERE $where ORDER BY name ASC");
				$name->setRowTitle("direct_customer_name");
				$this->selectedFilters->add($name);
			}
			
			$name = new filterCombo("name");
			$name->setField("report.name");
			$name->setSQLSource("CCR","SELECT DISTINCT name AS name, name AS data FROM report WHERE $where ORDER BY name ASC");
			
			if ($reportType == 'summary_indirect')
			{
				$name->setRowTitle("indirect_customer_name");
			}
			else 
			{
				$name->setRowTitle("direct_customer_name");
			}
			$this->selectedFilters->add($name);
			
			$country = new filterCombo("country");
			$country->setField("report.country");
			$country->setSQLSource("CCR","SELECT DISTINCT country AS name, country AS data FROM report WHERE $where AND country NOT IN ('', ' ') ORDER BY name ASC");
			$country->setRowTitle("customer_country");
			$this->selectedFilters->add($country);
			
			
		
		}
		
		
		
		
		if ($reportType == 'analysis_material')
		{
			$materialGroup = new filterCombo("material_group");
			$materialGroup->setField("material.materialKey");
			$materialGroup->setSQLSource("CCR","SELECT DISTINCT materialKey AS name, materialKey AS data FROM material WHERE materialKey NOT IN ('', ' ') ORDER BY name ASC");
			$materialGroup->setRowTitle("material_group");
			$this->selectedFilters->add($materialGroup);
		}
		
		if ($reportType == 'analysis_material' || $reportType == 'customer_survey')
		{
			$productRange = new filterCombo("productFamily");
			$productRange->setField("material.productFamily");
			$productRange->setSQLSource("CCR","SELECT DISTINCT productFamily AS name, productFamily AS data FROM material WHERE productFamily NOT IN ('', ' ') ORDER BY name ASC");
			$productRange->setRowTitle("product_range");
			$this->selectedFilters->add($productRange);
			
			/*$manufacturingSite = new filterCombo("manufacturingSite");
			$manufacturingSite->setField("report.manufacturingSite");
			$manufacturingSite->setSQLSource("CCR","SELECT DISTINCT country AS name, country AS data FROM report WHERE country NOT IN ('', ' ') ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);*/
		}
		
		if ($reportType == 'analysis_material')
		{
			$competitorName = new filterCombo("competitorName");
			$competitorName->setField("material.competitorName");
			$competitorName->setSQLSource("CCR","SELECT DISTINCT competitorName AS name, competitorName AS data FROM material WHERE competitorName NOT IN ('', ' ') ORDER BY name ASC");
			$competitorName->setRowTitle("competitor_name");
			$this->selectedFilters->add($competitorName);
			
			$competitorProduct = new filterCombo("competitorProductCode");
			$competitorProduct->setField("material.competitorProductCode");
			$competitorProduct->setSQLSource("CCR","SELECT DISTINCT competitorProductCode AS name, competitorProductCode AS data FROM material WHERE competitorProductCode NOT IN ('', ' ') ORDER BY name ASC");
			$competitorProduct->setRowTitle("competitor_product");
			$this->selectedFilters->add($competitorProduct);
		}
		
			
			
		
			
		
		
		
	/*	$reportDate = new filterBetweenDate("reportDate");
		$reportDate->setFilterRowTitle("report_date");
		$this->seletectedFilters->add($reportDate);
		
		$contactDate = new filterBetweenDate("contactDate");
		$contactDate->setFilterRowTitle("contact_date");
		$this->seletectedFilters->add($contactDate);
		
		$contactType = new filterList("contactType");
		$contactType->setSQLSource("CCR","SELECT DISTINCT contactType AS name, contactType AS data FROM report ORDER BY contactType ASC");
		$contactType->setFilterRowTitle("contact_type");
		$this->seletectedFilters->add($contactType);
		
		$materialKey = new filterList("materialKey");
		$materialKey->setSQLSource("CCR","SELECT DISTINCT materialKey AS name, materialKey AS data FROM material ORDER BY MaterialKey ASC");
		$materialKey->setFilterRowTitle("material_key");
		$this->seletectedFilters->add($tmaterialKey);*/
	}

	
	
	/*private function defineReportFilters()
	{
		$report = new group("report");
		$report->setBorder(false);
		
		$material = new group("material");
		$material->setBorder(false);
		
		
		
		$this->report->add($report);
		$this->report->add($material);
	}*/
	/*
	
	private function defineColumns()
	{
		$id = new column("id");
		$id->setDisplayName("id");
		$id->setTable("report");
		$this->report->addColumn($id);
		
		$owner = new columnNTLogon("owner");
		$owner->setDisplayName("owner");
		$owner->setTable("report");
		$this->report->addColumn($owner);
		
		$report_date = new columnDate("reportDate");
		$report_date->setDisplayName("report_date");
		$report_date->setTable("report");
		$this->report->addColumn($report_date);
		
		$contact_date = new columnDate("contactDate");
		$contact_date->setDisplayName("contact_date");
		$contact_date->setTable("report");
		$this->report->addColumn($contact_date);
		
		$contact_type = new column("contactType");
		$contact_type->setDisplayName("contact_type");
		$contact_type->setTable("report");
		$this->report->addColumn($contact_type);
		
		$existing_new_business = new column("existingNewBusiness");
		$existing_new_business->setDisplayName("existing_new_business");
		$existing_new_business->setTable("report");
		$this->report->addColumn($existing_new_business);
		
		$status = new columnCCRstatus("status");
		$status->setDisplayName("status");
		$status->setTable("report");
		$this->report->addColumn($status);
		
		$customer = new column("name");
		$customer->setDisplayName("customer");
		$customer->setTable("report");
		$this->report->addColumn($customer);
		
		
		
	}*/
}

class ccrOwnerColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= usercache::getInstance()->get($fields[$this->getName()])->getName();
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ccrDateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= page::transformDateForPHP($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ccrTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ccrIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/ccr/index?id=" . $fields[$this->getName()] . "\">" . $fields[$this->getName()] . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}

class ccrMaterialColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/ccr/view?material=" . $fields['materialId'] . "\">" . page::xmlentities($fields['materialKey']) . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}

?>