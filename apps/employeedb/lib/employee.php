<?php

class employee
{
	public $personalDetailsForm;
	public $jobRoleForm;
	public $employmentHistoryForm;
	public $ITInformationForm;
	public $assetDataForm;
	public $trainingForm;
	public $PPEandHSEtrainingForm;
	
	private $id = 0;
	
	private $loadedFromDatabase = false;
	
	function __construct()
	{
		$this->definePersonalDetailsForm();
		$this->defineJobRoleForm();
		$this->defineEmploymentHistoryForm();
		$this->defineITInformationForm();
		$this->defineAssetDataForm();
		$this->defineTrainingForm();
		$this->definePPEandHSEtrainingForm();
		
		$this->personalDetailsForm->setStoreInSession(true);
		$this->personalDetailsForm->loadSessionData();
		$this->personalDetailsForm->processDependencies();

		$this->jobRoleForm->setStoreInSession(true);
		$this->jobRoleForm->loadSessionData();
		$this->jobRoleForm->processDependencies();
		
		$this->employmentHistoryForm->setStoreInSession(true);
		$this->employmentHistoryForm->loadSessionData();
		$this->employmentHistoryForm->processDependencies();
		
		$this->ITInformationForm->setStoreInSession(true);
		$this->ITInformationForm->loadSessionData();
		$this->ITInformationForm->processDependencies();
		
		$this->assetDataForm->setStoreInSession(true);
		$this->assetDataForm->loadSessionData();
		$this->assetDataForm->processDependencies();
		
		$this->trainingForm->setStoreInSession(true);
		$this->trainingForm->loadSessionData();
		$this->trainingForm->processDependencies();
		
		$this->PPEandHSEtrainingForm->setStoreInSession(true);
		$this->PPEandHSEtrainingForm->loadSessionData();
		$this->PPEandHSEtrainingForm->processDependencies();
		
		
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
		
		if (!$this->personalDetailsForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->jobRoleForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->employmentHistoryForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->ITInformationForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->assetDataForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->trainingForm->validate($groups))
		{
			$valid = false;
		}
		
		if (!$this->PPEandHSEtrainingForm->validate($groups))
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
			
			$this->personalDetailsForm->get("picture")->load("/apps/employeedb/attachments/photos/" . $this->id . "/");
			
			
			foreach ($fields as $key => $value)
			{
				if ($this->personalDetailsForm->get($key))
				{
					if ($this->personalDetailsForm->get($key)->getDataType() == "date")
					{
						$this->personalDetailsForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->personalDetailsForm->get($key)->setValue(page::xmlentities($value));
					}
				}
				
				if ($this->jobRoleForm->get($key))
				{
					if ($this->jobRoleForm->get($key)->getDataType() == "date")
					{
						$this->jobRoleForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->jobRoleForm->get($key)->setValue(page::xmlentities($value));
					}
				}
				
				if ($this->employmentHistoryForm->get($key))
				{
					if ($this->employmentHistoryForm->get($key)->getDataType() == "date")
					{
						$this->employmentHistoryForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->employmentHistoryForm->get($key)->setValue(page::xmlentities($value));
					}
				}
				
				if ($this->ITInformationForm->get($key))
				{
					if ($this->ITInformationForm->get($key)->getDataType() == "date")
					{
						$this->ITInformationForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->ITInformationForm->get($key)->setValue(page::xmlentities($value));
					}
				}
				
				if ($this->assetDataForm->get($key))
				{
					if ($this->assetDataForm->get($key)->getDataType() == "date")
					{
						$this->assetDataForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->assetDataForm->get($key)->setValue(page::xmlentities($value));
					}
				}
				
				/*if ($this->trainingForm->get($key))
				{
					if ($this->trainingForm->get($key)->getDataType() == "date")
					{
						$this->trainingForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->trainingForm->get($key)->setValue(page::xmlentities($value));
					}
				}*/
				
				if ($this->PPEandHSEtrainingForm->get($key))
				{
					if ($this->PPEandHSEtrainingForm->get($key)->getDataType() == "date")
					{
						$this->PPEandHSEtrainingForm->get($key)->setValue(page::transformDateForPHP($value));
					}
					else 
					{
						$this->PPEandHSEtrainingForm->get($key)->setValue(page::xmlentities($value));
					}
				}
			}
			
			$jobHistoryDataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM jobHistory WHERE employeeId = " . $this->id);
			$this->employmentHistoryForm->multiplePopulate($jobHistoryDataset, $this->employmentHistoryForm->getGroup('jobHistoryGroup'));
			

			$internalCoursesDataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM internalCourses WHERE employeeId = " . $this->id);
			$this->trainingForm->multiplePopulate($internalCoursesDataset, $this->trainingForm->getGroup('internalCoursesGroup'));
	
			
			$externalCoursesDataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM externalCourses WHERE employeeId = " . $this->id);
			$this->trainingForm->multiplePopulate($externalCoursesDataset, $this->trainingForm->getGroup('externalCoursesGroup'));
			

			
			$this->personalDetailsForm->putValuesInSession();
			$this->jobRoleForm->putValuesInSession();
			$this->employmentHistoryForm->putValuesInSession();
			$this->ITInformationForm->putValuesInSession();
			$this->assetDataForm->putValuesInSession();
			$this->trainingForm->putValuesInSession();
			$this->PPEandHSEtrainingForm->putValuesInSession();
			
			
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
			
			$personalDetailsQuery = $this->personalDetailsForm->generateUpdateQuery("employee");
		
			if (!empty($personalDetailsQuery))
			{
				mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $personalDetailsQuery . " WHERE id='" . $this->id . "'");
			}
			
