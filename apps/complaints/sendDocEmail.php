<?php
//ini_set("default_charset", "iso-8859-1");

require_once('/usr/share/pear/Mail.php');
require_once('/usr/share/pear/Mail/mime.php');
require_once('/usr/share/pear/Mail/smtp.php');

//require_once('./NEWNEWPEAR/Mail.php');
//require_once('./NEWNEWPEAR/Mail/mime.php');
//require_once('./NEWNEWPEAR/Mail/smtp.php');

/**
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 21/08/2007
 */
class sendDocEmail extends page
{
	private $form;		

	function __construct()
	{
		parent::__construct();
		page::setDebug(true); // debug at the bottom
		
		$this->setActivityLocation('Complaints');
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/complaints/menu.xml");
		
		
		$this->add_output("<emailDocument>");
		
		$snapins_left = new snapinGroup('complaints_left');		//creates the snapin group for complaints
		$snapins_left->register('apps/complaints', 'loadComplaint', true, true);		//puts the Complaint load snapin in the page
		$snapins_left->register('apps/complaints', 'yourComplaints', true, true);		//puts the Complaint report snapin in the page
		$snapins_left->register('apps/complaints', 'bookmarkedComplaints', true, true);		//puts the Complaint bookmark snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->form->loadSessionData();
		
		$this->form->processDependencies();
		
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			
			if ($this->form->validate())
			{
				// if it validates, do some database magic
				if ($_REQUEST['mode'] == "newEmail")
				{
					//echo "HELLO";exit;
					$query = $this->form->generateInsertQuery();
					
					$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '" . $_GET['complaintId'] . "'");
					$fields = mysql_fetch_array($dataset);
					
					
					//////////////////////////new
//					$dataset2 = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE id = '" . $fields['sapCustomerNumber'] . "'");
//					$fields2 = mysql_fetch_array($dataset2);
//					
//					$sapCust = $fields2['emailAddress'];
					/////////////////////////////
					
					//emailAttachment::send($this->form->get("sendToEmailAddress")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), "Scapa Ltd - Document Enclosed", $this->form->get("description")->getValue(), $_GET['type'] . $_GET['complaintId'], $this->form->get("sendTo")->getValue());
					
					$userName = currentuser::getInstance()->getNTlogon();
					
					$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT locale FROM employee WHERE NTLogon = '" . $userName . "'");
					$fieldsEmployee = mysql_fetch_array($datasetEmployee);
					
					if($fieldsEmployee['locale'] == "GERMANY")
					{
						$legal = "Scapa Deutschland GmbH \nMarkircher Strasse 12 a \n68229 Mannheim \nRegistergericht: Mannheim HRB 7848 \nUST-Id. Nr. DE 143 875 475 \nGeschäftsführer: Mark Robert Stirzaker, Sandro Pellegrino";
					}
					elseif($fieldsEmployee['local'] == "ITALY")
					{
						$legal = "Scapa Italia S.p.A. \nVia Vittorio Emanuele 2°, 27 \n13030 Ghislarengo (VC) \nCod. Fisc. E Part.Iva n. IT00161310024 \nReg. Tribunale Vercelli n. 2133";
					}
					elseif($fieldsEmployee['local'] == "UK")
					{
						if($fieldsEmployee['site'] == "Ashton")
						{
							$legal = "Scapa UK Ltd. \nManchester Road \nAshton under Lyne \nOL7 0ED \nUK";
						}
						else 
						{
							$legal = "Scapa UK Ltd. \nUnit 15 \nThe Woodside Estate \nDunstable \nBedfordshire \nLU5 4TP \nUK";
						}
					}
					else
					{
						$legal = "";
					}
					
					$currentUser = usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail();
					
					if($this->getComplaintType($_GET['complaintId']) == "supplier_complaint")
					{
						$this->emailAttachmentSend(utf8_encode($this->decideTitle($this->form->get("title")->getValue(), $_GET['lang'])), $this->form->get("sendToEmailAddress")->getValue(), $currentUser, usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), "Scapa - Document Enclosed", $this->form->get("description")->getValue(), "supplier" . $_GET['type'] . "-" . $_GET['lang'] . $_GET['complaintId'], $this->form->get("sendTo")->getValue(), utf8_encode($this->decideClose($_GET['lang'])),utf8_encode($legal));
						$this->emailAttachmentSend(utf8_encode($this->decideTitle($this->form->get("title")->getValue(), $_GET['lang'])), $this->form->get("ccTo")->getValue(), $currentUser, usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), "Scapa - Document Enclosed", "You have been copied in on this email by " . $currentUser . "\n\nMessage:\n\n" . $this->form->get("description")->getValue(), "supplier" . $_GET['type'] . "-" . $_GET['lang'] . $_GET['complaintId'], $this->form->get("sendTo")->getValue(), utf8_encode($this->decideClose($_GET['lang'])), utf8_encode($legal));												
					}
					else 
					{
						$this->emailAttachmentSend(utf8_encode($this->decideTitle($this->form->get("title")->getValue(), $_GET['lang'])), $this->form->get("sendToEmailAddress")->getValue(), $currentUser, usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), "Scapa - Document Enclosed", $this->form->get("description")->getValue(), $_GET['type'] . "-" . $_GET['lang'] . $_GET['complaintId'], $this->form->get("sendTo")->getValue(), utf8_encode($this->decideClose($_GET['lang'])),utf8_encode($legal));
						$this->emailAttachmentSend(utf8_encode($this->decideTitle($this->form->get("title")->getValue(), $_GET['lang'])), $this->form->get("ccTo")->getValue(), $currentUser, usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName(), "Scapa - Document Enclosed", "You have been copied in on this email by " . $currentUser . "\n\nMessage:\n\n" . $this->form->get("description")->getValue(), $_GET['type'] . "-" . $_GET['lang'] . $_GET['complaintId'], $this->form->get("sendTo")->getValue(), utf8_encode($this->decideClose($_GET['lang'])), utf8_encode($legal));	
					}
					
