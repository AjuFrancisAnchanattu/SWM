<?php

class dropdownAlternative extends dropdown
{	
	
	private $dropdownOptions;
	private $textboxOtherOption;
	protected $value;
	
	
	function __construct($name)
	{
		$this->form = new form($name);
		
		$this->options[] = array('value' => '', 'display' => translate::getInstance()->translate('please_select'));
		
		
		$this->name = $name;
		
		$this->formOptions = new group("formOptions");
		/*	
		$this->dropdownOptions = new specialDropdown($this->name ."_options");
		$this->dropdownOptions->setShowRow(false);
		$this->dropdownOptions->setDataType("string");
		$this->form->add($this->dropdownOptions);
		*/
		
		$this->textboxOtherOption = new textbox($this->name ."_otherOption");
		$this->textboxOtherOption->setShowRow(false);
		$this->textboxOtherOption->setDataType("string");
		$this->textboxOtherOption->setCssClass("textboxOther");
		$this->formOptions->add($this->textboxOtherOption);
		
		
		$this->form->add($this->formOptions);
		
	}
	
	
	public function output()
	{
		$valueIsInOptions = false;
	
		
		if (!$this->getVisible())
		{
			return "";
		}
		
		$this->lateBindingGetSource();
		$this->addOtherOption();
		
		$output = $this->getRowTop();
		
		$output .= "<dropdownAlternative>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . $this->postback . "</postback>";
		
		
		$output .= "<dropdownOptions>";
			$output .= "<name>" . $this->name . "</name>";
			$output .= "<postback>" . $this->postback . "</postback>";
	
			for ($i=0; $i < count($this->options); $i++)
			{
				if ($this->value == $this->options[$i]['value'] || $this->value == "") 
				{
					$valueIsInOptions = true;
				}
			}
			if ($valueIsInOptions == false)
			{
				$this->textboxOtherOption->setValue($this->value);	
				$this->setValue("other");
			}
			else 
			{
				$this->textboxOtherOption->setCssClass("textboxInvisible");	
			}
			for ($i=0; $i < count($this->options); $i++)
			{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['value']) . "\" selected=\"" . ($this->value == $this->options[$i]['value'] ? 'yes' : 'no') . "\">" . ($this->shouldTranslate() ? translate::getInstance()->translate(page::xmlentities($this->options[$i]['display'])) : page::xmlentities($this->options[$i]['display'])) . "</option>\n";
			}
			
			$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "</dropdownOptions>";
		
		$output .= "<textboxOtherOption>" . $this->textboxOtherOption->output() . "</textboxOtherOption>";
		
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "</dropdownAlternative>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
	
	/*
	public function generateInsertQuery()
	{
		
		$output = array(
			'name' =>  $this->name,
			'value' => "'" . $quantity['value'] . "', '" . $measurement['value'] . "'"
		);
		
	
		return $output;
		
	}
	
	public function generateUpdateQuery()
	{
		
		$quantity = $this->form->get($this->name.'_quantity')->generateUpdateQuery();
		$measurement = $this->form->get($this->name.'_measurement')->generateUpdateQuery();
			
		$output = $quantity . ", " . $measurement;			
		
		return $output;
		
	}
	*/
	public function setRequired($choice)
	{
		$this->required = $choice;
		//$this->dropdownOptions->setRequired($choice);
		$this->textboxOtherOption->setRequired($choice);
	}
	
	/*
	public function setSQLSource($database, $sql)
	{
		$this->dropdownOptions->setSQLSource($database, $sql);
	}
	
	public function setXMLSource($xml,$cacheTime=86400)
	{
		$this->dropdownOptions->setXMLSource($xml,$cacheTime=86400);
	}
	*/
	
	
	public function getValue()
	{
		page::addDebug($this->value, __FILE__, __LINE__);
		
		if (strtolower($this->value) == "other")
		{ 
			return $this->textboxOtherOption->getValue();
		}
		else 
		{
			return $this->value;
		}
	}
	
	
	public function processPost($value)
	{
		if (!is_null($value))
		{
			$this->setValue($value);
			
			
			if (strtolower($value) == "other")
			{ 
				$this->form->processPost();
				$this->setValue($this->form->get($this->name ."_otherOption")->getValue());
			}
		}
	}
	
	/*
	public function setValue($value)
	{
		$this->value = $value;
		//$this->dropdownOptions->setValue($value);	
	}
	*/
	
	public function addOtherOption()
	{
		array_push($this->options, array('value' => "other", 'display' => "OTHER"));
	}
}

?>