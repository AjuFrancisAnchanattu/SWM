<?php

include_once('complaintLib.php');

/**
 * Customer Conclusion Form
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 24/11/2010
 */
class customerConclusion
{
	private $isValid = true;
	private $complaintId;
	private $loadFromSession;
	private $storedConclusion;
	private $submittedConclusion;
	private $approvalCompleted;
	private $readOnly;
	private $returnedGoods;
	private $disposedGoods;
	
	public $form;
	

	function __construct($complaintId, $loadFromSession, $readOnly = false)
	{
		$this->complaintId = $complaintId;
		$this->readOnly = $readOnly;
		$this->loadFromSession = $loadFromSession;
		
		$this->complaintLib = new complaintLib();
		$this->approval = new approval($this->complaintId);
		
		if (isset($_GET['approvalRollback']) && $_GET['approvalRollback'] == true)
		{
			$this->approvalRollback();
		}

		$this->submittedConclusion = $this->complaintLib->isSubmitted($this->complaintId, 'conclusion');
		
		$this->determineReturnedGoods();
		$this->determineDisposedGoods();
		
		$this->defineForm();
		$this->load();
	}
	
	
	private function determineDisposedGoods()
	{
		$this->awaitingDTGAuthorisation = false;
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT goodsAction, disposeGoodsConfirmed 
			FROM evaluation
			WHERE complaintId = " . $this->complaintId);

		if (mysql_num_rows($dataset) > 0)
		{		
			$fields = mysql_fetch_array($dataset);			
			
			if ($fields['goodsAction'] == 0 && $fields['disposeGoodsConfirmed'] == 1)
			{
				$this->disposedGoods = true;
			}
			else 
			{
				$this->disposedGoods = false;
				
				if ($fields['goodsAction'] == 0 && $fields['disposeGoodsConfirmed'] == 0)
				{
					$this->awaitingDTGAuthorisation = true;
				}
			}
		}
		else 
		{
			$this->disposedGoods = false;
		}
	}
	
	
	private function determineReturnedGoods()
	{
		$this->awaitingRTGAuthorisation = false;
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT goodsAction, returnGoodsConfirmed 
			FROM evaluation
			WHERE complaintId = " . $this->complaintId);

		if (mysql_num_rows($dataset) > 0)
		{		
			$fields = mysql_fetch_array($dataset);			
			
			if ($fields['goodsAction'] == 1 && $fields['returnGoodsConfirmed'] == 1)
			{
				$this->returnedGoods = true;
			}
			else 
			{
				$this->returnedGoods = false;
				
				if ($fields['goodsAction'] == 1 && $fields['returnGoodsConfirmed'] == 0)
				{					
					$this->awaitingRTGAuthorisation = true;
				}
			}
		}
		else 
		{
			$this->returnedGoods = false;
		}
	}
	
	public function showForm()
	{
		$this->setupFields();		
		
		return $this->form->output();
	}
	
	
	private function setupFields()
	{
		if ($this->approval->started())
		{
			//$this->form->get("disposalNoteDate")->setReadOnly(true);
			//$this->form->get("categoryId")->setReadOnly(true);
			//$this->form->get("modCategoryReason")->setReadOnly(true);
			$this->form->get("isCreditOrDebitNote")->setReadOnly(true);
		}		
	}	
	
	
	public function showFormReadOnly()
	{
		return $this->form->readOnlyOutput();
	}
	

	public function validate()
	{
		$this->isValid = $this->form->validate();
		
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
		page::addDebug("Loading Complaint ID = " . $this->complaintId, __FILE__, __LINE__);

		if (!$this->loadFromSession)
		{		
			// Load category
//			$datasetComplaint = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
//				SELECT categoryId
//				FROM complaint
//				WHERE id = " . $this->complaintId);
//	
//			$fieldsComplaint = mysql_fetch_array($datasetComplaint);			
//			
//			$this->form->populate($fieldsComplaint);
			
			if ($this->submittedConclusion)
			{
				$datasetConclusion = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					SELECT *
					FROM conclusion
					WHERE complaintId = " . $this->complaintId);
		
				$fieldsConclusion = mysql_fetch_array($datasetConclusion);
				
				$this->form->populate($fieldsConclusion);
				
				$this->form->get("attachment")->load("/apps/customerComplaints/attachments/conclusion/" . $this->complaintId . "/");
				
				if ($this->returnedGoods)
				{
					$datasetConclusionReturnedGoods = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						SELECT `complaintId`, `dateReturnsReceived`, `receiver`, CONCAT(`returnQuantityReceived_quantity`,'|',`returnQuantityReceived_measurement`) AS returnQuantityReceived, `lotNumber`, `condition`
						FROM conclusionReturnedGoods
						WHERE complaintId = " . $this->complaintId);
					
					if ($fieldsConclusion['returnsReceived'] == 1)
					{
						$fieldsConclusionReturnedGoods = mysql_fetch_array($datasetConclusionReturnedGoods);
						
						if ($fieldsConclusionReturnedGoods)
						{						
							$this->form->populate($fieldsConclusionReturnedGoods);
                            
                            $this->form->get("returnsReceived")->setReadOnly(true);
						}
					}
				}
				
				$this->loadReturnNos();
			}
		}
		else 
		{
			$this->form->loadSessionData();
			$this->form->processPost();
		}
			
		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
		
		if ($this->form->get("approvalOwnerMessage"))
		{
			$this->form->get("approvalOwnerMessage")->setValue("PLEASE CONSULT THE APPROVAL MATRIX AND ASK THE CORRECT APPROVER");
		}
	}
	
	
	public function saveReturnedGoods()
	{
		if ($this->form->get("dateReturnsReceived")->getValue() == NULL)
		{
			$dateReturnsReceived = "NULL,";
		}
		else 
		{
			$dateReturnsReceived = "'" . myCalendar::dateForSQL($this->form->get("dateReturnsReceived")->getValue()) . "',";
		}	
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT *
			FROM conclusionReturnedGoods
			WHERE complaintId = " . $this->complaintId);
			
		if ($this->form->get("returnsReceived")->getValue() == 1)
		{	
			if (mysql_num_rows($dataset) == 0)
			{			
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					INSERT INTO conclusionReturnedGoods 
					(`complaintId`, `dateReturnsReceived`, `receiver`, `returnQuantityReceived_quantity`, `returnQuantityReceived_measurement`, `lotNumber`, `condition`)
					VALUES (" . $this->complaintId . ", "
					. $dateReturnsReceived . " '" . 
					$this->form->get("receiver")->getValue() . "', '" .	
					$this->form->get("returnQuantityReceived")->getQuantity() . "', '" .
					$this->form->get("returnQuantityReceived")->getMeasurement() ."', '" .
					$this->form->get("lotNumber")->getValue() . "', '" .
					$this->form->get("condition")->getValue() . "')");
			}
			else 
			{
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					UPDATE conclusionReturnedGoods 				
					" . $this->form->generateUpdateQuery("conclusionReturnedGoods") . "  
					WHERE `complaintId` = " . $this->complaintId);
			}
		}
		else 
		{
			if (mysql_num_rows($dataset) != 0)
			{
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					DELETE FROM conclusionReturnedGoods
					WHERE complaintId = " . $this->complaintId);
			}
		}
	}
	
