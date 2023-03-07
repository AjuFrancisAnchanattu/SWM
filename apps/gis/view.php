<?php

require 'lib/gis.php';

/**
*
 * This is the GIS Application.
 * This is the view page of the GIS.
 * 
 * @package intranet	
 * @subpackage gis
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 10/11/2008
 */

class view extends page
{
	// Class Index
	private $gis;

	function __construct()
	{
		parent::__construct();
		
		if(!currentuser::getInstance()->hasPermission("gis_admin"))
		{
			die("You do not have permission to view the Global Information System");
		}
		
		$fileResults = array();

		$this->setActivityLocation('GIS');
		
		page::setDebug(true); // debug at the bottom

		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gis/xml/menu.xml");
		$this->add_output("<gisHome>");

		$snapins_left = new snapinGroup('snapin_left');
		$snapins_left->register('apps/gis', 'warning', true, true);
		$snapins_left->register('apps/gis', 'loadgis', true, true);
		$snapins_left->register('apps/gis', 'profileTypes', true, true);
		//$snapins_left->register('apps/gis', 'newInformation', true, true);
		//$snapins_left->register('apps/gis', 'archive', true, true);

		$this->add_output("<snapin_left>" . $snapins_left->getOutput() . "</snapin_left>");

		$this->gis = new gis();	//creates an empty gis
		
		if(isset($_REQUEST['gis']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['id'] =$_REQUEST['gis'];
			$_REQUEST['id'] =$_REQUEST['gis'];
		}
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// checks if a gis id was passed
			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}

			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}
			
			$this->xml .= "<gis_report>";

