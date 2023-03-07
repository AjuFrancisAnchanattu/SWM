<?php
/**
 * This is a snapin that displays an employee's details.
 * By default it shows the current user's details, but any user's details can be brought up within this snapin.
 * It displays the employee's name, email, phone number, fax number, language, local, site, department, role and a photograph of them.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Dan Eltis
 * @version 01/02/2006
 * @todo Make it possible to view anyone's details.
 * @todo Make it only show the photograph if the user viewing them has also submitted one.
 */
class addressbook extends snapin
{

	private $form;
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("ADDRESS_BOOK"));
		$this->setClass(__CLASS__);
	}

	public function output()
	{

		page::setDebug(true);

		if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_REQUEST['employee']) && !empty($_REQUEST['employee']))
		{
			//$user =  user::getNTLoginFromName(isset($_REQUEST['employee']) ? $_REQUEST['employee'] : currentuser::getInstance()->getNTLogon());
			page::redirect("/home/index?person=" . user::getNTLoginFromName($_REQUEST['employee']));
		}
		else
		{
			$user = isset($_REQUEST['person']) ? $_REQUEST['person'] : currentuser::getInstance()->getNTLogon();
		}

		$this->xml .= sprintf("<addressbook canImpersonate=\"%s\" isImpersonating=\"%s\">",
			currentuser::getInstance()->isAdmin() && $user != currentuser::getInstance()->getNTLogon()  ? "true" : "false",
			isset($_SESSION['impersonate']) && $user == currentuser::getInstance()->getNTLogon() ? "true" : "false"
		);


		//$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM employee WHERE NTLogon='$user'");

		$person = new user();
		$person->load($user);

		if ($person->isValid())
		{
		    $this->xml .= "<ntlogon>" . $person->getNTLogon() . "</ntlogon>\n";
		    $this->xml .= "<name>" . $person->getName() . "</name>\n";
		    $this->xml .= "<email>" . $person->getEmail() . "</email>\n";
		    $this->xml .= "<phone>" . ($person->getPhone() != "" ? $person->getPhone() : '-'). "</phone>\n";
		    $this->xml .= "<fax>" . ($person->getFax() != "" ? $person->getFax() : '-') . "</fax>\n";
		    $this->xml .= "<language>" . translate::getInstance()->translate(strtolower($person->getLanguage())) . "</language>\n";
		    $this->xml .= "<locale>" . translate::getInstance()->translate(strtolower($person->getLocale())) . "</locale>\n";
		    $this->xml .= "<site>" . $person->getSite() . "</site>\n";
		    $this->xml .= "<photo>no</photo>\n";
		    $this->xml .= "<department>-</department>\n";
		    $this->xml .= "<role>-</role>\n";
		}
		else
		{
			$this->xml .= "<ntlogon></ntlogon>\n";
		    $this->xml .= "<name>" . translate::getInstance()->translate("user_not_found") . "</name>\n";
		    $this->xml .= "<email>-</email>\n";
		    $this->xml .= "<phone>-</phone>\n";
		    $this->xml .= "<fax>-</fax>\n";
		    $this->xml .= "<language>-</language>\n";
		    $this->xml .= "<locale>-</locale>\n";
		    $this->xml .= "<site>-</site>\n";
		    $this->xml .= "<photo>no</photo>\n";
		    $this->xml .= "<department>-</department>\n";
		    $this->xml .= "<role></role>\n";
		}

		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT NTLogon, firstName, lastName, locale FROM employee ORDER BY firstName");

		$column1 = array();
		$column2 = array();
		$column3 = array();

		$current = 1;

		while ($fields = mysql_fetch_array($dataset))
		{
			switch ($current)
			{
				case 1:
					array_push($column1, $fields);
					$current = 2;
					break;

				case 2:
					array_push($column2, $fields);
					$current = 3;
					break;

				case 3:
					array_push($column3, $fields);
					$current = 1;
					break;
			}
		}

		/*$this->xml .= "<contactListLeft>";

		for ($i=0; $i < count($column1); $i++)
		{
			$this->xml .= $this->doPersonBit($column1[$i]);
		}

		$this->xml .= "</contactListLeft>\n<contactListMiddle>";

		for ($i=0; $i < count($column2); $i++)
		{
			$this->xml .= $this->doPersonBit($column2[$i]);
		}

		$this->xml .= "</contactListMiddle>\n<contactListRight>";

		for ($i=0; $i < count($column3); $i++)
		{
			$this->xml .= $this->doPersonBit($column3[$i]);
		}

		$this->xml .= "</contactListRight>";	*/
		$this->xml .= "</addressbook>";

		return $this->xml;
	}

	/*

	function doPersonBit($fields)
	{
		return ("<contactPerson ntlogon=\"" . page::xmlentities($fields['NTLogon']) . "\" country=\"" . $fields['locale'] . "\">". page::xmlentities($fields['firstName'] . " ". $fields['lastName']) . "</contactPerson>\n");
	}
	*/


}

?>