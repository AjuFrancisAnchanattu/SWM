<?php

class chatcache
{
	private $messages = array();
	
	
	public static function getInstance()
	{
		static $instance;
		
		if (!isset($instance))
		{
            $c = __CLASS__;

            $instance = new $c;
        }

        return $instance;
	}
	
	public function add($chats)
	{
		$this->messages[$chats->getId()] = $chats;
	}
	
	public function get($id)
	{
		$id = strtolower($id);
		
		if (!isset($this->messages[$id]))
		{
			$chats = new chatClass();
			$chats->load($id);
			$this->add($chats);
			page::addDebug("$id is not cached, adding", __FILE__, __LINE__);
		}
		else 
		{
			page::addDebug("$id is cached", __FILE__, __LINE__);
		}
		
		return $this->messages[$id];
	}
}