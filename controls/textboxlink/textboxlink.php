<?php

class textboxlink extends item
{	
	private $openNewWindow = "";
	
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
		
		$output = $this->getRowTop();
		
		$output .= "<textboxlink>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<link>" . $this->link . "</link>";
		$output .= "<openNewWindow>" . $this->getOpenNewWindow() . "</openNewWindow>";		
		$output .= "</textboxlink>";
		
		$output .= $this->getRowBottom();
		
		return $output;
		
		
	}
	
	public function setOpenNewWindow($openNewWindow)
	{
		$this->openNewWindow = $openNewWindow;
	}
	
	public function getOpenNewWindow()
	{
		return $this->openNewWindow;
	}
}

?>