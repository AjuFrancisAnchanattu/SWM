<?php

require 'newLeaver.php';

class newLeaverManipulate extends page
{
	protected $newLeaver;
	
	protected $pageAction;
		
	protected $valid = false;
	
	protected $employeeStages = array(
		'new_leaver'
	);
	
	
	function __construct()
	{
		// call page constructor
		parent::__construct();
	}
	
	
	public function processPost()
	{
		switch($this->getLocation())
		{
			case 'new_leaver':
				
				$this->newLeaver->newLeaverForm->processPost();
				$this->newLeaver->newLeaverForm->validate();
				break;
		}
		
		if ($this->getPageAction() != $this->getLocation())
		{
			switch($this->getPageAction())
			{
				case 'new_leaver':
					
					if (!$this->newLeaver->newLeaverForm->isValid())
					{
						$this->newLeaver->newLeaverForm->validate();
					}
					break;
			}
		}
	}
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			
			if ($this->getPageAction() == "submit")
			{
				if ($this->newLeaver->validate())
				{
					page::addDebug("valid", __FILE__, __LINE__);
					$this->newLeaver->save();
					
					$this->redirect("/apps/employeedb/index?id=" . $this->newLeaver->getId());
				}
				else
				{
					$this->add_output("<error />");
					
					$foundFirst = false;
					
					$this->setPageAction("personal_details");
					
					// find first error.
					
					if (!$this->newLeaver->newLeaverForm->isValid())
					{
						$this->setPageAction("new_leaver");
						$foundFirst = true;
					}
					
				}
			}
		}
	}

	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['location'] =  $this->getFirstAvailableSection();
		}
		
		return $_SESSION['apps'][$GLOBALS['app']]['location'];
	}
	
	public function setLocation($location)
	{
		page::addDebug("set location $location", __FILE__, __LINE__);
		$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
	}
	
	public function getPageAction()
	{
		if (!isset($this->pageAction))
		{
			if (isset($_POST['action']))
			{
				$this->pageAction = $_POST['action'];
			}
			else 
			{
				if (isset($_REQUEST['action']))
				{
					$this->pageAction = $_REQUEST['action'];
				}
				else 
				{
					$this->pageAction = $this->getFirstAvailableSection();
				}
			}
		}
		
		return $this->pageAction;
		
	}
	
	public function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}
	
	
	public function getFirstAvailableSection()
	{
		$section = 'new_leaver';
		
		if (currentuser::getInstance()->hasPermission('admin') ||
			currentuser::getInstance()->hasPermission('employeedb_global') ||
			currentuser::getInstance()->hasPermission('employeedb_personal_details')
		)
		{
			$section = 'new_leaver';
		}

		return $section;
	}
	
	
	
	public function doStuffAndShow($outputType="normal")
	{
		$output = "";
		

		if (in_array($this->getPageAction(), $this->employeeStages))
		{
			$this->setLocation($this->getPageAction());
			
			$dbCountry = $this->newLeaver->newLeaverForm->get("personnelFile")->getValue();
			$userCountry = 'employeedb_uk';
			
			switch($dbCountry)
			{				
				case 'Ashton':
				case 'Dunstable':
				case 'Columbine Street':
				case 'Lymington':
					
					$userCountry = 'employeedb_uk';
					break;
					
				case 'Valence':
				case 'Barcelona':
				case 'Bellegarde':
					
					$userCountry = 'employeedb_french';
					break;
					
				case 'Ghislarengo':
					
					$userCountry = 'employeedb_italian';
					break;
					
				case 'Rorschach':
					
					$userCountry = 'employeedb_swiss';
					break;
					
				case 'Mannheim':
					
					$userCountry = 'employeedb_german';
					break;
					
					
			}
			
			
			$output .= "<" . $this->getPageAction() . ">";
			
			switch ($this->getPageAction())
			{
				case 'new_leaver':
					
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_personal_details') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->newLeaver->newLeaverForm->output();
					}
					else 
					{
						$this->newLeaver->newLeaverForm->showLegend(false);
						$this->newLeaver->newLeaverForm->processDependencies();
						$output .= $this->newLeaver->newLeaverForm->readOnlyOutput();
					}
					break;					
			}
			
			//$output .= $this->newLeaver->form->output(array($this->getPageAction()));
			
			$output .= "</" . $this->getPageAction() . ">";			
		}
		
		return $output;
	}
	
	
	public function buildMenu()
	{		
		$output = "";
		
		$dbCountry = $this->newLeaver->newLeaverForm->get("personnelFile")->getValue();
		$userCountry = 'employeedb_uk';
		
		switch($dbCountry)
		{				
			case 'Ashton':
			case 'Dunstable':
			case 'Columbine Street':
			case 'Lymington':
				
				$userCountry = 'employeedb_uk';
				break;
				
			case 'Valence':
			case 'Barcelona':
			case 'Bellegarde':
				
				$userCountry = 'employeedb_french';
				break;
				
			case 'Ghislarengo':
				
				$userCountry = 'employeedb_italian';
				break;
				
			case 'Rorschach':
				
				$userCountry = 'employeedb_swiss';
				break;
				
			case 'Mannheim':
				
				$userCountry = 'employeedb_german';
				break;
				
				
		}
		
		
		if (!currentuser::getInstance()->hasPermission('admin') && 
			!currentuser::getInstance()->hasPermission('employeedb_global') &&
			!currentuser::getInstance()->hasPermission($userCountry)
		)
		{
			die ("<h3>You do not have access to this persons data</h3><p>Click <a href=\"/apps/employeedb/\">here</a> to return to the system.</p>");
		}
		
	
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_personal_details') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'new_leaver' ? 'true' : 'false') . '" name="new_leaver" valid="' . ($this->newLeaver->newLeaverForm->isValid() ? "true" : "false") . '" />';
		}
		
		return $output;		
	}
}