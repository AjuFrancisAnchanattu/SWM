<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- Health and Safety Region Home Page -->
	<xsl:template match="healthAndSafetyRegionLevelHome">
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
				
					<h1><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" /><xsl:value-of select="thisUserRegion" /> | <xsl:value-of select="thisYear" /></h1>
				
					<div class="snapin_top">
    	                <div class="snapin_top_3">
                    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety} (<xsl:value-of select="thisUserRegion" />)</p>
                    	</div>
                  	</div>
                  	
                  	<div class="snapin_content">
                        <div class="snapin_content_3">
                        	
                        	<p style="background: #FFFFFF; padding: 10px;"><xsl:if test="exportType='server'"><strong>{TRANSLATE:step_one} </strong> <a href="javascript:ExportToServer();">{TRANSLATE:export_all_charts_to_server}</a><!-- | <strong>{TRANSLATE:step_two} </strong> <a href="healthandsafetyAddComments?region={thisUserRegion}">{TRANSLATE:print_pdf}</a>--> | <a href="healthandsafetyRegionLevel?region={thisUserRegion}&amp;exporttype=client">{TRANSLATE:client_export}</a> </xsl:if> <xsl:if test="exportType='client'"> | <a href="healthandsafetyRegionLevel?site={thisUserRegion}">{TRANSLATE:report_export}</a></xsl:if></p>
                        	
                        </div>
                    </div>
                    
                    <br />
                    
                   	<!-- Template for All Site Trend Charts-->
                    <xsl:apply-templates select="healthAndSafetyRegionTrendCharts" />
                    
                    <br />
                    
                    <!-- Template for All Site Table -->
                    <xsl:apply-templates select="healthAndSafetyRegionTableHeader" />
                    
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
	
	<xsl:template match="healthAndSafetyLTARegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyAccRegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
	<xsl:template match="healthAndSafetyLTDRegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
	<xsl:template match="healthAndSafetyReportableRegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
	<xsl:template match="healthAndSafetySafetyOppRegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
	<xsl:template match="healthAndSafetyDACRRegionTrendChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/Column2D.swf", "<xsl:value-of select="chartName" />", 1280, "<xsl:value-of select="chartHeight" />", "0", "1");
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
	
	<xsl:template match="healthAndSafetyRegionTrendCharts">
			
		<div class="snapin_top">
	        <div class="snapin_top_3">
	    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety_charts} | <a href="#top" style="color: #FFFFFF;">{TRANSLATE:back_to_top}</a><a name="viewCharts" style="color: #4d4545;">-</a></p>
	    	</div>
	  	</div>
	      	
	      	<div class="snapin_content">
	            <div class="snapin_content_3">
	
				<xsl:apply-templates select="healthAndSafetyLTARegionTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyAccRegionTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyLTDRegionTrendChart" /><br />                        	
		    	<xsl:apply-templates select="healthAndSafetyReportableRegionTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetySafetyOppRegionTrendChart" /><br />
		    	<xsl:apply-templates select="healthAndSafetyDACRRegionTrendChart" />
		    	
		    	</div>
		    </div>
	</xsl:template>
	
	<xsl:template match="healthAndSafetyRegionTableHeader">
	<div class="snapin_top">
	        <div class="snapin_top_3">
	    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:health_and_safety} (<xsl:value-of select="../thisUserRegion" />) | <a href="#top" style="color: #FFFFFF;">{TRANSLATE:back_to_top}</a></p>
	    	</div>
	  	</div>
	      	
	      	<div class="snapin_content">
	            <div class="snapin_content_3">
	
				<xsl:apply-templates select="healthAndSafetyRegionTable" />
		    	
		    	</div>
		    </div>
    </xsl:template>
	
	<xsl:template match="healthAndSafetyRegionTable">
	
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
    		
    		<tr style="background: #FFFFFF; font-weight: bold;">
    			<td><a href="#dacrChart">DACR</a></td>
    			
    			<xsl:for-each select="monthData">
    				
    				<td><xsl:value-of select="dacr" /></td>
    			
    			</xsl:for-each>
    			
    		</tr>
    		
    		
    	</table>
	</xsl:template>
	
</xsl:stylesheet>