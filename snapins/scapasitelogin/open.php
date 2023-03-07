<?php
	$dbhost = '10.1.10.6';
	$dbuser = 'root';
	$dbpass = 'backweb';
	
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');

	$dbname = 'membership';
	mysql_select_db($dbname);

	// Do Some Stuff Now
	$query = "SELECT firstName, lastName, site_login_status, site FROM employee WHERE site = 'Dunstable' AND site_login_status = 'In' ORDER BY firstName ASC, lastName ASC";
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Scapa Personnel Status</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script type="text/javascript">
function printWindow(){
   printScreen = parseInt(navigator.appVersion)
   if (printScreen >= 4) window.print()
}
</script>
<style type="text/css">
<!--
body {
	margin-left: 10px;
	margin-top: 10px;
	margin-right: 10px;
	margin-bottom: 10px;
}
.style1 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: large;
	font-style: italic;
}
.style2 {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: small;
}
-->
</style></head>

<body>
<table width="650" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td width="59"><img src="/snapins/scapasitelogin/logo.gif" width="59" height="85" /></td>
    <td width="577"><span class="style1">Scapa Personnel Status </span></td>
  </tr>
  <tr>
	<td colspan="2" class="style2"><div align="center"><a href="javascript:printWindow()">Print</a></div></td>
</tr>
</table>
<table width="500" border="1" cellspacing="2" cellpadding="2">
<tr>
    <td><span class="style2"><strong>First Name</strong></span></td>
    <td class="style2"><strong>Surname</strong></td>
    <td class="style2"><strong>Location</strong></td>
  </tr>
<?php
// Printing results in HTML
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) 
{
   echo "\t<tr>\n";
   echo "<td width='150'><span class='style2'>" . $line['firstName'] . "</span></td><td width='150'><span class='style2'>" . $line['lastName'] . "</span></td><td><span class='style2'>" . $line['site_login_status'] . "</span></td>";
   echo "\t</tr>\n";
}
?> 
</table>
</body>
</html>

<?php
// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
?> 