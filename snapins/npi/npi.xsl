<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="npi">
	
	
			<table cellspacing="0" width="260">
				<tr><td>NPIs you initiated:</td></tr>
				<xsl:choose>
         			<xsl:when test="initialiseNPICount > 0">	
						<xsl:apply-templates select="npi_initialised" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td>None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	<tr><td><div class='red'>NPIs requiring your attention:</div></td></tr>
       		 	<xsl:choose>
         			<xsl:when test="attentionNPICount = '0'">	
         				<tr><td>None</td></tr>	
					</xsl:when>
          			<xsl:otherwise>
            			<xsl:apply-templates select="npi_attention" />
         		 	</xsl:otherwise>
       		 	</xsl:choose>
			</table>

	
	</xsl:template>
	
	<xsl:template match="npi_initialised">
    	<tr><td><a href="/apps/npi/default.aspx?ref={id}"><xsl:value-of select="id" /></a></td></tr>
        <tr><td><xsl:value-of select="dateRaised" /></td></tr>
        <tr><td><i><xsl:value-of select="status" /></i></td></tr>
    </xsl:template>
    
    <xsl:template match="npi_attention">
    	<tr><td><a href="/apps/npi/default.aspx?ref={id}"><xsl:value-of select="id" /></a></td></tr>
        <tr><td><xsl:value-of select="dateRaised" /></td></tr>
        <tr><td><i><xsl:value-of select="status" /></i></td></tr>
    </xsl:template>
</xsl:stylesheet>