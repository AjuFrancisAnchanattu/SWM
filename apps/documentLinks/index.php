<?php
require 'lib/docLink.php';

/**
 * This is the documentLinks Application.
 * 
 * @package intranet	
 * @subpackage documentLinks
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 22/05/2009
 */
class index extends page 
{
	private $section;
	private $sqlWhere = "";
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('documentLinks');
		
		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/documentLinks/xml/menu.xml");

		$this->xml .= "<documentLinks>";
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for support
		$snapins_left->register('apps/documentLinks', 'sections', true, true);		//puts the support load snapin in the page
		
		$this->xml .= "<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>";
				
		
		// Checks to see if a section is passed over, and sets the SQL where statement.
		if(isset($_REQUEST['section']) && $_REQUEST['section'] != "")
		{
			$this->section = $_REQUEST['section'];
			$this->sqlWhere = " WHERE section = '" . $this->section . "'";
		}
		
		
		// Runs the SQL Statement
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM links" . $this->sqlWhere . " ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<documentDetails>";
			
			$this->xml .= "<id>" . $fields['id'] . "</id>";
			$this->xml .= "<filename>" . $fields['filename'] . "</filename>";
			$this->xml .= "<section>" . $fields['section'] . "</section>";
			$this->xml .= "<title>" . $fields['title'] . "</title>";
			$this->xml .= "<dateAdded>" . page::transformDateForPHP($fields['date']) . "</dateAdded>";
			$this->xml .= "<addedBy>" . usercache::getInstance()->get($fields['addedBy'])->getName() . "</addedBy>";
			
			$this->xml .= "</documentDetails>";
		}
		
		
		
		
		
		
			
		$this->xml .= "";
		
		$this->add_output($this->xml);
			
		$this->add_output("</documentLinks>");
		
		$this->output('./apps/documentLinks/xsl/index.xsl');
	}
}

?>