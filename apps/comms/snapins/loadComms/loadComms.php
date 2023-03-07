<?php
/**
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 06/04/2009
 */
class loadComms extends snapin 
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("news_load"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['commsId']))
		{
			// get anything posted by the form
			
			if ($_POST['commsId'] != '')
			{
				page::redirect("/apps/comms/viewArticle?id=" . $_POST['commsId']);
			}
		}
	}
	
	public function output()
	{				
		
		$this->xml .= "<loadComms>";
		
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm ORDER BY openDate DESC");
		}
		else 
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE newsType=1 ORDER BY openDate DESC");
		}
		
		while($fieldset = mysql_fetch_array($dataset))
		{
			$this->xml .= "<articleList>";
			
			$this->xml .= "<articleSubject>" . $fieldset['subject'] . "</articleSubject>";
			$this->xml .= "<articleId>" . $fieldset['id'] . "</articleId>";
			
			$this->xml .= "</articleList>";
		
		}
		
		
		$this->xml .= "</loadComms>";
		
		return $this->xml;
	}
}

?>