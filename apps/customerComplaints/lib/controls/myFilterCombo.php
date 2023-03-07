<?php

class myFilterCombo extends combo 
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
			$values = array();
			$nullValue = false;
			foreach( $exploded as $value )
			{
				if( $value != "NULL" )
				{
					$values[] = $value;
				}
				else
				{
					$nullValue = true;
				}
			}
			$sql = $this->field . " IN ('" . implode("','", $values) . "')";
			if( $nullValue )
			{
				$sql .= " OR " . $this->field . " IS NULL ";
			}
			page::addDebug("$sql", __FILE__, __LINE__);
		}
		
		return $sql;
	}
}

?>