	/**
	 * Saves data from the form to the database
	 */
	public function save()
	{
		page::addDebug("Saving Complaint Conclusion",__FILE__,__LINE__);
		
		$this->complaintLib->startRecordingChanges( $this->complaintId );
			
		$logDescription = '';
		$comment = '';
		
		$this->saveReturnNos();
				
		// Check if a new complaint owner has been selected
		if ($this->form->get("complaintOwner")
			&& $this->form->get("complaintOwner")->getValue() != '')
		{
			$selectedComplaintOwner = $this->form->get("complaintOwner")->getValue();
		}
		
		if ($this->returnedGoods)
		{
			$this->saveReturnedGoods();
		}
	
		// If the conclusion hasn't been submitted, the form is inserted into the database
		if (!$this->submittedConclusion)
		{
			// Insert into conclusion table
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				INSERT INTO conclusion " . 
				$this->form->generateInsertQuery("conclusion"));
				
			$logAction = 'conclusion_created_submitted';
				
			// If approval is requested, insert the first approval stage
			if ($this->form->get("newApprovalYesNo") && $this->form->get("newApprovalYesNo")->getValue() == 1)
			{
				$newApprovalArr = $this->getVariablesAndSetupApprovalStage();					
				
				$owner = $newApprovalArr['owner'];
				$logDescription = $newApprovalArr['logDescription'];
			}
			else
			{		
				if (isset($selectedComplaintOwner))
				{
					$owner = $selectedComplaintOwner;
					$logDescription = "{TRANSLATE:sent_to} " . usercache::getInstance()->get($owner)->getName();

					// Email new owner that the complaint requires their attention
					myEmail::send(
						$this->complaintId, 
						"conclusion_updated", 
						$owner, 
						currentuser::getInstance()->getNTLogon()
					);
				}	
				else
				{
					$owner = $this->complaintLib->getCurrentComplaintOwner($this->complaintId);
				}
				
				if ($this->form->get("creditAuthorisation") && $this->form->get("creditAuthorisation")->getValue() == 1)
				{
					if (!isset($selectedComplaintOwner))
					{
						$owner = complaintLib::getInitiator($this->complaintId);
					}
										
					$logAction = 'conclusion_completed';
				}			
			}
			
			$this->updateComplaintOwnerAndCategory($owner, ''); //$this->form->get("categoryId")->getValue()

			// Notify current user that the conclusion has been submitted successfully
			myEmail::send(
				$this->complaintId, 
				"conclusion_created", 
				currentuser::getInstance()->getNTLogon(), 
				'intranet@scapa.com'
			);

			// Set submission date and submission person after the form is submitted
			$this->complaintLib->setSubmissionValues($this->complaintId, 'conclusion');
		}
		// If the conclusion is already stored it is updated
		else
		{				
			// Update the fields in the conclusion table
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				UPDATE conclusion " . $this->form->generateUpdateQuery("conclusion") . "
				WHERE complaintId = " . $this->complaintId);

			if ($this->approval->completed() &&
				!($this->form->get("creditAuthorisation") && $this->form->get("creditAuthorisation")->getValue() == 1))
			{				
				// Set the owner to the selected owner, otherwise the current user
				$owner = (isset($selectedComplaintOwner)) ? $selectedComplaintOwner : currentuser::getInstance()->getNTLogon();
				
				if (isset($selectedComplaintOwner) || $owner != currentuser::getInstance()->getName())
				{
					$logDescription = "{TRANSLATE:sent_to} " . usercache::getInstance()->get($owner)->getName();
					
					// Email new owner to say that the complaint requires their attention
					myEmail::send(
						$this->complaintId, 
						"conclusion_updated", 
						$owner, 
						currentuser::getInstance()->getNTLogon()
					);
				}
			}
			else if ($this->form->get("creditAuthorisation") && $this->form->get("creditAuthorisation")->getValue() == 1)
			{
				if ($this->complaintLib->totalClosure($this->complaintId))
				{
					$owner = complaintLib::getInitiator($this->complaintId);
					
					// Email initiator to say that the complaint requires their attention
					myEmail::send(
						$this->complaintId, 
						"complaint_closed", 
						$owner, 
						currentuser::getInstance()->getNTLogon()
					);
					
					$logAction = 'conclusion_completed';
				}
				else 
				{
					$owner = (isset($selectedComplaintOwner)) ? $selectedComplaintOwner : complaintLib::getInitiator($this->complaintId);
					
					$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						SELECT creditAuthorisationDate 
						FROM conclusion
						WHERE complaintId = " . $this->complaintId . "
						");
					
					$fields = mysql_fetch_assoc($dataset);
					
					if ($fields['creditAuthorisationDate'] == NULL)
					{
						$logAction = 'conclusion_completed';
					}				
				}
			}
			else if ($this->form->get("newApprovalYesNo"))
			{
				$newApprovalYesNo = $this->form->get("newApprovalYesNo")->getValue();
				$comment = ($this->form->get("newApprovalNotes")) ? $this->form->get("newApprovalNotes")->getValue() : '';
				
                $goodsActionApproval = ($this->form->get("goodsActionYesNo")) ? 
                    $this->form->get("goodsActionYesNo")->getValue() : 0;
                
				if ($this->approval->stage() != 0)
				{
					// Update the current approval stage
					$extraSQL = ($comment != '') ? ", notes = '" . complaintLib::transformForDB($comment) . "' " : '';
	
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						UPDATE approval
						SET dateCompleted = '" .  date("Y-m-d") . "',
						completedBy = '" . currentuser::getInstance()->getNTLogon() . "',
						approved = " . $newApprovalYesNo . ", 
                        goodsActionApproved = " . $goodsActionApproval .
						$extraSQL . "
						WHERE complaintId = " .  $this->complaintId . "
						AND approvalStage = " . $this->approval->stage());
				}
				
				if ($newApprovalYesNo == 1
					&& ($this->approval->stage() < $this->approval->maxStage()))
				{				
					$newApprovalArr = $this->getVariablesAndSetupApprovalStage($comment);					
					
					$owner = $newApprovalArr['owner'];			
					$logDescription = '{TRANSLATE:approval_stage} ' . $this->approval->stage() . ' {TRANSLATE:approved}. ' . $newApprovalArr['logDescription'];
				}
				else if ($newApprovalYesNo == 1
					&& ($this->approval->stage() == $this->approval->maxStage()))
				{
                    $initiator = complaintLib::getInitiator($this->complaintId);
                    $owner = $initiator;
                    
                    // determine type of goods action
                    $goodsAction = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						SELECT goodsAction
						FROM evaluation
						WHERE complaintId = " . $this->complaintId);
					
                    $goodsActionResult = mysql_fetch_array($goodsAction);
                                        
                    if ($goodsActionApproval == 1)
                    {
                        // check goods action (1 = return goods, 0 = dispose)
                        if ($goodsActionResult['goodsAction'] == 1)
                        {
                            // if the goods are coming back, set the owner to be the warehouse manager for the despatch site
                            $owner = complaintLib::getWarehouseManagerFromComplaintDespatchSite($this->complaintId);
                            
                            // save definitive goods action to evaluation table
                            mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
                                UPDATE evaluation
                                SET returnGoodsConfirmed = " . $goodsActionApproval . "
                                WHERE complaintId = " .  $this->complaintId);
                            
                            // email warehouse manager to let them know they are now the complaint owner
                            myEmail::send(
                                $this->complaintId, 
                                "warehouse_manager_email", 
                                $owner, 
                                currentuser::getInstance()->getNTLogon()
                            );
                        }
                        else if ($goodsActionResult['goodsAction'] == 0)
                        {
                            // save definitive goods action to evaluation table
                            mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
                                UPDATE evaluation
                                SET disposeGoodsConfirmed = " . $goodsActionApproval . "
                                WHERE complaintId = " .  $this->complaintId);
                        }
                    }
                                        
					$logAction = 'conclusion_updated_approval_completed';
                   
                    // let customer care know that credit has been approved
					myEmail::send(
						$this->complaintId, 
						"credit_approved", 
						$initiator, 
						currentuser::getInstance()->getNTLogon()
					);
				}
				else if ($newApprovalYesNo == 0
					&& $this->approval->stage() != 0)
				{
					$logAction = 'conclusion_updated_approval_rejected';	
					$owner = complaintLib::getInitiator($this->complaintId);
					$logDescription = '{TRANSLATE:sent_to} ' . usercache::getInstance()->get($owner)->getName();
					
					myEmail::send(
						$this->complaintId, 
						"credit_rejected", 
						$owner, 
						currentuser::getInstance()->getNTLogon()
					);
				}
			}
			
			if (!isset($owner))
			{
				$owner = (isset($selectedComplaintOwner)) ? $selectedComplaintOwner : $this->complaintLib->getComplaintOwner($this->complaintId);
			}
			
			if (!isset($logAction))
			{
				$logAction = 'conclusion_updated';
			}
								
			$this->updateComplaintOwnerAndCategory($owner, ''); //$this->form->get("categoryId")->getValue()
		}
		
