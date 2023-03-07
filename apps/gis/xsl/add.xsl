<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="gis.xsl"/>
	
	<xsl:template match="gisAdd">
	
		<script language="Javascript" src="/apps/gis/javascript/material_quantity.js" type="text/javascript">-</script>
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				<div class="title-boxblue">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:gis_details}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
					
				<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						<tr>
							<td width="40%"><strong>{TRANSLATE:gis_id}:</strong></td>
							<td width="40%"><xsl:value-of select="gisno" /></td>
						</tr>	
						<tr>
							<td width="40%"><strong>{TRANSLATE:profile_name}:</strong></td>
							<td width="40%"><xsl:value-of select="custName" /></td>
						</tr>
						<tr>
							<td><strong>Initiator:</strong></td>
							<td><xsl:value-of select="initiator" /></td>
						</tr>
						<tr>
							<td width="40%"><strong>{TRANSLATE:submission_date}:</strong></td>
							<td width="40%"><xsl:value-of select="initialSubmissionDate"/></td>
						</tr>	
<!--						<tr>
							<td width="40%"><strong>{TRANSLATE:you_are_at_stage}:</strong></td>
							<td width="40%">{TRANSLATE:<xsl:value-of select="gisReport/form/@name"/>_report}</td>
						</tr>	
-->				<!--</table>
				--><br />
				<xsl:if test="newgisCheck='yes'">
				<!--<table width="100%" cellspacing="0" cellpadding="4" class="indented">
				-->	<tr>
						<td width="100%" colspan="2"><div align="center"><input type="submit" value="View gis" onclick="buttonLink('view?gis={gisno}&amp;status=gis')" /></div></td>
					</tr>
				</xsl:if>
				</table>
				<br />
									
				<!--<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:report_summary}</p>
					</div></div></div></div>
					
					<table width="100%" cellspacing="0" cellpadding="4" class="indented">
						
						<xsl:apply-templates select="reportNav" />
						
						
					</table>-->
					
					
					
					<br />
					
					<xsl:apply-templates select="orderControl" />
					
					<br />
					
				</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="gisReport" />
					
				</td>
			</tr>

		</table>
		
		<xsl:if test="whichAnchor">
			<script language="javascript">
				document.onload = moveToWhere();
				function moveToWhere(){
					var curtop = 0;
					var obj = document.getElementById('<xsl:value-of select="whichAnchor"/>');
					if (obj.offsetParent) {
						do {
							curtop += obj.offsetTop;
						} while (obj = obj.offsetParent);
					}
					window.scrollTo(0,(curtop-500));
				}
			</script>
		</xsl:if>
		
	</xsl:template>
	

	
	<xsl:template match="gisReport">
		<div class="title-box2">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
								</div>
							</div>
						</div>
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