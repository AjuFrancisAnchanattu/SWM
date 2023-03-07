<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="CCR_opportunitiesReport">

		<table width="100%" cellpadding="10" border="0">
			<tr>
				<td>
		
					<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
						<div style="float: right;">
							<a href="#"><img src="/images/ccr/view.png" height="15" width="15" alt="Toggle Filters" onclick="return toggle_display('filters')"/></a>
						</div>
						<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Filters</p>
					</div></div></div></div>
						
						
						<div id="filters">
								<xsl:apply-templates select="filtersForm"/>
						</div>
						
					
						
					<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
						<div style="float: right;">
							<a href="#"><img src="/images/ccr/view.png" height="15" width="15" alt="Toggle Report Options" onclick="return toggle_display('report')"/></a>
						</div>
						<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Report Options</p>
					</div></div></div></div>
						
						<!--<div id="report" style="display:none;">-->
							<div id="report">
							Results <xsl:value-of select="resultStart"/> to <xsl:value-of select="resultEnd"/> of <xsl:value-of select="resultCount"/>
							<br />
							Page: <xsl:apply-templates select="reportPage"/>
							<xsl:apply-templates select="reportForm"/>
						</div>
						

						<div class="snapin_top"><div class="snapin_top_1"><div class="snapin_top_2"><div class="snapin_top_3">
							<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Results</p>
						</div></div></div></div>
						<div style="padding-top: 5px;">
							<table width="100%" align="center" bgcolor="#CCCCCC" style="border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">	
								<xsl:choose>
									<xsl:when test="/page/CCR_opportunitiesReport/opportunity/">
										<xsl:apply-templates select="field" />
										<xsl:apply-templates select="opportunity" />
									</xsl:when>
									<xsl:otherwise>
										<tr align="center" height="20" bgcolor="#FFFFFF"><td colspan="13">No Results Found</td></tr>
									</xsl:otherwise>
								</xsl:choose>
							</table>
						</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="filtersForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="reportForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="field">
		<th width="1%" bgcolor="#DDDDDD"><a href="./opportunitiesReport?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=ASC"><img src="/images/up.gif" border="0" alt="" /></a><a href="./opportunitiesReport?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=DESC"><img src="/images/down.gif" border="0" alt="" /></a></th>
		<th bgcolor="#DDDDDD"><xsl:value-of select="fieldName" /></th>
	</xsl:template>
	
	<xsl:template match="opportunity">
		<tr align="center" height="20" bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#EFEFEF'" onmouseout="this.style.backgroundColor='#FFFFFF'">
			<td colspan="2"><xsl:value-of select="materialKey" /></td>	
			<td colspan="2"><xsl:value-of select="priority" /></td>	
			<td colspan="2"><xsl:value-of select="annual_volume" /></td>
			<td colspan="2"><xsl:value-of select="fiscal_volume" /></td>
			<td colspan="2"><xsl:value-of select="annual_value" /></td>
			<td colspan="2"><xsl:value-of select="fiscal_value" /></td>
			<td colspan="2"><xsl:value-of select="budget_value" /></td>
			<td colspan="2"><xsl:value-of select="success_chance" /></td>
			<td colspan="2"><xsl:value-of select="project_start_date" /></td>
			<td colspan="2"><xsl:value-of select="project_owner" /></td>
			<td colspan="2"><xsl:value-of select="site" /></td>
			<td colspan="2"><xsl:value-of select="customer_group" /></td>
			<td colspan="2"><xsl:value-of select="business_unit" /></td>
			<td colspan="2" bgcolor="{nextActionColour}"><xsl:value-of select="nextAction" /></td>	
		</tr>
	</xsl:template>
	
	<xsl:template match="reportPage">
		<xsl:choose>
			<xsl:when test="selected='no'">
				<a href="opportunitiesReport?report={reportID}&amp;orderBy={orderBy}&amp;type={type}&amp;offset={offset}"><xsl:value-of select="number"/> </a>
			</xsl:when>
			<xsl:otherwise>
				<b> <xsl:value-of select="number"/> </b>
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>
		
</xsl:stylesheet>