<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="employeedb.xsl"/>
	
	<xsl:template match="employee">
	
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p>{TRANSLATE:summary}</p>
		</div></div></div></div>
		
		<table width="100%" cellspacing="0" cellpadding="4" class="indented">
			<tr>
				<td class="row_name valid_row" width="28%">Database Id</td>
				<td class="valid_row"><xsl:value-of select="id"/></td>
			</tr>
			<tr>
				<td class="row_name valid_row" width="28%">{TRANSLATE:full_name}</td>
				<td class="valid_row"><xsl:value-of select="fullName"/></td>
			</tr>
			<tr>
				<td class="row_name valid_row" width="28%">{TRANSLATE:site}</td>
				<td class="valid_row"><xsl:value-of select="site"/></td>
			</tr>
			<tr>
				<td class="row_name valid_row" width="28%">{TRANSLATE:job_title}</td>
				<td class="valid_row"><xsl:value-of select="jobTitle"/></td>
			</tr>
			
		</table>
		
		<br />

	
	</xsl:template>
		
	
</xsl:stylesheet>