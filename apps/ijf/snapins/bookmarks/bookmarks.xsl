<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="CCRbookmarks">
		<table cellspacing="0" width="260">
			<xsl:choose>
     			<xsl:when test="count(bookmark) > 0">	
					<xsl:for-each select="bookmark">
						<tr>
				    		<td><a href="/apps/ccr/search?id={id}"><xsl:value-of select="name" /></a></td>
				    	</tr>
					</xsl:for-each>
				</xsl:when>
      			<xsl:otherwise>
        			<tr><td>None</td></tr>
     		 	</xsl:otherwise>
   		 	</xsl:choose>
		</table>
	</xsl:template>

</xsl:stylesheet>