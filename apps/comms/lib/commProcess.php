<?php
class commsProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $comm;
	protected $commId = 0;
	
	function __construct($comm)
	{
		$this->comm = $comm;
	}
	
	public function getcomm()
	{
		return $this->comm;
	}
	
	public function setcommId($id)
	{
		$this->commId = $id;
	}
	
	public function getcommId()
	{
		return $this->commId;
	}
	
}