<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- inventory Site Home Page -->
	<xsl:template match="inventoryHome">
	
		<script type="text/javascript">
			var currency = '<xsl:value-of select="currency" />';
			<![CDATA[
			
			
			function collapse_all() 
			{  		
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
				
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{				
						if (allTrTags[i].className != '')
						{																		
							if (allTrTags[i].className == 'bu')
							{	
								var thisId = allTrTags[i].id;
								var img = document.getElementById(thisId).getElementsByTagName("img");
								
								img[0].src="../../images/dTree/plus.png";
								img[0].className = "plus";								
							}
							else
							{					
								if (allTrTags[i].id != undefined)
								{
									if (allTrTags[i].id != null)
									{
										if (allTrTags[i].id != '')
										{
											var thisId = allTrTags[i].id;
											
											if (allTrTags[i].className == 'region')
											{
												var img = document.getElementById(thisId).getElementsByTagName("img");
									
												img[0].src="../../images/dTree/plus.png";
												img[0].className = "plus";
											}
											
											document.getElementById(thisId).style.display = 'none';
										}
									}
								}
							}
						}					
					}
				}
			}
			
			
			function expand_all() 
			{  		
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
				
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].className != '')
						{			
							if (allTrTags[i].className == 'bu')
							{
								var thisId = allTrTags[i].id;
								var img = document.getElementById(thisId).getElementsByTagName("img");
								
								img[0].src="../../images/dTree/minus.png";
								img[0].className = "minus";
							}
							else
							{								
								if (allTrTags[i].id != null)
								{			
									if (allTrTags[i].id != '')
									{							
										var thisId = allTrTags[i].id;
										
										if (allTrTags[i].className == 'region')
										{
											var img = document.getElementById(thisId).getElementsByTagName("img");
								
											img[0].src="../../images/dTree/minus.png";
											img[0].className = "minus";
										}
										
										document.getElementById(thisId).style.display = '';
									}
								}
							}
						}
					}
				}
			}							
			
					
			function toggle_row(passedId, $rowType) 
			{  			
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
				
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].id != null)
						{			
							var thisId = allTrTags[i].id;
						
							var comparisonId = thisId.substring(0, (passedId.length))
									
							if (comparisonId == passedId)
							{															
								if (thisId == passedId) // change expand/collapse symbol if parent
								{
									var img = document.getElementById(thisId).getElementsByTagName("img");

									if (img[0].className == "minus")
									{
										img[0].src="../../images/dTree/plus.png";
										img[0].className = "plus";
									}
									else
									{
										img[0].src="../../images/dTree/minus.png";
										img[0].className = "minus";
									}
								}
								else // show/hide children
								{															
									if ($rowType == 'region')
									{
										if (document.getElementById(thisId).style.display == 'none')
										{
											document.getElementById(thisId).style.display = '';
										}
										else
										{
											document.getElementById(thisId).style.display = 'none';
										}		
									}
									else
									{
										if (document.getElementById(thisId).style.display == '')
										{										
											document.getElementById(thisId).style.display = 'none';
										}
										else
										{
											if (document.getElementById(thisId).className == 'region')
											{
												var img = document.getElementById(thisId).getElementsByTagName("img");
												img[0].src="../../images/dTree/plus.png";
												img[0].className = "plus";
											
												document.getElementById(thisId).style.display = '';
											}
										}
									}
								}		
							}
						}
					}
				} 
			}
			]]>
			
			function excelLinkChange()
			{
				var date = document.getElementById('xlWeek').value;
				var bu = document.getElementById('xlBu').value;
				var plant = document.getElementById('xlPlant').value;
				
				<xsl:choose>
					<xsl:when test="xlFormat = 'bu'">
						<![CDATA[
						document.getElementById('excelLink').href = 'excelExports/inventoryExport?format=bu&date=' + date + "&bu=" + bu + "&plant=" + plant + "&currency=" + currency;
						]]>
					</xsl:when>
					<xsl:otherwise>
						<![CDATA[
						document.getElementById('excelLink').href = 'excelExports/inventoryExport?date=' + date + "&bu=" + bu + "&plant=" + plant + "&currency=" + currency;
						]]>
					</xsl:otherwise>
				</xsl:choose>
			}
			
			
		</script> 
	
