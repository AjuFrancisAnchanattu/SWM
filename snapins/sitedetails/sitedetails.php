<?php
/**
 * This is a snapin that displays a site's details.  
 * By default it shows the current user's site, but any site's details can be brought up within this snapin.
 * It displays the site's name, email, country, address, phone number and fax number.
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author Ben Pearson
 * @version 01/02/2006
 */
class sitedetails extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("SITE_DETAILS"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
	}
	
	public function output()
	{		
		if (isset($_REQUEST['site']))
		{
			$site = $_REQUEST['site'];
		}
		else
		{
			$site = currentuser::getInstance()->getSite();
		}
		
		
		$this->xml .= "<sitedetails>";
		
		$this->xml .= "<helptext>" . translate::getInstance()->translate("route_help") . "</helptext>";
		
		$this->xml .= "<currentShowing>" . $site . "</currentShowing>";
		
		/*$selSite = new dropdown();
		$selSite->setName("selSite");
		
		$selSite->setData("SELECT site_name AS name, site_name AS data FROM sites ORDER BY site_name ASC");
		$this->xml .= $selSite->output();*/
		
		$dataset = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT * FROM sites WHERE name = '" . $site . "'");
	
		if ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<name>" . $fields['name'] . "</name>\n";
			$this->xml .= "<country>" . translate::getInstance()->translate($fields['country']) . "</country>\n";
			$this->xml .= "<address>" . page::formatAsParagraphs($fields['address']) . "</address>\n";
			$this->xml .= "<phone>" . ($fields['phone']!=' ' ? $fields['phone'] : '-'). "</phone>\n";
			$this->xml .= "<fax>" . ($fields['fax']!=' ' ? $fields['fax'] : '-') . "</fax>\n";
			
			// Need to add in the map file exists here, if does then show the map @ link
			if(file_exists("./snapins/sitedetails/maps/" . $fields['name'] . ".jpg"))
			{
				$this->xml .= "<mapExists>true</mapExists>";				
				$this->xml .= "<map>" . $fields['map'] . "</map>\n";
			}
			else 
			{
				if($fields['map']!="")
				{
					$this->xml .= "<mapExists>default</mapExists>";				
					$this->xml .= "<map>" . $fields['map'] . "</map>\n";
				}
			}
			
			if($fields['longLat'] != "")
			{
				$this->xml .= "<gps>on</gps>";
				$this->xml .= "<routeLink>" . "&amp;daddr=Scapa+" . $fields['country'] . "+(" . $fields['name'] . ")+%40" . $fields['longLat'] . "</routeLink>";
			}
			
			
			
			//$this->xml .= "<small_map>" . ($fields['small_map']!=' ' ? $fields['small_map'] : '-') . "</small_map>\n";
		}
		
		
		
		
		$dataset  = mysql::getInstance()->selectDatabase("membership")->Execute("SELECT name, country FROM sites ORDER BY name ASC");
		$this->xml .= "<siteList>";
		
		while ($fields = mysql_fetch_array($dataset)) 
		{
			$this->xml .= "<site>";
			
			$site == $fields['name'] ? $this->xml .= "<selected>true</selected>" : $this->xml .= "";
			
			$this->xml .= "<name>" . $fields['name'] . "</name>";
			$this->xml .= "<name>" . $fields['name'] . "</name>";
			$this->xml .= "<country>" . $fields['country'] . "</country>\n";
			$this->xml .= "</site>";
		}
		
		$this->xml .= "</siteList>";
		$this->xml .= "</sitedetails>";
		
		return $this->xml;
	}
	
	public function routeLink($site,$country,$url)
	{
		
		$longlat = substr(substr($url, (strpos($url,"ll="))+3), 0, strpos(substr($url, (strpos($url,"ll="))+3), "&amp;spn"));
		
		$longlat = "&amp;daddr=Scapa+" . $country . "+(" . $site . ")+%40" . $longlat;
		
		return $longlat;
	}
}

?>