<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsReports">
			<table cellspacing="0" width="260">
			
				<xsl:choose>
         			<xsl:when test="reportCount > 0">	
         				<tr><td><strong>ID</strong></td><td><strong>Customer</strong></td><td><strong>Complaint Owner</strong></td></tr>
						<xsl:apply-templates select="complaints_Report" />
					</xsl:when>
          			<xsl:otherwise>
            			<tr><td colspan="3">None</td></tr>
         		 	</xsl:otherwise>
				</xsl:choose>

				<xsl:choose>
					<xsl:when test="savedReportCount > 0">	
						<tr><td colspan="3"><br /><strong>Saved Complaint Forms</strong></td></tr>
						<xsl:for-each select="savedComplaint">
							<xsl:choose>
         						<xsl:when test="savedType = 'conclusion'">
								<tr><td colspan="3"><a href="/apps/complaints/index?delSavedForm=1&amp;sfID={savedID}">Del</a> - <xsl:value-of select="savedType"/> - <a href="/apps/complaints/resume?sfID={savedID}&amp;complaint={savedComplaintID}&amp;status={savedType}"><xsl:value-of select="savedDate"/></a></td></tr>
							</xsl:when>
         						<xsl:when test="savedType = 'evaluation'">
								<tr><td colspan="3"><a href="/apps/complaints/index?delSavedForm=1&amp;sfID={savedID}">Del</a> - <xsl:value-of select="savedType"/> - <a href="/apps/complaints/resume?sfID={savedID}&amp;complaint={savedComplaintID}&amp;status={savedType}"><xsl:value-of select="savedDate"/></a></td></tr>
							</xsl:when>
							<xsl:otherwise>
								<tr><td colspan="3"><a href="/apps/complaints/index?delSavedForm=1&amp;sfID={savedID}">Del</a> - <xsl:value-of select="savedType"/> - <a href="/apps/complaints/add?sfID={savedID}"><xsl:value-of select="savedDate"/></a></td></tr>
							</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<tr><td colspan="3"><strong>Saved Complaint Forms</strong></td></tr>
						<tr><td colspan="3">None</td></tr>
					</xsl:otherwise>
       		 		</xsl:choose>
			</table>
	</xsl:template>
	
	<xsl:template match="complaints_Report">
    	<tr><td>
    	<xsl:choose>
					<xsl:when test="complaint_type='customer_complaint'">
						<a href="/apps/complaints/index?id={id}">C<xsl:value-of select="id" /></a>
					</xsl:when>
					<xsl:when test="complaint_type='hs'">
						<a href="/apps/complaints/index?id={id}">HS<xsl:value-of select="id" /></a>
					</xsl:when>
					<xsl:when test="complaint_type='environment'">
						<a href="/apps/complaints/index?id={id}">EV<xsl:value-of select="id" /></a>
					</xsl:when>
					<xsl:when test="complaint_type='quality'">
						<a href="/apps/complaints/index?id={id}">Q<xsl:value-of select="id" /></a>
					</xsl:when>
					<xsl:when test="complaint_type='supplier_complaint'">
						<a href="/apps/complaints/index?id={id}">SC<xsl:value-of select="id" /></a>
					</xsl:when>
					<xsl:when test="complaint_type='survey_scorecard'">
						<a href="/apps/complaints/index?id={id}">SS<xsl:value-of select="id" /></a>
					</xsl:when>
				</xsl:choose>
    	</td><td><xsl:value-of select="sapCustomerNumber" /></td><td><xsl:value-of select="owner" /></td></tr>
    </xsl:template>
    


</xsl:stylesheet>