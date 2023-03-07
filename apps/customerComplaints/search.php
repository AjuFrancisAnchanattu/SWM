<?php

include_once 'lib/sapCustomer.php';
include_once 'lib/complaintLib.php';
include_once 'lib/controls/mySelectedFiltersList.php';
include_once 'lib/controls/reportsFilterComboSub.php';
include_once 'lib/controls/moneyFilter.php';
include_once 'lib/controls/filterExistsCombo.php';
include_once 'lib/controls/mySearchResults.php';
include_once 'lib/controls/myExcelResults.php';
include_once 'lib/controls/myFilterCombo.php';
include_once 'lib/controls/daysOpenFilter.php';

/**
 * Allows users to search for specific report data (only reports on submitted complaints)
 */
class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;
	
	public $reportId;	// the session id of the current report

	function __construct()
	{
		parent::__construct();
		
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('customerComplaints');

		// Ensure a report session id is set
		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'bookmark'))
		{
			/** Load filters into session so that bookmarked search can be edited **/
			do
			{
				$reportId = 1 * rand(1, 999999);
			}
			while(isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $reportId]));

			$this->reportId = $reportId;
		}

		else
		{
			if (isset($_GET['reportId']))
			{
				$this->reportId = $_REQUEST['reportId'];
			}
			else
			{
				do
				{
					$reportId = 1 * rand(1, 999999);
				}
				while(isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $reportId]));

				page::redirect("./search?reportId=" . $reportId);
			}

			// Clear session if using reports link from the menu
			if (isset($_GET['clear']) && $_GET['clear'] == 'true')
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]);

				$this->redirect("search?");
			}
		}

		$loadedBookmark = false;

		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'bookmark'))
		{
			$bookmarkDataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT `filters`, `reportType`, `columns`
				FROM bookmarks
				WHERE `id` = '" . $_REQUEST['bookmarkId'] . "'");

			$fieldsBookmark = mysql_fetch_array($bookmarkDataset);

			$filters = unserialize($fieldsBookmark['filters']);
			$columns = unserialize($fieldsBookmark['columns']);

			$this->selectedFilters = $filters;
	
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] = array();
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["filters"]["default"] = array();
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"] = $columns;
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["chooseReportForm"] = array('reportType' => $fieldsBookmark['reportType']);

			foreach ($this->selectedFilters->getAllControls() as $controlKey => $controlValue)
			{
				if ($controlValue->getVisible())
				{
					array_push($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"], $controlKey);

					$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["filters"]["default"]["$controlKey"] = $this->selectedFilters->get($controlKey)->getValue();
				}
			}

			$loadedBookmark = true;
		}

		if (isset($_POST['reportType']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["chooseReportForm"]["reportType"] = $_POST['reportType'];
		}

		if (isset($_POST['columns']))
		{
			//var_dump('test');die();
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"] = $_POST['columns'];
		}
		else
		{
			$this->selectedColumns = array();

			if (isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"])
				&& count($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"]) > 0)
			{
				foreach($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"] as $val)
				{
					$this->selectedColumns[] = $val;
				}
			}
			else
			{
				$this->selectedColumns = array();
				$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"] = array();
			}
		}

		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");

		$this->defineChooseReportForm();

		$this->chooseReportForm->loadSessionData();

		$this->chooseReportForm->processPost();
		$this->chooseReportForm->validate();

		$this->defineSelectedFilters();

		$this->selectedFilters->form->loadSessionData();
		$this->defineAddFiltersForm();
		$this->selectedFilters->processPost();

		$validSelFilters = true;

//		foreach ($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] as $selFilter)
//		{
//			if (!$selFilter->validate())
//			{
//				$validSelFilters = false;
//			}
//		}
		
		if (((isset($_REQUEST['action']) && $_REQUEST['action'] == 'view') || $loadedBookmark == true) && $validSelFilters)
		{
			if (isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"]))
			{
				for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"]); $i++)
				{
					$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"][$i])->setVisible(true);
				}
			}

			if (isset($_REQUEST['save']) && $_REQUEST['save'] == 'true')
			{
				$serializedSelectedFilters = mysql_escape_string(serialize($this->selectedFilters));
				$whatColumns = mysql_escape_string(serialize($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"]));


				if (isset($_REQUEST['bookmarkId']))
				{
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						UPDATE bookmarks
						SET filters = '" . $serializedSelectedFilters . "',
							reportType = '" . $this->chooseReportForm->get("reportType")->getValue() . "',
							columns = '" . $whatColumns . "'
						WHERE id = " . $_REQUEST['bookmarkId']);

					$this->redirect("editBookmark?mode=edit&bookmarkId=" . $_REQUEST['bookmarkId'] . "&reportId=" . $this->reportId); // redirect to update bookmark name
				}
				else
				{
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						INSERT INTO bookmarks
						(name, owner, filters, reportType, columns)
						VALUES ('" . page::nowDateForMysql() . "',
							'" . currentuser::getInstance()->getNTLogon() . "',
							'" . $serializedSelectedFilters . "',
							'" . $this->chooseReportForm->get("reportType")->getValue() . "',
							'" . $whatColumns."')");

					$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						SELECT *
						FROM bookmarks
						WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "'
						ORDER BY `id` DESC LIMIT 1");

					$fields = mysql_fetch_array($dataset);

					$this->redirect("editBookmark?mode=edit&bookmarkId=" . $fields['id'] . "&reportId=" . $this->reportId); // redirect to update bookmark name
				}
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
			$this->add_output("<complaintSearch>");

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'run')
			{
				if ($this->selectedFilters->form->validate())
				{
					if (isset($_REQUEST['bookmarkId']))
					{
						$bookmarkEdited = '&bookmarkEdited=true';

						$this->redirect("search?action=view&bookmarkId=" . $_REQUEST['bookmarkId'] . "&reportId=" . $this->reportId . $bookmarkEdited);
					}
					else
					{
						$this->redirect("search?action=view&reportId=" . $this->reportId);
					}
				}
				else
				{
					$this->add_output("<error />");
				}
			}

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'changeReportType')
			{
				$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] = array();
			}

			if (!isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"]))
			{
				$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] = array();
			}

			if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'addFilter')
			{
				$this->addFiltersForm->processPost();

				$filters = explode("||", $this->addFiltersForm->get('filters')->getValue());

				for ($i=0; $i < count($filters); $i++)
				{
					if (!in_array($filters[$i], $_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"]) && $filters[$i] != "")
					{
						$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"][] = $filters[$i];
					}
				}

				$this->addFiltersForm->get('filters')->setValue("");
			}

			if (isset($_REQUEST['action']) && strstr($_REQUEST['action'], 'removeFilter'))
			{
				$remove = substr($_REQUEST['action'], 13, strlen($_REQUEST['action']) - 13);

				page::addDebug("remove $remove", __FILE__, __LINE__);

				$selectedFilters = $_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"];

				$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] = array();

				for ($i=0; $i < count($selectedFilters); $i++)
				{
					if ($remove != $selectedFilters[$i])
					{
						$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"][] = $selectedFilters[$i];
					}
				}
			}

			if (isset($_REQUEST['action']) && strstr($_REQUEST['action'], 'removeAllFilters'))
			{
				$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"] = array();
			}

			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"]); $i++)
			{
				$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["selectedFilters"][$i])->setVisible(true);
			}

			$this->getSnapins();

			if ($this->chooseReportForm->get("reportType")->getValue() == "custom")
			{
				$this->add_output("<columnFilters>");

					$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						SELECT distinct(reportColumns.translationName)
						FROM reportColumns
						LEFT OUTER JOIN intranet.translations
						ON reportColumns.translationName = intranet.translations.translateFrom
						WHERE intranet.translations.application = 'global' 
						ORDER BY intranet.translations." . strtolower(currentuser::getInstance()->getLanguage()) . " ASC"
						);

					// Output all columns on left side
					while ($fields = mysql_fetch_assoc($dataset))
					{
						$this->add_output("<columnSelectionOption><name>" . $fields['translationName'] . "</name></columnSelectionOption>");
					}

					// Output selected columns on right side
					if (isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"])
						&& is_array($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"]))
					{
						foreach($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]["searchColumns"] as $val)
						{
							$this->add_output("<columnSelectionOptionSelected><name>" . $val . "</name></columnSelectionOptionSelected>");
						}
					}

				$this->add_output("</columnFilters>");
			}

			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");
			
			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");

			$this->add_output("<selectedFilters>");
			$this->add_output($this->selectedFilters->getOutput());
			$this->add_output("</selectedFilters>");

			$this->add_output("</complaintSearch>");
		}

		if (isset($_REQUEST['bookmarkEdited']))
		{
			$this->add_output("<bookmarkEdited />");
		}

		if (isset($_REQUEST['bookmarkId']))
		{
			$this->add_output("<bookmarkId>" . $_REQUEST['bookmarkId'] . "</bookmarkId>");
		}

		$this->add_output("<reportId>" . $this->reportId . "</reportId>");

		$this->output('./apps/customerComplaints/xsl/search.xsl');
	}

	/**
	 * Gets the snapins to display on the page
	 */
	private function getSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');

		if (isset($this->complaintId) && $this->complaintId > 0)
		{
			$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		}
		else
		{
			$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		}

		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
	}

	private function showExcelResults()
	{
		$results = new myExcelResults();

		$results = $this->processResults($results);

		$results->display();

		exit(0);
	}

	private function showResults()
	{
		if (isset($_REQUEST['load']))
		{
			// Load database into session
			$this->redirect("search?action=view&reportId=" . $reportId);
		}

		$results = new mySearchResults($this->reportId);

		$results = $this->processResults($results);

		$this->add_output($results->getOutput());
	}

	private function processResults($results)
	{
		$results->setSelectedFilters($this->selectedFilters);

		$results->setDatabase("complaintsCustomer");

		$results->setBaseQuery("SELECT *
			FROM complaint LEFT OUTER JOIN evaluation ON complaint.id = evaluation.complaintId
			LEFT OUTER JOIN conclusion ON complaint.id = conclusion.complaintId LEFT OUTER JOIN invoicePopup ON complaint.id = invoicePopup.complaintId
			LEFT OUTER JOIN conclusionReturnNo ON complaint.id = conclusionReturnNo.complaintId
			LEFT OUTER JOIN SAP.invoices ON complaintsCustomer.invoicePopup.invoiceNo = SAP.invoices.invoiceNo 
			WHERE complaint.submitStatus = 1 GROUP BY complaint.id");

		$results->setOrderBy("complaint.id");

		// Default ID column to show
		$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

		switch ($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'summary':

				$columns = array(
					"complaint_conclusion_owner",
					"evaluation_owner",
					"date_complaint",
					"complaint_submission_date",
					"days_open",
					"category",										
					"despatch_site",
					"manufacturing_site",
					"origin_site_error",
					"business_unit",
					"complaint_value_gbp",
					"sap_customer_number",
					"sap_customer_name",
					"complaint_validated",
					"corrective_action_complete",
					"validation_verification_complete",
					"credit_authorisation_complete",
					"total_closure"
				);

				break;

			case 'custom':

				$columns = $this->selectedColumns;

				break;

			case 'performance':

				$columns = array(
					"date_complaint",
					"complaint_submission_date",
					"days_open",
					"origin_site_error",
					"business_unit",
					"total_closure_date",
					"implemented_perm_corrective_actions_imp_date",
					"complaint_value",
					"currency",
					"time_to_log_complaint",
					"time_for_analysis",
					"time_for_root_cause_date",
					"time_for_containment_actions",
					"time_for_possible_solutions",
					"time_for_implemented_permanent_corrective_actions",
					"time_for_corrective_actions_validation",
					"time_for_preventive_actions",
					"time_for_corrective_actions_completion",
					"time_for_validation_verification_completion",
					"time_for_credit_authorisation",
					"total_close_out_performance"
				);

				break;
		}

		foreach ($columns AS $column)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT *
				FROM reportColumns
				WHERE translationName = '" . $column . "'");

			$fields = mysql_fetch_assoc($dataset);

			$results->addColumn(new $fields['columnType'](
				$fields['table'] . ".`" . $fields['fieldName'] . "`",
				$fields['translationName'],
				$fields['translationName'],
				true));
		}

		$results->performQuery();

		return $results;
	}

	private function defineChooseReportForm()
	{
		$this->chooseReportForm = new form("chooseReportForm");

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]['chooseReportForm']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]['chooseReportForm'] = array();
		}

		$sessionName = $_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]['chooseReportForm'];

		$this->chooseReportForm->setStoreInSession(true, $sessionName);
		$default = new group("default");

		$reportType = new radio("reportType");
		$reportType->setDataType("string");
		$reportType->setLength(50);
		$reportType->setRequired(true);

		$data = array(
			array('value' => 'summary', 'display' => translate::getInstance()->translate('summary')),
			array('value' => 'custom', 'display' => translate::getInstance()->translate('custom')),
			array('value' => 'performance', 'display' => translate::getInstance()->translate('performance'))
		);

		$reportType->setArraySource($data);

		if (isset($_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]['chooseReportForm']['reportType']))
		{
			$reportValue = $_SESSION['apps'][$GLOBALS['app']]['report_' . $this->reportId]['chooseReportForm']['reportType'];
		}
		else
		{
			$reportValue = 'summary';
		}

		$reportType->setValue($reportValue);
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

		$this->selectedFilters = new mySelectedFiltersList($this->reportId);
