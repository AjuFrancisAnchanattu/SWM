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

function displayAllUsers($where)
{
	$connection = mysql_connect("10.1.10.12", "complaints", "backweb");
	
	mysql_select_db("membership", $connection);
	
	$dataset = mysql_query("SELECT `firstName`, `lastName`, `NTLogon` FROM `employee` " . $where . " ORDER BY `firstName`, `lastName` ASC ", $connection);
	
	while($fields = mysql_fetch_array($dataset))
	{
		echo "<option value='" . $fields['NTLogon'] . "'>" . htmlspecialchars($fields['firstName']) . " " . htmlspecialchars($fields['lastName']) . "</option>";
	}
}

?>
<html>
<head>
<title>CC Scapa User</title>
<link rel="stylesheet" href="/css/default.css"/>
<script language="JavaScript">

	// DP
	var sendReq = getXmlHttpRequestObject();
	var receiveReq = getXmlHttpRequestObject();
	
	//Add a message to the chat server.
	function updateUserContactList(ajaxUrl, source, desination)
	{
		if (sendReq.readyState == 4 || sendReq.readyState == 0) 
		{
			sendReq.open("POST", '/controls/showCC/updatecc.php' , true);
			sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
			sendReq.onreadystatechange = getNameList; 
			var param = 'searchName=' + document.getElementById(source).value;
			sendReq.send(param);
		}
	}
		
	function getNameList() 
	{
		if (receiveReq.readyState == 4 || receiveReq.readyState == 0) 
		{
			receiveReq.open("GET", '/controls/showCC/updatecc.php', true);
			receiveReq.onreadystatechange = handleReceiveNameList; 
			receiveReq.send(null);
		}			
	}
	
	function handleReceiveNameList() 
	{
		if (receiveReq.readyState == 4) 
		{
			var destination = document.getElementById("usersorig");
			var xmldoc = receiveReq.responseXML;
			var user_nodes = xmldoc.getElementsByTagName("user"); 
			var n_userNodes = user_nodes.length;
			
			for(var i=0; i<n_userNodes; i++)
			{
				var firstName = user_nodes[i].getElementsByTagName("firstName");
				var lastName = user_nodes[i].getElementsByTagName("lastName");
				var email = user_nodes[i].getElementsByTagName("email");
//				destination.innerHTML += user_node[0].firstChild.nodeValue + '&nbsp;';
//				destination.innerHTML += '<font class="chat_time">' + time_node[0].firstChild.nodeValue + '</font><br />';
//				destination.innerHTML += text_node[0].firstChild.nodeValue + '<br />';
//				destination.scrollTop = chat_div.scrollHeight;
//				lastMessage = (message_nodes[i].getAttribute('id'));
// do stuff in here
			}
			
			mTimer = setTimeout('getNameList();',2000); //Refresh our list in 2 seconds
		}
	}


	function getXmlHttpRequestObject() 
	{
		if (window.XMLHttpRequest) 
		{
			return new XMLHttpRequest();
		}
		else if(window.ActiveXObject)
		{
			return new ActiveXObject("Microsoft.XMLHTTP");
		}
		else
		{
			document.getElementById('p_status').innerHTML = 'Status: Cound not create XmlHttpRequest Object.  Consider upgrading your browser.';
		}
	}

	// *DP
	
//	var XmlHttpObj;
//	var dropDownAjaxField;
//	var dropDownAjaxDestination;
	
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


</script>
</head>

<body>

<form id="ccusers" name="ccusers" action="">
<h1>Multi-User select for Scapa (Alpha)</h1>
Select a number of users you would like add and then click the &gt;&gt; Button.<br />
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
	<input type="text" id="name" name="name" size="28" onkeyup="Javascript: updateUserContactList('/controls/showCC/updatecc.php','name','usersorig');" />
	<select name="usersorig" id="usersorig" multiple="true" class="" size="35 ondblclick="Javascript: moveSelectionRight();">
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
	<select name="users[]" id="users" multiple="true" class="" size="36" ondblclick="Javascript: moveSelectionLeft();">
		<?php 
			
			$users = explode(",", $_GET['users']);
			
			foreach ($users as $value)
			{
				$sqlInsert .= "'" . $value . "',";
			}
			$whereUser = "WHERE NTLogon IN (" . substr($sqlInsert, 0, -1) . ")";
			
			displayAllUsers($whereUser);
		
		?>
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