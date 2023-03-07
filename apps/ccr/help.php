<?php

class help extends page 
{	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		
		$this->add_output("<CCRHelp>");
		
		$snapins = new snapinGroup('ccr_left');
		$snapins->register('apps/ccr', 'load', true);
		$snapins->register('apps/ccr', 'reports', true);
		$snapins->register('apps/ccr', 'actions', true);
		
		$snapins->get('reports')->setName(translate::getInstance()->translate("your_reports"));
		$snapins->get('actions')->setName(translate::getInstance()->translate("your_actions"));
		
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$this->add_output("</CCRHelp>");
		$this->output('./apps/ccr/xsl/help.xsl');
	}
}

?>