<?php

class sapcache
{
	private $sapNos = array();
	
	
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
	
	public function add($sap)
	{
		$this->sapNos[$sap->getId()] = $sap;
	}
	
	public function get($id)
	{
		$id = strtolower($id);
		
		if (!isset($this->sapNos[$id]))
		{
			$sap = new sapClass();
			$sap->load($id);
			$this->add($sap);
			page::addDebug("$id is not cached, adding", __FILE__, __LINE__);
		}
		else 
		{
			page::addDebug("$id is cached", __FILE__, __LINE__);
		}
		
		return $this->sapNos[$id];
	}
}