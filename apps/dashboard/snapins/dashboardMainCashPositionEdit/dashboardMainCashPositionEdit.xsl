<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainCashPositionEdit">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
	
			<table cellspacing="2" width="260">
				
				<tr>
					<td colspan="2">{TRANSLATE:click_to_edit}</td>
				</tr>
			
				<xsl:for-each select="cashEntry">
					<tr>
						<xsl:choose>
							<xsl:when test="cashEntryLocked='1'">
								<td style="line-height: 20px;"><img src="/images/pad_lock.gif" align="absmiddle" style="padding-right: 5px;" alt="This report is being updated." /><xsl:value-of select="cashEntryCashDate" /></td>
								<td style="line-height: 20px;"><xsl:value-of select="cashEntryRegionName" /></td>
							</xsl:when>
							<xsl:otherwise>
								<td style="line-height: 20px;"><a href="{cashEntryEditLink}"><xsl:value-of select="cashEntryCashDate" /></a></td>
								<td style="line-height: 20px;"><a href="{cashEntryEditLink}"><xsl:value-of select="cashEntryRegionName" /></a></td>
							</xsl:otherwise>
						</xsl:choose>
					</tr>
				</xsl:for-each>
				
			</table>
		
		</xsl:when>
		
		<xsl:otherwise>
			<div class="red_notification">
				<h1><strong>{TRANSLATE:access_denied}</strong></h1>
			</div>
		</xsl:otherwise>
		
		</xsl:choose>
	</xsl:template>
    
</xsl:stylesheet>