//
//		if ($reportType == 'summary')
//		{
			$complaintId = new filterCombo("complaintId");
			$complaintId->setField("complaint.id");
			$complaintId->setSQLSource("complaintsCustomer","SELECT DISTINCT id AS name, id AS data FROM complaint ORDER BY name ASC");
			$complaintId->setRowTitle("complaint_id");
			$this->selectedFilters->add($complaintId);

			$complaintAndConclusionOwner = new filterCombo("complaintAndConclusionOwner");
			$complaintAndConclusionOwner->setField("complaint.complaintOwner");
			$complaintAndConclusionOwner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.complaint WHERE complaint.complaintOwner = membership.employee.ntlogon ORDER BY name ASC");
			$complaintAndConclusionOwner->setRowTitle("complaint_conclusion_owner");
			$this->selectedFilters->add($complaintAndConclusionOwner);
			
			$initiator = new filterCombo("initiator");
			$initiator->setField("complaint.submitBy");
			$initiator->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.complaint WHERE complaint.submitBy = membership.employee.ntlogon ORDER BY name ASC");
			$initiator->setRowTitle("initiator");
			$this->selectedFilters->add($initiator);

			$evaluationOwner = new filterCombo("evaluationOwner");
			$evaluationOwner->setField("complaint.evaluationOwner");
			$evaluationOwner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.complaint WHERE complaint.evaluationOwner = membership.employee.ntlogon ORDER BY name ASC");
			$evaluationOwner->setRowTitle("evaluation_owner");
			$this->selectedFilters->add($evaluationOwner);

			$complaintDate = new filterDateRange("complaintDate");
			$complaintDate->setField("complaint.complaintDate");
			$complaintDate->setRowTitle("date_complaint");
			$this->selectedFilters->add($complaintDate);
			
			$createdDate = new filterDateRange("submissionDate");
			$createdDate->setField("complaint.submissionDate");
			$createdDate->setRowTitle("complaint_submission_date");
			$this->selectedFilters->add($createdDate);

			$category = new reportsFilterComboSub("category");
			$category->setField("complaint.categoryId");
			$category->setSQLSource("complaintsCustomer","SELECT DISTINCT SUBSTRING(`selectionOption`,1,2) AS name, id AS data FROM selectionOptions WHERE typeId = 3 ORDER BY name ASC");
			$category->setRowTitle("category");
			$this->selectedFilters->add($category);

			$originalCategory = new reportsFilterComboSub("originalCategory");
			$originalCategory->setField("complaint.originalCategoryId");
			$originalCategory->setSQLSource("complaintsCustomer","SELECT DISTINCT SUBSTRING(`selectionOption`,1,2) AS name, id AS data FROM selectionOptions WHERE typeId = 3 ORDER BY name ASC");
			$originalCategory->setRowTitle("original_category");
			$this->selectedFilters->add($originalCategory);			
			
			$despatchSite = new filterComboSub("despatchSite");
			$despatchSite->setField("complaint.despatchSite");
			$despatchSite->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 2 ORDER BY name ASC");
			$despatchSite->setRowTitle("despatch_site");
			$this->selectedFilters->add($despatchSite);

			$manufacturingSite = new filterComboSub("manufacturingSite");
			$manufacturingSite->setField("complaint.manufacturingSite");
			$manufacturingSite->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 2 ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);

			$originSiteError = new myFilterCombo("siteOriginError");
			$originSiteError->setField("complaint.siteOriginError");
			$originSiteError->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 2 ORDER BY name ASC");
			$originSiteError->setRowTitle("origin_site_error");
			$this->selectedFilters->add($originSiteError);

