<?php

/**
 *
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */

// Adds the image details to the database.

class changeFileName extends page
{
	
	function __construct()
	{
		parent::__construct();

		$imageFileName = array();
		$uploadedDateTime = array();
		$fileName = array();
		$extension = array();
		$comments = array();
		
		//Change the below variables
		die("Change the details in the file!");
		// Id of the gallery.
		$galleryId = 0;
		// Folder name where the images reside in the tmep folder
		$tempFolder = "";
		
		
		//this add all the file in the directory into the $imageFileName array
		$count=0;	
		$dirPath = 'apps/gallery/images/temp/' . $tempFolder . '/';
		if ($handle = opendir($dirPath))
		{
		   while (false !== ($file = readdir($handle))) 
		   {
		      if ($file != "." && $file != ".." && $file != "Thumbs.db") 
		      {
		         if (is_dir("$dirPath/$file")) 
		         {
		            // do nothing
		         }
		         else
		         {
		            $imageFileName[$count] = $file;
		            $count++;
		         }
		      }
		   }
		  closedir($handle);
		}		      
		sort($imageFileName);
		// File handling to here!

		
		echo "<br /><br />";
		
		for($i=0;$i<$count;$i++)
		{
			$extension[$i] =  substr(strchr(strtolower($imageFileName[$i]), "."), 1);
			$fileName[$i] = substr(strtolower($imageFileName[$i]),  0, -strlen($extension[$i])-1);
		   	
			
			$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $galleryId . " AND fileName = '" . $fileName[$i] . "' ORDER BY uploadedDateTime, fileName ASC");
			$field = mysql_fetch_array($dataset);
			
			//echo "<br />" . $imageFileName[$i] . " becomes " . $field['id'] . "." . $extension[$i];
			
			if ($handle = opendir($dirPath))
			{
			   while (false !== ($file = readdir($handle))) 
			   {
			      if($file == $imageFileName[$i]) 
			      {
			         if (is_dir("$dirPath/$file")) 
			         {
			            // do nothing
			         }
			         else
			         {
			         	echo "<br />";
			            echo $file;
			            echo "<br />";
			            echo $imageFileName[$i] . " becomes " . $field['id'] . "." . $extension[$i];
			            echo "<br />";
			            // rename the file in here!
			            
			            
			            
			            // rename the files
			            rename($dirPath . $file, $dirPath . $field['id'] . "." . $extension[$i]);
			            
			            
			            
			         }
			      }
			   }
			  closedir($handle);
			}		      
			
		}
		 
		   
		   
		   
		
	}

}

?>