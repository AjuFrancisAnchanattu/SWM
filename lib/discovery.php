<?php

class discovery
{
	protected $description;
	protected $sysDomain;
	protected $sysMachine;
	protected $sysUsername;
	protected $macAddress;
	protected $ipAddress1;
	protected $lastContact;
	protected $sysSerialNo;
	protected $memoryKb;
	protected $diskSpaceMb;
	protected $diskFreeMb;
	
	protected $loaded = false;
	
	
	public function load($NTLogon)
	{
		// make safe input
		
		$stripped = stripslashes($NTLogon);
		
		$corrected = str_replace("&#39;", "'", strtolower($stripped));
		$corrected = str_replace("'", "", $corrected);
		
		$this->sysUsername = strtolower($corrected);
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM discovery WHERE sysUsername = '" . addslashes($this->sysUsername) . "'");
		
		// if not in the membership database, discovery isn't valid
		$this->valid = (mysql_num_rows($dataset) == 0) ? false : true;
		
		page::addDebug("Loading discovery " . $this->sysUsername . ($this->valid ? 'true' : 'false'), __FILE__, __LINE__);
			
		if($fields = mysql_fetch_array($dataset))
		{
			$this->description = $fields['description'];
			$this->sysDomain = $fields['sysDomain'];
			$this->sysMachine = $fields['sysMachine'];
			$this->sysUsername = $fields['sysUsername'];
			$this->macAddress = $fields['macAddress'];
			$this->ipAddress1 = $fields['ipAddress1'];
			$this->lastContact = $fields['lastContact'];
			$this->sysSerialNo = $fields['sysSerialNo'];
			$this->memoryKb = $fields['MemoryKb'];
			$this->diskSpaceMb = $fields['DiskSpaceMb'];
			$this->diskFreeMb = $fields['DiskFreeMb'];
		}
	}
	
	public function save()
	{
		
	}	
	        
	public function delete()
	{
		
	}
	
	public function getSysDescription()
	{
		return ($this->description) == '' ? 'Not Found In Centennial' : $this->description;
	}
	
	public function getSysDomain()
	{
		return ($this->sysDomain) == '' ? 'Not Found In Centennial' : $this->sysDomain;
	}
	
	public function getSysMachine()
	{
		return ($this->sysMachine) == '' ? 'Not Found In Centennial' : $this->sysMachine;
	}
	
	public function getSysUsername()
	{
		return $this->sysUsername;
	}
	
	public function getMacAddress()
	{
		return ($this->macAddress) == '' ? 'Not Found In Centennial' : $this->macAddress;
	}
	
	public function getIPAddress()
	{
		return ($this->ipAddress1) == '' ? 'Not Found In Centennial' : $this->ipAddress1;
	}
	
	public function getLastContact()
	{
		return ($this->lastContact) == '' ? 'Not Found In Centennial' : common::transformDateTimeForPHP($this->lastContact);
	}
	
	public function getSysSerialNo()
	{
		return ($this->sysSerialNo) == '' ? 'Not Found In Centennial' : $this->sysSerialNo;
	}
	
	public function getMemory()
	{
		return ($this->memoryKb) == '' ? 'Not Found In Centennial' : $this->memoryKb = $this->memoryKb / 1024 . ' MB';
	}
	
	public function getDiskSize()
	{
		return ($this->diskSpaceMb) == '' ? 'Not Found In Centennial' : $this->diskSpaceMb . ' MB';
	}
	
	public function getDiskFreeSpace()
	{
		return ($this->diskFreeMb) == '' ? 'Not Found In Centennial' : $this->diskFreeMb . ' MB';
	}
	
}

?>