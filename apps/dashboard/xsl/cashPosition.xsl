<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- Cash Position Site Home Page -->
	<xsl:template match="cashPositionHome">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">	
				
				<xsl:if test="added='true'">
					<div class="green_notification">
						<h1><strong>{TRANSLATE:added_successfully}</strong></h1>
					</div>
				</xsl:if>	
				
				<xsl:choose>
				<!-- If the current user has Cash Position Privalges allow, otherwise show Access Denied-->
				<xsl:when test="allowed='1'">
					
					<h1>{TRANSLATE:cash_position}</h1>
					
						<xsl:if test='bankNotInArray != 0'>
					    	
					    	<strong>{TRANSLATE:sites_not_entered}: </strong>
					    
					    	<xsl:for-each select="bankNotInArray">
						    	<xsl:value-of select="bankName" />
						    </xsl:for-each>
						    
						    <br /><br />
					    </xsl:if>
                    
					<xsl:apply-templates select="cashPositionCharts" />
                
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
	
	<!-- Cash Position Add Page -->
	<xsl:template match="cashPositionAdd">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
				
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
					<h1><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" /><xsl:value-of select="bankName" /><xsl:if test="cashEdit='true'"> | <xsl:value-of select="cashDate" /></xsl:if></h1>
				
					<!--<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">-->
					
					<xsl:choose>
						<xsl:when test="region='EUROPE'">
							<div class="green_notification" style="padding: 10px;"><b>Jump To: </b> <a href="#ukplctop">UK/PLC</a> | <a href="#francetop">France</a> | <a href="#italytop">Italy</a> | <a href="#schweiztop">Schweiz</a> | <a href="#spaintop">Spain</a> | <a href="#germanytop">Germany</a> | <a href="#beneluxtop">Benelux</a></div>
						</xsl:when>
						<xsl:when test="region='ASIA'">
													
						</xsl:when>
						<xsl:when test="region='NA'">
													
						</xsl:when>
						<xsl:otherwise>
							
						</xsl:otherwise>
					</xsl:choose>
					
					<div class="title-box2">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<p style="color: #FFFFFF; font-weight: bold;">{TRANSLATE:add_cash_position_report}</p>
							</div></div>
						</div></div></div></div>
					</div>
					
						<xsl:apply-templates select="form" />
					
				</td>
			</tr>
		</table>		
		
	</xsl:template>
	
	<xsl:template match="cashPositionCharts">
			
		<!--<div class="snapin_top">
	        <div class="snapin_top_3">
	    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:net_cash_position}</p>
	    	</div>
	  	</div>-->
	  	
	  	<div class="title-box2">
				<div class="left-top-corner">
                   <div class="right-top-corner">
                      <div class="right-bot-corner">
                         <div class="left-bot-corner">
                            <div class="inner">
                               <div class="wrapper">
                					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:net_cash_position}</p>   					            
                               </div>
                         </div>
                      </div>
                   </div>
                </div>
             </div>
        </div>
	      	
	      	<div class="snapin_content">
	            <div class="snapin_content_3">
	
					<xsl:apply-templates select="cashPositionChartGauge" /><br />
					<xsl:apply-templates select="cashPositionChart" /><br />
		    	
		    	</div>
		    </div>
		    
	</xsl:template>
	
	<xsl:template match="cashPositionChart">
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
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/FusionCharts/MSLine.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
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
	
	<xsl:template match="cashPositionChartGauge">
		<table cellspacing="0" width="260">
			<tr>
				<td style="background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;"><strong>Cash Position as of: <xsl:value-of select="lastAuthorisedCashDate" /></strong></td>
			</tr>
			<tr>
				<td>
				<xsl:choose>
					<xsl:when test="../../allowed='1'">
						<a name="ltaChart" />
						<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
						<script type="text/javascript">
						
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 400;
							
					        var <xsl:value-of select="chartName" /> = new FusionCharts("../../lib/charts/Widgets/HLinearGauge.swf", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
					        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
					        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
					        
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
	
</xsl:stylesheet>