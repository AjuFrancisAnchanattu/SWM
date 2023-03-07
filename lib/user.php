<?php

class user
{
	protected $firstName;
	protected $lastName;
	protected $NTLogon;
	protected $email;
	protected $language;
	protected $site;
	protected $frenchSite;
	protected $department;
	protected $photo;
	protected $locale;
	protected $fax;
	protected $phone;
	protected $valid;
	protected $region;
	protected $enabled;
	protected $systemAdministrator;
	protected $role;
	protected $mobile;
	protected $ceo;
	protected $ipAddress;
	
	protected $loaded = false;
	
	
	public function load($NTLogon)
	{
		// make safe input
		
		$stripped = stripslashes($NTLogon);
		
		$corrected = str_replace("&#39;", "'", strtolower($stripped));
		$corrected = str_replace("'", "", $corrected);
		
		$this->NTLogon = strtolower($NTLogon);
		
		
		
		//$this->NTLogon = $NTLogon;
		
		
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM employee WHERE NTLogon='" . addslashes($corrected) . "' AND enabled = 1");
		
		// if not in the membership database, user isn't valid
		$this->valid = (mysql_num_rows($dataset) == 0) ? false : true;
		
		page::addDebug("Loading user " . $this->NTLogon . ($this->valid ? 'true' : 'false'), __FILE__, __LINE__);
			
		

		if ($fields = mysql_fetch_array($dataset)) 
		{
			$this->enabled = $fields['enabled'];
	        $this->firstName = $fields['firstName'];
	        $this->lastName = $fields['lastName'];
	        $this->email = $fields['email'];
	        $this->language = $fields['language'];
	        $this->site = $fields['site'];
	        $this->frenchSite = $fields['frenchSite'];
	        $this->locale = $fields['locale'];
	        $this->phone = $fields['phone'];
	        $this->region = $fields['region'];
	        $this->fax = $fields['fax'];
	        $this->department = $fields['department'];
	        $this->photo = $fields['photo'];
	        $this->systemAdministrator = $fields['systemAdministrator'];
	        $this->role = $fields['role'];
	        $this->mobile = $fields['mobile'];
	        $this->ceo = $fields['ceo'];
	        $this->ipAddress = $fields['lastIP'];
		}
	}
	
	public function save()
	{
		
	}	
	        
	public function delete()
	{
		
	}
	
	public static function getNTLoginFromName($name)
	{
		/*static $trans;

		if (!isset($trans))
		{
			$trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
			foreach ($trans as $key => $value)
			{
				$trans[$key] = '&#'.ord($key).';';
			}
			
			// dont translate the '&' in case it is part of &xxx;
			$trans[chr(38)] = '&';
		}

		// after the initial translation, _do_ map standalone '&' into '&#38;'
		$coverted = preg_replace("/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,5};)/","&#38;" , strtr($name, $trans));
*/
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM employee WHERE CONCAT(firstName,' ',lastName) = '" . addslashes($name) . "'");

		if ($fields = mysql_fetch_array($dataset)) 
		{
			return strtolower($fields['NTLogon']);
		}
		
		return null;
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function isEnabled()
	{
		return $this->enabled;
	}

	// fun get/set stuff here
	
	public function getFirstName()
	{
		return $this->firstName;
	}
	
	public function getLastName()
	{
		return $this->lastName;
	}
	
	public function getName()
	{
		if ($this->valid)
		{
			//page::addDebug($this->NTLogon . " (" . $this->firstName . ' ' . $this->lastName . ") is  a valid user... apparently", __FILE__, __LINE__);
			return $this->firstName . ' ' . $this->lastName;
		}
		else 
		{
			//page::addDebug($this->NTLogon . " is NOT a valid user... apparently", __FILE__, __LINE__);
			return $this->NTLogon;
		}
	}
	
	public function getNTLogon()
	{
		return $this->NTLogon;
	}
	
	public function getLanguage()
	{
		return $this->language;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getPhone()
	{
		return $this->phone;
	}
	
	public function getFax()
	{
		return $this->fax;
	}
	
	public function getLocale()
	{
		return $this->locale;
	}
	
	public function getIsUSA()
	{
		if($this->locale == "USA" || $this->locale == "CANADA")
		{
			return $this->locale;
		}
		else 
		{
			return "";
		}
	}
	
	public function getIsAsia()
	{
		if($this->locale == "MALAYSIA" || $this->locale == "CHINA" || $this->locale == "KOREA")
		{
			return $this->locale;
		}
		else 
		{
			return "";
		}
	}
	
	public function getIsEnglish()
	{
		if($this->language == "ENGLISH")
		{
			return $this->language;
		}
		else 
		{
			return "";
		}
	}
	
	public function getIsSystemAdmin()
	{
		if($this->systemAdministrator == 1)
		{
			return $this->systemAdministrator;
		}
		else 
		{
			return "";
		}
	}
	
	public function getIsCEO()
	{
		if($this->ceo == 1)
		{
			return $this->ceo;
		}
		else 
		{
			return "";
		}
	}
	
	public function getSite()
	{
		return $this->site;
	}
	
	public function getFrenchSite()
	{
		return $this->frenchSite;
	}
	
	public function getDepartment()
	{
		return $this->department;
	}
	
	public function getPhoto()
	{
		return $this->photo;
	}
	
	public function getRegion()
	{
		return $this->region;
	}
	
	public function getRole()
	{
		return $this->role;
	}
	
	public function getMobile()
	{
		return $this->mobile;
	}
	
	public function getIPAddress()
	{
		return $this->ipAddress;
	}
}

?>