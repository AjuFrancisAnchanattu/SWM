<?php
class myAutocomplete extends autocomplete
{
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	private $nullable = false;

	private $validateDatabase;
	private $validateQuery;
	
	private $afterUpdateJs = null;
	private $url = null;
	
	public function setUrl($url)
	{
		$this->url = $url;
	}
	
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

	public function normalOutput()
	{
		/*if (!$this->getVisible())
		{
			return "";
		}*/

		$output = $this->getRowTop();

		$output .= "<autocomplete>";
		$output .= "<legend>" . $this->value . "</legend>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<url>" . $this->url . "</url>";
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

		$output .= "</autocomplete>";

		$output .= $this->getRowBottom();

		return $output;
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
	
	//we change that, because we always validate with query
	public function setValidateQuery($database, $table, $field, $extraCondition = "")
	{		
		$this->validateDatabase = $database;
		if( $extraCondition != "" )
		{
			$this->validateQuery = "SELECT `$field` FROM $table WHERE $extraCondition AND `$field` LIKE ";
		}
		else
		{
			$this->validateQuery = "SELECT `$field` FROM $table WHERE `$field` LIKE ";
		}
	}

	//we change that, because we want to vlaidate 
	//only if the control is not in read only mode
	public function validate()
	{
		$valid = true;
			
		if ( $this->getVisible() && $this->outputType == 0 && 
			($this->required || (!$this->required && $this->getValue() != '') ) )
		{
			$sql = $this->validateQuery . " '" . $this->getValue() . "'";
			
			$dataset = mysql::getInstance()->selectDatabase($this->validateDatabase)
				->Execute($sql);
			
			$valid = (mysql_num_rows($dataset) == 0 ? false : true);
		}
		
		page::addDebug("Auto valid: " . ($valid ? 'true' : 'false'), __FILE__, __LINE__);
		
		$this->setValid($valid);
		
		return $valid;
	}
	
	public function setNullable( $choice = true )
	{
		$this->nullable = $choice;
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
			//return parent::generateInsertQuery();
			return array(
				'name' => "`" . $this->getName() . "`",
				'value' => "'" . $this->getValue() . "'"
			);
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
			return "`" . $this->getName() . "` = '" . $this->getValue() . "'";
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
}
?>