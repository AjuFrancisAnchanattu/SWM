<?php

class filterNumber extends filter
{
	private $options;
	private $optionNames;
	private $sqlStatement = "";
	private $sqlDatabase = "";
	private $xmlStatement = "";
	private $xmlCacheTime;
	private $postback = false;
	private $number;
			
	function __construct($name)
	{
		$this->form = new form($name);
		
		$this->name = $name;
		
		$this->controlGroup = new group("controlGroup");
		
		$this->number = new textbox($this->name."NUMBER");
		$this->number->setShowRow(false);
		$this->number->setDataType("number");
		$this->controlGroup->add($this->number);
		
		$this->form->add($this->controlGroup);
	}
	
	public function output()
	{	
		$output = $this->getFilterRowTop();
		$output .= "<filterBetweenNumber>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<number>" . $this->min->output() . "</number>";
		$output .= "<postback>" . $this->postback . "</postback>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . ($this->getDataType() == 'date' ? 'DD/MM/YYYY' : '') . "</legend>";
		$output .= "</filterNumber>";	
		$output .= $this->getFilterRowBottom();
		if ($this->isEnabled())
		{
			return $output;
		}
	}
	
	public function setValue($value)
	{
		if (is_array($value))
		{
			$this->value = $value;
		}
		elseif (is_string($value))
		{
			$this->value = explode(",",$value);
		}
		else 
		{
			echo "Unknown type set as value.";
		}
		if (isset($value[0]))
		{
			$this->number->setValue($this->value[0]);
		}
	}

	
	public function getValue()
	{
		return $this->number->getValue();
		//return implode(",",$this->value);
	}
	
	public function getComparisonType()
	{
		return "BETWEEN";
	}
}

?>