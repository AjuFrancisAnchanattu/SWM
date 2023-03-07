<?php
require 'ijfProcess.php';
require 'commercialPlanning.php';
require 'dataAdministration.php';
require 'finance.php';
require 'production.php';
//require 'productionSite.php';
require 'productOwner.php';
require 'purchasing.php';

/**
 * This is the IJF (Item Justification Form) Application.
 *
 * This is the IJF class.  This class has a small initiation form and controls the other parts of the IJF process.
 *
 * @package apps
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class ijf
{
	private $dataAdministration;
	private $production;
	private $purchasing;
	private $finance;
	private $productOwner;
	private $commercialPlanning;

	private $id;
	private $status;
	//private $value;
	//private $volume;
	//private $material_type;
	public $form;

	public $attachments;

	private $loadedFromDatabase = false;


	private $customerName = "";


	function __construct()
	{

		$this->defineForm();			//creates the form

		$this->form->loadSessionData();	//puts any data in the session back in the form


		if (isset($_SESSION['apps'][$GLOBALS['app']]['ijf']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the IJF is loaded from the database
		}

		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id'];		//checks if there is a IJF id in the session
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = "";
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['complete']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = false;
		}

		$this->loadSessionSections();		//loads any of the IJF sections that are stored in the session

		$this->form->processDependencies();
	}


	/**
	 * This function checks what sections of the IJF are stored in the session.
	 * If it finds one it creates a new instance of the section.
	 *
	 */
	private function loadSessionSections()
	{
		if (isset($_SESSION['apps'][$GLOBALS['app']]['dataAdministration']))
		{
			$this->dataAdministration = new dataAdministration($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['purchasing']))
		{
			$this->purchasing = new purchasing($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['production']))
		{
			$this->production = new production($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['productOwner']))
		{
			$this->productOwner = new productOwner($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['commercialPlanning']))
		{
			$this->commercialPlanning = new commercialPlanning($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['finance']))
		{
			$this->finance = new finance($this);
		}
	}

	/**
	 * This is the IJF load function.  An ID of the a IJF is passed to the function and from this the IJF fields are loaded.
	 * The function loads all the IJF details into the IJF form and the session.
	 * After loading the initial IJF, each table will be search to find all the sections for the IJF.
	 * These sections are then told to load.
	 *
	 * @param int $id
	 * @return boolean
	 */
	public function load($id)
	{

		page::addDebug("loading IJF id=$id", __FILE__, __LINE__);

		unset ($_SESSION['apps'][$GLOBALS['app']]);
		unset ($this->dataAdministration);
		unset ($this->purchasing);
		unset ($this->production);
		unset ($this->productOwner);
		unset ($this->commercialPlanning);
		unset ($this->finance);


		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;

		$this->form->setStoreInSession(true);


		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['ijf']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;

			$this->form->get("width")->setValue(array($fields['width_quantity'], $fields['width_measurement']));
			$this->form->get("ijfLength")->setValue(array($fields['ijfLength_quantity'], $fields['ijfLength_measurement']));
			$this->form->get("thickness")->setValue(array($fields['thickness_quantity'], $fields['thickness_measurement']));



			foreach ($fields as $key => $value)			//puts each value of each field into the IJF form
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}

			$this->form->get("attachment")->load("/apps/ijf/attachments/" . $this->id . "/");

			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			unset($_SESSION['apps'][$GLOBALS['app']]['production']);
			unset($_SESSION['apps'][$GLOBALS['app']]['purchasing']);
			unset($_SESSION['apps'][$GLOBALS['app']]['productOwner']);
			unset($_SESSION['apps'][$GLOBALS['app']]['commercialPlanning']);
			unset($_SESSION['apps'][$GLOBALS['app']]['finance']);
			unset($_SESSION['apps'][$GLOBALS['app']]['dataAdministration']);
			return false;
		}


		/**
		 * checks for a dataAdministration section of the IJF and loads it
		 */
		///$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM dataAdministration WHERE ijfId = $id");
		//while($fields = mysql_fetch_array($dataset))
		//{
			$this->dataAdministration = new dataAdministration($this);
			if (!$this->dataAdministration->load($id))
			{
				unset($this->dataAdministration);
			}
			page::addDebug("load dataAdministration for IJF id=$id", __FILE__, __LINE__);
		//}

		/**
		 * checks for a production section of the IJF and loads it
		 */
		//$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM production WHERE ijfId = $id");
		//while($fields = mysql_fetch_array($dataset))
		//{
			$this->production = new production($this);
			if(!$this->production->load($id))
			{
				unset($this->production);
			}
			page::addDebug("load production for IJF id=" . $id, __FILE__, __LINE__);
		//}

		/**
		 * checks for a purchasing section of the IJF and loads it
		 */
		//$dataset = mysql::getInstance()->selectDatabase("SLOBS")->Execute("SELECT id FROM purchasing WHERE slob_id = $id");
		//while($fields = mysql_fetch_array($dataset))
		//{
			$this->purchasing = new purchasing($this);
			if(!$this->purchasing->load($id))
			{
				unset($this->purchasing);
				page::addDebug("failed purchase", __FILE__, __LINE__);
			}
			page::addDebug("load purchasing for IJF id=$id", __FILE__, __LINE__);
		//}

		/**
		 * checks for a finance section of the IJF and loads it
		 */
		//$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM finance WHERE ijfId = $id");
		//while($fields = mysql_fetch_array($dataset))
		//{
			$this->finance = new finance($this);
			if(!$this->finance->load($id))
			{
				unset($this->finance);
			}
			page::addDebug("load finance for IJF id=" . $id, __FILE__, __LINE__);
		//}

		/**
		 * checks for a commercial planning section of the IJF and loads it
		 */
		//$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM commercialPlanning WHERE ijfId = $id");
		//while($fields = mysql_fetch_array($dataset))
		//{
			$this->commercialPlanning = new commercialPlanning($this);
			if(!$this->commercialPlanning->load($id))
			{
				unset($this->commercialPlanning);
			}

			page::addDebug("load commercial planning for IJF id=" . $id, __FILE__, __LINE__);
		//}

		return true;
	}

	/**
	 * Returns the id for the IJF
	 *
	 * @return int
	 */
	public function getID()
	{
		return $this->form->get("id")->getValue();
	}

	public function getMaterialNumber()
	{
		return $this->form->get("material_number")->getValue();
	}

	/**
	 * Retuns the dataAdministration instance
	 *
	 * @return dataAdministration
	 */
	public function getDataAdministration()
	{
		if (isset($this->dataAdministration))
		{
			return $this->dataAdministration;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Retuns the purchasing instance
	 *
	 * @return purchasing
	 */
	public function getPurchasing()
	{
		if (isset($this->purchasing))
		{
			return $this->purchasing;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Retuns the production instance
	 *
	 * @return production
	 */
	public function getProduction()
	{
		if (isset($this->production))
		{
			return $this->production;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the finance instance
	 *
	 * @return finance
	 */
	public function getFinance()
	{
		if (isset($this->finance))
		{
			return $this->finance;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Retuns the demand planning instance
	 *
	 * @return productOwner
	 */
	public function getProductOwner()
	{
		if (isset($this->productOwner))
		{
			return $this->productOwner;
		}
		else
		{

			return false;
		}
	}

	/**
	 * Retuns the commercial planning instance
	 *
	 * @return commercialPlanning
	 */
	public function getCommercialPlanning()
	{
		if (isset($this->commercialPlanning))
		{
			return $this->commercialPlanning;
		}
		else
		{
			return false;
		}
	}


	/**
	 * Adds a new section to the IJF.
	 * The section name is passed to the function as a string and then a new instance of that section is created.
	 * The function is used when the user is adding a new part to the IJF.
	 *
	 * @param string $section
	 */
	public function addSection($section)
	{
		switch ($section)
		{
			case 'ijf':
				$this->ijf = new ijf($this);
				break;
			case 'dataAdministration':
				$this->dataAdministration = new dataAdministration($this);
				break;
			case 'production':
				$this->production = new production($this);
				break;
			case 'purchasing':
				$this->purchasing = new purchasing($this);
				break;
			case 'productOwner':
				$this->productOwner = new productOwner($this);
				break;
			case 'finance':
				$this->finance = new finance($this);
				break;
			case 'commercialPlanning':
				$this->commercialPlanning = new commercialPlanning($this);
				break;

			default: die("unknown status sent");
		}
	}



	/**
	 * Validates every section of the IJF
	 *
	 * @return boolean
	 */
	public function validate()
	{
		$valid = true;

		if (!$this->form->validate())
		{
			$valid = false;
		}

		if (isset($this->dataAdministration))
		{
			if(!$this->dataAdministration->validate())
			{
				$valid = false;
			}
		}

		if (isset($this->purchasing))
		{
			if(!$this->purchasing->validate())
			{
				$valid = false;
			}
		}

		if (isset($this->production))
		{
			if(!$this->production->validate())
			{
				$valid = false;
			}
		}

		if (isset($this->productOwner))
		{
			if(!$this->productOwner->validate())
			{
				$valid = false;
			}
		}

		if (isset($this->commercialPlanning))
		{
			if(!$this->commercialPlanning->validate())
			{
				$valid = false;
			}
		}

		if (isset($this->finance))
		{
			if(!$this->finance->validate())
			{
				$valid = false;
			}
		}

		return $valid;
	}



	/**
	 * Save function.
	 * The section (process) of the IJF is passed to this function and the function saves the section that is passed.
	 *
	 * @param string $process
	 */
	public function save($process)
	{
		page::addDebug("Saving IJF process: ".$process,__FILE__,__LINE__);

		switch ($process)
		{
			case 'ijf':

				$this->determineStatus();


				if ($this->loadedFromDatabase)
				{
					$this->form->get("updatedDate")->setValue(common::nowDateForMysql());

					$this->form->get("initialSubmissionDate")->setIgnore(true);

					$this->form->get("owner")->setValue($this->form->get("ijf_owner")->getValue());

					// update
					mysql::getInstance()->selectDatabase("IJF")->Execute("UPDATE ijf " . $this->form->generateUpdateQuery("ijf") . " WHERE id='" . $this->id . "'");
					page::addDebug("Checking if updating IJF table", __FILE__, __LINE__);


					// save new data

					$this->addLog("IJF Report Updated");
				}
				else
				{
					// set user
					$this->form->get("initiatorInfo")->setValue(currentuser::getInstance()->getNTLogon());

					// set report date
					$this->form->get("initialSubmissionDate")->setValue(common::nowDateForMysql());

					// set IJF owner
					$this->form->get("owner")->setValue($this->form->get("ijf_owner")->getValue());


					// begin transaction
					mysql::getInstance()->selectDatabase("IJF")->Execute("BEGIN");

					// find customer name from customer account number
					if ($_SERVER['REQUEST_METHOD'] && $this->form->get('existingCustomer')->getValue() == 'yes')
					{
						$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `salesPerson`, `name`, `id` FROM `customer` WHERE `id` = '" . $this->form->get('customerAccountNumber')->getValue() . "'");
						$fields = mysql_fetch_array($dataset);
						$this->form->get("customerName")->setValue(page::xmlentities($fields['name']));
						$this->form->get("salesRep")->setValue($fields['salesPerson']);
					}


					// insert

					mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO ijf " . $this->form->generateInsertQuery("ijf"));

					// get last inserted
					$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id FROM ijf ORDER BY id DESC LIMIT 1");

					$fields = mysql_fetch_array($dataset);

					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);

					// end transaction
					mysql::getInstance()->selectDatabase("IJF")->Execute("COMMIT");

					$this->addLog(translate::getInstance()->translate("ijf_added"));
					$this->addLog(translate::getInstance()->translate("sent_to_" . $this->form->get("status")->getValue()) . " (" . usercache::getInstance()->get($this->form->get("owner")->getValue())->getName() .")");

					// email here
					$this->getEmailNotification($fields['id'], $this->form->get("status")->getValue());

					$this->form->get("attachment")->setFinalFileLocation("/apps/ijf/attachments/" . $this->id . "/");
					$this->form->get("attachment")->moveTempFileToFinal();

			}


			break;

		case 'dataAdministration':
			$this->dataAdministration->setIjfId($this->id);
			$this->dataAdministration->save();
			break;
		case 'purchasing':
			$this->purchasing->setIjfId($this->id);
			$this->purchasing->save();
			break;
		case 'production':
			$this->production->setIjfId($this->id);
			$this->production->save();
			break;
		case 'productOwner':
			$this->productOwner->setIjfId($this->id);
			$this->productOwner->save();
			break;
		case 'commercialPlanning':
			$this->commercialPlanning->setIjfId($this->id);
			$this->commercialPlanning->save();
			break;
		case 'finance':
			$this->finance->setIjfId($this->id);
			$this->finance->save();
			break;

		}


		page::redirect("/apps/ijf/");		//redirects the page back to the summary

		//page::redirect("resume?ijf=" . $this->form->get("id")->getValue() . "&status=productionSite");
	}

	public function determineStatus()
	{
				$location = "commercialPlanning";
				$this->status = $location;
				$this->form->get('status')->setValue($location);

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

	/**
	 * function used for adding information to the log.
	 * A string containing what happened is passed, and inserted into the log database.
	 * It automatically links the action in the log to the current loaded IJF.
	 *
	 * @param string $action
	 */
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
		$this->getID(),
		currentuser::getInstance()->getNTLogon(),
		$action,
		common::nowDateTimeForMysql()
		));
	}

	public function getOwner()
	{
		return $this->form->get("owner")->getValue();
	}

	public function getCreator()
	{
		return $this->form->get("initiatorInfo")->getValue();
	}

	public function getInitiatorInfo()
	{
		return $this->form->get("initiatorInfo")->getValue();
	}


	/**
	 * defines the form
	 * @todo fix the material type selector. Both raw and semi finished goods should have the hierarchy selector displayed. and the finished and traded goods should have a radio button so you can select either method of determining the material key.
	 * @todo fix the dataAdministration inspection control. looks like the javascript is messing up, but will be due to the material type control.
	 */
	public function defineForm()
	{
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("initiation");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);

		$initiation = new group("initiation");
		$customer = new group("customer");
		$existingCustomerGroupNo = new group("existingCustomerGroupNo");
		$existingCustomerGroupYes = new group("existingCustomerGroupYes");
		$contact = new group("contact");
		$productDetails = new group("productDetails");
		$wordQuoteReqGroup = new group("wordQuoteReqGroup");
		$productSiteDetails = new group("productSiteDetails");
		$productionSiteDunstable = new group("productionSiteDunstable");
		$productionSiteRorschach = new group("productionSiteRorschach");
		$productionSiteMegalon = new group("productionSiteMegalon");
		$productionSiteGhislarengo = new group("productionSiteGhislarengo");
		$ijf = new group("ijf");
		$productionSiteGroup = new group("productionSiteGroup");
		$potentialBusiness = new group("potentialBusiness");
		$sendToUser = new group("sendToUser");


		$id = new textbox("id");
		$id->setTable("ijf");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		$status->setValue("initiation");
		$status->setTable("ijf");
		$status->setVisible(false);
		$initiation->add($status);

		$updatedDate = new textbox("updatedDate");
		$updatedDate->setTable("ijf");
		$updatedDate->setVisible(false);
		$updatedDate->setDataType("text");
		$updatedDate->setRequired(false);
		$updatedDate->setLength(50);
		$updatedDate->setLabel("date_entered");
		$updatedDate->setValue($today);
		$initiation->add($updatedDate);

		$ijfDueDate  = new textbox("ijfDueDate");
		$ijfDueDate->setTable("ijf");
		$ijfDueDate->setVisible(false);
		$ijfDueDate->setDataType("text");
		$ijfDueDate->setRequired(true);
		$ijfDueDate->setLength(50);
		$ijfDueDate->setValue($next_week_date);
		$ijfDueDate->setLabel("completion_date");
		$initiation->add($ijfDueDate);

		$initiatorInfo = new textbox("initiatorInfo");
		$initiatorInfo->setTable("ijf");
		$initiatorInfo->setVisible(false);
		$initiatorInfo->setDataType("text");
		$initiatorInfo->setRequired(false);
		$initiatorInfo->setLength(50);
		$initiatorInfo->setIsAnNTLogon(true);
		$initiation->add($initiatorInfo);

		$initialSubmissionDate = new textbox("initialSubmissionDate");
		$initialSubmissionDate->setTable("ijf");
		$initialSubmissionDate->setVisible(false);
		$initialSubmissionDate->setDataType("text");
		$initialSubmissionDate->setRequired(false);
		$initialSubmissionDate->setLength(50);
		$initialSubmissionDate->setLabel("date_entered");
		$initialSubmissionDate->setValue($today);
		$initiation->add($initialSubmissionDate);


		$existingCustomer = new radio("existingCustomer");
		$existingCustomer->setGroup("customer");
		$existingCustomer->setDataType("string");
		$existingCustomer->setLength(5);
		$existingCustomer->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$existingCustomer->setRowTitle("existing_customer");
		$existingCustomer->setLabel("Customer Details");
		$existingCustomer->setRequired(true);
		$existingCustomer->setValue("yes");
		$existingCustomer->setTable("ijf");
		$existingCustomer->setHelpId(2000);

		// Dependency
		$existingCustomerShowGroup = new dependency();
		$existingCustomerShowGroup->addRule(new rule('customer', 'existingCustomer', 'no'));
		$existingCustomerShowGroup->setGroup('existingCustomerGroupNo');
		$existingCustomerShowGroup->setShow(true);

		$existingCustomerShowNextGroup = new dependency();
		$existingCustomerShowNextGroup->addRule(new rule('customer', 'existingCustomer', 'yes'));
		$existingCustomerShowNextGroup->setGroup('existingCustomerGroupYes');
		$existingCustomerShowNextGroup->setShow(true);

		$existingCustomer->addControllingDependency($existingCustomerShowGroup);
		$existingCustomer->addControllingDependency($existingCustomerShowNextGroup);
		$customer->add($existingCustomer);


		$customerName = new textbox("customerName");
		$customerName->setGroup("existingCustomerGroupNo");
		$customerName->setDataType("string");
		$customerName->setLength(30);
		$customerName->setRowTitle("customer_name");
		$customerName->setRequired(false);
		$customerName->setLabel("Customer Name");
		$customerName->setVisible(true);
		$customerName->setTable("ijf");
		$customerName->setHelpId(2002);
		$existingCustomerGroupNo->add($customerName);



		$customerAccountNumber = new autocomplete("customerAccountNumber");
		$customerAccountNumber->setGroup("customer");
		$customerAccountNumber->setDataType("string");
		$customerAccountNumber->setLength(30);
		$customerAccountNumber->setRowTitle("customer_account_number");
		$customerAccountNumber->setUrl("/apps/ijf/ajax/sap?");
		$customerAccountNumber->setRequired(false);
		$customerAccountNumber->setLabel("Customer Account Number");
		$customerAccountNumber->setTable("ijf");
		$customerAccountNumber->setHelpId(2001);
		$existingCustomerGroupYes->add($customerAccountNumber);


		//$customerName = new textbox("customerName");
		//$customerName->setGroup("customer");
		//$customerName->setDataType("string");
		//$customerName->setLength(30);
		//$customerName->setRowTitle("customer_name");
		//$customerName->setRequired(false);
		//$customerName->setVisible(false);
		//$customerName->setTable("ijf");
		//$customerName->setHelpId(2002);
		//$customer->add($customerName);


		$contactName = new textbox("contactName");
		$contactName->setGroup("customer");
		$contactName->setDataType("string");
		$contactName->setLength(30);
		$contactName->setRowTitle("contact_name");
		$contactName->setLabel("Contact Details");
		$contactName->setRequired(true);
		$contactName->setTable("ijf");
		$contactName->setHelpId(2003);
		$contact->add($contactName);

		$contactPosition = new textbox("contactPosition");
		$contactPosition->setGroup("customer");
		$contactPosition->setDataType("string");
		$contactPosition->setLength(30);
		$contactPosition->setRowTitle("contact_position");
		$contactPosition->setRequired(false);
		$contactPosition->setTable("ijf");
		$contactPosition->setHelpId(2004);
		$contact->add($contactPosition);

		$contactTel = new textbox("contactTel");
		$contactTel->setGroup("customer");
		$contactTel->setDataType("string");
		$contactTel->setLength(30);
		$contactTel->setRowTitle("contact_tel");
		$contactTel->setRequired(false);
		$contactTel->setTable("ijf");
		$contactTel->setHelpId(2005);
		$contact->add($contactTel);

		$salesRep = new invisibletext("salesRep");
		$salesRep->setGroup("productDetails");
		$salesRep->setDataType("string");
		$salesRep->setLength(50);
		$salesRep->setRowTitle("sales_rep");
		$salesRep->setRequired(false);
		$salesRep->setTable("ijf");
		$salesRep->setHelpId(2012);
		$contact->add($salesRep);


		$sellingCountry = new dropdown("sellingCountry");
		$sellingCountry->setGroup("productDetails");
		$sellingCountry->setDataType("string");
		$sellingCountry->setLength(30);
		$sellingCountry->setRowTitle("selling_country");
		$sellingCountry->setRequired(false);
		$sellingCountry->setXMLSource("./apps/ijf/xml/countries.xml");
		$sellingCountry->setLabel("Product Details");
		$sellingCountry->setValue("United Kingdom");
		$sellingCountry->setTable("ijf");
		$sellingCountry->setHelpId(2006);
		$productDetails->add($sellingCountry);

		$materialGroup = new autocomplete("materialGroup");
		$materialGroup->setGroup("productDetails");
		$materialGroup->setDataType("string");
		$materialGroup->setLength(30);
		$materialGroup->setUrl("/apps/ijf/ajax/materialgroup?");
		$materialGroup->setRowTitle("material_group");
		$materialGroup->setRequired(true);
		$materialGroup->setTable("ijf");
		$materialGroup->setHelpId(2007);
		$productDetails->add($materialGroup);


		$businessUnit = new autocomplete("businessUnit");
		$businessUnit->setGroup("productDetails");
		$businessUnit->setDataType("string");
		$businessUnit->setLength(50);
		$businessUnit->setRowTitle("bu");
		$businessUnit->setUrl("/apps/ijf/ajax/bu?");
		$businessUnit->setRequired(false);
		$businessUnit->setTable("ijf");
		$businessUnit->setHelpId(2013);
		$productDetails->add($businessUnit);

		$reasonIJF = new dropdown("reasonIJF");
		$reasonIJF->setTable("ijf");
		$reasonIJF->setVisible(true);
		$reasonIJF->setGroup("productDetails");
		$reasonIJF->setArraySource(array(
			array('value' => 'quote', 'display' => 'Quote'),
			array('value' => 'quote_and_sample', 'display' => 'Quote and Sample'),
			array('value' => 'sample', 'display' => 'Sample'),
			array('value' => 'order', 'display' => 'Order')
		));
		$reasonIJF->setDataType("string");
		$reasonIJF->setHelpId(2025);
		$reasonIJF->setRequired(false);
		$reasonIJF->setRowTitle("reason_for_ijf");
		$productDetails->add($reasonIJF);

		$description = new textarea("description");
		$description->setGroup("productDetails");
		$description->setDataType("string");
		$description->setLength(50);
		$description->setRowTitle("description");
		$description->setRequired(false);
		$description->setTable("ijf");
		$description->setHelpId(2020);
		$productDetails->add($description);

		$productOwner = new textarea("productOwner");
		$productOwner->setGroup("productDetails");
		$productOwner->setDataType("string");
		$productOwner->setLength(50);
		$productOwner->setRowTitle("product_owner");
		$productOwner->setRequired(false);
		$productOwner->setTable("ijf");
		$productOwner->setHelpId(2020);
		$productDetails->add($productOwner);

		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ijf/tmp");
		$attachment->setFinalFileLocation("/apps/ijf/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("ijf");
		$productDetails->add($attachment);

		$barManView = new radio("barManView");
		$barManView->setGroup("barManViewGroup");
		$barManView->setDataType("string");
		$barManView->setLength(50);
		$barManView->setRowTitle("bar_man_view_request");
		$barManView->setRequired(false);
		$barManView->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$barManView->setTable("ijf");
		$barManView->setHelpId(2019);
		$productDetails->add($barManView);


		$wordQuoteReq = new radio("wordQuoteReq");
		$wordQuoteReq->setGroup("productDetails");
		$wordQuoteReq->setDataType("string");
		$wordQuoteReq->setLength(50);
		$wordQuoteReq->setRowTitle("word_quote_req");
		$wordQuoteReq->setRequired(true);
		$wordQuoteReq->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$wordQuoteReq->setTable("ijf");
		$wordQuoteReq->setHelpId(2014);

		// Dependency
		$wqrShowGroup = new dependency();
		$wqrShowGroup->addRule(new rule('productDetails', 'wordQuoteReq', 'yes'));
		$wqrShowGroup->setGroup('wordQuoteReqGroup');
		$wqrShowGroup->setShow(true);

		$wordQuoteReq->addControllingDependency($wqrShowGroup);
		$productDetails->add($wordQuoteReq);

		$wqrAddress = new textbox("wqrAddress");
		$wqrAddress->setDataType("string");
		$wqrAddress->setGroup("wordQuoteReqGroup");
		$wqrAddress->setLabel("Word Quote Request Further Details");
		$wqrAddress->setLength(50);
		$wqrAddress->setRowTitle("wrq_address");
		$wqrAddress->setRequired(false);
		$wqrAddress->setTable("ijf");
		$wqrAddress->setHelpId(2015);
		$wordQuoteReqGroup->add($wqrAddress);

		$wqrCity = new textbox("wqrCity");
		$wqrCity->setDataType("string");
		$wqrCity->setGroup("wordQuoteReqGroup");
		$wqrCity->setLength(50);
		$wqrCity->setRowTitle("wrq_city");
		$wqrCity->setRequired(false);
		$wqrCity->setTable("ijf");
		$wqrCity->setHelpId(2016);
		$wordQuoteReqGroup->add($wqrCity);

		$wqrCountry = new textbox("wqrCountry");
		$wqrCountry->setDataType("string");
		$wqrCountry->setGroup("wordQuoteReqGroup");
		$wqrCountry->setLength(50);
		$wqrCountry->setRowTitle("wrq_country");
		$wqrCountry->setRequired(false);
		$wqrCountry->setTable("ijf");
		$wqrCountry->setHelpId(2017);
		$wordQuoteReqGroup->add($wqrCountry);

		$wqrPostCode = new textbox("wqrPostCode");
		$wqrPostCode->setDataType("string");
		$wqrPostCode->setGroup("wordQuoteReqGroup");
		$wqrPostCode->setLength(50);
		$wqrPostCode->setRowTitle("wrq_postcode");
		$wqrPostCode->setRequired(false);
		$wqrPostCode->setTable("ijf");
		$wqrPostCode->setHelpId(2018);
		$wordQuoteReqGroup->add($wqrPostCode);

		$productionSite = new radio("productionSite");
		$productionSite->setGroup("productDetails");
		$productionSite->setDataType("string");
		$productionSite->setLength(30);
		$productionSite->setRowTitle("production_site");
		$productionSite->setXMLSource("./apps/ijf/xml/sites.xml");
		$productionSite->setLabel("Specific Site Details");
		//$productionSite->setValue("");
		$productionSite->setRequired(true);
		$productionSite->setTable("ijf");
		$productionSite->setHelpId(2011);


		// PRODUCTION SITE FIELDS ***********************

		// Dunstable Dependency
		$dunstableShowGroupDependency = new dependency();
		$dunstableShowGroupDependency->addRule(new rule('productSiteDetails', 'productionSite', 'dunstable'));
		$dunstableShowGroupDependency->setGroup('productionSiteDunstable');
		$dunstableShowGroupDependency->setShow(true);

		// Rorschach Dependency
		$rorschachShowGroupDependency = new dependency();
		$rorschachShowGroupDependency->addRule(new rule('productSiteDetails', 'productionSite', 'rorschach'));
		$rorschachShowGroupDependency->setGroup('productionSiteRorschach');
		$rorschachShowGroupDependency->setShow(true);

		// Megalon Dependency
		$megalonShowGroupDependency = new dependency();
		$megalonShowGroupDependency->addRule(new rule('productSiteDetails', 'productionSite', 'megalon'));
		$megalonShowGroupDependency->setGroup('productionSiteMegalon');
		$megalonShowGroupDependency->setShow(true);

		// Ghislarengo Dependency
		$ghislarengoShowGroupDependency = new dependency();
		$ghislarengoShowGroupDependency->addRule(new rule('productSiteDetails', 'productionSite', 'ghislarengo'));
		$ghislarengoShowGroupDependency->setGroup('productionSiteGhislarengo');
		$ghislarengoShowGroupDependency->setShow(true);

		$productionSite->addControllingDependency($dunstableShowGroupDependency);
		$productionSite->addControllingDependency($rorschachShowGroupDependency);
		$productionSite->addControllingDependency($megalonShowGroupDependency);
		$productionSite->addControllingDependency($ghislarengoShowGroupDependency);


		$alternativeLiner = new dropdown("alternativeLiner");
		$alternativeLiner->setTable("ijf");
		$alternativeLiner->setVisible(true);
		$alternativeLiner->setGroup("productionSiteDunstable");
		$alternativeLiner->setDataType("string");
		$alternativeLiner->setLabel("Site Specific For Dunstable");
		$alternativeLiner->setHelpId(2100);
		$alternativeLiner->setXMLSource("./apps/ijf/xml/alternativeLiner.xml");
		$alternativeLiner->setRequired(false);
		$alternativeLiner->setValue("white");
		$alternativeLiner->setRowTitle("alternative_liner");
		$productionSiteDunstable->add($alternativeLiner);

		$coreDimensions = new textbox("coreDimensions");
		$coreDimensions->setTable("ijf");
		$coreDimensions->setVisible(true);
		$coreDimensions->setGroup("productionSiteRorschach");
		$coreDimensions->setLabel("Site Specific For Rorschach");
		$coreDimensions->setDataType("string");
		$coreDimensions->setHelpId(2101);
		$coreDimensions->setRequired(false);
		$coreDimensions->setRowTitle("core_dimensions");
		$productionSiteRorschach->add($coreDimensions);

		$kindOfPackaging = new textbox("kindOfPackaging");
		$kindOfPackaging->setTable("ijf");
		$kindOfPackaging->setVisible(true);
		$kindOfPackaging->setGroup("productionSiteRorschach");
		$kindOfPackaging->setDataType("string");
		$kindOfPackaging->setHelpId(2101);
		$kindOfPackaging->setRequired(false);
		$kindOfPackaging->setRowTitle("cartons");
		$productionSiteRorschach->add($kindOfPackaging);

		$cartons = new textbox("cartons");
		$cartons->setTable("ijf");
		$cartons->setVisible(true);
		$cartons->setGroup("productionSiteRorschach");
		$cartons->setDataType("string");
		$cartons->setHelpId(2101);
		$cartons->setRequired(false);
		$cartons->setRowTitle("cartons");
		$productionSiteRorschach->add($cartons);

		$splices = new textbox("splices");
		$splices->setTable("ijf");
		$splices->setVisible(true);
		$splices->setGroup("productionSiteRorschach");
		$splices->setDataType("string");
		$splices->setRequired(false);
		$splices->setRowTitle("splices");
		$productionSiteRorschach->add($splices);

		$slittingPreferences = new textbox("slittingPreferences");
		$slittingPreferences->setTable("ijf");
		$slittingPreferences->setVisible(true);
		$slittingPreferences->setGroup("productionSiteRorschach");
		$slittingPreferences->setDataType("string");
		$slittingPreferences->setRequired(false);
		$slittingPreferences->setRowTitle("slittingPreferences");
		$productionSiteRorschach->add($slittingPreferences);

		$labellingRequirements = new textbox("labellingRequirements");
		$labellingRequirements->setTable("ijf");
		$labellingRequirements->setVisible(true);
		$labellingRequirements->setGroup("productionSiteRorschach");
		$labellingRequirements->setDataType("string");
		$labellingRequirements->setRequired(false);
		$labellingRequirements->setRowTitle("labellingRequirements");
		$productionSiteRorschach->add($labellingRequirements);


		$alternativeSellingUOM = new textbox("alternativeSellingUOM");
		$alternativeSellingUOM->setTable("ijf");
		$alternativeSellingUOM->setVisible(true);
		$alternativeSellingUOM->setGroup("productionSiteMegalon");
		$alternativeSellingUOM->setLabel("Site Specific For Megalon");
		$alternativeSellingUOM->setDataType("string");
		$alternativeSellingUOM->setRequired(false);
		$alternativeSellingUOM->setRowTitle("alternativeSellingUOM");
		$productionSiteMegalon->add($alternativeSellingUOM);

		$innerDiameterReq = new textbox("innerDiameterReq");
		$innerDiameterReq->setTable("ijf");
		$innerDiameterReq->setVisible(true);
		$innerDiameterReq->setGroup("productionSiteMegalon");
		$innerDiameterReq->setDataType("string");
		$innerDiameterReq->setRequired(false);
		$innerDiameterReq->setRowTitle("innerDiameterReq");
		$productionSiteMegalon->add($innerDiameterReq);

		$outerDiameterReq = new textbox("outerDiameterReq");
		$outerDiameterReq->setTable("ijf");
		$outerDiameterReq->setVisible(true);
		$outerDiameterReq->setGroup("productionSiteMegalon");
		$outerDiameterReq->setDataType("string");
		$outerDiameterReq->setRequired(false);
		$outerDiameterReq->setRowTitle("outerDiameterReq");
		$productionSiteMegalon->add($outerDiameterReq);



		$labels = new textbox("labels");
		$labels->setTable("ijf");
		$labels->setVisible(true);
		$labels->setGroup("productionSiteGhislarengo");
		$labels->setLabel("Site Specific For Ghislarengo");
		$labels->setDataType("string");
		$labels->setRequired(false);
		$labels->setRowTitle("labels");
		$productionSiteGhislarengo->add($labels);



		$productSiteDetails->add($productionSite);



		$width = new measurement("width");
		$width->setTable("ijf");
		$width->setVisible(true);
		$width->setGroup("productionSite");
		$width->setDataType("string");
		$width->setRequired(false);
		$width->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$width->setRowTitle("width");
		$width->setLabel("Site Specific Information");
		$productionSiteGroup->add($width);


		$ijfLength = new measurement("ijfLength");
		$ijfLength->setTable("ijf");
		$ijfLength->setVisible(true);
		$ijfLength->setGroup("productionSite");
		$ijfLength->setDataType("string");
		$ijfLength->setRequired(false);
		$ijfLength->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$ijfLength->setRowTitle("length");
		$productionSiteGroup->add($ijfLength);


		$thickness = new measurement("thickness");
		$thickness->setTable("ijf");
		$thickness->setVisible(true);
		$thickness->setGroup("productionSite");
		$thickness->setDataType("string");
		$thickness->setRequired(false);
		$thickness->setArraySource(array(
			array('value' => 'mm', 'display' => 'mm'),
			array('value' => 'metres', 'display' => 'Metres'))
		);
		$thickness->setRowTitle("thickness");
		$productionSiteGroup->add($thickness);


		$colour = new dropdown("colour");
		$colour->setTable("ijf");
		$colour->setVisible(true);
		$colour->setGroup("productionSite");
		$colour->setXMLSource("./apps/ijf/xml/colours.xml");
		$colour->setDataType("string");
		$colour->setRequired(false);
		$colour->setRowTitle("colour");
		$productionSiteGroup->add($colour);


		$liner = new dropdown("liner");
		$liner->setTable("ijf");
		$liner->setVisible(true);
		$liner->setGroup("productionSite");
		$liner->setDataType("string");
		$liner->setRequired(false);
		$liner->setArraySource(array(
		array('value' => 'paper', 'display' => 'Paper'),
		array('value' => 'film', 'display' => 'Film'))
		);
		$liner->setRowTitle("liner");
		$productionSiteGroup->add($liner);

		$certificateOfConformity = new dropdown("certificateOfConformity");
		$certificateOfConformity->setTable("ijf");
		$certificateOfConformity->setVisible(true);
		$certificateOfConformity->setGroup("productionSite");
		$certificateOfConformity->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No'))
		);
		$certificateOfConformity->setDataType("string");
		$certificateOfConformity->setRequired(false);
		$certificateOfConformity->setRowTitle("certificateOfConformity");
		$productionSiteGroup->add($certificateOfConformity);


		/*$specificLiningOverhang = new textbox("specificLiningOverhang");
		$specificLiningOverhang->setTable("ijf");
		$specificLiningOverhang->setVisible(true);
		$specificLiningOverhang->setGroup("productionSite");
		$specificLiningOverhang->setDataType("string");
		$specificLiningOverhang->setRequired(false);
		$specificLiningOverhang->setRowTitle("specificLiningOverhang");
		$productionSiteGroup->add($specificLiningOverhang);

		$overlapRequired = new textbox("overlapRequired");
		$overlapRequired->setTable("ijf");
		$overlapRequired->setVisible(true);
		$overlapRequired->setGroup("productionSite");
		$overlapRequired->setDataType("string");
		$overlapRequired->setRequired(false);
		$overlapRequired->setRowTitle("overlapRequired");
		$productionSiteGroup->add($overlapRequired);

		$laminates = new dropdown("laminates");
		$laminates->setTable("ijf");
		$laminates->setVisible(true);
		$laminates->setGroup("productionSite");
		$laminates->setDataType("string");
		$laminates->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No'))
		);
		$laminates->setRequired(false);
		$laminates->setRowTitle("laminates");
		$productionSiteGroup->add($laminates);*/

		$tolerances = new textarea("tolerances");
		$tolerances->setTable("ijf");
		$tolerances->setVisible(true);
		$tolerances->setGroup("productionSite");
		$tolerances->setDataType("string");
		$tolerances->setRequired(false);
		$tolerances->setRowTitle("tolerances");
		$productionSiteGroup->add($tolerances);

		$formatComments = new textarea("formatComments");
		$formatComments->setTable("ijf");
		$formatComments->setVisible(true);
		$formatComments->setGroup("productionSite");
		$formatComments->setDataType("string");
		$formatComments->setRequired(false);
		$formatComments->setRowTitle("formatComments");
		$productionSiteGroup->add($formatComments);

		$comments = new textarea("comments");
		$comments->setGroup("ijf");
		$comments->setRowTitle("comments");
		$comments->setTable("ijf");
		$productionSiteGroup->add($comments);

		$core = new dropdown("core");
		$core->setTable("ijf");
		$core->setVisible(true);
		$core->setGroup("productionSite");
		$core->setDataType("string");
		$core->setValue("Standard");
		$core->setXMLSource("./apps/ijf/xml/core.xml");
		$core->setRequired(false);
		$core->setRowTitle("core");
		$productionSiteGroup->add($core);


		$sellingUOM = new dropdown("sellingUOM");
		$sellingUOM->setTable("ijf");
		$sellingUOM->setVisible(true);
		$sellingUOM->setGroup("productionSite");
		$sellingUOM->setValue("Roll");
		$sellingUOM->setDataType("string");
		$sellingUOM->setXMLSource("./apps/ijf/xml/sellingUOM.xml");
		$sellingUOM->setRequired(false);
		$sellingUOM->setRowTitle("sellingUOM");
		$productionSiteGroup->add($sellingUOM);



		$annualQuantityUOM = new textbox("annualQuantityUOM");
		$annualQuantityUOM->setTable("ijf");
		$annualQuantityUOM->setVisible(true);
		$annualQuantityUOM->setGroup("potentialBusiness");
		$annualQuantityUOM->setDataType("string");
		$annualQuantityUOM->setLabel("Potential Business");
		$annualQuantityUOM->setRequired(false);
		$annualQuantityUOM->setRowTitle("annual_quantity_in_selling_uom");
		$potentialBusiness->add($annualQuantityUOM);

		$firstOrderQuantityUOM = new textbox("firstOrderQuantityUOM");
		$firstOrderQuantityUOM->setTable("ijf");
		$firstOrderQuantityUOM->setVisible(true);
		$firstOrderQuantityUOM->setGroup("potentialBusiness");
		$firstOrderQuantityUOM->setDataType("string");
		$firstOrderQuantityUOM->setRequired(false);
		$firstOrderQuantityUOM->setRowTitle("first_order_quantity_in_selling_uom");
		$potentialBusiness->add($firstOrderQuantityUOM);



		$targetPrice = new textbox("targetPrice");
		$targetPrice->setTable("ijf");
		$targetPrice->setVisible(true);
		$targetPrice->setGroup("potentialBusiness");
		$targetPrice->setDataType("string");
		$targetPrice->setRequired(false);
		$targetPrice->setRowTitle("target_price");
		$potentialBusiness->add($targetPrice);

		$currency = new dropdown("currency");
		$currency->setTable("ijf");
		$currency->setVisible(true);
		$currency->setGroup("potentialBusiness");
		$currency->setDataType("string");
		$currency->setXMLSource("./apps/ijf/xml/currency.xml");
		$currency->setRequired(false);
		$currency->setRowTitle("currency");
		$potentialBusiness->add($currency);

		$potentialComments = new textarea("potentialComments");
		$potentialComments->setTable("ijf");
		$potentialComments->setVisible(true);
		$potentialComments->setGroup("potentialBusiness");
		$potentialComments->setDataType("string");
		$potentialComments->setRequired(false);
		$potentialComments->setRowTitle("comments");
		$potentialBusiness->add($potentialComments);

		// END OF PRODUCTION SITE FIELDS **************

		$tobeSentTo = new readonly("tobeSentTo");
		$tobeSentTo->setGroup("sendToUser");
		$tobeSentTo->setLength(200);
		$tobeSentTo->setRowTitle("ijf_to_be_sent_to");
		$tobeSentTo->setLabel("User Delivery Options");
		$tobeSentTo->setValue("Commercial Planning");
		$tobeSentTo->setRequired(false);
		$tobeSentTo->setHelpId(2020);
		$sendToUser->add($tobeSentTo);


		$ijf_owner = new dropdown("ijf_owner");
		$ijf_owner->setGroup("sendTo");
		$ijf_owner->setLength(250);
		$ijf_owner->setTable("ijf");
		$ijf_owner->setRowTitle("send_ijf_to");
		$ijf_owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf_commercialPlanner' ORDER BY employee.NTLogon");
		$ijf_owner->setRequired(true);
		$ijf_owner->setVisible(true);
		$ijf_owner->setHelpId(1008);
		$sendToUser->add($ijf_owner);


		$owner = new dropdown("owner");
		$owner->setGroup("sendToUser");
		$owner->setLength(250);
		$owner->setTable("ijf");
		$owner->setRowTitle("send_ijf_to");
		$owner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permission LIKE 'ijf_%' ORDER BY employee.NTLogon");
		$owner->setRequired(true);
		$owner->setVisible(false);
		$owner->setHelpId(2009);
		$sendToUser->add($owner);


		$delegate_owner = new autocomplete("delegate_owner");
		$delegate_owner->setGroup("sendToUser");
		$delegate_owner->setLength(250);
		$delegate_owner->setTable("ijf");
		$delegate_owner->setUrl("/apps/ijf/ajax/ccijf?");
		$delegate_owner->setRowTitle("cc_to_ijf");
		$delegate_owner->setRequired(false);
		$delegate_owner->setHelpId(2010);
		$sendToUser->add($delegate_owner);

		$email_text = new textarea("email_text");
		$email_text->setGroup("sendToUser");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setHelpId(1500);
		$email_text->setTable("ijf");
		$sendToUser->add($email_text);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);


		$this->form->add($initiation);
		$this->form->add($customer);
		$this->form->add($existingCustomerGroupNo);
		$this->form->add($existingCustomerGroupYes);
		$this->form->add($contact);
		$this->form->add($productDetails);
		$this->form->add($wordQuoteReqGroup);
		$this->form->add($productSiteDetails);
		$this->form->add($productionSiteDunstable);
		$this->form->add($productionSiteRorschach);
		$this->form->add($productionSiteMegalon);
		$this->form->add($productionSiteGhislarengo);
		$this->form->add($ijf);
		$this->form->add($productionSiteGroup);
		$this->form->add($potentialBusiness);
		$this->form->add($sendToUser);


	}

	public function getCurrency()
	{
		return $this->form->get("value")->getMeasurement();
	}


	public function getEmailNotification($id, $status)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<newAction><status>" . $status . "</status><action>" . $id . "</action><completionDate>" . $this->form->get("ijfDueDate")->getValue() . "</completionDate><email_text>" . $this->form->get("email_text")->getValue() . "</email_text><sent_from>" . usercache::getInstance()->get($this->form->get("initiatorInfo")->getValue())->getName() . "</sent_from><emailSectionName>" . translate::getInstance()->translate($status) . "</emailSectionName></newAction>");

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXML($dom);
		$cc = $this->form->get("delegate_owner")->getValue();

		email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("new_ijf_action") . " - ID: " . $id), "$email", $cc);

		return true;
	}

}

?>