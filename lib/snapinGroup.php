<?php

class snapinGroup
{
	private $name;
	
	private $snapins = array();
	//private $all = array();
	private $used = array();
	
	function __construct($name)
	{
		$this->name = $name;
	}
	
	/**
	 * Register a snapin with the group
	 *
	 * @param string $area
	 * @param string $name
	 * @param boolean $showByDefault	show by default
	 * @param boolean $force	optional. force snapin is shown and can't be deleted
	 */
	
	public function register($fileLocation, $name, $showByDefault, $force=false)
	{
		if ($fileLocation == "global")
		{
			$file = page::getRoot() . "/snapins/" . $name . "/" . $name . ".php";
		}
		else 
		{
			$file = page::getRoot() . "/" . $fileLocation . "/snapins/" . $name . "/" . $name . ".php";
		}
		
		if (file_exists($file))
		{
			require_once($file);
			
			$_snapin = new $name();
			$_snapin->setArea($this->name);
			
			if ($force)
			{
				$_snapin->setCanClose(false);
			}
			
			$_snapin->setDefaultView($showByDefault);
			
			
			
			
			//$userSnapins = currentuser::getInstance()->getSnapins($this->name);
			
			//if ()
			//$_snapin->setPosition($userSnapins[$name]);
			
			
			// store snapin object, we may not use it
			$this->snapins[$name] = $_snapin;
			
    		if ($_snapin->hasPermission() && ($force == true || $_snapin->hasPreference($showByDefault)))
    		{
    			// register the snapin as active
    			$this->used[] = $name;
    			
    			page::addDebug("Register $name", __FILE__, __LINE__);
    		}
    		else 
    		{
    			page::addDebug("Don't show $name", __FILE__, __LINE__);
    		}
    		//$this->all[] = $name;
		}
		else 
		{
			page::addDebug("Snapin $file does not exist", __FILE__, __LINE__);
		}
	}
	
	public function get($name)
	{
		return $this->snapins[$name];
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getAll()
	{
		return array_keys($this->snapins);
	}
	
	public function getRegistered()
	{
		$registered = array();
		
		for ($i=0; $i < count($this->used); $i++)
		{
			$registered[$this->used[$i]] = $this->snapins[$this->used[$i]];
		}
		
		return $registered;
	}

		
	public function getOutput()
	{
		$output = "";
		
		$userSnapins = currentuser::getInstance()->getSnapins($this->name);
		
		$_SESSION['snapins'][$this->name] = array();
		
		// no user prefs, go in register order
		if (count($userSnapins) == 0)
		{
			for ($i=0; $i < count($this->used); $i++)
			{
				$output .= $this->snapins[$this->used[$i]]->getOutput();
				
				
				page::addDebug("Default " . $this->used[$i], __FILE__, __LINE__);
				
				// add to session for manipulation purposes
				$_SESSION['snapins'][$this->name][] = $this->used[$i];
			}
		}
		// go in order of database. bodgetastic style.
		else 
		{			
			foreach ($userSnapins as $key => $value)
			{
				$output .= $this->snapins[$key]->getOutput();
				
				page::addDebug("DB " . $key, __FILE__, __LINE__);
				
				// add to session for manipulation purposes
				$_SESSION['snapins'][$this->name][] = $key;
			}		
			
		}
			
		return $output;
	}
}

?>