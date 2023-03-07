<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="comboCustomColumns">
		<script language="JavaScript">
						function selectAllColumns(){
							i = 0;
							var selColumns = document.getElementById('columns');
							if(selColumns){
								while(i != selColumns.options.length){
									selColumns.options[i].selected = true;
									i++;
								}
							}
						}
		</script>
		 <script language="JavaScript">
			function moveSelectionRight(){
				var i = 0;
				var selColumns = document.getElementById('columns');
				while(i != document.form.columnsorig.length){
					if(document.form.columnsorig.options[i].selected){
						//we now need to check if it is already in the list - if not add
						var foundMatch = false;
						var j = 0;
						while(j != selColumns.options.length){
							if(selColumns.options[j].value == document.form.columnsorig.options[i].value)
								foundMatch = true;
							j++;
						}
						if(!foundMatch)
							selColumns.options[selColumns.options.length] = new Option(document.form.columnsorig.options[i].value,document.form.columnsorig.options[i].value);
					}
					i++;
				}
			}
			function moveSelectionLeft(){	
				var i = 0;
				var toDelete = new Array();
				var selColumns = document.getElementById('columns');
				var loopLength = selColumns.options.length;
				i = (loopLength-1);
				while(i != -1){
					if(selColumns.options[i].selected){
						selColumns.options[i] = null;
					}
					i--;
				}
			}
		</script>

<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>	
			<xsl:element name="select">
			
				<xsl:attribute name="name">columnsorig</xsl:attribute>
				<xsl:attribute name="multiple">true</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="required = 'true'">
						<xsl:attribute name="class">dropdown required</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="class">dropdown optional</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
				
				
				<xsl:for-each select = "option">
					<xsl:choose>
					<xsl:when test="@selected='yes'">
						<option value="{@name}" selected="selected"><xsl:value-of select="."/></option>
					</xsl:when>
					<xsl:otherwise>
						<option value="{@name}"><xsl:value-of select="."/></option>
					</xsl:otherwise>
					</xsl:choose>
			</xsl:for-each>	
		   
			</xsl:element>
		</td>
		<td>				
		<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveSelectionRight();" /> 
				<br />
				<br />
		<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveSelectionLeft();" /> 
		</td>
		<td valign="top">
			<xsl:element name="select">
			
				<xsl:attribute name="name">columns[]</xsl:attribute>
				<xsl:attribute name="id">columns</xsl:attribute>
				<xsl:attribute name="multiple">true</xsl:attribute>
				
				<xsl:choose>
					<xsl:when test="required = 'true'">
						<xsl:attribute name="class">dropdown required</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="class">dropdown optional</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
				
				
				<xsl:for-each select = "option">
					<xsl:choose>
					<xsl:when test="@selected='yes'">
						<option value="{@name}" selected="selected"><xsl:value-of select="."/></option>
					</xsl:when>
					</xsl:choose>
			</xsl:for-each>	
		   
			</xsl:element>
		</td>
	</tr>
</table>
	
	</xsl:template>
	
</xsl:stylesheet>