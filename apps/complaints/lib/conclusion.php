<?php

/**
 * This is the Complaint Application.
 *
 * This is the conclusion class.  This class is used to conduct the conclusion part of the Complaint process.
 *
 * @package apps
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class conclusion extends complaintsProcess
{
	/**
	 * The constructor, which the Complaints is passed to.
	 *
	 * @param complaints $complaints
	 */
	function __construct($complaint)
	{
		parent::__construct($complaint);

		if(isset($_GET['typeOfComplaint']))
		{
			if($_GET['typeOfComplaint'] == "supplier_complaint")
			{
				$this->defineSupplierForm();
			}
			elseif($_GET['typeOfComplaint'] == "quality_complaint")
			{
				$this->defineQualityForm();
			}
			else
			{
				$this->defineForm();
			}
		}
		else
		{
			if(isset($_REQUEST['id']))
			{
				$complaintTypeID = $_REQUEST['id'];
			}
			else
			{
				if(isset($_REQUEST['complaint']))
				{
					$complaintTypeID = $_REQUEST['complaint'];
				}
				else
				{
					$complaintTypeID = $this->complaint->getId();
				}
			}

			// bodge for saved form
			if(isset($_REQUEST["sfID"]))
			{
				$savedData = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfTypeOfComplaint FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."'");
				$dataRow = mysql_fetch_assoc($savedData);

				$complaintType = $dataRow['sfTypeOfComplaint'];

				if($complaintType == "supplier_complaint")
				{
					$this->defineSupplierForm();
					$this->complaintType = "supplier_complaint";
				}
				elseif($complaintType == "quality_complaint")
				{
					$this->defineQualityForm();
					$this->complaintType = "quality_complaint";
				}
				else
				{
					$this->defineForm();
				}
			}
			else
			{
				if($this->complaint->getComplaintType($complaintTypeID) == "supplier_complaint")
				{
					$this->defineSupplierForm();
					$this->complaintType = "supplier_complaint";
				}
				elseif($this->complaint->getComplaintType($complaintTypeID) == "quality_complaint")
				{
					$this->defineQualityForm();
					$this->complaintType = "quality_complaint";
				}
				else
				{
					$this->defineForm();
				}
			}
		}

		$this->form->get('complaintId')->setValue($this->complaint->getId());

		$this->form->setStoreInSession(true);

		$this->form->loadSessionData();

		if(isset($_SESSION['apps'][$GLOBALS['app']]['conclusion']['loadedFromDatabase']) && isset($_REQUEST['complaint']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the Complaint is loaded from the database
		}

		//$this->complaint->form->get("creditNoteRequested")->getValue() == 'NO' ? $this->form->get("creditNoteValue")->setVisible(false) : "";

		if($this->complaint->getComplaintType($complaintTypeID) == "customer_complaint")
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion WHERE complaintId = '" . $this->complaint->getId() . "'");
			$fields = mysql_fetch_array($dataset);

			if(mysql_num_rows($dataset) == 0)
			{
				$this->form->get("creditNoteValue")->setValue(array($this->complaint->form->get("complaintValue")->getQuantity(), $this->complaint->form->get("complaintValue")->getMeasurement()));
			}
			else
			{
				$this->form->get("creditNoteValue")->setValue(array($fields['creditNoteValue_quantity'], $fields['creditNoteValue_measurement']));
			}
		}

		if($this->complaint->getComplaintType($complaintTypeID) != "quality_complaint")
		{
			$this->form->get("currentComplaintCategory")->setValue($this->complaint->form->get("category")->getValue());
		}

		if($this->complaint->getComplaintType($complaintTypeID) == "supplier_complaint" && $this->complaint->getEvaluation())
		{
		//	$this->form->get("implementedPermanentCorrectiveActionValidatedReadOnly")->setValue($this->complaint->getEvaluation()->form->get("implementedActions")->getValue() ? $this->complaint->getEvaluation()->form->get("implementedActions")->getValue() : "");
		//	$this->form->get("implementedPermanentCorrectiveActionValidatedAuthorReadOnly")->setValue($this->complaint->getEvaluation()->form->get("implementedActionsAuthor")->getValue() ? $this->complaint->getEvaluation()->form->get("implementedActionsAuthor")->getValue() : "");
		//	$this->form->get("implementedPermanentCorrectiveActionValidatedDateReadOnly")->setValue($this->complaint->getEvaluation()->form->get("implementedActionsDate")->getValue() ? $this->complaint->getEvaluation()->form->get("implementedActionsDate")->getValue() : "");
		}

		//$this->form->get("creditNoteValueReadOnly")->setValue($this->form->get("creditNoteValue")->getValue());
		// Set Process Owner in construct ...
		$this->form->get("processOwner3")->setValue(currentuser::getInstance()->getNTLogon());

		$this->form->processDependencies();


	}

	public function lockComplaint($id, $status)
	{
		$nowTimeStamp = strtotime(date('Y-m-d H:i:s', time()));
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET locked = '" . $status . "' WHERE id = " . $id . "");
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET lockedTime = '" . $nowTimeStamp . "' WHERE id = " . $id . "");
	}


	public function load($id)
	{

		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);

		//if(!isset($_REQUEST["sfID"])){//fudge to get round the loading of the form vars
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion INNER JOIN complaint ON conclusion.complaintId=complaint.id WHERE complaintId = "  . $id);
		//}else{
			//$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion INNER JOIN complaint ON conclusion.complaintId=complaint.id WHERE complaintId = 'UNIX_TIMESTAMP(NOW())'");
		//}

		if (mysql_num_rows($dataset) == 1)
		{

			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['conclusion']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			if($this->complaint->getComplaintType($id) == "supplier_complaint")
			{
				// do nothing ...
			}
			elseif($this->complaint->getComplaintType($id) == "quality_complaint")
			{
				// do nothing ...
			}
			else
			{
				$this->form->get("amount")->setValue(array($fields['creditNoteValue_quantity'], $fields['creditNoteValue_measurement']));
			}

//			foreach ($fields as $key => $value)
//			{
//				if ($this->form->get($key))
//				{
//					$this->form->get($key)->setValue($value);
//				}
//			}

			$this->form->populate($fields);

			if(isset($_REQUEST["sfID"])){
				$this->sfID = $_REQUEST["sfID"];
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$_REQUEST["sfID"]."' LIMIT 1");
				while ($fields2 = mysql_fetch_array($dataset)){
					$savedFields = unserialize($fields2["sfValue"]);
				}

				if($savedFields){
					foreach ($savedFields as $key => $value)
					{
						if($value)$fields[$key] = $value;
					}
				}
			}

			// Format and put values in fields
			//retrieve specific info for american fields
			$datasetLocation = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
			$fieldsLocation = mysql_fetch_array($datasetLocation);

			if($this->complaint->getComplaintType($id) == "supplier_complaint")
			{
				$fields['totalClosureDate'] == "0000-00-00" ? $this->form->get("totalClosureDate")->setValue("") : $this->form->get("totalClosureDate")->setValue(page::transformDateForPHP($fields['totalClosureDate']));

				$this->form->get("sp_requestDisposalReadOnly")->setValue($fields['sp_requestDisposal']);
				$this->form->get("sp_amountReadOnly")->setValue($fields['sp_amount_quantity'] . " " . $fields['sp_amount_measurement']);
				$this->form->get("sp_amount")->setValue(array($fields['sp_amount_quantity'], $fields['sp_amount_measurement']));
				$this->form->get("sp_valueReadOnly")->setValue($fields['sp_value_quantity'] . " " . $fields['sp_value_measurement']);
				$this->form->get("sp_value")->setValue(array($fields['sp_value_quantity'], $fields['sp_value_measurement']));
				$this->form->get("sp_requestEmailTextReadOnly")->setValue($fields['sp_requestEmailText']);
				$this->form->get("processOwner3RequestReadOnly")->setValue(usercache::getInstance()->get($fields['processOwner3Request'])->getName());

				$this->form->get("sp_requestAuthorisedReadOnly")->setValue($fields['sp_requestAuthorised']);
				$this->form->get("sp_requestAuthorisedDateReadOnly")->setValue(page::transformDateForPHP($fields['sp_requestAuthorisedDate']));
				$this->form->get("sp_requestAuthorisedNameReadOnly")->setValue(usercache::getInstance()->get($fields['sp_requestAuthorisedName'])->getName());
				$this->form->get("sp_requestAuthorisedEmailTextReadOnly")->setValue($fields['sp_requestAuthorisedEmailText']);
				$this->form->get("sp_requestAuthorisorNameReadOnly")->setValue(usercache::getInstance()->get($fields['sp_requestAuthorisorName'])->getName());
				$this->form->get("sp_supplierCreditNumber")->setValue(array($fields['sp_supplierCreditNumber_quantity'], $fields['sp_supplierCreditNumber_measurement']));
				$this->form->get("sp_sapItemNumberReadOnly")->setValue($fields['sp_sapItemNumber']);

				$this->form->get("sp_debitValue2")->setValue(array($fields['sp_debitValue2_quantity'], $fields['sp_debitValue2_measurement']));
			}
			elseif($this->complaint->getComplaintType($id) == "quality_complaint")
			{
				$fields['totalClosureDate'] == "0000-00-00" ? $this->form->get("totalClosureDate")->setValue("") : $this->form->get("totalClosureDate")->setValue(page::transformDateForPHP($fields['totalClosureDate']));

				$this->complaint->getConclusion()->form->get("qu_requestDisposalName")->setVisible(false);

				$this->form->get("qu_requestForDisposalReadOnly")->setValue($fields['qu_requestForDisposal']);
				$this->form->get("qu_amountReadOnly")->setValue($fields['qu_amount_quantity'] . " " . $fields['qu_amount_measurement']);
				$this->form->get("qu_amount")->setValue(array($fields['qu_amount_quantity'], $fields['qu_amount_measurement']));
				$this->form->get("qu_requestDateReadOnly")->setValue(page::transformDateForPHP($fields['qu_requestDate']));
				$this->form->get("qu_requestDisposalNameReadOnly")->setValue(usercache::getInstance()->get($fields['qu_requestDisposalName'])->getName());

				$this->form->get("qu_disposalAuthorisedReadOnly")->setValue($fields['qu_disposalAuthorised']);
				$this->form->get("qu_disposalAuthorisedCommentReadOnly")->setValue($fields['qu_disposalAuthorisedComment']);
				$this->form->get("qu_disposalAuthorisedDateReadOnly")->setValue(page::transformDateForPHP($fields['qu_disposalAuthorisedDate']));
				$this->form->get("qu_disposalAuthorisedNameReadOnly")->setValue(usercache::getInstance()->get($fields['qu_disposalAuthorisedName'])->getName());

			}
			else
			{
				$this->form->get("amount")->setValue(array($fields['amount_quantity'], $fields['amount_measurement']));
				$this->form->get("creditNoteValueReadOnly")->setValue("" . $fields['creditNoteValue_quantity'] . " " . $fields['creditNoteValue_measurement'] . "");
				$this->form->get("defectiveMaterialAmount")->setValue(array($fields['defectiveMaterialAmount_quantity'], $fields['defectiveMaterialAmount_measurement']));

				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
				{
					//$fields['dateReturnsReceived'] == "0000-00-00" ? $this->form->get("dateReturnsReceived")->setValue("") : $this->form->get("dateReturnsReceived")->setValue(page::transformDateForPHP($fields['dateReturnsReceived']));
					$this->form->get("creditNoteValue")->getValue() < '0' ? $this->form->get("requestForCredit")->setVisible(false) : '';
					$this->form->get("returnQuantityReceived")->setValue(array($fields['returnQuantityReceived_quantity'], $fields['returnQuantityReceived_measurement']));
					$this->form->get("naSizeReturned")->setValue(array($fields['naSizeReturned_quantity'], $fields['naSizeReturned_measurement']));
				}
				else
				{
					//$this->form->get("creditNoteValue")->setValue(array($fields['creditNoteValue_quantity'], $fields['creditNoteValue_measurement']));
					//added PH 09/01/08 - to hide request for credit authorisation if authorisation has already been sought
					$this->form->get("creditNoteValue")->getValue() < '0' ? $this->form->get("requestForCredit")->setVisible(false) : '';
				}

				$fields['disposalNoteDate'] == "0000-00-00" ? $this->form->get("disposalNoteDate")->setValue("") : $this->form->get("disposalNoteDate")->setValue(page::transformDateForPHP($fields['disposalNoteDate']));

				$fields['closedDate'] == "0000-00-00" ? $this->form->get("closedDate")->setValue("") : $this->form->get("closedDate")->setValue(page::transformDateForPHP($fields['closedDate']));

				$fields['totalClosureDate'] == "0000-00-00" ? $this->form->get("totalClosureDate")->setValue("") : $this->form->get("totalClosureDate")->setValue(page::transformDateForPHP($fields['totalClosureDate']));

				$fields['dateCreditNoteRaised'] == "0000-00-00" ? $this->form->get("dateCreditNoteRaised")->setValue("") : $this->form->get("dateCreditNoteRaised")->setValue(page::transformDateForPHP($fields['dateCreditNoteRaised']));

				// Multiple - SAP Return Number
				$this->form->getGroup('sapReturnFormMulti')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM sapReturnNumber WHERE complaintId = " . $id . " ORDER BY `id`"));

				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() != "USA")
				{
					// JM - Add readonly values to Commercial Level Advise Stage
					$this->form->get("commercialLevelCreditAuthorisedAdvise2")->setValue($fields['commercialLevelCreditAuthorisedAdvise']);
					$this->form->get("commercialCreditAuthoriserAdvise2")->setValue(usercache::getInstance()->get($fields['commercialCreditAuthoriserAdvise'])->getName());
					$this->form->get("commercialCreditNewCommercialOwner2")->setValue(usercache::getInstance()->get($fields['commercialCreditNewCommercialOwner'])->getName());
					$this->form->get("commercialReasonAdvise2")->setValue($fields['commercialReasonAdvise']);

					//PH - Add readonly values to Commercial Level (level 2)
					$this->form->get("commercialLevelCreditAuthorised2")->setValue($fields['commercialLevelCreditAuthorised']);
					$this->form->get("commercialCreditAuthoriser2")->setValue(usercache::getInstance()->get($fields['commercialCreditAuthoriser'])->getName());
					$this->form->get("commercialCreditNewFinanceOwner2")->setValue(usercache::getInstance()->get($fields['commercialCreditNewFinanceOwner'])->getName());
					$this->form->get("commercialReason2")->setValue($fields['commercialReason']);

					// not quite sure this is the way to go ... $this->form->getGroup('sapGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE `complaintId` = " . $this->id . " ORDER BY `id`"));
				}

				//PH - Add readonly values to Finance Level
				$this->form->get("financeLevelCreditAuthorised2")->setValue($fields['financeLevelCreditAuthorised']);
				$this->form->get("financeCreditAuthoriser2")->setValue(usercache::getInstance()->get($fields['financeCreditAuthoriser'])->getName());
				$this->form->get("financeCreditNewComplaintOwner2")->setValue(usercache::getInstance()->get($fields['financeCreditNewComplaintOwner'])->getName());
				$this->form->get("financeReason2")->setValue($fields['financeReason']);
			}

			//$this->form->get('updatedDate')->setValue(page::transformDateForPHP($this->form->get('updatedDate')->getValue()));

			if($this->complaint->getComplaintType($id) == "supplier_complaint")
			{
				// do nothing
				//$this->form->get("attachment")->load("/apps/complaints/attachments/conc/" . $id . "/");
			}
			else
			{
				// do nothing
			}

			$this->form->putValuesInSession();

			$this->form->processDependencies();

			return true;
		}
		else
		{
			//echo "HERE";exit;
			unset($_SESSION['apps'][$GLOBALS['app']]['conclusion']);
			//unset($_SESSION['apps'][$GLOBALS['app']]);
			return false;
		}

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

	public function supplierFinanceStageComplete()
	{
		$supplierFinanceComplete = $this->form->get("supplierFinanceComplete")->getValue();

		return $supplierFinanceComplete;
	}

	public function qualityFinanceStageComplete()
	{
		$qualityFinanceComplete = $this->form->get("qualityFinanceComplete")->getValue();

		return $qualityFinanceComplete;
	}

	public function save()
	{
		$this->determineStatus();

		if ($this->loadedFromDatabase)
		{
			$this->form->get("owner")->setIgnore(true);

			$datasetLocation = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
			$fieldsLocation = mysql_fetch_array($datasetLocation);

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->form->get("sp_requestAuthorised")->getValue() == "Yes" && $this->supplierFinanceStageComplete() != "Yes")
				{
					$this->form->get("supplierFinanceComplete")->setValue("Yes");
					$this->form->get("sp_requestAuthorisedDate")->setValue(page::nowDateForPHP());
					$this->form->get("owner")->setValue($this->form->get("sp_requestAuthorisedName")->getValue());
					$this->addLog(translate::getInstance()->translate("request_authorised_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", utf8_encode($this->form->get("sp_requestAuthorisedEmailText")->getValue()));
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("sp_requestAuthorisedEmailText")->getValue()));
				}

				if($this->form->get("sp_requestDisposal")->getValue() == "Yes" && $this->form->get("sp_requestAuthorised")->getValue() != "Yes")
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3Request")->getValue());
					$this->addLog(translate::getInstance()->translate("request_disposal_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", utf8_encode($this->form->get("sp_requestEmailText")->getValue()));
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("sp_requestEmailText")->getValue()));
				}

				if($this->supplierFinanceStageComplete() == "Yes" || $this->form->get("sp_requestDisposal")->getValue() == "No")
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
				}

				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("supplier_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
						$this->getSupplierEmailNotification($this->complaint->getId(), $this->complaint->form->get("sp_sapSupplierNumber")->getValue(), "supplierComplaintClosed");
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				if($this->form->get("qu_disposalAuthorised")->getValue() == "Yes" && $this->qualityFinanceStageComplete() != "Yes")
				{
					$this->form->get("qualityFinanceComplete")->setValue("Yes");
					$this->form->get("qu_disposalAuthorisedDate")->setValue(page::nowDateForPHP());
					$this->form->get("owner")->setValue($this->form->get("qu_disposalAuthorisedName")->getValue());
					$this->addLog(translate::getInstance()->translate("request_authorised_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", utf8_encode($this->form->get("qu_disposalAuthorisedComment")->getValue()));
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("qu_disposalAuthorisedComment")->getValue()));
				}

				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("quality_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}

				if($this->qualityFinanceStageComplete() == "Yes")
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
				}
			}
			else
			{
				$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '"  . $this->getcomplaintId() . "'");
				$fieldsComplaint = mysql_fetch_array($datasetComplaint);

				$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT NTLogon FROM employee WHERE CONCAT(firstName, ' ', lastName) = \""  . $fieldsComplaint['internalSalesName'] . "\"");
				$fieldsLogon = mysql_fetch_array($datasetLogon);

				if($this->form->get("requestForCredit")->getValue() == "YES")
				{
					$this->calculateCurrency($this->form->get("creditNoteValue")->getMeasurement());
					//if($fieldsLocation['complaintLocation'] == 'american')
					//{
						//$this->form->get("transferOwnership")->setValue($this->form->get("transferOwnershipAmerican"));
					//}
					$this->form->get("owner")->setValue($this->form->get("transferOwnership")->getValue());
					if($this->form->get("financeLevelCreditAuthorised")->getValue() == '')
					{
						$this->form->get("financeStageCompleted")->setValue("NO");
					}
				}
				else
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
				}

				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
				{
					if($this->form->get("requestForCredit")->getValue() == "YES" && $this->form->get("financeLevelCreditAuthorised")->getValue() == "")
					{
						$this->addLog(translate::getInstance()->translate("credit_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("ccCommercialCreditComment")->getValue());
						$this->form->get("emailMessage")->setValue($this->form->get("ccCommercialCreditComment")->getValue());
					}
				}
				else
				{
					if($this->form->get("requestForCredit")->getValue() == "YES" && $this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == "" && $this->form->get("commercialLevelCreditAuthorised")->getValue() == "")
					{
						$this->addLog(translate::getInstance()->translate("credit_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("ccCommercialCreditComment")->getValue());
						$this->form->get("emailMessage")->setValue($this->form->get("ccCommercialCreditComment")->getValue());
					}
				}

				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("internal complaint closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
						//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET overallComplaintStatus = 'Closed' WHERE id ='" . $this->getcomplaintId() . "'");
					}
					$this->setInternalComplaintStatus("Closed");
					//set the customer complaint value to closed also, the next section (if) will close it properly
					$this->form->get("customerComplaintStatus")->setValue("Closed");
				}
				else
				{
					//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET overallComplaintStatus = '' WHERE id ='" . $this->getcomplaintId() . "'");
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}

				if($this->form->get("customerComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("closedDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("closedDate")->setValue(page::nowDateForPHP());
						$this->addLog("customer complaint closed " . $this->form->get("closedDate")->getValue(), $this->form->get("finalComments")->getValue()); //////added this 26/10/07
					}
					$this->setCustomerComplaintStatus("Closed");
				}
				else//if($this->form->get("customerComplaintStatus")->getValue() == 'Open')
				{
					$this->form->get("closedDate")->setValue("0");
				}

				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
				{
					if ($this->form->get("financeStageCompleted")->getValue() == 'NO')
					{
						if ($this->form->get("financeLevelCreditAuthorised")->getValue() == 'YES' || $this->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
						{
							$this->form->get("financeCreditAuthoriser")->setValue(currentuser::getInstance()->getNTLogon());
							$this->form->get("financeCreditNewComplaintOwner")->setValue($fieldsLogon['NTLogon']);
							$this->form->get("emailMessage")->setValue($this->form->get("financeReason")->getValue());

							if ($this->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
							{
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("finance_authorized_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("financeReason")->getValue());
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("creditAuthorisationStatus")->setValue("Authorisation Process COMPLETE");
								$this->form->get("emailMessage")->setValue($this->form->get("financeReason")->getValue());
							}
							else
							{
								//should be sent to initiator
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("finance_declined_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("financeReason")->getValue());
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("creditDeclined")->setValue("YES");
								$this->form->get("emailMessage")->setValue($this->form->get("financeReason")->getValue());
							}
						}
					}
					else
					{
						$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
						$this->form->get("emailMessage")->setValue("");
					}
				}
				else
				{
					//PH
					if ($this->form->get("financeStageCompleted")->getValue() == 'NO')
					{

						if ($this->form->get("financeLevelCreditAuthorised")->getValue() == 'YES' || $this->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
						{
							$this->form->get("financeCreditAuthoriser")->setValue(currentuser::getInstance()->getNTLogon());
							$this->form->get("financeCreditNewComplaintOwner")->setValue($fieldsLogon['NTLogon']);
							$this->form->get("emailMessage")->setValue($this->form->get("financeReason")->getValue());

							if ($this->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
							{
								//$this->form->get("owner")->setValue($this->form->get("financeCreditNewComplaintOwner")->getValue()); //complaint must go back to customer care so use fields NTLogon
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("finance_authorized_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("financeReason")->getValue());
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("creditAuthorisationStatus")->setValue("Authorisation Process COMPLETE");
								//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE conclusion SET `financeStageCompleted` = '" . $this->form->get("financeStageCompleted")->getValue() . "' WHERE complaintId='" . $this->getcomplaintId() . "'");
							}
							else //if finance autho = no
							{
								//should be sent to initiator
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("finance_declined_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("financeReason")->getValue());
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("creditDeclined")->setValue("YES");
								//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE conclusion SET `financeStageCompleted` = '" . $this->form->get("financeStageCompleted")->getValue() . "' WHERE complaintId='" . $this->getcomplaintId() . "'");
							}
						}
						elseif ($this->form->get("commercialLevelCreditAuthorised")->getValue() == 'YES' || $this->form->get("commercialLevelCreditAuthorised")->getValue() == 'NO')
						{
							$this->form->get("commercialCreditAuthoriser")->setValue(currentuser::getInstance()->getNTLogon());
							$this->form->get("emailMessage")->setValue($this->form->get("commercialReason")->getValue());

							if ($this->form->get("commercialLevelCreditAuthorised")->getValue() == 'YES')
							{
								$this->form->get("owner")->setValue($this->form->get("commercialCreditNewFinanceOwner")->getValue());
								$this->addLog(translate::getInstance()->translate("commercial_authorized_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("commercialReason")->getValue());
							}
							else //if commercial autho = no
							{
								//should be sent to initiator
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("commercial_declined_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("commercialReason")->getValue());
								$this->form->get("financeLevelCreditAuthorised")->setValue("NO");
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("commercialCreditNewFinanceOwner")->setValue($fieldsLogon['NTLogon']);
								$this->form->get("creditDeclined")->setValue("YES");
								//$this->form->get("financeCreditAuthoriser2")->setValue("");
							}
						}
						elseif ($this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == 'YES' || $this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == 'NO')
						{
							$this->form->get("commercialCreditAuthoriserAdvise")->setValue(currentuser::getInstance()->getNTLogon());
							$this->form->get("emailMessage")->setValue($this->form->get("commercialReasonAdvise")->getValue());

							if ($this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == 'YES')
							{
								$this->form->get("owner")->setValue($this->form->get("commercialCreditNewCommercialOwner")->getValue());
								$this->addLog(translate::getInstance()->translate("commercial_advise_authorized_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("commercialReasonAdvise")->getValue());
							}
							else //if commercial advise = no
							{
								//should be sent ot initiator
								$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
								$this->addLog(translate::getInstance()->translate("commercial_advise_declined_sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("commercialReasonAdvise")->getValue());
								$this->form->get("commercialLevelCreditAuthorised")->setValue("NO");
								$this->form->get("financeLevelCreditAuthorised")->setValue("NO");
								$this->form->get("financeStageCompleted")->setValue("YES");
								$this->form->get("commercialCreditNewCommercialOwner")->setValue($fieldsLogon['NTLogon']);
								$this->form->get("creditDeclined")->setValue("YES");
								//$this->form->get("commercialCreditNewFinanceOwner2")->setValue("");
								//$this->form->get("financeCreditNewComplaintOwner2")->setValue($fieldsComplaint['internalSalesName']);
								//$this->form->get("commercialCreditAuthoriser2")->setValue("");
								//$this->form->get("financeCreditAuthoriser2")->setValue("");
							}
						}
					}
					else
					{
						if($fieldsLogon['NTLogon'] != "")
						{
							$this->form->get("owner")->setValue($fieldsLogon['NTLogon']);
						}
						else
						{
							$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
						}
						$this->form->get("emailMessage")->setValue("");
					}
				}


			}

			$this->addLog("conclusion report updated");

			//die("Finance Stage: " . $this->form->get("financeStageCompleted")->getValue());

			$this->checkConclusionFieldsUpdated();

			// update
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE conclusion " . $this->form->generateUpdateQuery("conclusion") . " WHERE complaintId='" . $this->getcomplaintId() . "'");

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id ='" . $this->getcomplaintId() . "'");

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET owner ='" . $this->form->get("owner")->getValue() . "' WHERE id ='" . $this->getcomplaintId() . "'");

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("supplier_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
						$this->getSupplierEmailNotification($this->complaint->getId(), $this->complaint->form->get("sp_sapSupplierNumber")->getValue(), "supplierComplaintClosed");
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("internal_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}

				if($this->form->get("qu_requestForDisposal")->getValue() == "Yes" && $this->qualityFinanceStageComplete() != "Yes")
				{
					$this->form->get("qu_requestDate")->getValue() == "" ? $this->form->get("qu_requestDate")->setValue(page::nowDateForPHP()) : $this->form->get("qu_requestDate")->setValue($this->form->get("qu_requestDate")->getValue());
					$this->form->get("owner")->setValue($this->form->get("qu_requestDisposalName")->getValue());
					$this->addLog(translate::getInstance()->translate("disposal_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", "");
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", "");
				}
				else
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", "");
				}
			}
			else
			{
				//Email must be sent after the info has ben added to the log otherwise the email will go out, even after the finance stage has been cmopleted.  putting it here will stop that.
				if($this->form->get("customerComplaintStatus")->getValue() == "Closed" || $this->form->get("internalComplaintStatus")->getValue() == "Closed")
				{
					//no email to be sent
				}
				else
				{
					if ($this->form->get("financeStageCompleted")->getValue() == "YES")//value changed to yes on submission of form at finance level
					{
						if($this->form->get("creditDeclined")->getValue() == "YES")
						{
							$this->getEmailNotification($this->getComplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), "creditDeclined", utf8_encode($this->form->get("emailMessage")->getValue()));
						}
						else
						{
							$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("emailMessage")->getValue()));
						}
					}
					else //before finance stage is completed emails requesting credit are sent out
					{
						$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "creditRequest", utf8_encode($this->form->get("emailMessage")->getValue()));
					}
				}
			}

		}
		else
		{

			/* Peter, noticed this line is defaulting the owner to transfer ownership
			before the request for credit is being implemented causing it to default to
			Arwed so I have commented this out. - Jason */
			//$this->form->get("owner")->setValue($this->form->get("transferOwnership")->getValue());


			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("supplier_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
						$this->getSupplierEmailNotification($this->complaint->getId(), $this->complaint->form->get("sp_sapSupplierNumber")->getValue(), "supplierComplaintClosed");
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					//if no date previously entered
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						//enter todays date
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("internal_complaint_closed " . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
					}
					$this->setInternalComplaintStatus("Closed");
				}
				else
				{
					$this->form->get("totalClosureDate")->setValue("0");
					$this->setInternalComplaintStatus("Open");
				}

				if($this->form->get("qu_requestForDisposal")->getValue() == "Yes")
				{
					$this->form->get("qu_requestDate")->getValue() == "" ? $this->form->get("qu_requestDate")->setValue(page::nowDateForPHP()) : $this->form->get("qu_requestDate")->setValue($this->form->get("qu_requestDate")->getValue());
					$this->form->get("owner")->setValue($this->form->get("qu_requestDisposalName")->getValue());
					$this->addLog(translate::getInstance()->translate("disposal_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", "");
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", "");
				}
				else
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", "");
				}
			}
			else
			{
				if($this->form->get("requestForCredit")->getValue() == "YES")
				{
					$this->calculateCurrency($this->form->get("creditNoteValue")->getMeasurement());
					//if($fieldsLocation['complaintLocation'] == 'american')
					//{
						//$this->form->get("transferOwnership")->setValue($this->form->get("transferOwnershipAmerican"));
					//}
					$this->form->get("owner")->setValue($this->form->get("transferOwnership")->getValue());
					if($this->form->get("financeLevelCreditAuthorised")->getValue() == '')
					{
						$this->form->get("financeStageCompleted")->setValue("NO");
					}
				}
				else
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3")->getValue());
					$this->form->get("financeStageCompleted")->setValue("YES");
				}

				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
				{
					if($this->form->get("requestForCredit")->getValue() == "YES" && $this->form->get("financeLevelCreditAuthorised")->getValue() == "")
					{
						$this->addLog(translate::getInstance()->translate("credit_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("ccCommercialCreditComment")->getValue());
						$this->form->get("emailMessage")->setValue($this->form->get("ccCommercialCreditComment")->getValue());
					}
				}
				else
				{
					if($this->form->get("requestForCredit")->getValue() == "YES" && $this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == "")
					{
						$this->addLog(translate::getInstance()->translate("credit_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", $this->form->get("ccCommercialCreditComment")->getValue());
						$this->form->get("emailMessage")->setValue($this->form->get("ccCommercialCreditComment")->getValue());
					}
				}

				if($this->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					if($this->form->get("totalClosureDate")->getValue() == "")
					{
						$this->form->get("totalClosureDate")->setValue(page::nowDateForPHP());
						$this->addLog("internal complaint closed" . $this->form->get("totalClosureDate")->getValue());//////added this 26/10/07
						//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET overallComplaintStatus = 'Closed' WHERE id ='" . $this->getcomplaintId() . "'");
					}
					$this->setInternalComplaintStatus("Closed");
					//close customer complaint status on the form, the next if will take care of the rest of it.
					$this->form->get("customerComplaintStatus")->setValue("Closed");
				}

				if($this->form->get("customerComplaintStatus")->getValue() == 'Closed')
				{
					if($this->form->get("closedDate")->getValue() == "")
					{
						$this->form->get("closedDate")->setValue(page::nowDateForPHP());
						$this->addLog("customer complaint closed " . $this->form->get("closedDate")->getValue(), $this->form->get("finalComments")->getValue());//////added this 26/10/07
					}
					$this->setCustomerComplaintStatus("Closed");
				}

				if($this->form->get("customerComplaintStatus")->getValue() == 'Open')
				{
					if($this->form->get("closedDate")->getValue() != "")
					{
						$this->form->get("closedDate")->setValue("");
					}
				}
			}


			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("conclusion_completed_disposed"));
			}
			else
			{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")");
			}

			//$this->form->get("commercialCreditAuthoriser")->setValue(currentuser::getInstance()->getNTLogon());


			/* WC EDIT */
			$doUpdate = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(complaintId) as doUpdate FROM conclusion WHERE complaintId = '" . $this->getcomplaintId() . "'");
			$doUpdateFields = mysql_fetch_array($doUpdate);
			if($doUpdateFields["doUpdate"]>0){
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE conclusion " . $this->form->generateUpdateQuery("conclusion")." WHERE complaintId = '".$this->getcomplaintId()."'");
			}else{
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO conclusion " . $this->form->generateInsertQuery("conclusion"));
			}
			/* WC END */
			/*
			mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO conclusion " . $this->form->generateInsertQuery("conclusion"));
			*/

			// Send Email
			$datasetEmail = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($datasetEmail);

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->form->get("sp_requestDisposal")->getValue() == "Yes")
				{
					$this->form->get("owner")->setValue($this->form->get("processOwner3Request")->getValue());
					$this->addLog(translate::getInstance()->translate("disposal_requested_sent_to_") . " (" . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) .")", utf8_encode($this->form->get("sp_requestEmailText")->getValue()));
					$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("sp_requestEmailText")->getValue()));
				}
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				// do nohing ...
			}
			else
			{
				if($this->form->get("requestForCredit")->getValue() == 'YES')
				{
					$this->form->get("emailMessage")->setValue($this->form->get("ccCommercialCreditComment")->getValue());
				}

				if($this->form->get("customerComplaintStatus")->getValue() == "Closed" || $this->form->get("internalComplaintStatus")->getValue() == "Closed")
				{
					//no email to be sent
				}
				else
				{
					if ($this->form->get("financeStageCompleted")->getValue() == "YES")//value changed to yes on submission of form at finance level
					{
						$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "conclusionAction", utf8_encode($this->form->get("emailMessage")->getValue()));
					}
					else //before finance stage is completed emails requesting credit are sent out
					{
						$this->getEmailNotification($this->getcomplaintId(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "creditRequest", utf8_encode($this->form->get("emailMessage")->getValue()));
					}
				}
			}

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id = " . $this->getcomplaintId() . "");

		}

		if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
		{
//			$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/conc/" . $this->getcomplaintId() . "/");
//			$this->form->get("attachment")->moveTempFileToFinal();
		}
		elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
		{
			// do nothing ...
		}
		else
		{
			// For multiple fields - sAP Order Number
			mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM sapReturnNumber WHERE complaintId = " . $this->getcomplaintId());

			for ($i=0; $i < $this->form->getGroup("sapReturnFormMulti")->getRowCount(); $i++)
			{
				$this->form->getGroup("sapReturnFormMulti")->setForeignKeyValue($this->getcomplaintId());
				mysql::getInstance()->selectDatabase("Complaints")->Execute("INSERT INTO sapReturnNumber " . $this->form->getGroup("sapReturnFormMulti")->generateInsertQuery($i));
			}
		}



		$this->lockComplaint($this->getcomplaintId(), "unlocked");

		//page::redirect("/apps/complaints/");
		page::redirect("/apps/complaints/index?id=" . $this->getcomplaintId());		//redirects the page back to the summary
	}

	public function checkConclusionFieldsUpdated()
	{
		// Check Current Field Values

		if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
		{
			//$currentImplementedPermanentCorrectiveActionValidatedYesNo = $this->form->get("implementedPermanentCorrectiveActionValidatedYesNo")->getValue();
		}
		elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
		{
			//$currentImplementedPermanentCorrectiveActionValidatedYesNo = $this->form->get("implementedPermanentCorrectiveActionValidatedYesNo")->getValue();
		}
		else
		{
			$currentCreditNoteValue_quantity = $this->form->get("creditNoteValue")->getQuantity();
			$currentCreditNoteValue_measurement = $this->form->get("creditNoteValue")->getMeasurement();
			$currentIsCreditOrDebitNote = $this->form->get("isCreditOrDebitNote")->getValue();

			if($this->form->get("requestForCredit")->getValue())
			{
				$currentRequestCreditNote = $this->form->get("requestForCredit")->getValue();
			}

			// Check Updated Field Values
			$checkUpdated = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `creditNoteValue_quantity`, `creditNoteValue_measurement`, `requestForCredit`, `isCreditOrDebitNote` FROM conclusion WHERE complaintId = " . $this->getcomplaintId() . "");
			$fieldsUpdated = mysql_fetch_array($checkUpdated);

			$newCreditNoteValue_quantity = $fieldsUpdated['creditNoteValue_quantity'];
			$newCreditNoteValue_measurement = $fieldsUpdated['creditNoteValue_measurement'];
			$newIsCreditOrDebitNote = $fieldsUpdated['isCreditOrDebitNote'];

			if($this->form->get("requestForCredit")->getValue())
			{
				$newRequestCreditNote = $fieldsUpdated['requestForCredit'];
			}

			// Compare Current and New Fields
			$updatedFields = "";

			if($currentCreditNoteValue_quantity != $newCreditNoteValue_quantity || $currentCreditNoteValue_measurement != $newCreditNoteValue_measurement)
			{
				$updatedFields .= "Credit Note Value: Old(" . $newCreditNoteValue_quantity . " " . $newCreditNoteValue_measurement . ") New(" . $currentCreditNoteValue_quantity . " " . $currentCreditNoteValue_measurement . ") - ";
			}

			if($this->form->get("requestForCredit")->getValue())
			{
				if($currentRequestCreditNote != $newRequestCreditNote)
				{
					$updatedFields .= "Credit Note Requested: Old(" . $newRequestCreditNote . ") New(" . $currentRequestCreditNote . ") - ";
				}
			}

			if($this->form->get("isCreditOrDebitNote")->getValue())
			{
				if($currentIsCreditOrDebitNote != $newIsCreditOrDebitNote)
				{
					$updatedFields .= "Is Credit Or Debit Note: Old(" . $newIsCreditOrDebitNote . ") New(" . $currentIsCreditOrDebitNote . ") - ";
				}
			}

			if($updatedFields)
			{
				$this->addLog(translate::getInstance()->translate("conclusion_fields_have_been_updated") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", substr_replace($updatedFields ,"",-2));
			}
		}
	}



	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
			$this->getComplaint()->form->get("id")->getValue(),
			addslashes(currentuser::getInstance()->getNTLogon()),
			addslashes($action),
			common::nowDateTimeForMysql(),
			$comment
		));
	}


	public function getOwner()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['owner'];
	}

	public function getCommercialAdviseCreditStatus()
	{
		return $this->form->get("commercialLevelCreditAuthorisedAdvise")->getValue();
	}

	public function getCommercialCreditStatus()
	{
		return $this->form->get("commercialLevelCreditAuthorised")->getValue();
	}

	public function getFinanceCreditStatus()
	{
		return $this->form->get("financeLevelCreditAuthorised")->getValue();
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

	public function determineStatus()
	{
		$location = "conclusion";
		$this->status = $location;
		$this->form->get('status')->setValue($location);
	}

	public function calculateCurrency($currency)
	{
		$currencyConversion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `currency` WHERE `currency` = '" . $currency ."'");
		$currencyConversionFields = mysql_fetch_array($currencyConversion);

		$value = $this->form->get("creditNoteValue")->getQuantity() * $currencyConversionFields['currencyValue'];

		$gbpCurrency = sprintf("%.2f", $value);

		$this->form->get("creditNoteGBP")->setValue(array("" . $gbpCurrency . "", "GBP"));
	}

	public function defineForm()
	{
		$businessUnit_value = $this->getComplaint()->form->get("businessUnit")->getValue();

		/* WC AE - 28/01/08 */
		$savedFields = array();

		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		/*
		echo "<pre>";
		print_r($savedFields);
		echo "</pre>";
		exit;
		*/
		/* WC END*/
		$today = date("d/m/Y",time());

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("conclusion" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$idHeadersCustom = new group("initiation2");
		$idHeadersCustom->setBorder(false);

		$sapReturnFormMulti = new multiplegroup("sapReturnFormMulti");
		$sapReturnFormMulti->setTitle("SAP return Number");
		$sapReturnFormMulti->setNextAction("conclusion");
		$sapReturnFormMulti->setTable("sapReturnNumber");
		$sapReturnFormMulti->setForeignKey("complaintId");


		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$modComplaintOptionNo = new group("modComplaintOptionNo");
		$modComplaintOptionNo->setBorder(false);
		$modComplaintOptionYes = new group("modComplaintOptionYes");
		$modComplaintOptionYes->setBorder(false);
		$materialAmountGroup = new group("materialAmountGroup");
		$creditGroup =  new group("creditGroup");
		$creditGroup->setBorder(false);
		$creditGroupNo = new group("creditGroupNo");
		$creditGroupYes =  new group("creditGroupYes");
//		$creditGroupYes->setBorder(false);
//		$transferOwnershipNo = new group("transferOwnershipNo");
//		$transferOwnershipYes = new group("transferOwnershipYes");
		$commercialCreditStatusGroupAdvise = new group("commercialCreditStatusGroupAdvise");
		$commercialCreditStatusGroupAdvise->setBorder(false);
		$adviseAcceptedYes = new group("adviseAcceptedYes");
		$adviseAcceptedYes->setBorder(false);
		$commercialAdviseReason = new group("commercialAdviseReason");
		$commercialAdviseReason->setBorder(false);
		$commercialCreditStatusGroup = new group("commercialCreditStatusGroup");
		$commercialCreditStatusGroup->setBorder(false);
		$commercialAcceptedYes = new group("commercialAcceptedYes");
		$commercialAcceptedYes->setBorder(false);
		$commercialAcceptedReason = new group("commercialAcceptedReason");
		$commercialAcceptedReason->setBorder(false);
		$financeCreditStatusGroup = new group("financeCreditStatusGroup");
		$creditAuthorisationStatusGroup = new group("creditAuthorisationStatusGroup");
		$creditRaisedGroup = new group("creditRaisedGroup");
		$creditRaisedGroup->setBorder(false);
		$creditRaisedGroupYes = new group("creditRaisedGroupYes");
		$creditRaisedGroupYes->setBorder(false);
		$creditRaisedGroupDef = new group("creditRaisedGroupDef");
		$complaintClosure = new group("complaintClosure");
		$customerCreditGroup = new group("customerCreditGroup");
		$complaintClosure->setBorder(false);
		$customerComplaintStatusClosed = new group("customerComplaintStatusClosed");
		$totalClosureGroup = new group("totalClosureGroup");
		$totalClosureGroup->setBorder(false);
		$internalComplaintStatusClosed = new group("internalComplaintStatusClosed");
		$internalComplaintStatusClosed->setBorder(false);
		$submitGroup = new group("submitGroup");



		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"])){//this means we are coming from the print function defined on homepage

			$dataset2 = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion LEFT JOIN complaint ON conclusion.complaintId=complaint.id WHERE complaintId = '"  . $this->complaint->getId()."'");
			$fields2 = mysql_fetch_array($dataset2);

			$showID = new textbox("showID");
			$showID->setValue($this->complaint->getId());
			$showID->setRowTitle("complaint_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("evaluation");
			$idHeadersCustom->add($showID);

			$showSAP = new textbox("showSAP");
			$showSAP->setValue($fields2["sapCustomerNumber"]);
			$showSAP->setRowTitle("SAP_customer_number");
			$showSAP->setGroup("initiation");
			$showSAP->setDataType("string");
			$showSAP->setLength(50);
			$showSAP->setRequired(false);
			$showSAP->setTable("evaluation");
			$idHeadersCustom->add($showSAP);

			$showSAPName = new textbox("showSAPName");
			$showSAPName->setValue($fields2["sapName"]);
			$showSAPName->setRowTitle("SAP_customer_name");
			$showSAPName->setGroup("initiation");
			$showSAPName->setDataType("string");
			$showSAPName->setLength(50);
			$showSAPName->setRequired(false);
			$showSAPName->setTable("evaluation");
			$idHeadersCustom->add($showSAPName);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
				$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("evaluation");
			$idHeadersCustom->add($showOpenDate);
		}



		if(!isset($savedFields["0|sapReturnNumber"])){//the first one will always need to be set if its saved
			$sapReturnNumber = new textbox("sapReturnNumber");
			$sapReturnNumber->setDataType("text");
			$sapReturnNumber->setLength(255);
			$sapReturnNumber->setRequired(false);
			$sapReturnNumber->setRowTitle("sap_return__number");
			$sapReturnNumber->setTable("sapReturnNumber");
			$sapReturnNumber->setHelpId(8007);
			//$sapReturnNumber->setValidateQuery("SAP", "material_group", "key");
			$sapReturnFormMulti->add($sapReturnNumber);
		}else{
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|sapReturnNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$sapReturnNumber = new textbox("sapReturnNumber");
					if(isset($savedFields["0|sapReturnNumber"]))
						$sapReturnNumber->setValue($savedFields["0|sapReturnNumber"]);
					$sapReturnNumber->setDataType("text");
					$sapReturnNumber->setLength(255);
					$sapReturnNumber->setRequired(false);
					$sapReturnNumber->setRowTitle("sap_return__number");
					$sapReturnNumber->setTable("sapReturnNumber");
					$sapReturnNumber->setHelpId(8007);
					//$sapReturnNumber->setValidateQuery("SAP", "material_group", "key");
					$sapReturnFormMulti->add($sapReturnNumber);
				}else{
					$sapReturnFormMulti->addRowCustom($savedFields[$i."|sapReturnNumber"]);
				}
			}
		}

		$datasetLocation = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `creditNoteRequested` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
		$fieldsLocation = mysql_fetch_array($datasetLocation);

		if($fieldsLocation['complaintLocation'] == 'american')
		{
			$sapItemNumberCon = new textbox("sapItemNumberCon");
			$sapItemNumberCon->setDataType("text");
			$sapItemNumberCon->setLength(255);
			$sapItemNumberCon->setRequired(false);
			$sapItemNumberCon->setRowTitle("sap_item_number");
			$sapItemNumberCon->setTable("conclusion");
			$sapItemNumberCon->setHelpId(8007);
			//$sapReturnNumber->setValidateQuery("SAP", "material_group", "key");
			$initiation->add($sapItemNumberCon);
		}

		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("conclusion");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		else $status->setValue("initiation");
		$status->setTable("conclusion");
		$status->setVisible(false);
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
			$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);



		$returnFormDate = new calendar("returnFormDate");
		if(isset($savedFields["returnFormDate"]))
			$returnFormDate->setValue($savedFields["returnFormDate"]);
		$returnFormDate->setGroup("initiation");
		$returnFormDate->setDataType("date");
		$returnFormDate->setErrorMessage("textbox_date_error");
		$returnFormDate->setLength(255);
		$returnFormDate->setRowTitle("return_product_return_date");
		$returnFormDate->setRequired(false);
		$returnFormDate->setTable("conclusion");
		$returnFormDate->setVisible(false);
		$returnFormDate->setHelpId(9101);
		$initiation->add($returnFormDate);


		// Peter Hawley added.
		if($fieldsLocation['complaintLocation'] == 'american')
		{
//			$returnQuantityReceived = new measurement("returnQuantityReceived");
//			if(isset($savedFields["returnQuantityReceived_quantity"]) && isset($savedFields["returnQuantityReceived_measurement"])){
//			$arr[0] = $savedFields["returnQuantityReceived_quantity"];
//			$arr[1] = $savedFields["returnQuantityReceived_measurement"];
//			$returnQuantityReceived->setValue($arr);
//			}else $returnQuantityReceived->setMeasurement("mm");
//			$returnQuantityReceived->setGroup("initiation");
//			$returnQuantityReceived->setDataType("string");
//			$returnQuantityReceived->setLength(255);
//			$returnQuantityReceived->setRowTitle("return_quantity_received");
//			$returnQuantityReceived->setRequired(false);
//			$returnQuantityReceived->setTable("conclusion");
//			$returnQuantityReceived->setXMLSource("./apps/complaints/xml/uom.xml");
//			$returnQuantityReceived->setHelpId(9190);
//			$initiation->add($returnQuantityReceived);

			$dateReturnsReceived = new calendar("dateReturnsReceived");
			if(isset($savedFields["dateReturnsReceived"]))
				$dateReturnsReceived->setValue($savedFields["dateReturnsReceived"]);
			$dateReturnsReceived->setGroup("initiation");
			$dateReturnsReceived->setDataType("date");
			$dateReturnsReceived->setErrorMessage("textbox_date_error");
			$dateReturnsReceived->setLength(255);
			$dateReturnsReceived->setRowTitle("date_returns_received");
			$dateReturnsReceived->setRequired(false);
			$dateReturnsReceived->setTable("conclusion");
			$dateReturnsReceived->setHelpId(9191);
			$initiation->add($dateReturnsReceived);

			$receiver = new textbox("receiver");
			if(isset($savedFields["receiver"]))
				$receiver->setValue($savedFields["receiver"]);
			$receiver->setGroup("initiation");
			$receiver->setDataType("string");
			$receiver->setLength(255);
			$receiver->setRowTitle("receiver");
			$receiver->setRequired(false);
			$receiver->setTable("conclusion");
			$receiver->setHelpId(9192);
			$initiation->add($receiver);
		}

		$disposalNoteDate = new calendar("disposalNoteDate");
		if(isset($savedFields["disposalNoteDate"]))
			$disposalNoteDate->setValue($savedFields["disposalNoteDate"]);
		$disposalNoteDate->setGroup("initiation");
		$disposalNoteDate->setDataType("date");
		$disposalNoteDate->setLength(255);
		$disposalNoteDate->setErrorMessage("textbox_date_error");
		$disposalNoteDate->setRowTitle("date_disposal_note_signed_back");
		$disposalNoteDate->setRequired(false);
		$disposalNoteDate->setTable("conclusion");
		//$disposalNoteDate->setLabel("Temp Note: Automatically populated fields are no longer shown in add mode but will be available in view mode!");
		$disposalNoteDate->setHelpId(9103);
		$initiation->add($disposalNoteDate);

		if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
		{
			$returnQuantityReceived = new measurement("returnQuantityReceived");
			if(isset($savedFields["returnQuantityReceived_quantity"]) && isset($savedFields["returnQuantityReceived_measurement"])){
				$arr[0] = $savedFields["returnQuantityReceived_quantity"];
				$arr[1] = $savedFields["returnQuantityReceived_measurement"];
			$returnQuantityReceived->setValue($arr);
			}else $returnQuantityReceived->setMeasurement("mm");
			$returnQuantityReceived->setGroup("initiation");
			$returnQuantityReceived->setDataType("string");
			$returnQuantityReceived->setLength(255);
			$returnQuantityReceived->setRowTitle("return_quantity_received");
			$returnQuantityReceived->setRequired(false);
			$returnQuantityReceived->setTable("conclusion");
			$returnQuantityReceived->setXMLSource("./apps/complaints/xml/uom.xml");
			$returnQuantityReceived->setHelpId(919024234);
			$initiation->add($returnQuantityReceived);

			$naLotNumber = new textbox("naLotNumber");
			if(isset($savedFields["naLotNumber"]))
				$naLotNumber->setValue($savedFields["naLotNumber"]);
			$naLotNumber->setGroup("initiation");
			$naLotNumber->setDataType("string");
			$naLotNumber->setLength(255);
			$naLotNumber->setRowTitle("lot_number");
			$naLotNumber->setRequired(false);
			$naLotNumber->setTable("conclusion");
			$naLotNumber->setHelpId(9190242341);
			$initiation->add($naLotNumber);

			$naSizeReturned = new measurement("naSizeReturned");
			if(isset($savedFields["naSizeReturned_quantity"]) && isset($savedFields["naSizeReturned_measurement"])){
				$arr[0] = $savedFields["naSizeReturned_quantity"];
				$arr[1] = $savedFields["naSizeReturned_measurement"];
			$naSizeReturned->setValue($arr);
			}else $naSizeReturned->setMeasurement("mm");
			$naSizeReturned->setGroup("initiation");
			$naSizeReturned->setDataType("string");
			$naSizeReturned->setLength(255);
			$naSizeReturned->setRowTitle("size_returned");
			$naSizeReturned->setRequired(false);
			$naSizeReturned->setTable("conclusion");
			$naSizeReturned->setXMLSource("./apps/complaints/xml/uom.xml");
			$naSizeReturned->setHelpId(9190242342);
			$initiation->add($naSizeReturned);

			$naCondition = new textarea("naCondition");
			if(isset($savedFields["naCondition"]))
				$naCondition->setValue($savedFields["naCondition"]);
			$naCondition->setGroup("initiation");
			$naCondition->setDataType("text");
			$naCondition->setRowTitle("condition");
			$naCondition->setRequired(false);
			$naCondition->setTable("conclusion");
			$naCondition->setHelpId(9190242343);
			$initiation->add($naCondition);
		}

		$modComplaintOption = new radio("modComplaintOption");
		$modComplaintOption->setGroup("initiation");
		$modComplaintOption->setDataType("string");
		$modComplaintOption->setLength(5);
		$modComplaintOption->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$modComplaintOption->setRowTitle("is_complaint_category_correct");
		$modComplaintOption->setRequired(false);
		if(isset($savedFields["modComplaintOption"]))
			$modComplaintOption->setValue($savedFields["modComplaintOption"]);
		else $modComplaintOption->setValue("YES");
		$modComplaintOption->setTable("conclusion");
		$modComplaintOption->setHelpId(9104);


		// Dependency
		$modComplaintOption_dependency = new dependency();
		$modComplaintOption_dependency->addRule(new rule('initiation', 'modComplaintOption', 'NO'));
		$modComplaintOption_dependency->setGroup('modComplaintOptionNo');
		$modComplaintOption_dependency->setShow(true);

		$modComplaintOptionYes_dependency = new dependency();
		$modComplaintOptionYes_dependency->addRule(new rule('initiation', 'modComplaintOption', 'YES'));
		$modComplaintOptionYes_dependency->setGroup('modComplaintOptionYes');
		$modComplaintOptionYes_dependency->setShow(true);

		$modComplaintOption->addControllingDependency($modComplaintOption_dependency);
		$modComplaintOption->addControllingDependency($modComplaintOptionYes_dependency);
		$initiation->add($modComplaintOption);

		$modComplaintCategory = new dropdown("modComplaintCategory");
		if(isset($savedFields["modComplaintCategory"]))
			$modComplaintCategory->setValue($savedFields["modComplaintCategory"]);
		$modComplaintCategory->setGroup("modComplaintOptionNo");
		$modComplaintCategory->setDataType("string");
		$modComplaintCategory->setLength(255);
		$modComplaintCategory->setRowTitle("mod_complaint_category");
		$modComplaintCategory->setRequired(false);
		//$modComplaintCategory->setXMLSource("./apps/complaints/xml/category.xml");
		$modComplaintCategory->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'customer' AND field = 'category' ORDER BY `details` ASC");
		$modComplaintCategory->setTable("conclusion");
		$modComplaintCategory->setTranslate(true);
		$modComplaintCategory->setHelpId(9105);
		$modComplaintOptionNo->add($modComplaintCategory);


		$modComplaintReason = new textarea("modComplaintReason");
		if(isset($savedFields["modComplaintReason"]))
			$modComplaintReason->setValue($savedFields["modComplaintReason"]);
		$modComplaintReason->setGroup("modComplaintOptionNo");
		$modComplaintReason->setDataType("text");
		$modComplaintReason->setRowTitle("mod_complaint_reason");
		$modComplaintReason->setRequired(false);
		$modComplaintReason->setTable("conclusion");
		$modComplaintReason->setHelpId(9106);
		$modComplaintOptionNo->add($modComplaintReason);

		$currentComplaintCategory = new readonly("currentComplaintCategory");
		if(isset($savedFields["currentComplaintCategory"]))
			$currentComplaintCategory->setValue($savedFields["currentComplaintCategory"]);
		$currentComplaintCategory->setGroup("modComplaintOptionYes");
		$currentComplaintCategory->setDataType("string");
		$currentComplaintCategory->setLength(255);
		$currentComplaintCategory->setRowTitle("current_complaint_category");
		$currentComplaintCategory->setRequired(false);
		$currentComplaintCategory->setTable("complaint");
		$currentComplaintCategory->setHelpId(9189);
		$modComplaintOptionYes->add($currentComplaintCategory);

		$defectiveMaterialAmount = new measurement("defectiveMaterialAmount");
		$defectiveMaterialAmount->setGroup("materialAmountGroup");
		$defectiveMaterialAmount->setDataType("string");
		$defectiveMaterialAmount->setLength(10);
		$defectiveMaterialAmount->setXMLSource("./apps/complaints/xml/uom.xml");
		$defectiveMaterialAmount->setRowTitle("defective_material_amount");
		if(isset($savedFields["defectiveMaterialAmount_quantity"]) && isset($savedFields["defectiveMaterialAmount_measurement"])){
			$arr[0] = $savedFields["defectiveMaterialAmount_quantity"];
			$arr[1] = $savedFields["defectiveMaterialAmount_measurement"];
			$defectiveMaterialAmount->setValue($arr);
		}else $defectiveMaterialAmount->setMeasurement("mm");

		$defectiveMaterialAmount->setRequired(false);
		$defectiveMaterialAmount->setTable("conclusion");
		$defectiveMaterialAmount->setHelpId(8018);
		$materialAmountGroup->add($defectiveMaterialAmount);

		$isCreditOrDebitNote = new radio("isCreditOrDebitNote");
		$isCreditOrDebitNote->setGroup("creditGroup");
		$isCreditOrDebitNote->setDataType("string");
		$isCreditOrDebitNote->setLength(5);
		$isCreditOrDebitNote->setArraySource(array(
			array('value' => 'credit', 'display' => 'Credit'),
			array('value' => 'debit', 'display' => 'Debit')
		));
		$isCreditOrDebitNote->setRowTitle("is_credit_or_debit_note");
		$isCreditOrDebitNote->setRequired(false);
		if(isset($savedFields["isCreditOrDebitNote"]))
			$isCreditOrDebitNote->setValue($savedFields["isCreditOrDebitNote"]);
		else $isCreditOrDebitNote->setValue("credit");
		$isCreditOrDebitNote->setTable("conclusion");
		$isCreditOrDebitNote->setHelpId(9104);
		$creditGroup->add($isCreditOrDebitNote);

		$creditNoteValue = new measurement("creditNoteValue");
		$creditNoteValue->setGroup("creditGroup");
		$creditNoteValue->setDataType("string");
		$creditNoteValue->setLength(20);
		$creditNoteValue->setRowTitle("credit_note_value");
		$creditNoteValue->setRequired(false);
		$creditNoteValue->setTable("conclusion");
		$creditNoteValue->setXMLSource("./apps/complaints/xml/currency.xml");
		if(isset($savedFields["creditNoteValue_quantity"]) && isset($savedFields["creditNoteValue_measurement"])){
			$arr[0] = $savedFields["creditNoteValue_quantity"];
			$arr[1] = $savedFields["creditNoteValue_measurement"];
			$creditNoteValue->setValue($arr);
		}else $creditNoteValue->setMeasurement("EUR");
		//$creditNoteValue->setOnChange("update_american_credit_list();");
		$creditNoteValue->setHelpId(8187);
		$creditGroup->add($creditNoteValue);

		$creditNoteValueReadOnly = new readonly("creditNoteValueReadOnly");
		//if(isset($savedFields["creditNoteValueReadOnly"]))
		//	$creditNoteValueReadOnly->setValue($savedFields["creditNoteValueReadOnly"]);
		if(isset($savedFields["creditNoteValue_quantity"]) && isset($savedFields["creditNoteValue_measurement"])){
			$arr[0] = $savedFields["creditNoteValue_quantity"];
			$arr[1] = $savedFields["creditNoteValue_measurement"];
			$creditNoteValueReadOnly->setValue($savedFields["creditNoteValue_quantity"] . " " .$savedFields["creditNoteValue_measurement"]);
			$creditNoteValue->setValue($arr);
		}
		$creditNoteValueReadOnly->setGroup("creditGroup");
		$creditNoteValueReadOnly->setDataType("string");
		$creditNoteValueReadOnly->setLength(255);
		$creditNoteValueReadOnly->setRowTitle("credit_note_value_read_only");
		$creditNoteValueReadOnly->setRequired(false);
		$creditNoteValueReadOnly->setVisible(false);
		$creditNoteValueReadOnly->setTable("complaint");
		$creditNoteValueReadOnly->setHelpId(9189);
		$creditGroup->add($creditNoteValueReadOnly);

		$creditNoteGBP = new measurement("creditNoteGBP");
		$creditNoteGBP->setGroup("creditGroup");
		$creditNoteGBP->setDataType("string");
		$creditNoteGBP->setLength(20);
		$creditNoteGBP->setRowTitle("credit_note_gbp");
		$creditNoteGBP->setRequired(false);
		$creditNoteGBP->setVisible(false);
		$creditNoteGBP->setTable("conclusion");
		$creditNoteGBP->setXMLSource("./apps/complaints/xml/currency.xml");
		$creditGroup->add($creditNoteGBP);

		//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
		if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
		{
			$datasetReturns = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM naCreditGroupInformation  WHERE complaintId = " . $this->complaint->form->get("id")->getValue() . "");

			if(mysql_num_rows($datasetReturns) > 0)
			{
				$displayOldAuthorisedNACredit = new textboxlink("displayOldAuthorisedNACredit");
				$displayOldAuthorisedNACredit->setRowTitle("old_na_credit_authorisations");
				$displayOldAuthorisedNACredit->setHelpId(67566754345);
				$displayOldAuthorisedNACredit->setLabel("Previous Authorisations");
				$displayOldAuthorisedNACredit->setLink("/apps/complaints/index?id=" . $this->complaint->form->get("id")->getValue() . "");
				$displayOldAuthorisedNACredit->setOpenNewWindow(0);
				$displayOldAuthorisedNACredit->setValue("(" . mysql_num_rows($datasetReturns) . ") {TRANSLATE:this_na_conclusion_old_authorisations}");
				$creditGroup->add($displayOldAuthorisedNACredit);
			}

			if($this->complaint->form->get("complaintValue")->getQuantity() < 2500)
			{
				$limits = "lower";
			}
			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 2500 && $this->complaint->form->get("complaintValue")->getQuantity() < 1000)
			{
				$limits = "lower_mid";
			}
			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 10000 && $this->complaint->form->get("complaintValue")->getQuantity() < 30000)
			{
				$limits = "upper_mid";
			}
			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 30000)
			{
				$limits = "upper";
			}
			else
			{
				$limits = "";
			}

			// Start NA Customer Complaint - Conclusion Stages
			$requestForCredit = new radio("requestForCredit");
			$requestForCredit->setGroup("creditGroup");
			$requestForCredit->setDataType("string");
			$requestForCredit->setLength(5);
			$requestForCredit->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$requestForCredit->setRowTitle("request_for_supplier_carrier_interco_credit");
			$requestForCredit->setRequired(false);
			if(isset($savedFields["requestForCredit"]))
				$requestForCredit->setValue($savedFields["requestForCredit"]);
			else $requestForCredit->setValue("NO");
			$requestForCredit->setTable("conclusion");
			$requestForCredit->setHelpId(9107);

			// Dependency
			$requestForCredit_dependency = new dependency();
			$requestForCredit_dependency->addRule(new rule('creditGroup', 'requestForCredit', 'YES'));
			$requestForCredit_dependency->setGroup('creditGroupYes');
			$requestForCredit_dependency->setShow(true);

			// Dependency
			$requestForCreditNo_dependency = new dependency();
			$requestForCreditNo_dependency->addRule(new rule('creditGroup', 'requestForCredit', 'NO'));
			$requestForCreditNo_dependency->setGroup('creditGroupNo');
			$requestForCreditNo_dependency->setShow(true);

			$requestForCredit->addControllingDependency($requestForCredit_dependency);
			$requestForCredit->addControllingDependency($requestForCreditNo_dependency);
			$creditGroup->add($requestForCredit);

			$transferOwnership = new dropdown("transferOwnership");
			if(isset($savedFields["transferOwnership"]))
				$transferOwnership->setValue($savedFields["transferOwnership"]);
			$transferOwnership->setGroup("creditGroupYes");
			$transferOwnership->setDataType("string");
			$transferOwnership->setLength(255);
			$transferOwnership->setRowTitle("send_request_to");
			$transferOwnership->setRequired(false);
			//$transferOwnership->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_american_credit_%' OR permissions.permission = 'complaints_return_approval_na_%' ORDER BY employee.NTLogon");
			$transferOwnership->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE `permission` LIKE 'complaints_return_approval_na_%' ORDER BY employee.NTLogon");
			$transferOwnership->setTable("conclusion");
			$transferOwnership->setHelpId(9108);
			$creditGroupYes->add($transferOwnership);

			$ccCommercialCredit = new autocomplete("ccCommercialCredit");
			if(isset($savedFields["ccCommercialCredit"]))
				$ccCommercialCredit->setValue($savedFields["ccCommercialCredit"]);
			$ccCommercialCredit->setGroup("creditGroupYes");
			$ccCommercialCredit->setDataType("string");
			$ccCommercialCredit->setLength(5);
			$ccCommercialCredit->setRowTitle("cc_to");
			$ccCommercialCredit->setRequired(false);
			$ccCommercialCredit->setUrl("/apps/complaints/ajax/sendCreditTo?");
			$ccCommercialCredit->setTable("conclusion");
			$ccCommercialCredit->setHelpId(9147);
			$creditGroupYes->add($ccCommercialCredit);

			$ccCommercialCreditComment = new textarea("ccCommercialCreditComment");
			if(isset($savedFields["ccCommercialCreditComment"]))
				$ccCommercialCreditComment->setValue($savedFields["ccCommercialCreditComment"]);
			$ccCommercialCreditComment->setGroup("creditGroupYes");
			$ccCommercialCreditComment->setDataType("text");
			$ccCommercialCreditComment->setRowTitle("comment");
			$ccCommercialCreditComment->setRequired(false);
			$ccCommercialCreditComment->setTable("conclusion");
			$ccCommercialCreditComment->setHelpId(9187);
			$creditGroupYes->add($ccCommercialCreditComment);

			$submit1 = new submit("submit1");
			$submit1->setGroup("creditGroupYes");
			$submit1->setVisible(true);
			$creditGroupYes->add($submit1);

			$creditAuthorisationStatus = new textbox("creditAuthorisationStatus");
			$creditAuthorisationStatus->setGroup("creditAuthorisationStatusGroup");
			$creditAuthorisationStatus->setDataType("string");
			$creditAuthorisationStatus->setLength(255);
			$creditAuthorisationStatus->setRowTitle("credit_authorisation_status");
			$creditAuthorisationStatus->setRequired(false);
			$creditAuthorisationStatus->setVisible(false);
			$creditAuthorisationStatus->setLabel("Credit Authorisation");
			$creditAuthorisationStatus->setTable("conclusion");
			if(isset($savedFields["creditAuthorisationStatus"]))
				$creditAuthorisationStatus->setValue($savedFields["creditAuthorisationStatus"]);
			else $creditAuthorisationStatus->setValue("Authorisation Process INCOMPLETE ");
			$creditAuthorisationStatus->setHelpId(9742);
			$creditAuthorisationStatusGroup->add($creditAuthorisationStatus);

			// Finance Credit Level (1st Stage) - Start
			$financeLevelCreditAuthorised = new radio("financeLevelCreditAuthorised");
			$financeLevelCreditAuthorised->setGroup("financeCreditStatusGroup");
			$financeLevelCreditAuthorised->setDataType("string");
			$financeLevelCreditAuthorised->setLength(5);
			$financeLevelCreditAuthorised->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$financeLevelCreditAuthorised->setRowTitle("finance_level_credit_authorised");
			$financeLevelCreditAuthorised->setRequired(false);
			$financeLevelCreditAuthorised->setLabel("FINANCE Credit Authorisation");
			$financeLevelCreditAuthorised->setTable("conclusion");
			$financeLevelCreditAuthorised->setHelpId(97429856);
			$financeCreditStatusGroup->add($financeLevelCreditAuthorised);



			$financeCreditAuthoriser = new textbox("financeCreditAuthoriser");
			if(isset($savedFields["financeCreditAuthoriser"]))
				$financeCreditAuthoriser->setValue($savedFields["financeCreditAuthoriser"]);
			$financeCreditAuthoriser->setGroup("financeCreditStatusGroup");
			$financeCreditAuthoriser->setDataType("string");
			$financeCreditAuthoriser->setLength(255);
			$financeCreditAuthoriser->setRowTitle("finance_credit_authoriser");
			$financeCreditAuthoriser->setRequired(false);
			$financeCreditAuthoriser->setVisible(false);
			$financeCreditAuthoriser->setTable("conclusion");
			$financeCreditAuthoriser->setHelpId(97429856456);
			$financeCreditStatusGroup->add($financeCreditAuthoriser);

			$financeCreditNewComplaintOwner = new dropdown("financeCreditNewComplaintOwner");
			if(isset($savedFields["financeCreditNewComplaintOwner"]))
				$financeCreditNewComplaintOwner->setValue($savedFields["financeCreditNewComplaintOwner"]);
			$financeCreditNewComplaintOwner->setGroup("financeCreditStatusGroup");
			$financeCreditNewComplaintOwner->setDataType("string");
			$financeCreditNewComplaintOwner->setLength(5);
			$financeCreditNewComplaintOwner->setRowTitle("back_to_complaint_owner");
			$financeCreditNewComplaintOwner->setRequired(false);
			$financeCreditNewComplaintOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
			$financeCreditNewComplaintOwner->setTable("conclusion");
			$financeCreditNewComplaintOwner->setHelpId(9742985645612);
			$financeCreditNewComplaintOwner->setVisible(false);
			$financeCreditStatusGroup->add($financeCreditNewComplaintOwner);

			$financeReason = new textarea("financeReason");
			if(isset($savedFields["financeReason"]))
				$financeReason->setValue($savedFields["financeReason"]);
			$financeReason->setGroup("financeCreditStatusGroup");
			$financeReason->setDataType("text");
			$financeReason->setRowTitle("finance_reason_commercial_level");
			$financeReason->setRequired(false);
			$financeReason->setTable("conclusion");
			$financeReason->setHelpId(9742985645632);
			$financeCreditStatusGroup->add($financeReason);

			$datasetCredit = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion WHERE complaintId = '"  . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fieldsCredit = mysql_fetch_array($datasetCredit);

			if ($fieldsCredit > 0)
			{
				$financeCredAuthorised = $fieldsCredit['commercialLevelCreditAuthorised'];
				$financeCredAuthoriser = usercache::getInstance()->get($fieldsCredit['commercialCreditAuthoriser'])->getName();
				$financeCredAuthOwner = usercache::getInstance()->get($fieldsCredit['commercialCreditNewFinanceOwner'])->getName();
				$financeCredAuthReason = $fieldsCredit['commercialReason'];
			}

			$financeLevelCreditAuthorised2 = new readonly("financeLevelCreditAuthorised2");
			$financeLevelCreditAuthorised2->setGroup("financeCreditStatusGroup");
			$financeLevelCreditAuthorised2->setDataType("string");
			$financeLevelCreditAuthorised2->setLength(5);
			$financeLevelCreditAuthorised2->setRowTitle("finance_level_credit_authorised");
			$financeLevelCreditAuthorised2->setRequired(false);
			$financeLevelCreditAuthorised2->setLabel("FINANCE Credit Authorisation");
			$fieldsCredit > 0 ? $financeLevelCreditAuthorised2->setValue("" . $financeCredAuthorised . "") : "";
			$financeLevelCreditAuthorised2->setTable("conclusion");
			$financeLevelCreditAuthorised2->setHelpId(9189);
			$financeCreditStatusGroup->add($financeLevelCreditAuthorised2);


			$reAuthoriseNACreditReadOnly = new textboxlink("reAuthoriseNACreditReadOnly");
			$reAuthoriseNACreditReadOnly->setRowTitle("re_authorise_na_credit");
			$reAuthoriseNACreditReadOnly->setHelpId(67566754);
			$reAuthoriseNACreditReadOnly->setLink("/apps/complaints/reAuthorise?complaint=" . $this->complaint->form->get("id")->getValue() . "&amp;status=conclusion&amp;mode=returnNACreditRequest");
			$reAuthoriseNACreditReadOnly->setOpenNewWindow(0);
			$reAuthoriseNACreditReadOnly->setValue("{TRANSLATE:re_authorise_na_credit_link}");
			$financeCreditStatusGroup->add($reAuthoriseNACreditReadOnly);

			$financeCreditAuthoriser2 = new readonly("financeCreditAuthoriser2");
			$financeCreditAuthoriser2->setGroup("financeCreditStatusGroup");
			$financeCreditAuthoriser2->setDataType("string");
			$financeCreditAuthoriser2->setLength(255);
			$financeCreditAuthoriser2->setRowTitle("finance_credit_authoriser");
			$financeCreditAuthoriser2->setRequired(false);
			$fieldsCredit > 0 ? $financeCreditAuthoriser2->setValue("" . $financeCredAuthoriser . "") : "";
			$financeCreditAuthoriser2->setVisible(true);
			$financeCreditAuthoriser2->setTable("conclusion");
			$financeCreditAuthoriser2->setHelpId(9190);
			$financeCreditStatusGroup->add($financeCreditAuthoriser2);

			$financeCreditNewComplaintOwner2 = new readonly("financeCreditNewComplaintOwner2");
			$financeCreditNewComplaintOwner2->setGroup("financeCreditStatusGroup");
			$financeCreditNewComplaintOwner2->setDataType("string");
			$financeCreditNewComplaintOwner2->setLength(5);
			$financeCreditNewComplaintOwner2->setRowTitle("back_to_complaint_owner");
			$financeCreditNewComplaintOwner2->setRequired(false);
			$fieldsCredit > 0 ? $financeCreditNewComplaintOwner2->setValue("" . $financeCredAuthOwner . "") : "";
			$financeCreditNewComplaintOwner2->setTable("conclusion");
			$financeCreditNewComplaintOwner2->setHelpId(9110);
			$financeCreditStatusGroup->add($financeCreditNewComplaintOwner2);

			$financeReason2 = new readonly("financeReason2");
			$financeReason2->setGroup("commercialCreditStatusGroup");
			$financeReason2->setDataType("text");
			$financeReason2->setRowTitle("finance_reason_commercial_level");
			$financeReason2->setRequired(false);
			$financeReason2->setTable("conclusion");
			$fieldsCredit > 0 ? $financeReason2->setValue("" . $financeCredAuthReason . "") : "";
			$financeReason2->setHelpId(9191);
			$financeCreditStatusGroup->add($financeReason2);

			$financeStageCompleted = new radio("financeStageCompleted");
			$financeStageCompleted->setGroup("financeCreditStatusGroup");
			$financeStageCompleted->setDataType("string");
			$financeStageCompleted->setLength(5);
			$financeStageCompleted->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$financeStageCompleted->setRowTitle("finance_stage_completed");
			$financeStageCompleted->setRequired(false);
			$financeStageCompleted->setTable("conclusion");
			$financeStageCompleted->setVisible(false);
			$financeStageCompleted->setValue("NO");
			$financeCreditStatusGroup->add($financeStageCompleted);

			$submit3 = new submit("submit3");
			$submit3->setGroup("financeCreditStatusGroup");
			$submit3->setVisible(true);
			$financeCreditStatusGroup->add($submit3);
			// Finance Credit Level (1st Stage) - End
		}
		else
		{
			// Start European Customer Complaint - Conclusion Stages
			$requestForCredit = new radio("requestForCredit");
			$requestForCredit->setGroup("creditGroup");
			$requestForCredit->setDataType("string");
			$requestForCredit->setLength(5);
			$requestForCredit->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$requestForCredit->setRowTitle("request_for_supplier_carrier_interco_credit");
			$requestForCredit->setRequired(false);
			//if($fieldsLocation["creditNoteRequested"] == 'NO'){$requestForCredit->setVisible(false);} else {$requestForCredit->setVisible(true);}
			if(isset($savedFields["requestForCredit"]))
				$requestForCredit->setValue($savedFields["requestForCredit"]);
			else $requestForCredit->setValue("NO");
			if($fieldsLocation['complaintLocation'] == 'american')
			{
				$requestForCredit->setOnKeyPress("update_american_credit_list();");
			}
			$requestForCredit->setTable("conclusion");
			$requestForCredit->setHelpId(9107);

			// Dependency
			$requestForCredit_dependency = new dependency();
			$requestForCredit_dependency->addRule(new rule('creditGroup', 'requestForCredit', 'YES'));
			$requestForCredit_dependency->setGroup('creditGroupYes');
			$requestForCredit_dependency->setShow(true);

			// Dependency
			$requestForCreditNo_dependency = new dependency();
			$requestForCreditNo_dependency->addRule(new rule('creditGroup', 'requestForCredit', 'NO'));
			$requestForCreditNo_dependency->setGroup('creditGroupNo');
			$requestForCreditNo_dependency->setShow(true);

			$requestForCredit->addControllingDependency($requestForCredit_dependency);
			$requestForCredit->addControllingDependency($requestForCreditNo_dependency);
			$creditGroup->add($requestForCredit);

			$transferOwnership = new dropdown("transferOwnership");
			if(isset($savedFields["transferOwnership"]))
				$transferOwnership->setValue($savedFields["transferOwnership"]);
			$transferOwnership->setGroup("creditGroupYes");
			$transferOwnership->setDataType("string");
			$transferOwnership->setLength(255);
			$transferOwnership->setRowTitle("send_request_to");
			$transferOwnership->setRequired(false);
			//die($this->getComplaint()->form->get("businessUnit")->getValue());
			//$transferOwnership->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_commercial_lower_" . $businessUnit_value . "' OR permissions.permission = 'complaints_commercial_higher_" . $businessUnit_value . "' ORDER BY employee.NTLogon");
			$transferOwnership->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_commercial_lower_" . $this->getComplaint()->form->get("businessUnit")->getValue() . "' ORDER BY employee.NTLogon");
			$transferOwnership->setArraySource(array(
				array('value' => 'jmatthews', 'display' => 'Jason Matthews'),
			));
			$transferOwnership->clearData();
			$transferOwnership->setTable("conclusion");
			$transferOwnership->setHelpId(9108);
			$creditGroupYes->add($transferOwnership);

			$ccCommercialCredit = new autocomplete("ccCommercialCredit");
			if(isset($savedFields["ccCommercialCredit"]))
				$ccCommercialCredit->setValue($savedFields["ccCommercialCredit"]);
			$ccCommercialCredit->setGroup("creditGroupYes");
			$ccCommercialCredit->setDataType("string");
			$ccCommercialCredit->setLength(5);
			$ccCommercialCredit->setRowTitle("cc_to");
			$ccCommercialCredit->setRequired(false);
			$ccCommercialCredit->setUrl("/apps/complaints/ajax/sendCreditTo?");
			$ccCommercialCredit->setTable("conclusion");
			$ccCommercialCredit->setHelpId(9147);
			$creditGroupYes->add($ccCommercialCredit);

			$ccCommercialCreditComment = new textarea("ccCommercialCreditComment");
			if(isset($savedFields["ccCommercialCreditComment"]))
				$ccCommercialCreditComment->setValue($savedFields["ccCommercialCreditComment"]);
			$ccCommercialCreditComment->setGroup("creditGroupYes");
			$ccCommercialCreditComment->setDataType("text");
			$ccCommercialCreditComment->setRowTitle("comment");
			$ccCommercialCreditComment->setRequired(false);
			$ccCommercialCreditComment->setTable("conclusion");
			$ccCommercialCreditComment->setHelpId(9187);
			$creditGroupYes->add($ccCommercialCreditComment);

			$submit1 = new submit("submit1");
			$submit1->setGroup("creditGroupYes");
			$submit1->setVisible(true);
			$creditGroupYes->add($submit1);


			$creditAuthorisationStatus = new textbox("creditAuthorisationStatus");
			$creditAuthorisationStatus->setGroup("creditAuthorisationStatusGroup");
			$creditAuthorisationStatus->setDataType("string");
			$creditAuthorisationStatus->setLength(255);
			$creditAuthorisationStatus->setRowTitle("credit_authorisation_status");
			$creditAuthorisationStatus->setRequired(false);
			$creditAuthorisationStatus->setVisible(false);
			$creditAuthorisationStatus->setLabel("Credit Authorisation");
			$creditAuthorisationStatus->setTable("conclusion");
			if(isset($savedFields["creditAuthorisationStatus"]))
				$creditAuthorisationStatus->setValue($savedFields["creditAuthorisationStatus"]);
			else $creditAuthorisationStatus->setValue("Authorisation Process INCOMPLETE ");
			$creditAuthorisationStatus->setHelpId(9742);
			$creditAuthorisationStatusGroup->add($creditAuthorisationStatus);

			/* Commercial Credit Level Authorised Advice (1st Stage) - Start */
			$commercialLevelCreditAuthorisedAdvise = new radio("commercialLevelCreditAuthorisedAdvise");
			$commercialLevelCreditAuthorisedAdvise->setGroup("commercialCreditStatusGroupAdvise");
			$commercialLevelCreditAuthorisedAdvise->setDataType("string");
			$commercialLevelCreditAuthorisedAdvise->setLength(5);
			$commercialLevelCreditAuthorisedAdvise->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			if(isset($savedFields["commercialLevelCreditAuthorisedAdvise"]))
				$commercialLevelCreditAuthorisedAdvise->setValue($savedFields["commercialLevelCreditAuthorisedAdvise"]);
			$commercialLevelCreditAuthorisedAdvise->setRowTitle("credit_authorised_commercial_level_advise");
			$commercialLevelCreditAuthorisedAdvise->setRequired(false);
			$commercialLevelCreditAuthorisedAdvise->setLabel("COMMERCIAL Credit Authorisation - Advice");
			$commercialLevelCreditAuthorisedAdvise->setTable("conclusion");
			$commercialLevelCreditAuthorisedAdvise->setHelpId(9178);
			//$commercialCreditStatusGroupAdvise->add($commercialLevelCreditAuthorisedAdvise);

			// Dependency
			$adviseDependency = new dependency();
			$adviseDependency->addRule(new rule('commercialCreditStatusGroupAdvise', 'commercialLevelCreditAuthorisedAdvise', 'NO'));
			$adviseDependency->setGroup('adviseAcceptedYes');
			$adviseDependency->setShow(false);

			$commercialLevelCreditAuthorisedAdvise->addControllingDependency($adviseDependency);
			$commercialCreditStatusGroupAdvise->add($commercialLevelCreditAuthorisedAdvise);

			$commercialCreditAuthoriserAdvise = new textbox("commercialCreditAuthoriserAdvise");
			if(isset($savedFields["commercialCreditAuthoriserAdvise"]))
				$commercialCreditAuthoriserAdvise->setValue($savedFields["commercialCreditAuthoriserAdvise"]);
			$commercialCreditAuthoriserAdvise->setGroup("commercialCreditStatusGroupAdvise");
			$commercialCreditAuthoriserAdvise->setDataType("string");
			$commercialCreditAuthoriserAdvise->setLength(255);
			$commercialCreditAuthoriserAdvise->setRowTitle("credit_authoriser_advise");
			$commercialCreditAuthoriserAdvise->setRequired(false);
			$commercialCreditAuthoriserAdvise->setVisible(false);
			$commercialCreditAuthoriserAdvise->setTable("conclusion");
			$commercialCreditAuthoriserAdvise->setHelpId(9123);
			//$commercialCreditStatusGroupAdvise->add($commercialCreditAuthoriserAdvise);
			$adviseAcceptedYes->add($commercialCreditAuthoriserAdvise);


			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT creditNoteGBP_quantity, creditNoteValue_measurement, creditNoteValue_quantity FROM conclusion WHERE complaintId = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fields = mysql_fetch_array($dataset);

			if($fields['creditNoteValue_measurement'] == 'EUR')
			{
				if($fields['creditNoteValue_quantity'] > 5500)
				{
					$ext = "higher";
				}
				else
				{
					$ext = "lower";
				}
			}
			else
			{
				if($fields['creditNoteGBP_quantity'] > 5000)
				{
					$ext = "higher";
				}
				else
				{
					$ext = "lower";
				}
			}

			$commercialCreditNewCommercialOwner = new dropdown("commercialCreditNewCommercialOwner");
			if(isset($savedFields["commercialCreditNewCommercialOwner"]))
				$commercialCreditNewCommercialOwner->setValue($savedFields["commercialCreditNewCommercialOwner"]);
			$commercialCreditNewCommercialOwner->setGroup("commercialCreditStatusGroupAdvise");
			$commercialCreditNewCommercialOwner->setDataType("string");
			$commercialCreditNewCommercialOwner->setLength(5);
			$commercialCreditNewCommercialOwner->setRowTitle("send_to_commercial_owner_level_two");
			$commercialCreditNewCommercialOwner->setRequired(false);
			$commercialCreditNewCommercialOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_commercial_" . $ext . "_" . $this->getComplaint()->form->get("businessUnit")->getValue() . "' ORDER BY employee.NTLogon");
			$commercialCreditNewCommercialOwner->clearData();
			$commercialCreditNewCommercialOwner->setArraySource(array(
				array('value' => 'jmatthews', 'display' => 'Jason Matthews')
			));
			$commercialCreditNewCommercialOwner->setTable("conclusion");
			$commercialCreditNewCommercialOwner->setHelpId(9108);
			//$commercialCreditStatusGroupAdvise->add($commercialCreditNewCommercialOwner);
			$adviseAcceptedYes->add($commercialCreditNewCommercialOwner);

			$commercialReasonAdvise = new textarea("commercialReasonAdvise");
			if(isset($savedFields["commercialReasonAdvise"]))
				$commercialReasonAdvise->setValue($savedFields["commercialReasonAdvise"]);
			$commercialReasonAdvise->setGroup("commercialCreditStatusGroupAdvise");
			$commercialReasonAdvise->setDataType("text");
			$commercialReasonAdvise->setRowTitle("commercial_reason_commercial_level_advise");
			$commercialReasonAdvise->setRequired(false);
			$commercialReasonAdvise->setTable("conclusion");
			$commercialReasonAdvise->setHelpId(9114);
			//$commercialCreditStatusGroupAdvise->add($commercialReasonAdvise);
			$commercialAdviseReason->add($commercialReasonAdvise);

			$datasetCreditCommercialAdvise = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion WHERE complaintId = '"  . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fieldsCreditCommercialAdvise = mysql_fetch_array($datasetCreditCommercialAdvise);

			if ($fieldsCreditCommercialAdvise > 0)
			{
				$commercialCredAuthorisedAdvise = $fieldsCreditCommercialAdvise['commercialLevelCreditAuthorisedAdvise'];
				$commercialCredAuthoriserAdvise = usercache::getInstance()->get($fieldsCreditCommercialAdvise['commercialCreditAuthoriserAdvise'])->getName();
				$commercialCredAuthOwnerAdvise = usercache::getInstance()->get($fieldsCreditCommercialAdvise['commercialCreditNewCommercialOwner'])->getName();
				$commercialCredAuthReasonAdvise = $fieldsCreditCommercialAdvise['commercialReasonAdvise'];
			}

			$commercialLevelCreditAuthorisedAdvise2 = new readonly("commercialLevelCreditAuthorisedAdvise2");
			$commercialLevelCreditAuthorisedAdvise2->setGroup("commercialCreditStatusGroupAdvise");
			$commercialLevelCreditAuthorisedAdvise2->setDataType("string");
			$commercialLevelCreditAuthorisedAdvise2->setLength(5);
			$commercialLevelCreditAuthorisedAdvise2->setRowTitle("credit_authorised_commercial_level_advise");
			$commercialLevelCreditAuthorisedAdvise2->setRequired(false);
			$commercialLevelCreditAuthorisedAdvise2->setLabel("COMMERCIAL Credit Authorisation - Advice");
			$fieldsCreditCommercialAdvise > 0 ? $commercialLevelCreditAuthorisedAdvise2->setValue("" . $commercialCredAuthorisedAdvise . "") : "";
			$commercialLevelCreditAuthorisedAdvise2->setTable("conclusion");
			$commercialLevelCreditAuthorisedAdvise2->setHelpId(9189);
			$commercialCreditStatusGroupAdvise->add($commercialLevelCreditAuthorisedAdvise2);

			$commercialCreditAuthoriserAdvise2 = new readonly("commercialCreditAuthoriserAdvise2");
			$commercialCreditAuthoriserAdvise2->setGroup("commercialCreditStatusGroupAdvise");
			$commercialCreditAuthoriserAdvise2->setDataType("string");
			$commercialCreditAuthoriserAdvise2->setLength(255);
			$commercialCreditAuthoriserAdvise2->setRowTitle("credit_authoriser_advise");
			$commercialCreditAuthoriserAdvise2->setRequired(false);
			$fieldsCreditCommercialAdvise > 0 ? $commercialCreditAuthoriserAdvise2->setValue("" . $commercialCredAuthoriserAdvise . "") : "";
			$commercialCreditAuthoriserAdvise2->setVisible(true);
			$commercialCreditAuthoriserAdvise2->setTable("conclusion");
			$commercialCreditAuthoriserAdvise2->setHelpId(9190);
			$commercialCreditStatusGroupAdvise->add($commercialCreditAuthoriserAdvise2);

			$commercialCreditNewCommercialOwner2 = new readonly("commercialCreditNewCommercialOwner2");
			$commercialCreditNewCommercialOwner2->setGroup("commercialCreditStatusGroupAdvise");
			$commercialCreditNewCommercialOwner2->setDataType("string");
			$commercialCreditNewCommercialOwner2->setLength(5);
			$commercialCreditNewCommercialOwner2->setRowTitle("send_to_commercial_owner_level_two");
			$commercialCreditNewCommercialOwner2->setRequired(false);
			$fieldsCreditCommercialAdvise > 0 ? $commercialCreditNewCommercialOwner2->setValue("" . $commercialCredAuthOwnerAdvise . "") : "";
			$commercialCreditNewCommercialOwner2->setTable("conclusion");
			$commercialCreditNewCommercialOwner2->setHelpId(9108);
			$commercialCreditStatusGroupAdvise->add($commercialCreditNewCommercialOwner2);

			$commercialReasonAdvise2 = new readonly("commercialReasonAdvise2");
			$commercialReasonAdvise2->setGroup("commercialCreditStatusGroupAdvise");
			$commercialReasonAdvise2->setDataType("text");
			$commercialReasonAdvise2->setRowTitle("commercial_reason_commercial_level");
			$commercialReasonAdvise2->setRequired(false);
			$commercialReasonAdvise2->setTable("conclusion");
			$fieldsCreditCommercialAdvise > 0 ? $commercialReasonAdvise2->setValue("" . $commercialCredAuthReasonAdvise . "") : "";
			$commercialReasonAdvise2->setHelpId(9191);
			$commercialCreditStatusGroupAdvise->add($commercialReasonAdvise2);
			/* Commercial Credit Level Authorised Advice (1st Stage) - End */

			$submitAdvise = new submit("submitAdvise");
			$submitAdvise->setGroup("commercialCreditStatusGroupAdvise");
			$submitAdvise->setVisible(true);
			//$commercialCreditStatusGroupAdvise->add($submitAdvise);
			$commercialAdviseReason->add($submitAdvise);

			/* Commercial Credit Level Authorised (2nd Stage) - Start */
			$commercialLevelCreditAuthorised = new radio("commercialLevelCreditAuthorised");
			$commercialLevelCreditAuthorised->setGroup("commercialCreditStatusGroup");
			$commercialLevelCreditAuthorised->setDataType("string");
			$commercialLevelCreditAuthorised->setLength(5);
			$commercialLevelCreditAuthorised->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			if(isset($savedFields["commercialLevelCreditAuthorised"]))
				$commercialLevelCreditAuthorised->setValue($savedFields["commercialLevelCreditAuthorised"]);
			$commercialLevelCreditAuthorised->setRowTitle("credit_authorised_commercial_level");
			$commercialLevelCreditAuthorised->setRequired(false);
			$commercialLevelCreditAuthorised->setLabel("COMMERCIAL Credit Authorisation - LEVEL 2");
			$commercialLevelCreditAuthorised->setTable("conclusion");
			$commercialLevelCreditAuthorised->setHelpId(9178);
			//$commercialCreditStatusGroup->add($commercialLevelCreditAuthorised);

			// Dependency
			$commercialDependency = new dependency();
			$commercialDependency->addRule(new rule('commercialCreditStatusGroup', 'commercialLevelCreditAuthorised', 'NO'));
			$commercialDependency->setGroup('commercialAcceptedYes');
			$commercialDependency->setShow(false);

			$commercialLevelCreditAuthorised->addControllingDependency($commercialDependency);
			$commercialCreditStatusGroup->add($commercialLevelCreditAuthorised);

			$commercialCreditAuthoriser = new textbox("commercialCreditAuthoriser");
			if(isset($savedFields["commercialCreditAuthoriser"]))
				$commercialCreditAuthoriser->setValue($savedFields["commercialCreditAuthoriser"]);
			$commercialCreditAuthoriser->setGroup("commercialCreditStatusGroup");
			$commercialCreditAuthoriser->setDataType("string");
			$commercialCreditAuthoriser->setLength(255);
			$commercialCreditAuthoriser->setRowTitle("credit_authoriser");
			$commercialCreditAuthoriser->setRequired(false);
			$commercialCreditAuthoriser->setVisible(false);
			$commercialCreditAuthoriser->setTable("conclusion");
			$commercialCreditAuthoriser->setHelpId(9123);
			//$commercialCreditStatusGroup->add($commercialCreditAuthoriser);
			$commercialAcceptedYes->add($commercialCreditAuthoriser);


			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT creditNoteGBP_quantity, creditNoteValue_measurement, creditNoteValue_quantity FROM conclusion WHERE complaintId = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fields = mysql_fetch_array($dataset);

			if($fields['creditNoteValue_measurement'] == 'EUR')
			{
				if($fields['creditNoteValue_quantity'] > 5500)
				{
					$ext = "higher";
				}
				else
				{
					$ext = "lower";
				}
			}
			else
			{
				if($fields['creditNoteGBP_quantity'] > 5000)
				{
					$ext = "higher";
				}
				else
				{
					$ext = "lower";
				}
			}


			$commercialCreditNewFinanceOwner = new dropdown("commercialCreditNewFinanceOwner");
			if(isset($savedFields["commercialCreditNewFinanceOwner"]))
				$commercialCreditNewFinanceOwner->setValue($savedFields["commercialCreditNewFinanceOwner"]);
			$commercialCreditNewFinanceOwner->setGroup("commercialCreditStatusGroup");
			$commercialCreditNewFinanceOwner->setDataType("string");
			$commercialCreditNewFinanceOwner->setLength(5);
			$commercialCreditNewFinanceOwner->setRowTitle("send_to_finance_owner");
			$commercialCreditNewFinanceOwner->setRequired(false);
			$commercialCreditNewFinanceOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_finance_" . $ext . "_" . $this->getComplaint()->form->get("businessUnit")->getValue() . "' ORDER BY employee.NTLogon");
			$commercialCreditNewFinanceOwner->setArraySource(array(
				array('value' => 'jmatthews', 'display' => 'Jason Matthews')
			));
			$commercialCreditNewFinanceOwner->clearData();
			$commercialCreditNewFinanceOwner->setTable("conclusion");
			$commercialCreditNewFinanceOwner->setHelpId(9109);
			//$commercialCreditStatusGroup->add($commercialCreditNewFinanceOwner);
			$commercialAcceptedYes->add($commercialCreditNewFinanceOwner);

			$commercialReason = new textarea("commercialReason");
			if(isset($savedFields["commercialReason"]))
				$commercialReason->setValue($savedFields["commercialReason"]);
			$commercialReason->setGroup("commercialCreditStatusGroup");
			$commercialReason->setDataType("text");
			$commercialReason->setRowTitle("commercial_reason_commercial_level");
			$commercialReason->setRequired(false);
			$commercialReason->setTable("conclusion");
			$commercialReason->setHelpId(9114);
			//$commercialCreditStatusGroup->add($commercialReason);
			$commercialAcceptedReason->add($commercialReason);

			$datasetCreditCommercial = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion WHERE complaintId = '"  . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fieldsCreditCommercial = mysql_fetch_array($datasetCreditCommercial);

			if ($fieldsCreditCommercial > 0)
			{
				$commercialCredAuthorised = $fieldsCreditCommercial['commercialLevelCreditAuthorised'];
				$commercialCredAuthoriser = usercache::getInstance()->get($fieldsCreditCommercial['commercialCreditAuthoriser'])->getName();
				$commercialCredAuthOwner = usercache::getInstance()->get($fieldsCreditCommercial['commercialCreditNewFinanceOwner'])->getName();
				$commercialCredAuthReason = $fieldsCreditCommercial['commercialReason'];
			}

			$commercialLevelCreditAuthorised2 = new readonly("commercialLevelCreditAuthorised2");
			$commercialLevelCreditAuthorised2->setGroup("commercialCreditStatusGroup");
			$commercialLevelCreditAuthorised2->setDataType("string");
			$commercialLevelCreditAuthorised2->setLength(5);
			$commercialLevelCreditAuthorised2->setRowTitle("commercial_level_credit_authorised");
			$commercialLevelCreditAuthorised2->setRequired(false);
			$commercialLevelCreditAuthorised2->setLabel("COMMERCIAL Credit Authorisation - LEVEL 2");
			$fieldsCreditCommercial > 0 ? $commercialLevelCreditAuthorised2->setValue("" . $commercialCredAuthorised . "") : "";
			$commercialLevelCreditAuthorised2->setTable("conclusion");
			$commercialLevelCreditAuthorised2->setHelpId(9189);
			$commercialCreditStatusGroup->add($commercialLevelCreditAuthorised2);

			$commercialCreditAuthoriser2 = new readonly("commercialCreditAuthoriser2");
			$commercialCreditAuthoriser2->setGroup("commercialCreditStatusGroup");
			$commercialCreditAuthoriser2->setDataType("string");
			$commercialCreditAuthoriser2->setLength(255);
			$commercialCreditAuthoriser2->setRowTitle("credit_authoriser");
			$commercialCreditAuthoriser2->setRequired(false);
			$fieldsCreditCommercial > 0 ? $commercialCreditAuthoriser2->setValue("" . $commercialCredAuthoriser . "") : "";
			$commercialCreditAuthoriser2->setVisible(true);
			$commercialCreditAuthoriser2->setTable("conclusion");
			$commercialCreditAuthoriser2->setHelpId(9190);
			$commercialCreditStatusGroup->add($commercialCreditAuthoriser2);

			$commercialCreditNewFinanceOwner2 = new readonly("commercialCreditNewFinanceOwner2");
			$commercialCreditNewFinanceOwner2->setGroup("commercialCreditStatusGroup");
			$commercialCreditNewFinanceOwner2->setDataType("string");
			$commercialCreditNewFinanceOwner2->setLength(5);
			$commercialCreditNewFinanceOwner2->setRowTitle("send_to_finance_owner");
			$commercialCreditNewFinanceOwner2->setRequired(false);
			$fieldsCreditCommercial > 0 ? $commercialCreditNewFinanceOwner2->setValue("" . $commercialCredAuthOwner . "") : "";
			$commercialCreditNewFinanceOwner2->setTable("conclusion");
			$commercialCreditNewFinanceOwner2->setHelpId(9109);
			$commercialCreditStatusGroup->add($commercialCreditNewFinanceOwner2);

			$commercialReason2 = new readonly("commercialReason2");
			$commercialReason2->setGroup("commercialCreditStatusGroup");
			$commercialReason2->setDataType("text");
			$commercialReason2->setRowTitle("commercial_reason_commercial_level");
			$commercialReason2->setRequired(false);
			$commercialReason2->setTable("conclusion");
			$fieldsCreditCommercial > 0 ? $commercialReason2->setValue("" . $commercialCredAuthReason . "") : "";
			$commercialReason2->setHelpId(9191);
			$commercialCreditStatusGroup->add($commercialReason2);
			/* Commercial Credit Level Authorised (2nd Stage) - End */


			$submit2 = new submit("submit2");
			$submit2->setGroup("commercialCreditStatusGroup");
			$submit2->setVisible(true);
			//$commercialCreditStatusGroup->add($submit2);
			$commercialAcceptedReason->add($submit2);

			/////////////////////

			$financeLevelCreditAuthorised = new radio("financeLevelCreditAuthorised");
			$financeLevelCreditAuthorised->setGroup("financeCreditStatusGroup");
			$financeLevelCreditAuthorised->setDataType("string");
			$financeLevelCreditAuthorised->setLength(5);
			$financeLevelCreditAuthorised->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$financeLevelCreditAuthorised->setRowTitle("finance_level_credit_authorised");
			$financeLevelCreditAuthorised->setRequired(false);
			$financeLevelCreditAuthorised->setLabel("FINANCE Credit Authorisation");
			$financeLevelCreditAuthorised->setTable("conclusion");
			$financeLevelCreditAuthorised->setHelpId(9742985645654);
			$financeCreditStatusGroup->add($financeLevelCreditAuthorised);


			$financeCreditAuthoriser = new textbox("financeCreditAuthoriser");
			if(isset($savedFields["financeCreditAuthoriser"]))
				$financeCreditAuthoriser->setValue($savedFields["financeCreditAuthoriser"]);
			$financeCreditAuthoriser->setGroup("financeCreditStatusGroup");
			$financeCreditAuthoriser->setDataType("string");
			$financeCreditAuthoriser->setLength(255);
			$financeCreditAuthoriser->setRowTitle("finance_credit_authoriser");
			$financeCreditAuthoriser->setRequired(false);
			$financeCreditAuthoriser->setVisible(false);
			$financeCreditAuthoriser->setTable("conclusion");
			$financeCreditAuthoriser->setHelpId(9742985645687);
			$financeCreditStatusGroup->add($financeCreditAuthoriser);

			$financeCreditNewComplaintOwner = new dropdown("financeCreditNewComplaintOwner");
			if(isset($savedFields["financeCreditNewComplaintOwner"]))
				$financeCreditNewComplaintOwner->setValue($savedFields["financeCreditNewComplaintOwner"]);
			$financeCreditNewComplaintOwner->setGroup("financeCreditStatusGroup");
			$financeCreditNewComplaintOwner->setDataType("string");
			$financeCreditNewComplaintOwner->setLength(5);
			$financeCreditNewComplaintOwner->setRowTitle("back_to_complaint_owner");
			$financeCreditNewComplaintOwner->setRequired(false);
			$financeCreditNewComplaintOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
			$financeCreditNewComplaintOwner->setTable("conclusion");
			$financeCreditNewComplaintOwner->setHelpId(97429856456123);
			$financeCreditNewComplaintOwner->setVisible(false);
			$financeCreditStatusGroup->add($financeCreditNewComplaintOwner);

			$financeReason = new textarea("financeReason");
			if(isset($savedFields["financeReason"]))
				$financeReason->setValue($savedFields["financeReason"]);
			$financeReason->setGroup("financeCreditStatusGroup");
			$financeReason->setDataType("text");
			$financeReason->setRowTitle("finance_reason_commercial_level");
			$financeReason->setRequired(false);
			$financeReason->setTable("conclusion");
			$financeReason->setHelpId(97429856456091);
			$financeCreditStatusGroup->add($financeReason);

			$datasetCredit = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion WHERE complaintId = '"  . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fieldsCredit = mysql_fetch_array($datasetCredit);

			if ($fieldsCredit > 0)
			{
				$financeCredAuthorised = $fieldsCredit['commercialLevelCreditAuthorised'];
				$financeCredAuthoriser = usercache::getInstance()->get($fieldsCredit['commercialCreditAuthoriser'])->getName();
				$financeCredAuthOwner = usercache::getInstance()->get($fieldsCredit['commercialCreditNewFinanceOwner'])->getName();
				$financeCredAuthReason = $fieldsCredit['commercialReason'];
			}

			$financeLevelCreditAuthorised2 = new readonly("financeLevelCreditAuthorised2");
			$financeLevelCreditAuthorised2->setGroup("financeCreditStatusGroup");
			$financeLevelCreditAuthorised2->setDataType("string");
			$financeLevelCreditAuthorised2->setLength(5);
			$financeLevelCreditAuthorised2->setRowTitle("finance_level_credit_authorised");
			$financeLevelCreditAuthorised2->setRequired(false);
			$financeLevelCreditAuthorised2->setLabel("FINANCE Credit Authorisation");
			$fieldsCredit > 0 ? $financeLevelCreditAuthorised2->setValue("" . $financeCredAuthorised . "") : "";
			$financeLevelCreditAuthorised2->setTable("conclusion");
			$financeLevelCreditAuthorised2->setHelpId(9189);
			$financeCreditStatusGroup->add($financeLevelCreditAuthorised2);

			$financeCreditAuthoriser2 = new readonly("financeCreditAuthoriser2");
			$financeCreditAuthoriser2->setGroup("financeCreditStatusGroup");
			$financeCreditAuthoriser2->setDataType("string");
			$financeCreditAuthoriser2->setLength(255);
			$financeCreditAuthoriser2->setRowTitle("finance_credit_authoriser");
			$financeCreditAuthoriser2->setRequired(false);
			$fieldsCredit > 0 ? $financeCreditAuthoriser2->setValue("" . $financeCredAuthoriser . "") : "";
			$financeCreditAuthoriser2->setVisible(true);
			$financeCreditAuthoriser2->setTable("conclusion");
			$financeCreditAuthoriser2->setHelpId(9190);
			$financeCreditStatusGroup->add($financeCreditAuthoriser2);

			$financeCreditNewComplaintOwner2 = new readonly("financeCreditNewComplaintOwner2");
			$financeCreditNewComplaintOwner2->setGroup("financeCreditStatusGroup");
			$financeCreditNewComplaintOwner2->setDataType("string");
			$financeCreditNewComplaintOwner2->setLength(5);
			$financeCreditNewComplaintOwner2->setRowTitle("back_to_complaint_owner");
			$financeCreditNewComplaintOwner2->setRequired(false);
			$fieldsCredit > 0 ? $financeCreditNewComplaintOwner2->setValue("" . $financeCredAuthOwner . "") : "";
			$financeCreditNewComplaintOwner2->setTable("conclusion");
			$financeCreditNewComplaintOwner2->setHelpId(9110);
			$financeCreditStatusGroup->add($financeCreditNewComplaintOwner2);

			$financeReason2 = new readonly("financeReason2");
			$financeReason2->setGroup("commercialCreditStatusGroup");
			$financeReason2->setDataType("text");
			$financeReason2->setRowTitle("finance_reason_commercial_level");
			$financeReason2->setRequired(false);
			$financeReason2->setTable("conclusion");
			$fieldsCredit > 0 ? $financeReason2->setValue("" . $financeCredAuthReason . "") : "";
			$financeReason2->setHelpId(9191);
			$financeCreditStatusGroup->add($financeReason2);

			$financeStageCompleted = new radio("financeStageCompleted");
			$financeStageCompleted->setGroup("financeCreditStatusGroup");
			$financeStageCompleted->setDataType("string");
			$financeStageCompleted->setLength(5);
			$financeStageCompleted->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$financeStageCompleted->setRowTitle("finance_stage_completed");
			$financeStageCompleted->setRequired(false);
			$financeStageCompleted->setTable("conclusion");
			$financeStageCompleted->setVisible(false);
			$financeStageCompleted->setValue("NO");
			$financeCreditStatusGroup->add($financeStageCompleted);

			$submit3 = new submit("submit3");
			$submit3->setGroup("financeCreditStatusGroup");
			$submit3->setVisible(true);
			$financeCreditStatusGroup->add($submit3);
		}



		/////////////////////

		$creditDeclined = new radio("creditDeclined");
		$creditDeclined->setGroup("creditGroup");
		$creditDeclined->setDataType("string");
		$creditDeclined->setLength(5);
		$creditDeclined->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		if(isset($savedFields["creditDeclined"]))
			$creditDeclined->setValue($savedFields["creditDeclined"]);
		$creditDeclined->setRowTitle("credit_declined");
		$creditDeclined->setRequired(false);
		$creditDeclined->setVisible(false);
		$creditDeclined->setTable("conclusion");
		$creditRaisedGroup->add($creditDeclined);

		$requestForCreditRaised = new radio("requestForCreditRaised");
		$requestForCreditRaised->setGroup("creditGroup");
		$requestForCreditRaised->setDataType("string");
		$requestForCreditRaised->setLength(5);
		$requestForCreditRaised->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		if(isset($savedFields["requestForCreditRaised"]))
			$requestForCreditRaised->setValue($savedFields["requestForCreditRaised"]);
		$requestForCreditRaised->setRowTitle("request_for_credit_raised");
		$requestForCreditRaised->setRequired(false);
		$requestForCreditRaised->setLabel("Supplier/Carrier/Interco Credit");
		$requestForCreditRaised->setTable("conclusion");
		$requestForCreditRaised->setHelpId(91090000000);


		// Dependency
		$requestForCreditRaised_dependency = new dependency();
		$requestForCreditRaised_dependency->addRule(new rule('creditRaisedGroup', 'requestForCreditRaised', 'YES'));
		$requestForCreditRaised_dependency->setGroup('creditRaisedGroupYes');
		$requestForCreditRaised_dependency->setShow(true);

		$requestForCreditRaised->addControllingDependency($requestForCreditRaised_dependency);
		$creditRaisedGroup->add($requestForCreditRaised);

		$creditNumber = new textbox("creditNumber");
		if(isset($savedFields["creditNumber"]))
			$creditNumber->setValue($savedFields["creditNumber"]);
		$creditNumber->setGroup("creditRaisedGroupYes");
		$creditNumber->setDataType("string");
		$creditNumber->setLength(255);
		$creditNumber->setRowTitle("credit_number");
		$creditNumber->setRequired(false);
		$creditNumber->setTable("conclusion");
		$creditNumber->setHelpId(910900000001);
		$creditRaisedGroupYes->add($creditNumber);

		$amount = new measurement("amount");
		if(isset($savedFields["amount_quantity"]) && isset($savedFields["amount_measurement"])){
			$arr[0] = $savedFields["amount_quantity"];
			$arr[1] = $savedFields["amount_measurement"];
			$amount->setValue($arr);
		}
		$amount->setGroup("creditRaisedGroupYes");
		$amount->setDataType("string");
		$amount->setLength(5);
		$amount->setXMLSource("./apps/complaints/xml/currency.xml");
		$amount->setRowTitle("amount");
		$amount->setRequired(false);
		$amount->setTable("conclusion");
		$amount->setHelpId(910900000002);
		$creditRaisedGroupYes->add($amount);

		//commmented out for Stefan 04/01/2008 - also on manipulate
		/*$informISDate = new textbox("informISDate");
		$informISDate->setGroup("creditRaisedGroupDef");
		$informISDate->setDataType("date");
		$informISDate->setErrorMessage("textbox_date_error");
		$informISDate->setLength(255);
		$informISDate->setRowTitle("inform_is_date");
		$informISDate->setRequired(false);
		$informISDate->setTable("conclusion");
		$informISDate->setHelpId(9112);
		$creditRaisedGroupDef->add($informISDate);*/

