<?php
/**
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */
class album extends page
{
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		$this->add_output("<addAlbum>");
		
		$snapins = new snapinGroup('usermanager_left');
		$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$this->defineForm();
		
		$this->loadedFromDatabase = false;
		
		if(isset($_GET["mode"]) && isset($_GET['albumId']))
		{
			$id = $_GET['albumId'];
			$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery WHERE id = $id");
			
			if(mysql_num_rows($dataset) == 1)
			{
				$this->loadedFromDatabase = true;
				$this->id = $_GET['albumId'];
			}
		}

		if($this->loadedFromDatabase)	
		{
			if($_GET['mode'] == "Edit")
			{
				$fields = mysql_fetch_array($dataset);
				
				foreach ($fields as $key => $value)
				{
					if ($this->form->get($key))
					{
						$this->form->get($key)->setValue($value);
					}
				}
				
				$datasetPermissions = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM permissions WHERE albumId = $this->id");
				$fieldsPermission = mysql_fetch_array($datasetPermissions);
				
				foreach ($fieldsPermission as $key => $value)
				{
					if ($this->form->get($key))
					{
						$this->form->get($key)->setValue($value);
					}
				}
				
							
			}
			else if($_GET['mode'] == "Delete")
			{
				$this->deleteAlbum($this-id);	
				page::redirect("./index");			
			}
		}			
			
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			if($this->form->validate())
			{
				if($this->loadedFromDatabase)
				{
					$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("BEGIN");
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("UPDATE gallery " . $this->form->generateUpdateQuery("album") . " WHERE id='" . $this->id . "'");
		
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("COMMIT");

					$this->form->get("albumId")->setValue($this->id);
					
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("UPDATE permissions " . $this->form->generateUpdateQuery("permissions") . "WHERE albumId = " . $this->id . "");
					
					
					
					//page::redirect("./index");
				}
				else 
				{
					$this->form->get("updatedDate")->setValue(common::nowDateForMysql());
					$this->form->get("initiatedDate")->setValue(common::nowDateForMysql());
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("BEGIN");
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO gallery " . $this->form->generateInsertQuery("album"));
					
					$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT id FROM gallery ORDER BY id DESC LIMIT 1");
					$fields=mysql_fetch_array($dataset);
					
					$this->form->get("albumId")->setValue($fields['id']);
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("INSERT INTO permissions " . $this->form->generateInsertQuery("permissions"));
					
					mysql::getInstance()->selectDatabase("imageGallery")->Execute("COMMIT");
					
					page::redirect("./index");
				}
			}
			else 
			{
				$this->add_output("<error>1</error>");
			}
		}
		
		$this->add_output($this->form->output());
		
		$this->add_output("</addAlbum>");
		$this->output('./apps/gallery/xsl/addAlbum.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("addNewAlbum");
		
		$albumGroup = new group("albumGroup");
		$albumGroup->setBorder(false);
		
		$permissionsGroup = new group("permissionsGroup");
		$permissionsGroup->setBorder(false);
		
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
		$owner->setValue(currentuser::getInstance()->getNTLogon());
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
		$warning = new readonly("warning");
		$warning->setGroup("albumGroup");
		$warning->setVisible(true);
		$warning->setRowTitle("warning");
		$warning->setValue("{TRANSLATE:album_warning}");
		$albumGroup->add($warning);
		
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
		
		
		// Set the locale permissions
		$albumId = new textbox("albumId");
		$albumId->setVisible(false);
		$albumId->setDataType("number");
		$albumId->setGroup("permissionsGroup");
		$albumId->setTable("permissions");
		$albumId->setRowTitle("albumId");
		$permissionsGroup->add($albumId);
		
		$permissionRO = new readonly("warning");
		$permissionRO->setGroup("permissionsGroup");
		$permissionRO->setVisible(true);
		$permissionRO->setLabel("Permissions");
		$permissionRO->setRowTitle("Permissions");
		$permissionRO->setValue("{TRANSLATE:permissions_text}");
		$permissionsGroup->add($permissionRO);
		
		$ashton = new checkbox("ashton");
		$ashton->setGroup("permissionsGroup");
		$ashton->setRowTitle("Ashton");
		$ashton->setTable("permissions");
		$permissionsGroup->add($ashton);
		
		$barcelona = new checkbox("barcelona");
		$barcelona->setGroup("permissionsGroup");
		$barcelona->setRowTitle("Barcelona");
		$barcelona->setTable("permissions");
		$permissionsGroup->add($barcelona);
		
		$bellegarde = new checkbox("bellegarde");
		$bellegarde->setGroup("permissionsGroup");
		$bellegarde->setRowTitle("Bellegarde");
		$bellegarde->setTable("permissions");
		$permissionsGroup->add($bellegarde);
		
		$dunstable = new checkbox("dunstable");
		$dunstable->setGroup("permissionsGroup");
		$dunstable->setRowTitle("Dunstable");
		$dunstable->setTable("permissions");
		$permissionsGroup->add($dunstable);
		
		$ghislarengo = new checkbox("ghislarengo");
		$ghislarengo->setGroup("permissionsGroup");
		$ghislarengo->setRowTitle("Ghislarengo");
		$ghislarengo->setTable("permissions");
		$permissionsGroup->add($ghislarengo);
		
		$iberica = new checkbox("iberica");
		$iberica->setGroup("permissionsGroup");
		$iberica->setRowTitle("Iberica");
		$iberica->setTable("permissions");
		$permissionsGroup->add($iberica);
		
		$mannheim = new checkbox("mannheim");
		$mannheim->setGroup("permissionsGroup");
		$mannheim->setRowTitle("Mannheim");
		$mannheim->setTable("permissions");
		$permissionsGroup->add($mannheim);
		
		$rorschach = new checkbox("rorschach");
		$rorschach->setGroup("permissionsGroup");
		$rorschach->setRowTitle("Rorschach");
		$rorschach->setTable("permissions");
		$permissionsGroup->add($rorschach);
		
		$valence = new checkbox("valence");
		$valence->setGroup("permissionsGroup");
		$valence->setRowTitle("Valence");
		$valence->setTable("permissions");
		$permissionsGroup->add($valence);
		
		$submit = new submit("Submit");
		$submitGroup->add($submit);
		
		$this->form->add($albumGroup);
		$this->form->add($permissionsGroup);
		$this->form->add($submitGroup);
	}
	
	function deleteAlbum($id)
	{
		$imageRootDir = "apps/gallery/images/";

		$datasetDeleteImages = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE galleryId = " . $id);
		
		while($fieldsDeleteImages = mysql_fetch_array($datasetDeleteImages))
		{
			$deleteImage = $fieldsDeleteImages['id'] . "." . $fieldsDeleteImages['extension'];
			
			unlink($imageRootDir . "large/" . $deleteImage);
			unlink($imageRootDir . "medium/" . $deleteImage);
			unlink($imageRootDir . "small/" . $deleteImage);
		}

		mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM images WHERE galleryId=" . $this->id);	
		mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM permissions WHERE albumId=" . $this->id);	
		mysql::getInstance()->selectDatabase("imageGallery")->Execute("DELETE FROM gallery WHERE id=" . $this->id);	
	}
}

?>