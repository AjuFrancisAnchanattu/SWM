<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="index.xsl"/>
	
	<xsl:template match="addDocLink">
	
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="docLinkReport" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	

	
	<xsl:template match="docLinkReport">
		<div class="heading_top">
			<div class="heading_top_1">
				<div class="heading_top_2">
					<div class="heading_top_3">
						<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:add_document_link}</p>
					</div>
				</div>
			</div>
		</div>
		<xsl:if test="@orderId">
			<input type="hidden" name="orderId" value="{@orderId}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	

	
</xsl:stylesheet>