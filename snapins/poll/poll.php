<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 09/07/2009
 */
class poll extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	private $application = "scapa_poll";
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->application));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$this->xml .= "<poll>";
		
		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";
		
		$this->like = 180;
		
		$this->dontLike = 30;
		
		$this->na = 90;
		
		$this->xml .= "<widthValueLike>" . $this->like . "</widthValueLike>";
		$this->xml .= "<widthValueDontLike>" . $this->dontLike . "</widthValueDontLike>";
		$this->xml .= "<widthValueNA>" . $this->na . "</widthValueNA>";
		
		
		
		//		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM usefulLinks WHERE NTLogon = '" . currentuser::getInstance()->getNTLogon() . "' ORDER BY description DESC");
//		
//		while($fields = mysql_fetch_array($dataset))
//		{
//			$this->xml .= "<userLink>";
//			
//			$this->xml .= "<urlLink>" . urldecode($fields['url']) . "</urlLink>";
//			$this->xml .= "<descLink>" . $fields['description'] . "</descLink>";
//			$this->xml .= "<icon>" . $fields['icon'] . "</icon>";
//			
//			$this->xml .= "</userLink>";
//		}
		
		
		$this->xml .= "</poll>";
		
		return $this->xml;
	}
}

?>