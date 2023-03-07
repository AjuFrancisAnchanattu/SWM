<?php

class details extends page
{
	// declare form
	private $form;
	private $currentUserDN;
	private $currentUserTelNumber;
	private $currentUserMobNumber;
	private $currentUserTitle;
	private $currentUserDepartment;


	function __construct()
	{
		parent::__construct();
		
		page::redirect("http://ukdunapp022");
		
		$this->setActivityLocation('Preferences');

		$this->setDebug(true);

		$this->header->setLocation($this->getActivityLocation());

		$this->add_output("<details>");

		if(isset($_REQUEST['updated']))
	    {
	    	if($_REQUEST['updated'] == "true")
	    	{
	    		$this->add_output("<detailsUpdated>1</detailsUpdated>");
	    		$this->add_output("<showNotice>0</showNotice>");
	    	}
	    	else
	    	{
	    		$this->add_output("<detailsUpdated>0</detailsUpdated>");
	    		$this->add_output("<showNotice>1</showNotice>");
	    	}
	    }

	    $this->getCurrentUserDN(currentuser::getInstance()->getNTLogon());


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

				mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE employee $query WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'");

		    	//BEGIN

				$ad = ldap_connect("10.1.199.11");

			    if(!$ad)
			    {
			    	$ad = ldap_connect("10.14.199.11")
			          or die("Couldn't connect to UKASHDCF011 or UKDUNDC001 AD!");
			    }

			    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);

			    $bd = ldap_bind($ad,"kayako@scapa.local","8p0WHaw")
						or die("Couldn't bind to AD!");

			    $user = array();
			    $phone = "-";
			    $fax = "-";
			    $site = "-";
			    $mobile = "-";
			    $role = "-";
			    $department = "-";

				if($this->form->get("phone")->getValue() != "")
			    {
			    	$phone = $this->form->get("phone")->getValue();
			    }

			    if($this->form->get("fax")->getValue() != "")
				{
					$fax = $this->form->get("fax")->getValue();
			    }

			    if($this->form->get("site")->getValue() != "")
			    {
			    	$site = $this->form->get("site")->getValue();
			    }

			    if($this->form->get("mobile")->getValue() != "")
			    {
			    	$mobile = $this->form->get("mobile")->getValue();
			    }

			    if($this->form->get("role")->getValue() != "")
			    {
			    	$role = $this->form->get("role")->getValue();
			    }

			    if($this->form->get("department")->getValue() != "")
			    {
			    	$department = $this->form->get("department")->getValue();
			    }

				$user["telephonenumber"] = $phone;
				$user["facsimileTelephoneNumber"] = $fax;
				$user["physicalDeliveryOfficeName"] = $site;
				$user["mobile"] = $mobile;
				$user["title"] = $role;
				$user["department"] = $department;

			    //$result = ldap_mod_replace($ad, $dn, $user);
			    $result = ldap_mod_replace($ad, $this->currentUserDN, $user);
			    //$result = ldap_modify($ad, $dn, $user);

			    if($result)
			    {
			    	ldap_unbind($ad);

			    	mysql::getInstance()->selectDatabase("membership")->Execute("UPDATE employee SET updated = 1 WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'");

			    	page::redirect("./details?updated=true");
			    }
			    else
			    {
			    	ldap_unbind($ad);

			    	page::redirect("./details?updated=false");
			    }
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
		$phone->setRequired(true);
		$personalDetails->add($phone);

		$fax = new textbox("fax");
		$fax->setRowTitle("fax");
		$fax->setDataType("string");
		$fax->setLength(50);
		$fax->setRequired(false);
		$personalDetails->add($fax);

		$mobile = new textbox("mobile");
		$mobile->setRowTitle("mobile");
		$mobile->setDataType("string");
		$mobile->setLength(50);
		$mobile->setRequired(false);
		$personalDetails->add($mobile);


		$department = new textbox("department");
		$department->setRowTitle("department");
		$department->setDataType("string");
		$department->setLength(255);
		$department->setRequired(true);
		$personalDetails->add($department);

		$role = new textbox("role");
		$role->setRowTitle("job_title");
		$role->setDataType("string");
		$role->setRequired(true);
		$role->setLength(255);
		$personalDetails->add($role);


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
		$this->form->get("firstName")->setValue(currentuser::getInstance()->getFirstName());
		$this->form->get("lastName")->setValue(currentuser::getInstance()->getLastName());
		$this->form->get("email")->setValue(currentuser::getInstance()->getEmail());
		$this->form->get("phone")->setValue(currentuser::getInstance()->getPhone());
		$this->form->get("fax")->setValue(currentuser::getInstance()->getFax());
		$this->form->get("department")->setValue(currentuser::getInstance()->getDepartment());
		$this->form->get("language")->setValue(currentuser::getInstance()->getLanguage());
		$this->form->get("locale")->setValue(currentuser::getInstance()->getLocale());
		$this->form->get("site")->setValue(currentuser::getInstance()->getSite());
		$this->form->get("role")->setValue(currentuser::getInstance()->getRole());
		$this->form->get("mobile")->setValue(currentuser::getInstance()->getMobile());
	}

	private function getCurrentUserDN($NTLogon)
	{
		$ds = ldap_connect(common::getMainDC());

	    if(!$ds)
	    {
	    	$ds = ldap_connect(common::getBackupDC())
	          or die("Couldn't connect to UKASHDCF011 or UKDUNDC001 AD!");
	    }

		$dn[]='DC=scapa,DC=local';

		$id[] = $ds;

		$filter = "samaccountname=$NTLogon";

		$ldapBind = ldap_bind($ds,"kayako","8p0WHaw");

		if (!$ldapBind)
		{
			die('Cannot Bind to LDAP server');
		}

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

		$result = ldap_search($id,$dn,$filter);

		$search = false;

		foreach ($result as $value)
		{
		    if(ldap_count_entries($ds,$value)>0)
		    {
		        $search = $value;
		        break;
		    }
		}

		if($search)
		{
		    $info = ldap_get_entries($ds, $search);
		}
		else
		{
		    $info = false;
		}

		if($info != false)
		{
			$this->currentUserDN = htmlentities($info[0]["dn"]);
		}
		else
		{
			$this->currentUserDN = "";
		}

		ldap_unbind($ds);
	}
}


?>