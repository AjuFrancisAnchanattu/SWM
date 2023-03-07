<?php

/**
 * @package Complaints - Customer
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 02/11/2010
 */
class ceoInfo extends snapin
{

	function __construct()
	{
		$this->setName("CEO Awards Information");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}


	public function output()
	{
		$this->xml .= "<ceoInfo></ceoInfo>";

		return $this->xml;
	}

}

?>