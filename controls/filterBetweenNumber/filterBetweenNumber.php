<?php

class filterBetweenNumber extends filter
{
	private $options;
	private $optionNames;
	private $sqlStatement = "";
	private $sqlDatabase = "";
	private $xmlStatement = "";
	private $xmlCacheTime;
	private $postback = false;
	private $min;
	private $max;
			
	function __construct($name)
	{
		$this->form = new form($name);
		
		$this->name = $name;
		
		$this->controlGroup = new group("controlGroup");
		
		$this->min = new textbox($this->name."MIN");
		$this->min->setShowRow(false);
		$this->min->setDataType("number");
		$this->controlGroup->add($this->min);
		
		$this->max = new textbox($this->name."MAX");
		$this->max->setShowRow(false);
		$this->max->setDataType("number");
		$this->controlGroup->add($this->max);
		
		$this->form->add($this->controlGroup);
	}
	
	public function output()
	{	
		$output = $this->getFilterRowTop();
		$output .= "<filterBetweenNumber>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<min>" . $this->min->output() . "</min>";
		$output .= "<max>" . $this->max->output() . "</max>";
		$output .= "<postback>" . $this->postback . "</postback>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . ($this->getDataType() == 'date' ? 'DD/MM/YYYY' : '') . "</legend>";
		$output .= "</filterBetweenNumber>";	
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
			$this->min->setValue($this->value[0]);
		}
		if (isset($value[1]))
		{
			$this->max->setValue($this->value[1]);
		}
	}

	
	public function getValue()
	{
		return $this->min->getValue() . "," . $this->max->getValue();
		//return implode(",",$this->value);
	}
	
	public function getComparisonType()
	{
		return "BETWEEN";
	}
}

?>