<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="header">
	
		<div class="widthhackforie" id="widthhackforieid" style="background: #FFFFFF url(/images/top_left.gif) no-repeat top left;"><div style="background: url(/images/top_right.gif) no-repeat top right; border-bottom: 1px solid #666666;">
									
		<div class="header">
	
				<!--<xsl:choose>
					<xsl:when test="@state='scapanetdev'">
					</xsl:when>
					<xsl:otherwise>
						
					</xsl:otherwise>
				</xsl:choose>-->
				
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td rowspan="3">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">
								<tr>
									<td><a href="http://ukdunapp022"><img src="/images/logo_new.jpg" class="logo" alt="Scapa Intranet" /></a></td>
									<!--<td valign="top"><br /><a href="/"><img src="/images/One_Scapa_Logo2.jpg" alt="World class, inspired, market driven team, focused on optimising customer &amp; shareholder value through responsible, agile delivery of specialist tape solutions." /></a><br /><br />
									"<a href="#" class="vision" title="{world_class_description}" style="" >{TRANSLATE:world_class}</a>, <a href="#" class="vision" title="{inspired_description}" style="" >{TRANSLATE:inspired}</a>, <a href="#" class="vision" title="{market_driven_description}" style="">{TRANSLATE:market_driven} </a><a href="#" class="vision" title="{team_description}" style="" >{TRANSLATE:team}</a>, focused on optimising customer &amp; shareholder <a href="#" class="vision" title="{value_description}">{TRANSLATE:value}</a> through <a href="#" class="vision" title="{responsible_description}">{TRANSLATE:responsible}</a>, <a href="#" class="vision" title="{agile_description}">{TRANSLATE:agile}</a> delivery of specialist <a href="#" class="vision" title="{tape_solutions_description}" style="" >{TRANSLATE:tape_solutions}</a>".
									</td>-->
								</tr>
							</table>
						</td>
						<!--<td rowspan="3">
							
						</td>-->
						<td>
						
							<p style="text-align: right; padding: 10px 20px 0 0;">
							
							<table cellpadding="0" cellspacing="0" width="350px" height="25px">
								<tr>
									<td>
										<!--<strong>{TRANSLATE:scapa_instant_messaging}: </strong><img src="/images/icons2020/chat.jpg" hspace="5" align="absmiddle" />-->
									</td>

									<!--<xsl:choose>
										<xsl:when test="chat_id != 0">
											<td>
												<div align="right">
													<a href="#" onclick="window.open('/apps/chat/chat?person_name={myname}&amp;chat_name={chat_id}&amp;NTLogon={myNTLogon}&amp;myphoto={myphoto}', 'initiateChat{chat_id}','menubar=0,resizable=0,width=300,height=500'); window.self.close();"><strong>{TRANSLATE:chat_requested}</strong></a> ({TRANSLATE:click_to_open} )
												</div>
											</td>
										</xsl:when>
										<xsl:otherwise>
											<td align="right">
												<xsl:choose>
												<xsl:when test="IMpermissions='false'">
													Not currently available.
												</xsl:when>
												<xsl:otherwise>
													<select onchange="open_chat_window(this.options[this.selectedIndex].value);">
														<option>{TRANSLATE:users_online} (<xsl:value-of select="numUsersOnline" />)</option>
														<xsl:for-each select="onlineUsers">
																<option value="{onlineUsersLogon}"><xsl:value-of select="onlineUsersName" /></option>
														</xsl:for-each>
													</select>												
												</xsl:otherwise>
												</xsl:choose>
											</td>
										</xsl:otherwise>
									</xsl:choose>-->
									
									<td align="right"><!--|--> Logged in as <xsl:value-of select="@user" /> <!--|--> </td>
									
<!-- this is the code for adding or removing pages from the usefulLinks snapin: DP -->
									<!--<td valign="absmiddle">
												<a href="#" title="Add this page to your useful links" onclick="Javascript:usefulLinkWindow('{currentUrl}', 'add')" id="addLink" style="display:{addLinkStyle}">Add</a>
												<a href="#" title="Remove this page from your useful links" onclick="Javascript:usefulLinkWindow('{currentUrl}', 'remove')" id="removeLink" style="display:{removeLinkStyle}">Remove</a>
												<a href="#" onclick="Javascript:window.open('/apps/help/window/helpWindow?type=page&amp;app=useful_links','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">
													<img src="/images/icons1515/help.png" />
												</a>
									</td>-->
								</tr>
<!-- to here -->
<!--								<tr>
									<xsl:choose>
										<xsl:when test="chat_id != 0">
											<td width="300px" style="background-color: #EFEFEF"><div align="center"><xsl:value-of select="myname" /> - <a href="#" onclick="window.open('/apps/chat/chat?person_name={myname}&amp;chat_name={chat_id}&amp;NTLogon={myNTLogon}&amp;myphoto={myphoto}', 'initiateChat','menubar=0,resizable=0,width=300,height=500');"><strong>{TRANSLATE:open_chat}</strong></a></div></td>
										</xsl:when>
										<xsl:otherwise>
											<td width="300px"></td>
										</xsl:otherwise>
									</xsl:choose>
									<td colspan="2" width="300px"></td>
								</tr>
