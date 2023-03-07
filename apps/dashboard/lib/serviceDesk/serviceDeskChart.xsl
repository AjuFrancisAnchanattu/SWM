<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	
	<!-- 
		Template renders serviceDesk chart
	 -->
	<xsl:template match="chartData">
	
		<!-- 
			A global variable chart_name used in various places, 
				mainly for dynamic generation of paths and javascript functions
		 -->
		<xsl:variable name="chart_name"> <xsl:value-of select="@val"/> </xsl:variable>
		
		<table cellspacing="0" width="260" align="center">
			<tr>
				<td>
				
					<!-- 
						This bit is executed only if there are controlls to display
					 -->
					<xsl:if test="./radioControll | ./cmbControll">
					
						<!-- 
							All controlls are stored in this div
						 -->
						<div id="chartControll_{$chart_name}"></div>
							
						<script language="Javascript" type="text/javascript">
							
							//function used to display controlls
							function displayControlls_<xsl:value-of select="$chart_name" />()
							{
								//setting up values for radio controlls
								<xsl:if test="./radioControll">
									
									var radioArg= '<xsl:value-of select="./radioControll/@var" />';
									
									//holds values of different radio buttons
									var radios_val = new Array();
									
									//holds display text for radio buttons
									var radios_disp = new Array();
									
									<xsl:for-each select="radio">
									
										//add values and displays to arrays
										radios_val.push('<xsl:value-of select="@val"/>');
										radios_disp.push('<xsl:value-of select="@disp"/>');
									
									</xsl:for-each>
								</xsl:if>
								
								//setting up values for dropdown controlls
								<xsl:if test="./cmbControll">
								
									//create array for each dropdown, 
									<xsl:for-each select="cmbControll">
										var <xsl:value-of select="@var"/>s= new Array();
									</xsl:for-each>
									
									//put values to appropriate dropdowns
									<xsl:for-each select="cmb">
											<xsl:value-of select="@var"/>s.push('<xsl:value-of select="@val"/>');
									</xsl:for-each>
								</xsl:if>
								
								//same as $chart_name (xslt variable), created because
								//in some places it is easier to use javaScript variable
								//instead of xslt variable
								var chart_name = '<xsl:value-of select="$chart_name" />';
								
								//displaying radio buttons
								<xsl:if test="./radioControll">
								
									//value of radio button to be selected as default
									var <xsl:value-of select="./radioControll/@var" />='<xsl:value-of select="./radioControll/@def" />';
									
									var controlls = '<div id="radios_' + chart_name + '_' + radioArg + '" align="left" width="100%" style="margin-bottom: 1px; background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">';
									
									//adding radio buttons to div
									for (var i in radios_val)
									{
										if(parseInt(i) || i=='0')
										{
											//checking if radio is the one to be selected as default
											if ( radios_val[i] == <xsl:value-of select="./radioControll/@var" />)
												controlls += '<input type="radio" id="radio_' + chart_name + '_' + radios_val[i] + '" name="radio_' + radioArg + '_' + chart_name + '" value="' + radios_val[i] + '"  onclick="display_' + chart_name + '();" checked="1">' + radios_disp[i] + '</input>';
											else
												controlls += '<input type="radio" id="radio_' + chart_name + '_' + radios_val[i] + '" name="radio_' + radioArg + '_' + chart_name + '" value="' + radios_val[i] + '"  onclick="display_' + chart_name + '();">' + radios_disp[i] + '</input>';
										}
									}
									
									controlls += '</div>';
								</xsl:if>
								
								//displaying dropdowns
								<xsl:if test="./cmbControll">
								
									//values of each dropdown to be selected as default
									<xsl:for-each select="cmbControll">
										var <xsl:value-of select="@var"/>= <xsl:value-of select="@def"/>;
									</xsl:for-each>
									
									controlls += '<div align="left" width="100%" style="margin-bottom: 1px; background: #FFFFFF; border: 1px solid #9c9898; padding: 0px;">';
									
									controlls += '<table style="padding: 0px; margin: 0px;"><tr>';
									
									//looping through all dropdowns
									<xsl:for-each select="cmbControll">
										
										controlls += '<td style="padding-left: 10px;">';
										
										//title of dropdown
										var title = '<xsl:value-of select="@var"/>:';
										title = title.charAt(0).toUpperCase() + title.slice(1);
										controlls += title;
										
										//name of dropdown to be put to html
										var tmp = '<xsl:value-of select="@var"/>';
										
										//adding dropdown to html
										controlls += '<select name="' + tmp + '_' + chart_name + '" id="' + tmp + '_' + chart_name + '" onChange="display_' + chart_name + '();">';
										
										//adding values to just added dropdown
										for(var i in <xsl:value-of select="@var"/>s)
										{
											if(parseInt(i) || i=='0')
											{
												//checking if value is to be selected as default
												if ( <xsl:value-of select="@var"/>s[i] == <xsl:value-of select="@var"/>)
													controlls += '<option selected="1">' + <xsl:value-of select="@var"/>s[i] + '</option>';
												else
													controlls += '<option>' + <xsl:value-of select="@var"/>s[i] + '</option>';
											}
										}
										
										controlls += '</select>';
										controlls += '</td>';
									</xsl:for-each>
									
									controlls += '</tr></table>';
									
									controlls += '</div>';
								</xsl:if>
								
								//add all controlls to controlls div
								document.getElementById('chartControll_<xsl:value-of select="$chart_name" />').innerHTML = controlls;			
							}
							
							//call the function to display controlls
							displayControlls_<xsl:value-of select="$chart_name" />();
							
						</script>
					</xsl:if>
				
				<!-- 
					Display chart only if user has permission
				 -->
				<xsl:choose>
					<xsl:when test="allowed='1'">
						
						<!-- 
							Div to display the chart
						 -->
						<div id="chartDiv_{$chart_name}" align="left">chartDiv_<xsl:value-of select="$chart_name" /></div>
						
						<script language="Javascript" type="text/javascript">
							
							//function used to display chart
							function display_<xsl:value-of select="$chart_name" />()
							{
							
								//similar step as in displayControlls, getting all possible 
								//values for radio buttons
								<xsl:if test="./radioControll">
									var radioArg= '<xsl:value-of select="./radioControll/@var" />';
									//possible values for arguments
									<xsl:if test="./radioControll">
										var radios_val = new Array();
										var radios_disp = new Array();
										<xsl:for-each select="radio">
												radios_val.push('<xsl:value-of select="@val"/>');
												radios_disp.push('<xsl:value-of select="@disp"/>');
										</xsl:for-each>
									</xsl:if>
								</xsl:if>
								
								//similar step as in displayControlls, getting all possible 
								//values for dropdowns
								<xsl:if test="./cmbControll">
									<xsl:for-each select="cmbControll">
										var <xsl:value-of select="@var"/>s= new Array();
									</xsl:for-each>
									
									<xsl:for-each select="cmb">
											<xsl:value-of select="@var"/>s.push('<xsl:value-of select="@val"/>');
									</xsl:for-each>
								</xsl:if>
								
								//same as $chart_name (xslt variable), created because
								//in some places it is easier to use javaScript variable
								//instead of xslt variable
								var chart_name = '<xsl:value-of select="$chart_name" />';
								
								//get the value of selected radio button
								<xsl:if test="./radioControll">
								
									var <xsl:value-of select="./radioControll/@var" />;
									
									for (var i in radios_val)
										if(parseInt(i) || i=='0')
										{
											if(document.getElementById('radio_' + chart_name + '_' + radios_val[i]).checked)
												<xsl:value-of select="./radioControll/@var" />= radios_val[i];
										}
									
								</xsl:if>
								
								//get values of dropdowns
								<xsl:if test="./cmbControll">
								
									<xsl:for-each select="cmbControll">
										var <xsl:value-of select="@var"/> = document.getElementById('<xsl:value-of select="@var"/>_' + chart_name).selectedIndex;
										<xsl:value-of select="@var"/> = <xsl:value-of select="@var"/>s[<xsl:value-of select="@var"/>];
									</xsl:for-each>
								
								</xsl:if>
								
								//do AJAX
								var xmlVar;
								var ajaxRequest;
								
								try{
									// Opera 8.0+, Firefox, Safari
									ajaxRequest = new XMLHttpRequest();
								} catch (e){
									// Internet Explorer Browsers
									try{
										ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
									} catch (e) {
										try{
											ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
										} catch (e){
											// Something went wrong
											alert("Your browser broke!");
											return false;
										}
									}
								}
								
								// Create a function that will receive data sent from the server
								ajaxRequest.onreadystatechange = function(){
									if(ajaxRequest.readyState == 4){
									
										xmlVar = ajaxRequest.responseText;
										
										//check desired width of a chart
				    					<xsl:choose>
											<xsl:when test="overRideChartWidth='0'">
												var screenW = screen.width / 2 - 220;
											</xsl:when>
											<xsl:when test="overRideChartWidth='1'">
												var screenW = screen.width / 3 - 64;
											</xsl:when>
											<xsl:when test="overRideChartWidth='2'">
												var screenW = (screen.width /2 - 200) * 2;
											</xsl:when>
										</xsl:choose>
					    				
					    				//display the chart
										var <xsl:value-of select="$chart_name" /> = new FusionCharts("../../lib/charts/FusionCharts/<xsl:value-of select="chartType"/>.swf", "<xsl:value-of select="$chart_name" />", screenW, "<xsl:value-of select="chartHeight" />" , "0", "1");								
								        <xsl:value-of select="$chart_name" />.setDataXML(xmlVar);
								        <xsl:value-of select="$chart_name" />.render("chartDiv_<xsl:value-of select="$chart_name" />");
									}
								}
								
								//this will be a string with arguments to send to ajax script
								var argsString= '';
								
								//argument with value of radio button
								<xsl:if test="./radioControll">
									argsString = '&amp;<xsl:value-of select="./radioControll/@var" />=' + <xsl:value-of select="./radioControll/@var" />;
								</xsl:if>
								
								//arguments with values of dropdowns
								<xsl:if test="./cmbControll">
									<xsl:for-each select="cmbControll">
										argsString += '&amp;<xsl:value-of select="@var"/>=' + <xsl:value-of select="@var"/>;
									</xsl:for-each>
								</xsl:if>
								
								//path to ajax script
								var path = '<xsl:value-of select="@val"/>';
								
								argsString += '&amp;exporter=<xsl:value-of select="chartExport"/>';
								
								//send request to ajax script and wait for response
								ajaxRequest.open("GET", "/apps/dashboard/snapins/" + path + "/Ajax/generateUpdatedChart?" + argsString, false);
								ajaxRequest.send(null); 
					    	}
					    	
					    	//call the function
					    	display_<xsl:value-of select="$chart_name" />();
					    	
					    </script>
					    
					</xsl:when>
					<xsl:otherwise>
						You do not have access to the <xsl:value-of select="$chart_name" /> report.
					</xsl:otherwise>
				</xsl:choose>
				
				</td>
			</tr>
			
			<!-- 
				This is only executed if the chart is drillDown
			 -->
			<xsl:if test="drillDown">
				<tr>
					<td>
						
						<!-- 
							Small info for user
						 -->
						<div id="chartDiv_{$chart_name}_DrillDown_Title" align="left" width="100%" style=" margin-bottom: 1px; background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">
							<table width="100%">
								<tr>
									<td width="90%" align="left">
										Click chart above to show details on chart below
									</td>
									<!-- 
										+|- to show/hide the chart
									 -->
									<td width="10%" align="right" valign="top">
										<xsl:choose>
											<xsl:when test="showExport='true'">
												<a href="#" onclick="toggle_display('open_{$chart_name}_DrillDown'); return false;" style="text-decoration: none; color: black;">+|-</a>
											</xsl:when>
											
											<xsl:otherwise>
												<a href="#" onclick="toggle_display('open_{$chart_name}_DrillDown'); toggleShowDrillDownCheckBox('{$chart_name}'); return false;" style="text-decoration: none; color: black;">+|-</a>
											</xsl:otherwise>
										</xsl:choose>
									</td>
								</tr>
							</table>
						</div>
						
						<!-- 
							this div will hold drilldown
						 -->
						<div id="open_{$chart_name}_DrillDown">
							<div id="chartDiv_{$chart_name}_DrillDown" ></div>
						</div>
						
						<script type="text/javascript">
							
							//function to display drilldown
							//args- string with arguments for display of the drilldown
							function display_<xsl:value-of select="$chart_name" />_DrillDown(args)
							{
								//getting individual arguments from the string
								//structure of the string:
								//
								//	'arg1name=arg1val,arg2name=arg2val,...'
								
								var argsArray = args.split(",");
								var argType = new Array();
								var argVal = new Array();
								
								for (var i in argsArray)
									if(parseInt(i) || i=='0')
									{
										tmpArray= argsArray[parseInt(i)].split("=",2);
										argType.push(tmpArray[0]);
										argVal.push(tmpArray[1]);
									}
								
								//do AJAX
								var xmlDrillDown;
								var ajaxRequest;
							
								try{
									// Opera 8.0+, Firefox, Safari
									ajaxRequest = new XMLHttpRequest();
								} catch (e){
									// Internet Explorer Browsers
									try{
										ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
									} catch (e) {
										try{
											ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
										} catch (e){
											// Something went wrong
											alert("Your browser broke!");
											return false;
										}
									}
								}
								
								// Create a function that will receive data sent from the server
								ajaxRequest.onreadystatechange = function(){
									if(ajaxRequest.readyState == 4){
									
										xmlDrillDown = ajaxRequest.responseText;
										
										//again width of the chart
				    					<xsl:choose>
											<xsl:when test="overRideChartWidth='0'">
												var screenW = screen.width / 2 - 220;
											</xsl:when>
											<xsl:when test="overRideChartWidth='1'">
												var screenW = screen.width / 3 - 64;
											</xsl:when>
											<xsl:when test="overRideChartWidth='2'">
												var screenW = (screen.width /2 - 200) * 2;
											</xsl:when>
										</xsl:choose>
					    				
					    				//display drilldown
										var drillDown_<xsl:value-of select="$chart_name" /> = new FusionCharts("../../lib/charts/FusionCharts/<xsl:value-of select="drillDownType"/>.swf", "drillDown_<xsl:value-of select="$chart_name" />", screenW, "<xsl:value-of select="chartHeight" />", "0" , "1");								
								        drillDown_<xsl:value-of select="$chart_name" />.setDataXML(xmlDrillDown);
								        drillDown_<xsl:value-of select="$chart_name" />.render("chartDiv_<xsl:value-of select="$chart_name" />_DrillDown");
								        
								        //show the div with chart
								        document.getElementById('open_<xsl:value-of select="$chart_name" />_DrillDown').style.display = '';
								        
								        <xsl:choose>
											<xsl:when test="showExport='true'">
											</xsl:when>
											
											<xsl:otherwise>
												toggleShowDrillDownCheckBox('<xsl:value-of select="$chart_name" />');
											</xsl:otherwise>
										</xsl:choose>
									}
								}
								
								//string with arguments to pass to ajax
								var argStr = '';
								
								//prepare them values
								for (var i in argsArray)
									if(parseInt(i) || i=='0')
										argStr += "&amp;" + argType[parseInt(i)] + "=" + argVal[parseInt(i)];
								
								<xsl:choose>
									<xsl:when test="showExport='true'">
										argStr += '&amp;exporter=drillDown_<xsl:value-of select="chartExport"/>';
									</xsl:when>
									<xsl:otherwise>
										argStr += '&amp;exporter=<xsl:value-of select="chartExport"/>';
									</xsl:otherwise>
								</xsl:choose>
								
								//send request to ajax
								ajaxRequest.open("GET", "/apps/dashboard/snapins/<xsl:value-of select="$chart_name" />/Ajax/generateDrillDown?" + argStr, true);
								ajaxRequest.send(null); 
							}
						</script>
						
					</td>
				</tr>
			</xsl:if>
			
			<!-- 
				This is only displayed if 1 has been passed as showExports variable
					to output function of the chart.
			 -->
			<xsl:choose>
				<xsl:when test="showExport='true'">
				<tr>
					<td>
						<table width="100%">
							<tr>
								
								<!-- 
									This export is for main chart
									Displayed always if user wants to display exports
								 -->
								<td  allign="left">
									<div align="left" width="100%" style=" margin-bottom: 1px; background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">
									
										<div style="border-bottom: 1px solid #9c9898;">Export Chart</div>
										<div id="chartDiv_{$chart_name}_Export" align="center">chartDiv_<xsl:value-of select="$chart_name" />_Export</div>
										<script type="text/javascript">
											var mainExportComponent = new FusionChartsExportObject("<xsl:value-of select="chartExport" />", "../../lib/charts/FusionCharts/FCExporter.swf");
											mainExportComponent.debugMode = true;
											mainExportComponent.Render("chartDiv_<xsl:value-of select="$chart_name" />_Export");
										</script>
									
									</div>
								</td>
								
							<!-- 
								This export is for drillDown chart
								Displayed only if user wants to display exports
									and chart is drilldown one
							 -->
							<xsl:if test="drillDown">
								<td  allign="left">
									<div align="left" width="100%" style=" margin-bottom: 1px; background: #FFFFFF; border: 1px solid #9c9898; padding: 5px;">
									
										<div style="border-bottom: 1px solid #9c9898;">Export Details Chart</div>
										<div id="chartDiv_{$chart_name}_DrillDown_Export" align="center">chartDiv_<xsl:value-of select="$chart_name" />_DrillDown_Export</div>
										<script type="text/javascript">
											var drillDownExportComponent = new FusionChartsExportObject("drillDown_<xsl:value-of select="chartExport" />", "../../lib/charts/FusionCharts/FCExporter.swf");
											drillDownExportComponent.debugMode = true;
											drillDownExportComponent.Render("chartDiv_<xsl:value-of select="$chart_name" />_DrillDown_Export");
										</script>
									
									</div>
								</td>
							</xsl:if>
							</tr>
						</table>
					</td>
				</tr>
				</xsl:when>
			</xsl:choose>
			
		</table>
	</xsl:template>
    
</xsl:stylesheet>