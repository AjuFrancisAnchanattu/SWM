<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="scapainstantmessaging">
	
	<xsl:choose>
		<xsl:when test="chat_id != 0">
			<table cellspacing="0" width="260">
				<tr>
					<td width="99%"><a href="#" onclick="window.open('/apps/chat/chat?person_name={myname}&amp;chat_name={chat_id}&amp;myphoto={myphoto}&amp;NTLogon={myNTLogon}', 'initiateChat','menubar=0,resizable=1,width=300,height=500');"><strong>{TRANSLATE:open_chat}</strong></a> {TRANSLATE:opens_browser_window}</td>
				</tr>
			</table>
		</xsl:when>
		<xsl:otherwise>
			<table cellspacing="0" width="260">
				<tr>
					<td width="99%">{TRANSLATE:no_chat_requests}</td>
				</tr>
			</table>	
		</xsl:otherwise>
	</xsl:choose>
	
	</xsl:template>
	
</xsl:stylesheet>