<?php
require 'lib/comm.php';

/**
*
 * This is the comms Application.
 * This is the home page of comms.
 * 
 * @package apps	
 * @subpackage comms
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 22/06/2009
 */

class viewArticle extends page
{
	private $comm;
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Comms');

		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/comms/menu.xml");
		$this->add_output("<commsHome>");

		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for comms
		//$snapins_left->register('apps/comms', 'loadComms', true, true);		//puts the comms load snapin in the page
		$snapins_left->register('apps/comms', 'generalComms', true, true);		//puts the comms load snapin in the page

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->xml .= "<commAdmin>true</commAdmin>";
		}
		
		if(isset($_REQUEST['id']) && $_REQUEST['id'] != "")
		{
			$id = $_REQUEST['id'];
		}
		else 
		{
			page::redirect("/");
		}
		
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE id = " . $id	);
		
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<articleId>" . $fields['id'] . "</articleId>";
		$this->xml .= "<articleTitle>" . $fields['subject'] . "</articleTitle>";
		
		if($fields['isImage'] == 1)
		{
			$this->xml .= "<isImage>1</isImage>";
			$this->xml .= "<articleImageLink>" . $fields['imageURL'] . "</articleImageLink>";
			$this->xml .= "<articleBody>" . page::reversexmlentities($fields['body'])  . "</articleBody>";
		}
		else 
		{
			$this->xml .= "<articleBody>" . page::formatAsParagraphs($fields['body'])  . "</articleBody>";
		}
		
		$this->xml .= "<articleDate>" . common::transformDateTimeForPHP($fields['openDate']) . "</articleDate>";
		
		
		if(is_dir(getcwd() . "/apps/comms/attachments/" . $id))
		{
			$this->xml .= "<articleAttachment>";
			$dirPath = getcwd() . "/apps/comms/attachments/" . $id;
			
			if ($handle = opendir($dirPath))
			{
			   while (false !== ($file = readdir($handle))) 
			   {
			      if ($file != "." && $file != ".." && $file != "Thumbs.db" && !is_dir("$dirPath/$file")) 
			      {
			     		$this->xml .= "<fileName><name>" . $file . "</name></fileName>";
			      }
			   }
			   closedir($handle);
			}
			$this->xml .= "</articleAttachment>";
		}

		
		
		
		$this->add_output($this->xml);
		
		$this->add_output("</commsHome>");

		$this->output('./apps/comms/xsl/viewArticle.xsl');
	}
}

?>