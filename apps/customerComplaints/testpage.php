<?php

class testpage extends page
{
	private $logExceptions = array(4,41,43);

	function __construct()
	{
		echo currentuser::getInstance()->getNTLogon();
	}
	
}