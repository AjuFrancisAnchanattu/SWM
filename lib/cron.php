<?php

// This is essentially a stripped down version of page that is extended for command line scripts

class cron extends common
{
	function __construct()
	{				
		$split = explode("/", substr($GLOBALS['path'] . $GLOBALS['file'], 1));
		
		$root = "/";
		
		for ($i=0; $i < count($split) - 1; $i++)
		{
			$root .= $split[$i] . "/";
		}

		if (file_exists($root . "config/db.php"))
		{
			require $root . "config/db.php";
		}
		else if (file_exists($root . "../config/db.php"))
		{
			require $root . "../config/db.php";
		}
		else 
		{
			require $GLOBALS['path'] . "/config/db.php"; 
		}
	}
}

?>