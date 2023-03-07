<?php

require 'technical.php';
require 'material.php';
require 'action.php';
require 'reportaction.php';
require 'materialaction.php';
require 'opportunity.php';


class ccr
{
	private $materials = array();
	private $actions = array();
	private $technical = array();
	
	private $id;
	public $form;
	
	public $attachments;
	
	private $loadedFromDatabase = false;
	
	
	private $customerName = "";
	

	
	
	
	function __construct()
	{
		$this->defineForm();
			
		$this->form->loadSessionData();
			
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['report']['loadedFromDatabase']))
		{
			$this->loadedFromDatabase = true;
		}
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id'];
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['owner']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = "";
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['customerName']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['customerName'] = "";
		}
		
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['complete']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = false;
		}
	
		$this->loadSessionTechnicals();
		
		$this->loadSessionMaterials();
		
		$this->loadSessionActions();
		
		$this->updateCurrencyLegend();
		
		$this->form->processDependencies();
	}
	
	
	

	public function load($id)
	{
		// save any filters!
		if (isset($_SESSION['apps'][$GLOBALS['app']]['selectedFilters']) && isset($_SESSION['apps'][$GLOBALS['app']]['filters']) && isset($_SESSION['apps'][$GLOBALS['app']]['chooseReportForm']))
		{
			$selectedFilters = $_SESSION['apps'][$GLOBALS['app']]['selectedFilters'];
			$filters = $_SESSION['apps'][$GLOBALS['app']]['filters'];
			$chooseReportForm = $_SESSION['apps'][$GLOBALS['app']]['chooseReportForm'];
		}
		
		unset ($_SESSION['apps'][$GLOBALS['app']]);
		
		if (isset($selectedFilters))
		{
			$_SESSION['apps'][$GLOBALS['app']]['selectedFilters'] = $selectedFilters;
			$_SESSION['apps'][$GLOBALS['app']]['filters'] = $filters;
			$_SESSION['apps'][$GLOBALS['app']]['chooseReportForm'] = $chooseReportForm;
		}
		
		$this->materials = array();
		$this->actions = array();
		$this->technical = array();
		
		$this->form->setStoreInSession(true);
		
		
		if (!is_numeric($id))
		{
			return false;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM report WHERE id = $id");
				
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['report']['loadedFromDatabase'] = true;
			
			// load data into array
			$fields = mysql_fetch_array($dataset);
			
			// populate form items
			$this->form->populate($fields);
			
			
			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			
			$_SESSION['apps'][$GLOBALS['app']]['complete'] = $fields['status'] == 1 ? true : false;
			
			$_SESSION['apps'][$GLOBALS['app']]['owner'] = $fields['owner'];
			
			if ($fields['typeOfCustomer'] == 'new_customer' || $fields['typeOfCustomer'] == 'customer_distributor')
			{
				$_SESSION['apps'][$GLOBALS['app']]['customerName'] = page::xmlentities($fields['name']);
			}
			else 
			{
				$_SESSION['apps'][$GLOBALS['app']]['customerName'] = page::xmlentities($fields['directCustomerName']);
			}
			
			
			
			$this->form->get("attachment")->load("/apps/ccr/attachments/reports/" . $this->id . "/");
			
			
			// lets sort out the data properly
			
			// convert to correct date format
			//$this->form->get('contactDate')->setValue(page::transformDateForPHP($this->form->get('contactDate')->getValue()));
			
			//$this->form->get('completionDate')->setValue($this->form->get('completionDate')->getValue() == '0000-00-00' ? "Not Complete" : page::transformDateForPHP($this->form->get('completionDate')->getValue()));
			
			if ($this->form->get('completionDate')->getValue() == '00/00/0000')
			{
				$this->form->get('completionDate')->setValue("Not Complete");
			}
			
			switch($this->form->get('status')->getValue())
			{
				case '0':
					
					$this->form->get('status')->setValue(translate::getInstance()->translate("in_progress"));
					break;
					
				case '1':
					
					$this->form->get('status')->setValue(translate::getInstance()->translate("completed"));
					break;
			}
				
			
			
			// sort out the customer type			
			
			switch ($fields['typeOfCustomer'])
			{
				case 'existing_client':
				
					//$this->form->get('sapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingClientSapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingDistributorSapNumber')->setValue($fields['sapNumber']);
					$this->form->get('customerOfDistributorSapNumber')->setValue($fields['sapNumber']);
					break;
					
				
				case 'existing_distributor':
				
					//$this->form->get('sapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingClientSapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingDistributorSapNumber')->setValue($fields['sapNumber']);
					
					$this->form->get('customerOfDistributorSapNumber')->setValue($fields['sapNumber']);
					break;
					
				case 'customer_distributor':
		
					//$this->form->get('sapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingClientSapNumber')->setValue($fields['sapNumber']);
					$this->form->get('existingDistributorSapNumber')->setValue($fields['sapNumber']);
					$this->form->get('customerOfDistributorSapNumber')->setValue($fields['sapNumber']);
					
					$this->form->get('customerOfDistributorName')->setValue(page::xmlentities($fields['name']));
					$this->form->get('customerOfDistributorAddress')->setValue(page::xmlentities($fields['address']));
					$this->form->get('customerOfDistributorCountry')->setValue($fields['country']);
					$this->form->get('customerOfDistributorGroup')->setValue($fields['group']);
					$this->form->get('customerOfDistributorJointCall')->setValue($fields['jointCall']);
					$this->form->get('customerOfDistributorExisting')->setValue($fields['existing']);	
					break;
					
				case 'new_customer':
				
					$this->form->get('newCustomerName')->setValue(page::xmlentities($fields['name']));
					$this->form->get('newCustomerAddress')->setValue(page::xmlentities($fields['address']));
					$this->form->get('newCustomerCountry')->setValue($fields['country']);
					$this->form->get('newCustomerGroup')->setValue($fields['group']);				
					break;
			}
			

			
			$this->form->putValuesInSession();
						
			$actionDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM action WHERE parentId = " . $this->id . " AND type='ccr' ORDER BY id");
		
			while($actionFields = mysql_fetch_array($actionDataset))
			{
				$id = count($this->actions);
				$this->actions[] = new reportaction($_SESSION['apps'][$GLOBALS['app']]['action']);
				$this->actions[$id]->load($actionFields['id']);
				$this->actions[$id]->setReportId($this->id);
			}
			
		
			
			$materialDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM material WHERE ccrId = " . $this->id . " ORDER BY id");
		
			while($materialFields = mysql_fetch_array($materialDataset))
			{
				$id = count($this->materials);
				$this->materials[] = new material();
				$this->materials[$id]->load($materialFields['id']);
				$this->materials[$id]->setCcrId($this->id);
			}
			
			
			$technicalDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM technical WHERE ccrId = " . $this->id . " ORDER BY id");
		
			while($technicalFields = mysql_fetch_array($technicalDataset))
			{
				$id = count($this->technical);
				$this->technical[] = new technical();
				$this->technical[$id]->load($technicalFields['id']);
				$this->technical[$id]->setCcrId($this->id);
			}
			
			
			$this->updateCurrencyLegend();
			
				
			
			$this->form->processDependencies();
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	public function complete()
	{
		$this->save();
			
		mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("UPDATE report SET `completionDate`='%s', `finalComments`='%s', `status`=1 WHERE id='%u'",
			common::nowDateForMysql(),
			$this->form->get("finalComments")->getValue(),
			$this->id
		));
		
		$this->form->get("completionDate")->setValue(common::nowDateForPHP());
		$this->form->get("status")->setValue(translate::getInstance()->translate("completed"));
		
		$this->addLog("Completed CCR");
	}
	
	
	public function updateCurrencyLegend()
	{
		for ($i=0; $i < count($this->technical); $i++)
		{
			$this->getTechnical($i)->form->get("incomeQuantity")->setLegend($this->form->get("currency")->getValue());
		}
		
		for ($i=0; $i < count($this->materials); $i++)
		{
			$this->getMaterial($i)->form->get("incomeQuantity")->setLegend($this->form->get("currency")->getValue());
		}
	}
	
	
	
	public function save()
	{
		if($this->form->get('status')->getValue() == translate::getInstance()->translate("in_progress"))
		{
			$this->form->get('status')->setValue(0);
		}
		elseif($this->form->get('status')->getValue() == translate::getInstance()->translate("completed"))
		{
			$this->form->get('status')->setValue(1);
		}
		
		$this->form->get('status')->setIgnore(false);
		
		$this->getSAPCustomerDetails();
		
		
		
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE report " . $this->form->generateUpdateQuery("report") . " WHERE id='" . $this->id . "'");
			
			// delete existing records
			//mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM customer WHERE ccrId=" . $this->id);
			
			// save new data
			$this->saveCustomerBit($this->id);
			
			$this->addLog("Report updated");
		}
		else 
		{
			// set user
			$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
			
			// set report date
			$this->form->get("reportDate")->setValue(common::nowDateForMysql());
			
			
			// begin transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO report " . $this->form->generateInsertQuery("report"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM report ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			
			// end transaction
			mysql::getInstance()->selectDatabase("CCR")->Execute("COMMIT");
			
			
			$this->id = $fields['id'];
			
			// save customer data
			$this->saveCustomerBit($this->id);
			
			$this->addLog("Report added");
		}
		
		
		$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/reports/" . $this->id . "/");
		$this->form->get("attachment")->moveTempFileToFinal();
		
		
		$actionList = array();
		
		for ($i=0; $i < count($this->actions); $i++)
		{
			$this->actions[$i]->setReportId($this->id);
			$this->actions[$i]->save();
			
			$actionList[] = $this->actions[$i]->form->getDatabaseId();
		}
		
		if (count($actionList) > 0)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=" . $this->id . " AND type='ccr' AND NOT id IN (" . implode(",",$actionList) . ")");
		}
		else 
		{
			// delete all
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM action WHERE parentId=" . $this->id . " AND type='ccr'");
		}
		
		
		
		$materialList = array();
		
		for ($i=0; $i < count($this->materials); $i++)
		{
			$this->materials[$i]->setCcrId($this->id);
			$this->materials[$i]->save();
			
			$materialList[] = $this->materials[$i]->form->getDatabaseId();
		}
		
		if (count($materialList) > 0)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM material WHERE ccrId=" . $this->id . " AND NOT id IN (" . implode(",",$materialList) . ")");
		}
		else 
		{
			// delete all
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM material WHERE ccrId=" . $this->id);
		}
		
		
		
		$technicalList = array();
		
		for ($i=0; $i < count($this->technical); $i++)
		{
			$this->technical[$i]->setCcrId($this->id);
			$this->technical[$i]->save();
			
			$technicalList[] = $this->technical[$i]->form->getDatabaseId();
		}
		
		if (count($technicalList) > 0)
		{
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM technical WHERE ccrId=" . $this->id . " AND NOT id IN (" . implode(",",$technicalList) . ")");
		}
		else 
		{
			// delete all
			mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM technical WHERE ccrId=" . $this->id);
		}
		
		//page::redirect("/apps/ccr/");
	}
	
	
	public function isComplete()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['complete'];
	}
	
	
	public function saveCustomerBit($id)
	{
		switch ($this->form->get('typeOfCustomer')->getValue())
		{
			case 'customer_distributor':
			
				mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("UPDATE report SET `name`='%s', `address`='%s', `country`='%s', `group`='%s', `jointCall`='%s', `existing`='%s' WHERE `id`=%u",
					//$this->form->get('customerOfDistributorSapNumber')->getValue(),
					$this->form->get('customerOfDistributorName')->getValue(),
					$this->form->get('customerOfDistributorAddress')->getValue(),
					$this->form->get('customerOfDistributorCountry')->getValue(),
					$this->form->get('customerOfDistributorGroup')->getValue(),
					$this->form->get('customerOfDistributorJointCall')->getValue(),
					$this->form->get('customerOfDistributorExisting')->getValue(),
					$id
				));
				
				break;
				
				
			case 'new_customer':
			
				mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("UPDATE report SET `sapNumber`=NULL, `name`='%s', `address`='%s', `country`='%s', `group`='%s' WHERE `id`=%u",
					//$this->form->get('sapNumber')->getValue(),
					$this->form->get('newCustomerName')->getValue(),
					$this->form->get('newCustomerAddress')->getValue(),
					$this->form->get('newCustomerCountry')->getValue(),
					$this->form->get('newCustomerGroup')->getValue(),
					$id
				));
				
				break;	
		}
	}
	
	
	public function getSAPCustomerDetails()
	{
		$isSap = true;
		
		switch ($this->form->get('typeOfCustomer')->getValue())
		{
			case 'existing_client':
				
				$this->form->get("sapNumber")->setValue($this->form->get('existingClientSapNumber')->getValue());
				break;
				
			case 'customer_distributor':
				
				$this->form->get("sapNumber")->setValue($this->form->get('customerOfDistributorSapNumber')->getValue());
				break;
				
			case 'existing_distributor':
				
				$this->form->get("sapNumber")->setValue($this->form->get('existingDistributorSapNumber')->getValue());
				break;
				
			default:
				
				$isSap = false;
				// stops it being used.
				$this->form->get("sapNumber")->setTable("dummy");
				break;
		}
		
		if ($isSap)
		{
			$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute(sprintf("SELECT * FROM customer WHERE `id`=%u", 
				$this->form->get("sapNumber")->getValue()
			));
			
			if ($fields = mysql_fetch_array($dataset))
			{
				$this->form->get("directCustomerName")->setValue(page::xmlentities($fields['name']));
				$this->form->get("directCustomerAddress")->setValue(page::xmlentities($fields['address'] . "\n" . $fields['city'] . "\n" . $fields['postcode']));
				$this->form->get("directCustomerCountry")->setValue(page::xmlentities($fields['country']));
			}
		}
	}
	
	
	/*public function getSAPCustomerName()
	{
		$id = "";
		
		switch ($this->form->get('typeOfCustomer')->getValue())
		{
			case 'existing_client':
				
				$id = $this->form->get('existingClientSapNumber')->getValue();
				break;
				
			case 'customer_distributor':
				
				$id = $this->form->get('customerOfDistributorSapNumber')->getValue();
				break;
				
			default:
				
				$id = $this->form->get('existingDistributorSapNumber')->getValue();
				break;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute(sprintf("SELECT name FROM customer WHERE `id`=%u", 
			$id
		));
		
		$name = "";
		
		if ($fields = mysql_fetch_array($dataset))
		{
			$name = $fields['name'];	
		}
		
		return page::xmlentities($name);
	}*/
	
	public function getCustomerName()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['customerName'];
	}
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("INSERT INTO log (ccrId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
			$this->id,
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql()
		));
	}
	
	
	public function getOwner()
	{
		return $_SESSION['apps'][$GLOBALS['app']]['owner'];
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	
	
	
	
	
	
	public function addTechnical()
	{
		$id = count($this->technical);
		$this->technical[] = new technical();
		$this->getTechnical($id)->setCcrId($this->id);

		$this->getTechnical($id)->form->get("incomeQuantity")->setLegend($this->form->get("currency")->getValue());
		
		return $id;
	}
	
	
	public function removeTechnical($id)
	{
		for ($i=$id; $i < count($this->technical); $i++)
		{
			if (isset($this->technical[$i+1]))
			{
				$this->technical[$i] = $this->technical[$i+1];
				$this->technical[$i]->id = $i;
				$this->technical[$i]->form->setMultipleFormSessionId($i);
				
				$_SESSION['apps'][$GLOBALS['app']]['technical'][$i] = $_SESSION['apps'][$GLOBALS['app']]['technical'][$i+1];
			}
			else 
			{
				unset($this->technical[$i]);
				unset ($_SESSION['apps'][$GLOBALS['app']]['technical'][$i]);
			}
			
		}
	}
	
	
	public function getTechnical($id)
	{
		return $this->technical[$id];
	}
	
	public function getTechnicals()
	{
		return $this->technical;
	}
	
	public function loadSessionTechnicals()
	{
		$this->technical = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['technical']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['technical']); $i++)
			{
				$this->technical[] = new technical($i);
				$this->getTechnical($i)->setCcrId($this->id);
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	public function addMaterial()
	{
		$id = count($this->materials);
		$this->materials[] = new material();
		$this->getMaterial($id)->setCcrId($this->id);

		$this->getMaterial($id)->form->get("incomeQuantity")->setLegend($this->form->get("currency")->getValue());
		
		return $id;
	}
	
	
	public function removeMaterial($id)
	{
		for ($i=$id; $i < count($this->materials); $i++)
		{
			if (isset($this->materials[$i+1]))
			{
				$this->materials[$i] = $this->materials[$i+1];
				$this->materials[$i]->id = $i;
				$this->materials[$i]->form->setMultipleFormSessionId($i);
				
				$_SESSION['apps'][$GLOBALS['app']]['material'][$i] = $_SESSION['apps'][$GLOBALS['app']]['material'][$i+1];
			}
			else 
			{
				unset($this->materials[$i]);
				unset ($_SESSION['apps'][$GLOBALS['app']]['material'][$i]);
			}
			
		}
	}
	
	
	public function getMaterial($id)
	{
		return $this->materials[$id];
	}
	
	public function getMaterials()
	{
		return $this->materials;
	}
	
	public function loadSessionMaterials()
	{
		$this->materials = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['material']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['material']); $i++)
			{
				$this->materials[] = new material($i);
				$this->getMaterial($i)->setCcrId($this->id);
			}
		}
	}
	
	
	public function loadSessionActions()
	{
		$this->actions = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['action']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['action']); $i++)
			{
				$this->actions[] = new reportaction($_SESSION['apps'][$GLOBALS['app']]['action'], $i);
				$this->getAction($i)->setReportId($this->id);
			}
		}
	}
	
	public function addAction()
	{
		$id = count($this->actions);
		$this->actions[] = new reportaction($_SESSION['apps'][$GLOBALS['app']]['action']);
		$this->getAction($id)->setReportId($this->id);
		
		return $id;
	}
	
	
	public function removeAction($id)
	{
		for ($i=$id; $i < count($this->actions); $i++)
		{
			if (isset($this->actions[$i+1]))
			{
				$this->actions[$i] = $this->actions[$i+1];
				$this->actions[$i]->id = $i;
				$this->actions[$i]->form->setMultipleFormSessionId($i);
				
				$_SESSION['apps'][$GLOBALS['app']]['action'][$i] = $_SESSION['apps'][$GLOBALS['app']]['action'][$i+1];
			}
			else 
			{
				unset($this->actions[$i]);
				unset ($_SESSION['apps'][$GLOBALS['app']]['action'][$i]);
			}
			
		}
	}
	
	
	public function getAction($id)
	{
		return $this->actions[$id];
	}
	
	public function getActions()
	{
		return $this->actions;
	}
	
	
	public function validate()
	{
		$valid = true;
		
		if (!$this->form->validate())
		{
			$valid = false;
		}
		
		for ($i=0; $i < count($this->actions); $i++)
		{
			if (!$this->getAction($i)->validate())
			{
				$valid = false;
			}
		}
		
		for ($i=0; $i < count($this->technical); $i++)
		{
			if (!$this->getTechnical($i)->validate())
			{
				$valid = false;
			}
		}
		
		for ($i=0; $i < count($this->materials); $i++)
		{
			if (!$this->getMaterial($i)->validate())
			{
				$valid = false;
			}
		}
		
		
		return $valid;
	}
	
	
	
	public function showCompletionBits($outputType)
	{
		if ($this->loadedFromDatabase)
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
	}
	
	
	public function getEntireLogOutput()
	{
		$xml = "<log>";
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->execute(sprintf("SELECT * FROM `log` WHERE `ccrId`=%u ORDER BY `logDate` DESC", 
			$this->getId()
		));
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$xml .= "<item>";
			$xml .= "<area>" . $fields['area'] . "</area>\n";
			$xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
			$xml .= "<date>" . page::transformDateTimeForPHP($fields['logDate']) . "</date>\n";
			$xml .= "<action>" . $fields['action'] . "</action>\n";
			$xml .= "</item>";
		}
		
		$xml .= "</log>";
		
		return $xml;
	}
	
	
	public function getLogOutput()
	{
		$xml = "<log>";
		
		$dataset = mysql::getInstance()->selectDatabase("CCR")->execute(sprintf("SELECT * FROM `log` WHERE `ccrId`=%u AND `area` IN ('report', '') ORDER BY `logDate` DESC", 
			$this->getId()
		));
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$xml .= "<item>";
			$xml .= "<area>" . $fields['area'] . "</area>\n";
			$xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
			$xml .= "<date>" . page::transformDateTimeForPHP($fields['logDate']) . "</date>\n";
			$xml .= "<action>" . $fields['action'] . "</action>\n";
			$xml .= "</item>";
		}
		
		$xml .= "</log>";
		
		return $xml;
	}
	
	

	
	public function defineForm()
	{
		$today = date("d/m/Y",time());
		
		// define the actual form
		$this->form = new form("report");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		
		// set-up groups
		// these are collections of form items that have dependencies, so will be used to show and hide bits
		$switch = new group("switch");
		//// bodge
		$customerDetails = new group("customerDetails");
		$customerDetails->setVisible(false);
		
		$report = new group("report");
		$report->setBorder(false);
		
		$objectiveGroup = new group("objectiveGroup");
		$objectiveGroup->setBorder(false);
		
		$reportEnd = new group("reportEnd");
		
		$client = new group("client");
		//$client->setBorder(false);
		
		$SAPexistingDistributor = new group("SAPexistingDistributor");
		

		$SAPcustomerOfDistributor = new group("SAPcustomerOfDistributor");
		//$SAPcustomerOfDistributor->setBorder(false);
				
		$newCustomer = new group("newCustomer");
		$customerOfDistributor = new group("customerOfDistributor");
		$status = new group("status");
		
		
		
		$owner = new textbox("owner");
		$owner->setTable("report");
		$owner->setVisible(false);
		$owner->setDataType("text");
		$owner->setRequired(false);
		$owner->setLength(50);
		$owner->setLabel("Report owner");
		$owner->setIsAnNTLogon(true);
		$switch->add($owner);
		
		$reportDate = new textbox("reportDate");
		$reportDate->setTable("report");
		$reportDate->setVisible(false);
		$reportDate->setDataType("text");
		$reportDate->setRequired(false);
		$reportDate->setLength(50);
		$reportDate->setLabel("Report date");
		
		$switch->add($reportDate);
		
		
		
		
		
		$typeOfCustomer = new radio("typeOfCustomer");
		$typeOfCustomer->setTable("report");
		$typeOfCustomer->setDataType("string");
		$typeOfCustomer->setLength(50);
		$typeOfCustomer->setTranslate(true);
		$typeOfCustomer->setRequired(true);
		$typeOfCustomer->setHelpId(3);
		$typeOfCustomer->setXMLSource("./apps/ccr/xml/customerType.xml");
		$typeOfCustomer->setRowTitle("What type of Customer has been contacted?");
		$typeOfCustomer->setValue("existing_client");
		
		
		//
		// Set-up dependencies this control
		//
		
		$customerDependency = new dependency();
		$customerDependency->addRule(new rule('switch', 'typeOfCustomer', 'existing_client'));
		//$customerDependency->setRuleCondition("or");
		$customerDependency->setGroup('client');
		$customerDependency->setShow(true);
		
		$typeOfCustomer->addControllingDependency($customerDependency);
		
		
		$SAPexistingDistributorDependency = new dependency();
		//$customerDependency->addRule(new rule('switch', 'typeOfCustomer', 'existing_client'));
		$SAPexistingDistributorDependency->addRule(new rule('switch', 'typeOfCustomer', 'existing_distributor'));
		//$customerDependency->addRule(new rule('switch', 'typeOfCustomer', 'customer_distributor'));
		//$customerDependency->setRuleCondition("or");
		$SAPexistingDistributorDependency->setGroup('SAPexistingDistributor');
		$SAPexistingDistributorDependency->setShow(true);
		
		$typeOfCustomer->addControllingDependency($SAPexistingDistributorDependency);
		
		
		$SAPcustomerOfDistributorDependency = new dependency();
		$SAPcustomerOfDistributorDependency->addRule(new rule('switch', 'typeOfCustomer', 'customer_distributor'));
		//$customerDependency->setRuleCondition("or");
		$SAPcustomerOfDistributorDependency->setGroup('SAPcustomerOfDistributor');
		$SAPcustomerOfDistributorDependency->setShow(true);
		
		$typeOfCustomer->addControllingDependency($SAPcustomerOfDistributorDependency);
		
		
		$customerOfDistributorDependency = new dependency();
		$customerOfDistributorDependency->addRule(new rule('switch', 'typeOfCustomer', 'customer_distributor'));
		$customerOfDistributorDependency->setGroup('customerOfDistributor');
		$customerOfDistributorDependency->setShow(true);
		
		$typeOfCustomer->addControllingDependency($customerOfDistributorDependency);
		
		
		$newCustomerDependency = new dependency();
		$newCustomerDependency->addRule(new rule('switch', 'typeOfCustomer', 'new_customer'));
		$newCustomerDependency->setGroup('newCustomer');
		$newCustomerDependency->setShow(true);
		
		$typeOfCustomer->addControllingDependency($newCustomerDependency);
		
		
		$switch->add($typeOfCustomer);
		
		
		
		
		
		
		$existingClientSapNumber = new autocomplete("existingClientSapNumber");
		$existingClientSapNumber->setTable("customer");
		$existingClientSapNumber->setDataType("number");
		$existingClientSapNumber->setLength(6);
		$existingClientSapNumber->setUrl('/apps/ccr/ajax/sap?');
		$existingClientSapNumber->setRowTitle("sap_account_number");
		$existingClientSapNumber->setRequired(true);
		$existingClientSapNumber->setLabel("Existing Scapa Customer");
		$existingClientSapNumber->setHelpId(1);
		$existingClientSapNumber->setIgnore(true);
		$existingClientSapNumber->setValidateQuery("SAP", "customer", "id");
		$client->add($existingClientSapNumber);
		
		
		$existingDistributorSapNumber = new autocomplete("existingDistributorSapNumber");
		$existingDistributorSapNumber->setTable("customer");
		$existingDistributorSapNumber->setDataType("number");
		$existingDistributorSapNumber->setLength(6);
		$existingDistributorSapNumber->setUrl('/apps/ccr/ajax/sap?');
		$existingDistributorSapNumber->setRowTitle("sap_account_number");
		$existingDistributorSapNumber->setRequired(true);
		$existingDistributorSapNumber->setLabel("Existing Scapa Distributor");
		$existingDistributorSapNumber->setHelpId(2);
		$existingDistributorSapNumber->setIgnore(true);
		$existingDistributorSapNumber->setValidateQuery("SAP", "customer", "id");
		$SAPexistingDistributor->add($existingDistributorSapNumber);
		
		$customerOfDistributorSapNumber = new autocomplete("customerOfDistributorSapNumber");
		$customerOfDistributorSapNumber->setTable("customer");
		$customerOfDistributorSapNumber->setDataType("number");
		$customerOfDistributorSapNumber->setLength(6);
		$customerOfDistributorSapNumber->setUrl('/apps/ccr/ajax/sap?');
		$customerOfDistributorSapNumber->setRowTitle("sap_account_number");
		$customerOfDistributorSapNumber->setRequired(true);
		$customerOfDistributorSapNumber->setLabel("Existing Scapa Distributor");
		$customerOfDistributorSapNumber->setHelpId(40);
		$customerOfDistributorSapNumber->setIgnore(true);
		$customerOfDistributorSapNumber->setValidateQuery("SAP", "customer", "id");
		$SAPcustomerOfDistributor->add($customerOfDistributorSapNumber);
		
		
		$sapNumber = new textbox("sapNumber");
		$sapNumber->setTable("report");
		$sapNumber->setVisible(false);
		$sapNumber->setDataType("number");
		$sapNumber->setLength(6);
		$report->add($sapNumber);
		
		
		$directCustomerName = new textbox("directCustomerName");
		$directCustomerName->setTable("report");
		$directCustomerName->setVisible(false);
		$directCustomerName->setDataType("text");
		$directCustomerName->setRequired(false);
		$directCustomerName->setLength(255);
		$directCustomerName->setRowTitle("Name");
		$customerDetails->add($directCustomerName);
		
		$directCustomerAddress = new textbox("directCustomerAddress");
		$directCustomerAddress->setTable("report");
		$directCustomerAddress->setVisible(false);
		$directCustomerAddress->setDataType("text");
		$directCustomerAddress->setRequired(false);
		$directCustomerAddress->setLength(255);
		$directCustomerAddress->setRowTitle("Address");
		$customerDetails->add($directCustomerAddress);
		
		$directCustomerCountry = new textbox("directCustomerCountry");
		$directCustomerCountry->setTable("report");
		$directCustomerCountry->setVisible(false);
		$directCustomerCountry->setDataType("text");
		$directCustomerCountry->setRequired(false);
		$directCustomerCountry->setLength(50);
		$directCustomerCountry->setRowTitle("Country");
		$customerDetails->add($directCustomerCountry);
		
		
		$newCustomerName = new textbox("newCustomerName");
		$newCustomerName->setTable("customer");
		$newCustomerName->setDataType("text");
		$newCustomerName->setRequired(true);
		$newCustomerName->setLength(50);
		$newCustomerName->setHelpId(16);
		$newCustomerName->setRowTitle("name");
		$newCustomerName->setLabel("New Prospective Customer");
		$newCustomer->add($newCustomerName);
		
		
		
		$newCustomerAddress = new textarea("newCustomerAddress");
		$newCustomerAddress->setTable("customer");
		$newCustomerAddress->setGroup("newCustomer");
		$newCustomerAddress->setDataType("text");
		$newCustomerAddress->setRequired(false);
		$newCustomerAddress->setLength(100);
		$newCustomerAddress->setHelpId(17);
		$newCustomerAddress->setRowTitle("address");
		$newCustomer->add($newCustomerAddress);
		
		
		
		$newCustomerCountry = new dropdown("newCustomerCountry");
		$newCustomerCountry->setTable("customer");
		$newCustomerCountry->setGroup("newCustomer");
		$newCustomerCountry->setXMLSource("./apps/ccr/xml/countryCodes.xml");
		$newCustomerCountry->setDataType("text");
		$newCustomerCountry->setRequired(false);
		$newCustomerCountry->setHelpId(18);
		$newCustomerCountry->setLength(100);
		$newCustomerCountry->setRowTitle("country");
		$newCustomer->add($newCustomerCountry);
		
		
		
		$newCustomerGroup = new dropdown("newCustomerGroup");
		$newCustomerGroup->setTable("customer");
		//$newCustomerGroup->setGroup("newCustomer");
		$newCustomerGroup->setXMLSource("./apps/ccr/xml/customerGroup.xml");
		$newCustomerGroup->setDataType("text");
		$newCustomerGroup->setRequired(false);
		$newCustomerGroup->setLength(100);
		$newCustomerGroup->setHelpId(19);
		$newCustomerGroup->setRowTitle("group");
		$newCustomer->add($newCustomerGroup);
		
		
		
		$customerOfDistributorName = new textbox("customerOfDistributorName");
		$customerOfDistributorName->setTable("customer");
		//$customerOfDistributorName->setGroup("customerOfDistributor");
		$customerOfDistributorName->setDataType("text");
		$customerOfDistributorName->setRequired(true);
		$customerOfDistributorName->setLength(50);
		$customerOfDistributorName->setHelpId(12);
		$customerOfDistributorName->setRowTitle("name");
		$customerOfDistributorName->setLabel("Customer of an Existing Scapa Distributor");
		$customerOfDistributor->add($customerOfDistributorName);
		
		
		$customerOfDistributorAddress = new textarea("customerOfDistributorAddress");
		$customerOfDistributorAddress->setTable("customer");
		//$customerOfDistributorAddress->setGroup("customerOfDistributor");
		$customerOfDistributorAddress->setDataType("text");
		$customerOfDistributorAddress->setRequired(false);
		$customerOfDistributorAddress->setLength(100);
		$customerOfDistributorAddress->setHelpId(13);
		$customerOfDistributorAddress->setRowTitle("address");
		$customerOfDistributor->add($customerOfDistributorAddress);
		
		
		$customerOfDistributorCountry = new dropdown("customerOfDistributorCountry");
		$customerOfDistributorCountry->setTable("customer");
		//$customerOfDistributorCountry->setGroup("customerOfDistributor");
		$customerOfDistributorCountry->setXMLSource("./apps/ccr/xml/countryCodes.xml");
		$customerOfDistributorCountry->setDataType("text");
		$customerOfDistributorCountry->setRequired(false);
		$customerOfDistributorCountry->setLength(100);
		$customerOfDistributorCountry->setHelpId(14);
		$customerOfDistributorCountry->setRowTitle("country");
		$customerOfDistributor->add($customerOfDistributorCountry);
		
		
		$customerOfDistributorGroup = new dropdown("customerOfDistributorGroup");
		$customerOfDistributorGroup->setTable("customer");
		//$customerOfDistributorGroup->setGroup("customerOfDistributor");
		$customerOfDistributorGroup->setXMLSource("./apps/ccr/xml/customerGroup.xml");
		$customerOfDistributorGroup->setDataType("text");
		$customerOfDistributorGroup->setRequired(false);
		$customerOfDistributorGroup->setLength(100);
		$customerOfDistributorGroup->setHelpId(15);
		$customerOfDistributorGroup->setRowTitle("customer_group");
		$customerOfDistributor->add($customerOfDistributorGroup);
		
		
		$customerOfDistributorJointCall = new radio("customerOfDistributorJointCall");
		$customerOfDistributorJointCall->setTable("customer");
		//$customerOfDistributorJointCall->setGroup("customerOfDistributor");
		$customerOfDistributorJointCall->setDataType("string");
		$customerOfDistributorJointCall->setLength(50);
		$customerOfDistributorJointCall->setRequired(true);
		$customerOfDistributorJointCall->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$customerOfDistributorJointCall->setValue("yes");
		$customerOfDistributorJointCall->setRowTitle("joint_call");
		$customerOfDistributor->add($customerOfDistributorJointCall);
		
		
		$customerOfDistributorExisting = new radio("customerOfDistributorExisting");
		$customerOfDistributorExisting->setTable("customer");
		//$customerOfDistributorExisting->setGroup("customerOfDistributor");
		$customerOfDistributorExisting->setDataType("string");
		$customerOfDistributorExisting->setLength(50);
		$customerOfDistributorExisting->setRequired(true);
		$customerOfDistributorExisting->setArraySource(array(
			array('value' => 'existing', 'display' => 'Existing'),
			array('value' => 'new', 'display' => 'New')
		));
		$customerOfDistributorExisting->setValue("existing");
		$customerOfDistributorExisting->setRowTitle("new_or_existing_customer");
		$customerOfDistributor->add($customerOfDistributorExisting);
		
		
		
	
		
		
		
		
		$contactDate = new textbox("contactDate");
		$contactDate->setTable("report");
		//$contactDate->setGroup("report");
		$contactDate->setDataType("date");
		$contactDate->setRequired(true);
		$contactDate->setLength(10);
		$contactDate->setRowTitle("contact_date");
		$contactDate->setHelpId(4);
		$contactDate->setValue($today);
		$report->add($contactDate);
		
		$contactPerson = new textbox("contactPerson");
		$contactPerson->setTable("report");
		$contactPerson->setDataType("string");
		$contactPerson->setLength(250);
		$contactPerson->setHelpId(5);
		$contactPerson->setRowTitle("contact_person");
		$report->add($contactPerson);
		
		$contactSite = new textbox("contactSite");
		$contactSite->setTable("report");
		$contactSite->setDataType("string");
		$contactSite->setLength(250);
		$contactSite->setHelpId(41);
		$contactSite->setRowTitle("contact_site");
		$report->add($contactSite);
		

		$contactType = new dropdownAlternative("contactType");
		$contactType->setTable("report");
		$contactType->setXMLSource("./apps/ccr/xml/contactType.xml");
		$contactType->setRequired(true);
		$contactType->setTranslate(true);
		$contactType->setDataType("string");
		$contactType->setLength(10);
		$contactType->setHelpId(6);
		$contactType->setRowTitle("contact_type");
		//$contactType->setValue("phone");
		$report->add($contactType);
		
		
		$existingNewBusiness = new radio("existingNewBusiness");
		$existingNewBusiness->setTable("report");
		//$existingNewBusiness->setGroup("report");
		$existingNewBusiness->setDataType("string");
		$existingNewBusiness->setLength(50);
		$existingNewBusiness->setHelpId(7);
		$existingNewBusiness->setRequired(true);
		$existingNewBusiness->setTranslate(true);
		$existingNewBusiness->setArraySource(array(
			array('value' => 'existing', 'display' => 'existing'),
			array('value' => 'new', 'display' => 'new')
		));
		$existingNewBusiness->setValue("existing");
		$existingNewBusiness->setRowTitle("new_or_existing_business");
		$report->add($existingNewBusiness);
		
		
		$customerSurvey = new radio("customerSurvey");
		$customerSurvey->setTable("report");
		$customerSurvey->setDataType("string");
		$customerSurvey->setLength(50);
		$customerSurvey->setHelpId(39);
		$customerSurvey->setRequired(true);
		$customerSurvey->setTranslate(true);
		$customerSurvey->setArraySource(array(
			array('value' => 'yes', 'display' => 'Yes'),
			array('value' => 'no', 'display' => 'No')
		));
		$customerSurvey->setValue("no");
		$customerSurvey->setRowTitle("customer_Survey");
		$report->add($customerSurvey);
		
		
		$customerSurveyDependency = new dependency();
		$customerSurveyDependency->addRule(new rule('report', 'customerSurvey', 'no'));
		$customerSurveyDependency->setGroup('objectiveGroup');
		$customerSurveyDependency->setShow(true);
		
		$customerSurvey->addControllingDependency($customerSurveyDependency);
		
		
		$objective = new textarea("objective");
		$objective->setTable("report");
		//$objective->setGroup("report");
		$objective->setDataType("text");
		$objective->setHelpId(8);
		$objective->setRowTitle("objective_of_contact");
		$objectiveGroup->add($objective);
		
		
		$generalNarative = new textarea("generalNarative");
		$generalNarative->setTable("report");
		//$generalNarative->setGroup("report");
		$generalNarative->setDataType("text");
		$generalNarative->setHelpId(9);
		$generalNarative->setRowTitle("general_narrative");
		$reportEnd->add($generalNarative);
		
		
		$currency = new dropdown("currency");
		$currency->setTable("report");
		$currency->setXMLSource("./xml/currency.xml");
		//$currency->setGroup("report");
		$currency->setDataType("string");
		$currency->setLength(50);
		$currency->setHelpId(10);
		$currency->setRowTitle("invoice_currency");
		$reportEnd->add($currency);
		
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ccr/tmp");
		$attachment->setFinalFileLocation("/apps/ccr/attachments");
		$attachment->setRowTitle("attach_document");
		$attachment->setHelpId(11);
		$attachment->setNextAction("report");
		$reportEnd->add($attachment);
		
		
		
		
		$statusId = new readonly("status");
		//$statusId->setGroup("status");
		$statusId->setDataType("number");
		$statusId->setRequired(false);
		$statusId->setValue(0);
		$statusId->setTable("report");
		$statusId->setRowTitle("report_status");
		$statusId->setVisible(false);
		$status->add($statusId);
		
		
		$completionDate = new readonly("completionDate");
		//$completionDate->setGroup("status");
		$completionDate->setDataType("date");
		$completionDate->setRequired(false);
		$completionDate->setTable("report");
		$completionDate->setRowTitle("completion_date");
		$completionDate->setVisible(false);
		$status->add($completionDate);
		
		
		$finalComments = new textarea("finalComments");
		//$finalComments->setGroup("status");
		$finalComments->setDataType("text");
		$finalComments->setRequired(false);
		$finalComments->setValue("");
		$finalComments->setVisible(false);
		$finalComments->setRowTitle("completion_comments");
		$finalComments->setTable("action");
		$status->add($finalComments);
		
		
		$this->form->add($switch);	
		$this->form->add($client);
		$this->form->add($SAPexistingDistributor);
		$this->form->add($SAPcustomerOfDistributor);
		$this->form->add($customerDetails);
		
		$this->form->add($customerOfDistributor);
		$this->form->add($newCustomer);
		
		$this->form->add($report);
		$this->form->add($objectiveGroup);
		$this->form->add($reportEnd);
		$this->form->add($status);
		
		
	}
	
	
	function getLoadedFromDatabase()
	{
		return $this->loadedFromDatabase;
	}
}

?>