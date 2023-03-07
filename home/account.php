<?php

class account extends page
{
	// declare form
	private $form;
		
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Account');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		
		$this->add_output("<details>");

		$this->defineForm();
		$this->loadDetails();
		
		$this->form->loadSessionData();
				
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			
			if ($this->form->validate())
			{
				// if it validates, do some database magic
				$query = $this->form->generateUpdateQuery();
				
				//echo "<p>UPDATE employee $query WHERE NTLogon='".currentuser::getInstance()->getNTLogon()."'</p>";
				mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE employee $query WHERE NTLogon='".currentuser::getInstance()->getNTLogon()."'");
				
				header("Location: /");
				exit();
			}
			else 
			{
				echo "not valid";
			}
		}
		
		
		
		// show form
		$this->add_output($this->form->output());
		$this->add_output("</details>");
		$this->output('./xsl/details.xsl');
		
	}
	
	
	private function defineForm()
	{
		$this->form = new form("details");
		$this->form->showLegend(true);
		
		//$this->form->storeInSession(true);
		$personalDetails = new group("personalDetails");
		
		$firstName = new readonly("firstName");
		$firstName->setDataType("string");
		$firstName->setRequired(false);
		$firstName->setRowTitle("first_name");
		$personalDetails->add($firstName);
		
		
		$lastName = new readonly("lastName");
		$lastName->setRowTitle("last_name");
		$personalDetails->add($lastName);
		
		
		$email = new readonly("email");
		$email->setRowTitle("email");
		$personalDetails->add($email);
		
		
		$phone = new textbox("phone");
		$phone->setRowTitle("phone");
		$phone->setDataType("string");
		$phone->setLength(50);
		$personalDetails->add($phone);
		
		
		$fax = new textbox("fax");
		$fax->setRowTitle("fax");
		$fax->setDataType("string");
		$fax->setLength(50);
		$personalDetails->add($fax);
		
		
		$language = new dropdown("language");
		$language->setDataType("string");
		$language->setLength(50);
		$language->setRequired(true);
		$language->setXMLSource("./xml/languages.xml");
		$language->setRowTitle("language");
		$personalDetails->add($language);
		

		$locale = new dropdown("locale");
		$locale->setDataType("string");
		$locale->setLength(50);
		$locale->setRequired(true);
		$locale->setXMLSource("./xml/locales.xml");
		$locale->setRowTitle("locale");
		$personalDetails->add($locale);
		
		
		$site = new dropdown("site");
		$site->setDataType("string");
		$site->setLength(50);
		$site->setRequired(true);
		$site->setSQLSource("membership","SELECT name, name AS data FROM sites ORDER BY name ASC");
		$site->setRowTitle("site");
		$personalDetails->add($site);
		
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$personalDetails->add($submit);
		
		
		$this->form->add($personalDetails);
	}
	
	public function loadDetails()
	{
//		$this->form->get("firstName")->setValue(currentuser::getInstance()->getFirstName());
//		$this->form->get("lastName")->setValue(currentuser::getInstance()->getLastName());
//		$this->form->get("email")->setValue(currentuser::getInstance()->getEmail());
//		$this->form->get("phone")->setValue(currentuser::getInstance()->getPhone());
//		$this->form->get("fax")->setValue(currentuser::getInstance()->getFax());
//		$this->form->get("language")->setValue(currentuser::getInstance()->getLanguage());
//		$this->form->get("locale")->setValue(currentuser::getInstance()->getLocale());
//		$this->form->get("site")->setValue(currentuser::getInstance()->getSite());
	}
}


?>