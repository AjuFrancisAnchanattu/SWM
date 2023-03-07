<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:include href="../../../xsl/global.xsl"/>
	
	<!-- Sales and Orders Site Home Page -->
	<xsl:template match="salesAndOrdersHome">
	
	<style type="text/css">
		.tableSubHeader { background-color: #dfdfdf; }
		.sideHeader { width: 16px; }
		#topLevel { border-bottom: 1px solid #888; border-right: 1px solid #888; }
		#topLevel td { border-top: 1px solid #888; border-left: 1px solid #888; }
		
		.redCell { background-color: #ff8888; }
		.amberCell { background-color: #FCE085; }
		.greenCell { background-color: #ccffbb; }
		
		.seg { background-color: #efefef; }
		a:hover { cursor: pointer; }
		.hoverBackgroundActive td.greenCell, .hoverBackgroundActive td.seg, .hoverBackgroundActive td.sideHeader { background-color: #fff; }
		.hoverBackgroundNone { }
		
		input#filtersSubmit { margin-left: 20px; } //border: 1px solid #aaa; font-size: 8pt; padding: 3px; color: #fff; background: url("../../images/title-box-bg2.gif"); font-family: verdana; font-weight: bold;}
		
		#filterSub { margin-top: 10px; }
		
		.cornerText { color: #ffff33; float: right; font-weight: normal; padding-right: 6px; font-size: 8pt; text-decoration: none; margin-top: 1px; }
		.cornerLink:hover * { text-decoration: underline; }
		td#centerFilters { display: table; height: 30px; width: 100%; }
		
		.allTotal { padding-left: 12px; background-color: #dfdfdf; }
		.totalRegionCell { background-color: #efefef; }
		
		a.noLinkStyle { text-decoration: none; color: #000; }

		td#centerFilters div {  vertical-align: middle; display:table-cell; }
		td#centerFilters div.filterHeader { margin: 3px 4px 0 0; padding-top: 10px; }
		
		div.funnel { width: 760px; float: left; }
		div.graph { }
		
		div#selectedLetter a { background: #dfdfdf; }
		div.letter { float: left; border-right: 1px solid #888; }
		div.letter a { display: block; width: 20px; height: 17px; background: #efefef;  color: #333; text-align: center; padding-top: 2px; padding-bottom: 1px; }
		div.letter a:hover { background-color: #dfdfdf; color: #000; }		
		div.letter a, div.letter a:hover, div.letter a:active, div.letter a:visited { text-decoration: none; }
		#letters { border-left: 1px solid #888; border-bottom: 1px solid #888; border-top: 1px solid #888; float: left; display: inline; }
	</style>

	<script type="text/javascript">
		<![CDATA[

			function collapseAll(type)
			{
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
						
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].id != null)
						{
							var thisId = allTrTags[i].id;
						
							var comparison = thisId.substring(0, 3);
														
							if (comparison == "row")
							{
								if (document.getElementById(thisId).style.display != 'none')
								{							
									var bu = document.getElementById(thisId).title;

									if (type == 'sp')
									{
										toggle_sp(bu);
									}
									else
									{
										toggle_row(bu, 'true');
									}
								}
							}
							
							if (comparison == "reg")
							{
								if (document.getElementById(thisId).style.display != 'none')
								{							
									var bu = document.getElementById(thisId).title;
									toggle_sp(bu);
								}
							}
						}
					}
				}
			}
			
			
			function expandAll(type)
			{
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
					
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].id != null)
						{
							var thisId = allTrTags[i].id;
						
							var comparison = thisId.substring(0, 3);
														
							if (comparison == "row")
							{
								if (document.getElementById(thisId).style.display == 'none')
								{
									var bu = document.getElementById(thisId).title;
																		
									if (type == 'sp')
									{
										toggle_sp(bu);
									}
									else
									{
										toggle_row(bu, 'true');
									}
								}
							}
							
							if (comparison == "reg")
							{
								if (document.getElementById(thisId).style.display == 'none')
								{							
									var bu = document.getElementById(thisId).title;
									toggle_sp(bu);
								}
							}
						}
					}
				}
			}
		
			
			function toggle_sp(passedId) 
			{
				var parentId = "";
				
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
				
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].id != null)
						{			
							var thisId = allTrTags[i].id;
						
							var comparisonId = thisId.substring(3, (passedId.length + 3))
														
							if (comparisonId == passedId)
							{								
								parentId = passedId;
								
								if (document.getElementById(thisId).style.display == 'none')
								{
									document.getElementById(thisId).style.display = '';
									var plusImage = false;
								}
								else
								{
									document.getElementById(thisId).style.display = 'none';
									var plusImage = true;
								}
							}
						}
					}
				}
				
				if (parentId != "")
				{
					var img = document.getElementById(parentId).getElementsByTagName("img");

					if (plusImage == true)
					{
						img[0].src="../../images/dTree/plus.png";
					}
					else
					{
						img[0].src="../../images/dTree/minus.png";
					}
				}
			}
			

			function toggle_row(passedId, regularRow) 
			{  
				var parentId = "";
			
				var allTrTags = document.getElementById('topLevel').getElementsByTagName("tr");
				
				for (var i in allTrTags)
				{  
					if (allTrTags[i] != null)
					{			
						if (allTrTags[i].id != null)
						{			
							var thisId = allTrTags[i].id;
						
							var comparisonId = thisId.substring(3, (passedId.length + 3))
									
							if (comparisonId == passedId)
							{															
								parentId = comparisonId.substring(0,3);
															
								if (document.getElementById(thisId).style.display == 'none')
								{
									document.getElementById(thisId).style.display = '';
									var plusImage = false;
								}
								else
								{
									document.getElementById(thisId).style.display = 'none';
									var plusImage = true;
								}				
							}
						}
					}
				} 
				
				if (parentId != "")
				{
					var img = document.getElementById(parentId).getElementsByTagName("img");
					
					if (plusImage == true)
					{
						img[0].src="../../images/dTree/plus.png";
					}
					else
					{
						img[0].src="../../images/dTree/minus.png";
					}
				}
				
				if (regularRow == 'true')
				{	
					var allId = passedId + "All";
					
					buAll = document.getElementById(allId);
					
					if (buAll.style.visibility == 'hidden')
					{
						buAll.style.visibility = 'visible';
					}
					else
					{
						buAll.style.visibility = 'hidden';
					}
				}
			} 
			
			
			function topRowHover(obj)
			{
				//obj.className = "hoverBackgroundActive";
				//cellHighlight(obj);
				//obj.nextSibling.className = "hoverBackgroundActive";
			}
			
			function topRowHoverOff(obj)
			{
				//obj.className = "hoverBackgroundNone";
				//obj.nextSibling.className = "hoverBackgroundNone";
			}
			
			function bottomRowHover(obj)
			{
				//obj.className = "hoverBackgroundActive";
				//obj.previousSibling.className = "hoverBackgroundActive";
			}
			
			function bottomRowHoverOff(obj)
			{
				//obj.className = "hoverBackgroundNone";
				//obj.previousSibling.className = "hoverBackgroundNone";
			}
			
			function cellHighlight(obj)
			{
				rowCells = obj.getElementsByTagName("td");
				
				for (var i in rowCells)
				{  
					if (rowCells[i] != null)
					{									
						//alert(rowCells[i].style.background);
						rowCells[i].style.background = '#ff2222';
					}
				}
			}

			function updateSAODropDown($obj)
			{
				// Update the month dropdown if the year dropdown is changed
				if ($obj.id == 'year')
				{
					clearList('month');
				
					var year = document.getElementById('year');
					var year_value = encodeURIComponent(year.options[year.selectedIndex].value);
						
					// Source then the target
					updateDropdown('/apps/dashboard/ajax/saoUpdateMonth?year=' + year_value, 'year', 'month');
				}
			}			
			
			function checkThis($obj)
			{
				alert("test");
				$obj.checked = true;
			}
			
			function setFunnelWidthBU()
			{
				var funnelWidth = (screen.width / 2) - 58;
						
				var funnel1 = document.getElementById('chart3');
				var funnel2 = document.getElementById('chart4');
				
				funnel1.style.width = funnelWidth + "px";
				funnel1.style.margin = "0 31px 0 0";
				
				funnel2.style.width = funnelWidth + "px";
			}		
			
			function setFunnelWidthKCG()
			{
				var funnelWidth = (screen.width / 2) - 58;
				var marginLeft = (funnelWidth / 2) + 16;
						
				var funnel = document.getElementById('chart3');
				
				funnel.style.width = funnelWidth + "px";
				funnel.style.margin = "0 0 0 " + marginLeft + "px";
			}		
			
			function redirectToPage(obj, queryString)
			{
				var page = obj.selectedIndex + 1;
				var url = "salesAndOrdersKCG?" + queryString + "&page=" + page

				window.navigate(url);
			}
			
		]]>
	</script>

		<p style="padding: 10px; background: #fff; margin: 20px; border: 1px solid #ff2222; font-weight: bold;">We are experiencing a few problems with SAP reporting.  Please check back soon.  Sorry for the inconvenience.</p>
	
		<table width="100%" cellpadding="0">
			
			<tr>
				<td valign="top" style="padding: 10px;">		
				
				<div class="title-boxgrey">
					<div class="left-top-corner">
						<div class="right-top-corner">
							<div class="right-bot-corner">
								<div class="left-bot-corner">
									<div class="inner">
										<div class="wrapper">
											<div style="float: left;">
												<h1 style="margin: 1px 30px 0 0; padding-bottom: 1px; line-height: 1.3em; font-weight: bold; font-size: 9pt; color: #FFFFFF;">
													{TRANSLATE:currently_looking_at} 
													<xsl:choose>
											         	<xsl:when test="thisPage='salesAndOrdersSalesEmp'">
											         		<xsl:value-of select="saoSalesEmpTable/salesPerson" />, 
											         	</xsl:when>
											         	<xsl:otherwise>
											         		<xsl:value-of select="buToDisplay" />, 
															<xsl:if test="regionToDisplay!=''">Region: <xsl:value-of select="regionToDisplay" />, </xsl:if>
											         	</xsl:otherwise>
											         </xsl:choose>
													
													<xsl:value-of select="marginToDisplay" />, Currency: <xsl:value-of select="currencyToDisplay" />
													<xsl:if test="customerAmount!=''">, DDM: <xsl:value-of select="customerAmountToDisplay" /></xsl:if>
													<xsl:if test="kcgSearch!=''">, Search: <xsl:value-of select="kcgSearch" /></xsl:if>
													<xsl:if test="filterByToDisplay!=''">, Filter Totals By: <xsl:value-of select="filterByToDisplay" /></xsl:if>
												</h1>
											</div>
											<div style="float: right; color: #fff; font-weight: bold; width: 94px; margin-top: 1px">
												<a href="#" class="cornerLink" onclick="toggle_display('filters'); return false;">
													<img src="../../images/arrow5.gif" alt="show/hide filters" style="margin-top: 2px; float: right; vertical-align: middle;" />	
													<div class="cornerText" style="width: 71px;">toggle filters</div>
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
						<div id="filters" name="filters" style="margin-top: -10px;">	            
							
							<xsl:apply-templates select="displayFilters" />
				    		
				    	</div>	
			    	</div>
			    </div>

				</td>
			</tr> 

			<tr>
				<td valign="top" style="padding: 10px;">
					
					<div class="title-box2">
						<div class="left-top-corner">
							<div class="right-top-corner">
								<div class="right-bot-corner">
									<div class="left-bot-corner">
										<div class="inner">
											<div class="wrapper">
												<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:sao_top_level_table}</p>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="snapin_content">
						
						<div class="snapin_bevel_1" style="margin-top: 1px; padding: 0 8px"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
						
						<div style="float: left;"><a href="{thisPage}?{queryString}#sao_chart_rya"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin-right: 5px; vertical-align: middle;" /><!--{TRANSLATE:view_chart_all}-->Rolling Year Average</a></div>
						
						<xsl:choose>
				         	<xsl:when test="thisPage='salesAndOrdersSalesEmp'">
				         		
				         	</xsl:when>
				         	<xsl:when test="thisPage='salesAndOrdersKCG'">
				         		<div style="float: left;"><a href="{thisPage}?{queryString}#sao_summary_funnelCustomer"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin: 0 5px 0 20px; vertical-align: middle;" /><!--{TRANSLATE:view_chart_all}-->Top 10 Key Customers</a></div>
				         	</xsl:when>
				         	<xsl:otherwise>
								<div style="float: left;"><a href="{thisPage}?{queryString}#sao_chart_yd"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin: 0 5px 0 20px; vertical-align: middle;" /><!--{TRANSLATE:view_chart_all}-->Year Difference</a></div>
								
								<xsl:if test="ceo='false'">
									<div style="float: left;"><a href="{thisPage}?{queryString}#sao_summary_funnelCustomer"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin: 0 5px 0 20px; vertical-align: middle;" /><!--{TRANSLATE:view_chart_all}-->Top 10 Key Customers</a></div>
									<div style="float: left;"><a href="{thisPage}?{queryString}#sao_summary_funnelSalesPeople"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="margin: 0 5px 0 20px; vertical-align: middle;" /><!--{TRANSLATE:view_chart_all}-->Top 10 Sales People</a></div>
								</xsl:if>
								
								<div style="float: left;"><a href="/apps/dashboard/pdf/salesAndOrders/generateSAOPDF?year={year}&amp;month={month}&amp;fromDate={fromDate}&amp;toDate={toDate}&amp;currency={currencyToDisplay}" target="_blank"><img src="/images/pdficon.png" style="margin: 0 5px 0 20px; vertical-align: middle;" />Export Top 10 Key Customers to PDF</a></div>
							</xsl:otherwise>
				        </xsl:choose>
				        
				        
						<xsl:if test="thisPage = 'salesAndOrdersKCG'">
							<div style="float: right;"><xsl:apply-templates select="pageDropdown" /></div>
						</xsl:if>
							
						</div></div></div></div>
						
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
<!--			                                     	<div style="float: left; height: 30px width: 100%;">-->

			                                     		<h1 style="margin: 7px 25px 14px 0; padding: 0; float: left;"><span style="text-decoration: underline; margin-right: 15px;"><xsl:value-of select="dataTypeToDisplay" /></span><span style="font-size: 9pt;"><xsl:value-of select="monthsToDisplay" /> (<xsl:value-of select="yearToDisplay" />)</span></h1>
				                                     	
			                                     		<xsl:choose>
				                                     	<xsl:when test="dataTypeToDisplay='Sales'">
			                                     		
			                                     		<div style="float: left; margin: 0; background: #eee; padding: 5px; border: 1px solid #999;"><strong>Key (Sales Value): </strong>
					                                     	<div style="display: inline; background: #ff8888; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#60;95%</div>
					                                     	<div style="display: inline; background: #FCE085; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">95% - 99.99%</div>
					                                     	<div style="display: inline; background: #ccffbb; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#62;100%</div>
					                                     	
				                                     	</div>
				                                     	
				                                     	<div style="float: left; margin: 0 0 0 20px; background: #eee; padding: 5px; border: 1px solid #999;"><strong>Key (Sales Margin): </strong>
					                                     	<div style="display: inline; background: #ff8888; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#60;-1%</div>
					                                     	<div style="display: inline; background: #FCE085; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">-1% - -0.01%</div>
					                                     	<div style="display: inline; background: #ccffbb; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#62;0%</div>
				                                     	</div>
				                                     	</xsl:when>
				                                     	<xsl:otherwise>
				                                     		<div style="float: left; margin: 0; background: #eee; padding: 5px; border: 1px solid #999;"><strong>Key (Order Value): </strong>
					                                     	<div style="display: inline; background: #ff8888; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#60;95%</div>
					                                     	<div style="display: inline; background: #FCE085; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">95% - 99.99%</div>
					                                     	<div style="display: inline; background: #ccffbb; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#62;100%</div>
					                                     	
				                                     	</div>
				                                     	
				                                     	<div style="float: left; margin: 0 0 0 20px; background: #eee; padding: 5px; border: 1px solid #999;"><strong>Key (Order Margin): </strong>
					                                     	<div style="display: inline; background: #ff8888; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#60;-1%</div>
					                                     	<div style="display: inline; background: #FCE085; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">-1% - -0.01%</div>
					                                     	<div style="display: inline; background: #ccffbb; height: 12px; padding: 0 4px; border: 1px solid #666; margin: 0px 0 0 10px;">&#62;0%</div>
				                                     	</div>
				                                     	</xsl:otherwise>
				                                     	</xsl:choose>
				                                     	
				                                     	
			                                     	
