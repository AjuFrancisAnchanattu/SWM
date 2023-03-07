<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- zoverduen Site Home Page -->
	<xsl:template match="zoverduenHome">
	
		<style type="text/css">
			
			.selected {  }
			.notSelected { display: none; }
			a.anchor { text-decoration: none; color: 000; }
			.cornerText { color: #ffff33; float: right; font-weight: normal; padding-right: 6px; font-size: 8pt; text-decoration: none; margin-top: 1px; }
			.cornerLink:hover * { text-decoration: underline; }
			a:hover {cursor: pointer; }
		
		</style>
	
		<script type="text/javascript">
			
			function updateReloadDataDiv(id)	
			{	
				var allDivs = document.getElementById("masterTable").getElementsByTagName("div");
				
				for (var i in allDivs)
				{  
					if (allDivs[i] != null)
					{			
						if (allDivs[i].id != null)
						{
							var thisId = allDivs[i].id;
						
							var comparison = thisId.substring(0, 5);
														
							if (comparison == "table")
							{
								document.getElementById(thisId).className = 'notSelected';
							}
						}
					}
				}
				
				var tableId = "table" + id;
				var classSelected = 'box selected';

				document.getElementById(tableId).className = classSelected;
			}	
		
		</script>
	
<!--		<p style="padding: 10px; background: #fff; margin: 20px; border: 1px solid #ff2222; font-weight: bold;">We are experiencing a few problems with SAP reporting this morning.  Please check back soon.  Sorry for the inconvenience.</p>-->
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="background: url(/images/dotted_background.gif) repeat-y top right; padding: 10px;" width="50%">
				
					<!--<div align="center"><img src="/images/zoverduen.png" alt="zoverduen" /></div>-->
				
					<!--<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>-->
					
					<div class="title-box2">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:zoverduen_top_level_table} <xsl:value-of select="buToDisplay" /></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="snapin_content">
			            <div class="snapin_content_3">		            
							<!-- box begin -->
			                <div class="box">
			                   <div class="border-bot">
			                      <div class="left-top-corner">
			                         <div class="right-top-corner">
			                            <div class="right-bot-corner">
			                               <div class="left-bot-corner">
			                                  <div class="inner">
			                                     <div class="wrapper">
			                                     
			                                     	<h1>Open and Overdues as of: <xsl:value-of select="dateToDisplay" /></h1>
			                                     	
													<xsl:apply-templates select="zoverduenTopLevelTable" />									
			                                     </div>
			                                  </div>
			                               </div>
			                            </div>
			                         </div>
			                      </div>
			                   </div>
			                </div>
			                <!-- box end -->
				    	
				    	</div>
				    </div>
				</td>
	
				<td valign="top" style="padding: 10px;" width="50%">		
				
				<div class="title-boxgrey">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<h1 style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:currently_looking_at} <xsl:value-of select="plantToDisplay" /> <xsl:value-of select="buToDisplay" /></h1>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<br />
				
				<div class="title-boxblue">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:zoverduen_filters}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="snapin_content">
		            <div class="snapin_content_3">
		
						<xsl:apply-templates select="displayFilters" />
			    	
			    	</div>
			    </div>
			    
			    <br />
			    
			   <!--<div class="title-boxblue">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
										
											<div style="float: left;">
												<p style="margin: 2px 0 0 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:zoverduen_top_twenty}</p>
											</div>
											<div style="float: right; color: #fff; font-weight: bold;">
												<a href="#top" class="cornerLink" onclick="toggle_display('topTwenty'); return toggle_display('1')">
													<img src="../../images/arrow4.gif" alt="return to the top of the page" style="margin-top: 2px; float: right; vertical-align: middle;" />	
													<div class="cornerText">back to top</div>
												</a>
											</div>
											
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="snapin_content">
		            <div class="snapin_content_3">
		
						<div id="topTwenty" name="topTwenty">
		            		<xsl:apply-templates select="topTwenty" />
		            	</div>
		            	
		            	<script type="text/javascript">
		
							document.getElementById("topTwenty").style.display = "none";
						
						</script>
			    	
			    	</div>
			    </div>
			    
			    <br />-->
				<a name="summary" class="anchor"></a>
				<div class="title-box2">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											
											<div style="float: left;">
												<p style="margin: 2px 0 0 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:zoverduen_chart}</p>
											</div>
											<div style="float: right; color: #fff; font-weight: bold;">
												<a href="#top" class="cornerLink">
													<img src="../../images/arrow3.gif" alt="return to the top of the page" style="margin-top: 2px; float: right; vertical-align: middle;" />	
													<div class="cornerText">back to top</div>
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		      	
				<div class="snapin_content">
		            <div class="snapin_content_3">

						<xsl:apply-templates select="zoverduenChart" />
			    	
			    	</div>
			    </div>
			    
			    <xsl:if test="showByNumberOfDays != 'false'">
			    
				    <br />
					<a name="viewbynoofdays" class="anchor"></a>
				    <div class="title-box2">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												
												<div style="float: left;">
													<p style="margin:2px 0 0 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:open_overdue_orders_by_days_open}</p>
												</div>
												<div style="float: right; color: #fff; font-weight: bold;">
													<a href="#top" class="cornerLink">
														<img src="../../images/arrow3.gif" alt="return to the top of the page" style="margin-top: 2px; float: right; vertical-align: middle;" />	
														<div class="cornerText">back to top</div>
													</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
		      	
				
					<div class="snapin_content">
			            <div class="snapin_content_3">
	
							<table width="100%" cellpadding="0" cellspacing="0" id="masterTable">
								<tr>
									<td style="padding-bottom: 8px;"><xsl:apply-templates select="zoverduenByDaysChart" /></td>
									
								</tr>
								<tr>
									<td Style=""><a name="top10overdueorders" class="anchor"></a>
										<xsl:for-each select="zoverduenByDaysTables">
											
											<xsl:apply-templates select="zoverduenByDaysTable" />
										
										</xsl:for-each>
									</td>
								</tr>
							</table>
				    	
				    	</div>
				    </div>
				</xsl:if>
			    
			    <br />
                    
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
	<xsl:template match="topTwenty">
		<table width="100%" cellpadding="2" cellspacing="2">
			<tr style="background-color: #D5D5D5;">
				<td>Plant</td>
				<td>Sold to Party</td>
				<td>Days Overdue</td>
				<td>Open Amount</td>
				<td>Mat No</td>
			</tr>
			
			<xsl:for-each select="topTwentyItem">
				<tr>
					<td><xsl:value-of select="plant" /></td>
					<td><xsl:value-of select="stp" /></td>
					<td><xsl:value-of select="daysOverdue" /></td>
					<td><xsl:value-of select="openAmount" /></td>
					<td><xsl:value-of select="matNo" /></td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
	
	<xsl:template match="drilledDownTable">
		<table width="100%" cellpadding="2" cellspacing="2">
			<tr>
				<td>{TRANSLATE:material}</td>
				<td>{TRANSLATE:material_description}</td>
			</tr>
		
			<xsl:for-each select="drilledDownTableItem">
				<tr>
					<td><xsl:value-of select="material" /></td>
					<td><xsl:value-of select="materialDesc" /></td>
				</tr>
			</xsl:for-each>
			
		</table>
	</xsl:template>
	
	<xsl:template match="displayFilters">
	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
			<div style="float: left;"><a href="#summary"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin-right: 5px; vertical-align: middle;" />Summary Chart</a></div>
			<xsl:if test="../showByNumberOfDays != 'false'">
				<div style="float: left; padding-left: 20px;"><a href="#viewbynoofdays"><img src="../../images/icons2020/bargraph.png" alt="View Chart" style="margin-right: 5px; vertical-align: middle;" />View By Number of Days</a></div>
				<div style="float: left; padding-left: 20px;"><a href="#top10overdueorders"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin-right: 5px; vertical-align: middle;" />Top 10 Overdue Orders</a></div>
			</xsl:if>
		</div></div></div></div>
	
		<table width="100%" cellpadding="5" cellspacign="0">
			<tr>
				<td colspan="3">{TRANSLATE:select_chart_format}: 
				
					<xsl:for-each select="zoverduenRadioButton">
						<xsl:choose>
							<xsl:when test="radioChecked='1'">
								<input type="radio" name="{radioButtonName}" value="{radioButtonValue}" checked="1" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
							</xsl:when>
							<xsl:otherwise>
								<input type="radio" name="{radioButtonName}" value="{radioButtonValue}" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
					
					<!--<a href="/apps/dashboard/excelExports/zoverduenExport?"><img src="/images/excel.gif" /></a>-->
				
				</td>
			</tr>
			<tr>				
				<xsl:for-each select="zoverduenFilterDropdowns">
					<td>{TRANSLATE:<xsl:value-of select="translateName" />}: <xsl:apply-templates select="zoverduenFilterDropdown" /></td>	
				</xsl:for-each>
			</tr>
			<tr>				
				<td colspan="3">
					<img src="/images/arrow.gif" align="absmiddle" /> <a href="#" onclick="toggle_display('show_zoverduen_by_site'); return false;">{TRANSLATE:more_options}</a><br />
				</td>
			</tr>
			<tr>				
				<td colspan="3">
					<div id="show_zoverduen_by_site">
					<strong>{TRANSLATE:show_zoverduen_by_site}:</strong> 
					
						<xsl:choose>
							<xsl:when test="tickBoxSelectedOpenandOverdue='1'">
								<input type="checkbox" name="OpenandOverdue" id="OpenandOverdue" checked="1" />
							</xsl:when>
							<xsl:otherwise>
								<input type="checkbox" name="OpenandOverdue" id="OpenandOverdue" />		
							</xsl:otherwise>
						</xsl:choose>
						
						<br /><br />
					
					<table width="500px" cellpadding="2" cellspascing="2">
						<tr>
							<td><strong>{TRANSLATE:plant}</strong></td>
							<td><strong>{TRANSLATE:open}</strong><br /><a href="#" onclick="selectUnSelectOpenandOverdue(1,'Open'); return false;">{TRANSLATE:select_all}</a> | <a href="#" onclick="selectUnSelectOpenandOverdue(0,'Open'); return false;">{TRANSLATE:unselect_all}</a></td>
							<td><strong>{TRANSLATE:overdue}</strong><br /><a href="#" onclick="selectUnSelectOpenandOverdue(1,'Overdue'); return false;">{TRANSLATE:select_all}</a> | <a href="#" onclick="selectUnSelectOpenandOverdue(0,'Overdue'); return false;">{TRANSLATE:unselect_all}</a></td>
						</tr>
						<xsl:for-each select="plantsToShow">
							<tr>
								<td><xsl:value-of select="plantName" /></td>
								<td>
									<xsl:choose>
										<xsl:when test="tickBoxSelectedOpen='1'">
											<input type="checkbox" name="{plantName}Open" id="{plantName}Open" checked="1" onclick="document.getElementById('OpenandOverdue').checked = 1;" />
										</xsl:when>
										<xsl:otherwise>
											<input type="checkbox" name="{plantName}Open" id="{plantName}Open" onclick="document.getElementById('OpenandOverdue').checked = 1;" />
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="tickBoxSelectedOverdue='1'">
											<input type="checkbox" name="{plantName}Overdue" id="{plantName}Overdue" checked="1" onclick="document.getElementById('OpenandOverdue').checked = 1;" />
										</xsl:when>
										<xsl:otherwise>
											<input type="checkbox" name="{plantName}Overdue" id="{plantName}Overdue" onclick="document.getElementById('OpenandOverdue').checked = 1;" />
										</xsl:otherwise>
									</xsl:choose>
								</td>
							</tr>	
						</xsl:for-each>
					</table>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" value="Submit" /></td>
			</tr>
		</table>
		
		<script type="text/javascript">
			document.getElementById('show_zoverduen_by_site').style.display = "none";
			
			function selectUnSelectOpenandOverdue(value, measure)
			{
				document.getElementById('Ashton' + measure).checked = value;
				document.getElementById('Barcelona' + measure).checked = value;
				document.getElementById('CA-Satellites' + measure).checked = value;
				document.getElementById('Carlstadt' + measure).checked = value;
				document.getElementById('Dunstable' + measure).checked = value;
				document.getElementById('El-Paso' + measure).checked = value;
				document.getElementById('Ghislarengo' + measure).checked = value;
				document.getElementById('Inglewood' + measure).checked = value;
				document.getElementById('Laredo' + measure).checked = value;
				document.getElementById('Liverpool' + measure).checked = value;
				document.getElementById('Mannheim' + measure).checked = value;
				document.getElementById('Mielec' + measure).checked = value;
				document.getElementById('Renfrew' + measure).checked = value;
				document.getElementById('Rorschach' + measure).checked = value;
				document.getElementById('Valence' + measure).checked = value;
				document.getElementById('Windsor' + measure).checked = value;
				
				document.getElementById('OpenandOverdue').checked = 1;
			}
		</script>
	
	</xsl:template>
		
	<xsl:template match="zoverduenFilterDropdown">
		<select id="{dropdownName}" name="{dropdownName}">
			<xsl:for-each select="option">
				<xsl:choose>
					<xsl:when test="optionSelected='1'">
						<option selected="selected" value="{optionValue}"><xsl:value-of select="optionDisplayValue" /></option>
					</xsl:when>
					<xsl:otherwise>
						<option value="{optionValue}"><xsl:value-of select="optionDisplayValue" /></option>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</select>	
	</xsl:template>
	
	<xsl:template match="zoverduenTopLevelTable">
		
		<table width="100%" cellpadding="5" cellspacing="0">
			<tr style="background-color: #D5D5D5">
				<td><strong>{TRANSLATE:plant}</strong></td>
				<td><strong>Order<br />Line<br />Items</strong></td>
				<td><strong>Value<br />Open<br />Orders</strong></td>
				<td><strong>Overdue<br />Line<br />Items</strong></td>
				<td><strong>Value<br />Overdue<br />Orders</strong></td>
				<td><strong>%</strong>*</td>
			</tr>
			
			<xsl:for-each select="plantItem">
				<tr onmouseover="this.style.backgroundColor='#dfdfdf';" onmouseout="this.style.backgroundColor='#FFFFFF';">
					<xsl:choose>
						<xsl:when test="../../zoverduenBusinessUnit != ''">
							<td><a href="excelExports/zoverduenExport?zoverduenPlant={plantName}&amp;bu={../../zoverduenBusinessUnit}" target="_blank" border="0" alt="Export Overdue Orders"><img src="/images/excel2.gif" align="absmiddle" /></a><xsl:text> </xsl:text><a href="zoverduenDrillDown?chartName=zoverduen_summary&amp;zoverduenPlant={plantName}&amp;bu={../../zoverduenBusinessUnit}"><xsl:value-of select="plantName" /></a></td>
						</xsl:when>
						<xsl:otherwise>
							<td><a href="excelExports/zoverduenExport?zoverduenPlant={plantName}" target="_blank" border="0" alt="Export Overdue Orders"><img src="/images/excel2.gif" align="absmiddle" /></a><xsl:text> </xsl:text><a href="zoverduenDrillDown?chartName=zoverduen_summary&amp;zoverduenPlant={plantName}"><xsl:value-of select="plantName" /></a></td>
						</xsl:otherwise>
					</xsl:choose>
					
					
					<td><xsl:value-of select="totalOpenLineItems" /></td>
					<td><xsl:value-of select="openValue" /></td>
					<td><xsl:value-of select="totalOverdueLineItems" /></td>
					<td><xsl:value-of select="overdueValue" /></td>
					<td><xsl:value-of select="percentage" /></td>
				</tr>
			</xsl:for-each>
			
			<xsl:for-each select="groupPlantItem">
			
				<tr style="background-color: #DFDFDF">
					<td><a href="excelExports/zoverduenExport?zoverduenPlant=Group" target="_blank" border="0" alt="Export Overdue Orders"><img src="/images/excel2.gif" align="absmiddle" /></a><xsl:text> </xsl:text><strong><a href="zoverduenDrillDown?chartName=zoverduen_summary&amp;zoverduenPlant={plantName}"><xsl:value-of select="plantName" /></a></strong></td>
					<td><strong><xsl:value-of select="totalOpenLineItems" /></strong></td>
					<td><strong><xsl:value-of select="openValue" /></strong></td>
					<td><strong><xsl:value-of select="totalOverdueLineItems" /></strong></td>
					<td><strong><xsl:value-of select="overdueValue" /></strong></td>
					<td><strong><xsl:value-of select="percentage" /></strong></td>
				</tr>
			
			</xsl:for-each>
		</table>
		
		<p>* This is showing the percentage of overdue to open order values</p>
	
	</xsl:template>
	
	<xsl:template match="zoverduenChart">
	
		<xsl:choose>
			<xsl:when test="allowed='1'">
			
				<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
		
				<script type="text/javascript">
				
					// Get dimension of screen and change dimensions.
					var screenW = screen.width - 650;
					
			        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />MultiAxisLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
			        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
			        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
			        
			    </script>
			    
			    <div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
				<script type="text/javascript">
					var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter1", "../../lib/charts/FusionCharts/FCExporter.swf");
					<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
					<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
				</script>
			    
			    <script type="text/javascript">
				
					// Save chart to seperate location
					function ExportToServer()
					{
						if(confirm('Warning: This may take up to 60 seconds to complete.\nPlease wait for Save Complete before opening the PDF.'))
						{
							<xsl:value-of select="chartName" />myExportComponent.BeginExportAll();	
						}
					}
			        
			    </script>
			    
			</xsl:when>
			<xsl:otherwise>
				You do not have access to the <xsl:value-of select="chartName" /> report.
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
	<xsl:template match="zoverduenByDaysChart">
	
		<xsl:choose>
			<xsl:when test="allowed='1'">
			
				<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
		
				<script type="text/javascript">
				
					// Get dimension of screen and change dimensions.
					var screenW = screen.width - 650;
					
					
					
			        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
			        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
			        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
			        
			    </script>
			    
			    <div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
				<script type="text/javascript">
					var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter2", "../../lib/charts/FusionCharts/FCExporter.swf");
					<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
					<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
				</script>
			    
			    <script type="text/javascript">
				
					// Save chart to seperate location
					function ExportToServer()
					{
						if(confirm('Warning: This may take up to 60 seconds to complete.\nPlease wait for Save Complete before opening the PDF.'))
						{
							<xsl:value-of select="chartName" />myExportComponent.BeginExportAll();	
						}
					}
			        
			    </script>
			    
			</xsl:when>
			<xsl:otherwise>
				You do not have access to the <xsl:value-of select="chartName" /> report.
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>
	
	<xsl:template match="zoverduenByDaysTable">
		<!-- Dynamic Table -->
        
		<div class="box {@class}" id="table{@id}">
           <div class="border-bot">
              <div class="left-top-corner">
                 <div class="right-top-corner">
                    <div class="right-bot-corner">
                       <div class="left-bot-corner">
                          <div class="inner">
                             <div class="wrapper">
                             
                             	<h1>Top 10 Overdue Orders by Value (<xsl:value-of select="tableDays" />)</h1>
                             	
                             	<p>Click on a bar to see the top overdue items by number of days, or <a href="#" style="color: #ff2222;" onClick="updateReloadDataDiv('All'); return false;">click here to see the total items</a></p>
                             	
                             	<div id="reloadData">
                             	<table width="100%" cellpadding="4" cellspacing="2">
									<tr style="background-color: #D5D5D5;">
										<td><b>Plant</b></td>
										<td><b>Customer</b></td>
										<td><b>Days Overdue</b></td>
										<td><b>Mat Group</b></td>
										<td><b>BU</b></td>
										<td><b>Value<br />(GBP)</b></td>
									</tr>
									
									<xsl:for-each select="top15">
										<tr>
											<td><xsl:value-of select="plant" /></td>
											<td><xsl:value-of select="customer" /></td>
											<td><xsl:value-of select="daysOverdue" /></td>
											<td><xsl:value-of select="materialGroup" /></td>
											<td><xsl:value-of select="custGroup" /></td>
											<td><xsl:value-of select="value" /></td>
										</tr>
										
										<tr>
											<td colspan="7" style="border-bottom: 1px dashed #999999"><b>Material Description:</b><xsl:text> </xsl:text><xsl:value-of select="materialDesc" /></td>
										</tr>
									</xsl:for-each>
								</table>
								</div>
                             </div>
                          </div>
                       </div>
                    </div>
                 </div>
              </div>
           </div>
        </div>
        <!-- box end -->
		
	</xsl:template>
	
</xsl:stylesheet>