<?php

// set-up environemnt 
$GLOBALS['path'] = substr($_SERVER["SCRIPT_NAME"], 0, strlen($_SERVER["SCRIPT_NAME"]) - strlen("/lib/cronlauncher.php"));
$GLOBALS['isCommandLine'] = true;


// get the cron class
require ($GLOBALS['path'] . '/lib/common.php');
require ($GLOBALS['path'] . '/lib/cron.php');
//require ($GLOBALS['path'] . '/lib/page.php');

// execute script
if (isset($_SERVER["argv"][1]))
{
	$GLOBALS['file'] = $_SERVER["argv"][1];
	require ($GLOBALS['path'] . $GLOBALS['file']);
}
else 
{
	die("No script passed to run\n");
}

?>