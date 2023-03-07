<?php

require 'ccr.php';

class manipulate extends page
{
	protected $ccr;
	
	protected $pageAction;
	
	
	protected $reportActionId;
	
	protected $materialId;
	protected $materialActionId;
	
	protected $technicalId;
	
	protected $opportunityId;
	protected $opportunityActionId;
	
	
	
	protected $delegateForm;
	
	
	
	protected $valid = false;
	
	
	function __construct()
	{
		// call page constructor
		parent::__construct();
		
		$this->defineDelegateForm();
	}
		
	
	// process form submissions
	
	public function processPost()
	{
		// process request
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			// get anything posted by the form
						
			switch($this->getLocation())
			{
				case 'report':
					
					//$this->ccr->attachments->processPost();
					$this->ccr->form->processPost();
					$this->ccr->updateCurrencyLegend();
					break;
					
				case 'reportaction':
			
					if (strstr($this->getPageAction(), "delegate"))
					{
						$this->delegateForm->processPost();
						page::addDebug("Processed post", __FILE__, __LINE__);
					}
					else
					{
						$this->ccr->getAction($this->getReportActionId())->form->processPost();
					}
					
					// bodge! we unset the page action in case there is an attachment so it can set the correct pageAction
					unset($this->pageAction);
					
					break;
					
					
				case 'material':
					$this->ccr->getMaterial($this->getMaterialId())->form->processPost();
					$this->ccr->getMaterial($this->getMaterialId())->processHierarchy();
					break;
					
				
				case 'technical':
					$this->ccr->getTechnical($this->getTechnicalId())->form->processPost();
					//$this->ccr->getTechnical($this->getTechnicalId())->processHierarchy();
					break;
				
				case 'materialaction':
					
					if (strstr($this->getPageAction(), "delegate"))
					{
						$this->delegateForm->processPost();
					}
					else
					{
						$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->processPost();
					}
					
					// bodge! we unset the page action in case there is an attachment so it can set the correct pageAction
					unset($this->pageAction);
					
					break;
					
				
				case 'opportunity':
					
					$this->ccr->getMaterial($this->getMaterialId())->getOpportunity($this->getOpportunityId())->form->processPost();
					break;
					
				case 'opportunityaction':
					
					$this->ccr->getMaterial($this->getMaterialId())->getOpportunity($this->getOpportunityId())->getAction($this->getOpportunityActionId())->form->processPost();
					break;
					
			}
			
			$this->ccr->form->processDependencies();
		}
	}
	
	
	
	public function validate()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['validate']) && $_POST['validate']=='true')
			{
				$this->valid = $this->ccr->validate();
			}
		}
		elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_REQUEST['offline']))
		{
			$this->valid = $this->ccr->validate();
		}
		
		
		
		if ($this->getPageAction() == "submit")
		{
			if ($this->valid)
			{
				page::addDebug("valid", __FILE__, __LINE__);
				$this->ccr->save();
				
				
				//
				$this->redirect("/apps/ccr/index?id=" . $this->ccr->getId());
			}
			else
			{
				$this->add_output("<error />");
				
				//
				// find first error and set the page action to go to the first error. helpful style
				//
				
				// we use the found variable so that we limit any pointless loopage and make sure we stop at the first instance
				$found = false;
				
				// check highest level report
				if (!$this->ccr->form->isValid())
				{
					$this->setPageAction("report");
					$found = true;
				}
				
				// technical enquiries				
				if (!$found)
				{
					for ($technical=0; $technical < count($this->ccr->getTechnicals()); $technical++)
					{
						if (!$this->ccr->getTechnical($technical)->form->isValid())
						{
							$this->setPageAction("technical_".$technical);
							$found = true;
							break;
						}
					}
				}
				
				
				// report actions
				if (!$found)
				{
					for ($action=0; $action < count($this->ccr->getActions()); $action++)
					{
						if (!$this->ccr->getAction($action)->form->isValid())
						{
							$this->setPageAction("reportaction_".$action);
							$found = true;
							break;
						}
					}
				}
				
				// materials, which also have actions themselves
				if (!$found)
				{
					for ($material=0; $material < count($this->ccr->getMaterials()); $material++)
					{
						if (!$this->ccr->getMaterial($material)->form->isValid())
						{
							$this->setPageAction("material_".$material);
							$found = true;
							break;
						}
						
						if (!$found)
						{
							for ($action=0; $action < count($this->ccr->getMaterial($material)->getActions()); $action++)
							{
								if (!$this->ccr->getMaterial($material)->getAction($action)->form->isValid())
								{
									$this->setPageAction("materialaction_".$material."_".$action);
									$found = true;
									break;
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	
	
	public function doStuffAndShow($outputType="normal")
	{
		$output = "";
		
		
		if (isset($_REQUEST['id']))
		{
			$requestID = $_REQUEST['id'];
		}
		else 
		{
			$requestID = "-1";
		}
		
		
		if ($this->getPageAction() == "completereport")
		{
			$this->ccr->form->get("finalComments")->processPost(isset($_POST['finalComments']) ? page::xmlEntities($_POST['finalComments']) : '');

			//$this->ccr->save();
			$this->ccr->complete();		
			
			$this->redirect("/apps/ccr/index?id=" . $this->ccr->getId());
		}
		
		
		
		
		
		if (preg_match("/^completereportaction_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setReportActionId($match[1]);
			

			$this->ccr->getAction($this->getReportActionId())->form->get("completionComments")->processPost(isset($_POST['completionComments']) ? page::xmlEntities($_POST['completionComments']) : '');
			
			$this->ccr->getAction($this->getReportActionId())->complete();
			
			$this->ccr->addLog("Action completed by " . currentuser::getInstance()->getName());
			
			$this->setPageAction("reportaction_". $this->getReportActionId());
		}
		
		
		
		if (preg_match("/^delegatereportaction_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setReportActionId($match[1]);
			$this->setLocation("reportaction");
			//$this->setPageAction("

			//$this->ccr->getAction($this->getReportActionId())->form->get("completionComments")->processPost(isset($_POST['completionComments']) ? $_POST['completionComments'] : '');
			
			//$this->ccr->getAction($this->getReportActionId())->complete();
			
			//$this->setPageAction("reportaction_". $this->getReportActionId());
			
			$this->delegateForm->get("submit")->setAction("delegatereportaction_" . $this->getMaterialActionId());
			
			$output .= "<ccrDelegate id=\"".$this->getReportActionId()."\">";
			
			$output .= $this->delegateForm->output();
			
			$output .= "</ccrDelegate>";
			
			return $output;
		}
		
		

		
		if (preg_match("/^reportaction_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("reportaction");
			//$this->setMaterialId($match[1]);
			$this->setReportActionId($match[1]);
			
			$this->ccr->getAction($this->getReportActionId())->form->get("attachment")->setNextAction("reportaction_" . $this->getReportActionId());
			
			
			if (!$this->ccr->getAction($this->getReportActionId())->isComplete())
			{
				$output .= "<reportActionControl id=\"".$this->getReportActionId(). "\" isComplete=\"" . ($this->ccr->getAction($match[1])->isComplete() ? 'true' : 'false') . "\" isOwner=\"" . ($this->ccr->getAction($match[1])->getOwner() == currentuser::getInstance()->getNTLogon() ? "true" : "false") . "\" databaseID=\"". $this->ccr->getAction($match[1])->form->getDatabaseId() . "\" ccrReportID=\"" . $requestID . "\" />";
			}
			
			
			
			$output .= "<ccrReportAction id=\"".$this->getReportActionId(). "\" person=\"" . $this->ccr->getAction($this->getReportActionId())->form->get("personResponsible")->getDisplayValue() . "\" ccrReportID=\"" . $requestID . "\" >";
			
			$this->ccr->getAction($this->getReportActionId())->showCompletionBits($outputType);
			
			
			
			
			if ($outputType=="normal" && !$this->ccr->getAction($this->getReportActionId())->isComplete())
			{
				$output .= $this->ccr->getAction($this->getReportActionId())->form->output();
			}
			else 
			{
				$this->ccr->getAction($this->getReportActionId())->form->showLegend(false);
				
				$exceptions = array();
				
				if ($this->ccr->getAction($this->getReportActionId())->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->getAction($this->getReportActionId())->isComplete())
				{
					$exceptions[] = "completionComments";
				}
				
				$output .= $this->ccr->getAction($this->getReportActionId())->form->readOnlyOutput($exceptions);
			}
			$output .= "</ccrReportAction>";
			
			return $output;
		}
		
		
		
		
		if ($this->getPageAction() == "addreportaction")
		{			
			$this->setLocation("reportaction");
			$this->setReportActionId($this->ccr->addAction());
			
			$this->ccr->getAction($this->getReportActionId())->form->get("attachment")->setNextAction("reportaction_" . $this->getReportActionId());
			
			$output .= "<technicalControl />";
			$output .= "<materialControl />";
			//$output .= "<reportActionControl />";
			$output .= "<reportActionControl id=\"".$this->getReportActionId(). "\" isComplete=\"false\" isOwner=\"false\" databaseID=\"". $this->ccr->getAction($this->getReportActionId())->form->getDatabaseId() . "\" />";
		
			$output .= "<ccrReportAction id=\"".$this->getReportActionId()."\">";
			$output .= $this->ccr->getAction($this->getReportActionId())->form->output();
			$output .= "</ccrReportAction>";
			
			return $output;
		}
		
	
		
		if (preg_match("/^removereportaction_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->ccr->removeAction($match[1]);
			//$this->setMaterialId($match[1]);
			$this->setLocation("report");
			
			$this->setPageAction("report");
		}
		
		
		
		
		
		
		if ($this->getPageAction() == "technical")
		{
			$this->setLocation("technical");
			
			//$this->ccr->getMaterial($this->getMaterialId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<technicalControl id=\"".$this->getTechnicalId()."\" />";
			$output .= "<materialControl />";
			$output .= "<reportActionControl />";
			
			page::addDebug("Show technical", __FILE__, __LINE__);
			
		//	$this->ccr->getTechnical($this->getTechnicalId())->processHierarchy();
			
			$output .= "<ccrTechnical id=\"".$this->getTechnicalId()."\">";
			$output .= $this->ccr->getTechnical($this->getTechnicalId())->form->output();
			$output .= "</ccrTechnical>";
			
			return $output;
		}
		
		
		if (preg_match("/^technical_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("technical");
			$this->setTechnicalId($match[1]);
			
			//$this->ccr->getTechnical($this->getTechnicalId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<technicalControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialControl />";
			$output .= "<reportActionControl />";
			
			page::addDebug("Show technical", __FILE__, __LINE__);
			
			//$this->ccr->getTechnical($this->getTechnicalId())->processHierarchy();
			
			$output .= "<ccrTechnical id=\"".$this->getTechnicalId()."\">";
			
			
			if ($outputType=="normal")
			{
				$output .= $this->ccr->getTechnical($this->getTechnicalId())->form->output();
			}
			else 
			{
				$this->ccr->getTechnical($this->getTechnicalId())->form->showLegend(false);
				$output .= $this->ccr->getTechnical($this->getTechnicalId())->form->readOnlyOutput();
			}
			
			$output .= "</ccrTechnical>";
			
			return $output;
		}
		
		
				
		if ($this->getPageAction() == "addtechnical")
		{
			$this->setLocation("technical");
			$this->setTechnicalId($this->ccr->addTechnical());
			
			//$this->ccr->getTechnical($this->getTechnicalId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<technicalControl id=\"".$this->getTechnicalId()."\" />";
			$output .= "<materialControl />";
			$output .= "<reportActionControl />";
		
		//	$this->ccr->getTechnical($this->getTechnicalId())->processHierarchy();
				
			$output .= "<ccrTechnical id=\"".$this->getTechnicalId()."\">";
			$output .= $this->ccr->getTechnical($this->getTechnicalId())->form->output();
			$output .= "</ccrTechnical>";
			
			return $output;
		}
		

		if (preg_match("/^removetechnical_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->ccr->removeTechnical($match[1]);
			$this->setLocation("report");
			
			$this->setPageAction("report");
		}
		
		
		
		
		
		
		
		
		
		

		
		if ($this->getPageAction() == "material")
		{
			$this->setLocation("material");
			
			$this->ccr->getMaterial($this->getMaterialId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" />";
			
			page::addDebug("Show material", __FILE__, __LINE__);
			
			$this->ccr->getMaterial($this->getMaterialId())->processHierarchy();
			
			$output .= "<ccrMaterial id=\"".$this->getMaterialId()."\">";
			$output .= $this->ccr->getMaterial($this->getMaterialId())->form->output();
			$output .= "</ccrMaterial>";
			
			return $output;
		}
		
		
		if (preg_match("/^material_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("material");
			$this->setMaterialId($match[1]);
			
			$this->ccr->getMaterial($this->getMaterialId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" />";
			
			page::addDebug("Show material", __FILE__, __LINE__);
			
			$this->ccr->getMaterial($this->getMaterialId())->processHierarchy();
			
			$output .= "<ccrMaterial id=\"".$this->getMaterialId()."\" materialGroupID=\"" . ($this->ccr->getMaterial($this->getMaterialId())->form->get("isSapProduct")->getValue() == 'yes' ? $this->ccr->getMaterial($this->getMaterialId())->form->get("materialKey")->getValue() : $this->ccr->getMaterial($this->getMaterialId())->form->get("alternativeMaterialKey")->getValue()) . "\">";
			
			
			if ($outputType=="normal")
			{
				$output .= $this->ccr->getMaterial($this->getMaterialId())->form->output();
			}
			else 
			{
				$this->ccr->getMaterial($this->getMaterialId())->form->showLegend(false);
				$output .= $this->ccr->getMaterial($this->getMaterialId())->form->readOnlyOutput();
			}
			
			$output .= "</ccrMaterial>";
			
			return $output;
		}
		
		
				
		if ($this->getPageAction() == "addmaterial")
		{
			$this->setLocation("material");
			$this->setMaterialId($this->ccr->addMaterial());
			
			$this->ccr->getMaterial($this->getMaterialId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			$output .= "<technicalControl />";
			$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" />";
		
			$this->ccr->getMaterial($this->getMaterialId())->processHierarchy();
				
			$output .= "<ccrMaterial id=\"".$this->getMaterialId()."\">";
			$output .= $this->ccr->getMaterial($this->getMaterialId())->form->output();
			$output .= "</ccrMaterial>";
			
			return $output;
		}
		

		if (preg_match("/^removematerial_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->ccr->removeMaterial($match[1]);
			$this->setLocation("report");
			
			$this->setPageAction("report");
		}
		
		
		if (preg_match("/^completematerialaction_([0-9]+)_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setMaterialId($match[1]);
			$this->setMaterialActionId($match[2]);
			
			$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->get("completionComments")->processPost(isset($_POST['completionComments']) ? page::xmlEntities($_POST['completionComments']) : '');
			
			
			$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->complete();
			
			$this->ccr->addLog("Action completed by " . currentuser::getInstance()->getName());		//BPD 09/03/2006
			
			$this->setPageAction("materialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
		}
		
		
		if (preg_match("/^delegatematerialaction_([0-9]+)_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setMaterialId($match[1]);
			$this->setMaterialActionId($match[2]);
			
			$this->setLocation("delegatematerialaction");
			
			//$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->get("completionComments")->processPost(isset($_POST['completionComments']) ? $_POST['completionComments'] : '');
			
			
			$this->delegateForm->get("submit")->setAction("delegatematerialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
			
			$output .= "<ccrDelegate id=\"".$this->getMaterialActionId()."\" material=\"".$this->getMaterialId()."\">";
			
			$output .= $this->delegateForm->output();
			$output .= "</ccrDelegate>";
			
			return $output;
			
			
			
			//$this->setPageAction("delegatematerialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
		}
		
		
		
		if (preg_match("/^materialaction_([0-9]+)_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("materialaction");
			$this->setMaterialId($match[1]);
			$this->setMaterialActionId($match[2]);
			
			$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->get("attachment")->setNextAction("materialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
			
			
			
			//$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialControl />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" id=\"".$this->getMaterialActionId(). "\" isComplete=\"" . ($this->ccr->getMaterial($match[1])->getAction($match[2])->isComplete() ? "true" : "false") . "\" isOwner=\"" . ($this->ccr->getMaterial($match[1])->getAction($match[2])->getOwner() == currentuser::getInstance()->getNTLogon() ? "true" : "false") . "\" databaseID=\"". $this->ccr->getMaterial($match[1])->getAction($match[2])->form->getDatabaseId() . "\" ccrReportID=\"" . $requestID . "\" />";
			
			$output .= "<ccrAction id=\"".$this->getMaterialActionId()."\" material=\"".$this->getMaterialId() . "\" person=\"" . $this->ccr->getMaterial($match[1])->getAction($match[2])->form->get("personResponsible")->getDisplayValue() . "\" >";
			
			
			$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->showCompletionBits($outputType);
			
			if ($outputType=="normal" && !$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->isComplete())
			{
				$output .= $this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->output();
			}
			else 
			{
				$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->showLegend(false);
				
				$exceptions = array();
				
				if ($this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->isComplete())
				{
					$exceptions[] = "completionComments";
				}
				
				$output .= $this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->readOnlyOutput($exceptions);
			}
			
			$output .= "</ccrAction>";
			
			if ($outputType=="readonly")
			{
				//$output .= $this->ccr->getLogOutput();
			}
			
			return $output;
		}
		
		
		
		if (preg_match("/^addmaterialaction_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->setLocation("materialaction");
			$this->setMaterialId($match[1]);
			$this->setMaterialActionId($this->ccr->getMaterial($this->getMaterialId())->addAction());
			
			$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->get("attachment")->setNextAction("materialaction_" . $this->getMaterialId() . "_" . $this->getMaterialActionId());
			
			
			//$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialControl />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" id=\"".$this->getMaterialActionId()."\" />";
			
			$output .= "<ccrAction id=\"" . $this->getMaterialActionId() . "\" material=\"".$this->getMaterialId()."\">";
			$output .= $this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->output();
			$output .= "</ccrAction>";
			
			return $output;
		}

		
		
		if (preg_match("/^removematerialaction_([0-9]+)_([0-9]+)$/", $this->getPageAction(), $match))
		{
			$this->ccr->getMaterial($match[1])->removeAction($match[2]);
			$this->setMaterialId($match[1]);
			$this->setLocation("material");
			
			$this->ccr->getMaterial($this->getMaterialId())->form->get("attachment")->setNextAction("material_" . $this->getMaterialId());
			
			
			$output .= "<materialControl id=\"".$this->getMaterialId()."\" />";
			$output .= "<materialActionControl material=\"".$this->getMaterialId()."\" />";
					
			$output .= "<ccrMaterial id=\"".$this->getMaterialId()."\">";
			$output .= $this->ccr->getMaterial($this->getMaterialId())->form->output();
			$output .= "</ccrMaterial>";
			
			return $output;
		}
		
		if ($this->getPageAction() == "report")
		{
			$this->setLocation("report");
		
			
			// controls down the left hand side
			
			// see if all the actions for the ccr are closed, if they are show the complete control
			
			$readyForComplete = true;
			
			for ($actionReport = 0; $actionReport < count($this->ccr->getActions()); $actionReport++)
			{
				if (!$this->ccr->getAction($actionReport)->isComplete()) {
					$readyForComplete = false;
					break;
				}
			}
			
			for ($material = 0; $material < count($this->ccr->getMaterials()); $material++)
			{
				for ($action = 0; $action < count($this->ccr->getMaterial($material)->getActions()); $action++)
				{
					if (!$this->ccr->getMaterial($material)->getAction($action)->isComplete()) {
						$readyForComplete = false;
						break;
					}
				}
			}
			
			if ($this->ccr->getLoadedFromDatabase())
			{
				if ($readyForComplete)
				{
					$output .= "<reportControl isComplete=\"" . ($this->ccr->isComplete() ? "true" : "false") . "\" isOwner=\"" . ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon() ? "true" : "false") . "\" />";
									
					$this->ccr->showCompletionBits($outputType);
				}
				else 
				{
					$this->ccr->form->get('status')->setVisible(true);
				}
			}
			
			$output .= "<reportActionControl />";
			
			$output .= "<technicalControl />";
			
			$output .= "<materialControl />";
			
			
			
			
			// content
			
			$output .= "<ccrReport id=\"" . $requestID . "\" customerName=\"" . $this->ccr->getCustomerName() . "\">";
			if ($outputType=="normal")
			{
				$output .= $this->ccr->form->output();
			}
			else 
			{
				$exceptions = array();
				
				if ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon() && !$this->ccr->isComplete())
				{
					$exceptions[] = "finalComments";
				}
				
				if ($this->ccr->form->get("typeOfCustomer")->getValue() != "new_customer")
				{
					$this->ccr->form->getGroup("client")->setBorder(false);
					$this->ccr->form->getGroup("SAPexistingDistributor")->setBorder(false);
					$this->ccr->form->getGroup("SAPcustomerOfDistributor")->setBorder(false);
					$this->ccr->form->getGroup("customerDetails")->setVisible(true);
					
					$this->ccr->form->get("directCustomerName")->setVisible(true);
					$this->ccr->form->get("directCustomerAddress")->setVisible(true);
					$this->ccr->form->get("directCustomerCountry")->setVisible(true);
				}
				
				$this->ccr->form->showLegend(false);
				$output .= $this->ccr->form->readOnlyOutput($exceptions);
				
				
				
				
			}
			$output .= "</ccrReport>";
			
			if ($outputType != "normal")
			{
				$output .= $this->ccr->getLogOutput();
			}
			
			return $output;
		}
		
		
		
		if ($this->getPageAction() == "print")
		{
			$this->setLocation("print");
			
			$output .= "<ccrReport id=\"" . $_REQUEST['id'] . "\" customerName=\"" . $this->ccr->getCustomerName() . "\">";
			
			if ($this->ccr->form->get("typeOfCustomer")->getValue() != "new_customer")
			{
				$this->ccr->form->getGroup("client")->setBorder(false);
				$this->ccr->form->getGroup("SAPexistingDistributor")->setBorder(false);
				$this->ccr->form->getGroup("SAPcustomerOfDistributor")->setBorder(false);
				
				$this->ccr->form->getGroup("customerDetails")->setVisible(true);
				
				$this->ccr->form->get("directCustomerName")->setVisible(true);
				$this->ccr->form->get("directCustomerAddress")->setVisible(true);
				$this->ccr->form->get("directCustomerCountry")->setVisible(true);
			}
				
			$this->ccr->form->showLegend(false);
			$output .= $this->ccr->form->readOnlyOutput();
			
			$output .= "</ccrReport>";
			
			for ($i=0;$i<count($this->ccr->getActions());$i++)
			{
				$this->setReportActionId($i);
			
				
				$output .= "<ccrReportAction id=\"".$this->getReportActionId()."\" person=\"" . $this->ccr->getAction($i)->form->get("personResponsible")->getDisplayValue() . "\" ccrReportID=\"" . $_REQUEST['id'] . "\" >";
				$this->ccr->getAction($this->getReportActionId())->form->showLegend(false);
				$this->ccr->getAction($this->getReportActionId())->showCompletionBits($outputType);
				$output .= $this->ccr->getAction($this->getReportActionId())->form->readOnlyOutput();
				$output .= "</ccrReportAction>";
			}
			
			
			for ($materialNumber=0;$materialNumber<count($this->ccr->getMaterials());$materialNumber++)
			{
				$this->setLocation("material");
				$this->setMaterialId($materialNumber);
			

				$output .= "<ccrMaterial id=\"".$this->getMaterialId()."\" materialGroupID=\"" . $this->ccr->getMaterial($materialNumber)->form->get("materialKey")->getValue() . "\">";
				$this->ccr->getMaterial($this->getMaterialId())->form->showLegend(false);
				$output .= $this->ccr->getMaterial($this->getMaterialId())->form->readOnlyOutput();
				
				
				for ($actionNumber=0; $actionNumber < count($this->ccr->getMaterial($materialNumber)->getActions()); $actionNumber++)
				{
					$this->setLocation("materialaction");
					$this->setMaterialId($materialNumber);
					$this->setMaterialActionId($actionNumber);
					
					//$personResponsible = usercache::getInstance()->get($this->ccr->getMaterial($materialNumber)->getAction($actionNumber)->form->get("personResponsible")->getValue());
		    
					//$this->ccr->getMaterial($materialNumber)->getAction($actionNumber)->form->get("personResponsible")->setValue($personResponsible->getName());
			
					
					$output .= "<ccrAction id=\"".$this->getMaterialActionId()."\" material=\"".$this->getMaterialId()."\" person=\"" . $this->ccr->getMaterial($materialNumber)->getAction($actionNumber)->form->get("personResponsible")->getDisplayValue() . "\">";
					$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->showLegend(false);
					$this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->showCompletionBits($outputType);
					$output .= $this->ccr->getMaterial($this->getMaterialId())->getAction($this->getMaterialActionId())->form->readOnlyOutput();
					$output .= "</ccrAction>";
				}
				$output .= "</ccrMaterial>";
			}
			
			$output .= $this->ccr->getEntireLogOutput();
			
			return $output;
			
		}
		
	}
	
	
	
	
	
	public function buildMenu()
	{
		// report root
		
		
		$output = sprintf('<reportNav valid="%s" selected="%s">',
			$this->ccr->form->isValid() ? 'true' : 'false',
			$this->getLocation() == 'report' ? 'true' : 'false'
		);
		
		
		$reportAttachments = $this->ccr->form->get("attachment")->getValue();
		
		for ($attachment=0; $attachment < count($reportAttachments); $attachment++)
		{
			$output .= sprintf('<reportAttachmentNav name="%s">%s</reportAttachmentNav>', 
				$reportAttachments[$attachment]['name'],
				$reportAttachments[$attachment]['file']
			);
		}
		
		
		// report actions
		
		for ($action=0; $action < count($this->ccr->getActions()); $action++)
		{
			$output .= sprintf('<reportActionNav valid="%s" person="%s" selected="%s" action="%u">', 
				$this->ccr->getAction($action)->form->isValid() ? 'true' : 'false',
				$this->ccr->getAction($action)->form->get("personResponsible")->getDisplayValue(),
				($this->getLocation() == 'reportaction' && $this->getReportActionId()==$action) ? 'true' : 'false',
				$action
			);
			
			$reportActionAttachments = $this->ccr->getAction($action)->form->get("attachment")->getValue();
		
			for ($attachment=0; $attachment < count($reportActionAttachments); $attachment++)
			{
				$output .= sprintf('<reportActionAttachmentNav name="%s">%s</reportActionAttachmentNav>', 
					$reportActionAttachments[$attachment]['name'],
					$reportActionAttachments[$attachment]['file']
				);
			}
			
			
			$output .= '</reportActionNav>';
		}
		
		
		
		// technical bit
		
		for ($technical=0; $technical < count($this->ccr->getTechnicals()); $technical++)
		{
			$output .= sprintf('<technicalNav id="%u" valid="%s" selected="%s" />', 
				$technical,
				$this->ccr->getTechnical($technical)->form->isValid() ? 'true' : 'false',
				($this->getLocation() == 'technical' && $this->getTechnicalId()==$technical) ? 'true' : 'false'
			);
			
			//$reportActionAttachments = $this->ccr->getAction($action)->form->get("attachment")->getValue();
	
			
			//$output .= '</reportActionNav>';
		}
		
		
		
		
		// materials
		
		for ($material=0; $material < count($this->ccr->getMaterials()); $material++)
		{
			// material
			
			$output .= sprintf('<materialNav id="%u" materialGroupID="%s" valid="%s" selected="%s">',
				$material,
				($this->ccr->getMaterial($material)->form->get("isSapProduct")->getValue() == 'yes' ? $this->ccr->getMaterial($material)->form->get("materialKey")->getValue() : $this->ccr->getMaterial($material)->form->get("alternativeMaterialKey")->getValue()),
				$this->ccr->getMaterial($material)->form->isValid() ? 'true' : 'false',
				($this->getLocation() == 'material' && $this->getMaterialId()==$material) ? 'true' : 'false'
			);
			
			
			$materialAttachments = $this->ccr->getMaterial($material)->form->get("attachment")->getValue();
		
			for ($attachment=0; $attachment < count($materialAttachments); $attachment++)
			{
				$output .= sprintf('<materialAttachmentNav name="%s">%s</materialAttachmentNav>', 
					$materialAttachments[$attachment]['name'],
					$materialAttachments[$attachment]['file']
				);
			}
			
			
			
			// material actions
			
			for ($action=0; $action < count($this->ccr->getMaterial($material)->getActions()); $action++)
			{
				$output .= sprintf('<materialActionNav material="%u" person="%s" valid="%s" selected="%s" action="%u">',
					$material,
					$this->ccr->getMaterial($material)->getAction($action)->form->get("personResponsible")->getDisplayValue(),
					$this->ccr->getMaterial($material)->getAction($action)->form->isValid() ? 'true' : 'false',
					($this->getLocation() == 'materialaction' && $this->getMaterialId()==$material && $this->getMaterialActionId()==$action) ? 'true' : 'false',
					$action
				);
				
				$actionAttachments = $this->ccr->getMaterial($material)->getAction($action)->form->get("attachment")->getValue();
		
				for ($attachment=0; $attachment < count($actionAttachments); $attachment++)
				{
					$output .= sprintf('<materialActionAttachmentNav name="%s">%s</materialActionAttachmentNav>', 
						$actionAttachments[$attachment]['name'],
						$actionAttachments[$attachment]['file']
					);
				}
				
				$output .= '</materialActionNav>';
			}
			
			$output .= "</materialNav>";
			
		}
		
		$output .= "</reportNav>";
		
		
		
		return $output;
	}
	
	
	
	
	
	
	//
	//
	//
	//  Lots of set and get stuff
	//
	//
	//
	
	
	
	public function getLocation()
	{
		if (!isset($_SESSION['apps'][$GLOBALS['app']]['location']))
		{
			$_SESSION['apps'][$GLOBALS['app']]['location'] = 'report';
		}
		
		return $_SESSION['apps'][$GLOBALS['app']]['location'];
	}
	
	public function setLocation($location)
	{
		page::addDebug("set location $location", __FILE__, __LINE__);
		$_SESSION['apps'][$GLOBALS['app']]['location'] = $location;
	}
	
	
	
	
	public function getPageAction()
	{
		if (!isset($this->pageAction))
		{
			if (isset($_POST['action']))
			{
				$this->pageAction = $_POST['action'];
			}
			else 
			{
				if (isset($_REQUEST['action']))
				{
					$this->pageAction = $_REQUEST['action'];
				}
				else 
				{
					$this->pageAction = "report";
				}
			}
		}
		
		return $this->pageAction;
		
	}
	
	public function setPageAction($pageAction)
	{
		$this->pageAction = $pageAction;
	}
	
	
	
	// Actions attached to the report
	
	public function getReportActionId()
	{
		if (!isset($this->reportActionId))
		{
			$this->reportActionId = isset($_POST['actionId']) ? $_POST['actionId'] : "0";
		}
		
		return $this->reportActionId;
	}
	
	public function setReportActionId($reportActionId)
	{
		$this->reportActionId = $reportActionId;
	}
	
	
	// Technical	
	
	public function getTechnicalId()
	{
		if (!isset($this->technicalId))
		{
			$this->technicalId = isset($_POST['technicalId']) ? $_POST['technicalId'] : "0";
		}
		
		return $this->technicalId;
	}
	
	public function setTechnicalId($technicalId)
	{
		$this->technicalId = $technicalId;
	}
	
	
	
	// Materials	
	
	public function getMaterialId()
	{
		if (!isset($this->materialId))
		{
			$this->materialId = isset($_POST['materialId']) ? $_POST['materialId'] : "0";
		}
		
		return $this->materialId;
	}
	
	public function setMaterialId($materialId)
	{
		$this->materialId = $materialId;
	}
	
	
	// Actions attached to materials
	
	public function getMaterialActionId()
	{
		if (!isset($this->materialActionId))
		{
			$this->materialActionId = isset($_POST['actionId']) ? $_POST['actionId'] : "0";
		}
		
		return $this->materialActionId;
	}
	
	public function setMaterialActionId($materialActionId)
	{
		$this->materialActionId = $materialActionId;
	}
	
	
	
	// Opportunity
	
	public function getOpportunityId()
	{
		if (!isset($this->opportunityId))
		{
			$this->opportunityId = isset($_POST['opportunityId']) ? $_POST['opportunityId'] : "0";
		}
		
		return $this->opportunityId;
	}
	
	public function setOpportunityId($opportunityId)
	{
		$this->opportunityId = $opportunityId;
	}
	
	
	/**
	 * Actions attached to opportunities
	 * 
	 * bigger descriptiony stuff
	 */
	public function getOpportunityActionId()
	{
		if (!isset($this->opportunityActionId))
		{
			$this->opportunityActionId = isset($_POST['actionId']) ? $_POST['actionId'] : "0";
		}
		
		return $this->opportunityActionId;
	}
	
	public function setOpportunityActionId($opportunityActionId)
	{
		$this->opportunityActionId = $opportunityActionId;
	}
	
	
	
	public function defineDelegateForm()
	{
		$this->delegateForm = new form("delegate");
		
		$delegate = new group("delegate");
		
	
		/*$parentId = new invisibletext("id");			//id of the ccr or opportunity
		$parentId->setGroup("action");
		$parentId->setDataType("number");
		$parentId->setRequired(false);
		$parentId->setValue(0);
		$parentId->setTable("action");
		$parentId->setVisible(false);
		$action->add($parentId);*/
		
		
		$personResponsible = new autocomplete("personResponsible");
		$personResponsible->setGroup("action");
		$personResponsible->setDataType("string");
		$personResponsible->setLength(50);
		$personResponsible->setUrl('/ajax/employee?key=personResponsible');
		$personResponsible->setRowTitle("Delegate To");
		$personResponsible->setRequired(true);
		$personResponsible->setTable("action");
		$personResponsible->setIsAnNTLogon(true);
		$delegate->add($personResponsible);
		
		
		
		$message = new textarea("message");
		$message->setGroup("action");
		$message->setDataType("text");
		$message->setRequired(false);
		$message->setRowTitle("Message");
		$message->setTable("action");
		$delegate->add($message);
		
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$submit->setValue("Confirm Delegation");
		//$submit->setAction("")
		$delegate->add($submit);
		

		$this->delegateForm->add($delegate);
	}
	
}

?>