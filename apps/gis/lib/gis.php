<?php

require 'gisProcess.php';
/**
 * This is the gis (Global Information System) Application.
 *
 * This is the gis class.  This class has a small initiation form and controls the other parts of the gis process.
 * 
 * @package apps	
 * @subpackage gis
 * @copyright Scapa Ltd.
 * @author Jason Matthews & David Pickwell
 * @version 10/11/2008
 */
class gis
{
	private $id;
	private $status;
	public $form;

	public $attachments;

	private $loadedFromDatabase = false;

	function __construct()
	{
		
		$this->defineForm();
		
		$this->form->loadSessionData();	//puts any data in the session back in the form
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the gis is loaded from the database
		}

		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id'];		//checks if there is a gis id in the session
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = "";
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['complete']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = false;
		}

		$this->loadSessionSections();		//loads any of the GIS sections that are stored in the session

		$this->form->processDependencies();
	}
	
	private function loadSessionSections()		
	{
		if (isset($_SESSION['apps'][$GLOBALS['app']]['addNewSection']))
		{
			$this->addNewSection = new addNewSection($this);
		}
	}

	public function load($id)
	{
		page::addDebug("loading gis id=$id", __FILE__, __LINE__);
		
		if (!is_numeric($id))
		{
			return false;
		}
		
		$this->id = $id;
		
		$this->form->setStoreInSession(true);
		
		$dataset = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase'] = true;
			
			$fields = mysql_fetch_array($dataset);

			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;

			if($fields['profileType'] == "market")
			{
				$this->form->get('m_profileName')->setValue($fields['profileName']);
				$this->form->get('m_profileNameRO')->setValue($fields['profileName']);
			}
			else 
			{
				$this->form->get('c_profileName')->setValue($fields['profileName']);
				$this->form->get('c_profileNameRO')->setValue($fields['profileName']);
			}

			$this->form->get("initiatorRO")->setValue(usercache::getInstance()->get($this->form->get('initiator')->getValue())->getName());

			$this->form->get('profileTypeRO')->setValue(translate::getInstance()->translate($this->form->get('profileType')->getValue()));
			
			$this->form->populate($fields);			
			
			$this->form->get("attachment")->load("/apps/gis/attachments/" . $this->id . "/");
			
			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
		}		

		return true;
	}


	public function getID()
	{
		return $this->form->get("id")->getValue();
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



	public function save($process)
	{
		page::addDebug("Saving gis process: ".$process,__FILE__,__LINE__);
		
		switch ($process)
		{
			case 'gis':
				
				$this->determineStatus();
								
				if ($this->loadedFromDatabase)
				{
					$this->form->get("dateUpdated")->setValue(date("Y-m-d",time()));
					$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
					
					$this->form->get("attachment")->setFinalFileLocation("/apps/gis/attachments/" . $this->id . "/");
					$this->form->get("attachment")->moveTempFileToFinal();		
					
					// update
					mysql::getInstance()->selectDatabase("gis")->Execute("UPDATE gis " . $this->form->generateUpdateQuery("gis") . " WHERE id='" . $this->getID() . "'");
					page::addDebug("Checking if updating gis table", __FILE__, __LINE__);
		
					// save new data to the log and clear the comments field.
					$this->addLog(translate::getInstance()->translate("gis_report_updated"), $this->form->get('comments')->getValue());	
					$this->form->get('comments')->setValue("");	
					
					// getEmailNotification($template, $sendTo, $gisId, $status, $sender)
					if($this->form->get('initiator')->getValue() == $this->form->get('owner')->getValue())
					{
						//$this->getEmailNotification("newUpdate", $this->form->get("id")->getValue(), currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon(), $this->form->get("profileName")->getValue(), $this->form->get("comments")->getValue());
					}
					else 
					{
						//$this->getEmailNotification("newUpdate", $this->form->get("id")->getValue(), currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon(), $this->form->get("profileName")->getValue(), $this->form->get("comments")->getValue());
						//$this->getEmailNotification("notificationOfUpdate", $this->form->get("id")->getValue(), $this->form->get('initiator')->getValue(), currentuser::getInstance()->getNTLogon(), $this->form->get("profileName")->getValue(), $this->form->get("comments")->getValue());
					}
					
					
				}
				else
				{
					if($this->form->get('profileType')->getValue() == "market")
					{
						$this->form->get('profileName')->setValue($this->form->get('m_profileName')->getValue());
					}
					else 
					{
						$this->form->get('profileName')->setValue($this->form->get('c_profileName')->getValue());
					}
					
					
					
					
					// begin transaction
					mysql::getInstance()->selectDatabase("gis")->Execute("BEGIN");
					
					// insert
					mysql::getInstance()->selectDatabase("gis")->Execute("INSERT INTO gis " . $this->form->generateInsertQuery("gis"));
				
					// get last inserted
					$dataset = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT id FROM gis ORDER BY id DESC LIMIT 1");
					
					$fields = mysql_fetch_array($dataset);
		
					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);
		
					// end transaction
					mysql::getInstance()->selectDatabase("gis")->Execute("COMMIT");
		
					$this->form->get("attachment")->setFinalFileLocation("/apps/gis/attachments/" . $this->id . "/");
					$this->form->get("attachment")->moveTempFileToFinal();
					
					$this->addLog(translate::getInstance()->translate("gis_submitted"), $this->form->get('comments')->getValue());
					
					// Notify the initiator of the submission.
					// getEmailNotification($template, $gisId, $sendTo, $sender, $profileName, $comments)
					//$this->getEmailNotification("newProfile", $this->form->get("id")->getValue(), currentuser::getInstance()->getNTLogon(), currentuser::getInstance()->getNTLogon(), $this->form->get("profileName")->getValue(), $this->form->get("comments")->getValue());

				}			
			break;	
		}
		
					
		page::redirect("/apps/gis/index?id=" . $this->getID() . "");  //redirects the page back to the summary		
	}
	

	public function determineStatus()
	{
		$location = "gis";
		$this->status = $location;
		$this->form->get('status')->setValue($location);
	}
	
	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}

	public function addLog($action, $comments)
	{
		mysql::getInstance()->selectDatabase("gis")->Execute(sprintf("INSERT INTO log (gisId, NTLogon, action, logDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$this->getID(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comments
		));
	}

	public function getOwner()
	{
		return $this->form->get("owner")->getValue();
	}
	
	public function getInitiator()
	{
		return $this->form->get("initiator")->getValue();
	}
	
	public function defineForm()
	{
		
		//echo $this->form->id;
		
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("initiation");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		// define the groups
		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$typeCompetitor = new group("typeCompetitor");
		$typeCompetitor->setBorder(false);
		$typeMarket = new group("typeMarket");
		$typeMarket->setBorder(false);
		$attachmentGroup = new group("attachmentGroup");
		$attachmentGroup->setBorder(true);
		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);
		
		$id = new textbox("id");
		$id->setTable("gis");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setRowTitle("id");
		$id->setDataType("number");
		$initiation->add($id);
		
		$initiatorRO = new readonly("initiatorRO");
		$initiatorRO->setVisible(true);
		$initiatorRO->setDataType("string");
		$initiatorRO->setRequired(false);
		$initiatorRO->setLength(50);
		$initiatorRO->setRowTitle("initiator");
		$initiatorRO->setValue(currentuser::getInstance()->getName());
		$initiation->add($initiatorRO);
		
		$initiator = new textbox("initiator");
		$initiator->setTable("gis");
		$initiator->setVisible(false);
		$initiator->setDataType("string");
		$initiator->setRequired(false);
		$initiator->setLength(50);
		$initiator->setRowTitle("initiator");
		$initiator->setValue(currentuser::getInstance()->getNTLogon());
		$initiation->add($initiator);
		
		$profileName = new textbox("profileName");
		$profileName->setTable("gis");
		$profileName->setVisible(false);
		$profileName->setRowTitle("competitor_name");
		$profileName->setDataType("string");
		$profileName->setRequired(true);
		$profileName->setLength(255);
		$initiation->add($profileName);
		
		$owner = new textbox("owner");
		$owner->setTable("gis");
		$owner->setVisible(false);
		$owner->setDataType("string");
		$owner->setRequired(false);
		$owner->setLength(50);
		$owner->setRowTitle("current_owner");
		$owner->setValue(currentuser::getInstance()->getNTLogon());
		$initiation->add($owner);
		
		$dateAdded = new textbox("dateAdded");
		$dateAdded->setTable("gis");
		$dateAdded->setVisible(false);
		$dateAdded->setDataType("string");
		$dateAdded->setRequired(false);
		$dateAdded->setLength(50);
		$dateAdded->setRowTitle("date_added");
		$dateAdded->setValue($today);
		$initiation->add($dateAdded);
		
		$dateUpdated = new textbox("dateUpdated");
		$dateUpdated->setTable("gis");
		$dateUpdated->setVisible(false);
		$dateUpdated->setDataType("string");
		$dateUpdated->setRequired(false);
		$dateUpdated->setLength(50);
		$dateUpdated->setRowTitle("date_updated");
		$dateUpdated->setValue($today);
		$initiation->add($dateUpdated);
		
		$status = new textbox("status");
		$status->setTable("gis");
		$status->setVisible(false);
		$status->setDataType("string");
		$status->setRequired(false);
		$status->setLength(50);
		$status->setRowTitle("status");
		$initiation->add($status);

		$profileTypeRO = new readonly("profileTypeRO");
		$profileTypeRO->setDataType("string");
		$profileTypeRO->setRequired(false);
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$profileTypeRO->setVisible(true);
		}
		else 
		{
			$profileTypeRO->setVisible(false);
		}
		$profileTypeRO->setLength(50);
		$profileTypeRO->setRowTitle("profile_type");
		$initiation->add($profileTypeRO);
		
		$profileType = new radio("profileType");
		$profileType->setGroup("start");
		$profileType->setTable("gis");
		$profileType->setRowTitle("profile_type");
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$profileType->setVisible(false);
		}
		$profileType->setArraySource(array(
			array('value' => 'competitor', 'display' => 'Competitor'),
			array('value' => 'market', 'display' => 'Market')
		));
		$profileType->setRequired(true);
		$profileType->setValue("competitor");
		
		$typeMarketDependency = new dependency();
		$typeMarketDependency->addRule(new rule('initiation','profileType','market'));
		$typeMarketDependency->setGroup('typeMarket');
		$typeMarketDependency->setShow(true);

		$typeCompetitorDependency = new dependency();
		$typeCompetitorDependency->addRule(new rule('initiation','profileType','competitor'));
		$typeCompetitorDependency->setGroup('typeCompetitor');
		$typeCompetitorDependency->setShow(true);
		
		$profileType->addControllingDependency($typeMarketDependency);
		$profileType->addControllingDependency($typeCompetitorDependency);
		$initiation->add($profileType);
		
		/*** Competitor Profile ***/
		
		$c_profileName = new textbox("c_profileName");
		$c_profileName->setRowTitle("profileName");
		$c_profileName->setDataType("string");
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$c_profileName->setVisible(false);
		}
		else 
		{
			$c_profileName->setVisible(true);
		}
		$c_profileName->setRequired(true);
		$c_profileName->setLength(255);
		$typeCompetitor->add($c_profileName);

		$c_profileNameRO = new readonly("c_profileNameRO");
		$c_profileNameRO->setRowTitle("profileName");
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$c_profileNameRO->setVisible(true);
		}
		else 
		{
			$c_profileNameRO->setVisible(false);
		}
		$c_profileNameRO->setDataType("string");
		$c_profileNameRO->setRequired(true);
		$c_profileNameRO->setLength(255);
		$typeCompetitor->add($c_profileNameRO);

		$c_website = new textbox("c_website");
		$c_website->setVisible(true);
		$c_website->setTable("gis");
		$c_website->setRowTitle("website");
		$c_website->setDataType("string");
		$c_website->setRequired(false);
		$c_website->setLength(255);
		$typeCompetitor->add($c_website);

		$c_background = new textarea("c_background");
		$c_background->setTable("gis");
		$c_background->setVisible(true);
		$c_background->setLargeTextarea(true);
		$c_background->setDataType("text");
		$c_background->setRequired(false);
		$c_background->setRowTitle("background");
		$typeCompetitor->add($c_background);
		
		$c_corporateStructure = new textarea("c_corporateStructure");
		$c_corporateStructure->setTable("gis");
		$c_corporateStructure->setLargeTextarea(true);
		$c_corporateStructure->setVisible(true);
		$c_corporateStructure->setDataType("text");
		$c_corporateStructure->setRequired(false);
		$c_corporateStructure->setRowTitle("corporateStructure");
		$typeCompetitor->add($c_corporateStructure);
		
		$c_financialHighlights = new textarea("c_financialHighlights");
		$c_financialHighlights->setTable("gis");
		$c_financialHighlights->setLargeTextarea(true);
		$c_financialHighlights->setVisible(true);
		$c_financialHighlights->setDataType("text");
		$c_financialHighlights->setRequired(false);
		$c_financialHighlights->setRowTitle("financialHighlights");
		$typeCompetitor->add($c_financialHighlights);
		
		$c_keyPersonnel = new textarea("c_keyPersonnel");
		$c_keyPersonnel->setTable("gis");
		$c_keyPersonnel->setVisible(true);
		$c_keyPersonnel->setDataType("text");
		$c_keyPersonnel->setRequired(false);
		$c_keyPersonnel->setLargeTextarea(true);
		$c_keyPersonnel->setRowTitle("keyPersonnel");
		$typeCompetitor->add($c_keyPersonnel);
		
		$c_marketSectorActivity = new textarea("c_marketSectorActivity");
		$c_marketSectorActivity->setTable("gis");
		$c_marketSectorActivity->setVisible(true);
		$c_marketSectorActivity->setLargeTextarea(true);
		$c_marketSectorActivity->setDataType("text");
		$c_marketSectorActivity->setRequired(false);
		$c_marketSectorActivity->setRowTitle("marketSectorActivity");
		$typeCompetitor->add($c_marketSectorActivity);
		
		$c_productRangeActivity = new textarea("c_productRangeActivity");
		$c_productRangeActivity->setTable("gis");
		$c_productRangeActivity->setVisible(true);
		$c_productRangeActivity->setLargeTextarea(true);
		$c_productRangeActivity->setDataType("text");
		$c_productRangeActivity->setRequired(false);
		$c_productRangeActivity->setRowTitle("productRangeActivity");
		$typeCompetitor->add($c_productRangeActivity);
		
		$c_newProducts = new textarea("c_newProducts");
		$c_newProducts->setTable("gis");
		$c_newProducts->setVisible(true);
		$c_newProducts->setLargeTextarea(true);
		$c_newProducts->setDataType("text");
		$c_newProducts->setRequired(false);
		$c_newProducts->setRowTitle("newProducts");
		$typeCompetitor->add($c_newProducts);
		
		$c_technicalComparisons = new textarea("c_technicalComparisons");
		$c_technicalComparisons->setTable("gis");
		$c_technicalComparisons->setVisible(true);
		$c_technicalComparisons->setLargeTextarea(true);
		$c_technicalComparisons->setDataType("text");
		$c_technicalComparisons->setRequired(false);
		$c_technicalComparisons->setRowTitle("technicalComparisons");
		$typeCompetitor->add($c_technicalComparisons);
		
		$c_currentPricingLevels = new textarea("c_currentPricingLevels");
		$c_currentPricingLevels->setTable("gis");
		$c_currentPricingLevels->setVisible(true);
		$c_currentPricingLevels->setLargeTextarea(true);
		$c_currentPricingLevels->setDataType("text");
		$c_currentPricingLevels->setRequired(false);
		$c_currentPricingLevels->setRowTitle("currentPricingLevels");
		$typeCompetitor->add($c_currentPricingLevels);
		
		$c_packagingAndBranding = new textarea("c_packagingAndBranding");
		$c_packagingAndBranding->setTable("gis");
		$c_packagingAndBranding->setVisible(true);
		$c_packagingAndBranding->setLargeTextarea(true);
		$c_packagingAndBranding->setDataType("text");
		$c_packagingAndBranding->setRequired(false);
		$c_packagingAndBranding->setRowTitle("packagingAndBranding");
		$typeCompetitor->add($c_packagingAndBranding);
		
		$c_serviceLevels = new textarea("c_serviceLevels");
		$c_serviceLevels->setTable("gis");
		$c_serviceLevels->setVisible(true);
		$c_serviceLevels->setLargeTextarea(true);
		$c_serviceLevels->setDataType("text");
		$c_serviceLevels->setRequired(false);
		$c_serviceLevels->setRowTitle("serviceLevels");
		$typeCompetitor->add($c_serviceLevels);
		
		$c_geographicActivity = new textarea("c_geographicActivity");
		$c_geographicActivity->setTable("gis");
		$c_geographicActivity->setVisible(true);
		$c_geographicActivity->setLargeTextarea(true);
		$c_geographicActivity->setDataType("text");
		$c_geographicActivity->setRequired(false);
		$c_geographicActivity->setRowTitle("geographicActivity");
		$typeCompetitor->add($c_geographicActivity);
		
		$c_activeAccounts = new textarea("c_activeAccounts");
		$c_activeAccounts->setTable("gis");
		$c_activeAccounts->setVisible(true);
		$c_activeAccounts->setLargeTextarea(true);
		$c_activeAccounts->setDataType("text");
		$c_activeAccounts->setRequired(false);
		$c_activeAccounts->setRowTitle("activeAccounts");
		$typeCompetitor->add($c_activeAccounts);
		
		$c_marketingActivity = new textarea("c_marketingActivity");
		$c_marketingActivity->setTable("gis");
		$c_marketingActivity->setVisible(true);
		$c_marketingActivity->setLargeTextarea(true);
		$c_marketingActivity->setDataType("text");
		$c_marketingActivity->setRequired(false);
		$c_marketingActivity->setRowTitle("marketingActivity");
		$typeCompetitor->add($c_marketingActivity);
		
		$c_strengthWeakness = new textarea("c_strengthWeakness");
		$c_strengthWeakness->setTable("gis");
		$c_strengthWeakness->setVisible(true);
		$c_strengthWeakness->setLargeTextarea(true);
		$c_strengthWeakness->setDataType("text");
		$c_strengthWeakness->setRequired(false);
		$c_strengthWeakness->setRowTitle("strengthWeakness");
		$typeCompetitor->add($c_strengthWeakness);
		
		$c_currentStrategy = new textarea("c_currentStrategy");
		$c_currentStrategy->setTable("gis");
		$c_currentStrategy->setVisible(true);
		$c_currentStrategy->setLargeTextarea(true);
		$c_currentStrategy->setDataType("text");
		$c_currentStrategy->setRequired(false);
		$c_currentStrategy->setRowTitle("currentStrategy");
		$typeCompetitor->add($c_currentStrategy);
		
		$c_informationSources = new textarea("c_informationSources");
		$c_informationSources->setTable("gis");
		$c_informationSources->setVisible(true);
		$c_informationSources->setLargeTextarea(true);
		$c_informationSources->setDataType("text");
		$c_informationSources->setRequired(false);
		$c_informationSources->setRowTitle("informationSources");
		$typeCompetitor->add($c_informationSources);
		
		$c_distributionStrategy = new textarea("c_distributionStrategy");
		$c_distributionStrategy->setTable("gis");
		$c_distributionStrategy->setVisible(true);
		$c_distributionStrategy->setLargeTextarea(true);
		$c_distributionStrategy->setDataType("text");
		$c_distributionStrategy->setRequired(false);
		$c_distributionStrategy->setRowTitle("distributionStrategy");
		$typeCompetitor->add($c_distributionStrategy);
		
		$c_summary = new textarea("c_summary");
		$c_summary->setTable("gis");
		$c_summary->setVisible(true);
		$c_summary->setLargeTextarea(true);
		$c_summary->setDataType("text");
		$c_summary->setRequired(false);
		$c_summary->setRowTitle("summary");
		$typeCompetitor->add($c_summary);
		
		/*** Market Profile ***/
		
		$m_profileName = new textbox("m_profileName");
		$m_profileName->setVisible(true);
		$m_profileName->setRowTitle("profileName");
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$m_profileName->setVisible(false);
		}
		else 
		{
			$m_profileName->setVisible(true);
		}
		$m_profileName->setDataType("string");
		$m_profileName->setRequired(true);
		$m_profileName->setLength(255);
		$typeMarket->add($m_profileName);

		$m_profileNameRO = new readonly("m_profileNameRO");
		$m_profileNameRO->setVisible(false);
		if(isset($_SESSION['apps'][$GLOBALS['app']]['gis']['loadedFromDatabase']))
		{
			$m_profileNameRO->setVisible(true);
		}
		else 
		{
			$m_profileNameRO->setVisible(false);
		}
		$m_profileNameRO->setRowTitle("profileName");
		$m_profileNameRO->setDataType("string");
		$m_profileNameRO->setRequired(true);
		$m_profileNameRO->setLength(255);
		$typeMarket->add($m_profileNameRO);

		$m_sizeAndGrowth = new textarea("m_sizeAndGrowth");
		$m_sizeAndGrowth->setTable("gis");
		$m_sizeAndGrowth->setVisible(true);
		$m_sizeAndGrowth->setDataType("text");
		$m_sizeAndGrowth->setRequired(false);
		$m_sizeAndGrowth->setLargeTextarea(true);
		$m_sizeAndGrowth->setRowTitle("sizeAndGrowth");
		$typeMarket->add($m_sizeAndGrowth);
		
		$m_competitors = new textarea("m_competitors");
		$m_competitors->setTable("gis");
		$m_competitors->setVisible(true);
		$m_competitors->setLargeTextarea(true);
		$m_competitors->setDataType("text");
		$m_competitors->setRequired(false);
		$m_competitors->setRowTitle("competitors");
		$typeMarket->add($m_competitors);
		
		$m_technologyChanges = new textarea("m_technologyChanges");
		$m_technologyChanges->setTable("gis");
		$m_technologyChanges->setVisible(true);
		$m_technologyChanges->setLargeTextarea(true);
		$m_technologyChanges->setDataType("text");
		$m_technologyChanges->setRequired(false);
		$m_technologyChanges->setRowTitle("technologyChanges");
		$typeMarket->add($m_technologyChanges);
		
		$m_legislation = new textarea("m_legislation");
		$m_legislation->setLargeTextarea(true);
		$m_legislation->setTable("gis");
		$m_legislation->setVisible(true);
		$m_legislation->setDataType("text");
		$m_legislation->setRequired(false);
		$m_legislation->setRowTitle("legislation");
		$typeMarket->add($m_legislation);
		
		$attachment = new attachment("attachment");
		//if(isset($savedFields["attachment"]))
			//$attachment->setValue($savedFields["attachment"]);
		$attachment->setTempFileLocation("/apps/gis/tmp");
		$attachment->setFinalFileLocation("/apps/gis/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setNextAction("gis");
		$attachment->setVisible(true);
		$attachment->setAnchorRef("attachment");
		$attachmentGroup->add($attachment);

		$comments = new textarea("comments");
		$comments->setVisible(true);
		$comments->setDataType("text");
		$comments->setRequired(false);
		$comments->setLabel("{TRANSLATE:submission_comments}");
		$comments->setRowTitle("comments_on_this_submission");
		$sendToUser->add($comments);
		
		
		
		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);
				

		$this->form->add($initiation);
		$this->form->add($typeCompetitor);
		$this->form->add($typeMarket);
		$this->form->add($attachmentGroup);
		$this->form->add($sendToUser);
	}
	
	public function getEmailNotification($template, $gisId, $sendTo, $sender, $profileName, $comments)
	{
		echo "Template: " . $template . "<br />GID Id: " . $gisId . "<br />Send To: " .	$sendTo . "<br />Sender: " . $sender . "<br />Profile Name: "  . $profileName . "<br />Comments: ". $comments;
		
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<newProfile><gisId>" . $gisId . "</gisId></newProfile>");
			
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/gis/xsl/email.xsl");
		
		// transform xml using xsl
		
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
		
		$email = $proc->transformToXML($dom);
		
		$subject = translate::getInstance()->translate("gis_action") . " - ID: " . $id;
		
		email::send($sendTo, $sender, $subject, "$email", "");
		
		return true;
	}
	
}

?>