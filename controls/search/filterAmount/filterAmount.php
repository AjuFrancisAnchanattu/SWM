<?php

class filterAmount extends item
{
	private $options;
	private $optionNames;
	private $sqlStatement = "";
	private $sqlDatabase = "";
	private $xmlStatement = "";
	private $xmlCacheTime;
	private $postback = false;
	//private $min;
	//private $max;
			
	function __construct($name)
	{
		$this->setRowType("filter");
		$this->setVisible(false);
		
		$this->setField($name);
		
		$this->form = new form($name);
		
		$this->name = $name;
		
		$this->controlGroup = new group("controlGroup");
		
		$min = new textbox($this->name."MIN");
		$min->setShowRow(false);
		$min->setRequired(true);
		$min->setDataType("string");
		$this->controlGroup->add($min);
		
		$max = new textbox($this->name."MAX");
		$max->setShowRow(false);
		$max->setRequired(true);
		$max->setDataType("string");
		$this->controlGroup->add($max);
		
		$this->form->add($this->controlGroup);
		$this->form->processPost();
	}
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<filterAmount>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<min>" . $this->form->get($this->name."MIN")->output() . "</min>";
		$output .= "<max>" . $this->form->get($this->name."MAX")->output() . "</max>";
		$output .= "<postback>" . $this->postback . "</postback>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . ($this->getDataType() == 'date' ? 'DD/MM/YYYY' : '') . "</legend>";
		$output .= "</filterAmount>";
			
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function processPost($value)
	{
		
		if (!is_null($value))
		{
			$this->form->processPost();
			$this->setValue($this->form->get($this->name ."MIN")->getValue() . "|" . $this->form->get($this->name ."MAX")->getValue());
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
			$this->value = explode("|",$value);
		}
		else 
		{
			echo "Unknown type set as value.";
		}
		
		if (isset($this->value[0]))
		{
			$this->form->get($this->name."MIN")->setValue($this->value[0]);
		}
		
		if (isset($this->value[1]))
		{
			$this->form->get($this->name."MAX")->setValue($this->value[1]);
		}
	}

	public function getValue()
	{
		//return implode(",",$this->value);
		//echo ($this->form->get($this->name ."MIN")->getValue() . "|" . $this->form->get($this->name ."MAX")->getValue());
		return ($this->form->get($this->name ."MIN")->getValue() . "|" . $this->form->get($this->name ."MAX")->getValue());
	}
	
	public function setField($field)
	{
		$this->field = $field;
	}
	
	public function generateSQL()
	{
		$sql = "";
		
		//$value = $this->getValue();
		
		//if (strlen($this->form->get($this->name ."MIN")->getValue()) > 0 && strlen($this->form->get($this->name ."MAX")->getValue()) > 0)
		//{
			//$exploded = explode(",", $value);
			$sql = $this->field . " >= '" . common::transformDateForMYSQL($this->form->get($this->name ."MIN")->getValue()) . "' AND " . $this->field . " <= '" . common::transformDateForMYSQL($this->form->get($this->name ."MAX")->getValue()) . "'";
			page::addDebug("$sql", __FILE__, __LINE__);
		//}
		
		return $sql;
	}
	
	
	public function validate()
	{
		$valid = false;
		
		if ($this->_checkDate($this->form->get($this->name ."MIN")->getValue()) && $this->_checkDate($this->form->get($this->name ."MAX")->getValue()))
		{
			$valid = true;
		}
		
		$this->setValid($valid);
		
		return $valid;
	}
	
	public function _checkDate($value)
	{
		if (!preg_match("/^[0-3][0-9]\/[0-1][0-9]\/[0-9]{4}$/", $value))
		{
			return false;
		}
		
		$dateArray = explode("/",$value);
		
		if (!checkdate($dateArray[1],$dateArray[0],$dateArray[2]))
		{
			return false;
		}
		
		return true;
	}
}

?>