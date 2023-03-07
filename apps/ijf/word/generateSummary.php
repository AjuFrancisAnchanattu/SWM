<?php

/**
 * 
 * @package intranet	
 * @subpackage Complaints
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateSummary extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		// Get all data pertaining to the IJF
		
		$datasetIJF = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `ijf` WHERE id = '" . $_REQUEST['id'] . "'");
		$fieldsIJF = mysql_fetch_array($datasetIJF);
		
		$datasetCommercialPlanning = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `commercialPlanning` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsCommercialPlanning = mysql_fetch_array($datasetCommercialPlanning);
		
		$datasetDataAdmin = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `dataAdministration` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsDataAdmin = mysql_fetch_array($datasetDataAdmin);
		
		$datasetFinance = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `finance` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsFinance = mysql_fetch_array($datasetFinance);
		
		$datasetProduction = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `production` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsProduction = mysql_fetch_array($datasetProduction);
		
		$datasetPurchasing = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `purchasing` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsPurchasing = mysql_fetch_array($datasetPurchasing);
		
		$datasetQuality = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM `quality` WHERE ijfId = '" . $_REQUEST['id'] . "'");
		$fieldsQuality = mysql_fetch_array($datasetQuality);
		
		// Set up the tick boxes
		$blankBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid291345 {\*\bkmkstart Check1} FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid291345\charrsid291345 {\*\datafield 650000001400000006436865636b3100000000000000000000000000
}{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres0}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid14165849 {\*\bkmkend Check1} ";
				
		$tickBox = "}{\field\fldpriv{\*\fldinst {\fs18\lang2057\langfe1033\langnp2057\insrsid7735582  FORMCHECKBOX }{\fs18\lang2057\langfe1033\langnp2057\insrsid4203260 {\*\datafield 650000001400000006436865636b3100010000000000000000000000}
{\*\formfield{\fftype1\ffres25\fftypetxt0\ffhps20{\*\ffname Check1}\ffdefres1}}}}{\fldrslt }}{\fs18\lang2057\langfe1033\langnp2057\insrsid7735582 {\*\bkmkend Check1}";		

		// Set up the rtf code for carriage return
		$rtfReturn = '\par';
		
		//first we tell php the filename
		$file = './apps/ijf/word/summaryTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 

		/*****************
		* Data Retrieval *
		*****************/
		
		// COMMON DATA
		$id = $_REQUEST['id'];
		$commonDate = common::nowDateForPHP();
		$commonName = usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getName();
		
		// IJF DATA
		$costedLotSize = $fieldsIJF['costedLotSize'];
		$costedLotSizeMeasurement = $fieldsIJF['costedLotSizeMeasurement'];
		$moq = $fieldsIJF['moq'];
		$initiatorInfo = usercache::getInstance()->get($fieldsIJF['initiatorInfo'])->getName();
		$initialSubmissionDate = common::transformDateForPHP($fieldsIJF['initialSubmissionDate']);
		$customerAccountNumber = $fieldsIJF['customerAccountNumber'];
		if($customerAccountNumber == "")
		{
			$customerName = $fieldsIJF['customerName'];
		}
		else 
		{
			$datasetSAP = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT * FROM `customer` WHERE `id` LIKE '" . $customerAccountNumber . "'");
			$fieldsSAP = mysql_fetch_array($datasetSAP);
			$customerName = $fieldsSAP['name'];
		}
		$customerCountry = $fieldsIJF['customerCountry'];
		$contactName = $fieldsIJF['contactName'];
		$contactPosition = $fieldsIJF['contactPosition'];
		$commodityCode = $fieldsIJF['commodityCode'];
		$contactTel = $fieldsIJF['contactTel'];
		$salesRep = $fieldsIJF['salesRep'];
		$materialGroup = $fieldsIJF['materialGroup'];
		$businessUnit = $fieldsIJF['businessUnit'];
		$materialNo = $fieldsIJF['materialNo'];
		$reasonIJF = $fieldsIJF['reasonIJF'];
		$productDescription = str_replace('<br />', $rtfReturn, nl2br($fieldsIJF['productDescription']));
		$productOwner = $fieldsIJF['productOwner'];
		$wqrAddress = $fieldsIJF['wqrAddress'];
		$wqrCity = $fieldsIJF['wqrCity'];
		$wqrCountry = $fieldsIJF['wqrCountry'];
		$wqrPostCode = $fieldsIJF['wqrPostCode'];
		$productionSite = $fieldsIJF['productionSite'];
		$width = $fieldsIJF['width_quantity'] . " " . $fieldsIJF['width_measurement'];
		$ijfLength = $fieldsIJF['ijfLength_quantity'] . " " . $fieldsIJF['ijfLength_measurement'];
		$thickness = $fieldsIJF['thickness_quantity'] . " " . $fieldsIJF['thickness_measurement'];
		$colour = $fieldsIJF['colour'];
		$alternativeLiner = $fieldsIJF['alternativeLiner'];
		$otherAlternativeLinerColour = $fieldsIJF['otherAlternativeLinerColour'];
		$specialDetails = $fieldsIJF['specialDetails'];
		$tolerances = str_replace('<br />', $rtfReturn, nl2br($fieldsIJF['tolerances']));
		$formatComments = str_replace('<br />', $rtfReturn, nl2br($fieldsIJF['formatComments']));
		$initationComments = str_replace('<br />', $rtfReturn, nl2br($fieldsIJF['comments']));
		$core = $fieldsIJF['core'];
		$sellingUOM = $fieldsIJF['sellingUOM'];
		$annualQuantityUOM = $fieldsIJF['annualQuantityUOM'];
		$firstOrderQuantityUOM = $fieldsIJF['firstOrderQuantityUOM'];
		$targetPrice = $fieldsIJF['targetPrice'] . " " . $fieldsIJF['currency'];
		$potentialComments = $fieldsIJF['potentialComments'];
		$puSapPartNumber = $fieldsIJF['puSapPartNumber'];
		// IJF Tick boxes
		$barManViewNo = $fieldsIJF['barManView'] == "no" ? "$tickBox" : "$blankBox";
		$barManViewYes = $fieldsIJF['barManView'] != "no" ? "$tickBox" : "$blankBox";
		$barManViewCompleteNo = $fieldsIJF['barManViewComplete'] == "no" ? "$tickBox" : "$tickBox";
		$barManViewCompleteYes = $fieldsIJF['barManViewComplete'] != "no" ? "$tickBox" : "$blankBox";
		$existingCustomerNo = $fieldsIJF['existingCustomer'] == "no" ? "$tickBox" : "$blankBox";
		$existingCustomerYes = $fieldsIJF['existingCustomer'] != "no" ? "$tickBox" : "$blankBox";
		$wordQuoteReqNo = $fieldsIJF['wordQuoteReq'] == "no" ? "$tickBox" : "$blankBox";
		$wordQuoteReqYes = $fieldsIJF['wordQuoteReq'] != "no" ? "$tickBox" : "$blankBox";
		$linerFilm = $fieldsIJF['liner'] == "film" ? "$tickBox" : "$blankBox";
		$linerPaper = $fieldsIJF['liner'] == "paper" ? "$tickBox" : "$blankBox";
		$linerOther = $fieldsIJF['liner'] == "other" ? "$tickBox" : "$blankBox";
		$doubleSidedNo = $fieldsIJF['doubleSided'] == "no" ? "$tickBox" : "$blankBox";
		$doubleSidedYes = $fieldsIJF['doubleSided'] != "no" ? "$tickBox" : "$blankBox";
		$doubleSidedOptionsStandard = $fieldsIJF['doubleSidedOptions'] == "standard" ? "$tickBox" : "$blankBox";
		$doubleSidedOptionsSpecial = $fieldsIJF['doubleSidedOptions'] == "special" ? "$tickBox" : "$blankBox";
		
		// COMMERCIAL PLANNING DATA
		$commercialPlanningCommentsComplete = str_replace('<br />', $rtfReturn, nl2br($fieldsIJF['commercialPlanningCommentsComplete']));
		// Commercial Planning Tick boxes
		$acceptedRejectedAccepted = $fieldsCommercialPlanning['acceptedRejected'] == "accepted" ? "$tickBox" : "$blankBox";
		$acceptedRejectedRejected = $fieldsCommercialPlanning['acceptedRejected'] == "rejected" ? "$tickBox" : "$blankBox";
		$acceptedRejectedNeither = $fieldsCommercialPlanning['acceptedRejected'] == "neither" ? "$tickBox" : "$blankBox";
			
		// DATA ADMINISTRATION DATA
		$daSapPartNumber = $fieldsDataAdmin['daSapPartNumber'];
		$wipPartNumbers = $fieldsDataAdmin['wipPartNumbers'];
		$dataAdminComments = str_replace('<br />', $rtfReturn, nl2br($fieldsDataAdmin['dataAdminComments']));
		$locationOwnerDA = $fieldsDataAdmin['location_ownerDA'];
		$dataAdministrationOwner = usercache::getInstance()->get($fieldsDataAdmin['dataAdministration_owner'])->getName();
		
		// FINANCE DATA
		$smc = $fieldsFinance['smc'];
		$currency1 = $fieldsFinance['currency1'];
		$smcPerUnit = $fieldsFinance['smc_per_unit'];
		$smcUnitOfMeasurement = $fieldsFinance['smc_unit_of_measurement'];
		$intercoPrice = $fieldsFinance['intercoPrice'];
		$currency2 = $fieldsFinance['currency2'];
		$intercoPerUnit = $fieldsFinance['interco_per_unit'];
		$intercoUnitOfMeasurement = $fieldsFinance['interco_unit_of_measurement'];
		$financeComments = str_replace('<br />', $rtfReturn, nl2br($fieldsFinance['financeComments']));
		$fLocationOwner = $fieldsFinance['location_owner'];
		$FinanceOwner = usercache::getInstance()->get($fieldsFinance['finance_owner'])->getName();
		
		// PURCHASING DATA
		$purDescription = $fieldsPurchasing['description'];
		$commodityCodeCountry = $fieldsPurchasing['commodityCodeCountry'];
		$leadTime = $fieldsPurchasing['leadTime'];
		$price = $fieldsPurchasing['price'] . " " . $fieldsPurchasing['currencyPurchasing'];
		$freightDutyInformation = $fieldsPurchasing['freightDutyInformation'];
		$purComments = $fieldsPurchasing['comments'];
		$purLocationOwner = $fieldsPurchasing['location_owner'];
		$purchasingOwner = usercache::getInstance()->get($fieldsPurchasing['purchasing_owner'])->getName();
		
		// PRODUCTION
		$testingComments = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['testingRequiredComments']));
		$minimumOrderQuantity = $fieldsProduction['minimumOrderQuantity'];
		$sugCostedLotSize = $fieldsProduction['sugCostedLotSize'];
		$toolsComments = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['toolsComments']));
		$packagingRequiredComments = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['packagingRequiredComments']));
		$cartonQuantity = $fieldsProduction['cartonQuantity'];
		$cartonsPerLayer = $fieldsProduction['cartonsPerLayer'];
		$layersPerPallet = $fieldsProduction['layersPerPallet'];
		$palletQuantity = $fieldsProduction['palletQuantity'];
		$extraCartonSpecification = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['extraCartonSpecification']));
		$specificCarton = $fieldsProduction['specificCarton'];
		$palletSpecification = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['palletSpecification']));
		$barcodeType = $fieldsProduction['barcodeType'];
		$barcodeRequired = $fieldsProduction['barcodeRequired'];
		$barcodeType = $fieldsProduction['barcodeType'];
		$barcodeRequired = $fieldsProduction['barcodeRequired'];
		$labellingSpecificationComments = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['labellingSpecificationComments']));
		$routing = $fieldsProduction['routing'];
		$setUpTime = $fieldsProduction['setUpTime'];
		$quantityPerHour = $fieldsProduction['quantityPerHour'];
		$inputMaterialRequired = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['inputMaterialRequired']));
		$specialInstructions = str_replace('<br />', $rtfReturn, nl2br($fieldsProduction['specialInstructions']));
		$location_owner_pro = $fieldsProduction['location_owner'];
		$production_owner = usercache::getInstance()->get($fieldsProduction['production_owner'])->getName();
		// Production Tick Boxes
		$testingRequiredYes = $fieldsProduction['testingRequired'] != "no" ? "$tickBox" : "$blankBox";
		$testingRequiredNo = $fieldsProduction['testingRequired'] == "no" ? "$tickBox" : "$blankBox";
		$toolsRequiredYes = $fieldsProduction['toolsRequired'] != "no" ? "$tickBox" : "$blankBox";
		$toolsRequiredNo = $fieldsProduction['toolsRequired'] == "no" ? "$tickBox" : "$blankBox";
		$viableYes = $fieldsProduction['viable'] != "no" ? "$tickBox" : "$blankBox";
		$viableNo = $fieldsProduction['viable'] == "no" ? "$tickBox" : "$blankBox";
		$specialPackagingYes = $fieldsProduction['packagingRequired'] != "no" ? "$tickBox" : "$blankBox";
		$specialPackagingNo = $fieldsProduction['packagingRequired'] == "no" ? "$tickBox" : "$blankBox";
		$labellingSpecificationYes = $fieldsProduction['labellingSpecification'] != "no" ? "$tickBox" : "$blankBox";
		$labellingSpecificationNo = $fieldsProduction['labellingSpecification'] == "no" ? "$tickBox" : "$blankBox";
		$newItemToBePurchasedYes = $fieldsProduction['newItemToBePurchased'] != "no" ? "$tickBox" : "$blankBox";
		$newItemToBePurchasedNo = $fieldsProduction['newItemToBePurchased'] == "no" ? "$tickBox" : "$blankBox";
		
		//QUALITY
		$qualityComments = $fieldsQuality['qualityComments'];
		$quallocOwner = $fieldsQuality['location_owner'];
		$qualityOwner = $fieldsQuality['quality_owner'];
		
		/****************
		* Replace Overs *
		****************/
		
		// COMMON REPLACE OVERS
		$data = str_replace('[[COMMONDATE]]',$commonDate,$data);
		$data = str_replace('[[ID]]',$id,$data);
		$data = str_replace('[[COMMONNAME]]',$commonName,$data);
		
		//IJF REPLACE OVERS
		$data = str_replace('[[INITIATOR]]',replaceForeignCharsForRTF($initiatorInfo),$data);
		$data = str_replace('[[CREATEDDATE]]',replaceForeignCharsForRTF($initialSubmissionDate),$data);
		$data = str_replace('[[ECY]]',replaceForeignCharsForRTF($existingCustomerYes),$data);
		$data = str_replace('[[ECN]]',replaceForeignCharsForRTF($existingCustomerNo),$data);
		$data = str_replace('[[CUSTACCNO]]',replaceForeignCharsForRTF($customerAccountNumber),$data);
		$data = str_replace('[[CUSTNAME]]',replaceForeignCharsForRTF($customerName),$data);
		$data = str_replace('[[CUSTCOUNTRY]]',replaceForeignCharsForRTF($customerCountry),$data);
		$data = str_replace('[[CONTNAME]]',replaceForeignCharsForRTF($contactName),$data);
		$data = str_replace('[[CONTPOS]]',replaceForeignCharsForRTF($contactPosition),$data);
		$data = str_replace('[[CONTTEL]]',replaceForeignCharsForRTF($contactTel),$data);
		$data = str_replace('[[SALESREP]]',replaceForeignCharsForRTF($salesRep),$data);
		$data = str_replace('[[MATGROUP]]',replaceForeignCharsForRTF($materialGroup),$data);
		$data = str_replace('[[BUSIUNIT]]',replaceForeignCharsForRTF($businessUnit),$data);
		$data = str_replace('[[MATNO]]',replaceForeignCharsForRTF($materialNo),$data);
		$data = str_replace('[[REASONIJF]]',replaceForeignCharsForRTF($reasonIJF),$data);
		$data = str_replace('[[DESCRIPTION]]',replaceForeignCharsForRTF($productDescription),$data);
		$data = str_replace('[[PRODOWNER]]',replaceForeignCharsForRTF($productOwner),$data);
		$data = str_replace('[[WQRY]]',replaceForeignCharsForRTF($wordQuoteReqYes),$data);
		$data = str_replace('[[WQRN]]',replaceForeignCharsForRTF($wordQuoteReqNo),$data);
		$data = str_replace('[[WADRESS]]',replaceForeignCharsForRTF($wqrAddress),$data);
		$data = str_replace('[[WCITY]]',replaceForeignCharsForRTF($wqrCity),$data);
		$data = str_replace('[[WCOUNTRY]]',replaceForeignCharsForRTF($wqrCountry),$data);
		$data = str_replace('[[WPOSTCODE]]',replaceForeignCharsForRTF($wqrPostCode),$data);
		$data = str_replace('[[PRODSITE]]',replaceForeignCharsForRTF($productionSite),$data);
		$data = str_replace('[[WIDTH]]',replaceForeignCharsForRTF($width),$data);
		$data = str_replace('[[LENGTH]]',replaceForeignCharsForRTF($ijfLength),$data);
		$data = str_replace('[[THICKNESS]]',replaceForeignCharsForRTF($thickness),$data);
		$data = str_replace('[[COLOUR]]',replaceForeignCharsForRTF($colour),$data);
		$data = str_replace('[[LFILM]]',replaceForeignCharsForRTF($linerFilm),$data);
		$data = str_replace('[[LPAPER]]',replaceForeignCharsForRTF($linerPaper),$data);
		$data = str_replace('[[LOTHER]]',replaceForeignCharsForRTF($linerOther),$data);
		$data = str_replace('[[ALTLINER]]',replaceForeignCharsForRTF($alternativeLiner),$data);
		$data = str_replace('[[SPECLINERCOL]]',replaceForeignCharsForRTF($otherAlternativeLinerColour),$data);
		$data = str_replace('[[DSY]]',replaceForeignCharsForRTF($doubleSidedYes),$data);
		$data = str_replace('[[DSN]]',replaceForeignCharsForRTF($doubleSidedNo),$data);
		$data = str_replace('[[DSOST]]',replaceForeignCharsForRTF($doubleSidedOptionsStandard),$data);
		$data = str_replace('[[DSOSP]]',replaceForeignCharsForRTF($doubleSidedOptionsSpecial),$data);
		$data = str_replace('[[DSSPECDET]]',replaceForeignCharsForRTF($specialDetails),$data);
		$data = str_replace('[[TOLERANCES]]',replaceForeignCharsForRTF($tolerances),$data);
		$data = str_replace('[[FORMATCOMMENTS]]',replaceForeignCharsForRTF($formatComments),$data);
		$data = str_replace('[[IJFCOMMENTS]]',replaceForeignCharsForRTF($initationComments),$data);
		$data = str_replace('[[CORE]]',replaceForeignCharsForRTF($core),$data);
		$data = str_replace('[[SELLINGUOM]]',replaceForeignCharsForRTF($sellingUOM),$data);
		$data = str_replace('[[ANNUALQUANTITY]]',replaceForeignCharsForRTF($annualQuantityUOM),$data);
		$data = str_replace('[[1ORDERQUANTITY]]',replaceForeignCharsForRTF($firstOrderQuantityUOM),$data);
		$data = str_replace('[[TARGETPRICE]]',replaceForeignCharsForRTF($targetPrice),$data);
		$data = str_replace('[[POTCOMMENTS]]',replaceForeignCharsForRTF($potentialComments),$data);
		
		// DATA ADMIN REPLACE OVERS
		$data = str_replace('[[DASAPPARTNUMBER]]',replaceForeignCharsForRTF($daSapPartNumber),$data);
		$data = str_replace('[[WIPPARTNUMBERS]]',replaceForeignCharsForRTF($wipPartNumbers),$data);
		$data = str_replace('[[MOQ]]',replaceForeignCharsForRTF($moq),$data);
		$data = str_replace('[[COMMODITYCODE]]',replaceForeignCharsForRTF($commodityCode),$data);
		$data = str_replace('[[BMVY]]',replaceForeignCharsForRTF($barManViewYes),$data);
		$data = str_replace('[[BMVN]]',replaceForeignCharsForRTF($barManViewNo),$data);
		$data = str_replace('[[BMVCY]]',replaceForeignCharsForRTF($barManViewCompleteYes),$data);
		$data = str_replace('[[BMVCN]]',replaceForeignCharsForRTF($barManViewCompleteNo),$data);
		$data = str_replace('[[BARMANVIEW]]',replaceForeignCharsForRTF($barManView),$data);
		$data = str_replace('[[BARMANVIEWCOMPLETE]]',replaceForeignCharsForRTF($barManViewComplete),$data);
		$data = str_replace('[[LOCATIONOWNERDA]]',replaceForeignCharsForRTF($locationOwnerDA),$data);
		$data = str_replace('[[DATAADMINISTRATIONOWNER]]',replaceForeignCharsForRTF($dataAdministrationOwner),$data);
		$data = str_replace('[[DATAADMINCOMMENTS]]',replaceForeignCharsForRTF($dataAdminComments),$data);
		
		// PRODUCTION REPLACE OVERS
		$data = str_replace('[[TRY]]',replaceForeignCharsForRTF($testingRequiredYes),$data);
		$data = str_replace('[[TRN]]',replaceForeignCharsForRTF($testingRequiredNo),$data);
		$data = str_replace('[[TESTINGCOMMENTS]]',replaceForeignCharsForRTF($testingComments),$data);
		$data = str_replace('[[TORY]]',replaceForeignCharsForRTF($toolsRequiredYes),$data);
		$data = str_replace('[[TORN]]',replaceForeignCharsForRTF($toolsRequiredNo),$data);
		$data = str_replace('[[VY]]',replaceForeignCharsForRTF($viableYes),$data);
		$data = str_replace('[[VN]]',replaceForeignCharsForRTF($viableNo),$data);
		$data = str_replace('[[MINORDQUAN]]',replaceForeignCharsForRTF($minimumOrderQuantity),$data);
		$data = str_replace('[[SUGCOSLOTSIZE]]',replaceForeignCharsForRTF($sugCostedLotSize),$data);
		$data = str_replace('[[TOOLSCOMMENTS]]',replaceForeignCharsForRTF($toolsComments),$data);
		$data = str_replace('[[SPRY]]',replaceForeignCharsForRTF($specialPackagingYes),$data);
		$data = str_replace('[[SPRN]]',replaceForeignCharsForRTF($specialPackagingNo),$data);
		$data = str_replace('[[PACKAGINGCOMMENTS]]',replaceForeignCharsForRTF($packagingRequiredComments),$data);
		$data = str_replace('[[CARQUA]]',replaceForeignCharsForRTF($cartonQuantity),$data);
		$data = str_replace('[[CARPERLAY]]',replaceForeignCharsForRTF($cartonsPerLayer),$data);
		$data = str_replace('[[PALQUANT]]',replaceForeignCharsForRTF($palletQuantity),$data);
		$data = str_replace('[[LAYPERPAL]]',replaceForeignCharsForRTF($layersPerPallet),$data);
		$data = str_replace('[[SPECIFICCARTON]]',replaceForeignCharsForRTF($specificCarton),$data);
		$data = str_replace('[[EXTRACARSPEC]]',replaceForeignCharsForRTF($extraCartonSpecification),$data);
		$data = str_replace('[[PALLETSPEC]]',replaceForeignCharsForRTF($palletSpecification),$data);
		$data = str_replace('[[BARTYPE]]',replaceForeignCharsForRTF($barcodeType),$data);
		$data = str_replace('[[BARCODE]]',replaceForeignCharsForRTF($barcodeRequired),$data);
		$data = str_replace('[[LSY]]',replaceForeignCharsForRTF($labellingSpecificationYes),$data);
		$data = str_replace('[[LSN]]',replaceForeignCharsForRTF($labellingSpecificationNo),$data);
		$data = str_replace('[[LABELCOMMENTS]]',replaceForeignCharsForRTF($labellingSpecificationComments),$data);
		$data = str_replace('[[ROUTING]]',replaceForeignCharsForRTF($routing),$data);
		$data = str_replace('[[SETUPTIME]]',replaceForeignCharsForRTF($setUpTime),$data);
		$data = str_replace('[[QUANTITYPERHOUR]]',replaceForeignCharsForRTF($quantityPerHour),$data);
		$data = str_replace('[[IMPUTMETREQ]]',replaceForeignCharsForRTF($inputMaterialRequired),$data);
		$data = str_replace('[[SPECIALINST]]',replaceForeignCharsForRTF($specialInstructions),$data);
		$data = str_replace('[[NIY]]',replaceForeignCharsForRTF($newItemToBePurchasedYes),$data);
		$data = str_replace('[[NIN]]',replaceForeignCharsForRTF($newItemToBePurchasedNo),$data);
		$data = str_replace('[[PROLOCATIONOWNER]]',replaceForeignCharsForRTF($location_owner_pro),$data);
		$data = str_replace('[[PRODUCTIONOWNER]]',replaceForeignCharsForRTF($production_owner),$data);
		
		// PURCHASING REPLACE OVERS
		$data = str_replace('[[PUSAPPARTNO]]',replaceForeignCharsForRTF($puSapPartNumber),$data);
		$data = str_replace('[[PURDESCRIPTION]]',replaceForeignCharsForRTF($purDescription),$data);
		$data = str_replace('[[MOQ]]',replaceForeignCharsForRTF($moq),$data);
		$data = str_replace('[[COMMCODE]]',replaceForeignCharsForRTF($commodityCode),$data);
		$data = str_replace('[[COUNORIGIN]]',replaceForeignCharsForRTF($commodityCodeCountry),$data);
		$data = str_replace('[[LEADTIME]]',replaceForeignCharsForRTF($leadTime),$data);
		$data = str_replace('[[PRICE]]',replaceForeignCharsForRTF($price),$data);
		$data = str_replace('[[FREIGHTDUTYINFO]]',replaceForeignCharsForRTF($freightDutyInformation),$data);
		$data = str_replace('[[PURCOMMENTS]]',replaceForeignCharsForRTF($purComments),$data);
		$data = str_replace('[[PURLOCATION_OWNER]]',replaceForeignCharsForRTF($purLocationOwner),$data);
		$data = str_replace('[[PURCHASING_OWNER]]',replaceForeignCharsForRTF($purchasingOwner),$data);

		// FINANCE REPLACE OVERS
		$data = str_replace('[[DASAPPARTNO]]',replaceForeignCharsForRTF($daSapPartNumber),$data);
		$data = str_replace('[[SMC]]',replaceForeignCharsForRTF($smc),$data);
		$data = str_replace('[[CURRENCY1]]',replaceForeignCharsForRTF($currency1),$data);
		$data = str_replace('[[SMC_PER_UNIT]]',replaceForeignCharsForRTF($smcPerUnit),$data);
		$data = str_replace('[[SMC_UNIT_OF_MEASUREMENT]]',replaceForeignCharsForRTF($smcUnitOfMeasurement),$data);
		$data = str_replace('[[INTERCOPRICE]]',replaceForeignCharsForRTF($intercoPrice),$data);
		$data = str_replace('[[CURRENCY2]]',replaceForeignCharsForRTF($currency2),$data);
		$data = str_replace('[[INTERCO_PER_UNIT]]',replaceForeignCharsForRTF($intercoPerUnit),$data);
		$data = str_replace('[[INTERCO_UNIT_OF_MEASUREMENT]]',replaceForeignCharsForRTF($intercoUnitOfMeasurement),$data);
		$data = str_replace('[[COSTEDLOTSIZE]]',replaceForeignCharsForRTF($costedLotSize),$data);
		$data = str_replace('[[COSTEDLOTSIZEMEASUREMENT]]',replaceForeignCharsForRTF($costedLotSizeMeasurement),$data);
		$data = str_replace('[[FINANCECOMMENTS]]',replaceForeignCharsForRTF($financeComments),$data);
		$data = str_replace('[[FLOCATION_OWNER]]',replaceForeignCharsForRTF($fLocationOwner),$data);
		$data = str_replace('[[FINANCE_OWNER]]',replaceForeignCharsForRTF($FinanceOwner),$data);
		
		// COMMERCIAL PLANNING REPLACE OVERS
		$data = str_replace('[[ACCEPTED]]',replaceForeignCharsForRTF($acceptedRejectedAccepted),$data);
		$data = str_replace('[[REJECTED]]',replaceForeignCharsForRTF($acceptedRejectedRejected),$data);
		$data = str_replace('[[NEITHER]]',replaceForeignCharsForRTF($acceptedRejectedNeither),$data);
		$data = str_replace('[[CPCOMMENTS]]',replaceForeignCharsForRTF($commercialPlanningCommentsComplete),$data);

		// QUALITY REPLACE OVERS
		$data = str_replace('[[QCOMMENTS]]', replaceForeignCharsForRTF(str_replace("<br />", " \par ", nl2br($qualityComments))), $data);
		$data = str_replace('[[QUALLOC_OWNER]]', replaceForeignCharsForRTF($quallocOwner), $data);
		$data = str_replace('[[QUALITY_OWNER]]', replaceForeignCharsForRTF($qualityOwner), $data);
		
		
		
		// close file 
		fclose($fp);
		
		// Save the file here
		$fpSaveFile = './apps/ijf/word/files/ijfComplete' . $_REQUEST['id'] .  '.rtf';
				
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		$this->addLog("IJF Complete Document Generated");
		
		// Close the File
		fclose($fpSave);
		
		// Insert entrys into the database ...
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM `documents` WHERE ijfId = '" . $_REQUEST['id'] . "' AND name = 'completeDocument'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("INSERT INTO `documents` (ijfId, name, date) VALUES(" . $_REQUEST['id'] . ", 'completeDocument', '" . common::nowDateForMysql() . "')");
		
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