<?php

class dropdown extends itemlist
{	
	private $cssClass = "dropdown";
	private $onChange = "";

	function __construct($name)
	{
		parent::__construct($name);
		
		$this->options[] = array('value' => '', 'display' => translate::getInstance()->translate('please_select'));
	}

	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		$this->lateBindingGetSource();
		
		$output = $this->getRowTop();
		
		$output .= "<dropdown>";
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
		
		$output .= "<cssClass>" . $this->cssClass . "</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		$output .= "</dropdown>";
		
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
	
	public function addOption($value,$display){
		$this->options[] = array('value' => $value, 'display' => $display);
	}
	
}

?>