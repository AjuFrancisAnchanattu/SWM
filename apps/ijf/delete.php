<?php
require 'lib/ijf.php';

/**
 * This is the IJF (Item Justification Form) Application.
 *
 * 
 * @package intranet	
 * @subpackage IJF
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class delete extends page
{
	/**
	 * This stores the IJF which is loaded.
	 *
	 * @var slob
	 */
	private $ijf;
	
	function __construct()
	{
		
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT * FROM ijf WHERE id='" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		$this->id = $fields['id'];
		
		// new action, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<deleteAction><action>" . $fields['id'] . "</action><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from><creator>" . usercache::getInstance()->get($fields['initiatorInfo'])->getName() . "</creator></deleteAction>");
		
	
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/ijf/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		email::send(usercache::getInstance()->get($fields['initiatorInfo'])->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("deleted_ijf")) . " - ID: " . $fields['id'], "$email");
		
		// Ensures all fields that use the requested IJF_id are deleted ...
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM ijf WHERE id='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM log WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM commercialPlanning WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM dataAdministration WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM finance WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM production WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM quality WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM productManager WHERE ijfId='" . $_REQUEST['id'] . "'");	
		//mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM productionSite WHERE ijfId='" . $_REQUEST['id'] . "'");	
		//mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM productOwner WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM purchasing WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM documents WHERE ijfId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("IJF")->Execute("DELETE FROM commentLog WHERE ijfId='" . $_REQUEST['id'] . "'");	
	
		// Redirect To IJF Home
		header("Location: ../../apps/ijf");
		
	}
	
}

?>