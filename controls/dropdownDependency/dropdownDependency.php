<?php

class dropdownDependency extends dropdown
{	
	private $cssClass = "dropdown";
	private $onChange = "";

	function __construct($name)
	{
		parent::__construct($name);
		
	}

	
	public function output()
	{		
		if (!$this->getVisible())
		{
			return "";
		}
		$this->lateBindingGetSource();
		
		$output = $this->getRowTop();
		
		$output .= "<dropdownDependency>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . ($this->postback == true ? 'true' : 'false') . "</postback>";

		if (strlen($this->onChange) > 0)
		{
			$output .= "<onChange>" . $this->onChange . "</onChange>";
		} 		
		
		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . ($this->getValue() == $this->options[$i]['value'] ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$i]['display'])) : page::xmlentities($this->options[$i]['display'])) . "</option>\n";
		}
		
		
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
		
		
		
		$output .= "<cssClass>" . $this->cssClass . "</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "</dropdownDependency>";
		
		$output .= $this->getRowBottom();
		
		return $output;
		
	}
	
	public function setCssClass($class)
	{
		$this->cssClass = $class;
	}
	
	public function setPostback($choice)
	{
		$this->postback = $choice;
	}
	
	public function setOnChange($onChange)
	{
		$this->onChange = $onChange;
	}
	
}

?>