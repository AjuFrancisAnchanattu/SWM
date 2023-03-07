<?php
/**
 * Edit the Acknowledgement data before generating a pdf
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 01/03/2011
 */

class edit_Disposal_Note
{
	public $pdfType = "disposal_note";
	
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
		
		$this->form->get("toTheAttention2")->setValue( sapCustomer::getName($complaint['sapCustomerNo']) );
		$this->form->get("complaintDate")->setValue( $complaint['complaintDate'] );
		$this->form->get("problemDescription")->setValue( $complaint['problemDescription'] );
	}
	
	private function defineForm()
	{
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$myGroup = new group("myGroup");
		
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