<?php

class productManager extends ijfProcess 
{	
	

	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		//$this->defineForm();
		
		$this->form->get('ijfId')->setValue($this->ijf->getId());
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['productManager']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database
		}
		
		//echo nl2br($this->form->get("inputMaterialRequired")->getValue());
		
		//die();
	
		$this->form->processDependencies();
	}

		
	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM productManager INNER JOIN ijf ON productManager.ijfId=ijf.id WHERE ijfId = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			
			page::addDebug("sdfsdfsdfsdf", __FILE__, __LINE__);
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['productManager']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			//var_dump($fields);
			
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
			unset($_SESSION['apps'][$GLOBALS['app']]['productManager']);
			return false;
		}
	}
	
	public function complete()
	{
		
	}
	
	
	
	public function save()
	{	
		
		$this->determineStatus();
		
		if ($this->loadedFromDatabase)
		{
			$this->getIJF()->form->get("updatedDate")->setValue(common::nowDateForMysql());
					
			$this->getIJF()->form->get("initialSubmissionDate")->setIgnore(true);
			
			$this->form->get("owner")->setValue($this->form->get("productManager_owner")->getValue());
			
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE productManager " . $this->form->generateUpdateQuery("productManager") . " WHERE ijfId='" . $this->getIJF()->getID() . "'");
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			
			// save new data
			
			$this->addLog(translate::getInstance()->translate("product_manager_report_updated"));
			
			//Send Email
			$this->getEmailNotification("productManager", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("production_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
		}
		else 
		{
		
			$this->form->get("owner")->setValue($this->form->get("productManager_owner")->getValue());
			
			// set report date
			$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
			
			
			// begin transaction
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// insert
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO productManager " . $this->form->generateInsertQuery("productManager"));
			
			
			$this->addLog(translate::getInstance()->translate("product_manager_report_added"));
			
			$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");				
			
			// Send Email
			$this->getEmailNotification("productManager", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("production_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
		}
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
//			$this->form->get('status')->setVisible(true);
//			$this->form->get('completionDate')->setVisible(true);
//			
//			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
//			{
//				$this->form->get('finalComments')->setVisible(true);
//			}
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
		
		//if ($this->form->get("pass_inspection")->getValue() == 'no')
		//{
		//	$this->status = 'complete';
		//	$this->form->get('status')->setValue("complete");
		//}
		//else 
		//{
		//	$this->status = 'demandPlanning';
		//	$this->form->get('status')->setValue("demandPlanning");
		//}
	}
	

	public function defineForm()
	{
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("productManager");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$ijf = new group("ijf");
		$sentTo = new group("sentTo");
		$submitGroup = new group("submitGroup");
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setTable("productManager");
		$ijfId->setVisible(false);
		$ijfId->setGroup("ijf");
		$ijfId->setDataType("number");
		$ijfId->setValue(0);
		$ijf->add($ijfId);
		
		$updatedDate = new textbox("updatedDate");
		$updatedDate->setValue($today);
		$updatedDate->setTable("ijf");
		$updatedDate->setVisible(false);
		$ijf->add($updatedDate);

		$productManager_comments = new textarea("productManager_comments");
		$productManager_comments->setTable("productManager");
		$productManager_comments->setVisible(true);
		$productManager_comments->setGroup("ijf");
		$productManager_comments->setDataType("text");
		$productManager_comments->setRequired(false);
		$productManager_comments->setRowTitle("product_manager_comments");
		$ijf->add($productManager_comments);
		
		
		$status = new textbox("status");
		$status->setValue("ijf");
		$status->setGroup("sentTo");
		$status->setTable("ijf");
		$status->setVisible(false);
		$sentTo->add($status);		
		
		
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("sendTo");
		$location_owner->setLength(250);
		$location_owner->setTable("productManager");
		$location_owner->setRowTitle("send_ijf_to_location");
		$location_owner->setLabel("User Delivery Options");
		$location_owner->setRequired(true);
		$location_owner->setHelpId(2098);
		$location_owner->setXMLSource("./apps/ijf/xml/departments.xml");
		$sentTo->add($location_owner);
		
		$productManager_owner = new dropdown("productManager_owner");
		$productManager_owner->setLength(250);
		$productManager_owner->setTable("productManager");
		$productManager_owner->setRowTitle("pass_ijf_to");
		$productManager_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$productManager_owner->setRequired(true);
		$productManager_owner->setHelpId(2099);
		$productManager_owner->setVisible(true);
		$sentTo->add($productManager_owner);
		
		$owner = new dropdown("owner");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->setRowTitle("pass_ijf_to");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$owner->setRequired(false);
		$owner->setVisible(false);
		$sentTo->add($owner);
		
		$delegate_owner = new multipleCC("delegate_owner");
		$delegate_owner->setGroup("sendTo");
		$delegate_owner->setLength(250);
		$delegate_owner->setTable("ijf");
		$delegate_owner->setDataType("text");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2104);
		$sentTo->add($delegate_owner);
		
		$email_text = new textarea("email_text");
		$email_text->setGroup("sentTo");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setHelpId(2105);
		$email_text->setTable("ijf");
		$sentTo->add($email_text);
		
		$submit = new submit("submit");
		$submit->setGroup("sentTo");
		$submit->setVisible(true);
		$submitGroup->add($submit);
		
		
		$this->form->add($ijf);
		$this->form->add($sentTo);	
		$this->form->add($submitGroup);
		
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
		
//		if($action == "production_cc") $subjectText = "CC - " . $subjectText;
	
		email::send($owner, /*"intranet@scapa.com"*/ $sender, $subjectText, "$email", "$cc");
		
		return true;
	}

}

?>