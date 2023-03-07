<?php

class columnNTLogon extends column
{	
	function __construct($name)
	{
		parent::__construct($name);
		$this->name = $name;
	}

	public function getValue()
	{
		return usercache::getInstance()->get($this->value)->getName();
	}
}

?>
	