		$this->setCompletionDatesAndPeople();
		
		// Determine the status of the complaint and update the database if the complaint has now closed
		$this->complaintLib->totalClosure($this->complaintId);
		
		// Add log
		$logId = $this->complaintLib->addLog($this->complaintId, $logAction, $logDescription, $comment);
		
		// Update attachment(s)
		$this->form->get("attachment")->setFinalFileLocation("/apps/customerComplaints/attachments/conclusion/" . $this->complaintId . "/");
		$this->form->get("attachment")->moveTempFileToFinal();

		$this->complaintLib->stopRecordingChanges($logId);
		
		// Remove this form from session
		unset($_SESSION['apps'][$GLOBALS['app']]['customerConclusion_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]);	

		// Redirect to summary page
		page::redirect("/apps/customerComplaints/index?complaintId=" . $this->complaintId);
	}

	
	/**
	 * Inserts new approval stage, emails new approver, and returns array containing the new owner and a log description
	 *
	 * @return array $newApprovalArr
	 */
	private function getVariablesAndSetupApprovalStage($comment = '')
	{
		$owner = $this->form->get("newApprovalPerson")->getValue();
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			INSERT INTO approval
			(complaintId, approvalStage, dateRequested, askedBy, assignedTo)
			VALUES (" . $this->complaintId . ", " .
			($this->approval->stage() + 1) . ", '" .
			date("Y-m-d") . "', '" .
			currentuser::getInstance()->getNTLogon() . "', '" .
			$owner . "')");	

		// Email approval person to complete the stage of approval
		myEmail::send(
			$this->complaintId, 
			"approve_conclusion", 
			$owner, 
			currentuser::getInstance()->getNTLogon(), 
			$comment
		);	

		$logDescription = "{TRANSLATE:sent_to} " . usercache::getInstance()->get($owner)->getName() . 
			' ({TRANSLATE:for_approval_stage} ' . ($this->approval->stage() + 1) . '/' . $this->approval->maxStage() . ')';
			
		$newApprovalArr = array('owner' => $owner, 'logDescription' => $logDescription);
			
		return $newApprovalArr;
	}
	
	
	/**
	 * Define the actual form
	 */
	private function defineForm()
	{
		$this->form = new myForm("customerConclusion_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		$sql = "SELECT * FROM complaint WHERE id = " . $this->complaintId;

		$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);

		$complaintFields = mysql_fetch_array($dataset);		
		
		$sql = "SELECT correctiveAction, validationVerification, complaintJustified, goodsAction, returnGoodsConfirmed, disposeGoodsConfirmed, submitStatus
			FROM evaluation
			WHERE complaintId = " . $this->complaintId;

		$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);

		$evaluationFields = mysql_fetch_array($dataset);

		$sapReturnNoGroup = new multiplegroup("sapReturnNoGroup");
		$sapReturnNoGroup->setTitle("sap_return_number");
		$sapReturnNoGroup->setAnchorRef("sapReturnNoGroup");
		$sapReturnNoGroup->setBorder(false);
		
		$group1 = new group("group1");
		$group1->setBorder(false);

		$categoryCorrectGroup = new group("categoryCorrectGroup");
		$categoryCorrectGroup->setBorder(false);

		$categoryCorrectDetailsGroup = new group("categoryCorrectDetailsGroup");
		$categoryCorrectDetailsGroup->setBorder(false);

		$attachmentGroup = new group("attachmentGroup");
		$attachmentGroup->setBorder(false);

		$creditGroup = new group("creditGroup");
		$creditGroup->setBorder(false);
		
		$finalCommentsGroup = new group("finalCommentsGroup");
		$finalCommentsGroup->setBorder(false);

		$group2 = new group("group2");
		$group2->setBorder(false);

		$closureGroup = new group("closureGroup");
		$closureGroup->setBorder(false);

		$creditNoGroup = new group("creditNoGroup");
		$creditNoGroup->setBorder(false);
		
		if ($this->returnedGoods)
		{
			$rtgGroup = new group("rtgGroup");
			$rtgGroup->setBorder(false);
			
			$rtgYesGroup = new group("rtgYesGroup");
			$rtgYesGroup->setBorder(false);
		}
		
		if( !$this->readOnly )
		{
			$newApprovalRollbackGroup = new group("newApprovalRollbackGroup");
			$newApprovalRollbackGroup->setBorder(false);
		}
		
		$closureGroup2 = new group("closureGroup2");
		$closureGroup2->setBorder(false);
				
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
	

		$sapReturnNo = new myTextbox("sapReturnNo");
		$sapReturnNo->setGroup("sapReturnNoGroup");
		$sapReturnNo->setDataType("string");
		//$sapReturnNo->setValidationType("number");
		$sapReturnNo->setLength(20);
		$sapReturnNo->setRowTitle("sap_return_number");
		if ($this->returnedGoods)
		{	
			$sapReturnNo->setRequired(true);
		}
		else
		{
			$sapReturnNo->setRequired(false);
		}
		$sapReturnNo->setHelpId(847);
		$sapReturnNoGroup->add($sapReturnNo);	
		
		$complaintId = new readOnly("complaintId");
		$complaintId->setTable("conclusion");
		$complaintId->setGroup("group1");
		$complaintId->setRowTitle("complaint_id");
		$complaintId->setValue($this->complaintId);
		$complaintId->setVisible(false);
		$complaintId->setIgnore(false);
		$group1->add( $complaintId);
				
		$disposalNoteDate = new myCalendar("disposalNoteDate");
		$disposalNoteDate->setGroup("group1");
		$disposalNoteDate->setErrorMessage("textbox_date_error");
		$disposalNoteDate->setRowTitle("date_disposal_note_signed_back");
		$disposalNoteDate->setRequired(false);
		//$disposalNoteDate->setNullable(true);
		$disposalNoteDate->setTable("conclusion");
		$disposalNoteDate->setHelpId(848);
		$group1->add($disposalNoteDate);
		
		$where = ($complaintFields['creditNoteRequested'] == '1' && $evaluationFields['complaintJustified'] == '0')
			? " AND selectionOption LIKE 'S%' " : '';		
		
//		$category = new myDropdown("categoryId");
//		$category->setTable("complaint");
//		$category->setTranslate(true);
//		$category->setSQLSource("complaintsCustomer",
//			"SELECT selectionOption AS name, id AS value
//			FROM selectionOptions
//			WHERE typeId = " . complaintLib::getOptionTypeId('category') . " " .
//			$where . "
//			ORDER BY selectionOption ASC");
//		$category->setDataType("string");
//		$category->setGroup("categoryCorrectDetailsGroup");
//		$category->setRowTitle("correct_category");
//		$category->setErrorMessage("dropdown_error");
//		$category->setRequired(false);
//		$category->setHelpId(8005);
//		$categoryCorrectDetailsGroup->add($category);

//		$modCategoryReason = new myTextarea("modCategoryReason");
//		$modCategoryReason->setGroup("categoryCorrectDetailsGroup");
//		$modCategoryReason->setDataType("text");
//		$modCategoryReason->setRowTitle("mod_complaint_reason");
//		$modCategoryReason->setRequired(false);
//		$modCategoryReason->setTable("conclusion");
//		$modCategoryReason->setHelpId(849);
//		$categoryCorrectDetailsGroup->add($modCategoryReason);

		$attachment = new myAttachment("attachment");
		$attachment->setTempFileLocation("/apps/customerComplaints/tmp");
		$attachment->setFinalFileLocation("/apps/customerComplaints/attachments/conclusion");
		$attachment->setNextAction("conclusion");
		$attachment->setAnchorRef("attachment");
		$attachment->setRowTitle("attached documents");
		$attachment->setHelpId(11);
		$attachmentGroup->add($attachment);
		
		if ($evaluationFields['submitStatus'] == 1)
		{
			$caValue = ($evaluationFields['correctiveAction'] == 1) ? 'Yes' :  'No';
			$vvValue = ($evaluationFields['validationVerification'] == 1) ? 'Yes' :  'No';
			$cjValue = ($evaluationFields['complaintJustified'] == 1) ? 'Yes' :  'No';
			if ($evaluationFields['goodsAction'] == 1)
			{
				$gaValue = "{TRANSLATE:return_goods}";
			}
			else if ($evaluationFields['goodsAction'] == 0)
			{
				$gaValue = "{TRANSLATE:dispose_goods}";
			}
			else 
			{
				$gaValue = "{TRANSLATE:None}";
			}
			
			$rgaValue = ($evaluationFields['returnGoodsConfirmed'] == 1) ? 'Yes' :  'No';
			$dgaValue = ($evaluationFields['disposeGoodsConfirmed'] == 1) ? 'Yes' :  'No';
		}
		else 
		{
			$message = "{TRANSLATE:evaluation_not_submitted}";
			
			$caValue = $message;
			$vvValue = $message;
			$cjValue = $message;
			$gaValue = $message;
			$rgaValue = $message;
			$dgaValue = $message;
		}

		$correctiveAction = new readOnly("correctiveAction");
		$correctiveAction->setGroup("creditGroup");
		$correctiveAction->setRowTitle("corrective_action_complete");
		$correctiveAction->setValue($caValue);
		$correctiveAction->setIgnore(false);
		$correctiveAction->setLabel("Complaint Details");
		$creditGroup->add($correctiveAction);

		$validationVerification = new readOnly("validationVerification");
		$validationVerification->setGroup("creditGroup");
		$validationVerification->setRowTitle("validation_verification_complete");
		$validationVerification->setValue($vvValue);
		$validationVerification->setIgnore(false);
		$creditGroup->add($validationVerification);

		$complaintJustification = new readOnly("complaintJustification");
		$complaintJustification->setGroup("creditGroup");
		$complaintJustification->setRowTitle("complaint_validated");
		$complaintJustification->setValue($cjValue);
		$complaintJustification->setIgnore(false);
		$creditGroup->add($complaintJustification);

		$returnGoods = new readOnly("returnGoods");
		$returnGoods->setGroup("creditGroup");
		$returnGoods->setRowTitle("goods_action_recommended");
		$returnGoods->setValue($gaValue);
		$returnGoods->setIgnore(false);
		$creditGroup->add($returnGoods);

//		if ($evaluationFields['goodsAction'] == 1)
//		{
//			$returnGoodsAuthorised = new readOnly("returnGoodsAuthorised");
//			$returnGoodsAuthorised->setGroup("creditGroup");
//			$returnGoodsAuthorised->setRowTitle("return_goods_authorised");
//			$returnGoodsAuthorised->setValue($rgaValue);
//			$returnGoodsAuthorised->setIgnore(false);
//			$creditGroup->add($returnGoodsAuthorised);
//		}
//		else if ($evaluationFields['goodsAction'] == 0)
//		{
//			$disposeGoodsAuthorised = new readOnly("returnGoodsAuthorised");
//			$disposeGoodsAuthorised->setGroup("creditGroup");
//			$disposeGoodsAuthorised->setRowTitle("dispose_goods_authorised");
//			$disposeGoodsAuthorised->setValue($dgaValue);
//			$disposeGoodsAuthorised->setIgnore(false);
//			$creditGroup->add($disposeGoodsAuthorised);
//		}
	
		$sql = "SELECT complaintValue, complaintCurrency, complaintValueBase, totalInvoicesValue, totalInvoicesValueComplaint, additionalCosts, 
			nonInvoiceCostsComment AS nonInvoiceCosts_comment
			FROM complaint
			WHERE complaint.id = " . $this->complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		$fields = mysql_fetch_array($dataset);
		
		$valueBase = ($fields['complaintValueBase'] == 1) ? '{TRANSLATE:yes}' : '{TRANSLATE:no}';
		$addCosts = ($fields['additionalCosts'] == null) ? 0 : $fields['additionalCosts'];
		
		$complaintValueBase = new readonly("complaintValueBaseRO");
		$complaintValueBase->setGroup("creditGroup");
		$complaintValueBase->setValue($valueBase);
		$complaintValueBase->setDataType("string");
		$complaintValueBase->setLength(20);
		$complaintValueBase->setRowTitle("complaint_value_based_on_invoices");
		$complaintValueBase->setRequired(false);
		$creditGroup->add($complaintValueBase);
		
		if ($fields['complaintValueBase'] == 1)
		{
			$invoicesValue = new readonly("invoicesValueRO");
			$invoicesValue->setGroup("creditGroup");
			$invoicesValue->setValue($fields['totalInvoicesValue'] . ' ' . complaintLib::getOptionText($fields['complaintCurrency']));
			$invoicesValue->setDataType("string");
			$invoicesValue->setLength(20);
			$invoicesValue->setRowTitle("total_invoices_value");
			$invoicesValue->setRequired(false);
			$creditGroup->add($invoicesValue);
			
			$invoicesValue = new readonly("invoicesValueComplaintRO");
			$invoicesValue->setGroup("creditGroup");
			$invoicesValue->setValue($fields['totalInvoicesValueComplaint'] . ' ' . complaintLib::getOptionText($fields['complaintCurrency']));
			$invoicesValue->setDataType("string");
			$invoicesValue->setLength(20);
			$invoicesValue->setRowTitle("invoices_value_complaint");
			$invoicesValue->setRequired(false);
			$creditGroup->add($invoicesValue);
			
			$additionalCosts = new readonly("additionalCostsRO");
			$additionalCosts->setGroup("creditGroup");
			$additionalCosts->setValue($addCosts . ' ' . complaintLib::getOptionText($fields['complaintCurrency']));
			$additionalCosts->setDataType("string");
			$additionalCosts->setLength(20);
			$additionalCosts->setRowTitle("additional_costs");
			$additionalCosts->setRequired(false);
			$creditGroup->add($additionalCosts);				
		}
		else 
		{
			$nonInvoiceCosts_comment = new readonly("nonInvoiceCosts_commentRO");
			$nonInvoiceCosts_comment->setGroup("creditGroup");
			$nonInvoiceCosts_comment->setValue($fields['nonInvoiceCosts_comment']);
			$nonInvoiceCosts_comment->setDataType("text");
			$nonInvoiceCosts_comment->setRowTitle("nonInvoice_costs_comment");
			$nonInvoiceCosts_comment->setRequired(false);
			$creditGroup->add($nonInvoiceCosts_comment);
		}
		
		$creditNoteValue = new readonly("creditNoteValue");
		$creditNoteValue->setGroup("creditGroup");
		$creditNoteValue->setValue($fields['complaintValue'] . ' ' . complaintLib::getOptionText($fields['complaintCurrency']));
		$creditNoteValue->setDataType("string");
		$creditNoteValue->setLength(20);
		$creditNoteValue->setRowTitle("complaint_value");
		$creditNoteValue->setRequired(false);
		$creditGroup->add($creditNoteValue);
		
		
		$sql = "SELECT isCreditOrDebitNote
			FROM conclusion
			WHERE complaintId = " . $this->complaintId;

		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);

