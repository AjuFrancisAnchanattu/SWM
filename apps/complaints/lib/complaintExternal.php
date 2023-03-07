<?php

/**
 * This is the Complaint Application.
 *
 * This is the external complaint class.  This class is used to conduct the external part of the Complaint process.
 *
 * @package apps
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class complaintExternal extends complaintsProcess
{
	/**
	 * The constructor, which the Complaints is passed to.
	 *
	 * @param complaints $complaints
	 */
	function __construct($complaint)
	{
		parent::__construct($complaint);

		$this->defineSupplierForm();

		//$this->form->get('complaintId')->setValue($this->getId());

		$this->form->setStoreInSession(true);

		$this->form->loadSessionData();

		if (isset($_SESSION['apps'][$GLOBALS['app']]['complaintExternal']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the Complaint is loaded from the database
		}

		$this->form->processDependencies();

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'approve')
		{
			$this->save("approve");
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'acceptContainmentAction')
		{
			$this->save("acceptContainmentAction");
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'reject')
		{
			$this->save("reject");
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'rejectContainmentAction')
		{
			$this->save("rejectContainmentAction");
		}

		$this->form->get("complaintId")->setValue($_REQUEST['complaint']);

	}

	public function lockComplaint($id, $status)
	{
		$nowTimeStamp = strtotime(date('Y-m-d H:i:s', time()));
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET locked = '" . $status . "' WHERE id = " . $id . "");
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET lockedTime = '" . $nowTimeStamp . "' WHERE id = " . $id . "");
	}


	public function load($id)
	{
		page::addDebug("loading Complaint id=$id", __FILE__, __LINE__);

		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;

		$this->form->setStoreInSession(true);

		$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM complaintExternal WHERE id = " . $_REQUEST['complaint'] . "");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;

			$_SESSION['apps'][$GLOBALS['app']]['complaint']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$this->id = $fields['id'];

			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;

			$this->form->populate($fields);

			$datasetMultiples2 = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM sapItemNumber WHERE complaintId = $this->id");
			$sapItemNo = "";
			while($fieldsMultiples2 = mysql_fetch_array($datasetMultiples2))
			{
				$sapItemNo .= $fieldsMultiples2['sapItemNumber'] . " | ";
			}

			if($fieldsMultiples2['sapItemNumber'] != "")
			{
				$sapItemNo = substr($sapItemNo, 0, -2);
			}
			else
			{
				$sapItemNo = "";
			}

			$datasetMultiples3 = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE complaintId = $this->id");
			$materialGroups = "";
			while($fieldsMultiples3 = mysql_fetch_array($datasetMultiples3))
			{
				$materialGroups .= $fieldsMultiples3['materialGroup'] . " - Description: " . $fieldsMultiples3['productDescription'] . " | ";
			}

			if($fieldsMultiples3['materialGroup'] != "")
			{
				$materialGroups = substr($materialGroups, 0, -2);
			}
			else
			{
				$materialGroups = "";
			}


			$this->form->get("sapItems")->setValue($sapItemNo);
			$this->form->get("materialGroup")->setValue($materialGroups);

			// Populate the read only's with formatted text
			$fields['sp_additionalComplaintCost_quantity'] != "" ? $this->form->get("sp_additionalComplaintCost")->setValue($fields['sp_additionalComplaintCost_quantity'] . " " . $fields['sp_additionalComplaintCost_measurement']) : $this->form->get("sp_additionalComplaintCost")->setValue("");

			$fields['complaintValue_quantity'] != "" ? $this->form->get("complaintValue")->setValue($fields['complaintValue_quantity'] . " " . $fields['complaintValue_measurement']) : $this->form->get("complaintValue")->setValue("");

			$fields['quantityUnderComplaint_quantity'] != "" ? $this->form->get("quantityUnderComplaint")->setValue($fields['quantityUnderComplaint_quantity'] . " " . $fields['quantityUnderComplaint_measurement']) : $this->form->get("quantityUnderComplaint")->setValue("");

			$fields['sp_quantityRecieved_quantity'] != "" ? $this->form->get("sp_quantityRecieved")->setValue($fields['sp_quantityRecieved_quantity'] . " " . $fields['sp_quantityRecieved_measurement']) : $this->form->get("sp_quantityRecieved")->setValue("");

			$this->form->get("openDate")->setValue(page::transformDateForPHP($this->form->get("openDate")->getValue()));

			$this->form->get("sp_buyer")->setValue(usercache::getInstance()->get($this->form->get("sp_buyer")->getValue())->getName());

			//$fields['defectQuantity_quantity'] != "" ? $this->form->get("defectQuantity")->setValue($fields['defectQuantity_quantity'] . " " . $fields['defectQuantity_measurement']) : $this->form->get("defectQuantity")->setValue("");
			//$fields['defectQuantity2_quantity'] != "" ? $this->form->get("defectQuantity2")->setValue($fields['defectQuantity2_quantity'] . " " . $fields['defectQuantity2_measurement']) : $this->form->get("defectQuantity2")->setValue("");
			//$fields['defectQuantity3_quantity'] != "" ? $this->form->get("defectQuantity3")->setValue($fields['defectQuantity3_quantity'] . " " . $fields['defectQuantity3_measurement']) : $this->form->get("defectQuantity3")->setValue("");


			$this->form->get("defectQuantity")->setValue(array($fields['defectQuantity_quantity'], $fields['defectQuantity_measurement']));
			$this->form->get("defectQuantity2")->setValue(array($fields['defectQuantity2_quantity'], $fields['defectQuantity2_measurement']));
			$this->form->get("defectQuantity3")->setValue(array($fields['defectQuantity3_quantity'], $fields['defectQuantity3_measurement']));

			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();


		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			return false;
		}

		return true;
	}

	public function setCustomerComplaintStatus($type)  // JM Created on: 19/12/2007
	{
		/*
			This is the overall CUSTOMER Complaint Status
			i.e. Customer Complaint is closed, users are still able to edit etc.
		*/
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET overallCustomerComplaintStatus = '" . $type . "' WHERE id ='" . $this->getcomplaintId() . "'");

	}

	public function setInternalComplaintStatus($type)  // JM Created on: 19/12/2007
	{
		/*
			This is the overall INTERNAL Complaint Status
			i.e. Both Customer and Internal Complaint are closed, users can no longer edit.
		*/
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET overallComplaintStatus = '" . $type . "' WHERE id ='" . $this->getcomplaintId() . "'");
	}

	public function save($process)
	{
		page::addDebug("Saving Complaint process: ".$process,__FILE__,__LINE__);

		$this->sp_sapSupplierNumber = $_SESSION['apps'][$GLOBALS['app']]['complaint']['complaintDetails']['sp_sapSupplierNumber'];

		switch ($process)
		{
//			case 'complaint':
//
//				if ($this->loadedFromDatabase)
//				{
//					// set this to 1 when external supplier has entered fields.
//					$this->form->get("extStatus")->setValue("1");
//
//					// update
//					mysql::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal " . $this->form->generateUpdateQuery("complaintExternal") . " WHERE id = " . $this->id . "");
//
//					// save new data
//					//$this->addLog(translate::getInstance()->translate("complaint_updated_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());
//
//				}
//				else
//				{
//
//					// insert
//					mysql::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO complaintExternal " . $this->form->generateInsertQuery("complaintExternal"));
//
//					// get last inserted
//					$dataset = mysql::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT id FROM complaintExternal ORDER BY id DESC LIMIT 1");
//
//					$fields = mysql_fetch_array($dataset);
//
//					$this->id = $fields['id'];
//					$this->form->get("id")->setValue($fields['id']);
//
//			}
//
//			//$this->lockComplaint($this->id, "unlocked");
//
//			//$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", utf8_encode($this->form->get("email_text")->getValue()));
//
//			break;

			case 'approve':

				if($this->form->get("analysis")->getValue() != "")
				{
					$this->form->get("analysisyn")->setValue("Yes");
				}
				else
				{
					$this->form->get("analysisyn")->setValue("No");
				}

				if($this->form->get("rootCauses")->getValue() != "")
				{
					$this->form->get("rootCausesyn")->setValue("Yes");
				}
				else
				{
					$this->form->get("rootCausesyn")->setValue("No");
				}

				if($this->form->get("implementedActions")->getValue() != "")
				{
					$this->form->get("implementedActionsyn")->setValue("Yes");
				}
				else
				{
					$this->form->get("implementedActionsyn")->setValue("No");
				}

				if($this->form->get("possibleSolutions")->getValue() != "")
				{
					$this->form->get("possibleSolutionsyn")->setValue("Yes");
				}
				else
				{
					$this->form->get("possibleSolutionsyn")->setValue("No");
				}

				if($this->form->get("preventivePermCorrActions")->getValue() != "")
				{
					$this->form->get("preventiveActionsyn")->setValue("Yes");
				}
				else
				{
					$this->form->get("preventiveActionsyn")->setValue("No");
				}



				//if($this->form->get("implementedPermanentCorrectiveActionValidated")->getValue() != "")
				//{
				//	$this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("Yes");
				//}
				//else
				//{
				//	$this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("No");
				//}

				if($this->form->get("containmentActionSupplier")->getValue() != "")
				{
					$this->form->get("containmentActionSupplieryn")->setValue("Yes");
				}
				else
				{
					$this->form->get("containmentActionSupplieryn")->setValue("No");
				}

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET scapaStatus = 1 WHERE id = " . $_REQUEST['complaint'] . "");

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET containmentActionAdded = 2 WHERE id = " . $_REQUEST['complaint'] . "");

				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET containmentActionSupplieryn = '" . $this->form->get("containmentActionSupplieryn")->getValue() . "', analysisyn = '" . $this->form->get("analysisyn")->getValue() . "', rootCausesyn = '" . $this->form->get("rootCausesyn")->getValue() . "', implementedActionsyn = '" . $this->form->get("implementedActionsyn")->getValue() . "', possibleSolutionsyn = '" . $this->form->get("possibleSolutionsyn")->getValue() . "', preventiveActionsyn = '" . $this->form->get("preventiveActionsyn")->getValue() . "' WHERE id = " . $_REQUEST['complaint'] . "");

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplier8dTimerUpdated = '" . page::nowDateTimeForMysql() . "', supplier8dTimerStatus = '1' WHERE id = " . $_REQUEST['complaint'] . "");

				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation WHERE complaintId = " . $_REQUEST['complaint'] . "");

				$fields = mysql_fetch_array($dataset);

				if($fields)
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation " . $this->form->generateUpdateQuery("complaintExternal") . " WHERE complaintId = " . $_REQUEST['complaint'] . "");
				}
				else
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO evaluation " . $this->form->generateInsertQuery("complaintExternal"));
				}

				$this->addLog(translate::getInstance()->translate("external_complaint_approved") . " - " . page::xmlentities(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()) . "", "");

				$this->getEmailNotification($this->getSupplierEmail($this->sp_sapSupplierNumber), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $_REQUEST['complaint'], "newExternalApproved");

				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET does8DActionExist = 'YES' WHERE id = " . $_REQUEST['complaint'] . "");

				page::redirect("/apps/complaints/index?id=" . $_REQUEST['complaint']);		//redirects the page back to the summary

			break;

			case 'reject':

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET scapaStatus = 0, extStatus = 0 WHERE id = " . $_REQUEST['complaint'] . "");

				// Find 8D Completion Date - Plus 10 Days to now.
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplier8dTimer = '" . page::nowDateTimeForMysqlPlusTenDays() . "', supplier8dTimerStatus = '0' WHERE id = " . $_REQUEST['complaint'] . "");

				page::redirect("/apps/complaints/reject?complaintId=" . $_REQUEST['complaint'] . "&type=8d");		//redirects the page back to the summary

			break;

			case 'rejectContainmentAction':

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET scapaStatus = 0, extStatus = 0 WHERE id = " . $_REQUEST['complaint'] . "");
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET containmentActionAdded = '' WHERE id = " . $_REQUEST['complaint'] . "");
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplierTimerStatus = '0', supplierTimer = '" . page::nowDateTimeForMysqlPlusOneDay() . "' WHERE id = " . $_REQUEST['complaint'] . "");

				page::redirect("/apps/complaints/reject?complaintId=" . $_REQUEST['complaint'] . "&type=3d");		//redirects the page back to the summary

			break;

			case 'acceptContainmentAction':

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET scapaStatus = 0, extStatus = 0 WHERE id = " . $_REQUEST['complaint'] . "");

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET containmentActionAdded = '2' WHERE id = " . $_REQUEST['complaint'] . "");

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplierTimerStatus = '1', supplierTimerUpdated = '" . page::nowDateTimeForMysql() . "' WHERE id = " . $_REQUEST['complaint'] . "");

				$this->addLog(translate::getInstance()->translate("external_containment_action_approved") . " - " . page::xmlentities(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()) . "", "");

				// Find 8D Completion Date - Plus 10 Days to now.
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplier8dTimer = '" . page::nowDateTimeForMysqlPlusTenDays() . "' WHERE id = " . $_REQUEST['complaint'] . "");

				$this->getEmailNotification($this->getSupplierEmail($this->sp_sapSupplierNumber), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $_REQUEST['complaint'], "newExternalContainmentActionApproved");

				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET doesContainmentActionExist = 'YES' WHERE id = " . $_REQUEST['complaint'] . "");

				break;


			default:
				die("Default here ...");

		}

	}

	public function getSupplierEmail($supplierNumber)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM `supplier` WHERE `id` = '" . $supplierNumber ."'");
		$fields = mysql_fetch_array($dataset);

		$emailAddress = $fields['emailAddress'];

		return $emailAddress;
	}



	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
			$_REQUEST['complaint'],
			currentuser::getInstance()->getNTLogon(),
			utf8_encode($action),
			common::nowDateTimeForMysql(),
			$comment
		));
	}


	public function getOwner()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['owner'];
	}

	public function getId()
	{
		return $this->id;
	}


	public function validate()
	{
		$valid = true;

		if (!$this->form->validate())
		{
			$valid = false;
		}

		return $valid;
	}

	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}

	public function showCompletionBits($outputType)
	{
		if ($outputType == "readOnly")
		{
			//$this->form->get('status')->setVisible(true);
			//$this->form->get('completionDate')->setVisible(true);

			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
			{
				//$this->form->get('finalComments')->setVisible(true);
			}
		}

		if ($outputType == "normal")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
			{
				//$this->form->get('finalComments')->setVisible(true);
			}
		}
	}

	public function defineSupplierForm()
	{
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("complaintExternal" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
		$verificationOfStockYes = new group("verificationOfStockYes");
		$verificationOfStockYes->setBorder(false);
		$warehouseGroup = new group("warehouseGroup");
		$warehouseGroup->setBorder(false);
		$warehouseYes = new group("warehouseYes");
		$warehouseYes->setBorder(false);
		$productionGroup = new group("productionGroup");
		$productionGroup->setBorder(false);
		$productionYes = new group("productionYes");
		$productionYes->setBorder(false);
		$transitGroup = new group("transitGroup");
		$transitGroup->setBorder(false);
		$transitYes = new group("transitYes");
		$transitYes->setBorder(false);
		$typeOfComplaintGroup1 = new group("typeOfComplaintGroup1");
		$typeOfComplaintGroup11 = new group("typeOfComplaintGroup11");
		$complaintJustifiedNoGroup = new group("complaintJustifiedNoGroup");
		$typeOfComplaintGroup2 = new group("typeOfComplaintGroup2");
		$sendToUser = new group("sendToUser");

		$complaintId = new textbox("complaintId");
		$complaintId->setTable("complaintExternal");
		$complaintId->setVisible(false);
		$complaintId->setDataType("number");
		$initiation->add($complaintId);

		$extStatus = new textbox("extStatus");
		$extStatus->setTable("complaintExternal");
		$extStatus->setVisible(false);
		$extStatus->setIgnore(false);
		$extStatus->setDataType("string");
		$initiation->add($extStatus);

		$containmentActionAdded = new textbox("containmentActionAdded");
		$containmentActionAdded->setTable("complaintExternal");
		$containmentActionAdded->setVisible(false);
		$containmentActionAdded->setIgnore(false);
		$containmentActionAdded->setDataType("string");
		$initiation->add($containmentActionAdded);

		$openDate = new textbox("openDate");
		$openDate->setTable("complaint");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$openDate = new readonly("openDate");
		$openDate->setGroup("typeOfComplaintGroup");
		$openDate->setDataType("string");
		$openDate->setLabel("1 - Information");
		$openDate->setRowTitle("scapa_complaint_date");
		$openDate->setTable("complaintExternal");
		$openDate->setHelpId(10001);
		$typeOfComplaintGroup->add($openDate);

		$sp_siteConcerned = new readonly("sp_siteConcerned");
		$sp_siteConcerned->setGroup("typeOfComplaintGroup");
		$sp_siteConcerned->setDataType("string");
		$sp_siteConcerned->setRowTitle("site_concerned");
		$sp_siteConcerned->setTable("complaintExternal");
		$sp_siteConcerned->setHelpId(10001);
		$typeOfComplaintGroup->add($sp_siteConcerned);

		$sp_buyer = new readonly("sp_buyer");
		$sp_buyer->setGroup("typeOfComplaintGroup");
		$sp_buyer->setDataType("string");
		$sp_buyer->setRowTitle("buyer");
		$sp_buyer->setTable("complaintExternal");
		$sp_buyer->setHelpId(10001);
		$typeOfComplaintGroup->add($sp_buyer);

		$category = new readonly("category");
		$category->setGroup("typeOfComplaintGroup");
		$category->setDataType("string");
		$category->setLength(255);
		$category->setRowTitle("apparent_category");
		$category->setTable("complaintExternal");
		$category->setHelpId(8005);
		$typeOfComplaintGroup->add($category);

		//$colour = new readonly("colour");
		//$colour->setGroup("typeOfComplaintGroup");
		//$colour->setDataType("string");
		//$colour->setRowTitle("colour");
		//$colour->setTable("complaintExternal");
		//$colour->setHelpId(10002);
		//$typeOfComplaintGroup->add($colour);

		$batchNumber = new readonly("batchNumber");
		$batchNumber->setGroup("typeOfComplaintGroup");
		$batchNumber->setDataType("string");
		$batchNumber->setRowTitle("scapa_batch_number");
		$batchNumber->setTable("complaintExternal");
		$batchNumber->setHelpId(10003);
		$typeOfComplaintGroup->add($batchNumber);

		$supplierBatchNumber = new readonly("supplierBatchNumber");
		$supplierBatchNumber->setGroup("typeOfComplaintGroup");
		$supplierBatchNumber->setDataType("string");
		$supplierBatchNumber->setRowTitle("supplier_batch_number");
		$supplierBatchNumber->setTable("complaintExternal");
		$supplierBatchNumber->setHelpId(10003);
		$typeOfComplaintGroup->add($supplierBatchNumber);

		/*$dimensionThickness = new readonly("dimensionThickness");
		$dimensionThickness->setGroup("typeOfComplaintGroup");
		$dimensionThickness->setDataType("string");
		$dimensionThickness->setRowTitle("thickness");
		$dimensionThickness->setTable("complaintExternal");
		$dimensionThickness->setHelpId(10004);
		$typeOfComplaintGroup->add($dimensionThickness);

		$dimensionWidth = new readonly("dimensionWidth");
		$dimensionWidth->setGroup("typeOfComplaintGroup");
		$dimensionWidth->setDataType("string");
		$dimensionWidth->setRowTitle("width");
		$dimensionWidth->setTable("complaintExternal");
		$dimensionWidth->setHelpId(10005);
		$typeOfComplaintGroup->add($dimensionWidth);

		$dimensionLength = new readonly("dimensionLength");
		$dimensionLength->setGroup("typeOfComplaintGroup");
		$dimensionLength->setDataType("string");
		$dimensionLength->setRowTitle("length");
		$dimensionLength->setTable("complaintExternal");
		$dimensionLength->setHelpId(10006);
		$typeOfComplaintGroup->add($dimensionLength);*/

		//$invoiceNo = new readonly("invoiceNo");
		//$invoiceNo->setGroup("typeOfComplaintGroup");
		//$invoiceNo->setDataType("string");
		//$invoiceNo->setRowTitle("invoice_no");
		//$invoiceNo->setTable("complaintExternal");
		//$invoiceNo->setHelpId(10007);
		//$typeOfComplaintGroup->add($invoiceNo);

		//$invoiceDate = new readonly("invoiceDate");
		//$invoiceDate->setGroup("typeOfComplaintGroup");
		//$invoiceDate->setDataType("string");
		//$invoiceDate->setRowTitle("invoice_date");
		//$invoiceDate->setTable("complaintExternal");
		//$invoiceDate->setHelpId(10008);
		//$typeOfComplaintGroup->add($invoiceDate);

		//$scapaPartNo = new readonly("scapaPartNo");
		//$scapaPartNo->setGroup("typeOfComplaintGroup");
		//$scapaPartNo->setDataType("string");
		//$scapaPartNo->setRowTitle("scapa_part_no");
		//$scapaPartNo->setTable("complaintExternal");
		//$scapaPartNo->setHelpId(10009);
		//$typeOfComplaintGroup->add($scapaPartNo);

		//$supplierConfirmNo = new readonly("supplierConfirmNo");
		//$supplierConfirmNo->setGroup("typeOfComplaintGroup");
		//$supplierConfirmNo->setDataType("string");
		//$supplierConfirmNo->setRowTitle("supplier_confirm_no");
		//$supplierConfirmNo->setTable("complaintExternal");
		//$supplierConfirmNo->setHelpId(10010);
		//$typeOfComplaintGroup->add($supplierConfirmNo);

//		$productDescription = new readonly("productDescription");
//		$productDescription->setGroup("typeOfComplaintGroup");
//		$productDescription->setDataType("string");
//		$productDescription->setRowTitle("material_description");
//		$productDescription->setTable("complaintExternal");
//		$productDescription->setHelpId(10011);
//		$typeOfComplaintGroup->add($productDescription);

		$sapItems = new readonly("sapItems");
		$sapItems->setGroup("typeOfComplaintGroup");
		$sapItems->setDataType("string");
		$sapItems->setRowTitle("material_number");
		$sapItems->setTable("complaintExternal");
		$sapItems->setHelpId(10012);
		$typeOfComplaintGroup->add($sapItems);

//		$quantity = new readonly("quantity");
//		$quantity->setGroup("typeOfComplaintGroup");
//		$quantity->setDataType("string");
//		$quantity->setRowTitle("quantity_received");
//		$quantity->setTable("complaintExternal");
//		$quantity->setHelpId(10013);
//		$typeOfComplaintGroup->add($quantity);

		$sp_quantityRecieved = new readonly("sp_quantityRecieved");
		$sp_quantityRecieved->setGroup("typeOfComplaintGroup");
		$sp_quantityRecieved->setDataType("string");
		$sp_quantityRecieved->setRowTitle("quantity_received");
		$sp_quantityRecieved->setTable("complaintExternal");
		$sp_quantityRecieved->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_quantityRecieved);

		$quantityUnderComplaint = new readonly("quantityUnderComplaint");
		$quantityUnderComplaint->setGroup("typeOfComplaintGroup");
		$quantityUnderComplaint->setDataType("string");
		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
		$quantityUnderComplaint->setTable("complaintExternal");
		$quantityUnderComplaint->setHelpId(10014);
		$typeOfComplaintGroup->add($quantityUnderComplaint);

		$complaintValue = new readonly("complaintValue");
		$complaintValue->setGroup("typeOfComplaintGroup");
		$complaintValue->setDataType("string");
		$complaintValue->setRowTitle("complaint_value");
		$complaintValue->setTable("complaintExternal");
		$complaintValue->setHelpId(10014);
		$typeOfComplaintGroup->add($complaintValue);

		$sp_additionalComplaintCost = new readonly("sp_additionalComplaintCost");
		$sp_additionalComplaintCost->setGroup("typeOfComplaintGroup");
		$sp_additionalComplaintCost->setDataType("string");
		$sp_additionalComplaintCost->setRowTitle("additional_complaint_cost");
		$sp_additionalComplaintCost->setTable("complaintExternal");
		$sp_additionalComplaintCost->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_additionalComplaintCost);

		$sp_detailsOfComplaintCost = new readonly("sp_detailsOfComplaintCost");
		$sp_detailsOfComplaintCost->setGroup("typeOfComplaintGroup");
		$sp_detailsOfComplaintCost->setDataType("string");
		$sp_detailsOfComplaintCost->setRowTitle("details_of_complaint_cost");
		$sp_detailsOfComplaintCost->setTable("complaintExternal");
		$sp_detailsOfComplaintCost->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_detailsOfComplaintCost);

		$materialGroup = new readonly("materialGroup");
		$materialGroup->setGroup("typeOfComplaintGroup");
		$materialGroup->setDataType("string");
		$materialGroup->setRowTitle("material_group");
		$materialGroup->setTable("complaintExternal");
		$materialGroup->setHelpId(10014);
		$typeOfComplaintGroup->add($materialGroup);

		$sp_supplierItemNumber = new readonly("sp_supplierItemNumber");
		$sp_supplierItemNumber->setGroup("typeOfComplaintGroup");
		$sp_supplierItemNumber->setDataType("string");
		$sp_supplierItemNumber->setRowTitle("supplier_item_number");
		$sp_supplierItemNumber->setTable("complaintExternal");
		$sp_supplierItemNumber->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_supplierItemNumber);

		$sp_supplierProductDescription = new readonly("sp_supplierProductDescription");
		$sp_supplierProductDescription->setGroup("typeOfComplaintGroup");
		$sp_supplierProductDescription->setDataType("string");
		$sp_supplierProductDescription->setRowTitle("supplier_product_description");
		$sp_supplierProductDescription->setTable("complaintExternal");
		$sp_supplierProductDescription->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_supplierProductDescription);

		$sp_goodsReceivedDate = new readonly("sp_goodsReceivedDate");
		$sp_goodsReceivedDate->setGroup("typeOfComplaintGroup");
		$sp_goodsReceivedDate->setDataType("string");
		$sp_goodsReceivedDate->setRowTitle("goods_received_date");
		$sp_goodsReceivedDate->setTable("complaintExternal");
		$sp_goodsReceivedDate->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_goodsReceivedDate);

		$sp_goodsReceivedNumber = new readonly("sp_goodsReceivedNumber");
		$sp_goodsReceivedNumber->setGroup("typeOfComplaintGroup");
		$sp_goodsReceivedNumber->setDataType("string");
		$sp_goodsReceivedNumber->setRowTitle("goods_received_number");
		$sp_goodsReceivedNumber->setTable("complaintExternal");
		$sp_goodsReceivedNumber->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_goodsReceivedNumber);

		$sp_purchaseOrderNumber = new readonly("sp_purchaseOrderNumber");
		$sp_purchaseOrderNumber->setGroup("typeOfComplaintGroup");
		$sp_purchaseOrderNumber->setDataType("string");
		$sp_purchaseOrderNumber->setRowTitle("purchase_order_number");
		$sp_purchaseOrderNumber->setTable("complaintExternal");
		$sp_purchaseOrderNumber->setHelpId(10014);
		$typeOfComplaintGroup->add($sp_purchaseOrderNumber);

		$g8d = new readonly("g8d");
		$g8d->setGroup("typeOfComplaintGroup");
		$g8d->setDataType("string");
		$g8d->setRowTitle("full_8d_required");
		$g8d->setTable("complaintExternal");
		$g8d->setHelpId(10014);
		$typeOfComplaintGroup->add($g8d);

		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/complaintsExternal/tmp");
		$attachment->setFinalFileLocation("/apps/complaintsExternal/attachments");
		$attachment->setHelpId(11);
		$typeOfComplaintGroup->add($attachment);

		// not needed as can be displayed on left hand side

		/*$scapaContact = new readonly("scapaContact");
		$scapaContact->setGroup("typeOfComplaintGroup");
		$scapaContact->setDataType("string");
		$scapaContact->setRowTitle("scapa_contact");
		$scapaContact->setTable("complaintExternal");
		$scapaContact->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaContact);

		$scapaTel = new readonly("scapaTel");
		$scapaTel->setGroup("typeOfComplaintGroup");
		$scapaTel->setDataType("string");
		$scapaTel->setRowTitle("scapa_tel");
		$scapaTel->setTable("complaintExternal");
		$scapaTel->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaTel);

		$scapaSite = new readonly("scapaSite");
		$scapaSite->setGroup("typeOfComplaintGroup");
		$scapaSite->setDataType("string");
		$scapaSite->setRowTitle("scapa_site");
		$scapaSite->setTable("complaintExternal");
		$scapaSite->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaSite);

		$scapaEmail = new readonly("scapaEmail");
		$scapaEmail->setGroup("typeOfComplaintGroup");
		$scapaEmail->setDataType("string");
		$scapaEmail->setRowTitle("scapa_email");
		$scapaEmail->setTable("complaintExternal");
		$scapaEmail->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaEmail);

		$scapaSupplierNumber = new readonly("scapaSupplierNumber");
		$scapaSupplierNumber->setGroup("typeOfComplaintGroup");
		$scapaSupplierNumber->setDataType("string");
		$scapaSupplierNumber->setRowTitle("supplier_number");
		$scapaSupplierNumber->setTable("complaintExternal");
		$scapaSupplierNumber->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaSupplierNumber);

		$scapaSupplierName = new readonly("scapaSupplierName");
		$scapaSupplierName->setGroup("typeOfComplaintGroup");
		$scapaSupplierName->setDataType("string");
		$scapaSupplierName->setRowTitle("supplier_name");
		$scapaSupplierName->setTable("complaintExternal");
		$scapaSupplierName->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaSupplierName);

		$scapaSupplierContact = new textbox("scapaSupplierContact");
		$scapaSupplierContact->setGroup("typeOfComplaintGroup");
		$scapaSupplierContact->setDataType("string");
		$scapaSupplierContact->setRowTitle("scapa_supplier_contact");
		$scapaSupplierContact->setTable("complaintExternal");
		$scapaSupplierContact->setHelpId(8199);
		$typeOfComplaintGroup->add($scapaSupplierContact);*/

		$problemDescription = new readonly("problemDescription");
		$problemDescription->setGroup("typeOfComplaintGroup");
		$problemDescription->setDataType("string");
		$problemDescription->setLabel("2 - Problem Description");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setTable("complaintExternal");
		$problemDescription->setHelpId(10015);
		$typeOfComplaintGroup->add($problemDescription);

		$sp_sampleSent = new readonly("sp_sampleSent");
		$sp_sampleSent->setTable("complaintExternal");
		$sp_sampleSent->setLength(20);
		//$sp_sampleSent->setArraySource(array(
		//	array('value' => 'Yes', 'display' => 'Yes'),
		//	array('value' => 'No', 'display' => 'No')
		//));
		if(isset($savedFields["sampleForwarded"]))
			$sp_sampleSent->setValue($savedFields["sampleForwarded"]);
		$sp_sampleSent->setDataType("string");
		$sp_sampleSent->setHelpId(10016);
		$sp_sampleSent->setRowTitle("sample_forwarded_by_scapa");
		$typeOfComplaintGroup->add($sp_sampleSent);

		$sp_sampleSentDate = new readonly("sp_sampleSentDate");
		$sp_sampleSentDate->setGroup("typeOfComplaintGroup");
		$sp_sampleSentDate->setDataType("date");
		$sp_sampleSentDate->setRowTitle("sample_date");
		$sp_sampleSentDate->setTable("complaintExternal");
		$sp_sampleSentDate->setHelpId(10017);
		$typeOfComplaintGroup->add($sp_sampleSentDate);

