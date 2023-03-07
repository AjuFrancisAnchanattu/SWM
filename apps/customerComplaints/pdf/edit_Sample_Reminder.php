<?php
/**
 * Edit the Acknowledgement data before generating a pdf
 *
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Daniel Gruszczyk
 * @version 01/03/2011
 */

class edit_Sample_Reminder
{
	protected $pdfType = "sample_reminder";
	
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
		$sql = "SELECT *, CONCAT( DATEDIFF(NOW(), complaintDate), ' day(s)') AS dateDiff FROM complaint WHERE id=" . $this->complaintId;
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$complaint = mysql_fetch_array($dataset);
		
		$this->form->get("toTheAttention2")->setValue( sapCustomer::getName($complaint['sapCustomerNo']) );
		$this->form->get("complaintDate")->setValue( $complaint['complaintDate'] );
		$this->form->get("problemDescription")->setValue( $complaint['problemDescription'] );
		$this->form->get("dateDiff")->setValue( $complaint['dateDiff'] );
		
		
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
		
		$comment = new textarea("comment");
		$comment->setGroup("myGroup");
		$comment->setDataType("text");
		$comment->setRowTitle("comment");
		$comment->setLabel("comment");
		$comment->setLargeTextarea(true);
		//$comment->setHelpId(8027);
		$myGroup->add($comment);
		
		$dateDiff = new textbox("dateDiff");
		$dateDiff->setGroup("myGroup");
		$dateDiff->setLength(20);
		$dateDiff->setDataType("string");
		$dateDiff->setRowTitle("days_complaint_open");
		//$dateDiff->setHelpId(8027);
		$myGroup->add($dateDiff);
		
		$salesName = new textbox("salesName");
		$salesName->setGroup("myGroup");
		$salesName->setDataType("string");
		$salesName->setRowTitle("sales_name");
		$salesName->setLabel("undersigned");
		//$salesName->setHelpId(8027);
		$myGroup->add($salesName);
		
		$salesTel = new textbox("salesTel");
		$salesTel->setGroup("myGroup");
		$salesTel->setDataType("string");
		$salesTel->setRowTitle("telephone");
		//$salesTel->setHelpId(8027);
		$myGroup->add($salesTel);
		
		$salesFax = new textbox("salesFax");
		$salesFax->setGroup("myGroup");
		$salesFax->setDataType("string");
		$salesFax->setRowTitle("fax");
		//$salesFax->setHelpId(8027);
		$myGroup->add($salesFax);
		
		$salesEmail = new textbox("salesEmail");
		$salesEmail->setGroup("myGroup");
		$salesEmail->setDataType("string");
		$salesEmail->setRowTitle("e-mail");
		//$salesEmail->setHelpId(8027);
		$myGroup->add($salesEmail);
		
		$siteAddress = new textarea("siteAddress");
		$siteAddress->setGroup("myGroup");
		$siteAddress->setDataType("string");
		$siteAddress->setRowTitle("sales_office");
		//$siteAddress->setHelpId(8027);
		$myGroup->add($siteAddress);
		
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