<?php

/**
 * @package Complaints - Customer
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 02/11/2010
 */
class ceoCountdown extends snapin
{

	function __construct()
	{
		$this->setName("Deadline Countdown");
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}


	public function output()
	{
		$this->xml .= "<ceoCountdown>";
		
		$currentYear = (int)date("Y");
		$currentMonth = (int)date("m");
		$currentDay = (int)date("d");
		$currentHour = (int)date("G");
		
		if ($currentYear > 2011 || $currentMonth > 11 || ($currentDay >= 23 && $currentHour >= 8))
		{
			$this->xml .= "<deadlineMet>true</deadlineMet>";
		}
		else
		{
			$this->xml .= "<deadlineMet>false</deadlineMet>";
		}
			
		
		$this->xml .= "</ceoCountdown>";
		
		return $this->xml;
	}

}

?>