<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="search">

		<table width="100%" cellpadding="10" border="0">
			<tr>
				<td>
					<xsl:choose>
						<xsl:when test="@displayReport='yes'">
							<div id="filters" style="display:none;">
								<h1>{TRANSLATE:load_bookmarked_report}</h1>
								<xsl:apply-templates select="bookmarkedReportsForm"/>
								<h1>{TRANSLATE:create_new_report}</h1>
								<xsl:apply-templates select="filtersForm"/>
							</div>
						</xsl:when>
						<xsl:otherwise>
							<div id="filters">
								<h1>{TRANSLATE:load_bookmarked_report}</h1>
								<xsl:apply-templates select="bookmarkedReportsForm"/>
								<h1>{TRANSLATE:create_new_report}</h1>
								<xsl:apply-templates select="filtersForm"/>
							</div>
						</xsl:otherwise>
					</xsl:choose>
							
						
					
						
						
						<!--<div id="report" style="display:none;">-->
							
						
						<xsl:if test="@displayReport='yes'">
							<h1>{TRANSLATE:results_for_report}: <xsl:value-of select="reportName"/> (<a href="#" onclick="return toggle_display('filters')">{TRANSLATE:revise_report}</a>)</h1> 
							<div style="padding-top: 5px;">
							
							<xsl:if test="/page/search/report/">
								<table width="100%" align="center"  style="border-right: 2px solid #FFFFFF; border-left: 2px solid #FFFFFF;">	
									<tr>
										<td width="25%">
											<table cellpadding="10px" width="100%" align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;">
												<tr>
													<td>
														<input type="text" name="reportName" id="reportName" class="required"/>
													</td>
													<td>
														<input type="submit" value="save_report" onclick="buttonPress('bookmarkReport');" />
													</td>
												</tr>
											</table>
										</td>
										<td width="75%">
											<table cellpadding="3px" width="100%" align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;">
												<tr>
													<td>
														Results: <xsl:value-of select="resultStart"/> to <xsl:value-of select="resultEnd"/> of <xsl:value-of select="resultCount"/>
													</td>
												</tr>
												<tr>
													<td>
														<table cellpadding="0" cellspacing="0"><tr>
														<td>Page: </td><xsl:apply-templates select="reportPage"/>			
														</tr></table>
														
													</td>
												</tr>
											</table>
										</td>
										
									</tr>
								</table>	
								
							</xsl:if>
								<table width="100%" align="center" bgcolor="#CCCCCC" style="border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">	
									<xsl:choose>
										<xsl:when test="/page/search/report/">
											<xsl:apply-templates select="field" />
											<xsl:apply-templates select="report" />
										</xsl:when>
										<xsl:otherwise>
											<tr align="center" height="20" bgcolor="#FFFFFF"><td colspan="13">{TRANSLATE:no_results_found}</td></tr>
										</xsl:otherwise>
									</xsl:choose>
								</table>
							</div>
						</xsl:if>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="bookmarkedReportsForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="filtersForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="reportForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="field">
		<th width="1%" bgcolor="#DDDDDD"><a href="./search?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=ASC"><img src="/images/up.gif" border="0" alt="" /></a><a href="./search?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=DESC"><img src="/images/down.gif" border="0" alt="" /></a></th>
		<th bgcolor="#DDDDDD"><xsl:value-of select="fieldName" /></th>
	</xsl:template>
	
	<xsl:template match="report">
		<tr align="center" height="20" bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#EFEFEF'" onmouseout="this.style.backgroundColor='#FFFFFF'">
			<td colspan="2"><a href="view?id={id}"><xsl:value-of select="id" /></a></td>	
			<td colspan="2"><xsl:value-of select="owner" /></td>	
			<td colspan="2"><xsl:value-of select="reportDate" /></td>
			<td colspan="2"><xsl:value-of select="contactDate" /></td>	
			<td colspan="2"><xsl:value-of select="contactType" /></td>	
			<td colspan="2"><xsl:value-of select="existingNewBusiness" /></td>	
			<td colspan="2"><xsl:value-of select="status" /></td>	
			
			<td colspan="2"><xsl:value-of select="customer" /></td>	

		</tr>
	</xsl:template>
	
	<xsl:template match="reportPage">
		
		<xsl:choose>
			<xsl:when test="selected='no'">
				<td width="25px" align="center"><a href="search?report={reportID}&amp;orderBy={orderBy}&amp;type={type}&amp;offset={offset}"><xsl:value-of select="number"/> </a></td>
			</xsl:when>
			<xsl:otherwise>
				<td width="25px" align="center"><b> <xsl:value-of select="number"/> </b></td>
			</xsl:otherwise>
		</xsl:choose>
		
		
	</xsl:template>
		
</xsl:stylesheet>