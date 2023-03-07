<?php

/*$userNameArray = split("\\\\",$_SERVER['REMOTE_USER']);		//have to double escape the backslash key
$userName = strtolower($userNameArray[1]);
$value = str_replace("'","----",$userName);

setcookie("ntlogon", $value, time()+60*60*24*30, "/");

setcookie("auth", md5($value . "scapanet"), time()+60*60*24*30, "/");

header("Location: " . (isset($_REQUEST['url']) ? $_REQUEST['url'] : '/'));*/



?>
test