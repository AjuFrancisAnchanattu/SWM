<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="complaintsSearch">
	
	<table width="100%" cellpadding="0">
		<tr>
			<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
				<div id="snapin_left_container">
					
					<xsl:apply-templates select="snapin_left" />
				
				</div>
			</td>

			<td valign="top" style="padding: 10px;">

				<xsl:apply-templates select="error" />
			
				<!--<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px; margin-bottom: 10px;">
                   <p style="margin: 0; line-height: 15px;"><strong>Notice:</strong> This is still experimental code.</p>
                </div>-->
                
				<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
					<p>Create a New Search</p>
				</div></div></div></div>
				
				

				<xsl:apply-templates select="chooseReport"/>
				
				<xsl:apply-templates select="addFilters"/>
				
				<xsl:apply-templates select="selectedFilters"/>
				
				<div style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; padding: 5px; background #FFFFFF; text-align: center;">
					<input type="submit" value="Run Search" onclick="buttonPress('run');" />
				</div>
	
			</td>
		</tr>
	</table>
	
	</xsl:template>
	
	
	<xsl:template match="chooseReport">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	<xsl:template match="selectedFilters">
	
		<h1 style="margin-bottom: 10px;">Selected Filters</h1>
	
		<xsl:choose>
			<xsl:when test="form/group/row">
				<xsl:apply-templates select="form" />
			</xsl:when>
			<xsl:otherwise>
				<p style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; background: #DDDDDD; padding: 5px; margin-top: 0;">None</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="addFilters">
	
		<h1 style="margin-bottom: 10px;">Available Filters</h1>

		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	
	
	
	<xsl:template match="searchResults">
	
		<table width="100%" cellpadding="0">
			<!--<tr>
				<td style="padding-left: 10px; padding-right: 10px;">
				
				<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
	            	<p style="margin: 0; line-height: 15px;"><a href="search?action=view&amp;save=true"><strong>Save Bookmark</strong></a></p>
            	</div>
				</td>
			</tr>-->
			
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
										<!--<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 900px;"><a target="_blank" href="searchBookmarks?action=bookmark&amp;mode=excel&amp;bookmarkId={bookmarkId}"><img src="/images/excel.gif" border="0" /></a></td>-->
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
									</tr>
									<tr>
									</tr>
									<tr>
										<!--<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 1000px;"><a target="_blank" href="search?action=view&amp;mode=excel"><img src="/images/excel.gif" border="0" /></a></td>-->
										<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 900px;"><a target="_blank" href="searchBookmarks?action=bookmark&amp;mode=excel&amp;bookmarkId={bookmarkId}"><img src="/images/excel.gif" border="0" /></a></td>
									</tr>
								</table>			
							
							</td>
						</tr>
					</table>
				
	
					<table width="100%" cellspacing="0" class="data_table" style="border: 1px solid #CCCCCC;">
					
						<xsl:apply-templates select="searchRowHeader"/>
						
						<xsl:apply-templates select="searchRow"/>
					
					</table>
					
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
										<!--<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 900px;"><a target="_blank" href="searchBookmarks?action=bookmark&amp;mode=excel&amp;bookmarkId={bookmarkId}"><img src="/images/excel.gif" border="0" /></a></td>-->
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
									</tr>
									<tr>
									</tr>
									<tr>
										<!--<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 1000px;"><a target="_blank" href="search?action=view&amp;mode=excel"><img src="/images/excel.gif" border="0" /></a></td>-->
										<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 900px;"><a target="_blank" href="searchBookmarks?action=bookmark&amp;mode=excel&amp;bookmarkId={bookmarkId}"><img src="/images/excel.gif" border="0" /></a></td>
									</tr>
								</table>			
							
							</td>
						</tr>
					</table>
		
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="firstPageLink">
		<a href="searchBookmarks?action=bookmark&amp;bookmarkId={../bookmarkId}&amp;orderBy={@orderBy}&amp;order={@order}&amp;page=1">First</a><span style="padding: 0 10px 0 10px;">...</span>
	</xsl:template>
	
	<xsl:template match="pageLink">
		<xsl:choose>
			<xsl:when test="@current='true'">
				<span style="font-weight: bold; padding-right: 10px;"><xsl:value-of select="text()" /></span>
			</xsl:when>
			<xsl:otherwise>
				<span style="padding-right: 10px;"><a href="searchBookmarks?action=bookmark&amp;bookmarkId={../bookmarkId}&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}"><xsl:value-of select="text()" /></a></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="lastPageLink">
		<span style="padding-right: 10px;">...</span><a href="searchBookmarks?action=bookmark&amp;bookmarkId={../bookmarkId}&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}">Last</a>
	</xsl:template>
	
	
	<xsl:template match="searchRowHeader">
		<tr>
			<xsl:apply-templates select="searchColumnHeader"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchRow">
		<tr onmouseover="this.style.backgroundColor='#F4F4F4';" onmouseout="this.style.backgroundColor='#FFFFFF';">
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
				<a href="searchBookmarks?action=bookmark&amp;bookmarkId={../bookmarkId}&amp;orderBy={field}&amp;order=ASC&amp;page={page}"><img src="/images/up.gif" border="0" alt="" /></a><a href="searchBookmarks?action=bookmark&amp;bookmarkId={../bookmarkId}&amp;orderBy={field}&amp;order=DESC&amp;page={page}"><img src="/images/down.gif" border="0" alt="" /></a>
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
		<xsl:attribute name="style">border-right: 1px solid #DFDFDF;</xsl:attribute>
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