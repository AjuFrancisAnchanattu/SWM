<html>
	<head>
		<title>Scapa Instant Messaging</title>
		<style type="text/css">
			
			body {
				font-family: Verdana;
				font-size: 10px;
				background-color: #e5e5cb;
			}
			
			h2 {
				font-size: 16px;
				font-family: arial;
				color: #4d4545;
			}
			
			.chat_time {
				font-style: italic;
				font-size: 9px;
			}
			
			.user_look {
				font-style: bold;
				font-size: 12px;
			}
			
			.chat_text {
				font-size: 11px;
				line-height: 17px;
			}
			
			.class1 A:link {text-decoration: underline; color: #000000;}
			.class1 A:visited {text-decoration: underline; color: #000000;}
			.class1 A:active {text-decoration: underline; color: #000000;}
			.class1 A:hover {text-decoration: underline; color: #000000;}
			
		</style>
		<script language="JavaScript" type="text/javascript">
			var sendReq = getXmlHttpRequestObject();
			var receiveReq = getXmlHttpRequestObject();
			var lastMessage = 0;
			var mTimer;
			var timer;
			var count = 0;
			var windowOnFocus;
					
			window.onbeforeunload = confirmUnload; // Load Method when X is pressed ...
			
			// When browser window is closed send message to other user and close the chat request ...
			function confirmUnload()
			{				
				if(event)
				{
					if (sendReq.readyState == 4 || sendReq.readyState == 0) 
					{
						sendReq.open("POST", 'http://scapanetdev/apps/chat/getChat?chat=' + document.getElementById('chat_name').value + '&last=' + lastMessage, true);
						sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
						sendReq.onreadystatechange = handleSendChat; 
						var param = 'message=User ' + document.getElementById('person_name').value + ' has left the conversation.';
						param += '&name=' + document.getElementById('person_name').value + '';
						param += '&chat=' + document.getElementById('chat_name').value + '';
						sendReq.send(param);
						document.getElementById('txt_message').value = '';
					}
					
					//alert("You are now leaving the Instant Messaging System.");
					
					if (sendReq.readyState == 4 || sendReq.readyState == 0)
					{
						sendReq.open("POST", 'http://scapanetdev/apps/chat/getChat?chat=' + document.getElementById('chat_name').value + '&last=' + lastMessage, true);
						sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
						sendReq.onreadystatechange = handleResetChat; 
						var param = 'stopChatAction=stopChat';
						sendReq.send(param);
					}
				}
				
				//window.opener.close();
			}
			
			//Function for initializating the page.
			function startChat() 
			{
				window.focus();
				
				//Set the focus to the Message Box.
				document.getElementById('txt_message').focus();
				
				// Add to the Status that user has joined the chat ...
				//document.getElementById('p_status').innerHTML = 'User has joined the chat ...';
				
				//Start Recieving Messages.
				getChatText();
			}
					
			//Gets the browser specific XmlHttpRequest Object
			function getXmlHttpRequestObject() 
			{
				if (window.XMLHttpRequest) {
					return new XMLHttpRequest();
				} else if(window.ActiveXObject) {
					return new ActiveXObject("Microsoft.XMLHTTP");
				} else {
					document.getElementById('p_status').innerHTML = 'Status: Cound not create XmlHttpRequest Object.  Consider upgrading your browser.';
				}
			}
			
			//Gets the current messages from the server
			function getChatText() 
			{
				if (receiveReq.readyState == 4 || receiveReq.readyState == 0) 
				{
					receiveReq.open("GET", 'http://scapanetdev/apps/chat/getChat?chat=' + document.getElementById('chat_name').value + '&last=' + lastMessage, true);
					receiveReq.onreadystatechange = handleReceiveChat; 
					receiveReq.send(null);
				}
			}
			
			//Add a message to the chat server.
			function sendChatText() 
			{
				if(document.getElementById('txt_message').value == '') 
				{
					alert("You have not entered a message.");
					return;
				}
				
				if (sendReq.readyState == 4 || sendReq.readyState == 0) 
				{
					sendReq.open("POST", 'http://scapanetdev/apps/chat/getChat?chat=' + document.getElementById('chat_name').value + '&last=' + lastMessage, true);
					sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
					sendReq.onreadystatechange = handleSendChat; 
					var param = 'message=' + document.getElementById('txt_message').value;
					param += '&name=' + document.getElementById('person_name').value + '';
					param += '&chat=' + document.getElementById('chat_name').value + '';
					sendReq.send(param);
					document.getElementById('txt_message').value = '';
				}
			}
			
			//When our message has been sent, update our page.
			function handleSendChat() 
			{
				//Clear out the existing timer so we don't have 
				//multiple timer instances running.
				clearInterval(mTimer);
				getChatText();
			}
			
			//Function for handling the return of chat text
			function handleReceiveChat() 
			{
				if (receiveReq.readyState == 4) 
				{
					var chat_div = document.getElementById('div_chat');
					var xmldoc = receiveReq.responseXML;
					var message_nodes = xmldoc.getElementsByTagName("message"); 
					var n_messages = message_nodes.length
					var currentUser = document.getElementById('person_name').value;
					
					currentUser = currentUser.replace("%20", " ");
					
					for (i = 0; i < n_messages; i++) 
					{
						var user_node = message_nodes[i].getElementsByTagName("user");
						var text_node = message_nodes[i].getElementsByTagName("text");
						var time_node = message_nodes[i].getElementsByTagName("time");
						
						if(user_node[0].firstChild.nodeValue == currentUser)
						{
							chat_div.innerHTML += '<img src="../../images/speechBubbleBlueChat.jpg" /><font class="user_look"><strong> ' + user_node[0].firstChild.nodeValue + '</strong></font>&nbsp;';
						}
						else
						{
							chat_div.innerHTML += '<img src="../../images/speechBubbleRedChat.jpg" /><font class="user_look"><strong> ' + user_node[0].firstChild.nodeValue + '</strong></font>&nbsp;';
						}
						chat_div.innerHTML += '<font class="chat_time">' + time_node[0].firstChild.nodeValue + '</font><br />';
						chat_div.innerHTML += '<font class="chat_text">' + text_node[0].firstChild.nodeValue + '</font><br />';
						chat_div.scrollTop = chat_div.scrollHeight;
						lastMessage = (message_nodes[i].getAttribute('id'));
						
						window.focus();
						document.getElementById('txt_message').focus();
						
					}
					
					mTimer = setTimeout('getChatText();',1000); //Refresh our chat in 2 seconds
				}
			}
			
			//This functions handles when the user presses enter.  Instead of submitting the form, we
			//send a new message to the server and return false.
			function blockSubmit() 
			{
				sendChatText();
				return false;
			}
			
			//This cleans out the database so we can start a new chat session.
			function resetChat() 
			{
				if (sendReq.readyState == 4 || sendReq.readyState == 0) 
				{
					sendReq.open("POST", 'http://scapanetdev/apps/chat/getChat?chat=' + document.getElementById('chat_name').value + '&last=' + lastMessage, true);
					sendReq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
					sendReq.onreadystatechange = handleResetChat; 
					var param = 'action=reset';
					sendReq.send(param);
					document.getElementById('txt_message').value = '';
					
					alert("reset chat");
				}							
			}
			
			//This function handles the response after the page has been refreshed.
			function handleResetChat() 
			{
				document.getElementById('div_chat').innerHTML = '';
				getChatText();
			}
			
			function statusOver()
			{
				document.getElementById('statusBar').style.display = "";
			}
			
			function statusOut()
			{	
				clearInterval(timer);
				timer = setTimeout("document.getElementById('statusBar').style.display = 'none'",3000); // 3 secs
			}
			
			function statusChange(status)
			{
				document.getElementById('currentStatus').innerHTML = status;
				document.getElementById('statusBar').style.display = "none";
				document.getElementById('txt_message').value = document.getElementById('person_name').value + " has the status: " + status;
				sendChatText();
			}
				
		</script>
	</head>
	<body onload="javascript:startChat();">
	<?php
		$_GET['myphoto'] == 'false' ? $myPhotoFileName = "default.PNG" : $myPhotoFileName = $_GET['NTLogon'] . ".png";
	?>
	<p id="random">
	
	</p>
		<table width="100%" cellpadding="0" cellspacing="0" >
			<tr style="background-color: #ffffff;">
				<td >
					<img src="../../images/top_left_small.jpg" alt="scapaicon" style=" vertical-align: top;" />
				</td>
				<td align="right">
					<img src="../../images/top_right_small.jpg" alt="scapaicon" style=" vertical-align: top;" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div style="border-bottom: 1px solid black; background-color: #ffffff;	" >
						<table>
							<tr>
								<td style="padding-left: 5px; padding-bottom: 5px;">
									<img src="../../data/hr/photos/<?php echo $myPhotoFileName; ?>" height="50px" />
								</td>
								<td width="100%" align="right" style="padding-right: 5px;">
									<font style="font-size: 16px;	font-family: arial;color: #4d4545; font-weight: bold;">Scapa Instant Messaging (BETA)</font><br />
										<font class="chat_text">
											<?php echo str_replace("%20", " ", $_GET['person_name']); ?> - <font id="currentStatus" style="background-color: #efefef" onmouseover="javascript:statusOver();" onmouseout="javascript:statusOut();"> Chat</font><img style="vertical-align: bottom" src="../../images/down2.gif" onmouseover="javascript:statusOver();" onmouseout="javascript:statusOut();" />
										<br />
										<font>&nbsp;</font>
										<font id="statusBar" class="class1" style="display:none; background-color: #efefef"">
											<font style="font-size:11px; background-color: #efefef" onclick="javascript:statusChange('Chat');"><a href="#">Chat</a></font>									
											<font style="font-size:11px; background-color: #efefef" onclick="javascript:statusChange('Busy');"><a href="#">Busy</a></font>									
											<font style="font-size:11px; background-color: #efefef" onclick="javascript:statusChange('Away');"><a href="#">Away</a></font>									
										</font>

								</td>
							</tr>
						</table>	
					</div>
				</td>
			</tr>
			<tr style="background-color: #efefef; padding-left: 5px; padding-top: 10px; padding-bottom: 10px; ">
				<td colspan="2" style="border-bottom: 1px solid black;" >
					<div id="div_chat" style="height: 300px; width: 275px; overflow: auto; background-color: #efefef; border: 1px solid #efefef;">
					</div>
				</td>
			</tr>
			<form id="frmmain" name="frmmain" onsubmit="return blockSubmit();">
				<tr style="background-color: #dfdfdf; padding: 0px; align: center; padding-top: 5px;">
					<td colspan="2" align="center">
				
						<!--<textarea rows="2" id="txt_message" name="txt_message" onkeyup="javascript:checkEnter(event);" style="width: 260;" ></textarea>-->
						<input type="text" id="txt_message" name="txt_message" onsubmit="javascript:sendChatText();" onfocus="javascript:changeFocus();"  style="width: 90%;" />
					</td>
				</tr>
				<tr style="background-color: #dfdfdf; padding: 0px; align: center; padding-top: 5px;">
				<td colspan="2" align="center">
						<!--<input type="button" name="btn_get_chat" id="btn_get_chat" value="Refresh Chat" onclick="javascript:getChatText();" />-->
						<input type="button" name="btn_send_chat" id="btn_send_chat" value="Send" onclick="javascript:sendChatText();" />
						<!--  Hidden Field --><input type="hidden" name="person_name" id="person_name" value="<?php echo $_GET['person_name']; ?>" />
						<!--  Hidden Field --><input type="hidden" name="chat_name" id="chat_name" value="<?php echo $_GET['chat_name']; ?>" />
						<!--  Hidden Field --><input type="hidden" name="NTLogon" id="NTLogon" value="<?php echo $_GET['NTLogon']; ?>" />
					</td>
				</tr>
			</form>
			<tr style="background-color: #dfdfdf;">
				<td>
					<img src="../../images/bottom_left_chat.jpg" alt="scapaicon" style=" vertical-align: bottom;" />
				</td>
				<td align="right">
					<img src="../../images/bottom_right_chat.jpg" alt="scapaicon" style=" vertical-align: bottom;" />
				</td>
			</tr>
		</table>
	</body>
	
</html>