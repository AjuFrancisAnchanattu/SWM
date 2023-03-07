<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainSAO">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
	
		<table cellspacing="2" width="260">
			
			<!-- options go here 
			
			<tr>
				<td colspan="2"><input type="submit" name="action" id="action" value="Submit" /></td>
			</tr>  -->
			
		</table>
		
		</xsl:when>
		
		<xsl:otherwise>
			<div class="red_notification">
				<h1><strong>{TRANSLATE:access_denied}</strong></h1>
			</div>
		</xsl:otherwise>
	</xsl:template>

</xsl:stylesheet>