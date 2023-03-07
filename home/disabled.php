<?php

class disabled extends page
{
	function __construct()
	{
		parent::__construct();

		$this->header->setLocation($this->getActivityLocation());
		
		$this->add_output("<disabled />");
		
		$this->output('./xsl/disabled.xsl');
	}
}