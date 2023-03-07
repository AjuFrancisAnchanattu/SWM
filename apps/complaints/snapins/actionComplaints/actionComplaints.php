<?php
/**  
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/05/2006
 */
class actionComplaints extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_complaint_actions"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$open = array();
		$actionCount = 0;
		$this->xml .= "<complaintsActions>";
	
		$dataset = mysql::getInstance()->selectDatabase("complaints")->Execute("SELECT `id`, `status`, `internalSalesName` FROM `complaint` WHERE `owner` = '" . currentuser::getInstance()->getNTLogon() . "' AND `status` != 'complete' ORDER BY id DESC");
		

		while ($fields = mysql_fetch_array($dataset))
		{
				$open[] = $fields;
		}
		
		for ($i=0; $i < count($open); $i++)
		{			
			$this->xml .= "<complaints_Action>";
			$this->xml .= "<id>" . $open[$i]['id'] . "</id>\n";
			$this->xml .= "<link>" . $open[$i]['status'] . "</link>\n";	
			//$this->xml .= "<initiatorInfo>" . usercache::getInstance()->get(page::xmlentities($open[$i]['initiatorInfo']))->getName() . "</initiatorInfo>";
            $this->xml .= "<status>" . translate::getInstance()->translate($open[$i]['status']) . "</status>";
            $this->xml .= "</complaints_Action>";
            $actionCount++;
            
		}
		$this->xml .= "<actionCount>" . $actionCount . "</actionCount>";
		$this->xml .= "</complaintsActions>";
		
		return $this->xml;
	}
}

?>