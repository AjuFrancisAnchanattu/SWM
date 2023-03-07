<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 11/02/2009
 */
class usefulLinks extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	private $application = "useful_links";
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->application));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<usefullinks>";
		
		if(currentuser::getInstance()->hasPermission("webex"))
		{
			$this->xml .= "<webex>true</webex>";
		}
		
		
		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM usefulLinks WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY description DESC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<userLink>";
			
			$this->xml .= "<urlLink>" . urldecode($fields['url']) . "</urlLink>";
			$this->xml .= "<descLink>" . $fields['description'] . "</descLink>";
			$this->xml .= "<icon>" . $fields['icon'] . "</icon>";
			
			$this->xml .= "</userLink>";
		}
		
		
		$this->xml .= "</usefullinks>";
		
		return $this->xml;
	}
}

?>