<?php
/**
 * This is a snapin which displays an employees CCRs (Customer Contact Reports).
 * It shows what reports they have open, the company name of the report and the date on which the CCR was created.
 * This version of the snapin is NOT closable by the user, and is for the CCR application of the intranet.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 */
class reports extends snapin
{
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("your_ijf_reports"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}

	public function output()
	{
		$ijfCount = 0;

		$this->xml .= "<ijfReports>";

		if(!isset($_REQUEST["viewReport"]))
		{
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id, owner, status, initiatorInfo FROM ijf WHERE initiatorInfo = '" . currentuser::getInstance()->getNTLogon() . "' AND status != 'complete' ORDER BY id DESC LIMIT 10");
		}
		elseif($_REQUEST["viewReport"] == 'inProgress')
		{
			$dataset = mysql::getInstance()->selectDatabase("IJF")->Execute("SELECT id, owner, status, initiatorInfo FROM ijf WHERE initiatorInfo ='" . currentuser::getInstance()->getNTLogon() . "' AND status != 'complete' ORDER BY id DESC LIMIT 10");
		}

		while ($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<ijf_Report>";
			$this->xml .= "<id>" . $fields['id'] . "</id>\n";
			$this->xml .= "<owner>" . usercache::getInstance()->get(page::xmlentities($fields['owner']))->getName() . "</owner>";
            $this->xml .= "<status>" . translate::getInstance()->translate($fields['status']) . "</status>";
            $this->xml .= "</ijf_Report>";
            $ijfCount++;
		}

		$this->xml .= "<reportCount>" . $ijfCount . "</reportCount>";
		$this->xml .= "</ijfReports>";

		return $this->xml;
	}
}

?>