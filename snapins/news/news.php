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
	public $newsPosts = array();

	private $pageAction;
	private $maxNumberOfDays = 7;

	/**
	 * @param string $area the area of the page the snapin should appear in
	 */
	function __construct()
	{
		$this->setName(translate::getInstance()->translate("news"));
		$this->setClass(__CLASS__);
		$this->setCanClose(false);
		$this->setColourScheme("title-box2");
		

		$this->getSharepointDocumentRSSFeed();

		if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_REQUEST['searchTerm']) && !empty($_REQUEST['searchTerm']))
		{
			$dataset = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT id FROM comm WHERE subject = '" . addslashes($_REQUEST['searchTerm']) . "'");

			$fields = mysql_fetch_array($dataset);

			page::redirect("/apps/comms/viewArticle?id=" . $fields['id']);
		}
	}
	
	
	private function getSharepointDocumentRSSFeed()
	{
		
	}
	
	
	function is_valid_url($url)
	{    
		$url = @parse_url($url);    
		
		if (!$url)    
		{        
			return false;    
		}    
		
		$url = array_map('trim', $url);    
		
		$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];    
		
		$path = (isset($url['path'])) ? $url['path'] : '';    
		
		if ($path == '')    
		{        
			$path = '/';    
		}    
		
		$path .= (isset($url['query'])) ? "?$url[query]" : '';    
		
		if (isset($url['host']) AND $url['host'] != gethostbyname($url['host']))    
		{        
			$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");            
			
			// if-statement added 19/11/2010 - Rob - ensures function returns false if there's a problem getting the headers
			if ($headers != false)
			{
				$headers = (is_array($headers)) ? implode("\n", $headers) : $headers;        
				
				return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
			}
		}    
		return false;
	}
	

	public function output()
	{
		$newsRSS = 'http://www.scapa.com/en/news_rss.xml';
		
		$adminNotificationCount = 0;
		$notificationCount = 0;

		$this->xml .= "<news>";

		// Hide or Show the Add News Links
		if(currentuser::getInstance()->hasPermission("comm_admin"))
		{
			$this->xml .= "<commAdmin>true</commAdmin>";
		}

		$doc = new DOMDocument();
		
		libxml_use_internal_errors(true);
		
		// Load the RSS Feed URL (scapa.com)
		try
		{
			// check if RSS Feed is available			
			if (!$this->is_valid_url($newsRSS))
			{
				throw new Exception('Problem loading xml');
			}			
			
			// check if RSS Feed will load
			if (!$doc->load($newsRSS))
			{
				throw new Exception('Problem loading xml2');
			}
			
			$arrFeeds = array();
	
			foreach ($doc->getElementsByTagName('item') as $node)
			{
				$date = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;

				$time = substr($date, 16, 9);
				
				$processedDate = date("Y-m-d", strtotime(substr($date, 5, 11))) . $time;
				
				if ($processedDate > date("Y-m-d", mktime(1,1,1,11,15,2011))) //Only dates after 14/11/2011 - problematic article on this date (invalid chars) - Rob
				{					
					$itemRSS = array (
						'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
						'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
						'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
						'date' => $processedDate
						);
					array_push($arrFeeds, $itemRSS);
				}
			}	
	
			$numOfThreads = count($arrFeeds) - 1;
	
			for ($i = 0; $i <= $numOfThreads; $i++)
			{
				$this->newsPosts[$i]['type'] = "rss";	
			
				$daysSincePub = page::getTimeDifference($arrFeeds[$i]['date'], page::nowDateTimeForMysql());
	
				if($daysSincePub['days'] < $this->maxNumberOfDays)
				{
					$this->newsPosts[$i]['new'] = "1";
				}
				$this->newsPosts[$i]['title'] = $arrFeeds[$i]['title'];
				$this->newsPosts[$i]['body'] = $arrFeeds[$i]['desc'];
				$this->newsPosts[$i]['link'] = $arrFeeds[$i]['link'];

				$this->newsPosts[$i]['date'] = common::transformDateTimeForPHP($arrFeeds[$i]['date']);
				
				$this->newsPosts[$i]['unix'] = $this->convertToUnix($arrFeeds[$i]['date']);
				
			}
		}
		catch (Exception $xmlError)
		{
			$this->xml .= "<rssError>true</rssError>";
			$i = 0;
			//email::send('robert.markiewka@scapa.com', 'robert.markiewka@scapa.com', 'RSS Feed Offline', 'The RSS feed is offline.' . $xmlError);
			email::send('intranet@scapa.com', 'intranet@scapa.com', 'RSS Feed Offline', 'The RSS feed is offline.');
		}
		
		//$i = 0;

		// Scapa Comm News
		$datasetCommsNews = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM comm WHERE newsType = 1 ORDER BY openDate DESC LIMIT 6");

		if(mysql_num_rows($datasetCommsNews) > 0)
		{
			while($fieldsCommsNews = mysql_fetch_array($datasetCommsNews))
			{
				$daysSincePubCommsNews = page::getTimeDifference($fieldsCommsNews['openDate'], page::nowDateTimeForMysql());

				if($daysSincePubCommsNews['days'] < $this->maxNumberOfDays)
				{
					$this->newsPosts[$i]['new'] = "1";
				}

				$this->newsPosts[$i]['type'] = "news";
				$this->newsPosts[$i]['title'] = $fieldsCommsNews['subject'];
				$this->newsPosts[$i]['date'] = common::transformDateTimeForPHP($fieldsCommsNews['openDate']);
				
				$this->newsPosts[$i]['link'] = "/apps/comms/viewArticle?id=" . $fieldsCommsNews['id'];
				$this->newsPosts[$i]['unix'] = $this->convertToUnix($fieldsCommsNews['openDate']);

				if(strlen($fieldsCommsNews['body']) > 250)
				{
					$this->newsPosts[$i]['body'] = substr($fieldsCommsNews['body'], 0, 200) . "...";
				}
				else
				{
					$this->newsPosts[$i]['body'] = $fieldsCommsNews['body'];
				}

				$i++;
			}
		}

		// Scapa Questions
		$datasetQuestions = mysql::getInstance()->selectDatabase("comms")->Execute("SELECT * FROM askAQuestion WHERE newsType = 1 ORDER BY openDate DESC LIMIT 2");

		if(mysql_num_rows($datasetQuestions) > 0)
		{
			while($fieldsCommsQuestions = mysql_fetch_array($datasetQuestions))
			{
				$daysSincePubCommsNews = page::getTimeDifference($fieldsCommsQuestions['openDate'], page::nowDateTimeForMysql());
				if($daysSincePubCommsNews['days'] < $this->maxNumberOfDays)
				{
					$this->newsPosts[$i]['new'] = "1";
				}

				$this->newsPosts[$i]['type'] = "ask";
				$this->newsPosts[$i]['title'] = $fieldsCommsQuestions['subject'];
				$this->newsPosts[$i]['link'] = "/apps/comms/viewAskAQuestion?id=" . $fieldsCommsQuestions['id'];
				$this->newsPosts[$i]['date'] = common::transformDateTimeForPHP($fieldsCommsQuestions['openDate']);
				$this->newsPosts[$i]['unix'] = $this->convertToUnix($fieldsCommsQuestions['openDate']);

				if(strlen($fieldsCommsQuestions['body']) > 250)
				{
					$this->newsPosts[$i]['body'] = substr($fieldsCommsQuestions['body'], 0, 200) . "...";
				}
				else
				{
					$this->newsPosts[$i]['body'] = $fieldsCommsQuestions['body'];
				}

				$i++;
			}
		}

		// Sorts the array into Date order.
		$this->newsPosts = ($this->msort($this->newsPosts));

		// This shows the first 5 posts
		for ($count = 0; $count < 5; $count++)
		{
			$this->xml .= "<newsPost>";

			if(isset($this->newsPosts[$count]['new']))
			{
				$this->xml .= "<newsNew>1</newsNew>";
			}
			$this->xml .= "<newsType>" . $this->newsPosts[$count]['type'] . "</newsType>";
			$this->xml .= "<newsTitle>" . page::xmlentities($this->newsPosts[$count]['title']) . "</newsTitle>";
			$this->xml .= "<newsBody>" . page::xmlentities($this->newsPosts[$count]['body']) . "</newsBody>";
			$this->xml .= "<newsLink>" . $this->newsPosts[$count]['link'] . "</newsLink>";
			$this->xml .= "<newsDate>" . $this->newsPosts[$count]['date'] . "</newsDate>";

			$this->xml .= "</newsPost>";
		}

		// This shows the last 5 posts
		for ($count = 5; $count < count($this->newsPosts); $count++)
		{
			$this->xml .= "<newsPostExpanded>";

			if(isset($this->newsPosts[$count]['new']))
			{
				$this->xml .= "<newsNew>1</newsNew>";
			}
			$this->xml .= "<newsType>" . $this->newsPosts[$count]['type'] . "</newsType>";
			$this->xml .= "<newsTitle>" . page::xmlentities($this->newsPosts[$count]['title']) . "</newsTitle>";
			$this->xml .= "<newsBody>" . page::xmlentities($this->newsPosts[$count]['body']) . "</newsBody>";
			$this->xml .= "<newsLink>" . $this->newsPosts[$count]['link'] . "</newsLink>";
			$this->xml .= "<newsDate>" . $this->newsPosts[$count]['date'] . "</newsDate>";

			$this->xml .= "</newsPostExpanded>";
		}

		$this->xml .= "</news>";

		return $this->xml;
	}


	// Sorts the multidimensional array
	function msort($array, $id='unix') {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id]) {
                    if ($item[$id]>$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        return $temp_array;
   }

   // Converts MySQL to unix time
	function convertToUnix($str)
	{
		list($date, $time) = explode(' ', $str);
		list($year, $month, $day) = explode('-', $date);
		list($hour, $minute, $second) = explode(':', $time);

		$timestamp = mktime($hour, $minute, $second, $month, $day, $year);

		return $timestamp;
	}
}

?>