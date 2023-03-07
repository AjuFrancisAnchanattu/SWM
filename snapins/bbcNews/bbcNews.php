<?php
/**
 * This is a snapin that displays the lastest updates from the BBC News webiste.  
 *
 * @package snapins
 * @copyright Scapa Ltd.
 * @author David Pickwell
 * @version 04/12/2008
 */
class bbcNews extends snapin
{	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("NEWS_FEED"));
		$this->setClass(__CLASS__);
	}
	
	public function output()
	{		
		$numFeeds = 5;
		
		$feedURL = array(
			"http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/latest_published_stories/rss.xml",
			"http://rss.cnn.com/rss/cnn_latest.rss",
			"http://www.france24.com/fr/monde/rss",
			"http://www.tagesschau.de/xml/tagesschau-meldungen/",
			"http://www.ansa.it/main/notizie/awnplus/topnews/synd/ansait_awnplus_topnews_medsynd_Today_Idx.xml"
		);
			
		$feedName = array("BBC News (UK)","CNN (US)","France 24 (FR)","Tagesschau (DE)","ANSA top news (IT)");
		
		if (isset($_REQUEST['feed']))
		{
			$requestedFeed = $_REQUEST['feed'];
			$i=0;
			while($feedName[$i] != $requestedFeed)
			{
				$i++;
			}
			$request_url = $feedURL[$i];
		}
		else
		{
			$requestedFeed = $feedName[0];
			$request_url = $feedURL[0];
		}
		
		$xml = simplexml_load_file($request_url) or die("feed not loading");		
		
		$this->xml .= "<newsFeed>";
		$this->xml .= "<numFeeds>" . $numFeeds . "</numFeeds>";
		$this->xml .= "<siteName>" . $requestedFeed . "</siteName>";
		$this->xml .= "<feedURL>" . $request_url ."</feedURL>";
		
		for($i=0 ; $i < $numFeeds; $i++)
		{
			$this->xml .= "<newsItem>";
			$this->xml .= "<title>" . $xml->channel->item[$i]->title . "</title>";
			$this->xml .= "<link>" . $xml->channel->item[$i]->link . "</link>";
			$this->xml .= "<description>" . $xml->channel->item[$i]->description . "</description>";
			$this->xml .= "<pubDate>" . $xml->channel->item[$i]->pubDate . "</pubDate>";
			
			if($i == ($numFeeds-1))
			{
				$this->xml .= "<lastRow>True</lastRow>";
			}
			
			$this->xml .= "</newsItem>";
		}
		
		$this->xml .= "<feedType>";
		
		for($i=0; $i < count($feedURL); $i++) 
		{
			$this->xml .= "<feed><name>" . $feedName[$i] . "</name></feed>";
		}
		
		$this->xml .= "</feedType>";
		$this->xml .= "</newsFeed>";
		
		return $this->xml;
	}
}

?>