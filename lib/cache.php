<?php

class cache
{
	public static function getRemoteDocument($url, $cache=3600)
	{
		$key = md5($url);
		$file = "./cache/$key.cache";
		
		
		if (file_exists($file))
		{
			$handle = fopen($file, "r");
			
			$content = fread($handle, filesize($file));
			
			$data = unserialize($content);
			
			if ($data['date'] > time())
			{
				return $data['data'];
			}
		}
				
		if(!$handle = fopen($url, "rb"))
		{
			return "";
		}
		
		$contents = '';
		
		while (!feof($handle)) 
		{
			$contents .= fread($handle, 4096);
		}
		
		fclose($handle);
		
		cache::writeCache($contents, $url, $cache);
		
		return $contents;		
	}
	
	public static function getLocalDocument($document, $cache=3600)
	{
		return cache::getRemoteDocument($document, $cache);
	}
	
	public static function getCache($key)
	{
		$hashedkey = md5($key);
		
		$file = "./cache/$hashedkey.cache";
		
		
		//page::addDebug("cache: $file", __FILE__, __LINE__);
		
		//echo "$file<br />";
		
		if (file_exists($file))
		{
			$handle = fopen($file, "r");
			
			$content = fread($handle, filesize($file));
			
			fclose($handle);
			
			$data = unserialize($content);
			
			if ($data['date'] > time())
			{
				return $data['data'];
			}
		}

		return false;
	}
	
	public static function writeCache($contents, $key, $cache=3600)
	{
		$hashedkey = md5($key);
		
		$file = "./cache/$hashedkey.cache";
		
		$data = array(
			'date'	=> time() + $cache,
			'data'	=> $contents
		);
		
		$handle = fopen($file, "w+");
		fwrite($handle, serialize($data));
		fclose($handle);
	}
}

?>