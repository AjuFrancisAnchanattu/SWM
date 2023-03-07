<?php

class discoverycache
{
	private $discoverys = array();
	
	
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
	
	public function add($discovery)
	{
		$this->discoverys[$discovery->getSysUsername()] = $discovery;
	}
	
	public function get($NTLogon)
	{
		$NTLogon = strtolower($NTLogon);
		
		if (!isset($this->discoverys[$NTLogon]))
		{
			$discovery = new discovery();
			$discovery->load($NTLogon);
			$this->add($discovery);
			page::addDebug("$NTLogon is not cached, adding", __FILE__, __LINE__);
		}
		else 
		{
			page::addDebug("$NTLogon is cached", __FILE__, __LINE__);
		}
		
		return $this->discoverys[$NTLogon];
	}
}
