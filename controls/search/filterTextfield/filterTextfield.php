<?php

class filterTextfield extends textboxAuto
{	
	private $url;
	private $data = array();
	
	private $validateDatabase;
	private $validateQuery;
	private $overideValidate = false;
	
	private $field;
	

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		
		$this->setRowType("filter");
		$this->setVisible(false);
	}
	
	
	public function output()
	{
		/*if (!$this->getVisible())
		{
			return "";
		}*/
		
		$output = $this->getRowTop();
			
		$output .= "<autocomplete>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<url>" . $this->url . "</url>";
		$output .= "</autocomplete>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setField($field)
	{
		$this->field = $field;
	}
	
	public function generateSQL()
	{
		$sql = "";
		
		$value = $this->getValue();
		
		if (strlen($value) > 0)
		{
			$exploded = explode("||", $value);
		  	$sql = $this->field . " LIKE ('%" . implode("','", $exploded) . "%')";
		 //$sql = $this->field . " LIKE ('%" . implode("','", $exploded) . "%',)"; //  changed this on 08/10/2008
			page::addDebug("$sql", __FILE__, __LINE__);
		}
		
		return $sql;
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
}

?>