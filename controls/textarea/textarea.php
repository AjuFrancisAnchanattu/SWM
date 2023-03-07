<?php

class textarea extends item
{	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
		
		$output .= "<textarea>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->value . "</value>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<largeTextarea>" . ($this->largetextarea == true ? 'true' : 'false') . "</largeTextarea>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		$output .= "</textarea>";
		
		$output .= $this->getRowBottom();
		
		return $output;
	}

}

?>