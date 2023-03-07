<?php

class getChat
{
	public function __construct()
	{
		//Send some headers to keep the user's browser from caching the response.
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
		header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
		header("Cache-Control: no-cache, must-revalidate" ); 
		header("Pragma: no-cache" );
		header("Content-Type: text/xml; charset=utf-8");
		
		//Check to see if a message was sent.
		if(isset($_POST['message']) && $_POST['message'] != '') 
		{			
			mysql::getInstance()->selectDatabase("chat")->Execute("INSERT INTO message (chat_id, user_id, user_name, message, post_time) VALUES (" . $_GET['chat'] . ", 1, '" . $_POST['name'] . "', '" . $_POST['message'] . "', NOW())");
		}
		
		//Check to see if a reset request was sent.
		if(isset($_POST['action']) && $_POST['action'] == 'reset') 
		{
			mysql::getInstance()->selectDatabase("chat")->Execute("DELETE FROM message WHERE chat_id = " . $_GET['chat'] . "");
		}
		
		//Check to see if a reset request was sent.
		if(isset($_POST['stopChatAction']) && $_POST['stopChatAction'] == 'stopChat')
		{
			mysql::getInstance()->selectDatabase("chat")->Execute("UPDATE chat SET isChatOpen = 0 WHERE chat_name = " . $_GET['chat'] . "");
		}
		
		//Create the XML response.
		$xml = '<?xml version="1.0"?>';
		
		$xml .= "<root>";
		
		//Check to ensure the user is in a chat room.
		if(!isset($_GET['chat'])) 
		{
			$xml .='Your are not currently in a chat session.  <a href="">Enter a chat session here</a>';
			$xml .= '<message id="0">';
			$xml .= '<user>Admin</user>';
			$xml .= '<text>Your are not currently in a chat session.  &lt;a href=""&gt;Enter a chat session here&lt;/a&gt;</text>';
			$xml .= '<time>' . date('h:i') . '</time>';
			$xml .= '</message>';
		} 
		else
		{
			$last = (isset($_GET['last']) && $_GET['last'] != '') ? $_GET['last'] : 0;
			
			$dataset = mysql::getInstance()->selectDatabase("chat")->Execute("SELECT message_id, user_name, message, date_format(post_time, '%h:%i') as post_time" . " FROM message WHERE chat_id = " . $_GET['chat'] . " AND message_id > " . $last . "");

			//Loop through each message and create an XML message node for each.
			while($message_array = mysql_fetch_array($dataset)) 
			{
				$xml .= '<message id="' . $message_array['message_id'] . '">';
				$xml .= '<user>' . htmlspecialchars($message_array['user_name']) . '</user>';
				$xml .= '<text>' . htmlspecialchars($message_array['message']) . '</text>';
				$xml .= '<time>' . $message_array['post_time'] . '</time>';
				$xml .= '</message>';
			}
		}
		
		$xml .= '</root>';
		echo $xml;
	}
}
?>