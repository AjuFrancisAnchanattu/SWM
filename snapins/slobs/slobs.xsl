<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="slobActions">
	<table cellspacing="0" width="260">
				<xsl:choose>
         			<xsl:when test="actionCount > 0">	
         			    <tr><strong>Your Input is Required</strong></tr>
         				<tr><td><strong>ID</strong></td><td><strong>Creator</strong></td><td><strong>Material No</strong></td></tr>
						<xsl:apply-templates select="slobAction" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td>None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
			</table>
	</xsl:template>
	
	<xsl:template match="slobAction">
		
    	<tr><td><a href="/apps/slobs/resume?slob={id}&amp;status={link}"><xsl:value-of select="id" /></a></td><td><xsl:value-of select="creator" /></td><td><xsl:value-of select="material_number" /></td></tr>
    </xsl:template>
    


</xsl:stylesheet>