//			$salesOffice = new salesOfficeCombo("salesOffice");
//			$salesOffice->setField("complaint.sapCustomerNo");
//			$salesOffice->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, selectionOption AS data FROM selectionOptions WHERE typeId = 2 ORDER BY name ASC");
//			$salesOffice->setRowTitle("sales_office");
//			$this->selectedFilters->add($salesOffice);

			$invoiceDate = new filterDateRange("invoiceDate");
			$invoiceDate->setField("invoices.invoiceDate");
			$invoiceDate->setRowTitle("invoice_date");
			$this->selectedFilters->add($invoiceDate);		
						
			$businessUnit = new businessUnitCombo("businessUnit");
			$businessUnit->setField("complaint.sapCustomerNo");
			$businessUnit->setSQLSource("SAP","SELECT distinct(newMrkt) AS name, newMrkt AS data FROM businessUnits ORDER BY name ASC");
			$businessUnit->setRowTitle("business_unit");
			$this->selectedFilters->add($businessUnit);

			$sapCustomerNumber = new filterSAPNumber("sapCustomerNumber");
			$sapCustomerNumber->setField("complaint.sapCustomerNo");
			$sapCustomerNumber->setRowTitle("sap_customer_number");
			$sapCustomerNumber->setUrl("/apps/customerComplaints/ajax/sapCustomerNo?");
			$this->selectedFilters->add($sapCustomerNumber);

			$sapCustomerName = new filterSAPNumber("sapCustomerName");
			$sapCustomerName->setField("complaint.sapCustomerNo");
			$sapCustomerName->setRowTitle("sap_customer_name");
			$sapCustomerName->setUrl("/apps/customerComplaints/ajax/sapCustomerName?");
			$this->selectedFilters->add($sapCustomerName);
			
			$sapBatchNo = new filterCombo("sapBatchNo");
			$sapBatchNo->setField("invoicePopup.batch_edit");
			$sapBatchNo->setSQLSource("complaintsCustomer","SELECT DISTINCT batch_edit AS name, batch_edit AS data from invoicePopup ORDER BY name ASC");
			$sapBatchNo->setRowTitle("batch_no");
			$this->selectedFilters->add($sapBatchNo);
		
			$sapItemNo = new filterCombo("invoicesId");
			$sapItemNo->setField("invoicePopup.invoicesId");
			$sapItemNo->setSQLSource("complaintsCustomer","SELECT DISTINCT invoicesId AS name, invoicesId AS data from invoicePopup ORDER BY name ASC");
			$sapItemNo->setRowTitle("sap_item_number");
			$this->selectedFilters->add($sapItemNo);
			
			$sapMaterialNo = new filterCombo("sapMatNo");
			$sapMaterialNo->setField("invoices.material");
			$sapMaterialNo->setSQLSource("SAP","SELECT DISTINCT material AS name, material AS data from SAP.invoices INNER JOIN complaintsCustomer.invoicePopup ON SAP.invoices.invoiceNo = complaintsCustomer.invoicePopup.invoiceNo ORDER BY name ASC");
			$sapMaterialNo->setRowTitle("material_number");
			$this->selectedFilters->add($sapMaterialNo);
			
			$sapMaterialNo = new filterCombo("sapMatGroup");
			$sapMaterialNo->setField("invoices.materialGroup");
			$sapMaterialNo->setSQLSource("SAP","SELECT DISTINCT materialGroup AS name, materialGroup AS data from SAP.invoices INNER JOIN complaintsCustomer.invoicePopup ON SAP.invoices.invoiceNo = complaintsCustomer.invoicePopup.invoiceNo ORDER BY name ASC");
			$sapMaterialNo->setRowTitle("material_group");
			$this->selectedFilters->add($sapMaterialNo);

			$totalClosure = new filterCombo("totalClosure");
			$totalClosure->setField("complaint.totalClosure");
			$totalClosure->setArraySource(array(
				array("display" => translate::getInstance()->translate("Open"), "value" => "0"),
				array("display" => translate::getInstance()->translate("Closed"), "value" => "1")
				));
			$totalClosure->setRowTitle("total_closure");
			$this->selectedFilters->add($totalClosure);

			$customerComplaintRef = new filterCombo("complaintRef");
			$customerComplaintRef->setField("complaint.complaintRef");
			$customerComplaintRef->setSQLSource("complaintsCustomer","SELECT DISTINCT complaintRef AS name, complaintRef AS data from complaint ORDER BY name ASC");
			$customerComplaintRef->setRowTitle("customer_complaint_ref");
			$this->selectedFilters->add($customerComplaintRef);

			$invoiceNo = new filterCombo("invoiceNo");
			$invoiceNo->setField("invoicePopup.invoiceNo");
			$invoiceNo->setSQLSource("complaintsCustomer","SELECT DISTINCT invoiceNo AS name, invoiceNo AS data from invoicePopup ORDER BY name ASC");
			$invoiceNo->setRowTitle("invoice_number");
			$this->selectedFilters->add($invoiceNo);

			$complaintValueGBP = new moneyFilter("complaintValueGBP");
			$complaintValueGBP->setField("complaint.complaintValueGBP");
			$complaintValueGBP->setRowTitle("gbpComplaintValue_quantity");
			$this->selectedFilters->add($complaintValueGBP);
			
			$daysOpen = new daysOpenFilter("daysOpen");
			$daysOpen->setField("complaint.id");
			$daysOpen->setRowTitle("days_open");
			$this->selectedFilters->add($daysOpen);

			$creditNoteRequested = new filterCombo("creditNoteRequested");
			$creditNoteRequested->setField("complaint.creditNoteRequested");
			$creditNoteRequested->setArraySource(array(
				array("display" => "No", "value" => "0"),
				array("display" => "Yes", "value" => "1")
				));
			$creditNoteRequested->setRowTitle("credit_note_requested");
			$this->selectedFilters->add($creditNoteRequested);

			$factoredProduct = new filterCombo("factoredProduct");
			$factoredProduct->setField("complaint.factoredProduct");
			$factoredProduct->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$factoredProduct->setRowTitle("factored_product");
			$this->selectedFilters->add($factoredProduct);

			$factoredProduct = new filterCombo("factoredProduct");
			$factoredProduct->setField("complaint.factoredProduct");
			$factoredProduct->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$factoredProduct->setRowTitle("factored_product");
			$this->selectedFilters->add($factoredProduct);

			$sampleReceived = new filterCombo("sampleReceived");
			$sampleReceived->setField("evaluation.sampleReceived");
			$sampleReceived->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$sampleReceived->setRowTitle("sample_received");
			$this->selectedFilters->add($sampleReceived);

			$analysisAuthor = new filterCombo("analysisAuthor");
			$analysisAuthor->setField("evaluation.analysisAuthor");
			$analysisAuthor->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.evaluation WHERE evaluation.analysisAuthor = membership.employee.ntlogon ORDER BY name ASC");
			$analysisAuthor->setRowTitle("analysis_author");
			$this->selectedFilters->add($analysisAuthor);

			$evaluationType = new filterCombo("evaluationType");
			$evaluationType->setField("evaluation.full8d");
			$evaluationType->setArraySource(array(
				array("display" => translate::getInstance()->translate("root_cause_corrective_action"), "value" => "0"),
				array("display" => translate::getInstance()->translate("full_8d"), "value" => "1")
				));
			$evaluationType->setRowTitle("evaluation_type");
			$this->selectedFilters->add($evaluationType);

			$isComplaintValid = new filterCombo("isComplaintValid");
			$isComplaintValid->setField("evaluation.complaintJustified");
			$isComplaintValid->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$isComplaintValid->setRowTitle("complaint_validated");
			$this->selectedFilters->add($isComplaintValid);
