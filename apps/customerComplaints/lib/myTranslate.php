<?php

class myTranslate
{
	private $lang = 'english';
	private $app = NULL;
	private $translations = array();
	
	private $languages = array(
		"EN" => "english",
		"DE" => "german",
		"ITA" => "italian",
		"FR" => "french",
		
		"english" => "english",
		"german" => "german",
		"italian" => "italian",
		"french" => "french"
	);
	
	function __construct( $language = 'english', $application = NULL )
	{
		$this->lang = $this->languages[ $language ];
		
		if( $application != NULL && $application != '' )
		{
			$this->app = $application;
		}
	}
	
	public function translate( $phrase )
	{
		if( !array_key_exists( $phrase, $this->translations ) )
		{
			$sqlApp = ( $this->app == NULL ) ? " AND application IN ('global')" : " AND application IN ('$this->app', 'global')";
			
			$sql = "SELECT $this->lang 
					FROM translations 
					WHERE translateFrom = '$phrase'" . $sqlApp;
			
			$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute( $sql );
			
			$fields = mysql_fetch_array( $dataset );
			
			if( $fields[ $this->lang ] == NULL || $fields[ $this->lang ] == "" )
			{
				$sql = "SELECT english 
					FROM translations 
					WHERE translateFrom = '$phrase'" . $sqlApp;
					
				$dataset = mysql::getInstance()->selectDatabase("intranet")->Execute( $sql );
			
				$fields = mysql_fetch_array( $dataset );
				
				if( $fields['english'] == NULL || $fields['english'] == "" )
				{
					$translation = $phrase . "^^";
				}
				else
				{
					$translation =  $fields['english'] . "^";
				}
			}
			else
			{
				$translation = $fields[ $this->lang ];
			}
			
			$this->translations[ $phrase ] = $translation;
		}
		
		return $this->translations[ $phrase ];
	}
}

?>