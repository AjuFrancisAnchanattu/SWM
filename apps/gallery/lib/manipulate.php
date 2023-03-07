<?php

require 'gallery.php';


class manipulate extends page
{
	protected $gallery;
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
				case 'gallery':
					$this->gallery->form->processPost();
					$this->gallery->form->processDependencies();
					break;			
			}
		}
	}
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if(isset($_REQUEST['gallery']))
			{
				$_REQUEST['gallery'] = $_REQUEST['gallery'];
			}
			else 
			{
				$_REQUEST['gallery'] = 0;
			}
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->gallery->validate();
			}
				
		}
			
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE gallery", __FILE__, __LINE__);
//				if(isset($_REQUEST["sfID"]) && $_REQUEST["sfID"]){
//					mysql::getInstance()->selectDatabase("gallerys")->Execute("DELETE FROM savedForms WHERE sfID = '".$_REQUEST["sfID"]."' AND sfOwner = '".currentuser::getInstance()->getNTLogon()."' LIMIT 1");
//				}
									
				$this->gallery->save($this->getLocation());
			}
			else
			{
			
				$this->add_output("<error />");
				
				// Find Errors
				if (!$this->gallery->form->isValid())
				{
					$this->setPageAction("gallery");
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
		
		if ($this->getPageAction() == "gallery")
		{
			$this->setLocation("gallery");
									
			// content
			
			$output .= "<galleryReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{		
				$output .= $this->gallery->form->output();
			}
			else 
			{
				$exceptions = array();
								
				$this->gallery->form->showLegend(false);
				$output .= $this->gallery->form->readOnlyOutput($exceptions);		
			}
			
			$output .= "</galleryReport>";
			
			return $output;
		}
	}
		
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO gallery", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'gallery';
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
					$this->pageAction = "gallery";
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