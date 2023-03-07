<?php

class productionSite extends ijfProcess 
{	
	private $status;
	
	
	function __construct($ijf)
	{
		parent::__construct($ijf);
		
		$this->defineForm();
		
		$this->form->get('ijfId')->setValue($this->ijf->getId());
		
		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['productionSite']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database
		}
	
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


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = " . $id);

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['productionSite']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);
			
			$this->form->get("width")->setValue(array($fields['width_quantity'], $fields['width_measurement']));
			$this->form->get("ijfLength")->setValue(array($fields['ijfLength_quantity'], $fields['ijfLength_measurement']));
			$this->form->get("thickness")->setValue(array($fields['thickness_quantity'], $fields['thickness_measurement']));

			

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
			unset($_SESSION['apps'][$GLOBALS['app']]['productionSite']);
			return false;
		}
		
	}
	

	
	public function save()
	{	
		$this->determineStatus();
		
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE productionSite " . $this->form->generateUpdateQuery("productionSite") . " WHERE ijfId='" . $this->getIjfId() . "'");
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIJF()->getID() . "'");
			
			// save new data
			
			
			$this->addLog("productionSite report updated");
		}
		else 
		{
		
			// set report date
			$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
			
			
			// begin transaction
			//mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");
			
			// insert
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIjfId() . "'");
			
			
			//mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");
			
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			$this->id = $fields['id'];
			
			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO productionSite " . $this->form->generateInsertQuery("productionSite"));
			
			$this->addLog(translate::getInstance()->translate("productionSite_planning_report_completed"));
			
			$emailSectionName = translate::getInstance()->translate($this->ijf->form->get("status")->getValue());
			
			// new action, email the owner
			$dom = new DomDocument;
			$emailNextSectionName = translate::getInstance()->translate($this->form->get("status")->getValue());
			$dom->loadXML("<newAction><status>" . $this->form->get("status")->getValue() . "</status><action>" . $fields['id'] . "</action><completionDate>" . $this->ijf->form->get("ijfDueDate")->getValue() . "</completionDate><emailSectionName>" . $emailNextSectionName . "</emailSectionName></newAction>");
	
			// load xsl
			$xsl = new DomDocument;
			$xsl->load("./apps/ijf/xsl/email.xsl");
	
			// transform xml using xsl
			$proc = new xsltprocessor;
			$proc->importStyleSheet($xsl);
	
			$email = $proc->transformToXML($dom);
			$cc = $this->form->get("delegate_owner")->getValue();
	
			email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $fields['id']), "$email", "$cc");
		
			/*
			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("productionSite_completed_disposed"));
			}
			else 
			{
			*/
			$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");				
			//}
			


			
		}
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
	
	}
	

	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate) VALUES ('%u', '%s', '%s', '%s')",
			$_REQUEST['ijf'],
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
	}

	public function defineForm()
	{
		//$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = '" . $this->getIjfId() . "'");
		//$fields = mysql_fetch_array($dataset);
		//$prodSite = $fields['productionSite'];
		
		$prodSite = $this->ijf->form->get('productionSite')->getValue();
				
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("productionSite");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		$ijf = new group("ijf");
		$productionSite = new group("productionSite");
		$potentialBusiness = new group("potentialBusiness");
		$sentTo = new group("sentTo");		
		
		
		$ijfId = new invisibletext("ijfId");
		$ijfId->setTable("productionSite");
		$ijfId->setVisible(false);
		$ijfId->setGroup("ijf");
		$ijfId->setDataType("number");
		$ijfId->setValue(0);
		$ijf->add($ijfId);
		
		$status = new textbox("status");
		$status->setValue("ijf");
		$status->setTable("ijf");
		$status->setRequired(true);
		$status->setVisible(false);
		$ijf->add($status);
		
		$updatedDate = new textbox("updatedDate");
		$updatedDate->setValue($today);
		$updatedDate->setTable("ijf");
		$updatedDate->setVisible(false);
		$ijf->add($updatedDate);
		
		$width = new measurement("width");
		$width->setTable("productionSite");
		$width->setVisible(true);
		$width->setGroup("productionSite");
		$width->setDataType("string");
		$width->setRequired(false);
		$width->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$width->setRowTitle("width");
		$width->setLabel("Site Specific Information");
		$productionSite->add($width);
		
		
		$ijfLength = new measurement("ijfLength");
		$ijfLength->setTable("productionSite");
		$ijfLength->setVisible(true);
		$ijfLength->setGroup("productionSite");
		$ijfLength->setDataType("string");
		$ijfLength->setRequired(false);
		$ijfLength->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$ijfLength->setRowTitle("length");
		$productionSite->add($ijfLength);
		
		
		$thickness = new measurement("thickness");
		$thickness->setTable("productionSite");
		$thickness->setVisible(true);
		$thickness->setGroup("productionSite");
		$thickness->setDataType("string");
		$thickness->setRequired(false);
		$thickness->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$thickness->setRowTitle("thickness");
		$productionSite->add($thickness);
		
		
		$colour = new dropdown("colour");
		$colour->setTable("productionSite");
		$colour->setVisible(true);
		$colour->setGroup("productionSite");
		$colour->setXMLSource("./apps/ijf/xml/colours.xml");
		$colour->setDataType("string");
		$colour->setRequired(false);
		$colour->setRowTitle("colour");
		$productionSite->add($colour);
		
		
		if ($prodSite == "Lymington")
		{
		
			$liner = new dropdown("liner");
			$liner->setTable("productionSite");
			$liner->setVisible(true);
			$liner->setGroup("productionSite");
			$liner->setDataType("string");
			$liner->setRequired(false);
			$liner->setArraySource(array(
			array('value' => 'paper', 'display' => 'Paper'),
			array('value' => 'film', 'display' => 'Film'))
			);
			$liner->setRowTitle("liner");
			$productionSite->add($liner);

			$certificateOfConformity = new dropdown("certificateOfConformity");
			$certificateOfConformity->setTable("productionSite");
			$certificateOfConformity->setVisible(true);
			$certificateOfConformity->setGroup("productionSite");
			$certificateOfConformity->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No'))
			);
			$certificateOfConformity->setDataType("string");
			$certificateOfConformity->setRequired(false);
			$certificateOfConformity->setRowTitle("certificateOfConformity");
			$productionSite->add($certificateOfConformity);


			$specificLiningOverhang = new textbox("specificLiningOverhang");
			$specificLiningOverhang->setTable("productionSite");
			$specificLiningOverhang->setVisible(true);
			$specificLiningOverhang->setGroup("productionSite");
			$specificLiningOverhang->setDataType("string");
			$specificLiningOverhang->setRequired(false);
			$specificLiningOverhang->setRowTitle("specificLiningOverhang");
			$productionSite->add($specificLiningOverhang);

			$overlapRequired = new textbox("overlapRequired");
			$overlapRequired->setTable("productionSite");
			$overlapRequired->setVisible(true);
			$overlapRequired->setGroup("productionSite");
			$overlapRequired->setDataType("string");
			$overlapRequired->setRequired(false);
			$overlapRequired->setRowTitle("overlapRequired");
			$productionSite->add($overlapRequired);

			$laminates = new dropdown("laminates");
			$laminates->setTable("productionSite");
			$laminates->setVisible(true);
			$laminates->setGroup("productionSite");
			$laminates->setDataType("string");
			$laminates->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No'))
			);
			$laminates->setRequired(false);
			$laminates->setRowTitle("laminates");
			$productionSite->add($laminates);

			$tolerances = new textarea("tolerances");
			$tolerances->setTable("productionSite");
			$tolerances->setVisible(true);
			$tolerances->setGroup("productionSite");
			$tolerances->setDataType("string");
			$tolerances->setRequired(false);
			$tolerances->setRowTitle("tolerances");
			$productionSite->add($tolerances);

			$formatComments = new textarea("formatComments");
			$formatComments->setTable("productionSite");
			$formatComments->setVisible(true);
			$formatComments->setGroup("productionSite");
			$formatComments->setDataType("string");
			$formatComments->setRequired(false);
			$formatComments->setRowTitle("formatComments");
			$productionSite->add($formatComments);
		
		}
		
		if ($prodSite == "Dunstable")
		{
		
			$alternativeLiner = new textbox("alternativeLiner");
			$alternativeLiner->setTable("productionSite");
			$alternativeLiner->setVisible(true);
			$alternativeLiner->setGroup("productionSite");
			$alternativeLiner->setDataType("string");
			$alternativeLiner->setRequired(false);
			$alternativeLiner->setValue("N/A");
			$alternativeLiner->setRowTitle("alternative_liner");
			$productionSite->add($alternativeLiner);
		
		}
		
		
		$comments = new textarea("comments");
		$comments->setGroup("productionSite");
		$comments->setRowTitle("comments");
		$comments->setTable("productionSite");
		$productionSite->add($comments);
		
		$core = new dropdown("core");
		$core->setTable("productionSite");
		$core->setVisible(true);
		$core->setGroup("productionSite");
		$core->setDataType("string");
		$core->setXMLSource("./apps/ijf/xml/core.xml");
		$core->setRequired(false);
		$core->setRowTitle("core");
		$productionSite->add($core);
		
		
		if ($prodSite == "Rorschach")
		{
		
			$cartons = new textbox("cartons");
			$cartons->setTable("productionSite");
			$cartons->setVisible(true);
			$cartons->setGroup("productionSite");
			$cartons->setDataType("string");
			$cartons->setRequired(false);
			$cartons->setRowTitle("cartons");
			$productionSite->add($cartons);

			$splices = new textbox("splices");
			$splices->setTable("productionSite");
			$splices->setVisible(true);
			$splices->setGroup("productionSite");
			$splices->setDataType("string");
			$splices->setRequired(false);
			$splices->setRowTitle("splices");
			$productionSite->add($splices);

			$slittingPreferences = new textbox("slittingPreferences");
			$slittingPreferences->setTable("productionSite");
			$slittingPreferences->setVisible(true);
			$slittingPreferences->setGroup("productionSite");
			$slittingPreferences->setDataType("string");
			$slittingPreferences->setRequired(false);
			$slittingPreferences->setRowTitle("slittingPreferences");
			$productionSite->add($slittingPreferences);

			$labellingRequirements = new textbox("labellingRequirements");
			$labellingRequirements->setTable("productionSite");
			$labellingRequirements->setVisible(true);
			$labellingRequirements->setGroup("productionSite");
			$labellingRequirements->setDataType("string");
			$labellingRequirements->setRequired(false);
			$labellingRequirements->setRowTitle("labellingRequirements");
			$productionSite->add($labellingRequirements);
		
		}
		
		
		$sellingUOM = new dropdown("sellingUOM");
		$sellingUOM->setTable("productionSite");
		$sellingUOM->setVisible(true);
		$sellingUOM->setGroup("productionSite");
		$sellingUOM->setDataType("string");
		$sellingUOM->setXMLSource("./apps/ijf/xml/sellingUOM.xml");
		$sellingUOM->setRequired(false);
		$sellingUOM->setRowTitle("sellingUOM");
		$productionSite->add($sellingUOM);
		
		if ($prodSite == "Megalon")
		{
			$alternativeSellingUOM = new textbox("alternativeSellingUOM");
			$alternativeSellingUOM->setTable("productionSite");
			$alternativeSellingUOM->setVisible(true);
			$alternativeSellingUOM->setGroup("productionSite");
			$alternativeSellingUOM->setDataType("string");
			$alternativeSellingUOM->setRequired(false);
			$alternativeSellingUOM->setRowTitle("alternativeSellingUOM");
			$productionSite->add($alternativeSellingUOM);
			
			$innerDiameterReq = new textbox("innerDiameterReq");
			$innerDiameterReq->setTable("productionSite");
			$innerDiameterReq->setVisible(true);
			$innerDiameterReq->setGroup("productionSite");
			$innerDiameterReq->setDataType("string");
			$innerDiameterReq->setRequired(false);
			$innerDiameterReq->setRowTitle("innerDiameterReq");
			$productionSite->add($innerDiameterReq);
			
			$outerDiameterReq = new textbox("outerDiameterReq");
			$outerDiameterReq->setTable("productionSite");
			$outerDiameterReq->setVisible(true);
			$outerDiameterReq->setGroup("productionSite");
			$outerDiameterReq->setDataType("string");
			$outerDiameterReq->setRequired(false);
			$outerDiameterReq->setRowTitle("outerDiameterReq");
			$productionSite->add($outerDiameterReq);
		}
		
		
		$annualQuantityUOM = new textbox("annualQuantityUOM");
		$annualQuantityUOM->setTable("productionSite");
		$annualQuantityUOM->setVisible(true);
		$annualQuantityUOM->setGroup("potentialBusiness");
		$annualQuantityUOM->setDataType("string");
		$annualQuantityUOM->setLabel("Potential Business");
		$annualQuantityUOM->setRequired(false);
		$annualQuantityUOM->setRowTitle("annual_quantity_in_selling_uom");
		$potentialBusiness->add($annualQuantityUOM);
		
		$firstOrderQuantityUOM = new textbox("firstOrderQuantityUOM");
		$firstOrderQuantityUOM->setTable("productionSite");
		$firstOrderQuantityUOM->setVisible(true);
		$firstOrderQuantityUOM->setGroup("potentialBusiness");
		$firstOrderQuantityUOM->setDataType("string");
		$firstOrderQuantityUOM->setRequired(false);
		$firstOrderQuantityUOM->setRowTitle("first_order_quantity_in_selling_uom");
		$potentialBusiness->add($firstOrderQuantityUOM);
		
		if ($prodSite == "Ghislarengo")
		{
			$labels = new textbox("labels");
			$labels->setTable("productionSite");
			$labels->setVisible(true);
			$labels->setGroup("potentialBusiness");
			$labels->setDataType("string");
			$labels->setRequired(false);
			$labels->setRowTitle("labels");
			$potentialBusiness->add($labels);
		}
		
		
		$targetPrice = new textbox("targetPrice");
		$targetPrice->setTable("productionSite");
		$targetPrice->setVisible(true);
		$targetPrice->setGroup("potentialBusiness");
		$targetPrice->setDataType("string");
		$targetPrice->setRequired(false);
		$targetPrice->setRowTitle("target_price");
		$potentialBusiness->add($targetPrice);
		
		$currency = new dropdown("currency");
		$currency->setTable("productionSite");
		$currency->setVisible(true);
		$currency->setGroup("potentialBusiness");
		$currency->setDataType("string");
		$currency->setXMLSource("./apps/ijf/xml/currency.xml");
		$currency->setRequired(false);
		$currency->setRowTitle("currency");
		$potentialBusiness->add($currency);
		
		$potentialComments = new textarea("potentialComments");
		$potentialComments->setTable("productionSite");
		$potentialComments->setVisible(true);
		$potentialComments->setGroup("potentialBusiness");
		$potentialComments->setDataType("string");
		$potentialComments->setRequired(false);
		$potentialComments->setRowTitle("comments");
		$potentialBusiness->add($potentialComments);
		
		
		
		//$productionSite_owner = new textbox("productionSite_owner");
		//$productionSite_owner->setTable("productionSite");
		//$productionSite_owner->setGroup("productionSite");
		//$productionSite_owner->setVisible(false);
		//$productionSite_owner->setDataType("string");
		//$productionSite_owner->setValue(currentuser::getInstance()->getNTLogon());
		//$sentTo->add($productionSite_owner);
		
		$location_owner = new dropdown("location_owner");
		$location_owner->setGroup("sendTo");
		$location_owner->setLength(250);
		$location_owner->setTable("productionSite");
		$location_owner->setRowTitle("send_ijf_to_location");
		$location_owner->setLabel("User Delivery Options");
		$location_owner->setRequired(false);
		$location_owner->setHelpId(1008);
		$location_owner->clearData();
		// $location_owner->setXMLSource("http://scapanetdev/apps/ijf/xml/departments.xml");
		$location_owner->setArraySource(array(
			array('value' => 'commercialPlanning', 'display' => 'Commercial Planning')
		));
		$sentTo->add($location_owner);
		
		$owner = new dropdown("owner");
		$owner->setGroup("productionSite");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->clearData();
		$owner->setRowTitle("ijf_owner");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` LEFT JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission='ijf_commercialPlanning_" . $this->ijf->form->get("productionSite")->getValue() . "' || permission='ijf_admin' ORDER BY employee.NTLogon");
		$owner->setRequired(false);
		$owner->setValue(currentuser::getInstance()->getNTLogon());
		$sentTo->add($owner);
		
		$delegate_owner = new autocomplete("delegate_owner");
		$delegate_owner->setGroup("sendTo");
		$delegate_owner->setLength(250);
		$delegate_owner->setTable("ijf");
		$delegate_owner->setUrl("/apps/ijf/ajax/ccijf?");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2010);
		$sentTo->add($delegate_owner);
		
		
		
		$this->form->add($ijf);
		$this->form->add($productionSite);	
		$this->form->add($potentialBusiness);
		$this->form->add($sentTo);
		
	}
	
}

?>