<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateReturnFormen extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		//first we tell php the filename
		$file = './apps/complaints/word/returnForm-enTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		
		// DATA
		$complaintId = $fields['id'];
		$shipCo = "";
		$date = date("d/m/Y");
		$sapReturnNo = "";
		$contactName = "";
		$custName = $fields['sapName'];
		$contactNo = "";
		$collectionAddress = "";
		$mat = "";
		$weight = "";
		$presNoPal = "";
		$comments = "";
		$name = "";
		$requested = "";
		$dateOne = "";
		$authorised = "";
		$dateTwo = "";
		$specialTransport = "";
		$metTR = "";
		$cost = "";
		$signature = "";
		$goodsRec = "";
		$quantityRe = "";
		$signedFor = "";
		$dateThree = "";
		$scrap = "";
		$rework = "";
		$administrCost = "";
		$reworkCost = "";
		$costTwo = "";
		$signTwo = "";
		$signedForByTwo = "";
		$dateFour = "";
		
		
		// REPLACE OVERS
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintId),$data);
		$data = str_replace('[[SHIPCO]]',replaceForeignCharsForRTF($shipCo),$data);
		$data = str_replace('[[DATE]]',replaceForeignCharsForRTF($date),$data);
		$data = str_replace('[[SAPRETURNNO]]',replaceForeignCharsForRTF($sapReturnNo),$data);
		$data = str_replace('[[CONTACTNAME]]',replaceForeignCharsForRTF($contactName),$data);
		$data = str_replace('[[CUSTNAME]]',replaceForeignCharsForRTF($custName),$data);
		$data = str_replace('[[CONTACTNO]]',replaceForeignCharsForRTF($contactNo),$data);
		$data = str_replace('[[COLLECTIONADDRESS]]',replaceForeignCharsForRTF($collectionAddress),$data);
		$data = str_replace('[[MAT]]',replaceForeignCharsForRTF($mat),$data);
		$data = str_replace('[[WEIGHT]]',replaceForeignCharsForRTF($weight),$data);
		$data = str_replace('[[PRESNOPAL]]',replaceForeignCharsForRTF($presNoPal),$data);
		$data = str_replace('[[COMMENTS]]',replaceForeignCharsForRTF($comments),$data);
		$data = str_replace('[[NAME]]',replaceForeignCharsForRTF($name),$data);
		$data = str_replace('[[REQUESTED]]',replaceForeignCharsForRTF($requested),$data);
		$data = str_replace('[[DATEONE]]',replaceForeignCharsForRTF($dateOne),$data);
		$data = str_replace('[[AUTHORISED]]',replaceForeignCharsForRTF($authorised),$data);
		$data = str_replace('[[DATETWO]]',replaceForeignCharsForRTF($dateTwo),$data);
		$data = str_replace('[[SPECIALTRANSPORT]]',replaceForeignCharsForRTF($specialTransport),$data);
		$data = str_replace('[[METHODTR]]',replaceForeignCharsForRTF($metTR),$data);
		$data = str_replace('[[COST]]',replaceForeignCharsForRTF($cost),$data);
		$data = str_replace('[[SIGNATURE]]',replaceForeignCharsForRTF($signature),$data);
		$data = str_replace('[[GOODSREC]]',replaceForeignCharsForRTF($goodsRec),$data);
		$data = str_replace('[[QUANTITYRE]]',replaceForeignCharsForRTF($quantityRe),$data);
		$data = str_replace('[[SIGNEDFOR]]',replaceForeignCharsForRTF($signedFor),$data);
		$data = str_replace('[[DATETHREE]]',replaceForeignCharsForRTF($dateThree),$data);
		$data = str_replace('[[SCRAP]]',replaceForeignCharsForRTF($scrap),$data);
		$data = str_replace('[[REWORK]]',replaceForeignCharsForRTF($rework),$data);
		$data = str_replace('[[ADMINISTRCOST]]',replaceForeignCharsForRTF($administrCost),$data);
		$data = str_replace('[[REWORKCOST]]',replaceForeignCharsForRTF($reworkCost),$data);
		$data = str_replace('[[COSTTWO]]',replaceForeignCharsForRTF($costTwo),$data);
		$data = str_replace('[[SIGNTWO]]',replaceForeignCharsForRTF($signTwo),$data);
		$data = str_replace('[[SIGNEDFORBYTWO]]',replaceForeignCharsForRTF($signedForByTwo),$data);
		$data = str_replace('[[DATEFOUR]]',replaceForeignCharsForRTF($dateFour),$data);

		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/returnForm-en' . $fields['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		// Close the File
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/returnForm-en" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("Return Form Created");
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'returnForm'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'returnForm', '" . common::nowDateForMysql() . "', 'en')");
		
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