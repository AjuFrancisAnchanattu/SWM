<?php

class usercache
{
	private $users = array();
	
	
	public static function getInstance()
	{
		static $instance;
		
		if (!isset($instance))
		{
            $c = __CLASS__;

            $instance = new $c;
        }

        return $instance;
	}
	
	public function add($user)
	{
		$this->users[$user->getNTLogon()] = $user;
	}
	
	public function get($NTLogon)
	{
		$NTLogon = strtolower($NTLogon);
		
		if (!isset($this->users[$NTLogon]))
		{
			$user = new user();
			$user->load($NTLogon);
			$this->add($user);
			page::addDebug("$NTLogon is not cached, adding", __FILE__, __LINE__);
		}
		else 
		{
			page::addDebug("$NTLogon is cached", __FILE__, __LINE__);
		}
		
		return $this->users[$NTLogon];
	}
}
