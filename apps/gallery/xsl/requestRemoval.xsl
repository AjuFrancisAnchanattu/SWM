<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="requestRemovalPhoto">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">		
					<xsl:if test="error='1'">
						<div style="background: #f2d2d2; padding: 0 10px 0 10px; border: 1px solid #f2d2d2; margin-bottom: 10px;">
							<h1>Warning: Form submission error</h1>
							<!-- show which error here! -->
						</div>
					</xsl:if>
					
					<!-- Show the image here -->
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p><xsl:value-of select="fileName"/>.<xsl:value-of select="extension"/></p>
					</div></div></div></div>
					<table width="100%" cellpadding="4">
						<tr class="valid_row" align="center">
							<td class="valid_row">
								<img src="images/medium/{imageId}.{extension}" />
							</td>
						</tr>
					</table>				
					
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Request Removal</p>
					</div></div></div></div>				
					<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>

	</xsl:template>
	
	<xsl:template match="requestRemovalComment">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">		
					<xsl:if test="error='1'">
						<div style="background: #f2d2d2; padding: 0 10px 0 10px; border: 1px solid #f2d2d2; margin-bottom: 10px;">
							<h1>Warning: Form submission error</h1>
							<!-- show which error here! -->
						</div>
					</xsl:if>
					
					<!-- Show the image here -->
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Request Comment Removal</p>
					</div></div></div></div>				
					<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>

	</xsl:template>
	
</xsl:stylesheet>
