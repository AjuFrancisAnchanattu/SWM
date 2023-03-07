<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- Health and Safety Home Page -->
	<xsl:template match="healthAndSafetyHome">
	
		<script type="text/javascript">
			<![CDATA[
				
				function changeExportType(exportType)
				{	
					if (exportType == 'client')
					{
						window.location='healthandsafetySiteLevel?exportType=client';
					}
					else
					{
						window.location='healthandsafetySiteLevel?exportType=server';
					}
				}
				
			]]>
		</script>
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
				
					<!--<div align="center"><img src="/images/healthandsafety.png" alt="Health and Safety" /></div>-->
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		

				<xsl:choose>
				<!-- If the current user has Health and Safety Privalges allow, otherwise show Access Denied-->
				<xsl:when test="allowed='1'">
				
					<h1><img src="/images/dashboards/users_mixed_gender.png" align="left" style="padding-right: 5px; margin: -14px 0 0 0" /><xsl:value-of select="thisSite" /> | <xsl:value-of select="thisYear" /></h1>
					<h1 style="visibility: hidden; margin: 0; padding: 0; height: 6px">.</h1>
		           	<div class="title-box1">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety} (<xsl:value-of select="thisSite" />)</p>
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
		                        		<p style="margin: 0 10px; float: left;">|</p><a href="javascript:GeneratePDF();"><img src="../../images/famIcons/page.png" alt="" style="float: left; margin-right: 4px;" />{TRANSLATE:print_pdf}</a>
		                        	</xsl:if>
		                        </div>
                  			</div>
                 
                    </div>
                    
                    <br />
                    
                   	<!-- Template for All Site Trend Charts-->
                    <xsl:apply-templates select="healthAndSafetySiteTrendCharts" />
                    
                    <br />
                    
                    <!-- Template for Site Trend Charts Comments -->
                    <xsl:apply-templates select="healthAndSafetyComments" />
                    
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
	
	<!-- Health and Safety Add Page -->
	<xsl:template match="healthAndSafetyAdd">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					
					<!--<div align="center"><img src="/images/healthandsafety.png" alt="Health and Safety" /></div>-->
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
<!--					<h1><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" /><xsl:value-of select="thisSite" /> | <xsl:value-of select="monthToBeAdded" /> | <xsl:value-of select="thisYear" /></h1>-->
					<h1><img src="/images/dashboards/users_mixed_gender.png" align="left" style="padding-right: 5px; margin: -14px 0 0 0" /><xsl:value-of select="thisSite" /> | <xsl:value-of select="thisYear" /></h1>
					<h1 style="visibility: hidden; margin: 0; padding: 0; height: 6px">.</h1>
					<!--<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>{TRANSLATE:add_health_and_safety_entry}</p>
					</div></div></div></div>
					-->
					
					<div class="title-box1">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:add_health_and_safety_entry}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>	
									
						<xsl:apply-templates select="form" />
					
				</td>
			</tr>
		</table>		
		
	</xsl:template>
	
	<!-- Health and Safety Add Comments Page -->
	<xsl:template match="healthAndSafetyAddComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					
					<!--<div align="center"><img src="/images/healthandsafety.png" alt="Health and Safety" /></div>	-->
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">	
				
					<h1><img src="/images/dashboards/users_mixed_gender.png" align="left" style="padding-right: 5px; margin: -14px 0 0 0" />GROUP | <xsl:value-of select="monthToBeAdded" /> | <xsl:value-of select="thisYear" /></h1>
					<h1 style="visibility: hidden; margin: 0; padding: 0; height: 6px">.</h1>
										
					
					<div class="title-box1">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety_report}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>	
					
						<xsl:apply-templates select="form" />
					
				</td>
			</tr>
		</table>		
		
	</xsl:template>
	
	<xsl:template match="healthAndSafetyComments">
		<p style="background: #FFFFFF; font-weight: bold; padding: 5px;"><strong>{TRANSLATE:comments}: </strong></p>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyLTASiteTrendChart">
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
									window.open("pdf/healthandsafety/generateHASPDF?haSReportType=site&amp;siteType=<xsl:value-of select="../../thisSite" />");
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
								if(confirm('Warning: This may take up to 60 seconds to complete.\nPlease wait for the PDF to open.'))
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
	
	<xsl:template match="healthAndSafetyAccSiteTrendChart">
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
	
	<xsl:template match="healthAndSafetyLTDSiteTrendChart">
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
	
	<xsl:template match="healthAndSafetyReportableSiteTrendChart">
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
	
	<xsl:template match="healthAndSafetySafetyOppSiteTrendChart">
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
	
	<xsl:template match="healthAndSafetyDACRSiteTrendChart">
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
	
	<xsl:template match="healthAndSafetyComments">

		<div class="title-box1">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety_comments}</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
      	
      	<div class="snapin_content">
            <div class="snapin_content_3">
            
            <table width="100%" cellpadding="0" cellspacing="0" style="background: #FFFFFF;">
            <tr>
            <td style="text-align: center;">
			<table width="98%" cellpadding="2" cellspacing="2" style="background: #FFFFFF; margin: 0 1%; text-align: left;">
			
			<xsl:for-each select="healthAndSafetyComment">
				<tr>
					<td style="border-bottom: 1px solid #999;"><div style="visibility: hidden;">.</div></td>
				</tr>	
				<tr>
					<td style="padding: 5px; border-bottom: 1px dotted #999;">
						<img src="/images/famIcons/comment_reverse.png" align="left" style="padding-right: 8px; margin-top: 1px;" /><strong style="color: #507726;"><xsl:value-of select="initiator" /></strong> - <xsl:value-of select="dateAdded" /> - <span style="font-style: italic;">{TRANSLATE:month}: <xsl:value-of select="monthToBeAdded" /> - {TRANSLATE:year}: <xsl:value-of select="yearToBeAdded" /></span>
					</td>
				</tr>
				<tr>
					<td style="padding: 5px;"><xsl:apply-templates select="comment" /></td>
				</tr>
				
			</xsl:for-each>
			<tr>
				<td><br /></td>
			</tr>
			</table>
			</td>
			</tr>
			</table>
			</div>
			
		</div>
			
		
	</xsl:template>
	
	<xsl:template match="comment">
		<xsl:apply-templates select="para" />
	</xsl:template>
	
	
	<xsl:template match="para">
		<p style="margin: 10px 23px; line-height: 15px;"><xsl:value-of select="text()" /><br /></p>
	</xsl:template>
	
	
	<xsl:template match="healthAndSafetySiteTrendCharts">
			
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
	
				<xsl:apply-templates select="healthAndSafetyLTASiteTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyAccSiteTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyLTDSiteTrendChart" /><br />                        	
		    	<xsl:apply-templates select="healthAndSafetyReportableSiteTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetySafetyOppSiteTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyDACRSiteTrendChart" />
		    	
		    	</div>
		    </div>
	</xsl:template>
	
</xsl:stylesheet>