/*
			$isComplaintValid = new filterCombo("isComplaintValid");
			$isComplaintValid->setField("evaluation.complaintJustified");
			$isComplaintValid->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$isComplaintValid->setRowTitle("complaint_validated");
			$this->selectedFilters->add($isComplaintValid);
*/
			$rootCauseCode = new filterCombo("rootCauseCode");
			$rootCauseCode->setField("evaluation.rootCauseCode");
			$rootCauseCode->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 6 ORDER BY name ASC");
			$rootCauseCode->setRowTitle("root_causes_code");
			$rootCauseCode->setTranslate(true);
			$this->selectedFilters->add($rootCauseCode);

			$failureCode = new filterCombo("failureCode");
			$failureCode->setField("evaluation.failureCode");
			$failureCode->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 5 ORDER BY name ASC");
			$failureCode->setRowTitle("failure_code");
			$failureCode->setTranslate(true);
			$this->selectedFilters->add($failureCode);
			
			// Added 5/11/2012 - Rob
			$severity = new filterCombo("severity");
			$severity->setField("evaluation.severity");
			$severity->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 8 ORDER BY name ASC");
			$severity->setRowTitle("severity");
			$severity->setTranslate(true);
			$this->selectedFilters->add($severity);
            
            $lossDamages = new filterCombo("lossDamages");
			$lossDamages->setField("evaluation.lossDamages");
			$lossDamages->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0")
				));
			$lossDamages->setRowTitle("lossDamages");
			$this->selectedFilters->add($lossDamages);

			$attributableProcess = new filterCombo("attributableProcess");
			$attributableProcess->setField("evaluation.attributableProcess");
			$attributableProcess->setSQLSource("complaintsCustomer","SELECT selectionOption AS name, id AS data FROM selectionOptions WHERE typeId = 7 ORDER BY name ASC");
			$attributableProcess->setRowTitle("attributable_process");
			$attributableProcess->setTranslate(true);
			$this->selectedFilters->add($attributableProcess);

			$goodsAction = new filterCombo("goodsAction");
			$goodsAction->setField("evaluation.goodsAction");
			$goodsAction->setArraySource(array(
				array("display" => translate::getInstance()->translate("no_action"), "value" => "-1"),
				array("display" => translate::getInstance()->translate("return_goods"), "value" => "1"),
				array("display" => translate::getInstance()->translate("dispose_goods"), "value" => "0")
				));
			$goodsAction->setRowTitle("goods_action");
			$this->selectedFilters->add($goodsAction);

			$goodsAction = new filterCombo("goodsAction");
			$goodsAction->setField("evaluation.goodsAction");
			$goodsAction->setArraySource(array(
				array("display" => translate::getInstance()->translate("no_action"), "value" => "-1"),
				array("display" => translate::getInstance()->translate("return_goods"), "value" => "1"),
				array("display" => translate::getInstance()->translate("dispose_goods"), "value" => "0")
				));
			$goodsAction->setRowTitle("goods_action");
			$this->selectedFilters->add($goodsAction);

			$goodsReturnApprover = new filterCombo("goodsReturnApprover");
			$goodsReturnApprover->setField("evaluation.returnGoodsNTLogon");
			$goodsReturnApprover->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.evaluation WHERE evaluation.returnGoodsNTLogon = membership.employee.ntlogon ORDER BY name ASC");
			$goodsReturnApprover->setRowTitle("RETURN_GOODS_APPROVER");
			$this->selectedFilters->add($goodsReturnApprover);
			
			$goodsDisposeApprover = new filterCombo("goodsDisposeApprover");
			$goodsDisposeApprover->setField("evaluation.disposeGoodsNTLogon ");
			$goodsDisposeApprover->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaintsCustomer.evaluation WHERE evaluation.disposeGoodsNTLogon  = membership.employee.ntlogon ORDER BY name ASC");
			$goodsDisposeApprover->setRowTitle("DISPOSE_GOODS_APPROVER");
			$this->selectedFilters->add($goodsDisposeApprover);
			
			$correctiveActionComplete = new myFilterCombo("correctiveActionComplete");
			$correctiveActionComplete->setField("evaluation.correctiveAction");
			$correctiveActionComplete->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0||NULL")
				));
			$correctiveActionComplete->setRowTitle("corrective_action_complete");
			$this->selectedFilters->add($correctiveActionComplete);

			$validationVerificationComplete = new myFilterCombo("validationVerificationComplete");
			$validationVerificationComplete->setField("evaluation.validationVerification");
			$validationVerificationComplete->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => "1"),
				array("display" => translate::getInstance()->translate("No"), "value" => "0||NULL")
				));
			$validationVerificationComplete->setRowTitle("validation_verification_complete");
			$this->selectedFilters->add($validationVerificationComplete);

			$sapReturnNo = new filterCombo("sapReturnNo");
			$sapReturnNo->setField("conclusionReturnNo.sapReturnNo");
			$sapReturnNo->setSQLSource("complaintsCustomer","SELECT DISTINCT sapReturnNo AS name, sapReturnNo AS data from conclusionReturnNo ORDER BY name ASC");
			$sapReturnNo->setRowTitle("sap_return_number");
			$this->selectedFilters->add($sapReturnNo);

			$isCreditOrDebitNote = new filterCombo("isCreditOrDebitNote");
			$isCreditOrDebitNote->setField("conclusion.isCreditOrDebitNote");
			$isCreditOrDebitNote->setArraySource(array(
				array("display" => "credit", "value" => "1"),
				array("display" => "debit", "value" => "0")
				));
			$isCreditOrDebitNote->setTranslate(true);
			$isCreditOrDebitNote->setRowTitle("is_credit_or_debit_note");
			$this->selectedFilters->add($isCreditOrDebitNote);

			$creditAuthorisationComplete = new myFilterCombo("creditAuthorisationComplete");
			$creditAuthorisationComplete->setField("conclusion.creditAuthorisation");
			$creditAuthorisationComplete->setArraySource(array(
				array("display" => "yes", "value" => "1"),
				array("display" => "no", "value" => "0||NULL")
				));
			$creditAuthorisationComplete->setTranslate(true);
			$creditAuthorisationComplete->setRowTitle("credit_authorisation_complete");
			$this->selectedFilters->add($creditAuthorisationComplete);

			$analysisExists = new filterExistsCombo("analysisExists");
			$analysisExists->setField("evaluation.analysis");
			$analysisExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$analysisExists->setTranslate(true);
			$analysisExists->setRowTitle("analysis_exists");
			$this->selectedFilters->add($analysisExists);

			// Added 5/11/2012 - Rob
			$reasonsNonDetectionExists = new filterExistsCombo("reasonsNonDetectionExists");
			$reasonsNonDetectionExists->setField("evaluation.reasonsNonDetection");
			$reasonsNonDetectionExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$reasonsNonDetectionExists->setTranslate(true);
			$reasonsNonDetectionExists->setRowTitle("reasons_non_detection_exists");
			$this->selectedFilters->add($reasonsNonDetectionExists);
			
			$rootCauseExists = new filterExistsCombo("rootCauseExists");
			$rootCauseExists->setField("evaluation.rootCauses");
			$rootCauseExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$rootCauseExists->setTranslate(true);
			$rootCauseExists->setRowTitle("root_cause_exists");
			$this->selectedFilters->add($rootCauseExists);
						
			$containmentActionsExist = new filterExistsCombo("containmentActionsExist");
			$containmentActionsExist->setField("evaluation.containmentActions");
			$containmentActionsExist->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$containmentActionsExist->setTranslate(true);
			$containmentActionsExist->setRowTitle("containment_action_exists");
			$this->selectedFilters->add($containmentActionsExist);
			
			$possibleSolutionExists = new filterExistsCombo("possibleSolutionExists");
			$possibleSolutionExists->setField("evaluation.possibleSolutions");
			$possibleSolutionExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$possibleSolutionExists->setTranslate(true);
			$possibleSolutionExists->setRowTitle("possible_solutions_exists");
			$this->selectedFilters->add($possibleSolutionExists);

			$impPermCAExists = new filterExistsCombo("impPermCAExists");
			$impPermCAExists->setField("evaluation.correctiveActions");
			$impPermCAExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$impPermCAExists->setTranslate(true);
			$impPermCAExists->setRowTitle("implemented_actions_exists");
			$this->selectedFilters->add($impPermCAExists);

			$caValidationExists = new filterExistsCombo("caValidationExists");
			$caValidationExists->setField("evaluation.correctiveActionsValidation");
			$caValidationExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$caValidationExists->setTranslate(true);
			$caValidationExists->setRowTitle("corrective_actions_validation_exists");
			$this->selectedFilters->add($caValidationExists);

			$preventiveActionsExists = new filterExistsCombo("preventiveActionsExists");
			$preventiveActionsExists->setField("evaluation.preventiveActions");
			$preventiveActionsExists->setArraySource(array(
				array("display" => "yes", "value" => "y"),
				array("display" => "no", "value" => "n")
				));
			$preventiveActionsExists->setTranslate(true);
			$preventiveActionsExists->setRowTitle("PREVENTIVE_ACTIONS_EXISTS");
			$this->selectedFilters->add($preventiveActionsExists);
			
			$financeApproval = new myFilterCombo("financeApproval");
			$financeApproval->setField("complaint.id");
			$financeApproval->setArraySource(array(
				array("display" => translate::getInstance()->translate("Yes"), "value" => $this->getFinanceComplaints() )
				));
			$financeApproval->setRowTitle("credit_approval_in_finance_stage");
			$this->selectedFilters->add($financeApproval);

			// Sort the filter list alphabetically
			$this->selectedFilters->sort();
