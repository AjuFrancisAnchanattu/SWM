<?php

class reAuthorise extends page
{
	function __construct()
	{
		parent::__construct();

		switch ($_REQUEST['mode'])
		{
			case 'returnRequestEvaluation':
				
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `returnGoods`, `returnRequestValue_quantity`, `returnRequestValue_measurement`, `returnRequestComment`, `returnRequestName`, `returnRequestCC`, `returnApprovalRequest`, `returnApprovalRequestComment`, `returnApprovalRequestName` FROM evaluation WHERE complaintId = " . $_REQUEST['complaint'] . "");
				$fields = mysql_fetch_array($dataset);
				
				if(mysql_num_rows($dataset) > 0)
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO returnGoodsInformation (complaintId, returnGoods, returnRequestValue_quantity, returnRequestValue_measurement, returnRequestComment, returnRequestName, returnRequestCC, returnApprovalRequest, returnApprovalRequestComment, returnApprovalRequestName, dateReturnReAuthorised) VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
						$_REQUEST['complaint'],
						$fields['returnGoods'],
						$fields['returnRequestValue_quantity'],
						$fields['returnRequestValue_measurement'],
						$fields['returnRequestComment'],
						$fields['returnRequestName'],
						$fields['returnRequestCC'],
						$fields['returnApprovalRequest'],
						$fields['returnApprovalRequestComment'],
						$fields['returnApprovalRequestName'],
						page::nowDateTimeForMysql()
					));
					
					// Update Evaluation Form to not show previous values
					$datasetUpdate = mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation SET returnGoods = 'NO', returnRequestValue_quantity = '', returnRequestValue_measurement = '', returnRequestComment = '', returnRequestName = '', returnRequestCC = '', returnApprovalRequest = '', returnApprovalRequestComment = '', returnApprovalRequestName = '' WHERE complaintId = " . $_REQUEST['complaint'] ."");			
					
