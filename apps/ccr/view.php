<?php

require 'lib/manipulate.php';

class view extends manipulate
{
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		$this->setPrintCss("/css/ccr.css");
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		
		if (!isset($_REQUEST['id']))
		{
			if (isset($_REQUEST['action']))
			{
				// get the report id from the action.
				
				$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("SELECT id, type, parentId FROM action WHERE id = '%u'", $_REQUEST['action']));
		
				$fields = mysql_fetch_array($dataset);
		
				switch ($fields['type'])
				{
					case 'ccr':
						
						$_REQUEST['id'] = $fields['parentId'];
						break;
					
					case 'material':
						
						$parent = $fields['parentId'];
						$materialActionsDataset = mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("SELECT id, ccrId FROM material WHERE id ='%u'", $parent));
						
						$materialFields = mysql_fetch_array($materialActionsDataset);
						$_REQUEST['id'] = $materialFields['ccrId'];
						break;
						
					default:
						
						die("report could not be found from action id");
				}
			}
			elseif (isset($_REQUEST['material']))
			{
				// get the report id from the material.
				
				$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute(sprintf("SELECT id, ccrId FROM material WHERE id = '%u'", $_REQUEST['material']));
		
				$fields = mysql_fetch_array($dataset);
		
				$_REQUEST['id'] = $fields['ccrId'];
			}
			else 
			{
				//page::message()
				die("Report not found");
			}
		}
		
		
		
		
		
		$this->add_output('<ccrView id="' . (isset($_REQUEST['id']) ? $_REQUEST['id'] : '') . '">');
		
		session::clear();
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_REQUEST['action']))
		{
			$this->setPageAction("report");
		}
		
		
		$this->ccr = new ccr();
		
		if (isset($_REQUEST['id']))
		{
			$this->ccr->load($_REQUEST['id']);
			
			if ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_REQUEST['action']) || isset($_REQUEST['material'])))
			{				
				// loop through report actions

				if (isset($_REQUEST['action']))
				{
					for($action=0; $action < count($this->ccr->getActions()); $action++)
					{
						if ($this->ccr->getAction($action)->form->getDatabaseId() == $_REQUEST['action'])
						{
							$this->pageAction = "reportaction_$action";
							break;
						}
					}
				}
				
				
				
				// loop through material actions

				for($material=0; $material < count($this->ccr->getMaterials()); $material++)
				{
					if (isset($_REQUEST['material']))
					{
						if ($this->ccr->getMaterial($material)->form->getDatabaseId() == $_REQUEST['material'])
						{
							$this->pageAction = "material_" . $material;
							break;
						}
					}
					
					if (isset($_REQUEST['action']))
					{
						for($action=0; $action < count($this->ccr->getMaterial($material)->getActions()); $action++)
						{
							if ($this->ccr->getMaterial($material)->getAction($action)->form->getDatabaseId() == $_REQUEST['action'])
							{
								$this->pageAction = "materialaction_" . $material . "_" . $action;
								break;
							}
						}
					}
				}
			}
		}
		
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// bodge tastic
			if (strstr($this->getPageAction(), "delegate") && isset($_POST['personResponsible']))
			{
				$this->delegateForm->processPost();
				
				
				if ($this->delegateForm->validate())
				{
					if (strstr($this->getPageAction(), "report")) 
					{
						$databaseId = $this->ccr->getAction($this->getReportActionId())->form->getDatabaseId();

						
						$this->ccr->getAction($this->getReportActionId())->form->get("personResponsible")->setValue($this->delegateForm->get('personResponsible')->getValue());
						
						$this->setPageAction("reportaction_" . $this->getReportActionId());
					}
					else 
					{
						$databaseId = $this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->getDatabaseId();
					
						
						$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->get("personResponsible")->setValue($this->delegateForm->get('personResponsible')->getValue());
						
						$this->setPageAction("materialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
					}
					
					
					mysql::getInstance()->selectDatabase("CCR")->Execute("UPDATE action SET `personResponsible`='" . $this->delegateForm->get('personResponsible')->getValue() . "' WHERE id='" . $databaseId . "'");
					
					$this->ccr->addLog("Action delegated to " . $_POST['personResponsible']);
					
				
					
					// email the new owner
					$dom = new DomDocument;
					$dom->loadXML("<delegateAction><action>" . $databaseId . "</action><from>" . currentuser::getInstance()->getName() . "</from><completionDate>-</completionDate>" . (strlen($_POST['message']) > 1 ? "<message>" . $_POST['message'] . "</message>" : "") . "</delegateAction>");
			
			        // load xsl
			        $xsl = new DomDocument;
			        $xsl->load("/home/live/apps/ccr/xsl/email.xsl");
			        
			        // transform xml using xsl
			        $proc = new xsltprocessor;
			        $proc->importStyleSheet($xsl);
			
			   		$email = $proc->transformToXML($dom);
					
					email::send(usercache::getInstance()->get($this->delegateForm->get("personResponsible")->getValue())->getEmail(), "intranet@scapa.com", translate::getInstance()->translate("ccr_action"), $email);
					

						
				}
			
			}
		}		
		
	
		$this->add_output($this->doStuffAndShow("readOnly"));
		
		
		if ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->isComplete())
		{	
			$this->add_output(sprintf('<editToggle report="%s" currentLocation="%s" />',
				$this->ccr->getId(),
				$this->getPageAction() == "print" ? "report" : $this->getPageAction()
			));
		}
		
		$this->add_output(sprintf('<printControl print="%s" />',
			$this->getPageAction() == "print" ? 'true' : 'false'
		));
		
		$this->add_output($this->buildMenu());
		
		
		// show form	
		$this->add_output("</ccrView>");
	
		$this->output('./apps/ccr/xsl/view.xsl');
	}
	

	
}

?>