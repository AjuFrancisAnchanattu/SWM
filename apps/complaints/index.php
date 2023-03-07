<?php
require 'lib/complaint.php';

/**
*
 * This is the Complaints Application.
 * This is the home page of Complaints.
 *
 * @package intranet
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */

class index extends page
{
	// Class Index
	private $complaint;

	function __construct()
	{

		if(isset($_REQUEST["delSavedForm"]) && isset($_REQUEST["sfID"])){
			$sfID = $_REQUEST["sfID"];
			mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM savedForms WHERE sfID = '$sfID' AND sfOwner = '".currentuser::getInstance()->getNTLogon()."' LIMIT 1");
			page::redirect("/apps/complaints/");
			exit;
		}

		parent::__construct();

		$this->setActivityLocation('Complaints');

		common::hitCounter($this->getActivityLocation());

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		$this->add_output("<complaintsHome>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for Complaints
//		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'addComplaint', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaints load snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the complaints report snapin in the page
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the complaints bookmarked snapin in the page
		$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccDocumentation', true, true);

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->checkEmailSent();

		$this->complaint = new complaint();		//creates an empty Complaint


		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// checks if a complaint id was passed

			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}

			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_REQUEST['id']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
				//$_POST['report'] = "";
			}

			unset($_SESSION['apps'][$GLOBALS['app']]);

			$this->xml .= "<complaints_report>";

			if(currentuser::getInstance()->hasPermission("complaints_admin"))
			{
				$this->xml .= "<complaintAdmin>true</complaintAdmin>";
			}

			//loads a report if a report id is set
			if ($this->complaint->load($_POST['report']))
			{
				$this->xml .= "<id>" . $this->complaint->getId() . "</id>\n";

				if($this->getComplaintType($_POST['report']) == "supplier_complaint")
				{
					$this->xml .= "<supplierId>" . $this->complaint->form->get("sp_sapSupplierNumber")->getValue() . "</supplierId>";
					$this->xml .= "<supplierLanguage>" . $this->complaint->form->get("supplierDefaultLanguage")->getValue() . "</supplierLanguage>";
				}

				$this->xml .= "<complaint_type>" . $this->complaint->form->get("typeOfComplaint")->getValue() . "</complaint_type>\n";

				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
				$this->xml .= "<admin>" . currentuser::getInstance()->isAdmin() . "</admin>\n";

				// Set Status Total
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = " . $_POST['report'] . "");
				$fields = mysql_fetch_array($dataset);
				$this->xml .= $fields['statusTotal'] == 1 ? "<statusTotal>true</statusTotal>" : "<statusTotal>false</statusTotal>";

				//loads the comments details for the Complaints
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaintComments WHERE complaintId='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");

				$this->xml .= "<complaintsComment>";

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<item2>";
					$this->xml .= "<id2>" . usercache::getInstance()->get($fields['id'])->getName() . "</id2>\n";
					$this->xml .= "<user2>" . usercache::getInstance()->get($fields['owner'])->getName() . "</user2>\n";
					$this->xml .= "<date2>" . common::transformDateForPHP($fields['logDate']) . "</date2>\n";
					$this->xml .= "<comment>" . $fields['description'] . "</comment>\n";

					$this->xml .= currentuser::getInstance()->getNTLogon() == $fields['owner'] ? "<editable>true</editable>" : "<editable>false</editable>";

					$this->xml .= "</item2>";
				}

				$this->xml .= "</complaintsComment>";

				//loads the external details for the Complaints