					$this->addLog("Returns Authorisation Rolled Back", "Previous Information: Return Request Value " . $fields['returnRequestValue_quantity'] . $fields['returnRequestValue_measurement'] . " - Comment: " . $fields['returnRequestComment'] . " - Request Name: " . usercache::getInstance()->get($fields['returnRequestName'])->getName() . " - Return Approved: " . $fields['returnApprovalRequest'] . " - Return Approved Comment: " . $fields['returnApprovalRequestComment'] . " - Return Approval Request Name: " . usercache::getInstance()->get($fields['returnApprovalRequestName'])->getName() .  "");
				}
				else 
				{
					// Log in History - Successful
					$this->addLog("Information could not be found to Roll Back Returns");
				}
				
				break;
				
			case 'disposalRequestEvaluation':
				
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `disposeGoods`, `returnApprovalDisposalValue_quantity`, `returnApprovalDisposalValue_measurement`, `returnApprovalDisposalComment`, `returnApprovalDisposalName`, `returnApprovalDisposalRequest`, `returnDisposalRequestComment`, `returnDisposalRequestName` FROM evaluation WHERE complaintId = " . $_REQUEST['complaint'] . "");
				$fields = mysql_fetch_array($dataset);
				
				if(mysql_num_rows($dataset) > 0)
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO disposalGoodsInformation (complaintId, disposeGoods, returnApprovalDisposalValue_quantity, returnApprovalDisposalValue_measurement, returnApprovalDisposalComment, returnApprovalDisposalName, returnApprovalDisposalRequest, returnDisposalRequestComment, returnDisposalRequestName, dateDisposalReAuthorised) VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
						$_REQUEST['complaint'],
						$fields['disposeGoods'],
						$fields['returnApprovalDisposalValue_quantity'],
						$fields['returnApprovalDisposalValue_measurement'],
						$fields['returnApprovalDisposalComment'],
						$fields['returnApprovalDisposalName'],
						$fields['returnApprovalDisposalRequest'],
						$fields['returnDisposalRequestComment'],
						$fields['returnDisposalRequestName'],
						page::nowDateTimeForMysql()
					));	
					
					// Update Evaluation Form to not show previous values
					$datasetUpdate = mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE evaluation SET disposeGoods = 'NO', returnApprovalDisposalValue_quantity = '', returnApprovalDisposalValue_measurement = '', returnApprovalDisposalComment = '', returnApprovalDisposalName = '', returnApprovalDisposalRequest = '', returnDisposalRequestComment = '', returnDisposalRequestName = '' WHERE complaintId = " . $_REQUEST['complaint'] ."");			
								
					// Log in History - Successful
					$this->addLog("Disposal Authorisation Rolled Back", "Previous Information: Disposal Request Value " . $fields['returnApprovalDisposalValue_quantity'] . $fields['returnApprovalDisposalValue_measurement'] . " - Comment: " . $fields['returnApprovalDisposalComment'] . " - Request Name: " . usercache::getInstance()->get($fields['returnApprovalDisposalName'])->getName() . " - Return Approved: " . $fields['returnApprovalDisposalRequest'] . " - Return Approved Comment: " . $fields['returnDisposalRequestComment'] . " - Return Approval Request Name: " . usercache::getInstance()->get($fields['returnDisposalRequestName'])->getName() .  "");
				}
				else 
				{
					// Log in History - Successful
					$this->addLog("Information could not be found to Roll Back Disposals");
				}
				
				break;
				
			case 'returnNACreditRequest':
				
				$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `financeLevelCreditAuthorised`, `financeCreditAuthoriser`, `financeCreditNewComplaintOwner`, `ccCommercialCreditComment`, `requestForCredit`, `financeStageCompleted`, `creditAuthorisationStatus`, `financeReason` FROM conclusion WHERE complaintId = " . $_REQUEST['complaint'] . "");
				$fields = mysql_fetch_array($dataset);
				
				if(mysql_num_rows($dataset) > 0)
				{
					mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO naCreditGroupInformation (complaintId, financeLevelCreditAuthorised, financeCreditAuthoriser, financeCreditNewComplaintOwner, ccCommercialCreditComment, requestForCredit, financeStageCompleted, creditAuthorisationStatus, financeReason, dateNACreditReAuthorised) VALUES (%u, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
						$_REQUEST['complaint'],
						$fields['financeLevelCreditAuthorised'],
						$fields['financeCreditAuthoriser'],
						$fields['financeCreditNewComplaintOwner'],
						$fields['ccCommercialCreditComment'],
						$fields['requestForCredit'],
						$fields['financeStageCompleted'],
						$fields['creditAuthorisationStatus'],
						$fields['financeReason'],
						page::nowDateForMysql()
					));	
					
					// Update Evaluation Form to not show previous values
					$datasetUpdate = mysql::getInstance()->selectDatabase("complaints")->Execute("UPDATE conclusion SET financeLevelCreditAuthorised = '', financeCreditAuthoriser = '', financeCreditNewComplaintOwner = '', ccCommercialCreditComment = '', requestForCredit = 'NO', financeStageCompleted = '', creditAuthorisationStatus = '', financeReason = '' WHERE complaintId = " . $_REQUEST['complaint'] ."");
								
					// Log in History - Successful
					$this->addLog("NA Credit Authorisation Rolled Back", "Previous Information: Finance Authorised: " . $fields['financeLevelCreditAuthorised'] . " - Finance Authoriser: " . usercache::getInstance()->get($fields['financeCreditAuthoriser'])->getName() . " - Finance Complaint Owner: " . usercache::getInstance()->get($fields['financeCreditNewComplaintOwner'])->getName() . " - CC User: " . $fields['ccCommercialCreditComment'] . " - Finance Reason: " . $fields['financeReason'] . "");
				}
				else 
				{
					// Log in History - Successful
					$this->addLog("Information could not be found to Roll Back NA Credit");
				}
				
				break;
				
			default:
				
				// Do Nothing ...
				
				break;
				
		}
		
		// Redirect User back to page ...
		page::redirect('/apps/complaints/resume?complaint=' . $_REQUEST['complaint'] . '&status='  . $_REQUEST['status'] . ''); // redirects to homepage
	}

	private function addLog($action, $description)
	{
		mysql::getInstance()->selectDatabase("complaints")->Execute(sprintf("INSERT INTO actionLog (complaintId, NTLogon, actionDescription, actionDate, description) VALUES (%u, '%s', '%s', '%s', '%s')",
			$_REQUEST['complaint'],
			currentuser::getInstance()->getNTLogon(),
			addslashes($action),
			common::nowDateTimeForMysql(),
			$description
		));
	}
}

?>