		$fieldsConc = mysql_fetch_array($dataset);

		$creditDebit = ($fieldsConc['isCreditOrDebitNote'] == 1) ? 'Credit' : 'Debit';

		$isCreditOrDebitNote = new myRadio("isCreditOrDebitNote");
		$isCreditOrDebitNote->setArraySource(array(
			array('value' => 1, 'display' => 'credit'),
			array('value' => 0, 'display' => 'debit')));
		$isCreditOrDebitNote->setDataType("number");
		$isCreditOrDebitNote->setLength(1);
		$isCreditOrDebitNote->setValue(1);
		$isCreditOrDebitNote->setRequired(false);
		$isCreditOrDebitNote->setTable("conclusion");
		$isCreditOrDebitNote->setHelpId(9104);			
		$isCreditOrDebitNote->setTranslate(true);
		$isCreditOrDebitNote->setGroup("creditGroup");
		$isCreditOrDebitNote->setRowTitle("is_credit_or_debit_note");
		if ($complaintFields['creditNoteRequested'] == 1)
		{
			$isCreditOrDebitNote->setVisible(false);
		}
		$creditGroup->add($isCreditOrDebitNote);
						
		//!$this->awaitingRTGAuthorisation && $evaluationFields['returnGoodsConfirmed'] != -1 
		//	&& $this->awaitingDTGAuthorisation == false && $evaluationFields['disposeGoodsConfirmed'] != -1 

