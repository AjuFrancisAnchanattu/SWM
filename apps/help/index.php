<?php
/**
 * @package apps
 * @subpackage Help
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 12/03/2009
 */
class index extends page 
{
	public $type;
	public $language = array('ENGLISH', 'FRENCH', 'GERMAN', 'ITALIAN', 'SPANISH');
	
	function __construct()
	{
		parent::__construct();
		
		$this->setActivityLocation('Help');
		
		$this->setDebug(true);
		
		$this->header->setLocation($this->getActivityLocation());
		
		
		$this->header->setLocation($this->getActivityLocation());
		$this->header->setMenuXML("./apps/help/xml/menu.xml");
		
		$this->xml .= "<helpHome>";
		
		
		$snapins = new snapinGroup('usermanager_left');
		$snapins->register('apps/help', 'appsSnapin', true, true);
		
		$this->xml .= "<snapin_left>" . $snapins->getOutput() . "</snapin_left>";
		
		if(isset($_REQUEST['type']) && $_REQUEST['type'] != "")
		{
			$this->type = $_REQUEST['type'];
		}
	

		
		if(isset($this->type))
		{
			$this->xml .= "<applicationList>";
			
			$this->xml .= "<type>" . $this->type . "</type>";
			
			
			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute("SELECT * FROM help WHERE type='" . $this->type . "' ORDER BY app");
			
			while($fieldset = mysql_fetch_array($dataset))
			{
				$this->xml .= "<applicationLine>";
				
				$this->xml .= "<app>" . $fieldset['app'] . "</app>";
				
				foreach($this->language AS $val)
				{
					if($fieldset[$val] != "")
					{
						$this->xml .= "<".$val.">true</".$val.">";
					}
					if(file_exists("./apps/help/flash/" . $fieldset['type'] . "/" . $fieldset['app'] . "/" . $fieldset['app'] . "_" . $val . ".swf"))
					{
						$this->xml .= "<file_".$val.">true</file_".$val.">";
					}
				}
				
				$this->xml .= "</applicationLine>";
			}
			
			$this->xml .= "</applicationList>";
		
			
		
		}
		else 
		{
			$this->xml .="<helpText>";
			
			$this->xml .="<ds>fsd</ds>";
			
			$this->xml .="</helpText>";
			
			
		}
		
		
		
		
		
		$this->add_output($this->xml);
		$this->add_output("</helpHome>");
		$this->output('./apps/help/xsl/index.xsl');
		
		
			
	}
}

?>