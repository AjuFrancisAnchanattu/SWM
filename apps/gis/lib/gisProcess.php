<?php

class gisProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $gis;
	protected $gisId = 0;
	
	function __construct($gis)
	{
		$this->gis = $gis;
	}
	
	public function getGis()
	{
		return $this->gis;
	}
	
	public function setGisId($id)
	{
		$this->gisId = $id;
	}
	
	public function getGisId()
	{
		return $this->gisId;
	}
	
}