<!--		<p style="padding: 10px; background: #fff; margin: 20px; border: 1px solid #ff2222; font-weight: bold;">We are experiencing a few problems with SAP reporting this morning.  Please check back soon.  Sorry for the inconvenience.</p>-->
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="background: url(/images/dotted_background.gif) repeat-y top right; padding: 10px;" width="50%">
				
					<div class="title-box2">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:inventory_top_level_table} <xsl:value-of select="displayTableFormat" /></p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="snapin_content">
						
						<div class="snapin_bevel_1" style="margin-top: 1px; padding: 0 8px">
							<div class="snapin_bevel_2">
								<div class="snapin_bevel_3">
									<div class="snapin_bevel_4">
							
										<xsl:apply-templates select="tableFilters" />
									
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
				                                     
				                                     	<h1 style="margin-top: 4px;">Gross Inventory as of: <xsl:value-of select="dateToDisplay" /></h1>
				                                     					                                     	
														<xsl:choose>
															<xsl:when test="tableFormat = 'bu'">
																<xsl:apply-templates select="inventoryBuTopLevelTable" />									
															</xsl:when>
															<xsl:when test="tableFormat = 'stockTurns'">
																<xsl:apply-templates select="stockTurnsTopLevelTable" />									
															</xsl:when>
															<xsl:otherwise>
																<xsl:apply-templates select="inventoryPlantTopLevelTable" />
															</xsl:otherwise>
														</xsl:choose>
														
				                                     </div>
				                                  </div>
				                               </div>
				                            </div>
				                         </div>
				                      </div>
				                   </div>
				                </div>
				                <!-- box end -->
				                
				                		      	          
								<!-- box begin -->
				                <div style="padding: 5px 9px; background: #fff; margin: 8px 0px; border: 1px solid #aaa;">
				               	<p style="margin: 0px 0 1px 4px;"><strong>Note</strong> - figures are calculated with standard costs</p>
								<p style="margin: 0px 0 1px 4px;"><strong>*</strong> - stock turns are operational stock turns</p>
				               	</div>
				                 					
				                
			                </div>
			                
			                 
				    	
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
											<h1 id="looking-at" style="margin: 0; font-weight: bold; font-size: 10pt; color: #FFFFFF;">{TRANSLATE:currently_looking_at} <xsl:value-of select="plantToDisplay" /> <xsl:value-of select="buToDisplay" /></h1>
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
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:currency_display}</p>
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
			    
				<div class="title-boxblue">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:export_data_to_excel}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="snapin_content">
		            <div class="snapin_content_3">
		
			    		<xsl:apply-templates select="xlFeature" />
			    	</div>
			    </div>
			    
			    <br />
			    
				<div class="title-box2">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:inventory_chart}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		      	
		      	<div class="snapin_content">
		            <div class="snapin_content_3">
		
						<xsl:apply-templates select="inventoryChart" />
			    	
			    	</div>
			    </div>
			    
			    <br />
				
				</td>
			</tr>
		</table>
		
		<script type="text/javascript">
			excelLinkChange();
		</script>
		
	</xsl:template>
	
	
	<xsl:template match="xlFeature">
	
		<div style="float: left; width: 100%; padding-bottom: 4px;">
		
			<xsl:for-each select="inventoryFilterDropdowns">
				<div style="float: left; margin: 4px 12px 0 0;"><span style="margin: 0 6px 0 0">{TRANSLATE:<xsl:value-of select="translateName" />}: </span><xsl:apply-templates select="excelFilterDropdown" /></div>
			</xsl:for-each>
			
			<a href="#" id="excelLink">
				<img src="../../../images/excel.gif" style="float: left; width: 22px; margin: 4px 0 0 0; height: 22px;" alt="" />
			</a>
			
		</div>
	
	</xsl:template>
	
	
	<xsl:template match="displayFilters">
	
		<div style="float: left; width: 240px; padding-bottom: 4px;">
		
			<xsl:for-each select="inventoryFilterDropdowns">
				<p style="float: left; margin: 8px 6px 0 8px;">{TRANSLATE:<xsl:value-of select="translateName" />}: </p><div style="margin-top: 4px; float: left;"><xsl:apply-templates select="inventoryFilterDropdown" /></div>
			</xsl:for-each>
				
		<input type="submit" value="Submit" style="float: left; width: 80px; margin: 3px 0 0 12px;" />
					
		</div>

	</xsl:template>
	
	
	<xsl:template match="tableFilters">
	
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td>
					<div>{TRANSLATE:select_table_format}:
				
						<xsl:for-each select="inventoryRadioButton">
							<xsl:choose>
								<xsl:when test="radioChecked='1'">
									<input type="radio" name="{radioButtonName}" onClick="location.href='inventoryDrillDown?tableFormat={radioButtonValue}'" value="{radioButtonValue}" checked="1" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
								</xsl:when>
								<xsl:otherwise>
									<input type="radio" name="{radioButtonName}" onClick="location.href='inventoryDrillDown?tableFormat={radioButtonValue}'" value="{radioButtonValue}" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					
					<!--<a href="/apps/dashboard/excelExports/inventoryExport?"><img src="/images/excel.gif" /></a>-->
					</div>
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="excelFilterDropdown">
		<select id="{dropdownName}" name="{dropdownName}" onchange="excelLinkChange(); return false;">
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
	
		
	<xsl:template match="inventoryFilterDropdown">
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
	
	<xsl:template match="stockTurnsTopLevelTable">
		
		<table width="100%" cellpadding="5" cellspacing="0">
			<tr style="background-color: #D5D5D5">
				<td><strong>{TRANSLATE:plant}</strong></td>
				<td><strong>RM Stock<br/>Turns*<br /></strong></td>
				<td><strong>SF Stock<br/>Turns*<br /></strong></td>
				<td><strong>FG Stock<br/>Turns*<br /></strong></td>
				<td><strong>Operational Stock<br />Turns</strong></td>
			</tr>
			
			<xsl:for-each select="plantItem">
				<tr style="background: #fff;" onmouseover="this.style.backgroundColor='#dfdfdf';" onmouseout="this.style.backgroundColor='#fff';">
					<td><a href="inventoryDrillDown?tableFormat=stock&amp;plant={plantName}"><xsl:value-of select="plantName" /></a></td>
					<td><xsl:value-of select="rmValue" /></td>
					<td><xsl:value-of select="sfValue" /></td>
					<td><xsl:value-of select="fgValue" /></td>
					<td><xsl:value-of select="overallValue" /></td>
				</tr>
			</xsl:for-each>
			
			<xsl:for-each select="groupPlantItem">
			
				<tr style="background-color: #DFDFDF">
					<td><strong><a href="inventoryDrillDown?tableFormat=stock&amp;plant={plantName}"><xsl:value-of select="plantName" /></a></strong></td>
					<td><strong><xsl:value-of select="rmValue" /></strong></td>
					<td><strong><xsl:value-of select="sfValue" /></strong></td>
					<td><strong><xsl:value-of select="fgValue" /></strong></td>
					<td><strong><xsl:value-of select="overallValue" /></strong></td>
				</tr>
			
			</xsl:for-each>
			
		</table>
		
	</xsl:template>
	
	<xsl:template match="inventoryPlantTopLevelTable">
		
		<table width="100%" cellpadding="5" cellspacing="0">
			<tr style="background-color: #D5D5D5">
				<td><strong>{TRANSLATE:plant}</strong></td>
				<td><strong>RM<br />Value<br />(<xsl:value-of select="currency" />)</strong></td>
				<td><strong>SF<br />Value<br />(<xsl:value-of select="currency" />)</strong></td>
				<td><strong>FG<br />Value<br />(<xsl:value-of select="currency" />)</strong></td>
				<td><strong>Total<br />Value<br />(<xsl:value-of select="currency" />)</strong></td>
				<td><strong>Operational<br />Stock<br />Turns</strong></td>
			</tr>
			
			<xsl:for-each select="plantItem">
				<tr style="background: #fff;" onmouseover="this.style.backgroundColor='#dfdfdf';" onmouseout="this.style.backgroundColor='#fff';">
					<td><a href="inventoryDrillDown?tableFormat=plant&amp;plant={plantName}"><xsl:value-of select="plantName" /></a></td>
					<td><xsl:value-of select="rmValue" /></td>
					<td><xsl:value-of select="sfValue" /></td>
					<td><xsl:value-of select="fgValue" /></td>
					<td><xsl:value-of select="overallValue" /></td>
					<td><xsl:value-of select="stockTurns" /></td>
				</tr>
			</xsl:for-each>
			
			<xsl:for-each select="groupPlantItem">
			
				<tr style="background-color: #DFDFDF">
					<td><strong><a href="inventoryDrillDown?tableFormat=plant&amp;plant={plantName}"><xsl:value-of select="plantName" /></a></strong></td>
					<td><strong><xsl:value-of select="rmValue" /></strong></td>
					<td><strong><xsl:value-of select="sfValue" /></strong></td>
					<td><strong><xsl:value-of select="fgValue" /></strong></td>
					<td><strong><xsl:value-of select="overallValue" /></strong></td>
					<td><strong><xsl:value-of select="stockTurns" /></strong></td>
				</tr>
			
			</xsl:for-each>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="inventoryBuTopLevelTable">
	

		<div style="float: left; margin: 0 10px 20px 5px; font-style: italic;">Expand/Collapse All</div>
		<a href="#" onclick="expand_all(); return false;" style="display: block; width: 20px; float: left;"><img src="../../images/dTree/plus.png" style="vertical-align: middle;" /></a>
		<a href="#" onclick="collapse_all(); return false;" style="display: block; width: 20px; float: left;"><img src="../../images/dTree/minus.png" style="vertical-align: middle;" /></a>

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
	
			<tr style="background-color: #D5D5D5;">
				<td style="padding: 10px 5px 10px 7px;"><strong>{TRANSLATE:businessUnit}</strong></td>
				<td><strong>FG Value (<xsl:value-of select="currency" />)</strong></td>
				<td><strong>Operational <br />Stock Turns</strong></td>
			</tr>
			
			<xsl:for-each select="buRecord">
				<tr style="background: #e0e0e0;" id="{bu}" class="bu">
					<td><a href="#" onclick="toggle_row('{bu}', 'bu'); return false;" style="display: block; width: 20px; float: left;"><img src="../../images/dTree/minus.png" style="vertical-align: middle;" class="minus"/></a><a href="inventoryDrillDown?tableFormat=bu&amp;bu={bu}" style="display: block; float: left;"><xsl:value-of select="bu" /></a></td>
					<td><xsl:value-of select="fgValue" /></td>
					<td><xsl:value-of select="stockTurns" /></td>
				</tr>
				
				<xsl:for-each select="regionRecord">
					<tr style="background: #f0f0f0;" id="{../bu}{region}"  class="region">
						<td style="padding-left: 30px;"><a href="#" onclick="toggle_row('{../bu}{region}', 'region'); return false;" style="display: block; width: 20px; float: left;"><img src="../../images/dTree/minus.png" class="minus" style="vertical-align: middle;"/></a><a href="inventoryDrillDown?tableFormat=bu&amp;bu={../bu}&amp;region={region}" style="display: block; float: left;"><xsl:value-of select="region" /></a></td>
						<td><xsl:value-of select="fgValue" /></td>
						<td><xsl:value-of select="stockTurns" /></td>
					</tr>
					
					<xsl:for-each select="plantRecord">
						<tr onmouseover="this.style.backgroundColor='#dfdfdf';" onmouseout="this.style.backgroundColor='#FFFFFF';" id="{../../bu}{../region}{plant}" class="plant">
							<td style="padding-left: 60px;"><a href="inventoryDrillDown?tableFormat=bu&amp;bu={../../bu}&amp;region={../region}&amp;plant={plant}"><xsl:value-of select="plant" /></a></td>
							<td><xsl:value-of select="fgValue" /></td>
							<td><xsl:value-of select="stockTurns" /></td>
						</tr>					
					</xsl:for-each>
					
				</xsl:for-each>
			</xsl:for-each>
			
			<xsl:for-each select="groupBuItem">
			
				<tr style="background-color: #D5D5D5;" class="bu" id="{plantName}">
					<td><a href="#" onclick="toggle_row('{plantName}', 'region'); return false;" style="display: block; width: 20px; float: left;"><img src="../../images/dTree/minus.png" class="minus" style="vertical-align: middle;"/></a><strong><a href="inventoryDrillDown?tableFormat=bu"><xsl:value-of select="plantName" /></a></strong></td>
					<td><strong><xsl:value-of select="fgValue" /></strong></td>
					<td><strong><xsl:value-of select="stockTurns" /></strong></td>
				</tr>
			
			</xsl:for-each>
			
			<xsl:for-each select="groupBuSubItem">
			
				<tr style="background-color: #D5D5D5;" id="Group{plantName}" class="plant">
					<td style="padding-left: 50px;"><strong><a href="inventoryDrillDown?tableFormat=bu&amp;region={plantName}"><xsl:value-of select="plantName" /></a></strong></td>
					<td><strong><xsl:value-of select="fgValue" /></strong></td>
					<td><strong><xsl:value-of select="stockTurns" /></strong></td>
				</tr>
			
			</xsl:for-each>
			
		</table>
		
		<script type="text/javascript">
			collapse_all();
		</script>
	
	</xsl:template>
	
	
	<xsl:template match="inventoryChart">
	
		<xsl:apply-templates select="chart" />
	
	</xsl:template>
			
	
	<xsl:template match="chart">
		
		<xsl:variable name="chart_name"> <xsl:value-of select="chartName"/> </xsl:variable>
		
		<xsl:choose>
			<xsl:when test="allowed='1'">
			
				<div id="chartDiv_{$chart_name}" align="center"><xsl:value-of select="$chart_name" /></div>
		
				<script type="text/javascript">
				
					// Get dimension of screen and change dimensions.
					var screenW = screen.width - 650;
					
			        var <xsl:value-of select="$chart_name" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" /><xsl:value-of select="chartType" />", "<xsl:value-of select="$chart_name" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
			        <xsl:value-of select="$chart_name" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
			        <xsl:value-of select="$chart_name" />.render("chartDiv_<xsl:value-of select="$chart_name" />");
			        
			    </script>
			    
			    <div style="border-bottom: 1px solid #9c9898; padding-top:5px;" align="center">Export Chart</div>
				
			    <div id="chartDiv_{$chart_name}_Export" align="center" style="padding-top:5px;">chartDiv_<xsl:value-of select="$chart_name" />_Export</div>
				
				<script type="text/javascript">
					var mainExportComponent = new FusionChartsExportObject("inventory_Exporter", "../../lib/charts/FusionCharts/FCExporter.swf");
					mainExportComponent.debugMode = true;
					mainExportComponent.componentAttributes.btndisabledtitle = 'Right-click on the chart to begin saving'; 
					mainExportComponent.componentAttributes.width = '250';


					mainExportComponent.Render("chartDiv_<xsl:value-of select="$chart_name" />_Export");
					
					
					
				</script>
			    
			</xsl:when>
			<xsl:otherwise>
				You do not have access to the <xsl:value-of select="$chart_name" /> report.
			</xsl:otherwise>
		</xsl:choose>
	
	</xsl:template>

	
</xsl:stylesheet>