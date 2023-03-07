<?php

require 'employee.php';

class manipulate extends page
{
	protected $employee;
	
	protected $pageAction;
		
	protected $valid = false;
	
	protected $employeeStages = array(
		'personal_details',
		'job_role',
		'employment_history',
		'it_information',
		'asset_data',
		'training',
		'ppe_and_hse'
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
			case 'personal_details':
				
				$this->employee->personalDetailsForm->processPost();
				$this->employee->personalDetailsForm->validate();
				break;
				
			case 'job_role':
					
				$this->employee->jobRoleForm->processPost();
				$this->employee->jobRoleForm->validate();
				break;
				
			case 'employment_history':
			
				$this->employee->employmentHistoryForm->processPost();
				$this->employee->employmentHistoryForm->validate();
				break;
				
			case 'it_information':
			
				$this->employee->ITInformationForm->processPost();
				$this->employee->ITInformationForm->validate();
				break;
				
			case 'asset_data':
			
				$this->employee->assetDataForm->processPost();
				$this->employee->assetDataForm->validate();
				break;
				
			case 'training':
			
				$this->employee->trainingForm->processPost();
				$this->employee->trainingForm->validate();
				break;
				
			case 'ppe_and_hse':
			
				$this->employee->PPEandHSEtrainingForm->processPost();
				$this->employee->PPEandHSEtrainingForm->validate();
				break;
		}
		
		if ($this->getPageAction() != $this->getLocation())
		{
			switch($this->getPageAction())
			{
				case 'personal_details':
					
					if (!$this->employee->personalDetailsForm->isValid())
					{
						$this->employee->personalDetailsForm->validate();
					}
					break;
					
				case 'job_role':
						
					if (!$this->employee->jobRoleForm->isValid())
					{
						$this->employee->jobRoleForm->validate();
					}
					break;
					
				case 'employment_history':
				
					if (!$this->employee->employmentHistoryForm->isValid())
					{
						$this->employee->employmentHistoryForm->validate();
					}
					break;
					
				case 'it_information':
				
					if (!$this->employee->ITInformationForm->isValid())
					{
						$this->employee->ITInformationForm->validate();
					}
					break;
					
				case 'asset_data':
				
					if (!$this->employee->assetDataForm->isValid())
					{
						$this->employee->assetDataForm->validate();
					}
					break;
					
				case 'training':
				
					if (!$this->employee->trainingForm->isValid())
					{
						$this->employee->trainingForm->validate();
					}
					break;
					
				case 'ppe_and_hse':
				
					if (!$this->employee->PPEandHSEtrainingForm->isValid())
					{
						$this->employee->PPEandHSEtrainingForm->validate();
					}
					break;
			}
		}
		
