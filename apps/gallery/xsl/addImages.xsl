<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	
	
	
	<xsl:template match="addImages">
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
					
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add Images</p>
					</div></div></div></div>				
					<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>

	</xsl:template>
	
	
	
</xsl:stylesheet>
