<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="toolBoxMain">
			
	<table width="250">
		<tr>
				<td>
				
				<table width="250">
					<tr>
						<td><strong>Complaint Number: </strong></td>
						<td><xsl:value-of select="complaintId"/></td>
					</tr>
					<tr>
						<td><strong>Date Opened: </strong></td>
						<td><xsl:value-of select="complaintOpenDate"/></td>
					</tr>
					<tr>
						<td><strong>Complaint Owner: </strong></td>
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
				<a href="view?complaint={id}&amp;status=complaint"><strong>View</strong></a> | <a href="resume?complaint={id}&amp;status=complaint"><strong>Edit</strong></a> Complaint<br />
			</xsl:when>
			<xsl:when test="complaintStatus='false'">
				<a href="add?"><strong>Add</strong></a> Complaint<br />
			</xsl:when>
			<xsl:otherwise>
				No complaint sections exist
			</xsl:otherwise>
		</xsl:choose>
		</td></tr>
		
		<tr><td>
		<xsl:choose>					
			<xsl:when test="evaluationStatus='true'">
				<a href="view?complaint={id}&amp;status=evaluation"><strong>View</strong></a> | <a href="resume?complaint={id}&amp;status=evaluation"><strong>Edit</strong></a> Evaluation<br />
			</xsl:when>
			<xsl:when test="evaluationStatus='false'">
				<a href="add?"><strong>Add</strong></a> Evaluation<br />
			</xsl:when>
			<xsl:otherwise>
				No evaluation sections exist
			</xsl:otherwise>
		</xsl:choose>
		</td></tr>
		
		<tr><td>
		<xsl:choose>					
			<xsl:when test="conclusionStatus='true'">
				<a href="view?complaint={id}&amp;status=conclusion"><strong>View</strong></a> | <a href="resume?complaint={id}&amp;status=conclusion"><strong>Edit</strong></a> Conclusion<br />
			</xsl:when>
			<xsl:when test="conclusionStatus='false'">
				<a href="add?"><strong>Add</strong></a> Conclusion<br />
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
	</table>
	
	<br />
	<input type="submit" value="Submit" onclick="buttonPress('submit');" />
			
	</xsl:template>
	
</xsl:stylesheet>