<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	<xsl:include href="dashboards.xsl"/>

	<xsl:template match="ccHome">	

		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/summary.js">-</script>
		<link rel="stylesheet" href="/apps/customerComplaints/css/summary.css"/>
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		
		<script>
			LightBox.add( "summary_customerComplaints", 
				{ 	
					blockBelow : true,	//not blocking anything below
					width: 610,
					height: 360,
					border : "special",
					draggable : false
				}
			);
			
			if( window.location.hostname == "10.1.10.6" || window.location.hostname == "10.1.50.11" )
			{
				window.location = "http://scapanet/apps/customerComplaints/index?";
			}
		</script>
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">
					
					<xsl:choose>
						<xsl:when test="wrongComplaintNo"> 
						
							<div style="background: #DFDFDF; padding: 8px;">
	
								<h1>{TRANSLATE:no_report_found}</h1>
						
							</div>
							
							<xsl:apply-templates select="ccCharts" />
							
						</xsl:when>
						<xsl:when test="noComplaintNo"> 
						
							<div style="background: #DFDFDF; padding: 8px;">
	
								<h1>{TRANSLATE:no_report_loaded}</h1>
						
							</div>
							
							<xsl:apply-templates select="ccCharts" />
							
						</xsl:when>
						<xsl:when test="complaintDeleted"> 
						
							<div style="background: #DFDFDF; padding: 8px;">
								
								<h1>{TRANSLATE:complaint_deleted}</h1>
								
							</div>
							
							<xsl:apply-templates select="ccCharts" />
							
						</xsl:when>
						<xsl:otherwise>
						
							<xsl:if test="pdfEmailSent">
								<div style="background: #CCFFE7; padding: 8px;">
								
									<h1>{TRANSLATE:pdf_email_sent}</h1>
									
								</div>
							</xsl:if>
						
							<xsl:apply-templates select="ccSummary" />
							
							<xsl:apply-templates select="ccDocuments" />
							
							<xsl:apply-templates select="ccComments" />
							
							<xsl:apply-templates select="ccLog" />
							
						</xsl:otherwise>
					</xsl:choose>
					
				</td>
			</tr>
		</table>		
	</xsl:template>
	
	<xsl:template match="ccSummary">
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<img src="../../images/famIcons/report.png" alt="" class="titleIcon" />
					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:summary}</p>
				</div></div>
			</div></div></div></div>
		</div>
				
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:complaint_id}</td>
				<td class="valid_row"><xsl:value-of select="complaintId" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_complaint_date}</td>
				<td class="valid_row"><xsl:value-of select="createdDate" /> ({TRANSLATE:days_open}: <xsl:value-of select="daysOpen" />)</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:complaint_creator}</td>
				<td class="valid_row"><xsl:value-of select="createdBy" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:complaint_status}</td>
				<td class="valid_row">
					<table cellspacing="0" id="complaintStatus">
						<tr>
							<td class="fieldName">{TRANSLATE:complaint_status}:</td>
							<td style="font-weight: bold;">
								<xsl:value-of select="complaintStatus" />
							</td>
						</tr>
						<tr>
							<td class="fieldName" style="font-style: italic;">{TRANSLATE:complaint_validated}:</td>
							<td>
								<xsl:value-of select="complaintValidationStatus" />
							</td>
						</tr>
						<tr>
							<td class="fieldName" style="font-style: italic;">{TRANSLATE:corrective_action_complete}:</td>
							<td>
								<xsl:value-of select="correctiveActionStatus" />
							</td>
						</tr>
						<tr>
							<td class="fieldName" style="font-style: italic;">{TRANSLATE:validation_verification_complete}:</td>
							<td>
								<xsl:value-of select="validationVerificationStatus" />
							</td>
						</tr>
						<tr>
							<td class="fieldName" style="font-style: italic;">{TRANSLATE:credit_authorisation}:</td>
							<td>
								<xsl:value-of select="creditAuthorisationStatus" />
							</td>
						</tr>
					</table>
					
				</td>				
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:customer_name}</td>
				<td class="valid_row">
					<xsl:choose>
						<xsl:when test = "sapCustomerNo">
							<a href="javascript: openComplaintsPopup({sapCustomerNo})"> 
								<xsl:value-of select="sapCustomerNo" /> - <xsl:value-of select="sapCustomerName" />
								<img src="../../images/famIcons/application_view_list.png" style="margin: 0px 0px -4px 6px;" alt="View All Complaints"/> 
							</a>
							
							<script>
								function openComplaintsPopup( id)
								{
									//set address of page to display in lightBox					
									LightBox.summary_customerComplaints.setURL( "http://" + window.location.hostname + "/apps/customerComplaints/customerPopup/customerPopup");
									LightBox.summary_customerComplaints.setPOST( "sapNumber=" + id );
									
									//set icon to display/hide icon on event
									LightBox.summary_customerComplaints.showMiddle();
								}	
							</script>
						</xsl:when>
						<xsl:otherwise>
							N/A
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:grouped_with_another_complaint}</td>
				<td class="valid_row">
					<xsl:choose>
						<xsl:when test="groupComplaint">
							<a href="javascript: openGroupedComplaintsPopup({complaintId})"> 
								{TRANSLATE:yes}<img src="../../images/famIcons/application_view_list.png" style="margin: 0px 0px -4px 6px;" alt="View All Complaints"/> 
							</a>
							
							<script>
								function openGroupedComplaintsPopup(id)
								{
									//set address of page to display in lightBox					
									LightBox.summary_customerComplaints.setURL( "http://" + window.location.hostname + "/apps/customerComplaints/groupedComplaintsPopup/groupedComplaintsPopup");
									LightBox.summary_customerComplaints.setPOST( "complaintId=" + id );
									
									//set icon to display/hide icon on event
									LightBox.summary_customerComplaints.showMiddle();
								}
							</script>
						</xsl:when>
						<xsl:otherwise>
							{TRANSLATE:no}
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:problem_description}</td>
				<td class="valid_row"><xsl:apply-templates select="problemDescription" /></td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:saved_invoices}</td>
				<td class="valid_row">
					
					<xsl:choose>
						<xsl:when test="savedInvoices/numberOfInvoices=0">
							N/A
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates select="savedInvoices" />
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%" valign="top">{TRANSLATE:available_reports}</td>
				<td class="valid_row">
					<xsl:apply-templates select="availableReports" />
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">
					{TRANSLATE:complaint_conclusion_owner}
				</td>
				<td class="valid_row">
					<strong>
						<xsl:value-of select="complaintOwner" />
					</strong>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">
					{TRANSLATE:evaluation_owner}
				</td>
				<td class="valid_row">
					<strong>
						<xsl:value-of select="evaluationOwner" />
					</strong>
				</td>
			</tr>
			<tr class="valid_row">
				<td class="cell_name" width="28%">{TRANSLATE:complaint_tools}</td>
				<td class="valid_row">
					<xsl:apply-templates select="complaintTool"/>
				</td>
			</tr>
		</table>
		
		<br />		
	</xsl:template>	

	
	<xsl:template match="complaintTool">
	
		<xsl:choose>
			<xsl:when test="../complaintStatus='Closed'">
				
				<a style="display: block; float: left;" href="addComment?complaintId={../complaintId}">
					<img src="../../images/famIcons/comment_add.png" alt="" style="float: left; margin-right: 3px;" />
					{TRANSLATE:add_comment}
				</a>	
			
				<xsl:if test="../userIsAdmin">
					<a style="display: block; float: left; margin-left: 20px;" href="reopen?complaintId={../complaintId}">
						<img src="../../images/famIcons/book_open.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:reopen}
					</a>									
				</xsl:if>
			
			</xsl:when>
			<xsl:otherwise>
				
				<a style="display: block; float: left;" href="takeover?complaintId={../complaintId}">
					<img src="../../images/famIcons/user_suit.png" alt="" style="float: left; margin-right: 3px;" />
					{TRANSLATE:takeover}
				</a>
								
				<a style="display: block; float: left; margin-left: 20px;" href="delegate?complaintId={../complaintId}&amp;action=takeover">
					<img src="../../images/famIcons/user_go.png" alt="" style="float: left; margin-right: 3px;" />
					{TRANSLATE:delegate}
				</a>	
				
				<a style="display: block; float: left; margin-left: 20px;" href="addComment?complaintId={../complaintId}">
					<img src="../../images/famIcons/comment_add.png" alt="" style="float: left; margin-right: 3px;" />
					{TRANSLATE:add_comment}
				</a>	
					
				<a style="display: block; float: left; margin-left: 20px;" href="reminder?complaintId={../complaintId}">
					<img src="../../images/famIcons/bell.png" alt="" style="float: left; margin-right: 3px;" />
					{TRANSLATE:send_a_reminder}
				</a>
				
				<xsl:if test="../userIsAdmin">
					<a style="display: block; float: left; margin-left: 20px;" onClick="confirmDelete({../complaintId});" href="#">
						<img src="../../images/famIcons/delete.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:delete}
					</a>									
				</xsl:if>
				
			</xsl:otherwise>								
		</xsl:choose>
	
	</xsl:template>
	
	
	<xsl:template match="availableReports">
		<table class="availableReports" cellspacing="0">
			<tr>
				<td class="availableReportsFieldName">{TRANSLATE:complaint}:</td>
				
				<xsl:if test="complaintView">
					<td>
						<a href="view?complaintId={../complaintId}&amp;stage=complaint" style="display: block; float: left;">
							<img src="../../images/famIcons/magnifier.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:view}
						</a>
					</td>
					<xsl:if test="not(complaintAll)">
						<td/>
						<td/>
					</xsl:if>
				</xsl:if>
				
				<xsl:if test="complaintAll">
					<td>
						<a href="edit?complaintId={../complaintId}&amp;stage=complaint" style="display: block; float: left;">
							<img src="../../images/famIcons/application_form_edit.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:edit}
						</a>
					</td>
					<td/>
				</xsl:if>
				
				<xsl:if test="complaintLocked">
					<td class="lock" colspan="3">
						<img src="../../images/famIcons/lock.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:locked_for} <xsl:value-of select="complaintLocked" />
					</td>
				</xsl:if>
				
				<xsl:if test="complaintNone">
					<td>
						<img src="../../images/famIcons/cross.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:none}
					</td>
					<td/>
					<td/>
				</xsl:if>
			</tr>
			
			
			<tr>
				<td class="availableReportsFieldName">{TRANSLATE:evaluation}:</td>
				
				<xsl:if test="evaluationView">
					<td>
						<a href="view?complaintId={../complaintId}&amp;stage=evaluation" style="display: block; float: left;">
							<img src="../../images/famIcons/magnifier.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:view}
						</a>
					</td>
					<xsl:if test="not(evaluationAll)">
						<td/>
						<td/>
					</xsl:if>
				</xsl:if>
				
				<xsl:if test="evaluationAll">
					<td>
						<a href="edit?complaintId={../complaintId}&amp;stage=evaluation" style="display: block; float: left;">
							<img src="../../images/famIcons/application_form_edit.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:edit}
						</a>
					</td>
					<td/>
				</xsl:if>
				
				<xsl:if test="evaluationNone">
					<td>
						<img src="../../images/famIcons/cross.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:none}
					</td>
					<td/>
					<td/>
				</xsl:if>
				
				<xsl:if test="evaluationLocked">
					<td class="lock" colspan="3">
						<img src="../../images/famIcons/lock.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:locked_for} <xsl:value-of select="evaluationLocked" />
					</td>
				</xsl:if>
				
				<xsl:if test="evaluationAdd">
					<td>
						<a href="add?complaintId={../complaintId}&amp;stage=evaluation" style="display: block; float: left;">
							<img src="../../images/famIcons/add.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:add}
						</a>
					</td>
					<td/>
					<td/>
				</xsl:if>
			</tr>

			
			<tr>
				<td class="availableReportsFieldName">{TRANSLATE:conclusion}:</td>
				
				<xsl:if test="conclusionView">
					<td>
						<a href="view?complaintId={../complaintId}&amp;stage=conclusion" style="display: block; float: left;">
							<img src="../../images/famIcons/magnifier.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:view}
						</a>
					</td>
					<xsl:if test="not(conclusionAll)">
						<td/>
						<td/>
					</xsl:if>
				</xsl:if>
				
				<xsl:if test="conclusionAll">
					<td>
						<a href="edit?complaintId={../complaintId}&amp;stage=conclusion" style="display: block; float: left;">
							<img src="../../images/famIcons/application_form_edit.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:edit}
						</a>
					</td>
					<td/>
				</xsl:if>
				
				<xsl:if test="conclusionNone">
					<td>
						<img src="../../images/famIcons/cross.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:none}
					</td>
					<td/>
					<td/>
				</xsl:if>
				
				<xsl:if test="conclusionLocked">
					<td class="lock" colspan="3">
						<img src="../../images/famIcons/lock.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:locked_for} <xsl:value-of select="conclusionLocked" />
					</td>
				</xsl:if>
				
				<xsl:if test="conclusionAdd">
					<td>
						<a href="add?complaintId={../complaintId}&amp;stage=conclusion" style="display: block; float: left;">
							<img src="../../images/famIcons/add.png" alt="" style="float: left; margin-right: 3px;" />
							{TRANSLATE:add}
						</a>
					</td>
					<td/>
					<td/>
				</xsl:if>
			</tr>
				
		</table>
	</xsl:template>
	
	
	<xsl:template match="ccComments">			
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<img src="../../images/famIcons/comments.png" alt="" class="titleIcon" />
					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
						{TRANSLATE:comments}
					</p>
				</div></div>
			</div></div></div></div>
		</div>
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			
			<xsl:choose>
				<xsl:when test="noComments='true'">
					<tr class="valid_row">
						<td>none</td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="comment"/>
				</xsl:otherwise>
			</xsl:choose>
		</table>
		
		<br />			
	</xsl:template>
	
	
	<xsl:template match="ccDocuments">		
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<img src="../../images/famIcons/folder.png" alt="" class="titleIcon" />
					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
						{TRANSLATE:documents}
					</p>
				</div></div>
			</div></div></div></div>
		</div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">			
			
			<tr class="valid_row">
				<td class="cell_name" valign="top">
					<span style="font-size:1.1em; font-weight: bold;">{TRANSLATE:document_type}</span>
				</td>
				<td class="cell_name" valign="top">
					<span style="font-size:1.1em; font-weight: bold;">{TRANSLATE:new_documents}</span>
					<br/>
					<span style="font-size:0.9em;">{TRANSLATE:click_language}</span>
				</td>
				<td class="cell_name" valign="top">
					<span style="font-size:1.1em; font-weight: bold;">{TRANSLATE:existing_documents}</span>
				</td>
			</tr>
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">Acknowledgement</xsl:with-param>
				</xsl:call-template>
			</tr>
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">8D</xsl:with-param>
				</xsl:call-template>
			</tr>
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">Root_Cause_Corrective_Action</xsl:with-param>
				</xsl:call-template>
			</tr>
			
			<!-- COMMENTED OUT RETURN FORM (27/04/2011 DG)
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">Return_Request</xsl:with-param>
				</xsl:call-template>
			</tr>
			-->
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">Disposal_Note</xsl:with-param>
				</xsl:call-template>
			</tr>
			
			<tr class="valid_row">
				<xsl:call-template name="pdfDetails">
					<xsl:with-param name="pdfType">Sample_Reminder</xsl:with-param>
				</xsl:call-template>
			</tr>
			
		</table>
		
		<br />
	</xsl:template>
	
	<xsl:template name="pdfDetails">
		
		<xsl:param name="pdfTag">pdf_<xsl:value-of select="$pdfType"/></xsl:param>
		
		<td class="cell_name"  style="width: 28%;">{TRANSLATE:<xsl:value-of select="$pdfType"/>}</td>
		<td class="cell_name"  style="width: 25%;">
			<a href="editPDF?complaintId={../complaintId}&amp;pdfType={$pdfType}&amp;lang=EN">
				<img src="../../images/famFlags/gb.png" alt="" style="float: left; margin-right: 6px;" />
			</a>
			
			<a href="editPDF?complaintId={../complaintId}&amp;pdfType={$pdfType}&amp;lang=DE">
				<img src="../../images/famFlags/de.png" alt="" style="float: left; margin-right: 6px;" />
			</a>
			
			<a href="editPDF?complaintId={../complaintId}&amp;pdfType={$pdfType}&amp;lang=FR">
				<img src="../../images/famFlags/fr.png" alt="" style="float: left; margin-right: 6px;" />
			</a>
			
			<a href="editPDF?complaintId={../complaintId}&amp;pdfType={$pdfType}&amp;lang=ITA">
				<img src="../../images/famFlags/it.png" alt="" style="float: left; margin-right: 6px;" />
			</a>
		</td>
		
		<td class="valid_row">
			<xsl:if test="*[name()=$pdfTag]">
			
				<xsl:param name="pdfLink"><xsl:value-of select="*[name()=$pdfTag]/pdfLink"/></xsl:param>
				<xsl:param name="pdfGen"><xsl:value-of select="*[name()=$pdfTag]/pdfGen"/></xsl:param>
				<xsl:param name="pdfLang"><xsl:value-of select="*[name()=$pdfTag]/pdfLang"/></xsl:param>
				
				<div style="float: left; min-width: 210px;">
					<a href="{$pdfLink}"  style="display: block; float: left; margin-right: 20px;" target="_blank">
						<img src="../../images/famIcons/page_white_acrobat.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:open}
					</a>
					<a href="/apps/customerComplaints/emailPDF?complaintId={../complaintId}&amp;pdfType={$pdfType}&amp;lang={$pdfLang}" style="display: block; float: left; margin-right: 20px;">
						<img src="../../images/famIcons/email.png" alt="" style="float: left; margin-right: 3px;" />
						{TRANSLATE:email_document}
					</a>
				</div>
				<div href="#"  style="display: block; float: left; padding: 0; min-width: 250px;">
					({TRANSLATE:last_generated}: <xsl:value-of select="$pdfGen" />, {TRANSLATE:language}: {TRANSLATE:<xsl:value-of select="$pdfLang" />})
				</div>
			</xsl:if>
		</td>
	
	</xsl:template>
	
	
	<xsl:template match="comment">			
		<tr class="valid_row">
			<td class="cell_name" style="width: 28%; vertical-align: top;"><strong><xsl:value-of select="./commentPostedBy" /></strong><br /><xsl:value-of select="./commentDate" /></td>
			<td class="valid_row"><xsl:apply-templates select="commentDescription"/></td>
		</tr>			
	</xsl:template>
	
	
	<xsl:template match="log">
		<xsl:element name="tr">
			<xsl:attribute name="class">valid_row</xsl:attribute>
			<xsl:if test="logComment != '' or changes">
				<xsl:attribute name="myId"><xsl:value-of select="logId"/></xsl:attribute>
			</xsl:if>
			
			
			<td width="37px" valign="center">
				<xsl:choose>
				
					<xsl:when test="(logComment != '') or changes">
						<a onclick="toggleDetails('logComment_{logId}','logImage_{logId}');">
							<img 	
								id="logImage_{logId}" 
								src="../../images/dTree/plus.png" 
								style="float: left; margin-right: 6px; cursor: hand;" 
							/>
						</a>
					</xsl:when>
					
					<xsl:otherwise>
						<p style="visibility: hidden;">.</p>
					</xsl:otherwise>
					
				</xsl:choose>							
			</td>
			
			<td class="valid_row logDate" style="width: 28%;"><xsl:value-of select="./logDate" /></td>
			<td class="valid_row" style="width: 25%;"><xsl:value-of select="./loggedBy" /></td>
			<td class="valid_row" style="width: 47%;"><xsl:value-of select="./logAction" /></td>
			
		</xsl:element>
		
		<xsl:if test="logComment != '' or changes">
		
			<tr class="valid_row" id="logComment_{logId}" name="logComment" style="display: none;">
				<td class="valid_row" colspan="4" style="padding-left: 30px; background: #f2f2f2;">
				
					<xsl:if test="logComment">
						<div style="font-size: 1em; padding: 5px 6px;">
							<xsl:apply-templates select="logComment" />
						</div>
					</xsl:if>
					
					<xsl:if test="logComment != '' and changes">
						<hr/>
					</xsl:if>
					
					<xsl:apply-templates select="changes" />
					
				</td>
			</tr>
			
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="logComment">
		<xsl:apply-templates select="para" />
	</xsl:template>
	
	<xsl:template match="changes">
		<div style="font-size: 1em; padding: 5px 6px;">
			{TRANSLATE:changes_to_form}:
		</div>
		
		
		<table id="changesTable" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<th class="left">
					{TRANSLATE:field_name}
				</th>
				<th class="middle">
					{TRANSLATE:old_values}
				</th>
				<th class="right">
					{TRANSLATE:new_values}
				</th>
			</tr>
			
			<xsl:for-each select="field">
				<xsl:choose>
					<xsl:when test="(field_name = 'sap_item_number_added') or (field_name = 'sap_return_number_added')">
						<tr onMouseOver="this.className = 'highlight';" onMouseOut="this.className = '';">
							<td class="left tdHeader">
								{TRANSLATE:<xsl:value-of select="field_name" />}
							</td>
							<td class="right" colspan="2" align="center" valign="top">
								<xsl:value-of select="old_value" />
							</td>
						</tr>
					</xsl:when>
					<xsl:otherwise>
						<tr onMouseOver="this.className = 'highlight';" onMouseOut="this.className = '';">
							<td class="left">
								{TRANSLATE:<xsl:value-of select="field_name" />}
							</td>
							<td class="middle" align="left" valign="top">
								<xsl:value-of select="old_value" />
							</td>
							<td class="right" align="left" valign="top">
								<xsl:value-of select="new_value" />
							</td>
						</tr>
					</xsl:otherwise>
				</xsl:choose>
				
			</xsl:for-each>
			
		</table>
	</xsl:template>
	
	
	<xsl:template match="ccLog">
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner" style="padding: 5px 10px 6px 10px;"><div class="wrapper" >
				
					<a onclick="toggleLog();return false;" style="float: right; color: #ffffff;">
						<img 	
							id="toggleLogImage" 
							myDisplay="none"
							src="../../images/dTree/plus_white.png" 
							style="margin-right: 6px; cursor: hand;" 
						/>
					</a>
					<div style="float: left; width: 300px; margin-top: 1px;">
						<img src="../../images/famIcons/application_view_list.png" alt="" class="titleIcon" />
						<p style="margin: 00; font-weight: bold; color: #FFFFFF;">
							{TRANSLATE:Log}
						</p>
					</div>
					
				</div></div>
			</div></div></div></div>
		</div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented" id="logTable">			
			
			<xsl:choose>
				<xsl:when test="noLogs='true'">
					<tr class="valid_row">
						<td>N/A</td>
					</tr>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="log"/>
				</xsl:otherwise>
			</xsl:choose>
			
		</table>
		
		<br />
	</xsl:template>		
	
	
	<xsl:template match="commentDescription">
		<xsl:apply-templates select="para" />
	</xsl:template>	
	
	
	<xsl:template match="savedInvoices">
		<div id="invoices">
			<xsl:for-each select="invoice">
				
				<div class="invoiceToggle">
					<a onClick="toggleDetails('invoiceRow_{@id}','img_{@id}'); return false;">
						<img src="../../images/dTree/plus.png" alt="" id="img_{@id}"/>
						<xsl:value-of select="@id"/>
					</a>
				</div>
				
				<div id="invoiceRow_{@id}" class="invoice" style="display: none;">
					
					<xsl:for-each select="invoiceRow">
						
						<table>
							<tr>
								<td class="dataTitle" style="border-bottom: 1px solid #aaa;">{TRANSLATE:despatch_date}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="despatchDate"/></td>
								<td class="dataTitle" style="border-bottom: 1px solid #aaa; border-left: 1px solid #aaa;">{TRANSLATE:batch_number}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="batch"/></td>
							</tr>
							
							<tr>
								<td class="dataTitle" style="border-bottom: 1px solid #aaa;">{TRANSLATE:delivery_number}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="deliveryNo"/></td>								
								<td class="dataTitle" style="border-bottom: 1px solid #aaa; border-left: 1px solid #aaa;">{TRANSLATE:material_group}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="materialGroup"/></td>
							</tr>
							
							<tr>
								<td class="dataTitle" style="border-bottom: 1px solid #aaa;">{TRANSLATE:material}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="material"/></td>
								<td class="dataTitle" style="border-bottom: 1px solid #aaa; border-left: 1px solid #aaa;">{TRANSLATE:INVOICE_VALUE_COMPLAINT}:</td>
								<td class="data" style="border-bottom: 1px solid #aaa;"><xsl:value-of select="netValueItem"/></td>
							</tr>
							
							<tr>
								<td class="dataTitle">{TRANSLATE:quantity}:</td>
								<td class="data"><xsl:value-of select="deliveryQuantity"/></td>
								<td class="dataTitle" style="border-left: 1px solid #aaa;">{TRANSLATE:invoice_value}:</td>
								<td class="data"><xsl:value-of select="netValueItemTotal"/></td>
							</tr>
							
							<tr>
								<td class="dataTitle" colspan="4" style="border-top: 1px solid #aaa;"><div style="margin-bottom: -4px;">{TRANSLATE:material_description}:</div><br />
									<xsl:value-of select="materialDescription"/>
								</td>
							</tr>
							
						</table>
						
					</xsl:for-each>
					
				</div>
			</xsl:for-each>
		</div>
		
	</xsl:template>	
	
	
	<xsl:template match="problemDescription">
		<xsl:apply-templates select="para" />
	</xsl:template>	
	
	
</xsl:stylesheet>