<?php

require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');

class externalFunctions extends page
{
	function __construct()
	{
		parent::__construct();

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = " . $_REQUEST['id'] . "");
		$fields = mysql_fetch_array($dataset);

		switch ($_REQUEST['mode'])
		{
			case 'send':

				$this->doEmail("extSendEmail", $fields);
				$this->addLog(translate::getInstance()->translate("complaint_externally_sent") . " - " . usercache::getInstance()->get($fields['owner'])->getName() . "");

				break;
				
			case 'addAccountToSupplier':
					
				// Redirect to Add Account To Supplier page and check for existing users ...
				page::redirect("addAccountToSupplier?sapSupplierNumber=" . $fields['sapCustomerNumber'] . "&" . "complaintId=" . $_REQUEST['id'] . "");
				
				break;

			case 'reminder':
				
				$datasetExt = mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("SELECT * FROM users WHERE username = '" . $_GET['supplierUsername'] . "'");
				$fieldsExt = mysql_fetch_array($datasetExt);
				
				if(mysql_num_rows($datasetExt) > 0)
				{
					$this->doExtEmailNew("extReminderEmail", $fieldsExt, $_REQUEST['id']);
					$this->addLog(translate::getInstance()->translate("reminder_sent_to_supplier") . " - " . $_GET['supplierUsername']);
				}
				else 
				{
					$this->addLog(translate::getInstance()->translate("reminder_not_sent_to_supplier"));
					page::redirect('./?emailSent=false'); // redirects to homepage
				}

//				$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM supplier WHERE id = '" . $fields['sapCustomerNumber'] . "'");
//				$fieldsSAP = mysql_fetch_array($datasetSAP);
//				
//				if(mysql_num_rows($datasetSAP) > 0)
//				{
//					$this->doExtEmail("extReminderEmail", $fieldsSAP);
//					$this->addLog(translate::getInstance()->translate("reminder_sent_to_supplier"));	
//				}
//				else 
//				{
//					$this->addLog(translate::getInstance()->translate("reminder_not_sent_to_supplier"));
//					page::redirect('./?emailSent=false'); // redirects to homepage
//				}

				break;
				
			case 'resendSupplierComplaintEmail':
				
				//$_REQUEST['supplierId'] = str_replace("%20", "", $_REQUEST['supplierId']); // For NA Spaces in ID Names
				
				$datasetExt = mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("SELECT * FROM users WHERE username = '" . $_GET['supplierUsername'] . "'");
				$fieldsExt = mysql_fetch_array($datasetExt);
				
				if(mysql_num_rows($datasetExt) > 0)
				{
					$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '" . $_REQUEST['id'] . "'");
					$fieldsComplaint = mysql_fetch_array($datasetComplaint);
					
					if(mysql_num_rows($datasetComplaint) != 0)
					{
						$this->doExtResendEmail("newExternalResend", $fieldsComplaint, $fieldsExt);
						$this->addLog(translate::getInstance()->translate("complaint_email_resent_to_supplier") . " - " . $_GET['supplierUsername']);
					}
					else 
					{
						$this->addLog(translate::getInstance()->translate("invalid_or_no_complaint_found_email_not_sent"));
						page::redirect('./?emailSent=false'); // redirects to homepage
					}
				}
				else 
				{
					$this->addLog(translate::getInstance()->translate("invalid_or_no_complaint_found_email_not_sent"));
					page::redirect('./?emailSent=false'); // redirects to homepage
				}
				
//				$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM supplier WHERE id = '" . $_REQUEST['supplierId'] . "'");
//				$fieldsSAP = mysql_fetch_array($datasetSAP);
//				
//				if(mysql_num_rows($datasetSAP) != 0)
//				{
//					$datasetComplaint = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '" . $_REQUEST['id'] . "'");
//					$fieldsComplaint = mysql_fetch_array($datasetComplaint);
//					
//					if(mysql_num_rows($datasetComplaint) != 0)
//					{
//						$this->doExtResendEmail("newExternalResend", $fieldsComplaint, $fieldsSAP);
//						$this->addLog(translate::getInstance()->translate("complaint_email_resent_to_supplier"));	
//					}
//					else 
//					{
//						$this->addLog(translate::getInstance()->translate("invalid_or_no_complaint_found_email_not_sent"));
//						page::redirect('./?emailSent=false'); // redirects to homepage
//					}
//				}
//				else 
//				{
//					$this->addLog(translate::getInstance()->translate("invalid_or_no_email_address_found_email_not_sent"));
//					page::redirect('./?emailSent=false'); // redirects to homepage
//				}
				
				break;

			case 'resendManual':
				
				//$_REQUEST['supplierId'] = str_replace("%20", "", $_REQUEST['supplierId']); // For NA Spaces in ID Names
				
				$datasetExt = mysqlExt::getInstance()->selectDatabase("scapa_external")->Execute("SELECT * FROM users WHERE username = '" . $_GET['supplierUsername'] . "'");
				$fieldsExt = mysql_fetch_array($datasetExt);
				
				if(mysql_num_rows($datasetExt) > 0)
				{
					$this->resendManualNew(usercache::getInstance()->get($_GET['supplierUsername'])->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail(), $_GET['supplierDefaultLanguage'], $fieldsExt['username'], $fieldsExt['password']);
					$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier") . " - " . $_GET['supplierUsername']);
				}
				else 
				{
					$this->addLog(translate::getInstance()->translate("invalid_or_no_email_address_found_manual_not_sent"));
					page::redirect('./?emailSent=false'); // redirects to homepage
				}

//				$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM supplier WHERE id = '" . $_REQUEST['supplierId'] . "'");
//				$fieldsSAP = mysql_fetch_array($datasetSAP);
//
//				if(mysql_num_rows($datasetSAP) != 0)
//				{
//					$supplierEmailAddress = $fieldsSAP['emailAddress'];
//					$fromEmailAddress = usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getEmail();
//					$defaultLanguage = $_REQUEST['language'];
//
//					// Now Check and Add Account if can be done
//					$datasetExtUsers = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `users` WHERE sapSupplierNumber = '" . $_REQUEST['supplierId'] . "'");
//					$fieldsExtUsers = mysql_fetch_array($datasetExtUsers);
//
//					if(mysql_num_rows($datasetExtUsers) == 1)
//					{
//						// Insert Employee Details for Site to work correctly but set as externalUser is 1.
//						$datasetEmployee = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `employee` WHERE NTLogon = '" . $supplierEmailAddress . "'");
//
//						if(mysql_num_rows($datasetEmployee) == 1)
//						{
//							$this->resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $fieldsExtUsers['username'], $fieldsExtUsers['password']);
//							$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier"));
//						}
//						else
//						{
//							mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO `employee` (NTLogon, email, enabled, externalUser, language) VALUES ('" . $fieldsExtUsers['username'] . "','" . $fieldsExtUsers['username'] . "', 1,1,'" . $defaultLanguage . "')");	
//
//							$this->resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $fieldsExtUsers['username'], $fieldsExtUsers['password']);
//							$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier"));
//						}
//
//						//$this->resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $fieldsExtUsers['username'], $fieldsExtUsers['password']);
//						//$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier"));
//					}
//					else
//					{
//						$username = $supplierEmailAddress;
//
//						$externalPassword = rand("100000", "999999");
//
//						// Insert Login Details to Extranet
//						mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO `users` (username, password, sapSupplierNumber, emailAddress) VALUES ('" . $username . "','" . $externalPassword . "','" . $_REQUEST['supplierId'] . "','" . $supplierEmailAddress . "')");
//
//						// Insert Employee Details for Site to work correctly but set as externalUser is 1.
//						$datasetEmployee = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `employee` WHERE NTLogon = '" . $username . "'");
//
//						if(mysql_num_rows($datasetEmployee) == 1)
//						{
//							$this->resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $username, $externalPassword);
//							$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier"));
//						}
//						else
//						{
//							mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO `employee` (NTLogon, email, enabled, externalUser, language) VALUES ('" . $username . "','" . $supplierEmailAddress . "', 1,1, '" . $defaultLanguage . "')");
//
//							$this->resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $username, $externalPassword);
//							$this->addLog(translate::getInstance()->translate("manual_resent_to_supplier"));
//						}
//					}
//
//				}
//				else
//				{
//					$this->addLog(translate::getInstance()->translate("invalid_or_no_email_address_found_manual_not_sent"));
//					page::redirect('./?emailSent=false'); // redirects to homepage
//				}

				break;

			default:

				break;
		}

		page::redirect('./?emailSent=true'); // redirects to homepage

	}

