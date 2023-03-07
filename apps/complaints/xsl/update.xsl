<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


	
	<xsl:include href="complaints.xsl"/>
	
	<xsl:template match="complaintUpdate">
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				
		
				<div class="snapin_top"><div class="snapin_top_3">
					<div style="color: #FFFFFF; font-weight: bold">Complaints Toolbox</div>
				</div></div>				
		
				<div class="snapin_content"><div class="snapin_content_3">
					
					<table width="250">
					<tr>
						<td>
						
						<table width="250">
							<tr>
								<td><strong>Complaint Number: </strong></td>
								<td><xsl:value-of select="complaintId"/></td>
							</tr>
							<tr>
								<td><strong>Complaint Type: </strong></td>
								<td><xsl:value-of select="complaint_type"/></td>
							</tr>
							<tr>
								<td><strong>SAP Customer No: </strong></td>
								<td><xsl:value-of select="sapCustomerNumber"/></td>
								</tr>
							<tr>
								<td><strong>Customer Name: </strong></td>
								<td><xsl:value-of select="customerName"/></td>
							</tr>
							<tr>
								<td><strong>Date Opened: </strong></td>
								<td><xsl:value-of select="complaintOpenDate"/></td>
							</tr>
							<tr>
								<td><strong>Customer Care: </strong></td>
								<td><xsl:value-of select="internalSalesName"/></td>
								</tr>
							<tr>
								<td><strong>Complaint Owner: </strong></td>
								<td><xsl:value-of select="complaintOwner"/></td>
							</tr>
						</table>
						</td>
					</tr>				
					<tr>
						<td><hr /><strong>Grouped Complaint?: </strong><xsl:value-of select="groupedComplaint"/><xsl:if test="groupedComplaint='Yes'"> (<a href="index?id={groupedComplaintID}" target="_blank"><xsl:value-of select="groupedComplaintID"/></a>)</xsl:if></td>
					</tr>
					<xsl:if test="groupedComplaint='Yes'">
					<tr>
						<td><strong>Type: </strong><xsl:value-of select="typeOfGroupedComplaint"/></td>
					</tr>
					</xsl:if>
					<tr>
						<td><hr /><strong>Complaint Tools: </strong></td>
					</tr>
					<tr>
						<td>
						<ul>
							<li><a href="addComment?id={id}&amp;mode=takeover">Takeover Ownership</a></li>
							<li><a href="delegate?mode=delegate&amp;complaintId={id}">Delegate</a></li>
							<li><a href="addComment?mode=add&amp;complaintId={id}">Add Comment</a></li>
							<li><a href="sendReminder?id={id}">Send A Reminder</a></li>
							<xsl:if test="complaintAdmin='true'">
							<li><a href="delegate?mode=reopen&amp;complaintId={id}">Re-Open</a></li>
							</xsl:if>
						</ul>
						</td>
					</tr>
					
					
					</table>
					
					<!--<br />
					<input type="submit" value="Submit" onclick="buttonPress('submit');" />	-->
					
				</div></div>
				
				<br />
				
				<xsl:apply-templates select="credit_authorised_main" />
				
				<br />
				
				<div class="snapin_top"><div class="snapin_top_3">
					<div style="color: #FFFFFF; font-weight: bold">Complaints Documentation</div>
				</div></div>
				
				<div class="snapin_content"><div class="snapin_content_3">
				<table cellspacing="4" width="260">
				<tr>
     				<td><strong>Reference Documentation</strong></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/ICS-Authorisation_Notes.pdf">NEW Credit Authorisation Procedure</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/po.xls">Display process owner matrix</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/shipping.xls">Display Shipping department matrix </a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/is.xls">Display IS list</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/complaint_manual.doc">Complaint handling manual</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/interco_cn_issue.xls">Interco Credit Note Responsibilities</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/na_contacts.xls">NA complaint contacts in Europe</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/na_quality_contacts.xls">NA Quality Contacts</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/interco_ics_matrix.xls">Interco Matrix</a></td>
     			</tr>
     			<tr>
     				<td><br /><strong>External Links</strong></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="https://suppliers.valeo.com/suppliers/">Valeo Complaint system</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="http://extranet.duraauto.com/">Dura Score Card Management</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="https://gpdb.yazaki-europe.com/yazaki_asp/">Yazaki Complaint Management</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="https://us.sso.covisint.com/jsp/preLogin.jsp?host=https://portal.covisint.com&amp;ct_orig_uri=%2Fwps%2Fprivate%2F">Convisint Complaint Management</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="http://b2b.psa-peugeot-citroen.com/index.htm">PSA complaint management</a></td>
     			</tr>
     			<tr>
     				<td><img src="/images/point.jpg" /> <a target="_blank" href="https://access2.lear.com">Lear Quality System</a></td>
     			</tr>
			</table>
				</div></div>
				
			</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
					<xsl:apply-templates select="complaintReport" />
					<xsl:apply-templates select="printdiv" />
				</td>
			</tr>

		</table>
		
	</xsl:template>

	<xsl:template match="complaintReport">
	<div id="printthis">
		
		<xsl:if test="../showInfo='true'">
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:complaint_external_info}</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td>{TRANSLATE:external_info}</td>
			</tr>
			<tr>
				<td>									
					<xsl:choose>
						<xsl:when test="../containmentActionAdded='2'">
							<input type="submit" value="Approve" onclick="buttonPress('approve');" />
							<input type="submit" value="Reject" onclick="buttonPress('reject');" />
						</xsl:when>
						<xsl:when test="../containmentActionAdded='1'">
							<input type="submit" value="Approve" onclick="buttonPress('acceptContainmentAction');" />
							<input type="submit" value="Reject" onclick="buttonPress('rejectContainmentAction');" />
						</xsl:when>
						<xsl:otherwise>
							<input type="submit" value="Approve" onclick="buttonPress('approve');" />
							<input type="submit" value="Reject" onclick="buttonPress('reject');" />
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		
		<br />
		</xsl:if>
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
		</div></div></div></div>
	
		
		<xsl:if test="@orderId">
			<input type="hidden" name="orderId" value="{@orderId}" />
		</xsl:if>
		
		<xsl:apply-templates select="form" />
	</div>	
	</xsl:template>
	
	<xsl:template match="printdiv">


	<script language="Javascript">		
		function printDiv(obj) {
			
			var content = document.getElementById(obj).innerHTML;
			/*var newwin = window.open('', 'newwin');
			newwin.document.write('<html><head><title>Print Page</title>');
			newwin.document.write('<style type="text/css">body { background-color: #FFFFFF; background-image: none;');
			newwin.document.write('font-family: Arial, Helvetica, sans-serif;font-size: 12px;');
			newwin.document.write('}</style>\n');
			newwin.document.write('</head>');
			newwin.document.write('<body>');
			newwin.document.write(content);
			newwin.document.write('</body>');
			newwin.document.write('</html>');*/

			//newwin.print();
			//newwin.close();
		}
		document.onload = printDiv('printthis');
	</script>
	</xsl:template>
</xsl:stylesheet>