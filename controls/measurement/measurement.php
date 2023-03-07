<?php

class measurement extends itemlist
{			
	private $form;
	private $quantity;
	private $measurement;
	
	function __construct($name)
	{
		$this->form = new form($name);
		
		$this->name = $name;
		
		$this->controlGroup = new group("controlGroup");
			
		$this->quantity = new textbox($this->name."_quantity");
		$this->quantity->setShowRow(false);
		$this->quantity->setDataType("decimal");
		$this->quantity->setCssClass("quantity");
		//$this->form->add($this->quantity);
		$this->controlGroup->add($this->quantity);
		
		$this->measurement = new dropdown($this->name."_measurement");
		$this->measurement->setShowRow(false);
		$this->measurement->setDataType("text");
		$this->measurement->setCssClass("measurement");
		$this->measurement->clearData();
		//$this->form->add($this->measurement);
		$this->controlGroup->add($this->measurement);
		$this->form->add($this->controlGroup);
	}
	
	public function clearData()
	{
		$this->measurement->clearData();
	}
	
	public function generateInsertQuery()
	{
		$quantity = $this->form->get($this->name.'_quantity')->generateInsertQuery();
		$measurement = $this->form->get($this->name.'_measurement')->generateInsertQuery();
		
		
		$output = array(
			'name' => $quantity['name'] . "," . $measurement['name'],
			'value' => $quantity['value'] . "," . $measurement['value']
		);
		
	
		return $output;
	}
	
	public function generateUpdateQuery()
	{
		$quantity = $this->form->get($this->name.'_quantity')->generateUpdateQuery();
		$measurement = $this->form->get($this->name.'_measurement')->generateUpdateQuery();
	
		$output = $quantity . ", " . $measurement;			
		
		return $output;
	}
	

	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<measurement>";
		$output .= "<name>" . $this->name . "</name>";
	
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<quantity>" . $this->quantity->output() . "</quantity>";
		$output .= "<measure>" . $this->measurement->output() . "</measure>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . ($this->value[1] == $this->options[$i]['value'] ? 'yes' : 'no') . "\">" . page::xmlentities($this->options[$i]['display']) . "</option>\n";
		}
		$output .= "</measurement>";
		
		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function setRequired($choice)
	{
		$this->measurement->setRequired($choice);
		$this->quantity->setRequired($choice);
	}
	
	
	public function setSQLSource($database, $sql)
	{
		$this->measurement->setSQLSource($database, $sql);
	}
	
	public function setXMLSource($xml,$cacheTime=86400)
	{
		$this->measurement->setXMLSource($xml,$cacheTime=86400);
	}

	public function setArraySource($array)
	{
		$this->measurement->setArraySource($array);
	}
	
	public function setQuantity($value)
	{
		$this->quantity->setValue($value);
	}
	
	public function setMeasurement($value)
	{
		$this->measurement->setValue($value);
	}
	
	public function getQuantity()
	{
		return $this->form->get($this->name ."_quantity")->getValue();
	}
	
	public function getMeasurement()
	{
		return $this->form->get($this->name ."_measurement")->getValue();
	}

	
	
	public function processPost($value)
	{		
		if (!is_null($value))
		{
			$this->form->processPost();
			
			$this->setValue($this->form->get($this->name ."_quantity")->getValue() . "|" . $this->form->get($this->name ."_measurement")->getValue());
			//$this->value[] = $this->form->get($this->name ."_quantity")->getValue();
			//$this->value[] = $this->form->get($this->name ."_measurement")->getValue();
			//$this->setValue($value);
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
			$this->quantity->setValue($this->value[0]);
		}
		
		if (isset($this->value[1]))
		{
			$this->measurement->setValue($this->value[1]);
		}
	}
	
	public function validate()
	{
		$valid = false;
		if ($this->quantity->validate())
		{
			$valid = true;
		}
		if ($this->measurement->validate())
		{
			$valid = true;
		}
		
		// Changed to actually validate the values in the measurement 29/10/2008
//		if(is_numeric($this->quantity->getValue()))
//		{
//			$valid = true;
//		}
//		else 
//		{
//			$valid = false;
//		}
		
		return $valid;
	}
	
	public function isValid()
	{
		$valid = true;
		
		if ($this->quantity->isValid() == false || $this->measurement->isValid() == false)
		{
			$valid = false;
		}
		return $valid;
	}

	public function getValue()
	{
		//return implode(",",$this->value);
		
		return $this->form->get($this->name ."_quantity")->getValue() . "|" . $this->form->get($this->name ."_measurement")->getValue();
	}
	
	public function getDisplayValue()
	{
		return $this->form->get($this->name ."_quantity")->getValue() . " " . $this->form->get($this->name ."_measurement")->getValue();
	}
	
	public function setOnKeyPress($javascript)
	{
		$this->form->get($this->name ."_quantity")->setOnKeyPress($javascript);
	}
}

?>