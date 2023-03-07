<?php
require 'lib/docman.php';

/**
 * This is the DocMan Application.
 *
 * 
 * @package intranet	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class delete extends page
{
	/**
	 * This stores the DocMan which is loaded.
	 *
	 * @var docman
	 */
	private $docman;
	
	function __construct()
	{
		
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM documents WHERE id='" . $_REQUEST['id'] . "'");
		$fields = mysql_fetch_array($dataset);
		$this->id = $fields['id'];
		
		// new action, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<deleteAction><action>" . $fields['id'] . "</action><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from><creator>" . usercache::getInstance()->get($fields['creator'])->getName() . "</creator></deleteAction>");
		
	
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/docman/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
	
		email::send(usercache::getInstance()->get($fields['creator'])->getEmail(), "intranet@scapa.com", (translate::getInstance()->translate("deleted_docman")) . " - ID: " . $fields['id'], "$email");
		
		
		
		// Ensures all fields that use the requested docman_id are deleted ...
		mysql::getInstance()->selectDatabase("DocMan")->Execute("DELETE FROM documents WHERE id='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("DocMan")->Execute("DELETE FROM log WHERE docId='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("DocMan")->Execute("DELETE FROM commentLog WHERE docId='" . $_REQUEST['id'] . "'");	
	
		// Redirect To Docman Home
		header("Location: ../../apps/docman/");
		
	}
	
}

?>