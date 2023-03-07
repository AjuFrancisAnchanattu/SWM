<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>
	
	<xsl:template match="ceoAwards">		
		
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
								<img src="../../images/famIcons/application_form_edit.png" alt="" class="titleIcon" style="display: block; float: left;" />
								<p style="margin: 0 0 0 6px; float: left; width: 500px; font-weight: bold; color: #FFFFFF;">CEO Awards 2011 - Submit Request For Application Form</p>
							</div></div>
						</div></div></div></div>
					</div>
					
					<xsl:if test="userSubmitted">
						<p style="padding: 10px;">You have already submitted a CEO Awards Entry Form.</p>
					</xsl:if>
					
					<xsl:apply-templates select="error" />
					
					<xsl:apply-templates select="ceoAwardsForm" />							
				</td>
			</tr>
		</table>		
		
		<script type="text/javascript">
				
			
				var submitButton = document.getElementById('submitGroupGroup').getElementsByTagName("tr")[0].getElementsByTagName("td")[0].getElementsByTagName("input")[0];
				
				submitButton.disabled = true;
				
				var checkboxArray = new Array();
					
				document.getElementsByName('innovation')[0].onclick = function(){	
					checkChecked();										
				};
				document.getElementsByName('continuousImprovement')[0].onclick = function(){	
					checkChecked();										
				};
				document.getElementsByName('serviceExcellence')[0].onclick = function(){	
					checkChecked();										
				};
				
				
				function checkChecked()
				{
					if (document.getElementsByName('innovation')[0].checked 
						|| document.getElementsByName('continuousImprovement')[0].checked 
						|| document.getElementsByName('serviceExcellence')[0].checked )
					{
						var currentDateTime = new Date();
						var deadline = new Date(2011,11,23,8,0,0,0);
						
						if (currentDateTime <xsl:text disable-output-escaping="yes">&gt;</xsl:text> deadline)
						{
							submitButton.disabled = true;
						}
						else
						{
							submitButton.disabled = false;
						}
					}
					else
					{
						submitButton.disabled = true;
					}
				}			
				
				document.onload = checkChecked();
				
		
			
		</script>		
		
	</xsl:template>	
	
	<xsl:template match="ceoAwardsForm">
		<xsl:apply-templates select="form" />
	</xsl:template>	
	
</xsl:stylesheet>