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
class complaints extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName("COMPLAINTS");
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$this->xml .= "<complaints>";				
		
		$complaintCount = 0;
		
		$dataset = mysql::getInstance()->selectDatabase("[complaints-live]")->Execute("SELECT complaint.complaint_number, data.sap_name FROM complaint INNER JOIN data ON (complaint.complaint_number = data.complaint_number) WHERE owner = '" . currentuser::getInstance()->getNTLogon() . "' AND status_total=1 ORDER BY complaint.complaint_number ASC");
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<complaint_owned>";
			$this->xml .= "<number>" . $fields['complaint_number'] . "</number>\n";
            $this->xml .= "<name>" . $fields['sap_name'] . "</name>";
            $this->xml .= "</complaint_owned>";
            $complaintCount++;
		}
		$this->xml .= "<complaintCount>" . $complaintCount . "</complaintCount>";
		$this->xml .= "</complaints>";
		
		return $this->xml;
	}
}

?>