//		$sampleReceivedDate = new textbox("sampleReceivedDate");
//		$sampleReceivedDate->setGroup("typeOfComplaintGroup");
//		$sampleReceivedDate->setDataType("date");
//		$sampleReceivedDate->setRowTitle("sample_received_date");
//		$sampleReceivedDate->setTable("complaintExternal");
//		$sampleReceivedDate->setHelpId(8199);
//		$typeOfComplaintGroup->add($sampleReceivedDate);

//		$containmentAction = new readonly("containmentAction");
//		$containmentAction->setGroup("typeOfComplaintGroup");
//		$containmentAction->setDataType("string");
//		$containmentAction->setRowTitle("actions_by_scapa_to_minimise_problem");
//		$containmentAction->setTable("complaintExternal");
//		$containmentAction->setHelpId(10019);
//		$typeOfComplaintGroup->add($containmentAction);

		$actionRequested = new readonly("actionRequested");
		$actionRequested->setGroup("typeOfComplaintGroup");
		$actionRequested->setDataType("string");
		$actionRequested->setRowTitle("actions_by_scapa_to_minimise_problem");
		$actionRequested->setTable("complaintExternal");
		$actionRequested->setHelpId(10019);
		$typeOfComplaintGroup->add($actionRequested);

		$actionRequestedFromSupplier = new readonly("actionRequestedFromSupplier");
		$actionRequestedFromSupplier->setGroup("typeOfComplaintGroup");
		$actionRequestedFromSupplier->setDataType("string");
		$actionRequestedFromSupplier->setRowTitle("actions_requested_from_supplier");
		$actionRequestedFromSupplier->setTable("complaintExternal");
		$actionRequestedFromSupplier->setHelpId(10019);
		$typeOfComplaintGroup->add($actionRequestedFromSupplier);

		$teamLeader = new textbox("teamLeader");
		$teamLeader->setGroup("typeOfComplaintGroup");
		$teamLeader->setDataType("string");
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setRowTitle("person_responsible");
		$teamLeader->setTable("complaintExternal");
		$teamLeader->setHelpId(10018);
		$typeOfComplaintGroup->add($teamLeader);

		$teamLeaderReadOnly = new readonly("teamLeaderReadOnly");
		$teamLeaderReadOnly->setGroup("typeOfComplaintGroup");
		$teamLeaderReadOnly->setDataType("text");
		$teamLeaderReadOnly->setLength(255);
		$teamLeaderReadOnly->setLabel("3 - Immediate Supplier Actions");
		$teamLeaderReadOnly->setRowTitle("person_responsible");
		$teamLeaderReadOnly->setVisible(false);
		$teamLeaderReadOnly->setHelpId(9037);
		$typeOfComplaintGroup->add($teamLeaderReadOnly);

		$verificationOfStock = new radio("verificationOfStock");
		$verificationOfStock->setGroup("typeOfComplaintGroup");
		$verificationOfStock->setDataType("string");
		$verificationOfStock->setRowTitle("verification_of_stock");
		$verificationOfStock->setRequired(false);
		$verificationOfStock->setLabel("3B - Verification Of Stock");
		$verificationOfStock->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$verificationOfStock->setTable("complaintExternal");
		$verificationOfStock->setHelpId(10022);
		$verificationOfStock->setValue("YES");

		// Dependency
		$verificationOfStock_dependency = new dependency();
		$verificationOfStock_dependency->addRule(new rule('typeOfComplaintGroup', 'verificationOfStock', 'YES'));
		$verificationOfStock_dependency->setGroup('verificationOfStockYes');
		$verificationOfStock_dependency->setShow(true);

		$verificationOfStock2_dependency = new dependency();
		$verificationOfStock2_dependency->addRule(new rule('typeOfComplaintGroup', 'verificationOfStock', 'YES'));
		$verificationOfStock2_dependency->setGroup('warehouseGroup');
		$verificationOfStock2_dependency->setShow(true);

		$verificationOfStock3_dependency = new dependency();
		$verificationOfStock3_dependency->addRule(new rule('typeOfComplaintGroup', 'verificationOfStock', 'YES'));
		$verificationOfStock3_dependency->setGroup('productionGroup');
		$verificationOfStock3_dependency->setShow(true);

		$verificationOfStock4_dependency = new dependency();
		$verificationOfStock4_dependency->addRule(new rule('typeOfComplaintGroup', 'verificationOfStock', 'YES'));
		$verificationOfStock4_dependency->setGroup('transitGroup');
		$verificationOfStock4_dependency->setShow(true);

		$verificationOfStock->addControllingDependency($verificationOfStock_dependency);
		$verificationOfStock->addControllingDependency($verificationOfStock2_dependency);
		$verificationOfStock->addControllingDependency($verificationOfStock3_dependency);
		$verificationOfStock->addControllingDependency($verificationOfStock4_dependency);
		$typeOfComplaintGroup->add($verificationOfStock);

		$verificationOfStockReadOnly = new readonly("verificationOfStockReadOnly");
		$verificationOfStockReadOnly->setGroup("typeOfComplaintGroup");
		$verificationOfStockReadOnly->setDataType("text");
		$verificationOfStockReadOnly->setLength(255);
		$verificationOfStockReadOnly->setRowTitle("verification_of_stock");
		$verificationOfStockReadOnly->setLabel("3B - Verification Of Stock");
		$verificationOfStockReadOnly->setVisible(false);
		$verificationOfStockReadOnly->setHelpId(9037);
		$typeOfComplaintGroup->add($verificationOfStockReadOnly);


		$warehouse = new radio("warehouse");
		$warehouse->setGroup("warehouseGroup");
		$warehouse->setDataType("string");
		$warehouse->setRowTitle("warehouse");
		$warehouse->setRequired(false);
		$warehouse->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$warehouse->setTable("complaintExternal");
		$warehouse->setHelpId(10022);
		$warehouse->setValue("NO");

		// Dependency
		$warehouse_dependency = new dependency();
		$warehouse_dependency->addRule(new rule('warehouseGroup', 'warehouse', 'YES'));
		$warehouse_dependency->setGroup('warehouseYes');
		$warehouse_dependency->setShow(true);

		$warehouse->addControllingDependency($warehouse_dependency);
		$warehouseGroup->add($warehouse);

		$warehouseDate = new textbox("warehouseDate");
		$warehouseDate->setGroup("warehouseYes");
		$warehouseDate->setDataType("date");
		$warehouseDate->setRowTitle("warehouse_stock_date");
		$warehouseDate->setTable("complaintExternal");
		$warehouseDate->setHelpId(10018);
		$warehouseYes->add($warehouseDate);

		$defectQuantity = new measurement("defectQuantity");
		$defectQuantity->setGroup("warehouseYes");
		$defectQuantity->setDataType("string");
		$defectQuantity->setRowTitle("defect_quantity");
		$defectQuantity->setXMLSource("./apps/complaintsExternal/xml/uom.xml");
		$defectQuantity->setTable("complaintExternal");
		$defectQuantity->setHelpId(10018);
		$warehouseYes->add($defectQuantity);

		$productionRadio = new radio("productionRadio");
		$productionRadio->setGroup("productionGroup");
		$productionRadio->setDataType("string");
		$productionRadio->setRowTitle("production");
		$productionRadio->setRequired(false);
		$productionRadio->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$productionRadio->setTable("complaintExternal");
		$productionRadio->setHelpId(10022);
		$productionRadio->setValue("NO");

		// Dependency
		$productionRadio_dependency = new dependency();
		$productionRadio_dependency->addRule(new rule('productionGroup', 'productionRadio', 'YES'));
		$productionRadio_dependency->setGroup('productionYes');
		$productionRadio_dependency->setShow(true);

		$productionRadio->addControllingDependency($productionRadio_dependency);
		$productionGroup->add($productionRadio);

		$productionDate = new textbox("productionDate");
		$productionDate->setGroup("productionYes");
		$productionDate->setDataType("date");
		$productionDate->setRowTitle("production_date");
		$productionDate->setTable("complaintExternal");
		$productionDate->setHelpId(10018);
		$productionYes->add($productionDate);

		$defectQuantity2 = new measurement("defectQuantity2");
		$defectQuantity2->setGroup("productionYes");
		$defectQuantity2->setDataType("string");
		$defectQuantity2->setRowTitle("defect_quantity");
		$defectQuantity2->setXMLSource("./apps/complaintsExternal/xml/uom.xml");
		$defectQuantity2->setTable("complaintExternal");
		$defectQuantity2->setHelpId(10018);
		$productionYes->add($defectQuantity2);

		$transitRadio = new radio("transitRadio");
		$transitRadio->setGroup("transitGroup");
		$transitRadio->setDataType("string");
		$transitRadio->setRowTitle("transit");
		$transitRadio->setRequired(false);
		$transitRadio->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$transitRadio->setTable("complaintExternal");
		$transitRadio->setHelpId(10022);
		$transitRadio->setValue("NO");

		// Dependency
		$transitRadio_dependency = new dependency();
		$transitRadio_dependency->addRule(new rule('transitGroup', 'transitRadio', 'YES'));
		$transitRadio_dependency->setGroup('transitYes');
		$transitRadio_dependency->setShow(true);

		$transitRadio->addControllingDependency($transitRadio_dependency);
		$transitGroup->add($transitRadio);

		$transitDate = new textbox("transitDate");
		$transitDate->setGroup("transitYes");
		$transitDate->setDataType("date");
		$transitDate->setRowTitle("transit_date");
		$transitDate->setTable("complaintExternal");
		$transitDate->setHelpId(10018);
		$transitYes->add($transitDate);

		$defectQuantity3 = new measurement("defectQuantity3");
		$defectQuantity3->setGroup("transitYes");
		$defectQuantity3->setDataType("string");
		$defectQuantity3->setRowTitle("defect_quantity");
		$defectQuantity3->setXMLSource("./apps/complaintsExternal/xml/uom.xml");
		$defectQuantity3->setTable("complaintExternal");
		$defectQuantity3->setHelpId(10018);
		$transitYes->add($defectQuantity3);

		$invoiceDeliveryNote = new textbox("invoiceDeliveryNote");
		$invoiceDeliveryNote->setGroup("transitYes");
		$invoiceDeliveryNote->setDataType("string");
		$invoiceDeliveryNote->setRowTitle("invoice_delivery_note");
		$invoiceDeliveryNote->setTable("complaintExternal");
		$invoiceDeliveryNote->setHelpId(10018);
		$transitYes->add($invoiceDeliveryNote);

		$goodJobInvoiceNo = new textbox("goodJobInvoiceNo");
		$goodJobInvoiceNo->setGroup("typeOfComplaintGroup");
		$goodJobInvoiceNo->setDataType("string");
		$goodJobInvoiceNo->setRowTitle("invoice_no");
		$goodJobInvoiceNo->setLabel("3C - Good Material Information");
		$goodJobInvoiceNo->setTable("complaintExternal");
		$goodJobInvoiceNo->setHelpId(10018);
		$typeOfComplaintGroup1->add($goodJobInvoiceNo);

		$goodJobInvoiceNoReadOnly = new readonly("goodJobInvoiceNoReadOnly");
		$goodJobInvoiceNoReadOnly->setGroup("typeOfComplaintGroup1");
		$goodJobInvoiceNoReadOnly->setDataType("text");
		$goodJobInvoiceNoReadOnly->setLength(255);
		$goodJobInvoiceNoReadOnly->setRowTitle("invoice_no");
		$goodJobInvoiceNoReadOnly->setVisible(false);
		$goodJobInvoiceNoReadOnly->setHelpId(9037);
		$typeOfComplaintGroup1->add($goodJobInvoiceNoReadOnly);

		$deliveryNote = new textbox("deliveryNote");
		$deliveryNote->setGroup("typeOfComplaintGroup");
		$deliveryNote->setDataType("string");
		$deliveryNote->setRowTitle("delivery_note");
		$deliveryNote->setTable("complaintExternal");
		$deliveryNote->setHelpId(10018);
		$typeOfComplaintGroup1->add($deliveryNote);

		$deliveryNoteReadOnly = new readonly("deliveryNoteReadOnly");
		$deliveryNoteReadOnly->setGroup("typeOfComplaintGroup1");
		$deliveryNoteReadOnly->setDataType("text");
		$deliveryNoteReadOnly->setLength(255);
		$deliveryNoteReadOnly->setRowTitle("delivery_note");
		$deliveryNoteReadOnly->setVisible(false);
		$deliveryNoteReadOnly->setHelpId(9037);
		$typeOfComplaintGroup1->add($deliveryNoteReadOnly);

		$containmentActionSupplier = new textarea("containmentActionSupplier");
		if(isset($savedFields["containmentActionSupplier"]))
		$containmentActionSupplier->setValue($savedFields["containmentActionSupplier"]);
		$containmentActionSupplier->setGroup("typeOfComplaintGroup1");
		$containmentActionSupplier->setDataType("text");
		$containmentActionSupplier->setRowTitle("containment_action");
		$containmentActionSupplier->setLabel("Containment Action");
		$containmentActionSupplier->setRequired(true);
		$containmentActionSupplier->setTable("complaintExternal");
		$containmentActionSupplier->setHelpId(9037);
		$typeOfComplaintGroup1->add($containmentActionSupplier);

		$containmentActionSupplieryn = new radio("containmentActionSupplieryn");
		$containmentActionSupplieryn->setGroup("typeOfComplaintGroup1");
		$containmentActionSupplieryn->setDataType("string");
		$containmentActionSupplieryn->setLength(3);
		$containmentActionSupplieryn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$containmentActionSupplieryn->setRowTitle("containmentActionyn");
		$containmentActionSupplieryn->setRequired(false);
		$containmentActionSupplieryn->setVisible(false);
		$containmentActionSupplieryn->setTable("complaintExternal");
		$typeOfComplaintGroup1->add($containmentActionSupplieryn);

		$containmentActionReadOnly = new readonly("containmentActionReadOnly");
		$containmentActionReadOnly->setGroup("typeOfComplaintGroup1");
		$containmentActionReadOnly->setDataType("text");
		$containmentActionReadOnly->setLength(255);
		//$containmentAction->setRowTitle("containment_action");
		$containmentActionReadOnly->setRowTitle("containment_action");
		$containmentActionReadOnly->setLabel("Containment Action");
		$containmentActionReadOnly->setVisible(false);
		$containmentActionReadOnly->setHelpId(9037);
		$typeOfComplaintGroup1->add($containmentActionReadOnly);

		$authorName = new textbox("authorName");
		$authorName->setGroup("typeOfComplaintGroup1");
		$authorName->setDataType("string");
		$authorName->setRowTitle("author_name");
		$authorName->setTable("complaintExternal");
		$authorName->setHelpId(10018);
		$typeOfComplaintGroup1->add($authorName);

		$authorNameReadOnly = new readonly("authorNameReadOnly");
		$authorNameReadOnly->setGroup("typeOfComplaintGroup1");
		$authorNameReadOnly->setDataType("text");
		$authorNameReadOnly->setLength(255);
		$authorNameReadOnly->setRowTitle("author_name");
		$authorNameReadOnly->setVisible(false);
		$authorNameReadOnly->setHelpId(9037);
		$typeOfComplaintGroup1->add($authorNameReadOnly);

		$authorDate = new textbox("authorDate");
		$authorDate->setGroup("typeOfComplaintGroup1");
		$authorDate->setDataType("date");
		$authorDate->setRowTitle("author_date");
		$authorDate->setTable("complaintExternal");
		$authorDate->setHelpId(10018);
		$typeOfComplaintGroup1->add($authorDate);

		$authorDateReadOnly = new readonly("authorDateReadOnly");
		$authorDateReadOnly->setGroup("typeOfComplaintGroup1");
		$authorDateReadOnly->setDataType("text");
		$authorDateReadOnly->setLength(255);
		$authorDateReadOnly->setRowTitle("author_date");
		$authorDateReadOnly->setVisible(false);
		$authorDateReadOnly->setHelpId(9037);
		$typeOfComplaintGroup1->add($authorDateReadOnly);

		$confirmCollectionOfGoods = new textbox("confirmCollectionOfGoods");
		$confirmCollectionOfGoods->setGroup("typeOfComplaintGroup1");
		$confirmCollectionOfGoods->setDataType("date");
		$confirmCollectionOfGoods->setRowTitle("specified_date_for_collection_of_goods");
		$confirmCollectionOfGoods->setTable("complaintExternal");
		$confirmCollectionOfGoods->setHelpId(10059345435);
		$typeOfComplaintGroup1->add($confirmCollectionOfGoods);

		$confirmCollectionOfGoodsInstructions = new textarea("confirmCollectionOfGoodsInstructions");
		$confirmCollectionOfGoodsInstructions->setGroup("typeOfComplaintGroup1");
		$confirmCollectionOfGoodsInstructions->setDataType("text");
		$confirmCollectionOfGoodsInstructions->setRowTitle("collection_instructions");
		$confirmCollectionOfGoodsInstructions->setTable("complaintExternal");
		$confirmCollectionOfGoodsInstructions->setHelpId(1005997897);
		$typeOfComplaintGroup1->add($confirmCollectionOfGoodsInstructions);

		$submitFirst = new submit("submitFirst");
		$submitFirst->setGroup("typeOfComplaintGroup1");
		$submitFirst->setVisible(true);
		$typeOfComplaintGroup1->add($submitFirst);

		$analysis = new textarea("analysis");
		$analysis->setGroup("typeOfComplaintGroup11");
		$analysis->setDataType("text");
		$analysis->setRowTitle("analysis");
		$analysis->setLabel("4 - Evaluation and Action");
		$analysis->setTable("complaintExternal");
		$analysis->setHelpId(10020);
		$typeOfComplaintGroup11->add($analysis);

		$analysisyn = new radio("analysisyn");
		$analysisyn->setGroup("typeOfComplaintGroup11");
		$analysisyn->setDataType("string");
		$analysisyn->setLength(3);
		$analysisyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$analysisyn->setRowTitle("analysis_entered");
		$analysisyn->setRequired(false);
		$analysisyn->setVisible(false);
		if(isset($savedFields["analysis_entered"]))
		$analysisyn->setValue($savedFields["analysis_entered"]);
		else $analysisyn->setValue("No");
		$analysisyn->setTable("complaintExternal");
		$typeOfComplaintGroup11->add($analysisyn);

		$nameOfAnalysis = new textbox("nameOfAnalysis");
		$nameOfAnalysis->setGroup("typeOfComplaintGroup11");
		$nameOfAnalysis->setDataType("string");
		$nameOfAnalysis->setRowTitle("name");
		$nameOfAnalysis->setTable("complaintExternal");
		$nameOfAnalysis->setHelpId(10026);
		$typeOfComplaintGroup11->add($nameOfAnalysis);

		$dateOfAnalysis = new textbox("dateOfAnalysis");
		$dateOfAnalysis->setGroup("typeOfComplaintGroup11");
		$dateOfAnalysis->setDataType("date");
		$dateOfAnalysis->setRowTitle("team_leader");
		$dateOfAnalysis->setRowTitle("date");
		$dateOfAnalysis->setTable("complaintExternal");
		$dateOfAnalysis->setHelpId(10026);
		$typeOfComplaintGroup11->add($dateOfAnalysis);

		$complaintJustified = new radio("complaintJustified");
		$complaintJustified->setGroup("typeOfComplaintGroup");
		$complaintJustified->setDataType("string");
		$complaintJustified->setRowTitle("complaint_accepted");
		$complaintJustified->setRequired(false);
		$complaintJustified->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$complaintJustified->setTable("complaintExternal");
		$complaintJustified->setHelpId(10022);
		$complaintJustified->setValue("YES");

		// Dependency
		$complaintJustified_dependency = new dependency();
		$complaintJustified_dependency->addRule(new rule('typeOfComplaintGroup11', 'complaintJustified', 'NO'));
		$complaintJustified_dependency->setGroup('complaintJustifiedNoGroup');
		$complaintJustified_dependency->setShow(false);

		$complaintJustified->addControllingDependency($complaintJustified_dependency);
		$typeOfComplaintGroup11->add($complaintJustified);

		// Justify = No
		$returnGoods = new radio("returnGoods");
		$returnGoods->setGroup("complaintJustifiedNoGroup");
		$returnGoods->setDataType("string");
		$returnGoods->setRowTitle("return_goods");
		$returnGoods->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$returnGoods->setTable("complaintExternal");
		$returnGoods->setRequired(false);
		$returnGoods->setHelpId(10023);
		$returnGoods->setValue("NO");
		$complaintJustifiedNoGroup->add($returnGoods);

		// Justify = No
		$disposeGoods = new radio("disposeGoods");
		$disposeGoods->setGroup("complaintJustifiedNoGroup");
		$disposeGoods->setDataType("string");
		$disposeGoods->setRowTitle("dispose_goods");
		$disposeGoods->setRequired(false);
		$disposeGoods->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$disposeGoods->setTable("complaintExternal");
		$disposeGoods->setValue("NO");
		$disposeGoods->setHelpId(10024);
		$complaintJustifiedNoGroup->add($disposeGoods);

		// Justify = No
		$sp_materialCredited = new radio("sp_materialCredited");
		$sp_materialCredited->setGroup("complaintJustifiedNoGroup");
		$sp_materialCredited->setDataType("string");
		$sp_materialCredited->setLength(5);
		$sp_materialCredited->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_materialCredited->setRowTitle("material_credited");
		$sp_materialCredited->setRequired(false);
		if(isset($savedFields["sp_materialCredited"]))
			$sp_materialCredited->setValue($savedFields["sp_materialCredited"]);
		else $sp_materialCredited->setValue("NO");
		$sp_materialCredited->setTable("complaintExternal");
		$sp_materialCredited->setHelpId(9018);
		$complaintJustifiedNoGroup->add($sp_materialCredited);

		// Justify = No
		$sp_materialReplaced = new radio("sp_materialReplaced");
		$sp_materialReplaced->setGroup("complaintJustifiedNoGroup");
		$sp_materialReplaced->setDataType("string");
		$sp_materialReplaced->setLength(5);
		$sp_materialReplaced->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_materialReplaced->setRowTitle("material_replaced");
		$sp_materialReplaced->setRequired(false);
		if(isset($savedFields["sp_materialReplaced"]))
			$sp_materialReplaced->setValue($savedFields["sp_materialReplaced"]);
		else $sp_materialReplaced->setValue("NO");
		$sp_materialReplaced->setTable("complaintExternal");
		$sp_materialReplaced->setHelpId(9018);
		$complaintJustifiedNoGroup->add($sp_materialReplaced);

		$sp_useGoods = new radio("sp_useGoods");
		$sp_useGoods->setGroup("complaintJustifiedNoGroup");
		$sp_useGoods->setDataType("string");
		$sp_useGoods->setRowTitle("use_goods");
		$sp_useGoods->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$sp_useGoods->setTable("complaintExternal");
		$sp_useGoods->setRequired(false);
		$sp_useGoods->setHelpId(10023);
		$sp_useGoods->setValue("NO");
		$complaintJustifiedNoGroup->add($sp_useGoods);

		$sp_reworkGoods = new radio("sp_reworkGoods");
		$sp_reworkGoods->setGroup("complaintJustifiedNoGroup");
		$sp_reworkGoods->setDataType("string");
		$sp_reworkGoods->setRowTitle("rework_goods");
		$sp_reworkGoods->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$sp_reworkGoods->setTable("complaintExternal");
		$sp_reworkGoods->setRequired(false);
		$sp_reworkGoods->setHelpId(10023);
		$sp_reworkGoods->setValue("NO");
		$complaintJustifiedNoGroup->add($sp_reworkGoods);

		$sp_sortGoods = new radio("sp_sortGoods");
		$sp_sortGoods->setGroup("complaintJustifiedNoGroup");
		$sp_sortGoods->setDataType("string");
		$sp_sortGoods->setRowTitle("sort_goods");
		$sp_sortGoods->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
		));
		$sp_sortGoods->setTable("complaintExternal");
		$sp_sortGoods->setRequired(false);
		$sp_sortGoods->setHelpId(10023);
		$sp_sortGoods->setValue("NO");
		$complaintJustifiedNoGroup->add($sp_sortGoods);

