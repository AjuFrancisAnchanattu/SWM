<?php

require 'lib/gis.php';

class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	function __construct()
	{
		parent::__construct();
		
		if(!currentuser::getInstance()->hasPermission("gis_admin"))
		{
			die("You do not have permission to view the Global Information System");
		}
		
		$this->setPrintCss("/css/ccr.css");
		
		$this->setActivityLocation('GIS');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		
		$this->header->setMenuXML("./apps/gis/xml/menu.xml");
		
		$this->defineChooseReportForm();
		
		$this->chooseReportForm->loadSessionData();
		
		$this->chooseReportForm->processPost();

		$this->chooseReportForm->validate();
		
		$this->defineSelectedFilters();
		
		$this->selectedFilters->form->loadSessionData();
		
		$this->defineAddFiltersForm();
		
		$this->selectedFilters->processPost();
		
		if(isset($_POST['columns']))
		{
			$_SESSION["searchColumns"] = $_POST['columns'];
			$_SESSION["reportType"] = $_POST['reportType'];
		}
		else
		{
			$this->selectedColumns = array();
			if(isset($_SESSION["searchColumns"]) && count($_SESSION["searchColumns"]) > 0)
			{
				foreach($_SESSION["searchColumns"] as $val)
				{
					$this->selectedColumns[] = $val;
				}
				$this->showAllCols = false;
			}
			else
			{
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
			
			$this->add_output("<GISsearch>");
			
			
			
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
			$snapins_left = new snapinGroup('GISsearch');
			$snapins_left->register('apps/gis', 'warning', true, true);
			$snapins_left->register('apps/gis', 'loadgis', true, true);
			$snapins_left->register('apps/gis', 'profileTypes', true, true);
			//$snapins_left->register('apps/gis', 'newInformation', true, true);
			//$snapins_left->register('apps/gis', 'archive', true, true);
	
			$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
			
			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");
			
//			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_incomplete_gis")
//			{
//				$this->add_output("<columnFilters>");
//				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
//				{
//					foreach($_SESSION["searchColumns"] as $val)
//						$this->add_output("<".$val.">1</".$val.">");
//				}
//				$this->add_output("</columnFilters>");
//			}
//			
//			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_complete_gis")
//			{
//				$this->add_output("<columnFilters>");
//				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
//				{
//					foreach($_SESSION["searchColumns"] as $val)
//						$this->add_output("<".$val.">1</".$val.">");
//				}
//				$this->add_output("</columnFilters>");
//			}
//			
//			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_view_all_gis")
//			{
//				$this->add_output("<columnFilters>");
//				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
//				{
//					foreach($_SESSION["searchColumns"] as $val)
//						$this->add_output("<".$val.">1</".$val.">");
//				}
//				$this->add_output("</columnFilters>");
//			}

			if($this->chooseReportForm->get("reportType")->getValue() == "competitor_custom")
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
			
			$this->add_output("</GISsearch>");
		}
	
		$this->output('./apps/gis/xsl/search.xsl');	
	}
	
//	private function showExcelResults()
//	{
//		$results = new excelResults();
//		
//		$results->setSelectedFilters($this->selectedFilters);
//		
//		$results->setDatabase("gis");
//		
//		$results->setBaseQuery("SELECT * FROM gis");
//		
//		$results->setOrderBy("id");
//		
//		$results->addColumn(new gisTranslateColumn("gis.`id`", "id", "id", true));
//		
//		$dummy = new gis();
//		
//		switch($this->chooseReportForm->get("reportType")->getValue())
//		{
//			case 'market':
//				
//				$results->setBaseQuery("SELECT * FROM gis WHERE gis.`profileType`='market'");
//				$results->setOrderBy("gis.id");
//				
//				$results->addColumn(new column("gis.`profileName`", "profile_name", "profile_name", true));
//				$results->addColumn(new gisOwnerColumn("gis.`initiator`", "initiator", "initiator", true));
//				$results->addColumn(new gisDateColumn("gis.`dateAdded`", "date_added", "date_added", true));
//				$results->addColumn(new gisDateColumn("gis.`dateUpdated`", "date_updated", "date_updated", true));
//				$results->addColumn(new column("gis.`m_sizeAndGrowth`", "size_and_growth", "size_and_growth", true));
//				$results->addColumn(new column("gis.`m_competitors`", "competitors", "competitors", true));
//				$results->addColumn(new column("gis.`m_technologyChanges`", "technology_changes", "technology_changes", true));
//				$results->addColumn(new column("gis.`m_legislation`", "legislation", "legislation", true));
//				
//			break;
//			
//			case 'competitor':
//			
//				$results->setBaseQuery("SELECT * FROM gis WHERE gis.`profileType`='competitor'");
//				$results->setOrderBy("gis.id");
//				
//				$results->addColumn(new column("gis.`profileName`", "profile_name", "profile_name", true));
//				$results->addColumn(new gisOwnerColumn("gis.`initiator`", "initiator", "initiator", true));
//				$results->addColumn(new gisDateColumn("gis.`dateAdded`", "date_added", "date_added", true));
//				$results->addColumn(new gisDateColumn("gis.`dateUpdated`", "date_updated", "date_updated", true));
//				$results->addColumn(new column("gis.`c_background`", "background", "background", true));
//				$results->addColumn(new column("gis.`c_corporateStructure`", "corporate_structure", "corporate_structure", true));
//				$results->addColumn(new column("gis.`c_financialHighlights`", "financial_highlights", "financial_highlights", true));
//				$results->addColumn(new column("gis.`c_keyPersonnel`", "key_personnel", "key_personnel", true));
//				$results->addColumn(new column("gis.`c_MarketSectorActivity`", "market_sector_activity", "market_sector_activity", true));
//				$results->addColumn(new column("gis.`c_productRangeActivity`", "product_range_activity", "product_range_activity", true));
//				$results->addColumn(new column("gis.`c_newProducts`", "new_products", "new_products", true));
//				$results->addColumn(new column("gis.`c_technicalComparisons`", "technical_comparisons", "technical_comparisons", true));
//				$results->addColumn(new column("gis.`c_currentPricingLevels`", "current_pricing_levels", "current_pricing_levels", true));
//				$results->addColumn(new column("gis.`c_packagingAndBranding`", "packaging_and_branding", "packaging_and_branding", true));
//				$results->addColumn(new column("gis.`c_serviceLevels`", "service_levels", "service_levels", true));
//				$results->addColumn(new column("gis.`c_geographicActivity`", "geographic_activity", "geographic_activity", true));
//				$results->addColumn(new column("gis.`c_activeAccounts`", "active_accounts", "active_accounts", true));
//				$results->addColumn(new column("gis.`c_marketingActivity`", "marketing_activity", "marketing_activity", true));
//				$results->addColumn(new column("gis.`c_strengthWeakness`", "strength_weakness", "strength_weakness", true));
//				$results->addColumn(new column("gis.`c_currentStrategy`", "current_strategy", "current_strategy", true));
//				$results->addColumn(new column("gis.`c_distributionStrategy`", "distribution_strategy", "distribution_strategy", true));
//				$results->addColumn(new column("gis.`c_summary`", "summary", "summary", true));
//				
//			break;
//		}
//		
//		
//		echo $results->performQuery();
//	
//		$results->display();
//	
//		exit(0);
//	}
	
	
	private function showResults()
	{
		if (isset($_REQUEST['load']))
		{
			// load saved stuff from db into session...
			
			$this->redirect("search?action=view");
		}
		
		$results = new searchResultsHor();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("gis");
		
		$results->setBaseQuery("SELECT * FROM gis");
		$results->setOrderBy("gis.id");
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'market':
				
				$results->setBaseQuery("SELECT * FROM gis WHERE gis.`profileType`='market'");
				$results->setOrderBy("gis.id");
				
				$results->addColumnHor(new gisIDColumn("gis.`id`", "id", "id", true));
				$results->addColumnHor(new column("gis.`profileName`", "profile_name", "profile_name", true));
				$results->addColumnHor(new gisOwnerColumn("gis.`initiator`", "initiator", "initiator", true));
				$results->addColumnHor(new gisDateColumn("gis.`dateAdded`", "date_added", "date_added", true));
				$results->addColumnHor(new gisDateColumn("gis.`dateUpdated`", "date_updated", "date_updated", true));
				$results->addColumnHor(new columnHor("gis.`m_sizeAndGrowth`", "size_and_growth", "size_and_growth", true));
				$results->addColumnHor(new columnHor("gis.`m_competitors`", "competitors", "competitors", true));
				$results->addColumnHor(new columnHor("gis.`m_technologyChanges`", "technology_changes", "technology_changes", true));
				$results->addColumnHor(new columnHor("gis.`m_legislation`", "legislation", "legislation", true));
				
			break;
			
			case 'competitor':
			
				$results->setBaseQuery("SELECT * FROM gis WHERE gis.`profileType`='competitor'");
				$results->setOrderBy("gis.id");
				
				$results->addColumnHor(new gisIDColumn("gis.`id`", "id", "id", true));
				$results->addColumnHor(new column("gis.`profileName`", "profile_name", "profile_name", true));
				$results->addColumnHor(new gisOwnerColumn("gis.`initiator`", "initiator", "initiator", true));
				$results->addColumnHor(new gisDateColumn("gis.`dateAdded`", "date_added", "date_added", true));
				$results->addColumnHor(new gisDateColumn("gis.`dateUpdated`", "date_updated", "date_updated", true));
				$results->addColumnHor(new columnHor("gis.`c_background`", "background", "background", true));
				$results->addColumnHor(new columnHor("gis.`c_corporateStructure`", "corporate_structure", "corporate_structure", true));
				$results->addColumnHor(new columnHor("gis.`c_financialHighlights`", "financial_highlights", "financial_highlights", true));
				$results->addColumnHor(new columnHor("gis.`c_keyPersonnel`", "key_personnel", "key_personnel", true));
				$results->addColumnHor(new columnHor("gis.`c_MarketSectorActivity`", "market_sector_activity", "market_sector_activity", true));
				$results->addColumnHor(new columnHor("gis.`c_productRangeActivity`", "product_range_activity", "product_range_activity", true));
				$results->addColumnHor(new columnHor("gis.`c_newProducts`", "new_products", "new_products", true));
				$results->addColumnHor(new columnHor("gis.`c_technicalComparisons`", "technical_comparisons", "technical_comparisons", true));
				$results->addColumnHor(new columnHor("gis.`c_currentPricingLevels`", "current_pricing_levels", "current_pricing_levels", true));
				$results->addColumnHor(new columnHor("gis.`c_packagingAndBranding`", "packaging_and_branding", "packaging_and_branding", true));
				$results->addColumnHor(new columnHor("gis.`c_serviceLevels`", "service_levels", "service_levels", true));
				$results->addColumnHor(new columnHor("gis.`c_geographicActivity`", "geographic_activity", "geographic_activity", true));
				$results->addColumnHor(new columnHor("gis.`c_activeAccounts`", "active_accounts", "active_accounts", true));
				$results->addColumnHor(new columnHor("gis.`c_marketingActivity`", "marketing_activity", "marketing_activity", true));
				$results->addColumnHor(new columnHor("gis.`c_strengthWeakness`", "strength_weakness", "strength_weakness", true));
				$results->addColumnHor(new columnHor("gis.`c_currentStrategy`", "current_strategy", "current_strategy", true));
				$results->addColumnHor(new columnHor("gis.`c_informationSources`", "information_sources", "information_sources", true));
				$results->addColumnHor(new columnHor("gis.`c_distributionStrategy`", "distribution_strategy", "distribution_strategy", true));
				$results->addColumnHor(new columnHor("gis.`c_summary`", "summary", "summary", true));
				$results->addColumnHor(new columnHor("gis.`c_website`", "website", "website", true));
				
			break;
			

			case 'competitor_custom':
			
				$results->setBaseQuery("SELECT * FROM gis WHERE gis.`profileType`='competitor'");
				$results->setOrderBy("gis.id");
				
				$results->addColumnHor(new gisIDColumn("gis.`id`", "id", "id", true));
				$results->addColumnHor(new column("gis.`profileName`", "profile_name", "profile_name", true));
				
				if(in_array("initiator", $this->selectedColumns))
					$results->addColumnHor(new gisOwnerColumn("gis.`initiator`", "initiator", "initiator", true));
				if(in_array("dateAdded", $this->selectedColumns))
					$results->addColumnHor(new gisDateColumn("gis.`dateAdded`", "date_added", "date_added", true));
				if(in_array("dateUpdated", $this->selectedColumns))
					$results->addColumnHor(new gisDateColumn("gis.`dateUpdated`", "date_updated", "date_updated", true));
				if(in_array("background", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_background`", "background", "background", true));
				if(in_array("corporateStructure", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_corporateStructure`", "corporate_structure", "corporate_structure", true));
				if(in_array("financialHighlights", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_financialHighlights`", "financial_highlights", "financial_highlights", true));
				if(in_array("keyPersonnel", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_keyPersonnel`", "key_personnel", "key_personnel", true));
				if(in_array("marketSectorActivity", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_MarketSectorActivity`", "market_sector_activity", "market_sector_activity", true));
				if(in_array("productRangeActivity", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_productRangeActivity`", "product_range_activity", "product_range_activity", true));
				if(in_array("newProducts", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_newProducts`", "new_products", "new_products", true));
				if(in_array("technicalComparisons", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_technicalComparisons`", "technical_comparisons", "technical_comparisons", true));
				if(in_array("currentPricingLevels", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_currentPricingLevels`", "current_pricing_levels", "current_pricing_levels", true));
				if(in_array("packagingAndBranding", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_packagingAndBranding`", "packaging_and_branding", "packaging_and_branding", true));
				if(in_array("serviceLevels", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_serviceLevels`", "service_levels", "service_levels", true));
				if(in_array("geographicActivity", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_geographicActivity`", "geographic_activity", "geographic_activity", true));
				if(in_array("activeAccounts", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_activeAccounts`", "active_accounts", "active_accounts", true));
				if(in_array("marketingActivity", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_marketingActivity`", "marketing_activity", "marketing_activity", true));
				if(in_array("strengthWeakness", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_strengthWeakness`", "strength_weakness", "strength_weakness", true));
				if(in_array("currentStrategy", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_currentStrategy`", "current_strategy", "current_strategy", true));
				if(in_array("informationSources", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_informationSources`", "information_sources", "information_sources", true));
				if(in_array("distributionStrategy", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_distributionStrategy`", "distribution_strategy", "distribution_strategy", true));
				if(in_array("summary", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_summary`", "summary", "summary", true));
				if(in_array("website", $this->selectedColumns))
					$results->addColumnHor(new columnHor("gis.`c_website`", "website", "website", true));
							
				
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
		$reportType->setDataType("string");
		$reportType->setLength(50);
		$reportType->setRequired(true);
		$data = array(
			array('value' => 'competitor', 'display' => 'Competitor Profiles'),
			array('value' => 'competitor_custom', 'display' => 'Competitor Profiles Custom'),
			array('value' => 'market', 'display' => 'Market Profiles'),
		);
		$reportType->setArraySource($data);
		$reportType->setValue("competitor");
		$reportType->setRowTitle("profile_type");
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
				
		$reportType->setFilterObject($this->selectedFilters);
		
		$reportType->setRowTitle("filter_name");
		$default->add($reportType);
		
		$this->addFiltersForm->add($default);
	}
	
	
	private function defineSelectedFilters()
	{
		$reportType = $this->chooseReportForm->get('reportType')->getValue();
		
		$this->selectedFilters = new selectedFiltersList();
		
		if ($reportType == 'competitor' || $reportType == 'competitor_custom')
		{
			$profileName = new filterCombo("profileName");
			$profileName->setField("gis.profileName");
			$profileName->setSQLSource("gis","SELECT DISTINCT profileName AS name, profileName AS data FROM gis WHERE profileType='competitor' ORDER BY name ASC");
			$profileName->setRowTitle("profile_name");
			$this->selectedFilters->add($profileName);
		}
		
		if ($reportType == "market")
		{
			$profileName = new filterCombo("profileName");
			$profileName->setField("gis.profileName");
			$profileName->setSQLSource("gis","SELECT DISTINCT profileName AS name, profileName AS data FROM gis WHERE profileType='market' ORDER BY name ASC");
			$profileName->setRowTitle("profile_name");
			$this->selectedFilters->add($profileName);
		}
	}
}

class gisOwnerColumn extends columnHor
{
	public function getOutput($fields)
	{
		$xml = "<searchColumnHor sortable=\"" . $this->getSortable() . "\"><textHor>\n";
		$xml .= usercache::getInstance()->get($fields[$this->getName()])->getName();
		$xml .= "</textHor></searchColumnHor>";
		
		return $xml;
	}
}

class gisDateColumn extends columnHor
{
	public function getOutput($fields)
	{
		$xml = "<searchColumnHor sortable=\"" . $this->getSortable() . "\"><textHor>\n";
		$xml .= page::transformDateForPHP($fields[$this->getName()]);
		$xml .= "</textHor></searchColumnHor>";
		
		return $xml;
	}
}

class gisTranslateColumn extends columnHor
{
	public function getOutput($fields)
	{
		$xml = "<searchColumnHor sortable=\"" . $this->getSortable() . "\"><textHor>\n";
		$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		$xml .= "</textHor></searchColumnHor>";
		
		return $xml;
	}
}

class gisIDColumn extends columnHor
{
	public function getOutput($fields)
	{
		$xml = "<searchColumnHor sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<linkHor url=\"/apps/gis/index?id=" . $fields[$this->getName()] . "\">" . $fields[$this->getName()] . "</linkHor>";
		$xml .= "</searchColumnHor>";
		
		return $xml;
	}
}

?>