<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainCashPosition">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
		
		<script type="text/javascript">
						
			function TickAll()
			{
				this.document.getElementById("UK/PLC").checked = 1;
				this.document.getElementById("France").checked = 1;
				this.document.getElementById("Schweiz").checked = 1;
				this.document.getElementById("Italy").checked = 1;
				this.document.getElementById("Germany").checked = 1;
				this.document.getElementById("Spain").checked = 1;
				this.document.getElementById("Benelux").checked = 1;
				this.document.getElementById("USA1").checked = 1;
				this.document.getElementById("USA2").checked = 1;
				this.document.getElementById("CAN1").checked = 1;
				this.document.getElementById("CAN2").checked = 1;
				this.document.getElementById("Suzhou").checked = 1;
				this.document.getElementById("SSITCO").checked = 1;
				this.document.getElementById("Hong Kong").checked = 1;
				this.document.getElementById("Korea").checked = 1;
				this.document.getElementById("Malaysia").checked = 1;
				this.document.getElementById("Group").checked = 1;
				this.document.getElementById("DEBT").checked = 1;
			}
			
			function UntickAll()
			{
				this.document.getElementById("UK/PLC").checked = 0;
				this.document.getElementById("France").checked = 0;
				this.document.getElementById("Schweiz").checked = 0;
				this.document.getElementById("Italy").checked = 0;
				this.document.getElementById("Germany").checked = 0;
				this.document.getElementById("Spain").checked = 0;
				this.document.getElementById("Benelux").checked = 0;
				this.document.getElementById("USA1").checked = 0;
				this.document.getElementById("USA2").checked = 0;
				this.document.getElementById("CAN1").checked = 0;
				this.document.getElementById("CAN2").checked = 0;
				this.document.getElementById("Suzhou").checked = 0;
				this.document.getElementById("SSITCO").checked = 0;
				this.document.getElementById("Hong Kong").checked = 0;
				this.document.getElementById("Korea").checked = 0;
				this.document.getElementById("Malaysia").checked = 0;
				this.document.getElementById("Group").checked = 0;
				this.document.getElementById("DEBT").checked = 0;
			}
	        
	    </script>
	
		<table cellspacing="2" width="260">
			
			<xsl:if test="addAllowed='1'">
				<xsl:choose>
					<xsl:when test="multipleBanks=1">
						<tr>
							<td>
								<ol style="padding: 0px; margin: 0px; line-height: 19px;">
									<xsl:for-each select="addLevel">
										<li><img src="/images/arrow.gif" align="absmiddle" /><a href="{addLink}">{TRANSLATE:add_cash_report} (<xsl:value-of select="region" />)</a></li>
									</xsl:for-each>
									<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=397" target="_blank">{TRANSLATE:help}</a></li>
								</ol>
							</td>
						</tr>	
						<tr>
							<td><hr /></td>
						</tr>
					</xsl:when>
					<xsl:otherwise>
						<tr>
							<td>
								<ol style="padding: 0px; margin: 0px; line-height: 19px;">
									<li><img src="/images/arrow.gif" align="absmiddle" /><a href="{addLink}">{TRANSLATE:add_cash_report} (<xsl:value-of select="region" />)</a></li>
									<li><img src="/images/arrow.gif" align="absmiddle" /><a href="/apps/documentLinks/retrieve?docId=397" target="_blank">{TRANSLATE:help}</a></li>
								</ol>
							</td>
						</tr>	
						<tr>
							<td><hr /></td>
						</tr>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			
			<tr>
				<td>{TRANSLATE:select_banks_to_view}</td>
			</tr>
			
			<tr>
				<td><a href="#cashTop" onclick="javascript:TickAll();">{TRANSLATE:tick_all}</a> | <a href="#cashTop" onclick="javascript:UntickAll();">{TRANSLATE:untick_all}</a></td>
			</tr>
			
			<xsl:for-each select="bankNameItem">
				<tr>
					<td>
						<xsl:choose>
							<xsl:when test="checked='true'">
								<input id="{bankName}" name="{bankName}" type="checkbox" checked="" />
							</xsl:when>
							<xsl:otherwise>
								<input id="{bankName}" name="{bankName}" type="checkbox" />
							</xsl:otherwise>
						</xsl:choose>
						
						<xsl:value-of select="bankName" />
					</td>
				</tr>
			</xsl:for-each>
			
			<tr>
				<td><input type="submit" name="action" id="action" value="Submit" /></td>
			</tr>
			
		</table>
		
		</xsl:when>
		
		<xsl:otherwise>
			<div class="red_notification">
				<h1><strong>{TRANSLATE:access_denied}</strong></h1>
			</div>
		</xsl:otherwise>
		
		</xsl:choose>
	</xsl:template>
    
</xsl:stylesheet>