<?php

/**
*
 * This is the news page for the BES Application
 *
 * @package apps
 * @subpackage BES
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 23/06/2010
 */

class news extends page
{

	function __construct()
	{				
		parent::__construct();
		
		if (isset($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
		}
		else 
		{
			die("No news item selected.");
		}
		
		$this->add_output("<besNews>");
		$this->xml = "";
		
		$sql = "SELECT subject, body, openDate 
			FROM comm
			WHERE newstype = '1'
			AND id = '" . $id . "'";
		
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute($sql);
				
		$counter = 0;
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<newsSubject>" . $fields['subject'] . "</newsSubject>";
		$this->xml .= "<newsContent>" . page::formatAsParagraphs($fields['body']) . "</newsContent>";
		$this->xml .= "<newsDate>" . $fields['openDate'] . "</newsDate>";
		
		$this->add_output($this->xml);
		$this->add_output("</besNews>");
		$this->output('./apps/bes/xsl/bes.xsl');			
	}
		
}