//		$transferOwnership2 = new radio("transferOwnership2");
//		$transferOwnership2->setGroup("creditRaisedGroupDef");
//		$transferOwnership2->setDataType("string");
//		$transferOwnership2->setLength(5);
//		$transferOwnership2->setArraySource(array(
//			array('value' => 'YES', 'display' => 'Yes'),
//			array('value' => 'NO', 'display' => 'No')
//		));
//		$transferOwnership2->setRowTitle("transfer_ownership");
//		$transferOwnership2->setRequired(false);
//		$transferOwnership2->setValue("NO");
//		$transferOwnership2->setTable("conclusion");
//		$transferOwnership2->setHelpId(9113);
//		$creditRaisedGroupDef->add($transferOwnership2);



		$customerComplaintStatus = new radio("customerComplaintStatus");
		$customerComplaintStatus->setGroup("complaintClosure");
		$customerComplaintStatus->setDataType("string");
		$customerComplaintStatus->setLength(5);
		$customerComplaintStatus->setArraySource(array(
			array('value' => 'Open', 'display' => 'Open'),
			array('value' => 'Closed', 'display' => 'Closed')
		));
		$customerComplaintStatus->setRowTitle("customer_complaint_status");
		$customerComplaintStatus->setRequired(false);
		if(isset($savedFields["customerComplaintStatus"]))
			$customerComplaintStatus->setValue($savedFields["customerComplaintStatus"]);
		else $customerComplaintStatus->setValue("Open");
		$customerComplaintStatus->setTable("conclusion");
		$customerComplaintStatus->setLabel("Complaint Closure");
		$customerComplaintStatus->setHelpId(9154);


		// Dependency
