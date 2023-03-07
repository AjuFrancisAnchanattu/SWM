<?php

include('lib/complaintLib.php');

// PEAR mail classes
require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');

class emailPDF extends page
{
	private $complaintId;
	private $form;
	private $pdfType;
	private $language;
	private $formName;

	function __construct()
	{
		parent::__construct();

		if( isset( $_GET['complaintId'] ) )
		{
			$this->complaintId = $_GET['complaintId'];
		}
		else
		{
			die("no complaint id set");
		}

		if( isset( $_GET['pdfType'] ) )
		{
			$this->pdfType = $_GET['pdfType'];
		}
		else
		{
			die("no pdf type set");
		}

		if( isset( $_GET['lang'] ) )
		{
			$this->language = $_GET['lang'];
		}
		else
		{
			$this->language = 'EN';
		}

		$this->formName = "emailPDF_" . $this->complaintId . "_" . currentuser::getInstance()->getNTLogon();

		parent::__construct();

		$this->defineForm();

		$this->load();
	}

	private function show()
	{
		$this->setActivityLocation('Complaints - Customer - E-mail PDF');

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/customerComplaints/xml/menu.xml");
		page::setDebug(true);

		$this->add_output("<emailPDF>");
		$this->addSnapins();
		$this->add_output("<pdfType>" . $this->pdfType . "</pdfType>");
		$this->add_output($this->form->output());
		$this->add_output("</emailPDF>");

		$this->output('./apps/customerComplaints/xsl/emailPDF.xsl');
	}

	private function load()
	{
		if( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			$this->loadFromSession();

			if( $_POST['action'] == 'submit' )
			{
				$valid = $this->form->validate();

				if( $valid )
				{
					$this->email();
					page::redirect("/apps/customerComplaints/index?complaintId=" . $this->complaintId . "&pdfEmailSent=true" );
				}
			}
		}
		else
		{
			$this->loadFromDB();
		}

		$this->show();
	}

	private function defineForm()
	{
		$this->form = new form( $this->formName );
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$mainGroup = new group("mainGroup");
		$mainGroup->setBorder(false);

		$emailTo = new textbox("emailTo");
		$emailTo->setDataType("text");
		$emailTo->setRowTitle("email_to");
		$emailTo->setRequired(true);
		$mainGroup->add($emailTo);

		$emailAddress = new myTextbox("emailAddress");
		$emailAddress->setDataType("text");
		$emailAddress->setRowTitle("email_address");
		$emailAddress->setRequired(true);
		$emailAddress->setValidationType( myTextbox::$EMAIL );
		$mainGroup->add($emailAddress);

		$cc_to = new myCC("cc_to");
		$cc_to->setDataType("text");
		$cc_to->setRowTitle("multiple_cc_test");
		$cc_to->setRequired(false);
		$cc_to->setGroup("mainGroup");
		$cc_to->setOnClick("open_cc_window");
		$mainGroup->add($cc_to);

		$emailSubject = new textbox("emailSubject");
		$emailSubject->setDataType("text");
		$emailSubject->setRowTitle("email_subject");
		$emailSubject->setRequired(true);
		$mainGroup->add($emailSubject);

		$attachment = new myAttachment("attachment");
		$attachment->setTempFileLocation("/apps/customerComplaints/tmp");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setAnchorRef("attachment");
		$mainGroup->add($attachment);

		$comment = new myTextarea("comment");
		$comment->setDataType("text");
		$comment->setTable("evaluation");
		$comment->setGroup("mainGroup");
		$comment->setRowTitle("email_message");
		$comment->setLargeTextarea(true);
		$comment->setRequired(false);
		$comment->setHelpId(850);
		$mainGroup->add($comment);

		$submit = new submit("submit");
		$mainGroup->add($submit);

		$this->form->add($mainGroup);
	}

