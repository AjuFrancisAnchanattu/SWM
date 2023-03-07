<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
		
	
	
	
	
	<xsl:template match="moneyFilterQuantity">
	
		<xsl:element name="input">
		
			<xsl:attribute name="type">text</xsl:attribute>
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="id"><xsl:value-of select="name" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="value" /></xsl:attribute>
			<xsl:attribute name="maxlength"><xsl:value-of select="maxlength" /></xsl:attribute>
			<!-- Added by JM -->
			<xsl:attribute name="minlength"><xsl:value-of select="minlength" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class"><xsl:value-of select="cssClass"/> optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="onKeyPress">
				<xsl:attribute name="onKeyUp"><xsl:value-of select="onKeyPress" />();</xsl:attribute>
			</xsl:if>
			
			<xsl:if test="onChange">
				<xsl:attribute name="onChange"><xsl:value-of select="onChange" />();</xsl:attribute>
			</xsl:if>
			
		</xsl:element>
		
		<xsl:if test="anchorRef">
			<a name="{anchorRef}" id="{anchorRef}"></a>
		</xsl:if>
		
		<span style="padding-left: 8px;"><xsl:value-of select="legend" /></span>
		
		<xsl:choose>
			<xsl:when test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
			</xsl:when>
			<xsl:otherwise>
				
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
		
	
	<xsl:template match="moneyFilter">
		
		<script type="text/javascript" src="/apps/customerComplaints/lib/controls/moneyFilter.js">-</script>		 	
	
		<input type="hidden" name="{name}" value="dummy" />
		<table>
			<tr>
				<td>
					<xsl:apply-templates select="operation" />
				</td>
				<td>
					<xsl:apply-templates select="quantity" />
				</td>
			</tr>
		</table>	
		
	</xsl:template>
	
	
	<xsl:template match="operation">
		<xsl:apply-templates select="dropdown" />
	</xsl:template>
		
	
	<xsl:template match="complaintSearch">
	
		<link rel="stylesheet" href="/apps/customerComplaints/css/customerComplaints.css"/>
		
		<style>
			h1, h1:hover, h1:link {
				color: #333;
				text-decoration: none;
				border: none;
			}
			
			.columnsSelect {
				height: 198px;
			}
			
			#columnsSelect td {
				border: none;
			}
		
		</style>
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
		
				<td valign="top" style="padding: 10px;">
		
					<xsl:apply-templates select="error" />
		            
					<div class="title-box1">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<img src="../../images/famIcons/magnifier_zoom_in.png" alt="" class="titleIcon" />
								
								<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
									{TRANSLATE:create_new_search}
								</p>
								
							</div></div>
						</div></div></div></div>
					</div>
		
					<xsl:apply-templates select="chooseReport"/>
					
					<xsl:apply-templates select="addFilters"/>
					
					<xsl:apply-templates select="columnFilters"/>
					
					<xsl:apply-templates select="selectedFilters"/>
					
					<div style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; padding: 5px; background #FFFFFF; text-align: center;">
						<input type="submit" value="Run Search" onclick="buttonPress('run');" />
						<input type="submit" value="Remove All Filters" onclick="buttonPress('removeAllFilters');" />
					</div>
					
				</td>
			</tr>
		</table>
	
	</xsl:template>

	
	<xsl:template match="reportId">
		<!-- Don't show report id -->
	</xsl:template>
	
	
	<xsl:template match="bookmarkId">
		<!-- Don't show bookmark id -->
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
		
		<h1 style="margin-bottom: 10px; text-decoration: none;">Selected Filters</h1>
	
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
		
		<style>
			.searchResultsRow:hover {
				background: #eee;
			}
		</style>
		
		<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px; margin-left: 10px; margin-right: 10px;">
			<p style="margin: 0; line-height: 15px;">
				<a href="search?action=view&amp;save=true&amp;reportId={../reportId}">
					<strong>Save New Bookmark</strong>
				</a> | 
				<xsl:choose>
					<xsl:when test="../bookmarkId">
						<xsl:if test="../bookmarkEdited">
							<a href="search?action=view&amp;save=true&amp;reportId={../reportId}&amp;bookmarkId={../bookmarkId}">
								<strong>Update Existing Bookmark</strong>
							</a> | 
						</xsl:if>
						<a href="search?reportId={../reportId}&amp;bookmarkId={../bookmarkId}">
							<strong>Edit</strong>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="search?reportId={../reportId}">
							<strong>Edit</strong>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</p>
		</div>
		
		<div style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px; margin: 10px; height: 30px;">
			<div style="float: left; margin-right: 10px;">
				<a target="_blank" href="search?action=view&amp;mode=excel&amp;reportId={../reportId}">
					<img src="/images/excel.gif" border="0" />
				</a>
			</div>
			<div style="float: left; margin-top: -1px;">
				<p style="margin: 0; padding:0;">
					Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" />
				</p>
				<p style="margin: 0; padding:0;">
					Page:
					<xsl:apply-templates select="firstPageLink" />
					<xsl:apply-templates select="pageLink" />
					<xsl:apply-templates select="lastPageLink" />
				</p>
			</div>
		</div>
		
		<div id="searchResults" style="overflow-x: scroll; overflow-y: hidden; margin: 0 10px; border: 1px solid #CCCCCC; border-top: none;">
			<table width="100%" cellpadding="0" class="data_table">
							
				<xsl:apply-templates select="searchRowHeader"/>
				
				<xsl:apply-templates select="searchRow"/>
				
			</table>
		</div>
		
		<div style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px; margin: 10px; height: 30px;">
			<div style="float: left; margin-right: 10px;">
				<a target="_blank" href="search?action=view&amp;mode=excel&amp;reportId={../reportId}">
					<img src="/images/excel.gif" border="0" />
				</a>
			</div>
			<div style="float: left; margin-top: -1px;">
				<p style="margin: 0; padding:0;">
					Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" />
				</p>
				<p style="margin: 0; padding:0;">
					Page:
					<xsl:apply-templates select="firstPageLink" />
					<xsl:apply-templates select="pageLink" />
					<xsl:apply-templates select="lastPageLink" />
				</p>
			</div>
		</div>
		
		<script type="text/javascript">
			function DoubleScroll(element)
			{
				var scrollbar= document.createElement('div');
				scrollbar.appendChild(document.createElement('div'));
				scrollbar.style.overflow= 'scroll';
				scrollbar.style.overflowY= 'hidden';
				scrollbar.style.margin = '0 10px';
				scrollbar.style.paddingBottom = '3px';
				scrollbar.style.paddingTop = '0px';
				scrollbar.style.border = '1px solid #CCCCCC';
				scrollbar.style.borderBottom = 'none';
				scrollbar.firstChild.style.width= element.scrollWidth+'px';
				scrollbar.firstChild.style.height= "0px";
				
				scrollbar.onscroll= function()
				{
					element.scrollLeft= scrollbar.scrollLeft;
				};
				
				element.onscroll= function()
				{
					scrollbar.scrollLeft= element.scrollLeft;
				};
				
				element.parentNode.insertBefore(scrollbar, element);
			}
			
			DoubleScroll(document.getElementById('searchResults')); 
		</script> 
	
	</xsl:template>
	
	
	<xsl:template match="firstPageLink">
		<a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page=1&amp;reportId={../../../reportId}">First</a><span style="padding: 0 10px 0 10px;">...</span>
	</xsl:template>
	
	
	<xsl:template match="pageLink">
		<xsl:choose>
			<xsl:when test="@current='true'">
				<span style="font-weight: bold; padding-right: 10px;"><xsl:value-of select="text()" /></span>
			</xsl:when>
			<xsl:otherwise>
				<span style="padding-right: 10px;"><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}&amp;reportId={../../reportId}"><xsl:value-of select="text()" /></a></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="lastPageLink">
		<span style="padding-right: 10px;">...</span><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}&amp;reportId={../../reportId}">Last</a>
	</xsl:template>
	
	
	<xsl:template match="searchRowHeader">
		<tr>
			<xsl:apply-templates select="searchColumnHeader"/>
		</tr>	
	</xsl:template>
	
	
	<xsl:template match="searchRow">
		<tr class="searchResultsRow">
			<xsl:apply-templates select="searchColumn"/>
		</tr>	
	</xsl:template>
	
	
	<xsl:template match="searchColumnHeader">
	
		<xsl:element name="th">
			<xsl:attribute name="style">
				<xsl:if test="sortFocus='true'">
					background: #dcddf2;
				</xsl:if>
				border-left: #AAAAAA 1px solid;
				padding: 5px;
			</xsl:attribute>
			<xsl:value-of select="title"/>
		</xsl:element>
		
		<xsl:if test="sortable='1'">
			<xsl:element name="th">
				<xsl:attribute name="width">1%</xsl:attribute>
				<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
				<a href="search?action=view&amp;orderBy={field}&amp;order=ASC&amp;page={page}&amp;reportId={../../../reportId}">
					<img src="/images/up.gif" border="0" alt="" />
				</a>
				<a href="search?action=view&amp;orderBy={field}&amp;order=DESC&amp;page={page}&amp;reportId={../../../reportId}">
					<img src="/images/down.gif" border="0" alt="" />
				</a>
			</xsl:element>	
		</xsl:if>
		
	</xsl:template>
		
	
	<xsl:template match="searchColumn">
		<xsl:element name="td">
			<xsl:if test="@sortable='1'">
				<xsl:attribute name="colspan">2</xsl:attribute>
			</xsl:if>
			
			<xsl:attribute name="style">
				border-left: #AAAAAA 1px solid;
				border-bottom: #AAAAAA 1px solid;
				padding: 5px;
			</xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="link">
					<xsl:apply-templates select="link"/>
				</xsl:when>
				<xsl:when test="text = ''">
					-
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="text"/>
				</xsl:otherwise>
			</xsl:choose>			
			
			
		</xsl:element>	
	</xsl:template>
	
	
	<xsl:template match="text">
		<xsl:choose>
			<xsl:when test="stage">
				<xsl:apply-templates select="stage"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="text()"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="stage">
		<xsl:value-of select="text()"/>
		<br/>
	</xsl:template>
	
	
	<xsl:template match="link">
		<a href="{@url}"><xsl:value-of select="text()"/></a>
	</xsl:template>
	
	
	<xsl:template match="columnFilters">
	
