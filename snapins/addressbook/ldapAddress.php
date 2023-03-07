<?php 

$LDAP_NAME[0] = "UKDUNDC001"; 
$LDAP_SERVER[0] = "10.1.199.11"; 
$LDAP_ROOT_DN[0] = "OU=Scapa,DC=scapa,DC=local"; 

//If no server chosen set it to 0 
$SERVER_ID=0;  

//Connect to LDAP 
$connect_id = ldap_connect($LDAP_SERVER[$SERVER_ID]); 

 
var_dump($connect_id);

die();

//if($connect_id) 
//{ 
////Authenticate 
//$bind_id = ldap_bind($connect_id); 
//
////Perform Search 
//$search_id = ldap_search($connect_id, $LDAP_ROOT_DN[$SERVER_ID], $ldap_query); 
//
////Assign Result Set to an Array 
//$result_array = ldap_get_entries($connect_id, $search_id); 
//} 
//else 
//{ 
////Echo Connection Error 
//echo "Could not connect to LDAP server: $LDAP_SERVER[$SERVER_ID]"; 
//} 


die();
//Sort results if search was successful 
//if($result_array) 
//{ 
//for($i=0; $i) { 
//$format_array[$i][0] = strtolower($result_array[$i]["cn"][0]); 
//$format_array[$i][1] = $result_array[$i]["dn"]; 
//$format_array[$i][2] = strtolower($result_array[$i]["givenname"][0]); 
//$format_array[$i][3] = strtolower($result_array[$i]["sn"][0]); 
//$format_array[$i][4] = strtolower($result_array[$i]["mail"][0]); 
//} 
//
////Sort array 
//sort($format_array, "SORT_STRING"); 
//
//for($i=0; $i<count($format_array); $i++) 
//{ 
//$cn = $format_array[$i][0]; 
//$dn = $format_array[$i][1]; 
//$fname = ucwords($format_array[$i][2]); 
//$lname = ucwords($format_array[$i][3]); 
//$email = $format_array[$i][4]; 
//
//if($dn && $fname && $lname && $email) 
//{ 
//$result_list .= "<A HREF=\"ldap://$LDAP_SERVER[$SERVER_ID]/$dn\">$fname $lname</A>"; 
//$result_list .= " <A HREF=\"mailto:$email\">$email</A><BR>\n"; 
//} 
//elseif($dn && $cn && $email) 
//{ 
//$result_list .= "$cn"; 
//$result_list .= " <$email>
//\n"; 
//} 
//} 
//} 
//else 
//{ 
//echo "Result set empty for query: $ldap_query"; 
//} 
//
// 
//
////Close Connection 
//ldap_close($connect_id); 
//
//
//
////Echo Results 
//if($result_list) 
//{ 
//echo "<CENTER><TABLE BORDER=\"1\" CELLSPACING=\"0\" CELLPADDING=\"10\" BGCOLOR=\"#FFFFEA\" WIDTH=\"450\"><TR><TD>$result_list</TD></TR> </TABLE></CENTER>"; 
//} 
//else 
//echo "No Results"; 

?> 