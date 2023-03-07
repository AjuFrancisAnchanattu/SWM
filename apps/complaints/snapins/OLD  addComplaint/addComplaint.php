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
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("add_complaint_type"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);


//		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['typeValue']))
//		{
//			// get anything posted by the form
//
//			if ($_POST['typeValue'] != '')
//			{
//				page::redirect("/apps/complaints/add?typeOfComplaint=" . $_POST['typeValue']);
//			}
//		}
	}

	public function output()
	{
		$this->xml .= "<complaintsAdd>";

		$this->xml .= "</complaintsAdd>";

		return $this->xml;
	}
}

?>