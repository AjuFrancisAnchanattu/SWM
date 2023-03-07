<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="filterNumber">
		<table>
			<xsl:apply-templates select="number" />
		</table>
	</xsl:template>
	
	<xsl:template match="number">
		<tr><td>Number:</td><td><xsl:apply-templates select="textbox" /></td></tr>
	</xsl:template>
	
</xsl:stylesheet>