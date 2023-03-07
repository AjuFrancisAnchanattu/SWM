<?php

/**
 * This is the Complaint Application.
 *
 * This is the evaluation class.  This class is used to conduct the Evaluation part of the Complaint process.
 *
 * @package apps
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class evaluation extends complaintsProcess
{
	/**
	 * The constructor, which the Complaints is passed to.
	 *
	 * @param complaints $complaints
	 */

	public $attachments;

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

				$complaintTypeID = $dataRow['sfTypeOfComplaint'];

				if($complaintTypeID == "supplier_complaint")
				{
					$this->defineSupplierForm();
					$this->complaintType = "supplier_complaint";
				}
				elseif($complaintTypeID == "quality_complaint")
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

		// For NA Complaints
		$cat = $this->getCategory();

		if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
		{
			if($this->complaint->getComplaintType($complaintTypeID) == "customer_complaint")
			{
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation WHERE complaintId = '" . $this->complaint->getId() . "'");
				$fields = mysql_fetch_array($dataset);

				if(mysql_num_rows($dataset) == 0)
				{
					$this->form->get("returnRequestValue")->setValue(array($this->complaint->form->get("complaintValue")->getQuantity(), $this->complaint->form->get("complaintValue")->getMeasurement()));
					$this->form->get("returnApprovalDisposalValue")->setValue(array($this->complaint->form->get("complaintValue")->getQuantity(), $this->complaint->form->get("complaintValue")->getMeasurement()));

				}
				else
				{
					$this->form->get("returnRequestValue")->setValue(array($fields['returnRequestValue_quantity'], $fields['returnRequestValue_measurement']));
					$this->form->get("returnApprovalDisposalValue")->setValue(array($fields['returnApprovalDisposalValue_quantity'], $fields['returnApprovalDisposalValue_measurement']));

				}
			}
		}

		$this->form->get('complaintId')->setValue($this->complaint->getId());

		$this->form->setStoreInSession(true);

		$this->form->loadSessionData();

		if(isset($_SESSION['apps'][$GLOBALS['app']]['evaluation']['loadedFromDatabase']) && isset($_REQUEST['complaint']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the Complaint is loaded from the database
		}

		$this->form->processDependencies();
	}

	public function lockComplaint($id, $status)
	{
		$nowTimeStamp = strtotime(date('Y-m-d H:i:s', time()));
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET locked = '" . $status . "' WHERE id = " . $id . "");
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET lockedTime = '" . $nowTimeStamp . "' WHERE id = " . $id . "");
	}

	public function getCategory()
	{
		return $this->complaint->form->get("category")->getValue();
	}

	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);

		if(!isset($_REQUEST["sfID"]))
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation LEFT JOIN complaint ON evaluation.complaintId=complaint.id WHERE complaintId = "  . $id);
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation LEFT JOIN complaint ON evaluation.complaintId=complaint.id WHERE complaintId = 'UNIX_TIMESTAMP(NOW())'");
		}

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['evaluation']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

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

			$this->form->populate($fields);

			if($this->complaint->getComplaintType($this->id) == "quality_complaint")
			{
				// do nothing ..
			}
			else
			{
				if(!$this->form->get("complaintJustified")->getValue())
				{
					$this->form->get("complaintJustified")->setValue("na");
					$this->form->get("returnGoods")->setValue("na");
					$this->form->get("disposeGoods")->setValue("na");
					//$this->form->get("sp_materialCredited")->setValue("na");
					//$this->form->get("sp_materialReplaced")->setValue("na");
					//$this->form->get("sp_useGoods")->setValue("na");
				}
			}

			if($this->complaint->getComplaintType($this->id) == "supplier_complaint")
			{
				$this->form->get("defectQuantity3")->setValue($fields['defectQuantity3_quantity']);
				$this->form->get("defectQuantity2")->setValue($fields['defectQuantity2_quantity']);
				$this->form->get("defectQuantity")->setValue($fields['defectQuantity_quantity']);
			}
			elseif($this->complaint->getComplaintType($this->id) == "quality_complaint")
			{
				$this->form->get("attachment")->load("/apps/complaints/attachments/eval/" . $this->id . "/");
				$fields['analysisDate'] == "0000-00-00" ?	$this->form->get('analysisDate')->setValue("") : $this->form->get('analysisDate')->setValue(page::transformDateForPHP($fields['analysisDate']));
				$fields['rootCausesDate'] == "0000-00-00" ?  $this->form->get('rootCausesDate')->setValue("") : $this->form->get('rootCausesDate')->setValue(page::transformDateForPHP($fields['rootCausesDate']));
				$fields['implementedActionsDate'] == "0000-00-00" ?  $this->form->get('implementedActionsDate')->setValue("") : $this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($fields['implementedActionsDate']));
			}
			else
			{
				$this->form->get("attachment")->load("/apps/complaints/attachments/eval/" . $this->id . "/");
				$fields['analysisDate'] == "0000-00-00" ?	$this->form->get('analysisDate')->setValue("") : $this->form->get('analysisDate')->setValue(page::transformDateForPHP($fields['analysisDate']));
				$fields['rootCausesDate'] == "0000-00-00" ?  $this->form->get('rootCausesDate')->setValue("") : $this->form->get('rootCausesDate')->setValue(page::transformDateForPHP($fields['rootCausesDate']));
				$this->form->get("updateInitiator")->setValue("No");

				$fields['implementedActionsDate'] == "0000-00-00" ?  $this->form->get('implementedActionsDate')->setValue("") : $this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($fields['implementedActionsDate']));
				$fields['implementedPermanentCorrectiveActionValidatedDate'] == "0000-00-00" ?  $this->form->get('implementedPermanentCorrectiveActionValidatedDate')->setValue("") : $this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($fields['implementedPermanentCorrectiveActionValidatedDate']));
			}

			page::addDebug("THE G8D ID IS " . $fields['g8d'], __FILE__, __LINE__);

			if($this->complaint->getComplaintType($this->id) != "supplier_complaint")
			{
				$fields['implementedActionsEstimated'] == "0000-00-00" ?  $this->form->get('implementedActionsEstimated')->setValue("") : $this->form->get('implementedActionsEstimated')->setValue(page::transformDateForPHP($fields['implementedActionsEstimated']));
				$fields['implementedActionsImplementation'] == "0000-00-00" ?  $this->form->get('implementedActionsImplementation')->setValue("") : $this->form->get('implementedActionsImplementation')->setValue(page::transformDateForPHP($fields['implementedActionsImplementation']));
				$fields['implementedActionsEffectiveness'] == "0000-00-00" ?  $this->form->get('implementedActionsEffectiveness')->setValue("") : $this->form->get('implementedActionsEffectiveness')->setValue(page::transformDateForPHP($fields['implementedActionsEffectiveness']));
				$this->form->get("emailText")->setValue("");

				$this->form->get("processOwner2")->setValue($this->form->get("owner")->getValue());
			}


			$datasetCategoryM = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'M%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
			$rowCategoryM = mysql_fetch_array($datasetCategoryM);

			$datasetCategoryD = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'D%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
			$rowCategoryD = mysql_fetch_array($datasetCategoryD);

			$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fields8D = mysql_fetch_array($dataset8D);


			if($fields8D['g8d'] == "yes")
			{
				if($this->complaint->getComplaintType($this->id) == "supplier_complaint")
				{
					$fields['warehouseDate'] == "0000-00-00" ?  $this->form->get('warehouseDate')->setValue("") : $this->form->get('warehouseDate')->setValue(page::transformDateForPHP($fields['possibleSolutionsDate']));
				}
				else
				{
					$fields['possibleSolutionsDate'] == "0000-00-00" ?  $this->form->get('possibleSolutionsDate')->setValue("") : $this->form->get('possibleSolutionsDate')->setValue(page::transformDateForPHP($fields['possibleSolutionsDate']));
					$fields['preventiveActionsDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsDate')->setValue("") : $this->form->get('preventiveActionsDate')->setValue(page::transformDateForPHP($fields['preventiveActionsDate']));
					$fields['preventiveActionsValidationDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsValidationDate')->setValue("") : $this->form->get('preventiveActionsValidationDate')->setValue(page::transformDateForPHP($fields['preventiveActionsValidationDate']));
					$fields['preventiveActionsImplementedDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsImplementedDate')->setValue("") : $this->form->get('preventiveActionsImplementedDate')->setValue(page::transformDateForPHP($fields['preventiveActionsImplementedDate']));
					$fields['preventiveActionsEstimatedDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsEstimatedDate')->setValue("") : $this->form->get('preventiveActionsEstimatedDate')->setValue(page::transformDateForPHP($fields['preventiveActionsEstimatedDate']));
					$fields['managementSystemReviewedDate'] == "0000-00-00" ?  $this->form->get('managementSystemReviewedDate')->setValue("") : $this->form->get('managementSystemReviewedDate')->setValue(page::transformDateForPHP($fields['managementSystemReviewedDate']));
					$fields['fmeaDate'] == "0000-00-00" ?  $this->form->get('fmeaDate')->setValue("") : $this->form->get('fmeaDate')->setValue(page::transformDateForPHP($fields['fmeaDate']));
					$fields['customerSpecificationDate'] == "0000-00-00" ?  $this->form->get('customerSpecificationDate')->setValue("") : $this->form->get('customerSpecificationDate')->setValue(page::transformDateForPHP($fields['customerSpecificationDate']));
					$fields['dateSampleReceived'] == "0000-00-00" ?  $this->form->get('dateSampleReceived')->setValue("") : $this->form->get('dateSampleReceived')->setValue(page::transformDateForPHP($fields['dateSampleReceived']));
					$fields['flowChartDate'] == "0000-00-00" ?  $this->form->get('flowChartDate')->setValue("") : $this->form->get('flowChartDate')->setValue(page::transformDateForPHP($fields['flowChartDate']));
					$fields['containmentActionDate'] == "0000-00-00" ?  $this->form->get('containmentActionDate')->setValue("") : $this->form->get('containmentActionDate')->setValue(page::transformDateForPHP($fields['containmentActionDate']));
				}

			}

			$cat = $this->getCategory();

			if($this->complaint->getComplaintType($this->id) == "quality_complaint")
			{
				// do nothing ...
			}
			else
			{
				if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S" || $cat[0] == "O"))
				{
					// Return the goods
					$this->form->get("returnGoodsReadOnly")->setValue($fields['returnGoods']);
					$this->form->get("returnRequestValueReadOnly")->setValue($fields['returnRequestValue_quantity'] . " " . $fields['returnRequestValue_measurement']);
					$this->form->get("returnRequestValue")->setValue(array($fields['returnRequestValue_quantity'], $fields['returnRequestValue_measurement']));
					$this->form->get("returnRequestCommentReadOnly")->setValue($fields['returnRequestComment']);
					$this->form->get("returnRequestNameReadOnly")->setValue(usercache::getInstance()->get($fields['returnRequestName'])->getName());
					$this->form->get("returnRequestCCReadOnly")->setValue(usercache::getInstance()->get($fields['returnRequestCC'])->getName());

					$this->form->get("returnApprovalRequestReadOnly")->setValue($fields['returnApprovalRequest']);
					$this->form->get("returnApprovalRequestCommentReadOnly")->setValue($fields['returnApprovalRequestComment']);
					$this->form->get("returnApprovalRequestNameReadOnly")->setValue(usercache::getInstance()->get($fields['returnApprovalRequestName'])->getName());

					// Dipose the goods
					$this->form->get("disposeGoodsReadOnly")->setValue($fields['disposeGoods']);
					$this->form->get("returnApprovalDisposalValueReadOnly")->setValue($fields['returnApprovalDisposalValue_quantity'] . " " . $fields['returnApprovalDisposalValue_measurement']);
					$this->form->get("returnApprovalDisposalValue")->setValue(array($fields['returnApprovalDisposalValue_quantity'], $fields['returnApprovalDisposalValue_measurement']));
					$this->form->get("returnApprovalDisposalCommentReadOnly")->setValue($fields['returnApprovalDisposalComment']);
					$this->form->get("returnApprovalDisposalNameReadOnly")->setValue(usercache::getInstance()->get($fields['returnApprovalDisposalName'])->getName());

					$this->form->get("returnApprovalDisposalRequestReadOnly")->setValue($fields['returnApprovalDisposalRequest']);
					$this->form->get("returnDisposalRequestCommentReadOnly")->setValue($fields['returnDisposalRequestComment']);
					$this->form->get("returnDisposalRequestNameReadOnly")->setValue(usercache::getInstance()->get($fields['returnDisposalRequestName'])->getName());
				}
			}

			$this->form->putValuesInSession();

			$this->form->processDependencies();

			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['evaluation']);
			//unset($_SESSION['apps'][$GLOBALS['app']]);
			return false;
		}

	}

	public function save()
	{

		page::addDebug("Saving Evaluation process: ".$process,__FILE__,__LINE__);

		$this->determineStatus();

		//die();

		$originalOwner = $this->form->get("owner")->getValue();

		if ($this->loadedFromDatabase)
		{
			$this->form->get("complaintId")->setIgnore(true);

			if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
			{
				// do nothing.
			}
			elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
			{
				$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");
				$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
				$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");

				$this->form->get("containmentAction")->getValue() == "" ? $this->form->get("containmentActionyn")->setValue("No") : $this->form->get("containmentActionyn")->setValue("Yes");
				$this->form->get("possibleSolutions")->getValue() == "" ? $this->form->get("possibleSolutionsyn")->setValue("No") : $this->form->get("possibleSolutionsyn")->setValue("Yes");
				$this->form->get("preventiveActions")->getValue() == "" ? $this->form->get("preventiveActionsyn")->setValue("No") : $this->form->get("preventiveActionsyn")->setValue("Yes");
				$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
			}
			else
			{
				if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
				{
					$this->form->get("category")->setIgnore(true);
				}

				if($this->form->get("isSampleReceived")->getValue() == "NO")
				{
					$this->form->get("dateSampleReceived")->setValue("");
				}

				//if($this->form->get("fmeaReviewed")->getValue() == "" && $this->complaint->form->get("businessUnit")->getValue() == "automotive")
				//{
//					$this->complaint->getEmailNotification(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "reviewFMEA", "", $this->form->get("complaintJustified")->getValue());
				//}

				$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");

				$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
				$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");
				$this->form->get("implementedPermanentCorrectiveActionValidated")->getValue() == "" ? $this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("No") : $this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("Yes");

				//enter Yes - No if data in textareas, to help with searches
				$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaintId() . "'");
				$fields8D = mysql_fetch_array($dataset8D);

				if($fields8D['g8d'] == "yes")
				{
					$this->form->get("containmentAction")->getValue() == "" ? $this->form->get("containmentActionyn")->setValue("No") : $this->form->get("containmentActionyn")->setValue("Yes");
					$this->form->get("possibleSolutions")->getValue() == "" ? $this->form->get("possibleSolutionsyn")->setValue("No") : $this->form->get("possibleSolutionsyn")->setValue("Yes");
					$this->form->get("preventiveActions")->getValue() == "" ? $this->form->get("preventiveActionsyn")->setValue("No") : $this->form->get("preventiveActionsyn")->setValue("Yes");
				}

				//if complaint not justified clear all previously entered data in the following fields
				if($this->form->get("complaintJustified")->getValue() == "NO")
				{
					$this->form->get("teamLeader")->setValue("");
					$this->form->get("teamMember")->setValue("");
					$this->form->get("rootCauses")->setValue("");
					$this->form->get("failureCode")->setValue("");
					$this->form->get("rootCauseCode")->setValue("");
					$this->form->get("attributableProcess")->setValue("");
					$this->form->get("rootCausesAuthor")->setValue("");
					$this->form->get("rootCausesDate")->setValue("0");
					$this->form->get("returnGoods")->setValue("NO");
					$this->form->get("disposeGoods")->setValue("NO");
					$this->form->get("updateInitiator")->setValue("No");

					if($fields8D['g8d'] == "yes")
					{
						$this->form->get("containmentAction")->setValue("");
						$this->form->get("containmentActionAuthor")->setValue("");
						$this->form->get("containmentActionDate")->setValue("0");
						$this->form->get("possibleSolutions")->setValue("");
						$this->form->get("possibleSolutionsAuthor")->setValue("");
						$this->form->get("possibleSolutionsDate")->setValue("");
						$this->form->get("preventiveActions")->setValue("");
						$this->form->get("preventiveActionsAuthor")->setValue("");
						$this->form->get("preventiveActionsDate")->setValue("0");
						$this->form->get("preventiveActionsEstimatedDate")->setValue("0");
						$this->form->get("preventiveActionsImplementedDate")->setValue("0");
						$this->form->get("preventiveActionsValidationDate")->setValue("0");
					}

					$this->form->get("implementedActions")->setValue("");
					$this->form->get("implementedActionsAuthor")->setValue("");
					$this->form->get("implementedActionsDate")->setValue("0");
					$this->form->get("implementedActionsEstimated")->setValue("0");
					$this->form->get("implementedActionsImplementation")->setValue("0");
					$this->form->get("implementedActionsEffectiveness")->setValue("0");

					$this->form->get("implementedPermanentCorrectiveActionValidated")->setValue("");
					$this->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setValue("");
					$this->form->get("implementedPermanentCorrectiveActionValidatedDate")->setValue("0");

				}

				// set Complaint owner
				if($this->form->get("transferOwnership2")->getValue() == "NO")
				{
					//die("1");
					$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT owner FROM complaint WHERE id = " . $this->getComplaintId() . "");
					$fieldsComplaint = mysql_fetch_array($datasetComplaint);
					$this->form->get("owner")->setValue($fieldsComplaint['owner']);
				}
				else
				{
					//die("2");
					 $this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
				}

				if($this->form->get("isPORight")->getValue() == "NO")
				{
					//die("3");
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET owner = '" . $this->form->get("transferOwnership")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");
					$this->complaint->getEmailNotification($this->getowner(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("reasonForRejection")->getValue()), $this->form->get("complaintJustified")->getValue());
				}
				else
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . "WHERE id= '" . $this->getcomplaintId() . "'");
				}

				$this->emailInitiator();

				if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
				{
					$this->form->get("category")->setIgnore(true);
				}
				else
				{
					$cat = $this->getCategory();
					$type = $cat[0];
					if($type == "M" || $type == "D" || $type == "S")
					{
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET g8d = 'yes', category = '" . $this->form->get("category")->getValue() . "' WHERE id = '" . $this->getComplaintId() . "'");
					}
					else
					{
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");
					}
				}

				///just added for the adding of attachments
				$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/eval/" . $this->getcomplaintId() . "/");
				$this->form->get("attachment")->moveTempFileToFinal();

				if ($this->form->get("transferOwnership2")->getValue() == "YES")
				{
					$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), $this->form->get("complaintJustified")->getValue());
				}

				// Set the Evaluation Owner Request for NA Complaints Only
				$cat = $this->getCategory();

				if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
				{
					if($this->form->get("returnGoods")->getValue() == "YES" && $this->form->get("returnApprovalRequest")->getValue() != "YES")
					{
						//die("4");
						$this->form->get("owner")->setValue($this->form->get("returnRequestName")->getValue());
						$this->addLog(translate::getInstance()->translate("evaluation_return_request_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnRequestName")->getValue())->getName(), "");
					}

					if($this->form->get("returnApprovalRequest")->getValue() == "YES")
					{
						//die("5");
						$this->form->get("owner")->setValue($this->form->get("returnApprovalRequestName")->getValue());
						$this->form->get("returnApprovalRequestStatus")->setValue("1");
						$this->addLog(translate::getInstance()->translate("evaluation_return_approval_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnApprovalRequestName")->getValue())->getName(), "");
					}

					if($this->form->get("disposeGoods")->getValue() == "YES" && $this->form->get("returnApprovalDisposalRequest")->getValue() != "YES")
					{
						//die("6");
						$this->form->get("owner")->setValue($this->form->get("returnApprovalDisposalName")->getValue());
						$this->addLog(translate::getInstance()->translate("evaluation_disposal_request_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnApprovalDisposalName")->getValue())->getName(), "");
					}

					if($this->form->get("returnApprovalDisposalRequest")->getValue() == "YES")
					{
						//die("7");
						$this->form->get("owner")->setValue($this->form->get("returnDisposalRequestName")->getValue());
						$this->form->get("returnApprovalDisposalRequestStatus")->setValue("1");
						$this->addLog(translate::getInstance()->translate("evaluation_disposal_aaproval_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnDisposalRequestName")->getValue())->getName(), "");
					}

					if($this->form->get("returnApprovalDisposalRequestStatus")->getValue() == 1 && $this->form->get("returnApprovalDisposalRequestStatus")->getValue() == 1)
					{
						//die("8");
						$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
					}
				}
				else
				{
					//die("9");
					$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
				}

			}

			$this->checkEvaluationFieldsUpdated();

			// update
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation") . " WHERE complaintId= " . $this->getcomplaintId() . "");

			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");

			$this->addLog(translate::getInstance()->translate("evaluation_updated_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());

			if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
			{
				//$this->saveExternal("update", $this->getComplaintId());
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), "");
			}
			elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
			{
				///just added for the adding of attachments
				$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/eval/" . $this->getcomplaintId() . "/");
				$this->form->get("attachment")->moveTempFileToFinal();

				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), "");
			}

		}
		else
		{
			//Determine Complaint Type
			if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
			{
				$this->form->get("scapaStatus")->setValue("0");
				$this->form->get("extStatus")->setValue("0");
				$this->form->get("added")->setValue("1");
			}
			elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
			{
				$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");
				$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
				$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");

				$this->form->get("containmentAction")->getValue() == "" ? $this->form->get("containmentActionyn")->setValue("No") : $this->form->get("containmentActionyn")->setValue("Yes");
				$this->form->get("possibleSolutions")->getValue() == "" ? $this->form->get("possibleSolutionsyn")->setValue("No") : $this->form->get("possibleSolutionsyn")->setValue("Yes");
				$this->form->get("preventiveActions")->getValue() == "" ? $this->form->get("preventiveActionsyn")->setValue("No") : $this->form->get("preventiveActionsyn")->setValue("Yes");
				//die("1.0");
				$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
			}
			else
			{
				//enter Yes - No if data in textareas, to help with searches
				$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaintId() . "'");
				$fields8D = mysql_fetch_array($dataset8D);


				if($this->form->get("isSampleReceived")->getValue() == "NO")
				{
					$this->form->get("dateSampleReceived")->setValue("");
				}

				$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");
				$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
				$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");
				$this->form->get("implementedPermanentCorrectiveActionValidated")->getValue() == "" ? $this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("No") : $this->form->get("implementedPermanentCorrectiveActionValidatedyn")->setValue("Yes");

				if($fields8D['g8d'] == "yes")
				{
					$this->form->get("containmentAction")->getValue() == "" ? $this->form->get("containmentActionyn")->setValue("No") : $this->form->get("containmentActionyn")->setValue("Yes");
					$this->form->get("possibleSolutions")->getValue() == "" ? $this->form->get("possibleSolutionsyn")->setValue("No") : $this->form->get("possibleSolutionsyn")->setValue("Yes");
					$this->form->get("preventiveActions")->getValue() == "" ? $this->form->get("preventiveActionsyn")->setValue("No") : $this->form->get("preventiveActionsyn")->setValue("Yes");
				}

				//if complaint not justified clear all previously entered data in the following fields
				if($this->form->get("complaintJustified")->getValue() == "NO")
				{
					$this->form->get("teamLeader")->setValue("");
					$this->form->get("teamMember")->setValue("");
					$this->form->get("rootCauses")->setValue("");
					$this->form->get("failureCode")->setValue("");
					$this->form->get("rootCauseCode")->setValue("");
					$this->form->get("attributableProcess")->setValue("");
					$this->form->get("rootCausesAuthor")->setValue("");
					$this->form->get("rootCausesDate")->setValue("0");
					$this->form->get("returnGoods")->setValue("NO");
					$this->form->get("disposeGoods")->setValue("NO");
					$this->form->get("updateInitiator")->setValue("No");

					if($fields8D['g8d'] == "yes")
					{
						$this->form->get("containmentAction")->setValue("");
						$this->form->get("containmentActionAuthor")->setValue("");
						$this->form->get("containmentActionDate")->setValue("0");
						$this->form->get("possibleSolutions")->setValue("");
						$this->form->get("possibleSolutionsAuthor")->setValue("");
						$this->form->get("possibleSolutionsDate")->setValue("");
						$this->form->get("preventiveActions")->setValue("");
						$this->form->get("preventiveActionsAuthor")->setValue("");
						$this->form->get("preventiveActionsDate")->setValue("0");
						$this->form->get("preventiveActionsEstimatedDate")->setValue("0");
						$this->form->get("preventiveActionsImplementedDate")->setValue("0");
						$this->form->get("preventiveActionsValidationDate")->setValue("0");
					}

					$this->form->get("implementedActions")->setValue("");
					$this->form->get("implementedActionsAuthor")->setValue("");
					$this->form->get("implementedActionsDate")->setValue("0");
					$this->form->get("implementedActionsEstimated")->setValue("0");
					$this->form->get("implementedActionsImplementation")->setValue("0");
					$this->form->get("implementedActionsEffectiveness")->setValue("0");

					$this->form->get("implementedPermanentCorrectiveActionValidated")->setValue("");
					$this->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setValue("");
					$this->form->get("implementedPermanentCorrectiveActionValidatedDate")->setValue("0");
				}

				// set Complaint owner
				if($this->form->get("transferOwnership2")->getValue() == "NO")
				{
					$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT owner FROM complaint WHERE id = " . $this->getComplaintId() . "");
					$fieldsComplaint = mysql_fetch_array($datasetComplaint);
					$this->form->get("owner")->setValue(utf8_encode($fieldsComplaint['owner']));
					//die("1.1");
				}
				else
				{
					// Set the Evaluation Owner Request for NA Complaints Only
					$cat = $this->getCategory();

					if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
					{
						if($this->form->get("returnGoods")->getValue() == "YES" && $this->form->get("returnApprovalRequest")->getValue() != "YES")
						{
							//die("1.2");
							$this->form->get("owner")->setValue($this->form->get("returnRequestName")->getValue());
							$this->addLog(translate::getInstance()->translate("evaluation_return_request_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnRequestName")->getValue())->getName(), "");
						}

						elseif($this->form->get("returnApprovalRequest")->getValue() == "YES")
						{
							//die("1.3");
							$this->form->get("owner")->setValue($this->form->get("returnApprovalRequestName")->getValue());
							$this->form->get("returnApprovalRequestStatus")->setValue("1");
							$this->addLog(translate::getInstance()->translate("evaluation_return_approval_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnApprovalRequestName")->getValue())->getName(), "");
						}

						elseif($this->form->get("disposeGoods")->getValue() == "YES" && $this->form->get("returnApprovalDisposalRequest")->getValue() != "YES")
						{
							//die("1.4");
							$this->form->get("owner")->setValue($this->form->get("returnApprovalDisposalName")->getValue());
							$this->addLog(translate::getInstance()->translate("evaluation_disposal_request_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnApprovalDisposalName")->getValue())->getName(), "");
						}

						elseif($this->form->get("returnApprovalDisposalRequest")->getValue() == "YES")
						{
							//die("1.5");
							$this->form->get("owner")->setValue($this->form->get("returnDisposalRequestName")->getValue());
							$this->form->get("returnApprovalDisposalRequestStatus")->setValue("1");
							$this->addLog(translate::getInstance()->translate("evaluation_disposal_aaproval_send_to") . " - " . usercache::getInstance()->get($this->form->get("returnDisposalRequestName")->getValue())->getName(), "");
						}

						elseif($this->form->get("returnApprovalDisposalRequestStatus")->getValue() == 1 && $this->form->get("returnApprovalDisposalRequestStatus")->getValue() == 1)
						{
							//die("1.6");
							$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
						}

						else
						{
							$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
						}
					}
					else
					{
						//die("1.7");
						$this->form->get("owner")->setValue($this->form->get("processOwner2")->getValue());
					}
				}

				// begin transaction
				mysql::getInstance()->selectDatabase("complaints")->Execute("BEGIN");

				if($this->form->get("isPORight")->getValue() == "NO")
				{
					//die("1.8");
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET owner = '" . $this->form->get("transferOwnership")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");
					$this->complaint->getEmailNotification($this->getowner(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("reasonForRejection")->getValue()), $this->form->get("complaintJustified")->getValue());
					$this->form->get("owner")->setValue($this->form->get("transferOwnership")->getValue());
				}
				else
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . "WHERE id = " . $this->getcomplaintId() . "");
				}

				$this->emailInitiator();

				// Send Email
				$datasetEmail = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint ORDER BY id DESC LIMIT 1");
				$fields = mysql_fetch_array($datasetEmail);
				if ($this->form->get("transferOwnership2")->getValue() == "YES")
				{
					$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), $this->form->get("complaintJustified")->getValue());
				}

				if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
				{
					$this->form->get("category")->setIgnore(true);
				}
				else
				{
					$cat = $this->getCategory();
					$type = $cat[0];
					if($type == "M" || $type == "D" || $type == "S")
					{
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET g8d = 'yes', category = '" . $this->form->get("category")->getValue() . "' WHERE id = '" . $this->getComplaintId() . "'");
					}
					else
					{
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");
					}
				}

				/// added to save attachment
				$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/eval/" . $this->getcomplaintId() . "/");
				$this->form->get("attachment")->moveTempFileToFinal();

				// For multiple fields - CC Complaint Group
