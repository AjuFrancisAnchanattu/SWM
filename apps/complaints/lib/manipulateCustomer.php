<?php

require 'complaintCustomer.php';


class manipulateCustomer extends page
{
	protected $complaintCustomer;
	protected $pageAction;
	protected $valid = false;	
	
	function __construct()
	{
		parent::__construct();
	}
	
	public function processPost()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{						
			page::addDebug("Current Location: " . $this->getLocation(), __FILE__, __LINE__);
			
			switch($this->getLocation())
			{
				case 'complaintCustomer':
					$this->complaintCustomer->form->processPost();
					$this->complaintCustomer->form->processDependencies();
					break;
				case 'evaluationCustomer':
					$this->complaintCustomer->getEvaluationCustomer()->form->processPost();
					$this->complaintCustomer->form->processDependencies();
					break;
				case 'conclusionCustomer':
					$this->complaintCustomer->getConclusionCustomer()->form->processPost();
					$this->complaintCustomer->form->processDependencies();
					break;				
			}
		}
	}
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{						
			if(isset($_REQUEST['complaintCustomer']))
			{
				$_REQUEST['complaintCustomer'] = $_REQUEST['complaintCustomer'];
			}
			else 
			{
				$_REQUEST['complaintCustomer'] = 0;
			}
			
			$today = date("Y-m-d");
			$today_date = strtotime($today);
			$complaintDate = strtotime(page::transformDateForMYSQL($this->complaintCustomer->form->get("customerComplaintDate")->getValue()));
		
			if($complaintDate > $today_date)
			{
				$this->add_output("<error />");
				$this->setPageAction("complaintCustomer");
			}
			
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->complaintCustomer->validate();
			}
			
			$receptionDate = strtotime(page::transformDateForMYSQL($this->complaintCustomer->form->get("sampleReceptionDate")->getValue()));
		
			if($this->complaintCustomer->form->get("sampleReceptionDate")->getValue() == "Yes")
			{
				if($receptionDate > $today_date)
				{
					$this->add_output("<error />");
					$this->setPageAction("complaintCustomer");
				}
				
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					$this->valid = $this->complaintCustomer->validate();
				}		
			}
		
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{					
				//validate the entry in the processOwner field, is it a valid NTLogon?
				$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaintCustomer->form->get("processOwner")->getValue() . "'");
				if (mysql_num_rows($datasetLogon) != 1)
				{
					//die("name in PO box incorrect");
					$this->add_output("<error />");
					$this->complaintCustomer->form->get("processOwner")->setValid(false);
					$this->setPageAction("complaintCustomer");
				}
				
				if($this->complaintCustomer->form->get("groupAComplaint")->getValue() == "Yes")
				{
					$datasetGroupedComplaints = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE id = '" . $this->complaintCustomer->form->get("groupedComplaintId")->getValue() . "'");
					
					if(mysql_num_rows($datasetGroupedComplaints) != 1)
					{
						$this->complaintCustomer->form->get("groupedComplaintId")->setValid(false);
						$this->setPageAction("complaintCustomer");
					}
				}
				
				//validate entry in the SAP customer number field, is it a valid ID?
				$datasetSap = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT id FROM customer WHERE `id` = '" . $this->complaintCustomer->form->get("sapCustomerNumber")->getValue() . "'");
																	
				if (mysql_num_rows($datasetSap) != 1)
				{
					$this->add_output("<error />");
					$this->complaintCustomer->form->get("sapCustomerNumber")->setValid(false);
					$this->setPageAction("complaintCustomer");
					
				}
			}
			
			if($this->complaintCustomerCustomer->getEvaluationCustomer())
			{				
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("isSampleReceived")->getValue() == "YES")
				{				
					if (isset($_POST['validate']) && $_POST['validate']=='true')
					{
						$this->valid = $this->complaintCustomer->validate();
					}
				}		
				
				
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					//echo "|".$this->valid."<br>";
					$this->valid = $this->complaintCustomer->validate();
					//echo "|".$this->valid;exit;
				}
				
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("transferOwnership2")->getValue() == 'NO')
				{
					//do nothing, captures the posibility that neither yes or no is selected doing it this way :)
				}
				else 
				{
					$cat = $this->complaintCustomer->form->get("category")->getValue();
					
					if($this->complaintCustomer->determineNAOrEuropeEvaluationProcessRoute() == "USA")
					{
						// don't do the validation for process owner here ...
					}
					else 
					{
						if($this->complaintCustomer->getEvaluationCustomer()->form->get("processOwner2")->getValue() == '')
						{
							$this->add_output("<error />");
							$this->complaintCustomer->getEvaluationCustomer()->form->get("processOwner2")->setValid(false);
							$this->setPageAction("evaluation");
						}
						else 
						{
							$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaintCustomer->getEvaluationCustomer()->form->get("processOwner2")->getValue() . "'");
							if (mysql_num_rows($datasetLogon) != 1)
							{
								$this->add_output("<error />");
								$this->complaintCustomer->getEvaluationCustomer()->form->get("processOwner2")->setValid(false);
								$this->setPageAction("evaluation");
							}
						}	
					}
				}
			}
			
			if($this->complaintCustomer->getConclusionCustomer())
			{				
				
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE COMPLAINT", __FILE__, __LINE__);
				if(isset($_REQUEST["sfID"]) && $_REQUEST["sfID"]){
					mysql::getInstance()->selectDatabase("complaints")->Execute("DELETE FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."' AND sfOwner = '".currentuser::getInstance()->getNTLogon()."' LIMIT 1");
				}
									
				$this->complaintCustomer->save($this->getLocation());
			}
			else
			{
				$this->add_output("<error />");
				
				// Find Errors
				if (!$this->complaintCustomer->form->isValid())
				{
					$this->setPageAction("complaintCustomer");
				}
				
				if($this->complaintCustomer->getEvaluationCustomer())
				{
					if (!$this->complaintCustomer->getEvaluationCustomer()->form->isValid())
					{
						$this->setPageAction("evaluation");
					}
				}
				
				if($this->complaintCustomer->getConclusionCustomer())
				{
					if (!$this->complaintCustomer->getConclusionCustomer()->form->isValid())
					{
						$this->setPageAction("conclusion");
					}
				}
			}
		}
		
	}
	
	public function doStuffAndShow($outputType="normal")
	{
		$output = "";
		
		page::addDebug("PAGE ACTION: ". $this->getPageAction(), __FILE__, __LINE__);	
		
		if (isset($_REQUEST['id']))
		{
			$requestID = $_REQUEST['id'];
		}
		else 
		{
			$requestID = "-1";
		}
		
		// Hide the submit to external once it has been set to Yes
		if($this->getPageAction() == "complaintCustomer")
		{
			$this->setLocation("complaintCustomer");
		}
					
		if ($this->getPageAction() == "evaluation")
		{
			$this->setLocation("evaluation");
			
			$output .= "<complaintReport id=\"" . $requestID . "\">";
			
			// This will show if the customer complaint type is North America and M/D/S complaint type.
			$cat = $this->complaintCustomer->form->get("category")->getValue();
			
			if($this->complaintCustomer->determineNAOrEuropeEvaluationProcessRoute() == "USA")
			{
				// Evaluation - Return the Goods Process
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("returnGoods")->getValue() == "YES")
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnGoods")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestValue")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestComment")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestName")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestCC")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestSubmit")->setVisible(false);						
					
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnGoodsReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("reAuthoriseReturnGoodsReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestValueReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestCommentReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestNameReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnRequestCCReadOnly")->setVisible(true);
				}
				else 
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequest")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("reAuthoriseReturnGoodsReadOnly")->setVisible(false);
				}
				
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequest")->getValue() == "YES")
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequest")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestComment")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestName")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestSubmit")->setVisible(false);						
					
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestCommentReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalRequestNameReadOnly")->setVisible(true);
				}
				
				// Evaluation - Dispose the Goods Process
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("disposeGoods")->getValue() == "YES")
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("disposeGoods")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalValue")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalComment")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalName")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalSubmit")->setVisible(false);					
					
					$this->complaintCustomer->getEvaluationCustomer()->form->get("disposeGoodsReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("reAuthoriseDisposalGoodsReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalValueReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalCommentReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalNameReadOnly")->setVisible(true);
				}
				else 
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalRequest")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("reAuthoriseDisposalGoodsReadOnly")->setVisible(false);
				}
				
				if($this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalRequest")->getValue() == "YES")
				{
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalRequest")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnDisposalRequestComment")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnDisposalRequestName")->setVisible(false);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnDisposalSubmit")->setVisible(false);					
					
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnApprovalDisposalRequestReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnDisposalRequestCommentReadOnly")->setVisible(true);
					$this->complaintCustomer->getEvaluationCustomer()->form->get("returnDisposalRequestNameReadOnly")->setVisible(true);
				}
			}
			
			if($this->complaintCustomer->getEvaluationCustomer()->form->get("complaintJustified")->getValue() == "NO")
			{
				$this->complaintCustomer->getEvaluationCustomer()->form->processDependencies();
			}
			
			if ($outputType=="normal")
			{
				$output .= $this->complaintCustomer->getEvaluationCustomer()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->complaintCustomer->getEvaluationCustomer()->form->showLegend(false);
				$output .= $this->complaintCustomer->getEvaluationCustomer()->form->readOnlyOutput($exceptions);
			}
			$output .= "</complaintReport>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "conclusion")
		{
			$this->setLocation("conclusion");
			
			$output .= "<complaintReport id=\"" . $requestID . "\">";
			
			//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
			if($this->complaintCustomer->determineNAOrEuropeConclusionProcessRoute() == "USA")
			{
				if($this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->getValue() == 'NO')
				{						
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditAuthoriser2")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCreditRaised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);
					
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->getValue() == 'YES')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("processOwner3")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCredit")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCreditComment")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit1")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == '')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCreditRaised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditAuthoriser2")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("reAuthoriseNACreditReadOnly")->setVisible(true);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
				{						
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("reAuthoriseNACreditReadOnly")->setVisible(true);
				}		
				
				if($this->complaintCustomer->form->get("creditNoteRequested")->getValue() == "NO")
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setValue("NO");

					$datasetAmerican = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `salesOffice`, `complaintValue_quantity` FROM complaint WHERE id = '"  . $this->complaintCustomer->getId()."'");
					$fieldsAmerican = mysql_fetch_array($datasetAmerican);

					if($fieldsAmerican['complaintLocation'] == 'american')
					{
						
					}
					else 
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCredit")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCreditComment")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);	
						$this->complaintCustomer->getConclusionCustomer()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);
					}
					
				}
				else 
				{
					//check the db to see if there has already been a credit request
					$datasetRequest = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(requestForCredit) as creditCount FROM conclusion WHERE complaintId = '" . $this->complaintCustomer->form->get("id")->getValue() . "'");
					$fieldsRequest = mysql_fetch_array($datasetRequest);
					if($fieldsRequest['creditCount'] == '0')
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(true);
					}
					else
					{
						$datasetRequestValue = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT requestForCredit FROM conclusion WHERE complaintId = '" . $this->complaintCustomer->form->get("id")->getValue() . "'");
						$fieldsRequestValue = mysql_fetch_array($datasetRequestValue);
						if($fieldsRequestValue['requestForCredit'] == 'YES')
						{
							$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(false);
						}
						else
						{
							$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(true);
						}
					}
				}
			}
			else 
			{
				if($this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->getValue() == 'NO')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewCommercialOwner")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriser2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditAuthoriser2")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCreditRaised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submitAdvise")->setVisible(false); // Added By Jason 27/12/2007
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);
				}	
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->getValue() == 'YES'/* && !isset($_REQUEST["sfID"])*/)
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("processOwner3")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCredit")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCreditComment")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit1")->setVisible(false);	
					
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->getValue() == '')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);				
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCreditRaised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriser2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);
					
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditAuthoriser2")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->getValue() == 'YES')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
					
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == '')
				{
					$datasetCredit = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id, creditNoteGBP_quantity, creditNoteValue_measurement, creditNoteValue_quantity FROM conclusion WHERE complaintId = " . $this->complaintCustomer->form->get("id")->getValue() . "");
					$fieldsCredit = mysql_fetch_array($datasetCredit);
					
					$level = "";
					
					if($fieldsCredit['creditNoteValue_measurement'] == 'EUR')
					{
						if($fieldsCredit['creditNoteValue_quantity'] > 1500)
						{
							$level = "higher";
						}
					}
					else 
					{
						if($fieldsCredit['creditNoteGBP_quantity'] > 1000)
						{
							$level = "higher";
						}
					}
					
					if($level == "higher")
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriser")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
						
						//readonly versions
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriser2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);
						
						//readonly versions
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);
					}
					else 
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise")->setValue("YES");
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise2")->setValue("ADVISE NOT REQUIRED");
						//readonly versions
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);
					}
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == 'YES')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorisedAdvise")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReasonAdvise")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewCommercialOwner")->setVisible(false);
					
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("submitAdvise")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->getValue() == 'NO')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == '')
				{						
					//readonly versions
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditAuthoriser2")->setVisible(false);
				}
	
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
				}
				
				if($this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
					
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
				}		
				
				if($this->complaintCustomer->form->get("creditNoteRequested")->getValue() == "NO")
				{
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(false);
					$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setValue("NO");

					$datasetAmerican = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `salesOffice`, `complaintValue_quantity` FROM complaint WHERE id = '"  . $this->complaintCustomer->getId()."'");
					$fieldsAmerican = mysql_fetch_array($datasetAmerican);

					if($fieldsAmerican['complaintLocation'] == 'american')
					{
						
					}
					else 
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("transferOwnership")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCredit")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("ccCommercialCreditComment")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialReason")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeReason")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("submit2")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("submit3")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaintCustomer->getConclusionCustomer()->form->get("dateCreditNoteRaised")->setVisible(false);	
					}
					
				}
				else 
				{
					//check the db to see if there has already been a credit request
					$datasetRequest = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(requestForCredit) as creditCount FROM conclusion WHERE complaintId = '" . $this->complaintCustomer->form->get("id")->getValue() . "'");
					$fieldsRequest = mysql_fetch_array($datasetRequest);
					if($fieldsRequest['creditCount'] == '0')
					{
						$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(true);
					}
					else
					{
						$datasetRequestValue = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT requestForCredit FROM conclusion WHERE complaintId = '" . $this->complaintCustomer->form->get("id")->getValue() . "'");
						$fieldsRequestValue = mysql_fetch_array($datasetRequestValue);
						if($fieldsRequestValue['requestForCredit'] == 'YES')
						{
							$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(false);
						}
						else
						{
							$this->complaintCustomer->getConclusionCustomer()->form->get("requestForCredit")->setVisible(true);
						}
					}
				}
			}
				
			if ($outputType=="normal")
			{
				$output .= $this->complaintCustomer->getConclusionCustomer()->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->complaintCustomer->getConclusionCustomer()->form->showLegend(false);
				$output .= $this->complaintCustomer->getConclusionCustomer()->form->readOnlyOutput($exceptions);
			}
			$output .= "</complaintReport>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "complaintCustomer")
		{
			$this->setLocation("complaintCustomer");
			
			$today = date("Y-m-d");
			$today_date = strtotime($today);
			$complaintDate = strtotime(page::transformDateForMYSQL($this->complaintCustomer->form->get("customerComplaintDate")->getValue()));
			
			if($complaintDate > $today_date)
			{
				$this->complaintCustomer->form->get("customerComplaintDate")->setValid(false);
			}
			
			$receptionDate = strtotime(page::transformDateForMYSQL($this->complaintCustomer->form->get("sampleReceptionDate")->getValue()));
			
			if($receptionDate > $today_date)
			{
				$this->complaintCustomer->form->get("sampleReceptionDate")->setValid(false);
			}
									
			// content
			$output .= "<complaintReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{				
				$output .= $this->complaintCustomer->form->output();
			}
			else 
			{
				
				$exceptions = array();
								
				$this->complaintCustomer->form->get("salesOffice")->setVisible(true);
				$this->complaintCustomer->form->showLegend(false);
				$output .= $this->complaintCustomer->form->readOnlyOutput($exceptions);		
			}
			
			$output .= "</complaintReport>";
			
			return $output;
		}
	}
	
	
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO complaint", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'complaint';
		}
		
		return $_SESSION['apps'][$GLOBALS['app']]['location'];
	}
	
	public function setLocation($location)
	{
		page::addDebug("set location $location", __FILE__, __LINE__);
		$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
	}
	
	public function getPageAction()
	{
		if (!isset($this->pageAction))
		{
			if (isset($_POST['action']))
			{
				$this->pageAction = $_POST['action'];
			}
			else 
			{
				if (isset($_REQUEST['action']))
				{
					$this->pageAction = $_REQUEST['action'];
				}
				else 
				{
					$this->pageAction = "complaintCustomer";
				}
			}
		}
		
		return $this->pageAction;
	}
	
	public function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}
	
	public function getComplaintType($id)
	{
		return "customer_complaint";
	}
	
}

?>