				if($this->complaint->form->get("typeOfComplaint")->getValue() == "supplier_complaint")
				{

					$datasetExt = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM complaintExternal WHERE id='" . $_POST['report'] . "'");
					$fieldsExt = mysql_fetch_array($datasetExt);

					// Determine whether or not to show the External Complaint in Summary Form
					$fieldsExt['extStatus'] == 1 ? $this->xml .= "<ext_complaint_updated>1</ext_complaint_updated>" : $this->xml .= "<ext_complaint_updated>0</ext_complaint_updated>";
					$fieldsExt['scapaStatus'] == 1 ? $this->xml .= "<scapa_complaint_updated>1</scapa_complaint_updated>" : $this->xml .= "<scapa_complaint_updated>0</scapa_complaint_updated>";
					$fieldsExt['added'] == 1 ? $this->xml .= "<ext_complaint_added>1</ext_complaint_added>" : $this->xml .= "<ext_complaint_added>0</ext_complaint_added>";

					$datasetHoldInvoice = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = " . $_POST['report'] . "");
					$fieldsHoldInvoice = mysql_fetch_array($datasetHoldInvoice);

					$this->xml .= "<holdDebitNote>" . $fieldsHoldInvoice['holdDebitNote'] . "</holdDebitNote>";

					$fieldsExt == 0 ? $this->xml .= "<ext_complaint_added>0</ext_complaint_added>" : "";

					$fieldsExt != 0 ? $this->xml .= "<complaints_timer>" . page::transformDateTimeForPHP($fieldsExt['supplierTimer']) . "</complaints_timer>\n" : "";

					$this->xml .= "<containmentActionAdded>" . $fieldsExt['containmentActionAdded'] . "</containmentActionAdded>";

					if($diff = page::getTimeDifference(page::nowDateTimeForMysql(), $fieldsExt['supplierTimer']))
					{
						$this->xml .= "<complaints_timer_hours_remaining>" . sprintf('%02d:%02d', $diff['hours'], $diff['minutes']) . "</complaints_timer_hours_remaining>\n";
					}
					else
					{
						$this->xml .= "<complaints_timer_hours_remaining>Overdue</complaints_timer_hours_remaining>\n";
					}

					//if($diff = page::getTimeDifference($fieldsExt['supplierTimerUpdated'], $fieldsExt['supplierTimer']))
					//{
						//$this->xml .= "<complaints_timer_hours_taken>" . sprintf('%02d:%02d', $diff['hours'], $diff['minutes']) . "</complaints_timer_hours_taken>\n";
					//}

					$this->xml .= "<complaintsExt>";

					while ($fieldsExt = mysql_fetch_array($datasetExt))
					{
						$this->xml .= "<complaints_ext>";
						$this->xml .= "<complaints_ext_od>" . $fieldsExt['openDate'] . "</complaints_ext_od>\n";
						$this->xml .= "<complaints_ext_ss>" . $fieldsExt['scapaStatus'] . "</complaints_ext_ss>\n";
						$this->xml .= "<complaints_ext_es>" . $fieldsExt['extStatus'] . "</complaints_ext_es>\n";
						$this->xml .= "<complaints_ext_scn>" . $fieldsExt['sapCustomerNumber'] . "</complaints_ext_scn>\n";
						$this->xml .= "</complaints_ext>";
					}

					$this->xml .= "</complaintsExt>";

					$datasetInternalFields = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id='" . $_POST['report'] . "' AND internal_fields = '1'");

					while ($fieldsInternalFields = mysql_fetch_array($datasetInternalFields))
					{
						$this->xml .= "<internal_fields>" . $fieldsInternalFields['internal_fields'] . "</internal_fields>\n";

						$this->xml .= "<supplierComplaintWithInternalFields>";
						$this->xml .= "<internal_teamLeader>" . $fieldsInternalFields['internal_teamLeader'] . "</internal_teamLeader>\n";
						$this->xml .= "<internal_teamMember>" . $fieldsInternalFields['internal_teamMember'] . "</internal_teamMember>\n";
						$this->xml .= "<internal_qu_stockVerificationMade>" . $fieldsInternalFields['internal_qu_stockVerificationMade'] . "</internal_qu_stockVerificationMade>\n";
						$this->xml .= "<internal_qu_stockVerificationName>" . $fieldsInternalFields['internal_qu_stockVerificationName'] . "</internal_qu_stockVerificationName>\n";
						$this->xml .= "<internal_qu_stockVerificationDate>" . page::transformDateForPHP($fieldsInternalFields['internal_qu_stockVerificationDate']) . "</internal_qu_stockVerificationDate>\n";
						$this->xml .= "<internal_qu_otherMaterialEffected>" . $fieldsInternalFields['internal_qu_otherMaterialEffected'] . "</internal_qu_otherMaterialEffected>\n";
						$this->xml .= "<internal_qu_otherMatDetails>" . $fieldsInternalFields['internal_qu_otherMatDetails'] . "</internal_qu_otherMatDetails>\n";
						$this->xml .= "<internal_analysis>" . $fieldsInternalFields['internal_analysis'] . "</internal_analysis>\n";
						$this->xml .= "<internal_author>" . $fieldsInternalFields['internal_author'] . "</internal_author>\n";
						$this->xml .= "<internal_analysisDate>" . page::transformDateForPHP($fieldsInternalFields['internal_analysisDate']) . "</internal_analysisDate>\n";
						$this->xml .= "<internal_additionalComments>" . $fieldsInternalFields['internal_additionalComments'] . "</internal_additionalComments>\n";
						$this->xml .= "</supplierComplaintWithInternalFields>";
					}
				}

				if($this->complaint->form->get("typeOfComplaint")->getValue() == "customer_complaint")
				{
					$datasetInternalFields = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id='" . $_POST['report'] . "' AND internal_fields = '1'");

					while ($fieldsInternalFields = mysql_fetch_array($datasetInternalFields))
					{
						$this->xml .= "<internal_fields>" . $fieldsInternalFields['internal_fields'] . "</internal_fields>\n";

						$this->xml .= "<supplierComplaintWithInternalFields>";
						$this->xml .= "<internal_teamLeader>" . $fieldsInternalFields['internal_teamLeader'] . "</internal_teamLeader>\n";
						$this->xml .= "<internal_teamMember>" . $fieldsInternalFields['internal_teamMember'] . "</internal_teamMember>\n";
						$this->xml .= "<internal_qu_stockVerificationMade>" . $fieldsInternalFields['internal_qu_stockVerificationMade'] . "</internal_qu_stockVerificationMade>\n";
						$this->xml .= "<internal_qu_stockVerificationName>" . $fieldsInternalFields['internal_qu_stockVerificationName'] . "</internal_qu_stockVerificationName>\n";
						$this->xml .= "<internal_qu_stockVerificationDate>" . page::transformDateForPHP($fieldsInternalFields['internal_qu_stockVerificationDate']) . "</internal_qu_stockVerificationDate>\n";
						$this->xml .= "<internal_qu_otherMaterialEffected>" . $fieldsInternalFields['internal_qu_otherMaterialEffected'] . "</internal_qu_otherMaterialEffected>\n";
						$this->xml .= "<internal_qu_otherMatDetails>" . $fieldsInternalFields['internal_qu_otherMatDetails'] . "</internal_qu_otherMatDetails>\n";
						$this->xml .= "<internal_analysis>" . $fieldsInternalFields['internal_analysis'] . "</internal_analysis>\n";
						$this->xml .= "<internal_author>" . $fieldsInternalFields['internal_author'] . "</internal_author>\n";
						$this->xml .= "<internal_analysisDate>" . page::transformDateForPHP($fieldsInternalFields['internal_analysisDate']) . "</internal_analysisDate>\n";
						$this->xml .= "<internal_additionalComments>" . $fieldsInternalFields['internal_additionalComments'] . "</internal_additionalComments>\n";
						$this->xml .= "</supplierComplaintWithInternalFields>";
					}
				}

