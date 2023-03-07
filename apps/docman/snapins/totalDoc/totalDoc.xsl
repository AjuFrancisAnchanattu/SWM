<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="totalDoc">
	
	
	<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-right: 5px;">Total Documents: <strong><xsl:value-of select="total_documents"/></strong></td>
			</tr>
			<tr>
				<td style="padding-right: 5px;">Last Added: <strong><xsl:value-of select="last_added"/></strong> [<a href="{link}">Open</a>]</td>
			</tr>
			<tr>
				<td style="padding-right: 5px;">Creator: <strong><xsl:value-of select="last_creator"/></strong></td>
			</tr>
			<tr>
				<td style="padding-right: 5px;">Creation Date: <strong><xsl:value-of select="creation_date"/></strong></td>
			</tr>
		</table>
	
	
		
	</xsl:template>
	
</xsl:stylesheet>