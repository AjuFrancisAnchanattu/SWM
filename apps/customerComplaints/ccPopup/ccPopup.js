function addCC(fieldName)
{
	var options = document.getElementById("ccPeople").options;
	
	var cc = new Array();
	
	for( var i = 0; i < options.length; i++)
	{
		cc[i] = options[i].value;
	}
	
	parent.document.getElementById(fieldName).value = cc.join(",");
	parent.LightBox.myCCPopup.hide();
}

function updateDetails(li)
{
	var name = trim(li.firstChild.nextSibling.innerHTML);
	var logon = trim(li.firstChild.innerHTML);
	var email = trim(li.lastChild.innerHTML);
	
	document.getElementById("searchEmployee").value = name;
	document.getElementById("searchEmployee_NTLogon").value = logon;
	document.getElementById("searchEmployee_email").value = email;
	
	addEmployee();
}

function removeEmployee()
{
	RemoveItem();
	
	return false;
}

function addEmployee()
{
	var employee = document.getElementById("searchEmployee").value;
	var email = document.getElementById("searchEmployee_email").value;
	
	if( employee != "" && validate() )
	{
		AddItem( "ccPeople", employee, email );
		
		clearValues();
	}
	
	return false;
}

function clearValues()
{
	document.getElementById("searchEmployee").value = "";
	document.getElementById("searchEmployee_email").value = "";
	document.getElementById("searchEmployee_NTLogon").value = "";
}

function validate()
{
	if( !validateDetails() )
	{
		alert("Employee selected does not exist!");
		return false;
	}
	
	if( !validateList() )
	{
		clearValues();
		return false;
	}
	
	return true;
}

function validateList()
{
	var name = document.getElementById("searchEmployee").value;
	var email = document.getElementById("searchEmployee_email").value;
	
	var options = document.getElementById("ccPeople").options;
	
	for( var i = 0; i < options.length; i++)
	{
		if( options[i].text == name || options[i].value == email )
		{
			return false;
		}
	}
	
	return true;
}

function validateDetails()
{
	var name = document.getElementById("searchEmployee").value;
	var email = document.getElementById("searchEmployee_email").value;
	var logon = document.getElementById("searchEmployee_NTLogon").value;
	
	var ajaxPOST = "name=" + encodeURIComponent( name ) + "&logon=" + encodeURIComponent( logon ) + "&email=" + encodeURIComponent( email );
	
	var ajaxURL = '/apps/customerComplaints/ccPopup/ccPopupValidate';
	
	var http_request = false;
	
	if (window.XMLHttpRequest) 
	{
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) 
		{
			http_request.overrideMimeType('text/html');
		}
	} 
	else if (window.ActiveXObject) 
	{
		try 
		{
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) 
		{
			try 
			{
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (e) 
			{}
		}
	}
	if (!http_request) 
	{
		alert('Cannot create XMLHTTP instance');
		return false;
	}

	http_request.open('POST', ajaxURL, false);
	http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http_request.setRequestHeader("Content-length", ajaxPOST.length);
	http_request.setRequestHeader("Connection", "close");
	http_request.send(ajaxPOST);
	
	var response = http_request.responseText;
	
	if( response == "1" )
	{
		return true;
	}
	else
	{
		alert( response );
		return false;
	}
}

function RemoveItem()
{
	var options = document.getElementById("ccPeople").options;
	
	if( options.selectedIndex > -1 )
	{
		options[ options.selectedIndex ] = null;
		options.selectedIndex = -1;
	}
}

function AddItem(DropdownId, Text, Value)
{            
	var opt = document.createElement("option");

	document.getElementById(DropdownId).options.add(opt);
	
	opt.text = Text;
	opt.value = Value;
}

function trim(str) {
	return str.replace(/^\s*([\S\s]*?)\s*$/, '$1');
}