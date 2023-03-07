<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="complaintsAdd">
	
	
	<table border="0" cellpadding="2" cellspacing="2">
	
			
	<!--<tr>
				<td style="padding-right: 5px;">Type:
				
					<select name="typeValue" id="typeValue" class="required">
						<option value="customer_complaint">Customer Complaint</option>
						<option value="supplier_complaint">Supplier Complaint</option>
					</select>

				</td>
				<td><input type="submit" value="Add" /></td>
			</tr>-->
		<tr>
			<td><a href="add?">{TRANSLATE:add_customer_complaint}</a></td>
		</tr>
		<tr>
			<td><a href="add?typeOfComplaint=supplier_complaint">{TRANSLATE:add_supplier_complaint}</a></td>
		</tr>
		<tr>
			<td><a href="add?typeOfComplaint=quality_complaint">{TRANSLATE:add_internal_complaint}</a></td>
		</tr>
			
			
		</table>
		
	
		
		
	</xsl:template>
	
</xsl:stylesheet>