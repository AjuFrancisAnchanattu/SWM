<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 13/02/2008
 * @todo This snapin needs a description or may need to be deleted?
 */


class addComplaint extends snapin
{
	private $server;
	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("add_complaint_type"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		$this->server = $_SERVER['HTTP_HOST'];
	}

	public function output()
	{
		$this->xml .= "<complaintsAdd server='" . $this->server . "'></complaintsAdd>";

		return $this->xml;
	}
}

?>