//		$customerComplaintStatus_dependency = new dependency();
//		$customerComplaintStatus_dependency->addRule(new rule('complaintClosure', 'customerComplaintStatus', 'Closed'));
//		$customerComplaintStatus_dependency->setGroup('customerComplaintStatusClosed');
//		$customerComplaintStatus_dependency->setShow(true);
//
//		$customerComplaintStatus->addControllingDependency($customerComplaintStatus_dependency);
		$complaintClosure->add($customerComplaintStatus);


		$customerCreditNumber = new textbox("customerCreditNumber");
		if(isset($savedFields["customerCreditNumber"]))
			$customerCreditNumber->setValue($savedFields["customerCreditNumber"]);
		$customerCreditNumber->setGroup("customerCreditGroup");
		$customerCreditNumber->setDataType("string");
		$customerCreditNumber->setLength(255);
		$customerCreditNumber->setRowTitle("customer_credit_number");
		$customerCreditNumber->setLabel("Customer Credit Number and Raised Date");
		$customerCreditNumber->setRequired(false);
		$customerCreditNumber->setTable("conclusion");
		$customerCreditNumber->setHelpId(9112);
		$customerCreditGroup->add($customerCreditNumber);

		$dateCreditNoteRaised = new calendar("dateCreditNoteRaised");
		if(isset($savedFields["dateCreditNoteRaised"]))
			$dateCreditNoteRaised->setValue($savedFields["dateCreditNoteRaised"]);
		$dateCreditNoteRaised->setGroup("customerCreditGroup");
		$dateCreditNoteRaised->setDataType("date");
		$dateCreditNoteRaised->setErrorMessage("textbox_date_error");
		$dateCreditNoteRaised->setLength(255);
		$dateCreditNoteRaised->setRowTitle("date_credit_note_raised");
		$dateCreditNoteRaised->setRequired(false);
		$dateCreditNoteRaised->setTable("conclusion");
		$dateCreditNoteRaised->setHelpId(910900000006);
		$customerCreditGroup->add($dateCreditNoteRaised);



		$finalComments = new textarea("finalComments");
		if(isset($savedFields["finalComments"]))
			$finalComments->setValue($savedFields["finalComments"]);
		$finalComments->setGroup("customerComplaintStatusClosed");
		$finalComments->setDataType("text");
		$finalComments->setRowTitle("finalComments");
		$finalComments->setRequired(false);
		$finalComments->setTable("conclusion");
		$finalComments->setHelpId(9114);
		$customerComplaintStatusClosed->add($finalComments);

		$emailMessage = new textarea("emailMessage");
		$emailMessage->setGroup("customerComplaintStatusClosed");
		$emailMessage->setDataType("text");
		$emailMessage->setRowTitle("emailMessage");
		$emailMessage->setRequired(false);
		$emailMessage->setTable("conclusion");
		$emailMessage->setVisible(false);
		$customerComplaintStatusClosed->add($emailMessage);

		$closedDate = new calendar("closedDate");
		if(isset($savedFields["closedDate"]))
			$closedDate->setValue($savedFields["closedDate"]);
		$closedDate->setGroup("customerComplaintStatusClosed");
		$closedDate->setDataType("date");
		$closedDate->setErrorMessage("textbox_date_error");
		$closedDate->setLength(255);
		$closedDate->setRowTitle("complaint_closed_date");
		$closedDate->setRequired(false);
		$closedDate->setTable("complaint");
		$closedDate->setHelpId(9115);
		$closedDate->setVisible(false);
		$customerComplaintStatusClosed->add($closedDate);


		$internalComplaintStatus = new radio("internalComplaintStatus");
		$internalComplaintStatus->setGroup("totalClosureGroup");
		$internalComplaintStatus->setDataType("string");
		$internalComplaintStatus->setLength(5);
		$internalComplaintStatus->setArraySource(array(
			array('value' => 'Open', 'display' => 'Open'),
			array('value' => 'Closed', 'display' => 'Closed')
		));
		$internalComplaintStatus->setRowTitle("internal_complaint_status");
		$internalComplaintStatus->setRequired(false);
		if(isset($savedFields["internal_complaint_status"]))
			$internalComplaintStatus->setValue($savedFields["internal_complaint_status"]);
		else $internalComplaintStatus->setValue("Open");
		$internalComplaintStatus->setTable("conclusion");
		$internalComplaintStatus->setLabel("Total Closure");
		$internalComplaintStatus->setHelpId(91158757);


		// Dependency
		$internalComplaintStatus_dependency = new dependency();
		$internalComplaintStatus_dependency->addRule(new rule('totalClosureGroup', 'internalComplaintStatus', 'Open'));
		$internalComplaintStatus_dependency->setGroup('internalComplaintStatusClosed');
		$internalComplaintStatus_dependency->setShow(true);

		$internalComplaintStatus->addControllingDependency($internalComplaintStatus_dependency);
		$totalClosureGroup->add($internalComplaintStatus);


		$totalClosureDate = new calendar("totalClosureDate");
		if(isset($savedFields["totalClosureDate"]))
			$totalClosureDate->setValue($savedFields["totalClosureDate"]);
		$totalClosureDate->setGroup("internalComplaintStatusClosed");
		$totalClosureDate->setDataType("date");
		$totalClosureDate->setErrorMessage("textbox_date_error");
		$totalClosureDate->setLength(255);
		$totalClosureDate->setRowTitle("total_closure_date");
		$totalClosureDate->setRequired(false);
		$totalClosureDate->setVisible(false);
		$totalClosureDate->setTable("complaint");
		$totalClosureDate->setHelpId(9116);
		$internalComplaintStatusClosed->add($totalClosureDate);

		$processOwner3 = new dropdown("processOwner3");
		if(isset($savedFields["processOwner3"]))
			$processOwner3->setValue($savedFields["processOwner3"]);
		$processOwner3->setGroup("submitGroup");
		$processOwner3->setDataType("string");
		$processOwner3->setRowTitle("chosen_complaint_owner");
		$processOwner3->setRequired(false);
		$processOwner3->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, NTLogon AS value FROM `employee` ORDER BY firstName ASC, lastName ASC");
		$processOwner3->setTable("conclusion");
		$processOwner3->clearData();
		$processOwner3->setHelpId(8145);
		$internalComplaintStatusClosed->add($processOwner3);




		$submit4 = new submit("submit4");
		$submit4->setGroup("submitGroup");
		$submit4->setVisible(true);
		$submitGroup->add($submit4);

		$this->form->add($idHeadersCustom);
		$this->form->add($sapReturnFormMulti);
		$this->form->add($initiation);
		$this->form->add($modComplaintOptionNo);
		$this->form->add($modComplaintOptionYes);
		$this->form->add($materialAmountGroup);
		$this->form->add($creditGroup);
		$this->form->add($creditGroupNo);
		$this->form->add($creditGroupYes);
