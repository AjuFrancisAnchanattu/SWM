<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="employeedb.xsl"/>
	
	<xsl:template match="employeeView">
	
		
	
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" class="reportNav" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p><xsl:value-of select="name" /></p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4"  class="indented">
						
						<xsl:apply-templates select="employeeNavSection" />
						
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
	
	<xsl:template match="printControl">
	
		<br />
					
		<div style="text-align: center; padding: 5px;" class="indented">
			<xsl:if test="@print='true'">
				<input type="submit" value="Switch to View Mode" onclick="buttonPress('personal_details');" />
			</xsl:if>
			<xsl:if test="@print='false'">
				<input type="submit" value="View All Details" onclick="buttonPress('print');" />
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
			<input type="button" value="Switch to Edit Mode" onclick="window.location = '/apps/employeedb/edit?id={@id}&amp;action={@currentLocation}';" />
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