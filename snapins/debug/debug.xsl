<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="debug">
	
		<table cellspacing="0">
			<tr>
				<td>
					<xsl:apply-templates select="SQLDebug" />
				</td>
			</tr>
		</table>

	</xsl:template>
	<xsl:template match="SQLDebug">
		<xsl:apply-templates select="para" />
	</xsl:template>
</xsl:stylesheet>