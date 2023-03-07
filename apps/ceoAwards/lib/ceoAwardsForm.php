<?php
/**
 * @package apps
 * @subpackage customerComplaints
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 26/11/2010
 */

// PEAR mail classes
require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');

class ceoAwardsForm
{
	public $form;

	/******IMPORTANT******/
	public $server = "live";	// Determines if server is dev or live
	/*********************/

	function __construct($loadFromSession = false, $readOnly = false)
	{
		$this->loadFromSession = $loadFromSession;

		$this->defineForm();
		$this->load();
	}


	private function load()
	{
		$this->form->get("name")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName());

		if (!$this->loadFromSession)
		{
			$this->loadInitialValues();
		}
		else
		{
			if (!isset($_POST["innovation"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['ceoAwards' . currentuser::getInstance()->getNTLogon()]['awards']['innovation']);
			}

			if (!isset($_POST["continuousImprovement"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['ceoAwards' . currentuser::getInstance()->getNTLogon()]['awards']['continuousImprovement']);
			}

			if (!isset($_POST["serviceExcellence"]))
			{
				unset($_SESSION['apps'][$GLOBALS['app']]['ceoAwards' . currentuser::getInstance()->getNTLogon()]['awards']['serviceExcellence']);
			}

			$this->form->loadSessionData();
			$this->form->processPost();
			$this->submit();
		}

//		$this->form->putValuesInSession();
		$this->form->processDependencies(true);
	}


	/**
	 * Pre-populate user details
	 */
	private function loadInitialValues()
	{
		$this->form->get("jobTitle")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRole());
		$this->form->get("regionId")->setValue($this->getSelectionIDFromOption(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getRegion()));
		$this->form->get("siteId")->setValue($this->getSelectionIDFromOption(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite()));
	}


	private function getSelectionIDFromOption($option)
	{
		$selectSQL = "SELECT id
			FROM selectionOptions
			WHERE selectionOption = '" . $option . "'";

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($selectSQL);

		if ($fields = mysql_fetch_array($dataset))
		{
			return $fields['id'];
		}

		return 0;
	}


	private function getSelectionOptionFromID($id)
	{
		$selectSQL = "SELECT selectionOption
			FROM selectionOptions
			WHERE id = " . $id;

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($selectSQL);

		if ($fields = mysql_fetch_array($dataset))
		{
			return $fields['selectionOption'];
		}

		return "";
	}


	public function showForm()
	{
		return $this->form->showForm();
	}


