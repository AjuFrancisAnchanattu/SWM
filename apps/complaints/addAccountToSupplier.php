<?php

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 26/03/2009
 */
class addAccountToSupplier extends page
{
	private $form;
	private $comment;
	private $date;
	private $owner;
	private $externalPassword;
	
	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Complaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
			
		$this->add_output("<complaintsAddAccountToSupplier>");
		
		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
//		$snapins_left->register('apps/complaints', 'summaryComplaints', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'addComplaint', true, true);		//puts the Complaints add snapin in the page
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaints load snapin in the page
		//$snapins_left->register('apps/complaints', 'actionComplaints', true, true);		//puts the complaints actions snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the complaints report snapin in the page
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the complaints bookmarked snapin in the page
		$snapins_left->register('apps/complaints', 'refDocuments', true, true);		//puts the complaints ref docs snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->setStoreInSession(true);
		
		$this->form->processDependencies();
		
		if(isset($_GET['mode']) && $_GET['mode'] == "deleteuser")
		{
			mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("DELETE FROM users WHERE username LIKE '%" . $_GET['username'] . "%'");
			mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("DELETE FROM employee WHERE NTLogon LIKE '%" . $_GET['username'] . "%'");
			page::redirect('./');
		}
		
		if(isset($_GET['sapSupplierNumber']))
		{
			$_GET['sapSupplierNumber'] = str_replace("%20", "", $_GET['sapSupplierNumber']); // For NA Spaces in ID Names
			
			$this->add_output("<sapSupplierNumber>" . $_GET['sapSupplierNumber'] . "</sapSupplierNumber>");
			
			$datasetExt = mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("SELECT * FROM users INNER JOIN employee ON users.username = employee.NTLogon WHERE users.sapSupplierNumber LIKE '%" . $_GET['sapSupplierNumber'] . "%'");

			while($fieldsExt = mysql_fetch_array($datasetExt))
			{
				$this->add_output("<complaintsAddAccountToSupplierUsers>");
					$this->add_output("<userUsername>" . $fieldsExt['username'] . "</userUsername>");
					$this->add_output("<userFirstName>" . $fieldsExt['firstName'] . "</userFirstName>");
					$this->add_output("<userLastName>" . $fieldsExt['lastName'] . "</userLastName>");
					$this->add_output("<userEmailAddress>" . $fieldsExt['emailAddress'] . "</userEmailAddress>");
					$this->add_output("<userDefaultLanguage>" . $fieldsExt['language'] . "</userDefaultLanguage>");
				$this->add_output("</complaintsAddAccountToSupplierUsers>");
			}
		}
		
		if(isset($_GET['complaintId']))
		{
			$this->add_output("<complaintId>" . $_GET['complaintId'] . "</complaintId>");
		}		
		
