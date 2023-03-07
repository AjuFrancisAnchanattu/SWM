<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- DDDP Site Home Page -->
	<xsl:template match="dddpHome">
	
<!--		<p style="padding: 10px; background: #fff; margin: 20px; border: 1px solid #ff2222; font-weight: bold;">We are experiencing a few problems with SAP reporting this morning.  Please check back soon.  Sorry for the inconvenience.</p>-->
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="background: url(/images/dotted_background.gif) repeat-y top right; padding: 10px;" width="50%">
				
					<!--<div align="center"><img src="/images/DDDP.png" alt="DDDP" /></div>-->
				
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
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:dddp_top_level_table}: <xsl:value-of select="buToDisplay" /></p>
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
			                                     
			                                     	<h1><xsl:value-of select="monthToDisplay" /> (<xsl:value-of select="yearToDisplay" />)</h1>
			                                     	
													<xsl:apply-templates select="dddpTopLevelTable" />									
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
											<h1 style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:currently_looking_at} <xsl:value-of select="siteToDisplay" /> <xsl:value-of select="buToDisplay" /></h1>
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
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:dddp_filters}</p>
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
				
				<div class="title-box2">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:dddp_chart}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		      	
		      	<div class="snapin_content">
		            <div class="snapin_content_3">
		
						<xsl:apply-templates select="dddpChart" />
			    	
			    	</div>
			    </div>
			    
			    <br />
				
				<!--<div class="title-box1">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:drilled_down_table}</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
		      	
		      	<div class="snapin_content">
		            <div class="snapin_content_3">
		
						<xsl:apply-templates select="drilledDownTable" />
			    	
			    	</div>
			    </div>-->
                    
				</td>
			</tr>
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
	
		<table width="100%" cellpadding="5" cellspacign="0">
			<tr>
				<td colspan="3">{TRANSLATE:select_chart_format}: 
				
					<xsl:for-each select="dddpRadioButton">
						<xsl:choose>
							<xsl:when test="radioChecked='1'">
								<input type="radio" name="{radioButtonName}" value="{radioButtonValue}" checked="1" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
							</xsl:when>
							<xsl:otherwise>
								<input type="radio" name="{radioButtonName}" value="{radioButtonValue}" />{TRANSLATE:<xsl:value-of select="radioTranslate" />}
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
					
					<!--<a href="/apps/dashboard/excelExports/dddpExport?"><img src="/images/excel.gif" /></a>-->
				
				</td>
			</tr>
			<tr>				
				<xsl:for-each select="dddpFilterDropdowns">
					<td>{TRANSLATE:<xsl:value-of select="translateName" />}: <xsl:apply-templates select="dddpFilterDropdown" /></td>	
				</xsl:for-each>
			</tr>
			<tr>				
				<td colspan="3">
					<img src="/images/arrow.gif" align="absmiddle" /> <a href="#" onclick="toggle_display('show_clip_and_rlip_by_site'); return false;">{TRANSLATE:more_options}</a><br />
				</td>
			</tr>
			<tr>				
				<td colspan="3">
					<div id="show_clip_and_rlip_by_site">
					<strong>{TRANSLATE:show_clip_and_rlip_by_site}:</strong> 
					
						<xsl:choose>
							<xsl:when test="tickBoxSelectedCLIPandRLIP='1'">
								<input type="checkbox" name="CLIPandRLIP" id="CLIPandRLIP" checked="1" />
							</xsl:when>
							<xsl:otherwise>
								<input type="checkbox" name="CLIPandRLIP" id="CLIPandRLIP" />		
							</xsl:otherwise>
						</xsl:choose>
						
						<br /><br />
					
					<table width="500px" cellpadding="2" cellspascing="2">
						<tr>
							<td><strong>{TRANSLATE:plant}</strong></td>
							<td><strong>{TRANSLATE:clip}</strong><br /><a href="#" onclick="selectUnSelectCLIPRLIP(1,'CLIP'); return false;">{TRANSLATE:select_all}</a> | <a href="#" onclick="selectUnSelectCLIPRLIP(0,'CLIP'); return false;">{TRANSLATE:unselect_all}</a></td>
							<td><strong>{TRANSLATE:rlip}</strong><br /><a href="#" onclick="selectUnSelectCLIPRLIP(1,'RLIP'); return false;">{TRANSLATE:select_all}</a> | <a href="#" onclick="selectUnSelectCLIPRLIP(0,'RLIP'); return false;">{TRANSLATE:unselect_all}</a></td>
						</tr>
						<xsl:for-each select="plantsToShow">
							<tr>
								<td><xsl:value-of select="plantName" /></td>
								<td>
									<xsl:choose>
										<xsl:when test="tickBoxSelectedCLIP='1'">
											<input type="checkbox" name="{plantName}CLIP" id="{plantName}CLIP" checked="1" onclick="document.getElementById('CLIPandRLIP').checked = 1;" />
										</xsl:when>
										<xsl:otherwise>
											<input type="checkbox" name="{plantName}CLIP" id="{plantName}CLIP" onclick="document.getElementById('CLIPandRLIP').checked = 1;" />
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td>
									<xsl:choose>
										<xsl:when test="tickBoxSelectedRLIP='1'">
											<input type="checkbox" name="{plantName}RLIP" id="{plantName}RLIP" checked="1" onclick="document.getElementById('CLIPandRLIP').checked = 1;" />
										</xsl:when>
										<xsl:otherwise>
											<input type="checkbox" name="{plantName}RLIP" id="{plantName}RLIP" onclick="document.getElementById('CLIPandRLIP').checked = 1;" />
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
			document.getElementById('show_clip_and_rlip_by_site').style.display = "none";
			
			function selectUnSelectCLIPRLIP(value, measure)
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
				
				document.getElementById('CLIPandRLIP').checked = 1;
			}
		</script>
	
	</xsl:template>
		
	<xsl:template match="dddpFilterDropdown">
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
	
	<xsl:template match="dddpTopLevelTable">
		
		<table width="100%" cellpadding="5" cellspacing="0">
			<tr style="background-color: #D5D5D5">
				<td><strong>{TRANSLATE:plant}</strong></td>
				<xsl:if test="mtdTable='mtdTable'"><td><strong>Line<br />Items</strong></td></xsl:if>
				<td><strong>{TRANSLATE:clip}<br />(%)</strong></td>
				<td><strong>{TRANSLATE:clip_objective}<br />(<xsl:value-of select="clipTarget" />%)</strong></td>
				<td><strong>{TRANSLATE:rlip}<br />(%)</strong></td>
				<td><strong>{TRANSLATE:rlip_objective}<br />(<xsl:value-of select="rlipTarget" />%)</strong></td>
				<xsl:if test="mtdTable='mtdTable'"><td><strong>RLIP<br />Opp (%)</strong></td></xsl:if>
				<td><strong>RLIP<br />Default<br />Date (%)</strong></td>
			</tr>
			
			<xsl:for-each select="shippingPointItem">
			<tr onmouseover="this.style.backgroundColor='#dfdfdf';" onmouseout="this.style.backgroundColor='#FFFFFF';">
				<xsl:choose>
					<xsl:when test="../../businessUnit != ''">
						<td><a href="dddpDrillDown?chartFormat={../../chartFormat}&amp;site={shippingPointName}&amp;businessUnit={../../businessUnit}"><xsl:value-of select="shippingPointName" /></a></td>
					</xsl:when>
					<xsl:otherwise>
						<td><a href="dddpDrillDown?chartFormat={../../chartFormat}&amp;site={shippingPointName}"><xsl:value-of select="shippingPointName" /></a></td>
					</xsl:otherwise>
				</xsl:choose>
					
					
				<xsl:if test="../mtdTable='mtdTable'"><td><xsl:value-of select="totalLineItems" /></td></xsl:if>
				<td><xsl:value-of select="clipValue" /></td>
				
					<xsl:choose>
						<xsl:when test="formatClipPosition='1'">
							<td class="green"><img src="/images/traffic/green.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="red"><img src="/images/traffic/red.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:otherwise>
					</xsl:choose>
					
				<td><xsl:value-of select="rlipValue" /></td>
				
					<xsl:choose>
						<xsl:when test="formatRlipPosition='1'">
							<td class="green"><img src="/images/traffic/green.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="rlipFromObjective" />--></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="red"><img src="/images/traffic/red.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="rlipFromObjective" />--></td>
						</xsl:otherwise>
					</xsl:choose>
					
				<!--<td><xsl:value-of select="totalRLIPLinesMissed" /></td>-->
				<xsl:if test="../mtdTable='mtdTable'"><td><xsl:value-of select="totalRLIPOpp" /></td></xsl:if>
				
				<td><xsl:value-of select="rlipLinesPercentage" /></td>
			</tr>
			</xsl:for-each>
			
			<xsl:for-each select="groupShippingPointItem">
			
				<tr style="background-color: #DFDFDF">
					<td><a href="dddpDrillDown?chartName=dddp_summary&amp;site=GROUP"><strong>{TRANSLATE:group}</strong></a></td>
					<xsl:if test="../mtdTable='mtdTable'"><td><strong><xsl:value-of select="totalLineItems" /></strong></td></xsl:if>
					<td><strong><xsl:value-of select="groupCLIP" /></strong></td>
					<xsl:choose>
						<xsl:when test="formatClipPositionGroup='1'">
							<td class="green"><img src="/images/traffic/green.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="red"><img src="/images/traffic/red.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:otherwise>
					</xsl:choose>
					<td><strong><xsl:value-of select="groupRLIP" /></strong></td>
					<xsl:choose>
						<xsl:when test="formatRlipPositionGroup='1'">
							<td class="green"><img src="/images/traffic/green.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:when>
						<xsl:otherwise>
							<td class="red"><img src="/images/traffic/red.gif" align="absmiddle" style="padding-right: 5px;" /><!--<xsl:value-of select="clipFromObjective" />--></td>
						</xsl:otherwise>
					</xsl:choose>
					
					<!--<td><strong><xsl:value-of select="totalRLIPLinesMissed" /></strong></td>-->
					<xsl:if test="../mtdTable='mtdTable'"><td><strong><xsl:value-of select="totalRLIPOpp" /></strong></td></xsl:if>
					
					<td><strong><xsl:value-of select="rlipLinesPercentage" /></strong></td>
				</tr>
			
			</xsl:for-each>
		</table>
	
	</xsl:template>
	
	<xsl:template match="dddpChart">
	
		<xsl:choose>
			<xsl:when test="allowed='1'">
				
				<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
		
				<script type="text/javascript">
				
					// Get dimension of screen and change dimensions.
					var screenW = screen.width - 650;
					
			        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/MSLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
</xsl:stylesheet>