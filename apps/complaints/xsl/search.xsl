<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">


	<xsl:include href="../../../xsl/global.xsl"/>
		
	<xsl:template match="complaintsSearch">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td valign="top" style="width: 300px; background: url(/images/dotted_background.gif) repeat-y top right;">			
					<div id="snapin_left_container">	
						<xsl:apply-templates select="snapin_left" />
					</div>
				</td>
				<td valign="top" style="padding: 10px;">
					<xsl:apply-templates select="error" />
						<!--
							<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px; margin-bottom: 10px;">
			                   <p style="margin: 0; line-height: 15px;"><strong>Notice:</strong> This is still experimental code.</p>
		          </div>
	          -->
	        <div class="heading_top">
		        <div class="heading_top_1">
			        <div class="heading_top_2">
				        <div class="heading_top_3">
									<p>Create a New Search</p>
								</div>
							</div>
						</div>
					</div>
				
					<xsl:apply-templates select="chooseReport"/>
	
					<xsl:apply-templates select="chooseReportFields"/>
					
					<xsl:apply-templates select="addFilters"/>
	
					<xsl:apply-templates select="columnFilters"/>
					
					<xsl:apply-templates select="supplierColumnFilters"/>
					
					<xsl:apply-templates select="qualityColumnFilters"/>
					
					<xsl:apply-templates select="selectedFilters"/>
					
					<div style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; padding: 5px; background #FFFFFF; text-align: center;">
						<input type="button" value="Run Search" onclick="whatSearchColumns(),buttonPress('run') " />
						<input type="submit" value="Remove All Filters" onclick="buttonPress('removeAllFilters');" />
					</div>
				</td>
			</tr>
		</table>
	</xsl:template>
	
	
	<xsl:template match="chooseReport">
		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	
	
	<xsl:template match="selectedFilters">
		<script language="JavaScript">
		
				function whatSearchColumns()
				{
					var searchColumnsCustomer = document.getElementById('columns');
					var searchColumnsSupplier = document.getElementById('columnsSupplier');
					var searchColumnsQuality = document.getElementById('columnsQuality');
					
					if(searchColumnsCustomer){
						selectAllColumns();	
					}
					if(searchColumnsSupplier){
						selectAllColumnsSuppler();
					}
					if(searchColumnsQuality){
						selectAllColumnsQuality();
					}
				}
		
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
				selectAllColumns();
				
				function selectAllColumnsSuppler(){
					i = 0;
					var selColumns = document.getElementById('columnsSupplier');
					if(selColumns){
						while(i != selColumns.options.length){
							selColumns.options[i].selected = true;
							i++;
						}
					}
				}
				selectAllColumnsSuppler();
				
				function selectAllColumnsQuality(){
					i = 0;
					var selColumns = document.getElementById('columnsQuality');
					if(selColumns){
						while(i != selColumns.options.length){
							selColumns.options[i].selected = true;
							i++;
						}
					}
				}
				selectAllColumnsQuality();
		</script>
			<h1 style="margin-bottom: 10px;">
				Selected Filters
			</h1>
		
			<xsl:choose>
				<xsl:when test="form/group/row">
					<xsl:apply-templates select="form" />
				</xsl:when>
				<xsl:otherwise>
					<p style="border-left: 5px solid #EFEFEF; border-right: 5px solid #EFEFEF; background: #DDDDDD; padding: 5px; margin-top: 0;">None</p>
				</xsl:otherwise>
			</xsl:choose>
			
	</xsl:template>
	
	
	<xsl:template match="addFilters">
	
		<h1 style="margin-bottom: 10px;">Available Filters</h1>

		<xsl:apply-templates select="form"/>
	</xsl:template>
	
	<xsl:template match="columnFilters">
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
				selectAllColumns()
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
				selectAllColumns()
			}
			
		</script>
		<h1 style="margin-bottom: 10px;">Column Filters</h1>
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;">
		<tr id="filtersRow" class="valid_row"><td class="cell_name" width="15%" valign="top"></td>
		<td>

		<table border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>
				<xsl:element name="select">
				<xsl:attribute name="name">columnsorig</xsl:attribute>
				<xsl:attribute name="multiple">true</xsl:attribute>
				<xsl:attribute name="size">10</xsl:attribute>

					<xsl:choose>
						<xsl:when test="required = 'true'">
							<xsl:attribute name="class">dropdown required</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">dropdown optional</xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>

					
					
					
					<xsl:choose>
						<xsl:when test="implementedPermanentCorrectiveActionValidated=1">
							<option value="implementedPermanentCorrectiveActionValidated" selected="1">{TRANSLATE:implemented_permanent_corrective_action_validated}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implementedPermanentCorrectiveActionValidated">{TRANSLATE:implemented_permanent_corrective_action_validated}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implementedPermanentCorrectiveActionValidatedyn=1">
							<option value="implementedPermanentCorrectiveActionValidatedyn" selected="1">{TRANSLATE:was_implemented_permanent_corrective_action_validated}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implementedPermanentCorrectiveActionValidatedyn">{TRANSLATE:was_implemented_permanent_corrective_action_validated}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implementedPermanentCorrectiveActionValidatedDate=1">
							<option value="implementedPermanentCorrectiveActionValidatedDate" selected="1">{TRANSLATE:the_implemented_permanent_corrective_action_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implementedPermanentCorrectiveActionValidatedDate">{TRANSLATE:the_implemented_permanent_corrective_action_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implementedPermanentCorrectiveActionValidatedAuthor=1">
							<option value="implementedPermanentCorrectiveActionValidatedAuthor" selected="1">{TRANSLATE:the_implemented_permanent_corrective_action_validated_author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implementedPermanentCorrectiveActionValidatedAuthor">{TRANSLATE:the_implemented_permanent_corrective_action_validated_author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					
					
					
					<xsl:choose>
						<xsl:when test="action_requested=1">
							<option value="action_requested" selected="1">{TRANSLATE:action_requested}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="action_requested">{TRANSLATE:action_requested}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="analysis=1">
							<option value="analysis" selected="1">{TRANSLATE:analysis}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="analysis">{TRANSLATE:analysis}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="analysis_date=1">
							<option value="analysis_date" selected="1">{TRANSLATE:analysis_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="analysis_date">{TRANSLATE:analysis_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="attributable_process=1">
							<option value="attributable_process" selected="1">{TRANSLATE:attributable_process}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="attributable_process">{TRANSLATE:attributable_process}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="author=1">
							<option value="author" selected="1">{TRANSLATE:author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="author">{TRANSLATE:author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="commercialReason=1">
							<option value="commercialReason" selected="1">{TRANSLATE:commercial_authorisation_reason}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="commercialReason">{TRANSLATE:commercial_authorisation_reason}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financeReason=1">
							<option value="financeReason" selected="1">{TRANSLATE:finance_authorisation_reason}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="financeReason">{TRANSLATE:finance_authorisation_reason}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="awaiting_batch_number=1">
							<option value="awaiting_batch_number" selected="1">{TRANSLATE:awaiting_batch_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="awaiting_batch_number">{TRANSLATE:awaiting_batch_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="awaiting_dimensions=1">
							<option value="awaiting_dimensions" selected="1">{TRANSLATE:awaiting_dimensions}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="awaiting_dimensions">{TRANSLATE:awaiting_dimensions}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="awaiting_invoice=1">
							<option value="awaiting_invoice" selected="1">{TRANSLATE:awaiting_invoice}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="awaiting_invoice">{TRANSLATE:awaiting_invoice}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="awaiting_quantity_under_complaint=1">
							<option value="awaiting_quantity_under_complaint" selected="1">{TRANSLATE:awaiting_quantity_under_complaint}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="awaiting_quantity_under_complaint">{TRANSLATE:awaiting_quantity_under_complaint}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="batch_number=1">
							<option value="batch_number" selected="1">{TRANSLATE:batch_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="batch_number">{TRANSLATE:batch_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="BusinessUnit=1">
							<option value="BusinessUnit" selected="1">{TRANSLATE:business_unit}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="BusinessUnit">{TRANSLATE:business_unit}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="carrier_name=1">
							<option value="carrier_name" selected="1">{TRANSLATE:carrier_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="carrier_name">{TRANSLATE:carrier_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="Category=1">
							<option value="Category" selected="1">{TRANSLATE:category}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="Category">{TRANSLATE:category}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="Performancecco=1">
							<option value="Performancecco" selected="1">{TRANSLATE:close_out_performance}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="Performancecco">{TRANSLATE:close_out_performance}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="colour=1">
							<option value="colour" selected="1">{TRANSLATE:colour}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="colour">{TRANSLATE:colour}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="comments=1">
							<option value="comments" selected="1">{TRANSLATE:comments}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="comments">{TRANSLATE:comments}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="reason_for_rejection=1">
							<option value="reason_for_rejection" selected="1">{TRANSLATE:reason_for_rejection}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="reason_for_rejection">{TRANSLATE:reason_for_rejection}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="closed_date=1">
							<option value="closed_date" selected="1">{TRANSLATE:closed_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="closed_date">{TRANSLATE:closed_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="modComplaintReason=1">
							<option value="modComplaintReason" selected="1">{TRANSLATE:mod_complaint_reason}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="modComplaintReason">{TRANSLATE:mod_complaint_reason}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_justified=1">
							<option value="complaint_justified" selected="1">{TRANSLATE:complaint_justified}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="complaint_justified">{TRANSLATE:complaint_justified}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_location=1">
							<option value="complaint_location" selected="1">{TRANSLATE:complaint_location}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="complaint_location">{TRANSLATE:complaint_location}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ComplaintOwner=1">
							<option value="ComplaintOwner" selected="1">{TRANSLATE:complaint_owner}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ComplaintOwner">{TRANSLATE:complaint_owner}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ComplaintType=1">
							<option value="ComplaintType" selected="1">{TRANSLATE:complaint_type}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ComplaintType">{TRANSLATE:complaint_type}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ComplaintValue=1">
							<option value="ComplaintValue" selected="1">{TRANSLATE:complaint_value}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ComplaintValue">{TRANSLATE:complaint_value}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_value_quantity=1">
							<option value="complaint_value_quantity" selected="1">{TRANSLATE:complaint_value_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="complaint_value_quantity">{TRANSLATE:complaint_value_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_value_measurement=1">
							<option value="complaint_value_measurement" selected="1">{TRANSLATE:complaint_value_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="complaint_value_measurement">{TRANSLATE:complaint_value_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="containment_action=1">
							<option value="containment_action" selected="1">{TRANSLATE:containment_action}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="containment_action">{TRANSLATE:containment_action}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="containment_action_author=1">
							<option value="containment_action_author" selected="1">{TRANSLATE:containment_action_author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="containment_action_author">{TRANSLATE:containment_action_author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="containment_action_date=1">
							<option value="containment_action_date" selected="1">{TRANSLATE:containment_action_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="containment_action_date">{TRANSLATE:containment_action_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ccCommercialCredit=1">
							<option value="ccCommercialCredit" selected="1">{TRANSLATE:cc_commercial_credit}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ccCommercialCredit">{TRANSLATE:cc_commercial_credit}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="correct_category=1">
							<option value="correct_category" selected="1">{TRANSLATE:correct_category}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="correct_category">{TRANSLATE:correct_category}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="CreatedDate=1">
							<option value="CreatedDate" selected="1">{TRANSLATE:created_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="CreatedDate">{TRANSLATE:created_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditAdviceDate=1">
							<option value="creditAdviceDate" selected="1">{TRANSLATE:credit_advice_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="creditAdviceDate">{TRANSLATE:credit_advice_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="amount_quantity=1">
							<option value="amount_quantity" selected="1">{TRANSLATE:amount_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="amount_quantity">{TRANSLATE:amount_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="amount_measurement=1">
							<option value="amount_measurement" selected="1">{TRANSLATE:amount_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="amount_measurement">{TRANSLATE:amount_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="commercialLevelCreditAuthorised=1">
							<option value="commercialLevelCreditAuthorised" selected="1">{TRANSLATE:commercial_level_credit_authorised}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="commercialLevelCreditAuthorised">{TRANSLATE:commercial_level_credit_authorised}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="commercialCreditAuthoriser=1">
							<option value="commercialCreditAuthoriser" selected="1">{TRANSLATE:commercial_credit_authoriser}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="commercialCreditAuthoriser">{TRANSLATE:commercial_credit_authoriser}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financeLevelCreditAuthorised=1">
							<option value="financeLevelCreditAuthorised" selected="1">{TRANSLATE:finance_level_credit_authorised}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="financeLevelCreditAuthorised">{TRANSLATE:finance_level_credit_authorised}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financeCreditAuthoriser=1">
							<option value="financeCreditAuthoriser" selected="1">{TRANSLATE:finance_credit_authoriser}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="financeCreditAuthoriser">{TRANSLATE:finance_credit_authoriser}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="credit_note_requested=1">
							<option value="credit_note_requested" selected="1">{TRANSLATE:credit_note_requested}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="credit_note_requested">{TRANSLATE:credit_note_requested}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditNoteValue_quantity=1">
							<option value="creditNoteValue_quantity" selected="1">{TRANSLATE:creditNoteValue_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="creditNoteValue_quantity">{TRANSLATE:creditNoteValue_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditNoteValue_measurement=1">
							<option value="creditNoteValue_measurement" selected="1">{TRANSLATE:creditNoteValue_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="creditNoteValue_measurement">{TRANSLATE:creditNoteValue_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ccCommercialCreditComment=1">
							<option value="ccCommercialCreditComment" selected="1">{TRANSLATE:cc_commercial_credit_comment}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ccCommercialCreditComment">{TRANSLATE:cc_commercial_credit_comment}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="CustomerComplaintStatus=1">
							<option value="CustomerComplaintStatus" selected="1">{TRANSLATE:customer_complaint_status}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="CustomerComplaintStatus">{TRANSLATE:customer_complaint_status}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerCreditNumber=1">
							<option value="customerCreditNumber" selected="1">{TRANSLATE:customer_credit_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerCreditNumber">{TRANSLATE:customer_credit_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customer_item_number=1">
							<option value="customer_item_number" selected="1">{TRANSLATE:customer_item_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customer_item_number">{TRANSLATE:customer_item_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerSpecification=1">
							<option value="customerSpecification" selected="1">{TRANSLATE:customer_specification}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerSpecification">{TRANSLATE:customer_specification}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerSpecificationDate=1">
							<option value="customerSpecificationDate" selected="1">{TRANSLATE:customer_specification_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerSpecificationDate">{TRANSLATE:customer_specification_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="customerSpecificationRef=1">
							<option value="customerSpecificationRef" selected="1">{TRANSLATE:customer_specification_ref}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="customerSpecificationRef">{TRANSLATE:customer_specification_ref}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateCreditNoteRaised=1">
							<option value="dateCreditNoteRaised" selected="1">{TRANSLATE:date_credit_note_raised}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dateCreditNoteRaised">{TRANSLATE:date_credit_note_raised}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dateReturnsReceived=1">
							<option value="dateReturnsReceived" selected="1">{TRANSLATE:date_returns_received}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dateReturnsReceived">{TRANSLATE:date_returns_received}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="date_sample_received=1">
							<option value="date_sample_received" selected="1">{TRANSLATE:date_sample_received}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="date_sample_received">{TRANSLATE:date_sample_received}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="defectiveMaterialAmount_quantity=1">
							<option value="defectiveMaterialAmount_quantity" selected="1">{TRANSLATE:defective_material_amount_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="defectiveMaterialAmount_quantity">{TRANSLATE:defective_material_amount_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="defectiveMaterialAmount_measurement=1">
							<option value="defectiveMaterialAmount_measurement" selected="1">{TRANSLATE:defective_material_amount_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="defectiveMaterialAmount_measurement">{TRANSLATE:defective_material_amount_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="DespatchSite=1">
							<option value="DespatchSite" selected="1">{TRANSLATE:despatch_site}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="DespatchSite">{TRANSLATE:despatch_site}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_length_quantity=1">
							<option value="dimension_length_quantity" selected="1">{TRANSLATE:dimension_length_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_length_quantity">{TRANSLATE:dimension_length_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_length_measurement=1">
							<option value="dimension_length_measurement" selected="1">{TRANSLATE:dimension_length_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_length_measurement">{TRANSLATE:dimension_length_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_thickness_quantity=1">
							<option value="dimension_thickness_quantity" selected="1">{TRANSLATE:dimension_thickness_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_thickness_quantity">{TRANSLATE:dimension_thickness_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_thickness_measurement=1">
							<option value="dimension_thickness_measurement" selected="1">{TRANSLATE:dimension_thickness_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_thickness_measurement">{TRANSLATE:dimension_thickness_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_width_quantity=1">
							<option value="dimension_width_quantity" selected="1">{TRANSLATE:dimension_width_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_width_quantity">{TRANSLATE:dimension_width_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dimension_width_measurement=1">
							<option value="dimension_width_measurement" selected="1">{TRANSLATE:dimension_width_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dimension_width_measurement">{TRANSLATE:dimension_width_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="dispose_goods=1">
							<option value="dispose_goods" selected="1">{TRANSLATE:dispose_goods}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="dispose_goods">{TRANSLATE:dispose_goods}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ExternalSalesName=1">
							<option value="ExternalSalesName" selected="1">{TRANSLATE:external_sales_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ExternalSalesName">{TRANSLATE:external_sales_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="factored_product=1">
							<option value="factored_product" selected="1">{TRANSLATE:factored_product}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="factored_product">{TRANSLATE:factored_product}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="failure_code=1">
							<option value="failure_code" selected="1">{TRANSLATE:failure_code}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="failure_code">{TRANSLATE:failure_code}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="finalComments=1">
							<option value="finalComments" selected="1">{TRANSLATE:final_comments}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="finalComments">{TRANSLATE:final_comments}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="financeCreditNewComplaintOwner=1">
							<option value="financeCreditNewComplaintOwner" selected="1">{TRANSLATE:final_credit_result_sent_to}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="financeCreditNewComplaintOwner">{TRANSLATE:final_credit_result_sent_to}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="fmea=1">
							<option value="fmea" selected="1">{TRANSLATE:fmea}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="fmea">{TRANSLATE:fmea}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="fmeaDate=1">
							<option value="fmeaDate" selected="1">{TRANSLATE:fmea_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="fmeaDate">{TRANSLATE:fmea_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="fmeaRef=1">
							<option value="fmeaRef" selected="1">{TRANSLATE:fmea_ref}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="fmeaRef">{TRANSLATE:fmea_ref}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="g8d=1">
							<option value="g8d" selected="1">{TRANSLATE:g8d}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="g8d">{TRANSLATE:g8d}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="is_sample_received=1">
							<option value="is_sample_received" selected="1">{TRANSLATE:is_sample_received}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="is_sample_received">{TRANSLATE:is_sample_received}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_author=1">
							<option value="implemented_actions_author" selected="1">{TRANSLATE:implemented_actions_author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions_author">{TRANSLATE:implemented_actions_author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ImplementedActionsDate=1">
							<option value="ImplementedActionsDate" selected="1">{TRANSLATE:implemented_actions_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ImplementedActionsDate">{TRANSLATE:implemented_actions_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_estimated=1">
							<option value="implemented_actions_estimated" selected="1">{TRANSLATE:implemented_actions_estimated}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions_estimated">{TRANSLATE:implemented_actions_estimated}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_implementation=1">
							<option value="implemented_actions_implementation" selected="1">{TRANSLATE:implemented_actions_implementation}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions_implementation">{TRANSLATE:implemented_actions_implementation}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_effectiveness=1">
							<option value="implemented_actions_effectiveness" selected="1">{TRANSLATE:implemented_actions_effectiveness}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions_effectiveness">{TRANSLATE:implemented_actions_effectiveness}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions=1">
							<option value="implemented_actions" selected="1">{TRANSLATE:implemented_actions}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions">{TRANSLATE:implemented_actions}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="interco=1">
							<option value="interco" selected="1">{TRANSLATE:interco}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="interco">{TRANSLATE:interco}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="InternalComplaintStatus=1">
							<option value="InternalComplaintStatus" selected="1">{TRANSLATE:internal_complaint_status}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="InternalComplaintStatus">{TRANSLATE:internal_complaint_status}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="InternalSalesName=1">
							<option value="InternalSalesName" selected="1">{TRANSLATE:complaint_creator}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="InternalSalesName">{TRANSLATE:complaint_creator}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="is_complaint_cat_right=1">
							<option value="is_complaint_cat_right" selected="1">{TRANSLATE:is_complaint_cat_right}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="is_complaint_cat_right">{TRANSLATE:is_complaint_cat_right}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="is_po_right=1">
							<option value="is_po_right" selected="1">{TRANSLATE:is_po_right}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="is_po_right">{TRANSLATE:is_po_right}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="line_stoppage=1">
							<option value="line_stoppage" selected="1">{TRANSLATE:line_stoppage}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="line_stoppage">{TRANSLATE:line_stoppage}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="line_stoppage_details=1">
							<option value="line_stoppage_details" selected="1">{TRANSLATE:line_stoppage_details}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="line_stoppage_details">{TRANSLATE:line_stoppage_details}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="management_system_reviewed=1">
							<option value="management_system_reviewed" selected="1">{TRANSLATE:management_system_reviewed}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="management_system_reviewed">{TRANSLATE:management_system_reviewed}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="management_system_reviewed_date=1">
							<option value="management_system_reviewed_date" selected="1">{TRANSLATE:management_system_reviewed_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="management_system_reviewed_date">{TRANSLATE:management_system_reviewed_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="management_system_reviewed_ref=1">
							<option value="management_system_reviewed_ref" selected="1">{TRANSLATE:management_system_reviewed_ref}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="management_system_reviewed_ref">{TRANSLATE:management_system_reviewed_ref}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ManufacturingSite=1">
							<option value="ManufacturingSite" selected="1">{TRANSLATE:manufacturing_site}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ManufacturingSite">{TRANSLATE:manufacturing_site}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="modComplaintCategory=1">
							<option value="modComplaintCategory" selected="1">{TRANSLATE:mod_complaint_category}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="modComplaintCategory">{TRANSLATE:mod_complaint_category}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="modComplaintOption=1">
							<option value="modComplaintOption" selected="1">{TRANSLATE:mod_complaint_option}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="modComplaintOption">{TRANSLATE:mod_complaint_option}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="OriginSiteError=1">
							<option value="OriginSiteError" selected="1">{TRANSLATE:origin_site_error}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="OriginSiteError">{TRANSLATE:origin_site_error}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="Performance3d=1">
							<option value="Performance3d" selected="1">{TRANSLATE:performance3d}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="Performance3d">{TRANSLATE:Performance3d}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="Performance5d=1">
							<option value="Performance5d" selected="1">{TRANSLATE:performance5d}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="Performance5d">{TRANSLATE:performance5d}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="Performance8d=1">
							<option value="Performance8d" selected="1">{TRANSLATE:performance8d}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="Performance8d">{TRANSLATE:performance8d}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="possible_solutions=1">
							<option value="possible_solutions" selected="1">{TRANSLATE:possible_solutions}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="possible_solutions">{TRANSLATE:possible_solutions}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="possible_solutions_author=1">
							<option value="possible_solutions_author" selected="1">{TRANSLATE:possible_solutions_author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="possible_solutions_author">{TRANSLATE:possible_solutions_author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="possible_solutions_date=1">
							<option value="possible_solutions_date" selected="1">{TRANSLATE:possible_solutions_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="possible_solutions_date">{TRANSLATE:possible_solutions_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="problem_description=1">
							<option value="problem_description" selected="1">{TRANSLATE:problem_description}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="problem_description">{TRANSLATE:problem_description}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="product_supplier_name=1">
							<option value="product_supplier_name" selected="1">{TRANSLATE:product_supplier_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="product_supplier_name">{TRANSLATE:product_supplier_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="product_description=1">
							<option value="product_description" selected="1">{TRANSLATE:product_description}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="product_description">{TRANSLATE:product_description}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="quantity_under_complaint_quantity=1">
							<option value="quantity_under_complaint_quantity" selected="1">{TRANSLATE:quantity_under_complaint_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="quantity_under_complaint_quantity">{TRANSLATE:quantity_under_complaint_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="quantity_under_complaint_measurement=1">
							<option value="quantity_under_complaint_measurement" selected="1">{TRANSLATE:quantity_under_complaint_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="quantity_under_complaint_measurement">{TRANSLATE:quantity_under_complaint_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="requestAuthorisation=1">
							<option value="requestAuthorisation" selected="1">{TRANSLATE:request_authorisation}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="requestAuthorisation">{TRANSLATE:request_authorisation}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="requestAuthAdvice=1">
							<option value="requestAuthAdvice" selected="1">{TRANSLATE:request_auth_advice}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="requestAuthAdvice">{TRANSLATE:request_auth_advice}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="requestForCredit=1">
							<option value="requestForCredit" selected="1">{TRANSLATE:request_for_credit}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="requestForCredit">{TRANSLATE:request_for_credit}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="requestForCreditRaised=1">
							<option value="requestForCreditRaised" selected="1">{TRANSLATE:request_for_credit_raised}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="requestForCreditRaised">{TRANSLATE:request_for_credit_raised}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="returnFormDate=1">
							<option value="returnFormDate" selected="1">{TRANSLATE:return_form_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="returnFormDate">{TRANSLATE:return_form_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="return_goods=1">
							<option value="return_goods" selected="1">{TRANSLATE:return_goods}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="return_goods">{TRANSLATE:return_goods}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="returnQuantityReceived_quantity=1">
							<option value="returnQuantityReceived_quantity" selected="1">{TRANSLATE:return_quantity_received_quantity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="returnQuantityReceived_quantity">{TRANSLATE:return_quantity_received_quantity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="returnQuantityReceived_measurement=1">
							<option value="returnQuantityReceived_measurement" selected="1">{TRANSLATE:return_quantity_received_measurement}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="returnQuantityReceived_measurement">{TRANSLATE:return_quantity_received_measurement}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="root_causes=1">
							<option value="root_causes" selected="1">{TRANSLATE:root_causes}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="root_causes">{TRANSLATE:root_causes}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="root_causes_author=1">
							<option value="root_causes_author" selected="1">{TRANSLATE:root_causes_author}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="root_causes_author">{TRANSLATE:root_causes_author}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="root_cause_code=1">
							<option value="root_cause_code" selected="1">{TRANSLATE:root_cause_code}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="root_cause_code">{TRANSLATE:root_cause_code}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="root_causes_date=1">
							<option value="root_causes_date" selected="1">{TRANSLATE:root_causes_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="root_causes_date">{TRANSLATE:root_causes_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sales_containment_actions=1">
							<option value="sales_containment_actions" selected="1">{TRANSLATE:sales_containment_actions}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sales_containment_actions">{TRANSLATE:sales_containment_actions}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="SalesOffice=1">
							<option value="SalesOffice" selected="1">{TRANSLATE:sales_office}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="SalesOffice">{TRANSLATE:sales_office}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sample_date=1">
							<option value="sample_date" selected="1">{TRANSLATE:sample_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sample_date">{TRANSLATE:sample_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sample_received=1">
							<option value="sample_received" selected="1">{TRANSLATE:sample_received}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sample_received">{TRANSLATE:sample_received}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sample_reception_date=1">
							<option value="sample_reception_date" selected="1">{TRANSLATE:sample_reception_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sample_reception_date">{TRANSLATE:sample_reception_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sample_transferred=1">
							<option value="sample_transferred" selected="1">{TRANSLATE:sample_transferred}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sample_transferred">{TRANSLATE:sample_transferred}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="SAPCustomerName=1">
							<option value="SAPCustomerName" selected="1">{TRANSLATE:sap_customer_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="SAPCustomerName">{TRANSLATE:sap_customer_name}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
					<xsl:choose>
						<xsl:when test="SAPCustomerNumber=1">
							<option value="SAPCustomerNumber" selected="1">{TRANSLATE:sap_customer_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="SAPCustomerNumber">{TRANSLATE:sap_customer_number}</option>
						</xsl:otherwise>
					</xsl:choose>					
					
					<xsl:choose>
						<xsl:when test="sapItemNumber=1">
							<option value="sapItemNumber" selected="1">{TRANSLATE:sap_item_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sapItemNumber">{TRANSLATE:sap_item_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="MaterialGroup=1">
							<option value="MaterialGroup" selected="1">{TRANSLATE:sap_material_groups}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="MaterialGroup">{TRANSLATE:sap_material_groups}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="severity=1">
							<option value="severity" selected="1">{TRANSLATE:severity}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="severity">{TRANSLATE:severity}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="SpecificCategory=1">
							<option value="SpecificCategory" selected="1">{TRANSLATE:specific_category}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="SpecificCategory">{TRANSLATE:specific_category}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditNumber=1">
							<option value="creditNumber" selected="1">{TRANSLATE:credit_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="creditNumber">{TRANSLATE:credit_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="team_leader=1">
							<option value="team_leader" selected="1">{TRANSLATE:team_leader}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="team_leader">{TRANSLATE:team_leader}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="team_member=1">
							<option value="team_member" selected="1">{TRANSLATE:team_member}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="team_member">{TRANSLATE:team_member}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="total_closure_date=1">
							<option value="total_closure_date" selected="1">{TRANSLATE:total_closure_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="total_closure_date">{TRANSLATE:total_closure_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="receiver=1">
							<option value="receiver" selected="1">{TRANSLATE:receiver}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="receiver">{TRANSLATE:receiver}</option>
						</xsl:otherwise>
					</xsl:choose>	
					
										<!-- NA filters -->
					
					<xsl:choose>
						<xsl:when test="NAreturnDisposalRequestName=1">
							<option value="NAreturnDisposalRequestName" selected="1">{TRANSLATE:na_return_disposal_request_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnDisposalRequestName">{TRANSLATE:na_return_disposal_request_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnRequestName=1">
							<option value="NAreturnRequestName" selected="1">{TRANSLATE:na_return_request_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnRequestName">{TRANSLATE:na_return_request_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnRequestValue=1">
							<option value="NAreturnRequestValue" selected="1">{TRANSLATE:na_return_request_value}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnRequestValue">{TRANSLATE:na_return_request_value}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalRequestName=1">
							<option value="NAreturnApprovalRequestName" selected="1">{TRANSLATE:na_return_approval_request_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalRequestName">{TRANSLATE:na_return_approval_request_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalRequest=1">
							<option value="NAreturnApprovalRequest" selected="1">{TRANSLATE:na_return_approval_request}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalRequest">{TRANSLATE:na_return_approval_request}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalDisposalValue=1">
							<option value="NAreturnApprovalDisposalValue" selected="1">{TRANSLATE:na_return_approval_disposal_value}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalDisposalValue">{TRANSLATE:na_return_approval_disposal_value}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalDisposalRequestStatus=1">
							<option value="NAreturnApprovalDisposalRequestStatus" selected="1">{TRANSLATE:na_return_approval_disposal_request_status}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalDisposalRequestStatus">{TRANSLATE:na_return_approval_disposal_request_status}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalDisposalRequest=1">
							<option value="NAreturnApprovalDisposalRequest" selected="1">{TRANSLATE:na_return_approval_disposal_request}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalDisposalRequest">{TRANSLATE:na_return_approval_disposal_request}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAreturnApprovalDisposalName=1">
							<option value="NAreturnApprovalDisposalName" selected="1">{TRANSLATE:na_return_approval_disposal_name}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAreturnApprovalDisposalName">{TRANSLATE:na_return_approval_disposal_name}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NArequestForCredit=1">
							<option value="NArequestForCredit" selected="1">{TRANSLATE:na_request_for_credit}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NArequestForCredit">{TRANSLATE:na_request_for_credit}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAccCommercialCredit=1">
							<option value="NAccCommercialCredit" selected="1">{TRANSLATE:na_cc_commercial_credit}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAccCommercialCredit">{TRANSLATE:na_cc_commercial_credit}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAcreditAuthorisationStatus=1">
							<option value="NAcreditAuthorisationStatus" selected="1">{TRANSLATE:na_credit_authorisation_status}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAcreditAuthorisationStatus">{TRANSLATE:na_credit_authorisation_status}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAfinanceCreditAuthoriser=1">
							<option value="NAfinanceCreditAuthoriser" selected="1">{TRANSLATE:na_finance_credit_authoriser}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAfinanceCreditAuthoriser">{TRANSLATE:na_finance_credit_authoriser}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAfinanceCreditNewComplaintOwner=1">
							<option value="NAfinanceCreditNewComplaintOwner" selected="1">{TRANSLATE:na_finance_credit_new_complaint_owner}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAfinanceCreditNewComplaintOwner">{TRANSLATE:na_finance_credit_new_complaint_owner}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAfinanceLevelCreditAuthorised=1">
							<option value="NAfinanceLevelCreditAuthorised" selected="1">{TRANSLATE:na_finance_level_credit_authorised}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAfinanceLevelCreditAuthorised">{TRANSLATE:na_finance_level_credit_authorised}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="NAfinanceStageCompleted=1">
							<option value="NAfinanceStageCompleted" selected="1">{TRANSLATE:na_finance_stage_competed}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="NAfinanceStageCompleted">{TRANSLATE:na_finance_stage_competed}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="naLotNumber=1">
							<option value="naLotNumber" selected="1">{TRANSLATE:na_lot_number}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="naLotNumber">{TRANSLATE:na_lot_number}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="naSizeReturned=1">
							<option value="naSizeReturned" selected="1">{TRANSLATE:na_size_returned}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="naSizeReturned">{TRANSLATE:na_size_returned}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="naCondition=1">
							<option value="naCondition" selected="1">{TRANSLATE:na_condition}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="naCondition">{TRANSLATE:na_condition}</option>
						</xsl:otherwise>
					</xsl:choose>
					

									
					<!-- NA Filters to here! -->				

<!-- Stuff not needed but kept in case.					
				
					<xsl:choose>
						<xsl:when test="SAPItemNumber=1">
							<option value="SAPItemNumber" selected="1">{TRANSLATE:sap_item_numbers}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="SAPItemNumber">{TRANSLATE:sap_item_numbers}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ClosedDate=1">
							<option value="ClosedDate" selected="1">{TRANSLATE:closed_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ClosedDate">{TRANSLATE:closed_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_justified=1">
							<option value="complaint_justified" selected="1">{TRANSLATE:complaint_justified}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="complaint_justified">{TRANSLATE:complaint_justified}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_date=1">
							<option value="implemented_actions_date" selected="1">{TRANSLATE:implemented_actions_date}</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="implemented_actions_date">{TRANSLATE:implemented_actions_date}</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ProcessOwner=1">
							<option value="ProcessOwner" selected="1">Process owner</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="ProcessOwner">Process owner</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="OverallCustomerComplaintStatus=1">
							<option value="OverallCustomerComplaintStatus" selected="1">Overall Customer Complaint Status</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="OverallCustomerComplaintStatus">Overall Customer Complaint Status</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="OverallComplaintStatus=1">
							<option value="OverallComplaintStatus" selected="1">Overall Complaint Status</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="OverallComplaintStatus">Overall Complaint Status</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currency=1">
							<option value="currency" selected="1">currency</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="currency">currency</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="similar_recall=1">
							<option value="similar_recall" selected="1">similar recall</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="similar_recall">similar recall</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="stock_verif_made=1">
							<option value="stock_verif_made" selected="1">stock verif made</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="stock_verif_made">stock verif made</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructions=1">
							<option value="inspectionInstructions" selected="1">inspectionInstructions</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="inspectionInstructions">inspectionInstructions</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructionsRef=1">
							<option value="inspectionInstructionsRef" selected="1">inspectionInstructionsRef</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="inspectionInstructionsRef">inspectionInstructionsRef</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructionsDate=1">
							<option value="inspectionInstructionsDate" selected="1">inspectionInstructionsDate</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="inspectionInstructionsDate">inspectionInstructionsDate</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="submitDate=1">
							<option value="submitDate" selected="1">submitDate</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="submitDate">submitDate</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="replyDate=1">
							<option value="replyDate" selected="1">replyDate</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="replyDate">replyDate</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="recallProductFromOther=1">
							<option value="recallProductFromOther" selected="1">recallProductFromOther</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="recallProductFromOther">recallProductFromOther</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="authorisationRequestTo=1">
							<option value="authorisationRequestTo" selected="1">authorisationRequestTo</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="authorisationRequestTo">authorisationRequestTo</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="authorisationRequestDate=1">
							<option value="authorisationRequestDate" selected="1">authorisationRequestDate</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="authorisationRequestDate">authorisationRequestDate</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditAuthorised=1">
							<option value="creditAuthorised" selected="1">creditAuthorised</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="creditAuthorised">creditAuthorised</option>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sapReturnNumber=1">
							<option value="sapReturnNumber" selected="1">sap Return Number</option>
						</xsl:when>
						<xsl:otherwise>
							<option value="sapReturnNumber">sap Return Number</option>
						</xsl:otherwise>
					</xsl:choose>
					-->
					
				</xsl:element>
				</td>
				<td width="2%"></td>
				<td> 
				<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveSelectionRight();" /> 
				<br />
				<br />
				<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveSelectionLeft();" /> 
				</td>
				<td width="2%"></td>
				<td valign="top">
				<xsl:element name="select">
				<xsl:attribute name="name">columns[]</xsl:attribute>
				<xsl:attribute name="id">columns</xsl:attribute>
				<xsl:attribute name="multiple">true</xsl:attribute>
				<xsl:attribute name="size">10</xsl:attribute>

				<xsl:attribute name="class">dropdown required</xsl:attribute>

				
				
				<xsl:choose>
					<xsl:when test="implementedPermanentCorrectiveActionValidated=1">
						<option value="implementedPermanentCorrectiveActionValidated" >{TRANSLATE:implemented_permanent_corrective_action_validated}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implementedPermanentCorrectiveActionValidatedyn=1">
						<option value="implementedPermanentCorrectiveActionValidatedyn" >{TRANSLATE:was_implemented_permanent_corrective_action_validated}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implementedPermanentCorrectiveActionValidatedDate=1">
						<option value="implementedPermanentCorrectiveActionValidatedDate" >{TRANSLATE:the_implemented_permanent_corrective_action_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implementedPermanentCorrectiveActionValidatedAuthor=1">
						<option value="implementedPermanentCorrectiveActionValidatedAuthor" >{TRANSLATE:the_implemented_permanent_corrective_action_validated_author}</option>
					</xsl:when>
				</xsl:choose>
				
				
				
				
				<xsl:choose>
					<xsl:when test="action_requested=1">
						<option value="action_requested" >{TRANSLATE:action_requested}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="analysis=1">
						<option value="analysis" >{TRANSLATE:analysis}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="analysis_date=1">
						<option value="analysis_date" >{TRANSLATE:analysis_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="attributable_process=1">
						<option value="attributable_process" >{TRANSLATE:attributable_process}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="author=1">
						<option value="author" >{TRANSLATE:author}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="commercialReason=1">
						<option value="commercialReason" >{TRANSLATE:commercial_authorisation_reason}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="financeReason=1">
						<option value="financeReason" >{TRANSLATE:finance_authorisation_reason}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="awaiting_batch_number=1">
						<option value="awaiting_batch_number" >{TRANSLATE:awaiting_batch_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="awaiting_dimensions=1">
						<option value="awaiting_dimensions" >{TRANSLATE:awaiting_dimensions}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="awaiting_invoice=1">
						<option value="awaiting_invoice" >{TRANSLATE:awaiting_invoice}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="awaiting_quantity_under_complaint=1">
						<option value="awaiting_quantity_under_complaint" >{TRANSLATE:awaiting_quantity_under_complaint}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="batch_number=1">
						<option value="batch_number" >{TRANSLATE:batch_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="BusinessUnit=1">
						<option value="BusinessUnit" >{TRANSLATE:business_unit}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="carrier_name=1">
						<option value="carrier_name" >{TRANSLATE:carrier_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="Category=1">
						<option value="Category" >{TRANSLATE:category}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="Performancecco=1">
						<option value="Performancecco" >{TRANSLATE:close_out_performance}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="colour=1">
						<option value="colour" >{TRANSLATE:colour}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="comments=1">
						<option value="comments" >{TRANSLATE:comments}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="reason_for_rejection=1">
						<option value="reason_for_rejection" >{TRANSLATE:reason_for_rejection}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="closed_date=1">
						<option value="closed_date" >{TRANSLATE:closed_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="modComplaintReason=1">
						<option value="modComplaintReason" >{TRANSLATE:mod_complaint_reason}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="complaint_justified=1">
						<option value="complaint_justified" >{TRANSLATE:complaint_justified}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="complaint_location=1">
						<option value="complaint_location" >{TRANSLATE:complaint_location}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ComplaintOwner=1">
						<option value="ComplaintOwner" >{TRANSLATE:complaint_owner}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ComplaintType=1">
						<option value="ComplaintType" >{TRANSLATE:complaint_type}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ComplaintValue=1">
						<option value="ComplaintValue" >{TRANSLATE:complaint_value}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="complaint_value_quantity=1">
						<option value="complaint_value_quantity" >{TRANSLATE:complaint_value_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="complaint_value_measurement=1">
						<option value="complaint_value_measurement" >{TRANSLATE:complaint_value_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="containment_action=1">
						<option value="containment_action" >{TRANSLATE:containment_action}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="containment_action_author=1">
						<option value="containment_action_author" >{TRANSLATE:containment_action_author}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="containment_action_date=1">
						<option value="containment_action_date" >{TRANSLATE:containment_action_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ccCommercialCredit=1">
						<option value="ccCommercialCredit" >{TRANSLATE:cc_commercial_credit}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="correct_category=1">
						<option value="correct_category" >{TRANSLATE:correct_category}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="CreatedDate=1">
						<option value="CreatedDate" >{TRANSLATE:created_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="creditAdviceDate=1">
						<option value="creditAdviceDate" >{TRANSLATE:credit_advice_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="amount_quantity=1">
						<option value="amount_quantity" >{TRANSLATE:amount_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="amount_measurement=1">
						<option value="amount_measurement" >{TRANSLATE:amount_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="commercialLevelCreditAuthorised=1">
						<option value="commercialLevelCreditAuthorised" >{TRANSLATE:commercial_level_credit_authorised}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="commercialCreditAuthoriser=1">
						<option value="commercialCreditAuthoriser" >{TRANSLATE:commercial_credit_authoriser}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="financeLevelCreditAuthorised=1">
						<option value="financeLevelCreditAuthorised" >{TRANSLATE:finance_level_credit_authorised}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="financeCreditAuthoriser=1">
						<option value="financeCreditAuthoriser" >{TRANSLATE:finance_credit_authoriser}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="credit_note_requested=1">
						<option value="credit_note_requested" >{TRANSLATE:credit_note_requested}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="creditNoteValue_quantity=1">
						<option value="creditNoteValue_quantity" >{TRANSLATE:creditNoteValue_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="creditNoteValue_measurement=1">
						<option value="creditNoteValue_measurement" >{TRANSLATE:creditNoteValue_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ccCommercialCreditComment=1">
						<option value="ccCommercialCreditComment" >{TRANSLATE:cc_commercial_credit_comment}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="CustomerComplaintStatus=1">
						<option value="CustomerComplaintStatus" >{TRANSLATE:customer_complaint_status}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="customerCreditNumber=1">
						<option value="customerCreditNumber" >{TRANSLATE:customer_credit_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="customer_item_number=1">
						<option value="customer_item_number" >{TRANSLATE:customer_item_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="customerSpecification=1">
						<option value="customerSpecification" >{TRANSLATE:customer_specification}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="customerSpecificationDate=1">
						<option value="customerSpecificationDate" >{TRANSLATE:customer_specification_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="customerSpecificationRef=1">
						<option value="customerSpecificationRef" >{TRANSLATE:customer_specification_ref}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dateCreditNoteRaised=1">
						<option value="dateCreditNoteRaised" >{TRANSLATE:date_credit_note_raised}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dateReturnsReceived=1">
						<option value="dateReturnsReceived" >{TRANSLATE:date_returns_received}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="date_sample_received=1">
						<option value="date_sample_received" >{TRANSLATE:date_sample_received}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="defectiveMaterialAmount_quantity=1">
						<option value="defectiveMaterialAmount_quantity" >{TRANSLATE:defective_material_amount_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="defectiveMaterialAmount_measurement=1">
						<option value="defectiveMaterialAmount_measurement" >{TRANSLATE:defective_material_amount_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="DespatchSite=1">
						<option value="DespatchSite" >{TRANSLATE:despatch_site}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_length_quantity=1">
						<option value="dimension_length_quantity" >{TRANSLATE:dimension_length_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_length_measurement=1">
						<option value="dimension_length_measurement" >{TRANSLATE:dimension_length_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_thickness_quantity=1">
						<option value="dimension_thickness_quantity" >{TRANSLATE:dimension_thickness_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_thickness_measurement=1">
						<option value="dimension_thickness_measurement" >{TRANSLATE:dimension_thickness_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_width_quantity=1">
						<option value="dimension_width_quantity" >{TRANSLATE:dimension_width_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dimension_width_measurement=1">
						<option value="dimension_width_measurement" >{TRANSLATE:dimension_width_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="dispose_goods=1">
						<option value="dispose_goods" >{TRANSLATE:dispose_goods}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ExternalSalesName=1">
						<option value="ExternalSalesName" >{TRANSLATE:external_sales_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="factored_product=1">
						<option value="factored_product" >{TRANSLATE:factored_product}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="failure_code=1">
						<option value="failure_code" >{TRANSLATE:failure_code}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="finalComments=1">
						<option value="finalComments" >{TRANSLATE:final_comments}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="financeCreditNewComplaintOwner=1">
						<option value="financeCreditNewComplaintOwner" >{TRANSLATE:final_credit_result_sent_to}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="fmea=1">
						<option value="fmea" >{TRANSLATE:fmea}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="fmeaDate=1">
						<option value="fmeaDate" >{TRANSLATE:fmea_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="fmeaRef=1">
						<option value="fmeaRef" >{TRANSLATE:fmea_ref}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="g8d=1">
						<option value="g8d" >{TRANSLATE:g8d}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="is_sample_received=1">
						<option value="is_sample_received" >{TRANSLATE:is_sample_received}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implemented_actions_author=1">
						<option value="implemented_actions_author" >{TRANSLATE:implemented_actions_author}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ImplementedActionsDate=1">
						<option value="ImplementedActionsDate" >{TRANSLATE:implemented_actions_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implemented_actions_estimated=1">
						<option value="implemented_actions_estimated" >{TRANSLATE:implemented_actions_estimated}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implemented_actions_implementation=1">
						<option value="implemented_actions_implementation" >{TRANSLATE:implemented_actions_implementation}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implemented_actions_effectiveness=1">
						<option value="implemented_actions_effectiveness" >{TRANSLATE:implemented_actions_effectiveness}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="implemented_actions=1">
						<option value="implemented_actions" >{TRANSLATE:implemented_actions}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="interco=1">
						<option value="interco" >{TRANSLATE:interco}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="InternalComplaintStatus=1">
						<option value="InternalComplaintStatus" >{TRANSLATE:internal_complaint_status}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="InternalSalesName=1">
						<option value="InternalSalesName" >{TRANSLATE:complaint_creator}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="is_complaint_cat_right=1">
						<option value="is_complaint_cat_right" >{TRANSLATE:is_complaint_cat_right}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="is_po_right=1">
						<option value="is_po_right" >{TRANSLATE:is_po_right}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="line_stoppage=1">
						<option value="line_stoppage" >{TRANSLATE:line_stoppage}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="line_stoppage_details=1">
						<option value="line_stoppage_details" >{TRANSLATE:line_stoppage_details}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="management_system_reviewed=1">
						<option value="management_system_reviewed" >{TRANSLATE:management_system_reviewed}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="management_system_reviewed_date=1">
						<option value="management_system_reviewed_date" >{TRANSLATE:management_system_reviewed_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="management_system_reviewed_ref=1">
						<option value="management_system_reviewed_ref" >{TRANSLATE:management_system_reviewed_ref}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="ManufacturingSite=1">
						<option value="ManufacturingSite" >{TRANSLATE:manufacturing_site}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="modComplaintCategory=1">
						<option value="modComplaintCategory" >{TRANSLATE:mod_complaint_category}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="modComplaintOption=1">
						<option value="modComplaintOption" >{TRANSLATE:mod_complaint_option}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="OriginSiteError=1">
						<option value="OriginSiteError" >{TRANSLATE:origin_site_error}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="Performance3d=1">
						<option value="Performance3d" >{TRANSLATE:performance3d}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="Performance5d=1">
						<option value="Performance5d" >{TRANSLATE:performance5d}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="Performance8d=1">
						<option value="Performance8d" >{TRANSLATE:performance8d}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="possible_solutions=1">
						<option value="possible_solutions" >{TRANSLATE:possible_solutions}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="possible_solutions_author=1">
						<option value="possible_solutions_author" >{TRANSLATE:possible_solutions_author}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="possible_solutions_date=1">
						<option value="possible_solutions_date" >{TRANSLATE:possible_solutions_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="problem_description=1">
						<option value="problem_description" >{TRANSLATE:problem_description}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="product_supplier_name=1">
						<option value="product_supplier_name" >{TRANSLATE:product_supplier_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="product_description=1">
						<option value="product_description" >{TRANSLATE:product_description}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="quantity_under_complaint_quantity=1">
						<option value="quantity_under_complaint_quantity" >{TRANSLATE:quantity_under_complaint_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="quantity_under_complaint_measurement=1">
						<option value="quantity_under_complaint_measurement" >{TRANSLATE:quantity_under_complaint_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="requestAuthorisation=1">
						<option value="requestAuthorisation" >{TRANSLATE:request_authorisation}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="requestAuthAdvice=1">
						<option value="requestAuthAdvice" >{TRANSLATE:request_auth_advice}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="requestForCredit=1">
						<option value="requestForCredit" >{TRANSLATE:request_for_credit}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="requestForCreditRaised=1">
						<option value="requestForCreditRaised" >{TRANSLATE:request_for_credit_raised}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="returnFormDate=1">
						<option value="returnFormDate" >{TRANSLATE:return_form_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="return_goods=1">
						<option value="return_goods" >{TRANSLATE:return_goods}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="returnQuantityReceived_quantity=1">
						<option value="returnQuantityReceived_quantity" >{TRANSLATE:return_quantity_received_quantity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="returnQuantityReceived_measurement=1">
						<option value="returnQuantityReceived_measurement" >{TRANSLATE:return_quantity_received_measurement}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="root_causes=1">
						<option value="root_causes" >{TRANSLATE:root_causes}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="root_causes_author=1">
						<option value="root_causes_author" >{TRANSLATE:root_causes_author}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="root_cause_code=1">
						<option value="root_cause_code" >{TRANSLATE:root_cause_code}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="root_causes_date=1">
						<option value="root_causes_date" >{TRANSLATE:root_causes_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="sales_containment_actions=1">
						<option value="sales_containment_actions" >{TRANSLATE:sales_containment_actions}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="SalesOffice=1">
						<option value="SalesOffice" >{TRANSLATE:sales_office}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="sample_date=1">
						<option value="sample_date" >{TRANSLATE:sample_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="sample_received=1">
						<option value="sample_received" >{TRANSLATE:sample_received}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="sample_reception_date=1">
						<option value="sample_reception_date" >{TRANSLATE:sample_reception_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="sample_transferred=1">
						<option value="sample_transferred" >{TRANSLATE:sample_transferred}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="SAPCustomerName=1">
						<option value="SAPCustomerName" >{TRANSLATE:sap_customer_name}</option>
					</xsl:when>
				</xsl:choose>	
				
				<xsl:choose>
					<xsl:when test="SAPCustomerNumber=1">
						<option value="SAPCustomerNumber" >{TRANSLATE:sap_customer_number}</option>
					</xsl:when>
				</xsl:choose>					
				
				<xsl:choose>
					<xsl:when test="sapItemNumber=1">
						<option value="sapItemNumber" >{TRANSLATE:sap_item_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="MaterialGroup=1">
						<option value="MaterialGroup" >{TRANSLATE:sap_material_groups}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="severity=1">
						<option value="severity" >{TRANSLATE:severity}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="SpecificCategory=1">
						<option value="SpecificCategory" >{TRANSLATE:specific_category}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="creditNumber=1">
						<option value="creditNumber" >{TRANSLATE:credit_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="team_leader=1">
						<option value="team_leader" >{TRANSLATE:team_leader}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="team_member=1">
						<option value="team_member" >{TRANSLATE:team_member}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="total_closure_date=1">
						<option value="total_closure_date" >{TRANSLATE:total_closure_date}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="receiver=1">
						<option value="receiver" >{TRANSLATE:receiver}</option>
					</xsl:when>
				</xsl:choose>					
									
				<!-- NA filters -->
				
				<xsl:choose>
					<xsl:when test="naLotNumber=1">
						<option value="naLotNumber" >{TRANSLATE:na_lot_number}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="naSizeReturned=1">
						<option value="naSizeReturned" >{TRANSLATE:na_size_returned}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="naCondition=1">
						<option value="naCondition" >{TRANSLATE:na_condition}</option>
					</xsl:when>
				</xsl:choose>
				
				
				<xsl:choose>
					<xsl:when test="NAreturnDisposalRequestName=1">
						<option value="NAreturnDisposalRequestName" >{TRANSLATE:na_return_disposal_request_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnRequestName=1">
						<option value="NAreturnRequestName" >{TRANSLATE:na_return_request_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnRequestValue=1">
						<option value="NAreturnRequestValue" >{TRANSLATE:na_return_request_value}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalRequestName=1">
						<option value="NAreturnApprovalRequestName">{TRANSLATE:na_return_approval_request_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalDisposalValue=1">
						<option value="NAreturnApprovalDisposalValue">{TRANSLATE:na_return_approval_request}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalRequest=1">
						<option value="NAreturnApprovalRequest">{TRANSLATE:na_return_approval_disposal_value}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalDisposalRequestStatus=1">
						<option value="NAreturnApprovalDisposalRequestStatus">{TRANSLATE:na_return_approval_disposal_request_status}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalDisposalRequest=1">
						<option value="NAreturnApprovalDisposalRequest">{TRANSLATE:na_return_approval_disposal_request}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAreturnApprovalDisposalName=1">
						<option value="NAreturnApprovalDisposalName">{TRANSLATE:na_return_approval_disposal_name}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NArequestForCredit=1">
						<option value="NArequestForCredit">{TRANSLATE:na_request_for_credit}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAccCommercialCredit=1">
						<option value="NAccCommercialCredit">{TRANSLATE:na_cc_commercial_credit}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAcreditAuthorisationStatus=1">
						<option value="NAcreditAuthorisationStatus">{TRANSLATE:na_credit_authorisation_status}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAfinanceCreditAuthoriser=1">
						<option value="NAfinanceCreditAuthoriser">{TRANSLATE:na_finance_credit_authoriser}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAfinanceCreditNewComplaintOwner=1">
						<option value="NAfinanceCreditNewComplaintOwner">{TRANSLATE:na_finance_credit_new_complaint_owner}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAfinanceLevelCreditAuthorised=1">
						<option value="NAfinanceLevelCreditAuthorised">{TRANSLATE:na_finance_level_credit_authorised}</option>
					</xsl:when>
				</xsl:choose>
				
				<xsl:choose>
					<xsl:when test="NAfinanceStageCompleted=1">
						<option value="NAfinanceStageCompleted">{TRANSLATE:na_finance_stage_competed}</option>
					</xsl:when>
				</xsl:choose>
				
				
				
				<!-- NA Filters to here! -->
					
					
					
<!-- Commented out cos not used! -->
										
<!--			<xsl:choose>
						<xsl:when test="SAPItemNumber=1">
							<option value="SAPItemNumber" >{TRANSLATE:sap_item_numbers}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ClosedDate=1">
							<option value="ClosedDate" >{TRANSLATE:closed_date}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="complaint_justified=1">
							<option value="complaint_justified" >{TRANSLATE:complaint_justified}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="implemented_actions_date=1">
							<option value="implemented_actions_date" >{TRANSLATE:implemented_actions_date}</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="ProcessOwner=1">
							<option value="ProcessOwner" >Process owner</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="OverallCustomerComplaintStatus=1">
							<option value="OverallCustomerComplaintStatus" >Overall Customer Complaint Status</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="OverallComplaintStatus=1">
							<option value="OverallComplaintStatus" >Overall Complaint Status</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="currency=1">
							<option value="currency" >currency</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="similar_recall=1">
							<option value="similar_recall" >similar recall</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="stock_verif_made=1">
							<option value="stock_verif_made" >stock verif made</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructions=1">
							<option value="inspectionInstructions" >inspectionInstructions</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructionsRef=1">
							<option value="inspectionInstructionsRef" >inspectionInstructionsRef</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="inspectionInstructionsDate=1">
							<option value="inspectionInstructionsDate" >inspectionInstructionsDate</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="submitDate=1">
							<option value="submitDate" >submitDate</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="replyDate=1">
							<option value="replyDate" >replyDate</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="recallProductFromOther=1">
							<option value="recallProductFromOther" >recallProductFromOther</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="authorisationRequestTo=1">
							<option value="authorisationRequestTo" >authorisationRequestTo</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="authorisationRequestDate=1">
							<option value="authorisationRequestDate" >authorisationRequestDate</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="creditAuthorised=1">
							<option value="creditAuthorised" >creditAuthorised</option>
						</xsl:when>
					</xsl:choose>
					
					<xsl:choose>
						<xsl:when test="sapReturnNumber=1">
							<option value="sapReturnNumber" >sap Return Number</option>
						</xsl:when>
					</xsl:choose>

-->
					
				</xsl:element>
				</td>
			</tr>
		</table>

		</td>
		</tr>
		</table>

	</xsl:template>		
	
	
	<!-- below is the style sheet for the supplier complaints custom fields -->
	
	<xsl:template match="supplierColumnFilters">
		<script language="JavaScript">
			function moveSupplierSelectionRight()
			{
				var i = 0;
				var selColumns = document.getElementById('columnsSupplier');
				while(i != document.form.columnsorig.length)
				{
					if(document.form.columnsorig.options[i].selected)
					{
						//we now need to check if it is already in the list - if not add
						var foundMatch = false;
						var j = 0;
						while(j != selColumns.options.length)
						{
							if(selColumns.options[j].value == document.form.columnsorig.options[i].value)
							foundMatch = true;
							j++;
						}
						if(!foundMatch)
						selColumns.options[selColumns.options.length] = new Option(document.form.columnsorig.options[i].value,document.form.columnsorig.options[i].value);
					}
					i++;
				}
				selectAllColumns()
			}
			
			function moveSupplierSelectionLeft()
			{	
				var i = 0;
				var toDelete = new Array();
				var selColumns = document.getElementById('columnsSupplier');
				var loopLength = selColumns.options.length;
				i = (loopLength-1);
				while(i != -1)
				{
					if(selColumns.options[i].selected)
					{
						selColumns.options[i] = null;
					}
					i--;
				}
				selectAllColumns()
			}
		</script>
		
		<h1 style="margin-bottom: 10px;">
			Column Filters
		</h1>
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;">
			<tr id="filtersRow" class="valid_row">
				
				<td class="cell_name" width="15%" valign="top">
				</td>
				
				<td>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<xsl:element name="select">
									<xsl:attribute name="name">columnsorig</xsl:attribute>
									
									<xsl:attribute name="multiple">true</xsl:attribute>
									<xsl:attribute name="size">10</xsl:attribute>
									
									<xsl:choose>
										<xsl:when test="required = 'true'">
											<xsl:attribute name="class">dropdown required</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<xsl:attribute name="class">dropdown optional</xsl:attribute>
										</xsl:otherwise>
									</xsl:choose>
									
												
											   <!-- A -->
		                              <!-- Actions requested from the customer -->
		                              <xsl:choose>
		                                  <xsl:when test="action_requested=1">
		                                      <option value="action_requested" selected="1">{TRANSLATE:actions_by_scapa_to_minimise_problem}</option>
		                                  </xsl:when>
		                                  <xsl:otherwise>
		                                      <option value="action_requested">{TRANSLATE:actions_by_scapa_to_minimise_problem}</option>
		                                  </xsl:otherwise>
		                              </xsl:choose>
                                    
                                    <!-- additional comments -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComments=1">
                                            <option value="additionalComments" selected="1">{TRANSLATE:additional_comments_no_warning}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="additionalComments">{TRANSLATE:additional_comments_no_warning}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Additional complaint cost -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComplaintCost=1">
                                            <option value="additionalComplaintCost" selected="1">{TRANSLATE:additional_complaint_cost}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="additionalComplaintCost">{TRANSLATE:additional_complaint_cost}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Amount - conclusion -->
                                    <xsl:choose>
                                        <xsl:when test="sp_amount=1">
                                            <option value="sp_amount" selected="1">{TRANSLATE:amount}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_amount">{TRANSLATE:amount}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Analysis -->
                                    <xsl:choose>
                                        <xsl:when test="analysis=1">
                                            <option value="analysis" selected="1">{TRANSLATE:analysis}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="analysis">{TRANSLATE:analysis}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Analysis Date -->
                                    <xsl:choose>
                                        <xsl:when test="analysis_date=1">
                                            <option value="analysis_date" selected="1">{TRANSLATE:analysis_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="analysis_date">{TRANSLATE:analysis_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- approval for usage -->
                                    <xsl:choose>
		                                  <xsl:when test="customerDerongation=1">
		                                      <option value="customerDerongation" selected="1">{TRANSLATE:customer_derongation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerDerongation">{TRANSLATE:customer_derongation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- attributable process -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="attributable_process=1">
                                            <option value="attributable_process" selected="1">{TRANSLATE:attributable_process}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="attributable_process">{TRANSLATE:attributable_process}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- author -->
                                    <xsl:choose>
                                        <xsl:when test="author=1">
                                            <option value="author" selected="1">{TRANSLATE:author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="author">{TRANSLATE:author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- B -->
                                    <!-- batch number -->
                                    <xsl:choose>
                                        <xsl:when test="batch_number=1">
                                            <option value="batch_number" selected="1">{TRANSLATE:batch_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="batch_number">{TRANSLATE:batch_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- buyer -->
                                    <xsl:choose>
                                        <xsl:when test="buyer=1">
                                            <option value="buyer" selected="1">{TRANSLATE:buyer}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="buyer">{TRANSLATE:buyer}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- C -->	
                                    <!-- carrier name -->							
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="carrier_name=1">
                                            <option value="carrier_name" selected="1">{TRANSLATE:carrier_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="carrier_name">{TRANSLATE:carrier_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- category -->
                                    <xsl:choose>
                                        <xsl:when test="Category=1">
                                            <option value="Category" selected="1">{TRANSLATE:category}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="Category">{TRANSLATE:category}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- chosen complaint owner -->
												<xsl:choose>
		                                  <xsl:when test="processOwnerRequest=1">
		                                      <option value="processOwnerRequest" selected="1">{TRANSLATE:chosen_complaint_owner}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="processOwnerRequest">{TRANSLATE:chosen_complaint_owner}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- chosen complaint in conclusion -->
												<xsl:choose>
		                                  <xsl:when test="processOwner3=1">
		                                      <option value="processOwner3" selected="1">{TRANSLATE:chosen_complaint_owner_conclusion}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="processOwner3">{TRANSLATE:chosen_complaint_owner_conclusion}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- comment -->
												<xsl:choose>
		                                  <xsl:when test="sp_comment=1">
		                                      <option value="sp_comment" selected="1">{TRANSLATE:comment}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_comment">{TRANSLATE:comment}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- complaint justified -->
                                    <xsl:choose>
                                        <xsl:when test="complaint_justified=1">
                                            <option value="complaint_justified" selected="1">{TRANSLATE:complaint_justified}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="complaint_justified">{TRANSLATE:complaint_justified}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint location -->
                                    <xsl:choose>
                                        <xsl:when test="ComplaintLocation=1">
                                            <option value="ComplaintLocation" selected="1">{TRANSLATE:complaint_location}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="ComplaintLocation">{TRANSLATE:complaint_location}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint owner -->
                                    <xsl:choose>
                                        <xsl:when test="ComplaintOwner=1">
                                            <option value="ComplaintOwner" selected="1">{TRANSLATE:complaint_owner}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="ComplaintOwner">{TRANSLATE:complaint_owner}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint type -->
<!--                                    <xsl:choose>
                                        <xsl:when test="ComplaintType=1">
                                            <option value="ComplaintType" selected="1">{TRANSLATE:complaint_type}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="ComplaintType">{TRANSLATE:complaint_type}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
-->
                                    <!-- complaint value gbp -->
                                    <xsl:choose>
                                        <xsl:when test="gbpComplaintValue=1">
                                            <option value="gbpComplaintValue" selected="1">{TRANSLATE:complaint_value_gbp}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="gbpComplaintValue">{TRANSLATE:complaint_value_gbp}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint value -->
                                    <xsl:choose>
                                        <xsl:when test="complaint_value_quantity=1">
                                            <option value="complaint_value_quantity" selected="1">{TRANSLATE:complaint_value_quantity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="complaint_value_quantity">{TRANSLATE:complaint_value_quantity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- comments -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="comments=1">
                                            <option value="comments" selected="1">{TRANSLATE:comments}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="comments">{TRANSLATE:comments}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- comments/reason for rejection -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="reason_for_rejection=1">
                                            <option value="reason_for_rejection" selected="1">{TRANSLATE:reason_for_rejection}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="reason_for_rejection">{TRANSLATE:reason_for_rejection}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- containment action -->
                                    <xsl:choose>
                                        <xsl:when test="containment_action=1">
                                            <option value="containment_action" selected="1">{TRANSLATE:containment_action}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containment_action">{TRANSLATE:containment_action}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action author -->
                                    <!--
                                   <xsl:choose>
                                        <xsl:when test="containment_action_author=1">
                                            <option value="containment_action_author" selected="1">{TRANSLATE:containment_action_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containment_action_author">{TRANSLATE:containment_action_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- containment action date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="containment_action_date=1">
                                            <option value="containment_action_date" selected="1">{TRANSLATE:containment_action_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containment_action_date">{TRANSLATE:containment_action_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                   	-->
                                   
                                    <!-- copy credit request to -->
                                    <xsl:choose>
                                        <xsl:when test="ccComercialCredit=1">
                                            <option value="ccComercialCredit" selected="1">{TRANSLATE:cc_commercial_credit}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="ccComercialCredit">{TRANSLATE:cc_commercial_credit}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>	
                                    
                                    <!-- correct category -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="correct_category=1">
                                            <option value="correct_category" selected="1">{TRANSLATE:correct_category}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="correct_category">{TRANSLATE:correct_category}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- created date -->
                                    <xsl:choose>
                                        <xsl:when test="CreatedDate=1">
                                            <option value="CreatedDate" selected="1">{TRANSLATE:created_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="CreatedDate">{TRANSLATE:created_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer complaint date -->
                                    <xsl:choose>
                                        <xsl:when test="customerComplaintDate=1">
                                            <option value="customerComplaintDate" selected="1">{TRANSLATE:customer_complaint_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerComplaintDate">{TRANSLATE:customer_complaint_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!--customer name -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_customerName=1">
		                                      <option value="sp_customerName" selected="1">{TRANSLATE:customer_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_customerName">{TRANSLATE:customer_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                   
                                    <!-- customer specification -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecification=1">
                                            <option value="customerSpecification" selected="1">{TRANSLATE:customer_specification}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecification">{TRANSLATE:customer_specification}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer specification date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationDate=1">
                                            <option value="customerSpecificationDate" selected="1">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecificationDate">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- customer specification ref -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationRef=1">
                                            <option value="customerSpecificationRef" selected="1">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecificationRef">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    
                                    <!-- D -->
                                    <!-- date of analysis -->
                                    <xsl:choose>
                                        <xsl:when test="dateOfAnalysis=1">
                                            <option value="dateOfAnalysis" selected="1">{TRANSLATE:date_of_analysis}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dateOfAnalysis">{TRANSLATE:date_of_analysis}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- date sample received by po -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="date_sample_received=1">
                                            <option value="date_sample_received" selected="1">{TRANSLATE:date_sample_received}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="date_sample_received">{TRANSLATE:date_sample_received}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- date sample sent -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSentDate=1">
                                            <option value="sampleSentDate" selected="1">{TRANSLATE:sample_sent_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sampleSentDate">{TRANSLATE:sample_sent_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- delivery note -->
                                    <xsl:choose>
                                        <xsl:when test="deliveryNote=1">
                                            <option value="deliveryNote" selected="1">{TRANSLATE:delivery_note}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="deliveryNote">{TRANSLATE:delivery_note}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- details of complaint cost -->
                                    <xsl:choose>
                                        <xsl:when test="detailsOfComplaintCost=1">
                                            <option value="detailsOfComplaintCost" selected="1">{TRANSLATE:details_of_complaint_cost}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="detailsOfComplaintCost">{TRANSLATE:details_of_complaint_cost}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                   <!-- dispose the goods -->
                                    <xsl:choose>
                                        <xsl:when test="dispose_goods=1">
                                            <option value="dispose_goods" selected="1">{TRANSLATE:dispose_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dispose_goods">{TRANSLATE:dispose_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Does 8D Action Exist -->
                                    <xsl:choose>
                                        <xsl:when test="does8DActionExist=1">
                                            <option value="does8DActionExist" selected="1">{TRANSLATE:does_8d_action_exist}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="does8DActionExist">{TRANSLATE:does_8d_action_exist}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Does Containment Action Exist -->
		                              <xsl:choose>
		                                  <xsl:when test="doesContainmentActionExist=1">
		                                      <option value="doesContainmentActionExist" selected="1">{TRANSLATE:does_containment_action_exist}</option>
		                                  </xsl:when>
		                                  <xsl:otherwise>
		                                      <option value="doesContainmentActionExist">{TRANSLATE:does_containment_action_exist}</option>
		                                  </xsl:otherwise>
		                              </xsl:choose>
                                    
                                    
                                    <!-- E -->
                                    <!-- Email comment  -->
												<xsl:choose>
		                                  <xsl:when test="sp_requestEmailText=1">
		                                      <option value="sp_requestEmailText" selected="1">{TRANSLATE:email_text_comment}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_requestEmailText">{TRANSLATE:email_text_comment}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- email text -->
												<!--
												<xsl:choose>
		                                  <xsl:when test="sp_requestAuthorisedEmailText=1">
		                                      <option value="sp_requestAuthorisedEmailText" selected="1">{TRANSLATE:email_text}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_requestAuthorisedEmailText">{TRANSLATE:email_text}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->

                                    
                                    <!-- F -->
                                    <!-- failure code -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="failure_code=1">
                                            <option value="failure_code" selected="1">{TRANSLATE:failure_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="failure_code">{TRANSLATE:failure_code}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- final comment -->
												<xsl:choose>
		                                  <xsl:when test="sp_finalComments=1">
		                                      <option value="sp_finalComment" selected="1">{TRANSLATE:final_comment}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_finalComments">{TRANSLATE:final_comment}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
 		
                                    <!-- finance stage complete -->
                                    <xsl:choose>
                                        <xsl:when test="financeStageComplete=1">
                                            <option value="financeStageComplete" selected="1">{TRANSLATE:finance_stage_completed}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="financeStageComplete">{TRANSLATE:finance_stage_completed}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Flow chart -->
                                    <xsl:choose>
                                        <xsl:when test="flowChart=1">
                                            <option value="flowChart" selected="1">{TRANSLATE:flow_chart}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="flowChart">{TRANSLATE:flow_chart}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- fmea -->
                                    <xsl:choose>
                                        <xsl:when test="fmea=1">
                                            <option value="fmea" selected="1">{TRANSLATE:fmea}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmea">{TRANSLATE:fmea}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- fmea date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="fmeaDate=1">
                                            <option value="fmeaDate" selected="1">{TRANSLATE:fmea_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmeaDate">{TRANSLATE:fmea_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- fmea ref -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="fmeaRef=1">
                                            <option value="fmeaRef" selected="1">{TRANSLATE:fmea_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmeaRef">{TRANSLATE:fmea_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    
                                    <!-- G -->
                                    <xsl:choose>
                                        <xsl:when test="g8d=1">
                                            <option value="g8d" selected="1">{TRANSLATE:g8d}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="g8d">{TRANSLATE:g8d}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="goodsReceivedDate=1">
                                            <option value="goodsReceivedDate" selected="1">{TRANSLATE:goods_received_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="goodsReceivedDate">{TRANSLATE:goods_received_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>					
                                    
                                    <xsl:choose>
                                        <xsl:when test="goodsReceivedNumber=1">
                                            <option value="goodsReceivedNumber" selected="1">{TRANSLATE:goods_received_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="goodsReceivedNumber">{TRANSLATE:goods_received_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- H -->
                                    <!-- has a sample been received -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_sample_received=1">
                                            <option value="is_sample_received" selected="1">{TRANSLATE:is_sample_received}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="is_sample_received">{TRANSLATE:is_sample_received}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- how was error detected? -->
                                    <xsl:choose>
                                        <xsl:when test="how_was_error_detected=1">
                                            <option value="how_was_error_detected" selected="1">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="how_was_error_detected">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    
                                    <!-- I -->
                                    <!-- implemented actions -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions=1">
                                            <option value="implemented_actions" selected="1">>{TRANSLATE:implemented_actions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions">{TRANSLATE:implemented_actions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- implemented actions author -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_author=1">
                                            <option value="implemented_actions_author" selected="1">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions_author">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- implmented actions date -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_date=1">
                                            <option value="implemented_actions_date" selected="1">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions_date">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- implmented actions estimate date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_estimated=1">
                                            <option value="implemented_actions_estimated" selected="1">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions_estimated">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- implmented actions implmentation date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_implementation=1">
                                            <option value="implemented_actions_implementation" selected="1">{TRANSLATE:implemented_actions_implementation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions_implementation">{TRANSLATE:implemented_actions_implementation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- implmented actions validation of effectiveness date -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_effectiveness=1">
                                            <option value="implemented_actions_effectiveness" selected="1">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implemented_actions_effectiveness">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- inspection instructions -->
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructions=1">
                                            <option value="inspectionInstructions" selected="1">{TRANSLATE:inspectionInstructions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="inspectionInstructions">{TRANSLATE:inspectionInstructions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- inspection instructions date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructionsDate=1">
                                            <option value="inspectionInstructionsDate" selected="1">{TRANSLATE:inspectionInstructionsDate}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="inspectionInstructionsDate">{TRANSLATE:inspectionInstructionsDate}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- inspection instructions reference -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructionsRef=1">
                                            <option value="inspectionInstructionsRef" selected="1">{TRANSLATE:inspectionInstructionsRef}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="inspectionInstructionsRef">{TRANSLATE:inspectionInstructionsRef}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- Internal Complaint Status -->
												<xsl:choose>
		                                  <xsl:when test="internalComplaintStatus=1">
		                                      <option value="internalComplaintStatus" selected="1">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internalComplaintStatus">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- invoice delivery note -->
                                    <xsl:choose>
                                        <xsl:when test="invoiceDeliveryNote=1">
                                            <option value="invoiceDeliveryNote" selected="1">{TRANSLATE:invoice_delivery_note}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="invoiceDeliveryNote">{TRANSLATE:invoice_delivery_note}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Invoice number -->
                                    <xsl:choose>
                                        <xsl:when test="goodJobInvoiceNo=1">
                                            <option value="goodJobInvoiceNo" selected="1">{TRANSLATE:invoice_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="goodJobInvoiceNo">{TRANSLATE:invoice_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- is complaint category right -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_complaint_cat_right=1">
                                            <option value="is_complaint_cat_right" selected="1">{TRANSLATE:is_complaint_cat_right}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="is_complaint_cat_right">{TRANSLATE:is_complaint_cat_right}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- is process owner correct -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_po_right=1">
                                            <option value="is_po_right" selected="1">{TRANSLATE:is_po_right}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="is_po_right">{TRANSLATE:is_po_right}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <xsl:choose>
		                                  <xsl:when test="isWithSupplier=1">
		                                      <option value="isWithSupplier" selected="1">{TRANSLATE:is_with_supplier}</option>
		                                  </xsl:when>
		                                  <xsl:otherwise>
		                                      <option value="isWithSupplier">{TRANSLATE:is_with_supplier}</option>
		                                  </xsl:otherwise>
		                              </xsl:choose>
                                    
                                    
                                    <!-- M -->
                                    <!-- management system reviewed -->
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed=1">
                                            <option value="management_system_reviewed" selected="1">{TRANSLATE:management_system_reviewed}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="management_system_reviewed">{TRANSLATE:management_system_reviewed}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- management system reviewed date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed_date=1">
                                            <option value="management_system_reviewed_date" selected="1">{TRANSLATE:management_system_reviewed_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="management_system_reviewed_date">{TRANSLATE:management_system_reviewed_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- management system reviewed reference -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed_ref=1">
                                            <option value="management_system_reviewed_ref" selected="1">{TRANSLATE:management_system_reviewed_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="management_system_reviewed_ref">{TRANSLATE:management_system_reviewed_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                   -->
                                   
                                    <!-- material blocked -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlocked=1">
                                            <option value="materialBlocked" selected="1">{TRANSLATE:material_blocked}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="materialBlocked">{TRANSLATE:material_blocked}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- material blocked date -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlockedDate=1">
                                            <option value="materialBlockedDate" selected="1">{TRANSLATE:material_blocked_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="materialBlockedDate">{TRANSLATE:material_blocked_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- material blocked name -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlockedName=1">
                                            <option value="materialBlockedName" selected="1">{TRANSLATE:material_blocked_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="materialBlockedName">{TRANSLATE:material_blocked_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- material credited -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialCredited=1">
		                                      <option value="sp_materialCredited" selected="1">{TRANSLATE:material_credited}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialCredited">{TRANSLATE:material_credited}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- material disposed -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialDisposed=1">
		                                      <option value="sp_materialDisposed" selected="1">{TRANSLATE:material_disposed}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialDisposed">{TRANSLATE:material_disposed}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- Material disposed code -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialDisposedCode=1">
		                                      <option value="sp_materialDisposedCode" selected="1">{TRANSLATE:material_disposed_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialDisposedCode">{TRANSLATE:material_disposed_code}</option>
                                        </xsl:otherwise>
												</xsl:choose>
                                    
												<!-- Material disposed date -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialDisposedDate=1">
		                                      <option value="sp_materialDisposedDate" selected="1">{TRANSLATE:material_disposed_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialDisposedDate">{TRANSLATE:material_disposed_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- Material disposed name -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialDisposedName=1">
		                                      <option value="sp_materialDisposedName" selected="1">{TRANSLATE:material_disposed_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialDisposedName">{TRANSLATE:material_disposed_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
								
                                    <!-- Material disposed name -->
												<xsl:choose>
		                                  <xsl:when test="MaterialGroup=1">
		                                      <option value="MaterialGroup" selected="1">{TRANSLATE:material_group}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="MaterialGroup">{TRANSLATE:material_group}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
								
                                    <!-- material group -->
 <!--                                   <xsl:choose>
                                        <xsl:when test="MaterialGroup=1">
                                            <option value="MaterialGroup" selected="1">{TRANSLATE:material_group}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="MaterialGroup">{TRANSLATE:material_group}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
-->                                    
                                    <!-- material involved -->
                                    <xsl:choose>
                                        <xsl:when test="materialInvolved=1">
                                            <option value="materialInvolved" selected="1">{TRANSLATE:material_involved}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="materialInvolved">{TRANSLATE:material_involved}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- material replaced -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReplaced=1">
		                                      <option value="sp_materialReplaced" selected="1">{TRANSLATE:material_replaced}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialReplaced">{TRANSLATE:material_replaced}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- material returned -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReturned=1">
		                                      <option value="sp_materialReturned" selected="1">{TRANSLATE:material_returned}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialReturned">{TRANSLATE:material_returned}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- Material returned date -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReturnedDate=1">
		                                      <option value="sp_materialReturnedDate" selected="1">{TRANSLATE:material_returned_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialReturnedDate">{TRANSLATE:material_returned_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- Material returned name -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReturnedName=1">
		                                      <option value="sp_materialReturnedName" selected="1">{TRANSLATE:material_returned_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_materialReturnedName">{TRANSLATE:material_returned_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    
                                    <!-- N -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSentName=1">
                                            <option value="sampleSentName" selected="1">{TRANSLATE:sample_sent_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sampleSentName">{TRANSLATE:sample_sent_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>					
                                            
                                    
                                    
                                    <!-- P -->
                                    <!-- possible solutions -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions=1">
                                            <option value="possible_solutions" selected="1">{TRANSLATE:possible_solutions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possible_solutions">{TRANSLATE:possible_solutions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- possible solutions author -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions_author=1">
                                            <option value="possible_solutions_author" selected="1">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possible_solutions_author">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- possible solutions date -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions_date=1">
                                            <option value="possible_solutions_date" selected="1">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possible_solutions_date">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- preventive_action_verified_date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="estimatedDatePrev=1">
                                            <option value="estimatedDatePrev" selected="1">{TRANSLATE:preventive_action_verified_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="estimatedDatePrev">{TRANSLATE:preventive_action_verified_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    <!-- preventive_actions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventivePermCorrActions=1">
                                            <option value="preventivePermCorrActions" selected="1">{TRANSLATE:preventive_perm_corr_actions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventivePermCorrActions">{TRANSLATE:preventive_perm_corr_actions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    <!-- preventative actions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions=1">
                                            <option value="preventive_actions" selected="1">{TRANSLATE:preventive_actions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions">{TRANSLATE:preventive_actions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions author -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_author=1">
                                            <option value="preventive_actions_author" selected="1">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions_author">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                  <!-- preventative actions date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_date=1">
                                            <option value="preventive_actions_date" selected="1">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions_date">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions estimate date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_estimated=1">
                                            <option value="preventive_actions_estimated" selected="1">{TRANSLATE:preventive_actions_estimated}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions_estimated">{TRANSLATE:preventive_actions_estimated}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions implementation date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_implementation=1">
                                            <option value="preventive_actions_implementation" selected="1">{TRANSLATE:preventive_actions_implementation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions_implementation">{TRANSLATE:preventive_actions_implementation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions valitdation of effectiveness date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_effectiveness=1">
                                            <option value="preventive_actions_effectiveness" selected="1">{TRANSLATE:preventive_actions_effectiveness}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventive_actions_effectiveness">{TRANSLATE:preventive_actions_effectiveness}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- problem description -->
                                    <xsl:choose>
                                        <xsl:when test="problem_description=1">
                                            <option value="problem_description" selected="1">{TRANSLATE:problem_description}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="problem_description">{TRANSLATE:problem_description}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- process owner -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="ProcessOwner=1">
                                            <option value="ProcessOwner" selected="1">{TRANSLATE:process_owner}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="ProcessOwner">{TRANSLATE:process_owner}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- product descriptions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="product_description=1">
                                            <option value="product_description" selected="1">{TRANSLATE:product_description}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="product_description">{TRANSLATE:product_description}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    <!-- production date -->
                                    <xsl:choose>
                                        <xsl:when test="productionDate=1">
                                            <option value="productionDate" selected="1">{TRANSLATE:production_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="productionDate">{TRANSLATE:production_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- production quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity2=1">
                                            <option value="defectQuantity2" selected="1">{TRANSLATE:production_quantity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="defectQuantity2">{TRANSLATE:production_quantity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- purchase order number -->
                                    <xsl:choose>
                                        <xsl:when test="purchaseOrderNumber=1">
                                            <option value="purchaseOrderNumber" selected="1">{TRANSLATE:purchase_order_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="purchaseOrderNumber">{TRANSLATE:purchase_order_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- Q -->
                                    <xsl:choose>
                                        <xsl:when test="quantityReceived=1">
                                            <option value="quantityReceived" selected="1">{TRANSLATE:quantity_received}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="quantityReceived">{TRANSLATE:quantity_received}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="quantity_under_complaint_quantity=1">
                                            <option value="quantity_under_complaint_quantity" selected="1">{TRANSLATE:quantity_under_complaint_quantity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="quantity_under_complaint_quantity">{TRANSLATE:quantity_under_complaint_quantity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>	
                                    
                                    
                                    <!-- R -->
                                    <!-- request authorised -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestAuthorised=1">
		                                      <option value="sp_requestAuthorised" selected="1">{TRANSLATE:request_authorised}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_requestAuthorised">{TRANSLATE:request_authorised}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
                                    <!-- Request authorised date  -->
												<!--
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestAuthorisedDate=1">
		                                      <option value="sp_requestAuthorisedDate" selected="1">{TRANSLATE:request_authorised_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_requestAuthorisedDate">{TRANSLATE:request_authorised_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->                                    
	
                                    <!-- Request disposal -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestDisposal=1">
		                                      <option value="sp_requestDisposal" selected="1">{TRANSLATE:request_disposal}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_requestDisposal">{TRANSLATE:request_disposal}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- return the goods -->
                                    <xsl:choose>
                                        <xsl:when test="return_goods=1">
                                            <option value="return_goods" selected="1">{TRANSLATE:return_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="return_goods">{TRANSLATE:return_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- rework the goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_reworkGoods=1">
                                            <option value="sp_reworkGoods" selected="1">{TRANSLATE:rework_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_reworkGoods">{TRANSLATE:rework_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- root causes -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes=1">
                                            <option value="root_causes" selected="1">{TRANSLATE:root_causes}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="root_causes">{TRANSLATE:root_causes}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- root causes author -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes_author=1">
                                            <option value="root_causes_author" selected="1">{TRANSLATE:root_causes_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="root_causes_author">{TRANSLATE:root_causes_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- root causes code -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="root_cause_code=1">
                                            <option value="root_cause_code" selected="1">{TRANSLATE:root_cause_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="root_cause_code">{TRANSLATE:root_cause_code}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- root causes date -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes_date=1">
                                            <option value="root_causes_date" selected="1">{TRANSLATE:root_causes_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="root_causes_date">{TRANSLATE:root_causes_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- S -->
                                    <!-- sales office -->
                                    <xsl:choose>
                                        <xsl:when test="SalesOffice=1">
                                            <option value="SalesOffice" selected="1">{TRANSLATE:sales_office}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SalesOffice">{TRANSLATE:sales_office}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- sample/photo reception date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="sample_reception_date=1">
                                            <option value="sample_reception_date" selected="1">{TRANSLATE:sample_reception_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sample_reception_date">{TRANSLATE:sample_reception_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- sample/phot trnasfereed to po date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="sample_date=1">
                                            <option value="sample_date" selected="1">{TRANSLATE:sample_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sample_date">{TRANSLATE:sample_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- sample sent -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSent=1">
                                            <option value="sampleSent" selected="1">{TRANSLATE:sample_sent}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sampleSent">{TRANSLATE:sample_sent}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- SAP Customer name -->
<!--                                    <xsl:choose>
                                        <xsl:when test="SAPCustomerName=1">
                                            <option value="SAPCustomerName" selected="1">{TRANSLATE:sap_customer_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SAPCustomerName">{TRANSLATE:sap_customer_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
-->                                
                                    <!-- SAP customer number -->
<!--                                    <xsl:choose>
                                        <xsl:when test="SAPCustomerNumber=1">
                                            <option value="SAPCustomerNumber" selected="1">{TRANSLATE:sap_customer_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SAPCustomerNumber">{TRANSLATE:sap_customer_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
-->                                    
                                    <!-- SAP Item Numbers -->
                                    <xsl:choose>
                                        <xsl:when test="SAPItemNumber=1">
                                            <option value="SAPItemNumber" selected="1">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SAPItemNumber">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- SAP Item Number conclusion -->
                                    <xsl:choose>
                                        <xsl:when test="sp_sapItemNumber=1">
                                            <option value="sp_sapItemNumber" selected="1">{TRANSLATE:sap_item_number_conclusion}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_sapItemNumber">{TRANSLATE:sap_item_number_conclusion}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- SAP return number -->
												<xsl:choose>
		                                  <xsl:when test="sp_sapReturnNumber=1">
		                                      <option value="sp_sapReturnNumber" selected="1">{TRANSLATE:sap_return_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_sapReturnNumber">{TRANSLATE:sap_return_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- SAP supplier name -->
                                    <xsl:choose>
                                        <xsl:when test="SapSupplierNumber=1">
                                            <option value="SapSupplierNumber" selected="1">{TRANSLATE:sap_supplier_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SapSupplierNumber">{TRANSLATE:sap_supplier_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- SAP supplier number -->
                                    <xsl:choose>
                                        <xsl:when test="SapSupplierName=1">
                                            <option value="SapSupplierName" selected="1">{TRANSLATE:sap_supplier_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SapSupplierName">{TRANSLATE:sap_supplier_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- site concerned -->
                                    <xsl:choose>
                                        <xsl:when test="SiteConcerned=1">
                                            <option value="SiteConcerned" selected="1">{TRANSLATE:site_concerned}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="SiteConcerned">{TRANSLATE:site_concerned}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- sort goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_sortGoods=1">
                                            <option value="sp_sortGoods" selected="1">{TRANSLATE:sort_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_sortGoods">{TRANSLATE:sort_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>	
                                    
                                   	<!-- status -->
                                    <xsl:choose>
                                        <xsl:when test="status=1">
                                            <option value="status" selected="1">{TRANSLATE:status}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="status">{TRANSLATE:status}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>	
                                    
                                   	<!-- Supplier credit note received  -->
                                   	<xsl:choose>
		                                  <xsl:when test="sp_supplierCreditNoteRec=1">
		                                      <option value="sp_supplierCreditNoteRec" selected="1">{TRANSLATE:supplier_credit_note_received}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_supplierCreditNoteRec">{TRANSLATE:supplier_credit_note_received}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- Supplier credit number  -->
									<xsl:choose>
		                                  <xsl:when test="sp_supplierCreditNumber=1">
		                                      <option value="sp_supplierCreditNumber" selected="1">{TRANSLATE:supplier_credit_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_supplierCreditNumber">{TRANSLATE:supplier_credit_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
												
                                    <!-- supplier item number -->
                                    <xsl:choose>
                                        <xsl:when test="supplierItemNumber=1">
                                            <option value="supplierItemNumber" selected="1">{TRANSLATE:supplier_item_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="supplierItemNumber">{TRANSLATE:supplier_item_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- supplier product description -->
                                    <xsl:choose>
                                        <xsl:when test="supplierProductDescription=1">
                                            <option value="supplierProductDescription" selected="1">{TRANSLATE:supplier_product_description }</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="supplierProductDescription">{TRANSLATE:supplier_product_description}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>	
                                    
									<!-- Supplier replacement received -->
									<xsl:choose>
		                                  <xsl:when test="sp_supplierReplacementRec=1">
		                                      <option value="sp_supplierReplacementRec" selected="1">{TRANSLATE:supplier_replacement_received}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_supplierReplacementRec">{TRANSLATE:supplier_replacement_received}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    
                                    <!-- T -->
                                    <xsl:choose>
                                        <xsl:when test="team_leader=1">
                                            <option value="team_leader" selected="1">{TRANSLATE:team_leader}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="team_leader">{TRANSLATE:team_leader}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- team member -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="team_member=1">
                                            <option value="team_member" selected="1">{TRANSLATE:team_member}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="team_member">{TRANSLATE:team_member}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                   -->
                                   
                                    <!-- transit date -->
                                    <xsl:choose>
                                        <xsl:when test="transitDate=1">
                                            <option value="transitDate" selected="1">{TRANSLATE:transit_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="transitDate">{TRANSLATE:transit_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
									         <!-- transit quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity3=1">
                                            <option value="defectQuantity3" selected="1">{TRANSLATE:transit_quantity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="defectQuantity3">{TRANSLATE:transit_quantity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- U -->
                                    <!-- use the goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_useGoods=1">
                                            <option value="sp_useGoods" selected="1">{TRANSLATE:use_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_useGoods">{TRANSLATE:use_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- V -->
                                    <!-- value -->
                                    <xsl:choose>
                                        <xsl:when test="sp_value=1">
                                            <option value="sp_value" selected="1">{TRANSLATE:value}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_value">{TRANSLATE:value}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- W -->
                                    <!-- warehouse date -->
                                    <xsl:choose>
                                        <xsl:when test="warehouseDate=1">
                                            <option value="warehouseDate" selected="1">{TRANSLATE:warehouse_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="warehouseDate">{TRANSLATE:warehouse_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- warehouse quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity=1">
                                            <option value="defectQuantity" selected="1">{TRANSLATE:warehouse_quantity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="defectQuantity">{TRANSLATE:warehouse_quantity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- internal fields turned into supplier complaint -->
                                    <xsl:choose>
                                        <xsl:when test="internal_teamLeader=1">
                                            <option value="internal_teamLeader" selected="1">{TRANSLATE:internal_teamLeader}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_teamLeader">{TRANSLATE:internal_teamLeader}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_teamMember=1">
                                            <option value="internal_teamMember" selected="1">{TRANSLATE:internal_teamMember}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_teamMember">{TRANSLATE:internal_teamMember}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationMade=1">
                                            <option value="internal_qu_stockVerificationMade" selected="1">{TRANSLATE:internal_qu_stockVerificationMade}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_qu_stockVerificationMade">{TRANSLATE:internal_qu_stockVerificationMade}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationName=1">
                                            <option value="internal_qu_stockVerificationName" selected="1">{TRANSLATE:internal_qu_stockVerificationName}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_qu_stockVerificationName">{TRANSLATE:internal_qu_stockVerificationName}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationDate=1">
                                            <option value="internal_qu_stockVerificationDate" selected="1">{TRANSLATE:internal_qu_stockVerificationDate}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_qu_stockVerificationDate">{TRANSLATE:internal_qu_stockVerificationDate}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_otherMaterialEffected=1">
                                            <option value="internal_qu_otherMaterialEffected" selected="1">{TRANSLATE:internal_qu_otherMaterialEffected}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_qu_otherMaterialEffected">{TRANSLATE:internal_qu_otherMaterialEffected}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_otherMatDetails=1">
                                            <option value="internal_qu_otherMatDetails" selected="1">{TRANSLATE:internal_qu_otherMatDetails}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_qu_otherMatDetails">{TRANSLATE:internal_qu_otherMatDetails}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_analysis=1">
                                            <option value="internal_analysis" selected="1">{TRANSLATE:internal_analysis}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_analysis">{TRANSLATE:internal_analysis}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_author=1">
                                            <option value="internal_author" selected="1">{TRANSLATE:internal_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_author">{TRANSLATE:internal_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_analysisDate=1">
                                            <option value="internal_analysisDate" selected="1">{TRANSLATE:internal_analysisDate}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_analysisDate">{TRANSLATE:internal_analysisDate}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_additionalComments=1">
                                            <option value="internal_additionalComments" selected="1">{TRANSLATE:internal_additionalComments}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internal_additionalComments">{TRANSLATE:internal_additionalComments}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                           			
                                    
								</xsl:element>
							</td>
							
							<td width="2%">
							</td>
							
							<td> 
								<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveSupplierSelectionRight();" /> 
								<br />
								<br />
								<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveSupplierSelectionLeft();" /> 
							</td>
							
							<td width="2%">
							</td>
			
							<td valign="top">
								<xsl:element name="select">
									<xsl:attribute name="name">columnsSupplier[]</xsl:attribute>
									<xsl:attribute name="id">columnsSupplier</xsl:attribute>
									<xsl:attribute name="multiple">true</xsl:attribute>
									<xsl:attribute name="size">10</xsl:attribute>
									<xsl:attribute name="class">dropdown required</xsl:attribute>
												
                                    <!-- A -->
												<!-- Actions requested from the customer -->
                                    <xsl:choose>
                                        <xsl:when test="action_requested=1">
                                            <option value="action_requested">{TRANSLATE:actions_by_scapa_to_minimise_problem}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- additional comments -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComments=1">
                                            <option value="additionalComments">{TRANSLATE:additional_comments_no_warning}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Additional complaint cost -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComplaintCost=1">
                                            <option value="additionalComplaintCost">{TRANSLATE:additional_complaint_cost}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Amount - conclusion -->
                                    <xsl:choose>
                                        <xsl:when test="sp_amount=1">
                                            <option value="sp_amount">{TRANSLATE:amount}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Analysis -->
                                    <xsl:choose>
                                        <xsl:when test="analysis=1">
                                            <option value="analysis">{TRANSLATE:analysis}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Analysis Date -->
                                    <xsl:choose>
                                        <xsl:when test="analysis_date=1">
                                            <option value="analysis_date">{TRANSLATE:analysis_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- approval for usage -->
                                    <xsl:choose>
		                                  <xsl:when test="customerDerongation=1">
		                                      <option value="customerDerongation">{TRANSLATE:customer_derongation}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- attributable process -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="attributable_process=1">
                                            <option value="attributable_process">{TRANSLATE:attributable_process}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- author -->
                                    <xsl:choose>
                                        <xsl:when test="author=1">
                                            <option value="author">{TRANSLATE:author}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    
                                    <!-- B -->
                                    <!-- batch number -->
                                    <xsl:choose>
                                        <xsl:when test="batch_number=1">
                                            <option value="batch_number">{TRANSLATE:batch_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- buyer -->
                                    <xsl:choose>
                                        <xsl:when test="buyer=1">
                                            <option value="buyer" >{TRANSLATE:buyer}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- C -->	
                                    <!-- carrier name -->							
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="carrier_name=1">
                                            <option value="carrier_name">{TRANSLATE:carrier_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- category -->
                                    <xsl:choose>
                                        <xsl:when test="Category=1">
                                            <option value="Category">{TRANSLATE:category}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- chosen complaint owner -->
												<xsl:choose>
		                                  <xsl:when test="processOwnerRequest=1">
		                                      <option value="processOwnerRequest">{TRANSLATE:chosen_complaint_owner}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- chosen complaint owner conclusion -->
												<xsl:choose>
		                                  <xsl:when test="processOwner3=1">
		                                      <option value="processOwner3">{TRANSLATE:chosen_complaint_owner}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- comment -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_comment=1">
		                                      <option value="sp_comment">{TRANSLATE:comment}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- complaint justified -->
                                    <xsl:choose>
                                        <xsl:when test="complaint_justified=1">
                                            <option value="complaint_justified">{TRANSLATE:complaint_justified}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- complaint justified -->
                                    <xsl:choose>
                                        <xsl:when test="complaint_justified=1">
                                            <option value="complaint_justified">{TRANSLATE:complaint_justified}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- complaint location -->
                                    <xsl:choose>
                                        <xsl:when test="ComplaintLocation=1">
                                            <option value="ComplaintLocation">{TRANSLATE:complaint_location}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- complaint owner -->
                                    <xsl:choose>
                                        <xsl:when test="ComplaintOwner=1">
                                            <option value="ComplaintOwner">{TRANSLATE:complaint_owner}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- complaint type -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="ComplaintType=1">
                                            <option value="ComplaintType">{TRANSLATE:complaint_type}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- complaint value -->
                                    <xsl:choose>
                                        <xsl:when test="gbpComplaintValue=1">
                                            <option value="gbpComplaintValue">{TRANSLATE:complaint_value}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- complaint value -->
                                    <xsl:choose>
                                        <xsl:when test="complaint_value_quantity=1">
                                            <option value="complaint_value_quantity">{TRANSLATE:complaint_value_quantity}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- comments -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="comments=1">
                                            <option value="comments">{TRANSLATE:comments}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- comments/reason for rejection -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="reason_for_rejection=1">
                                            <option value="reason_for_rejection">{TRANSLATE:reason_for_rejection}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- containment action -->
                                    <xsl:choose>
                                        <xsl:when test="containment_action=1">
                                            <option value="containment_action">{TRANSLATE:containment_action}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action author -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="containment_action_author=1">
                                            <option value="containment_action_author">{TRANSLATE:containment_action_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- containment action date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="containment_action_date=1">
                                            <option value="containment_action_date">{TRANSLATE:containment_action_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- copy credit request to -->
                                    <xsl:choose>
                                        <xsl:when test="ccComercialCredit=1">
                                            <option value="ccComercialCredit">{TRANSLATE:cc_commercial_credit}</option>
                                        </xsl:when>
                                    </xsl:choose>	
                                    <!-- correct category -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="correct_category=1">
                                            <option value="correct_category">{TRANSLATE:correct_category}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- created date -->
                                    <xsl:choose>
                                        <xsl:when test="customerComplaintDate=1">
                                            <option value="customerComplaintDate">{TRANSLATE:customer_complaint_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- created date -->
                                    <xsl:choose>
                                        <xsl:when test="CreatedDate=1">
                                            <option value="CreatedDate">{TRANSLATE:created_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!--customer name -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_customerName=1">
		                                      <option value="sp_customerName">{TRANSLATE:customer_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer specification -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecification=1">
                                            <option value="customerSpecification">{TRANSLATE:customer_specification}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer specification date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationDate=1">
                                            <option value="customerSpecificationDate">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- customer specification ref -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationRef=1">
                                            <option value="customerSpecificationRef">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    
                                    <!-- D -->
                                    <!-- date of analysis -->
                                    <xsl:choose>
                                        <xsl:when test="dateOfAnalysis=1">
                                            <option value="dateOfAnalysis">{TRANSLATE:date_of_analysis}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- date sample received by po -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="date_sample_received=1">
                                            <option value="date_sample_received">{TRANSLATE:date_sample_received}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- date sample sent -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSentDate=1">
                                            <option value="sampleSentDate">{TRANSLATE:sample_sent_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   
                                   <!-- delivery note -->
                                    <xsl:choose>
                                        <xsl:when test="deliveryNote=1">
                                            <option value="deliveryNote">{TRANSLATE:delivery_note}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- details of complaint cost -->
                                    <xsl:choose>
                                        <xsl:when test="detailsOfComplaintCost=1">
                                            <option value="detailsOfComplaintCost">{TRANSLATE:details_of_complaint_cost}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- dispose the goods -->
                                    <xsl:choose>
                                        <xsl:when test="dispose_goods=1">
                                            <option value="dispose_goods">{TRANSLATE:dispose_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Does 8D Action Exist -->
                                    <xsl:choose>
                                        <xsl:when test="does8DActionExist=1">
                                            <option value="does8DActionExist">{TRANSLATE:does_8d_action_exist}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Does Containment Action Exist -->
		                              <xsl:choose>
		                                  <xsl:when test="doesContainmentActionExist=1">
		                                      <option value="doesContainmentActionExist">{TRANSLATE:does_containment_action_exist}</option>
		                                  </xsl:when>
		                              </xsl:choose>
                                    
                                    
                                    <!-- E -->
                                    <!-- Email comment  -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestEmailText=1">
		                                      <option value="sp_requestEmailText">{TRANSLATE:email_text_comment}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- email text -->
												<!--
												<xsl:choose>
		                                  <xsl:when test="sp_requestAuthorisedEmailText=1">
		                                      <option value="sp_requestAuthorisedEmailText">{TRANSLATE:email_text}</option>
                                        </xsl:when>
                                    </xsl:choose>
												-->
                                    
                                    <!-- F -->
                                    <!-- failure code -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="failure_code=1">
                                            <option value="failure_code">{TRANSLATE:failure_code}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- final comment -->
												<xsl:choose>
		                                  <xsl:when test="sp_finalComments=1">
		                                      <option value="sp_finalComment">{TRANSLATE:final_comment}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   
                                    <!-- finance stage complete -->
                                    <xsl:choose>
                                        <xsl:when test="financeStageComplete=1">
                                            <option value="financeStageComplete">{TRANSLATE:finance_stage_completed}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Flow chart -->
												<xsl:choose>
                                        <xsl:when test="flowChart=1">
                                            <option value="flowChart">{TRANSLATE:flow_chart}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- fmea -->
                                    <xsl:choose>
                                        <xsl:when test="fmea=1">
                                            <option value="fmea">{TRANSLATE:fmea}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- fmea date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="fmeaDate=1">
                                            <option value="fmeaDate">{TRANSLATE:fmea_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    <!-- fmea ref -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="fmeaRef=1">
                                            <option value="fmeaRef">{TRANSLATE:fmea_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   -->
                                    
                                    <!-- G -->
                                    <xsl:choose>
                                        <xsl:when test="g8d=1">
                                            <option value="g8d">{TRANSLATE:g8d}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="goodsReceivedDate=1">
                                            <option value="goodsReceivedDate">{TRANSLATE:goods_received_date}</option>
                                        </xsl:when>
                                    </xsl:choose>					
                                    
                                    <xsl:choose>
                                        <xsl:when test="goodsReceivedNumber=1">
                                            <option value="goodsReceivedNumber">{TRANSLATE:goods_received_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- H -->
                                    <!-- has a sample been received -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_sample_received=1">
                                            <option value="is_sample_received">{TRANSLATE:is_sample_received}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- how was error detected? -->
                                    <xsl:choose>
                                        <xsl:when test="how_was_error_detected=1">
                                            <option value="how_was_error_detected">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    
                                    
                                    <!-- I -->
                                    <!-- implemented actions -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions=1">
                                            <option value="implemented_actions">>{TRANSLATE:implemented_actions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- implemented actions author -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_author=1">
                                            <option value="implemented_actions_author">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- implmented actions date -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_date=1">
                                            <option value="implemented_actions_date">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- implmented actions estimate date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_estimated=1">
                                            <option value="implemented_actions_estimated">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- implmented actions implmentation date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_implementation=1">
                                            <option value="implemented_actions_implementation">{TRANSLATE:implemented_actions_implementation}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- implmented actions validation of effectiveness date -->
                                    <xsl:choose>
                                        <xsl:when test="implemented_actions_effectiveness=1">
                                            <option value="implemented_actions_effectiveness">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- inspection instructions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructions=1">
                                            <option value="inspectionInstructions">{TRANSLATE:inspectionInstructions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- inspection instructions date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructionsDate=1">
                                            <option value="inspectionInstructionsDate">{TRANSLATE:inspectionInstructionsDate}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   -->
                                   
                                    <!-- inspection instructions reference -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="inspectionInstructionsRef=1">
                                            <option value="inspectionInstructionsRef">{TRANSLATE:inspectionInstructionsRef}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                                                        
                                    <!-- Internal Complaint Status -->
												<xsl:choose>
		                                  <xsl:when test="internalComplaintStatus=1">
		                                      <option value="internalComplaintStatus">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- invoice delivery note -->
                                    <xsl:choose>
                                        <xsl:when test="invoiceDeliveryNote=1">
                                            <option value="invoiceDeliveryNote">{TRANSLATE:invoice_delivery_note}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Invoice number -->
                                    <xsl:choose>
                                        <xsl:when test="goodJobInvoiceNo=1">
                                            <option value="goodJobInvoiceNo">{TRANSLATE:invoice_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- is complaint category right -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_complaint_cat_right=1">
                                            <option value="is_complaint_cat_right">{TRANSLATE:is_complaint_cat_right}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- is process owner correct -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="is_po_right=1">
                                            <option value="is_po_right">{TRANSLATE:is_po_right}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <xsl:choose>
                                        <xsl:when test="isWithSupplier=1">
                                            <option value="isWithSupplier">{TRANSLATE:is_with_supplier}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- M -->
                                    <!-- management system reviewed -->
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed=1">
                                            <option value="management_system_reviewed">{TRANSLATE:management_system_reviewed}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- management system reviewed date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed_date=1">
                                            <option value="management_system_reviewed_date">{TRANSLATE:management_system_reviewed_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- management system reviewed reference -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="management_system_reviewed_ref=1">
                                            <option value="management_system_reviewed_ref">{TRANSLATE:management_system_reviewed_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   -->
                                    <!-- material blocked -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlocked=1">
                                            <option value="materialBlocked">{TRANSLATE:material_blocked}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material blocked date -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlockedDate=1">
                                            <option value="materialBlockedDate">{TRANSLATE:material_blocked_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material blocked name -->
                                    <xsl:choose>
                                        <xsl:when test="materialBlockedName=1">
                                            <option value="materialBlockedName">{TRANSLATE:material_blocked_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material credited -->
                                    <xsl:choose>
                                        <xsl:when test="sp_materialCredited=1">
                                            <option value="sp_materialCredited">{TRANSLATE:material_credited}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material disposed -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialDisposed=1">
		                                      <option value="sp_materialDisposed">{TRANSLATE:material_disposed}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Material disposed code -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialDisposedCode=1">
		                                      <option value="sp_materialDisposedCode">{TRANSLATE:material_disposed_code}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Material disposed date -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialDisposedDate=1">
		                                      <option value="sp_materialDisposedDate">{TRANSLATE:material_disposed_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- Material disposed name -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialDisposedName=1">
		                                      <option value="sp_materialDisposedName">{TRANSLATE:material_disposed_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Material disposed name -->
                                    <xsl:choose>
		                                  <xsl:when test="MaterialGroup=1">
		                                      <option value="MaterialGroup">{TRANSLATE:material_groups}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material group -->
<!--                                   <xsl:choose>
                                        <xsl:when test="MaterialGroup=1">
                                            <option value="MaterialGroup">{TRANSLATE:material_group}</option>
                                        </xsl:when>
                                    </xsl:choose>
-->                                    
                                    <!-- material involved -->
                                    <xsl:choose>
                                        <xsl:when test="materialInvolved=1">
                                            <option value="materialInvolved">{TRANSLATE:material_involved}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material replaced -->
                                    <xsl:choose>
                                        <xsl:when test="sp_materialReplaced=1">
                                            <option value="sp_materialReplaced">{TRANSLATE:material_replaced}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- material returned -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_materialReturned=1">
		                                      <option value="sp_materialReturned">{TRANSLATE:material_returned}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Material returned date -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReturnedDate=1">
		                                      <option value="sp_materialReturnedDate">{TRANSLATE:material_returned_date}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- Material returned name -->
												<xsl:choose>
		                                  <xsl:when test="sp_materialReturnedName=1">
		                                      <option value="sp_materialReturnedName">{TRANSLATE:material_returned_name}</option>
                                        </xsl:when>
                                    </xsl:choose> 
                                    
                                    
                                    
                                    
                                    <!-- N -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSentName=1">
                                            <option value="sampleSentName">{TRANSLATE:sample_sent_name}</option>
                                        </xsl:when>
                                    </xsl:choose>					
                                            
                                    
                                    
                                    <!-- P -->
                                    <!-- possible solutions -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions=1">
                                            <option value="possible_solutions">{TRANSLATE:possible_solutions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- possible solutions author -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions_author=1">
                                            <option value="possible_solutions_author">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- possible solutions date -->
                                    <xsl:choose>
                                        <xsl:when test="possible_solutions_date=1">
                                            <option value="possible_solutions_date">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- preventive_action_verified_date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="estimatedDatePrev=1">
                                            <option value="estimatedDatePrev" selected="1">{TRANSLATE:preventive_action_verified_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    <!-- preventive_actions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventivePermCorrActions=1">
                                            <option value="preventivePermCorrActions" selected="1">{TRANSLATE:preventive_perm_corr_actions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    <!-- preventative actions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions=1">
                                            <option value="preventive_actions">{TRANSLATE:preventive_actions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions author -->
                                   <!--
                                   <xsl:choose>
                                        <xsl:when test="preventive_actions_author=1">
                                            <option value="preventive_actions_author">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   -->
                                    <!-- preventative actions date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_date=1">
                                            <option value="preventive_actions_date">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions estimate date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_estimated=1">
                                            <option value="preventive_actions_estimated">{TRANSLATE:preventive_actions_estimated}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- preventative actions implementation date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_implementation=1">
                                            <option value="preventive_actions_implementation">{TRANSLATE:preventive_actions_implementation}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    <!-- preventative actions valitdation of effectiveness date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="preventive_actions_effectiveness=1">
                                            <option value="preventive_actions_effectiveness">{TRANSLATE:preventive_actions_effectiveness}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                   <!-- problem description -->
                                    <xsl:choose>
                                        <xsl:when test="problem_description=1">
                                            <option value="problem_description">{TRANSLATE:problem_description}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- process owner -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="ProcessOwner=1">
                                            <option value="ProcessOwner">{TRANSLATE:process_owner}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- product descriptions -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="product_description=1">
                                            <option value="product_description">{TRANSLATE:product_description}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    <!-- production date -->
                                    <xsl:choose>
                                        <xsl:when test="productionDate=1">
                                            <option value="productionDate">{TRANSLATE:production_date}</option>
                                        </xsl:when>
                                   </xsl:choose>
                                   
                                   <!-- production quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity2=1">
                                            <option value="defectQuantity2">{TRANSLATE:production_quantity}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- purchase order number -->
                                    <xsl:choose>
                                        <xsl:when test="purchaseOrderNumber=1">
                                            <option value="purchaseOrderNumber">{TRANSLATE:purchase_order_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- Q -->
                                    <xsl:choose>
                                        <xsl:when test="quantityReceived=1">
                                            <option value="quantityReceived">{TRANSLATE:quantity_received}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="quantity_under_complaint_quantity=1">
                                            <option value="quantity_under_complaint_quantity">{TRANSLATE:quantity_under_complaint_quantity}</option>
                                        </xsl:when>
                                    </xsl:choose>	
                                    
                                    
                                    <!-- R -->
                                    <!-- request authorised -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestAuthorised=1">
		                                      <option value="sp_requestAuthorised">{TRANSLATE:request_authorised}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- Request authorised date  -->
                                    <!--
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestAuthorisedDate=1">
		                                      <option value="sp_requestAuthorisedDate">{TRANSLATE:request_authorised_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->

                                    <!-- Request disposal -->
                                    <xsl:choose>
		                                  <xsl:when test="sp_requestDisposal=1">
		                                      <option value="sp_requestDisposal">{TRANSLATE:request_disposal}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                  	
                                    <!-- return the goods -->
                                    <xsl:choose>
                                        <xsl:when test="return_goods=1">
                                            <option value="return_goods">{TRANSLATE:return_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- rework the goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_reworkGoods=1">
                                            <option value="sp_reworkGoods">{TRANSLATE:rework_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- root causes -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes=1">
                                            <option value="root_causes">{TRANSLATE:root_causes}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- root causes author -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes_author=1">
                                            <option value="root_causes_author">{TRANSLATE:root_causes_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- root causes code -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="root_cause_code=1">
                                            <option value="root_cause_code">{TRANSLATE:root_cause_code}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- root causes date -->
                                    <xsl:choose>
                                        <xsl:when test="root_causes_date=1">
                                            <option value="root_causes_date">{TRANSLATE:root_causes_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- S -->
                                    <!-- sales office -->
                                    <xsl:choose>
                                        <xsl:when test="SalesOffice=1">
                                            <option value="SalesOffice">{TRANSLATE:sales_office}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- sample/photo reception date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="sample_reception_date=1">
                                            <option value="sample_reception_date">{TRANSLATE:sample_reception_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- sample/phot trnasfereed to po date -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="sample_date=1">
                                            <option value="sample_date">{TRANSLATE:sample_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- sample sent -->
                                    <xsl:choose>
                                        <xsl:when test="sampleSent=1">
                                            <option value="sampleSent">{TRANSLATE:sample_sent}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- SAP Customer name -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="SAPCustomerName=1">
                                            <option value="SAPCustomerName">{TRANSLATE:sap_customer_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- SAP customer number -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="SAPCustomerNumber=1">
                                            <option value="SAPCustomerNumber">{TRANSLATE:sap_customer_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    -->
                                    
                                    <!-- SAP Item Numbers -->
                                    <xsl:choose>
                                        <xsl:when test="SAPItemNumber=1">
                                            <option value="SAPItemNumber">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- SAP Item Numbers -->
                                    <xsl:choose>
                                        <xsl:when test="sp_sapItemNumber=1">
                                            <option value="sp_sapItemNumber">{TRANSLATE:sap_item_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
								            <!-- SAP return number -->
												<xsl:choose>
		                                  <xsl:when test="sp_sapReturnNumber=1">
		                                      <option value="sp_sapReturnNumber">{TRANSLATE:sap_return_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- SAP supplier name -->
                                    <xsl:choose>
                                        <xsl:when test="SapSupplierNumber=1">
                                            <option value="SapSupplierNumber" >{TRANSLATE:sap_supplier_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- SAP supplier number -->
                                    <xsl:choose>
                                        <xsl:when test="SapSupplierName=1">
                                            <option value="SapSupplierName" >{TRANSLATE:sap_supplier_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- site concerned -->
                                    <xsl:choose>
                                        <xsl:when test="SiteConcerned=1">
                                            <option value="SiteConcerned">{TRANSLATE:site_concerned}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- sort goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_sortGoods=1">
                                            <option value="sp_sortGoods">{TRANSLATE:sort_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>	

												<!-- status -->
                                    <xsl:choose>
                                        <xsl:when test="status=1">
                                            <option value="status">{TRANSLATE:status}</option>
                                        </xsl:when>
                                    </xsl:choose>	

												<!-- Supplier credit note received  -->
												<xsl:choose>
		                                  <xsl:when test="sp_supplierCreditNoteRec=1">
		                                      <option value="sp_supplierCreditNoteRec">{TRANSLATE:supplier_credit_note_received}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- Supplier credit number  -->
												<xsl:choose>
		                                  <xsl:when test="sp_supplierCreditNumber=1">
		                                      <option value="sp_supplierCreditNumber">{TRANSLATE:supplier_credit_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
												
                                    <!-- supplier item number -->
                                    <xsl:choose>
                                        <xsl:when test="supplierItemNumber=1">
                                            <option value="supplierItemNumber">{TRANSLATE:supplier_item_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- supplier product description -->
                                    <xsl:choose>
                                        <xsl:when test="supplierProductDescription=1">
                                            <option value="supplierProductDescription">{TRANSLATE:supplier_product_description }</option>
                                        </xsl:when>
                                    </xsl:choose>	
                                    
                                    <!-- Supplier replacement received -->
												<xsl:choose>
		                                  <xsl:when test="sp_supplierReplacementRec=1">
		                                      <option value="sp_supplierReplacementRec">{TRANSLATE:supplier_replacement_received}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    
                                    <!-- T -->
                                    <xsl:choose>
                                        <xsl:when test="team_leader=1">
                                            <option value="team_leader">{TRANSLATE:team_leader}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- team member -->
                                    <!--
                                    <xsl:choose>
                                        <xsl:when test="team_member=1">
                                            <option value="team_member">{TRANSLATE:team_member}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                   -->
                                   
                                    <!-- transit date -->
                                    <xsl:choose>
                                        <xsl:when test="transitDate=1">
                                            <option value="transitDate">{TRANSLATE:transit_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- transit quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity3=1">
                                            <option value="defectQuantity3">{TRANSLATE:transit_quantity}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- U -->
                                    <!-- use the goods -->
                                    <xsl:choose>
                                        <xsl:when test="sp_useGoods=1">
                                            <option value="sp_useGoods">{TRANSLATE:use_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- V -->
                                    <!-- value -->
                                    <xsl:choose>
                                        <xsl:when test="sp_value=1">
                                            <option value="sp_value">{TRANSLATE:sp_value}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- W -->
                                    <!-- warehouse date -->
                                    <xsl:choose>
                                        <xsl:when test="warehouseDate=1">
                                            <option value="warehouseDate">{TRANSLATE:warehouse_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    <!-- warehouse quantity -->
                                    <xsl:choose>
                                        <xsl:when test="defectQuantity=1">
                                            <option value="defectQuantity">{TRANSLATE:warehouse_quantity}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- internal fields turned into supplier complaint -->
                                    <xsl:choose>
                                        <xsl:when test="internal_teamLeader=1">
                                            <option value="internal_teamLeader">{TRANSLATE:internal_teamLeader}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_teamMember=1">
                                            <option value="internal_teamMember">{TRANSLATE:internal_teamMember}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationMade=1">
                                            <option value="internal_qu_stockVerificationMade">{TRANSLATE:internal_qu_stockVerificationMade}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationName=1">
                                            <option value="internal_qu_stockVerificationName">{TRANSLATE:internal_qu_stockVerificationName}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_stockVerificationDate=1">
                                            <option value="internal_qu_stockVerificationDate">{TRANSLATE:internal_qu_stockVerificationDate}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_otherMaterialEffected=1">
                                            <option value="internal_qu_otherMaterialEffected">{TRANSLATE:internal_qu_otherMaterialEffected}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_qu_otherMatDetails=1">
                                            <option value="internal_qu_otherMatDetails">{TRANSLATE:internal_qu_otherMatDetails}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_analysis=1">
                                            <option value="internal_analysis">{TRANSLATE:internal_analysis}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_author=1">
                                            <option value="internal_author">{TRANSLATE:internal_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_analysisDate=1">
                                            <option value="internal_analysisDate">{TRANSLATE:internal_analysisDate}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
                                        <xsl:when test="internal_additionalComments=1">
                                            <option value="internal_additionalComments">{TRANSLATE:internal_additionalComments}</option>
                                        </xsl:when>
                                    </xsl:choose>
                           
                                    
									
								</xsl:element>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>	
	</xsl:template>
	
	
	<!-- Quality complaint custom fields -->

	<xsl:template match="qualityColumnFilters">
		<script language="JavaScript">
			function moveQualitySelectionRight()
			{
				var i = 0;
				var selColumns = document.getElementById('columnsQuality');
				while(i != document.form.columnsorig.length)
				{
					if(document.form.columnsorig.options[i].selected)
					{
						//we now need to check if it is already in the list - if not add
						var foundMatch = false;
						var j = 0;
						while(j != selColumns.options.length)
						{
							if(selColumns.options[j].value == document.form.columnsorig.options[i].value)
							foundMatch = true;
							j++;
						}
						if(!foundMatch)
						selColumns.options[selColumns.options.length] = new Option(document.form.columnsorig.options[i].value,document.form.columnsorig.options[i].value);
					}
					i++;
				}
				selectAllColumns()
			}
			
			function moveQualitySelectionLeft()
			{	
				var i = 0;
				var toDelete = new Array();
				var selColumns = document.getElementById('columnsQuality');
				var loopLength = selColumns.options.length;
				i = (loopLength-1);
				while(i != -1)
				{
					if(selColumns.options[i].selected)
					{
						selColumns.options[i] = null;
					}
					i--;
				}
				selectAllColumns()
			}
		</script>
		
		<h1 style="margin-bottom: 10px;">
			Column Filters
		</h1>
		
		<table width="100%" cellspacing="0" cellpadding="4" style="border-right: 5px solid #EFEFEF; border-left: 5px solid #EFEFEF;">
			<tr id="filtersRow" class="valid_row">
				
				<td class="cell_name" width="15%" valign="top">
				</td>
				
				<td>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td>
								<xsl:element name="select">
									<xsl:attribute name="name">columnsorig</xsl:attribute>
									
									<xsl:attribute name="multiple">true</xsl:attribute>
									<xsl:attribute name="size">10</xsl:attribute>
									
									<xsl:choose>
										<xsl:when test="required = 'true'">
											<xsl:attribute name="class">dropdown required</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<xsl:attribute name="class">dropdown optional</xsl:attribute>
										</xsl:otherwise>
									</xsl:choose>
									
												<!-- A -->
												<!-- additional Comments -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComments=1">
                                            <option value="additionalComments" selected="1">{TRANSLATE:additional_comments_eval}</option>
                                        </xsl:when>
                                         <xsl:otherwise>
                                            <option value="additionalComments">{TRANSLATE:additional_comments_eval}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- analysis -->
                                    <xsl:choose>
                                        <xsl:when test="analysis=1">
                                            <option value="analysis" selected="1">{TRANSLATE:analysis}</option>
                                        </xsl:when>
                                         <xsl:otherwise>
                                            <option value="analysis">{TRANSLATE:analysis}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- analysis author -->
                                    <xsl:choose>
                                        <xsl:when test="author=1">
                                            <option value="author" selected="1">{TRANSLATE:analysis_author}</option>
                                        </xsl:when>
                                         <xsl:otherwise>
                                            <option value="author">{TRANSLATE:analysis_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- analysis date -->
                                    <xsl:choose>
                                        <xsl:when test="analysisDate=1">
                                            <option value="analysisDate" selected="1">{TRANSLATE:analysis_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="analysisDate">{TRANSLATE:analysis_date}</option>
                                        </xsl:otherwise>
                                     </xsl:choose>
                                    
												<!-- analysis yes no -->
                                    <xsl:choose>
                                        <xsl:when test="analysisyn=1">
                                            <option value="analysisyn" selected="1">{TRANSLATE:analysis_yes_no}</option>
                                        </xsl:when>
                                         <xsl:otherwise>
                                            <option value="analysisyn">{TRANSLATE:analysis_yes_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- attributable process -->
                                    <xsl:choose>
                                        <xsl:when test="attributableProcess=1">
                                            <option value="attributableProcess" selected="1">{TRANSLATE:attributable_process}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="attributableProcess">{TRANSLATE:attributable_process}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- author_for_goods_decision -->
                                    <xsl:choose>
                                        <xsl:when test="qu_authorGoodsDecision=1">
                                            <option value="qu_authorGoodsDecision" selected="1">{TRANSLATE:author_for_goods_decision}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_authorGoodsDecision">{TRANSLATE:author_for_goods_decision}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- author_for_goods_decision date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_authorGoodsDecisionDate=1">
                                            <option value="qu_authorGoodsDecisionDate" selected="1">{TRANSLATE:author_for_goods_decision_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_authorGoodsDecisionDate">{TRANSLATE:author_for_goods_decision_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
												<!-- B -->
												<!-- batch number -->
                                    <xsl:choose>
                                        <xsl:when test="batchNumber=1">
                                            <option value="batchNumber" selected="1">{TRANSLATE:batch_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="batchNumber">{TRANSLATE:batch_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
												<!-- C -->
												<!-- category -->
                                    <xsl:choose>
                                        <xsl:when test="Category=1">
                                            <option value="Category" selected="1">{TRANSLATE:category}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="Category">{TRANSLATE:category}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- chosen complaint owner conclusion-->
                                    <xsl:choose>
                                        <xsl:when test="processOwner3=1">
                                            <option value="processOwner3" selected="1">{TRANSLATE:chosen_complaint_owner_conclusion}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="processOwner3">{TRANSLATE:chosen_complaint_owner_conclusion}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- chosen complaint owner complaint-->
                                    <xsl:choose>
                                        <xsl:when test="processOwner=1">
                                            <option value="processOwner" selected="1">{TRANSLATE:chosen_complaint_owner_complaint}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="processOwner">{TRANSLATE:chosen_complaint_owner_complaint}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- chosen complaint owner evaluation -->
                                    <xsl:choose>
                                        <xsl:when test="processOwner2=1">
                                            <option value="processOwner2" selected="1">{TRANSLATE:chosen_complaint_owner_evaluation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="processOwner2">{TRANSLATE:chosen_complaint_owner_evaluation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- clause effected -->
                                    <xsl:choose>
                                        <xsl:when test="clauseEffected=1">
                                            <option value="clauseEffected" selected="1">{TRANSLATE:clause_effected}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="clauseEffected">{TRANSLATE:clause_effected}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- clause effected -->
                                    <xsl:choose>
                                        <xsl:when test="customerComplaintDate=1">
                                            <option value="customerComplaintDate" selected="1">{TRANSLATE:customerComplaintDate}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerComplaintDate">{TRANSLATE:customerComplaintDate}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- colour -->
                                    <xsl:choose>
                                        <xsl:when test="colour=1">
                                            <option value="colour" selected="1">{TRANSLATE:colour}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="colour">{TRANSLATE:colour}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint costs -->
                                    <xsl:choose>
                                        <xsl:when test="qu_complaintCosts=1">
                                            <option value="qu_complaintCosts" selected="1">{TRANSLATE:complaint_costs}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_complaintCosts">{TRANSLATE:complaint_costs}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- complaint will be actioned -->
                                    <xsl:choose>
                                        <xsl:when test="qu_supplierIssueAction=1">
                                            <option value="qu_supplierIssueAction" selected="1">{TRANSLATE:complaint_will_be_actioned}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_supplierIssueAction">{TRANSLATE:complaint_will_be_actioned}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- comment on cost -->
                                    <xsl:choose>
                                        <xsl:when test="qu_commentOnCost=1">
                                            <option value="qu_commentOnCost" selected="1">{TRANSLATE:comment_on_cost}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_commentOnCost">{TRANSLATE:comment_on_cost}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action -->
                                    <xsl:choose>
                                        <xsl:when test="containment_action=1">
                                            <option value="containment_action" selected="1">{TRANSLATE:containment_action}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containment_action">{TRANSLATE:containment_action}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentAction_eval=1">
                                            <option value="containmentAction_eval" selected="1">{TRANSLATE:eval_containment_action}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containmentAction_eval">{TRANSLATE:eval_containment_action}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action author (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionAuthor_eval=1">
                                            <option value="containmentActionAuthor_eval" selected="1">{TRANSLATE:eval_containment_action_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containmentActionAuthor_eval">{TRANSLATE:eval_containment_action_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action date (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionDate_eval=1">
                                            <option value="containmentActionDate_eval" selected="1">{TRANSLATE:eval_containment_action_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containmentActionDate_eval">{TRANSLATE:eval_containment_action_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- containment action yes no (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionyn_eval=1">
                                            <option value="containmentActionyn_eval" selected="1">{TRANSLATE:eval_containment_action_yes_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="containmentActionyn_eval">{TRANSLATE:eval_containment_action_yes_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer approved -->
                                    <xsl:choose>
                                        <xsl:when test="qu_customerApproved=1">
                                            <option value="qu_customerApproved" selected="1">{TRANSLATE:customer_approved}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_customerApproved">{TRANSLATE:customer_approved}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer specification -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecification=1">
                                            <option value="customerSpecification" selected="1">{TRANSLATE:customer_specification}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecification">{TRANSLATE:customer_specification}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer specification date -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationDate=1">
                                            <option value="customerSpecificationDate" selected="1">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecificationDate">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- customer specification ref -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationRef=1">
                                            <option value="customerSpecificationRef" selected="1">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="customerSpecificationRef">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
                                    <!-- D -->
												<!-- date_of_manufacturing -->
                                    <xsl:choose>
                                        <xsl:when test="dateOfManufacturing=1">
                                            <option value="dateOfManufacturing" selected="1">{TRANSLATE:date_of_manufacturing}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dateOfManufacturing">{TRANSLATE:date_of_manufacturing}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- details -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherMatDetails=1">
                                            <option value="qu_otherMatDetails" selected="1">{TRANSLATE:other_material_details}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_otherMatDetails">{TRANSLATE:other_material_details}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal authorised -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorised=1">
                                            <option value="qu_disposalAuthorised" selected="1">{TRANSLATE:disposal_authorised}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalAuthorised">{TRANSLATE:disposal_authorised}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal authorised comment -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedComment=1">
                                            <option value="qu_disposalAuthorisedComment" selected="1">{TRANSLATE:disposal_authorised_comment}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalAuthorisedComment">{TRANSLATE:disposal_authorised_comment}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal authorised date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedDate=1">
                                            <option value="qu_disposalAuthorisedDate" selected="1">{TRANSLATE:disposal_authorised_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalAuthorisedDate">{TRANSLATE:disposal_authorised_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal authorised name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedName=1">
                                            <option value="disposalAuthorisedName" selected="1">{TRANSLATE:disposal_authorised_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="disposalAuthorisedName">{TRANSLATE:disposal_authorised_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal booked -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBooked=1">
                                            <option value="qu_disposalBooked" selected="1">{TRANSLATE:disposal_booked}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalBooked">{TRANSLATE:disposal_booked}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal booked name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBookedName=1">
                                            <option value="qu_disposalBookedName" selected="1">{TRANSLATE:disposal_booked_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalBookedName">{TRANSLATE:disposal_booked_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal booked date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBookedDate=1">
                                            <option value="qu_disposalBookedDate" selected="1">{TRANSLATE:disposal_booked_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalBookedDate">{TRANSLATE:disposal_booked_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal code -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalCode=1">
                                            <option value="qu_disposalCode" selected="1">{TRANSLATE:disposal_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalCode">{TRANSLATE:disposal_code}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal cost centre -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalCostCentre=1">
                                            <option value="qu_disposalCostCentre" selected="1">{TRANSLATE:disposal_cost_centre}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalCostCentre">{TRANSLATE:disposal_cost_centre}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal physically done -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDone=1">
                                            <option value="qu_disposalPhysicallyDone" selected="1">{TRANSLATE:disposal_physically_done}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalPhysicallyDone">{TRANSLATE:disposal_physically_done}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal physically done date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDoneDate=1">
                                            <option value="qu_disposalPhysicallyDoneDate" selected="1">{TRANSLATE:disposal_physically_done_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalPhysicallyDoneDate">{TRANSLATE:disposal_physically_done_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- disposal physically done name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDoneName=1">
                                            <option value="qu_disposalPhysicallyDoneName" selected="1">{TRANSLATE:disposal_physically_done_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_disposalPhysicallyDoneName">{TRANSLATE:disposal_physically_done_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- dispose goods -->
                                    <xsl:choose>
                                        <xsl:when test="disposeGoods=1">
                                            <option value="disposeGoods" selected="1">{TRANSLATE:dispose_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="disposeGoods">{TRANSLATE:dispose_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- E -->
												<!-- email text -->
                                    <xsl:choose>
                                        <xsl:when test="emailText=1">
                                            <option value="emailText" selected="1">{TRANSLATE:email_text_evaluation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="emailText">{TRANSLATE:email_text_evaluation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
												
												<!-- F -->
												<!-- failure code -->
                                    <xsl:choose>
                                        <xsl:when test="failureCode=1">
                                            <option value="failureCode" selected="1">{TRANSLATE:failure_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="failureCode">{TRANSLATE:failure_code}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- final comments -->
                                    <xsl:choose>
                                        <xsl:when test="finalComments=1">
                                            <option value="finalComments" selected="1">{TRANSLATE:final_comments}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="finalComments">{TRANSLATE:final_comments}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- found by -->
                                    <xsl:choose>
                                        <xsl:when test="qu_foundBy=1">
                                            <option value="qu_foundBy" selected="1">{TRANSLATE:found_by}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_foundBy">{TRANSLATE:found_by}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- flowChart -->
                                    <xsl:choose>
                                        <xsl:when test="flowChart=1">
                                            <option value="flowChart" selected="1">{TRANSLATE:flowChart}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="flowChart">{TRANSLATE:flowChart}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- flowChart Date -->
                                    <xsl:choose>
                                        <xsl:when test="flowChartDate=1">
                                            <option value="flowChartDate" selected="1">{TRANSLATE:flowChart_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="flowChartDate">{TRANSLATE:flowChart_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- flowChart Ref-->
                                    <xsl:choose>
                                        <xsl:when test="flowChartRef=1">
                                            <option value="flowChartRef" selected="1">{TRANSLATE:flowChart_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="flowChartRef">{TRANSLATE:flowChart_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- fmea -->
                                    <xsl:choose>
                                        <xsl:when test="fmea=1">
                                            <option value="fmea" selected="1">{TRANSLATE:fmea}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmea">{TRANSLATE:fmea}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- fmea date -->
                                    <xsl:choose>
                                        <xsl:when test="fmeaDate=1">
                                            <option value="fmeaDate" selected="1">{TRANSLATE:fmea_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmeaDate">{TRANSLATE:fmea_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- fmea ref -->
                                    <xsl:choose>
                                        <xsl:when test="fmeaRef=1">
                                            <option value="fmeaRef" selected="1">{TRANSLATE:fmea_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="fmeaRef">{TRANSLATE:fmea_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- G -->
                                    <!-- grouped complaint -->
												<xsl:choose>
		                                  <xsl:when test="groupAComplaint=1">
		                                      <option value="groupAComplaint" selected="1">{TRANSLATE:group_a_complaint}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="groupAComplaint">{TRANSLATE:group_a_complaint}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- grouped complaint id -->
												<xsl:choose>
		                                  <xsl:when test="groupedComplaintId=1">
		                                      <option value="groupedComplaintId" selected="1">{TRANSLATE:grouped_complaint_id}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="groupedComplaintId">{TRANSLATE:grouped_complaint_id}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												
                                    <!-- H -->
												<!-- how was error detected? -->
                                    <xsl:choose>
                                        <xsl:when test="how_was_error_detected=1">
                                            <option value="how_was_error_detected" selected="1">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="how_was_error_detected">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- I -->
												<!-- implemented actions -->
												<xsl:choose>
		                                  <xsl:when test="implementedActions=1">
		                                      <option value="implementedActions" selected="1">{TRANSLATE:implemented_actions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActions">{TRANSLATE:implemented_actions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_author -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsAuthor=1">
		                                      <option value="implementedActionsAuthor" selected="1">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsAuthor">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_date -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsDate=1">
		                                      <option value="implementedActionsDate" selected="1">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsDate">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_effectiveness -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsEffectiveness=1">
		                                      <option value="implementedActionsEffectiveness" selected="1">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsEffectiveness">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_estimated -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsEstimated=1">
		                                      <option value="implementedActionsEstimated" selected="1">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsEstimated">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_implementation -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsImplemetation=1">
		                                      <option value="implementedActionsImplemetation" selected="1">{TRANSLATE:implemented_actions_implemetation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsImplemetation">{TRANSLATE:implemented_actions_implemetation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- implemented_actions_yes_no -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsyn=1">
		                                      <option value="implementedActionsImplemetation" selected="1">{TRANSLATE:implemented_actions_yes_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="implementedActionsyn">{TRANSLATE:implemented_actions_yes_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- Internal complaint status -->
												<xsl:choose>
		                                  <xsl:when test="internalComplaintStatus=1">
		                                      <option value="internalComplaintStatus" selected="1">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internalComplaintStatus">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- Internal Reference Number -->
												<xsl:choose>
		                                  <xsl:when test="internalReferenceNumber=1">
		                                      <option value="internalReferenceNumber" selected="1">{TRANSLATE:internal_reference_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="internalReferenceNumber">{TRANSLATE:internal_reference_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												<!-- issue supplier complaint -->
												<xsl:choose>
		                                  <xsl:when test="qu_supplierIssue=1">
		                                      <option value="qu_supplierIssue" selected="1">{TRANSLATE:supplier_issue}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_supplierIssue">{TRANSLATE:supplier_issue}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

												
                                    <!-- J -->
												
												
                                    <!-- K -->
												
												
                                    <!-- L -->
												<!-- length -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionLength=1">
                                            <option value="dimensionLength" selected="1">{TRANSLATE:length}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dimensionLength">{TRANSLATE:length}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- line stoppage -->
                                    <xsl:choose>
                                        <xsl:when test="lineStoppage=1">
                                            <option value="lineStoppage" selected="1">{TRANSLATE:line_stoppage}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="lineStoppage">{TRANSLATE:line_stoppage}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- lot no -->
                                    <xsl:choose>
                                        <xsl:when test="lotNo=1">
                                            <option value="lotNo" selected="1">{TRANSLATE:lot_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="lotNo">{TRANSLATE:lot_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    
												<!-- M -->
												<!--  management_system_review -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReview=1">
                                            <option value="managementSystemReview" selected="1">{TRANSLATE:management_system_review}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="managementSystemReview">{TRANSLATE:management_system_review}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- management_system_review_date -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReviewDate=1">
                                            <option value="managementSystemReviewDate" selected="1">{TRANSLATE:management_system_review_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="managementSystemReviewDate">{TRANSLATE:management_system_review_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- management_system_review_ref -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReviewRef=1">
                                            <option value="managementSystemReviewRef" selected="1">{TRANSLATE:management_system_review_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="managementSystemReviewRef">{TRANSLATE:management_system_review_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- manufacturing number -->
                                    <xsl:choose>
                                        <xsl:when test="manufacturingNumber=1">
                                            <option value="manufacturingNumber" selected="1">{TRANSLATE:manufacturing_number}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="manufacturingNumber">{TRANSLATE:manufacturing_number}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                               		<!-- material blocked -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialBlocked=1">
                                            <option value="qu_materialBlocked" selected="1">{TRANSLATE:material_blocked}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialBlocked">{TRANSLATE:material_blocked}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material blocked date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialBlockedDate=1">
                                            <option value="qu_materialBlockedDate" selected="1">{TRANSLATE:material_blocked_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialBlockedDate">{TRANSLATE:material_blocked_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material blocked name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialBlockedName=1">
                                            <option value="qu_materialBlockedName" selected="1">{TRANSLATE:material_blocked_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialBlockedName">{TRANSLATE:material_blocked_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material groups -->
                                    <xsl:choose>
                                        <xsl:when test="sapMaterialGroups=1">
                                            <option value="sapMaterialGroups" selected="1">{TRANSLATE:sap_material_groups}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sapMaterialGroups">{TRANSLATE:sap_material_groups}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material involved -->
                                    <xsl:choose>
                                        <xsl:when test="materialInvolved=1">
                                            <option value="materialInvolved" selected="1">{TRANSLATE:material_involved}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="materialInvolved">{TRANSLATE:material_involved}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material location -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialLocation=1">
		                                      <option value="qu_materialLocation" selected="1">{TRANSLATE:material_location}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialLocation">{TRANSLATE:material_location}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- material returned to customer -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomer=1">
                                            <option value="qu_materialReturnedToCustomer" selected="1">{TRANSLATE:material_returned_to_customer}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialReturnedToCustomer">{TRANSLATE:material_returned_to_customer}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material returned to customer date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomerDate=1">
                                            <option value="qu_materialReturnedToCustomerDate" selected="1">{TRANSLATE:material_returned_to_customer_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialReturnedToCustomerDate">{TRANSLATE:material_returned_to_customer_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material returned to customer name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomerName=1">
                                            <option value="qu_materialReturnedToCustomerName" selected="1">{TRANSLATE:material_returned_to_customer_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialReturnedToCustomerName">{TRANSLATE:material_returned_to_customer_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- material unblocked -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlocked=1">
		                                      <option value="qu_materialUnBlocked" selected="1">{TRANSLATE:material_unblocked}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialUnBlocked">{TRANSLATE:material_unblocked}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- material unblocked date -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlockedDate=1">
		                                      <option value="qu_materialUnBlockedDate" selected="1">{TRANSLATE:material_unblocked_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialUnBlockedDate">{TRANSLATE:material_unblocked_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    <!-- material unblocked name -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlockedName=1">
		                                      <option value="qu_materialUnBlockedName" selected="1">{TRANSLATE:material_unblocked_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_materialUnBlockedName">{TRANSLATE:material_unblocked_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    
												<!-- N -->
												<!-- material location -->
												<xsl:choose>
		                                  <xsl:when test="qu_nameOfCustomer=1">
		                                      <option value="qu_nameOfCustomer" selected="1">{TRANSLATE:name_of_customer}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_nameOfCustomer">{TRANSLATE:name_of_customer}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>

                                    
												<!-- O -->
												<!-- others -->
                                    <xsl:choose>
                                        <xsl:when test="others=1">
                                            <option value="others" selected="1">{TRANSLATE:others}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="others">{TRANSLATE:others}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- other material affected -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherMaterialEffected=1">
                                            <option value="qu_otherMaterialEffected" selected="1">{TRANSLATE:other_material_effected}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_otherMaterialEffected">{TRANSLATE:other_material_effected}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- other_similar_products_recalled -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherSimilarProducts=1">
                                            <option value="qu_otherSimilarProducts" selected="1">{TRANSLATE:other_similar_products_recalled}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_otherSimilarProducts">{TRANSLATE:other_similar_products_recalled}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- owner -->
                                    <xsl:choose>
                                        <xsl:when test="owner=1">
                                            <option value="owner" selected="1">{TRANSLATE:owner}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="owner">{TRANSLATE:owner}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- P -->
												<!-- possible solutions -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutions=1">
                                            <option value="possibleSolutions" selected="1">{TRANSLATE:possible_solutions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possibleSolutions">{TRANSLATE:possible_solutions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- possible solutions author -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutionsAuthor=1">
                                            <option value="possibleSolutionsAuthor" selected="1">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possibleSolutionsAuthor">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- possible solutions date -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutionsDate=1">
                                            <option value="possibleSolutionsDate" selected="1">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="possibleSolutionsDate">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActions=1">
                                            <option value="preventiveActions" selected="1">{TRANSLATE:preventive_actions}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActions">{TRANSLATE:preventive_actions}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions author -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsAuthor=1">
                                            <option value="preventiveActionsAuthor" selected="1">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsAuthor">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions date -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsDate=1">
                                            <option value="preventiveActionsDate" selected="1">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsDate">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions estimated date-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsEstimatedDate=1">
                                            <option value="preventiveActionsEstimatedDate" selected="1">{TRANSLATE:preventive_actions_estimated_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsEstimatedDate">{TRANSLATE:preventive_actions_estimated_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions implemented date -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsImplementedDate=1">
                                            <option value="preventiveActionsImplementedDate" selected="1">{TRANSLATE:preventive_actions_implemented_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsImplementedDate">{TRANSLATE:preventive_actions_implemented_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions validation date-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsValidationDate=1">
                                            <option value="preventiveActionsValidationDate" selected="1">{TRANSLATE:preventive_actions_validation_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsValidationDate">{TRANSLATE:preventive_actions_validation_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions yes no-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsyn=1">
                                            <option value="preventiveActionsyn" selected="1">{TRANSLATE:preventive_actions_yes_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="preventiveActionsyn">{TRANSLATE:preventive_actions_yes_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <xsl:choose>
					<xsl:when test="problem_description=1">
						<option value="problem_description" selected="1">{TRANSLATE:problem_description}</option>
					</xsl:when>
					<xsl:otherwise>
						<option value="problem_description">{TRANSLATE:problem_description}</option>
					</xsl:otherwise>
				    </xsl:choose>
                                    
                             			<!-- product descriptions -->
                                    <xsl:choose>
                                        <xsl:when test="productDescription=1">
                                            <option value="productDescription" selected="1">{TRANSLATE:product_description}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="productDescription">{TRANSLATE:product_description}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- Q -->
												<!-- product descriptions -->
                                    <xsl:choose>
                                        <xsl:when test="quantityUnderComplaint=1">
                                            <option value="quantityUnderComplaint" selected="1">{TRANSLATE:quantity_under_complaint}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="quantityUnderComplaint">{TRANSLATE:quantity_under_complaint}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- R -->
												<!-- request action -->
                                    <xsl:choose>
		                                  <xsl:when test="requestedAction=1">
		                                      <option value="requestedAction" selected="1">{TRANSLATE:requested_action}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="requestedAction">{TRANSLATE:requested_action}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- request for disposal -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposal=1">
		                                      <option value="qu_requestForDisposal" selected="1">{TRANSLATE:request_for_disposal}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_requestForDisposal">{TRANSLATE:request_for_disposal}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- request for disposal amount-->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposalAmount=1">
		                                      <option value="qu_requestForDisposalAmount" selected="1">{TRANSLATE:request_for_disposal_amount}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_requestForDisposalAmount">{TRANSLATE:request_for_disposal_amount}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- request for disposal date -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposalDate=1">
		                                      <option value="qu_requestForDisposalDate" selected="1">{TRANSLATE:request_for_disposal_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="requestForDisposalDate">{TRANSLATE:request_for_disposal_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- request for disposal name -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestDisposalName=1">
		                                      <option value="qu_requestDisposalName" selected="1">{TRANSLATE:request_for_disposal_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_requestDisposalName">{TRANSLATE:request_for_disposal_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- rework the goods -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_reworkTheGoods=1">
		                                      <option value="qu_reworkTheGoods" selected="1">{TRANSLATE:rework_the_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_reworkTheGoods">{TRANSLATE:rework_the_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- risk assessment -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessment=1">
		                                      <option value="riskAssessment" selected="1">{TRANSLATE:risk_assessment}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="riskAssessment">{TRANSLATE:risk_assessment}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- risk assessment date -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessmentDate=1">
		                                      <option value="riskAssessmentDate" selected="1">{TRANSLATE:risk_assessment_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="riskAssessmentDate">{TRANSLATE:risk_assessment_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- risk assessment ref -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessmentRef=1">
		                                      <option value="riskAssessmentRef" selected="1">{TRANSLATE:risk_assessment_ref}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="riskAssessmentRef">{TRANSLATE:risk_assessment_ref}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- root causes -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCauses=1">
		                                      <option value="rootCauses" selected="1">{TRANSLATE:root_causes}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="rootCauses">{TRANSLATE:root_causes}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- root causes author-->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesAuthor=1">
		                                      <option value="rootCausesAuthor" selected="1">{TRANSLATE:root_causes_author}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="rootCausesAuthor">{TRANSLATE:root_causes_author}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- root cause code-->
                                    <xsl:choose>
		                                  <xsl:when test="rootCauseCode=1">
		                                      <option value="rootCauseCode" selected="1">{TRANSLATE:root_cause_code}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="rootCauseCode">{TRANSLATE:root_cause_code}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- root causes date -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesDate=1">
		                                      <option value="rootCausesDate" selected="1">{TRANSLATE:root_causes_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="rootCausesDate">{TRANSLATE:root_causes_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												<!-- root causes yes no -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesyn=1">
		                                      <option value="rootCausesyn" selected="1">{TRANSLATE:root_causes_yes_no}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="rootCausesyn">{TRANSLATE:root_causes_yes_no}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
						
												
                                    <!-- S -->
												<!-- sales office -->
                                    <xsl:choose>
                                        <xsl:when test="salesOffice=1">
                                            <option value="salesOffice" selected="1">{TRANSLATE:sales_office}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="salesOffice">{TRANSLATE:sales_office}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- sap item numbers -->
                                    <xsl:choose>
                                        <xsl:when test="sapItemNumbers=1">
                                            <option value="sapItemNumbers" selected="1">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sapItemNumbers">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- severity -->
                                    <xsl:choose>
                                        <xsl:when test="severity=1">
                                            <option value="severity" selected="1">{TRANSLATE:severity}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="severity">{TRANSLATE:severity}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- site concerned -->
                                    <xsl:choose>
                                        <xsl:when test="sp_siteConcerned=1">
                                            <option value="sp_siteConcerned" selected="1">{TRANSLATE:site_concerned}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="sp_siteConcerned">{TRANSLATE:site_concerned}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- T -->
												<!-- team leader -->
                                    <xsl:choose>
                                        <xsl:when test="teamLeader=1">
                                            <option value="teamLeader" selected="1">{TRANSLATE:team_leader}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="teamLeader">{TRANSLATE:team_leader}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- team member -->
                                    <xsl:choose>
                                        <xsl:when test="teamMember=1">
                                            <option value="teamMember" selected="1">{TRANSLATE:team_member}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="teamMember">{TRANSLATE:team_member}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- thickness -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionThickness=1">
                                            <option value="dimensionThickness" selected="1">{TRANSLATE:thickness}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dimensionThickness">{TRANSLATE:thickness}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- thickness -->
                                    <xsl:choose>
                                        <xsl:when test="totalClosureDate=1">
                                            <option value="totalClosureDate" selected="1">{TRANSLATE:total_closure_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="totalClosureDate">{TRANSLATE:total_closure_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
                                    <!-- U -->
												<!-- use goods -->
                                    <xsl:choose>
                                        <xsl:when test="qu_useGoods=1">
                                            <option value="qu_useGoods" selected="1">{TRANSLATE:use_goods}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_useGoods">{TRANSLATE:use_goods}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- Use Goods with Customer Derongation -->
                                    <xsl:choose>
                                        <xsl:when test="qu_useGoodsDerongation=1">
                                            <option value="qu_useGoodsDerongation" selected="1">{TRANSLATE:use_goods_derongation}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_useGoodsDerongation">{TRANSLATE:use_goods_derongation}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
												<!-- V -->
												<!-- verification date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationDate=1">
                                            <option value="qu_verificationDate" selected="1">{TRANSLATE:verification_date}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_verificationDate">{TRANSLATE:verification_date}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- verification made -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationMade=1">
                                            <option value="qu_verificationMade" selected="1">{TRANSLATE:verification_made}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_verificationMade">{TRANSLATE:verification_made}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												<!-- verification name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationName=1">
                                            <option value="qu_verificationName" selected="1">{TRANSLATE:verification_name}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_verificationName">{TRANSLATE:verification_name}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
												
												<!-- W -->
												<!-- weight of material -->
                                    <xsl:choose>
                                        <xsl:when test="qu_weightOfMaterial=1">
                                            <option value="qu_weightOfMaterial" selected="1">{TRANSLATE:weight_of_material}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="qu_weightOfMaterial">{TRANSLATE:weight_of_material}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Where error Detected -->
                                    <xsl:choose>
                                        <xsl:when test="whereErrorOccured=1">
                                            <option value="whereErrorOccured" selected="1">{TRANSLATE:where_error_occured}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="whereErrorOccured">{TRANSLATE:where_error_occured}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                    
                                    <!-- Width -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionWidth=1">
                                            <option value="dimensionWidth" selected="1">{TRANSLATE:width}</option>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <option value="dimensionWidth">{TRANSLATE:width}</option>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                                                        
                                    
                                    <!-- X -->
												
                                    
                                    <!-- Y -->
												
                                    
                                    <!-- Z -->
												
									
                                    
                                    
                                    
                                    
                                    
									
											   
                           			
                                    
								</xsl:element>
							</td>
							
							<td width="2%">
							</td>
							
							<td> 
								<input type="button" name="moveRight" value="&gt;&gt;" onClick="Javascript: moveQualitySelectionRight();" /> 
								<br />
								<br />
								<input type="button" name="moveLeft" value="&lt;&lt;" onClick="Javascript: moveQualitySelectionLeft();" /> 
							</td>
							
							<td width="2%">
							</td>
			
							<td valign="top">
								<xsl:element name="select">
									<xsl:attribute name="name">columnsQuality[]</xsl:attribute>
									<xsl:attribute name="id">columnsQuality</xsl:attribute>
									<xsl:attribute name="multiple">true</xsl:attribute>
									<xsl:attribute name="size">10</xsl:attribute>
									<xsl:attribute name="class">dropdown required</xsl:attribute>
												
												
																					<!-- A -->
												<!-- additional comments -->
                                    <xsl:choose>
                                        <xsl:when test="additionalComments=1">
                                            <option value="additionalComments">{TRANSLATE:additional_comments_eval}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- analysis -->
                                    <xsl:choose>
                                        <xsl:when test="analysis=1">
                                            <option value="analysis">{TRANSLATE:analysis}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- analysis author -->
                                    <xsl:choose>
                                        <xsl:when test="author=1">
                                            <option value="author">{TRANSLATE:analysis_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- analysis date -->
                                    <xsl:choose>
                                        <xsl:when test="analysisDate=1">
                                            <option value="analysisDate">{TRANSLATE:analysis_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- analysis yes no -->
                                    <xsl:choose>
                                        <xsl:when test="analysisyn=1">
                                            <option value="analysis">{TRANSLATE:analysis_yes_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- attributable process -->
                                    <xsl:choose>
                                        <xsl:when test="attributableProcess=1">
                                            <option value="attributableProcess">{TRANSLATE:attributable_process}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- author_for_goods_decision -->
                                    <xsl:choose>
                                        <xsl:when test="qu_authorGoodsDecision=1">
                                            <option value="qu_authorGoodsDecision">{TRANSLATE:author_for_goods_decision}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- author_for_goods_decision date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_authorGoodsDecisionDate=1">
                                            <option value="qu_authorGoodsDecisionDate">{TRANSLATE:author_for_goods_decision_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
												<!-- B -->
												<!-- batch number -->
                                    <xsl:choose>
                                        <xsl:when test="batchNumber=1">
                                            <option value="batchNumber">{TRANSLATE:batch_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
												<!-- C -->
												<!-- category -->
                                    <xsl:choose>
                                        <xsl:when test="Category=1">
                                            <option value="Category">{TRANSLATE:category}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- chosen complaint owner conclusion-->
                                    <xsl:choose>
                                        <xsl:when test="processOwner3=1">
                                            <option value="processOwner3">{TRANSLATE:chosen_complaint_owner_conclusion}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- chosen complaint owner complaint-->
                                    <xsl:choose>
                                        <xsl:when test="processOwner=1">
                                            <option value="processOwner">{TRANSLATE:chosen_complaint_owner_complaint}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- chosen complaint owner evaluation -->
                                    <xsl:choose>
                                        <xsl:when test="processOwner2=1">
                                            <option value="processOwner2">{TRANSLATE:chosen_complaint_owner_evaluation}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- clause effected -->
                                    <xsl:choose>
                                        <xsl:when test="clauseEffected=1">
                                            <option value="clauseEffected">{TRANSLATE:clause_effected}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- clause effected -->
                                    <xsl:choose>
                                        <xsl:when test="customerComplaintDate=1">
                                            <option value="customerComplaintDate">{TRANSLATE:customerComplaintDate}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- colour -->
                                    <xsl:choose>
                                        <xsl:when test="colour=1">
                                            <option value="colour">{TRANSLATE:colour}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- complaint costs -->
                                    <xsl:choose>
                                        <xsl:when test="qu_complaintCosts=1">
                                            <option value="qu_complaintCosts">{TRANSLATE:complaint_costs}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- complaint will be actioned -->
                                    <xsl:choose>
                                        <xsl:when test="qu_supplierIssueAction=1">
                                            <option value="qu_supplierIssueAction">{TRANSLATE:complaint_will_be_actioned}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- comment on cost -->
                                    <xsl:choose>
                                        <xsl:when test="qu_commentOnCost=1">
                                            <option value="qu_commentOnCost">{TRANSLATE:comment_on_cost}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action -->
                                    <xsl:choose>
                                        <xsl:when test="containment_action=1">
                                            <option value="containment_action">{TRANSLATE:containment_action}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentAction_eval=1">
                                            <option value="containmentAction_eval">{TRANSLATE:eval_containment_action}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action author (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionAuthor_eval=1">
                                            <option value="containmentActionAuthor_eval">{TRANSLATE:eval_containment_action_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action date (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionDate_eval=1">
                                            <option value="containmentActionDate_eval">{TRANSLATE:eval_containment_action_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- containment action yes no (eval)-->
                                    <xsl:choose>
                                        <xsl:when test="containmentActionyn_eval=1">
                                            <option value="containmentActionyn_eval">{TRANSLATE:eval_containment_action_yes_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer approved -->
                                    <xsl:choose>
                                        <xsl:when test="qu_customerApproved=1">
                                            <option value="qu_customerApproved">{TRANSLATE:customer_approved}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer specification -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecification=1">
                                            <option value="customerSpecification">{TRANSLATE:customer_specification}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer specification date -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationDate=1">
                                            <option value="customerSpecificationDate">{TRANSLATE:customer_specification_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- customer specification ref -->
                                    <xsl:choose>
                                        <xsl:when test="customerSpecificationRef=1">
                                            <option value="customerSpecificationRef">{TRANSLATE:customer_specification_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    <!-- D -->
												<!-- date_of_manufacturing -->
                                    <xsl:choose>
                                        <xsl:when test="dateOfManufacturing=1">
                                            <option value="dateOfManufacturing">{TRANSLATE:date_of_manufacturing}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- details -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherMatDetails=1">
                                            <option value="qu_otherMatDetails">{TRANSLATE:other_material_details}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal authorised -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorised=1">
                                            <option value="qu_disposalAuthorised">{TRANSLATE:disposal_authorised}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal authorised comment -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedComment=1">
                                            <option value="qu_disposalAuthorisedComment">{TRANSLATE:disposal_authorised_comment}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal authorised date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedDate=1">
                                            <option value="qu_disposalAuthorisedDate">{TRANSLATE:disposal_authorised_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal authorised name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalAuthorisedName=1">
                                            <option value="disposalAuthorisedName">{TRANSLATE:disposal_authorised_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal booked -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBooked=1">
                                            <option value="qu_disposalBooked">{TRANSLATE:disposal_booked}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal booked name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBookedName=1">
                                            <option value="qu_disposalBookedName">{TRANSLATE:disposal_booked_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal booked date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalBookedDate=1">
                                            <option value="qu_disposalBookedDate">{TRANSLATE:disposal_booked_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal code -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalCode=1">
                                            <option value="qu_disposalCode">{TRANSLATE:disposal_code}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal cost centre -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalCostCentre=1">
                                            <option value="qu_disposalCostCentre">{TRANSLATE:disposal_cost_centre}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal physically done -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDone=1">
                                            <option value="qu_disposalPhysicallyDone">{TRANSLATE:disposal_physically_done}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal physically done date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDoneDate=1">
                                            <option value="qu_disposalPhysicallyDoneDate">{TRANSLATE:disposal_physically_done_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- disposal physically done name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_disposalPhysicallyDoneName=1">
                                            <option value="qu_disposalPhysicallyDoneName">{TRANSLATE:disposal_physically_done_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- dispose goods -->
                                    <xsl:choose>
                                        <xsl:when test="disposeGoods=1">
                                            <option value="disposeGoods">{TRANSLATE:dispose_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- E -->
												<!-- email text -->
                                    <xsl:choose>
                                        <xsl:when test="emailText=1">
                                            <option value="emailText">{TRANSLATE:email_text_evaluation}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
												
												<!-- F -->
												<!-- failure code -->
                                    <xsl:choose>
                                        <xsl:when test="failureCode=1">
                                            <option value="failureCode">{TRANSLATE:failure_code}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- final comments -->
                                    <xsl:choose>
                                        <xsl:when test="finalComments=1">
                                            <option value="finalComments">{TRANSLATE:final_comments}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- found by -->
                                    <xsl:choose>
                                        <xsl:when test="qu_foundBy=1">
                                            <option value="qu_foundBy">{TRANSLATE:found_by}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- flowChart -->
                                    <xsl:choose>
                                        <xsl:when test="flowChart=1">
                                            <option value="flowChart">{TRANSLATE:flowChart}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- flowChart Date -->
                                    <xsl:choose>
                                        <xsl:when test="flowChartDate=1">
                                            <option value="flowChartDate">{TRANSLATE:flowChart_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- flowChart Ref-->
                                    <xsl:choose>
                                        <xsl:when test="flowChartRef=1">
                                            <option value="flowChartRef">{TRANSLATE:flowChart_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- fmea -->
                                    <xsl:choose>
                                        <xsl:when test="fmea=1">
                                            <option value="fmea">{TRANSLATE:fmea}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- fmea date -->
                                    <xsl:choose>
                                        <xsl:when test="fmeaDate=1">
                                            <option value="fmeaDate">{TRANSLATE:fmea_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- fmea ref -->
                                    <xsl:choose>
                                        <xsl:when test="fmeaRef=1">
                                            <option value="fmeaRef">{TRANSLATE:fmea_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- G -->
												<!-- grouped complaint -->
												<xsl:choose>
		                                  <xsl:when test="groupAComplaint=1">
		                                      <option value="groupAComplaint">{TRANSLATE:group_a_complaint}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- grouped complaint id -->
												<xsl:choose>
		                                  <xsl:when test="groupedComplaintId=1">
		                                      <option value="groupedComplaintId">{TRANSLATE:grouped_complaint_id}</option>
                                        </xsl:when>
                                    </xsl:choose>

												
                                    <!-- H -->
												<!-- how was error detected? -->
                                    <xsl:choose>
                                        <xsl:when test="how_was_error_detected=1">
                                            <option value="how_was_error_detected">{TRANSLATE:how_was_error_detected}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- I -->
												<!-- implemented actions -->
												<xsl:choose>
		                                  <xsl:when test="implementedActions=1">
		                                      <option value="implementedActions">{TRANSLATE:implemented_actions}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_author -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsAuthor=1">
		                                      <option value="implementedActionsAuthor">{TRANSLATE:implemented_actions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_date -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsDate=1">
		                                      <option value="implementedActionsDate">{TRANSLATE:implemented_actions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_effectiveness -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsEffectiveness=1">
		                                      <option value="implementedActionsEffectiveness">{TRANSLATE:implemented_actions_effectiveness}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_estimated -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsEstimated=1">
		                                      <option value="implementedActionsEstimated">{TRANSLATE:implemented_actions_estimated}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_implementation -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsImplemetation=1">
		                                      <option value="implementedActionsImplemetation">{TRANSLATE:implemented_actions_implemetation}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- implemented_actions_yes_no -->
												<xsl:choose>
		                                  <xsl:when test="implementedActionsyn=1">
		                                      <option value="implementedActionsImplemetation">{TRANSLATE:implemented_actions_yes_no}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- Internal complaint status -->
												<xsl:choose>
		                                  <xsl:when test="internalComplaintStatus=1">
		                                      <option value="internalComplaintStatus">{TRANSLATE:internal_complaint_status}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- Internal Reference Number -->
												<xsl:choose>
		                                  <xsl:when test="internalReferenceNumber=1">
		                                      <option value="internalReferenceNumber">{TRANSLATE:internal_reference_number}</option>
                                        </xsl:when>
                                    </xsl:choose>

												<!-- issue supplier complaint -->
												<xsl:choose>
		                                  <xsl:when test="qu_supplierIssue=1">
		                                      <option value="qu_supplierIssue">{TRANSLATE:supplier_issue}</option>
                                        </xsl:when>
                                    </xsl:choose>

												
                                    <!-- J -->
												
												
                                    <!-- K -->
												
												
                                    <!-- L -->
												<!-- length -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionLength=1">
                                            <option value="dimensionLength">{TRANSLATE:length}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- line stoppage -->
                                    <xsl:choose>
                                        <xsl:when test="lineStoppage=1">
                                            <option value="lineStoppage">{TRANSLATE:line_stoppage}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- lot no -->
                                    <xsl:choose>
                                        <xsl:when test="lotNo=1">
                                            <option value="lotNo">{TRANSLATE:lot_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
												<!-- M -->
												<!--  management_system_review -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReview=1">
                                            <option value="managementSystemReview">{TRANSLATE:management_system_review}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- management_system_review_date -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReviewDate=1">
                                            <option value="managementSystemReviewDate">{TRANSLATE:management_system_review_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- management_system_review_ref -->
                                    <xsl:choose>
                                        <xsl:when test="managementSystemReviewRef=1">
                                            <option value="managementSystemReviewRef">{TRANSLATE:management_system_review_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- manufacturing number -->
                                    <xsl:choose>
                                        <xsl:when test="manufacturingNumber=1">
                                            <option value="manufacturingNumber">{TRANSLATE:manufacturing_number}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                               		<!-- material blocked -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialBlocked=1">
                                            <option value="qu_materialBlocked">{TRANSLATE:material_blocked}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material groups -->
                                    <xsl:choose>
                                        <xsl:when test="sapMaterialGroups=1">
                                            <option value="sapMaterialGroups">{TRANSLATE:sap_material_groups}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material involved -->
                                    <xsl:choose>
                                        <xsl:when test="materialInvolved=1">
                                            <option value="materialInvolved">{TRANSLATE:material_involved}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material location -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialLocation=1">
		                                      <option value="qu_materialLocation">{TRANSLATE:material_location}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- material returned to customer -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomer=1">
                                            <option value="qu_materialReturnedToCustomer">{TRANSLATE:material_returned_to_customer}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material returned to customer date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomerDate=1">
                                            <option value="qu_materialReturnedToCustomerDate">{TRANSLATE:material_returned_to_customer_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material returned to customer name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_materialReturnedToCustomerName=1">
                                            <option value="qu_materialReturnedToCustomerName">{TRANSLATE:material_returned_to_customer_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- material unblocked -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlocked=1">
		                                      <option value="qu_materialUnBlocked">{TRANSLATE:material_unblocked}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- material unblocked date -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlockedDate=1">
		                                      <option value="qu_materialUnBlockedDate">{TRANSLATE:material_unblocked_date}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    <!-- material unblocked name -->
												<xsl:choose>
		                                  <xsl:when test="qu_materialUnBlockedName=1">
		                                      <option value="qu_materialUnBlockedName">{TRANSLATE:material_unblocked_name}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    
												<!-- N -->
												<!-- material location -->
												<xsl:choose>
		                                  <xsl:when test="qu_nameOfCustomer=1">
		                                      <option value="qu_nameOfCustomer">{TRANSLATE:name_of_customer}</option>
                                        </xsl:when>
                                    </xsl:choose>

                                    
												<!-- O -->
												<!-- others -->
                                    <xsl:choose>
                                        <xsl:when test="others=1">
                                            <option value="others">{TRANSLATE:others}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- other material affected -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherMaterialEffected=1">
                                            <option value="qu_otherMaterialEffected">{TRANSLATE:other_material_effected}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- other_similar_products_recalled -->
                                    <xsl:choose>
                                        <xsl:when test="qu_otherSimilarProducts=1">
                                            <option value="qu_otherSimilarProducts">{TRANSLATE:other_similar_products_recalled}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- owner -->
                                    <xsl:choose>
                                        <xsl:when test="owner=1">
                                            <option value="owner">{TRANSLATE:owner}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- P -->
												<!-- possible solutions -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutions=1">
                                            <option value="possibleSolutions">{TRANSLATE:possible_solutions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- possible solutions author -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutionsAuthor=1">
                                            <option value="possibleSolutionsAuthor">{TRANSLATE:possible_solutions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- possible solutions date -->
                                    <xsl:choose>
                                        <xsl:when test="possibleSolutionsDate=1">
                                            <option value="possibleSolutionsDate">{TRANSLATE:possible_solutions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActions=1">
                                            <option value="preventiveActions">{TRANSLATE:preventive_actions}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions author -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsAuthor=1">
                                            <option value="preventiveActionsAuthor">{TRANSLATE:preventive_actions_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions date -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsDate=1">
                                            <option value="preventiveActionsDate">{TRANSLATE:preventive_actions_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions estimated date-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsEstimatedDate=1">
                                            <option value="preventiveActionsEstimatedDate">{TRANSLATE:preventive_actions_estimated_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions implemented date -->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsImplementedDate=1">
                                            <option value="preventiveActionsImplementedDate">{TRANSLATE:preventive_actions_implemented_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions validation date-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsValidationDate=1">
                                            <option value="preventiveActionsValidationDate">{TRANSLATE:preventive_actions_validation_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- preventive actions yes no-->
                                    <xsl:choose>
                                        <xsl:when test="preventiveActionsyn=1">
                                            <option value="preventiveActionsyn">{TRANSLATE:preventive_actions_yes_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- problem description -->
                                    <xsl:choose>
                                        <xsl:when test="problem_description=1">
                                            <option value="problem_description">{TRANSLATE:problem_description}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                             			<!-- product descriptions -->
                                    <xsl:choose>
                                        <xsl:when test="productDescription=1">
                                            <option value="productDescription">{TRANSLATE:product_description}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- Q -->
												<!-- product descriptions -->
                                    <xsl:choose>
                                        <xsl:when test="quantityUnderComplaint=1">
                                            <option value="quantityUnderComplaint">{TRANSLATE:quantity_under_complaint}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- R -->
												<!-- request action -->
                                    <xsl:choose>
		                                  <xsl:when test="requestedAction=1">
		                                      <option value="requestedAction">{TRANSLATE:requested_action}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- request for disposal -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposal=1">
		                                      <option value="qu_requestForDisposal">{TRANSLATE:request_for_disposal}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- request for disposal amount-->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposalAmount=1">
		                                      <option value="qu_requestForDisposalAmount">{TRANSLATE:request_for_disposal_amount}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- request for disposal date -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestForDisposalDate=1">
		                                      <option value="qu_requestForDisposalDate">{TRANSLATE:request_for_disposal_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- request for disposal name -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_requestDisposalName=1">
		                                      <option value="qu_requestDisposalName">{TRANSLATE:request_for_disposal_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- rework the goods -->
                                    <xsl:choose>
		                                  <xsl:when test="qu_reworkTheGoods=1">
		                                      <option value="qu_reworkTheGoods">{TRANSLATE:rework_the_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- risk assessment -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessment=1">
		                                      <option value="riskAssessment">{TRANSLATE:risk_assessment}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- risk assessment date -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessmentDate=1">
		                                      <option value="riskAssessmentDate">{TRANSLATE:risk_assessment_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- risk assessment ref -->
                                    <xsl:choose>
		                                  <xsl:when test="riskAssessmentRef=1">
		                                      <option value="riskAssessmentRef">{TRANSLATE:risk_assessment_ref}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- root causes -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCauses=1">
		                                      <option value="rootCauses">{TRANSLATE:root_causes}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- root causes author-->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesAuthor=1">
		                                      <option value="rootCausesAuthor">{TRANSLATE:root_causes_author}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- root cause code-->
                                    <xsl:choose>
		                                  <xsl:when test="rootCauseCode=1">
		                                      <option value="rootCauseCode">{TRANSLATE:root_cause_code}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- root causes date -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesDate=1">
		                                      <option value="rootCausesDate">{TRANSLATE:root_causes_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												<!-- root causes yes no -->
                                    <xsl:choose>
		                                  <xsl:when test="rootCausesyn=1">
		                                      <option value="rootCausesyn">{TRANSLATE:root_causes_yes_no}</option>
                                        </xsl:when>
                                    </xsl:choose>
						
												
                                    <!-- S -->
												<!-- sales office -->
                                    <xsl:choose>
                                        <xsl:when test="salesOffice=1">
                                            <option value="salesOffice">{TRANSLATE:sales_office}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- sap item numbers -->
                                    <xsl:choose>
                                        <xsl:when test="sapItemNumbers=1">
                                            <option value="sapItemNumbers">{TRANSLATE:sap_item_numbers}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- severity -->
                                    <xsl:choose>
                                        <xsl:when test="severity=1">
                                            <option value="severity">{TRANSLATE:severity}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- site concerned -->
                                    <xsl:choose>
                                        <xsl:when test="sp_siteConcerned=1">
                                            <option value="sp_siteConcerned">{TRANSLATE:site_concerned}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- T -->
												<!-- team leader -->
                                    <xsl:choose>
                                        <xsl:when test="teamLeader=1">
                                            <option value="teamLeader">{TRANSLATE:team_leader}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- team member -->
                                    <xsl:choose>
                                        <xsl:when test="teamMember=1">
                                            <option value="teamMember">{TRANSLATE:team_member}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- thickness -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionThickness=1">
                                            <option value="dimensionThickness">{TRANSLATE:thickness}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- total closure date -->
                                    <xsl:choose>
                                        <xsl:when test="totalClosureDate=1">
                                            <option value="totalClosureDate">{TRANSLATE:total_closure_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
                                    <!-- U -->
												<!-- use goods -->
                                    <xsl:choose>
                                        <xsl:when test="qu_useGoods=1">
                                            <option value="qu_useGoods">{TRANSLATE:use_goods}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- Use Goods with Customer Derongation -->
                                    <xsl:choose>
                                        <xsl:when test="qu_useGoodsDerongation=1">
                                            <option value="qu_useGoodsDerongation">{TRANSLATE:use_goods_derongation}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
												<!-- V -->
												<!-- verification date -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationDate=1">
                                            <option value="qu_verificationDate">{TRANSLATE:verification_date}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- verification made -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationMade=1">
                                            <option value="qu_verificationMade">{TRANSLATE:verification_made}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												<!-- verification name -->
                                    <xsl:choose>
                                        <xsl:when test="qu_verificationName=1">
                                            <option value="qu_verificationName">{TRANSLATE:verification_name}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
												
												<!-- W -->
												<!-- weight of material -->
                                    <xsl:choose>
                                        <xsl:when test="qu_weightOfMaterial=1">
                                            <option value="qu_weightOfMaterial">{TRANSLATE:weight_of_material}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Where error Detected -->
                                    <xsl:choose>
                                        <xsl:when test="whereErrorOccured=1">
                                            <option value="whereErrorOccured">{TRANSLATE:where_error_occured}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    <!-- Width -->
                                    <xsl:choose>
                                        <xsl:when test="dimensionWidth=1">
                                            <option value="dimensionWidth">{TRANSLATE:width}</option>
                                        </xsl:when>
                                    </xsl:choose>
                                    
                                    
                                    
                                    <!-- X -->
												
                                    
                                    <!-- Y -->
												
                                    
                                    <!-- Z -->

                                    
                           
                                    
									
								</xsl:element>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>	
	</xsl:template>
	
	
	<xsl:template match="searchResults">
	
		<table width="100%" cellpadding="0">
			<tr>
				<td style="padding-left: 10px; padding-right: 10px;">
				
				<div style="background: #ffffe1; border: 1px solid #000000; padding: 5px;">
	            	<p style="margin: 0; line-height: 15px;"><a href="search?action=view&amp;save=true"><strong>Save Bookmark</strong></a></p>
            	</div>
				</td>
			</tr>
			
			<tr>
				<td style="padding: 10px">
				
					<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
						<tr>
							<!--<td style="width: 350px;">
							
								<xsl:apply-templates select="form" />
							
							</td>
							<td style="padding-left: 10px;">-->
							<td>
								<table width="100%" cellspacing="0" cellpadding="4" style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px;">
									<tr>
										<td>Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" /></td>
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
									</tr>
									<tr>
									</tr>
									<tr>
										<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 1000px;"><a target="_blank" href="search?action=view&amp;mode=excel"><img src="/images/excel.gif" border="0" /></a></td>
									</tr>
								</table>			
							
							</td>
						</tr>
					</table>
				
	
					<table width="100%" cellspacing="0" class="data_table" style="border: 1px solid #CCCCCC;">
					
						<xsl:apply-templates select="searchRowHeader"/>
						
						<xsl:apply-templates select="searchRow"/>
					
					</table>
					
					<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 10px;">
						<tr>
							<!--<td style="width: 350px;">
							
								<xsl:apply-templates select="form" />
							
							</td>
							<td style="padding-left: 10px;">-->
							<td>
								<table width="100%" cellspacing="0" cellpadding="4" style="background: #DDDDDD; border: 1px solid #CCCCCC; padding: 5px;">
									<tr>
										<td>Results <xsl:value-of select="resultsFrom" /> to <xsl:value-of select="resultsTo" /> of <xsl:value-of select="numResults" /></td>
									</tr>
									<tr>
										<td>
											Page:
											<xsl:apply-templates select="firstPageLink" />
											<xsl:apply-templates select="pageLink" />
											<xsl:apply-templates select="lastPageLink" />
										</td>
									</tr>
									<tr>
									</tr>
									<tr>
										<td rowspan="2" style="text-align: left; padding-left: 10 px; padding-right: 1000px;"><a target="_blank" href="search?action=view&amp;mode=excel"><img src="/images/excel.gif" border="0" /></a></td>
									</tr>
								</table>			
							
							</td>
						</tr>
					</table>
		
				</td>
			</tr>
		</table>
	
	</xsl:template>
	
	
	<xsl:template match="firstPageLink">
		<a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page=1">First</a><span style="padding: 0 10px 0 10px;">...</span>
	</xsl:template>
	
	<xsl:template match="pageLink">
		<xsl:choose>
			<xsl:when test="@current='true'">
				<span style="font-weight: bold; padding-right: 10px;"><xsl:value-of select="text()" /></span>
			</xsl:when>
			<xsl:otherwise>
				<span style="padding-right: 10px;"><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}"><xsl:value-of select="text()" /></a></span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="lastPageLink">
		<span style="padding-right: 10px;">...</span><a href="search?action=view&amp;orderBy={@orderBy}&amp;order={@order}&amp;page={text()}">Last</a>
	</xsl:template>
	
	
	<xsl:template match="searchRowHeader">
		<tr>
			<xsl:apply-templates select="searchColumnHeader"/>
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchRow">
		<tr onmouseover="this.style.backgroundColor='#F4F4F4';" onmouseout="this.style.backgroundColor='#FFFFFF';">
			<xsl:apply-templates select="searchColumn" />
		</tr>	
	</xsl:template>
	
	<xsl:template match="searchColumnHeader">
	
		<xsl:if test="sortable='1'">
			<xsl:element name="th">
				<xsl:attribute name="width">1%</xsl:attribute>
				<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
				<a href="search?action=view&amp;orderBy={field}&amp;order=ASC&amp;page={page}"><img src="/images/up.gif" border="0" alt="" /></a><a href="search?action=view&amp;orderBy={field}&amp;order=DESC&amp;page={page}"><img src="/images/down.gif" border="0" alt="" /></a>
			</xsl:element>	
		</xsl:if>
		
		<xsl:element name="th">
			<xsl:if test="sortFocus='true'">
					<xsl:attribute name="style">background: #dcddf2;</xsl:attribute>
				</xsl:if>
			<xsl:value-of select="title"/>
		</xsl:element>
	</xsl:template>
		
	
	<xsl:template match="searchColumn">
		<xsl:element name="td">
		<xsl:attribute name="style">border-right: 1px solid #DFDFDF;</xsl:attribute>
			<xsl:if test="@sortable='1'">
				<xsl:attribute name="colspan">2</xsl:attribute>
			</xsl:if>
			
			<xsl:apply-templates select="text"/>
			<xsl:apply-templates select="link"/>
			
		</xsl:element>	
	</xsl:template>
	
	<xsl:template match="text">
		<xsl:value-of select="text()"/>
	</xsl:template>
	
	<xsl:template match="link">
		<a href="{@url}"><xsl:value-of select="text()"/></a>
	</xsl:template>
		
</xsl:stylesheet>