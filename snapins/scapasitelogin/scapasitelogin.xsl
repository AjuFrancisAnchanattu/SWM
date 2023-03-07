<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="scapasitelogin">
	
	<xsl:choose>
		<xsl:when test="chat_id != 0">
			<table cellspacing="0" width="260">
				<tr>
					<td width="99%"><a href="#" onclick="window.open('/apps/chat/chat?person_name={myname}&amp;chat_name={chat_id}', 'initiateChat','menubar=0,resizable=0,width=350,height=600');">Open Chat</a> (Opens Browser Window)</td>
				</tr>
			</table>
		</xsl:when>
		<xsl:otherwise>
			<table cellspacing="0" width="260">
				<tr>
					<td width="99%"><a href="#" onclick="window.open('/apps/chat/first?', 'initiateChat','menubar=0,resizable=0,width=350,height=600');">Initiate Chat</a> (Opens Browser Window)</td>
				</tr>
			</table>	
		</xsl:otherwise>
	</xsl:choose>
	
	</xsl:template>
	
</xsl:stylesheet>