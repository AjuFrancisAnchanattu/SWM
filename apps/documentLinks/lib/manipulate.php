<?php

require 'docLink.php';

class manipulate extends page
{
	protected $docLink;
	protected $pageAction;
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
				case 'docLink':
					$this->docLink->form->processPost();
					break;
			}
			
			$this->docLink->form->processDependencies();
		}
	}
	
	
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
				
			
			
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->docLink->validate();
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE docLink", __FILE__, __LINE__);
				
				$this->docLink->save($this->getLocation());
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->docLink->form->isValid())
				{
					$this->setPageAction("docLink");
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
		
		
		if ($this->getPageAction() == "docLink")
		{
			$this->setLocation("docLink");
		
			// content
			
			$output .= "<docLinkReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->docLink->form->output();
			}
			else 
			{
				$this->docLink->form->showLegend(false);
				$output .= $this->docLink->form->readOnlyOutput($exceptions);
			}
			$output .= "</docLinkReport>";
			
			return $output;
		}
		
	}
		
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO docLink", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'docLink';
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
					$this->pageAction = "docLink";
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
			"docLink",
			$this->docLink->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'docLink' ? 'true' : 'false'
		);		
		$output .= "</reportNav>";
		
		return $output;
	}
}

?>