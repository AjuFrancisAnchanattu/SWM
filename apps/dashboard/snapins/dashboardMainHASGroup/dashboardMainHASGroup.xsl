<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="dashboardMainHASGroup">
		<xsl:choose>
		
		<xsl:when test="allowed='1'">
	
		<table cellspacing="2" width="260">
			
			<!-- DTREE -->
			
			<a href="javascript: d.openAll();">Expand All</a> | <a href="javascript: d.closeAll();">Collapse All</a>
		
			<br /><br />
	
			<script type="text/javascript" >
			
				<![CDATA[
				
					d = new dTree('d');
					
					// stop links from opening in a new window
					d.config.target = null;
	
					d.add(0,-1,'Health & Safety Locations');
					
					d.add(1,0,'Group');
				]]>
					
					<xsl:if test="allowedGroup='1'">
						<![CDATA[d.add(2,1,'View Group','/apps/dashboard/healthandsafetyGroupLevel?');]]>
					</xsl:if>
					
				<![CDATA[
					d.add(3,0,'European');
				]]>
					
					<!--<xsl:if test="allowedRegionEurope='1'">
						<![CDATA[d.add(4,3,'View All European','/apps/dashboard/healthandsafetyRegionLevel?region=EUROPE');]]>
					</xsl:if>-->
					
						<xsl:if test="allowedSiteAshton='1'">
							<![CDATA[d.add(5,3,'Ashton','/apps/dashboard/healthandsafetySiteLevel?site=Ashton');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteBarcelona='1'">
							<![CDATA[d.add(6,3,'Barcelona','/apps/dashboard/healthandsafetySiteLevel?site=Barcelona');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteDunstable='1'">
							<![CDATA[d.add(7,3,'Dunstable','/apps/dashboard/healthandsafetySiteLevel?site=Dunstable');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteGhislarengo='1'">
							<![CDATA[d.add(8,3,'Ghislarengo','/apps/dashboard/healthandsafetySiteLevel?site=Ghislarengo');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteMannheim='1'">
							<![CDATA[d.add(9,3,'Mannheim','/apps/dashboard/healthandsafetySiteLevel?site=Mannheim');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteRorschach='1'">
							<![CDATA[d.add(10,3,'Rorschach','/apps/dashboard/healthandsafetySiteLevel?site=Rorschach');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteValence='1'">
							<![CDATA[d.add(11,3,'Valence','/apps/dashboard/healthandsafetySiteLevel?site=Valence');]]>
						</xsl:if>
					
				<![CDATA[
					d.add(12,0,'North America');
				]]>
				
					<!--<xsl:if test="allowedRegionNA='1'">
						<![CDATA[d.add(13,12,'View All NA','/apps/dashboard/healthandsafetyRegionLevel?region=NA');]]>
					</xsl:if>-->
					
						<xsl:if test="allowedSiteCarlstadt='1'">
							<![CDATA[d.add(14,12,'Carlstadt','/apps/dashboard/healthandsafetySiteLevel?site=Carlstadt');]]>
						</xsl:if>
						
						<xsl:if test="allowedSiteInglewood='1'">
							<![CDATA[d.add(15,12,'Inglewood','/apps/dashboard/healthandsafetySiteLevel?site=Inglewood');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteRenfrew='1'">
							<![CDATA[d.add(16,12,'Renfrew','/apps/dashboard/healthandsafetySiteLevel?site=Renfrew');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteSyracuse='1'">
							<![CDATA[d.add(17,12,'Syracuse','/apps/dashboard/healthandsafetySiteLevel?site=Syracuse');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteWindsor='1'">
							<![CDATA[d.add(18,12,'Windsor','/apps/dashboard/healthandsafetySiteLevel?site=Windsor');]]>
						</xsl:if>
					
				<![CDATA[
					d.add(19,0,'Asia');
				]]>
				
					<!--<xsl:if test="allowedRegionAsia='1'">	
						<![CDATA[d.add(20,19,'View All Asia','/apps/dashboard/healthandsafetyRegionLevel?region=ASIA');]]>
					</xsl:if>-->					
					
						<xsl:if test="allowedSiteMalaysia='1'">
							<![CDATA[d.add(21,19,'Malaysia','/apps/dashboard/healthandsafetySiteLevel?site=Malaysia');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteChina='1'">
							<![CDATA[d.add(22,19,'China','/apps/dashboard/healthandsafetySiteLevel?site=China');]]>
						</xsl:if>
							
						<xsl:if test="allowedSiteKorea='1'">
							<![CDATA[d.add(23,19,'Korea','/apps/dashboard/healthandsafetySiteLevel?site=Korea');]]>
						</xsl:if>
					
				<![CDATA[
					document.write(d);
				]]>
				
			</script>
			
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