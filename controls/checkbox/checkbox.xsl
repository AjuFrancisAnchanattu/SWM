<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="checkbox">
	
		<xsl:element name="input">
		
			<xsl:attribute name="type">checkbox</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:if test="value='on'">
				<xsl:attribute name="checked"></xsl:attribute>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">checkboxbox required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">checkbox optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
		</xsl:element>
		<span style="padding-left: 8px;"><xsl:value-of select="setBoxName" /></span>
	
	</xsl:template>
	
</xsl:stylesheet>