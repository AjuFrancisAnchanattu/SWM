<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="appsSnapin">
		<table border="0" cellpadding="0" cellspacing="0" width="260px">

			<tr>
				<td colspan="2">To list Application helps, click below</td>
			</tr>
			
			<tr>
				<td colspan="2">
					<hr noshade="noshade" size="1" />
				</td>
			</tr>
			
			<xsl:for-each select="appFolder">
				<tr>
					<td>
						<a href="./index?type={appFolderName}"><xsl:value-of select="appFolderName" /></a>
					</td>
					<td>
						{TRANSLATE:<xsl:value-of select="appFolderName" />}
					</td>
				</tr>
			</xsl:for-each>
		
		</table>	
	</xsl:template>
</xsl:stylesheet>