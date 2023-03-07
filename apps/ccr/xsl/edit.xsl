<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="ccrEdit">
	
		
	
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
									
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:report} <xsl:value-of select="@id" /> {TRANSLATE:summary}</p>
					</div></div></div></div>

									
					<table width="100%" cellspacing="0" cellpadding="4"  class="indented">
						
						<xsl:apply-templates select="reportNav" />
						
						<tr>
							<td style="padding: 5px; text-align: center;">
						
								<input type="submit" value="Update Report" onclick="buttonPress('submit');" />
								
							</td>
						</tr>
						
					</table>
					
					
					<xsl:apply-templates select="viewToggle" />
					
					
					<br />
					
					<xsl:apply-templates select="reportControl" />
					<xsl:apply-templates select="technicalControl" />
					<xsl:apply-templates select="materialControl" />
					<xsl:apply-templates select="reportActionControl" />
					<xsl:apply-templates select="materialActionControl" />
					<xsl:apply-templates select="attachmentControl" />				
					
					
					<br />

				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="ccrReport" />
					<xsl:apply-templates select="ccrTechnical" />
					<xsl:apply-templates select="ccrMaterial" />
					<xsl:apply-templates select="ccrAction" />
					<xsl:apply-templates select="ccrReportAction" />
					<xsl:apply-templates select="ccrOpportunity" />
					<xsl:apply-templates select="ccrDelegate" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	
	
	<xsl:template match="viewToggle">
		
		<br />
	
		<div style="text-align: center; padding: 5px;" class="indented">
			<input type="button" value="Switch to View Mode" onclick="buttonLink('/apps/ccr/view?id={@report}&amp;action={@currentLocation}');" />
		</div>
		
	</xsl:template>
	
	
	
	<xsl:template match="ccrReport">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:edit_report}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	
	<xsl:template match="ccrTechnical">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:edit_technical_enquiry}</p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="technicalId" value="{@id}" />
		</xsl:if>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	
	<xsl:template match="ccrMaterial">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:edit_material} #<xsl:value-of select="@id+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="materialId" value="{@id}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrAction">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:edit_action} #<xsl:value-of select="@id+1" /> {TRANSLATE:for_material} #<xsl:value-of select="@material+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="actionId" value="{@id}" />
		</xsl:if>
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrReportAction">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:action} #<xsl:value-of select="@id+1" /> {TRANSLATE:for_report} (<xsl:value-of select="@ccrReportID" />)<xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="actionId" value="{@id}" />
		</xsl:if>
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>

	
	<xsl:template match="ccrOpportunity">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:edit_opportunity} #<xsl:value-of select="@id+1" /> {TRANSLATE:for_material} #<xsl:value-of select="@material+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="opportunityId" value="{@id}" />
		</xsl:if>
			
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		
		<xsl:apply-templates select="form" />
	
		
	</xsl:template>
	
	
	<xsl:template match="ccrDelegate">
					
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Delegate action #<xsl:value-of select="@id+1" /> for Material #<xsl:value-of select="@material+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
		</div></div></div></div>
	
		<xsl:apply-templates select="form" />
	
	</xsl:template>
	
	
</xsl:stylesheet>