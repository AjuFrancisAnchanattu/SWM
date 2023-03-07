<?php

class snapin
{
	protected $xml;
	
	protected $name;
	protected $area;
	protected $canClose = true;
	protected $defaultView = false;
	protected $class;
	protected $showHelp = false;	
	protected $colourScheme = "title-boxblue";
	
	protected $permissionsAllowed = array();
	protected $localeAllowed = array();
	protected $localeDisallowed = array();
	
	
	protected $position;
	
	
	public function setName($name)
	{
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setArea($area)
	{
		$this->area = $area;
	}
	
	public function getArea()
	{
		return $this->area;
	}
	
	
	public function setClass($class)
	{
		$this->class = $class;
	}
	
	public function getClass()
	{
		return $this->class;
	}
	
	
	public function setCanClose($canClose)
	{
		$this->canClose = $canClose;
	}
	
	public function getCanClose()
	{
		return $this->canClose;
	}
	
	public function setShowHelp($showHelp)
	{
		$this->showHelp = $showHelp;
	}
	
	public function getShowHelp()
	{
		return $this->showHelp;
	}
	
	/**
	 * Set the colour for the snapin title bar
	 * title-box2, title-box1, title-boxgrey, title-boxblue
	 *
	 * @param string $colourScheme (title-box2, title-box1, title-boxblue, title-boxgrey)
	 */
	public function setColourScheme($colourScheme)
	{		
		$this->colourScheme = $colourScheme;
	}
	
	public function getColourScheme()
	{		
		return $this->colourScheme;	
	}
	
	/**
	 * Set colour scheme of snapin top bar
	 *
	 * @param string $defaultView ("title-box1", "title-box2", etc)
	 */
	public function setDefaultView($defaultView)
	{
		$this->defaultView = $defaultView;
	}
	
	public function getDefaultView()
	{
		return $this->defaultView;
	}
	

	public function setPermissionsAllowed($permissionsAllowed)
	{
		$this->permissionsAllowed = $permissionsAllowed;
	}
	
	public function setLocaleAllowed($localeAllowed)
	{
		$this->localeAllowed = $localeAllowed;
	}
	
	public function setLocaleDisallowed($localeDisallowed)
	{
		$this->localeDisallowed = $localeDisallowed;
	}
	
	
	public function canView()
	{
		$allowed = true;
		
		if (count($this->permissionsAllowed) > 0)
		{
			$allowed = false;
			
			for($i=0; $i < count($this->permissionsAllowed); $i++)
    		{
    			if (currentuser::getinstance()->hasPermission($this->permissionsAllowed[$i]))
    			{
					$allowed = true;
				}
    		}
		}
		
		return $allowed;
	}
	
	public function hasPermission()
	{
		$allowed = true;
		
		if (count($this->permissionsAllowed) > 0)
		{
			$allowed = false;
			
			for($i=0; $i < count($this->permissionsAllowed); $i++)
    		{
    			if (currentuser::getinstance()->hasPermission($this->permissionsAllowed[$i]))
    			{
					$allowed = true;
				}
    		}
		}
		
		return $allowed;
		//return true;
	}
	
	

		
	public function getOutput()
	{
		if ($this->canView())
		{
			if (empty($this->name) || empty($this->class) ||  empty($this->area))
			{
				die("Name (".$this->name."), Class (".$this->class.") or Area (".$this->area.") not set in snapin");
			}
			
			if(isset($_REQUEST['dashboardLocation']))
			{
				$dashboardLocation = $_REQUEST['dashboardLocation'];
			}
			else 
			{
				$dashboardLocation = "default";
			}
			
			$output = '<snapin name="'. $this->getName() .'" canClose="'. ($this->getCanClose() ? 'true' : 'false') .'" showHelp="'. ($this->getShowHelp() ? 'true' : 'false') .'" class="' . $this->getClass() . '" area="' . $this->getArea() . '" dashboardLocation="' . $dashboardLocation . '" colourScheme="' . $this->getColourScheme() . '">';
			$output .= $this->output();
			$output .= '</snapin>';
			
			return $output;
		}
	}
	
	public function setPosition($position)
	{
		$this->position = $position;
	}
	
	public function hasPreference($showByDefault)
	{
		if (count(currentuser::getInstance()->getSnapins($this->getArea())) == 0)
		{
			page::addDebug($this->class . " has default pref of " . ($showByDefault ? 'true' : 'false') , __FILE__, __LINE__);
			return $showByDefault;
		}
		else 
		{
			page::addDebug($this->getArea() . " user has " . count(currentuser::getInstance()->getSnapins($this->getArea())), __FILE__, __LINE__);
			page::addDebug($this->class . " has user pref of " . (array_key_exists($this->class, currentuser::getInstance()->getSnapins($this->getArea())) ? 'true' : 'false') , __FILE__, __LINE__);
			return array_key_exists($this->class, currentuser::getInstance()->getSnapins($this->getArea()));
		}
	}
	
	
	
	
	/* Move snapin stuff */
	
	
	
	
	/*
	
	
	public function moveUp($snapinToMoveUpName, $snapinToMoveUpArea)
	{
		$usersSnapins = currentuser::getInstance()->getSnapins();

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE ntlogon='" . currentuser::getinstance()->getNTLogon() . "'");			
		foreach($usersSnapins as $usersSnapinsArea => $usersSnapinsNameArray)
		{
			for($pos=0;$pos<count($usersSnapinsNameArray);$pos++)
			{
				if ((($pos+1) < count($usersSnapinsNameArray)) && ($usersSnapinsNameArray[($pos+1)] == $snapinToMoveUpName) && ($usersSnapinsArea == $snapinToMoveUpArea))
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . ($pos+1) . "', '". $usersSnapinsArea . "')");	
				}
				elseif (($usersSnapinsNameArray[$pos] == $snapinToMoveUpName) && ($usersSnapinsArea == $snapinToMoveUpArea))
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . ($pos-1) . "', '". $usersSnapinsArea . "')");
				}
				else
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . $pos . "', '". $usersSnapinsArea . "')");	
				}
			}
		}
	}
	
	public function moveDown($snapinToMoveDownName,$snapinToMoveDownArea)
	{
		$usersSnapins = currentuser::getInstance()->getSnapins();

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM snapins WHERE ntlogon='" . currentuser::getinstance()->getNTLogon() . "'");			
		foreach($usersSnapins as $usersSnapinsArea => $usersSnapinsNameArray)
		{
			for($pos=0;$pos<count($usersSnapinsNameArray);$pos++)
			{
				if ((($pos-1) >= 0) && ($usersSnapinsNameArray[($pos-1)] == $snapinToMoveDownName) && ($usersSnapinsArea == $snapinToMoveDownArea))
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . ($pos-1) . "', '". $usersSnapinsArea . "')");	
				}
				elseif (($usersSnapinsNameArray[$pos] == $snapinToMoveDownName) && ($usersSnapinsArea == $snapinToMoveDownArea))
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . ($pos+1) . "', '". $usersSnapinsArea . "')");
				}
				else
				{
					mysql::getInstance()->selectDatabase("membership")->Execute("INSERT INTO snapins (NTLogon,name,pos,area) VALUES ('" . currentuser::getinstance()->getNTLogon() . "', '" . $usersSnapinsNameArray[$pos] . "', '" . $pos . "', '". $usersSnapinsArea . "')");	
				}
			}
		}
	}*/
	
}

?>