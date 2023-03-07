<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generate8dde extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		$datasetEval = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `evaluation` WHERE `complaintId` = '" . $_REQUEST['id'] . "'");
		$fieldsEval = mysql_fetch_array($datasetEval);
		
		$datasetEmployee = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE employee.NTLogon = '" . currentuser::getInstance()->getNTlogon() . "'");
		$fieldsEmployee = mysql_fetch_array($datasetEmployee);
		
		$datasetEmployeeOwner = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE employee.NTLogon = '" . $fields['owner'] . "'");
		$fieldsEmployeeOwner = mysql_fetch_array($datasetEmployeeOwner);
		
		$datasetInitiator = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT employee.phone, employee.fax, employee.email FROM `employee` INNER JOIN `sites` ON employee.site = sites.name WHERE CONCAT(employee.firstName, ' ', employee.lastName) = '" . $fields['internalSalesName'] . "'");
		$fieldsInitiator = mysql_fetch_array($datasetInitiator);
		//---------------------------------------------------
		$datasetInvoiceNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaInvoiceNumberDate` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapInvoiceDateResultsArray = array();
		$sapInvoiceNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetInvoiceNumber))
		{
			$sapInvoiceNumberResults = array_push($sapInvoiceNumberResultsArray, $row['scapaInvoiceNumber']);
			
			$sapInvoiceDateResults = array_push($sapInvoiceDateResultsArray, common::transformDateForPHP($row['scapaInvoiceDate']));
		}
		//----------------------------------------------------	
		$datasetSAPItem = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapSAPItemResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetSAPItem))
		{
			$sapSAPItemResults = array_push($sapSAPItemResultsArray, $row['sapItemNumber']);
		}
		//-----------------------------------------------------
		$datasetMaterialGroup = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `materialGroup` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapMatGroupResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetMaterialGroup))
		{
			$sapMatGroupResults = array_push($sapMatGroupResultsArray, $row['materialGroup']);
		}
		
		$blankBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid291345 {\*\bkmkstart Check1} FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid291345\charrsid291345 {\*\datafield 650000001400000006436865636b3100000000000000000000000000
}{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres0}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid14165849 {\*\bkmkend Check1} ";
		
		$tickBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid7735582  FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid4203260 {\*\datafield 650000001400000006436865636b3100010000000000000000000000}
{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres1}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid7735582 {\*\bkmkend Check1}";
		
		
		//first we tell php the filename
		$file = './apps/complaints/word/8d-deTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		
		//check to make sure "" is shown for date value 0000-00-00 for all dates in DATA
		$fields['sampleReceptionDate'] == "0000-00-00" ? $dateTwoValue = "" : $dateTwoValue = common::transformDateForPHP($fields['sampleReceptionDate']);
		$fields['openDate'] == "0000-00-00" ? $regDateValue = "" : $regDateValue = common::transformDateForPHP($fields['openDate']);
		$fieldsEval['analysisDate'] == "0000-00-00" ?	$analysisDateValue = "" : $analysisDateValue = common::transformDateForPHP($fieldsEval['analysisDate']);
		$fieldsEval['rootCausesDate'] == "0000-00-00" ? $rootCausesDateValue = "" : $rootCausesDateValue = common::transformDateForPHP($fieldsEval['rootCausesDate']);
		$fieldsEval['implementedActionsEstimated'] == "0000-00-00" ? $estDateValue = "" : $estDateValue = common::transformDateForPHP($fieldsEval['implementedActionsEstimated']);
		$fieldsEval['implementedActionsImplementation'] == "0000-00-00" ? $implValue = "" : $implValue = common::transformDateForPHP($fieldsEval['implementedActionsImplementation']);
		$fieldsEval['implementedActionsEffectiveness'] == "0000-00-00" ? $valEffecValue = "" : $valEffecValue = common::transformDateForPHP($fieldsEval['implementedActionsEffectiveness']);
		$fieldsEval['preventiveActionsEstimatedDate'] == "0000-00-00" ? $estiDateValue = "" : $estiDateValue = common::transformDateForPHP($fieldsEval['preventiveActionsEstimatedDate']);
		$fieldsEval['preventiveActionsImplementedDate'] == "0000-00-00" ? $impleValue = "" : $impleValue = common::transformDateForPHP($fieldsEval['preventiveActionsImplementedDate']);
		$fieldsEval['preventiveActionsValidationDate'] == "0000-00-00" ? $valEffectValue = "" : $valEffectValue = common::transformDateForPHP($fieldsEval['preventiveActionsValidationDate']);
		
		//check if management system reviewed, flowchart, fmea & specifications have been set to yes and enter values
		$fieldsEval['managementSystemReviewedDate'] == "0000-00-00" ? $msDateValue = "N/A" : $msDateValue = common::transformDateForPHP($fieldsEval['managementSystemReviewedDate']);
		$fieldsEval['managementSystemReviewedRef'] == "" ? $msRefValue = "N/A" : $msRefValue = $fieldsEval['managementSystemReviewedRef'];
		$fieldsEval['flowChartDate'] == "0000-00-00" ? $fcDateValue = "N/A" : $fcDateValue = common::transformDateForPHP($fieldsEval['flowChartDate']);
		$fieldsEval['flowChartRef'] == "" ? $fcRefValue = "N/A" : $fcRefValue = $fieldsEval['flowChartRef'];
		$fieldsEval['fmeaDate'] == "0000-00-00" ? $fmDateValue = "N/A" : $fmDateValue = common::transformDateForPHP($fieldsEval['fmeaDate']);
		$fieldsEval['fmeaRef'] == "" ? $fmRefValue = "N/A" : $fmRefValue = $fieldsEval['fmeaRef'];
		$fieldsEval['customerSpecificationDate'] == "0000-00-00" ? $scDateValue = "N/A" : $scDateValue = common::transformDateForPHP($fieldsEval['customerSpecificationDate']);
		$fieldsEval['customerSpecificationRef'] == "" ? $scRefValue = "N/A" : $scRefValue = $fieldsEval['customerSpecificationRef'];
		
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
		$complaintNumber = $complaintType . "" . $fields['id'];
		$regDate = $regDateValue;
		$saName = usercache::getInstance()->get($fields['internalSalesName'])->getName(); //internal or external sales name
		$comRef = $fields['customerComplaintRef'];
		$saOffice = $fields['salesOffice'];
		$cusName = $fields['sapName'];
		
		foreach ($sapInvoiceDateResultsArray as $key => $val)
		{
			if ($val == "0000-00-00" || $val == "30/11/1999")
			{
				$val = "";
			}
			$invoiceDate .= $val . " ";
		};
		
		$date = $invoiceDate;
		
		foreach ($sapInvoiceNumberResultsArray as $key => $val)
		{
			$invoiceNum .= $val . ", ";
		};
		
		$invNo = $invoiceNum;
		
		$sapNo = $fields['sapCustomerNumber'];
		$cupANo = $fields['customerItemNumber'];
		$cusONo = "";
		$itemDes = $fields['productDescription'];
		$colour = $fields['colour'];
		
		foreach ($sapSAPItemResultsArray as $key => $val)
		{
			$sapSAPItem .= $val . ", ";
		};
		$sapitem = $sapSAPItem;
		
		$batchNo = $fields['batchNumber'];
		$quan = $fields['quantityUnderComplaint_quantity'] . " " . $fields['quantityUnderComplaint_measurement'];
		
		foreach ($sapMatGroupResultsArray as $key => $val)
		{
			$sapMatGroup .= $val . ", ";
		};
		$matgr = $sapMatGroup;
		
		$problemIdCus = $fields['problemDescription'];
		$dateTwo = $dateTwoValue;
		$prown = usercache::getInstance()->get($fields['processOwner'])->getName();
		$tlead = $fieldsEval['teamLeader'];
		$salesActions = $fields['salesContainmentActions'];
		$requestActions = $fields['actionRequested'];
		$containmentActions = $fieldsEval['containmentAction'];
		$analysisRootCauses = $fieldsEval['rootCauses'];
		$analysisText = $fieldsEval['analysis'];
		$doan = $analysisDateValue;// . ", " . $rootCausesDateValue;
		$comments = $fieldsEval['comments'];
		$possibleActions = $fieldsEval['possibleSolutions'];
		$implCorrActions = $fieldsEval['implementedActions'];
		$estDate = $estDateValue;
		$impl = $implValue;
		$valEffec = $valEffecValue;
		$improvement = $fieldsEval['preventiveActions'];
		$estiDate = $estiDateValue;
		$imple = $impleValue;
		$valEffect = $valEffectValue;
		$closure = "";
		$tel = $fieldsInitiator['phone'];
		$fax = $fieldsInitiator['fax'];
		$email = $fieldsInitiator['email'];
		$msRef = $msRefValue;
		$msDate = $msDateValue;
		$fcRef = $fcRefValue;
		$fcDate = $fcDateValue;
		$fmRef = $fmRefValue;
		$fmDate = $fmDateValue;
		$scRef = $scRefValue;
		$scDate = $scDateValue;
		
		// Tick Boxes oh great fun....
		$sampleReceived = $fields['sampleReceived'] == "Yes" ? "$tickBox " : "$blankBox";
		$complaintJustified = $fieldsEval['complaintJustified'] == "YES" ? "$tickBox " : "$blankBox";
		$complaintRejected = $fieldsEval['complaintJustified'] == "NO" ? "$tickBox " : "$blankBox";
		$complaintUndecided = $fieldsEval['complaintJustified'] == "undecided" ? "$tickBox " : "$blankBox";
		$returnTheGoods = $fieldsEval['returnGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$disposeTheGoods = $fieldsEval['disposeGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$managementReviewedYes = $fieldsEval['managementSystemReviewed'] == "YES" ? "$tickBox " : "$blankBox";
		$managementReviewedNo = $fieldsEval['managementSystemReviewed'] == "NO" || $fieldsEval['managementSystemReviewed'] == "no" ? "$tickBox " : "$blankBox";
		$managementReviewedNa = $fieldsEval['managementSystemReviewed'] == "na" ? "$tickBox " : "$blankBox";
		$flowChartYes = $fieldsEval['flowChart'] == "YES" ? "$tickBox " : "$blankBox";
		$flowChartNo = $fieldsEval['flowChart'] == "NO" ? "$tickBox " : "$blankBox";
		$flowChartNa = $fieldsEval['flowChart'] == "na" ? "$tickBox " : "$blankBox";
		$fmeaYes = $fieldsEval['fmea'] == "YES" ? "$tickBox " : "$blankBox";
		$fmeaNo = $fieldsEval['fmea'] == "NO" ? "$tickBox " : "$blankBox";
		$fmeaNa = $fieldsEval['fmea'] == "na" ? "$tickBox " : "$blankBox";
		$customerSpecYes = $fieldsEval['customerSpecification'] == "YES" ? "$tickBox " : "$blankBox";
		$customerSpecNo = $fieldsEval['customerSpecification'] == "NO" ? "$tickBox " : "$blankBox";
		$customerSpecNa = $fieldsEval['customerSpecification'] == "na" ? "$tickBox " : "$blankBox";
		
		
		
		// REPLACE OVERS
		$data = str_replace('[[ID]]',replaceForeignCharsForRTF($complaintNumber),$data);
		$data = str_replace('[[REGDATE]]',replaceForeignCharsForRTF($regDate),$data);
		$data = str_replace('[[SANAME]]',replaceForeignCharsForRTF($saName),$data);
		$data = str_replace('[[TEL]]',replaceForeignCharsForRTF($tel),$data);
		$data = str_replace('[[FAX]]',replaceForeignCharsForRTF($fax),$data);
		$data = str_replace('[[EMAIL]]',replaceForeignCharsForRTF($email),$data);
		$data = str_replace('[[COMREF]]',replaceForeignCharsForRTF($comRef),$data);
		$data = str_replace('[[SAOFFICE]]',replaceForeignCharsForRTF($saOffice),$data);
		$data = str_replace('[[CUSNAME]]',replaceForeignCharsForRTF($cusName),$data);
		$data = str_replace('[[INVNO]]',replaceForeignCharsForRTF($invNo),$data);
		$data = str_replace('[[DATE]]',replaceForeignCharsForRTF($date),$data);
		$data = str_replace('[[SAPNO]]',replaceForeignCharsForRTF($sapNo),$data);
		$data = str_replace('[[CUPANO]]',replaceForeignCharsForRTF($cupANo),$data);
		$data = str_replace('[[CUSONO]]',replaceForeignCharsForRTF($cusONo),$data);
		$data = str_replace('[[ITEMDES]]',replaceForeignCharsForRTF($itemDes),$data);
		$data = str_replace('[[COLOUR]]',replaceForeignCharsForRTF($colour),$data);
		$data = str_replace('[[SAPITEM]]',replaceForeignCharsForRTF($sapitem),$data);
		$data = str_replace('[[BATCHNO]]',replaceForeignCharsForRTF($batchNo),$data);
		$data = str_replace('[[QUAN]]',replaceForeignCharsForRTF($quan),$data);
		$data = str_replace('[[MATGR]]',replaceForeignCharsForRTF($matgr),$data);
		$data = str_replace('[[PROBLEMIDCUS]]',replaceForeignCharsForRTF($problemIdCus),$data);
		$data = str_replace('[[DATETWO]]',replaceForeignCharsForRTF($dateTwo),$data);
		$data = str_replace('[[PROWN]]',replaceForeignCharsForRTF($prown),$data);
		$data = str_replace('[[TLEAD]]',replaceForeignCharsForRTF($tlead),$data);
		$data = str_replace('[[SALESACTIONS]]',replaceForeignCharsForRTF($salesActions),$data);
		$data = str_replace('[[REQUESTACTIONS]]',replaceForeignCharsForRTF($requestActions),$data);
		$data = str_replace('[[CONTAINMENTACTIONS]]',replaceForeignCharsForRTF($containmentActions),$data);
		$data = str_replace('[[ANALYROOTCAUSES]]',replaceForeignCharsForRTF($analysisRootCauses),$data);
		$data = str_replace('[[ANALYSISTEXT]]',replaceForeignCharsForRTF($analysisText),$data);
		$data = str_replace('[[DOAN]]',replaceForeignCharsForRTF($doan),$data);
		$data = str_replace('[[COMMENTS]]',replaceForeignCharsForRTF($comments),$data);
		$data = str_replace('[[POSSIBLEACTIONS]]',replaceForeignCharsForRTF($possibleActions),$data);
		$data = str_replace('[[IMPLCORRACTIONS]]',replaceForeignCharsForRTF($implCorrActions),$data);
		$data = str_replace('[[ESTDATE]]',replaceForeignCharsForRTF($estDate),$data);
		$data = str_replace('[[IMPL]]',replaceForeignCharsForRTF($impl),$data);
		$data = str_replace('[[VALEFFEC]]',replaceForeignCharsForRTF($valEffec),$data);
		$data = str_replace('[[IMPROVEMENT]]',replaceForeignCharsForRTF($improvement),$data);
		$data = str_replace('[[ESTIDATE]]',replaceForeignCharsForRTF($estiDate),$data);
		$data = str_replace('[[IMPLE]]',replaceForeignCharsForRTF($imple),$data);
		$data = str_replace('[[VALEFFECT]]',replaceForeignCharsForRTF($valEffect),$data);
		$data = str_replace('[[CLOSURE]]',replaceForeignCharsForRTF($closure),$data);
		$data = str_replace('[[SR]]',replaceForeignCharsForRTF($sampleReceived),$data);
		$data = str_replace('[[CJ]]',replaceForeignCharsForRTF($complaintJustified),$data);
		$data = str_replace('[[CR]]',replaceForeignCharsForRTF($complaintRejected),$data);
		$data = str_replace('[[CU]]',replaceForeignCharsForRTF($complaintUndecided),$data);
		$data = str_replace('[[RTG]]',replaceForeignCharsForRTF($returnTheGoods),$data);
		$data = str_replace('[[DTG]]',replaceForeignCharsForRTF($disposeTheGoods),$data);
		$data = str_replace('[[MSY]]',replaceForeignCharsForRTF($managementReviewedYes),$data);
		$data = str_replace('[[MSN]]',replaceForeignCharsForRTF($managementReviewedNo),$data);
		$data = str_replace('[[MSNA]]',replaceForeignCharsForRTF($managementReviewedNa),$data);
		$data = str_replace('[[FCY]]',replaceForeignCharsForRTF($flowChartYes),$data);
		$data = str_replace('[[FCN]]',replaceForeignCharsForRTF($flowChartNo),$data);
		$data = str_replace('[[FCNA]]',replaceForeignCharsForRTF($flowChartNa),$data);
		$data = str_replace('[[FMY]]',replaceForeignCharsForRTF($fmeaYes),$data);
		$data = str_replace('[[FMN]]',replaceForeignCharsForRTF($fmeaNo),$data);
		$data = str_replace('[[FMNA]]',replaceForeignCharsForRTF($fmeaNa),$data);
		$data = str_replace('[[SCY]]',replaceForeignCharsForRTF($customerSpecYes),$data);
		$data = str_replace('[[SCN]]',replaceForeignCharsForRTF($customerSpecNo),$data);
		$data = str_replace('[[SCNA]]',replaceForeignCharsForRTF($customerSpecNa),$data);
		$data = str_replace('[[MSDATE]]',replaceForeignCharsForRTF($msDate),$data);
		$data = str_replace('[[MSREF]]',replaceForeignCharsForRTF($msRef),$data);
		$data = str_replace('[[FCDATE]]',replaceForeignCharsForRTF($fcDate),$data);
		$data = str_replace('[[FCREF]]',replaceForeignCharsForRTF($fcRef),$data);
		$data = str_replace('[[FMDATE]]',replaceForeignCharsForRTF($fmDate),$data);
		$data = str_replace('[[FMREF]]',replaceForeignCharsForRTF($fmRef),$data);
		$data = str_replace('[[SCDATE]]',replaceForeignCharsForRTF($scDate),$data);
		$data = str_replace('[[SCREF]]',replaceForeignCharsForRTF($scRef),$data);
		
		// close file 
		fclose($fp);
		
		// print file contents 
		//print "The data in the file is \"".$data."\"";
		
		
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/8d-de' . $fields['id'] .  '.rtf';
		
		
		
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		fclose($fpSave);
		
		chmod("./apps/complaints/word/files/8d-de" . $fields['id'] .  ".rtf", 0777);
		
		$this->addLog("8D Created");
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = '8d'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", '8d', '" . common::nowDateForMysql() . "', 'de')");
		
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