<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="comm">
	
	
	</xsl:template>
	
	<xsl:template match="commsHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
					<xsl:choose>
						<xsl:when test="emailSent='true'">
							<div class="green_notification">
								<h1><strong>{TRANSLATE:email_sent_successfully}</strong></h1>
							</div>
						</xsl:when>
						<xsl:when test="emailSent='false'">
							<div class="red_notification">
								<h1><strong>{TRANSLATE:email_not_sent_see_log}</strong></h1>
							</div>
						</xsl:when>
					</xsl:choose>

					<xsl:choose>
						<xsl:when test="comms_report/id">
							<xsl:apply-templates select="comms_report" />	
						</xsl:when>
						<xsl:otherwise>
						<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:comms}</h1>
							<p>{TRANSLATE:comm_info}</p>							
						</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
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
	
	
	
	<xsl:template match="comms_report">
		
		<h1 style="margin-bottom: 10px;">{TRANSLATE:comm_id}: 
		
		<xsl:value-of select="id"/> <xsl:if test="admin='1'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this comm? \nThis action is irreversible!'))top.location = 'tasks?commId={id}&amp;mode=deletecomm';">{TRANSLATE:delete}</a>)</xsl:if></h1>
		
		<xsl:apply-templates select="commsSummary" />

		<xsl:apply-templates select="commsLog" />
			
	</xsl:template>
	
	<xsl:template match="commsLog">
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:history} <a href="#" onclick="toggle_display('historyBox'); return toggle_display('openedHistoryBox')"><img src="/apps/complaints/toggle.gif" align="center" /></a></p>
		</div></div></div></div>

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
	
	<xsl:template match="commsSummary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:comm_date}</td>
				<td class="valid_row"><xsl:value-of select="openDate" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:subject}</td>
				<td class="valid_row"><xsl:value-of select="subject" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:comm_body}</td>
				<td class="valid_row"><xsl:apply-templates select="newsBody" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:comm_tools}</td>
				<td class="valid_row"><a href="viewArticle?id={commId}"><strong>{TRANSLATE:view}</strong></a><xsl:if test="../../commAdmin='true'"> | <a href="resume?commId={commId}"><strong>{TRANSLATE:edit}</strong></a> | </xsl:if>
				
				<xsl:if test="../../commAdmin='true'">
					<xsl:choose>
						<xsl:when test="published='0'">
							<a href="tasks?commId={commId}&amp;task=publish"><strong>{TRANSLATE:publish_news}</strong></a> 
						</xsl:when>
						<xsl:otherwise>
							<a href="tasks?commId={commId}&amp;task=unpublish"><strong>{TRANSLATE:unpublish_news}</strong></a>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				</td>
			</tr>
		</table>
		
		<br />		
		
	</xsl:template>
	
	
	
	<xsl:template match="sections">
		<xsl:apply-templates select="section" /><br />
		<xsl:choose>
			<xsl:when test="admin='true'">
				<input type="submit" value="View" onclick="buttonLink('view?comm={@id}&amp;status=comm')" />	
				<input type="submit" value="Edit" onclick="buttonLink('resume?comm={@id}&amp;status=comm')" />
			</xsl:when>
			<xsl:when test="isOwner='true'">
				<input type="submit" value="View" onclick="buttonLink('view?comm={@id}&amp;status=comm')" />
				<input type="submit" value="Edit" onclick="buttonLink('resume?comm={@id}&amp;status=comm')" />
			</xsl:when>
			<xsl:otherwise>
				<input type="submit" value="View" onclick="buttonLink('view?comm={@id}&amp;status=comm')" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="newsBody">
		<xsl:value-of select="para"/><br />
	</xsl:template>
	
	<xsl:template match="section">
		<xsl:value-of select="text()"/><br />
	</xsl:template>
	
</xsl:stylesheet>