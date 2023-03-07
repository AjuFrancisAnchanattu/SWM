<?php

/**
 * @package apps
 * @subpackage carhire
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 31/07/2006
 */
class index extends page
{

	private $form;	
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Car Hire');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/carhire/menu.xml");
		
		unset($_SESSION['apps'][$GLOBALS['app']]);
		
		$this->add_output("<carhireHome>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			
			if ($this->form->validate())
			{		
					$query = $this->form->generateInsertQuery('carhire');
					
					// Transform dates from database to human dates lol
					$this->form->get("startDate")->setValue(common::transformDateForPHP());
					$this->form->get("endDate")->setValue(common::transformDateForPHP());
					
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT into carhire " .  $query );
					
									
					$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM carhire ORDER BY id DESC LIMIT 1");
					$fields = mysql_fetch_array($dataset);
					$this->form->get("id")->setValue($fields['id']);
					
					// new action, email the owner
					$dom = new DomDocument;
					$dom->loadXML("<carHireSubmit><customerName>" . $fields['customerName'] . "</customerName><contactName>" . $fields['contactName'] . "</contactName><telNumber>" . $fields['telNumber'] . "</telNumber><flightNumber>" . $fields['flightNumber'] . "</flightNumber><insurance>" . $fields['insurance'] . "</insurance><accountCode>" . $fields['accountCode'] . "</accountCode><emailAddress>" . $fields['emailAddress'] . "</emailAddress><orderNumber>" . $fields['orderNumber'] . "</orderNumber><vehicleGroup>" . $fields['vehicleGroup'] . "</vehicleGroup><driversName>" . $fields['driversName'] . "</driversName><startDate>" . $fields['startDate'] . "</startDate><startTime>" . $fields['startTime'] . "</startTime><houseNumber>" . $fields['houseNumber'] . "</houseNumber><streetName>" . $fields['streetName'] . "</streetName><townCity>" . $fields['townCity'] . "</townCity><postcode>" . $fields['postcode'] . "</postcode><telNumberContactName>" . $fields['telNumberContactName'] . "</telNumberContactName><collectionType>" . $fields['collectionType'] . "</collectionType><endDate>" . $fields['endDate'] . "</endDate><endTime>" . $fields['endTime'] . "</endTime><isCollectionSameAsDelivery>" . $fields['isCollectionSameAsDelivery'] . "</isCollectionSameAsDelivery><additionalGroup>" . $fields['additionalGroup'] . "</additionalGroup><keysLeft>" . $fields['keysLeft'] . "</keysLeft><c_houseNo>" . $fields['c_houseNo'] . "</c_houseNo><c_streetName>" . $fields['c_streetName'] . "</c_streetName><c_postcode>" . $fields['c_postcode'] . "</c_postcode><c_townCity>" . $fields['c_townCity'] . "</c_townCity><c_telNumber>" . $fields['c_telNumber'] . "</c_telNumber></carHireSubmit>");
		
					// load xsl
					$xsl = new DomDocument;
					$xsl->load("./apps/carhire/xsl/email.xsl");
	
					// transform xml using xsl
					$proc = new xsltprocessor;
					$proc->importStyleSheet($xsl);
	
					$email = $proc->transformToXML($dom);
				
					email::send("sue.cooper@scapa.com", "intranet@scapa.com", (translate::getInstance()->translate("car_hire_submit")), $email);
					
					page::redirect('./'); // redirects to homepage
					
				
				
			}
			
		}
		
