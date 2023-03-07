<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:include href="../lib/controls/myItemPopUpReadOnly.xsl"/>
	<xsl:include href="../lib/controls/myAutocomplete.xsl"/>
	<xsl:include href="../lib/controls/myCalendar.xsl"/>
	<xsl:include href="../lib/controls/myRadio.xsl"/>
	<xsl:include href="textAreaFix.xsl"/>
	
	<!--
		Some files we need even before we load anything...
	-->
	<xsl:template name="ccImports">
		<link rel="stylesheet" href="/apps/customerComplaints/css/customerComplaints.css"/>
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/warningMessage.js">-</script>
	</xsl:template>
	
	
	<!--
		Loads snapins
	-->
	<xsl:template name="ccSnapins">
		<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
			<div id="snapin_left_container">
				<xsl:apply-templates select="snapin_left" />
			</div>
			
		</td>
	</xsl:template>
	
	
	<!--
		Title for a edit/add form
	-->
	<xsl:template name="ccTitle">
		<xsl:apply-templates select="ccAddForm/error" />
		<div style="display: none;" id="complaintId" complaintId="{id}"><xsl:value-of select="id"/></div>
		
		<div class="title-box1">
			<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
				<div class="inner"><div class="wrapper">
					<xsl:element name="img">
						<xsl:attribute name="src">../../images/famIcons/application_form_<xsl:value-of select="$formType"/>.png</xsl:attribute>
						<xsl:attribute name="class">titleIcon</xsl:attribute>
					</xsl:element>
					
					<p style="margin: 0; font-weight: bold; color: #FFFFFF;">
						{TRANSLATE:<xsl:value-of select="$formType"/>}
						<xsl:choose>
							<xsl:when test="complaint">
								{TRANSLATE:complaint}
								<xsl:if test="$formType = 'edit'">({TRANSLATE:complaint_id}: <xsl:value-of select="id"/>)</xsl:if>
							</xsl:when>
							
							<xsl:when test="evaluation">											
								{TRANSLATE:evaluation} ({TRANSLATE:complaint_id}: <xsl:value-of select="id"/>)
							</xsl:when>										
							
							<xsl:when test="conclusion">
								{TRANSLATE:conclusion} ({TRANSLATE:complaint_id}: <xsl:value-of select="id"/>)
							</xsl:when>
						</xsl:choose>
					</p>
					
				</div></div>
			</div></div></div></div>
		</div>
	</xsl:template>
	
	
	<!--
		Form for new complaint/evaluation/conclusion
	-->
	<xsl:template match="add">
		<xsl:call-template name="ccImports"/>
		
		<table width="100%" cellpadding="0">
			<tr>
				<xsl:call-template name="ccSnapins"/>
				
				<td valign="top" style="padding: 10px;">
					
					<xsl:call-template name="ccTitle">
						<xsl:with-param name="formType">add</xsl:with-param>
					</xsl:call-template>
					
					<xsl:apply-templates select="ccAddForm" />
				</td>
			</tr>
		</table>
	</xsl:template>	
	
	
	<!--
		Form for editing complaint/evaluation/conclusion
	-->
	<xsl:template match="edit">
		<xsl:call-template name="ccImports"/>
	
		<table width="100%" cellpadding="0">
			<tr>
				<xsl:call-template name="ccSnapins"/>
	
				<td valign="top" style="padding: 10px;">
				
					<xsl:call-template name="ccTitle">
						<xsl:with-param name="formType">edit</xsl:with-param>
					</xsl:call-template>
					
					<xsl:choose>
						<xsl:when test="closedComplaint">
							<p id="noAccess">{TRANSLATE:complaint_closed_notification}</p>
						</xsl:when>						
						<xsl:when test="locked">
							<p id="noAccess">{TRANSLATE:report_locked_by} <xsl:value-of select="lockedUser"/></p>
						</xsl:when>
						<xsl:when test="noAccess">
							<p id="noAccess">{TRANSLATE:report_access_denied}</p>
						</xsl:when>
						<xsl:otherwise>
							<xsl:apply-templates select="ccAddForm" />
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	<!--
		The actual form
	-->
	<xsl:template match="ccAddForm">
	
		<xsl:if test="../complaint">
			<script type="text/javascript" src="/apps/customerComplaints/javascript/customerComplaint_beforeForm.js">-</script>
		</xsl:if>
		
		<xsl:apply-templates select="form" />
		
		<script type="text/javascript" src="/apps/customerComplaints/javascript/customerResume.js">-</script>
		
		<xsl:if test="../complaint">
			<script type="text/javascript" src="/apps/customerComplaints/invoicePopup/invoicePopup.js">-</script>
			<script type="text/javascript" src="/apps/customerComplaints/javascript/customerComplaint.js">-</script>
		</xsl:if>
		
		<xsl:if test="../evaluation">
			<script type="text/javascript" src="/apps/customerComplaints/javascript/customerEvaluation.js">-</script>
		</xsl:if>
		
		<xsl:if test="../conclusion">
			<script type="text/javascript" src="/apps/customerComplaints/javascript/customerConclusion.js">-</script>
		</xsl:if>
	</xsl:template>	
	
	
			
</xsl:stylesheet>