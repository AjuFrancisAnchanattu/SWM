<?php

require 'lib/employee.php';

class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	function __construct()
	{
		
		parent::__construct();
		//$this->setPrintCss("/employeedb/ccr.css");
		$this->setActivityLocation('employeedb');
		
		$this->setPermissionRequired(array('admin', 'employeedb_global', 'employeedb_global','employeedb_personal_details','employeedb_job_role','employeedb_employment_history','employeedb_it_information','employeedb_asset_data','employeedb_training','employeedb_ppe_and_hse'));
		
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/employeedb/xml/menu.xml");
			
		
		
		
		
		
		
		
		
		
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
			
			if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
			{
				$this->showExcelResults();
			}
			else 
			{
				$this->showResults();
			}
			
		}
		else 
		{
			
			$this->add_output("<employeedbsearch>");
			
			
			
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
			
			
			
			/*
			$snapins = new snapinGroup('ccr_search');
			$snapins->register('apps/ccr', 'bookmarks', true, true);
	
			
			$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
			*/
			
			
			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");
			$this->add_output("<selectedFilters>");
			$this->add_output($this->selectedFilters->getOutput());
			$this->add_output("</selectedFilters>");
			
			$this->add_output("</employeedbsearch>");
				
			
			
			
			// if post
			
			// save, redirect to view			
			
		}
		
		

		$this->output('./apps/employeedb/xsl/search.xsl');	
	}
	
	
	private function showExcelResults()
	{
		$results = new excelResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("employeedb");
		
		$results->setBaseQuery("SELECT * FROM employee");
		$results->setOrderBy("name");
		
		$results->addColumn(new employeedbIDColumn("employee.`id`", "id", "id", true));
		//$results->addColumn(new column("employee.`firstName`", "firstName", "first_name", true));
		//$results->addColumn(new column("employee.`lastName`", "lastName", "last_name", true));
		
		$dummy = new employee();
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'all':
				
				$this->addFieldsFromForm($results, $dummy->personalDetailsForm);
				$this->addFieldsFromForm($results, $dummy->jobRoleForm);
				$this->addFieldsFromForm($results, $dummy->employmentHistoryForm);
				$this->addFieldsFromForm($results, $dummy->ITInformationForm);
				$this->addFieldsFromForm($results, $dummy->assetDataForm);
				$this->addFieldsFromForm($results, $dummy->trainingForm);
				$this->addFieldsFromForm($results, $dummy->PPEandHSEtrainingForm);
				break;
				
			case 'personal_details':
						
				$this->addFieldsFromForm($results, $dummy->personalDetailsForm);
				break;
				
			case 'job_role':
				
				$results->addColumn(new column("employee.`name`", "name", "name", true));
				$this->addFieldsFromForm($results, $dummy->jobRoleForm);
				break;
				
			case 'employment_history':
				
				$results->addColumn(new column("employee.`name`", "name", "name", true));	
				$this->addFieldsFromForm($results, $dummy->employmentHistoryForm);
				break;
				
			case 'it_information':
						
				$results->addColumn(new column("employee.`name`", "name", "name", true));
				$this->addFieldsFromForm($results, $dummy->ITInformationForm);
				break;
				
			case 'asset_data':
						
				$results->addColumn(new column("employee.`name`", "name", "name", true));
				$this->addFieldsFromForm($results, $dummy->assetDataForm);
				break;
				
			case 'training':
						
				$results->addColumn(new column("employee.`name`", "name", "name", true));
				$this->addFieldsFromForm($results, $dummy->trainingForm);
				break;
				
			case 'personal_protective_equipment':
						
				$results->addColumn(new column("employee.`name`", "name", "name", true));
				$this->addFieldsFromForm($results, $dummy->PPEandHSEtrainingForm);
				break;
		}
		
		
		
	

		
		
	/*	foreach ($dummy->personalDetailsForm->getGroup("default")->getAllControls() as $test)
		{
			if ($test->getDataType() != 'attachment')
			{
				$results->addColumn(new column("employee.`" . $test->getName() . "`", $test->getName(), $test->getRowTitle(), true));
			}
			//echo $test->getName() . " - " . $test->getRowTitle() . "\n";
		}
		*/
		//$results->addColumn(new column("employee.`name`", "name", "known_as", true));
		
		$results->performQuery();
	
		$results->display();
	
		exit(0);
	}
	
	private function addFieldsFromForm($results, $form)
	{
		$groups = $form->getGroupNames();
		
		for ($i=0; $i < count($groups); $i++)
		{
			if (get_class($form->getGroup($groups[$i])) == 'group')
			{
				foreach ($form->getGroup($groups[$i])->getAllControls() as $control)
				{
					switch (get_class($control))
					{
						case 'attachment':
							
							break;
							
						case 'measurement':
							
							$results->addColumn(new column("employee.`" . $control->getName() . "_quantity`", $control->getName() . "_quantity", $control->getRowTitle() . " (Quantity)", true));						
							$results->addColumn(new column("employee.`" . $control->getName() . "_measurement`", $control->getName() . "_measurement", $control->getRowTitle() . " (Units)", true));				
							
							break;
							
						default:
							
							
							
							$results->addColumn(new column("employee.`" . $control->getName() . "`", $control->getName(), $control->getRowTitle(), true));						
					}
				}
			}
			else
			{
				//for ($row=0; $i < $form->getGroup($groups[$i])->getRowCount(); $row++)
				//{
					// loop temployeedbough controls
					foreach($form->getGroup($groups[$i])->getAllControls(0) as $controlKey => $controlValue)
					{
						$results->addColumn(new column("employee.`dummy`", "dummy", $form->getGroup($groups[$i])->get(0, $controlKey)->getRowTitle(), true));						
					}
				//}
			}
		}
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
		
		$results->setDatabase("employeedb");
		
		$results->setBaseQuery("SELECT * FROM employee");
		$results->setOrderBy("name");
		
		
		$results->addColumn(new employeedbIDColumn("employee.`id`", "id", "id", true));
		//$results->addColumn(new ccrOwnerColumn("report.`owner`", "owner", "report_owner", true));
		//$results->addColumn(new column("report.`sapNumber`", "sapNumber", "sap_account_number", true));
		$results->addColumn(new column("employee.`firstName`", "firstName", "first_name", true));
		$results->addColumn(new column("employee.`lastName`", "lastName", "last_name", true));
		$results->addColumn(new column("employee.`personnelFile`", "personnelFile", "personnel_file", true));
		
		$results->addColumn(new column("employee.`workLocation`", "workLocation", "site", true));
		//$results->addColumn(new column("employee.`country`", "country", "country", true));
		
		//$results->addColumn(new column("employee.`managerResponsible`", "managerResponsible", "manager", true));
		$results->addColumn(new column("employee.`department`", "department", "department", true));
		
		$results->addColumn(new column("employee.`costCentre`", "costCentre", "cost_centre", true));
		//$results->addColumn(new column("employee.`directCustomerCountry`", "directCustomerCountry", "country", true));
		//$results->addColumn(new ccrDateColumn("report.`contactDate`", "contactDate", "contactDate", true));
		//$results->addColumn(new ccrTranslateColumn("report.`contactType`", "contactType", "contactType", true));
				
		
		
		/*switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary_direct':
				
				$results->setBaseQuery("SELECT * FROM employee");
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
				
				//SELECT owner AS `report.owner`, COUNT(id) As num,
//(SELECT COUNT(id) FROM report r WHERE contactType='personal' AND owner = `report.owner`) AS personal,
//(SELECT COUNT(id) FROM report r WHERE contactType='phone_call' AND owner = `report.owner`) AS phone_call,
//(SELECT COUNT(id) FROM report r WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`) AS other
//FROM report r GROUP BY owner
				
				$results->setBaseQuery("SELECT * FROM report GROUP BY owner");
				
				$results->addColumn(new ccrOwnerColumn("report.`owner`", "report.owner", "sales_employee", true));
				$results->addColumn(new column("COUNT(id)", "num_of_reports", "num_of_reports", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='personal' AND owner = `report.owner`)", "num_of_personal", "num_of_personal", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='phone_call' AND owner = `report.owner`)", "num_of_phone", "num_of_phone", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`)", "num_of_other", "num_of_other", true));
				break;
		}*/
		
		
		
		
		
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
			array('value' => 'all', 'display' => 'All'),
			array('value' => 'personal_details', 'display' => 'Personal Details'),
			array('value' => 'job_role', 'display' => 'Current Job Role'),
			array('value' => 'employment_history', 'display' => 'Employment History '),
			array('value' => 'it_information', 'display' => 'I.T Information '),
			array('value' => 'asset_data', 'display' => 'Asset Data'),
			array('value' => 'training', 'display' => 'Training'),
			array('value' => 'personal_protective_equipment', 'display' => 'Personal Protective Equipment ')
		);
		/*
		if (currentuser::getInstance()->hasPermission("admin") || currentuser::getInstance()->hasPermission("ccr_admin")) {
			$data[] = array('value' => 'analysis_material', 'display' => 'Analysis by Material Group');
			$data[] = array('value' => 'customer_survey', 'display' => 'Customer Survery reports');
			$data[] = array('value' => 'activity', 'display' => 'Activity report');
		}
		*/
		
		$reportType->setArraySource($data);
		$reportType->setValue("all");
		$reportType->setRowTitle("excel_output_sections");
		//$reportType->setPostBack("changeReportType");
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
			
		
		/*$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT DISTINCT workLocation AS a, workLocation AS b FROM employee ORDER BY workLocation ASC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			page::addDebug('value: '. $fields[1] . ' display: ' . $fields[0], __FILE__, __LINE__);
		}	*/
		
		
		$section = new filterCombo("section");
		$section->setField("workLocation");
		//$section->setSQLSource("employeedb","SELECT DISTINCT workLocation AS a, workLocation AS b FROM employee WHERE workLocation NOT IN ('') ORDER BY workLocation ASC");
		
		$section->setArraySource(array(
			array('value' => 'personal_details', 'display' => 'Personal Details'),
			array('value' => 'job_role', 'display' => 'Current Job Role'),
			array('value' => 'employment_history', 'display' => 'Employment History '),
			array('value' => 'it_information', 'display' => 'I.T Information '),
			array('value' => 'asset_data', 'display' => 'Asset Data'),
			array('value' => 'training', 'display' => 'Training'),
			array('value' => 'personal_protective_equipment', 'display' => 'PPE and HSE Training')
		));
		$section->setRowTitle("section");
		//$this->selectedFilters->add($section);
		
		
		$employmentStatus = new filterCombo("employmentStatus");
		$employmentStatus->setField("employmentStatus");
		$employmentStatus->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$employmentStatus->setSQLSource("employeedb","SELECT DISTINCT employmentStatus AS a, employmentStatus AS b FROM employee WHERE employmentStatus NOT IN ('') ORDER BY employmentStatus ASC");
		$employmentStatus->setRowTitle("Employment Status");
		$this->selectedFilters->add($employmentStatus);
		
		$personnelFile = new filterCombo("personnelFile");
		$personnelFile->setField("personnelFile");
		$personnelFile->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$personnelFile->setSQLSource("employeedb","SELECT DISTINCT personnelFile AS a, personnelFile AS b FROM employee WHERE personnelFile NOT IN ('') ORDER BY personnelFile ASC");
		$personnelFile->setRowTitle("Personnel File");
		$this->selectedFilters->add($personnelFile);
		
		$payrollLocation = new filterCombo("payrollLocation");
		$payrollLocation->setField("payrollLocation");
		$payrollLocation->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$payrollLocation->setSQLSource("employeedb","SELECT DISTINCT payrollLocation AS a, payrollLocation AS b FROM employee WHERE payrollLocation NOT IN ('') ORDER BY payrollLocation ASC");
		$payrollLocation->setRowTitle("Payroll Location");
		$this->selectedFilters->add($payrollLocation);
		
		$SETResponsible = new filterCombo("SETResponsible");
		$SETResponsible->setField("payrollLocation");
		$SETResponsible->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$SETResponsible->setSQLSource("employeedb","SELECT DISTINCT SETResponsible AS a, SETResponsible AS b FROM employee WHERE SETResponsible NOT IN ('') ORDER BY SETResponsible ASC");
		$SETResponsible->setRowTitle("SET Manager");
		$this->selectedFilters->add($SETResponsible);
		
		$jobRole = new filterCombo("jobRole");
		$jobRole->setField("jobRole");
		$jobRole->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$jobRole->setSQLSource("employeedb","SELECT DISTINCT jobRole AS a, jobRole AS b FROM employee WHERE jobRole NOT IN ('') ORDER BY jobRole ASC");
		$jobRole->setRowTitle("Job Role");
		$this->selectedFilters->add($jobRole);
		
		$jobType = new filterCombo("jobType");
		$jobType->setField("jobType");
		$jobType->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$jobType->setSQLSource("employeedb","SELECT DISTINCT jobType AS a, jobType AS b FROM employee WHERE jobType NOT IN ('') ORDER BY jobType ASC");
		$jobType->setRowTitle("Job Type");
		$this->selectedFilters->add($jobType);
		
		$jobLength = new filterCombo("jobLength");
		$jobLength->setField("jobLength");
		$jobLength->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$jobLength->setSQLSource("employeedb","SELECT DISTINCT jobLength AS a, jobLength AS b FROM employee WHERE jobLength NOT IN ('') ORDER BY jobLength ASC");
		$jobLength->setRowTitle("Job Length");
		$this->selectedFilters->add($jobLength);
		
		$site = new filterCombo("site");
		$site->setField("workLocation");
		$site->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$site->setSQLSource("employeedb","SELECT DISTINCT workLocation AS a, workLocation AS b FROM employee WHERE workLocation NOT IN ('') ORDER BY workLocation ASC");
		$site->setRowTitle("Work Location");
		$this->selectedFilters->add($site);
		
		$country = new filterCombo("country");
		$country->setField("country");
		$country->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$country->setSQLSource("employeedb","SELECT DISTINCT country AS a, country AS b FROM employee WHERE country NOT IN ('') ORDER BY country ASC");
		$country->setRowTitle("country");
		$this->selectedFilters->add($country);
		
		
		$manager = new filterCombo("manager");
		$manager->setField("managerResponsible");
		$manager->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$manager->setSQLSource("employeedb","SELECT DISTINCT managerResponsible AS a, managerResponsible AS b FROM employee WHERE managerResponsible NOT IN ('') ORDER BY managerResponsible ASC");
		$manager->setRowTitle("Manager");
		$this->selectedFilters->add($manager);
		
		$costCentre = new filterCombo("costcentre");
		$costCentre->setField("costCentre");
		$costCentre->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$costCentre->setSQLSource("employeedb","SELECT DISTINCT costCentre AS a, costCentre AS b FROM employee WHERE costCentre NOT IN ('') ORDER BY costCentre ASC");
		$costCentre->setRowTitle("cost_centre");
		$this->selectedFilters->add($costCentre);
		
		$department = new filterCombo("department");
		$department->setField("department");
		$department->setArraySource(array(
			array('value' => ' ', 'display' => 'None set')
		));
		$department->setSQLSource("employeedb","SELECT DISTINCT department AS a, department AS b FROM employee WHERE department NOT IN ('') ORDER BY department ASC");
		$department->setRowTitle("department");
		$this->selectedFilters->add($department);
	}
		
			
			
	
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

class employeedbTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class employeedbIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/employeedb/index?id=" . $fields['id'] . "\">" . $fields['id'] . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}


?>