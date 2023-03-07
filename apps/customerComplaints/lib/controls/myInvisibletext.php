<?php
class myInvisibletext extends invisibletext
{
	private $nullable = false;
	
	public function readOnlyOutput()
	{
		return "";
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
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