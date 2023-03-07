<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 24/11/2010
 */
$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class ccSummary extends snapin 
{	
	private $complaintId;
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->complaintId = $_GET['complaintId'];
		
		$this->complaintLib = new complaintLib();
		$this->approval = new approval( $this->complaintId );
		
		$this->setName(translate::getInstance()->translate("complaint") . ': ' . $this->complaintId);
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{
		$complaint = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT * 
				FROM complaint 
				WHERE id = " . $this->complaintId);
				
		$evaluation = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT * 
				FROM evaluation 
				WHERE complaintId = " . $this->complaintId);
			
		$conclusion = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT * 
				FROM conclusion 
				WHERE complaintId = " . $this->complaintId);
		
		$complaintFields = mysql_fetch_array($complaint);
		$evaluationFields = mysql_fetch_assoc($evaluation);
		$conclusionFields = mysql_fetch_assoc($conclusion);
		
		$this->xml .= "<ccSummarySnapin>";
		
		$this->xml .= (DEV ? "<root>scapanetdev</root>" : "<root>scapanet</root>");
		
		if ( currentuser::getInstance()->getNTLogon() == strtolower($complaintFields['complaintOwner']) )
		{
			$this->userIsComplaintOwner = true;
		}
		else
		{
			$this->userIsComplaintOwner = false;
		}
		
		if ( currentuser::getInstance()->getNTLogon() == strtolower($complaintFields['evaluationOwner']) )
		{
			$this->userIsEvaluationOwner = true;
		}
		else
		{
			$this->userIsEvaluationOwner = false;
		}
		
		if ( currentuser::getInstance()->hasPermission("customerComplaints_admin") )
		{
			$this->userIsAdmin = true;
			$this->xml .= "<userIsAdmin />";
		}
		else
		{
			$this->userIsAdmin = false;
		}
		
		$this->xml .= "<complaintId>" . $complaintFields['id'] . "</complaintId>";
		$this->xml .= "<customerName>" . sapCustomer::getName($complaintFields['sapCustomerNo']) . "</customerName>";
		$this->xml .= "<bu>" . sapCustomer::getBU($complaintFields['sapCustomerNo']) . "</bu>";
		$this->xml .= "<createdBy>" . usercache::getInstance()->get($complaintFields['submitBy'])->getName() . "</createdBy>";
		$this->xml .= "<cOwner>" . usercache::getInstance()->get($complaintFields['complaintOwner'])->getName() . "</cOwner>";
		$this->xml .= "<eOwner>" . usercache::getInstance()->get($complaintFields['evaluationOwner'])->getName() . "</eOwner>";
		
		if($complaintFields['complaintDate'] != NULL)
		{
			$this->xml .= "<complaintDate>" . myCalendar::dateForUser($complaintFields['complaintDate']) . "</complaintDate>";
		}
		
		if($complaintFields['categoryId'] != NULL)
		{
			$this->xml .= "<category>" . complaintLib::getOptionText( $complaintFields['categoryId'] ) . "</category>";
		}
		
		if($complaintFields['complaintValue'] != NULL)
		{
			$this->xml .= "<complaintValue>" . 
							$complaintFields['complaintValue'] . " " . 
							complaintLib::getOptionText( $complaintFields['complaintCurrency'] ) . 
						  "</complaintValue>";
		}
		
		
		//complaint:
		if( ($this->userIsComplaintOwner || $this->userIsAdmin) && $complaintFields['totalClosure'] != 1 && !$this->approval->started())
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'complaint' ) )
			{
				$this->xml .= "<complaintAll />";
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'complaint' ) )->getName();
				$this->xml .= "<complaintLocked>$user</complaintLocked>";
			}
		}
		else
		{
			if( $complaintFields['submitStatus'] == 1 )
			{
				$this->xml .= "<complaintView />";
			}
			else
			{
				$this->xml .= "<complaintNone />";
			}
		}
		
		//evaluation:
		if( ($this->userIsEvaluationOwner || $this->userIsAdmin) && $complaintFields['totalClosure'] != 1 && $complaintFields['submitStatus'] == 1)
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'evaluation' ) )
			{
				if( mysql_num_rows( $evaluation ) == 1 )
				{
					
					$this->xml .= "<evaluationAll />";
				}
				else
				{
					$this->xml .= "<evaluationAdd />";
				}
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'evaluation' ) )->getName();
				$this->xml .= "<evaluationLocked>$user</evaluationLocked>";
			}
		}
		else
		{
			if( mysql_num_rows( $evaluation ) == 1 && $evaluationFields['submitStatus'] == 1 )
			{
				$this->xml .= "<evaluationView />";
			}
			else
			{
				$this->xml .= "<evaluationNone />";
			}
		}
		
		//conclusion
		if( ($this->userIsComplaintOwner || $this->userIsAdmin) && $complaintFields['totalClosure'] != 1 && $complaintFields['submitStatus'] == 1)
		{
			if( $this->complaintLib->isUnlockedForUser( $this->complaintId, 'conclusion' ) )
			{
				if( mysql_num_rows( $conclusion ) == 1 )
				{
					
					$this->xml .= "<conclusionAll />";
				}
				else
				{
					$this->xml .= "<conclusionAdd />";
				}
			}
			else
			{
				$user = usercache::getInstance()->get( $this->complaintLib->getLockedUser( $this->complaintId, 'conclusion' ) )->getName();
				$this->xml .= "<conclusionLocked>$user</conclusionLocked>";
			}
		}
		else
		{
			if( mysql_num_rows( $conclusion ) == 1 )
			{
				$this->xml .= "<conclusionView />";
			}
			else
			{
				$this->xml .= "<conclusionNone />";
			}
		}
		
		
		//files for complaint (always check)
		$this->xml .= $this->loadAttachments("complaint");
		
		//files for evaluation
		$this->xml .= $this->loadAttachments("evaluation");
	
		//files for conclusion
		$this->xml .= $this->loadAttachments("conclusion");
		
		//invoices
		$this->xml .= $this->displayInvoices();
		
		$this->xml .= "</ccSummarySnapin>";
		
		//die( $this->xml );
		
		return $this->xml;
	}
	
	function getDirectoryList($form) 
	{
		$directory = $_SERVER['DOCUMENT_ROOT'] . "/apps/customerComplaints/attachments/$form/$this->complaintId";
		
		// create an array to hold directory list
		$results = array();

		if(file_exists($directory))
		{
			// create a handler for the directory
			$handler = opendir($directory);

			// open directory and walk through the filenames
			while ($file = readdir($handler)) 
			{
				// if file isn't this directory or its parent, add it to the results
				if ($file != "." && $file != "..") 
				{
					$results[] = $file;
				}
			}

			// tidy up: close the handler
			closedir($handler);
		}
		
		// done!
		return $results;
	}
	
	private function loadAttachments($form)
	{
		$xml = "";
		
		//files for complaint (always check)
		$attachments = $this->getDirectoryList($form);
		
		if(count($attachments) > 0)
		{
			foreach($attachments as $attachment)
			{
				if(strlen($attachment) > 20)
				{
					$name = substr( $attachment, 0, 17) . "...";
				}
				else
				{
					$name = $attachment;
				}
				$xml .= '<' . $form . '_attachment value="' . $attachment . '" name="' . $name . '" />';
			}
		}
		
		return $xml;
	}
	
	private function displayInvoices()
	{
		$dataset_material = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT sap.material AS material, sap.materialDescription AS materialDescription, sap.MaterialGroup AS materialGroup 
				FROM complaintsCustomer.invoicePopup cc
				JOIN SAP.invoices sap
				ON cc.invoicesId = sap.id 
				WHERE cc.complaintId = " . $this->complaintId . " 
				GROUP BY sap.material");
		
		$xml = "<numberOfMaterials>" . mysql_num_rows($dataset_material) . "</numberOfMaterials>";
		
		while($fields_material = mysql_fetch_array($dataset_material))
		{
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute("SELECT CONCAT(SUM(netValueItem_edit),' ', netValueItemCurrency_edit) AS total
					FROM complaintsCustomer.invoicePopup cc
					JOIN SAP.invoices sap
					ON sap.id = cc.invoicesId 
					WHERE complaintId = " . $this->complaintId . " 
					AND material = '" . $fields_material['material'] . "' 
					GROUP BY material");
			
			$fields_totalValue = mysql_fetch_array( $dataset );
			
			
			$xml .= "<material 
						id='" . $fields_material['material'] . "' 
						description='" . htmlentities($fields_material['materialDescription']) . "' 
						group='" .  $fields_material['materialGroup'] . "' 
						totalValue='" . $fields_totalValue["total"] . "'
					>";
			
			$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
				->Execute("SELECT 
							despatchDate, 
							deliveryNo, 
							cc.invoiceNo AS invoiceNo,
							batch_edit, 
							deliveryQuantity_edit, 
							deliveryQuantityUOM_edit, 
							netValueItem_edit, 
							netValueItemCurrency_edit
					FROM complaintsCustomer.invoicePopup cc
					JOIN SAP.invoices sap
					ON sap.id = cc.invoicesId 
					WHERE complaintId = " . $this->complaintId . " 
					AND material = '" . $fields_material['material'] . "'");
			
			for( $i=0; $i < mysql_num_rows($dataset); $i++)
			{
				$fields = mysql_fetch_array($dataset);
							
				$batchNo = ($fields['batch_edit'] == null) ? '-' : $fields['batch_edit'];
				
				
				$xml .= "<invoiceRow>";
				
					$xml .= "<invoiceNo>" . $fields['invoiceNo'] . "</invoiceNo>";
					$xml .= "<despatchDate>" . myCalendar::dateForUser($fields['despatchDate']) . "</despatchDate>";
					$xml .= "<deliveryNo>" . $fields['deliveryNo'] . "</deliveryNo>";
					$xml .= "<batch>" . $batchNo . "</batch>";
					$xml .= "<deliveryQuantity>" . $fields['deliveryQuantity_edit'] . " " . $fields['deliveryQuantityUOM_edit'] . "</deliveryQuantity>";
					$xml .= "<netValueItem>" . $fields['netValueItem_edit'] . " " . $fields['netValueItemCurrency_edit'] . "</netValueItem>";
					
					if( $i < mysql_num_rows($dataset) -1 )
					{
						$xml .= "<hr/>";
					}
				
				$xml .= "</invoiceRow>";
			}
			
			$xml .= "</material>";
		}
		
		return $xml;
	}

}

?>