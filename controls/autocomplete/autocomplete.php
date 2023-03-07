<?php

class autocomplete extends textbox
{	
	private $url;
	private $rowCount;
	private $data = array();
	
	private $validateDatabase;
	private $validateQuery;
	private $overideValidate = false;
	
	
	public function output()
	{
		/*if (!$this->getVisible())
		{
			return "";
		}*/
		
		$output = $this->getRowTop();
			
		$output .= "<autocomplete>";
		$output .= "<legend>" . $this->value . "</legend>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<url>" . $this->url . "</url>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if (!empty($this->onBlur))
		{
			$output .= "<onBlur>" . $this->onBlur . "</onBlur>";
		}
		
		$output .= "</autocomplete>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setUrl($url)
	{
		$this->url = $url;
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
		$this->validateQuery = "SELECT `$field` FROM $table WHERE `$field` = ";
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