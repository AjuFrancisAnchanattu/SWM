<?php

class purchasing extends ijfProcess 
{	

	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		
		$this->form->get('ijfId')->setValue($this->ijf->getId());
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['purchasing']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database
		}
	
		$this->form->processDependencies();
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = " . $this->ijf->getId());
		$fields = mysql_fetch_array($dataset);
		
		$this->form->get('commodityCode')->setValue($fields['commodityCode']);
			

		
	}
	
	
	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM purchasing INNER JOIN ijf ON purchasing.ijfId = ijf.id WHERE ijfId = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['purchasing']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);


			// $this->form->get("sold_value")->setValue(array($fields['sold_value_quantity'], $fields['sold_value_measurement']));
			
			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
		
			$this->form->get('updatedDate')->setValue(page::transformDateForPHP($this->form->get('updatedDate')->getValue()));
			
			$this->form->putValuesInSession();

			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['purchasing']);
			return false;
		}
	}
	
	public function complete()
	{
		
	}
	
	public function getMoq()
	{
		return $this->form->get("moq")->getValue();
	}
	
	
	
	public function save()
	{	
		$this->determineStatus();
		
		if ($this->loadedFromDatabase)
		{
			$this->getIJF()->form->get("updatedDate")->setValue(common::nowDateForMysql());
					
			$this->getIJF()->form->get("initialSubmissionDate")->setIgnore(true);
			
			$this->form->get("owner")->setValue($this->form->get("purchasing_owner")->getValue());
			
			//die("UPDATE purchasing " . $this->form->generateUpdateQuery("purchasing") . " WHERE ijfId='" . $this->getIJF()->getID() . "'");
			
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE purchasing " . $this->form->generateUpdateQuery("purchasing") . " WHERE ijfId='" . $this->getIJF()->getID() . "'");
			
			
			
			// save new data
			
			
			$this->addLog("Purchasing report updated");
			
			// Send Email
			$this->getEmailNotification("purchasing", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			//$this->getEmailNotification("purchasing_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
		}
		else 
		{
			
			$this->form->get("owner")->setValue($this->form->get("purchasing_owner")->getValue());
					
			// set report date
			$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
			
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];

			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO purchasing " . $this->form->generateInsertQuery("purchasing"));
			
			$this->addLog(translate::getInstance()->translate("purchasing_report_completed"));
			
			// Send Email
			$this->getEmailNotification("purchasing", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			//$this->getEmailNotification("purchasing_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
			
			//if ($this->status == 'complete')
			//{
			//	$this->addLog(translate::getInstance()->translate("ijf_completed_sold_to_supplier"));
			//}
			//else 
			//{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");
			//}
			
			

			
		}
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
		
		//page::redirect("/apps/ijf/");
	}
	

	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate, comment) VALUES (%u, '%s', '%s', '%s', '%s')",
			$this->getIJF()->form->get("id")->getValue(),
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql(),
			$this->form->get("email_text")->getValue()
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
			$this->form->get('status')->setVisible(true);
			$this->form->get('completionDate')->setVisible(true);
			
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
			{
				$this->form->get('finalComments')->setVisible(true);
			}
		}
		
		if ($outputType == "normal")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
			{
				$this->form->get('finalComments')->setVisible(true);
			}
		}
	}

	public function determineStatus()
	{
		if ($_REQUEST['location_owner'])
		{
			$location = $_REQUEST['location_owner'];
				$this->status = $location;
				$this->form->get('status')->setValue($location);
		}
		
		//if ($this->form->get("sold_to_supplier")->getValue() == 'no')
		//{
		//	$this->status = 'production';
		//	$this->form->get('status')->setValue("production");
		//}
		//else 
		//{
		//	$this->status = 'complete';
		//	$this->form->get('status')->setValue("complete");
		//}
	}

	public function defineForm()
	{
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("purchasing");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		$purchasing = new group("purchasing");
		$sapPartNumberGroup = new group("sapPartNumberGroup");
		$sendToUser = new group("sendToUser");
		$ijf = new group("ijf");

		
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setLength(10);
		$ijfId->setTable("purchasing");
		$ijfId->setRowTitle("ijfId");
		$ijfId->setRequired(false);
		$ijfId->setVisible(false);
		$ijfId->setValue(0);
		$ijf->add($ijfId);
		
		$updatedDate = new textbox("updatedDate");
		$updatedDate->setValue($today);
		$updatedDate->setTable("ijf");
		$updatedDate->setVisible(false);
		$ijf->add($updatedDate);
		
		
		$puSapPartNumber = new textbox("puSapPartNumber");
		$puSapPartNumber->setLength(240);
		$puSapPartNumber->setTable("ijf");
		$puSapPartNumber->setLabel("SAP Part Number");
		$puSapPartNumber->setRowTitle("sap_part_number");
		$puSapPartNumber->setRequired(false);
		$puSapPartNumber->setVisible(true);
		$puSapPartNumber->setHelpId(2106);
		$sapPartNumberGroup->add($puSapPartNumber);
		
		
		
		$description = new textbox("description");
		$description->setTable("purchasing");
		$description->setVisible(true);
		$description->setGroup("purchasing");
		$description->setLabel("Details");
		$description->setDataType("string");
		$description->setRequired(false);
		$description->setHelpId(2107);
		$description->setRowTitle("description");
		$purchasing->add($description);
		
	///////////////////////
		$datasetProd = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM production WHERE ijfId = '" . $this->getIJF()->form->get("id")->getValue() . "'");
		$fieldsProd = mysql_fetch_array($datasetProd);
		
		$countryOfOrigin = new textbox("countryOfOrigin");
		$countryOfOrigin->setTable("purchasing");
		$fieldsProd['newItemToBePurchased'] == "yes" ? $countryOfOrigin->setVisible(true) : $countryOfOrigin->setVisible(false);
		$countryOfOrigin->setGroup("purchasing");
		$countryOfOrigin->setDataType("string");
		$countryOfOrigin->setRequired(false);
		$countryOfOrigin->setHelpId(2108);
		$countryOfOrigin->setRowTitle("country_of_origin");
		$purchasing->add($countryOfOrigin);
	///////////////////////

		$moq = new textbox("moq");
		$moq->setTable("ijf");
		$moq->setVisible(true);
		$moq->setGroup("purchasing");
		$moq->setDataType("string");
		$moq->setRequired(false);
		$moq->setHelpId(2109);
		$moq->setRowTitle("moq");
		$purchasing->add($moq);
		
		$commodityCode = new textbox("commodityCode");
		$commodityCode->setTable("ijf");
		$commodityCode->setVisible(true);
		$commodityCode->setGroup("ijf");
		$commodityCode->setDataType("string");
		$commodityCode->setRequired(false);
		$commodityCode->setHelpId(2110);
		$commodityCode->setRowTitle("commodity_code");
		$purchasing->add($commodityCode);

		$commodityCodeCountry = new textbox("commodityCodeCountry");
		$commodityCodeCountry->setTable("purchasing");
		$commodityCodeCountry->setVisible(true);
		$commodityCodeCountry->setGroup("purchasing");
		$commodityCodeCountry->setDataType("string");
		$commodityCodeCountry->setRequired(false);
		$commodityCodeCountry->setHelpId(2111);
		$commodityCodeCountry->setRowTitle("commodity_code_country");
		$purchasing->add($commodityCodeCountry);
		
		$leadTime = new textbox("leadTime");
		$leadTime->setTable("purchasing");
		$leadTime->setVisible(true);
		$leadTime->setGroup("purchasing");
		$leadTime->setDataType("string");
		$leadTime->setRequired(false);
		$leadTime->setHelpId(2112);
		$leadTime->setRowTitle("lead_time");
		$purchasing->add($leadTime);
		
		$price = new textbox("price");
		$price->setTable("purchasing");
		$price->setVisible(true);
		$price->setGroup("purchasing");
		$price->setDataType("string");
		$price->setHelpId(2113);
		$price->setRequired(false);
		$price->setRowTitle("price");
		$purchasing->add($price);
		
		
		
		$currencyPurchasing = new dropdown("currencyPurchasing");
		$currencyPurchasing->setTable("purchasing");
		$currencyPurchasing->setVisible(true);
		$currencyPurchasing->setGroup("purchasing");
		$currencyPurchasing->setDataType("string");
		$currencyPurchasing->setXMLSource("./apps/ijf/xml/currency.xml");
		$currencyPurchasing->setRequired(false);
		$currencyPurchasing->setHelpId(2056);
		$currencyPurchasing->setRowTitle("currency");
		$purchasing->add($currencyPurchasing);
		
		$freightDutyInformation = new textbox("freightDutyInformation");
		$freightDutyInformation->setTable("purchasing");
		$freightDutyInformation->setVisible(true);
		$freightDutyInformation->setGroup("purchasing");
		$freightDutyInformation->setDataType("string");
		$freightDutyInformation->setHelpId(2114);
		$freightDutyInformation->setRequired(false);
		$freightDutyInformation->setRowTitle("freight_duty_information");
		$purchasing->add($freightDutyInformation);
		
		
		
		$comments = new textarea("comments");
		$comments->setLength(240);
		$comments->setTable("purchasing");
		$comments->setRowTitle("comments");
		$comments->setDataType("text");
		$comments->setRequired(false);
		$comments->setHelpId(2115);
		$comments->setVisible(true);
		$purchasing->add($comments);
		
		
	
		
		
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("sendToUser");
		$location_owner->setLength(250);
		$location_owner->setTable("purchasing");
		$location_owner->setRowTitle("send_ijf_to_location");
		$location_owner->setLabel("User Delivery Options");
		$location_owner->setRequired(true);
		$location_owner->setHelpId(2098);
		$location_owner->setXMLSource("./apps/ijf/xml/departments.xml");
		$location_owner->setValue("finance");
		$sendToUser->add($location_owner);
		
		$purchasing_owner = new dropdown("purchasing_owner");
		$purchasing_owner->setLength(250);
		$purchasing_owner->setTable("purchasing");
		$purchasing_owner->setRowTitle("ijf_owner");
		$purchasing_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$purchasing_owner->setRequired(true);
		$purchasing_owner->setHelpId(2099);
		$purchasing_owner->setVisible(true);
		$sendToUser->add($purchasing_owner);
		
		$owner = new dropdown("owner");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->setRowTitle("ijf_owner");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$owner->setRequired(false);
		$owner->setVisible(false);
		$sendToUser->add($owner);
		
		$status = new textbox("status");
		$status->setLength(250);
		$status->setTable("ijf");
		$status->setVisible(false);
		$status->setValue("initiation");
		$status->setRowTitle("status");
		$status->setRequired(false);
		$sendToUser->add($status);
		
		$delegate_owner = new multipleCC("delegate_owner");
		$delegate_owner->setGroup("sendToUser");
		$delegate_owner->setLength(250);
		$delegate_owner->setTable("purchasing");
		$delegate_owner->setDataType("text");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2010);
		$sendToUser->add($delegate_owner);
		
		$email_text = new textarea("email_text");
		$email_text->setGroup("sendToUser");
		$email_text->setDataType("text");
		$email_text->setTable("purchasing");
		$email_text->setRowTitle("email_text");
		$email_text->setHelpId(2105);
		$email_text->setTable("ijf");
		$sendToUser->add($email_text);
		
		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);	
		
		
		
		
		$this->form->add($ijf);
		$this->form->add($sapPartNumberGroup);
		$this->form->add($purchasing);	
		$this->form->add($sendToUser);	
		
	}

	public function getEmailNotification($action, $id, $status, $owner, $sender, $email_text)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><status>" . $status . "</status><action>" . $id . "</action><completionDate>" . common::transformDateForPHP($this->ijf->form->get("ijfDueDate")->getValue()) . "</completionDate><email_text>" . $this->form->get("email_text")->getValue() . "</email_text><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailSectionName>" . translate::getInstance()->translate($status) . "</emailSectionName><email_text>" . utf8_decode($email_text) . "</email_text></$action>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		//$cc = $this->form->get("delegate_owner")->getValue();
	
		$subjectText = (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $id);

		if($owner == "marie.jamieson@scapa.com" || $owner == "Owais.Hassan@scapa.com" || $owner == "alexandra.harrison@scapa.com")
		{
			$cc = "European.FinanceCostings@scapa.com";
		}
		else
		{
			$cc = $this->form->get("delegate_owner")->getValue();
		}
		
		//if($action == "purchasing_cc") $subjectText = "CC - " . $subjectText;
	
		email::send($owner, /*"intranet@scapa.com"*/ $sender, $subjectText, "$email", "$cc");
		
		return true;
	}
}

?>