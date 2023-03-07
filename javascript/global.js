function toggle_display(idname)
{
	obj = document.getElementById(idname);
	
	if (obj)
	{
		if (obj.style.display == "none")
		{
			obj.style.display = "";
		}
		else
		{
			obj.style.display = "none";
		}
	}
	return false;
}


function dataTableRowOver(row)
{
	if (row.className != 'selected')
	{
		row.style.backgroundColor = '#EEEEEE';
	}
	else
	{
		row.style.backgroundColor = '#f2d2d2';
	}
	row.style.cursor = 'pointer';
}


function dataTableRowOut(row)
{
	if (row.className != 'selected')
	{
		row.style.backgroundColor = '#FFFFFF';
	}
	row.style.cursor = '';
}

function toggleSelect(row, id)
{
	if (row.className != 'selected')
	{
		row.className = 'selected';
		row.style.backgroundColor = '#f2d2d2';
		document.getElementById('select_'+id).checked = true;
	}
	else
	{
		row.className = '';
		row.style.backgroundColor = '#FFFFFF';
		document.getElementById('select_'+id).checked = false;
	}	
}

function itemSelect()
{
	var theRows = null;
	f = document.forms[0];
	
	if (typeof(document.getElementsByTagName) != 'undefined') {
        theRows = document.getElementById('data_table').getElementsByTagName('tr');
        
        for (var i=1; i<=theRows.length; i++)
		{
			if (document.getElementById('select_'+i))
			{
				if (!f.selectall.checked)
				{
					document.getElementById('row_'+i).className = '';
					document.getElementById('row_'+i).style.backgroundColor = '#FFFFFF';
				}
				else
				{
					document.getElementById('row_'+i).className = 'selected';
					document.getElementById('row_'+i).style.backgroundColor = '#f2d2d2';
				}
				
				document.getElementById('select_'+i).checked = f.selectall.checked;
			}
		}
    }
    else
    {
        return false;
    }
}

//function itemSelect()
//{
//	f = document.forms[0];
//	for (i = 0 ; i < f.elements.length; i++) {
//		if ((f.elements[i].type == "checkbox") && (f.elements[i].name == "select[]")) {
//			if (!(f.elements[i].value == "DISABLED" || f.elements[i].disabled)) {
//				f.elements[i].checked = f.selectall.checked;
//			}
//		}
//	}
//
//	return true;
//}

function selectNone()
{
	var theRows = null;
	
	if (typeof(document.getElementsByTagName) != 'undefined') {
        theRows = document.getElementById('data_table').getElementsByTagName('tr');
        
        for (var i=1; i<=theRows.length; i++)
		{
			if (document.getElementById('checkbox_'+i))
			{
				document.getElementById('checkbox_'+i).checked = false;
			}
		}
    }
    else
    {
        return false;
    }
}


var lastSelected = "NULL";
var lastSelected_iframe = "NULL";
var lastSubSelected = "NULL";
var lastSubSelected_iframe = "NULL";
var timedOff = 0;
var timedOff_iframe = 0;
var timedSubOff = 0;
var timedSubOff_iframe = 0;

function getElementPosition(eElement)
{
	var nLeftPos = document.getElementById(eElement).offsetLeft;
	nLeftPos += document.getElementById(eElement).offsetWidth;
	
	var eParElement = document.getElementById(eElement).offsetParent;
	while (eParElement != null)
	{
		nLeftPos += eParElement.offsetLeft;
		eParElement = eParElement.offsetParent;
	}
	
	return nLeftPos;
}

function getElementTopPosition(eElement)
{
	var nTopPos = document.getElementById(eElement).offsetTop;
	var eParElement = document.getElementById(eElement).offsetParent;
	while (eParElement != null)
	{
		nTopPos += eParElement.offsetTop;
		eParElement = eParElement.offsetParent;
	}
	return nTopPos;
}

