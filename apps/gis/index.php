<?php

require 'lib/gis.php';

/**
*
 * This is the GIS Application.
 * This is the home page of the GIS.
 * 
 * @package intranet	
 * @subpackage gis
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/11/2008
 */

class index extends page
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
		
		$this->setActivityLocation('GIS');
		common::hitCounter($this->getActivityLocation());

		
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
		
		if(isset($_REQUEST['profileType']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['id'] = NULL;
			$_REQUEST['id'] = NULL;
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
			//if ($this->gis->load($_POST['report'], $lockedStatus))
			if ($this->gis->load($_POST['report']))
			{
				$this->xml .= "<id>" . $this->gis->getId() . "</id>\n";
				$this->xml .= "<profileName>" . $this->gis->form->get('profileName')->getValue() . "</profileName>";
				$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
				$this->xml .= currentuser::getInstance()->isAdmin()?"<admin>true</admin>\n":"<admin>false</admin>\n";
				
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
				
				//loads the summary details for the gis
				$this->xml .= "<gisSummary>";

				$this->xml .= "<dateAdded>" . common::transformDateForPHP($this->gis->form->get('dateAdded')->getValue()) . "</dateAdded>\n";
				$this->xml .= "<initiator>" . usercache::getInstance()->get($this->gis->form->get('initiator')->getValue())->getName() . "</initiator>\n";
				$this->xml .= "<profileName>" . $this->gis->form->get('profileName')->getValue() . "</profileName>\n";
				if($this->gis->form->get('dateAdded')->getValue() != $this->gis->form->get('dateUpdated')->getValue())
				{
					$this->xml .= "<updated>true</updated>";
					$this->xml .= "<dateUpdated>" . common::transformDateForPHP($this->gis->form->get('dateUpdated')->getValue()) . "</dateUpdated>\n";
					$this->xml .= "<owner>" . usercache::getInstance()->get($this->gis->form->get("owner")->getValue())->getName() . "</owner>";
				}
				$this->xml .= $this->gis->getID()?"<gistatus>true</gistatus>\n":"<gistatus>false</gistatus>";

				$this->xml .= "</gisSummary>";

				
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
			if(isset($_GET['profileType']))
			{
				if($_GET['profileType'] != 'all')
				{
					$datasetCompetitors = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis WHERE `profileType` = '" . $_GET['profileType'] . "' ORDER BY profileName ASC");
				}
				else 
				{
				$datasetCompetitors = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis ORDER BY profileName ASC");
				}
			}
			else
			{
				$datasetCompetitors = mysql::getInstance()->selectDatabase("gis")->Execute("SELECT * FROM gis ORDER BY profileName ASC");
			}

			while ($fieldsCompetitors = mysql_fetch_array($datasetCompetitors))
			{
				$this->add_output("<competitorList>");
				$this->add_output("<id>" . $fieldsCompetitors['id'] . "</id>");
				$this->add_output("<profileName>" . $fieldsCompetitors['profileName'] . "</profileName>\n");
				$this->add_output("<profileType>" .  translate::getInstance()->translate($fieldsCompetitors['profileType']) . "</profileType>\n");
				$this->add_output("<dateUpdated>" . common::transformDateForPHP($fieldsCompetitors['dateUpdated']) . "</dateUpdated>\n");
				$this->add_output("<initiator>" . usercache::getInstance()->get($fieldsCompetitors['initiator'])->getName() . "</initiator>\n");
				$this->add_output("</competitorList>");
			}
		}

		
		$this->add_output("</gisHome>");
		
		
		
		$this->output('./apps/gis/xsl/home.xsl');

	}

}

?>