//		$immediateAction = new textarea("immediateAction");
//		$immediateAction->setGroup("typeOfComplaintGroup");
//		$immediateAction->setDataType("string");
//		$immediateAction->setRowTitle("immediate_action");
//		$immediateAction->setTable("complaintExternal");
//		$immediateAction->setHelpId(10019);
//		$typeOfComplaintGroup->add($immediateAction);

//		$stockVerificationMade = new radio("stockVerificationMade");
//		$stockVerificationMade->setTable("complaintExternal");
//		$stockVerificationMade->setLabel("3 - Containment Actions");
//		$stockVerificationMade->setLength(20);
//		$stockVerificationMade->setArraySource(array(
//			array('value' => 'yes', 'display' => 'Yes'),
//			array('value' => 'no', 'display' => 'No')
//		));
//		if(isset($savedFields["stockVerificationMade"]))
//			$stockVerificationMade->setValue($savedFields["stockVerificationMade"]);
//		$stockVerificationMade->setDataType("string");
//		$stockVerificationMade->setRowTitle("stock_verification_made");
//		$typeOfComplaintGroup->add($stockVerificationMade);

//		$stockVerificationComment = new textarea("stockVerificationComment");
//		$stockVerificationComment->setGroup("typeOfComplaintGroup");
//		$stockVerificationComment->setDataType("string");
//		$stockVerificationComment->setRowTitle("stock_verification_comment");
//		$stockVerificationComment->setTable("complaintExternal");
//		$stockVerificationComment->setHelpId(8199);
//		$typeOfComplaintGroup->add($stockVerificationComment);


		// Justify = No
		$rootCauses = new textarea("rootCauses");
		$rootCauses->setGroup("complaintJustifiedNoGroup");
		$rootCauses->setDataType("text");
		$rootCauses->setRowTitle("root_cause");
		$rootCauses->setTable("complaintExternal");
		$rootCauses->setHelpId(10021);
		$complaintJustifiedNoGroup->add($rootCauses);

		$rootCausesyn = new radio("rootCausesyn");
		$rootCausesyn->setGroup("complaintJustifiedNoGroup");
		$rootCausesyn->setDataType("string");
		$rootCausesyn->setLength(3);
		$rootCausesyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$rootCausesyn->setRowTitle("analysis_entered");
		$rootCausesyn->setRequired(false);
		$rootCausesyn->setVisible(false);
		if(isset($savedFields["analysis_entered"]))
		$rootCausesyn->setValue($savedFields["analysis_entered"]);
		else $rootCausesyn->setValue("No");
		$rootCausesyn->setTable("complaintExternal");
		$complaintJustifiedNoGroup->add($rootCausesyn);

		// Justify = No
		$rootCausesAuthor = new textbox("rootCausesAuthor");
		if(isset($savedFields["rootCausesAuthor"]))
		$rootCausesAuthor->setValue($savedFields["rootCausesAuthor"]);
		$rootCausesAuthor->setGroup("complaintJustifiedNoGroup");
		$rootCausesAuthor->setDataType("string");
		$rootCausesAuthor->setLength(255);
		$rootCausesAuthor->setRowTitle("root_cause_author");
		$rootCausesAuthor->setRequired(false);
		$rootCausesAuthor->setTable("complaintExternal");
		$rootCausesAuthor->setHelpId(9037);
		$complaintJustifiedNoGroup->add($rootCausesAuthor);

		// Justify = No
		$rootCausesDate = new textbox("rootCausesDate");
		if(isset($savedFields["rootCausesDate"]))
		$rootCausesDate->setValue($savedFields["rootCausesDate"]);
		$rootCausesDate->setGroup("complaintJustifiedNoGroup");
		$rootCausesDate->setDataType("date");
		$rootCausesDate->setLength(255);
		$rootCausesDate->setRowTitle("root_cause_date");
		$rootCausesDate->setRequired(false);
		$rootCausesDate->setTable("complaintExternal");
		$rootCausesDate->setHelpId(9037);
		$complaintJustifiedNoGroup->add($rootCausesDate);

		// Justify = No
		$possibleSolutions = new textarea("possibleSolutions");
		$possibleSolutions->setGroup("complaintJustifiedNoGroup");
		$possibleSolutions->setDataType("text");
		$possibleSolutions->setRowTitle("team_leader");
		$possibleSolutions->setRowTitle("possible_solutions");
		$possibleSolutions->setTable("complaintExternal");
		$possibleSolutions->setHelpId(10028);
		$complaintJustifiedNoGroup->add($possibleSolutions);

		$possibleSolutionsyn = new radio("possibleSolutionsyn");
		$possibleSolutionsyn->setGroup("complaintJustifiedNoGroup");
		$possibleSolutionsyn->setDataType("string");
		$possibleSolutionsyn->setLength(3);
		$possibleSolutionsyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$possibleSolutionsyn->setRowTitle("possible_solutions_entered");
		$possibleSolutionsyn->setRequired(false);
		$possibleSolutionsyn->setVisible(false);
		if(isset($savedFields["analysis_entered"]))
		$possibleSolutionsyn->setValue($savedFields["analysis_entered"]);
		else $possibleSolutionsyn->setValue("No");
		$possibleSolutionsyn->setTable("complaintExternal");
		$complaintJustifiedNoGroup->add($possibleSolutionsyn);

		// Justify = No
		$possibleSolutionsAuthor = new textbox("possibleSolutionsAuthor");
		if(isset($savedFields["possibleSolutionsAuthor"]))
		$possibleSolutionsAuthor->setValue($savedFields["possibleSolutionsAuthor"]);
		$possibleSolutionsAuthor->setGroup("complaintJustifiedNoGroup");
		$possibleSolutionsAuthor->setDataType("string");
		$possibleSolutionsAuthor->setLength(255);
		$possibleSolutionsAuthor->setRowTitle("possible_solutions_author");
		$possibleSolutionsAuthor->setRequired(false);
		$possibleSolutionsAuthor->setTable("complaintExternal");
		$possibleSolutionsAuthor->setHelpId(9037);
		$complaintJustifiedNoGroup->add($possibleSolutionsAuthor);

		// Justify = No
		$possibleSolutionsDate = new textbox("possibleSolutionsDate");
		if(isset($savedFields["possibleSolutionsDate"]))
		$possibleSolutionsDate->setValue($savedFields["possibleSolutionsDate"]);
		$possibleSolutionsDate->setGroup("complaintJustifiedNoGroup");
		$possibleSolutionsDate->setDataType("date");
		$possibleSolutionsDate->setLength(255);
		$possibleSolutionsDate->setRowTitle("possible_solutions_date");
		$possibleSolutionsDate->setRequired(false);
		$possibleSolutionsDate->setTable("complaintExternal");
		$possibleSolutionsDate->setHelpId(9037);
		$complaintJustifiedNoGroup->add($possibleSolutionsDate);

		// Justify = No
		$implementedActions = new textarea("implementedActions");
		$implementedActions->setGroup("complaintJustifiedNoGroup");
		$implementedActions->setDataType("text");
		$implementedActions->setRowTitle("team_leader");
		$implementedActions->setRowTitle("implemented_perm_corr_actions");
		$implementedActions->setTable("complaintExternal");
		$implementedActions->setHelpId(10029);
		$complaintJustifiedNoGroup->add($implementedActions);

		$implementedActionsyn = new radio("implementedActionsyn");
		$implementedActionsyn->setGroup("complaintJustifiedNoGroup");
		$implementedActionsyn->setDataType("string");
		$implementedActionsyn->setLength(3);
		$implementedActionsyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$implementedActionsyn->setRowTitle("rootcauses_entered");
		$implementedActionsyn->setRequired(false);
		$implementedActionsyn->setVisible(false);
		if(isset($savedFields["analysis_entered"]))
		$implementedActionsyn->setValue($savedFields["analysis_entered"]);
		else $implementedActionsyn->setValue("No");
		$implementedActionsyn->setTable("complaintExternal");
		$complaintJustifiedNoGroup->add($implementedActionsyn);

		// Justify = No
		$implementedActionsAuthor = new textbox("implementedActionsAuthor");
		if(isset($savedFields["implementedActionsAuthor"]))
		$implementedActionsAuthor->setValue($savedFields["implementedActionsAuthor"]);
		$implementedActionsAuthor->setGroup("complaintJustifiedNoGroup");
		$implementedActionsAuthor->setDataType("string");
		$implementedActionsAuthor->setLength(255);
		$implementedActionsAuthor->setRowTitle("implemented_actions_author");
		$implementedActionsAuthor->setRequired(false);
		$implementedActionsAuthor->setTable("complaintExternal");
		$implementedActionsAuthor->setHelpId(9021);
		$complaintJustifiedNoGroup->add($implementedActionsAuthor);

		// Justify = No
		$implementedActionsDate = new textbox("implementedActionsDate");
		if(isset($savedFields["implementedActionsDate"]))
		$implementedActionsDate->setValue($savedFields["implementedActionsDate"]);
		$implementedActionsDate->setGroup("complaintJustifiedNoGroup");
		$implementedActionsDate->setDataType("date");
		$implementedActionsDate->setLength(255);
		$implementedActionsDate->setRowTitle("implemented_actions_date_verified");
		$implementedActionsDate->setRequired(false);
		$implementedActionsDate->setTable("complaintExternal");
		$implementedActionsDate->setHelpId(9022);
		$complaintJustifiedNoGroup->add($implementedActionsDate);