	private function doEmail($action, $fields)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>". $_REQUEST['id'] ."</action><sent_from>". usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() ."</sent_from><owner>". usercache::getInstance()->get($fields['owner'])->getName() ."</owner></$action>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/complaints/xsl/email.xsl");

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);

   		email::send(usercache::getInstance()->get($fields['owner'])->getEmail(), /*"intranet@scapa.com"*/usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "Complaints Action Reminder", $email);
	}
	
	private function doExtEmail($action, $fieldsSAP)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>". $_REQUEST['id'] ."</action><sent_from>". usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() ."</sent_from></$action>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/complaints/xsl/email.xsl");

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);

   		email::send($fieldsSAP['emailAddress'], /*"intranet@scapa.com"*/usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "Complaints Action Reminder", $email);
	}
	
	private function doExtEmailNew($action, $fieldsSAP, $complaintId)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>". $complaintId ."</action><sent_from>". usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName() ."</sent_from></$action>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/complaints/xsl/email.xsl");

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);

   		email::send($fieldsSAP['emailAddress'], /*"intranet@scapa.com"*/usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "Complaints Action Reminder", $email);
	}
	
	private function doExtResendEmail($action, $fieldsComplaint, $fieldsExt)
	{
		$dom = new DomDocument;
		$dom->loadXML("<$action><action>" . $fieldsComplaint['id'] . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><buyer_name>" . usercache::getInstance()->get($fieldsComplaint['sp_buyer'])->getName() . "</buyer_name><buyer_email>" . usercache::getInstance()->get($fieldsComplaint['sp_buyer'])->getEmail() . "</buyer_email><buyer_phone>" . usercache::getInstance()->get($fieldsComplaint['sp_buyer'])->getPhone() . "</buyer_phone></$action>");

        // load xsl
        $xsl = new DomDocument;
        $xsl->load("./apps/complaints/xsl/email.xsl");

        // transform xml using xsl
        $proc = new xsltprocessor;
        $proc->importStyleSheet($xsl);

   		$email = $proc->transformToXML($dom);

   		email::send($fieldsExt['emailAddress'], /*"intranet@scapa.com"*/usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), "Complaints Action Reminder - " . $fieldsComplaint['id'] . "", $email);
	}

