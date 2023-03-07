<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsNav">
	
	
	<table border="0" cellpadding="2" cellspacing="2">
	
		<tr>
			<td>
			
			<xsl:choose>
				<xsl:when test="complaintId='false'">
					<a href="/apps/complaints/"><strong>{TRANSLATE:summary}</strong></a>
				</xsl:when>
				<xsl:otherwise>
					<a href="index?id={complaintId}"><strong>{TRANSLATE:summary}</strong></a>
				</xsl:otherwise>
			</xsl:choose>
			
			</td>
		</tr>
			
			
	</table>
		
	</xsl:template>
	
</xsl:stylesheet>