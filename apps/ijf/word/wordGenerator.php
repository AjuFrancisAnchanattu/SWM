<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class wordGenerator extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE id = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$datasetProduction = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT `minimumOrderQuantity`, `cartonQuantity` FROM `production` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsProduction = mysql_fetch_array($datasetProduction);
		
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE employee.NTLogon = '" . currentuser::getInstance()->getNTlogon() . "'");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);

		$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT `name`, `address`, `postcode`, `salesPerson`, `city`, `country` FROM `customer` WHERE `id` = '" . $fields['customerAccountNumber'] . "'");
		$fieldsSAP = mysql_fetch_array($datasetSAP);
		
		//first we tell php the filename
		$file = './apps/ijf/word/ijfTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		
		// DATA
		//Word Quote Details
		if($fields['existingCustomer']=="yes" && $fields['wqrAddress']=="")
		{
			$companyName = $fieldsSAP['name'];
			$companyAddress = $fieldsSAP['address'];
			$companyCity = $fieldsSAP['city'];
			$companyPostcode = $fieldsSAP['postcode'];
			$companyCountry = $fieldsSAP['country']; 
		}
		elseif($fields['existingCustomer']=="yes" && $fields['wqrAddress']!="")
		{
			$companyName = $fieldsSAP['name'];
			$companyAddress = $fields['wqrAddress'];
			$companyCity = $fields['wqrCity'];
			$companyPostcode = $fields['wqrPostCode'];
			$companyCountry = $fields['wqrCountry']; 
		}
		else	
		{
			$companyName = $fields['customerName'];
			$companyAddress = $fields['wqrAddress'];
			$companyCity = $fields['wqrCity'];
			$companyPostcode = $fields['wqrPostCode'];
			$companyCountry = $fields['wqrCountry']; 			
		}
		
		$date = common::nowDateForPHP();
		$contactName = $fields['contactName'];
		$scapaMaterialGroup = $fields['materialGroup'];
		$colour = $fields['colour'];
		$thickness = $fields['thickness_quantity'] . " " . $fields['thickness_measurement'];
		$width = $fields['width_quantity'] . " " . $fields['width_measurement'];
		$length = $fields['ijfLength_quantity'] . " " . $fields['ijfLength_measurement'];
		$minimumOrderQuantity = $fieldsProduction['minimumOrderQuantity'];
		$cartonQuantity = $fieldsProduction['cartonQuantity'];
		$currency = $fields['currency'];
		$name = usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName();
//		$comments = "";
		$tel = $fieldsEmployee['phone'];
		$fax = $fieldsEmployee['fax'];
		$email = $fieldsEmployee['email'];
		$address = $fieldsEmployee['address'];
		
		$formatAddress = str_replace(',', '\par', $address);
		
		// REPLACE OVERS
		$data = str_replace('[[COMPANYNAME]]',$companyName,$data);
		$data = str_replace('[[COMPANYADDRESS]]',$companyAddress,$data);
		$data = str_replace('[[COMPANYCITY]]',$companyCity,$data);
		$data = str_replace('[[COMPANYPOSTCODE]]',$companyPostcode,$data);
		$data = str_replace('[[COMPANYCOUNTRY]]',$companyCountry,$data);
		$data = str_replace('[[DATE]]',$date,$data);
		$data = str_replace('[[CONTACTNAME]]',$contactName,$data);
		$data = str_replace('[[SCAPAMATERIALGROUP]]',$scapaMaterialGroup,$data);
		$data = str_replace('[[COLOUR]]',$colour,$data);
		$data = str_replace('[[THICKNESS]]',$thickness,$data);
		$data = str_replace('[[WIDTH]]',$width,$data);
		$data = str_replace('[[LENGTH]]',$length,$data);
		$data = str_replace('[[MINIMUMORDERQUANTITY]]',$minimumOrderQuantity,$data);
		$data = str_replace('[[CARTONQUANTITY]]',$cartonQuantity,$data);
		$data = str_replace('[[CURRENCY]]',$currency,$data);
		$data = str_replace('[[NAME]]',$name,$data);
//		$data = str_replace('[[COMMENTS]]',$comments,$data);
		$data = str_replace('[[TEL]]',$tel,$data);
		$data = str_replace('[[FAX]]',$fax,$data);
		$data = str_replace('[[EMAIL]]',$email,$data);
		$data = str_replace('[[ADDRESS]]',$formatAddress,$data);

		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/ijf/word/files/ijf' . $fields['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		$this->addLog("Enquiry Letter Created");

		
		// Close the File
		fclose($fpSave);
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM `documents` WHERE ijfId = '" . $fields['id'] . "' AND name='enquiryLetter'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO `documents` (ijfId, name, date) VALUES(" . $fields['id'] . ", 'enquiryLetter', '" . common::nowDateForMysql() . "')");
		
		page::redirect("/apps/ijf/");	
	}

	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("IJF")->Execute(sprintf("INSERT INTO log (ijfId, NTLogon, action, logDate) VALUES (%u, '%s', '%s', '%s')",
		$_REQUEST['id'],
		currentuser::getInstance()->getNTLogon(),
		$action,
		common::nowDateTimeForMysql()
		));
	}
}

?>