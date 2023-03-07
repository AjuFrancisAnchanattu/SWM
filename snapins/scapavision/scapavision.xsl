<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="scapavision">
	
		<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="0" cellspacing="0" width="98%">
				<tr>
					<td>
					
					<table width="100%">
						<tr>
							<td width="60%"><div align="left"><a href="/apps/comms/scapaVision?">{TRANSLATE:learn_more}</a> | <a href="/apps/comms/leanSixSigma?">{TRANSLATE:lean_six_sigma}</a> | <a href="/apps/comms/faq?">{TRANSLATE:frequently_asked_questions}</a></div></td>
							<td width="40%"><div align="right"><img src="/images/icons2020/copy.jpg" style="margin-right: 4px;" align="absmiddle" /><!--<a href="/apps/comms/viewAllArticles?">{TRANSLATE:scapa_news}</a> | --><a href="/apps/comms/askAQuestion?type=askAQuestion">{TRANSLATE:ask_a_question}</a> | <a href="/apps/comms/askAQuestion?type=askAQuestion&amp;subjectStory=Got A Story: ">{TRANSLATE:got_a_story}</a></div></td>
						</tr>
					</table>
					
					</td>
				</tr>
			</table>
		
		</div></div></div></div>
		
		<div style="padding-top: 10px;">
	
			<table cellspacing="0" width="98%" style="background: #FFFFFF; border: 1px solid #000000; padding: 10px;" align="absmiddle">
				<tr>
					<td>
					
						<!--<div id="TipLayer" style="visibility:hidden;position:absolute;z-index:100;top:-100">-</div>
						<script language="Javascript" src="/javascript/style.js" type="text/javascript">-</script>
						<p style="line-height: 19px;"><strong>SCAPA VISION</strong><br />"<a href="#" onMouseOver="stm(Text[14],Style[12])" onMouseOut="htm()" style="text-decoration: none;"><u>World class</u></a>, <a href="#" onMouseOver="stm(Text[15],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">inspired</a>, <a href="#" onMouseOver="stm(Text[16],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">market driven </a>  <a href="#" onMouseOver="stm(Text[17],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">team</a>, focused on optimising customer &amp; shareholder <a href="#" onMouseOver="stm(Text[18],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">value</a> through <a href="#" onMouseOver="stm(Text[19],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">responsible</a>, <a href="#" onMouseOver="stm(Text[20],Style[13])" onMouseOut="htm()" style="text-decoration: none; border-bottom:1px dotted #000000;">agile</a> delivery of specialist <a href="#" onMouseOver="stm(Text[21],Style[13])" onMouseOut="htm()" style="text-decoration: none;"><u>tape solutions</u></a>".</p>
						-->
						
						<a href="/apps/comms/scapaVision?"><img src="/images/One_Scapa_Logo2.jpg" align="absmiddle" vspace="10" /></a>
					</td>
					<td style="padding-left: 10px; padding-right: 10px">
						
						<xsl:choose>
							<xsl:when test="translate='true'">
								<p style="line-height: 19px; font-size: 14px;">{TRANSLATE:scapa_translated_vision}</p>							
							</xsl:when>
							<xsl:otherwise>
								<p style="line-height: 19px; font-size: 14px;">"<a href="/apps/comms/scapaVision?" class="vision" title="{world_class_description}" style="" ><b>{TRANSLATE:world_class}</b></a>, <a href="/apps/comms/scapaVision?" class="vision" title="{inspired_description}" style="">{TRANSLATE:inspired}</a>, <a href="/apps/comms/scapaVision?" class="vision" title="{market_driven_description}" style="">{TRANSLATE:market_driven} </a> <a href="/apps/comms/scapaVision?" class="vision" title="{team_description}" style="" >{TRANSLATE:team}</a>, {TRANSLATE:focused_on_optimising} <a href="/apps/comms/scapaVision?" class="vision" title="{value_description}">{TRANSLATE:value}</a> {TRANSLATE:through} <a href="/apps/comms/scapaVision?" class="vision" title="{responsible_description}">{TRANSLATE:responsible}</a>, <a href="/apps/comms/scapaVision?" class="vision" title="{agile_description}">{TRANSLATE:agile}</a> {TRANSLATE:delivery_of_specialist} <a href="/apps/comms/scapaVision?" class="vision" title="{tape_solutions_description}" style="" ><b>{TRANSLATE:tape_solutions}</b></a>."</p>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</table>
			
		</div>

	
	</xsl:template>
    
</xsl:stylesheet>