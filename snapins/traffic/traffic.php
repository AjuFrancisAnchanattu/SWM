<?php
/**
 * This is a snapin that displays the lastest updates from the BBC News webiste.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 04/12/2008
 */
class traffic extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	private $traffic = array();
	
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("TRAFFIC"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$NUMFEEDS = 10;
		
		$this->xml .= "<trafficFeed>";
		
		$contents = cache::getLocalDocument("./snapins/traffic/traffic.xml");
		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML($contents);
		$results = $xmlDoc->getElementsByTagName('item');
		
		$i = 0;
		
		$this->xml .= "<feedType>";
		
		foreach ($results as $result)
		{
			$this->traffic[] = array('feedURL' => $result->getAttribute('feedURL'), 'feedName' => $result->getAttribute('feedName'), 'googleMapLink' => $result->getAttribute('googleMapLink'));
			$this->xml .= "<feed><feedNumber>tf" . $i++ . "</feedNumber><name>" . $result->getAttribute('feedName') . "</name></feed>";
		}
		
		$this->xml .= "</feedType>";
		
		isset($_REQUEST['trafficFeed']) ? $feedNumber = ereg_replace('tf', '', $_REQUEST['trafficFeed']) : $feedNumber= 0;
		
		$xml = simplexml_load_file($this->traffic[$feedNumber]['feedURL']) or die("feed not loading");		
		
		
		$this->xml .= "<feedURL>" . $this->traffic[$feedNumber]['feedURL'] . "</feedURL>";
		$this->xml .= "<googleMapLink>" . ereg_replace('&', '&amp;', $this->traffic[$feedNumber]['googleMapLink']) . "</googleMapLink>";
		
		


		$this->xml .= "<title>" .  $xml->channel->title;
		
		if($feedNumber == 0)
		{
			$this->xml  .= " (5 Latest).";
		}

		$this->xml .= "</title>";
		
//		$this->xml .= "<googleMapLink>" . str_ireplace("&","&amp;",$this->traffic[$feedNumber]['googleMapLink']) . "</googleMapLink>";
//
//		echo ":: " .$this->traffic[$feedNumber]['googleMapLink'];
//		
		$i=0;
		
		while($xml->channel->item[$i])
		{
			$this->xml .= "<feedItem>";
			
			$this->xml .= "<id>" . $i . "</id>";
			$this->xml .= "<itemLocation>" . substr($xml->channel->item[$i]->title, 0, strpos($xml->channel->item[$i]->title, "|")-1) . "</itemLocation>";
			$this->xml .= "<itemDirection>" . substr(substr($xml->channel->item[$i]->title, strpos($xml->channel->item[$i]->title, "|")+1), 0, strpos(substr($xml->channel->item[$i]->title, strpos($xml->channel->item[$i]->title, "|")+1), "|")-1) . "</itemDirection>";
			$this->xml .= "<itemReason>" . $xml->channel->item[$i]->category . "</itemReason>";
			
			$this->xml .= "<itemDescription>" . $xml->channel->item[$i]->description . "</itemDescription>";
			$this->xml .= "<itemPubDate>" . substr($xml->channel->item[$i]->pubDate, 0, -4) . "</itemPubDate>";
			$this->xml .= "<itemLink>" . ereg_replace('&', '&amp;', $xml->channel->item[$i]->link) . "</itemLink>";
			
			
			$this->xml .= "</feedItem>";
			$i++;
			
			if($feedNumber==0 && $i>4)
			{
				break;
			}
		}
		
		$this->xml .= "<notificationCount>" . $i . "</notificationCount>";
		
		$this->xml .= "</trafficFeed>";
		
		return $this->xml;
	}
}

?>