<?php

class debug extends snapin 
{	
	function __construct()
	{
		$this->setName("SQL Debug");
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<debug>";
				
		$start = page::getTime();
        $this->xml .= "<SQLDebug>" . page::formatAsParagraphs(page::xmlentities($GLOBALS['sql_debug']),"\n") . "</SQLDebug>";

		$this->xml .= "</debug>";
		
		return $this->xml;
	}
}

?>