			$this->addLog("Record updated");
			
		}
		else 
		{		
			// begin transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("BEGIN");
			
			// insert
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO employee " . $this->personalDetailsForm->generateInsertQuery("employee"));
			
			// get last inserted
			$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT id FROM employee ORDER BY id DESC LIMIT 1");
			$fields = mysql_fetch_array($dataset);
			
			// end transaction
			mysql::getInstance()->selectDatabase("employeedb")->Execute("COMMIT");
			
			
			$this->id = $fields['id'];

			$this->addLog("Record added");
		}
		
		
		
		
		
		
		$jobRoleQuery = $this->jobRoleForm->generateUpdateQuery("employee");
		
		if (!empty($jobRoleQuery))
		{
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $jobRoleQuery . " WHERE id='" . $this->id . "'");
		}
		
		$employmentHistoryQuery = $this->employmentHistoryForm->generateUpdateQuery("employee");
		
		if (!empty($employmentHistoryQuery))
		{
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $employmentHistoryQuery . " WHERE id='" . $this->id . "'");
		}
		
		$ITInformationQuery = $this->ITInformationForm->generateUpdateQuery("employee");
		
		if (!empty($ITInformationQuery))
		{
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $ITInformationQuery . " WHERE id='" . $this->id . "'");
		}
		
		$assetDataQuery = $this->assetDataForm->generateUpdateQuery("employee");
		
		if (!empty($assetDataQuery))
		{
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $assetDataQuery . " WHERE id='" . $this->id . "'");
		}
		
		$PPEandHSEtrainingQuery = $this->PPEandHSEtrainingForm->generateUpdateQuery("employee");
		
		if (!empty($PPEandHSEtrainingQuery))
		{
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $PPEandHSEtrainingQuery . " WHERE id='" . $this->id . "'");
		}
		
		/*
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $this->employmentHistoryForm->generateUpdateQuery("employee") . " WHERE id='" . $this->id . "'");
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $this->ITInformationForm->generateUpdateQuery("employee") . " WHERE id='" . $this->id . "'");
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $this->assetDataForm->generateUpdateQuery("employee") . " WHERE id='" . $this->id . "'");
			//mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $this->trainingForm->generateUpdateQuery("employee") . " WHERE id='" . $this->id . "'");
			mysql::getInstance()->selectDatabase("employeedb")->Execute("UPDATE employee " . $this->PPEandHSEtrainingForm->generateUpdateQuery("employee") . " WHERE id='" . $this->id . "'");
			
		*/
		
		
		
		
		
		
		
		
		mysql::getInstance()->selectDatabase("employeedb")->Execute("DELETE FROM jobHistory WHERE employeeId = " . $this->id);
			
		for ($i=0; $i < $this->employmentHistoryForm->getGroup("jobHistoryGroup")->getRowCount(); $i++)
		{
			$this->employmentHistoryForm->getGroup("jobHistoryGroup")->setForeignKeyValue($this->id);
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO jobHistory " . $this->employmentHistoryForm->getGroup("jobHistoryGroup")->generateInsertQuery($i));
		}
		
		mysql::getInstance()->selectDatabase("employeedb")->Execute("DELETE FROM internalCourses WHERE employeeId = " . $this->id);
		
		for ($i=0; $i < $this->trainingForm->getGroup("internalCoursesGroup")->getRowCount(); $i++)
		{
			$this->trainingForm->getGroup("internalCoursesGroup")->setForeignKeyValue($this->id);
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO internalCourses " . $this->trainingForm->getGroup("internalCoursesGroup")->generateInsertQuery($i));
		}
		
		mysql::getInstance()->selectDatabase("employeedb")->Execute("DELETE FROM externalCourses WHERE employeeId = " . $this->id);
		
		for ($i=0; $i < $this->trainingForm->getGroup("externalCoursesGroup")->getRowCount(); $i++)
		{
			$this->trainingForm->getGroup("externalCoursesGroup")->setForeignKeyValue($this->id);
			mysql::getInstance()->selectDatabase("employeedb")->Execute("INSERT INTO externalCourses " . $this->trainingForm->getGroup("externalCoursesGroup")->generateInsertQuery($i));
		}
		
		
		$this->personalDetailsForm->get("picture")->setFinalFileLocation("/apps/employeedb/attachments/photos/" . $this->id . "/");
		$this->personalDetailsForm->get("picture")->moveTempFileToFinal();
		
		
		//$this->form->get("attachment")->setFinalFileLocation("/apps/ccr/attachments/reports/" . $this->id . "/");
		//$this->form->get("attachment")->moveTempFileToFinal();
		
		
		//page::redirect("/apps/ccr/");
	}
	
	
	
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getFullName()
	{
		return $this->personalDetailsForm->get("firstName")->getValue() . " " . $this->personalDetailsForm->get("lastName")->getValue();
	}
	
	public function getName()
	{
		return $this->personalDetailsForm->get("name")->getValue();
	}
	
	
	private function definePersonalDetailsForm()
	{
		$this->personalDetailsForm = new form("personalDetailsForm");
		$this->personalDetailsForm->showLegend(true);
		
		$default = new group("default");
		/*$job_role = new group("job_role");
		$employment_history = new group("employment_history");
		$it_information = new group("it_information");
		$asset_data = new group("asset_data");
		$training = new group("training");
		$personal_protective_equipment = new group("personal_protective_equipment");*/
		
		
		
		$firstName = new textbox("firstName");
		$firstName->setTable("employee");
		$firstName->setDataType("text");
		$firstName->setRequired(true);
		$firstName->setLength(50);
		$firstName->setRowTitle("first_name");
		$firstName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($firstName);
		
		$lastName = new textbox("lastName");
		$lastName->setTable("employee");
		$lastName->setDataType("text");
		$lastName->setRequired(true);
		$lastName->setLength(50);
		$lastName->setRowTitle("last_name");
		$lastName->setOnKeyPress("employeedbUpdateKnownAsField");
		$default->add($lastName);
		
		
		$middleName1 = new textbox("middleName1");
		$middleName1->setTable("employee");
		$middleName1->setDataType("text");
		$middleName1->setRequired(false);
		$middleName1->setLength(50);
		$middleName1->setRowTitle("middle_name_1");
		$default->add($middleName1);
		
		$middleName2 = new textbox("middleName2");
		$middleName2->setTable("employee");
		$middleName2->setDataType("text");
		$middleName2->setRequired(false);
		$middleName2->setLength(50);
		$middleName2->setRowTitle("middle_name_2");
		$default->add($middleName2);
		
		$lastName = new textbox("name");
		$lastName->setTable("employee");
		$lastName->setDataType("text");
		$lastName->setRequired(true);
		$lastName->setLength(100);
		$lastName->setRowTitle("known_as");
		$default->add($lastName);
		
		$nationalInsurance = new textbox("nationalInsuranceNumber");
		$nationalInsurance->setTable("employee");
		$nationalInsurance->setDataType("text");
		$nationalInsurance->setRequired(false);
		$nationalInsurance->setLength(50);
		$nationalInsurance->setRowTitle("national_insurance_number");
		$default->add($nationalInsurance);
		
		$dateOfBirth = new textbox("dateOfBirth");
		$dateOfBirth->setTable("employee");
		$dateOfBirth->setDataType("date");
		$dateOfBirth->setRequired(false);
		//$nationalInsurance->setLength(50);
		$dateOfBirth->setRowTitle("date_of_birth");
		$default->add($dateOfBirth);
		
		
		$gender = new radio("gender");
		$gender->setTable("employee");
		$gender->setDataType("text");
		$gender->setLength(50);
		$gender->setRequired(true);
		$gender->setArraySource(array(
			array('value' => 'male', 'display' => 'Male'),
			array('value' => 'female', 'display' => 'Female')
		));
		$gender->setValue('male');
		$gender->setRowTitle("gender");
		$default->add($gender);
		
		
		$nationality = new dropdown("nationality");
		$nationality->setTable("employee");
		$nationality->setDataType("string");
		$nationality->setRequired(false);
		$nationality->setXMLSource('apps/employeedb/xml/country.xml');
		$nationality->setRowTitle("country_of_origin");
		$default->add($nationality);

		
		$address1 = new textbox("address1");
		$address1->setTable("employee");
		$address1->setDataType("text");
		$address1->setLength(50);
		$address1->setRowTitle("address1");
		$default->add($address1);
		
		$address2 = new textbox("address2");
		$address2->setTable("employee");
		$address2->setDataType("text");
		$address2->setLength(50);
		$address2->setRowTitle("address2");
		$default->add($address2);
		
		$address3 = new textbox("city");
		$address3->setTable("employee");
		$address3->setDataType("text");
		$address3->setLength(50);
		$address3->setRowTitle("city");
		$default->add($address3);
		
		$address4 = new textbox("county");
		$address4->setTable("employee");
		$address4->setDataType("text");
		$address4->setLength(50);
		$address4->setRowTitle("county");
		$default->add($address4);
		
		$postcode = new textbox("postcode");
		$postcode->setTable("employee");
		$postcode->setDataType("text");
		$postcode->setLength(50);
		$postcode->setRowTitle("postcode");
		$default->add($postcode);
		
		$country = new dropdown("country");
		$country->setTable("employee");
		$country->setDataType("string");
		//$country->setRequired(true);
		$country->setXMLSource('apps/employeedb/xml/country.xml');
		$country->setRowTitle("country");
		$default->add($country);
		
		
		$homeTelephone = new textbox("homeTelephone");
		$homeTelephone->setTable("employee");
		$homeTelephone->setDataType("text");
		$homeTelephone->setLength(15);
		$homeTelephone->setRowTitle("home_telephone");
		$default->add($homeTelephone);
		
		$personalMobileNumber = new textbox("personalMobileNumber");
		$personalMobileNumber->setTable("employee");
		$personalMobileNumber->setDataType("text");
		$personalMobileNumber->setLength(15);
		$personalMobileNumber->setRowTitle("personal_mobile_Number");
		$default->add($personalMobileNumber);
		
		
		$contactPerson = new textbox("contactPerson");
		$contactPerson->setTable("employee");
		$contactPerson->setDataType("text");
		$contactPerson->setLength(50);
		$contactPerson->setRowTitle("contact_person");
		$default->add($contactPerson);
		
		$relationship = new dropdownAlternative("relationship");
		$relationship->setTable("employee");
		$relationship->setDataType("string");
		$relationship->setRequired(false);
		$relationship->setArraySource(array(
			array('value' => 'spouse', 'display' => 'Spouse'),
			array('value' => 'mother', 'display' => 'Mother'),
			array('value' => 'father', 'display' => 'Father'),
			array('value' => 'daughter', 'display' => 'Daughter'),
			array('value' => 'son', 'display' => 'Son'),
			array('value' => 'brother', 'display' => 'Brother'),
			array('value' => 'sister', 'display' => 'Sister')
		));
		$relationship->setRowTitle("relationship");
		$default->add($relationship);
		
		$nextOfKinContactDetails = new textarea("nextOfKinContactDetails");
		$nextOfKinContactDetails->setTable("employee");
		$nextOfKinContactDetails->setDataType("text");
		//$nextOfKinContactDetails->setLength(50);
		$nextOfKinContactDetails->setRowTitle("next_Of_Kin_Contact_Details");
		$default->add($nextOfKinContactDetails);

		
		$personnelFile = new dropdown("personnelFile");
		$personnelFile->setTable("employee");
		$personnelFile->setDataType("string");
		$personnelFile->setRequired(false);
		$personnelFile->setXMLSource('apps/employeedb/xml/sites.xml');
		$personnelFile->setRowTitle("personnel_File");
		$default->add($personnelFile);
		
		
		$scapaPhoneNumber = new textbox("scapaPhoneNumber");
		$scapaPhoneNumber->setTable("employee");
		$scapaPhoneNumber->setDataType("text");
		$scapaPhoneNumber->setLength(15);
		$scapaPhoneNumber->setRowTitle("scapa_Phone_Number");
		$default->add($scapaPhoneNumber);
		
		$scapaFaxNumber = new textbox("scapaFaxNumber");
		$scapaFaxNumber->setTable("employee");
		$scapaFaxNumber->setDataType("text");
		$scapaFaxNumber->setLength(15);
		$scapaFaxNumber->setRowTitle("scapa_fax_Number");
		$default->add($scapaFaxNumber);
		
		$scapaMobileNumber = new textbox("scapaMobileNumber");
		$scapaMobileNumber->setTable("employee");
		$scapaMobileNumber->setDataType("text");
		$scapaMobileNumber->setLength(15);
		$scapaMobileNumber->setRowTitle("scapa_mobile_Number");
		$default->add($scapaMobileNumber);

		
		
		$languagesSpoken = new textarea("languagesSpoken");
		$languagesSpoken->setTable("employee");
		$languagesSpoken->setDataType("text");
		//$languagesSpoken->setLength(15);
		$languagesSpoken->setRowTitle("languages_Spoken");
		$default->add($languagesSpoken);	
		
		
		$picture = new attachment("picture");
		$picture->setNextAction('personal_details');
		$picture->setTempFileLocation("/apps/employeedb/tmp");
		$picture->setFinalFileLocation("/apps/employeedb/attachments/photos");
		$picture->setRowTitle("picture");
		$default->add($picture);
		
		
		
		$this->personalDetailsForm->add($default);
	}
	
	private function defineJobRoleForm()
	{
		$this->jobRoleForm = new form("jobRoleForm");
		$this->jobRoleForm->showLegend(true);
		
		$positionFromGroup = new group("positionFromGroup");
		$positionFromGroup->setBorder(false);
		
		$jobTypeGroup = new group("jobTypeGroup");
		$jobTypeGroup->setBorder(false);
		$jobTypeDependencyGroup = new group("jobTypeDependencyGroup");
		
		$jobLengthGroup = new group("jobLengthGroup");
		$jobLengthGroup->setBorder(false);
		$jobLengthDependencyGroup = new group("jobLengthDependencyGroup");
		
		$default = new group("default");
		$default->setBorder(false);
		
		
		$carGroup = new group("carGroup");
		
		
		
		
		
		
		
		$currentPositionFrom = new textbox("currentPositionFrom");
		$currentPositionFrom->setTable("employee");
		$currentPositionFrom->setDataType("date");
		$currentPositionFrom->setRequired(false);
		$currentPositionFrom->setRowTitle("current_Position_From");
		$positionFromGroup->add($currentPositionFrom);
		
		
		$jobType = new radio("jobType");
		$jobType->setTable("employee");
		$jobType->setDataType("string");
		$jobType->setRequired(false);
		$jobType->setArraySource(array(
			array('value' => 'full', 'display' => 'Full Time'),
			array('value' => 'part', 'display' => 'Part Time')
		));
		$jobType->setValue('full');
		$jobType->setRowTitle("job_type");
		$jobTypeGroup->add($jobType);
		
		
		$partTimeDependency = new dependency();
		$partTimeDependency->addRule(new rule('jobTypeGroup', 'jobType', 'part'));
		$partTimeDependency->setGroup(array('jobTypeDependencyGroup'));
		$partTimeDependency->setShow(true);
		
		$jobType->addControllingDependency($partTimeDependency);
		
		
		
		$partTimePercentage = new textbox("partTimePercentage");
		$partTimePercentage->setTable("employee");
		$partTimePercentage->setDataType("number");
		$partTimePercentage->setLength(2);
		$partTimePercentage->setLegend("%");
		$partTimePercentage->setRequired(false);
		$partTimePercentage->setRowTitle("percentage_of_part_time");
		$jobTypeDependencyGroup->add($partTimePercentage);
		
		
		$jobLength = new radio("jobLength");
		$jobLength->setTable("employee");
		$jobLength->setDataType("string");
		$jobLength->setRequired(false);
		$jobLength->setArraySource(array(
			array('value' => 'permanent', 'display' => 'Permanent'),
			array('value' => 'tempscapa', 'display' => 'Temp - Scapa'),
			array('value' => 'tempagency', 'display' => 'Temp - Agency')
		));
		$jobLength->setValue('permanent');
		$jobLength->setRowTitle("job_Length");
		$jobLengthGroup->add($jobLength);
		
		
		$jobLengthDependency = new dependency();
		$jobLengthDependency->addRule(new rule('jobLengthGroup', 'jobLength', 'tempscapa'));
		$jobLengthDependency->addRule(new rule('jobLengthGroup', 'jobLength', 'tempagency'));
		$jobLengthDependency->setGroup(array('jobLengthDependencyGroup'));
		$jobLengthDependency->setRuleCondition("or");
		$jobLengthDependency->setShow(true);
		
		$jobLength->addControllingDependency($jobLengthDependency);
		
		
		$probableEndContractDate = new textbox("probableEndContractDate");
		$probableEndContractDate->setTable("employee");
		$probableEndContractDate->setDataType("date");
		$probableEndContractDate->setRequired(false);
		$probableEndContractDate->setRowTitle("probable_End_Contract_Date");
		$jobLengthDependencyGroup->add($probableEndContractDate);
		
		
		
		
		$jobTitle = new textbox("jobTitle");
		$jobTitle->setTable("employee");
		$jobTitle->setDataType("text");
		$jobTitle->setLength(50);
		$jobTitle->setRequired(false);
		$jobTitle->setRowTitle("job_title");
		$default->add($jobTitle);
		
		$jobRole = new dropdown("jobRole");
		$jobRole->setTable("employee");
		$jobRole->setDataType("string");
		$jobRole->setRequired(false);
		$jobRole->setXMLSource("apps/employeedb/xml/jobRole.xml");
		$jobRole->setRowTitle("job_role");
		$default->add($jobRole);
		
		$department = new dropdown("department");
		$department->setTable("employee");
		$department->setDataType("string");
		$department->setRequired(false);
		$department->setXMLSource("apps/employeedb/xml/departments.xml");
		$department->setRowTitle("department");
		$default->add($department);
		
		
		$managerResponsible = new autocomplete("managerResponsible");
		$managerResponsible->setTable("employee");
		$managerResponsible->setDataType("text");
		$managerResponsible->setLength(50);
		$managerResponsible->setUrl('/apps/employeedb/ajax/employee?key=managerResponsible');
		$managerResponsible->setRequired(false);
		$managerResponsible->setRowTitle("manager_responsible");
		$default->add($managerResponsible);
		
		/*$RMTResponsible = new autocomplete("RMTResponsible");
		$RMTResponsible->setTable("employee");
		$RMTResponsible->setDataType("text");
		$RMTResponsible->setLength(50);
		$RMTResponsible->setUrl('/apps/employeedb/ajax/employee?key=RMTResponsible');
		$RMTResponsible->setRequired(false);
		$RMTResponsible->setRowTitle("rmt_responsible");
		$default->add($RMTResponsible);*/
		
		$SETResponsible = new autocomplete("SETResponsible");
		$SETResponsible->setTable("employee");
		$SETResponsible->setDataType("text");
		$SETResponsible->setLength(50);
		$SETResponsible->setUrl('/apps/employeedb/ajax/employee?key=SETResponsible');
		$SETResponsible->setRequired(false);
		$SETResponsible->setRowTitle("set_responsible");
		$default->add($SETResponsible);
		
		
		$workLocation = new dropdownalternative("workLocation");
		$workLocation->setTable("employee");
		$workLocation->setDataType("string");
		$workLocation->setRequired(false);
		$workLocation->setXMLSource('apps/employeedb/xml/sites.xml');
		$workLocation->setArraySource(array(
			array('value' => 'Home', 'display' => 'Home')
		));
		$workLocation->setRowTitle("work_location");
		$default->add($workLocation);
		
		
		$payrollLocation = new dropdown("payrollLocation");
		$payrollLocation->setTable("employee");
		$payrollLocation->setDataType("string");
		$payrollLocation->setRequired(false);
		$payrollLocation->setXMLSource('apps/employeedb/xml/sites.xml');
		$payrollLocation->setRowTitle("payroll_location");
		$default->add($payrollLocation);
		
		
		$appraisalDate = new textbox("appraisalDate");
		$appraisalDate->setTable("employee");
		$appraisalDate->setDataType("date");
		$appraisalDate->setRequired(false);
		$appraisalDate->setRowTitle("last_appraisal_date");
		$default->add($appraisalDate);
		
		$appraisalBy = new autocomplete("appraisalBy");
		$appraisalBy->setTable("employee");
		$appraisalBy->setDataType("text");
		$appraisalBy->setLength(50);
		$appraisalBy->setUrl('/apps/employeedb/ajax/employee?key=appraisalBy');
		$appraisalBy->setRequired(false);
		$appraisalBy->setRowTitle("appraisal_By");
		$default->add($appraisalBy);
		
		$jobDescriptionReviewDate = new textbox("jobDescriptionReviewDate");
		$jobDescriptionReviewDate->setTable("employee");
		$jobDescriptionReviewDate->setDataType("date");
		$jobDescriptionReviewDate->setRequired(false);
		$jobDescriptionReviewDate->setRowTitle("job_Description_Review_Date");
		$default->add($jobDescriptionReviewDate);
		
		$salaryReviewDate = new textbox("salaryReviewDate");
		$salaryReviewDate->setTable("employee");
		$salaryReviewDate->setDataType("date");
		$salaryReviewDate->setRequired(false);
		$salaryReviewDate->setRowTitle("last_salary_Review_Date");
		$default->add($salaryReviewDate);
		
		$costCentre = new textbox("costCentre");
		$costCentre->setTable("employee");
		$costCentre->setDataType("number");
		$costCentre->setLength(10);
		$costCentre->setRequired(false);
		$costCentre->setRowTitle("cost_Centre");
		$default->add($costCentre);
		
		$costCentre = new textbox("costCentre");
		$costCentre->setTable("employee");
		$costCentre->setDataType("number");
		$costCentre->setLength(10);
		$costCentre->setRequired(false);
		$costCentre->setRowTitle("cost_Centre");
		$default->add($costCentre);
		
		$noticeCompany = new textbox("noticeCompany");
		$noticeCompany->setTable("employee");
		$noticeCompany->setDataType("number");
		$noticeCompany->setLength(10);
		$noticeCompany->setRequired(false);
		$noticeCompany->setRowTitle("notice_company");
		$default->add($noticeCompany);
		
		$noticeEmployee = new textbox("noticeEmployee");
		$noticeEmployee->setTable("employee");
		$noticeEmployee->setDataType("number");
		$noticeEmployee->setLength(10);
		$noticeEmployee->setRequired(false);
		$noticeEmployee->setRowTitle("notice_employee");
		$default->add($noticeEmployee);
		
		
		$bonusPlan = new dropdown("bonusPlan");
		$bonusPlan->setTable("employee");
		$bonusPlan->setDataType("text");
		$bonusPlan->setRequired(false);
		$bonusPlan->setXMLSource('apps/employeedb/xml/bonusPlans.xml');
		/*$bonusPlan->setArraySource(array(
			array('value' => 'none', 'display' => 'none'),
			array('value' => 'incentive', 'display' => 'incentive'),
			array('value' => 'site', 'display' => 'site'),
			array('value' => 'european', 'display' => 'european'),
			array('value' => 'rmt', 'display' => 'rmt')			
		));*/
		$bonusPlan->setValue("none");
		$bonusPlan->setRowTitle("bonus_plan");
		$default->add($bonusPlan);
		
		
		$schemePercent = new textbox("schemePercent");
		$schemePercent->setTable("employee");
		$schemePercent->setDataType("number");
		$schemePercent->setLength(2);
		$schemePercent->setRequired(false);
		$schemePercent->setRowTitle("bonus_plan_percentage");
		$default->add($schemePercent);
		
		
		
		
		$pensionScheme = new textbox("pensionScheme");
		$pensionScheme->setTable("employee");
		$pensionScheme->setDataType("text");
		$pensionScheme->setRequired(false);
		$pensionScheme->setLength(50);
		/*$pensionScheme->setArraySource(array(
			array('value' => 'Selotape', 'display' => 'Selotape'),
			array('value' => 'Scapa ', 'display' => 'Scapa')
		));*/
		$pensionScheme->setRowTitle("pension_scheme");
		$default->add($pensionScheme);
		
		
		
		$companyCar = new radio("companyCar");
		$companyCar->setTable("employee");
		$companyCar->setDataType("number");
		$companyCar->setRequired(false);
		$companyCar->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$companyCar->setValue(0);
		$companyCar->setRowTitle("company_car");
		
		$companyCarDependency = new dependency();
		$companyCarDependency->addRule(new rule('default', 'companyCar', 1));
		$companyCarDependency->setGroup(array('carGroup'));
		$companyCarDependency->setShow(true);
		
		$companyCar->addControllingDependency($companyCarDependency);
		
		
		$default->add($companyCar);	
	
	
		$carLevel = new textbox("carLevel");
		$carLevel->setTable("employee");
		$carLevel->setDataType("number");
		$carLevel->setLength(1);
		$carLevel->setRequired(false);
		$carLevel->setRowTitle("car_level");
		$carLevel->setValue(1);
		$carGroup->add($carLevel);
		
		$carFullyFinanced = new radio("carFullyFinanced");
		$carFullyFinanced->setTable("employee");
		$carFullyFinanced->setDataType("number");
		$carFullyFinanced->setRequired(true);
		$carFullyFinanced->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$carFullyFinanced->setValue(1);
		$carFullyFinanced->setRowTitle("car_fully_financed");
		$carGroup->add($carFullyFinanced);
		
		$carPerkRelated = new radio("carPerkRelated");
		$carPerkRelated->setTable("employee");
		$carPerkRelated->setDataType("text");
		$carPerkRelated->setRequired(true);
		$carPerkRelated->setArraySource(array(
			array('value' => 'perk', 'display' => 'Perk'),
			array('value' => 'job', 'display' => 'Job')
		));
		$carPerkRelated->setValue('job');
		$carPerkRelated->setRowTitle("car_allowance");
		$carGroup->add($carPerkRelated);
		
		
		
		$this->jobRoleForm->add($positionFromGroup);
		$this->jobRoleForm->add($jobTypeGroup);
		$this->jobRoleForm->add($jobTypeDependencyGroup);
		$this->jobRoleForm->add($jobLengthGroup);
		$this->jobRoleForm->add($jobLengthDependencyGroup);
		$this->jobRoleForm->add($default);
		$this->jobRoleForm->add($carGroup);
	}
	
	private function defineEmploymentHistoryForm()
	{
		$this->employmentHistoryForm = new form("EmploymentHistoryForm");
		$this->employmentHistoryForm->showLegend(true);

		$employmentStatusGroup = new group("employmentStatusGroup");
		$leaveDateGroup = new group("leaveDateGroup");
		$default = new group("default");
		
		$jobHistoryGroup = new multiplegroup("jobHistoryGroup");
		$jobHistoryGroup->setNextAction("employment_history");
		$jobHistoryGroup->setTable("jobHistory");
		$jobHistoryGroup->setTitle("Job History");
		$jobHistoryGroup->setForeignKey("employeeId");
		
		$employmentStartDate = new textbox("employmentStartDate");
		$employmentStartDate->setTable("employee");
		$employmentStartDate->setDataType("date");
		$employmentStartDate->setRequired(false);
		$employmentStartDate->setRowTitle("start_date_with_company");
		$employmentStatusGroup->add($employmentStartDate);
		
		
		$continuousServiceDate = new textbox("continuousServiceDate");
		$continuousServiceDate->setTable("employee");
		$continuousServiceDate->setDataType("date");
		$continuousServiceDate->setRequired(false);
		$continuousServiceDate->setRowTitle("continuous_service_date");
		$employmentStatusGroup->add($continuousServiceDate);
		
		
		$employmentStatus = new radio("employmentStatus");
		$employmentStatus->setTable("employee");
		$employmentStatus->setDataType("string");
		$employmentStatus->setRequired(true);
		$employmentStatus->setArraySource(array(
			array('value' => 'current', 'display' => 'Current'),
			array('value' => 'leaver', 'display' => 'Leaver'),
			array('value' => 'inactive', 'display' => 'Inactive')
		));
		$employmentStatus->setValue('current');
		$employmentStatus->setRowTitle("employment_Status");
		
		
		$employmentStatusDependency = new dependency();
		$employmentStatusDependency->addRule(new rule('employmentStatusGroup', 'employmentStatus', 'leaver'));
		$employmentStatusDependency->addRule(new rule('employmentStatusGroup', 'employmentStatus', 'inactive'));
		$employmentStatusDependency->setRuleCondition("or");
		$employmentStatusDependency->setGroup('leaveDateGroup');
		$employmentStatusDependency->setShow(true);
		
		$employmentStatus->addControllingDependency($employmentStatusDependency);
		
		$employmentStatusGroup->add($employmentStatus);
		
		
		$leaveReason = new dropdown("leaveReason");
		$leaveReason->setTable("employee");
		$leaveReason->setDataType("string");
		$leaveReason->setRequired(false);
		$leaveReason->setArraySource(array(
			array('value' => 'maternity', 'display' => 'Maternity'),
			array('value' => 'sick', 'display' => 'Long Term Sickness'),
			array('value' => 'sabbatical', 'display' => 'Sabbatical'),
			array('value' => 'retirement', 'display' => 'Retirement'),
			array('value' => 'redundancy', 'display' => 'Redundancy'),
			array('value' => 'dismissal', 'display' => 'Dismissal'),
			array('value' => 'resignation', 'display' => 'Resignation'),
			array('value' => 'death', 'display' => 'Death'),
			array('value' => 'business', 'display' => 'Business Transfer')
		));
		$leaveReason->setRowTitle("leave_reason");
		$leaveDateGroup->add($leaveReason);
		
		
		$contractEndDate = new textbox("contractEndDate");
		$contractEndDate->setTable("employee");
		$contractEndDate->setDataType("date");
		$contractEndDate->setRequired(false);
		$contractEndDate->setRowTitle("contract_End_Date");
		$leaveDateGroup->add($contractEndDate);
		
		
		$leaveDate = new textbox("leaveDate");
		$leaveDate->setTable("employee");
		$leaveDate->setDataType("date");
		$leaveDate->setRequired(false);
		$leaveDate->setRowTitle("leave_Date");
		$leaveDateGroup->add($leaveDate);
				
		
		$startDate = new textbox("startDate");
		$startDate->setTable("jobHistory");
		$startDate->setDataType("date");
		$startDate->setRequired(false);
		$startDate->setRowTitle("start_Date");
		$jobHistoryGroup->add($startDate);
		
		$endDate = new textbox("endDate");
		$endDate->setTable("jobHistory");
		$endDate->setDataType("date");
		$endDate->setRequired(false);
		$endDate->setRowTitle("end_Date");
		$jobHistoryGroup->add($endDate);
		
		
		$jobTitle = new textbox("jobTitle");
		$jobTitle->setTable("jobHistory");
		$jobTitle->setDataType("string");
		$jobTitle->setRequired(false);
		$jobTitle->setRowTitle("job_title");
		$jobHistoryGroup->add($jobTitle);
		
		
		
		/*$startingJobDescription = new textarea("startingJobDescription");
		$startingJobDescription->setTable("employee");
		$startingJobDescription->setDataType("text");
		$startingJobDescription->setRequired(false);
		$startingJobDescription->setRowTitle("starting_job_description");
		$default->add($startingJobDescription);
		*/
		
		
		
		
		$inductionLevel = new textbox("inductionLevel");
		$inductionLevel->setTable("employee");
		$inductionLevel->setDataType("number");
		$inductionLevel->setLength(1);
		$inductionLevel->setRequired(false);
		$inductionLevel->setRowTitle("induction_level");
		$default->add($inductionLevel);
		
		$inductionDate = new textbox("inductionDate");
		$inductionDate->setTable("employee");
		$inductionDate->setDataType("date");
		$inductionDate->setRequired(false);
		$inductionDate->setRowTitle("induction_date");
		$default->add($inductionDate);
		
		/*$successiveJobDescriptions = new textarea("successiveJobDescriptions");
		$successiveJobDescriptions->setTable("employee");
		$successiveJobDescriptions->setDataType("text");
		$successiveJobDescriptions->setRequired(false);
		$successiveJobDescriptions->setRowTitle("successive_job_descriptions");
		$default->add($successiveJobDescriptions);*/
		
		$probationPeriod = new textbox("probationPeriod");
		$probationPeriod->setTable("employee");
		$probationPeriod->setDataType("number");
		$probationPeriod->setLength(3);
		$probationPeriod->setRequired(false);
		$probationPeriod->setRowTitle("probation_period");
		$default->add($probationPeriod);
		
		$probationFromDate = new textbox("probationFromDate");
		$probationFromDate->setTable("employee");
		$probationFromDate->setDataType("date");
		$probationFromDate->setRequired(false);
		$probationFromDate->setRowTitle("probation_from_date");
		$default->add($probationFromDate);
		
		$probationToDate = new textbox("probationToDate");
		$probationToDate->setTable("employee");
		$probationToDate->setDataType("date");
		$probationToDate->setRequired(false);
		$probationToDate->setRowTitle("probation_to_date");
		$default->add($probationToDate);
		
		$estimatedRetirementDate = new textbox("estimatedRetirementDate");
		$estimatedRetirementDate->setTable("employee");
		$estimatedRetirementDate->setDataType("date");
		$estimatedRetirementDate->setRequired(false);
		$estimatedRetirementDate->setRowTitle("estimated_Retirement_Date");
		$default->add($estimatedRetirementDate);
		
		
		$positionsAppliedFor = new textarea("positionsAppliedFor");
		$positionsAppliedFor->setTable("employee");
		$positionsAppliedFor->setDataType("text");
		$positionsAppliedFor->setRequired(false);
		$positionsAppliedFor->setRowTitle("positions_Applied_For");
		$default->add($positionsAppliedFor);
		
		$previousEmployers = new textarea("previousEmployers");
		$previousEmployers->setTable("employee");
		$previousEmployers->setDataType("text");
		$previousEmployers->setRequired(false);
		$previousEmployers->setRowTitle("previous_Employers");
		$default->add($previousEmployers);
		
		
		$educationLevel = new dropdown("educationLevel");
		$educationLevel->setTable("employee");
		$educationLevel->setDataType("string");
		$educationLevel->setRequired(false);
		$educationLevel->setArraySource(array(
			array('value' => 'none', 'display' => 'No Formal Qualifications'),
			array('value' => 'apprenticeship', 'display' => 'Apprenticeship'),
			array('value' => 'school', 'display' => 'School'),
			array('value' => 'university', 'display' => 'University'),
			array('value' => 'postgrad', 'display' => 'Post Graduate')
		));
		$educationLevel->setRowTitle("education_Level");
		$default->add($educationLevel);
		
		$formalQualifications = new textarea("formalQualifications");
		$formalQualifications->setTable("employee");
		$formalQualifications->setDataType("text");
		$formalQualifications->setRequired(false);
		$formalQualifications->setRowTitle("formal_qualifications");
		$default->add($formalQualifications);
		
		$this->employmentHistoryForm->add($employmentStatusGroup);
		$this->employmentHistoryForm->add($leaveDateGroup);
		
		$this->employmentHistoryForm->add($jobHistoryGroup);
		
		$this->employmentHistoryForm->add($default);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	private function defineITInformationForm()
	{
		$this->ITInformationForm = new form("ITInformationForm");
		$this->ITInformationForm->showLegend(true);
		
		$switch = new group("switch");
		$default = new group("default");
		$default->setBorder(false);
		
		$sapSwitch = new group("sapSwitch");
		$sapSwitch->setBorder(false);
		
		$sap = new group("sap");
		
		$hardwareSwitch = new group("hardwareSwitch");
		$hardwareSwitch->setBorder(false);
		
		$hardware = new group("hardware");
		
		
		
		
		$networkLogonRequired = new radio("networkLogonRequired");
		$networkLogonRequired->setTable("employee");
		$networkLogonRequired->setDataType("number");
		$networkLogonRequired->setRequired(false);
		$networkLogonRequired->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$networkLogonRequired->setValue(0);
		$networkLogonRequired->setRowTitle("network_Logon_Required");
		$switch->add($networkLogonRequired);		
		
		
		$networkLogonDependency = new dependency();
		$networkLogonDependency->addRule(new rule('switch', 'networkLogonRequired', 1));
		$networkLogonDependency->setGroup(array('default', 'sapSwitch', 'hardwareSwitch'));
		$networkLogonDependency->setShow(true);
		
		$networkLogonRequired->addControllingDependency($networkLogonDependency);
		
		
		$networkLogonDependencySap = new dependency();
		$networkLogonDependencySap->addRule(new rule('switch', 'networkLogonRequired', 1));
		$networkLogonDependencySap->addRule(new rule('sapSwitch', 'sapLogonRequired', 1));
		$networkLogonDependencySap->setRuleCondition("AND");
		$networkLogonDependencySap->setGroup('sap');
		$networkLogonDependencySap->setShow(true);
		
		$networkLogonRequired->addControllingDependency($networkLogonDependencySap);
		
		
		$networkLogonDependencyHardware = new dependency();
		$networkLogonDependencyHardware->addRule(new rule('switch', 'networkLogonRequired', 1));
		$networkLogonDependencyHardware->addRule(new rule('hardwareSwitch', 'PCRequired', 1));
		$networkLogonDependencyHardware->setRuleCondition("AND");
		$networkLogonDependencyHardware->setGroup('hardware');
		$networkLogonDependencyHardware->setShow(true);
		
		$networkLogonRequired->addControllingDependency($networkLogonDependencyHardware);
		
		
		
		
		$networkLogonSite = new dropdownalternative("networkLogonSite");
		$networkLogonSite->setTable("employee");
		$networkLogonSite->setDataType("string");
		$networkLogonSite->setRequired(false);
		$networkLogonSite->setXMLSource('apps/employeedb/xml/sites.xml');
		$networkLogonSite->setRowTitle("network_Logon_Site");
		$default->add($networkLogonSite);
		
		
		$networkLogonDate = new textbox("networkLogonDate");
		$networkLogonDate->setTable("employee");
		$networkLogonDate->setDataType("date");
		$networkLogonDate->setRequired(false);
		$networkLogonDate->setRowTitle("network_Logon_Date");
		$default->add($networkLogonDate);
		
		
		$networkLogonGrantedDate = new textbox("networkLogonGrantedDate");
		$networkLogonGrantedDate->setTable("employee");
		$networkLogonGrantedDate->setDataType("date");
		$networkLogonGrantedDate->setRequired(false);
		$networkLogonGrantedDate->setRowTitle("network_Logon_Granted_Date");
		$default->add($networkLogonGrantedDate);
		
		$networkLogonGrantedDate = new textbox("networkLogonGrantedDate");
		$networkLogonGrantedDate->setTable("employee");
		$networkLogonGrantedDate->setDataType("date");
		$networkLogonGrantedDate->setRequired(false);
		$networkLogonGrantedDate->setRowTitle("network_Logon_Granted_Date");
		$default->add($networkLogonGrantedDate);
		
		$networkLogonRemovedFromDate = new textbox("networkLogonRemovedFromDate");
		$networkLogonRemovedFromDate->setTable("employee");
		$networkLogonRemovedFromDate->setDataType("date");
		$networkLogonRemovedFromDate->setRequired(false);
		$networkLogonRemovedFromDate->setRowTitle("network_Logon_Removed_From_Date");
		$default->add($networkLogonRemovedFromDate);
		
		$networkLogonRemovedDate = new textbox("networkLogonRemovedDate");
		$networkLogonRemovedDate->setTable("employee");
		$networkLogonRemovedDate->setDataType("date");
		$networkLogonRemovedDate->setRequired(false);
		$networkLogonRemovedDate->setRowTitle("network_Logon_Removed_Date");
		$default->add($networkLogonRemovedDate);
		
		
		$ITPolicySignedDate = new textbox("ITPolicySignedDate");
		$ITPolicySignedDate->setTable("employee");
		$ITPolicySignedDate->setDataType("date");
		$ITPolicySignedDate->setRequired(false);
		$ITPolicySignedDate->setRowTitle("IT_Policy_Signed_Date");
		
		$default->add($ITPolicySignedDate);
		
		
		
		$sapLogonRequired = new radio("sapLogonRequired");
		$sapLogonRequired->setTable("employee");
		$sapLogonRequired->setDataType("number");
		$sapLogonRequired->setRequired(false);
		$sapLogonRequired->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$sapLogonRequired->setValue(0);
		$sapLogonRequired->setRowTitle("sap_Logon_Required");
		$sapSwitch->add($sapLogonRequired);
		
		
		$sapLogonDependency = new dependency();
		$sapLogonDependency->addRule(new rule('sapSwitch', 'sapLogonRequired', 1));
		$sapLogonDependency->setGroup('sap');
		$sapLogonDependency->setShow(true);
		
		$sapLogonRequired->addControllingDependency($sapLogonDependency);
		
		
		
		
		$sapSecurityApprovalDate = new textbox("sapSecurityApprovalDate");
		$sapSecurityApprovalDate->setTable("employee");
		$sapSecurityApprovalDate->setDataType("date");
		$sapSecurityApprovalDate->setRequired(false);
		$sapSecurityApprovalDate->setRowTitle("sap_Security_Approval_Date");
		$sap->add($sapSecurityApprovalDate);
		
		
		$sapLogonGrantedDate = new textbox("sapLogonGrantedDate");
		$sapLogonGrantedDate->setTable("employee");
		$sapLogonGrantedDate->setDataType("date");
		$sapLogonGrantedDate->setRequired(false);
		$sapLogonGrantedDate->setRowTitle("sap_Logon_Granted_Date");
		$sap->add($sapLogonGrantedDate);
		
		$sapLogonRemovedDate = new textbox("sapLogonRemovedDate");
		$sapLogonRemovedDate->setTable("employee");
		$sapLogonRemovedDate->setDataType("date");
		$sapLogonRemovedDate->setRequired(false);
		$sapLogonRemovedDate->setRowTitle("sap_Logon_Removed_Date");
		$sap->add($sapLogonRemovedDate);
		
		
		
		
		
		$PCRequired = new radio("PCRequired");
		$PCRequired->setTable("employee");
		$PCRequired->setDataType("number");
		$PCRequired->setRequired(false);
		$PCRequired->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$PCRequired->setValue(0);
		$PCRequired->setRowTitle("PC_Required");
		$hardwareSwitch->add($PCRequired);
		
		
		$PCDependency = new dependency();
		$PCDependency->addRule(new rule('hardwareSwitch', 'PCRequired', 1));
		$PCDependency->setGroup('hardware');
		$PCDependency->setShow(true);
		
		$PCRequired->addControllingDependency($PCDependency);
		
		
		$DatePCIssued = new textbox("DatePCIssued");
		$DatePCIssued->setTable("employee");
		$DatePCIssued->setDataType("date");
		$DatePCIssued->setRequired(false);
		$DatePCIssued->setRowTitle("Date_PC_Issued");
		$hardware->add($DatePCIssued);
		
		$PCType = new radio("PCType");
		$PCType->setTable("employee");
		$PCType->setDataType("text");
		$PCType->setRequired(false);
		$PCType->setArraySource(array(
			array('value' => 'desktop', 'display' => 'Desktop'),
			array('value' => 'laptop', 'display' => 'Laptop')
		));
		$PCType->setValue('desktop');
		$PCType->setRowTitle("PC_Type");
		$hardware->add($PCType);
		
		$PCModel = new textbox("PCModel");
		$PCModel->setTable("employee");
		$PCModel->setDataType("text");
		$PCModel->setRequired(false);
		$PCModel->setRowTitle("PC_Model");
		$hardware->add($PCModel);
		
		$PCAssetNumber = new textbox("PCAssetNumber");
		$PCAssetNumber->setTable("employee");
		$PCAssetNumber->setDataType("text");
		$PCAssetNumber->setRequired(false);
		$PCAssetNumber->setRowTitle("PC_Asset_Number");
		$hardware->add($PCAssetNumber);
		
		$DateLaptopReturned = new textbox("DateLaptopReturned");
		$DateLaptopReturned->setTable("employee");
		$DateLaptopReturned->setDataType("date");
		$DateLaptopReturned->setRequired(false);
		$DateLaptopReturned->setRowTitle("Date_Laptop_Returned");
		$hardware->add($DateLaptopReturned);
		
		$HomePrinterIssued = new textbox("HomePrinterIssued");
		$HomePrinterIssued->setTable("employee");
		$HomePrinterIssued->setDataType("date");
		$HomePrinterIssued->setRequired(false);
		$HomePrinterIssued->setRowTitle("Home_Printer_Issued");
		$hardware->add($HomePrinterIssued);
		
		$HomePrinterReturned = new textbox("HomePrinterReturned");
		$HomePrinterReturned->setTable("employee");
		$HomePrinterReturned->setDataType("date");
		$HomePrinterReturned->setRequired(false);
		$HomePrinterReturned->setRowTitle("Home_Printer_Returned");
		$hardware->add($HomePrinterReturned);
		
		$MobileDeviceIssued = new textbox("MobileDeviceIssued");
		$MobileDeviceIssued->setTable("employee");
		$MobileDeviceIssued->setDataType("date");
		$MobileDeviceIssued->setRequired(false);
		$MobileDeviceIssued->setRowTitle("Mobile_Device_Issued");
		$hardware->add($MobileDeviceIssued);
		
		$MobileDeviceReturned = new textbox("MobileDeviceReturned");
		$MobileDeviceReturned->setTable("employee");
		$MobileDeviceReturned->setDataType("date");
		$MobileDeviceReturned->setRequired(false);
		$MobileDeviceReturned->setRowTitle("Mobile_Device_Returned");
		$hardware->add($MobileDeviceReturned);
		
		$HomeRouterIssued = new textbox("HomeRouterIssued");
		$HomeRouterIssued->setTable("employee");
		$HomeRouterIssued->setDataType("date");
		$HomeRouterIssued->setRequired(false);
		$HomeRouterIssued->setRowTitle("Home_Router_Issued");
		$hardware->add($HomeRouterIssued);
		
		$HomeRouterReturned = new textbox("HomeRouterReturned");
		$HomeRouterReturned->setTable("employee");
		$HomeRouterReturned->setDataType("date");
		$HomeRouterReturned->setRequired(false);
		$HomeRouterReturned->setRowTitle("Home_Router_Returned");
		$hardware->add($HomeRouterReturned);
		
		
		$this->ITInformationForm->add($switch);
		$this->ITInformationForm->add($default);
		$this->ITInformationForm->add($sapSwitch);
		$this->ITInformationForm->add($sap);
		$this->ITInformationForm->add($hardwareSwitch);
		$this->ITInformationForm->add($hardware);
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	private function defineAssetDataForm()
	{
		$this->assetDataForm  = new form("assetDataForm");
		$this->assetDataForm->showLegend(true);
		
		$default = new group("default");
		
		$companyCarIssuedDate = new textbox("companyCarIssuedDate");
		$companyCarIssuedDate->setTable("employee");
		$companyCarIssuedDate->setDataType("date");
		$companyCarIssuedDate->setRequired(false);
		$companyCarIssuedDate->setRowTitle("company_Car_Issued_Date");
		$default->add($companyCarIssuedDate);
		
		$companyCarRegistration = new textbox("companyCarRegistration");
		$companyCarRegistration->setTable("employee");
		$companyCarRegistration->setDataType("text");
		$companyCarRegistration->setLength(10);
		$companyCarRegistration->setRequired(false);
		$companyCarRegistration->setRowTitle("company_Car_Registration");
		$default->add($companyCarRegistration);
		
		$companyCarContractDistance = new measurement("companyCarContractDistance");
		$companyCarContractDistance->setTable("employee");
		$companyCarContractDistance->setDataType("number");
		$companyCarContractDistance->setLength(6);
		$companyCarContractDistance->setRequired(false);
		$companyCarContractDistance->setRowTitle("company_Car_Contract_Distance");
		$companyCarContractDistance->clearData();
		$companyCarContractDistance->setArraySource(array(
			array('value' => 'M', 'display' => 'Miles'),
			array('value' => 'KM', 'display' => 'KM')
		));
		$default->add($companyCarContractDistance);
		
		
		$companyCarContractReturnDate = new textbox("companyCarContractReturnDate");
		$companyCarContractReturnDate->setTable("employee");
		$companyCarContractReturnDate->setDataType("date");
		$companyCarContractReturnDate->setRequired(false);
		$companyCarContractReturnDate->setRowTitle("company_Car_Contract_Return_Date");
		$default->add($companyCarContractReturnDate);
		
		$companyCarReturnedDate = new textbox("companyCarReturnedDate");
		$companyCarReturnedDate->setTable("employee");
		$companyCarReturnedDate->setDataType("date");
		$companyCarReturnedDate->setRequired(false);
		$companyCarReturnedDate->setRowTitle("company_Car_Returned_Date");
		$default->add($companyCarReturnedDate);
		
		$mobilesPhoneIssuedDate = new textbox("mobilesPhoneIssuedDate");
		$mobilesPhoneIssuedDate->setTable("employee");
		$mobilesPhoneIssuedDate->setDataType("date");
		$mobilesPhoneIssuedDate->setRequired(false);
		$mobilesPhoneIssuedDate->setRowTitle("mobiles_Phone_Issued_Date");
		$default->add($mobilesPhoneIssuedDate);
		
		$mobilesPhoneReturnedDate = new textbox("mobilesPhoneReturnedDate");
		$mobilesPhoneReturnedDate->setTable("employee");
		$mobilesPhoneReturnedDate->setDataType("date");
		$mobilesPhoneReturnedDate->setRequired(false);
		$mobilesPhoneReturnedDate->setRowTitle("mobiles_Phone_Returned_Date");
		$default->add($mobilesPhoneReturnedDate);
		
		$officeKeysIssuedDate = new textbox("officeKeysIssuedDate");
		$officeKeysIssuedDate->setTable("employee");
		$officeKeysIssuedDate->setDataType("date");
		$officeKeysIssuedDate->setRequired(false);
		$officeKeysIssuedDate->setRowTitle("office_Keys_Issued_Date");
		$default->add($officeKeysIssuedDate);
		
		$officeKeysReturnedDate = new textbox("officeKeysReturnedDate");
		$officeKeysReturnedDate->setTable("employee");
		$officeKeysReturnedDate->setDataType("date");
		$officeKeysReturnedDate->setRequired(false);
		$officeKeysReturnedDate->setRowTitle("office_Keys_Returned_Date");
		$default->add($officeKeysReturnedDate);
		
		$gateKeysIssuedDate = new textbox("gateKeysIssuedDate");
		$gateKeysIssuedDate->setTable("employee");
		$gateKeysIssuedDate->setDataType("date");
		$gateKeysIssuedDate->setRequired(false);
		$gateKeysIssuedDate->setRowTitle("gate_Keys_Issued_Date");
		$default->add($gateKeysIssuedDate);
		
		$gateKeysReturnedDate = new textbox("gateKeysReturnedDate");
		$gateKeysReturnedDate->setTable("employee");
		$gateKeysReturnedDate->setDataType("date");
		$gateKeysReturnedDate->setRequired(false);
		$gateKeysReturnedDate->setRowTitle("gate_Keys_Returned_Date");
		$default->add($gateKeysReturnedDate);
		
		
		
		
		$creditCardIssuedDate = new textbox("creditCardIssuedDate");
		$creditCardIssuedDate->setTable("employee");
		$creditCardIssuedDate->setDataType("date");
		$creditCardIssuedDate->setRequired(false);
		$creditCardIssuedDate->setRowTitle("credit_Card_Issued_Date");
		$default->add($creditCardIssuedDate);
		
		$creditCardDetails = new textarea("creditCardDetails");
		$creditCardDetails->setTable("employee");
		$creditCardDetails->setDataType("text");
		$creditCardDetails->setRequired(false);
		$creditCardDetails->setRowTitle("credit_Card_Details");
		$default->add($creditCardDetails);
		
		
		$creditCardReturnedDate = new textbox("creditCardReturnedDate");
		$creditCardReturnedDate->setTable("employee");
		$creditCardReturnedDate->setDataType("date");
		$creditCardReturnedDate->setRequired(false);
		$creditCardReturnedDate->setRowTitle("credit_Card_Returned_Date");
		$default->add($creditCardReturnedDate);
		
		$fuelCardIssuedDate = new textbox("fuelCardIssuedDate");
		$fuelCardIssuedDate->setTable("employee");
		$fuelCardIssuedDate->setDataType("date");
		$fuelCardIssuedDate->setRequired(false);
		$fuelCardIssuedDate->setRowTitle("fuel_Card_Issued_Date");
		$default->add($fuelCardIssuedDate);
		
		$fuelCardReturnedDate = new textbox("fuelCardReturnedDate");
		$fuelCardReturnedDate->setTable("employee");
		$fuelCardReturnedDate->setDataType("date");
		$fuelCardReturnedDate->setRequired(false);
		$fuelCardReturnedDate->setRowTitle("fuel_Card_Returned_Date");
		$default->add($fuelCardReturnedDate);
		
		$permanentExpenseAdvanceIssuedDate = new textbox("permanentExpenseAdvanceIssuedDate");
		$permanentExpenseAdvanceIssuedDate->setTable("employee");
		$permanentExpenseAdvanceIssuedDate->setDataType("date");
		$permanentExpenseAdvanceIssuedDate->setRequired(false);
		$permanentExpenseAdvanceIssuedDate->setRowTitle("permanent_Expense_Advance_Issued_Date");
		$default->add($permanentExpenseAdvanceIssuedDate);
		
		$permanentExpenseAdvanceIssuedDate = new textbox("permanentExpenseAdvanceIssuedDate");
		$permanentExpenseAdvanceIssuedDate->setTable("employee");
		$permanentExpenseAdvanceIssuedDate->setDataType("date");
		$permanentExpenseAdvanceIssuedDate->setRequired(false);
		$permanentExpenseAdvanceIssuedDate->setRowTitle("permanent_Expense_Advance_Issued_Date");
		$default->add($permanentExpenseAdvanceIssuedDate);
		
		$permanentExpenseAdvanceAmmount = new measurement("permanentExpenseAdvanceAmmount");
		$permanentExpenseAdvanceAmmount->setTable("employee");
		$permanentExpenseAdvanceAmmount->setDataType("number");
		$permanentExpenseAdvanceAmmount->setXMLSource("xml/currency.xml");
		$permanentExpenseAdvanceAmmount->setRequired(false);
		$permanentExpenseAdvanceAmmount->setRowTitle("permanent_Expense_Advance_Ammount");
		$default->add($permanentExpenseAdvanceAmmount);
		
		$permanentExpenseAdvanceReturnedDate = new textbox("permanentExpenseAdvanceReturnedDate");
		$permanentExpenseAdvanceReturnedDate->setTable("employee");
		$permanentExpenseAdvanceReturnedDate->setDataType("date");
		$permanentExpenseAdvanceReturnedDate->setRequired(false);
		$permanentExpenseAdvanceReturnedDate->setRowTitle("permanent_Expense_Advance_Retuned_Date");
		$default->add($permanentExpenseAdvanceReturnedDate);
		
		$this->assetDataForm->add($default);
	}
	
	private function defineTrainingForm()
	{
		$this->trainingForm = new form("trainingForm");
		$this->trainingForm->showLegend(true);
		
		$internalCoursesGroup = new multiplegroup("internalCoursesGroup");
		$internalCoursesGroup->setNextAction("training");
		$internalCoursesGroup->setTable("internalCourses");
		$internalCoursesGroup->setTitle("internal_Courses");
		$internalCoursesGroup->setForeignKey("employeeId");
		
		
		$externalCoursesGroup = new multiplegroup("externalCoursesGroup");
		$externalCoursesGroup->setNextAction("training");
		$externalCoursesGroup->setTable("externalCourses");
		$externalCoursesGroup->setTitle("external_Courses");
		$externalCoursesGroup->setForeignKey("employeeId");
		
	//	$default = new group("default");
	//	$default->setBorder(false);
	
		
		
		
		
		$internalCourseDate = new textbox("internalCourseDate");
		$internalCourseDate->setTable("internalCourses");
		$internalCourseDate->setDataType("date");
		$internalCourseDate->setRequired(false);
		$internalCourseDate->setRowTitle("internal_Courses_date");
		$internalCoursesGroup->add($internalCourseDate);
		
		$internalCourseTitle = new textbox("internalCourseTitle");
		$internalCourseTitle->setTable("internalCourses");
		$internalCourseTitle->setDataType("string");
		$internalCourseTitle->setRequired(false);
		$internalCourseTitle->setRowTitle("internal_Courses_title");
		$internalCoursesGroup->add($internalCourseTitle);
		
		$internalCourseTheme = new textbox("internalCourseTheme");
		$internalCourseTheme->setTable("internalCourses");
		$internalCourseTheme->setDataType("string");
		$internalCourseTheme->setRequired(false);
		$internalCourseTheme->setRowTitle("internal_Courses_theme");
		$internalCoursesGroup->add($internalCourseTheme);
		
		
		
		
		$externalCourseDate = new textbox("externalCourseDate");
		$externalCourseDate->setTable("externalCourses");
		$externalCourseDate->setDataType("date");
		$externalCourseDate->setRequired(false);
		$externalCourseDate->setRowTitle("external_Courses_date");
		$externalCoursesGroup->add($externalCourseDate);
		
		$externalCourseTitle = new textbox("externalCourseTitle");
		$externalCourseTitle->setTable("externalCourses");
		$externalCourseTitle->setDataType("string");
		$externalCourseTitle->setRequired(false);
		$externalCourseTitle->setRowTitle("external_Courses_title");
		$externalCoursesGroup->add($externalCourseTitle);
		
		$externalCourseTheme = new textbox("externalCourseTheme");
		$externalCourseTheme->setTable("externalCourses");
		$externalCourseTheme->setDataType("string");
		$externalCourseTheme->setRequired(false);
		$externalCourseTheme->setRowTitle("external_Courses_theme");
		$externalCoursesGroup->add($externalCourseTheme);
		
		
		
		
		
		
		
		$this->trainingForm->add($internalCoursesGroup);
		$this->trainingForm->add($externalCoursesGroup);

	}
		
	private function definePPEandHSEtrainingForm()
	{
		$this->PPEandHSEtrainingForm = new form("PPEandHSEtrainingForm");
		$this->PPEandHSEtrainingForm->showLegend(true);
		
		$default = new group("default");
		$default->setBorder(false);
		$bottom = new group("bottom");
		
			
		$forkliftSwitch = new group("forkliftSwitch");
		$forkliftSwitch->setBorder(false);
		$forkliftGroup = new group("forkliftGroup");
		
		$firstAidSwitch = new group("firstAidSwitch");
		$firstAidSwitch->setBorder(false);
		$firstAidGroup = new group("firstAidGroup");
		
		
		
		
		$nextReviewDateForHSTraining = new textbox("nextReviewDateForHSTraining");
		$nextReviewDateForHSTraining->setTable("employee");
		$nextReviewDateForHSTraining->setDataType("date");
		$nextReviewDateForHSTraining->setRequired(false);
		$nextReviewDateForHSTraining->setRowTitle("next_Review_Date_For_HS_Training");
		$default->add($nextReviewDateForHSTraining);
		
		
		$baseHSTraining = new radio("baseHSTraining");
		$baseHSTraining->setTable("employee");
		$baseHSTraining->setDataType("number");
		$baseHSTraining->setRequired(false);
		$baseHSTraining->setRowTitle("base_HS_Training");
		$baseHSTraining->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$baseHSTraining->setValue(0);
		$default->add($baseHSTraining);
		
		
		$directorsHSTraining = new radio("directorsHSTraining");
		$directorsHSTraining->setTable("employee");
		$directorsHSTraining->setDataType("number");
		$directorsHSTraining->setRequired(false);
		$directorsHSTraining->setRowTitle("directors_HS_Training");
		$directorsHSTraining->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$directorsHSTraining->setValue(0);
		$default->add($directorsHSTraining);
		
		
		
		
		$forkliftTruckLicense = new radio("forkliftTruckLicense");
		$forkliftTruckLicense->setTable("employee");
		$forkliftTruckLicense->setDataType("number");
		$forkliftTruckLicense->setRequired(false);
		$forkliftTruckLicense->setRowTitle("forklift_Truck_License");
		$forkliftTruckLicense->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$forkliftTruckLicense->setValue(0);
		
		$forkliftTruckLicenseDependency = new dependency();
		$forkliftTruckLicenseDependency->addRule(new rule('forkliftSwitch', 'forkliftTruckLicense', 1));
		$forkliftTruckLicenseDependency->setGroup(array('forkliftGroup'));
		$forkliftTruckLicenseDependency->setShow(true);
		
		$forkliftTruckLicense->addControllingDependency($forkliftTruckLicenseDependency);
		
		$forkliftSwitch->add($forkliftTruckLicense);
		
		
		$forkliftTruckLicenseDate = new textbox("forkliftTruckLicenseDate");
		$forkliftTruckLicenseDate->setTable("employee");
		$forkliftTruckLicenseDate->setDataType("date");
		$forkliftTruckLicenseDate->setRequired(false);
		$forkliftTruckLicenseDate->setRowTitle("forklift_Truck_License_Date");
		$forkliftGroup->add($forkliftTruckLicenseDate);
		
		
		
		
		$firstAidTraining = new radio("firstAidTraining");
		$firstAidTraining->setTable("employee");
		$firstAidTraining->setDataType("number");
		$firstAidTraining->setRequired(false);
		$firstAidTraining->setRowTitle("first_Aid_Training");
		$firstAidTraining->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$firstAidTraining->setValue(0);
		
		$firstAidTrainingDependency = new dependency();
		$firstAidTrainingDependency->addRule(new rule('firstAidSwitch', 'firstAidTraining', 1));
		$firstAidTrainingDependency->setGroup(array('firstAidGroup'));
		$firstAidTrainingDependency->setShow(true);
		
		$firstAidTraining->addControllingDependency($firstAidTrainingDependency);
				
		$firstAidSwitch->add($firstAidTraining);
		
		
		
		$firstAidTrainingDate = new textbox("firstAidTrainingDate");
		$firstAidTrainingDate->setTable("employee");
		$firstAidTrainingDate->setDataType("date");
		$firstAidTrainingDate->setRequired(false);
		$firstAidTrainingDate->setRowTitle("first_Aid_Training_Date");
		$firstAidGroup->add($firstAidTrainingDate);
		
		
		
		
		
		$safetyShoes = new radio("safetyShoes");
		$safetyShoes->setTable("employee");
		$safetyShoes->setDataType("number");
		$safetyShoes->setRequired(false);
		$safetyShoes->setRowTitle("safety_Shoes");
		$safetyShoes->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$safetyShoes->setValue(0);
		$bottom->add($safetyShoes);
		
		$safetyGlasses = new radio("safetyGlasses");
		$safetyGlasses->setTable("employee");
		$safetyGlasses->setDataType("number");
		$safetyGlasses->setRequired(false);
		$safetyGlasses->setRowTitle("safety_Glasses");
		$safetyGlasses->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$safetyGlasses->setValue(0);
		$bottom->add($safetyGlasses);
		
		$earDefenders = new radio("earDefenders");
		$earDefenders->setTable("employee");
		$earDefenders->setDataType("number");
		$earDefenders->setRequired(false);
		$earDefenders->setRowTitle("ear_Defenders");
		$earDefenders->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$earDefenders->setValue(0);
		$bottom->add($earDefenders);
		
		$safetyHelmets = new radio("safetyHelmets");
		$safetyHelmets->setTable("employee");
		$safetyHelmets->setDataType("number");
		$safetyHelmets->setRequired(false);
		$safetyHelmets->setRowTitle("safety_Helmets");
		$safetyHelmets->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$safetyHelmets->setValue(0);
		$bottom->add($safetyHelmets);
		
		$highVisJackets = new radio("highVisJackets");
		$highVisJackets->setTable("employee");
		$highVisJackets->setDataType("number");
		$highVisJackets->setRequired(false);
		$highVisJackets->setRowTitle("highVisJackets");
		$highVisJackets->setArraySource(array(
			array('value' => '1', 'display' => 'Yes'),
			array('value' => '0', 'display' => 'No')
		));
		$highVisJackets->setValue(0);
		$bottom->add($highVisJackets);
		
		$this->PPEandHSEtrainingForm->add($default);
		$this->PPEandHSEtrainingForm->add($forkliftSwitch);
		$this->PPEandHSEtrainingForm->add($forkliftGroup);
		$this->PPEandHSEtrainingForm->add($firstAidSwitch);
		$this->PPEandHSEtrainingForm->add($firstAidGroup);
		$this->PPEandHSEtrainingForm->add($bottom);
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
	
}

?>