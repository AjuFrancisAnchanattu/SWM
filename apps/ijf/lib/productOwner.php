<?php

class productOwner extends ijfProcess 
{	
	

	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
	
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


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM productOwner INNER JOIN ijf ON productOwner.ijf-ijf.id WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['productOwner']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			
			$this->form->get("disposal_volume")->setValue(array($fields['disposal_volume_quantity'], $fields['disposal_volume_measurement']));
			
			if ($this->getIJF()->getRemainingQuantity() != "0.00")
			{
				$perUnit = $this->getIJF()->getRemainingValue() / $this->getIJF()->getRemainingQuantity();
			}
			else
			{
				$perUnit = 0;
			}
			
			$this->form->get("disposal_value")->setValue($fields['disposal_volume_quantity'] * $perUnit . " " . $this->getIJF()->getCurrency());
			
			
			
		
			$this->form->get('date')->setValue(page::transformDateForPHP($this->form->get('date')->getValue()));

			$this->form->putValuesInSession();

			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['productOwner']);
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
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE productOwner " . $this->form->generateUpdateQuery("production") . " WHERE ijfId='" . $this->id . "'");
			
			
			
			// save new data
			
			
			$this->addLog("productOwner report updated");
		}
		else 
		{
		
			// set report date
			$this->form->get("date")->setValue(common::nowDateForMysql());
			
			
			// begin transaction
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// insert
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO productOwner " . $this->form->generateInsertQuery("production"));
			
			$this->addLog(translate::getInstance()->translate("productOwner_report_completed"));
			
			// new action, email the owner
			$dom = new DomDocument;
			$dom->loadXML("<newProductionAction><status>" . $this->form->get("status")->getValue() . "</status><action>" . $fields['id'] . "</action><completionDate>" . $this->form->get("date")->getValue() . "</completionDate></newProductionAction>");

			// load xsl
			$xsl = new DomDocument;
			$xsl->load("http://scapanet/apps/ijf/xsl/email.xsl");

			// transform xml using xsl
			$proc = new xsltprocessor;
			$proc->importStyleSheet($xsl);

			$email = $proc->transformToXML($dom);

			email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), "intranet@scapa.com", translate::getInstance()->translate("new_ijf_action"), "$email");
			
			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("ijf_completed_unworkable"));
			}
			else 
			{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");				
			}

			
		}
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
		
		page::redirect("/apps/ijf/");
	}
	

	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
			$this->getIJF()->form->get("id")->getValue(),
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql()
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
		$this->form = new form("production");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		$production = new group("production");
		$production->setBorder(false);
		$inspection = new group("inspection");
		$inspection->setBorder(false);
		$yes_pass_inspection = new group("yes_pass_inspection");
		$yes_pass_inspection->setBorder(false);
		$no_pass_inspection = new group("no_pass_inspection");
		$no_pass_inspection->setBorder(false);
		$partly_pass_inspection = new group("partly_pass_inspection");
		$partly_pass_inspection->setBorder(false);
		$slob = new group("slob");

		
		
		$slob_id = new textbox("slob_id");
		$slob_id->setTable("production");
		$slob_id->setVisible(false);
		$slob_id->setDataType("number");
		$slob_id->setValue($this->getIJF()->form->get("id")->getValue());
		$production->add($slob_id);
		
		$production_owner = new textbox("production_owner");
		$production_owner->setTable("production");
		$production_owner->setVisible(false);
		$production_owner->setDataType("string");
		$production_owner->setValue(currentuser::getInstance()->getNTLogon());
		$production->add($production_owner);
		
		$status = new textbox("status");
		$status->setValue("production");
		$status->setTable("slob");
		$status->setVisible(false);
		$production->add($status);
		
		
		$date = new textbox("date");
		$date->setTable("production");
		$date->setVisible(false);
		$date->setDataType("text");
		$date->setRequired(false);
		$date->setLength(50);
		$date->setLabel("date_entered");
		$production->add($date);
		
		
		$slob_volume_quantity_hidden = new invisibletext("slob_volume_quantity_hidden");
		$slob_volume_quantity_hidden->setGroup("slob");
		$slob_volume_quantity_hidden->setDataType("string");
		$slob_volume_quantity_hidden->setVisible(false);
		$slob_volume_quantity_hidden->setRowTitle("stock_volume_hidden");
		//$slob_volume_quantity_hidden->setValue($this->getIJF()->form->get("volume")->getQuantity());
		$slob->add($slob_volume_quantity_hidden);
		
		$slob_volume_measurement_hidden = new invisibletext("slob_volume_measurement_hidden");
		$slob_volume_measurement_hidden->setGroup("slob");
		$slob_volume_measurement_hidden->setDataType("string");
		$slob_volume_measurement_hidden->setVisible(false);
		$slob_volume_measurement_hidden->setRowTitle("stock_volume_hidden");
		//$slob_volume_measurement_hidden->setValue($this->getIJF()->form->get("volume")->getMeasurement());
		$slob->add($slob_volume_measurement_hidden);
		
		$slob_value = new readonly("slob_value");
		$slob_value->setGroup("slob");
		$slob_value->setDataType("string");
		$slob_value->setRowTitle("stock_value");
		//$slob_value->setValue($this->getIJF()->getRemainingValue());
		$slob->add($slob_value);
		
		$slob_value_quantity_hidden = new invisibletext("slob_value_quantity_hidden");
		$slob_value_quantity_hidden->setGroup("slob");
		$slob_value_quantity_hidden->setVisible(false);
		$slob_value_quantity_hidden->setDataType("string");
		$slob_value_quantity_hidden->setRowTitle("stock_value_hidden");
		//$slob_value_quantity_hidden->setValue($this->getIJF()->form->get("value")->getQuantity());
		$slob->add($slob_value_quantity_hidden);
		
		$slob_value_measurement_hidden = new invisibletext("slob_value_measurement_hidden");
		$slob_value_measurement_hidden->setGroup("slob");
		$slob_value_measurement_hidden->setDataType("string");
		$slob_value_measurement_hidden->setVisible(false);
		$slob_value_measurement_hidden->setRowTitle("stock_value_hidden");
		//$slob_value_measurement_hidden->setValue($this->getIJF()->form->get("value")->getMeasurement());
		$slob->add($slob_value_measurement_hidden);
		

		$pass_inspection = new radio("pass_inspection");
		$pass_inspection->setGroup("pass_inspection");
		$pass_inspection->setDataType("string");
		$pass_inspection->setLength(50);
		$pass_inspection->setRequired(true);
		$pass_inspection->setLabel("Reworkable Notes");
		$pass_inspection->setArraySource(array(
			array('value' => 'yes', 'display' => 'yes'),
			array('value' => 'partly', 'display' => 'partly'),
			array('value' => 'no', 'display' => 'no')
		));
		$pass_inspection->setValue('yes');
		$pass_inspection->setRowTitle("can_the_material_be_reworked_to_produce_usable_or_saleable_material");
		$pass_inspection->setOnKeyPress("slobCalculateRemainingVolumeAndValue");
		$pass_inspection->setTable("production");
		
	
		
		$yes_dependency = new dependency();
		$yes_dependency->addRule(new rule('inspection', 'pass_inspection', 'yes'));
		$yes_dependency->addRule(new rule('inspection', 'pass_inspection', 'partly'));
		$yes_dependency->setGroup('yes_pass_inspection');
		$yes_dependency->setRuleCondition("or");
		$yes_dependency->setShow(true);
		
		$no_dependency = new dependency();
		$no_dependency->addRule(new rule('inspection', 'pass_inspection', 'no'));
		$no_dependency->addRule(new rule('inspection', 'pass_inspection', 'partly'));
		$no_dependency->setGroup('no_pass_inspection');
		$no_dependency->setRuleCondition("or");
		$no_dependency->setShow(true);
		
		$partly_dependency = new dependency();
		$partly_dependency->addRule(new rule('inspection', 'pass_inspection', 'partly'));
		$partly_dependency->setGroup('partly_pass_inspection');
		$partly_dependency->setShow(true);

		
		
		$pass_inspection->addControllingDependency($yes_dependency);
		$pass_inspection->addControllingDependency($partly_dependency);
		$pass_inspection->addControllingDependency($no_dependency);	
		
		
		//$pass_inspection->addControllingDependency($partly1_dependency);
		
		$inspection->add($pass_inspection);
		

		
		$disposal_volume = new measurement("disposal_volume");
		$disposal_volume->setGroup("partly_pass_inspection");
		//$disposal_volume->setArraySource(array(
		//	array('value' => $this->getIJF()->form->get("volume")->getMeasurement(), 'display' => $this->getIJF()->form->get("volume")->getMeasurement())
		//));
		$disposal_volume->setRowTitle("quantity_for_disposal");
		$disposal_volume->setTable("production");
		$disposal_volume->setOnKeyPress("slobCalculateRemainingVolumeAndValue");
		$partly_pass_inspection->add($disposal_volume);

		
		$disposal_value = new readonly("disposal_value");
		$disposal_value->setGroup("partly_pass_inspection");
		$disposal_value->setRowTitle("value_for_disposal");
		$disposal_value->setTable("production");
		$partly_pass_inspection->add($disposal_value);
		

		$disposal_note = new textarea("disposal_note");
		$disposal_note->setGroup("no_pass_inspection");
		$disposal_note->setRowTitle("disposal_note");
		$disposal_note->setTable("production");
		$no_pass_inspection->add($disposal_note);

		
		$reworkable_note = new textarea("reworkable_note");
		$reworkable_note->setTable("production");
		$reworkable_note->setRowTitle("reworkable_note");
		$reworkable_note->setRequired(false);
		$yes_pass_inspection->add($reworkable_note);
		
		
		$remaining_volume = new readonly("remaining_volume");
		$remaining_volume->setGroup("slob");
		$remaining_volume->setDataType("string");
		$remaining_volume->setRowTitle("reworkable_stock_quantity");
		$remaining_volume->setIgnore(true);
		//$remaining_volume->setValue($this->getSlob()->getRemainingQuantity());
		$yes_pass_inspection->add($remaining_volume);
		
		$remaining_value = new readonly("remaining_value");
		$remaining_value->setGroup("slob");
		$remaining_value->setDataType("string");
		$remaining_value->setRowTitle("reworkable_stock_value");
		$remaining_value->setIgnore(true);
		//$remaining_value->setValue($this->getSlob()->getRemainingValue());
		$yes_pass_inspection->add($remaining_value);
		
		
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("sendTo");
		$location_owner->setLength(250);
		$location_owner->setTable("slob");
		$location_owner->setRowTitle("send_slob_to_location");
		$location_owner->setLabel("User Delivery Options");
		$location_owner->setRequired(false);
		$location_owner->setHelpId(1008);
		$location_owner->setXMLSource("http://scapanet/apps/slobs/xml/departments.xml");
		$yes_pass_inspection->add($location_owner);
		
		$owner = new dropdown("owner");
		$owner->setLength(250);
		$owner->setTable("slob");
		$owner->setRowTitle("pass_slob_to");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` LEFT JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission='slobs_commercial_planning' ORDER BY employee.NTLogon");
		$owner->setRequired(true);
		$owner->setValue($production_owner->getValue());
		$yes_pass_inspection->add($owner);
		
		
		
		
		$this->form->add($slob);
		$this->form->add($inspection);
		$this->form->add($partly_pass_inspection);
		$this->form->add($no_pass_inspection);
		
		$this->form->add($yes_pass_inspection);
		$this->form->add($production);	
		
	}
}

?>