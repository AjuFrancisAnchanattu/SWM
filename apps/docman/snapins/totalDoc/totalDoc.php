<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 10/05/2006
 * @todo This snapin needs a description or may need to be deleted?
 */


class totalDoc extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("total_doc"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		
		
	}
	
	public function output()
	{		
		$datasetCount = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT count(*) AS `total_documents` FROM documents");
		$fieldsCount = mysql_fetch_array($datasetCount);
		
		$dataset = mysql::getInstance()->selectDatabase("DocMan")->Execute("SELECT `docName`, `creator`, `date`, `docSource`, `serverPath` FROM documents ORDER BY `id` LIMIT 1");
		$fields = mysql_fetch_array($dataset);
		
		$this->xml .= "<totalDoc>";
			$this->xml .= "<total_documents>" . $fieldsCount['total_documents'] . "</total_documents>";
			$this->xml .= "<last_added>" . $fields['docName'] . "</last_added>";
			$this->xml .= "<last_creator>" . usercache::getInstance()->get($fields['creator'])->getName() . "</last_creator>";
			$this->xml .= "<creation_date>" . $fields['date'] . "</creation_date>";
			$this->xml .= "<link>" . $fields['serverPath'] . "</link>";
		$this->xml .= "</totalDoc>";
		
		return $this->xml;
	}
}

?>