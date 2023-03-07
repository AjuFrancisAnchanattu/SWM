<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="deadlines">
	
		<table cellspacing="0" width="260">
		
         		
						<xsl:apply-templates select="deadline" />
					
			</table>
			
		
		
	</xsl:template>
	
	<xsl:template match="deadline">
		<tr>
			<td><xsl:value-of select="name" />:</td>
			<td align="right"><xsl:value-of select="date" /></td>
			
    	</tr>
	</xsl:template>
	
</xsl:stylesheet>