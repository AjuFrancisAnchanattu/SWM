<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="docman.xsl"/>
	
	<xsl:template match="DocManHierarchy">
	<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">		
					<h1>Document Hierarchy</h1>	
					<ul>
						<xsl:apply-templates select="hierarchy" />
					</ul>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="hierarchy">
		
		<!-- 
		Just for a test.  AJAX is needed for the tree menu.
		-->
		<div id="docmanhier" class="docmanhier">
			<li><a href="#" onclick="showTreeMenu(this.value)"><xsl:value-of select="docCategory" /></a></li>
		</div>
	
	</xsl:template>
	
</xsl:stylesheet>