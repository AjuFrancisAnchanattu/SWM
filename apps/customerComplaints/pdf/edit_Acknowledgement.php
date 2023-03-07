<?php
/**
 * Edit the Acknowledgement data before generating a pdf
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 24/11/2010
 */

class edit_Acknowledgement
{
	protected $pdfType = "Acknowledgement";
	
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
		$datasetComplaint = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT * FROM complaint WHERE id = " . $this->complaintId);
		$fieldsComplaint = mysql_fetch_array($datasetComplaint);
		$this->form->populate($fieldsComplaint);

		$datasetEvaluation = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute("
			SELECT * FROM evaluation WHERE complaintId = " . $this->complaintId);
		if($fieldsEvaluation = mysql_fetch_array($datasetEvaluation))
		{
			$this->form->populate($fieldsEvaluation);
		}
		
		$this->form->get('toTheAttention2')->setValue(sapCustomer::getName($fieldsComplaint['sapCustomerNo']));
		
		$sql = "SELECT salesEmployees.name AS name, salesEmployees.NTLogon AS NTLogon
				FROM customers 
				INNER JOIN salesEmployees 
				ON customers.empResp = salesEmployees.id 
				WHERE customers.id LIKE('" . $fieldsComplaint['sapCustomerNo'] . "')";
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute($sql);
		
		if( mysql_num_rows( $dataset ) > 0 )
		{
			$salesEmployee = mysql_fetch_array($dataset);
			$this->form->get("salesName")->setValue( $salesEmployee['name'] );
			
			if( $salesEmployee['NTLogon'] != NULL )
			{
				$sql = "SELECT *
						FROM employee 
						INNER JOIN sites
						ON employee.site = sites.name
						WHERE NTLogon = '" . $salesEmployee['NTLogon'] . "'";
			}
			else
			{
				$sql = "SELECT * 
						FROM employee 
						INNER JOIN sites
						ON employee.site = sites.name
						WHERE CONCAT( firstName, ' ', lastName) = '" . $salesEmployee['name'] . "'";
			}
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
			
			if( mysql_num_rows( $dataset ) > 0 )
			{
				$employeeData = mysql_fetch_array( $dataset );
				$this->form->get("salesTel")->setValue( $employeeData['phone'] );
				$this->form->get("salesFax")->setValue( $employeeData['fax'] );
				$this->form->get("salesEmail")->setValue( $employeeData['email'] );
				$this->form->get("siteAddress")->setValue( $employeeData['address'] );
			}
		}
	}

	
	private function defineForm()
	{
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$myGroup = new group("myGroup");
		$myGroup->setBorder(false);
		
		$otherGroup = new group("otherGroup");
		$otherGroup->setBorder(false);
		
		$myGroup2 = new group("myGroup2");
		
		$toTheAttention = new textbox("toTheAttention");
		$toTheAttention->setGroup("myGroup");
		$toTheAttention->setDataType("string");
		$toTheAttention->setLength(30);
		$toTheAttention->setRowTitle("to_attention");
		$myGroup->add($toTheAttention);

		$toTheAttention2 = new textbox("toTheAttention2");
		$toTheAttention2->setGroup("myGroup");
		$toTheAttention2->setDataType("string");
		$toTheAttention2->setLength(50);
		$toTheAttention2->setRowTitle("sap_customer_name");
		$myGroup->add($toTheAttention2);

		$complaintDate = new myCalendar("complaintDate");
		$complaintDate->setGroup("myGroup");
		$complaintDate->setTable('complaint');
		$complaintDate->setRowTitle("origination_date");
		$myGroup->add($complaintDate);

		$customerComplaintRef = new textbox("customerComplaintRef");
		$customerComplaintRef->setGroup("myGroup");
		$customerComplaintRef->setDataType("string");
		$customerComplaintRef->setLength(50);
		$customerComplaintRef->setRowTitle("customer_complaint_ref");
		$customerComplaintRef->setTable('complaint');
		$customerComplaintRef->setRequired(false);
		$myGroup->add($customerComplaintRef);

		$customerOrderNo = new textbox("customerOrderNumber");
		$customerOrderNo->setGroup("myGroup");
		$customerOrderNo->setDataType("string");
		$customerOrderNo->setLength(50);
		$customerOrderNo->setRowTitle("customer_order_number");
		$customerOrderNo->setRequired(false);
		$myGroup->add($customerOrderNo);

		$problemDescription = new textarea("problemDescription");
		$problemDescription->setGroup("myGroup");
		$problemDescription->setDataType("text");
		$problemDescription->setTable('complaint');
		$problemDescription->setRowTitle("problem_identified_by_customer");
		$problemDescription->setLargeTextarea(true);
		$myGroup->add($problemDescription);

		$noFurtherInfo = new checkbox("noFurtherInfo");
		$noFurtherInfo->setGroup("myGroup");
		$noFurtherInfo->setDataType("string");
		$noFurtherInfo->setLength(5);
		$noFurtherInfo->setRowTitle("no_further_information");
		$myGroup->add($noFurtherInfo);

		$requireBatchNumbers = new checkbox("requireBatchNumbers");
		$requireBatchNumbers->setGroup("myGroup");
		$requireBatchNumbers->setDataType("string");
		$requireBatchNumbers->setLength(5);
		$requireBatchNumbers->setRowTitle("batch_number_label");
		$requireBatchNumbers->setLabel("Information Required From The Customer");
		$myGroup->add($requireBatchNumbers);

		$requireOtherNumbers = new checkbox("requireOtherNumbers");
		$requireOtherNumbers->setGroup("myGroup");
		$requireOtherNumbers->setDataType("string");
		$requireOtherNumbers->setLength(5);
		$requireOtherNumbers->setRowTitle("number_del_notes");
		$myGroup->add($requireOtherNumbers);

		$requireExactQuantity = new checkbox("requireExactQuantity");
		$requireExactQuantity->setGroup("myGroup");
		$requireExactQuantity->setDataType("string");
		$requireExactQuantity->setLength(5);
		$requireExactQuantity->setRowTitle("quantity_non_conforming");
		$myGroup->add($requireExactQuantity);

		$requireExactDimensions = new checkbox("requireExactDimensions");
		$requireExactDimensions->setGroup("myGroup");
		$requireExactDimensions->setDataType("string");
		$requireExactDimensions->setLength(5);
		$requireExactDimensions->setRowTitle("exact_dimensions");
		$myGroup->add($requireExactDimensions);

		$requireSamples = new checkbox("requireSamples");
		$requireSamples->setGroup("myGroup");
		$requireSamples->setDataType("string");
		$requireSamples->setLength(5);
		$requireSamples->setRowTitle("samples_defect");
		$myGroup->add($requireSamples);

		$requireOther = new myRadio("requireOther");
		$requireOther->setArraySource(array(
				array('value' => 1, 'display' => 'yes'),
				array('value' => 0, 'display' => 'no')
			));
		$requireOther->setTranslate(true);
		$requireOther->setValue(0);
		$requireOther->setGroup("myGroup");
		$requireOther->setDataType("string");
		$requireOther->setLength(5);
		$requireOther->setRowTitle("other_as_specified");
			$requireOtherDependency = new dependency();
			$requireOtherDependency->addRule(
				new rule('myGroup', 'requireOther', 1));
			$requireOtherDependency->setGroup('otherGroup');
			$requireOtherDependency->setShow(true);
		$requireOther->addControllingDependency($requireOtherDependency);
		$myGroup->add($requireOther);
		
		$otherRequirements = new textarea("otherRequirements");
		$otherRequirements->setGroup("otherGroup");
		$otherRequirements->setDataType("text");
		$otherRequirements->setRowTitle("other_requirements");
		$otherRequirements->setLargeTextarea(true);
		$otherGroup->add($otherRequirements);

		$salesContainmentActions = new textarea("containmentActions");
		$salesContainmentActions->setGroup("myGroup2");
		$salesContainmentActions->setDataType("text");
		$salesContainmentActions->setLabel('Actions');
		$salesContainmentActions->setTable('evaluation');
		$salesContainmentActions->setRowTitle("sales_containment_actions");
		$salesContainmentActions->setLargeTextarea(true);
		$myGroup2->add($salesContainmentActions);

		$customerRequestedActions = new textarea("customerRequestedActions");
		$customerRequestedActions->setGroup("myGroup2");
		$customerRequestedActions->setDataType("text");
		$customerRequestedActions->setTable('complaint');
		$customerRequestedActions->setRowTitle("requested_action_customer");
		$customerRequestedActions->setLargeTextarea(true);
		$myGroup2->add($customerRequestedActions);

		// Customer Care (Not Sales)
		
		$salesName = new textbox("salesName");
		$salesName->setGroup("myGroup2");
		$salesName->setDataType("string");
		$salesName->setRowTitle("customer_care_person");
		$salesName->setLabel("undersigned");
		//$salesName->setHelpId(8027);
		$myGroup2->add($salesName);
		
		$salesTel = new textbox("salesTel");
		$salesTel->setGroup("myGroup2");
		$salesTel->setDataType("string");
		$salesTel->setRowTitle("telephone");
		//$salesTel->setHelpId(8027);
		$myGroup2->add($salesTel);
		
		$salesFax = new textbox("salesFax");
		$salesFax->setGroup("myGroup2");
		$salesFax->setDataType("string");
		$salesFax->setRowTitle("fax");
		//$salesFax->setHelpId(8027);
		$myGroup2->add($salesFax);
		
		$salesEmail = new textbox("salesEmail");
		$salesEmail->setGroup("myGroup2");
		$salesEmail->setDataType("string");
		$salesEmail->setRowTitle("e-mail");
		//$salesEmail->setHelpId(8027);
		$myGroup2->add($salesEmail);
		
		$siteAddress = new textarea("siteAddress");
		$siteAddress->setGroup("myGroup2");
		$siteAddress->setDataType("string");
		$siteAddress->setRowTitle("customer_care_office");
		//$siteAddress->setHelpId(8027);
		$myGroup2->add($siteAddress);
		
		$language = new dropdown("language");
		$language->setGroup("myGroup2");
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
		$myGroup2->add($language);
		
		$submit = new submit("submit");
		$submit->setGroup("myGroup2");
		$submit->setValue('Print PDF');
		$myGroup2->add($submit);
			
		$this->form->add($myGroup);
		$this->form->add($otherGroup);
		$this->form->add($myGroup2);
	}
}