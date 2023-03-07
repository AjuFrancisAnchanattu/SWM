<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateSupplierLetterit extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		// Open the database connections.
		// Complaints Database
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);

		// Employee database
		$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTLogon() . "'");
		$fieldsEmp = mysql_fetch_array($datasetEmp);
		
		$datasetAddress = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT sites.address FROM employee, sites WHERE employee.site = sites.name AND NTLogon = '" . currentuser::getInstance()->getNTLogon() . "'");
		$fieldsAddress = mysql_fetch_array($datasetAddress);
		
		// SAP Database
		$datasetCust = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM `customer` WHERE `id` = '" . $fields['sp_sapSupplierNumber'] . "'");
		$fieldCust = mysql_fetch_array($datasetCust);
	
		// Invoice Database
		$datasetInvoice = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM scapaInvoiceNumberDate WHERE scapaInvoiceNumberDate.complaintId  ='" . $_REQUEST['id'] . "'");
		
		//first we tell php the filename
		$file = './apps/complaints/word/supplierLetter-itTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
			
		//get the complaint type into 1 / 2 letter format
		$cType = $fields['typeOfComplaint'];

		if($cType == "hs")
		{
			$complaintType = "HS";
		}
		elseif ($cType == "environment")
		{
			$complaintType = "EV";
		}
		elseif ($cType == "quality")
		{
			$complaintType = "Q";
		}
		elseif ($cType == "customer_complaint")
		{
			$complaintType = "C";
		}
		elseif ($cType == "supplier_complaint")
		{
			$complaintType = "SC";
		}
		elseif ($cType == "survey_scorecard")
		{
			$complaintType = "SS";
		}

		// DATA > Retrieves data from the database query, and adds it into a variable.
		$sapCustName = $fieldCust['name'];
		$sapCustEmail = $fieldCust['emailAddress'];
		$sapCustNumber = $fields['sp_sapSupplierNumber'];
		$sapCustAddress = $fieldCust['address'] . " \par " . $fieldCust['city'] . " \par " . $fieldCust['postcode'];
		$complaintNumber = $complaintType . $fields['id'];
		$acknowledgementNumber = $fields['sp_acknowledgementNumber'];
		$batchNumber = $fields['batchNumber'];
		$productDescription = $fields['productDescription'];
		$sapItemNumbers = $fields['sapItemNumbers'];
		$quantityUnderComplaint = $fields['quantityUnderComplaint_quantity']." ".$fields['quantityUnderComplaint_measurement'];
		$dateNow = date("d/m/Y");
		$complaintValueQ = $fields['complaintValue_quantity'];
		$complaintValueM = $fields['complaintValue_measurement'];
		$actionRequested = $fields['actionRequested'];
		$address = $fieldsAddress['address'];
		$matThick = $fields['dimensionThickness_quantity'] . $fields['dimensionThickness_measurement'];
		$matWidth = $fields['dimensionWidth_quantity'] . $fields['dimensionWidth_measurement'];
		$matLength = $fields['dimensionLength_quantity'] . $fields['dimensionLength_measurement'];
		$supplierRefNo = $fields['customerComplaintRef'];
		$supplierProductDescription = $fields['sp_supplierProductDescription'];
		
		// Dates
		$fields['customerComplaintDate'] == "0000-00-00" ?  $registrationDate = "" : $registrationDate = common::transformDateForPHP($fields['customerComplaintDate']);
		$fields['sp_sampleSentDate'] == "0000-00-00" ? $sampleSentDate = "" : $sampleSentDate = common::transformDateForPHP($fields['sp_sampleSentDate']);
		
		// automatic fields
		$userName = $fieldsEmp['firstName'] . " " . $fieldsEmp['lastName'];
		$userTel = $fieldsEmp['phone'];
		$userFax = $fieldsEmp['fax'];
		$userEmail = $fieldsEmp['email'];
		
		// Invoice number and dates.
		$i=0;
		while ($row = mysql_fetch_array($datasetInvoice)) 
		{
			$row['scapaInvoiceDate'] == "0000-00-00" ?  $scapaInvoiceDate = "" : $scapaInvoiceDate = common::transformDateForPHP($row['scapaInvoiceDate']);
			$i==0 ? $invoiceRow[$i] = $row['scapaInvoiceNumber'] . " - " . $scapaInvoiceDate : $invoiceRow[$i] = " \par " . $row['scapaInvoiceNumber'] . " - " . $scapaInvoiceDate;
			$invoiceDetails = $invoiceDetails . $invoiceRow[$i];
			$i++;
		}
		
		
		//Stuff Not in the Database