function showDropdown(id)
{
	if (lastSelected != "NULL")
	{
		document.getElementById(lastSelected).style.visibility="hidden";
		document.getElementById(lastSelected_iframe).style.display="none";
	}

	clearTimeout(timedOff);
	clearTimeout(timedOff_iframe);
	
	
	document.getElementById('dropdown_container_'+id).style.left=(getElementPosition('li_toolbar_'+id) - 184) + "px";
	document.getElementById('dropdown_container_'+id).style.top=(getElementTopPosition('li_toolbar_'+id) + 31) + "px";
	document.getElementById('dropdown_container_'+id).style.visibility="visible";
	document.getElementById("dropdown_container_" + id).style.zIndex = 10;
	lastSelected = 'dropdown_container_'+id;
	
	
	document.getElementById("dropdown_container_iframe_" + id).style.display="block";
	
	document.getElementById("dropdown_container_iframe_" + id).style.left = document.getElementById("dropdown_container_" + id).offsetLeft + 16;
	document.getElementById("dropdown_container_iframe_" + id).style.top = document.getElementById("dropdown_container_" + id).offsetTop;
	document.getElementById("dropdown_container_iframe_" + id).style.zIndex = 9;
	document.getElementById("dropdown_container_iframe_" + id).style.width = '174px';
	document.getElementById("dropdown_container_iframe_" + id).style.height = document.getElementById('dropdown_container_'+id).offsetHeight;
	
	lastSelected_iframe = 'dropdown_container_iframe_'+id;
}

function showSubDropdown(parent, id)
{
	clearTimeout(timedOff);
	clearTimeout(timedOff_iframe);

	if (lastSubSelected != "NULL")
	{
		document.getElementById(lastSubSelected).style.visibility="hidden";
		document.getElementById(lastSubSelected_iframe).style.display="none";
	}

	clearTimeout(timedSubOff);
	clearTimeout(timedSubOff_iframe);
	
	if (document.getElementById('sub_dropdown_container_'+id))
	{
		document.getElementById('sub_dropdown_container_'+id).style.left=(getElementPosition('li_toolbar_'+parent) - 365) + "px";
		document.getElementById('sub_dropdown_container_'+id).style.top=(getElementTopPosition('dropdown_'+id) -10 ) + "px";
		document.getElementById('sub_dropdown_container_'+id).style.visibility="visible";
		lastSubSelected = 'sub_dropdown_container_'+id;
		
		
		document.getElementById("sub_dropdown_container_iframe_" + id).style.display="block";
	
		document.getElementById("sub_dropdown_container_iframe_" + id).style.left = document.getElementById("sub_dropdown_container_" + id).offsetLeft + 16;
		document.getElementById("sub_dropdown_container_iframe_" + id).style.top = document.getElementById("sub_dropdown_container_" + id).offsetTop;
		document.getElementById("sub_dropdown_container_iframe_" + id).style.zIndex = 9;
		document.getElementById("sub_dropdown_container_iframe_" + id).style.width = '174px';
		document.getElementById("sub_dropdown_container_iframe_" + id).style.height = document.getElementById('sub_dropdown_container_'+id).offsetHeight;
		
		lastSubSelected_iframe = 'sub_dropdown_container_iframe_'+id;
	}
}

function hideSubDropdown(id, timer)
{
	hideDropdown(id);

	if (lastSubSelected != "NULL")
	{
		clearTimeout(timedSubOff);
		clearTimeout(timedSubOff_iframe);
		
		if (document.getElementById('sub_dropdown_container_'+id))
		{
			var a="document.getElementById('sub_dropdown_container_" +id+ "').style.visibility=" + "'hidden'";
			var b="document.getElementById('sub_dropdown_container_iframe_" +id+ "').style.display='none'";
			
			if (timer)
			{
				timedSubOff = setTimeout(a, 600);
				timedSubOff_iframe = setTimeout(b, 600);
			}
			else
			{
				timedSubOff = setTimeout(a, 0);
				timedSubOff_iframe = setTimeout(b, 0);
			}
		}
	}

}

function hideDropdown(id)
{
	if (lastSelected != "NULL")
	{
		clearTimeout(timedOff);
		clearTimeout(timedOff_iframe);

		if (document.getElementById('dropdown_container_'+id))
		{
			var a="document.getElementById('dropdown_container_" +id+ "').style.visibility='hidden'";
			timedOff = setTimeout(a, 600);
			
			var b="document.getElementById('dropdown_container_iframe_" +id+ "').style.display='none'";
			timedOff_iframe = setTimeout(b, 600);
		}
	}
}



