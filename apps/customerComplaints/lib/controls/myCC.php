<?php

class myCC extends item
{	
	private $cssClass = "textbox";
	private $onKeyPress = "";
	private $onChange = "";
	private $onClick = "";
	
	public function output()
	{
		if (!$this->getVisible())
		{
			return "";
		}
		
		$output = $this->getRowTop();
			
		$output .= "<multipleCC>";
		$output .= "<name>" . $this->name . "</name>";
		$output .= "<value>" . $this->getDisplayValue() . "</value>";
		$output .= "<maxlength>" . $this->getLength() . "</maxlength>";
		$output .= "<minlength>" . $this->getMinLength() . "</minlength>"; // Added by JM
		$output .= "<cssClass>" . $this->cssClass . "</cssClass>";
		$output .= "<required>" . ($this->required == true ? 'true' : 'false') . "</required>";
		$output .= "<legend>" . $this->getLegend() . "</legend>";
		$output .= "<errorMessage>" . $this->getErrorMessage() . "</errorMessage>";
		
		if (!empty($this->onKeyPress))
		{
			$output .= "<onKeyPress>" . $this->onKeyPress . "</onKeyPress>";
		}
		
		if (!empty($this->onChange))
		{
			$output .= "<onChange>" . $this->onChange . "</onChange>";
		}
		
		if (!empty($this->onClick))
		{
			$output .= "<onClick>" . $this->onClick . "</onClick>";
		}
		
		$output .= "</multipleCC>";
		
		$output .= $this->getRowBottom();

		return $output;
	}
	
	public function setCssClass($class)
	{
		$this->cssClass = $class;
	}
	
	public function setOnKeyPress($javascript)
	{
		$this->onKeyPress = $javascript;
	}
	
	public function setOnChange($javascript)
	{
		$this->onChange = $javascript;
	}
	
	public function setOnClick($javascript)
	{
		$this->onClick = $javascript;
	}
	
	public function validate()
	{
		$valid = true;
		$values = explode( ",", $this->getValue() );
		$pattern = "/^[A-Za-z0-9._%+-]+@(?:[A-Za-z0-9-]+\.)+[A-Za-z]{2,4}$/";
		
		if($this->getVisible() && $this->getValue() != "" && count( $values ) > 0)
		{
			foreach( $values AS $email )
			{
				if( !$this->validateRegex($pattern, $email) )
				{
					$valid = false;
					$this->setErrorMessage("invalid_email");
					break;
				}
				
				$sql = "SELECT *
						FROM employee 
						WHERE email = '$email' 
						ORDER BY NTLogon ASC 
						LIMIT 1";
					
				$dataset = mysql::getInstance()->selectDatabase("membership")->Execute($sql);
				
				if( mysql_num_rows( $dataset ) == 0 )
				{
					$valid = false;
					$this->setErrorMessage("invalid_email");
					break;
				}
			}
		}
		
		$this->setValid($valid);
		page::addDebug("Validation Checking: " . $this->getName() . ": " . ($valid ? 'true' : 'false') , __FILE__, __LINE__);
		
		return $valid;
	}
	
	private function validateRegex($pattern, $value)
	{
		$valid = true;
		
		if(preg_match($pattern, $value) == 0)
		{
			$valid = false;
		}
		else
		{
			$valid = true;
		}
		
		return $valid;
	}
}

?>