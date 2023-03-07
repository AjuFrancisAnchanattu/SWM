<?php

class column
{
	protected $name;
	protected $displayName;
	protected $value;
	protected $table;
	
	function __construct($name)
	{
		$this->name = $name;
	}
	
	public function setDisplayName($value)
	{
		$this->displayName = $value;
	}
	
	public function getDisplayName()
	{
		return $this->displayName;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	public function getName()
	{
		return $this->name;
	}
	public function setTable($table)
	{
		$this->table = $table;
	}
	public function getTable()
	{
		return $this->table;
	}
}

?>