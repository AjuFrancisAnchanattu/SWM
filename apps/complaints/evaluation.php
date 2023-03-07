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
	function __construct($complaint)
	{
		parent::__construct($complaint);
		
		$this->defineForm();
				
		$this->form->get('complaintId')->setValue($this->complaint->getId());
				
		$this->form->setStoreInSession(true);
		
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['evaluation']['loadedFromDatabase']))
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
	
	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);

		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation LEFT JOIN complaint ON evaluation.complaintId=complaint.id WHERE complaintId = "  . $id);
		

		if (mysql_num_rows($dataset) == 1)
		{
			
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['evaluation']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
			
			
//			foreach ($fields as $key => $value)
//			{
//				if ($this->form->get($key))
//				{
//					$this->form->get($key)->setValue($value);
//				}
//			}

			$this->form->populate($fields);
			
			$this->form->get("attachment")->load("/apps/complaints/attachments/" . $this->id . "/");////////////added this
			
			page::addDebug("THE G8D ID IS " . $fields['g8d'], __FILE__, __LINE__);
			
			
			
			//$this->form->get('analysisDate')->setValue(page::transformDateForPHP($this->form->get('analysisDate')->getValue()));
//			$this->form->get('rootCausesDate')->setValue(page::transformDateForPHP($this->form->get('rootCausesDate')->getValue()));
//			$this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($this->form->get('implementedActionsDate')->getValue()));
//			$this->form->get('implementedActionsEstimated')->setValue(page::transformDateForPHP($this->form->get('implementedActionsEstimated')->getValue()));
//			$this->form->get('implementedActionsImplementation')->setValue(page::transformDateForPHP($this->form->get('implementedActionsImplementation')->getValue()));
//			$this->form->get('implementedActionsEffectiveness')->setValue(page::transformDateForPHP($this->form->get('implementedActionsEffectiveness')->getValue()));
//			$this->form->get('managementSystemReviewedDate')->setValue(page::transformDateForPHP($this->form->get('managementSystemReviewedDate')->getValue()));
//			$this->form->get('fmeaDate')->setValue(page::transformDateForPHP($this->form->get('fmeaDate')->getValue()));
//			$this->form->get('customerSpecificationDate')->setValue(page::transformDateForPHP($this->form->get('customerSpecificationDate')->getValue()));
//			$this->form->get('dateSampleReceived')->setValue(page::transformDateForPHP($this->form->get('dateSampleReceived')->getValue()));
			
			//Use these instead of the above because they check to see if a value has been entered
			
			$fields['analysisDate'] == "0000-00-00" ?	$this->form->get('analysisDate')->setValue("") : $this->form->get('analysisDate')->setValue(page::transformDateForPHP($fields['analysisDate']));
			//$this->form->get('analysisDate')->getValue() == "0000-00-00" ?  $this->form->get('analysisDate')->setValue($today) : $this->form->get('analysisDate')->setValue(page::transformDateForPHP($this->form->get('analysisDate')->getValue()));
			$fields['rootCausesDate'] == "0000-00-00" ?  $this->form->get('rootCausesDate')->setValue("") : $this->form->get('rootCausesDate')->setValue(page::transformDateForPHP($fields['rootCausesDate']));
			$fields['implementedActionsDate'] == "0000-00-00" ?  $this->form->get('implementedActionsDate')->setValue("") : $this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($fields['implementedActionsDate']));			
			$fields['implementedActionsEstimated'] == "0000-00-00" ?  $this->form->get('implementedActionsEstimated')->setValue("") : $this->form->get('implementedActionsEstimated')->setValue(page::transformDateForPHP($fields['implementedActionsEstimated']));
			$fields['implementedActionsImplementation'] == "0000-00-00" ?  $this->form->get('implementedActionsImplementation')->setValue("") : $this->form->get('implementedActionsImplementation')->setValue(page::transformDateForPHP($fields['implementedActionsImplementation']));
			$fields['implementedActionsEffectiveness'] == "0000-00-00" ?  $this->form->get('implementedActionsEffectiveness')->setValue("") : $this->form->get('implementedActionsEffectiveness')->setValue(page::transformDateForPHP($fields['implementedActionsEffectiveness']));
			$this->form->get("emailText")->setValue("");
			$this->form->get("updateInitiator")->setValue("No");
			
			$datasetCategoryM = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'M%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
			$rowCategoryM = mysql_fetch_array($datasetCategoryM);
			
			$datasetCategoryD = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category, id FROM complaint WHERE category LIKE 'D%' AND id = " . $this->getComplaint()->form->get("id")->getValue() . "");
			$rowCategoryD = mysql_fetch_array($datasetCategoryD);
			
			$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaint()->form->get("id")->getValue() . "'");
			$fields8D = mysql_fetch_array($dataset8D);
			
			//die("this forms id is: " . $this->getComplaint()->form->get("id")->getValue() . ".");
			
			if(/*$this->form->get("complaintJustified")->getValue() == "YES" && $rowCategoryM > 0 || $rowCategoryD > 0*/ $fields8D['g8d'] == "yes")
			{
//				$this->form->get('possibleSolutionsDate')->setValue(page::transformDateForPHP($this->form->get('possibleSolutionsDate')->getValue()));
//				$this->form->get('preventiveActionsDate')->setValue(page::transformDateForPHP($this->form->get('preventiveActionsDate')->getValue()));
//				$this->form->get('preventiveActionsValidationDate')->setValue(page::transformDateForPHP($this->form->get('preventiveActionsValidationDate')->getValue()));
//				$this->form->get('preventiveActionsImplementedDate')->setValue(page::transformDateForPHP($this->form->get('preventiveActionsImplementedDate')->getValue()));
//				$this->form->get('preventiveActionsEstimatedDate')->setValue(page::transformDateForPHP($this->form->get('preventiveActionsEstimatedDate')->getValue()));
//				$this->form->get('containmentActionDate')->setValue(page::transformDateForPHP($this->form->get('containmentActionDate')->getValue()));	
			
				///////use these instead of above because they check to see if a value has been entered
				$fields['possibleSolutionsDate'] == "0000-00-00" ?  $this->form->get('possibleSolutionsDate')->setValue("") : $this->form->get('possibleSolutionsDate')->setValue(page::transformDateForPHP($fields['possibleSolutionsDate']));
				$fields['preventiveActionsDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsDate')->setValue("") : $this->form->get('preventiveActionsDate')->setValue(page::transformDateForPHP($fields['preventiveActionsDate']));
				$fields['preventiveActionsValidationDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsValidationDate')->setValue("") : $this->form->get('preventiveActionsValidationDate')->setValue(page::transformDateForPHP($fields['preventiveActionsValidationDate']));
				$fields['preventiveActionsImplementedDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsImplementedDate')->setValue("") : $this->form->get('preventiveActionsImplementedDate')->setValue(page::transformDateForPHP($fields['preventiveActionsImplementedDate']));
				$fields['preventiveActionsEstimatedDate'] == "0000-00-00" ?  $this->form->get('preventiveActionsEstimatedDate')->setValue("") : $this->form->get('preventiveActionsEstimatedDate')->setValue(page::transformDateForPHP($fields['preventiveActionsEstimatedDate']));
				$fields['containmentActionDate'] == "0000-00-00" ?  $this->form->get('containmentActionDate')->setValue("") : $this->form->get('containmentActionDate')->setValue(page::transformDateForPHP($fields['containmentActionDate']));
				
				$fields['managementSystemReviewedDate'] == "0000-00-00" ?  $this->form->get('managementSystemReviewedDate')->setValue("") : $this->form->get('managementSystemReviewedDate')->setValue(page::transformDateForPHP($fields['managementSystemReviewedDate']));
				$fields['fmeaDate'] == "0000-00-00" ?  $this->form->get('fmeaDate')->setValue("") : $this->form->get('fmeaDate')->setValue(page::transformDateForPHP($fields['fmeaDate']));
				$fields['customerSpecificationDate'] == "0000-00-00" ?  $this->form->get('customerSpecificationDate')->setValue("") : $this->form->get('customerSpecificationDate')->setValue(page::transformDateForPHP($fields['customerSpecificationDate']));
				$fields['dateSampleReceived'] == "0000-00-00" ?  $this->form->get('dateSampleReceived')->setValue("") : $this->form->get('dateSampleReceived')->setValue(page::transformDateForPHP($fields['dateSampleReceived']));
				$fields['flowChartDate'] == "0000-00-00" ?  $this->form->get('flowChartDate')->setValue("") : $this->form->get('flowChartDate')->setValue(page::transformDateForPHP($fields['flowChartDate']));
			}
			
			
			$this->form->get("processOwner")->setValue($this->form->get("owner")->getValue());
			
			$this->form->putValuesInSession();

			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['evaluation']);
			return false;
		}
		
	}
		
	public function save()
	{	
		$this->determineStatus();
		
		//die();
		
		if ($this->loadedFromDatabase)
		{			
			//$this->getComplaint()->form->get("updatedDate")->setValue(common::nowDateForMysql());
					
			//$this->getComplaint()->form->get("initialSubmissionDate")->setIgnore(true);
			
			//$this->form->get("owner")->setValue($this->form->get("evaluation_owner")->getValue());
			
			$this->form->get("complaintId")->setIgnore(true);
			
			if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
			{
				$this->form->get("category")->setIgnore(true);
			}
			
			//enter Yes - No if data in textareas, to help with searches
			$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaintId() . "'");
			$fields8D = mysql_fetch_array($dataset8D);
			
			$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");
			$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
			$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");
			
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
			}
					
			// set Complaint owner	
			if($this->form->get("transferOwnership2")->getValue() == "NO")
			{
				$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT owner FROM complaint WHERE id = " . $this->getComplaintId() . "");
				$fieldsComplaint = mysql_fetch_array($datasetComplaint);
				$this->form->get("owner")->setValue($fieldsComplaint['owner']);
			}
			else 
			{				
				$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
			}
			
			// update
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation") . " WHERE complaintId= " . $this->getcomplaintId() . "");
			
			if($this->form->get("isPORight")->getValue() == "NO")
			{		
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET owner = '" . $this->form->get("transferOwnership")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");
				$this->complaint->getEmailNotification($this->getowner(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("reasonForRejection")->getValue(), $this->form->get("complaintJustified")->getValue());
			}
			else 
			{
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . "WHERE id= '" . $this->getcomplaintId() . "'");	
			}
		///////////	
			//email initiator with details of owners choice to return or dispose goods
			$datasetInitiator = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, internalSalesName FROM complaint WHERE id = '" . $this->getcomplaintId() . "' ORDER BY id DESC LIMIT 1");
			$fieldsInitiator = mysql_fetch_array($datasetInitiator);
			$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT email FROM employee WHERE CONCAT(firstName, ' ', lastName) = '" . $fieldsInitiator['internalSalesName'] . "' LIMIT 1");
			$fieldsEmployee = mysql_fetch_array($datasetEmployee);
			
			if($this->form->get("updateInitiator")->getValue() == "Yes")
			{
				if($this->form->get("disposeGoods")->getValue() == "YES" && $this->form->get("returnGoods")->getValue() == "NO")
				{
					$message = "The goods are to be disposed";
					$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "initiatorUpdate", $message, $this->form->get("complaintJustified")->getValue());
				}
				elseif($this->form->get("disposeGoods")->getValue() == "NO" && $this->form->get("returnGoods")->getValue() == "YES")
				{
					$message = "The goods are to be returned";
					$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "initiatorUpdate", $message, $this->form->get("complaintJustified")->getValue());
				}
			}
		//////////
			
			if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
			{
				$this->form->get("category")->setIgnore(true);
			}
			else 
			{
				$cat = $this->form->get("category")->getValue();
				$type = $cat[0];
				if($type == "M" || $type == "D")
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET g8d = 'yes', category = '" . $this->form->get("category")->getValue() . "' WHERE id = '" . $this->getComplaintId() . "'");
				}
				else 
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");			
				}
			}
			
			
			///just added for the adding of attachments
			$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->getcomplaintId() . "/");
			$this->form->get("attachment")->moveTempFileToFinal();
			
			if ($this->form->get("transferOwnership2")->getValue() == "YES")
			{
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("emailText")->getValue(), $this->form->get("complaintJustified")->getValue());
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("copyTo")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("emailText")->getValue(), $this->form->get("complaintJustified")->getValue());
			}
			
			mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");
			
			// save new data
			
			
			$this->addLog(translate::getInstance()->translate("evaluation_updated_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());
		}
		else 
		{
//			$this->form->get('analysisDate')->setIgnore(false);
//			$this->form->get('rootCausesDate')->setValue(page::transformDateForPHP($this->form->get('rootCausesDate')->getValue()));
//			$this->form->get('implementedActionsDate')->setValue(page::transformDateForPHP($this->form->get('implementedActionsDate')->getValue()));
//			$this->form->get('implementedActionsEstimated')->setValue(page::transformDateForPHP($this->form->get('implementedActionsEstimated')->getValue()));
//			$this->form->get('implementedActionsImplementation')->setValue(page::transformDateForPHP($this->form->get('implementedActionsImplementation')->getValue()));
//			$this->form->get('implementedActionsEffectiveness')->setValue(page::transformDateForPHP($this->form->get('implementedActionsEffectiveness')->getValue()));
//			$this->form->get('managementSystemReviewedDate')->setValue(page::transformDateForPHP($this->form->get('managementSystemReviewedDate')->getValue()));
//			$this->form->get('fmeaDate')->setValue(page::transformDateForPHP($this->form->get('fmeaDate')->getValue()));
//			$this->form->get('customerSpecificationDate')->setValue(page::transformDateForPHP($this->form->get('customerSpecificationDate')->getValue()));
//			$this->form->get('dateSampleReceived')->setValue(page::transformDateForPHP($this->form->get('dateSampleReceived')->getValue()));
			
			
			//$this->form->get("owner")->setValue($this->form->get("evaluation_owner")->getValue());
			
			// set report date
			//$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
			
			// set Complaint owner					
			//$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
			
			//enter Yes - No if data in textareas, to help with searches
			$dataset8D = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT g8d FROM complaint WHERE id = '" . $this->getComplaintId() . "'");
			$fields8D = mysql_fetch_array($dataset8D);
			
			$this->form->get("analysis")->getValue() == "" ? $this->form->get("analysisyn")->setValue("No") : $this->form->get("analysisyn")->setValue("Yes");
			$this->form->get("rootCauses")->getValue() == "" ? $this->form->get("rootCausesyn")->setValue("No") : $this->form->get("rootCausesyn")->setValue("Yes");
			$this->form->get("implementedActions")->getValue() == "" ? $this->form->get("implementedActionsyn")->setValue("No") : $this->form->get("implementedActionsyn")->setValue("Yes");
			
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
			}
				
			// set Complaint owner
			if($this->form->get("transferOwnership2")->getValue() == "NO")
			{
				$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT owner FROM complaint WHERE id = " . $this->getComplaintId() . "");
				$fieldsComplaint = mysql_fetch_array($datasetComplaint);
				$this->form->get("owner")->setValue(utf8_encode($fieldsComplaint['owner']));
			}
			else 
			{				
				$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
			}
			
			// begin transaction
			mysql::getInstance()->selectDatabase("complaints")->Execute("BEGIN");
			
			if($this->form->get("isPORight")->getValue() == "NO")
			{
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET owner = '" . $this->form->get("transferOwnership")->getValue() . "' WHERE id = " . $this->getcomplaintId() . "");
				$this->complaint->getEmailNotification($this->getowner(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("reasonForRejection")->getValue(), $this->form->get("complaintJustified")->getValue());
				$this->form->get("owner")->setValue($this->form->get("transferOwnership")->getValue());
			}
			else 
			{
				mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . "WHERE id = " . $this->getcomplaintId() . "");	
			}
			
			// insert
			
			mysql::getInstance()->selectDatabase("complaints")->Execute("COMMIT");
			
			
			mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO evaluation " . $this->form->generateInsertQuery("evaluation"));
		///////////	
			//email initiator with details of owners choice to return or dispose goods
			$datasetInitiator = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, internalSalesName FROM complaint WHERE id = '" . $this->getcomplaintId() . "' ORDER BY id DESC LIMIT 1");
			$fieldsInitiator = mysql_fetch_array($datasetInitiator);
			$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT email FROM employee WHERE CONCAT(firstName, ' ', lastName) = '" . $fieldsInitiator['internalSalesName'] . "' LIMIT 1");
			$fieldsEmployee = mysql_fetch_array($datasetEmployee);
			
			if($this->form->get("updateInitiator")->getValue() == "Yes")
			{
				if($this->form->get("disposeGoods")->getValue() == "YES" && $this->form->get("returnGoods")->getValue() == "NO")
				{
					$message = "The goods are to be disposed";
					$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "initiatorUpdate", $message, $this->form->get("complaintJustified")->getValue());
				}
				elseif($this->form->get("disposeGoods")->getValue() == "NO" && $this->form->get("returnGoods")->getValue() == "YES")
				{
					$message = "The goods are to be returned";
					$this->getEmailNotification($fieldsEmployee['email'], usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(),	$this->getcomplaintId(), "initiatorUpdate", $message, $this->form->get("complaintJustified")->getValue());
				}
			}
		//////////
			
			// Send Email
			$datasetEmail = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($datasetEmail);
			if ($this->form->get("transferOwnership2")->getValue() == "YES")
			{
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("emailText")->getValue(), $this->form->get("complaintJustified")->getValue());	
				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("copyTo")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->getcomplaintId(), "newEvaluation", $this->form->get("emailText")->getValue(), $this->form->get("complaintJustified")->getValue());
			}
				
			if($this->form->get("isComplaintCatRight")->getValue() == "Yes")
			{
				$this->form->get("category")->setIgnore(true);
			}
			else 
			{
				$cat = $this->form->get("category")->getValue();
				$type = $cat[0];
				if($type == "M" || $type == "D")
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET g8d = 'yes', category = '" . $this->form->get("category")->getValue() . "' WHERE id = '" . $this->getComplaintId() . "'");
				}
				else 
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id='" . $this->getcomplaintId() . "'");			
				}
			}
			
			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("evaluation_completed_disposed"));
			}
			else 
			{
				$this->addLog(translate::getInstance()->translate("evaluation_added_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());
			}
			
			/// added to save attachment
			$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->getcomplaintId() . "/");
			$this->form->get("attachment")->moveTempFileToFinal();
			
	}
		$this->lockComplaint($this->getcomplaintId(), "unlocked");
	
	
		page::redirect("/apps/complaints/");
	}
	

	
	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
			$this->getComplaint()->form->get("id")->getValue(),
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

	public function determineStatus()
	{		
		//if ($_REQUEST['location_owner'])
		//{
				//$location = $_REQUEST['location_owner'];
				//$this->status = $location;
				//$this->form->get('status')->setValue($location);
		//}
		
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
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("evaluation");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$isPORightNo = new group("isPORightNo");
		$transferOwnership2Group = new group("transferOwnership2Group");
		$sampleReceivedGroup = new group("sampleReceivedGroup");
		$sampleReceivedGroup->setBorder(false);
		$isSampleReceivedYes = new group("isSampleReceivedYes");
		$sampleReceivedGroupAfter = new group("sampleReceivedGroupAfter");
		$sampleReceivedGroupAfter->setBorder(false);		
		$isComplaintCatRightNo = new group("isComplaintCatRightNo");
		$complaintJustifiedGroup = new group("complaintJustifiedGroup");
		$complaintJustifiedGroup->setBorder(false);
		$complaintJustifiedYes = new group("complaintJustifiedYes");
		$managementSystemGroup = new group("managementSystemGroup");
		$managementSystemRefYes = new group("managementSystemRefYes");
		$fmeaGroup = new group("fmeaGroup");
		$fmeaDepGroup = new group("fmeaDepGroup");
		$customerSpecificationGroup = new group("customerSpecificationGroup");
		$customerSpecificationGroupYes = new group("customerSpecificationGroupYes");
		$flowChartGroup = new group("flowChartGroup");
		$flowChartGroupYes = new group("flowChartGroupYes");
		$commentsGroup = new group("commentsGroup");
		//$transferOwnership2Group = new group("transferOwnership2Group");
		$transferOwnership2GroupYes = new group("transferOwnership2GroupYes");
		$submitGroup = new group("submitGroup");

		$complaintId = new invisibletext("complaintId");
		$complaintId->setTable("evaluation");
		$complaintId->setVisible(false);
		$complaintId->setGroup("initiation");
		$complaintId->setDataType("number");
		$complaintId->setValue(0);
		$initiation->add($complaintId);
		
		$status = new textbox("status");
		$status->setValue("conclusion");
		$status->setTable("evaluation");
		$status->setVisible(false);
		$initiation->add($status);
		
		$owner = new textbox("owner");
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
		$isPORight->setValue("YES");
		$isPORight->setTable("evaluation");
		$isPORight->setHelpId(9000);
		
		
		// Dependency
		$isPORight_dependency = new dependency();
		$isPORight_dependency->addRule(new rule('initiation', 'isPORight', 'NO'));
		$isPORight_dependency->setGroup('isPORightNo');
		$isPORight_dependency->setShow(true);
		
		$isPORight->addControllingDependency($isPORight_dependency);
		$initiation->add($isPORight);

		$reasonForRejection = new textarea("reasonForRejection");
		$reasonForRejection->setGroup("isPORightNo");
		$reasonForRejection->setDataType("text");
		$reasonForRejection->setRowTitle("reason_for_rejection");
		$reasonForRejection->setRequired(false);
		$reasonForRejection->setTable("evaluation");
		$reasonForRejection->setHelpId(9001);
		$isPORightNo->add($reasonForRejection);	
		
				
		$transferOwnership = new dropdown("transferOwnership");
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
		$transferOwnership2->setGroup("complaintJustifiedYes");
		$transferOwnership2->setDataType("string");
		$transferOwnership2->setLength(5);
		$transferOwnership2->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$transferOwnership2->setRowTitle("transfer_ownership");
		$transferOwnership2->setRequired(false);
		$transferOwnership2->setValue("YES");
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
		$isSampleReceived->setRowTitle("is_sample_or_photo_received");
		$isSampleReceived->setRequired(true);
		$isSampleReceived->setValue("NO");
		$isSampleReceived->setTable("evaluation");
		$isSampleReceived->setHelpId(9003);
		
		
		// Dependency
		$isSampleReceived_dependency = new dependency();
		$isSampleReceived_dependency->addRule(new rule('sampleReceivedGroup', 'isSampleReceived', 'YES'));
		$isSampleReceived_dependency->setGroup('isSampleReceivedYes');
		$isSampleReceived_dependency->setShow(true);
		
		$isSampleReceived->addControllingDependency($isSampleReceived_dependency);
		$sampleReceivedGroup->add($isSampleReceived);
		
		$dateSampleReceived = new textbox("dateSampleReceived");
		$dateSampleReceived->setGroup("isSampleReceivedYes");
		$dateSampleReceived->setDataType("date");
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
		$analysisyn->setValue("No");
		$analysisyn->setTable("evaluation");
		$sampleReceivedGroupAfter->add($analysisyn);
		
		$author = new textbox("author");
		$author->setGroup("sampleReceivedGroupAfter");
		$author->setDataType("string");
		$author->setLength(255);
		$author->setRowTitle("author");
		$author->setRequired(false);
		$author->setTable("evaluation");
		$author->setHelpId(9006);
		$sampleReceivedGroupAfter->add($author);
		
		$analysisDate = new textbox("analysisDate");
		$analysisDate->setGroup("sampleReceivedGroupAfter");
		$analysisDate->setDataType("date");
		$analysisDate->setLength(30);
		$analysisDate->setRowTitle("analysis_date");
		$analysisDate->setRequired(false);
		$analysisDate->setTable("evaluation");//was complaintEval???
		$analysisDate->setHelpId(9007);
		$sampleReceivedGroupAfter->add($analysisDate);
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments");
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
		$isComplaintCatRight->setValue("Yes");
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
		$category->setValue($fieldsCat['category']);
		$category->setXMLSource("./apps/complaints/xml/category.xml");
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
			array('value' => 'undecided', 'display' => 'Undecided'),
			array('value' => 'unproven', 'display' => 'Unproven')
		));
		$complaintJustified->setRowTitle("complaint_justified");
		$complaintJustified->setRequired(true);
		$complaintJustified->setValue("undecided");
		$complaintJustified->setTable("evaluation");
		$complaintJustified->setHelpId(9011);
		
		
		// Dependency
		$complaintJustified_dependency = new dependency();
		$complaintJustified_dependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 'NO'));
		$complaintJustified_dependency->setGroup('complaintJustifiedYes');
		$complaintJustified_dependency->setShow(false);
		
		$complaintJustified->addControllingDependency($complaintJustified_dependency);
		$complaintJustifiedGroup->add($complaintJustified);
		
		
		$teamLeader = new textbox("teamLeader");
		$teamLeader->setGroup("complaintJustifiedYes");
		$teamLeader->setDataType("string");
		$teamLeader->setLength(255);
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setRequired(false);
		$teamLeader->setTable("evaluation");
		$teamLeader->setHelpId(9012);
		$complaintJustifiedYes->add($teamLeader);
		
		$teamMember = new textarea("teamMember");
		$teamMember->setGroup("complaintJustifiedYes");
		$teamMember->setDataType("text");
		$teamMember->setRowTitle("team_member");
		$teamMember->setRequired(false);
		$teamMember->setTable("evaluation");
		$teamMember->setHelpId(9013);
		$complaintJustifiedYes->add($teamMember);	
		
		$rootCauses = new textarea("rootCauses");
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
		$rootCausesyn->setValue("No");
		$rootCausesyn->setTable("evaluation");
		$complaintJustifiedYes->add($rootCausesyn);
		
		$failureCode = new textbox("failureCode");
		$failureCode->setGroup("complaintJustifiedYes");
		$failureCode->setDataType("string");
		$failureCode->setLength(255);
		$failureCode->setRowTitle("failure_code");
		$failureCode->setRequired(false);
		$failureCode->setTable("evaluation");
		$failureCode->setHelpId(9037);
		$complaintJustifiedYes->add($failureCode);
		
		$rootCauseCode = new textbox("rootCauseCode");
		$rootCauseCode->setGroup("complaintJustifiedYes");
		$rootCauseCode->setDataType("string");
		$rootCauseCode->setLength(255);
		$rootCauseCode->setRowTitle("root_cause_code");
		$rootCauseCode->setRequired(false);
		$rootCauseCode->setTable("evaluation");
		$rootCauseCode->setHelpId(9038);
		$complaintJustifiedYes->add($rootCauseCode);
		
		$attributableProcess = new textbox("attributableProcess");
		$attributableProcess->setGroup("complaintJustifiedYes");
		$attributableProcess->setDataType("string");
		$attributableProcess->setLength(255);
		$attributableProcess->setRowTitle("attributable_process");
		$attributableProcess->setRequired(false);
		$attributableProcess->setTable("evaluation");
		$attributableProcess->setHelpId(9015);
		$complaintJustifiedYes->add($attributableProcess);
		
		$rootCausesAuthor = new textbox("rootCausesAuthor");
		$rootCausesAuthor->setGroup("complaintJustifiedYes");
		$rootCausesAuthor->setDataType("string");
		$rootCausesAuthor->setLength(255);
		$rootCausesAuthor->setRowTitle("root_causes_author");
		$rootCausesAuthor->setRequired(false);
		$rootCausesAuthor->setTable("evaluation");
		$rootCausesAuthor->setHelpId(9015);
		$complaintJustifiedYes->add($rootCausesAuthor);
		
		$rootCausesDate = new textbox("rootCausesDate");
		$rootCausesDate->setGroup("complaintJustifiedYes");
		$rootCausesDate->setDataType("date");
		$rootCausesDate->setRowTitle("root_causes_date");
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
		$returnGoods->setValue("NO");
		$returnGoods->setTable("evaluation");
		$returnGoods->setHelpId(9017);
		$complaintJustifiedYes->add($returnGoods);
		
		$disposeGoods = new radio("disposeGoods");
		$disposeGoods->setGroup("complaintJustifiedYes");
		$disposeGoods->setDataType("string");
		$disposeGoods->setLength(5);
		$disposeGoods->setArraySource(array(
			array('value' => 'YES', 'display' => 'Yes'),
			array('value' => 'NO', 'display' => 'No')
		));
		$disposeGoods->setRowTitle("dispose_goods");
		$disposeGoods->setRequired(true);
		$disposeGoods->setValue("NO");
		$disposeGoods->setTable("evaluation");
		$disposeGoods->setHelpId(9018);
		$complaintJustifiedYes->add($disposeGoods);

		$updateInitiator = new radio("updateInitiator");
		$updateInitiator->setGroup("complaintJustifiedYes");
		$updateInitiator->setDataType("string");
		$updateInitiator->setLength(3);
		$updateInitiator->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$updateInitiator->setRowTitle("update_initiator");
		$updateInitiator->setRequired(false);
		$updateInitiator->setVisible(true);
		$updateInitiator->setValue("No");
		$updateInitiator->setTable("evaluation");
		$complaintJustifiedYes->add($updateInitiator);

		
