<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="ccrAdd">
	
		
	
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:report_summary}</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						
						<xsl:apply-templates select="reportNav" />
						
						<tr>
							<td style="padding: 5px; text-align: center;">
						
								<input type="submit" value="Submit Report" onclick="buttonPress('submit');" />
								
							</td>
						</tr>
						
					</table>
					
					<br />
					
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
					<xsl:apply-templates select="ccrReportAction" />
					
					<xsl:apply-templates select="ccrTechnical" />
					
					<xsl:apply-templates select="ccrMaterial" />
					<xsl:apply-templates select="ccrAction" />
					<xsl:apply-templates select="ccrOpportunity" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	

	
	<xsl:template match="ccrReport">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:report}</p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrTechnical">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:technical_enquiry}</p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="technicalId" value="{@id}" />
		</xsl:if>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	
	<xsl:template match="ccrMaterial">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
				<p>{TRANSLATE:material_group} #<xsl:value-of select="@id+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="materialId" value="{@id}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrAction">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:action} #<xsl:value-of select="@id+1" />

			<xsl:if test="@material">
				{TRANSLATE:for_material_group} #<xsl:value-of select="@material+1" />
			</xsl:if>
			
			<xsl:if test="@material">
				{TRANSLATE:for_report}
			</xsl:if>
					
			</p>
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
			<p>{TRANSLATE:action} #<xsl:value-of select="@id+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="actionId" value="{@id}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrOpportunity">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:opportunity} #<xsl:value-of select="@id+1" /> {TRANSLATE:for_material} #<xsl:value-of select="@material+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="opportunityId" value="{@id}" />
		</xsl:if>
			
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		
		<xsl:apply-templates select="form" />
		
		
	</xsl:template>

	
</xsl:stylesheet>