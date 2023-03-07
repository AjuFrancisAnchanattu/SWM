<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="weather">
	
		<p style="margin: 0;">
		
			<table width="260">
				<tr>
					<td width="50"><img src="{iconData}" alt="{conditionData}" /></td>
					<td width="210"><strong><xsl:value-of select="cityData" /></strong><br /><xsl:value-of select="conditionData" /><br /><xsl:value-of select="tempData" /> C</td>
				</tr>
			</table>

			<table width="260" cellpadding="2" cellspacing="2">
				<tr>
					<td width="86" style="background-color: #F5F5F5">
						<div align="center">
						<strong><xsl:value-of select="dayOfWeek1" /></strong><br />
						<img src="{iconData1}" alt="{conditionData1}" /><br />
						<xsl:value-of select="conditionData1" /><br /><xsl:value-of select="tempData1" /> C
						</div>
					</td>
					<td width="86" style="background-color: #F5F5F5">
						<div align="center">
						<strong><xsl:value-of select="dayOfWeek2" /></strong><br />
						<img src="{iconData2}" alt="{conditionData2}" /><br />
						<xsl:value-of select="conditionData2" /><br /><xsl:value-of select="tempData2" /> C
						</div>
					</td>
					<td width="88" style="background-color: #F5F5F5">
						<div align="center">
						<strong><xsl:value-of select="dayOfWeek3" /></strong><br />
						<img src="{iconData3}" alt="{conditionData3}" /><br />
						<xsl:value-of select="conditionData3" /><br /><xsl:value-of select="tempData3" /> C
						</div>
					</td>
				</tr>
			</table>		
		</p>
		
	</xsl:template>
	
</xsl:stylesheet>