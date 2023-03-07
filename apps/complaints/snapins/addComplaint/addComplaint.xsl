<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsAdd">
	
		<table border="0" cellpadding="2" cellspacing="2">
		
			<tr>
				<td><a href="http://{@server}/apps/customerComplaints/add?stage=complaint">{TRANSLATE:add_customer_complaint}</a></td>
			</tr>
			<tr>
				<td><a href="http://{@server}/apps/complaints/add?typeOfComplaint=supplier_complaint">{TRANSLATE:add_supplier_complaint}</a></td>
			</tr>
			<tr>
				<td><a href="http://{@server}/apps/complaints/add?typeOfComplaint=quality_complaint">{TRANSLATE:add_internal_complaint}</a></td>
			</tr>
				
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>