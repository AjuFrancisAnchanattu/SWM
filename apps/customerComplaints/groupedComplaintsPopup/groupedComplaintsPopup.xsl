<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="groupedComplaintsPopup">
	<html>
		<head>
			<style type="text/css">
				body
				{
					font-family: "Arial", "Verdana", sans-serif;
					text-align: center;
					padding: 5px 0 0 0;
					margin: 0;
				}
				#content
				{
					width: 600px;
				}
				#title
				{
					text-align: left;
					padding: 2px 0 2px 10px;
					font-size: 0.775em;
					background: #c40204;
					color: #FFFFFF;
					font-variant: small-caps;
				}
				#top_info
				{
					font-size: 0.775em;
					text-align: left;
					padding: 5px 5px 5px 9px;
					border-left: #bbbbbb 1px solid;
					border-right: #bbbbbb 1px solid;
					border-bottom: #bbbbbb 1px solid;
				}
				#complaints
				{
					margin: 2px;
					text-align: center;
				}
				#complaints tr.hover
				{
					background: #dddddd;
					cursor: pointer;
				}
				#complaints th
				{
					font-size: 0.775em;
					padding: 5px;
					font-variant: small-caps;
					border-bottom: #bbbbbb 2px solid;
				}
				#complaints td
				{
					font-size: 0.75em;
					padding: 10px 5px;
					border-bottom: #bbbbbb 1px solid;
				}
				#complaints td.status
				{
					font-variant: small-caps;
				}
				#tableContainer
				{
					height: 300px;
					overflow-y: auto;
					border: #bbbbbb 1px solid;
					margin-top: 5px;
				}
				
			</style>
			<script>
				function openComplaint(id)
				{
					var url = "http://" + window.location.hostname + "/apps/customerComplaints/index?complaintId=" + id;
					parent.location = url;
				}
			</script>
		</head>
		
		<body>
			<div id="content">
				<div id="title">
					<b>{TRANSLATE:complaints_grouped_with_complaint}:</b> <span style="margin-left: 10px"><xsl:value-of select="complaintId"/></span>
				</div>
				<div id="top_info">
					<b>{TRANSLATE:sap_customer_no}:</b> <span style="margin-left: 10px"><xsl:value-of select="sapNumber"/></span> <span style="margin-left: 10px; font-style: italic;">(<xsl:value-of select="sapName" />)</span>
				</div>
				
				<div id="tableContainer">
					<xsl:apply-templates select="complaints" />
				</div>
			</div>
		</body>
		
	</html>
</xsl:template>

<xsl:template match="complaints">
	<table id="complaints" cellspacing="0" cellpadding="0">
	
		<tr>
			<th>{TRANSLATE:id}</th>
			<th>{TRANSLATE:submission_date}</th>
			<th width="25%">{TRANSLATE:owner}</th>
			<th width="25%">{TRANSLATE:evaluation_owner}</th>
			<th width="20%">{TRANSLATE:complaint_value}</th>
			<th>{TRANSLATE:status}</th>
		</tr>
		
		<xsl:for-each select="complaint">
			<tr onmouseover="this.className='hover';" onmouseout="this.className='';" onclick="openComplaint('{id}');">
				<td> <xsl:value-of select="id" /> </td>
				<td> <xsl:value-of select="submissionDate" /> </td>
				<td> <xsl:value-of select="complaintOwner" /> </td> 
				<td> <xsl:value-of select="evaluationOwner" /> </td>
				<td> <xsl:value-of select="complaintValue" /><xsl:text> </xsl:text><xsl:value-of select="complaintCurrency" /> </td>
				<td class="status">
					<xsl:choose>
						<xsl:when test="complaintClosed">
							<span style="color: red;"> {TRANSLATE:closed} </span>
						</xsl:when>
						<xsl:otherwise>
							<span style="color: blue;"> {TRANSLATE:open} </span>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</xsl:for-each>
		
	</table>
</xsl:template>

</xsl:stylesheet>