				//loads the log details for the Complaints
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM actionLog WHERE complaintId ='" . $_POST['report'] . "' ORDER BY actionDate DESC, actionId DESC");

				$this->xml .= "<complaintsLog>";

				while ($fields = mysql_fetch_array($dataset))
				{
					$this->xml .= "<item>";
					$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
					$this->xml .= "<date>" . common::transformDateTimeForPHP($fields['actionDate']) . "</date>\n";
					$this->xml .= "<action>" . $fields['actionDescription'] . "</action>\n";
					$this->xml .= "<logId>" . $fields['actionId'] . "</logId>\n";
					$this->xml .= "<description>" . $fields['description'] . "</description>\n";
					strlen($fields['description']) > 0 ? $this->xml .= "<descriptionLength>long</descriptionLength>" : $this->xml .= "<descriptionLength>short</descriptionLength>";
					$this->xml .= "</item>";
				}

				$this->xml .= "</complaintsLog>";


				$datasetConclusion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `closedDate`, `totalClosureDate`, `customerComplaintStatus`, `internalComplaintStatus` FROM conclusion WHERE complaintId = " . $_POST['report'] . "");
				$fieldsConclusion = mysql_fetch_array($datasetConclusion);

				$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `closedDate`, `totalClosureDate`, `sapCustomerNumber`, `sapName`, `groupAComplaint`, `groupedComplaintId`, `typeOfComplaint`, `originalStateComplaint`, `sapItemNumbers`, `problemDescription`, `internalSalesName`, `externalSalesName` FROM complaint WHERE id = " . $_POST['report'] . "");
				$fieldsComplaint = mysql_fetch_array($datasetComplaint);

				if($this->getComplaintType($_POST['report']) == "supplier_complaint")
				{
					// do nothing
				}
				else
				{
					$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM customer WHERE id = '" . $fieldsComplaint['sapCustomerNumber'] . "'");
					$fieldsSAP = mysql_fetch_array($datasetSAP);
				}

				//loads the summary details for the Complaints
				$this->xml .= "<complaintsSummary>";

				if($this->complaint->getComplaintType($_POST['report']) == "supplier_complaint")
				{
					$this->xml .= "<isSupplierComplaint>true</isSupplierComplaint>";
				}
				elseif($this->complaint->getComplaintType($_POST['report']) == "quality_complaint")
				{
					$this->xml .= "<isQualityComplaint>true</isQualityComplaint>";
				}
				else
				{
					$this->xml .= "<isSupplierComplaint>false</isSupplierComplaint>";
					$this->xml .= "<isQualityComplaint>false</isQualityComplaint>";
				}

				if($this->complaint->getID() && $this->complaint->getEvaluation() && $this->complaint->getConclusion())
				{
					$this->xml .= "<printAll>true</printAll>";
				}

				$this->xml .= "<openDate>" . common::transformDateForPHP($this->complaint->form->get("openDate")->getValue()) . "</openDate>\n";

				$todaysDate = strtotime(date("Y-m-d", time()));
				$creationDate = strtotime($this->complaint->form->get("openDate")->getValue());
				$daysFromCreation = ($todaysDate - $creationDate) / 86400;


				$daysMinusWeekends = $daysFromCreation / 7;

				// Just for a bit of formatting
				$daysFromCreation == 0 ? $this->xml .= "<daysFromCreation>Today!</daysFromCreation>" : "";
				$daysFromCreation == 1 ? $this->xml .= "<daysFromCreation>" . sprintf("%.0f", $daysFromCreation) . " Day Ago</daysFromCreation>" : "";
				$daysFromCreation > 1 ? $this->xml .= "<daysFromCreation>" . sprintf("%.0f", $daysFromCreation) . " Days Ago</daysFromCreation>" : "";


				if($fieldsConclusion['customerComplaintStatus'] == "" || $fieldsConclusion['customerComplaintStatus'] == "Open")
				{
					$this->xml .= "<custComplaintStatus>Open</custComplaintStatus>";
				}
				else
				{
					$this->xml .= "<custComplaintStatus>Closed</custComplaintStatus>";
					$this->xml .= "<custComplaintClosedDate>" . page::transformDateForPHP($fieldsComplaint['closedDate']) . "</custComplaintClosedDate>";
				}