<!--		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>-->
		
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
							{
								foundMatch = true;
							}
							j++;
						}
						
						if(!foundMatch)
						{
							var display = document.form.columnsorig.options[i].text;
							var value = document.form.columnsorig.options[i].value;
							
							selColumns.options[selColumns.options.length] = new Option(display , value);
						}
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
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;" id="columnsSelect">
			<tr id="filtersRow" class="valid_row">
				<td>
		
					<table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
						<tr>
							<td>
							<xsl:element name="select">
							<xsl:attribute name="name">columnsorig</xsl:attribute>
							<xsl:attribute name="multiple">true</xsl:attribute>
			
								<xsl:attribute name="class">dropdown optional columnsSelect</xsl:attribute>
									
									<xsl:apply-templates select="columnSelectionOption" />	
							
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
				
									<xsl:attribute name="class">dropdown required columnsSelect</xsl:attribute>
									
									<xsl:apply-templates select="columnSelectionOptionSelected" />						
									
								</xsl:element>
							</td>
						</tr>
					</table>
		
				</td>
			</tr>
		</table>

	</xsl:template>		
	
	
	<xsl:template match="columnSelectionOption">
	
		<option value="{name}">{TRANSLATE:<xsl:value-of select="name" />}</option>
	
	</xsl:template>		
	
	
	<xsl:template match="columnSelectionOptionSelected">
	
		<option value="{name}">{TRANSLATE:<xsl:value-of select="name" />}</option>
	
	</xsl:template>		
	
	
	<xsl:template match="row">
	
		<xsl:if test="@label">
		
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="@name" />LabelRow</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>
		
			<td colspan="2" style="background: #CCCCCC;"><strong><xsl:value-of select="@label" /></strong></td>
			
		</xsl:element>
		
		</xsl:if>
	
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="@name" />Row</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="@valid = 'true'">
					<xsl:attribute name="class">valid_row</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">invalid_row</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:element name="td">
			
				<xsl:attribute name="class">cell_name</xsl:attribute>
							
				<xsl:attribute name="width">28%</xsl:attribute>
				<xsl:attribute name="valign">top</xsl:attribute>
				
				<xsl:if test="@debug = '1'">				
					<xsl:element name="img">
						<xsl:attribute name="style">float: left;</xsl:attribute>
						<xsl:attribute name="src">/images/info.png</xsl:attribute>
						<xsl:attribute name="alt"><xsl:value-of select="@name" /></xsl:attribute>
						<xsl:attribute name="title"><xsl:value-of select="@name" /></xsl:attribute>
					</xsl:element>
				</xsl:if>

				<div style="float: left;"><xsl:value-of select="@title" />: </div>
				
				
				<xsl:choose>
					<xsl:when test="@helpedit != '0'">
						<xsl:if test="@help != '0'"><div style="float: right;"><a href="/apps/translations/help?id={@helpedit}"><img src="/images/icons2020/small_help.jpg" id="helpicon_{@help}" style="cursor: pointer;" onMouseOver="showHelp('{@help}');" onMouseOut="hideHelp('{@help}');" /></a></div></xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="@help != '0'"><div style="float: right;"><img src="/images/icons2020/small_help.jpg" id="helpicon_{@help}" style="cursor: pointer;" onMouseOver="showHelp('{@help}');" onMouseOut="hideHelp('{@help}');" /></div></xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:apply-templates select="help" />
				
				<xsl:if test="@type = 'filter'">
					<div style="float: left; clear: both; margin-top: 20px;">
					<input 
						type="submit"
						value=" Remove filter "
						onclick="buttonPress('removeFilter-{@name}');"
						style="width: 100px;"
					/>
					</div>
				</xsl:if>
			
			</xsl:element>
			
			<xsl:element name="td">
			
				<xsl:apply-templates select="textbox" />
				<xsl:apply-templates select="textboxlink" />
				<xsl:apply-templates select="dropdown" />
				<xsl:apply-templates select="calendar" />
				<xsl:apply-templates select="dropdownSubmit" />
				<xsl:apply-templates select="readonly" />
				<xsl:apply-templates select="submit" />
				<xsl:apply-templates select="combo" />
				<xsl:apply-templates select="comboCustomColumns" />
				<xsl:apply-templates select="textarea" />
				<xsl:apply-templates select="attachment" />
				<xsl:apply-templates select="attached" />
				<xsl:apply-templates select="checkbox" />
				<xsl:apply-templates select="radio" />
				<xsl:apply-templates select="autocomplete" />	
				<xsl:apply-templates select="measurement" />
				<xsl:apply-templates select="dropdownAlternative" />
				<xsl:apply-templates select="comboAlternative" />
				<xsl:apply-templates select="comboSelector" />				
				<xsl:apply-templates select="filterDateRange" />
				<xsl:apply-templates select="filterAmount" />
				<xsl:apply-templates select="moneyFilter" />	
				<xsl:apply-templates select="availableFiltersList" />

			</xsl:element>			
				
		</xsl:element>
	
	</xsl:template>
	
	
</xsl:stylesheet>