function openWindow(width, height, url)
{
	var newWindow;
	newWindow = window.open(url, '0', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width='+width+',height='+height+',top='+((screen.height - height) / 2)+',left='+((screen.width - width) / 2));
	newWindow.focus();
}

function openWindow(width, height, url, options)
{
	var newWindow;
	newWindow = window.open(url, '0', options+',width='+width+',height='+height+',top='+((screen.height - height) / 2)+',left='+((screen.width - width) / 2));
	newWindow.focus();
}


function buttonPress(action, nextAction)
{
	if (typeof nextAction != "undefined") {
		document.getElementById('nextAction').value=nextAction;
	}
	
	document.getElementById('action').value=action;
	postback();
	disableButtons();
}
/* WC AE - 24/01/08
	added function to add in anchor tag to the action of the form, in order to get back to the page position
*/
function buttonPressMultiGroup(action, nextAction, anchor)
{
	if (typeof nextAction != "undefined") {
		document.getElementById('nextAction').value=nextAction;
	}
	if(anchor){
		//THIS BIT BINNED OFF AS PROBS WITH IE
		/*var curLocation = window.location.href;
		if(curLocation.indexOf("#") >= 0){
			//var ind = curLocation.indexOf("#");
			//curLocation = curLocation.substr(0, ind);
			//document.form.action = curLocation + "#" + anchor;
		}else{
			//document.getElementById('form').setAttribute("action", "test.html");
		}*/
		var newElem = document.createElement("input");
		newElem.name = "whichAnchor";
		newElem.type = 'hidden';
		newElem.value = anchor;
		document.getElementById('form').appendChild(newElem);
		//alert(newElem.value);
	}	
	document.getElementById('action').value=action;
	postback();
	disableButtons();
}
/* WC END*/

// A duplicate of the above but for attachment
function buttonPressAttachment(anchor)
{	
	if(anchor){
		var newElem = document.createElement("input");
		newElem.name = "whichAnchor";
		newElem.type = 'hidden';
		newElem.value = anchor;
		document.getElementById('form').appendChild(newElem);
	}
	
	postback();
	disableButtons();
}

function postback()
{
	document.getElementById('form').submit();
}

function linkFormSubmit(action, validate)
{
	document.getElementById('validate').value=validate;
	document.getElementById('action').value=action;
	postback();
}

function removeMultipleGroupRow(id, group, nextAction)
{
	document.getElementById('multipleGroupId' + group).value=id;
	buttonPress("multipleGroupRemove|" + group, nextAction)
}


function disableButtons()
{	
	var list = document.getElementsByTagName('input');

    if (list)
    {
		for (i=0; i < list.length; i++)
		{
			if (list[i].type == 'submit' || list[i].type == 'button')
			{
				list[i].disabled=true;
			}
		}
    }
}

function buttonLink(url)
{
	window.location = url;
	disableButtons();
}

function checkForOtherDropdown(field, otherField, required)		//used with alternativeDropdown control to check if "other" is selected as an option
{
	var requiredClass = 'optional';

	if (required == 'true')
	{
		requiredClass = 'required';
	}
	
	if (field.value == 'other')
	{
		document.getElementById(otherField).className = 'textboxOther ' + requiredClass;
	}
	else
	{
		document.getElementById(otherField).className = 'textboxInvisible';
	}
}


function checkForOtherCombo(field, otherField, required)		//used with alternativeCombo control to check if "other" is selected as an option
{
	var requiredClass = 'optional';
			
	if (required == 'true')
	{
		requiredClass = 'required';
	}
		
	var i=0;
	var max = field.options.length;
	var found = false;
	
	
	for (i = 0; i < max; i++)
	{
		if (field.options[i].text == 'Other' && field.options[i].selected == true)
		{
			found = true;
		}
	}
	
	if (found == true)
	{
		document.getElementById(otherField).className = 'textboxOther ' + requiredClass;
	}
	else
	{
		document.getElementById(otherField).className = 'textboxInvisible';
	}
}

function goToAction(id, formAction)
{
	buttonPress(formAction);
	document.form.action = "/apps/ccr/view?id=" + id;
	postback();
}


function toggleComboSelected(item)
{		
	if (document.getElementById(item).className == 'selected')
	{
		document.getElementById(item).className = '';
	}
	else
	{
		document.getElementById(item).className = 'selected';
	}
}


function windowLoaded(event)
{	
	var tags = document.getElementsByTagName("label");
	
	for(var i=0; i < tags.length; i++) 
	{
		tags[i].ondrag = function () { return false; };
		tags[i].onselectstart = function () { return false; };
	}

}


function showHelp(id)
{
	//document.getElementById("help_" + id).style.left=(getElementPosition('helpicon_' + id)) + 13 + "px";
	//document.getElementById("help_" + id).style.top=(getElementTopPosition('helpicon_' + id)) + 2 + "px";
	
	document.getElementById("help_div_" + id).style.left=(getElementPosition('helpicon_' + id)) + 8 + "px";
	document.getElementById("help_div_" + id).style.top=(getElementTopPosition('helpicon_' + id)) - 5 + "px";
	document.getElementById("help_div_" + id).style.zIndex = 10;
	
	document.getElementById("help_iframe_" + id).style.left = document.getElementById("help_div_" + id).style.left;
	document.getElementById("help_iframe_" + id).style.top = document.getElementById("help_div_" + id).style.top;
	document.getElementById("help_iframe_" + id).style.zIndex = 9;
	document.getElementById("help_iframe_" + id).style.backgroundColor = '#000000';
	document.getElementById("help_iframe_" + id).style.width = document.getElementById("help_div_" + id).offsetWidth;
	document.getElementById("help_iframe_" + id).style.height = document.getElementById("help_div_" + id).offsetHeight;
	
	document.getElementById("help_div_" + id).style.visibility="visible";
	document.getElementById("help_iframe_" + id).style.display="block";
}

function hideHelp(id)
{
	document.getElementById("help_div_" + id).style.visibility="hidden";
	document.getElementById("help_iframe_" + id).style.display="none";
}


function slobCalculateRemainingVolumeAndValue()
{
	if (document.getElementById("pass_inspectionyes").checked)
	{
		var slob_volume = document.getElementById("slob_volume_quantity_hidden").value;
		var volume_measurement = document.getElementById("slob_volume_measurement_hidden").value;
		var slob_value = document.getElementById("slob_value_quantity_hidden").value;
		var value_measurement = document.getElementById("slob_value_measurement_hidden").value;
		
		document.getElementById("remaining_volume").innerHTML = "";
		document.getElementById("remaining_value").innerHTML = "";
		
		answer = document.createTextNode(slob_volume + " " + volume_measurement);
		document.getElementById("remaining_volume").appendChild(answer);
		answer = document.createTextNode(slob_value + " " + value_measurement);
		document.getElementById("remaining_value").appendChild(answer);
	}
	else if (document.getElementById("pass_inspectionno").checked)
	{
		var volume_measurement = document.getElementById("slob_volume_measurement_hidden").value;
		var value_measurement = document.getElementById("slob_value_measurement_hidden").value;
		
		document.getElementById("remaining_volume").innerHTML = "";
		document.getElementById("remaining_value").innerHTML = "";
		
		answer = document.createTextNode("0.00 " + volume_measurement);
		document.getElementById("remaining_volume").appendChild(answer);
		answer = document.createTextNode("0.00 " + value_measurement);
		document.getElementById("remaining_value").appendChild(answer);
	}
	else if (document.getElementById("pass_inspectionpartly").checked)
	{
		slobCalculateRemainingValue();
		slobCalculateRemainingVolume();
		slobCalculateDisposalValue();
	}
}


function slobCalculateRemainingVolume()
{
	var slob_volume = document.getElementById("slob_volume_quantity_hidden").value;
	var disposal_volume = document.getElementById("disposal_volume_quantity").value;
	var measurement = document.getElementById("slob_volume_measurement_hidden").value;
		
	document.getElementById("remaining_volume").innerHTML = "";
	
	if (Number(disposal_volume) <= Number(slob_volume))
	{
		answer = document.createTextNode(Math.round(((slob_volume - disposal_volume)*100)/100) + " " + measurement);
	}
	else
	{
		answer = document.createTextNode("0.00 " + measurement);
	}
	document.getElementById("remaining_volume").appendChild(answer);
}



function slobCalculateRemainingValue()
{
	var slob_volume = document.getElementById("slob_volume_quantity_hidden").value;
	var slob_value = document.getElementById("slob_value_quantity_hidden").value;
	var disposal_volume = document.getElementById("disposal_volume_quantity").value;
	var measurement = document.getElementById("slob_value_measurement_hidden").value;
		
	document.getElementById("remaining_value").innerHTML = "";
	
	if (Number(slob_volume) > 0 && (Number(disposal_volume) <= Number(slob_volume)))
	{
		answer = document.createTextNode(slob_value - (Math.round((slob_value/slob_volume)*disposal_volume*100)/100) + " " + measurement);
	}
	else
	{
		answer = document.createTextNode("0.00 " + measurement);
	}
	document.getElementById("remaining_value").appendChild(answer);
}


function slobCalculateDisposalValue()
{
	var slob_volume = document.getElementById("slob_volume_quantity_hidden").value;
	var slob_value = document.getElementById("slob_value_quantity_hidden").value;
	var disposal_volume = document.getElementById("disposal_volume_quantity").value;
	var measurement = document.getElementById("slob_value_measurement_hidden").value;
	
	
	document.getElementById("disposal_value").innerHTML = "";
	
	if (Number(slob_volume) > 0 && (Number(disposal_volume) <= Number(slob_volume)))
	{
		answer = document.createTextNode((Math.round((slob_value/slob_volume)*disposal_volume*100)/100) + " " + measurement)
	}
	else
	{
		answer = document.createTextNode(slob_value + " " + measurement)
	}
	document.getElementById("disposal_value").appendChild(answer);
}


function getRadioValue(radioObject)
{					
	if (!radioObject)	
	{
		return "no object";
	}
					
	for (i=0; i < radioObject.length; i++)
	{
		alert (radioObject[i].checked);
		if (radioObject[i].checked)
		{
			return radioObject[i].value;
		}
	}
					
	return "";
}

function hrUpdateKnownAsField()
{
	document.getElementById("name").value = document.getElementById("firstName").value + " " + document.getElementById("lastName").value;
}

function goToSlob(totalnum_slobs_displayed)
{
	var slobID = document.getElementById("totalnum_slobs_displayed").value;
	window.location = ("/apps/slobs/index?id=" + slobID + "");
}


function set_notification_for_supplier_submission() // Supplier Complaints Add Form
{
	if (document.getElementById("sp_submitToExtSupplierYes").checked)
	{
		confirm("Important: This will submit the complaint to the Supplier.\n\nIf you are sure please click OK and continue.\n\nOtherwise click Cancel to this message and select No from the radio button.");
	}
}

function qu_supplierIssue_alert()
{
	alert("With this action a Supplier Complaint will be created.  You must fill in all required Supplier fields.");	
}

function qu_customerIssue_alert()
{
	alert("With this action a Customer Complaint will be created.  You must fill in all required Customer fields.");
}

function overwrite_dependencies_supplier_issue_action()  // Overwrite the dependencies for Internal Complaint Supplier Issue Action Field
{
	var d = new Date();

	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();
	
	// For formatting of the date ...
	if(curr_date < 10)
	{
		var curr_date = "0" + curr_date;
	}
	if(curr_month < 10)
	{
		var curr_month = "0" + curr_month;
	}
		
	if (document.getElementById('qu_supplierIssueActionNo').checked)
	{
		var authorToCopy = document.getElementById('author').value
		
		document.getElementById('rootCauses').value = "complaint not actioned";
		document.getElementById('rootCausesAuthor').value = authorToCopy;
		document.getElementById('rootCausesDate').value = curr_date + "/" + curr_month + "/" + curr_year;
				
		document.getElementById('containmentAction').value = "complaint not actioned";
		document.getElementById('containmentActionAuthor').value = authorToCopy;
		document.getElementById('containmentActionDate').value = curr_date + "/" + curr_month + "/" + curr_year;
		
		document.getElementById('possibleSolutions').value = "complaint not actioned";
		document.getElementById('possibleSolutionsAuthor').value = authorToCopy;
		document.getElementById('possibleSolutionsDate').value = curr_date + "/" + curr_month + "/" + curr_year;
		
		document.getElementById('implementedActions').value = "complaint not actioned";
		document.getElementById('implementedActionsAuthor').value = authorToCopy;
		document.getElementById('implementedActionsDate').value = curr_date + "/" + curr_month + "/" + curr_year;
		
		document.getElementById('preventiveActions').value = "complaint not actioned";
		document.getElementById('preventiveActionsAuthor').value = authorToCopy;
		document.getElementById('preventiveActionsDate').value = curr_date + "/" + curr_month + "/" + curr_year;
		
	}
	else
	{
		document.getElementById('rootCauses').value = "";
		document.getElementById('rootCausesAuthor').value = "";
		document.getElementById('rootCausesDate').value = "";
		
		document.getElementById('containmentAction').value = "";
		document.getElementById('containmentActionAuthor').value = "";
		document.getElementById('containmentActionDate').value = "";
		
		document.getElementById('possibleSolutions').value = "";
		document.getElementById('possibleSolutionsAuthor').value = "";
		document.getElementById('possibleSolutionsDate').value = "";
		
		document.getElementById('implementedActions').value = "";
		document.getElementById('implementedActionsAuthor').value = "";
		document.getElementById('implementedActionsDate').value = "";
		
		document.getElementById('preventiveActions').value = "";
		document.getElementById('preventiveActionsAuthor').value = "";
		document.getElementById('preventiveActionsDate').value = "";
	}
}

function open_cc_window(field)
{		
	screenWidth = (screen.width / 2) - 250;
	screenHeight = (screen.height / 2) - 175;
	
	myRef = window.open('/controls/showCC/showCC.php?field=' + field + '','ccusers','width=500,height=420,toolbar=0,resizable=1,location=1,status=1');
	
	myRef.moveTo(screenWidth,screenHeight);
	myRef.focus();
}

function open_chat_window(NTLogon)
{		
	screenWidth = (screen.width / 2) - 250;
	screenHeight = (screen.height / 2) - 175;
	
	myChat = window.open('/apps/chat/first?NTLogon=' + NTLogon + '','chat','width=300,height=500,toolbar=0,resizable=0,location=0,status=0');
	
	myChat.moveTo(screenWidth,screenHeight);
	myChat.focus();
}

// Used by the Permissions App in User Manager
function checkAppPermissions(application, count)
{	
	if(document.getElementById(application).checked==true)
	{
		for(var i=0; i < count.innerHTML; i++)
		{
			document.getElementById(application + i).checked=true;
		}
	}
	else
	{
		for(var i=0; i < count.innerHTML; i++)
		{
			document.getElementById(application + i).checked=false;
		}
	}
}

function hideAppPermissions(application, count)
{	
	if(document.getElementById(application + "View").value == "Hide")
	{
		for(var i=0; i < count.innerHTML; i++)
		{
			document.getElementById("tr" + application + i).style.display="none";
		}
		document.getElementById(application + "minusIcon").style.display = "none"
		document.getElementById(application + "plusIcon").style.display = ""
		document.getElementById(application + "View").value = "Show"
	}
	else
	{
		for(var i=0; i < count.innerHTML; i++)
		{
			document.getElementById("tr" + application + i).style.display="";
		}
		document.getElementById(application + "plusIcon").style.display = "none"
		document.getElementById(application + "minusIcon").style.display = ""
		document.getElementById(application + "View").value = "Hide"
	}
}

function checkPermissions(application)
{
	var count = document.getElementById(application + "_total").innerHTML;
	var allPermissions = true;
	
	for(var i=0; i<count; i++)
	{
		if(document.getElementById(application + i).checked == false)
		{
			allPermissions = false;
		}
	}
	document.getElementById(application).checked = allPermissions;
}


// Below are the functions for the Image Gallery

// function for moving the image thumb nails.
function moveThumbs(moveAction, totalImages, numberOfThumbnails, albumNumber)
{
	var currentNumber = document.getElementById("currentThumbPos" + albumNumber).innerHTML;

	if(moveAction == "right")
	{
		currentNumber++;
		scrollToImage(currentNumber,totalImages,numberOfThumbnails, albumNumber)
	}
	else if(moveAction == "left")
	{
		currentNumber--;
		scrollToImage(currentNumber,totalImages,numberOfThumbnails, albumNumber)
	}
}

// The image that you want to select, 
function scrollToImage(image,totalImages,numberOfThumbnails, albumNumber)
{
	// Check to see where the scroll bar needs to go.
		
	if(image < 2)
	{
		document.getElementById("currentThumbPos" + albumNumber).innerHTML = 2;
	}
	else if(image > totalImages-3)
	{
		document.getElementById("currentThumbPos" + albumNumber).innerHTML = totalImages-3;
	}
	else
	{
		document.getElementById("currentThumbPos" + albumNumber).innerHTML = image;
	}
	
	var lowerThumb = (parseFloat(document.getElementById("currentThumbPos" + albumNumber).innerHTML)) - 2;
	var upperThumb = (parseFloat(document.getElementById("currentThumbPos" + albumNumber).innerHTML)) + 2;
	
	
	for(var i=0; i<totalImages; i++)
	{
		if((i>=lowerThumb) && (i<=upperThumb))
		{
			document.getElementById("album" + albumNumber + "thumb" + i).style.display = "";
		}
		else
		{
			document.getElementById("album" + albumNumber + "thumb" + i).style.display = "none";
		}
	}
}

// Functions to override dependencies in Internal Complaint Groups
function hide_field_manually(fieldName)
{	
	var fieldId = fieldName.name;
	var fieldValue = fieldName.value;
	
	if(fieldValue == "Yes")
	{
		document.getElementById(fieldId + 'DateRow').style.display = "";
	}
	else
	{
		document.getElementById(fieldId + 'DateRow').style.display = "none";
	}
}

function copyItemDescription(someText, someText2, someText3, someText4, someText5, someText6)
{
	document.getElementById(someText2).value = someText;
	
	document.getElementById(someText4).value = someText3;
	
	document.getElementById(someText6).value = someText5;
}

// Pop up for the multiNTLogon
function open_NTLogon_window(field, valueOfField)
{		
	screenWidth = (screen.width / 2) - 250;
	screenHeight = 50;
	
	myRef = window.open('/controls/showNTLogon/showNTLogon.php?field=' + field + '&users=' + valueOfField + '','ccusers','width=500,height=800,toolbar=0,resizable=1,location=1,status=1');
	
	myRef.moveTo(screenWidth,screenHeight);
	myRef.focus();
}

function openSupportPrintWindow(url)
{		
	screenWidth = (screen.width / 2) - 400;
	screenHeight = 50;
	
	myRef = window.open(url,'ccusers','width=800,height=500,scrollbars=1,toolbar=0,resizable=1,location=1,status=1');
	
	myRef.moveTo(screenWidth,screenHeight);
	myRef.focus();
}

// Show hide divs for Traffic Report
function showHideTrafficReport(id, count)
{
	if(document.getElementById("lId" + id).style.display == "")
	{
		document.getElementById("lId" + id).style.display = "none";
		document.getElementById("rId" + id).style.display = "none";
	}
	else
	{
		for(var i=0; i<count; i++)
		{
			document.getElementById("lId" + i).style.display = "none";
			document.getElementById("rId" + i).style.display = "none";
		}
		
		if(document.getElementById("lId" + id).style.display == "none")
		{
			document.getElementById("lId" + id).style.display = "";
			document.getElementById("rId" + id).style.display = "";
		}
	}
}

// To show the subject lines on the support tables
function showHideTableSubject(event, id)
{
	if(event == "show")
	{
		alert();
		document.body.style.cursor='pointer';
		document.getElementById('sID_' + id).style.display='';
		document.getElementById('oID_' + id).style.backgroundColor = 'CC0000';
		
	}
	else if(event == "hide")
	{
		document.body.style.cursor='auto';
		document.getElementById('sID_' + id).style.display='none';
		document.getElementById('oID_' + id).style.backgroundColor = 'FFFFFF';
	}
}

// for the bubble pop ups!

//function showToolTip(e,text)
//{
//	if(document.all)e = event;
//	
//	var obj = document.getElementById('bubble_tooltip');
//	var obj2 = document.getElementById('bubble_tooltip_content');
//	obj2.innerHTML = text;
//	obj.style.display = 'block';
//	var st = Math.max(document.body.scrollTop,document.documentElement.scrollTop);
//	if(navigator.userAgent.toLowerCase().indexOf('safari')>=0)st=0; 
//	var leftPos = e.clientX - 100;
//	if(leftPos<0)leftPos = 0;
//	obj.style.left = leftPos + 'px';
//	obj.style.top = e.clientY - obj.offsetHeight -1 + st + 'px';
//}	
//
//function hideToolTip()
//{
//	document.getElementById('bubble_tooltip').style.display = 'none';
//	
//}

//function openMoreTraffic()
//{
//	if(document.getElementById("plusTraffic").style.display == "")
//	{
//		document.getElementById("plusTraffic").style.display = "none";
//		document.getElementById("minusTraffic").style.display = "";
//		document.getElementById("rIdAll").style.display = "";
//		document.getElementById("lIdAll").style.display = "";
//	}
//	else
//	{
//		document.getElementById("plusTraffic").style.display = "";
//		document.getElementById("minusTraffic").style.display = "none";
//		document.getElementById("rIdAll").style.display = "none";
//		document.getElementById("lIdAll").style.display = "nob";
//	}
//}

// Functions for Rebates
function copy_rebate_details()
{					
	if(document.getElementById("copyDetails").checked == true)
	{				
		
	}
}

function rebates_setCopyTo()
{
	if(document.getElementById('rebateTypenew').checked)
	{
		document.getElementById('copyDetails0').checked = "1";

		document.getElementById('rebateDetailsGroup').style.display = '';
		document.getElementById('rebateDetails2Group').style.display = '';
		document.getElementById('askForApprovalGroupGroup').style.display = '';
		document.getElementById('rebateApprovedGroupGroup').style.display = '';
		document.getElementById('rebateApprovedGroupStep2Group').style.display = '';
		document.getElementById('rebateApprovedGroupStep2YesGroup').style.display = '';
		document.getElementById('inputIntoSAPGroupGroup').style.display = '';
		document.getElementById('soldToPartyAddGroupGroup').style.display = '';
		document.getElementById('soldToPartyAddGroupNoGroup').style.display = '';
		document.getElementById('inputIntoSAPGroupGroup').style.display = '';
		document.getElementById('generalRebateGroupGroup').style.display = '';
		document.getElementById('generalRebateGroupYesGroup').style.display = '';
		document.getElementById('stagedRebateGroupGroup').style.display = '';
		document.getElementById('stagedRebateGroupYesGroup').style.display = '';
		document.getElementById('accrualReminderGroup').style.display = '';
	}
	else
	{
		alert("Please enter a valid rebate number");
	}
}

// End Rebates Functions


// used by the support application to check all boxes when mass delegating tickets (massDelegate.php)
function checkedAll(totalTickets)
{
	for(var i=0; i<totalTickets; i++)
	{
		document.getElementById('checkbox_' + i).checked = document.getElementById('masterCheck').checked;
	}
}

// Updates the URL textbox with in the Help application.
function updateHelpURL()
{
	document.getElementById("url").value="onclick=\"Javascript:window.open('/apps/help/window/helpWindow?type=" + document.getElementById('type').value + "&amp;app=" + document.getElementById('app').value + "','','toolbars=0,menubar=0,location=0,status=no,resizable=1,scrollbars=1, height=500, width=800')\"";
}

// Copies the help link from the index page to memory
function copyLinkToMemory(htmlId)
{
	alert("Copied into memory:\n\n" + document.getElementById(htmlId).innerText);
	
	document.getElementById("holdtext").innerText = document.getElementById(htmlId).innerText;
	Copied = document.getElementById("holdtext").createTextRange();
	Copied.execCommand("Copy");
	window.status='Link is now in memory (' + htmlId + ')!';
}


// This function controls the usefulLinks cell in the header, and the new window opening.
function usefulLinkWindow(currentUrl, store)
{
	if(store == 'add')
	{
		document.getElementById('addLink').style.display = 'none';
		document.getElementById('removeLink').style.display = '';
	}
	else
	{
		document.getElementById('addLink').style.display = '';
		document.getElementById('removeLink').style.display = 'none';
	}
	
	
	height = 310;
	width = 540;
	newWindow = window.open('/apps/usefulLinks/addLink?url=' + currentUrl + '&amp;store=' + store + '','','toolbars=0,menubar=0,location=0,status=no,resizable=0,scrollbars=0, height=310, width=540')
	
	moveY = ((screen.height-height)/2);
	moveX = ((screen.width-width)/2);
	
	newWindow.moveTo(moveX, moveY);
}

var chartsComplete = 0;

//Callback handler method which is invoked after the chart has saved image on server.
function FC_Exported(objRtn)
{ 
	chartsComplete = chartsComplete + 1;
	
	// When all 7 charts have been exported show the Save Complete textbox.
	if(chartsComplete == 6)
	{
		alert("Save Complete!\n\nAll charts have now been saved.");
	}
	
	//chartsComplete = chartsComplete + 1;
	
	//if (objRtn.statusCode == "1")
	//{
//		alert("The chart was successfully saved on server. The file can be accessed from " + objRtn.fileName);
//	}
//	else
//	{
		//alert("The chart could not be saved on server. There was an error. Description : " + objRtn.statusMessage);
	//}
}

function FC_Rendered(objRtn)
{ 
	chart.saveAsImage();
	
	//if (objRtn.statusCode == "1")
	//{
//		alert("The chart was successfully saved on server. The file can be accessed from " + objRtn.fileName);
//	}
//	else
//	{
		//alert("The chart could not be saved on server. There was an error. Description : " + objRtn.statusMessage);
	//}
}

// Functions for Searching Sharepoint
function searchSharepoint(searchString, searchLocation)
{
	var search_location = document.getElementById(searchLocation);
	var search_location_value = encodeURIComponent(search_location.options[search_location.selectedIndex].value);
	
	window.location = "/apps/sharepoint/index?searchString=" + searchString + "&searchLocation=" + search_location_value;
}

function searchSharepointTextboxSubmit(searchString, searchLocation)
{
	var keycode;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	else return true;
	
	var search_location = document.getElementById(searchLocation);
	var search_location_value = encodeURIComponent(search_location.options[search_location.selectedIndex].value);
	
	if (keycode == 13)
	{
	   window.location = "/apps/sharepoint/index?searchString=" + searchString + "&searchLocation=" + search_location_value;
	   return false;
	}
	else
	{
		return true;	
	}
}