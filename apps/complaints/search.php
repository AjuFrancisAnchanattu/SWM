<?php

require 'lib/complaint.php';

class search extends page
{
	private $chooseReportForm;
	private $addFiltersForm;
	private $selectedFilters;

	function __construct()
	{
		/*
		echo "<pre>";
		print_r($_POST);
		echo "</pre>";
		exit;
		*/
		parent::__construct();
		$this->setPrintCss("/css/ccr.css");
		$this->setActivityLocation('Complaints');
		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");


		$this->defineChooseReportForm(); // Show [chooseReportForm] array
		$this->chooseReportForm->loadSessionData();

		$this->chooseReportForm->processPost();
		$this->chooseReportForm->validate(); // Validate Choose Report [chooseReportForm][valid]
		$this->defineSelectedFilters(); // FILTERS ARRAY [filters] array

		$this->selectedFilters->form->loadSessionData();

		$this->defineAddFiltersForm();
		$this->selectedFilters->processPost();
		$this->selectedFilters->form->putValuesInSession(); // FILTERS ARRAY [filters][default]

		/* WC - AE 17/01/08
		added to be able to select which columns are displayed on frontend report
		*/
		if(isset($_POST['columns'])){
			$_SESSION["searchColumns"] = $_POST['columns'];
			$_SESSION["reportType"] = $_POST['reportType'];
		}
		elseif(isset($_POST['columnsSupplier'])){
			$_SESSION["searchColumns"] = $_POST['columnsSupplier'];
			$_SESSION["reportType"] = $_POST['reportType'];
		}
		elseif(isset($_POST['columnsQuality'])){
			$_SESSION["searchColumns"] = $_POST['columnsQuality'];
			$_SESSION["reportType"] = $_POST['reportType'];
		}
		else
		{
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
		//exit;
		/* WC END */

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view')
		{
			//echo "<pre>";
			//print_r($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]);
			//echo "</pre>";

			/* WC - AE: commented block out as file doesnt exist */
			if (!isset($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]))
			$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array();
			/*
			if (!isset($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]))
			{
			//$this->redirect("searchResults?");
			}
			*/
			/* WC END */
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]); $i++)
			{
				$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][$i])->setVisible(true);
			}

			// SAVE BOOKMARK!
			if (isset($_REQUEST['save']) && $_REQUEST['save'] == 'true')
			{
				$blah = mysql_escape_string(serialize($this->selectedFilters));
				/* WC - AE. 18/01/08
				serialise session data */
				//if($_SESSION["searchColumns"])
				$whatColumns = mysql_escape_string(serialize($_SESSION["searchColumns"]));
				//else $whatColumns = '';

				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO bookmarksParent (name, owner) VALUES ('" . page::nowDateForMysql() . "', '" . currentuser::getInstance()->getNTLogon() . "')");

				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM bookmarksParent ORDER BY `id` DESC LIMIT 1");
				$fields = mysql_fetch_array($dataset);

				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `bookmarksParent` SET `bookmarkParentId` = '" . $fields['id'] . "' WHERE id = " . $fields['id'] . "");

				$datasetNext = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM bookmarksParent ORDER BY `id` DESC LIMIT 1");
				$fieldsNext = mysql_fetch_array($datasetNext);
				/* WC - AE. 18/01/08
				added serialise of session columns and store in db
				*/
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO bookmarks (bookmarkId, filters, reportType, `columns`) VALUES (" . $fieldsNext['id'] . ", '" . $blah . "', '" . $this->chooseReportForm->get("reportType")->getValue() . "', '".$whatColumns."')");

				$this->redirect("editBookmark?mode=edit&bookmarkId=" . $fieldsNext['bookmarkParentId'] . "&bookmarkMainId=" . $fields['id'] . ""); // redirect to update bookmark name

			}
			// END SAVE BOOKMARK
			if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'excel')
			{
				if(isset($_SESSION["reportType"]))
				//$this->chooseReportForm->get("reportType")->setValue($_SESSION["reportType"]);
				$this->chooseReportForm->get("reportType")->getValue();
				$this->showExcelResults();
			}

			else
			{
				//if(isset($_SESSION["reportType"]))
				//$this->chooseReportForm->get("reportType")->setValue($_SESSION["reportType"]);
				$this->showResults();
			}
		}
		else
		{

			$this->add_output("<complaintsSearch>");

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
				$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array(); // IF NOT SET START [selectedFilters] array
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

			if (isset($_REQUEST['action']) && strstr($_REQUEST['action'], 'removeAllFilters'))
			{
				$_SESSION['apps'][$GLOBALS['app']]["selectedFilters"] = array();
			}


			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"]); $i++)
			{
				$this->selectedFilters->get($_SESSION['apps'][$GLOBALS['app']]["selectedFilters"][$i])->setVisible(true);
			}


			// edit
			$snapins = new snapinGroup('complaints_search');
			//			$snapins->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaints add snapin in the page
			$snapins->register('apps/complaints', 'addComplaint', true, true);		//puts the Complaints add snapin in the page
			$snapins->register('apps/complaints', 'loadComplaint', true, true);
			$snapins->register('apps/complaints', 'yourComplaints', true, true);
			$snapins->register('apps/complaints', 'bookmarkedComplaints', true, true);
			//$snapins->register('apps/complaints', 'bookmarkedComplaints', true, true);
			$snapins->register('apps/complaints', 'refDocuments', true, true);		//puts the complaints ref docs snapin in the page


			$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");


			$this->add_output("<chooseReport>");
			$this->add_output($this->chooseReportForm->output());
			$this->add_output("</chooseReport>");

			$this->add_output("<addFilters>");
			$this->add_output($this->addFiltersForm->output());
			$this->add_output("</addFilters>");

			/* WC - AE 17/01/08
			added extra xml to accomodate the column filters multi select
			these values are stored / retrieved from session
			*/
			if($this->chooseReportForm->get("reportType")->getValue() == "custom"){
				$this->add_output("<columnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"])){
					foreach($_SESSION["searchColumns"] as $val)
					$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</columnFilters>");
			}

			if($this->chooseReportForm->get("reportType")->getValue() == "customSupplier"){
				$this->add_output("<supplierColumnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"])){
					foreach($_SESSION["searchColumns"] as $val)
					$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</supplierColumnFilters>");
			}

			if($this->chooseReportForm->get("reportType")->getValue() == "customQuality"){
				$this->add_output("<qualityColumnFilters>");
				if(isset($_SESSION["searchColumns"]) && is_array($_SESSION["searchColumns"])){
					foreach($_SESSION["searchColumns"] as $val)
					$this->add_output("<".$val.">1</".$val.">");
				}
				$this->add_output("</qualityColumnFilters>");
			}
			/* WC END */

			$this->add_output("<selectedFilters>");
			$this->add_output($this->selectedFilters->getOutput());
			$this->add_output("</selectedFilters>");

			$this->add_output("</complaintsSearch>");


			// if post

			// save, redirect to view
		}
		//echo $this->output;
		$this->output('./apps/complaints/xsl/search.xsl');
	}



	// Show Excel Results
	/* WC - AE 18/01/08
	This whole section has had results filtered into columns
	*/
	private function showExcelResults()
	{
		$results = new excelResults();

		$results->setSelectedFilters($this->selectedFilters);
		$results->setDatabase("complaints");

		$results->setBaseQuery("SELECT * FROM complaint");
		$results->setOrderBy("id");

		$dummy = new complaint();

		switch($this->chooseReportForm->get("reportType")->getValue())//here is the prob in switch
		{
			case 'custom':
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId LEFT JOIN materialGroup ON conclusion.complaintId=materialGroup.complaintId");
				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");

				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				if(in_array("ComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));
				if(in_array("ComplaintLocation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`complaintLocation`", "complaintLocation", "complaint_location", true));
				if(in_array("InternalSalesName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internalSalesName`", "internalSalesName", "complaint_creator", true));
				if(in_array("ExternalSalesName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`externalSalesName`", "externalSalesName", "external_sales_name", true));
				if(in_array("ProcessOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "process_owner", true));
				if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				if(in_array("DespatchSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "despatch_site", true));
				if(in_array("ManufacturingSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));
				if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				if(in_array("ComplaintType", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));
				if(in_array("complaint_justified", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));
				if(in_array("SAPCustomerNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));
				if(in_array("SAPCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));
				//				if(in_array("full_8d_required", $this->selectedColumns) || $this->showAllCols)
				//					$results->addColumn(new column("complaint.`full8dRequired`", "full8dRequired", "full_8d_required", true));
				if(in_array("g8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`g8d`", "g8d", "g8d", true));
				if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				if(in_array("sapItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));
				if(in_array("MaterialGroup", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));
				if(in_array("interco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`interco`", "interco", "interco", true));
				if(in_array("product_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productDescription`", "productDescription", "product_description", true));
				if(in_array("awaiting_dimensions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`awaitingDimensions`", "awaitingDimensions", "awaiting_dimensions", true));
				if(in_array("dimension_thickness_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionThickness_quantity`", "dimensionThickness_quantity", "dimension_thickness_quantity", true));
				if(in_array("dimension_thickness_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionThickness_measurement`", "dimensionThickness_measurement", "dimension_thickness_measurement", true));
				if(in_array("dimension_width_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionWidth_quantity`", "dimensionWidth_quantity", "dimension_width_quantity", true));
				if(in_array("dimension_width_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionWidth_measurement`", "dimensionWidth_measurement", "dimension_width_measurement", true));
				if(in_array("dimension_length_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionLength_quantity`", "dimensionLength_quantity", "dimension_length_quantity", true));
				if(in_array("dimension_length_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`dimensionLength_measurement`", "dimensionLength_measurement", "dimension_length_measurement", true));
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`colour`", "colour", "colour", true));
				if(in_array("factored_product", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`factoredProduct`", "factoredProduct", "factored_product", true));
				if(in_array("product_supplier_name", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productSupplierName`", "productSupplierName", "product_supplier_name", true));
				if(in_array("customer_item_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`customerItemNumber`", "customerItemNumber", "customer_item_number", true));
				if(in_array("quantity_under_complaint_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`quantityUnderComplaint_quantity`", "quantityUnderComplaint_quantity", "quantity_under_complaint_quantity", true));
				if(in_array("quantity_under_complaint_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`quantityUnderComplaint_measurement`", "quantityUnderComplaint_measurement", "quantity_under_complaint_measurement", true));
				if(in_array("complaint_value_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`complaintValue_quantity`", "complaintValue_quantity", "complaint_value_quantity", true));
				if(in_array("complaint_value_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`complaintValue_measurement`", "complaintValue_measurement", "complaint_value_measurement", true));
				//if(in_array("currency", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`currency`", "currency", "currency", true));
				if(in_array("awaiting_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`awaitingBatchNumber`", "awaitingBatchNumber", "awaiting_batch_number", true));
				if(in_array("batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`batchNumber`", "batchNumber", "batch_number", true));
				if(in_array("supplier_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`supplierBatchNumber`", "batchNumber", "scapa_batch_number", true));
				//if(in_array("DispatchSite", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "despatch_site", true));
				//if(in_array("ManufacturingSite", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "site_at_origin", true));
				if(in_array("carrier_name", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`carrierName`", "carrierName", "carrier_name", true));
				if(in_array("credit_note_requested", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`creditNoteRequested`", "creditNoteRequested", "credit_note_requested", true));
				if(in_array("problem_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`problemDescription`", "problemDescription", "problem_description", true));
				if(in_array("severity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`severity`", "severity", "severity", true));
				if(in_array("line_stoppage", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lineStoppage`", "lineStoppage", "line_stoppage", true));
				if(in_array("line_stoppage_details", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lineStoppageDetails`", "lineStoppageDetails", "line_stoppage_details", true));
				//if(in_array("automotive_covisint", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new complaintDateColumn("complaint.`automotiveCovisint`", "automotiveCovisint", "automotive_covisint", true));
				//if(in_array("covisint_ref", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new column("complaint.`covisintRef`", "covisintRef", "covisint_ref", true));
				//if(in_array("covisint_date", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`covisintDate`", "covisintDate", "covisint_date", true));
				if(in_array("sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sampleReceived`", "sampleReceived", "sample_received", true));
				if(in_array("sample_reception_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleReceptionDate`", "sampleReceptionDate", "sample_reception_date", true));
				if(in_array("sample_transferred", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sampleTransferred`", "sampleTransferred", "sample_transferred", true));
				if(in_array("sample_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleDate`", "sampleDate", "sample_date", true));
				if(in_array("sales_containment_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesContainmentActions`", "salesContainmentActions", "sales_containment_actions", true));
				if(in_array("action_requested", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`actionRequested`", "actionRequested", "action_requested", true));
				if(in_array("awaiting_invoice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`awaitingInvoice`", "awaitingInvoice", "awaiting_invoice", true));
				if(in_array("awaiting_quantity_under_complaint", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`awaitingQuantityUnderComplaint`", "awaitingQuantityUnderComplaint", "awaiting_quantity_under_complaint", true));
				if(in_array("closed_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`closedDate`", "closedDate", "closed_date", true));
				if(in_array("total_closure_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`totalClosureDate`", "totalClosureDate", "total_closure_date", true));
				//$results->addColumn(new complaintDateColumn("complaint.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("is_po_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isPORight`", "isPORight", "is_po_right", true));
				if(in_array("reason_for_rejection", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`reasonForRejection`", "reasonForRejection", "reason_for_rejection", true));
				if(in_array("is_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isSampleReceived`", "isSampleReceived", "is_sample_received", true));
				if(in_array("date_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`dateSampleReceived`", "dateSampleReceived", "date_sample_received", true));
				if(in_array("team_leader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamLeader`", "teamLeader", "team_leader", true));
				if(in_array("team_member", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamMember`", "teamMember", "team_member", true));
				if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysis`", "analysis", "analysis", true));
				if(in_array("author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`author`", "author", "author", true));
				if(in_array("analysis_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`analysisDate`", "analysisDate", "analysis_date", true));
				if(in_array("is_complaint_cat_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isComplaintCatRight`", "isComplaintCatRight", "is_complaint_cat_right", true));
				if(in_array("correct_category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`correctCategory`", "correctCategory", "correct_category", true));
				if(in_array("root_causes", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauses`", "rootCauses", "root_causes", true));
				if(in_array("failure_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`failureCode`", "failureCode", "failure_code", true));
				if(in_array("root_cause_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauseCode`", "rootCauseCode", "root_cause_code", true));
				if(in_array("attributable_process", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`attributableProcess`", "attributableProcess", "attributable_process", true));
				if(in_array("root_causes_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesAuthor`", "rootCausesAuthor", "root_causes_author", true));
				if(in_array("root_causes_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`rootCausesDate`", "rootCausesDate", "root_causes_date", true));
				if(in_array("is_complaint_cat_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isComplaintCatRight`", "isComplaintCatRight", "is_complaint_cat_right", true));
				if(in_array("correct_category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`correctCategory`", "correctCategory", "correct_category", true));
				if(in_array("complaint_justified", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));
				if(in_array("return_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnGoods`", "returnGoods", "return_goods", true));
				if(in_array("dispose_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`disposeGoods`", "disposeGoods", "dispose_goods", true));
				//if(in_array("similar_recall", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new column("evaluation.`similarRecall`", "similarRecall", "similar_recall", true));
				//if(in_array("date_of_intermediate", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new column("evaluation.`dateOfIntermediate`", "dateOfIntermediate", "date_of_intermediate", true));
				//if(in_array("stock_verif_made", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new column("evaluation.`stockVerifMade`", "stockVerifMade", "stock_verif_made", true));
				if(in_array("containment_action", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "containment_action", true));
				if(in_array("containment_action_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionAuthor`", "containmentActionAuthor", "containment_action_author", true));
				if(in_array("containment_action_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`containmentActionDate`", "containmentActionDate", "containment_action_date", true));
				if(in_array("possible_solutions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutions`", "possibleSolutions", "possible_solutions", true));
				if(in_array("possible_solutions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsAuthor`", "possibleSolutionsAuthor", "possible_solutions_author", true));
				if(in_array("possible_solutions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`possibleSolutionsDate`", "possibleSolutionsDate", "possible_solutions_date", true));
				if(in_array("implemented_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActions`", "implementedActions", "implemented_actions", true));
				if(in_array("implemented_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsAuthor`", "implementedActionsAuthor", "implemented_actions_author", true));
				if(in_array("implemented_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("implemented_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				//if(in_array("implemented_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				if(in_array("implemented_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsImplementation`", "implementedActionsImplementation", "implemented_actions_implementation", true));
				if(in_array("implemented_actions_effectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsEffectiveness`", "implementedActionsEffectiveness", "implemented_actions_effectiveness", true));
				if(in_array("preventive_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActions`", "preventiveActions", "preventive_actions", true));
				if(in_array("preventive_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsAuthor`", "preventiveActionsAuthor", "preventive_actions_author", true));
				if(in_array("preventive_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsDate`", "preventiveActionsDate", "preventive_actions_date", true));
				if(in_array("preventive_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEstimated`", "preventiveActionsEstimated", "preventive_actions_estimated", true));
				if(in_array("preventive_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsImplementation`", "preventiveActionsImplementation", "preventive_actions_implementation", true));
				if(in_array("preventive_actions_effectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEffectiveness`", "preventiveActionsEffectiveness", "preventive_actions_effectiveness", true));
				if(in_array("management_system_reviewed", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewed`", "managementSystemReviewed", "management_system_reviewed", true));
				if(in_array("management_system_reviewed_ref", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewedRef`", "managementSystemReviewedRef", "management_system_reviewed_ref", true));
				if(in_array("management_system_reviewed_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`managementSystemReviewedDate`", "managementSystemReviewedDate", "management_system_reviewed_date", true));
				if(in_array("inspectionInstructions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructions`", "inspectionInstructions", "inspectionInstructions", true));
				if(in_array("inspectionInstructionsRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructionsRef`", "inspectionInstructionsRef", "inspectionInstructionsRef", true));
				if(in_array("inspectionInstructionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`inspectionInstructionsDate`", "inspectionInstructionsDate", "inspectionInstructionsDate", true));
				if(in_array("fmea", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmea`", "fmea", "fmea", true));
				if(in_array("fmeaRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmeaRef`", "fmeaRef", "fmea_ref", true));
				if(in_array("fmeaDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`fmeaDate`", "fmeaDate", "fmea_date", true));
				if(in_array("customerSpecification", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecification`", "customerSpecification", "customer_specification", true));
				if(in_array("customerSpecificationRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecificationRef`", "customerSpecificationRef", "customer_specification_ref", true));
				if(in_array("customerSpecificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`customerSpecificationDate`", "customerSpecificationDate", "customer_specification_date", true));
				if(in_array("comments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`comments`", "comments", "comments", true));
				//if(in_array("sapReturnNumber", $this->selectedColumns) || $this->showAllCols)
				//	$results->addColumn(new column("conclusion.`sapReturnNumber`", "sapReturnNumber", "sap_return_number", true));
				if(in_array("returnFormDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`returnFormDate`", "returnFormDate", "return_form_date", true));
				if(in_array("returnQuantityReceived_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`returnQuantityReceived_quantity`", "returnQuantityReceived_quantity", "return_quantity_received_quantity", true));
				if(in_array("returnQuantityReceived_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`returnQuantityReceived_measurement`", "returnQuantityReceived_measurement", "return_quantity_received_measurement", true));
				if(in_array("dateReturnsReceived", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`dateReturnsReceived`", "dateReturnsReceived", "date_returns_received", true));
				if(in_array("receiver", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`receiver`", "receiver", "receiver", true));
				if(in_array("disposalNoteDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`disposalNoteDate`", "disposalNoteDate", "disposal_note_date", true));
				if(in_array("modComplaintOption", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`modComplaintOption`", "modComplaintOption", "mod_complaint_option", true));
				if(in_array("modComplaintCategory", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`modComplaintCategory`", "modComplaintCategory", "mod_complaint_category", true));
				if(in_array("modComplaintReason", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`modComplaintReason`", "modComplaintReason", "mod_complaint_reason", true));
				if(in_array("defectiveMaterialAmount_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`defectiveMaterialAmount_quantity`", "defectiveMaterialAmount_quantity", "defective_material_amount_quantity", true));
				if(in_array("defectiveMaterialAmount_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`defectiveMaterialAmount_measurement`", "defectiveMaterialAmount_measurement", "defective_material_amount_measurement", true));
				if(in_array("creditNoteValue_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditNoteValue_quantity`", "creditNoteValue_quantity", "creditNoteValue_quantity", true));
				if(in_array("creditNoteValue_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditNoteValue_measurement`", "creditNoteValue_measurement", "creditNoteValue_measurement", true));
				if(in_array("dateCreditNoteRaised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`dateCreditNoteRaised`", "dateCreditNoteRaised", "date_credit_note_raised", true));
				if(in_array("creditNoteGBP_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditNoteGBP_quantity`", "creditNoteGBP_quantity", "credit_note_gbp_quantity", true));
				if(in_array("creditNoteGBP_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditNoteGBP_measurement`", "creditNoteGBP_measurement", "credit_note_gbp_measurement", true));
				if(in_array("requestForCredit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`requestForCredit`", "requestForCredit", "request_for_credit", true));
				if(in_array("transferOwnership", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`transferOwnership`", "transferOwnership", "send_credit_request_to", true));
				if(in_array("ccCommercialCredit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`ccCommercialCredit`", "ccCommercialCredit", "cc_commercial_credit", true));
				if(in_array("ccCommercialCreditComment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`ccCommercialCreditComment`", "ccCommercialCreditComment", "cc_commercial_credit_comment", true));
				if(in_array("commercialLevelCreditAuthorisedAdvise", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.commercialLevelCreditAuthorisedAdvise", "commercialLevelCreditAuthorisedAdvise", "commercial_level_credit_authorised_advise", true));
				if(in_array("commercialCreditAuthoriserAdvise", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.commercialCreditAuthoriserAdvise", "commercialCreditAuthoriserAdvise", "commercial_credit_authoriser_advise", true));
				if(in_array("commercialCreditNewCommercialOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.commercialCreditNewCommercialOwner", "commercialCreditNewCommercialOwner", "commercial_credit_request_sent_to", true));
				if(in_array("commercialReasonAdvise", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.commercialReasonAdvise", "commercialReasonAdvise", "commercial_advise_authorisation_reason", true));
				if(in_array("commercialLevelCreditAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`commercialLevelCreditAuthorised`", "commercialLevelCreditAuthorised", "commercial_level_credit_authorised", true));
				if(in_array("commercialCreditAuthoriser", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`commercialCreditAuthoriser`", "commercialCreditAuthoriser", "commercial_credit_authoriser", true));
				if(in_array("commercialCreditNewFinanceOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.commercialCreditNewFinanceOwner", "commercialCreditNewFinanceOwner", "finance_credit_request_sent_to", true));
				if(in_array("commercialReason", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`commercialReason`", "commercialReason", "commercial_authorisation_reason", true));
				if(in_array("financeLevelCreditAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeLevelCreditAuthorised`", "financeLevelCreditAuthorised", "finance_level_credit_authorised", true));
				if(in_array("financeCreditAuthoriser", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeCreditAuthoriser`", "financeCreditAuthoriser", "finance_credit_authoriser", true));
				if(in_array("financeCreditNewComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeCreditNewComplaintOwner`", "financeCreditNewComplaintOwner", "final_credit_result_sent_to", true));
				if(in_array("financeReason", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeReason`", "financeReason", "finance_authorisation_reason", true));
				if(in_array("financeStageCompleted", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.financeStageCompleted", "financeStageCompleted", "finance_stage_completed", true));
				if(in_array("requestForCreditRaised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`requestForCreditRaised`", "requestForCreditRaised", "request_for_credit_raised", true));
				if(in_array("creditNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditNumber`", "creditNumber", "credit_number", true));
				if(in_array("amount_quantity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`amount_quantity`", "amount_quantity", "amount_quantity", true));
				if(in_array("amount_measurement", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`amount_measurement`", "amount_measurement", "amount_measurement", true));
				if(in_array("customerCreditNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`customerCreditNumber`", "customerCreditNumber", "customer_credit_number", true));
				if(in_array("dateCreditNoteRaised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.dateCreditNoteRaised", "dateCreditNoteRaised", "date_credit_note_raised", true));
				if(in_array("finalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`finalComments`", "finalComments", "finalComments", true));
				if(in_array("InternalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("CustomerComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "customerComplaintStatus", "customer_complaint_status", true));

				if(in_array("implementedPermanentCorrectiveActionValidated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.implementedPermanentCorrectiveActionValidated", "implemented_permanent_corrective_action_validated", "implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`implementedPermanentCorrectiveActionValidatedyn`", "was_implemented_permanent_corrective_action_validated", "was_implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedPermanentCorrectiveActionValidatedDate`", "the_implemented_permanent_corrective_action_date", "the_implemented_permanent_corrective_action_date", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedPermanentCorrectiveActionValidatedAuthor`", "the_implemented_permanent_corrective_action_validated_author", "the_implemented_permanent_corrective_action_validated_author", true));

				// NA filters

				if(in_array("NAccCommercialCredit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`ccCommercialCredit`", "na_cc_commercial_credit", "na_cc_commercial_credit", true));
				if(in_array("NAcreditAuthorisationStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`creditAuthorisationStatus`", "na_credit_authorisation_status", "na_credit_authorisation_status", true));
				if(in_array("NAfinanceCreditAuthoriser", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("conclusion.`financeCreditAuthoriser`", "na_finance_credit_authoriser", "na_finance_credit_authoriser", true));
				if(in_array("NAfinanceCreditNewComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("conclusion.`financeCreditNewComplaintOwner`", "na_finance_credit_new_complaint_owner", "na_finance_credit_new_complaint_owner", true));
				if(in_array("NAfinanceLevelCreditAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeLevelCreditAuthorised`", "na_finance_level_credit_authorised", "na_finance_level_credit_authorised", true));
				if(in_array("NAfinanceStageCompleted", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`financeStageCompleted`", "na_finance_stage_competed", "na_finance_stage_competed", true));
				if(in_array("NArequestForCredit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`requestForCredit`", "na_request_for_credit", "na_request_for_credit", true));
				if(in_array("NAreturnApprovalDisposalName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`returnApprovalDisposalName`", "na_return_approval_disposal_name", "na_return_approval_disposal_name", true));
				if(in_array("NAreturnApprovalDisposalRequest", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnApprovalDisposalRequest`", "na_return_approval_disposal_request", "na_return_approval_disposal_request", true));
				if(in_array("NAreturnApprovalDisposalRequestStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnApprovalDisposalRequestStatus`", "na_return_approval_disposal_request_status", "na_return_approval_disposal_request_status", true));
				if(in_array("NAreturnApprovalDisposalValue", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`returnApprovalDisposalValue_quantity`", "na_return_approval_disposal_value", "na_return_approval_disposal_value", true));
					$results->addColumn(new column("evaluation.`returnApprovalDisposalValue_measurement`", "na_return_approval_disposal_measurement", "na_return_approval_disposal_measurement", true));
				}
				if(in_array("NAreturnApprovalRequest", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnApprovalRequest`", "na_return_approval_request", "na_return_approval_request", true));
				if(in_array("NAreturnApprovalRequestName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`returnApprovalRequestName`", "na_return_approval_request_name", "na_return_approval_request_name", true));

				if(in_array("NAreturnRequestValue", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`returnRequestValue_quantity`", "na_return_request_value", "na_return_request_value", true));
					$results->addColumn(new column("evaluation.`returnRequestValue_measurement`", "na_return_request_measurement", "na_return_request_measurement", true));
				}
				if(in_array("NAreturnRequestName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`returnRequestName`", "na_return_request_name", "na_return_request_name", true));
				if(in_array("NAreturnDisposalRequestName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`returnDisposalRequestName`", "na_return_disposal_request_name", "na_return_disposal_request_name", true));

				if(in_array("naLotNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`naLotNumber`", "na_lot_number", "na_lot_number", true));
				if(in_array("naSizeReturned", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`naSizeReturned_quantity`", "na_size_returned_quantity", "na_size_returned_quantity", true));
					$results->addColumn(new column("conclusion.`naSizeReturned_measurement`", "na_size_returned_measurement", "na_size_returned_measurement", true));
				}
				if(in_array("naCondition", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`naCondition`", "na_condition", "na_condition", true));

				// NA Filters End

				break;

				// Added this 031008 because the excel on customer default was erroring out!

			case 'default':

				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN sapItemNumber ON conclusion.complaintId=sapItemNumber.complaintId LEFT JOIN materialGroup ON sapItemNumber.complaintId=materialGroup.complaintId");
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN materialGroup ON conclusion.complaintId=materialGroup.complaintId");
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE typeOfComplaint = 'customer_complaint'");
				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN scapaInvoiceNumberDate ON complaint.id=scapaInvoiceNumberDate.complaintId WHERE typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//$results->addColumn(new complaintDateColumn("complaint.`automotiveCovisint`", "automotiveCovisint", "automotive_covisint", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				//$results->addColumn(new column("sapItemNumber.`sapItemNumber`", "sapItemNumber", "sap_item_number", true));
				//if(in_array("DispatchSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "dispatch_site", true));
				//if(in_array("ManufacturingSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("InternalSalesName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internalSalesName`", "internalSalesName", "complaint_creator", true));
				//if(in_array("ProcessOwner", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "process_owner", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("ComplaintValue", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));
				//if(in_array("ComplaintType", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));

				//$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));

				//if(in_array("SAPCustomerNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));
				//if(in_array("SAPCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));

				//$results->addColumn(new complaintStatusColumn("conclusion.`customerComplaintStatus`", "customerComplaintStatus", "customer_complaint_status", true));
				//$results->addColumn(new complaintStatusColumn("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));

				//if(in_array("OverallComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));
				//if(in_array("OverallCustomerComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));
				//if(in_array("ComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;
				// To here!

			case 'customSupplier':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");

				// Complaints Table - 45 fields in here!
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new originalStateColumn("complaint.`originalStateComplaint`", "originalStateComplaint", "original_state_complaint", true));

				if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				if(in_array("customerComplaintDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`customerComplaintDate`", "customerComplaintDate", "created_date", true));
				if(in_array("SiteConcerned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "complaining_site", true));
				if(in_array("buyer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_buyer`", "sp_buyer", "buyer", true));
				if(in_array("SapSupplierNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sapSupplierNumber`", "sp_sapSupplierNumber", "sap_supplier_number", true));
				if(in_array("SapSupplierName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sapSupplierName`", "sp_sapSupplierName", "sap_supplier_name", true));
				if(in_array("how_was_error_detected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_was_error_detected", true));
				if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				if(in_array("g8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`g8d`", "g8d", "g8d", true));
				if(in_array("problem_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`problemDescription`", "problemDescription", "problem_description", true));
				if(in_array("action_requested", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`actionRequested`", "actionRequested", "actions_by_scapa_to_minimise_problem", true));
				if(in_array("materialInvolved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialInvolved`", "sp_materialInvolved", "material_involved", true));
				if(in_array("SAPItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));
				if(in_array("MaterialGroup", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));
				if(in_array("materialBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlocked`", "sp_materialBlocked", "material_blocked", true));
				if(in_array("materialBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlockedName`", "sp_materialBlockedName", "material_blocked_name", true));
				if(in_array("materialBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlockedDate`", "sp_materialBlockedDate", "material_blocked_date", true));
				if(in_array("supplierItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_supplierItemNumber`", "sp_supplierItemNumber", "supplier_item_number", true));
				if(in_array("batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`batchNumber`", "batchNumber", "batch_number", true));
				if(in_array("supplier_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`supplierBatchNumber`", "batchNumber", "scapa_batch_number", true));
				if(in_array("supplierProductDescription", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_supplierProductDescription`", "sp_supplierProductDescription", "supplier_product_description", true));
				if(in_array("goodsReceivedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_goodsReceivedDate`", "sp_goodsReceivedDate", "goods_received_date", true));
				if(in_array("goodsReceivedNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_goodsReceivedNumber`", "sp_goodsReceivedNumber", "goods_received_number", true));
				if(in_array("quantityReceived", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`sp_quantityRecieved_quantity`", "sp_quantityRecieved_quantity", "quantity_received", true));
					$results->addColumn(new column("complaint.`sp_quantityRecieved_measurement`", "sp_quantityRecieved_measurement", "quantity_received_measurement", true));
				}
				if(in_array("quantity_under_complaint_quantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`quantityUnderComplaint_quantity`", "quantityUnderComplaint_quantity", "quantity_under_complaint_quantity", true));
					$results->addColumn(new column("complaint.`quantityUnderComplaint_measurement`", "quantityUnderComplaint_measurement", "quantity_under_complaint_measurement", true));
				}
				if(in_array("complaint_value_quantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`complaintValue_quantity`", "complaintValue_quantity", "complaint_value_quantity", true));
					$results->addColumn(new column("complaint.`complaintValue_measurement`", "complaintValue_measurement", "complaint_value_measurement", true));
				}
				if(in_array("additionalComplaintCost", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`sp_additionalComplaintCost_quantity`", "sp_additionalComplaintCost_quantity", "additional_complaint_cost", true));
					$results->addColumn(new column("complaint.`sp_additionalComplaintCost_measurement`", "sp_additionalComplaintCost_measurement", "additional_complaint_cost_measurement", true));
				}
				if(in_array("detailsOfComplaintCost", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_detailsOfComplaintCost`", "sp_detailsOfComplaintCost", "details_of_complaint_cost", true));
				if(in_array("purchaseOrderNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_purchaseOrderNumber`", "sp_purchaseOrderNumber", "purchase_order_number", true));
				if(in_array("sampleSent", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSent`", "sp_sampleSent", "sp_sampleSent", true));
				if(in_array("sampleSentDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSentDate`", "sp_sampleSentDate", "sample_sent_date", true));
				if(in_array("sampleSentName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSentName`", "sp_sampleSentName", "sample_sent_name", true));
				if(in_array("ComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));
				if(in_array("ComplaintLocation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`complaintLocation`", "complaintLocation", "complaint_location", true));
				if(in_array("ProcessOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "process_owner", true));
				if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				if(in_array("doesContainmentActionExist", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`doesContainmentActionExist`", "doesContainmentActionExist", "does_containment_action_exist", true));
				if(in_array("does8DActionExist", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`does8DActionExist`", "does8DActionExist", "does_8d_action_exist", true));

				/*
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`status`", "status", "status", true));
				if(in_array("product_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productDescription`", "productDescription", "product_description", true));
				if(in_array("ComplaintType", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));
				if(in_array("SAPCustomerNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));
				if(in_array("SAPCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));
				if(in_array("carrier_name", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`carrierName`", "carrierName", "carrier_name", true));
				if(in_array("sample_reception_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleReceptionDate`", "sampleReceptionDate", "sample_reception_date", true));
				if(in_array("sample_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleDate`", "sampleDate", "sample_date", true));
				*/


				// Evaluation Table - 40 Fields! (not 63 fields) Woo hoo!
				// name of analysis!

				if(in_array("isWithSupplier", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));
				if(in_array("team_leader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamLeader`", "teamLeader", "team_leader", true));
				if(in_array("i", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`warehouseDate`", "warehouseDate", "warehouse_stock", true));
				if(in_array("defectQuantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity_quantity`", "defectQuantity_quantity", "warehouse_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity_measurement`", "defectQuantity_measurement", "warehouse_measurement", true));
				}
				if(in_array("productionDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`productionDate`", "productionDate", "production_date", true));
				if(in_array("defectQuantity2", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity2_quantity`", "defectQuantity2_quantity", "production_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity2_measurement`", "defectQuantity2_measurement", "production_measurement", true));
				}
				if(in_array("transitDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`transitDate`", "transitDate", "transit_date", true));
				if(in_array("defectQuantity3", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity3_quantity`", "defectQuantity3_quantity", "transit_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity3_measurement`", "defectQuantity3_measurement", "transit_measurement", true));
				}
				if(in_array("goodJobInvoiceNo", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`goodJobInvoiceNo`", "goodJobInvoiceNo", "good_job_invoice_no", true));
				if(in_array("deliveryNote", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`deliveryNote`", "deliveryNote", "delivery_note_no", true));
				if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysis`", "analysis", "analysis", true));
				if(in_array("author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`author`", "author", "author", true));
				if(in_array("analysis_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`analysisDate`", "analysisDate", "analysis_date", true));
				if(in_array("root_causes", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauses`", "rootCauses", "root_causes", true));
				if(in_array("root_causes_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesAuthor`", "rootCausesAuthor", "root_causes_author", true));
				if(in_array("root_causes_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`rootCausesDate`", "rootCausesDate", "root_causes_date", true));
				if(in_array("complaint_justified", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));
				if(in_array("return_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnGoods`", "returnGoods", "return_goods", true));
				if(in_array("dispose_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`disposeGoods`", "disposeGoods", "dispose_goods", true));
				if(in_array("sp_materialCredited", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_materialCredited`", "sp_materialCredited", "material_credited", true));
				if(in_array("sp_materialReplaced", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_materialReplaced`", "sp_materialReplaced", "material_replaced", true));
				if(in_array("vAnalysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`dateOfAnalysis`", "dateOfAnalysis", "date_of_analysis", true));
				if(in_array("sp_useGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_useGoods`", "sp_useGoods", "use_goods", true));
				if(in_array("sp_reworkGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_reworkGoods`", "sp_reworkGoods", "rework_goods", true));
				if(in_array("sp_sortGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_sortGoods`", "sp_sortGoods", "sort_goods", true));
				if(in_array("containment_action", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "containment_action", true));
				if(in_array("implemented_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActions`", "implementedActions", "implemented_actions", true));
				if(in_array("implemented_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsAuthor`", "implementedActionsAuthor", "implemented_actions_author", true));
				if(in_array("implemented_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("management_system_reviewed", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewed`", "managementSystemReviewed", "management_system_reviewed", true));
				if(in_array("flowChart", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChart`", "flowChart", "flow_chart", true));
				if(in_array("fmea", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmea`", "fmea", "fmea", true));
				if(in_array("customerSpecification", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecification`", "customerSpecification", "customer_specification", true));
				if(in_array("additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`additionalComments`", "additionalComments", "additional_comments2", true));
				if(in_array("possible_solutions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutions`", "possibleSolutions", "possible_solutions", true));
				if(in_array("possible_solutions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsAuthor`", "possibleSolutionsAuthor", "possible_solutions_author", true));
				if(in_array("possible_solutions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`possibleSolutionsDate`", "possibleSolutionsDate", "possible_solutions_date", true));

				/*
				if(in_array("is_po_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isPORight`", "isPORight", "is_po_right", true));
				if(in_array("reason_for_rejection", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`reasonForRejection`", "reasonForRejection", "reason_for_rejection", true));
				if(in_array("is_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isSampleReceived`", "isSampleReceived", "is_sample_received", true));
				if(in_array("date_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`dateSampleReceived`", "dateSampleReceived", "date_sample_received", true));
				if(in_array("team_member", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamMember`", "teamMember", "team_member", true));
				if(in_array("is_complaint_cat_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isComplaintCatRight`", "isComplaintCatRight", "is_complaint_cat_right", true));
				if(in_array("correct_category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`correctCategory`", "correctCategory", "correct_category", true));
				if(in_array("failure_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`failureCode`", "failureCode", "failure_code", true));
				if(in_array("root_cause_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauseCode`", "rootCauseCode", "root_cause_code", true));
				if(in_array("attributable_process", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`attributableProcess`", "attributableProcess", "attributable_process", true));
				if(in_array("containment_action_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionAuthor`", "containmentActionAuthor", "containment_action_author", true));
				if(in_array("containment_action_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`containmentActionDate`", "containmentActionDate", "containment_action_date", true));
				if(in_array("implemented_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				if(in_array("implemented_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsImplementation`", "implementedActionsImplementation", "implemented_actions_implementation", true));
				if(in_array("preventive_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActions`", "preventiveActions", "preventive_actions", true));
				if(in_array("preventive_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsAuthor`", "preventiveActionsAuthor", "preventive_actions_author", true));
				if(in_array("preventive_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsDate`", "preventiveActionsDate", "preventive_actions_date", true));
				if(in_array("preventive_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEstimated`", "preventiveActionsEstimated", "preventive_actions_estimated", true));
				if(in_array("preventive_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsImplementation`", "preventiveActionsImplementation", "preventive_actions_implementation", true));
				if(in_array("preventive_actions_effectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEffectiveness`", "preventiveActionsEffectiveness", "preventive_actions_effectiveness", true));
				if(in_array("management_system_reviewed_ref", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewedRef`", "managementSystemReviewedRef", "management_system_reviewed_ref", true));
				if(in_array("management_system_reviewed_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`managementSystemReviewedDate`", "managementSystemReviewedDate", "management_system_reviewed_date", true));
				if(in_array("inspectionInstructions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructions`", "inspectionInstructions", "inspectionInstructions", true));
				if(in_array("inspectionInstructionsRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructionsRef`", "inspectionInstructionsRef", "inspectionInstructionsRef", true));
				if(in_array("inspectionInstructionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`inspectionInstructionsDate`", "inspectionInstructionsDate", "inspectionInstructionsDate", true));
				if(in_array("fmeaRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmeaRef`", "fmeaRef", "fmea_ref", true));
				if(in_array("fmeaDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`fmeaDate`", "fmeaDate", "fmea_date", true));
				if(in_array("customerSpecificationRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecificationRef`", "customerSpecificationRef", "customer_specification_ref", true));
				if(in_array("customerSpecificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`customerSpecificationDate`", "customerSpecificationDate", "customer_specification_date", true));
				if(in_array("comments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`comments`", "comments", "comments", true));
				*/

				// Conclusion Table 24 fields now!


				if(in_array("customerDerongation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`customerDerongation`", "sp_customerDerongation", "customer_derongation", true));
				if(in_array("sp_customerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_customerName`", "sp_customerName", "customer_name", true));
				if(in_array("sp_requestDisposal", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestDisposal`", "sp_requestDisposal", "request_disposals", true));
				if(in_array("sp_sapItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_sapItemNumber`", "sp_sapItemNumber", "sap_item_number", true));
				if(in_array("sp_amount", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_amount_quantity`", "sp_amount_quantity", "disposal_amount_quantity", true));
					$results->addColumn(new column("conclusion.`sp_amount_measurement`", "sp_amount_measurement", "disposal_amount_measurement", true));
				}
				if(in_array("sp_value", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_value_quantity`", "sp_value_quantity", "disposal_value_quantity", true));
					$results->addColumn(new column("conclusion.`sp_value_measurement`", "sp_value_measurement", "disposal_value_measurement", true));
				}
				if(in_array("sp_requestEmailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestEmailText`", "sp_requestEmailText", "email_text_comment", true));
				if(in_array("processOwner3Request", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`processOwner3Request`", "processOwner3Request", "chosen_complaint_owner", true));
				if(in_array("sp_requestAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorised`", "sp_requestAuthorised", "request_authorised", true));
				if(in_array("sp_requestAuthorisorName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisorName`", "sp_requestAuthorisorName", "sp_requestAuthorisorName", true));
				if(in_array("sp_materialDisposed", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposed`", "sp_materialDisposed", "material_disposed", true));
				if(in_array("sp_materialDisposedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedName`", "sp_materialDisposedName", "material_disposed_name", true));
				if(in_array("sp_materialDisposedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedDate`", "sp_materialDisposedDate", "material_disposed_date", true));
				if(in_array("sp_materialDisposedCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedCode`", "sp_materialDisposedCode", "material_disposed_code", true));
				if(in_array("sp_materialReturned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturned`", "sp_materialReturned", "material_returned", true));
				if(in_array("sp_materialReturnedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturnedName`", "sp_materialReturnedName", "material_returned_name", true));
				if(in_array("sp_materialReturnedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturnedDate`", "sp_materialReturnedDate", "material_returned_date", true));
				if(in_array("sp_sapReturnNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_sapReturnNumber`", "sp_sapReturnNumber", "sap_return_number", true));
				if(in_array("sp_supplierCreditNoteRec", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierCreditNoteRec`", "sp_supplierCreditNoteRec", "supplier_credit_note_received", true));
				if(in_array("sp_supplierCreditNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierCreditNumber`", "sp_supplierCreditNumber", "supplier_credit_number", true));
				if(in_array("sp_supplierCreditNumber", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_supplierCreditNumber_quantity`", "sp_supplierCreditNumber_quantity", "sp_supplierCreditNumber_value", true));
					$results->addColumn(new column("conclusion.`sp_supplierCreditNumber_measurement`", "sp_supplierCreditNumber_measurement", "sp_supplierCreditNumber_measurement", true));
				}
				if(in_array("sp_comment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_comment`", "sp_comment", "comment", true));
				if(in_array("sp_supplierReplacementRec", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierReplacementRec`", "sp_supplierReplacementRec", "supplier_replacement_received", true));
				if(in_array("sp_finalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_finalComments`", "sp_finalComments", "final_comment", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("processOwner3", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`processOwner3`", "processOwner3", "chosen_complaint_owner", true));

				// Fields from Internal Complaint to Supplier Complaint
				if(in_array("internal_teamLeader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_teamLeader`", "internal_teamLeader", "internal_team_leader", true));
				if(in_array("internal_teamMember", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_teamMember`", "internal_teamMember", "internal_team_member", true));
				if(in_array("internal_qu_stockVerificationMade", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_stockVerificationMade`", "internal_qu_stockVerificationMade", "internal_qu_stock_verification_made", true));
				if(in_array("internal_qu_stockVerificationName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_stockVerificationName`", "internal_qu_stockVerificationName", "internal_qu_stock_verification_name", true));
				if(in_array("internal_qu_stockVerificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`internal_qu_stockVerificationDate`", "internal_qu_stockVerificationDate", "internal_qu_stock_verification_date", true));
				if(in_array("internal_qu_otherMaterialEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_otherMaterialEffected`", "internal_qu_otherMaterialEffected", "internal_qu_other_material_effected", true));
				if(in_array("internal_qu_otherMatDetails", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_otherMatDetails`", "internal_qu_otherMatDetails", "internal_qu_other_mat_details", true));
				if(in_array("internal_analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_analysis`", "internal_analysis", "internal_analysis", true));
				if(in_array("internal_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_author`", "internal_author", "internal_author", true));
				if(in_array("internal_analysisDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`internal_analysisDate`", "internal_analysisDate", "internal_analysis_date", true));
				if(in_array("internal_additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_additionalComments`", "internal_additionalComments", "internal_additional_comments", true));





				/*
				if(in_array("sp_requestAuthorisedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisedDate`", "sp_requestAuthorisedDate", "request_authorised_date", true));
				if(in_array("sp_requestAuthorisedEmailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisedEmailText`", "sp_requestAuthorisedEmailText", "email_text", true));
				*/				break;

			case 'defaultSupplier':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new originalStateColumn("complaint.`originalStateComplaint`", "originalStateComplaint", "original_state_complaint", true));

				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`sp_buyer`", "sp_buyer", "buyer", true));

				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));

				$results->addColumn(new column("complaint.`sp_sapSupplierNumber`", "sp_sapSupplierNumber", "sp_sapSupplierNumber", true));

				$results->addColumn(new column("complaint.`sp_sapSupplierName`", "sp_sapSupplierName", "sp_sapSupplierName", true));

				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_error_detected", true));

				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));

				//$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;

			case 'defaultQuality':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'quality_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));

				$results->addColumn(new column("complaint.`whereErrorOccured`", "whereErrorOccured", "where_error_occured", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));

				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));

				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_error_detected", true));

				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));

				//$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;

			case 'customQuality':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'quality_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				/* 121 fields all together */
				/* Complaints database - 25 Fields */


				if(in_array("owner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "owner", true));
				if(in_array("salesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				if(in_array("groupAComplaint", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`groupAComplaint`", "groupAComplaint", "group_a_complaint", true));
				if(in_array("groupedComplaintId", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`groupedComplaintId`", "groupedComplaintId", "grouped_complaint_id", true));
				if(in_array("whereErrorOccured", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`whereErrorOccured`", "whereErrorOccured", "whereErrorOccured", true));
				if(in_array("sp_siteConcerned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));
				if(in_array("others", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`others`", "others", "others", true));
				if(in_array("qu_foundBy", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_foundBy`", "qu_foundBy", "found_by", true));
				if(in_array("internalReferenceNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internalReferenceNumber`", "internalReferenceNumber", "internal_reference_number", true));
				if(in_array("clauseEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`clauseEffected`", "clauseEffected", "clause_effected", true));
				if(in_array("severity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`severity`", "severity", "severity", true));
				if(in_array("customerComplaintDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`customerComplaintDate`", "customerComplaintDate", "created_date", true));
				if(in_array("how_was_error_detected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_was_error_detected", true));
				if(in_array("lineStoppage", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lineStoppage`", "lineStoppage", "line_stoppage", true));
				if(in_array("problem_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`problemDescription`", "problemDescription", "problem_description", true));
				if(in_array("containment_action", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "containment_action", true));
				if(in_array("requestedAction", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`requestedAction`", "requestedAction", "requested_action", true));
				if(in_array("materialInvolved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialInvolved`", "qu_materialInvolved", "material_involved", true));
				if(in_array("manufacturingNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`manufacturingNumber`", "manufacturingNumber", "manufacturing_number", true));
				if(in_array("dateOfManufacturing", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`dateOfManufacturing`", "dateOfManufacturing", "date_of_manufacturing", true));
				if(in_array("sapMaterialGroups", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));
				if(in_array("sapItemNumbers", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));
				if(in_array("batchNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`batchNumber`", "batchNumber", "batch_number", true));
				if(in_array("supplier_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`supplierBatchNumber`", "batchNumber", "scapa_batch_number", true));
				if(in_array("lotNo", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lotNo`", "lotNo", "lot_no", true));
				if(in_array("qu_materialLocation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialLocation`", "qu_materialLocation", "material_location", true));
				if(in_array("productDescription", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productDescription`", "productDescription", "product_description", true));
				if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				if(in_array("dimensionThickness", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionThickness_quantity`", "dimensionThickness_quantity", "dimensionThickness", true));
					$results->addColumn(new column("complaint.`dimensionThickness_measurement`", "dimensionThickness_measurement", "dimensionThickness_measurement", true));
				}
				if(in_array("dimensionWidth", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionWidth_quantity`", "dimensionWidth_quantity", "dimensionWidth", true));
					$results->addColumn(new column("complaint.`dimensionWidth_measurement`", "dimensionWidth_measurement", "dimensionWidth_measurement", true));
				}
				if(in_array("dimensionLength", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionLength_quantity`", "dimensionLength_quantity", "dimensionLength", true));
					$results->addColumn(new column("complaint.`dimensionLength_measurement`", "dimensionLength_measurement", "dimensionLength_measurement", true));
				}
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`colour`", "colour", "colour", true));
				if(in_array("quantityUnderComplaint", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`quantityUnderComplaint_quantity`", "quantityUnderComplaint_quantity", "quantityUnderComplaint", true));
					$results->addColumn(new column("complaint.`quantityUnderComplaint_measurement`", "quantityUnderComplaint_measurement", "quantityUnderComplaint_measurement", true));
				}
				if(in_array("qu_complaintCosts", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`qu_complaintCosts_quantity`", "qu_complaintCosts_quantity", "qu_complaintCosts", true));
					$results->addColumn(new column("complaint.`qu_complaintCosts_measurement`", "qu_complaintCosts_measurement", "qu_complaintCosts_measurement", true));
				}
				if(in_array("qu_commentOnCost", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_commentOnCost`", "qu_commentOnCost", "comment_on_cost", true));
				if(in_array("qu_weightOfMaterial", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`qu_weightOfMaterial_quantity`", "qu_weightOfMaterial_quantity", "qu_weightOfMaterial", true));
					$results->addColumn(new column("complaint.`qu_weightOfMaterial_measurement`", "qu_weightOfMaterial_measurement", "qu_weightOfMaterial_measurement", true));
				}
				if(in_array("qu_materialBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlocked`", "qu_materialBlocked", "material_blocked", true));
				if(in_array("qu_materialBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlockedDate`", "qu_materialBlockedDate", "material_blocked_date", true));
				if(in_array("qu_materialBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlockedName`", "qu_materialBlockedName", "material_blocked_name", true));
				if(in_array("processOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "chosen_complaint_owner_complaint", true));

				if(in_array("implementedPermanentCorrectiveActionValidated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.implementedPermanentCorrectiveActionValidated", "implemented_permanent_corrective_action_validated", "implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`implementedPermanentCorrectiveActionValidatedyn`", "was_implemented_permanent_corrective_action_validated", "was_implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedPermanentCorrectiveActionValidatedDate`", "the_implemented_permanent_corrective_action_date", "the_implemented_permanent_corrective_action_date", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedPermanentCorrectiveActionValidatedAuthor`", "the_implemented_permanent_corrective_action_validated_author", "the_implemented_permanent_corrective_action_validated_author", true));

				/* Evaluation database - 69 fields! */

				if(in_array("teamLeader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamLeader`", "teamLeader", "team_leader", true));
				if(in_array("teamMember", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamMember`", "teamMember", "team_member", true));
				if(in_array("qu_verificationMade", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_verificationMade`", "qu_verificationMade", "verification_made", true));
				if(in_array("qu_verificationName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_verificationName`", "qu_verificationName", "verification_name", true));
				if(in_array("qu_verificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`qu_verificationDate`", "qu_verificationDate", "verification_date", true));
				if(in_array("qu_otherMaterialEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherMaterialEffected`", "qu_otherMaterialEffected", "other_material_effected", true));
				if(in_array("qu_otherMatDetails", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherMatDetails`", "qu_otherMatDetails", "other_material_details", true));
				if(in_array("analysisyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysisyn`", "analysisyn", "analysis_yes_no", true));
				if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysis`", "analysis", "analysis", true));
				if(in_array("author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`author`", "author", "author", true));
				if(in_array("analysisDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`analysisDate`", "analysisDate", "analysis_date", true));
				if(in_array("qu_supplierIssue", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_supplierIssue`", "qu_supplierIssue", "supplier_issue", true));
				if(in_array("qu_supplierIssueAction", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_supplierIssueAction`", "qu_supplierIssueAction", "complaint_will_be_actioned", true));
				if(in_array("rootCauses", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauses`", "rootCauses", "root_causes", true));
				if(in_array("rootCausesyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesyn`", "rootCausesyn", "root_causes_yes_no", true));
				if(in_array("rootCausesAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesAuthor`", "rootCausesAuthor", "root_causes_author", true));
				if(in_array("rootCausesDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`rootCausesDate`", "rootCausesDate", "root_causes_date", true));
				if(in_array("attributableProcess", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`attributableProcess`", "attributableProcess", "attributable_process", true));
				if(in_array("failureCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`failureCode`", "failureCode", "failure_code", true));
				if(in_array("rootCauseCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauseCode`", "rootCauseCode", "root_cause_code", true));
				if(in_array("disposeGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`disposeGoods`", "disposeGoods", "dispose_goods", true));
				if(in_array("qu_useGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_useGoods`", "qu_useGoods", "use_goods", true));
				if(in_array("qu_useGoodsDerongation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_useGoodsDerongation`", "qu_useGoodsDerongation", "use_goods_derongation", true));
				if(in_array("qu_customerApproved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_customerApproved`", "qu_customerApproved", "customer_approved", true));
				if(in_array("qu_nameOfCustomer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_nameOfCustomer`", "qu_nameOfCustomer", "name_of_customer", true));
				if(in_array("qu_reworkTheGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_reworkGoods`", "qu_reworkGoods", "rework_the_goods", true));
				if(in_array("qu_otherSimilarProducts", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherSimilarProducts`", "qu_otherSimilarProducts", "other_similar_products_recalled", true));
				if(in_array("qu_authorGoodsDecision", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_authorGoodsDecision`", "qu_authorGoodsDecision", "author_for_goods_decision", true));
				if(in_array("qu_authorGoodsDecisionDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`qu_authorGoodsDecisionDate`", "qu_authorGoodsDecisionDate", "author_for_goods_decision_date", true));
				if(in_array("containmentAction_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "eval_containment_action", true));
				if(in_array("containmentActionyn_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionyn`", "containmentActionyn", "eval_containment_action_yes_no", true));
				if(in_array("containmentActionAuthor_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionAuthor`", "containmentActionAuthor", "eval_containment_action_author", true));
				if(in_array("containmentActionDate_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`containmentActionDate`", "containmentActionDate", "eval_containment_action_date", true));
				if(in_array("possibleSolutions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutions`", "possibleSolutions", "possible_solutions", true));
				if(in_array("possibleSolutionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsyn`", "possibleSolutionsyn", "possible_solutions_yes_no", true));
				if(in_array("possibleSolutionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsAuthor`", "possibleSolutionsAuthor", "possible_solutions_author", true));
				if(in_array("possibleSolutionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`possibleSolutionsDate`", "possibleSolutionsDate", "possible_solutions_date", true));
				if(in_array("implementedActionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsyn`", "implementedActionsyn", "implemented_actions_yes_no", true));
				if(in_array("implementedActions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActions`", "implementedActions", "implemented_actions", true));
				if(in_array("implementedActionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsAuthor`", "implementedActionsAuthor", "implemented_actions_author", true));
				if(in_array("implementedActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("implementedActionsEstimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				if(in_array("implementedActionsEffectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsEffectiveness`", "implementedActionsEffectiveness", "implemented_actions_effectiveness", true));
				if(in_array("implementedActionsImplemetation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsImplementation`", "implementedActionsImplemetantion", "implemented_actions_implementation", true));
				if(in_array("preventiveActions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActions`", "preventiveActions", "preventive_actions", true));
				if(in_array("preventiveActionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsyn`", "preventiveActionsyn", "preventive_actions_yes_no", true));
				if(in_array("preventiveActionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsAuthor`", "preventiveActionsAuthor", "preventive_actions_author", true));
				if(in_array("preventiveActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsDate`", "preventiveActionsDate", "preventive_actions_date", true));
				if(in_array("preventiveActionsEstimatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsEstimatedDate`", "preventiveActionsEstimatedDate", "preventive_actions_estimated_date", true));
				if(in_array("preventiveActionsImplementedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsImplementedDate`", "preventiveActionsImplementedDate", "preventive_actions_implemented_date", true));
				if(in_array("preventiveActionsValidationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsValidationDate`", "preventiveActionsValidationDate", "preventive_actions_validation_date", true));
				if(in_array("riskAssessment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`riskAssessment`", "riskAssessment", "risk_assessment", true));
				if(in_array("riskAssessmentRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`riskAssessmentRef`", "riskAssessmentRef", "risk_assessment_date", true));
				if(in_array("riskAssessmentDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`riskAssessmentDate`", "riskAssessmentDate", "risk_assessment_ref", true));
				if(in_array("managementSystemReview", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewed`", "managementSystemReviewed", "management_system_review", true));
				if(in_array("managementSystemReviewDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`managementSystemReviewedDate`", "managementSystemReviewedDate", "management_system_review_date", true));
				if(in_array("managementSystemReviewRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewedRef`", "managementSystemedReviewRef", "management_system_review_ref", true));
				if(in_array("fmea", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmea`", "fmea", "fmea", true));
				if(in_array("fmeaDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`fmeaDate`", "fmeaDate", "fmea_date", true));
				if(in_array("fmeaRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmeaRef`", "fmeaRef", "fmea_ref", true));
				if(in_array("customerSpecification", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecification`", "customerSpecification", "customer_specification", true));
				if(in_array("customerSpecificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`customerSpecificationDate`", "customerSpecificationDate", "customer_specification_date", true));
				if(in_array("customerSpecificationRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecificationRef`", "customerSpecificationRef", "customer_specification_ref", true));
				if(in_array("flowChart", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChart`", "flowChart", "flow_chart", true));
				if(in_array("flowChartDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`flowChartDate`", "flowChartDate", "flow_chart_date", true));
				if(in_array("flowChartRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChartRef`", "flowChartRef", "flow_chart_ref", true));
				if(in_array("additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`additionalComments`", "additionalComments", "additional_comments_eval", true));
				if(in_array("processOwner2", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`processOwner2`", "processOwner2", "chosen_complaint_owner_evaluation", true));
				if(in_array("emailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`emailText`", "emailText", "email_text_evaluation", true));


				/* conclusion table - 27 fields! */
				if(in_array("qu_materialUnBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialUnBlocked`", "qu_materialUnBlocked", "material_unblocked", true));
				if(in_array("qu_materialUnBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialUnBlockedName`", "qu_materialUnBlockedName", "material_unblocked_name", true));
				if(in_array("qu_materialUnBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_materialUnBlockedDate`", "qu_materialUnBlockedDate", "material_unblocked_date", true));
				if(in_array("qu_requestForDisposal", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_requestForDisposal`", "qu_requestForDisposal", "request_for_disposal", true));
				if(in_array("qu_requestForDisposalAmount", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`qu_amount_quantity`", "qu_amount_quantity", "request_for_disposal_amount", true));
					$results->addColumn(new column("conclusion.`qu_amount_measurement`", "qu_amount_measurement", "request_for_disposal_amount_measurement", true));
				}
				if(in_array("qu_requestForDisposalDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_requestDate`", "qu_requestDate", "request_for_disposal_date", true));
				if(in_array("qu_requestForDisposalName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_requestDisposalName`", "qu_requestDisposalName", "request_for_disposal_name", true));
				if(in_array("qu_disposalAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorised`", "qu_disposalAuthorised", "disposal_authorised", true));
				if(in_array("qu_disposalAuthorisedComment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorisedComment`", "qu_disposalAuthorisedComment", "disposal_authorised_comment", true));
				if(in_array("qu_disposalAuthorisedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalAuthorisedDate`", "qu_disposalAuthorisedDate", "disposal_authorised_date", true));
				if(in_array("qu_disposalAuthorisedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorisedName`", "qu_disposalAuthorisedName", "disposal_authorised_name", true));
				if(in_array("qu_disposalBooked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalBooked`", "qu_disposalBooked", "disposal_booked", true));
				if(in_array("qu_disposalBookedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalBookedName`", "qu_disposalBookedName", "disposal_booked_name", true));
				if(in_array("qu_disposalBookedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalBookedDate`", "qu_disposalBookedDate", "disposal_booked_date", true));
				if(in_array("qu_disposalCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalCode`", "qu_disposalCode", "disposal_code", true));
				if(in_array("qu_disposalCostCentre", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalCostCentre`", "qu_disposalCostCentre", "disposal_cost_centre", true));
				if(in_array("qu_disposalPhysicallyDone", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalPhysicallyDone`", "qu_disposalPhysicallyDone", "disposal_physically_done", true));
				if(in_array("qu_disposalPhysicallyDoneName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalPhysicallyDoneName`", "qu_disposalPhysicallyDoneName", "disposal_physically_done_name", true));
				if(in_array("qu_disposalPhysicallyDoneDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalPhysicallyDoneDate`", "qu_disposalPhysicallyDoneDate", "disposal_physically_done_date", true));
				if(in_array("qu_materialReturnedToCustomer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialReturnedToCustomer`", "qu_materialReturnedToCustomer", "material_returned_to_customer", true));
				if(in_array("qu_materialReturnedToCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialReturnedToCustomerName`", "qu_materialReturnedToCustomerName", "material_returned_to_customer_name", true));
				if(in_array("qu_materialReturnedToCustomerDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_materialReturnedToCustomerDate`", "qu_materialReturnedToCustomerDate", "material_returned_to_customer_date", true));
				if(in_array("finalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`finalComments`", "finalComments", "final_comments", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("totalClosureDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`totalClosureDate`", "totalClosureDate", "total_closure_date", true));
				if(in_array("processOwner3", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("conclusion.`processOwner3`", "processOwner3", "chosen_complaint_owner_conclusion", true));

				break;

			case 'performance':

				$this->updatePerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "customer_complaint_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("ClosedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`closedDate`", "closedDate", "closed_date", true));
				//$results->addColumn(new column("complaint.`analysisDate`", "analysisDate", "analysis_date", true));
				//if(in_array("ImplementedActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				$results->addColumn(new column("complaint.`complaintValue_quantity`", "complaintValue_quantity", "complaint_value_quantity", true));
				$results->addColumn(new column("complaint.`complaintValue_measurement`", "complaintValue_measurement", "complaint_value_measurement", true));
				$results->addColumn(new column("conclusion.`creditNoteValue_quantity`", "creditNoteValue_quantity", "credit_note_value", true));
				$results->addColumn(new column("conclusion.`creditNoteValue_measurement`", "creditNoteValue_measurement", "credit_note_measurement", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_3d`", "performance_3d", "reg_performance_3d", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_5d`", "performance_5d", "reg_performance_5d", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8d`", "performance_8d", "reg_performance_8d", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_cco`", "performance_cco", "close_out_performance", true));

				$results->addColumn(new column("complaint.`performance_tco`", "performance_tco", "total_close_out_performance", true));

				break;

			case 'performanceSupplier':

				$this->updateSupplierPerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_supplier_number", true));
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_supplier_name", true));

				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`analysisyn`", "analysisyn", "analysis", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_3dSupplier`", "performance_3dSupplier", "performance_3d_supplier", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new externalStatus("complaint.`performance_3dSupplierStatus`", "performance_3dSupplierStatus", "performance_3d_supplier_status", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8dSupplier`", "performance_8dSupplier", "performance_8dSupplier", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new externalStatus("complaint.`performance_8dSupplierStatus`", "performance_8dSupplierStatus", "performance_8dSupplier_status", true));


				break;

			case 'performance_summary':

				$this->updatePerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`analysisyn`", "analysisyn", "analysis", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_3d`", "performance_3d", "reg_performance_3d", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_5d`", "performance_5d", "reg_performance_5d", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8d`", "performance_8d", "reg_performance_8d", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_cco`", "performance_cco", "close_out_performance", true));

				break;

			case 'all':

				$results->setBaseQuery("SELECT * FROM complaint WHERE overallCustomerComplaintStatus != 'Closed'");

				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "dispatch_site", true));

				$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));

				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));

				$results->addColumn(new column("complaint.`internalSalesName`", "internalSalesName", "complaint_creator", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`businessUnit`", "businessUnit", "business_unit", true));

				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));

				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));

				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));

				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				$results->addColumn(new column("complaint.`complaintHowLong`", "complaintHowLong", "complaint_unchanged_for_days", true));

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

							$results->addColumn(new column("complaint.`" . $control->getName() . "_quantity`", $control->getName() . "_quantity", $control->getRowTitle() . " (Quantity)", true));
							$results->addColumn(new column("complaint.`" . $control->getName() . "_measurement`", $control->getName() . "_measurement", $control->getRowTitle() . " (Units)", true));

							break;

						default:



							$results->addColumn(new column("complaint.`" . $control->getName() . "`", $control->getName(), $control->getRowTitle(), true));
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
					$results->addColumn(new column("complaint.`dummy`", "dummy", $form->getGroup($groups[$i])->get(0, $controlKey)->getRowTitle(), true));
				}
				//}
			}
		}
	}





	/* WC - AE 18/01/08
	This whole section has had results filtered into columns
	*/

	private function showResults()
	{

		if (isset($_REQUEST['load']))
		{
			// load saved stuff from db into session...

			$this->redirect("searchNew?action=view");
		}

		$results = new searchResults();

		$results->setSelectedFilters($this->selectedFilters);


		//var_dump($this->selectedFilters);

		$results->setDatabase("complaints");
		$results->setBaseQuery("SELECT * FROM complaint");
		$results->setOrderBy("complaint.id");
		switch($this->chooseReportForm->get("reportType")->getValue())
		{
			case 'custom':
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId LEFT JOIN materialGroup ON conclusion.complaintId=materialGroup.complaintId");
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				$results->setBaseQuery("SELECT * FROM complaint WHERE typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");

				// Default ID column to show
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$datasetCustom = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaintsReport WHERE complaintType = 'customer_complaint' ORDER BY name ASC");

				while($fieldsCustom = mysql_fetch_array($datasetCustom))
				{
					if(in_array($fieldsCustom['name'], $this->selectedColumns) || $this->showAllCols)
					{
						switch($fieldsCustom['customFilterType'])
						{
							case 'complaintDateColumn':

								$results->addColumn(new complaintDateColumn("" . $fieldsCustom['complaintTable'] . ".`" . $fieldsCustom['name'] . "`", $fieldsCustom['name'], $fieldsCustom['priorTranslation'], true));

								break;

							case 'complaintOwnerColumn':

								$results->addColumn(new complaintOwnerColumn("" . $fieldsCustom['complaintTable'] . ".`" . $fieldsCustom['name'] . "`", $fieldsCustom['name'], $fieldsCustom['priorTranslation'], true));

							default:

								$results->addColumn(new column("" . $fieldsCustom['complaintTable'] . ".`" . $fieldsCustom['name'] . "`", $fieldsCustom['name'], $fieldsCustom['priorTranslation'], true));

								break;
						}
					}
				}

				break;

			case 'customSupplier':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");

				// Complaints Table - 45 fields in here!
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new originalStateColumn("complaint.`originalStateComplaint`", "originalStateComplaint", "original_state_complaint", true));

				if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				if(in_array("customerComplaintDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`customerComplaintDate`", "customerComplaintDate", "created_date", true));
				if(in_array("SiteConcerned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "complaining_site", true));
				if(in_array("buyer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_buyer`", "sp_buyer", "buyer", true));
				if(in_array("SapSupplierNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sapSupplierNumber`", "sp_sapSupplierNumber", "sap_supplier_number", true));
				if(in_array("SapSupplierName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sapSupplierName`", "sp_sapSupplierName", "sap_supplier_name", true));
				if(in_array("how_was_error_detected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_was_error_detected", true));
				if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				if(in_array("g8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`g8d`", "g8d", "g8d", true));
				if(in_array("problem_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`problemDescription`", "problemDescription", "problem_description", true));
				if(in_array("action_requested", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`actionRequested`", "actionRequested", "actions_by_scapa_to_minimise_problem", true));
				if(in_array("materialInvolved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialInvolved`", "sp_materialInvolved", "material_involved", true));
				if(in_array("SAPItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));
				if(in_array("MaterialGroup", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));
				if(in_array("materialBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlocked`", "sp_materialBlocked", "material_blocked", true));
				if(in_array("materialBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlockedName`", "sp_materialBlockedName", "material_blocked_name", true));
				if(in_array("materialBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_materialBlockedDate`", "sp_materialBlockedDate", "material_blocked_date", true));
				if(in_array("supplierItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_supplierItemNumber`", "sp_supplierItemNumber", "supplier_item_number", true));
				if(in_array("batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`batchNumber`", "batchNumber", "batch_number", true));
				if(in_array("supplier_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`supplierBatchNumber`", "batchNumber", "scapa_batch_number", true));
				if(in_array("supplierProductDescription", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_supplierProductDescription`", "sp_supplierProductDescription", "supplier_product_description", true));
				if(in_array("goodsReceivedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_goodsReceivedDate`", "sp_goodsReceivedDate", "goods_received_date", true));
				if(in_array("goodsReceivedNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_goodsReceivedNumber`", "sp_goodsReceivedNumber", "goods_received_number", true));
				if(in_array("quantityReceived", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`sp_quantityRecieved_quantity`", "sp_quantityRecieved_quantity", "quantity_received", true));
					$results->addColumn(new column("complaint.`sp_quantityRecieved_measurement`", "sp_quantityRecieved_measurement", "quantity_received_measurement", true));
				}
				if(in_array("quantity_under_complaint_quantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`quantityUnderComplaint_quantity`", "quantityUnderComplaint_quantity", "quantity_under_complaint_quantity", true));
					$results->addColumn(new column("complaint.`quantityUnderComplaint_measurement`", "quantityUnderComplaint_measurement", "quantity_under_complaint_measurement", true));
				}
				if(in_array("complaint_value_quantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`complaintValue_quantity`", "complaintValue_quantity", "complaint_value_quantity", true));
					$results->addColumn(new column("complaint.`complaintValue_measurement`", "complaintValue_measurement", "complaint_value_measurement", true));
				}
				if(in_array("additionalComplaintCost", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`sp_additionalComplaintCost_quantity`", "sp_additionalComplaintCost_quantity", "additional_complaint_cost", true));
					$results->addColumn(new column("complaint.`sp_additionalComplaintCost_measurement`", "sp_additionalComplaintCost_measurement", "additional_complaint_cost_measurement", true));
				}
				if(in_array("detailsOfComplaintCost", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_detailsOfComplaintCost`", "sp_detailsOfComplaintCost", "details_of_complaint_cost", true));
				if(in_array("purchaseOrderNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_purchaseOrderNumber`", "sp_purchaseOrderNumber", "purchase_order_number", true));
				if(in_array("sampleSent", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSent`", "sp_sampleSent", "sp_sampleSent", true));
				if(in_array("sampleSentDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSentDate`", "sp_sampleSentDate", "sample_sent_date", true));
				if(in_array("sampleSentName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_sampleSentName`", "sp_sampleSentName", "sample_sent_name", true));
				if(in_array("ComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));
				if(in_array("ComplaintLocation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`complaintLocation`", "complaintLocation", "complaint_location", true));
				if(in_array("ProcessOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "process_owner", true));
				if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				if(in_array("doesContainmentActionExist", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`doesContainmentActionExist`", "doesContainmentActionExist", "does_containment_action_exist", true));
				if(in_array("does8DActionExist", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`does8DActionExist`", "does8DActionExist", "does_8d_action_exist", true));

				/*
				if(in_array("status", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`status`", "status", "status", true));
				if(in_array("product_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productDescription`", "productDescription", "product_description", true));
				if(in_array("ComplaintType", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));
				if(in_array("SAPCustomerNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));
				if(in_array("SAPCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));
				if(in_array("carrier_name", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`carrierName`", "carrierName", "carrier_name", true));
				if(in_array("sample_reception_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleReceptionDate`", "sampleReceptionDate", "sample_reception_date", true));
				if(in_array("sample_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`sampleDate`", "sampleDate", "sample_date", true));
				*/


				// Evaluation Table - 40 Fields! (not 63 fields) Woo hoo!
				// name of analysis!

				if(in_array("isWithSupplier", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));
				if(in_array("team_leader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamLeader`", "teamLeader", "team_leader", true));
				if(in_array("i", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`warehouseDate`", "warehouseDate", "warehouse_stock", true));
				if(in_array("defectQuantity", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity_quantity`", "defectQuantity_quantity", "warehouse_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity_measurement`", "defectQuantity_measurement", "warehouse_measurement", true));
				}
				if(in_array("productionDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`productionDate`", "productionDate", "production_date", true));
				if(in_array("defectQuantity2", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity2_quantity`", "defectQuantity2_quantity", "production_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity2_measurement`", "defectQuantity2_measurement", "production_measurement", true));
				}
				if(in_array("transitDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`transitDate`", "transitDate", "transit_date", true));
				if(in_array("defectQuantity3", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("evaluation.`defectQuantity3_quantity`", "defectQuantity3_quantity", "transit_quantity", true));
					//					$results->addColumn(new column("evaluation.`defectQuantity3_measurement`", "defectQuantity3_measurement", "transit_measurement", true));
				}
				if(in_array("goodJobInvoiceNo", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`goodJobInvoiceNo`", "goodJobInvoiceNo", "good_job_invoice_no", true));
				if(in_array("deliveryNote", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`deliveryNote`", "deliveryNote", "delivery_note_no", true));
				if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysis`", "analysis", "analysis", true));
				if(in_array("author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`author`", "author", "author", true));
				if(in_array("analysis_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`analysisDate`", "analysisDate", "analysis_date", true));
				if(in_array("root_causes", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauses`", "rootCauses", "root_causes", true));
				if(in_array("root_causes_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesAuthor`", "rootCausesAuthor", "root_causes_author", true));
				if(in_array("root_causes_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`rootCausesDate`", "rootCausesDate", "root_causes_date", true));
				if(in_array("complaint_justified", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));
				if(in_array("return_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`returnGoods`", "returnGoods", "return_goods", true));
				if(in_array("dispose_goods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`disposeGoods`", "disposeGoods", "dispose_goods", true));
				if(in_array("sp_materialCredited", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_materialCredited`", "sp_materialCredited", "material_credited", true));
				if(in_array("sp_materialReplaced", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_materialReplaced`", "sp_materialReplaced", "material_replaced", true));
				if(in_array("vAnalysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`dateOfAnalysis`", "dateOfAnalysis", "date_of_analysis", true));
				if(in_array("sp_useGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_useGoods`", "sp_useGoods", "use_goods", true));
				if(in_array("sp_reworkGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_reworkGoods`", "sp_reworkGoods", "rework_goods", true));
				if(in_array("sp_sortGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`sp_sortGoods`", "sp_sortGoods", "sort_goods", true));
				if(in_array("containment_action", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "containment_action", true));
				if(in_array("implemented_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActions`", "implementedActions", "implemented_actions", true));
				if(in_array("implemented_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsAuthor`", "implementedActionsAuthor", "implemented_actions_author", true));
				if(in_array("implemented_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("management_system_reviewed", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewed`", "managementSystemReviewed", "management_system_reviewed", true));
				if(in_array("flowChart", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChart`", "flowChart", "flow_chart", true));
				if(in_array("fmea", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmea`", "fmea", "fmea", true));
				if(in_array("customerSpecification", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecification`", "customerSpecification", "customer_specification", true));
				if(in_array("additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`additionalComments`", "additionalComments", "additional_comments2", true));
				if(in_array("possible_solutions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutions`", "possibleSolutions", "possible_solutions", true));
				if(in_array("possible_solutions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsAuthor`", "possibleSolutionsAuthor", "possible_solutions_author", true));
				if(in_array("possible_solutions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`possibleSolutionsDate`", "possibleSolutionsDate", "possible_solutions_date", true));

				/*
				if(in_array("is_po_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isPORight`", "isPORight", "is_po_right", true));
				if(in_array("reason_for_rejection", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`reasonForRejection`", "reasonForRejection", "reason_for_rejection", true));
				if(in_array("is_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isSampleReceived`", "isSampleReceived", "is_sample_received", true));
				if(in_array("date_sample_received", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`dateSampleReceived`", "dateSampleReceived", "date_sample_received", true));
				if(in_array("team_member", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamMember`", "teamMember", "team_member", true));
				if(in_array("is_complaint_cat_right", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`isComplaintCatRight`", "isComplaintCatRight", "is_complaint_cat_right", true));
				if(in_array("correct_category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`correctCategory`", "correctCategory", "correct_category", true));
				if(in_array("failure_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`failureCode`", "failureCode", "failure_code", true));
				if(in_array("root_cause_code", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauseCode`", "rootCauseCode", "root_cause_code", true));
				if(in_array("attributable_process", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`attributableProcess`", "attributableProcess", "attributable_process", true));
				if(in_array("containment_action_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionAuthor`", "containmentActionAuthor", "containment_action_author", true));
				if(in_array("containment_action_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`containmentActionDate`", "containmentActionDate", "containment_action_date", true));
				if(in_array("implemented_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				if(in_array("implemented_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsImplementation`", "implementedActionsImplementation", "implemented_actions_implementation", true));
				if(in_array("preventive_actions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActions`", "preventiveActions", "preventive_actions", true));
				if(in_array("preventive_actions_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsAuthor`", "preventiveActionsAuthor", "preventive_actions_author", true));
				if(in_array("preventive_actions_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsDate`", "preventiveActionsDate", "preventive_actions_date", true));
				if(in_array("preventive_actions_estimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEstimated`", "preventiveActionsEstimated", "preventive_actions_estimated", true));
				if(in_array("preventive_actions_implementation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsImplementation`", "preventiveActionsImplementation", "preventive_actions_implementation", true));
				if(in_array("preventive_actions_effectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsEffectiveness`", "preventiveActionsEffectiveness", "preventive_actions_effectiveness", true));
				if(in_array("management_system_reviewed_ref", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewedRef`", "managementSystemReviewedRef", "management_system_reviewed_ref", true));
				if(in_array("management_system_reviewed_date", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`managementSystemReviewedDate`", "managementSystemReviewedDate", "management_system_reviewed_date", true));
				if(in_array("inspectionInstructions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructions`", "inspectionInstructions", "inspectionInstructions", true));
				if(in_array("inspectionInstructionsRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`inspectionInstructionsRef`", "inspectionInstructionsRef", "inspectionInstructionsRef", true));
				if(in_array("inspectionInstructionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`inspectionInstructionsDate`", "inspectionInstructionsDate", "inspectionInstructionsDate", true));
				if(in_array("fmeaRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmeaRef`", "fmeaRef", "fmea_ref", true));
				if(in_array("fmeaDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`fmeaDate`", "fmeaDate", "fmea_date", true));
				if(in_array("customerSpecificationRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecificationRef`", "customerSpecificationRef", "customer_specification_ref", true));
				if(in_array("customerSpecificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`customerSpecificationDate`", "customerSpecificationDate", "customer_specification_date", true));
				if(in_array("comments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`comments`", "comments", "comments", true));
				*/

				// Conclusion Table 24 fields now!


				if(in_array("customerDerongation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`customerDerongation`", "sp_customerDerongation", "customer_derongation", true));
				if(in_array("sp_customerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_customerName`", "sp_customerName", "customer_name", true));
				if(in_array("sp_requestDisposal", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestDisposal`", "sp_requestDisposal", "request_disposals", true));
				if(in_array("sp_sapItemNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_sapItemNumber`", "sp_sapItemNumber", "sap_item_number", true));
				if(in_array("sp_amount", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_amount_quantity`", "sp_amount_quantity", "disposal_amount_quantity", true));
					$results->addColumn(new column("conclusion.`sp_amount_measurement`", "sp_amount_measurement", "disposal_amount_measurement", true));
				}
				if(in_array("sp_value", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_value_quantity`", "sp_value_quantity", "disposal_value_quantity", true));
					$results->addColumn(new column("conclusion.`sp_value_measurement`", "sp_value_measurement", "disposal_value_measurement", true));
				}
				if(in_array("sp_requestEmailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestEmailText`", "sp_requestEmailText", "email_text_comment", true));
				if(in_array("processOwner3Request", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`processOwner3Request`", "processOwner3Request", "chosen_complaint_owner", true));
				if(in_array("sp_requestAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorised`", "sp_requestAuthorised", "request_authorised", true));
				if(in_array("sp_requestAuthorisorName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisorName`", "sp_requestAuthorisorName", "sp_requestAuthorisorName", true));
				if(in_array("sp_materialDisposed", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposed`", "sp_materialDisposed", "material_disposed", true));
				if(in_array("sp_materialDisposedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedName`", "sp_materialDisposedName", "material_disposed_name", true));
				if(in_array("sp_materialDisposedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedDate`", "sp_materialDisposedDate", "material_disposed_date", true));
				if(in_array("sp_materialDisposedCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialDisposedCode`", "sp_materialDisposedCode", "material_disposed_code", true));
				if(in_array("sp_materialReturned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturned`", "sp_materialReturned", "material_returned", true));
				if(in_array("sp_materialReturnedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturnedName`", "sp_materialReturnedName", "material_returned_name", true));
				if(in_array("sp_materialReturnedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_materialReturnedDate`", "sp_materialReturnedDate", "material_returned_date", true));
				if(in_array("sp_sapReturnNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_sapReturnNumber`", "sp_sapReturnNumber", "sap_return_number", true));
				if(in_array("sp_supplierCreditNoteRec", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierCreditNoteRec`", "sp_supplierCreditNoteRec", "supplier_credit_note_received", true));
				if(in_array("sp_supplierCreditNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierCreditNumber`", "sp_supplierCreditNumber", "supplier_credit_number", true));
				if(in_array("sp_supplierCreditNumber", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`sp_supplierCreditNumber_quantity`", "sp_supplierCreditNumber_quantity", "sp_supplierCreditNumber_value", true));
					$results->addColumn(new column("conclusion.`sp_supplierCreditNumber_measurement`", "sp_supplierCreditNumber_measurement", "sp_supplierCreditNumber_measurement", true));
				}
				if(in_array("sp_comment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_comment`", "sp_comment", "comment", true));
				if(in_array("sp_supplierReplacementRec", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_supplierReplacementRec`", "sp_supplierReplacementRec", "supplier_replacement_received", true));
				if(in_array("sp_finalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_finalComments`", "sp_finalComments", "final_comment", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("processOwner3", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`processOwner3`", "processOwner3", "chosen_complaint_owner", true));

				// Fields from Internal Complaint to Supplier Complaint
				if(in_array("internal_teamLeader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_teamLeader`", "internal_teamLeader", "internal_team_leader", true));
				if(in_array("internal_teamMember", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_teamMember`", "internal_teamMember", "internal_team_member", true));
				if(in_array("internal_qu_stockVerificationMade", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_stockVerificationMade`", "internal_qu_stockVerificationMade", "internal_qu_stock_verification_made", true));
				if(in_array("internal_qu_stockVerificationName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_stockVerificationName`", "internal_qu_stockVerificationName", "internal_qu_stock_verification_name", true));
				if(in_array("internal_qu_stockVerificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`internal_qu_stockVerificationDate`", "internal_qu_stockVerificationDate", "internal_qu_stock_verification_date", true));
				if(in_array("internal_qu_otherMaterialEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_otherMaterialEffected`", "internal_qu_otherMaterialEffected", "internal_qu_other_material_effected", true));
				if(in_array("internal_qu_otherMatDetails", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_qu_otherMatDetails`", "internal_qu_otherMatDetails", "internal_qu_other_mat_details", true));
				if(in_array("internal_analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_analysis`", "internal_analysis", "internal_analysis", true));
				if(in_array("internal_author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_author`", "internal_author", "internal_author", true));
				if(in_array("internal_analysisDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`internal_analysisDate`", "internal_analysisDate", "internal_analysis_date", true));
				if(in_array("internal_additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internal_additionalComments`", "internal_additionalComments", "internal_additional_comments", true));


				/*
				if(in_array("sp_requestAuthorisedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisedDate`", "sp_requestAuthorisedDate", "request_authorised_date", true));
				if(in_array("sp_requestAuthorisedEmailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`sp_requestAuthorisedEmailText`", "sp_requestAuthorisedEmailText", "email_text", true));
				*/				break;

			case 'customQuality':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON evaluation.complaintId=conclusion.complaintId WHERE complaint.typeOfComplaint = 'quality_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				/* 121 fields all together */
				/* Complaints database - 25 Fields */


				if(in_array("owner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "owner", true));
				if(in_array("salesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				if(in_array("groupAComplaint", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`groupAComplaint`", "groupAComplaint", "group_a_complaint", true));
				if(in_array("groupedComplaintId", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`groupedComplaintId`", "groupedComplaintId", "grouped_complaint_id", true));
				if(in_array("whereErrorOccured", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`whereErrorOccured`", "whereErrorOccured", "whereErrorOccured", true));
				if(in_array("sp_siteConcerned", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));
				if(in_array("others", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`others`", "others", "others", true));
				if(in_array("qu_foundBy", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_foundBy`", "qu_foundBy", "found_by", true));
				if(in_array("internalReferenceNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internalReferenceNumber`", "internalReferenceNumber", "internal_reference_number", true));
				if(in_array("clauseEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`clauseEffected`", "clauseEffected", "clause_effected", true));
				if(in_array("severity", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`severity`", "severity", "severity", true));
				if(in_array("customerComplaintDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`customerComplaintDate`", "customerComplaintDate", "created_date", true));
				if(in_array("how_was_error_detected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_was_error_detected", true));
				if(in_array("lineStoppage", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lineStoppage`", "lineStoppage", "line_stoppage", true));
				if(in_array("problem_description", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`problemDescription`", "problemDescription", "problem_description", true));
				if(in_array("containment_action", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "containment_action", true));
				if(in_array("requestedAction", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`requestedAction`", "requestedAction", "requested_action", true));
				if(in_array("materialInvolved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialInvolved`", "qu_materialInvolved", "material_involved", true));
				if(in_array("manufacturingNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`manufacturingNumber`", "manufacturingNumber", "manufacturing_number", true));
				if(in_array("dateOfManufacturing", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`dateOfManufacturing`", "dateOfManufacturing", "date_of_manufacturing", true));
				if(in_array("sapMaterialGroups", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));
				if(in_array("sapItemNumbers", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));
				if(in_array("batchNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`batchNumber`", "batchNumber", "batch_number", true));
				if(in_array("supplier_batch_number", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`supplierBatchNumber`", "batchNumber", "scapa_batch_number", true));
				if(in_array("lotNo", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`lotNo`", "lotNo", "lot_no", true));
				if(in_array("qu_materialLocation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialLocation`", "qu_materialLocation", "material_location", true));
				if(in_array("productDescription", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`productDescription`", "productDescription", "product_description", true));
				if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				if(in_array("dimensionThickness", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionThickness_quantity`", "dimensionThickness_quantity", "dimensionThickness", true));
					$results->addColumn(new column("complaint.`dimensionThickness_measurement`", "dimensionThickness_measurement", "dimensionThickness_measurement", true));
				}
				if(in_array("dimensionWidth", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionWidth_quantity`", "dimensionWidth_quantity", "dimensionWidth", true));
					$results->addColumn(new column("complaint.`dimensionWidth_measurement`", "dimensionWidth_measurement", "dimensionWidth_measurement", true));
				}
				if(in_array("dimensionLength", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`dimensionLength_quantity`", "dimensionLength_quantity", "dimensionLength", true));
					$results->addColumn(new column("complaint.`dimensionLength_measurement`", "dimensionLength_measurement", "dimensionLength_measurement", true));
				}
				if(in_array("colour", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`colour`", "colour", "colour", true));
				if(in_array("quantityUnderComplaint", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`quantityUnderComplaint_quantity`", "quantityUnderComplaint_quantity", "quantityUnderComplaint", true));
					$results->addColumn(new column("complaint.`quantityUnderComplaint_measurement`", "quantityUnderComplaint_measurement", "quantityUnderComplaint_measurement", true));
				}
				if(in_array("qu_complaintCosts", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`qu_complaintCosts_quantity`", "qu_complaintCosts_quantity", "qu_complaintCosts", true));
					$results->addColumn(new column("complaint.`qu_complaintCosts_measurement`", "qu_complaintCosts_measurement", "qu_complaintCosts_measurement", true));
				}
				if(in_array("qu_commentOnCost", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_commentOnCost`", "qu_commentOnCost", "comment_on_cost", true));
				if(in_array("qu_weightOfMaterial", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("complaint.`qu_weightOfMaterial_quantity`", "qu_weightOfMaterial_quantity", "qu_weightOfMaterial", true));
					$results->addColumn(new column("complaint.`qu_weightOfMaterial_measurement`", "qu_weightOfMaterial_measurement", "qu_weightOfMaterial_measurement", true));
				}
				if(in_array("qu_materialBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlocked`", "qu_materialBlocked", "material_blocked", true));
				if(in_array("qu_materialBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlockedDate`", "qu_materialBlockedDate", "material_blocked_date", true));
				if(in_array("qu_materialBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`qu_materialBlockedName`", "qu_materialBlockedName", "material_blocked_name", true));
				if(in_array("processOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "chosen_complaint_owner_complaint", true));

				if(in_array("implementedPermanentCorrectiveActionValidated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.implementedPermanentCorrectiveActionValidated", "implemented_permanent_corrective_action_validated", "implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`implementedPermanentCorrectiveActionValidatedyn`", "was_implemented_permanent_corrective_action_validated", "was_implemented_permanent_corrective_action_validated", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedPermanentCorrectiveActionValidatedDate`", "the_implemented_permanent_corrective_action_date", "the_implemented_permanent_corrective_action_date", true));
				if(in_array("implementedPermanentCorrectiveActionValidatedAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedPermanentCorrectiveActionValidatedAuthor`", "the_implemented_permanent_corrective_action_validated_author", "the_implemented_permanent_corrective_action_validated_author", true));


				/* Evaluation database - 69 fields! */

				if(in_array("teamLeader", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamLeader`", "teamLeader", "team_leader", true));
				if(in_array("teamMember", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`teamMember`", "teamMember", "team_member", true));
				if(in_array("qu_verificationMade", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_verificationMade`", "qu_verificationMade", "verification_made", true));
				if(in_array("qu_verificationName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_verificationName`", "qu_verificationName", "verification_name", true));
				if(in_array("qu_verificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`qu_verificationDate`", "qu_verificationDate", "verification_date", true));
				if(in_array("qu_otherMaterialEffected", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherMaterialEffected`", "qu_otherMaterialEffected", "other_material_effected", true));
				if(in_array("qu_otherMatDetails", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherMatDetails`", "qu_otherMatDetails", "other_material_details", true));
				if(in_array("analysisyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysisyn`", "analysisyn", "analysis_yes_no", true));
				if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`analysis`", "analysis", "analysis", true));
				if(in_array("author", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`author`", "author", "author", true));
				if(in_array("analysisDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`analysisDate`", "analysisDate", "analysis_date", true));
				if(in_array("qu_supplierIssue", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_supplierIssue`", "qu_supplierIssue", "supplier_issue", true));
				if(in_array("qu_supplierIssueAction", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_supplierIssueAction`", "qu_supplierIssueAction", "complaint_will_be_actioned", true));
				if(in_array("rootCauses", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauses`", "rootCauses", "root_causes", true));
				if(in_array("rootCausesyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesyn`", "rootCausesyn", "root_causes_yes_no", true));
				if(in_array("rootCausesAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCausesAuthor`", "rootCausesAuthor", "root_causes_author", true));
				if(in_array("rootCausesDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`rootCausesDate`", "rootCausesDate", "root_causes_date", true));
				if(in_array("attributableProcess", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`attributableProcess`", "attributableProcess", "attributable_process", true));
				if(in_array("failureCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`failureCode`", "failureCode", "failure_code", true));
				if(in_array("rootCauseCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`rootCauseCode`", "rootCauseCode", "root_cause_code", true));
				if(in_array("disposeGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`disposeGoods`", "disposeGoods", "dispose_goods", true));
				if(in_array("qu_useGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_useGoods`", "qu_useGoods", "use_goods", true));
				if(in_array("qu_useGoodsDerongation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_useGoodsDerongation`", "qu_useGoodsDerongation", "use_goods_derongation", true));
				if(in_array("qu_customerApproved", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_customerApproved`", "qu_customerApproved", "customer_approved", true));
				if(in_array("qu_nameOfCustomer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_nameOfCustomer`", "qu_nameOfCustomer", "name_of_customer", true));
				if(in_array("qu_reworkTheGoods", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_reworkGoods`", "qu_reworkGoods", "rework_the_goods", true));
				if(in_array("qu_otherSimilarProducts", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_otherSimilarProducts`", "qu_otherSimilarProducts", "other_similar_products_recalled", true));
				if(in_array("qu_authorGoodsDecision", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`qu_authorGoodsDecision`", "qu_authorGoodsDecision", "author_for_goods_decision", true));
				if(in_array("qu_authorGoodsDecisionDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`qu_authorGoodsDecisionDate`", "qu_authorGoodsDecisionDate", "author_for_goods_decision_date", true));
				if(in_array("containmentAction_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentAction`", "containmentAction", "eval_containment_action", true));
				if(in_array("containmentActionyn_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionyn`", "containmentActionyn", "eval_containment_action_yes_no", true));
				if(in_array("containmentActionAuthor_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`containmentActionAuthor`", "containmentActionAuthor", "eval_containment_action_author", true));
				if(in_array("containmentActionDate_eval", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`containmentActionDate`", "containmentActionDate", "eval_containment_action_date", true));
				if(in_array("possibleSolutions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutions`", "possibleSolutions", "possible_solutions", true));
				if(in_array("possibleSolutionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsyn`", "possibleSolutionsyn", "possible_solutions_yes_no", true));
				if(in_array("possibleSolutionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`possibleSolutionsAuthor`", "possibleSolutionsAuthor", "possible_solutions_author", true));
				if(in_array("possibleSolutionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`possibleSolutionsDate`", "possibleSolutionsDate", "possible_solutions_date", true));
				if(in_array("implementedActionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsyn`", "implementedActionsyn", "implemented_actions_yes_no", true));
				if(in_array("implementedActions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActions`", "implementedActions", "implemented_actions", true));
				if(in_array("implementedActionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`implementedActionsAuthor`", "implementedActionsAuthor", "implemented_actions_author", true));
				if(in_array("implementedActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				if(in_array("implementedActionsEstimated", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsEstimated`", "implementedActionsEstimated", "implemented_actions_estimated", true));
				if(in_array("implementedActionsEffectiveness", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsEffectiveness`", "implementedActionsEffectiveness", "implemented_actions_effectiveness", true));
				if(in_array("implementedActionsImplemetation", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsImplementation`", "implementedActionsImplemetantion", "implemented_actions_implementation", true));
				if(in_array("preventiveActions", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActions`", "preventiveActions", "preventive_actions", true));
				if(in_array("preventiveActionsyn", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsyn`", "preventiveActionsyn", "preventive_actions_yes_no", true));
				if(in_array("preventiveActionsAuthor", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`preventiveActionsAuthor`", "preventiveActionsAuthor", "preventive_actions_author", true));
				if(in_array("preventiveActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsDate`", "preventiveActionsDate", "preventive_actions_date", true));
				if(in_array("preventiveActionsEstimatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsEstimatedDate`", "preventiveActionsEstimatedDate", "preventive_actions_estimated_date", true));
				if(in_array("preventiveActionsImplementedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsImplementedDate`", "preventiveActionsImplementedDate", "preventive_actions_implemented_date", true));
				if(in_array("preventiveActionsValidationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`preventiveActionsValidationDate`", "preventiveActionsValidationDate", "preventive_actions_validation_date", true));
				if(in_array("riskAssessment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`riskAssessment`", "riskAssessment", "risk_assessment", true));
				if(in_array("riskAssessmentRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`riskAssessmentRef`", "riskAssessmentRef", "risk_assessment_date", true));
				if(in_array("riskAssessmentDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`riskAssessmentDate`", "riskAssessmentDate", "risk_assessment_ref", true));
				if(in_array("managementSystemReview", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewed`", "managementSystemReviewed", "management_system_review", true));
				if(in_array("managementSystemReviewDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`managementSystemReviewedDate`", "managementSystemReviewedDate", "management_system_review_date", true));
				if(in_array("managementSystemReviewRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`managementSystemReviewedRef`", "managementSystemedReviewRef", "management_system_review_ref", true));
				if(in_array("fmea", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmea`", "fmea", "fmea", true));
				if(in_array("fmeaDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`fmeaDate`", "fmeaDate", "fmea_date", true));
				if(in_array("fmeaRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`fmeaRef`", "fmeaRef", "fmea_ref", true));
				if(in_array("customerSpecification", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecification`", "customerSpecification", "customer_specification", true));
				if(in_array("customerSpecificationDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`customerSpecificationDate`", "customerSpecificationDate", "customer_specification_date", true));
				if(in_array("customerSpecificationRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`customerSpecificationRef`", "customerSpecificationRef", "customer_specification_ref", true));
				if(in_array("flowChart", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChart`", "flowChart", "flow_chart", true));
				if(in_array("flowChartDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`flowChartDate`", "flowChartDate", "flow_chart_date", true));
				if(in_array("flowChartRef", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`flowChartRef`", "flowChartRef", "flow_chart_ref", true));
				if(in_array("additionalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`additionalComments`", "additionalComments", "additional_comments_eval", true));
				if(in_array("processOwner2", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("evaluation.`processOwner2`", "processOwner2", "chosen_complaint_owner_evaluation", true));
				if(in_array("emailText", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("evaluation.`emailText`", "emailText", "email_text_evaluation", true));


				/* conclusion table - 27 fields! */
				if(in_array("qu_materialUnBlocked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialUnBlocked`", "qu_materialUnBlocked", "material_unblocked", true));
				if(in_array("qu_materialUnBlockedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialUnBlockedName`", "qu_materialUnBlockedName", "material_unblocked_name", true));
				if(in_array("qu_materialUnBlockedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_materialUnBlockedDate`", "qu_materialUnBlockedDate", "material_unblocked_date", true));
				if(in_array("qu_requestForDisposal", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_requestForDisposal`", "qu_requestForDisposal", "request_for_disposal", true));
				if(in_array("qu_requestForDisposalAmount", $this->selectedColumns) || $this->showAllCols)
				{
					$results->addColumn(new column("conclusion.`qu_amount_quantity`", "qu_amount_quantity", "request_for_disposal_amount", true));
					$results->addColumn(new column("conclusion.`qu_amount_measurement`", "qu_amount_measurement", "request_for_disposal_amount_measurement", true));
				}
				if(in_array("qu_requestForDisposalDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_requestDate`", "qu_requestDate", "request_for_disposal_date", true));
				if(in_array("qu_requestForDisposalName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_requestDisposalName`", "qu_requestDisposalName", "request_for_disposal_name", true));
				if(in_array("qu_disposalAuthorised", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorised`", "qu_disposalAuthorised", "disposal_authorised", true));
				if(in_array("qu_disposalAuthorisedComment", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorisedComment`", "qu_disposalAuthorisedComment", "disposal_authorised_comment", true));
				if(in_array("qu_disposalAuthorisedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalAuthorisedDate`", "qu_disposalAuthorisedDate", "disposal_authorised_date", true));
				if(in_array("qu_disposalAuthorisedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalAuthorisedName`", "qu_disposalAuthorisedName", "disposal_authorised_name", true));
				if(in_array("qu_disposalBooked", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalBooked`", "qu_disposalBooked", "disposal_booked", true));
				if(in_array("qu_disposalBookedName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalBookedName`", "qu_disposalBookedName", "disposal_booked_name", true));
				if(in_array("qu_disposalBookedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalBookedDate`", "qu_disposalBookedDate", "disposal_booked_date", true));
				if(in_array("qu_disposalCode", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalCode`", "qu_disposalCode", "disposal_code", true));
				if(in_array("qu_disposalCostCentre", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalCostCentre`", "qu_disposalCostCentre", "disposal_cost_centre", true));
				if(in_array("qu_disposalPhysicallyDone", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalPhysicallyDone`", "qu_disposalPhysicallyDone", "disposal_physically_done", true));
				if(in_array("qu_disposalPhysicallyDoneName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_disposalPhysicallyDoneName`", "qu_disposalPhysicallyDoneName", "disposal_physically_done_name", true));
				if(in_array("qu_disposalPhysicallyDoneDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_disposalPhysicallyDoneDate`", "qu_disposalPhysicallyDoneDate", "disposal_physically_done_date", true));
				if(in_array("qu_materialReturnedToCustomer", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialReturnedToCustomer`", "qu_materialReturnedToCustomer", "material_returned_to_customer", true));
				if(in_array("qu_materialReturnedToCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`qu_materialReturnedToCustomerName`", "qu_materialReturnedToCustomerName", "material_returned_to_customer_name", true));
				if(in_array("qu_materialReturnedToCustomerDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`qu_materialReturnedToCustomerDate`", "qu_materialReturnedToCustomerDate", "material_returned_to_customer_date", true));
				if(in_array("finalComments", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`finalComments`", "finalComments", "final_comments", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("internalComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));
				if(in_array("totalClosureDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("conclusion.`totalClosureDate`", "totalClosureDate", "total_closure_date", true));
				if(in_array("processOwner3", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("conclusion.`processOwner3`", "processOwner3", "chosen_complaint_owner_conclusion", true));

				break;


			case 'default':

				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN sapItemNumber ON conclusion.complaintId=sapItemNumber.complaintId LEFT JOIN materialGroup ON sapItemNumber.complaintId=materialGroup.complaintId");
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN materialGroup ON conclusion.complaintId=materialGroup.complaintId");
				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId LEFT JOIN scapaInvoiceNumberDate ON complaint.id=scapaInvoiceNumberDate.complaintId WHERE typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//$results->addColumn(new complaintDateColumn("complaint.`automotiveCovisint`", "automotiveCovisint", "automotive_covisint", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("Category", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`category`", "category", "category", true));
				//$results->addColumn(new column("sapItemNumber.`sapItemNumber`", "sapItemNumber", "sap_item_number", true));
				//if(in_array("DispatchSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "dispatch_site", true));
				//if(in_array("ManufacturingSite", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("InternalSalesName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`internalSalesName`", "internalSalesName", "complaint_creator", true));
				//if(in_array("ProcessOwner", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new complaintOwnerColumn("complaint.`processOwner`", "processOwner", "process_owner", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("ComplaintValue", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));
				//if(in_array("ComplaintType", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));

				//$results->addColumn(new complaintTranslateColumn("evaluation.`complaintJustified`", "complaintJustified", "complaint_justified", true));

				//if(in_array("SAPCustomerNumber", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));
				//if(in_array("SAPCustomerName", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));

				//$results->addColumn(new complaintStatusColumn("conclusion.`customerComplaintStatus`", "customerComplaintStatus", "customer_complaint_status", true));
				//$results->addColumn(new complaintStatusColumn("conclusion.`internalComplaintStatus`", "internalComplaintStatus", "internal_complaint_status", true));

				//if(in_array("OverallComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));
				//if(in_array("OverallCustomerComplaintStatus", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));
				//if(in_array("ComplaintOwner", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;

			case 'defaultSupplier':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new originalStateColumn("complaint.`originalStateComplaint`", "originalStateComplaint", "original_state_complaint", true));

				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`sp_buyer`", "sp_buyer", "buyer", true));

				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));

				$results->addColumn(new column("complaint.`sp_sapSupplierNumber`", "sp_sapSupplierNumber", "sp_sapSupplierNumber", true));

				$results->addColumn(new column("complaint.`sp_sapSupplierName`", "sp_sapSupplierName", "sp_sapSupplierName", true));

				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_error_detected", true));

				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));

				//$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;

			case 'defaultQuality':

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'quality_complaint'");
				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`sp_siteConcerned`", "sp_siteConcerned", "site_concerned", true));

				$results->addColumn(new column("complaint.`whereErrorOccured`", "whereErrorOccured", "where_error_occured", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`sapItemNumbers`", "sapItemNumbers", "sap_item_numbers", true));

				$results->addColumn(new column("complaint.`sapMaterialGroups`", "sapMaterialGroups", "sap_material_groups", true));

				$results->addColumn(new column("complaint.`howErrorDetected`", "howErrorDetected", "how_error_detected", true));

				$results->addColumn(new complaintStatusColumn("complaint.`overallComplaintStatus`", "overallComplaintStatus", "internal_complaint_status", true));

				//$results->addColumn(new complaintStatusColumn("complaint.`overallCustomerComplaintStatus`", "overallCustomerComplaintStatus", "customer_complaint_status", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				break;

			case 'performance':

				$this->updatePerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				//$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				//$results->setBaseQuery("SELECT * FROM complaint WHERE typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "customer_complaint_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("ClosedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`closedDate`", "closedDate", "closed_date", true));
				//$results->addColumn(new column("complaint.`analysisDate`", "analysisDate", "analysis_date", true));
				//if(in_array("ImplementedActionsDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("evaluation.`implementedActionsDate`", "implementedActionsDate", "implemented_actions_date", true));
				$results->addColumn(new column("complaint.`complaintValue_quantity`", "complaintValue_quantity", "complaint_value_quantity", true));
				$results->addColumn(new column("complaint.`complaintValue_measurement`", "complaintValue_measurement", "complaint_value_measurement", true));
				$results->addColumn(new column("conclusion.`creditNoteValue_quantity`", "creditNoteValue_quantity", "credit_note_value", true));
				$results->addColumn(new column("conclusion.`creditNoteValue_measurement`", "creditNoteValue_measurement", "credit_note_measurement", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_ttl`", "performance_ttl", "reg_performance_ttl", true));
				$results->addColumn(new column("complaint.`performance_3d`", "performance_3d", "reg_performance_3d", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_5d`", "performance_5d", "reg_performance_5d", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8d`", "performance_8d", "reg_performance_8d", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_cco`", "performance_cco", "close_out_performance", true));

				$results->addColumn(new column("complaint.`performance_tco`", "performance_tco", "total_close_out_performance", true));

				break;

			case 'performance_summary':

				$this->updatePerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId WHERE complaint.typeOfComplaint = 'customer_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`analysisyn`", "analysisyn", "analysis", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_3d`", "performance_3d", "reg_performance_3d", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_5d`", "performance_5d", "reg_performance_5d", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8d`", "performance_8d", "reg_performance_8d", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_cco`", "performance_cco", "close_out_performance", true));

				break;

			case 'performanceSupplier':

				$this->updateSupplierPerformanceValues();

				$results->setBaseQuery("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId LEFT JOIN conclusion ON complaint.id=conclusion.complaintId WHERE complaint.typeOfComplaint = 'supplier_complaint'");
				$results->setOrderBy("complaint.id");
				//if(in_array("ID", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));
				//if(in_array("CreatedDate", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));
				//if(in_array("SalesOffice", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_supplier_number", true));
				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_supplier_name", true));

				$results->addColumn(new column("complaint.`sp_submitToExtSupplier`", "sp_submitToExtSupplier", "is_with_supplier", true));
				//if(in_array("OriginSiteError", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));
				//if(in_array("BusinessUnit", $this->selectedColumns) || $this->showAllCols)
				//$results->addColumn(new complaintTranslateColumn("complaint.`businessUnit`", "businessUnit", "business_unit", true));
				//if(in_array("analysis", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`analysisyn`", "analysisyn", "analysis", true));

				//Performance values for 3d, 5d, 8d, CCO
				//if(in_array("Performance3d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_3dSupplier`", "performance_3dSupplier", "performance_3d_supplier", true));
				//if(in_array("Performance5d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new externalStatus("complaint.`performance_3dSupplierStatus`", "performance_3dSupplierStatus", "performance_3d_supplier_status", true));
				//if(in_array("Performance8d", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new column("complaint.`performance_8dSupplier`", "performance_8dSupplier", "performance_8dSupplier", true));
				//if(in_array("Performancecco", $this->selectedColumns) || $this->showAllCols)
				$results->addColumn(new externalStatus("complaint.`performance_8dSupplierStatus`", "performance_8dSupplierStatus", "performance_8dSupplier_status", true));


				break;

			case 'all':

				$results->setBaseQuery("SELECT * FROM complaint WHERE overallCustomerComplaintStatus != 'Closed'");

				$results->setOrderBy("complaint.id");

				$results->addColumn(new complaintIDColumn("complaint.`id`", "id", "id", true));

				$results->addColumn(new complaintDateColumn("complaint.`openDate`", "openDate", "created_date", true));

				$results->addColumn(new column("complaint.`category`", "category", "category", true));

				$results->addColumn(new column("complaint.`despatchSite`", "despatchSite", "dispatch_site", true));

				$results->addColumn(new column("complaint.`manufacturingSite`", "manufacturingSite", "manufacturing_site", true));

				$results->addColumn(new column("complaint.`siteAtOrigin`", "siteAtOrigin", "origin_site_error", true));

				$results->addColumn(new column("complaint.`internalSalesName`", "internalSalesName", "complaint_creator", true));

				$results->addColumn(new column("complaint.`salesOffice`", "salesOffice", "sales_office", true));

				$results->addColumn(new column("complaint.`businessUnit`", "businessUnit", "business_unit", true));

				$results->addColumn(new column("complaint.`gbpComplaintValue_quantity`", "gbpComplaintValue_quantity", "gbpComplaintValue_quantity", true));

				$results->addColumn(new complaintTranslateColumn("complaint.`typeOfComplaint`", "typeOfComplaint", "complaint_type", true));

				$results->addColumn(new column("complaint.`sapCustomerNumber`", "sapCustomerNumber", "sap_customer_number", true));

				$results->addColumn(new column("complaint.`sapName`", "sapName", "sap_customer_name", true));

				$results->addColumn(new complaintOwnerColumn("complaint.`owner`", "owner", "complaint_owner", true));

				$results->addColumn(new complaintHowLongColumn("complaint.`id`", "id", "complaint_unchanged_for_days", true));

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

	public function updateSupplierPerformanceValues()
	{
		$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM complaintExternal");

		while ($row = mysql_fetch_array($dataset))
		{
			// 3D Calculation

			if($row['supplierTimerUpdated'] == "0000-00-00 00:00:00")
			{
				$row['supplierTimerUpdated'] = page::nowDateTimeForMysql();
			}

			$performance_3dSupplier = $this->datediff($row['supplierTimer'], $row['supplierTimerUpdated']);

			if($performance_3dSupplier > 0)
			{
				$performance_3dSupplier = $performance_3dSupplier;
			}
			elseif($performance_3dSupplier < 0 && $row['supplierTimerUpdated'] != "0000-00-00 00:00:00")
			{
				$performance_3dSupplier = $performance_3dSupplier;
			}
			else
			{
				$performance_3dSupplier = "0";
			}

			$performance_3dSupplierStatus = $row['supplierTimerStatus'];

			// 8D Calculation

			if($row['supplier8dTimerUpdated'] == "0000-00-00 00:00:00")
			{
				$row['supplierTimerUpdated'] = page::nowDateTimeForMysql();
			}

			$performance_8dSupplier = $this->datediff($row['supplier8dTimer'], $row['supplier8dTimerUpdated']);

			if($performance_8dSupplier > 0)
			{
				$performance_8dSupplier = $performance_8dSupplier;
			}
			elseif($performance_8dSupplier < 0 && $row['supplier8dTimerUpdated'] != "0000-00-00 00:00:00")
			{
				$performance_8dSupplier = $performance_8dSupplier;
			}
			else
			{
				$performance_8dSupplier = "0";
			}

			$performance_8dSupplierStatus = $row['supplier8dTimerStatus'];

			// Update Values

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_3dSupplier` = '" . $performance_3dSupplier . "', `performance_3dSupplierStatus` = '" . $performance_3dSupplierStatus . "', `performance_8dSupplier` = '" . $performance_8dSupplier . "', `performance_8dSupplierStatus` = '" . $performance_8dSupplierStatus . "' WHERE id = " . $row['id'] . "");

		}
	}

	public function updatePerformanceValues()
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint");

		while ($row = mysql_fetch_array($dataset))
		{

			$performance_ttl = $this->datediff($row['customerComplaintDate'], $row['openDate']);

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_ttl` = '" . $performance_ttl . "' WHERE id = " . $row['id'] . "");

		}

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint LEFT JOIN evaluation ON complaint.id=evaluation.complaintId");

		while ($row = mysql_fetch_array($dataset))
		{

			$performance_3d = $this->datediff($row['customerComplaintDate'], $row['containmentActionDate']);
			$performance_3d < -5 ? $performance_3d = "n/a" : $performance_3d;
			$performance_5d = $this->datediff($row['openDate'], $row['analysisDate']);
			$performance_5d < -5 ? $performance_5d = "n/a" : $performance_5d;
			$performance_8d = $this->datediff($row['openDate'], $row['implementedActionsDate']);
			$performance_8d < -5 ? $performance_8d = "n/a" : $performance_8d;

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_3d` = '" . $performance_3d . "' WHERE id = " . $row['id'] . "");
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_8d` = '" . $performance_8d . "' WHERE id = " . $row['id'] . "");
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_5d` = '" . $performance_5d . "' WHERE id = " . $row['id'] . "");

		}

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint");

		while ($row = mysql_fetch_array($dataset))
		{

			$performance_cco = $this->datediff($row['openDate'], $row['closedDate']);
			$performance_cco < -5 ? $performance_cco = "n/a" : $performance_cco;

			$performance_tco = $this->datediff($row['openDate'], $row['totalClosureDate']);
			$performance_tco < -5 ? $performance_tco = "n/a" : $performance_tco;

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE `complaint` SET `performance_cco` = '" . $performance_cco . "', `performance_tco` = '" . $performance_tco . "' WHERE id = " . $row['id'] . "");

		}
	}

	public function datediff($datefrom, $dateto)
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



	private function defineChooseReportForm() // CUSTOM REPORTS IN HERE
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
		array('value' => 'default', 'display' => 'Customer - Default'),
		array('value' => 'custom', 'display' => 'Customer - Custom'),
		array('value' => 'performance', 'display' => 'Customer - Performance'),
		array('value' => 'performance_summary', 'display' => 'Customer - Performance Summary'),
		array('value' => 'defaultSupplier', 'display' => 'Supplier - Default'),
		array('value' => 'customSupplier', 'display' => 'Supplier - Custom'),
		array('value' => 'performanceSupplier', 'display' => 'Supplier - Performance'),
		array('value' => 'defaultQuality', 'display' => 'Internal - Default'),
		array('value' => 'customQuality', 'display' => 'Internal - Custom'),
		array('value' => 'all', 'display' => 'All - Activity Log'),
		);

		//if (currentuser::getInstance()->hasPermission("admin") || currentuser::getInstance()->hasPermission("ijf_admin")) {
		//	$data[] = array('value' => 'analysis_material', 'display' => 'Analysis by Material Group');
		//	$data[] = array('value' => 'customer_survey', 'display' => 'Customer Survery reports');
		//	$data[] = array('value' => 'activity', 'display' => 'Activity report');
		//}

		$reportType->setArraySource($data);
		$reportType->setValue("default");
		$reportType->setRowTitle("report_type");
		$reportType->setPostBack("changeReportType");
		$default->add($reportType);

		$this->chooseReportForm->add($default);
	}


	private function defineAddFiltersForm() // CUSTOM REPORTS IN HERE
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

		if ($reportType == 'customQuality' || $reportType == 'defaultQuality')
		{
			$implementedPermanentCorrectiveActionValidatedyn = new filterCombo("implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setField("complaint.implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setSQLSource("complaints","SELECT DISTINCT implementedPermanentCorrectiveActionValidatedyn AS name, implementedPermanentCorrectiveActionValidatedyn AS data FROM complaint ORDER BY name ASC");
			$implementedPermanentCorrectiveActionValidatedyn->setRowTitle("was_implemented_permanent_corrective_action_validated");
			$this->selectedFilters->add($implementedPermanentCorrectiveActionValidatedyn);

			$complaintLocation = new filterCombo("complaintLocation");
			$complaintLocation->setField("complaint.complaintLocation");
			$complaintLocation->setSQLSource("complaints","SELECT DISTINCT complaintLocation AS name, complaintLocation AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$complaintLocation->setRowTitle("complaint_location");
			$this->selectedFilters->add($complaintLocation);

			$analysisExists = new filterCombo("analysisExists");
			$analysisExists->setField("complaint.analysisyn");
			$analysisExists->setSQLSource("complaints","SELECT DISTINCT `analysisyn` AS name, `analysisyn` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint'");
			$analysisExists->setRowTitle("analysis_exists");
			$this->selectedFilters->add($analysisExists);

			$category = new filterComboSub("category");
			$category->setField("complaint.category");
			$category->setSQLSource("complaints","SELECT DISTINCT SUBSTRING(`category`,1,1) AS name, SUBSTRING(`category`,1,1) AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			//$category->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint ORDER BY name ASC");
			$category->setRowTitle("complaint_category");
			$this->selectedFilters->add($category);

			$internalSalesName = new filterCombo("internalSalesName");
			$internalSalesName->setField("complaint.internalSalesName");
			$internalSalesName->setSQLSource("complaints","SELECT DISTINCT internalSalesName AS name, internalSalesName AS data FROM complaint WHERE complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			//$internalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.internalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$internalSalesName->setRowTitle("complaint_creator");
			$this->selectedFilters->add($internalSalesName);

			$analysisPeriod = new filterDateRange("analysisPeriod");

			if ($reportType == 'activity')
			{
				$analysisPeriod->setRowType("row");
				$analysisPeriod->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("complaint.openDate");
			$analysisPeriod->setRowTitle("complaint_creation_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);

			$complaintId = new filterCombo("complaintId");
			$complaintId->setField("complaint.id");
			$complaintId->setSQLSource("complaints","SELECT id AS name, id AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$complaintId->setRowTitle("complaint_id");
			$this->selectedFilters->add($complaintId);

			$owner = new filterCombo("owner");
			$owner->setField("complaint.owner");
			$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.owner = membership.employee.ntlogon AND complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$owner->setRowTitle("complaint_owner");
			$this->selectedFilters->add($owner);

			$containmentAction = new filterCombo("containmentAction");
			$containmentAction->setField("complaint.containmentActionyn");
			$containmentAction->setSQLSource("complaints","SELECT DISTINCT `containmentActionyn` AS name, `containmentActionyn` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint'");
			$containmentAction->setRowTitle("containment_action_exists");
			$this->selectedFilters->add($containmentAction);

			$complaintStatus = new filterCombo("complaintStatus");
			$complaintStatus->setField("complaint.overallComplaintStatus");
			$complaintStatus->setSQLSource("complaints","SELECT DISTINCT `overallComplaintStatus` AS name, `overallComplaintStatus` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$complaintStatus->setRowTitle("complaint_status");
			$this->selectedFilters->add($complaintStatus);

			$failureCode = new filterCombo("failureCode");
			$failureCode->setField("evaluation.failureCode");
			$failureCode->setSQLSource("complaints","SELECT DISTINCT evaluation.`failureCode` AS name, evaluation.`failureCode` AS data FROM evaluation JOIN complaint ON complaint.`id` = evaluation.`complaintId` WHERE complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$failureCode->setRowTitle("failure_code");
			$this->selectedFilters->add($failureCode);

			$foundBy = new filterCombo("foundBy");
			$foundBy->setField("complaint.qu_foundBy");
			$foundBy->setSQLSource("complaints","SELECT DISTINCT complaint.`qu_foundBy` AS name, complaint.`qu_foundBy` AS data FROM  complaint WHERE complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$foundBy->setRowTitle("found_by");
			$this->selectedFilters->add($foundBy);

			$howErrorDetected = new filterCombo("howErrorDetected");
			$howErrorDetected->setField("complaint.howErrorDetected");
			$howErrorDetected->setSQLSource("complaints","SELECT DISTINCT howErrorDetected AS name, howErrorDetected AS data FROM complaint WHERE complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$howErrorDetected->setRowTitle("how_was_error_detected");
			$this->selectedFilters->add($howErrorDetected);

			$implementedActions = new filterCombo("implementedActions");
			$implementedActions->setField("complaint.implementedActionsyn");
			$implementedActions->setSQLSource("complaints","SELECT DISTINCT `implementedActionsyn` AS name, `implementedActionsyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'quality_complaint'");
			$implementedActions->setRowTitle("implemented_actions_exists");
			$this->selectedFilters->add($implementedActions);

			$qu_supplierIssueAction = new filterCombo("qu_supplierIssueAction");
			$qu_supplierIssueAction->setField("evaluation.qu_supplierIssueAction");
			$qu_supplierIssueAction->setSQLSource("complaints","SELECT DISTINCT `qu_supplierIssueAction` AS name, `qu_supplierIssueAction` AS data FROM evaluation RIGHT JOIN complaint ON complaint.id = evaluation.complaintId WHERE complaint.typeOfComplaint = 'quality_complaint'");
			$qu_supplierIssueAction->setRowTitle("internal_complaint_actioned");
			$this->selectedFilters->add($qu_supplierIssueAction);

			$lineStoppage = new filterCombo("lineStoppage");
			$lineStoppage->setField("complaint.lineStoppage");
			$lineStoppage->setSQLSource("complaints","SELECT DISTINCT lineStoppage AS name, lineStoppage AS data FROM complaint WHERE complaint.typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$lineStoppage->setRowTitle("line_stoppage");
			$this->selectedFilters->add($lineStoppage);

			$materialGroup = new filterComboLike("materialGroup");
			$materialGroup->setField("complaint.sapMaterialGroups");
			$materialGroup->setSQLSource("complaints","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM materialGroup ORDER BY name ASC");
			$materialGroup->setRowTitle("material_group");
			$this->selectedFilters->add($materialGroup);

			$qu_materialInvolved = new filterCombo("qu_materialInvolved");
			$qu_materialInvolved->setField("complaint.qu_materialInvolved");
			$qu_materialInvolved->setSQLSource("complaints","SELECT DISTINCT qu_materialInvolved AS name, qu_materialInvolved AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$qu_materialInvolved->setRowTitle("material_involved");
			$this->selectedFilters->add($qu_materialInvolved);

			$possibleSolutions = new filterCombo("possibleSolutions");
			$possibleSolutions->setField("complaint.possibleSolutionsyn");
			$possibleSolutions->setSQLSource("complaints","SELECT DISTINCT `possibleSolutionsyn` AS name, `possibleSolutionsyn` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint'");
			$possibleSolutions->setRowTitle("possible_solutions_exists");
			$this->selectedFilters->add($possibleSolutions);

			$preventiveAction = new filterCombo("preventiveAction");
			$preventiveAction->setField("complaint.preventiveActionsyn");
			$preventiveAction->setSQLSource("complaints","SELECT DISTINCT `preventiveActionsyn` AS name, `preventiveActionsyn` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint'");
			$preventiveAction->setRowTitle("preventive_actions_exists");
			$this->selectedFilters->add($preventiveAction);

			$rootCauseCode = new filterCombo("rootCauseCode");
			$rootCauseCode->setField("evaluation.rootCauseCode");
			$rootCauseCode->setSQLSource("complaints","SELECT DISTINCT `rootCauseCode` AS name, `rootCauseCode` AS data FROM evaluation ORDER BY name ASC");
			$rootCauseCode->setRowTitle("root_cause_code");
			$this->selectedFilters->add($rootCauseCode);

			$rootCauses = new filterCombo("rootCauses");
			$rootCauses->setField("complaint.rootCausesyn");
			$rootCauses->setSQLSource("complaints","SELECT DISTINCT `rootCausesyn` AS name, `rootCausesyn` AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint'");
			$rootCauses->setRowTitle("root_causes_exists");
			$this->selectedFilters->add($rootCauses);

			$salesOffice = new filterCombo("salesOffice");
			$salesOffice->setField("complaint.salesOffice");
			$salesOffice->setSQLSource("complaints","SELECT DISTINCT salesOffice AS name, salesOffice AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$salesOffice->setRowTitle("sales_office");
			$this->selectedFilters->add($salesOffice);

			$sapItemNumber = new filterTextfield("sapItemNumber");
			$sapItemNumber->setField("complaint.sapItemNumbers");
			//$sapItemNumber->setSQLSource("sapItemNumber","SELECT DISTINCT sapItemNumber AS name, sapItemNumber AS data FROM sapItemNumber ORDER BY name ASC");
			$sapItemNumber->setRowTitle("sap_item_number");
			//$this->selectedFilters->add($sapItemNumber);
			//$sapItemNumber = new filterTextfield("sapItemNumbers");
			//$sapItemNumber->setField("complaint.sapItemNumbers");
			$sapItemNumber->setUrl("/apps/complaints/ajax/sapItemNo?");
			//$sapItemNumber->setRowTitle("sap_item_number");
			$this->selectedFilters->add($sapItemNumber);

			$batchNumber = new filterCombo("batchNumber");
			$batchNumber->setField("complaint.batchNumber");
			$batchNumber->setSQLSource("complaints","SELECT DISTINCT batchNumber AS name, batchNumber AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$batchNumber->setRowTitle("scapa_batch_number");
			$this->selectedFilters->add($batchNumber);

			$supplierBatchNumber = new filterCombo("supplierBatchNumber");
			$supplierBatchNumber->setField("complaint.supplierBatchNumber");
			$supplierBatchNumber->setSQLSource("complaints","SELECT DISTINCT supplierBatchNumber AS name, supplierBatchNumber AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$supplierBatchNumber->setRowTitle("supplier_batch_number");
			$this->selectedFilters->add($supplierBatchNumber);

			$severity = new filterCombo("severity");
			$severity->setField("complaint.severity");
			$severity->setSQLSource("complaints","SELECT DISTINCT severity AS name, severity AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$severity->setRowTitle("severity");
			$this->selectedFilters->add($severity);

			$whereErrorOccured = new filterCombo("whereErrorOccured");
			$whereErrorOccured->setField("complaint.whereErrorOccured");
			$whereErrorOccured->setSQLSource("complaints","SELECT DISTINCT whereErrorOccured AS name, whereErrorOccured AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$whereErrorOccured->setRowTitle("where_error_occured");
			$this->selectedFilters->add($whereErrorOccured);

			$sp_siteConcerned = new filterCombo("sp_siteConcerned");
			$sp_siteConcerned->setField("complaint.sp_siteConcerned");
			$sp_siteConcerned->setSQLSource("complaints","SELECT DISTINCT sp_siteConcerned AS name, sp_siteConcerned AS data FROM complaint WHERE typeOfComplaint = 'quality_complaint' ORDER BY name ASC");
			$sp_siteConcerned->setRowTitle("site_concerned");
			$this->selectedFilters->add($sp_siteConcerned);


		}

		if ($reportType == 'customSupplier' || $reportType == 'defaultSupplier' || $reportType == 'performanceSupplier')
		{
			/* Added 19/12/2008 */
			$doesContainmentActionExist = new filterCombo("doesContainmentActionExist");
			$doesContainmentActionExist->setField("complaint.doesContainmentActionExist");
			$doesContainmentActionExist->setSQLSource("complaints","SELECT DISTINCT doesContainmentActionExist AS name, doesContainmentActionExist AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$doesContainmentActionExist->setRowTitle("does_containment_action_exist");
			$this->selectedFilters->add($doesContainmentActionExist);

			$does8DActionExist = new filterCombo("does8DActionExist");
			$does8DActionExist->setField("complaint.does8DActionExist");
			$does8DActionExist->setSQLSource("complaints","SELECT DISTINCT does8DActionExist AS name, does8DActionExist AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$does8DActionExist->setRowTitle("does_8d_action_exist");
			$this->selectedFilters->add($does8DActionExist);


			/* Added 06/10/2008 */
			$complaintLocation = new filterCombo("complaintLocation");
			$complaintLocation->setField("complaint.complaintLocation");
			$complaintLocation->setSQLSource("complaints","SELECT DISTINCT complaintLocation AS name, complaintLocation AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$complaintLocation->setRowTitle("complaint_location");
			$this->selectedFilters->add($complaintLocation);

			$internalSalesName = new filterCombo("internalSalesName");
			$internalSalesName->setField("complaint.internalSalesName");
			$internalSalesName->setSQLSource("complaints","SELECT DISTINCT internalSalesName AS name, internalSalesName AS data FROM complaint WHERE complaint.typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			//$internalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.internalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$internalSalesName->setRowTitle("complaint_creator");
			$this->selectedFilters->add($internalSalesName);

			$confirmCollectionOfGoods = new filterDateRange("confirmCollectionOfGoods");
			$confirmCollectionOfGoods->setField("evaluation.confirmCollectionOfGoods");
			//$confirmCollectionOfGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`confirmCollectionOfGoods` AS name, evaluation.`confirmCollectionOfGoods` AS data FROM evaluation INNER JOIN complaint ON complaint.`id` = evaluation.`complaintId` WHERE complaint.typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$confirmCollectionOfGoods->setRowTitle("goods_collection_date");
			$this->selectedFilters->add($confirmCollectionOfGoods);

			$returnGoods = new filterCombo("returnGoods");
			$returnGoods->setField("evaluation.returnGoods");
			$returnGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`returnGoods` AS name, evaluation.`returnGoods` AS data FROM evaluation ORDER BY name ASC");
			$returnGoods->setRowTitle("return_goods");
			$this->selectedFilters->add($returnGoods);

			$batchNumber = new filterCombo("batchNumber");
			$batchNumber->setField("complaint.batchNumber");
			$batchNumber->setSQLSource("complaints","SELECT DISTINCT batchNumber AS name, batchNumber AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$batchNumber->setRowTitle("scapa_batch_number");
			$this->selectedFilters->add($batchNumber);

			$supplierBatchNumber = new filterCombo("supplierBatchNumber");
			$supplierBatchNumber->setField("complaint.supplierBatchNumber");
			$supplierBatchNumber->setSQLSource("complaints","SELECT DISTINCT supplierBatchNumber AS name, supplierBatchNumber AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$supplierBatchNumber->setRowTitle("supplier_batch_number");
			$this->selectedFilters->add($supplierBatchNumber);

			$internalFields = new filterCombo("internalFields");
			$internalFields->setField("complaint.internal_fields");
			$internalFields->setSQLSource("complaints","SELECT DISTINCT internal_fields AS name, internal_fields AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$internalFields->setRowTitle("internal_fields_exist");
			$this->selectedFilters->add($internalFields);

			$disposeGoods = new filterCombo("disposeGoods");
			$disposeGoods->setField("evaluation.disposeGoods");
			$disposeGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`disposeGoods` AS name, evaluation.`disposeGoods` AS data FROM evaluation ORDER BY name ASC");
			$disposeGoods->setRowTitle("dispose_goods");
			$this->selectedFilters->add($disposeGoods);

			$sp_useGoods = new filterCombo("sp_useGoods");
			$sp_useGoods->setField("evaluation.sp_useGoods");
			$sp_useGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`sp_useGoods` AS name, evaluation.`sp_useGoods` AS data FROM evaluation ORDER BY name ASC");
			$sp_useGoods->setRowTitle("use_goods");
			$this->selectedFilters->add($sp_useGoods);

			$sp_reworkGoods = new filterCombo("sp_reworkGoods");
			$sp_reworkGoods->setField("evaluation.sp_reworkGoods");
			$sp_reworkGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`sp_reworkGoods` AS name, evaluation.`sp_reworkGoods` AS data FROM evaluation ORDER BY name ASC");
			$sp_reworkGoods->setRowTitle("rework_goods");
			$this->selectedFilters->add($sp_reworkGoods);

			$sp_sortGoods = new filterCombo("sp_sortGoods");
			$sp_sortGoods->setField("evaluation.sp_sortGoods");
			$sp_sortGoods->setSQLSource("complaints","SELECT DISTINCT evaluation.`sp_sortGoods` AS name, evaluation.`sp_sortGoods` AS data FROM evaluation ORDER BY name ASC");
			$sp_sortGoods->setRowTitle("sort_goods");
			$this->selectedFilters->add($sp_sortGoods);

			/* To here */



			$analysisExists = new filterCombo("analysisExists");
			$analysisExists->setField("complaint.analysisyn");
			$analysisExists->setSQLSource("complaints","SELECT DISTINCT `analysisyn` AS name, `analysisyn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$analysisExists->setRowTitle("analysis_exists");
			$this->selectedFilters->add($analysisExists);

			$category = new filterComboSub("category");
			$category->setField("complaint.category");
			$category->setSQLSource("complaints","SELECT DISTINCT SUBSTRING(`category`,1,1) AS name, SUBSTRING(`category`,1,1) AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			//$category->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint ORDER BY name ASC");
			$category->setRowTitle("complaint_category");
			$this->selectedFilters->add($category);

			$analysisPeriod = new filterDateRange("analysisPeriod");

			if ($reportType == 'activity')
			{
				$analysisPeriod->setRowType("row");
				$analysisPeriod->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("complaint.openDate");
			$analysisPeriod->setRowTitle("complaint_creation_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);

			$complaintId = new filterCombo("complaintId");
			$complaintId->setField("complaint.id");
			$complaintId->setSQLSource("complaints","SELECT id AS name, id AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$complaintId->setRowTitle("complaint_id");
			$this->selectedFilters->add($complaintId);

			$complaintJustified = new filterCombo("complaintJustified");
			$complaintJustified->setField("evaluation.complaintJustified");
			$complaintJustified->setSQLSource("complaints","SELECT DISTINCT evaluation.complaintJustified AS name, evaluation.complaintJustified AS data FROM evaluation ORDER BY name ASC");
			$complaintJustified->setRowTitle("complaint_justified");
			$this->selectedFilters->add($complaintJustified);


			$owner = new filterCombo("owner");
			$owner->setField("complaint.owner");
			$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.owner = membership.employee.ntlogon AND complaint.typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$owner->setRowTitle("complaint_owner");
			$this->selectedFilters->add($owner);

			$containmentActionSupplieryn = new filterCombo("containmentActionSupplieryn");
			$containmentActionSupplieryn->setField("complaint.containmentActionSupplieryn");
			$containmentActionSupplieryn->setSQLSource("complaints","SELECT DISTINCT `containmentActionSupplieryn` AS name, `containmentActionSupplieryn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$containmentActionSupplieryn->setRowTitle("containment_action_exists");
			$this->selectedFilters->add($containmentActionSupplieryn);

			/*
			$creditAmount = new filterCombo("creditAmount");
			$creditAmount->setField("conclusion.creditNoteValue_quantity");
			$creditAmount->setSQLSource("complaints","SELECT DISTINCT `creditNoteValue_quantity` AS name, `creditNoteValue_quantity` AS data FROM conclusion ORDER BY name ASC");
			$creditAmount->setRowTitle("credit_amount");
			$this->selectedFilters->add($creditAmount);
			*/

			$complaintStatus = new filterCombo("complaintStatus");
			$complaintStatus->setField("complaint.overallComplaintStatus");
			$complaintStatus->setSQLSource("complaints","SELECT DISTINCT `overallComplaintStatus` AS name, `overallComplaintStatus` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$complaintStatus->setRowTitle("supplier_complaint_status");
			$this->selectedFilters->add($complaintStatus);

			$groupedComplaint = new filterCombo("groupedComplaint");
			$groupedComplaint->setField("complaint.groupAComplaint");
			$groupedComplaint->setSQLSource("complaints","SELECT DISTINCT groupAComplaint AS name, groupAComplaint AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$groupedComplaint->setRowTitle("grouped_complaint");
			$this->selectedFilters->add($groupedComplaint);

			$sp_sampleSent = new filterCombo("sp_sampleSent");
			$sp_sampleSent->setField("complaint.sp_sampleSent");
			$sp_sampleSent->setSQLSource("complaints","SELECT DISTINCT `sp_sampleSent` AS name, `sp_sampleSent` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$sp_sampleSent->setRowTitle("sample_sent");
			$this->selectedFilters->add($sp_sampleSent);

			$howErrorDetected = new filterCombo("howErrorDetected");
			$howErrorDetected->setField("complaint.howErrorDetected");
			$howErrorDetected->setSQLSource("complaints","SELECT DISTINCT howErrorDetected AS name, howErrorDetected AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$howErrorDetected->setRowTitle("how_was_error_detected");
			$this->selectedFilters->add($howErrorDetected);

			$implementedActions = new filterCombo("implementedActions");
			$implementedActions->setField("complaint.implementedActionsyn");
			$implementedActions->setSQLSource("complaints","SELECT DISTINCT `implementedActionsyn` AS name, `implementedActionsyn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$implementedActions->setRowTitle("implemented_actions_exists");
			$this->selectedFilters->add($implementedActions);

			$sp_buyer = new filterCombo("sp_buyer");
			$sp_buyer->setField("complaint.sp_buyer");
			$sp_buyer->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.sp_buyer = membership.employee.ntlogon AND complaint.typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			//$sp_buyer->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.internalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$sp_buyer->setRowTitle("buyer_name");
			$this->selectedFilters->add($sp_buyer);

			$sp_submitToExtSupplier = new filterCombo("sp_submitToExtSupplier");
			$sp_submitToExtSupplier->setField("complaint.sp_submitToExtSupplier");
			$sp_submitToExtSupplier->setSQLSource("complaints","SELECT DISTINCT `sp_submitToExtSupplier` AS name, `sp_submitToExtSupplier` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$sp_submitToExtSupplier->setRowTitle("is_with_supplier");
			$this->selectedFilters->add($sp_submitToExtSupplier);




			//			$sp_buyer = new filterTextfield("sp_buyer");
			//			$sp_buyer->setField("complaint.sp_buyer");
			//			$sp_buyer->setRowTitle("buyer_name");
			//			$sp_buyer->setUrl("/apps/complaints/ajax/spBuyer?");
			//			$this->selectedFilters->add($sp_buyer);
			//
			/*
			$lineStoppage = new filterCombo("lineStoppage");
			$lineStoppage->setField("complaint.lineStoppage");
			$lineStoppage->setSQLSource("complaints","SELECT DISTINCT lineStoppage AS name, lineStoppage AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$lineStoppage->setRowTitle("line_stoppage");
			$this->selectedFilters->add($lineStoppage);

			$manufacturingSite = new filterCombo("manufacturingSite");
			$manufacturingSite->setField("complaint.manufacturingSite");
			$manufacturingSite->setSQLSource("complaints","SELECT DISTINCT manufacturingSite AS name, manufacturingSite AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);
			*/

			$materialGroup = new filterComboLike("materialGroup");
			$materialGroup->setField("complaint.sapMaterialGroups");
			$materialGroup->setSQLSource("complaints","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM materialGroup ORDER BY name ASC");
			$materialGroup->setRowTitle("material_group");
			$this->selectedFilters->add($materialGroup);

			$possibleSolutions = new filterCombo("possibleSolutions");
			$possibleSolutions->setField("complaint.possibleSolutionsyn");
			$possibleSolutions->setSQLSource("complaints","SELECT DISTINCT `possibleSolutionsyn` AS name, `possibleSolutionsyn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$possibleSolutions->setRowTitle("possible_solutions_exists");
			$this->selectedFilters->add($possibleSolutions);

			$preventiveAction = new filterCombo("preventiveAction");
			$preventiveAction->setField("complaint.preventiveActionsyn");
			$preventiveAction->setSQLSource("complaints","SELECT DISTINCT `preventiveActionsyn` AS name, `preventiveActionsyn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$preventiveAction->setRowTitle("preventive_actions_exists");
			$this->selectedFilters->add($preventiveAction);

			$rootCauses = new filterCombo("rootCauses");
			$rootCauses->setField("complaint.rootCausesyn");
			$rootCauses->setSQLSource("complaints","SELECT DISTINCT `rootCausesyn` AS name, `rootCausesyn` AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint'");
			$rootCauses->setRowTitle("root_causes_exists");
			$this->selectedFilters->add($rootCauses);

			$salesOffice = new filterCombo("salesOffice");
			$salesOffice->setField("complaint.salesOffice");
			$salesOffice->setSQLSource("complaints","SELECT DISTINCT salesOffice AS name, salesOffice AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$salesOffice->setRowTitle("sales_office");
			$this->selectedFilters->add($salesOffice);

			$sapSupplierName = new filterSAPName("sapSupplierName");
			$sapSupplierName->setField("complaint.sp_sapSupplierName");
			$sapSupplierName->setRowTitle("sap_supplier_name");
			$sapSupplierName->setUrl("/apps/complaints/ajax/sapSupplierName?");
			$this->selectedFilters->add($sapSupplierName);

			//$sp_sapSupplierNumber = new filterSAPNumber("sp_sapSupplierNumber");
			$sp_sapSupplierNumber = new filterCombo("sp_sapSupplierNumber");
			$sp_sapSupplierNumber->setField("complaint.sp_sapSupplierNumber");
			$sp_sapSupplierNumber->setRowTitle("sap_supplier_number");
			$sp_sapSupplierNumber->setSQLSource("complaints","SELECT DISTINCT sp_sapSupplierNumber AS name, sp_sapSupplierNumber AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			//$sp_sapSupplierNumber->setUrl("/apps/complaints/ajax/sapSupplierNo?");
			$this->selectedFilters->add($sp_sapSupplierNumber);

			$sapItemNumber = new filterTextfield("sapItemNumber");
			$sapItemNumber->setField("complaint.sapItemNumbers");
			//$sapItemNumber->setSQLSource("sapItemNumber","SELECT DISTINCT sapItemNumber AS name, sapItemNumber AS data FROM sapItemNumber ORDER BY name ASC");
			$sapItemNumber->setRowTitle("sap_item_number");
			//$this->selectedFilters->add($sapItemNumber);
			//$sapItemNumber = new filterTextfield("sapItemNumbers");
			//$sapItemNumber->setField("complaint.sapItemNumbers");
			$sapItemNumber->setUrl("/apps/complaints/ajax/sapItemNo?");
			//$sapItemNumber->setRowTitle("sap_item_number");
			$this->selectedFilters->add($sapItemNumber);

			/*
			$severity = new filterCombo("severity");
			$severity->setField("complaint.severity");
			$severity->setSQLSource("complaints","SELECT DISTINCT severity AS name, severity AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$severity->setRowTitle("severity");
			$this->selectedFilters->add($severity);

			$siteAtOrigin = new filterCombo("siteAtOrigin");
			$siteAtOrigin->setField("complaint.siteAtOrigin");
			$siteAtOrigin->setSQLSource("complaints","SELECT DISTINCT siteAtOrigin AS name, siteAtOrigin AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$siteAtOrigin->setRowTitle("site_at_origin");
			$this->selectedFilters->add($siteAtOrigin);
			*/

			$specificCategory = new filterCombo("specificCategory");
			$specificCategory->setField("complaint.category");
			$specificCategory->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$specificCategory->setRowTitle("specific_category");
			$this->selectedFilters->add($specificCategory);

			$sp_siteConcerned = new filterCombo("sp_siteConcerned");
			$sp_siteConcerned->setField("complaint.sp_siteConcerned");
			$sp_siteConcerned->setSQLSource("complaints","SELECT DISTINCT sp_siteConcerned AS name, sp_siteConcerned AS data FROM complaint WHERE typeOfComplaint = 'supplier_complaint' ORDER BY name ASC");
			$sp_siteConcerned->setRowTitle("site_concerned");
			$this->selectedFilters->add($sp_siteConcerned);

			$sp_supplierCreditNoteRec = new filterCombo("sp_supplierCreditNoteRec");
			$sp_supplierCreditNoteRec->setField("conclusion.sp_supplierCreditNoteRec");
			$sp_supplierCreditNoteRec->setSQLSource("conclusion","SELECT DISTINCT conclusion.sp_supplierCreditNoteRec AS name, conclusion.sp_supplierCreditNoteRec AS data FROM conclusion ORDER BY name ASC");
			$sp_supplierCreditNoteRec->setRowTitle("supplier_credit_note_received");
			$this->selectedFilters->add($sp_supplierCreditNoteRec);

			$sp_supplierReplacementRec = new filterCombo("sp_supplierReplacementRec");
			$sp_supplierReplacementRec->setField("conclusion.sp_supplierReplacementRec");
			$sp_supplierReplacementRec->setSQLSource("complaints","SELECT DISTINCT sp_supplierReplacementRec AS name, sp_supplierReplacementRec AS data FROM conclusion ORDER BY name ASC");
			$sp_supplierReplacementRec->setRowTitle("supplier_eplacement_received");
			$this->selectedFilters->add($sp_supplierReplacementRec);


		}

		if($reportType == 'all')
		{
			$implementedPermanentCorrectiveActionValidatedyn = new filterCombo("implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setField("complaint.implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setSQLSource("complaints","SELECT DISTINCT implementedPermanentCorrectiveActionValidatedyn AS name, implementedPermanentCorrectiveActionValidatedyn AS data FROM complaint ORDER BY name ASC");
			$implementedPermanentCorrectiveActionValidatedyn->setRowTitle("was_implemented_permanent_corrective_action_validated");
			$this->selectedFilters->add($implementedPermanentCorrectiveActionValidatedyn);

			$complaintLocation = new filterCombo("complaintLocation");
			$complaintLocation->setField("complaint.complaintLocation");
			$complaintLocation->setSQLSource("complaints","SELECT DISTINCT complaintLocation AS name, complaintLocation AS data FROM complaint ORDER BY name ASC");
			$complaintLocation->setRowTitle("complaint_location");
			$this->selectedFilters->add($complaintLocation);

			$analysisExists = new filterCombo("analysisExists");
			$analysisExists->setField("complaint.analysisyn");
			$analysisExists->setSQLSource("complaints","SELECT DISTINCT `analysisyn` AS name, `analysisyn` AS data FROM complaint");
			$analysisExists->setRowTitle("analysis_exists");
			$this->selectedFilters->add($analysisExists);

			$businessUnit = new filterCombo("businessUnit");
			$businessUnit->setField("complaint.businessUnit");
			$businessUnit->setSQLSource("complaints","SELECT DISTINCT businessUnit AS name, businessUnit AS data FROM complaint ORDER BY name ASC");
			$businessUnit->setRowTitle("business_unit");
			$this->selectedFilters->add($businessUnit);

			$category = new filterComboSub("category");
			$category->setField("complaint.category");
			$category->setSQLSource("complaints","SELECT DISTINCT SUBSTRING(`category`,1,1) AS name, SUBSTRING(`category`,1,1) AS data FROM complaint ORDER BY name ASC");
			//$category->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint ORDER BY name ASC");
			$category->setRowTitle("complaint_category");
			$this->selectedFilters->add($category);

			$analysisPeriod = new filterDateRange("analysisPeriod");

			if ($reportType == 'activity')
			{
				$analysisPeriod->setRowType("row");
				$analysisPeriod->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("complaint.openDate");
			$analysisPeriod->setRowTitle("complaint_creation_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);

			$complaintId = new filterCombo("complaintId");
			$complaintId->setField("complaint.id");
			$complaintId->setSQLSource("complaints","SELECT id AS name, id AS data FROM complaint ORDER BY name ASC");
			$complaintId->setRowTitle("complaint_id");
			$this->selectedFilters->add($complaintId);

			$owner = new filterCombo("owner");
			$owner->setField("complaint.owner");
			$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.owner = membership.employee.ntlogon ORDER BY name ASC");
			$owner->setRowTitle("complaint_owner");
			$this->selectedFilters->add($owner);

			$containmentAction = new filterCombo("containmentAction");
			$containmentAction->setField("complaint.containmentActionyn");
			$containmentAction->setSQLSource("complaints","SELECT DISTINCT `containmentActionyn` AS name, `containmentActionyn` AS data FROM complaint");
			$containmentAction->setRowTitle("containment_action_exists");
			$this->selectedFilters->add($containmentAction);

			$complaintStatus = new filterCombo("complaintStatus");
			$complaintStatus->setField("complaint.overallCustomerComplaintStatus");
			$complaintStatus->setSQLSource("complaints","SELECT DISTINCT `overallCustomerComplaintStatus` AS name, `overallCustomerComplaintStatus` AS data FROM complaint ORDER BY name ASC");
			$complaintStatus->setRowTitle("customer_complaint_status");
			$this->selectedFilters->add($complaintStatus);

			$despatchSite = new filterCombo("despatchSite");
			$despatchSite->setField("complaint.despatchSite");
			$despatchSite->setSQLSource("complaints","SELECT DISTINCT despatchSite AS name, despatchSite AS data FROM complaint ORDER BY name ASC");
			$despatchSite->setRowTitle("despatch_site");
			$this->selectedFilters->add($despatchSite);

			$externalSalesName = new filterCombo("externalSalesName");
			$externalSalesName->setField("complaint.externalSalesName");
			$externalSalesName->setSQLSource("complaints","SELECT DISTINCT externalSalesName AS name, externalSalesName AS data FROM complaint ORDER BY name ASC");
			//$externalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.externalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$externalSalesName->setRowTitle("external_sales_name");
			$this->selectedFilters->add($externalSalesName);

			$groupedComplaint = new filterCombo("groupedComplaint");
			$groupedComplaint->setField("complaint.groupAComplaint");
			$groupedComplaint->setSQLSource("complaints","SELECT DISTINCT groupAComplaint AS name, groupAComplaint AS data FROM complaint ORDER BY name ASC");
			$groupedComplaint->setRowTitle("grouped_complaint");
			$this->selectedFilters->add($groupedComplaint);

			$howErrorDetected = new filterCombo("howErrorDetected");
			$howErrorDetected->setField("complaint.howErrorDetected");
			$howErrorDetected->setSQLSource("complaints","SELECT DISTINCT howErrorDetected AS name, howErrorDetected AS data FROM complaint ORDER BY name ASC");
			$howErrorDetected->setRowTitle("how_was_error_detected");
			$this->selectedFilters->add($howErrorDetected);

			$implementedActions = new filterCombo("implementedActions");
			$implementedActions->setField("complaint.implementedActionsyn");
			$implementedActions->setSQLSource("complaints","SELECT DISTINCT `implementedActionsyn` AS name, `implementedActionsyn` AS data FROM complaint");
			$implementedActions->setRowTitle("implemented_actions_exists");
			$this->selectedFilters->add($implementedActions);

			$internalComplaintStatus = new filterCombo("overallComplaintStatus");
			$internalComplaintStatus->setField("complaint.overallComplaintStatus");
			$internalComplaintStatus->setSQLSource("complaints","SELECT DISTINCT overallComplaintStatus AS name, overallComplaintStatus AS data FROM complaint ORDER BY name ASC");
			$internalComplaintStatus->setRowTitle("internal_complaint_status");
			$this->selectedFilters->add($internalComplaintStatus);

			$internalSalesName = new filterCombo("internalSalesName");
			$internalSalesName->setField("complaint.internalSalesName");
			$internalSalesName->setSQLSource("complaints","SELECT DISTINCT internalSalesName AS name, internalSalesName AS data FROM complaint ORDER BY name ASC");
			//$internalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.internalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$internalSalesName->setRowTitle("complaint_creator");
			$this->selectedFilters->add($internalSalesName);

			$lineStoppage = new filterCombo("lineStoppage");
			$lineStoppage->setField("complaint.lineStoppage");
			$lineStoppage->setSQLSource("complaints","SELECT DISTINCT lineStoppage AS name, lineStoppage AS data FROM complaint ORDER BY name ASC");
			$lineStoppage->setRowTitle("line_stoppage");
			$this->selectedFilters->add($lineStoppage);

			$manufacturingSite = new filterCombo("manufacturingSite");
			$manufacturingSite->setField("complaint.manufacturingSite");
			$manufacturingSite->setSQLSource("complaints","SELECT DISTINCT manufacturingSite AS name, manufacturingSite AS data FROM complaint ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);

			//29/02/2008//changed to filterComboLike
			$materialGroup = new filterComboLike("materialGroup");
			$materialGroup->setField("complaint.sapMaterialGroups");
			$materialGroup->setSQLSource("complaints","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM materialGroup ORDER BY name ASC");
			$materialGroup->setRowTitle("material_group");
			$this->selectedFilters->add($materialGroup);

			$possibleSolutions = new filterCombo("possibleSolutions");
			$possibleSolutions->setField("complaint.possibleSolutionsyn");
			$possibleSolutions->setSQLSource("complaints","SELECT DISTINCT `possibleSolutionsyn` AS name, `possibleSolutionsyn` AS data FROM complaint");
			$possibleSolutions->setRowTitle("possible_solutions_exists");
			$this->selectedFilters->add($possibleSolutions);

			$preventiveAction = new filterCombo("preventiveAction");
			$preventiveAction->setField("complaint.preventiveActionsyn");
			$preventiveAction->setSQLSource("complaints","SELECT DISTINCT `preventiveActionsyn` AS name, `preventiveActionsyn` AS data FROM complaint");
			$preventiveAction->setRowTitle("preventive_actions_exists");
			$this->selectedFilters->add($preventiveAction);

			$rootCauses = new filterCombo("rootCauses");
			$rootCauses->setField("complaint.rootCausesyn");
			$rootCauses->setSQLSource("complaints","SELECT DISTINCT `rootCausesyn` AS name, `rootCausesyn` AS data FROM complaint");
			$rootCauses->setRowTitle("root_causes_exists");
			$this->selectedFilters->add($rootCauses);

			$salesOffice = new filterCombo("salesOffice");
			$salesOffice->setField("complaint.salesOffice");
			$salesOffice->setSQLSource("complaints","SELECT DISTINCT salesOffice AS name, salesOffice AS data FROM complaint ORDER BY name ASC");
			$salesOffice->setRowTitle("sales_office");
			$this->selectedFilters->add($salesOffice);

			$sapCustomerName = new filterSAPName("sapCustomerName");
			$sapCustomerName->setField("complaint.sapName");
			$sapCustomerName->setRowTitle("sap_customer_name");
			$sapCustomerName->setUrl("/apps/complaints/ajax/sapCustomerName?");
			$this->selectedFilters->add($sapCustomerName);

			$sapCustomerNumber = new filterSAPNumber("sapCustomerNumber");
			$sapCustomerNumber->setField("complaint.sapCustomerNumber");
			$sapCustomerNumber->setRowTitle("sap_customer_number");
			$sapCustomerNumber->setUrl("/apps/complaints/ajax/sapCustomerNo?");
			$this->selectedFilters->add($sapCustomerNumber);

			$sapItemNumber = new filterTextfield("sapItemNumber");
			$sapItemNumber->setField("complaint.sapItemNumbers");
			//$sapItemNumber->setSQLSource("sapItemNumber","SELECT DISTINCT sapItemNumber AS name, sapItemNumber AS data FROM sapItemNumber ORDER BY name ASC");
			$sapItemNumber->setRowTitle("sap_item_number");
			//$this->selectedFilters->add($sapItemNumber);
			//$sapItemNumber = new filterTextfield("sapItemNumbers");
			//$sapItemNumber->setField("complaint.sapItemNumbers");
			$sapItemNumber->setUrl("/apps/complaints/ajax/sapItemNo?");
			//$sapItemNumber->setRowTitle("sap_item_number");
			$this->selectedFilters->add($sapItemNumber);

			$severity = new filterCombo("severity");
			$severity->setField("complaint.severity");
			$severity->setSQLSource("complaints","SELECT DISTINCT severity AS name, severity AS data FROM complaint ORDER BY name ASC");
			$severity->setRowTitle("severity");
			$this->selectedFilters->add($severity);

			$siteAtOrigin = new filterCombo("siteAtOrigin");
			$siteAtOrigin->setField("complaint.siteAtOrigin");
			$siteAtOrigin->setSQLSource("complaints","SELECT DISTINCT siteAtOrigin AS name, siteAtOrigin AS data FROM complaint ORDER BY name ASC");
			$siteAtOrigin->setRowTitle("site_at_origin");
			$this->selectedFilters->add($siteAtOrigin);

			$specificCategory = new filterCombo("specificCategory");
			$specificCategory->setField("complaint.category");
			$specificCategory->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint ORDER BY name ASC");
			$specificCategory->setRowTitle("specific_category");
			$this->selectedFilters->add($specificCategory);


			$typeOfComplaint = new filterCombo("typeOfComplaint");
			$typeOfComplaint->setField("complaint.typeOfComplaint");
			$typeOfComplaint->setSQLSource("complaints","SELECT DISTINCT typeOfComplaint AS name, typeOfComplaint AS data FROM complaint ORDER BY name ASC");
			$typeOfComplaint->setRowTitle("type_of_complaint");
			$this->selectedFilters->add($typeOfComplaint);
		}

		if ($reportType == 'custom' || $reportType == 'default' || $reportType == 'performance' || $reportType == 'performance_summary')
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







			$implementedPermanentCorrectiveActionValidatedyn = new filterCombo("implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setField("complaint.implementedPermanentCorrectiveActionValidatedyn");
			$implementedPermanentCorrectiveActionValidatedyn->setSQLSource("complaints","SELECT DISTINCT implementedPermanentCorrectiveActionValidatedyn AS name, implementedPermanentCorrectiveActionValidatedyn AS data FROM complaint ORDER BY name ASC");
			$implementedPermanentCorrectiveActionValidatedyn->setRowTitle("was_implemented_permanent_corrective_action_validated");
			$this->selectedFilters->add($implementedPermanentCorrectiveActionValidatedyn);

			$complaintLocation = new filterCombo("complaintLocation");
			$complaintLocation->setField("complaint.complaintLocation");
			$complaintLocation->setSQLSource("complaints","SELECT DISTINCT complaintLocation AS name, complaintLocation AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$complaintLocation->setRowTitle("complaint_location");
			$this->selectedFilters->add($complaintLocation);

			$analysisExists = new filterCombo("analysisExists");
			$analysisExists->setField("complaint.analysisyn");
			$analysisExists->setSQLSource("complaints","SELECT DISTINCT `analysisyn` AS name, `analysisyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$analysisExists->setRowTitle("analysis_exists");
			$this->selectedFilters->add($analysisExists);

			$attributeProcess = new filterCombo("attributeProcess");
			$attributeProcess->setField("evaluation.attributableProcess");
			$attributeProcess->setSQLSource("complaints","SELECT DISTINCT `attributableProcess` AS name, `attributableProcess` AS data FROM evaluation ORDER BY name ASC");
			$attributeProcess->setRowTitle("attributable_process");
			$attributeProcess->setSelectSize(15);
			$this->selectedFilters->add($attributeProcess);

			$batchNumber = new filterCombo("batchNumber");
			$batchNumber->setField("complaint.batchNumber");
			$batchNumber->setSQLSource("complaints","SELECT DISTINCT batchNumber AS name, batchNumber AS data FROM complaint WHERE typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$batchNumber->setRowTitle("scapa_batch_number");
			$this->selectedFilters->add($batchNumber);

			$businessUnit = new filterCombo("businessUnit");
			$businessUnit->setField("complaint.businessUnit");
			$businessUnit->setSQLSource("complaints","SELECT DISTINCT businessUnit AS name, businessUnit AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$businessUnit->setRowTitle("business_unit");
			$this->selectedFilters->add($businessUnit);

			$category = new filterComboSub("category");
			$category->setField("complaint.category");
			$category->setSQLSource("complaints","SELECT DISTINCT SUBSTRING(`category`,1,1) AS name, SUBSTRING(`category`,1,1) AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			//$category->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint ORDER BY name ASC");
			$category->setRowTitle("complaint_category");
			$this->selectedFilters->add($category);

			$analysisPeriod = new filterDateRange("analysisPeriod");

			if ($reportType == 'activity')
			{
				$analysisPeriod->setRowType("row");
				$analysisPeriod->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("complaint.openDate");
			$analysisPeriod->setRowTitle("complaint_creation_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);

			$complaintId = new filterCombo("complaintId");
			$complaintId->setField("complaint.id");
			$complaintId->setSQLSource("complaints","SELECT id AS name, id AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$complaintId->setRowTitle("complaint_id");
			$this->selectedFilters->add($complaintId);

			$complaintJustified = new filterCombo("complaintJustified");
			$complaintJustified->setField("evaluation.complaintJustified");
			$complaintJustified->setSQLSource("complaints","SELECT DISTINCT complaintJustified AS name, complaintJustified AS data FROM evaluation ORDER BY name ASC");
			$complaintJustified->setRowTitle("complaint_justified");
			$this->selectedFilters->add($complaintJustified);

			//			$complaintJustified = new filterCombo("complaintJustified");
			//			$complaintJustified->setField("evaluation.complaintJustified");
			//			$complaintJustified->setSQLSource("complaints","SELECT DISTINCT `complaintJustified` AS name, `complaintJustified` AS data FROM evaluation");
			//			$complaintJustified->setRowTitle("complaint_justified");
			//			$this->selectedFilters->add($complaintJustified);


			$owner = new filterCombo("owner");
			$owner->setField("complaint.owner");
			$owner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.owner = membership.employee.ntlogon AND complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$owner->setRowTitle("complaint_owner");
			$owner->setSelectSize(15);
			$this->selectedFilters->add($owner);

			$containmentAction = new filterCombo("containmentAction");
			$containmentAction->setField("complaint.containmentActionyn");
			$containmentAction->setSQLSource("complaints","SELECT DISTINCT `containmentActionyn` AS name, `containmentActionyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$containmentAction->setRowTitle("containment_action_exists");
			$this->selectedFilters->add($containmentAction);

			//			$testField = new filterAmount("testField");
			//
			//			if ($reportType == 'activity')
			//			{
			//				$testField->setRowType("row");
			//				$testField->setVisible(true);
			//			}
			//
			//			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			//			$testField->setField("complaint.openDate");
			//			$testField->setRowTitle("credit_amount");
			//			$testField->setRequired(true);
			//			$this->selectedFilters->add($testField);

			$creditAmount = new filterCombo("creditAmount");
			$creditAmount->setField("conclusion.creditNoteValue_quantity");
			$creditAmount->setSQLSource("complaints","SELECT DISTINCT `creditNoteValue_quantity` AS name, `creditNoteValue_quantity` AS data FROM conclusion ORDER BY name ASC");
			$creditAmount->setRowTitle("credit_amount");
			$creditAmount->setSelectSize(15);
			$this->selectedFilters->add($creditAmount);

			$commercialAuthoriserAdvise = new filterCombo("commercialCreditAuthoriserAdvise");
			$commercialAuthoriserAdvise->setField("conclusion.commercialCreditAuthoriserAdvise");
			$commercialAuthoriserAdvise->setSQLSource("complaints","SELECT DISTINCT `commercialCreditAuthoriserAdvise` AS name, `commercialCreditAuthoriserAdvise` AS data FROM conclusion");
			$commercialAuthoriserAdvise->setRowTitle("commercial_credit_authoriser_advise");
			$commercialAuthoriserAdvise->setSelectSize(15);
			$this->selectedFilters->add($commercialAuthoriserAdvise);

			$commercialAuthoriser = new filterCombo("commercialAuthoriser");
			$commercialAuthoriser->setField("conclusion.commercialCreditAuthoriser");
			$commercialAuthoriser->setSQLSource("complaints","SELECT DISTINCT `commercialCreditAuthoriser` AS name, `commercialCreditAuthoriser` AS data FROM conclusion");
			$commercialAuthoriser->setRowTitle("commercial_credit_authoriser");
			$commercialAuthoriser->setSelectSize(15);
			$this->selectedFilters->add($commercialAuthoriser);

			$financeAuthoriser = new filterCombo("financeAuthoriser");
			$financeAuthoriser->setField("conclusion.financeCreditAuthoriser");
			$financeAuthoriser->setSQLSource("complaints","SELECT DISTINCT `financeCreditAuthoriser` AS name, `financeCreditAuthoriser` AS data FROM conclusion");
			$financeAuthoriser->setRowTitle("finance_credit_authoriser");
			$financeAuthoriser->setSelectSize(15);
			$this->selectedFilters->add($financeAuthoriser);

			$complaintStatus = new filterCombo("complaintStatus");
			$complaintStatus->setField("complaint.overallCustomerComplaintStatus");
			$complaintStatus->setSQLSource("complaints","SELECT DISTINCT `overallCustomerComplaintStatus` AS name, `overallCustomerComplaintStatus` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$complaintStatus->setRowTitle("customer_complaint_status");
			$this->selectedFilters->add($complaintStatus);

			$despatchSite = new filterCombo("despatchSite");
			$despatchSite->setField("complaint.despatchSite");
			$despatchSite->setSQLSource("complaints","SELECT DISTINCT despatchSite AS name, despatchSite AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$despatchSite->setRowTitle("despatch_site");
			$this->selectedFilters->add($despatchSite);

			$disposeGoods = new filterCombo("disposeGoods");
			$disposeGoods->setField("evaluation.disposeGoods");
			$disposeGoods->setSQLSource("complaints","SELECT DISTINCT disposeGoods AS name, disposeGoods AS data FROM evaluation ORDER BY name ASC");
			$disposeGoods->setRowTitle("dispose_goods");
			$this->selectedFilters->add($disposeGoods);

			$externalSalesName = new filterCombo("externalSalesName");
			$externalSalesName->setField("complaint.externalSalesName");
			$externalSalesName->setSQLSource("complaints","SELECT DISTINCT externalSalesName AS name, externalSalesName AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			//$externalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.externalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$externalSalesName->setRowTitle("external_sales_name");
			$externalSalesName->setSelectSize(15);
			$this->selectedFilters->add($externalSalesName);

			$failureCode = new filterCombo("failureCode");
			$failureCode->setField("evaluation.failureCode");
			$failureCode->setSQLSource("complaints","SELECT DISTINCT `failureCode` AS name, `failureCode` AS data FROM evaluation ORDER BY name ASC");
			$failureCode->setRowTitle("failure_code");
			$failureCode->setSelectSize(15);
			$this->selectedFilters->add($failureCode);

			$groupedComplaint = new filterCombo("groupedComplaint");
			$groupedComplaint->setField("complaint.groupAComplaint");
			$groupedComplaint->setSQLSource("complaints","SELECT DISTINCT groupAComplaint AS name, groupAComplaint AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$groupedComplaint->setRowTitle("grouped_complaint");
			$this->selectedFilters->add($groupedComplaint);

			$invoiceNumberDate = new filterCombo("invoiceNumberDate");
			$invoiceNumberDate->setField("scapaInvoiceNumberDate.scapaInvoiceNumber");
			$invoiceNumberDate->setSQLSource("complaints","SELECT DISTINCT `scapaInvoiceNumber` AS name, `scapaInvoiceNumber` AS data FROM scapaInvoiceNumberDate ORDER BY name ASC");
			$invoiceNumberDate->setRowTitle("invoice_number");
			$invoiceNumberDate->setSelectSize(15);
			$this->selectedFilters->add($invoiceNumberDate);

			$isSampleReceived = new filterCombo("isSampleReceived");
			$isSampleReceived->setField("evaluation.isSampleReceived");
			$isSampleReceived->setSQLSource("complaints","SELECT DISTINCT `isSampleReceived` AS name, `isSampleReceived` AS data FROM evaluation");
			$isSampleReceived->setRowTitle("is_sample_photo_received");
			$this->selectedFilters->add($isSampleReceived);

			$howErrorDetected = new filterCombo("howErrorDetected");
			$howErrorDetected->setField("complaint.howErrorDetected");
			$howErrorDetected->setSQLSource("complaints","SELECT DISTINCT howErrorDetected AS name, howErrorDetected AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$howErrorDetected->setRowTitle("how_was_error_detected");
			$this->selectedFilters->add($howErrorDetected);

			$implementedActions = new filterCombo("implementedActions");
			$implementedActions->setField("complaint.implementedActionsyn");
			$implementedActions->setSQLSource("complaints","SELECT DISTINCT `implementedActionsyn` AS name, `implementedActionsyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$implementedActions->setRowTitle("implemented_actions_exists");
			$this->selectedFilters->add($implementedActions);

			$internalComplaintStatus = new filterCombo("overallComplaintStatus");
			$internalComplaintStatus->setField("complaint.overallComplaintStatus");
			$internalComplaintStatus->setSQLSource("complaints","SELECT DISTINCT overallComplaintStatus AS name, overallComplaintStatus AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$internalComplaintStatus->setRowTitle("internal_complaint_status");
			$this->selectedFilters->add($internalComplaintStatus);

			$internalSalesName = new filterCombo("internalSalesName");
			$internalSalesName->setField("complaint.internalSalesName");
			$internalSalesName->setSQLSource("complaints","SELECT DISTINCT internalSalesName AS name, internalSalesName AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			//$internalSalesName->setSQLSource("complaints","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.internalSalesName = membership.employee.ntlogon ORDER BY name ASC");
			$internalSalesName->setRowTitle("complaint_creator");
			$internalSalesName->setSelectSize(15);
			$this->selectedFilters->add($internalSalesName);

			$lineStoppage = new filterCombo("lineStoppage");
			$lineStoppage->setField("complaint.lineStoppage");
			$lineStoppage->setSQLSource("complaints","SELECT DISTINCT lineStoppage AS name, lineStoppage AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$lineStoppage->setRowTitle("line_stoppage");
			$this->selectedFilters->add($lineStoppage);

			$manufacturingSite = new filterCombo("manufacturingSite");
			$manufacturingSite->setField("complaint.manufacturingSite");
			$manufacturingSite->setSQLSource("complaints","SELECT DISTINCT manufacturingSite AS name, manufacturingSite AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$manufacturingSite->setRowTitle("manufacturing_site");
			$this->selectedFilters->add($manufacturingSite);

			//29/02/2008//changed to filterComboLike
			$materialGroup = new filterComboLike("materialGroup");
			$materialGroup->setField("complaint.sapMaterialGroups");
			$materialGroup->setSQLSource("complaints","SELECT DISTINCT materialGroup AS name, materialGroup AS data FROM materialGroup ORDER BY name ASC");
			$materialGroup->setRowTitle("material_group");
			$this->selectedFilters->add($materialGroup);

			$possibleSolutions = new filterCombo("possibleSolutions");
			$possibleSolutions->setField("complaint.possibleSolutionsyn");
			$possibleSolutions->setSQLSource("complaints","SELECT DISTINCT `possibleSolutionsyn` AS name, `possibleSolutionsyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$possibleSolutions->setRowTitle("possible_solutions_exists");
			$this->selectedFilters->add($possibleSolutions);

			$preventiveAction = new filterCombo("preventiveAction");
			$preventiveAction->setField("complaint.preventiveActionsyn");
			$preventiveAction->setSQLSource("complaints","SELECT DISTINCT `preventiveActionsyn` AS name, `preventiveActionsyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$preventiveAction->setRowTitle("preventive_actions_exists");
			$this->selectedFilters->add($preventiveAction);

			$rootCauses = new filterCombo("rootCauses");
			$rootCauses->setField("complaint.rootCausesyn");
			$rootCauses->setSQLSource("complaints","SELECT DISTINCT `rootCausesyn` AS name, `rootCausesyn` AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint'");
			$rootCauses->setRowTitle("root_causes_exists");
			$this->selectedFilters->add($rootCauses);

			$rootCauseCode = new filterCombo("rootCauseCode");
			$rootCauseCode->setField("evaluation.rootCauseCode");
			$rootCauseCode->setSQLSource("complaints","SELECT DISTINCT `rootCauseCode` AS name, `rootCauseCode` AS data FROM evaluation ORDER BY name ASC");
			$rootCauseCode->setRowTitle("root_cause_code");
			$rootCauseCode->setSelectSize(15);
			$this->selectedFilters->add($rootCauseCode);

			$salesOffice = new filterCombo("salesOffice");
			$salesOffice->setField("complaint.salesOffice");
			$salesOffice->setSQLSource("complaints","SELECT DISTINCT salesOffice AS name, salesOffice AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$salesOffice->setRowTitle("sales_office");
			$this->selectedFilters->add($salesOffice);

			$sapCustomerName = new filterSAPName("sapCustomerName");
			$sapCustomerName->setField("complaint.sapName");
			$sapCustomerName->setRowTitle("sap_customer_name");
			$sapCustomerName->setUrl("/apps/complaints/ajax/sapCustomerName?");
			$this->selectedFilters->add($sapCustomerName);

			$sapCustomerNumber = new filterSAPNumber("sapCustomerNumber");
			$sapCustomerNumber->setField("complaint.sapCustomerNumber");
			$sapCustomerNumber->setRowTitle("sap_customer_number");
			$sapCustomerNumber->setUrl("/apps/complaints/ajax/sapCustomerNo?");
			$this->selectedFilters->add($sapCustomerNumber);

			$sapItemNumber = new filterTextfield("sapItemNumber");
			$sapItemNumber->setField("complaint.sapItemNumbers");
			//$sapItemNumber->setSQLSource("sapItemNumber","SELECT DISTINCT sapItemNumber AS name, sapItemNumber AS data FROM sapItemNumber ORDER BY name ASC");
			$sapItemNumber->setRowTitle("sap_item_number");
			//$this->selectedFilters->add($sapItemNumber);
			//$sapItemNumber = new filterTextfield("sapItemNumbers");
			//$sapItemNumber->setField("complaint.sapItemNumbers");
			$sapItemNumber->setUrl("/apps/complaints/ajax/sapItemNo?");
			//$sapItemNumber->setRowTitle("sap_item_number");
			$this->selectedFilters->add($sapItemNumber);

			$severity = new filterCombo("severity");
			$severity->setField("complaint.severity");
			$severity->setSQLSource("complaints","SELECT DISTINCT severity AS name, severity AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$severity->setRowTitle("severity");
			$this->selectedFilters->add($severity);

			$siteAtOrigin = new filterCombo("siteAtOrigin");
			$siteAtOrigin->setField("complaint.siteAtOrigin");
			$siteAtOrigin->setSQLSource("complaints","SELECT DISTINCT siteAtOrigin AS name, siteAtOrigin AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$siteAtOrigin->setRowTitle("site_at_origin");
			$this->selectedFilters->add($siteAtOrigin);

			$specificCategory = new filterCombo("specificCategory");
			$specificCategory->setField("complaint.category");
			$specificCategory->setSQLSource("complaints","SELECT DISTINCT category AS name, category AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$specificCategory->setRowTitle("specific_category");
			$specificCategory->setSelectSize(15);
			$this->selectedFilters->add($specificCategory);


			$typeOfComplaint = new filterCombo("typeOfComplaint");
			$typeOfComplaint->setField("complaint.typeOfComplaint");
			$typeOfComplaint->setSQLSource("complaints","SELECT DISTINCT typeOfComplaint AS name, typeOfComplaint AS data FROM complaint WHERE complaint.typeOfComplaint = 'customer_complaint' ORDER BY name ASC");
			$typeOfComplaint->setRowTitle("type_of_complaint");
			$this->selectedFilters->add($typeOfComplaint);

			/* North American Filters */

			$naLotNumber = new filterCombo("naLotNumber");
			$naLotNumber->setField("conclusion.naLotNumber");
			$naLotNumber->setSQLSource("complaints","SELECT DISTINCT naLotNumber AS name, naLotNumber AS data FROM conclusion ORDER BY name ASC");
			$naLotNumber->setRowTitle("na_lot_number");
			$naLotNumber->setSelectSize(15);
			$this->selectedFilters->add($naLotNumber);

			$naSizeReturned = new filterCombo("naSizeReturned");
			$naSizeReturned->setField("conclusion.naSizeReturned_quantity");
			$naSizeReturned->setSQLSource("complaints","SELECT DISTINCT naSizeReturned_quantity AS name, naSizeReturned_quantity AS data FROM conclusion ORDER BY name ASC");
			$naSizeReturned->setRowTitle("na_size_returned");
			$naSizeReturned->setSelectSize(15);
			$this->selectedFilters->add($naSizeReturned);

			$naCondition = new filterCombo("naCondition");
			$naCondition->setField("conclusion.naCondition");
			$naCondition->setSQLSource("complaints","SELECT DISTINCT naCondition AS name, naCondition AS data FROM conclusion ORDER BY name ASC");
			$naCondition->setRowTitle("na_condition");
			$naCondition->setSelectSize(15);
			$this->selectedFilters->add($naCondition);

			$NAreturnRequestValue = new filterCombo("NAreturnRequestValue");
			$NAreturnRequestValue->setField("evaluation.returnRequestValue_quantity");
			$NAreturnRequestValue->setSQLSource("complaints","SELECT DISTINCT returnRequestValue_quantity AS name, returnRequestValue_quantity AS data FROM evaluation ORDER BY name ASC");
			$NAreturnRequestValue->setRowTitle("na_return_request_value");
			$NAreturnRequestValue->setSelectSize(15);
			$this->selectedFilters->add($NAreturnRequestValue);

			$NAreturnRequestName = new filterCombo("NAreturnRequestName");
			$NAreturnRequestName->setField("evaluation.returnRequestName");
			$NAreturnRequestName->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.evaluation WHERE evaluation.returnRequestName = membership.employee.ntlogon ORDER BY name ASC");
			$NAreturnRequestName->setRowTitle("na_return_request_name");
			$this->selectedFilters->add($NAreturnRequestName);

			$NAreturnDisposalRequestName = new filterCombo("NAreturnDisposalRequestName");
			$NAreturnDisposalRequestName->setField("evaluation.returnDisposalRequestName");
			$NAreturnDisposalRequestName->setSQLSource("complaints", "SELECT DISTINCT returnDisposalRequestName AS name, returnDisposalRequestName AS data FROM evaluation ORDER BY name ASC");
			$NAreturnDisposalRequestName->setRowTitle("na_return_disposal_request_name");
			$this->selectedFilters->add($NAreturnDisposalRequestName);

			$NAreturnApprovalRequestName = new filterCombo("NAreturnApprovalRequestName");
			$NAreturnApprovalRequestName->setField("evaluation.returnApprovalRequestName");
			$NAreturnApprovalRequestName->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.evaluation WHERE evaluation.returnApprovalRequestName = membership.employee.ntlogon ORDER BY name ASC");
			$NAreturnApprovalRequestName->setRowTitle("na_return_approval_request_name");
			$this->selectedFilters->add($NAreturnApprovalRequestName);

			$NAreturnApprovalRequest = new filterCombo("NAreturnApprovalRequest");
			$NAreturnApprovalRequest->setField("evaluation.returnApprovalRequest");
			$NAreturnApprovalRequest->setSQLSource("complaints","SELECT DISTINCT returnApprovalRequest AS name, returnApprovalRequest AS data FROM evaluation ORDER BY name ASC");
			$NAreturnApprovalRequest->setRowTitle("na_return_approval_request");
			$this->selectedFilters->add($NAreturnApprovalRequest);

			$NAreturnApprovalDisposalValue = new filterCombo("NAreturnApprovalDisposalValue");
			$NAreturnApprovalDisposalValue->setField("evaluation.returnApprovalDisposalValue_quantity");
			$NAreturnApprovalDisposalValue->setSQLSource("complaints","SELECT DISTINCT returnApprovalDisposalValue_quantity AS name, returnApprovalDisposalValue_quantity AS data FROM evaluation ORDER BY name ASC");
			$NAreturnApprovalDisposalValue->setRowTitle("na_return_approval_disposal_value");
			$this->selectedFilters->add($NAreturnApprovalDisposalValue);

			$NAreturnApprovalDisposalRequestStatus = new filterCombo("NAreturnApprovalDisposalRequestStatus");
			$NAreturnApprovalDisposalRequestStatus->setField("evaluation.returnApprovalDisposalRequestStatus");
			$NAreturnApprovalDisposalRequestStatus->setSQLSource("complaints","SELECT DISTINCT returnApprovalDisposalRequestStatus AS name, returnApprovalDisposalRequestStatus AS data FROM evaluation ORDER BY name ASC");
			$NAreturnApprovalDisposalRequestStatus->setRowTitle("na_return_approval_disposal_request_status");
			$this->selectedFilters->add($NAreturnApprovalDisposalRequestStatus);

			$NAreturnApprovalDisposalRequest = new filterCombo("NAreturnApprovalDisposalRequest");
			$NAreturnApprovalDisposalRequest->setField("evaluation.returnApprovalDisposalRequest");
			$NAreturnApprovalDisposalRequest->setSQLSource("complaints","SELECT DISTINCT returnApprovalDisposalRequest AS name, returnApprovalDisposalRequest AS data FROM evaluation ORDER BY name ASC");
			$NAreturnApprovalDisposalRequest->setRowTitle("na_return_approval_disposal_request");
			$this->selectedFilters->add($NAreturnApprovalDisposalRequest);

			$NAreturnApprovalDisposalName = new filterCombo("NAreturnApprovalDisposalName");
			$NAreturnApprovalDisposalName->setField("evaluation.returnApprovalDisposalName");
			$NAreturnApprovalDisposalName->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.evaluation WHERE evaluation.returnApprovalDisposalName = membership.employee.ntlogon ORDER BY name ASC");
			$NAreturnApprovalDisposalName->setRowTitle("na_return_approval_disposal_name");
			$this->selectedFilters->add($NAreturnApprovalDisposalName);

			$NArequestForCredit = new filterCombo("NArequestForCredit");
			$NArequestForCredit->setField("conclusion.requestForCredit");
			$NArequestForCredit->setSQLSource("complaints","SELECT DISTINCT requestForCredit AS name, requestForCredit AS data FROM conclusion ORDER BY name ASC");
			$NArequestForCredit->setRowTitle("na_request_for_credit");
			$this->selectedFilters->add($NArequestForCredit);

			$NAfinanceStageCompleted = new filterCombo("NAfinanceStageCompleted");
			$NAfinanceStageCompleted->setField("conclusion.financeStageCompleted");
			$NAfinanceStageCompleted->setSQLSource("complaints","SELECT DISTINCT financeStageCompleted AS name, financeStageCompleted AS data FROM conclusion ORDER BY name ASC");
			$NAfinanceStageCompleted->setRowTitle("na_finance_stage_competed");
			$this->selectedFilters->add($NAfinanceStageCompleted);

			$NAfinanceLevelCreditAuthorised = new filterCombo("NAfinanceLevelCreditAuthorised");
			$NAfinanceLevelCreditAuthorised->setField("conclusion.financeLevelCreditAuthorised");
			$NAfinanceLevelCreditAuthorised->setSQLSource("complaints","SELECT DISTINCT financeLevelCreditAuthorised AS name, financeLevelCreditAuthorised AS data FROM conclusion ORDER BY name ASC");
			$NAfinanceLevelCreditAuthorised->setRowTitle("na_finance_level_credit_authorised");
			$this->selectedFilters->add($NAfinanceLevelCreditAuthorised);

			$NAfinanceCreditNewComplaintOwner = new filterCombo("NAfinanceCreditNewComplaintOwner");
			$NAfinanceCreditNewComplaintOwner->setField("conclusion.financeCreditNewComplaintOwner");
			$NAfinanceCreditNewComplaintOwner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.conclusion WHERE conclusion.financeCreditNewComplaintOwner = membership.employee.ntlogon ORDER BY name ASC");
			$NAfinanceCreditNewComplaintOwner->setRowTitle("na_finance_credit_new_complaint_owner");
			$NAfinanceCreditNewComplaintOwner->setSelectSize(15);
			$this->selectedFilters->add($NAfinanceCreditNewComplaintOwner);

			$NAccCommercialCredit = new filterCombo("NAccComercialCredit");
			$NAccCommercialCredit->setField("conclusion.ccCommercialCredit");
			$NAccCommercialCredit->setSQLSource("complaints","SELECT DISTINCT ccCommercialCredit AS name, ccCommercialCredit AS data FROM conclusion ORDER BY name ASC");
			$NAccCommercialCredit->setRowTitle("na_cc_commercial_credit");
			$NAccCommercialCredit->setSelectSize(15);
			$this->selectedFilters->add($NAccCommercialCredit);

			$NAcreditAuthorisationStatus = new filterCombo("NAcreditAuthorisationStatus");
			$NAcreditAuthorisationStatus->setField("conclusion.creditAuthorisationStatus");
			$NAcreditAuthorisationStatus->setSQLSource("complaints","SELECT DISTINCT creditAuthorisationStatus AS name, creditAuthorisationStatus AS data FROM conclusion ORDER BY name ASC");
			$NAcreditAuthorisationStatus->setRowTitle("na_credit_authorisation_status");
			$this->selectedFilters->add($NAcreditAuthorisationStatus);

			$NAfinanceCreditAuthoriser = new filterCombo("NAfinanceCreditAuthoriser");
			$NAfinanceCreditAuthoriser->setField("conclusion.financeCreditAuthoriser");
			$NAfinanceCreditAuthoriser->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.conclusion WHERE conclusion.financeCreditAuthoriser = membership.employee.ntlogon ORDER BY name ASC");
			$NAfinanceCreditAuthoriser->setRowTitle("na_finance_credit_authoriser");
			$NAfinanceCreditAuthoriser->setSelectSize(15);
			$this->selectedFilters->add($NAfinanceCreditAuthoriser);


			/* North American Filters end Here */




			//$sapCustomerNumber = new filterCombo("sapCustomerNumber");
			//$sapCustomerNumber->setField("complaint.sapCustomerNumber");
			//$sapCustomerNumber->setSQLSource("complaints","SELECT DISTINCT sapCustomerNumber AS name, sapCustomerNumber AS data FROM complaint ORDER BY name ASC");
			//$sapCustomerNumber->setRowTitle("sap_customer_number");
			//$this->selectedFilters->add($sapCustomerNumber);

			//$sapCustomerName = new filterCombo("sapCustomerName");
			//$sapCustomerName->setField("complaint.sapName");
			//$sapCustomerName->setSQLSource("complaints","SELECT DISTINCT sapName AS name, sapName AS data FROM complaint ORDER BY name ASC");
			//$sapCustomerName->setRowTitle("sap_customer_name");
			//$this->selectedFilters->add($sapCustomerName);



			//			$processOwner = new filterCombo("processOwner");
			//			$processOwner->setField("complaint.processOwner");
			//			$processOwner->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE complaint.processOwner = membership.employee.ntlogon ORDER BY name ASC");
			//			$processOwner->setRowTitle("process_owner");
			//			$this->selectedFilters->add($processOwner);



			/*
			$processOwner = new filterDateRange("processOwner");

			if ($reportType == 'activity')
			{
			$processOwner->setRowType("row");
			$processOwner->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, IJF.ijf WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$processOwner->setField("complaint.openDate");
			$processOwner->setRowTitle("complaint_creation_date");
			$processOwner->setRequired(true);
			$this->selectedFilters->add($processOwner);*/

			//no production site - manufacture site only
			//			$site = new filterCombo("site");
			//			$site->setField("complaint.site");
			//			$site->setSQLSource("complaints","SELECT DISTINCT site AS name, site AS data FROM complaint ORDER BY name ASC");
			//			$site->setRowTitle("production_site");
			//			$this->selectedFilters->add($site);


			//			$material_number = new filterCombo("material_number");
			//			$material_number->setField("complaint.material_number");
			//			$material_number->setSQLSource("complaints","SELECT DISTINCT material_number AS name, material_number AS data FROM complaint ORDER BY name ASC");
			//			$material_number->setRowTitle("material_number");
			//			$this->selectedFilters->add($material_number);


			//			$openDate = new filterCombo("openDate");
			//			$openDate->setField("complaint.openDate");
			//			$openDate->setSQLSource("complaints","SELECT DISTINCT openDate AS name, openDate AS data FROM complaint ORDER BY name ASC");
			//			$openDate->setRowTitle("open_date");
			//			$this->selectedFilters->add($openDate);


			/*$creditAuthoriser = new filterCombo("creditAuthoriser");
			$creditAuthoriser->setField("complaint.creditAuthoriser");
			$creditAuthoriser->setSQLSource("complaints","SELECT DISTINCT creditAuthoriser AS name, creditAuthoriser AS data FROM complaint ORDER BY name ASC");
			$creditAuthoriser->setRowTitle("business_unit");
			$this->selectedFilters->add($creditAuthoriser);*/






			//			$status = new filterCombo("status");
			//			$status->setField("complaint.status");
			//			$status->setSQLSource("complaints","SELECT DISTINCT status AS name, status AS data from complaint WHERE status != '' ORDER BY name ASC");
			//			$status->setRowTitle("status");
			//			$this->selectedFilters->add($status);

			//			$code = new filterCombo("code");
			//			$code->setField("complaint.code");
			//			$code->setSQLSource("complaints","SELECT DISTINCT `code` AS name, `code` AS data from complaint WHERE status != '' ORDER BY name ASC");
			//			$code->setRowTitle("complaint_code");
			//			$this->selectedFilters->add($code);














			//$manBarViewRequest = new filterCombo("manBarViewRequest");
			//$manBarViewRequest->setField("complaint.barManView");
			//$manBarViewRequest->setSQLSource("complaints","SELECT DISTINCT barManView AS name, barManView AS data FROM complaint ORDER BY name ASC");
			//$manBarViewRequest->setRowTitle("bar_man_view_request");
			//$this->selectedFilters->add($manBarViewRequest);

			/*
			$analysisPeriod = new filterDateRange("analysisPeriod");

			if ($reportType == 'activity')
			{
			$analysisPeriod->setRowType("row");
			$analysisPeriod->setVisible(true);
			}

			// $analysisPeriod->setSQLSource("membership","SELECT DISTINCT concat(firstName,' ',lastName) AS name, ntlogon AS data from membership.employee, complaints.complaint WHERE ijf.initiatorInfo = ntlogon ORDER BY name ASC");
			$analysisPeriod->setField("complaint.updatedDate");
			$analysisPeriod->setRowTitle("complaint_updated_date");
			$analysisPeriod->setRequired(true);
			$this->selectedFilters->add($analysisPeriod);
			*/

		}

		/*
		if ($reportType == "commercial_planner_view")
		{
		$resultsAcceptedRejected = new filterCombo("resultsAcceptedRejected");
		$resultsAcceptedRejected->setField("commercialPlanning.acceptedRejected");
		$resultsAcceptedRejected->setSQLSource("complaints","SELECT DISTINCT acceptedRejected AS name, acceptedRejected AS data FROM commercialPlanning ORDER BY name ASC");
		$resultsAcceptedRejected->setRowTitle("acceptedRejected");
		$this->selectedFilters->add($resultsAcceptedRejected);

		$testingRequired = new filterCombo("testingRequired");
		$testingRequired->setField("production.testingRequired");
		$testingRequired->setSQLSource("complaints","SELECT DISTINCT testingRequired AS name, testingRequired AS data from production WHERE testingRequired != '' ORDER BY name ASC");
		$testingRequired->setRowTitle("testing_required");
		$this->selectedFilters->add($testingRequired);
		}
		*/


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

class complaintOwnerColumn extends column
{
	public function getOutput($fields)
	{

		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= page::xmlentities(usercache::getInstance()->get($fields[$this->getName()])->getName());
		//$xml .= $fields[$this->getName()];
		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class complaintDateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";

		//		if($fields[$this->getName()] == "30/11/1999" || $fields[$this->getName()] == "0000-00-00")
		//		{
		//
		//			$xml .= "";
		//		}
		//		else
		//		{
		$xml .= page::transformDateForPHP($fields[$this->getName()]);
		//}
		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class complaintTranslateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";

		if($fields[$this->getName()] == "quality_complaint")
		{
			$xml .= translate::getInstance()->translate("internal_complaint");
		}
		else
		{
			$xml .= translate::getInstance()->translate($fields[$this->getName()]);
		}

		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class complaintIDColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/complaints/index?id=" . $fields[$this->getName()] . "\">" . $fields[$this->getName()] . "</link>";
		$xml .= "</searchColumn>";

		return $xml;
	}
}

class complaintMaterialColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
		$xml .= "<link url=\"/apps/complaints/view?material=" . $fields['materialId'] . "\">" . page::xmlentities($fields['materialKey']) . "</link>";
		$xml .= "</searchColumn>";

		return $xml;
	}
}
class complaintStatusColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= $fields[$this->getName()] == '' ? "Open" : $fields[$this->getName()];
		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class originalStateColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";
		$xml .= $fields[$this->getName()] != '' ? translate::getInstance()->translate("internal_quality_complaint") : translate::getInstance()->translate("supplier_complaint");
		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class externalStatus extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";

		if($fields[$this->getName()] == "0")
		{
			$xml .= "Open";
		}
		elseif($fields[$this->getName()] == "1")
		{
			$xml .= "Accepted";
		}
		else
		{
			$xml .= "Open";
		}

		$xml .= "</text></searchColumn>";

		return $xml;
	}
}

class complaintHowLongColumn extends column
{
	public function getOutput($fields)
	{
		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\"><text>\n";

		$datasetActionLog = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM actionLog WHERE complaintId = " . $fields[$this->getName()] . " ORDER BY actionId DESC LIMIT 1");
		$fieldsActionLog = mysql_fetch_array($datasetActionLog);

		$xml .= $this->datediff($fieldsActionLog['actionDate'], page::nowDateTimeForMysql());

		// $fields[$this->getName()] -> outputs the Complaint ID

		$xml .= "</text></searchColumn>";

		return $xml;
	}

	public function datediff($datefrom, $dateto)
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
}

//class complaintCategoryColumn extends column
//{
//	public function getOutput($fields)
//	{
//		$xml = "<searchColumn sortable=\"" . $this->getSortable() . "\">\n";
//		$xml .= "<link url=\"/apps/complaints/index?id=" . $fields[$this->getName()] . "\">" . $fields[substr($this->getName(), 1, 1)] . "</link>";
//		$xml .= "</searchColumn>";
//	}
//}

?>