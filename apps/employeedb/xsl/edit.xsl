<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="employeedb.xsl"/>
	
	<xsl:template match="employeeEdit">
	
		
	
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
									
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p><xsl:value-of select="name" /></p>
					</div></div></div></div>

									
					<table width="100%" cellspacing="0" cellpadding="4"  class="indented">
						
						<xsl:apply-templates select="employeeNavSection" />
						
						<tr>
							<td style="padding: 5px; text-align: center;">
						
								<input type="submit" value="Save" onclick="buttonPress('submit');" />
								
							</td>
						</tr>
						
					</table>
					
					
					<xsl:apply-templates select="viewToggle" />
					
					
					<br />
					
					<xsl:apply-templates select="reportControl" />
					<xsl:apply-templates select="materialControl" />
					<xsl:apply-templates select="reportActionControl" />
					<xsl:apply-templates select="materialActionControl" />
					<xsl:apply-templates select="attachmentControl" />				
					
					
					<br />

				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="personal_details"/>
					<xsl:apply-templates select="job_role"/>
					<xsl:apply-templates select="employment_history"/>
					<xsl:apply-templates select="it_information"/>
					<xsl:apply-templates select="asset_data"/>
					<xsl:apply-templates select="training"/>
					<xsl:apply-templates select="ppe_and_hse"/>
					
				</td>
			</tr>

		</table>
		
	</xsl:template>
	
	
	<xsl:template match="viewToggle">
		
		<br />
	
		<div style="text-align: center; padding: 5px;" class="indented">
			<input type="button" value="Switch to View Mode" onclick="window.location = '/apps/employeedb/view?id={@id}&amp;action={@currentLocation}';" />
		</div>
		
	</xsl:template>
	
	<xsl:template match="employeeNavSection">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('{@name}', 'true');">{TRANSLATE:<xsl:value-of select="@name" />}</a>
				</xsl:element>
			</xsl:element>
		</tr>
	
	</xsl:template>
	
	
</xsl:stylesheet>