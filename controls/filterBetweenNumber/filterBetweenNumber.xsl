<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="filterBetweenNumber">
		<table>
			<xsl:apply-templates select="min" />
			<xsl:apply-templates select="max" />
		</table>
	</xsl:template>
	
	<xsl:template match="min">
		<tr><td>Between:</td><td><xsl:apply-templates select="textbox" /></td></tr>
	</xsl:template>
	
	<xsl:template match="max">
		<tr><td>And:</td><td><xsl:apply-templates select="textbox" /></td></tr>
	</xsl:template>
	
</xsl:stylesheet>