<?php
class ijfProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $ijf;
	protected $ijfId = 0;
	
	function __construct($ijf)
	{
		$this->ijf = $ijf;
	}
	
	public function getIJF()
	{
		return $this->ijf;
	}
	
	public function setIjfId($id)
	{
		$this->ijfId = $id;
	}
	
	public function getIjfId()
	{
		return $this->ijfId;
	}
	
}