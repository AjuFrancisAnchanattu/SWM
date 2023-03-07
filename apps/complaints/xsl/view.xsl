<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


	
	<xsl:include href="complaints.xsl"/>
	
	<xsl:template match="complaintView">
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				
		
				<div class="snapin_top"><div class="snapin_top_3">
					<div style="color: #FFFFFF; font-weight: bold">Complaints Toolbox</div>
				</div></div>				
		
				<div class="snapin_content"><div class="snapin_content_3">
					
					<table width="250">
					<!--<tr>
						<td><a href="index?id={complaintId}"><strong>{TRANSLATE:back_to_summary}</strong></a></td>
					</tr>
					<tr>
						<td><hr /></td>
					</tr>-->
					<tr>
						<td>
						
						<table width="250">
							<tr>
								<td><strong>{TRANSLATE:complaint_number}: </strong></td>
								<td><xsl:value-of select="complaintId"/></td>
							</tr>
							<tr>
								<td><strong>{TRANSLATE:complaint_type}: </strong></td>
								<td>
								<xsl:choose>
									<xsl:when test="complaint_type='Quality Complaint'">
										{TRANSLATE:internal_complaint}
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="complaint_type"/>
									</xsl:otherwise>
								</xsl:choose>
								</td>
							</tr>
							<xsl:choose>
								<xsl:when test="typeOfComplaint='supplier_complaint'">
									<tr>
										<td><strong>{TRANSLATE:sap_supplier_number}: </strong></td>
										<td><xsl:value-of select="sapCustomerNumber"/></td>
									</tr>
									<tr>
										<td><strong>{TRANSLATE:buyer}: </strong></td>
										<td><xsl:value-of select="buyer"/></td>
									</tr>
								</xsl:when>
								<xsl:when test="typeOfComplaint='quality_complaint'">
									<!-- Important: This used to be quality_complaint -->
								</xsl:when>
								<xsl:otherwise>
									<tr>
										<td><strong>{TRANSLATE:sap_customer_number}: </strong></td>
										<td><xsl:value-of select="sapCustomerNumber"/></td>
									</tr>
									<tr>
										<td><strong>{TRANSLATE:customer_name}: </strong></td>
										<td><xsl:value-of select="customerName"/></td>
									</tr>
								</xsl:otherwise>
							</xsl:choose>
							
							<tr>
								<td><strong>{TRANSLATE:complaint_opened}: </strong></td>
								<td><xsl:value-of select="complaintOpenDate"/></td>
							</tr>
							<!--<tr>
								<td><strong>Customer Care: </strong></td>
								<td><xsl:value-of select="internalSalesName"/></td>
							</tr>-->
							<tr>
								<td><strong>{TRANSLATE:complaint_owner}: </strong></td>
								<td><xsl:value-of select="complaintOwner"/></td>
							</tr>
							<tr>
								<td colspan="2"><hr /></td>
							</tr>
						</table>
						
						</td>
					</tr>				
				<tr><td>
				
				<xsl:choose>
					<xsl:when test="complaintStatus='true'">
						 <a href="view2?complaint={id}&amp;status=complaint&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=complaint"><strong>Edit</strong></a></xsl:if> {TRANSLATE:complaint}<br />
					</xsl:when>
					<xsl:when test="complaintStatus='false'">
						<a href="resume?complaint={id}&amp;status=complaint"><strong>{TRANSLATE:add}</strong></a> {TRANSLATE:complaint}<br />
					</xsl:when>
					<xsl:when test="complaintOverallStatus='true'">
						<a href="view2?complaint={id}&amp;status=complaint&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=complaint"><strong>{TRANSLATE:view}</strong></a>	 {TRANSLATE:complaint}<br />
					</xsl:when>
					<xsl:otherwise>
						No complaint sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="evaluationStatus='true'">
						<a href="view2?complaint={id}&amp;status=evaluation&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=evaluation"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=evaluation"><strong>Edit</strong></a></xsl:if> {TRANSLATE:evaluation}<br />
					</xsl:when>
					<xsl:when test="evaluationStatus='false'">
						<a href="resume?complaint={id}&amp;status=evaluation"><strong>{TRANSLATE:add}</strong></a> {TRANSLATE:evaluation}<br />
					</xsl:when>
					<xsl:when test="evaluationOverallStatus='true'">
						<a href="view2?complaint={id}&amp;status=evaluation&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=evaluation"><strong>{TRANSLATE:view}</strong></a> {TRANSLATE:evaluation}<br />
					</xsl:when>
					<xsl:otherwise>
						{TRANSLATE:no_evaluation_sections_exist}
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="conclusionStatus='true'">
						<a href="view2?complaint={id}&amp;status=conclusion&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=conclusion"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=conclusion"><strong>Edit</strong></a></xsl:if> {TRANSLATE:conclusion}<br />
					</xsl:when>
					<xsl:when test="conclusionStatus='false'">
						<a href="resume?complaint={id}&amp;status=conclusion"><strong>{TRANSLATE:add}</strong></a> {TRANSLATE:conclusion}<br />
					</xsl:when>
					<xsl:when test="conclusionOverallStatus='true'">
						<a href="view2?complaint={id}&amp;status=conclusion&amp;print=1" target="_blank"><strong>Print</strong></a> | <a href="view?complaint={id}&amp;status=conclusion"><strong>{TRANSLATE:view}</strong></a> {TRANSLATE:conclusion}<br />
					</xsl:when>
					<xsl:otherwise>
						No conclusion sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
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
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="http://ext.scapa.com">Scapa Extranet</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="https://suppliers.valeo.com/suppliers/">Valeo Complaint system</a></td>
	 			</tr>
	 			<!--<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="http://extranet.duraauto.com/">Dura Score Card Management</a></td>
	 			</tr>-->
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
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="http://www.vwgroupsupply.com/b2b/vwb2b_folder/supplypublic/en.html">VW Group</a></td>
	 			</tr>
	 			<tr>
	 				<td><br /><strong>North American Reference Documentation</strong></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/SNA_Retturn_Approval_Credit_Authorization_Matrix_080422.pdf">Return Material &amp; Credit Authorization</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/NA_Process_Owner_Matrix_9_12_2008.xls">Process Owner Matrix</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/8D_description.doc">8D Description</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CsrAccountResponsibility.pdf">CSR Account Responsibility</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CS0012_ra_instructions_Renfrew.doc">Return Instructions Renfrew</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CS0015_ra_instructions_Windsor.doc">Return Instructions Windsor</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CS0016_ra_instructions_for_Liverpool_NY.doc">Return Instructions Liverpool NY</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CS0018_ra_instructions_NJ.doc">Return Instructions NJ</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/CS_ra_instructions_for_Inglewood.doc">Return Instructions Inglewood</a></td>
	 			</tr>
	 			<tr>
	 				<td><img src="/images/point.jpg" /> <a target="_blank" href="/apps/complaints/data/NA_Customer_Complaint_Process_Flow_28100.pdf">NA Customer Complaint Process Flow</a></td>
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