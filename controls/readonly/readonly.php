<?php

class readonly extends item
{
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

		$output .= "<readonly name=\"". $this->name . "\">" . page::formatAsParagraphs($this->getDisplayValue()) . "</readonly>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
}

?>