		// show form
		$this->add_output($this->form->output());
		$this->add_output("</carhireHome>");
		$this->output('./apps/carhire/xsl/carhire.xsl');
	}
	
	
	
	public function defineForm()
	{
		$today = date("d/m/Y",time());
		
		$this->form = new form("carhire");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$customerDetails = new group("customerDetails");
		$deliveryDetails = new group("deliveryDetails");
		$collectionTypeGroup = new group("collectionTypeGroup");
		$collectionDetails = new group("collectionDetails");
		$no = new group("no");
		$additionalInformation = new group("additionalInformation");
		

		$id = new textbox("id");
		$id->setTable("carhire");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$customerDetails->add($id);
		
		$customerName = new textbox("customerName");
		$customerName->setTable("carhire");
		$customerName->setLabel("Customer and Car Details");
		$customerName->setDataType("text");
		$customerName->setRowTitle("customer_name");
		$customerName->setValue("Scapa Tapes UK Ltd");
		$customerName->setRequired(false);
		$customerName->setHelpId(6000);
		$customerName->setVisible(true);
		$customerDetails->add($customerName);

		$contactName = new textbox("contactName");
		$contactName->setTable("carhire");
		$contactName->setDataType("text");
		$contactName->setRowTitle("contact_name");
		$contactName->setRequired(true);
		$contactName->setHelpId(6001);
		$contactName->setVisible(true);
		$customerDetails->add($contactName);
		
		$telNumber = new textbox("telNumber");
		$telNumber->setTable("carhire");
		$telNumber->setDataType("text");
		$telNumber->setRowTitle("tel_number");
		$telNumber->setRequired(false);
		$telNumber->setHelpId(6002);
		$telNumber->setVisible(true);
		$customerDetails->add($telNumber);
		
		$flightNumber = new textbox("flightNumber");
		$flightNumber->setTable("carhire");
		$flightNumber->setDataType("text");
		$flightNumber->setRowTitle("flight_number");
		$flightNumber->setRequired(false);
		$flightNumber->setHelpId(6003);
		$flightNumber->setVisible(true);
		$customerDetails->add($flightNumber);
		
		$insurance = new dropdown("insurance");
		$insurance->setTable("carhire");
		$insurance->setDataType("text");
		$insurance->setRowTitle("insurance");
		$insurance->setRequired(false);
		$insurance->setArraySource(array(
			array('value' => 'COI', 'display' => 'COI - Customers Own Insurance'),
			array('value' => 'COW', 'display' => 'COW - Hire Companies Insurance'))
		);
		$insurance->setHelpId(6004);
		$insurance->setVisible(true);
		$customerDetails->add($insurance);
		
		$accountCode = new textbox("accountCode");
		$accountCode->setTable("carhire");
		$accountCode->setDataType("text");
		$accountCode->setRowTitle("account_code");
		$accountCode->setRequired(false);
		$accountCode->setHelpId(6005);
		$accountCode->setVisible(true);
		$customerDetails->add($accountCode);
		
		$emailAddress = new textbox("emailAddress");
		$emailAddress->setTable("carhire");
		$emailAddress->setDataType("text");
		$emailAddress->setRowTitle("email_address");
		$emailAddress->setRequired(false);
		$emailAddress->setHelpId(6006);
		$emailAddress->setVisible(true);
		$customerDetails->add($emailAddress);
		
		$orderNumber = new textbox("orderNumber");
		$orderNumber->setTable("carhire");
		$orderNumber->setDataType("text");
		$orderNumber->setRowTitle("car_order_number");
		$orderNumber->setRequired(false);
		$orderNumber->setHelpId(6007);
		$orderNumber->setVisible(true);
		$customerDetails->add($orderNumber);
		
		$vehicleGroup = new textbox("vehicleGroup");
		$vehicleGroup->setTable("carhire");
		$vehicleGroup->setDataType("text");
		$vehicleGroup->setRowTitle("vehicle_group");
		$vehicleGroup->setRequired(false);
		$vehicleGroup->setValue("3 - 1600/1800cc Manual");
		$vehicleGroup->setHelpId(6008);
		$vehicleGroup->setVisible(true);
		$customerDetails->add($vehicleGroup);
		
		$driversName = new textbox("driversName");
		$driversName->setTable("carhire");
		$driversName->setDataType("text");
		$driversName->setRowTitle("driving_name");
		$driversName->setRequired(false);
		$driversName->setHelpId(6009);
		$driversName->setVisible(true);
		$customerDetails->add($driversName);
		
		
		$startDate = new textbox("startDate");
		$startDate->setTable("carhire");
		$startDate->setDataType("date");
		$startDate->setRowTitle("start_date");
		$startDate->setLabel("Delivery Details");
		$startDate->setRequired(false);
		$startDate->setHelpId(6010);
		$startDate->setVisible(true);
		$deliveryDetails->add($startDate);
		
		$startTime = new textbox("startTime");
		$startTime->setTable("carhire");
		$startTime->setDataType("text");
		$startTime->setRowTitle("start_time");
		$startTime->setRequired(false);
		$startTime->setHelpId(6011);
		$startTime->setVisible(true);
		$deliveryDetails->add($startTime);
		
		$houseNumber = new textbox("houseNumber");
		$houseNumber->setTable("carhire");
		$houseNumber->setDataType("text");
		$houseNumber->setRowTitle("house_number");
		$houseNumber->setRequired(false);
		$houseNumber->setHelpId(6012);
		$houseNumber->setVisible(true);
		$deliveryDetails->add($houseNumber);
		
		$streetName = new textbox("streetName");
		$streetName->setTable("carhire");
		$streetName->setDataType("text");
		$streetName->setRowTitle("street_name");
		$streetName->setRequired(false);
		$streetName->setHelpId(6013);
		$streetName->setVisible(true);
		$deliveryDetails->add($streetName);
		
		$townCity = new textbox("townCity");
		$townCity->setTable("carhire");
		$townCity->setDataType("text");
		$townCity->setRowTitle("town_city");
		$townCity->setRequired(false);
		$townCity->setHelpId(6014);
		$townCity->setVisible(true);
		$deliveryDetails->add($townCity);
		
		$postcode = new textbox("postcode");
		$postcode->setTable("carhire");
		$postcode->setDataType("text");
		$postcode->setRowTitle("postcode");
		$postcode->setRequired(false);
		$postcode->setHelpId(6015);
		$postcode->setVisible(true);
		$deliveryDetails->add($postcode);
		
		$telNumberContactName = new textbox("telNumberContactName");
		$telNumberContactName->setTable("carhire");
		$telNumberContactName->setDataType("text");
		$telNumberContactName->setRowTitle("tel_number_contact_name");
		$telNumberContactName->setRequired(false);
		$telNumberContactName->setHelpId(6016);
		$telNumberContactName->setVisible(true);
		$deliveryDetails->add($telNumberContactName);
		
		
		$collectionType = new dropdown("collectionType");
		$collectionType->setTable("carhire");
		$collectionType->setDataType("text");
		$collectionType->setRowTitle("collection_type");
		$collectionType->setRequired(false);
		$collectionType->setLabel("Collection Type");
		$collectionType->setArraySource(array(
			array('value' => 'APU', 'display' => 'APU - Collection From Home or Company Address'),
			array('value' => 'ADO', 'display' => 'ADO - Drop of at Airport or Branch'),
			array('value' => 'TBA', 'display' => 'TBA - To Be Advised'))
		);
		$collectionType->setHelpId(6017);
		$collectionType->setVisible(true);
		$collectionTypeGroup->add($collectionType);
		
		
		$endDate = new textbox("endDate");
		$endDate->setTable("carhire");
		$endDate->setDataType("date");
		$endDate->setRowTitle("end_date");
		$endDate->setRequired(false);
		$endDate->setLabel("Collection Details");
		$endDate->setHelpId(6018);
		$endDate->setVisible(true);
		$collectionDetails->add($endDate);
		
		$endTime = new textbox("endTime");
		$endTime->setTable("carhire");
		$endTime->setDataType("text");
		$endTime->setRowTitle("end_time");
		$endTime->setRequired(false);
		$endTime->setHelpId(6019);
		$endTime->setVisible(true);
		$collectionDetails->add($endTime);
		
		$isCollectionSameAsDelivery = new radio("isCollectionSameAsDelivery");
		$isCollectionSameAsDelivery->setTable("carhire");
		$isCollectionSameAsDelivery->setDataType("text");
		$isCollectionSameAsDelivery->setRowTitle("is_collection_same_as_delivery");
		$isCollectionSameAsDelivery->setValue("no");
		$isCollectionSameAsDelivery->setRequired(false);
		$isCollectionSameAsDelivery->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No'))
		);
		$isCollectionSameAsDelivery->setHelpId(6020);
		$isCollectionSameAsDelivery->setVisible(true);
		
		
		$no_collectionSameAsDelivery = new dependency();
		$no_collectionSameAsDelivery->addRule(new rule('collectionDetails', 'isCollectionSameAsDelivery', 'no'));
		$no_collectionSameAsDelivery->setGroup('no');
		$no_collectionSameAsDelivery->setShow(true);
		
		$isCollectionSameAsDelivery->addControllingDependency($no_collectionSameAsDelivery);
		
		$collectionDetails->add($isCollectionSameAsDelivery);
		
		$c_houseNo = new textbox("c_houseNo");
		$c_houseNo->setGroup("yes");
		$c_houseNo->setTable("carhire");		
		$c_houseNo->setRequired(false);
		$c_houseNo->setLabel("Further Collection Details");
		$c_houseNo->setDataType("text");
		$c_houseNo->setRowTitle("house_number");
		$no->add($c_houseNo);
		
		$c_streetName = new textbox("c_streetName");
		$c_streetName->setGroup("yes");
		$c_streetName->setTable("carhire");		
		$c_streetName->setRequired(false);
		$c_streetName->setDataType("text");
		$c_streetName->setRowTitle("street_name");
		$no->add($c_streetName);
		
		$c_townCity = new textbox("c_townCity");
		$c_townCity->setGroup("yes");
		$c_townCity->setTable("carhire");		
		$c_townCity->setRequired(false);
		$c_townCity->setDataType("text");
		$c_townCity->setRowTitle("town_city");
		$no->add($c_townCity);
		
		$c_postcode = new textbox("c_postcode");
		$c_postcode->setGroup("yes");
		$c_postcode->setTable("carhire");		
		$c_postcode->setRequired(false);
		$c_postcode->setDataType("text");
		$c_postcode->setRowTitle("postcode");
		$no->add($c_postcode);
		
		$c_telNumber = new textbox("c_telNumber");
		$c_telNumber->setGroup("yes");
		$c_telNumber->setTable("carhire");		
		$c_telNumber->setRequired(false);
		$c_telNumber->setDataType("text");
		$c_telNumber->setRowTitle("tel_number");
		$no->add($c_telNumber);
		
		
		
		
		$additionalGroup = new textarea("additionalGroup");
		$additionalGroup->setTable("carhire");
		$additionalGroup->setLabel("Additional Information");
		$additionalGroup->setDataType("text");
		$additionalGroup->setRowTitle("additional_information");
		$additionalGroup->setRequired(false);
		$additionalGroup->setHelpId(6021);
		$additionalGroup->setVisible(true);
		$additionalInformation->add($additionalGroup);
		
		$keysLeft = new textbox("keysLeft");
		$keysLeft->setTable("carhire");
		$keysLeft->setDataType("text");
		$keysLeft->setRowTitle("keys_left");
		$keysLeft->setRequired(false);
		$keysLeft->setHelpId(6022);
		$keysLeft->setVisible(true);
		$additionalInformation->add($keysLeft);
		
		
		$submit = new submit("submit");
		$additionalInformation->add($submit);
		
		
		$this->form->add($customerDetails);
		$this->form->add($deliveryDetails);
		$this->form->add($collectionTypeGroup);
		$this->form->add($collectionDetails);
		$this->form->add($no);
		$this->form->add($additionalInformation);

		
	}
	
	function setFormValues()
	{
		if ($_REQUEST['mode'] == "add")
		{
			$today = date("d/m/Y",time());
			
			$this->form->get("id")->setValue($_GET['id']);
			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
		}
	}
}

?>