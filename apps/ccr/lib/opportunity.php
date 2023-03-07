<?php

class opportunity
{
	private $actions = array();
	
	public $form;
	

	function __construct(&$sessionLocation, $id=-1)
	{
		$this->defineForm();
		
		$this->form->setStoreInSession(true, $sessionLocation);
		
		if ($id != -1)
		{
			//echo "<p>oog $id</p>";
			$this->form->setMultipleFormSessionId($id);
		}
		else 
		{
			//echo "<p>virgin one</p>";
		}
		
		$this->form->setMultipleFormSession(true);
		$this->form->loadSessionData();
	}
	
		
	
	private function defineForm()
	{
		$this->form = new form("opportunity");
		
		//$this->form->storeInSession(true);
		
		$annual_volume = new textbox("annual_volume");
		$annual_volume->setRowTitle("Total Annual Volume in a Full Year");
		$annual_volume->setDataType("number");
		$annual_volume->setLength(50);
		$annual_volume->setRequired(true);
		$annual_volume->setTable("opportunity");
		$this->form->add($annual_volume);
		
		$fiscal_volume = new textbox("fiscal_volume");
		$fiscal_volume->setRowTitle("Volume Current Fiscal Year");
		$fiscal_volume->setDataType("string");
		$fiscal_volume->setLength(50);
		$fiscal_volume->setRequired(true);
		$fiscal_volume->setTable("opportunity");
		$this->form->add($fiscal_volume);
		
		$volume_units = new textbox("volume_units");
		$volume_units->setRowTitle("Volume units");
		$volume_units->setDataType("string");
		$volume_units->setLength(10);
		$volume_units->setRequired(true);
		$volume_units->setTable("opportunity");
		$this->form->add($volume_units);
		
		$annual_value = new textbox("annual_value");
		$annual_value->setRowTitle("Total Annual Value in a Full Year");
		$annual_value->setDataType("number");
		$annual_value->setLength(50);
		$annual_value->setRequired(true);
		$annual_value->setTable("opportunity");
		$this->form->add($annual_value);
		
		$fiscal_value = new textbox("fiscal_value");
		$fiscal_value->setRowTitle("Value in Current Fiscal Year");
		$fiscal_value->setDataType("number");
		$fiscal_value->setLength(50);
		$fiscal_value->setRequired(true);
		$fiscal_value->setTable("opportunity");
		$this->form->add($fiscal_value);
		
		$budget_value = new textbox("budget_value");
		$budget_value->setRowTitle("Value Included in Budget Current Fiscal Year");
		$budget_value->setDataType("number");
		$budget_value->setLength(50);
		$budget_value->setRequired(true);
		$budget_value->setTable("opportunity");
		$this->form->add($budget_value);
		
		$success_chance = new textbox("success_chance");
		$success_chance->setRowTitle("Chance of Success (%)");
		$success_chance->setDataType("number");
		$success_chance->setLength(3);
		$success_chance->setRequired(true);
		$success_chance->setTable("opportunity");
		$this->form->add($success_chance);
		
		$project_start_date = new textbox("project_start_date");
		$project_start_date->setRowTitle("Project Start Date");
		$project_start_date->setDataType("date");
		$project_start_date->setRequired(true);
		$project_start_date->setTable("opportunity");
		$this->form->add($project_start_date);
		
		$project_owner = new dropdown("project_owner");
		$project_owner->setRowTitle("Project Owner / Leader");
		$project_owner->setDataType("string");
		$project_owner->setSQLSource("membership","SELECT (concat(firstName,' ',lastName)) as name, ntlogon AS data FROM employee ORDER BY name ASC");
		$project_owner->setLength(50);
		$project_owner->setRequired(true);
		$project_owner->setTable("opportunity");
		$this->form->add($project_owner);
		
		$site = new dropdown("site");
		$site->setRowTitle("Site");
		$site->setDataType("string");
		$site->setSQLSource("membership","SELECT name, name AS data FROM sites ORDER BY name ASC");
		$site->setLength(50);
		$site->setRequired(true);
		$site->setTable("opportunity");
		$this->form->add($site);
		
		$customer_group = new textbox("customer_group");
		$customer_group->setRowTitle("Customer Group");
		$customer_group->setDataType("string");
		$customer_group->setLength(50);
		$customer_group->setRequired(true);
		$customer_group->setTable("opportunity");
		$this->form->add($customer_group);
		
		$business_unit = new textbox("business_unit");
		$business_unit->setRowTitle("Business Unit");
		$business_unit->setDataType("string");
		$business_unit->setLength(50);
		$business_unit->setRequired(true);
		$business_unit->setTable("opportunity");
		$this->form->add($business_unit);
	}
}

?>