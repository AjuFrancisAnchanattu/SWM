<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="ccr.xsl"/>

	<xsl:template match="CCR_opportunities">
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					
					
					<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">		
					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Report Summary</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4" style="background: #EFEFEF; border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">
						
						<xsl:apply-templates select="opportunitynav" />
						
						<tr>
							<td style="padding: 5px; text-align: center;">
						
								<input type="submit" value="Submit Opportunity" onclick="buttonPress('submit');" />
								
							</td>
						</tr>
						
					</table>
					
					<br />
					
					<xsl:apply-templates select="actionControl" />
					<xsl:apply-templates select="attachmentControl" />				
					
					
					<br />

				</td>
			
				<td style="padding: 10px">
				
					<xsl:apply-templates select="error" />
					
					<xsl:apply-templates select="ccrReport" />
					<xsl:apply-templates select="ccrAction" />
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	<xsl:template match="ccrReport">
	
		<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Opportunity</p>
		</div></div></div></div>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>
	
	<xsl:template match="ccrAction">
	
		<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Action #<xsl:value-of select="@id+1" /></p>
		</div></div></div></div>
		
		<xsl:if test="@id">
			<input type="hidden" name="actionId" value="{@id}" />
		</xsl:if>
		<xsl:if test="@material">
			<input type="hidden" name="materialId" value="{@material}" />
		</xsl:if>
			
		<xsl:apply-templates select="form" />
		
	</xsl:template>

	
	<xsl:template match="opportunitynav">
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #DDDDDD;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">
					<!--<xsl:attribute name="style">background: #f2d2d2;</xsl:attribute>-->
					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="#" onclick="linkFormSubmit('opportunity', 'true');">Opportunity</a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="actionnav" />
		
	</xsl:template>
	
	
	<xsl:template match="actionnav">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #DDDDDD;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">
					<!--<xsl:attribute name="style">background: #f2d2d2;</xsl:attribute>-->
					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/action.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="#" onclick="linkFormSubmit('action_{@id}', 'true');">Action <xsl:value-of select="@id+1" /></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="actionnav" />
		<xsl:apply-templates select="opportunitynav" />
		
	</xsl:template>
	
	<xsl:template match="attachmentControl">
	
		<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">		
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Attach Document</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">
			<tr>
				<td style="background: #EFEFEF; border-top: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
					
				</td>
			</tr>
		</table>
		
		<xsl:apply-templates select="form" />
		
		
		<br />
		
	</xsl:template>
		
	<xsl:template match="actionControl">
	
		<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Action options</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" style="background: #EFEFEF; border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">
			<tr>
				<td style=" border-top: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;">
				
					<table border="0" width="100%">
						<tr>
							<td>Add an Action to this opportunity</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addaction');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>Delete selected Action</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removeaction_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
</xsl:stylesheet>