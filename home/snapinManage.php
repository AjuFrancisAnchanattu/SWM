<?php

class snapinManage extends page 
{
	function __construct()
	{
		parent::__construct();
		
		$this->setDebug(true);
		
		/**
		 * Dashboard Manage ELSE Homepage Manage
		 */
		if(isset($_REQUEST['area']) && ($_REQUEST['area'] == "dashboardLeft" || $_REQUEST['area'] == "dashboardMiddle" || $_REQUEST['area'] == "dashboardRight"))
		{
			if(isset($_REQUEST['delete']) && isset($_REQUEST['area']))
			{
				// delete what's in there
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM dashboardSnapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area = '" . $_REQUEST['area'] . "'");
				
				$delete = false;
				
				for($i=0; $i < count($_SESSION['snapins'][$_REQUEST['area']]); $i++)
				{
					if($_REQUEST['delete'] == $_SESSION['snapins'][$_REQUEST['area']][$i])
					{
						$delete = $i;
					}
					else 
					{
						mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO dashboardSnapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $_SESSION['snapins'][$_REQUEST['area']][$i] . "', $i, '" . $_REQUEST['area'] . "')");
					}
				}
				
				if($delete)
				{
					unset($_SESSION['snapins'][$_REQUEST['area']][$delete]);
				}
			}
						
			if(isset($_REQUEST['add']) && isset($_REQUEST['area']))
			{
				// delete what's in there
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM dashboardSnapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area = '" . $_REQUEST['area'] . "'");
		
				$_SESSION['snapins'][$_REQUEST['area']][] = $_REQUEST['add'];
				
				for($i=0; $i < count($_SESSION['snapins'][$_REQUEST['area']]); $i++)
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO dashboardSnapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $_SESSION['snapins'][$_REQUEST['area']][$i] . "', $i, '" . $_REQUEST['area'] . "')");
				}
			}
			
			if(isset($_SERVER['HTTP_REFERER']))
			{
				//goes back to the page the user was on before
				$this->redirect($_SERVER['HTTP_REFERER']);
			}
			else 
			{
				//or goes back to the home page
				$this->redirect("/home/dashboard?");
			}
		}
		else 
		{
			if(isset($_REQUEST['delete']) && isset($_REQUEST['area']))
			{
				// delete what's in there
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area = '" . $_REQUEST['area'] . "'");
				
				$delete = false;
				
				for($i=0; $i < count($_SESSION['snapins'][$_REQUEST['area']]); $i++)
				{
					if($_REQUEST['delete'] == $_SESSION['snapins'][$_REQUEST['area']][$i])
					{
						$delete = $i;
					}
					else 
					{
						mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $_SESSION['snapins'][$_REQUEST['area']][$i] . "', $i, '" . $_REQUEST['area'] . "')");
					}
				}
				
				if($delete)
				{
					unset($_SESSION['snapins'][$_REQUEST['area']][$delete]);
				}
			}
			
			
			if(isset($_REQUEST['add']) && isset($_REQUEST['area']))
			{
				// delete what's in there
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area = '" . $_REQUEST['area'] . "'");
		
				$_SESSION['snapins'][$_REQUEST['area']][] = $_REQUEST['add'];
				
				for($i=0; $i < count($_SESSION['snapins'][$_REQUEST['area']]); $i++)
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon, name, pos, area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $_SESSION['snapins'][$_REQUEST['area']][$i] . "', $i, '" . $_REQUEST['area'] . "')");
				}
			}
			
			if(isset($_REQUEST['restoreDefault']))
			{
				$areas = explode(",", $_REQUEST['restoreDefault']);	
				
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE NTLogon='" . currentuser::getInstance()->getNTLogon() . "' AND area IN ('" . implode("','", $areas) . "')");
			}
			
			if(isset($_SERVER['HTTP_REFERER']))
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
}

?>