//	public function resendManual($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $supplierUsername, $supplierPassword)
//	{
//		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
//		{
//			$host = common::getUSAMailHost();
//			$username = common::getUSAMailUsername();
//			$password = common::getUSAMailPassword();
//		}
//		else
//		{
//			$host = common::getMailHost();
//			$username = common::getMailUsername();
//			$password = common::getMailPassword();
//		}
//
//		$headers = array ('From' => $fromEmailAddress,
//		  'Subject' => "Scapa Supplier Manual and Account Details");
//
//		//Update SAP Customer Name and External Sales Name
//		//$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `language` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
//		//$sapFields = mysql_fetch_array($sapDataset);
//
//		// Retrieve prefered language for SAP supplier
//		$language = $defaultLanguage;
//
//		// Declare and issue values for extranet username and password
//		$extranetUsername = $supplierUsername;
//		$extranetPassword = $supplierPassword;
//
//		switch ($language)
//		{
//			case 'ITALIAN':
//
//				$textMessage = "Spettabile Fornitore,\n\nIn allegato, Vogliate trovare il manuale relativo al nuovo sistema Scapa di gestione non conformità fornitori.\n\nPer qualsiasi informazione Vi invitiamo a contattare la Vs. interfaccia Scapa per gli acquisti o il Responsabile Sistema Integrato.\n\nDistinti saluti.";
//				$attachment = "/home/live/apps/complaints/data/manualIT.pdf";
//
//				break;
//
//			case 'FRENCH':
//
//				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
//				$attachment = "/home/live/apps/complaints/data/manualFR.pdf";
//
//				break;
//
//			case 'GERMAN':
//
//				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
//				$attachment = "/home/live/apps/complaints/data/manualDE.pdf";
//
//			default:
//
//				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchasor or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
//				$attachment = "/home/live/apps/complaints/data/manual.pdf";
//
//				break;
//		}
//
//
//		$mime = new Mail_Mime();
//
//		$mime->addAttachment($attachment);
//
//		// No decoding required here.
//		$mime->setTxtBody($textMessage);
//
//		$body = $mime->get();
//
//		$hdrs = $mime->headers($headers);
//
//		$smtp = new Mail_smtp(
//		  array ('host' => $host,
//		    'auth' => true,
//		    'username' => $username,
//		    'password' => $password));
//
//		$smtp->send($supplierEmailAddress, $hdrs, $body);
//	}

	public function resendManualNew($supplierEmailAddress, $fromEmailAddress, $defaultLanguage, $supplierUsername, $supplierPassword)
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
		  'Subject' => "Scapa Supplier Manual and Account Details");

		//Update SAP Customer Name and External Sales Name
		//$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `language` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
		//$sapFields = mysql_fetch_array($sapDataset);

		// Retrieve prefered language for SAP supplier
		$language = $defaultLanguage;

		// Declare and issue values for extranet username and password
		$extranetUsername = $supplierUsername;
		$extranetPassword = $supplierPassword;

		switch ($language)
		{
			case 'ITALIAN':

				$textMessage = "Spettabile Fornitore,\n\nIn allegato, Vogliate trovare il manuale relativo al nuovo sistema Scapa di gestione non conformità fornitori.\n\nPer qualsiasi informazione Vi invitiamo a contattare la Vs. interfaccia Scapa per gli acquisti o il Responsabile Sistema Integrato.\n\nDistinti saluti.";
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
	}

	private function addLog($action)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
		$_REQUEST['id'],
		currentuser::getInstance()->getNTLogon(),
		addslashes($action),
		common::nowDateTimeForMysql()
		));
	}
}

?>