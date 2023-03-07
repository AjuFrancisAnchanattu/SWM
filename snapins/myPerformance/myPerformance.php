<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/05/2009
 */
class myPerformance extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	private $application = "my_performance";
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->application));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$this->xml .= "<myPerformance>";
		
		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";
		
		$this->xml .= "</myPerformance>";
		
		return $this->xml;
	}
}

?>