				if($fieldsConclusion['internalComplaintStatus'] == "" || $fieldsConclusion['internalComplaintStatus'] == "Open")
				{
					$this->xml .= "<internalComplaintStatus>Open</internalComplaintStatus>";
				}
				else
				{
					$this->xml .= "<internalComplaintStatus>Closed</internalComplaintStatus>";
					$this->xml .= "<internalComplaintClosedDate>" . page::transformDateForPHP($fieldsComplaint['totalClosureDate']) . "</internalComplaintClosedDate>";
				}


				if(currentuser::getInstance()->getNTLogon() == 'jmatthews' || currentuser::getInstance()->getNTLogon() == 'rmarkiewka' || currentuser::getInstance()->getNTLogon() == 'slietmann' || currentuser::getInstance()->getNTLogon() == 'phawley' || currentuser::getInstance()->getNTLogon() == 'tobrien')
				{
					$this->xml .= "<complaintAdmin>true</complaintAdmin>";
				}


				$this->xml .= "<owner>" . usercache::getInstance()->get($this->complaint->form->get("owner")->getValue())->getName() . "</owner>";

				// This is what he complaint used to be (only used for quality into supplier complaints)
				if($fieldsComplaint['originalStateComplaint'] != "")
				{
					$this->xml .= "<originalStateComplaintAdded>1</originalStateComplaintAdded>";
					$this->xml .= "<originalStateComplaint>" . $fieldsComplaint['originalStateComplaint'] . "</originalStateComplaint>";
				}

				page::addDebug("SAP NNNNNNNNNNNNNNNNNAAAAAAAAAAAAAAMMMMMMMMMEEEEEEEEEEE" . $fieldsComplaint["sapName"] . "" , __FILE__, __LINE__);

				$this->xml .= "<sapCustomerName>" . $fieldsComplaint["sapName"] . "</sapCustomerName>";

				if($this->getComplaintType($_POST['report']) == "supplier_complaint")
				{
					$this->xml .= "<sapCustomerNumber>" . $this->complaint->form->get("sp_sapSupplierNumber")->getValue() . "</sapCustomerNumber>";
					$this->xml .= "<buyer>" . usercache::getInstance()->get($this->complaint->form->get("sp_buyer")->getValue())->getName() . "</buyer>";
				}
				elseif($this->getComplaintType($_POST['report']) == "quality_complaint")
				{
					$this->xml .= "<foundBy>" . $this->complaint->form->get("qu_foundBy")->getValue() . "</foundBy>";
					$this->xml .= "<whereErrorDetected>" . $this->complaint->form->get("whereErrorOccured")->getValue() . "</whereErrorDetected>";
					$this->xml .= "<siteConcerned>" . $this->complaint->form->get("sp_siteConcerned")->getValue() . "</siteConcerned>";

					$datasetMaterialInfo = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = " . $this->complaint->getID() . " ORDER BY id DESC");

					while($fieldsMaterialInfo = mysql_fetch_array($datasetMaterialInfo))
					{
						$this->xml .= "<materialGroupsInformation>";
						$this->xml .= "<sapItemNumber>" . $fieldsMaterialInfo['sapItemNumber'] . "</sapItemNumber>";
						$this->xml .= "<materialGroupNumber>" . $fieldsMaterialInfo['sapItemNumberMaterialGroup'] . "</materialGroupNumber>";
						$this->xml .= "<colour>" . $fieldsMaterialInfo['sapItemNumberColour'] . "</colour>";
						//$this->xml .= "<dimensions>" . $fieldsMaterialInfo['sapItemNumberDimensionThicknessNew'] . " " . $fieldsMaterialInfo['sapItemNumberDimensionThicknessNewUOM'] . "</dimensions>";
						$this->xml .= "<batchNumber>" . $fieldsMaterialInfo['sapItemNumberBatchNumber'] . "</batchNumber>";
						$this->xml .= "<quantity>" . $fieldsMaterialInfo['sapItemNumberQuantityUnderComplaintNew'] . " " . $fieldsMaterialInfo['sapItemNumberQuantityUnderComplaintNewUOM'] . "</quantity>";
						$this->xml .= "<location>" . $fieldsMaterialInfo['sapItemNumberLocation'] . "</location>";
						$this->xml .= "<materialBlocked>" . $fieldsMaterialInfo['sapItemNumberQu_materialBlocked'] . "</materialBlocked>";
						$this->xml .= "<materialBlockedDate>" . page::transformDateForPHP($fieldsMaterialInfo['sapItemNumberQu_materialBlockedDate']) . "</materialBlockedDate>";
						$this->xml .= "</materialGroupsInformation>";
					}
				}
				else
				{
					$this->xml .= "<sapCustomerNumber>" . $this->complaint->form->get("sapCustomerNumber")->getValue() . "</sapCustomerNumber>";
					$this->xml .= $fieldsSAP['emailAddress'] == "" ? "<sapEmailAddress>N/A</sapEmailAddress>" : "<sapEmailAddress>" . $fieldsSAP['emailAddress'] . "</sapEmailAddress>";
					$this->xml .= $this->complaint->form->get("sampleReceived")->getValue() == "Yes" ? "<sampleRecIntSales>Yes</sampleRecIntSales><sampleRecIntSalesDate>" . $this->complaint->form->get("sampleReceptionDate")->getValue() . "</sampleRecIntSalesDate>" : "<sampleRecIntSales>No</sampleRecIntSales>";
				}