-->							</table>
							
							</p>
						
						</td>
					</tr>
					<tr>
						<td>
							<p style="text-align: right; font-weight: bold; font-family: arial; color: #999999; font-size: 24px; font-style: italic; padding: 3px 18px 0 0;"><xsl:value-of select="@location" /></p>
						</td>
					</tr>
					<tr>
						<td valign="bottom" align="right">
							<xsl:if test="count(item) &gt; 0">
								<div class="nav">
									<ul>
										
										<xsl:for-each select="item">
									
											<li id="li_toolbar_{@id}" onMouseOver="showDropdown('{@id}');" onMouseOut="hideDropdown('{@id}');"><a href="{@url}" target="{@target}"><xsl:value-of select="@title"/></a></li>
											
										</xsl:for-each>

									</ul>
								</div>
								
								<xsl:apply-templates select="item" />
							</xsl:if>
						</td>
					</tr>
				</table>
				
			</div>
		
		</div></div>
		
		<xsl:if test="count(secondaryMenuItem) &gt; 0">
		
			<div class="toolbar">
				<ul>
					<xsl:apply-templates select="secondaryMenuItem" />
					<li style="width: 10px; background: none; padding: 0;"><img src="/images/sub_menu_corner.gif" width="10" height="22" border="0" alt="" /></li>
				</ul>
			</div>
			
		</xsl:if>
			
		<xsl:apply-templates select="notice" />
		
		<xsl:apply-templates select="errorLog" />
		
	</xsl:template>
	
	

	
	<xsl:template match="secondaryMenuItem">
		<li><a href="{@url}" target="{@target}"><xsl:value-of select="text()" /></a></li>
	</xsl:template>
	
	
	<xsl:template match="notice">
		<div class="notice">Notice: <xsl:value-of select="text()" /></div>
	</xsl:template>
	
	
	<xsl:template match="errorLog">
		<div style="background: #f2d2d2; padding: 0 10px 10px 10px; border: 2px dashed #f20000; margin: 10px;">
			<h1>PHP Errors occurred</h1>
			<xsl:value-of select="text()" />
		</div>
	</xsl:template>
	
	
	
	<xsl:template match="item">
		
		<div class="dropdown_container" id="dropdown_container_{@id}" onMouseOver="showDropdown('{@id}');" onMouseOut="hideDropdown('{@id}');">
	
			<table width="170" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="9"><img src="/images/top_left_corner_dark.gif" /></td>
					<td><img src="/images/menu-bg.gif" width="156" height="9" /></td>
					<td width="9"><img src="/images/top_right_corner_dark.gif" /></td>
				</tr>
				<tr>
					<td colspan="3" bgcolor="#c84c4c" style="padding: 2px;">
						<xsl:apply-templates select="child" />
						
						<!-- this is a dirty hack! -->						
						<div style="height: 1px; background: #c60000; border: none;"><img src="/images/top_right_corner_dark.gif" height="1" width="1" /></div>
						
						
					</td>
				</tr>
				<tr>
					<td width="9"><img src="/images/bottom_left_corner_dark.gif" /></td>
					<td><img src="/images/menu-bg.gif" width="156" height="9" /></td>
					<td width="9"><img src="/images/bottom_right_corner_dark.gif" /></td>
				</tr>
			</table>
			
		</div>
		
		<iframe id="dropdown_container_iframe_{@id}" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" src="about:blank" scrolling="no" frameborder="0" >-</iframe>


		
		<xsl:for-each select="child">
			<xsl:if test="count(grandchild) &gt; 0">
			<div class="dropdown_container" id="sub_dropdown_container_{@id}" onMouseOver="showSubDropdown('{@parent}', '{@id}');" onMouseOut="hideSubDropdown('{@id}', true);">

			<table width="170" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="9"><img src="/images/top_left_corner_dark.gif" /></td>
					<td><img src="/images/menu-bg.gif" width="156" height="9" /></td>
					<td width="9"><img src="/images/top_right_corner_dark.gif" /></td>
				</tr>
				<tr>
					<td colspan="3" bgcolor="#c84c4c" style="padding: 2px;">
						<xsl:apply-templates select="grandchild" />
						
						<!-- this is a dirty hack! -->						
						<div style="height: 1px; background: #c60000; border: none;"><img src="/images/top_right_corner_dark.gif" height="1" width="1" /></div>
						
					</td>
				</tr>
				<tr>
					<td width="9"><img src="/images/bottom_left_corner_dark.gif" /></td>
					<td><img src="/images/menu-bg.gif" width="156" height="9" /></td>
					<td width="9"><img src="/images/bottom_right_corner_dark.gif" /></td>
				</tr>
			</table>

			</div>
			
			<iframe id="sub_dropdown_container_iframe_{@id}"  style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" src="about:blank" scrolling="no" frameborder="0">-</iframe>

			</xsl:if>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template match="child">
		<div class="dropdown_menu" id="dropdown_{@id}" onMouseOver="showSubDropdown('{@parent}', '{@id}');" onMouseOut="hideSubDropdown('{@id}', false);"><a href="{@url}" target="{@target}"><xsl:value-of select="@title"/></a></div>
	</xsl:template>
	
	<xsl:template match="grandchild">
		
		<div class="dropdown_menu" id="dropdown_{@id}"><a href="{@url}" target="{@target}"><xsl:value-of select="@title"/></a></div>
		
		<!-- onMouseOver="showSubDropdown('{@parent_id}_{@id}','{@id}');" onMouseOut="hideSubDropdown('{@parent_id}_{@id}', false);" -->
	</xsl:template>
	
</xsl:stylesheet>