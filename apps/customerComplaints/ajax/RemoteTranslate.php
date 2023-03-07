<?php
class RemoteTranslate extends page
{
	function __construct()
	{
		if( isset($_REQUEST['phrase']))
		{
			$phrase = $_REQUEST['phrase'];
		}
		else
		{
			die('no phrase set to translate');
		}
		
		echo rawurlencode( html_entity_decode(translate::getInstance()->translate( $phrase ), ENT_QUOTES ) );
	}
}
?>