<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="comms.xsl"/>
	
	<xsl:template match="commAskAQuestion">
	
		<script language="Javascript" src="/apps/comm/javascript/material_quantity.js" type="text/javascript">-</script>
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">					
					
				<div id="snapin_left_container">
					<xsl:apply-templates select="snapin_left" />
				</div>
				
				</td>
			
				<td valign="top" style="padding: 10px;">
				
					<xsl:apply-templates select="error" />
				
					<xsl:apply-templates select="commReport" />
					
					
				</td>
			</tr>

		</table>
		
		<xsl:if test="whichAnchor">
			<script language="javascript">
				document.onload = moveToWhere();
				function moveToWhere(){
					var curtop = 0;
					var obj = document.getElementById('<xsl:value-of select="whichAnchor"/>');
					if (obj.offsetParent) {
						do {
							curtop += obj.offsetTop;
						} while (obj = obj.offsetParent);
					}
					window.scrollTo(0,(curtop-500));
				}
			</script>
		</xsl:if>
		
	</xsl:template>
	
	<xsl:template match="commReport">
		<div class="heading_top"><div class="heading_top_1"><div class="heading_top_2"><div class="heading_top_3">
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:<xsl:value-of select="form/@name"/>_ask_a_question}</p>
		</div></div></div></div>
		
		<div style="padding: 5px;">
		<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
		
		<p><strong>{TRANSLATE:welcome_to_ask_a_question}</strong></p>
		
		<p>{TRANSLATE:welcome_to_ask_a_question_2}</p>
		<p>{TRANSLATE:welcome_to_ask_a_question_3}</p>
		<p>{TRANSLATE:welcome_to_ask_a_question_4}</p>
		
		</div>
		</div>
		
		<xsl:apply-templates select="form" />
		
	</xsl:template>

</xsl:stylesheet>