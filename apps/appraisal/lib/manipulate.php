<?php

require 'appraisal.php';


class manipulate extends page
{
	protected $appraisal;
	protected $pageAction;
	protected $reportActionId;
	protected $delegateForm;
	protected $valid = false;	
	
	function __construct()
	{
		parent::__construct();
	}
		
	// process form submissions
	public function processPost()
	{
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			page::addDebug("Current Location: " . $this->getLocation(), __FILE__, __LINE__);
						
			switch($this->getLocation())
			{
				case 'appraisal':
					$this->appraisal->form->processPost();
					$this->appraisal->form->processDependencies();
					// $this->appraisal->processHierarchy();
					break;
				case 'review':
					$this->appraisal->getReview()->form->processPost();
					$this->appraisal->form->processDependencies();
					break;
				case 'development':
					$this->appraisal->getDevelopment()->form->processPost();
					$this->appraisal->form->processDependencies();
					break;	
				case 'training':
					$this->appraisal->getTraining()->form->processPost();
					$this->appraisal->form->processDependencies();
					break;	
				case 'relationships':
					$this->appraisal->getRelationships()->form->processPost();
					$this->appraisal->form->processDependencies();
					break;		
			}
			
		}
	}
	
	
	
	public function validate()
	{
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
//			$this->getPageAction();
			
//			if ($this->getPageAction() == "appraisal" || $this->getPageAction() == "")
//			{			
				$ignore = false;
							
				if(isset($_GET['typeOfappraisal']) && $_GET['typeOfappraisal'] == "supplier_appraisal")
				{
					$today = date("Y-m-d");
					$today_date = strtotime($today);
					$appraisalDate = strtotime(page::transformDateForMYSQL($this->appraisal->form->get("customerappraisalDate")->getValue()));
				
					if($appraisalDate > $today_date)
					{
						$this->add_output("<error />");
						$this->setPageAction("appraisal");
					}
					
					if (isset($_POST['validate']) && $_POST['validate']=='true')
					{
						$this->valid = $this->appraisal->validate();
					}
				}
				else 
				{					
					if(isset($_REQUEST['appraisal']))
					{
						$_REQUEST['appraisal'] = $_REQUEST['appraisal'];
					}
					else 
					{
						$_REQUEST['appraisal'] = 0;
					}

					$today = date("Y-m-d");
					$today_date = strtotime($today);
					
					if (isset($_POST['validate']) && $_POST['validate']=='true')
					{
						$this->valid = $this->appraisal->validate();
					}						
				}
			
			//}
		
			
			if($this->appraisal->getReview())
			{														
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					$this->valid = $this->appraisal->validate();
				}
			}
			
			if($this->appraisal->getDevelopment())
			{				
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					$this->valid = $this->appraisal->validate();
				}
			}
			
			if($this->appraisal->getTraining())
			{				
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					$this->valid = $this->appraisal->validate();
				}
			}
			
			if($this->appraisal->getRelationships())
			{				
				if (isset($_POST['validate']) && $_POST['validate']=='true')
				{
					$this->valid = $this->appraisal->validate();
				}
			}
			
			//}
			
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE appraisal", __FILE__, __LINE__);
				
				if(isset($_REQUEST["sfID"]) && $_REQUEST["sfID"])
				{
					mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."' AND sfOwner = '".currentuser::getInstance()->getNTLogon()."' LIMIT 1");
				}
									
				$this->appraisal->save($this->getLocation());
			}
			else
			{
				$this->add_output("<error />");
				
				// Find Errors
				if (!$this->appraisal->form->isValid())
				{
					$this->setPageAction("appraisal");
				}
				
				if($this->appraisal->getReview())
				{
					if (!$this->appraisal->getReview()->form->isValid())
					{
						$this->setPageAction("review");
					}
				}
				
				if($this->appraisal->getDevelopment())
				{
					if (!$this->appraisal->getDevelopment()->form->isValid())
					{
						$this->setPageAction("development");
					}
				}
				
				if($this->appraisal->getTraining())
				{
					if (!$this->appraisal->getTraining()->form->isValid())
					{
						$this->setPageAction("training");
					}
				}
				
				if($this->appraisal->getRelationships())
				{
					if (!$this->appraisal->getRelationships()->form->isValid())
					{
						$this->setPageAction("relationships");
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
					
		if ($this->getPageAction() == "review")
		{
			
			$this->setLocation("review");
			
			$output .= "<appraisalReport id=\"" . $requestID . "\">";
				
			$this->appraisal->getReview()->form->processDependencies();
			
			if ($outputType=="normal")
			{
				$output .= $this->appraisal->getReview()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->appraisal->getReview()->form->showLegend(false);
				$output .= $this->appraisal->getReview()->form->readOnlyOutput($exceptions);
			}
			$output .= "</appraisalReport>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "development")
		{
			$this->setLocation("development");
			
			$output .= "<appraisalReport id=\"" . $requestID . "\">";
			
			// tasks and visibles here	

			if ($outputType=="normal")
			{
				$output .= $this->appraisal->getDevelopment()->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->appraisal->getDevelopment()->form->showLegend(false);
				$output .= $this->appraisal->getDevelopment()->form->readOnlyOutput($exceptions);
			}
			$output .= "</appraisalReport>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "training")
		{
			$this->setLocation("training");
			
			$output .= "<appraisalReport id=\"" . $requestID . "\">";
			
			// tasks and visibles here	

			if ($outputType=="normal")
			{
				$output .= $this->appraisal->getTraining()->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->appraisal->getTraining()->form->showLegend(false);
				$output .= $this->appraisal->getTraining()->form->readOnlyOutput($exceptions);
			}
			$output .= "</appraisalReport>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "relationships")
		{
			$this->setLocation("relationships");
			
			$output .= "<appraisalReport id=\"" . $requestID . "\">";
			
			// tasks and visibles here	

			if ($outputType=="normal")
			{
				$output .= $this->appraisal->getRelationships()->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->appraisal->getRelationships()->form->showLegend(false);
				$output .= $this->appraisal->getRelationships()->form->readOnlyOutput($exceptions);
			}
			$output .= "</appraisalReport>";
			
			return $output;
		}
		
		
		/*
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
		
		if ($this->getPageAction() == "appraisal")
		{
			$this->setLocation("appraisal");
			
			$today = date("Y-m-d");
									
			// content
			$output .= "<appraisalReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{				
				$output .= $this->appraisal->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->appraisal->form->showLegend(false);
				$output .= $this->appraisal->form->readOnlyOutput($exceptions);		
			}
			
			$output .= "</appraisalReport>";
			
			return $output;
		}		
	}
		
	
	
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO appraisal", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'appraisal';
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
					$this->pageAction = "appraisal";
				}
			}
		}
		
		return $this->pageAction;
		
	}
	
	public function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}	
	
}

?>