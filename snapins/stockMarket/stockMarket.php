<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason matthews
 * @version 12/02/2008
 */
class stockmarket extends snapin
{	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("stock_market_information"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<stockmarket>";
		
		$this->xml .= "</stockmarket>";

		return $this->xml;
	}
		
}

?>