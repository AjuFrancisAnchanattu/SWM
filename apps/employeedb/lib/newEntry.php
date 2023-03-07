<?php

class newEntry
{
	public $newEntryForm;
	
	private $id = 0;
	
	private $loadedFromDatabase = false;
	
	function __construct()
	{
		$this->defineNewEntryForm();

		$this->newEntryForm->setStoreInSession(true);
		$this->newEntryForm->loadSessionData();
		$this->newEntryForm->processDependencies();
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
		{
			$this->id = $_SESSION['apps'][$GLOBALS['app']]['id'];
		}
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['loadedFromDatabase']))
		{
			$this->loadedFromDatabase = true;
		}
	}
	
	public function validate($groups = array())
	{
		$valid = true;
		
		if (!$this->newEntryForm->validate($groups))
		{
			$valid = false;
		}
				
		return $valid;
	}
	
	
	public function load($id)
	{
		if (!is_numeric($id))
		{
			return false;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM employee WHERE id = $id");
		
		if (mysql_num_rows($dataset) == 1)
		{			
			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['loadedFromDatabase'] = true;
				
			$fields = mysql_fetch_array($dataset);
			
			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;
			
			//$this->personalDetailsForm->get("picture")->load("/apps/employeedb/attachments/photos/" . $this->id . "/");
			
			
			foreach ($fields as $key => $value)
			{
				if ($this->newEntryForm->get($key))
				{
					if ($this->newEntryForm->get($key)->getDataType() == "date")
					{
						$this->newEntryForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->newEntryForm->get($key)->setValue(page::xmlentities($value));
					}
				}
			}
			
			
			$this->newEntryForm->putValuesInSession();
			
			return true;
		}
		else
		{
			return false;
		}
		
		
	}
	
	
	public function save()
	{
		if ($this->loadedFromDatabase)
		{
			// update
			
			$newEntryFormQuery = $this->newEntryForm->generateUpdateQuery("newEntry");
		
			if (!empty($newEntryFormQuery))
			{
				mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $newEntryFormQuery . " WHERE id='" . $this->id . "'");
			}
			
			$this->addLog("Record updated");
			
		}
		else 
		{		
			$this->newEntryForm->get("name")->setValue($this->newEntryForm->get("firstName")->getValue() . " " . $this->newEntryForm->get("lastName")->getValue());
			
			// begin transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO employee " . $this->newEntryForm->generateInsertQuery("employee"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT id FROM employee ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			
			// end transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("COMMIT");
			
			$this->id = $fields['id'];

			$this->addLog("Record added");
		}

		// Send Emails determined by site
		$emailDataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT employee.NTLogon, employee.firstName, employee.lastName, employee.email, employee.site FROM employee INNER JOIN permissions ON employee.NTLogon = permissions.NTLogon WHERE employee.site = '" . $this->newEntryForm->get("personnelFile")->getValue() . "' AND permissions.permission = 'employeedb_entry_" . $this->newEntryForm->get("personnelFile")->getValue() . "'");
		while($emailFields = mysql_fetch_array($emailDataset))
		{
			$this->sendConfirmationEmail("newEntry", $this->id, $emailFields['firstName'] . " " . $emailFields['lastName'], $this->newEntryForm->get("name")->getValue(), $emailFields['email'], $this->newEntryForm->get("employmentStartDate")->getValue());	
		}
			
		// Send to SET Responsible
		$this->sendConfirmationEmail("newEntry", $this->id, $this->newEntryForm->get("SETResponsible")->getValue(), $this->newEntryForm->get("name")->getValue(), usercache::getInstance()->get($this->newEntryForm->get("SETResponsible")->getValue())->getEmail(), $this->newEntryForm->get("employmentStartDate")->getValue());	
		
		
		//$this->newEntryForm->get("picture")->setFinalFileLocation("/apps/employeedb/attachments/photos/" . $this->id . "/");
		//$this->newEntryForm->get("picture")->moveTempFileToFinal();
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
		
		page::redirect("/apps/employeedb/");
	}
	
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getFullName()
	{
		return $this->newEntryForm->get("firstName")->getValue() . " " . $this->newEntryForm->get("lastName")->getValue();
	}
	
	public function getName()
	{
		return $this->newEntryForm->get("name")->getValue();
	}
	
	private function defineNewEntryForm()
	{
		$this->newEntryForm = new form("newEntryForm");
		$this->newEntryForm->showLegend(true);
		
		$default = new group("default");
		$jobGroup = new group("jobGroup");
		$locationGroup = new group("locationGroup");
		$logonGroup = new group("logonGroup");
		
		$firstName = new textbox("firstName");
		$firstName->setTable("employee");
		$firstName->setDataType("text");
		$firstName->setRequired(true);
		$firstName->setLength(50);
		$firstName->setRowTitle("first_name");
		$firstName->setLabel("Personnel Details");
		//$firstName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($firstName);
		
		$lastName = new textbox("lastName");
		$lastName->setTable("employee");
		$lastName->setDataType("text");
		$lastName->setRequired(true);
		$lastName->setLength(50);
		$lastName->setRowTitle("last_name");
		//$lastName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($lastName);
		
		$name = new textbox("name");
		$name->setTable("employee");
		$name->setDataType("text");
		$name->setRequired(false);
		$name->setVisible(false);
		$name->setLength(50);
		$name->setRowTitle("name");
		$default->add($name);
		
		$personnelFile = new dropdown("personnelFile");
		$personnelFile->setTable("employee");
		$personnelFile->setDataType("string");
		$personnelFile->setRequired(false);
		$personnelFile->setXMLSource('apps/employeedb/xml/sites.xml');
		$personnelFile->setRowTitle("personnel_File");
		$default->add($personnelFile);
		
		$department = new dropdown("department");
		$department->setTable("employee");
		$department->setDataType("string");
		$department->setLabel("Job Specific");
		$department->setRequired(false);
		$department->setXMLSource("apps/employeedb/xml/departments.xml");
		$department->setRowTitle("department");
		$jobGroup->add($department);
		
		$managerResponsible = new autocomplete("managerResponsible");
		$managerResponsible->setTable("employee");
		$managerResponsible->setDataType("text");
		$managerResponsible->setLength(50);
		$managerResponsible->setUrl('/apps/employeedb/ajax/employee?key=managerResponsible');
		$managerResponsible->setRequired(false);
		$managerResponsible->setRowTitle("manager_responsible");
		$jobGroup->add($managerResponsible);
		
		$SETResponsible = new dropdown("SETResponsible");
		$SETResponsible->setTable("employee");
		$SETResponsible->setDataType("text");
		$SETResponsible->setLength(50);
		$SETResponsible->setArraySource(array(
			array('value' => 'awoodward', 'display' => 'Andy Woodward'),
			array('value' => 'csmith', 'display' => 'Chris Smith'),
			array('value' => 'dsherwin', 'display' => 'Derick Sherwin')
		));
		$SETResponsible->setRequired(false);
		$SETResponsible->setRowTitle("set_responsible");
		$jobGroup->add($SETResponsible);
		
		$workLocation = new dropdownalternative("workLocation");
		$workLocation->setTable("employee");
		$workLocation->setDataType("string");
		$workLocation->setLabel("Location and Start Date");
		$workLocation->setRequired(false);
		$workLocation->setXMLSource('apps/employeedb/xml/sites.xml');
		$workLocation->setArraySource(array(
			array('value' => 'Home', 'display' => 'Home')
		));
		$workLocation->setRowTitle("work_location");
		$locationGroup->add($workLocation);
		
		$employmentStartDate = new textbox("employmentStartDate");
		$employmentStartDate->setTable("employee");
		$employmentStartDate->setDataType("date");
		$employmentStartDate->setRequired(false);
		$employmentStartDate->setRowTitle("start_date_with_company");
		$locationGroup->add($employmentStartDate);
		
		$networkLogonRequired = new radio("networkLogonRequired");
		$networkLogonRequired->setTable("employee");
		$networkLogonRequired->setDataType("number");
		$networkLogonRequired->setLabel("Access To Scapa Network");
		$networkLogonRequired->setRequired(false);
		$networkLogonRequired->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$networkLogonRequired->setValue(0);
		$networkLogonRequired->setRowTitle("network_Logon_Required");
		$logonGroup->add($networkLogonRequired);	
		
		$employmentStatus = new radio("employmentStatus");
		$employmentStatus->setTable("employee");
		$employmentStatus->setDataType("string");
		$employmentStatus->setRequired(true);
		$employmentStatus->setArraySource(array(
			array('value' => 'current', 'display' => 'Current'),
			array('value' => 'leaver', 'display' => 'Leaver'),
			array('value' => 'inactive', 'display' => 'Inactive')
		));
		$employmentStatus->setValue('inactive');
		$employmentStatus->setVisible(false);
		$employmentStatus->setRowTitle("employment_Status");	
		$logonGroup->add($employmentStatus);
		
		
		$this->newEntryForm->add($default);
		$this->newEntryForm->add($jobGroup);
		$this->newEntryForm->add($locationGroup);
		$this->newEntryForm->add($logonGroup);
	}	
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("employeedb")->Execute(sprintf("INSERT INTO log (employeeId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
			$this->id,
			currentuser::getInstance()->getNTLogon(),
			$action,
			common::nowDateTimeForMysql()
		));
	}
	
	public function sendConfirmationEmail($location, $id, $sendTo, $name, $emailAddress, $startDate)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$location><sendTo>" . $sendTo . "</sendTo><id>" . $id . "</id><name>" . $name . "</name><startDate>" . $startDate . "</startDate></$location>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/employeedb/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		//email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("new_employee_entry") . " - Name: " . $name), "$email");
		email::send($emailAddress, "intranet@scapa.com", (translate::getInstance()->translate("new_employee_entry") . " - Name: " . $name), "$email");
		
		return true;
	}
	
}

?>