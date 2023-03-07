<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="gis">
	
	
	</xsl:template>
	
	
	<xsl:template match="gisHome">

		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">	
					<xsl:choose>
						<xsl:when test="gis_report/id">
							<xsl:apply-templates select="gis_report" />	
						</xsl:when>
						<xsl:when test="notfound='true'">
							<h1>
								<img src="http://scapanetdev/apps/gis/error_loading_gis.jpg" align="center" />
								<font color="red">
									{TRANSLATE:error_loading_gis}
								</font>
							</h1>
							<p>
								{TRANSLATE:error_loading_gis_message}
							</p>
						</xsl:when>
						<xsl:otherwise>
							<div class="title-box2">
								<div class="left-top-corner">
									<div class="right-top-corner">
										<div class="right-bot-corner">
											<div class="left-bot-corner">
												<div class="inner">
													<div class="wrapper">
														<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:competitor_profile_list}</p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<table width="100%" cellspacing="2" cellpadding="2" class="data_table" style="border: 1px solid #CCCCCC;">
								<tr>
									<td width="30%">
										<strong>	
											{TRANSLATE:competitor_name}
										</strong>
									</td>
									<td width="30%">
										<strong>
											{TRANSLATE:profile_type}
										</strong>
									</td>
									<td>
										<strong>
											{TRANSLATE:details}
										</strong>
									</td>
									<td>
										<br />
										<br />
									</td>
									<td>
										<br />
										<br />
									</td>
								</tr>
								<xsl:apply-templates select="competitorList"/>
							</table>
							<br />
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	
	<xsl:template match="competitorList">
		<tr onmouseover="this.style.backgroundColor='#F4F4F4';" onmouseout="this.style.backgroundColor='#FFFFFF';">
			<td><a href="/apps/gis/index?id={id}"><xsl:value-of select="profileName" /></a></td>
			<td><xsl:value-of select="profileType" /></td>
			<td>{TRANSLATE:last_updated}: <xsl:value-of select="dateUpdated" /> {TRANSLATE:by} <xsl:value-of select="initiator" /></td>
			<td />
			<td><a href="/apps/gis/resume?gis={id}&amp;status=gis">{TRANSLATE:update}</a></td>
<!--			<td><a href="Javascript:if (confirm('Are you sure you wish to delete this competitor? \nThis action is irreversible!'))top.location = '/apps/gis/delete?id={id}';">{TRANSLATE:delete}</a></td>
-->
		</tr>	
	</xsl:template>
	
	

	<xsl:template match="site">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />					
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>	
	
	<xsl:template match="gis_report">
		
		<table width="100%">
			<tr>
				<td>
					<h1 style="margin-bottom: 10px;">ID: <xsl:value-of select="id"/> -  <xsl:value-of select="profileName"/> <xsl:if test="admin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1>
				</td>
			</tr>
		</table>
		
		<xsl:apply-templates select="gisSummary" />

		<xsl:apply-templates select="gisLog" />	
			
	</xsl:template>
	
				
				
				
				
	
	<xsl:template match="gisLog">
	
		<div class="title-box1">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:history} <a href="#" onclick="toggle_display('historyBox'); return toggle_display('openedHistoryBox')"><img src="/images/serviceDesk/toggle2.png" align="center" /></a></p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="openedHistoryBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<xsl:choose>
			
				<xsl:when test="item">
					<xsl:for-each select="item">
						<tr class="valid_row">
							<div id="notificationsLink{logId}">
							<td width="25%" valign="top">
								<xsl:choose>
									<xsl:when test="descriptionLength='long'">
										<a href="#documents" onclick="toggle_display('notificationsLink{logId}'); return toggle_display('openNotificationForm{logId}')"><img src="/images/comment.png" style="margin-right: 10px;" align="left" /></a> <xsl:value-of select="date" />
									</xsl:when>
									<xsl:otherwise>
										<img src="../../images/ccr/report.png" style="margin-right: 10px;" align="left" /> <xsl:value-of select="date" />
									</xsl:otherwise>
								</xsl:choose>							
							</td>
							
							<td width="25%" valign="top"><xsl:value-of select="user" /></td>
							<td width="50%" valign="top"><xsl:value-of select="action" /></td>
							</div>
						</tr>
						<tr id="openNotificationForm{logId}" style="display:none" bgcolor="#F8F8F8">
							<td colspan="2"></td>
							<td width="50%"><xsl:value-of select="description" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>
		</div>
		
	</xsl:template>
	
	<xsl:template match="gisSummary">
	
		<div class="title-box2">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:summary}</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			

			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="dateAdded"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:initiator}</td>
				<td class="valid_row"><xsl:value-of select="initiator"/></td>
			</tr>
			<xsl:choose>
				<xsl:when test="updated='true'">
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:date_updated}</td>
						<td class="valid_row"><xsl:value-of select="dateUpdated"/></td>
					</tr>
					<tr class="valid_row">
						<td class="cell_name" width="28%">{TRANSLATE:updated_by}</td>
						<td class="valid_row"><xsl:value-of select="owner"/></td>
					</tr>
				</xsl:when>
			</xsl:choose>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:report}</td>
				<td class="valid_row">
					<input type="submit" value="View Details" onclick="buttonLink('view?gis={../id}&amp;status=gis')" />	
					<input type="submit" value="Edit" onclick="buttonLink('resume?gis={../id}&amp;status=gis')" />
					
				</td>
			</tr>
			</table>	
		
		<br />		
		
	</xsl:template>
	
	<xsl:template match="addNewSection">
	</xsl:template>
	
</xsl:stylesheet>