//		}
	}
	
	private function getFinanceComplaints()
	{
		$financeComplaints = array();
		$sql = "SELECT comp.id 
				FROM complaint comp
				JOIN conclusion conc
				ON comp.id = conc.complaintId 
				JOIN approval app
				ON comp.id = app.complaintId 
				WHERE comp.complaintValueGBP > 5000 AND comp.complaintValueGBP <25000 
				AND app.dateCompleted = '0000-00-00'
				GROUP BY comp.id
				HAVING MAX(app.approvalStage) = 3";
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		while($fields = mysql_fetch_array($dataset) )
		{
			array_push($financeComplaints, $fields['id']);
		}
		$sql = "SELECT comp.id 
				FROM complaint comp
				JOIN conclusion conc
				ON comp.id = conc.complaintId 
				JOIN approval app
				ON comp.id = app.complaintId 
				WHERE comp.complaintValueGBP >= 25000 
				AND app.dateCompleted = '0000-00-00'
				GROUP BY comp.id
				HAVING MAX(app.approvalStage) = 4";
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		while($fields = mysql_fetch_array($dataset) )
		{
			array_push($financeComplaints, $fields['id']);
		}
		
		return implode("||", $financeComplaints);
	}
}

/** CUSTOMER COLUMNS **/
class myColumn extends column
{
	public function getOutput($fields)
	{
		$xml = $fields[ $this->getName() ];
		
		return $xml;
	}
}


