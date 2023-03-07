<?php

//require 'ijf.php';

/**
 * This is the IJF Application.
 *
 * This is the finance class.  This class is used to conduct the dataAdministration inspection part of the IJF process.
 *
 * @package apps
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class dataAdministration extends ijfProcess
{
	/**
	 * The constructor, which the IJF is passed to.
	 *
	 * @param IJF $ijg
	 */
	function __construct($ijf)
	{
		parent::__construct($ijf);

		$this->defineForm();

		$this->form->get('ijfId')->setValue($this->ijf->getId());

		$this->form->setStoreInSession(true);

		$this->form->loadSessionData();

		if (isset($_SESSION['apps'][$GLOBALS['app']]['dataAdministration']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database

		}

		if ($this->ijf->form->get('barManView')->getValue() == 'yes')
		{
			$this->form->get('barMan')->setValue('This IJF requires a Bar/Man View Request');
		}
		else
		{
			$this->form->get('barMan')->setValue('N/A');
		}





		$this->form->processDependencies();

		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = "  . $this->ijf->getId());
		$fields = mysql_fetch_array($dataset);

		$this->form->get('commodityCode')->setValue($fields['commodityCode']);

		//$this->form->get('wipPartNumbers')->setValue($fieldsDA['wipPartNumbers']);


		//$this->form->get('wipPartNumbers')->setValue($this->ijf->form->get("wipPartNumbers")->getValue());

		//	$this->form->get("moq")->setValue($fields['moq']);
		//	$this->form->get("commodityCode")->setValue($fields['commodityCode']);


	}

	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;
		$this->form->setStoreInSession(true);


		//$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM dataAdministration LEFT JOIN purchasing ON purchasing.ijfId = dataAdministration.ijfId INNER JOIN ijf ON dataAdministration.ijfId=ijf.id WHERE ijf.id = "  . $id);
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM dataAdministration LEFT JOIN ijf ON dataAdministration.ijfId=ijf.id LEFT JOIN purchasing ON purchasing.ijfId=ijf.id WHERE ijf.id = "  . $id);




		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['dataAdministration']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}

			$datasetDA = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM dataAdministration WHERE ijfId = "  . $id);
			$fieldsDA = mysql_fetch_array($datasetDA);

			$this->form->get('daSapPartNumber')->setValue($fieldsDA['daSapPartNumber']);

			$this->form->get('moq')->setValue($this->ijf->form->get('moq')->getValue());


			$this->form->get('updatedDate')->setValue(page::transformDateForPHP($this->form->get('updatedDate')->getValue()));

			$this->form->putValuesInSession();

			$this->form->processDependencies();




			return true;
		}
		else
		{
			unset($_SESSION['apps'][$GLOBALS['app']]['dataAdministration']);
			return false;
		}
	}

	public function save()
	{
		$this->determineStatus();

		if ($this->loadedFromDatabase)
		{

			$this->form->get('ijfId')->setValue($this->ijf->getID());
			
			$this->getIJF()->form->get("updatedDate")->setValue(common::nowDateForMysql());

			$this->getIJF()->form->get("initialSubmissionDate")->setIgnore(true);

			$this->form->get("owner")->setValue($this->form->get("dataAdministration_owner")->getValue());

			// update - This is where the database is updated. the gUQ takes the fields, and updates the details in the db.
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE dataAdministration " . $this->form->generateUpdateQuery("dataAdministration") . " WHERE ijfId='" . $this->getIjfId() . "'");
			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->getIjfId() . "'");
			//mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE purchasing " . $this->form->generateUpdateQuery("purchasing") . " WHERE ijfId='" . $this->getIjfId() . "'");

			// save new data

			$this->addLog("Data Administration Report Updated");

			// Send Email
			$this->getEmailNotification("dataAdmin", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("dataAdmin_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));

		}
		else
		{
			$this->form->get("owner")->setValue($this->form->get("dataAdministration_owner")->getValue());

			// set report date
			$this->form->get("updatedDate")->setValue(common::nowDateForMysql());

			// begin transaction
			mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");

			// insert

			mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . "WHERE id= '" . $this->getIjfId() . "'");

			mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");

			mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO dataAdministration " . $this->form->generateInsertQuery("dataAdministration"));

			$this->addLog(translate::getInstance()->translate("dataAdministration_completed"));

			// Send Email
			$datasetEmail = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($datasetEmail);
			$this->getEmailNotification("dataAdmin", $this->getIjfId(), $this->form->get("status")->getValue(),usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(),usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));
			//$this->getEmailNotification("dataAdmin_cc", $this->getIjfId(), $this->form->get("status")->getValue(),$this->form->get("delegate_owner")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), utf8_encode($this->form->get("email_text")->getValue()));

			if ($this->status == 'complete')
			{
				$this->addLog(translate::getInstance()->translate("dataAdministration_completed_final"));
			}
			else
			{
				$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");
			}
		}
		page::redirect("/apps/ijf/");
	}



	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate, comment ) VALUES (%u, '%s', '%s', '%s', '%s')",
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

	public function getMoq()
	{
		return $this->form->get("moq")->getValue();
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
		if ($_REQUEST['location_ownerDA'])
		{
			$location = $_REQUEST['location_ownerDA'];
				$this->status = $location;
				$this->form->get('status')->setValue($location);
		}

		//if ($this->getIJF()->form->get("material_type")->getValue() == 'raw_material')
		//{
		//	$this->status = 'purchasing';
		//	$this->form->get('status')->setValue("purchasing");
		//}
		//elseif ($this->getIJF()->form->get("material_type")->getValue() == 'semi_finished')
		//{
		//	$this->status = 'production';
		//	$this->form->get('status')->setValue("production");
		//}
		//elseif ($this->getiIJF()->form->get("material_type")->getValue() == 'finished_traded_goods')
		//{
		//	$this->status = 'commercialPlanning';
		//	$this->form->get('status')->setValue("commercialPlanning");
		//}
		//elseif ($this->getIJF()->form->get("material_type")->getValue() == 'finished_traded')
		//{
		//	$this->status = 'commercialPlanning';
		//	$this->form->get('status')->setValue("commercialPlanning");
		//}

	}

	public function defineForm()
	{
		$today = date("d/m/Y",time());

		// define the actual form
		$this->form = new form("dataAdministration");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$ijf = new group("ijf");
		$sendToUser = new group("sendToUser");
		$commentsGroup = new group("commentsGroup");
		$purchasing = new group("purchasing");
		$barManGroup = new group("barManGroup");
		$sapPartNumberGroup = new group("sapPartNumberGroup");
		$barManViewShow = new group("barManViewShow");

		$updatedDate = new textbox("updatedDate");
		$updatedDate->setValue($today);
		$updatedDate->setTable("ijf");
		$updatedDate->setVisible(false);
		$ijf->add($updatedDate);

		$ijfId = new invisibletext("ijfId");
		$ijfId->setLength(10);
		$ijfId->setTable("dataAdministration");
		$ijfId->setRowTitle("ijfId");
		$ijfId->setRequired(false);
		$ijfId->setVisible(false);
		$ijfId->setValue(0);
		$sapPartNumberGroup->add($ijfId);

		$daSapPartNumber = new textbox("daSapPartNumber");
		$daSapPartNumber->setTable("dataAdministration");
		$daSapPartNumber->setVisible(true);
		$daSapPartNumber->setDataType("string");
		$daSapPartNumber->setRequired(false);
		$daSapPartNumber->setLabel("IJF Details");
		$daSapPartNumber->setHelpId(2066);
		$daSapPartNumber->setRowTitle("da_sap_part_number");
		$sapPartNumberGroup->add($daSapPartNumber);

		$costedLotSize = new textbox("costedLotSize");
		$costedLotSize->setTable("ijf");
		$costedLotSize->setVisible(true);
		$costedLotSize->setDataType("string");
		$costedLotSize->setRequired(false);
		$costedLotSize->setRowTitle("costed_lot_size");
		$costedLotSize->setHelpId(2067);;
		$sapPartNumberGroup->add($costedLotSize);

		$costedLotSizeMeasurement = new textbox("costedLotSizeMeasurement");
		$costedLotSizeMeasurement->setTable("ijf");
		$costedLotSizeMeasurement->setVisible(true);
		$costedLotSizeMeasurement->setDataType("string");
		$costedLotSizeMeasurement->setRequired(false);
		$costedLotSizeMeasurement->setRowTitle("costed_lot_size_measurement");
		$costedLotSizeMeasurement->setHelpId(2067);;
		$sapPartNumberGroup->add($costedLotSizeMeasurement);

		$wipPartNumbers = new textarea("wipPartNumbers");
		$wipPartNumbers->setLength(240);
		$wipPartNumbers->setTable("ijf");
		$wipPartNumbers->setRowTitle("wip_part_numbers");
		$wipPartNumbers->setRequired(false);
		$wipPartNumbers->setVisible(true);
		$wipPartNumbers->setDataType("text");
		$wipPartNumbers->setHelpId(2068);
		$sapPartNumberGroup->add($wipPartNumbers);

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
		$commodityCode->setGroup("purchasing");
		$commodityCode->setDataType("string");
		$commodityCode->setRequired(false);
		$commodityCode->setHelpId(2110);
		$commodityCode->setRowTitle("commodity_code");
		$purchasing->add($commodityCode);


		$dataAdminComments = new textarea("dataAdminComments");
		$dataAdminComments->setLength(240);
		$dataAdminComments->setTable("dataAdministration");
		$dataAdminComments->setRowTitle("comments");
		$dataAdminComments->setDataType("text");
		$dataAdminComments->setLabel("Comments");
		$dataAdminComments->setRequired(false);
		$dataAdminComments->setVisible(true);
		$dataAdminComments->setHelpId(2069);
		$commentsGroup->add($dataAdminComments);

		$barMan = new readonly("barMan");
		$barMan->setLength(240);
		$barMan->setTable("ijf");
		$barMan->setRowTitle("bar_man");
		$barMan->setLabel("Bar/Man View");
		$barMan->setRequired(false);
		$barManViewShow->add($barMan);

		$barManViewComplete = new radio("barManViewComplete");
		$barManViewComplete->setGroup("barManViewShow");
		$barManViewComplete->setDataType("string");
		$barManViewComplete->setLength(50);
		$barManViewComplete->setRowTitle("bar_man_selector");
		$barManViewComplete->setRequired(false);
		$barManViewComplete->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$barManViewComplete->setTable("ijf");
		$barManViewComplete->setValue('no');
		$barManViewComplete->setHelpId(2019);
		$barManViewShow->add($barManViewComplete);

		$location_ownerDA = new dropdown("location_ownerDA");
		$location_ownerDA->setGroup("sendToUser");
		$location_ownerDA->setLength(250);
		$location_ownerDA->setTable("dataAdministration");
		$location_ownerDA->setRowTitle("send_ijf_to_location");
		$location_ownerDA->setLabel("User Delivery Options");
		$location_ownerDA->setRequired(true);
		$location_ownerDA->setHelpId(2070);
		$location_ownerDA->setXMLSource("./apps/ijf/xml/departments.xml");
		$sendToUser->add($location_ownerDA);

		$dataAdministration_owner = new dropdown("dataAdministration_owner");
		$dataAdministration_owner->setGroup("sendToUser");
		$dataAdministration_owner->setLength(250);
		$dataAdministration_owner->setTable("dataAdministration");
		$dataAdministration_owner->setRowTitle("send_ijf_to");
		$dataAdministration_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$dataAdministration_owner->setRequired(true);
		$dataAdministration_owner->setVisible(true);
		$dataAdministration_owner->setHelpId(2009);
		$sendToUser->add($dataAdministration_owner);


		$owner = new dropdown("owner");
		$owner->setGroup("sendToUser");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->setRowTitle("send_ijf_to");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf%' ORDER BY employee.firstName, employee.lastName");
		$owner->setRequired(false);
		$owner->setVisible(false);
		$owner->setHelpId(2009);
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
		$delegate_owner->setTable("dataAdministration");
		$delegate_owner->setDataType("text");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2010);
		$sendToUser->add($delegate_owner);

		$email_text = new textarea("email_text");
		$email_text->setGroup("sendToUser");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setHelpId(2071);
		$email_text->setTable("ijf");
		$sendToUser->add($email_text);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);


		$this->form->add($ijf);
		$this->form->add($sapPartNumberGroup);
		$this->form->add($purchasing);
		$this->form->add($commentsGroup);
		$this->form->add($barManGroup);
		$this->form->add($barManViewShow);

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

		//if($action == "dataAdmin_cc") $subjectText = "CC - " . $subjectText;

		email::send($owner, /*"intranet@scapa.com"*/ $sender, $subjectText, "$email", "$cc");

		return true;
	}

}

?>