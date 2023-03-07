<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="appraisal.xsl"/>
	
	<xsl:template match="appraisalAdd">
		
		<table width="100%" cellpadding="10">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
		
				<div class="snapin_top"><div class="snapin_top_3">
					<div style="color: #FFFFFF; font-weight: bold">Appraisal Toolbox</div>
				</div></div>
		
			<div class="snapin_content"><div class="snapin_content_3">
					
					<table width="250">
					<tr>
							<td>
							
							<table width="250">
								<tr>
									<td><strong>Appraisal Number: </strong></td>
									<td><xsl:value-of select="appraisalId"/></td>
								</tr>
								<tr>
									<td><strong>Date Created: </strong></td>
									<td><xsl:value-of select="appraisalOpenDate"/></td>
								</tr>
								<tr>
									<td><strong>Appraisal Owner: </strong></td>
									<td><xsl:value-of select="appraisalOwner"/></td>
								</tr>
								<tr>
									<td colspan="2"><hr /></td>
								</tr>
							</table>
							
							</td>
						</tr>
				<tr><td>
				<xsl:choose>
					<xsl:when test="appraisalStatus='true'">
						<a href="view?appraisal={id}&amp;status=appraisal"><strong>View</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?appraisal={id}&amp;status=appraisal"><strong>Edit</strong></a></xsl:if> appraisal<br />
					</xsl:when>
					<xsl:when test="appraisalStatus='false'">
						<a href="add?"><strong>Add</strong></a> appraisal<br />
					</xsl:when>
					<xsl:otherwise>
						No Appraisal sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="reviewStatus='true'">
						<a href="view?appraisal={id}&amp;status=review"><strong>View</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?appraisal={id}&amp;status=review"><strong>Edit</strong></a></xsl:if> Review<br />
					</xsl:when>
					<xsl:when test="reviewStatus='false'">
						<a href="resume?appraisal={id}&amp;status=review"><strong>Add</strong></a> Review<br />
					</xsl:when>
					<xsl:otherwise>
						No Review sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="developmentStatus='true'">
						<a href="view?appraisal={id}&amp;status=development"><strong>View</strong></a> | <a href="resume?appraisal={id}&amp;status=development"><strong>Edit</strong></a> Development<br />
					</xsl:when>
					<xsl:when test="developmentStatus='false'">
						<a href="resume?appraisal={id}&amp;status=development"><strong>Add</strong></a> Development<br />
					</xsl:when>
					<xsl:otherwise>
						No development sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="trainingStatus='true'">
						<a href="view?appraisal={id}&amp;status=training"><strong>View</strong></a> | <a href="resume?appraisal={id}&amp;status=training"><strong>Edit</strong></a> Training<br />
					</xsl:when>
					<xsl:when test="trainingStatus='false'">
						<a href="resume?appraisal={id}&amp;status=training"><strong>Add</strong></a> Training<br />
					</xsl:when>
					<xsl:otherwise>
						No training sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="relationshipsStatus='true'">
						<a href="view?appraisal={id}&amp;status=relationships"><strong>View</strong></a> | <a href="resume?appraisal={id}&amp;status=relationships"><strong>Edit</strong></a> Relationships<br />
					</xsl:when>
					<xsl:when test="relationshipsStatus='false'">
						<a href="resume?appraisal={id}&amp;status=relationships"><strong>Add</strong></a> Relationships<br />
					</xsl:when>
					<xsl:otherwise>
						No relationships sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
					
					</table>
					
					<br />
					
				</div></div>
				
				<br />
				
				<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
				</div>	
				
				
			</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="appraisalReport" />
					
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
	
	
	<xsl:template match="appraisalReport">
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