/*
		$internalComplaintReference = $('');
		$materialValue = $('');
		$materialValueCurrency = $('');
		$otherDefectsFound = $('');
		$material = $('');
		$quantity = $('');
		$value = $('');
*/	


		
		// REPLACE OVERS > Replaces the [[*]] field inthe RTF Docment with the relevent variable.	
		$data = str_replace('[[D]]',replaceForeignCharsForRTF($dateNow),$data);
		$data = str_replace('[[C]]',replaceForeignCharsForRTF($userName),$data);
		$data = str_replace('[[CE]]',replaceForeignCharsForRTF($userEmail),$data);
		$data = str_replace('[[CF]]',replaceForeignCharsForRTF($userFax),$data);
		$data = str_replace('[[CT]]',replaceForeignCharsForRTF($userTel),$data);		
		$data = str_replace('[[SC]]',replaceForeignCharsForRTF($sapCustName),$data);
		$data = str_replace('[[SCN]]',replaceForeignCharsForRTF($sapCustNumber),$data);
		$data = str_replace('[[SCE]]',replaceForeignCharsForRTF($sapCustEmail),$data);
		$data = str_replace('[[SCA]]',replaceForeignCharsForRTF($sapCustAddress),$data);
		$data = str_replace('[[THICK]]',replaceForeignCharsForRTF($matThick),$data);
		$data = str_replace('[[WIDTH]]',replaceForeignCharsForRTF($matWidth),$data);
		$data = str_replace('[[LENGTH]]',replaceForeignCharsForRTF($matLength),$data);
		$data = str_replace('[[SRN]]',replaceForeignCharsForRTF($supplierRefNo),$data);
		
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintNumber),$data);		
		$data = str_replace('[[RD]]',replaceForeignCharsForRTF($registrationDate),$data);		
		$data = str_replace('[[AN]]',replaceForeignCharsForRTF($acknowledgementNumber),$data);
		$data = str_replace('[[BN]]',replaceForeignCharsForRTF($batchNumber),$data);
		$data = str_replace('[[PD]]',replaceForeignCharsForRTF($productDescription),$data);
		$data = str_replace('[[SPD]]',replaceForeignCharsForRTF($supplierProductDescription),$data);
		$data = str_replace('[[SIN]]',replaceForeignCharsForRTF($sapItemNumbers),$data);
		$data = str_replace('[[QUC]]',replaceForeignCharsForRTF($quantityUnderComplaint),$data);
		$data = str_replace('[[SSD]]',replaceForeignCharsForRTF($sampleSentDate),$data);		
		$data = str_replace('[[AR]]',replaceForeignCharsForRTF($actionRequested),$data);
		$data = str_replace('[[A]]',replaceForeignCharsForRTF($address),$data);
		$data = str_replace('[[CVQ]]',replaceForeignCharsForRTF($complaintValueQ),$data);
		$data = str_replace('[[CVM]]',replaceForeignCharsForRTF($complaintValueM),$data);
		$data = str_replace('[[INV]]',replaceForeignCharsForRTF($invoiceDetails),$data);
				
		// Stuff not in Database!
		$data = str_replace('[[ICR]]',replaceForeignCharsForRTF($internalComplaintReference),$data);
		$data = str_replace('[[ODF]]',replaceForeignCharsForRTF($otherDefectsFound),$data);
		$data = str_replace('[[M]]',replaceForeignCharsForRTF($material),$data);
		$data = str_replace('[[Q]]',replaceForeignCharsForRTF($quantity),$data);
		$data = str_replace('[[V]]',replaceForeignCharsForRTF($value),$data);
		$data = str_replace('[[MVQ]]',replaceForeignCharsForRTF($materialValue),$data);
		$data = str_replace('[[MVM]]',replaceForeignCharsForRTF($materialValueCurrency),$data);
		
		
		
		
		// close file 
		fclose($fp);
		
		// print file contents 
		//print "The data in the file is \"".$data."\"";		
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/supplierLetter-it' . $fields['id'] .  '.rtf';				
		
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/supplierLetter-it" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("Supplier Letter Created");
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'supplierLetter'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'supplierLetter', '" . common::nowDateForMysql() . "', 'it')");
		
		page::redirect("/apps/complaints/#documents");		
	}
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
		$_REQUEST['id'],
		currentuser::getInstance()->getNTLogon(),
		$action,
		common::nowDateTimeForMysql()
		));
	}
	
}
?>