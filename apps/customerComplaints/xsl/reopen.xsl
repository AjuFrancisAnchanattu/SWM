<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="ccReopen">		
		
		<script type="text/javascript" src="/apps/customerComplaints/lib/LightBox/LightBox_v2.js">-</script>
		
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
								
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
					
				</td>
	
				<td valign="top" style="padding: 10px;">
					
					<div class="title-box1">
						<div class="left-top-corner"><div class="right-top-corner"><div class="right-bot-corner"><div class="left-bot-corner">
							<div class="inner"><div class="wrapper">
								<img src="../../images/famIcons/application_go.png" alt="" class="titleIcon" style="float: left;" />
								<p style="margin: 0 0 0 3px; font-weight: bold; color: #FFFFFF; float: left;">
									{TRANSLATE:reopen}
								</p>
							</div></div>
						</div></div></div></div>
					</div>
					<p style="border-bottom: 1px solid #ccc; padding: 10px; margin: 0 5px; background: #DFDFDF;">
						{TRANSLATE:reopen_info}:
					</p>
					<xsl:apply-templates select="form" />

				</td>
			</tr>
		</table>		
		
		<script type="text/javascript">
				
			<![CDATA[
				var button = document.getElementById('submitGroupGroup').getElementsByTagName("tr")[1].getElementsByTagName("td")[0].getElementsByTagName("input")[0];
				button.style.marginLeft = "5px";
				
				var submitButton = document.getElementById('submitGroupGroup').getElementsByTagName("tr")[0].getElementsByTagName("td")[0].getElementsByTagName("input")[0];
				submitButton.style.marginRight = "5px";
		
				var targetTd = document.getElementById('submitGroupGroup').getElementsByTagName("tr")[0].getElementsByTagName("td")[0];
		
				targetTd.appendChild( button);
		
				var trToRemove = document.getElementById('submitGroupGroup').getElementsByTagName("tr")[1];
				trToRemove.parentNode.removeChild( trToRemove);
				
				submitButton.disabled = true;
				
				var checkboxArray = new Array();
					
				document.getElementsByName('reopenCorrectiveAction')[0].onclick = function(){	
					checkChecked();										
				};
				document.getElementsByName('reopenValidationVerification')[0].onclick = function(){	
					checkChecked();										
				};
				document.getElementsByName('reopenCreditAuthorisation')[0].onclick = function(){	
					checkChecked();										
				};
				
				
				function checkChecked()
				{
					if (document.getElementsByName('reopenCorrectiveAction')[0].checked 
						|| document.getElementsByName('reopenValidationVerification')[0].checked 
						|| document.getElementsByName('reopenCreditAuthorisation')[0].checked )
					{
						submitButton.disabled = false;
					}
					else
					{
						submitButton.disabled = true;
					}
				}			
				
				document.onload = checkChecked();
				
			]]>
			
		</script>
	</xsl:template>
	
</xsl:stylesheet>