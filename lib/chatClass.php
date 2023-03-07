<?php

// This is for Instant Messaging

class chatClass
{
	protected $id;
	protected $name;
	protected $city;
	protected $language;
	protected $email;
	
	protected $loaded = false;
	
	
	public function load($id)
	{		
		$dataset = mysql::getInstance()->selectDatabase("chat")->Execute("SELECT * FROM chat WHERE id = '" . addslashes($id) . "'");

		$this->valid = (mysql_num_rows($dataset) == 0) ? false : true;
		
		page::addDebug("Loading Chat Message " . $id . ($this->valid ? 'true' : 'false'), __FILE__, __LINE__);

		if ($fields = mysql_fetch_array($dataset)) 
		{
			$this->id = $fields['id'];
	        $this->name = $fields['name'];
	        $this->city = $fields['city'];
	        $this->language = $fields['language'];
	        $this->email = $fields['emailAddress'];
		}
	}
	
	public function save()
	{
		
	}	
	        
	public function delete()
	{
		
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function isEnabled()
	{
		return $this->enabled;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getLanguage()
	{
		return $this->language;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
		
}

?>