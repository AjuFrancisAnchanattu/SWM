<?php
define('FPDF_FONTPATH', $_SERVER['DOCUMENT_ROOT'] . '/apps/complaints/pdf/font/');
include_once('ufpdf.php');

/**
 * 
 * @package apps	
 * @subpackage Appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 26/11/2008
 */

class generateAppraisal extends page
{	
	protected $pdf;
	protected $pageMode;
	
	function __construct()
	{		
		if(isset($_REQUEST['mode']) == "email")
		{
			$this->pageMode = "email";
		}
		
		$this->generatePDF();
	}		
	
	public function generatePDF()
	{
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM `appraisal` WHERE id = " . $this->getID() . "");
		$fields = mysql_fetch_array($dataset);
		
		$surname = $fields['surname'];
		$firstName = $fields['firstName'];
		$department = $fields['department'];
		$site = $fields['site'];
		$jobHeld = $fields['jobHeld'];
		$jobHeldSince = page::transformDateForPHP($fields['jobHeldSince']);
		$workingTime = $fields['workingTime'];
		$managerConductingAppraisal = $fields['managerConductingAppraisal'];
		$dateOfAppraisal = page::transformDateForPHP($fields['dateOfAppraisal']);
		$dateOfLastAppraisal = page::transformDateForPHP($fields['dateOfLastAppraisal']);
		$presenceOfThirdParty = $fields['presenceOfThirdParty'];
		$periodAppraised = translate::getInstance()->translate($fields['periodAppraised']);
		$jobDescriptionAvailable = $fields['jobDescriptionAvailable'] == "yes" ? "Job Description, " : "";
		$lastObjectives = $fields['lastObjectives'] == "yes" ? "Last Objectives, " : "";
		$trainingPlan = $fields['trainingPlan'] == "yes" ? "Training Plan, " : "";
		$dataBase = $fields['dataBase'] == "yes" ? "Data Base, " : "";
		$otherAvailable = $fields['otherAvailable'] == "yes" ? "Other, " : "";
		
		$documentsAvailable = $jobDescriptionAvailable . $lastObjectives . $trainingPlan . $dataBase . $otherAvailable;
		
		$developmentsInJobRoleSinceLastAppraisal = $fields['developmentsInJobRoleSinceLastAppraisal'];
		$satisfactionInCurrentJobRole = $fields['satisfactionInCurrentJobRoll'];
		$clarityOfJobRole = $fields['clarityOfJobRoll'];
		$workloadWithinTheDepartment = $fields['workloadWithinTheDepartment'];
		$equipmentAvailableForWork = $fields['equipmentAvailableForWork'];
		$regularCirculationOfInformation = $fields['regularCirculationOfInformation'];
		$regularCommentsOnYourWork = $fields['regularCommentsOnYourWork'];
		$contactWithCustomers = $fields['contactWithCustomers'];
		$atmosphereAtWork = $fields['atmosphereAtWork'];
		$trainingPossibilities = $fields['trainingPossibilities'];
		$securityAndComfortAtWork = $fields['securityAndComfortAtWork'];
		$managementStyle = $fields['managementStyle'];
		$otherScore = $fields['otherScore'];
		$commentsFromEmployeePart3 = $fields['commentsFromEmployeePart3'];
		$commentsFromManagerPart3 = $fields['commentsFromManagerPart3'];
		
		
		$pdf = new UFPDF();
		$pdf->AddFont('Arial', '', 'arial.php');
		
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 14);
		$pdf->SetFillColor(230, 230, 230);
		$pdf->SetDrawColor(180, 180, 180);		

		
		$pdf->Image('./apps/appraisal/pdf/scapa-logo.jpg', 10, 8, 33);
		$pdf->Cell(180,5,'APPRAISAL FORM', 0, 0, 'R', 0);
		
		$pdf->Ln(5);
		
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(180,5,"$firstName" . " $surname", 0, 0, 'R', 0);
		