		if ($this->getPageAction() == "test")
		{
			if ($this->employee->employmentHistoryForm->getGroup("jobHistoryGroup"))
			{
				$this->employee->employmentHistoryForm->getGroup("jobHistoryGroup")->addRow();
				$this->setPageAction('employment_history');
			}
			else 
			{
				var_dump($this->employee->employmentHistoryForm->getGroupNames());
				die("group not found");
			}
		}
	}
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			
			if ($this->getPageAction() == "submit")
			{
				if ($this->employee->validate())
				{
					page::addDebug("valid", __FILE__, __LINE__);
					$this->employee->save();
					
					$this->redirect("/apps/employeedb/index?id=" . $this->employee->getId());
				}
				else
				{
					$this->add_output("<error />");
					
					$foundFirst = false;
					
					$this->setPageAction("personal_details");
					
					// find first error.
					
					if (!$this->employee->personalDetailsForm->isValid())
					{
						$this->setPageAction("personal_details");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->jobRoleForm->isValid())
					{
						$this->setPageAction("job_role");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->employmentHistoryForm->isValid())
					{
						$this->setPageAction("employment_history");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->ITInformationForm->isValid())
					{
						$this->setPageAction("it_information");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->assetDataForm->isValid())
					{
						$this->setPageAction("asset_data");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->trainingForm->isValid())
					{
						$this->setPageAction("training");
						$foundFirst = true;
					}
					
					if (!$foundFirst && !$this->employee->PPEandHSEtrainingForm->isValid())
					{
						$this->setPageAction("ppe_and_hse");
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
		$section = 'personal_details';
		
		
		if (currentuser::getInstance()->hasPermission('employeedb_ppe_and_hse'))
		{
			$section = 'ppe_and_hse';
		}
		
		if (currentuser::getInstance()->hasPermission('employeedb_training'))
		{
			$section = 'training';
		}
		
		if (currentuser::getInstance()->hasPermission('employeedb_asset_data'))
		{
			$section = 'asset_data';
		}
		
		if (currentuser::getInstance()->hasPermission('employeedb_it_information'))
		{
			$section = 'it_information';
		}
		
		if (currentuser::getInstance()->hasPermission('employeedb_employment_history'))
		{
			$section = 'employment_history';
		}
		
		
		if (currentuser::getInstance()->hasPermission('employeedb_job_role'))
		{
			$section = 'job_role';
		}
		
		if (currentuser::getInstance()->hasPermission('admin') ||
			currentuser::getInstance()->hasPermission('employeedb_global') ||
			currentuser::getInstance()->hasPermission('employeedb_personal_details')
		)
		{
			$section = 'personal_details';
		}

		return $section;
	}
	
	
	
	public function doStuffAndShow($outputType="normal")
	{
		$output = "";
		

		if (in_array($this->getPageAction(), $this->employeeStages))
		{
			$this->setLocation($this->getPageAction());
			
			$dbCountry = $this->employee->personalDetailsForm->get("personnelFile")->getValue();
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
				case 'personal_details':
					
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_personal_details') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->personalDetailsForm->output();
					}
					else 
					{
						$this->employee->personalDetailsForm->showLegend(false);
						$this->employee->personalDetailsForm->processDependencies();
						$output .= $this->employee->personalDetailsForm->readOnlyOutput();
					}
					break;
					
				case 'job_role':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_job_role') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->jobRoleForm->output();
					}
					else 
					{
						$this->employee->jobRoleForm->showLegend(false);
						$this->employee->jobRoleForm->processDependencies();
						$output .= $this->employee->jobRoleForm->readOnlyOutput();
					}
					break;
					
				case 'employment_history':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_employment_history') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->employmentHistoryForm->output();
					}
					else 
					{
						$this->employee->employmentHistoryForm->showLegend(false);
						$this->employee->employmentHistoryForm->processDependencies();
						$output .= $this->employee->employmentHistoryForm->readOnlyOutput();
					}
					break;
					
				case 'it_information':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_it_information') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->ITInformationForm->output();
					}
					else 
					{
						$this->employee->ITInformationForm->showLegend(false);
						$this->employee->ITInformationForm->processDependencies();
						$output .= $this->employee->ITInformationForm->readOnlyOutput();
					}
					break;
					
				case 'asset_data':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_asset_data') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->assetDataForm->output();
					}
					else 
					{
						$this->employee->assetDataForm->showLegend(false);
						$this->employee->assetDataForm->processDependencies();
						$output .= $this->employee->assetDataForm->readOnlyOutput();
					}
					break;
					
				case 'training':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_training') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->trainingForm->output();
					}
					else 
					{
						$this->employee->trainingForm->showLegend(false);
						$this->employee->trainingForm->processDependencies();
						$output .= $this->employee->trainingForm->readOnlyOutput();
					}
					break;
					
				case 'ppe_and_hse':
				
					if ($outputType=="normal" 
						&& (currentuser::getInstance()->hasPermission('admin')
						|| currentuser::getInstance()->hasPermission('employeedb_global')
						|| (currentuser::getInstance()->hasPermission('employeedb_ppe_and_hse') && currentuser::getInstance()->hasPermission($userCountry))
					))
					{
						$output .= $this->employee->PPEandHSEtrainingForm->output();
					}
					else 
					{
						$this->employee->PPEandHSEtrainingForm->showLegend(false);
						$this->employee->PPEandHSEtrainingForm->processDependencies();
						$output .= $this->employee->PPEandHSEtrainingForm->readOnlyOutput();
					}
					break;
					
			}
			
			//$output .= $this->employee->form->output(array($this->getPageAction()));
			
			$output .= "</" . $this->getPageAction() . ">";			
		}
		
		return $output;
	}
	
	
	public function buildMenu()
	{		
		$output = "";
		
		$dbCountry = $this->employee->personalDetailsForm->get("personnelFile")->getValue();
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
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'personal_details' ? 'true' : 'false') . '" name="personal_details" valid="' . ($this->employee->personalDetailsForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_job_role') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'job_role' ? 'true' : 'false') . '" name="job_role" valid="' . ($this->employee->jobRoleForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_employment_history') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'employment_history' ? 'true' : 'false') . '" name="employment_history" valid="' . ($this->employee->employmentHistoryForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_it_information') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'it_information' ? 'true' : 'false') . '" name="it_information" valid="' . ($this->employee->ITInformationForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_asset_data') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'asset_data' ? 'true' : 'false') . '" name="asset_data" valid="' . ($this->employee->assetDataForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_training') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'training' ? 'true' : 'false') . '" name="training" valid="' . ($this->employee->trainingForm->isValid() ? "true" : "false") . '" />';
		}
		
		if (currentuser::getInstance()->hasPermission('admin')
			|| currentuser::getInstance()->hasPermission('employeedb_global')
			|| (currentuser::getInstance()->hasPermission('employeedb_ppe_and_hse') && currentuser::getInstance()->hasPermission($userCountry))
		)
		{	
			$output .= '<employeeNavSection selected="' . ($this->getLocation() == 'ppe_and_hse' ? 'true' : 'false') . '" name="ppe_and_hse" valid="' . ($this->employee->PPEandHSEtrainingForm->isValid() ? "true" : "false") . '" />';
		}
		
		return $output;		
	}
}