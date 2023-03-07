<?php

/**
 * This is the Complaints Application.
 *
 * @package controls
 * @subpackage showCC
 * @copyright Scapa Ltd.
 * @author Jason Matthews
 * @version 30/10/2008
 */

function displayAllUsers()
{
	$connection = mysql_connect("10.1.10.12", "complaints", "backweb");
	mysql_select_db("membership", $connection);
	$dataset = mysql_query("SELECT `firstName`, `lastName`, `email` FROM `employee` WHERE enabled = 1 ORDER BY `firstName`, `lastName` ASC", $connection);

	while($fields = mysql_fetch_array($dataset))
	{
		echo "<option value='" . $fields['email'] . "'>" . htmlspecialchars($fields['firstName']) . " " . htmlspecialchars($fields['lastName']) . "</option>";
	}
}

?>
<html>
<head>
<title>CC Scapa User</title>
<link rel="stylesheet" href="/css/default.css"/>
<script language="JavaScript">
	function selectAllColumns()
	{
		i = 0;
		var selUsers = document.getElementById('users');
		if(selUsers){
			while(i != selUsers.options.length){
				selUsers.options[i].selected = true;
				i++;
			}
		}
	}
	//selectAllColumns();

	function moveSelectionRight()
	{
		var i = 0;
		var selUsers = document.getElementById('users');

		while(i != document.getElementById('usersorig').length){
			if(document.getElementById('usersorig').options[i].selected){
				//we now need to check if it is already in the list - if not add
				var foundMatch = false;
				var j = 0;
				while(j != selUsers.options.length){
					if(selUsers.options[j].value == document.getElementById('usersorig').options[i].value)
						foundMatch = true;
					j++;
				}
				if(!foundMatch)
					selUsers.options[selUsers.options.length] = new Option(document.getElementById('usersorig').options[i].value,document.getElementById('usersorig').options[i].value);
			}
			i++;
		}
		selectAllColumns()
	}


	function moveSelectionLeft()
	{
		var i = 0;
		var toDelete = new Array();
		var selUsers = document.getElementById('users');
		var loopLength = selUsers.options.length;
		i = (loopLength-1);
		while(i != -1){
			if(selUsers.options[i].selected){
				selUsers.options[i] = null;
			}
			i--;
		}
		selectAllColumns()
	}

	function copySelectionToPage(field)
	{
		var i = 0;
		var selUsers = document.getElementById('users');
		var selUsersLength = document.getElementById('users').length;
		var number = 0;
		var toCopy = new Array();

		while(i != selUsersLength)
		{
			if(selUsers.options[i].selected)
			{
				toCopy.push(selUsers.options[i].value);
			}
			i++;
		}

		window.opener.document.getElementById(field).value = "";

		window.opener.document.getElementById(field).value = toCopy;

		window.close();
	}

	function  updateUserContactList()
	{
		// need this to update the usersorig list with population from sql query
	}

</script>
</head>

<body onload="Javascript: determineField();">

<form id="ccusers" name="ccusers" action="">
<h1>Mass Email CC for Scapa Users (Alpha)</h1>
Select a number of users you would like to CC and then click the &gt;&gt; Button.<br />
Alternatively please double click the users you require.<br />
Once finished click <strong>Done</strong>.
<table width="100%" cellpadding="2" cellspacing="2">
<tr>
	<td width="40%"><strong>Select Recipients</strong></td>
	<td width="20%">&nbsp;</td>
	<td width="40%"><strong>Selected Recipients</strong></td>
</tr>
<tr>
	<td width="40%">
	<!--<input type="text" id="" name="" size="28" onkeypress="Javascript: updateUserContactList();" />-->
	<select name="usersorig" id="usersorig" multiple="true" class="" size="15" ondblclick="Javascript: moveSelectionRight();">
		<?php


			displayAllUsers();


		?>
	</select>
	</td>
	<td width="20%">
		<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveSelectionRight();"/>
		<br />
		<br />
		<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveSelectionLeft();"/>
	</td>
	<td width="40%">
	<select name="users[]" id="users" multiple="true" class="" size="15" ondblclick="Javascript: moveSelectionLeft();">

	</select>
	</td>
</tr>
<tr>
	<td colspan="3"><br /><div align="center"><input type="button" value="Done" onclick="Javascript: copySelectionToPage('<?php echo $_GET['field']; ?>');" /></div></td>
</tr>
</table>

</form>
</body>

</html>