// declare a global  XMLHTTP Request object
var XmlHttpObj;


var dropDownAjaxField;
var dropDownAjaxDestination;

var readOnlyAjaxField;


// create an instance of XMLHTTPRequest Object, varies with browser type, try for IE first then Mozilla
function CreateXmlHttpObj()
{
	// try creating for IE (note: we don't know the user's browser type here, just attempting IE first.)
	try
	{
		XmlHttpObj = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch(e)
	{
		try
		{
			XmlHttpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(oc)
		{
			XmlHttpObj = null;
		}
	}
	// if unable to create using IE specific code then try creating for Mozilla (FireFox)
	if(!XmlHttpObj && typeof XMLHttpRequest != "undefined")
	{
		XmlHttpObj = new XMLHttpRequest();
	}
}

function clearList(list)
{
	var sourceList = document.getElementById(list);

	// clear the list
	for (var count = sourceList.options.length-1; count >-1; count--)
	{
		sourceList.options[count] = null;
	}
}


function update_product_family()
{
	clearList('product_hierarchy_1');
	clearList('product_hierarchy_2');
	clearList('product_hierarchy_3');
	clearList('hierarchy_derived_key');

	var product_family = document.getElementById('product_range');
	var product_family_value = encodeURIComponent(product_family.options[product_family.selectedIndex].value);


	updateDropdown('/apps/ccr/ajax/hierarchy?product_range=' + product_family_value, 'product_range', 'product_hierarchy_1');
}

function initiation_update_route_to()		//JM - function used in SLOBS app
{
	document.getElementById("route_to_be_taken").innerHTML = "";

	var quality_inspection = document.getElementById('quality_inspection');
	var quality_inspection_value = quality_inspection.options[quality_inspection.selectedIndex].value;

	if (quality_inspection_value == 'yes')
	{
		text = document.createTextNode("Quality/Technical Decision");
		document.getElementById('route_to_be_taken').appendChild(text);

	} else if (quality_inspection_value == 'no')
	{

		if (document.getElementById('material_typesemi_finished').checked)
		{
			text = document.createTextNode("Production");
			document.getElementById('route_to_be_taken').appendChild(text);

		} else if (document.getElementById('material_typeraw_material').checked)
		{
			text = document.createTextNode("Purchasing");
			document.getElementById('route_to_be_taken').appendChild(text);

		} else if (document.getElementById('material_typefinished_traded_goods').checked)
		{
			text = document.createTextNode("Commercial Planning");
			document.getElementById('route_to_be_taken').appendChild(text);

		} else if (document.getElementById('material_typefinished_traded').checked)
		{
			text = document.createTextNode("Commercial Planning");
			document.getElementById('route_to_be_taken').appendChild(text);

		}
	}
}


function initiation_update_route_to_quality()		//JM - function used in SLOBS app
{
	document.getElementById("route_to_be_taken").innerHTML = "";

	if (document.getElementById('pass_inspectionno').checked)
	{
		text = document.createTextNode("SLOB Disposed");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
}

function initiation_update_route_to_production()		//JM - function used in SLOBS app
{
	document.getElementById("route_to_be_taken").innerHTML = "";

	if (document.getElementById('pass_inspectionno').checked)
	{
		text = document.createTextNode("SLOB Disposed");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
	else if (document.getElementById('pass_inspectionyes').checked || document.getElementById('pass_inspectionpartly').checked)
	{
		text = document.createTextNode("Demand Planning");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
}

function initiation_update_route_to_purchasing()		//JM - function used in SLOBS app
{
	document.getElementById("route_to_be_taken").innerHTML = "";

	if (document.getElementById('sold_to_supplierno').checked)
	{
		text = document.createTextNode("Production");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
	else if (document.getElementById('sold_to_supplieryes').checked || document.getElementById('sold_to_supplierbut').checked)
	{
		text = document.createTextNode("SLOB Completed");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
}

function initiation_update_route_to_sales()		//JM - function used in SLOBS app
{
	document.getElementById("route_to_be_taken").innerHTML = "";

	if (document.getElementById('saleableyes').checked)
	{
		text = document.createTextNode("Sold - SLOB Complete");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
	else if (document.getElementById('saleableno').checked)
	{
		text = document.createTextNode("Commercial Planning");
		document.getElementById('route_to_be_taken').appendChild(text);
	}
}

function update_american_credit_list()	//PH - function used in credit stage of complaints
{
	//clearList('transferOwnershipAmerican');
	
	var amount = document.getElementById('creditNoteValueAmerican');
	var amount_value = amount.options[amount.selectedIndex].value;
	
	//if (amount_value == '10')
	//{
		var permission = 'complaints_american_credit_lower';
		updateDropdown('/apps/complaints/ajax/americanCredit?transferOwnershipAmerican=' + permission, 'creditNoteValueAmerican', 'transferOwnershipAmerican');
		//updateDropdown('/apps/npi/ajax/potential?type=new&marketPotential=' + currency_ref_value, 'currencyReference', 'marketPotential');
	//}
}

function update_route_to_ijf()		//JM - function used in IJF app
{
	document.getElementById("suggestedRoute").innerHTML = "";

	var acceptedRejected = document.getElementById('acceptedRejected');
	var acceptedRejected_value = acceptedRejected.options[acceptedRejected.selectedIndex].value;

	if (acceptedRejected_value == 'accepted')
	{
		if (document.getElementById('ijfCompletedyes').checked)
		{
			text = document.createTextNode("IJF Completed");
			document.getElementById('suggestedRoute').appendChild(text);

		}
		else if (document.getElementById('ijfCompletedno').checked)
		{
			text = document.createTextNode("Data Administration");
			document.getElementById('suggestedRoute').appendChild(text);
		}

	}
	else if (acceptedRejected_value == 'rejected')
	{
		if (document.getElementById('ijfCompletedyes').checked)
		{
			text = document.createTextNode("IJF Completed - Rejected");
			document.getElementById('suggestedRoute').appendChild(text);

		}
		else if (document.getElementById('ijfCompletedno').checked)
		{
			text = document.createTextNode("Back To Initiator");
			document.getElementById('suggestedRoute').appendChild(text);
		}
	}
	else if (acceptedRejected_value == 'neither')
	{
		if (document.getElementById('ijfCompletedyes').checked)
		{
			text = document.createTextNode("Please select from dropdown below ...");
			document.getElementById('suggestedRoute').appendChild(text);

		}
		else if (document.getElementById('ijfCompletedno').checked)
		{
			text = document.createTextNode("Please select from dropdown below ...");
			document.getElementById('suggestedRoute').appendChild(text);
		}
	}
}

function update_npi_marketpotential()			//PH - function used in NPI app (adapted from update_route_to_ijf())
{															//and the product hierarchy functions
	clearList('marketPotential');
	clearList('maxCustomerPotential');
	clearList('scapaCustomerPotential');
	clearList('scapaYearPotential');
	
	var currency_ref = document.getElementById('currencyReference');
	var currency_ref_value = encodeURIComponent(currency_ref.options[currency_ref.selectedIndex].value);
	
	if((document.getElementById('newReplacementnew').checked) && (currency_ref_value !== ""))
	{		
		updateDropdown('/apps/npi/ajax/potential?type=new&marketPotential=' + currency_ref_value, 'currencyReference', 'marketPotential');
	}
	else if(document.getElementById('newReplacementrep').checked && currency_ref_value !== "")
	{
		updateDropdown('/apps/npi/ajax/potential?type=rep&marketPotential=' + currency_ref_value, 'currencyReference', 'marketPotential');
	}
}

function update_npi_maxcustomerpotential()		//PH - function used in NPI app
{
	clearList('maxCustomerPotential');
	clearList('scapaCustomerPotential');
	clearList('scapaYearPotential');
	
	var currency_ref2 = document.getElementById('currencyReference');
	var currency_ref_value2 = encodeURIComponent(currency_ref2.options[currency_ref2.selectedIndex].value);
	
	if(document.getElementById('newReplacementnew').checked)
	{
		updateDropdown('/apps/npi/ajax/potential2?type=new&maxCustomerPotential=' + currency_ref_value2, 'currencyReference', 'maxCustomerPotential');
	}
	else if(document.getElementById('newReplacementrep').checked)
	{
		updateDropdown('/apps/npi/ajax/potential2?type=rep&maxCustomerPotential=' + currency_ref_value2, 'currencyReference', 'maxCustomerPotential');
	}
}

function update_npi_scapacustomerpotential()	//PH - function used in NPI app
{
	clearList('scapaCustomerPotential');
	clearList('scapaYearPotential');
	
	var currency_ref3 = document.getElementById('currencyReference');
	var currency_ref_value3 = encodeURIComponent(currency_ref3.options[currency_ref3.selectedIndex].value);
	
	if(document.getElementById('newReplacementnew').checked)
	{
		updateDropdown('/apps/npi/ajax/potential3?type=new&scapaCustomerPotential=' + currency_ref_value3, 'currencyReference', 'scapaCustomerPotential');
	}
	else if(document.getElementById('newReplacementrep').checked)
	{
		updateDropdown('/apps/npi/ajax/potential3?type=rep&scapaCustomerPotential=' + currency_ref_value3, 'currencyReference', 'scapaCustomerPotential');
	}
}

function update_npi_scapayearpotential()		//PH - function used in NPI app
{
	clearList('scapaYearPotential');
	
	var currency_ref4 = document.getElementById('currencyReference');
	var currency_ref_value4 = encodeURIComponent(currency_ref4.options[currency_ref4.selectedIndex].value);
	
	if(document.getElementById('newReplacementnew').checked)
	{
		updateDropdown('/apps/npi/ajax/potential4?type=new&scapaYearPotential=' + currency_ref_value4, 'currencyReference', 'scapaYearPotential');
	}
	else if(document.getElementById('newReplacementrep').checked)
	{
		updateDropdown('/apps/npi/ajax/potential4?type=rep&scapaYearPotential=' + currency_ref_value4, 'currencyReference', 'scapaYearPotential');
	}
}

function update_npi_priority(strategicImportance, scpValue)		//PH - function used in NPI->technicalManager app
{
	//document.getElementById("priorityReadOnly").innerHTML = ""
	
	
	// Get strategic importance from dropdownbox. - WORKS!!! YAY!!!
	var timeToDevAndSell = document.getElementById('timeToDevAndSell');
	var timeToDevAndSell_value = encodeURIComponent(timeToDevAndSell.options[timeToDevAndSell.selectedIndex].value);

	var result = timeToDevAndSell_value * strategicImportance * scpValue;
	var resultLetter = "N/A";
	
	if(result > 400) resultLetter = "A";		
	else if(result <= 400 && result >= 200) resultLetter = "B";
	else if(result < 200) resultLetter = "C";
	
	text = document.createTextNode(resultLetter);
	//document.getElementById('priorityReadOnly').appendChild(text);
	document.getElementById('priority').value = resultLetter;
	
	// returns all values involved in this function.
	// alert('timeToDevAndSell: ' + timeToDevAndSell_value + '\nstrategicImportance: ' + strategicImportance + '\ncustomerPotential: ' + scpValue + '\nResult: ' + result+ '\nResult Letter: ' + resultLetter);
	
}

function update_sap_customer_email()		//JM - function used in Complaints app
{
	clearList('sapEmailAddress');

	var sap_customer_number = document.getElementById('sapCustomerNumber');
	var sap_customer_number_value = encodeURIComponent(sap_customer_number.options[sap_customer_number.selectedIndex].value);

	updateDropdown('/apps/complaints/ajax/sapEmailAddress?sapCustomerNumber=' + sap_customer_number_value, 'sapCustomerNumber', 'sapEmailAddress');
}


function initiation_update_owner()		//BPD - function used in SLOBS app
{
	clearList('owner');

	var quality_inspection = document.getElementById('quality_inspection');
	var quality_inspection_value = encodeURIComponent(quality_inspection.options[quality_inspection.selectedIndex].value);

	var material_type = document.getElementById('material_type');
	var material_type_value = encodeURIComponent(material_type.options[material_type.selectedIndex].value);

	updateDropdown('/apps/slobs/ajax/owner?quality_inspection=' + quality_inspection_value + '&material_type=' + material_type_value, 'quality_inspection', 'owner');
}

function commercialPlanning_update_owner()		//BPD - function used in SLOBS app
{
	clearList('owner');

	var sale_offered = document.getElementById('sale_offered');
	var sale_offered_value = encodeURIComponent(sale_offered.options[sale_offered.selectedIndex].value);

	var material_type = document.getElementById('material_type');
	var material_type_value = document.getElementById('material_type').value;


	updateDropdown('/apps/slobs/ajax/owner?sale_offered=' + sale_offered_value + '&material_type=' + material_type_value, 'sale_offered', 'owner');
}


function update_product_hierarchy_1()
{
	clearList('product_hierarchy_2');
	clearList('product_hierarchy_3');
	clearList('hierarchy_derived_key');

	var product_family = document.getElementById('product_range');
	var product_family_value = encodeURIComponent(product_family.options[product_family.selectedIndex].value);

	var product_hierarchy_1 = document.getElementById('product_hierarchy_1');
	var product_hierarchy_1_value = encodeURIComponent(product_hierarchy_1.options[product_hierarchy_1.selectedIndex].value);

	updateDropdown('/apps/ccr/ajax/hierarchy?product_range=' + product_family_value + '&product_hierarchy_1=' + product_hierarchy_1_value, 'product_hierarchy_1', 'product_hierarchy_2');
}


function update_product_hierarchy_2()
{
	clearList('product_hierarchy_3');
	clearList('hierarchy_derived_key');

	var product_family = document.getElementById('product_range');
	var product_family_value = encodeURIComponent(product_family.options[product_family.selectedIndex].value);

	var product_hierarchy_1 = document.getElementById('product_hierarchy_1');
	var product_hierarchy_1_value = encodeURIComponent(product_hierarchy_1.options[product_hierarchy_1.selectedIndex].value);

	var product_hierarchy_2 = document.getElementById('product_hierarchy_2');
	var product_hierarchy_2_value = encodeURIComponent(product_hierarchy_2.options[product_hierarchy_2.selectedIndex].value);


	updateDropdown('/apps/ccr/ajax/hierarchy?product_range=' + product_family_value + '&product_hierarchy_1=' + product_hierarchy_1_value + '&product_hierarchy_2=' + product_hierarchy_2_value, 'product_hierarchy_2', 'product_hierarchy_3');
}



function update_product_hierarchy_3()
{
	clearList('hierarchy_derived_key');

	var product_family = document.getElementById('product_range');
	var product_family_value = encodeURIComponent(product_family.options[product_family.selectedIndex].value);

	var product_hierarchy_1 = document.getElementById('product_hierarchy_1');
	var product_hierarchy_1_value = encodeURIComponent(product_hierarchy_1.options[product_hierarchy_1.selectedIndex].value);

	var product_hierarchy_2 = document.getElementById('product_hierarchy_2');
	var product_hierarchy_2_value = encodeURIComponent(product_hierarchy_2.options[product_hierarchy_2.selectedIndex].value);

	var product_hierarchy_3 = document.getElementById('product_hierarchy_3');
	var product_hierarchy_3_value = encodeURIComponent(product_hierarchy_3.options[product_hierarchy_3.selectedIndex].value);


	//updateReadOnly('/apps/ccr/ajax/hierarchy?product_range=' + product_family_value + '&product_hierarchy_1=' + product_hierarchy_1_value + '&product_hierarchy_2=' + product_hierarchy_2_value + '&product_hierarchy_3=' + product_hierarchy_3_value, 'product_hierarchy_key');

	updateDropdown('/apps/ccr/ajax/hierarchy?product_range=' + product_family_value + '&product_hierarchy_1=' + product_hierarchy_1_value + '&product_hierarchy_2=' + product_hierarchy_2_value + '&product_hierarchy_3=' + product_hierarchy_3_value, 'product_hierarchy_3', 'hierarchy_derived_key');
}


function updateDropdown(ajaxUrl, source, destination)
{
	var sourceList = document.getElementById(source);

	dropDownAjaxDestination = destination;
	dropDownAjaxField = source;

	var selectedSource = sourceList.options[sourceList.selectedIndex].value;

	if (selectedSource != "Please select...")
	{
		CreateXmlHttpObj();

		// verify XmlHttpObj variable was successfully initialized
		if(XmlHttpObj)
		{
			// assign the StateChangeHandler function ( defined below in this file)
			// to be called when the state of the XmlHttpObj changes
			// receiving data back from the server is one such change
			XmlHttpObj.onreadystatechange = StateChangeHandler;

			// define the iteraction with the server -- true for as asynchronous.
			XmlHttpObj.open("GET", ajaxUrl,  true);

			// send request to server, null arg  when using "GET"
			XmlHttpObj.send(null);
		}
	}
}

function updateMultipleFieldValues(ajaxURL, source)
{
	// Get the source value
	var sourceField = document.getElementById(source);
	
	
}

function updateSupplierEmailAddress(ajaxUrl, source, destination)
{
	var sourceList = document.getElementById(source);

	dropDownAjaxDestination = destination;
	dropDownAjaxField = source;

	var selectedSource = sourceList.value;

	if (selectedSource != "Please select...")
	{
		CreateXmlHttpObj();

		// verify XmlHttpObj variable was successfully initialized
		if(XmlHttpObj)
		{
			// assign the StateChangeHandler function ( defined below in this file)
			// to be called when the state of the XmlHttpObj changes
			// receiving data back from the server is one such change
			XmlHttpObj.onreadystatechange = StateChangeHandler;

			// define the iteraction with the server -- true for as asynchronous.
			XmlHttpObj.open("GET", ajaxUrl,  true);

			// send request to server, null arg  when using "GET"
			XmlHttpObj.send(null);
		}
	}
}

function setExternalSupplierEmail()
{
	var supplierNumber = document.getElementById("sp_sapSupplierNumber").value;
	var supplierNumber_value = encodeURIComponent(supplierNumber.value);
	
	updateSupplierEmailAddress('/apps/complaints/ajax/updateSupplierEmail?supplierNo=' + supplierNumber_value, 'sp_sapSupplierNumber', 'externalEmailAddress');
}

// this function called when state of  XmlHttpObj changes
// we're interested in the state that indicates data has been
// received from the server
function StateChangeHandler()
{
	// state ==4 indicates receiving response data from server is completed
	if(XmlHttpObj.readyState == 4)
	{
		// To make sure valid response is received from the server, 200 means response received is OK
		if(XmlHttpObj.status == 200)
		{
			PopulateDestination(XmlHttpObj.responseXML.documentElement);
		}
		else
		{
			alert("problem retrieving data from the server, status code: "  + XmlHttpObj.status);
		}
	}
}


function PopulateDestination(node)
{
	var destinationList = document.getElementById(dropDownAjaxDestination);

	// clear the list
	for (var count = destinationList.options.length-1; count >-1; count--)
	{
		destinationList.options[count] = null;
	}

	var destinationNodes = node.getElementsByTagName('row');
	var idValue;
	var textValue;
	var optionItem;
	// populate the dropdown list with data from the xml doc
	for (var count = 0; count < destinationNodes.length; count++)
	{
		textValue = GetInnerText(destinationNodes[count]);
		idValue = destinationNodes[count].getAttribute("name");
		optionItem = new Option( textValue, idValue,  false, false);
		destinationList.options[destinationList.length] = optionItem;
	}
}


function update_supplier_complaint_error_field() // Supplier Complaints Add Form
{
	var how_error_detected = document.getElementById('howErrorDetected');
	var how_error_detected_value = encodeURIComponent(how_error_detected.options[how_error_detected.selectedIndex].value);
	
	if(how_error_detected_value != "incident")
	{
		//document.getElementById('howErrorDetectedIncidentGroup').style.display = '';
		//alert("df");
	}
}


// returns the node text value
function GetInnerText (node)
{
	return (node.textContent || node.innerText || node.text);
}

// Pricing
function update_product_name_dropdown()
{
	// Update productName dropdown
	
	clearList('productName');

	var sales_person_name_number = document.getElementById('salesPersonNameNumber');
	var sales_person_name_number_value = encodeURIComponent(sales_person_name_number.options[sales_person_name_number.selectedIndex].value);
					
	// Source then the target
	updateDropdown('/apps/pricing/ajax/productName?sales_person_name=' + sales_person_name_number_value, 'salesPersonNameNumber', 'productName');
}

function update_product_dropdown()
{
	// Update productName dropdown
	
	clearList('product');
	
	var product_name = document.getElementById('productName');
	var product_name_value = encodeURIComponent(product_name.options[product_name.selectedIndex].value);
			
	// Source then the target
	updateDropdown('/apps/pricing/ajax/product?product_name=' + product_name_value, 'productName', 'product');
}

function update_where_error_occurred_dropdown()
{
	clearList('whereErrorOccured');
	
	var site_concerned = document.getElementById('sp_siteConcerned');
	var site_concerned_value = encodeURIComponent(site_concerned.options[site_concerned.selectedIndex].value);
		
	updateDropdown('/apps/complaints/ajax/whereErrorOccurred?sp_siteConcerned=' + site_concerned_value, 'sp_siteConcerned', 'whereErrorOccured');
}

function show_details_internal_complaint()
{
	var where_error_occured = document.getElementById('whereErrorOccured');
	var where_error_occured_value = encodeURIComponent(where_error_occured.options[where_error_occured.selectedIndex].value);
	
	if(where_error_occured_value == "Other")
	{
		document.getElementById("othersRow").style.display = '';
	}
	else
	{
		document.getElementById("othersRow").style.display = 'none';
	}
}

function copyToProcessOwnerField(destination, source)
{
	document.getElementById(destination).value = '';
	
	var return_request_name = document.getElementById(source);
	var return_request_name_value = encodeURIComponent(return_request_name.options[return_request_name.selectedIndex].value);
	
	document.getElementById(destination).value = return_request_name_value;
}



// For the NPI Region owner dropdowns.
function update_productManager_dropdown()
{
	// Update productName dropdown
	
	clearList('productManager');
	
	var business_unit = document.getElementById('businessUnit');
	var business_unit_value = encodeURIComponent(business_unit.options[business_unit.selectedIndex].value);
	
	var owner_region = document.getElementById('ownerRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	// 23/07/2012 - Rob - Pass whether the npi was made on or after this date to determine whether or not to load the new market sector list
	var is_new = document.getElementById('isNew');
	var is_new_value = encodeURIComponent(is_new.value);

//	alert(owner_region_value);
//	alert(business_unit_value);
	
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegionProductManager?businessUnit=' + business_unit_value + '&region=' + owner_region_value + '&isNew=' + is_new_value, 'businessUnit', 'productManager');
	
}

// For the NPI Region owner dropdowns.
function update_delegateTo_dropdown($status)
{
	// Update productName dropdown
	
	clearList('delegateTo');
	
	if($status == "productManager")
	{
		$status = "technicalManager";
	}
	else if($status == "technicalManager")
	{
		$status = "commercialManager";
	}
	else
	{
		$status = "productManager";
	}
	
	
	var owner_region = document.getElementById('delegateRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegion?owner_region=' + owner_region_value + '&status=' + $status, 'delegateRegion', 'delegateTo');
}

function update_technicalManager_dropdown()
{
	// Update productName dropdown
	
	clearList('technicalManager');
	
	var owner_region = document.getElementById('techManRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegionTechManager?owner_region=' + owner_region_value, 'techManRegion', 'technicalManager');
}

function update_commercialManager_dropdown()
{
	// Update productName dropdown
	
	clearList('commercialManager');
	
	var owner_region = document.getElementById('comManRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegionComManager?owner_region=' + owner_region_value, 'comManRegion', 'commercialManager');
}

function update_delegateComManager_dropdown()
{
	// Update productName dropdown
	
	clearList('delegateTo');
	
	var owner_region = document.getElementById('delegateRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegionComManager?owner_region=' + owner_region_value, 'delegateRegion', 'delegateTo');
}

function update_delegateTechMan_dropdown()
{
	// Update productName dropdown
	
	clearList('delegateTo');
	
	var owner_region = document.getElementById('delegateRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/ownerRegionTechManager?owner_region=' + owner_region_value, 'delegateRegion', 'delegateTo');
}


function update_businessUnit_dropdown()
{
	// Update productName dropdown
	
	clearList('businessUnit');
	
	var owner_region = document.getElementById('ownerRegion');
	var owner_region_value = encodeURIComponent(owner_region.options[owner_region.selectedIndex].value);
	
	// 23/07/2012 - Rob - Pass whether the npi was made on or after this date to determine whether or not to load the new market sector list
	var is_new = document.getElementById('isNew');
	var is_new_value = encodeURIComponent(is_new.value);
	
	//alert(owner_region_value);
			
	// Source then the target
	updateDropdown('/apps/npi/ajax/businessUnit?businessUnit=' + owner_region_value + '&isNew=' + is_new_value, 'ownerRegion', 'businessUnit');
}

function update_s2_from_s1_dropdown()
{
	clearList('s2');
	clearList('s3');
	
	var s1 = document.getElementById('s1');
	var s1_value = encodeURIComponent(s1.options[s1.selectedIndex].value);
			
	// Source then the target
	updateDropdown('/apps/serviceDesk/ajax/s2?s2=' + s1_value, 's1', 's2');
}

function update_s3_from_s2_dropdown()
{
	clearList('s3');
	
	var s1 = document.getElementById('s1');
	var s1_value = encodeURIComponent(s1.options[s1.selectedIndex].value);
	
	var s2 = document.getElementById('s2');
	var s2_value = encodeURIComponent(s2.options[s2.selectedIndex].value);
			
	// Source then the target
	updateDropdown('/apps/serviceDesk/ajax/s3?s3=' + s2_value + '&s2=' + s1_value, 's2', 's3');
}

function update_s4_from_s3_dropdown()
{
	clearList('s4');
	
	var s3 = document.getElementById('s3');
	var s3_value = encodeURIComponent(s3.options[s3.selectedIndex].value);
			
	// Source then the target
	updateDropdown('/apps/serviceDesk/ajax/s4?s4=' + s3_value, 's3', 's4');
}

function updateDropdownNoDestination(ajaxUrl, source)
{
	var sourceList = document.getElementById(source);

	//dropDownAjaxDestination = destination;
	dropDownAjaxField = source;

	var selectedSource = sourceList.options[sourceList.selectedIndex].value;

	if (selectedSource != "Please select...")
	{
		CreateXmlHttpObj();

		// verify XmlHttpObj variable was successfully initialized
		if(XmlHttpObj)
		{
			// assign the StateChangeHandler function ( defined below in this file)
			// to be called when the state of the XmlHttpObj changes
			// receiving data back from the server is one such change
			XmlHttpObj.onreadystatechange = StateChangeHandler;

			// define the iteraction with the server -- true for as asynchronous.
			XmlHttpObj.open("GET", ajaxUrl,  true);

			// send request to server, null arg  when using "GET"
			XmlHttpObj.send(null);
		}
	}
}

function get_cash_date_selected()
{
	var cash_date = document.getElementById('cashDate');
	var cash_date_value = encodeURIComponent(cash_date.options[cash_date.selectedIndex].value);
	
	alert(cash_date_value);

	updateDropdownNoDestination('/apps/dashboard/ajax/getForecastDates?cashDate=' + cash_date_value, 'cashDate');
}

function update_sap_external_status()
{
	var sap_option = document.getElementById('sapOptionscr');
	
	if(sap_option.checked == true)
	{
		document.getElementById('sapExternalStatus').value = 'S4 Call With CSC';
		document.getElementById('priority').value = 'S4';
	}
	else
	{
		document.getElementById('sapExternalStatus').value = 'S3 Call With CSC';
		document.getElementById('priority').value = 'S3';
	}
}

