<?php

require 'lib/comm.php';

class viewNews extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	function __construct()
	{
		
		parent::__construct();
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('comm');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		
		$this->defineChooseReportForm();
		
		$this->chooseReportForm->loadSessionData();
		
		$this->chooseReportForm->processPost();
		$this->chooseReportForm->validate();
		
		
		$this->defineSelectedFilters();
		
		$this->selectedFilters->form->loadSessionData();
		$this->defineAddFiltersForm();
		$this->selectedFilters->processPost();
				
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
				$this->redirect("viewNews?");
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
			
			$this->add_output("<commsearch>");
			
			
			
			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'run')
			{
				if ($this->selectedFilters->form->validate())
				{
					$this->redirect("viewNews?action=view");
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
			$snapins = new snapinGroup('comm_search');
			$snapins->register('apps/comms', 'loadcomm', true, true);
			$snapins->register('apps/comms', 'yourcomm', true, true);

			
			$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
			
			
			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_incomplete_comm")
			{
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
				{
					foreach($_SESSION["searchColumns"] as $val)
						$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_complete_comm")
			{
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"]))
				{
					foreach($_SESSION["searchColumns"] as $val)
						$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}
			
			if($this->chooseReportForm->get("reportType")->getValue() == "custom_summary_view_all_comm")
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
			
			$this->add_output("</commsearch>");

			// if post
			
			// save, redirect to view			
			
		}
		
		

		$this->output('./apps/comms/xsl/search.xsl');	
	}
	
	
	// Show Excel Results
	private function showExcelResults()
	{
		$results = new excelResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("comms");
		
		$results->setBaseQuery("SELECT * FROM comm");
		$results->setOrderBy("id");
		
		$results->addColumn(new commTranslateColumn("comm.`id`", "id", "id", true));
		//$results->addColumn(new column("employee.`firstName`", "firstName", "first_name", true));
		//$results->addColumn(new column("employee.`lastName`", "lastName", "last_name", true));
		
		$dummy = new comm();
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary':
				
				$results->setBaseQuery("SELECT * FROM comm");
				$results->setOrderBy("comm.id");
				
				$results->addColumn(new commIDColumn("comm.`id`", "id", "id", true));
				$results->addColumn(new column("comm.`newsType`", "newsType", "newsType", true));
				$results->addColumn(new commDateTimeColumn("comm.`openDate`", "openDate", "openDate", true));
				$results->addColumn(new commOwnerColumn("comm.`creator`", "creator", "creator", true));
				
			break;
			
		}
		
		$results->performQuery();
	
		$results->display();
	
		exit(0);
	}
	
	
	private function showResults()
	{
		if (isset($_REQUEST['load']))
		{
			// load saved stuff from db into session...
			
			$this->redirect("viewNews?action=view");
		}
		
		$results = new searchResults();
		
		$results->setSelectedFilters($this->selectedFilters);
		
		$results->setDatabase("comms");
		
		$results->setBaseQuery("SELECT * FROM comm");
		$results->setOrderBy("comm.id");
		
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary':
				
				$results->setBaseQuery("SELECT * FROM comm");
				$results->setOrderBy("comm.id");
				
				$results->addColumn(new commIDColumn("comm.`id`", "id", "id", true));
				$results->addColumn(new commPublished("comm.`newsType`", "newsType", "newsType", true));
				$results->addColumn(new column("comm.`subject`", "subject", "subject", true));
				$results->addColumn(new commDateTimeColumn("comm.`openDate`", "openDate", "openDate", true));
				$results->addColumn(new commOwnerColumn("comm.`creator`", "creator", "creator", true));
				
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
			array('value' => 'summary', 'display' => 'Summary')
		);
				
		$reportType->setArraySource($data);
		$reportType->setValue("summary");
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
		
		$reportType->setFilterObject($this->selectedFilters);
		
		$reportType->setRowTitle("filter_name");
		$default->add($reportType);
		
		$this->addFiltersForm->add($default);
	}
	
	
	private function defineSelectedFilters()
	{
		$reportType = $this->chooseReportForm->get('reportType')->getValue();
		
		$this->selectedFilters = new selectedFiltersList();
		
		if ($reportType == 'summary')
		{			
			
		}
	}
}

class commOwnerColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= usercache::getInstance()->get($fields[$this->getName()])->getName();
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class commDateTimeColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= page::transformDateTimeForPHP($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class commTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}

class commIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/comms/index?id=" . $fields[$this->getName()] . "\">" . $fields[$this->getName()] . "</link>";
		$xml .= "</searchColumn>";
		
		return $xml;
	}
}

class commPublished extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
				
		$fields[$this->getName()] == "1" ? $xml .= translate::getInstance()->translate("comm_published") : $xml .= translate::getInstance()->translate("comm_unpublished");
		
		$xml .= "</text></searchColumn>";
		
		return $xml;
	}
}
?>