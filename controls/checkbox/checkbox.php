<?php

class checkbox extends item
{	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
			
		$output .= "<checkbox>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "</checkbox>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}
}

?>