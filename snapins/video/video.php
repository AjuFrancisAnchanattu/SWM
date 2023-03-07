<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/01/2009
 */
class video extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("videos"));
		$this->setClass(__CLASS__);
		//$this->setCanClose(false);
	}
	
	public function output()
	{				
		$this->xml .= "<videoHome>";
		
		$dataset = mysql::getInstance()->selectDatabase("videos")->Execute("SELECT * FROM video ORDER BY id DESC LIMIT 1");
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<videoName>" . $fields['videoName'] . "</videoName>";
		$this->xml .= "<videoSrc>" . $fields['videoSrc'] . "</videoSrc>";
		$this->xml .= "<videoLocation>" . $fields['videoLocation'] . "</videoLocation>";
		
		$this->xml .= "</videoHome>";
		
		return $this->xml;
	}

}

?>