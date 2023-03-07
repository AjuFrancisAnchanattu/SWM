<?php

class edit_Root_Cause_Corrective_Action
{
	public $pdfType = "root_cause_corrective_action";
	
	private $complaintId;
	public $form;
	
	function __construct($complaintId)
	{
		$this->complaintId = $complaintId;
		$this->formName = get_class($this) . '_' . $this->complaintId . '_' . currentuser::getInstance()->getNTLogon();
	
		$this->defineForm();
	}
	
	public function populateForm()
	{
		$sql = "SELECT * FROM complaint WHERE id=" . $this->complaintId;
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$complaint = mysql_fetch_array($dataset);
		
		$sql = "SELECT * FROM evaluation WHERE complaintId=" . $this->complaintId;
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$evaluation = mysql_fetch_array($dataset);
		
		$this->form->get("registrationDate")->setValue( $complaint['complaintDate'] );
		
		$sql = "SELECT salesEmployees.name AS name, salesEmployees.NTLogon AS NTLogon
				FROM customers 
				INNER JOIN salesEmployees 
				ON customers.salesEmp = salesEmployees.id 
				WHERE customers.id LIKE('" . $complaint['sapCustomerNo'] . "')";
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			$salesEmployee = mysql_fetch_array($dataset);
			$this->form->get("salesName")->setValue( $salesEmployee['name'] );
			
			if( $salesEmployee['NTLogon'] != NULL )
			{
				$sql = "SELECT *
						FROM employee 
						WHERE NTLogon = '" . $salesEmployee['NTLogon'] . "'";
			}
			else
			{
				$sql = "SELECT * 
						FROM employee 
						WHERE CONCAT( firstName, ' ', lastName) = '" . $salesEmployee['name'] . "'";
			}
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
			
			if( mysql_num_rows( $dataset ) > 0 )
			{
				$employeeData = mysql_fetch_array( $dataset );
				$this->form->get("tel")->setValue( $employeeData['phone'] );
				$this->form->get("salesFax")->setValue( $employeeData['fax'] );
				$this->form->get("salesEmail")->setValue( $employeeData['email'] );
				$this->form->get("salesOffice")->setValue( $employeeData['site'] );
			}
		}
		
		$this->form->get("customerName")->setValue( sapCustomer::getName($complaint['sapCustomerNo']) );
		$this->form->get("customerNumber")->setValue( $complaint['sapCustomerNo'] );
		
		$this->form->get("problemDescription")->setValue( $complaint['problemDescription'] );
		
		$this->form->get("containmentActions")->setValue( $complaint['containmentActions'] );
		$this->form->get("customerRequestedActions")->setValue( $complaint['customerRequestedActions'] );
		
		$this->form->get("sampleReceived")->setValue( $evaluation['sampleReceived'] == 0 ? "No" : "Yes" );
		$this->form->get("sampleDate")->setValue( $evaluation['sampleDate'] );
		$this->form->get("teamLeader")->setValue( $evaluation['teamLeader'] );
		$this->form->get("processOwner")->setValue( usercache::getInstance()->get($complaint['evaluationOwner'])->getName() );
		$this->form->get("complaintJustified")->setValue( $evaluation['complaintJustified'] == 1 ? "Yes" : "No" );
		switch( $evaluation['goodsAction'] )
		{
			case null:
				$this->form->get("returnGoods")->setValue( "N/A" );
				$this->form->get("disposeGoods")->setValue( "N/A" );
				break;
			case "0":
				$this->form->get("returnGoods")->setValue( "No" );
				$this->form->get("disposeGoods")->setValue( "Yes" );
				break;
			case "1":
				$this->form->get("returnGoods")->setValue( "Yes" );
				$this->form->get("disposeGoods")->setValue( "No" );
				break;
		}
		$this->form->get("comments")->setValue( $evaluation['additionalComments'] );
		
