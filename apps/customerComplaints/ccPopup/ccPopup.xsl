<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccPopup">
		<html>
			<head>
				<link rel="stylesheet" href="/apps/customerComplaints/ccPopup/ccPopup.css"/>
				
				<script src="/javascript/scriptaculous/prototype.js" type="text/javascript">-</script>
				<script src="/javascript/scriptaculous/scriptaculous.js" type="text/javascript">-</script>
			
				<script type="text/javascript" src="/apps/customerComplaints/javascript/RemoteTranslate.js">-</script>
				<script type="text/javascript" src="/apps/customerComplaints/ccPopup/ccPopup.js">-</script>
			</head>
			
			<body>
			
				<div id="content">
				
					<div id="title">
						Copy To
					</div>
					
					<div id="wrapper">
					
						<div class="section">
						
							<div class="sectionInput">
							
								<xsl:element name="input">
									<xsl:attribute name="type">text</xsl:attribute>
									<xsl:attribute name="id">searchEmployee_NTLogon</xsl:attribute>
									<xsl:attribute name="class">invisibleInput</xsl:attribute>
								</xsl:element>
								
								<xsl:element name="input">
									<xsl:attribute name="type">text</xsl:attribute>
									<xsl:attribute name="id">searchEmployee_email</xsl:attribute>
									<xsl:attribute name="class">invisibleInput</xsl:attribute>
								</xsl:element>
								
								<xsl:element name="input">
									<xsl:attribute name="autocomplete">off</xsl:attribute>
									<xsl:attribute name="type">text</xsl:attribute>
									<xsl:attribute name="id">searchEmployee</xsl:attribute>
									<xsl:attribute name="name">searchEmployee</xsl:attribute>
									<xsl:attribute name="maxlength">60</xsl:attribute>
								</xsl:element>
								
								<div class="auto_complete" id="searchEmployee_auto_complete">-</div>
					
								<script type="text/javascript" language="javascript" charset="utf-8">
									new Ajax.Autocompleter( 
										'searchEmployee', 
										'searchEmployee_auto_complete', 
										'/apps/customerComplaints/ajax/employeeEmail?', 
										{updateElement: updateDetails} 
									);
								</script>
								
							</div>
							
							<div class="sectionLink">
						
								<a href="" onclick="return addEmployee();" style="margin-left: 5px;">
									<img src="../../../images/famIcons/add.png" border="0" style="margin-top: 3px;"/>
								</a>
								
							</div>
							
						</div>
						
						<div class="section">
							
							<div class="sectionInput">
							
								<select name="ccPeople" id="ccPeople" size="4">
									<xsl:for-each select="option">
										<option value="{@value}"><xsl:value-of select="@text"/></option>
									</xsl:for-each>
								</select>
								
							</div>
							
							<div class="sectionLink">
						
								<a href="" onclick="return removeEmployee();" style="margin-left: 5px; left: 0; top: 0;">
									<img src="../../../images/famIcons/delete.png" border="0" style="margin-top: 3px;"/>
								</a>
								
							</div>
						
						</div>
						
						<div class="section">
						
							<input type="button" onClick="return addCC('{fieldName}');" value="OK"/>
							
						</div>

					</div>
					
				</div>
				
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>