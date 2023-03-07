<?php

require 'lib/action.php';

class opportunities extends page 
{
	private $header;
	private $form;
	private $annual_volume;
	private $fiscal_volume;
	private $volume_units;
	private $annual_value;
	private $fiscal_value;
	private $budget_value;
	private $success_chance;
	private $project_start_date;
	private $project_owner;
	private $site;
	private $customer_group;
	private $business_unit;
	private $submit;

	
	private $actions = array();
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/menu.xml");

		$this->add_output("<CCR_opportunities>");
		
		
		
		$this->setPageSnapins(
			array(
			'ccr_left' =>
				array(
					'ccrload',
					'opportunityload',
					'ccractions',
					'ccrreports'
				)
			));
		
			$this->add_output("<snapin_left>");	
		
		$this->get_snapins('ccr_left', $this->snapins['ccr_left']);
		
		$this->add_output("</snapin_left>");
		
		$this->defineForm();
		$this->form->loadSessionData();
		$this->setFormValues();
		
		/*
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			
			$this->form->processPost();
						

			if ($this->form->validate())
			{
				// if it validates, do some database magic
				$query = $this->form->generateInsertQuery("opportunity");
				
				
				mysql::getInstance()->selectDatabase("CCR")->Execute("INSERT into opportunity $query");
				
				header("Location: /apps/ccr/");
				exit();
			}
			else 
			{
				$this->add_output("<error />");
			}
		}
		*/
		