//		$this->form->add($transferOwnershipNo);
//		$this->form->add($transferOwnershipYes);
		$this->form->add($creditAuthorisationStatusGroup);
		$this->form->add($commercialCreditStatusGroupAdvise);
		$this->form->add($adviseAcceptedYes);
		$this->form->add($commercialAdviseReason);
		$this->form->add($commercialCreditStatusGroup);
		$this->form->add($commercialAcceptedYes);
		$this->form->add($commercialAcceptedReason);
		$this->form->add($financeCreditStatusGroup);
		$this->form->add($creditRaisedGroup);
		$this->form->add($creditRaisedGroupYes);
		$this->form->add($creditRaisedGroupDef);
		$this->form->add($customerCreditGroup);
		$this->form->add($complaintClosure);
		$this->form->add($customerComplaintStatusClosed);
		$this->form->add($totalClosureGroup);
		$this->form->add($internalComplaintStatusClosed);
		$this->form->add($submitGroup);
		/*
		echo "<pre>";
		print_r($this->form);
		echo "</pre>";
		exit;
		*/

	}

	public function defineSupplierForm()
	{
		$savedFields = array();

		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("conclusion" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");

		$implementedGroup = new group("implementedGroup");
		$implementedGroup->setBorder(false);
		$implementedGroupYes = new group("implementedGroupYes");
		$implementedGroupYes->setBorder(false);
		$holdInvoiceGroup = new group("holdInvoiceGroup");
		$holdInvoiceGroup->setBorder(false);
		$sp_submitToFinanceGroup = new group("sp_submitToFinanceGroup");
		$sp_submitToFinanceGroup->setBorder(false);
		$sp_submitToFinanceYes = new group("sp_submitToFinanceYes");
		$sp_submitToFinanceYes->setBorder(false);
//		$putFullAccountOnHoldGroup = new group("putFullAccountOnHoldGroup");
//		$putFullAccountOnHoldGroup->setBorder(false);

		//$creditRaisedGroup = new group("creditRaisedGroup");
		$creditRaisedGroupYes = new group("creditRaisedGroupYes");
		$creditRaisedGroupYes->setBorder(false);
		$customerDerongationYes = new group("customerDerongationYes");
		$customerDerongationYes->setBorder(false);
		$creditRaisedGroupYes2 = new group("creditRaisedGroupYes2");
		$creditRaisedGroupYes2->setBorder(false);
		$sp_requestDisposalYes = new group("sp_requestDisposalYes");
		$sp_requestDisposalYes->setBorder(false);
		$creditRaisedGroupYes3 = new group("creditRaisedGroupYes3");

		$sp_requestAuthorisedYes = new group("sp_requestAuthorisedYes");


		$sp_materialDisposedGroup = new group("sp_materialDisposedGroup");
		$sp_materialDisposedGroup->setBorder(false);
		$sp_materialDisposedYes = new group("sp_materialDisposedYes");
		$sp_materialDisposedYes->setBorder(false);
		$sp_materialReturnedGroup = new group("sp_materialReturnedGroup");
		$sp_materialReturnedGroup->setBorder(false);
		$sp_materialReturnedYes = new group("sp_materialReturnedYes");
		$sp_materialReturnedYes->setBorder(false);

		$creditRaisedGroupYes4 = new group("creditRaisedGroupYes4");
		$creditRaisedGroupYes4->setBorder(false);
		$sp_supplierCreditNoteRecYes = new group("sp_supplierCreditNoteRecYes");
		$sp_supplierCreditNoteRecYes->setBorder(false);
		$creditRaisedGroupYes41 = new group("creditRaisedGroupYes41");
		$creditRaisedGroupYes41->setBorder(false);

		$creditRaisedGroupNo = new group("creditRaisedGroupNo");
		$creditRaisedGroupNo->setBorder(false);
		$creditRaisedGroupNo2 = new group("creditRaisedGroupNo2");

		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		$totalClosureGroup = new group("totalClosureGroup");
		$totalClosureGroup->setBorder(false);
		$internalComplaintStatusClosed = new group("internalComplaintStatusClosed");
		$internalComplaintStatusClosed->setBorder(false);

		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"])){//this means we are coming from the print function defined on homepage

//			$dataset2 = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion LEFT JOIN complaint ON conclusion.complaintId=complaint.id WHERE complaintId = '"  . $this->complaint->getId()."'");
//			$fields2 = mysql_fetch_array($dataset2);
		}

		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("conclusion");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);

		// Complete Finance Step for Supplier Complaint
		$supplierFinanceComplete = new textbox("supplierFinanceComplete");
		$supplierFinanceComplete->setTable("conclusion");
		$supplierFinanceComplete->setVisible(false);
		$supplierFinanceComplete->setGroup("initiation");
		$supplierFinanceComplete->setDataType("string");
		$initiation->add($supplierFinanceComplete);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		else $status->setValue("initiation");
		$status->setTable("conclusion");
		$status->setVisible(false);
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
			$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$currentComplaintCategory = new readonly("currentComplaintCategory");
		if(isset($savedFields["currentComplaintCategory"]))
			$currentComplaintCategory->setValue($savedFields["currentComplaintCategory"]);
		$currentComplaintCategory->setGroup("modComplaintOptionYes");
		$currentComplaintCategory->setDataType("string");
		$currentComplaintCategory->setLength(255);
		$currentComplaintCategory->setRowTitle("current_complaint_category");
		$currentComplaintCategory->setRequired(false);
		$currentComplaintCategory->setVisible(false);
		$currentComplaintCategory->setTable("complaint");
		$currentComplaintCategory->setHelpId(9189);
		$initiation->add($currentComplaintCategory);

