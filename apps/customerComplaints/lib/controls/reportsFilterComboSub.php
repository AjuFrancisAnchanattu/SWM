<?php

class reportsFilterComboSub extends combo 
{
	private $field;
	

	function __construct($name)
	{
		parent::__construct($name);

		// fallback in case setField is not called manually
		$this->setField($name);
		
		$this->setRowType("filter");
		$this->setVisible(false);
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
			
			foreach ($exploded as $key => $val)
			{
	    		$sql .= $this->field . " IN ('$val') OR ";
			}
			
			$sql = substr($sql, 0, -3);
			page::addDebug("$sql", __FILE__, __LINE__);
		}
		
		return $sql;
	}
}

?>