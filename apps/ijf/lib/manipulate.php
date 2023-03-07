<?php

require 'ijf.php';


class manipulate extends page
{
	protected $ijf;
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
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			page::addDebug("Current Location: " . $this->getLocation(), __FILE__, __LINE__);
			
			switch($this->getLocation())
			{
				case 'ijf':
					$this->ijf->form->processPost();
					// $this->ijf->processHierarchy();
					break;
				case 'dataAdministration':
					$this->ijf->getDataAdministration()->form->processPost();
					break;
				case 'quality':
					$this->ijf->getQuality()->form->processPost();
					break;
				case 'productManager':
					$this->ijf->getProductManager()->form->processPost();
					break;
				case 'purchasing':
					$this->ijf->getPurchasing()->form->processPost();
					break;
				case 'production':
					$this->ijf->getProduction()->form->processPost();
					break;
				case 'productOwner':
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
			
			}
			
			$this->ijf->form->processDependencies();
		}
	}
	
	
	
	public function validate()
	{
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->ijf->validate();
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE IJF", __FILE__, __LINE__);
				
				$this->ijf->save($this->getLocation());
				//$this->setPageAction("ijf");
				
				//commented out for debug BPD
				//$this->redirect("/apps/ijf/index?id=" . $this->ijf->getId());
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->ijf->form->isValid())
				{
					$this->setPageAction("ijf");
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
		
		
		if ($this->getPageAction() == "dataAdministration")
		{
			$this->setLocation("dataAdministration");
			
			$output .= "<ijfReport id=\"" . $requestID . "\">";

	//		die($this->ijf->getFinance->form->get("barMan")->getValue());
//			
//			if($this->ijf->getFinance->get("barManView")->getValue() == "yes")
//			{
//				if($this->ijf->getFinance()->form->get("barManViewComplete")->getValue() == "no")			
//				{
//					$this->ijf->getFinance()->form->get("barMan")->setVisible(true);
//					$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(true);
//					$this->ijf->getFinance()->form->get('barMan')->setValue('This IJF requires a Bar/Man View Request');
//					$this->ijf->getFinance()->form->get('barManShow')->setVisible(false);
//				}
//				else 
//				{
//					$this->ijf->getFinance()->form->get("barMan")->setVisible(false);
//					$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(false);
//					$this->ijf->getFinance()->form->get('barManShow')->setValue($this->ijf->getFinance()->form->get("barManViewComplete")->getValue());
//				}
//			}
//			else
//			{
//				$this->ijf->getFinance()->form->get("barMan")->setVisible(false);
//				$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(false);	
//				$this->ijf->getFinance()->form->get('barManShow')->setVisible(false);				
//			}
			
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getDataAdministration()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->ijf->getDataAdministration()->form->showLegend(false);
				$output .= $this->ijf->getDataAdministration()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";
			
			
			return $output;
		}
		
		/*if ($this->getPageAction() == "productionSite")
		{
			$this->setLocation("productionSite");
			
			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getProductionSite()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->ijf->getProductionSite()->form->showLegend(false);
				$output .= $this->ijf->getProductionSite()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";
			
			return $output;
		}
		*/
		
		if ($this->getPageAction() == "purchasing")
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
		if ($this->getPageAction() == "quality")
		{
			$this->setLocation("quality");
			
			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getQuality()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->ijf->getQuality()->form->showLegend(false);
				$output .= $this->ijf->getQuality()->form->readOnlyOutput($exceptions);
			}
			$output .= "</ijfReport>";
			
			return $output;
		}
		if ($this->getPageAction() == "productManager")
		{
			$this->setLocation("productManager");
			
			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ijf->getProductManager()->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->ijf->getProductManager()->form->showLegend(false);
				$output .= $this->ijf->getProductManager()->form->readOnlyOutput($exceptions);
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
		}
		
		
				
		if ($this->getPageAction() == "addorder")
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
		}
		
		
		if ($this->getPageAction() == "commercialPlanning")
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
			
			if($this->ijf->form->get("barManView")->getValue() == "yes")
			{
				if($this->ijf->getFinance()->form->get("barManViewComplete")->getValue() == "no")			
				{
					$this->ijf->getFinance()->form->get("barMan")->setVisible(true);
					$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(true);
					$this->ijf->getFinance()->form->get('barMan')->setValue('This IJF requires a Bar/Man View Request');
					$this->ijf->getFinance()->form->get('barManShow')->setVisible(false);
				}
				else 
				{
					$this->ijf->getFinance()->form->get("barMan")->setVisible(false);
					$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(false);
					$this->ijf->getFinance()->form->get('barManShow')->setValue($this->ijf->getFinance()->form->get("barManViewComplete")->getValue());
				}
			}
			else
			{
				$this->ijf->getFinance()->form->get("barMan")->setVisible(false);
				$this->ijf->getFinance()->form->get("barManViewComplete")->setVisible(false);	
				$this->ijf->getFinance()->form->get('barManShow')->setVisible(false);				
			}
			
			
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
		}
		
		
		if ($this->getPageAction() == "ijf")
		{
			$this->setLocation("ijf");
		
			
			
			// content
			
			$output .= "<ijfReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{	
				$output .= $this->ijf->form->output();
			}
			else 
			{
				$exceptions = array();
				
				// $this->ijf->form->processDependencies();
				
				$this->ijf->form->get("existingCustomerName")->setValue(page::xmlentities($this->ijf->form->get("existingCustomerName")->getValue()));
				$this->ijf->form->get("existingCustomerName")->setVisible(true);
				
				$this->ijf->form->showLegend(false);
				$output .= $this->ijf->form->readOnlyOutput($exceptions);				
				//$output .= $this->ijf->form->processDependencies();
			}
			
			$output .= "</ijfReport>";
			
			return $output;
		}	
		
	}
		
	
	
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO ijf", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'ijf';
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
					$this->pageAction = "ijf";
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
			"ijf",
			$this->ijf->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'ijf' ? 'true' : 'false'
		);		
		$output .= "</reportNav>";
		
		
		if ($this->ijf->getDataAdministration())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"dataAdministration",
			$this->ijf->getDataAdministration()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'dataAdministration' ? 'true' : 'false'
			);	
			$output .= "</reportNav>";
		}
		
		if ($this->ijf->getPurchasing())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"purchasing",
			$this->ijf->getPurchasing()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'purchasing' ? 'true' : 'false'
			);	
			$output .= "</reportNav>";
		}
		
		if ($this->ijf->getProduction())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"production",
			$this->ijf->getProduction()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'production' ? 'true' : 'false'
			);	
			$output .= "</reportNav>";
		}
		
		if ($this->ijf->getProductManager())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"productManager",
			$this->ijf->getProductManager()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'quality' ? 'true' : 'false'
			);	
			$output .= "</reportNav>";
		}
		if ($this->ijf->getQuality())
		{
			$output .= sprintf('<reportNav item="%s" valid="%s" selected="%s">',
			"quality",
			$this->ijf->getQuality()->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'quality' ? 'true' : 'false'
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
	
}

?>