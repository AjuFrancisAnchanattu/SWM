<?php
require 'complaintsProcess.php';
require 'complaintExternal.php';
require 'evaluation.php';
require 'conclusion.php';

//require_once('/usr/share/pear/Mail.php');
//require_once('/usr/share/pear/Mail/mime.php');
//require_once('/usr/share/pear/Mail/smtp.php');

/**
 * This is the Complaints Application.
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class complaint
{
	private $id;
	private $status;
	public $form;
	public $attachments;
	private $loadedFromDatabase = false;
	private $complaintType;

	private $customerName = "";

	private $externalPassword = "";
	private $externalPassword2 = "";


	function __construct($loadFromSession=true)
	{
		$this->loadFromSession = $loadFromSession;

		if(isset($_SESSION['apps'][$GLOBALS['app']]['complaint']['loadedFromDatabase']) && isset($_REQUEST['complaint']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the Complaint is loaded from the database
		}

		// Take the ID from the REQUEST before going to the session...
		if(isset($_REQUEST['id']))
		{
			$this->id = $_REQUEST['id'];
		}
		else
		{
			if(isset($_REQUEST['complaint']))
			{
				$this->id = $_REQUEST['complaint'];
			}
			else
			{

				if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
				{
					$this->id = $_SESSION['apps'][$GLOBALS['app']]['id']; //checks if there is a Complaint id in the session
				}
			}
		}

		//if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = "";
		}

		if (!isset($_SESSION['apps'][$GLOBALS['app']]['complete']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = false;
		}

		// Determine Type Of Complaint
		if(isset($_GET['typeOfComplaint']))
		{
			if($_GET['typeOfComplaint'] == "supplier_complaint")
			{
				$this->defineSupplierForm();
				$this->complaintType = "supplier_complaint";
			}
			elseif($_GET['typeOfComplaint'] == "quality_complaint")
			{
				$this->defineQualityForm();
				$this->complaintType = "quality_complaint";
			}
			else
			{
				$this->defineForm();
			}
		}
		else
		{
			if(isset($_REQUEST['id']))
			{
				$complaintTypeID = $_REQUEST['id'];
			}
			else
			{
				$complaintTypeID = $this->id;
			}

			// bodge for saved form
			if(isset($_REQUEST["sfID"]))
			{
				$savedData = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfTypeOfComplaint FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."'");
				$dataRow = mysql_fetch_assoc($savedData);

				$complaintTypeID = $dataRow['sfTypeOfComplaint'];

				if($complaintTypeID == "supplier_complaint")
				{
					$this->defineSupplierForm();
					$this->complaintType = "supplier_complaint";
				}
				elseif($complaintTypeID == "quality_complaint")
				{
					$this->defineQualityForm();
					$this->complaintType = "quality_complaint";
				}
				else
				{
					$this->defineForm();
				}
			}
			else
			{
				if($this->getComplaintType($complaintTypeID) == "supplier_complaint")
				{
					$this->defineSupplierForm();
					$this->complaintType = "supplier_complaint";
				}
				elseif($this->getComplaintType($complaintTypeID) == "quality_complaint")
				{
					$this->defineQualityForm();
					$this->complaintType = "quality_complaint";
				}
				else
				{
					$this->defineForm();
				}
			}

		}

		/*WC EDIT */
		if($this->loadFromSession)//catch the no load of the session data
		$this->form->loadSessionData();

		$this->loadSessionSections();

		/* WC END */
		$this->form->processDependencies();
	}

	private function loadSessionSections()
	{
		if (isset($_SESSION['apps'][$GLOBALS['app']]['evaluation']))
		{
			$this->evaluation = new evaluation($this);
		}
		if (isset($_SESSION['apps'][$GLOBALS['app']]['conclusion']))
		{
			$this->conclusion = new conclusion($this);
		}
	}

	public function loadSessionSectionsAll()
	{
		$this->evaluation = new evaluation($this);
		//die($this);
		$this->conclusion = new conclusion($this);
	}

	public function sapItemNumbers($complaintId)
	{
		$sapItemNumbers = "";

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM sapItemNumber WHERE `complaintId` = '" . $complaintId . "'");
		while ($fields = mysql_fetch_array($dataset))
		{
			$sapItemNumbers .= "" . $fields["sapItemNumber"] . ",";
		}
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET sapItemNumbers = '" . $sapItemNumbers . "' WHERE id = '" . $complaintId . "'");
	}

	public function sapMaterialGroup($complaintId)
	{
		$sapMaterialGroups = "";

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE `complaintId` = '" . $complaintId . "'");
		while ($fields = mysql_fetch_array($dataset))
		{
			$sapMaterialGroups .= $fields["materialGroup"] . ",";
		}
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET sapMaterialGroups = '" . $sapMaterialGroups . "' WHERE id = '" . $complaintId . "'");
	}

	// Important!! for NA and Customer Evaluation
	public function getLocale($site) // Global Function needed
	{
		switch ($site)
		{
			case 'Ashton':
				return "EUROPE";
				break;
			case 'Barcelona':
				return "EUROPE";
				break;
			case 'Bellegarde':
				return "EUROPE";
				break;
			case 'Dunstable':
				return "EUROPE";
				break;
			case 'Ghislarengo':
				return "EUROPE";
				break;
			case 'Mannheim':
				return "EUROPE";
				break;
			case 'Rorschach':
				return "EUROPE";
				break;
			case 'Valence':
				return "EUROPE";
				break;
			case 'Carlstadt':
				return "USA";
				break;
			case 'Inglewood':
				return "USA";
				break;
			case 'Renfrew':
				return "USA";
				break;
			case 'Syracuse':
				return "USA";
				break;
			case 'Windsor':
				return "USA";
				break;
			default:
				return "EUROPE";
				break;
		}
	}

	// Important!! for NA and Customer Evaluation
	public function determineNAOrEuropeEvaluationProcessRoute()
	{
		if($this->getLocale($this->form->get("salesOffice")->getValue()) == "USA")
		{
			if($this->getComplaintType($this->id) == "customer_complaint")
			{
				if($this->getLocale($this->form->get("siteAtOrigin")->getValue()) == "USA")
				{
					$evalProcess = "USA";
				}
				else
				{
					$evalProcess = "EUROPE";
				}
			}
			else
			{
				$evalProcess = "EUROPE";
			}
		}
		elseif($this->getLocale($this->form->get("salesOffice")->getValue()) == "EUROPE")
		{
			if($this->getComplaintType($this->id) == "customer_complaint")
			{
				if($this->getLocale($this->form->get("siteAtOrigin")->getValue()) == "EUROPE")
				{
					$evalProcess = "EUROPE";
				}
				else
				{
					$evalProcess = "USA";
				}
			}
			else
			{
				$evalProcess = "EUROPE";
			}
		}
		else
		{
			$evalProcess = "EUROPE";
		}

		return $evalProcess;
	}

	// Important!! for NA and Customer Conclusion
	public function determineNAOrEuropeConclusionProcessRoute()
	{
		if($this->getLocale($this->form->get("salesOffice")->getValue()) == "USA")
		{
			$conclusionProcess = "USA";
		}
		elseif($this->getLocale($this->form->get("salesOffice")->getValue()) == "EUROPE")
		{
			$conclusionProcess = "EUROPE";
		}
		else
		{
			$conclusionProcess = "EUROPE";
		}

		return $conclusionProcess;
	}

	public function load($id)
	{
		page::addDebug("loading Complaint id=$id", __FILE__, __LINE__);

		if (!is_numeric($id))
		{
			return false;
		}

		$this->id = $id;

		$this->form->setStoreInSession(true);

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{

			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['complaint']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$loadedFromSavedForms = false;
			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			if(isset($_REQUEST["sfID"]))
			{
				$savedData = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."'");
				$dataRow = mysql_fetch_assoc($savedData);
				$dataFields = unserialize($dataRow["sfValue"]);

				if(is_array($dataFields)){
					foreach($dataFields as $key => $val){
						$fields[$key] = $val;
					}
				}

				$loadedFromSavedForms = true;
			}

			$fields["dimensionThickness"] = array($fields['dimensionThickness_quantity'], $fields['dimensionThickness_measurement']);

			$fields["quantityUnderComplaint"] = array($fields['quantityUnderComplaint_quantity'], $fields['quantityUnderComplaint_measurement']);
			$fields["complaintValue"] = array($fields['complaintValue_quantity'], $fields['complaintValue_measurement']);

			$fields["sp_quantityRecieved"] = array($fields['sp_quantityRecieved_quantity'], $fields['sp_quantityRecieved_measurement']);
			$fields["sp_additionalComplaintCost"] = array($fields['sp_additionalComplaintCost_quantity'], $fields['sp_additionalComplaintCost_measurement']);
			$fields["sp_debitValue"] = array($fields['sp_debitValue_quantity'], $fields['sp_debitValue_measurement']);

			if($this->getComplaintType($this->id) == "quality_complaint")
			{
				$this->form->get("qu_weightOfMaterial")->setValue(array($fields['qu_weightOfMaterial_quantity'], $fields['qu_weightOfMaterial_measurement']));
				$this->form->get("qu_complaintCosts")->setValue(array($fields['qu_complaintCosts_quantity'], $fields['qu_complaintCosts_measurement']));
				$fields["dimensionWidth"] = $fields['dimensionWidth_quantity'] . " " . $fields['dimensionWidth_measurement'];
				$fields["dimensionLength"] = $fields['dimensionLength_quantity'] . " " . $fields['dimensionLength_measurement'];
			}
			else
			{
				// Format and put values in fields
				$this->form->get("complaintValue")->setValue(array($fields['complaintValue_quantity'], $fields['complaintValue_measurement']));
				$fields["dimensionWidth"] = array($fields['dimensionWidth_quantity'], $fields['dimensionWidth_measurement']);
				$fields["dimensionLength"] = array($fields['dimensionLength_quantity'], $fields['dimensionLength_measurement']);
			}

			$this->form->get("quantityUnderComplaint")->setValue(array($fields['quantityUnderComplaint_quantity'], $fields['quantityUnderComplaint_measurement']));

			$this->form->get("customerComplaintDate")->setValue(page::transformDateForPHP($this->form->get("customerComplaintDate")->getValue()));

			$this->form->populate($fields);

			if($this->getComplaintType($this->id) == "supplier_complaint")
			{
				$this->form->getGroup('materialGroupGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				$this->form->getGroup('sapGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE `complaintId` = " . $this->id . " ORDER BY `id`"));

				//$this->form->getGroup('scapaInvoiceYesGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaInvoiceNumberDate WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				$this->form->get("attachment")->load("/apps/complaints/attachments/" . $this->id . "/");

				//$this->form->getGroup('ccComplaintGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM ccGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				$datasetSupplier = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM supplier WHERE id = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
				$fieldsSupplier = mysql_fetch_array($datasetSupplier);

				$this->form->get("externalEmailAddress")->setValue($fieldsSupplier['emailAddress']);
				//$this->form->get("externalEmailAddress")->setValue(sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getEmail());

				$datasetExtCheckName = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `users` LEFT JOIN `employee` ON users.username = employee.NTLogon WHERE users.username = '" . $fieldsSupplier['emailAddress'] . "'");

				// Put First Name and Last name in External textfields if they exist...
				if(mysql_num_rows($datasetExtCheckName) != 0)
				{
					$fieldsExtCheckName = mysql_fetch_array($datasetExtCheckName);

					if($fieldsExtCheckName['language'] == "")
					{
						$fieldsExtCheckName['language'] = "ENGLISH";
					}
					
					// temporary fix to close complaints where the supplier doesn't have stored firstname/lastname values - 22/09/2010 - Rob
					$fieldsExtCheckName['firstName'] .= " ";
					$fieldsExtCheckName['lastName'] .= " ";

					$this->form->get("externalFirstName")->setValue($fieldsExtCheckName['firstName']);
					$this->form->get("externalLastName")->setValue($fieldsExtCheckName['lastName']);
					$this->form->get("supplierDefaultLanguage")->setValue($fieldsExtCheckName['language']);
				}

			}
			elseif($this->getComplaintType($this->id) == "quality_complaint")
			{
				$this->form->get("attachment")->load("/apps/complaints/attachments/" . $this->id . "/");

				$this->form->getGroup('materialGroupGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				//$this->form->getGroup('ccComplaintGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM ccGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				//if(!$loadedFromSavedForms  || (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'evaluation' || $_REQUEST['status'] == 'conclusion' )))
				//{
				$this->form->getGroup('sapGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE `complaintId` = " . $this->id . " ORDER BY `id`"));
				//}

				if($this->form->get("submitOnBehalf")->getValue() == "no")
				{
					$this->form->get("internalSalesName")->setValue("");
				}

				$fields['sampleReceptionDate'] == "0000-00-00" ?  $this->form->get("sampleReceptionDate")->setValue("") : $this->form->get("sampleReceptionDate")->setValue(page::transformDateForPHP($fields['sampleReceptionDate']));

				$this->form->get("email_text")->setValue("");
			}
			else
			{
				$this->form->get("attachment")->load("/apps/complaints/attachments/" . $this->id . "/");
				$this->form->get("carrierName")->getValue() == '' ? $this->form->get("carrierName")->setValue("N/A") : $this->form->get("carrierName")->getValue();

				/**
				 * WC edit - start
				 * Have added OR rule to if statement to except evalutaions & conculsions
				 * from this rule when loading from a saved from, to allow correct
				 * loading of 'orderDetailsMulti' into form and session
				 */
				if(!$loadedFromSavedForms  || (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'evaluation' || $_REQUEST['status'] == 'conclusion' ))){
					$this->form->getGroup('orderDetailsMulti')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaOrderNumber WHERE complaintId = " . $this->id . " ORDER BY `id`"));
				}
				/**
				 * WC edit - end
				 */

				$this->form->getGroup('scapaInvoiceYesGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaInvoiceNumberDate WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				$this->form->getGroup('materialGroupGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				$this->form->getGroup('intercoGroupYes')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaIntercoOrder WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				//$this->form->getGroup('ccComplaintGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM ccGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

				//if(!$loadedFromSavedForms  || (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'evaluation' || $_REQUEST['status'] == 'conclusion' )))
				//{
				$this->form->getGroup('sapGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE `complaintId` = " . $this->id . " ORDER BY `id`"));
				//}

				if($this->form->get("submitOnBehalf")->getValue() == "no")
				{
					$this->form->get("internalSalesName")->setValue("");
				}

				$fields['sampleReceptionDate'] == "0000-00-00" ?  $this->form->get("sampleReceptionDate")->setValue("") : $this->form->get("sampleReceptionDate")->setValue(page::transformDateForPHP($fields['sampleReceptionDate']));
				$fields['sampleDate'] == "0000-00-00" ?  $this->form->get("sampleDate")->setValue("") : $this->form->get("sampleDate")->setValue(page::transformDateForPHP($fields['sampleDate']));

				$this->form->get("email_text")->setValue("");
			}

			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();

		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			return false;
		}


		/**
		 * checks for a evaluation section of the Complaints and loads it
		 */
		$this->evaluation = new evaluation($this);
		if (!$this->evaluation->load($id))
		{
			unset($this->evaluation);
		}
		page::addDebug("load evaluation for Complaint id=$id", __FILE__, __LINE__);

		/**
		 * checks for a conclusion section of the Complaints and loads it
		 */
		$this->conclusion = new conclusion($this);
		if (!$this->conclusion->load($id))
		{
			unset($this->conclusion);
		}
		page::addDebug("load conclusion for Complaint id=$id", __FILE__, __LINE__);

		return true;
	}


	public function getID()
	{
		if(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		elseif(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		else
		{
			$cfi = $this->form->get("id")->getValue();
		}

		return $cfi;
	}


	public function getEvaluation()
	{
		if (isset($this->evaluation))
		{
			return $this->evaluation;
		}
		else
		{
			return false;
		}
	}

	public function getConclusion()
	{
		if (isset($this->conclusion))
		{
			return $this->conclusion;
		}
		else
		{
			return false;
		}
	}



	public function addSection($section)
	{
		switch ($section)
		{
			case 'complaints':
				$this->complaint = new complaint($this);
				break;
			case 'evaluation':
				$this->evaluation = new evaluation($this);
				break;
			case 'conclusion':
				$this->conclusion = new conclusion($this);
				break;

			default: die('addSection() unknown $section');
		}
	}



	public function validate()
	{
		$valid = true;
		if(!isset($_GET["sfID"])){

			if (!$this->form->validate())
			{
				$valid = false;
			}

		}

		if (isset($this->evaluation))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "evaluation")){

				if(!$this->evaluation->validate())
				{
					$valid = false;
				}

			}
		}

		if (isset($this->conclusion))
		{
			if(!isset($_GET["sfID"]) || (isset($_GET["sfID"]) && $_POST["action"]=="submit" && $_GET["status"] == "conclusion")){

				if(!$this->conclusion->validate())
				{
					$valid = false;
				}
			}
		}

		return $valid;
	}

	public function save($process)
	{
		page::addDebug("Saving Complaint process: ".$process,__FILE__,__LINE__);

		switch ($process)
		{
			case 'complaint':

				$this->determineStatus();

				if ($this->loadedFromDatabase)
				{
					//internal sales name not to be changed after the first time a complaint is entered
					$this->form->get("internalSalesName")->setIgnore(true);
					$this->form->get("salesOffice")->setIgnore(true);

					// set Complaint owner
					$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());

					if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
					{
						$this->form->get("openDate")->setValue($this->form->get("openDate")->getValue());

						$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

						//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
						//$fieldsEmp = mysql_fetch_array($datasetEmp);

						//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
						$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());

						$this->form->get("sapCustomerNumber")->setValue($this->form->get("sp_sapSupplierNumber")->getValue());

						if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						{
							$this->form->get("scapaStatus")->setValue("0");
							$this->form->get("extStatus")->setValue("0");
							$this->form->get("added")->setValue("1");
						}
					}
					elseif($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
					{
						if($this->form->get("submitOnBehalf")->getValue() == "yes")
						{
							$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $this->form->get("internalSalesName")->getValue() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get($this->form->get("internalSalesName")->getValue())->getSite());

							// set Internal sales name
							//$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get($fieldsEmp['NTLogon'])->getName());
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
						else
						{
							$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());

							// set Internal Sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
					}
					else
					{
						if($this->form->get("submitOnBehalf")->getValue() == "yes")
						{
							$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $this->form->get("internalSalesName")->getValue() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get($this->form->get("internalSalesName")->getValue())->getSite());

							// set Internal sales name
							//$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get($fieldsEmp['NTLogon'])->getName());
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
						else
						{
							$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());

							// set Internal Sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}

						//if M or D selected default g8d to yes
						$cat = $this->form->get("category")->getValue();
						if($cat[0] == M || $cat[0] == D)
						{
							$this->form->get("g8d")->setValue("yes");
						}
					}

					if($this->form->get("groupAComplaint")->getValue() == "Yes")
					{
						//ereg_replace("[A-Za-z]", "", $this->form->get("groupedComplaintId")->getValue());

						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'No', groupedComplaintId = '' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->id . "' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->form->get("groupedComplaintId")->getValue() . "' WHERE id = '" . $this->id . "'");
					}

					// Calculate Currency 27/02/2008 - JM
					if($this->complaintType != "quality_complaint")
					{
						$this->calculateCurrency($this->form->get("complaintValue")->getMeasurement());
					}

					//if($this->complaintType == "quality_complaint")
					//{
					//	$this->calculateCurrency($this->form->get("qu_complaintCosts")->getMeasurement());
					//}


					if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
					{
						//Update SAP Customer Name and External Sales Name
						$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `id`, `name`, `salesPerson` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
						$sapFields = mysql_fetch_array($sapDataset);

						$this->form->get("sp_sapSupplierName")->setValue(page::xmlentities($sapFields['name']));
						//$this->form->get("sp_sapSupplierName")->setValue(page::xmlentities(sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getName()));
						$this->form->get("externalSalesName")->setValue($sapFields['salesPerson']);
						//$this->form->get("externalSalesName")->setValue(sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getSalesPerson());

						if ($this->form->get("addSAPEmailAddress")->getValue() == 'yes')
						{
							mysql::getInstance()->selectDatabase("SAP")->Execute("UPDATE supplier SET emailAddress = '" . $this->form->get("newSAPEmailAddress")->getValue() . "' WHERE id ='" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
						}

						$this->form->get("sapName")->setValue($this->form->get("sp_sapSupplierName")->getValue());
					}
					elseif($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
					{
						// do nothing ...
					}
					else
					{
						//Update SAP Customer Name and External Sales Name
						$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `id`, `name`, `salesPerson` FROM `customer` WHERE `id` = '" . $this->form->get("sapCustomerNumber")->getValue() . "'");
						$sapFields = mysql_fetch_array($sapDataset);

						$this->form->get("sapName")->setValue(page::xmlentities($sapFields['name']));
						$this->form->get("externalSalesName")->setValue($sapFields['salesPerson']);

						if ($this->form->get("addSAPEmailAddress")->getValue() == 'yes')
						{
							mysql::getInstance()->selectDatabase("SAP")->Execute("UPDATE customer SET emailAddress = '" . $this->form->get("newSAPEmailAddress")->getValue() . "' WHERE id ='" . $this->form->get("sapCustomerNumber")->getValue() . "'");
						}

						//if sample recieved has previously been yes the old date must be cleared to avoid error
						if ($this->form->get("sampleReceived")->getValue() == "No")
						{
							$this->form->get("sampleReceptionDate")->setValue("0");
							$this->form->get("sampleTransferred")->setValue("no");
							$this->form->get("sampleDate")->setValue("0");
						}
						if ($this->form->get("sampleTransferred")->getValue() == "no")
						{
							$this->form->get("sampleDate")->setValue("0");
						}
					}

					// Check Fields Changed Function
					$this->checkFieldsUpdated();

					// update
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id= " . $this->id . "");

					if($this->complaintType == "supplier_complaint")
					{
						$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
						if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						{
							$this->form->get("attachment")->setUploadExternal(true);
							$this->form->get("attachment")->setExtFinalFileLocation("/apps/complaintsExternal/attachments/" . $this->id . "/");
						}
						$this->form->get("attachment")->moveTempFileToFinal();
					}
					else
					{
						$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
					}

					// This is usually where the email happens but for a test I will do it at the end of the save().
					//$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", $this->form->get("email_text")->getValue());
					//$this->getEmailNotification(usercache::getInstance()->get($this->form->get("copy_to")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "copyTo", $this->form->get("email_text")->getValue());

					// save new data
					$this->addLog(translate::getInstance()->translate("complaint_updated_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());

					if($this->complaintType == "supplier_complaint")
					{
						if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						{
							$this->saveExternal("insert", $this->id);
						}
					}

				}
				else
				{
					// set Complaint owner
					$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());

					if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
					{
						$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

						//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
						//$fieldsEmp = mysql_fetch_array($datasetEmp);

						$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());

						$this->form->get("sapCustomerNumber")->setValue($this->form->get("sp_sapSupplierNumber")->getValue());

						if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						{
							$this->form->get("scapaStatus")->setValue("0");
							$this->form->get("extStatus")->setValue("0");
							$this->form->get("added")->setValue("1");
						}

					}
					elseif($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
					{
						if($this->form->get("submitOnBehalf")->getValue() == "yes")
						{
							$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

							$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $this->form->get("internalSalesName")->getValue() ."'");
							$fieldsEmp = mysql_fetch_array($datasetEmp);

							$this->form->get("salesOffice")->setValue($fieldsEmp['site']);

							// set Internal sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
						else
						{
							$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());

							// set Internal Sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
					}
					else
					{
						if($this->form->get("submitOnBehalf")->getValue() == "yes")
						{
							$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

							$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $this->form->get("internalSalesName")->getValue() ."'");
							$fieldsEmp = mysql_fetch_array($datasetEmp);

							$this->form->get("salesOffice")->setValue($fieldsEmp['site']);

							// set Internal sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}
						else
						{
							$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

							//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
							//$fieldsEmp = mysql_fetch_array($datasetEmp);

							//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
							$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());

							// set Internal Sales name
							$this->form->get("internalSalesName")->setValue(addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName()));
						}

						//if M or D selected default g8d to yes
						$cat = $this->form->get("category")->getValue();
						if($cat[0] == 'M' || $cat[0] == 'D')
						{
							$this->form->get("g8d")->setValue("yes");
						}
					}

					// Calculate Currency 27/02/2008 - JM
					if($this->complaintType == "quality_complaint")
					{
						//$this->calculateCurrency($this->form->get("qu_complaintCosts")->getMeasurement());
					}
					else
					{
						$this->calculateCurrency($this->form->get("complaintValue")->getMeasurement());
					}

					// set report date
					$this->form->get("openDate")->setValue(common::nowDateForMysql());

					//Determine Complaint Type
					if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
					{
						//Update SAP Customer Name and External Sales Name
						$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `id`, `name`, `salesPerson` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
						$sapFields = mysql_fetch_array($sapDataset);

						$this->form->get("sp_sapSupplierName")->setValue(page::xmlentities($sapFields['name']));
						//$this->form->get("sp_sapSupplierName")->setValue(page::xmlentities(sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getName()));
						$this->form->get("externalSalesName")->setValue($sapFields['salesPerson']);
						//$this->form->get("externalSalesName")->setValue(sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getSalesPerson());

						if ($this->form->get("addSAPEmailAddress")->getValue() == 'yes')
						{
							mysql::getInstance()->selectDatabase("SAP")->Execute("UPDATE supplier SET emailAddress = '" . $this->form->get("newSAPEmailAddress")->getValue() . "' WHERE id ='" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
						}

						$this->form->get("sapName")->setValue($this->form->get("sp_sapSupplierName")->getValue());

					}
					elseif($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
					{
						// do nothing ..
					}
					else
					{
						$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `id`, `name`, `salesPerson` FROM `customer` WHERE `id` = '" . $this->form->get("sapCustomerNumber")->getValue() . "'");
						$sapFields = mysql_fetch_array($sapDataset);

						$this->form->get("sapName")->setValue(page::xmlentities($sapFields['name']));
						$this->form->get("externalSalesName")->setValue($sapFields['salesPerson']);

						//if sample recieved has previously been yes the old date must be cleared to avoid error
						if ($this->form->get("sampleReceived")->getValue() == "No")
						{
							$this->form->get("sampleReceptionDate")->setValue("0");
							$this->form->get("sampleTransferred")->setValue("No");
							$this->form->get("sampleDate")->setValue("0");
						}
						if ($this->form->get("sampleTransferred")->getValue() == "No")
						{
							$this->form->get("sampleDate")->setValue("0");
						}

						// begin transaction
						mysql::getInstance()->selectDatabase("complaints")->Execute("BEGIN");

						if ($this->form->get("addSAPEmailAddress")->getValue() == 'yes')
						{
							mysql::getInstance()->selectDatabase("SAP")->Execute("UPDATE customer SET emailAddress = '" . $this->form->get("newSAPEmailAddress")->getValue() . "' WHERE id ='" . $this->form->get("sapCustomerNumber")->getValue() . "'");
						}
					}

					// insert

					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO complaint " . $this->form->generateInsertQuery("complaint"));

					// get last inserted
					$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint ORDER BY id DESC LIMIT 1");

					$fields = mysql_fetch_array($dataset);

					$this->id = $fields['id'];
					$this->form->get("id")->setValue($fields['id']);

					if($this->form->get("groupAComplaint")->getValue() == "Yes")
					{
						//ereg_replace("[A-Za-z]", "", $this->form->get("groupedComplaintId")->getValue());

						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'No', groupedComplaintId = '' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->id . "' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->form->get("groupedComplaintId")->getValue() . "' WHERE id = '" . $this->id . "'");
					}

					// end transaction
					mysql::getInstance()->selectDatabase("complaints")->Execute("COMMIT");

					if($this->form->get("submitOnBehalf")->getValue() == "yes")
					{
						$this->addLog(translate::getInstance()->translate("complaint_added_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . " on behalf of " . page::xmlentities(usercache::getInstance()->get($this->form->get("internalSalesName")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());
					}
					else
					{
						$this->addLog(translate::getInstance()->translate("complaint_added_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());
					}

					if($this->complaintType == "supplier_complaint")
					{
						$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
						//if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						//{
						$this->form->get("attachment")->setUploadExternal(true);
						$this->form->get("attachment")->setExtFinalFileLocation("/apps/complaintsExternal/attachments/" . $this->id . "/");
						//}
						$this->form->get("attachment")->moveTempFileToFinal();
					}
					elseif($this->complaintType == "quality_complaint")
					{
						$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();
					}
					else
					{
						if($this->form->get("businessUnit")->getValue() == "automotive")
						{
							$this->getEmailNotification("stefan.lietmann@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", "A new automotive complaint");
						}
						//email Sandro Pellegrino when credit requested and origin of site error is Rorschach
						if($this->form->get("siteAtOrigin")->getValue() == "Rorschach" && $this->form->get("creditNoteRequested")->getValue() == "YES")
						{
							$this->getEmailNotification("sandro.pellegrino@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", "A new complaint with a credit request and Rorschach as the site at the origin of error");
						}

						$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
						$this->form->get("attachment")->moveTempFileToFinal();

						if($this->form->get("siteAtOrigin")->getValue() != "Ashton" && $this->form->get("siteAtOrigin")->getValue() != "Barcelona" && $this->form->get("siteAtOrigin")->getValue() != "Bellegarde" && $this->form->get("siteAtOrigin")->getValue() != "Dunstable" && $this->form->get("siteAtOrigin")->getValue() != "Ghislarengo" && $this->form->get("siteAtOrigin")->getValue() != "Luton" && $this->form->get("siteAtOrigin")->getValue() != "Mannheim" && $this->form->get("siteAtOrigin")->getValue() != "Valence")
						{
							$this->getEmailNotification("paola.crepaldi@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("nathalie.rigal@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("gwen.aubry@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("lisa.kean@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("andrew.young@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("stefan.lietmann@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
							$this->getEmailNotification("jason.matthews@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newNonEuropeanComplaint");
						}

					}

					if($this->complaintType == "supplier_complaint")
					{
						if($this->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
						{
							$this->saveExternal("insert", $this->id);
						}
					}
				}

				if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
				{
					// For multiple fields - SAP Item Number
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sapItemNumber` WHERE `complaintId` = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("sapGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("sapGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO sapItemNumber " . $this->form->getGroup("sapGroup")->generateInsertQuery($i));
					}
					$this->sapItemNumbers($this->id);

					// For multiple fields - Material Group
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM materialGroup WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("materialGroupGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("materialGroupGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO materialGroup " . $this->form->getGroup("materialGroupGroup")->generateInsertQuery($i));
					}
					$this->sapMaterialGroup($this->id);

					// For multiple fields - Scapa Invoice Number and Date
					//				mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM scapaInvoiceNumberDate WHERE complaintId = " . $this->id);
					//
					//				for ($i=0; $i < $this->form->getGroup("scapaInvoiceYesGroup")->getRowCount(); $i++)
					//				{
					//					$this->form->getGroup("scapaInvoiceYesGroup")->setForeignKeyValue($this->id);
					//					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO scapaInvoiceNumberDate " . $this->form->getGroup("scapaInvoiceYesGroup")->generateInsertQuery($i));
					//				}

					// For multiple fields - CC Complaint Group
					//mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM ccGroup WHERE complaintId = " . $this->id);

					//				for ($i=0; $i < $this->form->getGroup("ccComplaintGroup")->getRowCount(); $i++)
					//				{
					//					$this->form->getGroup("ccComplaintGroup")->setForeignKeyValue($this->id);
					//					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO ccGroup " . $this->form->getGroup("ccComplaintGroup")->generateInsertQuery($i));
					//				}

				}
				elseif($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
				{
					// For multiple fields - SAP Item Number
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sapItemNumber` WHERE `complaintId` = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("sapGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("sapGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO sapItemNumber " . $this->form->getGroup("sapGroup")->generateInsertQuery($i));
					}
					$this->sapItemNumbers($this->id);

					// For multiple fields - Material Group
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM materialGroup WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("materialGroupGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("materialGroupGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO materialGroup " . $this->form->getGroup("materialGroupGroup")->generateInsertQuery($i));
					}
					$this->sapMaterialGroup($this->id);
				}
				else
				{
					// For multiple fields - Scapa Order Number
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM scapaOrderNumber WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("orderDetailsMulti")->getRowCount(); $i++)
					{
						$this->form->getGroup("orderDetailsMulti")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("Complaints")->Execute("INSERT INTO scapaOrderNumber " . $this->form->getGroup("orderDetailsMulti")->generateInsertQuery($i));
					}

					// For multiple fields - Scapa Invoice Number and Date
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM scapaInvoiceNumberDate WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("scapaInvoiceYesGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("scapaInvoiceYesGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO scapaInvoiceNumberDate " . $this->form->getGroup("scapaInvoiceYesGroup")->generateInsertQuery($i));
					}

					// For multiple fields - Material Group
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM materialGroup WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("materialGroupGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("materialGroupGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO materialGroup " . $this->form->getGroup("materialGroupGroup")->generateInsertQuery($i));
					}
					$this->sapMaterialGroup($this->id);

					// For multiple fields - SAP Item Number
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `sapItemNumber` WHERE `complaintId` = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("sapGroup")->getRowCount(); $i++)
					{
						$this->form->getGroup("sapGroup")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO sapItemNumber " . $this->form->getGroup("sapGroup")->generateInsertQuery($i));
					}
					$this->sapItemNumbers($this->id);

					// For multiple fields - Scapa Interco Order
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM scapaIntercoOrder WHERE complaintId = " . $this->id);

					for ($i=0; $i < $this->form->getGroup("intercoGroupYes")->getRowCount(); $i++)
					{
						$this->form->getGroup("intercoGroupYes")->setForeignKeyValue($this->id);
						mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO scapaIntercoOrder " . $this->form->getGroup("intercoGroupYes")->generateInsertQuery($i));
					}

					// For multiple fields - CC Complaint Group
					//mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM ccGroup WHERE complaintId = " . $this->id);

					/*for ($i=0; $i < $this->form->getGroup("ccComplaintGroup")->getRowCount(); $i++)
					{
					$this->form->getGroup("ccComplaintGroup")->setForeignKeyValue($this->id);
					mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO ccGroup " . $this->form->getGroup("ccComplaintGroup")->generateInsertQuery($i));
					}*/

				}

				// Don't send email if current owner submits the complaint -
				//if(currentuser::getInstance()->getNTLogon() != $this->form->get("owner")->getValue())
				//{
				if($this->complaintType == "supplier_complaint")
				{
					if($this->form->get("sp_submitToExtSupplier")->getValue() != "Yes")
					{
						$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", utf8_encode($this->form->get("email_text")->getValue()));
					}
					else
					{
						// do nothing if sending to supplier - Stefan change
					}
				}
				else
				{
					$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", utf8_encode($this->form->get("email_text")->getValue()));
				}
				//}

				break;

			case 'evaluation':

				$this->evaluation->setComplaintId($this->id);
				$this->evaluation->save();
				break;
			case 'conclusion':
				$this->conclusion->setComplaintId($this->id);
				$this->conclusion->save();
				break;
		}


		page::redirect("/apps/complaints/index?id=" . $this->id);		//redirects the page back to the summary

	}

	//	public function getSupplierEmail($supplierNumber)
	//	{
	//		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `emailAddress` FROM `supplier` WHERE `id` = '" . $supplierNumber ."'");
	//		$fields = mysql_fetch_array($dataset);
	//
	//		$emailAddress = $fields['emailAddress'];
	//
	//		return $emailAddress;
	//	}

	public function checkFieldsUpdated()
	{
		// Check Current Field Values
		$currentCategory = $this->form->get("category")->getValue();
		$currentProblemDescription = $this->form->get("problemDescription")->getValue();

		if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
		{
			$currentMaterialInvolved = $this->form->get("sp_materialInvolved")->getValue();
			$currentSampleSent = $this->form->get("sp_sampleSent")->getValue();
		}
		elseif($this->complaintType == "quality_complaint")
		{
			// do nothing for now ...
			$currentQuantityUnderComplaint_quantity = $this->form->get("quantityUnderComplaint")->getQuantity();
			$currentQuantityUnderComplaint_measurement = $this->form->get("quantityUnderComplaint")->getMeasurement();
			$currentComplaintValue_quantity = $this->form->get("qu_complaintCosts")->getQuantity();
			$currentComplaintValue_measurement = $this->form->get("qu_complaintCosts")->getMeasurement();
			$currentLineStoppage = $this->form->get("lineStoppage")->getValue();
		}
		else
		{
			$currentLineStoppage = $this->form->get("lineStoppage")->getValue();
			$currentSampleReceived = $this->form->get("sampleReceived")->getValue();
		}

		if($this->complaintType != "quality_complaint")
		{
			$currentQuantityUnderComplaint_quantity = $this->form->get("quantityUnderComplaint")->getQuantity();
			$currentQuantityUnderComplaint_measurement = $this->form->get("quantityUnderComplaint")->getMeasurement();
			$currentComplaintValue_quantity = $this->form->get("complaintValue")->getQuantity();
			$currentComplaintValue_measurement = $this->form->get("complaintValue")->getMeasurement();
		}

		// Check Updated Field Values
		$checkUpdated = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `category`, `problemDescription`, `sp_materialInvolved`, `lineStoppage`, `sampleReceived`, `quantityUnderComplaint_quantity`, `quantityUnderComplaint_measurement`, `complaintValue_quantity`, `complaintValue_measurement`, `sp_sampleSent`, `qu_complaintCosts_quantity`, `qu_complaintCosts_measurement` FROM complaint WHERE id = " . $this->id . "");
		$fieldsUpdated = mysql_fetch_array($checkUpdated);

		$newCategory = $fieldsUpdated['category'];
		$newProblemDescription = $fieldsUpdated['problemDescription'];
		$newMaterialInvolved = $fieldsUpdated['sp_materialInvolved'];
		$newLineStoppage = $fieldsUpdated['lineStoppage'];
		$newQuantityUnderComplaint_quantity = $fieldsUpdated['quantityUnderComplaint_quantity'];
		$newQuantityUnderComplaint_measurement = $fieldsUpdated['quantityUnderComplaint_measurement'];
		$newComplaintValue_quantity = $fieldsUpdated['complaintValue_quantity'];
		$newComplaintValue_measurement = $fieldsUpdated['complaintValue_measurement'];
		$newQuComplaintValue_quantity = $fieldsUpdated['qu_complaintCosts_quantity'];
		$newQuComplaintValue_measurement = $fieldsUpdated['qu_complaintCosts_measurement'];

		$newSampleReceived = $fieldsUpdated['sampleReceived'];
		$newSampleSent = $fieldsUpdated['sp_sampleSent'];

		// Compare Current and New Fields
		$updatedFields = "";

		if($currentCategory != $newCategory)
		{
			$updatedFields .= "Category: Old(" . translate::getInstance()->translate($newCategory) . ") New(" . translate::getInstance()->translate($currentCategory) . ") - ";
		}

		if($currentProblemDescription != $newProblemDescription)
		{
			$updatedFields .= "Problem Description: Old(" . $newProblemDescription . ") New(" . $currentProblemDescription . ") - ";
		}

		if($this->complaintType == "supplier_complaint")  // need to change as don't like if and ==
		{
			if($currentMaterialInvolved != $newMaterialInvolved)
			{
				$updatedFields .= "Material Involved: Old(" . $newMaterialInvolved . ") New(" . $currentMaterialInvolved . ") - ";
			}
		}
		else
		{
			if($currentLineStoppage != $newLineStoppage)
			{
				$updatedFields .= "Line Stoppage: Old(" . $newLineStoppage . ") New(" . $currentLineStoppage . ") - ";
			}
		}

		if($this->complaintType == "quality_complaint")  // need to change as don't like if and ==
		{
			if($currentQuantityUnderComplaint_quantity != $newQuantityUnderComplaint_quantity || $currentQuantityUnderComplaint_measurement != $newQuantityUnderComplaint_measurement)
			{
				$updatedFields .= "Quantity Under Complaint: Old(" . $newQuantityUnderComplaint_quantity . " " . $newQuantityUnderComplaint_measurement . ") New(" . $currentQuantityUnderComplaint_quantity . " " . $currentQuantityUnderComplaint_measurement . ") - ";
			}

			if($currentComplaintValue_quantity != $newQuComplaintValue_quantity || $currentComplaintValue_measurement != $newQuComplaintValue_measurement)
			{
				$updatedFields .= "Complaint Value: Old(" . $newQuComplaintValue_quantity . " " . $newQuComplaintValue_measurement . ") New(" . $currentComplaintValue_quantity . " " . $currentComplaintValue_measurement . ") - ";
			}
		}
		else
		{
			if($currentQuantityUnderComplaint_quantity != $newQuantityUnderComplaint_quantity || $currentQuantityUnderComplaint_measurement != $newQuantityUnderComplaint_measurement)
			{
				$updatedFields .= "Quantity Under Complaint: Old(" . $newQuantityUnderComplaint_quantity . " " . $newQuantityUnderComplaint_measurement . ") New(" . $currentQuantityUnderComplaint_quantity . " " . $currentQuantityUnderComplaint_measurement . ") - ";
			}

			if($currentComplaintValue_quantity != $newComplaintValue_quantity || $currentComplaintValue_measurement != $newComplaintValue_measurement)
			{
				$updatedFields .= "Complaint Value: Old(" . $newComplaintValue_quantity . " " . $newComplaintValue_measurement . ") New(" . $currentComplaintValue_quantity . " " . $currentComplaintValue_measurement . ") - ";
			}
		}

		if($currentSampleReceived != $newSampleReceived)
		{
			$updatedFields .= "Sample Received: Old(" . $newSampleReceived . ") New(" . $currentSampleReceived . ") - ";
		}

		if($currentSampleSent != $newSampleSent)
		{
			$updatedFields .= "Sample Sent: Old(" . $newSampleSent . ") New(" . $currentSampleSent . ") - ";
		}

		if($updatedFields)
		{
			$this->addLog(translate::getInstance()->translate("complaint_fields_have_been_updated") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", substr_replace($updatedFields ,"",-2));
		}
	}

	public function sendSupplierComment($id)
	{
		if($this->form->get("externalComment")->getValue() != "")
		{
			mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO comments VALUES ('" . $id . "','" . currentuser::getInstance()->getNTlogon() . "','External Submission','" . page::nowDateForMysql() . "','" . $this->form->get("externalComment")->getValue() . "')");
		}
	}

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

		//Update SAP Customer Name and External Sales Name
		//$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `language` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
		//$sapFields = mysql_fetch_array($sapDataset);

		// Retrieve prefered language for SAP supplier
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

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchaser or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manualFR.pdf";

				break;

			case 'GERMAN':

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchaser or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
				$attachment = "/home/live/apps/complaints/data/manualDE.pdf";

			default:

				$textMessage = "Dear Supplier,\n\nPlease see attached the manual for the new Scapa Supplier complaint system.\n\nIf you have any questions, please contact the relevant purchaser or Quality Manager from Scapa.\n\nExtranet URL: http://ext.scapa.com\n\nYour username is: " . $extranetUsername . "\n\nYour password is: " . $extranetPassword . "\n\nPlease ensure you change your password and update your contact information on first use.\n\nMany Thanks, \n\nScapa";
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

	public function checkSupplierExistsOnExtranet($emailAddress)
	{
		//$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `users` WHERE sapSupplierNumber = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");

		// Enable this for Multiple Users on one Supplier Number
		$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `users` WHERE username = '" . $emailAddress . "'");

		if(mysql_num_rows($dataset) == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function makeNewSupplierOnExtranet($emailAddress)
	{
		$username = $emailAddress;

		$this->externalPassword = rand("100000", "999999");

		//$this->externalPassword2 = sha1($this->externalPassword);

		$firstName = $this->form->get("externalFirstName")->getValue();

		$lastName = $this->form->get("externalLastName")->getValue();

		// Insert Login Details to Extranet
		mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO `users` (username, password, sapSupplierNumber, emailAddress) VALUES ('" . $username . "','" . $this->externalPassword . "','" . $this->form->get("sp_sapSupplierNumber")->getValue() . "','" . $emailAddress . "')");

		// Insert Employee Details for Site to work correctly but set as externalUser is 1.
		$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM `employee` WHERE NTLogon = '" . $username . "'");

		if(mysql_num_rows($dataset) == 1)
		{
			// do nothing ...
		}
		else
		{
			mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO `employee` (NTLogon, firstName, lastName, email, enabled, externalUser, language) VALUES ('" . $username . "','" . $firstName . "','" . $lastName . "','" . $emailAddress . "', 1,1, '" . $this->form->get("supplierDefaultLanguage")->getValue() . "')");
		}
	}

	public function saveExternal($option, $id)
	{
		switch ($option)
		{
			case 'insert':

				$dataset = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT id FROM complaintExternal WHERE id = " . $this->id . "");

				if(mysql_num_rows($dataset) > 0)
				{
					$this->addLog(translate::getInstance()->translate("edited_complaint_not_updated_on_supplier") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", "Important! - Updated values on the complaint are not seen by the Supplier after it has been submitted externally.");
					break;
				}

				// Add to action log
				//mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",

				//$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
				//$fields = mysql_fetch_array($dataset);

				//$this->form->get("scapaContact")->setValue($fields['firstName'] . " " . $fields['lastName']);
				$this->form->get("scapaContact")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName());
				//$this->form->get("scapaTel")->setValue($fields['phone']);
				$this->form->get("scapaTel")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getPhone());
				//$this->form->get("scapaSite")->setValue($fields['site']);
				$this->form->get("scapaSite")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());
				//$this->form->get("scapaEmail")->setValue($fields['email']);
				$this->form->get("scapaEmail")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail());
				$this->form->get("id")->setValue($this->id);

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO complaintExternal " . $this->form->generateInsertQueryExt("complaintExt"));

				// For multiple fields - SAP Item Number
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("DELETE FROM `sapItemNumber` WHERE `complaintId` = " . $this->id);

				for ($i=0; $i < $this->form->getGroup("sapGroup")->getRowCount(); $i++)
				{
					$this->form->getGroup("sapGroup")->setForeignKeyValue($this->id);
					mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO sapItemNumber " . $this->form->getGroup("sapGroup")->generateInsertQuery($i));
				}

				// For multiple fields - Material Group
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("DELETE FROM materialGroup WHERE complaintId = " . $this->id);

				for ($i=0; $i < $this->form->getGroup("materialGroupGroup")->getRowCount(); $i++)
				{
					$this->form->getGroup("materialGroupGroup")->setForeignKeyValue($this->id);
					mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO materialGroup " . $this->form->getGroup("materialGroupGroup")->generateInsertQuery($i));
				}

				//$this->getEmailNotification(usercache::getInstance()->get("jason.matthews@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint"));

				//Update SAP Customer Name and External Sales Name
				$sapDataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `id`, `name`, `salesPerson`, `emailAddress` FROM `supplier` WHERE `id` = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");
				$sapFields = mysql_fetch_array($sapDataset);

				// Email Notification to Supplier
				if($this->form->get("externalEmailAddress")->getValue() == $sapFields['emailAddress'])
				{
					//$emailAddress = sapcache::getInstance()->get($this->form->get("sp_sapSupplierNumber")->getValue())->getEmail();
					$emailAddress = $sapFields['emailAddress'];
				}
				elseif($this->form->get("externalEmailAddress")->getValue() != $sapFields['emailAddress'] && $sapFields['emailAddress'] != "")
				{
					$emailAddress = $this->form->get("externalEmailAddress")->getValue();
				}
				elseif($sapFields['emailAddress'] == "")
				{
					mysql::getInstance()->selectDatabase("SAP")->Execute("UPDATE supplier SET emailAddress = '" . $this->form->get("externalEmailAddress")->getValue() . "' WHERE id = '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "'");

					$emailAddress = $this->form->get("externalEmailAddress")->getValue();
				}

				//$this->getEmailNotification($this->getSupplierEmail($this->form->get("sp_sapSupplierNumber")->getValue()), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newExternal", $this->form->get("externalComment")->getValue());
				$this->getEmailNotification($emailAddress . ", intranet@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newExternal", $this->form->get("externalComment")->getValue(), page::nowDateTimeForMysqlPlusOneDay());

				// Add Log to show it has been submitted to external system ...
				$this->addLog(translate::getInstance()->translate("complaint_updated_sent_to_supplier_by") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("externalComment")->getValue());

				/*
				Timer Information - Supplier Module Only - This is to set the time the complaint was submitted to the supplier.
				Need to determine what happens after the time.
				*/

				//$nowDatePlusOneDay = page::nowDateTimeForMysqlPlusOneDay(); // Keep consistent.

				//mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET supplierTimer = '" . page::nowDateTimeForMysqlPlusOneDay() . "' WHERE id = " . $id . "");

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET supplierTimer = '" . page::nowDateTimeForMysqlPlusOneDay() . "', supplierTimerStatus = '0' WHERE id = " . $id . "");

				// Add the External Comment to the Extranet
				$this->sendSupplierComment($this->id);

				if(!$this->checkSupplierExistsOnExtranet($emailAddress))
				{
					$this->makeNewSupplierOnExtranet($emailAddress);

					// Send Supplier Manual with attachment
					$this->sendSupplierManual($emailAddress, usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail());
				}

				break;

			case 'update':

				die("You can not update a Supplier Complaint once it has been externally submitted.");

				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
				$fields = mysql_fetch_array($dataset);

				$this->form->get("scapaContact")->setValue($fields['firstName'] . " " . $fields['lastName']);
				$this->form->get("scapaTel")->setValue($fields['phone']);
				$this->form->get("scapaSite")->setValue($fields['site']);
				$this->form->get("scapaEmail")->setValue($fields['email']);
				$this->form->get("id")->setValue($this->id);

				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal " . $this->form->generateUpdateQueryExt("complaintExt") . " WHERE id = " . $id . "");

				// For multiple fields - SAP Item Number
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("DELETE FROM `sapItemNumber` WHERE `complaintId` = " . $this->id);

				for ($i=0; $i < $this->form->getGroup("sapGroup")->getRowCount(); $i++)
				{
					$this->form->getGroup("sapGroup")->setForeignKeyValue($this->id);
					mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO sapItemNumber " . $this->form->getGroup("sapGroup")->generateInsertQuery($i));
				}

				// For multiple fields - Material Group
				mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("DELETE FROM materialGroup WHERE complaintId = " . $this->id);

				for ($i=0; $i < $this->form->getGroup("materialGroupGroup")->getRowCount(); $i++)
				{
					$this->form->getGroup("materialGroupGroup")->setForeignKeyValue($this->id);
					mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("INSERT INTO materialGroup " . $this->form->getGroup("materialGroupGroup")->generateInsertQuery($i));
				}

				//mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("UPDATE complaintExternal SET (id, complaintOpenDate, scapaStatus, extStatus, sapCustomerNumber, owner) VALUES (" . $this->id . ", '" . $this->form->get("openDate")->getValue() . "', '0', '0', '" . $this->form->get("sp_sapSupplierNumber")->getValue() . "', '" . $this->form->get("internalSalesName")->getValue() . "')");

				//$this->getEmailNotification(usercache::getInstance()->get("jason.matthews@scapa.com", usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint"));

				break;

			case 'added':

				// do nothing ...

				break;

			default:

				// do nothing ...

				break;
		}
	}

	public function getComplaintLocaleSettings($locale)
	{
		//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $locale ."'");
		//$fieldsEmp = mysql_fetch_array($datasetEmp);

		// above is not needed as call can be made to user class ...

		$userLocale = currentuser::getInstance()->getLocale();

		if($userLocale == "USA" || $userLocale == "CANADA")
		{
			$this->form->get("complaintLocation")->setValue("american");
		}
		elseif($userLocale == "MALAYSIA")
		{
			$this->form->get("complaintLocation")->setValue("malaysian");
		}
		else
		{
			$this->form->get("complaintLocation")->setValue("european");
		}
	}

	public function calculateCurrency($currency)
	{
		$currencyConversion = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `currency` WHERE `currency` = '" . $currency ."'");
		$currencyConversionFields = mysql_fetch_array($currencyConversion);

		//if($this->complaintType == "quality_complaint")
		//{
			//$value = $this->form->get("qu_complaintCosts")->getQuantity() * $currencyConversionFields['currencyValue'];
		//}
		//else
		//{
			$value = $this->form->get("complaintValue")->getQuantity() * $currencyConversionFields['currencyValue'];
		//}

		$gbpCurrency = sprintf("%.2f", $value);

		$this->form->get("gbpComplaintValue")->setValue(array("" . $gbpCurrency . "", "GBP"));
	}

	public function determineStatus()
	{
		$location = "complaint";
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


	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
		$this->getID(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}

	public function getCreator()
	{
		return $this->form->get("creator")->getValue();
	}


	public function defineForm()
	{
		$savedFields = array();
		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}

		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("complaint" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
		//$initiation->setBorder(false);
		$submitOnBehalfGroup = new group("submitOnBehalfGroup");
		$groupComplaint = new group("groupComplaint");
		$groupComplaintYes = new group("groupComplaintYes");

		$complaintDetails = new group("complaintDetails");
		$complaintDetails->setBorder(false);
		$addSAPEmailYes = new group("addSAPEmailYes");
		$addSAPEmailYes->setBorder(false);
		$complaintDetailsStageTwo = new group("complaintDetailsStageTwo");

		$orderDetailsMulti = new multiplegroup("orderDetailsMulti");
		$orderDetailsMulti->setTitle("Scapa Order Number");
		$orderDetailsMulti->setNextAction("complaint");
		$orderDetailsMulti->setAnchorRef("orderDetailsMultiAnch");
		$orderDetailsMulti->setTable("complaintMultiples");
		$orderDetailsMulti->setForeignKey("complaintId");
		$orderDetails = new group("orderDetails");

		$scapaInvoiceYesGroup = new multiplegroup("scapaInvoiceYesGroup");
		$scapaInvoiceYesGroup->setTitle("Scapa Invoice Number and Date");
		$scapaInvoiceYesGroup->setNextAction("complaint");
		$scapaInvoiceYesGroup->setAnchorRef("scapaInvoiceYesGroupAnch");
		$scapaInvoiceYesGroup->setTable("scapaInvoiceNumberDate");
		$scapaInvoiceYesGroup->setForeignKey("complaintId");

		$intercoGroup = new group("intercoGroup");
		$intercoGroupYes = new multiplegroup("intercoGroupYes");
		$intercoGroupYes->setTitle("Interco Orders");
		$intercoGroupYes->setNextAction("complaint");
		$intercoGroupYes->setAnchorRef("scapaInvoiceYesGroupAnch");
		$intercoGroupYes->setTable("scapaIntercoOrder");
		$intercoGroupYes->setForeignKey("complaintId");

		$intercoMaterialGroup = new group("intercoMaterialGroup");
		$intercoMaterialGroup->setBorder(false);

		$materialGroupGroup = new multiplegroup("materialGroupGroup");
		$materialGroupGroup->setTitle("Material Group");
		$materialGroupGroup->setNextAction("complaint");
		$materialGroupGroup->setAnchorRef("materialGroupGroupAnch");
		$materialGroupGroup->setTable("materialGroup");
		$materialGroupGroup->setForeignKey("complaintId");

		$awaitingDimensionsNo = new group("awaitingDimensionsNo");
		$awaitingDimensionsNo->setBorder(false);
		$intercoColourGroup = new group("intercoColourGroup");


		$sapGroup = new multiplegroup("sapGroup");
		$sapGroup->setTitle("Sap Item Number");
		$sapGroup->setNextAction("complaint");
		$sapGroup->setAnchorRef("sapGroupAnch");
		$sapGroup->setTable("sapItemNumber");
		$sapGroup->setForeignKey("complaintId");


		$sapNextGroup = new group("sapNextGroup");
		$awaitingQuantityNo = new group("awaitingQuantityNo");
		$despatchGroup = new group("despatchGroup");
		$despatchGroup->setBorder(false);
		$factoredProductGroup = new group("factoredProductGroup");
		$factoredProductYes = new group("factoredProductYes");
		$awaitingBatchNumberNo = new group("awaitingBatchNumberNo");
		$awaitingBatchNumberNo->setBorder(false);
		$sitesGroup = new group("sitesGroup");
		$creditGroup = new group("creditGroup");
		$creditGroup->setBorder(false);
		$sampleGroup = new group("sampleGroup");
		$sampleGroup->setBorder(false);
		$sampleReceivedYes = new group("sampleReceivedYes");
		$sampleReceivedYes->setBorder(false);
		$lineStoppageGroup = new group("lineStoppageGroup");
		$lineStoppageYes = new group("lineStoppageYes");
		$actionsGroup = new group("actionsGroup");
		$actionsGroup->setBorder(false);

		/*$ccComplaintGroup = new multiplegroup("ccComplaintGroup");
		$ccComplaintGroup->setTitle("Select someone to CC the below message to");
		$ccComplaintGroup->setNextAction("complaint");
		$ccComplaintGroup->setAnchorRef("copy_to");
		$ccComplaintGroup->setTable("ccGroup");
		$ccComplaintGroup->setForeignKey("complaintId");
		$ccComplaintGroup->setBorder(false);*/

		$actionsGroup2 = new group("actionsGroup2");
		$actionsGroup2->setBorder(false);

		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);

		if(isset($_REQUEST["printAll"]) || (isset($_REQUEST["print"]) && $_REQUEST["status"] == "complaint")  ){//this means we are coming from the print function defined on homepage

			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '"  . $_REQUEST['complaint']."' LIMIT 1");

			$fields2 = mysql_fetch_array($dataset);
			if($fields2){
				foreach ($fields2 as $key => $value)
				{
					if($value){
						if(!strtotime($value) && $value != "0000-00-00"){
							$savedFields[$key] = $value;
						}else if(strtotime($value) && $value != "0000-00-00"){//if it is a date field then chenge the layout
							$savedFields[$key] = page::transformDateForPHP($value);
						}

					}
				}
			}

			$showID = new textbox("showID");
			$showID->setValue($_REQUEST['complaint']);
			$showID->setRowTitle("complaint_id");
			$showID->setGroup("initiation");
			$showID->setDataType("string");
			$showID->setLength(30);
			$showID->setRequired(false);
			$showID->setTable("complaint");
			$initiation->add($showID);

			$showSAP = new textbox("showSAP");
			$showSAP->setValue($fields2["sapCustomerNumber"]);
			$showSAP->setRowTitle("SAP_customer_number");
			$showSAP->setGroup("initiation");
			$showSAP->setDataType("string");
			$showSAP->setLength(50);
			$showSAP->setRequired(false);
			$showSAP->setTable("complaint");
			$initiation->add($showSAP);

			$showSAPName = new textbox("showSAPName");
			$showSAPName->setValue($fields2["sapName"]);
			$showSAPName->setRowTitle("SAP_customer_name");
			$showSAPName->setGroup("initiation");
			$showSAPName->setDataType("string");
			$showSAPName->setLength(50);
			$showSAPName->setRequired(false);
			$showSAPName->setTable("complaint");
			$initiation->add($showSAPName);

			$showOpenDate = new textbox("showOpenDate");
			if($fields2["openDate"] != "0000-00-00")
			$showOpenDate->setValue(page::transformDateForPHP($fields2["openDate"]));
			$showOpenDate->setRowTitle("open_date");
			$showOpenDate->setGroup("initiation");
			$showOpenDate->setDataType("string");
			$showOpenDate->setLength(50);
			$showOpenDate->setRequired(false);
			$showOpenDate->setTable("complaint");
			$initiation->add($showOpenDate);
		}

		$id = new textbox("id");
		if(isset($savedFields["id"]))
		$id->setValue($savedFields["id"]);
		$id->setTable("complaint");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
		$status->setValue($savedFields["status"]);
		$status->setTable("complaint");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("complaint");
		$initiation->add($status);

		$openDate = new textbox("openDate");
		if(isset($savedFields["openDate"]))
		$openDate->setValue($savedFields["openDate"]);
		$openDate->setTable("complaint");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$complaintLocation = new radio("complaintLocation");
		$complaintLocation->setTable("complaint");
		$complaintLocation->setLength(20);
		$complaintLocation->setArraySource(array(
		array('value' => 'european', 'display' => 'European'),
		array('value' => 'american', 'display' => 'American'),
		array('value' => 'malaysian', 'display' => 'Malaysian')
		));
		if(isset($savedFields["complaintLocation"]))
		$complaintLocation->setValue($savedFields["complaintLocation"]);
		$complaintLocation->setVisible(false);
		$complaintLocation->setIgnore(false);
		$complaintLocation->setDataType("string");
		$initiation->add($complaintLocation);

		$typeOfComplaint = new dropdown("typeOfComplaint");
		$typeOfComplaint->setGroup("typeOfComplaintGroup");
		$typeOfComplaint->setDataType("string");
		$typeOfComplaint->setXMLSource("./apps/complaints/xml/complaintType.xml");
		$typeOfComplaint->setRowTitle("complaint_type");
		$typeOfComplaint->setRequired(false);
		//		$typeOfComplaint->setLabel("Complaint Type Details");
		$typeOfComplaint->setTable("complaint");
		$typeOfComplaint->setVisible(false);
		$typeOfComplaint->setHelpId(8199);
		if(isset($savedFields["typeOfComplaint"]))
		$typeOfComplaint->setValue($savedFields["typeOfComplaint"]);
		else $typeOfComplaint->setValue("customer_complaint");
		$typeOfComplaintGroup->add($typeOfComplaint);

		$submitOnBehalf = new radio("submitOnBehalf");
		$submitOnBehalf->setGroup("initiation");
		$submitOnBehalf->setDataType("string");
		$submitOnBehalf->setLength(5);
		$submitOnBehalf->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$submitOnBehalf->setRowTitle("submit_on_behalf_of_someone");
		//		$submitOnBehalf->setLabel("Complaint Creator Details");
		$submitOnBehalf->setRequired(true);
		if(isset($savedFields["submitOnBehalf"]))
		$submitOnBehalf->setValue($savedFields["submitOnBehalf"]);
		else $submitOnBehalf->setValue("no");
		$submitOnBehalf->setTable("complaint");
		$submitOnBehalf->setHelpId(8000);

		// Dependency
		$behalfOfGroup = new dependency();
		$behalfOfGroup->addRule(new rule('initiation', 'submitOnBehalf', 'yes'));
		$behalfOfGroup->setGroup('submitOnBehalfGroup');
		$behalfOfGroup->setShow(true);

		$submitOnBehalf->addControllingDependency($behalfOfGroup);
		$initiation->add($submitOnBehalf);

		$internalSalesName = new autocomplete("internalSalesName");
		if(isset($savedFields["internalSalesName"]))
		$internalSalesName->setValue($savedFields["internalSalesName"]);
		$internalSalesName->setGroup("submitOnBehalfGroup");
		$internalSalesName->setDataType("string");
		$internalSalesName->setLength(30);
		$internalSalesName->setUrl("/apps/complaints/ajax/internalSalesName?");
		$internalSalesName->setRowTitle("internal_sales_name");
		$internalSalesName->setRequired(true);
		$internalSalesName->setTable("complaint");
		$internalSalesName->setHelpId(8100);
		$submitOnBehalfGroup->add($internalSalesName);

		$salesOffice = new dropdown("salesOffice");
		if(isset($savedFields["salesOffice"]))
		$salesOffice->setValue($savedFields["salesOffice"]);
		$salesOffice->setGroup("submitOnBehalfGroup");
		$salesOffice->setDataType("string");
		$salesOffice->setLength(30);
		$salesOffice->setXMLSource("./apps/complaints/xml/sites.xml");
		$salesOffice->setRowTitle("sales_office");
		$salesOffice->setRequired(false);
		$salesOffice->setVisible(false);
		$salesOffice->setTable("complaint");
		$salesOffice->setHelpId(8178);
		$submitOnBehalfGroup->add($salesOffice);


		$groupAComplaint = new radio("groupAComplaint");
		$groupAComplaint->setGroup("groupComplaint");
		$groupAComplaint->setDataType("string");
		$groupAComplaint->setLength(5);
		$groupAComplaint->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$groupAComplaint->setRowTitle("group_with_another_complaint");
		//		$groupAComplaint->setLabel("Group A Complaint");
		$groupAComplaint->setRequired(true);
		$groupAComplaint->setTable("complaint");
		if(isset($savedFields["groupAComplaint"]))
		$groupAComplaint->setValue($savedFields["groupAComplaint"]);
		else $groupAComplaint->setValue("No");
		$groupAComplaint->setHelpId(8188);

		// Dependency
		$groupComplaintDependency = new dependency();
		$groupComplaintDependency->addRule(new rule('groupComplaint', 'groupAComplaint', 'Yes'));
		$groupComplaintDependency->setGroup('groupComplaintYes');
		$groupComplaintDependency->setShow(true);

		$groupAComplaint->addControllingDependency($groupComplaintDependency);
		$groupComplaint->add($groupAComplaint);

		$groupedComplaintId = new autocomplete("groupedComplaintId");
		if(isset($savedFields["groupedComplaintId"]))
		$groupedComplaintId->setValue($savedFields["groupedComplaintId"]);
		$groupedComplaintId->setGroup("groupComplaintYes");
		$groupedComplaintId->setDataType("string");
		$groupedComplaintId->setLength(30);
		$groupedComplaintId->setUrl("/apps/complaints/ajax/groupComplaint?");
		$groupedComplaintId->setRowTitle("grouped_complaint_id");
		$groupedComplaintId->setRequired(true);
		$groupedComplaintId->setTable("complaint");
		$groupedComplaintId->setHelpId(8159);
		$groupComplaintYes->add($groupedComplaintId);


		$howErrorDetected = new dropdown("howErrorDetected");
		$howErrorDetected->setGroup("complaintDetails");
		$howErrorDetected->setDataType("string");
		$howErrorDetected->setLength(50);
		$howErrorDetected->setTranslate(true);
		$howErrorDetected->setErrorMessage("dropdown_error");
		$howErrorDetected->setRowTitle("how_error_detected");
		$howErrorDetected->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'customer' AND field = 'howErrorDetected' ORDER BY `details` ASC");
		$howErrorDetected->setRequired(true);
		//		$howErrorDetected->setLabel("Complaint Details");
		if(isset($savedFields["howErrorDetected"]))
		$howErrorDetected->setValue($savedFields["howErrorDetected"]);
		else $howErrorDetected->setValue("Incident");
		$howErrorDetected->setTable("complaint");
		$howErrorDetected->setHelpId(8001);

		$howErrorDetected_dependency = new dependency();
		$howErrorDetected_dependency->addRule(new rule('complaintDetails', 'howErrorDetected', 'internal_review'));
		$howErrorDetected_dependency->setGroup('complaintDetails');
		$howErrorDetected_dependency->setShow(false);
		$complaintDetails->add($howErrorDetected);

		$businessUnit = new dropdown("businessUnit");
		if(isset($savedFields["businessUnit"]))
		$businessUnit->setValue($savedFields["businessUnit"]);
		$businessUnit->setGroup("complaintDetails");
		$businessUnit->setDataType("string");
		$businessUnit->setLength(50);
		$businessUnit->setRowTitle("business_unit");
		$businessUnit->setErrorMessage("dropdown_error");
		$businessUnit->setRequired(false);
		$businessUnit->setXMLSource("./apps/complaints/xml/businessUnit.xml");
		$businessUnit->setRequired(true);
		$businessUnit->setTable("complaint");
		$businessUnit->setHelpId(8101);
		$complaintDetails->add($businessUnit);

		//		$scapaSalesName = new autocomplete("scapaSalesName");
		//		if(isset($savedFields["scapaSalesName"]))
		//			$scapaSalesName->setValue($savedFields["scapaSalesName"]);
		//		$scapaSalesName->setGroup("complaintDetails");
		//		$scapaSalesName->setDataType("string");
		//		$scapaSalesName->setLength(30);
		//		$scapaSalesName->setRowTitle("scapa_sales_name");
		//		$scapaSalesName->setRequired(false);
		//		$scapaSalesName->setUrl("/apps/complaints/ajax/scapaSalesName?");
		//		$scapaSalesName->setTable("complaint");
		//		$scapaSalesName->setHelpId(81011);
		//		$complaintDetails->add($scapaSalesName);

		$customerComplaintRef = new textbox("customerComplaintRef");
		if(isset($savedFields["customerComplaintRef"]))
		$customerComplaintRef->setValue($savedFields["customerComplaintRef"]);
		$customerComplaintRef->setGroup("complaintDetails");
		$customerComplaintRef->setDataType("string");
		$customerComplaintRef->setLength(30);
		$customerComplaintRef->setRowTitle("customer_complaint_ref");
		$customerComplaintRef->setRequired(false);
		$customerComplaintRef->setTable("complaint");
		$customerComplaintRef->setHelpId(8002);
		$complaintDetails->add($customerComplaintRef);

		$customerComplaintDate = new calendar("customerComplaintDate");
		if(isset($savedFields["customerComplaintDate"]))
		$customerComplaintDate->setValue($savedFields["customerComplaintDate"]);
		$customerComplaintDate->setGroup("complaintDetails");
		$customerComplaintDate->setDataType("date");
		$customerComplaintDate->setLength(30);
		$customerComplaintDate->setRowTitle("customer_complaint_date");
		$customerComplaintDate->setRequired(true);
		$customerComplaintDate->setErrorMessage("textbox_date_error_future");
		$customerComplaintDate->setTable("complaint");
		$customerComplaintDate->setHelpId(8003);
		$complaintDetails->add($customerComplaintDate);


		$sapCustomerNumber = new autocomplete("sapCustomerNumber");
		if(isset($savedFields["sapCustomerNumber"]))
		$sapCustomerNumber->setValue($savedFields["sapCustomerNumber"]);
		$sapCustomerNumber->setGroup("complaintDetails");
		$sapCustomerNumber->setDataType("string");
		$sapCustomerNumber->setRowTitle("sap_customer_number");
		$sapCustomerNumber->setRequired(true);
		$sapCustomerNumber->setErrorMessage("sap_error");
		$sapCustomerNumber->setUrl("/apps/complaints/ajax/sap?");
		//$sapCustomerNumber->setOnChange("update_sap_customer_email();");
		//$sapCustomerNumber->setValidateQuery("SAP", "customer", "name");
		$sapCustomerNumber->setTable("complaint");
		$sapCustomerNumber->setHelpId(8004);
		$complaintDetails->add($sapCustomerNumber);



		//		$existingClientSapNumber = new autocomplete("existingClientSapNumber");
		//		$existingClientSapNumber->setTable("customer");
		//		$existingClientSapNumber->setDataType("number");
		//		$existingClientSapNumber->setLength(6);
		//		$existingClientSapNumber->setUrl('/apps/ccr/ajax/sap?');
		//		$existingClientSapNumber->setRowTitle("sap_account_number");
		//		$existingClientSapNumber->setRequired(true);
		//		$existingClientSapNumber->setLabel("Existing Scapa Customer");
		//		$existingClientSapNumber->setHelpId(1);
		//		$existingClientSapNumber->setIgnore(true);
		//		$existingClientSapNumber->setValidateQuery("SAP", "customer", "id");
		//		$client->add($existingClientSapNumber);



		$addSAPEmailAddress = new radio("addSAPEmailAddress");
		$addSAPEmailAddress->setGroup("complaintDetails");
		$addSAPEmailAddress->setDataType("string");
		$addSAPEmailAddress->setLength(5);
		$addSAPEmailAddress->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$addSAPEmailAddress->setRowTitle("add_update_email_address");
		$addSAPEmailAddress->setRequired(true);
		$addSAPEmailAddress->setTable("complaint");
		if(isset($savedFields["addSAPEmailAddress"]))
		$addSAPEmailAddress->setValue($savedFields["addSAPEmailAddress"]);
		else $addSAPEmailAddress->setValue("no");
		$addSAPEmailAddress->setHelpId(8475);

		// Dependency
		$addSAPEmailDependency = new dependency();
		$addSAPEmailDependency->addRule(new rule('complaintDetails', 'addSAPEmailAddress', 'yes'));
		$addSAPEmailDependency->setGroup('addSAPEmailYes');
		$addSAPEmailDependency->setShow(true);

		$addSAPEmailAddress->addControllingDependency($addSAPEmailDependency);
		$complaintDetails->add($addSAPEmailAddress);

		$newSAPEmailAddress = new textbox("newSAPEmailAddress");
		if(isset($savedFields["newSAPEmailAddress"]))
		$newSAPEmailAddress->setValue($savedFields["newSAPEmailAddress"]);
		$newSAPEmailAddress->setGroup("addSAPEmailYes");
		$newSAPEmailAddress->setDataType("string");
		$newSAPEmailAddress->setLength(255);
		$newSAPEmailAddress->setHelpId(8475);
		$newSAPEmailAddress->setRowTitle("new_sap_email_address");
		$newSAPEmailAddress->setVisible(true);
		$newSAPEmailAddress->setTable("complaint");
		$addSAPEmailYes->add($newSAPEmailAddress);

		$sapName = new textbox("sapName");
		if(isset($savedFields["sapName"]))
		$sapName->setValue($savedFields["sapName"]);
		$sapName->setGroup("complaintDetailsStageTwo");
		$sapName->setDataType("string");
		$sapName->setLength(255);
		$sapName->setRowTitle("sap_customer_name");
		$sapName->setRequired(true);
		$sapName->setVisible(false);
		$sapName->setTable("complaint");
		$complaintDetailsStageTwo->add($sapName);

		$externalSalesName = new textbox("externalSalesName");
		if(isset($savedFields["externalSalesName"]))
		$externalSalesName->setValue($savedFields["externalSalesName"]);
		$externalSalesName->setGroup("complaintDetailsStageTwo");
		$externalSalesName->setDataType("string");
		$externalSalesName->setLength(255);
		$externalSalesName->setRowTitle("external_sales_name");
		$externalSalesName->setRequired(true);
		$externalSalesName->setVisible(false);
		$externalSalesName->setTable("complaint");
		$complaintDetailsStageTwo->add($externalSalesName);

		$attachment = new attachment("attachment");
		//if(isset($savedFields["attachment"]))
		//$attachment->setValue($savedFields["attachment"]);
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("complaint");
		$attachment->setAnchorRef("attachment");
		$complaintDetailsStageTwo->add($attachment);

		$category = new dropdown("category");
		if(isset($savedFields["category"]))
		$category->setValue($savedFields["category"]);
		$category->setGroup("complaintDetailsStageTwo");
		$category->setDataType("string");
		$category->setLength(255);
		$category->setTranslate(true);
		$category->setRowTitle("apparent_category");
		$category->setErrorMessage("dropdown_error");
		$category->setRequired(true);
		//$category->setXMLSource("./apps/complaints/xml/category.xml");
		$category->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'customer' AND field = 'category' ORDER BY `details` ASC");
		$category->setTable("complaint");
		$category->setHelpId(8005);
		$complaintDetailsStageTwo->add($category);

		$g8d = new radio("g8d");
		$g8d->setGroup("complaintDetailsStageTwo");
		$g8d->setDataType("string");
		$g8d->setLength(5);
		$g8d->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$g8d->setRowTitle("full_8d_required");
		$g8d->setRequired(true);
		$g8d->setTable("complaint");
		if(isset($savedFields["g8d"]))
		$g8d->setValue($savedFields["g8d"]);
		else $g8d->setValue("no");
		$g8d->setHelpId(8006);
		$complaintDetailsStageTwo->add($g8d);

		// Change fields for Ordering in NA
		/*if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getIsUSA())
		{
		if(!isset($savedFields["0|scapaOrderNumber"]))
		{
		$scapaOrderNumber = new textbox("scapaOrderNumber");
		$scapaOrderNumber->setDataType("text");
		$scapaOrderNumber->setLength(255);
		$scapaOrderNumber->setRequired(true);
		$scapaOrderNumber->setErrorMessage("field_error");
		$scapaOrderNumber->setRowTitle("scapa_order_number");
		$scapaOrderNumber->setTable("scapaOrderNumber");
		$scapaOrderNumber->setHelpId(8007);
		$orderDetailsMulti->add($scapaOrderNumber);

		$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
		$scapaInvoiceNumber->setGroup("orderDetailsMulti");
		$scapaInvoiceNumber->setDataType("textMinLength");
		$scapaInvoiceNumber->setMinLength(4);
		$scapaInvoiceNumber->setErrorMessage("field_error");
		$scapaInvoiceNumber->setRowTitle("scapa_invoice_number");
		$scapaInvoiceNumber->setRequired(true);
		$scapaInvoiceNumber->setTable("scapaOrderNumber");
		$scapaInvoiceNumber->setHelpId(8022);
		$orderDetailsMulti->add($scapaInvoiceNumber);

		$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		$scapaInvoiceDate->setGroup("orderDetailsMulti");
		$scapaInvoiceDate->setDataType("date");
		$scapaInvoiceDate->setLength(255);
		$scapaInvoiceDate->setErrorMessage("textbox_date_error");
		$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
		$scapaInvoiceDate->setRequired(true);
		$scapaInvoiceDate->setTable("scapaOrderNumber");
		$scapaInvoiceDate->setHelpId(8010);
		$orderDetailsMulti->add($scapaInvoiceDate);

		$productDescription = new textarea("productDescription");
		$productDescription->setGroup("orderDetailsMulti");
		$productDescription->setDataType("text");
		$productDescription->setRowTitle("material_description");
		$productDescription->setRequired(false);
		$productDescription->setTable("scapaOrderNumber");
		$productDescription->setHelpId(8016);
		$orderDetailsMulti->add($productDescription);

		$sapItemNumber = new textbox("sapItemNumber");
		$sapItemNumber->setGroup("orderDetailsMulti");
		$sapItemNumber->setDataType("textMinLength");
		$sapItemNumber->setErrorMessage("field_error_sap_item");
		$sapItemNumber->setMinLength(3);
		$sapItemNumber->setRowTitle("sap_item_number");
		$sapItemNumber->setRequired(true);
		$sapItemNumber->setTable("scapaOrderNumber");
		$sapItemNumber->setHelpId(80222);
		$orderDetailsMulti->add($sapItemNumber);

		$quantityUnderComplaint = new measurement("quantityUnderComplaint");
		$quantityUnderComplaint->setGroup("orderDetailsMulti");
		$quantityUnderComplaint->setDataType("string");
		$quantityUnderComplaint->setErrorMessage("field_error");
		$quantityUnderComplaint->setLength(10);
		$quantityUnderComplaint->setXMLSource("./apps/complaints/xml/uom.xml");
		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
		$quantityUnderComplaint->setRequired(true);
		$quantityUnderComplaint->setTable("complaint");
		$quantityUnderComplaint->setHelpId(8026);
		$orderDetailsMulti->add($quantityUnderComplaint);

		$complaintValue = new measurement("complaintValue");
		$complaintValue->setGroup("orderDetailsMulti");
		$complaintValue->setDataType("string");
		$complaintValue->setErrorMessage("field_error");
		$complaintValue->setLength(10);
		$complaintValue->setRowTitle("complaint_value");
		$complaintValue->setRequired(true);
		$complaintValue->setTable("complaint");
		$complaintValue->setXMLSource("./apps/complaints/xml/currency.xml");
		$complaintValue->setHelpId(8027);
		$orderDetailsMulti->add($complaintValue);

		$batchNumber = new textbox("batchNumber");
		$batchNumber->setGroup("orderDetailsMulti");
		$batchNumber->setDataType("string");
		$batchNumber->setRowTitle("batch_number");
		$batchNumber->setRequired(false);
		$batchNumber->setVisible(true);
		$batchNumber->setTable("complaint");
		$batchNumber->setHelpId(8029);
		$orderDetailsMulti->add($batchNumber);
		}
		else
		{
		$this->form->groupsToExclude[] = "orderDetailsMulti";
		$i=0;
		$endList = false;
		do{
		if(!isset($savedFields[$i."|scapaOrderNumber"])){
		$maxList = $i;
		$endList = true;
		}
		$i++;
		}while(!$endList);
		for($i=0; $i<$maxList; $i++){
		if($i==0){//first will always be set
		$scapaOrderNumber = new textbox("scapaOrderNumber");
		if(isset($savedFields["0|scapaOrderNumber"]))
		$scapaOrderNumber->setValue($savedFields["0|scapaOrderNumber"]);
		$scapaOrderNumber->setDataType("text");
		$scapaOrderNumber->setLength(255);
		$scapaOrderNumber->setErrorMessage("field_error");
		$scapaOrderNumber->setRequired(true);
		$scapaOrderNumber->setRowTitle("scapa_order_number");
		$scapaOrderNumber->setTable("scapaOrderNumber");
		$scapaOrderNumber->setHelpId(8007);
		$orderDetailsMulti->add($scapaOrderNumber);
		}else{
		$orderDetailsMulti->addRowCustom($savedFields[$i."|scapaOrderNumber"]);
		}
		}
		}
		}
		else
		{*/
		if(!isset($savedFields["0|scapaOrderNumber"]))
		{
			$scapaOrderNumber = new textbox("scapaOrderNumber");
			$scapaOrderNumber->setDataType("text");
			$scapaOrderNumber->setLength(255);
			$scapaOrderNumber->setRequired(true);
			$scapaOrderNumber->setErrorMessage("field_error");
			$scapaOrderNumber->setRowTitle("scapa_order_number");
			$scapaOrderNumber->setTable("scapaOrderNumber");
			$scapaOrderNumber->setHelpId(8007);
			$orderDetailsMulti->add($scapaOrderNumber);
		}else{
			$this->form->groupsToExclude[] = "orderDetailsMulti";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|scapaOrderNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$scapaOrderNumber = new textbox("scapaOrderNumber");
					if(isset($savedFields["0|scapaOrderNumber"]))
					$scapaOrderNumber->setValue($savedFields["0|scapaOrderNumber"]);
					$scapaOrderNumber->setDataType("text");
					$scapaOrderNumber->setLength(255);
					$scapaOrderNumber->setErrorMessage("field_error");
					$scapaOrderNumber->setRequired(true);
					$scapaOrderNumber->setRowTitle("scapa_order_number");
					$scapaOrderNumber->setTable("scapaOrderNumber");
					$scapaOrderNumber->setHelpId(8007);
					$orderDetailsMulti->add($scapaOrderNumber);
				}else{
					$orderDetailsMulti->addRowCustom($savedFields[$i."|scapaOrderNumber"]);
				}
			}
		}
		/*}*/

		$awaitingInvoice = new radio("awaitingInvoice");
		$awaitingInvoice->setGroup("orderDetails");
		$awaitingInvoice->setDataType("string");
		$awaitingInvoice->setLength(5);
		$awaitingInvoice->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$awaitingInvoice->setRowTitle("awaiting_customer_information");
		$awaitingInvoice->setRequired(true);
		$awaitingInvoice->setTable("complaint");
		if(isset($savedFields["awaitingInvoice"]))
		$awaitingInvoice->setValue($savedFields["awaitingInvoice"]);
		else $awaitingInvoice->setValue("no");

		$awaitingInvoice->setHelpId(8008);

		// Dependency
		$scapaInvoiceGroup = new dependency();
		$scapaInvoiceGroup->addRule(new rule('orderDetails', 'awaitingInvoice', 'no'));
		$scapaInvoiceGroup->setGroup('scapaInvoiceYesGroup');
		$scapaInvoiceGroup->setShow(true);

		$awaitingInvoice->addControllingDependency($scapaInvoiceGroup);
		$orderDetails->add($awaitingInvoice);

		/*
		$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
		if(isset($savedFields["scapaInvoiceNumber"]))
		$scapaInvoiceNumber->setValue($savedFields["scapaInvoiceNumber"]);
		$scapaInvoiceNumber->setDataType("textMinLength");
		$scapaInvoiceNumber->setMinLength(6);
		$scapaInvoiceNumber->setRequired(true);
		$scapaInvoiceNumber->setRowTitle("scapa_invoice_number");
		$scapaInvoiceNumber->setTable("scapaInvoiceNumberDate");
		$scapaInvoiceNumber->setHelpId(8009);
		//$scapaInvoiceNumber->setValidateQuery("SAP", "material_group", "key");
		$scapaInvoiceYesGroup->add($scapaInvoiceNumber);
		*/

		if(!isset($savedFields["0|scapaInvoiceNumber"])){//the first one will always need to be set if its saved

			$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
			if(isset($savedFields["0|scapaInvoiceNumber"]))
			$scapaInvoiceNumber->setValue($savedFields["0|scapaInvoiceNumber"]);
			$scapaInvoiceNumber->setGroup("scapaInvoiceYesGroup");
			$scapaInvoiceNumber->setDataType("textMinLength");
			$scapaInvoiceNumber->setMinLength(4);
			$scapaInvoiceNumber->setErrorMessage("field_error");
			$scapaInvoiceNumber->setRowTitle("scapa_invoice_number");
			$scapaInvoiceNumber->setRequired(true);
			$scapaInvoiceNumber->setTable("scapaInvoiceNumberDate");
			$scapaInvoiceNumber->setHelpId(8022);
			$scapaInvoiceYesGroup->add($scapaInvoiceNumber);

			$scapaInvoiceDate = new textbox("scapaInvoiceDate");
			if(isset($savedFields["0|scapaInvoiceDate"]))
			$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
			$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
			$scapaInvoiceDate->setDataType("date");
			$scapaInvoiceDate->setLength(255);
			$scapaInvoiceDate->setErrorMessage("textbox_date_error");
			$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
			$scapaInvoiceDate->setRequired(true);
			$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
			$scapaInvoiceDate->setHelpId(8010);
			$scapaInvoiceYesGroup->add($scapaInvoiceDate);

		}else{
			$this->form->groupsToExclude[] = "scapaInvoiceYesGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|scapaInvoiceNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
					if(isset($savedFields["0|scapaInvoiceNumber"]))
					$scapaInvoiceNumber->setValue($savedFields["0|scapaInvoiceNumber"]);
					$scapaInvoiceNumber->setGroup("scapaInvoiceYesGroup");
					$scapaInvoiceNumber->setDataType("textMinLength");
					$scapaInvoiceNumber->setMinLength(4);
					$scapaInvoiceNumber->setErrorMessage("field_error");
					$scapaInvoiceNumber->setRowTitle("scapa_invoice_number");
					$scapaInvoiceNumber->setRequired(true);
					$scapaInvoiceNumber->setTable("scapaInvoiceNumberDate");
					$scapaInvoiceNumber->setHelpId(8022);
					$scapaInvoiceYesGroup->add($scapaInvoiceNumber);

					$scapaInvoiceDate = new textbox("scapaInvoiceDate");
					if(isset($savedFields["0|scapaInvoiceDate"]))
					$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
					$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
					$scapaInvoiceDate->setDataType("date");
					$scapaInvoiceDate->setLength(255);
					$scapaInvoiceDate->setErrorMessage("textbox_date_error");
					$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
					$scapaInvoiceDate->setRequired(true);
					$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
					$scapaInvoiceDate->setHelpId(8022);
					$scapaInvoiceYesGroup->add($scapaInvoiceDate);
				}else{
					$customArr = array();
					$customArr[] = $savedFields[$i."|scapaInvoiceNumber"];
					$customArr[] = $savedFields[$i."|scapaInvoiceDate"];
					$scapaInvoiceYesGroup->addRowCustomMultiple($customArr);
					//$scapaInvoiceYesGroup->addRowCustom($savedFields[$i."|scapaInvoiceDate"]);
				}
			}
		}

		/*
		$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		if(isset($savedFields["scapaInvoiceDate"]))
		$scapaInvoiceDate->setValue($savedFields["scapaInvoiceDate"]);
		$scapaInvoiceDate->setDataType("date");
		$scapaInvoiceDate->setLength(255);
		$scapaInvoiceDate->setRequired(true);
		$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
		$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
		$scapaInvoiceDate->setHelpId(8010);
		//$scapaInvoiceDate->setValidateQuery("SAP", "material_group", "key");
		$scapaInvoiceYesGroup->add($scapaInvoiceDate);
		*/
		/*
		if(!isset($savedFields["0|scapaInvoiceDate"])){//the first one will always need to be set if its saved
		//echo "HERE";exit;
		$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		if(isset($savedFields["0|scapaInvoiceDate"]))
		$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
		$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
		$scapaInvoiceDate->setDataType("date");
		$scapaInvoiceDate->setLength(255);
		$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
		$scapaInvoiceDate->setRequired(true);
		$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
		$scapaInvoiceDate->setHelpId(8010);
		$scapaInvoiceYesGroup->add($scapaInvoiceDate);
		}else{
		$this->form->groupsToExclude[] = "scapaInvoiceYesGroup";
		$i=0;
		$endList = false;
		do{
		if(!isset($savedFields[$i."|scapaInvoiceDate"])){
		$maxList = $i;
		$endList = true;
		}
		$i++;
		}while(!$endList);
		for($i=0; $i<$maxList; $i++){
		if($i==0){//first will always be set
		$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		if(isset($savedFields["0|scapaInvoiceDate"]))
		$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
		$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
		$scapaInvoiceDate->setDataType("date");
		$scapaInvoiceDate->setLength(255);
		$scapaInvoiceDate->setRowTitle("scapa_invoice_date");
		$scapaInvoiceDate->setRequired(true);
		$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
		$scapaInvoiceDate->setHelpId(8022);
		$scapaInvoiceYesGroup->add($scapaInvoiceDate);
		}else{
		$scapaInvoiceYesGroup->addRowCustom($savedFields[$i."|scapaInvoiceDate"]);
		}
		}
		}
		*/
		$interco = new radio("interco");
		$interco->setGroup("intercoGroup");
		$interco->setDataType("string");
		$interco->setLength(5);
		$interco->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$interco->setRowTitle("interco");
		$interco->setRequired(true);
		//		$interco->setLabel("Interco");
		$interco->setTable("complaint");
		if(isset($savedFields["interco"]))
		$interco->setValue($savedFields["interco"]);
		else $interco->setValue("no");
		$interco->setHelpId(8011);

		// Dependency
		$interco_dependency = new dependency();
		$interco_dependency->addRule(new rule('intercoGroup', 'interco', 'yes'));
		$interco_dependency->setGroup('intercoGroupYes');
		$interco_dependency->setShow(true);

		$interco->addControllingDependency($interco_dependency);
		$intercoGroup->add($interco);

		/*
		$intercoOrderNumber = new textbox("intercoOrderNumber");
		if(isset($savedFields["intercoOrderNumber"]))
		$intercoOrderNumber->setValue($savedFields["intercoOrderNumber"]);
		$intercoOrderNumber->setGroup("intercoGroupYes");
		$intercoOrderNumber->setDataType("string");
		$intercoOrderNumber->setLength(30);
		$intercoOrderNumber->setRowTitle("interco_order_number");
		$intercoOrderNumber->setRequired(true);
		$intercoOrderNumber->setTable("scapaIntercoOrder");
		$intercoOrderNumber->setHelpId(8012);
		$intercoGroupYes->add($intercoOrderNumber);
		$intercoInvoiceDate = new textbox("intercoInvoiceDate");
		if(isset($savedFields["intercoInvoiceDate"]))
		$intercoInvoiceDate->setValue($savedFields["intercoInvoiceDate"]);
		$intercoInvoiceDate->setGroup("intercoGroupYes");
		$intercoInvoiceDate->setDataType("date");
		$intercoInvoiceDate->setLength(30);
		$intercoInvoiceDate->setRowTitle("interco_invoice_date");
		$intercoInvoiceDate->setRequired(true);
		$intercoInvoiceDate->setTable("scapaIntercoOrder");
		$intercoInvoiceDate->setHelpId(8013);
		$intercoGroupYes->add($intercoInvoiceDate);
		*/



		if(!isset($savedFields["0|intercoOrderNumber"])){//the first one will always need to be set if its saved

			$intercoOrderNumber = new textbox("intercoOrderNumber");
			if(isset($savedFields["0|intercoOrderNumber"]))
			$intercoOrderNumber->setValue($savedFields["0|intercoOrderNumber"]);
			$intercoOrderNumber->setGroup("intercoGroupYes");
			$intercoOrderNumber->setDataType("string");
			$intercoOrderNumber->setLength(30);
			$intercoOrderNumber->setErrorMessage("field_error");
			$intercoOrderNumber->setRowTitle("interco_order_number");
			$intercoOrderNumber->setRequired(true);
			$intercoOrderNumber->setTable("scapaIntercoOrder");
			$intercoOrderNumber->setHelpId(8012);
			$intercoGroupYes->add($intercoOrderNumber);

			$intercoInvoiceDate = new textbox("intercoInvoiceDate");
			if(isset($savedFields["0|intercoInvoiceDate"]))
			$intercoInvoiceDate->setValue($savedFields["0|intercoInvoiceDate"]);
			$intercoInvoiceDate->setGroup("intercoGroupYes");
			$intercoInvoiceDate->setDataType("date");
			$intercoInvoiceDate->setErrorMessage("textbox_date_error");
			$intercoInvoiceDate->setLength(30);
			$intercoInvoiceDate->setRowTitle("interco_invoice_date");
			$intercoInvoiceDate->setRequired(true);
			$intercoInvoiceDate->setTable("scapaIntercoOrder");
			$intercoInvoiceDate->setHelpId(8013);
			$intercoGroupYes->add($intercoInvoiceDate);

		}else{
			$this->form->groupsToExclude[] = "intercoGroupYes";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|intercoOrderNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$intercoOrderNumber = new textbox("intercoOrderNumber");
					if(isset($savedFields["0|intercoOrderNumber"]))
					$intercoOrderNumber->setValue($savedFields["0|intercoOrderNumber"]);
					$intercoOrderNumber->setGroup("intercoGroupYes");
					$intercoOrderNumber->setDataType("string");
					$intercoOrderNumber->setLength(30);
					$intercoOrderNumber->setErrorMessage("field_error");
					$intercoOrderNumber->setRowTitle("interco_order_number");
					$intercoOrderNumber->setRequired(true);
					$intercoOrderNumber->setTable("scapaIntercoOrder");
					$intercoOrderNumber->setHelpId(8012);
					$intercoGroupYes->add($intercoOrderNumber);

					$intercoInvoiceDate = new textbox("intercoInvoiceDate");
					if(isset($savedFields["0|intercoInvoiceDate"]))
					$intercoInvoiceDate->setValue($savedFields["0|intercoInvoiceDate"]);
					$intercoInvoiceDate->setGroup("intercoGroupYes");
					$intercoInvoiceDate->setDataType("date");
					$intercoInvoiceDate->setLength(30);
					$intercoInvoiceDate->setErrorMessage("textbox_date_error");
					$intercoInvoiceDate->setRowTitle("interco_invoice_date");
					$intercoInvoiceDate->setRequired(true);
					$intercoInvoiceDate->setTable("scapaIntercoOrder");
					$intercoInvoiceDate->setHelpId(8013);
					$intercoGroupYes->add($intercoInvoiceDate);
				}else{
					$customArr = array();
					$customArr[] = $savedFields[$i."|intercoOrderNumber"];
					$customArr[] = $savedFields[$i."|intercoInvoiceDate"];
					$intercoGroupYes->addRowCustomMultiple($customArr);
					//$scapaInvoiceYesGroup->addRowCustom($savedFields[$i."|scapaInvoiceDate"]);
				}
			}
		}



		/*$dateOfIntercoInvoice = new textbox("dateOfIntercoInvoice");
		$dateOfIntercoInvoice->setGroup("intercoGroupYes");
		$dateOfIntercoInvoice->setDataType("date");
		$dateOfIntercoInvoice->setLength(30);
		$dateOfIntercoInvoice->setRowTitle("date_of_interco_invoice");
		$dateOfIntercoInvoice->setRequired(false);
		$dateOfIntercoInvoice->setTable("complaint");
		$dateOfIntercoInvoice->setHelpId(8014);
		$intercoGroupYes->add($dateOfIntercoInvoice);*/


		if(!isset($savedFields["0|materialGroup"])){//the first one will always need to be set if its saved
			$materialGroup = new textbox("materialGroup");
			//if(isset($savedFields["materialGroup"]))
			//$materialGroup->setValue($savedFields["materialGroup"]);
			$materialGroup->setGroup("materialGroupGroup");
			$materialGroup->setDataType("string");
			$materialGroup->setLength(255);
			$materialGroup->setRowTitle("material_group");
			$materialGroup->setRequired(false);
			$materialGroup->setTable("materialGroup");
			$materialGroup->setHelpId(8015);
			$materialGroupGroup->add($materialGroup);
		}else{
			$this->form->groupsToExclude[] = "materialGroupGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroup"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$materialGroup = new textbox("materialGroup");
					if(isset($savedFields["0|materialGroup"]))
					$materialGroup->setValue($savedFields["0|materialGroup"]);
					$materialGroup->setGroup("materialGroupGroup");
					$materialGroup->setDataType("string");
					$materialGroup->setLength(255);
					$materialGroup->setRowTitle("material_group");
					$materialGroup->setRequired(false);
					$materialGroup->setTable("materialGroup");
					$materialGroup->setHelpId(8015);
					$materialGroupGroup->add($materialGroup);
				}else{
					$materialGroupGroup->addRowCustom($savedFields[$i."|materialGroup"]);
				}
			}
		}

		$productDescription = new textarea("productDescription");
		if(isset($savedFields["productDescription"]))
		$productDescription->setValue($savedFields["productDescription"]);
		$productDescription->setGroup("intercoMaterialGroup");
		$productDescription->setDataType("text");
		$productDescription->setRowTitle("material_description");
		$productDescription->setRequired(false);
		$productDescription->setTable("complaint");
		$productDescription->setHelpId(8016);
		$intercoMaterialGroup->add($productDescription);

		$awaitingDimensions = new radio("awaitingDimensions");
		$awaitingDimensions->setGroup("intercoMaterialGroup");
		$awaitingDimensions->setDataType("string");
		$awaitingDimensions->setLength(5);
		$awaitingDimensions->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$awaitingDimensions->setRowTitle("awaiting_dimensions");
		$awaitingDimensions->setRequired(true);
		$awaitingDimensions->setTable("complaint");
		if(isset($savedFields["awaitingDimensions"]))
		$awaitingDimensions->setValue($savedFields["awaitingDimensions"]);
		else $awaitingDimensions->setValue("no");

		$awaitingDimensions->setHelpId(8017);


		// Dependency
		$interco_material_dependency = new dependency();
		$interco_material_dependency->addRule(new rule('intercoMaterialGroup', 'awaitingDimensions', 'no'));
		$interco_material_dependency->setGroup('awaitingDimensionsNo');
		$interco_material_dependency->setShow(true);

		$awaitingDimensions->addControllingDependency($interco_material_dependency);
		$intercoMaterialGroup->add($awaitingDimensions);

		$dimensionThickness = new measurement("dimensionThickness");
		if(isset($savedFields["dimensionThickness_quantity"]) && isset($savedFields["dimensionThickness_measurement"])){
			//echo strval($savedFields["dimensionThickness_quantity"]);exit;
			$arr[0] = $savedFields["dimensionThickness_quantity"];
			$arr[1] = $savedFields["dimensionThickness_measurement"];
			$dimensionThickness->setValue($arr);
		}else $dimensionThickness->setMeasurement("mm");
		$dimensionThickness->setGroup("awaitingDimensionsNo");
		$dimensionThickness->setDataType("string");
		$dimensionThickness->setLength(5);
		$dimensionThickness->setXMLSource("./apps/complaints/xml/uom.xml");
		$dimensionThickness->setRowTitle("thickness");

		$dimensionThickness->setRequired(false);
		$dimensionThickness->setTable("complaint");
		$dimensionThickness->setExtTable("complaintExt");
		$dimensionThickness->setHelpId(8018);
		$awaitingDimensionsNo->add($dimensionThickness);

		$dimensionWidth = new measurement("dimensionWidth");
		if(isset($savedFields["dimensionWidth_quantity"]) && isset($savedFields["dimensionWidth_measurement"])){
			$arr[0] = $savedFields["dimensionWidth_quantity"];
			$arr[1] = $savedFields["dimensionWidth_measurement"];
			$dimensionWidth->setValue($arr);
		}else $dimensionWidth->setMeasurement("mm");
		$dimensionWidth->setGroup("awaitingDimensionsNo");
		$dimensionWidth->setDataType("string");
		$dimensionWidth->setLength(5);
		$dimensionWidth->setXMLSource("./apps/complaints/xml/uom.xml");
		$dimensionWidth->setRowTitle("width");

		$dimensionWidth->setRequired(false);
		$dimensionWidth->setTable("complaint");
		$dimensionWidth->setHelpId(8019);
		$awaitingDimensionsNo->add($dimensionWidth);

		$dimensionLength = new measurement("dimensionLength");
		if(isset($savedFields["dimensionLength_quantity"]) && isset($savedFields["dimensionLength_measurement"])){
			$arr[0] = $savedFields["dimensionLength_quantity"];
			$arr[1] = $savedFields["dimensionLength_measurement"];
			$dimensionLength->setValue($arr);
		}else $dimensionLength->setMeasurement("m");
		$dimensionLength->setGroup("awaitingDimensionsNo");
		$dimensionLength->setDataType("string");
		$dimensionLength->setLength(5);
		$dimensionLength->setXMLSource("./apps/complaints/xml/uom.xml");
		$dimensionLength->setRowTitle("length");
		$dimensionLength->setRequired(false);

		$dimensionLength->setTable("complaint");
		$dimensionLength->setHelpId(8020);
		$awaitingDimensionsNo->add($dimensionLength);

		//UP TO HERE ALLY
		$colour = new textbox("colour");
		if(isset($savedFields["colour"]))
		$colour->setValue($savedFields["colour"]);
		$colour->setGroup("intercoColourGroup");
		$colour->setDataType("string");
		$colour->setRowTitle("colour");
		$colour->setRequired(false);
		$colour->setTable("complaint");
		$colour->setHelpId(8021);
		$intercoColourGroup->add($colour);


		if(!isset($savedFields["0|sapItemNumber"])){//the first one will always need to be set if its saved
			//echo "HERE";exit;
			$sapItemNumber = new textbox("sapItemNumber");
			if(isset($savedFields["0|sapItemNumber"]))
			$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
			$sapItemNumber->setGroup("sapGroup");
			$sapItemNumber->setDataType("textMinLength");
			$sapItemNumber->setErrorMessage("field_error_sap_item");
			$sapItemNumber->setMinLength(3);
			$sapItemNumber->setRowTitle("sap_item_number");
			$sapItemNumber->setRequired(true);
			$sapItemNumber->setTable("sapItemNumber");
			$sapItemNumber->setHelpId(8022);
			$sapGroup->add($sapItemNumber);
		}else{
			$this->form->groupsToExclude[] = "sapGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|sapItemNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$sapItemNumber = new textbox("sapItemNumber");
					if(isset($savedFields["0|sapItemNumber"]))
					$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
					$sapItemNumber->setGroup("sapGroup");
					$sapItemNumber->setDataType("textMinLength");
					$sapItemNumber->setErrorMessage("field_error_sap_item");
					$sapItemNumber->setMinLength(3);
					$sapItemNumber->setRowTitle("sap_item_number");
					$sapItemNumber->setRequired(true);
					$sapItemNumber->setTable("sapItemNumber");
					$sapItemNumber->setHelpId(8022);
					$sapGroup->add($sapItemNumber);
				}else{
					$sapGroup->addRowCustom($savedFields[$i."|sapItemNumber"]);
				}
			}
		}

		$factoredProduct = new radio("factoredProduct");
		$factoredProduct->setGroup("factoredProductGroup");
		$factoredProduct->setDataType("string");
		$factoredProduct->setLength(5);
		$factoredProduct->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$factoredProduct->setRowTitle("factored_product");
		$factoredProduct->setRequired(true);
		$factoredProduct->setTable("complaint");
		if(isset($savedFields["factoredProduct"]))
		$factoredProduct->setValue($savedFields["factoredProduct"]);
		else $factoredProduct->setValue("No");
		$factoredProduct->setHelpId(8023);
		//$sapNextGroup->add($factoredProduct);


		// Dependency
		$factoredProduct_dependency = new dependency();
		$factoredProduct_dependency->addRule(new rule('factoredProductGroup', 'factoredProduct', 'Yes'));
		$factoredProduct_dependency->setGroup('factoredProductYes');
		$factoredProduct_dependency->setShow(true);

		$factoredProduct->addControllingDependency($factoredProduct_dependency);
		$factoredProductGroup->add($factoredProduct);

		$productSupplierName = new textbox("productSupplierName");
		if(isset($savedFields["productSupplierName"]))
		$productSupplierName->setValue($savedFields["productSupplierName"]);
		$productSupplierName->setGroup("factoredProductYes");
		$productSupplierName->setDataType("string");
		$productSupplierName->setRowTitle("product_supplier_name");
		$productSupplierName->setRequired(false);
		$productSupplierName->setVisible(true);
		$productSupplierName->setTable("complaint");
		$productSupplierName->setHelpId(8047);
		$factoredProductYes->add($productSupplierName);
		/*
		$awaitingBatchNumber = new radio("awaitingBatchNumber");
		$awaitingBatchNumber->setGroup("despatchGroup");
		$awaitingBatchNumber->setDataType("string");
		$awaitingBatchNumber->setLength(5);
		$awaitingBatchNumber->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No'),
		array('value' => 'Na', 'display' => 'N/A')
		));
		$awaitingBatchNumber->setRowTitle("awaiting_batch_number");
		$awaitingBatchNumber->setRequired(true);

		if(isset($savedFields["awaitingBatchNumber"]))
		$awaitingBatchNumber->setValue($savedFields["awaitingBatchNumber"]);
		else $awaitingBatchNumber->setValue("No");

		$awaitingBatchNumber->setTable("complaint");
		$awaitingBatchNumber->setHelpId(8028);

		// Dependency
		$awaitingBatchNumber_dependency = new dependency();
		$awaitingBatchNumber_dependency->addRule(new rule('despatchGroup', 'awaitingBatchNumber', 'No'));
		$awaitingBatchNumber_dependency->setGroup('awaitingBatchNumberNo');
		$awaitingBatchNumber_dependency->setShow(true);

		$awaitingBatchNumber->addControllingDependency($awaitingBatchNumber_dependency);
		$despatchGroup->add($awaitingBatchNumber);

		$batchNumber = new textbox("batchNumber");
		if(isset($savedFields["batchNumber"]))
		$batchNumber->setValue($savedFields["batchNumber"]);
		$batchNumber->setGroup("awaitingBatchNumberNo");
		$batchNumber->setDataType("string");
		$batchNumber->setRowTitle("batch_number");
		$batchNumber->setRequired(false);
		$batchNumber->setVisible(true);
		$batchNumber->setTable("complaint");
		$batchNumber->setHelpId(8029);
		$awaitingBatchNumberNo->add($batchNumber);
		*/

		$customerItemNumber = new textbox("customerItemNumber");
		if(isset($savedFields["customerItemNumber"]))
		$customerItemNumber->setValue($savedFields["customerItemNumber"]);
		$customerItemNumber->setGroup("sapNextGroup");
		$customerItemNumber->setDataType("string");
		$customerItemNumber->setRowTitle("customer_item_number");
		$customerItemNumber->setRequired(false);
		$customerItemNumber->setTable("complaint");
		$customerItemNumber->setHelpId(8024);
		$sapNextGroup->add($customerItemNumber);

		// Not Found could be awaitingDimensions
		$awaitingQuantityUnderComplaint = new radio("awaitingQuantityUnderComplaint");
		$awaitingQuantityUnderComplaint->setGroup("sapNextGroup");
		$awaitingQuantityUnderComplaint->setDataType("string");
		$awaitingQuantityUnderComplaint->setLength(5);
		$awaitingQuantityUnderComplaint->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$awaitingQuantityUnderComplaint->setRowTitle("awaiting_quantity");
		$awaitingQuantityUnderComplaint->setRequired(true);
		$awaitingQuantityUnderComplaint->setTable("complaint");
		if(isset($savedFields["awaitingQuantityUnderComplaint"]))
		$awaitingQuantityUnderComplaint->setValue($savedFields["awaitingQuantityUnderComplaint"]);
		else $awaitingQuantityUnderComplaint->setValue("no");
		$awaitingQuantityUnderComplaint->setHelpId(8025);


		// Dependency
		$awaitingQuantity_dependency = new dependency();
		$awaitingQuantity_dependency->addRule(new rule('sapNextGroup', 'awaitingQuantityUnderComplaint', 'no'));
		$awaitingQuantity_dependency->setGroup('awaitingQuantityNo');
		$awaitingQuantity_dependency->setShow(true);

		$awaitingQuantityUnderComplaint->addControllingDependency($awaitingQuantity_dependency);
		$sapNextGroup->add($awaitingQuantityUnderComplaint);


		$quantityUnderComplaint = new measurement("quantityUnderComplaint");
		if(isset($savedFields["quantityUnderComplaint_quantity"]) && isset($savedFields["quantityUnderComplaint_measurement"])){
			$arr[0] = $savedFields["quantityUnderComplaint_quantity"];
			$arr[1] = $savedFields["quantityUnderComplaint_measurement"];
			$quantityUnderComplaint->setValue($arr);
		}else $quantityUnderComplaint->setMeasurement("roll");
		$quantityUnderComplaint->setGroup("awaitingQuantity");
		$quantityUnderComplaint->setDataType("string");
		$quantityUnderComplaint->setErrorMessage("field_error");
		$quantityUnderComplaint->setLength(10);
		$quantityUnderComplaint->setXMLSource("./apps/complaints/xml/uom.xml");
		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
		$quantityUnderComplaint->setRequired(true);
		$quantityUnderComplaint->setTable("complaint");

		$quantityUnderComplaint->setHelpId(8026);
		$awaitingQuantityNo->add($quantityUnderComplaint);


		$complaintValue = new measurement("complaintValue");
		if(isset($savedFields["complaintValue_quantity"]) && isset($savedFields["complaintValue_measurement"])){
			$arr[0] = $savedFields["complaintValue_quantity"];
			$arr[1] = $savedFields["complaintValue_measurement"];
			$complaintValue->setValue($arr);
		}else $complaintValue->setMeasurement("EUR");
		$complaintValue->setGroup("awaitingQuantityNo");
		$complaintValue->setDataType("string");
		$complaintValue->setErrorMessage("field_error");
		$complaintValue->setLength(10);
		$complaintValue->setRowTitle("complaint_value");
		$complaintValue->setRequired(true);
		$complaintValue->setTable("complaint");
		$complaintValue->setXMLSource("./apps/complaints/xml/currency.xml");

		$complaintValue->setHelpId(8027);
		$awaitingQuantityNo->add($complaintValue);

		$complaintValueComment = new textarea("complaintValueComment");
		$complaintValueComment->setGroup("awaitingQuantityNo");
		$complaintValueComment->setDataType("text");
		$complaintValueComment->setTable("complaint");
		$complaintValueComment->setHelpId(802712);
		$complaintValueComment->setRowTitle("comment");
		$awaitingQuantityNo->add($complaintValueComment);

		$gbpComplaintValue = new measurement("gbpComplaintValue");
		if(isset($savedFields["gbpComplaintValue"]))
		$gbpComplaintValue->setValue($savedFields["gbpComplaintValue"]);
		$gbpComplaintValue->setGroup("despatchGroup");
		$gbpComplaintValue->setDataType("string");
		$gbpComplaintValue->setLength(10);
		$gbpComplaintValue->setRowTitle("complaint_value");
		$gbpComplaintValue->setRequired(false);
		$gbpComplaintValue->setVisible(false);
		$gbpComplaintValue->setTable("complaint");
		$gbpComplaintValue->setXMLSource("./apps/complaints/xml/currency.xml");
		$gbpComplaintValue->setMeasurement("GBP");
		$gbpComplaintValue->setHelpId(8027);
		$despatchGroup->add($gbpComplaintValue);




		$awaitingBatchNumber = new radio("awaitingBatchNumber");
		$awaitingBatchNumber->setGroup("despatchGroup");
		$awaitingBatchNumber->setDataType("string");
		$awaitingBatchNumber->setLength(5);
		$awaitingBatchNumber->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No'),
		array('value' => 'Na', 'display' => 'N/A')
		));
		$awaitingBatchNumber->setRowTitle("awaiting_batch_number");
		$awaitingBatchNumber->setRequired(true);

		if(isset($savedFields["awaitingBatchNumber"]))
		$awaitingBatchNumber->setValue($savedFields["awaitingBatchNumber"]);
		else $awaitingBatchNumber->setValue("No");

		$awaitingBatchNumber->setTable("complaint");
		$awaitingBatchNumber->setHelpId(8028);


		// Dependency
		$awaitingBatchNumber_dependency = new dependency();
		$awaitingBatchNumber_dependency->addRule(new rule('despatchGroup', 'awaitingBatchNumber', 'No'));
		$awaitingBatchNumber_dependency->setGroup('awaitingBatchNumberNo');
		$awaitingBatchNumber_dependency->setShow(true);

		$awaitingBatchNumber->addControllingDependency($awaitingBatchNumber_dependency);
		$despatchGroup->add($awaitingBatchNumber);

		$batchNumber = new textbox("batchNumber");
		if(isset($savedFields["batchNumber"]))
		$batchNumber->setValue($savedFields["batchNumber"]);
		$batchNumber->setGroup("awaitingBatchNumberNo");
		$batchNumber->setDataType("text");
		$batchNumber->setRowTitle("batch_number");
		$batchNumber->setRequired(false);
		$batchNumber->setVisible(true);
		$batchNumber->setTable("complaint");
		$batchNumber->setHelpId(8029);
		$awaitingBatchNumberNo->add($batchNumber);


		$despatchSite = new dropdown("despatchSite");
		if(isset($savedFields["despatchSite"]))
		$despatchSite->setValue($savedFields["despatchSite"]);
		$despatchSite->setGroup("sitesGroup");
		$despatchSite->setDataType("string");
		$despatchSite->setErrorMessage("dropdown_error");
		$despatchSite->setLength(50);
		$despatchSite->setRowTitle("despatch_site");
		$despatchSite->setRequired(true);
		$despatchSite->setXMLSource("./apps/complaints/xml/sites.xml");
		$despatchSite->setTable("complaint");
		$despatchSite->setHelpId(8030);
		$sitesGroup->add($despatchSite);

		$manufacturingSite = new dropdown("manufacturingSite");
		if(isset($savedFields["manufacturingSite"]))
		$manufacturingSite->setValue($savedFields["manufacturingSite"]);
		$manufacturingSite->setGroup("sitesGroup");
		$manufacturingSite->setDataType("string");
		$manufacturingSite->setLength(50);
		$manufacturingSite->setErrorMessage("dropdown_error");
		$manufacturingSite->setRowTitle("manufacturing_site");
		$manufacturingSite->setRequired(true);
		$manufacturingSite->setXMLSource("./apps/complaints/xml/sites.xml");
		$manufacturingSite->setTable("complaint");
		$manufacturingSite->setHelpId(8031);
		$sitesGroup->add($manufacturingSite);

		$siteAtOrigin = new dropdown("siteAtOrigin");
		if(isset($savedFields["siteAtOrigin"]))
		$siteAtOrigin->setValue($savedFields["siteAtOrigin"]);
		$siteAtOrigin->setGroup("sitesGroup");
		$siteAtOrigin->setDataType("string");
		$siteAtOrigin->setLength(50);
		$siteAtOrigin->setErrorMessage("dropdown_error");
		$siteAtOrigin->setRowTitle("origin_site_error");
		$siteAtOrigin->setRequired(true);
		$siteAtOrigin->setXMLSource("./apps/complaints/xml/sites.xml");
		$siteAtOrigin->setTable("complaint");
		$siteAtOrigin->setHelpId(8032);
		$sitesGroup->add($siteAtOrigin);

		$carrierName = new textbox("carrierName");
		if(isset($savedFields["carrierName"]))
		$carrierName->setValue($savedFields["carrierName"]);
		$carrierName->setGroup("sitesGroup");
		$carrierName->setDataType("string");
		$carrierName->setLength(255);
		$carrierName->setRowTitle("carrier_name");
		$carrierName->setRequired(false);
		$carrierName->setTable("complaint");
		$carrierName->setHelpId(8033);
		$sitesGroup->add($carrierName);


		$creditNoteRequested = new radio("creditNoteRequested");
		$creditNoteRequested->setGroup("creditGroup");
		$creditNoteRequested->setDataType("string");
		$creditNoteRequested->setLength(5);
		$creditNoteRequested->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$creditNoteRequested->setRowTitle("credit_note_requested");
		$creditNoteRequested->setRequired(true);
		$creditNoteRequested->setTable("complaint");

		if(isset($savedFields["creditNoteRequested"]))
		$creditNoteRequested->setValue($savedFields["creditNoteRequested"]);
		else $creditNoteRequested->setValue("YES");

		//		$creditNoteRequested->setLabel("Credit Details");
		$creditNoteRequested->setHelpId(8034);
		$creditGroup->add($creditNoteRequested);

		$problemDescription = new textarea("problemDescription");
		if(isset($savedFields["problemDescription"]))
		$problemDescription->setValue($savedFields["problemDescription"]);
		$problemDescription->setGroup("creditGroup");
		$problemDescription->setDataType("text");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setRequired(true);
		$problemDescription->setTable("complaint");
		$problemDescription->setHelpId(8035);
		$creditGroup->add($problemDescription);

		$severity = new radio("severity");
		$severity->setGroup("creditGroup");
		$severity->setDataType("string");
		$severity->setLength(5);
		$severity->setArraySource(array(
		array('value' => 'High', 'display' => 'High'),
		array('value' => 'Low', 'display' => 'Low')
		));
		$severity->setRowTitle("severity");
		$severity->setRequired(true);
		if(isset($savedFields["severity"]))
		$severity->setValue($savedFields["severity"]);
		else $severity->setValue("High");

		$severity->setTable("complaint");
		$severity->setHelpId(8134);
		$creditGroup->add($severity);


		/*$automotiveCovisint = new radio("automotiveCovisint");
		$automotiveCovisint->setGroup("creditGroup");
		$automotiveCovisint->setDataType("string");
		$automotiveCovisint->setLength(5);
		$automotiveCovisint->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$automotiveCovisint->setRowTitle("automotive_covisint");
		$automotiveCovisint->setRequired(true);
		$automotiveCovisint->setTable("complaint");
		$automotiveCovisint->setValue("no");
		$automotiveCovisint->setHelpId(8036);

		$automotiveCovisint = new radio("automotiveCovisint");
		$automotiveCovisint->setGroup("creditGroup");
		$automotiveCovisint->setDataType("string");
		$automotiveCovisint->setLength(5);
		$automotiveCovisint->setArraySource(array(
		array('value' => 'YES', 'display' => 'Yes'),
		array('value' => 'NO', 'display' => 'No')
		));
		$automotiveCovisint->setRowTitle("automotive_covisint");
		$automotiveCovisint->setRequired(true);
		$automotiveCovisint->setTable("complaint");
		$automotiveCovisint->setValue("no");
		$automotiveCovisint->setHelpId(8036);


		// Dependency
		$automotiveCovisint_dependency = new dependency();
		$automotiveCovisint_dependency->addRule(new rule('creditGroup', 'automotiveCovisint', 'yes'));
		$automotiveCovisint_dependency->setGroup('automotiveCovisintYes');
		$automotiveCovisint_dependency->setShow(true);

		$automotiveCovisint->addControllingDependency($automotiveCovisint_dependency);
		$creditGroup->add($automotiveCovisint);

		$covisintRef = new textbox("covisintRef");
		$covisintRef->setGroup("automotiveCovisintYes");
		$covisintRef->setDataType("string");
		$covisintRef->setLength(255);
		$covisintRef->setRowTitle("covisint_ref_number");
		$covisintRef->setRequired(false);
		$covisintRef->setTable("complaint");
		$covisintRef->setHelpId(8037);
		$automotiveCovisintYes->add($covisintRef);

		$covisintDate = new textbox("covisintDate");
		$covisintDate->setGroup("automotiveCovisintYes");
		$covisintDate->setDataType("date");
		$covisintDate->setLength(255);
		$covisintDate->setRowTitle("automotive_covisint_date");
		$covisintDate->setRequired(false);
		$covisintDate->setTable("complaint");
		$covisintDate->setHelpId(8038);
		$automotiveCovisintYes->add($covisintDate);*/


		$sampleReceived = new radio("sampleReceived");
		$sampleReceived->setGroup("sampleGroup");
		$sampleReceived->setDataType("string");
		$sampleReceived->setLength(5);
		$sampleReceived->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$sampleReceived->setRowTitle("sample_received");
		$sampleReceived->setRequired(true);
		$sampleReceived->setTable("complaint");
		if(isset($savedFields["sampleReceived"]))
		$sampleReceived->setValue($savedFields["sampleReceived"]);
		else $sampleReceived->setValue("No");

		$sampleReceived->setHelpId(8039);


		// Dependency
		$sampleReceived_dependency = new dependency();
		$sampleReceived_dependency->addRule(new rule('sampleGroup', 'sampleReceived', 'Yes'));
		$sampleReceived_dependency->setGroup('sampleReceivedYes');
		$sampleReceived_dependency->setShow(true);

		$sampleReceived->addControllingDependency($sampleReceived_dependency);
		$sampleGroup->add($sampleReceived);

		$sampleReceptionDate = new calendar("sampleReceptionDate");
		if(isset($savedFields["sampleReceptionDate"]))
		$sampleReceptionDate->setValue($savedFields["sampleReceptionDate"]);
		$sampleReceptionDate->setGroup("sampleReceivedYes");
		$sampleReceptionDate->setDataType("date");
		$sampleReceptionDate->setLength(255);
		$sampleReceptionDate->setRowTitle("reception_date");
		$sampleReceptionDate->setRequired(false);
		$sampleReceptionDate->setTable("complaint");
		$sampleReceptionDate->setHelpId(8040);
		$sampleReceivedYes->add($sampleReceptionDate);

		$sampleTransferred = new radio("sampleTransferred");
		$sampleTransferred->setGroup("sampleReceivedYes");
		$sampleTransferred->setDataType("string");
		$sampleTransferred->setLength(5);
		$sampleTransferred->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$sampleTransferred->setRowTitle("sample_transferred_to_po");
		$sampleTransferred->setRequired(true);
		$sampleTransferred->setTable("complaint");
		if(isset($savedFields["sampleTransferred"]))
		$sampleTransferred->setValue($savedFields["sampleTransferred"]);
		else $sampleTransferred->setValue("no");

		$sampleTransferred->setHelpId(8041);
		$sampleReceivedYes->add($sampleTransferred);

		$sampleDate = new calendar("sampleDate");
		if(isset($savedFields["sampleDate"]))
		$sampleDate->setValue($savedFields["sampleDate"]);
		$sampleDate->setGroup("sampleReceivedYes");
		$sampleDate->setDataType("date");
		$sampleDate->setLength(255);
		$sampleDate->setRowTitle("transferred_to_po_date");
		$sampleDate->setRequired(false);
		$sampleDate->setTable("complaint");
		$sampleDate->setHelpId(8042);
		$sampleReceivedYes->add($sampleDate);

		$lineStoppage = new radio("lineStoppage");
		$lineStoppage->setGroup("creditGroup");
		$lineStoppage->setDataType("string");
		$lineStoppage->setLength(5);
		$lineStoppage->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$lineStoppage->setRowTitle("line_stoppage");
		$lineStoppage->setRequired(true);
		if(isset($savedFields["lineStoppage"]))
		$lineStoppage->setValue($savedFields["lineStoppage"]);
		else $lineStoppage->setValue("No");
		$lineStoppage->setTable("complaint");
		$lineStoppage->setHelpId(8135);

		// Dependency
		$lineStoppage_dependency = new dependency();
		$lineStoppage_dependency->addRule(new rule('lineStoppageGroup', 'lineStoppage', 'Yes'));
		$lineStoppage_dependency->setGroup('lineStoppageYes');
		$lineStoppage_dependency->setShow(true);

		$lineStoppage->addControllingDependency($lineStoppage_dependency);
		$lineStoppageGroup->add($lineStoppage);

		$lineStoppageDetails = new textarea("lineStoppageDetails");
		if(isset($savedFields["lineStoppageDetails"]))
		$lineStoppageDetails->setValue($savedFields["lineStoppageDetails"]);
		$lineStoppageDetails->setGroup("lineStoppageYes");
		$lineStoppageDetails->setDataType("text");
		$lineStoppageDetails->setRowTitle("details");
		$lineStoppageDetails->setRequired(false);
		$lineStoppageDetails->setTable("complaint");
		$lineStoppageDetails->setHelpId(8198);
		$lineStoppageYes->add($lineStoppageDetails);


		$salesContainmentActions = new textarea("salesContainmentActions");
		if(isset($savedFields["salesContainmentActions"]))
		$salesContainmentActions->setValue($savedFields["salesContainmentActions"]);
		$salesContainmentActions->setGroup("actionsGroup");
		$salesContainmentActions->setDataType("text");
		$salesContainmentActions->setRowTitle("sales_containment_actions");
		$salesContainmentActions->setRequired(false);
		//		$salesContainmentActions->setLabel("Actions");
		$salesContainmentActions->setTable("complaint");
		$salesContainmentActions->setHelpId(8043);
		$actionsGroup->add($salesContainmentActions);

		$actionRequested = new textarea("actionRequested");
		if(isset($savedFields["actionRequested"]))
		$actionRequested->setValue($savedFields["actionRequested"]);
		$actionRequested->setGroup("actionsGroup");
		$actionRequested->setDataType("text");
		$actionRequested->setRowTitle("actions_requested_from_the_customer");
		$actionRequested->setRequired(false);
		$actionRequested->setTable("complaint");
		$actionRequested->setHelpId(8044);
		$actionsGroup->add($actionRequested);

		/*/////////////////////
		$delegateTo = new autocomplete("delegateTo");
		$delegateTo->setGroup("delegate");
		$delegateTo->setDataType("string");
		$delegateTo->setUrl("/apps/complaints/ajax/delegate?");
		$delegateTo->setRowTitle("delegate_to");
		$delegateTo->setRequired(true);
		$delegate->add($delegateTo);
		/*/////////////////////

		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1111);
		$processOwnerLink->setLink("http://scapanet/apps/complaints/data/po.xls");
		$processOwnerLink->setValue("{TRANSLATE:process_owner_matrix_europe}");
		$actionsGroup->add($processOwnerLink);

		$processOwnerLink2 = new textboxlink("processOwnerLink2");
		$processOwnerLink2->setRowTitle("process_owner_link");
		$processOwnerLink2->setHelpId(1111);
		$processOwnerLink2->setLink("http://scapanet/apps/complaints/data/process_owner_matrix_na.xls");
		$processOwnerLink2->setValue("{TRANSLATE:process_owner_matrix_na}");
		$actionsGroup->add($processOwnerLink2);

		$processOwner = new autocomplete("processOwner");
		if(isset($savedFields["processOwner"]))
		$processOwner->setValue($savedFields["processOwner"]);
		$processOwner->setGroup("actionsGroup");
		$processOwner->setDataType("string");
		$processOwner->setErrorMessage("user_not_found");
		$processOwner->setRowTitle("COMPLAINT_OWNER");
		$processOwner->setRequired(true);
		$processOwner->setUrl("/apps/complaints/ajax/newProcessOwner?");
		$processOwner->setTable("complaint");
		$processOwner->setHelpId(8145);
		$actionsGroup->add($processOwner);

		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
		$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("actionsGroup");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("CC_customer");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("complaint");
		$copy_to->setHelpId(8146);
		$actionsGroup->add($copy_to);

		/*////////////////////
		$processOwner = new dropdown("processOwner");
		$processOwner->setGroup("actionsGroup");
		$processOwner->setDataType("string");
		$processOwner->setRowTitle("chosen_complaint_owner");
		$processOwner->setRequired(false);
		$processOwner->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.NTLogon");
		$processOwner->setTable("complaint");
		$processOwner->clearData();
		$processOwner->setHelpId(8145);
		$actionsGroup->add($processOwner);
		////////////////////*/
		/*
		$copy_to = new dropdown("copy_to");
		$copy_to->setGroup("ccComplaintGroup");
		$copy_to->setDataType("string");
		$copy_to->setRowTitle("CC");
		$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		$copy_to->setRequired(false);
		//$copy_to->setUrl("/apps/complaints/ajax/copyTo?");
		$copy_to->setTable("ccGroup");
		$copy_to->setHelpId(8146);
		$ccComplaintGroup->add($copy_to);
		*/




		/*if(!isset($savedFields["0|copy_to"])){//the first one will always need to be set if its saved
		$copy_to = new autocomplete("copy_to");
		if(isset($savedFields["0|copy_to"]))
		$copy_to->setValue($savedFields["0|copy_to"]);
		$copy_to->setGroup("ccComplaintGroup");
		$copy_to->setDataType("string");
		$copy_to->setRowTitle("CC");
		$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		$copy_to->setRequired(false);
		$copy_to->setTable("ccGroup");
		$copy_to->setHelpId(8146);
		$ccComplaintGroup->add($copy_to);
		}else{
		$this->form->groupsToExclude[] = "ccComplaintGroup";
		$i=0;
		$endList = false;
		do{
		if(!isset($savedFields[$i."|copy_to"])){
		$maxList = $i;
		$endList = true;
		}
		$i++;
		}while(!$endList);
		for($i=0; $i<$maxList; $i++){
		if($i==0){//first will always be set
		$copy_to = new autocomplete("copy_to");
		if(isset($savedFields["0|copy_to"]))
		$copy_to->setValue($savedFields["0|copy_to"]);
		$copy_to->setGroup("ccComplaintGroup");
		$copy_to->setDataType("string");
		$copy_to->setRowTitle("CC");
		$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		$copy_to->setRequired(false);
		$copy_to->setTable("ccGroup");
		$copy_to->setHelpId(8146);
		$ccComplaintGroup->add($copy_to);
		}else{

		$copy_to = new autocomplete("copy_to");
		if(isset($savedFields[$i."|copy_to"]))
		$copy_to->setValue($savedFields[$i."|copy_to"]);
		$copy_to->setGroup("ccComplaintGroup");
		$copy_to->setDataType("string");
		$copy_to->setRowTitle("CC");
		$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		$copy_to->setRequired(false);
		$copy_to->setTable("ccGroup");
		$copy_to->setHelpId(8146);
		//$ccComplaintGroup->add($copy_to);


		$ccComplaintGroup->addRowCustom($savedFields[$i."|copy_to"]);
		}
		}
		}*/







		$email_text = new textarea("email_text");
		if(isset($savedFields["email_text"]))
		$email_text->setValue($savedFields["email_text"]);
		$email_text->setGroup("actionsGroup");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setRequired(false);
		$email_text->setTable("complaint");
		$email_text->setHelpId(8045);
		$actionsGroup2->add($email_text);


		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser->add($submit);







		$this->form->add($typeOfComplaintGroup);

		$this->form->add($initiation);
		$this->form->add($submitOnBehalfGroup);
		$this->form->add($groupComplaint);
		$this->form->add($groupComplaintYes);
		$this->form->add($complaintDetails);
		$this->form->add($addSAPEmailYes);
		$this->form->add($complaintDetailsStageTwo);
		$this->form->add($orderDetailsMulti);
		$this->form->add($orderDetails);
		$this->form->add($scapaInvoiceYesGroup);
		$this->form->add($intercoGroup);
		$this->form->add($intercoGroupYes);
		$this->form->add($materialGroupGroup);
		$this->form->add($intercoMaterialGroup);
		$this->form->add($awaitingDimensionsNo);
		$this->form->add($intercoColourGroup);
		$this->form->add($sapGroup);
		$this->form->add($factoredProductGroup);
		$this->form->add($factoredProductYes);
		$this->form->add($sapNextGroup);
		$this->form->add($awaitingQuantityNo);
		$this->form->add($despatchGroup);
		$this->form->add($awaitingBatchNumberNo);
		$this->form->add($sitesGroup);
		$this->form->add($creditGroup);
		//$this->form->add($automotiveCovisintYes);
		$this->form->add($sampleGroup);
		$this->form->add($sampleReceivedYes);
		$this->form->add($lineStoppageGroup);
		$this->form->add($lineStoppageYes);
		$this->form->add($actionsGroup);
		//$this->form->add($ccComplaintGroup);
		$this->form->add($actionsGroup2);
		$this->form->add($sendToUser);

	}

	public function defineSupplierForm()
	{
		$savedFields = array();

		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);


		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("complaint" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$sapGroup = new multiplegroup("sapGroup");
		$sapGroup->setTitle("Material Number");
		$sapGroup->setNextAction("complaint");
		$sapGroup->setAnchorRef("sapGroupAnch");
		$sapGroup->setTable("sapItemNumber");
		$sapGroup->setForeignKey("complaintId");

		$materialGroupGroup = new multiplegroup("materialGroupGroup");
		$materialGroupGroup->setTitle("Material Group");
		$materialGroupGroup->setNextAction("complaint");
		$materialGroupGroup->setAnchorRef("materialGroupGroupAnch");
		$materialGroupGroup->setTable("materialGroup");
		$materialGroupGroup->setForeignKey("complaintId");

		//		$scapaInvoiceYesGroup = new multiplegroup("scapaInvoiceYesGroup");
		//		$scapaInvoiceYesGroup->setTitle("Supplier Invoice Number and Date");
		//		$scapaInvoiceYesGroup->setNextAction("complaint");
		//		$scapaInvoiceYesGroup->setAnchorRef("scapaInvoiceYesGroupAnch");
		//		$scapaInvoiceYesGroup->setTable("scapaInvoiceNumberDate");
		//		$scapaInvoiceYesGroup->setForeignKey("complaintId");

		// not required but pulled in
		$orderDetailsMulti = new multiplegroup("orderDetailsMulti");
		$orderDetailsMulti->setTitle("Scapa Order Number");
		$orderDetailsMulti->setVisible(false);
		$orderDetailsMulti->setNextAction("complaint");
		$orderDetailsMulti->setAnchorRef("orderDetailsMultiAnch");
		$orderDetailsMulti->setTable("complaintMultiples");
		$orderDetailsMulti->setForeignKey("complaintId");

		// not required but pulled in
		$intercoGroupYes = new multiplegroup("intercoGroupYes");
		$intercoGroupYes->setTitle("Interco Orders");
		$intercoGroupYes->setNextAction("complaint");
		$intercoGroupYes->setVisible(false);
		$intercoGroupYes->setAnchorRef("scapaInvoiceYesGroupAnch");
		$intercoGroupYes->setTable("scapaIntercoOrder");
		$intercoGroupYes->setForeignKey("complaintId");

		//		$ccComplaintGroup = new multiplegroup("ccComplaintGroup");
		//		$ccComplaintGroup->setTitle("Select someone to CC the below message to");
		//		$ccComplaintGroup->setNextAction("complaint");
		//		$ccComplaintGroup->setAnchorRef("copy_to");
		//		$ccComplaintGroup->setTable("ccGroup");
		//		$ccComplaintGroup->setVisible(true);
		//		$ccComplaintGroup->setForeignKey("complaintId");
		//		$ccComplaintGroup->setBorder(false);

		$initiation = new group("initiation");
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
		$groupComplaint = new group("groupComplaint");
		$groupComplaintYes = new group("groupComplaintYes");
		$complaintDetails = new group("complaintDetails");
		$complaintDetails->setBorder(false);
		$addSAPEmailYes = new group("addSAPEmailYes");
		$addSAPEmailYes->setBorder(false);
		$complaintDetails2 = new group("complaintDetails2");
		$complaintDetails2->setBorder(false);
		$materialInvolvedGroup = new group("materialInvolvedGroup");
		$materialInvolvedYes = new group("materialInvolvedYes");
		$materialBlockedGroup = new group("materialBlockedGroup");
		$materialBlockedYes = new group("materialBlockedYes");
		$complaintDetails2 = new group("complaintDetails2");
		$sampleSentGroup = new group("sampleSentGroup");
		$sampleSentYes = new group("sampleSentYes");
		$materialDebitedGroup = new group("materialDebitedGroup");
		$materialDebitedYes = new group("materialDebitedYes");

		$actionsGroup2 = new group("actionsGroup2");
		$actionsGroup2->setBorder(false);

		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);

		$sendToUser2 = new group("sendToUser2");
		$sendToUser2->setBorder(false);

		//this means we are coming from the print function defined on homepage - WC
		if(isset($_REQUEST["printAll"]) || (isset($_REQUEST["print"]) && $_REQUEST["status"] == "complaint")  )
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '"  . $_REQUEST['complaint']."' LIMIT 1");
			$fields2 = mysql_fetch_array($dataset);

			$showSAPNumber = new textbox("showSAPNumber");
			$showSAPNumber->setValue($fields2["sapCustomerNumber"]);
			$showSAPNumber->setRowTitle("sap_supplier_number");
			$showSAPNumber->setGroup("initiation");
			$showSAPNumber->setDataType("string");
			$showSAPNumber->setRequired(false);
			$showSAPNumber->setTable("complaint");
			$initiation->add($showSAPNumber);

			$showSAPName = new textbox("showSAPName");
			$showSAPName->setValue($fields2["sapName"]);
			$showSAPName->setRowTitle("sap_supplier_name");
			$showSAPName->setGroup("initiation");
			$showSAPName->setDataType("string");
			$showSAPName->setRequired(false);
			$showSAPName->setTable("complaint");
			$initiation->add($showSAPName);

		}

		$id = new textbox("id");
		$id->setExtTable("complaintExt");
		$id->setVisible(false);
		$id->setDataType("string");
		$initiation->add($id);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
		$status->setValue($savedFields["status"]);
		$status->setTable("complaint");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("complaint");
		$initiation->add($status);

		$scapaContact = new textbox("scapaContact");
		$scapaContact->setExtTable("complaintExt");
		$scapaContact->setVisible(false);
		$scapaContact->setDataType("string");
		$initiation->add($scapaContact);

		$scapaTel = new textbox("scapaTel");
		$scapaTel->setExtTable("complaintExt");
		$scapaTel->setVisible(false);
		$scapaTel->setDataType("string");
		$initiation->add($scapaTel);

		$scapaSite = new textbox("scapaSite");
		$scapaSite->setExtTable("complaintExt");
		$scapaSite->setVisible(false);
		$scapaSite->setDataType("string");
		$initiation->add($scapaSite);

		$scapaEmail = new textbox("scapaEmail");
		$scapaEmail->setExtTable("complaintExt");
		$scapaEmail->setVisible(false);
		$scapaEmail->setDataType("string");
		$initiation->add($scapaEmail);

		$scapaStatus = new textbox("scapaStatus");
		if(isset($savedFields["scapaStatus"]))
		$status->setValue($savedFields["scapaStatus"]);
		$scapaStatus->setExtTable("complaintExt");
		$scapaStatus->setVisible(false);
		$scapaStatus->setDataType("number");
		$initiation->add($scapaStatus);

		$extStatus = new textbox("extStatus");
		if(isset($savedFields["extStatus"]))
		$extStatus->setValue($savedFields["extStatus"]);
		$extStatus->setExtTable("complaintExt");
		$extStatus->setVisible(false);
		$extStatus->setDataType("number");
		$initiation->add($extStatus);

		$added = new textbox("added");
		if(isset($savedFields["added"]))
		$added->setValue($savedFields["added"]);
		$added->setTable("complaint");
		$added->setExtTable("complaintExt");
		$added->setVisible(false);
		$added->setDataType("number");
		$initiation->add($added);

		$submitOnBehalf = new radio("submitOnBehalf");
		$submitOnBehalf->setGroup("initiation");
		$submitOnBehalf->setDataType("string");
		$submitOnBehalf->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$submitOnBehalf->setRowTitle("submit_on_behalf_of_someone");
		$submitOnBehalf->setRequired(false);
		$submitOnBehalf->setIgnore(true);
		$submitOnBehalf->setVisible(false);
		$submitOnBehalf->setValue("no");
		$submitOnBehalf->setTable("complaint");
		$initiation->add($submitOnBehalf);

		$openDate = new textbox("openDate");
		if(isset($savedFields["openDate"]))
		$openDate->setValue($savedFields["openDate"]);
		$openDate->setTable("complaint");
		$openDate->setExtTable("complaintExt");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setExtTable("complaintExt");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		// not required but pulled in
		$scapaOrderNumber = new textbox("scapaOrderNumber");
		$scapaOrderNumber->setDataType("text");
		$scapaOrderNumber->setLength(255);
		$scapaOrderNumber->setVisible(false);
		$scapaOrderNumber->setIgnore(true);
		$scapaOrderNumber->setRowTitle("scapa_order_number");
		$scapaOrderNumber->setTable("scapaOrderNumber");
		$orderDetailsMulti->add($scapaOrderNumber);

		// not required but pulled in
		$intercoOrderNumber = new textbox("intercoOrderNumber");
		$intercoOrderNumber->setGroup("intercoGroupYes");
		$intercoOrderNumber->setDataType("string");
		$intercoOrderNumber->setLength(30);
		$intercoOrderNumber->setRowTitle("interco_order_number");
		$intercoOrderNumber->setRequired(false);
		$intercoOrderNumber->setIgnore(true);
		$intercoOrderNumber->setTable("scapaIntercoOrder");
		$intercoOrderNumber->setHelpId(8012);
		$intercoGroupYes->add($intercoOrderNumber);

		//		 not required but pulled in
		//		$copy_to = new dropdown("copy_to");
		//		$copy_to->setGroup("ccComplaintGroup");
		//		$copy_to->setDataType("string");
		//		$copy_to->setRowTitle("CC");
		//		$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//		$copy_to->setRequired(false);
		//		$copy_to->setIgnore(true);
		//		$copy_to->setTable("ccGroup");
		//		$copy_to->setHelpId(8146);
		//		$ccComplaintGroup->add($copy_to);

		$complaintLocation = new radio("complaintLocation");
		$complaintLocation->setTable("complaint");
		$complaintLocation->setLength(20);
		$complaintLocation->setArraySource(array(
		array('value' => 'european', 'display' => 'European'),
		array('value' => 'american', 'display' => 'American'),
		array('value' => 'malaysian', 'display' => 'Malaysian')
		));
		if(isset($savedFields["complaintLocation"]))
		$complaintLocation->setValue($savedFields["complaintLocation"]);
		$complaintLocation->setVisible(false);
		$complaintLocation->setIgnore(false);
		$complaintLocation->setDataType("string");
		$initiation->add($complaintLocation);

		//		$g8d = new radio("g8d");
		//		$g8d->setGroup("complaintDetailsStageTwo");
		//		$g8d->setDataType("string");
		//		$g8d->setArraySource(array(
		//			array('value' => 'yes', 'display' => 'Yes'),
		//			array('value' => 'no', 'display' => 'No')
		//		));
		//		$g8d->setRequired(false);
		//		$g8d->setVisible(false);
		//		$g8d->setTable("complaint");
		//		$initiation->add($g8d);

		//on Stefan's design this field is called Registered by.
		$internalSalesName = new autocomplete("internalSalesName");
		if(isset($savedFields["internalSalesName"]))
		$internalSalesName->setValue($savedFields["internalSalesName"]);
		$internalSalesName->setGroup("initiation");
		$internalSalesName->setDataType("string");
		$internalSalesName->setLength(50);
		$internalSalesName->setUrl("/apps/complaints/ajax/internalSalesName?");
		$internalSalesName->setRowTitle("internal_sales_name");
		$internalSalesName->setRequired(true);
		$internalSalesName->setVisible(false);
		$internalSalesName->setTable("complaint");
		$internalSalesName->setHelpId(8100);
		$initiation->add($internalSalesName);

		$salesOffice = new dropdown("salesOffice");
		if(isset($savedFields["salesOffice"]))
		$salesOffice->setValue($savedFields["salesOffice"]);
		$salesOffice->setGroup("initiation");
		$salesOffice->setDataType("string");
		$salesOffice->setLength(30);
		$salesOffice->setXMLSource("./apps/complaints/xml/sites.xml");
		$salesOffice->setRowTitle("sales_office");
		$salesOffice->setRequired(false);
		$salesOffice->setVisible(false);
		$salesOffice->setTable("complaint");
		$salesOffice->setHelpId(8178);
		$initiation->add($salesOffice);

		$typeOfComplaint = new dropdown("typeOfComplaint");
		$typeOfComplaint->setGroup("typeOfComplaintGroup");
		$typeOfComplaint->setDataType("string");
		$typeOfComplaint->setXMLSource("./apps/complaints/xml/complaintType.xml");
		$typeOfComplaint->setRowTitle("complaint_type");
		$typeOfComplaint->setRequired(true);
		//		$typeOfComplaint->setLabel("Complaint Type Details");
		$typeOfComplaint->setTable("complaint");
		$typeOfComplaint->setVisible(false);
		$typeOfComplaint->setHelpId(8199);
		if(isset($savedFields["typeOfComplaint"]))
		$typeOfComplaint->setValue($savedFields["typeOfComplaint"]);
		else $typeOfComplaint->setValue("supplier_complaint");
		$typeOfComplaintGroup->add($typeOfComplaint);

		$customerComplaintDate = new calendar("customerComplaintDate");
		if(isset($savedFields["customerComplaintDate"]))
		$customerComplaintDate->setValue($savedFields["customerComplaintDate"]);
		$customerComplaintDate->setGroup("complaintDetails");
		$customerComplaintDate->setDataType("date");
		$customerComplaintDate->setLength(30);
		$customerComplaintDate->setRowTitle("scapa_complaint_date");
		$customerComplaintDate->setErrorMessage("textbox_date_error_future");
		$customerComplaintDate->setRequired(true);
		$customerComplaintDate->setTable("complaint");
		$customerComplaintDate->setExtTable("complaintExt");
		$customerComplaintDate->setHelpId(8003);
		$complaintDetails->add($customerComplaintDate);

		//		$customerComplaintRef = new textbox("customerComplaintRef");
		//		if(isset($savedFields["customerComplaintRef"]))
		//			$customerComplaintRef->setValue($savedFields["customerComplaintRef"]);
		//		$customerComplaintRef->setGroup("complaintDetails");
		//		$customerComplaintRef->setDataType("string");
		//		$customerComplaintRef->setLength(30);
		//		$customerComplaintRef->setRowTitle("scapa_complaint_ref");
		//		$customerComplaintRef->setRequired(false);
		//		$customerComplaintRef->setTable("complaint");
		//		$customerComplaintRef->setHelpId(8002);
		//		$complaintDetails->add($customerComplaintRef);

		$groupAComplaint = new radio("groupAComplaint");
		$groupAComplaint->setGroup("groupComplaint");
		$groupAComplaint->setDataType("string");
		$groupAComplaint->setLength(5);
		$groupAComplaint->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$groupAComplaint->setRowTitle("group_with_another_complaint");
		//		$groupAComplaint->setLabel("Group A Complaint");
		$groupAComplaint->setTable("complaint");
		if(isset($savedFields["groupAComplaint"]))
		$groupAComplaint->setValue($savedFields["groupAComplaint"]);
		else $groupAComplaint->setValue("No");
		$groupAComplaint->setHelpId(8188);

		// Dependency
		$groupComplaintDependency = new dependency();
		$groupComplaintDependency->addRule(new rule('groupComplaint', 'groupAComplaint', 'Yes'));
		$groupComplaintDependency->setGroup('groupComplaintYes');
		$groupComplaintDependency->setShow(true);

		$groupAComplaint->addControllingDependency($groupComplaintDependency);
		$groupComplaint->add($groupAComplaint);

		$groupedComplaintId = new autocomplete("groupedComplaintId");
		if(isset($savedFields["groupedComplaintId"]))
		$groupedComplaintId->setValue($savedFields["groupedComplaintId"]);
		$groupedComplaintId->setGroup("groupComplaintYes");
		$groupedComplaintId->setDataType("string");
		$groupedComplaintId->setLength(30);
		$groupedComplaintId->setUrl("/apps/complaints/ajax/groupComplaint?");
		$groupedComplaintId->setRowTitle("grouped_complaint_id");
		$groupedComplaintId->setRequired(true);
		$groupedComplaintId->setTable("complaint");
		$groupedComplaintId->setHelpId(8159);
		$groupComplaintYes->add($groupedComplaintId);

		$sp_siteConcerned = new dropdown("sp_siteConcerned");
		if(isset($savedFields["sp_siteConcerned"]))
		$sp_siteConcerned->setValue($savedFields["sp_siteConcerned"]);
		$sp_siteConcerned->setGroup("complaintDetails");
		$sp_siteConcerned->setDataType("string");
		$sp_siteConcerned->setLength(50);
		$sp_siteConcerned->setRowTitle("complaining_site");
		$sp_siteConcerned->setRequired(true);
		$sp_siteConcerned->setXMLSource("./apps/complaints/xml/sites.xml");
		$sp_siteConcerned->setTable("complaint");
		$sp_siteConcerned->setHelpId(81591);
		$sp_siteConcerned->setExtTable("complaintExt");
		//$sp_complainingSite->setHelpId(8032);
		$complaintDetails->add($sp_siteConcerned);

		$sp_buyer = new dropdown("sp_buyer");
		$sp_buyer->setGroup("complaintDetails");
		$sp_buyer->setDataType("string");
		$sp_buyer->setLength(50);
		$sp_buyer->setRowTitle("buyer");
		$sp_buyer->setRequired(true);
		$sp_buyer->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon WHERE permissions.permission = 'complaints_buyer' ORDER BY employee.firstName, employee.lastName ASC");
		if(isset($savedFields["sp_buyer"]))
		$sp_buyer->setValue($savedFields["sp_buyer"]);
		else $sp_buyer->setValue("Incident");
		$sp_buyer->setTable("complaint");
		$sp_buyer->setHelpId(81592);
		$sp_buyer->setExtTable("complaintExt");
		$complaintDetails->add($sp_buyer);


		// This is a duplication for supplier complaint to show this information
		$sapCustomerNumber = new textbox("sapCustomerNumber");
		if(isset($savedFields["sapCustomerNumber"]))
		$sapCustomerNumber->setValue($savedFields["sapCustomerNumber"]);
		$sapCustomerNumber->setGroup("complaintDetails");
		$sapCustomerNumber->setDataType("string");
		$sapCustomerNumber->setRowTitle("sap_customer_number");
		$sapCustomerNumber->setVisible(false);
		$sapCustomerNumber->setExtTable("complaintExt");
		//$sapCustomerNumber->setOnChange("update_sap_customer_email();");
		//$sapCustomerNumber->setValidateQuery("SAP", "customer", "name");
		$sapCustomerNumber->setTable("complaint");
		$sapCustomerNumber->setHelpId(8159);
		$complaintDetails->add($sapCustomerNumber);

		$complaintId = new textbox("complaintId");
		if(isset($savedFields["complaintId"]))
		$complaintId->setValue($savedFields["complaintId"]);
		$complaintId->setGroup("complaintDetails");
		$complaintId->setDataType("number");
		$complaintId->setVisible(false);
		$complaintId->setExtTable("complaintExt");
		$complaintId->setHelpId(8004);
		$complaintDetails->add($complaintId);

		// This is a duplication for supplier complaint to show this information
		$sapName = new textbox("sapName");
		if(isset($savedFields["sapName"]))
		$sapName->setValue($savedFields["sapName"]);
		$sapName->setGroup("complaintDetailsStageTwo");
		$sapName->setDataType("string");
		$sapName->setLength(255);
		$sapName->setRowTitle("sap_customer_name");
		$sapName->setVisible(false);
		$sapName->setTable("complaint");
		$sapName->setExtTable("complaintExt");
		$complaintDetails->add($sapName);

		$sp_sapSupplierNumber = new autocomplete("sp_sapSupplierNumber");
		if(isset($savedFields["sp_sapSupplierNumber"]))
		$sp_sapSupplierNumber->setValue($savedFields["sp_sapSupplierNumber"]);
		$sp_sapSupplierNumber->setGroup("complaintDetails");
		$sp_sapSupplierNumber->setDataType("string");
		$sp_sapSupplierNumber->setLength(30);
		$sp_sapSupplierNumber->setUrl("/apps/complaints/ajax/sapSupplierNo?");
		$sp_sapSupplierNumber->setRowTitle("sap_supplier_number");
		$sp_sapSupplierNumber->setRequired(true);
		//$sp_sapSupplierNumber->setOnBlur("setExternalSupplierEmail");
		$sp_sapSupplierNumber->setTable("complaint");
		$sp_sapSupplierNumber->setHelpId(81593);
		$complaintDetails->add($sp_sapSupplierNumber);

		$addSAPEmailAddress = new radio("addSAPEmailAddress");
		$addSAPEmailAddress->setGroup("complaintDetails");
		$addSAPEmailAddress->setDataType("string");
		$addSAPEmailAddress->setLength(5);
		$addSAPEmailAddress->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$addSAPEmailAddress->setRowTitle("add_update_email_address");
		$addSAPEmailAddress->setRequired(false);
		$addSAPEmailAddress->setTable("complaint");
		if(isset($savedFields["addSAPEmailAddress"]))
		$addSAPEmailAddress->setValue($savedFields["addSAPEmailAddress"]);
		else $addSAPEmailAddress->setValue("no");
		$addSAPEmailAddress->setHelpId(8475);

		// Dependency
		$addSAPEmailDependency = new dependency();
		$addSAPEmailDependency->addRule(new rule('complaintDetails', 'addSAPEmailAddress', 'yes'));
		$addSAPEmailDependency->setGroup('addSAPEmailYes');
		$addSAPEmailDependency->setShow(true);

		$addSAPEmailAddress->addControllingDependency($addSAPEmailDependency);
		$complaintDetails->add($addSAPEmailAddress);

		$newSAPEmailAddress = new textbox("newSAPEmailAddress");
		if(isset($savedFields["newSAPEmailAddress"]))
		$newSAPEmailAddress->setValue($savedFields["newSAPEmailAddress"]);
		$newSAPEmailAddress->setGroup("addSAPEmailYes");
		$newSAPEmailAddress->setDataType("string");
		$newSAPEmailAddress->setLength(255);
		$newSAPEmailAddress->setRowTitle("new_sap_supplier_email_address");
		$newSAPEmailAddress->setVisible(true);
		$newSAPEmailAddress->setTable("complaint");
		$addSAPEmailYes->add($newSAPEmailAddress);

		$sp_sapSupplierName = new textbox("sp_sapSupplierName");
		if(isset($savedFields["sp_sapSupplierName"]))
		$carrierName->setValue($savedFields["sp_sapSupplierName"]);
		$sp_sapSupplierName->setGroup("complaintDetails");
		$sp_sapSupplierName->setDataType("string");
		$sp_sapSupplierName->setLength(255);
		$sp_sapSupplierName->setRowTitle("sap_supplier_name");
		$sp_sapSupplierName->setRequired(false);
		$sp_sapSupplierName->setVisible(false);
		$sp_sapSupplierName->setTable("complaint");
		$sp_sapSupplierName->setHelpId(8033);
		$complaintDetails->add($sp_sapSupplierName);

		$externalSalesName = new textbox("externalSalesName");
		if(isset($savedFields["externalSalesName"]))
		$externalSalesName->setValue($savedFields["externalSalesName"]);
		$externalSalesName->setGroup("complaintDetails");
		$externalSalesName->setDataType("string");
		$externalSalesName->setLength(255);
		$externalSalesName->setRowTitle("external_sales_name");
		$externalSalesName->setRequired(false);
		$externalSalesName->setVisible(false);
		$externalSalesName->setTable("complaint");
		$complaintDetails->add($externalSalesName);

		$howErrorDetected = new dropdown("howErrorDetected");
		$howErrorDetected->setGroup("complaintDetails");
		$howErrorDetected->setDataType("string");
		$howErrorDetected->setLength(50);
		$howErrorDetected->setTranslate(true);
		$howErrorDetected->setRowTitle("how_error_detected");
		$howErrorDetected->setErrorMessage("dropdown_error");
		$howErrorDetected->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'supplier' AND field = 'howErrorDetected' ORDER BY `details` ASC");
		$howErrorDetected->setRequired(true);
		//		$howErrorDetected->setLabel("Complaint Details");
		if(isset($savedFields["howErrorDetected"]))
		$howErrorDetected->setValue($savedFields["howErrorDetected"]);
		else $howErrorDetected->setValue("Incident");
		$howErrorDetected->setTable("complaint");
		$howErrorDetected->setOnChange("update_supplier_complaint_error_field()");
		$howErrorDetected->setHelpId(8001);
		$complaintDetails2->add($howErrorDetected);


		$category = new dropdown("category");
		if(isset($savedFields["category"]))
		$category->setValue($savedFields["category"]);
		$category->setGroup("complaintDetails");
		$category->setDataType("string");
		$category->setLength(255);
		$category->setRowTitle("apparent_category");
		$category->setRequired(true);
		$category->setErrorMessage("dropdown_error");
		$category->setTranslate(true);
		$category->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'supplier' AND field = 'category' ORDER BY `details` ASC");
		$category->setTable("complaint");
		$category->setExtTable("complaintExt");
		$category->setHelpId(8005);
		$complaintDetails2->add($category);

		$attachment = new attachment("attachment");
		//if(isset($savedFields["attachment"]))
		//$attachment->setValue($savedFields["attachment"]);
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("complaint");
		$attachment->setAnchorRef("attachment");
		$complaintDetails2->add($attachment);


		$sp_internalRefNumber = new textbox("sp_internalRefNumber");
		if(isset($savedFields["sp_internalRefNumber"]))
		$sp_internalRefNumber->setValue($savedFields["sp_internalRefNumber"]);
		$sp_internalRefNumber->setGroup("complaintDetails");
		$sp_internalRefNumber->setDataType("string");
		$sp_internalRefNumber->setLength(255);
		$sp_internalRefNumber->setRowTitle("internal_ref_number");
		$sp_internalRefNumber->setRequired(false);
		$sp_internalRefNumber->setVisible(false);
		$sp_internalRefNumber->setTable("complaint");
		$complaintDetails2->add($sp_internalRefNumber);

		$sp_performedRequestedBy = new textbox("sp_performedRequestedBy");
		if(isset($savedFields["sp_performedRequestedBy"]))
		$sp_performedRequestedBy->setValue($savedFields["sp_performedRequestedBy"]);
		$sp_performedRequestedBy->setGroup("complaintDetails");
		$sp_performedRequestedBy->setDataType("string");
		$sp_performedRequestedBy->setLength(255);
		$sp_performedRequestedBy->setRowTitle("performed_requested_by");
		$sp_performedRequestedBy->setRequired(false);
		$sp_performedRequestedBy->setVisible(false);
		$sp_performedRequestedBy->setTable("complaint");
		$complaintDetails2->add($sp_performedRequestedBy);


		$sp_materialInvolved = new radio("sp_materialInvolved");
		$sp_materialInvolved->setGroup("materialInvolvedGroup");
		$sp_materialInvolved->setDataType("string");
		$sp_materialInvolved->setLength(5);
		$sp_materialInvolved->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$sp_materialInvolved->setRowTitle("material_involved");
		$sp_materialInvolved->setValue("Yes");
		$sp_materialInvolved->setRequired(true);
		$sp_materialInvolved->setTable("complaint");
		$sp_materialInvolved->setExtTable("complaintExt");
		$sp_materialInvolved->setHelpId(80442);
		if(isset($savedFields["sp_materialInvolved"]))
		$sp_materialInvolved->setValue($savedFields["sp_materialInvolved"]);
		else $sp_materialInvolved->setValue("Yes");

		// Dependency
		$materialInvolvedDependency = new dependency();
		$materialInvolvedDependency->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency->setGroup('materialInvolvedYes');
		$materialInvolvedDependency->setShow(true);

		//		$materialInvolvedDependency2 = new dependency();
		//		$materialInvolvedDependency2->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		//		$materialInvolvedDependency2->setGroup('scapaInvoiceYesGroup');
		//		$materialInvolvedDependency2->setShow(true);

		$materialInvolvedDependency3 = new dependency();
		$materialInvolvedDependency3->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency3->setGroup('sapGroup');
		$materialInvolvedDependency3->setShow(true);

		$materialInvolvedDependency4 = new dependency();
		$materialInvolvedDependency4->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency4->setGroup('materialGroupGroup');
		$materialInvolvedDependency4->setShow(true);

		$materialInvolvedDependency5 = new dependency();
		$materialInvolvedDependency5->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency5->setGroup('materialBlockedGroup');
		$materialInvolvedDependency5->setShow(true);

		$materialInvolvedDependency6 = new dependency();
		$materialInvolvedDependency6->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency6->setGroup('materialBlockedYes');
		$materialInvolvedDependency6->setShow(true);

		$materialInvolvedDependency7 = new dependency();
		$materialInvolvedDependency7->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency7->setGroup('sampleSentGroup');
		$materialInvolvedDependency7->setShow(true);

		$materialInvolvedDependency8 = new dependency();
		$materialInvolvedDependency8->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		$materialInvolvedDependency8->setGroup('sampleSentYes');
		$materialInvolvedDependency8->setShow(true);

		//		$materialInvolvedDependency9 = new dependency();
		//		$materialInvolvedDependency9->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		//		$materialInvolvedDependency9->setGroup('materialDebitedGroup');
		//		$materialInvolvedDependency9->setShow(true);

		//		$materialInvolvedDependency10 = new dependency();
		//		$materialInvolvedDependency10->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		//		$materialInvolvedDependency10->setGroup('materialDebitedGroup');
		//		$materialInvolvedDependency10->setShow(true);
		//
		//		$materialInvolvedDependency11 = new dependency();
		//		$materialInvolvedDependency11->addRule(new rule('materialInvolvedGroup', 'sp_materialInvolved', 'Yes'));
		//		$materialInvolvedDependency11->setGroup('materialDebitedYesGroup');
		//		$materialInvolvedDependency11->setShow(true);

		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency);
		//$sp_materialInvolved->addControllingDependency($materialInvolvedDependency2);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency3);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency4);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency5);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency6);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency7);
		$sp_materialInvolved->addControllingDependency($materialInvolvedDependency8);
		//$sp_materialInvolved->addControllingDependency($materialInvolvedDependency9);
		//$sp_materialInvolved->addControllingDependency($materialInvolvedDependency10);
		//$sp_materialInvolved->addControllingDependency($materialInvolvedDependency11);
		$materialInvolvedGroup->add($sp_materialInvolved);

		if(!isset($savedFields["0|sapItemNumber"])){//the first one will always need to be set if its saved
			//echo "HERE";exit;
			$sapItemNumber = new textbox("sapItemNumber");
			if(isset($savedFields["0|sapItemNumber"]))
			$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
			$sapItemNumber->setGroup("sapGroup");
			$sapItemNumber->setDataType("textMinLength");
			$sapItemNumber->setErrorMessage("field_error");
			$sapItemNumber->setMinLength(3);
			$sapItemNumber->setRowTitle("sap_item_number");
			$sapItemNumber->setRequired(true);
			$sapItemNumber->setTable("sapItemNumber");
			$sapItemNumber->setHelpId(8022);
			$sapGroup->add($sapItemNumber);
		}else{
			$this->form->groupsToExclude[] = "sapGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|sapItemNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$sapItemNumber = new textbox("sapItemNumber");
					if(isset($savedFields["0|sapItemNumber"]))
					$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
					$sapItemNumber->setGroup("sapGroup");
					$sapItemNumber->setDataType("textMinLength");
					$sapItemNumber->setErrorMessage("field_error");
					$sapItemNumber->setMinLength(3);
					$sapItemNumber->setRowTitle("sap_item_number");
					$sapItemNumber->setRequired(true);
					$sapItemNumber->setTable("sapItemNumber");
					$sapItemNumber->setHelpId(8022);
					$sapGroup->add($sapItemNumber);
				}else{
					$sapGroup->addRowCustom($savedFields[$i."|sapItemNumber"]);
				}
			}
		}

		if(!isset($savedFields["0|materialGroup"])){//the first one will always need to be set if its saved
			$materialGroup = new textbox("materialGroup");
			//if(isset($savedFields["materialGroup"]))
			//$materialGroup->setValue($savedFields["materialGroup"]);
			$materialGroup->setGroup("materialGroupGroup");
			$materialGroup->setDataType("string");
			$materialGroup->setLength(255);
			$materialGroup->setRowTitle("material_group");
			$materialGroup->setRequired(false);
			$materialGroup->setTable("materialGroup");
			$materialGroup->setHelpId(8015);
			$materialGroupGroup->add($materialGroup);
		}else{
			$this->form->groupsToExclude[] = "materialGroupGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroup"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$materialGroup = new textbox("materialGroup");
					if(isset($savedFields["0|materialGroup"]))
					$materialGroup->setValue($savedFields["0|materialGroup"]);
					$materialGroup->setGroup("materialGroupGroup");
					$materialGroup->setDataType("string");
					$materialGroup->setLength(255);
					$materialGroup->setRowTitle("material_group");
					$materialGroup->setRequired(false);
					$materialGroup->setTable("materialGroup");
					$materialGroup->setHelpId(8015);
					$materialGroupGroup->add($materialGroup);
				}else{
					$materialGroupGroup->addRowCustom($savedFields[$i."|materialGroup"]);
				}
			}
		}

		if(!isset($savedFields["0|materialGroup"])){//the first one will always need to be set if its saved
			$productDescription = new textarea("productDescription");
			if(isset($savedFields["productDescription"]))
			$productDescription->setValue($savedFields["productDescription"]);
			$productDescription->setGroup("materialGroupGroup");
			$productDescription->setDataType("text");
			$productDescription->setRowTitle("material_description");
			$productDescription->setRequired(false);
			$productDescription->setTable("materialGroup");
			$productDescription->setHelpId(8016);
			$materialGroupGroup->add($productDescription);
		}else{
			$this->form->groupsToExclude[] = "materialGroupGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroup"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$productDescription = new textarea("productDescription");
					if(isset($savedFields["productDescription"]))
					$productDescription->setValue($savedFields["productDescription"]);
					$productDescription->setGroup("materialGroupGroup");
					$productDescription->setDataType("text");
					$productDescription->setRowTitle("material_description");
					$productDescription->setRequired(false);
					$productDescription->setTable("materialGroup");
					$productDescription->setHelpId(8016);
					$materialGroupGroup->add($productDescription);
				}else{
					$materialGroupGroup->addRowCustom($savedFields[$i."|materialGroup"]);
				}
			}
		}

		$sp_materialBlocked = new radio("sp_materialBlocked");
		$sp_materialBlocked->setGroup("materialBlockedGroup");
		$sp_materialBlocked->setDataType("string");
		$sp_materialBlocked->setLength(5);
		$sp_materialBlocked->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$sp_materialBlocked->setRowTitle("material_blocked");
		$sp_materialBlocked->setValue("No");
		$sp_materialBlocked->setHelpId(80443);
		$sp_materialBlocked->setRequired(true);
		$sp_materialBlocked->setTable("complaint");
		if(isset($savedFields["sp_materialBlocked"]))
		$sp_materialBlocked->setValue($savedFields["sp_materialBlocked"]);
		else $sp_materialBlocked->setValue("No");

		// Dependency
		$materialBlockedDependency = new dependency();
		$materialBlockedDependency->addRule(new rule('materialBlockedGroup', 'sp_materialBlocked', 'Yes'));
		$materialBlockedDependency->setGroup('materialBlockedYes');
		$materialBlockedDependency->setShow(true);

		$materialBlockedDependency2 = new dependency();
		$materialBlockedDependency2->addRule(new rule('materialBlockedGroup', 'sp_materialBlocked', 'No'));
		$materialBlockedDependency2->setGroup('materialBlockedYes');
		$materialBlockedDependency2->setShow(false);

		$sp_materialBlocked->addControllingDependency($materialBlockedDependency);
		$sp_materialBlocked->addControllingDependency($materialBlockedDependency2);
		$materialBlockedGroup->add($sp_materialBlocked);

		$sp_materialBlockedName = new textbox("sp_materialBlockedName");
		if(isset($savedFields["sp_materialBlockedName"]))
		$sp_materialBlockedName->setValue($savedFields["sp_materialBlockedName"]);
		$sp_materialBlockedName->setGroup("materialBlockedYes");
		$sp_materialBlockedName->setDataType("string");
		$sp_materialBlockedName->setLength(30);
		$sp_materialBlockedName->setHelpId(80444);
		$sp_materialBlockedName->setRowTitle("material_blocked_name");
		$sp_materialBlockedName->setRequired(false);
		$sp_materialBlockedName->setTable("complaint");
		$materialBlockedYes->add($sp_materialBlockedName);

		$sp_materialBlockedDate = new calendar("sp_materialBlockedDate");
		if(isset($savedFields["sp_materialBlockedDate"]))
		$sp_materialBlockedDate->setValue($savedFields["sp_materialBlockedDate"]);
		$sp_materialBlockedDate->setGroup("materialBlockedYes");
		$sp_materialBlockedDate->setDataType("date");
		$sp_materialBlockedDate->setLength(30);
		$sp_materialBlockedDate->setHelpId(80445);
		$sp_materialBlockedDate->setRowTitle("material_blocked_date");
		$sp_materialBlockedDate->setRequired(false);
		$sp_materialBlockedDate->setTable("complaint");
		$materialBlockedYes->add($sp_materialBlockedDate);

		//		$dimensionThickness = new measurement("dimensionThickness");
		//		if(isset($savedFields["dimensionThickness_quantity"]) && isset($savedFields["dimensionThickness_measurement"])){
		//			//echo strval($savedFields["dimensionThickness_quantity"]);exit;
		//			$arr[0] = $savedFields["dimensionThickness_quantity"];
		//			$arr[1] = $savedFields["dimensionThickness_measurement"];
		//			$dimensionThickness->setValue($arr);
		//		}else $dimensionThickness->setMeasurement("mm");
		//		$dimensionThickness->setGroup("materialInvolvedYes");
		//		$dimensionThickness->setDataType("string");
		//		$dimensionThickness->setLength(5);
		//		$dimensionThickness->setXMLSource("./apps/complaints/xml/uom.xml");
		//		$dimensionThickness->setRowTitle("thickness");
		//		$dimensionThickness->setRequired(false);
		//		$dimensionThickness->setTable("complaint");
		//		$dimensionThickness->setExtTable("complaintExt");
		//		$dimensionThickness->setHelpId(8018);
		//		$materialInvolvedYes->add($dimensionThickness);
		//
		//		$dimensionWidth = new measurement("dimensionWidth");
		//		if(isset($savedFields["dimensionWidth_quantity"]) && isset($savedFields["dimensionWidth_measurement"])){
		//			$arr[0] = $savedFields["dimensionWidth_quantity"];
		//			$arr[1] = $savedFields["dimensionWidth_measurement"];
		//			$dimensionWidth->setValue($arr);
		//		}else $dimensionWidth->setMeasurement("mm");
		//		$dimensionWidth->setGroup("materialInvolvedYes");
		//		$dimensionWidth->setDataType("string");
		//		$dimensionWidth->setLength(5);
		//		$dimensionWidth->setXMLSource("./apps/complaints/xml/uom.xml");
		//		$dimensionWidth->setRowTitle("width");
		//		$dimensionWidth->setRequired(false);
		//		$dimensionWidth->setTable("complaint");
		//		$dimensionWidth->setExtTable("complaintExt");
		//		$dimensionWidth->setHelpId(8019);
		//		$materialInvolvedYes->add($dimensionWidth);
		//
		//		$dimensionLength = new measurement("dimensionLength");
		//		if(isset($savedFields["dimensionLength_quantity"]) && isset($savedFields["dimensionLength_measurement"])){
		//			$arr[0] = $savedFields["dimensionLength_quantity"];
		//			$arr[1] = $savedFields["dimensionLength_measurement"];
		//			$dimensionLength->setValue($arr);
		//		}else $dimensionLength->setMeasurement("m");
		//		$dimensionLength->setGroup("materialInvolvedYes");
		//		$dimensionLength->setDataType("string");
		//		$dimensionLength->setLength(5);
		//		$dimensionLength->setXMLSource("./apps/complaints/xml/uom.xml");
		//		$dimensionLength->setRowTitle("length");
		//		$dimensionLength->setRequired(false);
		//		$dimensionLength->setTable("complaint");
		//		$dimensionLength->setExtTable("complaintExt");
		//		$dimensionLength->setHelpId(8020);
		//		$materialInvolvedYes->add($dimensionLength);
		//
		//		$colour = new textbox("colour");
		//		if(isset($savedFields["colour"]))
		//			$colour->setValue($savedFields["colour"]);
		//		$colour->setGroup("materialInvolvedYes");
		//		$colour->setDataType("string");
		//		$colour->setRowTitle("colour");
		//		$colour->setRequired(false);
		//		$colour->setTable("complaint");
		//		$colour->setExtTable("complaintExt");
		//		$colour->setHelpId(8021);
		//		$materialInvolvedYes->add($colour);

		$sp_supplierItemNumber = new textbox("sp_supplierItemNumber");
		if(isset($savedFields["sp_supplierItemNumber"]))
		$sp_supplierItemNumber->setValue($savedFields["sp_supplierItemNumber"]);
		$sp_supplierItemNumber->setGroup("materialInvolvedYes");
		$sp_supplierItemNumber->setDataType("string");
		$sp_supplierItemNumber->setRowTitle("supplier_item_number");
		$sp_supplierItemNumber->setRequired(false);
		$sp_supplierItemNumber->setHelpId(80446);
		$sp_supplierItemNumber->setTable("complaint");
		$sp_supplierItemNumber->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_supplierItemNumber);

		$sp_supplierProductDescription = new textarea("sp_supplierProductDescription");
		if(isset($savedFields["sp_supplierProductDescription"]))
		$sp_supplierProductDescription->setValue($savedFields["sp_supplierProductDescription"]);
		$sp_supplierProductDescription->setGroup("materialInvolvedYes");
		$sp_supplierProductDescription->setDataType("text");
		$sp_supplierProductDescription->setHelpId(80447);
		$sp_supplierProductDescription->setRowTitle("supplier_product_description");
		$sp_supplierProductDescription->setRequired(false);
		$sp_supplierProductDescription->setTable("complaint");
		$sp_supplierProductDescription->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_supplierProductDescription);

		$batchNumber = new textbox("batchNumber");
		if(isset($savedFields["batchNumber"]))
		$batchNumber->setValue($savedFields["batchNumber"]);
		$batchNumber->setGroup("materialInvolvedYes");
		$batchNumber->setDataType("string");
		$batchNumber->setRowTitle("scapa_batch_number");
		$batchNumber->setRequired(false);
		$batchNumber->setVisible(true);
		$batchNumber->setTable("complaint");
		$batchNumber->setExtTable("complaintExt");
		$batchNumber->setHelpId(8029);
		$materialInvolvedYes->add($batchNumber);

		$supplierBatchNumber = new textbox("supplierBatchNumber");
		if(isset($savedFields["supplierBatchNumber"]))
		$supplierBatchNumber->setValue($savedFields["supplierBatchNumber"]);
		$supplierBatchNumber->setGroup("materialInvolvedYes");
		$supplierBatchNumber->setDataType("string");
		$supplierBatchNumber->setRowTitle("supplier_batch_number");
		$supplierBatchNumber->setRequired(false);
		$supplierBatchNumber->setVisible(true);
		$supplierBatchNumber->setTable("complaint");
		$supplierBatchNumber->setExtTable("complaintExt");
		$supplierBatchNumber->setHelpId(80291);
		$materialInvolvedYes->add($supplierBatchNumber);

		$supplierDeliveryNoteNumber = new textbox("supplierDeliveryNoteNumber");
		if(isset($savedFields["supplierDeliveryNoteNumber"]))
		$supplierDeliveryNoteNumber->setValue($savedFields["supplierDeliveryNoteNumber"]);
		$supplierDeliveryNoteNumber->setGroup("materialInvolvedYes");
		$supplierDeliveryNoteNumber->setDataType("string");
		$supplierDeliveryNoteNumber->setRowTitle("supplier_delivery_note_number");
		$supplierDeliveryNoteNumber->setRequired(false);
		$supplierDeliveryNoteNumber->setVisible(true);
		$supplierDeliveryNoteNumber->setTable("complaint");
		$supplierDeliveryNoteNumber->setHelpId(80290);
		$materialInvolvedYes->add($supplierDeliveryNoteNumber);

		$sp_goodsReceivedDate = new calendar("sp_goodsReceivedDate");
		if(isset($savedFields["sp_goodsReceivedDate"]))
		$sp_goodsReceivedDate->setValue($savedFields["sp_goodsReceivedDate"]);
		$sp_goodsReceivedDate->setGroup("materialInvolvedYes");
		$sp_goodsReceivedDate->setDataType("date");
		$sp_goodsReceivedDate->setRowTitle("goods_received_date");
		$sp_goodsReceivedDate->setRequired(false);
		$sp_goodsReceivedDate->setHelpId(80291);
		$sp_goodsReceivedDate->setTable("complaint");
		$sp_goodsReceivedDate->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_goodsReceivedDate);

		$sp_goodsReceivedNumber = new textbox("sp_goodsReceivedNumber");
		if(isset($savedFields["sp_goodsReceivedNumber"]))
		$sp_goodsReceivedNumber->setValue($savedFields["sp_goodsReceivedNumber"]);
		$sp_goodsReceivedNumber->setGroup("materialInvolvedYes");
		$sp_goodsReceivedNumber->setDataType("string");
		$sp_goodsReceivedNumber->setRowTitle("goods_received_number");
		$sp_goodsReceivedNumber->setRequired(false);
		$sp_goodsReceivedNumber->setHelpId(80292);
		$sp_goodsReceivedNumber->setTable("complaint");
		$sp_goodsReceivedNumber->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_goodsReceivedNumber);

		$sp_quantityRecieved = new measurement("sp_quantityRecieved");
		if(isset($savedFields["sp_quantityRecieved_quantity"]) && isset($savedFields["sp_quantityRecieved_measurement"])){
			$arr[0] = $savedFields["sp_quantityRecieved_quantity"];
			$arr[1] = $savedFields["sp_quantityRecieved_measurement"];
			$sp_quantityRecieved->setValue($arr);
		}else $sp_quantityRecieved->setMeasurement("roll");
		$sp_quantityRecieved->setGroup("materialInvolvedYes");
		$sp_quantityRecieved->setDataType("string");
		$sp_quantityRecieved->setLength(10);
		$sp_quantityRecieved->setXMLSource("./apps/complaints/xml/uom.xml");
		$sp_quantityRecieved->setRowTitle("quantity_received");
		$sp_quantityRecieved->setRequired(false);
		$sp_quantityRecieved->setTable("complaint");
		$sp_quantityRecieved->setExtTable("complaintExt");
		$sp_quantityRecieved->setHelpId(8026);
		$materialInvolvedYes->add($sp_quantityRecieved);

		$quantityUnderComplaint = new measurement("quantityUnderComplaint");
		if(isset($savedFields["quantityUnderComplaint_quantity"]) && isset($savedFields["quantityUnderComplaint_measurement"])){
			$arr[0] = $savedFields["quantityUnderComplaint_quantity"];
			$arr[1] = $savedFields["quantityUnderComplaint_measurement"];
			$quantityUnderComplaint->setValue($arr);
		}else $quantityUnderComplaint->setMeasurement("roll");
		$quantityUnderComplaint->setGroup("materialInvolvedYes");
		$quantityUnderComplaint->setDataType("string");
		$quantityUnderComplaint->setErrorMessage("field_error");
		$quantityUnderComplaint->setLength(10);
		$quantityUnderComplaint->setXMLSource("./apps/complaints/xml/uom.xml");
		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
		$quantityUnderComplaint->setRequired(true);
		$quantityUnderComplaint->setTable("complaint");
		$quantityUnderComplaint->setExtTable("complaintExt");
		$quantityUnderComplaint->setHelpId(80261);
		$materialInvolvedYes->add($quantityUnderComplaint);

		$complaintValue = new measurement("complaintValue");
		if(isset($savedFields["complaintValue_quantity"]) && isset($savedFields["complaintValue_measurement"])){
			$arr[0] = $savedFields["complaintValue_quantity"];
			$arr[1] = $savedFields["complaintValue_measurement"];
			$complaintValue->setValue($arr);
		}else $complaintValue->setMeasurement("EUR");
		$complaintValue->setGroup("materialInvolvedYes");
		$complaintValue->setDataType("string");
		$complaintValue->setErrorMessage("field_error");
		$complaintValue->setLength(10);
		$complaintValue->setRowTitle("complaint_value");
		$complaintValue->setRequired(true);
		$complaintValue->setTable("complaint");
		$complaintValue->setExtTable("complaintExt");
		$complaintValue->setXMLSource("./apps/complaints/xml/currency.xml");
		$complaintValue->setHelpId(80271);
		$materialInvolvedYes->add($complaintValue);

		$complaintValueComment = new textarea("complaintValueComment");
		$complaintValueComment->setGroup("materialInvolvedYes");
		if(isset($savedFields["complaintValueComment"]))
		$complaintValueComment->setValue($savedFields["complaintValueComment"]);
		$complaintValueComment->setDataType("text");
		$complaintValueComment->setTable("complaint");
		$complaintValueComment->setHelpId(802712);
		$complaintValueComment->setRowTitle("comment");
		$materialInvolvedYes->add($complaintValueComment);

		$gbpComplaintValue = new measurement("gbpComplaintValue");
		if(isset($savedFields["gbpComplaintValue"]))
		$gbpComplaintValue->setValue($savedFields["gbpComplaintValue"]);
		$gbpComplaintValue->setGroup("materialInvolvedYes");
		$gbpComplaintValue->setDataType("string");
		$gbpComplaintValue->setLength(10);
		$gbpComplaintValue->setRowTitle("complaint_value");
		$gbpComplaintValue->setErrorMessage("field_error");
		$gbpComplaintValue->setRequired(false);
		$gbpComplaintValue->setVisible(false);
		$gbpComplaintValue->setTable("complaint");
		$gbpComplaintValue->setXMLSource("./apps/complaints/xml/currency.xml");
		$gbpComplaintValue->setMeasurement("GBP");
		$gbpComplaintValue->setHelpId(80272);
		$materialInvolvedYes->add($gbpComplaintValue);

		$sp_additionalComplaintCost = new measurement("sp_additionalComplaintCost");
		if(isset($savedFields["sp_additionalComplaintCost_quantity"]) && isset($savedFields["sp_additionalComplaintCost_measurement"])){
			$arr[0] = $savedFields["sp_additionalComplaintCost_quantity"];
			$arr[1] = $savedFields["sp_additionalComplaintCost_measurement"];
			$sp_additionalComplaintCost->setValue($arr);
		}else $sp_additionalComplaintCost->setMeasurement("EUR");
		$sp_additionalComplaintCost->setGroup("materialInvolvedYes");
		$sp_additionalComplaintCost->setDataType("string");
		$sp_additionalComplaintCost->setErrorMessage("field_error");
		$sp_additionalComplaintCost->setLength(10);
		$sp_additionalComplaintCost->setRowTitle("additional_complaint_cost");
		$sp_additionalComplaintCost->setRequired(false);
		$sp_additionalComplaintCost->setTable("complaint");
		$sp_additionalComplaintCost->setExtTable("complaintExt");
		$sp_additionalComplaintCost->setHelpId(80273);
		$sp_additionalComplaintCost->setXMLSource("./apps/complaints/xml/currency.xml");
		$materialInvolvedYes->add($sp_additionalComplaintCost);

		//		$sp_detailsOfAdditionalCost = new textarea("sp_detailsOfAdditionalCost");
		//		if(isset($savedFields["sp_detailsOfAdditionalCost"]))
		//			$sp_detailsOfAdditionalCost->setValue($savedFields["sp_detailsOfAdditionalCost"]);
		//		$sp_detailsOfAdditionalCost->setGroup("materialInvolvedYes");
		//		$sp_detailsOfAdditionalCost->setDataType("text");
		//		$sp_detailsOfAdditionalCost->setRowTitle("details_of_additional_cost");
		//		$sp_detailsOfAdditionalCost->setRequired(false);
		//		$sp_detailsOfAdditionalCost->setTable("complaint");
		//		$materialInvolvedYes->add($sp_detailsOfAdditionalCost);

		$sp_detailsOfComplaintCost = new textarea("sp_detailsOfComplaintCost");
		if(isset($savedFields["sp_detailsOfComplaintCost"]))
		$sp_detailsOfComplaintCost->setValue($savedFields["sp_detailsOfComplaintCost"]);
		$sp_detailsOfComplaintCost->setGroup("materialInvolvedYes");
		$sp_detailsOfComplaintCost->setDataType("text");
		$sp_detailsOfComplaintCost->setRowTitle("details_of_complaint_cost");
		$sp_detailsOfComplaintCost->setRequired(false);
		$sp_detailsOfComplaintCost->setHelpId(80274);
		$sp_detailsOfComplaintCost->setTable("complaint");
		$sp_detailsOfComplaintCost->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_detailsOfComplaintCost);





		//		$sp_acknowledgementNumber = new textbox("sp_acknowledgementNumber");
		//		if(isset($savedFields["sp_acknowledgementNumber"]))
		//			$sp_acknowledgementNumber->setValue($savedFields["sp_acknowledgementNumber"]);
		//		$sp_acknowledgementNumber->setGroup("materialInvolvedYes");
		//		$sp_acknowledgementNumber->setDataType("string");
		//		$sp_acknowledgementNumber->setRowTitle("acknowledgement_number");
		//		$sp_acknowledgementNumber->setRequired(false);
		//		$sp_acknowledgementNumber->setVisible(true);
		//		$sp_acknowledgementNumber->setTable("complaint");
		//		$sp_acknowledgementNumber->setHelpId(8029);
		//		$materialInvolvedYes->add($sp_acknowledgementNumber);



		$sp_purchaseOrderNumber = new textbox("sp_purchaseOrderNumber");
		if(isset($savedFields["sp_purchaseOrderNumber"]))
		$sp_purchaseOrderNumber->setValue($savedFields["sp_purchaseOrderNumber"]);
		$sp_purchaseOrderNumber->setGroup("materialInvolvedYes");
		$sp_purchaseOrderNumber->setDataType("string");
		$sp_purchaseOrderNumber->setRowTitle("purchase_order_number");
		$sp_purchaseOrderNumber->setRequired(false);
		$sp_purchaseOrderNumber->setHelpId(80275);
		$sp_purchaseOrderNumber->setTable("complaint");
		$sp_purchaseOrderNumber->setExtTable("complaintExt");
		$materialInvolvedYes->add($sp_purchaseOrderNumber);

		$g8d = new radio("g8d");
		$g8d->setGroup("complaintDetails2");
		$g8d->setDataType("string");
		$g8d->setLength(5);
		$g8d->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$g8d->setRowTitle("full_8d_required");
		$g8d->setRequired(true);
		$g8d->setTable("complaint");
		$g8d->setExtTable("complaintExt");
		if(isset($savedFields["g8d"]))
		$g8d->setValue($savedFields["g8d"]);
		else $g8d->setValue("yes");
		$g8d->setHelpId(8006);
		$complaintDetails2->add($g8d);

		//		$sp_materialLocation = new textbox("sp_materialLocation");
		//		if(isset($savedFields["sp_materialLocation"]))
		//			$sp_materialLocation->setValue($savedFields["sp_materialLocation"]);
		//		$sp_materialLocation->setGroup("materialInvolvedYes");
		//		$sp_materialLocation->setDataType("string");
		//		$sp_materialLocation->setRowTitle("material_location");
		//		$sp_materialLocation->setRequired(false);
		//		$sp_materialLocation->setTable("complaint");
		//		$materialInvolvedYes->add($sp_materialLocation);

		$problemDescription = new textarea("problemDescription");
		if(isset($savedFields["problemDescription"]))
		$problemDescription->setValue($savedFields["problemDescription"]);
		$problemDescription->setGroup("complaintDetails2");
		$problemDescription->setDataType("text");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setRequired(true);
		$problemDescription->setTable("complaint");
		$problemDescription->setExtTable("complaintExt");
		$problemDescription->setHelpId(8035);
		$complaintDetails2->add($problemDescription);

		$actionRequested = new textarea("actionRequested");
		if(isset($savedFields["actionRequested"]))
		$actionRequested->setValue($savedFields["actionRequested"]);
		$actionRequested->setGroup("complaintDetails2");
		$actionRequested->setDataType("text");
		$actionRequested->setRowTitle("actions_by_scapa_to_minimise_problem");
		$actionRequested->setRequired(false);
		$actionRequested->setTable("complaint");
		$actionRequested->setExtTable("complaintExt");
		$actionRequested->setHelpId(8044);
		$complaintDetails2->add($actionRequested);

		$actionRequestedFromSupplier = new textarea("actionRequestedFromSupplier");
		if(isset($savedFields["actionRequestedFromSupplier"]))
		$actionRequestedFromSupplier->setValue($savedFields["actionRequestedFromSupplier"]);
		$actionRequestedFromSupplier->setGroup("complaintDetails2");
		$actionRequestedFromSupplier->setDataType("text");
		$actionRequestedFromSupplier->setRowTitle("actions_requested_from_supplier");
		$actionRequestedFromSupplier->setRequired(false);
		$actionRequestedFromSupplier->setTable("complaint");
		$actionRequestedFromSupplier->setExtTable("complaintExt");
		$actionRequestedFromSupplier->setHelpId(80441);
		$complaintDetails2->add($actionRequestedFromSupplier);


		$sp_sampleSent = new radio("sp_sampleSent");
		$sp_sampleSent->setGroup("sampleSentGroup");
		$sp_sampleSent->setDataType("string");
		$sp_sampleSent->setLength(5);
		$sp_sampleSent->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$sp_sampleSent->setRowTitle("sample_sent");
		$sp_sampleSent->setValue("No");
		$sp_sampleSent->setRequired(true);
		$sp_sampleSent->setHelpId(804411);
		$sp_sampleSent->setTable("complaint");
		$sp_sampleSent->setExtTable("complaintExt");
		if(isset($savedFields["sp_sampleSent"]))
		$sp_sampleSent->setValue($savedFields["sp_sampleSent"]);
		else $sp_sampleSent->setValue("Yes");

		// Dependency
		$sampleSentDependency = new dependency();
		$sampleSentDependency->addRule(new rule('sampleSentGroup', 'sp_sampleSent', 'Yes'));
		$sampleSentDependency->setGroup('sampleSentYes');
		$sampleSentDependency->setShow(true);

		$sp_sampleSent->addControllingDependency($sampleSentDependency);
		$sampleSentGroup->add($sp_sampleSent);

		$sp_sampleSentName = new autocomplete("sp_sampleSentName");
		if(isset($savedFields["sp_sampleSentName"]))
		$sp_sampleSentName->setValue($savedFields["sp_sampleSentName"]);
		$sp_sampleSentName->setGroup("sampleSentYes");
		$sp_sampleSentName->setDataType("string");
		$sp_sampleSentName->setLength(250);
		$sp_sampleSentName->setHelpId(804412);
		$sp_sampleSentName->setRowTitle("sample_sent_name");
		$sp_sampleSentName->setUrl("/apps/complaints/ajax/sp_sampleSentName?");
		$sp_sampleSentName->setRequired(false);
		$sp_sampleSentName->setTable("complaint");
		$sampleSentYes->add($sp_sampleSentName);

		$sp_sampleSentDate = new calendar("sp_sampleSentDate");
		if(isset($savedFields["sp_sampleSentDate"]))
		$sp_sampleSentDate->setValue($savedFields["sp_sampleSentDate"]);
		$sp_sampleSentDate->setGroup("sampleSentYes");
		$sp_sampleSentDate->setDataType("date");
		$sp_sampleSentDate->setLength(30);
		$sp_sampleSentDate->setHelpId(804413);
		$sp_sampleSentDate->setRowTitle("date_sample_sent");
		$sp_sampleSentDate->setRequired(true);
		$sp_sampleSentDate->setTable("complaint");
		$sp_sampleSentDate->setExtTable("complaintExt");
		$sampleSentYes->add($sp_sampleSentDate);


		//		$sp_materialDebited = new radio("sp_materialDebited");
		//		$sp_materialDebited->setGroup("materialDebitedGroup");
		//		$sp_materialDebited->setDataType("string");
		//		$sp_materialDebited->setLength(5);
		//		$sp_materialDebited->setArraySource(array(
		//			array('value' => 'Yes', 'display' => 'Yes'),
		//			array('value' => 'No', 'display' => 'No')
		//		));
		//		$sp_materialDebited->setRowTitle("material_debited");
		//		$sp_materialDebited->setValue("Yes");
		//		$sp_materialDebited->setRequired(true);
		//		$sp_materialDebited->setTable("complaint");
		//		if(isset($savedFields["sp_materialDebited"]))
		//			$sp_materialDebited->setValue($savedFields["sp_materialDebited"]);
		//		else $sp_materialDebited->setValue("Yes");
		//		$materialDebitedGroup->add($sp_materialDebited);



		//		$sp_submitToPurchaser = new radio("sp_submitToPurchaser");
		//		$sp_submitToPurchaser->setGroup("sampleSentGroup");
		//		$sp_submitToPurchaser->setDataType("string");
		//		$sp_submitToPurchaser->setLength(5);
		//		$sp_submitToPurchaser->setArraySource(array(
		//			array('value' => 'Yes', 'display' => 'Yes'),
		//			array('value' => 'No', 'display' => 'No')
		//		));
		//		$sp_submitToPurchaser->setRowTitle("submit_to_purchaser");
		//		$sp_submitToPurchaser->setValue("No");
		//		$sp_submitToPurchaser->setRequired(true);
		//		$sp_submitToPurchaser->setTable("complaint");
		//		if(isset($savedFields["sp_submitToPurchaser"]))
		//			$sp_sampleSent->setValue($savedFields["sp_submitToPurchaser"]);
		//		else $sp_submitToPurchaser->setValue("No");
		//		$materialDebitedYes->add($sp_submitToPurchaser);


		////to be removed
		$sampleReceptionDate = new calendar("sampleReceptionDate");
		if(isset($savedFields["sampleReceptionDate"]))
		$sampleReceptionDate->setValue($savedFields["sampleReceptionDate"]);
		$sampleReceptionDate->setGroup("complaintDetails");
		$sampleReceptionDate->setDataType("date");
		$sampleReceptionDate->setLength(255);
		$sampleReceptionDate->setRowTitle("reception_date");
		$sampleReceptionDate->setRequired(false);
		$sampleReceptionDate->setVisible(false);
		$sampleReceptionDate->setIgnore(true);
		$sampleReceptionDate->setValue("to be removed");
		$sampleReceptionDate->setTable("complaint");
		$sampleReceptionDate->setHelpId(8040);
		$complaintDetails->add($sampleReceptionDate);

		////to be removed
		$sampleDate = new calendar("sampleDate");
		if(isset($savedFields["sampleDate"]))
		$sampleDate->setValue($savedFields["sampleDate"]);
		$sampleDate->setGroup("complaintDetails");
		$sampleDate->setDataType("date");
		$sampleDate->setLength(255);
		$sampleDate->setRowTitle("sample_date");
		$sampleDate->setRequired(false);
		$sampleDate->setVisible(false);
		$sampleDate->setIgnore(true);
		$sampleDate->setValue("to be removed");
		$sampleDate->setTable("complaint");
		$sampleDate->setHelpId(8040);
		$complaintDetails->add($sampleDate);

		//		$processOwnerLink = new textboxlink("processOwnerLink");
		//		$processOwnerLink->setRowTitle("process_owner_link");
		//		$processOwnerLink->setHelpId(1111);
		//		$processOwnerLink->setLink("http://scapanet/apps/complaints/data/po.xls");
		//		$processOwnerLink->setValue("Process Owner Matrix");
		//		$actionsGroup2->add($processOwnerLink);

		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1111);
		$processOwnerLink->setLink("http://scapanet/apps/complaints/data/po.xls");
		$processOwnerLink->setValue("Process Owner Matrix - Europe");
		$actionsGroup2->add($processOwnerLink);

		$processOwnerLink2 = new textboxlink("processOwnerLink2");
		$processOwnerLink2->setRowTitle("process_owner_link");
		$processOwnerLink2->setHelpId(1111);
		$processOwnerLink2->setLink("http://scapanet/apps/complaints/data/process_owner_matrix_na.xls");
		$processOwnerLink2->setValue("Process Owner Matrix - NA");
		$actionsGroup2->add($processOwnerLink2);


		$processOwner = new autocomplete("processOwner");
		if(isset($savedFields["processOwner"]))
		$processOwner->setValue($savedFields["processOwner"]);
		$processOwner->setGroup("complaintDetails2");
		$processOwner->setDataType("text");
		$processOwner->setRowTitle("COMPLAINT_OWNER");
		$processOwner->setRequired(true);
		$processOwner->setTable("complaint");
		$processOwner->setErrorMessage("user_not_found");
		$processOwner->setHelpId(8478);
		$processOwner->setUrl("/apps/complaints/ajax/newProcessOwner?");
		$actionsGroup2->add($processOwner);

		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
		$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("actionsGroup2");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("CC_customer");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("complaint");
		$copy_to->setHelpId(8146);
		$actionsGroup2->add($copy_to);


		//		if(!isset($savedFields["0|copy_to"])){//the first one will always need to be set if its saved
		//			$copy_to = new autocomplete("copy_to");
		//			if(isset($savedFields["0|copy_to"]))
		//				$copy_to->setValue($savedFields["0|copy_to"]);
		//			$copy_to->setGroup("ccComplaintGroup");
		//			$copy_to->setDataType("string");
		//			$copy_to->setRowTitle("CC");
		//			$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//			//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//			$copy_to->setRequired(false);
		//			$copy_to->setTable("ccGroup");
		//			$copy_to->setHelpId(8146);
		//			$ccComplaintGroup->add($copy_to);
		//		}else{
		//			$this->form->groupsToExclude[] = "ccComplaintGroup";
		//			$i=0;
		//			$endList = false;
		//			do{
		//				if(!isset($savedFields[$i."|copy_to"])){
		//					$maxList = $i;
		//					$endList = true;
		//				}
		//				$i++;
		//			}while(!$endList);
		//			for($i=0; $i<$maxList; $i++){
		//				if($i==0){//first will always be set
		//					$copy_to = new autocomplete("copy_to");
		//					if(isset($savedFields["0|copy_to"]))
		//						$copy_to->setValue($savedFields["0|copy_to"]);
		//					$copy_to->setGroup("ccComplaintGroup");
		//					$copy_to->setDataType("string");
		//					$copy_to->setRowTitle("CC");
		//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//					$copy_to->setRequired(false);
		//					$copy_to->setTable("ccGroup");
		//					$copy_to->setHelpId(8146);
		//					$ccComplaintGroup->add($copy_to);
		//				}else{
		//
		//					$copy_to = new autocomplete("copy_to");
		//					if(isset($savedFields[$i."|copy_to"]))
		//						$copy_to->setValue($savedFields[$i."|copy_to"]);
		//					$copy_to->setGroup("ccComplaintGroup");
		//					$copy_to->setDataType("string");
		//					$copy_to->setRowTitle("CC");
		//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//					$copy_to->setRequired(false);
		//					$copy_to->setTable("ccGroup");
		//					$copy_to->setHelpId(8146);
		//					//$ccComplaintGroup->add($copy_to);
		//
		//
		//					$ccComplaintGroup->addRowCustom($savedFields[$i."|copy_to"]);
		//				}
		//			}
		//		}

		$email_text = new textarea("email_text");
		if(isset($savedFields["email_text"]))
		$email_text->setValue($savedFields["email_text"]);
		$email_text->setGroup("sendToUser");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text_supplier_internal");
		$email_text->setRequired(false);
		$email_text->setTable("complaint");
		$email_text->setHelpId(8045);
		$sendToUser->add($email_text);


		$sp_submitToExtSupplier = new radio("sp_submitToExtSupplier");
		$sp_submitToExtSupplier->setGroup("sendToUser");
		$sp_submitToExtSupplier->setDataType("string");
		$sp_submitToExtSupplier->setLength(5);
		$sp_submitToExtSupplier->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$sp_submitToExtSupplier->setRowTitle("submit_to_external_supplier");
		$sp_submitToExtSupplier->setValue("No");
		$sp_submitToExtSupplier->setHelpId(80451);
		$sp_submitToExtSupplier->setRequired(true);
		$sp_submitToExtSupplier->setTable("complaint");
		$sp_submitToExtSupplier->setOnKeyPress("set_notification_for_supplier_submission()");
		if(isset($savedFields["sp_submitToExtSupplier"]))
		$sp_submitToExtSupplier->setValue($savedFields["sp_submitToExtSupplier"]);
		else $sp_submitToExtSupplier->setValue("No");

		// Dependency
		$openFinanceDependency = new dependency();
		$openFinanceDependency->addRule(new rule('sendToUser', 'sp_submitToExtSupplier', 'Yes'));
		$openFinanceDependency->setGroup('materialDebitedGroup');
		$openFinanceDependency->setShow(true);

		//		$openInvoiceDependency = new dependency();
		//		$openInvoiceDependency->addRule(new rule('sendToUser', 'sp_submitToExtSupplier', 'Yes'));
		//		$openInvoiceDependency->setGroup('scapaInvoiceYesGroup');
		//		$openInvoiceDependency->setShow(true);

		$sp_submitToExtSupplier->addControllingDependency($openFinanceDependency);
		//$sp_submitToExtSupplier->addControllingDependency($openInvoiceDependency);
		$sendToUser->add($sp_submitToExtSupplier);

		//		if(!isset($savedFields["0|scapaInvoiceNumber"])){//the first one will always need to be set if its saved
		//
		//			$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
		//			if(isset($savedFields["0|scapaInvoiceNumber"]))
		//				$scapaInvoiceNumber->setValue($savedFields["0|scapaInvoiceNumber"]);
		//			$scapaInvoiceNumber->setGroup("scapaInvoiceYesGroup");
		//			$scapaInvoiceNumber->setDataType("textMinLength");
		//			$scapaInvoiceNumber->setMinLength(6);
		//			$scapaInvoiceNumber->setRowTitle("supplier_invoice_number");
		//			$scapaInvoiceNumber->setRequired(false);
		//			$scapaInvoiceNumber->setTable("scapaInvoiceNumberDate");
		//			$scapaInvoiceNumber->setHelpId(8022);
		//			$scapaInvoiceYesGroup->add($scapaInvoiceNumber);
		//
		//			$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		//			if(isset($savedFields["0|scapaInvoiceDate"]))
		//				$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
		//			$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
		//			$scapaInvoiceDate->setDataType("date");
		//			$scapaInvoiceDate->setLength(255);
		//			$scapaInvoiceDate->setRowTitle("supplier_invoice_date");
		//			$scapaInvoiceDate->setRequired(false);
		//			$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
		//			$scapaInvoiceDate->setHelpId(8010);
		//			$scapaInvoiceYesGroup->add($scapaInvoiceDate);
		//
		//		}else{
		//			$this->form->groupsToExclude[] = "scapaInvoiceYesGroup";
		//			$i=0;
		//			$endList = false;
		//			do{
		//				if(!isset($savedFields[$i."|scapaInvoiceNumber"])){
		//					$maxList = $i;
		//					$endList = true;
		//				}
		//				$i++;
		//			}while(!$endList);
		//			for($i=0; $i<$maxList; $i++){
		//				if($i==0){//first will always be set
		//					$scapaInvoiceNumber = new textbox("scapaInvoiceNumber");
		//					if(isset($savedFields["0|scapaInvoiceNumber"]))
		//						$scapaInvoiceNumber->setValue($savedFields["0|scapaInvoiceNumber"]);
		//					$scapaInvoiceNumber->setGroup("scapaInvoiceYesGroup");
		//					$scapaInvoiceNumber->setDataType("textMinLength");
		//					$scapaInvoiceNumber->setMinLength(6);
		//					$scapaInvoiceNumber->setRowTitle("supplier_invoice_number");
		//					$scapaInvoiceNumber->setRequired(false);
		//					$scapaInvoiceNumber->setTable("scapaInvoiceNumberDate");
		//					$scapaInvoiceNumber->setHelpId(8022);
		//					$scapaInvoiceYesGroup->add($scapaInvoiceNumber);
		//
		//					$scapaInvoiceDate = new textbox("scapaInvoiceDate");
		//					if(isset($savedFields["0|scapaInvoiceDate"]))
		//						$scapaInvoiceDate->setValue($savedFields["0|scapaInvoiceDate"]);
		//					$scapaInvoiceDate->setGroup("scapaInvoiceYesGroup");
		//					$scapaInvoiceDate->setDataType("date");
		//					$scapaInvoiceDate->setLength(255);
		//					$scapaInvoiceDate->setRowTitle("supplier_invoice_date");
		//					$scapaInvoiceDate->setRequired(false);
		//					$scapaInvoiceDate->setTable("scapaInvoiceNumberDate");
		//					$scapaInvoiceDate->setHelpId(8022);
		//					$scapaInvoiceYesGroup->add($scapaInvoiceDate);
		//				}else{
		//					$customArr = array();
		//					$customArr[] = $savedFields[$i."|scapaInvoiceNumber"];
		//					$customArr[] = $savedFields[$i."|scapaInvoiceDate"];
		//					$scapaInvoiceYesGroup->addRowCustomMultiple($customArr);
		//					//$scapaInvoiceYesGroup->addRowCustom($savedFields[$i."|scapaInvoiceDate"]);
		//				}
		//			}
		//		}

		$externalEmailAddress = new textbox("externalEmailAddress");
		if(isset($savedFields["externalEmailAddress"]))
		$externalEmailAddress->setValue($savedFields["externalEmailAddress"]);
		$externalEmailAddress->setGroup("materialDebitedGroup");
		$externalEmailAddress->setDataType("string");
		$externalEmailAddress->setLength(250);
		$externalEmailAddress->setHelpId(80452);
		$externalEmailAddress->setRowTitle("the_complaint_will_be_sent_to");
		$externalEmailAddress->setRequired(true);
		$externalEmailAddress->setTable("complaint");
		$materialDebitedGroup->add($externalEmailAddress);

		$externalFirstName = new textbox("externalFirstName");
		if(isset($savedFields["externalFirstName"]))
		$externalFirstName->setValue($savedFields["externalFirstName"]);
		$externalFirstName->setGroup("materialDebitedGroup");
		$externalFirstName->setDataType("string");
		$externalFirstName->setLength(250);
		$externalFirstName->setHelpId(804522234);
		$externalFirstName->setRowTitle("supplier_contact_first_name");
		$externalFirstName->setRequired(true);
		$externalFirstName->setTable("complaint");
		$materialDebitedGroup->add($externalFirstName);

		$externalLastName = new textbox("externalLastName");
		if(isset($savedFields["externalLastName"]))
		$externalLastName->setValue($savedFields["externalLastName"]);
		$externalLastName->setGroup("materialDebitedGroup");
		$externalLastName->setDataType("string");
		$externalLastName->setLength(250);
		$externalLastName->setHelpId(80452657);
		$externalLastName->setRowTitle("supplier_contact_last_name");
		$externalLastName->setRequired(true);
		$externalLastName->setTable("complaint");
		$materialDebitedGroup->add($externalLastName);

		$supplierDefaultLanguage = new dropdown("supplierDefaultLanguage");
		if(isset($savedFields["supplierDefaultLanguage"]))
		{
			$supplierDefaultLanguage->setValue($savedFields["supplierDefaultLanguage"]);
		}
		$supplierDefaultLanguage->setGroup("materialDebitedGroup");
		$supplierDefaultLanguage->setDataType("string");
		$supplierDefaultLanguage->setLength(250);
		$supplierDefaultLanguage->setHelpId(804526573434);
		$supplierDefaultLanguage->setRowTitle("supplier_default_language");
		$supplierDefaultLanguage->setXMLSource("./apps/complaints/xml/languages.xml");
		$supplierDefaultLanguage->setValue("ENGLISH");
		$supplierDefaultLanguage->setRequired(true);
		$supplierDefaultLanguage->setTable("complaint");
		$materialDebitedGroup->add($supplierDefaultLanguage);

		$externalComment = new textarea("externalComment");
		if(isset($savedFields["externalComment"]))
		$externalComment->setValue($savedFields["externalComment"]);
		$externalComment->setGroup("materialDebitedGroup");
		$externalComment->setDataType("text");
		$externalComment->setRowTitle("external_comment");
		$externalComment->setRequired(false);
		$externalComment->setTable("complaint");
		$externalComment->setHelpId(80453);
		$materialDebitedGroup->add($externalComment);

		//		$sp_submitToFinance = new radio("sp_submitToFinance");
		//		$sp_submitToFinance->setGroup("materialDebitedGroup");
		//		$sp_submitToFinance->setDataType("string");
		//		$sp_submitToFinance->setLength(5);
		//		$sp_submitToFinance->setArraySource(array(
		//			array('value' => 'Yes', 'display' => 'Yes'),
		//			array('value' => 'No', 'display' => 'No')
		//		));
		//		$sp_submitToFinance->setRowTitle("hold_invoice_issue_debit_note");
		//		$sp_submitToFinance->setValue("No");
		//		$sp_submitToFinance->setHelpId(80454);
		//		$sp_submitToFinance->setRequired(true);
		//		$sp_submitToFinance->setTable("complaint");
		//		if(isset($savedFields["sp_submitToFinance"]))
		//			$sp_submitToFinance->setValue($savedFields["sp_submitToFinance"]);
		//		else $sp_submitToFinance->setValue("No");
		//
		//		// Dependency
		//		$submitToFinanceDependency = new dependency();
		//		$submitToFinanceDependency->addRule(new rule('materialDebitedGroup', 'sp_submitToFinance', 'Yes'));
		//		$submitToFinanceDependency->setGroup('materialDebitedYes');
		//		$submitToFinanceDependency->setShow(true);
		//
		//		$sp_submitToFinance->addControllingDependency($submitToFinanceDependency);
		//		$materialDebitedGroup->add($sp_submitToFinance);
		//
		//		$sp_debitNumber = new textbox("sp_debitNumber");
		//		if(isset($savedFields["sp_debitNumber"]))
		//			$sp_debitNumber->setValue($savedFields["sp_debitNumber"]);
		//		$sp_debitNumber->setGroup("materialDebitedYes");
		//		$sp_debitNumber->setDataType("string");
		//		$sp_debitNumber->setLength(100);
		//		$sp_debitNumber->setRowTitle("debit_number");
		//		$sp_debitNumber->setHelpId(80455);
		//		$sp_debitNumber->setRequired(false);
		//		$sp_debitNumber->setTable("complaint");
		//		$materialDebitedYes->add($sp_debitNumber);
		//
		//		$sp_debitValue = new measurement("sp_debitValue");
		//		if(isset($savedFields["sp_debitValue_quantity"]) && isset($savedFields["sp_debitValue_measurement"])){
		//			$arr[0] = $savedFields["sp_debitValue_quantity"];
		//			$arr[1] = $savedFields["sp_debitValue_measurement"];
		//			$sp_debitValue->setValue($arr);
		//		}else $sp_debitValue->setMeasurement("EUR");
		//		$sp_debitValue->setGroup("materialDebitedYes");
		//		$sp_debitValue->setDataType("string");
		//		$sp_debitValue->setLength(50);
		//		$sp_debitValue->setHelpId(80456);
		//		$sp_debitValue->setRowTitle("debit_value");
		//		$sp_debitValue->setRequired(true);
		//		$sp_debitValue->setTable("complaint");
		//		$sp_debitValue->setXMLSource("./apps/complaints/xml/currency.xml");
		//		$materialDebitedYes->add($sp_debitValue);
		//
		//		$sp_debitDate = new calendar("sp_debitDate");
		//		if(isset($savedFields["sp_debitDate"]))
		//			$sp_debitDate->setValue($savedFields["sp_debitDate"]);
		//		$sp_debitDate->setGroup("materialDebitedYes");
		//		$sp_debitDate->setDataType("date");
		//		$sp_debitDate->setLength(30);
		//		$sp_debitDate->setHelpId(80457);
		//		$sp_debitDate->setRowTitle("debit_date");
		//		$sp_debitDate->setRequired(false);
		//		$sp_debitDate->setTable("complaint");
		//		$materialDebitedYes->add($sp_debitDate);
		//
		//		$sp_debitName = new textbox("sp_debitName");
		//		if(isset($savedFields["sp_debitName"]))
		//			$sp_debitName->setValue($savedFields["sp_debitName"]);
		//		$sp_debitName->setGroup("materialDebitedYes");
		//		$sp_debitName->setDataType("string");
		//		$sp_debitName->setLength(250);
		//		$sp_debitName->setHelpId(80458);
		//		$sp_debitName->setRowTitle("debit_name");
		//		$sp_debitName->setRequired(false);
		//		$sp_debitName->setTable("complaint");
		//		$materialDebitedYes->add($sp_debitName);




		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser2->add($submit);


		$carrierName = new textbox("carrierName");
		if(isset($savedFields["carrierName"]))
		$carrierName->setValue($savedFields["carrierName"]);
		$carrierName->setGroup("sitesGroup");
		$carrierName->setDataType("string");
		$carrierName->setLength(255);
		$carrierName->setRowTitle("carrier_name");
		$carrierName->setRequired(false);
		$carrierName->setVisible(false);
		$carrierName->setTable("complaint");
		$carrierName->setHelpId(8033);
		$initiation->add($carrierName);

		$this->form->add($initiation);

		$this->form->add($orderDetailsMulti);
		$this->form->add($intercoGroupYes);

		$this->form->add($typeOfComplaintGroup);
		$this->form->add($groupComplaint);
		$this->form->add($groupComplaintYes);
		$this->form->add($complaintDetails);
		$this->form->add($addSAPEmailYes);
		$this->form->add($complaintDetails2);
		$this->form->add($materialInvolvedGroup);
		$this->form->add($sapGroup);
		$this->form->add($materialGroupGroup);
		$this->form->add($materialBlockedGroup);
		$this->form->add($materialBlockedYes);
		$this->form->add($materialInvolvedYes);
		$this->form->add($complaintDetails2);
		$this->form->add($sampleSentGroup);
		$this->form->add($sampleSentYes);
		//		$this->form->add($materialDebitedGroup);
		//		$this->form->add($materialDebitedYes);
		$this->form->add($actionsGroup2);
		//		$this->form->add($ccComplaintGroup);
		$this->form->add($sendToUser);
		//$this->form->add($scapaInvoiceYesGroup);
		$this->form->add($materialDebitedGroup);
		$this->form->add($materialDebitedYes);
		$this->form->add($sendToUser2);


	}

	public function defineQualityForm()
	{
		$savedFields = array();

		if(isset($_REQUEST["sfID"])){
			$this->sfID = $_REQUEST["sfID"];
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			while ($fields = mysql_fetch_array($dataset)){
				$savedFields = unserialize($fields["sfValue"]);
			}
		}
		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		if(isset($_REQUEST['complaint']))
		{
			$cfi = $_REQUEST['complaint'];
		}
		elseif(isset($_REQUEST['id']))
		{
			$cfi = $_REQUEST['id'];
		}
		else
		{
			$cfi = "";
		}

		// define the actual form
		$this->form = new form("complaint" . $cfi);
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$initiation->setBorder(false);
		$submitOnBehalfGroup = new group("submitOnBehalfGroup");
		$submitOnBehalfGroup->setBorder(false);
		$groupComplaint = new group("groupComplaint");
		$groupComplaint->setBorder(false);
		$groupComplaintYes = new group("groupComplaintYes");
		$groupComplaintYes->setBorder(false);
		$sendToUser2 = new group("sendToUser2");
		$sendToUser2->setBorder(false);
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
		$typeOfComplaintGroup->setBorder(false);
		$complaintDetails = new group("complaintDetails");
		$complaintDetails->setBorder(false);
		$lineStoppageGroup = new group("lineStoppageGroup");
		$lineStoppageGroup->setBorder(false);
		$lineStoppageYes = new group("lineStoppageYes");
		$lineStoppageYes->setBorder(false);
		$complaintDetails2 = new group("complaintDetails2");
		$complaintDetails2->setBorder(false);
		$materialInvolvedGroup = new group("materialInvolvedGroup");
		$materialInvolvedGroup->setBorder(false);
		$materialInvolvedYes = new group("materialInvolvedYes");
		$materialInvolvedYes->setBorder(false);

		$sendToUser22 = new group("sendToUser22");
		$sendToUser22->setBorder(false);

		$sapGroup = new multiplegroup("sapGroup");
		$sapGroup->setTitle("Material Number");
		$sapGroup->setNextAction("complaint");
		$sapGroup->setAnchorRef("sapGroupAnch");
		$sapGroup->setTable("sapItemNumber");
		$sapGroup->setForeignKey("complaintId");

		$materialGroupGroup = new multiplegroup("materialGroupGroup");
		$materialGroupGroup->setTitle("Material Group");
		$materialGroupGroup->setNextAction("complaint");
		$materialGroupGroup->setAnchorRef("materialGroupGroupAnch");
		$materialGroupGroup->setTable("materialGroup");
		$materialGroupGroup->setForeignKey("complaintId");

//		$sapItemNumberGroupedTGroup = new multiplegroup("sapItemNumberGroupedTGroup");
//		$sapItemNumberGroupedTGroup->setTitle("Item Fields");
//		$sapItemNumberGroupedTGroup->setNextAction("complaint");
//		$sapItemNumberGroupedTGroup->setAnchorRef("sapItemNumberGroupedTGroup");
//		$sapItemNumberGroupedTGroup->setTable("sapItemNumberGroupedTGroup");
//		$sapItemNumberGroupedTGroup->setForeignKey("complaintId");

		$complaintDetails3 = new group("complaintDetails3");
		$complaintDetails3->setBorder(false);
		$materialBlockedGroup = new group("materialBlockedGroup");
		$materialBlockedGroup->setBorder(false);
		$materialBlockedYes = new group("materialBlockedYes");
		$materialBlockedYes->setBorder(false);


		$id = new textbox("id");
		if(isset($savedFields["id"]))
		$id->setValue($savedFields["id"]);
		$id->setTable("complaint");
		$id->setVisible(false);
		$id->setIgnore(true);
		$id->setDataType("number");
		$initiation->add($id);

		$status = new textbox("status");
		if(isset($savedFields["status"]))
		$status->setValue($savedFields["status"]);
		$status->setTable("complaint");
		$status->setVisible(false);
		$status->setIgnore(false);
		$status->setDataType("string");
		$status->setValue("complaint");
		$initiation->add($status);

		$openDate = new textbox("openDate");
		if(isset($savedFields["openDate"]))
		$openDate->setValue($savedFields["openDate"]);
		$openDate->setTable("complaint");
		$openDate->setVisible(false);
		$openDate->setIgnore(false);
		$openDate->setDataType("text");
		$initiation->add($openDate);

		$owner = new textbox("owner");
		if(isset($savedFields["owner"]))
		$owner->setValue($savedFields["owner"]);
		$owner->setTable("complaint");
		$owner->setVisible(false);
		$owner->setIgnore(false);
		$owner->setDataType("string");
		$initiation->add($owner);

		$complaintLocation = new radio("complaintLocation");
		$complaintLocation->setTable("complaint");
		$complaintLocation->setLength(20);
		$complaintLocation->setArraySource(array(
		array('value' => 'european', 'display' => 'European'),
		array('value' => 'american', 'display' => 'American'),
		array('value' => 'malaysian', 'display' => 'Malaysian')
		));
		if(isset($savedFields["complaintLocation"]))
		$complaintLocation->setValue($savedFields["complaintLocation"]);
		$complaintLocation->setVisible(false);
		$complaintLocation->setIgnore(false);
		$complaintLocation->setDataType("string");
		$initiation->add($complaintLocation);

		$typeOfComplaint = new dropdown("typeOfComplaint");
		$typeOfComplaint->setGroup("typeOfComplaintGroup");
		$typeOfComplaint->setDataType("string");
		$typeOfComplaint->setXMLSource("./apps/complaints/xml/complaintType.xml");
		$typeOfComplaint->setRowTitle("complaint_type");
		$typeOfComplaint->setRequired(false);
		$typeOfComplaint->setTable("complaint");
		$typeOfComplaint->setVisible(false);
		$typeOfComplaint->setHelpId(8199);
		if(isset($savedFields["typeOfComplaint"]))
		$typeOfComplaint->setValue($savedFields["typeOfComplaint"]);
		else $typeOfComplaint->setValue("quality_complaint");
		$typeOfComplaintGroup->add($typeOfComplaint);

		$submitOnBehalf = new radio("submitOnBehalf");
		$submitOnBehalf->setGroup("initiation");
		$submitOnBehalf->setDataType("string");
		$submitOnBehalf->setLength(5);
		$submitOnBehalf->setArraySource(array(
		array('value' => 'yes', 'display' => 'Yes'),
		array('value' => 'no', 'display' => 'No')
		));
		$submitOnBehalf->setRowTitle("submit_on_behalf_of_someone");
		//		$submitOnBehalf->setLabel("Complaint Creator Details");
		$submitOnBehalf->setRequired(true);
		if(isset($savedFields["submitOnBehalf"]))
		$submitOnBehalf->setValue($savedFields["submitOnBehalf"]);
		else $submitOnBehalf->setValue("no");
		$submitOnBehalf->setTable("complaint");
		$submitOnBehalf->setHelpId(8000);

		// Dependency
		$behalfOfGroup = new dependency();
		$behalfOfGroup->addRule(new rule('initiation', 'submitOnBehalf', 'yes'));
		$behalfOfGroup->setGroup('submitOnBehalfGroup');
		$behalfOfGroup->setShow(true);

		$submitOnBehalf->addControllingDependency($behalfOfGroup);
		$initiation->add($submitOnBehalf);

		$internalSalesName = new autocomplete("internalSalesName");
		if(isset($savedFields["internalSalesName"]))
		$internalSalesName->setValue($savedFields["internalSalesName"]);
		$internalSalesName->setGroup("submitOnBehalfGroup");
		$internalSalesName->setDataType("string");
		$internalSalesName->setLength(30);
		$internalSalesName->setUrl("/apps/complaints/ajax/internalSalesName?");
		$internalSalesName->setRowTitle("internal_sales_name");
		$internalSalesName->setRequired(true);
		$internalSalesName->setTable("complaint");
		$internalSalesName->setHelpId(8100);
		$submitOnBehalfGroup->add($internalSalesName);

		$salesOffice = new dropdown("salesOffice");
		if(isset($savedFields["salesOffice"]))
		$salesOffice->setValue($savedFields["salesOffice"]);
		$salesOffice->setGroup("submitOnBehalfGroup");
		$salesOffice->setDataType("string");
		$salesOffice->setLength(30);
		$salesOffice->setXMLSource("./apps/complaints/xml/sites.xml");
		$salesOffice->setRowTitle("sales_office");
		$salesOffice->setRequired(false);
		$salesOffice->setVisible(false);
		$salesOffice->setTable("complaint");
		$salesOffice->setHelpId(8178);
		$submitOnBehalfGroup->add($salesOffice);

		$siteAtOrigin = new dropdown("siteAtOrigin");
		if(isset($savedFields["siteAtOrigin"]))
		$siteAtOrigin->setValue($savedFields["siteAtOrigin"]);
		$siteAtOrigin->setGroup("submitOnBehalfGroup");
		$siteAtOrigin->setDataType("string");
		$siteAtOrigin->setLength(30);
		$siteAtOrigin->setXMLSource("./apps/complaints/xml/sites.xml");
		$siteAtOrigin->setRowTitle("site_at_origin");
		$siteAtOrigin->setRequired(false);
		$siteAtOrigin->setVisible(false);
		$siteAtOrigin->setTable("complaint");
		$siteAtOrigin->setHelpId(8178);
		$submitOnBehalfGroup->add($siteAtOrigin);


		$groupAComplaint = new radio("groupAComplaint");
		$groupAComplaint->setGroup("groupComplaint");
		$groupAComplaint->setDataType("string");
		$groupAComplaint->setLength(5);
		$groupAComplaint->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$groupAComplaint->setRowTitle("group_with_another_complaint");
		//		$groupAComplaint->setLabel("Group A Complaint");
		$groupAComplaint->setRequired(true);
		$groupAComplaint->setTable("complaint");
		if(isset($savedFields["groupAComplaint"]))
		{
			$groupAComplaint->setValue($savedFields["groupAComplaint"]); // Done properly ...
		}
		else
		{
			$groupAComplaint->setValue("No");
		}
		$groupAComplaint->setHelpId(8188);

		// Dependency
		$groupComplaintDependency = new dependency();
		$groupComplaintDependency->addRule(new rule('groupComplaint', 'groupAComplaint', 'Yes'));
		$groupComplaintDependency->setGroup('groupComplaintYes');
		$groupComplaintDependency->setShow(true);

		$groupAComplaint->addControllingDependency($groupComplaintDependency);
		$groupComplaint->add($groupAComplaint);

		$groupedComplaintId = new autocomplete("groupedComplaintId");
		if(isset($savedFields["groupedComplaintId"]))
		$groupedComplaintId->setValue($savedFields["groupedComplaintId"]);
		$groupedComplaintId->setGroup("groupComplaintYes");
		$groupedComplaintId->setDataType("string");
		$groupedComplaintId->setLength(30);
		$groupedComplaintId->setUrl("/apps/complaints/ajax/groupComplaint?");
		$groupedComplaintId->setRowTitle("grouped_complaint_id");
		$groupedComplaintId->setRequired(true);
		$groupedComplaintId->setTable("complaint");
		$groupedComplaintId->setHelpId(8159);
		$groupComplaintYes->add($groupedComplaintId);

		$customerComplaintDate = new calendar("customerComplaintDate");
		if(isset($savedFields["customerComplaintDate"]))
		$customerComplaintDate->setValue($savedFields["customerComplaintDate"]);
		$customerComplaintDate->setGroup("complaintDetails");
		$customerComplaintDate->setDataType("date");
		$customerComplaintDate->setLength(30);
		$customerComplaintDate->setRowTitle("scapa_complaint_date");
		$customerComplaintDate->setErrorMessage("textbox_date_error_future");
		$customerComplaintDate->setRequired(true);
		$customerComplaintDate->setTable("complaint");
		$customerComplaintDate->setExtTable("complaintExt");
		$customerComplaintDate->setHelpId(8003);
		$complaintDetails->add($customerComplaintDate);

		//		// do be removed ...

		$sampleReceptionDate = new calendar("sampleReceptionDate");
		if(isset($savedFields["sampleReceptionDate"]))
		$sampleReceptionDate->setValue($savedFields["sampleReceptionDate"]);
		$sampleReceptionDate->setGroup("complaintDetails");
		$sampleReceptionDate->setDataType("date");
		$sampleReceptionDate->setLength(255);
		$sampleReceptionDate->setRowTitle("reception_date");
		$sampleReceptionDate->setRequired(false);
		$sampleReceptionDate->setVisible(false);
		$sampleReceptionDate->setIgnore(true);
		$sampleReceptionDate->setValue("to be removed");
		$sampleReceptionDate->setTable("complaint");
		$sampleReceptionDate->setHelpId(8040);
		$complaintDetails->add($sampleReceptionDate);

		$sp_siteConcerned = new dropdown("sp_siteConcerned");
		$sp_siteConcerned->setGroup("complaintDetails");
		$sp_siteConcerned->setDataType("string");
		$sp_siteConcerned->setLength(50);
		$sp_siteConcerned->setTranslate(true);
		$sp_siteConcerned->setErrorMessage("dropdown_error");
		$sp_siteConcerned->setRowTitle("site_concerned");
		$sp_siteConcerned->setRequired(true);
		if(isset($savedFields["sp_siteConcerned"]))
		$sp_siteConcerned->setValue($savedFields["sp_siteConcerned"]);
		else $sp_siteConcerned->setValue("");
		$sp_siteConcerned->setXMLSource("./apps/complaints/xml/sites.xml");
		$sp_siteConcerned->setOnChange("update_where_error_occurred_dropdown()");
		$sp_siteConcerned->setTable("complaint");
		$sp_siteConcerned->setHelpId(8001);
		$complaintDetails->add($sp_siteConcerned);

		$whereErrorOccured = new dropdown("whereErrorOccured");
		$whereErrorOccured->setGroup("complaintDetails");
		$whereErrorOccured->setDataType("string");
		$whereErrorOccured->setLength(50);
		$whereErrorOccured->setTranslate(true);
		$whereErrorOccured->setErrorMessage("dropdown_error");
		$whereErrorOccured->setRowTitle("where_error_detected");
		$whereErrorOccured->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = '" . $this->getSiteConcerned() . "' AND field = 'attributableProcess' ORDER BY `details` ASC");
		$whereErrorOccured->setRequired(true);
		if(isset($savedFields["whereErrorOccured"]))
		$whereErrorOccured->setValue($savedFields["whereErrorOccured"]);
		else $whereErrorOccured->setValue("");
		//$whereErrorOccured->setXMLSource("./apps/complaints/xml/sites.xml");
		$whereErrorOccured->setTable("complaint");
		$whereErrorOccured->setOnChange("show_details_internal_complaint()");
		$whereErrorOccured->setHelpId(8001);
		$complaintDetails->add($whereErrorOccured);

		$others = new textarea("others");
		if(isset($savedFields["others"]))
		$others->setValue($savedFields["others"]);
		$others->setGroup("complaintDetails");
		$others->setDataType("text");
		$others->setLength(255);
		$others->setRowTitle("details");
		$others->setTable("complaint");
		$others->setHelpId(8040);
		$complaintDetails->add($others);

		$howErrorDetected = new dropdown("howErrorDetected");
		$howErrorDetected->setGroup("complaintDetails");
		$howErrorDetected->setDataType("string");
		$howErrorDetected->setLength(50);
		$howErrorDetected->setTranslate(true);
		$howErrorDetected->setErrorMessage("dropdown_error");
		$howErrorDetected->setRowTitle("how_error_detected");
		$howErrorDetected->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'quality' AND field = 'howErrorDetected' ORDER BY `details` ASC");
		$howErrorDetected->setRequired(true);
		//		$howErrorDetected->setLabel("Complaint Details");
		if(isset($savedFields["howErrorDetected"]))
		$howErrorDetected->setValue($savedFields["howErrorDetected"]);
		else $howErrorDetected->setValue("Incident");
		$howErrorDetected->setTable("complaint");
		$howErrorDetected->setHelpId(8001);
		$complaintDetails->add($howErrorDetected);

		$category = new dropdown("category");
		if(isset($savedFields["category"]))
		$category->setValue($savedFields["category"]);
		$category->setGroup("complaintDetails");
		$category->setDataType("string");
		$category->setLength(255);
		$category->setRowTitle("apparent_category");
		$category->setRequired(true);
		$category->setErrorMessage("dropdown_error");
		$category->setTranslate(true);
		$category->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'supplier' AND field = 'category' ORDER BY `details` ASC");
		$category->setTable("complaint");
		$category->setHelpId(8005);
		$complaintDetails->add($category);

		$qu_foundBy = new textbox("qu_foundBy");
		if(isset($savedFields["qu_foundBy"]))
		$qu_foundBy->setValue($savedFields["qu_foundBy"]);
		$qu_foundBy->setGroup("complaintDetails");
		$qu_foundBy->setDataType("string");
		$qu_foundBy->setLength(255);
		$qu_foundBy->setRowTitle("qu_found_By");
		$qu_foundBy->setTable("complaint");
		$qu_foundBy->setHelpId(8040);
		$complaintDetails->add($qu_foundBy);

		$internalReferenceNumber = new textbox("internalReferenceNumber");
		if(isset($savedFields["internalReferenceNumber"]))
		$internalReferenceNumber->setValue($savedFields["internalReferenceNumber"]);
		$internalReferenceNumber->setGroup("complaintDetails");
		$internalReferenceNumber->setDataType("string");
		$internalReferenceNumber->setLength(255);
		$internalReferenceNumber->setRowTitle("internal_reference_number");
		$internalReferenceNumber->setTable("complaint");
		$internalReferenceNumber->setHelpId(8040);
		$complaintDetails->add($internalReferenceNumber);

		$clauseEffected = new textbox("clauseEffected");
		if(isset($savedFields["clauseEffected"]))
		$clauseEffected->setValue($savedFields["clauseEffected"]);
		$clauseEffected->setGroup("complaintDetails");
		$clauseEffected->setDataType("string");
		$clauseEffected->setLength(255);
		$clauseEffected->setRowTitle("clause_effected");
		$clauseEffected->setTable("complaint");
		$clauseEffected->setHelpId(8040);
		$complaintDetails->add($clauseEffected);

		$severity = new radio("severity");
		$severity->setGroup("creditGroup");
		$severity->setDataType("string");
		$severity->setLength(5);
		$severity->setArraySource(array(
		array('value' => 'High', 'display' => 'High'),
		array('value' => 'Low', 'display' => 'Low')
		));
		$severity->setRowTitle("severity");
		$severity->setRequired(true);
		if(isset($savedFields["severity"]))
		$severity->setValue($savedFields["severity"]);
		else $severity->setValue("High");
		$severity->setTable("complaint");
		$severity->setHelpId(8134);
		$complaintDetails->add($severity);

		$lineStoppage = new radio("lineStoppage");
		$lineStoppage->setGroup("creditGroup");
		$lineStoppage->setDataType("string");
		$lineStoppage->setLength(5);
		$lineStoppage->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$lineStoppage->setRowTitle("line_stoppage");
		$lineStoppage->setRequired(true);
		if(isset($savedFields["lineStoppage"]))
		$lineStoppage->setValue($savedFields["lineStoppage"]);
		else $lineStoppage->setValue("No");
		$lineStoppage->setTable("complaint");
		$lineStoppage->setHelpId(8135);

		// Dependency
		$lineStoppage_dependency = new dependency();
		$lineStoppage_dependency->addRule(new rule('lineStoppageGroup', 'lineStoppage', 'Yes'));
		$lineStoppage_dependency->setGroup('lineStoppageYes');
		$lineStoppage_dependency->setShow(true);

		$lineStoppage->addControllingDependency($lineStoppage_dependency);
		$lineStoppageGroup->add($lineStoppage);

		$lineStoppageDetails = new textarea("lineStoppageDetails");
		if(isset($savedFields["lineStoppageDetails"]))
		$lineStoppageDetails->setValue($savedFields["lineStoppageDetails"]);
		$lineStoppageDetails->setGroup("lineStoppageYes");
		$lineStoppageDetails->setDataType("text");
		$lineStoppageDetails->setRowTitle("details");
		$lineStoppageDetails->setRequired(false);
		$lineStoppageDetails->setTable("complaint");
		$lineStoppageDetails->setHelpId(8198);
		$lineStoppageYes->add($lineStoppageDetails);


		$problemDescription = new textarea("problemDescription");
		if(isset($savedFields["problemDescription"]))
		$problemDescription->setValue($savedFields["problemDescription"]);
		$problemDescription->setGroup("complaintDetails2");
		$problemDescription->setDataType("text");
		$problemDescription->setRowTitle("problem_description");
		$problemDescription->setRequired(true);
		$problemDescription->setTable("complaint");
		$problemDescription->setHelpId(8035);
		$complaintDetails2->add($problemDescription);

		//		$containmentAction = new textarea("containmentAction");
		//		if(isset($savedFields["containmentAction"]))
		//			$containmentAction->setValue($savedFields["containmentAction"]);
		//		$containmentAction->setGroup("complaintDetails2");
		//		$containmentAction->setDataType("text");
		//		$containmentAction->setRowTitle("containment_action");
		//		$containmentAction->setRequired(false);
		//		$containmentAction->setTable("complaint");
		//		$containmentAction->setHelpId(8035);
		//		$complaintDetails2->add($containmentAction);

		$requestedAction = new textarea("requestedAction");
		if(isset($savedFields["requestedAction"]))
		$requestedAction->setValue($savedFields["requestedAction"]);
		$requestedAction->setGroup("complaintDetails2");
		$requestedAction->setDataType("text");
		$requestedAction->setRowTitle("requested_action");
		$requestedAction->setRequired(false);
		$requestedAction->setTable("complaint");
		$requestedAction->setHelpId(8035);
		$complaintDetails2->add($requestedAction);

		$attachment = new attachment("attachment");
		//if(isset($savedFields["attachment"]))
		//$attachment->setValue($savedFields["attachment"]);
		$attachment->setTempFileLocation("/apps/complaints/tmp");
		$attachment->setFinalFileLocation("/apps/complaints/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setGroup("attachmentGroup");
		$attachment->setHelpId(11);
		$attachment->setNextAction("complaint");
		$attachment->setAnchorRef("attachment");
		$complaintDetails2->add($attachment);

		$qu_materialInvolved = new radio("qu_materialInvolved");
		$qu_materialInvolved->setGroup("materialInvolvedGroup");
		$qu_materialInvolved->setDataType("string");
		$qu_materialInvolved->setLength(5);
		$qu_materialInvolved->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$qu_materialInvolved->setRowTitle("material_involved");
		$qu_materialInvolved->setValue("Yes");
		$qu_materialInvolved->setRequired(true);
		$qu_materialInvolved->setTable("complaint");
		if(isset($savedFields["qu_materialInvolved"]))
		$qu_materialInvolved->setValue($savedFields["qu_materialInvolved"]);
		else $qu_materialInvolved->setValue("Yes");

		// Dependency
		$materialInvolvedDependency = new dependency();
		$materialInvolvedDependency->addRule(new rule('materialInvolvedGroup', 'qu_materialInvolved', 'Yes'));
		$materialInvolvedDependency->setGroup('materialInvolvedYes');
		$materialInvolvedDependency->setShow(true);

		$materialInvolvedDependency2 = new dependency();
		$materialInvolvedDependency2->addRule(new rule('materialInvolvedGroup', 'qu_materialInvolved', 'Yes'));
		$materialInvolvedDependency2->setGroup('sapGroup');
		$materialInvolvedDependency2->setShow(true);

		$materialInvolvedDependency3 = new dependency();
		$materialInvolvedDependency3->addRule(new rule('materialInvolvedGroup', 'qu_materialInvolved', 'Yes'));
		$materialInvolvedDependency3->setGroup('materialGroupGroup');
		$materialInvolvedDependency3->setShow(true);

		$materialInvolvedDependency4 = new dependency();
		$materialInvolvedDependency4->addRule(new rule('materialInvolvedGroup', 'qu_materialInvolved', 'Yes'));
		$materialInvolvedDependency4->setGroup('complaintDetails3');
		$materialInvolvedDependency4->setShow(true);

		$materialInvolvedDependency5 = new dependency();
		$materialInvolvedDependency5->addRule(new rule('materialInvolvedGroup', 'qu_materialInvolved', 'Yes'));
		$materialInvolvedDependency5->setGroup('materialBlockedGroup');
		$materialInvolvedDependency5->setShow(true);

		$qu_materialInvolved->addControllingDependency($materialInvolvedDependency);
		$qu_materialInvolved->addControllingDependency($materialInvolvedDependency2);
		$qu_materialInvolved->addControllingDependency($materialInvolvedDependency3);
		$qu_materialInvolved->addControllingDependency($materialInvolvedDependency4);
		$qu_materialInvolved->addControllingDependency($materialInvolvedDependency5);
		$materialInvolvedGroup->add($qu_materialInvolved);

		$manufacturingNumber = new textbox("manufacturingNumber");
		if(isset($savedFields["manufacturingNumber"]))
		$manufacturingNumber->setValue($savedFields["manufacturingNumber"]);
		$manufacturingNumber->setGroup("materialInvolvedYes");
		$manufacturingNumber->setDataType("string");
		$manufacturingNumber->setLength(255);
		$manufacturingNumber->setRowTitle("manufacturing_number");
		$manufacturingNumber->setErrorMessage("dropdown_error");
		//$manufacturingNumber->setTranslate(true);
		//$manufacturingNumber->setSQLSource("complaints","SELECT `details` AS name, `details` AS value FROM `dropdownsData` WHERE site = 'supplier' AND field = 'category' ORDER BY `details` ASC");
		$manufacturingNumber->setTable("complaint");
		$manufacturingNumber->setHelpId(8005);
		$materialInvolvedYes->add($manufacturingNumber);

		$dateOfManufacturing = new calendar("dateOfManufacturing");
		if(isset($savedFields["dateOfManufacturing"]))
		$dateOfManufacturing->setValue($savedFields["dateOfManufacturing"]);
		$dateOfManufacturing->setGroup("materialInvolvedYes");
		$dateOfManufacturing->setDataType("date");
		$dateOfManufacturing->setLength(255);
		$dateOfManufacturing->setRowTitle("date_of_manufacturing");
		$dateOfManufacturing->setTable("complaint");
		$dateOfManufacturing->setHelpId(8040);
		$materialInvolvedYes->add($dateOfManufacturing);

		$batchNumber = new textbox("batchNumber");
		if(isset($savedFields["batchNumber"]))
		$batchNumber->setValue($savedFields["batchNumber"]);
		$batchNumber->setGroup("materialInvolvedYes");
		$batchNumber->setDataType("string");
		$batchNumber->setRowTitle("scapa_batch_number");
		$batchNumber->setTable("complaint");
		$batchNumber->setHelpId(8029);
		$materialInvolvedYes->add($batchNumber);

		$supplierBatchNumber = new textbox("supplierBatchNumber");
		if(isset($savedFields["supplierBatchNumber"]))
		$supplierBatchNumber->setValue($savedFields["supplierBatchNumber"]);
		$supplierBatchNumber->setGroup("materialInvolvedYes");
		$supplierBatchNumber->setDataType("string");
		$supplierBatchNumber->setRowTitle("supplier_batch_number");
		$supplierBatchNumber->setRequired(false);
		$supplierBatchNumber->setVisible(true);
		$supplierBatchNumber->setTable("complaint");
		$supplierBatchNumber->setHelpId(80291);
		$materialInvolvedYes->add($supplierBatchNumber);

		$lotNo = new textbox("lotNo");
		if(isset($savedFields["lotNo"]))
		$lotNo->setValue($savedFields["lotNo"]);
		$lotNo->setGroup("materialInvolvedYes");
		$lotNo->setDataType("string");
		$lotNo->setRowTitle("lot_no");
		$lotNo->setTable("complaint");
		$lotNo->setHelpId(8029);
		$materialInvolvedYes->add($lotNo);

		$qu_materialLocation = new textbox("qu_materialLocation");
		if(isset($savedFields["qu_materialLocation"]))
		$qu_materialLocation->setValue($savedFields["qu_materialLocation"]);
		$qu_materialLocation->setGroup("materialInvolvedYes");
		$qu_materialLocation->setDataType("string");
		$qu_materialLocation->setRowTitle("qu_material_location");
		$qu_materialLocation->setRequired(false);
		$qu_materialLocation->setTable("complaint");
		$materialInvolvedYes->add($qu_materialLocation);

		if(!isset($savedFields["0|sapItemNumber"]))
		{//the first one will always need to be set if its saved

			$sapItemNumber = new autocomplete("sapItemNumber");
			if(isset($savedFields["0|sapItemNumber"]))
			$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
			$sapItemNumber->setGroup("sapGroup");
			$sapItemNumber->setDataType("textMinLength");
			$sapItemNumber->setErrorMessage("field_error");
			$sapItemNumber->setUrl("/apps/complaints/ajax/sapItemNumber?");
			$sapItemNumber->setMinLength(4);
			$sapItemNumber->setRowTitle("sap_item_number");
			$sapItemNumber->setRequired(true);
			$sapItemNumber->setTable("sapItemNumber");
			$sapItemNumber->setHelpId(8022);
			$sapGroup->add($sapItemNumber);

			$sapItemNumberMaterialGroup = new textbox("sapItemNumberMaterialGroup");
			if(isset($savedFields["0|sapItemNumberMaterialGroup"]))
			$sapItemNumberMaterialGroup->setValue($savedFields["0|sapItemNumberMaterialGroup"]);
			$sapItemNumberMaterialGroup->setGroup("sapGroup");
			$sapItemNumberMaterialGroup->setDataType("string");
			$sapItemNumberMaterialGroup->setLength(255);
			$sapItemNumberMaterialGroup->setRowTitle("material_group");
			$sapItemNumberMaterialGroup->setRequired(false);
			$sapItemNumberMaterialGroup->setTable("sapItemNumber");
			$sapItemNumberMaterialGroup->setHelpId(802224234);
			$sapGroup->add($sapItemNumberMaterialGroup);

			$sapItemNumberProductDescription = new textarea("sapItemNumberProductDescription");
			if(isset($savedFields["0|sapItemNumberProductDescription"]))
			$sapItemNumberProductDescription->setValue($savedFields["0|sapItemNumberProductDescription"]);
			$sapItemNumberProductDescription->setGroup("sapGroup");
			$sapItemNumberProductDescription->setDataType("text");
			$sapItemNumberProductDescription->setRowTitle("material_description");
			$sapItemNumberProductDescription->setRequired(false);
			$sapItemNumberProductDescription->setTable("sapItemNumber");
			$sapItemNumberProductDescription->setHelpId(8022242343);
			$sapGroup->add($sapItemNumberProductDescription);

			$sapItemNumberColour = new textbox("sapItemNumberColour");
			if(isset($savedFields["0|sapItemNumberColour"]))
			$sapItemNumberColour->setValue($savedFields["0|sapItemNumberColour"]);
			$sapItemNumberColour->setGroup("sapGroup");
			$sapItemNumberColour->setDataType("string");
			$sapItemNumberColour->setRowTitle("colour");
			$sapItemNumberColour->setRequired(false);
			$sapItemNumberColour->setTable("sapItemNumber");
			$sapItemNumberColour->setHelpId(80222423467546);
			$sapGroup->add($sapItemNumberColour);

			$sapItemNumberBatchNumber = new textbox("sapItemNumberBatchNumber");
			if(isset($savedFields["0|sapItemNumberBatchNumber"]))
			$sapItemNumberBatchNumber->setValue($savedFields["0|sapItemNumberBatchNumber"]);
			$sapItemNumberBatchNumber->setGroup("sapGroup");
			$sapItemNumberBatchNumber->setDataType("string");
			$sapItemNumberBatchNumber->setRowTitle("scapa_batch_number");
			$sapItemNumberBatchNumber->setTable("sapItemNumber");
			$sapItemNumberBatchNumber->setHelpId(80222423467546234);
			$sapGroup->add($sapItemNumberBatchNumber);

			$sapItemNumberComplaintCostNew = new textbox("sapItemNumberComplaintCostNew");
			if(isset($savedFields["0|sapItemNumberComplaintCostNew"]))
			$sapItemNumberComplaintCostNew->setValue($savedFields["0|sapItemNumberComplaintCostNew"]);
			$sapItemNumberComplaintCostNew->setGroup("sapGroup");
			$sapItemNumberComplaintCostNew->setDataType("string");
			$sapItemNumberComplaintCostNew->setErrorMessage("number_field");
			$sapItemNumberComplaintCostNew->setLength(255);
			$sapItemNumberComplaintCostNew->setRowTitle("complaint_cost_value");
			$sapItemNumberComplaintCostNew->setRequired(false);
			$sapItemNumberComplaintCostNew->setTable("sapItemNumber");
			$sapItemNumberComplaintCostNew->setHelpId(80222423467546234234324);
			$sapGroup->add($sapItemNumberComplaintCostNew);

			$sapItemNumberComplaintCostNewUOM = new dropdown("sapItemNumberComplaintCostNewUOM");
			if(isset($savedFields["0|sapItemNumberComplaintCostNewUOM"]))
			$sapItemNumberComplaintCostNewUOM->setValue($savedFields["0|sapItemNumberComplaintCostNewUOM"]);
			$sapItemNumberComplaintCostNewUOM->setGroup("sapGroup");
			$sapItemNumberComplaintCostNewUOM->setDataType("string");
			$sapItemNumberComplaintCostNewUOM->setLength(255);
			$sapItemNumberComplaintCostNewUOM->setRowTitle("complaint_cost_uom");
			$sapItemNumberComplaintCostNewUOM->setRequired(false);
			$sapItemNumberComplaintCostNewUOM->setErrorMessage("dropdown_error");
			$sapItemNumberComplaintCostNewUOM->setTranslate(true);
			$sapItemNumberComplaintCostNewUOM->setXMLSource("./apps/complaints/xml/currency.xml");
			$sapItemNumberComplaintCostNewUOM->setTable("sapItemNumber");
			$sapItemNumberComplaintCostNewUOM->setHelpId(8022242346754623423434);
			$sapGroup->add($sapItemNumberComplaintCostNewUOM);

			$sapItemNumberQuantityUnderComplaintNew = new textbox("sapItemNumberQuantityUnderComplaintNew");
			if(isset($savedFields["0|sapItemNumberQuantityUnderComplaintNew"]))
			$sapItemNumberQuantityUnderComplaintNew->setValue($savedFields["0|sapItemNumberQuantityUnderComplaintNew"]);
			$sapItemNumberQuantityUnderComplaintNew->setGroup("sapGroup");
			$sapItemNumberQuantityUnderComplaintNew->setDataType("string");
			$sapItemNumberQuantityUnderComplaintNew->setErrorMessage("number_field");
			$sapItemNumberQuantityUnderComplaintNew->setLength(255);
			$sapItemNumberQuantityUnderComplaintNew->setRowTitle("quantity_under_complaint_value");
			$sapItemNumberQuantityUnderComplaintNew->setRequired(false);
			$sapItemNumberQuantityUnderComplaintNew->setTable("sapItemNumber");
			$sapItemNumberQuantityUnderComplaintNew->setHelpId(80222423467546234234);
			$sapGroup->add($sapItemNumberQuantityUnderComplaintNew);

			$sapItemNumberQuantityUnderComplaintNewUOM = new dropdown("sapItemNumberQuantityUnderComplaintNewUOM");
			if(isset($savedFields["0|sapItemNumberQuantityUnderComplaintNewUOM"]))
			$sapItemNumberQuantityUnderComplaintNewUOM->setValue($savedFields["0|sapItemNumberQuantityUnderComplaintNewUOM"]);
			$sapItemNumberQuantityUnderComplaintNewUOM->setGroup("sapGroup");
			$sapItemNumberQuantityUnderComplaintNewUOM->setDataType("string");
			$sapItemNumberQuantityUnderComplaintNewUOM->setLength(255);
			$sapItemNumberQuantityUnderComplaintNewUOM->setRowTitle("quantity_under_complaint_uom");
			$sapItemNumberQuantityUnderComplaintNewUOM->setRequired(false);
			$sapItemNumberQuantityUnderComplaintNewUOM->setErrorMessage("dropdown_error");
			$sapItemNumberQuantityUnderComplaintNewUOM->setTranslate(true);
			$sapItemNumberQuantityUnderComplaintNewUOM->setXMLSource("./apps/complaints/xml/uom.xml");
			$sapItemNumberQuantityUnderComplaintNewUOM->setTable("sapItemNumber");
			$sapItemNumberQuantityUnderComplaintNewUOM->setHelpId(8022242346754623423434);
			$sapGroup->add($sapItemNumberQuantityUnderComplaintNewUOM);

//			$sapItemNumberDimensionThicknessNew = new textbox("sapItemNumberDimensionThicknessNew");
//			if(isset($savedFields["0|sapItemNumberDimensionThicknessNew"]))
//			$sapItemNumberDimensionThicknessNew->setValue($savedFields["0|sapItemNumberDimensionThicknessNew"]);
//			$sapItemNumberDimensionThicknessNew->setGroup("sapGroup");
//			$sapItemNumberDimensionThicknessNew->setDataType("number");
//			$sapItemNumberDimensionThicknessNew->setErrorMessage("number_field");
//			$sapItemNumberDimensionThicknessNew->setLength(255);
//			$sapItemNumberDimensionThicknessNew->setRowTitle("dimension_thickness_value");
//			$sapItemNumberDimensionThicknessNew->setRequired(false);
//			$sapItemNumberDimensionThicknessNew->setTable("sapItemNumber");
//			$sapItemNumberDimensionThicknessNew->setHelpId(8022242346754623476);
//			$sapGroup->add($sapItemNumberDimensionThicknessNew);
//
//			$sapItemNumberDimensionThicknessNewUOM = new dropdown("sapItemNumberDimensionThicknessNewUOM");
//			if(isset($savedFields["0|sapItemNumberDimensionThicknessNewUOM"]))
//			$sapItemNumberDimensionThicknessNewUOM->setValue($savedFields["0|sapItemNumberDimensionThicknessNewUOM"]);
//			$sapItemNumberDimensionThicknessNewUOM->setGroup("sapGroup");
//			$sapItemNumberDimensionThicknessNewUOM->setDataType("string");
//			$sapItemNumberDimensionThicknessNewUOM->setLength(255);
//			$sapItemNumberDimensionThicknessNewUOM->setRowTitle("dimension_thickness_uom");
//			$sapItemNumberDimensionThicknessNewUOM->setRequired(false);
//			$sapItemNumberDimensionThicknessNewUOM->setErrorMessage("dropdown_error");
//			$sapItemNumberDimensionThicknessNewUOM->setTranslate(true);
//			$sapItemNumberDimensionThicknessNewUOM->setXMLSource("./apps/complaints/xml/uom.xml");
//			$sapItemNumberDimensionThicknessNewUOM->setTable("sapItemNumber");
//			$sapItemNumberDimensionThicknessNewUOM->setHelpId(802224234675462341);
//			$sapGroup->add($sapItemNumberDimensionThicknessNewUOM);
//
//			$sapItemNumberDimensionWidthNew = new textbox("sapItemNumberDimensionWidthNew");
//			if(isset($savedFields["0|sapItemNumberDimensionWidthNew"]))
//			$sapItemNumberDimensionWidthNew->setValue($savedFields["0|sapItemNumberDimensionWidthNew"]);
//			$sapItemNumberDimensionWidthNew->setGroup("sapGroup");
//			$sapItemNumberDimensionWidthNew->setDataType("number");
//			$sapItemNumberDimensionWidthNew->setErrorMessage("number_field");
//			$sapItemNumberDimensionWidthNew->setLength(255);
//			$sapItemNumberDimensionWidthNew->setRowTitle("dimension_width_value");
//			$sapItemNumberDimensionWidthNew->setRequired(false);
//			$sapItemNumberDimensionWidthNew->setTable("sapItemNumber");
//			$sapItemNumberDimensionWidthNew->setHelpId(802224234675462);
//			$sapGroup->add($sapItemNumberDimensionWidthNew);
//
//			$sapItemNumberDimensionWidthNewUOM = new dropdown("sapItemNumberDimensionWidthNewUOM");
//			if(isset($savedFields["0|sapItemNumberDimensionWidthNewUOM"]))
//			$sapItemNumberDimensionWidthNewUOM->setValue($savedFields["0|sapItemNumberDimensionWidthNewUOM"]);
//			$sapItemNumberDimensionWidthNewUOM->setGroup("sapGroup");
//			$sapItemNumberDimensionWidthNewUOM->setDataType("string");
//			$sapItemNumberDimensionWidthNewUOM->setLength(255);
//			$sapItemNumberDimensionWidthNewUOM->setRowTitle("dimension_width_uom");
//			$sapItemNumberDimensionWidthNewUOM->setRequired(false);
//			$sapItemNumberDimensionWidthNewUOM->setErrorMessage("dropdown_error");
//			$sapItemNumberDimensionWidthNewUOM->setTranslate(true);
//			$sapItemNumberDimensionWidthNewUOM->setXMLSource("./apps/complaints/xml/uom.xml");
//			$sapItemNumberDimensionWidthNewUOM->setTable("sapItemNumber");
//			$sapItemNumberDimensionWidthNewUOM->setHelpId(562);
//			$sapGroup->add($sapItemNumberDimensionWidthNewUOM);

			$sapItemNumberQu_materialBlocked = new radio("sapItemNumberQu_materialBlocked");
			if(isset($savedFields["0|sapItemNumberQu_materialBlocked"]))
			$sapItemNumberQu_materialBlocked->setValue($savedFields["0|sapItemNumberQu_materialBlocked"]);
			else $sapItemNumberQu_materialBlocked->setValue("Yes");
			$sapItemNumberQu_materialBlocked->setGroup("sapGroup");
			$sapItemNumberQu_materialBlocked->setDataType("string");
			$sapItemNumberQu_materialBlocked->setLength(5);
			$sapItemNumberQu_materialBlocked->setArraySource(array(
				array('value' => 'Yes', 'display' => 'Yes'),
				array('value' => 'No', 'display' => 'No')
			));
			$sapItemNumberQu_materialBlocked->setRowTitle("material_blocked");
			$sapItemNumberQu_materialBlocked->setRequired(true);
			$sapItemNumberQu_materialBlocked->setTable("sapItemNumber");
			$sapItemNumberQu_materialBlocked->setHelpId(87567);
			$sapItemNumberQu_materialBlocked->setOnKeyPress("hide_field_manually(this)");
			$sapGroup->add($sapItemNumberQu_materialBlocked);

			$sapItemNumberQu_materialBlockedDate = new calendar("sapItemNumberQu_materialBlockedDate");
			if(isset($savedFields["0|sapItemNumberQu_materialBlockedDate"]))
			$sapItemNumberQu_materialBlockedDate->setValue($savedFields["0|sapItemNumberQu_materialBlockedDate"]);
			$sapItemNumberQu_materialBlockedDate->setGroup("sapGroup");
			$sapItemNumberQu_materialBlockedDate->setDataType("date");
			$sapItemNumberQu_materialBlockedDate->setLength(255);
			$sapItemNumberQu_materialBlockedDate->setRowTitle("material_blocked_date");
			$sapItemNumberQu_materialBlockedDate->setRequired(false);
			$sapItemNumberQu_materialBlockedDate->setTable("sapItemNumber");
			$sapItemNumberQu_materialBlockedDate->setHelpId(87567234);
			$sapGroup->add($sapItemNumberQu_materialBlockedDate);

			$sapItemNumberLocation = new textbox("sapItemNumberLocation");
			if(isset($savedFields["0|sapItemNumberLocation"]))
			$sapItemNumberLocation->setValue($savedFields["0|sapItemNumberLocation"]);
			$sapItemNumberLocation->setGroup("sapGroup");
			$sapItemNumberLocation->setDataType("text");
			$sapItemNumberLocation->setLength(255);
			$sapItemNumberLocation->setRowTitle("location");
			$sapItemNumberLocation->setRequired(false);
			$sapItemNumberLocation->setTable("sapItemNumber");
			$sapItemNumberLocation->setHelpId(8756723434545);
			$sapGroup->add($sapItemNumberLocation);

		}else{
			$this->form->groupsToExclude[] = "sapGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|sapItemNumber"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){

				if($i==0){//first will always be set

					$sapItemNumber = new textbox("sapItemNumber");
					if(isset($savedFields["0|sapItemNumber"]))
					$sapItemNumber->setValue($savedFields["0|sapItemNumber"]);
					$sapItemNumber->setGroup("sapGroup");
					$sapItemNumber->setDataType("textMinLength");
					$sapItemNumber->setErrorMessage("field_error");
					$sapItemNumber->setMinLength(4);
					$sapItemNumber->setRowTitle("sap_item_number");
					$sapItemNumber->setRequired(true);
					$sapItemNumber->setTable("sapItemNumber");
					$sapItemNumber->setHelpId(8022);
					$sapGroup->add($sapItemNumber);

					$sapItemNumberMaterialGroup = new textbox("sapItemNumberMaterialGroup");
					if(isset($savedFields["0|sapItemNumberMaterialGroup"]))
					$sapItemNumberMaterialGroup->setValue($savedFields["0|sapItemNumberMaterialGroup"]);
					$sapItemNumberMaterialGroup->setGroup("sapGroup");
					$sapItemNumberMaterialGroup->setDataType("string");
					$sapItemNumberMaterialGroup->setLength(255);
					$sapItemNumberMaterialGroup->setRowTitle("material_group");
					$sapItemNumberMaterialGroup->setRequired(false);
					$sapItemNumberMaterialGroup->setTable("sapItemNumber");
					$sapItemNumberMaterialGroup->setHelpId(802224234);
					$sapGroup->add($sapItemNumberMaterialGroup);

					$sapItemNumberProductDescription = new textarea("sapItemNumberProductDescription");
					if(isset($savedFields["0|sapItemNumberProductDescription"]))
					$sapItemNumberProductDescription->setValue($savedFields["0|sapItemNumberProductDescription"]);
					$sapItemNumberProductDescription->setGroup("sapGroup");
					$sapItemNumberProductDescription->setDataType("text");
					$sapItemNumberProductDescription->setRowTitle("material_description");
					$sapItemNumberProductDescription->setRequired(false);
					$sapItemNumberProductDescription->setTable("sapItemNumber");
					$sapItemNumberProductDescription->setHelpId(8022242343);
					$sapGroup->add($sapItemNumberProductDescription);

					$sapItemNumberColour = new textbox("sapItemNumberColour");
					if(isset($savedFields["0|sapItemNumberColour"]))
					$sapItemNumberColour->setValue($savedFields["0|sapItemNumberColour"]);
					$sapItemNumberColour->setGroup("sapGroup");
					$sapItemNumberColour->setDataType("string");
					$sapItemNumberColour->setRowTitle("colour");
					$sapItemNumberColour->setRequired(false);
					$sapItemNumberColour->setTable("sapItemNumber");
					$sapItemNumberColour->setHelpId(80222423467546);
					$sapGroup->add($sapItemNumberColour);

					$sapItemNumberBatchNumber = new textbox("sapItemNumberBatchNumber");
					if(isset($savedFields["0|sapItemNumberBatchNumber"]))
					$sapItemNumberBatchNumber->setValue($savedFields["0|sapItemNumberBatchNumber"]);
					$sapItemNumberBatchNumber->setGroup("sapGroup");
					$sapItemNumberBatchNumber->setDataType("string");
					$sapItemNumberBatchNumber->setRowTitle("scapa_batch_number");
					$sapItemNumberBatchNumber->setTable("sapItemNumber");
					$sapItemNumberBatchNumber->setHelpId(80222423467546234);
					$sapGroup->add($sapItemNumberBatchNumber);

					$sapItemNumberQuantityUnderComplaintNew = new textbox("sapItemNumberQuantityUnderComplaintNew");
					if(isset($savedFields["0|sapItemNumberQuantityUnderComplaintNew"]))
					$sapItemNumberQuantityUnderComplaintNew->setValue($savedFields["0|sapItemNumberQuantityUnderComplaintNew"]);
					$sapItemNumberQuantityUnderComplaintNew->setGroup("sapGroup");
					$sapItemNumberQuantityUnderComplaintNew->setDataType("string");
					$sapItemNumberQuantityUnderComplaintNew->setLength(255);
					$sapItemNumberQuantityUnderComplaintNew->setRowTitle("quantity_under_complaint_value");
					$sapItemNumberQuantityUnderComplaintNew->setRequired(false);
					$sapItemNumberQuantityUnderComplaintNew->setTable("sapItemNumber");
					$sapItemNumberQuantityUnderComplaintNew->setHelpId(80222423467546234234);
					$sapGroup->add($sapItemNumberQuantityUnderComplaintNew);

					$sapItemNumberQuantityUnderComplaintNewUOM = new dropdown("sapItemNumberQuantityUnderComplaintNewUOM");
					if(isset($savedFields["0|sapItemNumberQuantityUnderComplaintNewUOM"]))
					$sapItemNumberQuantityUnderComplaintNewUOM->setValue($savedFields["0|sapItemNumberQuantityUnderComplaintNewUOM"]);
					$sapItemNumberQuantityUnderComplaintNewUOM->setGroup("sapGroup");
					$sapItemNumberQuantityUnderComplaintNewUOM->setDataType("string");
					$sapItemNumberQuantityUnderComplaintNewUOM->setLength(255);
					$sapItemNumberQuantityUnderComplaintNewUOM->setRowTitle("quantity_under_complaint_uom");
					$sapItemNumberQuantityUnderComplaintNewUOM->setRequired(false);
					$sapItemNumberQuantityUnderComplaintNewUOM->setErrorMessage("dropdown_error");
					$sapItemNumberQuantityUnderComplaintNewUOM->setTranslate(true);
					$sapItemNumberQuantityUnderComplaintNewUOM->setXMLSource("./apps/complaints/xml/currency.xml");
					$sapItemNumberQuantityUnderComplaintNewUOM->setTable("sapItemNumber");
					$sapItemNumberQuantityUnderComplaintNewUOM->setHelpId(8022242346754623423434);
					$sapGroup->add($sapItemNumberQuantityUnderComplaintNewUOM);

//					$sapItemNumberDimensionThicknessNew = new textbox("sapItemNumberDimensionThicknessNew");
//					if(isset($savedFields["0|sapItemNumberDimensionThicknessNew"]))
//					$sapItemNumberDimensionThicknessNew->setValue($savedFields["0|sapItemNumberDimensionThicknessNew"]);
//					$sapItemNumberDimensionThicknessNew->setGroup("sapGroup");
//					$sapItemNumberDimensionThicknessNew->setDataType("string");
//					$sapItemNumberDimensionThicknessNew->setLength(255);
//					$sapItemNumberDimensionThicknessNew->setRowTitle("dimension_thickness_value");
//					$sapItemNumberDimensionThicknessNew->setRequired(false);
//					$sapItemNumberDimensionThicknessNew->setTable("sapItemNumber");
//					$sapItemNumberDimensionThicknessNew->setHelpId(8022242346754623476);
//					$sapGroup->add($sapItemNumberDimensionThicknessNew);
//
//					$sapItemNumberDimensionThicknessNewUOM = new dropdown("sapItemNumberDimensionThicknessNewUOM");
//					if(isset($savedFields["0|sapItemNumberDimensionThicknessNewUOM"]))
//					$sapItemNumberDimensionThicknessNewUOM->setValue($savedFields["0|sapItemNumberDimensionThicknessNewUOM"]);
//					$sapItemNumberDimensionThicknessNewUOM->setGroup("sapGroup");
//					$sapItemNumberDimensionThicknessNewUOM->setDataType("string");
//					$sapItemNumberDimensionThicknessNewUOM->setLength(255);
//					$sapItemNumberDimensionThicknessNewUOM->setRowTitle("dimension_thickness_uom");
//					$sapItemNumberDimensionThicknessNewUOM->setRequired(false);
//					$sapItemNumberDimensionThicknessNewUOM->setErrorMessage("dropdown_error");
//					$sapItemNumberDimensionThicknessNewUOM->setTranslate(true);
//					$sapItemNumberDimensionThicknessNewUOM->setXMLSource("./apps/complaints/xml/uom.xml");
//					$sapItemNumberDimensionThicknessNewUOM->setTable("sapItemNumber");
//					$sapItemNumberDimensionThicknessNewUOM->setHelpId(802224234675462341);
//					$sapGroup->add($sapItemNumberDimensionThicknessNewUOM);
//
//					$sapItemNumberDimensionWidthNew = new textbox("sapItemNumberDimensionWidthNew");
//					if(isset($savedFields["0|sapItemNumberDimensionWidthNew"]))
//					$sapItemNumberDimensionWidthNew->setValue($savedFields["0|sapItemNumberDimensionWidthNew"]);
//					$sapItemNumberDimensionWidthNew->setGroup("sapGroup");
//					$sapItemNumberDimensionWidthNew->setDataType("string");
//					$sapItemNumberDimensionWidthNew->setLength(255);
//					$sapItemNumberDimensionWidthNew->setRowTitle("dimension_width_value");
//					$sapItemNumberDimensionWidthNew->setRequired(false);
//					$sapItemNumberDimensionWidthNew->setTable("sapItemNumber");
//					$sapItemNumberDimensionWidthNew->setHelpId(802224234675462);
//					$sapGroup->add($sapItemNumberDimensionWidthNew);
//
//					$sapItemNumberDimensionWidthNewUOM = new dropdown("sapItemNumberDimensionWidthNewUOM");
//					if(isset($savedFields["0|sapItemNumberDimensionWidthNewUOM"]))
//					$sapItemNumberDimensionWidthNewUOM->setValue($savedFields["0|sapItemNumberDimensionWidthNewUOM"]);
//					$sapItemNumberDimensionWidthNewUOM->setGroup("sapGroup");
//					$sapItemNumberDimensionWidthNewUOM->setDataType("string");
//					$sapItemNumberDimensionWidthNewUOM->setLength(255);
//					$sapItemNumberDimensionWidthNewUOM->setRowTitle("dimension_width_uom");
//					$sapItemNumberDimensionWidthNewUOM->setRequired(false);
//					$sapItemNumberDimensionWidthNewUOM->setErrorMessage("dropdown_error");
//					$sapItemNumberDimensionWidthNewUOM->setTranslate(true);
//					$sapItemNumberDimensionWidthNewUOM->setXMLSource("./apps/complaints/xml/uom.xml");
//					$sapItemNumberDimensionWidthNewUOM->setTable("sapItemNumber");
//					$sapItemNumberDimensionWidthNewUOM->setHelpId(562);
//					$sapGroup->add($sapItemNumberDimensionWidthNewUOM);

					$sapItemNumberQu_materialBlocked = new radio("sapItemNumberQu_materialBlocked");
					if(isset($savedFields["0|sapItemNumberQu_materialBlocked"]))
					$sapItemNumberQu_materialBlocked->setValue($savedFields["0|sapItemNumberQu_materialBlocked"]);
					else $sapItemNumberQu_materialBlocked->setValue("Yes");
					$sapItemNumberQu_materialBlocked->setGroup("sapGroup");
					$sapItemNumberQu_materialBlocked->setDataType("string");
					$sapItemNumberQu_materialBlocked->setLength(5);
					$sapItemNumberQu_materialBlocked->setArraySource(array(
						array('value' => 'Yes', 'display' => 'Yes'),
						array('value' => 'No', 'display' => 'No')
					));
					$sapItemNumberQu_materialBlocked->setRowTitle("material_blocked");
					$sapItemNumberQu_materialBlocked->setValue("No");
					$sapItemNumberQu_materialBlocked->setRequired(true);
					$sapItemNumberQu_materialBlocked->setTable("sapItemNumber");
					$sapItemNumberQu_materialBlocked->setHelpId(87567);
					$sapItemNumberQu_materialBlocked->setOnKeyPress("hide_field_manually(this)");
					$sapGroup->add($sapItemNumberQu_materialBlocked);

					$sapItemNumberQu_materialBlockedDate = new textbox("sapItemNumberQu_materialBlockedDate");
					if(isset($savedFields["0|sapItemNumberQu_materialBlockedDate"]))
					$sapItemNumberQu_materialBlockedDate->setValue($savedFields["0|sapItemNumberQu_materialBlockedDate"]);
					$sapItemNumberQu_materialBlockedDate->setGroup("sapGroup");
					$sapItemNumberQu_materialBlockedDate->setDataType("date");
					$sapItemNumberQu_materialBlockedDate->setLength(255);
					$sapItemNumberQu_materialBlockedDate->setRowTitle("material_blocked_date");
					$sapItemNumberQu_materialBlockedDate->setRequired(false);
					$sapItemNumberQu_materialBlockedDate->setTable("sapItemNumber");
					$sapItemNumberQu_materialBlockedDate->setHelpId(87567234);
					$sapGroup->add($sapItemNumberQu_materialBlockedDate);

					$sapItemNumberLocation = new textbox("sapItemNumberLocation");
					if(isset($savedFields["0|sapItemNumberLocation"]))
					$sapItemNumberLocation->setValue($savedFields["0|sapItemNumberLocation"]);
					$sapItemNumberLocation->setGroup("sapGroup");
					$sapItemNumberLocation->setDataType("text");
					$sapItemNumberLocation->setLength(255);
					$sapItemNumberLocation->setRowTitle("location");
					$sapItemNumberLocation->setRequired(false);
					$sapItemNumberLocation->setTable("sapItemNumber");
					$sapItemNumberLocation->setHelpId(8756723434545);
					$sapGroup->add($sapItemNumberLocation);

				}else{
					$sapItemInformation = array();
					$sapItemInformation[] = $savedFields[$i."|sapItemNumber"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberMaterialGroup"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberProductDescription"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberColour"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberBatchNumber"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberQuantityUnderComplaintNew"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberQuantityUnderComplaintNewUOM"];
					//$sapItemInformation[] = $savedFields[$i."|sapItemNumberDimensionThicknessNew"];
					//$sapItemInformation[] = $savedFields[$i."|sapItemNumberDimensionThicknessNewUOM"];
					//$sapItemInformation[] = $savedFields[$i."|sapItemNumberDimensionWidthNew"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberQu_materialBlocked"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberQu_materialBlockedDate"];
					$sapItemInformation[] = $savedFields[$i."|sapItemNumberLocation"];
					$sapGroup->addRowCustomMultiple($sapItemInformation);
				}
			}
		}





		if(!isset($savedFields["0|materialGroup"])){//the first one will always need to be set if its saved
			$materialGroup = new readonly("materialGroup");
			//if(isset($savedFields["materialGroup"]))
			//$materialGroup->setValue($savedFields["materialGroup"]);
			$materialGroup->setGroup("materialGroupGroup");
			$materialGroup->setDataType("string");
			$materialGroup->setLength(255);
			$materialGroup->setRowTitle("material_group");
			$materialGroup->setRequired(false);
			$materialGroup->setTable("materialGroup");
			$materialGroup->setHelpId(8015);
			$materialGroupGroup->add($materialGroup);
		}else{
			$this->form->groupsToExclude[] = "materialGroupGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroup"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$materialGroup = new readonly("materialGroup");
					if(isset($savedFields["0|materialGroup"]))
					$materialGroup->setValue($savedFields["0|materialGroup"]);
					$materialGroup->setGroup("materialGroupGroup");
					$materialGroup->setDataType("string");
					$materialGroup->setLength(255);
					$materialGroup->setRowTitle("material_group");
					$materialGroup->setRequired(false);
					$materialGroup->setTable("materialGroup");
					$materialGroup->setHelpId(8015);
					$materialGroupGroup->add($materialGroup);
				}else{
					$materialGroupGroup->addRowCustom($savedFields[$i."|materialGroup"]);
				}
			}
		}

		if(!isset($savedFields["0|materialGroup"])){//the first one will always need to be set if its saved
			$productDescription = new readonly("productDescription");
			if(isset($savedFields["productDescription"]))
			$productDescription->setValue($savedFields["productDescription"]);
			$productDescription->setGroup("materialGroupGroup");
			$productDescription->setDataType("text");
			$productDescription->setRowTitle("material_description");
			$productDescription->setRequired(false);
			$productDescription->setTable("materialGroup");
			$productDescription->setHelpId(8016);
			$materialGroupGroup->add($productDescription);
		}else{
			$this->form->groupsToExclude[] = "materialGroupGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroup"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$productDescription = new readonly("productDescription");
					if(isset($savedFields["productDescription"]))
					$productDescription->setValue($savedFields["productDescription"]);
					$productDescription->setGroup("materialGroupGroup");
					$productDescription->setDataType("text");
					$productDescription->setRowTitle("material_description");
					$productDescription->setRequired(false);
					$productDescription->setTable("materialGroup");
					$productDescription->setHelpId(8016);
					$materialGroupGroup->add($productDescription);
				}else{
					$materialGroupGroup->addRowCustom($savedFields[$i."|materialGroup"]);
				}
			}
		}

		//		$productDescription = new textarea("productDescription");
		//		if(isset($savedFields["productDescription"]))
		//			$productDescription->setValue($savedFields["productDescription"]);
		//		$productDescription->setGroup("complaintDetails3");
		//		$productDescription->setDataType("text");
		//		$productDescription->setRowTitle("product_description");
		//		$productDescription->setRequired(false);
		//		$productDescription->setTable("complaint");
		//		$productDescription->setHelpId(8016);
		//		$complaintDetails3->add($productDescription);


		/**
		 * *
		 * These are the grouped fields for NA complaints - START
		 *
		 */

		/**

		// Sap Item Number Grouped Together Field
		if(!isset($savedFields["0|sapItemNumberGroupedT"])) //the first one will always need to be set if its saved
		{
			$sapItemNumberGroupedT = new textbox("sapItemNumberGroupedT");
			if(isset($savedFields["sapItemNumberGroupedT"]))
			$sapItemNumberGroupedT->setValue($savedFields["sapItemNumberGroupedT"]);
			$sapItemNumberGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$sapItemNumberGroupedT->setDataType("textMinLength");
			$sapItemNumberGroupedT->setErrorMessage("field_error");
			$sapItemNumberGroupedT->setMinLength(4);
			$sapItemNumberGroupedT->setRowTitle("sap_item_number");
			$sapItemNumberGroupedT->setRequired(true);
			$sapItemNumberGroupedT->setTable("sapItemNumberGroupedT");
			$sapItemNumberGroupedT->setHelpId(8022);
			$sapItemNumberGroupedTGroup->add($sapItemNumberGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|sapItemNumberGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$sapItemNumberGroupedT = new textbox("sapItemNumberGroupedT");
					if(isset($savedFields["sapItemNumberGroupedT"]))
					$sapItemNumberGroupedT->setValue($savedFields["sapItemNumberGroupedT"]);
					$sapItemNumberGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$sapItemNumberGroupedT->setDataType("textMinLength");
					$sapItemNumberGroupedT->setErrorMessage("field_error");
					$sapItemNumberGroupedT->setMinLength(4);
					$sapItemNumberGroupedT->setRowTitle("sap_item_number");
					$sapItemNumberGroupedT->setRequired(true);
					$sapItemNumberGroupedT->setTable("sapItemNumberGroupedT");
					$sapItemNumberGroupedT->setHelpId(8022);
					$sapItemNumberGroupedTGroup->add($sapItemNumberGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|sapItemNumberGroupedT"]);
				}
			}
		}

		// Material Group Grouped Together Field
		if(!isset($savedFields["0|materialGroupGroupedT"])) //the first one will always need to be set if its saved
		{
			$materialGroupGroupedT = new textbox("materialGroupGroupedT");
			if(isset($savedFields["materialGroupGroupedT"]))
			$materialGroupGroupedT->setValue($savedFields["materialGroupGroupedT"]);
			$materialGroupGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$materialGroupGroupedT->setDataType("textMinLength");
			$materialGroupGroupedT->setErrorMessage("field_error");
			$materialGroupGroupedT->setMinLength(4);
			$materialGroupGroupedT->setRowTitle("sap_item_number");
			$materialGroupGroupedT->setRequired(true);
			$materialGroupGroupedT->setTable("sapItemNumberGroupedT");
			$materialGroupGroupedT->setHelpId(8022);
			$sapItemNumberGroupedTGroup->add($materialGroupGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|materialGroupGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$materialGroupGroupedT = new textbox("materialGroupGroupedT");
					if(isset($savedFields["materialGroupGroupedT"]))
					$materialGroupGroupedT->setValue($savedFields["materialGroupGroupedT"]);
					$materialGroupGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$materialGroupGroupedT->setDataType("string");
					$materialGroupGroupedT->setLength(255);
					$materialGroupGroupedT->setRowTitle("material_group");
					$materialGroupGroupedT->setRequired(false);
					$materialGroupGroupedT->setTable("sapItemNumberGroupedT");
					$materialGroupGroupedT->setHelpId(8015);
					$sapItemNumberGroupedTGroup->add($materialGroupGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|materialGroupGroupedT"]);
				}
			}
		}

		// Material Description Grouped Together Field
		if(!isset($savedFields["0|productDescriptionGroupedT"])) //the first one will always need to be set if its saved
		{
			$productDescriptionGroupedT = new textarea("productDescriptionGroupedT");
			if(isset($savedFields["productDescriptionGroupedT"]))
			$productDescriptionGroupedT->setValue($savedFields["productDescriptionGroupedT"]);
			$productDescriptionGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$productDescriptionGroupedT->setDataType("text");
			$productDescriptionGroupedT->setRowTitle("material_description");
			$productDescriptionGroupedT->setRequired(false);
			$productDescriptionGroupedT->setTable("sapItemNumberGroupedT");
			$productDescriptionGroupedT->setHelpId(8016);
			$sapItemNumberGroupedTGroup->add($productDescriptionGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|productDescriptionGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$productDescriptionGroupedT = new textarea("productDescriptionGroupedT");
					if(isset($savedFields["productDescriptionGroupedT"]))
					$productDescriptionGroupedT->setValue($savedFields["productDescriptionGroupedT"]);
					$productDescriptionGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$productDescriptionGroupedT->setDataType("text");
					$productDescriptionGroupedT->setRowTitle("material_description");
					$productDescriptionGroupedT->setRequired(false);
					$productDescriptionGroupedT->setTable("sapItemNumberGroupedT");
					$productDescriptionGroupedT->setHelpId(8016);
					$sapItemNumberGroupedTGroup->add($productDescriptionGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|productDescriptionGroupedT"]);
				}
			}
		}


		// Quantity Under Complaint Grouped Together Field
		if(!isset($savedFields["0|quantityUnderComplaintGroupedT"])) //the first one will always need to be set if its saved
		{
			$quantityUnderComplaintGroupedT = new measurement("quantityUnderComplaintGroupedT");
			if(isset($savedFields["quantityUnderComplaintGroupedT_quantity"]) && isset($savedFields["quantityUnderComplaintGroupedT_measurement"])){
				$arr[0] = $savedFields["quantityUnderComplaintGroupedT_quantity"];
				$arr[1] = $savedFields["quantityUnderComplaintGroupedT_measurement"];
				$quantityUnderComplaintGroupedT->setValue($arr);
			}else $quantityUnderComplaintGroupedT->setMeasurement("roll");
			$quantityUnderComplaintGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$quantityUnderComplaintGroupedT->setDataType("string");
			$quantityUnderComplaintGroupedT->setErrorMessage("numeric_only_values");
			$quantityUnderComplaintGroupedT->setLength(10);
			$quantityUnderComplaintGroupedT->setXMLSource("./apps/complaints/xml/uom.xml");
			$quantityUnderComplaintGroupedT->setRowTitle("quantity_under_complaint");
			$quantityUnderComplaintGroupedT->setRequired(true);
			$quantityUnderComplaintGroupedT->setTable("sapItemNumberGroupedT");
			$quantityUnderComplaintGroupedT->setHelpId(8026);
			$sapItemNumberGroupedTGroup->add($quantityUnderComplaintGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|quantityUnderComplaintGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$quantityUnderComplaintGroupedT = new measurement("quantityUnderComplaintGroupedT");
					if(isset($savedFields["quantityUnderComplaintGroupedT_quantity"]) && isset($savedFields["quantityUnderComplaintGroupedT_measurement"])){
						$arr[0] = $savedFields["quantityUnderComplaintGroupedT_quantity"];
						$arr[1] = $savedFields["quantityUnderComplaintGroupedT_measurement"];
						$quantityUnderComplaintGroupedT->setValue($arr);
					}else $quantityUnderComplaintGroupedT->setMeasurement("roll");
					$quantityUnderComplaintGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$quantityUnderComplaintGroupedT->setDataType("string");
					$quantityUnderComplaintGroupedT->setErrorMessage("numeric_only_values");
					$quantityUnderComplaintGroupedT->setLength(10);
					$quantityUnderComplaintGroupedT->setXMLSource("./apps/complaints/xml/uom.xml");
					$quantityUnderComplaintGroupedT->setRowTitle("quantity_under_complaint");
					$quantityUnderComplaintGroupedT->setRequired(true);
					$quantityUnderComplaintGroupedT->setTable("sapItemNumberGroupedT");
					$quantityUnderComplaintGroupedT->setHelpId(8026);
					$sapItemNumberGroupedTGroup->add($quantityUnderComplaintGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|quantityUnderComplaintGroupedT"]);
				}
			}
		}


		// Complaint Costs Grouped Together Field
		if(!isset($savedFields["0|qu_complaintCostsGroupedT"])) //the first one will always need to be set if its saved
		{
			$qu_complaintCostsGroupedT = new measurement("qu_complaintCostsGroupedT");
			if(isset($savedFields["qu_complaintCostsGroupedT_quantity"]) && isset($savedFields["qu_complaintCostsGroupedT_measurement"])){
				$arr[0] = $savedFields["qu_complaintCostsGroupedT_quantity"];
				$arr[1] = $savedFields["qu_complaintCostsGroupedT_measurement"];
				$qu_complaintCostsGroupedT->setValue($arr);
			}else $qu_complaintCostsGroupedT->setMeasurement("roll");
			$qu_complaintCostsGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$qu_complaintCostsGroupedT->setDataType("string");
			$qu_complaintCostsGroupedT->setErrorMessage("numeric_only_values");
			$qu_complaintCostsGroupedT->setLength(10);
			$qu_complaintCostsGroupedT->setXMLSource("./apps/complaints/xml/currency.xml");
			$qu_complaintCostsGroupedT->setRowTitle("complaint_costs");
			$qu_complaintCostsGroupedT->setRequired(true);
			$qu_complaintCostsGroupedT->setTable("sapItemNumberGroupedT");
			$qu_complaintCostsGroupedT->setHelpId(8026);
			$sapItemNumberGroupedTGroup->add($qu_complaintCostsGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|qu_complaintCostsGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$qu_complaintCostsGroupedT = new measurement("qu_complaintCostsGroupedT");
					if(isset($savedFields["qu_complaintCostsGroupedT_quantity"]) && isset($savedFields["qu_complaintCostsGroupedT_measurement"])){
						$arr[0] = $savedFields["qu_complaintCostsGroupedT_quantity"];
						$arr[1] = $savedFields["qu_complaintCostsGroupedT_measurement"];
						$qu_complaintCostsGroupedT->setValue($arr);
					}else $qu_complaintCostsGroupedT->setMeasurement("roll");
					$qu_complaintCostsGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$qu_complaintCostsGroupedT->setDataType("string");
					$qu_complaintCostsGroupedT->setErrorMessage("numeric_only_values");
					$qu_complaintCostsGroupedT->setLength(10);
					$qu_complaintCostsGroupedT->setXMLSource("./apps/complaints/xml/currency.xml");
					$qu_complaintCostsGroupedT->setRowTitle("complaint_costs");
					$qu_complaintCostsGroupedT->setRequired(true);
					$qu_complaintCostsGroupedT->setTable("sapItemNumberGroupedT");
					$qu_complaintCostsGroupedT->setHelpId(8026);
					$sapItemNumberGroupedTGroup->add($qu_complaintCostsGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|qu_complaintCostsGroupedT"]);
				}
			}
		}

		// Comments on Complaint Costs Grouped Together Field
		if(!isset($savedFields["0|qu_commentOnCostGroupedT"])) //the first one will always need to be set if its saved
		{
			$qu_commentOnCostGroupedT = new textarea("qu_commentOnCostGroupedT");
			if(isset($savedFields["qu_commentOnCostGroupedT"]))
			$qu_commentOnCostGroupedT->setValue($savedFields["qu_commentOnCostGroupedT"]);
			$qu_commentOnCostGroupedT->setGroup("sapItemNumberGroupedTGroup");
			$qu_commentOnCostGroupedT->setDataType("text");
			$qu_commentOnCostGroupedT->setRowTitle("comment_on_cost");
			$qu_commentOnCostGroupedT->setRequired(false);
			$qu_commentOnCostGroupedT->setTable("sapItemNumberGroupedT");
			$qu_commentOnCostGroupedT->setHelpId(8021);
			$sapItemNumberGroupedTGroup->add($qu_commentOnCostGroupedT);
		}
		else
		{
			$this->form->groupsToExclude[] = "sapItemNumberGroupedTGroup";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|qu_commentOnCostGroupedT"])){
					$maxList = $i;
					$endList = true;
				}
				$i++;
			}while(!$endList);
			for($i=0; $i<$maxList; $i++){
				if($i==0){//first will always be set
					$qu_commentOnCostGroupedT = new textarea("qu_commentOnCostGroupedT");
					if(isset($savedFields["qu_commentOnCostGroupedT"]))
					$qu_commentOnCostGroupedT->setValue($savedFields["qu_commentOnCostGroupedT"]);
					$qu_commentOnCostGroupedT->setGroup("sapItemNumberGroupedTGroup");
					$qu_commentOnCostGroupedT->setDataType("text");
					$qu_commentOnCostGroupedT->setRowTitle("comment_on_cost");
					$qu_commentOnCostGroupedT->setRequired(false);
					$qu_commentOnCostGroupedT->setTable("sapItemNumberGroupedT");
					$qu_commentOnCostGroupedT->setHelpId(8021);
					$sapItemNumberGroupedTGroup->add($qu_complaintCostsGroupedT);
				}else{
					$sapItemNumberGroupedTGroup->addRowCustom($savedFields[$i."|qu_commentOnCostGroupedT"]);
				}
			}
		}

		**/

		/**
		 * *
		 * These are the grouped fields for NA complaints - FINISH
		 *
		 */


