<?php

class opportunityload extends snapin 
{	
	function __construct($area)
	{
		$this->setName(translate::getInstance()->translate("Load Opportunity"));
		$this->setClass(__CLASS__);
		$this->setArea($area);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<opportunityload>";
		
		$this->xml .= "</opportunityload>";
		
		return $this->xml;
	}
}

?>