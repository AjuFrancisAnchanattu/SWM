<?php

class group
{
	protected $name;
	
	
	protected $control = array();
	
	protected $visible = true;
	
	protected $showBorder = true;
	
	private $anchorRef = "";
	
	
	
	
	function __construct($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}		
	
	
	
	public function add($control)
	{
		$this->control[$control->getName()] = $control;
	}
	
	
	public function get($control)
	{
		if (isset($this->control[$control]))
		{
			return $this->control[$control];
		}
		else 
		{
			return null;
		}
	}
	
	
	public function getAllControls()
	{
		return $this->control;
	}
	
	
	public function getControlNames()
	{
		$controls = array();
		
		foreach($this->control as $key => $value)
		{
			$controls[] = $key;
		}
		
		return $controls;
	}
	
	
	public function setVisible($visible)
	{
		$this->visible = $visible;

		//foreach($this->control as $key => $value)
		//{
		//	$this->control[$key]->setGroupVisible($visible);
		//}
	}
	
	public function getVisible()
	{
		return $this->visible;
	}
	
	
	public function setBorder($showBorder)
	{
		$this->showBorder = $showBorder;
	}
	
	public function getBorder()
	{
		return $this->showBorder;
	}
	
	public function setAnchorRef($anchorRef)
	{
		$this->anchorRef = $anchorRef;
	}
	
	public function getAnchorRef()
	{
		return $this->anchorRef;
	}
	
}