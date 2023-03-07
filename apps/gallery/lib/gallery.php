<?php
require 'galleryProcess.php';

/**
 * This is the Gallery Application.
 *
 * @package apps
 * @subpackage gallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 26/01/2009
 */
class gallery
{
	private $id;
	private $status;
	public $form;
	public $attachments;
	private $loadedFromDatabase = false;

	function __construct($loadFromSession=true)
	{
		$this->loadFromSession = $loadFromSession;

		if (isset($_SESSION['apps'][$GLOBALS['app']]['gallery']['loadedFromDatabase']))
		{
			page::addDebug("Checking loadedFromDatabase is being set!!",__FILE__,__LINE__);
			$this->loadedFromDatabase = true;		//checks if the gallery is loaded from the database
		}
		
		// Take the ID from the REQUEST before going to the session...
		if(isset($_REQUEST['id']))
		{
			$this->id = $_REQUEST['id'];
			
			die($this->id);
		}
		else 
		{
			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']))
			{
				$this->id = $_SESSION['apps'][$GLOBALS['app']]['id']; //checks if there is a gallery id in the session
			}	
		}

		$this->defineForm();

		if($this->loadFromSession) //catch the no load of the session data
		{
			$this->form->loadSessionData();
			//$this->form->processDependencies();
		}
		
		$this->loadSessionSections();
		
	}

	private function loadSessionSections()
	{
		//if (isset($_SESSION['apps'][$GLOBALS['app']]['evaluation']))
		//{
		//	$this->evaluation = new evaluation($this);
		//}
	}

	public function loadSessionSectionsAll()
	{
		//$this->evaluation = new evaluation($this);
		//die($this);
		//$this->conclusion = new conclusion($this);
	}

	public function load($id)
	{
		page::addDebug("loading gallery id=$id", __FILE__, __LINE__);

		if (!is_numeric($id))
		{
			return false;
		}

		
		$this->id = $id;

		$this->form->setStoreInSession(true);

		$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = $id");

		if (mysql_num_rows($dataset) == 1)
		{

			$this->loadedFromDatabase = true;
			$_SESSION['apps'][$GLOBALS['app']]['gallery']['loadedFromDatabase'] = true;

			$fields = mysql_fetch_array($dataset);

			$loadedFromSavedForms = false;
			$this->id = $fields['id'];
			$_SESSION['apps'][$GLOBALS['app']]['id'] = $this->id;

			
			foreach ($fields as $key => $value)			//puts each value of each field into the IJF form
			{
				if ($this->form->get($key))
				{
					$this->form->get($key)->setValue($value);
				}
			}
			
			if($fields['permissionType'] == "site")
			{
				$datasetPerms = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM permissions WHERE albumId = $id");
				$fieldsPerms = mysql_fetch_array($datasetPerms);
				
				foreach ($fieldsPerms as $key => $value)			//puts each value of each field into the IJF form
				{
					if ($this->form->get($key))
					{
						$this->form->get($key)->setValue($value);
					}
				}
			}
			
			//$this->form->populate($fields);

			$this->form->putValuesInSession();		//puts all the form values into the sessions

			$this->form->processDependencies();
		}
		else
		{
			page::addDebug("this is to check if loadedfromdatabase is showing false", __FILE__, __LINE__);
			
			
			return false;
			
			
		}

		return true;
	}


	public function getID()
	{
		return $this->form->get("id")->getValue();
	}


	public function addSection($section)
	{
		switch ($section)
		{
			case 'gallery':
				$this->gallery = new gallery($this);
				break;

			default: die('addSection() unknown $section');
		}
	}



	public function validate()
	{
		$valid = true;
		if(!isset($_GET["sfID"])){

			if (!$this->form->validate())
			{
				$valid = false;
			}

		}

		return $valid;
	}