//				mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM ccGroup WHERE complaintId = " . $this->getcomplaintId());
//
//				for ($i=0; $i < $this->form->getGroup("transferOwnership2GroupYes2")->getRowCount(); $i++)
//				{
//					$this->form->getGroup("transferOwnership2GroupYes2")->setForeignKeyValue($this->getcomplaintId());
//					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO ccGroup " . $this->form->getGroup("transferOwnership2GroupYes2")->generateInsertQuery($i));
//				}

			}

			if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
			{
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), "");
			}
			elseif($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
			{
				///just added for the adding of attachments
				$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/eval/" . $this->getcomplaintId() . "/");
				$this->form->get("attachment")->moveTempFileToFinal();

				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", utf8_encode($this->form->get("emailText")->getValue()), "");
			}

			// insert

			mysql::getInstance()->selectDatabase("complaints")->Execute("COMMIT");

			/* WC EDIT */
			$doUpdate = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(complaintId) as doUpdate FROM evaluation WHERE complaintId = '" . $this->getcomplaintId() . "'");
			$doUpdateFields = mysql_fetch_array($doUpdate);
			if($doUpdateFields["doUpdate"]>0){
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation")." WHERE complaintId = '".$this->getcomplaintId()."'");
			}else{
				mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO evaluation " . $this->form->generateInsertQuery("evaluation"));
			}
			/* WC END */


			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("evaluation_completed_disposed"));
			}
			else
			{
				$this->addLog(translate::getInstance()->translate("evaluation_added_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());
			}
		}

		if($this->complaint->getComplaintType($this->getcomplaintId()) == "quality_complaint")
		{
			if($this->form->get("qu_supplierIssue")->getValue() == "Yes")
			{
				// Make a supplier form from a quality form.
				$this->makeSupplierForm();
			}

			if($this->form->get("qu_customerIssue")->getValue() == "Yes")
			{
				// Make a customer form from a quality form.
				$this->makeCustomerComplaintForm();
			}
		}

		if($this->form->generateUpdateQuery("complaint"))
		{
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") ." WHERE id = '". $this->getcomplaintId() ."'");
		}

		$this->lockComplaint($this->getcomplaintId(), "unlocked");

		//echo "owner" . $this->form->get("owner")->getValue();

		//die("end");

		page::redirect("/apps/complaints/index?id=" . $this->getcomplaintId());		//redirects the page back to the summary
	}

	public function checkEvaluationFieldsUpdated()
	{
		// Check Current Field Values
		if($this->complaint->getComplaintType($this->getcomplaintId()) != "quality_complaint")
		{
			$currentComplaintJustified = $this->form->get("complaintJustified")->getValue();
			$currentReturnGoods = $this->form->get("returnGoods")->getValue();
			$currentIsSampleReceived = $this->form->get("isSampleReceived")->getValue();
			$currentDateSampleReceived = $this->form->get("dateSampleReceived")->getValue();
		}

		$currentDisposeGoods = $this->form->get("disposeGoods")->getValue();

		if($this->complaint->getComplaintType($this->getcomplaintId()) == "supplier_complaint")
		{
			$currentUseTheGoods = $this->form->get("sp_useGoods")->getValue();
			$currentReworkGoods = $this->form->get("sp_reworkGoods")->getValue();
		}

		// Check Updated Field Values
		$checkUpdated = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintJustified`, `returnGoods`, `disposeGoods`, `sp_useGoods`, `sp_reworkGoods`, `isSampleReceived`, `dateSampleReceived` FROM evaluation WHERE complaintId = " . $this->getcomplaintId() . "");
		$fieldsUpdated = mysql_fetch_array($checkUpdated);

		$newComplaintJustified = $fieldsUpdated['complaintJustified'];
		$newReturnGoods = $fieldsUpdated['returnGoods'];
		$newDisposeGoods = $fieldsUpdated['disposeGoods'];
		$newUseTheGoods = $fieldsUpdated['sp_useGoods'];
		$newReworkGoods = $fieldsUpdated['sp_reworkGoods'];
		$newIsSampleReceived = $fieldsUpdated['isSampleReceived'];
		$newDateSampleReceived = page::transformDateForPHP($fieldsUpdated['dateSampleReceived']);

		// Compare Current and New Fields
		$updatedFields = "";

		if($this->complaint->getComplaintType($this->getcomplaintId()) != "quality_complaint")
		{
			if($currentComplaintJustified != $newComplaintJustified)
			{
				$updatedFields .= "Complaint Justified: Old(" . $newComplaintJustified . ") New(" . $currentComplaintJustified . ") - ";
			}

			if($currentReturnGoods != $newReturnGoods)
			{
				$updatedFields .= "Return Goods: Old(" . $newReturnGoods . ") New(" . $currentReturnGoods . ") - ";
			}

			if($currentIsSampleReceived != $newIsSampleReceived)
			{
				$updatedFields .= "Is Sample Received: Old(" . $newIsSampleReceived . ") New(" . $currentIsSampleReceived . ") - ";
			}

			if($currentDateSampleReceived != $newDateSampleReceived)
			{
				$updatedFields .= "Date Sample Received: Old(" . page::transformDateForPHP($newDateSampleReceived) . ") New(" . $currentDateSampleReceived . ") - ";
			}
		}

		if($currentDisposeGoods != $newDisposeGoods)
		{
			$updatedFields .= "Dispose Goods: Old(" . $newDisposeGoods . ") New(" . $currentDisposeGoods . ") - ";
		}

		if($currentUseTheGoods != $newUseTheGoods)
		{
			$updatedFields .= "Use Goods: Old(" . $newUseTheGoods . ") New(" . $currentUseTheGoods . ") - ";
		}

		if($currentReworkGoods != $newReworkGoods)
		{
			$updatedFields .= "Rework Goods: Old(" . $newReworkGoods . ") New(" . $currentReworkGoods . ") - ";
		}

		if($updatedFields)
		{
			$this->addLog(translate::getInstance()->translate("evaluation_fields_have_been_updated") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", substr_replace($updatedFields ,"",-2));
		}
	}

	public function makeSupplierForm()
	{
		// Create a Supplier Form from a Quality Complaint (Supplier Issue = Yes)
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET email_text = '', typeOfComplaint = 'supplier_complaint', originalStateComplaint = 'quality_complaint', g8d = 'yes', sp_materialInvolved = 'Yes', sp_materialBlocked = '" . $this->complaint->form->get("qu_materialBlocked")->getValue() . "', sp_materialBlockedDate = '" . page::transformDateForMYSQL($this->complaint->form->get("qu_materialBlockedDate")->getValue()) . "', sp_materialBlockedName = '" . $this->complaint->form->get("qu_materialBlockedName")->getValue() . "', sp_sampleSent = 'No', sp_submitToExtSupplier = 'No', addSAPEmailAddress = 'no', sp_siteConcerned = '" . $this->complaint->form->get("sp_siteConcerned")->getValue() . "', sp_submitToFinance = 'No' WHERE id = " . $this->getcomplaintId() . "");

		// Add Internal Bits which do not want to be deleted to the complaints table under the internal_ fields and show in comments.
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET internal_fields = '1', internal_teamLeader = '" . $this->form->get("teamLeader")->getValue() . "', internal_teamMember = '" . $this->form->get("teamMember")->getValue() . "', internal_qu_stockVerificationMade = '" . $this->form->get("qu_verificationMade")->getValue() . "', internal_qu_stockVerificationName = '" . $this->form->get("qu_verificationName")->getValue() . "', internal_qu_stockVerificationDate = '" . page::transformDateForMYSQL($this->form->get("qu_verificationDate")->getValue()) . "', internal_qu_otherMaterialEffected = '" . $this->form->get("qu_otherMaterialEffected")->getValue() . "', internal_qu_otherMatDetails = '" . $this->form->get("qu_otherMatDetails")->getValue() . "', internal_analysis = '" . $this->form->get("analysis")->getValue() . "', internal_author = '" . $this->form->get("author")->getValue() . "', internal_analysisDate = '" . page::transformDateForMYSQL($this->form->get("analysisDate")->getValue()) . "', internal_additionalComments = '" . $this->form->get("additionalComments")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");

		// Add the fact this has been done in the log.
		$this->addLog(translate::getInstance()->translate("supplier_complaint_created_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());

		$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), $this->getcomplaintId(), "turnInternalIntoSupplierComplaint", $this->form->get("emailText")->getValue(), "");

		// Redirect to the complaint section with supplier form loaded and delete the evaluation ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `evaluation` WHERE complaintId = '".$this->getcomplaintId()."'");
		page::redirect("/apps/complaints/resume?complaint=" . $this->getcomplaintId() . "&status=complaint");
	}

	public function makeCustomerComplaintForm()
	{
		// Create a Customer Form from a Quality Complaint (Cusgtomer Issue = Yes)
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET awaitingInvoice = 'no', interco = 'no', awaitingDimensions = 'no', factoredProduct = 'No', awaitingQuantityUnderComplaint = 'no', awaitingBatchNumber = 'No', creditNoteRequested = 'NO', sampleReceived = 'No', email_text = '', typeOfComplaint = 'customer_complaint', originalStateComplaint = 'quality_complaint', g8d = 'yes', sp_materialInvolved = 'Yes', sp_materialBlocked = '" . $this->complaint->form->get("qu_materialBlocked")->getValue() . "', sp_materialBlockedDate = '" . page::transformDateForMYSQL($this->complaint->form->get("qu_materialBlockedDate")->getValue()) . "', sp_materialBlockedName = '" . $this->complaint->form->get("qu_materialBlockedName")->getValue() . "', sp_sampleSent = 'No', sp_submitToExtSupplier = 'No', addSAPEmailAddress = 'no', sp_siteConcerned = '" . $this->complaint->form->get("sp_siteConcerned")->getValue() . "', sp_submitToFinance = 'No', siteAtOrigin = '" . $this->complaint->form->get("whereErrorOccured")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");

		// Add Internal Bits which do not want to be deleted to the complaints table under the internal_ fields and show in comments.
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET internal_fields = '1', internal_teamLeader = '" . $this->form->get("teamLeader")->getValue() . "', internal_teamMember = '" . $this->form->get("teamMember")->getValue() . "', internal_qu_stockVerificationMade = '" . $this->form->get("qu_verificationMade")->getValue() . "', internal_qu_stockVerificationName = '" . $this->form->get("qu_verificationName")->getValue() . "', internal_qu_stockVerificationDate = '" . page::transformDateForMYSQL($this->form->get("qu_verificationDate")->getValue()) . "', internal_qu_otherMaterialEffected = '" . $this->form->get("qu_otherMaterialEffected")->getValue() . "', internal_qu_otherMatDetails = '" . $this->form->get("qu_otherMatDetails")->getValue() . "', internal_analysis = '" . $this->form->get("analysis")->getValue() . "', internal_author = '" . $this->form->get("author")->getValue() . "', internal_analysisDate = '" . page::transformDateForMYSQL($this->form->get("analysisDate")->getValue()) . "', internal_additionalComments = '" . $this->form->get("additionalComments")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");

		// Add the fact this has been done in the log.
		$this->addLog(translate::getInstance()->translate("customer_complaint_created_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());

		$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), $this->getcomplaintId(), "turnInternalIntoCustomerComplaint", $this->form->get("emailText")->getValue(), "");

		// Redirect to the complaint section with supplier form loaded and delete the evaluation ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `evaluation` WHERE complaintId = '".$this->getcomplaintId()."'");
		page::redirect("/apps/complaints/resume?complaint=" . $this->getcomplaintId() . "&status=complaint");
	}

	public function getSupplierEmail($supplierNumber)
	{
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM `supplier` WHERE `id` = '" . $supplierNumber ."'");
		$fields = mysql_fetch_array($dataset);

		//$emailAddress = $fields['emailAddress'];
		//$emailAddress = sapcache::getInstance()->get($supplierNumber)->getEmail();

		return $fields['emailAddress'];
	}

	public function saveExternal($option, $id)
	{
		switch ($option)
		{
			case 'insert':

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal " . $this->form->generateUpdateQueryExt("evaluationExt") . " WHERE id = " . $id . "");

				break;

			case 'update':

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal " . $this->form->generateUpdateQueryExt("evaluationExt") . " WHERE id = " . $id . "");

				break;

			default:

				// do nothing ...

				break;
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

	}

	public function getComplaintCategory()
	{
		$datasetCategoryM = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'M%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
		$rowCategoryM = mysql_fetch_array($datasetCategoryM);
		$datasetCategoryD = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'D%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
		$rowCategoryD = mysql_fetch_array($datasetCategoryD);
	}

	public function defineForm()
	{
		/* WC AE - 28/01/08 */
		$savedFields = array();
		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		else
		{
			if(isset($_GET["print"]) && !isset($_REQUEST["printAll"]))
			{//this means we are coming from the print function defined on homepage

				//unset($_SESSION['apps'][$GLOBALS['app']]['evaluation']);

				$retArray = array();

				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation LEFT JOIN complaint ON evaluation.complaintId=complaint.id WHERE complaintId = '"  . $_REQUEST['complaint'] ."'");

				$fields2 = mysql_fetch_array($dataset);
				if($fields2){
					foreach ($fields2 as $key => $value)
					{
						if($value){
							if(!strtotime($value) && $value != "0000-00-00"){
								$savedFields[$key] = $value;
							}else if(strtotime($value) && $value != "0000-00-00"){//if it is a date field then chenge the layout
								$savedFields[$key] = page::transformDateForPHP($value);
							}

						}
					}
				}
			}
		}


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
		$this->form = new form("evaluation" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$isPORightNo = new group("isPORightNo");
		//$transferOwnership2Group = new group("transferOwnership2Group");
		$sampleReceivedGroup = new group("sampleReceivedGroup");
		$sampleReceivedGroup->setBorder(false);
		$isSampleReceivedYes = new group("isSampleReceivedYes");
		$sampleReceivedGroupAfter = new group("sampleReceivedGroupAfter");
		$sampleReceivedGroupAfter->setBorder(false);
		$isComplaintCatRightNo = new group("isComplaintCatRightNo");
		$complaintJustifiedGroup = new group("complaintJustifiedGroup");
		$complaintJustifiedGroup->setBorder(false);
		$complaintJustifiedYes = new group("complaintJustifiedYes");
		$complaintJustifiedYes->setBorder(false);
		$returnGoodsYes =  new group("returnGoodsYes");
		$returnGoodsYes->setBorder(false);
		$returnGoodsApprovalGroup =  new group("returnGoodsApprovalGroup");
		$returnGoodsApprovalGroup->setBorder(false);
		$returnGoodsApprovalGroupYes =  new group("returnGoodsApprovalGroupYes");
		$returnGoodsApprovalGroupYes->setBorder(false);
		$complaintJustifiedYes2 =  new group("complaintJustifiedYes2");
		$complaintJustifiedYes2->setBorder(false);
		$disposeGoodsYes = new group("disposeGoodsYes");
		$disposeGoodsYes->setBorder(false);
		$disposeGoodsApprovalGroup = new group("disposeGoodsApprovalGroup");
		$disposeGoodsApprovalGroup->setBorder(false);
		$disposeGoodsApprovalGroupYes = new group("disposeGoodsApprovalGroupYes");
		$disposeGoodsApprovalGroupYes->setBorder(false);
		$complaintJustifiedYes3 = new group("complaintJustifiedYes3");
		$complaintJustifiedYes3->setBorder(false);
		$managementSystemGroup = new group("managementSystemGroup");
		$managementSystemRefYes = new group("managementSystemRefYes");
		$fmeaGroup = new group("fmeaGroup");
		$fmeaDepGroup = new group("fmeaDepGroup");
		$fmeaReviewedGroup = new group("fmeaReviewedGroup");
		$fmeaReviewedGroup->setBorder(false);
		$customerSpecificationGroup = new group("customerSpecificationGroup");
		$customerSpecificationGroupYes = new group("customerSpecificationGroupYes");
		$flowChartGroup = new group("flowChartGroup");
		$flowChartGroupYes = new group("flowChartGroupYes");
		$commentsGroup = new group("commentsGroup");
		$transferOwnership2Group = new group("transferOwnership2Group");
		$transferOwnership2Group->setborder(false);
		$transferOwnership2GroupYes = new group("transferOwnership2GroupYes");
		$transferOwnership2GroupYes->setborder(false);

//		$transferOwnership2GroupYes2 = new multiplegroup("transferOwnership2GroupYes2");
//		$transferOwnership2GroupYes2->setTitle("Select someone to CC the below message to");
//		$transferOwnership2GroupYes2->setNextAction("evaluation");
//		$transferOwnership2GroupYes2->setAnchorRef("copy_to");
//		$transferOwnership2GroupYes2->setTable("ccGroup");
//		$transferOwnership2GroupYes2->setForeignKey("complaintId");
//		$transferOwnership2GroupYes2->setBorder(false);

		$transferOwnership2GroupYes3 = new group("transferOwnership2GroupYes3");
		$transferOwnership2GroupYes3->setborder(false);

		$submitGroup = new group("submitGroup");

		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("evaluation");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
		$status->setValue($savedFields["status"]);
		else $status->setValue("conclusion");
		$status->setTable("evaluation");
		$status->setVisible(false);
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		{
			$owner->setValue($savedFields["owner"]);
		}
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);


		$isPORight = new radio("isPORight");
		$isPORight->setGroup("initiation");
		$isPORight->setDataType("string");
		$isPORight->setLength(5);
		$isPORight->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$isPORight->setRowTitle("is_process_owner_right");
		$isPORight->setLabel("Evaluation Details");
		$isPORight->setRequired(true);
		if(isset($savedFields["isPORight"]))
		$isPORight->setValue($savedFields["isPORight"]);
		else $isPORight->setValue("YES");
		$isPORight->setTable("evaluation");
		$isPORight->setHelpId(9000);

		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"])){//this means we are coming from the print function defined on homepage

			$showID = new textbox("showID");
			$showID->setValue($this->complaint->getId());
			$showID->setRowTitle("complaint_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("evaluation");
			$initiation->add($showID);

			$showSAP = new textbox("showSAP");
			$showSAP->setValue($fields2["sapCustomerNumber"]);
			$showSAP->setRowTitle("SAP_customer_number");
			$showSAP->setGroup("initiation");
			$showSAP->setDataType("string");
			$showSAP->setLength(50);
			$showSAP->setRequired(false);
			$showSAP->setTable("evaluation");
			$initiation->add($showSAP);

			$showSAPName = new textbox("showSAPName");
			$showSAPName->setValue($fields2["sapName"]);
			$showSAPName->setRowTitle("SAP_customer_name");
			$showSAPName->setGroup("initiation");
			$showSAPName->setDataType("string");
			$showSAPName->setLength(50);
			$showSAPName->setRequired(false);
			$showSAPName->setTable("evaluation");
			$initiation->add($showSAPName);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
			$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("evaluation");
			$initiation->add($showOpenDate);
		}

		// Dependency
		$isPORight_dependency = new dependency();
		$isPORight_dependency->addRule(new rule('initiation', 'isPORight', 'NO'));
		$isPORight_dependency->setGroup('isPORightNo');
		$isPORight_dependency->setShow(true);

		$isPORight->addControllingDependency($isPORight_dependency);
		$initiation->add($isPORight);

		$reasonForRejection = new textarea("reasonForRejection");
		if(isset($savedFields["reasonForRejection"]))
		$reasonForRejection->setValue($savedFields["reasonForRejection"]);
		$reasonForRejection->setGroup("isPORightNo");
		$reasonForRejection->setDataType("text");
		$reasonForRejection->setRowTitle("reason_for_rejection");
		$reasonForRejection->setRequired(false);
		$reasonForRejection->setTable("evaluation");
		$reasonForRejection->setHelpId(9001);
		$isPORightNo->add($reasonForRejection);


		$transferOwnership = new dropdown("transferOwnership");
		if(isset($savedFields["transferOwnership"]))
		$transferOwnership->setValue($savedFields["transferOwnership"]);
		$transferOwnership->setGroup("actionsGroup");
		$transferOwnership->setDataType("string");
		$transferOwnership->setRowTitle("send_to");
		$transferOwnership->setRequired(false);
		$transferOwnership->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$transferOwnership->setTable("evaluation");
		$transferOwnership->clearData();
		$transferOwnership->setHelpId(9002);
		$isPORightNo->add($transferOwnership);

		$submit = new submit("submit");
		$submit->setGroup("isPORightNo");
		$submit->setVisible(true);
		$isPORightNo->add($submit);

		/*This transfer ownership affects what is seen at the bottom of the page,
		so the actual dependant fields are at the bottom of the form*/
		$transferOwnership2 = new radio("transferOwnership2");
		$transferOwnership2->setGroup("transferOwnership2Group");
		$transferOwnership2->setDataType("string");
		$transferOwnership2->setLength(5);
		$transferOwnership2->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$transferOwnership2->setRowTitle("transfer_ownership");
		$transferOwnership2->setRequired(false);
		if(isset($savedFields["transferOwnership2"]))
		$transferOwnership2->setValue($savedFields["transferOwnership2"]);
		else $transferOwnership2->setValue("YES");
		$transferOwnership2->setTable("evaluation");
		$transferOwnership2->setHelpId(9019);
		//$commentsGroup->add($transferOwnership2);

		//dependancy
		$transferOwnership2_dependency = new dependency();
		$transferOwnership2_dependency->addRule(new rule('transferOwnership2Group', 'transferOwnership2', 'YES'));
		$transferOwnership2_dependency->setGroup('transferOwnership2GroupYes');
		$transferOwnership2_dependency->setShow(true);

		$transferOwnership2->addControllingDependency($transferOwnership2_dependency);
		$transferOwnership2Group->add($transferOwnership2);
		//dependants to be found at the bottom of the form

		$isSampleReceived = new radio("isSampleReceived");
		$isSampleReceived->setGroup("sampleReceivedGroup");
		$isSampleReceived->setDataType("string");
		$isSampleReceived->setLength(5);
		$isSampleReceived->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$isSampleReceived->setRowTitle("is_sample_received");
		$isSampleReceived->setRequired(true);
		if(isset($savedFields["isSampleReceived"]))
		$isSampleReceived->setValue($savedFields["isSampleReceived"]);
		else $isSampleReceived->setValue("NO");
		$isSampleReceived->setTable("evaluation");
		$isSampleReceived->setHelpId(9003);


		// Dependency
		$isSampleReceived_dependency = new dependency();
		$isSampleReceived_dependency->addRule(new rule('sampleReceivedGroup', 'isSampleReceived', 'YES'));
		$isSampleReceived_dependency->setGroup('isSampleReceivedYes');
		$isSampleReceived_dependency->setShow(true);

		$isSampleReceived->addControllingDependency($isSampleReceived_dependency);
		$sampleReceivedGroup->add($isSampleReceived);

		$dateSampleReceived = new calendar("dateSampleReceived");
		if(isset($savedFields["dateSampleReceived"]))
		$dateSampleReceived->setValue($savedFields["dateSampleReceived"]);
		$dateSampleReceived->setGroup("isSampleReceivedYes");
		$dateSampleReceived->setDataType("date");
		$dateSampleReceived->setErrorMessage("textbox_date_error");
		$dateSampleReceived->setLength(30);
		$dateSampleReceived->setRowTitle("date_sample_received");
		$dateSampleReceived->setRequired(false);
		$dateSampleReceived->setTable("evaluation");
		$dateSampleReceived->setHelpId(9004);
		$isSampleReceivedYes->add($dateSampleReceived);

		//		$g8d = new radio("g8d");
		//		$g8d->setGroup("sampleReceivedGroupAfter");
		//		$g8d->setDataType("string");
		//		$g8d->setLength(5);
		//		$g8d->setArraySource(array(
		//			array('value' => 'yes', 'display' => 'Yes'),
		//			array('value' => 'no', 'display' => 'No')
		//		));
		//		$g8d->setRowTitle("full_8d_required");
		//		$g8d->setRequired(true);
		//		$g8d->setValue("no");
		//		$g8d->setTable("evaluation");
		//		$g8d->setHelpId(8006);
		//		$sampleReceivedGroupAfter->add($g8d);


		$analysis = new textarea("analysis");
		if(isset($savedFields["analysis"]))
		$analysis->setValue($savedFields["analysis"]);
		//else
		//$analysis->setValue("ALLY");
		$analysis->setGroup("sampleReceivedGroupAfter");
		$analysis->setDataType("text");
		$analysis->setRowTitle("analysis");
		$analysis->setRequired(false);
		$analysis->setTable("evaluation");
		$analysis->setHelpId(9005);
		$sampleReceivedGroupAfter->add($analysis);

		$analysisyn = new radio("analysisyn");
		$analysisyn->setGroup("sampleReceivedGroupAfter");
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
		$analysisyn->setTable("complaint");
		$sampleReceivedGroupAfter->add($analysisyn);

		$author = new textbox("author");
		if(isset($savedFields["author"]))
		$author->setValue($savedFields["author"]);
		$author->setGroup("sampleReceivedGroupAfter");
		$author->setDataType("string");
		$author->setLength(255);
		$author->setRowTitle("author");
		$author->setRequired(false);
		$author->setTable("evaluation");
		$author->setHelpId(9006);
		$sampleReceivedGroupAfter->add($author);

		$analysisDate = new calendar("analysisDate");
		if(isset($savedFields["analysisDate"]))
		$analysisDate->setValue($savedFields["analysisDate"]);
		$analysisDate->setGroup("sampleReceivedGroupAfter");
		$analysisDate->setDataType("date");
		$analysisDate->setLength(30);
		$analysisDate->setErrorMessage("textbox_date_error");
		$analysisDate->setRowTitle("analysis_date");
		$analysisDate->setRequired(false);
		$analysisDate->setTable("evaluation");//was complaintEval???
		$analysisDate->setHelpId(9007);
		$sampleReceivedGroupAfter->add($analysisDate);

		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments/eval");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(9008);
		$attachment->setNextAction("evaluation");
		$sampleReceivedGroupAfter->add($attachment);

		$isComplaintCatRight = new radio("isComplaintCatRight");
		$isComplaintCatRight->setGroup("sampleReceivedGroupAfter");
		$isComplaintCatRight->setDataType("string");
		$isComplaintCatRight->setLength(5);
		$isComplaintCatRight->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$isComplaintCatRight->setRowTitle("is_complaint_cat_right");
		$isComplaintCatRight->setRequired(true);
		if(isset($savedFields["isComplaintCatRight"]))
		$isComplaintCatRight->setValue($savedFields["isComplaintCatRight"]);
		else $isComplaintCatRight->setValue("Yes");
		$isComplaintCatRight->setTable("evaluation");
		$isComplaintCatRight->setHelpId(9009);


		// Dependency
		$isComplaintCatRight_dependency = new dependency();
		$isComplaintCatRight_dependency->addRule(new rule('sampleReceivedGroupAfter', 'isComplaintCatRight', 'No'));
		$isComplaintCatRight_dependency->setGroup('isComplaintCatRightNo');
		$isComplaintCatRight_dependency->setShow(true);

		$isComplaintCatRight->addControllingDependency($isComplaintCatRight_dependency);
		$sampleReceivedGroupAfter->add($isComplaintCatRight);

		$datasetCat = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
		$fieldsCat = mysql_fetch_array($datasetCat);

		$category = new dropdown("category");
		$category->setGroup("isComplaintCatRightNo");
		$category->setDataType("string");
		$category->setLength(50);
		$category->setRowTitle("correct_category");
		$category->setRequired(false);
		if(isset($savedFields["category"]))
		$category->setValue($savedFields["category"]);
		else $category->setValue($fieldsCat['category']);
		//$category->setXMLSource("./apps/complaints/xml/category.xml");
		$category->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'customer' AND field = 'category' ORDER BY `details` ASC");
		$category->setTranslate(true);
		$category->setTable("complaint");
		$category->setHelpId(9010);
		$isComplaintCatRightNo->add($category);



		$complaintJustified = new radio("complaintJustified");
		$complaintJustified->setGroup("complaintJustifiedGroup");
		$complaintJustified->setDataType("string");
		$complaintJustified->setLength(5);
		$complaintJustified->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No'),
		array('value' => 'undecided', 'display' => 'Undecided')
		));
		$complaintJustified->setRowTitle("complaint_justified");
		$complaintJustified->setRequired(true);
		if(isset($savedFields["complaintJustified"]))
		$complaintJustified->setValue($savedFields["complaintJustified"]);
		else $complaintJustified->setValue("YES");
		$complaintJustified->setTable("evaluation");
		$complaintJustified->setHelpId(9011);


		// Dependency
		$complaintJustified_dependency = new dependency();
		$complaintJustified_dependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 'YES'));
		$complaintJustified_dependency->setGroup('complaintJustifiedYes');
		$complaintJustified_dependency->setShow(true);

		$complaintJustified2_dependency = new dependency();
		$complaintJustified2_dependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 'YES'));
		$complaintJustified2_dependency->setGroup('complaintJustifiedYes2');
		$complaintJustified2_dependency->setShow(true);

		$complaintJustified3_dependency = new dependency();
		$complaintJustified3_dependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 'YES'));
		$complaintJustified3_dependency->setGroup('complaintJustifiedYes3');
		$complaintJustified3_dependency->setShow(true);

		$complaintJustified->addControllingDependency($complaintJustified_dependency);
		$complaintJustified->addControllingDependency($complaintJustified2_dependency);
		$complaintJustified->addControllingDependency($complaintJustified3_dependency);
		$complaintJustifiedGroup->add($complaintJustified);


		$teamLeader = new textbox("teamLeader");
		if(isset($savedFields["teamLeader"]))
		$teamLeader->setValue($savedFields["teamLeader"]);
		$teamLeader->setGroup("complaintJustifiedYes");
		$teamLeader->setDataType("string");
		$teamLeader->setLength(255);
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setRequired(false);
		$teamLeader->setTable("evaluation");
		$teamLeader->setHelpId(9012);
		$complaintJustifiedYes->add($teamLeader);

		$teamMember = new textarea("teamMember");
		if(isset($savedFields["teamMember"]))
		$teamMember->setValue($savedFields["teamMember"]);
		$teamMember->setGroup("complaintJustifiedYes");
		$teamMember->setDataType("text");
		$teamMember->setRowTitle("team_member");
		$teamMember->setRequired(false);
		$teamMember->setTable("evaluation");
		$teamMember->setHelpId(9013);
		$complaintJustifiedYes->add($teamMember);

		$rootCauses = new textarea("rootCauses");
		if(isset($savedFields["rootCauses"]))
		$rootCauses->setValue($savedFields["rootCauses"]);
		$rootCauses->setGroup("complaintJustifiedYes");
		$rootCauses->setDataType("text");
		$rootCauses->setRowTitle("root_causes");
		$rootCauses->setRequired(false);
		$rootCauses->setTable("evaluation");
		$rootCauses->setHelpId(9014);
		$complaintJustifiedYes->add($rootCauses);

		$rootCausesyn = new radio("rootCausesyn");
		$rootCausesyn->setGroup("complaintJustifiedYes");
		$rootCausesyn->setDataType("string");
		$rootCausesyn->setLength(3);
		$rootCausesyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$rootCausesyn->setRowTitle("rootCauses_entered");
		$rootCausesyn->setRequired(false);
		$rootCausesyn->setVisible(false);
		if(isset($savedFields["rootCausesyn"]))
		$rootCausesyn->setValue($savedFields["rootCausesyn"]);
		else $rootCausesyn->setValue("No");
		$rootCausesyn->setTable("complaint");
		$complaintJustifiedYes->add($rootCausesyn);


		if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
		{
			// do nothing
		}
		else
		{
			if($this->complaint->form->get("siteAtOrigin")->getValue() == 'Ashton' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Mannheim' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Rorschach' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Carlstadt' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Inglewood' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Renfrew' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Syracuse' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Windsor')
			{
				if($this->complaint->form->get("siteAtOrigin")->getValue() == 'Carlstadt' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Inglewood' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Renfrew' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Syracuse' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Windsor')
				{
					$failureCode = new dropdown("failureCode");
					$failureCode->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'failureCode' ORDER BY `details`");
					if(isset($savedFields["failureCode"]))
					$failureCode->setValue($savedFields["failureCode"]);
					$failureCode->setGroup("complaintJustifiedYes");
					$failureCode->setDataType("string");
					$failureCode->setLength(255);
					$failureCode->setRowTitle("failure_code");
					$failureCode->setRequired(true);
					$failureCode->setTable("evaluation");
					$failureCode->setHelpId(9037);
					$complaintJustifiedYes->add($failureCode);
				}
				else
				{
					$failureCode = new dropdown("failureCode");
					$failureCode->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'failureCode' ORDER BY `details`");
					if(isset($savedFields["failureCode"]))
					$failureCode->setValue($savedFields["failureCode"]);
					$failureCode->setGroup("complaintJustifiedYes");
					$failureCode->setDataType("string");
					$failureCode->setLength(255);
					$failureCode->setRowTitle("failure_code");
					$failureCode->setRequired(false);
					$failureCode->setTable("evaluation");
					$failureCode->setHelpId(9037);
					$complaintJustifiedYes->add($failureCode);
				}
			}
			else
			{
				$failureCode = new textbox("failureCode");
				if(isset($savedFields["failureCode"]))
				$failureCode->setValue($savedFields["failureCode"]);
				$failureCode->setGroup("complaintJustifiedYes");
				$failureCode->setDataType("string");
				$failureCode->setLength(255);
				$failureCode->setRowTitle("failure_code");
				$failureCode->setRequired(false);
				$failureCode->setTable("evaluation");
				$failureCode->setHelpId(9037);
				$complaintJustifiedYes->add($failureCode);
			}

			//$failureCode = new textbox("failureCode");
			//$failureCode = new dropdown("failureCode");

		}

		if($this->complaint->form->get("siteAtOrigin")->getValue() == 'Ashton' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Mannheim' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Rorschach' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Carlstadt' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Inglewood' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Renfrew' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Syracuse' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Windsor')
		{
			if($this->complaint->form->get("siteAtOrigin")->getValue() == 'Carlstadt' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Inglewood' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Renfrew' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Syracuse' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Windsor')
			{
				$rootCauseCode = new dropdown("rootCauseCode");
				$rootCauseCode->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'rootCauseCode' ORDER BY `details`");
				if(isset($savedFields["rootCauseCode"]))
				$rootCauseCode->setValue($savedFields["rootCauseCode"]);
				$rootCauseCode->setGroup("complaintJustifiedYes");
				$rootCauseCode->setDataType("string");
				$rootCauseCode->setLength(255);
				$rootCauseCode->setRowTitle("root_cause_code");
				$rootCauseCode->setRequired(true);
				$rootCauseCode->setTable("evaluation");
				$rootCauseCode->setHelpId(9038);
				$complaintJustifiedYes->add($rootCauseCode);
			}
			else
			{
				$rootCauseCode = new dropdown("rootCauseCode");
				$rootCauseCode->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'rootCauseCode' ORDER BY `details`");
				if(isset($savedFields["rootCauseCode"]))
				$rootCauseCode->setValue($savedFields["rootCauseCode"]);
				$rootCauseCode->setGroup("complaintJustifiedYes");
				$rootCauseCode->setDataType("string");
				$rootCauseCode->setLength(255);
				$rootCauseCode->setRowTitle("root_cause_code");
				$rootCauseCode->setRequired(false);
				$rootCauseCode->setTable("evaluation");
				$rootCauseCode->setHelpId(9038);
				$complaintJustifiedYes->add($rootCauseCode);
			}
		}
		else
		{
			$rootCauseCode = new textbox("rootCauseCode");
			if(isset($savedFields["rootCauseCode"]))
			$rootCauseCode->setValue($savedFields["rootCauseCode"]);
			$rootCauseCode->setGroup("complaintJustifiedYes");
			$rootCauseCode->setDataType("string");
			$rootCauseCode->setLength(255);
			$rootCauseCode->setRowTitle("root_cause_code");
			$rootCauseCode->setRequired(false);
			$rootCauseCode->setTable("evaluation");
			$rootCauseCode->setHelpId(9038);
			$complaintJustifiedYes->add($rootCauseCode);
		}
		//$rootCauseCode = new textbox("rootCauseCode");
		//$rootCauseCode = new dropdown("rootCauseCode");



		$site = $this->complaint->form->get("siteAtOrigin")->getValue();
		if($site == 'Ashton' || $site == 'Bellegarde' || $site == 'Carlstadt' || $site == 'Dunstable' || $site == 'Ghislarengo' || $site == 'Mannheim' || $site == 'Inglewood' || $site == 'Renfrew' || $site == 'Rorschach' || $site == 'Syracuse' || $site == 'Valence' || $site == 'Windsor')
		{
			if($this->complaint->form->get("siteAtOrigin")->getValue() == 'Carlstadt' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Inglewood' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Renfrew' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Syracuse' || $this->complaint->form->get("siteAtOrigin")->getValue() == 'Windsor')
			{
				$attributableProcess = new dropdown("attributableProcess");
				$attributableProcess->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'attributableProcess' ORDER BY `details`");
				if(isset($savedFields["attributableProcess"]))
				$attributableProcess->setValue($savedFields["attributableProcess"]);
				$attributableProcess->setGroup("complaintJustifiedYes");
				$attributableProcess->setDataType("string");
				$attributableProcess->setLength(255);
				$attributableProcess->setRowTitle("attributable_process");
				$attributableProcess->setRequired(true);
				//$this->complaint->form->get("siteAtOrigin")->getValue() == 'Rorschach' ?	$attributableProcess->setXMLSource("./apps/complaints/xml/attributableProcessRorschach.xml") : '';
				$attributableProcess->setTable("evaluation");
				$attributableProcess->setHelpId(9015);
				$complaintJustifiedYes->add($attributableProcess);
			}
			else
			{
				$attributableProcess = new dropdown("attributableProcess");
				$attributableProcess->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE `site` = '". $this->complaint->form->get("siteAtOrigin")->getValue() ."' AND field = 'attributableProcess' ORDER BY `details`");
				if(isset($savedFields["attributableProcess"]))
				$attributableProcess->setValue($savedFields["attributableProcess"]);
				$attributableProcess->setGroup("complaintJustifiedYes");
				$attributableProcess->setDataType("string");
				$attributableProcess->setLength(255);
				$attributableProcess->setRowTitle("attributable_process");
				$attributableProcess->setRequired(false);
				//$this->complaint->form->get("siteAtOrigin")->getValue() == 'Rorschach' ?	$attributableProcess->setXMLSource("./apps/complaints/xml/attributableProcessRorschach.xml") : '';
				$attributableProcess->setTable("evaluation");
				$attributableProcess->setHelpId(9015);
				$complaintJustifiedYes->add($attributableProcess);
			}
		}
		else
		{
			$attributableProcess = new textbox("attributableProcess");
			if(isset($savedFields["attributableProcess"]))
			$attributableProcess->setValue($savedFields["attributableProcess"]);
			$attributableProcess->setGroup("complaintJustifiedYes");
			$attributableProcess->setDataType("string");
			$attributableProcess->setLength(255);
			$attributableProcess->setRowTitle("attributable_process");
			$attributableProcess->setRequired(false);
			//$this->complaint->form->get("siteAtOrigin")->getValue() == 'Rorschach' ?	$attributableProcess->setXMLSource("./apps/complaints/xml/attributableProcessRorschach.xml") : '';
			$attributableProcess->setTable("evaluation");
			$attributableProcess->setHelpId(9015);
			$complaintJustifiedYes->add($attributableProcess);
		}

		$rootCausesAuthor = new textbox("rootCausesAuthor");
		if(isset($savedFields["rootCausesAuthor"]))
		$rootCausesAuthor->setValue($savedFields["rootCausesAuthor"]);
		$rootCausesAuthor->setGroup("complaintJustifiedYes");
		$rootCausesAuthor->setDataType("string");
		$rootCausesAuthor->setLength(255);
		$rootCausesAuthor->setRowTitle("root_causes_author");
		$rootCausesAuthor->setRequired(false);
		$rootCausesAuthor->setTable("evaluation");
		$rootCausesAuthor->setHelpId(901523);
		$complaintJustifiedYes->add($rootCausesAuthor);

		$rootCausesDate = new calendar("rootCausesDate");
		if(isset($savedFields["rootCausesDate"]))
		$rootCausesDate->setValue($savedFields["rootCausesDate"]);
		$rootCausesDate->setGroup("complaintJustifiedYes");
		$rootCausesDate->setDataType("date");
		$rootCausesDate->setRowTitle("root_causes_date");
		$rootCausesDate->setErrorMessage("textbox_date_error");
		$rootCausesDate->setRequired(false);
		$rootCausesDate->setTable("evaluation");
		$rootCausesDate->setHelpId(9016);
		$complaintJustifiedYes->add($rootCausesDate);

		$returnGoods = new radio("returnGoods");
		$returnGoods->setGroup("complaintJustifiedYes");
		$returnGoods->setDataType("string");
		$returnGoods->setLength(5);
		$returnGoods->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$returnGoods->setRowTitle("return_goods");
		$returnGoods->setRequired(true);
		if(isset($savedFields["returnGoods"]))
		$returnGoods->setValue($savedFields["returnGoods"]);
		else $returnGoods->setValue("NO");
		$returnGoods->setTable("evaluation");
		$returnGoods->setHelpId(9017);

		// Dependency
		$returnGoods_dependency = new dependency();
		$returnGoods_dependency->addRule(new rule('complaintJustifiedYes', 'returnGoods', 'YES'));
		$returnGoods_dependency->setGroup('returnGoodsYes');
		$returnGoods_dependency->setShow(true);

		$returnGoods->addControllingDependency($returnGoods_dependency);
		$complaintJustifiedYes->add($returnGoods);

		//$datasetAmerican = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `salesOffice`, `complaintValue_quantity` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
		//$fieldsAmerican = mysql_fetch_array($datasetAmerican);

		//if($fieldsAmerican['complaintLocation'] == 'american')

		$cat = $this->getCategory();

		//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
		if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA")
		{
			$datasetReturns = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM returnGoodsInformation WHERE complaintId = " . $this->complaint->form->get("id")->getValue() . "");

			if(mysql_num_rows($datasetReturns) > 0)
			{
				$displayOldAuthorisedReturnGoods = new readonly("displayOldAuthorisedReturnGoods");
				$displayOldAuthorisedReturnGoods->setRowTitle("old_authorisations");
				$displayOldAuthorisedReturnGoods->setHelpId(67566754345);
				$displayOldAuthorisedReturnGoods->setLabel("Previous Authorisations");
				//$displayOldAuthorisedReturnGoods->setLink("/apps/complaints/reAuthorise?complaint=" . $this->complaint->form->get("id")->getValue() . "&amp;status=evaluation&amp;mode=returnRequestEvaluation");
				//$displayOldAuthorisedReturnGoods->setOpenNewWindow(0);
				$displayOldAuthorisedReturnGoods->setValue("(" . mysql_num_rows($datasetReturns) . ") {TRANSLATE:this_evaluation_return_old_authorisations}");
				$complaintJustifiedYes->add($displayOldAuthorisedReturnGoods);
			}

			$returnGoodsReadOnly = new readonly("returnGoodsReadOnly");
			$returnGoodsReadOnly->setGroup("complaintJustifiedYes");
			$returnGoodsReadOnly->setRowTitle("return_goods");
			$returnGoodsReadOnly->setTable("evaluation");
			$returnGoodsReadOnly->setLabel("Return The Goods Request");
			$returnGoodsReadOnly->setVisible(false);
			$returnGoodsReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnGoodsReadOnly);

			$reAuthoriseReturnGoodsReadOnly = new textboxlink("reAuthoriseReturnGoodsReadOnly");
			$reAuthoriseReturnGoodsReadOnly->setRowTitle("re_authorise_return_goods");
			$reAuthoriseReturnGoodsReadOnly->setHelpId(67566754);
			$reAuthoriseReturnGoodsReadOnly->setLink("/apps/complaints/reAuthorise?complaint=" . $this->complaint->form->get("id")->getValue() . "&amp;status=evaluation&amp;mode=returnRequestEvaluation");
			$reAuthoriseReturnGoodsReadOnly->setOpenNewWindow(0);
			$reAuthoriseReturnGoodsReadOnly->setValue("{TRANSLATE:re_authorise_return_goods_link}");
			$complaintJustifiedYes->add($reAuthoriseReturnGoodsReadOnly);

			$returnRequestValue = new measurement("returnRequestValue");
			if(isset($savedFields["returnRequestValue"]))
			$returnRequestValue->setValue($savedFields["returnRequestValue"]);
			$returnRequestValue->setGroup("returnGoodsYes");
			$returnRequestValue->setDataType("string");
			$returnRequestValue->setRowTitle("return_goods_value");
			$returnRequestValue->setRequired(false);
			$returnRequestValue->setXMLSource("./apps/complaints/xml/currency.xml");
			$returnRequestValue->setTable("evaluation");
			$returnRequestValue->setHelpId(9016);
			$returnGoodsYes->add($returnRequestValue);

			$returnRequestValueReadOnly = new readonly("returnRequestValueReadOnly");
			$returnRequestValueReadOnly->setGroup("complaintJustifiedYes");
			$returnRequestValueReadOnly->setRowTitle("return_goods_value");
			$returnRequestValueReadOnly->setVisible(false);
			$returnRequestValueReadOnly->setTable("evaluation");
			$returnRequestValueReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnRequestValueReadOnly);

			$returnRequestComment = new textarea("returnRequestComment");
			if(isset($savedFields["returnRequestComment"]))
			$returnRequestComment->setValue($savedFields["returnRequestComment"]);
			$returnRequestComment->setGroup("returnGoodsYes");
			$returnRequestComment->setDataType("text");
			$returnRequestComment->setRowTitle("comment");
			$returnRequestComment->setRequired(false);
			$returnRequestComment->setTable("evaluation");
			$returnRequestComment->setHelpId(9087);
			$returnGoodsYes->add($returnRequestComment);

			$returnRequestCommentReadOnly = new readonly("returnRequestCommentReadOnly");
			$returnRequestCommentReadOnly->setGroup("complaintJustifiedYes");
			$returnRequestCommentReadOnly->setRowTitle("comment");
			$returnRequestCommentReadOnly->setTable("evaluation");
			$returnRequestCommentReadOnly->setVisible(false);
			$returnRequestCommentReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnRequestCommentReadOnly);

			$returnRequestName = new dropdown("returnRequestName");
			if(isset($savedFields["returnRequestName"]))
			$returnRequestName->setValue($savedFields["returnRequestName"]);
			$returnRequestName->setGroup("returnGoodsYes");
			$returnRequestName->setDataType("string");
			$returnRequestName->setRowTitle("send_request_to");
			$returnRequestName->setOnChange("copyToProcessOwnerField('processOwner2','returnRequestName')");
			$returnRequestName->setRequired(true);

			if($this->complaint->form->get("complaintValue")->getQuantity() < 2500)
			{
				$limits = "lower";
			}
			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 2500 && $this->complaint->form->get("complaintValue")->getQuantity() < 10000)
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

//			if($fieldsAmerican['complaintValue_quantity'] < 2500)
//			{
//				$limits = 'lower';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 2500 && $fieldsAmerican['complaintValue_quantity'] < 10000)
//			{
//				$limits = 'lower_mid';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 10000 && $fieldsAmerican['complaintValue_quantity'] < 30000)
//			{
//				$limits = 'upper_mid';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 30000)
//			{
//				$limits = 'upper';
//			}

			//$returnApprovalName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE `permission` LIKE 'complaints_return_approval_" . $fieldsAmerican['salesOffice'] . "_" . $limits . "' ORDER BY employee.NTLogon");

			//$returnRequestName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE `permission` LIKE 'complaints_return_approval_na_" . $limits . "' ORDER BY employee.NTLogon");
			$returnRequestName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY name ASC");
			$returnRequestName->setTable("evaluation");
			$returnRequestName->setHelpId(9017);
			$returnGoodsYes->add($returnRequestName);

			$returnRequestCC = new multipleCC("returnRequestCC");
			$returnRequestCC->setGroup("returnGoodsYes");
			$returnRequestCC->setDataType("text");
			$returnRequestCC->setRowTitle("cc");
			$returnRequestCC->setRequired(false);
			$returnRequestCC->setTable("evaluation");
			$returnRequestCC->setHelpId(9016);
			$returnGoodsYes->add($returnRequestCC);

			$returnRequestNameReadOnly = new readonly("returnRequestNameReadOnly");
			$returnRequestNameReadOnly->setGroup("complaintJustifiedYes");
			$returnRequestNameReadOnly->setRowTitle("send_request_to");
			$returnRequestNameReadOnly->setTable("evaluation");
			$returnRequestNameReadOnly->setVisible(false);
			$returnRequestNameReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnRequestNameReadOnly);

			$returnRequestCCReadOnly = new readonly("returnRequestCCReadOnly");
			$returnRequestCCReadOnly->setGroup("complaintJustifiedYes");
			$returnRequestCCReadOnly->setRowTitle("send_request_to_cc");
			$returnRequestCCReadOnly->setTable("evaluation");
			$returnRequestCCReadOnly->setVisible(false);
			$returnRequestCCReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnRequestCCReadOnly);

			$returnRequestSubmit = new submit("returnRequestSubmit");
			$returnRequestSubmit->setGroup("returnGoodsYes");
			$returnRequestSubmit->setVisible(true);
			$returnGoodsYes->add($returnRequestSubmit);

			$returnApprovalRequestStatus = new textbox("returnApprovalRequestStatus");
			$returnApprovalRequestStatus->setGroup("complaintJustifiedYes");
			$returnApprovalRequestStatus->setDataType("number");
			$returnApprovalRequestStatus->setVisible(false);
			$returnApprovalRequestStatus->setIgnore(false);
			$returnApprovalRequestStatus->setTable("evaluation");
			$returnApprovalRequestStatus->setHelpId(9016);
			$complaintJustifiedYes->add($returnApprovalRequestStatus);

			$returnApprovalRequest = new radio("returnApprovalRequest");
			$returnApprovalRequest->setGroup("returnGoodsApprovalGroup");
			$returnApprovalRequest->setDataType("string");
			$returnApprovalRequest->setLength(5);
			$returnApprovalRequest->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
			));
			$returnApprovalRequest->setRowTitle("return_approval_request");
			$returnApprovalRequest->setRequired(false);
			if(isset($savedFields["returnApprovalRequest"]))
			$returnApprovalRequest->setValue($savedFields["returnApprovalRequest"]);
			else $returnApprovalRequest->setValue("NO");
			$returnApprovalRequest->setTable("evaluation");
			$returnApprovalRequest->setHelpId(9017);

			// Dependency
			$returnApprovalRequest_dependency = new dependency();
			$returnApprovalRequest_dependency->addRule(new rule('returnGoodsApprovalGroup', 'returnApprovalRequest', 'YES'));
			$returnApprovalRequest_dependency->setGroup('returnGoodsApprovalGroupYes');
			$returnApprovalRequest_dependency->setShow(true);

			$returnApprovalRequest->addControllingDependency($returnApprovalRequest_dependency);
			$returnGoodsApprovalGroup->add($returnApprovalRequest);

			$returnApprovalRequestReadOnly = new readonly("returnApprovalRequestReadOnly");
			$returnApprovalRequestReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalRequestReadOnly->setRowTitle("return_approval_request");
			$returnApprovalRequestReadOnly->setTable("evaluation");
			$returnApprovalRequestReadOnly->setVisible(false);
			$returnApprovalRequestReadOnly->setLabel("Return The Goods Request Approval");
			$returnApprovalRequestReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalRequestReadOnly);

			$returnApprovalRequestComment = new textarea("returnApprovalRequestComment");
			if(isset($savedFields["returnApprovalRequestComment"]))
			$returnApprovalRequestComment->setValue($savedFields["returnApprovalRequestComment"]);
			$returnApprovalRequestComment->setGroup("returnGoodsApprovalGroupYes");
			$returnApprovalRequestComment->setDataType("text");
			$returnApprovalRequestComment->setRowTitle("comment");
			$returnApprovalRequestComment->setRequired(false);
			$returnApprovalRequestComment->setTable("evaluation");
			$returnApprovalRequestComment->setHelpId(9087);
			$returnGoodsApprovalGroupYes->add($returnApprovalRequestComment);

			$returnApprovalRequestCommentReadOnly = new readonly("returnApprovalRequestCommentReadOnly");
			$returnApprovalRequestCommentReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalRequestCommentReadOnly->setRowTitle("comment");
			$returnApprovalRequestCommentReadOnly->setTable("evaluation");
			$returnApprovalRequestCommentReadOnly->setVisible(false);
			$returnApprovalRequestCommentReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalRequestCommentReadOnly);

			$returnApprovalRequestName = new dropdown("returnApprovalRequestName");
			if(isset($savedFields["returnApprovalRequestName"]))
			$returnApprovalRequestName->setValue($savedFields["returnApprovalRequestName"]);
			$returnApprovalRequestName->setGroup("returnGoodsApprovalGroupYes");
			$returnApprovalRequestName->setDataType("string");
			$returnApprovalRequestName->setRowTitle("send_to");
			$returnApprovalRequestName->setRequired(true);
			$returnApprovalRequestName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
			$returnApprovalRequestName->setTable("evaluation");
			$returnApprovalRequestName->setOnChange("copyToProcessOwnerField('processOwner2','returnApprovalRequestName')");
			$returnApprovalRequestName->clearData();
			$returnApprovalRequestName->setHelpId(9002);
			$returnGoodsApprovalGroupYes->add($returnApprovalRequestName);

			$returnApprovalRequestNameReadOnly = new readonly("returnApprovalRequestNameReadOnly");
			$returnApprovalRequestNameReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalRequestNameReadOnly->setRowTitle("send_to");
			$returnApprovalRequestNameReadOnly->setTable("evaluation");
			$returnApprovalRequestNameReadOnly->setVisible(false);
			$returnApprovalRequestNameReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalRequestNameReadOnly);

			$returnApprovalRequestSubmit = new submit("returnApprovalRequestSubmit");
			$returnApprovalRequestSubmit->setGroup("returnGoodsApprovalGroupYes");
			$returnApprovalRequestSubmit->setVisible(true);
			$returnGoodsApprovalGroupYes->add($returnApprovalRequestSubmit);
		}

		$disposeGoods = new radio("disposeGoods");
		$disposeGoods->setGroup("complaintJustifiedYes2");
		$disposeGoods->setDataType("string");
		$disposeGoods->setLength(5);
		$disposeGoods->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$disposeGoods->setRowTitle("dispose_goods");
		$disposeGoods->setRequired(true);
		if(isset($savedFields["disposeGoods"]))
		$disposeGoods->setValue($savedFields["disposeGoods"]);
		else $disposeGoods->setValue("NO");
		$disposeGoods->setTable("evaluation");
		$disposeGoods->setHelpId(9018);

		// Dependency
		$disposeGoods_dependency = new dependency();
		$disposeGoods_dependency->addRule(new rule('complaintJustifiedYes2', 'disposeGoods', 'YES'));
		$disposeGoods_dependency->setGroup('disposeGoodsYes');
		$disposeGoods_dependency->setShow(true);

		$disposeGoods->addControllingDependency($disposeGoods_dependency);
		$complaintJustifiedYes2->add($disposeGoods);

		//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
		if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA")
		{
			$datasetDisposals = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM disposalGoodsInformation WHERE complaintId = " . $this->complaint->form->get("id")->getValue() . "");

			if(mysql_num_rows($datasetDisposals) > 0)
			{
				$displayOldAuthorisedDisposalGoods = new readonly("displayOldAuthorisedDisposalGoods");
				$displayOldAuthorisedDisposalGoods->setRowTitle("old_authorisations_disposal");
				$displayOldAuthorisedDisposalGoods->setHelpId(67566754289789);
				$displayOldAuthorisedDisposalGoods->setLabel("Previous Authorisations");
				//$displayOldAuthorisedReturnGoods->setLink("/apps/complaints/reAuthorise?complaint=" . $this->complaint->form->get("id")->getValue() . "&amp;status=evaluation&amp;mode=returnRequestEvaluation");
				//$displayOldAuthorisedReturnGoods->setOpenNewWindow(0);
				$displayOldAuthorisedDisposalGoods->setValue("(" . mysql_num_rows($datasetDisposals) . ") {TRANSLATE:this_evaluation_disposal_old_authorisations}");
				$complaintJustifiedYes->add($displayOldAuthorisedDisposalGoods);
			}

			$disposeGoodsReadOnly = new readonly("disposeGoodsReadOnly");
			$disposeGoodsReadOnly->setGroup("complaintJustifiedYes");
			$disposeGoodsReadOnly->setRowTitle("dispose_goods");
			$disposeGoodsReadOnly->setTable("evaluation");
			$disposeGoodsReadOnly->setLabel("Dispose The Goods Request");
			$disposeGoodsReadOnly->setVisible(false);
			$disposeGoodsReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($disposeGoodsReadOnly);

			$reAuthoriseDisposalGoodsReadOnly = new textboxlink("reAuthoriseDisposalGoodsReadOnly");
			$reAuthoriseDisposalGoodsReadOnly->setRowTitle("re_authorise_disposal_goods");
			$reAuthoriseDisposalGoodsReadOnly->setHelpId(67566754);
			$reAuthoriseDisposalGoodsReadOnly->setLink("/apps/complaints/reAuthorise?complaint=" . $this->complaint->form->get("id")->getValue() . "&amp;status=evaluation&amp;mode=disposalRequestEvaluation");
			$reAuthoriseDisposalGoodsReadOnly->setOpenNewWindow(0);
			$reAuthoriseDisposalGoodsReadOnly->setValue("{TRANSLATE:re_authorise_disposal_goods_link}");
			$complaintJustifiedYes->add($reAuthoriseDisposalGoodsReadOnly);

			$returnApprovalDisposalValue = new measurement("returnApprovalDisposalValue");
			if(isset($savedFields["returnApprovalDisposalValue"]))
			$returnApprovalDisposalValue->setValue($savedFields["returnApprovalDisposalValue"]);
			$returnApprovalDisposalValue->setGroup("disposeGoodsYes");
			$returnApprovalDisposalValue->setDataType("string");
			$returnApprovalDisposalValue->setRowTitle("disposal_goods_value");
			$returnApprovalDisposalValue->setXMLSource("./apps/complaints/xml/currency.xml");
			$returnApprovalDisposalValue->setRequired(false);
			$returnApprovalDisposalValue->setTable("evaluation");
			$returnApprovalDisposalValue->setHelpId(9016);
			$disposeGoodsYes->add($returnApprovalDisposalValue);

			$returnApprovalDisposalValueReadOnly = new readonly("returnApprovalDisposalValueReadOnly");
			$returnApprovalDisposalValueReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalDisposalValueReadOnly->setRowTitle("disposal_goods_value");
			$returnApprovalDisposalValueReadOnly->setTable("evaluation");
			$returnApprovalDisposalValueReadOnly->setVisible(false);
			$returnApprovalDisposalValueReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalDisposalValueReadOnly);

			$returnApprovalDisposalComment = new textarea("returnApprovalDisposalComment");
			if(isset($savedFields["returnApprovalDisposalComment"]))
			$returnApprovalDisposalComment->setValue($savedFields["returnApprovalDisposalComment"]);
			$returnApprovalDisposalComment->setGroup("disposeGoodsYes");
			$returnApprovalDisposalComment->setDataType("text");
			$returnApprovalDisposalComment->setRowTitle("comment");
			$returnApprovalDisposalComment->setRequired(false);
			$returnApprovalDisposalComment->setTable("evaluation");
			$returnApprovalDisposalComment->setHelpId(9087);
			$disposeGoodsYes->add($returnApprovalDisposalComment);

			$returnApprovalDisposalCommentReadOnly = new readonly("returnApprovalDisposalCommentReadOnly");
			$returnApprovalDisposalCommentReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalDisposalCommentReadOnly->setRowTitle("comment");
			$returnApprovalDisposalCommentReadOnly->setTable("evaluation");
			$returnApprovalDisposalCommentReadOnly->setVisible(false);
			$returnApprovalDisposalCommentReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalDisposalCommentReadOnly);

			$returnApprovalDisposalName = new dropdown("returnApprovalDisposalName");
			if(isset($savedFields["returnApprovalDisposalName"]))
			$returnApprovalDisposalName->setValue($savedFields["returnApprovalDisposalName"]);
			$returnApprovalDisposalName->setGroup("disposeGoodsYes");
			$returnApprovalDisposalName->setDataType("string");
			$returnApprovalDisposalName->setRowTitle("send_request_to");
			$returnApprovalDisposalName->setRequired(true);

//			if($this->complaint->form->get("complaintValue")->getQuantity() < 2500)
//			{
//				$limits = "lower";
//			}
//			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 2500 && $this->complaint->form->get("complaintValue")->getQuantity() < 1000)
//			{
//				$limits = "lower_mid";
//			}
//			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 10000 && $this->complaint->form->get("complaintValue")->getQuantity() < 30000)
//			{
//				$limits = "upper_mid";
//			}
//			elseif($this->complaint->form->get("complaintValue")->getQuantity() > 30000)
//			{
//				$limits = "upper";
//			}
//			else
//			{
//				$limits = "";
//			}

//			if($fieldsAmerican['complaintValue_quantity'] < 2500)
//			{
//				$limits = 'lower';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 2500 && $fieldsAmerican['complaintValue_quantity'] < 10000)
//			{
//				$limits = 'lower_mid';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 10000 && $fieldsAmerican['complaintValue_quantity'] < 30000)
//			{
//				$limits = 'upper_mid';
//			}
//			elseif($fieldsAmerican['complaintValue_quantity'] > 30000)
//			{
//				$limits = 'upper';
//			}

			//$returnApprovalName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE `permission` LIKE 'complaints_return_approval_" . $fieldsAmerican['salesOffice'] . "_" . $limits . "' ORDER BY employee.NTLogon");

			//$returnApprovalDisposalName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE `permission` LIKE 'complaints_return_approval_na_" . $limits . "' ORDER BY employee.NTLogon");
			$returnApprovalDisposalName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `employee` ORDER BY name ASC");
			$returnApprovalDisposalName->setTable("evaluation");
			$returnApprovalDisposalName->setOnChange("copyToProcessOwnerField('processOwner2','returnApprovalDisposalName')");
			$returnApprovalDisposalName->setHelpId(9017);
			$disposeGoodsYes->add($returnApprovalDisposalName);

			$returnApprovalDisposalNameReadOnly = new readonly("returnApprovalDisposalNameReadOnly");
			$returnApprovalDisposalNameReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalDisposalNameReadOnly->setRowTitle("send_request_to");
			$returnApprovalDisposalNameReadOnly->setTable("evaluation");
			$returnApprovalDisposalNameReadOnly->setVisible(false);
			$returnApprovalDisposalNameReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalDisposalNameReadOnly);

			$returnApprovalDisposalSubmit = new submit("returnApprovalDisposalSubmit");
			$returnApprovalDisposalSubmit->setGroup("disposeGoodsYes");
			$returnApprovalDisposalSubmit->setVisible(true);
			$disposeGoodsYes->add($returnApprovalDisposalSubmit);

			$returnApprovalDisposalRequestStatus = new textbox("returnApprovalDisposalRequestStatus");
			$returnApprovalDisposalRequestStatus->setGroup("complaintJustifiedYes");
			$returnApprovalDisposalRequestStatus->setDataType("number");
			$returnApprovalDisposalRequestStatus->setVisible(false);
			$returnApprovalDisposalRequestStatus->setIgnore(false);
			$returnApprovalDisposalRequestStatus->setTable("evaluation");
			$returnApprovalDisposalRequestStatus->setHelpId(9016);
			$complaintJustifiedYes->add($returnApprovalDisposalRequestStatus);

			$returnApprovalDisposalRequest = new radio("returnApprovalDisposalRequest");
			$returnApprovalDisposalRequest->setGroup("disposeGoodsApprovalGroup");
			$returnApprovalDisposalRequest->setDataType("string");
			$returnApprovalDisposalRequest->setLength(5);
			$returnApprovalDisposalRequest->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No')
			));
			$returnApprovalDisposalRequest->setRowTitle("return_approval_disposal_request");
			$returnApprovalDisposalRequest->setRequired(false);
			if(isset($savedFields["returnApprovalDisposalRequest"]))
			$returnApprovalDisposalRequest->setValue($savedFields["returnApprovalDisposalRequest"]);
			else $returnApprovalDisposalRequest->setValue("NO");
			$returnApprovalDisposalRequest->setTable("evaluation");
			$returnApprovalDisposalRequest->setHelpId(9017);

			// Dependency
			$returnApprovalDisposalRequest_dependency = new dependency();
			$returnApprovalDisposalRequest_dependency->addRule(new rule('disposeGoodsApprovalGroup', 'returnApprovalDisposalRequest', 'YES'));
			$returnApprovalDisposalRequest_dependency->setGroup('disposeGoodsApprovalGroupYes');
			$returnApprovalDisposalRequest_dependency->setShow(true);

			$returnApprovalDisposalRequest->addControllingDependency($returnApprovalDisposalRequest_dependency);
			$disposeGoodsApprovalGroup->add($returnApprovalDisposalRequest);

			$returnApprovalDisposalRequestReadOnly = new readonly("returnApprovalDisposalRequestReadOnly");
			$returnApprovalDisposalRequestReadOnly->setGroup("complaintJustifiedYes");
			$returnApprovalDisposalRequestReadOnly->setRowTitle("return_approval_disposal_request");
			$returnApprovalDisposalRequestReadOnly->setLabel("Dispose The Goods Approval");
			$returnApprovalDisposalRequestReadOnly->setTable("evaluation");
			$returnApprovalDisposalRequestReadOnly->setVisible(false);
			$returnApprovalDisposalRequestReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnApprovalDisposalRequestReadOnly);

			$returnDisposalRequestComment = new textarea("returnDisposalRequestComment");
			if(isset($savedFields["returnDisposalRequestComment"]))
			$returnDisposalRequestComment->setValue($savedFields["returnDisposalRequestComment"]);
			$returnDisposalRequestComment->setGroup("disposeGoodsApprovalGroupYes");
			$returnDisposalRequestComment->setDataType("text");
			$returnDisposalRequestComment->setRowTitle("comment");
			$returnDisposalRequestComment->setRequired(false);
			$returnDisposalRequestComment->setTable("evaluation");
			$returnDisposalRequestComment->setHelpId(9087);
			$disposeGoodsApprovalGroupYes->add($returnDisposalRequestComment);

			$returnDisposalRequestCommentReadOnly = new readonly("returnDisposalRequestCommentReadOnly");
			$returnDisposalRequestCommentReadOnly->setGroup("complaintJustifiedYes");
			$returnDisposalRequestCommentReadOnly->setRowTitle("comment");
			$returnDisposalRequestCommentReadOnly->setTable("evaluation");
			$returnDisposalRequestCommentReadOnly->setVisible(false);
			$returnDisposalRequestCommentReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnDisposalRequestCommentReadOnly);

			$returnDisposalRequestName = new dropdown("returnDisposalRequestName");
			if(isset($savedFields["returnDisposalRequestName"]))
			$returnDisposalRequestName->setValue($savedFields["returnDisposalRequestName"]);
			$returnDisposalRequestName->setGroup("disposeGoodsApprovalGroupYes");
			$returnDisposalRequestName->setDataType("string");
			$returnDisposalRequestName->setRowTitle("send_to");
			$returnDisposalRequestName->setRequired(true);
			$returnDisposalRequestName->setOnChange("copyToProcessOwnerField('processOwner2','returnDisposalRequestName')");
			$returnDisposalRequestName->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
			$returnDisposalRequestName->setTable("evaluation");
			$returnDisposalRequestName->clearData();
			$returnDisposalRequestName->setHelpId(9002);
			$disposeGoodsApprovalGroupYes->add($returnDisposalRequestName);

			$returnDisposalRequestNameReadOnly = new readonly("returnDisposalRequestNameReadOnly");
			$returnDisposalRequestNameReadOnly->setGroup("complaintJustifiedYes");
			$returnDisposalRequestNameReadOnly->setRowTitle("send_to");
			$returnDisposalRequestNameReadOnly->setTable("evaluation");
			$returnDisposalRequestNameReadOnly->setVisible(false);
			$returnDisposalRequestNameReadOnly->setHelpId(9017);
			$complaintJustifiedYes->add($returnDisposalRequestNameReadOnly);

			$returnDisposalSubmit = new submit("returnDisposalSubmit");
			$returnDisposalSubmit->setGroup("disposeGoodsApprovalGroupYes");
			$returnDisposalSubmit->setVisible(true);
			$disposeGoodsApprovalGroupYes->add($returnDisposalSubmit);
		}

		$updateInitiator = new radio("updateInitiator");
		$updateInitiator->setGroup("complaintJustifiedYes3");
		$updateInitiator->setDataType("string");
		$updateInitiator->setLength(3);
		$updateInitiator->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$updateInitiator->setRowTitle("update_initiator");
		$updateInitiator->setRequired(false);
		$updateInitiator->setHelpId(9017234);
		$updateInitiator->setVisible(true);
		if(isset($savedFields["update_initiator"]))
		$updateInitiator->setValue($savedFields["update_initiator"]);
		else $updateInitiator->setValue("No");
		$updateInitiator->setTable("evaluation");
		$complaintJustifiedYes3->add($updateInitiator);


		//		$submit = new submit("submit");
		//		$submit->setGroup("complaintJustifiedYes3");
		//		$submit->setVisible(true);
		//		$complaintJustifiedYes3->add($submit);



		//		$datasetCategoryM = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'M%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
		//		$rowCategoryM = mysql_fetch_array($datasetCategoryM);
		//		$datasetCategoryD = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'D%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
		//		$rowCategoryD = mysql_fetch_array($datasetCategoryD);

		$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
		$fields8D = mysql_fetch_array($dataset8D);
		//		if($rowCategoryM > 0 || $rowCategoryD > 0)
		if($fields8D['g8d'] == "yes")
		{
			$containmentAction = new textarea("containmentAction");
			if(isset($savedFields["containmentAction"]))
			$containmentAction->setValue($savedFields["containmentAction"]);
			/*
			$containmentAction->setGroup("complaintJustifiedYes3");
			$containmentAction->setDataType("string");
			//$containmentAction->setLength(1000);
			//echo "HERE";exit;
			$containmentAction->setRowTitle("containment_actions");
			$containmentAction->setRequired(false);
			$containmentAction->setTable("evaluation");
			$containmentAction->setHelpId(9087);
			*/

			$containmentAction->setGroup("complaintJustifiedYes3");
			$containmentAction->setDataType("text");
			$containmentAction->setRowTitle("containment_actions");
			$containmentAction->setRequired(false);
			$containmentAction->setTable("evaluation");
			$containmentAction->setHelpId(9087);

			$complaintJustifiedYes3->add($containmentAction);

			$containmentActionyn = new radio("containmentActionyn");
			$containmentActionyn->setGroup("complaintJustifiedYes3");
			$containmentActionyn->setDataType("string");
			$containmentActionyn->setLength(3);
			$containmentActionyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$containmentActionyn->setRowTitle("containmentAction_entered");
			$containmentActionyn->setRequired(false);
			$containmentActionyn->setVisible(false);
			if(isset($savedFields["containmentActionyn"]))
			$containmentActionyn->setValue($savedFields["containmentActionyn"]);
			else $containmentActionyn->setValue("No");
			$containmentActionyn->setTable("complaint");
			$complaintJustifiedYes3->add($containmentActionyn);

			$containmentActionAuthor = new textbox("containmentActionAuthor");
			if(isset($savedFields["containmentActionAuthor"]))
			$containmentActionAuthor->setValue($savedFields["containmentActionAuthor"]);
			$containmentActionAuthor->setGroup("complaintJustifiedYes3");
			$containmentActionAuthor->setDataType("string");
			$containmentActionAuthor->setLength(255);
			$containmentActionAuthor->setRowTitle("containment_actions_author");
			$containmentActionAuthor->setRequired(false);
			$containmentActionAuthor->setTable("evaluation");
			$containmentActionAuthor->setHelpId(9039);
			$complaintJustifiedYes3->add($containmentActionAuthor);

			$containmentActionDate = new calendar("containmentActionDate");
			if(isset($savedFields["containmentActionDate"]))
			$containmentActionDate->setValue($savedFields["containmentActionDate"]);
			$containmentActionDate->setGroup("complaintJustifiedYes3");
			$containmentActionDate->setDataType("date");
			$containmentActionDate->setErrorMessage("textbox_date_error");
			$containmentActionDate->setLength(255);
			$containmentActionDate->setRowTitle("containment_actions_date");
			$containmentActionDate->setRequired(false);
			$containmentActionDate->setTable("evaluation");
			$containmentActionDate->setHelpId(9025);
			$complaintJustifiedYes3->add($containmentActionDate);

			$possibleSolutions = new textarea("possibleSolutions");
			if(isset($savedFields["possibleSolutions"]))
			$possibleSolutions->setValue($savedFields["possibleSolutions"]);
			$possibleSolutions->setGroup("complaintJustifiedYes3");
			$possibleSolutions->setDataType("text");
			$possibleSolutions->setRowTitle("possible_solutions");
			$possibleSolutions->setRequired(false);
			$possibleSolutions->setTable("evaluation");
			$possibleSolutions->setHelpId(9088);
			$complaintJustifiedYes3->add($possibleSolutions);

			$possibleSolutionsyn = new radio("possibleSolutionsyn");
			$possibleSolutionsyn->setGroup("complaintJustifiedYes3");
			$possibleSolutionsyn->setDataType("string");
			$possibleSolutionsyn->setLength(3);
			$possibleSolutionsyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$possibleSolutionsyn->setRowTitle("possibleSolutions_entered");
			$possibleSolutionsyn->setRequired(false);
			$possibleSolutionsyn->setVisible(false);
			if(isset($savedFields["possibleSolutionsyn"]))
			$possibleSolutionsyn->setValue($savedFields["possibleSolutionsyn"]);
			else $possibleSolutionsyn->setValue("No");
			$possibleSolutionsyn->setTable("complaint");
			$complaintJustifiedYes3->add($possibleSolutionsyn);

			$possibleSolutionsAuthor = new textbox("possibleSolutionsAuthor");
			if(isset($savedFields["possibleSolutionsAuthor"]))
			$possibleSolutionsAuthor->setValue($savedFields["possibleSolutionsAuthor"]);
			$possibleSolutionsAuthor->setGroup("complaintJustifiedYes3");
			$possibleSolutionsAuthor->setDataType("string");
			$possibleSolutionsAuthor->setLength(255);
			$possibleSolutionsAuthor->setRowTitle("possible_solutions_author");
			$possibleSolutionsAuthor->setRequired(false);
			$possibleSolutionsAuthor->setTable("evaluation");
			$possibleSolutionsAuthor->setHelpId(9025);
			$complaintJustifiedYes3->add($possibleSolutionsAuthor);

			$possibleSolutionsDate = new calendar("possibleSolutionsDate");
			if(isset($savedFields["possibleSolutionsDate"]))
			$possibleSolutionsDate->setValue($savedFields["possibleSolutionsDate"]);
			$possibleSolutionsDate->setGroup("complaintJustifiedYes3");
			$possibleSolutionsDate->setDataType("date");
			$possibleSolutionsDate->setLength(255);
			$possibleSolutionsDate->setErrorMessage("textbox_date_error");
			$possibleSolutionsDate->setRowTitle("possible_solutions_date");
			$possibleSolutionsDate->setRequired(false);
			$possibleSolutionsDate->setTable("evaluation");
			$possibleSolutionsDate->setHelpId(9025);
			$complaintJustifiedYes3->add($possibleSolutionsDate);

		}

		$implementedActions = new textarea("implementedActions");
		if(isset($savedFields["implementedActions"]))
		$implementedActions->setValue($savedFields["implementedActions"]);
		$implementedActions->setGroup("complaintJustifiedYes3");
		$implementedActions->setDataType("text");
		$implementedActions->setRowTitle("implemented_actions");
		$implementedActions->setRequired(false);
		$implementedActions->setTable("evaluation");
		$implementedActions->setHelpId(9020);
		$complaintJustifiedYes3->add($implementedActions);

		$implementedActionsyn = new radio("implementedActionsyn");
		$implementedActionsyn->setGroup("complaintJustifiedYes3");
		$implementedActionsyn->setDataType("string");
		$implementedActionsyn->setLength(3);
		$implementedActionsyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$implementedActionsyn->setRowTitle("implementedAction_entered");
		$implementedActionsyn->setRequired(false);
		$implementedActionsyn->setVisible(false);
		if(isset($savedFields["implementedActionsyn"]))
		$implementedActionsyn->setValue($savedFields["implementedActionsyn"]);
		else $implementedActionsyn->setValue("No");
		$implementedActionsyn->setTable("complaint");
		$complaintJustifiedYes3->add($implementedActionsyn);

		$implementedActionsAuthor = new textbox("implementedActionsAuthor");
		if(isset($savedFields["implementedActionsAuthor"]))
		$implementedActionsAuthor->setValue($savedFields["implementedActionsAuthor"]);
		$implementedActionsAuthor->setGroup("complaintJustifiedYes3");
		$implementedActionsAuthor->setDataType("string");
		$implementedActionsAuthor->setLength(255);
		$implementedActionsAuthor->setRowTitle("implemented_actions_author");
		$implementedActionsAuthor->setRequired(false);
		$implementedActionsAuthor->setTable("evaluation");
		$implementedActionsAuthor->setHelpId(9021);
		$complaintJustifiedYes3->add($implementedActionsAuthor);

		$implementedActionsDate = new calendar("implementedActionsDate");
		if(isset($savedFields["implementedActionsDate"]))
		$implementedActionsDate->setValue($savedFields["implementedActionsDate"]);
		$implementedActionsDate->setGroup("complaintJustifiedYes3");
		$implementedActionsDate->setDataType("date");
		$implementedActionsDate->setErrorMessage("textbox_date_error");
		$implementedActionsDate->setLength(255);
		$implementedActionsDate->setRowTitle("implemented_actions_date");
		$implementedActionsDate->setRequired(false);
		$implementedActionsDate->setTable("evaluation");//was complaintEval?????
		$implementedActionsDate->setHelpId(9022);
		$complaintJustifiedYes3->add($implementedActionsDate);

		$implementedActionsEstimated = new calendar("implementedActionsEstimated");
		if(isset($savedFields["implementedActionsEstimated"]))
		$implementedActionsEstimated->setValue($savedFields["implementedActionsEstimated"]);
		$implementedActionsEstimated->setGroup("complaintJustifiedYes3");
		$implementedActionsEstimated->setDataType("date");
		$implementedActionsEstimated->setErrorMessage("textbox_date_error");
		$implementedActionsEstimated->setLength(255);
		$implementedActionsEstimated->setRowTitle("implemented_actions_estimated");
		$implementedActionsEstimated->setRequired(false);
		$implementedActionsEstimated->setTable("evaluation");
		$implementedActionsEstimated->setHelpId(9023);
		$complaintJustifiedYes3->add($implementedActionsEstimated);

		$implementedActionsImplementation = new calendar("implementedActionsImplementation");
		if(isset($savedFields["implementedActionsImplementation"]))
		$implementedActionsImplementation->setValue($savedFields["implementedActionsImplementation"]);
		$implementedActionsImplementation->setGroup("complaintJustifiedYes3");
		$implementedActionsImplementation->setDataType("date");
		$implementedActionsImplementation->setErrorMessage("textbox_date_error");
		$implementedActionsImplementation->setLength(255);
		$implementedActionsImplementation->setRowTitle("implemented_actions_implementation");
		$implementedActionsImplementation->setRequired(false);
		$implementedActionsImplementation->setTable("evaluation");
		$implementedActionsImplementation->setHelpId(9024);
		$complaintJustifiedYes3->add($implementedActionsImplementation);

		$implementedActionsEffectiveness = new calendar("implementedActionsEffectiveness");
		if(isset($savedFields["implementedActionsEffectiveness"]))
		$implementedActionsEffectiveness->setValue($savedFields["implementedActionsEffectiveness"]);
		$implementedActionsEffectiveness->setGroup("complaintJustifiedYes3");
		$implementedActionsEffectiveness->setDataType("date");
		$implementedActionsEffectiveness->setErrorMessage("textbox_date_error");
		$implementedActionsEffectiveness->setLength(255);
		$implementedActionsEffectiveness->setRowTitle("implemented_actions_effectiveness");
		$implementedActionsEffectiveness->setRequired(false);
		$implementedActionsEffectiveness->setTable("evaluation");
		$implementedActionsEffectiveness->setHelpId(9024345345);
		$complaintJustifiedYes3->add($implementedActionsEffectiveness);

		$implementedPermanentCorrectiveActionValidated = new textarea("implementedPermanentCorrectiveActionValidated");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidated"]))
		$implementedPermanentCorrectiveActionValidated->setValue($savedFields["implementedPermanentCorrectiveActionValidated"]);
		$implementedPermanentCorrectiveActionValidated->setGroup("complaintJustifiedYes3");
		$implementedPermanentCorrectiveActionValidated->setDataType("text");
		$implementedPermanentCorrectiveActionValidated->setRowTitle("implemented_permanent_corrective_action_validated");
		$implementedPermanentCorrectiveActionValidated->setRequired(false);
		$implementedPermanentCorrectiveActionValidated->setTable("evaluation");
		$implementedPermanentCorrectiveActionValidated->setHelpId(90243453451);
		$complaintJustifiedYes3->add($implementedPermanentCorrectiveActionValidated);

		$implementedPermanentCorrectiveActionValidatedyn = new radio("implementedPermanentCorrectiveActionValidatedyn");
		$implementedPermanentCorrectiveActionValidatedyn->setGroup("complaintJustifiedYes3");
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
		$implementedPermanentCorrectiveActionValidatedyn->setTable("complaint");
		$complaintJustifiedYes3->add($implementedPermanentCorrectiveActionValidatedyn);

		$implementedPermanentCorrectiveActionValidatedAuthor = new textbox("implementedPermanentCorrectiveActionValidatedAuthor");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]))
		$implementedPermanentCorrectiveActionValidatedAuthor->setValue($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]);
		$implementedPermanentCorrectiveActionValidatedAuthor->setGroup("complaintJustifiedYes3");
		$implementedPermanentCorrectiveActionValidatedAuthor->setDataType("string");
		$implementedPermanentCorrectiveActionValidatedAuthor->setLength(255);
		$implementedPermanentCorrectiveActionValidatedAuthor->setRowTitle("implemented_permanent_corrective_action_validated_author");
		$implementedPermanentCorrectiveActionValidatedAuthor->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedAuthor->setTable("evaluation");
		$implementedPermanentCorrectiveActionValidatedAuthor->setHelpId(90243453452);
		$complaintJustifiedYes3->add($implementedPermanentCorrectiveActionValidatedAuthor);

		$implementedPermanentCorrectiveActionValidatedDate = new calendar("implementedPermanentCorrectiveActionValidatedDate");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedDate"]))
		$implementedPermanentCorrectiveActionValidatedDate->setValue($savedFields["implementedPermanentCorrectiveActionValidatedDate"]);
		$implementedPermanentCorrectiveActionValidatedDate->setGroup("complaintJustifiedYes3");
		$implementedPermanentCorrectiveActionValidatedDate->setDataType("date");
		$implementedPermanentCorrectiveActionValidatedDate->setErrorMessage("textbox_date_error");
		$implementedPermanentCorrectiveActionValidatedDate->setLength(255);
		$implementedPermanentCorrectiveActionValidatedDate->setRowTitle("implemented_permanent_corrective_action_date");
		$implementedPermanentCorrectiveActionValidatedDate->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedDate->setTable("evaluation");//was complaintEval?????
		$implementedPermanentCorrectiveActionValidatedDate->setHelpId(90243453453);
		$complaintJustifiedYes3->add($implementedPermanentCorrectiveActionValidatedDate);


		if($fields8D['g8d'] == "yes")
		{

			$preventiveActions = new textarea("preventiveActions");
			if(isset($savedFields["preventiveActions"]))
			$preventiveActions->setValue($savedFields["preventiveActions"]);
			$preventiveActions->setGroup("complaintJustifiedYes3");
			$preventiveActions->setDataType("text");
			$preventiveActions->setRowTitle("preventive_action");
			$preventiveActions->setRequired(false);
			$preventiveActions->setTable("evaluation");
			$preventiveActions->setHelpId(9089);
			$complaintJustifiedYes3->add($preventiveActions);

			$preventiveActionsyn = new radio("preventiveActionsyn");
			$preventiveActionsyn->setGroup("complaintJustifiedYes3");
			$preventiveActionsyn->setDataType("string");
			$preventiveActionsyn->setLength(3);
			$preventiveActionsyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$preventiveActionsyn->setRowTitle("preventiveActions_entered");
			$preventiveActionsyn->setRequired(false);
			$preventiveActionsyn->setVisible(false);
			if(isset($savedFields["preventiveActionsyn"]))
			$preventiveActionsyn->setValue($savedFields["preventiveActionsyn"]);
			else $preventiveActionsyn->setValue("No");
			$preventiveActionsyn->setTable("complaint");
			$complaintJustifiedYes3->add($preventiveActionsyn);

			$preventiveActionsAuthor = new textbox("preventiveActionsAuthor");
			if(isset($savedFields["preventiveActionsAuthor"]))
			$preventiveActionsAuthor->setValue($savedFields["preventiveActionsAuthor"]);
			$preventiveActionsAuthor->setGroup("complaintJustifiedYes3");
			$preventiveActionsAuthor->setDataType("string");
			$preventiveActionsAuthor->setLength(255);
			$preventiveActionsAuthor->setRowTitle("preventive_actions_author");
			$preventiveActionsAuthor->setRequired(false);
			$preventiveActionsAuthor->setTable("evaluation");
			$preventiveActionsAuthor->setHelpId(9090);
			$complaintJustifiedYes3->add($preventiveActionsAuthor);

			$preventiveActionsDate = new calendar("preventiveActionsDate");
			if(isset($savedFields["preventiveActionsDate"]))
			$preventiveActionsDate->setValue($savedFields["preventiveActionsDate"]);
			$preventiveActionsDate->setGroup("complaintJustifiedYes3");
			$preventiveActionsDate->setDataType("date");
			$preventiveActionsDate->setErrorMessage("textbox_date_error");
			$preventiveActionsDate->setLength(255);
			$preventiveActionsDate->setRowTitle("preventive_actions_date");
			$preventiveActionsDate->setRequired(false);
			$preventiveActionsDate->setTable("evaluation");
			$preventiveActionsDate->setHelpId(9091);
			$complaintJustifiedYes3->add($preventiveActionsDate);

			$preventiveActionsEstimatedDate = new calendar("preventiveActionsEstimatedDate");
			if(isset($savedFields["preventiveActionsEstimatedDate"]))
			$preventiveActionsEstimatedDate->setValue($savedFields["preventiveActionsEstimatedDate"]);
			$preventiveActionsEstimatedDate->setGroup("complaintJustifiedYes3");
			$preventiveActionsEstimatedDate->setDataType("date");
			$preventiveActionsEstimatedDate->setErrorMessage("textbox_date_error");
			$preventiveActionsEstimatedDate->setLength(255);
			$preventiveActionsEstimatedDate->setRowTitle("preventive_actions_estimated_date");
			$preventiveActionsEstimatedDate->setRequired(false);
			$preventiveActionsEstimatedDate->setTable("evaluation");
			$preventiveActionsEstimatedDate->setHelpId(9092);
			$complaintJustifiedYes3->add($preventiveActionsEstimatedDate);

			$preventiveActionsImplementedDate = new calendar("preventiveActionsImplementedDate");
			if(isset($savedFields["preventiveActionsImplementedDate"]))
			$preventiveActionsImplementedDate->setValue($savedFields["preventiveActionsImplementedDate"]);
			$preventiveActionsImplementedDate->setGroup("complaintJustifiedYes3");
			$preventiveActionsImplementedDate->setErrorMessage("textbox_date_error");
			$preventiveActionsImplementedDate->setDataType("date");
			$preventiveActionsImplementedDate->setLength(255);
			$preventiveActionsImplementedDate->setRowTitle("preventive_actions_implemented_date");
			$preventiveActionsImplementedDate->setRequired(false);
			$preventiveActionsImplementedDate->setTable("evaluation");
			$preventiveActionsImplementedDate->setHelpId(9093);
			$complaintJustifiedYes3->add($preventiveActionsImplementedDate);

			$preventiveActionsValidationDate = new calendar("preventiveActionsValidationDate");
			if(isset($savedFields["preventiveActionsValidationDate"]))
			$preventiveActionsValidationDate->setValue($savedFields["preventiveActionsValidationDate"]);
			$preventiveActionsValidationDate->setGroup("complaintJustifiedYes3");
			$preventiveActionsValidationDate->setDataType("date");
			$preventiveActionsValidationDate->setErrorMessage("textbox_date_error");
			$preventiveActionsValidationDate->setLength(255);
			$preventiveActionsValidationDate->setRowTitle("preventive_actions_validation_date");
			$preventiveActionsValidationDate->setRequired(false);
			$preventiveActionsValidationDate->setTable("evaluation");
			$preventiveActionsValidationDate->setHelpId(9094);
			$complaintJustifiedYes3->add($preventiveActionsValidationDate);

		}


		$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
		$fields8D = mysql_fetch_array($dataset8D);

		if($fields8D['g8d'] == "yes")
		{
			$managementSystemReviewed = new radio("managementSystemReviewed");
			$managementSystemReviewed->setGroup("managementSystemGroup");
			$managementSystemReviewed->setDataType("string");
			$managementSystemReviewed->setLength(5);
			$managementSystemReviewed->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$managementSystemReviewed->setRowTitle("management_system_ref");
			$managementSystemReviewed->setRequired(false);
			if(isset($savedFields["managementSystemReviewed"]))
			$managementSystemReviewed->setValue($savedFields["managementSystemReviewed"]);
			else $managementSystemReviewed->setValue("NO");
			$managementSystemReviewed->setTable("evaluation");
			$managementSystemReviewed->setHelpId(9026);


			// Dependency
			$managementSystemReviewed_dependency = new dependency();
			$managementSystemReviewed_dependency->addRule(new rule('managementSystemGroup', 'managementSystemReviewed', 'YES'));
			$managementSystemReviewed_dependency->setGroup('managementSystemRefYes');
			$managementSystemReviewed_dependency->setShow(true);

			$managementSystemReviewed->addControllingDependency($managementSystemReviewed_dependency);
			$managementSystemGroup->add($managementSystemReviewed);

			$managementSystemReviewedRef = new textbox("managementSystemReviewedRef");
			if(isset($savedFields["managementSystemReviewedRef"]))
			$managementSystemReviewedRef->setValue($savedFields["managementSystemReviewedRef"]);
			$managementSystemReviewedRef->setGroup("managementSystemRefYes");
			$managementSystemReviewedRef->setDataType("string");
			$managementSystemReviewedRef->setLength(255);
			$managementSystemReviewedRef->setRowTitle("management_system_yes_ref");
			$managementSystemReviewedRef->setRequired(false);
			$managementSystemReviewedRef->setTable("evaluation");
			$managementSystemReviewedRef->setHelpId(9027);
			$managementSystemRefYes->add($managementSystemReviewedRef);

			$managementSystemReviewedDate = new calendar("managementSystemReviewedDate");
			if(isset($savedFields["managementSystemReviewedDate"]))
			$managementSystemReviewedDate->setValue($savedFields["managementSystemReviewedDate"]);
			$managementSystemReviewedDate->setGroup("managementSystemRefYes");
			$managementSystemReviewedDate->setDataType("date");
			$managementSystemReviewedDate->setErrorMessage("textbox_date_error");
			$managementSystemReviewedDate->setLength(255);
			$managementSystemReviewedDate->setRowTitle("management_system_date");
			$managementSystemReviewedDate->setRequired(false);
			$managementSystemReviewedDate->setTable("evaluation");
			$managementSystemReviewedDate->setHelpId(9028);
			$managementSystemRefYes->add($managementSystemReviewedDate);


			$fmea = new radio("fmea");
			$fmea->setGroup("fmeaGroup");
			$fmea->setDataType("string");
			$fmea->setLength(5);
			$fmea->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$fmea->setRowTitle("fmea");
			$fmea->setRequired(false);
			if(isset($savedFields["fmea"]))
			$fmea->setValue($savedFields["fmea"]);
			else $fmea->setValue("NO");
			$fmea->setTable("evaluation");
			$fmea->setHelpId(9029);


			// Dependency
			$fmea_dependency = new dependency();
			$fmea_dependency->addRule(new rule('fmeaGroup', 'fmea', 'YES'));
			$fmea_dependency->setGroup('fmeaDepGroup');
			$fmea_dependency->setShow(true);

			$fmea->addControllingDependency($fmea_dependency);
			$fmeaGroup->add($fmea);

			$fmeaRef = new textbox("fmeaRef");
			if(isset($savedFields["fmeaRef"]))
			$fmeaRef->setValue($savedFields["fmeaRef"]);
			$fmeaRef->setGroup("fmeaDepGroup");
			$fmeaRef->setDataType("string");
			$fmeaRef->setLength(255);
			$fmeaRef->setRowTitle("fmea_yes_ref");
			$fmeaRef->setRequired(false);
			$fmeaRef->setTable("evaluation");
			$fmeaRef->setHelpId(9030);
			$fmeaDepGroup->add($fmeaRef);

			$fmeaDate = new calendar("fmeaDate");
			if(isset($savedFields["fmeaDate"]))
			$fmeaDate->setValue($savedFields["fmeaDate"]);
			$fmeaDate->setGroup("fmeaDepGroup");
			$fmeaDate->setDataType("date");
			$fmeaDate->setErrorMessage("textbox_date_error");
			$fmeaDate->setLength(255);
			$fmeaDate->setRowTitle("fmea_ref_date");
			$fmeaDate->setRequired(false);
			$fmeaDate->setTable("evaluation");
			$fmeaDate->setHelpId(9031);
			$fmeaDepGroup->add($fmeaDate);

			$fmeaReviewed = new radio("fmeaReviewed");
			$fmeaReviewed->setGroup("fmeaReviewedGroup");
			$fmeaReviewed->setDataType("string");
			$fmeaReviewed->setLength(5);
			$fmeaReviewed->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$fmeaReviewed->setRowTitle("fmea_reviewed");
			$fmeaReviewed->setRequired(false);
			$fmeaReviewed->setVisible(false);
			if(isset($savedFields["fmeaReviewed"]))
			$fmeaReviewed->setValue($savedFields["fmeaReviewed"]);
			else $fmeaReviewed->setValue("");
			$fmeaReviewed->setTable("evaluation");
			$fmeaReviewed->setHelpId(9029);
			$fmeaReviewedGroup->add($fmeaReviewed);


			$customerSpecification = new radio("customerSpecification");
			$customerSpecification->setDataType("string");
			$customerSpecification->setLength(5);
			$customerSpecification->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$customerSpecification->setRowTitle("customer_specification");
			$customerSpecification->setRequired(false);
			if(isset($savedFields["customerSpecification"]))
			$customerSpecification->setValue($savedFields["customerSpecification"]);
			else $customerSpecification->setValue("NO");
			$customerSpecification->setTable("evaluation");
			$customerSpecification->setHelpId(9032);


			// Dependency
			$customerSpecification_dependency = new dependency();
			$customerSpecification_dependency->addRule(new rule('customerSpecificationGroup', 'customerSpecification', 'YES'));
			$customerSpecification_dependency->setGroup('customerSpecificationGroupYes');
			$customerSpecification_dependency->setShow(true);

			$customerSpecification->addControllingDependency($customerSpecification_dependency);
			$customerSpecificationGroup->add($customerSpecification);

			$customerSpecificationRef = new textbox("customerSpecificationRef");
			if(isset($savedFields["customerSpecificationRef"]))
			$customerSpecificationRef->setValue($savedFields["customerSpecificationRef"]);
			$customerSpecificationRef->setGroup("customerSpecificationGroupYes");
			$customerSpecificationRef->setDataType("string");
			$customerSpecificationRef->setLength(255);
			$customerSpecificationRef->setRowTitle("customer_specification_ref");
			$customerSpecificationRef->setRequired(false);
			$customerSpecificationRef->setTable("evaluation");
			$customerSpecificationRef->setHelpId(9033);
			$customerSpecificationGroupYes->add($customerSpecificationRef);

			$customerSpecificationDate = new calendar("customerSpecificationDate");
			if(isset($savedFields["customerSpecificationDate"]))
			$customerSpecificationDate->setValue($savedFields["customerSpecificationDate"]);
			$customerSpecificationDate->setGroup("customerSpecificationGroupYes");
			$customerSpecificationDate->setDataType("date");
			$customerSpecificationDate->setErrorMessage("textbox_date_error");
			$customerSpecificationDate->setLength(255);
			$customerSpecificationDate->setRowTitle("customer_specification_date");
			$customerSpecificationDate->setRequired(false);
			$customerSpecificationDate->setTable("evaluation");
			$customerSpecificationDate->setHelpId(9034);
			$customerSpecificationGroupYes->add($customerSpecificationDate);

			/////////////////////
			$flowChart = new radio("flowChart");
			$flowChart->setGroup("flowChartGroup");
			$flowChart->setDataType("string");
			$flowChart->setLength(5);
			$flowChart->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$flowChart->setRowTitle("flow_chart");
			$flowChart->setRequired(false);
			if(isset($savedFields["flowChart"]))
			$flowChart->setValue($savedFields["flowChart"]);
			else $flowChart->setValue("NO");
			$flowChart->setTable("evaluation");
			$flowChart->setHelpId(9035);


			// Dependency
			$flowChart_dependency = new dependency();
			$flowChart_dependency->addRule(new rule('flowChartGroup', 'flowChart', 'YES'));
			$flowChart_dependency->setGroup('flowChartGroupYes');
			$flowChart_dependency->setShow(true);

			$flowChart->addControllingDependency($flowChart_dependency);
			$flowChartGroup->add($flowChart);

			$flowChartRef = new textbox("flowChartRef");
			if(isset($savedFields["flowChartRef"]))
			$flowChartRef->setValue($savedFields["flowChartRef"]);
			$flowChartRef->setGroup("flowChartGroupYes");
			$flowChartRef->setDataType("string");
			$flowChartRef->setLength(255);
			$flowChartRef->setRowTitle("flow_chart_yes_ref");
			$flowChartRef->setRequired(false);
			$flowChartRef->setTable("evaluation");
			$flowChartRef->setHelpId(903534234);
			$flowChartGroupYes->add($flowChartRef);

			$flowChartDate = new calendar("flowChartDate");
			if(isset($savedFields["flowChartDate"]))
			$flowChartDate->setValue($savedFields["flowChartDate"]);
			$flowChartDate->setGroup("flowChartGroupYes");
			$flowChartDate->setDataType("date");
			$flowChartDate->setErrorMessage("textbox_date_error");
			$flowChartDate->setLength(255);
			$flowChartDate->setRowTitle("flow_chart_ref_date");
			$flowChartDate->setRequired(false);
			$flowChartDate->setTable("evaluation");
			$flowChartDate->setHelpId(903575677);
			$flowChartGroupYes->add($flowChartDate);

			///////////////////
		}

		$comments = new textarea("comments");
		if(isset($savedFields["comments"]))
		$comments->setValue($savedFields["comments"]);
		$comments->setGroup("commentsGroup");
		$comments->setDataType("text");
		$comments->setRowTitle("additional_comments");
		$comments->setRequired(false);
		$comments->setTable("evaluation");
		$comments->setHelpId(90357567776);
		$commentsGroup->add($comments);


		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1111);
		$processOwnerLink->setLink("http://scapanet/apps/complaints/data/process_owner_matrix_na.xls");
		$processOwnerLink->setValue("Process Owner Matrix");
		$transferOwnership2GroupYes->add($processOwnerLink);

		//$processOwner = new dropdown("processOwner");
		$processOwner2 = new autocomplete("processOwner2");
		if(isset($savedFields["processOwner2"]))
		$processOwner2->setValue($savedFields["processOwner2"]);
		$processOwner2->setGroup("commentsGroup");
		$processOwner2->setDataType("string");
		$processOwner2->setErrorMessage("user_not_found");
		$processOwner2->setRowTitle("chosen_complaint_owner");
		$processOwner2->setRequired(true);
		//$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner2->setUrl("/apps/complaints/ajax/complaintOwner2?");
		$processOwner2->setTable("evaluation");
		//$processOwner->clearData();
		$processOwner2->setHelpId(8145);
		$transferOwnership2GroupYes->add($processOwner2);

		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
			$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("transferOwnership2GroupYes");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("CC_customer");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("complaint");
		$copy_to->setHelpId(8146);
		$transferOwnership2GroupYes->add($copy_to);


		//		$copyTo = new autocomplete("copyTo");
		//		if(isset($savedFields["copyTo"]))
		//			$copyTo->setValue($savedFields["copyTo"]);
		//		$copyTo->setGroup("commentsGroup");
		//		$copyTo->setDataType("string");
		//		$copyTo->setRowTitle("CC");
		//		$copyTo->setRequired(false);
		//		$copyTo->setUrl("/apps/complaints/ajax/ccevaluation?");
		//		$copyTo->setTable("evaluation");
		//		$copyTo->setHelpId(8145);
		//		$transferOwnership2GroupYes->add($copyTo);

//		if(!isset($savedFields["0|copy_to"])){//the first one will always need to be set if its saved
//			$copy_to = new autocomplete("copy_to");
//			if(isset($savedFields["0|copy_to"]))
//			$copy_to->setValue($savedFields["0|copy_to"]);
//			$copy_to->setGroup("transferOwnership2GroupYes");
//			$copy_to->setDataType("string");
//			$copy_to->setRowTitle("CC");
//			//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//			$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//			$copy_to->setRequired(false);
//			$copy_to->setTable("ccGroup");
//			$copy_to->setHelpId(8146);
//			$transferOwnership2GroupYes2->add($copy_to);
//		}else{
//			$this->form->groupsToExclude[] = "ccComplaintGroup";
//			$i=0;
//			$endList = false;
//			do{
//				if(!isset($savedFields[$i."|copy_to"])){
//					$maxList = $i;
//					$endList = true;
//				}
//				$i++;
//			}while(!$endList);
//			for($i=0; $i<$maxList; $i++){
//				if($i==0){//first will always be set
//					$copy_to = new autocomplete("copy_to");
//					if(isset($savedFields["0|copy_to"]))
//					$copy_to->setValue($savedFields["0|copy_to"]);
//					$copy_to->setGroup("transferOwnership2GroupYes");
//					$copy_to->setDataType("string");
//					$copy_to->setRowTitle("CC");
//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//					$copy_to->setRequired(false);
//					$copy_to->setTable("ccGroup");
//					$copy_to->setHelpId(8146);
//					$transferOwnership2GroupYes2->add($copy_to);
//				}else{
//
//					$copy_to = new autocomplete("copy_to");
//					if(isset($savedFields[$i."|copy_to"]))
//					$copy_to->setValue($savedFields[$i."|copy_to"]);
//					$copy_to->setGroup("ccComplaintGroup");
//					$copy_to->setDataType("string");
//					$copy_to->setRowTitle("CC");
//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//					$copy_to->setRequired(false);
//					$copy_to->setTable("ccGroup");
//					$copy_to->setHelpId(8146);
//					//$ccComplaintGroup->add($copy_to);
//
//
//					$transferOwnership2GroupYes2->addRowCustom($savedFields[$i."|copy_to"]);
//				}
//			}
//		}





		$emailText = new textarea("emailText");
		if(isset($savedFields["emailText"]))
		$emailText->setValue($savedFields["emailText"]);
		$emailText->setGroup("commentsGroup");
		$emailText->setDataType("text");
		$emailText->setRowTitle("emailText");
		$emailText->setRequired(false);
		$emailText->setTable("evaluation");
		$emailText->setHelpId(9078);
		$transferOwnership2GroupYes3->add($emailText);




		$submit = new submit("submit");
		$submit->setGroup("sentTo");
		$submit->setVisible(true);
		$submitGroup->add($submit);


		$this->form->add($initiation);
		$this->form->add($isPORightNo);
		//$this->form->add($transferOwnership2Group);
		$this->form->add($sampleReceivedGroup);
		$this->form->add($isSampleReceivedYes);
		$this->form->add($sampleReceivedGroupAfter);
		$this->form->add($isComplaintCatRightNo);
		$this->form->add($complaintJustifiedGroup);
		$this->form->add($complaintJustifiedYes);
		$this->form->add($returnGoodsYes);
		$this->form->add($returnGoodsApprovalGroup);
		$this->form->add($returnGoodsApprovalGroupYes);
		$this->form->add($complaintJustifiedYes2);
		$this->form->add($disposeGoodsYes);
		$this->form->add($disposeGoodsApprovalGroup);
		$this->form->add($disposeGoodsApprovalGroupYes);
		$this->form->add($complaintJustifiedYes3);
		$this->form->add($managementSystemGroup);
		$this->form->add($managementSystemRefYes);
		$this->form->add($fmeaGroup);
		$this->form->add($fmeaDepGroup);
		$this->form->add($fmeaReviewedGroup);
		$this->form->add($customerSpecificationGroup);
		$this->form->add($customerSpecificationGroupYes);
		$this->form->add($flowChartGroup);
		$this->form->add($flowChartGroupYes);
		$this->form->add($transferOwnership2Group);
		$this->form->add($commentsGroup);
		$this->form->add($transferOwnership2GroupYes);
//		$this->form->add($transferOwnership2GroupYes2);
		$this->form->add($transferOwnership2GroupYes3);
		$this->form->add($submitGroup);



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

		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"]))
		{
			// do nothing ...
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
		$this->form = new form("evaluation" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

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

		$openDate = new textbox("openDate");
		$openDate->setTable("complaint");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

//		$openDate = new readonly("openDate");
//		$openDate->setGroup("typeOfComplaintGroup");
//		$openDate->setDataType("string");
//		$openDate->setLabel("1 - Information");
//		$openDate->setRowTitle("scapa_complaint_date");
//		$openDate->setTable("complaintExternal");
//		$openDate->setHelpId(10001);
//		$typeOfComplaintGroup->add($openDate);
//
//		$sp_siteConcerned = new readonly("sp_siteConcerned");
//		$sp_siteConcerned->setGroup("typeOfComplaintGroup");
//		$sp_siteConcerned->setDataType("string");
//		$sp_siteConcerned->setRowTitle("site_concerned");
//		$sp_siteConcerned->setTable("complaintExternal");
//		$sp_siteConcerned->setHelpId(10001);
//		$typeOfComplaintGroup->add($sp_siteConcerned);
//
//		$sp_buyer = new readonly("sp_buyer");
//		$sp_buyer->setGroup("typeOfComplaintGroup");
//		$sp_buyer->setDataType("string");
//		$sp_buyer->setRowTitle("buyer");
//		$sp_buyer->setTable("complaintExternal");
//		$sp_buyer->setHelpId(10001);
//		$typeOfComplaintGroup->add($sp_buyer);
//
//		$category = new readonly("category");
//		$category->setGroup("typeOfComplaintGroup");
//		$category->setDataType("string");
//		$category->setLength(255);
//		$category->setRowTitle("apparent_category");
//		$category->setTable("complaintExternal");
//		$category->setHelpId(8005);
//		$typeOfComplaintGroup->add($category);

		//$colour = new readonly("colour");
		//$colour->setGroup("typeOfComplaintGroup");
		//$colour->setDataType("string");
		//$colour->setRowTitle("colour");
		//$colour->setTable("complaintExternal");
		//$colour->setHelpId(10002);
		//$typeOfComplaintGroup->add($colour);

//		$batchNumber = new readonly("batchNumber");
//		$batchNumber->setGroup("typeOfComplaintGroup");
//		$batchNumber->setDataType("string");
//		$batchNumber->setRowTitle("batch_number");
//		$batchNumber->setTable("complaintExternal");
//		$batchNumber->setHelpId(10003);
//		$typeOfComplaintGroup->add($batchNumber);

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

//		$sapItems = new readonly("sapItems");
//		$sapItems->setGroup("typeOfComplaintGroup");
//		$sapItems->setDataType("string");
//		$sapItems->setRowTitle("material_number");
//		$sapItems->setTable("complaintExternal");
//		$sapItems->setHelpId(10012);
//		$typeOfComplaintGroup->add($sapItems);

//		$quantity = new readonly("quantity");
//		$quantity->setGroup("typeOfComplaintGroup");
//		$quantity->setDataType("string");
//		$quantity->setRowTitle("quantity_received");
//		$quantity->setTable("complaintExternal");
//		$quantity->setHelpId(10013);
//		$typeOfComplaintGroup->add($quantity);

//		$sp_quantityRecieved = new readonly("sp_quantityRecieved");
//		$sp_quantityRecieved->setGroup("typeOfComplaintGroup");
//		$sp_quantityRecieved->setDataType("string");
//		$sp_quantityRecieved->setRowTitle("quantity_received");
//		$sp_quantityRecieved->setTable("complaintExternal");
//		$sp_quantityRecieved->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_quantityRecieved);
//
//		$quantityUnderComplaint = new readonly("quantityUnderComplaint");
//		$quantityUnderComplaint->setGroup("typeOfComplaintGroup");
//		$quantityUnderComplaint->setDataType("string");
//		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
//		$quantityUnderComplaint->setTable("complaintExternal");
//		$quantityUnderComplaint->setHelpId(10014);
//		$typeOfComplaintGroup->add($quantityUnderComplaint);
//
//		$complaintValue = new readonly("complaintValue");
//		$complaintValue->setGroup("typeOfComplaintGroup");
//		$complaintValue->setDataType("string");
//		$complaintValue->setRowTitle("complaint_value");
//		$complaintValue->setTable("complaintExternal");
//		$complaintValue->setHelpId(10014);
//		$typeOfComplaintGroup->add($complaintValue);
//
//		$sp_additionalComplaintCost = new readonly("sp_additionalComplaintCost");
//		$sp_additionalComplaintCost->setGroup("typeOfComplaintGroup");
//		$sp_additionalComplaintCost->setDataType("string");
//		$sp_additionalComplaintCost->setRowTitle("additional_complaint_cost");
//		$sp_additionalComplaintCost->setTable("complaintExternal");
//		$sp_additionalComplaintCost->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_additionalComplaintCost);
//
//		$sp_detailsOfComplaintCost = new readonly("sp_detailsOfComplaintCost");
//		$sp_detailsOfComplaintCost->setGroup("typeOfComplaintGroup");
//		$sp_detailsOfComplaintCost->setDataType("string");
//		$sp_detailsOfComplaintCost->setRowTitle("details_of_complaint_cost");
//		$sp_detailsOfComplaintCost->setTable("complaintExternal");
//		$sp_detailsOfComplaintCost->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_detailsOfComplaintCost);
//
//		$materialGroup = new readonly("materialGroup");
//		$materialGroup->setGroup("typeOfComplaintGroup");
//		$materialGroup->setDataType("string");
//		$materialGroup->setRowTitle("material_group");
//		$materialGroup->setTable("complaintExternal");
//		$materialGroup->setHelpId(10014);
//		$typeOfComplaintGroup->add($materialGroup);
//
//		$sp_supplierItemNumber = new readonly("sp_supplierItemNumber");
//		$sp_supplierItemNumber->setGroup("typeOfComplaintGroup");
//		$sp_supplierItemNumber->setDataType("string");
//		$sp_supplierItemNumber->setRowTitle("supplier_item_number");
//		$sp_supplierItemNumber->setTable("complaintExternal");
//		$sp_supplierItemNumber->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_supplierItemNumber);
//
//		$sp_supplierProductDescription = new readonly("sp_supplierProductDescription");
//		$sp_supplierProductDescription->setGroup("typeOfComplaintGroup");
//		$sp_supplierProductDescription->setDataType("string");
//		$sp_supplierProductDescription->setRowTitle("supplier_product_description");
//		$sp_supplierProductDescription->setTable("complaintExternal");
//		$sp_supplierProductDescription->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_supplierProductDescription);
//
//		$sp_goodsReceivedDate = new readonly("sp_goodsReceivedDate");
//		$sp_goodsReceivedDate->setGroup("typeOfComplaintGroup");
//		$sp_goodsReceivedDate->setDataType("string");
//		$sp_goodsReceivedDate->setRowTitle("goods_received_date");
//		$sp_goodsReceivedDate->setTable("complaintExternal");
//		$sp_goodsReceivedDate->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_goodsReceivedDate);
//
//		$sp_goodsReceivedNumber = new readonly("sp_goodsReceivedNumber");
//		$sp_goodsReceivedNumber->setGroup("typeOfComplaintGroup");
//		$sp_goodsReceivedNumber->setDataType("string");
//		$sp_goodsReceivedNumber->setRowTitle("goods_received_number");
//		$sp_goodsReceivedNumber->setTable("complaintExternal");
//		$sp_goodsReceivedNumber->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_goodsReceivedNumber);
//
//		$sp_purchaseOrderNumber = new readonly("sp_purchaseOrderNumber");
//		$sp_purchaseOrderNumber->setGroup("typeOfComplaintGroup");
//		$sp_purchaseOrderNumber->setDataType("string");
//		$sp_purchaseOrderNumber->setRowTitle("purchase_order_number");
//		$sp_purchaseOrderNumber->setTable("complaintExternal");
//		$sp_purchaseOrderNumber->setHelpId(10014);
//		$typeOfComplaintGroup->add($sp_purchaseOrderNumber);
//
//		$g8d = new readonly("g8d");
//		$g8d->setGroup("typeOfComplaintGroup");
//		$g8d->setDataType("string");
//		$g8d->setRowTitle("full_8d_required");
//		$g8d->setTable("complaintExternal");
//		$g8d->setHelpId(10014);
//		$typeOfComplaintGroup->add($g8d);
//
//		$attachment = new attachment("attachment");
//		$attachment->setTempFileLocation("/apps/complaintsExternal/tmp");
//		$attachment->setFinalFileLocation("/apps/complaintsExternal/attachments");
//		$attachment->setHelpId(11);
//		$typeOfComplaintGroup->add($attachment);

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

//		$problemDescription = new readonly("problemDescription");
//		$problemDescription->setGroup("typeOfComplaintGroup");
//		$problemDescription->setDataType("string");
//		$problemDescription->setLabel("2 - Problem Description");
//		$problemDescription->setRowTitle("problem_description");
//		$problemDescription->setTable("complaintExternal");
//		$problemDescription->setHelpId(10015);
//		$typeOfComplaintGroup->add($problemDescription);
//
//		$sp_sampleSent = new readonly("sp_sampleSent");
//		$sp_sampleSent->setTable("complaintExternal");
//		$sp_sampleSent->setLength(20);
//		//$sp_sampleSent->setArraySource(array(
//		//	array('value' => 'Yes', 'display' => 'Yes'),
//		//	array('value' => 'No', 'display' => 'No')
//		//));
//		if(isset($savedFields["sampleForwarded"]))
//			$sp_sampleSent->setValue($savedFields["sampleForwarded"]);
//		$sp_sampleSent->setDataType("string");
//		$sp_sampleSent->setHelpId(10016);
//		$sp_sampleSent->setRowTitle("sample_forwarded_by_scapa");
//		$typeOfComplaintGroup->add($sp_sampleSent);
//
//		$sp_sampleSentDate = new readonly("sp_sampleSentDate");
//		$sp_sampleSentDate->setGroup("typeOfComplaintGroup");
//		$sp_sampleSentDate->setDataType("date");
//		$sp_sampleSentDate->setErrorMessage("textbox_date_error");
//		$sp_sampleSentDate->setRowTitle("sample_date");
//		$sp_sampleSentDate->setTable("complaintExternal");
//		$sp_sampleSentDate->setHelpId(10017);
//		$typeOfComplaintGroup->add($sp_sampleSentDate);

//		$sampleReceivedDate = new textbox("sampleReceivedDate");
//		$sampleReceivedDate->setGroup("typeOfComplaintGroup");
//		$sampleReceivedDate->setDataType("date");
//		$sampleReceivedDate->setErrorMessage("textbox_date_error");
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

//		$actionRequested = new readonly("actionRequested");
//		$actionRequested->setGroup("typeOfComplaintGroup");
//		$actionRequested->setDataType("string");
//		$actionRequested->setRowTitle("actions_by_scapa_to_minimise_problem");
//		$actionRequested->setTable("complaintExternal");
//		$actionRequested->setHelpId(10019);
//		$typeOfComplaintGroup->add($actionRequested);
//
//		$actionRequestedFromSupplier = new readonly("actionRequestedFromSupplier");
//		$actionRequestedFromSupplier->setGroup("typeOfComplaintGroup");
//		$actionRequestedFromSupplier->setDataType("string");
//		$actionRequestedFromSupplier->setRowTitle("actions_requested_from_supplier");
//		$actionRequestedFromSupplier->setTable("complaintExternal");
//		$actionRequestedFromSupplier->setHelpId(10019);
//		$typeOfComplaintGroup->add($actionRequestedFromSupplier);

		$teamLeader = new textbox("teamLeader");
		$teamLeader->setGroup("typeOfComplaintGroup");
		$teamLeader->setDataType("string");
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setRowTitle("person_responsible");
		$teamLeader->setTable("complaintExternal");
		$teamLeader->setHelpId(10018);
		$typeOfComplaintGroup->add($teamLeader);

//		$teamLeaderReadOnly = new readonly("teamLeaderReadOnly");
//		$teamLeaderReadOnly->setGroup("typeOfComplaintGroup");
//		$teamLeaderReadOnly->setDataType("text");
//		$teamLeaderReadOnly->setLength(255);
//		$teamLeaderReadOnly->setLabel("3 - Immediate Supplier Actions");
//		$teamLeaderReadOnly->setRowTitle("person_responsible");
//		$teamLeaderReadOnly->setVisible(false);
//		$teamLeaderReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup->add($teamLeaderReadOnly);

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

//		$verificationOfStockReadOnly = new readonly("verificationOfStockReadOnly");
//		$verificationOfStockReadOnly->setGroup("typeOfComplaintGroup");
//		$verificationOfStockReadOnly->setDataType("text");
//		$verificationOfStockReadOnly->setLength(255);
//		$verificationOfStockReadOnly->setRowTitle("verification_of_stock");
//		$verificationOfStockReadOnly->setLabel("3B - Verification Of Stock");
//		$verificationOfStockReadOnly->setVisible(false);
//		$verificationOfStockReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup->add($verificationOfStockReadOnly);


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

//		$goodJobInvoiceNoReadOnly = new readonly("goodJobInvoiceNoReadOnly");
//		$goodJobInvoiceNoReadOnly->setGroup("typeOfComplaintGroup1");
//		$goodJobInvoiceNoReadOnly->setDataType("text");
//		$goodJobInvoiceNoReadOnly->setLength(255);
//		$goodJobInvoiceNoReadOnly->setRowTitle("invoice_no");
//		$goodJobInvoiceNoReadOnly->setVisible(false);
//		$goodJobInvoiceNoReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup1->add($goodJobInvoiceNoReadOnly);

		$deliveryNote = new textbox("deliveryNote");
		$deliveryNote->setGroup("typeOfComplaintGroup");
		$deliveryNote->setDataType("string");
		$deliveryNote->setRowTitle("delivery_note");
		$deliveryNote->setTable("complaintExternal");
		$deliveryNote->setHelpId(10018);
		$typeOfComplaintGroup1->add($deliveryNote);

//		$deliveryNoteReadOnly = new readonly("deliveryNoteReadOnly");
//		$deliveryNoteReadOnly->setGroup("typeOfComplaintGroup1");
//		$deliveryNoteReadOnly->setDataType("text");
//		$deliveryNoteReadOnly->setLength(255);
//		$deliveryNoteReadOnly->setRowTitle("delivery_note");
//		$deliveryNoteReadOnly->setVisible(false);
//		$deliveryNoteReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup1->add($deliveryNoteReadOnly);

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

//		$containmentActionReadOnly = new readonly("containmentActionReadOnly");
//		$containmentActionReadOnly->setGroup("typeOfComplaintGroup1");
//		$containmentActionReadOnly->setDataType("text");
//		$containmentActionReadOnly->setLength(255);
//		//$containmentAction->setRowTitle("containment_action");
//		$containmentActionReadOnly->setRowTitle("containment_action");
//		$containmentActionReadOnly->setLabel("Containment Action");
//		$containmentActionReadOnly->setVisible(false);
//		$containmentActionReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup1->add($containmentActionReadOnly);

		$authorName = new textbox("authorName");
		$authorName->setGroup("typeOfComplaintGroup1");
		$authorName->setDataType("string");
		$authorName->setRowTitle("author_name");
		$authorName->setTable("complaintExternal");
		$authorName->setHelpId(10018);
		$typeOfComplaintGroup1->add($authorName);

//		$authorNameReadOnly = new readonly("authorNameReadOnly");
//		$authorNameReadOnly->setGroup("typeOfComplaintGroup1");
//		$authorNameReadOnly->setDataType("text");
//		$authorNameReadOnly->setLength(255);
//		$authorNameReadOnly->setRowTitle("author_name");
//		$authorNameReadOnly->setVisible(false);
//		$authorNameReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup1->add($authorNameReadOnly);

		$authorDate = new textbox("authorDate");
		$authorDate->setGroup("typeOfComplaintGroup1");
		$authorDate->setDataType("date");
		$authorDate->setRowTitle("author_date");
		$authorDate->setTable("complaintExternal");
		$authorDate->setHelpId(10018);
		$typeOfComplaintGroup1->add($authorDate);

//		$authorDateReadOnly = new readonly("authorDateReadOnly");
//		$authorDateReadOnly->setGroup("typeOfComplaintGroup1");
//		$authorDateReadOnly->setDataType("text");
//		$authorDateReadOnly->setLength(255);
//		$authorDateReadOnly->setRowTitle("author_date");
//		$authorDateReadOnly->setVisible(false);
//		$authorDateReadOnly->setHelpId(9037);
//		$typeOfComplaintGroup1->add($authorDateReadOnly);

		$confirmCollectionOfGoods = new textbox("confirmCollectionOfGoods");
		$confirmCollectionOfGoods->setGroup("typeOfComplaintGroup1");
		$confirmCollectionOfGoods->setDataType("date");
		$confirmCollectionOfGoods->setRowTitle("specified_date_for_collection_of_goods");
		$confirmCollectionOfGoods->setTable("complaintExternal");
		$confirmCollectionOfGoods->setLabel("Collection Details");
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
		$rootCausesyn->setRowTitle("root_cause_entered");
		$rootCausesyn->setRequired(false);
		$rootCausesyn->setVisible(false);
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
		$implementedActionsyn->setRowTitle("implemented_actions_entered");
		$implementedActionsyn->setRequired(false);
		$implementedActionsyn->setVisible(false);
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

		// Justify = No
		$estimatedDatePrev = new textbox("estimatedDatePrev");
		$estimatedDatePrev->setGroup("complaintJustifiedNoGroup");
		$estimatedDatePrev->setDataType("date");
		$estimatedDatePrev->setRowTitle("team_leader");
		$estimatedDatePrev->setRowTitle("preventive_action_verified_date");
		$estimatedDatePrev->setTable("complaintExternal");
		$estimatedDatePrev->setHelpId(10034);
		$complaintJustifiedNoGroup->add($estimatedDatePrev);

		$implementedPermanentCorrectiveActionValidated = new textarea("implementedPermanentCorrectiveActionValidated");
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
		$complaintJustifiedNoGroup->add($implementedPermanentCorrectiveActionValidatedDate);

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

	public function defineQualityForm()
	{
		$savedFields = array();

		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset))
			{
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
		$this->form = new form("evaluation" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
		$complaintGroup = new group("complaintGroup");
		$qu_verificationMadeYesGroup = new group("qu_verificationMadeYesGroup");
		$qu_otherMaterialEffectedGroup = new group("qu_otherMaterialEffectedGroup");
		$qu_otherMaterialEffectedYes = new group("qu_otherMaterialEffectedYes");
		$complaintGroup2 = new group("complaintGroup2");
		$qu_customerIssueActionGroup = new group("qu_customerIssueActionGroup");
		$qu_supplierIssueActionGroup = new group("qu_supplierIssueActionGroup");
		$complaintGroup3 = new group("complaintGroup3");
		$complaintGroup3GoodsGroup = new group("complaintGroup3GoodsGroup");
		$complaintGroup3RootCauseGroup = new group("complaintGroup3RootCauseGroup");
		$qu_useGoodsDerongationYesGroup = new group("qu_useGoodsDerongationYesGroup");
		$qu_customerApprovedYesGroup = new group("qu_customerApprovedYesGroup");
		$complaintGroup4 = new group("complaintGroup4");
		$complaintGroup4ContainmentActionGroup = new group("complaintGroup4ContainmentActionGroup");
		$complaintGroup4PossibleSolutionsGroup = new group("complaintGroup4PossibleSolutionsGroup");
		$complaintGroup4ImplementedActionsGroup = new group("complaintGroup4ImplementedActionsGroup");
		$complaintGroup41 = new group("complaintGroup41");
		$complaintGroup4PreventiveActionsGroup = new group("complaintGroup4PreventiveActionsGroup");
		$complaintGroup42 = new group("complaintGroup42");
		$riskAssessmentGroup = new group("riskAssessmentGroup");
		$riskAssessmentGroupYes = new group("riskAssessmentGroupYes");
		$managementSystemGroup = new group("managementSystemGroup");
		$managementSystemRefYes = new group("managementSystemRefYes");
		$fmeaGroup = new group("fmeaGroup");
		$fmeaDepGroup = new group("fmeaDepGroup");
		$customerSpecificationGroup = new group("customerSpecificationGroup");
		$customerSpecificationGroupYes = new group("customerSpecificationGroupYes");
		$flowChartGroup = new group("flowChartGroup");
		$flowChartGroupYes = new group("flowChartGroupYes");

		$sendToUser2 = new group("sendToUser2");
//		$transferOwnership2GroupYes2 = new multiplegroup("transferOwnership2GroupYes2");
//		$transferOwnership2GroupYes2->setTitle("Select someone to CC the below message to");
//		$transferOwnership2GroupYes2->setNextAction("evaluation");
//		$transferOwnership2GroupYes2->setAnchorRef("copy_to");
//		$transferOwnership2GroupYes2->setTable("ccGroup");
//		$transferOwnership2GroupYes2->setForeignKey("complaintId");
//		$transferOwnership2GroupYes2->setBorder(false);
		$sendToUser22 = new group("sendToUser22");


		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("evaluation");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
			$status->setValue($savedFields["status"]);
		$status->setTable("complaint");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("conclusion");
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$typeOfComplaint = new dropdown("typeOfComplaint");
		$typeOfComplaint->setGroup("typeOfComplaintGroup");
		$typeOfComplaint->setDataType("string");
		$typeOfComplaint->setXMLSource("./apps/complaints/xml/complaintType.xml");
		$typeOfComplaint->setRowTitle("complaint_type");
		$typeOfComplaint->setRequired(false);
//		$typeOfComplaint->setLabel("Complaint Type Details");
		$typeOfComplaint->setTable("complaint");
		$typeOfComplaint->setVisible(false);
		$typeOfComplaint->setHelpId(8199);
		if(isset($savedFields["typeOfComplaint"]))
			$typeOfComplaint->setValue($savedFields["typeOfComplaint"]);
		else $typeOfComplaint->setValue("quality_complaint");
		$typeOfComplaintGroup->add($typeOfComplaint);

		$teamLeader = new textbox("teamLeader");
		if(isset($savedFields["teamLeader"]))
		$teamLeader->setValue($savedFields["teamLeader"]);
		$teamLeader->setGroup("complaintGroup");
		$teamLeader->setDataType("string");
		$teamLeader->setLength(255);
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setRequired(false);
		$teamLeader->setTable("evaluation");
		$teamLeader->setHelpId(9012);
		$complaintGroup->add($teamLeader);

		$teamMember = new textarea("teamMember");
		if(isset($savedFields["teamMember"]))
		$teamMember->setValue($savedFields["teamMember"]);
		$teamMember->setGroup("complaintGroup");
		$teamMember->setDataType("text");
		$teamMember->setRowTitle("team_member");
		$teamMember->setRequired(false);
		$teamMember->setTable("evaluation");
		$teamMember->setHelpId(9013);
		$complaintGroup->add($teamMember);

		$qu_verificationMade = new radio("qu_verificationMade");
		$qu_verificationMade->setGroup("complaintGroup");
		$qu_verificationMade->setDataType("string");
		$qu_verificationMade->setLength(5);
		$qu_verificationMade->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
			));
		$qu_verificationMade->setRowTitle("verification_made");
		$qu_verificationMade->setRequired(false); // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_verificationMade"]))
			$qu_verificationMade->setValue($savedFields["qu_verificationMade"]);
		else $qu_verificationMade->setValue("NO");
		$qu_verificationMade->setTable("evaluation");
		$qu_verificationMade->setHelpId(9018);

		// Dependency
		$qu_verificationMade_dependency = new dependency();
		$qu_verificationMade_dependency->addRule(new rule('complaintGroup', 'qu_verificationMade', 'YES'));
		$qu_verificationMade_dependency->setGroup('qu_verificationMadeYesGroup');
		$qu_verificationMade_dependency->setShow(true);

		$qu_verificationMade->addControllingDependency($qu_verificationMade_dependency);
		$complaintGroup->add($qu_verificationMade);

		$qu_verificationName = new textbox("qu_verificationName");
		if(isset($savedFields["qu_verificationName"]))
		$qu_verificationName->setValue($savedFields["qu_verificationName"]);
		$qu_verificationName->setGroup("qu_verificationMadeYesGroup");
		$qu_verificationName->setDataType("string");
		$qu_verificationName->setLength(255);
		$qu_verificationName->setRowTitle("stock_verification_name");
		$qu_verificationName->setRequired(false);
		$qu_verificationName->setTable("evaluation");
		$qu_verificationName->setHelpId(9012);
		$qu_verificationMadeYesGroup->add($qu_verificationName);

		$qu_verificationDate = new calendar("qu_verificationDate");
		if(isset($savedFields["qu_verificationDate"]))
		$qu_verificationDate->setValue($savedFields["qu_verificationDate"]);
		$qu_verificationDate->setGroup("qu_verificationMadeYesGroup");
		$qu_verificationDate->setDataType("date");
		$qu_verificationDate->setErrorMessage("textbox_date_error");
		$qu_verificationDate->setRowTitle("stock_verification_date");
		$qu_verificationDate->setRequired(false);
		$qu_verificationDate->setTable("evaluation");
		$qu_verificationDate->setHelpId(9012);
		$qu_verificationMadeYesGroup->add($qu_verificationDate);

		$qu_otherMaterialEffected = new radio("qu_otherMaterialEffected");
		$qu_otherMaterialEffected->setGroup("qu_otherMaterialEffectedGroup");
		$qu_otherMaterialEffected->setDataType("string");
		$qu_otherMaterialEffected->setLength(5);
		$qu_otherMaterialEffected->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
			));
		$qu_otherMaterialEffected->setRowTitle("qu_other_material_effected");
		$qu_otherMaterialEffected->setRequired(false);  // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_otherMaterialEffected"]))
			$qu_otherMaterialEffected->setValue($savedFields["qu_otherMaterialEffected"]);
		else $qu_otherMaterialEffected->setValue("NO");
		$qu_otherMaterialEffected->setTable("evaluation");
		$qu_otherMaterialEffected->setHelpId(9018);

		// Dependency
		$qu_otherMaterialEffected_dependency = new dependency();
		$qu_otherMaterialEffected_dependency->addRule(new rule('qu_otherMaterialEffectedGroup', 'qu_otherMaterialEffected', 'YES'));
		$qu_otherMaterialEffected_dependency->setGroup('qu_otherMaterialEffectedYes');
		$qu_otherMaterialEffected_dependency->setShow(true);

		$qu_otherMaterialEffected->addControllingDependency($qu_otherMaterialEffected_dependency);
		$qu_otherMaterialEffectedGroup->add($qu_otherMaterialEffected);

		$qu_otherMatDetails = new textarea("qu_otherMatDetails");
		if(isset($savedFields["qu_otherMatDetails"]))
		$qu_otherMatDetails->setValue($savedFields["qu_otherMatDetails"]);
		$qu_otherMatDetails->setGroup("qu_otherMaterialEffectedYes");
		$qu_otherMatDetails->setDataType("text");
		$qu_otherMatDetails->setRowTitle("qu_other_mat_details");
		$qu_otherMatDetails->setRequired(false);
		$qu_otherMatDetails->setTable("evaluation");
		$qu_otherMatDetails->setHelpId(9013);
		$qu_otherMaterialEffectedYes->add($qu_otherMatDetails);

		$analysis = new textarea("analysis");
		if(isset($savedFields["analysis"]))
		$analysis->setValue($savedFields["analysis"]);
		//else
		//$analysis->setValue("ALLY");
		$analysis->setGroup("complaintGroup2");
		$analysis->setDataType("text");
		$analysis->setRowTitle("analysis");
		$analysis->setRequired(false);
		$analysis->setTable("evaluation");
		$analysis->setHelpId(9005);
		$complaintGroup2->add($analysis);

		$analysisyn = new radio("analysisyn");
		$analysisyn->setGroup("complaintGroup2");
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
		$analysisyn->setTable("complaint");
		$complaintGroup2->add($analysisyn);

		$author = new textbox("author");
		if(isset($savedFields["author"]))
		$author->setValue($savedFields["author"]);
		$author->setGroup("complaintGroup2");
		$author->setDataType("string");
		$author->setLength(255);
		$author->setRowTitle("author");
		$author->setRequired(false);
		$author->setTable("evaluation");
		$author->setHelpId(9006);
		$complaintGroup2->add($author);

		$analysisDate = new calendar("analysisDate");
		if(isset($savedFields["analysisDate"]))
		$analysisDate->setValue($savedFields["analysisDate"]);
		$analysisDate->setGroup("complaintGroup2");
		$analysisDate->setDataType("date");
		$analysisDate->setErrorMessage("textbox_date_error");
		$analysisDate->setLength(30);
		$analysisDate->setRowTitle("analysis_date");
		$analysisDate->setRequired(false);
		$analysisDate->setTable("evaluation");//was complaintEval???
		$analysisDate->setHelpId(9007);
		$complaintGroup2->add($analysisDate);

		$qu_supplierIssue = new radio("qu_supplierIssue");
		$qu_supplierIssue->setGroup("complaintGroup2");
		$qu_supplierIssue->setDataType("string");
		$qu_supplierIssue->setLength(3);
		$qu_supplierIssue->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_supplierIssue->setRowTitle("issue_supplier_complaint");
		$qu_supplierIssue->setRequired(false);
		if(isset($savedFields["qu_supplierIssue"]))
			$qu_supplierIssue->setValue($savedFields["qu_supplierIssue"]);
		else $qu_supplierIssue->setValue("No");
		$qu_supplierIssue->setTable("evaluation");
		$qu_supplierIssue->setOnKeyPress("qu_supplierIssue_alert()");

		// Dependency
		$qu_supplierIssue_dependency = new dependency();
		$qu_supplierIssue_dependency->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency->setGroup('qu_supplierIssueActionGroup');
		$qu_supplierIssue_dependency->setShow(false);

		$qu_supplierIssue_dependency2 = new dependency();
		$qu_supplierIssue_dependency2->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency2->setGroup('complaintGroup3');
		$qu_supplierIssue_dependency2->setShow(false);

		$qu_supplierIssue_dependency20 = new dependency();
		$qu_supplierIssue_dependency20->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency20->setGroup('complaintGroup3GoodsGroup');
		$qu_supplierIssue_dependency20->setShow(false);

		$qu_supplierIssue_dependency21 = new dependency();
		$qu_supplierIssue_dependency21->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency21->setGroup('complaintGroup3RootCauseGroup');
		$qu_supplierIssue_dependency21->setShow(false);

		$qu_supplierIssue_dependency3 = new dependency();
		$qu_supplierIssue_dependency3->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency3->setGroup('complaintGroup4');
		$qu_supplierIssue_dependency3->setShow(false);

		$qu_supplierIssue_dependency4 = new dependency();
		$qu_supplierIssue_dependency4->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency4->setGroup('complaintGroup4ContainmentActionGroup');
		$qu_supplierIssue_dependency4->setShow(false);

		$qu_supplierIssue_dependency5 = new dependency();
		$qu_supplierIssue_dependency5->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency5->setGroup('complaintGroup4PossibleSolutionsGroup');
		$qu_supplierIssue_dependency5->setShow(false);

		$qu_supplierIssue_dependency6 = new dependency();
		$qu_supplierIssue_dependency6->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency6->setGroup('complaintGroup4ImplementedActionsGroup');
		$qu_supplierIssue_dependency6->setShow(false);

		$qu_supplierIssue_dependency7 = new dependency();
		$qu_supplierIssue_dependency7->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency7->setGroup('complaintGroup41');
		$qu_supplierIssue_dependency7->setShow(false);

		$qu_supplierIssue_dependency8 = new dependency();
		$qu_supplierIssue_dependency8->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency8->setGroup('complaintGroup4PreventiveActionsGroup');
		$qu_supplierIssue_dependency8->setShow(false);

		$qu_supplierIssue_dependency9 = new dependency();
		$qu_supplierIssue_dependency9->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency9->setGroup('complaintGroup42');
		$qu_supplierIssue_dependency9->setShow(false);

		$qu_supplierIssue_dependency10 = new dependency();
		$qu_supplierIssue_dependency10->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency10->setGroup('riskAssessmentGroup');
		$qu_supplierIssue_dependency10->setShow(false);

		$qu_supplierIssue_dependency11 = new dependency();
		$qu_supplierIssue_dependency11->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency11->setGroup('managementSystemGroup');
		$qu_supplierIssue_dependency11->setShow(false);

		$qu_supplierIssue_dependency12 = new dependency();
		$qu_supplierIssue_dependency12->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency12->setGroup('fmeaGroup');
		$qu_supplierIssue_dependency12->setShow(false);

		$qu_supplierIssue_dependency13 = new dependency();
		$qu_supplierIssue_dependency13->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency13->setGroup('customerSpecificationGroup');
		$qu_supplierIssue_dependency13->setShow(false);

		$qu_supplierIssue_dependency14 = new dependency();
		$qu_supplierIssue_dependency14->addRule(new rule('complaintGroup2', 'qu_supplierIssue', 'Yes'));
		$qu_supplierIssue_dependency14->setGroup('flowChartGroup');
		$qu_supplierIssue_dependency14->setShow(false);

		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency2);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency20);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency21);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency3);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency4);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency5);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency6);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency7);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency8);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency9);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency10);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency11);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency12);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency13);
		$qu_supplierIssue->addControllingDependency($qu_supplierIssue_dependency14);
		$complaintGroup2->add($qu_supplierIssue);

		$qu_customerIssue = new radio("qu_customerIssue");
		$qu_customerIssue->setGroup("complaintGroup2");
		$qu_customerIssue->setDataType("string");
		$qu_customerIssue->setLength(3);
		$qu_customerIssue->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_customerIssue->setRowTitle("issue_customer_complaint");
		$qu_customerIssue->setRequired(false);
		if(isset($savedFields["qu_customerIssue"]))
			$qu_customerIssue->setValue($savedFields["qu_customerIssue"]);
		else $qu_customerIssue->setValue("No");
		$qu_customerIssue->setTable("evaluation");
		$qu_customerIssue->setOnKeyPress("qu_customerIssue_alert()");

		// Dependency
		$qu_customerIssue_dependency = new dependency();
		$qu_customerIssue_dependency->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency->setGroup('qu_supplierIssueActionGroup');
		$qu_customerIssue_dependency->setShow(false);

		$qu_customerIssue_dependency2 = new dependency();
		$qu_customerIssue_dependency2->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency2->setGroup('complaintGroup3');
		$qu_customerIssue_dependency2->setShow(false);

		$qu_customerIssue_dependency20 = new dependency();
		$qu_customerIssue_dependency20->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency20->setGroup('complaintGroup3GoodsGroup');
		$qu_customerIssue_dependency20->setShow(false);

		$qu_customerIssue_dependency21 = new dependency();
		$qu_customerIssue_dependency21->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency21->setGroup('complaintGroup3RootCauseGroup');
		$qu_customerIssue_dependency21->setShow(false);

		$qu_customerIssue_dependency3 = new dependency();
		$qu_customerIssue_dependency3->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency3->setGroup('complaintGroup4');
		$qu_customerIssue_dependency3->setShow(false);

		$qu_customerIssue_dependency4 = new dependency();
		$qu_customerIssue_dependency4->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency4->setGroup('complaintGroup4ContainmentActionGroup');
		$qu_customerIssue_dependency4->setShow(false);

		$qu_customerIssue_dependency5 = new dependency();
		$qu_customerIssue_dependency5->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency5->setGroup('complaintGroup4PossibleSolutionsGroup');
		$qu_customerIssue_dependency5->setShow(false);

		$qu_customerIssue_dependency6 = new dependency();
		$qu_customerIssue_dependency6->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency6->setGroup('complaintGroup4ImplementedActionsGroup');
		$qu_customerIssue_dependency6->setShow(false);

		$qu_customerIssue_dependency7 = new dependency();
		$qu_customerIssue_dependency7->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency7->setGroup('complaintGroup41');
		$qu_customerIssue_dependency7->setShow(false);

		$qu_customerIssue_dependency8 = new dependency();
		$qu_customerIssue_dependency8->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency8->setGroup('complaintGroup4PreventiveActionsGroup');
		$qu_customerIssue_dependency8->setShow(false);

		$qu_customerIssue_dependency9 = new dependency();
		$qu_customerIssue_dependency9->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency9->setGroup('complaintGroup42');
		$qu_customerIssue_dependency9->setShow(false);

		$qu_customerIssue_dependency10 = new dependency();
		$qu_customerIssue_dependency10->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency10->setGroup('riskAssessmentGroup');
		$qu_customerIssue_dependency10->setShow(false);

		$qu_customerIssue_dependency11 = new dependency();
		$qu_customerIssue_dependency11->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency11->setGroup('managementSystemGroup');
		$qu_customerIssue_dependency11->setShow(false);

		$qu_customerIssue_dependency12 = new dependency();
		$qu_customerIssue_dependency12->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency12->setGroup('fmeaGroup');
		$qu_customerIssue_dependency12->setShow(false);

		$qu_customerIssue_dependency13 = new dependency();
		$qu_customerIssue_dependency13->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency13->setGroup('customerSpecificationGroup');
		$qu_customerIssue_dependency13->setShow(false);

		$qu_customerIssue_dependency14 = new dependency();
		$qu_customerIssue_dependency14->addRule(new rule('qu_customerIssueActionGroup', 'qu_customerIssue', 'Yes'));
		$qu_customerIssue_dependency14->setGroup('flowChartGroup');
		$qu_customerIssue_dependency14->setShow(false);

		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency2);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency20);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency21);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency3);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency4);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency5);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency6);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency7);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency8);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency9);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency10);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency11);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency12);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency13);
		$qu_customerIssue->addControllingDependency($qu_customerIssue_dependency14);
		$qu_customerIssueActionGroup->add($qu_customerIssue);

		$qu_supplierIssueAction = new radio("qu_supplierIssueAction");
		$qu_supplierIssueAction->setGroup("qu_supplierIssueActionGroup");
		$qu_supplierIssueAction->setDataType("string");
		$qu_supplierIssueAction->setLength(3);
		$qu_supplierIssueAction->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$qu_supplierIssueAction->setRowTitle("complaint_will_be_actioned");
		$qu_supplierIssueAction->setRequired(false);
		if(isset($savedFields["qu_supplierIssueAction"]))
			$qu_supplierIssueAction->setValue($savedFields["qu_supplierIssueAction"]);
		else $qu_supplierIssueAction->setValue("Yes");
		$qu_supplierIssueAction->setTable("evaluation");
		$qu_supplierIssueAction->setOnKeyPress("overwrite_dependencies_supplier_issue_action()");

		// Dependency
		$qu_supplierIssueAction_dependency = new dependency();
		$qu_supplierIssueAction_dependency->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency->setGroup('complaintGroup3');
		$qu_supplierIssueAction_dependency->setShow(false);

		$qu_supplierIssueAction_dependency2 = new dependency();
		$qu_supplierIssueAction_dependency2->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency2->setGroup('complaintGroup4');
		$qu_supplierIssueAction_dependency2->setShow(false);

		$qu_supplierIssueAction_dependency21 = new dependency();
		$qu_supplierIssueAction_dependency21->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency21->setGroup('complaintGroup41');
		$qu_supplierIssueAction_dependency21->setShow(false);

		$qu_supplierIssueAction_dependency22 = new dependency();
		$qu_supplierIssueAction_dependency22->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency22->setGroup('complaintGroup42');
		$qu_supplierIssueAction_dependency22->setShow(false);

		$qu_supplierIssueAction_dependency3 = new dependency();
		$qu_supplierIssueAction_dependency3->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency3->setGroup('riskAssessmentGroup');
		$qu_supplierIssueAction_dependency3->setShow(false);

		$qu_supplierIssueAction_dependency4 = new dependency();
		$qu_supplierIssueAction_dependency4->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency4->setGroup('managementSystemGroup');
		$qu_supplierIssueAction_dependency4->setShow(false);

		$qu_supplierIssueAction_dependency5 = new dependency();
		$qu_supplierIssueAction_dependency5->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency5->setGroup('fmeaGroup');
		$qu_supplierIssueAction_dependency5->setShow(false);

		$qu_supplierIssueAction_dependency6 = new dependency();
		$qu_supplierIssueAction_dependency6->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency6->setGroup('customerSpecificationGroup');
		$qu_supplierIssueAction_dependency6->setShow(false);

		$qu_supplierIssueAction_dependency7 = new dependency();
		$qu_supplierIssueAction_dependency7->addRule(new rule('qu_supplierIssueActionGroup', 'qu_supplierIssueAction', 'No'));
		$qu_supplierIssueAction_dependency7->setGroup('flowChartGroup');
		$qu_supplierIssueAction_dependency7->setShow(false);

		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency2);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency21);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency22);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency3);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency4);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency5);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency6);
		$qu_supplierIssueAction->addControllingDependency($qu_supplierIssueAction_dependency7);
		$qu_supplierIssueActionGroup->add($qu_supplierIssueAction);

		$qu_supplierIssueActionReason = new textarea("qu_supplierIssueActionReason");
		if(isset($savedFields["qu_supplierIssueActionReason"]))
		$qu_supplierIssueActionReason->setValue($savedFields["qu_supplierIssueActionReason"]);
		$qu_supplierIssueActionReason->setGroup("complaintGroup3RootCauseGroup");
		$qu_supplierIssueActionReason->setDataType("text");
		$qu_supplierIssueActionReason->setRowTitle("reason_for_complaint");
		$qu_supplierIssueActionReason->setRequired(false);
		$qu_supplierIssueActionReason->setTable("evaluation");
		$qu_supplierIssueActionReason->setHelpId(9014);
		$complaintGroup3RootCauseGroup->add($qu_supplierIssueActionReason);

		$rootCauses = new textarea("rootCauses");
		if(isset($savedFields["rootCauses"]))
		$rootCauses->setValue($savedFields["rootCauses"]);
		$rootCauses->setGroup("complaintGroup3RootCauseGroup");
		$rootCauses->setDataType("text");
		$rootCauses->setRowTitle("root_causes");
		$rootCauses->setRequired(false);
		$rootCauses->setTable("evaluation");
		$rootCauses->setHelpId(9014);
		$complaintGroup3RootCauseGroup->add($rootCauses);

		$rootCausesyn = new radio("rootCausesyn");
		$rootCausesyn->setGroup("complaintGroup3RootCauseGroup");
		$rootCausesyn->setDataType("string");
		$rootCausesyn->setLength(3);
		$rootCausesyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$rootCausesyn->setRowTitle("rootCauses_entered");
		$rootCausesyn->setRequired(false);
		$rootCausesyn->setVisible(false);
		if(isset($savedFields["rootCausesyn"]))
		$rootCausesyn->setValue($savedFields["rootCausesyn"]);
		else $rootCausesyn->setValue("No");
		$rootCausesyn->setTable("complaint");
		$complaintGroup3RootCauseGroup->add($rootCausesyn);

		$rootCausesAuthor = new textbox("rootCausesAuthor");
		if(isset($savedFields["rootCausesAuthor"]))
		$rootCausesAuthor->setValue($savedFields["rootCausesAuthor"]);
		$rootCausesAuthor->setGroup("complaintGroup3RootCauseGroup");
		$rootCausesAuthor->setDataType("string");
		$rootCausesAuthor->setLength(255);
		$rootCausesAuthor->setRowTitle("root_causes_author");
		$rootCausesAuthor->setRequired(false);
		$rootCausesAuthor->setTable("evaluation");
		$rootCausesAuthor->setHelpId(9015);
		$complaintGroup3RootCauseGroup->add($rootCausesAuthor);

		$rootCausesDate = new calendar("rootCausesDate");
		if(isset($savedFields["rootCausesDate"]))
		$rootCausesDate->setValue($savedFields["rootCausesDate"]);
		$rootCausesDate->setGroup("complaintGroup3RootCauseGroup");
		$rootCausesDate->setDataType("date");
		$rootCausesDate->setErrorMessage("textbox_date_error");
		$rootCausesDate->setRowTitle("root_causes_date");
		$rootCausesDate->setRequired(false);
		$rootCausesDate->setTable("evaluation");
		$rootCausesDate->setHelpId(9016);
		$complaintGroup3RootCauseGroup->add($rootCausesDate);

		$attributableProcess = new textbox("attributableProcess");
		if(isset($savedFields["attributableProcess"]))
		$attributableProcess->setValue($savedFields["attributableProcess"]);
		$attributableProcess->setGroup("complaintGroup3");
		$attributableProcess->setDataType("string");
		$attributableProcess->setLength(255);
		$attributableProcess->setRowTitle("attributable_process");
		$attributableProcess->setRequired(false);
		$attributableProcess->setTable("evaluation");
		$attributableProcess->setHelpId(9015);
		$complaintGroup3->add($attributableProcess);

		$failureCode = new textbox("failureCode");
		if(isset($savedFields["failureCode"]))
		$failureCode->setValue($savedFields["failureCode"]);
		$failureCode->setGroup("complaintGroup3");
		$failureCode->setDataType("string");
		$failureCode->setLength(255);
		$failureCode->setRowTitle("failure_code");
		$failureCode->setRequired(false);
		$failureCode->setTable("evaluation");
		$failureCode->setHelpId(9037);
		$complaintGroup3->add($failureCode);

		$rootCauseCode = new textbox("rootCauseCode");
		if(isset($savedFields["rootCauseCode"]))
		$rootCauseCode->setValue($savedFields["rootCauseCode"]);
		$rootCauseCode->setGroup("complaintGroup3");
		$rootCauseCode->setDataType("string");
		$rootCauseCode->setLength(255);
		$rootCauseCode->setRowTitle("root_cause_code");
		$rootCauseCode->setRequired(false);
		$rootCauseCode->setTable("evaluation");
		$rootCauseCode->setHelpId(9038);
		$complaintGroup3->add($rootCauseCode);

		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments/eval");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(9008);
		$attachment->setNextAction("evaluation");
		$complaintGroup3->add($attachment);

		$disposeGoods = new radio("disposeGoods");
		$disposeGoods->setGroup("complaintGroup3GoodsGroup");
		$disposeGoods->setDataType("string");
		$disposeGoods->setLength(5);
		$disposeGoods->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$disposeGoods->setRowTitle("dispose_goods");
		$disposeGoods->setRequired(true);
		if(isset($savedFields["disposeGoods"]))
			$disposeGoods->setValue($savedFields["disposeGoods"]);
		else $disposeGoods->setValue("NO");
		$disposeGoods->setTable("evaluation");
		$disposeGoods->setHelpId(9018);
		$complaintGroup3GoodsGroup->add($disposeGoods);

		$qu_useGoods = new radio("qu_useGoods");
		$qu_useGoods->setGroup("complaintGroup3GoodsGroup");
		$qu_useGoods->setDataType("string");
		$qu_useGoods->setLength(5);
		$qu_useGoods->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$qu_useGoods->setRowTitle("use_goods");
		$qu_useGoods->setRequired(false); // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_useGoods"]))
			$qu_useGoods->setValue($savedFields["qu_useGoods"]);
		else $qu_useGoods->setValue("NO");
		$qu_useGoods->setTable("evaluation");
		$qu_useGoods->setHelpId(9018);
		$complaintGroup3GoodsGroup->add($qu_useGoods);

		$qu_useGoodsDerongation = new radio("qu_useGoodsDerongation");
		$qu_useGoodsDerongation->setGroup("complaintGroup3GoodsGroup");
		$qu_useGoodsDerongation->setDataType("string");
		$qu_useGoodsDerongation->setLength(5);
		$qu_useGoodsDerongation->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$qu_useGoodsDerongation->setRowTitle("use_goods_derongation");
		$qu_useGoodsDerongation->setRequired(false); // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_useGoodsDerongation"]))
			$qu_useGoodsDerongation->setValue($savedFields["qu_useGoodsDerongation"]);
		else $qu_useGoodsDerongation->setValue("NO");
		$qu_useGoodsDerongation->setTable("evaluation");
		$qu_useGoodsDerongation->setHelpId(9018);

		// Dependency
		$qu_useGoodsDerongation_dependency = new dependency();
		$qu_useGoodsDerongation_dependency->addRule(new rule('complaintGroup3GoodsGroup', 'qu_useGoodsDerongation', 'Yes'));
		$qu_useGoodsDerongation_dependency->setGroup('qu_useGoodsDerongationYesGroup');
		$qu_useGoodsDerongation_dependency->setShow(true);

		$qu_useGoodsDerongation->addControllingDependency($qu_useGoodsDerongation_dependency);
		$complaintGroup3GoodsGroup->add($qu_useGoodsDerongation);

		$qu_customerApproved = new radio("qu_customerApproved");
		$qu_customerApproved->setGroup("qu_useGoodsDerongationYesGroup");
		$qu_customerApproved->setDataType("string");
		$qu_customerApproved->setLength(5);
		$qu_customerApproved->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$qu_customerApproved->setRowTitle("customer_approved");
		$qu_customerApproved->setRequired(true);
		if(isset($savedFields["qu_customerApproved"]))
			$qu_customerApproved->setValue($savedFields["qu_customerApproved"]);
		else $qu_customerApproved->setValue("NO");
		$qu_customerApproved->setTable("evaluation");
		$qu_customerApproved->setHelpId(9018);

		// Dependency
		$qu_customerApproved_dependency = new dependency();
		$qu_customerApproved_dependency->addRule(new rule('qu_useGoodsDerongationYesGroup', 'qu_customerApproved', 'YES'));
		$qu_customerApproved_dependency->setGroup('qu_customerApprovedYesGroup');
		$qu_customerApproved_dependency->setShow(true);

		$qu_customerApproved->addControllingDependency($qu_customerApproved_dependency);
		$qu_useGoodsDerongationYesGroup->add($qu_customerApproved);

		$qu_nameOfCustomer = new textbox("qu_nameOfCustomer");
		if(isset($savedFields["qu_nameOfCustomer"]))
		$qu_nameOfCustomer->setValue($savedFields["qu_nameOfCustomer"]);
		$qu_nameOfCustomer->setGroup("qu_customerApprovedYesGroup");
		$qu_nameOfCustomer->setDataType("string");
		$qu_nameOfCustomer->setLength(255);
		$qu_nameOfCustomer->setRowTitle("name_of_customer");
		$qu_nameOfCustomer->setRequired(false);
		$qu_nameOfCustomer->setTable("evaluation");
		$qu_nameOfCustomer->setHelpId(9038);
		$qu_customerApprovedYesGroup->add($qu_nameOfCustomer);

		$qu_reworkGoods = new radio("qu_reworkGoods");
		$qu_reworkGoods->setGroup("complaintGroup4");
		$qu_reworkGoods->setDataType("string");
		$qu_reworkGoods->setLength(5);
		$qu_reworkGoods->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$qu_reworkGoods->setRowTitle("rework_goods");
		$qu_reworkGoods->setRequired(false); // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_reworkGoods"]))
		$qu_reworkGoods->setValue($savedFields["qu_reworkGoods"]);
		else $qu_reworkGoods->setValue("NO");
		$qu_reworkGoods->setTable("evaluation");
		$qu_reworkGoods->setHelpId(9018);
		$complaintGroup4->add($qu_reworkGoods);

		$qu_otherSimilarProducts = new radio("qu_otherSimilarProducts");
		$qu_otherSimilarProducts->setGroup("complaintGroup4");
		$qu_otherSimilarProducts->setDataType("string");
		$qu_otherSimilarProducts->setLength(5);
		$qu_otherSimilarProducts->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$qu_otherSimilarProducts->setRowTitle("other_similar_products_recalled");
		$qu_otherSimilarProducts->setRequired(false); // Changed by JM/RM - 14:57 - 20/08/2012
		if(isset($savedFields["qu_otherSimilarProducts"]))
			$qu_otherSimilarProducts->setValue($savedFields["qu_otherSimilarProducts"]);
		else $qu_otherSimilarProducts->setValue("NO");
		$qu_otherSimilarProducts->setTable("evaluation");
		$qu_otherSimilarProducts->setHelpId(9018);
		$complaintGroup4->add($qu_otherSimilarProducts);

		$qu_authorGoodsDecision = new textbox("qu_authorGoodsDecision");
		if(isset($savedFields["complaintGroup4"]))
		$qu_authorGoodsDecision->setValue($savedFields["qu_authorGoodsDecision"]);
		$qu_authorGoodsDecision->setGroup("complaintGroup4");
		$qu_authorGoodsDecision->setDataType("string");
		$qu_authorGoodsDecision->setLength(255);
		$qu_authorGoodsDecision->setRowTitle("author_for_goods_decision");
		$qu_authorGoodsDecision->setRequired(false);
		$qu_authorGoodsDecision->setTable("evaluation");
		$qu_authorGoodsDecision->setHelpId(9038);
		$complaintGroup4->add($qu_authorGoodsDecision);

		$qu_authorGoodsDecisionDate = new calendar("qu_authorGoodsDecisionDate");
		if(isset($savedFields["complaintGroup4"]))
		$qu_authorGoodsDecisionDate->setValue($savedFields["qu_authorGoodsDecisionDate"]);
		$qu_authorGoodsDecisionDate->setGroup("complaintGroup4");
		$qu_authorGoodsDecisionDate->setDataType("date");
		$qu_authorGoodsDecisionDate->setErrorMessage("textbox_date_error");
		$qu_authorGoodsDecisionDate->setLength(255);
		$qu_authorGoodsDecisionDate->setRowTitle("author_for_goods_decision_date");
		$qu_authorGoodsDecisionDate->setRequired(false);
		$qu_authorGoodsDecisionDate->setTable("evaluation");
		$qu_authorGoodsDecisionDate->setHelpId(9038);
		$complaintGroup4->add($qu_authorGoodsDecisionDate);

		//$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
		//$fields8D = mysql_fetch_array($dataset8D);
		//		if($rowCategoryM > 0 || $rowCategoryD > 0)
		//if($fields8D['g8d'] == "yes")
		//{
			$containmentAction = new textarea("containmentAction");
			if(isset($savedFields["containmentAction"]))
			$containmentAction->setValue($savedFields["containmentAction"]);
			/*
			$containmentAction->setGroup("complaintGroup4");
			$containmentAction->setDataType("string");
			//$containmentAction->setLength(1000);
			//echo "HERE";exit;
			$containmentAction->setRowTitle("containment_actions");
			$containmentAction->setRequired(false);
			$containmentAction->setTable("evaluation");
			$containmentAction->setHelpId(9087);
			*/

			$containmentAction->setGroup("complaintGroup4ContainmentActionGroup");
			$containmentAction->setDataType("text");
			$containmentAction->setRowTitle("containment_actions");
			$containmentAction->setRequired(false);
			$containmentAction->setTable("evaluation");
			$containmentAction->setHelpId(9087);

			$complaintGroup4ContainmentActionGroup->add($containmentAction);

			$containmentActionyn = new radio("containmentActionyn");
			$containmentActionyn->setGroup("complaintGroup4ContainmentActionGroup");
			$containmentActionyn->setDataType("string");
			$containmentActionyn->setLength(3);
			$containmentActionyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$containmentActionyn->setRowTitle("containmentAction_entered");
			$containmentActionyn->setRequired(false);
			$containmentActionyn->setVisible(false);
			if(isset($savedFields["containmentActionyn"]))
			$containmentActionyn->setValue($savedFields["containmentActionyn"]);
			else $containmentActionyn->setValue("No");
			$containmentActionyn->setTable("complaint");
			$complaintGroup4ContainmentActionGroup->add($containmentActionyn);

			$containmentActionAuthor = new textbox("containmentActionAuthor");
			if(isset($savedFields["containmentActionAuthor"]))
			$containmentActionAuthor->setValue($savedFields["containmentActionAuthor"]);
			$containmentActionAuthor->setGroup("complaintGroup4ContainmentActionGroup");
			$containmentActionAuthor->setDataType("string");
			$containmentActionAuthor->setLength(255);
			$containmentActionAuthor->setRowTitle("containment_actions_author");
			$containmentActionAuthor->setRequired(false);
			$containmentActionAuthor->setTable("evaluation");
			$containmentActionAuthor->setHelpId(9039);
			$complaintGroup4ContainmentActionGroup->add($containmentActionAuthor);

			$containmentActionDate = new calendar("containmentActionDate");
			if(isset($savedFields["containmentActionDate"]))
			$containmentActionDate->setValue($savedFields["containmentActionDate"]);
			$containmentActionDate->setGroup("complaintGroup4ContainmentActionGroup");
			$containmentActionDate->setDataType("date");
			$containmentActionDate->setErrorMessage("textbox_date_error");
			$containmentActionDate->setLength(255);
			$containmentActionDate->setRowTitle("containment_actions_date");
			$containmentActionDate->setRequired(false);
			$containmentActionDate->setTable("evaluation");
			$containmentActionDate->setHelpId(9025);
			$complaintGroup4ContainmentActionGroup->add($containmentActionDate);

			$possibleSolutions = new textarea("possibleSolutions");
			if(isset($savedFields["possibleSolutions"]))
			$possibleSolutions->setValue($savedFields["possibleSolutions"]);
			$possibleSolutions->setGroup("complaintGroup4PossibleSolutionsGroup");
			$possibleSolutions->setDataType("text");
			$possibleSolutions->setRowTitle("possible_solutions");
			$possibleSolutions->setRequired(false);
			$possibleSolutions->setTable("evaluation");
			$possibleSolutions->setHelpId(9088);
			$complaintGroup4PossibleSolutionsGroup->add($possibleSolutions);

			$possibleSolutionsyn = new radio("possibleSolutionsyn");
			$possibleSolutionsyn->setGroup("complaintGroup4PossibleSolutionsGroup");
			$possibleSolutionsyn->setDataType("string");
			$possibleSolutionsyn->setLength(3);
			$possibleSolutionsyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$possibleSolutionsyn->setRowTitle("possibleSolutions_entered");
			$possibleSolutionsyn->setRequired(false);
			$possibleSolutionsyn->setVisible(false);
			if(isset($savedFields["possibleSolutionsyn"]))
			$possibleSolutionsyn->setValue($savedFields["possibleSolutionsyn"]);
			else $possibleSolutionsyn->setValue("No");
			$possibleSolutionsyn->setTable("complaint");
			$complaintGroup4PossibleSolutionsGroup->add($possibleSolutionsyn);

			$possibleSolutionsAuthor = new textbox("possibleSolutionsAuthor");
			if(isset($savedFields["possibleSolutionsAuthor"]))
			$possibleSolutionsAuthor->setValue($savedFields["possibleSolutionsAuthor"]);
			$possibleSolutionsAuthor->setGroup("complaintGroup4PossibleSolutionsGroup");
			$possibleSolutionsAuthor->setDataType("string");
			$possibleSolutionsAuthor->setLength(255);
			$possibleSolutionsAuthor->setRowTitle("possible_solutions_author");
			$possibleSolutionsAuthor->setRequired(false);
			$possibleSolutionsAuthor->setTable("evaluation");
			$possibleSolutionsAuthor->setHelpId(9025);
			$complaintGroup4PossibleSolutionsGroup->add($possibleSolutionsAuthor);

			$possibleSolutionsDate = new calendar("possibleSolutionsDate");
			if(isset($savedFields["possibleSolutionsDate"]))
			$possibleSolutionsDate->setValue($savedFields["possibleSolutionsDate"]);
			$possibleSolutionsDate->setGroup("complaintGroup4PossibleSolutionsGroup");
			$possibleSolutionsDate->setDataType("date");
			$possibleSolutionsDate->setErrorMessage("textbox_date_error");
			$possibleSolutionsDate->setLength(255);
			$possibleSolutionsDate->setRowTitle("possible_solutions_date");
			$possibleSolutionsDate->setRequired(false);
			$possibleSolutionsDate->setTable("evaluation");
			$possibleSolutionsDate->setHelpId(9025);
			$complaintGroup4PossibleSolutionsGroup->add($possibleSolutionsDate);

		//}

		$implementedActions = new textarea("implementedActions");
		if(isset($savedFields["implementedActions"]))
		$implementedActions->setValue($savedFields["implementedActions"]);
		$implementedActions->setGroup("complaintGroup4ImplementedActionsGroup");
		$implementedActions->setDataType("text");
		$implementedActions->setRowTitle("implemented_actions");
		$implementedActions->setRequired(false);
		$implementedActions->setTable("evaluation");
		$implementedActions->setHelpId(9020);
		$complaintGroup4ImplementedActionsGroup->add($implementedActions);

		$implementedActionsyn = new radio("implementedActionsyn");
		$implementedActionsyn->setGroup("complaintGroup4ImplementedActionsGroup");
		$implementedActionsyn->setDataType("string");
		$implementedActionsyn->setLength(3);
		$implementedActionsyn->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$implementedActionsyn->setRowTitle("implementedAction_entered");
		$implementedActionsyn->setRequired(false);
		$implementedActionsyn->setVisible(false);
		if(isset($savedFields["implementedActionsyn"]))
		$implementedActionsyn->setValue($savedFields["implementedActionsyn"]);
		else $implementedActionsyn->setValue("No");
		$implementedActionsyn->setTable("complaint");
		$complaintGroup4ImplementedActionsGroup->add($implementedActionsyn);

		$implementedActionsAuthor = new textbox("implementedActionsAuthor");
		if(isset($savedFields["implementedActionsAuthor"]))
		$implementedActionsAuthor->setValue($savedFields["implementedActionsAuthor"]);
		$implementedActionsAuthor->setGroup("complaintGroup4ImplementedActionsGroup");
		$implementedActionsAuthor->setDataType("string");
		$implementedActionsAuthor->setLength(255);
		$implementedActionsAuthor->setRowTitle("implemented_actions_author");
		$implementedActionsAuthor->setRequired(false);
		$implementedActionsAuthor->setTable("evaluation");
		$implementedActionsAuthor->setHelpId(9021);
		$complaintGroup4ImplementedActionsGroup->add($implementedActionsAuthor);

		$implementedActionsDate = new calendar("implementedActionsDate");
		if(isset($savedFields["implementedActionsDate"]))
		$implementedActionsDate->setValue($savedFields["implementedActionsDate"]);
		$implementedActionsDate->setGroup("complaintGroup4ImplementedActionsGroup");
		$implementedActionsDate->setDataType("date");
		$implementedActionsDate->setErrorMessage("textbox_date_error");
		$implementedActionsDate->setLength(255);
		$implementedActionsDate->setRowTitle("implemented_actions_date");
		$implementedActionsDate->setRequired(false);
		$implementedActionsDate->setTable("evaluation");//was complaintEval?????
		$implementedActionsDate->setHelpId(9022);
		$complaintGroup4ImplementedActionsGroup->add($implementedActionsDate);

		$implementedActionsEstimated = new calendar("implementedActionsEstimated");
		if(isset($savedFields["implementedActionsEstimated"]))
		$implementedActionsEstimated->setValue($savedFields["implementedActionsEstimated"]);
		$implementedActionsEstimated->setGroup("complaintGroup41");
		$implementedActionsEstimated->setDataType("date");
		$implementedActionsEstimated->setErrorMessage("textbox_date_error");
		$implementedActionsEstimated->setLength(255);
		$implementedActionsEstimated->setRowTitle("implemented_actions_estimated");
		$implementedActionsEstimated->setRequired(false);
		$implementedActionsEstimated->setTable("evaluation");
		$implementedActionsEstimated->setHelpId(9023);
		$complaintGroup41->add($implementedActionsEstimated);

		$implementedActionsImplementation = new calendar("implementedActionsImplementation");
		if(isset($savedFields["implementedActionsImplementation"]))
		$implementedActionsImplementation->setValue($savedFields["implementedActionsImplementation"]);
		$implementedActionsImplementation->setGroup("complaintGroup41");
		$implementedActionsImplementation->setDataType("date");
		$implementedActionsImplementation->setErrorMessage("textbox_date_error");
		$implementedActionsImplementation->setLength(255);
		$implementedActionsImplementation->setRowTitle("implemented_actions_implementation");
		$implementedActionsImplementation->setRequired(false);
		$implementedActionsImplementation->setTable("evaluation");
		$implementedActionsImplementation->setHelpId(9024);
		$complaintGroup41->add($implementedActionsImplementation);

		$implementedActionsEffectiveness = new calendar("implementedActionsEffectiveness");
		if(isset($savedFields["implementedActionsEffectiveness"]))
		$implementedActionsEffectiveness->setValue($savedFields["implementedActionsEffectiveness"]);
		$implementedActionsEffectiveness->setGroup("complaintGroup41");
		$implementedActionsEffectiveness->setDataType("date");
		$implementedActionsEffectiveness->setErrorMessage("textbox_date_error");
		$implementedActionsEffectiveness->setLength(255);
		$implementedActionsEffectiveness->setRowTitle("implemented_actions_effectiveness");
		$implementedActionsEffectiveness->setRequired(false);
		$implementedActionsEffectiveness->setTable("evaluation");
		$implementedActionsEffectiveness->setHelpId(9025);
		$complaintGroup41->add($implementedActionsEffectiveness);

		$implementedPermanentCorrectiveActionValidated = new textarea("implementedPermanentCorrectiveActionValidated");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidated"]))
		$implementedPermanentCorrectiveActionValidated->setValue($savedFields["implementedPermanentCorrectiveActionValidated"]);
		$implementedPermanentCorrectiveActionValidated->setGroup("complaintGroup41");
		$implementedPermanentCorrectiveActionValidated->setDataType("text");
		$implementedPermanentCorrectiveActionValidated->setRowTitle("implemented_permanent_corrective_action_validated");
		$implementedPermanentCorrectiveActionValidated->setRequired(false);
		$implementedPermanentCorrectiveActionValidated->setTable("evaluation");
		$implementedPermanentCorrectiveActionValidated->setHelpId(90243453451);
		$complaintGroup41->add($implementedPermanentCorrectiveActionValidated);

		$implementedPermanentCorrectiveActionValidatedyn = new radio("implementedPermanentCorrectiveActionValidatedyn");
		$implementedPermanentCorrectiveActionValidatedyn->setGroup("complaintGroup41");
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
		$implementedPermanentCorrectiveActionValidatedyn->setTable("complaint");
		$complaintGroup41->add($implementedPermanentCorrectiveActionValidatedyn);

		$implementedPermanentCorrectiveActionValidatedAuthor = new textbox("implementedPermanentCorrectiveActionValidatedAuthor");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]))
		$implementedPermanentCorrectiveActionValidatedAuthor->setValue($savedFields["implementedPermanentCorrectiveActionValidatedAuthor"]);
		$implementedPermanentCorrectiveActionValidatedAuthor->setGroup("complaintGroup41");
		$implementedPermanentCorrectiveActionValidatedAuthor->setDataType("string");
		$implementedPermanentCorrectiveActionValidatedAuthor->setLength(255);
		$implementedPermanentCorrectiveActionValidatedAuthor->setRowTitle("implemented_permanent_corrective_action_validated_author");
		$implementedPermanentCorrectiveActionValidatedAuthor->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedAuthor->setTable("evaluation");
		$implementedPermanentCorrectiveActionValidatedAuthor->setHelpId(90243453452);
		$complaintGroup41->add($implementedPermanentCorrectiveActionValidatedAuthor);

		$implementedPermanentCorrectiveActionValidatedDate = new calendar("implementedPermanentCorrectiveActionValidatedDate");
		if(isset($savedFields["implementedPermanentCorrectiveActionValidatedDate"]))
		$implementedPermanentCorrectiveActionValidatedDate->setValue($savedFields["implementedPermanentCorrectiveActionValidatedDate"]);
		$implementedPermanentCorrectiveActionValidatedDate->setGroup("complaintGroup41");
		$implementedPermanentCorrectiveActionValidatedDate->setDataType("date");
		$implementedPermanentCorrectiveActionValidatedDate->setErrorMessage("textbox_date_error");
		$implementedPermanentCorrectiveActionValidatedDate->setLength(255);
		$implementedPermanentCorrectiveActionValidatedDate->setRowTitle("implemented_permanent_corrective_action_date");
		$implementedPermanentCorrectiveActionValidatedDate->setRequired(false);
		$implementedPermanentCorrectiveActionValidatedDate->setTable("evaluation");//was complaintEval?????
		$implementedPermanentCorrectiveActionValidatedDate->setHelpId(90243453453);
		$complaintGroup41->add($implementedPermanentCorrectiveActionValidatedDate);

		//if($fields8D['g8d'] == "yes")
		//{

			$preventiveActions = new textarea("preventiveActions");
			if(isset($savedFields["preventiveActions"]))
			$preventiveActions->setValue($savedFields["preventiveActions"]);
			$preventiveActions->setGroup("complaintGroup4PreventiveActionsGroup");
			$preventiveActions->setDataType("text");
			$preventiveActions->setRowTitle("preventive_action");
			$preventiveActions->setRequired(false);
			$preventiveActions->setTable("evaluation");
			$preventiveActions->setHelpId(9089);
			$complaintGroup4PreventiveActionsGroup->add($preventiveActions);

			$preventiveActionsyn = new radio("preventiveActionsyn");
			$preventiveActionsyn->setGroup("complaintGroup4PreventiveActionsGroup");
			$preventiveActionsyn->setDataType("string");
			$preventiveActionsyn->setLength(3);
			$preventiveActionsyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
			));
			$preventiveActionsyn->setRowTitle("preventiveActions_entered");
			$preventiveActionsyn->setRequired(false);
			$preventiveActionsyn->setVisible(false);
			if(isset($savedFields["preventiveActionsyn"]))
			$preventiveActionsyn->setValue($savedFields["preventiveActionsyn"]);
			else $preventiveActionsyn->setValue("No");
			$preventiveActionsyn->setTable("complaint");
			$complaintGroup4PreventiveActionsGroup->add($preventiveActionsyn);

			$preventiveActionsAuthor = new textbox("preventiveActionsAuthor");
			if(isset($savedFields["preventiveActionsAuthor"]))
			$preventiveActionsAuthor->setValue($savedFields["preventiveActionsAuthor"]);
			$preventiveActionsAuthor->setGroup("complaintGroup4PreventiveActionsGroup");
			$preventiveActionsAuthor->setDataType("string");
			$preventiveActionsAuthor->setLength(255);
			$preventiveActionsAuthor->setRowTitle("preventive_actions_author");
			$preventiveActionsAuthor->setRequired(false);
			$preventiveActionsAuthor->setTable("evaluation");
			$preventiveActionsAuthor->setHelpId(9090);
			$complaintGroup4PreventiveActionsGroup->add($preventiveActionsAuthor);

			$preventiveActionsDate = new calendar("preventiveActionsDate");
			if(isset($savedFields["preventiveActionsDate"]))
			$preventiveActionsDate->setValue($savedFields["preventiveActionsDate"]);
			$preventiveActionsDate->setGroup("complaintGroup4PreventiveActionsGroup");
			$preventiveActionsDate->setDataType("date");
			$preventiveActionsDate->setErrorMessage("textbox_date_error");
			$preventiveActionsDate->setLength(255);
			$preventiveActionsDate->setRowTitle("preventive_actions_date");
			$preventiveActionsDate->setRequired(false);
			$preventiveActionsDate->setTable("evaluation");
			$preventiveActionsDate->setHelpId(9091);
			$complaintGroup4PreventiveActionsGroup->add($preventiveActionsDate);

			$preventiveActionsEstimatedDate = new calendar("preventiveActionsEstimatedDate");
			if(isset($savedFields["preventiveActionsEstimatedDate"]))
			$preventiveActionsEstimatedDate->setValue($savedFields["preventiveActionsEstimatedDate"]);
			$preventiveActionsEstimatedDate->setGroup("complaintGroup42");
			$preventiveActionsEstimatedDate->setDataType("date");
			$preventiveActionsEstimatedDate->setErrorMessage("textbox_date_error");
			$preventiveActionsEstimatedDate->setLength(255);
			$preventiveActionsEstimatedDate->setRowTitle("preventive_actions_estimated_date");
			$preventiveActionsEstimatedDate->setRequired(false);
			$preventiveActionsEstimatedDate->setTable("evaluation");
			$preventiveActionsEstimatedDate->setHelpId(9092);
			$complaintGroup42->add($preventiveActionsEstimatedDate);

			$preventiveActionsImplementedDate = new calendar("preventiveActionsImplementedDate");
			if(isset($savedFields["preventiveActionsImplementedDate"]))
			$preventiveActionsImplementedDate->setValue($savedFields["preventiveActionsImplementedDate"]);
			$preventiveActionsImplementedDate->setGroup("complaintGroup42");
			$preventiveActionsImplementedDate->setDataType("date");
			$preventiveActionsImplementedDate->setErrorMessage("textbox_date_error");
			$preventiveActionsImplementedDate->setLength(255);
			$preventiveActionsImplementedDate->setRowTitle("preventive_actions_implemented_date");
			$preventiveActionsImplementedDate->setRequired(false);
			$preventiveActionsImplementedDate->setTable("evaluation");
			$preventiveActionsImplementedDate->setHelpId(9093);
			$complaintGroup42->add($preventiveActionsImplementedDate);

			$preventiveActionsValidationDate = new calendar("preventiveActionsValidationDate");
			if(isset($savedFields["preventiveActionsValidationDate"]))
			$preventiveActionsValidationDate->setValue($savedFields["preventiveActionsValidationDate"]);
			$preventiveActionsValidationDate->setGroup("complaintGroup42");
			$preventiveActionsValidationDate->setDataType("date");
			$preventiveActionsValidationDate->setErrorMessage("textbox_date_error");
			$preventiveActionsValidationDate->setLength(255);
			$preventiveActionsValidationDate->setRowTitle("preventive_actions_validation_date");
			$preventiveActionsValidationDate->setRequired(false);
			$preventiveActionsValidationDate->setTable("evaluation");
			$preventiveActionsValidationDate->setHelpId(9094);
			$complaintGroup42->add($preventiveActionsValidationDate);

		//}


		//$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
		//$fields8D = mysql_fetch_array($dataset8D);

		//if($fields8D['g8d'] == "yes")
		//{
			$riskAssessment = new radio("riskAssessment");
			$riskAssessment->setGroup("riskAssessmentGroup");
			$riskAssessment->setDataType("string");
			$riskAssessment->setLength(5);
			$riskAssessment->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$riskAssessment->setRowTitle("risk_assessment");
			$riskAssessment->setRequired(false);
			if(isset($savedFields["risk_assessment"]))
			$riskAssessment->setValue($savedFields["risk_assessment"]);
			else $riskAssessment->setValue("NO");
			$riskAssessment->setTable("evaluation");
			$riskAssessment->setHelpId(9026);

			// Dependency
			$riskAssessment_dependency = new dependency();
			$riskAssessment_dependency->addRule(new rule('riskAssessmentGroup', 'riskAssessment', 'YES'));
			$riskAssessment_dependency->setGroup('riskAssessmentGroupYes');
			$riskAssessment_dependency->setShow(true);

			$riskAssessment->addControllingDependency($riskAssessment_dependency);
			$riskAssessmentGroup->add($riskAssessment);

			$riskAssessmentRef = new textbox("riskAssessmentRef");
			if(isset($savedFields["riskAssessmentRef"]))
			$riskAssessmentRef->setValue($savedFields["riskAssessmentRef"]);
			$riskAssessmentRef->setGroup("riskAssessmentGroupYes");
			$riskAssessmentRef->setDataType("string");
			$riskAssessmentRef->setLength(255);
			$riskAssessmentRef->setRowTitle("risk_assessment_ref");
			$riskAssessmentRef->setRequired(false);
			$riskAssessmentRef->setTable("evaluation");
			$riskAssessmentRef->setHelpId(9027);
			$riskAssessmentGroupYes->add($riskAssessmentRef);

			$riskAssessmentDate = new calendar("riskAssessmentDate");
			if(isset($savedFields["riskAssessmentDate"]))
			$riskAssessmentDate->setValue($savedFields["riskAssessmentDate"]);
			$riskAssessmentDate->setGroup("riskAssessmentGroupYes");
			$riskAssessmentDate->setDataType("date");
			$riskAssessmentDate->setErrorMessage("textbox_date_error");
			$riskAssessmentDate->setLength(255);
			$riskAssessmentDate->setRowTitle("risk_assessment_date");
			$riskAssessmentDate->setRequired(false);
			$riskAssessmentDate->setTable("evaluation");
			$riskAssessmentDate->setHelpId(9028);
			$riskAssessmentGroupYes->add($riskAssessmentDate);

			$managementSystemReviewed = new radio("managementSystemReviewed");
			$managementSystemReviewed->setGroup("managementSystemGroup");
			$managementSystemReviewed->setDataType("string");
			$managementSystemReviewed->setLength(5);
			$managementSystemReviewed->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$managementSystemReviewed->setRowTitle("management_system_ref");
			$managementSystemReviewed->setRequired(false);
			if(isset($savedFields["managementSystemReviewed"]))
			$managementSystemReviewed->setValue($savedFields["managementSystemReviewed"]);
			else $managementSystemReviewed->setValue("NO");
			$managementSystemReviewed->setTable("evaluation");
			$managementSystemReviewed->setHelpId(9026);


			// Dependency
			$managementSystemReviewed_dependency = new dependency();
			$managementSystemReviewed_dependency->addRule(new rule('managementSystemGroup', 'managementSystemReviewed', 'YES'));
			$managementSystemReviewed_dependency->setGroup('managementSystemRefYes');
			$managementSystemReviewed_dependency->setShow(true);

			$managementSystemReviewed->addControllingDependency($managementSystemReviewed_dependency);
			$managementSystemGroup->add($managementSystemReviewed);

			$managementSystemReviewedRef = new textbox("managementSystemReviewedRef");
			if(isset($savedFields["managementSystemReviewedRef"]))
			$managementSystemReviewedRef->setValue($savedFields["managementSystemReviewedRef"]);
			$managementSystemReviewedRef->setGroup("managementSystemRefYes");
			$managementSystemReviewedRef->setDataType("string");
			$managementSystemReviewedRef->setLength(255);
			$managementSystemReviewedRef->setRowTitle("management_system_yes_ref");
			$managementSystemReviewedRef->setRequired(false);
			$managementSystemReviewedRef->setTable("evaluation");
			$managementSystemReviewedRef->setHelpId(9027);
			$managementSystemRefYes->add($managementSystemReviewedRef);

			$managementSystemReviewedDate = new calendar("managementSystemReviewedDate");
			if(isset($savedFields["managementSystemReviewedDate"]))
			$managementSystemReviewedDate->setValue($savedFields["managementSystemReviewedDate"]);
			$managementSystemReviewedDate->setGroup("managementSystemRefYes");
			$managementSystemReviewedDate->setDataType("date");
			$managementSystemReviewedDate->setErrorMessage("textbox_date_error");
			$managementSystemReviewedDate->setLength(255);
			$managementSystemReviewedDate->setRowTitle("management_system_date");
			$managementSystemReviewedDate->setRequired(false);
			$managementSystemReviewedDate->setTable("evaluation");
			$managementSystemReviewedDate->setHelpId(9028);
			$managementSystemRefYes->add($managementSystemReviewedDate);


			$fmea = new radio("fmea");
			$fmea->setGroup("fmeaGroup");
			$fmea->setDataType("string");
			$fmea->setLength(5);
			$fmea->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$fmea->setRowTitle("fmea");
			$fmea->setRequired(false);
			if(isset($savedFields["fmea"]))
			$fmea->setValue($savedFields["fmea"]);
			else $fmea->setValue("NO");
			$fmea->setTable("evaluation");
			$fmea->setHelpId(9029);


			// Dependency
			$fmea_dependency = new dependency();
			$fmea_dependency->addRule(new rule('fmeaGroup', 'fmea', 'YES'));
			$fmea_dependency->setGroup('fmeaDepGroup');
			$fmea_dependency->setShow(true);

			$fmea->addControllingDependency($fmea_dependency);
			$fmeaGroup->add($fmea);

			$fmeaRef = new textbox("fmeaRef");
			if(isset($savedFields["fmeaRef"]))
			$fmeaRef->setValue($savedFields["fmeaRef"]);
			$fmeaRef->setGroup("fmeaDepGroup");
			$fmeaRef->setDataType("string");
			$fmeaRef->setLength(255);
			$fmeaRef->setRowTitle("fmea_yes_ref");
			$fmeaRef->setRequired(false);
			$fmeaRef->setTable("evaluation");
			$fmeaRef->setHelpId(9030);
			$fmeaDepGroup->add($fmeaRef);

			$fmeaDate = new calendar("fmeaDate");
			if(isset($savedFields["fmeaDate"]))
			$fmeaDate->setValue($savedFields["fmeaDate"]);
			$fmeaDate->setGroup("fmeaDepGroup");
			$fmeaDate->setDataType("date");
			$fmeaDate->setErrorMessage("textbox_date_error");
			$fmeaDate->setLength(255);
			$fmeaDate->setRowTitle("fmea_ref_date");
			$fmeaDate->setRequired(false);
			$fmeaDate->setTable("evaluation");
			$fmeaDate->setHelpId(9031);
			$fmeaDepGroup->add($fmeaDate);


			$customerSpecification = new radio("customerSpecification");
			$customerSpecification->setDataType("string");
			$customerSpecification->setLength(5);
			$customerSpecification->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$customerSpecification->setRowTitle("customer_specification");
			$customerSpecification->setRequired(false);
			if(isset($savedFields["customerSpecification"]))
			$customerSpecification->setValue($savedFields["customerSpecification"]);
			else $customerSpecification->setValue("NO");
			$customerSpecification->setTable("evaluation");
			$customerSpecification->setHelpId(9032);


			// Dependency
			$customerSpecification_dependency = new dependency();
			$customerSpecification_dependency->addRule(new rule('customerSpecificationGroup', 'customerSpecification', 'YES'));
			$customerSpecification_dependency->setGroup('customerSpecificationGroupYes');
			$customerSpecification_dependency->setShow(true);

			$customerSpecification->addControllingDependency($customerSpecification_dependency);
			$customerSpecificationGroup->add($customerSpecification);

			$customerSpecificationRef = new textbox("customerSpecificationRef");
			if(isset($savedFields["customerSpecificationRef"]))
			$customerSpecificationRef->setValue($savedFields["customerSpecificationRef"]);
			$customerSpecificationRef->setGroup("customerSpecificationGroupYes");
			$customerSpecificationRef->setDataType("string");
			$customerSpecificationRef->setLength(255);
			$customerSpecificationRef->setRowTitle("customer_specification_ref");
			$customerSpecificationRef->setRequired(false);
			$customerSpecificationRef->setTable("evaluation");
			$customerSpecificationRef->setHelpId(9033);
			$customerSpecificationGroupYes->add($customerSpecificationRef);

			$customerSpecificationDate = new calendar("customerSpecificationDate");
			if(isset($savedFields["customerSpecificationDate"]))
			$customerSpecificationDate->setValue($savedFields["customerSpecificationDate"]);
			$customerSpecificationDate->setGroup("customerSpecificationGroupYes");
			$customerSpecificationDate->setDataType("date");
			$customerSpecificationDate->setErrorMessage("textbox_date_error");
			$customerSpecificationDate->setLength(255);
			$customerSpecificationDate->setRowTitle("customer_specification_date");
			$customerSpecificationDate->setRequired(false);
			$customerSpecificationDate->setTable("evaluation");
			$customerSpecificationDate->setHelpId(9034);
			$customerSpecificationGroupYes->add($customerSpecificationDate);

			/////////////////////
			$flowChart = new radio("flowChart");
			$flowChart->setGroup("flowChartGroup");
			$flowChart->setDataType("string");
			$flowChart->setLength(5);
			$flowChart->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No'),
			array('value' => 'na', 'display' => 'N/A')
			));
			$flowChart->setRowTitle("flow_chart");
			$flowChart->setRequired(false);
			if(isset($savedFields["flowChart"]))
			$flowChart->setValue($savedFields["flowChart"]);
			else $flowChart->setValue("NO");
			$flowChart->setTable("evaluation");
			$flowChart->setHelpId(9035);


			// Dependency
			$flowChart_dependency = new dependency();
			$flowChart_dependency->addRule(new rule('flowChartGroup', 'flowChart', 'YES'));
			$flowChart_dependency->setGroup('flowChartGroupYes');
			$flowChart_dependency->setShow(true);

			$flowChart->addControllingDependency($flowChart_dependency);
			$flowChartGroup->add($flowChart);

			$flowChartRef = new textbox("flowChartRef");
			if(isset($savedFields["flowChartRef"]))
			$flowChartRef->setValue($savedFields["flowChartRef"]);
			$flowChartRef->setGroup("flowChartGroupYes");
			$flowChartRef->setDataType("string");
			$flowChartRef->setLength(255);
			$flowChartRef->setRowTitle("flow_chart_yes_ref");
			$flowChartRef->setRequired(false);
			$flowChartRef->setTable("evaluation");
			$flowChartRef->setHelpId(9030);
			$flowChartGroupYes->add($flowChartRef);

			$flowChartDate = new calendar("flowChartDate");
			if(isset($savedFields["flowChartDate"]))
			$flowChartDate->setValue($savedFields["flowChartDate"]);
			$flowChartDate->setGroup("flowChartGroupYes");
			$flowChartDate->setDataType("date");
			$flowChartDate->setErrorMessage("textbox_date_error");
			$flowChartDate->setLength(255);
			$flowChartDate->setRowTitle("flow_chart_ref_date");
			$flowChartDate->setRequired(false);
			$flowChartDate->setTable("evaluation");
			$flowChartDate->setHelpId(9031);
			$flowChartGroupYes->add($flowChartDate);

			///////////////////
		//}

		$additionalComments = new textarea("additionalComments");
		if(isset($savedFields["additionalComments"]))
		$additionalComments->setValue($savedFields["additionalComments"]);
		$additionalComments->setGroup("sendToUser2");
		$additionalComments->setDataType("text");
		$additionalComments->setRowTitle("comments");
		$additionalComments->setRequired(false);
		$additionalComments->setTable("evaluation");
		$additionalComments->setHelpId(9031);
		$sendToUser2->add($additionalComments);

		$processOwner2 = new autocomplete("processOwner2");
		if(isset($savedFields["processOwner2"]))
		$processOwner2->setValue($savedFields["processOwner2"]);
		$processOwner2->setGroup("sendToUser2");
		$processOwner2->setDataType("string");
		$processOwner2->setErrorMessage("user_not_found");
		$processOwner2->setRequired(true);
		$processOwner2->setRowTitle("chosen_complaint_owner");
		$processOwner2->setRequired(true);
		$processOwner2->setUrl("/apps/complaints/ajax/complaintOwner2?");
		$processOwner2->setTable("evaluation");
		$processOwner2->setHelpId(8145);
		$sendToUser2->add($processOwner2);

		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
			$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("sendToUser2");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("cc");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("evaluation");
		$copy_to->setHelpId(8146);
		$sendToUser2->add($copy_to);

//		if(!isset($savedFields["0|copy_to"])){//the first one will always need to be set if its saved
//			$copy_to = new autocomplete("copy_to");
//			if(isset($savedFields["0|copy_to"]))
//			$copy_to->setValue($savedFields["0|copy_to"]);
//			$copy_to->setGroup("transferOwnership2GroupYes");
//			$copy_to->setDataType("string");
//			$copy_to->setRowTitle("CC");
//			//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//			$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//			$copy_to->setRequired(false);
//			$copy_to->setTable("ccGroup");
//			$copy_to->setHelpId(8146);
//			$transferOwnership2GroupYes2->add($copy_to);
//		}else{
//			$this->form->groupsToExclude[] = "ccComplaintGroup";
//			$i=0;
//			$endList = false;
//			do{
//				if(!isset($savedFields[$i."|copy_to"])){
//					$maxList = $i;
//					$endList = true;
//				}
//				$i++;
//			}while(!$endList);
//			for($i=0; $i<$maxList; $i++){
//				if($i==0){//first will always be set
//					$copy_to = new autocomplete("copy_to");
//					if(isset($savedFields["0|copy_to"]))
//					$copy_to->setValue($savedFields["0|copy_to"]);
//					$copy_to->setGroup("transferOwnership2GroupYes");
//					$copy_to->setDataType("string");
//					$copy_to->setRowTitle("CC");
//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//					$copy_to->setRequired(false);
//					$copy_to->setTable("ccGroup");
//					$copy_to->setHelpId(8146);
//					$transferOwnership2GroupYes2->add($copy_to);
//				}else{
//
//					$copy_to = new autocomplete("copy_to");
//					if(isset($savedFields[$i."|copy_to"]))
//					$copy_to->setValue($savedFields[$i."|copy_to"]);
//					$copy_to->setGroup("ccComplaintGroup");
//					$copy_to->setDataType("string");
//					$copy_to->setRowTitle("CC");
//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
//					$copy_to->setRequired(false);
//					$copy_to->setTable("ccGroup");
//					$copy_to->setHelpId(8146);
//					//$ccComplaintGroup->add($copy_to);
//
//
//					$transferOwnership2GroupYes2->addRowCustom($savedFields[$i."|copy_to"]);
//				}
//			}
//		}

		$emailText = new textarea("emailText");
		if(isset($savedFields["emailText"]))
		$emailText->setValue($savedFields["emailText"]);
		$emailText->setGroup("sendToUser22");
		$emailText->setDataType("text");
		$emailText->setRowTitle("emailText");
		$emailText->setRequired(false);
		$emailText->setTable("evaluation");
		$emailText->setHelpId(9078);
		$sendToUser22->add($emailText);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser22");
		$submit->setVisible(true);
		$sendToUser22->add($submit);


		$this->form->add($initiation);
		$this->form->add($typeOfComplaintGroup);
		$this->form->add($complaintGroup);
		$this->form->add($qu_verificationMadeYesGroup);
		$this->form->add($qu_otherMaterialEffectedGroup);
		$this->form->add($qu_otherMaterialEffectedYes);
		$this->form->add($complaintGroup2);
		$this->form->add($qu_customerIssueActionGroup);
		$this->form->add($qu_supplierIssueActionGroup);
		$this->form->add($complaintGroup3);
		$this->form->add($complaintGroup3GoodsGroup);
		$this->form->add($complaintGroup3RootCauseGroup);
		$this->form->add($qu_useGoodsDerongationYesGroup);
		$this->form->add($qu_customerApprovedYesGroup);
		$this->form->add($complaintGroup4);
		$this->form->add($complaintGroup4ContainmentActionGroup);
		$this->form->add($complaintGroup4PossibleSolutionsGroup);
		$this->form->add($complaintGroup4ImplementedActionsGroup);
		$this->form->add($complaintGroup41);
		$this->form->add($complaintGroup4PreventiveActionsGroup);
		$this->form->add($complaintGroup42);
		$this->form->add($riskAssessmentGroup);
		$this->form->add($riskAssessmentGroupYes);
		$this->form->add($managementSystemGroup);
		$this->form->add($managementSystemRefYes);
		$this->form->add($fmeaGroup);
		$this->form->add($fmeaDepGroup);
		$this->form->add($customerSpecificationGroup);
		$this->form->add($customerSpecificationGroupYes);
		$this->form->add($flowChartGroup);
		$this->form->add($flowChartGroupYes);
		$this->form->add($sendToUser2);
		//$this->form->add($transferOwnership2GroupYes2);
		$this->form->add($sendToUser22);
	}

	public function emailInitiator()
	{
		//email initiator with details of owners choice to return or dispose goods
		$datasetInitiator = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, internalSalesName FROM complaint WHERE id = '" . $this->getcomplaintId() . "' ORDER BY id DESC LIMIT 1");
		$fieldsInitiator = mysql_fetch_array($datasetInitiator);
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT email FROM employee WHERE CONCAT(firstName, ' ', lastName) = '" . addslashes($fieldsInitiator['internalSalesName']) . "' LIMIT 1");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);

		if($this->form->get("updateInitiator")->getValue() == "Yes")
		{
			if($this->form->get("disposeGoods")->getValue() == "YES" && $this->form->get("returnGoods")->getValue() == "NO")
			{
				$message = "The goods are to be disposed";
				$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "initiatorUpdate", utf8_encode($message), $this->form->get("complaintJustified")->getValue());
			}
			elseif($this->form->get("disposeGoods")->getValue() == "NO" && $this->form->get("returnGoods")->getValue() == "YES")
			{
				$message = "The goods are to be returned";
				$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(),	$this->getcomplaintId(), "initiatorUpdate", utf8_encode($message), $this->form->get("complaintJustified")->getValue());
			}
		}

		return true;
	}

	public function getEmailNotification($owner, $sender, $id, $action, $emailText, $complaintJustifiedStatus)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailText>" . utf8_decode($emailText) . "</emailText><complaint_justified>" . $complaintJustifiedStatus . "</complaint_justified></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		$cc = "";

		$cc = $this->form->get("copy_to")->getValue();

		// Do CC for Return Request NA
		$cat = $this->getCategory();
		if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
		{
			if($this->form->get("returnRequestCC")->getValue() != "" && $this->form->get("copy_to")->getValue() == "")
			{
				$cc = $this->form->get("returnRequestCC")->getValue();
			}
		}

		email::send($owner, /*"intranet@scapa.com"*/$sender, translate::getInstance()->translate("new_complaint_action") . " - ID: " . $id, "$email", "$cc");

		return true;
	}

}

?>