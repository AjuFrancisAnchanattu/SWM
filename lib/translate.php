<?php

/*
 * Singleton
 */

class translate
{
	private $data = array();


	public static function getInstance()
	{
		static $instance;

		if (!isset($instance))
		{
            $c = __CLASS__;

            $instance = new $c;
            $instance->loadTranslations();
        }

        return $instance;
	}

	public function loadTranslations()
	{
		$language = $this->getUserLanguage();

		if(!$this->data = cache::getCache("translations-" . $GLOBALS['appName'] . "-" .$language))
		{
			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM translations WHERE application IN ('global', 'new', '" . $GLOBALS['appName'] . "')");

			while ($fields = mysql_fetch_array($dataset))
			{
				$value = $fields['english'];

				if (!$fields[$language] == '')
				{
					$value = $fields[$language];
				}

				if ($value != '')
				{
					///page::addDebug("key: " . $fields['translateFrom'] . " value: $value", __FILE__, __LINE__);


					$this->data[strtoupper($fields['translateFrom'])] = array(
						'id' => $fields['id'],
						'value' => page::xmlentities($value)
					);
				}
			}

			cache::writeCache($this->data, "translations-" . $GLOBALS['appName'] . "-" .$language, 86400);
		}
	}

	public function getUserLanguage()
	{
		switch (currentuser::getInstance()->getLanguage())
		{
			case 'FRENCH':
				return 'french';
				break;

			case 'GERMAN':
				return 'german';
				break;

			case 'ITALIAN':
				return 'italian';
				break;

			case 'SPANISH':
				return 'spanish';
				break;

			default:
				return 'english';
		}
	}

	public function translate($key = "")
	{
		$value = $key;

		$key = strtoupper($key);

		if ($key == "")
		{
			return $key;
		}

		if (isset($this->data[$key]) )
		{
			$value = $this->data[$key]['value'];
		}
		else
		{
			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM translations WHERE translateFrom='" . page::xmlentities($key). "'");

			if (mysql_num_rows($dataset) == 0)
			{
				page::addDebug("Inserting $key", __FILE__, __LINE__);
				mysql::getInstance()->selectDatabase("intranet")->Execute("INSERT INTO translations(translateFrom, english, application) VALUES ('" . page::xmlentities($key) . "', '" . $value . "', 'new')");
			}
			else
			{
				page::addDebug("$key already exists, cache may need clearing or translation is part of another app", __FILE__, __LINE__);
			}
		}

		return trim($value);
	}

	public function getTranslateID($key = "")
	{
		$value = "0";

		$key = strtoupper($key);

		if (isset($this->data[$key]) )
		{
			$value = $this->data[$key]['id'];
		}

		return $value;
	}
}