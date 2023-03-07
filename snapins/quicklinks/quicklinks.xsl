<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="quicklinks">
	
			<table cellspacing="0">
				<xsl:apply-templates select="quicklink_item" />
			</table>
		
		<div id="addLinkLink" style="background: #DDDDDD; border: 1px solid #bfbdbd; padding: 2px;">
	
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td><img src="/images/contact.gif" style="display: block; margin-right: 4px;" /></td>
					<td><a href="#" onclick="toggle_display('addLinkLink'); return toggle_display('addLink')">{TRANSLATE:ADD_LINK}</a></td>
				</tr>
			</table>
		
		</div>
		
		<div id="addLink" style="background: #DDDDDD; border: 1px solid #bfbdbd; padding: 2px; display: none;">
		
			<table cellpadding="1" cellspacing="0">
				<tr>
					<td><img src="/images/contact.gif" style="display: block; margin-right: 4px;" /></td>
					<td><a href="#" onclick="toggle_display('addLinkLink'); return toggle_display('addLink')">{TRANSLATE:HIDE_ADD_LINK}</a></td>
				</tr>
			</table>
			
			<table>
				<tr>
					<td>ooh</td>
				</tr>
			</table>
			
		</div>
	
	</xsl:template>
	
	<xsl:template match="quicklink_item">
		<tr>
			<td><a href="{link}"><xsl:value-of select="text" /></a></td>
		</tr>
	</xsl:template>
	
</xsl:stylesheet>