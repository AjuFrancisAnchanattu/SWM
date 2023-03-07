<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMain">
		<table cellspacing="0" width="260">
			<xsl:choose>
     			<xsl:when test="dashboardCount > 0">	
					<xsl:apply-templates select="dashboardMain_details" />
				</xsl:when>
      			<xsl:otherwise>
        			<tr><td colspan="3">{TRANSLATE:none}</td></tr>
     		 	</xsl:otherwise>
			</xsl:choose>
		</table>
	</xsl:template>
	
	<xsl:template match ="dashboardMain_details">
		In here
	</xsl:template>
    
</xsl:stylesheet>