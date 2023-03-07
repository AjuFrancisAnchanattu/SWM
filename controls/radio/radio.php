<?php

class radio extends itemlist
{
	private $onKeyPress = "";
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$this->lateBindingGetSource();
		
		$output = $this->getRowTop();
		
		$output .= "<radio>";
		
		if (is_array($this->dependency))
		{
			$output .= "<dependency>";
			
			for ($i=0; $i < count($this->dependency); $i++)
			{
				$output .= "<outcome show=\"" . ($this->dependency[$i]->getShow() ? 'true' : 'false') . "\">";
				
				$group = $this->dependency[$i]->getGroup();
				
				if (is_array($group))
				{
					for ($g=0; $g < count($group); $g++)
					{
						$output .= "<group>" . $group[$g] . "</group>";
					}
				}
				else
				{
					$output .= "<group>" . $group . "</group>";
				}
				
				$rules = $this->dependency[$i]->getRules();
				$temp = array();
				
				for ($rule=0; $rule < count($rules); $rule++)
				{
					$temp[] = "document.getElementById('".$rules[$rule]->control.$rules[$rule]->value."').checked";
				}
				
				$condition = $this->dependency[$i]->getRuleCondition() == "or" ? " || " : " &amp;&amp; ";
				
				$output .= "<if>";
				$output .= implode($condition, $temp);
				$output .= "</if>";
				$output .= "</outcome>";
			}
			
			$output .= "</dependency>";
		}
		
		
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . ($this->postback ? $this->postback : 'false') . "</postback>";
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
		}

		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . ($this->getValue() == $this->options[$i]['value'] ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$i]['display'])) : page::xmlentities($this->options[$i]['display'])) . "</option>\n";
		}
		
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "</radio>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	public function setOnKeyPress($javascript)
	{
		$this->onKeyPress = $javascript;
	}
}

?>