//		$dimensionThickness = new measurement("dimensionThickness");
//		if(isset($savedFields["dimensionThickness_quantity"]) && isset($savedFields["dimensionThickness_measurement"])){
//			//echo strval($savedFields["dimensionThickness_quantity"]);exit;
//			$arr[0] = $savedFields["dimensionThickness_quantity"];
//			$arr[1] = $savedFields["dimensionThickness_measurement"];
//			$dimensionThickness->setValue($arr);
//		}else $dimensionThickness->setMeasurement("mm");
//		$dimensionThickness->setGroup("complaintDetails3");
//		$dimensionThickness->setDataType("string");
//		$dimensionThickness->setLength(5);
//		$dimensionThickness->setXMLSource("./apps/complaints/xml/uom.xml");
//		$dimensionThickness->setRowTitle("thickness");
//		$dimensionThickness->setErrorMessage("numeric_only_values");
//		$dimensionThickness->setRequired(false);
//		$dimensionThickness->setTable("complaint");
//		$dimensionThickness->setExtTable("complaintExt");
//		$dimensionThickness->setHelpId(8018);
//		$complaintDetails3->add($dimensionThickness);

		$dimensionWidth = new readonly("dimensionWidth");
//		if(isset($savedFields["dimensionWidth_quantity"]) && isset($savedFields["dimensionWidth_measurement"])){
//			$arr[0] = $savedFields["dimensionWidth_quantity"];
//			$arr[1] = $savedFields["dimensionWidth_measurement"];
//			$dimensionWidth->setValue($arr);
//		}else $dimensionWidth->setMeasurement("mm");
		$dimensionWidth->setGroup("complaintDetails3");
		$dimensionWidth->setDataType("string");
		$dimensionWidth->setLength(5);
		//$dimensionWidth->setXMLSource("./apps/complaints/xml/uom.xml");
		$dimensionWidth->setRowTitle("width");
		$dimensionWidth->setErrorMessage("numeric_only_values");
		$dimensionWidth->setRequired(false);
		$dimensionWidth->setTable("complaint");
		$dimensionWidth->setHelpId(8019);
		$complaintDetails3->add($dimensionWidth);

		$dimensionLength = new readonly("dimensionLength");
