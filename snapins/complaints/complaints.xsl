<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaints">
	

			<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
			
				<table border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td><a href="#" onclick="Javascript:window.open('../apps/help/window/helpWindow?app={snapin_name}','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a></td>
					</tr>
				</table>
		
			</div></div></div></div>
			
			<div style="padding-top: 10px;">
	
			<table cellspacing="0" width="260">
				<tr><td>{TRANSLATE:complaints_you_own}</td></tr>
				<xsl:choose>
         			<xsl:when test="complaintCount > 0">	
						<xsl:apply-templates select="complaint_owned" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td>None</td></tr>
         		 	</xsl:otherwise>
       		 	</xsl:choose>
			</table>
			
			</div>

	
	</xsl:template>
	
	<xsl:template match="complaint_owned">
    	<tr><td><a href="/apps/complaint/default.aspx?complaint={number}"><xsl:value-of select="name" /> (<xsl:value-of select="number" />)</a></td></tr>
    </xsl:template>
    
</xsl:stylesheet>