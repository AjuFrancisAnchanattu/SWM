<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 25/11/2008
 */
class yourappraisal extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("appraisals_you_own"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{				
		$appraisalCount = 0;
		
		$this->xml .= "<appraisalReports>";
		
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM appraisal WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY id DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			
			$this->xml .= "<appraisal_Report>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";		
			$this->xml .= "<person>" . $fields['firstName'] . " " . $fields['surname'] .  "</person>\n";
			$this->xml .= "<site>" . $fields['site'] . "</site>";
            $this->xml .= "</appraisal_Report>";
            
            $appraisalCount++;
		}
		$this->xml .= "<reportCount>" . $appraisalCount . "</reportCount>";

		/* WE AE - 22/01/08 
			added to give the saved forms */
		$savedFormCount = 0;
		$dataset = mysql::getInstance()->selectDatabase("appraisals")->Execute("SELECT * FROM savedForms WHERE `sfOwner` = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY sfDateInsert DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
//			$this->xml .= "<savedappraisal>";
//			$this->xml .= "<savedID>".$fields['sfID']."</savedID>";
//			$this->xml .= "<savedType>".$fields['sfForm']."</savedType>";			
//			$this->xml .= "<savedappraisalID>".$fields['sfappraisalID']."</savedappraisalID>";
//			$this->xml .= "<savedDate>".date("D jS M Y", $fields['sfDateInsert'])."</savedDate>";
//			$this->xml .= "</savedappraisal>";
			$savedFormCount++;
		}
		
		$this->xml .= "<savedReportCount>" . $savedFormCount . "</savedReportCount>";
		/* WC END*/
		$this->xml .= "</appraisalReports>";
		
		return $this->xml;
	}
}

?>