class nameColumn extends column
{
	public function getOutput($fields)
	{
		$xml = usercache::getInstance()->get($fields[$this->getName()])->getName();

		return $xml;
	}
}


class dateColumn extends column
{
	public $isDate = true;
	
	public function getExcelOutput($fields)
	{
		if (isset($fields[$this->getName()]) && $fields[$this->getName()]!="0000-00-00 00:00:00")
		{
			$xml = myCalendar::dateForSQL($fields[$this->getName()]);
		}
		else 
		{
			$xml = "";
		}

		return $xml;
	}
	
	public function getOutput($fields)
	{
		if (isset($fields[$this->getName()]) && $fields[$this->getName()]!="0000-00-00 00:00:00")
		{
			$xml = myCalendar::dateForUser($fields[$this->getName()]);
		}
		else 
		{
			$xml = "";
		}

		return $xml;
	}
}

class complaintIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = $fields[$this->getName()];
		
		return $xml;
	}
}
//
//class filterInvoiceDateRange extends filterDateRange 
//{	
//	public function generateSQL()
//	{
//		$sql = "SELECT distinct complaintsCustomer.invoicePopup.complaintId AS cId
//			FROM complaintsCustomer.invoicePopup
//			INNER JOIN SAP.invoices
//			ON complaintsCustomer.invoicePopup.invoiceNo = SAP.invoices.invoiceNo
//			WHERE SAP.invoices.invoiceDate >= '" . common::transformDateForMYSQL($this->form->get($this->name ."MIN")->getValue()) . "' 
//			AND SAP.invoices.invoiceDate <= '" . common::transformDateForMYSQL($this->form->get($this->name ."MAX")->getValue()) . "'";
//
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		
//		$complaintIds = "";
//			
//		while ($fields = mysql_fetch_array($dataset))
//		{
//			$complaintIds .= $fields['cId'] . ",";
//		}		
//		
//		if ($complaintIds != "")
//		{
//			// Trim final comma
//			$complaintIds = substr($complaintIds, 0, -1);				
//		}
//		else 
//		{
//			$complaintIds = "''";
//		}
//		
//		$sql = $this->field . " IN (" . $complaintIds . ")";
//		
//		$sql = $this->field . " >= '" . common::transformDateForMYSQL($this->form->get($this->name ."MIN")->getValue()) . "' AND " . $this->field . " <= '" . common::transformDateForMYSQL($this->form->get($this->name ."MAX")->getValue()) . "'";
//		
//		
//			page::addDebug("$sql", __FILE__, __LINE__);
//		
//		return $sql;
//	}
//}


class categoryColumn extends column
{
	public function getOutput($fields)
	{
		$catId = $fields[$this->getName()];
		
		if( $catId != null && $catId != "" )
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT selectionOption
				FROM selectionOptions
				WHERE id = " . $fields[$this->getName()] . "
				LIMIT 1");

			if(mysql_num_rows($dataset) > 0)
			{
				$fields = mysql_fetch_array($dataset);

				return translate::getInstance()->translate($fields['selectionOption']);
			}
		}

