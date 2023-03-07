<?php

/**
 * This is the appraisal Application.
 *
 * This is the review class.  This class is used to conduct the review part of the appraisal process.
 * 
 * @package apps	
 * @subpackage appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class review extends appraisalProcess
{
	/**
	 * The constructor, which the appraisal is passed to.
	 *
	 * @param appraisal $appraisal
	 */

	public $attachments;

	function __construct($appraisal)
	{
		parent::__construct($appraisal);

		$this->defineForm();
		
		$this->form->get('appraisalId')->setValue($this->appraisal->getId());

		$this->form->setStoreInSession(true);
		
		$this->form->loadSessionData();

		if (isset($_SESSION['apps'][$GLOBALS['app']]['review']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the appraisal is loaded from the database
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

		if(!isset($_REQUEST["sfID"]))
		{//fudge to get round the loading of the form vars
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM review LEFT JOIN appraisal ON review.appraisalId=appraisal.id WHERE appraisalId = "  . $id);
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM review LEFT JOIN appraisal ON review.appraisalId=appraisal.id WHERE appraisalId = 'UNIX_TIMESTAMP(NOW())'");
		}

		if (mysql_num_rows($dataset) == 1)
		{

			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['review']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			if(isset($_REQUEST["sfID"])){

				$this->sfID = $_REQUEST["sfID"];
				$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$_REQUEST["sfID"]."' LIMIT 1");

				while ($fields2 = mysql_fetch_array($dataset))
				{
					$savedFields = unserialize($fields2["sfValue"]);
				}

				if($savedFields)
				{
					foreach ($savedFields as $key => $value)
					{
						if($value)$fields[$key] = $value;
					}
				}
			}

			$this->form->populate($fields);
		
			$this->form->putValuesInSession();

			$this->form->processDependencies();

			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['review']);
			return false;
		}

	}

	public function save()
	{
	
		page::addDebug("Saving review process: ".$process,__FILE__,__LINE__);
		
		$this->determineStatus();

		$originalOwner = $this->form->get("owner")->getValue();
		
		if ($this->loadedFromDatabase)
		{
			$this->form->get("appraisalId")->setIgnore(true);

			// update
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE review " . $this->form->generateUpdateQuery("review") . " WHERE appraisalId= " . $this->getappraisalId() . "");
			
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal " . $this->form->generateUpdateQuery("appraisal") . " WHERE id='" . $this->getappraisalId() . "'");
			
			$this->addLog(translate::getInstance()->translate("review_updated_send_to") . " - " . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName(), $this->form->get("emailText")->getValue());
		}
		else
		{

			/* WC EDIT */
			$doUpdate = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT count(appraisalId) as doUpdate FROM review WHERE appraisalId = '" . $this->getappraisalId() . "'");
			$doUpdateFields = mysql_fetch_array($doUpdate);
			if($doUpdateFields["doUpdate"]>0)
			{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE review " . $this->form->generateUpdateQuery("review")." WHERE appraisalId = '".$this->getappraisalId()."'");
			}
			else
			{
				mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO review " . $this->form->generateInsertQuery("review"));
			}
			/* WC END */
		}
		
		if($this->form->generateUpdateQuery("appraisal"))
		{
			mysql::getInstance()->selectDatabase("appraisals")->Execute("UPDATE appraisal " . $this->form->generateUpdateQuery("appraisal") ." WHERE id = '". $this->getappraisalId() ."'");	
		}
		
		page::redirect("/apps/appraisal/");
	}
	
	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("appraisals")->Execute(sprintf("INSERT INTO actionLog (appraisalId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$this->getappraisal()->form->get("id")->getValue(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		utf8_encode(addslashes($action)),
		common::nowDateTimeForMysql(),
		$comment
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

	public function showCompletionBits($outputType)
	{
		if ($outputType == "readOnly")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() || $this->isComplete())
			{
				//$this->form->get('finalComments')->setVisible(true);
			}
		}

		if ($outputType == "normal")
		{
			if (currentuser::getInstance()->getNTLogon() == $this->getOwner() && !$this->isComplete())
			{
				//$this->form->get('finalComments')->setVisible(true);
			}
		}
	}

	public function determineStatus()
	{
		
	}

	public function defineForm()
	{	
		/* WC AE - 28/01/08 */
		$savedFields = array();
		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset))
			{
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		else
		{
			if(isset($_GET["print"]) && !isset($_REQUEST["printAll"]))
			{//this means we are coming from the print function defined on homepage
				$retArray = array();
	
				$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM review LEFT JOIN appraisal ON review.appraisalId=appraisal.id WHERE appraisalId = '"  . $_REQUEST['appraisal'] ."'");
	
				$fields2 = mysql_fetch_array($dataset);
				if($fields2){
					foreach ($fields2 as $key => $value)
					{
						if($value){
							if(!strtotime($value) && $value != "0000-00-00"){
								$savedFields[$key] = $value;
							}else if(strtotime($value) && $value != "0000-00-00"){//if it is a date field then chenge the layout
								$savedFields[$key] = page::transformDateForPHP($value);
							}
	
						}
					}
				}
			}
		}

		/* WC END*/
		$today = date("d/m/Y",time());

		// define the actual form
		$this->form = new form("review");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$submitGroup = new group("submitGroup");

		$appraisalId = new invisibletext("appraisalId");
		$appraisalId->setTable("review");
		$appraisalId->setVisible(false);
		$appraisalId->setGroup("initiation");
		$appraisalId->setDataType("number");
		$appraisalId->setValue(0);
		$initiation->add($appraisalId);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
		$status->setValue($savedFields["status"]);
		else $status->setValue("conclusion");
		$status->setTable("review");
		$status->setVisible(false);
		$initiation->add($status);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		$owner->setValue($savedFields["owner"]);
		$owner->setTable("appraisal");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		if(isset($_GET["print"]) && !isset($_REQUEST["printAll"]))
		{//this means we are coming from the print function defined on homepage
			
			$showID = new textbox("showID");
			$showID->setValue($this->appraisal->getId());
			$showID->setRowTitle("appraisal_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("review");
			$initiation->add($showID);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
			$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("review");
			$initiation->add($showOpenDate);
		}
		
		$reviewOfLastPeriodsPerformance = new textarea("reviewOfLastPeriodsPerformance");
		if(isset($savedFields["reviewOfLastPeriodsPerformance"]))
			$reviewOfLastPeriodsPerformance->setValue($savedFields["reviewOfLastPeriodsPerformance"]);
		$reviewOfLastPeriodsPerformance->setTable("review");
		$reviewOfLastPeriodsPerformance->setDataType("text");
		$reviewOfLastPeriodsPerformance->setRowTitle("consider_the_employees");
		$reviewOfLastPeriodsPerformance->setLabel("4 - Review of Last Period's Performance");
		$initiation->add($reviewOfLastPeriodsPerformance);
		
		$appraisalOfSkillsAndAptitudes = new readonly("appraisalOfSkillsAndAptitudes");
		if(isset($savedFields["appraisalOfSkillsAndAptitudes"]))
			$appraisalOfSkillsAndAptitudes->setValue($savedFields["appraisalOfSkillsAndAptitudes"]);
		$appraisalOfSkillsAndAptitudes->setTable("review");
		$appraisalOfSkillsAndAptitudes->setRowTitle("score_legend");
		$appraisalOfSkillsAndAptitudes->setValue("1 - Exceptional, 2 - Meets Requirements, 3 - Needs Improving, 4 - Does Not Meet Requirements, 5 - Not applicable or not observed int he current job role.");
		$appraisalOfSkillsAndAptitudes->setLabel("5 - Appraisal Of Skills and Aptitudes Used in Current Job Role");
		$initiation->add($appraisalOfSkillsAndAptitudes);
		
		$worksSafely = new dropdown("worksSafely");
		if(isset($savedFields["worksSafely"]))
			$worksSafely->setValue($savedFields["worksSafely"]);
		$worksSafely->setTable("review");
		$worksSafely->setDataType("string");
		$worksSafely->setXMLSource("./apps/appraisal/xml/score.xml");
		$worksSafely->setRowTitle("works_safely");
		$initiation->add($worksSafely);
		
		$qualityOfWork = new dropdown("qualityOfWork");
		if(isset($savedFields["qualityOfWork"]))
			$qualityOfWork->setValue($savedFields["qualityOfWork"]);
		$qualityOfWork->setTable("review");
		$qualityOfWork->setDataType("string");
		$qualityOfWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$qualityOfWork->setRowTitle("quality_of_work");
		$initiation->add($qualityOfWork);
		
		$quantityOfWork = new dropdown("quantityOfWork");
		if(isset($savedFields["quantityOfWork"]))
			$quantityOfWork->setValue($savedFields["quantityOfWork"]);
		$quantityOfWork->setTable("review");
		$quantityOfWork->setDataType("string");
		$quantityOfWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$quantityOfWork->setRowTitle("quantity_of_work");
		$initiation->add($quantityOfWork);
		
		$abilityToWorkInATeam = new dropdown("abilityToWorkInATeam");
		if(isset($savedFields["abilityToWorkInATeam"]))
			$abilityToWorkInATeam->setValue($savedFields["abilityToWorkInATeam"]);
		$abilityToWorkInATeam->setTable("review");
		$abilityToWorkInATeam->setDataType("string");
		$abilityToWorkInATeam->setXMLSource("./apps/appraisal/xml/score.xml");
		$abilityToWorkInATeam->setRowTitle("ability_to_work_in_a_team");
		$initiation->add($abilityToWorkInATeam);
		
		$qualityOfService = new dropdown("qualityOfService");
		if(isset($savedFields["qualityOfService"]))
			$qualityOfService->setValue($savedFields["qualityOfService"]);
		$qualityOfService->setTable("review");
		$qualityOfService->setDataType("string");
		$qualityOfService->setXMLSource("./apps/appraisal/xml/score.xml");
		$qualityOfService->setRowTitle("quality_of_service");
		$initiation->add($qualityOfService);
		
		$technicalKnowledge = new dropdown("technicalKnowledge");
		if(isset($savedFields["technicalKnowledge"]))
			$technicalKnowledge->setValue($savedFields["technicalKnowledge"]);
		$technicalKnowledge->setTable("review");
		$technicalKnowledge->setDataType("string");
		$technicalKnowledge->setXMLSource("./apps/appraisal/xml/score.xml");
		$technicalKnowledge->setRowTitle("technical_knowledge");
		$initiation->add($technicalKnowledge);
		
		$learningAbility = new dropdown("learningAbility");
		if(isset($savedFields["learningAbility"]))
			$learningAbility->setValue($savedFields["learningAbility"]);
		$learningAbility->setTable("review");
		$learningAbility->setDataType("string");
		$learningAbility->setXMLSource("./apps/appraisal/xml/score.xml");
		$learningAbility->setRowTitle("learning_ability");
		$initiation->add($learningAbility);
		
		$useOfEquipment = new dropdown("useOfEquipment");
		if(isset($savedFields["useOfEquipment"]))
			$useOfEquipment->setValue($savedFields["useOfEquipment"]);
		$useOfEquipment->setTable("review");
		$useOfEquipment->setDataType("string");
		$useOfEquipment->setXMLSource("./apps/appraisal/xml/score.xml");
		$useOfEquipment->setRowTitle("use_of_equipment");
		$initiation->add($useOfEquipment);
		
		$housekeeping = new dropdown("housekeeping");
		if(isset($savedFields["housekeeping"]))
			$housekeeping->setValue($savedFields["housekeeping"]);
		$housekeeping->setTable("review");
		$housekeeping->setDataType("string");
		$housekeeping->setXMLSource("./apps/appraisal/xml/score.xml");
		$housekeeping->setRowTitle("housekeeping");
		$initiation->add($housekeeping);
		
		$organisationOfWork = new dropdown("organisationOfWork");
		if(isset($savedFields["organisationOfWork"]))
			$organisationOfWork->setValue($savedFields["organisationOfWork"]);
		$organisationOfWork->setTable("review");
		$organisationOfWork->setDataType("string");
		$organisationOfWork->setXMLSource("./apps/appraisal/xml/score.xml");
		$organisationOfWork->setRowTitle("organisation_of_work");
		$initiation->add($organisationOfWork);
		
		$oralCommunication = new dropdown("oralCommunication");
		if(isset($savedFields["oralCommunication"]))
			$oralCommunication->setValue($savedFields["oralCommunication"]);
		$oralCommunication->setTable("review");
		$oralCommunication->setDataType("string");
		$oralCommunication->setXMLSource("./apps/appraisal/xml/score.xml");
		$oralCommunication->setRowTitle("oral_communication");
		$initiation->add($oralCommunication);
		
		$autonomous = new dropdown("autonomous");
		if(isset($savedFields["autonomous"]))
			$autonomous->setValue($savedFields["autonomous"]);
		$autonomous->setTable("review");
		$autonomous->setDataType("string");
		$autonomous->setXMLSource("./apps/appraisal/xml/score.xml");
		$autonomous->setRowTitle("autonomous");
		$initiation->add($autonomous);
		
		$initiative = new dropdown("initiative");
		if(isset($savedFields["initiative"]))
			$initiative->setValue($savedFields["initiative"]);
		$initiative->setTable("review");
		$initiative->setDataType("string");
		$initiative->setXMLSource("./apps/appraisal/xml/score.xml");
		$initiative->setRowTitle("initiative");
		$initiation->add($initiative);
		
		$senseOfResponsabilities = new dropdown("senseOfResponsabilities");
		if(isset($savedFields["senseOfResponsabilities"]))
			$senseOfResponsabilities->setValue($savedFields["senseOfResponsabilities"]);
		$senseOfResponsabilities->setTable("review");
		$senseOfResponsabilities->setDataType("string");
		$senseOfResponsabilities->setXMLSource("./apps/appraisal/xml/score.xml");
		$senseOfResponsabilities->setRowTitle("senseOfResponsabilities");
		$initiation->add($senseOfResponsabilities);
		
		$excellenceAndPerseverance = new dropdown("excellenceAndPerseverance");
		if(isset($savedFields["excellenceAndPerseverance"]))
			$excellenceAndPerseverance->setValue($savedFields["excellenceAndPerseverance"]);
		$excellenceAndPerseverance->setTable("review");
		$excellenceAndPerseverance->setDataType("string");
		$excellenceAndPerseverance->setXMLSource("./apps/appraisal/xml/score.xml");
		$excellenceAndPerseverance->setRowTitle("excellence_and_perseverance");
		$initiation->add($excellenceAndPerseverance);
		
		$flexbibilityAndAdaptation = new dropdown("flexbibilityAndAdaptation");
		if(isset($savedFields["flexbibilityAndAdaptation"]))
			$flexbibilityAndAdaptation->setValue($savedFields["flexbibilityAndAdaptation"]);
		$flexbibilityAndAdaptation->setTable("review");
		$flexbibilityAndAdaptation->setDataType("string");
		$flexbibilityAndAdaptation->setXMLSource("./apps/appraisal/xml/score.xml");
		$flexbibilityAndAdaptation->setRowTitle("flexbibility_and_adaptation");
		$initiation->add($flexbibilityAndAdaptation);
		
		$interpersonnalRelations = new dropdown("interpersonnalRelations");
		if(isset($savedFields["interpersonnalRelations"]))
			$interpersonnalRelations->setValue($savedFields["interpersonnalRelations"]);
		$interpersonnalRelations->setTable("review");
		$interpersonnalRelations->setDataType("string");
		$interpersonnalRelations->setXMLSource("./apps/appraisal/xml/score.xml");
		$interpersonnalRelations->setRowTitle("interpersonnal_relations");
		$initiation->add($interpersonnalRelations);
		
		$toleranceToStress = new dropdown("toleranceToStress");
		if(isset($savedFields["toleranceToStress"]))
			$toleranceToStress->setValue($savedFields["toleranceToStress"]);
		$toleranceToStress->setTable("review");
		$toleranceToStress->setDataType("string");
		$toleranceToStress->setXMLSource("./apps/appraisal/xml/score.xml");
		$toleranceToStress->setRowTitle("tolerance_to_stress");
		$initiation->add($toleranceToStress);
		
		$punctualityAndRegularity = new dropdown("punctualityAndRegularity");
		if(isset($savedFields["punctualityAndRegularity"]))
			$punctualityAndRegularity->setValue($savedFields["punctualityAndRegularity"]);
		$punctualityAndRegularity->setTable("review");
		$punctualityAndRegularity->setDataType("string");
		$punctualityAndRegularity->setXMLSource("./apps/appraisal/xml/score.xml");
		$punctualityAndRegularity->setRowTitle("punctuality_and_regularity");
		$initiation->add($punctualityAndRegularity);
		
		$otherHelp = new dropdown("otherHelp");
		if(isset($savedFields["otherHelp"]))
			$otherHelp->setValue($savedFields["otherHelp"]);
		$otherHelp->setTable("review");
		$otherHelp->setDataType("string");
		$otherHelp->setXMLSource("./apps/appraisal/xml/score.xml");
		$otherHelp->setRowTitle("other");
		$initiation->add($otherHelp);
		

		//$processOwner = new dropdown("processOwner");
		$processOwner2 = new autocomplete("processOwner2");
		if(isset($savedFields["processOwner2"]))
		$processOwner2->setValue($savedFields["processOwner2"]);
		$processOwner2->setGroup("submitGroup");
		$processOwner2->setDataType("string");
		$processOwner2->setRowTitle("chosen_appraisal_owner");
		$processOwner2->setLabel("Process Information");
		$processOwner2->setRequired(false);
		//$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner2->setUrl("/apps/appraisal/ajax/appraisalOwner2?");
		$processOwner2->setTable("review");
		//$processOwner->clearData();
		$processOwner2->setHelpId(8145);
		$submitGroup->add($processOwner2);





		$emailText = new textarea("emailText");
		if(isset($savedFields["emailText"]))
		$emailText->setValue($savedFields["emailText"]);
		$emailText->setGroup("submitGroup");
		$emailText->setDataType("text");
		$emailText->setRowTitle("emailText");
		$emailText->setRequired(false);
		$emailText->setTable("review");
		$emailText->setHelpId(9078);
		$submitGroup->add($emailText);

		$submit = new submit("submit");
		$submit->setGroup("sentTo");
		$submit->setVisible(true);
		$submitGroup->add($submit);


		$this->form->add($initiation);
		$this->form->add($submitGroup);

	}

	public function getEmailNotification($owner, $sender, $id, $action, $emailText, $appraisalJustifiedStatus)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><emailText>" . utf8_decode($emailText) . "</emailText><appraisal_justified>" . $appraisalJustifiedStatus . "</appraisal_justified></$action>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/appraisal/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);

		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT copy_to FROM ccGroup WHERE `appraisalId` = '" . $id . "'");

		$cc = "";

		email::send($owner, /*"intranet@scapa.com"*/$sender, (translate::getInstance()->translate("new_appraisal_action") . " - ID: " . $id), "$email", "$cc");

		return true;
	}

}

?>