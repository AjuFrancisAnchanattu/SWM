<?php

class submit extends item
{
	private $action = "submit";
	
	function __construct($name)
	{
		parent::__construct($name);
		$this->setIgnore(true);
	}
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		
		$output = "";
		
		if ($this->getShowRow())
		{
			$output .= "<buttonrow>";
		}
		
		$output .= "<submit>";
		$output .= "<value>" . ($this->value!=null ? translate::getInstance()->translate($this->value) : "Submit") . "</value>";
		$output .= "<action>" . $this->action . "</action>";
		$output .= "</submit>";
	
		if ($this->getShowRow())
		{
			$output .= "</buttonrow>";
		}
		
		return $output;
	}
	
	public function readonlyoutput()
	{
		return "";
	}
	
	public function setAction($action)
	{
		$this->action = $action;
	}
}

?>