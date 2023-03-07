<?php

class itemlist extends item 
{
	protected $options = array();
	protected $postback = false;
	protected $translate = false;
	protected $errorMessage = "";
	
	protected $source = array();
	
	// for setSQLSource
	protected $sqlSource_database;
	protected $sqlSource_query;
	
	// for setXMLSource
	protected $xmlSource_xml;
	protected $xmlSource_cacheTime;
	
	// for setArraySource
	protected $arraySource_array;
	
	
	function __construct($name)
	{
		parent::__construct($name);
	}
	
	public function clearData()
	{
		$this->options = array();
	}
	
	public function setPostBack($postback)
	{
		$this->postback = $postback;
	}
	
	public function setSQLSource($database, $query)
	{
		$this->source[] = 'sql';
		$this->sqlSource_database = $database;
		$this->sqlSource_query = $query;
	}
	
	public function setXMLSource($xml,$cacheTime=86400)
	{
		$this->source[] = 'xml';
		$this->xmlSource_xml = $xml;
		$this->xmlSource_cacheTime = $cacheTime;
	}
	
	public function setArraySource($array)
	{
		$this->source[] = 'array';
		$this->arraySource_array = $array;
	}
	
	public function setTranslate($translate)
	{
		$this->translate = $translate;	
	}
	
	public function shouldTranslate()
	{
		return $this->translate;
	}
	
	public function getOptions()
	{
		return $this->options;
	}
	
	// Added by JM
	public function setErrorMessage($errorMessage)
	{
		$this->errorMessage = translate::getInstance()->translate($errorMessage);
	}
	public function getErrorMessage()
	{
		return $this->errorMessage;
	}
	// END
	
	public function lateBindingGetSource()
	{
		for ($i=0; $i < count($this->source); $i++)
		{
			switch ($this->source[$i])
			{
				case 'sql':
					
					$this->_setSQLSource();
					break;
					
				case 'xml':
					
					$this->_setXMLSource();
					break;
					
				case 'array':
					
					$this->_setArraySource();
					break;
			}
		}
	}
	
	
	// internal function used by lateBindingGetSource()
	
	private function _setSQLSource()
	{
		$dataset = mysql::getInstance()->selectDatabase($this->sqlSource_database)->Execute($this->sqlSource_query);
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->options[] = array('value' => $fields[1], 'display' => $fields[0]);
		}	
	}
	
	private function _setXMLSource()
	{
		$contents = cache::getLocalDocument($this->xmlSource_xml, $this->xmlSource_cacheTime);
		
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($contents);
		$results = $xmlDoc->getElementsByTagName('item');
		
		foreach ($results as $result)
		{
			$this->options[] = array('value' => $result->getAttribute('value'), 'display' => $result->getAttribute('display') ? $result->getAttribute('display') : $result->getAttribute('value'));
		}

	}
	
	private function _setArraySource()
	{
		foreach ($this->arraySource_array as $item)
		{
			$this->options[] = array('value' => $item['value'], 'display' => $item['display']);
		}
	}
	
	
	public function readOnlyOutput()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$this->lateBindingGetSource();
		
		$output = $this->getRowTop(false);
		
		$tempValue = "";
		
		if ($this->getDisplayValue())
		{
			if (is_array($this->getDisplayValue()))
			{
				$tempArray = $this->getDisplayValue();
				
				if ($this->shouldTranslate())
				{
					for ($i=0; $i < count($tempArray); $i++)
					{
						$tempArray[$i] = translate::getInstance()->translate($tempArray[$i]);
					}
				}
				
				$tempValue = implode(", ", $tempArray);
			}
			else 
			{
				if ($this->shouldTranslate())
				{
					$tempValue = translate::getInstance()->translate($this->getDisplayValue());
				}
				else 
				{
					$tempValue = trim($this->getDisplayValue());
				}
			}
		}
		else
		{
			$tempValue = "-";
		}
		
		if ($tempValue == "" || $tempValue == " ")
		{
			$tempValue = "-";
		}

		$output .= "<readonly>" . page::formatAsParagraphs($tempValue, "\n") . "</readonly>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	
	public function getDisplayValue()
	{
		if ($this->isAnNTLogon)
		{
			if (strlen($this->getValue()) > 0)
			{
				//page::addDebug("getName()", __FILE__, __LINE__);
				return usercache::getInstance()->get($this->getValue())->getName();
			}
			else 
			{
				return "";
			}
		}
		else 
		{
			
			
			for ($i=0; $i < count($this->options); $i++)
			{
				page::addDebug($this->options[$i]['value'], __FILE__, __LINE__);
				if ($this->options[$i]['value'] == $this->getValue())
				{
					page::addDebug("wooo, winner", __FILE__, __LINE__);
					return $this->options[$i]['display'];
				}
			}
			
			page::addDebug("booo, " . $this->getValue(), __FILE__, __LINE__);
			return $this->getValue();
		}
	}
}

?>