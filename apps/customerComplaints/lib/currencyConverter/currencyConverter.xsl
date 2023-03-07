<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template match="currencyConverter">
	<html>
		<head>
			<script type="text/javascript" src="/apps/customerComplaints/lib/currencyConverter/currencyConverter.js">-</script>
			
			<style type="text/css">
				body
				{
					font-family: "Arial", "Verdana", sans-serif;
					text-align: center;
					margin: 0;
					padding: 5 0 0 0;
				}
				#content
				{
					width: 210px;
				}
				#title
				{
					text-align: left;
					padding: 2px 0 2px 10px;
					font-size: 0.775em;
					background: #c40204;
					color: #FFFFFF;
					font-variant: small-caps;
				}
				#top_info
				{
					font-size: 0.775em;
					text-align: left;
					padding: 5px 5px 5px 9px;
					margin-bottom: 5px;
					border-left: #bbbbbb 1px solid;
					border-right: #bbbbbb 1px solid;
					border-bottom: #bbbbbb 1px solid;
				}
				#error
				{
					width: 100%;
					text-align: center;
					display: none; 
					color: red; 
					font-size: 0.8em;
					font-weight: bold;
				}
				#value
				{
					width: 70%;
				}
				#bottom_info
				{
					border: 1px #bbbbbb solid;
					text-align: center;
				}
				#calculations
				{
					font-size: 0.8em;
					text-align: left;
				}
				#footer
				{
					text-align: center;
					font-size: 0.7em;
					color: #444444;
				}
				tr.hover
				{
					background: #dddddd;
					cursor: pointer;
				}
			</style>
		</head>
		
		<body>
			<div id="content">
				<div id="title">
					{TRANSLATE:currency_calculator}
				</div>
				<div id="top_info">
					<div>
						<input type="text" id="value" onChange="calculate();"/>
						<select id="currency" onChange="calculate();">
							<xsl:apply-templates select="exchangeRates" />
						</select>
					</div>
					<div id="error">
						{TRANSLATE:value_error}
					</div>
				</div>
				<div id="bottom_info">
					<table id="calculations">
					</table>
				</div>
				<div id="footer">
					<xsl:choose>
						<xsl:when test="exchangeRatesType = 'budget'">
							*{TRANSLATE:budget_rates}
						</xsl:when>
					</xsl:choose>
				</div>
			</div>
		</body>
		
		<xsl:if test="currency">
			<script>
				setDropdownCurrencyValue('<xsl:value-of select="currency" />');
			</script>
		</xsl:if>
		
		<xsl:if test="value">
			<script>
				document.getElementById("value").value = <xsl:value-of select="value" />;
				
				calculate();
			</script>
		</xsl:if>
	</html>
</xsl:template>

<xsl:template match="exchangeRates">
	<xsl:for-each select="exchangeRate">
		<option value="{@value}">
			<xsl:value-of select="@currency" />
		</option>
	</xsl:for-each>
</xsl:template>

</xsl:stylesheet>