					$this->addLog(translate::getInstance()->translate("document_emailed_" . $_GET['type'] . "") . " " .$this->form->get("sendToEmailAddress")->getValue(), $_GET['complaintId'], $this->form->get("description")->getValue());
					
					page::redirect('./'); // redirects to homepage
				
				}
			}
		}
		
		// show form
		$this->add_output($this->form->output());
		
		$this->add_output("</emailDocument>");
		$this->output('./apps/complaints/xsl/complaints.xsl');
	}
	
	public function emailAttachmentSend($title, $to, $from, $fromName, $subject, $message, $attachment, $sendToName, $close, $legal)//added $title
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
		
		$headers = array ('From' => $from,
		  'To' => $to,
		  'Subject' => $subject,
		  'To' => $to);
		 /* WC AE - Have added in UTF-8 headers */ 
		 
		$attachment = "/home/dev/apps/complaints/word/files/" . $attachment . ".rtf";
		
		$attachmentSpecified = "";

		$textMessage = "" . $title . " " . $sendToName . ",\n\n" . $message . "\n\n" . $close . "\n". $fromName . "\n\n" . $legal . "";
		
		$messageUnits = array();
		$newMessage = "";
		$messageUnits = str_split($textMessage);
		foreach($messageUnits as $ord){
			$newMessage .= "&".ord($ord).";";
		}

		$mime = new Mail_Mime(); 
		/* WC - AE: encoded all these chars incase containing german characters*/
		$mime->setTxtBody(utf8_decode($textMessage)); 
		
		$mime->txtHeaders($addHeaders);

		$mime->addAttachment($attachment); 
		
		
		$body = $mime->get(); 
		
		$hdrs = $mime->headers($headers);
		
		$smtp = new Mail_smtp( // MAY BE THIS LINE HERE!
		  array ('host' => $host,
		    'auth' => true,
		    'username' => $username,
		    'password' => $password));
		//echo $body;exit;
		$smtp->send($to, $hdrs, $body);
		
		// Error lines have been taken out to save error message
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	 function encode($in_str, $charset) {
	   $out_str = $in_str;
	   if ($out_str && $charset) {
		   // define start delimimter, end delimiter and spacer
		   $end = "?=";
		   $start = "=?" . $charset . "?B?";
		   $spacer = $end . "\r\n " . $start;
		   // determine length of encoded text within chunks
		   // and ensure length is even
		   $length = 75 - strlen($start) - strlen($end);
		   $length = floor($length/2) * 2;
		   // encode the string and split it into chunks 
		   // with spacers after each chunk
		   $out_str = base64_encode($out_str);
		   $out_str = chunk_split($out_str, $length, $spacer);
		   // remove trailing spacer and 
		   // add start and end delimiters
		   $spacer = preg_quote($spacer);
		   $out_str = preg_replace("/" . $spacer . "$/", "", $out_str);
		   $out_str = $start . $out_str . $end;
	   }
	   return $out_str;
	}

	private function defineForm()
	{	
		$this->form = new form("emailDocForm");
		
		$emailDocForm = new group("emailDocForm");
		
		//added whole field
		$title = new dropdown("title");
		$title->setGroup("emailDocForm");
		$title->setDataType("string");
		$title->setRowTitle("title");
		$title->setXMLSource("./apps/complaints/xml/title.xml");
		$title->setRequired(true);
		$emailDocForm->add($title);
		
		$sendTo = new textbox("sendTo");
		$sendTo->setGroup("emailDocForm");
		$sendTo->setDataType("string");
		$sendTo->setRowTitle("name");
		$sendTo->setRequired(false);
		$emailDocForm->add($sendTo);		
		
		//////////////////////////new
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '" . $_GET['complaintId'] . "'");
		$fields = mysql_fetch_array($dataset);

		$dataset2 = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM customer WHERE id = '" . $fields['sapCustomerNumber'] . "'");
		$fields2 = mysql_fetch_array($dataset2);
		
		$sapCust = $fields2['emailAddress'];
		
		//////////////////////////////
		
		$sendToEmailAddress = new textbox("sendToEmailAddress");
		$sendToEmailAddress->setGroup("emailDocForm");
		$sendToEmailAddress->setDataType("string");
		$sendToEmailAddress->setRowTitle("email_address");
		$sendToEmailAddress->setValue($sapCust);
		$sendToEmailAddress->setRequired(true);
		$emailDocForm->add($sendToEmailAddress);
		
		$ccTo = new multipleCC("ccTo");
		$ccTo->setGroup("emailDocForm");
		$ccTo->setDataType("text");
		$ccTo->setRowTitle("cc_to");
		$ccTo->setRequired(false);
		$emailDocForm->add($ccTo);		
		
		$description = new textarea("description");
		$description->setDataType("text");
		$description->setRowTitle("comment_complaint");
		$description->setRequired(false);
		$emailDocForm->add($description);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$emailDocForm->add($submit);
		
		$this->form->add($emailDocForm);
		$this->setFormValues();
		
	}
	
	function setFormValues()
	{
		if ($_REQUEST['mode'] == "add")
		{
			$today = date("d/m/Y",time());
			
			$this->form->get("logDate")->setValue($today);			
			$this->form->get("sentFrom")->setValue(currentuser::getInstance()->getNTLogon());
		}
	}
	
	public function decideTitle($title, $lang)
	{
		if($lang == "en")				//english document
		{
			$salutation = "Dear";
			//$closing = "Regards,";
			if ($title == "Mr." || $title == "Herr" || $title == "Sig." || $title == "Monsieur")
			{
				$title = "Mr.";
			}
			elseif ($title == "Mrs." || $title == "frau" || $title == "Sig.ra" || $title == "Madame") 
			{
				$title = "Mrs.";
			}
			elseif ($title == "Ms.") 
			{
				$title = "Ms.";
			}
			else
			{
				$title = "";
			}
		}
		elseif ($lang == "de")			//german document
		{
			if ($title == "Mr." || $title == "Herr" || $title == "Sig." || $title == "Monsieur")
			{
				$salutation = "Sehr geehrter";
				$title = "Herr";
			}
			elseif ($title == "Mrs." || $title == "frau" || $title == "Sig.ra" || $title == "Madame")				
			{
				$salutation = "Sehr geehrte";
				$title = "Frau";
			}
			else
			{
				$salutation = "";
				$title = "";
			}
		}
		elseif ($lang == "it") 			//italian document
		{
			if ($title == "Mr." || $title == "Herr" || $title == "Sig." || $title == "Monsieur")
			{
				$salutation = "Spett.le";
				$title = "Sig.";
			}
			elseif ($title == "Mrs." || $title == "frau" || $title == "Frau" || $title == "Sig.ra" || $title == "Madame") 
			{
				$salutation = "Gent.ma";
				$title = "Sig.ra";
			}
			else
			{
				$salutation = "";
				$title = "";
			}
		}
		elseif ($lang = "fr")			//french document
		{
			if ($title == "Mr." || $title == "Herr" || $title == "Sig." || $title == "Monsieur")
			{
				$salutation = "Cher";
				$title = "Monsieur";
			}
			elseif ($title == "Mrs." || $title == "frau" || $title == "Sig.ra" || $title == "Madame")
			{
				$salutation = "Chere";
				$title = "Madame";
			}
			else
			{
				$salutation = "";
				$title = "";
			}
		}
		else 							//default
		{
			$title = "";
			$salutation = "";
		}
		$head = $salutation . " " . $title;
		return $head;
	}
	
	public function decideClose($lang)
	{
		if($lang == "en")				//english document
		{
			$closing = "Regards,";
		}
		elseif ($lang == "de")			//german document
		{
			$closing = "Mit freundlichen Grüssen";
		}
		elseif ($lang == "it") 			//italian document
		{
			$closing = "Cordiali saluti";
		}
		elseif ($lang = "fr")			//french document
		{
			$closing = "Sincères salutations";
		}
		else 							//default
		{
			$closing = "...";
		}
		return $closing;
	}
	
	public function addLog($action, $id, $description)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$id,
		currentuser::getInstance()->getNTLogon(),
		$action,
		common::nowDateTimeForMysql(),
		$description
		));
	}
	
	public function getComplaintType($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT typeOfComplaint FROM complaint WHERE `id` = '" . $id . "'");
		
		$fields = mysql_fetch_array($dataset);
		
		$complaintType = $fields['typeOfComplaint'];
		
		return $complaintType;
	}
}

?>