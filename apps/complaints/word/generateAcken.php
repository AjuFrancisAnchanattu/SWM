<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateAcken extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE employee.NTLogon = '" . $fields['owner']/*currentuser::getInstance()->getNTlogon()*/ . "'");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);
		
		$datasetInitiator = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT employee.phone, employee.fax, employee.email, sites.address FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE CONCAT(employee.firstName, ' ', employee.lastName) = '" . $fields['internalSalesName'] . "'");
		$fieldsInitiator = mysql_fetch_array($datasetInitiator);
		
		$datasetSAPItem = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapSAPItemResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetSAPItem))
		{
			$sapSAPItemResults = array_push($sapSAPItemResultsArray, $row['sapItemNumber']);
		}
		
		$datasetOrderNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaOrderNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		$sapOrderNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetOrderNumber)) 
		{
			$sapOrderNumberResults = array_push($sapOrderNumberResultsArray, $row['scapaOrderNumber']);	
		}
		
//		$datasetInvoiceNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaInvoiceNumberDate` WHERE complaintId = '" . $_REQUEST['id'] . "'");
//		while ($row = mysql_fetch_array($datasetInvoiceNumber))
//		{
//			$sapInvoiceNumberResults = $sapInvoiceNumberResults + $row['scapaInvoiceNumber'];
//			$sapInvoiceDateResults = $sapInvoiceDateResults + $row['scapaInvoiceDate'];
//		}

//new bit
		$datasetInvoiceNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaInvoiceNumberDate` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapInvoiceDateResultsArray = array();
		$sapInvoiceNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetInvoiceNumber))
		{
			$sapInvoiceNumberResults = array_push($sapInvoiceNumberResultsArray, $row['scapaInvoiceNumber']);
			
			$sapInvoiceDateResults = array_push($sapInvoiceDateResultsArray, common::transformDateForPHP($row['scapaInvoiceDate']));
		}
//		new bit ends
		
		//first we tell php the filename
		$file = './apps/complaints/word/ack-enTemplate.rtf';
		
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
		
		foreach ($sapOrderNumberResultsArray as $key => $val)
		{
			$orderNo .=$val . ", ";
		}
		$scOrderNo = $orderNo;
		
		$cusCompRef = $fields['customerComplaintRef'];
		
		foreach ($sapInvoiceDateResultsArray as $key => $val)
		{
			$invoiceDate .= $val . " ";
		};
		
		$scInvoiceDate = $invoiceDate;
		
		foreach ($sapInvoiceNumberResultsArray as $key => $val)
		{
			$invoiceNum .= $val . ", ";
		};
		
		$scInvoiceNo = $invoiceNum;
		
//		$scInvoiceNo = $sapInvoiceNumberResults;
//		$scInvoiceDate = $sapInvoiceDateResults;
		$productDesc = $fields['productDescription'];
		$batchNo = $fields['batchNumber'];
		
		foreach ($sapSAPItemResultsArray as $key => $val)
		{
			$sapSAPItem .= $val . ", ";
		};
		$sapItemSap = $sapSAPItem;
		
		$quanUComp = $fields['quantityUnderComplaint_quantity'] . " " . $fields['quantityUnderComplaint_measurement'];
		$problemIden = $fields['problemDescription'];
		$comments = "";
		$undersigned = $fields['internalSalesName']; //usercache::getInstance()->get($fields['owner'])->getName();
		$tel = $fieldsInitiator['phone'];
		$fax = $fieldsInitiator['fax'];
		$email = $fieldsInitiator['email'];
		$address = $fieldsInitiator['address'];
		$date = date("d/m/Y");
		$requestedAction = $fields['actionRequested'];
		$salesContainmentAction = $fields['salesContainmentActions'];
		
		
		// REPLACE OVERS
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintId),$data);
		$data = str_replace('[[SAPNAME]]',replaceForeignCharsForRTF($sapName),$data);
		$data = str_replace('[[REGDATE]]',replaceForeignCharsForRTF($regDate),$data);
		$data = str_replace('[[SCORDERNO]]',replaceForeignCharsForRTF($scOrderNo),$data);
		$data = str_replace('[[CUSCOMPREF]]',replaceForeignCharsForRTF($cusCompRef),$data);
		$data = str_replace('[[SCINVOICENO]]',replaceForeignCharsForRTF($scInvoiceNo),$data);
		$data = str_replace('[[SCINVOICEDATE]]',replaceForeignCharsForRTF($scInvoiceDate),$data);
		$data = str_replace('[[PRODUCTDESC]]',replaceForeignCharsForRTF($productDesc),$data);
		$data = str_replace('[[BATCHNO]]',replaceForeignCharsForRTF($batchNo),$data);
		$data = str_replace('[[SAPITEMSAP]]',replaceForeignCharsForRTF($sapItemSap),$data);
		$data = str_replace('[[QUANUCOMP]]',replaceForeignCharsForRTF($quanUComp),$data);
		$data = str_replace('[[PROBLEMIDEN]]',replaceForeignCharsForRTF($problemIden),$data);
		$data = str_replace('[[COMMENTS]]',replaceForeignCharsForRTF($comments),$data);
		$data = str_replace('[[UNDERSIGNED]]',replaceForeignCharsForRTF($undersigned),$data);
		$data = str_replace('[[TEL]]',replaceForeignCharsForRTF($tel),$data);
		$data = str_replace('[[FAX]]',replaceForeignCharsForRTF($fax),$data);
		$data = str_replace('[[EMAIL]]',replaceForeignCharsForRTF($email),$data);
		$data = str_replace('[[ADDRESS]]',replaceForeignCharsForRTF($address),$data);
		$data = str_replace('[[DATE]]',replaceForeignCharsForRTF($date),$data);
		$data = str_replace('[[ACTIONREQUESTED]]',replaceForeignCharsForRTF($requestedAction),$data);
		$data = str_replace('[[SALESCONTAINMENTACTION]]',replaceForeignCharsForRTF($salesContainmentAction),$data);

		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/ack-en' . $fields['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		// Close the File
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/ack-en" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("Acknowledgement Created");
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'ack'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'ack', '" . common::nowDateForMysql() . "', 'en')");
		
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