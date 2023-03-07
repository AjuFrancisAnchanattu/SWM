<?php

include_once('complaintLib.php');

/**
 * Customer Evaluation Form
 * 
 * @package apps	
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 16/12/2010
 */
class customerEvaluation
{
	private $isValid = true;
	private $complaintId;
	private $loadFromSession;
		
	public $form;
	
	
	function __construct($complaintId, $loadFromSession)
	{
		$this->complaintLib = new complaintLib();
		
		$this->complaintId = $complaintId;
		
		$this->approval = new approval( $this->complaintId );
		
		$this->loadFromSession = $loadFromSession;
		
		$this->defineForm();
		
		$this->load();		
	}
	
	
	public function showForm()
	{
		return $this->form->output();
	}
	
	public function showFormReadOnly()
	{
		//$xml = $this->displayAuthoriseGoodsBox();
		
		$xml = $this->form->readOnlyOutput();
		
		return $xml;
	}
	
	private function setupFields()
	{	
		//if form is submitted:
		if( $this->complaintLib->isSubmitted( $this->complaintId, 'evaluation' ) )
		{
			if( $this->approval->started() )
			{
				$this->form->get("tempCategoryId")->setReadOnly(true);
				$this->form->get("complaintJustified")->setReadOnly(true);
				$this->form->get("full8d")->setReadOnly(true);
				$this->form->get("goodsAction")->setReadOnly(true);
			}
			
//			if( $this->goodsReturned() )
//			{
//				$this->form->get("complaintJustified")->setReadOnly(true);
//				$this->form->get("goodsAction")->setReadOnly(true);
//			}
//			
//			if( $this->goodsDisposed() )
//			{
//				$this->form->get("complaintJustified")->setReadOnly(true);
//				$this->form->get("goodsAction")->setReadOnly(true);
//			}
			
			//hide save/submit
			$this->form->get("submitStatus")->setVisible(false);
			
			//check if asked for return goods approval:
//			if( $this->returnGoodsRequested() )
//			{
//				//set goods action to read-only
//				$this->form->get("returnGoodsNTLogon")->setReadOnly(true);
//				$this->form->get("returnGoodsConfirmed")->setVisible(true);
//				
//				//check if approved/disapproved yet
//				if( $this->form->get("returnGoodsConfirmed")->getValue() != "" )
//				{
//					//show date and notes
//					$this->form->get("returnGoodsDate")->setVisible(true);
//					$this->form->get("returnGoodsNotes")->setVisible(true);
//				}
//			}
			
			//check if asked for dispose goods approval:
//			if( $this->disposeGoodsRequested() )
//			{
//				//set goods action to read-only
//				$this->form->get("disposeGoodsNTLogon")->setReadOnly(true);
//				$this->form->get("disposeGoodsConfirmed")->setVisible(true);
//				
//				//check if approved/disapproved yet
//				if( $this->form->get("disposeGoodsConfirmed")->getValue() != "" )
//				{
//					//show date and notes
//					$this->form->get("disposeGoodsDate")->setVisible(true);
//					$this->form->get("disposeGoodsNotes")->setVisible(true);
//				}
//			}
		}
	}
	
	public function validate()
	{	
		// If the form is set to be saved and is not already submitted, we bypass validation
		if (!$this->complaintLib->isSubmitted($this->complaintId, 'evaluation'))
		{
			if ($this->form->get("submitStatus")->getValue() == 0)
			{
				$this->isValid = $this->form->validateValuesOnly();
				
				return $this->isValid;
			}
		}
		
		$this->isValid = $this->form->validate();
		
		// Check if the correct category is set (S) if the complaint isn't valid
		if ($this->form->get("complaintJustified")->getValue() == 0)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT creditNoteRequested 
				FROM complaint 
				WHERE id = " . $this->complaintId);

			$fields = mysql_fetch_array($dataset);
			
			if ($fields['creditNoteRequested'] == 1)
			{
				$categoryId = $this->form->get("tempCategoryId")->getValue();
				
				$categoryText = complaintLib::getOptionText($categoryId);
				
				$categoryLetter = strtolower(substr($categoryText, 0, 1));
				
				// if selected complaint = please select and s category already in db...
				
				if ($categoryLetter != 's')
				{
					$this->form->get("tempCategoryId")->setErrorMessage("select_s_category");
					$this->form->get("tempCategoryId")->setValid(false);
					$this->isValid = false;
				}
			}
		}
		
