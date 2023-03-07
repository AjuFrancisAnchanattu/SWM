<?php
class myRadio extends radio
{
	//0 - normal output
	//1 - read-only output
	protected $outputType = 0;
	protected $nullable = false;
	
	protected $keyPress = "";
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}

		$this->lateBindingGetSource();

		$output = $this->getRowTop();

		$output .= "<radio>";

		if( $this->outputType == 0 )
		{
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
			
			if (!empty($this->keyPress))
			{
				$output .= "<onKeyPress>" . $this->keyPress . "</onKeyPress>";
			}
			
			$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
			$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		}
		else
		{
			$output .= "<disabled>true</disabled>";
		}

		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . ($this->postback ? $this->postback : 'false') . "</postback>";

		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . ($this->getValue() == $this->options[$i]['value'] ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$i]['display'])) : page::xmlentities($this->options[$i]['display'])) . "</option>\n";
		}

		
		$output .= "</radio>";

		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function setOnChange( $javascript )
	{
		$this->keyPress = $javascript;
	}
	
	/*
	public function output()
	{
		if($this->outputType == 0)
		{
			//textbox original output
			return parent::output();
		}
		else
		{
			//readonly output from item class
			return $this->readOnlyOutput();
		}
	}
	*/
	
	public function setReadOnly($setIgnore = false)
	{
		$this->outputType = 1;
		$this->setIgnore($setIgnore);
	}
	
	public function setNullable($choice = true)
	{
		$this->nullable = $choice;
	}
	
	public function validate()
	{
		if($this->outputType == 1)
		{
			$this->setValid(true);
			return true;
		}
		else
		{
			return parent::validate();
		}
	}
	
	public function resetValue()
	{
		$this->setValue("");
	}
	
	public function generateInsertQuery()
	{
		if($this->nullable && $this->getValue() == '')
		{
			return array(
				'name' => "`" . $this->getName() . "`",
				'value' => "NULL"
			);
		}
		else
		{
			return parent::generateInsertQuery();
		}
	}
	
	public function generateUpdateQuery()
	{
		if($this->nullable && $this->getValue() == '')
		{
			return "`" . $this->getName() . "` = NULL";
		}
		else
		{
			return parent::generateUpdateQuery();
		}
	}
	
	public function preInsertOperations()
	{
		if( !$this->nullable)
		{
			parent::preInsertOperations();
		}
	}
	
	public function preUpdateOperations()
	{
		if( !$this->nullable)
		{
			parent::preUpdateOperations();
		}
	}
}
?>