	public function save($process)
	{
		page::addDebug("Saving gallery process: ".$process,__FILE__,__LINE__);

		
		switch ($process)
		{
			case 'gallery':

				
				//$this->determineStatus();

				if ($this->loadedFromDatabase)
				{
					$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = " . $this->id);
					$fields = mysql_fetch_array($dataset);
					
					$this->form->get("id")->setValue($this->id);
					$this->form->get("albumId")->setValue($this->id);
					$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
					$this->form->get("initiatedDate")->setValue($fields['initiatedDate']);
					$this->form->get("owner")->setValue($fields['owner']);
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("BEGIN");
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM permissions WHERE albumId=" . $this->id);	
					mysql::getInstance()->selectDatabase("membership")->Execute("DELETE FROM permissions WHERE permission = 'albumId_" . $this->id . "'");
					
					if($this->form->get('permissionType')->getValue() == "site")
					{
						// Site permissions
						$this->form->get("permissionPersonnelNTlogon")->setValue("");
						
						mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO permissions " . $this->form->generateInsertQuery("permissions"));
					}
					else 
					{
						$userPermissions = explode(",",$this->form->get("permissionPersonnelNTlogon")->getValue());
						
						foreach ($userPermissions as $value)
						{
							$sqlInsert .= "('" . $value . "', 'albumId_" . $this->form->get("id")->getValue() . "'),";
						}
						$sqlInsert = "INSERT INTO permissions (NTLogon,permission) VALUES " . substr($sqlInsert,0,-1) . ";";
						
						mysql::getInstance()->selectDatabase("membership")->Execute($sqlInsert);
					}
					
					// update
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("UPDATE gallery " . $this->form->generateUpdateQuery("album") . " WHERE id= " . $this->id . "");

					// add to log
					$this->addLog(translate::getInstance()->translate("Gallery Details Updated by ") . " - " . currentuser::getInstance()->getName() . "");
				}
				else
				{
					$this->form->get("owner")->setValue(currentuser::getInstance()->getNTLogon());
					$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
					$this->form->get("initiatedDate")->setValue(common::nowDateForMysql());
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("BEGIN");
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO gallery " . $this->form->generateInsertQuery("album"));
					
					$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT id FROM gallery ORDER BY id DESC LIMIT 1");
					$fields=mysql_fetch_array($dataset);
					
					$this->id = $fields['id'];
					
					$this->form->get("id")->setValue($fields['id']);
					$this->form->get("albumId")->setValue($fields['id']);
					
					// Set the permissions here
					if($this->form->get('permissionType')->getValue() == "site")
					{
						// Site permissions
						mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO permissions " . $this->form->generateInsertQuery("permissions"));
					}
					else 
					{
						$userPermissions = explode(",",$this->form->get("permissionPersonnelNTlogon")->getValue());
						foreach ($userPermissions as $value)
						{
							$sqlInsert .= "('" . $value . "', 'albumId_" . $this->form->get("id")->getValue() . "'),";
						}
						$sqlInsert = "INSERT INTO permissions (NTLogon,permission) VALUES " . substr($sqlInsert,0,-1) . ";";
						
						mysql::getInstance()->selectDatabase("membership")->Execute($sqlInsert);
					}
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("COMMIT");
					
					$this->getEmailNotification($this->form->get("owner")->getValue(), $this->form->get("id")->getValue());

					$this->addLog(translate::getInstance()->translate("Gallery added by ") . " " . page::xmlentities(usercache::getInstance()->get($this->form->get("owner")->getValue())->getName()) . "");

					page::redirect("./index?addRequestSent=true");
				}

				break;
		}

		page::redirect("/apps/gallery/index");		//redirects the page back to the summary

	}

	


	public function addLog($action, $comment="")
	{
		mysql::getInstance()->selectDatabase("imageGallery")->Execute(sprintf("INSERT INTO log (albumId, NTLogon, action, dateTime, comments) VALUES (%u, '%s', '%s', '%s', '%s')",
		$this->getID(),
		addslashes(currentuser::getInstance()->getNTLogon()),
		addslashes($action),
		common::nowDateTimeForMysql(),
		$comment
		));
	}

	public function getCreator()
	{
		return $this->form->get("creator")->getValue();
	}


