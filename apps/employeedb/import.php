<?php

require 'lib/employee.php';

class import extends page
{
	private $fieldMap = array();
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('HR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/hr/xml/menu.xml");
		
		$file = $this->getRoot() . "/apps/hr/merged.csv";	

		$data = file($file);
		
		echo "<pre>";
		
		$records = 0;
		
		
		
		
		$this->fieldMap[0] = "id";
		
		$dummy = new employee();
		$this->addFieldsFromForm($dummy->personalDetailsForm);
		$this->addFieldsFromForm($dummy->jobRoleForm);
		$this->addFieldsFromForm($dummy->employmentHistoryForm);
		//$this->addFieldsFromForm($dummy->ITInformationForm);
		$this->addFieldsFromForm($dummy->assetDataForm);
		//$this->addFieldsFromForm($dummy->trainingForm);
		$this->addFieldsFromForm($dummy->PPEandHSEtrainingForm);
		
		//var_dump($this->fieldMap);
		
		foreach($data as $line)
		{
			$array = explode(",", $line);
			$records++;
			
			if ($records > 1)
			{
				
			
			$id = trim($array[0]);
			
			if ($id != "")
			{
				$id =  str_pad($id, 5, "0", STR_PAD_LEFT);
				
				//echo "id: " .$id. "\n";
				
				
				
				$fields = array();
				
				for ($i=1; $i < count($array); $i++)
				{
					$value = trim($array[$i]);
					
					if (isset($this->fieldMap[$i]) && $this->fieldMap[$i] != "NULL" && $value != "")
					{
						if (preg_match("/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/",$value))
						{
							$fields[] = "`" . $this->fieldMap[$i] . "`='" . page::transformDateForMYSQL($value) . "'";
						}
						else 
						{
							$fields[] = "`" . $this->fieldMap[$i] . "`='" . $value . "'";
						}
						
						
					}
				}
				
				
			/*	$query = "UPDATE employee SET " . implode(",", $fields) . " WHERE id=$id";
				
				mysql::getInstance()->selectDatabase("HR")->Execute($query);
				echo $query . "\n";
			
				
				$query = "INSERT INTO log (`employeeId`, `NTLogon`, `action`, `logDate`) VALUES ($id, '-', 'Data Import', '" . common::nowDateTimeForMysql() . "')";
				mysql::getInstance()->selectDatabase("HR")->Execute($query);
			*/	
				//echo 
				
				
				/*$id = str_pad($id, 5, "0", STR_PAD_LEFT);
				
				$dataset = mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("SELECT * FROM employee WHERE id=" . $id));
				
				if (mysql_num_rows($dataset) == 1)
				{
					$row = mysql_fetch_array($dataset);
					
					if ($row['firstName'] != $array[1] || $row['lastName'] != $array[2])
					{
						echo "Name mismatch for " . $id  . ", looking for " . $array[1] . " " . $array[2] . ", found ". $row['firstName'] . " " . $row['lastName'] . "\n";
					}
				}
				else 
				{
					echo "no record found for $id - " . $array[5] . "\n";
				}*/
			}
			else 
			{
				echo "id: INSERT NEW RECORD for " . $array[5] . "\n";
				
				$fields = array();
				$values = array();
				
				for ($i=1; $i < count($array); $i++)
				{
					$value = trim($array[$i]);
					
					if (isset($this->fieldMap[$i]) && $this->fieldMap[$i] != "NULL" && $value != "")
					{
						$fields[] = "`" . $this->fieldMap[$i] . "`";
						
						if (preg_match("/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/",$value))
						{
							$values[] = "'" . page::transformDateForMYSQL($value) . "'";
						}
						else 
						{
							$values[] = "'" . $value . "'";
						}
						
						//echo $this->fieldMap[$i] . ": " . $value . "\n";
					}
				}
			/*	
				
				$query = "INSERT INTO employee (" . implode(",", $fields) . ") VALUES (" . implode(",", $values) . ")";
				
				mysql::getInstance()->selectDatabase("HR")->Execute($query);
				
				echo $query . "\n";
				
				
				$query = "SELECT * FROM employee ORDER BY id DESC";
				$dataset = mysql::getInstance()->selectDatabase("HR")->Execute($query);
				
				$row = mysql_fetch_array($dataset);
				
				$query = "INSERT INTO log (`employeeId`, `NTLogon`, `action`, `logDate`) VALUES (" . $row['id'] . ", '-', 'Data Import', '" . common::nowDateTimeForMysql() . "')";
				mysql::getInstance()->selectDatabase("HR")->Execute($query);
				
				*/
			}
			/*
			for ($i=1; $i < count($array); $i++)
			{
				$value = trim($array[$i]);
				
				if (isset($this->fieldMap[$i]) && $value != "")
				{
					echo $this->fieldMap[$i] . ": " . $value . "\n";
				}
			}
			

				echo "\n\n";
			*/
			}
		}
		
		echo "\n\nTotal of $records records</pre>";
		
		
		if (isset($GLOBALS['runtimeErrorLog']))
	        {
	        	echo "Runtime ERROR Log: \n\n". $GLOBALS['runtimeErrorLog'] . "\n\n";
	        }
	        
	        if (isset($GLOBALS['runtimeDebug']))
	        {
	        	echo "Runtime DEBUG: \n\n". $GLOBALS['runtimeDebug'] . "\n\n";
	        }
	        	
	        
	        echo "SQL DEBUG: \n\n" . $GLOBALS['sql_debug'] . "\n\n";
		
		
		exit();
		
		
		
		
	/*	
		
		while ($row = mysql_fetch_array($dataset))
		{
			// bla
		}
		
		$file = $this->getRoot() . "/apps/hr/uk.txt";	

		$data = file($file);
		
		foreach($data as $line)
		{
			$array = explode("\t", $line);
			
			
			
			mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("INSERT INTO employee (`firstName`, `lastName`, `middleName1`, `name`, `nationalInsuranceNumber`, `costCentre`, `jobType`, `jobLength`, `gender`, `startDate`, `employmentStartDate`, `dateOfBirth`,`bonusPlan`, `pensionScheme`, `carLevel`, `companyCar`, `carFullyFinanced`, `address1`, `address2`, `address3`, `address4`, `postcode`, `country`) VALUES ('%s', '%s', '%s', '%s', '%s', %u, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %u, %u, %u, '%s', '%s', '%s', '%s', '%s', '%s')",
				page::xmlentities(ucfirst(strtolower($array[1]))), //firstName
				page::xmlentities(ucfirst(strtolower($array[3]))), //lastName
				page::xmlentities(ucfirst(strtolower($array[2]))), //middleName1
				page::xmlentities(ucfirst(strtolower($array[1])) . " " . ucfirst(strtolower($array[3]))), //name
				$array[4], //nationalInsuranceNumber
				$array[5], //costCentre
				trim($array[6]) == 'full time' ? 'full' : 'part', //jobType
				trim($array[7]) == 'permanent' ? 'permanent' : 'temp', //jobLength
				trim($array[8]) == 'male' ? 'male' : 'female', //gender
				$this->transformDateForMYSQL($array[9]), //startDate
				$this->transformDateForMYSQL($array[9]), //employmentStartDate
				$this->transformDateForMYSQL($array[10]), //dateOfBirth
				trim($array[11]) == '' ? 'none' : strtolower($array[11]), //bonusPlan
				trim($array[13]) == 'N/A' ? 'none' : $array[13], //pensionScheme
				trim($array[14]) == '' ? 0 : $array[14], //carLevel
				trim($array[14]) == '' ? 0 : 1, //companyCar
				$array[15] == 'Y' ? 1 : 0, //carFullyFinanced
				isset($array[17]) ? trim($array[17]) : '', //address1
				isset($array[18]) ? trim($array[18]) : '', //address2
				isset($array[19]) ? trim($array[19]) : '', //address3
				isset($array[20]) ? trim($array[20]) : '', //address4
				isset($array[21]) ? trim($array[21]) : '', //postcode
				'United Kingdom' //country	
			));
			
			
			
		}
		
		
		
		$file = $this->getRoot() . "/apps/hr/fr.txt";	

		$data = file($file);
		
		foreach($data as $line)
		{
			$array = explode("\t", $line);
			
			
			$department = "";
			
			switch (trim($array[22]))
			{
				case 'FACT':
					$department = 'Factory';
					break;
					
				case 'GEAD':
					$department = 'HR';
					break;
					
				case 'RD':
					$department = 'R&amp;D';
					break;
					
				case 'SALE':
					$department = 'Sales';
					break;
			}
			
			
			mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("INSERT INTO employee (`firstName`, `lastName`, `name`, `nationalInsuranceNumber`, `dateOfBirth`, `gender`,`nationality`,`address1`,`address2`,`postcode`,`homeTelephone`,`country`,`jobType`,`jobLength`,`jobTitle`,`workLocation`,`payrollLocation`,`personnelFile`,`SETResponsible`, `RMTResponsible`,`managerResponsible`,`department`,`costCentre`,`noticeCompany`,`noticeEmployee`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				page::xmlentities(trim(ucfirst(strtolower($array[1])))), //firstName
				page::xmlentities(trim(ucfirst(strtolower($array[0])))), //lastName
				page::xmlentities(trim(ucfirst(strtolower($array[1]))) . " " . trim(ucfirst(strtolower($array[0])))), //name
				$array[2], //nationalInsuranceNumber
				$this->transformDateForMYSQL($array[3]), //dateOfBirth
				$array[4] == 'male' ? 'male' : 'female', //gender`,`nationality`,`address1`,`address2`,`postcode`,`homeTelephone`,`country`,`jobType`,`jobTitle`,`workLocation`,`payrollLocation`,`workLocation`
				$array[6], //nationality
				trim($array[7]), //address1
				trim($array[8]), //address2
				//trim($array[17]), //address3
				//trim($array[18]), //address4
				trim($array[9]), //postcode
				trim($array[10]), //homeTelephone
				'France', //country	
				$array[12] == '100%' ? 'full' : 'part', //jobType
				$array[12] == '100%' ? 'permanent' : '', //jobLength
				trim($array[15]), //jobTitle
				$array[16], //workLocation
				$array[17], //payrollLocation
				
				$array[18], //personnelFile
				$array[19], //SETResponsible
				$array[20], //RMTResponsible
				$array[21], //managerResponsible
				$department, //department
				$array[24], //costCentre
				trim($array[25]) == '3 months' ? '12' : '8', //noticeCompany
				trim($array[26]) == '3 months' ? '12' : '8' //noticeEmployee
				//$array[16] //workLocation
			
				
			));
			
		}
		
		
		$file = $this->getRoot() . "/apps/hr/italy.txt";	

		$data = file($file);
		
		foreach($data as $line)
		{
			$array = explode("\t", $line);
			
			$department = "";
			
			switch (trim($array[19]))
			{
				case 'Factory Direct':
					$department = 'Factory';
					break;
					
				case 'Maintenance':
					$department = 'Maintenance';
					break;
					
				case 'Operations':
					$department = 'Operations';
					break;
					
				case 'Quality':
					$department = 'Quality';
					break;
				
				case 'Purchasing/planning':
					$department = 'Purchasing/planning';
					break;
						
				case 'Warehouse':
					$department = 'Warehouse';
					break;
					
				case 'Sales':
					$department = 'Sales';
					break;
					
				case 'Customer  Care':
					$department = 'Customer Care';
					break;
					
				case 'R&D':
					$department = 'R&amp;D';
					break;
					
				case 'G&A':
					$department = 'HR';
					break;
			}
			
			mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("INSERT INTO employee (`firstName`, `lastName`, `name`, `nationalInsuranceNumber`, `dateOfBirth`, `gender`,`nationality`,`address1`,`address2`,`address3`,`postcode`,`homeTelephone`,`country`,`jobType`,`jobLength`,`employmentStartDate`,`startDate`,`workLocation`,`payrollLocation`,`personnelFile`,`SETResponsible`, `RMTResponsible`,`managerResponsible`,`department`,`costCentre`,`bonusPlan`, `carLevel`, `companyCar`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				page::xmlentities(trim(ucfirst(strtolower($array[3])))), //firstName
				page::xmlentities(trim(ucfirst(strtolower($array[2])))), //lastName
				page::xmlentities(trim(ucfirst(strtolower($array[3]))) . " " . trim(ucfirst(strtolower($array[2])))), //name
				'', //$array[2], //nationalInsuranceNumber
				$this->transformDateForMYSQL($array[4]), //dateOfBirth
				$array[5] == 'Male' ? 'male' : 'female', //gender`,`nationality`,`address1`,`address2`,`postcode`,`homeTelephone`,`country`,`jobType`,`jobTitle`,`workLocation`,`payrollLocation`,`workLocation`
				$array[6], //nationality
				trim($array[7]), //address1
				trim($array[8]), //address2
				trim($array[9]), //address3
				//trim($array[18]), //address4
				trim($array[11]), //postcode
				'',//trim($array[10]), //homeTelephone
				'Italy', //country	
				$array[17] == '100%' ? 'full' : 'part', //jobType
				$array[17] == '100%' ? 'permanent' : '', //jobLength
				//trim($array[13]), //jobTitle
				$this->transformDateForMYSQL(trim($array[13])),
				$this->transformDateForMYSQL(trim($array[13])),
				'Ghislarengo', //workLocation
				'Ghislarengo', //payrollLocation
				
				'Ghislarengo', //personnelFile
				$array[22], //SETResponsible
				$array[21], //RMTResponsible
				$array[20], //managerResponsible
				$department, //department
				'',//$array[13] //costCentre
				isset($array[24]) && trim($array[23]) != '' ?  strtolower($array[23]) : 'none', //bonusPlan
				isset($array[24]) && trim($array[24]) != '' ? $array[24] : 0, //carLevel
				isset($array[24]) && trim($array[24]) != '' ? 1 : 0 //companyCar
				//trim($array[25]) == '3 months' ? '12' : '8', //noticeCompany
				//trim($array[26]) == '3 months' ? '12' : '8' //noticeEmployee
			));
		}
		
		
		
		
		$file = $this->getRoot() . "/apps/hr/swiss.txt";	

		$data = file($file);
		
		foreach($data as $line)
		{
			$array = explode("\t", $line);
			
			$department = "";
			
			switch (trim($array[9]))
			{
				case 'Fertigung II':
				case 'Fertigung I':
					$department = 'Factory';
					break;

					
				case 'Produktionsleitung':
					$department = 'Operations';
					break;
					
				case 'Qualitätssicherung':
					$department = 'Quality';
					break;
				
				case 'Planung':
				case 'Einkauf':
					$department = 'Purchasing/planning';
					break;
						
				case 'Mech. Werkstatt':
				case 'Elektro Werkstatt':
					$department = 'Warehouse';
					break;
					
				case 'Finanz und Rechnungswesen':
					$department = 'Finance';
					break;
					
			}
			
			
			mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("INSERT INTO employee (`costCentre`,`firstName`, `lastName`, `name`,`gender`, `employmentStartDate`, `startDate`, `jobType`, `jobLength`,`department`, `jobTitle`,`workLocation`,`payrollLocation`,`personnelFile`) VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s','%s','%s','%s','%s')",
				trim($array[8]), //costCentre	
				page::xmlentities(trim(ucfirst(strtolower($array[3])))), //firstName
				page::xmlentities(trim(ucfirst(strtolower($array[2])))), //lastName
				page::xmlentities(trim(ucfirst(strtolower($array[3])))  . " " . trim(ucfirst(strtolower($array[2])))), //lastName
				//$this->transformDateForMYSQL($array[4]), //dateOfBirth
				$array[1] == 'Male' ? 'male' : 'female', //gender
				$this->transformDateForMYSQL(trim($array[4])),
				$this->transformDateForMYSQL(trim($array[4])),
				//$array[5], //nationality
				$array[7] == '100' ? 'full' : 'part', //jobType
				$array[6] == 'Permanent' ? 'permanent' : 'temp', //jobLength
				$department,
				trim($array[10]),
				//$array[2], //nationalInsuranceNumber
				'Rorschach', //workLocation
				'Rorschach', //payrollLocation
				'Rorschach' //personnelFile
		
			));
			
		}
		
		$dataset = mysql::getInstance()->selectDatabase("HR")->Execute("SELECT * FROM employee");
		
		while ($row = mysql_fetch_array($dataset))
		{
			mysql::getInstance()->selectDatabase("HR")->Execute(sprintf("INSERT INTO log (employeeId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
				$row['id'],
				'-',
				'Employee Imported',
				common::nowDateTimeForMysql()
			));
		}
		
		$this->output('./apps/hr/xsl/summary.xsl');*/
	}
	
	
	private function addFieldsFromForm($form)
	{
		$groups = $form->getGroupNames();
		
		for ($i=0; $i < count($groups); $i++)
		{
			if (get_class($form->getGroup($groups[$i])) == 'group')
			{
				foreach ($form->getGroup($groups[$i])->getAllControls() as $control)
				{
					switch (get_class($control))
					{
						case 'attachment':
							
							break;
							
						case 'measurement':
							
							$this->fieldMap[] = $control->getName() . "_quantity";
							$this->fieldMap[] = $control->getName() . "_measurement";
							break;
							
						default:
							
							$this->fieldMap[] = $control->getName();
							
							//$results->addColumn(new column("employee.`" . $control->getName() . "`", $control->getName(), $control->getRowTitle(), true));						
					}
				}
			}
			else
			{
				// loop through controls
				foreach($form->getGroup($groups[$i])->getAllControls(0) as $controlKey => $controlValue)
				{
					$this->fieldMap[] = "NULL";
				}
			}
		}
	}
	
}

?>