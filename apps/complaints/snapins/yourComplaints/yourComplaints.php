<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class yourComplaints extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("complaints_you_own") . " (OLD SYSTEM)");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$complaintCount = 0;
		
		$this->xml .= "<complaintsReports>";
		
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `sp_siteConcerned`, `typeOfComplaint`, `sapName`, `id`, `owner`, `status`, `internalSalesName`, `sapCustomerNumber` FROM complaint WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' AND overallComplaintStatus != 'Closed' ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$datasetExt = mysqlExt::getInstance()->selectDatabase("complaintsExternal")->Execute("SELECT * FROM complaintExternal WHERE id='" . $fields['id'] . "'");
			$fieldsExt = mysql_fetch_array($datasetExt);
			
			$this->xml .= "<complaints_Report>";
			$fieldsExt['extStatus'] == 1 ? $this->xml .= "<ext_complaint_updated>1</ext_complaint_updated>" : $this->xml .= "<ext_complaint_updated>0</ext_complaint_updated>";
			$fieldsExt['scapaStatus'] == 1 ? $this->xml .= "<scapa_complaint_updated>1</scapa_complaint_updated>" : $this->xml .= "<scapa_complaint_updated>0</scapa_complaint_updated>";
			$fieldsExt['added'] == 1 ? $this->xml .= "<ext_complaint_added>1</ext_complaint_added>" : $this->xml .= "<ext_complaint_added>0</ext_complaint_added>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
			$this->xml .= "<complaint_type>" . $fields['typeOfComplaint'] . "</complaint_type>\n";		
			$this->xml .= "<owner>" . usercache::getInstance()->get(page::xmlentities($fields['owner']))->getName() . "</owner>";
            $this->xml .= "<sapCustomerNumber>" . $fields['sapName'] . "</sapCustomerNumber>";
            $this->xml .= "<sp_siteConcerned>" . $fields['sp_siteConcerned'] . "</sp_siteConcerned>";
            $this->xml .= "</complaints_Report>";
            $complaintCount++;
		}
		$this->xml .= "<reportCount>" . $complaintCount . "</reportCount>";

		/* WE AE - 22/01/08 
			added to give the saved forms */
		$savedFormCount = 0;
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY sfDateInsert DESC");
		while ($fields = mysql_fetch_array($dataset)){
			$this->xml .= "<savedComplaint>";
			$this->xml .= "<savedID>".$fields['sfID']."</savedID>";
			$this->xml .= "<savedType>".$fields['sfForm']."</savedType>";
			
			if($fields['sfTypeOfComplaint'] == "supplier_complaint")
			{
				$this->xml .= "<complaintType>SC - </complaintType>";
			}
			elseif($fields['sfTypeOfComplaint'] == "quality_complaint")
			{
				$this->xml .= "<complaintType>I - </complaintType>";
			}
			else 
			{
				$this->xml .= "<complaintType>C - </complaintType>";
			}
			
			$this->xml .= "<savedComplaintID>".$fields['sfComplaintID']."</savedComplaintID>";
			$this->xml .= "<savedDate>".date("D jS M Y", $fields['sfDateInsert'])."</savedDate>";
			$this->xml .= "</savedComplaint>";
			$savedFormCount++;
		}
		$this->xml .= "<savedReportCount>" . $savedFormCount . "</savedReportCount>";
		/* WC END*/
		$this->xml .= "</complaintsReports>";
		
		return $this->xml;
	}
}

?>