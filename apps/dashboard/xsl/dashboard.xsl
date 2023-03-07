<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="dashboard">
	
	</xsl:template>
	
	<xsl:template match="dashboardHome">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
				
					{TRANSLATE:dashboard}
					
				</td>
			</tr>
		</table>	
		
		
		<script type="text/javascript" language="javascript" charset="utf-8">
		<![CDATA[
		
		
			Sortable.create('snapin_left_container',
			{
				tag:'div',
				handle:'snapin_top',
				onUpdate:function(element)
				{
					new Ajax.Request('/home/moveDashboardSnapin',
					{
						parameters:Sortable.serialize('snapin_left_container',
						{
							tag:'div',
							name:'snapins'
						}),
						asynchronous:true
					})
				}
			});
			Sortable.create('snapin_right_container',
			{
				tag:'div',
				handle:'snapin_top',
				onUpdate:function(element)
				{
					new Ajax.Request('/home/moveDashboardSnapin',
					{
						parameters:Sortable.serialize('snapin_right_container',
						{
							tag:'div',
							name:'snapins'
						}),
						asynchronous:true
					})
				}
			});
		]]>
		</script>	
		
	</xsl:template>	
	
</xsl:stylesheet>