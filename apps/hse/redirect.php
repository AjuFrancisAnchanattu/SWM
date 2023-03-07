<?php

class redirect extends page
{
	function __construct()
	{
		if(isset($_GET['target_url1']) && isset($_GET['target_url1']) && isset($_GET['target_url3']))
		{
			page::redirect("http://ukdunapp017/login_check_wip.php?user=" . md5(currentuser::getInstance()->getNTLogon()) . "&target_url1=" . $_GET['target_url1'] . "&target_url2=" . $_GET['target_url2'] . "&target_url3=" . $_GET['target_url3']);
		}
		else
		{
			page::redirect("http://ukdunapp017/login_check_wip.php?user=" . md5(currentuser::getInstance()->getNTLogon()));
		}
	}
}


?>