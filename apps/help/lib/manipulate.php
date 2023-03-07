<?php

require 'helpApp.php';

class manipulate extends page
{
	protected $helpApp;
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
				case 'helpApp':
					$this->helpApp->form->processPost();
					break;
			}
			
			$this->helpApp->form->processDependencies();
		}
	}
	
	
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->swfValid = true;
				
			// check if swfXsize and swfYsize are ints.
			if($this->helpApp->form->get("swfXsize")->getValue() != "" && !is_numeric($this->helpApp->form->get("swfXsize")->getValue()))
			{
				$this->helpApp->form->get("swfXsize")->setValid(false);
				$this->setPageAction("helpApp");
				$this->swfValid = false;
			}
			
			// check if swfXsize and swfYsize are ints.
			if($this->helpApp->form->get("swfYsize")->getValue() != "" && !is_numeric($this->helpApp->form->get("swfYsize")->getValue()))
			{
				$this->helpApp->form->get("swfYsize")->setValid(false);
				$this->setPageAction("helpApp");
				$this->swfValid = false;
			}
			
			// checks to see if there are attachments, AND swf sizes are entered.
			if(count($this->helpApp->form->get("attachment")->getValue()) > 0)
			{
				if($this->helpApp->form->get("swfYsize")->getValue() == "")
				{
					$this->helpApp->form->get("swfYsize")->setValid(false);
					$this->setPageAction("helpApp");
					$this->swfValid = false;
				}
				
				if($this->helpApp->form->get("swfXsize")->getValue() == "")
				{
					$this->helpApp->form->get("swfXsize")->setValid(false);
					$this->setPageAction("helpApp");
					$this->swfValid = false;
				}
			}
			
			if(!$this->swfValid)
			{
				return false;
			}
			
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->helpApp->validate();
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE helpApp", __FILE__, __LINE__);
				
				$this->helpApp->save($this->getLocation());
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->helpApp->form->isValid())
				{
					$this->setPageAction("helpApp");
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
		
		
		if ($this->getPageAction() == "helpApp")
		{
			$this->setLocation("helpApp");
		
			// content
			
			$output .= "<helpReport id=\"" . $requestID . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->helpApp->form->output();
			}
			else 
			{
				if($this->helpApp->form->get("status")->getValue() == 'helpApp')
				{
					$this->helpApp->form->get("addDetails")->setVisible(true); // Show addDetails in view mode
					$this->helpApp->form->get("submitAddDetails")->setVisible(true); // Show addDetails submit button in view mode	
				}
				
				$exceptions = array("addDetails", "submitAddDetails", "delegateTo");
				
				$this->helpApp->form->showLegend(false);
				$output .= $this->helpApp->form->readOnlyOutput($exceptions);
			}
			$output .= "</helpReport>";
			
			return $output;
		}
		
	}
		
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO helpApp", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'helpApp';
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
					$this->pageAction = "helpApp";
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
			"npi",
			$this->helpApp->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'helpApp' ? 'true' : 'false'
		);		
		$output .= "</reportNav>";
		
		return $output;
	}
}

?>