		$this->form->get("analysis")->setValue( $evaluation['analysis'] );
		$this->form->get("analysisDate")->setValue( $evaluation['analysisDate'] );
		$this->form->get("analysisAuthor")->setValue( usercache::getInstance()->get($evaluation['analysisAuthor'])->getName() );
		
		$this->form->get("rootCauses")->setValue( $evaluation['rootCauses'] );
		$this->form->get("rootCauseDate")->setValue( $evaluation['rootCauseDate'] );
		$this->form->get("rootCauseAuthor")->setValue( usercache::getInstance()->get($evaluation['rootCauseAuthor'])->getName() );
		
		$this->form->get("correctiveActions")->setValue( $evaluation['correctiveActions'] );
		$this->form->get("correctiveActionsAuthor")->setValue( usercache::getInstance()->get($evaluation['correctiveActionsAuthor'])->getName() );
		$this->form->get("correctiveActionsDate")->setValue( $evaluation['correctiveActionsDate'] );
		$this->form->get("correctiveActionsEstDate")->setValue( $evaluation['correctiveActionsEstDate'] );
		$this->form->get("correctiveActionsImpDate")->setValue( $evaluation['correctiveActionsImpDate'] );
		$this->form->get("correctiveActionsEffectivenessValidationDate")->setValue( $evaluation['correctiveActionsEffectivenessValidationDate'] );
		
