<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccLoad">
		
		<style>
			.complaint_old
			{
				background: #E5FFE5;
				border: 1px #000000 solid;
				border-bottom: none;
			}
			
			.complaint_new
			{
				background: #ffffff;
				border: 1px #000000 solid;
				border-bottom: none;
			}
			
			.load_results_summary
			{
				background: #AAAAAA;
				border: 1px #000000 solid;
				border-top: 3px #000000 solid;
			}
			
			.load_results_legend
			{
				margin: 5px;
				padding: 3px;
				border: 1px #ffffff solid;
			}
			
			.load_results_legend_color
			{
				display: block;
				float: left;
				width: 10px;
				height: 10px;
				margin-right: 5px;				
			}
			
			.load_results_legend_text
			{
				color: white;
				margin-top: -2px;
			}
		</style>
		
		<div style="float: left; padding-top: 5px; margin-right: 5px;">ID:</div>
				
		<input style="width: 183px; margin-right: 5px;" autocomplete="off" type="text" name="loadId" id="loadId" class="required" /> 
		<div class="auto_complete" id="report_auto_complete">-</div>
		
		<script type="text/javascript" language="javascript" charset="utf-8">
			<![CDATA[		
				new Ajax.Autocompleter('loadId', 'report_auto_complete', '/apps/customerComplaints/ajax/complaintIdOldAndNew', {})
			]]>
		</script>
		
		<input type="submit" value="Load" />
	
		<p style="margin: 5px 0;">{TRANSLATE:please_note_load}</p>
		
	</xsl:template>
	
</xsl:stylesheet>