<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="appraisal">
	
	
	</xsl:template>
	
	<xsl:template match="appraisalHome">

	<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">
					
					<xsl:if test="emailSent='true'">
						<div class="green_notification">
							<h1><strong>{TRANSLATE:email_sent_successfully}</strong></h1>
						</div>
					</xsl:if>
					
					<xsl:if test="logout='true'">
						<div class="green_notification">
							<h1><strong>{TRANSLATE:logged_out_successfully}</strong><br /></h1>
							<p><a href="http://scapaconnect/Apps/MyPerformance/Default.aspx">{TRANSLATE:log_back_in}<br /></a></p>
						</div>
					</xsl:if>
					
					<xsl:if test="login='false'">
						<div class="red_notification">
							<h1><strong>{TRANSLATE:login_failed_try_again}</strong></h1>
						</div>
					</xsl:if>

					<xsl:choose>
						<xsl:when test="appraisal_report/id">
							<xsl:apply-templates select="appraisal_report" />	
						</xsl:when>
						<xsl:otherwise>
						<!--<div style="background: #DFDFDF; padding: 8px;">-->
							<!--<h1>{TRANSLATE:back_to_scapa_homepage}</h1>-->
							<!--<p>{TRANSLATE:appraisal_info}</p>
							<p>{TRANSLATE:appraisal_note}</p>-->
						<!--</div>-->
						<!--<br />-->
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
	<xsl:template match="appraisalComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add a Comment to appraisal: <xsl:value-of select="appraisalId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="appraisalDelegate">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate appraisal: <xsl:value-of select="appraisalId" /></p>
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
	
	
	
	<xsl:template match="appraisal_report">
		
		<table width="100%">
			<tr>
				<td><h1 style="margin-bottom: 10px;">
					Appraisal For: <xsl:value-of select="name" /> <xsl:if test="appraisalAdmin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1></td>
			</tr>
		</table>
		
		<xsl:apply-templates select="appraisalSummary" />
		
		<xsl:apply-templates select="appraisalComment" />

		<xsl:apply-templates select="appraisalLog" />	
			
	</xsl:template>
	
	<xsl:template match="appraisalComment">
	
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:comments} <a href="#" onclick="toggle_display('commentBox'); return toggle_display('openedCommentBox')"><img src="toggle.gif" align="center" /></a></p>
		</div></div></div></div>
		
		<div id="openedCommentBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or editable='true'">(<a href="addComment?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'addComment?mode=delete&amp;id={id2}&amp;appraisalId={../../id}';">Delete</a>)</xsl:if></td>
							<td class="valid_row"><strong>Comment: <xsl:value-of select="id2" /></strong> (Posted By: <xsl:value-of select="user2" />)<br /><br /><xsl:value-of select="comment" /></td>
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
		<br />
		
	</xsl:template>

	
	<xsl:template match="appraisalLog">
	
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
	
	<xsl:template match="appraisalSummary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="openDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row">
				
				<xsl:choose>
					<xsl:when test="appraisalStatus='true'">
						<a href="view?appraisal={../id}&amp;status=appraisal"><strong>View</strong></a> | <a href="resume?appraisal={../id}&amp;status=appraisal"><strong>Edit</strong></a> Appraisal<br />
					</xsl:when>
					<xsl:when test="appraisalStatus='false'">
						<a href="resume?appraisal={../id}&amp;status=appraisal"><strong>Add</strong></a> Appraisal<br />
					</xsl:when>
					<xsl:otherwise>
						No Appraisal sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="reviewStatus='true'">
						<a href="view?appraisal={../id}&amp;status=review"><strong>View</strong></a> | <a href="resume?appraisal={../id}&amp;status=review"><strong>Edit</strong></a> Review<br />
					</xsl:when>
					<xsl:when test="reviewStatus='false'">
						<a href="resume?appraisal={../id}&amp;status=review"><strong>Add</strong></a> Review<br />
					</xsl:when>
					<xsl:otherwise>
						No review sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="developmentStatus='true'">
						<a href="view?appraisal={../id}&amp;status=development"><strong>View</strong></a> | <a href="resume?appraisal={../id}&amp;status=development"><strong>Edit</strong></a> Development<br />
					</xsl:when>
					<xsl:when test="developmentStatus='false'">
						<a href="resume?appraisal={../id}&amp;status=development"><strong>Add</strong></a> Development<br />
					</xsl:when>
					<xsl:otherwise>
						No development sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="trainingStatus='true'">
						<a href="view?appraisal={../id}&amp;status=training"><strong>View</strong></a> | <a href="resume?appraisal={../id}&amp;status=training"><strong>Edit</strong></a> Training<br />
					</xsl:when>
					<xsl:when test="trainingStatus='false'">
						<a href="resume?appraisal={../id}&amp;status=training"><strong>Add</strong></a> Training<br />
					</xsl:when>
					<xsl:otherwise>
						No training sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="relationshipsStatus='true'">
						<a href="view?appraisal={../id}&amp;status=relationships"><strong>View</strong></a> | <a href="resume?appraisal={../id}&amp;status=relationships"><strong>Edit</strong></a> Relationships<br />
					</xsl:when>
					<xsl:when test="relationshipsStatus='false'">
						<a href="resume?appraisal={../id}&amp;status=relationships"><strong>Add</strong></a> Relationships<br />
					</xsl:when>
					<xsl:otherwise>
						No relationships sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				
				
					<!--<a href="view2?appraisal={../id}&amp;status=development&amp;print=1&amp;printAll=1" target="_blank"><strong>Print All</strong></a>-->
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:waiting_on_appraisal_owner}</td>
				<td class="valid_row"><strong><xsl:value-of select="owner"/></strong></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:appraisal_tools}</td>
				<td class="valid_row"><img src="/images/adobereader.jpg" hspace="5" /> <a href="Javascript:if (confirm('Have all sections been completed?\n\nIf all sections are not complete you may end up with an incomplete PDF.\n\nClick OK to ignore this message and generate the PDF, otherwise click CANCEL.'))top.location = 'generateAppraisal?id={../id}';">Generate/Open Appraisal (PDF)</a> - <a href="generateAppraisal?id={../id}&amp;mode=email">Email Appraisal to HR</a></td>
			</tr>
			</table>	
		
		<br />		
		
	</xsl:template>
	
</xsl:stylesheet>