		$this->form->get("correctiveActionsValidation")->setValue( $evaluation['correctiveActionsValidation'] );
		$this->form->get("correctiveActionsValidationDate")->setValue( $evaluation['correctiveActionsValidationDate'] );
		$this->form->get("correctiveActionsValidationAuthor")->setValue( usercache::getInstance()->get($evaluation['correctiveActionsValidationAuthor'])->getName() );
	}
	
	private function defineForm()
	{
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$myGroup = new group("myGroup");
		
		//*** Section 1 of PDF
		
		$registrationDate = new myCalendar("registrationDate");
		$registrationDate->setGroup("myGroup");
		$registrationDate->setRowTitle("registration_date");
		$registrationDate->setLabel("complaint_information");
		//$tel->setHelpId(8027);
		$myGroup->add($registrationDate);
		
		$salesName = new textbox("salesName");
		$salesName->setGroup("myGroup");
		$salesName->setDataType("string");
		$salesName->setLength(30);
		$salesName->setRowTitle("sales_name");
		//$salesName->setHelpId(8027);
		$myGroup->add($salesName);
		
		$tel = new textbox("tel");
		$tel->setGroup("myGroup");
		$tel->setDataType("string");
		$tel->setLength(30);
		$tel->setRowTitle("telephone");
		//$tel->setHelpId(8027);
		$myGroup->add($tel);
		
		$salesOffice = new textbox("salesOffice");
		$salesOffice->setGroup("myGroup");
		$salesOffice->setDataType("string");
		$salesOffice->setLength(30);
		$salesOffice->setRowTitle("sales_office");
		//$salesOffice->setHelpId(8027);
		$myGroup->add($salesOffice);
		
		$salesFax = new textbox("salesFax");
		$salesFax->setGroup("myGroup");
		$salesFax->setDataType("string");
		$salesFax->setLength(30);
		$salesFax->setRowTitle("fax");
		//$salesFax->setHelpId(8027);
		$myGroup->add($salesFax);
		
		$salesEmail = new textbox("salesEmail");
		$salesEmail->setGroup("myGroup");
		$salesEmail->setDataType("string");
		$salesEmail->setLength(50);
		$salesEmail->setRowTitle("e-mail");
		//$salesEmail->setHelpId(8027);
		$myGroup->add($salesEmail);
		
		$customerComplaintRef = new textbox("customerComplaintRef");
		$customerComplaintRef->setGroup("myGroup");
		$customerComplaintRef->setDataType("string");
		$customerComplaintRef->setLength(30);
		$customerComplaintRef->setRowTitle("customer_complaint_ref");
		//$customerComplaintRef->setHelpId(8027);
		$myGroup->add($customerComplaintRef);
		
		$customerName = new textbox("customerName");
		$customerName->setGroup("myGroup");
		$customerName->setDataType("string");
		$customerName->setLength(50);
		$customerName->setRowTitle("customer_name");
		//$customerName->setHelpId(8027);
		$myGroup->add($customerName);
		
		$customerNumber = new textbox("customerNumber");
		$customerNumber->setGroup("myGroup");
		$customerNumber->setDataType("string");
		$customerNumber->setLength(30);
		$customerNumber->setRowTitle("sap_customer_number");
		//$customerNumber->setHelpId(8027);
		$myGroup->add($customerNumber);
		
		$customerOrderNumber = new textbox("customerOrderNumber");
		$customerOrderNumber->setGroup("myGroup");
		$customerOrderNumber->setDataType("string");
		$customerOrderNumber->setLength(30);
		$customerOrderNumber->setRowTitle("customer_order_number");
		//$customerOrderNumber->setHelpId(8027);
		$myGroup->add($customerOrderNumber);
		
		$customerPartNumber = new textbox("customerPartNumber");
		$customerPartNumber->setGroup("myGroup");
		$customerPartNumber->setDataType("string");
		$customerPartNumber->setLength(50);
		$customerPartNumber->setRowTitle("customer_part_number");
		//$customerPartNumber->setHelpId(8027);
		$myGroup->add($customerPartNumber);
		
		//*** Section 2 of PDF
		
		$problemDescription = new textarea("problemDescription");
		$problemDescription->setGroup("myGroup");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setLabel("problem_identified_by_customer");
		$problemDescription->setLargeTextarea(true);
		$problemDescription->setDataType("text");
		//$problemDescription->setHelpId(8027);
		$myGroup->add($problemDescription);
		
		$customerRequestedActions = new textarea("customerRequestedActions");
		$customerRequestedActions->setGroup("myGroup");
		$customerRequestedActions->setRowTitle("actions_requested_from_the_customer");
		$customerRequestedActions->setLargeTextarea(true);
		$customerRequestedActions->setDataType("text");
		//$customerRequestedActions->setHelpId(8027);
		$myGroup->add($customerRequestedActions);
		
		$containmentActions = new textarea("containmentActions");
		$containmentActions->setGroup("myGroup");
		$containmentActions->setRowTitle("containment_actions");
		$containmentActions->setLargeTextarea(true);
		$containmentActions->setDataType("text");
		//$containmentActions->setHelpId(8027);
		$myGroup->add($containmentActions);
		
		
		//*** Section 4 of the PDF
		
		$sampleReceived = new textbox("sampleReceived");
		$sampleReceived->setGroup("myGroup");
		$sampleReceived->setDataType("string");
		$sampleReceived->setRowTitle("sample_received");
		$sampleReceived->setLength(10);
		$sampleReceived->setLabel("evaluation_and_action");
		//$sampleReceived->setHelpId(8027);
		$myGroup->add($sampleReceived);
		
		$sampleDate = new myCalendar("sampleDate");
		$sampleDate->setGroup("myGroup");
		$sampleDate->setRowTitle("date");
		//$sampleDate->setHelpId(8027);
		$myGroup->add($sampleDate);
		
		$complaintJustified = new textbox("complaintJustified");
		$complaintJustified->setGroup("myGroup");
		$complaintJustified->setDataType("string");
		$complaintJustified->setLength(10);
		$complaintJustified->setRowTitle("complaint_justified");
		//$complaintJustified->setHelpId(8027);
		$myGroup->add($complaintJustified);
		
		$processOwner = new textbox("processOwner");
		$processOwner->setGroup("myGroup");
		$processOwner->setDataType("string");
		$processOwner->setLength(30);
		$processOwner->setRowTitle("process_owner");
		//$processOwner->setHelpId(8027);
		$myGroup->add($processOwner);
		
		$teamLeader = new textbox("teamLeader");
		$teamLeader->setGroup("myGroup");
		$teamLeader->setDataType("string");
		$teamLeader->setLength(30);
		$teamLeader->setRowTitle("team_leader");
		//$teamLeader->setHelpId(8027);
		$myGroup->add($teamLeader);
		
		$returnGoods = new textbox("returnGoods");
		$returnGoods->setGroup("myGroup");
		$returnGoods->setDataType("string");
		$returnGoods->setLength(10);
		$returnGoods->setRowTitle("return_goods");
		//$returnGoods->setHelpId(8027);
		$myGroup->add($returnGoods);
		
		$disposeGoods = new textbox("disposeGoods");
		$disposeGoods->setGroup("myGroup");
		$disposeGoods->setDataType("string");
		$disposeGoods->setLength(10);
		$disposeGoods->setRowTitle("dispose_goods");
		//$disposeGoods->setHelpId(8027);
		$myGroup->add($disposeGoods);
		
		$comments = new textarea("comments");
		$comments->setGroup("myGroup");
		$comments->setRowTitle("comments");
		$comments->setLargeTextarea(true);
		$comments->setDataType("text");
		//$comments->setHelpId(8027);
		$myGroup->add($comments);
		
		$analysis = new textarea("analysis");
		$analysis->setGroup("myGroup");
		$analysis->setRowTitle("analysis");
		$analysis->setLabel("analysis");
		$analysis->setLargeTextarea(true);
		$analysis->setDataType("text");
		//$containmentActions->setHelpId(8027);
		$myGroup->add($analysis);
		
		$analysisDate = new myCalendar("analysisDate");
		$analysisDate->setGroup("myGroup");
		$analysisDate->setRowTitle("date");
		//$analysisDate->setHelpId(8027);
		$myGroup->add($analysisDate);
		
		$analysisAuthor = new textbox("analysisAuthor");
		$analysisAuthor->setGroup("myGroup");
		$analysisAuthor->setDataType("string");
		$analysisAuthor->setLength(30);
		$analysisAuthor->setRowTitle("author");
		//$analysisAuthor->setHelpId(8027);
		$myGroup->add($analysisAuthor);
		
		$rootCauses = new textarea("rootCauses");
		$rootCauses->setGroup("myGroup");
		$rootCauses->setRowTitle("root_causes");
		$rootCauses->setLabel("root_causes");
		$rootCauses->setDataType("text");
		$rootCauses->setLargeTextarea(true);
		//$rootCauses->setHelpId(8027);
		$myGroup->add($rootCauses);
		
		$rootCauseDate = new myCalendar("rootCauseDate");
		$rootCauseDate->setGroup("myGroup");
		$rootCauseDate->setRowTitle("date");
		//$rootCauseDate->setHelpId(8027);
		$myGroup->add($rootCauseDate);
		
		$rootCauseAuthor = new textbox("rootCauseAuthor");
		$rootCauseAuthor->setGroup("myGroup");
		$rootCauseAuthor->setDataType("string");
		$rootCauseAuthor->setLength(30);
		$rootCauseAuthor->setRowTitle("author");
		//$rootCauseAuthor->setHelpId(8027);
		$myGroup->add($rootCauseAuthor);
		
		//*** Section 6
		
		$correctiveActions = new textarea("correctiveActions");
		$correctiveActions->setGroup("myGroup");
		$correctiveActions->setRowTitle("implemented_perm_corrective_actions");
		$correctiveActions->setLabel("implemented_perm_corrective_actions");
		$correctiveActions->setLargeTextarea(true);
		$correctiveActions->setDataType("text");
		//$correctiveActions->setHelpId(8027);
		$myGroup->add($correctiveActions);
		
		$correctiveActionsAuthor = new textbox("correctiveActionsAuthor");
		$correctiveActionsAuthor->setGroup("myGroup");
		$correctiveActionsAuthor->setDataType("string");
		$correctiveActionsAuthor->setLength(30);
		$correctiveActionsAuthor->setRowTitle("author");
		//$correctiveActionsAuthor->setHelpId(8027);
		$myGroup->add($correctiveActionsAuthor);
		
		$correctiveActionsDate = new myCalendar("correctiveActionsDate");
		$correctiveActionsDate->setGroup("myGroup");
		$correctiveActionsDate->setRowTitle("date");
		//$correctiveActionsDate->setHelpId(8027);
		$myGroup->add($correctiveActionsDate);
		
		$correctiveActionsEstDate = new myCalendar("correctiveActionsEstDate");
		$correctiveActionsEstDate->setGroup("myGroup");
		$correctiveActionsEstDate->setRowTitle("corrective_actions_estimated_date");
		//$correctiveActionsEstDate->setHelpId(8027);
		$myGroup->add($correctiveActionsEstDate);
		
		$correctiveActionsImpDate = new myCalendar("correctiveActionsImpDate");
		$correctiveActionsImpDate->setGroup("myGroup");
		$correctiveActionsImpDate->setRowTitle("corrective_actions_implementation_date");
		//$correctiveActionsImpDate->setHelpId(8027);
		$myGroup->add($correctiveActionsImpDate);
		
		$correctiveActionsEffectivenessValidationDate = new myCalendar("correctiveActionsEffectivenessValidationDate");
		$correctiveActionsEffectivenessValidationDate->setGroup("myGroup");
		$correctiveActionsEffectivenessValidationDate->setRowTitle("corrective_actions_effectiveness_validation_date");
		//$correctiveActionsEffectivenessValidationDate->setHelpId(8027);
		$myGroup->add($correctiveActionsEffectivenessValidationDate);
		
		//***
		
		$correctiveActionsValidation = new textarea("correctiveActionsValidation");
		$correctiveActionsValidation->setGroup("myGroup");
		$correctiveActionsValidation->setRowTitle("corrective_actions_validation");
		$correctiveActionsValidation->setLabel("corrective_actions_validation");
		$correctiveActionsValidation->setLargeTextarea(true);
		$correctiveActionsValidation->setDataType("text");
		//$correctiveActionsValidation->setHelpId(8027);
		$myGroup->add($correctiveActionsValidation);
		
		$correctiveActionsValidationDate = new myCalendar("correctiveActionsValidationDate");
		$correctiveActionsValidationDate->setGroup("myGroup");
		$correctiveActionsValidationDate->setRowTitle("date");
		//$correctiveActionsValidationDate->setHelpId(8027);
		$myGroup->add($correctiveActionsValidationDate);
		
		$correctiveActionsValidationAuthor = new textbox("correctiveActionsValidationAuthor");
		$correctiveActionsValidationAuthor->setGroup("myGroup");
		$correctiveActionsValidationAuthor->setDataType("string");
		$correctiveActionsValidationAuthor->setLength(30);
		$correctiveActionsValidationAuthor->setRowTitle("author");
		//$correctiveActionsValidationAuthor->setHelpId(8027);
		$myGroup->add($correctiveActionsValidationAuthor);
		
		$language = new dropdown("language");
		$language->setGroup("myGroup");
		$language->setDataType("string");
		$language->setRowTitle("language");
		$language->setVisible(true);
		$language->setTranslate(true);
		$language->setArraySource(
			array(  
				array('value' => 'EN', 'display' => 'EN'),
				array('value' => 'DE', 'display' => 'DE'),
				array('value' => 'FR', 'display' => 'FR'),
				array('value' => 'ITA', 'display' => 'ITA')
			) 
		);
		$myGroup->add($language);
		
		$submit = new submit("submit");
		$submit->setGroup("myGroup");
		$submit->setValue('Print PDF');
		$myGroup->add($submit);
		
		$this->form->add($myGroup);
	}
}
?>