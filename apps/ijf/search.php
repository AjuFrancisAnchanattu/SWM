<?php

require 'lib/ijf.php';

class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	function __construct()
	{
		
		parent::__construct();
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('IJF');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ijf/menu.xml");
			
		
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

		if(isset($_POST['columns'])){
			$_SESSION["searchColumns"] = $_POST['columns'];
			$_SESSION["reportType"] = $_POST['reportType'];
		}else{
			$this->selectedColumns = array();
			if(isset($_SESSION["searchColumns"]) && count($_SESSION["searchColumns"]) > 0){
				foreach($_SESSION["searchColumns"] as $val)
					$this->selectedColumns[] = $val;
				$this->showAllCols = false;
			}else{
				$_SESSION["searchColumns"] = array();
				$this->showAllCols = true;
			}
		}		
		
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
			
			$this->add_output("<IJFsearch>");
			
			
			
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
			$snapins = new snapinGroup('ijf_search');
			$snapins->register('apps/ijf', 'load', true, true);
			$snapins->register('apps/ijf', 'actions', true, true);
			$snapins->register('apps/ijf', 'reports', true, true);
			$snapins->register('apps/ijf', 'additionalLinks', true, true);		//puts the additional Links snapin in the page

			
			$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
			
			
			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_incomplete_ijf")
			{
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
				{
					foreach($_SESSION["searchColumns"] as $val)
						$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_complete_ijf")
			{
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
				{
					foreach($_SESSION["searchColumns"] as $val)
						$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_view_all_ijf")
			{
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
				{
					foreach($_SESSION["searchColumns"] as $val)
						$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}

			
			$this->add_output("<selectedFilters>");
			$this->add_output($this->selectedFilters->getOutput());
			$this->add_output("</selectedFilters>");
			
			$this->add_output("</IJFsearch>");

			// if post
			
			// save, redirect to view			
			
		}
		
		

		$this->output('./apps/ijf/xsl/search.xsl');	
	}
	
	
	// Show Excel Results
	private function showExcelResults()
	{
		$results = new excelResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("IJF");
		
		$results->setBaseQuery("SELECT * FROM ijf");
		$results->setOrderBy("id");
		
		$results->addColumn(new ijfTranslateColumn("ijf.`id`", "id", "id", true));
		//$results->addColumn(new column("employee.`firstName`", "firstName", "first_name", true));
		//$results->addColumn(new column("employee.`lastName`", "lastName", "last_name", true));
		
		$dummy = new ijf();
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary_incomplete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf WHERE (ijf.status != 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
//				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
//				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
//				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "ijf_creation_date", true));
				
			break;
				
			case 'custom_summary_incomplete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id WHERE (ijf.status != 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
								
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;
					
			case 'summary_complete_ijf':

				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN finance ON finance.ijfid = ijf.id WHERE (ijf.status = 'complete')");
				$results->setOrderBy("ijf.id");				
							
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "ijf_business_unit", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
				$results->addColumn(new column("ijf.`salesRep`", "salesRep", "sales_rep", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new column("ijf.`materialNo`", "materialNo", "material_no", true));
				$results->addColumn(new column("ijf.`targetPrice`", "targetPrice", "target_price", true));
				$results->addColumn(new column("finance.`smc`", "smc", "smc", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
//				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
//				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
//				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				
			break;

				
			case 'custom_summary_complete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id WHERE (ijf.status = 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;
			
			
			case 'summary_view_all_ijf':
				
				
				$results->setBaseQuery("SELECT * FROM ijf");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				
			break;

				
			case 'custom_summary_view_all_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));								
				
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;

				
			case 'commercial_planner_view':
				
				$results->setBaseQuery("SELECT * FROM commercialPlanning LEFT JOIN ijf ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON commercialPlanning.ijfId = production.ijfId");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("commercialPlanning.`ijfId`", "ijfId", "ijfId", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
				$results->addColumn(new column("ijf.`IJFCompleted`", "IJFCompleted", "ijf_completed", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new column("commercialPlanning.`commercialPlanningComments`", "comments", "comments", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("production.`testingRequired`", "testingRequired", "testing_required", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiator_info", "initiator_info_report", true));
				$results->addColumn(new column("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				//$results->addColumn(new ijfMaterialColumn("material.`id` AS materialId, material.`materialKey`", "materialKey", "material_group", true));
				//$results->addColumn(new column("material.`incomeQuantity`", "incomeQuantity", "annual_potential", true));
				//$results->addColumn(new column("material.`volume_quantity`", "volumeQuantity", "annual_potential_volume", true));
				//$results->addColumn(new column("material.`volume_measurement`", "volumeMeasurement", "annual_potential_volume_unit", true));
			
		case 'ijf_report':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id");
				$results->setOrderBy("ijf.id");
				
				//$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "ijf_business_unit", true));
				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "ijf_creation_date", true));
				$results->addColumn(new column("ijf.`endDate`", "endDate", "end_date", true));
				$results->addColumn(new column("ijf.`daysToComplete`", "daysToComplete", "days_from_open_to_close", true));
//				$results->addColumn(new ijfDaysToComplete("ijf.`id`", "id", "days_from_open_to_close", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "owner", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				
			
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
	
	
	private function showResults()
	{
		if (isset($_REQUEST['load']))
		{
			// load saved stuff from db into session...
			
			$this->redirect("search?action=view");
		}
		
		$results = new searchResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("IJF");
		
		$results->setBaseQuery("SELECT * FROM ijf");
		$results->setOrderBy("ijf.id");
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary_incomplete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf WHERE (ijf.status != 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
//				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
//				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
//				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "ijf_creation_date", true));
				
			break;
				
			case 'custom_summary_incomplete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id WHERE (ijf.status != 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
								
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;
					
			case 'summary_complete_ijf':

				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN finance ON finance.ijfid = ijf.id WHERE (ijf.status = 'complete')");
				$results->setOrderBy("ijf.id");				
							
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "ijf_business_unit", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
				$results->addColumn(new column("ijf.`salesRep`", "salesRep", "sales_rep", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new column("ijf.`materialNo`", "materialNo", "material_no", true));
				$results->addColumn(new column("ijf.`targetPrice`", "targetPrice", "target_price", true));
				$results->addColumn(new column("finance.`smc`", "smc", "smc", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
//				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
//				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
//				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				
			break;

				
			case 'custom_summary_complete_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id WHERE (ijf.status = 'complete')");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;
			
			
			case 'summary_view_all_ijf':
				
				
				$results->setBaseQuery("SELECT * FROM ijf");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				$results->addColumn(new column("ijf.`customerName`", "customerName", "customer_name", true));
				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "ijf_creator", true));
				
			break;

				
			case 'custom_summary_view_all_ijf':
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON production.ijfId = ijf.id LEFT JOIN dataAdministration ON dataAdministration.ijfId = ijf.id");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));								
				
				if(in_array("acceptedRejected", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				if(in_array("barManView", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`barManView`", "barManView", "bar_man_view_request", true));
				if(in_array("businessUnit", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`colour`", "colour", "colour", true));
				if(in_array("customerAccountNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerAccountNumber`", "customerAccountNumber", "customer_account_number", true));
				if(in_array("customerCountry", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerCountry`", "customerCountry", "customer_country", true));				
				if(in_array("customerName", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`customerName`", "customerName", "customer_name", true));
				if(in_array("daSapPartNumber", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("dataAdministration.`daSapPartNumber`", "daSapPartNumber", "da_sap_part_number", true));
				if(in_array("initialSubmissionDate", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "date_entered", true));
				if(in_array("initiatorInfo", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiatorInfo", "initiator_info_report", true));
				if(in_array("materialGroup", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`materialGroup`", "materialGroup", "material_group", true));
				if(in_array("productionSite", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`productionSite`", "productionSite", "production_site", true));
				if(in_array("reasonIJF", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				if(in_array("routing", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`routing`", "routing", "routing", true));
				if(in_array("salesRep", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`salesRep`", "salesRep", "sales_rep", true));
				if(in_array("location_owner", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("commercialPlanning.`location_owner`", "location_owner", "send_ijf_to_location", true));
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				if(in_array("toolsRequired", $this->selectedColumns) || $this->showAllCols)
					$results->addColumn(new ijfTranslateColumn("production.`toolsRequired`", "toolsRequired", "tools_required", true));				
				
			break;

				
			case 'commercial_planner_view':
				
				$results->setBaseQuery("SELECT * FROM commercialPlanning LEFT JOIN ijf ON commercialPlanning.ijfId = ijf.id LEFT JOIN production ON commercialPlanning.ijfId = production.ijfId");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("commercialPlanning.`ijfId`", "ijfId", "ijfId", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "ijf_owner", true));
				$results->addColumn(new column("ijf.`IJFCompleted`", "IJFCompleted", "ijf_completed", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new column("commercialPlanning.`commercialPlanningComments`", "comments", "comments", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new column("production.`testingRequired`", "testingRequired", "testing_required", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`initiatorInfo`", "initiator_info", "initiator_info_report", true));
				$results->addColumn(new column("ijf.`reasonIJF`", "reasonIJF", "reason_for_ijf", true));
				//$results->addColumn(new ijfMaterialColumn("material.`id` AS materialId, material.`materialKey`", "materialKey", "material_group", true));
				//$results->addColumn(new column("material.`incomeQuantity`", "incomeQuantity", "annual_potential", true));
				//$results->addColumn(new column("material.`volume_quantity`", "volumeQuantity", "annual_potential_volume", true));
				//$results->addColumn(new column("material.`volume_measurement`", "volumeMeasurement", "annual_potential_volume_unit", true));
			
				
			case 'ijf_report':
				
				$datasetEndDate = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT DATE_FORMAT(logDate, '%Y-%m-%d') AS logDate, initialSubmissionDate, ijfId FROM ijf LEFT JOIN log ON log.ijfId = ijf.id ORDER BY logDate");
		
				while ($fieldsEndDate = mysql_fetch_array($datasetEndDate))
				{
					$dayDiff = $this->datediff($fieldsEndDate['initialSubmissionDate'], $fieldsEndDate['logDate']);
					mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf SET daysToComplete = '" . $dayDiff . "', endDate = '" . $fieldsEndDate['logDate'] . "' WHERE ijfCompleted = 'yes' AND  id = " . $fieldsEndDate['ijfId']);
				}
				
				$results->setBaseQuery("SELECT * FROM ijf LEFT JOIN commercialPlanning ON commercialPlanning.ijfId = ijf.id");
				$results->setOrderBy("ijf.id");
				
				$results->addColumn(new ijfIDColumn("ijf.`id`", "id", "id", true));
				$results->addColumn(new column("commercialPlanning.`acceptedRejected`", "acceptedRejected", "acceptedRejected", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`businessUnit`", "businessUnit", "ijf_business_unit", true));
				$results->addColumn(new column("ijf.`productionSite`", "productionSite", "productionSite", true));
				$results->addColumn(new column("ijf.`materialGroup`", "materialGroup", "material_group", true));
				$results->addColumn(new ijfDateColumn("ijf.`initialSubmissionDate`", "initialSubmissionDate", "ijf_creation_date", true));
				$results->addColumn(new column("ijf.`endDate`", "endDate", "end_date", true));
				$results->addColumn(new column("ijf.`daysToComplete`", "daysToComplete", "days_from_open_to_close", true));
//				$results->addColumn(new ijfDaysToComplete("ijf.`id`", "id", "days_from_open_to_close", true));
				$results->addColumn(new ijfOwnerColumn("ijf.`owner`", "owner", "owner", true));
				$results->addColumn(new ijfTranslateColumn("ijf.`status`", "status", "status", true));
				
//				$results->addColumn(new column("ijf.`ijf.updatedDate`", "updatedDate", "ijf_updated_date", true));
			
			
			break;
				
			
				
			/*	
			case 'activity':
				/*
				SELECT owner AS `report.owner`, COUNT(id) As num,
(SELECT COUNT(id) FROM report r WHERE contactType='personal' AND owner = `report.owner`) AS personal,
(SELECT COUNT(id) FROM report r WHERE contactType='phone_call' AND owner = `report.owner`) AS phone_call,
(SELECT COUNT(id) FROM report r WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`) AS other
FROM report r GROUP BY owner*/
			/*
				
				$results->setBaseQuery("SELECT * FROM report GROUP BY owner");
				
				$results->addColumn(new ijfOwnerColumn("report.`owner`", "report.owner", "sales_employee", true));
				$results->addColumn(new column("COUNT(id)", "num_of_reports", "num_of_reports", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='personal' AND owner = `report.owner`)", "num_of_personal", "num_of_personal", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType='phone_call' AND owner = `report.owner`)", "num_of_phone", "num_of_phone", true));
				$results->addColumn(new column("(SELECT COUNT(id) FROM report WHERE contactType NOT IN ('phone_call', 'personal') AND owner = `report.owner`)", "num_of_other", "num_of_other", true));
				break;
			
			*/
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
			array('value' => 'summary_incomplete_ijf', 'display' => 'Summary of Incomplete IJF Reports'),
			array('value' => 'custom_summary_incomplete_ijf', 'display' => 'Summary of Incomplete IJF Reports - Custom'),
			
			array('value' => 'summary_complete_ijf', 'display' => 'Summary of Complete IJF Reports'),
			array('value' => 'custom_summary_complete_ijf', 'display' => 'Summary of Complete IJF Reports - Custom'),
			
			array('value' => 'summary_view_all_ijf', 'display' => 'Summary of All IJF Reports'),
			array('value' => 'custom_summary_view_all_ijf', 'display' => 'Summary of All IJF Reports - Custom'),
			
			array('value' => 'commercial_planner_view', 'display' => 'Commercial Planner Reports'),			
			array('value' => 'ijf_report', 'display' => 'IJF Report'),			
		);
		
		//if (currentuser::getInstance()->hasPermission("admin") || currentuser::getInstance()->hasPermission("ijf_admin")) {
		//	$data[] = array('value' => 'analysis_material', 'display' => 'Analysis by Material Group');
		//	$data[] = array('value' => 'customer_survey', 'display' => 'Customer Survery reports');
		//	$data[] = array('value' => 'activity', 'display' => 'Activity report');
		//}
		
		
		$reportType->setArraySource($data);
		$reportType->setValue("summary_incomplete_ijf");
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
		
		if ($reportType == 'summary_incomplete_ijf' || 
			$reportType == 'summary_complete_ijf' ||
			$reportType == 'summary_view_all_ijf' ||
			$reportType == 'custom_summary_incomplete_ijf' || 
			$reportType == 'custom_summary_complete_ijf' ||
			$reportType == 'custom_summary_view_all_ijf' ||
			$reportType == 'ijf_report')
		{
			/*
			$sapNumber = new filterCombo("sapNumber");
			$sapNumber->setField("report.sapNumber");
			$sapNumber->setSQLSource("IJF","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM ijf WHERE $where AND materialGroup > 0 ORDER BY name ASC");
			
			if ($reportType == 'summary_indirect')
			{
				$sapNumber->setRowTitle("site");
			}
			else 
			{
				$sapNumber->setRowTitle("site");
			}
			
			$this->selectedFilters->add($sapNumber);
			*/
			if ($reportType == 'summary_incomplete_ijf' || 
				$reportType == 'summary_complete_ijf' ||
				$reportType == 'summary_view_all_ijf' ||
				$reportType == 'custom_summary_incomplete_ijf' || 
				$reportType == 'custom_summary_complete_ijf' ||
				$reportType == 'custom_summary_view_all_ijf')
			{	
				$owner = new filterCombo("owner");
				$owner->setField("ijf.owner");
				$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.owner = membership.employee.ntlogon ORDER BY name ASC");
				$owner->setRowTitle("owner_ijf");
				$this->selectedFilters->add($owner);
			}
			
			if ($reportType == 'summary_incomplete_ijf' || 
				$reportType == 'summary_complete_ijf' ||
				$reportType == 'summary_view_all_ijf' ||
				$reportType == 'custom_summary_incomplete_ijf' || 
				$reportType == 'custom_summary_complete_ijf' ||
				$reportType == 'custom_summary_view_all_ijf' ||
				$reportType == 'ijf_report')
			{
				$productionSite = new filterCombo("productionSite");
				$productionSite->setField("ijf.productionSite");
				$productionSite->setSQLSource("IJF","SELECT DISTINCT productionSite AS name, productionSite AS data FROM ijf ORDER BY name ASC");
				$productionSite->setRowTitle("production_site");
				$this->selectedFilters->add($productionSite);
				
				$materialGroup = new filterCombo("materialGroup");
				$materialGroup->setField("ijf.materialGroup");
				$materialGroup->setSQLSource("IJF","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM ijf ORDER BY name ASC");
				$materialGroup->setRowTitle("material_group");
				$this->selectedFilters->add($materialGroup);
			}
			
			if ($reportType == 'summary_incomplete_ijf' || 
				$reportType == 'summary_complete_ijf' ||
				$reportType == 'summary_view_all_ijf' ||
				$reportType == 'custom_summary_incomplete_ijf' || 
				$reportType == 'custom_summary_complete_ijf' ||
				$reportType == 'custom_summary_view_all_ijf')
			{			
				$name = new filterCombo("name");
				$name->setField("ijf.productOwner");
				$name->setSQLSource("IJF","SELECT DISTINCT productOwner AS name, productOwner AS data FROM ijf ORDER BY name ASC");
				
				if ($reportType == 'summary_incomplete_ijf' || 'custom_summary_incomplete_ijf')
				{
					$name->setRowTitle("product_owner");
				}
				else 
				{
					$name->setRowTitle("product_owner");
				}
				$this->selectedFilters->add($name);
				
				/*
				$country = new filterCombo("country");
				$country->setField("report.country");
				$country->setSQLSource("IJF","SELECT DISTINCT country AS name, country AS data FROM report WHERE $where AND country NOT IN ('', ' ') ORDER BY name ASC");
				$country->setRowTitle("customer_country");
				$this->selectedFilters->add($country);
				
				*/
			
				$initiatorInfo = new filterCombo("initiatorInfo");
				$initiatorInfo->setField("ijf.initiatorInfo");
				$initiatorInfo->setSQLSource("IJF","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = membership.employee.ntlogon ORDER BY name ASC");
				$initiatorInfo->setRowTitle("ijf_creator");
				$this->selectedFilters->add($initiatorInfo);
				
				$commercialPlanner = new filterCombo("commercialPlanner");
				$commercialPlanner->setField("ijf.owner");
				$commercialPlanner->setSQLSource("MEMBERSHIP","SELECT DISTINCT concat(employee.firstName,' ',employee.lastName) AS name, employee.ntlogon AS data from membership.employee, membership.permissions, IJF.ijf WHERE permissions.permission LIKE 'ijf_commercialPlanning%' AND employee.ntlogon = permissions.ntlogon AND permissions.ntlogon = ijf.owner ORDER BY name ASC");
				$commercialPlanner->setRowTitle("commercial_planner");
				$this->selectedFilters->add($commercialPlanner);
			}
			
			
			$status = new filterCombo("status");
			$status->setField("ijf.status");
			$status->setSQLSource("IJF","SELECT DISTINCT status AS name, status AS data from ijf WHERE status != '' ORDER BY name ASC");
			$status->setRowTitle("status");
			$this->selectedFilters->add($status);
			
			if ($reportType == 'summary_incomplete_ijf' || 
				$reportType == 'summary_complete_ijf' ||
				$reportType == 'summary_view_all_ijf' ||
				$reportType == 'custom_summary_incomplete_ijf' || 
				$reportType == 'custom_summary_complete_ijf' ||
				$reportType == 'custom_summary_view_all_ijf')
			{
				$manBarViewRequest = new filterCombo("manBarViewRequest");
				$manBarViewRequest->setField("ijf.barManView");
				$manBarViewRequest->setSQLSource("IJF","SELECT DISTINCT barManView AS name, barManView AS data FROM ijf ORDER BY name ASC");
				$manBarViewRequest->setRowTitle("bar_man_view_request");
				$this->selectedFilters->add($manBarViewRequest);
			}

			$businessUnit = new filterCombo("businessUnit");
			$businessUnit->setField("ijf.businessUnit");
			$businessUnit->setSQLSource("IJF","SELECT  DISTINCT businessUnit AS name, businessUnit AS data FROM ijf ORDER BY name ASC");
			$businessUnit->setRowTitle("business_unit");
			$this->selectedFilters->add($businessUnit);
			
			if ($reportType == 'summary_incomplete_ijf' || 
				$reportType == 'summary_complete_ijf' ||
				$reportType == 'summary_view_all_ijf' ||
				$reportType == 'custom_summary_incomplete_ijf' || 
				$reportType == 'custom_summary_complete_ijf' ||
				$reportType == 'custom_summary_view_all_ijf')
			{
				$materialNo = new filterCombo("materialNo");
				$materialNo->setField("ijf.materialNo");
				$materialNo->setSQLSource("IJF","SELECT DISTINCT materialNo AS name, materialNo AS data FROM ijf ORDER bY name ASC");
				$materialNo->setRowTitle("material_no");
				$this->selectedFilters->add($materialNo);
			
				$targetPrice = new filterCombo("targetPrice");
				$targetPrice->setField("ijf.targetPrice");
				$targetPrice->setSQLSource("IJF","SELECT DISTINCT targetPrice AS name, targetPrice AS data FROM ijf ORDER BY name ASC");
				$targetPrice->setRowTitle("target_price");
				$this->selectedFilters->add($targetPrice);
				
				$smc = new filterCombo("smc");
				$smc->setField("finance.smc");
				$smc->setSQLSource("IJF","SELECT DISTINCT smc AS name, smc AS data FROM finance ORDER BY name ASC");
				$smc->setRowTitle("smc");
				$this->selectedFilters->add($smc);
			}
			
			
			$analysisPeriod = new filterDateRange("analysisPeriod");
		
			if ($reportType == 'activity') 
			{
				$analysisPeriod->setRowType("row");
				$analysisPeriod->setVisible(true);
			}
		
			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("ijf.updatedDate");
			$analysisPeriod->setRowTitle("ijf_updated_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);
			
			
			$creationStartDate = new filterDateRange("creationStartDate");
		
			if ($reportType == 'activity') 
			{
				$creationStartDate->setRowType("row");
				$creationStartDate->setVisible(true);
			}
		
			$creationStartDate->setField("ijf.initialSubmissionDate");
			$creationStartDate->setRowTitle("ijf_creation_date");
			$creationStartDate->setRequired(true);
			$this->selectedFilters->add($creationStartDate);
			
		}
		
		
		if ($reportType == "commercial_planner_view" ||
			$reportType == "summary_complete_ijf" ||
			$reportType == "custom_summary_complete_ijf" ||
			$reportType == "ijf_report" )
		{
			$resultsAcceptedRejected = new filterCombo("resultsAcceptedRejected");
			$resultsAcceptedRejected->setField("commercialPlanning.acceptedRejected");
			$resultsAcceptedRejected->setSQLSource("IJF","SELECT DISTINCT acceptedRejected AS name, acceptedRejected AS data FROM commercialPlanning ORDER BY name ASC");
			$resultsAcceptedRejected->setRowTitle("acceptedRejected");
			$this->selectedFilters->add($resultsAcceptedRejected);
		}
		
		
		if ($reportType == "commercial_planner_view")
		{	
			$testingRequired = new filterCombo("testingRequired");
			$testingRequired->setField("production.testingRequired");
			$testingRequired->setSQLSource("IJF","SELECT DISTINCT testingRequired AS name, testingRequired AS data from production WHERE testingRequired != '' ORDER BY name ASC");
			$testingRequired->setRowTitle("testing_required");
			$this->selectedFilters->add($testingRequired);
			
			$commercialPlanning_owner = new filterCombo("commercialPlanning_owner");
			$commercialPlanning_owner->setField("commercialPlanning.commercialPlanning_owner");
			$commercialPlanning_owner->setSQLSource("IJF","SELECT DISTINCT commercialPlanning_owner AS name, commercialPlanning_owner AS data FROM commercialPlanning ORDER BY name ASC");
			$commercialPlanning_owner->setRowTitle("commercialPlanning_owner");
			$this->selectedFilters->add($commercialPlanning_owner);
		}
		
		
		/*
		if ($reportType == 'analysis_material' || $reportType == 'customer_survey')
		{
			$productRange = new filterCombo("productFamily");
			$productRange->setField("material.productFamily");
			$productRange->setSQLSource("IJF","SELECT DISTINCT productFamily AS name, productFamily AS data FROM material WHERE productFamily NOT IN ('', ' ') ORDER BY name ASC");
			$productRange->setRowTitle("product_range");
			$this->selectedFilters->add($productRange);
			
			$manufacturingSite = new filterCombo("manufacturingSite");
			$manufacturingSite->setField("report.manufacturingSite");
			$manufacturingSite->setSQLSource("IJF","SELECT DISTINCT country AS name, country AS data FROM report WHERE country NOT IN ('', ' ') ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);
		}
		*/
		
		/*
		if ($reportType == 'analysis_material')
		{
			$competitorName = new filterCombo("competitorName");
			$competitorName->setField("material.competitorName");
			$competitorName->setSQLSource("IJF","SELECT DISTINCT competitorName AS name, competitorName AS data FROM material WHERE competitorName NOT IN ('', ' ') ORDER BY name ASC");
			$competitorName->setRowTitle("competitor_name");
			$this->selectedFilters->add($competitorName);
			
			$competitorProduct = new filterCombo("competitorProductCode");
			$competitorProduct->setField("material.competitorProductCode");
			$competitorProduct->setSQLSource("IJF","SELECT DISTINCT competitorProductCode AS name, competitorProductCode AS data FROM material WHERE competitorProductCode NOT IN ('', ' ') ORDER BY name ASC");
			$competitorProduct->setRowTitle("competitor_product");
			$this->selectedFilters->add($competitorProduct);
		}
		*/
		
	/*	$reportDate = new filterBetweenDate("reportDate");
		$reportDate->setFilterRowTitle("report_date");
		$this->seletectedFilters->add($reportDate);
		
		$contactDate = new filterBetweenDate("contactDate");
		$contactDate->setFilterRowTitle("contact_date");
		$this->seletectedFilters->add($contactDate);
		
		$contactType = new filterList("contactType");
		$contactType->setSQLSource("IJF","SELECT DISTINCT contactType AS name, contactType AS data FROM report ORDER BY contactType ASC");
		$contactType->setFilterRowTitle("contact_type");
		$this->seletectedFilters->add($contactType);
		
		$materialKey = new filterList("materialKey");
		$materialKey->setSQLSource("IJF","SELECT DISTINCT materialKey AS name, materialKey AS data FROM material ORDER BY MaterialKey ASC");
		$materialKey->setFilterRowTitle("material_key");
		$this->seletectedFilters->add($tmaterialKey);*/
	}
	
	private function datediff($datefrom, $dateto)
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);

		$difference = $dateto - $datefrom; // Difference in seconds

		$days_difference = floor($difference / 86400);
		
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		
		$first_day = date("w", $datefrom);
		
		$days_remainder = floor($days_difference % 7);
		
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		
		if ($odd_days > 7) { // Sunday
			$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
			$days_remainder--;
		}
		
		$datediff = ($weeks_difference * 5) + $days_remainder;

		return $datediff;
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
		
		$status = new columnIJFstatus("status");
		$status->setDisplayName("status");
		$status->setTable("report");
		$this->report->addColumn($status);
		
		$customer = new column("name");
		$customer->setDisplayName("customer");
		$customer->setTable("report");
		$this->report->addColumn($customer);
		
		
		
	}*/
}

class ijfOwnerColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= usercache::getInstance()->get($fields[$this->getName()])->getName();
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ijfDateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= page::transformDateForPHP($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ijfTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class ijfIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/ijf/index?id=" . $fields[$this->getName()] . "\">" . $fields[$this->getName()] . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}

//class ijfEndDate extends column
//{
//	public function getOutput($fields)
//	{
//		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT logDate, ijfId FROM log WHERE ijfId = " . $fields[$this->getName()] . " ORDER BY logDate DESC LIMIT 1");
//		
//		$fields = mysql_fetch_array($dataset);
//		
//		$id = $fields['ijfId'];
//		$endDate = $fields['logDate'];
//		
//		mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf SET endDate = '" . $endDate . "' WHERE ijfCompleted = 'yes' AND  id = " . $id);
//		
//		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
//		$xml .= common::transformDateTimeForPHP($endDate);
//		$xml .= "</text></searchColumn>";
//		
//		return $xml;
//	}
//}

//class ijfDaysToComplete extends column
//{
//	public function getOutput($fields)
//	{
//		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT DATE_FORMAT(logDate, '%Y-%m-%d') AS logDate, initialSubmissionDate, ijfId FROM ijf LEFT JOIN log ON log.ijfId = ijf.id WHERE ijf.id = " . $fields[$this->getName()] . " ORDER BY logDate DESC LIMIT 1");
//		
//		$fields = mysql_fetch_array($dataset);
//		
//		$dayDiff = $this->datediff($fields['initialSubmissionDate'], $fields['logDate']);
////		var_dump($fields['ijfId']);
////		die();
//		mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf SET daysToComplete = '" . $dayDiff . "' WHERE id = " . $fields['ijfId']);
////		die();
//		
//		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
//		$xml .= common::transformDateForPHP($dayDiff . " Days");
//		$xml .= "</text></searchColumn>";
//		
//		return $xml;
//	}
//	
//	public function datediff($datefrom, $dateto)
//	{
//		$datefrom = strtotime($datefrom, 0);
//		$dateto = strtotime($dateto, 0);
//
//		$difference = $dateto - $datefrom; // Difference in seconds
//
//		$days_difference = floor($difference / 86400);
//		
//		$weeks_difference = floor($days_difference / 7); // Complete weeks
//		
//		$first_day = date("w", $datefrom);
//		
//		$days_remainder = floor($days_difference % 7);
//		
//		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
//		
//		if ($odd_days > 7) { // Sunday
//			$days_remainder--;
//		}
//		if ($odd_days > 6) { // Saturday
//			$days_remainder--;
//		}
//		
//		$datediff = ($weeks_difference * 5) + $days_remainder;
//
//		return $datediff;
//	}
//}

	

/* Currently not needed.
class ijfMaterialColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/ijf/view?material=" . $fields['materialId'] . "\">" . page::xmlentities($fields['materialKey']) . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}
*/
?>