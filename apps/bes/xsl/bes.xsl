<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	
	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:output
		method="xml"
		doctype-public="-//W3C//DTD XHTML 1.1 //EN"
		doctype-system="http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"
		encoding="ISO-8859-1"
		indent="yes"
	/>
	
	
	<xsl:template match="page">
		
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
		
			<head>
				<link rel="stylesheet" href="css/bes.css" />	
				<title>Scapa Intranet (BES)</title>			
			</head>
			
			<body>
				<xsl:apply-templates select="content" />
			</body>
			
		</html>	
	
	</xsl:template>
	
	
	<xsl:template match="content">
	
		<xsl:apply-templates />
			
	</xsl:template>	
	
	
	<xsl:template name="mainHeader">
	
		<a href="/apps/bes?" id="mainHeaderLink">
			<div class="title-box2">
				<div class="left-top-corner">
					<div class="right-top-corner">
						<div class="right-bot-corner">
							<div class="left-bot-corner">
								<div class="inner">
									<div class="wrapper" id="mainHeader">
										<img src="../../../images/scapaicon.gif.jpg" id="mainHeaderIcon" alt="" /><p id="mainHeaderTitle">Scapa Intranet (Blackberry Edition)</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</a>	
		
	</xsl:template>
	
	
	<xsl:template match="besNews">
	
		<xsl:call-template name="mainHeader" />
		
		<div id="newsSubject">
			<strong>News</strong> - <xsl:value-of select="newsSubject" />
		</div>
		
		<div id="newsContent">
			<p><xsl:apply-templates select="newsContent" /></p>
		</div>
		
		<p id="newsDate">Submitted on: <xsl:value-of select="newsDate" /></p>

		<a href="/apps/bes?" id="backLink">Back</a>
		
	</xsl:template>
	
	
	<xsl:template match="newsContent">
	
		<xsl:apply-templates select="para"/>
		
	</xsl:template>
		
	
	<xsl:template match="besHome">
		
		<xsl:call-template name="mainHeader" />
		
		<br />
		
		<p>Welcome <xsl:value-of select="user" />, more text goes here...</p>
		
		<br />		
		
		<div class="title-boxgrey">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<p id="contentsTitle">Contents</p>
									<a href="#top" class="topLink">
										<img src="../../../images/arrow5.gif" class="topLinkImage" alt="top" />
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<br />
		
		<p><a href="#news" class="contents">News</a></p>
		<p><a href="#salesAndOrders" class="contents">Sales and Orders</a></p>
		<p><a href="#openAndOverdue" class="contents">Open and Overdue Orders</a></p>
		
		<br /><br />		
		
		<div class="title-boxgrey">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<a name="news"></a>
									<p id="newsTitle">News</p>									
									<a href="#top" class="topLink">
										<img src="../../../images/arrow5.gif" class="topLinkImage" alt="top" />
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div id="newsUpper">
			<div style="float: left; background: #fff; margin-right: 10px; margin-bottom: -3px; text-align: center; vertical-align: middle;">
				<img src="news.jpg" alt="" style="width: 90px;" />
			</div>
			
			<div id="headlineBox">
			
				<xsl:for-each select="headLineOne">
					<p class="headline"><a href="news?&amp;id={@id}"><xsl:value-of select="." /></a></p>
				</xsl:for-each>
			
			</div>
		</div>
		
		<div class="content">
			
			<xsl:for-each select="headLineTwo">
				<p class="headline"><a href="news?&amp;id={@id}"><xsl:value-of select="." /></a></p>
			</xsl:for-each>	
			
			<br /><br />
			
			<a href="#" class="linkAsButton">Ask a question</a>
			
		</div>
		
		<br style="clear: both;" />
		
		<div class="title-boxgrey">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<a name="salesAndOrders"></a>
									<p id="salesAndOrdersTitle">Sales and Orders - <xsl:value-of select="yesterdayDate" /></p>
									<a href="#top" class="topLink">
										<img src="../../../images/arrow5.gif" class="topLinkImage" alt="top" />
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<table width="100%" cellpadding="1" cellspacing="1" id="salesAndOrders">
			<!--<tr>
				<td style="background-color: #333333; padding: 5px;" colspan="2"><span style="color: #FFFFFF;">Sales and Orders - <xsl:value-of select="ytdDate" /></span></td>
			</tr>-->
			<tr>
				<td class="light">MTD Group Sales</td>
				<td class="light"><xsl:value-of select="mtdSalesGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="light">MTD Group Orders</td>
				<td class="light"><xsl:value-of select="mtdOrdersGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="dark">MTD Europe Sales</td>
				<td class="dark"><xsl:value-of select="mtdEuropeSalesGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="dark">MTD Europe Orders</td>
				<td class="dark"><xsl:value-of select="mtdEuropeOrdersGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="light">MTD NA Sales</td>
				<td class="light"><xsl:value-of select="mtdNASalesUSD" /> USD</td>
			</tr>
			<tr>
				<td class="light">MTD NA Orders</td>
				<td class="light"><xsl:value-of select="mtdNAOrdersUSD" /> USD</td>
			</tr>
			
			
			<tr>
				<td class="dark">Group Sales (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="dark"><xsl:value-of select="yesterdaySalesGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="dark">Group Orders (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="dark"><xsl:value-of select="yesterdayOrdersGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="light">Europe Sales (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="light"><xsl:value-of select="yesterdayEuropeSalesGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="light">Europe Orders (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="light"><xsl:value-of select="yesterdayEuropeOrdersGBP" /> GBP</td>
			</tr>
			<tr>
				<td class="dark">NA Sales (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="dark"><xsl:value-of select="yesterdayNASalesUSD" /> USD</td>
			</tr>
			<tr>
				<td class="dark">NA Orders (<xsl:value-of select="yesterdayDate" />)</td>
				<td class="dark"><xsl:value-of select="yesterdayNAOrdersUSD" /> USD</td>
			</tr>
			
		</table>
		
		<div class="title-boxgrey">
			<div class="left-top-corner">
				<div class="right-top-corner">
					<div class="right-bot-corner">
						<div class="left-bot-corner">
							<div class="inner">
								<div class="wrapper">
									<a name="openAndOverdue"></a><p style="margin: 0; font-weight: bold; color: #FFFFFF; float: left;">Open and Overdue Orders - <xsl:value-of select="yesterdayDate" /></p><a href="#top" style="display: block; float: right; padding: 0 4px 3px 10px;"><img src="../../../images/arrow5.gif" style=" margin-top: 8px;" alt="top" /></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<xsl:apply-templates select="zoverduenTopLevelTable" />	
				
	</xsl:template>
	
	
	<xsl:template match="zoverduenTopLevelTable">
		
		<xsl:for-each select="plantItem">
		
			<table width="100%" cellpadding="1" cellspacing="1" id="salesAndOrders">
				<!--<tr>
					<td style="background-color: #333333; padding: 5px;" colspan="2"><span style="color: #FFFFFF;">Sales and Orders - <xsl:value-of select="ytdDate" /></span></td>
				</tr>-->
				<tr>
					<td colspan="2" style="background-color: #fff; padding: 5px; font-weight: bold;">Plant: <xsl:value-of select="plantName" /></td>
				</tr>
				<tr>
					<td class="light">Order Line Items:</td>
					<td class="dark"><xsl:value-of select="totalOpenLineItems" /></td>
				</tr>
				<tr>
					<td class="light">Value Open Orders:</td>
					<td class="dark"><xsl:value-of select="openValue" /></td>
				</tr>
				<tr>
					<td class="light">Overdue Line Items:</td>
					<td class="dark"><xsl:value-of select="totalOverdueLineItems" /></td>
				</tr>
				<tr>
					<td class="light">Value Overdue Orders:</td>
					<td class="dark"><xsl:value-of select="overdueValue" /></td>
				</tr>
				<tr>
					<td class="light">%*</td>
					<td class="dark"><xsl:value-of select="percentage" /></td>
				</tr>					
			
			</table>
		
		</xsl:for-each>
		
		<xsl:for-each select="groupPlantItem">
			
			<table width="100%" cellpadding="1" cellspacing="1" id="salesAndOrders">
			
				<tr>
					<td colspan="2" style="background-color: #fff; padding: 5px; font-weight: bold;">Plant: <xsl:value-of select="plantName" /></td>
				</tr>
				<tr>
					<td class="light">Order Line Items:</td>
					<td class="dark"><xsl:value-of select="totalOpenLineItems" /></td>
				</tr>
				<tr>
					<td class="light">Value Open Orders:</td>
					<td class="dark"><xsl:value-of select="openValue" /></td>
				</tr>
				<tr>
					<td class="light">Overdue Line Items:</td>
					<td class="dark"><xsl:value-of select="totalOverdueLineItems" /></td>
				</tr>
				<tr>
					<td class="light">Value Overdue Orders:</td>
					<td class="dark"><xsl:value-of select="overdueValue" /></td>
				</tr>
				<tr>
					<td class="light">%*</td>
					<td class="dark"><xsl:value-of select="percentage" /></td>
				</tr>
			
			</table>
			
		</xsl:for-each>
	
	</xsl:template>
	
	
</xsl:stylesheet>