<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="GISsearch">
	
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
                
				<div class="title-box2">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">Create a New Search</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<xsl:apply-templates select="chooseReport"/>
				
				<xsl:apply-templates select="addFilters"/>
				
				<xsl:apply-templates select="columnFilters"/>
				
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
	<!-- this has been added also -->
		<script language="JavaScript">
			function selectAllColumns()
			{
				i = 0;
				var selColumns = document.getElementById('columns');
				if(selColumns)
				{
					while(i != selColumns.options.length)
					{
						selColumns.options[i].selected = true;
						i++;
					}
				}
			}
			selectAllColumns();
		</script>
		<!-- to here! -->
		
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
	
		<xsl:apply-templates select="IJFsearchMessage"/>
	
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
									<tr>
										<td rowspan="2" style="text-align: left; padding-left: 10px;"><a target="_blank" href="search?action=view&amp;mode=excel"><img src="/images/excel.gif" border="0" /></a></td>
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
	
	<!-- Added code from here to the end for the column filtering! -->
	
	<xsl:template match="columnFilters">
		 <script language="JavaScript">
			function moveSelectionRight()
			{
				var i = 0;
				var selColumns = document.getElementById('columns');
				while(i != document.form.columnsorig.length)
				{
					if(document.form.columnsorig.options[i].selected)
					{
						//we now need to check if it is already in the list - if not add
						var foundMatch = false;
						var j = 0;
						while(j != selColumns.options.length)
						{
							if(selColumns.options[j].value == document.form.columnsorig.options[i].value)
								foundMatch = true;
							j++;
						}
						if(!foundMatch)
							selColumns.options[selColumns.options.length] = new Option(document.form.columnsorig.options[i].value,document.form.columnsorig.options[i].value);
					}
					i++;
				}
				selectAllColumns()
			}
			function moveSelectionLeft()
			{	
				var i = 0;
				var toDelete = new Array();
				var selColumns = document.getElementById('columns');
				var loopLength = selColumns.options.length;
				i = (loopLength-1);
				while(i != -1)
				{
					if(selColumns.options[i].selected)
					{
						selColumns.options[i] = null;
					}
					i--;
				}
				selectAllColumns()
			}
		</script>
		<h1 style="margin-bottom: 10px;">Column Filters</h1>
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;">
		<tr id="filtersRow" class="valid_row"><td class="cell_name" width="15%" valign="top"></td>
		<td>

		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
				<xsl:element name="select">
				<xsl:attribute name="name">columnsorig</xsl:attribute>
				<xsl:attribute name="multiple">true</xsl:attribute>

					<xsl:choose>
						<xsl:when test="required = 'true'">
							<xsl:attribute name="class">dropdown required</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">dropdown optional</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>

					
					
					<xsl:choose>
						<xsl:when test="initiator=1">
							<option value="initiator" selected="1">{TRANSLATE:initiator}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="initiator">{TRANSLATE:initiator}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateAdded=1">
							<option value="dateAdded" selected="1">{TRANSLATE:dateAdded}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dateAdded">{TRANSLATE:dateAdded}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateUpdated=1">
							<option value="dateUpdated" selected="1">{TRANSLATE:dateUpdated}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dateUpdated">{TRANSLATE:dateUpdated}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="background=1">
							<option value="background" selected="1">{TRANSLATE:background}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="background">{TRANSLATE:background}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="corporateStructure=1">
							<option value="corporateStructure" selected="1">{TRANSLATE:corporateStructure}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="corporateStructure">{TRANSLATE:corporateStructure}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financialHighlights=1">
							<option value="financialHighlights" selected="1">{TRANSLATE:financialHighlights}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="financialHighlights">{TRANSLATE:financialHighlights}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="keyPersonnel=1">
							<option value="keyPersonnel" selected="1">{TRANSLATE:keyPersonnel}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="keyPersonnel">{TRANSLATE:keyPersonnel}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="marketSectorActivity=1">
							<option value="marketSectorActivity" selected="1">{TRANSLATE:marketSectorActivity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="marketSectorActivity">{TRANSLATE:marketSectorActivity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="productRangeActivity=1">
							<option value="productRangeActivity" selected="1">{TRANSLATE:productRangeActivity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="productRangeActivity">{TRANSLATE:productRangeActivity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="newProducts=1">
							<option value="newProducts" selected="1">{TRANSLATE:newProducts}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="newProducts">{TRANSLATE:newProducts}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="technicalComparisons=1">
							<option value="technicalComparisons" selected="1">{TRANSLATE:technicalComparisons}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="technicalComparisons">{TRANSLATE:technicalComparisons}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currentPricingLevels=1">
							<option value="currentPricingLevels" selected="1">{TRANSLATE:currentPricingLevels}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="currentPricingLevels">{TRANSLATE:currentPricingLevels}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="packagingAndBranding=1">
							<option value="packagingAndBranding" selected="1">{TRANSLATE:packagingAndBranding}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="packagingAndBranding">{TRANSLATE:packagingAndBranding}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="serviceLevels=1">
							<option value="serviceLevels" selected="1">{TRANSLATE:serviceLevels}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="serviceLevels">{TRANSLATE:serviceLevels}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="geographicActivity=1">
							<option value="geographicActivity" selected="1">{TRANSLATE:geographicActivity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="geographicActivity">{TRANSLATE:geographicActivity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="activeAccounts=1">
							<option value="activeAccounts" selected="1">{TRANSLATE:activeAccounts}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="activeAccounts">{TRANSLATE:activeAccounts}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="marketingActivity=1">
							<option value="marketingActivity" selected="1">{TRANSLATE:marketingActivity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="marketingActivity">{TRANSLATE:marketingActivity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="strengthWeakness=1">
							<option value="strengthWeakness" selected="1">{TRANSLATE:strengthWeakness}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="strengthWeakness">{TRANSLATE:strengthWeakness}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currentStrategy=1">
							<option value="currentStrategy" selected="1">{TRANSLATE:currentStrategy}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="currentStrategy">{TRANSLATE:currentStrategy}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="informationSources=1">
							<option value="informationSources" selected="1">{TRANSLATE:informationSources}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="informationSources">{TRANSLATE:informationSources}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="distributionStrategy=1">
							<option value="distributionStrategy" selected="1">{TRANSLATE:distributionStrategy}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="distributionStrategy">{TRANSLATE:distributionStrategy}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="summary=1">
							<option value="summary" selected="1">{TRANSLATE:summary}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="summary">{TRANSLATE:summary}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="website=1">
							<option value="website" selected="1">{TRANSLATE:website}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="website">{TRANSLATE:website}</option>
						</xsl:otherwise>
					</xsl:choose>
					

					
					
				</xsl:element>
				</td>
				<td width="2%"></td>
				<td> 
					<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveSelectionRight();" /> 
					<br />
					<br />
					<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveSelectionLeft();" /> 
				</td>
				<td width="2%"></td>
				<td valign="top">
					<xsl:element name="select">
					<xsl:attribute name="name">columns[]</xsl:attribute>
					<xsl:attribute name="id">columns</xsl:attribute>
					<xsl:attribute name="multiple">true</xsl:attribute>
	
						<xsl:attribute name="class">dropdown required</xsl:attribute>
	
						
						
					<xsl:choose>
						<xsl:when test="initiator=1">
							<option value="initiator">{TRANSLATE:initiator}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateAdded=1">
							<option value="dateAdded">{TRANSLATE:dateAdded}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateUpdated=1">
							<option value="dateUpdated">{TRANSLATE:dateUpdated}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="background=1">
							<option value="background">{TRANSLATE:background}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="corporateStructure=1">
							<option value="corporateStructure">{TRANSLATE:corporateStructure}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financialHighlights=1">
							<option value="financialHighlights">{TRANSLATE:financialHighlights}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="keyPersonnel=1">
							<option value="keyPersonnel">{TRANSLATE:keyPersonnel}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="marketSectorActivity=1">
							<option value="marketSectorActivity">{TRANSLATE:marketSectorActivity}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="productRangeActivity=1">
							<option value="productRangeActivity">{TRANSLATE:productRangeActivity}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="newProducts=1">
							<option value="newProducts">{TRANSLATE:newProducts}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="technicalComparisons=1">
							<option value="technicalComparisons">{TRANSLATE:technicalComparisons}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currentPricingLevels=1">
							<option value="currentPricingLevels">{TRANSLATE:currentPricingLevels}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="packagingAndBranding=1">
							<option value="packagingAndBranding">{TRANSLATE:packagingAndBranding}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="serviceLevels=1">
							<option value="serviceLevels">{TRANSLATE:serviceLevels}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="geographicActivity=1">
							<option value="geographicActivity">{TRANSLATE:geographicActivity}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="activeAccounts=1">
							<option value="activeAccounts">{TRANSLATE:activeAccounts}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="marketingActivity=1">
							<option value="marketingActivity">{TRANSLATE:marketingActivity}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="strengthWeakness=1">
							<option value="strengthWeakness">{TRANSLATE:strengthWeakness}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currentStrategy=1">
							<option value="currentStrategy">{TRANSLATE:currentStrategy}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="informationSources=1">
							<option value="informationSources">{TRANSLATE:informationSources}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="distributionStrategy=1">
							<option value="distributionStrategy">{TRANSLATE:distributionStrategy}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="summary=1">
							<option value="summary">{TRANSLATE:summary}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="website=1">
							<option value="website">{TRANSLATE:website}</option>
						</xsl:when>
					</xsl:choose>

						
						
					</xsl:element>
				</td>
			</tr>
		</table>

		</td>
		</tr>
		</table>

	</xsl:template>		
	
	<!-- Here is the end of the code added -->
	
</xsl:stylesheet>