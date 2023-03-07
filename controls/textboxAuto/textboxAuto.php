<?php

class textboxAuto extends item
{	
	private $cssClass = "textbox";
	private $onKeyPress = "";
	
	
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
		
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
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
	
}

?>