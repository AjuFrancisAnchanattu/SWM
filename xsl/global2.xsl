<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1 //EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="iso-8859-1"
		indent="yes"
	/>
	
	

	
	<xsl:include href="../controls/form/form.xsl"/>	
	<xsl:include href="../controls/header/header.xsl"/>
	<xsl:include href="../snapins/controlpanel/controlpanel.xsl"/>
	<xsl:include href="../snapins/activityviewer/activityviewer.xsl"/>
	<xsl:include href="../snapins/news/news.xsl" />
	<xsl:include href="../snapins/addressbook/addressbook.xsl" />
	<xsl:include href="../snapins/sitedetails/sitedetails.xsl" />
	<xsl:include href="../snapins/quicklinks/quicklinks.xsl" />
	<xsl:include href="../snapins/lottery/lottery.xsl" />
	<xsl:include href="../snapins/payday/payday.xsl" />
	<xsl:include href="../snapins/npi/npi.xsl" />
	<xsl:include href="../snapins/complaints/complaints.xsl" />
	<xsl:include href="../snapins/debug/debug.xsl" />
	<xsl:include href="../snapins/horizontalrule/horizontalrule.xsl" />
	<xsl:include href="../snapins/scapasitelogin/scapasitelogin.xsl" />
	
	
	<xsl:include href="../apps/ccr/snapins/actions/actions.xsl" />
	<xsl:include href="../apps/ccr/snapins/reports/reports.xsl" />
	<xsl:include href="../apps/ccr/snapins/load/load.xsl" />
	<xsl:include href="../apps/ccr/snapins/bookmarks/bookmarks.xsl" />
	
	
	<xsl:include href="../snapins/opportunityload/opportunityload.xsl" />
	<xsl:include href="../snapins/bugs/bugs.xsl" />
	<xsl:include href="../snapins/horse/horse.xsl" />
	<xsl:include href="../snapins/deadlines/deadlines.xsl" />
	<xsl:include href="../snapins/scapasupport/scapasupport.xsl" />
	
	<xsl:include href="../controls/textbox/textbox.xsl" />
	<xsl:include href="../controls/dropdown/dropdown.xsl"/>
	<xsl:include href="../controls/submit/submit.xsl"/>
	<xsl:include href="../controls/readonly/readonly.xsl"/>
	<xsl:include href="../controls/textboxlink/textboxlink.xsl"/>
	<xsl:include href="../controls/combo/combo.xsl"/>
	<xsl:include href="../controls/radio/radio.xsl"/>
	<xsl:include href="../controls/attachment/attachment.xsl"/>
	<xsl:include href="../controls/textarea/textarea.xsl"/>
	<xsl:include href="../controls/autocomplete/autocomplete.xsl"/>
	<xsl:include href="../controls/invisibletext/invisibletext.xsl" />
	<xsl:include href="../controls/dropdownSubmit/dropdownSubmit.xsl" />
	

	<xsl:include href="../controls/measurement/measurement.xsl" />
	<xsl:include href="../controls/dropdownAlternative/dropdownAlternative.xsl" />
	<xsl:include href="../controls/comboAlternative/comboAlternative.xsl" />
	<xsl:include href="../controls/searchclass/searchclass.xsl" />
	
	<xsl:include href="../controls/search/availableFiltersList/availableFiltersList.xsl" />
	<xsl:include href="../controls/search/filterDateRange/filterDateRange.xsl" />
	<xsl:include href="../controls/search/filterAmount/filterAmount.xsl" />
	
	
	<xsl:include href="../apps/slobs/snapins/load/load.xsl" />
	<xsl:include href="../apps/slobs/snapins/reports/reports.xsl" />
	<xsl:include href="../apps/slobs/snapins/actions/actions.xsl" />
	<xsl:include href="../apps/slobs/snapins/updateValues/updateValues.xsl" />
	
	<xsl:include href="../apps/ijf/snapins/load/load.xsl" />
	<xsl:include href="../apps/ijf/snapins/reports/reports.xsl" />
	<xsl:include href="../apps/ijf/snapins/ijfactions/ijfactions.xsl" />
	<xsl:include href="../apps/ijf/snapins/actions/actions.xsl" />
	
	
	<xsl:include href="../apps/employeedb/snapins/load/load.xsl" />
	
	<xsl:include href="../apps/technical/snapins/load/load.xsl" />
	<xsl:include href="../apps/technical/snapins/waitingEnquiries/waitingEnquiries.xsl" />
	<xsl:include href="../apps/technical/snapins/enquiries/enquiries.xsl" />
	
	<xsl:include href="../apps/docman/snapins/loadDoc/loadDoc.xsl" />
	<xsl:include href="../apps/docman/snapins/totalDoc/totalDoc.xsl" />
	
	<xsl:include href="../apps/npi/snapins/loadnpi/loadnpi.xsl" />
	<xsl:include href="../apps/npi/snapins/actionnpi/actionnpi.xsl" />
	<xsl:include href="../apps/npi/snapins/yournpi/yournpi.xsl" />
	
	<xsl:include href="../apps/complaints/snapins/actionComplaints/actionComplaints.xsl" />
	<xsl:include href="../apps/complaints/snapins/loadComplaint/loadComplaint.xsl" />
	<xsl:include href="../apps/complaints/snapins/yourComplaints/yourComplaints.xsl" />
	<xsl:include href="../apps/complaints/snapins/bookmarkedComplaints/bookmarkedComplaints.xsl" />
	<xsl:include href="../apps/complaints/snapins/refDocuments/refDocuments.xsl" />
	<xsl:include href="../apps/complaints/snapins/toolBoxComplaints/toolBoxComplaints.xsl" />
	<xsl:include href="../apps/complaints/snapins/addComplaint/addComplaint.xsl" />
	
	<xsl:include href="../apps/usermanager/snapins/usermanageractions/usermanageractions.xsl" />
	
	<xsl:include href="../apps/pricing/snapins/load/load.xsl" />
	<xsl:include href="../apps/pricing/snapins/relatedlinks/relatedlinks.xsl" />
	<xsl:include href="../apps/pricing/snapins/alternativePrices/alternativePrices.xsl" />
	
	
	<xsl:template match="page">
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
			<head>
			
			<title>Scapa Intranet</title>
			
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			
			<link rel="stylesheet" href="/css/default.css" />
			
			<xsl:if test="@dev='true'">
				<link rel="stylesheet" href="/css/dev.css" />
			</xsl:if>
			
			<script language="Javascript" src="/javascript/global.js" type="text/javascript">-</script>
			<script language="Javascript" src="/javascript/dropdown.js" type="text/javascript">-</script>
			<script language="Javascript" src="/javascript/noReturnKey/noReturnKey.js" type="text/javascript">-</script>
			
			
			<!--
			<link rel="stylesheet" type="text/css" href="/apps/docman/foldertree/simpletree.css" />	
			<script type="text/javascript" src="/apps/docman/foldertree/simpletreemenu.js">
			-->
			<xsl:apply-templates select="content/printCss"/>
			
			<script src="/javascript/scriptaculous/prototype.js" type="text/javascript">-</script>
			<script src="/javascript/scriptaculous/scriptaculous.js" type="text/javascript">-</script>
			
			<xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[!--[if gte IE 5]]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[!--[if lt IE 7]]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			<style type="text/css">
			.nav li { 
				behavior: url( /javascript/iehover.htc );
			}
			</style>
			<xsl:text disable-output-escaping="yes">&lt;</xsl:text><![CDATA[![endif]]]><xsl:text disable-output-escaping="yes">&gt;&lt;</xsl:text><![CDATA[![endif]--]]><xsl:text disable-output-escaping="yes">&gt;</xsl:text>
			
			
			
			
			</head>
			<body>
				<!-- underlying colour -->
				<div style="background: #EFEFEF;">
					
				
					<div class="widthhackforie" style="background: url(/images/bottom_left.gif) no-repeat bottom left;"><div style="background: url(/images/bottom_right.gif) no-repeat bottom right; padding-top: 10px;">
					
						<xsl:apply-templates select="content" />
						
						<div style="height: 5px; visibility: hidden;">-</div>
						
			
					</div></div>
				
				</div>
				
				
			</body>
		</html>
	</xsl:template>
		
	
	<xsl:template match="printCss">
		<link rel="stylesheet" type="text/css" href="{text()}" media="print" />
	</xsl:template>
	
	
	<xsl:template match="content">
	
		<form id="form" name="form" action="" method="POST" enctype="multipart/form-data">
		<input type="hidden" name="action" id="action" value="" />
		<input type="hidden" name="nextAction" id="nextAction" value="" />
		<input type="hidden" name="validate" id="validate" value="true" />

			<xsl:apply-templates />
			
		</form>
			
	</xsl:template>
	
	
	<xsl:template match="home">
		
	
		<div class="auto_complete" id="employee_auto_complete" style="z-index: 1000000;">-</div>
		
		<table width="100%">
			<tr>
				<td  valign="top">
					
					<div id="snapin_left_container">
					
						<xsl:apply-templates select="snapin_left" />
			
					</div>
					
				</td>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top left;">
				
					<div id="snapin_right_container">
					
						<xsl:apply-templates select="snapin_right" />
						
						<div align="center">
						<script language="javascript" src="http://scapasupport/visitor/index.php?_m=livesupport&amp;_a=htmlcode&amp;departmentid=0">
						<![CDATA[
				
						]]>
						</script>
						</div>
			
					</div>
					
				</td>
			</tr>
		</table>

		
		<script type="text/javascript" language="javascript" charset="utf-8">
		<![CDATA[
		
		
			Sortable.create('snapin_left_container',
			{
				tag:'div',
				handle:'snapin_top',
				onUpdate:function(element)
				{
					new Ajax.Request('/home/moveSnapin',
					{
						parameters:Sortable.serialize('snapin_left_container',
						{
							tag:'div',
							name:'snapins'
						}),
						asynchronous:true
					})
				}
			});
			Sortable.create('snapin_right_container',
			{
				tag:'div',
				handle:'snapin_top',
				onUpdate:function(element)
				{
					new Ajax.Request('/home/moveSnapin',
					{
						parameters:Sortable.serialize('snapin_right_container',
						{
							tag:'div',
							name:'snapins'
						}),
						asynchronous:true
					})
				}
			});
		]]>
		</script>

		
	</xsl:template>

	
	<xsl:template match="snapin_left">
	
		<xsl:apply-templates select="snapin" />
		
	</xsl:template>
	
	<xsl:template match="snapin_right">
	
		<xsl:apply-templates select="snapin" />
		
	</xsl:template>
	
	
	<xsl:template match="snapin">
	
		<div class="snapin" id="snapin_{@area}|{@class}">
		
			<div class="snapin_top"><div class="snapin_top_3">
			
			
			<xsl:if test="@canClose = 'true'">
				<div style="float: right;">
					<a href="/home/snapinManage?delete={@class}&amp;area={@area}"><img src="/images/snapins/close.gif" height="15" width="15" alt="" /></a>
				</div>
			</xsl:if>
			
			<p style="margin: 0; font-weight: bold; color: #FFFFFF;"><xsl:value-of select="@name" /></p>
			
			</div></div>
			
	
			<div class="snapin_content"><div class="snapin_content_3">
					
				<xsl:apply-templates />
			</div></div>
		
		</div>
			
	</xsl:template>
	
	<xsl:template match="para">
		<p style="margin: 0; line-height: 15px;"><xsl:value-of select="text()" /><br /></p>
	</xsl:template>
	
	<xsl:template match="paralink">
		<p style="margin: 0; line-height: 15px;"><a href="{link}"><xsl:value-of select="text()" /></a><br /></p>
	</xsl:template>
	
	<xsl:template match="br">
		<br />
	</xsl:template>
	
	<xsl:template match="die">
	
		<div style="background: #f2d2d2; padding: 0 10px 10px 10px; border: 2px dashed #f20000; margin: 15px;">
	
			<h1>An error has occurred</h1>
			
			<p>An error has occurred while processing your request, the message the server gave is below. Scapa I.T. have been notified of the problem.</p>
			<p style="font-weight: bold">"<xsl:value-of select="text()" />"</p>
			
			<br />
			
			<p>
				File: <xsl:value-of select="@file" /><br />
				Line: <xsl:value-of select="@line" />
			</p>
	
		</div>
		
	</xsl:template>
	
	
	
	
	<xsl:template match="searchResults">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td style="padding: 10px">
				
					<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
						<tr>
							<!--<td style="width: 350px;">
							
								<xsl:apply-templates select="form" />
							
							</td>
							<td style="padding-left: 10px;">-->
							<td>
								<table width="100%" cellspacing="0" cellpadding="4" style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px;">
									<tr>
										<td>Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" /></td>
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
									</tr>
								</table>			
							
							</td>
						</tr>
					</table>
				
	
					<table width="100%" cellspacing="0" class="data_table" style="border: 1px solid #CCCCCC;">
					
						<xsl:apply-templates select="searchRowHeader"/>
						
						<xsl:apply-templates select="searchRow"/>
					
					</table>
		
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="firstPageLink">
		<a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page=1">First</a><span style="padding: 0 10px 0 10px;">...</span>
	</xsl:template>
	
	<xsl:template match="pageLink">
		<xsl:choose>
			<xsl:when test="@current='true'">
				<span style="font-weight: bold; padding-right: 10px;"><xsl:value-of select="text()" /></span>
			</xsl:when>
			<xsl:otherwise>
				<span style="padding-right: 10px;"><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}"><xsl:value-of select="text()" /></a></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="lastPageLink">
		<span style="padding-right: 10px;">...</span><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}">Last</a>
	</xsl:template>
	
	
	<xsl:template match="searchRowHeader">
		<tr>
			<xsl:apply-templates select="searchColumnHeader"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchRow">
		<tr>
			<xsl:apply-templates select="searchColumn"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchColumnHeader">
	
		<xsl:if test="sortable='1'">
			<xsl:element name="th">
				<xsl:attribute name="width">1%</xsl:attribute>
				<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
				<a href="search?action=view&amp;orderBy={field}&amp;order=ASC&amp;page={page}"><img src="/images/up.gif" border="0" alt="" /></a><a href="search?action=view&amp;orderBy={field}&amp;order=DESC&amp;page={page}"><img src="/images/down.gif" border="0" alt="" /></a>
			</xsl:element>	
		</xsl:if>
		
		<xsl:element name="th">
			<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
			<xsl:value-of select="title"/>
		</xsl:element>
	</xsl:template>
		
	
	<xsl:template match="searchColumn">
		<xsl:element name="td">
			<xsl:if test="@sortable='1'">
				<xsl:attribute name="colspan">2</xsl:attribute>
			</xsl:if>
			
			<xsl:apply-templates select="text"/>
			<xsl:apply-templates select="link"/>
			
		</xsl:element>	
	</xsl:template>
	
	<xsl:template match="text">
		<xsl:value-of select="text()"/>
	</xsl:template>
	
	<xsl:template match="link">
		<a href="{@url}"><xsl:value-of select="text()"/></a>
	</xsl:template>
	
</xsl:stylesheet>