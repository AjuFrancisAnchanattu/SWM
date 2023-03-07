<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="myPerformance">
	
		
		<!--<div class="snapin_bevel_1"><div class="snapin_bevel_2"><div class="snapin_bevel_3"><div class="snapin_bevel_4">
		
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td><a href="#" onclick="Javascript:window.open('../apps/help/window/helpWindow?type=snapin&amp;app={snapin_name}','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')">{TRANSLATE:what_is_this}</a></td>
				</tr>
			</table>
		
		</div></div></div></div>-->
		
		<!--<div style="padding-top: 10px;">-->
	
	
		<table cellspacing="2" width="260">
			<tr>
				<td><a href="/apps/appraisal/appraisalRedirect?"><img src="/images/myPerformanceLogo.gif" align="absmiddle" /></a></td>
			</tr>
			<tr>
				<td>
				<ol style="padding: 0px; margin: 0px; line-height: 21px;">
					<li><strong>{TRANSLATE:my_performance_help_support}</strong></li>
					<li>1. <a href="/apps/documentLinks/retrieve?docId=175" target="_blank">{TRANSLATE:overview_of_system_and_workflow}</a></li>
						<ul style="margin-left: 25px;">
							<li>1.1 <a href="/apps/video/viewVideo?id=27">{TRANSLATE:view_video}</a></li>
						</ul>
					<li>2. Performance Objectives</li>
						<ul style="margin-left: 25px;">
							<li>2.1 <a href="/apps/documentLinks/retrieve?docId=179" target="_blank">{TRANSLATE:how_to_create_perf_ob}</a></li>
							<li>2.2 <a href="/apps/documentLinks/retrieve?docId=181" target="_blank">{TRANSLATE:how_to_create_dev_ob}</a></li>
							<li>2.3 <a href="/apps/documentLinks/retrieve?docId=183" target="_blank">{TRANSLATE:how_to_agree_perf_ob}</a></li>
						</ul>
					<li>3. <a href="/apps/documentLinks/retrieve?docId=185" target="_blank">{TRANSLATE:employee_files}</a></li>
					<li>4. <a href="/apps/documentLinks/retrieve?docId=187" target="_blank">{TRANSLATE:company_information}</a></li>
					<li>5. <a href="/apps/documentLinks/retrieve?docId=189" target="_blank">{TRANSLATE:mid_year_review}</a></li>
					<li>6. <a href="/apps/documentLinks/retrieve?docId=191" target="_blank">{TRANSLATE:year_end_review}</a></li>
					<li>7. <a href="/apps/documentLinks/retrieve?docId=193" target="_blank">{TRANSLATE:reporting}</a></li>
					<li>8. <a href="/apps/documentLinks/retrieve?docId=415" target="_blank">What you need to do by 31st May 2010</a></li>
				</ol>
				</td>
			</tr>
		</table>
		
		<!--</div>-->

	</xsl:template>
	
</xsl:stylesheet>