	private function email()
	{
		$attachmentArray = array();

		//attachments
		if(isset($_SESSION['apps'][$GLOBALS['app']][ $this->formName ]['mainGroup']['attachment']))
		{
			foreach($_SESSION['apps'][ $GLOBALS['app'] ][ $this->formName ]['mainGroup']['attachment'] as $file)
			{
				if(DEV)
					$server = "dev";
				else
					$server = "live";

				array_push( $attachmentArray, "/home/" . $server . $file['file']);
			}
		}

		// Send document and attachments in email
		$this->send(
			$this->form->get("emailAddress")->getValue(),
			currentuser::getInstance()->getEmail(),
			$this->form->get("emailSubject")->getValue(),
			$attachmentArray,
			$this->form->get("emailTo")->getValue(),
			$this->form->get("comment")->getValue(),
			currentuser::getInstance()->getName(),
			$this->form->get("cc_to")->getValue(),
			3,
			true);

		$complaintLib = new complaintLib();

		$complaintLib->addLog(
			$this->complaintId,
			"pdf_sent",
			"{TRANSLATE:" . $this->pdfType . "} PDF {TRANSLATE:sent_to} " . $this->form->get("emailTo")->getValue() . " (" . $this->form->get("emailAddress")->getValue() . ")",
			$this->form->get("comment")->getValue()
		);
	}


	private function loadFromSession()
	{
		$this->form->loadSessionData();
		$this->form->processPost();
		$this->form->putValuesInSession();
	}


	private function loadFromDB()
	{
		//here we populate form:
		if( DEV )
		{
			$this->form->get("emailAddress")->setValue( currentuser::getInstance()->getEmail() );
		}
		else
		{
			$this->form->get("emailAddress")->setValue( sapCustomer::getEmail( complaintLib::getSapCustomerId( $this->complaintId ) ) );
		}
		$this->form->get("attachment")->addFile("/apps/customerComplaints/pdf/files/" . $this->pdfType . "/" , "complaint_" . $this->pdfType . "_" . $this->complaintId . "_" . $this->language . ".pdf");

		$this->form->putValuesInSession();
	}


	private function addSnapins()
	{
		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/customerComplaints', 'ccSummary', true, true);
		//$snapins_left->register('apps/customerComplaints', 'ccLoad', true, true);
		$snapins_left->register('apps/customerComplaints', 'ccOwned', true, true);
		//$snapins_left->register('apps/customerComplaints', 'ccBookmarks', true, true);
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
	}


	/**
	 * Send Email with Attachment
	 *
	 * @param string $to (email@address.com)
	 * @param string $from (email@address.com)
	 * @param string $subject (Email Subject Header)
	 * @param string $attachmentLocation (/apps/dev/apps/complaints/word/file.rtf)
	 * @param string $sendToName (CSC Support)
	 * @param string $message (Body Message)
	 * @param string $fromName (Rick Pazik)
	 */
	private function send($to, $from, $subject, $attachmentLocation, $sendToName, $message, $fromName, $cc = "", $xPriority = 1, $isLocationAnArray = false)
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

		$xPriority == 1 ? $xPriority = 1 : $xPriority = 3;

		$headers = array (
			'From' => $from,
			'To' => $to,
			'Cc' => $cc,
			'Subject' => utf8_decode(html_entity_decode($subject, ENT_QUOTES)),
			'X-Priority' => $xPriority);

		$mime = new Mail_Mime();

		$mime->setTXTBody(utf8_decode(html_entity_decode($message, ENT_QUOTES)));

		// If the $attachmentLocation parameter comes back as an array then use the below otherwise use the link.
		if($isLocationAnArray)
		{
			foreach($attachmentLocation as $attachmentArraySpecific)
			{

				$mime->addAttachment($attachmentArraySpecific);
			}
		}
		else
		{
			$attachment = $attachmentLocation;
			$mime->addAttachment($attachment);
		}

		$body = $mime->get();
		$hdrs = $mime->headers($headers);
		$params = array(
			'host' => $host,
		    'auth' => false,
		    'username' => $username,
		    'password' => $password
		);

		$mail =& Mail::factory('smtp', $params);
		$mail->send($to . ", " . $cc, $hdrs, $body);
	}
}

?>