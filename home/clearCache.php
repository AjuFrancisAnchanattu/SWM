<?php
/**
 * This class allows the admin to delete all the cached files on the PHP server.  
 * 
 * @package home
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 08/02/2006
 */
class clearCache extends page 
{			
	function __construct()
	{
		$dir = "./cache/";
		
		//checks if the cache directory exists
		if (is_dir($dir)) 
		{
			$dirhandler = opendir($dir);
			
				//loops through each file in the directory
		    	while ($file = readdir($dirhandler))
		    	{
		    		if ($file != "." && $file != "..")
		    		{
		    			//deletes each file
		        		unlink($dir . $file);
		    		}
		        }
		        closedir($dirhandler);
		    
		}
		
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