		// process request
		if(isset($_POST["action"]) && $_POST["action"] == "submit")
		{
			// get anything posted by the form
			$this->form->processPost();
			
			if(!common::isValidEmailAddress($this->form->get("username")->getValue()))
			{
				$this->add_output("<error />");
				$this->form->get("username")->setValid(false);
			}
			else 
			{
				if ($this->form->validate())
				{
					// Check if user already exists first ...
					$datasetExtCheck = mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("SELECT username FROM users WHERE username = '" . $this->form->get("username")->getValue() . "'");
					
					if(mysql_num_rows($datasetExtCheck) != 1)
					{
						// Add User to EXT User Table
						mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute(sprintf("INSERT INTO users (username, password, sapSupplierNumber, emailAddress) VALUES ('%s', '%s', '%s', '%s')",
							$this->form->get("username")->getValue(),
							$this->externalPassword = rand("100000", "999999"),
							$_GET['sapSupplierNumber'],
							$this->form->get("username")->getValue()
						));
						
						// Add User to EXT Employee Table
						mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute(sprintf("INSERT INTO employee (NTLogon, firstName, lastName, email, enabled, language) VALUES ('%s', '%s', '%s', '%s', %u, '%s')",
							$this->form->get("username")->getValue(),
							$this->form->get("firstName")->getValue(),
							$this->form->get("lastName")->getValue(),
							$this->form->get("username")->getValue(),
							1,
							$this->form->get("supplierDefaultLanguage")->getValue()
						));
					
						$this->addLog(translate::getInstance()->translate("user_account_added_to_supplier") . " " . $this->form->get("username")->getValue());
						$this->sendSupplierManual($this->form->get("username")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail());
						
						
						page::redirect('./?emailSent=true');
					}
					else 
					{
						$this->addLog(translate::getInstance()->translate("user_account_already_exists"));
						page::redirect('./?emailSent=false');
					}
				}	
			}		
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
						
			$this->form->processPost();
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</complaintsAddAccountToSupplier>");
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
		
	private function defineForm()
	{	
		$this->form = new form("AddAccountToSupplier");
		
		$AddAccountToSupplier = new group("AddAccountToSupplier");
		
		$id = new invisibletext("id");
		$id->setDataType("string");
		$id->setLength(50);
		$id->setVisible(false);
		$id->setRowTitle("id");
		$AddAccountToSupplier->add($id);
		
		$sapSupplierNumber = new invisibletext("sapSupplierNumber");
		$sapSupplierNumber->setDataType("string");
		$sapSupplierNumber->setLength(50);
		$sapSupplierNumber->setVisible(false);
		$sapSupplierNumber->setRowTitle("sap_supplier_number");
		$AddAccountToSupplier->add($sapSupplierNumber);
		
		$username = new textbox("username");
		$username->setGroup("AddAccountToSupplier");
		$username->setDataType("string");
		$username->setRowTitle("email_address");
		$username->setRequired(true);
		$username->setErrorMessage("email_address_error");
		$AddAccountToSupplier->add($username);
		
		$firstName = new textbox("firstName");
		$firstName->setDataType("string");
		$firstName->setGroup("AddAccountToSupplier");
		$firstName->setRowTitle("first_name");
		$firstName->setRequired(true);
		$firstName->setErrorMessage("field_error");
		$AddAccountToSupplier->add($firstName);
		
		$lastName = new textbox("lastName");
		$lastName->setDataType("string");
		$lastName->setGroup("AddAccountToSupplier");
		$lastName->setRowTitle("last_name");
		$lastName->setRequired(true);
		$lastName->setErrorMessage("field_error");
		$AddAccountToSupplier->add($lastName);
		
		$supplierDefaultLanguage = new dropdown("supplierDefaultLanguage");
		$supplierDefaultLanguage->setGroup("AddAccountToSupplier");
		$supplierDefaultLanguage->setDataType("string");
		$supplierDefaultLanguage->setLength(250);
		$supplierDefaultLanguage->setHelpId(804526573434);
		$supplierDefaultLanguage->setRowTitle("supplier_default_language");
		$supplierDefaultLanguage->setXMLSource("./apps/complaints/xml/languages.xml");
		$supplierDefaultLanguage->setValue("ENGLISH");
		$supplierDefaultLanguage->setRequired(true);
		$AddAccountToSupplier->add($supplierDefaultLanguage);
		
		$submit = new submit("submit");
		$AddAccountToSupplier->add($submit);
		
		$this->form->add($AddAccountToSupplier);
		$this->setFormValues();
		
	}
	
	function setFormValues()
	{
		$this->form->get("sapSupplierNumber")->setValue($_GET['sapSupplierNumber']);
	}
	
	public function getEmailNotification($id, $sender, $action, $owner, $sendTo)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $id . "</action><email_text>" . $this->form->get("description")->getValue() . "</email_text><createdBy>" . /*usercache::getInstance()->get(*/$owner/*)->getName()*/ . "</createdBy></$action>");
				
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");
	
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		email::send(usercache::getInstance()->get($sendTo)->getEmail(), $sender /*"intranet@scapa.com"*/, (translate::getInstance()->translate("AddAccountToSupplier_complaint") . " - Complaint ID: " . $id), "$email");
		
		return true;
	}
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
			$_GET['complaintId'],
			currentuser::getInstance()->getNTLogon(),
			addslashes($action),
			common::nowDateTimeForMysql()
		));
	}
	
	// Duplicate from complaint.php
	public function sendSupplierManual($supplierEmailAddress, $fromEmailAddress)
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

		$headers = array ('From' => $fromEmailAddress,
		'Subject' => "Scapa Supplier Manual and Account Details", 'To' => $supplierEmailAddress);

		$language = $this->form->get("supplierDefaultLanguage")->getValue();

		// Declare and issue values for extranet username and password
		$extranetUsername = $supplierEmailAddress;
		$extranetPassword = $this->externalPassword;

		switch ($language)
		{
			case 'ITALIAN':

				//$textMessage = "Spettabile Fornitore,\n\nIn allegato, Vogliate trovare il manuale relativo al nuovo sistema Scapa di gestione non conformit&#224; fornitori.\n\nPer qualsiasi informazione Vi invitiamo a contattare la Vs. interfaccia Scapa per gli acquisti o il Responsabile Sistema Integrato.\n\nDistinti saluti.";
				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchaser or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manualIT.pdf";

				break;

			case 'FRENCH':

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manualFR.pdf";

				break;

			case 'GERMAN':

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manualDE.pdf";

			default:

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manual.pdf";

				break;
		}


		$mime = new Mail_Mime();

		$mime->addAttachment($attachment);

		// No decoding required here.
		$mime->setTxtBody($textMessage);

		$body = $mime->get();

		$hdrs = $mime->headers($headers);

		$smtp = new Mail_smtp(
		array ('host' => $host,
		'auth' => true,
		'username' => $username,
		'password' => $password));

		$smtp->send($supplierEmailAddress, $hdrs, $body);

		$smtp->send("intranet@scapa.com", $hdrs, "Manual Sent");
	}
}

?>