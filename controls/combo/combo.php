<?php

class combo extends itemlist
{
	protected $value = array();	
	
	protected $selectSize;
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$this->lateBindingGetSource();
		
		$output = $this->getRowTop();
		
		$output .= "<combo>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . $this->postback . "</postback>";
		
		$output .= "<selectSize>" . $this->selectSize . "</selectSize>";		
		
		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . (in_array($this->options[$i]['value'], $this->value) ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$i]['display'])) : page::xmlentities($this->options[$i]['display'])) . "</option>\n";
		}
		
		$output .= "<required>" . $this->required . "</required>";
		$output .= "</combo>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setValue($value)
	{
		if (is_array($value))
		{
			$this->value = $value;
		}
		elseif (is_string($value))
		{
			$this->value = explode("||",$value);
		}
		else 
		{
			echo "Unknown type set as value.";
		}
	}
	
	public function getValue()
	{
		return implode("||",$this->value);
	}
}

?>