<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="measurement">
		<input type="hidden" name="{name}" value="dummy" />
			<xsl:apply-templates select="quantity" />
			<xsl:apply-templates select="measure" />
			
			<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
			
	</xsl:template>
	
	<xsl:template match="quantity">
		<xsl:apply-templates select="textbox" />
	</xsl:template>
	
	<xsl:template match="measure">
		<xsl:apply-templates select="dropdown" />
	</xsl:template>
	
</xsl:stylesheet>