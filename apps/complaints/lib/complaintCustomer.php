<?php
require 'complaintsProcess.php';
require 'complaintExternal.php';
require 'evaluation.php';
require 'conclusion.php';

/**
 * This is the CUSTOMER Complaints Application.
 *
 * @package apps
 * @subpackage complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/08/2009
 */

class complaintCustomer
{
	private $id;
	private $status;
	public $form;
	public $attachments;
	private $loadedFromDatabase = false;

	function __construct($loadFromSession = true)
	{
		$this->loadFromSession = $loadFromSession;

		if (isset($_SESSION['apps'][$GLOBALS['app']]['complaint']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true; //checks if the Complaint is loaded from the database
		}

		// Take the ID from the REQUEST before going to the session...
		if(isset($_REQUEST['id']))
		{
			$this->id = $_REQUEST['id'];
		}
		else
		{
			if(isset($_SESSION['apps'][$GLOBALS['app']]['id']))
			{
				$this->id = $_SESSION['apps'][$GLOBALS['app']]['id']; //checks if there is a Complaint id in the session
			}
		}

		$this->defineForm();
		
		if($this->loadFromSession)
		{
			$this->form->loadSessionData();
		}

		$this->loadSessionSections();

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
		$this->conclusion = new conclusion($this);
	}

	public function sapItemNumbers($complaintId)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM sapItemNumber WHERE `complaintId` = '" . $complaintId . "'");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$sapItemNumbers .= "" . $fields["sapItemNumber"] . ",";
		}
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET sapItemNumbers = '" . $sapItemNumbers . "' WHERE id = '" . $complaintId . "'");
	}

	public function sapMaterialGroup($complaintId)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE `complaintId` = '" . $complaintId . "'");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$sapMaterialGroups .= $fields["materialGroup"] . ",";
		}
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET sapMaterialGroups = '" . $sapMaterialGroups . "' WHERE id = '" . $complaintId . "'");
	}

	// Important!! for NA and Customer Evaluation
	public function getLocale($site)
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT region FROM sites WHERE name = '" . $site . "'");
		
		if(mysql_num_rows($dataset) > 0)
		{
			$fields = mysql_fetch_array($dataset);
			
			$region = $fields['region'];
		}
		else 
		{
			$region = "EUROPE";
		}
		
		return $region;
	}

	// Important!! for NA and Customer Evaluation
	public function determineNAOrEuropeEvaluationProcessRoute()
	{
		if($this->getLocale($this->form->get("salesOffice")->getValue()) == "USA")
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
		elseif($this->getLocale($this->form->get("salesOffice")->getValue()) == "EUROPE")
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

				if(is_array($dataFields))
				{
					foreach($dataFields as $key => $val)
					{
						$fields[$key] = $val;
					}
				}

				$loadedFromSavedForms = true;
			}

			$fields["dimensionThickness"] = array($fields['dimensionThickness_quantity'], $fields['dimensionThickness_measurement']);
			$fields["quantityUnderComplaint"] = array($fields['quantityUnderComplaint_quantity'], $fields['quantityUnderComplaint_measurement']);
			$fields["complaintValue"] = array($fields['complaintValue_quantity'], $fields['complaintValue_measurement']);
			$fields["dimensionWidth"] = array($fields['dimensionWidth_quantity'], $fields['dimensionWidth_measurement']);
			$fields["dimensionLength"] = array($fields['dimensionLength_quantity'], $fields['dimensionLength_measurement']);			
			
			$this->form->get("complaintValue")->setValue(array($fields['complaintValue_quantity'], $fields['complaintValue_measurement']));
			$this->form->get("quantityUnderComplaint")->setValue(array($fields['quantityUnderComplaint_quantity'], $fields['quantityUnderComplaint_measurement']));
			$this->form->get("customerComplaintDate")->setValue(page::transformDateForPHP($this->form->get("customerComplaintDate")->getValue()));

			$this->form->populate($fields);

			$this->form->get("attachment")->load("/apps/complaints/attachments/" . $this->id . "/");
			$this->form->get("carrierName")->getValue() == '' ? $this->form->get("carrierName")->setValue("N/A") : $this->form->get("carrierName")->getValue();

			if(!$loadedFromSavedForms || (isset($_REQUEST['status']) && ($_REQUEST['status'] == 'evaluation' || $_REQUEST['status'] == 'conclusion' )))
			{
				$this->form->getGroup('orderDetailsMulti')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaOrderNumber WHERE complaintId = " . $this->id . " ORDER BY `id`"));
			}

			$this->form->getGroup('scapaInvoiceYesGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaInvoiceNumberDate WHERE complaintId = " . $this->id . " ORDER BY `id`"));

			$this->form->getGroup('materialGroupGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM materialGroup WHERE complaintId = " . $this->id . " ORDER BY `id`"));

			$this->form->getGroup('intercoGroupYes')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaIntercoOrder WHERE complaintId = " . $this->id . " ORDER BY `id`"));

			$this->form->getGroup('sapGroup')->load(mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE `complaintId` = " . $this->id . " ORDER BY `id`"));
			
			if($this->form->get("submitOnBehalf")->getValue() == "no")
			{
				$this->form->get("internalSalesName")->setValue("");
			}

			$fields['sampleReceptionDate'] == "0000-00-00" ?  $this->form->get("sampleReceptionDate")->setValue("") : $this->form->get("sampleReceptionDate")->setValue(page::transformDateForPHP($fields['sampleReceptionDate']));
			$fields['sampleDate'] == "0000-00-00" ?  $this->form->get("sampleDate")->setValue("") : $this->form->get("sampleDate")->setValue(page::transformDateForPHP($fields['sampleDate']));

			$this->form->get("email_text")->setValue("");

			$this->form->putValuesInSession();  //puts all the form values into the sessions

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
		return $this->form->get("id")->getValue();
	}


	public function getEvaluationCustomer()
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

	public function getConclusionCustomer()
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

					if($this->form->get("submitOnBehalf")->getValue() == "yes")
					{
						$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

						$this->form->get("salesOffice")->setValue(usercache::getInstance()->get($this->form->get("internalSalesName")->getValue())->getSite());

						$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get($this->form->get("internalSalesName")->getValue())->getName());
					}
					else
					{
						$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

						$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getSite());

						$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName());
					}

					//if M or D selected default g8d to yes
					$cat = $this->form->get("category")->getValue();
					if($cat[0] == M || $cat[0] == D)
					{
						$this->form->get("g8d")->setValue("yes");
					}

					if($this->form->get("groupAComplaint")->getValue() == "Yes")
					{
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'No', groupedComplaintId = '' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->id . "' WHERE id = '" . $this->form->get("groupedComplaintId")->getValue() . "'");
						mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint SET groupAComplaint = 'Yes', groupedComplaintId = '" . $this->form->get("groupedComplaintId")->getValue() . "' WHERE id = '" . $this->id . "'");
					}
					
					$this->calculateCurrency($this->form->get("complaintValue")->getMeasurement());
					
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
					

					// Check Fields Changed Function
					$this->checkFieldsUpdated();

					// update
					mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE complaint " . $this->form->generateUpdateQuery("complaint") . " WHERE id= " . $this->id . "");

					$this->form->get("attachment")->setFinalFileLocation("/apps/complaints/attachments/" . $this->id . "/");
					$this->form->get("attachment")->moveTempFileToFinal();
					
					// save new data
					$this->addLog(translate::getInstance()->translate("complaint_updated_send_to") . " - " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "", $this->form->get("email_text")->getValue());
				}
				else
				{
					// set Complaint owner
					$this->form->get("owner")->setValue($this->form->get("processOwner")->getValue());
					
					if($this->form->get("submitOnBehalf")->getValue() == "yes")
					{
						$this->getComplaintLocaleSettings($this->form->get("internalSalesName")->getValue());

						$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . $this->form->get("internalSalesName")->getValue() ."'");
						$fieldsEmp = mysql_fetch_array($datasetEmp);

						$this->form->get("salesOffice")->setValue($fieldsEmp['site']);

						// set Internal sales name
						$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get($fieldsEmp['NTLogon'])->getName());
					}
					else
					{
						$this->getComplaintLocaleSettings(currentuser::getInstance()->getNTlogon());

						//$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT `site`, `NTLogon`, `locale` FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTlogon() ."'");
						//$fieldsEmp = mysql_fetch_array($datasetEmp);

						//$this->form->get("salesOffice")->setValue($fieldsEmp['site']);
						$this->form->get("salesOffice")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getSite());

						// set Internal Sales name
						$this->form->get("internalSalesName")->setValue(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName());
					}

					//if M or D selected default g8d to yes
					$cat = $this->form->get("category")->getValue();
					if($cat[0] == 'M' || $cat[0] == 'D')
					{
						$this->form->get("g8d")->setValue("yes");
					}
					
					$this->calculateCurrency($this->form->get("complaintValue")->getMeasurement());
					
					// set report date
					$this->form->get("openDate")->setValue(common::nowDateForMysql());

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
				}

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

				$this->getEmailNotification(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getEmail(), $this->id, "newComplaint", utf8_encode($this->form->get("email_text")->getValue()));
				
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

	public function checkFieldsUpdated()
	{
		// Check Current Field Values
		$currentCategory = $this->form->get("category")->getValue();
		$currentProblemDescription = $this->form->get("problemDescription")->getValue();

		$currentLineStoppage = $this->form->get("lineStoppage")->getValue();
		$currentSampleReceived = $this->form->get("sampleReceived")->getValue();
		
		$currentQuantityUnderComplaint_quantity = $this->form->get("quantityUnderComplaint")->getQuantity();
		$currentQuantityUnderComplaint_measurement = $this->form->get("quantityUnderComplaint")->getMeasurement();
		$currentComplaintValue_quantity = $this->form->get("complaintValue")->getQuantity();
		$currentComplaintValue_measurement = $this->form->get("complaintValue")->getMeasurement();
		
		// Check Updated Field Values
		$checkUpdated = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `category`, `problemDescription`, `lineStoppage`, `sampleReceived`, `quantityUnderComplaint_quantity`, `quantityUnderComplaint_measurement`, `complaintValue_quantity`, `complaintValue_measurement` FROM complaint WHERE id = " . $this->id . "");
		$fieldsUpdated = mysql_fetch_array($checkUpdated);

		$newCategory = $fieldsUpdated['category'];
		$newProblemDescription = $fieldsUpdated['problemDescription'];
		$newLineStoppage = $fieldsUpdated['lineStoppage'];
		$newQuantityUnderComplaint_quantity = $fieldsUpdated['quantityUnderComplaint_quantity'];
		$newQuantityUnderComplaint_measurement = $fieldsUpdated['quantityUnderComplaint_measurement'];
		$newComplaintValue_quantity = $fieldsUpdated['complaintValue_quantity'];
		$newComplaintValue_measurement = $fieldsUpdated['complaintValue_measurement'];

		$newSampleReceived = $fieldsUpdated['sampleReceived'];

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

		if($currentLineStoppage != $newLineStoppage)
		{
			$updatedFields .= "Line Stoppage: Old(" . $newLineStoppage . ") New(" . $currentLineStoppage . ") - ";
		}

		if($currentQuantityUnderComplaint_quantity != $newQuantityUnderComplaint_quantity || $currentQuantityUnderComplaint_measurement != $newQuantityUnderComplaint_measurement)
		{
			$updatedFields .= "Quantity Under Complaint: Old(" . $newQuantityUnderComplaint_quantity . " " . $newQuantityUnderComplaint_measurement . ") New(" . $currentQuantityUnderComplaint_quantity . " " . $currentQuantityUnderComplaint_measurement . ") - ";
		}

		if($currentComplaintValue_quantity != $newComplaintValue_quantity || $currentComplaintValue_measurement != $newComplaintValue_measurement)
		{
			$updatedFields .= "Complaint Value: Old(" . $newComplaintValue_quantity . " " . $newComplaintValue_measurement . ") New(" . $currentComplaintValue_quantity . " " . $currentComplaintValue_measurement . ") - ";
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

	public function getComplaintLocaleSettings($locale)
	{
		if(usercache::getInstance(currentuser::getInstance()->getNTLogon())->get()->getLocale() == "USA" || usercache::getInstance(currentuser::getInstance()->getNTLogon())->get()->getLocale() == "CANADA")
		{
			$this->form->get("complaintLocation")->setValue("american");
		}
		elseif(usercache::getInstance(currentuser::getInstance()->getNTLogon())->get()->getLocale() == "MALAYSIA")
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

		$value = $this->form->get("complaintValue")->getQuantity() * $currencyConversionFields['currencyValue'];

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
		if(isset($_REQUEST["sfID"]))
		{
			$this->sfID = $_REQUEST["sfID"];
			
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sfValue FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' AND sfID = '".$this->sfID."' LIMIT 1");
			
			while ($fields = mysql_fetch_array($dataset))
			{
				$savedFields = unserialize($fields["sfValue"]);
			}
		}

		$today = date("Y-m-d",time());
		$next_week_date = date("Y-m-d",time() + 604800);

		// define the actual form
		$this->form = new form("complaint");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		$this->form->groupsToExclude = array();

		$initiation = new group("initiation");
		$typeOfComplaintGroup = new group("typeOfComplaintGroup");
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

		$actionsGroup2 = new group("actionsGroup2");
		$actionsGroup2->setBorder(false);

		$sendToUser = new group("sendToUser");
		$sendToUser->setBorder(false);

		if(isset($_REQUEST["printAll"]) || (isset($_REQUEST["print"]) && $_REQUEST["status"] == "complaint"))
		{
			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint WHERE id = '"  . $_REQUEST['complaint'] . "' LIMIT 1");

			$fields2 = mysql_fetch_array($dataset);
			
			if($fields2)
			{
				foreach ($fields2 as $key => $value)
				{
					if($value)
					{
						if(!strtotime($value) && $value != "0000-00-00")
						{
							$savedFields[$key] = $value;
						}
						elseif(strtotime($value) && $value != "0000-00-00")
						{
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
		}
		else
		{
			$this->form->groupsToExclude[] = "orderDetailsMulti";
			$i=0;
			$endList = false;
			do{
				if(!isset($savedFields[$i."|scapaOrderNumber"]))
				{
					$maxList = $i;
					$endList = true;
				}
				
				$i++;
				
			} while(!$endList);
			
			for($i=0; $i<$maxList; $i++)
			{
				if($i==0)
				{
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
				}
				else
				{
					$orderDetailsMulti->addRowCustom($savedFields[$i."|scapaOrderNumber"]);
				}
			}
		}

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

		if(!isset($savedFields["0|scapaInvoiceNumber"]))
		{
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
		$batchNumber->setDataType("string");
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
		$this->form->add($sampleGroup);
		$this->form->add($sampleReceivedYes);
		$this->form->add($lineStoppageGroup);
		$this->form->add($lineStoppageYes);
		$this->form->add($actionsGroup);
		$this->form->add($actionsGroup2);
		$this->form->add($sendToUser);

	}

	public function getEmailNotification($owner, $sender, $id, $action, $email_text, $externalDate = "")
	{
		// newAction, email the owner
		$dom = new DomDocument;

		$dom->loadXML("<$action><action>" . $id . "</action><sent_from>" . usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName() . "</sent_from><email_text>" . utf8_decode($email_text) . "</email_text></$action>");
		
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

		return true;
	}
}

?>