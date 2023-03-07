<?php

class offlinetranslations extends page 
{
	function __construct()
	{
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM translations WHERE application='ccr' OR application='global' ORDER BY translateFrom ASC");
		
		echo "<pre>";
		
		while ($translation = mysql_fetch_array($dataset))
		{
			if (strlen($translation['french']) == 0 || strlen($translation['german']) == 0 || strlen($translation['italian']) == 0 || strlen($translation['spanish']) == 0)
			{
				// do nothing
			}
			else 
			{
				printf('globalTranslations["%s"] = Array("%s", "%s", "%s", "%s", "%s");', 
					$translation['translateFrom'],
					$this->fix($translation['english']),
					$this->fix($translation['french']),
					$this->fix($translation['german']),
					$this->fix($translation['italian']),
					$this->fix($translation['spanish'])
				);
				echo "\n";
			}
		}
		
		echo "</pre>";
	}
	
	function fix($text)
	{
		$value = trim($text);
		
		$value = page::xmlentities($value);
		
		//$value = htmlentities($value);
		
		$value = str_replace(array('&#60;', '&#62;', '&#39;', '&#34;'), array('<', '>', '', ''),  $value);
		
		//$value = str_replace("ó", '&oacute;', $value);
		
		return $value;
	}
}

?>