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
							<h1><img src="http://scapanetdev/apps/gis/error_loading_gis.jpg" align="center" /><font color="red">{TRANSLATE:error_loading_gis}</font></h1>
							<p>{TRANSLATE:error_loading_gis_message}</p>
						</xsl:when>
						<xsl:otherwise>
						<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_gis_loaded}</h1>
							<p>{TRANSLATE:gis_info}</p>
						</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="gisComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add a Comment to the gis</p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
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
	
	
	
	<xsl:template match="gis_report">
		
		<h1 style="margin-bottom: 10px;">gis ID: <xsl:value-of select="id"/>  <xsl:value-of select="customerName" /><xsl:if test="admin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1>
		
		<xsl:apply-templates select="summary" />
		
		<xsl:apply-templates select="gisComment" />

		<xsl:apply-templates select="log" />	
			
	</xsl:template>
	
	<xsl:template match="gisComment">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:comments}</p>
		</div></div></div></div>

		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or ../../isOwner='true'">(<a href="gisComments?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this comment? \nThis action is irreversible!'))top.location = 'gisComments?mode=delete&amp;id={id2}';">Delete</a>)</xsl:if></td>
							<td class="valid_row"><strong>Comment:</strong> (Posted By: <xsl:value-of select="user2" />)<br /><br /><xsl:value-of select="comment" /></td>
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
		<br />
		
	</xsl:template>

	
	<xsl:template match="log">
	
		<!--<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:history}</p>
		</div></div></div></div>

		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item">
					<xsl:for-each select="item">
						<tr class="valid_row">
							<td><img src="../../images/ccr/report.png" style="margin-right: 10px;" align="left" /><xsl:value-of select="date" /></td>
							<td><xsl:value-of select="user" /></td>
							<td><xsl:value-of select="action" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none}</td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>-->
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:history} <a href="#" onclick="toggle_display('historyBox'); return toggle_display('openedHistoryBox')"><img src="toggle.gif" align="center" /></a></p>
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
	
	<xsl:template match="summary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_account_number}</td>
				<td class="valid_row"><xsl:value-of select="customerAccountNumber"/></td>
			</tr>		
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_name}</td>
				<td class="valid_row"><xsl:value-of select="customerName"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="dateAdded"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:gis_creator}</td>
				<td class="valid_row"><xsl:value-of select="gisCreator"/></td>
			</tr>				
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:bu}</td>
				<td class="valid_row"><xsl:value-of select="bu"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row"><xsl:apply-templates select="sections" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:waiting_on}</td>
				<td class="valid_row">
				<xsl:choose>
					<xsl:when test="completed='true'">
						<strong>gis Accepted</strong>
					</xsl:when>
					<xsl:when test="completed='rejected'">
						<strong>gis Rejected</strong>
					</xsl:when>
					<xsl:otherwise>
						<strong><xsl:value-of select="gisOwner"/></strong>
					</xsl:otherwise>
				</xsl:choose>
				<!-- Show Add Comment link for both results -->
				<xsl:if test="../isOwner='true' or ../admin='true'"> - (<a href="gisComments?mode=add&amp;gisId={../id}">Add a Comment</a>)</xsl:if>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:initiator_commands}</td>
				<td class="valid_row">
					<a href="gisComments?mode=add&amp;gisId={../id}">Send a Reminder</a> - 
					<a href="takeovergis?id={../id}&amp;mode=takeover">Takeover</a> -
					<a href="delegate?mode=delegate&amp;gisId={../id}">Delegate</a>
				</td>
				
				<!-- a href="takeoverIJF?id={../id}&amp;mode=takeover" -->
			</tr>
		</table>
		
		<br />		
		
	</xsl:template>
	
		<xsl:template match="gisDelegate">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate gis: <xsl:value-of select="gisId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>

	
	<xsl:template match="sections">
		<xsl:apply-templates select="section" /><br />
		<xsl:choose>
			<xsl:when test="admin='true'">
				<input type="submit" value="View" onclick="buttonLink('view?gis={@id}&amp;status=gis')" />	
				<input type="submit" value="Edit" onclick="buttonLink('resume?gis={@id}&amp;status=gis')" />
			</xsl:when>
			<xsl:when test="isOwner='true'">
				<input type="submit" value="View" onclick="buttonLink('view?gis={@id}&amp;status=gis')" />
				<input type="submit" value="Edit" onclick="buttonLink('resume?gis={@id}&amp;status=gis')" />
			</xsl:when>
			<xsl:otherwise>
				<input type="submit" value="View" onclick="buttonLink('view?gis={@id}&amp;status=gis')" />	
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<xsl:template match="section">
		<xsl:value-of select="text()"/><br />
	</xsl:template>
	
	<xsl:template match="reportNav">

		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
			
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left;" src="/images/ccr/report.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('{@item}', 'true');"><xsl:value-of select="@item"/></a>
				</xsl:element>
			</xsl:element>
		</tr>
		
		<xsl:apply-templates select="orderNav" />
		
	</xsl:template>
	
<!--	<xsl:template match="orderControl">

		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:order_options}</p>
		</div></div></div></div>
			
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td style=" border-top: 1px solid #EFEFEF; border-bottom: 1px solid #EFEFEF;">
				
					<table border="0" width="100%">
						<tr>
							<td>{TRANSLATE:add_an_order}</td>
							<td style="text-align: right;"><input type="submit" value="Add" onclick="buttonPress('addorder');" /></td>
						</tr>
						<xsl:if test="@id">
							<tr>
								<td>{TRANSLATE:delete_selected_order}</td>
								<td style="text-align: right;"><input type="submit" value="Delete" onclick="buttonPress('removeorder_{@id}');" /></td>
							</tr>
						</xsl:if>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br />
		
	</xsl:template>
	
	<xsl:template match="orderNav">
	
		<tr>
			<xsl:element name="td">
			
				<xsl:if test="@selected='true'">
					<xsl:attribute name="style">background: #CCCCCC;</xsl:attribute>
				</xsl:if>
				
				<xsl:if test="@valid='false'">					
					<span style="float: right; background: #FF0000; padding: 0 5px 0 5px; color: #FFFFFF; font-weight: bold;">!</span>
				</xsl:if>
				
				<img style="float: left; margin-left: 15px; margin-right: 5px;" src="/images/ccr/material.png" />
				
				<xsl:element name="span">
				
					<xsl:if test="@selected='true'">
						<xsl:attribute name="style">font-weight: bold;</xsl:attribute>
					</xsl:if>
				
					<a href="Javascript:linkFormSubmit('order_{@id}', 'true');">{TRANSLATE:order} <xsl:value-of select="@id+1" /> </a>
				</xsl:element>
			</xsl:element>
		</tr>
	
	</xsl:template>
	-->
	
	
	
</xsl:stylesheet>