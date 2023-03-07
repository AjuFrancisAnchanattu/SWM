<?php

class edit_Return_Request
{
	protected $pdfType = "return_request";

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
		$this->form->get("customerName")->setValue( sapCustomer::getName($complaint['sapCustomerNo']) );
	}
	
	private function defineForm()
	{
		$this->form = new form($this->formName);
		$this->form->setStoreInSession(true);
		
		$myGroup = new group("myGroup");
		
		$toTheAttention = new textbox("toTheAttention");
		$toTheAttention->setGroup("myGroup");
		$toTheAttention->setDataType("string");
		$toTheAttention->setRowTitle("to_attention");
		//$toTheAttention->setHelpId(8027);
		$myGroup->add($toTheAttention);
		
		$toTheAttention2 = new textbox("toTheAttention2");
		$toTheAttention2->setGroup("myGroup");
		$toTheAttention2->setDataType("string");
		$toTheAttention2->setRowTitle("sap_customer_name");
		//$toTheAttention2->setHelpId(8027);
		$myGroup->add($toTheAttention2);
		
		$sapReturn = new textbox("sapReturn");
		$sapReturn->setGroup("myGroup");
		$sapReturn->setDataType("string");
		$sapReturn->setRowTitle("sap_return_no");
		//$sapReturn->setHelpId(8027);
		$myGroup->add($sapReturn);
		
		$customerName = new textbox("customerName");
		$customerName->setGroup("myGroup");
		$customerName->setDataType("string");
		$customerName->setRowTitle("customer_name");
		//$customerName->setHelpId(8027);
		$myGroup->add($customerName);
		
		$contactNumber = new textbox("contactNumber");
		$contactNumber->setGroup("myGroup");
		$contactNumber->setDataType("string");
		$contactNumber->setRowTitle("contact_number");
		//$contactNumber->setHelpId(8027);
		$myGroup->add($contactNumber);
		
		$contactName = new textbox("contactName");
		$contactName->setGroup("myGroup");
		$contactName->setDataType("string");
		$contactName->setRowTitle("contact_name");
		//$contactName->setHelpId(8027);
		$myGroup->add($contactName);
		
		$collectionAddress = new textarea("collectionAddress");
		$collectionAddress->setGroup("myGroup");
		$collectionAddress->setRowTitle("collection_address");
		$collectionAddress->setLabel("collection_address");
		//$collectionAddress->setHelpId(8027);
		$myGroup->add($collectionAddress);
		
		$material = new textbox("material");
		$material->setGroup("myGroup");
		$material->setDataType("string");
		$material->setRowTitle("material");
		//$material->setHelpId(8027);
		$myGroup->add($material);
		
		$weight = new textbox("weight");
		$weight->setGroup("myGroup");
		$weight->setDataType("string");
		$weight->setRowTitle("weight");
		//$weight->setHelpId(8027);
		$myGroup->add($weight);
		
		$presentation = new textbox("presentation");
		$presentation->setGroup("myGroup");
		$presentation->setDataType("string");
		$presentation->setRowTitle("presentation");
		//$presentation->setHelpId(8027);
		$myGroup->add($presentation);
		
		$comments = new textarea("comments");
		$comments->setGroup("myGroup");
		$comments->setRowTitle("problem_description");
		$comments->setLabel("comments");
		$comments->setLargeTextarea(true);
		//$comments->setHelpId(8027);
		$myGroup->add($comments);
		
		$transportChargedTo = new radio("transportChargedTo");
		$transportChargedTo->setGroup("mayGroup");
		$transportChargedTo->setDataType("string");
		$transportChargedTo->setLength(255);
		$transportChargedTo->setRowTitle("transport_charged_to");
		$transportChargedTo->setRequired(false);
		$transportChargedTo->setArraySource(
			array(
				array('display' => 'scapa', 'value' => 1),
				array('display' => 'customer', 'value' => 0),
				array('display' => 'other', 'value' => -1)
			)
		);
		$transportChargedTo->setTranslate(true);
		//$transportChargedTo->setHelpId(8027);
		$myGroup->add($transportChargedTo);
		
		$otherName = new textbox("otherName");
		$otherName->setGroup("myGroup");
		$otherName->setDataType("string");
		$otherName->setRowTitle("name");
		//$otherName->setHelpId(8027);
		$myGroup->add($otherName);
		
		
		$requestedBy = new textbox("requestedBy");
		$requestedBy->setGroup("myGroup");
		$requestedBy->setDataType("string");
		$requestedBy->setRowTitle("requested_by");
		//$requestedBy->setHelpId(8027);
		$myGroup->add($requestedBy);
		
		$requestedDate = new myCalendar("requestedDate");
		$requestedDate->setGroup("myGroup");
		$requestedDate->setRowTitle("date");
		//$requestedDate->setHelpId(8027);
		$myGroup->add($requestedDate);
		
		$authorisedBy = new textbox("authorisedBy");
		$authorisedBy->setGroup("myGroup");
		$authorisedBy->setDataType("string");
		$authorisedBy->setRowTitle("authorised_by");
		//$authorisedBy->setHelpId(8027);
		$myGroup->add($authorisedBy);
		
		$authorisedDate = new textbox("authorisedDate");
		$authorisedDate->setGroup("myGroup");
		$authorisedDate->setDataType("string");
		$authorisedDate->setRowTitle("date");
		//$authorisedDate->setHelpId(8027);
		$myGroup->add($authorisedDate);
		
		
		//*** 1
		$transportNo = new textbox("transportNo");
		$transportNo->setGroup("myGroup");
		$transportNo->setDataType("string");
		$transportNo->setRowTitle("special_transport_authorisation_no");
		$transportNo->setLabel("shipping");
		//$transportNo->setHelpId(8027);
		$myGroup->add($transportNo);
		
		$methodOfTransport = new textbox("methodOfTransport");
		$methodOfTransport->setGroup("myGroup");
		$methodOfTransport->setDataType("string");
		$methodOfTransport->setRowTitle("method_of_transport");
		//$methodOfTransport->setHelpId(8027);
		$myGroup->add($methodOfTransport);
		
		$transportCost = new textbox("transportCost");
		$transportCost->setGroup("myGroup");
		$transportCost->setDataType("string");
		$transportCost->setRowTitle("transport_costs");
		//$transportCost->setHelpId(8027);
		$myGroup->add($transportCost);
		
		$shippingSignature = new textbox("shippingSignature");
		$shippingSignature->setGroup("myGroup");
		$shippingSignature->setDataType("string");
		$shippingSignature->setRowTitle("signature");
		//$shippingSignature->setHelpId(8027);
		$myGroup->add($shippingSignature);
		
		$goodsRecievedNo = new textbox("goodsRecievedNo");
		$goodsRecievedNo->setGroup("myGroup");
		$goodsRecievedNo->setDataType("string");
		$goodsRecievedNo->setRowTitle("goods_recieved_no");
		//$goodsRecievedNo->setHelpId(8027);
		$myGroup->add($goodsRecievedNo);
		
		$quantityReturned = new textbox("quantityReturned");
		$quantityReturned->setGroup("myGroup");
		$quantityReturned->setDataType("string");
		$quantityReturned->setRowTitle("quantity_returned");
		//$quantityReturned->setHelpId(8027);
		$myGroup->add($quantityReturned);
		
		$shippingSignedForBy = new textbox("shippingSignedForBy");
		$shippingSignedForBy->setGroup("myGroup");
		$shippingSignedForBy->setDataType("string");
		$shippingSignedForBy->setRowTitle("signed_for_by");
		//$shippingSignedForBy->setHelpId(8027);
		$myGroup->add($shippingSignedForBy);
		
		$shippingSignedForDate = new myCalendar("shippingSignedForDate");
		$shippingSignedForDate->setGroup("myGroup");
		$shippingSignedForDate->setRowTitle("date");
		//$shippingSignedForDate->setHelpId(8027);
		$myGroup->add($shippingSignedForDate);
		
		//*** Section 2 of PDF
		
		$scrap = new checkbox("scrap");
		$scrap->setGroup("myGroup");
		$scrap->setDataType("string");
		$scrap->setLength(255);
		$scrap->setRowTitle("scrap");
		$scrap->setLabel("quality");
		$scrap->setRequired(false);
		$myGroup->add($scrap);
		
		$disposalInstruction = new textarea("disposalInstruction");
		$disposalInstruction->setGroup("myGroup");
		$disposalInstruction->setRowTitle("disposal_instruction");
		$disposalInstruction->setLargeTextarea(true);
		//$disposalInstruction->setHelpId(8027);
		$myGroup->add($disposalInstruction);
		
		$rework = new checkbox("rework");
		$rework->setGroup("myGroup");
		$rework->setDataType("string");
		$rework->setLength(255);
		$rework->setRowTitle("rework");
		$rework->setRequired(false);
		$myGroup->add($rework);
		
		$reworkInstruction = new textarea("reworkInstruction");
		$reworkInstruction->setGroup("myGroup");
		$reworkInstruction->setRowTitle("rework_instruction");
		$reworkInstruction->setLargeTextarea(true);
		//$reworkInstruction->setHelpId(8027);
		$myGroup->add($reworkInstruction);
		
		
		$adminCost = new textbox("adminCost");
		$adminCost->setGroup("myGroup");
		$adminCost->setDataType("string");
		$adminCost->setRowTitle("admin_cost");
		//$adminCost->setHelpId(8027);
		$myGroup->add($adminCost);
		
		$reworkCost = new textbox("reworkCost");
		$reworkCost->setGroup("myGroup");
		$reworkCost->setDataType("string");
		$reworkCost->setRowTitle("rework_cost");
		//$reworkCost->setHelpId(8027);
		$myGroup->add($reworkCost);
		
		$otherCost = new textbox("otherCost");
		$otherCost->setGroup("myGroup");
		$otherCost->setDataType("string");
		$otherCost->setRowTitle("cost");
		//$otherCost->setHelpId(8027);
		$myGroup->add($otherCost);
		
		$qualitySignature = new textbox("qualitySignature");
		$qualitySignature->setGroup("myGroup");
		$qualitySignature->setDataType("string");
		$qualitySignature->setRowTitle("signature");
		//$qualitySignature->setHelpId(8027);
		$myGroup->add($qualitySignature);
		
		$qualitySignedFor = new textbox("qualitySignedFor");
		$qualitySignedFor->setGroup("myGroup");
		$qualitySignedFor->setDataType("string");
		$qualitySignedFor->setRowTitle("signed_for_by");
		//$qualitySignedFor->setHelpId(8027);
		$myGroup->add($qualitySignedFor);
		
		$qualitySignedForDate = new myCalendar("qualitySignedForDate");
		$qualitySignedForDate->setGroup("myGroup");
		$qualitySignedForDate->setRowTitle("date");
		//$qualitySignedForDate->setHelpId(8027);
		$myGroup->add($qualitySignedForDate);
		
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