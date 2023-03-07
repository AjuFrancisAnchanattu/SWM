<?php
require 'lib/docman.php';

/**
 * This is the DocMan.
 *
 * 
 * This is the hierarchy of DM.
 * 
 * 
 * @package intranet	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class hierarchy extends page 
{
	/**
	 * This stores the DM which is loaded.
	 *
	 * @var docman
	 */
	private $docman;
	
	function __construct()
	{
		
		parent::__construct();
		$this->setActivityLocation('Doc Man');
		
		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/docman/menu.xml");

		$this->add_output("<DocManHierarchy>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for DM
		$snapins_left->register('apps/docman', 'loadDoc', true, true);		//puts the docman load snapin in the page
		$snapins_left->register('apps/docman', 'totalDoc', true, true);		//puts the docman total snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
				
		
		
		$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT `docCategory` FROM documents GROUP by docCategory");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->add_output("<hierarchy>");
			$this->add_output("<docCategory>" . $fields['docCategory'] . "</docCategory>");
			page::addDebug($fields['docCategory'], __FILE__, __LINE__);
			$this->add_output("</hierarchy>");
		}
		

		
		
		$this->add_output("</DocManHierarchy>");

		$this->output('./apps/docman/xsl/hierarchy.xsl');
	}
	
}

?>