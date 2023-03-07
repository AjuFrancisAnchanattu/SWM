<?php

/**
 * bla bla bla bla
 * 
 * @package home
 * 
 */


$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$GLOBALS['starttime'] = $mtime; 

/**
 * Home Page
 * 
 * Boring stuff happens here
 * 
 * @package home
 */
class index extends page 
{	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('home');
		
		//print_r($_SERVER);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML('./home/menu.xml');
		$this->header->setNotice("Scapa development Intranet system");
		
		
		$this->add_output("<home>");
		
		

		$this->add_output("</home>");
		
		
		$this->output();

	}
}

?>