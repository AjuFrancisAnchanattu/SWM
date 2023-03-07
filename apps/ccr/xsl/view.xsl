<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>
	
	<xsl:template match="ccrView">
	
		
	
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" class="reportNav" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Report <xsl:value-of select="@id" /> Summary</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4"  class="indented">
						
						<xsl:apply-templates select="reportNav" />
						
					</table>
					
					<xsl:apply-templates select="editToggle" />
					
					<xsl:apply-templates select="printControl" />
					
					<br />
					
					<xsl:apply-templates select="reportControl" />
					<xsl:apply-templates select="reportActionControl" />
					<xsl:apply-templates select="materialActionControl" />
					
					<br />

				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="ccrReport" />
					<xsl:apply-templates select="ccrReportAction" />
					<xsl:apply-templates select="ccrTechnical" />
					<xsl:apply-templates select="ccrMaterial" />
					<xsl:apply-templates select="ccrAction" />
					<xsl:apply-templates select="ccrOpportunity" />
					<xsl:apply-templates select="ccrDelegate" />
					
					<xsl:apply-templates select="log" />
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	
	<xsl:template match="printControl">
	
		<br />
					
		<div style="text-align: center; padding: 5px;" class="indented">
			<xsl:if test="@print='true'">
				<input type="submit" value="Switch to View Mode" onclick="buttonPress('report');" />
			</xsl:if>
			<xsl:if test="@print='false'">
				<input type="submit" value="View Entire CCR" onclick="buttonPress('print');" />
			</xsl:if>
		</div>
		
		<xsl:if test="@print='false'">
			<br />
			
			<div style="text-align: center; padding: 5px;" class="indented">
				<input type="button" value="Print Page" onclick="window.print();" />
			</div>
		</xsl:if>
		
		<xsl:if test="@print='true'">
			<br />
			
			<div style="text-align: center; padding: 5px;"  class="indented">
				<input type="button" value="Print CCR" onclick="window.print();" />
			</div>
		</xsl:if>
		
	</xsl:template>
	

	<xsl:template match="editToggle">
		<br />
	
		<div style="text-align: center; padding: 5px;" class="indented">
			<input type="button" value="Switch to Edit Mode" onclick="buttonLink('/apps/ccr/edit?id={@report}&amp;action={@currentLocation}');" />
		</div>
		
	</xsl:template>
	
	
	
	<xsl:template match="ccrReport">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Report <xsl:if test="@id != ''"> (<xsl:value-of select="@id"/>)</xsl:if><xsl:if test="@customerName != ''"> - <xsl:value-of select="@customerName"/></xsl:if></p>
		</div></div></div></div>
			
		<xsl:apply-templates select="form" />
				
	</xsl:template>
	
	
	<xsl:template match="ccrMaterial">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Material #<xsl:value-of select="@id+1" /><xsl:if test="@materialGroupID != ''"> (<xsl:value-of select="@materialGroupID"/>)</xsl:if></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="materialId" value="{@id}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrAction">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Action #<xsl:value-of select="@id+1" /> for Material #<xsl:value-of select="@material+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
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
			<p>Action #<xsl:value-of select="@id+1" /> for Report (<xsl:value-of select="@ccrReportID" />)<xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
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
			<p>Opportunity #<xsl:value-of select="@id+1" /> for Material #<xsl:value-of select="@material+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="opportunityId" value="{@id}" />
		</xsl:if>
			
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		
		<xsl:apply-templates select="form" />
	
		
	</xsl:template>
	
	
	<xsl:template match="ccrTechnical">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Technical Enquiry #<xsl:value-of select="@id+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="technicalId" value="{@id}" />
		</xsl:if>		
		
		<xsl:apply-templates select="form" />
	
		
	</xsl:template>
	
	
	<xsl:template match="ccrDelegate">
					
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>Delegate action #<xsl:value-of select="@id+1" /> for Material #<xsl:value-of select="@material+1" /><xsl:if test="@person != ''"> (<xsl:value-of select="@person"/>)</xsl:if></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="actionId" value="{@id}" />
		</xsl:if>
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
	
		<xsl:apply-templates select="form" />
	
	</xsl:template>
	
	
	<xsl:template match="materialActionControl">
		<xsl:if test="@isOwner='true' and @isComplete='false'">
			<xsl:if test="@id">
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>Action options</p>
				</div></div></div></div>
					
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
					<tr>
						<td style="border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
						
							<table border="0" width="100%">							
								<tr>
									<td>Delegate selected Action</td>
									<td style="text-align: right;"><input type="button" value="Delegate" onclick="linkFormSubmit('delegatematerialaction_{@material}_{@id}', 'false');" /></td>
								</tr>
								<tr>
									<td>Complete selected Action</td>
									<td style="text-align: right;"><input type="button" value="Complete" onclick="linkFormSubmit('completematerialaction_{@material}_{@id}', 'false');" /></td>
								</tr>
							</table>
							
						</td>
					</tr>
				</table>
				
				<br />
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="reportActionControl">
		<xsl:if test="@isOwner='true' and @isComplete='false'">
			<xsl:if test="@id">
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>Action options</p>
				</div></div></div></div>
				
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
					<tr>
						<td style="border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
						
							<table border="0" width="100%">
								<tr>
									<td>Delegate Action</td>
									<td style="text-align: right;"><input type="button" value="Delegate" onclick="linkFormSubmit('delegatereportaction_{@id}', 'false');" /></td>
								</tr>
								<tr>
									<td>Complete selected Action</td>
									<td style="text-align: right;"><input type="button" value="Complete" onclick="linkFormSubmit('completereportaction_{@id}', 'false');" /></td>
								</tr>
							</table>
							
						</td>
					</tr>
				</table>
				
				<br />
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
</xsl:stylesheet>