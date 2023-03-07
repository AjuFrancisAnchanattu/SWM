<?php

class docmanProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $docman;
	
	function __construct($docman)
	{
		$this->docman = $docman;
	}
	
	public function getDocMan()
	{
		return $this->docman;
	}
	
	public function setdocmanId($id)
	{
		$this->docman = $id;
	}
	
	public function getdocmanId()
	{
		return $this->docmanId;
	}
	
}