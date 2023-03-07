<?php
class myMeasurement extends itemlist
{
	private $form;
	private $quantity;
	private $measurement;
	
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	
	private $saveQuantityOnly = false;
	
	private $nullable = false;
	protected $required = true;
	private $measurementError = "measurement_error";
		
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
		//$this->measurement->clearData();
		//$this->form->add($this->measurement);
		$this->controlGroup->add($this->measurement);
		$this->form->add($this->controlGroup);
	}
	
	//override textbox original output
	public function output()
	{
		if($this->outputType == 0)
		{
			//textbox original output
			return $this->normalOutput();
		}
		else
		{
			//readonly output from item class
			return $this->readOnlyOutput();
		}
	}
	
	public function readOnlyOutput()
	{
		if (!$this->getVisible())
		{
			return "";
		}

		$complaintLib = new complaintLib();
		
		$output = $this->getRowTop(false);
		
		$quantity = $this->getQuantity();
		$measure = complaintLib::getOptionText( $this->getMeasurement() );
		
		$output .= "<readonly>" . page::formatAsParagraphs( $quantity . " " . $measure, "\n") . "</readonly>";

		$output .= $this->getRowBottom();

		return $output;
	}
	
	private function normalOutput()
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
	
	public function measurement_setOnChange( $javascript )
	{
		$this->measurement->setOnChange( $javascript . "();");
	}
	
	public function quantity_setOnChange( $javascript )
	{
		$this->quantity->setOnChange( $javascript );
	}
	
	public function setOnChange( $javascript )
	{
		$this->measurement_setOnChange( $javascript );
		$this->quantity_setOnChange( $javascript );
	}
	
	public function setSaveQuantityOnly()
	{
		$this->saveQuantityOnly = true;
	}
	
	//to set output type
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
	}
	
	public function setMeasurementError($error = "")
	{
		if ($error != "")
		{
			$this->measurementError = $error;
		}
	}
	
	//here we set if the controll is required or not
	//using 'itemlist' class variable
	//we do it so we can use item::isRequired() function
	public function setRequired($choice)
	{
		$this->measurement->setRequired($choice);
		$this->quantity->setRequired($choice);
		//inherited from 'item' class
		$this->required = $choice;
	}
	
	public function validate()
	{
		$valid = true;
		
		$value = $this->getQuantity();
		
		//validate currency
		//only if output is 'edit', controll is visible and 
		//is required or there is some value entered
		if($this->outputType == 0 && $this->getVisible() && ($this->isRequired() || $value != ""))
		{
			$pattern = "/^(\d|[1-9]\d*)(\.(\d\d?))?$/";
			
			if(preg_match($pattern, $value) == 0)
			{
				$valid = false;
				$this->setErrorMessage("field_error_thousand");
			}
			
			if($this->getMeasurement() == "")
			{
				$valid = false;
				
				$this->setErrorMessage($this->measurementError);
			}
		}
		
		//if there is some value entered in the controll
		//measurement must be chosen too
		if($this->outputType == 0 && $this->getVisible())
		{
			if( $value == "" && ($this->isRequired() || $this->getMeasurement() != "") && !$this->nullable)
			{
				$valid = false;
				$this->setErrorMessage("field_error");
			}
		}
		
		//using 'item' class method
		//to set the controll as a whole to valid/not valid
		$this->setValid($valid);
		
		page::addDebug("Validation Checking: " . $this->getName() . ": " . ($valid ? 'true' : 'false') , __FILE__, __LINE__);
		
		return $valid;
	}
	
	//we change functionality of measurement class again
	//it is because we do not set measurement or quantity as valid/not valid separately anymore
	//instead we change the whole controll as valid or not
	public function isValid()
	{
		//$this->valid is inherited from 'item' class
		return $this->valid;
	}
	
	//we change insert and update query
	//so we output data as a string in form quantity|measurement
	//instead of outputing value as 2 different controlls with _quantity and _measurement suffixes
	//this allows us to save the data using standard form::generate{Insert|Update}Query()
	//and still be able to load the data back using standard form::populate()
	public function generateInsertQuery()
	{
		$name = $this->getName();
		$quantity = $this->getQuantity();
		$measurement = $this->getMeasurement();
		
		if( $this->saveQuantityOnly )
		{
			if($this->nullable && $quantity == '')
			{
				return array(
					'name' => "`" . $name . "`",
					'value' => "NULL"
				);
			}
			else
			{
				return array(
					'name' => "`" . $name . "`",
					'value' => "'" . $quantity . "'"
				);
			}
		}
		else
		{
			if($this->nullable && $quantity == '')
			{
				return array(
					'name' => "`" . $name . "_quantity`, `" . $name . "_measurement`",
					'value' => "NULL, NULL"
				);
			}
			else
			{
				return array(
					'name' => "`" . $name . "_quantity`, `" . $name . "_measurement`",
					'value' => "'" . $quantity . "', '" . $measurement . "'"
				);
			}
		}
	}
	
	public function generateUpdateQuery()
	{
		$name = $this->getName();
		$quantity = $this->getQuantity();
		$measurement = $this->getMeasurement();
		
		if( $this->saveQuantityOnly )
		{
			if($this->nullable && $quantity == '')
			{
				return "`" . $name . "`= NULL";
			}
			else
			{
				return "`" . $name . "`= '" . $quantity . "'";
			}

		}
		else
		{
			if($this->nullable && $quantity == '')
			{
				//`" . $name . "`= '|', 
				return "`" . $name . "_quantity` = NULL,  
						`" . $name . "_measurement` = NULL";
			}
			else
			{
				
				//`" . $name . "`= '" . $quantity . "|" . $measurement . "', 
				return "`" . $name . "_quantity` = '" . $quantity . "',  
						`" . $name . "_measurement` = '" . $measurement . "'";
			}
		}
	}
	
	//*******************************************************
	
	public function clearData()
	{
		$this->measurement->clearData();
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

	public function getValue()
	{
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
	
	public function setRowTitle($rowTitle)
	{
		$this->rowTitle = $rowTitle;
	}
	
	public function getRowTitle()
	{
		return translate::getInstance()->translate($this->rowTitle);
	}
	
	public function getRowTitleTranslation()
	{
		return $this->rowTitle;
	}
}
?>