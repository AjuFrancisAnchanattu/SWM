<?php

require 'complaint.php';


class manipulate extends page
{
	protected $complaint;
	protected $pageAction;
	protected $reportActionId;
	protected $materialActionId;
	protected $opportunityId;
	protected $opportunityActionId;
	protected $delegateForm;
	protected $valid = false;
	protected $orderId;


	function __construct()
	{
		// call page constructor
		parent::__construct();
	}


	// process form submissions

	public function processPost()
	{
		//echo "HERE";exit;
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form

			page::addDebug("Current Location: " . $this->getLocation(), __FILE__, __LINE__);
			//echo $this->getLocation();exit;
			switch($this->getLocation())
			{
				case 'complaint':
					$this->complaint->form->processPost();
					$this->complaint->form->processDependencies();
					// $this->complaint->processHierarchy();
					break;
				case 'evaluation':
					$this->complaint->getEvaluation()->form->processPost();
					$this->complaint->form->processDependencies();
					break;
				case 'conclusion':
					$this->complaint->getConclusion()->form->processPost();
					$this->complaint->form->processDependencies();
					break;
				case 'complaintExternal':
					//$this->complaint->form->processPost();
					break;
				/*case 'productOwner':
					$this->ijf->getProductOwner()->form->processPost();
					break;
				case 'commercialPlanning':
					$this->ijf->getCommercialPlanning()->form->processPost();
					break;
				case 'finance':
					$this->ijf->getFinance()->form->processPost();
					break;
				//case 'productionSite':
				//	$this->ijf->getProductionSite()->form->processPost();
				//	break;
				*/

			}

		}
	}



	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			/*echo "<pre>";
			print_r($_SESSION);
			echo "</pre>";
			exit;*/
//			$this->getPageAction();

			//die("Page Action: " . $this->getPageAction());

