<?php

class newLeaver
{
	public $newLeaverForm;

	private $id = 0;

	private $loadedFromDatabase = false;

	function __construct()
	{
		$this->definenewLeaverForm();

		$this->newLeaverForm->setStoreInSession(true);
		$this->newLeaverForm->loadSessionData();
		$this->newLeaverForm->processDependencies();

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

		if (!$this->newLeaverForm->validate($groups))
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
				if ($this->newLeaverForm->get($key))
				{
					if ($this->newLeaverForm->get($key)->getDataType() == "date")
					{
						$this->newLeaverForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else
					{
						$this->newLeaverForm->get($key)->setValue(page::xmlentities($value));
					}
				}
			}


			$this->newLeaverForm->putValuesInSession();

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

			$newLeaverFormQuery = $this->newLeaverForm->generateUpdateQuery("newLeaver");

			if (!empty($newLeaverFormQuery))
			{
				mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $newLeaverFormQuery . " WHERE id='" . $this->id . "'");
			}

			$this->addLog("Record updated");

		}
		else
		{
			$this->newLeaverForm->get("name")->setValue($this->newLeaverForm->get("firstName")->getValue() . " " . $this->newLeaverForm->get("lastName")->getValue());
			
			$this->newLeaverForm->get("employeeId")->setValue($this->id);
			
			// begin transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("BEGIN");

			// insert
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO leaver " . $this->newLeaverForm->generateInsertQuery("leaver"));

			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT id FROM employee ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);

			// end transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("COMMIT");


			$this->id = $fields['id'];

			$this->addLog("Record added");
		}

		// Send Emails determined by site
		$emailDataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT employee.NTLogon, employee.firstName, employee.lastName, employee.email, employee.site FROM employee INNER JOIN permissions ON employee.NTLogon = permissions.NTLogon WHERE employee.site = '" . $this->newLeaverForm->get("personnelFile")->getValue() . "' AND permissions.permission = 'employeedb_leaver_" . $this->newLeaverForm->get("personnelFile")->getValue() . "'");
		while($emailFields = mysql_fetch_array($emailDataset))
		{
			$this->sendConfirmationEmail("newLeaver", $this->id, $emailFields['firstName'] . " " . $emailFields['lastName'], $this->newLeaverForm->get("name")->getValue(), $emailFields['email'], $this->newLeaverForm->get("leaveDate")->getValue());
		}

		$this->sendConfirmationEmail("newLeaver", $this->id, $this->newLeaverForm->get("SETResponsible")->getValue(), $this->newLeaverForm->get("name")->getValue(), usercache::getInstance()->get($this->newLeaverForm->get("SETResponsible")->getValue())->getEmail(), $this->newLeaverForm->get("leaveDate")->getValue());
		

		//$this->newLeaverForm->get("picture")->setFinalFileLocation("/apps/employeedb/attachments/photos/" . $this->id . "/");
		//$this->newLeaverForm->get("picture")->moveTempFileToFinal();


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
		return $this->newLeaverForm->get("firstName")->getValue() . " " . $this->newLeaverForm->get("lastName")->getValue();
	}

	public function getName()
	{
		return $this->newLeaverForm->get("name")->getValue();
	}

	private function definenewLeaverForm()
	{
		$this->newLeaverForm = new form("newLeaverForm");
		$this->newLeaverForm->showLegend(true);

		$default = new group("default");
		$jobGroup = new group("jobGroup");

		$firstName = new textbox("firstName");
		$firstName->setTable("leaver");
		$firstName->setDataType("text");
		$firstName->setRequired(true);
		$firstName->setLength(50);
		$firstName->setRowTitle("first_name");
		$firstName->setLabel("Personnel Details");
		//$firstName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($firstName);

		$lastName = new textbox("lastName");
		$lastName->setTable("leaver");
		$lastName->setDataType("text");
		$lastName->setRequired(true);
		$lastName->setLength(50);
		$lastName->setRowTitle("last_name");
		//$lastName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($lastName);

		$name = new textbox("name");
		$name->setTable("INSERT INTO employee (`firstName`,`lastName`,`name`,`personnelFile`,`department`,`workLocation`,`employeeStatus`,`employmentStatus`) VALUES ('test','person5','test person5','Dunstable','IT','Dunstable','','2007-06-25')");
		$name->setDataType("text");
		$name->setRequired(false);
		$name->setVisible(false);
		$name->setLength(50);
		$name->setRowTitle("name");
		$default->add($name);

		$personnelFile = new dropdown("personnelFile");
		$personnelFile->setTable("INSERT INTO employee (`firstName`,`lastName`,`name`,`personnelFile`,`department`,`workLocation`,`employeeStatus`,`employmentStatus`) VALUES ('test','person5','test person5','Dunstable','IT','Dunstable','','2007-06-25')");
		$personnelFile->setDataType("string");
		$personnelFile->setRequired(false);
		$personnelFile->setXMLSource('apps/employeedb/xml/sites.xml');
		$personnelFile->setRowTitle("personnel_File");
		$default->add($personnelFile);

		$department = new dropdown("department");
		$department->setTable("leaver");
		$department->setDataType("string");
		$department->setLabel("Job Specific");
		$department->setRequired(false);
		$department->setXMLSource("apps/employeedb/xml/departments.xml");
		$department->setRowTitle("department");
		$jobGroup->add($department);
		
		$SETResponsible = new dropdown("SETResponsible");
		$SETResponsible->setTable("leaver");
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
		$workLocation->setTable("leaver");
		$workLocation->setDataType("string");
		$workLocation->setRequired(false);
		$workLocation->setXMLSource('apps/employeedb/xml/sites.xml');
		$workLocation->setArraySource(array(
		array('value' => 'Home', 'display' => 'Home')
		));
		$workLocation->setRowTitle("work_location");
		$jobGroup->add($workLocation);

		$employmentStatus = new textbox("employmentStatus");
		$employmentStatus->setTable("leaver");
		$employmentStatus->setDataType("text");
		$employmentStatus->setRequired(false);
		$employmentStatus->setVisible(false);
		$employmentStatus->setLength(50);
		$employmentStatus->setRowTitle("employee_status");
		$jobGroup->add($employmentStatus);
		
		$employeeId = new textbox("employeeId");
		$employeeId->setTable("leaver");
		$employeeId->setDataType("text");
		$employeeId->setRequired(false);
		$employeeId->setVisible(false);
		$employeeId->setLength(50);
		$employeeId->setRowTitle("employeeId");
		$jobGroup->add($employeeId);

		$leaveDate = new textbox("leaveDate");
		$leaveDate->setTable("leaver");
		$leaveDate->setDataType("date");
		$leaveDate->setRequired(false);
		$leaveDate->setRowTitle("leave_date");
		$jobGroup->add($leaveDate);



		$this->newLeaverForm->add($default);
		$this->newLeaverForm->add($jobGroup);
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

	public function sendConfirmationEmail($location, $id, $sendTo, $name, $emailAddress, $leaveDate)
	{
		// newAction, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<$location><sendTo>" . $sendTo . "</sendTo><id>" . $id . "</id><name>" . $name . "</name><leaveDate>" . $leaveDate . "</leaveDate></$location>");
				
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/employeedb/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		//email::send(usercache::getInstance()->get($this->form->get("owner")->getValue())->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("new_employee_entry") . " - Name: " . $name), "$email");
		email::send($emailAddress, "intranet@scapa.com", (translate::getInstance()->translate("leaving_employee") . " - Name: " . $name), "$email");
		
		return true;
	}

}

?>