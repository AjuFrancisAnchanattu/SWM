<?php

class register extends page
{
	// declare form
	private $form;


	function __construct()
	{
		parent::__construct();

		//page::redirect("http://ukdunapp022");

		$this->header->setLocation($this->getActivityLocation());

		$this->defineForm();

		$this->add_output("<register>");


		$this->form->loadSessionData();


		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();


			if ($this->form->validate())
			{
				// if it validates, do some database magic
				$query = $this->form->generateInsertQuery();

				email::send(usercache::getInstance()->get("jason.matthews@scapa.com", "intranet@scapa.com", "New user", $query));
				//$GLOBALS['sql_debug'] .= "<p>INSERT INTO employee $query</p>";
				//mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO employee " . $query);

				page::redirect("/");
			}
			else
			{
				$this->add_output("<error />");
			}
		}

		// show form
		$this->add_output($this->form->output());
		$this->add_output("</register>");

		$this->output('./xsl/register.xsl');
	}

	private function defineForm()
	{
		$this->form = new form("details");

		$default = new group("default");

		//$this->form->storeInSession(true);

		$ntLogon = new readonly("ntLogon");
		$ntLogon->setValue(currentuser::getInstance()->getNTLogon());
		$ntLogon->setRowTitle("NT Logon");
		$default->add($ntLogon);


		$firstName = new textbox("firstName");
		//$firstName->setValue(currentuser::getInstance()->getFirstName());
		$firstName->setRowTitle("First name");
		$firstName->setRequired(true);
		$default->add($firstName);


		$lastName = new textbox("lastName");
		//$lastName->setValue(currentuser::getInstance()->getLastName());
		$lastName->setRowTitle("Last name");
		$lastName->setRequired(true);
		$default->add($lastName);


		$email = new textbox("email");
		//$email->setValue(currentuser::getInstance()->getEmail());
		$email->setRowTitle("Email");
		$email->setRequired(true);
		$default->add($email);


		$phone = new textbox("phone");
		//$phone->setValue(currentuser::getInstance()->getPhone());
		$phone->setRowTitle("Phone");
		$phone->setDataType("string");
		$phone->setLength(50);
		$default->add($phone);


		$fax = new textbox("fax");
		//$fax->setValue(currentuser::getInstance()->getFax());
		$fax->setRowTitle("Fax");
		$fax->setDataType("string");
		$fax->setLength(50);
		$default->add($fax);



		$language = new dropdown("language");
		$language->setDataType("string");
		$language->setLength(50);
		$language->setRequired(true);
		$language->setXMLSource("./xml/languages.xml");
		$language->setRowTitle("Language");




		$locale = new dropdown("locale");
		$locale->setDataType("string");
		$locale->setLength(50);
		$locale->setRequired(true);
		$locale->setXMLSource("./xml/locales.xml");
		$locale->setRowTitle("Locale");



		$site = new dropdown("site");
		$site->setDataType("string");
		$site->setLength(50);
		$site->setRequired(true);
		$site->setSQLSource("membership","SELECT name, name AS data FROM sites ORDER BY name ASC");
		$site->setRowTitle("Site");



		$ip_array = explode(".", currentuser::getInstance()->getIP());

		// some defaults
		$language->setValue("ENGLISH");
		$locale->setValue("UK");
		$site->setValue("Dunstable");

		switch($ip_array[1])
		{
			case '14':

				$site->setValue("Ashton");
				break;

			case '12':

				$language->setValue("SPANISH");
				$locale->setValue("SPAIN");
				$site->setValue("Barcelona");
				break;

			case '7':

				$language->setValue("FRENCH");
				$locale->setValue("FRANCE");
				$site->setValue("Bellegarde");
				break;

			case '15':
				$site->setValue("Blackburn");
				break;

			case '3':
				$site->setValue("Denton");
				break;

			case '1':
				$site->setValue("Dunstable");

				if ($ip_array[2] == '175')
				{
					$locale->setValue("MALAYSIA");
					$site->setValue("Malaysia");
				}

				break;

			case '9':

				$language->setValue("ITALIAN");
				$locale->setValue("ITALY");
				$site->setValue("Ghislarengo");
				break;

			case '2':
				$site->setValue("Lymington");
				break;

			case '10':

				$language->setValue("GERMAN");
				$locale->setValue("GERMANY");
				$site->setValue("Mannheim");
				break;

			case '8':
            case '6':

            	$language->setValue("FRENCH");
            	$locale->setValue("FRANCE");
               	$site->setValue("Valence");
               	break;

            case '4':
                $site->setValue("Cable");
                break;

            case '11':

            	$language->setValue("GERMAN");
            	$locale->setValue("SWITZERLAND");
                $site->setValue("Rorschach");
                break;

            case '20':
                $site->setValue("Maidstone");
                break;

		}

		$default->add($language);
		$default->add($locale);
		$default->add($site);

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$default->add($submit);

		$this->form->add($default);




	}

}

?>