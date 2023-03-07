<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


	
	<xsl:include href="appraisal.xsl"/>
	
	<xsl:template match="appraisalView">
		
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
								<td><strong>Date Opened: </strong></td>
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
						<a href="view?appraisal={id}&amp;status=appraisal"><strong>View</strong></a> | <a href="resume?appraisal={id}&amp;status=appraisal"><strong>Edit</strong></a> Appraisal<br />
					</xsl:when>
					<xsl:when test="appraisalStatus='false'">
						<a href="resume?appraisal={id}&amp;status=appraisal"><strong>Add</strong></a> Appraisal<br />
					</xsl:when>
					<xsl:when test="appraisalOverallStatus='true'">
						<a href="view?appraisal={id}&amp;status=appraisal"><strong>View</strong></a> Appraisal<br />
					</xsl:when>
					<xsl:otherwise>
						No appraisal sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="reviewStatus='true'">
						<a href="view?appraisal={id}&amp;status=review"><strong>View</strong></a><xsl:if test="lockStatus='unlocked'"> | <a href="resume?appraisal={id}&amp;status=review"><strong>Edit</strong></a></xsl:if> review<br />
					</xsl:when>
					<xsl:when test="reviewStatus='false'">
						<a href="resume?appraisal={id}&amp;status=review"><strong>Add</strong></a> review<br />
					</xsl:when>
					<xsl:when test="reviewOverallStatus='true'">
						<a href="view?appraisal={id}&amp;status=review"><strong>View</strong></a> review<br />
					</xsl:when>
					<xsl:otherwise>
						No review sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
				<tr><td>
				<xsl:choose>					
					<xsl:when test="developmentStatus='true'">
						<a href="view?appraisal={id}&amp;status=conclusion"><strong>View</strong></a> | <a href="resume?appraisal={id}&amp;status=conclusion"><strong>Edit</strong></a> Conclusion<br />
					</xsl:when>
					<xsl:when test="developmentStatus='false'">
						<a href="resume?appraisal={id}&amp;status=conclusion"><strong>Add</strong></a> Conclusion<br />
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
						No Relationships sections exist
					</xsl:otherwise>
				</xsl:choose>
				</td></tr>
				
					</table>
					
				</div></div>
				
				<br />
				
			</td>
			
				<td valign="top">
				
					<xsl:apply-templates select="error" />
					<xsl:apply-templates select="appraisalReport" />
					<xsl:apply-templates select="printdiv" />
				</td>
			</tr>

		</table>
		
	</xsl:template>

	<xsl:template match="appraisalReport">
	<div id="printthis">
	<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_report}</p>
		</div></div></div></div>
		
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