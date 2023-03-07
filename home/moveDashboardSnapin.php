<?php

class moveDashboardSnapin extends page 
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
		
		mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM dashboardSnapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area='" . $area . "'");
		
		
		// insert new ones. easy peasy
		for ($i=0;$i < count($_POST['snapins']); $i++)
		{
			$split = explode("|", $_POST['snapins'][$i]);
			$snapin = $split[1];
			
			mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO dashboardSnapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $snapin . "'," . ($i+1) . ", '" . $area . "')");
		}
	}
}

?>
Done