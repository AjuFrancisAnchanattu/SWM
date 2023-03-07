<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global2.xsl"/>

	<xsl:template match="complaints">
	
	
	</xsl:template>
	
	<xsl:template match="complaintsHome">

	<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
					
				</td>
	
				<td valign="top" style="padding: 10px;">		

					<xsl:choose>
						<xsl:when test="complaints_report/id">
							<xsl:apply-templates select="complaints_report" />	
						</xsl:when>
						<xsl:when test="notfound='true'">
							<h1><img src="http://scapanetdev/apps/complaints/error_loading_complaint.jpg" align="center" /><font color="red">{TRANSLATE:error_loading_complaint}</font></h1>
							<p>{TRANSLATE:error_loading_complaint_message}</p>
						</xsl:when>
						<xsl:otherwise>
						<div style="background: #DFDFDF; padding: 8px;">
							<h1>{TRANSLATE:no_report_loaded}</h1>
							<p>{TRANSLATE:complaints_info}</p>
							<p>{TRANSLATE:complaint_note}</p>
						</div>
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>
		
	</xsl:template>
	
	<xsl:template match="emailDocument">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Email Document: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintsComments">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Add a Comment to Complaint: <xsl:value-of select="complaintId" /></p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />					
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="editBookmark">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Edit Bookmark</p>
					</div></div></div></div>				
						<xsl:apply-templates select="form" />				
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="ComplaintOffline">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						
						<xsl:apply-templates select="snapin_left" />
					
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">	
				
					<xsl:apply-templates select="error" />	
	
					<h1>Download the offline Complaint Supplier Tool</h1>
					
					<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
	                   <p style="margin: 0; line-height: 15px;"><strong>Beta Testing</strong>. This version is for testing purposes only.</p>
	                </div>
					
					<div style="background: #DFDFDF; padding: 8px; margin: 10px 0 10px 0;">
					Right click and "Save target as" and put the file somewhere you can access when not connected to the network (Desktop for instance):
					
					<ul>
						<li><a href="complaint_offline.html">Download</a> (All languages)</li>
					</ul>
					
					</div>
					
					
					<h1>Instructions</h1>
					
					<div style="background: #DFDFDF; padding: 8px; margin-bottom: 10px;">
					
					<p>To save an offline report:</p>
			
					<ol>
						<li>Click "Save Report"</li>
						<li>Save as type: Text File (*.txt)</li>
						<li>Language: Unicode</li>
						<li>Give the file a useful name</li>
					</ol>
					
					</div>
					
					<h1>Import an offline report</h1>
					
					<table width="100%" cellspacing="0" cellpadding="4">
						<tr>
							<td class="valid_row">
					
								<input type="file" name="offlineFile" />
								
								<input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
						
							</td>
						</tr>
						<tr>
							<td cclass="valid_row" style="text-align: center">
						
								<input type="submit" value="Upload" onclick="buttonPress('upload');" />

							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
		<xsl:template match="complaintsDelegate">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>

				<td valign="top" style="padding: 10px;">		
					<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
						<p>Delegate Complaint: <xsl:value-of select="complaintId" /></p>
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
	
	
	
	<xsl:template match="complaints_report">
		
		<!--table width="100%" cellspacing="0">			
			<tr>
				<td width="28%"></td>
				<td><div align="right"><h1 style="margin-bottom: 10px;"><img src="rss.gif" /></h1></div></td>
			</tr>
		</table>-->
		
		<table width="100%">
			<tr>
				<td><h1 style="margin-bottom: 10px;">Complaint ID: 
				<xsl:choose>
					<xsl:when test="complaint_type='customer_complaint'">
						C<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='hs'">
						HS<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='environment'">
						EV<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='quality'">
						Q<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='supplier_complaint'">
						SC<xsl:value-of select="id"/>
					</xsl:when>
					<xsl:when test="complaint_type='survey_scorecard'">
						SS<xsl:value-of select="id"/>
					</xsl:when>
				</xsl:choose>
				  <xsl:value-of select="customerName" /> <xsl:if test="complaintAdmin='true'"> (<a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'delete?id={id}';">Delete</a>)</xsl:if></h1></td>
				<td><div align="right"><h1 style="margin-bottom: 10px; color: green"><strong>[ CUSTOMER COMPLAINT ]</strong></h1></div></td>
			</tr>
		</table>
		
		<xsl:apply-templates select="complaintsSummary" />
		
		<xsl:apply-templates select="complaintsDocuments" />
		
		<xsl:apply-templates select="complaintsComment" />

		<xsl:apply-templates select="complaintsLog" />	
			
	</xsl:template>
	
	<xsl:template match="complaintsComment">
	
		
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:comments} <a href="#" onclick="toggle_display('commentBox'); return toggle_display('openedCommentBox')"><img src="toggle.gif" align="center" /></a></p>
		</div></div></div></div>
		
		<div id="openedCommentBox">
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
	
			<xsl:choose>
			
				<xsl:when test="item2">
					<xsl:for-each select="item2">
						<tr class="valid_row">
							<td class="cell_name" valign="top" width="20%"><xsl:value-of select="date2" /><br /><xsl:if test="../../admin='true' or editable='true'">(<a href="addComment?mode=edit&amp;id={id2}">Edit</a> - <a href="Javascript:if (confirm('Are you sure you wish to delete this report? \nThis action is irreversible!'))top.location = 'addComment?mode=delete&amp;id={id2}&amp;complaintId={../../id}';">Delete</a>)</xsl:if></td>
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

	
	<xsl:template match="complaintsLog">
	
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
	
	<xsl:template match="complaintsSummary">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:date_added}</td>
				<td class="valid_row"><xsl:value-of select="openDate"/>
				<xsl:choose>
					<xsl:when test="custComplaintStatus='Closed'">
					</xsl:when>
					<xsl:otherwise>
						 - <xsl:value-of select="daysFromCreation"/>
					</xsl:otherwise>
				</xsl:choose>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_complaint_status}</td>
				<td class="valid_row"><strong><xsl:value-of select="custComplaintStatus"/></strong><xsl:if test="custComplaintStatus='Closed'"> - <xsl:value-of select="custComplaintClosedDate"/></xsl:if></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:internal_complaint_status}</td>
				<td class="valid_row"><strong><xsl:value-of select="internalComplaintStatus"/></strong><xsl:if test="internalComplaintStatus='Closed'"> - <xsl:value-of select="internalComplaintClosedDate"/></xsl:if></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:sap_customer_name}</td>
				<td class="valid_row"><xsl:value-of select="sapCustomerName"/> (<xsl:value-of select="sapCustomerNumber"/>)</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_email_address}</td>
				<td class="valid_row"><xsl:value-of select="sapEmailAddress"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:sample_received_by_internal_sales}</td>
				<td class="valid_row"><xsl:value-of select="sampleRecIntSales"/> - <xsl:value-of select="sampleRecIntSalesDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:sample_received_by_process_owner}</td>
				<td class="valid_row"><xsl:value-of select="sampleRecProOwner"/> - <xsl:value-of select="sampleRecProOwnerDate"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:grouped_with_another_complaint}</td>
				<td class="valid_row">
					<xsl:choose>
						<xsl:when test="groupAComplaint='No'">
							<xsl:value-of select="groupAComplaint"/>
						</xsl:when>
						<xsl:when test="complaint_type='customer_complaint'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">C<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="complaint_type='hs'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">HS<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="complaint_type='environment'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">EV<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="complaint_type='quality'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">Q<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="complaint_type='supplier_complaint'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">SC<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
						<xsl:when test="complaint_type='survey_scorecard'">
							<xsl:value-of select="groupAComplaint"/> - <a href="index?id={groupAComplaintId}">SS<xsl:value-of select="groupAComplaintId"/></a>
						</xsl:when>
					</xsl:choose>
				</td>
			</tr>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_complaint_closure}</td>
				<td class="valid_row"><xsl:value-of select="custComplaintClosure"/></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:total_closure}</td>
				<td class="valid_row"><xsl:value-of select="totalClosure"/></td>
			</tr>-->
			<xsl:if test="locked='locked'">
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:locked}</td>
				<td class="valid_row"><img src="/images/pad_lock.gif" align="left" /> <strong>Complaint Locked</strong></td>
			</tr>
			</xsl:if>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row">
				
				<xsl:choose>
					<xsl:when test="complaintStatus='true'">
						<a href="view?complaint={../id}&amp;status=complaint&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=complaint"><strong>View</strong></a><xsl:if test="locked='unlocked'"><xsl:if test="internalComplaintStatus='Open'"> | <a href="resume?complaint={../id}&amp;status=complaint"><strong>Edit</strong></a></xsl:if></xsl:if> Complaint<br />
					</xsl:when>
					<xsl:when test="complaintStatus='false'">
						<a href="resume?complaint={../id}&amp;status=complaint"><strong>Add</strong></a> Complaint<br />
					</xsl:when>
					<xsl:when test="complaintOverallStatus='true'">
						<a href="view?complaint={../id}&amp;status=complaint&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No complaint sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="evaluationStatus='true'">
						<a href="view?complaint={../id}&amp;status=evaluation&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=evaluation"><strong>View</strong></a><xsl:if test="locked='unlocked'"><xsl:if test="internalComplaintStatus='Open'"> | <a href="resume?complaint={../id}&amp;status=evaluation"><strong>Edit</strong></a></xsl:if></xsl:if> Evaluation<br />
					</xsl:when>
					<xsl:when test="evaluationStatus='false'">
						<a href="resume?complaint={../id}&amp;status=evaluation"><strong>Add</strong></a> Evaluation<br />
					</xsl:when>
					<xsl:when test="evaluationOverallStatus='true'">
						<a href="view?complaint={../id}&amp;status=evaluation&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No evaluation sections exist
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:choose>					
					<xsl:when test="conclusionStatus='true'">
						<a href="view?complaint={../id}&amp;status=conclusion&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={../id}&amp;status=conclusion"><strong>View</strong></a><xsl:if test="locked='unlocked'"><xsl:if test="internalComplaintStatus='Open'"> | <a href="resume?complaint={../id}&amp;status=conclusion"><strong>Edit</strong></a></xsl:if></xsl:if> Conclusion<br />
					</xsl:when>
					<xsl:when test="conclusionStatus='false'">
						<a href="resume?complaint={../id}&amp;status=conclusion"><strong>Add</strong></a> Conclusion<br />
					</xsl:when>
					<xsl:when test="conclusionOverallStatus='true'">
						<a href="view?complaint={../id}&amp;status=conclusion&amp;print=1"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> Complaint<br />
					</xsl:when>
					<xsl:otherwise>
						No conclusion sections exist
					</xsl:otherwise>
				</xsl:choose>
					
				</td>
			</tr>
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:process_owner}</td>
				<td class="valid_row"><strong><xsl:value-of select="processOwner"/></strong></td>
			</tr>-->
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:waiting_on_complaint_owner}</td>
				<td class="valid_row"><strong><xsl:value-of select="owner"/></strong></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:complaint_tools}</td>
				<td class="valid_row"><a href="addComment?id={../id}&amp;mode=takeover">Takeover Ownership</a> - <a href="delegate?mode=delegate&amp;complaintId={../id}">Delegate</a> - <a href="addComment?mode=add&amp;complaintId={../id}">Add Comment</a> - <a href="sendReminder?id={../id}">Send A Reminder</a> - <xsl:if test="complaintAdmin='true'"><a href="delegate?mode=reopen&amp;complaintId={../id}">Re-Open</a></xsl:if></td>
			</tr>
			<!--
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:documents}</td>
				<td class="valid_row"><a href="pdf/pdf?id={../id}">Generate 8D Document</a> - <a href="pdf/files/pdf8d{../id}.pdf" target="_blank">View 8D PDF</a></td>
			</tr>-->
			</table>		
		<br />		
		
	</xsl:template>
	
	<xsl:template match="complaintsDocuments">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:documents}</p>
		</div></div></div></div>
		<a name="documents" />
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<!--<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:language_output}</td>
				<td class="valid_row">
				<select name="language">
					<option value="en">English</option>
					<option value="fr">French</option>
					<option value="gr">German</option>
					<option value="it">Italian</option>
				</select>
				</td>
			</tr>-->
			<tr>
				<td colspan="3"><strong>Please select a language to generate by clicking the correct flag.</strong></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:acknowledgement}</td>
				<td class="cell_name" width="10%"><a href="word/generateAcken?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateAckde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateAckfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateAckit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
				<td class="valid_row" width="62%"><xsl:if test="openableAck='true' and typeAck='ack'"><a href="\\dellintranet2\complaintsd\ack-{genLanguageAck}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=ack&amp;complaintId={complaintId}&amp;lang={genLanguageAck}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedAck"/> - Language: <xsl:value-of select="genLanguageAck"/>)</xsl:if></td>
			</tr>

			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:8d}</td>
				<td class="cell_name"><a href="word/generate8den?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generate8dde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generate8dfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generate8dit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
				<td class="valid_row" width="62%"><xsl:if test="openable8d='true' and type8d='8d'"><a href="\\dellintranet2\complaintsd\8d-{genLanguage8d}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=8d&amp;complaintId={complaintId}&amp;lang={genLanguage8d}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGenerated8d"/> - Language: <xsl:value-of select="genLanguage8d"/>)</xsl:if></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:blank_8d}</td>
				<td class="cell_name"><a href="word/generateBlank8d?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /> </a></td>
				<td class="valid_row" width="62%"><xsl:if test="openableblank8d='true' and typeblank8d='blank8d'"><a href="\\dellintranet2\complaintsd\blank8d-en{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=blank8d&amp;complaintId={complaintId}&amp;lang=en">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedblank8d"/> - Language: <xsl:value-of select="genLanguageblank8d"/>)</xsl:if></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:return_form}</td>
				<td class="cell_name"><a href="word/generateReturnFormen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateReturnFormde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a></td>
				<td class="valid_row" width="62%"><xsl:if test="openableReturnForm='true' and typeReturnForm='returnForm'"><a href="\\dellintranet2\complaintsd\returnForm-{genLanguageReturnForm}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=returnForm&amp;complaintId={complaintId}&amp;lang={genLanguageReturnForm}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedReturnForm"/> - Language: <xsl:value-of select="genLanguageReturnForm"/>)</xsl:if></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:disposal_note}</td>
				<td class="cell_name"><a href="word/generateDisposalNoteen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateDisposalNotede?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /> </a></td>
				<td class="valid_row" width="62%"><xsl:if test="openableDisposalNote='true' and typeDisposalNote='disposalNote'"><a href="\\dellintranet2\complaintsd\disposalNote-{genLanguageDisposalNote}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=disposalNote&amp;complaintId={complaintId}&amp;lang={genLanguageDisposalNote}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedDisposalNote"/> - Language: <xsl:value-of select="genLanguageDisposalNote"/>)</xsl:if></td>
			</tr>
			
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:sample_reminder}</td>
				<td class="cell_name"><a href="word/generateSampleReminderen?id={id}"><img src="/apps/complaints/data/english_flag.gif" align="left" alt="Generate UK Document" /></a> <a href="word/generateSampleReminderde?id={id}"><img src="/apps/complaints/data/german_flag.gif" alt="Generate German Document" align="left" /></a> <a href="word/generateSampleReminderfr?id={id}"><img src="/apps/complaints/data/french_flag.gif" alt="Generate French Document" align="left" /></a> <a href="word/generateSampleReminderit?id={id}"><img src="/apps/complaints/data/italian_flag.gif" alt="Generate Italian Document" align="left" /></a></td>
				<td class="valid_row" width="62%"><xsl:if test="openableSampleReminder='true' and typeSampleReminder='sampleRem'"><a href="\\dellintranet2\complaintsd\sampleRem-{genLanguageSampleReminder}{complaintId}.rtf" target="_blank">Open</a> - <a href="sendDocEmail?mode=newEmail&amp;type=sampleRem&amp;complaintId={complaintId}&amp;lang={genLanguageSampleReminder}">Email Document</a> - (Last Generated: <xsl:value-of select="dateGeneratedSampleReminder"/> - Language: <xsl:value-of select="genLanguageSampleReminder"/>)</xsl:if></td>
			</tr>
			
			</table>		
			
		<br />		
		
		
				
		
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
	
	<xsl:template match="orderControl">

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
	
</xsl:stylesheet>