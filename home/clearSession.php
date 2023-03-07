<?php
/**
 * This class allows the admin to delete all the session data.  
 * 
 * @package home
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 18/05/2006
 */
class clearSession extends page 
{			
	function __construct()
	{
		$_SESSION = array();
		
		if (isset($_SERVER['HTTP_REFERER']))
		{
			//goes back to the page the user was on before
			$this->redirect($_SERVER['HTTP_REFERER']);
		}
		else 
		{
			//or goes back to the home page
			$this->redirect("/home/");
		}
	}
}
		
?>