	public function defineForm()
	{
		$this->form = new form("addNewAlbum");
		
		$albumGroup = new group("albumGroup");
		$albumGroup->setBorder(false);
		
		$permissionsGroup = new group("permissionsGroup");
		$permissionsGroup->setBorder(false);
		
		$permissionsSite = new group("permissionsSite");
		$permissionsSite->setBorder(false);
		
		$permissionsPersonnel = new group("permissionsPersonnel");
		$permissionsPersonnel->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		// Hidden Fields
		
		$id = new textbox("id");
		$id->setVisible(false);
		$id->setGroup("albumGroup");
		$id->setTable("album");
		$id->setRowTitle("id");
		$albumGroup->add($id);
		
		$owner = new textbox("owner");
		$owner->setVisible(false);
		$owner->setGroup("albumGroup");
		$owner->setTable("album");
		$owner->setRowTitle("owner");
		$albumGroup->add($owner);
		
		$initiatedDate = new textbox("initiatedDate");
		$initiatedDate->setVisible(false);
		$initiatedDate->setGroup("albumGroup");
		$initiatedDate->setTable("album");
		$initiatedDate->setRowTitle("initiatedDate");
		$albumGroup->add($initiatedDate);
		
		$updatedDate = new textbox("updatedDate");
		$updatedDate->setVisible(false);
		$updatedDate->setGroup("albumGroup");
		$updatedDate->setTable("album");
		$updatedDate->setRowTitle("updatedDate");
		$albumGroup->add($updatedDate);
		
		// Show fields
		$processRO = new readonly("processRO");
		$processRO->setVisible(true);
		$processRO->setValue("Submitting this form will sent an email to the IT department asking for photos to be added. You will be contacted shortly.");
		$processRO->setRowTitle("Instructions");
		$processRO->setGroup("albumGroup");
		$albumGroup->add($processRO);
		
		
		$albumName = new textbox("albumName");
		$albumName->setGroup("albumGroup");
		$albumName->setRowTitle("Album Name");
		$albumName->setDataType("string");
		$albumName->setLength(100);
		$albumName->setRequired(true);
		$albumName->setVisible(true);
		$albumName->setErrorMessage("enter_album_name");
		$albumName->setTable("album");
		$albumGroup->add($albumName);
		
		$description = new textarea("description");
		$description->setGroup("albumGroup");
		$description->setDataType("text");
		$description->setRowTitle("Description");
		$description->setRequired(true);
		$description->setVisible(true);
		$description->setErrorMessage("Enter a description");
		$description->setTable("album");
		$albumGroup->add($description);
		
		
		// Set up the permissions dependency.
		$permissionType = new radio("permissionType");
		$permissionType->setGroup("permissionsGroup");
		$permissionType->setDataType("string");
		$permissionType->setLength(10);
		$permissionType->setArraySource(array(array('value' => 'site', 'display' => 'Site'),array('value' => 'personnel', 'display' => 'Personnel')));
		$permissionType->setRowTitle("set_permissions_for");
		$permissionType->setRequired(true);
		$permissionType->setTable("album");
		
		// Dependency
		$permissionsForSite = new dependency();
		$permissionsForSite->addRule(new rule('permissionsGroup', 'permissionType', 'site'));
		$permissionsForSite->setGroup('permissionsSite');
		$permissionsForSite->setShow(true);
		
		$permissionsForPersonnel = new dependency();
		$permissionsForPersonnel->addRule(new rule('permissionsGroup', 'permissionType', 'personnel'));
		$permissionsForPersonnel->setGroup('permissionsPersonnel');
		$permissionsForPersonnel->setShow(true);
		
		$permissionType->addControllingDependency($permissionsForSite);
		$permissionType->addControllingDependency($permissionsForPersonnel);
		$permissionsGroup->add($permissionType);
		

		// Set the personnel permissions dependency group
		$permissionPersonnelRO = new readonly("permissionPersonnelRO");
		$permissionPersonnelRO->setGroup("permissionsPersonnel");
		$permissionPersonnelRO->setVisible(true);
		$permissionPersonnelRO->setLabel("Permissions for Personnel");
		$permissionPersonnelRO->setRowTitle("Permissions");
		$permissionPersonnelRO->setValue("{TRANSLATE:permission_for_personnel_ro}");
		$permissionsPersonnel->add($permissionPersonnelRO);
		
		$permissionPersonnelNTlogon = new multiNTLogon("permissionPersonnelNTlogon");
		$permissionPersonnelNTlogon->setGroup("permissionsPersonnel");
		$permissionPersonnelNTlogon->setLength(250);
		$permissionPersonnelNTlogon->setTable("album");
		$permissionPersonnelNTlogon->setDataType("text");
		$permissionPersonnelNTlogon->setRowTitle("add_user");
		$permissionPersonnelNTlogon->setRequired(false);
		$permissionsPersonnel->add($permissionPersonnelNTlogon);

		

		
		
		// Set the locale permissions dependency group
		$permissionSiteRO = new readonly("permissionSiteRO");
		$permissionSiteRO->setGroup("permissionsSite");
		$permissionSiteRO->setVisible(true);
		$permissionSiteRO->setLabel("Permissions for Site");
		$permissionSiteRO->setRowTitle("Permissions");
		$permissionSiteRO->setValue("{TRANSLATE:permissions_text}");
		$permissionsSite->add($permissionSiteRO);
		
		$ashton = new checkbox("ashton");
		$ashton->setGroup("permissionsGroup");
		$ashton->setRowTitle("Ashton");
		$ashton->setTable("permissions");
		$permissionsSite->add($ashton);
		
		$barcelona = new checkbox("barcelona");
		$barcelona->setGroup("permissionsGroup");
		$barcelona->setRowTitle("Barcelona");
		$barcelona->setTable("permissions");
		$permissionsSite->add($barcelona);
		
		$bellegarde = new checkbox("bellegarde");
		$bellegarde->setGroup("permissionsGroup");
		$bellegarde->setRowTitle("Bellegarde");
		$bellegarde->setTable("permissions");
		$permissionsSite->add($bellegarde);
		
		$dunstable = new checkbox("dunstable");
		$dunstable->setGroup("permissionsGroup");
		$dunstable->setRowTitle("Dunstable");
		$dunstable->setTable("permissions");
		$permissionsSite->add($dunstable);
		
		$ghislarengo = new checkbox("ghislarengo");
		$ghislarengo->setGroup("permissionsGroup");
		$ghislarengo->setRowTitle("Ghislarengo");
		$ghislarengo->setTable("permissions");
		$permissionsSite->add($ghislarengo);
		
		$iberica = new checkbox("iberica");
		$iberica->setGroup("permissionsGroup");
		$iberica->setRowTitle("Iberica");
		$iberica->setTable("permissions");
		$permissionsSite->add($iberica);
		
		$mannheim = new checkbox("mannheim");
		$mannheim->setGroup("permissionsGroup");
		$mannheim->setRowTitle("Mannheim");
		$mannheim->setTable("permissions");
		$permissionsSite->add($mannheim);
		
		$rorschach = new checkbox("rorschach");
		$rorschach->setGroup("permissionsGroup");
		$rorschach->setRowTitle("Rorschach");
		$rorschach->setTable("permissions");
		$permissionsSite->add($rorschach);
		
		$valence = new checkbox("valence");
		$valence->setGroup("permissionsGroup");
		$valence->setRowTitle("Valence");
		$valence->setTable("permissions");
		$permissionsSite->add($valence);
		
		$albumId = new textbox("albumId");
		$albumId->setVisible(false);
		$albumId->setTable("permissions");
		$albumId->setGroup("permissionsGroup");
		$permissionsGroup->add($albumId);
		
		$submit = new submit("Submit");
		$submitGroup->add($submit);
		
		$this->form->add($albumGroup);
		$this->form->add($permissionsGroup);
		$this->form->add($permissionsSite);
		$this->form->add($permissionsPersonnel);
		$this->form->add($submitGroup);
	}

	public function getEmailNotification($user, $albumId)
	{
				// newAction, email the owner
		$dom = new DomDocument;

		$dom->loadXML("<requestImageAdd><albumId>" . $albumId . "</albumId><user>" . $user . "</user></requestImageAdd>");
		
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/gallery/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		email::send("intranet@scapa.com", "intranet@scapa.com", "Request Image Add", "$email", "");
		
		return true;
	}
}

?>