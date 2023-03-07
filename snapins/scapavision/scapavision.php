<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 04/03/2009
 */
class scapavision extends snapin
{
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("scapa_vision"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->setColourScheme("title-boxgrey");
	}

	public function output()
	{
		$this->xml .= "<scapavision>";

		// Set the Scapa Vision based on Language
		if(usercache::getInstance()->get(currentuser::getInstance()->getNTLogon())->getLanguage() != "ENGLISH")
		{
			$this->xml .= "<translate>true</translate>";
		}

		// Scapa Vision Translations
		$this->xml .= "<world_class_description>" . translate::getInstance()->translate("world_class_description") . "</world_class_description>";
		$this->xml .= "<inspired_description>" . translate::getInstance()->translate("inspired_description") . "</inspired_description>";
		$this->xml .= "<market_driven_description>" . translate::getInstance()->translate("market_driven_description") . "</market_driven_description>";
		$this->xml .= "<team_description>" . translate::getInstance()->translate("team_description") . "</team_description>";
		$this->xml .= "<value_description>" . translate::getInstance()->translate("value_description") . "</value_description>";
		$this->xml .= "<responsible_description>" . translate::getInstance()->translate("responsible_description") . "</responsible_description>";
		$this->xml .= "<agile_description>" . translate::getInstance()->translate("agile_description") . "</agile_description>";
		$this->xml .= "<tape_solutions_description>" . translate::getInstance()->translate("tape_solutions_description") . "</tape_solutions_description>";

		$this->xml .= "</scapavision>";

		return $this->xml;
	}
}

?>