		$this->setDebug(true);
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			switch($this->getLocation())
			{
				case 'opportunity':
					
					//$this->attachments->processPost();
					$this->form->processPost();
					break;
					
				case 'action':
					//$this->getAction($this->getActionId())->form->processPost();			BENPD
					break;
					
			}
			
			
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->validate();
			}
		}
		
		
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				$this->save();
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->form->isValid())
				{
					$this->setPageAction("opportunity");
				}
				else 
				{
					for ($i=0; $i < count($this->getActions()); $i++)
					{
						if (!$this->getAction($i)->form->isValid())
						{
							$this->setPageAction("action_".$i);
							break;
						}
					}
				}
				
				
				
			}
		}
		
		
		switch($this->getPageAction())
		{
			case 'opportunity':
			
				$this->setLocation("opportunity");
			
				$this->add_output("<attachmentControl>");
				//$this->attachments->get('attachment')->setNextAction('opportunity');
				//$this->add_output($this->attachments->output());
				$this->add_output("</attachmentControl>");
				
				
				$this->add_output("<actionControl />");
				
				$this->add_output("<ccrReport>");
				$this->add_output($this->form->output());
				$this->add_output("</ccrReport>");
				break;
				
			case 'addaction':
			
				$this->setLocation("action");
				$this->setActionId($this->addAction());
				
				$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
		
			
				$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
				$this->add_output($this->getAction($this->getActionId())->form->output());
				$this->add_output("</ccrAction>");
				break;
				
			case 'action':
			
				//$this->setLocation("material");
				//$this->setMaterialId();
				
				$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
				
				page::addDebug("Show action", __FILE__, __LINE__);
				
				$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
				$this->add_output($this->getAction($this->getActionId())->form->output());
				$this->add_output("</ccrAction>");
				break;
				
		
			default:
			
				// some more custom hardcore actions
				
				if (preg_match("/^action_([0-9]+)$/", $this->getPageAction(), $match))
				{
					$this->setLocation("action");
					$this->setActionId($match[1]);
					
					$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
					
					
					page::addDebug("Show action", __FILE__, __LINE__);
					
					$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
					$this->add_output($this->getAction($this->getActionId())->form->output());
					$this->add_output("</ccrAction>");
				}

				if (preg_match("/^removeaction_([0-9]+)$/", $this->getPageAction(), $match))
				{
					$this->ccr->removeAction($match[1]);
					
					
					$this->setLocation("opportunity");
					
					$this->add_output("<attachmentControl />");
					$this->add_output("<actionControl />");
					
					$this->add_output("<ccrReport>");
					$this->add_output($this->form->output());
					$this->add_output("</ccrReport>");
				}
		}
		
	
		$this->add_output("<opportunitynav valid=\"" . ($this->form->isValid() ? 'true' : 'false') . "\" selected=\"". ($this->getLocation() == 'report' ? 'true' : 'false') . "\">");
				
		for ($i=0; $i < count($this->getActions()); $i++)
		{
			$this->add_output("<actionnav id=\"$i\" valid=\"" . ($this->getAction($i)->form->isValid() ? 'true' : 'false') . "\" selected=\"". ($this->getLocation() == 'action' && $this->getActionId()==$i ? 'true' : 'false') . "\">");
			
			$this->add_output("</actionnav>");
			
		}
		
		$this->add_output("</opportunitynav>");
		
		//$this->add_output("<actionControl/>");
		
		$this->add_output($this->form->output());
		$this->add_output("</CCR_opportunities>");
		$this->output('./apps/ccr/xsl/opportunities.xsl');
	}
	
	public function addAction()
	{
		$id = count($this->actions);
		$this->actions[] = new action($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']);
		//$this->getMaterial($id)->setCcrId($this->id);
		
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
	
	function getActionId()
	{
		if (!isset($this->actionId))
		{
			$this->actionId = isset($_POST['actionId']) ? $_POST['actionId'] : "0";
		}
		
		return $this->actionId;
	}
	
	function setActionId($actionId)
	{
		$this->actionId = $actionId;
	}
	
	
	
	public function loadSessionActions()
	{
		$this->actions = array();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']))
		{
			for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['opportunity']['action']); $i++)
			{
				$this->actions[] = new action($i);
				//$this->getMaterial($i)->setCcrId($this->id);
			}
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
			if (!$this->getAction($i)->validate())
			{
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	
	
	
	
		function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'opportunity';
		}
		
		return $_SESSION['apps'][$GLOBALS['app']]['location'];
	}
	
	function setLocation($location)
	{
		$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
	}
	
	
	
	
	function getPageAction()
	{
		if (!isset($this->pageAction))
		{
			$this->pageAction = isset($_POST['action']) ? $_POST['action'] : "opportunity";
		}
		
		return $this->pageAction;
		
	}
	
	function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}
	

	
	private function defineForm()
	{
		$this->form = new form("opportunity");
		
		//$this->form->storeInSession(true);
		
		$this->material_id = new autocomplete("materialKey");
		$this->material_id->setGroup("material");
		$this->material_id->setDataType("string");
		$this->material_id->setLength(6);
		$this->material_id->setUrl('/apps/ccr/ajax/materialgroup?');
		$this->material_id->setRowTitle("SAP Material Group Key");
		$this->material_id->setRequired(true);
		$this->form->add($this->material_id);
		
		$this->project_owner = new autocomplete("project_owner");
		$this->project_owner->setGroup("project");
		$this->project_owner->setDataType("string");
		$this->project_owner->setLength(50);
		$this->project_owner->setUrl('/ajax/employee?key=project_owner');
		$this->project_owner->setData("membership", "SELECT ntlogon AS data, concat(firstName,' ', lastName) AS name FROM employee");
		$this->project_owner->setRowTitle("Project Owner");
		$this->project_owner->setRequired(true);
		$this->project_owner->setTable("opportunity");
		$this->form->add($this->project_owner);
		
		$this->project_start_date = new textbox("project_start_date");
		$this->project_start_date->setGroup("project");
		$this->project_start_date->setRowTitle("Project Start Date");
		$this->project_start_date->setDataType("date");
		$this->project_start_date->setRequired(true);
		$this->project_start_date->setTable("opportunity");
		$today = date("d/m/Y",time());
		$this->project_start_date->setValue($today);
		$this->form->add($this->project_start_date);
		
		$this->site = new autocomplete("site");
		$this->site->setGroup("project");
		$this->site->setDataType("string");
		$this->site->setLength(50);
		$this->site->setUrl('/ajax/site?key=site');
		$this->site->setRowTitle("Site");
		$this->site->setRequired(true);
		$this->site->setTable("opportunity");
		$this->form->add($this->site);
		
		$this->business_unit = new dropdown("business_unit");
		$this->business_unit->setGroup("project");
		$this->business_unit->setRowTitle("Business Unit");
		$this->business_unit->setXMLSource("./apps/ccr/xml/opportunityBusinessUnit.xml");
		$this->business_unit->setDataType("string");
		$this->business_unit->setLength(50);
		$this->business_unit->setRequired(true);
		$this->business_unit->setTable("opportunity");
		$this->form->add($this->business_unit);
		
		
		
		
		$this->sapNumber = new autocomplete("sapNumber");
		$this->sapNumber->setGroup("customer");
		$this->sapNumber->setDataType("number");
		$this->sapNumber->setLength(6);
		$this->sapNumber->setUrl('/apps/ccr/ajax/sap?');
		$this->sapNumber->setRowTitle("SAP Account Number");
		$this->sapNumber->setRequired(true);
		$this->sapNumber->setTable("opportunity");
		$this->form->add($this->sapNumber);
		
		$this->customer_group = new dropdown("customer_group");
		$this->customer_group->setGroup("customer");
		$this->customer_group->setRowTitle("Customer Group");
		$this->customer_group->setXMLSource("./apps/ccr/xml/opportunityCustomerGroup.xml");
		$this->customer_group->setDataType("string");
		$this->customer_group->setLength(50);
		$this->customer_group->setRequired(true);
		$this->customer_group->setTable("opportunity");
		$this->form->add($this->customer_group);
		
		$this->customer_country = new textbox("customer_country");
		$this->customer_country->setGroup("customer");
		$this->customer_country->setRowTitle("Customer Country");
		$this->customer_country->setDataType("string");
		$this->customer_country->setLength(50);
		$this->customer_country->setRequired(true);
		$this->customer_country->setTable("opportunity");
		$this->form->add($this->customer_country);
		
		
		
		$this->annual_volume = new measurement("annual_volume");
		$this->annual_volume->setRowTitle("Total Annual Volume in a Full Year");
		$this->annual_volume->setDataType("string");
		$this->annual_volume->setLength(50);
		$this->annual_volume->setXMLSource("./apps/ccr/xml/units.xml");
		$this->annual_volume->setTable("opportunity");
		$this->form->add($this->annual_volume);
		
		$this->fiscal_volume = new measurement("fiscal_volume");
		$this->fiscal_volume->setRowTitle("Volume Current Fiscal Year");
		$this->fiscal_volume->setDataType("string");
		$this->fiscal_volume->setLength(50);
		$this->fiscal_volume->setXMLSource("./apps/ccr/xml/units.xml");
		$this->fiscal_volume->setTable("opportunity");
		$this->form->add($this->fiscal_volume);
		
		$this->annual_value = new measurement("annual_value");
		$this->annual_value->setRowTitle("Total Annual Value in a Full Year");
		$this->annual_value->setDataType("string");
		$this->annual_value->setLength(50);
		$this->annual_value->setXMLSource("./xml/currency.xml");
		$this->annual_value->setTable("opportunity");
		$this->annual_value->setMeasurement("GBP");
		$this->form->add($this->annual_value);
		
		$this->fiscal_value = new measurement("fiscal_value");
		$this->fiscal_value->setRowTitle("Value in Current Fiscal Year");
		$this->fiscal_value->setDataType("string");
		$this->fiscal_value->setLength(50);
		$this->fiscal_value->setXMLSource("./xml/currency.xml");
		$this->fiscal_value->setTable("opportunity");
		$this->fiscal_value->setMeasurement("GBP");
		$this->form->add($this->fiscal_value);
		
		$this->budget_value = new measurement("budget_value");
		$this->budget_value->setRowTitle("Value Included in Budget Current Fiscal Year");
		$this->budget_value->setDataType("string");
		$this->budget_value->setLength(50);
		$this->budget_value->setXMLSource("./xml/currency.xml");
		$this->budget_value->setTable("opportunity");
		$this->budget_value->setMeasurement("GBP");
		$this->form->add($this->budget_value);
		
		$this->success_chance = new textbox("success_chance");
		$this->success_chance->setRowTitle("Chance of Success (%)");
		$this->success_chance->setDataType("number");
		$this->success_chance->setLength(3);
		$this->success_chance->setRequired(true);
		$this->success_chance->setTable("opportunity");
		$this->form->add($this->success_chance);
		
		

		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$submit->setValue("Submit Opportunity");
		$this->form->add($submit);
	}
	
	function setFormValues()
	{
		if (isset($_REQUEST['mode']))
		{
			if ($_REQUEST['mode'] == "edit")
			{
				$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM opportunity WHERE (id ='" . $_REQUEST['id'] . "')");
				if ($fields = mysql_fetch_array($dataset))
				{			
					$this->annual_volume->setValue($fields['annual_volume']);
					$this->fiscal_volume->setValue($fields['fiscal_volume']);
					$this->annual_value->setValue($fields['annual_value']);
					$this->fiscal_value->setValue($fields['fiscal_value']);
					$this->budget_value->setValue($fields['budget_value']);
					$this->success_chance->setValue($fields['success_chance']);
					$this->project_start_date->setValue($this->form->transformDateForPHP($fields['project_start_date']));
					$this->site->setValue($fields['site']);
					$this->project_owner->setValue($fields['project_owner']);
					$this->customer_group->setValue($fields['customer_group']);
					$this->business_unit->setValue($fields['business_unit']);
				}
			}
		}
	}
	
}

?>