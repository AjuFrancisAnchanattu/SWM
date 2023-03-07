<?php

class filterCombo extends combo 
{
	private $field;
	
	public $selectSize;
	

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		$this->setSelectSize($this->selectSize);
		
		$this->setRowType("filter");
		$this->setVisible(false);
	}
	
	public function setField($field)
	{
		$this->field = $field;
	}
	
	public function setSelectSize($selectSize)
	{
		$this->selectSize = $selectSize;
	}
	
	public function generateSQL()
	{
		$sql = "";
		
		$value = $this->getValue();
		
		if (strlen($value) > 0)
		{
			$exploded = explode("||", $value);
			$sql = $this->field . " IN ('" . implode("','", $exploded) . "')";
			page::addDebug("$sql", __FILE__, __LINE__);
		}
		
		return $sql;
	}
}

?>