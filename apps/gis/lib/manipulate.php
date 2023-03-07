<?php

require 'gis.php';

class manipulate extends page
{
	protected $gis;
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
		
//			echo $this->getLocation();
//			die();
			
			switch($this->getLocation())
			{
				case 'gis':
					$this->gis->form->processPost();
					// $this->gis->processHierarchy();
				break;
					
				case 'addNewSection':
					//die("here");
					$this->gis->addNewSection()->form->processPost();
				break;
			}

			$this->gis->form->processDependencies();
		}
	}



	public function validate()
	{
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->gis->validate();
			}
		}

		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE gis", __FILE__, __LINE__);
				
				$this->gis->save($this->getLocation());
				$this->setPageAction("gis");

				//commented out for debug BPD
				//$this->redirect("/apps/gis/index?id=" . $this->gis->getId());
			}
			else
			{
				$this->add_output("<error />");

				// find first error.

				if (!$this->gis->form->isValid())
				{
					$this->setPageAction("gis");
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
		
		//echo "this->getPageAction: " . $this->getPageAction() . "<br />";
		
		if ($this->getPageAction() == "gis")
		{
			$this->setLocation("gis");

			// content

			$output .= "<gisReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->gis->form->output();
			}
			else
			{
				$exceptions = array();

				// $this->gis->form->processDependencies();

				$this->gis->form->showLegend(false);
				$output .= $this->gis->form->readOnlyOutput($exceptions);
				//$output .= $this->gis->form->processDependencies();
			}

			$output .= "</gisReport>";

			return $output;
		}

		if ($this->getPageAction() == "addNewSection")
		{
			$this->setLocation("addNewSection");

			// content
			
			$output .= "<gisReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->gis->getAddNewSection()->form->output();
				
				//var_dump($this->gis->form->output());
			}
			else
			{
				$exceptions = array();

				// $this->gis->form->processDependencies();

				$this->gis->getAddNewSection()->form->showLegend(false);
				$output .= $this->gis->getAddNewSection()->form->readOnlyOutput($exceptions);
			}

			$output .= "</gisReport>";

			return $output;
		}
		
		

//		if (preg_match("/^order_([0-9]+)$/", $this->getPageAction(), $match))
//		{
//			$this->setLocation("order");
//			page::adddebug("set order id: " . $match[1],__FILE__,__LINE__);
//			$this->setOrderId($match[1]);
//
//			$output .= "<orderControl id=\"".$this->getOrderId()."\" />";
//
//			page::addDebug("Show order", __FILE__, __LINE__);
//
//
//
//			$output .= "<gisReport orderId=\"" . $this->getOrderId() . "\">";
//			if ($outputType=="normal")
//			{
//				$this->gis->getProductOwner()->form->showLegend(false);
//				$output .= $this->gis->getProductOwner()->form->output();
//			}
//			else
//			{
//				$exceptions = array();
//
//
//				$this->gis->getProductOwner()->form->showLegend(false);
//				$output .= $this->gis->getProductOwner()->form->readOnlyOutput($exceptions);
//			}
//			$output .= "</gisReport>";
//
//
//
//
//			$output .= "<gisReport orderId=\"" . $this->getOrderId() . "\">";
//
//
//			if ($outputType=="normal")
//			{
//				$output .= $this->gis->getProductOwner()->getOrder($this->getOrderId())->form->output();
//			}
//			else
//			{
//				$output .= $this->gis->getProductOwner()->getOrder($this->getOrderId())->form->readOnlyOutput();
//			}
//
//			$output .= "</gisReport>";
//
//			return $output;
//		}
//
//
//
//		if ($this->getPageAction() == "addorder")
//		{
//			$this->setLocation("order");
//			$this->setOrderId($this->gis->getProductOwner()->addOrder());
//
//			$output .= "<orderControl id=\"".$this->getOrderId()."\" />";
//
//
//			$output .= "<gisReport orderId=\"" . $this->getOrderId() . "\">";
//			$output .= $this->gis->getProductOwner()->getOrder($this->getOrderId())->form->output();
//			$output .= "</gisReport>";
//
//			return $output;
//		}
//
//
//		if (preg_match("/^removeorder_([0-9]+)$/", $this->getPageAction(), $match))
//		{
//			$this->gis->getProductOwner()->removeOrder($match[1]);
//			$this->setLocation("demandPlanning");
//
//			//$output .= "<attachmentControl />";
//			$output .= "<orderControl />";
//
//			$output .= "<gisReport>";
//			$output .= $this->gis->getProductOwner()->form->output();
//			$output .= "</gisReport>";
//
//			return $output;
//		}
//
//
//		if ($this->getPageAction() == "commercialPlanning")
//		{
//			$this->setLocation("commercialPlanning");
//
//			$output .= "<gisReport id=\"" . $requestID . "\">";
//			if ($outputType=="normal")
//			{
//				$output .= $this->gis->getcommercialPlanning()->form->output();
//			}
//			else
//			{
//				$exceptions = array();
//
//				$this->gis->getcommercialPlanning()->form->showLegend(false);
//				$output .= $this->gis->getcommercialPlanning()->form->readOnlyOutput($exceptions);
//			}
//			$output .= "</gisReport>";
//
//			return $output;
//		}
//
//		if ($this->getPageAction() == "finance")
//		{
//			$this->setLocation("finance");
//
//			$output .= "<gisReport id=\"" . $requestID . "\">";
//
//			
//
//			if ($outputType=="normal")
//			{
//				$output .= $this->gis->getFinance()->form->output();
//			}
//			else
//			{
//				$exceptions = array();
//
//				$this->gis->getFinance()->form->showLegend(false);
//				$output .= $this->gis->getFinance()->form->readOnlyOutput($exceptions);
//			}
//			$output .= "</gisReport>";
//
//			return $output;
//		}
//
//
//		if ($this->getPageAction() == "gis")
//		{
//			$this->setLocation("gis");
//
//
//
//			// content
//
//			$output .= "<gisReport id=\"" . $requestID . "\">";
//			if ($outputType=="normal")
//			{
//				$output .= $this->gis->form->output();
//			}
//			else
//			{
//				$exceptions = array();
//
//				// $this->gis->form->processDependencies();
//
//				$this->gis->form->showLegend(false);
//				$output .= $this->gis->form->readOnlyOutput($exceptions);
//				//$output .= $this->gis->form->processDependencies();
//			}
//
//			$output .= "</gisReport>";
//
//			return $output;
//		}

	}



	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO gis", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'gis';
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
					$this->pageAction = "gis";
				}
			}
			
			if(isset($_REQUEST['action']) && substr($_REQUEST['action'],0,17) == "remove_attachment")
			{
				$this->pageAction = "gis";
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
		"gis",
		$this->gis->form->isValid() ? 'true' : 'false',
		$this->getLocation() == 'gis' ? 'true' : 'false'
		);
		$output .= "</reportNav>";


		


		return $output;
	}

//	public function getOrderId()
//	{
//		if (!isset($this->orderId))
//		{
//			$this->orderId = isset($_POST['orderId']) ? $_POST['orderId'] : "0";
//		}
//
//		return $this->orderId;
//	}
//
//	public function setOrderId($orderId)
//	{
//		$this->orderId = $orderId;
//	}

}

?>