<!--			                                     	</div>-->
			                                     	
			                                     	<xsl:choose>
				                                     	<xsl:when test="drillDown='salesPerson'">
				                                     		<xsl:apply-templates select="saoSalesPersonTable" />				                                     		
				                                     	</xsl:when>
				                                     	<xsl:when test="drillDown='salesManager'">
				                                     		<xsl:apply-templates select="saoSalesManagerTable" />				                                     		
				                                     	</xsl:when>
				                                     	<xsl:when test="drillDown='salesEmp'">
				                                     		<xsl:apply-templates select="saoSalesEmpTable" />				                                     		
				                                     	</xsl:when>
				                                     	<xsl:when test="drillDown='kcg'">
				                                     		<xsl:apply-templates select="saoKCGTable" />				                                     		
				                                     	</xsl:when>
														<xsl:otherwise>
				                                     		<xsl:for-each select="saoTopLevelTable">
																<xsl:apply-templates select="." />
																<br />
															</xsl:for-each>
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
				    	
				    	</div>
				    </div>
				    
				    <a name="chart"></a>
				    
				    <xsl:for-each select="saoDisplayChart">
					    <div class="{thisChartType}" id="chart{position()}">
						    <a name="{chartName}"></a>
							<br /><br />
							<div class="title-box2" >
								<div class="left-top-corner">
									<div class="right-top-corner">
										<div class="right-bot-corner">
											<div class="left-bot-corner">
												<div class="inner">
													<div class="wrapper">
														<div style="float: left;">
															<p style="margin: 2px 0 0 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="chartName" />}</p>
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
					      	
					      	<div class="snapin_content" style="">
					            <div class="snapin_content_3">
									<xsl:apply-templates select="saoChart" />
						    	</div>
						    </div>
						</div>
				  	</xsl:for-each>
				</td>
			</tr>
		</table>
		
		<xsl:choose>
         	<xsl:when test="thisPage='salesAndOrders' and ceo='false'">
         		<script type="text/javascript">
					setFunnelWidthBU();
				</script>                    		
         	</xsl:when>
         	<xsl:when test="thisPage='salesAndOrdersKCG'">
         		<script type="text/javascript">
					setFunnelWidthKCG();
				</script>  
         	</xsl:when>
        </xsl:choose>
		
        <script type="text/javascript">
			//toggle_display("filters");
		</script>	
		
	</xsl:template>
	
	
	<xsl:template match="saoChart">
	
		<xsl:choose>
			<xsl:when test="allowed='1'">
				
				<div id="chartdiv{chartName}" align="center"><xsl:value-of select="chartName" /></div>
				
				<script type="text/javascript">
				
					<xsl:choose>
						<xsl:when test="overRideChartWidth='true'">
							// Get dimension of screen and change dimensions.
							var screenW = screen.width - 100;
						</xsl:when>
						<xsl:otherwise>
							// Get dimension of screen and change dimensions.
							var screenW = screen.width / 2 - 220;
						</xsl:otherwise>
					</xsl:choose>
					
			        var <xsl:value-of select="chartName" /> = new FusionCharts("<xsl:value-of select="chartLocation" />", "<xsl:value-of select="chartName" />", screenW, "<xsl:value-of select="chartHeight" />", "0", "1");
			        <xsl:value-of select="chartName" />.setDataXML("<xsl:value-of select="graphChartData" disable-output-escaping="yes" />");
			        <xsl:value-of select="chartName" />.render("chartdiv<xsl:value-of select="chartName" />");
			        		        
			    </script>
			    
			    <div id="chartdiv{chartName}EXP" align="center"><xsl:value-of select="chartName" />EXP</div>
				<script type="text/javascript">
					var <xsl:value-of select="chartName" />myExportComponent = new FusionChartsExportObject("fcExporter<xsl:value-of select="chartName" />", "../../lib/charts/FusionCharts/FCExporter.swf");
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
	
	<xsl:template match="saoTopLevelTable">

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
			<tr style="background-color: #eaeaea">
				<td style="padding-left: 12px;" colspan="4">
					<xsl:choose>
						<xsl:when test="noDrillDown = 'true'">
							<div style="float: left;"><strong>BU</strong></div>
						</xsl:when>
						<xsl:otherwise>						
							<div style="float: left;"><strong>BU</strong></div><div id="toggleAll" style="float: left; width: 170px; margin: -2px 0 0 10px;">(expand/collapse all)<a href="#" class="noLinkStyle" onclick="expandAll('topLevel'); expandAll('topLevel'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 1px 4px 0 8px; vertical-align: middle;" alt="Expand All"/></a><a href="#" class="noLinkStyle" onclick="collapseAll('topLevel'); collapseAll('topLevel'); return false;"><img src="../../images/dTree/minus.png" style="padding: 0; margin: 1px 0 0 0; vertical-align: middle;" alt="Collapse All"/></a></div>
						</xsl:otherwise>
					</xsl:choose>
				</td>
				
				<xsl:for-each select="saoFields/saoField">
					<td><strong><xsl:value-of select="." /></strong><br /><xsl:value-of select="@dateFrom" /><br /><xsl:value-of select="@dateTo" /></td>
				</xsl:for-each>
			</tr>

			<xsl:for-each select="saoRecord">				
			
				<xsl:choose>
					<xsl:when test="../noDrillDown = 'true'">

						<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" id="order{position()}">
							<td rowspan="2" id="{substring(bu, 0, 4)}">
								<div style="width: 40px; margin-left: 7px;"><xsl:value-of select="bu" /></div>
							</td>
							<td rowspan="2" style="border: none; border-top: 1px solid #888; width: 60px;">
								<div id="{bu}{../region}"><a href="salesAndOrders?bu={bu}&amp;region={../region}{../../queryString}"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 4px 0 0 0; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="../region" /></a></div>
							</td>
							<td rowspan="2" style="border: none; border-top: 1px solid #888;"><a href="salesAndOrders?graphBu={bu}{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
							<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
							
							<xsl:for-each select="saoValues">
								<xsl:apply-templates select="saoValue"/>
							</xsl:for-each>
							<xsl:apply-templates select="saoBudget"/>
							
						</tr>
						<tr onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)" id="sales{position()}">
							<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
							<xsl:for-each select="saoMargins">
								<xsl:apply-templates select="saoMargin"/>
							</xsl:for-each>
							<xsl:apply-templates select="saoBudgetMargin"/>			
						</tr>
				
					</xsl:when>
					<xsl:otherwise>
						
						<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" id="order{position()}">
							<td rowspan="2" id="{substring(bu, 0, 4)}">
								<div style="width: 40px;"><a href="#" onclick="toggle_row('{bu}', 'true'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0 5px; margin-top: 4px; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="bu" /></a></div>
							</td>
							
							<td rowspan="2" style="border: none; border-top: 1px solid #888; width: 60px;">
								<div id="{bu}All"><a href="salesAndOrders?bu={bu}&amp;region=All{../../queryString}"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 4px 0 0 0; vertical-align: middle;" alt="Show/Hide"/>All</a></div>
							</td>
							<td rowspan="2" style="border: none; border-top: 1px solid #888;"><a href="salesAndOrders?graphBu={bu}{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
							<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
							
							<xsl:for-each select="saoValues">
								<xsl:apply-templates select="saoValue"/>
							</xsl:for-each>
							<xsl:apply-templates select="saoBudget"/>
							
						</tr>
						<tr onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)" id="sales{position()}">
							<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
							<xsl:for-each select="saoMargins">
								<xsl:apply-templates select="saoMargin"/>
							</xsl:for-each>
							<xsl:apply-templates select="saoBudgetMargin"/>			
						</tr>
					
					</xsl:otherwise>
				</xsl:choose>	
				
				<xsl:for-each select="regionDrillDown">
			
					<tr id="row{../bu}{position()}Order" onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" title="{../bu}">
						<td rowspan="2" class="seg"><img src="../../images/spacer.gif" title="" /></td>
						<td rowspan="2" class="seg"  style="border: none; border-top: 1px solid #888; margin-left: 1px;">
							<div style="float: left;">
								<a href="salesAndOrders?bu={../bu}&amp;region={region}{../../../queryString}"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 4px 0 0 0; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="region" /></a>
							</div>
						</td>
						<td rowspan="2" style="border: none; border-top: 1px solid #888;" class="seg"><a href="salesAndOrders?graphBu={../bu}&amp;graphRegion={region}{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
						
						<xsl:for-each select="saoValues">
							<xsl:apply-templates select="saoValue"/>
						</xsl:for-each>
						<xsl:apply-templates select="saoBudget"/>
						
					</tr>
					
					<tr id="row{../bu}{position()}Sales" onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)"  title="{../bu}">
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
						<xsl:for-each select="saoMargins">
							<xsl:apply-templates select="saoMargin"/>
						</xsl:for-each>
						<xsl:apply-templates select="saoBudgetMargin"/>			
					</tr>
			
				</xsl:for-each>
									
			</xsl:for-each>
			
			<xsl:choose>
				<xsl:when test="showAllTotals != 'false'">		
	
					<tr>
						<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>
						<td rowspan="2" class="seg" style="background-color: #dfdfdf; border-left: none;"  id="Tot">
							<div style="width: 40px;"><a href="#" onclick="toggle_sp('Tot'); return false;" style="font-weight: bold;"><img src="../../images/dTree/plus.png" style="padding: 0 0px; margin-top: 4px; vertical-align: middle;" alt="Show/Hide" />All</a></div>				
						</td>
						<td rowspan="2" class="seg" style="border: none; border-top: 1px solid #888; background-color: #dfdfdf;"><a href="salesAndOrders?graphRegion=All{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
						
						<xsl:for-each select="totalsAll/totalValue">
							<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudget" /></td>
						
					</tr>
					
					<tr>
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
						<xsl:for-each select="totalsAll/totalMargin">
							<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudgetMargin" /></td>
					</tr>
	
					<tr id="regTotalsNAOrders" title="Tot">
						<td rowspan="2" class="seg regionTotal"><img src="../../images/spacer.gif" title="" /></td>											
						<td rowspan="2" class="seg" style="padding-left: 23px; border-left: none;"><strong>NA</strong></td>
						<td rowspan="2" class="seg" style="border: none; border-top: 1px solid #888;"><a href="salesAndOrders?graphRegion=NA{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
						
						<xsl:for-each select="totalsNA/totalValue">
							<td style="background-color: #efefef"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #efefef"><xsl:value-of select="totalsNA/totalBudget" /></td>
					</tr>
					
					<tr id="regTotalsNASales" title="Tot">
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
						<xsl:for-each select="totalsNA/totalMargin">
							<td style="background-color: #efefef"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #efefef"><xsl:value-of select="totalsNA/totalBudgetMargin" /></td>
					</tr>
	
					<tr id="regTotalsEuropeOrders" title="Tot">
						<td rowspan="2" class="seg regionTotal"><img src="../../images/spacer.gif" title="" /></td>			
						<td rowspan="2" class="seg" style="padding-left: 23px; border-left: none;"><strong>Europe</strong></td>
						<td rowspan="2" class="seg" style="border: none; border-top: 1px solid #888;"><a href="salesAndOrders?graphRegion=Europe{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
						
						<xsl:for-each select="totalsEurope/totalValue">
							<td class="totalRegionCell"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #efefef"><xsl:value-of select="totalsEurope/totalBudget" /></td>
					</tr>
					<tr id="regTotalsEuropeSales" title="Tot">
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
						<xsl:for-each select="totalsEurope/totalMargin">
							<td style="background-color: #efefef"><xsl:value-of select="." /></td>
						</xsl:for-each>
						
						<td style="background-color: #efefef"><xsl:value-of select="totalsEurope/totalBudgetMargin" /></td>
					</tr>
			
				</xsl:when>
				
				<xsl:otherwise>
					
					<xsl:choose>
						<xsl:when test="showNATotals != 'false'">	
					
							<tr>
								<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>
								<td rowspan="2" class="seg" style="background-color: #dfdfdf; border-left: none;"  id="Tot">
									<div style="width: 40px;"> </div>				
								</td>
								<td rowspan="2" class="seg" style="border: none; border-top: 1px solid #888; background-color: #dfdfdf;"><a href="salesAndOrders?graphRegion=NA{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
								<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_value}</strong></td>
								
								<xsl:for-each select="totalsNA/totalValue">
									<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
								</xsl:for-each>
								
								<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsNA/totalBudget" /></td>
								
							</tr>
							<tr>
								<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_margin}</strong></td>
								
								<xsl:for-each select="totalsNA/totalMargin">
									<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
								</xsl:for-each>
								
								<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsNA/totalBudgetMargin" /></td>
							</tr>
							
						</xsl:when>
					
						<xsl:otherwise>

							<tr>
								<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>
								<td rowspan="2" class="seg" style="background-color: #dfdfdf; border-left: none;"  id="Tot">
									<div style="width: 40px;"> </div>	
								</td>
								<td rowspan="2" class="seg" style="border: none; border-top: 1px solid #888; background-color: #dfdfdf;"><a href="salesAndOrders?graphRegion=Europe{../../queryString}#chart"><img src="../../images/icons2020/linegraph.png" alt="View Chart" style="float: right; padding-right: 4px;" /></a></td>
								<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_value}</strong></td>
								
								<xsl:for-each select="totalsEurope/totalValue">
									<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
								</xsl:for-each>
								
								<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsEurope/totalBudget" /></td>
								
							</tr>
							<tr>
								<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_margin}</strong></td>
								
								<xsl:for-each select="totalsEurope/totalMargin">
									<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
								</xsl:for-each>
								
								<td style="background-color: #dfdfdf;"><xsl:value-of select="totalsEurope/totalBudgetMargin" /></td>
							</tr>
							
						</xsl:otherwise>						
					</xsl:choose>
	
				</xsl:otherwise>
				
			</xsl:choose>
			
		</table>
		
		<script type="text/javascript">
			collapseAll('topLevel');
		</script>
		
	</xsl:template>
	
	
	<xsl:template match="saoSalesManagerTable">

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
			<tr style="background-color: #eaeaea">
								
				<td rowspan="2" colspan="2" style="padding-left: 12px;">
					<div style="float: left;"><strong>Sales Manager</strong></div><div id="toggleAll" style="position: absolute; margin: -2px 0 0 10px;">(expand/collapse all)<a href="#" class="noLinkStyle" onclick="expandAll('sp'); expandAll('sp'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 1px 4px 0 8px; vertical-align: middle;" alt="Expand All"/></a><a href="#" class="noLinkStyle" onclick="collapseAll('sp'); collapseAll('sp'); return false;"><img src="../../images/dTree/minus.png" style="padding: 0; margin: 0; vertical-align: middle;" alt="Collapse All"/></a></div>
				</td>
				
				<xsl:for-each select="saoFields/saoField">
					<td colspan="2"><strong><xsl:value-of select="." /></strong><br /><xsl:value-of select="@dateFrom" /><br /><xsl:value-of select="@dateTo" /></td>
				</xsl:for-each>
			</tr>
			<tr class="tableSubHeader">
				<xsl:for-each select="saoFields/saoField">
					<td><strong>{TRANSLATE:sao_value}</strong></td>
					<td><strong>{TRANSLATE:sao_margin}</strong></td>
				</xsl:for-each>
			</tr>
			
			<xsl:for-each select="saoRecord">
				<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)">
					<td rowspan="2" id="{salesManagerId}">
						<xsl:choose>
							<xsl:when test="customerRows = 'true'">
								<div><a href="#" onclick="toggle_sp('{salesManagerId}'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0 5px; margin-top: 4px; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="salesManager" /></a></div>
							</xsl:when>
							<xsl:otherwise>
								<div style="margin-left: 28px;"><xsl:value-of select="salesManager" /></div>
							</xsl:otherwise>
						</xsl:choose>	
					</td>
					
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
					
					<xsl:for-each select="saoOrderValues">
						<xsl:apply-templates select="saoOrderValue"/>
					</xsl:for-each>
					<xsl:apply-templates select="saoBudget"/>
					<xsl:apply-templates select="saoBudgetMargin"/>
				</tr>
				<tr  onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)">
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
					<xsl:for-each select="saoSalesValues">
						<xsl:apply-templates select="saoSalesValue"/>
					</xsl:for-each>
				</tr>
				
				<xsl:for-each select="rowSalesPerson">
			
					<tr id="row{../salesManagerId}{position()}Order" onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" title="{../salesManagerId}">
						
						<td rowspan="2" class="seg">
							<div style="margin-left: 56px;">
								<a href="salesAndOrdersSalesEmp?salesPersonId={salesPersonId}"><xsl:value-of select="salesPerson" /></a>
							</div>
						</td>
						
						<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
						
						<xsl:for-each select="saoOrderValues">
							<xsl:apply-templates select="saoOrderValue"/>
						</xsl:for-each>
						<xsl:apply-templates select="saoBudget"/>
						<xsl:apply-templates select="saoBudgetMargin"/>
					</tr>
					
					<tr id="row{../salesManagerId}{position()}Sales" onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)" title="{../salesManagerId}">
						<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
						<xsl:for-each select="saoSalesValues">
							<xsl:apply-templates select="saoSalesValue"/>
						</xsl:for-each>
					</tr>
			
				</xsl:for-each>
				
			</xsl:for-each>

			
			<tr>
				<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>				
				
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
				
				<xsl:for-each select="totalsAll/totalOrders">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
				
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudget" /></td>
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudgetMargin" /></td>
			</tr>
			<tr>
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
				<xsl:for-each select="totalsAll/totalSales">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
			</tr>
					
		</table>
		
		<script type="text/javascript">
			collapseAll('topLevel');
		</script>
		
	</xsl:template>
		
	
	<xsl:template match="saoSalesEmpTable">

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
			<tr style="background-color: #eaeaea">
								
				<td rowspan="2" colspan="2" style="padding-left: 12px;">
					<div style="float: left;"><strong><xsl:value-of select="salesPerson" /></strong></div>
				</td>
				
				<xsl:for-each select="saoFields/saoField">
					<td colspan="2"><strong><xsl:value-of select="." /></strong><br /><xsl:value-of select="@dateFrom" /><br /><xsl:value-of select="@dateTo" /></td>
				</xsl:for-each>
			</tr>
			<tr class="tableSubHeader">
				<xsl:for-each select="saoFields/saoField">
					<td><strong>{TRANSLATE:sao_value}</strong></td>
					<td><strong>{TRANSLATE:sao_margin}</strong></td>
				</xsl:for-each>
			</tr>
			
			<xsl:for-each select="rowCustomer">
				<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)">
					<td rowspan="2" id="{customerId}">
						<div style="margin-left: 28px;"><xsl:value-of select="customer" /> (<xsl:value-of select="customerId" />)</div>
					</td>
					
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
					
					<xsl:for-each select="saoOrderValues">
						<xsl:apply-templates select="saoOrderValue"/>
					</xsl:for-each>
					<xsl:apply-templates select="saoBudget"/>
					<xsl:apply-templates select="saoBudgetMargin"/>
				</tr>
				<tr  onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)">
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
					<xsl:for-each select="saoSalesValues">
						<xsl:apply-templates select="saoSalesValue"/>
					</xsl:for-each>
				</tr>
				
			</xsl:for-each>
			
			<tr>
				<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>				
				
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
				
				<xsl:for-each select="totalsAll/totalOrders">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
				
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudget" /></td>
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudgetMargin" /></td>
			</tr>
			<tr>
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
				<xsl:for-each select="totalsAll/totalSales">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
			</tr>
					
		</table>
		
		<script type="text/javascript">
			collapseAll('topLevel');
		</script>
		
	</xsl:template>
		
		
	<xsl:template match="saoKCGTable">

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
			<tr style="background-color: #eaeaea">
								
				<td rowspan="2" colspan="2" style="padding-left: 12px;">
					<div style="float: left;"><strong>Key Customer Group</strong></div><div id="toggleAll" style="float: left; width: 170px; margin: -2px 0 0 10px;">(expand/collapse all)<a href="#" class="noLinkStyle" onclick="expandAll('topLevel'); expandAll('topLevel'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 1px 4px 0 8px; vertical-align: middle;" alt="Expand All"/></a><a href="#" class="noLinkStyle" onclick="collapseAll('topLevel'); collapseAll('topLevel'); return false;"><img src="../../images/dTree/minus.png" style="padding: 0; margin: 1px 0 0 0; vertical-align: middle;" alt="Collapse All"/></a></div>
				</td>
				
				<xsl:for-each select="saoFields/saoField">
					<td colspan="2"><strong><xsl:value-of select="." /></strong><br /><xsl:value-of select="@dateFrom" /><br /><xsl:value-of select="@dateTo" /></td>
				</xsl:for-each>
			</tr>
			<tr class="tableSubHeader">
				<xsl:for-each select="saoFields/saoField">
					<td><strong>{TRANSLATE:sao_value}</strong></td>
					<td><strong>{TRANSLATE:sao_margin}</strong></td>
				</xsl:for-each>
			</tr>
			
			<xsl:for-each select="showCustomer">
				<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)">
					<td rowspan="2" id="{customerProcessed}aaa">
						<xsl:choose>
							<xsl:when test="drillDownRows = 'true'">
								<div><a href="#" onclick="toggle_sp('{customerProcessed}aaa'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0 5px; margin-top: 4px; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="customer" /></a></div>
							</xsl:when>
							<xsl:otherwise>
								<div style="margin-left: 28px;"><xsl:value-of select="customer" /></div>
							</xsl:otherwise>
						</xsl:choose>	
					</td>
					
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
					
					<xsl:for-each select="saoOrderValues">
						<xsl:apply-templates select="saoOrderValue"/>
					</xsl:for-each>
					<xsl:apply-templates select="saoBudget"/>
					<xsl:apply-templates select="saoBudgetMargin"/>
				</tr>
				<tr  onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)">
					<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
					<xsl:for-each select="saoSalesValues">
						<xsl:apply-templates select="saoSalesValue"/>
					</xsl:for-each>
				</tr>
				
				<xsl:for-each select="rowSTP">
			
					<tr id="row{../customerProcessed}aaa{position()}Order" onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" title="{../customerProcessed}aaa">
						
						<td rowspan="2" class="seg">
							<div style="margin-left: 56px;">
								<xsl:value-of select="stp" /><br />(<xsl:value-of select="salesPerson" />)
							</div>
						</td>
						
						<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
						
						<xsl:for-each select="saoOrderValues">
							<xsl:apply-templates select="saoOrderValue"/>
						</xsl:for-each>
						<xsl:apply-templates select="saoBudget"/>
						<xsl:apply-templates select="saoBudgetMargin"/>
					</tr>
					
					<tr id="row{../customerProcessed}aaa{position()}Sales" onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)" title="{../customerProcessed}aaa">
						<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
						<xsl:for-each select="saoSalesValues">
							<xsl:apply-templates select="saoSalesValue"/>
						</xsl:for-each>
					</tr>
			
				</xsl:for-each>
				
			</xsl:for-each>
			
			<tr>
				<td rowspan="2" class="seg allTotal"><strong>Totals</strong></td>				
				
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_orders}</strong></td>
				
				<xsl:for-each select="totalsAll/totalOrders">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
				
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudget" /></td>
				<td rowspan="2" style="background-color: #dfdfdf;"><xsl:value-of select="totalsAll/totalBudgetMargin" /></td>
			</tr>
			<tr>
				<td class="tableSubHeader sideHeader"><strong>{TRANSLATE:sao_sales}</strong></td>
				<xsl:for-each select="totalsAll/totalSales">
					<td style="background-color: #dfdfdf;"><xsl:value-of select="." /></td>
				</xsl:for-each>
			</tr>
					
		</table>
		
		<script type="text/javascript">
			collapseAll('sp');
			collapseAll('sp');
		</script>
		
	</xsl:template>
	
	
	<xsl:template match="saoSalesPersonTable">

		<table width="100%" cellpadding="5" cellspacing="0" id="topLevel">
			<tr style="background-color: #eaeaea">
								
				<td colspan="2" style="padding-left: 12px;">
					<div style="float: left;"><strong><xsl:value-of select="../filterByToDisplay" /></strong></div><div id="toggleAll" style="float: left; width: 170px; margin: -2px 0 0 10px;">(expand/collapse all)<a href="#" class="noLinkStyle" onclick="expandAll('sp'); expandAll('sp'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0; margin: 1px 4px 0 8px; vertical-align: middle;" alt="Expand All"/></a><a href="#" class="noLinkStyle" onclick="collapseAll('sp'); collapseAll('sp'); return false;"><img src="../../images/dTree/minus.png" style="padding: 0; margin: 1px 0 0 0; vertical-align: middle;" alt="Collapse All"/></a></div>
				</td>
				
				<xsl:for-each select="saoFields/saoField">
					<td><strong><xsl:value-of select="." /></strong><br /><xsl:value-of select="@dateFrom" /><br /><xsl:value-of select="@dateTo" /></td>
				</xsl:for-each>
			</tr>
					
			<xsl:if test="noRecordsFound != ''">
				<tr>
					<td colspan="{columns}" style="padding-left: 12px;">
						<div style="padding: 20px 20px 20px 0; font-weight: bold; font-style: italic;"><xsl:value-of select="noRecordsFound" /></div>
					</td>
				</tr>
			</xsl:if>
			
			<xsl:for-each select="rowSalesPerson">
				<tr onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)">
					<td rowspan="2" id="{salesPersonId}">
						<xsl:choose>
							<xsl:when test="customerRows = 'true'">
								<div><a href="#" onclick="toggle_sp('{salesPersonId}'); return false;"><img src="../../images/dTree/plus.png" style="padding: 0 5px; margin-top: 4px; vertical-align: middle;" alt="Show/Hide"/><xsl:value-of select="salesPerson" /><xsl:if test="custId != ''"> (<xsl:value-of select="custId" />)</xsl:if></a></div>
							</xsl:when>
							<xsl:otherwise>
								<div style="margin-left: 28px;"><xsl:value-of select="salesPerson" /><xsl:if test="custId != ''"> (<xsl:value-of select="custId" />)</xsl:if></div>
							</xsl:otherwise>
						</xsl:choose>	
					</td>
					
					<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
					
					<xsl:for-each select="saoValues">
						<xsl:apply-templates select="saoValue"/>
					</xsl:for-each>
					<xsl:apply-templates select="saoBudget"/>
					
				</tr>
				<tr  onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)">
					<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
					<xsl:for-each select="saoMargins">
						<xsl:apply-templates select="saoMargin"/>
					</xsl:for-each>
					
					<xsl:apply-templates select="saoBudgetMargin"/>
				</tr>
				
				<xsl:for-each select="rowCustomer">
			
					<tr id="row{../salesPersonId}{position()}Order" onmouseover="topRowHover(this)" onMouseout="topRowHoverOff(this)" title="{../salesPersonId}">
						
						<td rowspan="2" class="seg">
							<div style="margin-left: 56px;">
								<xsl:value-of select="customer" /><xsl:if test="customerId != ''"> (<xsl:value-of select="customerId" />)</xsl:if>
							</div>
						</td>
						
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_value}</td>
						
						<xsl:for-each select="saoValues">
							<xsl:apply-templates select="saoValue"/>
						</xsl:for-each>
						
						<xsl:apply-templates select="saoBudget"/>
						
					</tr>
					
					<tr id="row{../salesPersonId}{position()}Sales" onmouseover="bottomRowHover(this)" onMouseout="bottomRowHoverOff(this)" title="{../salesPersonId}">
						<td class="tableSubHeader sideHeader">{TRANSLATE:sao_margin}</td>
						<xsl:for-each select="saoMargins">
							<xsl:apply-templates select="saoMargin"/>
						</xsl:for-each>
						<xsl:apply-templates select="saoBudgetMargin"/>
					</tr>
			
				</xsl:for-each>
				
			</xsl:for-each>

		</table>
		
		<script type="text/javascript">
			collapseAll('sp');
			collapseAll('sp');
		</script>
		
	</xsl:template>
	
	<xsl:template match="saoBudget">
    	<xsl:choose>
			<xsl:when test="substring(.,1,1) = '-'">
				<td class="redCell"><xsl:value-of select="." /></td>
			</xsl:when>
			<xsl:otherwise>
				<td class="greenCell"><xsl:value-of select="." /></td>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="saoBudgetMargin">
    	<xsl:choose>
			<xsl:when test="substring(.,1,1) = '-'">
				<td class="redCell"><xsl:value-of select="." /></td>
			</xsl:when>
			<xsl:otherwise>
				<td class="greenCell"><xsl:value-of select="." /></td>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="saoOrderValue">
    	<xsl:choose>
			<xsl:when test="substring(.,1,1) = '-'">
				<td class="redCell"><xsl:value-of select="." /></td>
			</xsl:when>
			<xsl:otherwise>
				<td class="greenCell"><xsl:value-of select="." /></td>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="saoMargin">

		<td class="{@color}"><xsl:value-of select="." /></td>
	
	</xsl:template>
	
	<xsl:template match="saoValue">

		<td class="{@color}"><xsl:value-of select="saoValue" /><br />B: <xsl:value-of select="saoBudget" /></td>
	
	</xsl:template>
	
	
	<xsl:template match="saoSalesValue">

		<td class="{@color}"><xsl:value-of select="saoSalesValue" /><br /><xsl:value-of select="saoSalesBudget" /></td>
	
	</xsl:template>
	
	<xsl:template match="displayFilters">
	
		<table width="100%" cellpadding="5" cellspacing="0">
			<tr>
				<td id="centerFilters">
					<!--<div style="display: inline; margin-right: 20px;">
						{TRANSLATE:<xsl:value-of select="saoRegionFilter/regionFilterDisplayName" />}:
						<xsl:for-each select="saoRegionFilter/option">
							<div style="margin-right: 5px; display: inline;">
								<xsl:choose>
									<xsl:when test="optionSelected='1'">
										<input type="radio" name="{../regionFilterName}" value="{optionValue}" checked="yes" /> 
									</xsl:when>
									<xsl:otherwise>
										<input type="radio" name="{../regionFilterName}" value="{optionValue}" /> 
									</xsl:otherwise>
								</xsl:choose>
								
								<xsl:value-of select="optionValue" />
							</div>
						</xsl:for-each>
						
					</div>-->
					
					
					
					<div style="float: left; display: inline;">
						<xsl:for-each select="saoFilterDropdowns">
							<div style="float: left; margin-top: 6px;">
								<div style="float: left;" class="filterHeader">{TRANSLATE:<xsl:value-of select="translateName" />}: </div>
								<div style="float: left;"><xsl:apply-templates select="saoFilterDropdown" /></div>
							</div>
						</xsl:for-each>
					
						<xsl:if test="../kcgSearch!='999999'"><div style="float: left;" class="filterHeader"><label for="kcgSearch">Search: </label></div>
							<div style="float: left; margin-top: 10px;"><input size="40" value="{../kcgSearch}" type="text" name="kcgSearch" id="kcgSearch" style="margin-right: 20px;" /></div>
						</xsl:if>
						
						<xsl:if test="showLetters='true'">
							<div style="float: left; margin-top: 16px; width: 630px;">
								<div style="float: left; display: inline; margin-top: 3px; margin-right: 4px;">Pages:</div>
								<div id="letters">								
									<xsl:for-each select="letters/letter">					
										<!--	Highlight selected letter	-->
										<xsl:choose>
											<xsl:when test="../../../selectedLetter = .">
												<div class="letter" id="selectedLetter"><a href="{thisPage}?{../../../queryString}&amp;letter={.}"><xsl:value-of select="." /></a></div>
											</xsl:when>
											<xsl:otherwise>
												<div class="letter"><a href="{thisPage}?{../../../queryString}&amp;letter={.}"><xsl:value-of select="." /></a></div>
											</xsl:otherwise>
										</xsl:choose>

									</xsl:for-each>
								</div>
							</div>
						</xsl:if>
					
					</div>
					
					<xsl:for-each select="saoFilterRadios">
						<div style="float: left; margin-top: 6px; width: 160px;">							
							<div style="float: left;"><xsl:apply-templates select="saoFilterRadio" /></div>
						</div>
					</xsl:for-each>
					
					<div style="float: left; margin-top: 6px; width: 170px;  display: inline;">										
						<div style="float: left; margin-top: 10px;"><input style="margin: -1px 0 0 0;" type="submit" value="Submit" id="filtersSubmit" /></div>
						<div style="margin-left: 20px; display: inline; float: left;"  class="filterHeader"><a href="{thisPage}?" style="padding-bottom: 4px;"><strong>Clear Filters</strong></a></div>
					</div>
					
					<div style="height: 6px; float: left; clear: both;"></div>
				</td>
			</tr>
		</table>
			
	</xsl:template>
	
	
	<xsl:template match="saoFilterRadio">
		
			<xsl:for-each select="option">
				<xsl:choose>
					<xsl:when test="optionSelected='1'">
						<label style="display: block; float: left; margin: 13px 20px 0 2px;"><input id="{optionValue}" name="{optionGroup}" value="{optionValue}" checked="checked" type="radio" style=" float: left; margin: -3px 1px 0 0;" /><xsl:value-of select="optionValue" /></label>
					</xsl:when>
					<xsl:otherwise>				
						<label style="display: block; float: left; margin: 13px 20px 0 2px;"><input id="{optionValue}" name="{optionGroup}" value="{optionValue}" type="radio" style=" float: left; margin: -3px 1px 0 0;" /><xsl:value-of select="optionValue" /></label>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		
	</xsl:template>
	
	
	<xsl:template match="saoFilterDropdown">
		
		<select id="{dropdownName}" name="{dropdownName}" onchange="updateSAODropDown(this)" style="margin-right: 20px;  margin-top: 10px; " >
	
			<xsl:for-each select="option">
				<xsl:choose>
					<xsl:when test="optionSelected='1'">
						<option selected="selected" value="{optionValue}"><xsl:value-of select="row" /></option>
					</xsl:when>
					<xsl:otherwise>
						<option value="{optionValue}"><xsl:value-of select="row" /></option>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
		</select>	
		
	</xsl:template>
	
	
	<xsl:template match="pageDropdown">
	
		<xsl:for-each select="saoFilterDropdowns">
		
			<div style="margin-top: 2px; float: left;">{TRANSLATE:<xsl:value-of select="translateName" />}: </div>
		
			<xsl:for-each select="saoFilterDropdown">
		
				<select id="{dropdownName}" name="{dropdownName}" onchange="updateSAODropDown(this); redirectToPage(this, '{../../../queryString}');" style="margin-left: 4px;  margin-top: -1px; " >
			
					<xsl:for-each select="option">
						<xsl:choose>
							<xsl:when test="optionSelected='1'">
								<option selected="selected" value="{optionValue}"><xsl:value-of select="row" /></option>
							</xsl:when>
							<xsl:otherwise>
								<option value="{optionValue}"><xsl:value-of select="row" /></option>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
				</select>	
				
			</xsl:for-each>
		</xsl:for-each>
<!--		window.navigate('salesAndOrdersKCG?{queryString}&amp;page=selectedIndexthis.selectedIndex+1-->
	</xsl:template>

</xsl:stylesheet>