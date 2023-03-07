<?xml version="1.0"?>

<!--
	@date	07/03/2011
	@author	Daniel Gruszczyk
-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<!--
		To display a header for a section of the snapin (Quick Summary, Forms, Attachments)
		
		Parameters:
			$summaryPart	- (required) name of a section on the snapin, for which we want the header to be displayed
	-->
	<xsl:template name="ccSummaryHeader">
		<div style="font-size: 1.1em; font-weight: bold; margin: -2px 0 5px 0;">
		
			<xsl:element name="a">
				<xsl:attribute name="onClick">toggleQuickSummary("<xsl:value-of select="$summaryPart"/>"); return false;</xsl:attribute>
				<xsl:attribute name="style">text-decoration: none;</xsl:attribute>
				
				<xsl:element name="img">
					<xsl:attribute name="src">../../images/dTree/minus.png</xsl:attribute>
					<xsl:attribute name="alt">{TRANSLATE:toggle}</xsl:attribute>
					<xsl:attribute name="id">quickSummary_<xsl:value-of select="$summaryPart"/>_img</xsl:attribute>
					<xsl:attribute name="style">margin: 0 0 -5px 0;</xsl:attribute>
				</xsl:element>
			</xsl:element>
			
			<xsl:element name="span">
				<xsl:attribute name="id">quickSummary_<xsl:value-of select="$summaryPart"/>_header</xsl:attribute>
				{TRANSLATE:<xsl:value-of select="$summaryPart"/>}
			</xsl:element>
			
		</div>
	</xsl:template>
	
	
	
	
	<!--
		To display row on a summary snapin, with given field name and value
		
		Parameters:
			$name	- (required) name to use in translation as a field name
			$value	- (required) value of that field
	-->
	<xsl:template name="ccSummaryRow">
		<tr>
			<td width="50%">
				<strong>
					{TRANSLATE:<xsl:value-of select="$name" />}
				</strong>
			</td>
			<td>
				<i>
					<xsl:value-of select="$value" />
				</i>
			</td>
		</tr>
	</xsl:template>
	
	
	
	
	<!--
		To display an icon with given picture and alt text
		Used for 'none' and 'locked' pictures
		
		Parameters:
			$iconName		- (required) name of the icon to display
			$translation	- (required) text to be translated
			$lockName		- (optional) name of a person for which the form is locked
	-->
	<xsl:template name="ccFormIcon">
		<xsl:param name="lockName"/>
		<xsl:element name="td">
			<xsl:if test="$lockName != ''">
				<xsl:attribute name="colspan">2</xsl:attribute>
			</xsl:if>
			<xsl:element name="img">
				<xsl:attribute name="src">../../images/famIcons/<xsl:value-of select="$iconName"/>.png</xsl:attribute>
				<xsl:attribute name="alt">{TRANSLATE:<xsl:value-of select="$translation"/>} <xsl:value-of select="$lockName"/></xsl:attribute>
				<xsl:attribute name="style">
					float: left;
					margin-right: 3px;
				</xsl:attribute>
			</xsl:element>
			{TRANSLATE:<xsl:value-of select="$translation"/>}
			<xsl:if test="$lockName != ''">
				<xsl:value-of select="$lockName"/>
			</xsl:if>
		</xsl:element>
	</xsl:template>
	
	
	
	
	<!--
		To display a link to given page with given picture and alt text
		Used for 'add', 'edit', 'view' and 'print' pictures
		
		Parameters:
			$iconName		- (required) name of the icon to display
			$linkType		- (required) name of a page to link as well as translation for alt text
			$formName		- (required) name of a form (complaint, evaluation or conclusion) for which we want to link
	-->
	<xsl:template name="ccFormLink">
		<td>
			<a href="{$linkType}?complaintId={complaintId}&amp;stage={$formName}" style="display: block; float: left;">
				<xsl:element name="img">
					<xsl:attribute name="src">../../images/famIcons/<xsl:value-of select="$iconName"/>.png</xsl:attribute>
					<xsl:attribute name="alt">{TRANSLATE:<xsl:value-of select="$linkType"/>}</xsl:attribute>
					<xsl:attribute name="style">float: left; margin-right: 3px;</xsl:attribute>
				</xsl:element>
				{TRANSLATE:<xsl:value-of select="$linkType"/>}
			</a>
		</td>
	</xsl:template>
	
	
	
	
	<!--
		To display appropriate links for a given form
		
		Parameters:
			$formName	- (required) name of a form (complaint, evaluation or conclusion) for which we want links
	-->
	<xsl:template name="ccFormLinks">
		<tr>
			<td class="availableReportsFieldName"><b>{TRANSLATE:<xsl:value-of select="$formName"/>}:</b></td>
			
			<xsl:if test="*[name()= concat( $formName, 'All')] or *[name()= concat( $formName, 'View')]">
				<xsl:call-template name="ccFormLink">
					<xsl:with-param name="formName"><xsl:value-of select="$formName"/></xsl:with-param>
					<xsl:with-param name="linkType">view</xsl:with-param>
					<xsl:with-param name="iconName">magnifier</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="*[name()= concat( $formName, 'All')]">
				<xsl:call-template name="ccFormLink">
					<xsl:with-param name="formName"><xsl:value-of select="$formName"/></xsl:with-param>
					<xsl:with-param name="linkType">edit</xsl:with-param>
					<xsl:with-param name="iconName">application_form_edit</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="*[name()= concat( $formName, 'None')]">
				<xsl:call-template name="ccFormIcon">
					<xsl:with-param name="iconName">cross</xsl:with-param>
					<xsl:with-param name="translation">none</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="*[name()= concat( $formName, 'Locked')]">
				<xsl:call-template name="ccFormIcon">
					<xsl:with-param name="iconName">lock</xsl:with-param>
					<xsl:with-param name="translation">locked_for</xsl:with-param>
					<xsl:with-param name="lockName"><xsl:value-of select="*[name()= concat( $formName, 'Locked')]"/></xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="$formName != 'complaint' and *[name()= concat( $formName, 'Add')]">
				<xsl:call-template name="ccFormLink">
					<xsl:with-param name="formName"><xsl:value-of select="$formName"/></xsl:with-param>
					<xsl:with-param name="linkType">add</xsl:with-param>
					<xsl:with-param name="iconName">add</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
		</tr>
	</xsl:template>
	
	
	
	
	<!--
		To display links to attachments for a given form
		
		Parameters:
			$formName	- (required) name of a form (complaint, evaluation or conclusion)
	-->
	<xsl:template name="ccFormAttachments">
		<xsl:param name="formAttachment"><xsl:value-of select="$formName"/>_attachment</xsl:param>
		<tr>
			<td valign="top">
				<strong>
					{TRANSLATE:<xsl:value-of select="$formName"/>}:
				</strong>
			</td>
			<td valign="top">
				<xsl:choose>
					<xsl:when test="*[name()=$formAttachment]">
						<ul style="padding: 0; margin: 0;">
							<xsl:for-each select="*[name()=$formAttachment]">
								<li class="attachmentFile">
									<a href="http://{../root}/apps/customerComplaints/attachments/{$formName}/{../complaintId}/{@value}" target="_blank">
										<xsl:value-of select="@name" />
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:when>
					<xsl:otherwise>
						<i>
							{TRANSLATE:none}
						</i>
					</xsl:otherwise>
				</xsl:choose>
			</td>
		</tr>
	</xsl:template>
	
	
	<xsl:template match="material">
				
		<tr>
			<td>
				<div class="invoiceRow">
					
					<div class="toggleDetailsLink">
						<a onClick="toggleMaterialDetails({@id}); return false;" style="display: block; cursor: pointer; padding-left: 2px;">
						
							<img src="../../images/dTree/plus.png" style="float: right;" alt="" id="{@id}_img" />
								
							<strong><xsl:value-of select="@id"/></strong> (<xsl:value-of select="@totalValue"/>)
						</a>
					</div>
					
					
					
					
					<div id="{@id}_content" style="display: none;" class="invoiceDiv">
						<table cellpading="0" cellspacing="0" class="invoiceTable">
							<tr>
								<td class="invoiceData_title darker" colspan="2" style="padding-top: 3px;">
									{TRANSLATE:material_description}:
								</td>
							</tr>
							<tr>
								<td class="invoiceData_data darker"  colspan="2" style="text-align: left; padding-left: 10px;">
									<xsl:value-of select="@description"/>
								</td>
							</tr>
							<tr>
								<td class="invoiceData_title darker" colspan="2" style="padding-top: 3px;">
									{TRANSLATE:material_group}:
								</td>
							</tr>
							<tr>
								<td class="invoiceData_data darker"  colspan="2" style="text-align: left; padding-left: 10px;">
									<xsl:value-of select="@group"/>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr noshade="noshade" size="1" />
								</td>
							</tr>
							<xsl:for-each select="invoiceRow">
								<tr>
									<td class="invoiceData_title darker">
										{TRANSLATE:invoice_no}:
									</td>
									<td class="invoiceData_data darker">
										 <xsl:value-of select="invoiceNo"/>
									</td>
								</tr>
								<tr>
									<td class="invoiceData_title">
										{TRANSLATE:quantity}:
									</td>
									<td class="invoiceData_data">
										<xsl:value-of select="deliveryQuantity"/>
									</td>
								</tr>
								<tr>
									<td class="invoiceData_title darker">
										{TRANSLATE:value}:
									</td>
									<td class="invoiceData_data darker">
										<xsl:value-of select="netValueItem"/>
									</td>
								</tr>
								<tr>
									<td class="invoiceData_title">
										{TRANSLATE:deliv_no}:
									</td>
									<td class="invoiceData_data">
										<xsl:value-of select="deliveryNo"/>
									</td>
								</tr>
								<tr>
									<td class="invoiceData_title darker">
										{TRANSLATE:batch_no}:
									</td>
									<td class="invoiceData_data darker">
										<xsl:value-of select="batch"/>
									</td>
								</tr>
								<tr>
									<td class="invoiceData_title">
										{TRANSLATE:despatch_date}:
									</td>
									<td class="invoiceData_data">
										<xsl:value-of select="despatchDate"/>
									</td>
								</tr>
								<xsl:if test="hr">
									<tr>
										<td colspan="2">
											<hr noshade="noshade" size="1" />
										</td>
									</tr>
								</xsl:if>
							</xsl:for-each>
						</table>
					</div>
				</div>
			</td>
		</tr>
		
	</xsl:template>
	
	
	
	<!--
		The actual snapin
		
		Parameters:
			none
	-->
	<xsl:template match="ccSummarySnapin">
		
		<link rel="stylesheet" href="/apps/customerComplaints/snapins/ccSummary/ccSummary.css"/>
		<script type="text/javascript" src="/apps/customerComplaints/snapins/ccSummary/ccSummary.js">-</script>
		
		<div align="center" style="background: #FFFFE5; border: 1px solid #000000; padding: 5px;" >
			<a href="index?complaintId={complaintId}">
				{TRANSLATE:back_to_summary}
			</a>
		</div>
		<hr/>
		
		<xsl:call-template name="ccSummaryHeader">
			<xsl:with-param name="summaryPart">content</xsl:with-param>
		</xsl:call-template>
		
		<table id="quickSummary_content_content" width="100%" cellspacing="5">
		
			<xsl:call-template name="ccSummaryRow">
				<xsl:with-param name="name">customer_name</xsl:with-param>
				<xsl:with-param name="value"><xsl:value-of select="customerName"/></xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccSummaryRow">
				<xsl:with-param name="name">business_unit</xsl:with-param>
				<xsl:with-param name="value"><xsl:value-of select="bu"/></xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccSummaryRow">
				<xsl:with-param name="name">initiated_by</xsl:with-param>
				<xsl:with-param name="value"><xsl:value-of select="createdBy"/></xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccSummaryRow">
				<xsl:with-param name="name">complaint_conclusion_owner</xsl:with-param>
				<xsl:with-param name="value"><xsl:value-of select="cOwner"/></xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccSummaryRow">
				<xsl:with-param name="name">evaluation_owner</xsl:with-param>
				<xsl:with-param name="value"><xsl:value-of select="eOwner"/></xsl:with-param>
			</xsl:call-template>
			
			<xsl:if test="complaintDate">
				<xsl:call-template name="ccSummaryRow">
					<xsl:with-param name="name">complaint_date</xsl:with-param>
					<xsl:with-param name="value"><xsl:value-of select="complaintDate"/></xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="category">
				<xsl:call-template name="ccSummaryRow">
					<xsl:with-param name="name">apparent_category</xsl:with-param>
					<xsl:with-param name="value"><xsl:value-of select="category"/></xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			
			<xsl:if test="complaintValue">
				<tr>
					<td>
						<strong>
							{TRANSLATE:complaint_value}
						</strong>
					</td>
					<td>
						<img 	id="currencyCalculatorImg"
								class="currencyPopupLink" 
								src="../../images/famIcons/calculator.png"/>
						<i>
							<xsl:value-of select="complaintValue"/>
						</i>
					</td>
					
					<script>
						//get value and currency
						var summary_snapin_txtValue = '<xsl:value-of select="complaintValue"/>';
						
						appendCalcIcon();
					</script>
				</tr>
			</xsl:if>
		</table>
		
		
		
		<hr />
		
		<xsl:call-template name="ccSummaryHeader">
			<xsl:with-param name="summaryPart">forms</xsl:with-param>
		</xsl:call-template>
		
		<table id="quickSummary_forms_content" width="100%" cellspacing="5" >
		
			<xsl:call-template name="ccFormLinks">
				<xsl:with-param name="formName">complaint</xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccFormLinks">
				<xsl:with-param name="formName">evaluation</xsl:with-param>
			</xsl:call-template>

			<xsl:call-template name="ccFormLinks">
				<xsl:with-param name="formName">conclusion</xsl:with-param>
			</xsl:call-template>
				
		</table>
		
		
		
		<hr/>
		
		<xsl:call-template name="ccSummaryHeader">
			<xsl:with-param name="summaryPart">attachments</xsl:with-param>
		</xsl:call-template>
		
		<table id="quickSummary_attachments_content" width="100%" cellspacing="5" >
			<xsl:call-template name="ccFormAttachments">
				<xsl:with-param name="formName">complaint</xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccFormAttachments">
				<xsl:with-param name="formName">evaluation</xsl:with-param>
			</xsl:call-template>
			
			<xsl:call-template name="ccFormAttachments">
				<xsl:with-param name="formName">conclusion</xsl:with-param>
			</xsl:call-template>
		</table>
		
		
		
		<hr/>
		
		<xsl:call-template name="ccSummaryHeader">
			<xsl:with-param name="summaryPart">material_involved</xsl:with-param>
		</xsl:call-template>
		
		<table id="quickSummary_material_involved_content" width="100%" cellspacing="5" >
			
			<xsl:choose>
				<xsl:when test="numberOfMaterials != 0">
					<xsl:apply-templates select="material" />
				</xsl:when>
				<xsl:otherwise>
					<tr>
						<td>
							N/A
						</td>
					</tr>
				</xsl:otherwise>
			</xsl:choose>
			
		</table>
		
		<script>
		
			if(readCookie("summary_content") == 0)
			{
				toggleQuickSummary("content");
			}
			
			if(readCookie("summary_forms") == 0)
			{
				toggleQuickSummary("forms");
			}
			
			if(readCookie("summary_attachments") == 0)
			{
				toggleQuickSummary("attachments");
			}
			
			if(readCookie("summary_material_involved") == 0)
			{
				toggleQuickSummary("material_involved");
			}
		
		</script>
		
	</xsl:template>
    
</xsl:stylesheet>