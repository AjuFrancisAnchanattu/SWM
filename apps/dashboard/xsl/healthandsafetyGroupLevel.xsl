<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- Health and Safety Group Home Page -->
	<xsl:template match="healthAndSafetyGroupLevelHome">
	
		<script type="text/javascript">
			<![CDATA[
				
				function changeExportType(exportType)
				{	
					if (exportType == 'client')
					{
						window.location='healthandsafetyGroupLevel?exportType=client';
					}
					else
					{
						window.location='healthandsafetyGroupLevel?exportType=server';
					}
				}
				
			]]>
		</script>	
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					
					<!--<div align="center"><img src="/images/healthandsafety.png" alt="Health and Safety" /></div>	-->
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		

				<xsl:choose>
				<!-- If the current user has Health and Safety Privalges allow, otherwise show Access Denied-->
				<xsl:when test="allowed='1'">
				
<!--					<h1><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" /><xsl:value-of select="thisUserGroup" /> | <xsl:value-of select="thisYear" /></h1>-->
				<h1><img src="/images/dashboards/users_mixed_gender.png" align="left" style="padding-right: 5px; margin: -14px 0 0 0" /><xsl:value-of select="thisUserGroup" /> | <xsl:value-of select="thisYear" /></h1>
					<h1 style="visibility: hidden; margin: 0; padding: 0; height: 6px">.</h1>
              		<div class="title-box1">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety} (<xsl:value-of select="thisUserGroup" />)</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>	

                    <div class="snapin_content">
                  		
                        	<div class="snapin_content_3">
                        		<div style="background: #fff; padding: 10px; height: 16px;">
		                        	<p style="float:left;"><img src="../../images/famIcons/application_go.png" alt="" style="float: left; margin-right: 4px;" />Export Type: </p>
		                        	
		                        	<xsl:choose>
		                        		<xsl:when test="exportType = 'server'">
		                        			<label style="display: block; float: left; margin: 0 5px 0 5px;"><input id="server" name="exportTypeServer" value="server" checked="yes" type="radio" onclick="changeExportType('server')" style=" float: left; margin: -3px 1px 0 0;" />Report</label>
		                        		</xsl:when>
		                        		<xsl:otherwise>
		                        			<label style="display: block; float: left; margin: 0 5px 0 5px;"><input id="server" name="exportTypeServer" value="server" type="radio" onclick="changeExportType('server')" style=" float: left; margin: -3px 1px 0 0;" />Report</label>
		                        		</xsl:otherwise>
		                        	</xsl:choose>
		                        	
		                        	<xsl:choose>
		                        		<xsl:when test="exportType = 'client'">
		                        			<label style="display: block; float: left; margin: 0 5px 0 0px;"><input id="client" name="exportTypeClient" value="client" checked="yes" type="radio" onclick="changeExportType('client')" style=" float: left; margin: -3px 1px 0 0;" />Individual Charts</label>
		                        		</xsl:when>
		                        		<xsl:otherwise>
		                        			<label style="display: block; float: left; margin: 0 5px 0 0px;"><input id="client" name="exportTypeClient" value="client" type="radio" onclick="changeExportType('client')" style=" float: left; margin: -3px 1px 0 0;" />Individual Charts</label>
		                        		</xsl:otherwise>
		                        	</xsl:choose>
		                        	
		                        	<xsl:if test="exportType='server'">
		                        		<p style="margin: 0 10px; float: left;">|</p>
		                        		<p style="margin: 0 5px; float: left;">Month:</p>
		                        		<select id="month" name="month" style="font-size: 9pt; margin-right: 20px; float: left; margin-top: -3px;" >
											
											<xsl:for-each select="monthOption">
												
												<option value="{@monthNum}"><xsl:value-of select="." /></option>
												
											</xsl:for-each>
											
										</select>
										
		                        		<a href="javascript:GeneratePDF();"><img src="../../images/famIcons/page.png" alt="" style="float: left; margin-right: 4px;" />Generate PDF (For Selected Month)</a>
		                        	</xsl:if>
		                        </div>
                  			</div>
                 
                    </div>
                    
                    <br />
                    
                   	<!-- Template for All Site Trend Charts-->
                    <xsl:apply-templates select="healthAndSafetyGroupTrendCharts" />
                    
                    <br />
                    
                    <!-- Template for All Site Table -->
                    <xsl:apply-templates select="healthAndSafetyGroupTableHeader" />
                    
                    </xsl:when>
                    
                    <xsl:otherwise>				
                    	<div class="red_notification">
							<h1><strong>{TRANSLATE:access_denied}</strong></h1>
						</div>
                    </xsl:otherwise>
                    
                    </xsl:choose>
                    
				</td>
			</tr>
		</table>		
		
	</xsl:template>
	
	<xsl:template match="healthAndSafetyLTAGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">					
						<a name="ltaChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
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
						
							var scrollDown = 0;
					    	var chartsComplete = 0;
					    	var scrollAmount = 357;	// set to chartHeight (300) + padding
					    
					    	function FC_Exported(objRtn)
							{ 
								chartsComplete = chartsComplete + 1;
								
								// When all 5 charts have been exported show the Save Complete textbox.
								if(chartsComplete == 5)
								{
									// scroll back to the top of the page
									window.scrollTo(0,0);
								
									<!-- reset chartsComplete so the PDF can be generated again -->
									chartsComplete = 0;
									
									month = document.getElementById('month').value;
									
									window.open("pdf/healthandsafety/generateHASPDF?haSReportType=<xsl:value-of select="../../thisUserGroup" />&amp;monthToBeAdded=" + month);
								}
								else if (scrollDown == 1)
								{
									// Scroll down until all 5 charts have exported
									window.scrollTo(0,scrollAmount);
									
									scrollAmount = scrollAmount + 357;
								}
								else
								{
									//
								}
							}	    
					    
							// Save chart to seperate location
							function GeneratePDF()
							{
								if (confirm('Warning: This may take up to 60 seconds to complete.\nPlease wait for the PDF to open.'))
								{
									scrollDown = 1;
								
									<xsl:value-of select="chartName" />myExportComponent.BeginExportAll();
								}
							}
					        
					    </script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyAccGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="#accidentsChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
						<div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
						<script type="text/javascript">
							var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter2", "../../lib/charts/FusionCharts/FCExporter.swf");
							<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
							<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
						</script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyLTDGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="#ltdChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
						<div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
						<script type="text/javascript">
							var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter3", "../../lib/charts/FusionCharts/FCExporter.swf");
							<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
							<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
						</script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyReportableGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="#reportableChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
						<div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
						<script type="text/javascript">
							var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter4", "../../lib/charts/FusionCharts/FCExporter.swf");
							<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
							<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
						</script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetySafetyOppGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="#safetyOppChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
						<div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
						<script type="text/javascript">
							var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter5", "../../lib/charts/FusionCharts/FCExporter.swf");
							<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
							<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
						</script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyDACRGroupTrendChart">
		<table cellspacing="0" width="260">
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="#dacrChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>						
						
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
					    </script>
					    
						<div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
						<script type="text/javascript">
							var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter6", "../../lib/charts/FusionCharts/FCExporter.swf");
							<xsl:value-of select="chartName" />myExportComponent.debugMode = true;
							<xsl:value-of select="chartName" />myExportComponent.Render("chartdiv<xsl:value-of select="chartName" />EXP");
						</script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyGroupTrendCharts">
				  	
	  	<div class="title-box1">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety_charts}</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
	      	
	      	<div class="snapin_content">
	            <div class="snapin_content_3">
	
				<xsl:apply-templates select="healthAndSafetyLTAGroupTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyAccGroupTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyLTDGroupTrendChart" /><br />                        	
		    	<xsl:apply-templates select="healthAndSafetyReportableGroupTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetySafetyOppGroupTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyDACRGroupTrendChart" />
		    	
		    	</div>
		    </div>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyGroupTableHeader">
	<div class="snapin_top">
	        <div class="snapin_top_3">
	    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety} (<xsl:value-of select="../thisUserGroup" />)</p>
	    	</div>
	  	</div>
	      	
	      	<div class="snapin_content">
	            <div class="snapin_content_3">
	
				<xsl:apply-templates select="healthAndSafetyGroupTable" />
		    	
		    	</div>
		    </div>
    </xsl:template>
	
	<xsl:template match="healthAndSafetyGroupTable">
	
		<table width="100%" cellpadding="1" cellspacing="1" class="data_table" style="border: 1px solid #CCCCCC;">
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td>Type</td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="monthName" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#ltaChart">LTA</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="lta" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#accidentsChart">LTA > 4 Days</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="acc4Days" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#ltdChart">LTD</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="ltd" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#reportableChart">Reportable</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="reportable" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#safetyOppChart">Safety Opp</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="safetyOpp" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		
    	</table>
	</xsl:template>
	
</xsl:stylesheet>