		if ($complaintFields['creditNoteRequested'] == 1 && 
			$this->approval->required() && $evaluationFields['submitStatus'] == 1)
		{	
			if ($this->approval->required())
			{
				// define groups for each approval stage completed
				if ($this->approval->started())
				{
					for ($stage = 1; $stage <= $this->approval->stage(); $stage++)
					{
						if (($stage == $this->approval->stage() && !$this->approval->completed())
							|| ($stage > $this->approval->maxStage()))
						{
							// don't show read-only fields
						}
						else
						{
							${"approvalStage{$stage}Group"} = new group("approvalStage" . $stage . "Group");
							${"approvalStage{$stage}Group"}->setBorder(false);
						}
					}
				}
			
				// define group for the next approval stage if all approval stages haven't been completed
				if (!$this->approval->completed() && !$this->readOnly)
				{
					$newApprovalStageGroup = new group("newApprovalStageGroup");
					$newApprovalStageGroup->setBorder(false);
			
					if ($this->approval->stage() < $this->approval->maxStage())
					{
						$newApprovalStagePersonGroup = new group("newApprovalStagePersonGroup");
						$newApprovalStagePersonGroup->setBorder(false);
					}
			
					$newApprovalStageNotesGroup = new group("newApprovalStageNotesGroup");
					$newApprovalStageNotesGroup->setBorder(false);
				}
			
				for ($stage = 1; $stage <= $this->approval->stage(); $stage++)
				{
					if (($stage == $this->approval->stage() && !$this->approval->completed())
						|| ($stage > $this->approval->maxStage()))
					{
						// don't show read-only fields
					}
					else
					{
						switch ($stage)
						{
							case 0:
								$label = "ask_for_approval";
								break;
							default:
								$label = "{TRANSLATE:approval_stage} " . $stage . "/" . $this->approval->maxStage();
								break;
						}
			
						$sql = "SELECT * FROM approval WHERE complaintId = " . $this->complaintId . "
							AND approvalStage = " . $stage;
			
						$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);
			
						$stageFields = mysql_fetch_array($dataset);
			
						$approved = ($stageFields['approved'] == 1) ? 'Yes' : 'No';
			
						${"approvalStage{$stage}Approved"} = new readonly("approvalStage" . $stage . "Approved");
						${"approvalStage{$stage}Approved"}->setGroup("approvalStage" . $stage . "Group");
						${"approvalStage{$stage}Approved"}->setDataType("text");
						if ($stage != 0)
							${"approvalStage{$stage}Approved"}->setRowTitle("approved");
						else
							${"approvalStage{$stage}Approved"}->setRowTitle("ask_for_approval");
						${"approvalStage{$stage}Approved"}->setLabel($label);
						${"approvalStage{$stage}Approved"}->setVisible(true);
						${"approvalStage{$stage}Approved"}->setValue($approved);
						${"approvalStage{$stage}Group"}->add(${"approvalStage{$stage}Approved"});
						${"approvalStage{$stage}Group"}->add(${"approvalStage{$stage}Approved"});
			
						// asked by & date
						${"approvalStage{$stage}AskedBy"} = new readonly("approvalStage" . $stage . "AskedBy");
						${"approvalStage{$stage}AskedBy"}->setGroup("approvalStage" . $stage . "Group");
						${"approvalStage{$stage}AskedBy"}->setDataType("text");
						${"approvalStage{$stage}AskedBy"}->setRowTitle("initiated_by");
						${"approvalStage{$stage}AskedBy"}->setVisible(true);
						${"approvalStage{$stage}AskedBy"}->setValue(usercache::getInstance()->get($stageFields['askedBy'])->getName() .
							' asked ' . usercache::getInstance()->get($stageFields['assignedTo'])->getName() .
							' for approval (' . myCalendar::dateForUser($stageFields['dateRequested']) . ')');
						${"approvalStage{$stage}Group"}->add(${"approvalStage{$stage}AskedBy"});
			
						// completed by & date
						${"approvalStage{$stage}CompletedBy"} = new readonly("approvalStage" . $stage . "CompletedBy");
						${"approvalStage{$stage}CompletedBy"}->setGroup("approvalStage" . $stage . "Group");
						${"approvalStage{$stage}CompletedBy"}->setDataType("text");
						${"approvalStage{$stage}CompletedBy"}->setRowTitle("approved_by");
						${"approvalStage{$stage}CompletedBy"}->setVisible(true);
						${"approvalStage{$stage}CompletedBy"}->setValue(usercache::getInstance()->get($stageFields['completedBy'])->getName() .
							' (' . myCalendar::dateForUser($stageFields['dateCompleted']) . ')');
						${"approvalStage{$stage}Group"}->add(${"approvalStage{$stage}CompletedBy"});
			
						// goods action approval
						${"approvalGoods{$stage}CompletedBy"} = new readonly("approvalGoods" . $stage);
						${"approvalGoods{$stage}CompletedBy"}->setGroup("approvalStage" . $stage . "Group");
						${"approvalGoods{$stage}CompletedBy"}->setDataType("text");
						${"approvalGoods{$stage}CompletedBy"}->setRowTitle("goods_action_approval");
						${"approvalGoods{$stage}CompletedBy"}->setVisible(true);
						${"approvalGoods{$stage}CompletedBy"}->setValue(($stageFields['goodsActionApproved'] == 1) ? "Approved" : "Rejected");
						${"approvalStage{$stage}Group"}->add(${"approvalGoods{$stage}CompletedBy"});
						
						// approval notes
						${"approvalStage{$stage}Notes"} = new readonly("approvalStage" . $stage . "Notes");
						${"approvalStage{$stage}Notes"}->setGroup("approvalStage" . $stage . "Group");
						${"approvalStage{$stage}Notes"}->setDataType("text");
						${"approvalStage{$stage}Notes"}->setRowTitle("comments");
						${"approvalStage{$stage}Notes"}->setVisible(true);
						${"approvalStage{$stage}Notes"}->setValue($stageFields['notes']);
						${"approvalStage{$stage}Group"}->add(${"approvalStage{$stage}Notes"});
					}
				}
			
				if( !$this->readOnly )
				{
					$approvalRollback = new textboxlink("approvalRollback");
					$approvalRollback->setGroup("newApprovalRollbackGroup");
					$approvalRollback->setDataType("text");
					$approvalRollback->setOpenNewWindow(0);
					$approvalRollback->setRowTitle("rollback_approval");
					$approvalRollback->setVisible(true);
					$approvalRollback->setValue('Rollback');
					$approvalRollback->setLink('javascript:conclusion.rollbackApproval(' . $this->complaintId . ')');
					$approvalRollback->setLabel("rollback_approval");
					$newApprovalRollbackGroup->add($approvalRollback);
				}
							
				if(!$this->approval->completed() && !$this->readOnly)
				{
					$label = "{TRANSLATE:approval_stage} " . $this->approval->stage() . "/" . $this->approval->maxStage();
			
					$appStage = ($this->approval->stage() > 1) ? $this->approval->stage() - 1 : $this->approval->stage();
			
					$sql = "SELECT * FROM approval WHERE complaintId = " . $this->complaintId . "
						AND approvalStage = " . $appStage;
			
					$dataset = mysql::getInstance()->selectDatabase('complaintsCustomer')->Execute($sql);
			
					$approvalFields = mysql_fetch_array($dataset);
			
					if ($this->approval->stage() != 0)
					{						
						// asked by & date
						$newApprovalAskedBy = new readonly("askedBy");
						$newApprovalAskedBy->setGroup("newApprovalStageGroup");
						$newApprovalAskedBy->setDataType("text");
						$newApprovalAskedBy->setRowTitle("initiated_by");
						$newApprovalAskedBy->setVisible(true);
						$newApprovalAskedBy->setLabel($label);
						$newApprovalAskedBy->setValue(
							usercache::getInstance()->get($approvalFields['askedBy'])->getName() . ' (' . myCalendar::dateForUser($approvalFields['dateRequested']) . ')');
						$newApprovalStageGroup->add($newApprovalAskedBy);
					}
			
					$newApprovalYesNo = new radio("newApprovalYesNo");
					$newApprovalYesNo->setLength(20);
					$newApprovalYesNo->setArraySource(
						array(
							array('value' => 1, 'display' => 'yes'),
							array('value' => 0, 'display' => 'no')
						));
					$newApprovalYesNo->setValue(0);
					$newApprovalYesNo->setTranslate(true);
					$newApprovalYesNo->setGroup("newApprovalStageGroup");
					$newApprovalYesNo->setDataType("string");
			
//					if ($this->approval->started())
//					{
						
//					}
//					else

					if (!$this->approval->started())
					{
						$newApprovalYesNo->setRowTitle("ask_for_approval");
						$newApprovalYesNo->setLabel("ask_for_approval");
			
						$newApprovalYesNoDependency2 = new dependency();
						$newApprovalYesNoDependency2->addRule(
							new rule('newApprovalStageGroup', 'newApprovalYesNo', '1'));
						$newApprovalYesNoDependency2->setGroup('newApprovalStagePersonGroup');
						$newApprovalYesNoDependency2->setShow(true);
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency2);
			
						$newApprovalYesNoDependency3 = new dependency();
						$newApprovalYesNoDependency3->addRule(
							new rule('newApprovalStageGroup', 'newApprovalYesNo', '0'));
						$newApprovalYesNoDependency3->setGroup('closureGroup');
						$newApprovalYesNoDependency3->setShow(true);
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency3);						
						
						$newApprovalYesNoDependency4 = new dependency();
						$newApprovalYesNoDependency4->addRule(
								new rule('newApprovalStageGroup', 'newApprovalYesNo', '0'));
						$newApprovalYesNoDependency4->addRule(new rule('closureGroup', 'creditAuthorisation', 1));
						$newApprovalYesNoDependency4->setGroup('creditNoGroup');
						$newApprovalYesNoDependency4->setShow(true);
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency4);
							
