#!/usr/bin/php -q
<?php

// This file is run as a cronjob from the command line and is accessed directly, so does not extend page

require 'sapimport.php';


/**
 * Report: CUSTCOMPLAIN2
 *
 * Field spec:
 * 0 - Customer
 * 1 - Name 1
 * 2 - Name 2
 * 3 - Street
 * 4 - City
 * 5 - Rg
 * 6 - Cty
 * 7 - PostalCode
 * 8 - SOrg.
 * 9 - Pers.no.
 * 10 - First name
 * 11 - Last name
 */

class sapcustomerdata extends sapimport 
{
	function __construct()
	{
		// check sapcustomerdata.dat exists
		
		
		
		$file = "/home/live/cron/sapcustomerdata.dat";
		
		if (!file_exists($file))
		{
			die ("Cannot find $file to import\n");
		}
		
		$data = file($file);
		
		reset($data);
		
		mysql::getInstance()->selectDatabase("SAP")->execute("DELETE FROM customer");
		
		echo "Removing current data...\n";
		
		echo "Importing new data...\n";

		// start at 1 as the first line *should* be columnn headers	  count($data)
		for($i=1; $i < count($data); $i++)
		{
			$columns = explode("\t", $data[$i]);
			
			
			mysql::getInstance()->selectDatabase("SAP")->execute(sprintf("INSERT INTO customer (`id`,`name`,`address`,`city`,`postcode`,`country`,`salesPerson`) VALUES ('%u','%s','%s','%s','%s','%s','%s')",
				substr($columns[0], 4), // remove 0000 from start of number
				addslashes($columns[1]),
				addslashes($columns[3]),
				addslashes($columns[4]),
				addslashes($columns[7]),
				addslashes($columns[6]),
				(strlen($columns[10] . ' ' . $columns[11]) > 1 ? addslashes(trim($columns[10] . ' ' . $columns[11])) : '')
			), false);
		}
		
		
		
		die ("Finished\n");
	}
	
}

new sapcustomerdata();

?>