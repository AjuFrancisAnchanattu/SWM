<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="sao">
	
		<style type="text/css">
			.totalsBox { float: left; text-align: center; font-size: 7pt; padding: 3px 6px; line-height: 1.1em; border: 1px solid #aaa; margin: 0 0 0 5px; background-color: #efefef; }
			.totalsBox span { line-height: 1.4em; }
			.totalsBox h4 { padding: 0; margin: -3px 0 -1px 0; font-size: 8pt; }
			.left { text-align: left; }
			.middleCol { padding: 0 12px; }
			.underline { text-decoration: underline; }
			.italic { font-style: italic; }
			.title { background-color: #fff; padding: 1px 3px; border: 1px solid #aaa; }
		</style>
		
		<table cellspacing="0" width="260">
			
			<tr>
				<td style="background: #FFFFFF; border: 1px solid #9c9898; padding: 4px 0;">
				
					<xsl:choose>	
						<xsl:when test="buTitle != ''">
							<div style="padding-left: 5px; padding-bottom: 5px;"><a href="#" onclick="toggle_display('totalsBoxes'); toggle_display('buTitle'); return false;">Toggle Summary <span id="buTitle">- <xsl:value-of select="buTitle" /></span></a></div>
						</xsl:when>
						<xsl:otherwise>
							<div style="padding-left: 5px; padding-bottom: 5px;"><a href="#" onclick="toggle_display('totalsBoxes'); toggle_display('buTitle'); return false;">Toggle Summary</a></div>
						</xsl:otherwise>
				</xsl:choose>
					
					
				
					<div id="totalsBoxes">
					
					<div class="totalsBox">
						<table>
							<tr>
								<td class="left"><strong class="title"><xsl:value-of select="pastDate" /></strong></td><td class="middleCol italic">Sales</td><td class="italic">Orders</td>
							</tr>
							<xsl:if test="yesterdaySalesAll != ''">
								<tr>
									<td class="left">Group</td><td class="middleCol"><xsl:value-of select="yesterdaySalesAll" /></td><td><xsl:value-of select="yesterdayOrdersAll" /></td>
								</tr>
							</xsl:if>
							<xsl:if test="yesterdaySalesEurope != ''">
								<tr>
									<td class="left">Europe</td><td class="middleCol"><xsl:value-of select="yesterdaySalesEurope" /></td><td><xsl:value-of select="yesterdayOrdersEurope" /></td>
								</tr>
							</xsl:if>
							<xsl:if test="yesterdaySalesNA != ''">
								<tr>
									<td class="left">NA</td><td class="middleCol"><xsl:value-of select="yesterdaySalesNA" /></td><td><xsl:value-of select="yesterdayOrdersNA" /></td>
								</tr>
							</xsl:if>
						</table>
					</div>	
					
					
					<div class="totalsBox">
						<table>
							<tr>
								<td class="left"><strong class="title">MTD</strong></td><td class="middleCol italic">Sales</td><td class="italic">Orders</td>
							</tr>
							<xsl:if test="mtdSalesAll != ''">
								<tr>
									<td class="left">Group</td><td class="middleCol"><xsl:value-of select="mtdSalesAll" /></td><td><xsl:value-of select="mtdOrdersAll" /></td>
								</tr>
							</xsl:if>
							<xsl:if test="mtdSalesEurope != ''">
								<tr>
									<td class="left">Europe</td><td class="middleCol"><xsl:value-of select="mtdSalesEurope" /></td><td><xsl:value-of select="mtdOrdersEurope" /></td>
								</tr>
							</xsl:if>
							<xsl:if test="mtdSalesNA != ''">
								<tr>
									<td class="left">NA</td><td class="middleCol"><xsl:value-of select="mtdSalesNA" /></td><td><xsl:value-of select="mtdOrdersNA" /></td>
								</tr>
							</xsl:if>
						</table>
					</div>		
					
					</div>		
				</td>
			</tr>
		
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="allowed='1'">
					
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 3 - 64;
							
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="graphChartLocation" />MSLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");

					    </script>
					    
					   <!-- <script type="text/javascript">
							    
					        var <xsl:value-of select="bulbName" /> = new FusionCharts("<xsl:value-of select="bulbChartLocation" />Bulb.swf", "<xsl:value-of select="bulbName" />", "50", "50");
					        <xsl:value-of select="bulbName" />.setDataXML("<xsl:value-of select="bulbChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="bulbName" />.render("chartdiv<xsl:value-of select="bulbName" />");
					        
					    </script>-->
					
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="chartName" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
		</table>
		
		<script type="text/javascript">
			document.getElementById("totalsBoxes").style.display = 'none';
			document.getElementById("buTitle").style.display = 'none';
		</script>
	</xsl:template>
    
</xsl:stylesheet>