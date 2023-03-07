<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 12/11/2009
 */
class employeeSurvey extends snapin
{
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	private $application = "scapa_monthly_employee_news";

	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->application));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->setColourScheme("title-box1");
	}

	public function output()
	{
		$this->xml .= "<employeeSurvey>";

		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";

		$this->xml .= "</employeeSurvey>";

		return $this->xml;
	}
}

?>