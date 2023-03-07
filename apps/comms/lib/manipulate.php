<?php

require 'comm.php';

class manipulate extends page
{
	protected $comm;
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
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			page::addDebug("Current Location: " . $this->getLocation(), __FILE__, __LINE__);
			
			switch($this->getLocation())
			{
				case 'comm':
					$this->comm->form->processPost();
					$this->comm->form->processDependencies();
					break;
			}
		}
	}
		
	public function validate()
	{		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{			
			if(isset($_REQUEST['comm']))
			{
				$_REQUEST['comm'] = $_REQUEST['comm'];
			}
			else 
			{
				$_REQUEST['comm'] = 0;
			}
		}
		
		page::addDebug("page action:" .$this->getPageAction(), __FILE__, __LINE__);
		
		if ($this->getPageAction() == "submit")
		{			
			
			if($this->comm->form->get("subject")->getValue() == "" || $this->comm->form->get("body")->getValue() == "")
			{
				$this->valid = false;
			}
			else 
			{
				$this->valid = true;
			}
			
			if($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				page::addDebug("SAVE THE comm", __FILE__, __LINE__);
				
				$this->comm->save($this->getLocation());
			}
			else
			{				
				$this->add_output("<error />");
				
				// Find Errors				
				//if (!$this->comm->form->isValid())
				//{
					$this->setPageAction("comm");
					
					if($this->comm->form->get("subject")->getValue() == "")
					{
						$this->comm->form->get("subject")->setValid(false);
					}
					
					if($this->comm->form->get("body")->getValue() == "")
					{
						$this->comm->form->get("body")->setValid(false);
					}
				//}
			}
			
		}
		else 
		{
			if(isset($_REQUEST['comm']) && isset($_REQUEST['status']))
			{
				if($_REQUEST['status'] == "comm")
				{
					$this->comm->load($_REQUEST['comm'], false);
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
		
		// THIS IS THE comm PAGE ACTION START
		if ($this->getPageAction() == "comm")
		{
			$this->setLocation("comm");
		
			// content
			$output .= "<commReport id=\"" . $requestID . "\">";
			
			// EDIT - This is the Edit Field part of the IF Statement
			if ($outputType=="normal")
			{				
				$output .= $this->comm->form->output();
			}
			else 
			{
				$this->comm->form->get("newsType")->setVisible(false);
				
				$this->comm->form->showLegend(false);
				$output .= $this->comm->form->readOnlyOutput();
			}
			
			$output .= "</commReport>";
			
			return $output;
		}		
	}
		
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			page::addDebug("DEFAULT SETTING LOCATION TO comm", __FILE__, __LINE__);
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'comm';
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
					$this->pageAction = "comm";
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