				$this->xml .= $this->complaint->form->get("groupAComplaint")->getValue() == "Yes" ? "<groupAComplaint>Yes</groupAComplaint><groupAComplaintId>" . $this->complaint->form->get("groupedComplaintId")->getValue() . "</groupAComplaintId>" : "<groupAComplaint>No</groupAComplaint>";

				// Get the type of complaint for the grouped complaint
				$datasetGrouped = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT typeOfComplaint FROM `complaint` WHERE id = '" . $this->complaint->form->get("groupedComplaintId")->getValue() . "'");
				$fieldsGrouped = mysql_fetch_array($datasetGrouped);

				$this->xml .= "<grouped_complaint_type>" . $fieldsGrouped['typeOfComplaint'] . "</grouped_complaint_type>";

				$this->xml .= "<complaint_type>" . $this->complaint->form->get("typeOfComplaint")->getValue() . "</complaint_type>";

				if($this->complaint->getEvaluation())
				{
					if($this->getComplaintType($_POST['report']) == "supplier_complaint")
					{
						// do nothing yet
					}
					elseif($this->getComplaintType($_POST['report']) == "quality_complaint")
					{
						// do nothing yet
					}
					else
					{
						$this->xml .= $this->complaint->getEvaluation()->form->get("isSampleReceived")->getValue() == "YES" ? "<sampleRecProOwner>Yes</sampleRecProOwner><sampleRecProOwnerDate>" . $this->complaint->getEvaluation()->form->get("dateSampleReceived")->getValue() . "</sampleRecProOwnerDate>" : "<sampleRecProOwner>No</sampleRecProOwner>";
					}
				}

				$this->xml .= "<sapItemNumbers>" . $fieldsComplaint['sapItemNumbers'] . "</sapItemNumbers>";
				$this->xml .= "<problemDescription>" . page::formatAsParagraphs($fieldsComplaint['problemDescription'], "\r\n") . "</problemDescription>";
				$this->xml .= "<complaintCreator>" . $fieldsComplaint['internalSalesName'] . "</complaintCreator>";
				$this->xml .= "<externalSalesName>" . $fieldsComplaint['externalSalesName'] . "</externalSalesName>";

				$this->xml .= $this->complaint->getID()?"<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>";
				$this->xml .= $this->complaint->getEvaluation()?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>";
				$this->xml .= $this->complaint->getConclusion()?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>";

				$this->xml .= currentuser::getInstance()->getNTLogon() == $this->complaint->form->get("owner")->getValue() ? "<complaintOwner>true</complaintOwner>" : "<complaintOwner>false</complaintOwner>";

				$this->xml .= "</complaintsSummary>";

				$this->xml .= "<complaintsDocuments>";

				if($this->complaint->getComplaintType($_POST['report']) == "supplier_complaint")
				{
					$this->xml .= "<isSupplierComplaint>true</isSupplierComplaint>";
				}
				elseif($this->complaint->getComplaintType($_POST['report']) == "quality_complaint")
				{
					$this->xml .= "<isQualityComplaint>true</isQualityComplaint>";
				}
				else
				{
					$this->xml .= "<isSupplierComplaint>false</isSupplierComplaint>";
					$this->xml .= "<isQualityComplaint>false</isQualityComplaint>";
				}

				if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
				{
					//$this->xml .= "<hostname>10.1.50.2</hostname>";
					//$this->xml .= "<hostname>" . common::getIntranetServerIP() . "</hostname>";
					$this->xml .= "<hostname>" . common::getIntranetServerHostname() . "</hostname>";
				}
				else
				{
					//$this->xml .= "<hostname>dellintranet2</hostname>";
					$this->xml .= "<hostname>" . common::getIntranetServerHostname() . "</hostname>";
				}

				$datasetAck = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'ack'");

