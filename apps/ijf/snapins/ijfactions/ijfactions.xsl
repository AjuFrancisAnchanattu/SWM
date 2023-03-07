<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ijfActionsSnapin">
	<table cellspacing="0" width="260">
				<xsl:choose>
         			<xsl:when test="actionCountSnapin > 0">	
         			    <tr><td colspan="3"><div class="red"><strong>Your Input is Required</strong></div></td></tr>
         				<tr><td><strong>ID</strong></td><td><strong>Creator</strong></td><td><strong>Production Site</strong></td></tr>
						<xsl:apply-templates select="ijf_Action0" />
						<tr><td colspan="3"><hr size="1" noshade="noshade" /></td></tr>
					</xsl:when>
          			<xsl:otherwise>
          				<tr><td colspan="3">No Actions</td></tr>
          				<tr><td colspan="3"><hr size="1" noshade="noshade" /></td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	
       		 	<xsl:choose>
       		 	<xsl:when test="reportCount > 0">
         				<tr><td colspan="3"><strong>Your Reports - Open</strong></td></tr>
       		 			<tr><td><strong>ID</strong></td>
         				<td><strong>Stage</strong></td>
         				<td><strong>Waiting On</strong></td></tr>
						<xsl:apply-templates select="ijf_Report1" />
				</xsl:when>
          		<xsl:otherwise>
          			<tr><td colspan="3"><strong>Your Reports - Open</strong></td></tr>
          			<tr><td>None</td></tr>
         		</xsl:otherwise>
       		 	</xsl:choose>
       		 	
       		 	<xsl:choose>
       		 	<xsl:when test="reportCount2 > 0">
         				<tr><td colspan="3"><br /><strong>Your Reports - Closed</strong></td></tr>
       		 			<tr><td><strong>ID</strong></td>
         				<td><strong>Stage</strong></td>
         				<td><strong>Waiting On</strong></td></tr>
						<xsl:apply-templates select="ijf_Report2" />
				</xsl:when>
          		<xsl:otherwise>
          			<tr><td colspan="3"><br /><strong>Your Report - Closed</strong></td></tr>
          			<tr><td>None</td></tr>
         		</xsl:otherwise>
       		 	</xsl:choose>
       		 	
			</table>
	</xsl:template>
	
	<xsl:template match="ijf_Action0">
    	<tr><td><a href="/apps/ijf/resume?ijf={idReport0}&amp;status={linkReport0}"><xsl:value-of select="idReport0" /></a></td><td><xsl:value-of select="initiatorInfoReport0" /></td><td><xsl:value-of select="productionSiteReport0" /></td></tr>
    </xsl:template>
    
  	<xsl:template match="ijf_Report1">
    	<tr><td><a href="/apps/ijf/index?id={idReport}"><xsl:value-of select="idReport" /></a></td><td><xsl:value-of select="statusReport" /></td><td><xsl:value-of select="ownerReport" /></td></tr>
    </xsl:template>
    
    <xsl:template match="ijf_Report2">
    	<tr><td><a href="/apps/ijf/index?id={idReport2}"><xsl:value-of select="idReport2" /></a></td><td><xsl:value-of select="statusReport2" /></td><td><xsl:value-of select="ownerReport2" /></td></tr>
    </xsl:template>
    

</xsl:stylesheet>