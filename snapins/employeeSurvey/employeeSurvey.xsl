<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="employeeSurvey">
	
		<table cellspacing="2" width="260">
			<tr>
				<td><img src="/images/enews.png" align="absmiddle" /></td>
			</tr>
			<tr>
				<td><strong>Welcome to the Scapa Monthly Employee News.</strong><br /><br />Please click below to read May's news update in your local language:</td>
			</tr>
			<tr>
				<td>
				<br />
				<ol style="padding: 0px; margin: 0px; line-height: 19px;">
					<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=675" target="_blank">English</a></li>
					<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=671" target="_blank">French</a></li>
					<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=673" target="_blank">German</a></li>
					<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=677" target="_blank">Italian</a></li>
				</ol>
				</td>
			</tr>
			<!--<tr>
				<td>
				<br />
				<a href="http://scapanet/apps/ceoAwards">CEO Awards</a>-->
				<!--<hr />-->
				<!--</td>
			</tr>-->
			<!--<tr>
				<td><strong>Coming soon:</strong><br /><br />July's Employee News will be published on Friday 29th July</td>
			</tr>-->
		</table>

	</xsl:template>
	
</xsl:stylesheet>