//		$processOwner = new dropdown("processOwner");
//		if(isset($savedFields["processOwner"]))
//			$processOwner->setValue($savedFields["processOwner"]);
//		$processOwner->setGroup("submitGroup");
//		$processOwner->setDataType("string");
//		$processOwner->setRowTitle("chosen_complaint_owner");
//		$processOwner->setRequired(false);
//		$processOwner->setVisible(false);
//		$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
//		$processOwner->setTable("conclusion");
//		$processOwner->clearData();
//		$processOwner->setHelpId(8145);
//		$initiation->add($processOwner);

		/////////////////////

//		$sp_customerDerongation = new radio("sp_customerDerongation");
//		$sp_customerDerongation->setGroup("creditGroup");
//		$sp_customerDerongation->setDataType("string");
//		$sp_customerDerongation->setLength(5);
//		$sp_customerDerongation->setArraySource(array(
//			array('value' => 'YES', 'display' => 'Yes'),
//			array('value' => 'NO', 'display' => 'No')
//		));
//		if(isset($savedFields["sp_customerDerongation"]))
//			$sp_customerDerongation->setValue($savedFields["sp_customerDerongation"]);
//		$sp_customerDerongation->setRowTitle("request_for_credit_raised");
//		$sp_customerDerongation->setRequired(false);
//		$sp_customerDerongation->setValue("NO");
//		$sp_customerDerongation->setLabel("Supplier/Carrier/Interco Credit");
//		$sp_customerDerongation->setTable("conclusion");
//		$sp_customerDerongation->setHelpId(9109);
//
//
//		// Dependency
//		$customerDerongation_dependency = new dependency();
//		$customerDerongation_dependency->addRule(new rule('creditRaisedGroup', 'sp_customerDerongation', 'YES'));
//		$customerDerongation_dependency->setGroup('creditRaisedGroupYes');
//		$customerDerongation_dependency->setShow(true);
//
//		$sp_customerDerongation->addControllingDependency($customerDerongation_dependency);
//		$creditRaisedGroup->add($sp_customerDerongation);

//		$creditNumber = new textbox("creditNumber");
//		if(isset($savedFields["creditNumber"]))
//			$creditNumber->setValue($savedFields["creditNumber"]);
//		$creditNumber->setGroup("creditRaisedGroupYes");
//		$creditNumber->setDataType("string");
//		$creditNumber->setLength(255);
//		$creditNumber->setRowTitle("credit_number");
//		$creditNumber->setRequired(false);
//		$creditNumber->setTable("conclusion");
//		$creditNumber->setHelpId(9110);
//		$creditRaisedGroupYes->add($creditNumber);