	private function save()
	{
		if ($this->userNotSubmitted())
		{
			$sql = "INSERT INTO ceoAwards " . $this->form->generateInsertQuery("ceoAwards");
			mysql::getInstance()->selectDatabase("intranet")->Execute($sql);

			$selectSQL = "SELECT id
				FROM ceoAwards
				ORDER BY id DESC LIMIT 0,1";

			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($selectSQL);

			$fields = mysql_fetch_assoc($dataset);

			$id = $fields['id'];

			mysql::getInstance()->selectDatabase("intranet")->Execute("
					UPDATE ceoAwards
					SET dateSubmitted = '" . date("Y-m-d G:i:s") . "',
					NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'
					WHERE id = " .  $id);

			if ($this->form->get("innovation")->getValue() == 'on')
			{
				mysql::getInstance()->selectDatabase("intranet")->Execute("
					UPDATE ceoAwards
					SET innovation = 1
					WHERE id = " .  $id);
			}

			if ($this->form->get("continuousImprovement")->getValue() == 'on')
			{
				mysql::getInstance()->selectDatabase("intranet")->Execute("
					UPDATE ceoAwards
					SET continuousImprovement = 1
					WHERE id = " .  $id);
			}

			if ($this->form->get("serviceExcellence")->getValue() == 'on')
			{
				mysql::getInstance()->selectDatabase("intranet")->Execute("
					UPDATE ceoAwards
					SET serviceExcellence = 1
					WHERE id = " .  $id);
			}

			unset($_SESSION['apps'][$GLOBALS['app']]['ceoAwards_' . currentuser::getInstance()->getNTLogon()]);

			$this->getEmailNotification();

			page::redirect("http://scapanet/home/");
		}
		else
		{
			page::redirect("http://scapanet/apps/ceoAwards/");
		}
	}

	//Saves the form to database
	public function submit()
	{
		if ($this->validate())
		{
			$this->save();
		}
	}

	//Process form submissions
	public function processPost()
	{
		$this->form->processPost();
	}

	//Validates forms
	public function validate()
	{
		$this->form->validate();

		if ($this->form->isValid())
		{
			return true;
		}

		return false;
	}


	public function show()
	{
		$output = "<ceoAwardsForm>";

			$output .= $this->form->output();

		$output .= "</ceoAwardsForm>";

		return $output;
	}


	/**
	 * Define the actual form
	 */
	public function defineForm()
	{
		$this->form = new form("ceoAwards_" . currentuser::getInstance()->getNTLogon());
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$userDetails = new group("userDetails");
		$userDetails->setBorder(false);

		$awards = new group("awards");
		$awards->setBorder(false);

		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);

		$name = new readonly("name");
		$name->setGroup("userDetails");
		$name->setRowTitle("Name");
		$name->setRequired(false);
		$name->setIgnore(false);
		$userDetails->add($name);

		$jobTitle = new textbox("jobTitle");
		$jobTitle->setGroup("userDetails");
		$jobTitle->setDataType("string");
		$jobTitle->setLength(128);
		$jobTitle->setErrorMessage("Please enter your job title.");
		$jobTitle->setRowTitle("Job Title");
		$jobTitle->setRequired(true);
		$jobTitle->setTable("ceoAwards");
		$userDetails->add($jobTitle);

		$region = new dropdown("regionId");
		$region->setGroup("userDetails");
		$region->setDataType("string");
		$region->setErrorMessage("dropdown_error");
		$region->setLength(50);
		$region->setRowTitle("Region");
		$region->setRequired(true);
		$region->setSQLSource("intranet",
			"SELECT selectionOption AS name, id AS value
			FROM intranet.selectionOptions
			WHERE typeId = 1
			ORDER BY selectionOption ASC");
		$region->setTable("ceoAwards");
		$userDetails->add($region);

		$site = new dropdown("siteId");
		$site->setGroup("userDetails");
		$site->setDataType("string");
		$site->setErrorMessage("dropdown_error");
		$site->setLength(50);
		$site->setRowTitle("Site");
		$site->setRequired(true);
		$site->setSQLSource("intranet",
			"SELECT selectionOption AS name, id AS value
			FROM intranet.selectionOptions
			WHERE typeId = 2
			ORDER BY selectionOption ASC");
		$site->setTable("ceoAwards");
		$userDetails->add($site);

		$innovation = new checkbox("innovation");
		$innovation->setGroup("awards");
		$innovation->setRowTitle("Innovation");
		$innovation->setVisible(true);
		$innovation->setLabel("Award Categories (please select at least one)");
		$awards->add($innovation);

		$continuousImprovement = new checkbox("continuousImprovement");
		$continuousImprovement->setGroup("awards");
		$continuousImprovement->setRowTitle("Continuous Improvement");
		$continuousImprovement->setVisible(true);
		$awards->add($continuousImprovement);

		$serviceExcellence = new checkbox("serviceExcellence");
		$serviceExcellence->setGroup("awards");
		$serviceExcellence->setRowTitle("Service Excellence");
		$serviceExcellence->setVisible(true);
		$awards->add($serviceExcellence);

		$submit = new submit("submit");
		$submit->setValue('Submit');
		$submit->setAction('submit');
		$submitGroup->add($submit);

		$this->form->add($userDetails);
		$this->form->add($awards);
		$this->form->add($submitGroup);
	}


	public function getEmailNotification()
	{
		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
		{
			$host = common::getUSAMailHost();
			$username = common::getUSAMailUsername();
			$password = common::getUSAMailPassword();
		}
		else
		{
			$host = common::getMailHost();
			$username = common::getMailUsername();
			$password = common::getMailPassword();
		}

		$mailbox = ($this->server == "dev") ? "intranet@scapa.com" : "ceoawards.2011@scapa.com";

		$headers = array (
			'From' => $mailbox,
			'To' => usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(),
			'Cc' => "",
			'Subject' => "CEO Awards Application Form Requested",
			'X-Priority' => 3);

		$dom = new DomDocument;

		$continuousImprovement = ($this->form->get("continuousImprovement")->getValue() == "on") ? "Yes" : "No";
		$innovation = ($this->form->get("innovation")->getValue() == "on") ? "Yes" : "No";
		$serviceExcellence = ($this->form->get("serviceExcellence")->getValue() == "on") ? "Yes" : "No";

		$dom->loadXML("<submitted>
				<name>" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() . "</name>
				<jobTitle>" . $this->form->get("jobTitle")->getValue() . "</jobTitle>
				<region>" . $this->getSelectionOptionFromID($this->form->get("regionId")->getValue()) . "</region>
				<site>" . $this->getSelectionOptionFromID($this->form->get("siteId")->getDisplayValue()) . "</site>
				<continuousImprovement>" . $continuousImprovement . "</continuousImprovement>
				<innovation>" . $innovation . "</innovation>
				<serviceExcellence>" . $serviceExcellence . "</serviceExcellence>
			</submitted>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ceoAwards/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		email::send($mailbox, usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), "CEO Awards Application Form Requested", $email);

		$mime = new Mail_Mime();

		$mime->setTXTBody($email);

		$mime->addAttachment("/home/" . $this->server . "/apps/ceoAwards/files/CEO_Awards_2011_Application_Form.doc");

		$body = $mime->get();

		$hdrs = $mime->headers($headers);

		$params = array(
			'host' => $host,
		    'auth' => true,
		    'username' => $username,
		    'password' => $password
		);

		$mail =& Mail::factory('smtp', $params);
		$mail->send(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), $hdrs, $body);
	}

	private function userNotSubmitted()
	{
		$selectSQL = "SELECT id
			FROM ceoAwards
			WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'";

		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute($selectSQL);

		if (mysql_num_rows($dataset) > 0)
		{
			return false;
		}

		return true;
	}
}

?>