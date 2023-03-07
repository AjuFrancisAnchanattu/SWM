<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="lottery">
	
			Last Draw: <xsl:value-of select="lotteryDate" /><br/>
			<table cellspacing="0" width="260">
			<tr>
				<xsl:apply-templates select="lotteryBall" />
				<xsl:apply-templates select="lotteryBonusBall" />
				</tr>
			</table>

	
	</xsl:template>
	
	
	<xsl:template match="lotteryBall">
		<td><img src="/images/snapins/lottery/euro_balls_home_{text()}.PNG" /></td>
	</xsl:template>
	
	<xsl:template match="lotteryBonusBall">
		<td><img src="/images/snapins/lottery/euro_bonus_home_{text()}.PNG" /></td>
	</xsl:template>

</xsl:stylesheet>