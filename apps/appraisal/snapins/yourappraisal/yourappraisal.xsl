<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="appraisalReports">
			<table cellspacing="0" width="260">
			
				<xsl:choose>
         			<xsl:when test="reportCount > 0">	
         				<tr><td><strong>Name</strong></td><td><strong>Site</strong></td></tr>
						<xsl:apply-templates select="appraisal_Report" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3">None</td></tr>
         		 	</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="savedReportCount > 0">
						<tr><td colspan="3"><hr size="1" style="color: #999999;" /><strong>Saved Appraisal Forms</strong></td></tr>

						<xsl:for-each select="savedappraisal">
							<xsl:choose>
							<xsl:when test="savedappraisalID > 0">
								<tr><td colspan="3"><a href="/apps/appraisal/index?delSavedForm=1&amp;sfID={savedID}">Del</a> - <xsl:value-of select="appraisalType"/><xsl:value-of select="savedType"/> - <a href="/apps/appraisal/resume?sfID={savedID}&amp;appraisal={savedappraisalID}&amp;status={savedType}"><xsl:value-of select="savedDate"/></a></td></tr>
							</xsl:when>
							<xsl:otherwise>
								<tr><td colspan="3"><a href="/apps/appraisal/index?delSavedForm=1&amp;sfID={savedID}">Del</a> - <xsl:value-of select="appraisalType"/><xsl:value-of select="savedType"/> - <a href="/apps/appraisal/add?sfID={savedID}"><xsl:value-of select="savedDate"/></a></td></tr>
							</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>

					</xsl:when>
					<xsl:otherwise>
						<tr><td colspan="3"><hr size="1" style="color: #999999;" /><strong>Saved Appraisal Forms</strong></td></tr>
						<tr><td colspan="3">None</td></tr>
					</xsl:otherwise>
       		 		</xsl:choose>
			</table>
	</xsl:template>
	
	<xsl:template match="appraisal_Report">
    	<tr>
	    	<td>
	    		<a href="index?id={id}"><xsl:value-of select="person" /></a>
	    	</td>
	    	<td>
	    		<xsl:value-of select="site" />
	    	</td>
    	</tr>
    </xsl:template>
    
</xsl:stylesheet>