<?php

class production extends ijfProcess 
{	
	

	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		//$this->defineForm();
		
		$this->form->get('ijfId')->setValue($this->ijf->getId());
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['production']['loadedFromDatabase']))
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


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM production INNER JOIN ijf ON production.ijfId=ijf.id WHERE ijfId = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			
			page::addDebug("sdfsdfsdfsdf", __FILE__, __LINE__);
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['production']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			$this->form->get('updatedDate')->setValue(page::transformDateForPHP($this->form->get('updatedDate')->getValue()));
			
			
			//if ($this->getIJF()->getRemainingQuantity() != "0.00")
			//{
			//	$perUnit = $this->getIJF()->getRemainingValue() / $this->getIJF()->getRemainingQuantity();
			//}
			//else
			//{
			//	$perUnit = 0;
			//}
			
			//$this->form->get("disposal_value")->setValue($fields['disposal_volume_quantity'] * $perUnit . " " . $this->getIJF()->getCurrency());
			
			

			$this->form->putValuesInSession();

			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['production']);
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
			
			$this->form->get("owner")->setValue($this->form->get("production_owner")->getValue());
			
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE production " . $this->form->generateUpdateQuery("production") . " WHERE ijfId='" . $this->getIJF()->getID() . "'");
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			
			// save new data
			
			if ($_REQUEST['testingRequired'] == 'yes')
			{
				$this->addLog(translate::getInstance()->translate("testing_still_required"));
			}
			else if ($_REQUEST['testingRequired'] == 'no')
			{
				$this->addLog(translate::getInstance()->translate("Production report updated"));
			}
			
			//Send Email
			$this->getEmailNotification("production", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("production_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
		}
		else 
		{
		
			$this->form->get("owner")->setValue($this->form->get("production_owner")->getValue());
			
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
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO production " . $this->form->generateInsertQuery("production"));
			
			
			if ($_REQUEST['testingRequired'] == 'yes')
			{
				$this->addLog(translate::getInstance()->translate("testing_required"));
			}
			else if ($_REQUEST['testingRequired'] == 'no')
			{
				$this->addLog(translate::getInstance()->translate("production_report_completed"));
			}
			
			
			//$this->addLog(translate::getInstance()->translate("production_report_completed"));
			
			
			//if ($this->status == 'complete')
			//{
			//	$this->addLog(translate::getInstance()->translate("ijf_completed_unworkable"));
			//}
			//else 
			//{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");				
			//}
			
			// Send Email
			$this->getEmailNotification("production", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("production_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));		
			
		}
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
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

		$ijf = new group("ijf");
		$production_t = new group("production_t");
		$production_t2 = new group("production_t2");
		$production_t3 = new group("production_t3");
		$production_t4 = new group("production_t4");
		$production_t5 = new group("production_t5");
		$production_t6 = new group("production_t6");
		$sentTo = new group("sentTo");
		$submitGroup = new group("submitGroup");
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setTable("production");
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
		
		$testingRequired = new dropdown("testingRequired");
		$testingRequired->setTable("production");
		$testingRequired->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$testingRequired->setVisible(true);
		$testingRequired->setGroup("production");
		$testingRequired->setLabel("Testing");
		$testingRequired->setHelpId(2072);
		$testingRequired->setRowTitle("testing_required");
		$production_t->add($testingRequired);
		
		$testingRequiredComments = new textarea("testingRequiredComments");
		$testingRequiredComments->setTable("production");
		$testingRequiredComments->setVisible(true);
		$testingRequiredComments->setGroup("production");
		$testingRequiredComments->setDataType("text");
		$testingRequiredComments->setRequired(false);
		$testingRequiredComments->setHelpId(2073);
		$testingRequiredComments->setRowTitle("testing_required_comments");
		$production_t->add($testingRequiredComments);
		
		$viable = new dropdown("viable");
		$viable->setTable("production");
		$viable->setVisible(true);
		$viable->setGroup("production");
		$viable->setDataType("string");
		$viable->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$viable->setRequired(false);
		$viable->setRowTitle("viable");
		$viable->setHelpId(2074);
		$viable->setLabel("Production Information");
		$production_t->add($viable);
		
		$minimumOrderQuantity = new textbox("minimumOrderQuantity");
		$minimumOrderQuantity->setTable("production");
		$minimumOrderQuantity->setVisible(true);
		$minimumOrderQuantity->setGroup("production");
		$minimumOrderQuantity->setDataType("text");
		$minimumOrderQuantity->setRequired(false);
		$minimumOrderQuantity->setHelpId(2075);
		$minimumOrderQuantity->setRowTitle("minimum_order_quantity");
		$production_t->add($minimumOrderQuantity);
		
		$toolsRequired = new dropdown("toolsRequired");
		$toolsRequired->setTable("production");
		$toolsRequired->setVisible(true);
		$toolsRequired->setGroup("production");
		$toolsRequired->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$toolsRequired->setDataType("string");
		$toolsRequired->setRequired(false);
		$toolsRequired->setHelpId(2076);
		$toolsRequired->setRowTitle("tools_required");
		$production_t->add($toolsRequired);
		
		$toolsComments = new textarea("toolsComments");
		$toolsComments->setTable("production");
		$toolsComments->setVisible(true);
		$toolsComments->setGroup("production");
		$toolsComments->setDataType("text");
		$toolsComments->setRequired(false);
		$toolsComments->setHelpId(2077);
		$toolsComments->setRowTitle("comments");
		$production_t->add($toolsComments);
		
		$sugCostedLotSize = new textbox("sugCostedLotSize");
		$sugCostedLotSize->setTable("production");
		$sugCostedLotSize->setVisible(true);
		$sugCostedLotSize->setGroup("production");
		$sugCostedLotSize->setDataType("string");
		$sugCostedLotSize->setRequired(false);
		$sugCostedLotSize->setHelpId(2078);
		$sugCostedLotSize->setRowTitle("suggested_costed_lot_size");
		$production_t->add($sugCostedLotSize);
		
		
		$packagingRequired = new dropdown("packagingRequired");
		$packagingRequired->setTable("production");
		$packagingRequired->setVisible(true);
		$packagingRequired->setLabel("Packaging Details");
		$packagingRequired->setGroup("production");
		$packagingRequired->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$packagingRequired->setDataType("string");
		$packagingRequired->setRequired(false);
		$packagingRequired->setHelpId(2079);
		$packagingRequired->setRowTitle("special_packaging_required");
		$production_t2->add($packagingRequired);
		
		$packagingRequiredComments = new textarea("packagingRequiredComments");
		$packagingRequiredComments->setTable("production");
		$packagingRequiredComments->setVisible(true);
		$packagingRequiredComments->setGroup("production");
		$packagingRequiredComments->setDataType("text");
		$packagingRequiredComments->setRequired(false);
		$packagingRequiredComments->setHelpId(2080);
		$packagingRequiredComments->setRowTitle("packaging_required_comments");
		$production_t2->add($packagingRequiredComments);
		
		$cartonQuantity = new textbox("cartonQuantity");
		$cartonQuantity->setTable("production");
		$cartonQuantity->setVisible(true);
		$cartonQuantity->setGroup("production");
		$cartonQuantity->setLabel("Carton / Pallet Details");
		$cartonQuantity->setDataType("string");
		$cartonQuantity->setRequired(false);
		$cartonQuantity->setHelpId(2081);
		$cartonQuantity->setRowTitle("carton_quantity");
		$production_t3->add($cartonQuantity);
		
		$cartonsPerLayer = new textbox("cartonsPerLayer");
		$cartonsPerLayer->setTable("production");
		$cartonsPerLayer->setVisible(true);
		$cartonsPerLayer->setGroup("production");
		$cartonsPerLayer->setDataType("string");
		$cartonsPerLayer->setRequired(false);
		$cartonsPerLayer->setHelpId(2082);
		$cartonsPerLayer->setRowTitle("cartons_per_layer");
		$production_t3->add($cartonsPerLayer);
		
		$layersPerPallet = new textbox("layersPerPallet");
		$layersPerPallet->setTable("production");
		$layersPerPallet->setVisible(true);
		$layersPerPallet->setGroup("production");
		$layersPerPallet->setDataType("string");
		$layersPerPallet->setRequired(false);
		$layersPerPallet->setHelpId(2083);
		$layersPerPallet->setRowTitle("layers_per_pallet");
		$production_t3->add($layersPerPallet);
		
		$palletQuantity = new textbox("palletQuantity");
		$palletQuantity->setTable("production");
		$palletQuantity->setVisible(true);
		$palletQuantity->setGroup("production");
		$palletQuantity->setDataType("string");
		$palletQuantity->setRequired(false);
		$palletQuantity->setHelpId(2084);
		$palletQuantity->setRowTitle("pallet_quantity");
		$production_t3->add($palletQuantity);
		
		$extraCartonSpecification = new textarea("extraCartonSpecification");
		$extraCartonSpecification->setTable("production");
		$extraCartonSpecification->setVisible(true);
		$extraCartonSpecification->setGroup("production");
		$extraCartonSpecification->setDataType("text");
		$extraCartonSpecification->setRequired(false);
		$extraCartonSpecification->setHelpId(2085);
		$extraCartonSpecification->setRowTitle("extra_carton_specification");
		$production_t3->add($extraCartonSpecification);
		
		$specificCarton = new textbox("specificCarton");
		$specificCarton->setTable("production");
		$specificCarton->setVisible(true);
		$specificCarton->setGroup("production");
		$specificCarton->setDataType("string");
		$specificCarton->setRequired(false);
		$specificCarton->setHelpId(2086);
		$specificCarton->setRowTitle("specific_carton");
		$production_t3->add($specificCarton);
		
		$palletSpecification = new textarea("palletSpecification");
		$palletSpecification->setTable("production");
		$palletSpecification->setVisible(true);
		$palletSpecification->setGroup("production");
		$palletSpecification->setDataType("text");
		$palletSpecification->setRequired(false);
		$palletSpecification->setHelpId(2087);(false);
		$palletSpecification->setRowTitle("pallet_specification");
		$production_t3->add($palletSpecification);
		
		$barcodeType = new textbox("barcodeType");
		$barcodeType->setTable("production");
		$barcodeType->setVisible(true);
		$barcodeType->setGroup("production");
		$barcodeType->setLabel("Barcode Details");
		$barcodeType->setDataType("string");
		$barcodeType->setRequired(false);
		$barcodeType->setHelpId(2088);
		$barcodeType->setRowTitle("barcode_type");
		$production_t4->add($barcodeType);
		
		$barcodeRequired = new textbox("barcodeRequired");
		$barcodeRequired->setTable("production");
		$barcodeRequired->setVisible(true);
		$barcodeRequired->setGroup("production");
		$barcodeRequired->setDataType("string");
		$barcodeRequired->setRequired(false);
		$barcodeRequired->setHelpId(2089);
		$barcodeRequired->setRowTitle("barcode");
		$production_t4->add($barcodeRequired);
		
		$labellingSpecification = new dropdown("labellingSpecification");
		$labellingSpecification->setTable("production");
		$labellingSpecification->setVisible(true);
		$labellingSpecification->setGroup("production");
		$labellingSpecification->setDataType("string");
		$labellingSpecification->setLabel("Labelling Details");
		$labellingSpecification->setRequired(false);
		$labellingSpecification->setHelpId(2090);
		$labellingSpecification->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$labellingSpecification->setRowTitle("labelling_specification");
		$production_t5->add($labellingSpecification);
		
		$labellingSpecificationComments = new textarea("labellingSpecificationComments");
		$labellingSpecificationComments->setTable("production");
		$labellingSpecificationComments->setVisible(true);
		$labellingSpecificationComments->setGroup("production");
		$labellingSpecificationComments->setDataType("text");
		$labellingSpecificationComments->setRequired(false);
		$labellingSpecificationComments->setHelpId(2091);
		$labellingSpecificationComments->setRowTitle("labelling_specification_comments");
		$production_t5->add($labellingSpecificationComments);
		
		$routing = new textarea("routing");
		$routing->setTable("production");
		$routing->setVisible(true);
		$routing->setGroup("production");
		$routing->setLabel("Production Details");
		$routing->setDataType("text");
		$routing->setRequired(false);
		$routing->setHelpId(2092);
		$routing->setRowTitle("routing");
		$production_t6->add($routing);
		
		$setUpTime = new textbox("setUpTime");
		$setUpTime->setTable("production");
		$setUpTime->setVisible(true);
		$setUpTime->setGroup("production");
		$setUpTime->setDataType("string");
		$setUpTime->setRequired(false);
		$setUpTime->setHelpId(2093);
		$setUpTime->setRowTitle("set_up_time");
		$production_t6->add($setUpTime);
		
		$quantityPerHour = new textbox("quantityPerHour");
		$quantityPerHour->setTable("production");
		$quantityPerHour->setVisible(true);
		$quantityPerHour->setGroup("production");
		$quantityPerHour->setDataType("string");
		$quantityPerHour->setRequired(false);
		$quantityPerHour->setHelpId(2094);
		$quantityPerHour->setRowTitle("quantity_per_hour");
		$production_t6->add($quantityPerHour);
		
		$inputMaterialRequired = new textarea("inputMaterialRequired");
		$inputMaterialRequired->setTable("production");
		$inputMaterialRequired->setVisible(true);
		$inputMaterialRequired->setGroup("production");
		$inputMaterialRequired->setDataType("text");
		$inputMaterialRequired->setRequired(false);
		$inputMaterialRequired->setHelpId(2095);
		$inputMaterialRequired->setRowTitle("input_material_required");
		$production_t6->add($inputMaterialRequired);
		
		$specialInstructions = new textarea("specialInstructions");
		$specialInstructions->setTable("production");
		$specialInstructions->setVisible(true);
		$specialInstructions->setGroup("production");
		$specialInstructions->setDataType("text");
		$specialInstructions->setRequired(false);
		$specialInstructions->setHelpId(2096);
		$specialInstructions->setRowTitle("special_instructions");
		$production_t6->add($specialInstructions);
		
		$newItemToBePurchased = new radio("newItemToBePurchased");
		$newItemToBePurchased->setTable("production");
		$newItemToBePurchased->setVisible(true);
		$newItemToBePurchased->setGroup("production");
		$newItemToBePurchased->setDataType("string");
		$newItemToBePurchased->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$newItemToBePurchased->setRequired(false);
		$newItemToBePurchased->setHelpId(2097);
		$newItemToBePurchased->setRowTitle("new_item_to_be_purchased");
		$production_t6->add($newItemToBePurchased);
		
	
		
		
		$status = new textbox("status");
		$status->setValue("ijf");
		$status->setGroup("sentTo");
		$status->setTable("ijf");
		$status->setVisible(false);
		$sentTo->add($status);		
		
		
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("sendTo");
		$location_owner->setLength(250);
		$location_owner->setTable("production");
		$location_owner->setRowTitle("send_ijf_to_location");
		$location_owner->setLabel("User Delivery Options");
		$location_owner->setRequired(true);
		$location_owner->setValue("production");
		$location_owner->setHelpId(2098);
		$location_owner->setXMLSource("./apps/ijf/xml/departments.xml");
		$sentTo->add($location_owner);
		
		$production_owner = new dropdown("production_owner");
		$production_owner->setLength(250);
		$production_owner->setTable("production");
		$production_owner->setRowTitle("pass_ijf_to");
		$production_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$production_owner->setRequired(true);
		$production_owner->setHelpId(2099);
		$production_owner->setVisible(true);
		$sentTo->add($production_owner);
		
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
		$this->form->add($production_t);
		$this->form->add($production_t2);
		$this->form->add($production_t3);
		$this->form->add($production_t4);
		$this->form->add($production_t5);
		$this->form->add($production_t6);
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

		$cc = $this->form->get('delegate_owner')->getValue();
		
//		if($action == "production_cc") $subjectText = "CC - " . $subjectText;
	
		email::send($owner, /*"intranet@scapa.com"*/ $sender, $subjectText, "$email", "$cc");
		
		return true;
	}

}

?>