			//loads a report if a report id is set
			if ($this->gis->load($_REQUEST['gis']))
			{
				$this->xml .= "<id>" . $this->gis->getId() . "</id>\n";
				$this->xml .= "<profileName>" . $this->gis->form->get('profileName')->getValue() . "</profileName>";
				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
				$this->xml .= currentuser::getInstance()->isAdmin()?"<admin>true</admin>\n":"<admin>false</admin>\n";
				
				//loads the summary details for the gis
				$this->xml .= "<gisSummary>";

				$this->xml .= "<owner>" . usercache::getInstance()->get($this->gis->form->get('owner')->getValue())->getName() . "</owner>";
				$this->xml .= "<dateAdded>" . common::transformDateForPHP($this->gis->form->get('dateAdded')->getValue()) . "</dateAdded>\n";
				$this->xml .= "<initiator>" . usercache::getInstance()->get($this->gis->form->get('initiator')->getValue())->getName() . "</initiator>\n";
				if($this->gis->form->get('dateAdded')->getValue() != $this->gis->form->get('dateUpdated')->getValue())
				{
					$this->xml .= "<updated>true</updated>";
					$this->xml .= "<dateUpdated>" . common::transformDateForPHP($this->gis->form->get('dateUpdated')->getValue()) . "</dateUpdated>\n";
					$this->xml .= "<owner>" . $this->gis->form->get('owner')->getValue() . "</owner>\n";
				}
				$this->xml .= $this->gis->getID()?"<gistatus>true</gistatus>\n":"<gistatus>false</gistatus>";
				
				$datasetRet2 = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis WHERE id ='" . $_POST['report'] . "'");
				$fieldsRet2 = mysql_fetch_array($datasetRet2);
				
				if($this->gis->form->get('profileType')->getValue() == 'competitor')
				{
					$filter = "c_";
				}
				else 
				{
					$filter = "m_";
				}
				
				
				// Extracts the fields used, and displats them for the anchor references
				$i=0;	$t=0;
				foreach($fieldsRet2 as $key => $value)
				{
					if($i==1 && substr($key, 0, 2) == $filter && $value != "")
					{
						$this->xml .= "<gisAnchor>";
						$this->xml .= "<anchor>" . translate::getInstance()->translate(substr($key, 2)) . "</anchor>";
						$this->xml .= "<anchorPoint>" . $key . "</anchorPoint>";
						$i=0;
						if($t==3)
						{
							$this->xml .= "<newRow>true</newRow>";
							$t=0;
						}
						else 
						{
							$t++;
						}
						$this->xml .= "</gisAnchor>";
					}
					else 
					{
						$i=1;
					}
				}
				if(file_exists("./apps/gis/attachments/" . $this->gis->getId()))
				{
					$this->xml .= "<gisAnchor>";
					$this->xml .= "<anchor>Files</anchor>";
					$this->xml .= "<anchorPoint>files</anchorPoint>";
					$this->xml .= "</gisAnchor>";
				}						
				
				$this->xml .= "</gisSummary>";

				$this->xml .= "<gisDetails>";

				// Displays the fields used, and the values in the database
				$i=0;
				foreach($fieldsRet2 as $key => $value)
				{
					if($i==1 && substr($key, 0, 2) == $filter && $value != "")
					{
						$this->xml .= "<gisDetailsRow>";
						$this->xml .= "<anchor>" . $key . "</anchor>";
						$this->xml .= "<fieldName>" . translate::getInstance()->translate(substr($key, 2)) . "</fieldName>";
						
						if(substr($key, 2) == "website")
						{
							$this->xml .= "<hyperlink>true</hyperlink>";
						}
						else 
						{
							$this->xml .= "<hyperlink>false</hyperlink>";
						}
						
						$this->xml .= "<fieldData>" . str_replace('?' , '-', page::formatAsParagraphs($value)) . "</fieldData>";
						$this->xml .= "</gisDetailsRow>";
						$i=0;
					}
					else 
					{
						$i=1;
					}
				}
				
				
				if(file_exists("./apps/gis/attachments/" . $this->gis->getId()))
				{
					$fileResults = $this->dirList("./apps/gis/attachments/" . $this->gis->getId() . "/");
					
					sort($fileResults);
					
					$this->xml .= "<gisFileDetailsRow>";
					$this->xml .= "<fileName>" . translate::getInstance()->translate("files_attached") . "</fileName>";
					
					foreach($fileResults as $key => $value)
					{
						$this->xml .= "<fileData><fileType>" . substr($value,-3) . "</fileType><datadata>" . $value . "</datadata></fileData>";
					}

					$this->xml .= "</gisFileDetailsRow>";
				}
				
				$this->xml .= "</gisDetails>";
				
				//loads the log details for the gis
				$datasetLog = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM log WHERE gisId ='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");

				$this->xml .= "<gisLog>";

				while ($fieldsLog = mysql_fetch_array($datasetLog))
				{
					$this->xml .= "<item>";
					$this->xml .= "<user>" . usercache::getInstance()->get($fieldsLog['NTLogon'])->getName() . "</user>\n";
					$this->xml .= "<date>" . common::transformDateForPHP($fieldsLog['logDate']) . "</date>\n";
					$this->xml .= "<action>" . $fieldsLog['action'] . "</action>\n";
					$this->xml .= "<logId>" . $fieldsLog['id'] . "</logId>\n";
					$this->xml .= "<description>" . $fieldsLog['description'] . "</description>\n";
					strlen($fieldsLog['description']) > 0 ? $this->xml .= "<descriptionLength>long</descriptionLength>" : $this->xml .= "<descriptionLength>short</descriptionLength>";
					$this->xml .= "</item>";
				}

				$this->xml .= "</gisLog>";
				
				$this->xml .= "</gis_report>";

				$this->add_output($this->xml);
			}

			else
			{
				page::addDebug("ERRRRRRORRRR", __FILE__, __LINE__);
			}
		}
		else 
		{
			die("this message shoud probably not be here!");
		}
		
		$this->add_output("</gisHome>");
		
		$this->output('./apps/gis/xsl/view.xsl');
	}
	
	
	
	function dirList ($directory) 
	{
		$results = array();
		$handler = opendir($directory);
		
		while ($file = readdir($handler)) 
		{
			if ($file != '.' && $file != '..')
			{
				$results[] = $file;
			}
		}
		closedir($handler);
		
		return $results;
	}
}

?>