		return $this->isValid;
	}
	
	
	/**
	 * Checks if form is valid
	 */
	public function isValid()
	{
		return $this->isValid;
	}
	
	
	/**
	 * Loads data into the form
	 */
	private function load()
	{
		page::addDebug("Loading Evaluation for Complaint ID = " . $this->complaintId, __FILE__, __LINE__);
		
		if (!$this->loadFromSession)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT * 
				FROM evaluation 
				WHERE complaintId = " . $this->complaintId);
		
			if (mysql_num_rows($dataset) == 1)
			{
				$fields = mysql_fetch_array($dataset);			
				
				// Populate the form with data
				$this->form->populate($fields);
				
				if ($this->complaintLib->isSubmitted($this->complaintId, 'evaluation'))
				{
					$this->form->get("newOwner")->setValue("");
					$this->form->get("tempCategoryId")->setValue($this->complaintLib->getComplaintCategory($this->complaintId));
				}
				else
				{
					//$this->form->get("submitStatus")->setValue(1);
				}
			}
			else
			{
				$this->form->get("tempCategoryId")->setValue($this->complaintLib->getComplaintCategory($this->complaintId));
			}
			
			// Check if attachment is to be updated
			$this->form->get("attachment")->load("/apps/customerComplaints/attachments/evaluation/" . $this->complaintId . "/");
		}
		else
		{
			$this->form->loadSessionData();
			$this->form->processPost();
		}
		
		$this->setupFields();
		
		$this->form->putValuesInSession();		
		$this->form->processDependencies(true);		
		//$this->complaintLib->fixDependencyValues($this->form);
	}
    
    private function alertLegal()
    {               
        $toUser = 'rsmith3';
        $ccEmail = 'maya.buchanan@scapa.com';
        $ccEmail = 'rhys.davies@scapa.com';
        
        myEmail::send(
            $this->complaintId, 
            'customer_complaints_legal', 
            $toUser, 
            "intranet",
            "",
            $ccEmail,
            true
        );
		
    }
    
    private function alertQualityHead()
    {         
        /*$toUser = 'intranet';
        
        $dataset =  mysql::getInstance()->selectDatabase("membership")->Execute("
			SELECT NTLogon
			FROM permissions
			WHERE permission = 'customerComplaints_QualityHead'");
        
        if (mysql_num_rows($dataset) > 0)
        {        
            $fields = mysql_fetch_array($dataset);
            $toUser = $fields['NTLogon'];
        }*/
        
		$toUser = 'ian.walker@scapa.com';
        $ccEmail = 'rhys.davies@scapa.com';
         
        myEmail::send(
            $this->complaintId, 
            'customer_complaints_qualityHead', 
            $toUser, 
            "intranet",
            "",
            $ccEmail,
            true
        );
    }
    
    /* If customer is claiming losses/damages, alert legal */
    private function checkSeverity($newRecord = false)
    {        
        // If evaluation is a new record, simply check the lossDamages field
        if ($newRecord)
        {
            if ($this->complaintLib->getOptionTranslation($this->form->get("severity")->getValue()) == "severe")
            {
                $this->alertQualityHead();
            }
        }
        else
        {
            // If record is not new, only check the severity field if the 
            // severity is not already set to Severe
            $selectSQL = "SELECT severity
    			FROM evaluation 
    			WHERE complaintId = " . $this->complaintId;
    	
    		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);
    		
    		if ($fields = mysql_fetch_array($dataset))
    		{	
                $severeId = $this->complaintLib->getOptionId("severe");
                
                if ($fields["severity"] != $severeId)
                {
                    if ($this->form->get("severity")->getValue() == $this->complaintLib->getOptionId("severe"))
                    {
                        $this->alertQualityHead();
                    }
                }
            }
        }
    }

    /* If customer is claiming losses/damages, alert legal */
    private function checkLossDamages($newRecord = false)
    {
        // If evaluation is a new record, simply check the lossDamages field
        if ($newRecord)
        {
            if ($this->form->get("lossDamages")->getValue() == 1)
            {
                $this->alertLegal();
            }
        }
        else
        {
            // If record is not new, only check the lossDamages field if the 
            // lossDamages is not already selected (to avoid sending emails on every update)            
            $selectSQL = "SELECT lossDamages
    			FROM evaluation 
    			WHERE complaintId = " . $this->complaintId;
    	
    		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);
    		
    		if ($fields = mysql_fetch_array($dataset))
    		{	
                if ($fields["lossDamages"] == 0)
                {
                    if ($this->form->get("lossDamages")->getValue() == 1)
                    {
                        $this->alertLegal();
                    }
                }
            }
        }
    }
    
	public function save()
	{
		$action = $this->determineAction();
		
		switch ( $action )
		{
			case 'INSERT_SUBMIT':
				$sendToUser = true;
				$setSubmissionValues = true;
				$submission = true;
				$updateComplaint = true;
				$trackChanges = true;
				
				$this->setEvaluationClosureDates();                
                $this->checkLossDamages(true);
                $this->checkSeverity(true);
				
				$sql = "INSERT INTO evaluation " . $this->form->generateInsertQuery("evaluation");
				break;
				
			case 'INSERT_SAVE':
				$sendToUser = false;
				$setSubmissionValues = false;
				$submission = false;
				$updateComplaint = false;
				$trackChanges = false;                
				
				$sql = "INSERT INTO evaluation " . $this->form->generateInsertQuery("evaluation");
				break;
				
			case 'UPDATE_SUBMIT':
				$sendToUser = true;
				$setSubmissionValues = true;
				$submission = true;
				$updateComplaint = true;
				$trackChanges = true;                
				
				$this->setEvaluationClosureDates();
                $this->checkLossDamages(false);
                $this->checkSeverity(false);
				
				$sql = "UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation") . " 
						WHERE complaintId = " . $this->complaintId;
				break;
				
			case 'UPDATE_SAVE':
				$sendToUser = false;
				$setSubmissionValues = false;
				$submission = false;
				$updateComplaint = false;
				$trackChanges = false;
				
				$sql = "UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation") . " 
						WHERE complaintId = " . $this->complaintId;	
				break;
				
			case 'UPDATE_EDIT':
				$updateOwners = true;
				$sendToUser = false;
				$setSubmissionValues = false;
				$submission = true;
				$updateComplaint = true;
				$trackChanges = true;
				
				$this->setEvaluationClosureDates();                
                $this->checkLossDamages(false);
                $this->checkSeverity(false);
				
				$sql = "UPDATE evaluation " . $this->form->generateUpdateQuery("evaluation") . " 
						WHERE complaintId = " . $this->complaintId;	
						
				//die( $sql );
				
				break;
		}
		
		if( $submission )
		{
			//$this->returnGoodsRequest();
		}
		
		if( $trackChanges )
		{
			$this->complaintLib->startRecordingChanges( $this->complaintId );
		}
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		if( $submission )
		{
			$this->evaluationClosures();
		}
		
		if( $setSubmissionValues )
		{
			$this->complaintLib->setSubmissionValues($this->complaintId, 'evaluation');
		}
		
		if( $updateComplaint )
		{
			$this->updateComplaint();
		}
		
		// Close Corrective Action / Validation Verification
		
		//add log
		$logId = $this->complaintLib->addLog($this->complaintId, 'evaluation_' . $action);
		
		if( $trackChanges )
		{
			$this->complaintLib->stopRecordingChanges($logId);
		}
			
		if( $sendToUser )
		{
			// Notify owners that the evaluation has been submitted
			myEmail::send(
				$this->complaintId, 
				'evaluation_created', 
				$this->complaintLib->getComplaintOwner( $this->complaintId), 
				currentuser::getInstance()->getNTLogon(),
				$this->form->get("emailText")->getValue()
			);
			
			myEmail::send(
				$this->complaintId, 
				'evaluation_created', 
				$this->complaintLib->getComplaintOwner( $this->complaintId, 'evaluation'), 
				currentuser::getInstance()->getNTLogon(),
				$this->form->get("emailText")->getValue()
			);
		}
		
		if (isset($updateOwners))
		{
			// Notify owners that the evaluation has been submitted
			myEmail::send(
				$this->complaintId, 
				'evaluation_updated', 
				$this->complaintLib->getComplaintOwner( $this->complaintId), 
				currentuser::getInstance()->getNTLogon(),
				$this->form->get("emailText")->getValue()
			);
			
			myEmail::send(
				$this->complaintId, 
				'evaluation_updated', 
				$this->complaintLib->getComplaintOwner( $this->complaintId, 'evaluation'), 
				currentuser::getInstance()->getNTLogon(),
				$this->form->get("emailText")->getValue()
			);
		}
			
		// Update Attachment			
		$this->form->get("attachment")->setFinalFileLocation("/apps/customerComplaints/attachments/evaluation/" . $this->complaintId . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
		
		// remove this form from session and go to summary page
		unset($_SESSION['apps'][$GLOBALS['app']]['customerEvaluation_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]);

		page::redirect("/apps/customerComplaints/index?complaintId=" . $this->complaintId);
	}
	
	private function defineForm()
	{
		$this->form = new myForm("customerEvaluation_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		/*
		 *	GROUPS
		 */
		
		//----
		$initiationGroup = new group("initiationGroup");
		$initiationGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$sampleReceivedGroup = new group("sampleReceivedGroup");
		$sampleReceivedGroup->setBorder(false);
		
		//controlled group
		$sampleReceivedDetailsGroup = new group("sampleReceivedDetailsGroup");
		$sampleReceivedDetailsGroup->setBorder(false);
		//----
		
		//----
		$analysisGroup = new group("analysisGroup");
		$analysisGroup->setBorder(false);
		//----
		
		//----
		$categoryCorrectGroup = new group("categoryCorrectGroup");
		$categoryCorrectGroup->setBorder(false);
		//----
		
		//----
		//controlling groups
		$full8dGroup = new group("full8dGroup");
		$full8dGroup->setBorder(false);
		
		$complaintJustifiedGroup = new group("complaintJustifiedGroup");
		$complaintJustifiedGroup->setBorder(false);
		
		//controlled groups
		$complaintJustified_non8d_DetailsGroup = new group("complaintJustified_non8d_DetailsGroup");
		$complaintJustified_non8d_DetailsGroup->setBorder(false);
		
		$goodsActionGroup = new group("goodsActionGroup");
		$goodsActionGroup->setBorder(false);
		
//		$returnGoodsRequestGroup = new group("returnGoodsRequestGroup");
//		$returnGoodsRequestGroup->setBorder(false);
//		
//		$disposeGoodsRequestGroup = new group("disposeGoodsRequestGroup");
//		$disposeGoodsRequestGroup->setBorder(false);
		
		$complaintJustified_8d_DetailsGroup = new group("complaintJustified_8d_DetailsGroup");
		$complaintJustified_8d_DetailsGroup->setBorder(false);
		
		$complaintJustified_non8d_DetailsGroup_2 = new group("complaintJustified_non8d_DetailsGroup_2");
		$complaintJustified_non8d_DetailsGroup_2->setBorder(false);
		
		$complaintJustified_8d_DetailsGroup_2 = new group("complaintJustified_8d_DetailsGroup_2");
		$complaintJustified_8d_DetailsGroup_2->setBorder(false);
		//----
		
		//----
		//controlling group
		$msrGroup = new group("msrGroup");
		$msrGroup->setBorder(false);
		
		//controlled group
		$msrDetailsGroup = new group("msrDetailsGroup");
		$msrDetailsGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$fmeaGroup = new group("fmeaGroup");
		$fmeaGroup->setBorder(false);
		
		//controlled group
		$fmeaDetailsGroup = new group("fmeaDetailsGroup");
		$fmeaDetailsGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$customerSpecGroup = new group("customerSpecGroup");
		$customerSpecGroup->setBorder(false);
		
		//controlled group
		$customerSpecDetailsGroup = new group("customerSpecDetailsGroup");
		$customerSpecDetailsGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$workInspectionGroup = new group("workInspectionGroup");
		$workInspectionGroup->setBorder(false);
		
		//controlled group
		$workInspectionDetailsGroup = new group("workInspectionDetailsGroup");
		$workInspectionDetailsGroup->setBorder(false);
		//----
		
		//----
		$additionalDetailsGroup = new group("additionalDetailsGroup");
		$additionalDetailsGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$correctiveActionGroup = new group("correctiveActionGroup");
		$correctiveActionGroup->setBorder(false);
		
		//controlled group
		$correctiveActionDetailsGroup = new group("correctiveActionDetailsGroup");
		$correctiveActionDetailsGroup->setBorder(false);
		//----
		
		//----
		//controlling group
		$validationVerificationGroup = new group("validationVerificationGroup");
		$validationVerificationGroup->setBorder(false);
		
		//controled group
		$validationVerificationDetailsGroup = new group("validationVerificationDetailsGroup");
		$validationVerificationDetailsGroup->setBorder(false);
		//----
		
		//----
		$submitStatusGroup = new group("submitStatusGroup");
		$submitStatusGroup->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		//----
		
		
		/*
		 *	FIELDS
		 */
		 
		//----
		$complaintId = new readOnly("complaintId");
		$complaintId->setTable("evaluation");
		$complaintId->setGroup("initiationGroup");
		$complaintId->setRowTitle("complaint_id");
		$complaintId->setValue( $this->complaintId);
		$complaintId->setVisible(false);
		$complaintId->setIgnore(false);
		$initiationGroup->add( $complaintId);
		//----
		 
		//----
		//controlling
		$sampleReceived = new myRadio("sampleReceived");
		$sampleReceived->setDataType("number");
		$sampleReceived->setTable("evaluation");
		$sampleReceived->setGroup("sampleReceivedGroup");
		$sampleReceived->setRowTitle("sample_received");
		$sampleReceived->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$sampleReceived->setValue(0);
		$sampleReceived->setRequired(true);
		$sampleReceived->setTranslate(true);
		$sampleReceived->setNullable(true);
		$sampleReceived->setHelpId(8039);
		
			// Dependency
			$sampleReceivedDependency = new dependency();
			$sampleReceivedDependency->addRule(new rule('sampleReceivedGroup', 'sampleReceived', 1));
			$sampleReceivedDependency->setGroup('sampleReceivedDetailsGroup');
			$sampleReceivedDependency->setShow(true);
			
		$sampleReceived->addControllingDependency($sampleReceivedDependency);
		$sampleReceivedGroup->add($sampleReceived);
		
		//controlled
		$sampleDate = new myCalendar("sampleDate");
		$sampleDate->setTable("evaluation");
		$sampleDate->setGroup("sampleReceivedDetailsGroup");
		$sampleDate->setRowTitle("date_sample_was_received");
		$sampleDate->setErrorMessage("textbox_date_error_future");
		$sampleDate->setRequired(false);
		$sampleDate->setNullable(true);
		$sampleDate->setHelpId(8040);
		$sampleReceivedDetailsGroup->add($sampleDate);
		//----
		
		//----
		$analysis = new myTextarea("analysis");
		$analysis->setDataType("text");
		$analysis->setTable("evaluation");
		$analysis->setGroup("analysisGroup");
		$analysis->setRowTitle("analysis");
		$analysis->setLargeTextarea(true);
		$analysis->setRequired(false);
		$analysis->setNullable(true);
		$analysis->setHelpId(9005);
		$analysisGroup->add($analysis);
		
		$analysisAuthor = new myAutocomplete("analysisAuthor");
		$analysisAuthor->setDataType("string");
		$analysisAuthor->setTable("evaluation");
		$analysisAuthor->setGroup("analysisGroup");
		$analysisAuthor->setRowTitle("author");
		$analysisAuthor->setIsAnNTLogon(true);
		$analysisAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$analysisAuthor->setErrorMessage("select_valid_employee");
		$analysisAuthor->setRequired(false);
		$analysisAuthor->setNullable(true);
		$analysisAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$analysisAuthor->setLength(50);
		$analysisAuthor->setHelpId(9006);
		$analysisGroup->add($analysisAuthor);
		
		$analysisDate = new myCalendar("analysisDate");
		$analysisDate->setTable("evaluation");
		$analysisDate->setGroup("analysisGroup");
		$analysisDate->setRowTitle("date");
		$analysisDate->setErrorMessage("textbox_date_error_future");
		$analysisDate->setRequired(false);
		$analysisDate->setNullable(true);
		$analysisDate->setHelpId(9007);
		$analysisGroup->add($analysisDate);
		
		$attachment = new myAttachment("attachment");
		$attachment->setTempFileLocation("/apps/customerComplaints/tmp");
		$attachment->setFinalFileLocation("/apps/customerComplaints/attachments/evaluation");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("evaluation");
		$attachment->setAnchorRef("attachment");
		$analysisGroup->add($attachment);
		//----
		
		//----
		$tempCategoryId = new myDropdown("tempCategoryId");
		$tempCategoryId->setDataType("string");
		$tempCategoryId->setTable("evaluation");
		$tempCategoryId->setGroup("categoryCorrectGroup");
		$tempCategoryId->setRowTitle("correct_complaint_category");
		$tempCategoryId->setErrorMessage("dropdown_error");
		$tempCategoryId->setTranslate(true);
		$tempCategoryId->setRequired(false);
		$tempCategoryId->setNullable(true);
		$tempCategoryId->setOnChange("evaluation.setEvaluationType();");
		$tempCategoryId->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('category') . "
			ORDER BY selectionOption ASC");
		$tempCategoryId->setHelpId(8005);
		$categoryCorrectGroup->add($tempCategoryId);
		//----
		
		//----
		//controlling
		$full8d = new myRadio("full8d");
		$full8d->setDataType("number");
		$full8d->setTable("evaluation");
		$full8d->setGroup("fill8dGroup");
		$full8d->setRowTitle("evaluation_type");
		$full8d->setLabel("evaluation_type");
		$full8d->setArraySource(array(
				array('value' => 1, 'display' => 'full_8d'),
				array('value' => 0, 'display' => 'root_cause_corrective_action')
			));
		$full8d->setValue(1);
		$full8d->setHelpId(830);
		$full8d->setTranslate(true);
		$full8d->setRequired(true);
		$full8d->setNullable(true);
		//$full8d->setHelpId();
		
			//to go with complaint justified (validated)
			$full8dDependency1 = new dependency();
			$full8dDependency1->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency1->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency1->setGroup('complaintJustified_8d_DetailsGroup');
			$full8dDependency1->setShow(true);
			
			$full8dDependency1_2 = new dependency();
			$full8dDependency1_2->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency1_2->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency1_2->setGroup('complaintJustified_8d_DetailsGroup_2');
			$full8dDependency1_2->setShow(true);
			
		$full8d->addControllingDependency($full8dDependency1);
		$full8d->addControllingDependency($full8dDependency1_2);
		
			//to go with msr
			$full8dDependency2 = new dependency();
			$full8dDependency2->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency2->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency2->setGroup('msrGroup');
			$full8dDependency2->setShow(true);
			
			$full8dDependency3 = new dependency();
			$full8dDependency3->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency3->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency3->addRule(new rule('msrGroup', 'msr', 1));
			$full8dDependency3->setGroup('msrDetailsGroup');
			$full8dDependency3->setShow(true);
		
		$full8d->addControllingDependency($full8dDependency2);
		$full8d->addControllingDependency($full8dDependency3);
		
			//to go with fmea
			$full8dDependency4 = new dependency();
			$full8dDependency4->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency4->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency4->setGroup('fmeaGroup');
			$full8dDependency4->setShow(true);
			
			$full8dDependency5 = new dependency();
			$full8dDependency5->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency5->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency5->addRule(new rule('fmeaGroup', 'fmea', 1));
			$full8dDependency5->setGroup('fmeaDetailsGroup');
			$full8dDependency5->setShow(true);
		
		$full8d->addControllingDependency($full8dDependency4);
		$full8d->addControllingDependency($full8dDependency5);
		
			//to go with customer spec
			$full8dDependency6 = new dependency();
			$full8dDependency6->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency6->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency6->setGroup('customerSpecGroup');
			$full8dDependency6->setShow(true);
			
			$full8dDependency7 = new dependency();
			$full8dDependency7->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency7->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency7->addRule(new rule('customerSpecGroup', 'customerSpec', 1));
			$full8dDependency7->setGroup('customerSpecDetailsGroup');
			$full8dDependency7->setShow(true);
		
		$full8d->addControllingDependency($full8dDependency6);
		$full8d->addControllingDependency($full8dDependency7);
		
			//to go with work inspection
			$full8dDependency8 = new dependency();
			$full8dDependency8->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency8->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency8->setGroup('workInspectionGroup');
			$full8dDependency8->setShow(true);
			
			$full8dDependency9 = new dependency();
			$full8dDependency9->addRule(new rule('full8dGroup', 'full8d', 1));
			$full8dDependency9->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$full8dDependency9->addRule(new rule('workInspectionGroup', 'workInspection', 1));
			$full8dDependency9->setGroup('workInspectionDetailsGroup');
			$full8dDependency9->setShow(true);
		
		$full8d->addControllingDependency($full8dDependency8);
		$full8d->addControllingDependency($full8dDependency9);
		
		$full8dGroup->add($full8d);
        
        $severity = new dropdown("severity");
		$severity->setGroup("complaintJustifiedGroup");
		$severity->setDataType("string");
		$severity->setRowTitle("severity");
        $severity->setLabel("complaint_validated");
		$severity->setTable("evaluation");
		$severity->setErrorMessage("dropdown_error");
		$severity->setTranslate(true);
		$severity->setRequired(false);
        $severity->setHelpId(998);
		$severity->setLength(20);
		$severity->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('severity') . "
			AND (active = 1 OR deactivatedDate >= '" . complaintLib::getComplaintDate($this->complaintId) . "')
			ORDER BY selectionOption ASC");
		$complaintJustifiedGroup->add($severity);
		
        $lossDamages = new myRadio("lossDamages");
		$lossDamages->setDataType("number");
		$lossDamages->setTable("evaluation");
		$lossDamages->setGroup("complaintJustifiedGroup");
		$lossDamages->setRowTitle("lossDamages");		
		$lossDamages->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$lossDamages->setValue(0);
		$lossDamages->setRequired(false);
		$lossDamages->setTranslate(true);
		$lossDamages->setNullable(true);
        $complaintJustifiedGroup->add($lossDamages);
        
		$complaintJustified = new myRadio("complaintJustified");
		$complaintJustified->setDataType("number");
		$complaintJustified->setTable("evaluation");
		$complaintJustified->setGroup("complaintJustifiedGroup");
		$complaintJustified->setRowTitle("complaint_validated");		
		$complaintJustified->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$complaintJustified->setValue(0);
		$complaintJustified->setRequired(true);
		$complaintJustified->setTranslate(true);
		$complaintJustified->setNullable(true);
		$complaintJustified->setHelpId(800);
		
		// Dependencies
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->setGroup('complaintJustified_non8d_DetailsGroup');
			$complaintJustifiedDependency->setShow(true);
		
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->setGroup('complaintJustified_non8d_DetailsGroup_2');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('complaintJustified_8d_DetailsGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('complaintJustified_8d_DetailsGroup_2');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			//to go with msr
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('msrGroup');
			$complaintJustifiedDependency->setShow(true);
		
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->addRule(new rule('msrGroup', 'msr', 1));
			$complaintJustifiedDependency->setGroup('msrDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('fmeaGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
			
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->addRule(new rule('fmeaGroup', 'fmea', 1));
			$complaintJustifiedDependency->setGroup('fmeaDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('customerSpecGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);	
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->addRule(new rule('customerSpecGroup', 'customerSpec', 1));
			$complaintJustifiedDependency->setGroup('customerSpecDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
		
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->setGroup('workInspectionGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
			
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('full8dGroup', 'full8d', 1));
			$complaintJustifiedDependency->addRule(new rule('workInspectionGroup', 'workInspection', 1));
			$complaintJustifiedDependency->setGroup('workInspectionDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
		
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->setGroup('correctiveActionGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('correctiveActionGroup', 'correctiveAction', 1));
			$complaintJustifiedDependency->setGroup('correctiveActionDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->setGroup('validationVerificationGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
			$complaintJustifiedDependency = new dependency();
			$complaintJustifiedDependency->addRule(new rule('complaintJustifiedGroup', 'complaintJustified', 1));
			$complaintJustifiedDependency->addRule(new rule('validationVerificationGroup', 'validationVerification', 1));
			$complaintJustifiedDependency->setGroup('validationVerificationDetailsGroup');
			$complaintJustifiedDependency->setShow(true);
			
		$complaintJustified->addControllingDependency($complaintJustifiedDependency);
		
		$complaintJustifiedGroup->add($complaintJustified);
		
		//controlled
		$teamLeader = new myAutocomplete("teamLeader");
		$teamLeader->setDataType("string");
		$teamLeader->setTable("evaluation");
		$teamLeader->setGroup("complaintJustified_non8d_DetailsGroup");
		$teamLeader->setRowTitle("team_leader");
		$teamLeader->setIsAnNTLogon(true);
		$teamLeader->setValidateQuery("membership", "employee", "NTLogon");
		$teamLeader->setErrorMessage("select_valid_employee");
		$teamLeader->setRequired(false);
		$teamLeader->setNullable(true);
		$teamLeader->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$teamLeader->setLength(50);
		$teamLeader->setHelpId(801);
		$complaintJustified_non8d_DetailsGroup->add($teamLeader);
		
		$teamMembers = new myTextarea("teamMembers");
		$teamMembers->setDataType("text");
		$teamMembers->setTable("evaluation");
		$teamMembers->setGroup("complaintJustified_non8d_DetailsGroup");
		$teamMembers->setRowTitle("team_members");
		$teamMembers->setHelpId(9013);
		$teamMembers->setNullable(true);
		$complaintJustified_non8d_DetailsGroup->add($teamMembers);
				
		$reasonsNonDetection = new myTextarea("reasonsNonDetection");
		$reasonsNonDetection->setDataType("text");
		$reasonsNonDetection->setTable("evaluation");
		$reasonsNonDetection->setGroup("complaintJustified_non8d_DetailsGroup");
		$reasonsNonDetection->setRowTitle("reasons_non_detection");
		$reasonsNonDetection->setLabel("reasons_non_detection");
		$reasonsNonDetection->setLargeTextarea(true);
		$reasonsNonDetection->setRequired(false);
		$reasonsNonDetection->setNullable(true);
		$complaintJustified_non8d_DetailsGroup->add($reasonsNonDetection);
		
		$rootCauses = new myTextarea("rootCauses");
		$rootCauses->setDataType("text");
		$rootCauses->setTable("evaluation");
		$rootCauses->setGroup("complaintJustified_non8d_DetailsGroup");
		$rootCauses->setRowTitle("root_causes");
		$rootCauses->setLabel("root_causes");
		$rootCauses->setLargeTextarea(true);
		$rootCauses->setRequired(false);
		$rootCauses->setNullable(true);
		$rootCauses->setHelpId(9014);
		$complaintJustified_non8d_DetailsGroup->add($rootCauses);
		
		$rootCauseAuthor = new myAutocomplete("rootCauseAuthor");
		$rootCauseAuthor->setDataType("string");
		$rootCauseAuthor->setTable("evaluation");
		$rootCauseAuthor->setGroup("complaintJustified_non8d_DetailsGroup");
		$rootCauseAuthor->setRowTitle("author");
		$rootCauseAuthor->setIsAnNTLogon(true);
		$rootCauseAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$rootCauseAuthor->setErrorMessage("select_valid_employee");
		$rootCauseAuthor->setRequired(true);
		$rootCauseAuthor->setNullable(true);
		$rootCauseAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$rootCauseAuthor->setLength(50);
		$rootCauseAuthor->setHelpId(9015);
		$complaintJustified_non8d_DetailsGroup->add($rootCauseAuthor);
		
		$rootCauseDate = new myCalendar("rootCauseDate");
		$rootCauseDate->setTable("evaluation");
		$rootCauseDate->setGroup("complaintJustified_non8d_DetailsGroup");
		$rootCauseDate->setRowTitle("date");
		$rootCauseDate->setErrorMessage("textbox_date_error_future");
		$rootCauseDate->setRequired(true);
		$rootCauseDate->setNullable(true);
		$rootCauseDate->setHelpId(802);
		$complaintJustified_non8d_DetailsGroup->add($rootCauseDate);
		
		$rootCauseCode = new myDropdown("rootCauseCode");
		$rootCauseCode->setDataType("string");
		$rootCauseCode->setTable("evaluation");
		$rootCauseCode->setGroup("complaintJustified_non8d_DetailsGroup");
		$rootCauseCode->setRowTitle("attributable_function");
		$rootCauseCode->setErrorMessage("dropdown_error");
		$rootCauseCode->setTranslate(true);
		$rootCauseCode->setRequired(true);
		$rootCauseCode->setNullable(true);
		$rootCauseCode->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('rootCauseCode') . "
			AND (active = 1 OR deactivatedDate >= '" . complaintLib::getComplaintDate($this->complaintId) . "')
			ORDER BY selectionOption ASC");
		$rootCauseCode->setHelpId(831);
		$complaintJustified_non8d_DetailsGroup->add($rootCauseCode);
		
		$attributableProcess = new myDropdown("attributableProcess");
		$attributableProcess->setDataType("string");
		$attributableProcess->setTable("evaluation");
		$attributableProcess->setGroup("complaintJustified_non8d_DetailsGroup");
		$attributableProcess->setRowTitle("attributable_process");
		$attributableProcess->setErrorMessage("dropdown_error");
		$attributableProcess->setTranslate(true);
		$attributableProcess->setRequired(true);
		$attributableProcess->setNullable(true);
		$attributableProcess->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('attributableProcess') . "
			AND (active = 1 OR deactivatedDate >= '" . complaintLib::getComplaintDate($this->complaintId) . "')
			ORDER BY selectionOption ASC");
		$attributableProcess->setHelpId(833);
		$complaintJustified_non8d_DetailsGroup->add($attributableProcess);
		
		$failureCode = new myDropdown("failureCode");
		$failureCode->setDataType("string");
		$failureCode->setTable("evaluation");
		$failureCode->setGroup("complaintJustified_non8d_DetailsGroup");
		$failureCode->setRowTitle("failure_code");
		$failureCode->setErrorMessage("dropdown_error");
		$failureCode->setTranslate(true);
		$failureCode->setRequired(true);
		$failureCode->setNullable(true);
		$failureCode->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('failureCode') . "
			AND (active = 1 OR deactivatedDate >= '" . complaintLib::getComplaintDate($this->complaintId) . "')
			ORDER BY selectionOption ASC");
		$failureCode->setHelpId(832);
		$complaintJustified_non8d_DetailsGroup->add($failureCode);
							
		$goodsAction = new myRadio("goodsAction");
		$goodsAction->setDataType("number");
		$goodsAction->setTable("evaluation");
		$goodsAction->setGroup("goodsActionGroup");
		$goodsAction->setRowTitle("goods_action");
		$goodsAction->setLabel("goods_action");
		$goodsAction->setArraySource(array(
				array('value' => -1, 'display' => 'no_action'),
				array('value' => 1, 'display' => 'return_goods'),
				array('value' => 0, 'display' => 'dispose_goods')
			));
		$goodsAction->setValue(-1);
		$goodsAction->setRequired(true);
		$goodsAction->setNullable();
		$goodsAction->setHelpId(9017);
		$goodsAction->setTranslate(true);
		
//			$returnGoodsDependency = new dependency();
//			$returnGoodsDependency->addRule(new rule('goodsActionGroup', 'goodsAction', 1));
//			$returnGoodsDependency->setGroup('returnGoodsRequestGroup');
//			$returnGoodsDependency->setShow(true);
//			
//			$disposeGoodsDependency = new dependency();
//			$disposeGoodsDependency->addRule(new rule('goodsActionGroup', 'goodsAction', 0));
//			$disposeGoodsDependency->setGroup('disposeGoodsRequestGroup');
//			$disposeGoodsDependency->setShow(true);
			
//		$goodsAction->addControllingDependency($returnGoodsDependency);
//		$goodsAction->addControllingDependency($disposeGoodsDependency);
		
		$goodsActionGroup->add($goodsAction);
		
//		$approvalOwnerLink = new textboxlink("approvalOwnerLink");
//		$approvalOwnerLink->setGroup("returnGoodsRequestGroup");
//		$approvalOwnerLink->setRowTitle("approval_matrix");
//		$approvalOwnerLink->setHelpId(1118);
//		$approvalOwnerLink->setLink("http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Goods%20Return%20Approval%20Matrix.xlsx");
//		$approvalOwnerLink->setValue("{TRANSLATE:approval_matrix}");
//		$returnGoodsRequestGroup->add($approvalOwnerLink);
//		
//		$returnGoodsNTLogon = new myDropdown("returnGoodsNTLogon");
//		$returnGoodsNTLogon->setDataType("string");
//		$returnGoodsNTLogon->setGroup("returnGoodsRequestGroup");
//		$returnGoodsNTLogon->setTable("evaluation");
//		$returnGoodsNTLogon->setSqlSource("membership",
//			"SELECT CONCAT( emp.firstName, ' ' , emp.lastName) AS name, emp.NTLogon AS value 
//			FROM employee emp
//			JOIN permissions prm
//			ON prm.NTLogon = emp.NTLogon 
//			WHERE permission IN ('customerComplaints_approval_returnGoodsApprover') 
//			GROUP BY emp.NTLogon
//			ORDER BY emp.NTLogon ASC"
//		);
//		$returnGoodsNTLogon->setSqlSource("membership",
//			"SELECT CONCAT( emp.firstName, ' ' , emp.lastName) AS name, emp.NTLogon AS value 
//			FROM employee emp
//			WHERE NTLogon = 'rmarkiewka'  
//			GROUP BY emp.NTLogon
//			ORDER BY emp.NTLogon ASC"
//		);
//		$returnGoodsNTLogon->setLength(25);
//		$returnGoodsNTLogon->setRowTitle("sent_to");
//		$returnGoodsNTLogon->setRequired(true);
//		$returnGoodsNTLogon->setNullable();
//		$returnGoodsRequestGroup->add($returnGoodsNTLogon);
//		
//		$returnGoodsConfirmed = new myRadio("returnGoodsConfirmed");
//		$returnGoodsConfirmed->setDataType("number");
//		$returnGoodsConfirmed->setGroup("returnGoodsRequestGroup");
//		$returnGoodsConfirmed->setRowTitle("request_status");
//		$returnGoodsConfirmed->setArraySource(array(
//				array('value' => -1, 'display' => 'rejected'),
//				array('value' => 1, 'display' => 'approved'),
//				array('value' => "", 'display' => 'awaiting')
//			));
//		$returnGoodsConfirmed->setTranslate(true);
//		$returnGoodsConfirmed->setVisible(false);
//		$returnGoodsConfirmed->setReadOnly(true);
//		$returnGoodsConfirmed->setValue("");
//		$returnGoodsRequestGroup->add($returnGoodsConfirmed);
//		
//		$returnGoodsDate = new myCalendar("returnGoodsDate");
//		$returnGoodsDate->setGroup("returnGoodsRequestGroup");
//		$returnGoodsDate->setRowTitle("date");
//		$returnGoodsDate->setVisible(false);
//		$returnGoodsDate->setReadOnly(true);
//		$returnGoodsRequestGroup->add($returnGoodsDate);
//		
//		$returnGoodsNotes = new readOnly("returnGoodsNotes");
//		$returnGoodsNotes->setDataType("text");
//		$returnGoodsNotes->setGroup("returnGoodsRequestGroup");
//		$returnGoodsNotes->setRowTitle("comments");
//		$returnGoodsNotes->setVisible(false);
//		$returnGoodsRequestGroup->add($returnGoodsNotes);
//		
//		$disposeApprovalOwnerLink = new textboxlink("disposeApprovalOwnerLink");
//		$disposeApprovalOwnerLink->setGroup("disposeGoodsRequestGroup");
//		$disposeApprovalOwnerLink->setRowTitle("approval_matrix");
//		$disposeApprovalOwnerLink->setHelpId(1118);
//		$disposeApprovalOwnerLink->setLink("http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Goods%20Return%20Approval%20Matrix.xlsx");
//		$disposeApprovalOwnerLink->setValue("{TRANSLATE:approval_matrix}");
//		$disposeGoodsRequestGroup->add($disposeApprovalOwnerLink);
//		
//		$disposeGoodsNTLogon = new myDropdown("disposeGoodsNTLogon");
//		$disposeGoodsNTLogon->setDataType("string");
//		$disposeGoodsNTLogon->setGroup("disposeGoodsRequestGroup");
//		$disposeGoodsNTLogon->setTable("evaluation");
//		$disposeGoodsNTLogon->setSqlSource("membership",
//			"SELECT CONCAT( emp.firstName, ' ' , emp.lastName) AS name, emp.NTLogon AS value 
//			FROM employee emp
//			JOIN permissions prm
//			ON prm.NTLogon = emp.NTLogon 
//			WHERE permission IN ('customerComplaints_approval_returnGoodsApprover') 
//			GROUP BY emp.NTLogon
//			ORDER BY emp.NTLogon ASC"
//		);
//		$disposeGoodsNTLogon->setSqlSource("membership",
//			"SELECT CONCAT( emp.firstName, ' ' , emp.lastName) AS name, emp.NTLogon AS value 
//			FROM employee emp
//			WHERE NTLogon = 'rmarkiewka'  
//			GROUP BY emp.NTLogon
//			ORDER BY emp.NTLogon ASC"
//		);
//		$disposeGoodsNTLogon->setLength(25);
//		$disposeGoodsNTLogon->setRowTitle("sent_to");
//		$disposeGoodsNTLogon->setRequired(true);
//		$disposeGoodsNTLogon->setNullable();
//		$disposeGoodsRequestGroup->add($disposeGoodsNTLogon);
//		
//		$disposeGoodsConfirmed = new myRadio("disposeGoodsConfirmed");
//		$disposeGoodsConfirmed->setDataType("number");
//		$disposeGoodsConfirmed->setGroup("disposeGoodsRequestGroup");
//		$disposeGoodsConfirmed->setRowTitle("request_status");
//		$disposeGoodsConfirmed->setArraySource(array(
//				array('value' => -1, 'display' => 'rejected'),
//				array('value' => 1, 'display' => 'approved'),
//				array('value' => "", 'display' => 'awaiting')
//			));
//		$disposeGoodsConfirmed->setTranslate(true);
//		$disposeGoodsConfirmed->setVisible(false);
//		$disposeGoodsConfirmed->setReadOnly(true);
//		$disposeGoodsConfirmed->setValue("");
//		$disposeGoodsRequestGroup->add($disposeGoodsConfirmed);
//		
//		$disposeGoodsDate = new myCalendar("disposeGoodsDate");
//		$disposeGoodsDate->setGroup("disposeGoodsRequestGroup");
//		$disposeGoodsDate->setRowTitle("date");
//		$disposeGoodsDate->setVisible(false);
//		$disposeGoodsDate->setReadOnly(true);
//		$disposeGoodsRequestGroup->add($disposeGoodsDate);
//		
//		$disposeGoodsNotes = new readOnly("disposeGoodsNotes");
//		$disposeGoodsNotes->setDataType("text");
//		$disposeGoodsNotes->setGroup("disposeGoodsRequestGroup");
//		$disposeGoodsNotes->setRowTitle("comments");
//		$disposeGoodsNotes->setVisible(false);
//		$disposeGoodsRequestGroup->add($disposeGoodsNotes);
		
		$containmentActions = new myTextarea("containmentActions");
		$containmentActions->setDataType("text");
		$containmentActions->setTable("evaluation");
		$containmentActions->setGroup("complaintJustified_8d_DetailsGroup");
		$containmentActions->setRowTitle("containment_actions");
		$containmentActions->setLabel("containment_actions");
		$containmentActions->setLargeTextarea(true);
		$containmentActions->setRequired(false);
		$containmentActions->setNullable(true);
		$containmentActions->setHelpId(9087);
		$complaintJustified_8d_DetailsGroup->add($containmentActions);
		
		$containmentActionsAuthor = new myAutocomplete("containmentActionsAuthor");
		$containmentActionsAuthor->setDataType("string");
		$containmentActionsAuthor->setTable("evaluation");
		$containmentActionsAuthor->setGroup("complaintJustified_8d_DetailsGroup");
		$containmentActionsAuthor->setRowTitle("author");
		$containmentActionsAuthor->setIsAnNTLogon(true);
		$containmentActionsAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$containmentActionsAuthor->setErrorMessage("select_valid_employee");
		$containmentActionsAuthor->setRequired(false);
		$containmentActionsAuthor->setNullable(true);
		$containmentActionsAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$containmentActionsAuthor->setLength(50);
		$containmentActionsAuthor->setHelpId(9039);
		$complaintJustified_8d_DetailsGroup->add($containmentActionsAuthor);
		
		$containmentActionsDate = new myCalendar("containmentActionsDate");
		$containmentActionsDate->setTable("evaluation");
		$containmentActionsDate->setGroup("complaintJustified_8d_DetailsGroup");
		$containmentActionsDate->setRowTitle("date");
		$containmentActionsDate->setErrorMessage("textbox_date_error_future");
		$containmentActionsDate->setRequired(false);
		$containmentActionsDate->setNullable(true);
		$containmentActionsDate->setHelpId(803);
		$complaintJustified_8d_DetailsGroup->add($containmentActionsDate);
		
		$possibleSolutions = new myTextarea("possibleSolutions");
		$possibleSolutions->setDataType("text");
		$possibleSolutions->setTable("evaluation");
		$possibleSolutions->setGroup("complaintJustified_8d_DetailsGroup");
		$possibleSolutions->setRowTitle("possible_solutions");
		$possibleSolutions->setLabel("possible_solutions");
		$possibleSolutions->setLargeTextarea(true);
		$possibleSolutions->setRequired(false);
		$possibleSolutions->setNullable(true);
		$possibleSolutions->setHelpId(9088);
		$complaintJustified_8d_DetailsGroup->add($possibleSolutions);
		
		$possibleSolutionsAuthor = new myAutocomplete("possibleSolutionsAuthor");
		$possibleSolutionsAuthor->setDataType("string");
		$possibleSolutionsAuthor->setTable("evaluation");
		$possibleSolutionsAuthor->setGroup("complaintJustified_8d_DetailsGroup");
		$possibleSolutionsAuthor->setRowTitle("author");
		$possibleSolutionsAuthor->setIsAnNTLogon(true);
		$possibleSolutionsAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$possibleSolutionsAuthor->setErrorMessage("select_valid_employee");
		$possibleSolutionsAuthor->setRequired(false);
		$possibleSolutionsAuthor->setNullable(true);
		$possibleSolutionsAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$possibleSolutionsAuthor->setLength(50);
		$possibleSolutionsAuthor->setHelpId(804);
		$complaintJustified_8d_DetailsGroup->add($possibleSolutionsAuthor);
		
		$possibleSolutionsDate = new myCalendar("possibleSolutionsDate");
		$possibleSolutionsDate->setTable("evaluation");
		$possibleSolutionsDate->setGroup("complaintJustified_8d_DetailsGroup");
		$possibleSolutionsDate->setRowTitle("date");
		$possibleSolutionsDate->setErrorMessage("textbox_date_error_future");
		$possibleSolutionsDate->setRequired(false);
		$possibleSolutionsDate->setNullable(true);
		$possibleSolutionsDate->setHelpId(805);
		$complaintJustified_8d_DetailsGroup->add($possibleSolutionsDate);
		
		$correctiveActions = new myTextarea("correctiveActions");
		$correctiveActions->setDataType("text");
		$correctiveActions->setTable("evaluation");
		$correctiveActions->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActions->setRowTitle("implemented_perm_corrective_actions");
		$correctiveActions->setLabel("implemented_permanent_corrective_actions");
		$correctiveActions->setLargeTextarea(true);
		$correctiveActions->setRequired(false);
		$correctiveActions->setNullable(true);
		$correctiveActions->setHelpId(9020);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActions);
		
		$correctiveActionsAuthor = new myAutocomplete("correctiveActionsAuthor");
		$correctiveActionsAuthor->setDataType("string");
		$correctiveActionsAuthor->setTable("evaluation");
		$correctiveActionsAuthor->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsAuthor->setRowTitle("author");
		$correctiveActionsAuthor->setIsAnNTLogon(true);
		$correctiveActionsAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$correctiveActionsAuthor->setErrorMessage("select_valid_employee");
		$correctiveActionsAuthor->setRequired(false);
		$correctiveActionsAuthor->setNullable(true);
		$correctiveActionsAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$correctiveActionsAuthor->setLength(50);
		$correctiveActionsAuthor->setHelpId(9021);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsAuthor);
		
		$correctiveActionsDate = new myCalendar("correctiveActionsDate");
		$correctiveActionsDate->setTable("evaluation");
		$correctiveActionsDate->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsDate->setRowTitle("date");
		$correctiveActionsDate->setErrorMessage("textbox_date_error_future");
		$correctiveActionsDate->setRequired(false);
		$correctiveActionsDate->setNullable(true);
		$correctiveActionsDate->setHelpId(9022);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsDate);
		
		$correctiveActionsEstDate = new myCalendar("correctiveActionsEstDate");
		$correctiveActionsEstDate->setTable("evaluation");
		$correctiveActionsEstDate->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsEstDate->setRowTitle("estimated_date");
		$correctiveActionsEstDate->setErrorMessage("textbox_date_error_future");
		$correctiveActionsEstDate->setRequired(false);
		$correctiveActionsEstDate->setNullable(true);
		$correctiveActionsEstDate->setHelpId(9023);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsEstDate);
		
		$correctiveActionsImpDate = new myCalendar("correctiveActionsImpDate");
		$correctiveActionsImpDate->setTable("evaluation");
		$correctiveActionsImpDate->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsImpDate->setRowTitle("implementation_date");
		$correctiveActionsImpDate->setErrorMessage("textbox_date_error_future");
		$correctiveActionsImpDate->setRequired(false);
		$correctiveActionsImpDate->setNullable(true);
		$correctiveActionsImpDate->setHelpId(9024);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsImpDate);
		
		$correctiveActionsEffectivenessValidationDate = new myCalendar("correctiveActionsEffectivenessValidationDate");
		$correctiveActionsEffectivenessValidationDate->setTable("evaluation");
		$correctiveActionsEffectivenessValidationDate->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsEffectivenessValidationDate->setRowTitle("validation_date");
		$correctiveActionsEffectivenessValidationDate->setErrorMessage("textbox_date_error_future");
		$correctiveActionsEffectivenessValidationDate->setRequired(false);
		$correctiveActionsEffectivenessValidationDate->setNullable(true);
		$correctiveActionsEffectivenessValidationDate->setHelpId(834);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsEffectivenessValidationDate);
		
		$correctiveActionsValidation = new myTextarea("correctiveActionsValidation");
		$correctiveActionsValidation->setDataType("text");
		$correctiveActionsValidation->setTable("evaluation");
		$correctiveActionsValidation->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsValidation->setRowTitle("corrective_actions_validation");
		$correctiveActionsValidation->setLabel("corrective_actions_validation");
		$correctiveActionsValidation->setLargeTextarea(true);
		$correctiveActionsValidation->setRequired(false);
		$correctiveActionsValidation->setNullable(true);
		$correctiveActionsValidation->setHelpId(9094);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsValidation);
		
		$correctiveActionsValidationAuthor = new myAutocomplete("correctiveActionsValidationAuthor");
		$correctiveActionsValidationAuthor->setDataType("string");
		$correctiveActionsValidationAuthor->setTable("evaluation");
		$correctiveActionsValidationAuthor->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsValidationAuthor->setRowTitle("author");
		$correctiveActionsValidationAuthor->setIsAnNTLogon(true);
		$correctiveActionsValidationAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$correctiveActionsValidationAuthor->setErrorMessage("select_valid_employee");
		$correctiveActionsValidationAuthor->setRequired(false);
		$correctiveActionsValidationAuthor->setNullable(true);
		$correctiveActionsValidationAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$correctiveActionsValidationAuthor->setLength(50);
		$correctiveActionsValidationAuthor->setHelpId(806);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsValidationAuthor);
		
		$correctiveActionsValidationDate = new myCalendar("correctiveActionsValidationDate");
		$correctiveActionsValidationDate->setTable("evaluation");
		$correctiveActionsValidationDate->setGroup("complaintJustified_non8d_DetailsGroup_2");
		$correctiveActionsValidationDate->setRowTitle("date");
		$correctiveActionsValidationDate->setErrorMessage("textbox_date_error_future");
		$correctiveActionsValidationDate->setRequired(false);
		$correctiveActionsValidationDate->setNullable(true);
		$correctiveActionsValidationDate->setHelpId(807);
		$complaintJustified_non8d_DetailsGroup_2->add($correctiveActionsValidationDate);
		
		$preventiveActions = new myTextarea("preventiveActions");
		$preventiveActions->setDataType("text");
		$preventiveActions->setTable("evaluation");
		$preventiveActions->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActions->setRowTitle("preventive_actions");
		$preventiveActions->setLabel("preventive_actions");
		$preventiveActions->setLargeTextarea(true);
		$preventiveActions->setRequired(false);
		$preventiveActions->setNullable(true);
		$preventiveActions->setHelpId(9089);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActions);
		
		$preventiveActionsAuthor = new myAutocomplete("preventiveActionsAuthor");
		$preventiveActionsAuthor->setDataType("string");
		$preventiveActionsAuthor->setTable("evaluation");
		$preventiveActionsAuthor->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActionsAuthor->setRowTitle("author");
		$preventiveActionsAuthor->setIsAnNTLogon(true);
		$preventiveActionsAuthor->setValidateQuery("membership", "employee", "NTLogon");
		$preventiveActionsAuthor->setErrorMessage("select_valid_employee");
		$preventiveActionsAuthor->setRequired(false);
		$preventiveActionsAuthor->setNullable(true);
		$preventiveActionsAuthor->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$preventiveActionsAuthor->setLength(50);
		$preventiveActionsAuthor->setHelpId(9090);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActionsAuthor);
		
		$preventiveActionsDate = new myCalendar("preventiveActionsDate");
		$preventiveActionsDate->setTable("evaluation");
		$preventiveActionsDate->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActionsDate->setRowTitle("date");
		$preventiveActionsDate->setErrorMessage("textbox_date_error_future");
		$preventiveActionsDate->setRequired(false);
		$preventiveActionsDate->setNullable(true);
		$preventiveActionsDate->setHelpId(9091);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActionsDate);
		
		$preventiveActionsEstimatedDate = new myCalendar("preventiveActionsEstimatedDate");
		$preventiveActionsEstimatedDate->setTable("evaluation");
		$preventiveActionsEstimatedDate->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActionsEstimatedDate->setRowTitle("estimated_date");
		$preventiveActionsEstimatedDate->setErrorMessage("textbox_date_error_future");
		$preventiveActionsEstimatedDate->setRequired(false);
		$preventiveActionsEstimatedDate->setNullable(true);
		$preventiveActionsEstimatedDate->setHelpId(9092);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActionsEstimatedDate);
		
		$preventiveActionsImplementationDate = new myCalendar("preventiveActionsImplementationDate");
		$preventiveActionsImplementationDate->setTable("evaluation");
		$preventiveActionsImplementationDate->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActionsImplementationDate->setRowTitle("implementation_date");
		$preventiveActionsImplementationDate->setErrorMessage("textbox_date_error_future");
		$preventiveActionsImplementationDate->setRequired(false);
		$preventiveActionsImplementationDate->setNullable(true);
		$preventiveActionsImplementationDate->setHelpId(9093);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActionsImplementationDate);
		
		$preventiveActionsValidationDate = new myCalendar("preventiveActionsValidationDate");
		$preventiveActionsValidationDate->setTable("evaluation");
		$preventiveActionsValidationDate->setGroup("complaintJustified_8d_DetailsGroup_2");
		$preventiveActionsValidationDate->setRowTitle("validation_date");
		$preventiveActionsValidationDate->setErrorMessage("textbox_date_error_future");
		$preventiveActionsValidationDate->setRequired(false);
		$preventiveActionsValidationDate->setNullable(true);
		$preventiveActionsValidationDate->setHelpId(808);
		$complaintJustified_8d_DetailsGroup_2->add($preventiveActionsValidationDate);
		//----
		
		//----
		//controlling
		$msr = new myRadio("msr");
		$msr->setDataType("number");
		$msr->setTable("evaluation");
		$msr->setGroup("msrGroup");
		$msr->setRowTitle("management_system_rev");
		$msr->setLabel("management_system_rev");
		$msr->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$msr->setValue(0);
		$msr->setRequired(true);
		$msr->setTranslate(true);
		$msr->setNullable(true);
		$msr->setHelpId(835);
		
		// Dependency
		$msrDependency = new dependency();
		$msrDependency->addRule(new rule('msrGroup', 'msr', 1));
		$msrDependency->setGroup('msrDetailsGroup');
		$msrDependency->setShow(true);
			
		$msr->addControllingDependency($msrDependency);
		$msrGroup->add($msr);
		
		//controlled
		$msrReference = new myTextbox("msrReference");
		$msrReference->setDataType("string");
		$msrReference->setTable("evaluation");
		$msrReference->setGroup("msrDetailsGroup");
		$msrReference->setRowTitle("reference");
		$msrReference->setRequired(false);
		$msrReference->setNullable(true);
		$msrReference->setLength(50);
		$msrReference->setHelpId(836);
		$msrDetailsGroup->add($msrReference);
		
		$msrDate = new myCalendar("msrDate");
		$msrDate->setTable("evaluation");
		$msrDate->setGroup("msrDetailsGroup");
		$msrDate->setRowTitle("date");
		$msrDate->setErrorMessage("textbox_date_error_future");
		$msrDate->setRequired(false);
		$msrDate->setNullable(true);
		$msrDate->setHelpId(837);
		$msrDetailsGroup->add($msrDate);
		//----
		
		//----
		//controlling
		$fmea = new myRadio("fmea");
		$fmea->setDataType("number");
		$fmea->setTable("evaluation");
		$fmea->setGroup("fmeaGroup");
		$fmea->setRowTitle("fmea");
		$fmea->setLabel("fmea");
		$fmea->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no'),
				array('value' => -1, 'display' => 'N/A')
			));
		$fmea->setValue(0);
		$fmea->setTranslate(true);
		$fmea->setRequired(true);
		$fmea->setNullable(true);
		$fmea->setHelpId(9029);
		
		// Dependency
		$fmeaDependency = new dependency();
		$fmeaDependency->addRule(new rule('fmeaGroup', 'fmea', 1));
		$fmeaDependency->setGroup('fmeaDetailsGroup');
		$fmeaDependency->setShow(true);
			
		$fmea->addControllingDependency($fmeaDependency);
		$fmeaGroup->add($fmea);
		
		//controlled
		$fmeaReference = new myTextbox("fmeaReference");
		$fmeaReference->setDataType("string");
		$fmeaReference->setTable("evaluation");
		$fmeaReference->setGroup("fmeaDetailsGroup");
		$fmeaReference->setRowTitle("reference");
		$fmeaReference->setRequired(false);
		$fmeaReference->setNullable(true);
		$fmeaReference->setLength(50);
		$fmeaReference->setHelpId(838);
		$fmeaDetailsGroup->add($fmeaReference);
		
		$fmeaDate = new myCalendar("fmeaDate");
		$fmeaDate->setTable("evaluation");
		$fmeaDate->setGroup("fmeaDetailsGroup");
		$fmeaDate->setRowTitle("date");
		$fmeaDate->setErrorMessage("textbox_date_error_future");
		$fmeaDate->setRequired(false);
		$fmeaDate->setNullable(true);
		$fmeaDate->setHelpId(839);
		$fmeaDetailsGroup->add($fmeaDate);
		//----
		
		//----
		//controlling
		$customerSpec = new myRadio("customerSpec");
		$customerSpec->setDataType("number");
		$customerSpec->setTable("evaluation");
		$customerSpec->setGroup("customerSpecGroup");
		$customerSpec->setRowTitle("customer_specification");
		$customerSpec->setLabel("customer_specification");
		$customerSpec->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$customerSpec->setValue(0);
		$customerSpec->setTranslate(true);
		$customerSpec->setRequired(true);
		$customerSpec->setNullable(true);
		$customerSpec->setHelpId(840);
		
		// Dependency
		$customerSpecDependency = new dependency();
		$customerSpecDependency->addRule(new rule('customerSpecGroup', 'customerSpec', 1));
		$customerSpecDependency->setGroup('customerSpecDetailsGroup');
		$customerSpecDependency->setShow(true);
			
		$customerSpec->addControllingDependency($customerSpecDependency);
		$customerSpecGroup->add($customerSpec);
		
		//controlled
		$customerSpecReference = new myTextbox("customerSpecReference");
		$customerSpecReference->setDataType("string");
		$customerSpecReference->setTable("evaluation");
		$customerSpecReference->setGroup("customerSpecDetailsGroup");
		$customerSpecReference->setRowTitle("reference");
		$customerSpecReference->setRequired(false);
		$customerSpecReference->setNullable(true);
		$customerSpecReference->setLength(50);
		$customerSpecReference->setHelpId(841);
		$customerSpecDetailsGroup->add($customerSpecReference);
		
		$customerSpecDate = new myCalendar("customerSpecDate");
		$customerSpecDate->setTable("evaluation");
		$customerSpecDate->setGroup("customerSpecDetailsGroup");
		$customerSpecDate->setRowTitle("date");
		$customerSpecDate->setErrorMessage("textbox_date_error_future");
		$customerSpecDate->setRequired(false);
		$customerSpecDate->setNullable(true);
		$customerSpecDate->setHelpId(842);
		$customerSpecDetailsGroup->add($customerSpecDate);
		//----
		
		//----
		//controlling
		$workInspection = new myRadio("workInspection");
		$workInspection->setDataType("number");
		$workInspection->setTable("evaluation");
		$workInspection->setGroup("workInspectionGroup");
		$workInspection->setRowTitle("flow_chart");
		$workInspection->setLabel("flow_chart");
		$workInspection->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$workInspection->setValue(0);
		$workInspection->setRequired(true);
		$workInspection->setTranslate(true);
		$workInspection->setNullable(true);
		$workInspection->setHelpId(809);
		
		// Dependency
		$workInspectionDependency = new dependency();
		$workInspectionDependency->addRule(new rule('workInspectionGroup', 'workInspection', 1));
		$workInspectionDependency->setGroup('workInspectionDetailsGroup');
		$workInspectionDependency->setShow(true);
			
		$workInspection->addControllingDependency($workInspectionDependency);
		$workInspectionGroup->add($workInspection);
		
		//controlled
		$workInspectionReference = new myTextbox("workInspectionReference");
		$workInspectionReference->setDataType("string");
		$workInspectionReference->setTable("evaluation");
		$workInspectionReference->setGroup("workInspectionDetailsGroup");
		$workInspectionReference->setRowTitle("reference");
		$workInspectionReference->setRequired(false);
		$workInspectionReference->setNullable(true);
		$workInspectionReference->setLength(50);
		$workInspectionReference->setHelpId(810);
		$workInspectionDetailsGroup->add($workInspectionReference);
		
		$workInspectionDate = new myCalendar("workInspectionDate");
		$workInspectionDate->setTable("evaluation");
		$workInspectionDate->setGroup("workInspectionDetailsGroup");
		$workInspectionDate->setRowTitle("date");
		$workInspectionDate->setErrorMessage("textbox_date_error_future");
		$workInspectionDate->setRequired(false);
		$workInspectionDate->setNullable(true);
		$workInspectionDate->setHelpId(811);
		$workInspectionDetailsGroup->add($workInspectionDate);
		//----
		
		//----
		$additionalComments = new myTextarea("additionalComments");
		$additionalComments->setDataType("text");
		$additionalComments->setTable("evaluation");
		$additionalComments->setGroup("additionalDetailsGroup");
		$additionalComments->setRowTitle("additional_comments");
		$additionalComments->setLabel("other_details");
		$additionalComments->setRequired(false);
		$additionalComments->setNullable();
		$additionalComments->setHelpId(812);
		$additionalDetailsGroup->add($additionalComments);
		
		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1118);
		$processOwnerLink->setLink("http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Process%20Owner%20Matrix.xls");
		$processOwnerLink->setValue("{TRANSLATE:process_owner_matrix}");
		$additionalDetailsGroup->add($processOwnerLink);
		
		$newOwner = new myAutocomplete("newOwner");
		$newOwner->setDataType("string");
		$newOwner->setTable("evaluation");
		$newOwner->setGroup("additionalDetailsGroup");
		$newOwner->setRowTitle("new_evaluation_owner");
		$newOwner->setValidateQuery("membership", "employee", "NTLogon");
		$newOwner->setErrorMessage("select_valid_employee");
		$newOwner->setRequired(false);
		$newOwner->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$newOwner->setLength(50);
		$newOwner->setHelpId(813);
		$additionalDetailsGroup->add($newOwner);
		
		$emailText = new myTextarea("emailText");
		$emailText->setDataType("text");
		$emailText->setTable("evaluation");
		$emailText->setGroup("additionalDetailsGroup");
		$emailText->setRowTitle("email_text");
		$emailText->setrequired(false);
		$emailText->setNullable();
		$emailText->setHelpId(8045);
		$additionalDetailsGroup->add($emailText);
		//----
		
		//----
		//controlling
		$correctiveAction = new myRadio("correctiveAction");
		$correctiveAction->setDataType("number");
		$correctiveAction->setTable("evaluation");
		$correctiveAction->setGroup("correctiveActionGroup");
		$correctiveAction->setRowTitle("corrective_action_complete");
		$correctiveAction->setLabel("corrective_action_complete");
		$correctiveAction->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$correctiveAction->setValue(0);
		$correctiveAction->setRequired(true);
		$correctiveAction->setTranslate(true);
		$correctiveAction->setNullable();
		$correctiveAction->setHelpId(843);
		
		// Dependency
		$correctiveActionDependency = new dependency();
		$correctiveActionDependency->addRule(new rule('correctiveActionGroup', 'correctiveAction', 1));
		$correctiveActionDependency->setGroup('correctiveActionDetailsGroup');
		$correctiveActionDependency->setShow(true);
			
		$correctiveAction->addControllingDependency($correctiveActionDependency);
		$correctiveActionGroup->add($correctiveAction);
		
		//controlled
		$correctiveActionDate = new myInvisibletext("correctiveActionDate");
		$correctiveActionDate->setTable("evaluation");
		$correctiveActionDate->setGroup("correctiveActionDetailsGroup");
		$correctiveActionDate->setRowTitle("date");
		$correctiveActionDate->setNullable();
		$correctiveActionDetailsGroup->add($correctiveActionDate);
		
		$correctiveActionPerson = new myInvisibletext("correctiveActionPerson");
		$correctiveActionPerson->setGroup("correctiveActionDetailsGroup");
		$correctiveActionPerson->setTable("evaluation");
		$correctiveActionPerson->setRowTitle("employee");
		$correctiveActionPerson->setNullable();
		$correctiveActionDetailsGroup->add($correctiveActionPerson);
		
		$correctiveActionDetails = new myTextarea("correctiveActionDetails");
		$correctiveActionDetails->setDataType("text");
		$correctiveActionDetails->setTable("evaluation");
		$correctiveActionDetails->setGroup("correctiveActionDetailsGroup");
		$correctiveActionDetails->setRowTitle("details");
		$correctiveActionDetails->setHelpId(844);
		$correctiveActionDetails->setNullable();
		$correctiveActionDetailsGroup->add($correctiveActionDetails);
		//----
		
		//----
		//controlling
		$validationVerification = new myRadio("validationVerification");
		$validationVerification->setDataType("number");
		$validationVerification->setTable("evaluation");
		$validationVerification->setGroup("validationVerificationGroup");
		$validationVerification->setRowTitle("validation_verification_complete");
		$validationVerification->setLabel("validation_verification_complete");
		$validationVerification->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$validationVerification->setValue(0);
		$validationVerification->setRequired(true);
		$validationVerification->setTranslate(true);
		$validationVerification->setNullable();
		$validationVerification->setHelpId(845);
		
		// Dependency
		$validationVerificationDependency = new dependency();
		$validationVerificationDependency->addRule(new rule('validationVerificationGroup', 'validationVerification', 1));
		$validationVerificationDependency->setGroup('validationVerificationDetailsGroup');
		$validationVerificationDependency->setShow(true);
		
		$validationVerification->addControllingDependency($validationVerificationDependency);
		$validationVerificationGroup->add($validationVerification);
		
		//controlled
		$validationVerificationDate = new myInvisibletext("validationVerificationDate");
		$validationVerificationDate->setTable("evaluation");
		$validationVerificationDate->setGroup("validationVerificationDetailsGroup");
		$validationVerificationDate->setRowTitle("date");
		$validationVerificationDate->setNullable();
		$validationVerificationDetailsGroup->add($validationVerificationDate);
		
		$validationVerificationPerson = new myInvisibletext("validationVerificationPerson");
		$validationVerificationPerson->setGroup("validationVerificationDetailsGroup");
		$validationVerificationPerson->setTable("evaluation");
		$validationVerificationPerson->setRowTitle("employee");
		$validationVerificationPerson->setNullable();
		$validationVerificationDetailsGroup->add($validationVerificationPerson);
		
		$validationVerificationDetails = new myTextarea("validationVerificationDetails");
		$validationVerificationDetails->setDataType("text");
		$validationVerificationDetails->setTable("evaluation");
		$validationVerificationDetails->setGroup("validationVerificationDetailsGroup");
		$validationVerificationDetails->setRowTitle("details");
		$validationVerificationDetails->setHelpId(846);
		$validationVerificationDetails->setNullable();
		$validationVerificationDetailsGroup->add($validationVerificationDetails);
		//----
		
		//----
		$submitStatus = new myRadio("submitStatus");
		$submitStatus->setGroup("submitStatusGroup");
		$submitStatus->setDataType("boolean");
		$submitStatus->setLength(1);
		$submitStatus->setLabel("submit_option");
		$submitStatus->setArraySource(
			array(
				array('value' => 1, 'display' => 'submit'),
				array('value' => 0, 'display' => 'save')
			));
		$submitStatus->setRowTitle("submit_option");
		$submitStatus->setRequired(true);
		$submitStatus->setTranslate(true);
		$submitStatus->setTable("evaluation");
		$submitStatus->setHelpId(1120);
		$submitStatus->setValue(1);
		$submitStatusGroup->add($submitStatus);
		
		$submit = new submit("submit");
		$submit->setGroup("submitGroup");
		$submit->setVisible(true);
		$submit->setValue('Submit');
		$submitGroup->add($submit);
		//----
		
		
		/*
		 *	ADD GROUPS TO FORM
		 */
		 
		$this->form->add($initiationGroup);
		 
		$this->form->add($sampleReceivedGroup);
		$this->form->add($sampleReceivedDetailsGroup);
		
		$this->form->add($analysisGroup);
		
		$this->form->add($categoryCorrectGroup);
		
		$this->form->add($full8dGroup);
		$this->form->add($complaintJustifiedGroup);
		$this->form->add($complaintJustified_non8d_DetailsGroup);
		$this->form->add($goodsActionGroup);
		//$this->form->add($returnGoodsRequestGroup);
		//$this->form->add($disposeGoodsRequestGroup);
		$this->form->add($complaintJustified_8d_DetailsGroup);
		$this->form->add($complaintJustified_non8d_DetailsGroup_2);
		$this->form->add($complaintJustified_8d_DetailsGroup_2);
		
		$this->form->add($msrGroup);
		$this->form->add($msrDetailsGroup);
		
		$this->form->add($fmeaGroup);
		$this->form->add($fmeaDetailsGroup);
		
		$this->form->add($customerSpecGroup);
		$this->form->add($customerSpecDetailsGroup);
		
		$this->form->add($workInspectionGroup);
		$this->form->add($workInspectionDetailsGroup);
		
		$this->form->add($additionalDetailsGroup);
		
		$this->form->add($correctiveActionGroup);
		$this->form->add($correctiveActionDetailsGroup);
		
		$this->form->add($validationVerificationGroup);
		$this->form->add($validationVerificationDetailsGroup);
		
		$this->form->add($submitStatusGroup);
		$this->form->add($submitGroup);
	}
	
	
	private function getReturnGoodsAuthoriser()
	{
//		$authoriser = $this->form->get("returnGoodsNTLogon")->getValue();
//		
//		return $authoriser;
	}
	
	private function getDisposeGoodsAuthoriser()
	{
//		$authoriser = $this->form->get("disposeGoodsNTLogon")->getValue();
//		
//		return $authoriser;
	}
	
	
	private function displayAuthoriseGoodsBox()
	{
//		$selectSQL = "SELECT returnGoodsNTLogon, returnGoodsConfirmed, disposeGoodsNTLogon, disposeGoodsConfirmed
//			FROM evaluation 
//			WHERE complaintId = " . $this->complaintId;
//	
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($selectSQL);
//		
//		if ($fields = mysql_fetch_array($dataset))
//		{	
//			if ($fields['returnGoodsNTLogon'] == currentuser::getInstance()->getNTLogon() && 
//				$fields['returnGoodsConfirmed'] == NULL )
//			{
//				return '<displayAuthoriseGoodsBox>return</displayAuthoriseGoodsBox>';
//			}
//			
//			if ($fields['disposeGoodsNTLogon'] == currentuser::getInstance()->getNTLogon() && 
//				$fields['disposeGoodsConfirmed'] == NULL )
//			{
//				return '<displayAuthoriseGoodsBox>dispose</displayAuthoriseGoodsBox>';
//			}
//		}
//		
//		return '';
	}
	
	private function determineAction()
	{
		$sql = "SELECT * 
			FROM evaluation 
			WHERE complaintId = " . $this->complaintId;
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		if( mysql_num_rows($dataset) == 0 )
		{
			//form beeing filled in first time
			if( $this->form->get("submitStatus")->getValue() == 1 )
			{
				//user wants to submit the form for the first time
				return "INSERT_SUBMIT";
			}
			else
			{
				//user wants to save the form for the first time
				return "INSERT_SAVE";
			}
		}
		else
		{
			//user edits the form
			if( !$this->complaintLib->isSubmitted($this->complaintId, 'evaluation') )
			{
				//user edits saved form
				if( $this->form->get("submitStatus")->getValue() == 1 )
				{
					//user wants to submit previously saved and edited form
					return "UPDATE_SUBMIT";
				}
				else
				{
					//user wants to save previously saved and edited form
					return "UPDATE_SAVE";
				}
			}
			else
			{
				//user edits submitted form
				return "UPDATE_EDIT";
			}
		}
	}

	private function updateComplaint()
	{
		if( $this->form->get("newOwner")->getValue() != "")
		{
			$this->complaintLib->setComplaintOwner( $this->complaintId, $this->form->get("newOwner")->getValue(), "evaluation");
		}
		$this->complaintLib->setComplaintCategory( $this->complaintId, $this->form->get("tempCategoryId")->getValue());
		
		// Determine the status of the complaint and update the database if the complaint has now closed
		$this->complaintLib->totalClosure($this->complaintId);
	}
	
	private function returnGoodsRequest()
	{
//		if( $this->sendReturnRequest() )
//		{
//			$returnGoodsAuthoriser = $this->getReturnGoodsAuthoriser();
//
//			// E-mail Authoriser to ask for authorisation
//			myEmail::send(
//				$this->complaintId, 
//				"authorise_goods_return", 
//				$returnGoodsAuthoriser, 
//				currentuser::getInstance()->getNTLogon()
//			);
//			
//			//var_dump($returnGoodsAuthoriser);die();
//			
//			$description = '{TRANSLATE:sent_to} ' . usercache::getInstance()->get( $returnGoodsAuthoriser )->getName();
//			
//			$this->complaintLib->addLog($this->complaintId, 'evaluation_return_goods', $description);
//		}
//		
//		if( $this->sendDisposeRequest() )
//		{
//			$disposeGoodsAuthoriser = $this->getDisposeGoodsAuthoriser();
//
//			// E-mail Authoriser to ask for authorisation
//			myEmail::send(
//				$this->complaintId, 
//				"authorise_goods_disposal", 
//				$disposeGoodsAuthoriser, 
//				currentuser::getInstance()->getNTLogon()
//			);
//			
//			$description = '{TRANSLATE:sent_to} ' . usercache::getInstance()->get( $disposeGoodsAuthoriser )->getName();
//			
//			$this->complaintLib->addLog($this->complaintId, 'evaluation_dispose_goods', $description);
//		}
//		
//		if( $this->abortReturnRequest() )
//		{
//			$sql = "SELECT returnGoodsNTLogon,  returnGoodsConfirmed
//					FROM evaluation 
//					WHERE complaintId = " . $this->complaintId;
//					
//			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//			$fields = mysql_fetch_array( $dataset );
//				
//			if( $fields["returnGoodsConfirmed"] == NULL )
//			{
//				$returnGoodsAuthoriser = $fields['returnGoodsNTLogon'];
//				
//				// E-mail Authoriser to say authorisation no longer needed
//				myEmail::send(
//					$this->complaintId, 
//					"dont_authorise_goods_return", 
//					$returnGoodsAuthoriser, 
//					currentuser::getInstance()->getNTLogon()
//				);
//			}
//			
//			$this->complaintLib->addLog($this->complaintId, 'chancel_return_goods');
//			
//			$this->form->get("returnGoodsNTLogon")->setIgnore(true);
//			
//			$sql = "UPDATE evaluation 
//					SET returnGoodsNTLogon = NULL, 
//						returnGoodsDate = NULL,
//						returnGoodsNotes = NULL,
//						returnGoodsConfirmed = NULL
//					WHERE complaintId = " . $this->complaintId;
//					
//			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		}
//		
//		if( $this->abortDisposeRequest() )
//		{
//			$sql = "SELECT disposeGoodsNTLogon, disposeGoodsConfirmed
//					FROM evaluation 
//					WHERE complaintId = " . $this->complaintId;
//					
//			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//			$fields = mysql_fetch_array( $dataset );
//				
//			if( $fields["disposeGoodsConfirmed"] == NULL )
//			{
//				$disposeGoodsAuthoriser = $fields['disposeGoodsNTLogon'];
//				
//				// E-mail Authoriser to say authorisation no longer needed
//				myEmail::send(
//					$this->complaintId, 
//					"dont_authorise_goods_disposal", 
//					$disposeGoodsAuthoriser, 
//					currentuser::getInstance()->getNTLogon()
//				);
//			}
//			
//			$this->complaintLib->addLog($this->complaintId, 'chancel_dispose_goods');
//			
//			$this->form->get("disposeGoodsNTLogon")->setIgnore(true);
//			
//			$sql = "UPDATE evaluation 
//					SET disposeGoodsNTLogon = NULL, 
//						disposeGoodsDate = NULL,
//						disposeGoodsNotes = NULL,
//						disposeGoodsConfirmed = NULL
//					WHERE complaintId = " . $this->complaintId;
//					
//			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		}
//		
//		return;
	}
	
	private function returnGoodsRequested()
	{
//		$sql = "SELECT goodsAction, submitStatus  
//				FROM evaluation 
//				WHERE complaintId = " . $this->complaintId;
//				
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		$fields = mysql_fetch_array( $dataset );
//		
//		if( mysql_num_rows($dataset) > 0 && $fields['goodsAction'] == 1 && $fields['submitStatus'] == 1)
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function disposeGoodsRequested()
	{
//		$sql = "SELECT goodsAction, submitStatus  
//				FROM evaluation 
//				WHERE complaintId = " . $this->complaintId;
//				
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		$fields = mysql_fetch_array( $dataset );
//		
//		if( mysql_num_rows($dataset) > 0 && $fields['goodsAction'] == 0 && $fields['submitStatus'] == 1)
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function sendReturnRequest()
	{
//		if( !$this->returnGoodsRequested() && $this->form->get("goodsAction")->getValue() == 1 )
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function sendDisposeRequest()
	{
//		if( !$this->disposeGoodsRequested() && $this->form->get("goodsAction")->getValue() == 0 )
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function abortReturnRequest()
	{
//		if( $this->returnGoodsRequested() && $this->form->get("goodsAction")->getValue() != 1 )
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function abortDisposeRequest()
	{
//		if( $this->disposeGoodsRequested() && $this->form->get("goodsAction")->getValue() != 0 )
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function goodsDisposed()
	{
//		$sql = "SELECT * 
//				FROM evaluation 
//				WHERE complaintId = " . $this->complaintId;
//				
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		
//		$fields = mysql_fetch_assoc($dataset);
//		
//		if ($fields['goodsAction'] == 0 && $fields['disposeGoodsConfirmed'] == 1)
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function goodsReturned()
	{
//		$sql = "SELECT * 
//				FROM conclusionReturnedGoods 
//				WHERE complaintId = " . $this->complaintId;
//				
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
//		
//		if( mysql_num_rows( $dataset ) > 0 )
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	
	private function evaluationClosures()
	{
		if($this->form->get("complaintJustified")->getValue() == 0)
		{
			$date = common::nowDateForMysql();
			$person = currentuser::getInstance()->getNTLogon();
			
			$sql = "UPDATE evaluation 
					SET correctiveAction = 1,
						correctiveActionPerson = '$person',
						correctiveActionDate = '$date',
						validationVerification = 1,
						validationVerificationPerson = '$person',
						validationVerificationDate = '$date'
					WHERE complaintId = " . $this->complaintId;
					
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		}
	}
	
	private function setEvaluationClosureDates()
	{
		if( $this->form->get("correctiveAction")->getValue() == 1 && $this->form->get("correctiveActionDate")->getValue() == "" )
		{
			$this->form->get("correctiveActionDate")->setValue( common::nowDateForMysql() );
			$this->form->get("correctiveActionPerson")->setValue( currentuser::getInstance()->getNTLogon() );
		}
		
		if( $this->form->get("validationVerification")->getValue() == 1 &&  $this->form->get("validationVerificationDate")->getValue() == "" )
		{
			$this->form->get("validationVerificationDate")->setValue( common::nowDateForMysql() );
			$this->form->get("validationVerificationPerson")->setValue( currentuser::getInstance()->getNTLogon() );
		}
	}
}