//		$submit = new submit("submit");
//		$submit->setGroup("complaintJustifiedYes");
//		$submit->setVisible(true);
//		$complaintJustifiedYes->add($submit);	
		
		
		
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
			$containmentAction->setGroup("complaintJustifiedYes");
			$containmentAction->setDataType("string");
			$containmentAction->setLength(255);
			$containmentAction->setRowTitle("containment_actions");
			$containmentAction->setRequired(false);
			$containmentAction->setTable("evaluation");
			$containmentAction->setHelpId(9087);
			$complaintJustifiedYes->add($containmentAction);
			
			$containmentActionyn = new radio("containmentActionyn");
			$containmentActionyn->setGroup("complaintJustifiedYes");
			$containmentActionyn->setDataType("string");
			$containmentActionyn->setLength(3);
			$containmentActionyn->setArraySource(array(
				array('value' => 'Yes', 'display' => 'Yes'),
				array('value' => 'No', 'display' => 'No')
			));
			$containmentActionyn->setRowTitle("containmentAction_entered");
			$containmentActionyn->setRequired(false);
			$containmentActionyn->setVisible(false);
			$containmentActionyn->setValue("No");
			$containmentActionyn->setTable("evaluation");
			$complaintJustifiedYes->add($containmentActionyn);
				
			$containmentActionAuthor = new textbox("containmentActionAuthor");
			$containmentActionAuthor->setGroup("complaintJustifiedYes");
			$containmentActionAuthor->setDataType("string");
			$containmentActionAuthor->setLength(255);
			$containmentActionAuthor->setRowTitle("containment_actions_author");
			$containmentActionAuthor->setRequired(false);
			$containmentActionAuthor->setTable("evaluation");
			$containmentActionAuthor->setHelpId(9025);
			$complaintJustifiedYes->add($containmentActionAuthor);
			
			$containmentActionDate = new textbox("containmentActionDate");
			$containmentActionDate->setGroup("complaintJustifiedYes");
			$containmentActionDate->setDataType("date");
			$containmentActionDate->setLength(255);
			$containmentActionDate->setRowTitle("containment_actions_date");
			$containmentActionDate->setRequired(false);
			$containmentActionDate->setTable("evaluation");
			$containmentActionDate->setHelpId(9025);
			$complaintJustifiedYes->add($containmentActionDate);
			
			
			
			$possibleSolutions = new textarea("possibleSolutions");
			$possibleSolutions->setGroup("complaintJustifiedYes");
			$possibleSolutions->setDataType("string");
			$possibleSolutions->setLength(255);
			$possibleSolutions->setRowTitle("possible_solutions");
			$possibleSolutions->setRequired(false);
			$possibleSolutions->setTable("evaluation");
			$possibleSolutions->setHelpId(9088);
			$complaintJustifiedYes->add($possibleSolutions);
			
			$possibleSolutionsyn = new radio("possibleSolutionsyn");
			$possibleSolutionsyn->setGroup("complaintJustifiedYes");
			$possibleSolutionsyn->setDataType("string");
			$possibleSolutionsyn->setLength(3);
			$possibleSolutionsyn->setArraySource(array(
				array('value' => 'Yes', 'display' => 'Yes'),
				array('value' => 'No', 'display' => 'No')
			));
			$possibleSolutionsyn->setRowTitle("possibleSolutions_entered");
			$possibleSolutionsyn->setRequired(false);
			$possibleSolutionsyn->setVisible(false);
			$possibleSolutionsyn->setValue("No");
			$possibleSolutionsyn->setTable("evaluation");
			$complaintJustifiedYes->add($possibleSolutionsyn);
			
			$possibleSolutionsAuthor = new textbox("possibleSolutionsAuthor");
			$possibleSolutionsAuthor->setGroup("complaintJustifiedYes");
			$possibleSolutionsAuthor->setDataType("string");
			$possibleSolutionsAuthor->setLength(255);
			$possibleSolutionsAuthor->setRowTitle("possible_solutions_author");
			$possibleSolutionsAuthor->setRequired(false);
			$possibleSolutionsAuthor->setTable("evaluation");
			$possibleSolutionsAuthor->setHelpId(9025);
			$complaintJustifiedYes->add($possibleSolutionsAuthor);
			
			$possibleSolutionsDate = new textbox("possibleSolutionsDate");
			$possibleSolutionsDate->setGroup("complaintJustifiedYes");
			$possibleSolutionsDate->setDataType("date");
			$possibleSolutionsDate->setLength(255);
			$possibleSolutionsDate->setRowTitle("possible_solutions_date");
			$possibleSolutionsDate->setRequired(false);
			$possibleSolutionsDate->setTable("evaluation");
			$possibleSolutionsDate->setHelpId(9025);
			$complaintJustifiedYes->add($possibleSolutionsDate);
			
			
			
			$preventiveActions = new textarea("preventiveActions");
			$preventiveActions->setGroup("complaintJustifiedYes");
			$preventiveActions->setDataType("string");
			$preventiveActions->setLength(255);
			$preventiveActions->setRowTitle("preventive_action");
			$preventiveActions->setRequired(false);
			$preventiveActions->setTable("evaluation");
			$preventiveActions->setHelpId(9089);
			$complaintJustifiedYes->add($preventiveActions);
			
			$preventiveActionsyn = new radio("preventiveActionsyn");
			$preventiveActionsyn->setGroup("complaintJustifiedYes");
			$preventiveActionsyn->setDataType("string");
			$preventiveActionsyn->setLength(3);
			$preventiveActionsyn->setArraySource(array(
				array('value' => 'Yes', 'display' => 'Yes'),
				array('value' => 'No', 'display' => 'No')
			));
			$preventiveActionsyn->setRowTitle("preventiveActions_entered");
			$preventiveActionsyn->setRequired(false);
			$preventiveActionsyn->setVisible(false);
			$preventiveActionsyn->setValue("No");
			$preventiveActionsyn->setTable("evaluation");
			$complaintJustifiedYes->add($preventiveActionsyn);
			
			$preventiveActionsAuthor = new textbox("preventiveActionsAuthor");
			$preventiveActionsAuthor->setGroup("complaintJustifiedYes");
			$preventiveActionsAuthor->setDataType("string");
			$preventiveActionsAuthor->setLength(255);
			$preventiveActionsAuthor->setRowTitle("preventive_actions_author");
			$preventiveActionsAuthor->setRequired(false);
			$preventiveActionsAuthor->setTable("evaluation");
			$preventiveActionsAuthor->setHelpId(9025);
			$complaintJustifiedYes->add($preventiveActionsAuthor);
			
			$preventiveActionsDate = new textbox("preventiveActionsDate");
			$preventiveActionsDate->setGroup("complaintJustifiedYes");
			$preventiveActionsDate->setDataType("date");
			$preventiveActionsDate->setLength(255);
			$preventiveActionsDate->setRowTitle("preventive_actions_date");
			$preventiveActionsDate->setRequired(false);
			$preventiveActionsDate->setTable("evaluation");
			$preventiveActionsDate->setHelpId(9025);
			$complaintJustifiedYes->add($preventiveActionsDate);
			
			$preventiveActionsEstimatedDate = new textbox("preventiveActionsEstimatedDate");
			$preventiveActionsEstimatedDate->setGroup("complaintJustifiedYes");
			$preventiveActionsEstimatedDate->setDataType("date");
			$preventiveActionsEstimatedDate->setLength(255);
			$preventiveActionsEstimatedDate->setRowTitle("preventive_actions_estimated_date");
			$preventiveActionsEstimatedDate->setRequired(false);
			$preventiveActionsEstimatedDate->setTable("evaluation");
			$preventiveActionsEstimatedDate->setHelpId(9025);
			$complaintJustifiedYes->add($preventiveActionsEstimatedDate);
			
			$preventiveActionsImplementedDate = new textbox("preventiveActionsImplementedDate");
			$preventiveActionsImplementedDate->setGroup("complaintJustifiedYes");
			$preventiveActionsImplementedDate->setDataType("date");
			$preventiveActionsImplementedDate->setLength(255);
			$preventiveActionsImplementedDate->setRowTitle("preventive_actions_implemented_date");
			$preventiveActionsImplementedDate->setRequired(false);
			$preventiveActionsImplementedDate->setTable("evaluation");
			$preventiveActionsImplementedDate->setHelpId(9025);
			$complaintJustifiedYes->add($preventiveActionsImplementedDate);
			
			$preventiveActionsValidationDate = new textbox("preventiveActionsValidationDate");
			$preventiveActionsValidationDate->setGroup("complaintJustifiedYes");
			$preventiveActionsValidationDate->setDataType("date");
			$preventiveActionsValidationDate->setLength(255);
			$preventiveActionsValidationDate->setRowTitle("preventive_actions_validation_date");
			$preventiveActionsValidationDate->setRequired(false);
			$preventiveActionsValidationDate->setTable("evaluation");
			$preventiveActionsValidationDate->setHelpId(9025);
			$complaintJustifiedYes->add($preventiveActionsValidationDate);
			
			
			
			
		
		}
		
		$implementedActions = new textarea("implementedActions");
		$implementedActions->setGroup("complaintJustifiedYes");
		$implementedActions->setDataType("text");
		$implementedActions->setRowTitle("implemented_actions");
		$implementedActions->setRequired(false);
		$implementedActions->setTable("evaluation");
		$implementedActions->setHelpId(9020);
		$complaintJustifiedYes->add($implementedActions);
		
		$implementedActionsyn = new radio("implementedActionsyn");
		$implementedActionsyn->setGroup("complaintJustifiedYes");
		$implementedActionsyn->setDataType("string");
		$implementedActionsyn->setLength(3);
		$implementedActionsyn->setArraySource(array(
			array('value' => 'Yes', 'display' => 'Yes'),
			array('value' => 'No', 'display' => 'No')
		));
		$implementedActionsyn->setRowTitle("implementedAction_entered");
		$implementedActionsyn->setRequired(false);
		$implementedActionsyn->setVisible(false);
		$implementedActionsyn->setValue("No");
		$implementedActionsyn->setTable("evaluation");
		$complaintJustifiedYes->add($implementedActionsyn);
		
		$implementedActionsAuthor = new textbox("implementedActionsAuthor");
		$implementedActionsAuthor->setGroup("complaintJustifiedYes");
		$implementedActionsAuthor->setDataType("string");
		$implementedActionsAuthor->setLength(255);
		$implementedActionsAuthor->setRowTitle("implemented_actions_author");
		$implementedActionsAuthor->setRequired(false);
		$implementedActionsAuthor->setTable("evaluation");
		$implementedActionsAuthor->setHelpId(9021);
		$complaintJustifiedYes->add($implementedActionsAuthor);
		
		$implementedActionsDate = new textbox("implementedActionsDate");
		$implementedActionsDate->setGroup("complaintJustifiedYes");
		$implementedActionsDate->setDataType("date");
		$implementedActionsDate->setLength(255);
		$implementedActionsDate->setRowTitle("implemented_actions_date");
		$implementedActionsDate->setRequired(false);
		$implementedActionsDate->setTable("evaluation");//was complaintEval?????
		$implementedActionsDate->setHelpId(9022);
		$complaintJustifiedYes->add($implementedActionsDate);
		
		$implementedActionsEstimated = new textbox("implementedActionsEstimated");
		$implementedActionsEstimated->setGroup("complaintJustifiedYes");
		$implementedActionsEstimated->setDataType("date");
		$implementedActionsEstimated->setLength(255);
		$implementedActionsEstimated->setRowTitle("implemented_actions_estimated");
		$implementedActionsEstimated->setRequired(false);
		$implementedActionsEstimated->setTable("evaluation");
		$implementedActionsEstimated->setHelpId(9023);
		$complaintJustifiedYes->add($implementedActionsEstimated);
		
		$implementedActionsImplementation = new textbox("implementedActionsImplementation");
		$implementedActionsImplementation->setGroup("complaintJustifiedYes");
		$implementedActionsImplementation->setDataType("date");
		$implementedActionsImplementation->setLength(255);
		$implementedActionsImplementation->setRowTitle("implemented_actions_implementation");
		$implementedActionsImplementation->setRequired(false);
		$implementedActionsImplementation->setTable("evaluation");
		$implementedActionsImplementation->setHelpId(9024);
		$complaintJustifiedYes->add($implementedActionsImplementation);
		
		$implementedActionsEffectiveness = new textbox("implementedActionsEffectiveness");
		$implementedActionsEffectiveness->setGroup("complaintJustifiedYes");
		$implementedActionsEffectiveness->setDataType("date");
		$implementedActionsEffectiveness->setLength(255);
		$implementedActionsEffectiveness->setRowTitle("implemented_actions_effectiveness");
		$implementedActionsEffectiveness->setRequired(false);
		$implementedActionsEffectiveness->setTable("evaluation");
		$implementedActionsEffectiveness->setHelpId(9025);
		$complaintJustifiedYes->add($implementedActionsEffectiveness);
		
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
			$managementSystemReviewed->setRequired(true);
			$managementSystemReviewed->setValue("NO");
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
			$managementSystemReviewedRef->setGroup("managementSystemRefYes");
			$managementSystemReviewedRef->setDataType("string");
			$managementSystemReviewedRef->setLength(255);
			$managementSystemReviewedRef->setRowTitle("management_system_yes_ref");
			$managementSystemReviewedRef->setRequired(false);
			$managementSystemReviewedRef->setTable("evaluation");
			$managementSystemReviewedRef->setHelpId(9027);
			$managementSystemRefYes->add($managementSystemReviewedRef);
			
			$managementSystemReviewedDate = new textbox("managementSystemReviewedDate");
			$managementSystemReviewedDate->setGroup("managementSystemRefYes");
			$managementSystemReviewedDate->setDataType("date");
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
			$fmea->setRequired(true);
			$fmea->setValue("NO");
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
			$fmeaRef->setGroup("fmeaDepGroup");
			$fmeaRef->setDataType("string");
			$fmeaRef->setLength(255);
			$fmeaRef->setRowTitle("fmea_yes_ref");
			$fmeaRef->setRequired(false);
			$fmeaRef->setTable("evaluation");
			$fmeaRef->setHelpId(9030);
			$fmeaDepGroup->add($fmeaRef);
			
			$fmeaDate = new textbox("fmeaDate");
			$fmeaDate->setGroup("fmeaDepGroup");
			$fmeaDate->setDataType("date");
			$fmeaDate->setLength(255);
			$fmeaDate->setRowTitle("fmea_ref_date");
			$fmeaDate->setRequired(false);
			$fmeaDate->setTable("evaluation");
			$fmeaDate->setHelpId(9031);
			$fmeaDepGroup->add($fmeaDate);
			
			
			$customerSpecification = new radio("customerSpecification");
			$customerSpecification->setGroup("customerSpecificationGroup");
			$customerSpecification->setDataType("string");
			$customerSpecification->setLength(5);
			$customerSpecification->setArraySource(array(
				array('value' => 'YES', 'display' => 'Yes'),
				array('value' => 'NO', 'display' => 'No'),
				array('value' => 'na', 'display' => 'N/A')
			));
			$customerSpecification->setRowTitle("customer_specification");
			$customerSpecification->setRequired(true);
			$customerSpecification->setValue("NO");
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
			$customerSpecificationRef->setGroup("customerSpecificationGroupYes");
			$customerSpecificationRef->setDataType("string");
			$customerSpecificationRef->setLength(255);
			$customerSpecificationRef->setRowTitle("customer_specification_ref");
			$customerSpecificationRef->setRequired(false);
			$customerSpecificationRef->setTable("evaluation");
			$customerSpecificationRef->setHelpId(9033);
			$customerSpecificationGroupYes->add($customerSpecificationRef);
			
			$customerSpecificationDate = new textbox("customerSpecificationDate");
			$customerSpecificationDate->setGroup("customerSpecificationGroupYes");
			$customerSpecificationDate->setDataType("date");
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
			$flowChart->setRequired(true);
			$flowChart->setValue("NO");
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
			$flowChartRef->setGroup("flowChartGroupYes");
			$flowChartRef->setDataType("string");
			$flowChartRef->setLength(255);
			$flowChartRef->setRowTitle("flow_chart_yes_ref");
			$flowChartRef->setRequired(false);
			$flowChartRef->setTable("evaluation");
			$flowChartRef->setHelpId(9030);
			$flowChartGroupYes->add($flowChartRef);
			
			$flowChartDate = new textbox("flowChartDate");
			$flowChartDate->setGroup("flowChartGroupYes");
			$flowChartDate->setDataType("date");
			$flowChartDate->setLength(255);
			$flowChartDate->setRowTitle("flow_chart_ref_date");
			$flowChartDate->setRequired(false);
			$flowChartDate->setTable("evaluation");
			$flowChartDate->setHelpId(9031);
			$flowChartGroupYes->add($flowChartDate);

