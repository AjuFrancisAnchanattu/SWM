<?php

require 'docman.php';


class manipulate extends page
{
	protected $docman;
	protected $pageAction;
	protected $reportActionId;
	protected $valid = false;
	
	
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
				case 'docman':
					$this->docman->form->processPost();
					// $this->docman->processHierarchy();
					break;
			}
			
			$this->docman->form->processDependencies();
		}
	}
	
	
	
	public function validate()
	{
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->docman->validate();
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE DOCMAN", __FILE__, __LINE__);
				
				$this->docman->save($this->getLocation());
				//$this->setPageAction("docman");
				
				//commented out for debug BPD
				//$this->redirect("/apps/docman/index?id=" . $this->docman->getId());
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->docman->form->isValid())
				{
					$this->setPageAction("docman");
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
		
		
		
		
		if ($this->getPageAction() == "docman")
		{
			$this->setLocation("docman");
		
			
			
			// content
			
			$output .= "<docManReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->docman->form->output();
			}
			else 
			{
				$exceptions = array();
				
				$this->docman->form->showLegend(false);
				$output .= $this->docman->form->readOnlyOutput($exceptions);
			}
			$output .= "</docManReport>";
			
			return $output;
		}	
		
	}
		
	
	
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO DOCMAN", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'docman';
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
					$this->pageAction = "docman";
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
			"docman",
			$this->docman->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'docman' ? 'true' : 'false'
		);		
		$output .= "</reportNav>";
		

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