						$newApprovalYesNoDependency5 = new dependency();
						$newApprovalYesNoDependency5->addRule(
							new rule('newApprovalStageGroup', 'newApprovalYesNo', '1'));
						$newApprovalYesNoDependency5->setGroup('closureGroup2');
						$newApprovalYesNoDependency5->setShow(false);
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency5);
						
						$newApprovalYesNoDependency6 = new dependency();
						$newApprovalYesNoDependency6->addRule(
							new rule('newApprovalStageGroup', 'newApprovalYesNo', '1'));
						$newApprovalYesNoDependency6->setGroup('newApprovalStageNotesGroup');
						$newApprovalYesNoDependency6->setShow(true);
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency6);
					}
					else
					{
						$newApprovalYesNo->setRowTitle("approved");
					}
							
			
					if ($this->approval->stage() < $this->approval->maxStage())
					{
//						$approvalOwnerMessage = new readonly("approvalOwnerMessage");
//						$approvalOwnerMessage->setGroup("newApprovalStagePersonGroup");
//						$approvalOwnerMessage->setRowTitle("approval_note");
//						$approvalOwnerMessage->setValue("");
//						$approvalOwnerMessage->setIgnore(false);
//						$approvalOwnerMessage->setVisible(true);
//						$newApprovalStagePersonGroup->add($approvalOwnerMessage);
//						
//						$approvalOwnerLink = new textboxlink("approvalOwnerLink");
//						$approvalOwnerLink->setGroup("newApprovalStagePersonGroup");
//						$approvalOwnerLink->setRowTitle("approval_matrix");
//						$approvalOwnerLink->setHelpId(1118);
//						$approvalOwnerLink->setLink("http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Global%20credit%20authorisation%20matrix.xlsx");
//						$approvalOwnerLink->setValue("{TRANSLATE:approval_matrix}");
//						$newApprovalStagePersonGroup->add($approvalOwnerLink);
						
						$newApprovalPerson = new dropdown("newApprovalPerson");
						$newApprovalPerson->setGroup("newApprovalStagePersonGroup");
						$newApprovalPerson->setDataType("string");
						if($this->approval->stage() == 0)
						{
							$newApprovalPerson->setRowTitle("to_be_approved_by");
						}
						else
						{
							$newApprovalPerson->setRowTitle("next_to_be_approved_by");
						}
						$newApprovalPerson->setRequired(true);
						$newApprovalPerson->setVisible(true);
						$newApprovalPerson->setErrorMessage("field_error");
						$newApprovalPerson->setArraySource($this->approval->authorisers($this->approval->stage() + 1));
						$newApprovalStagePersonGroup->add($newApprovalPerson);
																		
//						$newApprovalNotes2 = new myTextarea("newApprovalNotes2");
//						$newApprovalNotes2->setGroup("newApprovalStagePersonGroup");
//						$newApprovalNotes2->setDataType("text");
//						$newApprovalNotes2->setRowTitle("comments");
//						$newApprovalNotes2->setRequired(false);
//						$newApprovalNotes2->setIgnore(true);
//						$newApprovalNotes2->setVisible(true);
//						$newApprovalStagePersonGroup->add($newApprovalNotes2);

						$newApprovalYesNoDependency = new dependency();
						$newApprovalYesNoDependency->addRule(
							new rule('newApprovalStageGroup', 'newApprovalYesNo', '1'));
						$newApprovalYesNoDependency->setGroup('newApprovalStagePersonGroup');
						$newApprovalYesNoDependency->setShow(true);
		
						$newApprovalYesNo->addControllingDependency($newApprovalYesNoDependency);
					}
		
					$newApprovalNotes = new myTextarea("newApprovalNotes");
					$newApprovalNotes->setGroup("newApprovalStageNotesGroup");
					$newApprovalNotes->setDataType("text");
					$newApprovalNotes->setRowTitle("comments");
					$newApprovalNotes->setRequired(false);
					$newApprovalNotes->setIgnore(true);
					$newApprovalNotes->setVisible(true);
					$newApprovalStageNotesGroup->add($newApprovalNotes);
					
					$newApprovalYesNo->setRequired(true);
					$newApprovalYesNo->setVisible(true);
					$newApprovalStageGroup->add($newApprovalYesNo);
					
                    if ($this->approval->stage() != 0)
					{
                        // show goods action
                        $returnGoodsRecommendation = new readOnly("returnGoods");
                        $returnGoodsRecommendation->setGroup("newApprovalStageGroup");
                        $returnGoodsRecommendation->setRowTitle("goods_action_recommended");
                        $returnGoodsRecommendation->setValue($gaValue);
                        $returnGoodsRecommendation->setIgnore(false);
                        $newApprovalStageGroup->add($returnGoodsRecommendation);

                        $goodsActionYesNo = new radio("goodsActionYesNo");
                        $goodsActionYesNo->setArraySource(
                            array(
                                array('value' => 1, 'display' => 'yes'),
                                array('value' => 0, 'display' => 'no')
                            ));
                        $goodsActionYesNo->setRowTitle("goods_action_approved");
                        $goodsActionYesNo->setTranslate(true);
                        $goodsActionYesNo->setGroup("newApprovalStageGroup");
                        $goodsActionYesNo->setDataType("string");
                        $goodsActionYesNo->setRequired(true);
                        $goodsActionYesNo->setVisible(true);
                        $newApprovalStageGroup->add($goodsActionYesNo);
                    }
				}
			}
		}	
			
		// Updated by JM/RM 21/02/2012 - Hide the credit authorisation complete fields if awaiting goods returned/disposed
		//if (!$this->approval->started() || $this->approval->completed())
		if ((!$this->approval->started() || $this->approval->completed()))// && (!$this->awaitingRTGAuthorisation && !$this->awaitingDTGAuthorisation))
		{
			$creditAuthorisationComplete = new radio("creditAuthorisation");
			$creditAuthorisationComplete->setGroup("closureGroup");
			$creditAuthorisationComplete->setDataType("number");
			$creditAuthorisationComplete->setLabel('credit_closure');
			$creditAuthorisationComplete->setLength(1);
			$creditAuthorisationComplete->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
			$creditAuthorisationComplete->setRowTitle("credit_authorisation_complete");
			$creditAuthorisationComplete->setRequired(false);
			$creditAuthorisationComplete->setTranslate(true);
			$creditAuthorisationComplete->setTable("conclusion");
			$creditAuthorisationComplete->setValue(0);
			$creditAuthorisationComplete->setHelpId(9154);
	
				// Dependency
				$creditAuthorisationStatusDependency = new dependency();
				$creditAuthorisationStatusDependency->addRule(new rule('closureGroup', 'creditAuthorisation', 1));
				$creditAuthorisationStatusDependency->setGroup('creditNoGroup');
				$creditAuthorisationStatusDependency->setShow(true);
	
			$creditAuthorisationComplete->addControllingDependency($creditAuthorisationStatusDependency);
			$closureGroup->add($creditAuthorisationComplete);
						
			$creditNo = new textbox("creditNo");
			$creditNo->setGroup("creditNoGroup");
			$creditNo->setDataType("string");
			$creditNo->setLength(20);
			$creditNo->setRowTitle("customer_credit_number");
			$creditNo->setRequired(true);
			$creditNo->setTable("conclusion");
			$creditNo->setHelpId(8024);
			
			$dateCreditNoteRaised = new myCalendar("dateCreditNoteRaised");
			$dateCreditNoteRaised->setGroup("creditNoGroup");
			$dateCreditNoteRaised->setErrorMessage("textbox_date_error");
			$dateCreditNoteRaised->setRowTitle("date_credit_note_raised");
			$dateCreditNoteRaised->setRequired(false);
			$dateCreditNoteRaised->setNullable(true);
			$dateCreditNoteRaised->setTable("conclusion");
			$dateCreditNoteRaised->setHelpId(9103);
			
			if (!$this->isFullyApproved())
			{
				$creditNo->setVisible(false);
				$dateCreditNoteRaised->setVisible(false);
			}
			
			$creditNoGroup->add($creditNo);	
			$creditNoGroup->add($dateCreditNoteRaised);
	
			$finalComments = new textarea("finalComments");
			$finalComments->setGroup("closureGroup2");
			$finalComments->setDataType("text");
			$finalComments->setRowTitle("comments");
			$finalComments->setRequired(false);
			$finalComments->setTable("conclusion");
			$finalComments->setHelpId(9106);
			$closureGroup2->add($finalComments);
			//$finalCommentsGroup->add($finalComments);
			
			if (!$this->readOnly)
			{
				$owner = new myAutocomplete("complaintOwner");
				$owner->setGroup("closureGroup2");
				$owner->setDataType("string");
				$owner->setLength(25);
				$owner->setIsAnNTLogon(true);
				$owner->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
				$owner->setRowTitle("new_complaint_owner");
				$owner->setRequired(false);
				$owner->setErrorMessage("select_valid_employee");
				$owner->setValidateQuery("membership", "employee", "NTLogon");
				$owner->setHelpId(1119);
				$closureGroup2->add($owner);				
			}
		}			
		
		if ($this->returnedGoods)
		{			
			$returnsReceived = new myRadio("returnsReceived");
			$returnsReceived->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')));
			$returnsReceived->setDataType("number");
			$returnsReceived->setLength(1);
			$returnsReceived->setRequired(true);
			$returnsReceived->setTable("conclusion");		
			$returnsReceived->setTranslate(true);
			$returnsReceived->setLabel("returned_goods");
			$returnsReceived->setGroup("rtgGroup");
			$returnsReceived->setRowTitle("have_goods_been_received");
				
//			if ($this->approval->started())
//			{
//				$returnsReceived->setReadOnly(true);
//			}
			
				$returnsReceivedDependency1 = new dependency();
				$returnsReceivedDependency1->addRule(
					new rule('rtgGroup', 'returnsReceived', '1'));
				$returnsReceivedDependency1->setGroup('rtgYesGroup');
				$returnsReceivedDependency1->setShow(true);
				
				if (!$this->awaitingRTGAuthorisation && $evaluationFields['returnGoodsConfirmed'] != -1 && !$this->approval->completed() && 
					!$this->readOnly && $complaintFields['creditNoteRequested'] == 1 && $this->approval->required() && 
					$evaluationFields['submitStatus'] == 1)
				{
					$returnsReceivedDependency2 = new dependency();
					$returnsReceivedDependency2->addRule(
						new rule('rtgGroup', 'returnsReceived', '1'));
					$returnsReceivedDependency2->setGroup('newApprovalStageGroup');
					$returnsReceivedDependency2->setShow(true);
					
					$returnsReceived->addControllingDependency($returnsReceivedDependency2);
				}
				
				$returnsReceivedDependency3 = new dependency();
				$returnsReceivedDependency3->addRule(
					new rule('rtgGroup', 'returnsReceived', '1'));
				$returnsReceivedDependency3->setGroup('closureGroup');
				$returnsReceivedDependency3->setShow(true);
				
				$returnsReceivedDependency4 = new dependency();
				$returnsReceivedDependency4->addRule(
					new rule('rtgGroup', 'returnsReceived', '1'));
				$returnsReceivedDependency4->setGroup('closureGroup2');
				$returnsReceivedDependency4->setShow(true);
				
			$returnsReceived->addControllingDependency($returnsReceivedDependency1);
			$returnsReceived->addControllingDependency($returnsReceivedDependency3);
			$returnsReceived->addControllingDependency($returnsReceivedDependency4);
			$rtgGroup->add($returnsReceived);
				
				$dateReturnsReceived = new myCalendar("dateReturnsReceived");
				$dateReturnsReceived->setGroup("rtgYesGroup");
				$dateReturnsReceived->setErrorMessage("textbox_date_error");
				$dateReturnsReceived->setRowTitle("date_returns_received");
				$dateReturnsReceived->setRequired(true);
				$dateReturnsReceived->setNullable(true);
				$dateReturnsReceived->setTable("conclusionReturnedGoods");
				$rtgYesGroup->add($dateReturnsReceived);
		
				$receiver = new autocomplete("receiver");
				$receiver->setGroup("rtgYesGroup");
				$receiver->setDataType("string");
				$receiver->setLength(25);
				$receiver->setIsAnNTLogon(true);
				$receiver->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
				$receiver->setRowTitle("receiver");
				$receiver->setRequired(true);
				$receiver->setErrorMessage("select_valid_employee");
				$receiver->setValidateQuery("membership", "employee", "NTLogon");
				$receiver->setTable("conclusionReturnedGoods");
				$rtgYesGroup->add($receiver);
							
				$returnQuantityReceived = new myMeasurement("returnQuantityReceived");
				$returnQuantityReceived->setGroup("rtgYesGroup");
				$returnQuantityReceived->setErrorMessage("field_error");
				$returnQuantityReceived->setSQLSource("complaintsCustomer",
					"SELECT selectionOption AS name, id AS value 
					FROM selectionOptions
					WHERE typeId = " . complaintLib::getOptionTypeId('measurement') . "
					ORDER BY selectionOption ASC");
				$returnQuantityReceived->setRowTitle("return_quantity_received");
				$returnQuantityReceived->setRequired(true);								
				$returnQuantityReceived->setNullable();
				$returnQuantityReceived->setMeasurementError("select_measurement_unit");
				$returnQuantityReceived->setTable("conclusionReturnedGoods");
				$rtgYesGroup->add($returnQuantityReceived);
		
				$lotNumber = new textbox("lotNumber");
				$lotNumber->setGroup("rtgYesGroup");
				$lotNumber->setDataType("string");
				$lotNumber->setLength(255);
				$lotNumber->setRowTitle("lot_number");
				$lotNumber->setRequired(false);
				$lotNumber->setTable("conclusionReturnedGoods");
				$rtgYesGroup->add($lotNumber);
				
				$condition = new textarea("condition");
				$condition->setGroup("rtgYesGroup");
				$condition->setDataType("text");
				$condition->setRowTitle("condition");
				$condition->setRequired(false);
				$condition->setTable("conclusionReturnedGoods");
				$rtgYesGroup->add($condition);
		}
			

		$submit = new submit("submit");
		$submit->setGroup("submitGroup");
		$submit->setValue('Submit');
		$submit->setVisible(true);
		$submitGroup->add($submit);

		// Add all groups to the form
		$this->form->add($sapReturnNoGroup);
		$this->form->add($group1);
		$this->form->add($categoryCorrectGroup);
		$this->form->add($categoryCorrectDetailsGroup);
		$this->form->add($attachmentGroup);
		$this->form->add($group2);
		$this->form->add($creditGroup);
		
			

	//!$this->awaitingRTGAuthorisation && $evaluationFields['returnGoodsConfirmed'] != -1 
	//		&& $this->awaitingDTGAuthorisation == false && $evaluationFields['disposeGoodsConfirmed'] != -1 && 
		if ($complaintFields['creditNoteRequested'] == 1 && $this->approval->required() && $evaluationFields['submitStatus'] == 1)
		{
			if ($this->approval->stage() > 0 && !$this->readOnly)
			{
				$this->form->add($newApprovalRollbackGroup);
			}
			
			if ($this->approval->stage() > 1 || $this->approval->completed())
			{
				for ($stage = 1; $stage <= $this->approval->stage(); $stage++)
				{
					if (($stage == $this->approval->stage() && !$this->approval->completed())
						|| ($stage > $this->approval->maxStage()))
					{
						// don't show read-only fields
					}
					else
					{
						$this->form->add(${"approvalStage{$stage}Group"});
					}
				}
			}
		
			if (!$this->approval->completed() && !$this->readOnly)
			{
				$this->form->add($newApprovalStageGroup);
				
				
				if ($this->approval->stage() < $this->approval->maxStage())
				{
					$this->form->add($newApprovalStagePersonGroup);
				}
				
				$this->form->add($newApprovalStageNotesGroup);
			}
		}
	
        if ($this->returnedGoods)
		{
			$this->form->add($rtgGroup);
			$this->form->add($rtgYesGroup);
		}
        
		$this->form->add($closureGroup);
		$this->form->add($creditNoGroup);
		$this->form->add($closureGroup2);
                
		$this->form->add($finalCommentsGroup);
				
		$this->form->add($submitGroup);
	}
		
	
	/**
	 * Removes all approval stages
	 */
	private function approvalRollback()
	{
		// Add approval rollback to the log
		$this->complaintLib->addLog($this->complaintId, 'conclusion_approval_rollback');
		
		//$this->trackCreditApprovalRollback();
		
		$this->approval->rollback();
		
		// Redirect to clear the approval stage in the GET
		page::redirect("/apps/customerComplaints/edit?complaintId=" . $this->complaintId . "&stage=conclusion");
	}
			
	
