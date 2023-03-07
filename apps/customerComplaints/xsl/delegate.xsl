<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	<xsl:include href="../lib/controls/myCC.xsl"/>

	<xsl:template match="ccDelegate">	
		
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">
					
					<div class="title-box1">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<img src="../../images/famIcons/application_go.png" alt="" class="titleIcon" style="float: left;" /><p style="margin: 0 0 0 3px; font-weight: bold; color: #FFFFFF; float: left;">{TRANSLATE:delegate} <xsl:value-of select="ownerType" /></p>
							</div></div>
						</div></div></div></div>
					</div>
				
					<xsl:if test="(allLocked) or (complaintLocked) or (conclusionLocked) or (evaluationLocked)">
						<div style="margin: 5px; padding: 5px; text-align: center; border: 1px #FF3838 solid; background: #FFD8D8">
							
							<table style="text-align: left; margin-top: 10px;">
								<tr>
									<td colspan="2">
										<b>{TRANSLATE:forms_locked_error}:</b>
									</td>
								</tr>
								
								<xsl:if test="complaintLockedUser">
									<tr>
										<td>
											{TRANSLATE:complaint}:
										</td>
										<td>
											<img src="../../images/famIcons/lock.png" style="float: left; margin: -1px 3px 0 0;" />
											<span style="font-style: italic;">
												{TRANSLATE:locked_for} <xsl:value-of select="complaintLockedUser" />
											</span>
										</td>
									</tr>
								</xsl:if>
								
								<xsl:if test="evaluationLockedUser">
									<tr>
										<td>
											{TRANSLATE:evaluation}:
										</td>
										<td>
											<img src="../../images/famIcons/lock.png" style="float: left; margin: -1px 3px 0 0;" />
											<span style="font-style: italic;">
												{TRANSLATE:locked_for} <xsl:value-of select="evaluationLockedUser" />
											</span>
										</td>
									</tr>
								</xsl:if>
								
								<xsl:if test="conclusionLockedUser">
									<tr>
										<td>
											{TRANSLATE:conclusion}:
										</td>
										<td>
											<img src="../../images/famIcons/lock.png" style="float: left; margin: -1px 3px 0 0;" />
											<span style="font-style: italic;">
												{TRANSLATE:locked_for} <xsl:value-of select="conclusionLockedUser" />
											</span>
										</td>
									</tr>
								</xsl:if>
								
							</table>
						
						</div>
					</xsl:if>
					
					<xsl:if test="approval">
						
						<div id="warningPrompt" style="display: none; margin: 5px; padding: 5px; text-align: center; border: 1px #FF3838 solid; background: #FFD8D8">
							{TRANSLATE:delegate_approval_warning}
						</div>
						
						<script>
							function togglePrompt()
							{
								if( document.getElementById("delegateForm1").checked )
								{
									document.getElementById("warningPrompt").style.display = "";
								}
								else if( document.getElementById("delegateForm0").checked )
								{
									document.getElementById("warningPrompt").style.display = "none";
								}
							}
						</script>
					</xsl:if>
					
					<xsl:apply-templates select="form" />
				</td>
			</tr>
		</table>		
	</xsl:template>
	
</xsl:stylesheet>