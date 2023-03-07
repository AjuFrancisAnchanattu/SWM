<?php

class appraisalRedirect extends page
{
	private $currentUser;
	private $sfURL;
	private $sfPassword;
	private $sfToken = "p3rf0rmanc3mang3r"; // Success Factors Generated Token
	private $expire;
	private $secretKey = "5capa53cr3tk3y"; // Success Factors Generated Secret Key
	private $callerhash;
	private $success = 0; // Was the login successful or not.

	function __construct()
	{
		page::redirect("http://scapaconnect/Apps/MyPerformance/Default.aspx");
		die();
		
		$this->expire = date("Y") . "-" . date("m") . "-" . date("d") . "T" . date("H") . ":" . date("i") . ":" . date("s");

		$this->sfURL = "https://performancemanager.successfactors.eu/login?company=Scapa";

		$this->currentUser = currentuser::getInstance()->getNTLogon();
		//$this->sfPassword = currentuser::getInstance()->getNTLogon();
		$this->sfPassword = "Scapa1";

		$this->callerhash = md5($this->currentUser . $this->expire . $this->sfToken . $this->secretKey);

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT NTLogon FROM employee WHERE NTLogon = '" . $this->currentUser . "'");

		if(mysql_num_rows($dataset) > 0)
		{
			$this->success = 1;
			$this->appraisalActivityLog();
			$this->doRedirect();
		}
		else
		{
			$this->success = 0;
			$this->appraisalActivityLog();
			page::redirect("./apps/sf/login?login=false");
		}
	}

	private function doRedirect()
	{
		// Using Token Only Authentication
		//page::redirect($this->sfURL . "&username=" . $this->currentUser . "&password=" . $this->sfPassword . "&tklogin_key=" . $this->sfToken . "");

		// Using MD5 Authentication
		page::redirect($this->sfURL . "&username=" . $this->currentUser . "&password=" . $this->sfPassword . "&tklogin_key=" . $this->sfToken . "&expire=" . $this->expire . "&callerhash=" . $this->callerhash . "");
	}

	private function appraisalActivityLog()
	{
		// Add who was on the system and when
		mysql::getInstance()->selectDatabase("appraisals")->Execute("INSERT INTO successFactorsLog VALUES ('" . currentuser::getInstance()->getNTLogon() .  "', '" . common::nowDateTimeForMysql() . "', " . $this->success . ")");
	}

}


?>