//			if ($this->getPageAction() == "complaint" || $this->getPageAction() == "")
//			{

			//echo "|".$this->valid."<br>";

				$ignore = false;

				if(isset($_GET['typeOfComplaint']) && $_GET['typeOfComplaint'] == "supplier_complaint")
				{
					$today = date("Y-m-d");
					$today_date = strtotime($today);
					$complaintDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("customerComplaintDate")->getValue()));

					if($complaintDate > $today_date)
					{
						$this->add_output("<error />");
						$this->setPageAction("complaint");

						echo "1";
					}

					if (isset($_POST['validate']) && $_POST['validate']=='true')
					{
						$this->valid = $this->complaint->validate();
					}
				}
				else
				{
					if(isset($_REQUEST['complaint']))
					{
						$_REQUEST['complaint'] = $_REQUEST['complaint'];
					}
					else
					{
						$_REQUEST['complaint'] = 0;
					}

					if($this->getComplaintType($_REQUEST['complaint']) == "supplier_complaint")
					{
						if($this->getPageAction() == "approve")  // this is required for update
						{
							$ignore = true;
						}
						elseif($this->getPageAction() == "acceptContainmentAction")  // this is required for update
						{
							$ignore = true;
						}
						elseif($this->getPageAction() == "reject")  // this is required for update
						{
							$ignore = true;
						}
						else
						{
							$today = date("Y-m-d");
							$today_date = strtotime($today);
							$complaintDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("customerComplaintDate")->getValue()));

							if($complaintDate > $today_date)
							{
								$this->add_output("<error />");
								$this->setPageAction("complaint");

								echo "2";
							}

							if (isset($_POST['validate']) && $_POST['validate']=='true')
							{
								$this->valid = $this->complaint->validate();
							}
						}

					}
					else
					{
						$today = date("Y-m-d");
						$today_date = strtotime($today);
						$complaintDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("customerComplaintDate")->getValue()));

						if($complaintDate > $today_date)
						{
							$this->add_output("<error />");
							$this->setPageAction("complaint");

							echo "3";
						}

						if (isset($_POST['validate']) && $_POST['validate']=='true')
						{
							$this->valid = $this->complaint->validate();
						}

						$receptionDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("sampleReceptionDate")->getValue()));

						if($this->complaint->form->get("sampleReceptionDate")->getValue() == "Yes")
						{
							if($receptionDate > $today_date)
							{
								$this->add_output("<error />");
								$this->setPageAction("complaint");

								echo "4";
							}

							if (isset($_POST['validate']) && $_POST['validate']=='true')
							{
								$this->valid = $this->complaint->validate();
							}
						}
					}

				}

				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					if($this->getPageAction() == "approve")
					{
						$ignore = true;
					}
					elseif($this->getPageAction() == "acceptContainmentAction")
					{
						$ignore = true;
					}
					elseif($this->getPageAction() == "reject")  // this is required for update
					{
						$ignore = true;
					}
					else
					{
						//validate the entry in the processOwner field, is it a valid NTLogon?
						$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaint->form->get("processOwner")->getValue() . "'");
						if (mysql_num_rows($datasetLogon) != 1)
						{
							//die("name in PO box incorrect");
							$this->add_output("<error />");
							$this->complaint->form->get("processOwner")->setValid(false);
							$this->setPageAction("complaint");

							echo "5";

						}

						if($this->complaint->form->get("typeOfComplaint")->getValue() == "supplier_complaint")
						{
							if($this->complaint->form->get("sp_submitToExtSupplier")->getValue() == "Yes" && $this->complaint->form->get("externalEmailAddress")->getValue() == "")
							{
								$this->add_output("<error />");
								$this->complaint->form->get("externalEmailAddress")->setValid(false);
								$this->setPageAction("complaint");

								echo "6";
							}
						}

						if($this->complaint->form->get("groupAComplaint")->getValue() == "Yes")
						{
							$datasetGroupedComplaints = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT id FROM complaint WHERE id = '" . $this->complaint->form->get("groupedComplaintId")->getValue() . "'");

							if(mysql_num_rows($datasetGroupedComplaints) != 1)
							{
								//$this->add_output("<error />");
								$this->complaint->form->get("groupedComplaintId")->setValid(false);
								$this->setPageAction("complaint");

								echo "7";
							}
						}

						if($this->complaint->form->get("typeOfComplaint")->getValue() == "supplier_complaint")
						{
							//validate entry in the SAP customer number field, is it a valid ID?
							$datasetSap = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT id FROM supplier WHERE `id` = '" . $this->complaint->form->get("sp_sapSupplierNumber")->getValue() . "'");
							//die("sap customer number" . $this->complaint->form->get("sapCustomerNumber")->getValue());
							if (mysql_num_rows($datasetSap) != 1)
							{
								//die("id in customer no box incorrect");
								$this->add_output("<error />");
								$this->complaint->form->get("sp_sapSupplierNumber")->setValid(false);
								$this->setPageAction("complaint");

								echo "8";

							}
						}
						elseif($this->complaint->form->get("typeOfComplaint")->getValue() == "quality_complaint")
						{
							// do nothing ...
						}
						else
						{
							//validate entry in the SAP customer number field, is it a valid ID?
							$datasetSap = mysql::getInstance()->selectDatabase("SAP")->Execute("SELECT DISTINCT id FROM customer WHERE `id` = '" . $this->complaint->form->get("sapCustomerNumber")->getValue() . "'");
							//die("sap customer number" . $this->complaint->form->get("sapCustomerNumber")->getValue());

							if (mysql_num_rows($datasetSap) != 1)
							{
								//die("id in customer no box incorrect");
								$this->add_output("<error />");
								$this->complaint->form->get("sapCustomerNumber")->setValid(false);
								$this->setPageAction("complaint");

								echo "9";

							}
						}
					}

				}

			//}

			//echo "|".$this->valid."<br>";

			if($ignore)
			{
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					page::redirect("/apps/complaints/index?id=" . $_REQUEST['complaint']);		//redirects the page back to the summary
				}
			}
			else
			{

				if($this->complaint->getEvaluation())
				{
					if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
					{
						if (isset($_POST['validate']) && $_POST['validate']=='true')
						{
							//echo "|".$this->valid."<br>";
							$this->valid = $this->complaint->validate();
							//echo "|".$this->valid;exit;
						}
					}
					elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
					{
						if (isset($_POST['validate']) && $_POST['validate']=='true')
						{
							//echo "|".$this->valid."<br>";
							$this->valid = $this->complaint->validate();
							//echo "|".$this->valid;exit;
						}
					}
					else
					{
						if($this->complaint->getEvaluation()->form->get("isSampleReceived")->getValue() == "YES")
						{
							if($this->complaint->getEvaluation()->form->get("dateSampleReceived")->getValue() < $this->complaint->form->get("sampleReceptionDate")->getValue())
							{
								//$this->add_output("<error />");
								//$this->complaint->getEvaluation()->form->get("dateSampleReceived")->setValid(false);
								//$this->setPageAction("evaluation");
							}

							if (isset($_POST['validate']) && $_POST['validate']=='true')
							{
								$this->valid = $this->complaint->validate();
							}
						}
					}

					if (isset($_POST['validate']) && $_POST['validate']=='true')
					{
						//echo "|".$this->valid."<br>";
						$this->valid = $this->complaint->validate();
						//echo "|".$this->valid;exit;
					}

					if($this->complaint->form->get("typeOfComplaint")->getValue() == "supplier_complaint")
					{
//						if($this->complaint->getEvaluation()->form->get("processOwner2")->getValue() == '')
//						{
//							$this->add_output("<error />");
//							$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
//							$this->setPageAction("evaluation");
//						}
//						else
//						{
//							$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaint->getEvaluation()->form->get("processOwner2")->getValue() . "'");
//							if (mysql_num_rows($datasetLogon) != 1)
//							{
//								//die("name in PO box incorrect");
//								$this->add_output("<error />");
//								$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
//								$this->setPageAction("evaluation");
//							}
//						}
					}
					elseif($this->complaint->form->get("typeOfComplaint")->getValue() == "quality_complaint")
					{
						if($this->complaint->getEvaluation()->form->get("processOwner2")->getValue() == '')
						{
							$this->add_output("<error />");
							$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
							$this->setPageAction("evaluation");
						}
						else
						{
							$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaint->getEvaluation()->form->get("processOwner2")->getValue() . "'");
							if (mysql_num_rows($datasetLogon) != 1)
							{
								//die("name in PO box incorrect");
								$this->add_output("<error />");
								$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
								$this->setPageAction("evaluation");
							}
						}
					}
					else
					{
						if($this->complaint->getEvaluation()->form->get("transferOwnership2")->getValue() == 'NO')
						{
							//do nothing, captures the posibility that neither yes or no is selected doing it this way :)
						}
						else
						{
							// This will show if the customer complaint type is North America and M/D/S complaint type.
							// Updated 07/11/2008 to allow all Categories
							$cat = $this->complaint->form->get("category")->getValue();
							//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
							if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA")
							{
								// don't do the validation for process owner here ...
							}
							else
							{
								if($this->complaint->getEvaluation()->form->get("processOwner2")->getValue() == '')
								{
									$this->add_output("<error />");
									$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
									$this->setPageAction("evaluation");
								}
								else
								{
									$datasetLogon = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT DISTINCT NTLogon FROM employee WHERE `NTLogon` = '" . $this->complaint->getEvaluation()->form->get("processOwner2")->getValue() . "'");
									if (mysql_num_rows($datasetLogon) != 1)
									{
										//die("name in PO box incorrect");
										$this->add_output("<error />");
										$this->complaint->getEvaluation()->form->get("processOwner2")->setValid(false);
										$this->setPageAction("evaluation");
									}
								}
							}
						}

						$datasetFMEA = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT fmea FROM evaluation WHERE complaintId = " . $_REQUEST['complaint'] . "");

						if(mysql_num_rows($datasetFMEA) > 0)
						{
//							if($this->complaint->form->get("businessUnit")->getValue() == "automotive" && $this->complaint->getEvaluation()->form->get("fmeaReviewed")->getValue() == "" && $this->complaint->determineNAOrEuropeEvaluationProcessRoute() != "USA")
							if($this->complaint->form->get("businessUnit")->getValue() == "automotive" && $this->complaint->form->get("g8d")->getValue() == "yes" && $this->complaint->getEvaluation()->form->get("complaintJustified")->getValue() == "YES")
							{
								if($this->complaint->getEvaluation()->form->get("fmeaReviewed")->getValue() == "")
								{
									$this->add_output("<error />");
									$this->complaint->getEvaluation()->form->get("fmeaReviewed")->setValid(false);
									$this->setPageAction("evaluation");
								}
							}
						}
					}


				}

				if($this->complaint->getConclusion())
				{

				}

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

				$this->complaint->save($this->getLocation());
				//$this->setPageAction("complaint");

				//commented out for debug BPD
				//$this->redirect("/apps/complaint/index?id=" . $this->complaint->getId());
			}
			else
			{
				//echo $this->complaint->form->isValid();exit;
				$this->add_output("<error />");

				// Find Errors
				if (!$this->complaint->form->isValid())
				{
					$this->setPageAction("complaint");
				}

				if($this->complaint->getEvaluation())
				{
					if (!$this->complaint->getEvaluation()->form->isValid())
					{
						$this->setPageAction("evaluation");
					}
				}

				if($this->complaint->getConclusion())
				{
					if (!$this->complaint->getConclusion()->form->isValid())
					{
						$this->setPageAction("conclusion");
					}
				}

			}
		}

	}

	public function doStuffAndShow($outputType="normal")
	{
		//echo "HERE";exit;
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
		if($this->getPageAction() == "complaint")
		{
			$this->setLocation("complaint");

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->complaint->form->get("sp_submitToExtSupplier")->getValue() == "Yes")
				{
					$this->complaint->form->get("sp_submitToExtSupplier")->setVisible(false);
				}
			}
		}

		if ($this->getPageAction() == "evaluation")
		{
			$this->setLocation("evaluation");

//			$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT category FROM complaint WHERE id = " . $this->complaint->form->get("id")->getValue() . "");
//			$fields = mysql_fetch_array($dataset);
//
//			$pos1 = "" . stripos($fields['category'], "M") . "";
//			$pos2 = "" . stripos($fields['category'], "D") . "";
//
//			page::addDebug("POSITION ONE!!!" . stripos($fields['category'], "M"), __FILE__, __LINE__);
//			page::addDebug("POSITION TWO!!!" . stripos($fields['category'], "D"), __FILE__, __LINE__);
//
//			if($pos1 == "0")
//			{
//				$this->complaint->getEvaluation()->form->get("g8d")->setVisible(false);
//			}
//			elseif($pos1 == "")
//			{
//				$this->complaint->getEvaluation()->form->get("g8d")->setVisible(true);
//			}

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				// do nothing
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				// do nothing
			}
			else
			{
				if(page::transformDateForMYSQL($this->complaint->getEvaluation()->form->get("dateSampleReceived")->getValue()) < page::transformDateForMYSQL($this->complaint->form->get("sampleReceptionDate")->getValue()))
				{
					//$this->complaint->getEvaluation()->form->get("dateSampleReceived")->setValid(false);
				}
			}

			$output .= "<complaintReport id=\"" . $requestID . "\">";

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->complaint->form->get("sp_materialInvolved")->getValue() == "No")
				{
					$this->complaint->getEvaluation()->form->get("complaintJustified")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("returnGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("disposeGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("sp_materialCredited")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("sp_materialReplaced")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("sp_useGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("sp_reworkGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("sp_sortGoods")->setVisible(false);
					//$this->complaint->getEvaluation()->form->get("sp_verificationMade")->setVisible(false);
				}

				if($this->complaint->form->get("g8d")->getValue() == "no")
				{
					$this->complaint->getEvaluation()->form->get("possibleSolutions")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("possibleSolutionsDate")->setVisible(false);

					$this->complaint->getEvaluation()->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("managementSystemReviewed")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("flowChart")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("fmea")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("customerSpecification")->setVisible(false);

					//$this->complaint->getEvaluation()->form->get("preventivePermCorrActions")->setVisible(false);
					//$this->complaint->getEvaluation()->form->get("estimatedDatePrev")->setVisible(false);
				}
			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				if($this->complaint->form->get("qu_materialInvolved")->getValue() == "No")
				{
					$this->complaint->getEvaluation()->form->get("qu_verificationMade")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_otherMaterialEffected")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_supplierIssue")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_customerIssue")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_supplierIssueAction")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("disposeGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_useGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_useGoodsDerongation")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_reworkGoods")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_otherSimilarProducts")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_authorGoodsDecision")->setVisible(false);
					$this->complaint->getEvaluation()->form->get("qu_authorGoodsDecisionDate")->setVisible(false);
				}
			}
			else
			{
				// This will show if the customer complaint type is North America and M/D/S complaint type.
				$cat = $this->complaint->form->get("category")->getValue();
				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA" && ($cat[0] == "M" || $cat[0] == "D" || $cat[0] == "S"))
				if($this->complaint->determineNAOrEuropeEvaluationProcessRoute() == "USA")
				{
					// Evaluation - Return the Goods Process
					if($this->complaint->getEvaluation()->form->get("returnGoods")->getValue() == "YES")
					{
						$this->complaint->getEvaluation()->form->get("returnGoods")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnRequestValue")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnRequestComment")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnRequestName")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnRequestCC")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnRequestSubmit")->setVisible(false);

						$this->complaint->getEvaluation()->form->get("returnGoodsReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("reAuthoriseReturnGoodsReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnRequestValueReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnRequestCommentReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnRequestNameReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnRequestCCReadOnly")->setVisible(true);
					}
					else
					{
						$this->complaint->getEvaluation()->form->get("returnApprovalRequest")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("reAuthoriseReturnGoodsReadOnly")->setVisible(false);
					}

					if($this->complaint->getEvaluation()->form->get("returnApprovalRequest")->getValue() == "YES")
					{
						$this->complaint->getEvaluation()->form->get("returnApprovalRequest")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalRequestComment")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalRequestName")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalRequestSubmit")->setVisible(false);

						$this->complaint->getEvaluation()->form->get("returnApprovalRequestReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnApprovalRequestCommentReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnApprovalRequestNameReadOnly")->setVisible(true);
					}

					// Evaluation - Dispose the Goods Process
					if($this->complaint->getEvaluation()->form->get("disposeGoods")->getValue() == "YES")
					{
						$this->complaint->getEvaluation()->form->get("disposeGoods")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalValue")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalComment")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalName")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalSubmit")->setVisible(false);

						$this->complaint->getEvaluation()->form->get("disposeGoodsReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("reAuthoriseDisposalGoodsReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalValueReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalCommentReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalNameReadOnly")->setVisible(true);
					}
					else
					{
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalRequest")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("reAuthoriseDisposalGoodsReadOnly")->setVisible(false);
					}

					if($this->complaint->getEvaluation()->form->get("returnApprovalDisposalRequest")->getValue() == "YES")
					{
						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalRequest")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnDisposalRequestComment")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnDisposalRequestName")->setVisible(false);
						$this->complaint->getEvaluation()->form->get("returnDisposalSubmit")->setVisible(false);

						$this->complaint->getEvaluation()->form->get("returnApprovalDisposalRequestReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnDisposalRequestCommentReadOnly")->setVisible(true);
						$this->complaint->getEvaluation()->form->get("returnDisposalRequestNameReadOnly")->setVisible(true);
					}
				}

				if($this->complaint->getEvaluation()->form->get("complaintJustified")->getValue() == "NO")
				{
//					$this->complaint->getEvaluation()->form->get("teamLeader")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("teamMember")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("rootCauses")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("failureCode")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("rootCauseCode")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("rootCausesAuthor")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("rootCausesDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("returnGoods")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("disposeGoods")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("updateInitiator")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("containmentAction")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("containmentActionAuthor")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("containmentActionDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("attributableProcess")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("possibleSolutions")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("possibleSolutionsAuthor")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("possibleSolutionsDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActions")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActionsAuthor")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActionsDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActionsEstimated")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActionsImplementation")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("implementedActionsEffectiveness")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActions")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActionsAuthor")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActionsDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActionsEstimatedDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActionsImplementedDate")->setVisible(false);
//					$this->complaint->getEvaluation()->form->get("preventiveActionsValidationDate")->setVisible(false);

					$this->complaint->getEvaluation()->form->processDependencies();
				}

				$datasetFMEA = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT fmea FROM evaluation WHERE complaintId = " . $_REQUEST['complaint'] . "");

				if(mysql_num_rows($datasetFMEA) > 0)
				{
					if($this->complaint->form->get("businessUnit")->getValue() == "automotive" && $this->complaint->form->get("g8d")->getValue() == "yes" && $this->complaint->getEvaluation()->form->get("complaintJustified")->getValue() == "YES")
					{
						$this->complaint->getEvaluation()->form->get("fmeaReviewed")->setVisible(true);
					}
				}
			}


			if ($outputType=="normal")
			{
				/*echo "<pre>";
				print_r($this->complaint->getEvaluation());
				echo "</pre>";
				exit;*/
				$output .= $this->complaint->getEvaluation()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->complaint->getEvaluation()->form->showLegend(false);
				$output .= $this->complaint->getEvaluation()->form->readOnlyOutput($exceptions);
			}
			$output .= "</complaintReport>";

			return $output;
		}

		if ($this->getPageAction() == "conclusion")
		{
			$this->setLocation("conclusion");

			$output .= "<complaintReport id=\"" . $requestID . "\">";

			if($this->complaint->getComplaintType($this->complaint->getId()) == "supplier_complaint")
			{
				if($this->complaint->form->get("sp_materialInvolved")->getValue() == 'No')
				{
					//$this->complaint->getConclusion()->form->get("sp_customerDerongation")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestDisposal")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_sapItemNumber")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_amount")->setVisible(false);
					//$this->complaint->getConclusion()->form->get("sp_value")->setVisible(false);
					//$this->complaint->getConclusion()->form->get("sp_currency")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorised")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialDisposed")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialDisposedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialDisposedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialDisposedCode")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialReturned")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialReturnedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_materialReturnedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_sapReturnNumber")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_supplierCreditNoteRec")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_supplierCreditNumber")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_comment")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_supplierReplacementRec")->setVisible(false);
				}

				if($this->complaint->getConclusion()->form->get("sp_requestDisposal")->getValue() == 'Yes')
				{
					$this->complaint->getConclusion()->form->get("sp_requestDisposal")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_sapItemNumber")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_amount")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_value")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestEmailText")->setVisible(false);
					$this->complaint->getConclusion()->form->get("processOwner3Request")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_sapItemNumber")->setVisible(false);
					$this->complaint->getConclusion()->form->get("submitRequest")->setVisible(false);

					$this->complaint->getConclusion()->form->get("sp_requestDisposalReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_sapItemNumberReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_amountReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_valueReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_requestEmailTextReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("processOwner3RequestReadOnly")->setVisible(true);
				}
				if($this->complaint->getConclusion()->form->get("sp_requestDisposal")->getValue() == 'No')
				{
					$this->complaint->getConclusion()->form->get("sp_requestAuthorised")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisorName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedEmailText")->setVisible(false);
					$this->complaint->getConclusion()->form->get("submitAuthorised")->setVisible(false);
				}

				if($this->complaint->getConclusion()->form->get("sp_requestAuthorised")->getValue() == 'Yes')
				{
					$this->complaint->getConclusion()->form->get("sp_requestAuthorised")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisorName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedEmailText")->setVisible(false);
					$this->complaint->getConclusion()->form->get("submitAuthorised")->setVisible(false);

					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedDateReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedNameReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisedEmailTextReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("sp_requestAuthorisorNameReadOnly")->setVisible(true);
				}

				if($this->complaint->getConclusion()->form->get("internalComplaintStatus")->getValue() == 'Closed')
				{
					$this->complaint->getConclusion()->form->get("totalClosureDate")->setVisible(true);
				}

			}
			elseif($this->complaint->getComplaintType($this->complaint->getId()) == "quality_complaint")
			{
				if($this->complaint->getConclusion()->form->get("qu_requestForDisposal")->getValue() == 'Yes')
				{
					$this->complaint->getConclusion()->form->get("qu_requestForDisposal")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_amount")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_requestDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_requestDisposalName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("requestForDisposalSubmit")->setVisible(false);

					$this->complaint->getConclusion()->form->get("qu_requestForDisposalReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_amountReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_requestDateReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_requestDisposalNameReadOnly")->setVisible(true);
				}
				else
				{
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorised")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedComment")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedCommentSubmit")->setVisible(false);
				}

				if($this->complaint->getConclusion()->form->get("qu_disposalAuthorised")->getValue() == 'Yes')
				{
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorised")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedDate")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedName")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedComment")->setVisible(false);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedCommentSubmit")->setVisible(false);

					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedDateReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedNameReadOnly")->setVisible(true);
					$this->complaint->getConclusion()->form->get("qu_disposalAuthorisedCommentReadOnly")->setVisible(true);
				}
			}
			else
			{
				//if(usercache::getInstance()->get(currentuser::getInstance()->getNTlogon())->getLocale() == "USA")
				if($this->complaint->determineNAOrEuropeConclusionProcessRoute() == "USA")
				{
					if($this->complaint->getConclusion()->form->get("requestForCredit")->getValue() == 'NO')
					{
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditAuthoriser2")->setVisible(false);

						$this->complaint->getConclusion()->form->get("requestForCreditRaised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
						$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("creditNoteValueReadOnly")->setVisible(false);

						$this->complaint->getConclusion()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);

					}

					if($this->complaint->getConclusion()->form->get("requestForCredit")->getValue() == 'YES')
					{
						$this->complaint->getConclusion()->form->get("processOwner3")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("creditNoteValue")->setVisible(false);
						$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
						$this->complaint->getConclusion()->form->get("ccCommercialCredit")->setVisible(false);
						$this->complaint->getConclusion()->form->get("ccCommercialCreditComment")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit1")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == '')
					{
						$this->complaint->getConclusion()->form->get("requestForCreditRaised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditAuthoriser2")->setVisible(false);

						$this->complaint->getConclusion()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
					{
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);

						$this->complaint->getConclusion()->form->get("reAuthoriseNACreditReadOnly")->setVisible(true);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
					{
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);

						$this->complaint->getConclusion()->form->get("reAuthoriseNACreditReadOnly")->setVisible(true);
					}

					if($this->complaint->form->get("creditNoteRequested")->getValue() == "NO")
					{
						$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(false);
						$this->complaint->getConclusion()->form->get("requestForCredit")->setValue("NO");
						//$this->complaint->getConclusion()->form->get("creditNoteValue")->setVisible(false);

						$datasetAmerican = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `salesOffice`, `complaintValue_quantity` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
						$fieldsAmerican = mysql_fetch_array($datasetAmerican);

						if($fieldsAmerican['complaintLocation'] == 'american')
						{

						}
						else
						{
							$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
							$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
							$this->complaint->getConclusion()->form->get("ccCommercialCredit")->setVisible(false);
							$this->complaint->getConclusion()->form->get("ccCommercialCreditComment")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
							$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
							$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
							$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);

							$this->complaint->getConclusion()->form->get("reAuthoriseNACreditReadOnly")->setVisible(false);
						}

					}
					else
					{
						//check the db to see if there has already been a credit request
						$datasetRequest = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(requestForCredit) as creditCount FROM conclusion WHERE complaintId = '" . $this->complaint->form->get("id")->getValue() . "'");
						$fieldsRequest = mysql_fetch_array($datasetRequest);
						if($fieldsRequest['creditCount'] == '0')
						{
							$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(true);
						}
						else
						{
							$datasetRequestValue = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT requestForCredit FROM conclusion WHERE complaintId = '" . $this->complaint->form->get("id")->getValue() . "'");
							$fieldsRequestValue = mysql_fetch_array($datasetRequestValue);
							if($fieldsRequestValue['requestForCredit'] == 'YES')
							{
								$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(false);
							}
							else
							{
								$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(true);
							}
						}
					}
				}
				else
				{
					if($this->complaint->getConclusion()->form->get("requestForCredit")->getValue() == 'NO')
					{
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReasonAdvise")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewCommercialOwner")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReasonAdvise2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);

						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditAuthoriser2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);

						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditAuthoriser2")->setVisible(false);

						$this->complaint->getConclusion()->form->get("requestForCreditRaised")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("informISDate")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("transferOwnership2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submitAdvise")->setVisible(false); // Added By Jason 27/12/2007
						$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
						$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("creditNoteValueReadOnly")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("creditAuthorisationStatus")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("requestForCredit")->getValue() == 'YES'/* && !isset($_REQUEST["sfID"])*/)
					{
						$this->complaint->getConclusion()->form->get("processOwner3")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("creditNoteValue")->setVisible(false);
						$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
						$this->complaint->getConclusion()->form->get("ccCommercialCredit")->setVisible(false);
						$this->complaint->getConclusion()->form->get("ccCommercialCreditComment")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit1")->setVisible(false);

					}

					if($this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->getValue() == '')
					{
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
						$this->complaint->getConclusion()->form->get("requestForCreditRaised")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("informISDate")->setVisible(false);
						//$this->complaint->getConclusion()->form->get("transferOwnership2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
						$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
						$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditAuthoriser2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);

						//readonly versions
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditAuthoriser2")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->getValue() == 'YES')
					{
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);


						$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == '')
					{
						$datasetCredit = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT creditNoteGBP_quantity, creditNoteValue_measurement, creditNoteValue_quantity FROM conclusion WHERE complaintId = " . $this->complaint->form->get("id")->getValue() . "");
						$fieldsCredit = mysql_fetch_array($datasetCredit);

						$level = "";

						if($fieldsCredit['creditNoteValue_measurement'] == 'EUR')
						{
							if($fieldsCredit['creditNoteValue_quantity'] > 5500)
							{
								$level = "higher";
							}
						}
						else
						{
							if($fieldsCredit['creditNoteGBP_quantity'] > 5000)
							{
								$level = "higher";
							}
						}

						if($level == "higher")
						{
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditAuthoriser")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
							$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);

							//readonly versions
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditAuthoriser2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialReason2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner2")->setVisible(false);

							//readonly versions
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialReasonAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);
						}
						else
						{
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise")->setValue("YES");
							$this->complaint->getConclusion()->form->get("commercialReasonAdvise2")->setValue("ADVISE NOT REQUIRED");
							//readonly versions
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditAuthoriserAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialReasonAdvise2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditNewCommercialOwner2")->setVisible(false);
						}
					}

					if($this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise")->getValue() == 'YES')
					{
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorisedAdvise")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReasonAdvise")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewCommercialOwner")->setVisible(false);


						$this->complaint->getConclusion()->form->get("submitAdvise")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->getValue() == 'NO')
					{
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);

						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);

						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == '')
					{
						//readonly versions
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditAuthoriser2")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == 'YES')
					{
						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);


						$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
					}

					if($this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->getValue() == 'NO')
					{
						$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);

						$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
						$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);

						$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);
						$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
					}

					if($this->complaint->form->get("creditNoteRequested")->getValue() == "NO")
					{
						$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(false);
						$this->complaint->getConclusion()->form->get("requestForCredit")->setValue("NO");
						//$this->complaint->getConclusion()->form->get("creditNoteValue")->setVisible(false);

						$datasetAmerican = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `complaintLocation`, `salesOffice`, `complaintValue_quantity` FROM complaint WHERE id = '"  . $this->complaint->getId()."'");
						$fieldsAmerican = mysql_fetch_array($datasetAmerican);

						if($fieldsAmerican['complaintLocation'] == 'american')
						{

						}
						else
						{
							$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
							$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
							$this->complaint->getConclusion()->form->get("ccCommercialCredit")->setVisible(false);
							$this->complaint->getConclusion()->form->get("ccCommercialCreditComment")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialLevelCreditAuthorised")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialReason")->setVisible(false);
							$this->complaint->getConclusion()->form->get("commercialCreditNewFinanceOwner")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeLevelCreditAuthorised")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeReason")->setVisible(false);
							$this->complaint->getConclusion()->form->get("financeCreditNewComplaintOwner")->setVisible(false);
							$this->complaint->getConclusion()->form->get("submit2")->setVisible(false);
							$this->complaint->getConclusion()->form->get("submit3")->setVisible(false);
							$this->complaint->getConclusion()->form->get("customerCreditNumber")->setVisible(false);
							$this->complaint->getConclusion()->form->get("dateCreditNoteRaised")->setVisible(false);
						}

					}
					else
					{
						//check the db to see if there has already been a credit request
						$datasetRequest = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT count(requestForCredit) as creditCount FROM conclusion WHERE complaintId = '" . $this->complaint->form->get("id")->getValue() . "'");
						$fieldsRequest = mysql_fetch_array($datasetRequest);
						if($fieldsRequest['creditCount'] == '0')
						{
							$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(true);
						}
						else
						{
							$datasetRequestValue = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT requestForCredit FROM conclusion WHERE complaintId = '" . $this->complaint->form->get("id")->getValue() . "'");
							$fieldsRequestValue = mysql_fetch_array($datasetRequestValue);
							if($fieldsRequestValue['requestForCredit'] == 'YES')
							{
								$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(false);
							}
							else
							{
								$this->complaint->getConclusion()->form->get("requestForCredit")->setVisible(true);
							}
						}
					}
				}




			}

			/*else if($this->complaint->getConclusion()->form->get("requestForCredit")->getValue() == 'YES' && isset($_REQUEST["sfID"]))
			{
				$this->complaint->getConclusion()->form->get("processOwner")->setVisible(false);
				$this->complaint->getConclusion()->form->get("creditNoteValue")->setVisible(false);
				$this->complaint->getConclusion()->form->get("transferOwnership")->setVisible(false);
				$this->complaint->getConclusion()->form->get("ccCommercialCredit")->setVisible(false);
				$this->complaint->getConclusion()->form->get("ccCommercialCreditComment")->setVisible(false);
				$this->complaint->getConclusion()->form->get("submit1")->setVisible(false);
			}*/


			if ($outputType=="normal")
			{
				$output .= $this->complaint->getConclusion()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->complaint->getConclusion()->form->showLegend(false);
				$output .= $this->complaint->getConclusion()->form->readOnlyOutput($exceptions);
			}
			$output .= "</complaintReport>";

			return $output;
		}


		/*if ($this->getPageAction() == "purchasing")
		{
			$this->setLocation("purchasing");

			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getPurchasing()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->ijf->getPurchasing()->form->showLegend(false);
				$output .= $this->ijf->getPurchasing()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";

			return $output;
		}
		if ($this->getPageAction() == "production")
		{
			$this->setLocation("production");

			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getProduction()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->ijf->getProduction()->form->showLegend(false);
				$output .= $this->ijf->getProduction()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";

			return $output;
		}
		if ($this->getPageAction() == "productOwner")
		{
			$this->setLocation("productOwner");

			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getProductOwner()->form->output();
			}
			else
			{
				$exceptions = array();


				$this->ijf->getProductOwner()->form->showLegend(false);
				$output .= $this->ijf->getProductOwner()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";
			$output .= "<orderControl />";
			return $output;
		}




		if ($this->getPageAction() == "order")
		{
			$output .= "<orderControl id=\"".$this->getOrderId()."\" />";

			page::addDebug("Show order", __FILE__, __LINE__);

			$output .= "<ijfReport id=\"".$requestID."\">";
			$output .= $this->ijf->getProductOwner($this->getOrderId())->form->output();
			$output .= "</ijfReport>";

			return $output;
		}


		if (preg_match("/^order_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("order");
			page::adddebug("set order id: " . $match[1],__FILE__,__LINE__);
			$this->setOrderId($match[1]);

			$output .= "<orderControl id=\"".$this->getOrderId()."\" />";

			page::addDebug("Show order", __FILE__, __LINE__);



			$output .= "<ijfReport orderId=\"" . $this->getOrderId() . "\">";
			if ($outputType=="normal")
			{
				$this->ijf->getProductOwner()->form->showLegend(false);
				$output .= $this->ijf->getProductOwner()->form->output();
			}
			else
			{
				$exceptions = array();


				$this->ijf->getProductOwner()->form->showLegend(false);
				$output .= $this->ijf->getProductOwner()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";




			$output .= "<ijfReport orderId=\"" . $this->getOrderId() . "\">";


			if ($outputType=="normal")
			{
				$output .= $this->ijf->getProductOwner()->getOrder($this->getOrderId())->form->output();
			}
			else
			{
				$output .= $this->ijf->getProductOwner()->getOrder($this->getOrderId())->form->readOnlyOutput();
			}

			$output .= "</ijfReport>";

			return $output;
		}*/



		/*if ($this->getPageAction() == "addorder")
		{
			$this->setLocation("order");
			$this->setOrderId($this->ijf->getProductOwner()->addOrder());

			$output .= "<orderControl id=\"".$this->getOrderId()."\" />";


			$output .= "<ijfReport orderId=\"" . $this->getOrderId() . "\">";
			$output .= $this->ijf->getProductOwner()->getOrder($this->getOrderId())->form->output();
			$output .= "</ijfReport>";

			return $output;
		}


		if (preg_match("/^removeorder_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->ijf->getProductOwner()->removeOrder($match[1]);
			$this->setLocation("demandPlanning");

			//$output .= "<attachmentControl />";
			$output .= "<orderControl />";

			$output .= "<ijfReport>";
			$output .= $this->ijf->getProductOwner()->form->output();
			$output .= "</ijfReport>";

			return $output;
		}*/


		/*if ($this->getPageAction() == "commercialPlanning")
		{
			$this->setLocation("commercialPlanning");

			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getcommercialPlanning()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->ijf->getcommercialPlanning()->form->showLegend(false);
				$output .= $this->ijf->getcommercialPlanning()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";

			return $output;
		}

		if ($this->getPageAction() == "finance")
		{
			$this->setLocation("finance");

			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getFinance()->form->output();
			}
			else
			{
				$exceptions = array();

				$this->ijf->getFinance()->form->showLegend(false);
				$output .= $this->ijf->getFinance()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";

			return $output;
		}*/

		if ($this->getPageAction() == "complaint")
		{
			$this->setLocation("complaint");
			//$this->complaint->sfID = $this->sfID;
			$today = date("Y-m-d");
			$today_date = strtotime($today);
			$complaintDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("customerComplaintDate")->getValue()));

			if($complaintDate > $today_date)
			{
				$this->complaint->form->get("customerComplaintDate")->setValid(false);
			}

			$receptionDate = strtotime(page::transformDateForMYSQL($this->complaint->form->get("sampleReceptionDate")->getValue()));

			if($receptionDate > $today_date)
			{
				$this->complaint->form->get("sampleReceptionDate")->setValid(false);
			}

			// content

			$output .= "<complaintReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->complaint->form->output();
			}
			else
			{

				$exceptions = array();

				// $this->complaint->form->processDependencies();

				//$this->complaint->form->get("sapName")->setVisible(true);
				//$this->complaint->form->get("externalSalesName")->setVisible(true);
				//$this->complaint->form->get("submitOnBehalf")->setVisible(true);

				$this->complaint->form->get("salesOffice")->setVisible(true);
				$this->complaint->form->showLegend(false);
				$output .= $this->complaint->form->readOnlyOutput($exceptions);
				//$output .= $this->complaint->form->processDependencies();
			}

			$output .= "</complaintReport>";

			return $output;
		}

		if ($this->getPageAction() == "complaintExternal")
		{
			$this->setLocation("complaintExternal");

			// content

			$output .= "<complaintReport id=\"" . $requestID . "\">";

			if ($outputType=="normal")
			{
				$this->complaintExternal->form->get("batchNumber")->getValue() != "" ? $this->complaintExternal->form->get("batchNumber")->getValue() : $this->complaintExternal->form->get("batchNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("sp_supplierItemNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_supplierItemNumber")->getValue() : $this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_supplierProductDescription")->getValue() != "" ? $this->complaintExternal->form->get("sp_supplierProductDescription")->getValue() : $this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
				$this->complaintExternal->form->get("sp_goodsReceivedDate")->getValue() != "" ? $this->complaintExternal->form->get("sp_goodsReceivedDate")->getValue() : $this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
				$this->complaintExternal->form->get("sp_goodsReceivedNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_goodsReceivedNumber")->getValue() : $this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_purchaseOrderNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_purchaseOrderNumber")->getValue() : $this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("sp_sampleSentDate")->getValue() != "" ? $this->complaintExternal->form->get("sp_sampleSentDate")->getValue() : $this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
				$this->complaintExternal->form->get("actionRequested")->getValue() != "" ? $this->complaintExternal->form->get("actionRequested")->getValue() : $this->complaintExternal->form->get("actionRequested")->setVisible(false);
				$this->complaintExternal->form->get("actionRequestedFromSupplier")->getValue() != "" ? $this->complaintExternal->form->get("actionRequestedFromSupplier")->getValue() : $this->complaintExternal->form->get("actionRequestedFromSupplier")->setVisible(false);
				$this->complaintExternal->form->get("sapItems")->getValue() != "" ? $this->complaintExternal->form->get("sapItems")->getValue() : $this->complaintExternal->form->get("sapItems")->setVisible(false);
				$this->complaintExternal->form->get("sp_quantityRecieved")->getValue() != "" ? $this->complaintExternal->form->get("sp_quantityRecieved")->getValue() : $this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
				$this->complaintExternal->form->get("sp_additionalComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_additionalComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("materialGroup")->getValue() != "" ? $this->complaintExternal->form->get("materialGroup")->getValue() : $this->complaintExternal->form->get("materialGroup")->setVisible(false);

				if($this->complaintExternal->form->get("complaintJustified")->getValue() == "NO")
				{
					$this->complaintExternal->form->get("returnGoods")->setVisible(false);
					$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);

					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("analysis")->setVisible(false);
					$this->complaintExternal->form->get("nameOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("dateOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("rootCauses")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesAuthor")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesDate")->setVisible(false);
					$this->complaintExternal->form->get("implementedActions")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsDate")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidated")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedDate")->setVisible(false);

					$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
				}

				if($this->complaintExternal->form->get("containmentActionAdded")->getValue() == "" || $this->complaintExternal->form->get("containmentActionAdded")->getValue() == "1")
				{
					$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
					$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
					$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
					$this->complaintExternal->form->get("returnGoods")->setVisible(false);
					$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
					$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
					$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
					$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
					$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
					$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
					$this->complaintExternal->form->get("complaintValue")->setVisible(false);
					$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
					$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);

					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
					$this->complaintExternal->form->get("flowChart")->setVisible(false);
					$this->complaintExternal->form->get("fmea")->setVisible(false);
					$this->complaintExternal->form->get("customerSpecification")->setVisible(false);

					$this->complaintExternal->form->get("analysis")->setVisible(false);
					$this->complaintExternal->form->get("nameOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("dateOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("rootCauses")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesAuthor")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesDate")->setVisible(false);
					$this->complaintExternal->form->get("implementedActions")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsDate")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidated")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedDate")->setVisible(false);
					$this->complaintExternal->form->get("additionalComments")->setVisible(false);
					$this->complaintExternal->form->get("submit")->setVisible(false);

					if($this->complaintExternal->form->get("warehouse")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
					}

					if($this->complaintExternal->form->get("productionRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("productionDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
					}

					if($this->complaintExternal->form->get("transitRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("transitDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
					}

//					if($this->complaintExternal->form->get("sp_materialInvolved")->getValue() == "No")
//					{
//						$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
//						$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
//						$this->complaintExternal->form->get("returnGoods")->setVisible(false);
//						$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("batchNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
//						$this->complaintExternal->form->get("productionDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
//						$this->complaintExternal->form->get("transitDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
//						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//					}
				}
				else
				{
//					if($this->complaintExternal->form->get("sp_materialInvolved")->getValue() == "No")
//					{
//						$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
//						$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
//						$this->complaintExternal->form->get("returnGoods")->setVisible(false);
//						$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("batchNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
//						$this->complaintExternal->form->get("productionDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
//						$this->complaintExternal->form->get("transitDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
//						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//					}

					if($this->complaintExternal->form->get("g8d")->getValue() == "no")
					{
						$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
						$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
						$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
						$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
						$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
						$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
						$this->complaintExternal->form->get("flowChart")->setVisible(false);
						$this->complaintExternal->form->get("fmea")->setVisible(false);
						$this->complaintExternal->form->get("customerSpecification")->setVisible(false);
					}

					if($this->complaintExternal->form->get("containmentActionSupplier")->getValue() != "" && $this->complaintExternal->form->get("containmentActionAdded")->getValue() == "2")
					{
						$this->complaintExternal->form->get("submitFirst")->setVisible(false);
						$this->complaintExternal->form->get("containmentActionSupplier")->setVisible(false);
						$this->complaintExternal->form->get("containmentActionReadOnly")->setVisible(true);
					}

				}

				if($this->complaintExternal->form->get("g8d")->getValue() == "no")
				{
					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
					$this->complaintExternal->form->get("flowChart")->setVisible(false);
					$this->complaintExternal->form->get("fmea")->setVisible(false);
					$this->complaintExternal->form->get("customerSpecification")->setVisible(false);
				}

				$output .= $this->complaintExternal->form->output();
			}
			else
			{
				$exceptions = array();

				$this->complaintExternal->form->get("batchNumber")->getValue() != "" ? $this->complaintExternal->form->get("batchNumber")->getValue() : $this->complaintExternal->form->get("batchNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("sp_supplierItemNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_supplierItemNumber")->getValue() : $this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_supplierProductDescription")->getValue() != "" ? $this->complaintExternal->form->get("sp_supplierProductDescription")->getValue() : $this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
				$this->complaintExternal->form->get("sp_goodsReceivedDate")->getValue() != "" ? $this->complaintExternal->form->get("sp_goodsReceivedDate")->getValue() : $this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
				$this->complaintExternal->form->get("sp_goodsReceivedNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_goodsReceivedNumber")->getValue() : $this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_purchaseOrderNumber")->getValue() != "" ? $this->complaintExternal->form->get("sp_purchaseOrderNumber")->getValue() : $this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
				$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("sp_sampleSentDate")->getValue() != "" ? $this->complaintExternal->form->get("sp_sampleSentDate")->getValue() : $this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
				$this->complaintExternal->form->get("actionRequested")->getValue() != "" ? $this->complaintExternal->form->get("actionRequested")->getValue() : $this->complaintExternal->form->get("actionRequested")->setVisible(false);
				$this->complaintExternal->form->get("actionRequestedFromSupplier")->getValue() != "" ? $this->complaintExternal->form->get("actionRequestedFromSupplier")->getValue() : $this->complaintExternal->form->get("actionRequestedFromSupplier")->setVisible(false);
				$this->complaintExternal->form->get("sapItems")->getValue() != "" ? $this->complaintExternal->form->get("sapItems")->getValue() : $this->complaintExternal->form->get("sapItems")->setVisible(false);
				$this->complaintExternal->form->get("sp_quantityRecieved")->getValue() != "" ? $this->complaintExternal->form->get("sp_quantityRecieved")->getValue() : $this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
				$this->complaintExternal->form->get("sp_additionalComplaintCost")->getValue() != "" ? $this->complaintExternal->form->get("sp_additionalComplaintCost")->getValue() : $this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
				$this->complaintExternal->form->get("materialGroup")->getValue() != "" ? $this->complaintExternal->form->get("materialGroup")->getValue() : $this->complaintExternal->form->get("materialGroup")->setVisible(false);

				if($this->complaintExternal->form->get("complaintJustified")->getValue() == "NO")
				{
					$this->complaintExternal->form->get("returnGoods")->setVisible(false);
					$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);

					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("analysis")->setVisible(false);
					$this->complaintExternal->form->get("nameOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("dateOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("rootCauses")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesAuthor")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesDate")->setVisible(false);
					$this->complaintExternal->form->get("implementedActions")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsDate")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidated")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedDate")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
				}

				if($this->complaintExternal->form->get("containmentActionAdded")->getValue() == "" || $this->complaintExternal->form->get("containmentActionAdded")->getValue() == "1")
				{
					$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
					$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
					$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
					$this->complaintExternal->form->get("returnGoods")->setVisible(false);
					$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
					$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
					$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
					$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
					$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
					$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
					$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
					$this->complaintExternal->form->get("complaintValue")->setVisible(false);
					$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
					$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
					$this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);

					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
					$this->complaintExternal->form->get("flowChart")->setVisible(false);
					$this->complaintExternal->form->get("fmea")->setVisible(false);
					$this->complaintExternal->form->get("customerSpecification")->setVisible(false);

					$this->complaintExternal->form->get("analysis")->setVisible(false);
					$this->complaintExternal->form->get("nameOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("dateOfAnalysis")->setVisible(false);
					$this->complaintExternal->form->get("rootCauses")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesAuthor")->setVisible(false);
					$this->complaintExternal->form->get("rootCausesDate")->setVisible(false);
					$this->complaintExternal->form->get("implementedActions")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("implementedActionsDate")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidated")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedAuthor")->setVisible(false);
					//$this->complaintExternal->form->get("implementedPermanentCorrectiveActionValidatedDate")->setVisible(false);
					$this->complaintExternal->form->get("additionalComments")->setVisible(false);
					//$this->complaintExternal->form->get("submit")->setVisible(false);

					if($this->complaintExternal->form->get("warehouse")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
					}

					if($this->complaintExternal->form->get("productionRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("productionDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
					}

					if($this->complaintExternal->form->get("transitRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("transitDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
					}

//					if($this->complaintExternal->form->get("sp_materialInvolved")->getValue() == "No")
//					{
//						$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
//						$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
//						$this->complaintExternal->form->get("returnGoods")->setVisible(false);
//						$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("batchNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
//						$this->complaintExternal->form->get("productionDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
//						$this->complaintExternal->form->get("transitDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
//						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//					}

				}
				else
				{
					if($this->complaintExternal->form->get("warehouse")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
					}

					if($this->complaintExternal->form->get("productionRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("productionDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
					}

					if($this->complaintExternal->form->get("transitRadio")->getValue() == "NO")
					{
						$this->complaintExternal->form->get("transitDate")->setVisible(false);
						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
					}

//					if($this->complaintExternal->form->get("sp_materialInvolved")->getValue() == "No")
//					{
//						$this->complaintExternal->form->get("sp_sampleSent")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sampleSentDate")->setVisible(false);
//						$this->complaintExternal->form->get("complaintJustified")->setVisible(false);
//						$this->complaintExternal->form->get("returnGoods")->setVisible(false);
//						$this->complaintExternal->form->get("disposeGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_useGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_reworkGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_sortGoods")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialCredited")->setVisible(false);
//						$this->complaintExternal->form->get("sp_materialReplaced")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierProductDescription")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedDate")->setVisible(false);
//						$this->complaintExternal->form->get("sp_goodsReceivedNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_detailsOfComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("sp_purchaseOrderNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("batchNumber")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//						$this->complaintExternal->form->get("sp_supplierItemNumber")->setVisible(false);
//						$this->complaintExternal->form->get("verificationOfStock")->setVisible(false);
//						$this->complaintExternal->form->get("warehouseDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity")->setVisible(false);
//						$this->complaintExternal->form->get("productionDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity2")->setVisible(false);
//						$this->complaintExternal->form->get("transitDate")->setVisible(false);
//						$this->complaintExternal->form->get("defectQuantity3")->setVisible(false);
//						$this->complaintExternal->form->get("invoiceDeliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("goodJobInvoiceNo")->setVisible(false);
//						$this->complaintExternal->form->get("deliveryNote")->setVisible(false);
//						$this->complaintExternal->form->get("sapItems")->setVisible(false);
//						$this->complaintExternal->form->get("sp_quantityRecieved")->setVisible(false);
//						$this->complaintExternal->form->get("quantityUnderComplaint")->setVisible(false);
//						$this->complaintExternal->form->get("complaintValue")->setVisible(false);
//						$this->complaintExternal->form->get("sp_additionalComplaintCost")->setVisible(false);
//						$this->complaintExternal->form->get("materialGroup")->setVisible(false);
//					}
//
//					if($this->complaintExternal->form->get("g8d")->getValue() == "no")
//					{
//						$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
//						$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
//						$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
//						$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
//						$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
//						$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
//						$this->complaintExternal->form->get("flowChart")->setVisible(false);
//						$this->complaintExternal->form->get("fmea")->setVisible(false);
//						$this->complaintExternal->form->get("customerSpecification")->setVisible(false);
//					}
//
//					if($this->complaintExternal->form->get("containmentAction")->getValue() != "")
//					{
//						$this->complaintExternal->form->get("submitFirst")->setVisible(false);
//						$this->complaintExternal->form->get("containmentAction")->setVisible(false);
//						$this->complaintExternal->form->get("containmentActionReadOnly")->setVisible(true);
//					}
				}

				if($this->complaintExternal->form->get("g8d")->getValue() == "no")
				{
					$this->complaintExternal->form->get("possibleSolutions")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsAuthor")->setVisible(false);
					$this->complaintExternal->form->get("possibleSolutionsDate")->setVisible(false);
					$this->complaintExternal->form->get("preventivePermCorrActions")->setVisible(false);
					$this->complaintExternal->form->get("estimatedDatePrev")->setVisible(false);
					$this->complaintExternal->form->get("managementSystemReviewed")->setVisible(false);
					$this->complaintExternal->form->get("flowChart")->setVisible(false);
					$this->complaintExternal->form->get("fmea")->setVisible(false);
					$this->complaintExternal->form->get("customerSpecification")->setVisible(false);
				}

				$this->complaintExternal->form->showLegend(false);
				$output .= $this->complaintExternal->form->readOnlyOutput($exceptions);
				//$output .= $this->complaint->form->processDependencies();
			}




//			if ($outputType=="normal")
//			{
//				$output .= $this->complaint->form->output();
//			}
//			else
//			{
//
//				$exceptions = array();
//
//				// $this->complaint->form->processDependencies();
//
//				//$this->complaint->form->get("sapName")->setVisible(true);
//				//$this->complaint->form->get("externalSalesName")->setVisible(true);
//				//$this->complaint->form->get("submitOnBehalf")->setVisible(true);
//				$this->complaintExternal->form->get("openDate")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaContact")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaTel")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaSite")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaEmail")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaSupplierName")->setVisible(false);
//				//$this->complaintExternal->form->get("scapaSupplierContact")->setVisible(false);
//				$this->complaintExternal->form->showLegend(false);
//				$output .= $this->complaintExternal->form->readOnlyOutput($exceptions);
//				//$output .= $this->complaint->form->processDependencies();
//			}

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
		if(isset($_REQUEST['status']))
		{
			if($_REQUEST['status'] == "complaint")
			{
				$_SESSION['apps'][$GLOBALS['app']]['location'] = "complaint";
			}
			elseif($_REQUEST['status'] == "evaluation")
			{
				$_SESSION['apps'][$GLOBALS['app']]['location'] = "evaluation";
			}
			elseif($_REQUEST['status'] == "conclusion")
			{
				$_SESSION['apps'][$GLOBALS['app']]['location'] = "conclusion";
			}
			else
			{
				$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
			}
		}
		else
		{
			page::addDebug("set location $location", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
		}
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
					$this->pageAction = "complaint";
				}
			}
		}

		return $this->pageAction;

	}

	public function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}

	public function buildMenu()
	{
		// report root


		$output = sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"complaint",
			$this->complaint->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'complaint' ? 'true' : 'false'
		);
		$output .= "</reportNav>";


		if ($this->complaint->getEvaluation())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"evaluation",
			$this->complaint->getEvaluation()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'evaluation' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}

		if ($this->complaint->getConclusion())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"conclusion",
			$this->complaint->getConclusion()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'conclusion' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}

		/*if ($this->ijf->getProduction())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"production",
			$this->ijf->getProduction()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'production' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}

		if ($this->ijf->getProductOwner())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"demandPlanning",
			$this->ijf->getProductOwner()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'productOwner' ? 'true' : 'false'
		);


		//for ($order=0; $order < count($this->ijf->getProductOwner()->getOrders()); $order++)
		//{
		//	$output .= sprintf('<orderNav id="%u"  valid="%s" selected="%s">',
		//		$order,
		//		$this->ijf->getProductOwner()->getOrder($order)->form->isValid() ? 'true' : 'false',
		//		($this->getLocation() == 'order' && $this->getOrderId()==$order) ? 'true' : 'false'
		//	);
		//
		//	$output .= "</orderNav>";
		//
		//}


		$output .= "</reportNav>";
		}

		if ($this->ijf->getCommercialPlanning())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"commercialPlanning",
			$this->ijf->getCommercialPlanning()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'commercialPlanning' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}

		if ($this->ijf->getFinance())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"finance",
			$this->ijf->getFinance()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'finance' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}

		/*if ($this->ijf->getProductionSite())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"productionSite",
			$this->ijf->getProductionSite()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'productionSite' ? 'true' : 'false'
		);
		$output .= "</reportNav>";
		}
		*/


		return $output;
	}

	public function getOrderId()
	{
		if (!isset($this->orderId))
		{
			$this->orderId = isset($_POST['orderId']) ? $_POST['orderId'] : "0";
		}

		return $this->orderId;
	}

	public function setOrderId($orderId)
	{
		$this->orderId = $orderId;
	}

	public function getComplaintType($id)
	{
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT typeOfComplaint FROM complaint WHERE `id` = '" . $id . "'");

		$fields = mysql_fetch_array($dataset);

		$complaintType = $fields['typeOfComplaint'];

		return $complaintType;
	}

}

?>