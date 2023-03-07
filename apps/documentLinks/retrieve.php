<?php
//require 'lib/support.php';

/**
 * This is the documentLinks Application.
 * 
 * @package intranet	
 * @subpackage documentLinks
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 22/05/2009
 */
class retrieve extends page 
{
	private $docId;
	
	function __construct()
	{
		parent::__construct();
		
		if(isset($_REQUEST['docId']) && $_REQUEST['docId'] != "")
		{
			$this->docId = $_REQUEST['docId'];
		}
		else 
		{
			page::redirect("./errorpages/noIDError.htm");		
		}
		
		$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM links WHERE id = " . $this->docId);

		if(mysql_num_rows($dataset) == 0)
		{
			page::redirect("./errorpages/404error.htm");
		}
		
		$fields = mysql_fetch_array($dataset);
		
		echo "http://" . $_SERVER['HTTP_HOST'] . "/data/docs/" . $fields['id'] . "/" . $fields['filename'];
		
		//$file = "http://" . $_SERVER['HTTP_HOST'] . $fields['path'] . "/" . $fields['filename'];
		$file = "http://" . $_SERVER['HTTP_HOST'] . "/data/docs/" . $fields['id'] . "/" . $fields['filename'];
		
		page::redirect($file);
	}
}

?>