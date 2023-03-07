<?php

class multiNTLogon extends item
{	
	private $cssClass = "textbox";
	private $onKeyPress = "";
	private $onChange = "";
	private $onClick = "";
	
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
			
		$output .= "<multiNTLogon>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<minlength>" . $this->getMinLength() . "</minlength>"; // Added by JM
		$output .= "<cssClass>" . $this->cssClass . "</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . $this->getLegend() . "</legend>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
		}
		
		if (!empty($this->onChange))
		{
			$output .= "<onChange>" . $this->onChange . "</onChange>";
		}
		
		if (!empty($this->onClick))
		{
			$output .= "<onClick>" . $this->onClick . "</onClick>";
		}
		
		$output .= "</multiNTLogon>";
		
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
	
	public function setOnClick($javascript)
	{
		$this->onClick = $javascript;
	}
	
}

?>