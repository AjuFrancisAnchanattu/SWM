<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="vacancies">
	
		<table cellspacing="2" width="260">
			<xsl:if test="vacancyToShow='1'">
			
				<xsl:for-each select="vacancy_details">
				<tr>
					<td style="border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: #999999; padding-bottom: 8px; padding-top: 5px;">
						<strong>
							<a href="javascript: void(0)" onClick="window.open('http://ukashapp023/Vacancy/ViewVacancy.aspx?vacancyID={id}','mywindow','menubar=0,resizable=0,location=0,toolbar=0,scrollbars=1,width=700,height=600')">
								<xsl:value-of select="jobTitle" /> ...
							</a>
						</strong>
						
						<br /><br />
						Location: <xsl:value-of select="location" />
						<br />
						Hiring Manager: <xsl:value-of select="nameOfHiringManager" />
						<br />
						Closing Date: <xsl:value-of select="closingDate" />
					</td>
				</tr>
				</xsl:for-each>
			
			</xsl:if>
			
			<xsl:if test="vacancyToShow='0'">
				<tr>
					<td style="padding-bottom: 8px; padding-top: 5px;">
						There are currently no vacancies.
					</td>
				</tr>
			</xsl:if>
		</table>

	</xsl:template>
	
	<!--<xsl:template match="vacancy_details">
		<xsl:value-of select="subject" /><br />
	</xsl:template>-->
	
</xsl:stylesheet>