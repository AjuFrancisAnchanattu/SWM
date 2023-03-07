<?php
require 'lib/appraisal.php';

/**
 * This is the appraisal
 *
 * 
 * This is the home page of appraisal.
 * This page allows the user to load a summary of a appraisal.
 * The user can see what appraisal reports they own, which are currently open via the appraisal Report Snapin.
 * The user can also see what appraisal report actions they have waiting on them via the appraisal Action Snapin.
 * 
 * 
 * @package intranet	
 * @subpackage appraisal
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class delete extends page 
{
	/**
	 * This stores the appraisal which is loaded.
	 *
	 * @var appraisal
	 */
	private $appraisal;
	
	function __construct()
	{
		
		parent::__construct();
		
		page::setDebug(true); // debug at the bottom
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM appraisal WHERE id = " . $_REQUEST['id'] . "");
		$fields = mysql_fetch_array($dataset);
		$this->id = $fields['id'];
		
		// new action, email the owner
		$dom = new DomDocument;
		$dom->loadXML("<deleteAction><action>" . $fields['id'] . "</action><sent_from>" . usercache::getInstance()->get($fields['owner'])->getName() . "</sent_from><creator>" . usercache::getInstance()->get($fields['internalSalesName'])->getName() . "</creator></deleteAction>");
		
	
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/appraisal/xsl/email.xsl");
	
		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);
	
		$email = $proc->transformToXML($dom);
		
		// THEN DO THIS STUFF BELOW TO DELETE
				
		// Ensures all fields that use the requested appraisal_id are deleted ...
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `actionLog` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `appraisal` WHERE id ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `appraisalComments` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `savedForms` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `development` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `review` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `training` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	
		mysql::getInstance()->selectDatabase("appraisals")->Execute("DELETE FROM `relationships` WHERE appraisalId ='" . $_REQUEST['id'] . "'");	

		// Redirect To appraisal Home
		header("Location: /apps/appraisal/");
		
	}
	
}

?>