<?php
/**
 * This is a snapin which displays an employees complaints.
 * It shows what complaints they have open.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class complaintsHomepage extends snapin 
{	
	private $application = "complaints_snapin";
	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("complaints"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$this->xml .= "<complaintsHomepage>";
		
		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";
			
		$complaintCount = 0;
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT owner, id, sapName FROM complaint WHERE internalSalesName = '" . addslashes(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getName()) . "' AND overallComplaintStatus != 'Closed' ORDER BY id DESC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<initiatedComplaints>";
			
			$this->xml .= "<id>" . $fields['id'] . "</id>";
			$this->xml .= "<processOwner>" . usercache::getInstance()->get($fields['owner'])->getName() . "</processOwner>";
			$this->xml .= $fields['sapName'] == "" ? "<sapName>Complaint</sapName>" : "<sapName>" . $fields['sapName'] . "</sapName>";
			
			$this->xml .= "</initiatedComplaints>";

			$complaintCount++;
		}

	 	$this->xml .= "<initiatedCount>" . $complaintCount . "</initiatedCount>";
		
	 	$complaintCount =0;
	 	
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT sapName, id, status FROM complaint WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "' AND overallComplaintStatus != 'Closed' ORDER BY id DESC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<ownedComplaints>";
			
			$this->xml .= "<id>" . $fields['id'] . "</id>";
			$this->xml .= "<status>" . $fields['status'] . "</status>";
			$this->xml .= $fields['sapName'] == "" ? "<sapName>Complaint</sapName>" : "<sapName>" . $fields['sapName'] . "</sapName>";
			
			$this->xml .= "</ownedComplaints>";

			$complaintCount++;
		}
		
		$this->xml .= "<ownedCount>" . $complaintCount . "</ownedCount>";
		
		$this->xml .= "</complaintsHomepage>";
		
		return $this->xml;
	}
}

?>