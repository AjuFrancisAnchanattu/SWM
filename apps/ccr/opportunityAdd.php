 <?php

require 'lib/opportunityNew.php';

class opportunityAdd extends page
{
	private $opportunityForm, $actionForm;
	private $header;
	private $opportunityNew;
	
	private $pageAction;
	
	
	
	private $actionId;
	private $opportunityId;
	
	
	private $valid = false;
	

	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/menu.xml");

		$this->add_output("<opportunityAdd>");
		

		
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			session::clear();
			$this->setPageAction("opportunity");
		}
		

		
		$this->opportunityNew = new opportunityNew();
		
		
				

		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			switch($this->getLocation())
			{
				case 'opportunity':
					
					$this->opportunityNew->attachments->processPost();
					$this->opportunityNew->form->processPost();
					break;
					
				case 'action':
					$this->opportunityNew->getAction($this->getActionId())->form->processPost();
					break;
			}
			
			
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->opportunityNew->validate();
			}
		}
		
		
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				$this->opportunityNew->save();
			}
			else
			{
				$this->add_output("<error />");
				
				// find first error.
				
				if (!$this->opportunityNew->form->isValid())
				{
					$this->setPageAction("opportunity");
				}
				else 
				{
					for ($i=0; $i < count($this->opportunityNew->getActions()); $i++)
					{
						if (!$this->opportunityNew->getAction($i)->form->isValid())
						{
							$this->setPageAction("action_".$i);
							break;
						}
					}
				}
				
			}
		}
		
		
		switch($this->getPageAction())
		{
			case 'opportunity':
			
				$this->setLocation("opportunity");
			
				$this->add_output("<attachmentControl>");
				$this->opportunityNew->attachments->get('attachment')->setNextAction('opportunity');
				$this->add_output($this->opportunityNew->attachments->output());
				
				if (isset($_SESSION['apps'][$GLOBALS['app']]['attachment']))
				{
					for ($i=0; $i < count($_SESSION['apps'][$GLOBALS['app']]['attachment']); $i++)
					{
						$file = $_SESSION['apps'][$GLOBALS['app']]['attachment'][$i];
						$size = sprintf("%u", ceil(filesize($file)/1024));
						
						$fileSplit = explode("/", $file);
						
						$this->add_output("<loadedAttachment size=\"". $size ."\">" . $fileSplit[count($fileSplit)-1] . "</loadedAttachment>");
					}
				}
					
				$this->add_output("</attachmentControl>");
				
				
				$this->add_output("<actionControl />");
				
				
				$this->add_output("<ccrReport>");
				$this->add_output($this->opportunityNew->form->output());
				$this->add_output("</ccrReport>");
				break;
				
			case 'addaction':
			
				$this->setLocation("action");
				$this->setActionId($this->opportunityNew->addAction());
				
				$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
			
				$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
				$this->add_output($this->opportunityNew->getAction($this->getActionId())->form->output());
				$this->add_output("</ccrAction>");
				break;
				
			case 'action':
			
				//$this->setLocation("material");
				//$this->setMaterialId();
				
				$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
				
				page::addDebug("Show action", __FILE__, __LINE__);
				
				$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
				$this->add_output($this->opportunityNew->getAction($this->getActionId())->form->output());
				$this->add_output("</ccrAction>");
				break;
				
		
			default:
			
				// some more custom hardcore actions
				
				if (preg_match("/^action_([0-9]+)$/", $this->getPageAction(), $match))
				{
					$this->setLocation("action");
					$this->setActionId($match[1]);
					
					$this->add_output("<actionControl id=\"".$this->getActionId()."\" />");
					
					page::addDebug("Show action", __FILE__, __LINE__);
					
					$this->add_output("<ccrAction id=\"".$this->getActionId()."\">");
					$this->add_output($this->opportunityNew->getAction($this->getActionId())->form->output());
					$this->add_output("</ccrAction>");
				}

				if (preg_match("/^removeaction_([0-9]+)$/", $this->getPageAction(), $match))
				{
					$this->opportunityNew->removeAction($match[1]);
					
					
					$this->setLocation("opportunity");
					
					$this->add_output("<attachmentControl />");
					$this->add_output("<actionControl />");
					
					$this->add_output("<ccrReport>");
					$this->add_output($this->opportunityNew->form->output());
					$this->add_output("</ccrReport>");
				}
						
		}
		
		$this->add_output("<opportunitynav valid=\"" . ($this->opportunityNew->form->isValid() ? 'true' : 'false') . "\" selected=\"". ($this->getLocation() == 'opportunity' ? 'true' : 'false') . "\">");
				
		for ($i=0; $i < count($this->opportunityNew->getActions()); $i++)
		{
			$this->add_output("<actionnav id=\"$i\" valid=\"" . ($this->opportunityNew->getAction($i)->form->isValid() ? 'true' : 'false') . "\" selected=\"". ($this->getLocation() == 'action' && $this->getActionId()==$i ? 'true' : 'false') . "\">");
				
			$this->add_output("</actionnav>");
			
		}
		
		$this->add_output("</opportunitynav>");
		
		

		
		// show form
		//$this->add_output($this->form->output());
		$this->add_output("</opportunityAdd>");
	
		$this->output('./apps/ccr/xsl/opportunityAdd.xsl');
	}
	
	
	function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'opportunity';
		}
		
		return $_SESSION['apps'][$GLOBALS['app']]['location'];
	}
	
	function setLocation($location)
	{
		$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
	}
	
	
	
	
	function getPageAction()
	{
		if (!isset($this->pageAction))
		{
			$this->pageAction = isset($_POST['action']) ? $_POST['action'] : "opportunity";
		}
		
		return $this->pageAction;
		
	}
	
	function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}
	
	
	
	function getActionId()
	{
		if (!isset($this->actionId))
		{
			$this->actionId = isset($_POST['actionId']) ? $_POST['actionId'] : "0";
		}
		
		return $this->actionId;
	}
	
	function setActionId($actionId)
	{
		$this->actionId = $actionId;
	}
}

?>