<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="DocManHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
					<xsl:choose>
						<xsl:when test="DocMan_report/id/">
							<xsl:apply-templates select="DocMan_report" />
						</xsl:when>
						<xsl:otherwise>
						
						<div style="background: #DFDFDF; padding: 8px;">
						<h1>Document Management System</h1>	
						</div>					
						
						
									
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	
	<xsl:template match="DocMan_report">
		
		<table width="100%"><tr><td><h1 style="margin-bottom: 10px;">Document Name: <xsl:value-of select="docName"/><xsl:if test="admin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1></td><td><div align="right"></div></td></tr></table>
		
		<xsl:apply-templates select="DocMan_summary" />

		<xsl:apply-templates select="DocMan_comments" />
		
		<xsl:apply-templates select="DocMan_log" />	
			
	</xsl:template>
	
	<xsl:template match="DocMan_summary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:quick_find_id}</td>
				<td class="valid_row"><xsl:value-of select="id"/></td>
			</tr>	
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:docManDate}</td>
				<td class="valid_row"><xsl:value-of select="docManDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:docName}</td>
				<td class="valid_row"><xsl:value-of select="documentName"/></td>
			</tr>			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:creator}</td>
				<td class="valid_row"><xsl:value-of select="creator"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:intranet_server}</td>
				<td class="valid_row"><xsl:value-of select="intranet_server"/></td>
			</tr>	
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:server_path}</td>
				<td class="valid_row"><xsl:value-of select="server_path"/> [<a href="{server_path}" target="_blank">Open Document</a>]</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:document_owner}</td>
				<td class="valid_row"><xsl:value-of select="owner"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:description}</td>
				<td class="valid_row"><xsl:value-of select="description"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:edit_document}</td>
				<td class="valid_row"><xsl:if test="../isOwner='true' or ../admin='true'"><input type="submit" value="Edit Document" onclick="buttonLink('resume?docman={id}&amp;status=docman')" /></xsl:if></td>
			</tr>
			</table>		
		<br />		
		
	</xsl:template>
	
	<xsl:template match="DocMan_comments">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:comments}</p>
		</div></div></div></div>

		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or ../../isOwner='true'">(<a href="slobComments?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'slobComments?mode=delete&amp;id={id2}';">Delete</a>)</xsl:if></td>
							<td class="valid_row"><strong>Comment:</strong> (Posted By: <xsl:value-of select="user2" />)<br /><br /><xsl:value-of select="comment" /></td>
						</tr>
					</xsl:for-each>
				</xsl:when>
				
				<xsl:otherwise>
					<tr>
						<td class="valid_row">{TRANSLATE:none} - <a href="comment?mode=add?docId={../id}">Add Comment</a></td>
					</tr>
				</xsl:otherwise>
				
			</xsl:choose>

		</table>
		<br />
		
	</xsl:template>
	
	<xsl:template match="DocMan_log">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
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

		</table>
		
		
		
	</xsl:template>
	
</xsl:stylesheet>