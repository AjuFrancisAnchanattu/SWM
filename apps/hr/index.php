<?php

class index extends page 
{
	function __construct()
	{
		parent::__construct();
		
		$this->redirect("/apps/employeedb/");
	}
}

?>
