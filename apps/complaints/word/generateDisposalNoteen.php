<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateDisposalNoteen extends page
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
		
		$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `address`, `city`, `postcode` FROM `customer` WHERE name = '" . $fields['sapName'] . "'");
		$fieldsSAP = mysql_fetch_array($datasetSAP);
		
		$datasetSAPItem = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		while ($row = mysql_fetch_array($datasetSAPItem))
		{
			$sapItemNumberResults = $sapItemNumberResults + $row['sapItemNumber'];
		}
		
		
		//first we tell php the filename
		$file = './apps/complaints/word/disposalNote-enTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		
		// DATA
		$complaintId = $fields['id'];
		$date = date("d/m/Y");
		$custName = $fields['sapName'];
		$custAccount = "";
		$customerAddress = $fieldsSAP['address'] . ", " . $fieldsSAP['city'] . ", " . $fieldsSAP['postcode'];
		$custCName = "";
		$CusOrNo = "";
		$scapaOrNo = "";
		$scapaInvNo = "";
		$noDisDate = "";
		$productDescription = $fields['productDescription'];
		$supPrRef = "";
		$noSAPPr = $sapItemNumberResults;
		$proBatchNo = $fields['batchNumber'];
		$refDesPro = "";
		$problemDesc = $fields['problemDescription'];
		$dimen = "";
		$quanNonCon = "";
		$quanDes = "";
		$batchNumbers = "";
		$visaDate = "";
		$visaDateTwo = "";
		$name = "";
		$issue = "";
		
		
		// REPLACE OVERS
		$data = str_replace('[[COMPLAINTNO]]',replaceForeignCharsForRTF($complaintId),$data);
		$data = str_replace('[[DATE]]',replaceForeignCharsForRTF($date),$data);
		$data = str_replace('[[CUSTNAME]]',replaceForeignCharsForRTF($custName),$data);
		$data = str_replace('[[CUSTACCOUNT]]',replaceForeignCharsForRTF($custAccount),$data);
		$data = str_replace('[[CUSTCNAME]]',replaceForeignCharsForRTF($custCName),$data);
		$data = str_replace('[[CUSTOMERADDRESS]]',replaceForeignCharsForRTF($customerAddress),$data);
		$data = str_replace('[[CUSORNO]]',replaceForeignCharsForRTF($CusOrNo),$data);
		$data = str_replace('[[SCAPAORNO]]',replaceForeignCharsForRTF($scapaOrNo),$data);
		$data = str_replace('[[SCAPAINVNO]]',replaceForeignCharsForRTF($scapaInvNo),$data);
		$data = str_replace('[[NODISDATE]]',replaceForeignCharsForRTF($noDisDate),$data);
		$data = str_replace('[[PRODUCTDESCRIPTION]]',replaceForeignCharsForRTF($productDescription),$data);
		$data = str_replace('[[SUPPRREF]]',replaceForeignCharsForRTF($supPrRef),$data);
		$data = str_replace('[[NOSAPPR]]',replaceForeignCharsForRTF($noSAPPr),$data);
		$data = str_replace('[[PROBATCHNO]]',replaceForeignCharsForRTF($proBatchNo),$data);
		$data = str_replace('[[REFDESPRO]]',replaceForeignCharsForRTF($refDesPro),$data);
		$data = str_replace('[[DIMEN]]',replaceForeignCharsForRTF($dimen),$data);
		$data = str_replace('[[PROBLEMDESC]]',replaceForeignCharsForRTF($problemDesc),$data);
		$data = str_replace('[[QUANNONCON]]',replaceForeignCharsForRTF($quanNonCon),$data);
		$data = str_replace('[[QUANDES]]',replaceForeignCharsForRTF($quanDes),$data);
		$data = str_replace('[[BATCHNUMBERS]]',replaceForeignCharsForRTF($batchNumbers),$data);
		$data = str_replace('[[VISADATE]]',replaceForeignCharsForRTF($visaDate),$data);
		$data = str_replace('[[VISADATETWO]]',replaceForeignCharsForRTF($visaDateTwo),$data);
		$data = str_replace('[[NAME]]',replaceForeignCharsForRTF($name),$data);
		$data = str_replace('[[ISSUE]]',replaceForeignCharsForRTF($issue),$data);

		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/disposalNote-en' . $fields['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		// Close the File
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/disposalNote-en" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("Disposal Note Created");
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'disposalNote'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'disposalNote', '" . common::nowDateForMysql() . "', 'en')");
		
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