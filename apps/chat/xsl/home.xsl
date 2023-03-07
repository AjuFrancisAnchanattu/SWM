<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	
	<xsl:include href="../../../xsl/reducedGlobal.xsl"/>

	<xsl:template match="chat">
	
	
	</xsl:template>

	<xsl:template match="chatHome">

		<table width="100%" cellpadding="2" cellspacing="2" style="background-color: #FFFFFF">
			<tr>
				<td>Who would you like to speak to?</td>
			</tr>
		</table>
		
		<xsl:apply-templates select="form" />
			
	</xsl:template>
	
</xsl:stylesheet>