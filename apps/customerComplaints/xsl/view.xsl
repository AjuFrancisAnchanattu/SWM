<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	
	<xsl:include href="../../../xsl/global.xsl"/>
	<xsl:include href="../lib/controls/myItemPopUpReadOnly.xsl"/>
	<xsl:include href="textAreaFix.xsl"/>
	
	
	<!--
		The actual view page
	-->
	<xsl:template match="view">
	
		<link rel="stylesheet" href="/apps/customerComplaints/css/customerComplaints.css" media="screen"/>
		<link rel="stylesheet" href="/apps/customerComplaints/css/customerEvaluation.css" media="screen"/>
		<link rel="stylesheet" href="/apps/customerComplaints/css/view.css" media="screen"/>
		<link rel="stylesheet" href="/apps/customerComplaints/css/print.css" media="print"/>
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">
	
					<xsl:apply-templates select="error" />
								
					<div style="display: none;" id="complaintId" complaintId="{id}"><xsl:value-of select="id"/></div>
					
					<div class="title-box1">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<span class="noPrint" style="float: right; margin: 0;">
									
									<a style="color: #FFFFFF;" href="#" onclick="window.print(); return false;">
										{TRANSLATE:print}
										<img src="../../images/famIcons/printer.png" alt="" style="margin: 0 5px -3px 3px; padding: 0;" />
									</a>
								</span>
							
								<img src="../../images/famIcons/magnifier.png" alt="" class="titleIcon noPrint" />
								
								<span style="margin: 0; font-weight: bold; color: #FFFFFF;">
									<span class="noPrint">
									{TRANSLATE:view}
									</span>
									<xsl:choose>
										<xsl:when test="complaint">
											{TRANSLATE:complaint}
										</xsl:when>
										
										<xsl:when test="evaluation">
											{TRANSLATE:evaluation}
										</xsl:when>
										
										<xsl:when test="conclusion">
											{TRANSLATE:conclusion}
										</xsl:when>
									</xsl:choose>
									({TRANSLATE:complaint_id}: <xsl:value-of select="id"/>)
								</span>
								
							</div></div>
						</div></div></div></div>
					</div>
					
					<xsl:choose>
						<xsl:when test="noAccess">
							<p id="noAccess">{TRANSLATE:report_access_denied}</p>
						</xsl:when>	
						<xsl:otherwise>
							
							<xsl:apply-templates select="form" />
							
							<script type="text/javascript" src="/apps/customerComplaints/javascript/customerView.js">-</script>
							
							<xsl:if test="complaint">
								<script type="text/javascript" src="/apps/customerComplaints/invoicePopup/invoicePopup.js">-</script>
								<script type="text/javascript" src="/apps/customerComplaints/javascript/customerComplaint.js">-</script>
							</xsl:if>							
							
							<xsl:if test="evaluation">
								<xsl:if test="displayAuthoriseGoodsBox='return'">
									<xsl:copy-of select="$authoriseGoodsReturn" />
								</xsl:if>
								<xsl:if test="displayAuthoriseGoodsBox='dispose'">
									<xsl:copy-of select="$authoriseGoodsDisposal" />
								</xsl:if>								
							</xsl:if>		
							
							<xsl:if test="conclusion">
								<script type="text/javascript" src="/apps/customerComplaints/javascript/customerConclusion.js">-</script>
							</xsl:if>
						
						</xsl:otherwise>
					</xsl:choose>
	
				</td>
			</tr>
		</table>
		
	</xsl:template>	
			

	<!--
		Authorise goods lightbox
	-->
	<xsl:variable name="authoriseGoodsReturn">
		<div id="authoriseGoodsReturn">
			<div id="authoriseGoodsReturn_left">
				<div class="ie-margin-fix">
					<span>{TRANSLATE:goods_return_header}</span>
					<ul>
						<li>
							{TRANSLATE:goods_return_authorise_info}
						</li>
						<li>
							{TRANSLATE:goods_return_reject_info}
						</li>
					</ul>
				</div>
			</div>
			<div id="authoriseGoodsReturn_right">
				<div class="ie-margin-fix">
					<textarea id="authoriseGoodsReturn_notes" rows="6" cols="20"><xsl:text>Type any comments here</xsl:text></textarea><br/>
					<input type="button" value="Authorise" onClick="evaluation.authoriseGoodsReturn();"/>
					<input type="button" value="Reject" onClick="evaluation.rejectGoodsReturn();"/>
				</div>
			</div>
		</div>
		
		<script type="text/javascript" src="/apps/customerComplaints/javascript/customerEvaluation.js">-</script>
	</xsl:variable>
	
	<!--
		Authorise disposal of goods lightbox
	-->
	<xsl:variable name="authoriseGoodsDisposal">
		<div id="authoriseGoodsReturn">
			<div id="authoriseGoodsReturn_left">
				<div class="ie-margin-fix">
					<span>{TRANSLATE:goods_dispose_header}</span>
					<ul>
						<li>
							{TRANSLATE:goods_return_authorise_info}
						</li>
						<li>
							{TRANSLATE:goods_return_reject_info}
						</li>
					</ul>
				</div>
			</div>
			<div id="authoriseGoodsReturn_right">
				<div class="ie-margin-fix">
					<textarea id="authoriseGoodsReturn_notes" rows="6" cols="20"><xsl:text>Type any comments here</xsl:text></textarea><br/>
					<input type="button" value="Authorise" onClick="evaluation.authoriseGoodsDisposal();"/>
					<input type="button" value="Reject" onClick="evaluation.rejectGoodsDisposal();"/>
				</div>
			</div>
		</div>
		
		<script type="text/javascript" src="/apps/customerComplaints/javascript/customerEvaluation.js">-</script>
	</xsl:variable>
				
</xsl:stylesheet>