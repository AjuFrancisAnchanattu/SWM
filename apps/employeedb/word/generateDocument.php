<?php

/**
 * 
 * @package intranet	
 * @subpackage Employee DB
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/05/2007
 */

class generateDocument extends page
{	
	function __construct()
	{		
		$this->generateWordDocument();
	}		
	
	public function generateWordDocument()
	{
		
		$dataset = mysql::getInstance()->selectDatabase("employeedb")->Execute("SELECT * FROM `employee` WHERE `id` = '" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		
		//die($fields['id']);
		
		//first we tell php the filename
		$file = './apps/employeedb/word/empTemplate.rtf';
		
		// then we try to open the file, using the r mode, for reading only 
		$fp = fopen($file, 'rb') or die('Couldn\'t open file!'); 
		
		// read file contents 
		$data = fread($fp, filesize($file)) or die('Couldn\'t read file!'); 
		
		$todaysDate = $today = date("d/m/Y",time());
		
		// Populate Fields - Personal Details		
		$firstName = $fields['firstName'];
		$lastName = $fields['lastName'];
		$middleName1 = $fields['middleName1'];
		$knownAs = $fields['name'];
		$dateOfBirth = common::transformDateForPHP($fields['dateOfBirth']);
		$gender = $fields['gender'];
		$nationality = $fields['nationality'];
		$address1 = $fields['address1'];
		$address2 = $fields['address2'];
		$address3 = $fields['city'];
		$address4 = $fields['county'];
		$postcode = $fields['postcode'];
		$country = $fields['country'];
		$homeTelephone = $fields['homeTelephone'];
		$personalMobileNumber = $fields['personalMobileNumber'];
		$contactPerson = $fields['contactPerson'];
		$relationship = $fields['relationship'];
		$nextOfKinContactDetails = $fields['nextOfKinContactDetails'];
		$personnelFile = $fields['personnelFile'];
		$scapaPhoneNumber = $fields['scapaPhoneNumber'];
		$scapaFaxNumber = $fields['scapaFaxNumber'];
		$scapaMobileNumber = $fields['scapaMobileNumber'];
		$languagesSpoken = $fields['languagesSpoken'];
		
		// Populate Fields - Current Job Role		
		$currentPositionFrom = $fields['currentPositionFrom'];
		$jobType = $fields['jobType'];
		$partTimePercentage = $fields['partTimePercentage'];
		$jobLength = $fields['jobLength'];
		$jobRole = $fields['jobRole'];
		$jobTitle = $fields['jobTitle'];
		$department = $fields['department'];
		$managerResponsible = $fields['managerResponsible'];
		$SETResponsible = $fields['SETResponsible'];
		$workLocation = $fields['workLocation'];
		$payrollLocation = $fields['payrollLocation'];
		$appraisalDate = $fields['appraisalDate'];
		$appraisalBy = $fields['appraisalBy'];
		$jobDescriptionReviewDate = $fields['jobDescriptionReviewDate'];
		$salaryReviewDate = $fields['salaryReviewDate'];
		$costCentre = $fields['costCentre'];		
		$noticeCompany = $fields['noticeCompany'];
		$noticeEmployee = $fields['noticeEmployee'];
		$bonusPlan = $fields['bonusPlan'];
		$schemePercent = $fields['schemePercent'];
		$pensionScheme = $fields['pensionScheme'];
		$companyCar = $fields['companyCar'];
		
		// Populate Fields - Employment History		
		$employmentStartDate = $fields['employmentStartDate'];
		$continuousServiceDate = $fields['continuousServiceDate'];
		$employmentStatus = $fields['employmentStatus'];
		$inductionLevel = $fields['inductionLevel'];
		$inductionDate = $fields['inductionDate'];
		$probationPeriod = $fields['probationPeriod'];
		$probationFromDate = $fields['probationFromDate'];
		$probationToDate = $fields['probationToDate'];
		$estimatedRetirementDate = $fields['estimatedRetirementDate'];
		$positionAppliedFor = $fields['positionsAppliedFor'];
		$previousEmployers = $fields['previousEmployers'];
		$educationLevel = $fields['educationLevel'];
		$formalQualifications = $fields['formalQualifications'];
		
		// Populate Fields - IT Details		
		$networkLogonRequired = $fields['networkLogonRequired'];
		
		// Populate Fields - Asset Details		
		$companyCarIssuedDate = $fields['companyCarIssuedDate'];
		$companyCarRegistration = $fields['companyCarRegistration'];
		$companyCarContractDistance = $fields['companyCarContractDistance_quantity'] . " " . $fields['companyCarContractDistance_measurement'];
		$companyCarContractReturnDate = $fields['companyCarContractReturnDate'];
		$companyCarReturnedDate = $fields['companyCarReturnedDate'];
		$mobilesPhoneIssuedDate = $fields['mobilesPhoneIssuedDate'];
		$mobilesPhoneReturnDate = $fields['mobilesPhoneReturnedDate'];
		$officeKeysIssuedDate = $fields['officeKeysIssuedDate'];
		$officeKeysReturnedDatentDate = $fields['officeKeysReturnedDate'];
		$gateKeysIssuedDate = $fields['gateKeysIssuedDate'];
		$gateKeysReturnedDate = $fields['gateKeysReturnedDate'];
		$creditCardIssuedDate = $fields['creditCardIssuedDate'];
		$creditCardDetails = $fields['creditCardDetails'];
		$creditCardReturnedDate = $fields['creditCardReturnedDate'];
		$fuelCardIssuedDate = $fields['fuelCardIssuedDate'];
		$fueldCardReturnedDate = $fields['fuelCardReturnedDate'];
		$permanentExpenseAdvanceIssuedDate = $fields['permanentExpenseAdvanceIssuedDate'];
		$permanentExpenseAdvanceAmount = $fields['permanentExpenseAdvanceAmmount_quantity'] . " " . $fields['permanentExpenseAdvanceAmmount_measurement'];
		$permanentExpenseAdvanceReturnedDate = $fields['permanentExpenseAdvanceReturnedDate'];
		
		// Populate Fields - PPE and HSE Training		
		$nextReviewDateForHSTraining = $fields['nextReviewDateForHSTraining'];
		$baseHSTraining = $fields['baseHSTraining'] = 0 ? "Yes" : "No" ;
		$directoryHSTraining = $fields['directorsHSTraining'];
		$forkliftTruckLicence = $fields['forkliftTruckLicense'];
		$firstAidTraining = $fields['firstAidTraining'] = 0 ? "Yes" : "No" ;
		$safetyShoes = $fields['safetyShoes'] = 0 ? "Yes" : "No" ;
		$safetyGlasses = $fields['safetyGlasses'] = 0 ? "Yes" : "No" ;
		$earDefenders = $fields['earDefenders'] = 0 ? "Yes" : "No" ;
		$safetyHelmets = $fields['safetyHelmets'] = 0 ? "Yes" : "No" ;
		$highVisJackets = $fields['highVisJackets'] = 0 ? "Yes" : "No" ;
		
		
		// Populate Fields - Personal Details
		$data = str_replace('[[[DATE]]]',$todaysDate,$data);
		$data = str_replace('[[[FIRSTNAME]]]',$firstName,$data);
		$data = str_replace('[[[LASTNAME]]]',$lastName,$data);
		$data = str_replace('[[[MIDDLENAME]]]',$middleName1,$data);
		$data = str_replace('[[[KNOWNAS]]]',$knownAs,$data);
		$data = str_replace('[[[DOB]]]',$dateOfBirth,$data);
		$data = str_replace('[[[GENDER]]]',$gender,$data);
		$data = str_replace('[[[COUNTRYOFORIGIN]]]',$nationality,$data);
		$data = str_replace('[[[ADDRESSONE]]]',$address1,$data);
		$data = str_replace('[[[ADDRESSTWO]]]',$address2, $data);
		$data = str_replace('[[[CITY]]]',$address3,$data);
		$data = str_replace('[[[COUNTY]]]',$address4,$data);
		$data = str_replace('[[[POSTCODE]]]',$postcode,$data);
		$data = str_replace('[[[POSTCODE]]]',$postcode,$data);
		$data = str_replace('[[[COUNTRY]]]',$country,$data);
		$data = str_replace('[[[HOMETELEPHONE]]]',$homeTelephone,$data);
		$data = str_replace('[[[PERSONALMOBILENUMBER]]]',$personalMobileNumber,$data);
		$data = str_replace('[[[CONTACTPERSONSNAME]]]',$contactPerson,$data);
		$data = str_replace('[[[RELATIONSHIP]]]',$relationship,$data);
		$data = str_replace('[[[NEXTOFKIN]]]',$nextOfKinContactDetails,$data);
		$data = str_replace('[[[PERSONNELFILE]]]',$personnelFile,$data);
		$data = str_replace('[[[SCAPAPHONENUMBER]]]',$scapaPhoneNumber,$data);
		$data = str_replace('[[[SCAPAFAXNUMBER]]]',$scapaFaxNumber,$data);
		$data = str_replace('[[[SCAPAMOBILENUMBER]]]',$scapaMobileNumber,$data);
		$data = str_replace('[[[LANGUAGESSPOKEN]]]',$languagesSpoken,$data);
		
		// Populate Fields - Current Job Role
		$data = str_replace('CURRECTPOSITIONFROM',$currentPositionFrom,$data);
		$data = str_replace('JOBTYPE',$jobType,$data);
		$data = str_replace('FIRSTNAME',$partTimePercentage,$data);
		$data = str_replace('JOBLENGTH',$jobLength,$data);
		$data = str_replace('JOBROLE',$jobRole,$data);
		$data = str_replace('JOBTITLE',$jobTitle,$data);
		$data = str_replace('DEPARTMENT',$department,$data);
		$data = str_replace('MANAGERRESONSIBLE',$managerResponsible,$data);
		$data = str_replace('SETRESPONSIBLE',$SETResponsible,$data);
		$data = str_replace('WORKLOCATION',$workLocation,$data);
		$data = str_replace('PAYROLLLOCATION',$payrollLocation, $data);
		$data = str_replace('LASTAPPRASISALDATE',$appraisalDate,$data);
		$data = str_replace('APPRASIEDBY',$appraisalBy,$data);
		$data = str_replace('JOBDESCRIPTIONREVIEWDATE',$jobDescriptionReviewDate,$data);
		$data = str_replace('LASTSALARYREVIEWDATE',$salaryReviewDate,$data);
		$data = str_replace('COSTCENTRE',$costCentre,$data);
		$data = str_replace('NOTICECOMPANY',$noticeCompany,$data);
		$data = str_replace('NOTICEEMPLOYEE',$noticeEmployee,$data);
		$data = str_replace('BONUSPLANPERCENTAGE',$schemePercent,$data);
		$data = str_replace('BONUSPLAN',$bonusPlan,$data);
		$data = str_replace('PENSIONSCHEME',$pensionScheme,$data);
		$data = str_replace('COMPANYCAR',$companyCar,$data);
		
		// Populate Fields - Employment History
		$data = str_replace('STARTDATEWITHCOMPANY',$employmentStartDate,$data);
		$data = str_replace('CONTINUOUSSERVICEDATE',$continuousServiceDate,$data);
		$data = str_replace('EMPLOYMENTSTATUS',$employmentStatus,$data);
		$data = str_replace('INDUCTIONLEVEL',$inductionLevel,$data);
		$data = str_replace('INDUCTIONDATE',$inductionDate,$data);
		$data = str_replace('PROBATIONPERIOD',$probationPeriod,$data);
		$data = str_replace('PROBATIONFROMDATE',$probationFromDate,$data);
		$data = str_replace('PROBATIONTODATE',$probationToDate,$data);
		$data = str_replace('ESTIMATEDRETIREMENTDATE',$estimatedRetirementDate,$data);
		$data = str_replace('POSTITIONAPPLIEDFOR',$positionAppliedFor,$data);
		$data = str_replace('PREVIOUSEMPLOYERS',$previousEmployers, $data);
		$data = str_replace('EDUCATIONLEVEL',$educationLevel,$data);
		$data = str_replace('FORMALQUALIFICATIONS',$formalQualifications,$data);
		
		// Populate Fields - IT Details
		$data = str_replace('NETWORKLOGONREQUIRED',$networkLogonRequired,$data);
		
		// Populate Fields - Asset Details
		$data = str_replace('0ISSUEDDATE',$companyCarIssuedDate,$data);
		$data = str_replace('0REGISTRATION',$companyCarRegistration ,$data);
		$data = str_replace('0CONTRACTDISTANCE',$companyCarContractDistance ,$data);
		$data = str_replace('0CONTRACTRETURNDATE',$companyCarContractReturnDate,$data);
		//$data = str_replace('0RETURNEDDATE',$mobilesPhoneReturnDate,$data);
		$data = str_replace('MOBILEPHONEISSUED',$mobilesPhoneIssuedDate,$data);
		$data = str_replace('MOBILEPHONERETURNEDDATE',$mobilesPhoneReturnDate,$data);
		$data = str_replace('OFFICEKEYSISSUEDDATE',$officeKeysIssuedDate,$data);
		$data = str_replace('OFFICEKEYSRETURNEDDATE',$officeKeysReturnedDatentDate,$data);
		$data = str_replace('GATEKEYSISSUEDDATE',$gateKeysIssuedDate,$data);
		$data = str_replace('GATEKEYSRETURNDATE',$gateKeysReturnedDate,$data);
		$data = str_replace('CREDITCARDISSUEDDATE',$creditCardIssuedDate,$data);
		$data = str_replace('CREDITCARDDETAILS',$creditCardDetails,$data);
		$data = str_replace('CREDITCARDRETURNDATE',$creditCardReturnedDate,$data);
		$data = str_replace('FUELDCARDISSUEDDATE',$fuelCardIssuedDate,$data);
		$data = str_replace('FUELCARDRETURNEDDATE',$fueldCardReturnedDate,$data);
		$data = str_replace('PERMANENTEXPENSEISSUEDDATE',$permanentExpenseAdvanceIssuedDate,$data);
		$data = str_replace('PERMANENTEXPENSEAMOUNT',$permanentExpenseAdvanceAmount,$data);
		$data = str_replace('PERMANENTEXPENSERETURNEDDATE',$permanentExpenseAdvanceReturnedDate,$data);
		
		// Populate Fields - PPE and HSE
		$data = str_replace('NEXTREVIEWDATE',$nextReviewDateForHSTraining,$data);
		$data = str_replace('BASEHSTRAINING',$baseHSTraining ,$data);
		$data = str_replace('DIRECTORSHSTRAINING',$directoryHSTraining,$data);
		$data = str_replace('FORKLIFTTRAINING',$forkliftTruckLicence,$data);
		$data = str_replace('FIRSTAIDTRAINING',$firstAidTraining,$data);
		$data = str_replace('SAFETYSHOES',$safetyShoes,$data);
		$data = str_replace('SAFETYGLASSES',$safetyGlasses,$data);
		$data = str_replace('EARDEFENDERS',$earDefenders,$data);
		$data = str_replace('SAFETYHELEMTS',$safetyHelmets,$data);
		$data = str_replace('HIGHVISJACKETS',$highVisJackets,$data);
		
		
		//print $data; // Used to debug RTF Code
		
		
		// close file 
		fclose($fp);
		
		// print file contents 
		//print "The data in the file is \"".$data."\"";
		
		
		
		// Save the file here
		$fpSaveFile = "./apps/employeedb/word/employeeDocument" . $fields['id'] . ".rtf";
		
		
		
		$fpSave = fopen($fpSaveFile, 'w') or die('Couldn\'t open file to save!');
		
		fwrite($fpSave, $data); 
		
		fclose($fpSave);
		
		
		
		//$updateDataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM `complaint` WHERE `id` = '" . $fields['id'] . "'");
		//$updateFields = mysql_fetch_array($updateDataset);
		
		mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM `document` WHERE `employeeId` = " . $fields['id'] . "");
		mysql::getInstance()->selectDatabase("complaints")->Execute("INSERT INTO `document` VALUES ('','" . $fields['id'] . "','" . common::nowDateForMysql() . "')");
		
		page::redirect("/apps/employeedb/");
		
	}
	
}
	

?>