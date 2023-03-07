<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccrActions">
			<table cellspacing="0" width="260">
				<xsl:choose>
         			<xsl:when test="actionCount > 0">	
						<xsl:apply-templates select="ccrAction" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td>None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
			</table>
	</xsl:template>
	
	<xsl:template match="ccrAction">
    	<tr>
    		<td><a href="/apps/ccr/view?action={id}"><xsl:value-of select="ccrId" /></a></td>
			<td><xsl:value-of select="actionArising" /></td>
			<td><xsl:value-of select="targetCompletion" /></td>
			<td>
    			<xsl:if test="status='OVERDUE'">
    			<span style="background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
			</td>
    	</tr>
    </xsl:template>

</xsl:stylesheet>