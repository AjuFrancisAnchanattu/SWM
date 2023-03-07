<?php

class moveSnapin extends page 
{
	function __construct()
	{
		parent::__construct();
		
		
		if (!isset($_POST['snapins']))
		{
			die();
		}
		
		// we need the area, check the first record
		if (!isset($_POST['snapins'][0]))
		{
			die();
		}
		
		$split = explode("|", $_POST['snapins'][0]);
		
		$area = $split[0];
		
		// check if user has default snapin layout, as they are a bit *special*
		/*if (currentuser::getInstance()->isDefaultSnapins())
		{
			// get all snapins from default layout that aren't in the area we've touched
			$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM snapins WHERE ntlogon='default' AND area != '" . $area . "'");
			
			while ($fields = mysql_fetch_array($dataset))	
			{
				mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $fields['name'] . "'," . $fields['pos'] . ", '" . $fields['area'] . "')");
			}
		}
		else 
		{
			// delete existing records for the area
			mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area='" . $area . "'");
		}*/
		
		mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area='" . $area . "'");
		
		
		// insert new ones. easy peasy
		for ($i=0;$i < count($_POST['snapins']); $i++)
		{
			$split = explode("|", $_POST['snapins'][$i]);
			$snapin = $split[1];
			
			mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $snapin . "'," . ($i+1) . ", '" . $area . "')");
		}
	}
}

?>
Done