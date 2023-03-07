<?php
class helpAppProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $helpApp;
	protected $helpAppId = 0;
	
	function __construct($helpApp)
	{
		$this->helpApp = $helpApp;
	}
	
	public function gethelpApp()
	{
		return $this->helpApp;
	}
	
	public function sethelpAppId($id)
	{
		$this->helpAppId = $id;
	}
	
	public function gethelpAppId()
	{
		return $this->helpAppId;
	}
	
}