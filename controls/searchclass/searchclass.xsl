<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
		
	<xsl:template match="search">
	

		<table width="100%" cellpadding="10" border="0">
			<tr>
				<td>
					<xsl:if  test="report">
						<h1>{TRANSLATE:results_for_report}: <xsl:value-of select="reportName"/> (<a href="#" onclick="return toggle_display('filters')">{TRANSLATE:amend_report}</a>)</h1> 
					</xsl:if>
					
					<div>
							
						<xsl:choose>
							<xsl:when test="report">
								<div id="filters" style="display:none;">
									<h1>{TRANSLATE:edit_report}</h1>
									<xsl:apply-templates select="filterForm"/>
									<b>{TRANSLATE:selected_filters}:</b>
									
										<h1>{TRANSLATE:load_bookmarked_report}</h1>
									<xsl:apply-templates select="bookmarkForm"/>
								</div>
							</xsl:when>
							<xsl:otherwise>
								<div id="filters">
								
									
									<h1>{TRANSLATE:available_filters}</h1>
									<xsl:apply-templates select="filterSelectionForm"/>
									
									<h1>{TRANSLATE:applied_filters}</h1>
									
									<xsl:if test="count(filtersForm/form/group) = 0">

										<div style="background: #EEEEEE; margin: 0 5px 10px 5px; padding: 4px;">None</div>
									
									</xsl:if>
									
									<xsl:apply-templates select="filterForm"/>
									
									<h1>{TRANSLATE:columns}</h1>
									
									<xsl:apply-templates select="columnForm"/>
									
								</div>
							</xsl:otherwise>
						</xsl:choose>
							
						<xsl:if test="report">
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
								
						<xsl:if test="report">
							<table width="100%" align="center" bgcolor="#CCCCCC" style="border-right: 5px solid #FFFFFF; border-left: 5px solid #FFFFFF;">	
								<xsl:choose>
									<xsl:when test="report/reportRow">
										<xsl:apply-templates select="report" />
									</xsl:when>
									<xsl:otherwise>
										<tr align="center" height="20" bgcolor="#FFFFFF"><td colspan="13">{TRANSLATE:no_results_found}</td></tr>
									</xsl:otherwise>
								</xsl:choose>
							</table>
						</xsl:if>
										
					</div>
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="field">
		<th width="1%" bgcolor="#DDDDDD"><a href="./search?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=ASC"><img src="/images/up.gif" border="0" alt="" /></a><a href="./search?report={reportID}&amp;offset={offset}&amp;orderBy={fieldKey}&amp;type=DESC"><img src="/images/down.gif" border="0" alt="" /></a></th>
		<th bgcolor="#DDDDDD"><xsl:value-of select="fieldName" /></th>
	</xsl:template>
	
	<xsl:template match="report">
		<xsl:apply-templates select="field" />
		<xsl:apply-templates select="reportRow"/>
	</xsl:template>
	
	<xsl:template match="reportRow">
		<tr align="center" height="20" bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#EFEFEF'" onmouseout="this.style.backgroundColor='#FFFFFF'">
			<xsl:apply-templates select="column"/>
		</tr>
	</xsl:template>
	
	<xsl:template match="column">
		<td colspan="2"><xsl:value-of select="@value"/></td>	
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
	
	<xsl:template match="filterForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="filterSelectionForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	<xsl:template match="bookmarkForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="columnForm">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	<xsl:template match="searchResults">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td style="padding: 10px">
				
					<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
						<tr>
							<!--<td style="width: 350px;">
							
								<xsl:apply-templates select="form" />
							
							</td>
							<td style="padding-left: 10px;">-->
							<td>
								<table width="100%" cellspacing="0" cellpadding="4" style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px;">
									<tr>
										<td>Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" /></td>
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
								
										
									</tr>
								</table>
								
							
							</td>
						</tr>
					</table>
				
	
					<table width="100%" cellspacing="0" class="data_table" style="border: 1px solid #CCCCCC;">
					
						<xsl:apply-templates select="searchRowHeader"/>
						
						<xsl:apply-templates select="searchRow"/>
					
					</table>
		
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="firstPageLink">
		<a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page=1">First</a><span style="padding: 0 10px 0 10px;">...</span>
	</xsl:template>
	
	<xsl:template match="pageLink">
		<xsl:choose>
			<xsl:when test="@current='true'">
				<span style="font-weight: bold; padding-right: 10px;"><xsl:value-of select="text()" /></span>
			</xsl:when>
			<xsl:otherwise>
				<span style="padding-right: 10px;"><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}"><xsl:value-of select="text()" /></a></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="lastPageLink">
		<span style="padding-right: 10px;">...</span><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}">Last</a>
	</xsl:template>
	
	
	<xsl:template match="searchRowHeader">
		<tr>
			<xsl:apply-templates select="searchColumnHeader"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchRow">
		<tr>
			<xsl:apply-templates select="searchColumn"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchColumnHeader">
	
		<xsl:if test="sortable='1'">
			<xsl:element name="th">
				<xsl:attribute name="width">1%</xsl:attribute>
				<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
				<a href="search?action=view&amp;orderBy={field}&amp;order=ASC&amp;page={page}"><img src="/images/up.gif" border="0" alt="" /></a><a href="search?action=view&amp;orderBy={field}&amp;order=DESC&amp;page={page}"><img src="/images/down.gif" border="0" alt="" /></a>
			</xsl:element>	
		</xsl:if>
		
		<xsl:element name="th">
			<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
			<xsl:value-of select="title"/>
		</xsl:element>
	</xsl:template>
		
	
	<xsl:template match="searchColumn">
		<xsl:element name="td">
			<xsl:if test="@sortable='1'">
				<xsl:attribute name="colspan">2</xsl:attribute>
			</xsl:if>
			
			<xsl:apply-templates select="text"/>
			<xsl:apply-templates select="link"/>
			
		</xsl:element>	
	</xsl:template>
	
	<xsl:template match="text">
		<xsl:value-of select="text()"/>
	</xsl:template>
	
	<xsl:template match="link">
		<a href="{@url}"><xsl:value-of select="text()"/></a>
	</xsl:template>
	
		
</xsl:stylesheet>