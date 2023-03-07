<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generate8den extends page
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
		
		$datasetInvoiceNumber = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `scapaInvoiceNumberDate` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		
		$sapInvoiceDateResultsArray = array();
		$sapInvoiceNumberResultsArray = array();
		
		while ($row = mysql_fetch_array($datasetInvoiceNumber))
		{
			$sapInvoiceNumberResults = array_push($sapInvoiceNumberResultsArray, $row['scapaInvoiceNumber']);
			
			$sapInvoiceDateResults = array_push($sapInvoiceDateResultsArray, common::transformDateForPHP($row['scapaInvoiceDate']));
		}
		
		$datasetSAPItem = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `sapItemNumber` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		while ($row = mysql_fetch_array($datasetSAPItem))
		{
			$sapSAPItemResults = $sapSAPItemResults + $row['sapItemNumber'];
		}
		
		$datasetMaterialGroup = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `materialGroup` WHERE complaintId = '" . $_REQUEST['id'] . "'");
		while ($row = mysql_fetch_array($datasetMaterialGroup))
		{
			$sapMatGroupResults = $sapMatGroupResults + $row['materialGroup'];
		}
		
		
		$blankBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid291345 {\*\bkmkstart Check1} FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid291345\charrsid291345 {\*\datafield 650000001400000006436865636b3100000000000000000000000000
}{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres0}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid14165849 {\*\bkmkend Check1} ";
		
		$tickBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid7735582  FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid4203260 {\*\datafield 650000001400000006436865636b3100010000000000000000000000}
{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres1}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid7735582 {\*\bkmkend Check1}";
		
		
		//first we tell php the filename
		$file = './apps/complaints/word/8d-enTemplate.rtf';
		
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
		$sapitem = $sapSAPItemResults;
		$batchNo = $fields['batchNumber'];
		$quan = $fields['quantityUnderComplaint_quantity'] . " " . $fields['quantityUnderComplaint_measurement'];
		$matgr = $sapMatGroupResults;
		$problemIdCus = $fields['problemDescription'];
		$dateTwo = $dateTwoValue;
		$prown = usercache::getInstance()->get($fields['processOwner'])->getName();
		$tlead = $fieldsEval['teamLeader'];
		$salesActions = $fields['salesContainmentActions'];
		$requestActions = $fields['actionRequested'];
		$containmentActions = $fieldsEval['containmentAction'];
		$analysisRootCauses = $fieldsEval['rootCauses'];
		$analysisText = $fieldsEval['analysis'];
		$doan = $analysisDateValue . ", " . $rootCausesDateValue;
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
		$tel = $fieldsEmployee['phone'];
		$fax = $fieldsEmployee['fax'];
		$email = $fieldsEmployee['email'];
		
		
		// Tick Boxes oh great fun....
		$sampleReceived = $fields['sampleReceived'] == "Yes" ? "$tickBox " : "$blankBox";
		$complaintJustified = $fieldsEval['complaintJustified'] == "YES" ? "$tickBox " : "$blankBox";
		$complaintRejected = $fieldsEval['complaintJustified'] == "NO" ? "$tickBox " : "$blankBox";
		$complaintUnproven = $fieldsEval['complaintJustified'] == "unproven" ? "$tickBox " : "$blankBox";
		$returnTheGoods = $fieldsEval['returnGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$disposeTheGoods = $fieldsEval['disposeGoods'] == "YES" ? "$tickBox " : "$blankBox";
		$managementReviewedYes = $fieldsEval['managementSystemReviewed'] == "YES" ? "$tickBox " : "$blankBox";
		$managementReviewedNo = $fieldsEval['managementSystemReviewed'] == "NO" || $fieldsEval['managementSystemReviewed'] == "no" ? "$tickBox " : "$blankBox";
		$managementReviewedNa = $fieldsEval['managementSystemReviewed'] == "na" ? "$tickBox " : "$blankBox";
		$flowChartYes = /*$fieldsEval['inspectionInstructions'] == "YES" ? "$tickBox " : */"$blankBox";
		$flowChartNo = /*$fieldsEval['inspectionInstructions'] == "NO" ? "$tickBox " : */"$blankBox";
		$flowChartNa = /*$fieldsEval['inspectionInstructions'] == "na" ? "$tickBox " : */"$blankBox";
		$fmeaYes = $fieldsEval['fmea'] == "YES" ? "$tickBox " : "$blankBox";
		$fmeaNo = $fieldsEval['fmea'] == "NO" ? "$tickBox " : "$blankBox";
		$fmeaNa = $fieldsEval['fmea'] == "na" ? "$tickBox " : "$blankBox";
		$customerSpecYes = $fieldsEval['customerSpecification'] == "YES" ? "$tickBox " : "$blankBox";
		$customerSpecNo = $fieldsEval['customerSpecification'] == "NO" ? "$tickBox " : "$blankBox";
		$customerSpecNa = $fieldsEval['customerSpecification'] == "na" ? "$tickBox " : "$blankBox";
		

		
		// REPLACE OVERS
		$data = str_replace('[[ID]]',$complaintNumber,$data);
		$data = str_replace('[[REGDATE]]',$regDate,$data);
		$data = str_replace('[[SANAME]]',$saName,$data);
		$data = str_replace('[[TEL]]',$tel,$data);
		$data = str_replace('[[FAX]]',$fax,$data);
		$data = str_replace('[[EMAIL]]',$email,$data);
		$data = str_replace('[[COMREF]]',$comRef,$data);
		$data = str_replace('[[SAOFFICE]]',$saOffice,$data);
		$data = str_replace('[[CUSNAME]]',$cusName,$data);
		$data = str_replace('[[INVNO]]',$invNo,$data);
		$data = str_replace('[[DATE]]',$date,$data);
		$data = str_replace('[[SAPNO]]',$sapNo,$data);
		$data = str_replace('[[CUPANO]]',$cupANo,$data);
		$data = str_replace('[[CUSONO]]',$cusONo,$data);
		$data = str_replace('[[ITEMDES]]',$itemDes,$data);
		$data = str_replace('[[COLOUR]]',$colour,$data);
		$data = str_replace('[[SAPITEM]]',$sapitem,$data);
		$data = str_replace('[[BATCHNO]]',$batchNo,$data);
		$data = str_replace('[[QUAN]]',$quan,$data);
		$data = str_replace('[[MATGR]]',$matgr,$data);
		$data = str_replace('[[PROBLEMIDCUS]]',$problemIdCus,$data);
		$data = str_replace('[[DATETWO]]',$dateTwo,$data);
		$data = str_replace('[[PROWN]]',$prown,$data);
		$data = str_replace('[[TLEAD]]',$tlead,$data);
		$data = str_replace('[[SALESACTIONS]]',$salesActions,$data);
		$data = str_replace('[[REQUESTACTIONS]]',$requestActions,$data);
		$data = str_replace('[[CONTAINMENTACTIONS]]',$containmentActions,$data);
		$data = str_replace('[[ANALYROOTCAUSES]]',$analysisRootCauses,$data);
		$data = str_replace('[[ANALYSISTEXT]]',$analysisText,$data);
		$data = str_replace('[[DOAN]]',$doan,$data);
		$data = str_replace('[[COMMENTS]]',$comments,$data);
		$data = str_replace('[[POSSIBLEACTIONS]]',$possibleActions,$data);
		$data = str_replace('[[IMPLCORRACTIONS]]',$implCorrActions,$data);
		$data = str_replace('[[ESTDATE]]',$estDate,$data);
		$data = str_replace('[[IMPL]]',$impl,$data);
		$data = str_replace('[[VALEFFEC]]',$valEffec,$data);
		$data = str_replace('[[IMPROVEMENT]]',$improvement,$data);
		$data = str_replace('[[ESTIDATE]]',$estiDate,$data);
		$data = str_replace('[[IMPLE]]',$imple,$data);
		$data = str_replace('[[VALEFFECT]]',$valEffect,$data);
		$data = str_replace('[[CLOSURE]]',$closure,$data);
		$data = str_replace('[[SR]]',$sampleReceived,$data);
		$data = str_replace('[[CJ]]',$complaintJustified,$data);
		$data = str_replace('[[CR]]',$complaintRejected,$data);
		$data = str_replace('[[CU]]',$complaintUnproven,$data);
		$data = str_replace('[[RTG]]',$returnTheGoods,$data);
		$data = str_replace('[[DTG]]',$disposeTheGoods,$data);
		$data = str_replace('[[MSY]]',$managementReviewedYes,$data);
		$data = str_replace('[[MSN]]',$managementReviewedNo,$data);
		$data = str_replace('[[MSNA]]',$managementReviewedNa,$data);
		$data = str_replace('[[FCY]]',$flowChartYes,$data);
		$data = str_replace('[[FCN]]',$flowChartNo,$data);
		$data = str_replace('[[FCNA]]',$flowChartNa,$data);
		$data = str_replace('[[FMY]]',$fmeaYes,$data);
		$data = str_replace('[[FMN]]',$fmeaNo,$data);
		$data = str_replace('[[FMNA]]',$fmeaNa,$data);
		$data = str_replace('[[SCY]]',$customerSpecYes,$data);
		$data = str_replace('[[SCN]]',$customerSpecNo,$data);
		$data = str_replace('[[SCNA]]',$customerSpecNa,$data);

		
		
		// close file 
		fclose($fp);
		
		// print file contents 
		//print "The data in the file is \"".$data."\"";
		
		
		
		// Save the file here
		$fpSaveFile = './apps/complaints/word/files/8d-en' . $fields['id'] .  '.rtf';
		
		
		
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		fclose($fpSave);
		
		$this->addLog("8D Created");
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `documents` WHERE complaintId='" . $fields['id'] . "' AND type = '8d'");	
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `documents` (complaintId, type, date, language) VALUES(" . $fields['id'] . ", '8d', '" . common::nowDateForMysql() . "', 'en')");
		
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