<?php

/**
 * This adds a new user to the IJF system.
 * 
 * @author Jason Matthews
 * @version 07/08/2006
 */

// Ensures all fields that use the requested IJF_id are created ...
mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("INSERT INTO permissions VALUES ('','" . $_GET['name'] . "','ijf_hello')");	

//mysql::getInstance()->selectDatabase("MEMBERSHIP")->Execute("DELETE FROM purchasing WHERE ijfId='" . $_REQUEST['id'] . "'");	

// Redirect To IJF Home
header("Location: ../../apps/ijf");

?>