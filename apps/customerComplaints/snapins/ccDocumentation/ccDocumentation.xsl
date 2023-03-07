<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccDocumentation">
		
		<a href="javascript: d.openAll();">Expand All</a> | <a href="javascript: d.closeAll();">Collapse All</a>
		
		<br /><br />

		<script type="text/javascript" >
		
			// d.add(21,2,'Customer Care List','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/European/Customer%20Care%20Matrix%20Europe.xls');
				
			<![CDATA[
			
				d = new dTree('d');

				d.add(0,-1,'Complaints Documentation');
				
				d.add(1,0,'Global Reference Docs');
				d.add(10,1,'Credit Notes Authorisation Flowchart','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Credit%20Notes%20Authorisation%20Flowchart.ppt');
				d.add(11,1,'Global credit authorisation matrix','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Global%20credit%20authorisation%20matrix.xlsx');
				d.add(12,1,'Goods Return Approval Matrix','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Goods%20Return%20Approval%20Matrix.xlsx');
				d.add(13,1,'ICS Process Overview','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/ICS%20Process%20Overview%20042511.pdf.pdf');
				d.add(14,1,'Process Owner Matrix','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Process%20Owner%20Matrix.xls');
				d.add(15,1,'Supplier Handbook (EN)','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Supplier%20Hanbook%20english.pdf');
				d.add(16,1,'Supplier Handbook (FR)','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Supplier%20Handbook%20french.pdf');
				d.add(17,1,'Supplier Handbook (IT)','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Supplier%20Handbook%20italien.pdf');
				d.add(18,1,'Supplier Handbook (DE)','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Supplier%20Handboook%20German.pdf');
				d.add(19,1,'Supplier Complaint Process Flow','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/Global/Supplier_Complaint_Process_Flow.pdf');
				
				d.add(2,0,'European Reference Docs');
				d.add(20,2,'Shipping Department Matrix','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/European/Shipping%20departement%20matrix.xls');
				
				d.add(3,0,'North American Reference Docs');
				d.add(30,3,'NA Approval Matrix','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/NAApprovalMatrix.xlsx');
				d.add(31,3,'Return Instructions Inglewood','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/Return%20Instructions%20Inglewood.pdf');
				d.add(32,3,'Return Instructions Renfrew','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/Return%20Instructions%20Renfrew.pdf');
				d.add(33,3,'Return Instructions Windsor','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/Return%20Instructions%20Windsor.pdf');
				d.add(34,3,'Return Intructions Liverpool','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/Return%20Instructions%20Liverpool.docx');
				d.add(35,3,'US Returns To Renfrew','http://ukdunapp022/Document%20Management%20System/QUALITY/Complaints%20System%20Documentation/NA/US%20Returns%20to%20Renfrew.pdf');
				
				d.add(4,0,'External Links');
				d.add(40,4,'Scapa Extranet','http://ext.scapa.com');
				d.add(41,4,'Valeo Complaint system','https://suppliers.valeo.com/suppliers/');
				d.add(42,4,'Yazaki Complaint Management','https://gpdb.yazaki-europe.com/sm_apps/');
				d.add(43,4,'Convisint Complaint Management','https://us.sso.covisint.com/jsp/preLogin.jsp?host=https://portal.covisint.com&ct_orig_uri=%2Fwps%2Fprivate%2F');
				d.add(44,4,'PSA Complaint Management','http://b2b.psa-peugeot-citroen.com/index.htm');
				d.add(45,4,'Lear Quality System','https://access2.lear.com/');
				d.add(46,4,'Volvo System','https://xnet.volvo.com/xnet/resources/login.aspx?entrydomain=supplierportal.volvo.com');
				d.add(47,4,'VW Group','http://www.vwgroupsupply.com/b2b/vwb2b_folder/supplypublic/en.html');
					
				document.write(d);
			
			]]>
			
		</script>
			
	</xsl:template>
	
</xsl:stylesheet>