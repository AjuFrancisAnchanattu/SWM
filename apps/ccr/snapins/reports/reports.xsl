<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccrReports">
		<table cellspacing="0" width="260">
		
			<tr><td colspan="3"><strong>Open reports:</strong></td></tr>
		
			<xsl:choose>
     			<xsl:when test="openReportCount > 0">	
					<xsl:apply-templates select="openReport" />
				</xsl:when>
      			<xsl:otherwise>
        			<tr><td colspan="3">None</td></tr>
     		 	</xsl:otherwise>
   		 	</xsl:choose>
   		 	
   		 	<tr><td colspan="3" style="padding-top: 7px;"><strong>Closed reports:</strong></td></tr>
   		 	
   		 	<xsl:choose>
     			<xsl:when test="closedReportCount > 0">	
					<xsl:apply-templates select="closedReport" />
				</xsl:when>
      			<xsl:otherwise>
        			<tr><td colspan="3">None</td></tr>
     		 	</xsl:otherwise>
   		 	</xsl:choose>
		</table>
	</xsl:template>
	
	<xsl:template match="openReport">
    	<tr><td><a href="/apps/ccr/index?id={id}"><xsl:value-of select="id" /></a></td><td><xsl:value-of select="customerName" /></td><td><xsl:value-of select="reportDate" /></td></tr>
    </xsl:template>
    
    <xsl:template match="closedReport">
    	<tr><td><a href="/apps/ccr/index?id={id}"><xsl:value-of select="id" /></a></td><td><xsl:value-of select="customerName" /></td><td><xsl:value-of select="reportDate" /></td></tr>
    </xsl:template>

</xsl:stylesheet>