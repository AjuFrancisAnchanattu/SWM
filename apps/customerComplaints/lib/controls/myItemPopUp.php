<?php
class myItemPopUp extends itemPopUp
{
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	
	private $afterUpdateJs = null;
	
	public function setAfterUpdate( $functionName )
	{
		$this->afterUpdateJs = $functionName;
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
	
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function readOnlyOutput()
	{
		/*
		if (!$this->getVisible())
		{
			return "";
		}*/
		
		$output = $this->getRowTop(false);
		
		$output .= "<itemPopUp>";
		$output .= "<readonly />";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<popUpURL>" . $this->popUpURL . "</popUpURL>";	
		$output .= "</itemPopUp>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	//**************
	private $url;
	private $popUpURL;
	private $popUpButtonText;
	private $rowCount;
	private $data = array();
	
	private $validateDatabase;
	private $validateQuery;
	private $overideValidate = false;
	
	
	public function normalOutput()
	{
		/*if (!$this->getVisible())
		{
			return "";
		}*/
		
		$output = $this->getRowTop();
			
		$output .= "<itemPopUp>";
		$output .= "<legend>" . $this->value . "</legend>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<url>" . $this->url . "</url>";
		$output .= "<popUpURL>" . $this->popUpURL . "</popUpURL>";
		$output .= "<popUpButtonText>" . $this->popUpButtonText . "</popUpButtonText>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if( $this->afterUpdateJs != null && $this->afterUpdateJs != "" )
		{
			$output .= "<afterUpdate>" . $this->afterUpdateJs . "</afterUpdate>";
		}
		
		if (!empty($this->onBlur))
		{
			$output .= "<onBlur>" . $this->onBlur . "</onBlur>";
		}
		
		$output .= "</itemPopUp>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setUrl($url)
	{
		$this->url = $url;
	}
	
	public function setPopUpUrl($popUpURL)
	{
		$this->popUpURL = $popUpURL;
	}
	
	public function setPopUpButtonText($popUpButtonText)
	{
		$this->popUpButtonText = $popUpButtonText;
	}
		

	public function setData($database, $sql)
	{
		$dataset = mysql::getInstance()->selectDatabase($database)->Execute($sql);
		
		while ($fields = mysql_fetch_array($dataset))
		{
			array_push($this->data, array('name' => $fields[1], 'value' => $fields[0]));
		}	
	}
	
	
	public function getData()
	{
		return $this->data;
	}
	
	public function generateInsertQuery()
	{
		$value = $this->getValue();
		
		if (isset($this->data))
		{
			for ($i=0;$i<count($this->data);$i++)
			{
				if ($this->value == $this->data[$i]['name'])
				{
					$value = $this->data[$i]['value'];
				}
			}
		}
		
		$output = array(
			'name' =>  $this->getName(),
			'value' => "'" . $value . "'"
			);
	
		return $output;
	}
	
	public function generateUpdateQuery()
	{
		$value = $this->getValue();
		
		if (isset($this->data))
		{
			for ($i=0;$i<count($this->data);$i++)
			{
				if ($this->value == $this->data[$i]['name'])
				{
					$value = $this->data[$i]['value'];
				}
			}
		}
	
		$output = "`".$this->getName()."` = '" . $value . "'";					
		
		return $output;
	}
	
	
	public function setValidateQuery($database, $table, $field)
	{
		$this->overideValidate = true;
		
		$this->validateDatabase = $database;
		$this->validateQuery = "SELECT `$field` FROM $table WHERE `$field` LIKE ";
	}

	
	public function validate()
	{
		if ($this->overideValidate)
		{
			$valid = true;
				
			if ($this->getVisible())
			{
				$dataset = mysql::getInstance()->selectDatabase($this->validateDatabase)->Execute($this->validateQuery . " '" . $this->getValue() . "'");
				
				$valid = (mysql_num_rows($dataset) == 0 ? false : true);
			}
			
			page::addDebug("Auto valid: " . ($valid ? 'true' : 'false'), __FILE__, __LINE__);
			
			$this->setValid($valid);
			
			return $valid;
		}
		else 
		{
			return parent::validate();
		}
	}
	
	public function setOnBlur($javascript)
	{
		$this->onBlur = $javascript;
	}
}
?>