<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="IJFsearch">
	
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
						<xsl:when test="acceptedRejected=1">
							<option value="acceptedRejected" selected="1">{TRANSLATE:acceptedRejected}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="acceptedRejected">{TRANSLATE:acceptedRejected}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="barManView=1">
							<option value="barManView" selected="1">{TRANSLATE:bar_man_view_request}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="barManView">{TRANSLATE:bar_man_view_request}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="businessUnit=1">
							<option value="businessUnit" selected="1">{TRANSLATE:bu}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="businessUnit">{TRANSLATE:bu}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="colour=1">
							<option value="colour" selected="1">{TRANSLATE:colour}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="colour">{TRANSLATE:colour}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerAccountNumber=1">
							<option value="customerAccountNumber" selected="1">{TRANSLATE:customer_account_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerAccountNumber">{TRANSLATE:customer_account_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerCountry=1">
							<option value="customerCountry" selected="1">{TRANSLATE:customer_country}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerCountry">{TRANSLATE:customer_country}</option>
						</xsl:otherwise>
					</xsl:choose>
							
					<xsl:choose>
						<xsl:when test="customerName=1">
							<option value="customerName" selected="1">{TRANSLATE:customer_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerName">{TRANSLATE:customer_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="daSapPartNumber=1">
							<option value="daSapPartNumber" selected="1">{TRANSLATE:da_sap_part_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="daSapPartNumber">{TRANSLATE:da_sap_part_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="initialSubmissionDate=1">
							<option value="initialSubmissionDate" selected="1">{TRANSLATE:date_entered}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="initialSubmissionDate">{TRANSLATE:date_entered}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="initiatorInfo=1">
							<option value="initiatorInfo" selected="1">{TRANSLATE:initiator_info_report}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="initiatorInfo">{TRANSLATE:initiator_info_report}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="materialGroup=1">
							<option value="materialGroup" selected="1">{TRANSLATE:material_group}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="materialGroup">{TRANSLATE:material_group}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="productionSite=1">
							<option value="productionSite" selected="1">{TRANSLATE:production_site}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="productionSite">{TRANSLATE:production_site}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="reasonIJF=1">
							<option value="reasonIJF" selected="1">{TRANSLATE:reason_for_ijf}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="reasonIJF">{TRANSLATE:reason_for_ijf}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="routing=1">
							<option value="routing" selected="1">{TRANSLATE:routing}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="routing">{TRANSLATE:routing}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="salesRep=1">
							<option value="salesRep" selected="1">{TRANSLATE:sales_rep}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="salesRep">{TRANSLATE:sales_rep}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="location_owner=1">
							<option value="location_owner" selected="1">{TRANSLATE:send_ijf_to_location}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="location_owner">{TRANSLATE:send_ijf_to_location}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="status=1">
							<option value="status" selected="1">{TRANSLATE:status}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="status">{TRANSLATE:status}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="toolsRequired=1">
							<option value="toolsRequired" selected="1">{TRANSLATE:tools_required}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="toolsRequired">{TRANSLATE:tools_required}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<!-- Not needed at the moment!
					<xsl:choose>
						<xsl:when test="sellingSalesOrganistation=1">
							<option value="sellingSalesOrganistation" selected="1">{TRANSLATE:sellingSalesOrganistation}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sellingSalesOrganistation">{TRANSLATE:sellingSalesOrganistation}</option>
						</xsl:otherwise>
					</xsl:choose>
					-->	
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
							<xsl:when test="acceptedRejected=1">
								<option value="acceptedRejected">{TRANSLATE:acceptedRejected}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="barManView=1">
								<option value="barManView">{TRANSLATE:bar_man_view_request}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="businessUnit=1">
								<option value="businessUnit">{TRANSLATE:bu}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="colour=1">
								<option value="colour">{TRANSLATE:daSapPartNumcolourber}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="customerAccountNumber=1">
								<option value="customerAccountNumber">{TRANSLATE:customer_account_number}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="customerCountry=1">
								<option value="customerCountry">{TRANSLATE:customer_country}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="customerName=1">
								<option value="customerName">{TRANSLATE:customer_name}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="daSapPartNumber=1">
								<option value="daSapPartNumber">{TRANSLATE:daSapPartNumber}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="initialSubmissionDate=1">
								<option value="initialSubmissionDate">{TRANSLATE:date_entered}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="initiatorInfo=1">
								<option value="initiatorInfo">{TRANSLATE:initiator_info_report}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="materialGroup=1">
								<option value="materialGroup">{TRANSLATE:material_group}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="productionSite=1">
								<option value="productionSite">{TRANSLATE:production_site}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="reasonIJF=1">
								<option value="reasonIJF">{TRANSLATE:reason_for_ijf}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="routing=1">
								<option value="routing">{TRANSLATE:routing}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="salesRep=1">
								<option value="salesRep">{TRANSLATE:sales_rep}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="location_owner=1">
								<option value="location_owner">{TRANSLATE:send_ijf_to_location}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="status=1">
								<option value="status">{TRANSLATE:status}</option>
							</xsl:when>
						</xsl:choose>
						
						<xsl:choose>
							<xsl:when test="toolsRequired=1">
								<option value="toolsRequired">{TRANSLATE:tools_required}</option>
							</xsl:when>
						</xsl:choose>
						
						<!-- Not needed at the moment!
						<xsl:choose>
							<xsl:when test="sellingSalesOrganistation=1">
								<option value="sellingSalesOrganistation">{TRANSLATE:sellingSalesOrganistation}</option>
							</xsl:when>
						</xsl:choose>
						-->
	
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