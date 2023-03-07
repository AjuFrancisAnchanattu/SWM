<?php

function errorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
   $errortype = array (
		E_ERROR              => 'Error',
		E_WARNING            => 'Warning',
		E_PARSE              => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR      => 'Compile Error',
		E_COMPILE_WARNING    => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE        => 'User Notice',
		E_STRICT            => 'Runtime Notice'
		//E_RECOVERABLE_ERRROR => 'Catchable Fatal Error'
	);

	if (!strstr($filename, "C:\php\pear\"))
	{
		page::addError($errortype[$errno], htmlentities($errmsg), $filename, $linenum);

		page::addDebug(htmlentities($errmsg), $filename, $linenum);

		if (!currentuser::getInstance()->isAdmin())
		{
			//page::error($errortype[$errno] . ": " . htmlentities($errmsg), $filename, $linenum);
		}
	}
}


?>