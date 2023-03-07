<?php

class currentuser extends user
{
	private $snapins = array();
	//private $snapinPositions = array();
	
	//private $loadedSnapins = array();		//stores the users snapins, ordered according to the page.
	
	private $permissions = array();
	//private $isDefaultSnapins = false;
	
	
	public static function getInstance()
	{
		static $instance;
		
		if (!isset($instance))
		{
            $c = __CLASS__;

            $instance = new $c;
            if (!isset($_SESSION['impersonate']))
            {
	        	$instance->load($instance->getRemoteUser());
            }
            else
            {
            	$instance->load(strtolower($_SESSION['impersonate']));
            }
            
            if ($instance->isValid() && $instance->isEnabled())
            {
		        $instance->loadSnapins();
		        $instance->loadPermissions();
		        $instance->loadDashboardSnapins(); // Added 23/07/2009
            }
        }

        return $instance;
	}
	
	
	public function loadSnapins()
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM snapins WHERE ntlogon='" . addslashes($this->NTLogon) . "' ORDER BY area, pos");	
				
		$position = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			if (!isset($this->snapins[$fields['area']]))
			{
				$this->snapins[$fields['area']] = array();
				//$this->snapinPositions[$fields['area']] = array();
			}
			
			$this->snapins[$fields['area']][$fields['name']] = $position;
			//$this->snapinPositions[$fields['area']][$fields['name']] = $position;
			
			page::addDebug("Load ". $fields['name'], __FILE__, __LINE__);
			
			$position++;
		}
	}
	
	public function loadDashboardSnapins() // Added 24/07/2009
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM dashboardSnapins WHERE ntlogon='" . addslashes($this->NTLogon) . "' ORDER BY area, pos");
				
		$position = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			if (!isset($this->snapins[$fields['area']]))
			{
				$this->snapins[$fields['area']] = array();
				//$this->snapinPositions[$fields['area']] = array();
			}
			
			$this->snapins[$fields['area']][$fields['name']] = $position;
			//$this->snapinPositions[$fields['area']][$fields['name']] = $position;
			
			page::addDebug("Load ". $fields['name'], __FILE__, __LINE__);
			
			$position++;
		}
	}
	
	public function isDefaultSnapins()
	{
		return $this->isDefaultSnapins;
	}
	
	
	public function getSnapins($area)
	{
		if (isset($this->snapins[$area]))
		{
			return $this->snapins[$area];
		}
		else 
		{
			return array();
		}
	}
	
	
	public function restoreDefaultSnapins()
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE (NTLogon='" . addslashes($this->NTLogon) . "')");
	}
	
	public function restoreDefaultDashboardSnapins()
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM dashboardSnapins WHERE (NTLogon='" . addslashes($this->NTLogon) . "')");
	}
	
	public function loadPermissions()
	{
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT permission FROM permissions WHERE NTLogon='" . addslashes($this->NTLogon) . "'");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			array_push($this->permissions, $fields['permission']);
		}
	}
	
	public function hasPermission($permission)
	{
		return in_array($permission, $this->permissions);
	}
	
	public function isAdmin()
	{
		return $this->hasPermission('admin');
	}
	
	public function getIP()
    {
    	return $_SERVER['REMOTE_ADDR'];
    }
	
	
	/*
	 *	Ben Pearson 06/09/2005
	 */
    public function getRemoteUser()
    {
    	/*$userNameArray = split("\\\\",$_SERVER['REMOTE_USER']);		//have to double escape the backslash key
    	$userName = strtolower($userNameArray[1]);
    	return str_replace("'","----",$userName);*/
    	
    	return strtolower($_COOKIE['ntlogon']);
    	
    	//return 'deltis';
    }
}	