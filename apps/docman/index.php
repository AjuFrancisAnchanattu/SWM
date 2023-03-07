<?php
require 'lib/docman.php';

/**
 * This is the DocMan.
 *
 * 
 * This is the home page of DM.
 * This page allows the user to load a summary of a DM.
 * The user can see what DM reports they own, which are currently open via the DM Report Snapin.
 * The user can also see what DM report actions they have waiting on them via the DM Action Snapin.
 * 
 * 
 * @package intranet	
 * @subpackage DocMan
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/07/2006
 */
class index extends page 
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

		$this->add_output("<DocManHome>");
		
		$snapins_left = new snapinGroup('snapin_left');		//creates the snapin group for DM
		$snapins_left->register('apps/docman', 'loadDoc', true, true);		//puts the docman load snapin in the page
		$snapins_left->register('apps/docman', 'totalDoc', true, true);		//puts the docman total snapin in the page
		
		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");
				
		$this->docman = new docman();		//creates an empty DocMan
		
		if(isset($_REQUEST['notfound']) == 'true')
		{
				$this->add_output("<notfound>true</notfound>\n");
		}
		
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// checks if a DocMan id was passed

			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}
			
			
			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}
	
			
			
			$this->xml .= "<DocMan_report>";

			
			page::addDebug("ERRROROROROROROROROROROROR", __FILE__, __LINE__);
			
				//loads a report if a report id is set
				if ($this->docman->load($_POST['report']))
				{
					$this->xml .= "<id>" . $this->docman->getId() . "</id>\n";
					$this->xml .= "<docName>" . $this->docman->form->get("docName")->getValue() . "</docName>";
					$this->xml .= "<owner>" . usercache::getInstance()->get($this->docman->getOwner())->getName() . "</owner>\n";
					$this->xml .= "<admin>" . (currentuser::getInstance()->isAdmin() || currentuser::getInstance()->hasPermission('docman_admin') ? 'true' : 'false') . "</admin>\n";
						
				
					//loads the comments details for the SLOB
					$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM commentLog WHERE id='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");
		
					$this->xml .= "<DocMan_comments>";
					
						while ($fields = mysql_fetch_array($dataset)) 
						{
							$this->xml .= "<item2>";
							$this->xml .= "<id2>" . usercache::getInstance()->get($fields['id'])->getName() . "</id2>\n";
							$this->xml .= "<user2>" . usercache::getInstance()->get($fields['owner'])->getName() . "</user2>\n";
							$this->xml .= "<date2>" . $fields['logDate'] . "</date2>\n";
							$this->xml .= "<comment>" . $fields['comment'] . "</comment>\n";
							$this->xml .= "</item2>";
						}
						
					$this->xml .= "</DocMan_comments>";
					
					
					
					//loads the log details for the DocMan
					$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT * FROM log WHERE docId ='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");
		
					$this->xml .= "<DocMan_log>";
					
						while ($fields = mysql_fetch_array($dataset)) 
						{
							$this->xml .= "<item>";
							$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
							$this->xml .= "<date>" . $fields['logDate'] . "</date>\n";
							$this->xml .= "<action>" . $fields['action'] . "</action>\n";
							$this->xml .= "</item>";
						}
						
					$this->xml .= "</DocMan_log>";
					
					$no_sales = "Product has not been sold/disposed.";
					//loads the summary details for the DocMan
					$this->xml .= "<DocMan_summary>";
						$this->xml .= "<id>" . $this->docman->getId() . "</id>\n";
						$this->xml .= "<docManDate>" . $this->docman->form->get("date")->getValue() . "</docManDate>\n";
						$this->xml .= "<creator>" . $this->docman->form->get("creator")->getDisplayValue() . "</creator>\n";
						$this->xml .= "<documentName>" . $this->docman->form->get("docName")->getDisplayValue() . "</documentName>\n";
						$this->xml .= "<intranet_server>" . $this->docman->form->get("docLocation")->getDisplayValue() . "</intranet_server>\n";
						$this->xml .= "<server_path>" . $this->docman->form->get("serverPath")->getValue() . "</server_path>\n";
						$this->xml .= "<owner>" . usercache::getInstance()->get($this->docman->form->get("owner")->getValue())->getName() . "</owner>\n";
						$this->xml .= "<description>" . $this->docman->form->get("description")->getValue(). "</description>";
					$this->xml .= "</DocMan_summary>";
								


				$this->xml .= "</DocMan_report>";
			
			
				$this->add_output($this->xml);
			}
			else 
			{
				page::addDebug("ERRRRRRORRRR", __FILE__, __LINE__);
			}
	}
		
		
		
		$this->add_output("</DocManHome>");

		$this->output('./apps/docman/xsl/docman.xsl');
	}
	
}

?>