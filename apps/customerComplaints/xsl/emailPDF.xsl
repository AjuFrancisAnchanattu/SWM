<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	<xsl:include href="../lib/controls/myCC.xsl"/>
	
	<!--
		TO MAKE LARGE TEXTAREA SMALLER!
	-->
	<xsl:template match="textarea">
		
		<xsl:element name="textarea">
		
			<xsl:attribute name="name"><xsl:value-of select="name" /></xsl:attribute>
			
			<xsl:choose>
				<xsl:when test="required = 'true'">
					<xsl:attribute name="class">textarea required</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">textarea optional</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test="largeTextarea = 'true'">
				<xsl:attribute name="style">width: 99%; height: 250px;</xsl:attribute>
			</xsl:if>
			<xsl:value-of select="value" />
		
		</xsl:element>
		
		<a href="#{name}" onclick="javascript:openSpellCheck('{name}');"><img src="/images/icons2020/edit.jpg" alt="Spell Check" /></a>{TRANSLATE:english_only}
		
		<xsl:if test="../@valid = 'false'">
				<br /><br /><xsl:value-of select="errorMessage" />
		</xsl:if>
	</xsl:template>
	
	
	
	
	<!--
		THE ACTUAL FORM TO EMAIL PDF
	-->
	<xsl:template match="emailPDF">
		
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/warningMessage.js">-</script>
		
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
								<img src="../../../images/famIcons/application_go.png" alt="" class="titleIcon" style="float: left;" />
								<p style="margin: 0 0 0 3px; font-weight: bold; color: #FFFFFF; float: left;">
									{TRANSLATE:complaint} - {TRANSLATE:email} {TRANSLATE:<xsl:value-of select="pdfType" />}
								</p>
							</div></div>
						</div></div></div></div>
					</div>
					
					<xsl:apply-templates select="form" />
				</td>
			</tr>
		</table>
		
		<script>
			var attachmentWarning = new WarningMessage( RemoteTranslate("attach_warning") , "attachmentWarning" );
			document.getElementById("attachmentRow").insertBefore( attachmentWarning.generateWarningRow() );
			myAttachment = document.getElementsByName("attachmentUpload")[0].onchange = attachmentWarning.show;
		</script>
	</xsl:template>
	
</xsl:stylesheet>