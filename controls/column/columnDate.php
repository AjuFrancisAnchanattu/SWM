<?php

class columnDate extends column
{	
	function __construct($name)
	{
		parent::__construct($name);
		$this->name = $name;
	}

	public function getValue()
	{
		if (page::isDate($this->value))
		{
			return page::transformDateForPHP($this->value);
		}
	}
}

?>
	