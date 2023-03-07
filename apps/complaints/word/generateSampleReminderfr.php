<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateSampleReminderfr extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE employee.NTLogon = '" . currentuser::getInstance()->getNTlogon() . "'");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);
		
		$datasetInitiator = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT employee.phone, employee.fax, employee.email, sites.address FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE CONCAT(employee.firstName, ' ', employee.lastName) = '" . $fields['internalSalesName'] . "'");
		$fieldsInitiator = mysql_fetch_array($datasetInitiator);
		//-----------------------------------------------------
		$datasetInvoiceNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaInvoiceNumberDate` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapInvoiceDateResultsArray = array();
		$sapInvoiceNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetInvoiceNumber))
		{
			$sapInvoiceNumberResults = array_push($sapInvoiceNumberResultsArray, $row['scapaInvoiceNumber']);
			
			$sapInvoiceDateResults = array_push($sapInvoiceDateResultsArray, common::transformDateForPHP($row['scapaInvoiceDate']));
		}
		//-----------------------------------------------------
		$datasetOrderNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaOrderNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$orderNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetOrderNumber))
		{
			$sapOrderNumberResults = array_push($orderNumberResultsArray, $row['scapaOrderNumber']);
		}
		//-----------------------------------------------------
		$datasetSAPItem = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapSAPItemResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetSAPItem))
		{
			$sapSAPItemResults = array_push($sapSAPItemResultsArray, $row['sapItemNumber']);
		}
		
		//first we tell php the filename
		$file = './apps/complaints/word/sampleReminderfr.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		//check to make sure "" is shown for date value 0000-00-00 for all dates in DATA
		$fields['openDate'] == "0000-00-00" ? $regDateValue = "" : $regDateValue = common::transformDateForPHP($fields['openDate']);
		
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
		
		// DATA
		$complaintId = $complaintType . "" . $fields['id'];
		$sapName = $fields['sapName'];
		$regDate = $regDateValue;
		
		foreach ($orderNumberResultsArray as $key => $val)
		{
			$sapOrderNumber .= $val . ", ";
		}
		$orderNo = $sapOrderNumber;
		
		$cusCompRef = $fields['customerComplaintRef'];
		
		foreach ($sapInvoiceDateResultsArray as $key => $val)
		{
			if ($val == "0000-00-00" || $val == "30/11/1999")
			{
				$val = "";
			}
			$date .= $val . ", ";
		};
		
		$invoiceDate = $date;
		
		foreach ($sapInvoiceNumberResultsArray as $key => $val)
		{
			$invNum .= $val . ", ";
		};
		
		$invoiceNo = $invNum;
		
		$custONo = $fields['customerItemNumber'];
		$itemDescription = $fields['productDescription'];
		$batchNo = $fields['batchNumber'];
		
		foreach ($sapSAPItemResultsArray as $key => $val)
		{
			$sapSAPItem .= $val . ", ";
		};
		$sapitem = $sapSAPItem;
		
		$quanUComp = $fields['quantityUnderComplaint_quantity'] . " " . $fields['quantityUnderComplaint_measurement'];
		$problemIdenByCus = $fields['problemDescription'];
		$undersigned = $fields['internalSalesName']; //usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName();
		$tel = $fieldsInitiator['phone'];
		$fax = $fieldsInitiator['fax'];
		$email = $fieldsInitiator['email'];
		$address = $fieldsInitiator['address'];
		$date = date("d/m/Y");
		$complaintId2 = $complaintType . "" . $fields['id'];
		$dateDiff = $this->datediff($fields['openDate'], date("Y-m-d")) . " jour(s)";
		
		
		// REPLACE OVERS
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintId),$data);
		$data = str_replace('[[ID2]]',replaceForeignCharsForRTF($complaintId2),$data);
		$data = str_replace('[[SAPNAME]]',replaceForeignCharsForRTF($sapName),$data);
		$data = str_replace('[[REGDATE]]',replaceForeignCharsForRTF($regDate),$data);
		$data = str_replace('[[ORDERNO]]',replaceForeignCharsForRTF($orderNo),$data);
		$data = str_replace('[[CUSCREF]]',replaceForeignCharsForRTF($cusCompRef),$data);
		$data = str_replace('[[INVOICENO]]',replaceForeignCharsForRTF($invoiceNo),$data);
		$data = str_replace('[[CUSTONO]]',replaceForeignCharsForRTF($custONo),$data);
		$data = str_replace('[[INVOICEDATE]]',replaceForeignCharsForRTF($invoiceDate),$data);
		$data = str_replace('[[ITEMDESCRIPTION]]',replaceForeignCharsForRTF($itemDescription),$data);
		$data = str_replace('[[BATCHNO]]',replaceForeignCharsForRTF($batchNo),$data);
		$data = str_replace('[[ITEMSAP]]',replaceForeignCharsForRTF($sapitem),$data);
		$data = str_replace('[[QUANTUCOMP]]',replaceForeignCharsForRTF($quanUComp),$data);
		$data = str_replace('[[PROBLEMIDENBYCUS]]',replaceForeignCharsForRTF($problemIdenByCus),$data);
		$data = str_replace('[[UNDERSIGNED]]',replaceForeignCharsForRTF($undersigned),$data);
		$data = str_replace('[[TEL]]',replaceForeignCharsForRTF($tel),$data);
		$data = str_replace('[[FAX]]',replaceForeignCharsForRTF($fax),$data);
		$data = str_replace('[[EMAIL]]',replaceForeignCharsForRTF($email),$data);
		$data = str_replace('[[ADDRESS]]',replaceForeignCharsForRTF($address),$data);
		$data = str_replace('[[DATE]]',replaceForeignCharsForRTF($date),$data);
		$data = str_replace('[[DATEDIFF]]',replaceForeignCharsForRTF($dateDiff),$data);

		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/sampleRem-fr' . $fields['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		// Close the File
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/sampleRem-fr" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("Sample Reminder Created");
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'sampleRem'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'sampleRem', '" . common::nowDateForMysql() . "', 'fr')");
		
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
	
	public function datediff($datefrom, $dateto) 
	{
		$datefrom = strtotime($datefrom, 0);
		$dateto = strtotime($dateto, 0);
		
		$difference = $dateto - $datefrom; // Difference in seconds
		
		$days_difference = floor($difference / 86400);
		$weeks_difference = floor($days_difference / 7); // Complete weeks
		$first_day = date("w", $datefrom);
		$days_remainder = floor($days_difference % 7);
		$odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
		if ($odd_days > 7) { // Sunday
		$days_remainder--;
		}
		if ($odd_days > 6) { // Saturday
		$days_remainder--;
		}
		$datediff = ($weeks_difference * 5) + $days_remainder;
		
		return $datediff;
	}
	
}
	

?>