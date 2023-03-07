<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccrload">
	
		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td style="padding-right: 5px;">ID:</td>
				<td style="padding-right: 5px;">
					<input autocomplete="off" type="text" name="report" id="report" class="required" /> 
					<div class="auto_complete" id="report_auto_complete">-</div>
					
					<script type="text/javascript" language="javascript" charset="utf-8">
						<![CDATA[		
							new Ajax.Autocompleter('report', 'report_auto_complete', '/apps/ccr/ajax/reportautocomplete', {})
						]]>
					</script>
				</td>
				<td><input type="submit" value="Load" onclick="buttonPress('submit');" /></td>
			</tr>
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>