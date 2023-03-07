<?php

require 'lib/employee.php';


class index extends page 
{
	private $employee;
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('employeedb');
		
		$this->setPermissionRequired(array('admin', 'employeedb_global', 'employeedb_global','employeedb_personal_details','employeedb_job_role','employeedb_employment_history','employeedb_it_information','employeedb_asset_data','employeedb_training','employeedb_ppe_and_hse'));
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/employeedb/xml/menu.xml");
		
		
		$this->add_output("<employeedb>");
		
		$snapins = new snapinGroup('employeedb_left');
		$snapins->register('apps/employeedb', 'load', true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$this->employee = new employee();
		
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// get anything posted by the form
						
			
			if (isset($_REQUEST['id']))
			{
				$_POST['employee'] = $_REQUEST['id'];
			}
			
			
			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['employee']))
			{
				$_POST['employee'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}
	
			$this->xml .= "<employeeId>" . $this->employee->getId() . "</employeeId>\n";
			
			//$this->form->processPost();
			
			$this->xml .= "<employee>";
			
				if ($this->employee->load($_POST['employee']))
				{
					$this->xml .= "<id>" . $this->employee->getId() . "</id>\n";
					$this->xml .= "<name>" . $this->employee->getName() . "</name>";
					$this->xml .= "<fullName>" . $this->employee->getFullName() . "</fullName>";
					$this->xml .= "<site>" . $this->employee->jobRoleForm->get('workLocation')->getValue() . "</site>";
					$this->xml .= "<jobTitle>" . $this->employee->jobRoleForm->get('jobTitle')->getValue() . "</jobTitle>";
				}
				
			$this->xml .= "</employee>";
			
			
			
			$datasetDoc = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM `document` WHERE `employeeId` = " . $this->employee->getId() . "");
			$fieldsDoc = mysql_fetch_array($datasetDoc);
			
			if($fieldsDoc > 0)
			{
				$this->xml .= "<document>";
				$this->xml .= "<id>" . $fieldsDoc['employeeId'] . "</id>";
				$this->xml .= "<docCreationDate>" . common::transformDateForPHP($fieldsDoc['docCreationDate']) . "</docCreationDate>";
				$this->xml .= "</document>";
			}
			
			
			// LOG STUFF   - ps 1 and l are exactly the same character
		
			$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM log WHERE employeeId='" . $this->employee->getId() . "' ORDER BY logDate DESC, id DESC");

			$this->xml .= "<log>";
			
				while ($fields = mysql_fetch_array($dataset)) 
				{
					$this->xml .= "<item>";
					$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
					$this->xml .= "<date>" . $fields['logDate'] . "</date>\n";
					$this->xml .= "<action>" . $fields['action'] . "</action>\n";
					$this->xml .= "</item>";
				}
				
			$this->xml .= "</log>";
			
			$this->add_output($this->xml);
		}
					
		
		
		
		
		$this->add_output("</employeedb>");
		$this->output('./apps/employeedb/xsl/summary.xsl');
	}
}

?>
