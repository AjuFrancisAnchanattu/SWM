<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="ccrDelegate">
	
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					
					
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:report} <xsl:value-of select="@id" /> {TRANSLATE:summary}</p>
					</div></div></div></div>

					
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						
						<xsl:apply-templates select="reportNav" />
						
					</table>			
					
					
					<br />

				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
					
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate action #<xsl:value-of select="@id+1" /> for Material #<xsl:value-of select="@material+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
					</div></div></div></div>
				
					<xsl:apply-templates select="form" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	
	
</xsl:stylesheet>