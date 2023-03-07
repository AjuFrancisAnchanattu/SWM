<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="error">
		
		<div style="background: #f2d2d2; padding: 0 10px 0 10px; border: 1px solid #f2d2d2; margin-bottom: 10px;">
			<h1>Warning: Form submission error</h1>
			<h1>Please scroll down to view errors.</h1>			
			<xsl:if test="text()">
				df<p><xsl:value-of select="text()" /></p>
			</xsl:if>
		</div>
		
	</xsl:template>
	
	<xsl:template match="form">
	
		<xsl:element name="table">
			<xsl:attribute name="width">100%</xsl:attribute>
			<xsl:attribute name="cellspacing">0</xsl:attribute>
			<xsl:attribute name="cellpadding">4</xsl:attribute>
			
			<xsl:if test="@showBorder='true'">
				<xsl:attribute name="style">border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;</xsl:attribute>
			</xsl:if>
		
			<xsl:apply-templates />

		</xsl:element>
		
		<xsl:if test="@showLegend='true'">
		
			<div style="padding: 10px; text-align: center;">
			
				<table class="showLegend" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15" bgcolor="#F2D2D2" style="border: 1px solid #333333;"><img src="/images/clear.gif" height="15" width="15" alt="" /></td>
						<td style="padding: 0 10px 0 5px;">Required</td>
						<td width="15" bgcolor="#DCDDF2" style="border: 1px solid #333333;"><img src="/images/clear.gif" height="15" width="15" alt="" /></td>
						<td style="padding: 0 10px 0 5px;">Optional</td>
					</tr>
				</table>
			
			</div>
		
		</xsl:if>
		
	</xsl:template>
	
	
	<xsl:template match="readonlymultiplegroup">
	
		<xsl:element name="tbody">
			
			<xsl:attribute name="id"><xsl:value-of select="@name" />Group</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>
			
			
			<input type="hidden" name="multipleGroupId{@name}" id="multipleGroupId{@name}" value="" />
			
			<xsl:apply-templates select="readonlymultiplegrouprow" />
			
			<tr>
				<td colspan="2" style="background: #EFEFEF; border-bottom: 10px solid #EFEFEF;"></td>
			</tr>
				
		</xsl:element>
		
	</xsl:template>
	
	<xsl:template match="multiplegroup">
	
	
		<xsl:element name="tbody">
			
			<xsl:attribute name="id"><xsl:value-of select="@name" />Group</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>

			<input type="hidden" name="multipleGroupId{@name}" id="multipleGroupId{@name}" value="" />
			
			<xsl:apply-templates select="multiplegrouprow" />

			<tr>
				<td colspan="2" style="background: #CCCCCC; border-bottom: 10px solid #EFEFEF; text-align: center;">
				<xsl:if test="@anchorRef">
					<a name="{@anchorRef}" id="{@anchorRef}"></a>
				</xsl:if>
				<input type="submit" value="Add" onclick="document.getElementById('validate').value='false'; buttonPressMultiGroup('multipleGroupAdd|{@name}', '{@nextAction}', '{@anchorRef}');" />
				
				</td>
			</tr>
			
			<xsl:if test="@border = 'true'">
			
				<tr>
					<td colspan="2" style="background: #EFEFEF; border-bottom: 10px solid #EFEFEF;"></td>
				</tr>
				
			</xsl:if>
		
		</xsl:element>
		
	</xsl:template>
	
	

	
	<xsl:template match="readonlymultiplegrouprow">
	
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="../@name" /></xsl:attribute>

			<xsl:element name="td">
				<xsl:attribute name="style">background: #CCCCCC; line-height: 24px; padding-left: 5px;</xsl:attribute>
				<xsl:attribute name="colspan">2</xsl:attribute>
						
				<strong><xsl:value-of select="@title" /> - #<xsl:value-of select="position()" /></strong>
			</xsl:element>
			
		</xsl:element>
		
		<xsl:apply-templates select="row" />
		<xsl:apply-templates select="invisibletext" />
		<xsl:apply-templates select="attachment" />
		<xsl:apply-templates select="autocomplete" />

		<xsl:apply-templates select="filterRow" />
		<xsl:apply-templates select="buttonrow" />
	
	</xsl:template>
	
	
	<xsl:template match="multiplegrouprow">
	
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="../@name" /></xsl:attribute>

			<xsl:element name="td">
				<xsl:attribute name="style">background: #CCCCCC; line-height: 24px; padding-left: 5px;</xsl:attribute>
				<xsl:attribute name="colspan">2</xsl:attribute>
				
				<xsl:if test="position() > 1">
					<input type="submit" value="Remove" style="float: right;" onclick="document.getElementById('validate').value='false'; removeMultipleGroupRow('{position()}', '{../@name}', '{../@nextAction}');" />
				</xsl:if>
				
				<strong><xsl:value-of select="@title" /> - #<xsl:value-of select="position()" /></strong>
			</xsl:element>
			
		</xsl:element>
		
		<xsl:apply-templates select="row" />
		<xsl:apply-templates select="invisibletext" />
		<xsl:apply-templates select="attachment" />
		<xsl:apply-templates select="autocomplete" />

		<xsl:apply-templates select="filterRow" />
		<xsl:apply-templates select="buttonrow" />
	
	</xsl:template>
			
	
	
	<xsl:template match="group">
	
		<xsl:element name="tbody">
			
			<xsl:attribute name="id"><xsl:value-of select="@name" />Group</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>		
			
			<xsl:if test="@anchorRef">
					<a name="{@anchorRef}" id="{@anchorRef}"></a>
			</xsl:if>
	
			<xsl:apply-templates select="row" />
			<xsl:apply-templates select="invisibletext" />
			<xsl:apply-templates select="attachment" />
			<xsl:apply-templates select="autocomplete" />
	
			<xsl:apply-templates select="filterRow" />
			<xsl:apply-templates select="buttonrow" />
	
			<xsl:if test="@border = 'true'">
			
				<tr>
					<td colspan="2" style="background: #EFEFEF; border-bottom: 10px solid #EFEFEF;"></td>
				</tr>
				
			</xsl:if>
		
		</xsl:element>
		
	</xsl:template>
	
	
	
	<xsl:template match="row">
	
		<xsl:if test="@label">
		
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="@name" />LabelRow</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>
		
			<td colspan="2" style="background: #CCCCCC;"><strong><xsl:value-of select="@label" /></strong></td>
			
		</xsl:element>
		
		</xsl:if>
	
		<xsl:element name="tr">
			<xsl:attribute name="id"><xsl:value-of select="@name" />Row</xsl:attribute>
			
			<xsl:if test="@show = 'false'">
				<xsl:attribute name="style">display: none</xsl:attribute>
			</xsl:if>
			
			<xsl:choose>
				<xsl:when test="@valid = 'true'">
					<xsl:attribute name="class">valid_row</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="class">invalid_row</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:element name="td">
			
				<xsl:attribute name="class">cell_name</xsl:attribute>
							
				<xsl:attribute name="width">28%</xsl:attribute>
				<xsl:attribute name="valign">top</xsl:attribute>
				
				<xsl:if test="@debug = '1'">				
					<xsl:element name="img">
						<xsl:attribute name="style">float: left;</xsl:attribute>
						<xsl:attribute name="src">/images/info.png</xsl:attribute>
						<xsl:attribute name="alt"><xsl:value-of select="@name" /></xsl:attribute>
						<xsl:attribute name="title"><xsl:value-of select="@name" /></xsl:attribute>
					</xsl:element>
				</xsl:if>

				<div style="float: left;"><xsl:value-of select="@title" />: </div>
				
				
				<xsl:choose>
					<xsl:when test="@helpedit != '0'">
						<xsl:if test="@help != '0'"><div style="float: right;"><a href="/apps/translations/help?id={@helpedit}"><img src="/images/icons2020/small_help.jpg" id="helpicon_{@help}" style="cursor: pointer;" onMouseOver="showHelp('{@help}');" onMouseOut="hideHelp('{@help}');" /></a></div></xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="@help != '0'"><div style="float: right;"><img src="/images/icons2020/small_help.jpg" id="helpicon_{@help}" style="cursor: pointer;" onMouseOver="showHelp('{@help}');" onMouseOut="hideHelp('{@help}');" /></div></xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				
				<xsl:apply-templates select="help" />
				
				<xsl:if test="@type = 'filter'">
					<div style="float: left; clear: both; margin-top: 20px;">
					<input 
						type="submit"
						value=" Remove filter "
						onclick="buttonPress('removeFilter-{@name}');"
						style="width: 100px;"
					/>
					</div>
				</xsl:if>
			
			</xsl:element>
			
			<xsl:element name="td">
				<xsl:apply-templates select="textbox" />
				<xsl:apply-templates select="textboxlink" />
				<xsl:apply-templates select="dropdown" />
				<xsl:apply-templates select="calendar" />
				<xsl:apply-templates select="dropdownSubmit" />
				<xsl:apply-templates select="readonly" />
				<xsl:apply-templates select="submit" />
				<xsl:apply-templates select="combo" />
				<xsl:apply-templates select="comboCustomColumns" />
				<xsl:apply-templates select="textarea" />
				<xsl:apply-templates select="attachment" />
				<xsl:apply-templates select="attached" />
				<xsl:apply-templates select="checkbox" />
				<xsl:apply-templates select="radio" />
				<xsl:apply-templates select="autocomplete" />
				
				
			
				<xsl:apply-templates select="measurement" />
				<xsl:apply-templates select="dropdownAlternative" />
				<xsl:apply-templates select="comboAlternative" />
				<xsl:apply-templates select="comboSelector" />
				
				<xsl:apply-templates select="filterDateRange" />
				<xsl:apply-templates select="filterAmount" />
				
				
				<xsl:apply-templates select="availableFiltersList" />
				<xsl:apply-templates select="multipleCC" />
				<xsl:apply-templates select="multiNTLogon" />
				
				<xsl:apply-templates select="htmlEditor" />
				
				<xsl:apply-templates select="itemPopUp" />

			</xsl:element>
			
			
				
		</xsl:element>
	
	</xsl:template>
	
	
	<xsl:template match="help">
	
	<div id="help_div_{@id}" class="help">
		<xsl:apply-templates select="para" />
	</div>
	
	<iframe id="help_iframe_{@id}" src="javascript:false;" scrolling="no" frameborder="0"  style="position:absolute; top:0px; left:0px; display:none;">-</iframe>
	
	</xsl:template>
	
	
	<xsl:template match="filterRow">
	
		<tr>
			<xsl:element name="td">
				<xsl:attribute name="style">background: #EFEFEF; border-top: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF; border-right: 1px solid #CCCCCC;</xsl:attribute>
				<xsl:attribute name="width">28%</xsl:attribute>
				<xsl:value-of select="@title" />:
				<br/><br/><input type="submit" value="Remove Filter" onclick="buttonPress('removeFilter_{@name}');" />
			</xsl:element>
			
			<xsl:element name="td">
				<xsl:attribute name="style">background: #EFEFEF; border-top: 1px solid #FFFFFF; border-bottom: 1px solid #FFFFFF;</xsl:attribute>	
				<xsl:apply-templates select="filterList" />
				<xsl:apply-templates select="filterBetweenNumber" />	
				<xsl:apply-templates select="filterBetweenDate" />
			</xsl:element>
		</tr>
	
	</xsl:template>
	
	<xsl:template match="buttonrow">
	
		<tr bgcolor="#CCCCCC">
			<td colspan="2" align="center">
				<xsl:apply-templates select="submit" />
			</td>
		</tr>
	
	</xsl:template>
</xsl:stylesheet>