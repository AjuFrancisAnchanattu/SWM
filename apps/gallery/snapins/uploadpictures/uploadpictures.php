<?php
/**
 * This is a snapin to do with the User Manager.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 * @todo This currently does nothing, so make it do something.
 * @todo Give the snapin better documention.
 */
class uploadpictures extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("Upload Pictures"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['report']))
		{
			// get anything posted by the form
			
			if ($_POST['report'] != '')
			{
				$this->getEmailNotification(currentuser::getInstance()->getNTLogon(), $_POST['report']);
				
				page::redirect("/apps/gallery/index?addRequestSent=true");
			}
		}
	}
	
	public function output()
	{		
		$this->xml .= "<uploadpictures>";
		
		
		
		$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM gallery ORDER BY gallery.updatedDate DESC LIMIT 5");
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<galleryRow>";

			if(currentuser::getInstance()->isAdmin() || usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getNTLogon() == $fields['owner'])
			{
				$this->xml .= "<id>" . $fields['id'] . "</id>";
				$this->xml .= "<albumName>" . substr($fields['albumName'], 0, 15) . "...</albumName>";
			}
			elseif($fields['permissionType'] == "site")
			{
				$datasetPermissions = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM permissions WHERE albumId = " . $fields['id'] . "");
				$fieldsPermissions = mysql_fetch_array($datasetPermissions);
				
				if($fieldsPermissions[strtolower(currentuser::getInstance()->getSite())] == "on")
				{
					$this->xml .= "<id>" . $fields['id'] . "</id>";
					$this->xml .= "<albumName>" . substr($fields['albumName'], 0, 15) . "...</albumName>";
				}
				
				
			}
			elseif($fields['permissionType'] == "personnel")
			{
				$datasetPermissions = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM permissions WHERE NTLogon='" . usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getNTLogon() . "' AND permission = 'albumId_" . $fields['id'] . "'");
				
				if(mysql_num_rows($datasetPermissions) > 0)
				{
					$this->xml .= "<id>" . $fields['id'] . "</id>";
					$this->xml .= "<albumName>" . substr($fields['albumName'], 0, 15) . "...</albumName>";
				}
			}
			
				
			$this->xml .= "</galleryRow>";
		}
		
		
		$this->xml .= "</uploadpictures>";
		
		return $this->xml;
	}

	public function getEmailNotification($user, $albumId)
	{
				// newAction, email the owner
		$dom = new DomDocument;

		$dom->loadXML("<requestMoreImageAdd><albumId>" . $albumId . "</albumId><user>" . $user . "</user></requestMoreImageAdd>");
		
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