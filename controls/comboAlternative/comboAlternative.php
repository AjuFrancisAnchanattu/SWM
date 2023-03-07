<?php

class comboAlternative extends combo
{	
	
	private $comboOptions;
	private $textboxOtherOption;
	//protected $value = array();
	
	function __construct($name)
	{
		$this->form = new form($name."Form");
		
		$this->name = $name;
		
		$this->formOptions = new group("formOptions");
		
		$this->textboxOtherOption = new textbox($this->name ."_otherOption");
		$this->textboxOtherOption->setShowRow(false);
		$this->textboxOtherOption->setDataType("string");
		$this->textboxOtherOption->setCssClass("textboxOther");
		$this->formOptions->add($this->textboxOtherOption);
		
		$this->form->add($this->formOptions);
	}
	
	public function output()
	{
		$textboxOtherOptionIsUsed = false;
		
		if (!$this->getVisible())
		{
			return "";
		}
		
		$this->lateBindingGetSource();
		$this->addOtherOption();
		
		$output = $this->getRowTop();
		
		$output .= "<comboAlternative>";
		$output .= "<comboOptions>";
			$output .= "<name>" . $this->name . "</name>";
			$output .= "<postback>" . $this->postback . "</postback>";

				for ($i=0; $i < count($this->value); $i++)
				{
					$valueIsInOptions = false;
					
					for ($j=0;$j<count($this->options);$j++)
					{
						if ($this->value[$i] == $this->options[$j]['value'])
						{
							$valueIsInOptions = true;	
						}	
					}
					
					if ($valueIsInOptions == false)
					{
						$this->textboxOtherOption->setValue($this->value[$i]);	
						$textboxOtherOptionIsUsed = true;
					}
				}
				if ($textboxOtherOptionIsUsed == false)
				{
					$this->textboxOtherOption->setCssClass("textboxInvisible");	
				}
				
				for ($j=0; $j < count($this->options); $j++)
				{
					$output .= "<option name=\"" . page::xmlentities($this->options[$j]['value']) . "\" selected=\"" . (in_array($this->options[$j]['value'], $this->value) ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$j]['display'])) : page::xmlentities($this->options[$j]['display'])) . "</option>\n";
				}
				//$output .= "<option name=\"other\" selected=\"" . ($textboxOtherOptionIsUsed ? 'yes' : 'no') . "\">Other</option>\n";
			
			$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
			$output .= "</comboOptions>";
			$output .= "<textboxOtherOption>" . $this->textboxOtherOption->output() . "</textboxOtherOption>";
			$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
			
		$output .= "</comboAlternative>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	

	public function setRequired($choice)
	{
		$this->required = $choice;
		//$this->comboOptions->setRequired($choice);
		$this->textboxOtherOption->setRequired($choice);
	}
	
	public function getValue()
	{
		return implode(",",$this->value);
	}
	
	
	public function processPost($value)
	{
		if (!is_null($value))
		{
			$this->setValue($value);
			
			if (in_array("other",$this->value))
			{ 
				$this->form->processPost();
				$value[] = $this->form->get($this->name ."_otherOption")->getValue();
				$this->setValue($value);
			}
		}
	}
	
	public function setValue($value)
	{
		if (is_array($value))
		{
			$this->value = $value;
		}
		elseif (is_string($value))
		{
			$this->value = explode(",",$value);
		}
		else 
		{
			echo "Unknown type set as value.";
		}
	}
	
	public function addOtherOption()
	{
		array_push($this->options, array('value' => "other", 'display' => "Other"));
	}
	
	
	public function generateInsertQuery()
	{
		$output = array(
			'name' => "",
			'value' => ""
		);
		
		$tempValues = $this->value;
		
		for ($i=0;$i< count ($tempValues);$i++)
		{
			if ($tempValues[$i] == "other")
			{
				unset($tempValues[$i]);
				break;
			}
		}
		
		$tempValues = implode(",",$tempValues);
		
		$output['name'] = "`".$this->getName()."`";
		$output['value'] = "'" . $tempValues . "'";
			
		return $output;
	}
	
	
	
	public function generateUpdateQuery()
	{
		$output = "";
		
		$tempValues = $this->value;
		
		for ($i=0;$i< count ($tempValues);$i++)
		{
			if ($tempValues[$i] == "other")
			{
				unset($tempValues[$i]);
				break;
			}
		}
		
		$tempValues = implode(",",$tempValues);
		
		$output = "`".$this->getName()."` = '" . $tempValues . "'";			
				
		
		return $output;
	}
	
}

?>