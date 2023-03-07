<?php
/**
 * @package apps
 * @subpackage imageGallery
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 21/01/2009
 */
class requestRemoval extends page
{
	private $thumbId;
	private $imageId;
	private $albumId;
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('Gallery');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/gallery/xml/menu.xml");
		
		// Check to see if all parameters are passed over and in good condition!
		if(isset($_GET['photoId']) && $_GET['photoId'] != "") 
		{
			$this->mode = "Photo";
		}
		elseif(isset($_GET['commentId']) && $_GET['commentId'] != "") 
		{
			$this->mode = "Comment";
		}
		else 
		{
			page::redirect("./index?");
		}
		

		$this->add_output("<requestRemoval" . $this->mode . ">");
		
		$snapins = new snapinGroup('usermanager_left');
		$snapins->register('apps/gallery', 'uploadpictures', true, true);
		$snapins->register('apps/gallery', 'gallerylist', true, true);
		$snapins->register('apps/gallery', 'latestpictures', true, true);
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");

		if($this->mode == "Photo")
		{
			$datasetImage = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE fileName = '" . $_GET['photoId'] . "'");
			
			if(mysql_num_rows($datasetImage) != 1)
			{
				page::redirect("./index?");
			}
			
			$fieldsImage=mysql_fetch_array($datasetImage);

			$this->add_output("<extension>" . $fieldsImage['extension'] . "</extension>");
			$this->add_output("<fileName>" . $fieldsImage['fileName'] . "</fileName>");
			$this->add_output("<imageId>" . $fieldsImage['id'] . "</imageId>");
		}
		
		$this->defineForm();
		
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
			$this->form->processPost();
			
			if($this->form->validate())
			{
				// entering funness!
				if($this->mode == "Photo")
				{
					$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM images WHERE fileName = '" . $_GET['photoId'] . "'");
					$fields = mysql_fetch_array($dataset);
					
					$this->getEmailNotification($this->mode, $fields['fileName'], $fields['extension'], $this->form->get("removalReason")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName(), $fields['galleryId'], "", "");
				}
				else 
				{
					$dataset = mysql::getInstance()->selectDatabase("imageGallery")->Execute("SELECT * FROM `log` JOIN `images` ON `images`.id = `log`.imageId WHERE `log`.id =" . $_GET['commentId']);
					$fields = mysql_fetch_array($dataset);
					
					$this->getEmailNotification($this->mode, $fields['fileName'], $fields['extension'], $this->form->get("removalReason")->getValue(), usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName(), $fields['galleryId'], $fields['comments'], $fields['id']);
				}
				
				page::redirect("./viewImage?albumId=" . $fields['galleryId'] . "&photoId=" . $fields['fileName'] . "&remove=" . $this->mode);
			}
			else 
			{
				$this->add_output("<error>1</error>");
			}
		}
		
		$this->add_output($this->form->output());
		
		$this->add_output("</requestRemoval" . $this->mode . ">");
		$this->output('./apps/gallery/xsl/requestRemoval.xsl');
	}
	
	/**
	 * Creates the form and all the controls.
	 *
	 */
	private function defineForm()
	{	
		$this->form = new form("requestRemoval");
		
		$removalGroup = new group("removalGroup");
		$removalGroup->setBorder(false);
		
		$submitGroup = new group("submitGroup");
		$submitGroup->setBorder(false);
		
		// Shown fields
		$userRO = new readonly("userRO");
		$userRO->setValue(currentuser::getInstance()->getName());
		$userRO->setVisible(true);
		$userRO->setRowTitle("User Name");
		$userRO->setGroup("commentGroup");
		$removalGroup->add($userRO);
		
		$removalReason = new textarea("removalReason");
		$removalReason->setGroup("removalGroup");
		$removalReason->setDataType("text");
		$removalReason->setRowTitle("{TRANSLATE:your_reason}");
		$removalReason->setRequired(true);
		$removalReason->setVisible(true);
		$removalReason->setErrorMessage("Enter a reason");
		$removalReason->setTable("log");
		$removalGroup->add($removalReason);
		
		$submit = new submit("Submit");
		$submitGroup->add($submit);
		
		$this->form->add($removalGroup);
		$this->form->add($submitGroup);
	}
		
	
	public function getEmailNotification($type, $fileName,$extension,$removalReason,$user,$albumId, $comment, $commentId)
	{
		// newAction, email the owner
		$dom = new DomDocument;

		$dom->loadXML("<requestRemoval" . $type . "><fileName>" . $fileName . "</fileName><extension>" . $extension . "</extension><removalReason>" . $removalReason . "</removalReason><albumId>" . $albumId . "</albumId><user>" . $user . "</user><comment>" . $comment . "</comment><commentId>" . $commentId . "</commentId></requestRemoval" . $type . ">");
		
		// load xsl
		$xsl = new DomDocument;
		$xsl->load("./apps/gallery/xsl/email.xsl");

		// transform xml using xsl
		$proc = new xsltprocessor;
		$proc->importStyleSheet($xsl);

		$email = $proc->transformToXml($dom);

		email::send("intranet@scapa.com", "intranet@scapa.com", "Request Removal of " . $type, "$email", "");
		
		return true;
	}
}

?>