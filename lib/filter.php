<?php


class filter extends item
{	
	private $type;
	private $filterRowTitle;
	private $enabled=false;
	protected $table;
		
	public function enable($choice)
	{
		$this->enabled = $choice;
	}
	
	public function isEnabled()
	{
		return $this->enabled;
	}
	
	public function setFilterRowTitle($rowTitle)
	{
		$this->filterRowTitle = $rowTitle;
	}
	
	public function getFilterRowTitle()
	{
		return $this->filterRowTitle;
	}
	
	public function getFilterRowTop()
	{
		return "<filterRow title=\"". $this->getFilterRowTitle() . "\" type=\"" . $this->getType() . "\" name=\"" . $this->getName() . "\">";
	}

	public function getFilterRowBottom()
	{
		return "</filterRow>";
	}
	
	public function setType($type)
	{
		$availableTypes = array(
			'equals',
			'between',
			'like',
		);
		
		if (in_array($type, $availableTypes))
		{	
			$this->type = $type;
		}
		else 
		{
			die("Filter Type not supported in /lib/filters.php");
		}
	}
	
	public function getType()
	{
		return $this->type;	
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