<?php

require 'newEntry.php';

class newEntryManipulate extends page
{
	protected $newEntry;
	
	protected $pageAction;
		
	protected $valid = false;
	
	protected $employeeStages = array(
		'new_entry'
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
			case 'new_entry':
				
				$this->newEntry->newEntryForm->processPost();
				$this->newEntry->newEntryForm->validate();
				break;
		}
		
		if ($this->getPageAction() != $this->getLocation())
		{
			switch($this->getPageAction())
			{
				case 'new_entry':
					
					if (!$this->newEntry->newEntryForm->isValid())
					{
						$this->newEntry->newEntryForm->validate();
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
				if ($this->newEntry->validate())
				{
					page::addDebug("valid", __FILE__, __LINE__);
					$this->newEntry->save();
					
					$this->redirect("/apps/employeedb/index?id=" . $this->newEntry->getId());
				}
				else
				{
					$this->add_output("<error />");
					
					$foundFirst = false;
					
					$this->setPageAction("personal_details");
					
					// find first error.
					
					if (!$this->newEntry->newEntryForm->isValid())
					{
						$this->setPageAction("new_entry");
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
		$section = 'new_entry';
		
		if (currentuser::getInstance()->hasPermission('admin') ||
			currentuser::getInstance()->hasPermission('employeedb_global') ||
			currentuser::getInstance()->hasPermission('employeedb_personal_details')
		)
		{
			$section = 'new_entry';
		}

		return $section;
	}
	
	
	
	public function doStuffAndShow($outputType="normal")
	{
		$output = "";
		

		if (in_array($this->getPageAction(), $this->employeeStages))
		{
			$this->setLocation($this->getPageAction());
			
			$dbCountry = $this->newEntry->newEntryForm->get("personnelFile")->getValue();
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
				case 'new_entry':
					
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_personal_details') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->newEntry->newEntryForm->output();
					}
					else 
					{
						$this->newEntry->newEntryForm->showLegend(false);
						$this->newEntry->newEntryForm->processDependencies();
						$output .= $this->newEntry->newEntryForm->readOnlyOutput();
					}
					break;					
			}
			
			//$output .= $this->newEntry->form->output(array($this->getPageAction()));
			
			$output .= "</" . $this->getPageAction() . ">";			
		}
		
		return $output;
	}
	
	
	public function buildMenu()
	{		
		$output = "";
		
		$dbCountry = $this->newEntry->newEntryForm->get("personnelFile")->getValue();
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
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'new_entry' ? 'true' : 'false') . '" name="new_entry" valid="' . ($this->newEntry->newEntryForm->isValid() ? "true" : "false") . '" />';
		}
		
		return $output;		
	}
}