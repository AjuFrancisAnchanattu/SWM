<?php

class columnCCRstatus extends column
{	
	function __construct($name)
	{
		parent::__construct($name);
		$this->name = $name;
	}

	public function getValue()
	{
		if ($this->value == 0)
		{
			return translate::getInstance()->translate("in_progress");
		}
		elseif ($this->value == 1)
		{
			return translate::getInstance()->translate("completed");
		}
	}
}

?>