		return "N/A";
	}
}

class selectionOptionTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT selectionOption
			FROM selectionOptions
			WHERE id = '" . $fields[$this->getName()]. "'
			LIMIT 1");

		if(mysql_num_rows($dataset) > 0)
		{
			$fields = mysql_fetch_array($dataset);

			$xml = translate::getInstance()->translate($fields['selectionOption']);
		}
		else
		{
			$xml = "N/A";
		}

		return $xml;
	}
}

class booleanColumn extends column
{
	public $oneValue = "yes";
	public $zeroValue = "no";
	public $otherValue = "N/A";

	public function getOutput($fields)
	{
		if ($fields[$this->getName()] === '1')
		{
			$xml = translate::getInstance()->translate($this->oneValue);
		}
		else if ($fields[$this->getName()] === '0')
		{
			$xml = translate::getInstance()->translate($this->zeroValue);
		}
		else
		{
			$xml = translate::getInstance()->translate($this->otherValue);
		}

		return $xml;
	}
}

class goodsActionColumn extends booleanColumn
{
	public $oneValue = "return_goods";
	public $zeroValue = "dispose_goods";
	public $otherValue = "no_action";
}

class creditDebitColumn extends booleanColumn
{
	public $oneValue = "credit";
	public $zeroValue = "debit";
}

class full8dColumn extends booleanColumn
{
	public $oneValue = "full_8d";
	public $zeroValue = "root_cause_corrective_action";
}

class openClosedColumn extends booleanColumn
{
	public $oneValue = "closed";
	public $zeroValue = "open";
}

class sapCustomerNameColumn extends column
{
	public function getOutput($fields)
	{
		$xml = sapCustomer::getName($fields[$this->getName()]);

		return $xml;
	}
}


class batchNoColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT batch_edit
				FROM complaintsCustomer.invoicePopup
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['batch_edit'] != "")
				{
					$xml .= $data['batch_edit'] . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}



class materialGroupColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT materialGroup
				FROM SAP.invoices
				INNER JOIN complaintsCustomer.invoicePopup
				ON SAP.invoices.id = complaintsCustomer.invoicePopup.invoicesId
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['materialGroup'] != "")
				{
					$xml .= $data['materialGroup'] . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}

class daysOpenColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT CASE
					WHEN (
						complaint.closureDate IS NOT NULL
					)
					THEN (
						datediff(complaint.closureDate, complaint.submissionDate) 
					)
					ELSE (
						datediff('" . date("Y-m-d") . "', complaint.submissionDate) 
					)
				END AS dateDifference 
				FROM complaintsCustomer.complaint 
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		while ($data = mysql_fetch_array( $dataset ))
		{
			$xml = $data['dateDifference'];
		}
		
		return $xml;
	}
}


class materialNoColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT material
				FROM SAP.invoices
				INNER JOIN complaintsCustomer.invoicePopup
				ON SAP.invoices.id = complaintsCustomer.invoicePopup.invoicesId
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['material'] != "")
				{
					$xml .= $data['material'] . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}


class invoiceDateColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT invoiceDate
				FROM SAP.invoices
				INNER JOIN complaintsCustomer.invoicePopup
				ON SAP.invoices.id = complaintsCustomer.invoicePopup.invoicesId
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['invoiceDate'] != "")
				{
					$xml .= common::transformDateForPHP($data['invoiceDate']) . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}

class invoiceNoColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT invoiceNo
				FROM complaintsCustomer.invoicePopup
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['invoiceNo'] != "")
				{
					$xml .= common::transformDateForPHP($data['invoiceNo']) . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}


class sapItemNoColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT invoicesId
				FROM complaintsCustomer.invoicePopup
				INNER JOIN complaintsCustomer.complaint 
				ON complaintsCustomer.invoicePopup.complaintId = complaintsCustomer.complaint.id
				WHERE complaintsCustomer.complaint.id = " . $fields[ $this->getName() ];

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		$xml = "";
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			while ($data = mysql_fetch_array( $dataset ))
			{
				if ($data['invoicesId'] != "")
				{
					$xml .= common::transformDateForPHP($data['invoicesId']) . ", ";
				}
			}
		}			

		if ($xml != "")
		{
			$xml = substr($xml, 0, -2);
		}

		return $xml;
	}
}


class businessUnitColumn extends column
{
	public function getOutput($fields)
	{
		$sql = "SELECT newMrkt
				FROM businessUnits
				INNER JOIN customers
				ON businessUnits.seg = customers.customerGroup
				WHERE customers.id LIKE('" . $fields[$this->getName()] . "')";

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);

		if( mysql_num_rows( $dataset ) > 0 )
		{
			$buData = mysql_fetch_array( $dataset );
			
			$xml = $buData['newMrkt'];

			return $xml;
		}

		$xml = "N/A";

		return $xml;
	}
}


class invoiceDateCombo extends combo
{
	private $field;

	public $selectSize;

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		$this->setSelectSize($this->selectSize);

		$this->setRowType("filter");
		$this->setVisible(false);
	}

	public function setField($field)
	{
		$this->field = $field;
	}

	public function setSelectSize($selectSize)
	{
		$this->selectSize = $selectSize;
	}

	public function generateSQL()
	{
		$sql = "";

		$value = $this->getValue();

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
			SELECT invoiceDate
			FROM invoices
			INNER JOIN businessUnits
			ON businessUnits.seg = customers.customerGroup
			WHERE businessUnits.newMrkt = '" . $value . "'");

		$customerNos = '';

		while ($fields = mysql_fetch_assoc($dataset))
		{
			$customerNos .= $fields['id'] . ',';
		}

		$customerNos = substr($customerNos, 0, -1);

		if (strlen($value) > 0)
		{
			$exploded = explode("||", $value);
			$sql = $this->field . " IN (" . $customerNos . ")";
			page::addDebug("$sql", __FILE__, __LINE__);
		}

		return $sql;
	}
}


class businessUnitCombo extends combo
{
	private $field;

	public $selectSize;

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		$this->setSelectSize($this->selectSize);