//		$implementedActionsEstimated = new textbox("implementedActionsEstimated");
//		$implementedActionsEstimated->setGroup("typeOfComplaintGroup");
//		$implementedActionsEstimated->setDataType("date");
//		$implementedActionsEstimated->setRowTitle("estimated_date_imp");
//		$implementedActionsEstimated->setTable("complaintExternal");
//		$implementedActionsEstimated->setHelpId(10030);
//		$typeOfComplaintGroup->add($implementedActionsEstimated);
//
//		$implementedActionsImplementation = new textbox("implementedActionsImplementation");
//		$implementedActionsImplementation->setGroup("typeOfComplaintGroup");
//		$implementedActionsImplementation->setDataType("date");
//		$implementedActionsImplementation->setRowTitle("team_leader");
//		$implementedActionsImplementation->setRowTitle("implemented_date_imp");
//		$implementedActionsImplementation->setTable("complaintExternal");
//		$implementedActionsImplementation->setHelpId(10031);
//		$typeOfComplaintGroup->add($implementedActionsImplementation);
//
//		$implementedActionsEffectiveness = new textbox("implementedActionsEffectiveness");
//		$implementedActionsEffectiveness->setGroup("typeOfComplaintGroup");
//		$implementedActionsEffectiveness->setDataType("date");
//		$implementedActionsEffectiveness->setRowTitle("team_leader");
//		$implementedActionsEffectiveness->setRowTitle("val_eff_date_imp");
//		$implementedActionsEffectiveness->setTable("complaintExternal");
//		$implementedActionsEffectiveness->setHelpId(10032);
//		$typeOfComplaintGroup->add($implementedActionsEffectiveness);

		// Justify = No
		$preventivePermCorrActions = new textarea("preventivePermCorrActions");
		$preventivePermCorrActions->setGroup("complaintJustifiedNoGroup");
		$preventivePermCorrActions->setDataType("text");
		$preventivePermCorrActions->setRowTitle("team_leader");
		$preventivePermCorrActions->setRowTitle("preventive_actions");
		$preventivePermCorrActions->setTable("complaintExternal");
		$preventivePermCorrActions->setHelpId(10033);
		$complaintJustifiedNoGroup->add($preventivePermCorrActions);

		$preventiveActionsyn = new radio("preventiveActionsyn");
		$preventiveActionsyn->setGroup("complaintJustifiedNoGroup");
		$preventiveActionsyn->setDataType("string");
		$preventiveActionsyn->setLength(3);
		$preventiveActionsyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$preventiveActionsyn->setRowTitle("preventive_entered");
		$preventiveActionsyn->setRequired(false);
		$preventiveActionsyn->setVisible(false);
		if(isset($savedFields["analysis_entered"]))
		$preventiveActionsyn->setValue($savedFields["analysis_entered"]);
		else $preventiveActionsyn->setValue("No");
		$preventiveActionsyn->setTable("complaintExternal");
		$complaintJustifiedNoGroup->add($preventiveActionsyn);

		// Justify = No
		$estimatedDatePrev = new textbox("estimatedDatePrev");
		$estimatedDatePrev->setGroup("complaintJustifiedNoGroup");
		$estimatedDatePrev->setDataType("date");
		$estimatedDatePrev->setRowTitle("team_leader");
		$estimatedDatePrev->setRowTitle("preventive_action_verified_date");
		$estimatedDatePrev->setTable("complaintExternal");
		$estimatedDatePrev->setHelpId(10034);
		$complaintJustifiedNoGroup->add($estimatedDatePrev);

		/*$implementedPermanentCorrectiveActionValidated = new textarea("implementedPermanentCorrectiveActionValidated");
		/*$implementedPermanentCorrectiveActionValidated = new textarea("implementedPermanentCorrectiveActionValidated");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidated"]))
		$implementedPermanentCorrectiveActionValidated->setValue($savedFields["implementedPermanentCorrectiveActionValidated"]);
		$implementedPermanentCorrectiveActionValidated->setGroup("complaintJustifiedNoGroup");
		$implementedPermanentCorrectiveActionValidated->setDataType("text");
		$implementedPermanentCorrectiveActionValidated->setRowTitle("implemented_permanent_corrective_action_validated");
		$implementedPermanentCorrectiveActionValidated->setRequired(false);
		$implementedPermanentCorrectiveActionValidated->setTable("complaintExternal");
		$implementedPermanentCorrectiveActionValidated->setHelpId(90243453451);
		$complaintJustifiedNoGroup->add($implementedPermanentCorrectiveActionValidated);

		$implementedPermanentCorrectiveActionValidatedyn = new radio("implementedPermanentCorrectiveActionValidatedyn");
		$implementedPermanentCorrectiveActionValidatedyn->setGroup("complaintJustifiedNoGroup");
		$implementedPermanentCorrectiveActionValidatedyn->setDataType("string");
		$implementedPermanentCorrectiveActionValidatedyn->setLength(3);
		$implementedPermanentCorrectiveActionValidatedyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$implementedPermanentCorrectiveActionValidatedyn->setRowTitle("implemented_permanent_corrective_action_validated");
		$implementedPermanentCorrectiveActionValidatedyn->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedyn->setVisible(false);
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedyn"]))
		$implementedPermanentCorrectiveActionValidatedyn->setValue($savedFields["implementedPermanentCorrectiveActionValidatedyn"]);
		else $implementedPermanentCorrectiveActionValidatedyn->setValue("No");
		$implementedPermanentCorrectiveActionValidatedyn->setTable("complaintExternal");
		$complaintJustifiedNoGroup->add($implementedPermanentCorrectiveActionValidatedyn);

		$implementedPermanentCorrectiveActionValidatedAuthor = new textbox("implementedPermanentCorrectiveActionValidatedAuthor");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]))
		$implementedPermanentCorrectiveActionValidatedAuthor->setValue($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]);
		$implementedPermanentCorrectiveActionValidatedAuthor->setGroup("complaintJustifiedNoGroup");
		$implementedPermanentCorrectiveActionValidatedAuthor->setDataType("string");
		$implementedPermanentCorrectiveActionValidatedAuthor->setLength(255);
		$implementedPermanentCorrectiveActionValidatedAuthor->setRowTitle("implemented_permanent_corrective_action_validated_author");
		$implementedPermanentCorrectiveActionValidatedAuthor->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedAuthor->setTable("complaintExternal");
		$implementedPermanentCorrectiveActionValidatedAuthor->setHelpId(90243453452);
		$complaintJustifiedNoGroup->add($implementedPermanentCorrectiveActionValidatedAuthor);

		$implementedPermanentCorrectiveActionValidatedDate = new calendar("implementedPermanentCorrectiveActionValidatedDate");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedDate"]))
		$implementedPermanentCorrectiveActionValidatedDate->setValue($savedFields["implementedPermanentCorrectiveActionValidatedDate"]);
		$implementedPermanentCorrectiveActionValidatedDate->setGroup("complaintJustifiedNoGroup");
		$implementedPermanentCorrectiveActionValidatedDate->setDataType("date");
		$implementedPermanentCorrectiveActionValidatedDate->setErrorMessage("textbox_date_error");
		$implementedPermanentCorrectiveActionValidatedDate->setLength(255);
		$implementedPermanentCorrectiveActionValidatedDate->setRowTitle("implemented_permanent_corrective_action_date");
		$implementedPermanentCorrectiveActionValidatedDate->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedDate->setTable("complaintExternal");//was complaintEval?????
		$implementedPermanentCorrectiveActionValidatedDate->setHelpId(90243453453);
		$complaintJustifiedNoGroup->add($implementedPermanentCorrectiveActionValidatedDate);*/

