<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="commsDetails">
		<table cellspacing="2" width="260">
			<xsl:choose>
        		<xsl:when test="reportCount > 0">	
					<xsl:apply-templates select="comms_details" />
				</xsl:when>
   	     		<xsl:otherwise>
       				<xsl:if test="commAdmin='true'">
						</xsl:if>
            			<tr>
							<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="scapaVision?">{TRANSLATE:scapa_vision}</a></td>
						</tr>
						<tr>
							<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="faq?">{TRANSLATE:frequently_asked_questions}</a></td>
						</tr>
	            		<tr>
							<td><img src="/images/icons2020/copy.jpg" align="absmiddle" /> <a href="leanSixSigma?">{TRANSLATE:lean_six_sigma}</a></td>
						</tr>
      		</xsl:otherwise>
			</xsl:choose>
		</table>
	</xsl:template>
	
	<xsl:template match="comms_details">
		<xsl:choose>
			<xsl:when test="commstatus='true'">
				<tr><td><br /><a href="/apps/comms/pdf/printcomm?comm={id}&amp;status=comm" target="_blank"><strong>Print</strong></a> (PDF) | <a href="view?comm={id}&amp;status=comm"><strong>{TRANSLATE:view}</strong></a> | <a href="resume?comm={id}&amp;status=comm"><strong>{TRANSLATE:edit}</strong></a> {TRANSLATE:comm}</td></tr>
			</xsl:when>
			<xsl:when test="commstatus='false'">
				<tr><td><a href="add?"><strong>Add</strong></a> {TRANSLATE:comm}<br /></td></tr>
			</xsl:when>
			<xsl:otherwise>
				<tr>No comm section exists!</tr>
			</xsl:otherwise>
		</xsl:choose>
    </xsl:template>
    
</xsl:stylesheet>