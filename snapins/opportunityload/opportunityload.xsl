<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="opportunityload">
	
		<table border="0">
			<tr>
				<td>ID:</td>
				<td>
					<input autocomplete="off" type="text" name="opportunity" id="opportunity" class="required" /> 
					<div class="auto_complete" id="opportunity_auto_complete">-</div>
					
					<script type="text/javascript" language="javascript" charset="utf-8">
						<![CDATA[		
							new Ajax.Autocompleter('opportunity', 'opportunity_auto_complete', '/snapins/ccrload/opportunityautocomplete', {})
						]]>
					</script>
				</td>
				<td><input type="submit" value="Load" /></td>
			</tr>
		</table>
		
	</xsl:template>
	
</xsl:stylesheet>