		$this->setRowType("filter");
		$this->setVisible(false);
	}

	public function setField($field)
	{
		$this->field = $field;
	}

	public function setSelectSize($selectSize)
	{
		$this->selectSize = $selectSize;
	}

	public function generateSQL()
	{
		$sql = "";

		$value = $this->getValue();

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
			SELECT customers.id
			FROM customers
			INNER JOIN businessUnits
			ON businessUnits.seg = customers.customerGroup
			WHERE businessUnits.newMrkt = '" . $value . "'");

		$customerNos = '';

		while ($fields = mysql_fetch_assoc($dataset))
		{
			$customerNos .= $fields['id'] . ',';
		}

		$customerNos = substr($customerNos, 0, -1);

		if (strlen($value) > 0)
		{
			$exploded = explode("||", $value);
			$sql = $this->field . " IN (" . $customerNos . ")";
			page::addDebug("$sql", __FILE__, __LINE__);
		}

		return $sql;
	}
}

class salesOfficeCombo extends combo
{
	private $field;

	public $selectSize;

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		$this->setSelectSize($this->selectSize);

		$this->setRowType("filter");
		$this->setVisible(false);
	}

	public function setField($field)
	{
		$this->field = $field;
	}

	public function setSelectSize($selectSize)
	{
		$this->selectSize = $selectSize;
	}

	public function generateSQL()
	{
		$sql = "";

		$value = $this->getValue();

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("
			SELECT customers.id
			FROM customers
			INNER JOIN salesEmployees
			ON customers.salesEmp = salesEmployees.id
			INNER JOIN membership.employee
			ON salesEmployees.NTLogon = membership.employee.NTLogon
			WHERE membership.employee.site = '" . $value . "'");

		$customerNos = '';

		while ($fields = mysql_fetch_assoc($dataset))
		{
			$customerNos .= $fields['id'] . ',';
		}

		$customerNos = substr($customerNos, 0, -1);

		if (strlen($value) > 0)
		{
			$exploded = explode("||", $value);
			$sql = $this->field . " IN (" . $customerNos . ")";
			page::addDebug("$sql", __FILE__, __LINE__);
		}

		return $sql;
	}
}

class salesOfficeColumn extends column
{
	public function getOutput($fields)
	{
		$initiator = complaintLib::getInitiator( $fields['id'] );

		$sql = "SELECT *
				FROM employee
				WHERE NTLogon = '$initiator'";

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);

		if( mysql_num_rows( $dataset ) > 0 )
		{
			$employeeData = mysql_fetch_array( $dataset );

			$xml = $employeeData['site'];			
		}
		
		return $xml;
	}
}

class calcFromOpenDateColumn extends dateDiffColumn
{
	public $column1 = "complaintDate";
	public $column2 = "submissionDate";
}

class creditApproversColumn extends column 
{
	public function getData($fields)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT *
			FROM approval 
			WHERE complaintId = " . $fields[$this->getName()]
			);

		$approvers = array();
		
		for( $i=0; $i<mysql_num_rows( $dataset ); $i++)
		{
			$fields = mysql_fetch_array($dataset);
			
			if( $fields['completedBy'] != "" )
			{
				$approvers[$i] = translate::getInstance()->translate("approval_stage") . " " . $fields['approvalStage'] . ": " . usercache::getInstance()->get($fields['completedBy'])->getName();
			}
		}
		
		return $approvers;
	}
	
	public function getOutput($fields)
	{
		return "<stage>" . implode("</stage><stage>", $this->getData($fields)) . "</stage>";
	}
	
	public function getExcelOutput($fields)
	{
		return implode(" , ", $this->getData($fields));
	}
}



class dateDiffColumn extends column
{
	public $column1 = "";
	public $column2 = "";

	public $tables = "complaintTables";

	public $complaintTables = " FROM complaint ";

	public $evaluationTables = " FROM complaint
		INNER JOIN evaluation
		ON complaint.id = evaluation.complaintId ";

	public $conclusionTables = " FROM complaint
		INNER JOIN conclusion
		ON complaint.id = conclusion.complaintId ";

	public function getOutput($fields)
	{
		$datasetDates = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT " . $this->column1 . ", " . $this->column2 .
			$this->{$this->tables} . "
			WHERE complaint.id = " . $fields[$this->getName()]. "
			LIMIT 1");

		$fieldsDates = mysql_fetch_array($datasetDates);
		
		$searchField = $this->column1;
		if( strrpos($searchField, ".") )
		{
			$searchField = substr( $searchField, strrpos($searchField, ".") +1 );
		}

		if ($fieldsDates[$searchField] != null && $fieldsDates[$this->column2] != null)
		{
			$column1 = explode("-", $fieldsDates[$searchField]);
			$column2 = explode("-", $fieldsDates[$this->column2]);

			$column1 = mktime(0,0,0,$column1[1], $column1[2], $column1[0]) / 86400;
			$column2 = mktime(0,0,0,$column2[1], $column2[2], $column2[0]) / 86400;

			$daysTaken = round(($column2 - $column1), 1);
			
			$xml = $daysTaken;
		}
		else

		{
			$xml = "N/A";
		}

		return $xml;
	}
}

class rootCauseDateColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "rootCauseDate";

	public $tables = "evaluationTables";
}

class analysisDateColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "analysisDate";

	public $tables = "evaluationTables";
}

class correctiveActionsValidationColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "correctiveActionsValidationDate";

	public $tables = "evaluationTables";
}

class possibleSolutionsColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "possibleSolutionsDate";

	public $tables = "evaluationTables";
}

class preventiveActionsColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "preventiveActionsDate";

	public $tables = "evaluationTables";
}

class correctiveActionsImpCompletionColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "correctiveActionsImpDate";

	public $tables = "evaluationTables";
}

class creditAuthorisationColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "creditAuthorisationDate";

	public $tables = "conclusionTables";
}

class correctiveActionsCompletionColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "correctiveActionDate";

	public $tables = "evaluationTables";
}

class validationVerificationCompletionColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "validationVerificationDate";

	public $tables = "evaluationTables";
}

class containmentActionsDateColumn extends dateDiffColumn
{
	public $column1 = "complaint.submissionDate";
	public $column2 = "containmentActionsDate";

	public $tables = "evaluationTables";
}

class closeOutPerformanceColumn extends dateDiffColumn
{
	public $column1 = "submissionDate";
	public $column2 = "closureDate";
}

class selectionOptionColumn extends column
{
	public function getOutput($fields)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT selectionOption
			FROM selectionOptions
			WHERE id = '" . $fields[$this->getName()]. "'
			LIMIT 1");

		if (mysql_num_rows($dataset) > 0)
		{
			$fields = mysql_fetch_array($dataset);

			$xml = $fields['selectionOption'];
		}
		else
		{
			$xml = "N/A";
		}

		return $xml;
	}
}

?>