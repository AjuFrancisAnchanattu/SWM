<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateSupplier8den extends page
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
	
		// Evaluation Database
		$datasetEval = mysql::getInstance()->selectDatabase("evaluation")->Execute("SELECT * FROM `evaluation` WHERE `complaintId` = '" . $_REQUEST['id'] . "'");
		$fieldsEval = mysql_fetch_array($datasetEval);
		
		// Employee database
		$datasetEmp = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` WHERE `NTLogon` = '" . currentuser::getInstance()->getNTLogon() . "'");
		$fieldsEmp = mysql_fetch_array($datasetEmp);
		
		// SAP Database
		$dataset = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM `customer` WHERE `id` = '" . $fields['sp_sapSupplierNumber'] . "'");
		$fieldCust = mysql_fetch_array($dataset);

		//first we tell php the filename
		$file = './apps/complaints/word/supplier8d-enTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
			
		//Set up the tick boxes
		$blankBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid291345 {\*\bkmkstart Check1} FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid291345\charrsid291345 {\*\datafield 650000001400000006436865636b3100000000000000000000000000
}{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres0}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid14165849 {\*\bkmkend Check1} ";
				
		$tickBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid7735582  FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid4203260 {\*\datafield 650000001400000006436865636b3100010000000000000000000000}
{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres1}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid7735582 {\*\bkmkend Check1}";		
		
		
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
		$complaintNumber = $complaintType . $fields['id'];
		$site = $fields['sp_siteConcerned'];
		$comRef = $fields['customerComplaintRef'];
		$sampleRecieved = $fields['productSupplierName'];
		$analysis = $fieldsEval['analysis'];
		$rootCauses = $fieldsEval['rootCauses'];
		$possibleSolutions = $fieldsEval['possibleSolutions'];
		$implementedActions = $fieldsEval['implementedActions'];
		$teamLeader = $fieldsEval['teamLeader'];
		$containmentAction = $fieldsEval['containmentAction'];
		$problemIdCus = $fields['problemDescription'];
		$possibleCorrectiveActions = $fields['problemDescription'];
		$comments = $fieldsEval['comments'];
		$problemDescription = $fieldsEval['problemDescription'];
		
		
		// *** No fields in the Form, or Database for this data! ***
		//$supplierContactName = $fields['supplierContactName']
        $supplierContactName = 'supplierContactName';
		
        
		// Dates
 		$fieldsEval['implementedActionsEstimated'] == "0000-00-00" ?  $implementedActionsEstimated = "" : $implementedActionsEstimated = common::transformDateForPHP($fieldsEval['implementedActionsEstimated']);
		$fieldsEval['implementedActionsImplementation'] == "0000-00-00" ? $implementedActionsImplementation = "" : $implementedActionsImplementation = common::transformDateForPHP($fieldsEval['implementedActionsImplementation']);
		$fieldsEval['implementedActionsEffectiveness'] == "0000-00-00" ? $implementedActionsEffectiveness = "" : $implementedActionsEffectiveness = common::transformDateForPHP($fieldsEval['implementedActionsEffectiveness']);
		$fields['sp_sampleSentDate'] == "0000-00-00" ? $sampleSentDate = "" : $sampleSentDate = common::transformDateForPHP($fields['sp_sampleSentDate']);
		$fields['openDate'] == "0000-00-00" ?	$ScapaComplaintDateValue = "" : $ScapaComplaintDateValue = common::transformDateForPHP($fields['openDate']);
		$fields['openDate'] == "0000-00-00" ? $openDate = "" : $openDate = common::transformDateForPHP($fields['openDate']);

		
		// *** No Fields in the form for this data! ***
		$fieldsEval['dateSampleReceived'] == "0000-00-00" ? $dateSampleRecieved = "" : $dateSampleRecieved = common::transformDateForPHP($fields['dateSampleReceived']);
		$fieldsEval['preventiveActionsEstimated'] == "0000-00-00" ? $preventiveActionsEstDate = "" : $preventiveActionsEstDate = common::transformDateForPHP($fields['preventiveActionsEstimated']);
		$fieldsEval['preventiveActionsImplementation'] == "0000-00-00" ? $preventiveActionsImplementation = "" : $preventiveActionsImplementation = common::transformDateForPHP($fields['preventiveActionsImplementation']);
		$fieldsEval['preventiveActionsEffectiveness'] == "0000-00-00" ? $preventiveActionsEffectiveness = "" : $preventiveActionsEffectiveness = common::transformDateForPHP($fields['preventiveActionsEffectiveness']);
		$fieldsEval['analysisDate'] == "0000-00-00" ? $analysisDate = "" : $analysisDate = common::transformDateForPHP($fields['analysisDate']);
		$fields['totalClosureDate'] == "0000-00-00" ? $complaintClosure = "" : $complaintClosure = common::transformDateForPHP($fields['totalClosureDate']);
		
		
		// automatic fields
		$userName = $fieldsEmp['firstName'] . " " . $fieldsEmp['lastName'];
		$userTel = $fieldsEmp['phone'];
		$userFax = $fieldsEmp['fax'];
		$userEmail = $fieldsEmp['email'];
		
		
		// tickboxes
		$stockVerificationMadeYes = $fieldsEval['sp_verificationMade'] == "YES" ? "$tickBox " : "$blankBox";
		$stockVerificationMadeNo = $fieldsEval['sp_verificationMade'] == "NO" ? "$tickBox " : "$blankBox";		
		$sampleRecieved = $fieldsEval['sampleRecieved'] == "Yes" ? "$tickBox " : "$blankBox";		
		$complaintJustified = $fieldsEval['complaintJustified'] == "YES" ? "$tickBox " : "$blankBox";
		$complaintRejected = $fieldsEval['complaintJustified'] == "NO" ? "$tickBox " : "$blankBox";
		$complaintUndecided = $fieldsEval['complaintJustified'] == "undecided" ? "$tickbox " : "$blankBox";		
		$returnGoods = $fieldsEval['returnGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$useGoods = $fieldsEval['sp_useGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$reworkGoods = $fieldsEval['sp_reworkGoods'] == "YES" ? "$tickBox" : "$blankBox";
		$disposeGoods = $fieldsEval['disposeGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$materialCredited = $fieldsEval['sp_materialCredited'] == "YES" ? "$tickBox" : "$blankBox";
		$materialReplaced = $fieldsEval['sp_materialReplaced'] == "YES" ? "$tickBox" : "$blankBox";
		$managementSystemReviewedYes = $fieldsEval['managementSystemReviewed'] == "YES" ? "$tickBox " : "$blankBox";
		$managementSystemReviewedNo = $fieldsEval['managementSystemReviewed'] == "NO" ? "$tickBox " : "$blankBox";
		$managementSystemReviewedNA = $fieldsEval['managementSystemReviewed'] == "na" ? "$tickBox " : "$blankBox";
		$flowChartYes = $fieldsEval['flowChart'] == "YES" ? "$tickBox " : "$blankBox";
		$flowChartNo = $fieldsEval['flowChart'] == "NO" ? "$tickBox " : "$blankBox";
		$flowChartNA = $fieldsEval['flowChart'] == "na" ? "$tickBox " : "$blankBox";
		$fmeaYes = $fieldsEval['fmea'] == "YES" ? "$tickBox " : "$blankBox";
		$fmeaNo = $fieldsEval['fmea'] == "NO" ? "$tickBox " : "$blankBox";
		$fmeaNA = $fieldsEval['fmea'] == "na" ? "$tickBox " : "$blankBox";
		$customerSpecificationYes = $fieldsEval['customerSpecification'] == "YES" ? "$tickBox " : "$blankBox";
		$customerSpecificationNo = $fieldsEval['customerSpecification'] == "NO" ? "$tickBox " : "$blankBox";
		$customerSpecificationNA = $fieldsEval['customerSpecification'] == "na" ? "$tickBox " : "$blankBox";
		$sampleSentYes = $fields['sp_sampleSent'] == "Yes" ? "$tickBox " : "$blankBox";
			
				
		// REPLACE OVERS > Replaces the [[*]] field inthe RTF Docment with the relevent variable.	
		$data = str_replace('[[C]]',replaceForeignCharsForRTF($userName),$data);
		$data = str_replace('[[CE]]',replaceForeignCharsForRTF($userEmail),$data);
		$data = str_replace('[[CF]]',replaceForeignCharsForRTF($userFax),$data);
		$data = str_replace('[[COMREF]]',replaceForeignCharsForRTF($comRef),$data);
		$data = str_replace('[[CSN]]',replaceForeignCharsForRTF($customerSpecificationNo),$data);
		$data = str_replace('[[CSNA]]',replaceForeignCharsForRTF($customerSpecificationNA),$data);		
		$data = str_replace('[[CSY]]',replaceForeignCharsForRTF($customerSpecificationYes),$data);
		$data = str_replace('[[CT]]',replaceForeignCharsForRTF($userTel),$data);
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintNumber),$data);		
		$data = str_replace('[[PROBLEMIDCUS]]',replaceForeignCharsForRTF($problemIdCus),$data);
		$data = str_replace('[[SR]]',replaceForeignCharsForRTF($sampleRecieved),$data);
		$data = str_replace('[[CA]]',replaceForeignCharsForRTF($containmentAction),$data);
		$data = str_replace('[[SCD]]',replaceForeignCharsForRTF($ScapaComplaintDateValue),$data);
		$data = str_replace('[[ANALYSISTEXT]]',replaceForeignCharsForRTF($analysis),$data);
		$data = str_replace('[[RC]]',replaceForeignCharsForRTF($rootCauses),$data);
		$data = str_replace('[[CJ]]',replaceForeignCharsForRTF($complaintJustified),$data);
		$data = str_replace('[[CR]]',replaceForeignCharsForRTF($complaintRejected),$data);
		$data = str_replace('[[CU]]',replaceForeignCharsForRTF($complaintUndecided),$data);	
		$data = str_replace('[[RG]]',replaceForeignCharsForRTF($returnGoods),$data);
		$data = str_replace('[[UG]]',replaceForeignCharsForRTF($useGoods),$data);
 		$data = str_replace('[[RWG]]',replaceForeignCharsForRTF($reworkGoods),$data);
		$data = str_replace('[[DTG]]',replaceForeignCharsForRTF($disposeGoods),$data);
		$data = str_replace('[[MC]]',replaceForeignCharsForRTF($materialCredited),$data);
		$data = str_replace('[[MR]]',replaceForeignCharsForRTF($materialReplaced),$data);
		$data = str_replace('[[PS]]',replaceForeignCharsForRTF($possibleSolutions),$data);
		$data = str_replace('[[IMPLCORRACTIONS]]',replaceForeignCharsForRTF($implementedActions),$data);	
		$data = str_replace('[[MSY]]',replaceForeignCharsForRTF($managementSystemReviewedYes),$data);
		$data = str_replace('[[MSN]]',replaceForeignCharsForRTF($managementSystemReviewedNo),$data);
		$data = str_replace('[[MSNA]]',replaceForeignCharsForRTF($managementSystemReviewedNA),$data);
		$data = str_replace('[[FCY]]',replaceForeignCharsForRTF($flowChartYes),$data);
		$data = str_replace('[[FCN]]',replaceForeignCharsForRTF($flowChartNo),$data);
		$data = str_replace('[[FCNA]]',replaceForeignCharsForRTF($flowChartNA),$data);
		$data = str_replace('[[FMY]]',replaceForeignCharsForRTF($fmeaYes),$data);
		$data = str_replace('[[FMN]]',replaceForeignCharsForRTF($fmeaNo),$data);
		$data = str_replace('[[FMNA]]',replaceForeignCharsForRTF($fmeaNA),$data);
		$data = str_replace('[[TL]]',replaceForeignCharsForRTF($teamLeader),$data);
		$data = str_replace('[[IAE]]',replaceForeignCharsForRTF($implementedActionsEstimated),$data);
		$data = str_replace('[[IAI]]',replaceForeignCharsForRTF($implementedActionsImplementation),$data);
		$data = str_replace('[[IAE2]]',replaceForeignCharsForRTF($implementedActionsEffectiveness),$data);
		$data = str_replace('[[SSY]]',replaceForeignCharsForRTF($sampleSentYes),$data);
		$data = str_replace('[[SSD]]',replaceForeignCharsForRTF($sampleSentDate),$data);
		$data = str_replace('[[DSR]]',replaceForeignCharsForRTF($dateSampleRecieved),$data);
		$data = str_replace('[[COMR]]',replaceForeignCharsForRTF($comments),$data);	
		$data = str_replace('[[PD]]',replaceForeignCharsForRTF($problemDescription),$data);	
 		$data = str_replace('[[SITE]]',replaceForeignCharsForRTF($site),$data);
		$data = str_replace('[[SN]]',replaceForeignCharsForRTF($sapCustName),$data);
		$data = str_replace('[[SVMY]]',replaceForeignCharsForRTF($stockVerificationMadeYes),$data);
		$data = str_replace('[[SVMN]]',replaceForeignCharsForRTF($stockVerificationMadeNo),$data);
			
		// *** No fields in the form for this information. ***
		$data = str_replace('[[AD]]',replaceForeignCharsForRTF($analysisDate),$data);
		$data = str_replace('[[DSR]]',replaceForeignCharsForRTF($dateSampleRecieved),$data);
		$data = str_replace('[[PAED]]',replaceForeignCharsForRTF($preventiveActionsEstDate),$data);
		$data = str_replace('[[PAI]]',replaceForeignCharsForRTF($preventiveActionsImplementation),$data);
		$data = str_replace('[[PAVE]]',replaceForeignCharsForRTF($preventiveActionsEffectiveness),$data);
		$data = str_replace('[[SCN]]',replaceForeignCharsForRTF($supplierContactName),$data);
		$data = str_replace('[[CC]]',replaceForeignCharsForRTF($complaintClosure),$data);
	
		// close file 
		fclose($fp);
		
		// print file contents 
		//print "The data in the file is \"".$data."\"";		
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/supplier8d-en' . $fields['id'] .  '.rtf';				
		
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
				
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/supplier8d-en" . $fields['id'] .  ".rtf", 0777);
				
		$this->addLog("Supplier 8D Created");
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = 'supplier8d'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", 'supplier8d', '" . common::nowDateForMysql() . "', 'en')");
		
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