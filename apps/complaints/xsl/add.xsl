<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="complaints.xsl"/>
	
	<xsl:template match="complaintAdd">
		
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
								<!--<tr>
									<td><strong>Customer Name: </strong></td>
									<td><xsl:value-of select="customerName"/></td>
								</tr>-->
								<tr>
									<td><strong>{TRANSLATE:complaint_opened}: </strong></td>
									<td><xsl:value-of select="complaintOpenDate"/></td>
								</tr>
								<!--<tr>
									<td><strong>Customer Care Name: </strong></td>
									<td><xsl:value-of select="internalSalesName"/></td>
								</tr-->
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
						<a href="view?complaint={id}&amp;status=complaint"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=complaint"><strong>Edit</strong></a></xsl:if> {TRANSLATE:complaint}<br />
					</xsl:when>
					<xsl:when test="complaintStatus='false'">
						<a href="add?"><strong>Add</strong></a> {TRANSLATE:complaint}<br />
					</xsl:when>
					<xsl:otherwise>
						No complaint sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="evaluationStatus='true'">
						<a href="view?complaint={id}&amp;status=evaluation"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=evaluation"><strong>Edit</strong></a></xsl:if> {TRANSLATE:evaluation}<br />
					</xsl:when>
					<!--<xsl:when test="evaluationStatus='false' and typeOfComplaint!='supplier_complaint'"> DP-->
					<xsl:when test="evaluationStatus='false' and typeOfComplaint!='supplier_complaint' and complaintStatus='true'">
						<a href="resume?complaint={id}&amp;status=evaluation"><strong>{TRANSLATE:add}</strong></a> {TRANSLATE:evaluation}<br />
					</xsl:when>
					<xsl:otherwise>
						{TRANSLATE:no_evaluation_sections_exist}
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="conclusionStatus='true'">
						<a href="view?complaint={id}&amp;status=conclusion"><strong>{TRANSLATE:view}</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?complaint={id}&amp;status=conclusion"><strong>Edit</strong></a></xsl:if> {TRANSLATE:conclusion}<br />
					</xsl:when>
					<xsl:when test="conclusionStatus='false' and evaluationStatus='true'">
					<!--<xsl:when test="conclusionStatus='false'"> DP-->
						<a href="resume?complaint={id}&amp;status=conclusion"><strong>{TRANSLATE:add}</strong></a> {TRANSLATE:conclusion}<br />
					</xsl:when>
					<xsl:otherwise>
						{TRANSLATE:no_conclusion_sections_exist}
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
					<tr>
						<td><hr /><strong>{TRANSLATE:grouped_complaint}?: </strong><xsl:value-of select="groupedComplaint"/><xsl:if test="groupedComplaint='Yes'"> (<a href="index?id={groupedComplaintID}" target="_blank"><xsl:value-of select="groupedComplaintID"/></a>)</xsl:if></td>
					</tr>
					<xsl:if test="groupedComplaint='Yes'">
					<tr>
						<td><strong>Type: </strong><xsl:value-of select="typeOfGroupedComplaint"/></td>
					</tr>
					</xsl:if>
					</table>
					
					<br />
					<!--<input type="submit" value="Submit" onclick="buttonPress('submit');" />-->
					
				</div></div>
				
				<br />
				
				<xsl:apply-templates select="credit_authorised_main" />
				
				
				
				
				<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
				</div>	
				
				
			</td>
			
			
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="complaintReport" />
					
				</td>
			</tr>

		</table>
		<xsl:if test="sfIDVal > 0">
			<input type="hidden" name="sfID" value="{sfIDVal}" />
		</xsl:if>
		<xsl:if test="whichAnchor">
			<script language="javascript">
				document.onload = moveToWhere();
				function moveToWhere(){
					var curtop = 0;
					var obj = document.getElementById('<xsl:value-of select="whichAnchor"/>');
					if (obj.offsetParent) {
						do {
							curtop += obj.offsetTop;
						} while (obj = obj.offsetParent);
					}
					window.scrollTo(0,(curtop-500));
				}
			</script>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="credit_authorised_main">
	
		<div class="snapin_top"><div class="snapin_top_3">
			<div style="color: #FFFFFF; font-weight: bold">Credit Authorisation</div>
		</div></div>
		
		<div class="snapin_content"><div class="snapin_content_3">
			<table width="250">
			<tr>
				<td><strong>Status:</strong><br /><xsl:value-of select="credit_authorised"/></td>
			</tr>
		</table>
		
		</div></div>
	
	</xsl:template>
	
	
	<xsl:template match="complaintReport">
	<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
		</div></div></div></div>
		
		<xsl:if test="@orderId">
			<input type="hidden" name="orderId" value="{@orderId}" />
		</xsl:if>
		<script language="JavaScript">
			function saveFormForLater(){
				document.form.saveForm.value = "saveFormForLater";
				buttonPress('submit');
			}
		</script>
		<input type="hidden" name="saveForm" value="" />
		<input type="button" value="Save Form For Later" onClick="javascript: saveFormForLater();" />
		<br /><br />
		<xsl:apply-templates select="form" />
	</xsl:template>
	

	
</xsl:stylesheet>