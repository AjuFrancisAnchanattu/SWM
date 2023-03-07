<?php

class session
{
	public static function clear()
	{
		$_SESSION['apps'][$GLOBALS['app']] = array();	
		$_SESSION['snapins'] = array();
	}
}

?>