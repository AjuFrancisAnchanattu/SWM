<?php

class material
{
	private $actions = array();
	private $opportunities = array();
	
	public $form;
	
	private $id;
	private $ccrId;
	private $loadedFromDatabase = false;
	

	function __construct($id=-1)
	{
		$this->id = $id;
		
		$this->defineForm();
		
		$this->form->setStoreInSession(true);
		
		if ($id != -1)
		{
			$this->form->setMultipleFormSessionId($id);
		}
		
		$this->form->setMultipleFormSession(true);
		
		
		if ($id == -1)
		{
			$_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action'] = array();
			//$_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['opportunity'] = array();
		}
		
		$this->form->get("attachment")->setNextAction("material_" . $this->form->getMultipleFormSessionId());
		
		$this->form->loadSessionData();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['loadedFromDatabase']))
		{
			$this->loadedFromDatabase = true;
		}
	
		
		$this->loadSessionActions();
		//$this->loadSessionOpportunities();

		$this->form->processDependencies();
	}
	
	public function processHierarchy()
	{
		$product_range = $this->form->get("product_range")->getValue();
		$product_hierarchy_1 = $this->form->get("product_hierarchy_1")->getValue();
		$product_hierarchy_2 = $this->form->get("product_hierarchy_2")->getValue();
		$product_hierarchy_3 = $this->form->get("product_hierarchy_3")->getValue();
		$hierarchy_derived_key = $this->form->get("hierarchy_derived_key")->getValue();
		
		
		if ($product_hierarchy_1 != "")
		{
			$this->form->get("product_hierarchy_1")->setSQLSource("SAP","SELECT DISTINCT product_hierarchy_1 AS name, product_hierarchy_1 AS data FROM `material_group` WHERE (product_range = '" . $product_range . "') ORDER BY product_hierarchy_1");
		}
		
		if ($product_hierarchy_2 != "")
		{
			$this->form->get("product_hierarchy_2")->setSQLSource("SAP","SELECT DISTINCT product_hierarchy_2 AS name, product_hierarchy_2 AS data FROM `material_group` WHERE (product_range = '" . $product_range . "') AND (product_hierarchy_1 = '" .$product_hierarchy_1 . "') ORDER BY product_hierarchy_1");
		}
		
		if ($product_hierarchy_3 != "")
		{
			$this->form->get("product_hierarchy_3")->setSQLSource("SAP","SELECT DISTINCT product_hierarchy_3 AS name, product_hierarchy_3 AS data FROM `material_group` WHERE (product_range = '" . $product_range . "') AND (product_hierarchy_1 = '" .$product_hierarchy_1 . "') AND (product_hierarchy_2 = '" .$product_hierarchy_2 . "') ORDER BY product_hierarchy_1");
		}
		
		if ($hierarchy_derived_key != "")
		{
			$this->form->get("hierarchy_derived_key")->setSQLSource("SAP","SELECT DISTINCT CONCAT(`key`, ' [', `product_description`, ']') AS value, `key` AS name FROM `material_group` WHERE (product_range = '" . $product_range . "') AND (product_hierarchy_1 = '" .$product_hierarchy_1 . "') AND (product_hierarchy_2 = '" .$product_hierarchy_2 . "') AND (product_hierarchy_3 = '" .$product_hierarchy_3 . "') ORDER BY `key`");
		}
	}
	
	public function getLogOutput()
	{
		$xml = "<log>";
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->execute(sprintf("SELECT * FROM `log` WHERE `ccrId`=%u AND `area` ='material' ORDER BY `logDate` DESC", 
			$this->getId()
		));
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$xml .= "<item>";
			$xml .= "<area>" . $fields['area'] . "</area>\n";
			$xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
			$xml .= "<date>" . page::transformDateTimeForPHP($fields['logDate']) . "</date>\n";
			$xml .= "<action>" . $fields['action'] . "</action>\n";
			$xml .= "</item>";
		}
		
		$xml .= "</log>";
		
		return $xml;
	}
	
	
	function setCcrId($id)
	{
		$this->ccrId = $id;
		$this->form->get('ccrId')->setValue($id);
		//$this->form->putValuesInSession();
	}
	
	
	public function load($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM material WHERE id = $id");
				
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['loadedFromDatabase'] = true;
				
			$this->form->setDatabaseId($id);
	
			// load data into array
			$fields = mysql_fetch_array($dataset);
			
			// populate form items
			$this->form->populate($fields);
			
			
			$this->form->get("attachment")->load("/apps/ccr/attachments/materials/" . $this->form->getDatabaseId() . "/");
		
			
			// sort out the data
			$this->form->get("volume")->setValue(array($fields['volume_quantity'], $fields['volume_measurement']));	
			
			
			if ($this->form->get("isSapProduct")->getValue() == 'yes' && strlen($this->form->get("materialKey")->getValue()) > 0)
			{
				$this->form->get("knownMaterialKey")->setValue("yes");
				
				
				// get the hierarchy
				$hierarchy = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM material_group WHERE `key` = '" . $this->form->get("materialKey")->getValue() . "'");
			
				if ($hierarchyFields = mysql_fetch_array($hierarchy))
				{
					$this->form->get("product_range")->setValue($hierarchyFields['product_range']);
					$this->form->get("product_hierarchy_1")->setValue($hierarchyFields['product_hierarchy_1']);
					$this->form->get("product_hierarchy_2")->setValue($hierarchyFields['product_hierarchy_2']);
					$this->form->get("product_hierarchy_3")->setValue($hierarchyFields['product_hierarchy_3']);
					$this->form->get("hierarchy_derived_key")->setValue($this->form->get("materialKey")->getValue());
				}
		
			}
			else 
			{
				$this->form->get("knownMaterialKey")->setValue("no");
			}
			
			
			
					
			
						
			
			$actionDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM action WHERE parentId = $id AND type='material' ORDER BY id");
		
			while($actionFields = mysql_fetch_array($actionDataset))
			{
				$id = count($this->actions);
				$this->actions[] = new materialaction($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action']);
				$this->actions[$id]->load($actionFields['id']);
				$this->actions[$id]->setMaterialId($this->id);
			}
			
			$this->form->putValuesInSession();
			
			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			return 0;
		}
	}
	
		
	public function save()
	{
		if ($this->form->get('knownMaterialKey')->getValue() == 'no')
		{
			$this->form->get('materialKey')->setValue($this->form->get('hierarchy_derived_key')->getValue());
			$this->form->get('materialKey')->setVisible(true);
		}
		
		
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE material " . $this->form->generateUpdateQuery() . " WHERE id='" . $this->form->getDatabaseId() . "'");
		}
		else 
		{
			// begin transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO material " . $this->form->generateInsertQuery());
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM material ORDER BY id DESC LIMIT 1");
			
			// finish transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("COMMIT");
			
			
			
			
			$fields = mysql_fetch_array($dataset);
			
			$this->form->setDatabaseId($fields['id']);
		}
		
		
		$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/materials/" . $this->form->getDatabaseId() . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
		
		
		
		$actionList = array();
		
		for ($i=0; $i < count($this->actions); $i++)
		{
			$this->actions[$i]->setMaterialId($this->form->getDatabaseId());
			$this->actions[$i]->save();
			
			$actionList[] = $this->actions[$i]->form->getDatabaseId();
		}
		
		if (count($actionList) > 0)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=" . $this->form->getDatabaseId() . " AND type='material' AND NOT id IN (" . implode(",",$actionList) . ")");
		}
		else 
		{
			// delete all
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=" . $this->form->getDatabaseId() . " AND type='material'");
		}
		
		
		/*for ($i=0; $i < count($this->opportunities); $i++)
		{
			$this->opportunities[$i]->setMaterialId($this->form->getDatabaseId());
			$this->opportunities[$i]->save();
		}*/
		
	}
	
	
	public function addAction()
	{
		$id = count($this->actions);
		$this->actions[] = new materialaction($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action']);

		return $id;
	}
	
	public function removeAction($id)
	{
		for ($i=$id; $i < count($this->actions); $i++)
		{
			if (isset($this->actions[$i+1]))
			{
				$this->actions[$i] = $this->actions[$i+1];
				$this->actions[$i]->id = $i;
				$this->actions[$i]->form->setMultipleFormSessionId($i);
				
				$_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action'][$i] = $_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action'][$i+1];
			}
			else 
			{
				unset($this->actions[$i]);
				unset ($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action'][$i]);
			}
			
		}
	}
	
	public function getAction($id)
	{
		return $this->actions[$id];
	}
	
	public function getActions()
	{
		reset($this->actions);

		return $this->actions;
	}
	
	
	public function loadSessionActions()
	{
		$this->actions = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action']); $i++)
			{
				$this->actions[] = new materialaction($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['action'], $i);				
			}
		}
	}
	
	
	public function validate()
	{
		$valid = true;
		
		if (!$this->form->validate())
		{
			$valid = false;
		}
		
		for ($i=0; $i < count($this->actions); $i++)
		{
			if (!$this->getAction($i)->form->validate())
			{
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	/*
	
	public function addOpportunity()
	{
		$id = count($this->opportunities);
		$this->opportunities[] = new opportunity($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['opportunity']);

		return $id;
	}
	
	public function getOpportunity($id)
	{
		return $this->opportunities[$id];
	}
	
	public function getOpportunities()
	{
		return $this->opportunities;
	}
	
	
	public function loadSessionOpportunities()
	{
		$this->opportunities = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['opportunity']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['opportunity']); $i++)
			{
				$this->opportunities[] = new opportunity($_SESSION['apps'][$GLOBALS['app']]['material'][$this->form->getMultipleFormSessionId()]['opportunity'], $i);				
				page::addDebug("load opp.", __FILE__, __LINE__);
			}
		}
	}
	*/
	
	
	
	
	private function defineForm()
	{
		$this->form = new form("material");
		$this->form->showLegend(true);
		
		
		$material = new group("material");
		$sap = new group("sap");
		$sap->setBorder(false);
		
		$sapCode = new group("sapCode");
		$sapHierarchy = new group("sapHierarchy");
		
		
		$nonsap = new group("nonsap");
		
		
		$discussion = new group("discussion");
		$competitor = new group("competitor");
		$success = new group("success");
		$sales = new group("sales");
		
		
		$ccrId = new invisibletext("ccrId");
		$ccrId->setGroup("material");
		$ccrId->setDataType("number");
		$ccrId->setRequired(false);
		$ccrId->setValue(0);
		$ccrId->setVisible(false);
		$material->add($ccrId);
	
		
		$isSapProduct = new radio("isSapProduct");
		$isSapProduct->setGroup("material");
		$isSapProduct->setDataType("string");
		$isSapProduct->setLength(50);
		$isSapProduct->setRequired(true);
		$isSapProduct->setTranslate(true);
		$isSapProduct->setHelpId(23);
		$isSapProduct->setValue('yes');
		$isSapProduct->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		
		$isSapProduct->setRowTitle("is_sap_product");
		
		
		$sapDependency = new dependency();
		$sapDependency->addRule(new rule('material','isSapProduct', 'yes'));
		$sapDependency->setGroup('sap');
		$sapDependency->setShow(true);
		
		$isSapProduct->addControllingDependency($sapDependency);
		
		
		$nonSapDependency = new dependency();
		$nonSapDependency->addRule(new rule('material','isSapProduct', 'no'));
		$nonSapDependency->setGroup('nonsap');
		$nonSapDependency->setShow(true);
		
		$isSapProduct->addControllingDependency($nonSapDependency);
		
		
		$sapCodeDependencyParent = new dependency();
		$sapCodeDependencyParent->addRule(new rule('material', 'isSapProduct', 'yes'));
		$sapCodeDependencyParent->addRule(new rule('sap', 'knownMaterialKey', 'yes'));
		$sapCodeDependencyParent->setGroup('sapCode');
		$sapCodeDependencyParent->setShow(true);
		
		$isSapProduct->addControllingDependency($sapCodeDependencyParent);
		
		$sapHierarchyParent = new dependency();
		$sapHierarchyParent->addRule(new rule('material', 'isSapProduct', 'yes'));
		$sapHierarchyParent->addRule(new rule('sap', 'knownMaterialKey', 'no'));
		$sapHierarchyParent->setGroup('sapHierarchy');
		$sapHierarchyParent->setShow(true);
		
		$isSapProduct->addControllingDependency($sapHierarchyParent);
		
		
		$material->add($isSapProduct);
		
		
		
		$nonSapMaterialKey = new textbox("alternativeMaterialKey");
		$nonSapMaterialKey->setGroup("nonsap");
		$nonSapMaterialKey->setDataType("string");
		$nonSapMaterialKey->setLength(50);
		$nonSapMaterialKey->setRequired(true);
		$nonSapMaterialKey->setRowTitle("non_sap_material_key");
		
		$nonsap->add($nonSapMaterialKey);
		
		
		
		$knownMaterialKey = new radio("knownMaterialKey");
		$knownMaterialKey->setGroup("sap");
		$knownMaterialKey->setDataType("string");
		$knownMaterialKey->setLength(50);
		$knownMaterialKey->setRequired(true);
		$knownMaterialKey->setIgnore(true);
		$knownMaterialKey->setHelpId(24);
		$knownMaterialKey->setArraySource(array(
			array('value' => 'yes', 'display' => 'SAP code'),
			array('value' => 'no', 'display' => 'SAP product hierarchy')
		));
		$knownMaterialKey->setValue('yes');
		$knownMaterialKey->setRowTitle("sap_material_key_selector");
		
		
		
		
		
		$sapCodeDependency = new dependency();
		$sapCodeDependency->addRule(new rule('material', 'isSapProduct', 'yes'));
		$sapCodeDependency->addRule(new rule('sap', 'knownMaterialKey', 'yes'));
		$sapCodeDependency->setGroup('sapCode');
		$sapCodeDependency->setShow(true);
		
		$knownMaterialKey->addControllingDependency($sapCodeDependency);
		
		
		$sapHierarchyDependency = new dependency();
		$sapHierarchyDependency->addRule(new rule('material', 'isSapProduct', 'yes'));
		$sapHierarchyDependency->addRule(new rule('sap', 'knownMaterialKey', 'no'));
		$sapHierarchyDependency->setGroup('sapHierarchy');
		$sapHierarchyDependency->setShow(true);
		
		$knownMaterialKey->addControllingDependency($sapHierarchyDependency);
		
		
		$sap->add($knownMaterialKey);
		
		
		$key = new autocomplete("materialKey");
		$key->setGroup("sapCode");
		$key->setDataType("string");
		$key->setLength(10);
		$key->setUrl('/apps/ccr/ajax/materialgroup?');
		$key->setValidateQuery("SAP", "material_group", "key");
		$key->setRowTitle("sap_material_group_key");
		$key->setRequired(true);
		$key->setHelpId(25);
		$sapCode->add($key);
		
		$product_range = new dropdown("product_range");
		$product_range->setGroup("sapHierarchy");
		//$product_range->setDataType("string");
		$product_range->setIgnore(true);
		$product_range->setLength(250);
		$product_range->setRowTitle("product_family");
		$product_range->setHelpId(26);
		$product_range->clearData();
		$product_range->setArraySource(array(array('value' => 'Please select...', 'display' => 'Please select...')));
		$product_range->setSQLSource("SAP","SELECT DISTINCT product_range AS name, product_range AS data FROM material_group ORDER BY product_range");
		//$product_range->setRequired(true);
		//$product_range->setPostback(true);
		$product_range->setOnChange("update_product_family();");
		$sapHierarchy->add($product_range);
		
		$product_hierarchy_1 = new dropdown("product_hierarchy_1");
		$product_hierarchy_1->setGroup("sapHierarchy");
		$product_hierarchy_1->setIgnore(true);
		//$product_hierarchy_1->setDataType("string");
		$product_hierarchy_1->setLength(250);
		$product_hierarchy_1->clearData();
		$product_hierarchy_1->setOnChange("update_product_hierarchy_1();");
		$product_hierarchy_1->setRowTitle("product_hierarchy_level_1");

		$sapHierarchy->add($product_hierarchy_1);
		
		$product_hierarchy_2 = new dropdown("product_hierarchy_2");
		$product_hierarchy_2->setGroup("sapHierarchy");
		$product_hierarchy_2->setIgnore(true);
		//$product_hierarchy_2->setDataType("string");
		$product_hierarchy_2->setLength(250);
		$product_hierarchy_2->clearData();
		$product_hierarchy_2->setOnChange("update_product_hierarchy_2();");
		$product_hierarchy_2->setRowTitle("product_hierarchy_level_2");

		$sapHierarchy->add($product_hierarchy_2);
		
		$product_hierarchy_3 = new dropdown("product_hierarchy_3");
		$product_hierarchy_3->setGroup("sapHierarchy");
		$product_hierarchy_3->setIgnore(true);
		//$product_hierarchy_3->setDataType("string");
		$product_hierarchy_3->setLength(250);
		$product_hierarchy_3->clearData();
		$product_hierarchy_3->setOnChange("update_product_hierarchy_3();");
		$product_hierarchy_3->setRowTitle("product_hierarchy_level_3");

		$sapHierarchy->add($product_hierarchy_3);
		
		
	/*	$product_hierarchy_key = new readonly("product_hierarchy_key");
		$product_hierarchy_key->setGroup("sapHierarchy");
		$product_hierarchy_key->setDataType("string");
		$product_hierarchy_key->setLength(250);
		$product_hierarchy_key->setRowTitle("product_hierarchy_key");

		$sapHierarchy->add($product_hierarchy_key);
		
		$product_hierarchy_description = new readonly("product_hierarchy_description");
		$product_hierarchy_description->setGroup("sapHierarchy");
		$product_hierarchy_description->setDataType("string");
		$product_hierarchy_description->setLength(250);
		$product_hierarchy_description->setRowTitle("product_hierarchy_description");

		$sapHierarchy->add($product_hierarchy_description);*/
		
		$hierarchy_derived_key = new dropdown("hierarchy_derived_key");
		$hierarchy_derived_key->setGroup("sapHierarchy");
		$hierarchy_derived_key->setIgnore(true);
		//$hierarchy_derived_key->setDataType("string");
		$hierarchy_derived_key->setLength(250);
		$hierarchy_derived_key->clearData();
		$hierarchy_derived_key->setRequired(true);
		$hierarchy_derived_key->setRowTitle("hierarchy_derived_key");

		$sapHierarchy->add($hierarchy_derived_key);
		
		
		
		
		$discussionSubject = new dropdownAlternative("discussionSubject");
		$discussionSubject->setXMLSource("./apps/ccr/xml/discussionSubject.xml");
		$discussionSubject->setTranslate(true);
		$discussionSubject->setGroup("discussion");
		$discussionSubject->setDataType("string");
		$discussionSubject->setLength(255);
		$discussionSubject->setHelpId(27);
		$discussionSubject->setRowTitle("subject_of_discussion");
		$discussion->add($discussionSubject);
		
		$application = new textarea("application");
		$application->setGroup("discussion");
		$application->setDataType("text");
		$application->setRequired(false);
		$application->setHelpId(28);
		//$application->setLength(255);
		$application->setRowTitle("material_group_application");
		$discussion->add($application);
		
		$income = new textbox("incomeQuantity");
		$income->setGroup("discussion");
		$income->setDataType("number");
		$income->setValue("0");
		$income->setRequired(false);
		$income->setLength(10);
		$income->setHelpId(29);
		$income->setRowTitle("annual_potential_in_invoice_currency");
		$discussion->add($income);
		
		
		$volume = new measurement("volume");
		$volume->setGroup("discussion");
		$volume->setDataType("string");
		$volume->setValue("0");
		$volume->setLength(10);
		$volume->setHelpId(30);
		$volume->setXMLSource("./apps/ccr/xml/units.xml");
		$volume->setRowTitle("annual_potential_in_volume");
		$discussion->add($volume);
		
		
		$estimatedShare = new radio("estimatedShare");
		//$estimatedShare->setTable("customer");
		$estimatedShare->setDataType("string");
		$estimatedShare->setRequired(true);
		$estimatedShare->setTranslate(true);
		$estimatedShare->setLength(50);
		$estimatedShare->setArraySource(array(
			array('value' => '75', 'display' => '75'),
			array('value' => '50-75', 'display' => '50-75'),
			array('value' => '25-50', 'display' => '25-50'),
			array('value' => '25', 'display' => '25')
		));
		/*
		array('value' => '75', 'display' => 'More than 75%'),
			array('value' => '50-75', 'display' => '50 - 75%'),
			array('value' => '25-50', 'display' => '25 - 50%'),
			array('value' => '25', 'display' => 'Less than 25%')
			*/
		$estimatedShare->setRowTitle("estimated_Share");
		$discussion->add($estimatedShare);
		
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ccr/tmp");
		$attachment->setFinalFileLocation("/apps/ccr/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("report");
		$discussion->add($attachment);
		
		
		$competitorName = new comboAlternative("competitorName");
		$competitorName->setXMLSource("./apps/ccr/xml/competitorName.xml");
		$competitorName->setGroup("competitor");
		$competitorName->setDataType("string");
		$competitorName->setLength(100);
		$competitorName->setHelpId(31);
		$competitorName->setRowTitle("competitor_name");
		$competitor->add($competitorName);
		
		$competitorProductCode = new textbox("competitorProductCode");
		$competitorProductCode->setGroup("competitor");
		$competitorProductCode->setDataType("string");
		$competitorProductCode->setRequired(false);
		$competitorProductCode->setLength(100);
		$competitorProductCode->setHelpId(32);
		$competitorProductCode->setRowTitle("competitor_product_code_and_description");
		$competitor->add($competitorProductCode);
		
		$competitorTerms = new textarea("competitorTerms");
		$competitorTerms->setGroup("competitor");
		$competitorTerms->setDataType("text");
		$competitorTerms->setRequired(false);
		$competitorTerms->setHelpId(33);
		//$competitorTerms->setLength(255);
		$competitorTerms->setRowTitle("competitor_product_price_etc");
		$competitor->add($competitorTerms);
		
		$competitorActivity = new textarea("competitorActivity");
		$competitorActivity->setGroup("competitor");
		$competitorActivity->setDataType("text");
		$competitorActivity->setRequired(false);
		$competitorActivity->setHelpId(34);
		//$competitorActivity->setLength(255);
		$competitorActivity->setRowTitle("general_competitor_activity");
		$competitor->add($competitorActivity);
		
		
		
		$successChance = new dropdown("successChance");
		$successChance->setXMLSource("./apps/ccr/xml/successProbability.xml");
		$successChance->setTranslate(true);
		$successChance->setGroup("success");
		$successChance->setDataType("string");
		$successChance->setTranslate(true);
		$successChance->setLength(100);
		$successChance->setHelpId(42);
		$successChance->setRowTitle("probability_of_success");
		$success->add($successChance);
		
		$successReport = new textarea("successReport");
		$successReport->setGroup("success");
		$successReport->setDataType("text");
		$successReport->setHelpId(35);
		$successReport->setRequired(false);
		//$successReport->setLength(255);
		$successReport->setRowTitle("material_group_report_narrative");
		$success->add($successReport);
		
		
		
		$salesStage = new dropdownAlternative("salesStage");
		$salesStage->setXMLSource("./apps/ccr/xml/salesProcessStage.xml");
		$salesStage->setTranslate(true);
		$salesStage->setGroup("sales");
		$salesStage->setDataType("string");
		$salesStage->setLength(100);
		$salesStage->setHelpId(36);
		$salesStage->setRowTitle("sales_process_stage");
		$sales->add($salesStage);
		
		
		$reasonForGainLoss = new comboAlternative("reasonForGainLoss");
		$reasonForGainLoss->setXMLSource("./apps/ccr/xml/businessGoL.xml");
		$reasonForGainLoss->setTranslate(true);
		$reasonForGainLoss->setGroup("sales");
		$reasonForGainLoss->setDataType("string");
		$reasonForGainLoss->setHelpId(37);
		$reasonForGainLoss->setLength(100);
		$reasonForGainLoss->setRowTitle("reason_for_business_gain_or_loss");
		$sales->add($reasonForGainLoss);
		
		
		$commentOnGainLoss = new textarea("commentOnGainLoss");
		$commentOnGainLoss->setGroup("sales");
		$commentOnGainLoss->setDataType("text");
		$commentOnGainLoss->setRequired(false);
		$commentOnGainLoss->setHelpId(38);
		//$commentOnGainLoss->setLength(255);
		$commentOnGainLoss->setRowTitle("comments_on_business_gain_or_loss");
		$sales->add($commentOnGainLoss);
		
		
		$this->form->add($material);
		$this->form->add($nonsap);
		$this->form->add($sap);
		$this->form->add($sapCode);
		$this->form->add($sapHierarchy);
		$this->form->add($discussion);
		$this->form->add($competitor);
		$this->form->add($success);
		$this->form->add($sales);
	}
}

?>