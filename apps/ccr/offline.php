<?php

require 'lib/ccr.php';

class offline extends page 
{
	private $form;
	
	
	function __construct()
	{
		parent::__construct();
		$this->setActivityLocation('CCR');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/ccr/xml/menu.xml");
		
		
		$this->add_output("<CCROffline>");
		
		$snapins = new snapinGroup('ccr_left');
		$snapins->register('apps/ccr', 'load', true);
		$snapins->register('apps/ccr', 'reports', true);
		$snapins->register('apps/ccr', 'actions', true);
		
		$snapins->get('reports')->setName(translate::getInstance()->translate("your_reports"));
		$snapins->get('actions')->setName(translate::getInstance()->translate("your_actions"));
		
		
		$this->add_output("<snapin_left>" . $snapins->getOutput() . "</snapin_left>");
		

		
		
		
		
		
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'upload')
		{
			// read file
			$valid = false;
			
			session::clear();
			
			
			$uploadTmpFile = $_FILES['offlineFile']['tmp_name'];
			
			
			if (file_exists($uploadTmpFile) && filesize($uploadTmpFile) > 0)
			{
				$handle = fopen($uploadTmpFile, "r");
				$content = fread($handle, filesize($uploadTmpFile));
				fclose($handle);
				
				//$decoded = base64_decode(trim($content));
				
				$degapped = mb_convert_encoding($content, "UTF-8", "UTF-16LE");
				
				$decoded = base64_decode($degapped);
	
				
				
				if(!$data = @unserialize($decoded))
				{
					$this->add_output("<error>Invalid file</error>");
					//$data = unserialize($decoded);
					//echo "<pre>" . $content . "</pre>";
					//echo "<pre>" . $degapped . "</pre>\n";
					//echo "<pre>" . $decoded . "</pre>\n";
				}
				else 
				{
					//var_dump($data);
					
					
					if (isset($data['version']))
					{
						switch($data['version'])
						{
							case '1':

								//var_dump($data);
								
								$ccr = new ccr();
								
								foreach ($data['report'] as $key => $value)
    							{
    								if ($key == 'actions')
    								{
    									for ($i=0; $i < count($data['report']['actions']); $i++)
    									{
    										$id = $ccr->addAction();
    										
    										foreach ($data['report']['actions'][$i] as $key => $value)
    										{
    											//page::addDebug("$id set '$key' > '$value'", __FILE__, __LINE__);
    											//page::addDebug(is_null($ccr->getAction($id)->form->get($key)) ? 'null' : 'exists', __FILE__, __LINE__);
	    										$ccr->getAction($id)->form->get($key)->setValue(page::xmlentities($value));										
    										}
    										
    										$ccr->getAction($id)->form->putValuesInSession();
    									}
    								}
    								else 
    								{
    									// bodge for the different SAP fields
    									switch ($key)
										{
											case 'sapNumber':
    												
    											$ccr->form->get("existingClientSapNumber")->setValue(page::xmlentities($value));
    											$ccr->form->get("existingDistributorSapNumber")->setValue(page::xmlentities($value));
    											$ccr->form->get($key)->setValue(page::xmlentities($value));
    											break;
    										
											default:
												
												$ccr->form->get($key)->setValue(page::xmlentities($value));
												break;
										}
    								}
    							}
    							
    							
    							
    							for ($i=0; $i < count($data['materials']); $i++)
								{
									$id = $ccr->addMaterial();
									
									$volume_quantity = "";
									$volume_measurement = "";									
									
									foreach ($data['materials'][$i] as $key => $value)
									{
										if ($key == 'actions')
	    								{
	    									for ($x=0; $x < count($data['materials'][$i]['actions']); $x++)
	    									{
	    										$actionid = $ccr->getMaterial($id)->addAction();
	    										
	    										foreach ($data['materials'][$i]['actions'][$x] as $key => $value)
	    										{
		    										$ccr->getMaterial($id)->getAction($actionid)->form->get($key)->setValue(page::xmlentities($value));										
	    										}
	    										
	    										$ccr->getMaterial($id)->getAction($actionid)->form->putValuesInSession();
	    									}
	    								}
	    								else
    									{
    										// hard coded bodge for VOLUME measurement control
    										switch ($key)
    										{
    											case 'volume':
    												break;
    												
    											case 'volume_measurement':
    												
    												$volume_measurement = page::xmlentities($value);
    												break;
    												
    											case 'volume_quantity':
    												
    												$volume_quantity = page::xmlentities($value);
    												break;
    												
    											default:
    												
    												$ccr->getMaterial($id)->form->get($key)->setValue(page::xmlentities($value));
    												break;
    										}
											
    									}
    									
    									// hard coded bodge for measurement control
    									$ccr->getMaterial($id)->form->get("volume")->setValue($volume_quantity . "|" . $volume_measurement);
									}
									
									$ccr->getMAterial($id)->form->putValuesInSession();
								}
    							
					
								/*$ccr->form->get('typeOfCustomer')->setValue($data['report']['typeOfCustomer']);
								$ccr->form->get('sapNumber')->setValue($data['report']['sapNumber']);
								$ccr->form->get('contactDate')->setValue($data['report']['contactDate']);*/
								
								$ccr->form->putValuesInSession();
								
							    page::redirect("/apps/ccr/add?offline=true");
								
							
								break;
								
							default:
							
								$this->add_output("<error>Unsupported file version</error>");
						}
					}
					else 
					{	
						$this->add_output("<error>Invalid file, can not determine tool version</error>");
					}
				}
			}
		}

		
		//$this->add_output($this->form->output());
		
		$this->add_output("</CCROffline>");
		$this->output('./apps/ccr/xsl/offline.xsl');
	}
	
	private function defineForm()
	{
		$this->form = new form("CCR");
		
		//$this->form->storeInSession(true);
		
		$CCR_number = new textbox("CCR_number");
		$CCR_number->setRowTitle("ID");
		$CCR_number->setDataType("string");
		$CCR_number->setLength(50);
		$this->form->add($CCR_number);
		
		$submit = new submit("submit");
		$submit->setDataType("ignore");
		$this->form->add($submit);
	}
}

?>