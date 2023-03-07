<?php
/**
 * This is a snapin that displays Notification from employees.  
 * An employee can submit a notification into the system, which notifys the admin, who can then check if the notification is appropiate.  Admin can allow or reject a notification using this snapin.
 * The notifications are stored in the database "membership" and the table "notifications".
 *
 * @package snapins
 * @subpackage notifications
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 24/07/2006
 */
class news extends snapin
{
	private $pageAction;
	
	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("news"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		
		if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_REQUEST['searchTerm']) && !empty($_REQUEST['searchTerm']))
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT id FROM comm WHERE subject = '" . $_REQUEST['searchTerm'] . "'");
			
			$fields = mysql_fetch_array($dataset);
			
			page::redirect("/apps/comms/viewArticle?id=" . $fields['id']);
		}
	}
	
	public function output()
	{		
		
		$adminNotificationCount = 0;
		$notificationCount = 0;
			
		$this->xml .= "<news>";
		
		// Hide or Show the Add News Links
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->xml .= "<commAdmin>true</commAdmin>";
		}
		
		$doc = new DOMDocument();
		
		// Load the RSS Feed URL (scapa.com)
		$doc->load('http://www.scapa.com/en/news_rss.xml');
		
		$arrFeeds = array();
		
		foreach ($doc->getElementsByTagName('item') as $node) 
		{
			$itemRSS = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue
				);
			array_push($arrFeeds, $itemRSS);
		}
		
		
		$numOfThreads = count($arrFeeds) - 1;
		$numOfReadableThreds = 5;
		
		for ($i = 0; $i < $numOfReadableThreds; $i++)
		{
			$this->xml .= "<rssFeed>";
			
			$this->xml .= "<title>" . $arrFeeds[$i]['title'] . "</title>";
			$this->xml .= "<description>" . $arrFeeds[$i]['desc'] . "</description>";
			$this->xml .= "<link>" . $arrFeeds[$i]['link'] . "</link>";
			$this->xml .= "<date>" . common::transformDateTimeForPHP($arrFeeds[$i]['date']) . "</date>";
			
			$daysSincePub = page::getTimeDifference($arrFeeds[$i]['date'], page::nowDateTimeForMysql());
			
			if($daysSincePub['days'] < 14)
			{
				$this->xml .= "<daysSincePub>new</daysSincePub>";
			}
			
			$this->xml .= "</rssFeed>";
		}
		
		// Scapa Comm News		
		$datasetCommsNews = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE newsType = 1 ORDER BY openDate DESC LIMIT 5");
		
		if(mysql_num_rows($datasetCommsNews) > 0)
		{			
			$this->xml .= "<numOfNewsFeeds>1</numOfNewsFeeds>";
			
			while($fieldsCommsNews = mysql_fetch_array($datasetCommsNews))
			{
				$this->xml .= "<scapaNewsFeed>";
				
				$daysSincePubCommsNews = page::getTimeDifference($fieldsCommsNews['openDate'], page::nowDateTimeForMysql());
			
				if($daysSincePubCommsNews['days'] < 14)
				{
					$this->xml .= "<daysSincePubCommsNews>new</daysSincePubCommsNews>";
				}
								
				$this->xml .= "<scapaNewsFeedTitle>" . $fieldsCommsNews['subject'] . "</scapaNewsFeedTitle>";
				$this->xml .= "<scapaNewsFeedDescription>";

				if(strlen($fieldsCommsNews['body']) > 250)
				{
					$this->xml .= substr($fieldsCommsNews['body'], 0, 200) . "...";
				}
				else 
				{
					$this->xml .= $fieldsCommsNews['body'];
				}
				
				$this->xml .= "</scapaNewsFeedDescription>";
				$this->xml .= "<scapaNewsFeedDate>" . common::transformDateTimeForPHP($fieldsCommsNews['openDate']) . "</scapaNewsFeedDate>";
				$this->xml .= "<scapaNewsFeedLink>" . $fieldsCommsNews['id'] . "</scapaNewsFeedLink>";
				
				$this->xml .= "</scapaNewsFeed>";
				
			}
		}
		
		$datasetQuestions = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE newsType = 1");
		
		if(mysql_num_rows($datasetQuestions) > 0)
		{			
			$this->xml .= "<numOfQuestionFeeds>1</numOfQuestionFeeds>";
			
			while($fieldsCommsQuestions = mysql_fetch_array($datasetQuestions))
			{
				$this->xml .= "<scapaQuestionFeed>";
				
				$daysSincePubCommsNews = page::getTimeDifference($fieldsCommsQuestions['openDate'], page::nowDateTimeForMysql());
			
				if($daysSincePubCommsNews['days'] < 14)
				{
					$this->xml .= "<daysSincePubCommsQuestion>new</daysSincePubCommsQuestion>";
				}
								
				$this->xml .= "<scapaQuestionTitle>" . $fieldsCommsQuestions['subject'] . "</scapaQuestionTitle>";
				$this->xml .= "<scapaQuestionDescription>";

				if(strlen($fieldsCommsQuestions['body']) > 250)
				{
					$this->xml .= substr($fieldsCommsQuestions['body'], 0, 200) . "...";
				}
				else 
				{
					$this->xml .= $fieldsCommsQuestions['body'];
				}
				
				$this->xml .= "</scapaQuestionDescription>";
				$this->xml .= "<scapaQuestionDate>" . common::transformDateTimeForPHP($fieldsCommsQuestions['openDate']) . "</scapaQuestionDate>";
				$this->xml .= "<scapaQuestionLink>" . $fieldsCommsQuestions['id'] . "</scapaQuestionLink>";
				
				$this->xml .= "</scapaQuestionFeed>";
				
			}
		}
		
		
		
		$this->xml .= "<notificationCount>";
			$this->xml .= $notificationCount;
		$this->xml .= "</notificationCount>";
		
		$this->xml .= "</news>";

		return $this->xml;
		
	}
}

?>