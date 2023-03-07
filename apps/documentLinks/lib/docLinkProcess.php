<?php
class docLinkProcess
{	
	
	protected $id;
	protected $loadedFromDatabase;
	protected $docLink;
	protected $docLinkId = 0;
	
	function __construct($docLink)
	{
		$this->docLink = $docLink;
	}
	
	public function getdocLink()
	{
		return $this->docLink;
	}
	
	public function setdocLinkId($id)
	{
		$this->docLinkId = $id;
	}
	
	public function getdocLinkId()
	{
		return $this->docLinkId;
	}
	
}