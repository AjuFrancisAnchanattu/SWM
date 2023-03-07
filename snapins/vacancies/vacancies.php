<?php
/**
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 29/10/2009
 */
class vacancies extends snapin 
{	
	/**
	 * @param string $area the area of the screen the snapin should appear in
	 */
	private $application = "vacancies";
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate($this->application));
		$this->setClass(__CLASS__);
		//$this->setCanClose(false);
	}
	
	public function output()
	{		
		$this->xml .= "<vacancies>";			
		
		$this->xml .= "<snapin_name>" . $this->application . "</snapin_name>";
		
		$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM vacancies WHERE published = 1 ORDER BY id DESC");
		
		while($fields = mysql_fetch_array($dataset))
		{
			$this->xml .= "<vacancy_details>";
			
				$this->xml .= "<id>" . $fields['id'] . "</id>";
				$this->xml .= "<jobTitle>" . substr(page::xmlentities($fields['JobTitle']), 0, 30) . "</jobTitle>";
				$this->xml .= "<location>" . page::xmlentities($fields['Location']) . "</location>";
				$this->xml .= "<nameOfHiringManager>" . page::xmlentities($fields['NameOfHiringManager']) . "</nameOfHiringManager>";
				$this->xml .= "<closingDate>" . common::transformDateForPHP($fields['ClosingDate']) . "</closingDate>";
			
			$this->xml .= "</vacancy_details>";
		}
		
		if(mysql_num_rows($dataset) == 0)
		{
			$this->xml .= "<vacancyToShow>0</vacancyToShow>";
		}
		else
		{
			$this->xml .= "<vacancyToShow>1</vacancyToShow>";
		}
		
		$this->xml .= "</vacancies>";
		
		return $this->xml;
	}
}

?>