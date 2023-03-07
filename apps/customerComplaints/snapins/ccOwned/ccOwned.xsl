<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="ccOwned">
		<style type="text/css">
			a.saved, span.saved
			{
				color: #12AA67;
			}
			a.saved:hover
			{
				color: red;
			}
			
			a.NA, span.NA
			{
				color: #FF6633;
			}
			a.NA:hover
			{
				color: red;
			}
			
			#legend ul
			{
				list-style-type: none;
				padding-left: 0px;
				margin: 0px;
			}
			#legend ul li
			{
				margin-bottom: 2px;
			}
			#legend ul li span
			{
				float: left;
				width: 10px;
				margin-right: 10px;
				padding: 0px 2px;
			}
			div.legend_text 
			{
				margin-left: 18px;
				color: #000;
				width: 220px;
				margin-top: -3px;
				font-style: italic;
			}
			#legend_submitted, #legend_saved, #legend_NA
			{
				display: block;
				float: left;
				width: 10px;
				height: 10px;
				margin: 6px 0 0 0;				
			}
			#legend_submitted
			{
				background: #000;
			}
			#legend_saved
			{
				background: #12AA67;
			}
			#legend_NA
			{
				background: #FF6633;
			}
		</style>
		<table cellspacing="0" width="260">
			<tr>
				<td colspan="2" style="font-size: 1.12em;">
					<strong>
						{TRANSLATE:complaint}/{TRANSLATE:conclusion}
					</strong>
				</td>
			</tr>
			<xsl:choose>
				<xsl:when test="ownedComplaintCount > 0">	
					<tr>
						<td><strong>ID</strong></td><td><strong>{TRANSLATE:name}</strong></td>
					</tr>
					<xsl:apply-templates select="complaintOwned" />
				</xsl:when>
				
				<xsl:otherwise>
					<tr><td colspan="2">{TRANSLATE:none}</td></tr>
				</xsl:otherwise>
			</xsl:choose>
			
			<tr> <td colspan="2"> <hr/> </td> </tr>
			
			<tr>
				<td colspan="2" style="font-size: 1.12em;">
					<strong>
						{TRANSLATE:evaluation}
					</strong>
				</td>
			</tr>
			<xsl:choose>
				<xsl:when test="ownedEvaluationCount > 0">
					<tr>
						<td><strong>ID</strong></td><td><strong>{TRANSLATE:name}</strong></td>
					</tr>
					<xsl:apply-templates select="evaluationOwned" />
				</xsl:when>
				
				<xsl:otherwise>
					<tr><td colspan="2">{TRANSLATE:none}</td></tr>
				</xsl:otherwise>
			</xsl:choose>
			
			<tr> <td colspan="2"> <hr/> </td> </tr>
			
			<tr>
				<td id="legend" colspan="2">
					<strong>{TRANSLATE:legend}:</strong><br/>
					<ul>
						<li>
							<div id="legend_submitted"></div>
							<div class="legend_text"> {TRANSLATE:submitted}</div>
						</li>
						<li>
							<div id="legend_saved"></div>
							<div class="legend_text"> {TRANSLATE:saved}</div>
						</li>
						<li>
							<div id="legend_NA"></div>
							<div class="legend_text"> {TRANSLATE:submit_evaluation}</div>
						</li>
					</ul>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	<xsl:template match="complaintOwned">
    	<tr>
			<xsl:choose>
				<xsl:when test="saved">
					<td>
						<a href="/apps/customerComplaints/index?complaintId={id}" class="saved">
							<xsl:value-of select="id" />
						</a>
					</td>
					<td>
						<span class="saved">
							<xsl:value-of select="customer" />
						</span>
					</td>
				</xsl:when>
				<xsl:otherwise>
					<td>
						<a href="/apps/customerComplaints/index?complaintId={id}">
							<xsl:value-of select="id" />
						</a>
					</td>
					<td>
						<span>
							<xsl:value-of select="customer" />
						</span>
					</td>
				</xsl:otherwise>
			</xsl:choose>
    	</tr>
    </xsl:template>
	
	<xsl:template match="evaluationOwned">
    	<tr>
			<xsl:choose>
				<xsl:when test="NA">
					<td>
						<a href="/apps/customerComplaints/index?complaintId={id}" class="NA">
							<xsl:value-of select="id" />
						</a>
					</td>
					<td>
						<span class="NA">
							<xsl:value-of select="customer" />
						</span>
					</td>
				</xsl:when>
				<xsl:when test="saved">
					<td>
						<a href="/apps/customerComplaints/index?complaintId={id}" class="saved">
							<xsl:value-of select="id" />
						</a>
					</td>
					<td>
						<span class="saved">
							<xsl:value-of select="customer" />
						</span>
					</td>
				</xsl:when>
				<xsl:otherwise>
					<td>
						<a href="/apps/customerComplaints/index?complaintId={id}">
							<xsl:value-of select="id" />
						</a>
					</td>
					<td>
						<span>
							<xsl:value-of select="customer" />
						</span>
					</td>
				</xsl:otherwise>
			</xsl:choose>
    	</tr>
    </xsl:template>
    
</xsl:stylesheet>