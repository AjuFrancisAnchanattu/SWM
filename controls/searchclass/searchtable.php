<?php

class searchtable
{
	private $name;
	
	
	private $filter = array();
	
	
	
	function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}		
	
	
	
	public function add($filter)
	{
		$this->filter[$filter->getName()] = $filter;
	}
	
	
	public function get($filter)
	{
		if (isset($this->filter[$filter]))
		{
			return $this->filter[$filter];
		}
		else 
		{
			return null;
		}
	}
	
	
	public function getAllFilters()
	{
		return $this->filter;
	}
	
	
	public function getFilterNames()
	{
		$filter = array();
		
		foreach($this->filter as $key => $value)
		{
			$filter[] = $key;
		}
		
		return $filter;
	}	
}