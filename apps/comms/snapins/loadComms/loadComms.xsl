<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="loadComms">
	
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td style="padding-right: 5px;">ID:</td>
				<td style="padding-right: 5px;">
					<input autocomplete="off" type="text" name="commsId" id="commsId" class="required" /> 
					<div class="auto_complete" id="report_auto_complete">-</div>
					
					<script type="text/javascript" language="javascript" charset="utf-8">
						<![CDATA[		
							new Ajax.Autocompleter('commsId', 'report_auto_complete', '/apps/comms/ajax/reportautocomplete', {})
						]]>
					</script>
				</td>
				<td><input type="submit" value="Load" /></td>
			</tr>
			<tr><td colspan="3"><hr size="1" noshade="noshade" /></td></tr>
			
			<xsl:apply-templates select="articleList" />

		</table>	
		
	</xsl:template>
	
	<xsl:template match="articleList">
		<tr style="padding: 2px">
			<td colspan="3" ><img src="/images/icons1515/news.png" align="absmiddle" /><a href="./viewArticle?id={articleId}"><xsl:value-of select="articleSubject" /></a></td>
		</tr>
	</xsl:template>
    
</xsl:stylesheet>