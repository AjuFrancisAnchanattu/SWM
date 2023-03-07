<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsActions">
			<table cellspacing="0" width="260">
			
				<xsl:choose>
         			<xsl:when test="actionCount > 0">	
         				<tr><td><strong>ID</strong></td><td><strong>Stage</strong></td><td><strong>Waiting On</strong></td></tr>
						<xsl:apply-templates select="complaints_Action" />
						<tr><td colspan="3"><hr noshade="noshade" size="1" />(+) Add Comment To Complaint</td></tr>
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3">None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
       		 	
			</table>
	</xsl:template>
	
	<xsl:template match="complaints_Action">
    	<tr><td><a href="/apps/complaints/resume?complaint={id}&amp;status={link}"><xsl:value-of select="id" /></a> <a href="complaintsComments?mode=add&amp;complaintId={id}">+</a> </td><td><xsl:value-of select="status" /></td><td><xsl:value-of select="initiatorInfo" /></td></tr>
    </xsl:template>  


</xsl:stylesheet>