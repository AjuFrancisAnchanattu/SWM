<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="employeedbload">
	
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-right: 5px;">Name:</td>
				<td style="padding-right: 5px;">
					<input autocomplete="off" type="text" name="employee" id="employee" class="required" /> 
					<div class="auto_complete" id="employee_auto_complete">-</div>
					
					<script type="text/javascript" language="javascript" charset="utf-8">
						<![CDATA[		
							new Ajax.Autocompleter('employee', 'employee_auto_complete', '/apps/employeedb/ajax/employee?key=employee', {})
						]]>
					</script>
				</td>
				<td><input type="submit" value="Load" /></td>
			</tr>
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>