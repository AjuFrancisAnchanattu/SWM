<?php

require 'lib/manipulate.php';

class delegate extends manipulate
{
	private $actionDatabaseID;
	private $form;

	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->defineForm();
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		
		$this->add_output('<ccrDelegate id="' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '') . '">');
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			//session::clear();
			//$this->setPageAction("report");
		}
		
		
		$this->ccr = new ccr();
			
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST['id']))
		{
			$this->ccr->load($_REQUEST['id']);
		}
		
		/*if ($this->ccr->getOwner() != currentuser::getInstance()->getNTLogon())
		{
			page::redirect("/apps/ccr/");
		}*/
		
			
		/*$this->ccr = new ccr();
			
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST['id']))
		{
			$this->ccr->load($_REQUEST['id']);
			
			$this->setPageAction($_REQUEST['pageAction']);
			$this->actionDatabaseID = $_REQUEST['databaseID'];
		}*/
		
		
		
		
		/*if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->processPost();
			
			
			//if ($this->validate())
			//{
				$this->ccr->load($_REQUEST['id']);
				$this->setPageAction($_REQUEST['pageAction']);
				$this->actionDatabaseID = $_REQUEST['databaseID'];
				
				$this->ccr->getMaterial( $_REQUEST['material'])->getAction($_REQUEST['action'])->form->get("personResponsible")->setValue($this->form->get("personResponsible")->getDisplayValue());
				
				$this->ccr->getMaterial( $_REQUEST['material'])->getAction($_REQUEST['action'])->delegate();
				
			//}
			//else 
			//{
			//	echo "not valid";
			//}
		}*/
		
		/*
		if ($this->ccr->owner != currentuser::getInstance()->getNTLogon())
		{
			page::redirect("/apps/ccr/");
		}
		*/
		
		$this->processPost();
			
		//$this->validate();
			
		//$this->add_output($this->doStuffAndShow());
		
		$this->add_output($this->form->output());
		
		$this->add_output($this->buildMenu());
			
		
		// show form	
		$this->add_output("</ccrDelegate>");
		
		$this->output('./apps/ccr/xsl/delegate.xsl');

	}
	
	

	
	
	protected function defineForm()
	{		
		$this->form = new form("action");
		
		$action = new group("action");
		
	
		$parentId = new invisibletext("id");			//id of the ccr or opportunity
		$parentId->setGroup("action");
		$parentId->setDataType("number");
		$parentId->setRequired(false);
		$parentId->setValue(0);
		$parentId->setTable("action");
		$parentId->setVisible(false);
		$action->add($parentId);
				
		
		$personResponsible = new autocomplete("personResponsible");
		$personResponsible->setGroup("action");
		$personResponsible->setDataType("string");
		$personResponsible->setLength(50);
		$personResponsible->setUrl('/ajax/employee?key=personResponsible');
		$personResponsible->setRowTitle("Delegate To");
		$personResponsible->setRequired(true);
		$personResponsible->setTable("action");
		$personResponsible->setIsAnNTLogon(true);
		$action->add($personResponsible);
		
		
		
		$message = new textarea("message");
		$message->setGroup("action");
		$message->setDataType("text");
		$message->setRequired(false);
		$message->setRowTitle("Message");
		$message->setTable("action");
		$action->add($message);
		
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$submit->setValue("Confirm Delegation");
		$action->add($submit);
		

		$this->form->add($action);

	}
}

?>