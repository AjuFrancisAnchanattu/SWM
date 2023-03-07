<?php

include_once('complaintLib.php');

/**
 * Customer Complaint Form
 * 
 * @package apps	
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 24/11/2010
 */
class customerComplaint
{
	private $complaintId;
	private $loadFromSession = false;
	private $readOnly = false;
	private $isValid = true;
	
	public $form;
	
	
	function __construct($complaintId, $loadFromSession = false, $readOnly = false)
	{
		$this->complaintLib = new complaintLib();
		
		$this->complaintId = $complaintId;
		$this->loadFromSession = $loadFromSession;
		$this->readOnly = $readOnly;
		
		$this->approval = new approval( $this->complaintId );
		
		$this->defineForm();
		$this->load();
		$this->setupFields();
	}
	
	public function showForm()
	{
		return $this->form->output();
	}
	
	public function showFormReadOnly()
	{
		return $this->form->readOnlyOutput();
	}
	
	private function load()
	{
		page::addDebug("Loading Complaint ID = " . $this->complaintId, __FILE__, __LINE__);
		
		if (!$this->loadFromSession)
		{
			if( $this->complaintId > 0)
			{	
				$this->loadData_DB();
			}
			
			$this->loadInvoices();
			$this->loadGroupedComplaint();
		}
		else
		{
			$this->form->loadSessionData();
			$this->form->processPost();
		}
		
		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
	}

	public function validate()
	{
		// If the form is set to be saved and is not already submitted, we bypass validation
		if (!$this->complaintLib->isSubmitted($this->complaintId, 'complaint'))
		{
			if ($this->form->get("submitStatus")->getValue() == 0)
			{
				$this->isValid = $this->form->validateValuesOnly();
				return $this->isValid;
			}
		}
		
		$this->isValid = $this->form->validate();
		
		// Check an s-category is selected if the complaint is not valid and credit has been requested
		if ($this->form->get("creditNoteRequested")->getValue() == 1)
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT complaintJustified 
				FROM evaluation 
				WHERE complaintId = " . $this->complaintId);
			
			if (mysql_num_rows($dataset) > 0)
			{
				$fields = mysql_fetch_array($dataset);
				
				if ($fields['complaintJustified'] == 0)
				{
					$categoryId = $this->form->get("categoryId")->getValue();
				
					$categoryText = complaintLib::getOptionText($categoryId);
					
					$categoryLetter = strtolower(substr($categoryText, 0, 1));
					
					if ($categoryLetter != 's')
					{
						$this->form->get("categoryId")->setErrorMessage("select_s_category");
						$this->form->get("categoryId")->setValid(false);
						$this->isValid = false;
					}
				}
			}
		}

