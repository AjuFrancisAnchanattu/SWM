<?php
class myTextbox extends textbox
{
	private $validationType = "none";
	private $nullable = false;
	
	public static $PERCENT = "percent";
	public static $CURRENCY = "currency";
	public static $NUMBER = "number";
	public static $EMAIL = "email";
	
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	
	//override textbox original output
	public function output()
	{
		if($this->outputType == 0)
		{
			//textbox original output
			return parent::output();
		}
		else
		{
			//readonly output from item class
			return $this->readOnlyOutput();
		}
	}
	
	public function isReadOnly()
	{
		return $this->outputType == 1 ? true : false;
	}
	
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
	}
	
	//we set what data will be in the textbox
	public function setValidationType($type)
	{
		$this->validationType = $type;
	}
	
	//validation changes
	//it is needed for validating percentages or currencies in multigroup
	//as we can not use measurement in multigroup
	public function validate()
	{
		$valid = true;
		$value = $this->getValue();
		$error = $this->getErrorMessage();
		
		if($this->outputType == 0 && $this->getVisible() && $value != "")
		{
			switch($this->validationType)
			{
				//set appropriate regex
				case self::$PERCENT:
					$pattern = "/^(\d|[1-9]\d)(\.\d\d?)?$/";
					$error = "percent_error";
					break; //validateRegex
					
				case self::$CURRENCY:
					$pattern = "/^(\d|[1-9]\d*)(\.(\d{2}))?$/";
					$error = "currency_error";
					break;
				
				case self::$NUMBER:
					$pattern = "/^\d*$/";
					$error = "number_error";
					break;
					
				case self::$EMAIL:
					$pattern = "/^[A-Za-z0-9._%+-]+@(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,4}$/";
					$error = "email_error";
					break;
				
				case "none":
					return parent::validate();
			}
			
			//validate value against that regex
			$valid =  $this->validateRegex($pattern, $value);
		}
		else if( $this->outputType == 0 && $this->getVisible() && $this->isRequired() && $value == "" )
		{
			$valid = false;
			$error = "value_required";
		}
		
		$this->setValid($valid);
		$this->setErrorMessage( $error );
		
		page::addDebug("Validation Checking: " . $this->getName() . ": " . ($valid ? 'true' : 'false') , __FILE__, __LINE__);
		
		return $valid;
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	private function validateRegex($pattern, $value)
	{
		$valid = true;
		
		if(preg_match($pattern, $value) == 0)
		{
			$valid = false;
		}
		else
		{
			$valid = true;
		}
		
		return $valid;
	}
	
	public function resetValue()
	{
		$this->setValue("");
	}
	
	public function generateInsertQuery()
	{
		if($this->nullable && $this->getValue() == '')
		{
			return array(
				'name' => "`" . $this->getName() . "`",
				'value' => "NULL"
			);
		}
		else
		{
			return parent::generateInsertQuery();
		}
	}
	
	public function generateUpdateQuery()
	{
		if($this->nullable && $this->getValue() == '')
		{
			return "`" . $this->getName() . "` = NULL";
		}
		else
		{
			return parent::generateUpdateQuery();
		}
	}
	
	public function preInsertOperations()
	{
		if( !$this->nullable)
		{
			parent::preInsertOperations();
		}
	}
	
	public function preUpdateOperations()
	{
		if( !$this->nullable)
		{
			parent::preUpdateOperations();
		}
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