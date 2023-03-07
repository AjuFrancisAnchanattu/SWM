<?php

class UpdateAD extends page
{
	private $currentUserDN;

	function __construct()
	{
		parent::__construct();

		$this->setDebug(true);

		if(isset($_GET['username']))
		{
			$ntLogon = $_GET['username'];

			if(currentuser::getInstance()->getNTLogon() == $ntLogon)
			{
				$this->getCurrentUserDN($ntLogon);

				$ad = ldap_connect("10.14.199.111");

			    if(!$ad)
			    {
			    	$ad = ldap_connect("10.14.199.113")
			          or die("Couldn't connect to UKASHDCF011 or UKASHDCX012 AD!");
			    }

			    ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);

			    $bd = ldap_bind($ad,"kayako@scapa.local","8p0WHaw")
						or die("Couldn't bind to AD!");

			    $user = array();
			    $job = "-";
			    $site = "-";
			    $department = "-";
				$phone = "-";
			    $mobile = "-";
				$fax = "-";

				if(isset($_GET['job']))
				{
					$job = $_GET['job'];
				}

				if(isset($_GET['site']))
				{
					$site = $_GET['site'];
				}

				if(isset($_GET['dept']))
				{
					$department = $_GET['dept'];
				}

				if(isset($_GET['phone']))
				{
					$phone = $_GET['phone'];
				}

				if(isset($_GET['mobile']))
				{
					$mobile = $_GET['mobile'];
				}

				if(isset($_GET['fax']))
				{
					if($_GET['fax'] != "")
					{
						$fax = $_GET['fax'];
					}
				}

				$user["telephonenumber"] = str_replace("%20", " ", $phone);
				$user["facsimileTelephoneNumber"] = str_replace("%20", " ", $fax);
				$user["physicalDeliveryOfficeName"] = str_replace("%20", " ", $site);
				$user["mobile"] = str_replace("%20", " ", $mobile);
				$user["title"] = str_replace("%20", " ", $job);
				$user["department"] = str_replace("%20", " ", $department);

			    $result = ldap_mod_replace($ad, $this->currentUserDN, $user);

				$from = ""; // this page was called from

				if(isset($_GET['from']))
				{
					$from = $_GET['from'];

					if($from == "welcome")
					{
						if($result)
						{
							ldap_unbind($ad);
							page::redirect("http://scapaconnect/Pages/Welcome/WhatsNew.aspx");
						 }
						 else
						{
							 ldap_unbind($ad);
							 page::redirect("http://scapaconnect/Pages/Welcome/Default.aspx?failed=true");
						}
					}
					else if($from == "profile")
					{
						if($result)
						{
							ldap_unbind($ad);
							page::redirect("http://scapaconnect/EditProfile.aspx?saved=true");
						 }
						 else
						{
							 ldap_unbind($ad);
							 page::redirect("http://scapaconnect/EditProfile.aspx?saved=false");
						}
					}
					else
					{
						ldap_unbind($ad);
						die("from not set");
					}
				}
			}
		}
		else
		{
			echo "No Username given";
		}
	}


	private function getCurrentUserDN($NTLogon)
	{
		$ds = ldap_connect("10.14.199.111");

	    if(!$ds)
	    {
	    	$ds = ldap_connect("10.14.199.113")
	          or die("Couldn't connect to UKASHDCF011 or UKASHDCX012 AD!");
	    }

		$dn[]='DC=scapa,DC=local';

		$id[] = $ds;

		$filter = "samaccountname=$NTLogon";

		$ldapBind = ldap_bind($ds,"kayako","8p0WHaw");

		if (!$ldapBind)
		{
			die('Cannot Bind to LDAP server');
		}

		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

		$result = ldap_search($id,$dn,$filter);

		$search = false;

		foreach ($result as $value)
		{
		    if(ldap_count_entries($ds,$value)>0)
		    {
		        $search = $value;
		        break;
		    }
		}

		if($search)
		{
		    $info = ldap_get_entries($ds, $search);
		}
		else
		{
		    $info = false;
		}

		if($info != false)
		{
			$this->currentUserDN = htmlentities($info[0]["dn"]);
		}
		else
		{
			$this->currentUserDN = "";
		}

		ldap_unbind($ds);
	}
}


?>