//		if(isset($savedFields["dimensionLength_quantity"]) && isset($savedFields["dimensionLength_measurement"])){
//			$arr[0] = $savedFields["dimensionLength_quantity"];
//			$arr[1] = $savedFields["dimensionLength_measurement"];
//			$dimensionLength->setValue($arr);
//		}else $dimensionLength->setMeasurement("m");
		$dimensionLength->setGroup("complaintDetails3");
		$dimensionLength->setDataType("string");
		$dimensionLength->setLength(5);
		//$dimensionLength->setXMLSource("./apps/complaints/xml/uom.xml");
		$dimensionLength->setRowTitle("length");
		$dimensionLength->setErrorMessage("numeric_only_values");
		$dimensionLength->setRequired(false);
		$dimensionLength->setTable("complaint");
		$dimensionLength->setHelpId(8020);
		$complaintDetails3->add($dimensionLength);

		$colour = new readonly("colour");
		if(isset($savedFields["colour"]))
		$colour->setValue($savedFields["colour"]);
		$colour->setGroup("complaintDetails3");
		$colour->setDataType("string");
		$colour->setRowTitle("colour");
		$colour->setRequired(false);
		$colour->setTable("complaint");
		$colour->setHelpId(8021);
		$complaintDetails3->add($colour);

		$quantityUnderComplaint = new measurement("quantityUnderComplaint");
		if(isset($savedFields["quantityUnderComplaint_quantity"]) && isset($savedFields["quantityUnderComplaint_measurement"])){
			$arr[0] = $savedFields["quantityUnderComplaint_quantity"];
			$arr[1] = $savedFields["quantityUnderComplaint_measurement"];
			$quantityUnderComplaint->setValue($arr);
		}else $quantityUnderComplaint->setMeasurement("roll");
		$quantityUnderComplaint->setGroup("complaintDetails3");
		$quantityUnderComplaint->setDataType("string");
		$quantityUnderComplaint->setErrorMessage("numeric_only_values");
		$quantityUnderComplaint->setLength(10);
		$quantityUnderComplaint->setXMLSource("./apps/complaints/xml/uom.xml");
		$quantityUnderComplaint->setRowTitle("quantity_under_complaint");
		$quantityUnderComplaint->setRequired(true);
		$quantityUnderComplaint->setTable("complaint");
		$quantityUnderComplaint->setHelpId(8026);
		$complaintDetails3->add($quantityUnderComplaint);

		$qu_complaintCosts = new measurement("qu_complaintCosts");
		if(isset($savedFields["qu_complaintCosts_quantity"]) && isset($savedFields["qu_complaintCosts_measurement"])){
			$arr[0] = $savedFields["qu_complaintCosts_quantity"];
			$arr[1] = $savedFields["qu_complaintCosts_measurement"];
			$qu_complaintCosts->setValue($arr);
		}else $qu_complaintCosts->setMeasurement("roll");
		$qu_complaintCosts->setGroup("complaintDetails3");
		$qu_complaintCosts->setDataType("string");
		$qu_complaintCosts->setErrorMessage("numeric_only_values");
		$qu_complaintCosts->setLength(10);
		$qu_complaintCosts->setXMLSource("./apps/complaints/xml/currency.xml");
		$qu_complaintCosts->setRowTitle("complaint_costs");
		$qu_complaintCosts->setRequired(true);
		$qu_complaintCosts->setTable("complaint");
		$qu_complaintCosts->setHelpId(8026);
		$complaintDetails3->add($qu_complaintCosts);

		$qu_commentOnCost = new textarea("qu_commentOnCost");
		if(isset($savedFields["qu_commentOnCost"]))
		$qu_commentOnCost->setValue($savedFields["qu_commentOnCost"]);
		$qu_commentOnCost->setGroup("complaintDetails3");
		$qu_commentOnCost->setDataType("text");
		$qu_commentOnCost->setRowTitle("comment_on_cost");
		$qu_commentOnCost->setRequired(false);
		$qu_commentOnCost->setTable("complaint");
		$qu_commentOnCost->setHelpId(8021);
		$complaintDetails3->add($qu_commentOnCost);

		$qu_weightOfMaterial = new measurement("qu_weightOfMaterial");
		if(isset($savedFields["qu_weightOfMaterial_quantity"]) && isset($savedFields["qu_weightOfMaterial_measurement"])){
			$arr[0] = $savedFields["qu_weightOfMaterial_quantity"];
			$arr[1] = $savedFields["qu_weightOfMaterial_measurement"];
			$qu_weightOfMaterial->setValue($arr);
		}else $qu_weightOfMaterial->setMeasurement("kg");
		$qu_weightOfMaterial->setGroup("complaintDetails3");
		$qu_weightOfMaterial->setDataType("string");
		$qu_weightOfMaterial->setErrorMessage("numeric_only_values");
		$qu_weightOfMaterial->setLength(10);
		$qu_weightOfMaterial->setXMLSource("./apps/complaints/xml/uom.xml");
		$qu_weightOfMaterial->setRowTitle("qu_weight_of_material");
		$qu_weightOfMaterial->setTable("complaint");
		$qu_weightOfMaterial->setHelpId(8026);
		$complaintDetails3->add($qu_weightOfMaterial);


		$qu_materialBlocked = new radio("qu_materialBlocked");
		$qu_materialBlocked->setGroup("materialBlockedGroup");
		$qu_materialBlocked->setDataType("string");
		$qu_materialBlocked->setLength(5);
		$qu_materialBlocked->setArraySource(array(
		array('value' => 'Yes', 'display' => 'Yes'),
		array('value' => 'No', 'display' => 'No')
		));
		$qu_materialBlocked->setRowTitle("material_blocked");
		$qu_materialBlocked->setValue("No");
		$qu_materialBlocked->setRequired(true);
		$qu_materialBlocked->setTable("complaint");
		if(isset($savedFields["sp_materialBlocked"]))
		$qu_materialBlocked->setValue($savedFields["qu_materialBlocked"]);
		else $qu_materialBlocked->setValue("No");

		// Dependency
		$materialBlockedDependency = new dependency();
		$materialBlockedDependency->addRule(new rule('materialBlockedGroup', 'qu_materialBlocked', 'Yes'));
		$materialBlockedDependency->setGroup('materialBlockedYes');
		$materialBlockedDependency->setShow(true);

		$qu_materialBlocked->addControllingDependency($materialBlockedDependency);
		$materialBlockedGroup->add($qu_materialBlocked);

		$qu_materialBlockedName = new textbox("qu_materialBlockedName");
		if(isset($savedFields["qu_materialBlockedName"]))
		$qu_materialBlockedName->setValue($savedFields["qu_materialBlockedName"]);
		$qu_materialBlockedName->setGroup("materialBlockedYes");
		$qu_materialBlockedName->setDataType("string");
		$qu_materialBlockedName->setLength(30);
		$qu_materialBlockedName->setRowTitle("material_blocked_name");
		$qu_materialBlockedName->setRequired(false);
		$qu_materialBlockedName->setTable("complaint");
		$materialBlockedYes->add($qu_materialBlockedName);

		$qu_materialBlockedDate = new calendar("qu_materialBlockedDate");
		if(isset($savedFields["qu_materialBlockedDate"]))
		$qu_materialBlockedDate->setValue($savedFields["qu_materialBlockedDate"]);
		$qu_materialBlockedDate->setGroup("materialBlockedYes");
		$qu_materialBlockedDate->setDataType("date");
		$qu_materialBlockedDate->setLength(30);
		$qu_materialBlockedDate->setRowTitle("material_blocked_date");
		$qu_materialBlockedDate->setRequired(false);
		$qu_materialBlockedDate->setTable("complaint");
		$materialBlockedYes->add($qu_materialBlockedDate);

		$processOwnerLink = new textboxlink("processOwnerLink");
		$processOwnerLink->setRowTitle("process_owner_link");
		$processOwnerLink->setHelpId(1111);
		$processOwnerLink->setLink("http://scapanet/apps/complaints/data/po.xls");
		$processOwnerLink->setValue("Process Owner Matrix - Europe");
		$sendToUser2->add($processOwnerLink);

		$processOwnerLink2 = new textboxlink("processOwnerLink2");
		$processOwnerLink2->setRowTitle("process_owner_link");
		$processOwnerLink2->setHelpId(1111);
		$processOwnerLink2->setLink("http://scapanet/apps/complaints/data/process_owner_matrix_na.xls");
		$processOwnerLink2->setValue("Process Owner Matrix - NA");
		$sendToUser2->add($processOwnerLink2);

		$processOwner = new autocomplete("processOwner");
		if(isset($savedFields["processOwner"]))
		$processOwner->setValue($savedFields["processOwner"]);
		$processOwner->setGroup("sendToUser2");
		$processOwner->setDataType("string");
		$processOwner->setErrorMessage("user_not_found");
		$processOwner->setRowTitle("complaint_owner");
		$processOwner->setRequired(true);
		$processOwner->setUrl("/apps/complaints/ajax/newProcessOwner?");
		$processOwner->setTable("complaint");
		$processOwner->setHelpId(8145);
		$sendToUser2->add($processOwner);

		$copy_to = new multipleCC("copy_to");
		if(isset($savedFields["copy_to"]))
		$copy_to->setValue($savedFields["copy_to"]);
		$copy_to->setGroup("sendToUser2");
		$copy_to->setDataType("text");
		$copy_to->setRowTitle("CC_customer");
		$copy_to->setRequired(false);
		$copy_to->setIgnore(true);
		$copy_to->setTable("complaint");
		$copy_to->setHelpId(8146);
		$sendToUser2->add($copy_to);

		//		if(!isset($savedFields["0|copy_to"])){//the first one will always need to be set if its saved
		//			$copy_to = new autocomplete("copy_to");
		//			if(isset($savedFields["0|copy_to"]))
		//				$copy_to->setValue($savedFields["0|copy_to"]);
		//			$copy_to->setGroup("ccComplaintGroup");
		//			$copy_to->setDataType("string");
		//			$copy_to->setRowTitle("CC");
		//			$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//			//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//			$copy_to->setRequired(false);
		//			$copy_to->setTable("ccGroup");
		//			$copy_to->setHelpId(8146);
		//			$ccComplaintGroup->add($copy_to);
		//		}else{
		//			$this->form->groupsToExclude[] = "ccComplaintGroup";
		//			$i=0;
		//			$endList = false;
		//			do{
		//				if(!isset($savedFields[$i."|copy_to"])){
		//					$maxList = $i;
		//					$endList = true;
		//				}
		//				$i++;
		//			}while(!$endList);
		//			for($i=0; $i<$maxList; $i++){
		//				if($i==0){//first will always be set
		//					$copy_to = new autocomplete("copy_to");
		//					if(isset($savedFields["0|copy_to"]))
		//						$copy_to->setValue($savedFields["0|copy_to"]);
		//					$copy_to->setGroup("ccComplaintGroup");
		//					$copy_to->setDataType("string");
		//					$copy_to->setRowTitle("CC");
		//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//					$copy_to->setRequired(false);
		//					$copy_to->setTable("ccGroup");
		//					$copy_to->setHelpId(8146);
		//					$ccComplaintGroup->add($copy_to);
		//				}else{
		//
		//					$copy_to = new autocomplete("copy_to");
		//					if(isset($savedFields[$i."|copy_to"]))
		//						$copy_to->setValue($savedFields[$i."|copy_to"]);
		//					$copy_to->setGroup("ccComplaintGroup");
		//					$copy_to->setDataType("string");
		//					$copy_to->setRowTitle("CC");
		//					$copy_to->setUrl("/apps/complaints/ajax/copyToMulti?");
		//					//$copy_to->setSQLSource("membership","SELECT DISTINCT CONCAT(firstName, ' ',lastName) AS name, employee.NTLogon AS value FROM `permissions` INNER JOIN `employee` ON employee.ntlogon=permissions.ntlogon ORDER BY employee.firstName, employee.lastName ASC");
		//					$copy_to->setRequired(false);
		//					$copy_to->setTable("ccGroup");
		//					$copy_to->setHelpId(8146);
		//					//$ccComplaintGroup->add($copy_to);
		//
		//
		//					$ccComplaintGroup->addRowCustom($savedFields[$i."|copy_to"]);
		//				}
		//			}
		//		}

		$email_text = new textarea("email_text");
		if(isset($savedFields["email_text"]))
		$email_text->setValue($savedFields["email_text"]);
		$email_text->setGroup("sendToUser2");
		$email_text->setDataType("text");
		$email_text->setRowTitle("email_text");
		$email_text->setRequired(false);
		$email_text->setTable("complaint");
		$email_text->setHelpId(8045);
		$sendToUser22->add($email_text);

		$submit = new submit("submit");
		$submit->setGroup("sendToUser");
		$submit->setVisible(true);
		$sendToUser22->add($submit);


		$this->form->add($initiation);
		$this->form->add($submitOnBehalfGroup);
		$this->form->add($groupComplaint);
		$this->form->add($groupComplaintYes);
		$this->form->add($typeOfComplaintGroup);
		$this->form->add($complaintDetails);
		$this->form->add($lineStoppageGroup);
		$this->form->add($lineStoppageYes);
		$this->form->add($complaintDetails2);
		$this->form->add($materialInvolvedGroup);
		$this->form->add($materialInvolvedYes);
		$this->form->add($sapGroup);
		$this->form->add($materialGroupGroup);
		//$this->form->add($sapItemNumberGroupedTGroup);
		$this->form->add($complaintDetails3);
		$this->form->add($materialBlockedGroup);
		$this->form->add($materialBlockedYes);
		$this->form->add($sendToUser2);
		$this->form->add($sendToUser22);

	}

	public function getSiteConcerned()
	{
		// This is required to update the SQLSource on the WhereErrorOccurred field to add values.

		if(isset($_POST['sp_siteConcerned']))
		{
			$site = $_POST['sp_siteConcerned'];
		}
		elseif(isset($_SESSION['apps'][$GLOBALS['app']]['complaint']['complaintDetails']['sp_siteConcerned']))
		{
			$site = $_SESSION['apps'][$GLOBALS['app']]['complaint']['complaintDetails']['sp_siteConcerned'];
		}
		else
		{
			$site = "";
		}

		return $site;
	}

	public function getEmailNotification($owner, $sender, $id, $action, $email_text, $externalDate = "")
	{
		// newAction, email the owner
		$dom = new DomDocument;

		if($this->getComplaintType($this->id) == "supplier_complaint")
		{
			$dom->loadXML("<$action><action>" . $id . "</action><defaultLanguage>" . $this->form->get("supplierDefaultLanguage")->getValue() . "</defaultLanguage><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><email_text>" . utf8_decode($email_text) . "</email_text><buyer_name>" . usercache::getInstance()->get($this->form->get("sp_buyer")->getValue())->getName() . "</buyer_name><buyer_email>" . usercache::getInstance()->get($this->form->get("sp_buyer")->getValue())->getEmail() . "</buyer_email><buyer_phone>" . usercache::getInstance()->get($this->form->get("sp_buyer")->getValue())->getPhone() . "</buyer_phone><external_date>" . page::transformDateTimeForPHP($externalDate) . "</external_date></$action>");
		}
		else
		{
			$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><email_text>" . utf8_decode($email_text) . "</email_text></$action>");
		}

		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/complaints/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT copy_to FROM ccGroup WHERE `complaintId` = '" . $id . "'");

		$cc = "";

		$cc = $this->form->get("copy_to")->getValue();

		email::send($owner, /*"intranet@scapa.com"*/$sender, translate::getInstance()->translate('new_complaint_action') . " - ID: " . $id, "$email", "$cc");
		//email::send($owner, /*"intranet@scapa.com"*/$sender, "Nouvelle action concernant la réclamation ou le constat - ID: " . $id, "$email", "$cc");

		return true;
	}

	public function getComplaintType($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT typeOfComplaint FROM complaint WHERE `id` = '" . $id . "'");

		$fields = mysql_fetch_array($dataset);

		$complaintType = $fields['typeOfComplaint'];

		return $complaintType;
	}

	/**
	 * gets complaint form type from saved forms data
	 *
	 * @params string $id form id
	 */
	public function getSavedComplaintType($id = null)
	{
		if (null === $id) {
			$id = $this->id;
		}

		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfTypeOfComplaint FROM savedForms WHERE sfID = '". $id . "'");

		$fields = mysql_fetch_array($dataset);

		$complaintType = $fields['sfTypeOfComplaint'];

		return $complaintType;

	}


}

?>