//		$implementedPermanentCorrectiveActionValidatedReadOnly = new readonly("implementedPermanentCorrectiveActionValidatedReadOnly");
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setGroup("implementedGroup");
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setDataType("text");
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setRowTitle("implemented_permanent_corrective_action");
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setRequired(false);
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setLabel("Implementation");
//		$implementedPermanentCorrectiveActionValidatedReadOnly->setHelpId(90243453451);
//		$implementedGroup->add($implementedPermanentCorrectiveActionValidatedReadOnly);
//
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly = new readonly("implementedPermanentCorrectiveActionValidatedAuthorReadOnly");
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setGroup("implementedGroup");
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setDataType("string");
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setLength(255);
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setRequired(false);
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setRowTitle("implemented_permanent_corrective_action_author");;
//		$implementedPermanentCorrectiveActionValidatedAuthorReadOnly->setHelpId(90243453452);
//		$implementedGroup->add($implementedPermanentCorrectiveActionValidatedAuthorReadOnly);
//
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly = new readonly("implementedPermanentCorrectiveActionValidatedDateReadOnly");
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly->setGroup("implementedGroup");
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly->setDataType("string");
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly->setRequired(false);
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly->setRowTitle("implemented_permanent_corrective_action_date");
//		$implementedPermanentCorrectiveActionValidatedDateReadOnly->setHelpId(90243453453);
//		$implementedGroup->add($implementedPermanentCorrectiveActionValidatedDateReadOnly);

		$implementedPermanentCorrectiveActionValidatedYesNo = new radio("implementedPermanentCorrectiveActionValidatedYesNo");
		$implementedPermanentCorrectiveActionValidatedYesNo->setGroup("implementedGroup");
		$implementedPermanentCorrectiveActionValidatedYesNo->setDataType("string");
		$implementedPermanentCorrectiveActionValidatedYesNo->setLength(5);
		$implementedPermanentCorrectiveActionValidatedYesNo->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$implementedPermanentCorrectiveActionValidatedYesNo->setRowTitle("have_we_validated_the_action_from_supplier_is_sufficient");
		$implementedPermanentCorrectiveActionValidatedYesNo->setValue("No");
		$implementedPermanentCorrectiveActionValidatedYesNo->setHelpId(80454);
		$implementedPermanentCorrectiveActionValidatedYesNo->setRequired(true);
		$implementedPermanentCorrectiveActionValidatedYesNo->setTable("conclusion");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedYesNo"]))
			$implementedPermanentCorrectiveActionValidatedYesNo->setValue($savedFields["implementedPermanentCorrectiveActionValidatedYesNo"]);
		else $implementedPermanentCorrectiveActionValidatedYesNo->setValue("No");

		// Dependency
		$implementedPermanentCorrectiveActionValidatedYesNoDependency = new dependency();
		$implementedPermanentCorrectiveActionValidatedYesNoDependency->addRule(new rule('implementedGroup', 'implementedPermanentCorrectiveActionValidatedYesNo', 'Yes'));
		$implementedPermanentCorrectiveActionValidatedYesNoDependency->setGroup('implementedGroupYes');
		$implementedPermanentCorrectiveActionValidatedYesNoDependency->setShow(true);

		$implementedPermanentCorrectiveActionValidatedYesNo->addControllingDependency($implementedPermanentCorrectiveActionValidatedYesNoDependency);
		$implementedGroup->add($implementedPermanentCorrectiveActionValidatedYesNo);

		$implementedSupplierDescription = new textarea("implementedSupplierDescription");
		if(isset($savedFields["implementedSupplierDescription"]))
			$implementedSupplierDescription->setValue($savedFields["implementedSupplierDescription"]);
		$implementedSupplierDescription->setGroup("implementedGroupYes");
		$implementedSupplierDescription->setDataType("text");
		$implementedSupplierDescription->setLength(100);
		$implementedSupplierDescription->setRowTitle("description");
		$implementedSupplierDescription->setHelpId(804554);
		$implementedSupplierDescription->setRequired(false);
		$implementedSupplierDescription->setTable("conclusion");
		$implementedGroupYes->add($implementedSupplierDescription);

		$implementedSupplierWho = new textbox("implementedSupplierWho");
		if(isset($savedFields["implementedSupplierWho"]))
			$implementedSupplierWho->setValue($savedFields["implementedSupplierWho"]);
		$implementedSupplierWho->setGroup("implementedGroupYes");
		$implementedSupplierWho->setDataType("string");
		$implementedSupplierWho->setLength(100);
		$implementedSupplierWho->setRowTitle("author");
		$implementedSupplierWho->setHelpId(804552);
		$implementedSupplierWho->setRequired(false);
		$implementedSupplierWho->setTable("conclusion");
		$implementedGroupYes->add($implementedSupplierWho);

		$implementedSupplierWhen = new calendar("implementedSupplierWhen");
		if(isset($savedFields["implementedSupplierWhen"]))
			$implementedSupplierWhen->setValue($savedFields["implementedSupplierWhen"]);
		$implementedSupplierWhen->setGroup("implementedGroupYes");
		$implementedSupplierWhen->setDataType("date");
		$implementedSupplierWhen->setLength(100);
		$implementedSupplierWhen->setRowTitle("date");
		$implementedSupplierWhen->setHelpId(804553);
		$implementedSupplierWhen->setRequired(false);
		$implementedSupplierWhen->setTable("conclusion");
		$implementedGroupYes->add($implementedSupplierWhen);

		$holdInvoice = new radio("holdInvoice");
		$holdInvoice->setGroup("holdInvoiceGroup");
		$holdInvoice->setDataType("string");
		$holdInvoice->setLength(5);
		$holdInvoice->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$holdInvoice->setRowTitle("hold_invoice");
		$holdInvoice->setValue("No");
		$holdInvoice->setHelpId(80454);
		$holdInvoice->setRequired(true);
		$holdInvoice->setTable("conclusion");
		if(isset($savedFields["holdInvoice"]))
			$holdInvoice->setValue($savedFields["holdInvoice"]);
		else $holdInvoice->setValue("No");
		$holdInvoiceGroup->add($holdInvoice);

		$sp_submitToFinance2 = new radio("sp_submitToFinance2");
		$sp_submitToFinance2->setGroup("materialDebitedGroup");
		$sp_submitToFinance2->setDataType("string");
		$sp_submitToFinance2->setLength(5);
		$sp_submitToFinance2->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$sp_submitToFinance2->setRowTitle("issue_debit_note");
		$sp_submitToFinance2->setValue("No");
		$sp_submitToFinance2->setHelpId(80454);
		$sp_submitToFinance2->setRequired(true);
		$sp_submitToFinance2->setTable("conclusion");
		if(isset($savedFields["sp_submitToFinance"]))
			$sp_submitToFinance2->setValue($savedFields["sp_submitToFinance"]);
		else $sp_submitToFinance2->setValue("No");

		// Dependency
		$submitToFinanceDependency = new dependency();
		$submitToFinanceDependency->addRule(new rule('sp_submitToFinanceGroup', 'sp_submitToFinance2', 'Yes'));
		$submitToFinanceDependency->setGroup('sp_submitToFinanceYes');
		$submitToFinanceDependency->setShow(true);

		$sp_submitToFinance2->addControllingDependency($submitToFinanceDependency);
		$sp_submitToFinanceGroup->add($sp_submitToFinance2);

		$sp_debitNumber2 = new textbox("sp_debitNumber2");
		if(isset($savedFields["sp_debitNumber"]))
			$sp_debitNumber2->setValue($savedFields["sp_debitNumber"]);
		$sp_debitNumber2->setGroup("sp_submitToFinanceYes");
		$sp_debitNumber2->setDataType("string");
		$sp_debitNumber2->setLength(100);
		$sp_debitNumber2->setRowTitle("debit_number");
		$sp_debitNumber2->setHelpId(80455);
		$sp_debitNumber2->setRequired(false);
		$sp_debitNumber2->setTable("conclusion");
		$sp_submitToFinanceYes->add($sp_debitNumber2);

		$sp_debitValue2 = new measurement("sp_debitValue2");
		if(isset($savedFields["sp_debitValue2_quantity"]) && isset($savedFields["sp_debitValue2_measurement"])){
			$arr[0] = $savedFields["sp_debitValue2_quantity"];
			$arr[1] = $savedFields["sp_debitValue2_measurement"];
			$sp_debitValue2->setValue($arr);
		}else $sp_debitValue2->setMeasurement("EUR");
		$sp_debitValue2->setGroup("sp_submitToFinanceYes");
		$sp_debitValue2->setDataType("string");
		$sp_debitValue2->setLength(50);
		$sp_debitValue2->setHelpId(80456);
		$sp_debitValue2->setRowTitle("debit_value");
		$sp_debitValue2->setRequired(true);
		$sp_debitValue2->setTable("conclusion");
		$sp_debitValue2->setXMLSource("./apps/complaints/xml/currency.xml");
		$sp_submitToFinanceYes->add($sp_debitValue2);

		$sp_debitDate2 = new calendar("sp_debitDate2");
		if(isset($savedFields["sp_debitDate2"]))
			$sp_debitDate2->setValue($savedFields["sp_debitDate2"]);
		$sp_debitDate2->setGroup("sp_submitToFinanceYes");
		$sp_debitDate2->setDataType("date");
		$sp_debitDate2->setErrorMessage("textbox_date_error");
		$sp_debitDate2->setLength(30);
		$sp_debitDate2->setHelpId(80457);
		$sp_debitDate2->setRowTitle("debit_date");
		$sp_debitDate2->setRequired(false);
		$sp_debitDate2->setTable("conclusion");
		$sp_submitToFinanceYes->add($sp_debitDate2);

		$sp_debitName2 = new textbox("sp_debitName2");
		if(isset($savedFields["sp_debitName2"]))
			$sp_debitName2->setValue($savedFields["sp_debitName2"]);
		$sp_debitName2->setGroup("sp_submitToFinanceYes");
		$sp_debitName2->setDataType("string");
		$sp_debitName2->setLength(250);
		$sp_debitName2->setHelpId(80458);
		$sp_debitName2->setRowTitle("debit_name");
		$sp_debitName2->setRequired(false);
		$sp_debitName2->setTable("conclusion");
		$sp_submitToFinanceYes->add($sp_debitName2);

//		$putFullAccountOnHold = new radio("putFullAccountOnHold");
//		$putFullAccountOnHold->setGroup("holdInvoiceGroup");
//		$putFullAccountOnHold->setDataType("string");
//		$putFullAccountOnHold->setLength(5);
//		$putFullAccountOnHold->setArraySource(array(
//			array('value' => 'Yes', 'display' => 'Yes'),
//			array('value' => 'No', 'display' => 'No')
//		));
//		$putFullAccountOnHold->setRowTitle("put_full_account_on_hold");
//		$putFullAccountOnHold->setValue("No");
//		$putFullAccountOnHold->setHelpId(80454);
//		$putFullAccountOnHold->setRequired(true);
//		$putFullAccountOnHold->setTable("conclusion");
//		if(isset($savedFields["putFullAccountOnHold"]))
//			$putFullAccountOnHold->setValue($savedFields["putFullAccountOnHold"]);
//		else $putFullAccountOnHold->setValue("No");
//		$putFullAccountOnHoldGroup->add($putFullAccountOnHold);

		$customerDerongation = new radio("customerDerongation");
		$customerDerongation->setGroup("creditRaisedGroupYes");
		$customerDerongation->setDataType("string");
		$customerDerongation->setLength(5);
		$customerDerongation->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$customerDerongation->setRowTitle("customer_derongation");
		$customerDerongation->setRequired(false);
		if(isset($savedFields["customerDerongation"]))
			$customerDerongation->setValue($savedFields["customerDerongation"]);
		else $customerDerongation->setValue("No");
		$customerDerongation->setTable("conclusion");
		$customerDerongation->setHelpId(9154);

		// Dependency
		$customerDerongation_dependency = new dependency();
		$customerDerongation_dependency->addRule(new rule('creditRaisedGroupYes', 'customerDerongation', 'Yes'));
		$customerDerongation_dependency->setGroup('customerDerongationYes');
		$customerDerongation_dependency->setShow(true);

		$customerDerongation->addControllingDependency($customerDerongation_dependency);
		$creditRaisedGroupYes->add($customerDerongation);

		$sp_customerName = new textbox("sp_customerName");
		if(isset($savedFields["sp_customerName"]))
			$sp_customerName->setValue($savedFields["sp_customerName"]);
		$sp_customerName->setGroup("customerDerongationYes");
		$sp_customerName->setDataType("string");
		$sp_customerName->setLength(255);
		$sp_customerName->setRowTitle("customer_name");
		$sp_customerName->setRequired(false);
		$sp_customerName->setTable("conclusion");
		$sp_customerName->setHelpId(9110);
		$customerDerongationYes->add($sp_customerName);

//		$attachment = new attachment("attachment");
//		//if(isset($savedFields["attachment"]))
//			//$attachment->setValue($savedFields["attachment"]);
//		$attachment->setTempFileLocation("/apps/complaints/tmp");
//		$attachment->setFinalFileLocation("/apps/complaints/attachments/conc");
//		$attachment->setRowTitle("attach_document");
//		$attachment->setHelpId(11);
//		$attachment->setNextAction("conclusion");
//		$creditRaisedGroupYes2->add($attachment);

		$sp_requestDisposal = new radio("sp_requestDisposal");
		$sp_requestDisposal->setGroup("creditRaisedGroupYes2");
		$sp_requestDisposal->setDataType("string");
		$sp_requestDisposal->setLength(5);
		$sp_requestDisposal->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$sp_requestDisposal->setRowTitle("request_disposal");
		$sp_requestDisposal->setRequired(false);
		if(isset($savedFields["sp_requestDisposal"]))
			$sp_requestDisposal->setValue($savedFields["sp_requestDisposal"]);
		else $sp_requestDisposal->setValue("No");
		$sp_requestDisposal->setTable("conclusion");
		$sp_requestDisposal->setHelpId(9154);

		// Dependency
		$sp_requestDisposal_dependency = new dependency();
		$sp_requestDisposal_dependency->addRule(new rule('creditRaisedGroupYes2', 'sp_requestDisposal', 'Yes'));
		$sp_requestDisposal_dependency->setGroup('sp_requestDisposalYes');
		$sp_requestDisposal_dependency->setShow(true);

		$sp_requestDisposal->addControllingDependency($sp_requestDisposal_dependency);
		$creditRaisedGroupYes2->add($sp_requestDisposal);

		$sp_requestDisposalReadOnly = new readonly("sp_requestDisposalReadOnly");
		$sp_requestDisposalReadOnly->setGroup("creditRaisedGroupYes2");
		$sp_requestDisposalReadOnly->setDataType("string");
		$sp_requestDisposalReadOnly->setLength(5);
		$sp_requestDisposalReadOnly->setRowTitle("request_disposal");
		$sp_requestDisposalReadOnly->setVisible(false);
		$sp_requestDisposalReadOnly->setLabel("Request For Disposal");
		$sp_requestDisposalReadOnly->setHelpId(9189);
		$creditRaisedGroupYes2->add($sp_requestDisposalReadOnly);

		$sp_sapItemNumber = new textbox("sp_sapItemNumber");
		if(isset($savedFields["sp_sapItemNumber"]))
			$sp_sapItemNumber->setValue($savedFields["sp_sapItemNumber"]);
		$sp_sapItemNumber->setGroup("sp_requestDisposalYes");
		$sp_sapItemNumber->setDataType("string");
		$sp_sapItemNumber->setLength(255);
		$sp_sapItemNumber->setRowTitle("sap_item_number");
		$sp_sapItemNumber->setRequired(false);
		$sp_sapItemNumber->setTable("conclusion");
		$sp_sapItemNumber->setHelpId(9110);
		$sp_requestDisposalYes->add($sp_sapItemNumber);

		$sp_sapItemNumberReadOnly = new readonly("sp_sapItemNumberReadOnly");
		$sp_sapItemNumberReadOnly->setGroup("sp_requestDisposalYes");
		$sp_sapItemNumberReadOnly->setDataType("string");
		$sp_sapItemNumberReadOnly->setLength(5);
		$sp_sapItemNumberReadOnly->setRowTitle("sap_item_number");
		$sp_sapItemNumberReadOnly->setVisible(false);
		$sp_sapItemNumberReadOnly->setHelpId(9189);
		$sp_requestDisposalYes->add($sp_sapItemNumberReadOnly);

		$sp_amount = new measurement("sp_amount");
		if(isset($savedFields["sp_amount_quantity"]) && isset($savedFields["sp_amount_measurement"])){
			$arr[0] = $savedFields["sp_amount_quantity"];
			$arr[1] = $savedFields["sp_amount_measurement"];
			$sp_amount->setValue($arr);
		}else $sp_amount->setMeasurement("m");
		$sp_amount->setGroup("sp_requestDisposalYes");
		$sp_amount->setDataType("string");
		$sp_amount->setLength(5);
		$sp_amount->setXMLSource("./apps/complaints/xml/uom.xml");
		$sp_amount->setTable("conclusion");
		$sp_amount->setRowTitle("amount");
		$sp_amount->setRequired(false);
		$sp_requestDisposalYes->add($sp_amount);

		$sp_amountReadOnly = new readonly("sp_amountReadOnly");
		$sp_amountReadOnly->setGroup("sp_requestDisposalYes");
		$sp_amountReadOnly->setDataType("string");
		$sp_amountReadOnly->setLength(5);
		$sp_amountReadOnly->setRowTitle("amount");
		$sp_amountReadOnly->setVisible(false);
		$sp_amountReadOnly->setHelpId(9189);
		$sp_requestDisposalYes->add($sp_amountReadOnly);

		$sp_value = new measurement("sp_value");
		if(isset($savedFields["sp_value_quantity"]) && isset($savedFields["sp_value_measurement"])){
			$arr[0] = $savedFields["sp_value_quantity"];
			$arr[1] = $savedFields["sp_value_measurement"];
			$sp_value->setValue($arr);
		}else $sp_value->setMeasurement("m");
		$sp_value->setGroup("sp_requestDisposalYes");
		$sp_value->setDataType("string");
		$sp_value->setLength(5);
		$sp_value->setXMLSource("./apps/complaints/xml/currency.xml");
		$sp_value->setRowTitle("value");
		$sp_value->setTable("conclusion");
		$sp_value->setRequired(false);
		$sp_requestDisposalYes->add($sp_value);

		$sp_valueReadOnly = new readonly("sp_valueReadOnly");
		$sp_valueReadOnly->setGroup("sp_requestDisposalYes");
		$sp_valueReadOnly->setDataType("string");
		$sp_valueReadOnly->setLength(5);
		$sp_valueReadOnly->setRowTitle("value");
		$sp_valueReadOnly->setVisible(false);
		$sp_valueReadOnly->setHelpId(9189);
		$sp_requestDisposalYes->add($sp_valueReadOnly);

		$sp_requestEmailText = new textarea("sp_requestEmailText");
		if(isset($savedFields["sp_requestEmailText"]))
			$sp_requestEmailText->setValue($savedFields["sp_requestEmailText"]);
		$sp_requestEmailText->setGroup("sp_requestDisposalYes");
		$sp_requestEmailText->setDataType("text");
		$sp_requestEmailText->setLength(255);
		$sp_requestEmailText->setRowTitle("email_text_comment");
		$sp_requestEmailText->setRequired(false);
		$sp_requestEmailText->setTable("conclusion");
		$sp_requestEmailText->setHelpId(9110);
		$sp_requestDisposalYes->add($sp_requestEmailText);

		$sp_requestEmailTextReadOnly = new readonly("sp_requestEmailTextReadOnly");
		$sp_requestEmailTextReadOnly->setGroup("sp_requestDisposalYes");
		$sp_requestEmailTextReadOnly->setDataType("string");
		$sp_requestEmailTextReadOnly->setLength(5);
		$sp_requestEmailTextReadOnly->setRowTitle("email_text_comment");
		$sp_requestEmailTextReadOnly->setVisible(false);
		$sp_requestEmailTextReadOnly->setHelpId(9189);
		$sp_requestDisposalYes->add($sp_requestEmailTextReadOnly);



		$processOwner3Request = new autocomplete("processOwner3Request");
		if(isset($savedFields["processOwner3Request"]))
			$processOwner3Request->setValue($savedFields["processOwner3Request"]);
		$processOwner3Request->setGroup("sp_requestDisposalYes");
		$processOwner3Request->setDataType("string");
		$processOwner3Request->setRowTitle("chosen_complaint_owner");
		$processOwner3Request->setRequired(true);
		$processOwner3Request->setUrl("/apps/complaints/ajax/processOwner3Request?");
		$processOwner3Request->setTable("conclusion");
		$processOwner3Request->setHelpId(8145);
		$sp_requestDisposalYes->add($processOwner3Request);

		$processOwner3RequestReadOnly = new readonly("processOwner3RequestReadOnly");
		$processOwner3RequestReadOnly->setGroup("sp_requestDisposalYes");
		$processOwner3RequestReadOnly->setDataType("string");
		$processOwner3RequestReadOnly->setLength(5);
		$processOwner3RequestReadOnly->setRowTitle("chosen_complaint_owner");
		$processOwner3RequestReadOnly->setVisible(false);
		$processOwner3RequestReadOnly->setHelpId(9189);
		$sp_requestDisposalYes->add($processOwner3RequestReadOnly);

		$submitRequest = new submit("submitRequest");
		$submitRequest->setGroup("sp_requestDisposalYes");
		$submitRequest->setVisible(true);
		$sp_requestDisposalYes->add($submitRequest);

