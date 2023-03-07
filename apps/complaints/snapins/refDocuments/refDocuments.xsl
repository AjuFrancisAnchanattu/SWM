<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="refDocuments">
		
		<a href="javascript: d.openAll();">Expand All</a> | <a href="javascript: d.closeAll();">Collapse All</a>
		
		<br /><br />

		<script type="text/javascript" >
				
			<![CDATA[
			
				d = new dTree('d');

				d.add(0,-1,'Complaints Documentation');
				d.add(1,0,'European Reference Docs','#');
				d.add(2,1,'Credit Authorisation Procedure','/apps/documentLinks/retrieve?docId=215');
				d.add(3,1,'8D Description','/apps/documentLinks/retrieve?docId=217');
				d.add(4,1,'Display Process Owner Matrix','/apps/documentLinks/retrieve?docId=219');
				d.add(5,1,'Display Shipping Department Matrix','/apps/documentLinks/retrieve?docId=221');
				d.add(6,1,'Display Customer Care List','/apps/documentLinks/retrieve?docId=223');
				d.add(7,1,'Credit Authorisation Matrix','/apps/documentLinks/retrieve?docId=225');
				
				d.add(8,0,'External Links','#');
				d.add(9,8,'Scapa Extranet','http://ext.scapa.com');
				d.add(10,8,'Valeo Complaint system','https://suppliers.valeo.com/suppliers/');
				d.add(11,8,'Yazaki Complaint Management','https://gpdb.yazaki-europe.com/sm_apps/');
				d.add(12,8,'Convisint Complaint Management','https://us.sso.covisint.com/jsp/preLogin.jsp?host=https://portal.covisint.com&ct_orig_uri=%2Fwps%2Fprivate%2F');
				d.add(13,8,'PSA Complaint Management','http://b2b.psa-peugeot-citroen.com/index.htm');
				d.add(14,8,'Lear Quality System','https://access2.lear.com/');
				d.add(15,8,'Volvo System','https://xnet.volvo.com/xnet/resources/login.aspx?entrydomain=supplierportal.volvo.com');
				d.add(16,8,'VW Group','http://www.vwgroupsupply.com/b2b/vwb2b_folder/supplypublic/en.html');
								
				d.add(17,0,'Supplier Documentation ','#');
				d.add(18,17,'Supplier Complaint Process Flow','/apps/documentLinks/retrieve?docId=229');
				d.add(19,17,'Supplier Complaint Manual (EN)','/apps/documentLinks/retrieve?docId=231');
				d.add(20,17,'Supplier Complaint Manual (DE)','/apps/documentLinks/retrieve?docId=233');
				d.add(21,17,'Supplier Complaint Manual (FR)','/apps/documentLinks/retrieve?docId=235');
				d.add(22,17,'Supplier Complaint Manual (IT)','/apps/documentLinks/retrieve?docId=237');
				
				d.add(23,0,'North American Reference Docs','#');
				d.add(24,23,'Return Material & Credit Auth','/apps/documentLinks/retrieve?docId=239');
				d.add(25,23,'Process Owner Matrix','/apps/documentLinks/retrieve?docId=241');
				d.add(26,23,'8D Description','/apps/documentLinks/retrieve?docId=243');
				d.add(27,23,'CSR Account Responsibility','/apps/documentLinks/retrieve?docId=245');
				d.add(28,23,'Return Instructions Renfrew','/apps/documentLinks/retrieve?docId=247');
				d.add(29,23,'Return Instructions Windsor','/apps/documentLinks/retrieve?docId=249');
				d.add(30,23,'US Returns To Renfrew','/apps/documentLinks/retrieve?docId=251');
				d.add(31,23,'Return Instructions Liverpool','/apps/documentLinks/retrieve?docId=469');
				d.add(32,23,'Return Instructions Inglewood','/apps/documentLinks/retrieve?docId=255');
				d.add(33,23,'NA Cust Complaint Process Flow','/apps/documentLinks/retrieve?docId=257');
						
				document.write(d);
			
			]]>
			
		</script>
			
	</xsl:template>
	
</xsl:stylesheet>