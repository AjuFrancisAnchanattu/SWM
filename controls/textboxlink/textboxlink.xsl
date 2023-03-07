<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="textboxlink">
			
		<div class="readOnly" id="{@name}">
			
			<xsl:choose>
				<xsl:when test="openNewWindow='0'">
					<a href="{link}"><p style="margin: 0; line-height: 15px;"><xsl:value-of select="value" /><br /></p></a>
				</xsl:when>
				<xsl:otherwise>
					<a href="{link}" target="_blank"><p style="margin: 0; line-height: 15px;"><xsl:value-of select="value" /><br /></p></a>
				</xsl:otherwise>
			</xsl:choose>
		</div>
	
	</xsl:template>
	
</xsl:stylesheet>