				while ($row = mysql_fetch_array($datasetAck))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeAck>" . $row['type'] . "</typeAck>";
					$this->xml .= "<dateGeneratedAck>" . common::transformDateForPHP($row['date']) . "</dateGeneratedAck>";
					$this->xml .= "<genLanguageAck>" . $row['language'] . "</genLanguageAck>";
					$this->xml .= "<openableAck>true</openableAck>";
				}

				$dataset8d = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND (type = '8d' OR type = 'supplier8d')");

				while ($row = mysql_fetch_array($dataset8d))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<type8d>" . $row['type'] . "</type8d>";
					$this->xml .= "<dateGenerated8d>" . common::transformDateForPHP($row['date']) . "</dateGenerated8d>";
					$this->xml .= "<genLanguage8d>" . $row['language'] . "</genLanguage8d>";
					$this->xml .= "<openable8d>true</openable8d>";
				}


				$datasetblank8d = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'blank8d'");

				while ($row = mysql_fetch_array($datasetblank8d))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeblank8d>" . $row['type'] . "</typeblank8d>";
					$this->xml .= "<dateGeneratedblank8d>" . common::transformDateForPHP($row['date']) . "</dateGeneratedblank8d>";
					$this->xml .= "<genLanguageblank8d>" . $row['language'] . "</genLanguageblank8d>";
					$this->xml .= "<openableblank8d>true</openableblank8d>";
				}

				$datasetReturnForm = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'returnForm'");

				while ($row = mysql_fetch_array($datasetReturnForm))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeReturnForm>" . $row['type'] . "</typeReturnForm>";
					$this->xml .= "<dateGeneratedReturnForm>" . common::transformDateForPHP($row['date']) . "</dateGeneratedReturnForm>";
					$this->xml .= "<genLanguageReturnForm>" . $row['language'] . "</genLanguageReturnForm>";
					$this->xml .= "<openableReturnForm>true</openableReturnForm>";
				}

				$datasetDisposalNote = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'disposalNote'");

				while ($row = mysql_fetch_array($datasetDisposalNote))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeDisposalNote>" . $row['type'] . "</typeDisposalNote>";
					$this->xml .= "<dateGeneratedDisposalNote>" . common::transformDateForPHP($row['date']) . "</dateGeneratedDisposalNote>";
					$this->xml .= "<genLanguageDisposalNote>" . $row['language'] . "</genLanguageDisposalNote>";
					$this->xml .= "<openableDisposalNote>true</openableDisposalNote>";
				}


				$datasetSampleReminder = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'sampleRem'");

				while ($row = mysql_fetch_array($datasetSampleReminder))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeSampleReminder>" . $row['type'] . "</typeSampleReminder>";
					$this->xml .= "<dateGeneratedSampleReminder>" . common::transformDateForPHP($row['date']) . "</dateGeneratedSampleReminder>";
					$this->xml .= "<genLanguageSampleReminder>" . $row['language'] . "</genLanguageSampleReminder>";
					$this->xml .= "<openableSampleReminder>true</openableSampleReminder>";
				}


				$datasetSupplierLetter = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'supplierLetter'");

				while ($row = mysql_fetch_array($datasetSupplierLetter))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeSupplierLetter>" . $row['type'] . "</typeSupplierLetter>";
					$this->xml .= "<dateGeneratedSupplierLetter>" . common::transformDateForPHP($row['date']) . "</dateGeneratedSupplierLetter>";
					$this->xml .= "<genLanguageSupplierLetter>" . $row['language'] . "</genLanguageSupplierLetter>";
					$this->xml .= "<openableSupplierLetter>true</openableSupplierLetter>";
				}

				/*$datasetSupplierSummary = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'supplierSummary'");

				while ($row = mysql_fetch_array($datasetSupplierSummary))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<typeSupplierSummary>" . $row['type'] . "</typeSupplierSummary>";
					$this->xml .= "<dateGeneratedSupplierSummary>" . common::transformDateForPHP($row['date']) . "</dateGeneratedSupplierSummary>";
					$this->xml .= "<genLanguageSupplierSummary>" . $row['language'] . "</genLanguageSupplierSummary>";
					$this->xml .= "<openableSupplierSummary>true</openableSupplierSummary>";
				}*/

				$datasetInternal8d = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `documents` WHERE complaintId = '" . $_POST['report'] . "' AND type = 'internal8d'");

				while ($row = mysql_fetch_array($datasetInternal8d))
				{
					$this->xml .= "<complaintId>" . $row['complaintId'] . "</complaintId>";
					$this->xml .= "<type8d>" . $row['type'] . "</type8d>";
					$this->xml .= "<dateGenerated8d>" . common::transformDateForPHP($row['date']) . "</dateGenerated8d>";
					$this->xml .= "<genLanguage8d>" . $row['language'] . "</genLanguage8d>";
					$this->xml .= "<openable8d>true</openable8d>";
				}


				$this->xml .= "<id>" . $this->complaint->getId() . "</id>";

				$this->xml .= "</complaintsDocuments>";



				$this->xml .= "</complaints_report>";

				$this->add_output($this->xml);

				//$this->updatePerformanceValues();
			}

			else
			{
				page::addDebug("ERRRRRRORRRR", __FILE__, __LINE__);
			}
		}

		if(currentuser::getInstance()->hasPermission("complaints_view_graphs"))
		{
			//$this->add_output("<complaintAdmin>true</complaintAdmin>");

			//$this->displayCustomerComplaintsMonthly(); // Number of Customer Complaints Monthly 2008

			//$this->displaySupplierComplaintsMonthly(); // Number of Supplier Complaints Monthly 2008

			//$this->displayCustomerComplaintsValueMonthly(); // Number of Customer Complaints Value GBP Monthly 2008

			//$this->displayCustomerComplaintsByBusinessUnit(); // Number of Customer Complaints By Business Unit 2008
		}

		$this->add_output("</complaintsHome>");

		unset($_SESSION['apps'][$GLOBALS['app']]['complaint']['orderDetailsMulti']);
		unset($_SESSION['apps'][$GLOBALS['app']]['complaint']['sapGroup']);
		unset($_SESSION['apps'][$GLOBALS['app']]['complaint']['intercoGroupYes']);
		unset($_SESSION['apps'][$GLOBALS['app']]['complaint']['materialGroupGroup']);
		unset($_SESSION['apps'][$GLOBALS['app']]['complaint']['scapaInvoiceYesGroup']);

		$this->output('./apps/complaints/xsl/complaints.xsl');
	}

	public function displayCustomerComplaintsMonthly()
	{
		$this->add_output("<chartCustomerComplaintsMonthly>");

		$this->add_output("<graphWidth>400</graphWidth>");
		$this->add_output("<graphHeight>300</graphHeight>");

		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion() == "NA")
		{
			$this->add_output("<graphTitle>2010 Customer Complaints (Open and Closed NA/Europe)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");

				$this->add_output("<graphSep>" . mysql_num_rows($datasetSep) . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");

				$this->add_output("<graphOct>" . mysql_num_rows($datasetOct) . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");

				$this->add_output("<graphNov>" . mysql_num_rows($datasetNov) . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");

				$this->add_output("<graphDec>" . mysql_num_rows($datasetDec) . "</graphDec>");
		}
		else
		{
			$this->add_output("<graphTitle>2010 European Customer Complaints (Open and Closed)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");

				$this->add_output("<graphSep>" . mysql_num_rows($datasetSep) . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");

				$this->add_output("<graphOct>" . mysql_num_rows($datasetOct) . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");

				$this->add_output("<graphNov>" . mysql_num_rows($datasetNov) . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");

				$this->add_output("<graphDec>" . mysql_num_rows($datasetDec) . "</graphDec>");
		}

		$this->add_output("</chartCustomerComplaintsMonthly>");
	}

	public function displaySupplierComplaintsMonthly()
	{
		$this->add_output("<chartSupplierComplaintsMonthly>");

		$this->add_output("<graphWidth>400</graphWidth>");
		$this->add_output("<graphHeight>300</graphHeight>");

		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion() == "NA")
		{
			$this->add_output("<graphTitle>2010 Supplier Complaints (Open and Closed NA/Europe)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");

				$this->add_output("<graphSep>" . mysql_num_rows($datasetSep) . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");

				$this->add_output("<graphOct>" . mysql_num_rows($datasetOct) . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");

				$this->add_output("<graphNov>" . mysql_num_rows($datasetNov) . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");

				$this->add_output("<graphDec>" . mysql_num_rows($datasetDec) . "</graphDec>");
		}
		else
		{
			$this->add_output("<graphTitle>2010 European Supplier Complaints</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");

				$this->add_output("<graphSep>" . mysql_num_rows($datasetSep) . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");

				$this->add_output("<graphOct>" . mysql_num_rows($datasetOct) . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");

				$this->add_output("<graphNov>" . mysql_num_rows($datasetNov) . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'supplier_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");

				$this->add_output("<graphDec>" . mysql_num_rows($datasetDec) . "</graphDec>");
		}

		$this->add_output("</chartSupplierComplaintsMonthly>");
	}

	public function displayCustomerComplaintsValueMonthly()
	{
		$this->add_output("<chartCustomerComplaintsValueMonthly>");

		$this->add_output("<graphWidth>850</graphWidth>");
		$this->add_output("<graphHeight>300</graphHeight>");

		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion() == "NA")
		{
			$this->add_output("<graphTitle>2010 Customer Complaints Value (GBP) (Open and Closed NA/Europe)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");
			$fieldsJan = mysql_fetch_array($datasetJan);

				$this->add_output("<graphJan>" . $fieldsJan['totalSumGBP'] . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");
			$fieldsFeb = mysql_fetch_array($datasetFeb);

				$this->add_output("<graphFeb>" . $fieldsFeb['totalSumGBP'] . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");
			$fieldsMar = mysql_fetch_array($datasetMar);

				$this->add_output("<graphMar>" . $fieldsMar['totalSumGBP'] . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");
			$fieldsApr = mysql_fetch_array($datasetApr);

				$this->add_output("<graphApr>" . $fieldsApr['totalSumGBP'] . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");
			$fieldsMay = mysql_fetch_array($datasetMay);

				$this->add_output("<graphMay>" . $fieldsMay['totalSumGBP'] . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");
			$fieldsJune = mysql_fetch_array($datasetJune);

				$this->add_output("<graphJune>" . $fieldsJune['totalSumGBP'] . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");
			$fieldsJuly = mysql_fetch_array($datasetJuly);

				$this->add_output("<graphJuly>" . $fieldsJuly['totalSumGBP'] . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");
			$fieldsAug = mysql_fetch_array($datasetAug);

				$this->add_output("<graphAug>" . $fieldsAug['totalSumGBP'] . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");
			$fieldsSep = mysql_fetch_array($datasetSep);

				$this->add_output("<graphSep>" . $fieldsSep['totalSumGBP'] . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");
			$fieldsOct = mysql_fetch_array($datasetOct);

				$this->add_output("<graphOct>" . $fieldsOct['totalSumGBP'] . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");
			$fieldsNov = mysql_fetch_array($datasetNov);

				$this->add_output("<graphNov>" . $fieldsNov['totalSumGBP'] . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");
			$fieldsDec = mysql_fetch_array($datasetDec);

				$this->add_output("<graphDec>" . $fieldsDec['totalSumGBP'] . "</graphDec>");
		}
		else
		{
			$this->add_output("<graphTitle>2010 European Customer Complaints Value (GBP) (Open and Closed)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-01-01' AND '2010-01-31'");
			$fieldsJan = mysql_fetch_array($datasetJan);

				$this->add_output("<graphJan>" . $fieldsJan['totalSumGBP'] . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-02-01' AND '2010-02-31'");
			$fieldsFeb = mysql_fetch_array($datasetFeb);

				$this->add_output("<graphFeb>" . $fieldsFeb['totalSumGBP'] . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-03-01' AND '2010-03-31'");
			$fieldsMar = mysql_fetch_array($datasetMar);

				$this->add_output("<graphMar>" . $fieldsMar['totalSumGBP'] . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-04-01' AND '2010-04-31'");
			$fieldsApr = mysql_fetch_array($datasetApr);

				$this->add_output("<graphApr>" . $fieldsApr['totalSumGBP'] . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-05-01' AND '2010-05-31'");
			$fieldsMay = mysql_fetch_array($datasetMay);

				$this->add_output("<graphMay>" . $fieldsMay['totalSumGBP'] . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-06-01' AND '2010-06-31'");
			$fieldsJune = mysql_fetch_array($datasetJune);

				$this->add_output("<graphJune>" . $fieldsJune['totalSumGBP'] . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-07-01' AND '2010-07-31'");
			$fieldsJuly = mysql_fetch_array($datasetJuly);

				$this->add_output("<graphJuly>" . $fieldsJuly['totalSumGBP'] . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-08-01' AND '2010-08-31'");
			$fieldsAug = mysql_fetch_array($datasetAug);

				$this->add_output("<graphAug>" . $fieldsAug['totalSumGBP'] . "</graphAug>");

			$datasetSep = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-09-01' AND '2010-09-31'");
			$fieldsSep = mysql_fetch_array($datasetSep);

				$this->add_output("<graphSep>" . $fieldsSep['totalSumGBP'] . "</graphSep>");

			$datasetOct = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-10-01' AND '2010-10-31'");
			$fieldsOct = mysql_fetch_array($datasetOct);

				$this->add_output("<graphOct>" . $fieldsOct['totalSumGBP'] . "</graphOct>");

			$datasetNov = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-11-01' AND '2010-11-31'");
			$fieldsNov = mysql_fetch_array($datasetNov);

				$this->add_output("<graphNov>" . $fieldsNov['totalSumGBP'] . "</graphNov>");

			$datasetDec = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sum(gbpComplaintValue_quantity) AS `totalSumGBP` FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND openDate BETWEEN '2010-12-01' AND '2010-12-31'");
			$fieldsDec = mysql_fetch_array($datasetDec);

				$this->add_output("<graphDec>" . $fieldsDec['totalSumGBP'] . "</graphDec>");
		}

		$this->add_output("</chartCustomerComplaintsValueMonthly>");
	}

	public function displayCustomerComplaintsByBusinessUnit()
	{
		$this->add_output("<chartCustomerComplaintsByBusinessUnit>");

		$this->add_output("<graphWidth>400</graphWidth>");
		$this->add_output("<graphHeight>300</graphHeight>");

		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion() == "NA")
		{
			$this->add_output("<graphTitle>2010 Customer Complaints By Business Unit (Open and Closed)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'industrial_assembly' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'automotive' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'medical' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'construction' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'cable' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'interco' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'printing_and_graphics' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND businessUnit = 'sports_and_leisure' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");
		}
		else
		{
			$this->add_output("<graphTitle>2010 European Customer Complaints By Business Unit (Open and Closed)</graphTitle>");

			$datasetJan = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'industrial_assembly' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJan>" . mysql_num_rows($datasetJan) . "</graphJan>");

			$datasetFeb = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'automotive' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphFeb>" . mysql_num_rows($datasetFeb) . "</graphFeb>");

			$datasetMar = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'medical' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphMar>" . mysql_num_rows($datasetMar) . "</graphMar>");

			$datasetApr = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'construction' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphApr>" . mysql_num_rows($datasetApr) . "</graphApr>");

			$datasetMay = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'cable' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphMay>" . mysql_num_rows($datasetMay) . "</graphMay>");

			$datasetJune = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'interco' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJune>" . mysql_num_rows($datasetJune) . "</graphJune>");

			$datasetJuly = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'printing_and_graphics' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphJuly>" . mysql_num_rows($datasetJuly) . "</graphJuly>");

			$datasetAug = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE typeOfComplaint = 'customer_complaint' AND complaintLocation = 'european' AND businessUnit = 'sports_and_leisure' AND openDate BETWEEN '2010-01-01' AND '2010-12-31'");

				$this->add_output("<graphAug>" . mysql_num_rows($datasetAug) . "</graphAug>");
		}

		$this->add_output("</chartCustomerComplaintsByBusinessUnit>");
	}

	public function getComplaintType($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT typeOfComplaint FROM complaint WHERE `id` = '" . $id . "'");

		$fields = mysql_fetch_array($dataset);

		$complaintType = $fields['typeOfComplaint'];

		return $complaintType;
	}

	public function checkEmailSent()
	{
		// For a little justification and messaging for n000b users.
		if(isset($_GET['emailSent']))
		{
			if($_GET['emailSent'] == "true")
			{
				$this->xml .= "<emailSent>true</emailSent>";
			}

			if($_GET['emailSent'] == "false")
			{
				$this->xml .= "<emailSent>false</emailSent>";
			}
		}
	}

}

?>