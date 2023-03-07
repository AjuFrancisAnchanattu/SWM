<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="textarea">
		<xsl:element name="textarea">
		
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">textarea required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">textarea optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:value-of select="value" />
		
		</xsl:element>
		
	</xsl:template>
	
</xsl:stylesheet>
