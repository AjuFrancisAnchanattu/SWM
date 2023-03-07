<?php
require 'lib/ccr.php';

/**
 * This is the CCR (Customer Contact Report) Application.This application allows the sales team to document their contact with customers.
 *
 * This page allows the user to load a summary of a CCR.
 * They can also see any actions they have outstanding on a report.
 * All the reports they own.
 * And see a summary of a report they have loaded, which they can then view in full or edit.
 * 
 * @package intranet	
 * @subpackage ccr
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @author Dan Eltis
 * @version 01/02/2006
 */
class index extends page 
{
	/**
	 * The form
	 * @var form
	 */
	private $form;
	/**
	 * Will hold the current loaded CCR
	 * @var ccr
	 */
	private $ccr;
	
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		
		$this->add_output("<CCRHome>");
		

		$snapins = new snapinGroup('ccr_left');
		$snapins->register('apps/ccr', 'load', true, true);
		$snapins->register('apps/ccr', 'reports', true, true);
		$snapins->register('apps/ccr', 'actions', true, true);
		
		$snapins->get('reports')->setName(translate::getInstance()->translate("your_reports"));
		$snapins->get('actions')->setName(translate::getInstance()->translate("your_actions"));
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		
		$this->ccr = new ccr();
		
		
		if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) || isset($_REQUEST['id']))
		{
			// get anything posted by the form
						
			
			if (isset($_REQUEST['id']))
			{
				$_POST['report'] = $_REQUEST['id'];
			}
			
			
			if (isset($_SESSION['apps'][$GLOBALS['app']]['id']) && !isset($_POST['report']))
			{
				$_POST['report'] = $_SESSION['apps'][$GLOBALS['app']]['id'];
			}
	
			
			//$this->form->processPost();
			
			$this->xml .= "<CCR_report>";
			
				if ($this->ccr->load($_POST['report']))
				{
					$this->xml .= "<id>" . $this->ccr->getId() . "</id>\n";
					$this->xml .= "<customerName>" . $this->ccr->getCustomerName() . "</customerName>";
					$this->xml .= "<owner>" . usercache::getInstance()->get($this->ccr->getOwner())->getName() . "</owner>\n";
					$this->xml .= "<admin>" . (currentuser::getInstance()->isAdmin() ? 'true' : 'false') . "</admin>\n";
					$this->xml .= "<currentUser>" . currentuser::getInstance()->getNTLogon() . "</currentUser>\n";
					
					
					if ($this->ccr->getOwner() == currentuser::getInstance()->getNTLogon())
					{
						$this->xml .= "<isOwner>true</isOwner>\n";
					}
					else 
					{
						$this->xml .= "<isOwner>false</isOwner>\n";
					}
				
	
					// LOG STUFF   - ps 1 and l are exactly the same character
				
					$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT * FROM log WHERE ccrId='" . $_POST['report'] . "' ORDER BY logDate DESC, id DESC");
		
					$this->xml .= "<log>";
					
						while ($fields = mysql_fetch_array($dataset)) 
						{
							$this->xml .= "<item>";
							$this->xml .= "<area>" . $fields['area'] . "</area>\n";
							$this->xml .= "<user>" . usercache::getInstance()->get($fields['NTLogon'])->getName() . "</user>\n";
							$this->xml .= "<date>" . page::transformDateTimeForPHP($fields['logDate']) . "</date>\n";
							$this->xml .= "<action>" . $fields['action'] . "</action>\n";
							$this->xml .= "</item>";
						}
						
					$this->xml .= "</log>";
					
				
	
					$this->xml .= "<summary>";
					
						$this->xml .= "<owner>" . $this->ccr->form->get("owner")->getDisplayValue() . "</owner>\n";
						$this->xml .= "<customerName>" . $this->ccr->getCustomerName() . "</customerName>";
						
						$this->xml .= "<reportDate>" . page::transformDateForPHP($this->ccr->form->get("reportDate")->getDisplayValue()) . "</reportDate>\n";
						$this->xml .= "<contactDate>" . $this->ccr->form->get("contactDate")->getDisplayValue() . "</contactDate>\n";
						
						$dataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT status FROM report WHERE id='" . $_POST['report'] . "'");
						
						if ($fields = mysql_fetch_array($dataset))
						{
							if($fields['status'] == "0")
							{
								$this->xml .= "<status>In Progress</status>\n";
							}
							elseif($fields['status'] == "1")
							{
								$this->xml .= "<status>Completed</status>\n";
							}
							
						}
						$actionDataset = mysql::getInstance()->selectDatabase("CCR")->Execute("SELECT MIN(targetCompletion) AS nextActionCompletionDate FROM action WHERE ((type = 'ccr' and parentId ='" . $_POST['report'] . "') or (type = 'material' and parentId IN (SELECT id FROM material where ccrId='" . $_POST['report'] . "')) or (type = 'opportunity' and parentId IN (SELECT id from opportunity where materialKey IN (SELECT id FROM material where ccrId='" . $_POST['report'] . "')))) and status = '0'");
						
						if ($actionField = mysql_fetch_array($actionDataset)) 
						{
							$this->xml .= "<nextActionCompletionDate>" . page::transformDateForPHP($actionField['nextActionCompletionDate']) . "</nextActionCompletionDate>\n";
						}
							
				
						
						
						
				
						$actionPeople = array();
						//$materials = array();
						$technicals = array();
						$technicalOutstanding = 0;
				
						// report actions
					
						for ($action=0; $action < count($this->ccr->getActions()); $action++)
						{
							if (!(in_array($this->ccr->getAction($action)->form->get("personResponsible")->getDisplayValue(),$actionPeople)))
							{
								$actionPeople[] = $this->ccr->getAction($action)->form->get("personResponsible")->getDisplayValue();
							}
						}
						
						// technical enquiries
						
						$technicalOutstanding = count($this->ccr->getTechnicals());
						
						//var_dump($this->ccr->getTechnicals());
						//die();
						
						/*for ($technical=0; $technical < count($this->ccr->getTechnicals()); $technical++)
						{
							//if (!(in_array($this->ccr->getAction($action)->form->get("personResponsible")->getDisplayValue(),$actionPeople)))
							//{
								$technicals[] = $this->ccr->getTechnical($technical)->form->get("owner")->getDisplayValue();
								
								$technicalOutstanding++;
							//}
						}*/
				
				
				
						// materials
						
						for ($material=0; $material < count($this->ccr->getMaterials()); $material++)
						{
							// material
							for ($action=0; $action < count($this->ccr->getMaterial($material)->getActions()); $action++)
							{
								if (!(in_array($this->ccr->getMaterial($material)->getAction($action)->form->get("personResponsible")->getDisplayValue(),$actionPeople)))
								{
									$actionPeople[] = $this->ccr->getMaterial($material)->getAction($action)->form->get("personResponsible")->getDisplayValue();
								}
							}
							
							if ($this->ccr->getMaterial($material)->form->get("isSapProduct")->getValue() == 'yes')
							{
								$materials[] = $this->ccr->getMaterial($material)->form->get("materialKey")->getValue();
							}
							else 
							{
								$materials[] = $this->ccr->getMaterial($material)->form->get("alternativeMaterialKey")->getValue();
							}
						}
						
						if ($technicalOutstanding > 0)
						{
							$this->xml .= "<technicalEnquiries>" . $technicalOutstanding . " outstanding</technicalEnquiries>";	
						}
						else
						{
							$this->xml .= "<technicalEnquiries>None</technicalEnquiries>";	
						}
						
						if (isset($materials))
						{
							$this->xml .= "<reportMaterials>" . implode(", ",$materials) . "</reportMaterials>";	
						}
						else
						{
							$this->xml .= "<reportMaterials>None</reportMaterials>";	
						}
						
						if (isset($actionPeople) && count($actionPeople)>0)
						{
							$this->xml .= "<actionsOn>" . implode(", ",$actionPeople) . "</actionsOn>";
						}
						else
						{
							$this->xml .= "<actionsOn>None</actionsOn>";
						}
						
						/*echo "<pre>";
						var_dump($this->ccr->getMaterials());
						echo "</pre>";*/

					$this->xml .= "</summary>";
			
				$this->xml .= "</CCR_report>";
			
			
				$this->add_output($this->xml);
			}
		}

		
		$this->add_output("</CCRHome>");
		$this->output('./apps/ccr/xsl/summary.xsl');
	}
}

?>
