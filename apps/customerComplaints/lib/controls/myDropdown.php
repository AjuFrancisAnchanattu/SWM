<?php
class myDropdown extends dropdown
{
	//0 - normal output
	//1 - read-only output
	private $outputType = 0;
	private $nullable = false;
	
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
	
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
	}
	
	public function validate()
	{
		if($this->outputType == 1)
		{
			$this->setValid(true);
			return true;
		}
		else
		{
			return parent::validate();
		}
	}
	
	public function generateInsertQuery()
	{
		if($this->nullable && $this->getValue() == "")
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
		if($this->nullable && $this->getValue() == "")
		{
			return "`" . $this->getName() . "` = NULL";
		}
		else
		{
			return parent::generateUpdateQuery();
		}
	}
}
?>