<?php

class availableFieldsList extends combo
{
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<availableFieldsList>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . $this->postback . "</postback>";
		
		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['name']) . "\" selected=\"" . (in_array($this->options[$i]['name'], $this->value) ? 'yes' : 'no') . "\">" . page::xmlentities($this->options[$i]['value']) . "</option>\n";
		}
		
		$output .= "<required>" . $this->required . "</required>";
		$output .= "</availableFieldsList>";
		
		$output .= $this->getRowBottom();
		
		return $output;
        }
	
	public function setFilterObject($object)
	{
		foreach ($object->form->getGroup('default')->getAllControls() as $control)
		{
			$this->options[] = array('name' => $control->getName(), 'value' => $control->getRowTitle());
		}
	}
}

?>