<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:include href="../../../xsl/global.xsl"/>

	<xsl:template match="comm">
	
	
	</xsl:template>
	
	<xsl:template match="commsScapaVision">
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
                    	  	<p style="margin: 0; font-weight: bold; color: #FFFFFF;">{TRANSLATE:scapa_vision}</p>
                    	</div>
                  	</div>
                  	
                  	<div class="snapin_content">
                        <div class="snapin_content_3">
                        	<table cellspacing="0" width="100%" style="background: #FFFFFF; border: 1px solid #CCCCCC; padding: 10px;" align="absmiddle">
								<tr>
									<td style="padding: 15px;">										
										
										<img src="/images/One_Scapa_Logo2.jpg" alt="Scapa Vision" />
									
										<xsl:choose>
											<!-- ITALIAN -->
											<xsl:when test="lang='ITALIAN'">
											
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Scapa sta cambiando</h4>
												
												<p>	L'introduzione dello "Scapa Change Programme" e l'avvio del nostro piano quinquennale per trasformare la nostra societa in uno specialista di soluzioni adesive di classe mondiale, utilizzando al meglio il potenziale delle risorse individuali, di gruppo ed aziendali.</p>

												<p>	Abbiamo l'ambizioso piano di far crescere e rivoluzionare il modo in cui operiamo.</p>
												<p>	Quanto esposto nella nostra visione ci indica dove intendiamo essere da qui a cinque anni ed i nostri valori saranno una guida su come arrivarci.</p>

												<p>	Questo e un momento incredibilmente avvincente per tutti noi. Insieme costruiremo un'azienda per la quale tutti noi ne andremo estremamente orgogliosi.</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />La nostra visione</h4>
												
												<p style="font-size: 16px; line-height: 22px; font-weight: bold; color: #CC0000;">{TRANSLATE:scapa_translated_vision}</p>
												
												<ul style="margin-left: 25px;">
													<li style="padding-bottom: 8px;">Guidati dal mercato in stretta collaborazione con i clienti.</li>
													<li style="padding-bottom: 8px;">Gruppo molto motivato</li>
													<li style="padding-bottom: 8px;">Veloce e reattivo</li>
													<li style="padding-bottom: 8px;">Genera valore attraverso il miglioramento continuo.</li>
												</ul>
												
												<p style="font-size: 16px; line-height: 22px; font-weight: bold; color: #CC0000;">Soluzioni adesive di classe mondiale</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvalues"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />I nostri valori</h4>
												
												<p>I nostri valori definiscono il comportamento comune atteso dei dipendenti e della Societa nel lavorare insieme per raggiungere la nostra visione.</p>
												
												<ul>
													<li>Eccellenza</li>
													<li>Impegno</li>
													<li>Integrita</li>
													<li>Lavoro di squadra</li>
													<li>Responsabilita</li>
												</ul>
												
												<p><img src="/images/italian_values.jpg" /></p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
											
											</xsl:when>
											<!-- ITALIAN -->
											
											<!-- FRENCH -->
											<xsl:when test="lang='FRENCH'">
											
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Scapa evolue</h4>
												
												<p>L'introduction du nouveau projet de scapa marque le debut d'un programme de 5 ans visant a transformer notre activite, concevoir des solutions adhesives prestigieuses et developper notre potentiel en tant qu'individus, en tant qu'en qu'equipe et en tant qu'entreprise.</p>

												<p>Nous avons des projets ambitieux et nos methodes sont en constante evolution. Notre vision presente  notre avenir d'ici cinq ans-grace a nos valeurs, nous y parviendrons.</p>
												<p>C'est une periode exceptionnelle pour nous tous. Ensemble, nous construirons une entreprise dont nous seront tous fiers.</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Notre vision</h4>
												
												<p style="font-size: 16px; line-height: 22px; font-weight: bold; color: #CC0000;">{TRANSLATE:scapa_translated_vision}</p>
												
												<ul style="margin-left: 25px;">
													<li style="padding-bottom: 8px;">Ecoute du marche et proximite du client.</li>
													<li style="padding-bottom: 8px;">Equipe exceptionnelle dotee d'une tres grande motivation</li>
													<li style="padding-bottom: 8px;">Rapidite et reactivite</li>
													<li style="padding-bottom: 8px;">Creation de la valeur par l'amelioration permanente</li>
												</ul>
												
												<p style="font-size: 16px; line-height: 22px; font-weight: bold; color: #CC0000;">World Class Tape Solutions</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvalues"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Nos valeurs</h4>
												
												<p>Nos valeurs definissent les comportements que nous attendons de nos collaborateurs et de l'entreprise. C'est ensemble que nous travaillons pour atteindre notre vision.</p>
												
												<ul>
													<li>Excellence</li>
													<li>Engagement</li>
													<li>Integrite</li>
													<li>Travail en equipe</li>
													<li>Responsabilite</li>
												</ul>
												
												<p><img src="/images/french_values.jpg" /></p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Approche Marche</h4>
												
												<ul>
													<li>Nouvelle approche pour developper nos affaires basee sur la definition et le ciblage d'opportunites.</li>
													<li>Cette approche plus proactive implique des changements de comportements, d'attitudes et de process</li>
													<li>Nous avons deja identifie de nombreuses opportunites au travers des etudes marche realisees(Quadrant process)</li>
													<li>Nous reorientons notre structure Commerciale pour etre en phase avec cette nouvelle approche</li>
													<li>La prochaine etape consistera a developper une strategie specifique par marche</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Developpement de la Strategie Commerciale - Quadrant Process</h4>
												
												<p><img src="/images/french_qaud.jpg" /></p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Technologies</h4>
												
												<ul>
													<li>Le developpement de nouveaux produits sera realise suivant l'approche Marche</li>
													<li>Nous investirons dans de nouvelles technologies et applications selon notre strategie Commerciale</li>
													<li>Nous etablirons de nouveaux indicateurs de performance</li>
													<li>Mettre en phase l'organisation de la R&amp;D est un facteur essentiel du succes</li>
													<li>Nous voulons garder et attirer de nouveaux talents en R&amp;D</li>
													<li>La prochaine etape : developper la strategie R&amp;D et la matrice des Technologies (Technology Roadmap)</li>

												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Operations</h4>
												
												<ul>
													<li>L'objectif est de developper des sites de production World Class</li>
													<li>Implementer le Lean Six Sigma (LSS) comme modele d'amelioration continue</li>
													<li>Lean Six Sigma est l'outil le plus employe aujourd'hui par de nombreux concurrents/fournisseurs</li>
													<li>Les sites pilotes sont: Renfrew, Ashton, Coree</li>
													<li>L'implementation se fera ensuite sur les autres sites, en utilisant l'experience et la competence developpees sur les sites pilotes</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Employes</h4>
												
												<ul>
													<li>Un Management efficace et evalue au travers d'entretien d'appreciation</li>
													<li>Formation et developpement des competences</li>
													<li>Revoir les organisations</li>
													<li>Recompense et reconnaissance</li>
													<li>Mettre en place des procedures pour le recrutement et la fidelisation des employes</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Notre engagement vis a vis de vous</h4>
												
												<ul>
													<li>Revue des process de communication pour:</li>
													<li>Introduire des voies de communication simples, consistantes et efficaces</li>
													<li>Etablir des fonctions leader claires et accessibles</li>
													<li>Prendre en compte les remontees d'information</li>
													<li>Vous soutenir dans cette transformation de notre Business</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>

												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />A quoi ressemble le succes?</h4>
												
												<p><img src="/images/french_success.jpg" /></p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>

											
											</xsl:when>
											<!-- FRENCH -->
											
											<!-- GERMAN -->
											<!--<xsl:when test="lang='GERMAN'">
											
												<h4>{TRANSLATE:scapa_vision}</h4>
												
												<p style="font-size: 15px; line-height: 22px;">{TRANSLATE:scapa_translated_vision}</p>
											
											</xsl:when>-->
											<!-- GERMAN -->
											
											<!-- SPANISH IN HERE AT SOME POINT -->
											
											<!-- ENGLISH -->
											<xsl:otherwise>
											
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Scapa is Changing</h4>

												<p>The introduction of the OneScapa Change Programme is the start of our 5-year plan to transform our business into a world class operator in our field and to realise our potential as individuals, as a team and as a company.</p>
												
												<p>We have ambitious plans to grow and evolve the way we do things and our new vision statement sets out where we intend to be five years from now and our values will guide us in how we get there.</p>
												
												<p>This is an incredibly exciting time for us all and with your involvement we will together build a business of which we can all be extremely proud.</p>
												
												<ol style="margin-left: 25px;">
													<li style="padding-bottom: 8px;"><a href="#ourvision">Our Vision</a></li>
													<li style="padding-bottom: 8px;"><a href="#ourvalues">Our Values</a></li>
													<li style="padding-bottom: 8px;"><a href="#marketledapproach">Market Led Approach</a></li>
													<li style="padding-bottom: 8px;"><a href="#developquadprocess">Developing the Commercial Strategy - Quadrant Process</a></li>
													<li style="padding-bottom: 8px;"><a href="#technology">Technology</a></li>
													<li style="padding-bottom: 8px;"><a href="#operations">Operations</a></li>
													<li style="padding-bottom: 8px;"><a href="#people">People</a></li>
													<li style="padding-bottom: 8px;"><a href="#commitment">Our Commitment To You</a></li>
													<li style="padding-bottom: 8px;"><a href="#success">What does success look like?</a></li>
													<li style="padding-bottom: 8px;"><a href="#summary">Summary</a></li>
												</ol>
												
												<a name="ourvision"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Our Vision</h4>
		
												<p><img src="/images/scapa_vision.jpg" alt="Scapa Vision" /></p>
												
												<p style="font-size: 16px; line-height: 22px; font-weight: bold; color: #CC0000;">"World class, inspired, market driven team, focused on optimising customer and shareholder value through responsible, agile delivery of specialist tape solutions."</p>
												
												<p>This means we are:</p>
												
												<ul style="list-style-type: arrow;">
													<li>Market led with customer intimacy</li>
													<li>An exceptional highly motivated team</li>
													<li>Fast and responsive</li>
													<li>Delivering value through continuous improvement</li>
													<li>World class tape solutions</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Vision">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="ourvalues"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Our Values</h4>
		
												<p>Our values define the behaviours we expect from employees and the Company as we work together to achieve our vision.</p>
												
												<ul>
													<li>Excellence</li>
													<li>Commitment</li>
													<li>Team work</li>
													<li>Responsibility</li>
													<li>Integrity</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Our Values">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="marketledapproach"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Market Led Approach</h4>
												
												<p>
													<ul>
														<li>New approach to business development based on defining market opportunities and pursuing them</li>
														<li>More proactive approach to doing business requires changes in our behaviours, attitudes and processes</li>
														<li>We have identified opportunities already through the renowned Quadrant process</li>
														<li>We are restructuring our Commercial Organisation to align with the new approach</li>
														<li>Developing individual market strategies is the next step</li>
													</ul>
												</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Market Led Approach">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="developquadprocess"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Developing the Commercial Strategy - Quadrant Process</h4>
												
												<p><img src="/images/quad_process.jpg" alt="Developing the Commercial Strategy - Quadrant Process" /></p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Quadrant Process">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="technology"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Technology</h4>
												
												<p>
												<ul>
													<li>Product development will be market led</li>
													<li>We will invest in new technology development and applications according to our commercial strategy</li>
													<li>We will implement performance measurement metrics</li>
													<li>Realignment of the technical organisation is essential for success</li>
													<li>We want to retain and attract the top R&amp;D talent</li>
													<li>Next step is to develop the R&amp;D Strategy and Technology Roadmap</li>
												</ul>
												</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Technology">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="operations"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Operations</h4>
												
												<p>
												
												<ul>
													<li>Target is world class operations</li>
													<li>Implement Lean Six Sigma (LSS) as our model for continuous process improvement across Scapa</li>
													<li>Lean Six Sigma is the leading initiative today employed by many of our competitors/suppliers</li>
													<li>Pilot locations are: Renfrew, Ashton, Korea</li>
													<li>Roll out to other sites once confident of success and share experiences/skills</li>
												</ul>
												</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Operations">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="people"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />People</h4>
												
												<p>
												<ul>
													<li>Performance Management and Appraisal</li>
													<li>Training and Development</li>
													<li>Organisational Design</li>
													<li>Reward and Recognition</li>
													<li>Recruitment, Retention and Exit Procedures</li>
												</ul>
											
												</p>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=People">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="commitment"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Our Commitment To You</h4>
												
												<p>
												Reviewing communications across the business to:
												</p>

												<ul>
													<li>Introduce simple, consistent and effective communications channels</li>
													<li>Provide leadership visibility</li>
													<li>Gather feedback which we will act upon</li>
													<li>Support you as we transform our business</li>
												</ul>
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Commitment">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="success"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />What does success look like? </h4>
												
												
												<p><img src="/images/success_graph.jpg" alt="What does success look like?" /></p>
												
												
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Success">{TRANSLATE:ask_a_question}</a></p>
												
												<a name="summary"><img src="blank.gif" width="1px;" height="1px;" align="left" /></a>
												<h4><img src="/images/icons2020/arrow_right.png" align="left" style="padding-right: 5px;" />Summary</h4>
												
												
												<ul>
													<li>Change is not optional - We have a huge opportunity</li>
													<li>The new strategy, vision and values will guide everything we do</li>
													<li>We are one team, "One Scapa", working together to achieve success</li>
													<li>Our market-led approach to doing business is a fundamental strategic shift</li>
													<li>We are realigning our technology investment decisions to our market approach</li>
													<li>We strive for world class operational performance using Lean Six Sigma</li>
													<li>We are investing in our people to provide rewarding careers</li>
													<li>Business organisation structure is essential and we are taking time to get this right</li>
													<li>Communications will improve and that starts today</li>
												</ul>
																								
												<p style="padding-bottom: 30px;"><a href="#top">{TRANSLATE:back_to_top}</a> | <a href="askAQuestion?type=askAQuestion&amp;subject=Summary">{TRANSLATE:ask_a_question}</a></p>
												
											</xsl:otherwise>
										
										</xsl:choose>
									</td>
								</tr>
							</table>
                        </div>
                    </div>
						
				</td>
			</tr>
		</table>
	</xsl:template>
	
</xsl:stylesheet>