		$pdf->Ln(25);
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100,3,'1 - Employee Details', 0, 0, 'L', 0);
		$pdf->Ln(10);
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(40,5,'Surname', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$surname", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'First Name', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$firstName", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Department', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$department", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Site', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$site", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Job Held', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$jobHeld", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Since', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$jobHeldSince", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Working Time', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$workingTime", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Manager Conducting Appraisal', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$managerConductingAppraisal", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Date Of Appraisal', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$dateOfAppraisal", 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Date Of Last Appraisal', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$dateOfLastAppraisal", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Presence of Third Party', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$presenceOfThirdParty", 1, 0, 'L', 1);
		
		if($presenceOfThirdParty == "yes")
		{
			$pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$pdf->Cell(30,5,'Name', 0, 0, 'L', 0);
			$pdf->Cell(55,5,"12", 1, 0, 'L', 1);	
		}
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Period Appraised', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"$periodAppraised", 1, 0, 'L', 1);
		
		if($other)
		{
			$pdf->Cell(5,3,'', 0, 0, 'L', 0);
			$pdf->Cell(30,5,'Other', 0, 0, 'L', 0);
			$pdf->Cell(55,5,"12", 1, 0, 'L', 1);
		}
		
		$pdf->Ln(10);
		
		$pdf->Cell(40,5,'Documents Available', 0, 0, 'L', 0);
		$pdf->Cell(145,5,"$documentsAvailable", 1, 0, 'L', 1);
		
		$pdf->Ln(15);
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100,3,'2 - Developments in Job Role Since Last Appraisal', 0, 0, 'L', 0);
		$pdf->Ln(10);
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(40,5,'Details', 0, 0, 'L', 0);
		$pdf->MultiCell(145,5, wordwrap($developmentsInJobRoleSinceLastAppraisal, 115, "\r\n"), 1, 'L', 1);
		
		$pdf->Ln(10);
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100,3,'3 - Working Environment', 0, 0, 'L', 0);
		$pdf->Ln(10);
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(40,5,'1 = Very Satisfied, 3 = Satisfied, 5 = Not Satisfied', 0, 0, 'L', 0);
		
		$pdf->Ln(10);
		
		$pdf->Cell(80,5,'', 0, 0, 'C', 0);
		$pdf->Cell(5,5,"1", 0, 0, 'C', 0);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,"2", 0, 0, 'C', 0);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,"3", 0, 0, 'C', 0);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,"4", 0, 0, 'C', 0);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,"5", 0, 0, 'C', 0);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Satisfaction in Current Job Role', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$satisfactionInCurrentJobRole == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$satisfactionInCurrentJobRole == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$satisfactionInCurrentJobRole == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$satisfactionInCurrentJobRole == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$satisfactionInCurrentJobRole == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Clarity of Job Role and of Objectives Fixed', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$clarityOfJobRole == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$clarityOfJobRole == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$clarityOfJobRole == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$clarityOfJobRole == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$clarityOfJobRole == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Workload within the Department', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$workloadWithinTheDepartment == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$workloadWithinTheDepartment == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$workloadWithinTheDepartment == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$workloadWithinTheDepartment == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$workloadWithinTheDepartment == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Equipment Available for Work', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$equipmentAvailableForWork == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$equipmentAvailableForWork == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$equipmentAvailableForWork == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$equipmentAvailableForWork == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$equipmentAvailableForWork == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Regular Circulation of Information in the Department', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$regularCirculationOfInformation == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCirculationOfInformation == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCirculationOfInformation == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCirculationOfInformation == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCirculationOfInformation == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Regular Comments on your Work', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$regularCommentsOnYourWork == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCommentsOnYourWork == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCommentsOnYourWork == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCommentsOnYourWork == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$regularCommentsOnYourWork == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Contact with Customers (Internal/External)', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$contactWithCustomers == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$contactWithCustomers == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$contactWithCustomers == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$contactWithCustomers == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$contactWithCustomers == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Atmosphere at Work', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$atmosphereAtWork == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$atmosphereAtWork == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$atmosphereAtWork == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$atmosphereAtWork == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$atmosphereAtWork == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Training Possibilities', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$trainingPossibilities == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$trainingPossibilities == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$trainingPossibilities == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$trainingPossibilities == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$trainingPossibilities == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Security and Comfort at Work', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$securityAndComfortAtWork == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$securityAndComfortAtWork == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$securityAndComfortAtWork == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$securityAndComfortAtWork == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$securityAndComfortAtWork == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Management Style (communication, delegation)', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$managementStyle == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$managementStyle == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$managementStyle == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$managementStyle == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$managementStyle == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(5);
		
		$pdf->Cell(80,5,'Other (Sources of Satisfaction/Dissatisfaction', 0, 0, 'L', 0);
		$pdf->Cell(5,5,$otherScore == '1' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$otherScore == '2' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$otherScore == '3' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$otherScore == '4' ? "X" : "", 1, 0, 'C', 1);
		$pdf->Cell(2,5," ", 0, 0, 'C', 0);
		$pdf->Cell(5,5,$otherScore == '5' ? "X" : "", 1, 0, 'C', 1);
		
		$pdf->Ln(15);
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100,3,'Comments from Employee and Manager on Point 3 if desired', 0, 0, 'L', 0);
		$pdf->Ln(5);
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(40,5,'Details', 0, 0, 'L', 0);
		$pdf->Cell(145,5,"$commentsFromEmployeePart3 - $commentsFromManagerPart3", 1, 0, 'L', 1);
		
		// Page 2
		
		$pdf->AddPage();
		$pdf->SetFont('Arial', '', 14);
		$pdf->SetFillColor(230, 230, 230);
		$pdf->SetDrawColor(180, 180, 180);		

		
		$pdf->Image('./apps/appraisal/pdf/scapa-logo.jpg', 10, 8, 33);
		$pdf->Cell(180,5,'APPRAISAL FORM', 0, 0, 'R', 0);
		
		$pdf->Ln(5);
		
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(180,5,"Employee Name Here (Page " . $pdf->PageNo() . ")", 0, 0, 'R', 0);
		
		$pdf->Ln(25);
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(100,3,'1 - Page 2 Stuff', 0, 0, 'L', 0);
		$pdf->Ln(10);
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell(40,5,'Page 2 Stuff', 0, 0, 'L', 0);
		$pdf->Cell(55,5,'', 1, 0, 'L', 1);
		
		$pdf->Cell(5,3,'', 0, 0, 'L', 0);
		$pdf->Cell(30,5,'Page 2 Stuff', 0, 0, 'L', 0);
		$pdf->Cell(55,5,"12", 1, 0, 'L', 1);
		
		$pdf->Ln(10);
		
		
		
		
		$pdf->Close();
		
		$pdf->Output("appraisal" . $this->getID() . ".pdf", "F");
		
		// Determine if Generating and Emailing or Opening the Appraisal
		if($this->pageMode == "email")
		{
			$this->emailGeneratedPDF();
		}
		else 
		{
			$this->openGeneratedPDF();
		}
	}
	
	public function openGeneratedPDF()
	{
		$this->addLog(translate::getInstance()->translate("appraisal_generated") . " - " . page::xmlentities(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName()) . "");
		
		page::redirect("/apps/appraisal/pdf/appraisal" . $this->getID() . ".pdf");
	}
	
	public function emailGeneratedPDF()
	{	
		page::redirect("/apps/appraisal/emailAppraisal?id=" . $this->getID() . "");
	}
	
	public function addLog($action)
	{
		mysql::getInstance()->selectDatabase("appraisals")->Execute(sprintf("INSERT INTO actionLog (appraisalId, NTLogon, actionDescription, actionDate) VALUES (%u, '%s', '%s', '%s')",
		$this->getID(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql()
		));
	}
	
	public function getID()
	{
		return $_REQUEST['id'];
	}
}

?>