///////////////////
		}
		
		$comments = new textarea("comments");
		$comments->setGroup("commentsGroup");
		$comments->setDataType("text");
		$comments->setRowTitle("additional_comments");
		$comments->setRequired(false);
		$comments->setTable("evaluation");
		$comments->setHelpId(9035);
		$commentsGroup->add($comments);
		
		
		
		//$processOwner = new dropdown("processOwner");
		$processOwner = new autocomplete("processOwner");
		$processOwner->setGroup("commentsGroup");
		$processOwner->setDataType("string");
		$processOwner->setRowTitle("chosen_complaint_owner");
		$processOwner->setRequired(false);
		//$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner->setUrl("/apps/complaints/ajax/complaintOwner?");
		$processOwner->setTable("evaluation");
		//$processOwner->clearData();
		$processOwner->setHelpId(8145);
		$transferOwnership2GroupYes->add($processOwner);
		
		$copyTo = new autocomplete("copyTo");
		$copyTo->setGroup("commentsGroup");
		$copyTo->setDataType("string");
		$copyTo->setRowTitle("CC");
		$copyTo->setRequired(false);
		$copyTo->setUrl("/apps/complaints/ajax/ccevaluation?");
		$copyTo->setTable("evaluation");
		$copyTo->setHelpId(8145);
		$transferOwnership2GroupYes->add($copyTo);
		
		$emailText = new textarea("emailText");
		$emailText->setGroup("commentsGroup");
		$emailText->setDataType("text");
		$emailText->setRowTitle("emailText");
		$emailText->setRequired(false);
		$emailText->setTable("evaluation");
		$emailText->setHelpId(9078);
		$transferOwnership2GroupYes->add($emailText);
		
		
		
		
		$submit = new submit("submit");
		$submit->setGroup("sentTo");
		$submit->setVisible(true);
		$submitGroup->add($submit);	
		
		
		$this->form->add($initiation);
		$this->form->add($isPORightNo);
		$this->form->add($transferOwnership2Group);
		$this->form->add($sampleReceivedGroup);
		$this->form->add($isSampleReceivedYes);	
		$this->form->add($sampleReceivedGroupAfter);
		$this->form->add($isComplaintCatRightNo);
		$this->form->add($complaintJustifiedGroup);
		$this->form->add($complaintJustifiedYes);
		$this->form->add($managementSystemGroup);
		$this->form->add($managementSystemRefYes);
		$this->form->add($fmeaGroup);
		$this->form->add($fmeaDepGroup);
		$this->form->add($customerSpecificationGroup);
		$this->form->add($customerSpecificationGroupYes);
		$this->form->add($flowChartGroup);
		$this->form->add($flowChartGroupYes);
		//$this->form->add($transferOwnership2Group);
		$this->form->add($commentsGroup);
		$this->form->add($transferOwnership2GroupYes);
		$this->form->add($submitGroup);
		
			
		
	}

	public function getEmailNotification($owner, $sender, $id, $action, $emailText, $complaintJustifiedStatus)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailText>" . $emailText . "</emailText><complaint_justified>" . $complaintJustifiedStatus . "</complaint_justified></$action>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		//$cc = $this->form->get("delegate_owner")->getValue();
	
		email::send($owner, /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("new_complaint_action") . " - ID: " . $id), "$email", "");
		
		return true;
	}

}

?>