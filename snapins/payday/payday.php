<?php
/**
 * This is a snapin that allows the admin to keep track of the day they get paid.  
 * It displays how many days are left until their bank balances increase.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class payday extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("DAYS_UNTIL_PAYDAY"));
		$this->setClass(__CLASS__);
		$this->setPermissionsAllowed(array('admin'));
	}
	
	public function output()
	{		
		$this->xml .= "<payday>";
				
		$now_day = (int)date("j");
		$now_month = (int)date("n");
		$pay_day = 15;
		
		$pay_month = ($now_day <= $pay_day) ? (int)date("n") : (int)date("n")+1;
			
		$now_date = mktime(0,0,0,$now_month,$now_day);
		$pay_date = mktime(0,0,0,$pay_month,$pay_day);
		
		$daysTilPayDay = date("j",($pay_date - ($now_date))) -1;
		
		$this->xml .= "<days>$daysTilPayDay</days>\n";
		
		$this->xml .= "</payday>";
		
		return $this->xml;
	}
}

?>