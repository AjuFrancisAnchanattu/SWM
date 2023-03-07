<?php

class invisibletext extends item
{	
	
	public function output()
	{
		$this->setVisible(false);
		
		$output = "";
		
		$output .= "<invisibletext>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<required>" . $this->required . "</required>";
		$output .= "</invisibletext>";
		
		return $output;
	}
}

?>