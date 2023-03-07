<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Rob Markiewka
 * @version 11/11/2010
 */
 
$root = realpath($_SERVER["DOCUMENT_ROOT"]); 
include_once "$root/apps/customerComplaints/lib/complaintLib.php";

class ccOwned extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("complaints_you_own") . " (NEW SYSTEM)");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$complaintCount = 0;
		
		$this->xml .= "<ccOwned>";
		
		// have two sections - for correctiveAction & credit?
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT id, sapCustomerNo 
				FROM complaint 
				WHERE `complaintOwner` = '" . currentuser::getInstance()->getNTLogon() . "' 
				AND submissionDate IS NULL 
				AND totalClosure = 0 
				ORDER BY id DESC");
		
		$count = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<complaintOwned>";
				$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
	            $this->xml .= "<customer>" . sapCustomer::getName( $fields['sapCustomerNo']) . "</customer>";
				$this->xml .= "<saved/>";
            $this->xml .= "</complaintOwned>";
            
            $count++;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT id, sapCustomerNo 
				FROM complaint 
				WHERE `complaintOwner` = '" . currentuser::getInstance()->getNTLogon() . "' 
				AND submissionDate IS NOT NULL 
				AND totalClosure = 0
				ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<complaintOwned>";
				$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
	            $this->xml .= "<customer>" . sapCustomer::getName( $fields['sapCustomerNo']) . "</customer>";
            $this->xml .= "</complaintOwned>";
            
            $count++;
		}
		
		$this->xml .= "<ownedComplaintCount>" . $count . "</ownedComplaintCount>";

		$sql = "SELECT id, sapCustomerNo 
				FROM complaint 
				LEFT OUTER JOIN evaluation
				ON complaint.id = evaluation.complaintId 
				WHERE `evaluationOwner` = '" . currentuser::getInstance()->getNTLogon() . "' 
				AND evaluation.complaintId IS NULL
				AND totalClosure = 0
				ORDER BY id DESC";
				
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute($sql);
		
		$count = 0;
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<evaluationOwned>";
				$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
	            $this->xml .= "<customer>" . sapCustomer::getName( $fields['sapCustomerNo']) . "</customer>";
				$this->xml .= "<NA/>";
            $this->xml .= "</evaluationOwned>";
            
            $count++;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT id, sapCustomerNo 
				FROM complaint 
				LEFT OUTER JOIN evaluation
				ON complaint.id = evaluation.complaintId 
				WHERE `evaluationOwner` = '" . currentuser::getInstance()->getNTLogon() . "' 
				AND evaluation.complaintId IS NOT NULL 
				AND evaluation.submissionDate IS NULL 
				AND totalClosure = 0
				ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<evaluationOwned>";
				$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
	            $this->xml .= "<customer>" . sapCustomer::getName( $fields['sapCustomerNo']) . "</customer>";
				$this->xml .= "<saved/>";
            $this->xml .= "</evaluationOwned>";
            
            $count++;
		}
		
		$dataset = mysql::getInstance()->selectDatabase("complaintsCustomer")
			->Execute("SELECT id, sapCustomerNo 
				FROM complaint 
				LEFT OUTER JOIN evaluation
				ON complaint.id = evaluation.complaintId 
				WHERE `evaluationOwner` = '" . currentuser::getInstance()->getNTLogon() . "' 
				AND evaluation.submissionDate IS NOT NULL 
				AND totalClosure = 0
				ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<evaluationOwned>";
				$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
	            $this->xml .= "<customer>" . sapCustomer::getName( $fields['sapCustomerNo']) . "</customer>";
            $this->xml .= "</evaluationOwned>";
            
            $count++;
		}
		
		$this->xml .= "<ownedEvaluationCount>" . $count . "</ownedEvaluationCount>";
		
		$this->xml .= "</ccOwned>";
		
		return $this->xml;
	}
}

?>