//		$sp_currency = new textbox("sp_currency");
//		if(isset($savedFields["sp_currency"]))
//			$sp_currency->setValue($savedFields["sp_currency"]);
//		$sp_currency->setGroup("creditRaisedGroupYes3");
//		$sp_currency->setDataType("string");
//		$sp_currency->setLength(255);
//		$sp_currency->setRowTitle("currency");
//		$sp_currency->setRequired(false);
//		$sp_currency->setTable("conclusion");
//		$sp_currency->setHelpId(9110);
//		$creditRaisedGroupYes3->add($sp_currency);

		$sp_requestAuthorised = new radio("sp_requestAuthorised");
		$sp_requestAuthorised->setGroup("creditRaisedGroupYes3");
		$sp_requestAuthorised->setDataType("string");
		$sp_requestAuthorised->setLength(5);
		$sp_requestAuthorised->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$sp_requestAuthorised->setRowTitle("request_authorised");
		$sp_requestAuthorised->setRequired(false);
		if(isset($savedFields["sp_requestAuthorised"]))
			$sp_requestAuthorised->setValue($savedFields["sp_requestAuthorised"]);
		else $sp_requestAuthorised->setValue("No");
		$sp_requestAuthorised->setTable("conclusion");
		$sp_requestAuthorised->setHelpId(9154);

		// Dependency
		$sp_requestAuthorised_dependency = new dependency();
		$sp_requestAuthorised_dependency->addRule(new rule('creditRaisedGroupYes3', 'sp_requestAuthorised', 'Yes'));
		$sp_requestAuthorised_dependency->setGroup('sp_requestAuthorisedYes');
		$sp_requestAuthorised_dependency->setShow(true);

		$sp_requestAuthorised->addControllingDependency($sp_requestAuthorised_dependency);
		$creditRaisedGroupYes3->add($sp_requestAuthorised);

		$sp_requestAuthorisedReadOnly = new readonly("sp_requestAuthorisedReadOnly");
		$sp_requestAuthorisedReadOnly->setGroup("creditRaisedGroupYes3");
		$sp_requestAuthorisedReadOnly->setDataType("string");
		$sp_requestAuthorisedReadOnly->setLength(5);
		$sp_requestAuthorisedReadOnly->setRowTitle("request_authorised");
		$sp_requestAuthorisedReadOnly->setLabel("Disposal Authorised");
		$sp_requestAuthorisedReadOnly->setVisible(false);
		$sp_requestAuthorisedReadOnly->setHelpId(9189);
		$creditRaisedGroupYes3->add($sp_requestAuthorisedReadOnly);




		$sp_requestAuthorisedDate = new calendar("sp_requestAuthorisedDate");
		if(isset($savedFields["sp_requestAuthorisedDate"]))
			$sp_requestAuthorisedDate->setValue($savedFields["sp_requestAuthorisedDate"]);
		$sp_requestAuthorisedDate->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedDate->setDataType("date");
		$sp_requestAuthorisedDate->setErrorMessage("textbox_date_error");
		$sp_requestAuthorisedDate->setLength(255);
		$sp_requestAuthorisedDate->setRowTitle("request_authorised_date");
		$sp_requestAuthorisedDate->setVisible(false);
		$sp_requestAuthorisedDate->setIgnore(false);
		$sp_requestAuthorisedDate->setTable("conclusion");
		$sp_requestAuthorisedDate->setHelpId(9110);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedDate);

		$sp_requestAuthorisedDateReadOnly = new readonly("sp_requestAuthorisedDateReadOnly");
		$sp_requestAuthorisedDateReadOnly->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedDateReadOnly->setDataType("string");
		$sp_requestAuthorisedDateReadOnly->setLength(5);
		$sp_requestAuthorisedDateReadOnly->setRowTitle("request_authorised_date");
		$sp_requestAuthorisedDateReadOnly->setVisible(false);
		$sp_requestAuthorisedDateReadOnly->setHelpId(9189);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedDateReadOnly);

		$sp_requestAuthorisorName = new textbox("sp_requestAuthorisorName");
		if(isset($savedFields["sp_requestAuthorisorName"]))
			$sp_requestAuthorisorName->setValue($savedFields["sp_requestAuthorisorName"]);
		$sp_requestAuthorisorName->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisorName->setDataType("string");
		$sp_requestAuthorisorName->setLength(255);
		$sp_requestAuthorisorName->setRowTitle("authorisor_name");
		$sp_requestAuthorisorName->setRequired(false);
		$sp_requestAuthorisorName->setVisible(false);
		$sp_requestAuthorisorName->setIgnore(false);
		$sp_requestAuthorisorName->setTable("conclusion");
		$sp_requestAuthorisorName->setValue(currentuser::getInstance()->getNTLogon());
		$sp_requestAuthorisorName->setHelpId(9110);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisorName);

		$sp_requestAuthorisorNameReadOnly = new readonly("sp_requestAuthorisorNameReadOnly");
		$sp_requestAuthorisorNameReadOnly->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisorNameReadOnly->setDataType("string");
		$sp_requestAuthorisorNameReadOnly->setLength(5);
		$sp_requestAuthorisorNameReadOnly->setRowTitle("authorisor_name");
		$sp_requestAuthorisorNameReadOnly->setVisible(false);
		$sp_requestAuthorisorNameReadOnly->setHelpId(9189);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisorNameReadOnly);

		$sp_requestAuthorisedName = new autocomplete("sp_requestAuthorisedName");
		if(isset($savedFields["sp_requestAuthorisedName"]))
			$sp_requestAuthorisedName->setValue($savedFields["sp_requestAuthorisedName"]);
		$sp_requestAuthorisedName->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedName->setDataType("string");
		$sp_requestAuthorisedName->setLength(255);
		$sp_requestAuthorisedName->setRowTitle("request_authorised_name_send_to");
		$sp_requestAuthorisedName->setUrl("/apps/complaints/ajax/requestAuthorisedName?");
		$sp_requestAuthorisedName->setRequired(false);
		$sp_requestAuthorisedName->setTable("conclusion");
		$sp_requestAuthorisedName->setHelpId(9110);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedName);

		$sp_requestAuthorisedNameReadOnly = new readonly("sp_requestAuthorisedNameReadOnly");
		$sp_requestAuthorisedNameReadOnly->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedNameReadOnly->setDataType("string");
		$sp_requestAuthorisedNameReadOnly->setLength(5);
		$sp_requestAuthorisedNameReadOnly->setRowTitle("request_authorised_name_send_to");
		$sp_requestAuthorisedNameReadOnly->setVisible(false);
		$sp_requestAuthorisedNameReadOnly->setHelpId(9189);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedNameReadOnly);

		$sp_requestAuthorisedEmailText = new textarea("sp_requestAuthorisedEmailText");
		if(isset($savedFields["sp_requestAuthorisedEmailText"]))
			$sp_requestAuthorisedEmailText->setValue($savedFields["sp_requestAuthorisedEmailText"]);
		$sp_requestAuthorisedEmailText->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedEmailText->setDataType("string");
		$sp_requestAuthorisedEmailText->setLength(255);
		$sp_requestAuthorisedEmailText->setRowTitle("email_text");
		$sp_requestAuthorisedEmailText->setRequired(false);
		$sp_requestAuthorisedEmailText->setTable("conclusion");
		$sp_requestAuthorisedEmailText->setHelpId(9110);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedEmailText);

		$sp_requestAuthorisedEmailTextReadOnly = new readonly("sp_requestAuthorisedEmailTextReadOnly");
		$sp_requestAuthorisedEmailTextReadOnly->setGroup("sp_requestAuthorisedYes");
		$sp_requestAuthorisedEmailTextReadOnly->setDataType("string");
		$sp_requestAuthorisedEmailTextReadOnly->setLength(5);
		$sp_requestAuthorisedEmailTextReadOnly->setRowTitle("email_text");
		$sp_requestAuthorisedEmailTextReadOnly->setVisible(false);
		$sp_requestAuthorisedEmailTextReadOnly->setHelpId(9189);
		$sp_requestAuthorisedYes->add($sp_requestAuthorisedEmailTextReadOnly);

		$submitAuthorised = new submit("submitAuthorised");
		$submitAuthorised->setGroup("sp_requestAuthorisedYes");
		$submitAuthorised->setVisible(true);
		$sp_requestAuthorisedYes->add($submitAuthorised);

		$sp_materialDisposed = new radio("sp_materialDisposed");
		if(isset($savedFields["sp_materialDisposed"]))
			$sp_materialDisposed->setValue($savedFields["sp_materialDisposed"]);
		$sp_materialDisposed->setGroup("creditRaisedGroupYes4");
		$sp_materialDisposed->setDataType("string");
		$sp_materialDisposed->setLength(5);
		$sp_materialDisposed->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_materialDisposed->setRowTitle("material_disposed");
		$sp_materialDisposed->setRequired(false);
		$sp_materialDisposed->setValue("NO");
		$sp_materialDisposed->setTable("conclusion");
		$sp_materialDisposed->setHelpId(9110);

		// Dependency
		$sp_materialDisposed_dependency = new dependency();
		$sp_materialDisposed_dependency->addRule(new rule('sp_materialDisposedGroup', 'sp_materialDisposed', 'YES'));
		$sp_materialDisposed_dependency->setGroup('sp_materialDisposedYes');
		$sp_materialDisposed_dependency->setShow(true);

		$sp_materialDisposed->addControllingDependency($sp_materialDisposed_dependency);
		$sp_materialDisposedGroup->add($sp_materialDisposed);

		$sp_materialDisposedName = new textbox("sp_materialDisposedName");
		if(isset($savedFields["sp_materialDisposedName"]))
			$sp_materialDisposedName->setValue($savedFields["sp_materialDisposedName"]);
		$sp_materialDisposedName->setGroup("sp_materialDisposedYes");
		$sp_materialDisposedName->setDataType("string");
		$sp_materialDisposedName->setLength(255);
		$sp_materialDisposedName->setRowTitle("material_disposed_name");
		$sp_materialDisposedName->setRequired(false);
		$sp_materialDisposedName->setTable("conclusion");
		$sp_materialDisposedName->setHelpId(9110);
		$sp_materialDisposedYes->add($sp_materialDisposedName);

		$sp_materialDisposedDate = new calendar("sp_materialDisposedDate");
		if(isset($savedFields["sp_materialDisposedDate"]))
			$sp_materialDisposedDate->setValue($savedFields["sp_materialDisposedDate"]);
		$sp_materialDisposedDate->setGroup("sp_materialDisposedYes");
		$sp_materialDisposedDate->setDataType("date");
		$sp_materialDisposedDate->setErrorMessage("textbox_date_error");
		$sp_materialDisposedDate->setLength(255);
		$sp_materialDisposedDate->setRowTitle("material_disposed_date");
		$sp_materialDisposedDate->setRequired(false);
		$sp_materialDisposedDate->setTable("conclusion");
		$sp_materialDisposedDate->setHelpId(9110);
		$sp_materialDisposedYes->add($sp_materialDisposedDate);

		$sp_materialDisposedCode = new textbox("sp_materialDisposedCode");
		if(isset($savedFields["sp_materialDisposedCode"]))
			$sp_materialDisposedCode->setValue($savedFields["sp_materialDisposedCode"]);
		$sp_materialDisposedCode->setGroup("sp_materialDisposedYes");
		$sp_materialDisposedCode->setDataType("string");
		$sp_materialDisposedCode->setLength(255);
		$sp_materialDisposedCode->setRowTitle("material_disposed_code");
		$sp_materialDisposedCode->setRequired(false);
		$sp_materialDisposedCode->setTable("conclusion");
		$sp_materialDisposedCode->setHelpId(9110);
		$sp_materialDisposedYes->add($sp_materialDisposedCode);

		$sp_materialReturned = new radio("sp_materialReturned");
		if(isset($savedFields["sp_materialReturned"]))
			$sp_materialReturned->setValue($savedFields["sp_materialReturned"]);
		$sp_materialReturned->setGroup("sp_materialReturnedGroup");
		$sp_materialReturned->setDataType("string");
		$sp_materialReturned->setLength(5);
		$sp_materialReturned->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_materialReturned->setRowTitle("material_returned");
		$sp_materialReturned->setRequired(false);
		$sp_materialReturned->setValue("NO");
		$sp_materialReturned->setTable("conclusion");
		$sp_materialReturned->setHelpId(9110);

		// Dependency
		$sp_materialReturned_dependency = new dependency();
		$sp_materialReturned_dependency->addRule(new rule('sp_materialReturnedGroup', 'sp_materialReturned', 'YES'));
		$sp_materialReturned_dependency->setGroup('sp_materialReturnedYes');
		$sp_materialReturned_dependency->setShow(true);

		$sp_materialReturned->addControllingDependency($sp_materialReturned_dependency);
		$sp_materialReturnedGroup->add($sp_materialReturned);

		$sp_materialReturnedName = new textbox("sp_materialReturnedName");
		if(isset($savedFields["sp_materialReturnedName"]))
			$sp_materialReturnedName->setValue($savedFields["sp_materialReturnedName"]);
		$sp_materialReturnedName->setGroup("sp_materialReturnedYes");
		$sp_materialReturnedName->setDataType("string");
		$sp_materialReturnedName->setLength(255);
		$sp_materialReturnedName->setRowTitle("material_returned_name");
		$sp_materialReturnedName->setRequired(false);
		$sp_materialReturnedName->setTable("conclusion");
		$sp_materialReturnedName->setHelpId(9110);
		$sp_materialReturnedYes->add($sp_materialReturnedName);

		$sp_materialReturnedDate = new calendar("sp_materialReturnedDate");
		if(isset($savedFields["sp_materialReturnedDate"]))
			$sp_materialReturnedDate->setValue($savedFields["sp_materialReturnedDate"]);
		$sp_materialReturnedDate->setGroup("sp_materialReturnedYes");
		$sp_materialReturnedDate->setDataType("date");
		$sp_materialReturnedDate->setErrorMessage("textbox_date_error");
		$sp_materialReturnedDate->setLength(255);
		$sp_materialReturnedDate->setRowTitle("material_returned_date");
		$sp_materialReturnedDate->setRequired(false);
		$sp_materialReturnedDate->setTable("conclusion");
		$sp_materialReturnedDate->setHelpId(9110);
		$sp_materialReturnedYes->add($sp_materialReturnedDate);

		$sp_sapReturnNumber = new textbox("sp_sapReturnNumber");
		if(isset($savedFields["sp_sapReturnNumber"]))
			$sp_sapReturnNumber->setValue($savedFields["sp_sapReturnNumber"]);
		$sp_sapReturnNumber->setGroup("sp_materialReturnedYes");
		$sp_sapReturnNumber->setDataType("string");
		$sp_sapReturnNumber->setLength(255);
		$sp_sapReturnNumber->setRowTitle("sap_return_number");
		$sp_sapReturnNumber->setRequired(false);
		$sp_sapReturnNumber->setTable("conclusion");
		$sp_sapReturnNumber->setHelpId(9110);
		$sp_materialReturnedYes->add($sp_sapReturnNumber);

		$sp_supplierCreditNoteRec = new radio("sp_supplierCreditNoteRec");
		$sp_supplierCreditNoteRec->setGroup("creditRaisedGroupYes4");
		$sp_supplierCreditNoteRec->setDataType("string");
		$sp_supplierCreditNoteRec->setLength(5);
		$sp_supplierCreditNoteRec->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_supplierCreditNoteRec->setRowTitle("supplier_credit_note_received");
		$sp_supplierCreditNoteRec->setRequired(true);
		$sp_supplierCreditNoteRec->setTable("conclusion");
		if(isset($savedFields["sp_supplierCreditNoteRec"]))
			$sp_supplierCreditNoteRec->setValue($savedFields["sp_supplierCreditNoteRec"]);
		else $sp_supplierCreditNoteRec->setValue("NO");
		$sp_supplierCreditNoteRec->setHelpId(8034);

		// Dependency
		$sp_supplierCreditNoteRec_dependency = new dependency();
		$sp_supplierCreditNoteRec_dependency->addRule(new rule('creditRaisedGroupYes4', 'sp_supplierCreditNoteRec', 'YES'));
		$sp_supplierCreditNoteRec_dependency->setGroup('sp_supplierCreditNoteRecYes');
		$sp_supplierCreditNoteRec_dependency->setShow(true);

		$sp_supplierCreditNoteRec->addControllingDependency($sp_supplierCreditNoteRec_dependency);
		$creditRaisedGroupYes4->add($sp_supplierCreditNoteRec);

		$sp_sapReturnFullNumber = new textbox("sp_sapReturnFullNumber");
		if(isset($savedFields["sp_sapReturnFullNumber"]))
			$sp_sapReturnFullNumber->setValue($savedFields["sp_sapReturnFullNumber"]);
		$sp_sapReturnFullNumber->setGroup("sp_supplierCreditNoteRecYes");
		$sp_sapReturnFullNumber->setDataType("string");
		$sp_sapReturnFullNumber->setLength(255);
		$sp_sapReturnFullNumber->setRowTitle("supplier_credit_number");
		$sp_sapReturnFullNumber->setRequired(false);
		$sp_sapReturnFullNumber->setTable("conclusion");
		$sp_sapReturnFullNumber->setHelpId(9110);
		$sp_supplierCreditNoteRecYes->add($sp_sapReturnFullNumber);

		$sp_supplierCreditNumber = new measurement("sp_supplierCreditNumber");
		if(isset($savedFields["sp_supplierCreditNumber_quantity"]) && isset($savedFields["sp_supplierCreditNumber_measurement"])){
			$arr[0] = $savedFields["sp_supplierCreditNumber_quantity"];
			$arr[1] = $savedFields["sp_supplierCreditNumber_measurement"];
			$sp_supplierCreditNumber->setValue($arr);
		}else $sp_supplierCreditNumber->setMeasurement("EUR");
		$sp_supplierCreditNumber->setGroup("sp_supplierCreditNoteRecYes");
		$sp_supplierCreditNumber->setDataType("string");
		$sp_supplierCreditNumber->setLength(5);
		$sp_supplierCreditNumber->setXMLSource("./apps/complaints/xml/currency.xml");
		$sp_supplierCreditNumber->setTable("conclusion");
		$sp_supplierCreditNumber->setRowTitle("supplier_credit_value");
		$sp_supplierCreditNumber->setRequired(false);
		$sp_supplierCreditNoteRecYes->add($sp_supplierCreditNumber);

//		$sp_amount = new measurement("sp_amount");
//		if(isset($savedFields["sp_amount_quantity"]) && isset($savedFields["sp_amount_measurement"])){
//			$arr[0] = $savedFields["sp_amount_quantity"];
//			$arr[1] = $savedFields["sp_amount_measurement"];
//			$sp_amount->setValue($arr);
//		}else $sp_amount->setMeasurement("m");
//		$sp_amount->setGroup("creditRaisedGroupYes2");
//		$sp_amount->setDataType("string");
//		$sp_amount->setLength(5);
//		$sp_amount->setXMLSource("./apps/complaints/xml/currency.xml");
//		$sp_amount->setRowTitle("amount");
//		$sp_amount->setRequired(false);
//		$creditRaisedGroupYes2->add($sp_amount);

//		$sp_currency = new textbox("sp_currency");
//		if(isset($savedFields["sp_currency"]))
//			$sp_currency->setValue($savedFields["sp_currency"]);
//		$sp_currency->setGroup("creditRaisedGroupYes");
//		$sp_currency->setDataType("string");
//		$sp_currency->setLength(255);
//		$sp_currency->setRowTitle("currency");
//		$sp_currency->setRequired(false);
//		$sp_currency->setTable("conclusion");
//		$sp_currency->setHelpId(9110);
//		$creditRaisedGroupYes->add($sp_currency);

		$sp_comment = new textarea("sp_comment");
		if(isset($savedFields["sp_comment"]))
			$sp_comment->setValue($savedFields["sp_comment"]);
		$sp_comment->setGroup("creditRaisedGroupYes41");
		$sp_comment->setDataType("text");
		$sp_comment->setRowTitle("comment");
		$sp_comment->setRequired(false);
		$sp_comment->setTable("conclusion");
		$sp_comment->setHelpId(9110);
		$creditRaisedGroupYes41->add($sp_comment);

		$sp_supplierReplacementRec = new radio("sp_supplierReplacementRec");
		$sp_supplierReplacementRec->setGroup("creditRaisedGroupYes41");
		$sp_supplierReplacementRec->setDataType("string");
		$sp_supplierReplacementRec->setLength(5);
		$sp_supplierReplacementRec->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$sp_supplierReplacementRec->setRowTitle("supplier_replacement_received");
		$sp_supplierReplacementRec->setRequired(true);
		$sp_supplierReplacementRec->setTable("conclusion");
		if(isset($savedFields["sp_supplierReplacementRec"]))
			$sp_supplierReplacementRec->setValue($savedFields["sp_supplierReplacementRec"]);
		else $sp_supplierReplacementRec->setValue("NO");
		$sp_supplierReplacementRec->setHelpId(8034);
		$creditRaisedGroupYes41->add($sp_supplierReplacementRec);

		$sp_finalComments = new textarea("sp_finalComments");
		if(isset($savedFields["sp_finalComments"]))
			$sp_finalComments->setValue($savedFields["sp_finalComments"]);
		$sp_finalComments->setGroup("creditRaisedGroupNo2");
		$sp_finalComments->setDataType("text");
		$sp_finalComments->setRowTitle("final_comment");
		$sp_finalComments->setRequired(false);
		$sp_finalComments->setTable("conclusion");
		$sp_finalComments->setHelpId(9110);
		$creditRaisedGroupNo2->add($sp_finalComments);