//		$implementationDatePrev = new textbox("implementationDatePrev");
//		$implementationDatePrev->setGroup("typeOfComplaintGroup");
//		$implementationDatePrev->setDataType("date");
//		$implementationDatePrev->setRowTitle("team_leader");
//		$implementationDatePrev->setRowTitle("implementation_date_prev");
//		$implementationDatePrev->setTable("complaintExternal");
//		$implementationDatePrev->setHelpId(10035);
//		$typeOfComplaintGroup->add($implementationDatePrev);
//
//		$valEffDatePrev = new textbox("valEffDatePrev");
//		$valEffDatePrev->setGroup("typeOfComplaintGroup");
//		$valEffDatePrev->setDataType("date");
//		$valEffDatePrev->setRowTitle("team_leader");
//		$valEffDatePrev->setRowTitle("val_eff_date_prev");
//		$valEffDatePrev->setTable("complaintExternal");
//		$valEffDatePrev->setHelpId(10036);
//		$typeOfComplaintGroup->add($valEffDatePrev);

		$managementSystemReviewed = new radio("managementSystemReviewed");
		$managementSystemReviewed->setTable("complaintExternal");
		$managementSystemReviewed->setLength(20);
		$managementSystemReviewed->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'NA', 'display' => 'N/A')
		));
		if(isset($savedFields["managementSystemReviewed"]))
			$managementSystemReviewed->setValue($savedFields["managementSystemReviewed"]);
		$managementSystemReviewed->setDataType("string");
		$managementSystemReviewed->setHelpId(10037);
		$managementSystemReviewed->setRowTitle("management_system_reviewed");
		$typeOfComplaintGroup2->add($managementSystemReviewed);

		$flowChart = new radio("flowChart");
		$flowChart->setTable("complaintExternal");
		$flowChart->setLength(20);
		$flowChart->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'NA', 'display' => 'N/A')
		));
		if(isset($savedFields["flowChart"]))
			$flowChart->setValue($savedFields["flowChart"]);
		$flowChart->setDataType("string");
		$flowChart->setHelpId(10038);
		$flowChart->setRowTitle("flow_chart");
		$typeOfComplaintGroup2->add($flowChart);

		$fmea = new radio("fmea");
		$fmea->setTable("complaintExternal");
		$fmea->setLength(20);
		$fmea->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'NA', 'display' => 'N/A')
		));
		if(isset($savedFields["fmea"]))
			$fmea->setValue($savedFields["fmea"]);
		$fmea->setDataType("string");
		$fmea->setRowTitle("fmea");
		$fmea->setHelpId(10039);
		$typeOfComplaintGroup2->add($fmea);

		$customerSpecification = new radio("customerSpecification");
		$customerSpecification->setTable("complaintExternal");
		$customerSpecification->setLength(20);
		$customerSpecification->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'NA', 'display' => 'N/A')
		));
		if(isset($savedFields["customerSpecification"]))
			$customerSpecification->setValue($savedFields["customerSpecification"]);
		$customerSpecification->setDataType("string");
		$customerSpecification->setHelpId(10040);
		$customerSpecification->setRowTitle("specifications_customer");
		$typeOfComplaintGroup2->add($customerSpecification);

		$additionalComments = new textarea("additionalComments");
		$additionalComments->setGroup("typeOfComplaintGroup");
		$additionalComments->setDataType("text");
		$additionalComments->setHelpId(10041);
		$additionalComments->setRowTitle("any_additional_comments");
		$additionalComments->setTable("complaintExternal");
		$typeOfComplaintGroup2->add($additionalComments);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);


		$this->form->add($initiation);
		$this->form->add($typeOfComplaintGroup);
		$this->form->add($verificationOfStockYes);
		$this->form->add($warehouseGroup);
		$this->form->add($warehouseYes);
		$this->form->add($productionGroup);
		$this->form->add($productionYes);
		$this->form->add($transitGroup);
		$this->form->add($transitYes);
		$this->form->add($typeOfComplaintGroup1);
		$this->form->add($typeOfComplaintGroup11);
		$this->form->add($complaintJustifiedNoGroup);
		$this->form->add($typeOfComplaintGroup2);
		$this->form->add($sendToUser);

	}

	public function getEmailNotification($owner, $sender, $id, $action)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailMessage>" . utf8_decode($message) . "</emailMessage></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		//$cc = $this->form->get("delegate_owner")->getValue();

		email::send($owner, /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("complaint_action") . " - ID: " . $id), "$email", "");

		return true;
	}
}

?>