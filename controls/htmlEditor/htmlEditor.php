<?php

class htmlEditor extends item
{	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<htmlEditor>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		//$output .= "<largehtmlEditor>" . ($this->largehtmlEditor == true ? 'true' : 'false') . "</largehtmlEditor>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		$output .= "</htmlEditor>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}

}

?>