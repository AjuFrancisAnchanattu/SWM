<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ijfReports">
			<table cellspacing="0" width="260">
			
      		<tr><td colspan="3"><a href="/apps/ijf/index">All IJF's</a> - <a href="/apps/ijf/index?viewReport=inProgress">IJF's In Progress</a></td></tr> 		 	
    			
     				<xsl:choose>
         			<xsl:when test="reportCount > 0">	
         	<tr><td colspan="3"><hr noshade="noshade" size="1" />(Only most recent 10 listed)</td></tr>
			<tr><td><strong>ID</strong></td><td><strong>Stage</strong></td><td><strong>Waiting On</strong></td></tr>
						<xsl:apply-templates select="ijf_Report" />
						<tr><td colspan="3"><hr noshade="noshade" size="1" />(+) Add Comment To IJF</td></tr>
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3"><hr noshade="noshade" size="1" />None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	
			</table>
	</xsl:template>
	
	<xsl:template match="ijf_Report">
    	<tr><td><a href="/apps/ijf/index?id={id}"><xsl:value-of select="id" /></a> <a href="ijfComments?mode=add&amp;ijfId={id}">+</a> </td><td><xsl:value-of select="status" /></td><td><xsl:value-of select="owner" /></td></tr>
    </xsl:template>
    


</xsl:stylesheet>