		if( $this->form->get("complaintValueBase")->getValue() == 1 )
		{
			$sapCustomerNo = $this->form->get("sapCustomerNo")->getValue();
			
			if (!is_numeric($sapCustomerNo))
			{
				$this->form->get("sapCustomerNo")->setValid(false);
				$this->isValid = false;
				
				return $this->isValid;
			}
			
			if( $sapCustomerNo != '' )
			{
				for($row=0 ; $row < $this->form->getGroup("sapInvoiceNoGroup")->getRowCount() ; $row++)
				{
					$invoiceNo = $this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->getValue();
					
					if (!is_numeric($invoiceNo))
					{
						$this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->setValid(false);
						$this->isValid = false;
						
						return $this->isValid;
					}
						
					if ( $invoiceNo != '')
					{
						$sql = "SELECT * 
								FROM invoices 
								WHERE 
									invoiceNo = $invoiceNo 
								AND
									stp = $sapCustomerNo";
							
						$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
						
						if( mysql_num_rows( $dataset ) == 0 )
						{
							$this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->setErrorMessage("invoice_not_for_customer");
							$this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->setValid(false);
							$this->isValid = false;
						}
					}
				}
				
				if( $this->form->getGroup("sapInvoiceNoGroup")->getRowCount() > 1 )
				{
					for($i=1; $i<$this->form->getGroup("sapInvoiceNoGroup")->getRowCount(); $i++)
					{
						for($j=0; $j<$i; $j++)
						{
							$invoiceNoI = $this->form->getGroup("sapInvoiceNoGroup")->get($i,"invoiceNo")->getValue();
							$invoiceNoJ = $this->form->getGroup("sapInvoiceNoGroup")->get($j,"invoiceNo")->getValue();
							
							if( $invoiceNoI == $invoiceNoJ )
							{
								$this->form->getGroup("sapInvoiceNoGroup")->get($i,"invoiceNo")->setErrorMessage("duplicate_invoice");
								$this->form->getGroup("sapInvoiceNoGroup")->get($i,"invoiceNo")->setValid(false);
								$this->form->getGroup("sapInvoiceNoGroup")->get($j,"invoiceNo")->setErrorMessage("duplicate_invoice");
								$this->form->getGroup("sapInvoiceNoGroup")->get($j,"invoiceNo")->setValid(false);
								$this->isValid = false;
							}
						}
					}
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
	
	
	private function setOriginalCategory()
	{
		$sql = "UPDATE complaint
			SET originalCategoryId = categoryId 
			WHERE id = " . $this->complaintId;
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);		
	}
	
			
	public function save()
	{
		$action = $this->determineAction();
		
		if ($this->form->get("submitBy")->getValue() == '')
		{
			$this->form->get("submitBy")->setValue(currentuser::getInstance()->getNTLogon());
		}
		
		$valueGBP = complaintLib::convertToGBP( 
			$this->form->get("complaintValue")->getValue(),
			$this->form->get("complaintCurrency")->getValue());
		
		//var_dump( $this->form->get("complaintValue")->getValue() );
		//var_dump( $this->form->get("complaintCurrency")->getValue() );
		//var_dump( $valueGBP );
		//die();
		
		$this->form->get("complaintValueGBP")->setValue($valueGBP);
		
		switch( $action )
		{
			case "INSERT_SUBMIT":
				$sql = "INSERT INTO complaint " . $this->form->generateInsertQuery("complaint");
				
				$logAction = 'complaint_created_submitted';
				$emailAction = 'complaint_created';
				$sendToUser = true;
				
				$updateOwner = true;
				$complaintOwner = $this->form->get("submitBy")->getValue();
				
				$trackChanges = false;
				$setSubmissionValues = true;
				break;
				
			case "INSERT_SAVE":
				$this->form->get("evaluationOwner")->setIgnore(true);
				$sql = "INSERT INTO complaint " . $this->form->generateInsertQuery("complaint");
				
				$logAction = 'complaint_created_saved';
				$sendToUser = false;
				
				$updateOwner = true;
				$complaintOwner = currentuser::getInstance()->getNTLogon();
				
				$trackChanges = false;
				break;
				
			case "UPDATE_SUBMIT":
				$sql = "UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id= " . $this->complaintId;
				
				$logAction = 'complaint_updated_submitted';
				$emailAction = 'complaint_created';
				$sendToUser = true;
				
				$updateOwner = true;
				$complaintOwner = $this->form->get("submitBy")->getValue();
				
				$trackChanges = false;
				$setSubmissionValues = true;
				break;
				
			case "UPDATE_SAVE":
				$this->form->get("evaluationOwner")->setIgnore(true);
				$sql = "UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id= " . $this->complaintId;
				
				$logAction = 'complaint_updated_saved';
				$sendToUser = false;
				
				$updateOwner = true;
				$complaintOwner = currentuser::getInstance()->getNTLogon();
				
				$trackChanges = false;
				break;
				
			case "UPDATE_EDIT":
				$sql = "UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id= " . $this->complaintId;
				
				$logAction = "complaint_updated";
				$sendToUser = false;
				
				$updateOwner = false;
				
				$trackChanges = true;
				break;
				
			default:
				break;
		}
		
		page::addDebug("Saving Complaint",__FILE__,__LINE__);
		
		if( $trackChanges )
		{
			$this->complaintLib->startRecordingChanges( $this->complaintId );
		}
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( "BEGIN" );
		
		if( !$this->approval->started() )
		{
			$this->saveInvoices();
		}
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( $sql );
		
		if( $this->complaintId < 0 )
		{
			$oldId = $this->complaintId;
			$this->complaintId = $this->getNewId();
			$this->updateInvoices($oldId, $this->complaintId);
		}
		
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute( "COMMIT" );
		
		if( $this->form->get("groupComplaint")->getValue() == 1 )
		{
			$this->saveGroupedComplaint();
		}
		
		if( $updateOwner )
		{
			$this->complaintLib->setComplaintOwner($this->complaintId, $complaintOwner, 'complaint');
		}
		
		if( $sendToUser )
		{
			$complaintOwner = $this->form->get("submitBy")->getValue();
			$evaluationOwner = $this->form->get("evaluationOwner")->getValue();
			
			// Send e-mail notification to the complaint owner
			myEmail::send(
				$this->complaintId, 
				$emailAction, 
				$complaintOwner, 
				currentuser::getInstance()->getNTLogon(), 
				$this->form->get("emailText")->getValue()
			);
			
			// Send e-mail notification to the evaluation owner
			if ($complaintOwner != $evaluationOwner)
			{
				myEmail::send(
					$this->complaintId, 
					$emailAction, 
					$evaluationOwner, 
					currentuser::getInstance()->getNTLogon(), 
					$this->form->get("emailText")->getValue()
				);
			}
			
			// Send e-mail notification to the current user if he is not an owner
			if ($complaintOwner != currentuser::getInstance()->getNTLogon() && $evaluationOwner != currentuser::getInstance()->getNTLogon())
			{
				myEmail::send(
					$this->complaintId, 
					$emailAction, 
					currentuser::getInstance()->getNTLogon(), 
					currentuser::getInstance()->getNTLogon(), 
					$this->form->get("emailText")->getValue()
				);
			}
			
			if ($complaintOwner == $evaluationOwner)
			{
				$logDescription = "{TRANSLATE:sent_to} " . 
					usercache::getInstance()->get($complaintOwner)->getName() . " ({TRANSLATE:complaint_evaluation_conclusion_owner})";
			}
			else 
			{
				$logDescription = "{TRANSLATE:sent_to} " . 
					usercache::getInstance()->get($complaintOwner)->getName() . " ({TRANSLATE:complaint_conclusion_owner}) &amp; " . 
					usercache::getInstance()->get($evaluationOwner)->getName() . " ({TRANSLATE:evaluation_owner})";
			}
			
			$this->complaintLib->addLog(
				$this->complaintId, 
				$logAction, 
				$logDescription
			);
		}
		else
		{
			$logId = $this->complaintLib->addLog(
				$this->complaintId, 
				$logAction
			);
			
			
			if( $trackChanges )
			{
				$this->complaintLib->stopRecordingChanges($logId);
			}
			
		}
		
		if (isset($setSubmissionValues))
		{
			$this->complaintLib->setSubmissionValues($this->complaintId, 'complaint');			
			$this->setOriginalCategory();
		}
		
		// Update Attachment			
		$this->form->get("attachment")->setFinalFileLocation("/apps/customerComplaints/attachments/complaint/" . $this->complaintId . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
		
		if(isset($oldId))
		{
			//here remove old form from session
			unset($_SESSION['apps'][$GLOBALS['app']]['customerComplaint_' . $oldId . '_' . currentuser::getInstance()->getNTLogon()]);
		}
		else
		{
			// remove this form from session and go to summary page
			unset($_SESSION['apps'][$GLOBALS['app']]['customerComplaint_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon()]);
		}
		
		page::redirect("index?complaintId=" . $this->complaintId);
	}
	

	

	/**
	 * Define the actual form
	 */
	public function defineForm()
	{
		$this->form = new myForm("customerComplaint_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$submitOnBehalfGroup = new group("submitOnBehalfGroup");
		$submitOnBehalfGroup->setBorder(false);
		
		$submitByGroup = new group("submitByGroup");
		$submitByGroup->setBorder(false);
		
		$groupedComplaintGroup = new group("groupedComplaintGroup");
		$groupedComplaintGroup->setBorder(false);
		
		$groupedComplaintIdGroup = new multiplegroup("groupedComplaintIdGroup");
		$groupedComplaintIdGroup->setAnchorRef("complaintIdGroup");
		$groupedComplaintIdGroup->setTitle("grouped_with_complaint");
		$groupedComplaintIdGroup->setBorder(false);
		
		$group1 = new group("group1");
		$group1->setBorder(false);
		
		$group3 = new group("group3");
		$group3->setBorder(false);
		
		$factoredProductGroup = new group("factoredProductGroup");
		$factoredProductGroup->setBorder(false);
		
		$factoredProductSupplierGroup = new group("factoredProductSupplierGroup");
		$factoredProductSupplierGroup->setBorder(false);
			
		$group2 = new group("group2");
		$group2->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		$submitOnBehalf = new myRadio("submitOnBehalf");
		$submitOnBehalf->setGroup("submitOnBehalfGroup");
		$submitOnBehalf->setDataType("boolean");
		$submitOnBehalf->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$submitOnBehalf->setRowTitle("submit_on_behalf_of_someone");
		$submitOnBehalf->setRequired(true);
		$submitOnBehalf->setTranslate(true);
		$submitOnBehalf->setVisible(true);
		$submitOnBehalf->setHelpId(8000);
		$submitOnBehalf->setValue(0);
		$submitOnBehalf->setTable("complaint");
					
		// Dependency
		$behalfOfDependency = new dependency();
		$behalfOfDependency->addRule(new rule('submitOnBehalfGroup', 'submitOnBehalf', 1));
		$behalfOfDependency->setGroup('submitByGroup');
		$behalfOfDependency->setShow(true);

		$submitOnBehalf->addControllingDependency($behalfOfDependency);
		$submitOnBehalfGroup->add($submitOnBehalf);
				
		$submitBy = new myAutocomplete("submitBy");
		$submitBy->setGroup("submitByGroup");
		$submitBy->setDataType("string");
		$submitBy->setLength(25);
		$submitBy->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$submitBy->setRowTitle("employee");
		$submitBy->setRequired(true);
		$submitBy->setIgnore(false);
		$submitBy->setIsAnNTLogon(true);
		$submitBy->setTable("complaint");
		$submitBy->setValidateQuery("membership", "employee", "NTLogon");
		$submitBy->setHelpId(828);
		$submitBy->setErrorMessage("select_valid_employee");
		$submitByGroup->add($submitBy);
			
		$groupComplaint = new radio("groupComplaint");
		$groupComplaint->setGroup("groupedComplaintGroup");
		$groupComplaint->setDataType("boolean");
		$groupComplaint->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$groupComplaint->setRowTitle("grouped_with_another_complaint");
		$groupComplaint->setRequired(true);
		$groupComplaint->setVisible(true);
		$groupComplaint->setTranslate(true);
		$groupComplaint->setValue(0);
		$groupComplaint->setTable("complaint");
		$groupComplaint->setHelpId(8188);
					
		// Dependency
		$groupComplaintDependency = new dependency();
		$groupComplaintDependency->addRule(new rule('groupedComplaintGroup', 'groupComplaint', 1));
		$groupComplaintDependency->setGroup('groupedComplaintIdGroup');
		$groupComplaintDependency->setShow(true);

		$groupComplaint->addControllingDependency($groupComplaintDependency);
		$groupedComplaintGroup->add($groupComplaint);
				
		$groupComplaintId = new myAutocomplete("groupComplaintId");
		$groupComplaintId->setGroup("groupedComplaintIdGroup");
		$groupComplaintId->setDataType("string");
		$groupComplaintId->setLength(25);
		$groupComplaintId->setUrl("/apps/customerComplaints/ajax/complaintIdNewOnly?");
		$groupComplaintId->setRowTitle("grouped_complaint_id");
		$groupComplaintId->setRequired(true);
		$groupComplaintId->setErrorMessage("enter_complaint_id");
		$groupComplaintId->setHelpId(8159);
		$groupComplaintId->setValidateQuery( "complaintsCustomer", "complaint", "id", "id NOT LIKE '" . $this->complaintId . "'");
		$groupedComplaintIdGroup->add($groupComplaintId);	
			
		$complaintRef = new myTextbox("complaintRef");
		$complaintRef->setGroup("group1");
		$complaintRef->setDataType("string");
		$complaintRef->setLength(50);
		$complaintRef->setRowTitle("customer_complaint_ref");
		$complaintRef->setLabel("complaint_details");
		$complaintRef->setRequired(false);
		$complaintRef->setNullable();
		$complaintRef->setTable("complaint");
		$complaintRef->setHelpId(8002);
		$group1->add($complaintRef);	
		
		$complaintDate = new myCalendar("complaintDate");
		$complaintDate->setGroup("group1");
		$complaintDate->setDataType("date");
		$complaintDate->setLength(11);
		$complaintDate->setRowTitle("customer_complaint_date");
		$complaintDate->setRequired(true);
		$complaintDate->setErrorMessage("textbox_date_error_future");
		$complaintDate->setTable("complaint");
		$complaintDate->setHelpId(8003);
		$group1->add($complaintDate);
				
		$sapCustomerNo = new myAutocomplete("sapCustomerNo");
		$sapCustomerNo->setGroup("group1");
		$sapCustomerNo->setDataType("string");
		$sapCustomerNo->setRowTitle("sap_customer_number");
		$sapCustomerNo->setRequired(true);
		$sapCustomerNo->setErrorMessage("sap_error");
		$sapCustomerNo->setUrl("/apps/customerComplaints/ajax/sap?");
		$sapCustomerNo->setAfterUpdate("updateInvoicesAutocomplete");
		$sapCustomerNo->setTable("complaint");
		$sapCustomerNo->setNullable();
		$sapCustomerNo->setValidateQuery("SAP", "customers", "id");
		$sapCustomerNo->setHelpId(8004);
		$group1->add($sapCustomerNo);
		
		$attachment = new myAttachment("attachment");
		$attachment->setTempFileLocation("/apps/customerComplaints/tmp");
		$attachment->setFinalFileLocation("/apps/customerComplaints/attachments/complaint");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("complaint");
		$attachment->setAnchorRef("attachment");
		$group1->add($attachment);
		
		$category = new myDropdown("categoryId");
		$category->setGroup("group1");
		$category->setDataType("string");
		$category->setLength(255);
		$category->setTranslate(true);
		$category->setRowTitle("complaint_category");
		$category->setErrorMessage("dropdown_error");
		$category->setRequired(true);
		$category->setTranslate(true);
		$category->setNullable();
		$category->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('category') . "
			ORDER BY selectionOption ASC");
		$category->setTable("complaint");
		$category->setHelpId(8005);
		$group1->add($category);
		
		$complaintValueBaseGroup = new group("complaintValueBaseGroup");
		$complaintValueBaseGroup->setBorder(false);
		
		$complaintValueBase = new myRadio("complaintValueBase");
		$complaintValueBase->setGroup("complaintValueBaseGroup");
		$complaintValueBase->setDataType("number");
		$complaintValueBase->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$complaintValueBase->setRowTitle("complaint_value_based_on_invoices");
		$complaintValueBase->setLabel("complaint_values");
		$complaintValueBase->setRequired(true);
		$complaintValueBase->setTranslate(true);
		$complaintValueBase->setVisible(true);
		$complaintValueBase->setNullable();
		$complaintValueBase->setValue(1);
		$complaintValueBase->setTable("complaint");
		$complaintValueBase->setOnChange('complaint.complaintValueBaseChanged();');
		$complaintValueBase->setHelpId(820);
		
		$complaintValueBaseDependency1 = new dependency();
		$complaintValueBaseDependency1->addRule(new rule('complaintValueBaseGroup', 'complaintValueBase', 1));
		$complaintValueBaseDependency1->setGroup('sapInvoiceNoGroup');
		$complaintValueBaseDependency1->setShow(true);
		
		$complaintValueBaseDependency2 = new dependency();
		$complaintValueBaseDependency2->addRule(new rule('complaintValueBaseGroup', 'complaintValueBase', 0));
		$complaintValueBaseDependency2->setGroup('nonInvoiceCostsGroup');
		$complaintValueBaseDependency2->setShow(true);
		
		$complaintValueBaseDependency3 = new dependency();
		$complaintValueBaseDependency3->addRule(new rule('complaintValueBaseGroup', 'complaintValueBase', 1));
		$complaintValueBaseDependency3->setGroup('invoicesBasedGroup');
		$complaintValueBaseDependency3->setShow(true);
		
		$complaintValueBase->addControllingDependency($complaintValueBaseDependency1);
		$complaintValueBase->addControllingDependency($complaintValueBaseDependency2);
		$complaintValueBase->addControllingDependency($complaintValueBaseDependency3);
		$complaintValueBaseGroup->add($complaintValueBase);
		
		$sapInvoiceNoGroup = new multiplegroup("sapInvoiceNoGroup");
		$sapInvoiceNoGroup->setTitle("sap_invoice_no");
		$sapInvoiceNoGroup->setAnchorRef("sapInvoiceNoGroup");
		$sapInvoiceNoGroup->setTable("invoicePopup");
		$sapInvoiceNoGroup->setForeignKey("complaintId");
		$sapInvoiceNoGroup->setBorder(false);

		$sapInvoiceNo = new myItemPopUp("invoiceNo");
		$sapInvoiceNo->setDataType("number");
		$sapInvoiceNo->setPopUpButtonText(translate::getInstance()->translate("load_invoice"));
		$sapInvoiceNo->setLength(25);
		$sapInvoiceNo->setUrl("/apps/customerComplaints/ajax/invoiceNo?");
		//$sapInvoiceNo->setAfterUpdate("test");
		$sapInvoiceNo->setPopUpUrl('return complaint.openPopup(this.id);');
		$sapInvoiceNo->setRowTitle("sap_invoice_no");
		$sapInvoiceNo->setRequired(true);				// change this when the new control is ready
		$sapInvoiceNo->setTable("invoicePopup");
		$sapInvoiceNo->setValidateQuery("complaintsCustomer", "invoicePopup_TEMP", "invoiceNo");
		$sapInvoiceNo->setHelpId(821);
		$sapInvoiceNo->setErrorMessage("select_valid_sap_invoice_no");
		$sapInvoiceNoGroup->add($sapInvoiceNo);		
		
		$invoiceValue = new myInvisibletext("invoiceValue");
		$invoiceValue->setGroup("sapInvoiceNoGroup");
		$invoiceValue->setRequired(false);
		$invoiceValue->setIgnore(true);
		$sapInvoiceNoGroup->add($invoiceValue);
		
		// Added 5/11/2012 - Rob
		$invoiceValueTotal = new myInvisibletext("invoiceValueTotal");
		$invoiceValueTotal->setGroup("sapInvoiceNoGroup");
		$invoiceValueTotal->setRequired(false);
		$invoiceValueTotal->setIgnore(true);
		$sapInvoiceNoGroup->add($invoiceValueTotal);
		
		$invoiceValueShow = new readonly("invoiceValueShow");
		$invoiceValueShow->setGroup("sapInvoiceNoGroup");
		$invoiceValueShow->setRowTitle("invoice_value_complaint");
		$invoiceValueShow->setRequired(false);
		$invoiceValueShow->setIgnore(true);
		$invoiceValueShow->setHelpId(822);
		$sapInvoiceNoGroup->add($invoiceValueShow);
		
		// Added 5/11/2012 - Rob
		$invoiceValueTotalShow = new readonly("invoiceValueTotalShow");
		$invoiceValueTotalShow->setGroup("sapInvoiceNoGroup");
		$invoiceValueTotalShow->setRowTitle("invoice_value");
		$invoiceValueTotalShow->setRequired(false);
		$invoiceValueTotalShow->setIgnore(true);
		$invoiceValueTotalShow->setHelpId(822);
		$sapInvoiceNoGroup->add($invoiceValueTotalShow);
		
		$nonInvoiceCostsGroup = new group("nonInvoiceCostsGroup");
		$nonInvoiceCostsGroup->setBorder(false);
		
		$nonInvoiceCosts = new myMeasurement("nonInvoiceCosts");
		$nonInvoiceCosts->setGroup("nonInvoiceCostsGroup");
		$nonInvoiceCosts->setErrorMessage("field_error");
		$nonInvoiceCosts->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value 
			FROM selectionOptions
			WHERE typeId = " . complaintLib::getOptionTypeId('currency') . "
			ORDER BY selectionOption ASC");
		$nonInvoiceCosts->setRowTitle("nonInvoice_costs");
		$nonInvoiceCosts->setLabel("values");
		$nonInvoiceCosts->setRequired(false);
		$nonInvoiceCosts->setNullable();
		$nonInvoiceCosts->quantity_setOnChange("complaint.nonInvoiceCostsChanged");
		$nonInvoiceCosts->measurement_setOnChange("complaint.nonInvoiceCostsChanged");
		$nonInvoiceCosts->setSaveQuantityOnly();
		$nonInvoiceCosts->setTable("complaint");
		$nonInvoiceCosts->setHelpId(823);
		$nonInvoiceCosts->clearData();
		$nonInvoiceCostsGroup->add($nonInvoiceCosts);
		
		$nonInvoiceCostsComment = new myTextarea("nonInvoiceCostsComment");
		$nonInvoiceCostsComment->setGroup("nonInvoiceCostsGroup");
		$nonInvoiceCostsComment->setDataType("text");
		$nonInvoiceCostsComment->setTable("complaint");
		$nonInvoiceCostsComment->setHelpId(824);
		$nonInvoiceCostsComment->setNullable();
		$nonInvoiceCostsComment->setRowTitle("nonInvoice_costs_comment");
		$nonInvoiceCostsGroup->add($nonInvoiceCostsComment);
		
		$invoicesBasedGroup = new group("invoicesBasedGroup");
		$invoicesBasedGroup->setBorder(false);
		
		// Added 5/11/2012 - Rob
		$totalInvoicesValueComplaint = new myInvisibletext("totalInvoicesValueComplaint");
		$totalInvoicesValueComplaint->setGroup("invoicesBasedGroup");
		$totalInvoicesValueComplaint->setDataType("decimal");
		$totalInvoicesValueComplaint->setRowTitle("total_invoices_value");
		$totalInvoicesValueComplaint->setTable('complaint');
		$totalInvoicesValueComplaint->setRequired(false);
		$totalInvoicesValueComplaint->setNullable();
		$invoicesBasedGroup->add($totalInvoicesValueComplaint);
		
		$totalInvoicesValue = new myInvisibletext("totalInvoicesValue");
		$totalInvoicesValue->setGroup("invoicesBasedGroup");
		$totalInvoicesValue->setDataType("decimal");
		$totalInvoicesValue->setRowTitle("total_invoices_value");
		$totalInvoicesValue->setTable('complaint');
		$totalInvoicesValue->setRequired(false);
		$totalInvoicesValue->setNullable();
		$invoicesBasedGroup->add($totalInvoicesValue);
		
		// Added 5/11/2012 - Rob
		$totalInvoicesValueComplaint = new readonly("totalInvoicesValueShowComplaint");
		$totalInvoicesValueComplaint->setGroup("invoicesBasedGroup");
		$totalInvoicesValueComplaint->setDataType("decimal");
		$totalInvoicesValueComplaint->setRowTitle("total_invoices_value_complaint");
		$totalInvoicesValueComplaint->setTable('complaint');
		$totalInvoicesValueComplaint->setLabel("values");
		$totalInvoicesValueComplaint->setRequired(false);
		$totalInvoicesValueComplaint->setHelpId(825);
		$invoicesBasedGroup->add($totalInvoicesValueComplaint);
		
		$totalInvoicesValueShow = new readonly("totalInvoicesValueShow");
		$totalInvoicesValueShow->setGroup("invoicesBasedGroup");
		$totalInvoicesValueShow->setDataType("string");
		$totalInvoicesValueShow->setRowTitle("total_invoices_value");
		$totalInvoicesValueShow->setRequired(false);
		$totalInvoicesValueShow->setHelpId(999);
		$invoicesBasedGroup->add($totalInvoicesValueShow);
		
		$additionalCosts = new myTextbox("additionalCosts");
		$additionalCosts->setGroup("invoicesBasedGroup");
		$additionalCosts->setDataType("decimal");
		$additionalCosts->setTable('complaint');
		$additionalCosts->setRowTitle("additional_costs");
		$additionalCosts->setRequired(false);
		$additionalCosts->setNullable();
		$additionalCosts->setHelpId(826);
		$additionalCosts->setValidationType( myTextbox::$CURRENCY );
		$additionalCosts->setOnChange("complaint.additionalCostsChanged");
		$invoicesBasedGroup->add($additionalCosts);
				
		$additionalCostsComment = new myTextarea("additionalCostsComment");
		$additionalCostsComment->setGroup("invoicesBasedGroup");
		$additionalCostsComment->setDataType("text");
		$additionalCostsComment->setTable("complaint");
		$additionalCostsComment->setHelpId(827);
		$additionalCostsComment->setNullable();
		$additionalCostsComment->setRowTitle("additional_costs_comment");
		$invoicesBasedGroup->add($additionalCostsComment);
		
		$complaintValue = new myInvisibletext("complaintValue");
		$complaintValue->setGroup("group3");
		$complaintValue->setDataType("decimal");
		$complaintValue->setTable('complaint');
		$complaintValue->setRequired(false);
		$complaintValue->setRowTitle("complaint_value");
		$complaintValue->setNullable();
		$group3->add($complaintValue);
		
		$complaintCurrency = new myInvisibletext("complaintCurrency");
		$complaintCurrency->setGroup("group3");
		$complaintCurrency->setDataType("string");
		$complaintCurrency->setTable('complaint');
		$complaintCurrency->setRequired(false);
		$complaintCurrency->setRowTitle("currency");
		$complaintCurrency->setNullable();
		$group3->add($complaintCurrency);
		
		$complaintValueShow = new readonly("complaintValueShow");
		$complaintValueShow->setGroup("group3");
		$complaintValueShow->setDataType("string");
		$complaintValueShow->setRowTitle("complaint_value");
		$complaintValueShow->setHelpId(8027);
		$complaintValueShow->setRequired(false);
		$group3->add($complaintValueShow);
		
		$complaintValueGBP = new myInvisibletext("complaintValueGBP");
		$complaintValueGBP->setGroup("group3");
		$complaintValueGBP->setDataType("decimal");
		$complaintValueGBP->setTable('complaint');
		$complaintValueGBP->setRequired(false);
		$complaintValueGBP->setNullable();
		$group3->add($complaintValueGBP);
		
		$creditNoteRequested = new myRadio("creditNoteRequested");
		$creditNoteRequested->setGroup("group3");
		$creditNoteRequested->setDataType("string");
		$creditNoteRequested->setLength(5);
		$creditNoteRequested->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$creditNoteRequested->setValue(1);
		$creditNoteRequested->setTranslate(true);
		$creditNoteRequested->setRowTitle("credit_note_requested");
		$creditNoteRequested->setRequired(true);
		$creditNoteRequested->setNullable();
		$creditNoteRequested->setTable("complaint");
		$creditNoteRequested->setHelpId(8034);
		$group3->add($creditNoteRequested);
		
		$factoredProduct = new radio("factoredProduct");
		$factoredProduct->setGroup("factoredProductGroup");
		$factoredProduct->setDataType("boolean");
		$factoredProduct->setLength(1);
		$factoredProduct->setLabel("other_details");
		$factoredProduct->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$factoredProduct->setRowTitle("factored_product");
		$factoredProduct->setRequired(true);
		$factoredProduct->setTranslate(true);
		$factoredProduct->setTable("complaint");
		$factoredProduct->setValue(0);
		$factoredProduct->setHelpId(8023);
		
		// Dependency
		$factoredProductDependency = new dependency();
		$factoredProductDependency->addRule(new rule('factoredProductGroup', 'factoredProduct', 1));
		$factoredProductDependency->setGroup('factoredProductSupplierGroup');
		$factoredProductDependency->setShow(true);

		$factoredProduct->addControllingDependency($factoredProductDependency);
		$factoredProductGroup->add($factoredProduct);
							
		$factoredProductSupplier = new myTextbox("factoredProductSupplier");
		$factoredProductSupplier->setGroup("factoredProductSupplierGroup");
		$factoredProductSupplier->setDataType("string");
		$factoredProductSupplier->setLength(30);
		$factoredProductSupplier->setRowTitle("factored_product_supplier_name");
		$factoredProductSupplier->setRequired(false);
		$factoredProductSupplier->setNullable();
		$factoredProductSupplier->setVisible(true);
		$factoredProductSupplier->setTable("complaint");
		$factoredProductSupplier->setHelpId(8047);
		$factoredProductSupplierGroup->add($factoredProductSupplier);
		
		$customerItemNo = new myTextbox("customerItemNo");
		$customerItemNo->setGroup("group2");
		$customerItemNo->setDataType("string");
		$customerItemNo->setLength(70);
		$customerItemNo->setRowTitle("customer_item_number");
		$customerItemNo->setRequired(false);
		$customerItemNo->setNullable();
		$customerItemNo->setTable("complaint");
		$customerItemNo->setHelpId(8024);
		$group2->add($customerItemNo);
		
		$siteTypeId = complaintLib::getOptionTypeId('site');
		
		// Restrict site lists to active sites or sites that were active on the complaint date		
		if (!$this->complaintLib->isSubmitted($this->complaintId, 'complaint'))
		{
			$siteSQL = " AND (active = 1 OR active = 0 AND deactivatedDate > '" . date('Y-m-d') . "') ";
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
				SELECT complaintDate 
				FROM complaint 
				WHERE id = " . $this->complaintId);
			
			$fields = mysql_fetch_array($dataset);
			
			$siteSQL = " AND (active = 1 OR active = 0 AND deactivatedDate > '" . $fields['complaintDate'] . "') ";
		}

		$despatchSite = new dropdown("despatchSite");
		$despatchSite->setGroup("group2");
		$despatchSite->setDataType("string");
		$despatchSite->setErrorMessage("dropdown_error");
		$despatchSite->setLength(50);
		$despatchSite->setRowTitle("despatch_site");
		$despatchSite->setRequired(true);
		//$despatchSite->setXMLSource("./apps/complaints/xml/sites.xml");
		$despatchSite->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value
			FROM selectionOptions 
			WHERE typeId = " . $siteTypeId . " " . $siteSQL . "			
			ORDER BY selectionOption ASC");
		$despatchSite->setTable("complaint");
		$despatchSite->setHelpId(8030);
		$group2->add($despatchSite);
		
		$manufacturingSite = new dropdown("manufacturingSite");
		$manufacturingSite->setGroup("group2");
		$manufacturingSite->setDataType("string");
		$manufacturingSite->setLength(50);
		$manufacturingSite->setErrorMessage("dropdown_error");
		$manufacturingSite->setRowTitle("manufacturing_site");
		$manufacturingSite->setRequired(true);
		//$manufacturingSite->setXMLSource("./apps/complaints/xml/sites.xml");
		$manufacturingSite->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value
			FROM selectionOptions 
			WHERE typeId = " . $siteTypeId . " " . $siteSQL . "		
			ORDER BY selectionOption ASC");
		$manufacturingSite->setTable("complaint");
		$manufacturingSite->setHelpId(8031);
		$group2->add($manufacturingSite);

		$siteOriginError = new dropdown("siteOriginError");
		$siteOriginError->setGroup("group2");
		$siteOriginError->setDataType("string");
		$siteOriginError->setLength(50);
		$siteOriginError->setErrorMessage("dropdown_error");
		$siteOriginError->setRowTitle("origin_site_error");
		$siteOriginError->setRequired(true);
		//$siteOriginError->setXMLSource("./apps/complaints/xml/sites.xml");
		$siteOriginError->setSQLSource("complaintsCustomer",
			"SELECT selectionOption AS name, id AS value
			FROM selectionOptions 
			WHERE typeId = " . $siteTypeId . " " . $siteSQL . "		
			AND selectionOption != 'Other' 
			ORDER BY selectionOption ASC");
		$siteOriginError->setTable("complaint");
		$siteOriginError->setHelpId(8032);
		$group2->add($siteOriginError);

		$carrierName = new myTextbox("carrierName");
		$carrierName->setGroup("group2");
		$carrierName->setDataType("string");
		$carrierName->setLength(255);
		$carrierName->setRowTitle("carrier_name");
		$carrierName->setRequired(false);
		$carrierName->setNullable();
		$carrierName->setTable("complaint");
		$carrierName->setHelpId(8033);
		$group2->add($carrierName);

		$problemDescription = new myTextarea("problemDescription");
		$problemDescription->setGroup("group2");
		$problemDescription->setDataType("text");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setRequired(true);
		$problemDescription->setErrorMessage("enter_problem_description");
		$problemDescription->setTable("complaint");
		$problemDescription->setNullable();
		$problemDescription->setLargeTextarea(true);
		$problemDescription->setHelpId(8035);
		$group2->add($problemDescription);
				
		$containmentActions = new myTextarea("containmentActions");
		$containmentActions->setGroup("group2");
		$containmentActions->setDataType("text");
		$containmentActions->setRowTitle("containment_actions");
		$containmentActions->setRequired(false);
		$containmentActions->setNullable();
		$containmentActions->setTable("complaint");
		$containmentActions->setLargeTextarea(true);
		$containmentActions->setHelpId(8043);
		$group2->add($containmentActions);

		$customerRequestedActions = new myTextarea("customerRequestedActions");
		$customerRequestedActions->setGroup("group2");
		$customerRequestedActions->setDataType("text");
		$customerRequestedActions->setRowTitle("actions_requested_from_the_customer");
		$customerRequestedActions->setRequired(false);
		$customerRequestedActions->setNullable();
		$customerRequestedActions->setTable("complaint");
		$customerRequestedActions->setLargeTextarea(true);
		$customerRequestedActions->setHelpId(8044);
		$group2->add($customerRequestedActions);
		
		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1118);
		$processOwnerLink->setLink("http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Process%20Owner%20Matrix.xls");
		$processOwnerLink->setValue("{TRANSLATE:process_owner_matrix}");
		$group2->add($processOwnerLink);
		
		$owner = new myAutocomplete("evaluationOwner");
		$owner->setGroup("group2");
		$owner->setDataType("string");
		$owner->setLength(25);
		$owner->setIsAnNTLogon(true);
		$owner->setUrl("/apps/customerComplaints/ajax/employee?&amp;field=name");
		$owner->setRowTitle("evaluation_owner");
		$owner->setRequired(true);
		$owner->setErrorMessage("select_valid_employee");
		$owner->setValidateQuery("membership", "employee", "NTLogon");
		$owner->setTable("complaint");
		$owner->setHelpId(829);
		$owner->setNullable();
		$group2->add($owner);
		
		$emailText = new myTextarea("emailText");
		$emailText->setGroup("group2");
		$emailText->setDataType("text");
		$emailText->setRowTitle("email_text");
		$emailText->setRequired(false);
		$emailText->setNullable();
		$emailText->setTable("complaint");
		$emailText->setHelpId(8045);
		$group2->add($emailText);
		
		// If the form hasn't been submitted, offer the option to save or submit		
		if (!$this->complaintLib->isSubmitted($this->complaintId, 'complaint'))
		{
			$submitOption = new radio("submitStatus");
			$submitOption->setGroup("group2");
			$submitOption->setDataType("boolean");
			$submitOption->setLength(1);
			$submitOption->setArraySource(array(
					array('value' => 1, 'display' => 'submit'),
					array('value' => 0, 'display' => 'save')
				));
			$submitOption->setRowTitle("submit_option");
			$submitOption->setRequired(true);
			$submitOption->setTranslate(true);
			$submitOption->setTable("complaint");
			$submitOption->setOnKeyPress('complaint.toggleSaveSubmit();');
			$submitOption->setValue(1);
			$submitOption->setHelpId(1120);
			$group2->add($submitOption);
			
			$submit = new submit("submit");
			$submit->setGroup("submitGroup");
			$submit->setVisible(true);
			$submitGroup->add($submit);
		}
		else 
		{
			$submit = new submit("Submit");
			$submit->setGroup("submitGroup");
			$submit->setVisible(true);
			$submitGroup->add($submit);
		}
		
		// Add all groups to the form
		$this->form->add($submitOnBehalfGroup);
		$this->form->add($submitByGroup);
		//$this->form->add($submissionDateGroup);
		$this->form->add($groupedComplaintGroup);
		$this->form->add($groupedComplaintIdGroup);
		$this->form->add($group1);
		$this->form->add($complaintValueBaseGroup);
		$this->form->add($sapInvoiceNoGroup);
		$this->form->add($nonInvoiceCostsGroup);
		$this->form->add($invoicesBasedGroup);
		$this->form->add($group3);
		$this->form->add($factoredProductGroup);
		$this->form->add($factoredProductSupplierGroup);
		$this->form->add($group2);
		//$this->form->add($complaintQuantityGroup);
		$this->form->add($submitGroup);
	}
	
	private function getNewId()
	{
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT id 
			FROM complaint 
			ORDER BY id DESC
			LIMIT 1");
		
		$fields = mysql_fetch_array($dataset);
		
		return $fields['id']; 
	}
	
	private function determineAction()
	{
		if( $this->complaintId < 0 )
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
			if( $this->form->get("submitStatus") )
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
	
	private function saveGroupedComplaint()
	{
		$complaintId = $this->complaintId;
		
		$sql = "DELETE FROM groupedComplaints 
				WHERE complaintId = $complaintId";
				
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		for($row=0 ; $row < $this->form->getGroup("groupedComplaintIdGroup")->getRowCount() ; $row++)
		{
			if( $this->form->getGroup("groupedComplaintIdGroup")->get($row,"groupComplaintId")->getValue() != '' )
			{
				$groupComplaintId = $this->form->getGroup("groupedComplaintIdGroup")->get($row,"groupComplaintId")->getValue();
				
				$sql = "INSERT INTO groupedComplaints 
							(complaintId, groupComplaintId) 
						VALUES 
							($complaintId, $groupComplaintId)";
						
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
			}
		}
	}
	
	private function loadGroupedComplaint()
	{
		$sql = "SELECT groupComplaintId
				FROM groupedComplaints 
				WHERE complaintId = " . $this->complaintId . " 
				GROUP BY groupComplaintId";
		
		$this->form->getGroup('groupedComplaintIdGroup')->load(
				mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql)
			);
	}
	
	private function loadInvoices()
	{
		if( $this->readOnly || $this->approval->started() )
		{
			$sql = "SELECT 	distinct(invoiceNo), 
						SUM( netValueItem_edit ) AS invoiceValue, 
						netValueItemTotal_edit AS invoiceValueTotal, 
						CONCAT( CONCAT( SUM(netValueItem_edit), ' '), netValueItemCurrency_edit ) AS invoiceValueShow,
						CONCAT( CONCAT( netValueItemTotal_edit, ' '), netValueItemCurrency_edit ) AS invoiceValueTotalShow
					FROM invoicePopup 
					WHERE complaintId = " . $this->complaintId . " 
					GROUP BY invoiceNo";
			
			$this->form->getGroup('sapInvoiceNoGroup')->load(
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql)
				);
		}
		else
		{
			$sql = "DELETE FROM invoicePopup_TEMP 
					WHERE complaintId = " . $this->complaintId . " 
					AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
					
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
			
			$sql = "INSERT INTO invoicePopup_TEMP 
						(complaintId, invoicesId, invoiceNo, NTLogon, dateSaved, batch_edit, 
						deliveryQuantity_edit, deliveryQuantityUOM_edit, netValueItem_edit, 
						netValueItemCurrency_edit, netValueItemGBP_edit, netValueItemTotal_edit) 
					SELECT complaintId, invoicesId, invoiceNo, '" . currentuser::getInstance()->getNTLogon() . "', NOW(), batch_edit, 
							deliveryQuantity_edit, deliveryQuantityUOM_edit, netValueItem_edit, 
							netValueItemCurrency_edit, netValueItemGBP_edit, netValueItemTotal_edit 
					FROM invoicePopup 
					WHERE complaintId = " . $this->complaintId;
				
			mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
			
			$sql = "SELECT 	distinct(invoiceNo), 
							SUM( netValueItem_edit ) AS invoiceValue, 
							netValueItemTotal_edit AS invoiceValueTotal,
							CONCAT( CONCAT( SUM(netValueItem_edit), ' '), netValueItemCurrency_edit ) AS invoiceValueShow,
							CONCAT( CONCAT( (netValueItemTotal_edit), ' '), netValueItemCurrency_edit ) AS invoiceValueTotalShow
					FROM invoicePopup_TEMP 
					WHERE complaintId = " . $this->complaintId . " 
					AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' 
					GROUP BY invoiceNo";
				
			$this->form->getGroup('sapInvoiceNoGroup')->load(
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql)
				);
				
			for($row=0 ; $row < $this->form->getGroup("sapInvoiceNoGroup")->getRowCount() ; $row++)
			{
				$invoiceNo = $this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->getValue();
				if( $invoiceNo != "")
				{
					$sql = "UPDATE invoicePopup_TEMP 
							SET complaintRowNo = " . $row . " 
							WHERE complaintId = " . $this->complaintId . " 
							AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' 
							AND invoiceNo = " . $invoiceNo;
						
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
				}
			}
		}
	}
	
	private function saveInvoices()
	{
		$sql = "DELETE FROM invoicePopup 
			WHERE complaintId = " . $this->complaintId;
			
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		if( $this->form->get("complaintValueBase")->getValue() == 1 )
		{
			if ( $this->form->getGroup("sapInvoiceNoGroup")->get(0,"invoiceNo")->getValue() != '')
			{
				for($row=0 ; $row < $this->form->getGroup("sapInvoiceNoGroup")->getRowCount() ; $row++)
				{
					$invoiceNo = $this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->getValue();
					
					$sql = "INSERT INTO invoicePopup 
								(complaintId, invoicesId, invoiceNo, batch_edit, 
								deliveryQuantity_edit, deliveryQuantityUOM_edit, netValueItem_edit, 
								netValueItemCurrency_edit, netValueItemGBP_edit, netValueItemTotal_edit) 
							SELECT 
								complaintId, invoicesId, invoiceNo, batch_edit, 
								deliveryQuantity_edit, deliveryQuantityUOM_edit, netValueItem_edit, 
								netValueItemCurrency_edit, netValueItemGBP_edit, netValueItemTotal_edit
							FROM invoicePopup_TEMP 
							WHERE invoiceNo = " . $invoiceNo . " 
							AND complaintId = " . $this->complaintId . " 
							AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
							
					mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
				}
			}
		}
		
		$sql = "DELETE FROM invoicePopup_TEMP 
				WHERE complaintId = " .$this->complaintId . " 
				AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";
			
		mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
	}
	
	private function updateInvoices($oldId, $newId)
	{
		// Change the complaint ID in the invoicePopup table to the new complaint ID
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			UPDATE invoicePopup 
			SET complaintId = " . $newId . "
			WHERE complaintId = " . $oldId);
	}
	
	private function setupFields()
	{
		if( $this->readOnly )
		{
			if( $this->form->get("complaintCurrency")->getValue() != "" )
			{
				$this->formatCurrency( "totalInvoicesValueComplaint", "totalInvoicesValueShowComplaint" );
				$this->formatCurrency( "totalInvoicesValue", "totalInvoicesValueShow" );
				$this->formatCurrency( "complaintValue", "complaintValueShow" );
				$this->formatCurrency( "additionalCosts", "additionalCosts" );
				$this->form->get("nonInvoiceCosts" )->setVisible(false);
				$this->form->get("nonInvoiceCostsComment" )->setLabel("values");
			}
		}
		else
		{
			if ($this->complaintLib->isSubmitted($this->complaintId, 'complaint'))
			{
				$this->form->get("evaluationOwner")->setReadOnly(true);
				$this->form->get("submitOnBehalf")->setReadOnly(true);
				$this->form->get("submitBy")->setReadOnly(true);
				$this->form->get("emailText")->setReadOnly(true);
			}	
			else 
			{
				$this->form->get("submitBy")->setIsAnNTLogon(false);
				$this->form->get("evaluationOwner")->setIsAnNTLogon(false);
			}
			
			if( $this->approval->started() )
			{
				$this->form->get("sapCustomerNo")->setReadOnly(true);
				$this->form->get("nonInvoiceCosts")->setReadOnly(true);
				$this->form->get("nonInvoiceCostsComment")->setReadOnly(true);
				$this->form->get("complaintValueBase")->setReadOnly(true);
				$this->form->get("categoryId")->setReadOnly(true);
				$this->form->get("creditNoteRequested")->setReadOnly(true);
				$this->form->get("additionalCosts")->setReadOnly(true);
				$this->form->get("additionalCostsComment")->setReadOnly(true);
				$this->form->get("nonInvoiceCosts" )->setVisible(false);
				$this->form->get("nonInvoiceCostsComment" )->setLabel("values");
				
				$this->formatCurrency( "totalInvoicesValueComplaint", "totalInvoicesValueShowComplaint" );
				$this->formatCurrency( "totalInvoicesValue", "totalInvoicesValueShow" );
				$this->formatCurrency( "complaintValue", "complaintValueShow" );
				$this->formatCurrency( "additionalCosts", "additionalCosts" );
				
				for($row=0 ; $row < $this->form->getGroup("sapInvoiceNoGroup")->getRowCount() ; $row++)
				{
					$invoiceNo = $this->form->getGroup("sapInvoiceNoGroup")->get($row,"invoiceNo")->setReadOnly(true);
				}
			}
		}
	}

	private function loadData_DB()
	{
		$sql = "SELECT * 
				FROM complaint 
				WHERE id = " . $this->complaintId;
			
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		
		$fields = mysql_fetch_array($dataset);
		
		// Populate the form with data
		$this->form->populate($fields);
		
		// Check if attachment is to be updated
		$this->form->get("attachment")->load("/apps/customerComplaints/attachments/complaint/" . $this->complaintId . "/");
		
		$this->form->get("nonInvoiceCosts")->setMeasurement( $fields['complaintCurrency'] );
	}
	
	private function formatCurrency($sourceField, $destinationField)
	{
		$value = $this->form->get( $sourceField )->getValue();
		
		if( $value != '' )
		{
			$currency = complaintLib::getOptionText( $this->form->get("complaintCurrency")->getValue() );
			
			$display = sprintf("%01.2f", $value ) . " " . $currency;
			
			$this->form->get( $destinationField )->setValue( $display );
		}
	}
}
	
?>