<?php

/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class toolBoxComplaints extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("complaints_toolbox"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{						
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM complaint");
		$fields = mysql_fetch_array($dataset);
		
		$datasetEval = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM evaluation");
		$fieldsEval = mysql_fetch_array($datasetEval);
		
		$datasetConc = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT * FROM conclusion");
		$fieldsConc = mysql_fetch_array($datasetConc);
		
		$this->xml .= "<toolBoxMain>";
				
		if((!isset($_REQUEST['complaint'])) && (!isset($_REQUEST['status'])))
		{
			$this->xml .= "<complaintNo>N/A</complaintNo>";			
		}
		
		$this->xml .= !$fields['id'] ? "<complaintStatus>true</complaintStatus>\n":"<complaintStatus>false</complaintStatus>";
		$this->xml .= "<complaint_type>" . $fields['typeOfComplaint'] . "</complaint_type>\n";	
		$this->xml .= !$fieldsEval['complaintId'] ?"<evaluationStatus>true</evaluationStatus>\n":"<evaluationStatus>false</evaluationStatus>";
		$this->xml .= !$fieldsConc['complaintId'] ?"<conclusionStatus>true</conclusionStatus>\n":"<conclusionStatus>false</conclusionStatus>";
		$this->xml .= !$fields['id'] ? "<id>" . $id . "</id>" : "";
		$this->xml .= "<credit_authorised>Incomplete</credit_authorised>";
		
		
		
		
		$this->xml .= "</toolBoxMain>";
		
		return $this->xml;
	}
}

?>