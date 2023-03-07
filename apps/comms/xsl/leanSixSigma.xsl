<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="comm">
	
	
	</xsl:template>
	
	<xsl:template match="commsLeanSixSigma">
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
	
				<td valign="top" style="padding: 10px;">
				
					<div class="snapin_top">
    	                <div class="snapin_top_3">
                    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:lean_six_sigma}</p>
                    	</div>
                  	</div>
                  	
                  	<div class="snapin_content">
                        <div class="snapin_content_3">
					
						<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
							<tr>
								<td style="padding: 15px;">
								<a name="#top"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								
								<h2>Lean Six Sigma Outline</h2>
						
								<h3><u>Sections</u></h3>
								<ol>
									<li><a href="#background">Background</a></li>
									<li><a href="#continuous">Why a Continuous Improvement Programme?</a></li>
									<li><a href="#whatislean">What is Lean?</a></li>
									<li><a href="#sevenwastes">What are the Toyota Seven Wastes?</a></li>
									<li><a href="#definitions">Some Lean Definitions:</a></li>
									<li><a href="#sixsigma">What is Six Sigma?</a></li>
									<li><a href="#implementationplan">General Implementation Plan:</a></li>
									<li><a href="#schedule">Proposed Implementation Schedule for Beta Sites:</a></li>
									<li><a href="#rollout">Roll-out to other Sites:</a></li>
								</ol>
								
								<a name="background"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>Background</u></h3>
								<p>During a strategy workshop in Windsor, Connecticut (Oct 1st through 3rd, 2008) a work stream was identified to develop an "Operations Strategy" to match the emerging "Vision" and "Commercial Strategy".  As a key component of the Operations Work Stream this Team considered the selection of a recognized World Class Continuous Improvement Programme for implementation across the Group.</p>
								<p>From October through December 2008, the Operations Team identified and evaluated a number of continuous improvement methodologies that might be adopted by Scapa such as Total Quality Management, Theory of Constraints, Knowledge Based Management, Lean, and Six Sigma.  Following separate research by each of the Team members and discussions including sessions with three independent consultants in Canada, UK, and USA the Operations Team had the following recommendations approved during the January 2009 Strategy workshop in Ashton, UK:</p>
								<ul>
									<li>That Scapa consider the implementation of Lean Six Sigma (LSS) as the model for continuous process improvement</li>
									<li>That the implementation plan include:</li>
									<ul>
										<li>Sr. Management Orientation</li>
										<li>Selection of one beta site in Europe, NA, and Korea for simultaneous implementation</li>
										<li>Common project scope.</li>
										<li>Regional implementation partners.</li>
										<li>Involvement by Commercial and R&amp;D.</li>
										<li>Initial focus on Lean Methodology.</li>
										<li>Introduction of Six Sigma as second phase.</li>
										<li>Roll out to other sites once confident of success and to allow for sharing of experiences/skills.</li>
									</ul>
								</ul>
								<p><a href="#top">(top)</a></p>
								
								<a name="continuous"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>Why a Continuous Improvement Programme?</u></h3>
								<ul>
									<li>Integral to vision to be "World Class".</li>
									<li>Strongly supports objective to be highly agile.</li>
									<li>Optimizes value within business processes.</li>
									<li>Significantly supports investment in people and the organization.</li>
									<li>Simplifies management processes through empowerment. </li>
									<li>Synchronous with Health &amp; Safety and Environmental goals. </li>
									<li>Influential on achieving consistent quality of products.</li>
									<li>Contributes strongly to improved TWC performance.</li>
									<li>Generally improves competitiveness and overall success of Scapa.</li>
								</ul>
								<p><a href="#top">(top)</a></p>
						
								<a name="whatislean"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>What is Lean?</u></h3>
								<ul>
									<li>"Lean" is the philosophy of continuously identifying workplace wastes and ineffectiveness.</li>
									<li>"Waste" is defined as anything not necessary to produce a product or more value with less work.</li>
									<li>"Lean Manufacturing" is a process management philosophy derived mostly from the Toyota Production System (TPS).</li>
									<li>"Lean" is renowned for its focus on reduction of the original Toyota <strong><em>seven wastes</em></strong> in order to improve overall customer value.</li>
								</ul>
								<p><a href="#top">(top)</a></p>
						
								<a name="sevenwastes"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>What are the Toyota Seven Wastes?</u></h3>
								<ul>
									<li><strong>Defects</strong> - quality defects that prevent use.</li>
									<li><strong>Over-production</strong> - manufacturing more than needed.</li>
									<li><strong>Conveyance</strong> - movement of product with no added value.</li>
									<li><strong>Waiting</strong> - process delays waiting for materials or another operation.</li>
									<li><strong>Inventory</strong> - materials not being actively processed.</li>
									<li><strong>Motion</strong> - non-value worker or equipment motion.</li>
									<li><strong>Over-processing</strong> - over design or using a more expensive or valuable resource than needed.</li>
								</ul>
								<p><a href="#top">(top)</a></p>
								
								<a name="definitions"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>Some Lean Definitions:</u></h3>
								<ul>
									<li><strong>5-S</strong> - the simplification and organization of work cells within the operation.  It focuses on organization, communication, housekeeping, health &amp; safety, etc.  (Separate, Simplify, Sanitize, Standardize, Sustain)</li>
									<li><strong>Value Stream Mapping</strong> - identifies every process in a product's flow giving visibility to value and non-value-added steps.</li>
									<li><strong>Problem Solving</strong> - This involves many common problem solving approaches, PDCA (Plan, Do, Check, Act), Wishbone diagrams, Paredo, etc.</li>
									<li><strong>Kaizen</strong> - Rapid Improvement Methodology - short-term, concentrated, lightning paced attack on workplace wastes and ineffectiveness - <strong>"Creativity before Capital"</strong></li>
									<li><strong>Lean Supervisor Training</strong> - training of individuals who need to accomplish objectives through people.  Transitions these individuals from managers to leaders.</li>
								</ul>
								
								<p><a href="#top">(top)</a></p>
								
								<a name="sixsigma"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
								<h3><u>What is Six Sigma?</u></h3>
								<ul>
									<li><strong>Six Sigma</strong> focuses on reduction of variation for the purpose of removing the causes of defects and errors in manufacturing and business processes (statistical analysis).</li>
									<li><strong>Six Sigma</strong> is considered achieved when defects are less than 3.4 defects per million opportunities.</li>
									<li><strong>Black Belts</strong> and <strong>Green Belts</strong> are highly trained individuals in the use of Six Sigma statistical methods and have proven ability to successfully apply this methodology to process improvements.</li>
									<li><strong>5 Common Six Sigma Steps (DMAIC)</strong> - Define, Measure, Analyze, Improve, Control</li>
								</ul>
								<p><a href="#top">(top)</a></p>
								
								<h3><u><a name="implementationplan">General Implementation Plan:</a></u></h3>
								<p>While each region may vary their implementation plan to accommodate size, available skills, and regional needs the general plan for implementation would be as follows:</p>
								<ol>
									<li>General communication to Scapa management during the February 2009 strategy roll-out session in Ashton. <strong>Complete</strong></li>
									<li>Announcement of the plan to implement Lean Six Sigma throughout Scapa as key component of the new Vision and Market Focused business strategy. <strong>Complete</strong></li>
									<li>Two-day introduction for Sr. Management with time and location to be determined.  This introduction is not part of the critical path for implementation but should be ideally completed within the first 3-months of program implementation.</li>
									<li>During March 09, Project Governance will be established and implementation plans will be confirmed.</li>
									<li>Each beta site to identify and contract a credited Implementation Partner to assist with communication, development of an implementation plan, training, key team development, value stream mapping, and support of initial "Kaizen" projects.</li>
									<li>General communication to include all site members at the Beta sites.  This communication will include an introduction to Lean principles and outline the "journey" that is about to be launched.</li>
									<li>Identification of local implementation team (primary facilitators and participants).</li>
									<li>Completion of specific Lean Training will take place over a period of approximately 6 months.</li>
									<li>Implementation of a series of 6 to 8 "Kaizen" (process improvement) events will be staged following the initial training period for the primary purpose of entrenching skills and establishing an internally sustainable program.</li>
									<li>At such time as deemed appropriate but no earlier than month seven (7) the beta site would introduce Six Sigma as a tool to support the Lean culture and methodology.</li>
								</ol>
								<p><a href="#top">(top)</a></p>
						
								<h3><u><a name="schedule">Proposed Implementation Schedule for Beta Sites:</a></u></h3>
								<img src="../../data/Old Document Tree/leanSix/lss_chart.jpg" />
								<p><a href="#top">(top)</a></p>
						
								<h3><u><a name="">Proposed Project Governance:</a></u></h3>
								<p><strong>Project Sponsor:</strong> Ian Marchant has been appointed to provide overall guidance to the LSS project and monitor project progress.  He will intervene as necessary to remove barriers to success and provide additional resources as required.</p>
								<p><strong>Executive Steering Committee (ESC):</strong> This committee will be comprised of group of multi-functional Sr. Managers to oversee the global implementation of the LSS project from a standpoint of content, confirmation of implementation plans, monitoring of progress, and identification of barriers to success.  The ESC will support the Global Project Leader and be responsible to Project Sponsor.</p>
								<p><strong>Global Project Leader (GPL):</strong> Andy Boldt has been appointed as Global Project Leader and will be responsible for the direct management of the LSS project on a global basis.  The GPL will liaise on a regular basis with the Regional Project Leaders and Site LSS Steering Committees to ensure that robust project implementation plans are established and executed on a timely and fiscally responsible basis.  The GPL will provide assistance to Site Teams and the Regional Project Leaders as required to overcome issues, consider resources, and employ additional expertise if required.  The GPL will report overall project progress to the Executive Steering Committee and Sr. Executive Management and the Board on a periodic basis.</p>
								<p><strong>Regional Project Leader (RPL):</strong> The RPL will manage LSS implementation within their respective region.  They will work directly with the Site Steering Committees on a very regular basis to monitor progress, resolve issues, and ensure that project objectives are being met.  The Regional Project Leaders will be Andy Boldt (NA), Derek Sherwin (Europe), and KY Park (Korea).</p>
								<p><strong>Site Steering Committee (SSC):</strong> A SCC will be comprised of a multi-functional team of key site management.  They will assist with the development of the site implementation plan and participate directly in the successful execution of this plan.   They will meet monthly to review progress and identify long and short term actions for the site as it relates to LSS.</p>
								<p><a href="#top">(top)</a></p>
						
								<h3><u><a name="rollout">Roll-out to other Sites:</a></u></h3>
								<p>Once we have established a firm base at each of the beta implementations, a roll-out plan for all other sites within each region will be prepared for consideration by the Executive Steering Committee.  The experience and successes developed during the beta implementations will be used as a springboard for successful implementation elsewhere.  It is highly probable that the roll-out will involve multiple sites simultaneously within a region.</p>
								<p><a href="#top">(top)</a></p>
								
								</td>
							</tr>
						</table>
						
					</div></div>
					
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>