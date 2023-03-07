<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="currencyError">
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script>
			parent.complaint.hidePopup( RemoteTranslate('popup_currency_error') );
		</script>
	</xsl:template>
	
	<xsl:template match="invoiceNoError">
		<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
		<script>
			parent.complaint.hidePopup( RemoteTranslate('popup_invoiceNo_error') );
		</script>
	</xsl:template>

	<xsl:template match="invoicePopup">
		<html>
			<head>
				<link rel="stylesheet" href="/apps/customerComplaints/invoicePopup/invoicePopup.css"/>
				<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
				<script type="text/javascript" src="/apps/customerComplaints/invoicePopup/invoicePopup.js">-</script>
				
				<script>
					var invoicePopup;
					
					function initiate()
					{
						var complaintId = <xsl:value-of select="complaintId" />;
						var invoiceNo = <xsl:value-of select="invoiceNo" />;
						var rowNo = <xsl:value-of select="rowNo" />;
						var readonly = <xsl:value-of select="readonly" />;
						
						invoicePopup = new InvoicePopup(complaintId, invoiceNo, rowNo, readonly);
					}
					
					initiate();
				</script>
			</head>
			<body>
				<div id="content">
				
					<xsl:apply-templates select="invoiceHeader"/>
			
					<xsl:apply-templates select="invoiceData"/>

					<xsl:apply-templates select="invoiceFooter"/>
					
				</div>
			</body>
		</html>
	</xsl:template>
	
	<xsl:template match="invoiceHeader">
	
		<div id="title">
			<xsl:choose>
				<xsl:when test="../complaintId > 0">
					{TRANSLATE:complaint_id}: <i><xsl:value-of select="../complaintId"/></i>
				</xsl:when>
				<xsl:otherwise>
					{TRANSLATE:new_complaint}
				</xsl:otherwise>
			</xsl:choose>
		</div>
		
		<div id="top_info">
			<table id="headerTable">
				<tr>
					<td class="header">
						{TRANSLATE:invoice_no}:
					</td>
					<td class="message" id="invoiceNo">
						<xsl:value-of select="../invoiceNo"/>
					</td>
					
					<td class="header">
						{TRANSLATE:invoice_date}:
					</td>
					<td class="message">
						<xsl:value-of select="invoiceDate"/>
					</td>
					<td class="header">
						{TRANSLATE:sales_doc}:
					</td>
					<td class="message">
						<xsl:value-of select="salesDoc"/>
					</td>
				</tr>
				<tr>
					<td class="header">
						{TRANSLATE:sold_to}:
					</td>
					<td class="message">
						<xsl:value-of select="stp"/>
					</td>
					
					<td class="header">
						{TRANSLATE:customer_name}:
					</td>
					<td class="message">
						<xsl:value-of select="customerName"/>
					</td>
				</tr>
			</table>
		</div>
	</xsl:template>
	
	<xsl:template match="invoiceData">
		<div id="tableContainer">
			<div id="tableOverflow">
				<table id="invoicesTable" cellspacing="0" cellpadding="4" align="center" width="100%">
					
					<tr >
						<xsl:if test="../readonly = 'false'">
							<th style="background: #FFFFFF;" width="7px" class="check">
								<input 	
										class="checkbox" 
										type="checkbox" 
										name="save_invoice_all" 
										id="save_invoice_all" 
										onClick="invoicePopup.ui.toggleSelectAll();"
								/>
							</th>
							<th style="display: none;">Calc</th>
							<th style="display: none;">maxValue</th>
							<th style="display: none;">maxQuantity</th>
						</xsl:if>
						<th style="display: none;">Id</th>
						<th >{TRANSLATE:desp_date}</th>
						<th >{TRANSLATE:deliv_no}</th>
						<th >{TRANSLATE:batch_no}</th>
						<th >{TRANSLATE:deliv_quant}</th>
						<th >{TRANSLATE:material}</th>
						<th >{TRANSLATE:item_value}</th>
					</tr>
					
					<xsl:if test="../readonly = 'false'">
						<xsl:if test="saved_invoice">
							<tr style="background: #EFEFEF;">
								<td colspan="7" class="saved">
									{TRANSLATE:saved_invoices}
								</td>
							</tr>
						</xsl:if>
					</xsl:if>
					<xsl:for-each select="saved_invoice">
						<tr 
							valign="top" 
							id="invoice_{@row}" 
							onMouseOver="this.className = 'highlight';" 
							onMouseOut="this.className = '';"
						>
						
							<xsl:if test="../../readonly = 'false'">
								<td width="7px" class="check">
								
									<input 	
											class="checkbox" 
											type="checkbox" 
											name="save_invoice" 
											id="save_invoice_{@row}" 
											onClick="invoicePopup.ui.checkIfAllSelected();" 
									/>
									
								</td>
								
								<td id="calc_{@row}" style="display:none;">
									<xsl:value-of select="calc"/>
								</td>
								<td id="maxValue_{@row}" style="display:none;">
									<xsl:value-of select="maxValue"/>
								</td>
								<td id="maxQuantity_{@row}" style="display:none;">
									<xsl:value-of select="maxQuantity"/>
								</td>
							</xsl:if>
							
							<td id="invoicesId_{@row}" style="display: none;"><xsl:value-of select="invoicesId"/></td>
							
							<td align="center">
								<xsl:choose>
									<xsl:when test="despatchDate != ''">
										<xsl:value-of select="despatchDate"/>
									</xsl:when>
									<xsl:otherwise>
										N/A
									</xsl:otherwise>
								</xsl:choose>
							</td>
							
							<td align="center"><xsl:value-of select="deliveryNo"/></td>
							
							<td align="center" >
								<xsl:choose>
									<xsl:when test="../../readonly = 'false'">
									
										<input 	
												type="text" 
												value="{batch}" 
												size="10" 
												id="batch_{@row}" 
												onChange="invoicePopup.validate(this, {@row});"
										/>
										
									</xsl:when>
									<xsl:otherwise>
										<xsl:choose>
											<xsl:when test="batch != ''">
												<xsl:value-of select="batch"/>
											</xsl:when>
											<xsl:otherwise>
												-
											</xsl:otherwise>
										</xsl:choose>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							
							<td align="center" >
								<xsl:choose>
									<xsl:when test="../../readonly = 'false'">
									
										<input 
												type="text" 
												value="{deliveryQuantity}" 
												size="6" 
												id="deliveryQuantity_{@row}" 
												onChange="if(invoicePopup.validate(this, {@row})) invoicePopup.updateRowValue({@row});"
										/>
										
										<span id="uom_{@row}">
											<xsl:value-of select="uom"/>
										</span>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="delivery"/>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							
							<td width="20%">
								<xsl:value-of select="material"/><br />
								
								<div class="message" align="left">
									{TRANSLATE:material_group}: <xsl:value-of select="materialGroup"/>
								</div>
								
								<div class="message" align="left">
									<xsl:value-of select="materialDescription"/>
								</div>
							</td>
							
							<td align="center" >
								<xsl:choose>
									<xsl:when test="../../readonly = 'false'">
									
										<input 
												type="text" 
												value="{netValueItem}" 
												size="6" 
												id="netValueItem_{@row}" 
												onChange="invoicePopup.validate(this, {@row});"
										/>
										
										<span id="netValueItem_currency_{@row}">
											<xsl:value-of select="netValueItem_currency"/>
										</span>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="itemValue"/>
									</xsl:otherwise>
								</xsl:choose>	
							</td>
						</tr>
					</xsl:for-each>
					
					<xsl:if test="../readonly = 'false'">
						<xsl:if test="invoice">
							<tr style="background: #EFEFEF;">
								<td colspan="7" class="unsaved">
									{TRANSLATE:unsaved_invoices}
								</td>
							</tr>
						</xsl:if>
						<xsl:for-each select="invoice">
							<tr 
								valign="top" 
								id="invoice_{@row}" 
								onMouseOver="this.className = 'highlight';" 
								onMouseOut="this.className = '';"
							>
							
								<xsl:if test="../../readonly = 'false'">
									<td width="7px" class="check">
									
										<input 
												class="checkbox" 
												type="checkbox" 
												name="save_invoice" 
												id="save_invoice_{@row}" 
												onClick="invoicePopup.ui.checkIfAllSelected();"
										/>
									
									</td>
									
									<td id="calc_{@row}" style="display:none;">
										<xsl:value-of select="calc"/>
									</td>
									<td id="maxValue_{@row}" style="display:none;">
										<xsl:value-of select="maxValue"/>
									</td>
									<td id="maxQuantity_{@row}" style="display:none;">
										<xsl:value-of select="maxQuantity"/>
									</td>
								</xsl:if>
								
								<td id="invoicesId_{@row}" style="display: none;"><xsl:value-of select="invoicesId"/></td>
								
								<td align="center">
									<xsl:choose>
										<xsl:when test="despatchDate != ''">
											<xsl:value-of select="despatchDate"/>
										</xsl:when>
										<xsl:otherwise>
											N/A
										</xsl:otherwise>
									</xsl:choose>
								</td>
								
								<td align="center"><xsl:value-of select="deliveryNo"/></td>
								
								<td align="center" >
									<xsl:choose>
										<xsl:when test="../../readonly = 'false'">
										
											<input 
													type="text" 
													value="{batch}" 
													size="10" 
													id="batch_{@row}" 
													onChange="invoicePopup.validate(this, {@row});"
											/>
										
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="batch"/>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								
								<td align="center" >
									<xsl:choose>
										<xsl:when test="../../readonly = 'false'">
										
											<input 
													type="text" 
													value="{deliveryQuantity}" 
													size="6" id="deliveryQuantity_{@row}" 
													onChange="if(invoicePopup.validate(this, {@row})) invoicePopup.updateRowValue({@row});"
											/>
											
											<span id="uom_{@row}">
												<xsl:value-of select="uom"/>
											</span>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="delivery"/>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								
								<td width="20%">
									<xsl:value-of select="material"/><br />
									
									<div class="message" align="left">
										{TRANSLATE:material_group}: <xsl:value-of select="materialGroup"/>
									</div>
								
									<div class="message" align="left">
										<xsl:value-of select="materialDescription"/>
									</div>
								</td>
								
								<td align="center" >
									<xsl:choose>
										<xsl:when test="../../readonly = 'false'">
										
											<input 
													type="text" 
													value="{netValueItem}" 
													size="6" 
													id="netValueItem_{@row}" 
													onChange="invoicePopup.validate(this, {@row});"
											/>
									
											<span id="netValueItem_currency_{@row}">
												<xsl:value-of select="netValueItem_currency"/>
											</span>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="itemValue"/>
										</xsl:otherwise>
									</xsl:choose>	
								</td>
							</tr>
						</xsl:for-each>
					</xsl:if>
					
				</table>
				
				<input type="hidden" id="totalInvoiceValue" value="{totalInvoiceValue}" />
			</div>
		</div>
	</xsl:template>

	<xsl:template match="invoiceFooter">
		<div id="invoiceFooter">
			<table width="100%" height="100%">
				<tr>
					<xsl:if test="../readonly = 'false'">
						<td align="center">
						
							<xsl:element name="input">
								<xsl:attribute name="type">button</xsl:attribute>
								<xsl:attribute name="onclick">invoicePopup.save();</xsl:attribute>
								<xsl:attribute name="value">{TRANSLATE:save} &amp; {TRANSLATE:close}</xsl:attribute>
								<xsl:attribute name="style">width: 150px;</xsl:attribute>
							</xsl:element>
						
						</td>
						<td align="center">
						
							<xsl:element name="input">
								<xsl:attribute name="type">button</xsl:attribute>
								<xsl:attribute name="onclick">invoicePopup.reset();</xsl:attribute>
								<xsl:attribute name="value">{TRANSLATE:remove}/{TRANSLATE:Reset}</xsl:attribute>
								<xsl:attribute name="style">width: 150px;</xsl:attribute>
							</xsl:element>
							
						</td>
					</xsl:if>
					<td align="center">
					
						<xsl:element name="input">
							<xsl:attribute name="type">button</xsl:attribute>
							<xsl:attribute name="onclick">invoicePopup.close();</xsl:attribute>
							<xsl:attribute name="value">{TRANSLATE:close}</xsl:attribute>
							<xsl:attribute name="style">width: 150px;</xsl:attribute>
						</xsl:element>
						
					</td>
				</tr>
			</table>
		</div>
	</xsl:template>
	
</xsl:stylesheet>