//	public function trackCreditApprovalRollback()
//	{
//		$changedFields = array();
//		$oldValues = array();
//		$newValues = array();
//		
//		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
//			SELECT id 
//			FROM log
//			WHERE complaintId = " . $this->complaintId . "
//			AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'
//			AND actionDescription = 'conclusion_approval_rollback'
//			ORDER BY dateTime DESC LIMIT 0,1		
//			");
//					
//		$fields = mysql_fetch_assoc($dataset);
//		
//		$logId = $fields['id'];
//		
//		
//		
//		
//		array_push( $changedFields, $this->fields[$i]['translate'] );
//		array_push( $oldValues, $this->fields[$i]['oldValue'] );
//		array_push( $newValues, NULL );
//						
//		$dbFields = implode( "||" , $changedFields );
//		$dbOld = implode( "||" , $oldValues );
//		$dbNew = implode( "||" , $newValues );
//		
//		$sql = "INSERT INTO changes 
//			(complaintId , logId , fields , oldValues , newValues) 
//			VALUES 
//			($this->complaintId , $logId , '$dbFields' , '$dbOld' , '$dbNew')";
//			
//		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $sql );
//	}
	
	
	/**
	 * Updates the owner of the complaint, and also the category ID if passed
	 *
	 * @param string $NTLogon
	 */
	public function updateComplaintOwnerAndCategory($NTLogon, $categoryId = '')
	{
		$extraSQL = ($categoryId != '') ? ", categoryId = " . $categoryId : '';
		
		$sql = "UPDATE complaint
			SET complaintOwner = '" . $NTLogon . "'" .
			$extraSQL . " 
			WHERE id = " . $this->complaintId;
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
	}
	
	
	private function isFullyApproved()
	{	
		if ($this->approval->stage() > $this->approval->maxStage())
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	
	private function loadReturnNos()
	{		
		$sql = "SELECT distinct(sapReturnNo) 
			FROM conclusionReturnNo 
			WHERE complaintId = " . $this->complaintId;
		
		$this->form->getGroup('sapReturnNoGroup')->load(
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql)
			);
	}
	
	
	/**
	 * Notes the date that the credit authoriation was submitted, and who submitted it
	 */
	private function setCompletionDatesAndPeople()
	{
		$keyFields = array('creditAuthorisation');
		
		foreach ($keyFields as $field)
		{
			$fieldDate = $field . 'Date';
				
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT " . $fieldDate . "
				FROM conclusion
				WHERE complaintId = " . $this->complaintId);
			
			$datasetFields = mysql_fetch_assoc($dataset);				
				
			if ($this->form->get($field) && $this->form->get($field)->getValue() == 1)
			{					
				if ($datasetFields[$fieldDate] == null)
				{						
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
						UPDATE conclusion
						SET " . $fieldDate . " = '" . date('Y-m-d') . "', " .
						$field . "Person = '" . currentuser::getInstance()->getNTLogon() . "' 
						WHERE complaintId = " . $this->complaintId);
				}
			}
			else if ($this->form->get($field) && $this->form->get($field)->getValue() == 0 && $datasetFields[$fieldDate] != null)
			{
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					UPDATE conclusion
					SET " . $fieldDate . " = null, " . 
					$field . "Person = null 
					WHERE complaintId = " . $this->complaintId);
			}
		}
	}
	
	
	private function saveReturnNos()
	{
		// Remove previously submitted list of return numbers
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			DELETE FROM conclusionReturnNo WHERE complaintId = " . $this->complaintId);
		
		// Insert new updated list of return numbers
		for ($row = 0; $row < $this->form->getGroup("sapReturnNoGroup")->getRowCount(); $row++)
		{
			$returnNo = $this->form->getGroup("sapReturnNoGroup")->get($row,"sapReturnNo")->getValue();

			if ($returnNo != '')
			{
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
					INSERT INTO conclusionReturnNo
					(complaintId, sapReturnNo) 
					VALUES (" . $this->complaintId . ", '" . $returnNo . "')");
			}
		}
	}
	
}

?>