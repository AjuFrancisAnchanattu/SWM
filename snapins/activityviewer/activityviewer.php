<?php
/**
 * This is a snapin which shows who is on the intranet and where on the intranet they are.  
 * It also displays the user's locale and I.P. address.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 */
class activityviewer extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("ACTIVITY_VIEWER"));
		$this->setClass(__CLASS__);
		$this->setLocaleDisallowed(array('FRANCE'));
		//$this->setShowHelp(true);
	}
	
	public function output()
	{		
		$this->xml .= "<activityviewer>";
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM employee WHERE LastActivity > '" . date("Y/m/d H:i:s", time()-600) . "' ORDER BY LastActivity DESC");
		
		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<user>";
		    $this->xml .= "<ntlogon>" . page::xmlentities($fields['NTLogon']) . "</ntlogon>\n";
		    $this->xml .= "<name>" . page::xmlentities($fields['firstName'] . " " . $fields['lastName']) . "</name>\n";
		    $this->xml .= "<email>" . page::xmlentities($fields['email']) . "</email>\n";
		    $this->xml .= "<country>" . $fields['locale'] . "</country>\n";
		    $this->xml .= "<photo>" . ($fields['photo']=='1' ? 'yes' : 'no') . "</photo>\n";
		    $this->xml .= "<ip>" . $fields['lastIP'] . "</ip>\n";
		    $this->xml .= "<application>" . $fields['lastLocation'] . "</application>\n";
		    $this->xml .= "</user>";
		}
		
		$this->xml .= "<numOfUsers><count>" . mysql_num_rows($dataset) . "</count></numOfUsers>";
		
		$this->xml .= "</activityviewer>";
		
		return $this->xml;
	}
}

?>