//		$internalComplaintStatus = new radio("internalComplaintStatus");
//		$internalComplaintStatus->setGroup("creditRaisedGroupNo");
//		$internalComplaintStatus->setDataType("string");
//		$internalComplaintStatus->setLength(5);
//		$internalComplaintStatus->setArraySource(array(
//			array('value' => 'Open', 'display' => 'Open'),
//			array('value' => 'Closed', 'display' => 'Closed')
//		));
//		$internalComplaintStatus->setRowTitle("internal_complaint_status");
//		$internalComplaintStatus->setRequired(false);
//		if(isset($savedFields["internal_complaint_status"]))
//			$internalComplaintStatus->setValue($savedFields["internal_complaint_status"]);
//		else $internalComplaintStatus->setValue("Open");
//		$internalComplaintStatus->setTable("conclusion");
//		$internalComplaintStatus->setLabel("Total Closure");
//		$internalComplaintStatus->setHelpId(9154);
//		$creditRaisedGroupNo->add($internalComplaintStatus);

		$internalComplaintStatus = new radio("internalComplaintStatus");
		$internalComplaintStatus->setGroup("totalClosureGroup");
		$internalComplaintStatus->setDataType("string");
		$internalComplaintStatus->setLength(5);
		$internalComplaintStatus->setArraySource(array(
			array('value' => 'Open', 'display' => 'Open'),
			array('value' => 'Closed', 'display' => 'Closed')
		));
		$internalComplaintStatus->setRowTitle("internal_supplier_status");
		$internalComplaintStatus->setRequired(false);
		if(isset($savedFields["internal_complaint_status"]))
			$internalComplaintStatus->setValue($savedFields["internal_complaint_status"]);
		else $internalComplaintStatus->setValue("Open");
		$internalComplaintStatus->setTable("conclusion");
		$internalComplaintStatus->setLabel("Total Closure");
		$internalComplaintStatus->setHelpId(9154);

		// Dependency
		$internalComplaintStatus_dependency = new dependency();
		$internalComplaintStatus_dependency->addRule(new rule('totalClosureGroup', 'internalComplaintStatus', 'Closed'));
		$internalComplaintStatus_dependency->setGroup('internalComplaintStatusClosed');
		$internalComplaintStatus_dependency->setShow(false);

		$internalComplaintStatus->addControllingDependency($internalComplaintStatus_dependency);
		$totalClosureGroup->add($internalComplaintStatus);


		$totalClosureDate = new calendar("totalClosureDate");
		if(isset($savedFields["totalClosureDate"]))
			$totalClosureDate->setValue($savedFields["totalClosureDate"]);
		$totalClosureDate->setGroup("totalClosureGroup");
		$totalClosureDate->setDataType("date");
		$totalClosureDate->setErrorMessage("textbox_date_error");
		$totalClosureDate->setLength(255);
		$totalClosureDate->setRowTitle("total_closure_date");
		$totalClosureDate->setVisible(false);
		$totalClosureDate->setTable("complaint");
		$totalClosureDate->setHelpId(9116);
		$internalComplaintStatusClosed->add($totalClosureDate);

		$processOwner3 = new autocomplete("processOwner3");
		if(isset($savedFields["processOwner3"]))
			$processOwner3->setValue($savedFields["processOwner3"]);
		$processOwner3->setGroup("totalClosureGroup");
		$processOwner3->setDataType("string");
		$processOwner3->setRowTitle("chosen_complaint_owner");
		$processOwner3->setRequired(false);
		$processOwner3->setUrl("/apps/complaints/ajax/newProcessOwner3?");
		//$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner3->setTable("conclusion");
		//$processOwner->clearData();
		$processOwner3->setHelpId(8145);
		$internalComplaintStatusClosed->add($processOwner3);

		$submit = new submit("submit");
		$submit->setGroup("submitGroup");
		$submit->setVisible(true);
		$submitGroup->add($submit);


		$this->form->add($initiation);
		$this->form->add($implementedGroup);
		$this->form->add($implementedGroupYes);
		$this->form->add($holdInvoiceGroup);
		$this->form->add($sp_submitToFinanceGroup);
		$this->form->add($sp_submitToFinanceYes);
		//$this->form->add($putFullAccountOnHoldGroup);
		//$this->form->add($creditRaisedGroup);
		$this->form->add($creditRaisedGroupYes);
		$this->form->add($customerDerongationYes);
		$this->form->add($creditRaisedGroupYes2);
		$this->form->add($sp_requestDisposalYes);
		$this->form->add($creditRaisedGroupYes3);
		$this->form->add($sp_requestAuthorisedYes);

		$this->form->add($sp_materialDisposedGroup);
		$this->form->add($sp_materialDisposedYes);
		$this->form->add($sp_materialReturnedGroup);
		$this->form->add($sp_materialReturnedYes);

		$this->form->add($creditRaisedGroupYes4);
		$this->form->add($sp_supplierCreditNoteRecYes);
		$this->form->add($creditRaisedGroupYes41);
		$this->form->add($creditRaisedGroupNo);
		$this->form->add($creditRaisedGroupNo2);
		$this->form->add($totalClosureGroup);
		$this->form->add($internalComplaintStatusClosed);
		$this->form->add($submitGroup);
	}

	public function defineQualityForm()
	{
		$savedFields = array();

		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("conclusion" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$qu_materialBlockedYes = new group("qu_materialBlockedYes");
		$complaintGroup = new group("complaintGroup");
		$qu_requestForDisposalYes = new group("qu_requestForDisposalYes");
		$qu_disposalAuthorisedGroup = new group("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorisedGroup->setBorder(false);
		$qu_disposalAuthorisedYes = new group("qu_disposalAuthorisedYes");
		$qu_disposalAuthorisedYes->setBorder(false);
		$qu_disposalBookedGroup = new group("qu_disposalBookedGroup");
		//$qu_disposalBookedGroup->setBorder(false);
		$qu_disposalBookedGroupYes =  new group("qu_disposalBookedGroupYes");
		$qu_disposalBookedGroupYes->setBorder(false);
		$qu_disposalPhysicallyDoneGroup = new group("qu_disposalPhysicallyDoneGroup");
		$qu_disposalPhysicallyDoneGroup->setBorder(false);
		$qu_disposalPhysicallyDoneGroupYes = new group("qu_disposalPhysicallyDoneGroupYes");
		$qu_disposalPhysicallyDoneGroupYes->setBorder(false);
		$qu_materialReturnedToCustomerGroup = new group("qu_materialReturnedToCustomerGroup");
		$qu_materialReturnedToCustomerGroup->setBorder(false);
		$qu_materialReturnedToCustomerGroupYes = new group("qu_materialReturnedToCustomerGroupYes");
		$qu_materialReturnedToCustomerGroupYes->setBorder(false);
		$totalClosureGroup = new group("totalClosureGroup");
		$totalClosureGroup->setBorder(false);
		$internalComplaintStatusClosed = new group("internalComplaintStatusClosed");
		$internalComplaintStatusClosed->setBorder(false);
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);

		// Complete Finance Step for Supplier Complaint
		$qualityFinanceComplete = new textbox("qualityFinanceComplete");
		$qualityFinanceComplete->setTable("conclusion");
		$qualityFinanceComplete->setVisible(false);
		$qualityFinanceComplete->setGroup("initiation");
		$qualityFinanceComplete->setDataType("string");
		$initiation->add($qualityFinanceComplete);

		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("conclusion");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		else $status->setValue("initiation");
		$status->setTable("conclusion");
		$status->setVisible(false);
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
			$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$qu_materialUnBlocked = new radio("qu_materialUnBlocked");
		$qu_materialUnBlocked->setGroup("initiation");
		$qu_materialUnBlocked->setDataType("string");
		$qu_materialUnBlocked->setLength(5);
		$qu_materialUnBlocked->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_materialUnBlocked->setRowTitle("material_unblocked");
		$qu_materialUnBlocked->setRequired(false);
		if(isset($savedFields["qu_materialUnBlocked"]))
			$qu_materialUnBlocked->setValue($savedFields["qu_materialUnBlocked"]);
		else $qu_materialUnBlocked->setValue("No");
		$qu_materialUnBlocked->setTable("conclusion");
		$qu_materialUnBlocked->setHelpId(9154);

		// Dependency
		$qu_materialUnBlocked_dependency = new dependency();
		$qu_materialUnBlocked_dependency->addRule(new rule('initiation', 'qu_materialUnBlocked', 'Yes'));
		$qu_materialUnBlocked_dependency->setGroup('qu_materialBlockedYes');
		$qu_materialUnBlocked_dependency->setShow(true);

		$qu_materialUnBlocked->addControllingDependency($qu_materialUnBlocked_dependency);
		$initiation->add($qu_materialUnBlocked);

		$qu_materialUnBlockedName = new textbox("qu_materialUnBlockedName");
		if(isset($savedFields["qu_materialUnBlockedName"]))
			$qu_materialUnBlockedName->setValue($savedFields["qu_materialUnBlockedName"]);
		$qu_materialUnBlockedName->setGroup("qu_materialBlockedYes");
		$qu_materialUnBlockedName->setDataType("string");
		$qu_materialUnBlockedName->setLength(255);
		$qu_materialUnBlockedName->setRowTitle("material_unblocked_name");
		$qu_materialUnBlockedName->setTable("conclusion");
		$qu_materialUnBlockedName->setHelpId(9116);
		$qu_materialBlockedYes->add($qu_materialUnBlockedName);

		$qu_materialUnBlockedDate = new calendar("qu_materialUnBlockedDate");
		if(isset($savedFields["qu_materialUnBlockedDate"]))
			$qu_materialUnBlockedDate->setValue($savedFields["qu_materialUnBlockedDate"]);
		$qu_materialUnBlockedDate->setGroup("qu_materialBlockedYes");
		$qu_materialUnBlockedDate->setDataType("date");
		$qu_materialUnBlockedDate->setErrorMessage("textbox_date_error");
		$qu_materialUnBlockedDate->setRowTitle("material_unblocked_date");
		$qu_materialUnBlockedDate->setTable("conclusion");
		$qu_materialUnBlockedDate->setHelpId(9116);
		$qu_materialBlockedYes->add($qu_materialUnBlockedDate);

		$qu_requestForDisposal = new radio("qu_requestForDisposal");
		$qu_requestForDisposal->setGroup("complaintGroup");
		$qu_requestForDisposal->setDataType("string");
		$qu_requestForDisposal->setLength(5);
		$qu_requestForDisposal->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_requestForDisposal->setRowTitle("request_for_disposal");
		$qu_requestForDisposal->setRequired(false);
		if(isset($savedFields["qu_requestForDisposal"]))
			$qu_requestForDisposal->setValue($savedFields["qu_requestForDisposal"]);
		else $qu_requestForDisposal->setValue("No");
		$qu_requestForDisposal->setTable("conclusion");
		$qu_requestForDisposal->setHelpId(9154);

		// Dependency
		$qu_requestForDisposal_dependency = new dependency();
		$qu_requestForDisposal_dependency->addRule(new rule('complaintGroup', 'qu_requestForDisposal', 'Yes'));
		$qu_requestForDisposal_dependency->setGroup('qu_requestForDisposalYes');
		$qu_requestForDisposal_dependency->setShow(true);

		$qu_requestForDisposal->addControllingDependency($qu_requestForDisposal_dependency);
		$complaintGroup->add($qu_requestForDisposal);

		$qu_requestForDisposalReadOnly = new readonly("qu_requestForDisposalReadOnly");
		$qu_requestForDisposalReadOnly->setGroup("complaintGroup");
		$qu_requestForDisposalReadOnly->setDataType("string");
		$qu_requestForDisposalReadOnly->setLength(5);
		$qu_requestForDisposalReadOnly->setRowTitle("request_for_disposal");
		$qu_requestForDisposalReadOnly->setVisible(false);
		$qu_requestForDisposalReadOnly->setLabel("Request For Disposal");
		$qu_requestForDisposalReadOnly->setHelpId(9189);
		$complaintGroup->add($qu_requestForDisposalReadOnly);

		$qu_amount = new measurement("qu_amount");
		if(isset($savedFields["qu_amount_quantity"]) && isset($savedFields["qu_amount_measurement"])){
			$arr[0] = $savedFields["qu_amount_quantity"];
			$arr[1] = $savedFields["qu_amount_measurement"];
			$qu_amount->setValue($arr);
		}else $qu_amount->setMeasurement("mm");
		$qu_amount->setGroup("qu_requestForDisposalYes");
		$qu_amount->setDataType("string");
		$qu_amount->setLength(5);
		$qu_amount->setXMLSource("./apps/complaints/xml/currency.xml");
		$qu_amount->setRowTitle("amount");
		$qu_amount->setTable("conclusion");
		$qu_amount->setHelpId(8018);
		$qu_requestForDisposalYes->add($qu_amount);

		$qu_amountReadOnly = new readonly("qu_amountReadOnly");
		$qu_amountReadOnly->setGroup("complaintGroup");
		$qu_amountReadOnly->setDataType("string");
		$qu_amountReadOnly->setLength(5);
		$qu_amountReadOnly->setRowTitle("amount");
		$qu_amountReadOnly->setVisible(false);
		$qu_amountReadOnly->setHelpId(9189);
		$complaintGroup->add($qu_amountReadOnly);

		$qu_requestDate = new textbox("qu_requestDate");
		if(isset($savedFields["qu_materialBlockedDate"]))
			$qu_requestDate->setValue($savedFields["qu_materialBlockedDate"]);
		$qu_requestDate->setGroup("qu_requestForDisposalYes");
		$qu_requestDate->setDataType("date");
		$qu_requestDate->setErrorMessage("textbox_date_error");
		$qu_requestDate->setRowTitle("date");
		$qu_requestDate->setVisible(false);
		$qu_requestDate->setTable("conclusion");
		$qu_requestDate->setHelpId(9116);
		$qu_requestForDisposalYes->add($qu_requestDate);

		$qu_requestDateReadOnly = new readonly("qu_requestDateReadOnly");
		$qu_requestDateReadOnly->setGroup("complaintGroup");
		$qu_requestDateReadOnly->setDataType("string");
		$qu_requestDateReadOnly->setLength(5);
		$qu_requestDateReadOnly->setRowTitle("date");
		$qu_requestDateReadOnly->setVisible(false);
		$qu_requestDateReadOnly->setHelpId(9189);
		$complaintGroup->add($qu_requestDateReadOnly);

		$qu_requestDisposalName = new autocomplete("qu_requestDisposalName");
		if(isset($savedFields["qu_requestDisposalName"]))
			$qu_requestDisposalName->setValue($savedFields["qu_requestDisposalName"]);
		$qu_requestDisposalName->setGroup("qu_requestForDisposalYes");
		$qu_requestDisposalName->setDataType("string");
		$qu_requestDisposalName->setRowTitle("chosen_complaint_owner");
		$qu_requestDisposalName->setRequired(false);
		$qu_requestDisposalName->setUrl("/apps/complaints/ajax/quRequestDisposalName?");
		$qu_requestDisposalName->setTable("conclusion");
		$qu_requestDisposalName->setHelpId(8145);
		$qu_requestForDisposalYes->add($qu_requestDisposalName);

		$qu_requestDisposalNameReadOnly = new readonly("qu_requestDisposalNameReadOnly");
		$qu_requestDisposalNameReadOnly->setGroup("complaintGroup");
		$qu_requestDisposalNameReadOnly->setDataType("string");
		$qu_requestDisposalNameReadOnly->setLength(5);
		$qu_requestDisposalNameReadOnly->setRowTitle("chosen_complaint_owner");
		$qu_requestDisposalNameReadOnly->setVisible(false);
		$qu_requestDisposalNameReadOnly->setHelpId(9189);
		$complaintGroup->add($qu_requestDisposalNameReadOnly);

		$requestForDisposalSubmit = new submit("requestForDisposalSubmit");
		$requestForDisposalSubmit->setGroup("qu_requestForDisposalYes");
		$requestForDisposalSubmit->setVisible(true);
		$qu_requestForDisposalYes->add($requestForDisposalSubmit);

		$qu_disposalAuthorised = new radio("qu_disposalAuthorised");
		$qu_disposalAuthorised->setGroup("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorised->setDataType("string");
		$qu_disposalAuthorised->setLength(5);
		$qu_disposalAuthorised->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_disposalAuthorised->setRowTitle("disposal_authorised");
		$qu_disposalAuthorised->setRequired(false);
		if(isset($savedFields["qu_requestForDisposal"]))
			$qu_disposalAuthorised->setValue($savedFields["qu_requestForDisposal"]);
		else $qu_disposalAuthorised->setValue("No");
		$qu_disposalAuthorised->setTable("conclusion");
		$qu_disposalAuthorised->setHelpId(9154);

		// Dependency
		$qu_disposalAuthorised_dependency = new dependency();
		$qu_disposalAuthorised_dependency->addRule(new rule('qu_disposalAuthorisedGroup', 'qu_disposalAuthorised', 'Yes'));
		$qu_disposalAuthorised_dependency->setGroup('qu_disposalAuthorisedYes');
		$qu_disposalAuthorised_dependency->setShow(true);

		$qu_disposalAuthorised->addControllingDependency($qu_disposalAuthorised_dependency);
		$qu_disposalAuthorisedGroup->add($qu_disposalAuthorised);

		$qu_disposalAuthorisedReadOnly = new readonly("qu_disposalAuthorisedReadOnly");
		$qu_disposalAuthorisedReadOnly->setGroup("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorisedReadOnly->setDataType("string");
		$qu_disposalAuthorisedReadOnly->setLength(5);
		$qu_disposalAuthorisedReadOnly->setLabel("Approval For Disposal");
		$qu_disposalAuthorisedReadOnly->setRowTitle("disposal_authorised");
		$qu_disposalAuthorisedReadOnly->setVisible(false);
		$qu_disposalAuthorisedReadOnly->setHelpId(9189);
		$qu_disposalAuthorisedGroup->add($qu_disposalAuthorisedReadOnly);

		$qu_disposalAuthorisedDate = new calendar("qu_disposalAuthorisedDate");
		if(isset($savedFields["qu_disposalAuthorisedDate"]))
			$qu_disposalAuthorisedDate->setValue($savedFields["qu_disposalAuthorisedDate"]);
		$qu_disposalAuthorisedDate->setGroup("qu_disposalAuthorisedYes");
		$qu_disposalAuthorisedDate->setDataType("date");
		$qu_disposalAuthorisedDate->setErrorMessage("textbox_date_error");
		$qu_disposalAuthorisedDate->setRowTitle("date");
		$qu_disposalAuthorisedDate->setTable("conclusion");
		$qu_disposalAuthorisedDate->setVisible(false);
		$qu_disposalAuthorisedDate->setHelpId(9116);
		$qu_disposalAuthorisedYes->add($qu_disposalAuthorisedDate);

		$qu_disposalAuthorisedDateReadOnly = new readonly("qu_disposalAuthorisedDateReadOnly");
		$qu_disposalAuthorisedDateReadOnly->setGroup("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorisedDateReadOnly->setDataType("string");
		$qu_disposalAuthorisedDateReadOnly->setLength(5);
		$qu_disposalAuthorisedDateReadOnly->setRowTitle("date");
		$qu_disposalAuthorisedDateReadOnly->setVisible(false);
		$qu_disposalAuthorisedDateReadOnly->setHelpId(9189);
		$qu_disposalAuthorisedGroup->add($qu_disposalAuthorisedDateReadOnly);

		$qu_disposalAuthorisedComment = new textarea("qu_disposalAuthorisedComment");
		if(isset($savedFields["qu_disposalAuthorisedComment"]))
			$qu_disposalAuthorisedComment->setValue($savedFields["qu_disposalAuthorisedComment"]);
		$qu_disposalAuthorisedComment->setGroup("qu_disposalAuthorisedYes");
		$qu_disposalAuthorisedComment->setDataType("text");
		$qu_disposalAuthorisedComment->setRowTitle("comment");
		$qu_disposalAuthorisedComment->setTable("conclusion");
		$qu_disposalAuthorisedComment->setHelpId(9116);
		$qu_disposalAuthorisedYes->add($qu_disposalAuthorisedComment);

		$qu_disposalAuthorisedCommentReadOnly = new readonly("qu_disposalAuthorisedCommentReadOnly");
		$qu_disposalAuthorisedCommentReadOnly->setGroup("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorisedCommentReadOnly->setDataType("string");
		$qu_disposalAuthorisedCommentReadOnly->setLength(5);
		$qu_disposalAuthorisedCommentReadOnly->setRowTitle("comment");
		$qu_disposalAuthorisedCommentReadOnly->setVisible(false);
		$qu_disposalAuthorisedCommentReadOnly->setHelpId(9189);
		$qu_disposalAuthorisedGroup->add($qu_disposalAuthorisedCommentReadOnly);

		$qu_disposalAuthorisedName = new autocomplete("qu_disposalAuthorisedName");
		if(isset($savedFields["qu_disposalAuthorisedName"]))
			$qu_disposalAuthorisedName->setValue($savedFields["qu_disposalAuthorisedName"]);
		$qu_disposalAuthorisedName->setGroup("qu_disposalAuthorisedYes");
		$qu_disposalAuthorisedName->setDataType("string");
		$qu_disposalAuthorisedName->setRowTitle("chosen_complaint_owner");
		$qu_disposalAuthorisedName->setRequired(false);
		$qu_disposalAuthorisedName->setUrl("/apps/complaints/ajax/quDisposalAuthorisedName?");
		$qu_disposalAuthorisedName->setTable("conclusion");
		$qu_disposalAuthorisedName->setHelpId(8145);
		$qu_disposalAuthorisedYes->add($qu_disposalAuthorisedName);

		$qu_disposalAuthorisedNameReadOnly = new readonly("qu_disposalAuthorisedNameReadOnly");
		$qu_disposalAuthorisedNameReadOnly->setGroup("qu_disposalAuthorisedGroup");
		$qu_disposalAuthorisedNameReadOnly->setDataType("string");
		$qu_disposalAuthorisedNameReadOnly->setLength(5);
		$qu_disposalAuthorisedNameReadOnly->setRowTitle("chosen_complaint_owner");
		$qu_disposalAuthorisedNameReadOnly->setVisible(false);
		$qu_disposalAuthorisedNameReadOnly->setHelpId(9189);
		$qu_disposalAuthorisedGroup->add($qu_disposalAuthorisedNameReadOnly);

		$qu_disposalAuthorisedCommentSubmit = new submit("qu_disposalAuthorisedCommentSubmit");
		$qu_disposalAuthorisedCommentSubmit->setGroup("qu_disposalAuthorisedYes");
		$qu_disposalAuthorisedCommentSubmit->setVisible(true);
		$qu_disposalAuthorisedYes->add($qu_disposalAuthorisedCommentSubmit);


		$qu_disposalBooked = new radio("qu_disposalBooked");
		$qu_disposalBooked->setGroup("qu_disposalBookedGroup");
		$qu_disposalBooked->setDataType("string");
		$qu_disposalBooked->setLength(5);
		$qu_disposalBooked->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_disposalBooked->setRowTitle("disposal_booked");
		$qu_disposalBooked->setRequired(false);
		if(isset($savedFields["qu_requestForDisposal"]))
			$qu_disposalBooked->setValue($savedFields["qu_requestForDisposal"]);
		else $qu_disposalBooked->setValue("No");
		$qu_disposalBooked->setTable("conclusion");
		$qu_disposalBooked->setHelpId(9154);

		// Dependency
		$qu_disposalBooked_dependency = new dependency();
		$qu_disposalBooked_dependency->addRule(new rule('qu_disposalBookedGroup', 'qu_disposalBooked', 'Yes'));
		$qu_disposalBooked_dependency->setGroup('qu_disposalBookedGroupYes');
		$qu_disposalBooked_dependency->setShow(true);

		$qu_disposalBooked->addControllingDependency($qu_disposalBooked_dependency);
		$qu_disposalBookedGroup->add($qu_disposalBooked);

		$qu_disposalBookedName = new textbox("qu_disposalBookedName");
		if(isset($savedFields["qu_disposalBookedName"]))
			$qu_disposalBookedName->setValue($savedFields["qu_disposalBookedName"]);
		$qu_disposalBookedName->setGroup("qu_disposalBookedGroupYes");
		$qu_disposalBookedName->setDataType("string");
		$qu_disposalBookedName->setRowTitle("name");
		$qu_disposalBookedName->setTable("conclusion");
		$qu_disposalBookedName->setHelpId(9116);
		$qu_disposalBookedGroupYes->add($qu_disposalBookedName);

		$qu_disposalBookedDate = new calendar("qu_disposalBookedDate");
		if(isset($savedFields["qu_disposalBookedDate"]))
			$qu_disposalBookedDate->setValue($savedFields["qu_disposalBookedDate"]);
		$qu_disposalBookedDate->setGroup("qu_disposalBookedGroupYes");
		$qu_disposalBookedDate->setErrorMessage("textbox_date_error");
		$qu_disposalBookedDate->setRowTitle("date");
		$qu_disposalBookedDate->setDataType("date");
		$qu_disposalBookedDate->setTable("conclusion");
		$qu_disposalBookedDate->setHelpId(9116);
		$qu_disposalBookedGroupYes->add($qu_disposalBookedDate);

		$qu_disposalCode = new textbox("qu_disposalCode");
		if(isset($savedFields["qu_disposalCode"]))
			$qu_disposalCode->setValue($savedFields["qu_disposalCode"]);
		$qu_disposalCode->setGroup("qu_disposalBookedGroupYes");
		$qu_disposalCode->setDataType("string");
		$qu_disposalCode->setRowTitle("disposal_code");
		$qu_disposalCode->setTable("conclusion");
		$qu_disposalCode->setHelpId(9116);
		$qu_disposalBookedGroupYes->add($qu_disposalCode);

		$qu_disposalCostCentre = new textbox("qu_disposalCostCentre");
		if(isset($savedFields["qu_disposalCostCentre"]))
			$qu_disposalCostCentre->setValue($savedFields["qu_disposalCostCentre"]);
		$qu_disposalCostCentre->setGroup("qu_disposalBookedGroupYes");
		$qu_disposalCostCentre->setDataType("string");
		$qu_disposalCostCentre->setRowTitle("disposal_cost_centre");
		$qu_disposalCostCentre->setTable("conclusion");
		$qu_disposalCostCentre->setHelpId(9116);
		$qu_disposalBookedGroupYes->add($qu_disposalCostCentre);

		$qu_disposalPhysicallyDone = new radio("qu_disposalPhysicallyDone");
		$qu_disposalPhysicallyDone->setGroup("qu_disposalPhysicallyDoneGroup");
		$qu_disposalPhysicallyDone->setDataType("string");
		$qu_disposalPhysicallyDone->setLength(5);
		$qu_disposalPhysicallyDone->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_disposalPhysicallyDone->setRowTitle("disposal_physically_done");
		$qu_disposalPhysicallyDone->setRequired(false);
		if(isset($savedFields["qu_disposalPhysicallyDone"]))
			$qu_disposalPhysicallyDone->setValue($savedFields["qu_disposalPhysicallyDone"]);
		else $qu_disposalPhysicallyDone->setValue("No");
		$qu_disposalPhysicallyDone->setTable("conclusion");
		$qu_disposalPhysicallyDone->setHelpId(9154);

		// Dependency
		$qu_disposalPhysicallyDone_dependency = new dependency();
		$qu_disposalPhysicallyDone_dependency->addRule(new rule('qu_disposalPhysicallyDoneGroup', 'qu_disposalPhysicallyDone', 'Yes'));
		$qu_disposalPhysicallyDone_dependency->setGroup('qu_disposalPhysicallyDoneGroupYes');
		$qu_disposalPhysicallyDone_dependency->setShow(true);

		$qu_disposalPhysicallyDone->addControllingDependency($qu_disposalPhysicallyDone_dependency);
		$qu_disposalPhysicallyDoneGroup->add($qu_disposalPhysicallyDone);

		$qu_disposalPhysicallyDoneName = new textbox("qu_disposalPhysicallyDoneName");
		if(isset($savedFields["qu_disposalPhysicallyDoneName"]))
			$qu_disposalPhysicallyDoneName->setValue($savedFields["qu_disposalPhysicallyDoneName"]);
		$qu_disposalPhysicallyDoneName->setGroup("qu_disposalPhysicallyDoneGroupYes");
		$qu_disposalPhysicallyDoneName->setDataType("string");
		$qu_disposalPhysicallyDoneName->setRowTitle("name");
		$qu_disposalPhysicallyDoneName->setTable("conclusion");
		$qu_disposalPhysicallyDoneName->setHelpId(9116);
		$qu_disposalPhysicallyDoneGroupYes->add($qu_disposalPhysicallyDoneName);

		$qu_disposalPhysicallyDoneDate = new calendar("qu_disposalPhysicallyDoneDate");
		if(isset($savedFields["qu_disposalPhysicallyDoneDate"]))
			$qu_disposalPhysicallyDoneDate->setValue($savedFields["qu_disposalPhysicallyDoneDate"]);
		$qu_disposalPhysicallyDoneDate->setGroup("qu_disposalPhysicallyDoneGroupYes");
		$qu_disposalPhysicallyDoneDate->setDataType("date");
		$qu_disposalPhysicallyDoneDate->setErrorMessage("textbox_date_error");
		$qu_disposalPhysicallyDoneDate->setRowTitle("date");
		$qu_disposalPhysicallyDoneDate->setTable("conclusion");
		$qu_disposalPhysicallyDoneDate->setHelpId(9116);
		$qu_disposalPhysicallyDoneGroupYes->add($qu_disposalPhysicallyDoneDate);

		$qu_materialReturnedToCustomer = new radio("qu_materialReturnedToCustomer");
		$qu_materialReturnedToCustomer->setGroup("qu_materialReturnedToCustomerGroup");
		$qu_materialReturnedToCustomer->setDataType("string");
		$qu_materialReturnedToCustomer->setLength(5);
		$qu_materialReturnedToCustomer->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_materialReturnedToCustomer->setRowTitle("material_returned_to_customer");
		$qu_materialReturnedToCustomer->setRequired(false);
		if(isset($savedFields["qu_materialReturnedToCustomer"]))
			$qu_materialReturnedToCustomer->setValue($savedFields["qu_materialReturnedToCustomer"]);
		else $qu_materialReturnedToCustomer->setValue("No");
		$qu_materialReturnedToCustomer->setTable("conclusion");
		$qu_materialReturnedToCustomer->setHelpId(9154);

		// Dependency
		$qu_materialReturnedToCustomer_dependency = new dependency();
		$qu_materialReturnedToCustomer_dependency->addRule(new rule('qu_materialReturnedToCustomerGroup', 'qu_materialReturnedToCustomer', 'Yes'));
		$qu_materialReturnedToCustomer_dependency->setGroup('qu_materialReturnedToCustomerGroupYes');
		$qu_materialReturnedToCustomer_dependency->setShow(true);

		$qu_materialReturnedToCustomer->addControllingDependency($qu_materialReturnedToCustomer_dependency);
		$qu_materialReturnedToCustomerGroup->add($qu_materialReturnedToCustomer);

		$qu_materialReturnedToCustomerName = new textbox("qu_materialReturnedToCustomerName");
		if(isset($savedFields["qu_materialReturnedToCustomerName"]))
			$qu_materialReturnedToCustomerName->setValue($savedFields["qu_materialReturnedToCustomerName"]);
		$qu_materialReturnedToCustomerName->setGroup("qu_materialReturnedToCustomerGroupYes");
		$qu_materialReturnedToCustomerName->setDataType("string");
		$qu_materialReturnedToCustomerName->setRowTitle("name");
		$qu_materialReturnedToCustomerName->setTable("conclusion");
		$qu_materialReturnedToCustomerName->setHelpId(9116);
		$qu_materialReturnedToCustomerGroupYes->add($qu_materialReturnedToCustomerName);

		$qu_materialReturnedToCustomerDate = new calendar("qu_materialReturnedToCustomerDate");
		if(isset($savedFields["qu_materialReturnedToCustomerDate"]))
			$qu_materialReturnedToCustomerDate->setValue($savedFields["qu_materialReturnedToCustomerDate"]);
		$qu_materialReturnedToCustomerDate->setGroup("qu_materialReturnedToCustomerGroupYes");
		$qu_materialReturnedToCustomerDate->setDataType("date");
		$qu_materialReturnedToCustomerDate->setErrorMessage("textbox_date_error");
		$qu_materialReturnedToCustomerDate->setRowTitle("date");
		$qu_materialReturnedToCustomerDate->setTable("conclusion");
		$qu_materialReturnedToCustomerDate->setHelpId(9116);
		$qu_materialReturnedToCustomerGroupYes->add($qu_materialReturnedToCustomerDate);

		$finalComments = new textarea("finalComments");
		if(isset($savedFields["finalComments"]))
			$finalComments->setValue($savedFields["finalComments"]);
		$finalComments->setGroup("totalClosureGroup");
		$finalComments->setDataType("text");
		$finalComments->setRowTitle("final_comments");
		$finalComments->setTable("conclusion");
		$finalComments->setHelpId(9116);
		$totalClosureGroup->add($finalComments);


		$internalComplaintStatus = new radio("internalComplaintStatus");
		$internalComplaintStatus->setGroup("totalClosureGroup");
		$internalComplaintStatus->setDataType("string");
		$internalComplaintStatus->setLength(5);
		$internalComplaintStatus->setArraySource(array(
			array('value' => 'Open', 'display' => 'Open'),
			array('value' => 'Closed', 'display' => 'Closed')
		));
		$internalComplaintStatus->setRowTitle("internal_quality_status");
		$internalComplaintStatus->setRequired(false);
		if(isset($savedFields["internal_complaint_status"]))
			$internalComplaintStatus->setValue($savedFields["internal_complaint_status"]);
		else $internalComplaintStatus->setValue("Open");
		$internalComplaintStatus->setTable("conclusion");
		$internalComplaintStatus->setLabel("Total Closure");
		$internalComplaintStatus->setHelpId(9154);

		// Dependency
		$internalComplaintStatus_dependency = new dependency();
		$internalComplaintStatus_dependency->addRule(new rule('totalClosureGroup', 'internalComplaintStatus', 'Closed'));
		$internalComplaintStatus_dependency->setGroup('internalComplaintStatusClosed');
		$internalComplaintStatus_dependency->setShow(false);

		$internalComplaintStatus->addControllingDependency($internalComplaintStatus_dependency);
		$totalClosureGroup->add($internalComplaintStatus);


		$totalClosureDate = new calendar("totalClosureDate");
		if(isset($savedFields["totalClosureDate"]))
			$totalClosureDate->setValue($savedFields["totalClosureDate"]);
		$totalClosureDate->setGroup("totalClosureGroup");
		$totalClosureDate->setDataType("date");
		$totalClosureDate->setErrorMessage("textbox_date_error");
		$totalClosureDate->setLength(255);
		$totalClosureDate->setRowTitle("total_closure_date");
		$totalClosureDate->setVisible(false);
		$totalClosureDate->setTable("complaint");
		$totalClosureDate->setHelpId(9116);
		$internalComplaintStatusClosed->add($totalClosureDate);

		$processOwner3 = new autocomplete("processOwner3");
		if(isset($savedFields["processOwner3"]))
			$processOwner3->setValue($savedFields["processOwner3"]);
		$processOwner3->setGroup("internalComplaintStatusClosed");
		$processOwner3->setDataType("string");
		$processOwner3->setRowTitle("chosen_complaint_owner");
		$processOwner3->setRequired(false);
		$processOwner3->setUrl("/apps/complaints/ajax/newProcessOwner3?");
		//$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner3->setTable("conclusion");
		//$processOwner->clearData();
		$processOwner3->setHelpId(8145);
		$internalComplaintStatusClosed->add($processOwner3);


		$submit = new submit("submit");
		$submit->setGroup("submitGroup");
		$submit->setVisible(true);
		$submitGroup->add($submit);


		$this->form->add($initiation);
		$this->form->add($qu_materialBlockedYes);
		$this->form->add($complaintGroup);
		$this->form->add($qu_requestForDisposalYes);
		$this->form->add($qu_disposalAuthorisedGroup);
		$this->form->add($qu_disposalAuthorisedYes);
		$this->form->add($qu_disposalBookedGroup);
		$this->form->add($qu_disposalBookedGroupYes);
		$this->form->add($qu_disposalPhysicallyDoneGroup);
		$this->form->add($qu_disposalPhysicallyDoneGroupYes);
		$this->form->add($qu_materialReturnedToCustomerGroup);
		$this->form->add($qu_materialReturnedToCustomerGroupYes);
		$this->form->add($totalClosureGroup);
		$this->form->add($internalComplaintStatusClosed);
		$this->form->add($submitGroup);
	}

	public function getEmailNotification($id, $sender, $action, $message)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailMessage>" . utf8_decode($message) . "</emailMessage><complaintJustified>" . /*$this->complaint->getEvaluation()->form->get("complaintJustified")->getValue()*/ "</complaintJustified></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		//$cc = $this->form->get("delegate_owner")->getValue();

		email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), /*"intranet@scapa.com"*/$sender, translate::getInstance()->translate("new_complaint_action") . " - ID: " . $id, "$email", "");

		return true;
	}

	public function getSupplierEmailNotification($id, $supplierNumber, $action)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM `supplier` WHERE `id` = '" . $supplierNumber ."'");
		$fields = mysql_fetch_array($dataset);

		email::send($fields['emailAddress'], "intranet@scapa.com", (translate::getInstance()->translate("complaint_closed") . " - ID: " . $id), "$email");

		return true;
	}


}

?>