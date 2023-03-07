<?php
/*WC*/
class availableColumnsList extends combo
{
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<availableColumnsList>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<postback>" . $this->postback . "</postback>";
		
		for ($i=0; $i < count($this->options); $i++)
		{
			$output .= "<option name=\"" . page::xmlentities($this->options[$i]['name']) . "\" selected=\"" . (in_array($this->options[$i]['name'], $this->value) ? 'yes' : 'no') . "\">" . page::xmlentities($this->options[$i]['value']) . "</option>\n";
		}
		
		$output .= "<required>" . $this->required . "</required>";
		$output .= "</availableColumnsList>";
		
		$output .= $this->getRowBottom();
		
		return $output;
        }
	
	public function setFilterObject($object)
	{
		$colList = array();
		$colList[] = array('name' => "col1", 'value' => "col1");
		$colList[] = array('name' => "col2", 'value' => "col2");
		$colList[] = array('name' => "col3", 'value' => "col3");
		foreach ($colList as $control)
		{
			$this->options[] = $control;
		}
	}
}
/*WC END*/
?>