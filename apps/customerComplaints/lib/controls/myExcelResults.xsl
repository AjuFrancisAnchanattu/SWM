<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	
		
	<xsl:template match="excel">
		<html>
			<head>
			<style type="text/css">
				td {border:1px dotted #000;}
			</style>
			</head>
			<body>
			
				<table cellspacing="0" border="0" style="border-collapse:collapse;">
				<xsl:apply-templates select="excelrow"/>	
				</table>
				
			</body>
		</html>
	</xsl:template>
	
	
	<xsl:template match="excelrow">		
		<tr>
			<xsl:apply-templates select="link"/>
			<xsl:apply-templates select="excelth"/>	
			<xsl:apply-templates select="exceltd"/>
		</tr>
	</xsl:template>
	
	
	
	
	<xsl:template match="excelth">
		<th filter="ALL"><xsl:value-of select="text()" /></th>
	</xsl:template>
	
	
	<xsl:template match="exceltd">
		<td><xsl:value-of select="text()" /></td>
	</xsl:template>
	
	<xsl:template match="link">
		<td><a href="/apps/customerComplaints/index?complaintId={linkID}" target="_blank"><xsl:value-of select="linkID" /></a></td>
	</xsl:template>

</xsl:stylesheet>