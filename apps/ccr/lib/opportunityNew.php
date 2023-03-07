<?php

require 'action.php';


class opportunityNew
{
	private $actions = array();
	
	public $id;
	public $owner;
	public $form;
	
	public $attachments;
	
	private $loadedFromDatabase = false;
	
	
	
	function __construct()
	{
		$this->defineForm();
		$this->defineAttachmentForm();
		
		//$_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'] = array();
		
		$this->form->loadSessionData();
		//$this->attachments->loadSessionData();
	
		$this->loadSessionActions();
		
	}
	
	public function load($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunity WHERE id = $id");
				
		if (mysql_num_rows($dataset) == 1)
		{
			$this->loadedFromDatabase = true;
			
			$fields = mysql_fetch_array($dataset);
			
			$this->id = $fields['id'];
			$this->owner = $fields['owner'];
			
			
			foreach ($fields as $key => $value)
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			//$this->form->get('contactDate')->setValue(page::transformDateForPHP($this->form->get('contactDate')->getValue()));
		
				
			$actionDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM action WHERE opportunityID = $id");
		
			while($actionFields = mysql_fetch_array($actionDataset))
			{
				$id = count($this->actions);
				$this->actions[] = new action($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']);
				$this->actions[$id]->load($actionFields['id']);
				//$this->actions[$id]->setOpportunityId($this->id);
			}
				
			$this->form->putValuesInSession();
			
			return true;
		}
		else
		{
			return 0;
		}
	}
	
	public function save()
	{
		$this->setCalculatedFields();
		if ($this->loadedFromDatabase)
		{
			// update
			mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE opportunity SET " . $this->form->generateUpdateQuery("opportunity") . " WHERE id='" . $this->id . "'");
			
			// delete existing records
			//mysql::getInstance()->selectDatabase("CCR")->Execute("DELETE FROM customer WHERE ccrId=" . $this->id);
			
			// save new data
			$this->saveCustomerBit($this->id);
		}
		else 
		{
			// insert
			mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT INTO opportunity " . $this->form->generateInsertQuery("opportunity"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT id FROM opportunity ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			
			$this->id = $fields['id'];
			
			
		}
		
		for ($i=0; $i < count($this->actions); $i++)
		{
			$this->actions[$i]->setOpportunityId($this->id);
			$this->actions[$i]->save($this->loadedFromDatabase);
		}
	}
	
	
	public function addAction()
	{
		$id = count($this->actions);
		$this->actions[] = new action($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']);
		//$this->getAction($id)->setOpportunityId($this->id);
		
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
				
				$_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'][$i] = $_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'][$i+1];
			}
			else 
			{
				unset($this->actions[$i]);
				unset ($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'][$i]);
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
	
	public function loadSessionActions()
	{
		$this->actions = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']); $i++)
			{
				$this->actions[] = new action($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'], $i);
				//$this->getAction($i)->setOpportunityId($this->id);
			}
		}
		else 
		{
			$_SESSION['apps'][$GLOBALS['app']]['opportunity']['action'] = array();
		}
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
			if (!$this->getAction($i)->form->validate())
			{
				$valid = false;
			}
		}
				
		return $valid;
	}
	
	
	public function defineAttachmentForm()
	{
		$this->attachments = new form("attachments");
		$this->attachments->setStoreInSession(false);
		
		$attachmentGroup = new group("attachments");
		
		$attachment = new attachment("attachment");
		$attachment->setTempFileLocation("/apps/ccr/tmp");
		$attachmentGroup->add($attachment);
		
		$this->attachments->add($attachmentGroup);
	}
	
	function setCalculatedFields()
	{
		if (
			($this->form->get('fiscal_value')->getValue() != 0) ||
			($this->form->get('annual_value')->getValue() >= 300) ||
			($this->form->get('success_chance')->getValue() >= 75)
		   )
		{
			$priority = 'A';
		}
		elseif (
				(($this->form->get('annual_value')->getValue() >= 100) && ($this->form->get('annual_value')->getValue() < 300)) ||
				(($this->form->get('success_chance')->getValue() >= 50) && ($this->form->get('success_chance')->getValue() < 75))
			   )
		{
			$priority = 'B';	
		}
		else 
		{
			$priority = 'C';	
		}
		$this->form->get('priority')->setValue($priority);
	}
	
	public function defineForm()
	{
		$this->form = new form("opportunity");
		$this->form->setStoreInSession(true);
		$this->form->showLegend(true);
		
		$actionGroup = new group("action");
		$materialGroup = new group("material");
		$projectGroup = new group("project");
		$customerGroup = new group("customer");
		$opportunityGroup = new group("opportunity");
		
		
		$this->priority = new invisibletext("priority");			
		//$this->priority->setGroup("action");
		$this->priority->setDataType("string");
		$this->priority->setRequired(false);
		$this->priority->setValue("");
		$this->priority->setTable("action");
		$this->priority->setVisible(false);
		$this->priority->setTable("opportunity");
		//$this->form->add($this->priority);
		$actionGroup->add($this->priority);
		
		
		
		
		$this->materialKey = new autocomplete("materialKey");
		//$this->materialKey->setGroup("material");
		$this->materialKey->setDataType("string");
		$this->materialKey->setLength(6);
		$this->materialKey->setUrl('/apps/ccr/ajax/materialgroup?');
		$this->materialKey->setRowTitle("SAP Material Group Key");
		$this->materialKey->setRequired(true);
		$this->materialKey->setTable("opportunity");
		//$this->form->add($this->materialKey);
		$materialGroup->add($this->materialKey);
		
		$this->project_owner = new autocomplete("project_owner");
		//$this->project_owner->setGroup("project");
		$this->project_owner->setDataType("string");
		$this->project_owner->setLength(50);
		$this->project_owner->setUrl('/ajax/employee?key=project_owner');
		$this->project_owner->setData("membership", "SELECT ntlogon AS data, concat(firstName,' ', lastName) AS name FROM employee");
		$this->project_owner->setRowTitle("Project Owner");
		$this->project_owner->setRequired(true);
		$this->project_owner->setTable("opportunity");
		//$this->form->add($this->project_owner);
		$projectGroup->add($this->project_owner);
		
		$this->project_start_date = new textbox("project_start_date");
		//$this->project_start_date->setGroup("project");
		$this->project_start_date->setRowTitle("Project Start Date");
		$this->project_start_date->setDataType("date");
		$this->project_start_date->setRequired(true);
		$this->project_start_date->setTable("opportunity");
		$today = date("d/m/Y",time());
		$this->project_start_date->setValue($today);
		//$this->form->add($this->project_start_date);
		$projectGroup->add($this->project_start_date);
		
		$this->site = new autocomplete("site");
		//$this->site->setGroup("project");
		$this->site->setDataType("string");
		$this->site->setLength(50);
		$this->site->setUrl('/ajax/site?key=site');
		$this->site->setRowTitle("Site");
		$this->site->setRequired(true);
		$this->site->setTable("opportunity");
		//$this->form->add($this->site);
		$projectGroup->add($this->site);
		
		$this->business_unit = new dropdown("business_unit");
		//$this->business_unit->setGroup("project");
		$this->business_unit->setRowTitle("Business Unit");
		$this->business_unit->setXMLSource("./apps/ccr/xml/opportunityBusinessUnit.xml");
		$this->business_unit->setDataType("string");
		$this->business_unit->setLength(50);
		$this->business_unit->setRequired(true);
		$this->business_unit->setTable("opportunity");
		//$this->form->add($this->business_unit);
		$projectGroup->add($this->business_unit);
		
		
		
		
		$this->sapNumber = new autocomplete("sapNumber");
		//$this->sapNumber->setGroup("customer");
		$this->sapNumber->setDataType("number");
		$this->sapNumber->setLength(6);
		$this->sapNumber->setUrl('/apps/ccr/ajax/sap?');
		$this->sapNumber->setRowTitle("SAP Account Number");
		$this->sapNumber->setRequired(true);
		$this->sapNumber->setTable("opportunity");
		//$this->form->add($this->sapNumber);
		$customerGroup->add($this->sapNumber);
		
		$this->customer_group = new dropdown("customer_group");
		//$this->customer_group->setGroup("customer");
		$this->customer_group->setRowTitle("Customer Group");
		$this->customer_group->setXMLSource("./apps/ccr/xml/opportunityCustomerGroup.xml");
		$this->customer_group->setDataType("string");
		$this->customer_group->setLength(50);
		$this->customer_group->setRequired(true);
		$this->customer_group->setTable("opportunity");
		//$this->form->add($this->customer_group);
		$customerGroup->add($this->customer_group);
		
		$this->customer_country = new textbox("customer_country");
		//$this->customer_country->setGroup("customer");
		$this->customer_country->setRowTitle("Customer Country");
		$this->customer_country->setDataType("string");
		$this->customer_country->setLength(50);
		$this->customer_country->setRequired(true);
		$this->customer_country->setTable("opportunity");
		//$this->form->add($this->customer_country);
		$customerGroup->add($this->customer_country);
		
		
		
		$this->annual_volume = new measurement("annual_volume");
		$this->annual_volume->setRowTitle("Total Annual Volume in a Full Year");
		$this->annual_volume->setDataType("string");
		$this->annual_volume->setLength(50);
		$this->annual_volume->setValue(0);
		$this->annual_volume->setXMLSource("./apps/ccr/xml/units.xml");
		$this->annual_volume->setTable("opportunity");
		//$this->form->add($this->annual_volume);
		$opportunityGroup->add($this->annual_volume);
		
		$this->fiscal_volume = new measurement("fiscal_volume");
		$this->fiscal_volume->setRowTitle("Volume Current Fiscal Year");
		$this->fiscal_volume->setDataType("string");
		$this->fiscal_volume->setLength(50);
		$this->fiscal_volume->setValue(0);
		$this->fiscal_volume->setXMLSource("./apps/ccr/xml/units.xml");
		$this->fiscal_volume->setTable("opportunity");
		//$this->form->add($this->fiscal_volume);
		$opportunityGroup->add($this->fiscal_volume);
		
		$this->annual_value = new measurement("annual_value");
		$this->annual_value->setRowTitle("Total Annual Value in a Full Year");
		$this->annual_value->setDataType("string");
		$this->annual_value->setLength(50);
		$this->annual_value->setValue(0);
		$this->annual_value->setXMLSource("./xml/currency.xml");
		$this->annual_value->setTable("opportunity");
		$this->annual_value->setMeasurement("GBP");
		//$this->form->add($this->annual_value);
		$opportunityGroup->add($this->annual_value);
		
		$this->fiscal_value = new measurement("fiscal_value");
		$this->fiscal_value->setRowTitle("Value in Current Fiscal Year");
		$this->fiscal_value->setDataType("string");
		$this->fiscal_value->setLength(50);
		$this->fiscal_value->setValue(0);
		$this->fiscal_value->setXMLSource("./xml/currency.xml");
		$this->fiscal_value->setTable("opportunity");
		$this->fiscal_value->setMeasurement("GBP");
		//$this->form->add($this->fiscal_value);
		$opportunityGroup->add($this->fiscal_value);
		
		$this->budget_value = new measurement("budget_value");
		$this->budget_value->setRowTitle("Value Included in Budget Current Fiscal Year");
		$this->budget_value->setDataType("string");
		$this->budget_value->setLength(50);
		$this->budget_value->setValue(0);
		$this->budget_value->setXMLSource("./xml/currency.xml");
		$this->budget_value->setTable("opportunity");
		$this->budget_value->setMeasurement("GBP");
		//$this->form->add($this->budget_value);
		$opportunityGroup->add($this->budget_value);
		
		$this->success_chance = new textbox("success_chance");
		$this->success_chance->setRowTitle("Chance of Success (%)");
		$this->success_chance->setDataType("number");
		$this->success_chance->setLength(3);
		$this->success_chance->setRequired(true);
		$this->success_chance->setTable("opportunity");
		//$this->form->add($this->success_chance);
		$opportunityGroup->add($this->success_chance);
		
		
		$this->form->add($actionGroup);	
		$this->form->add($materialGroup);
		$this->form->add($customerGroup);
		$this->form->add($projectGroup);
		$this->form->add($opportunityGroup);
		
	}
	
}

?>