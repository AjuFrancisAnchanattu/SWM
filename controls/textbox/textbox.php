<?php

class textbox extends item
{	
	private $cssClass = "textbox";
	private $onKeyPress = "";
	private $onChange = "";
	private $anchorRef = "";
	
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		if ($this->getDataType() == 'date')
		{
			$this->setLegend('DD/MM/YYYY');
		}
		
		$output = $this->getRowTop();
			
		$output .= "<textbox>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<minlength>" . $this->getMinLength() . "</minlength>"; // Added by JM
		$output .= "<cssClass>" . $this->cssClass . "</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . $this->getLegend() . "</legend>";
		$output .= "<anchorRef>" . $this->getAnchorRef() . "</anchorRef>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
		}
		
		if (!empty($this->onChange))
		{
			$output .= "<onChange>" . $this->onChange . "</onChange>";
		}
		
		$output .= "</textbox>";
		
		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function setCssClass($class)
	{
		$this->cssClass = $class;
	}
	
	public function setOnKeyPress($javascript)
	{
		$this->onKeyPress = $javascript;
	}
	
	public function setOnChange($javascript)
	{
		$this->onChange = $javascript;
	}
	
	public function setAnchorRef($anchorRef)
	{
		$this->anchorRef = $anchorRef;
	}
	
	public function getAnchorRef()
	{
		return $this->anchorRef;
	}
	
}

?>