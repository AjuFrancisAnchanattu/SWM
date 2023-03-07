function Evaluation()
{
	this.addWarnings = function()
	{
		var attachmentWarning = new WarningMessage( RemoteTranslate("attach_warning") , "attachmentWarning" );
		document.getElementById("attachmentRow").insertBefore( attachmentWarning.generateWarningRow() );
		myAttachment = document.getElementsByName("attachmentUpload")[0].onchange = attachmentWarning.show;
		
		if( document.getElementById("submitStatusRow") )
		{
			var saveWarning = new WarningMessage( RemoteTranslate("save_warning") , "saveWarning" );
			document.getElementById("submitStatusRow").parentNode.appendChild( saveWarning.generateWarningRow() );
			function toggleSaveWarning()
			{
				if( document.getElementById("submitStatus0").checked )
				{
					saveWarning.show();
					document.getElementById("submitGroupGroup").getElementsByTagName("input")[0].value = RemoteTranslate("save");
				}
				else
				{
					saveWarning.hide();
					document.getElementById("submitGroupGroup").getElementsByTagName("input")[0].value = RemoteTranslate("submit");
				}
			}
			document.getElementById("submitStatus1").onclick = toggleSaveWarning;
			document.getElementById("submitStatus0").onclick = toggleSaveWarning;
			toggleSaveWarning();
		}
	}
	
	this.setEvaluationType = function()
	{
		var e = document.getElementById("tempCategoryId");
	
		var category = e.options[e.selectedIndex].text.substring(0, 1);
		
		if( category == "D" || category == "M")
		{
			document.getElementById("full8d1").checked = true;
		}
		else
		{
			document.getElementById("full8d0").checked = true;
		}
		
		dependency_full8d();
	}
	
	this.authoriseGoodsReturn = function()
	{
		var notes = document.getElementById("authoriseGoodsReturn_notes").value;
		if( notes == "Type any comments here" )
		{
			notes = "";
		}
		else if( notes.length > 22 && notes.substr(0, 22) == "Type any comments here" )
		{
			notes = notes.substr( 22 );
		}
		
		var ajaxPOST = "complaintId=" + encodeURIComponent( complaintId ) + "&notes=" + encodeURIComponent( notes );
		
		var ajaxURL = '/apps/customerComplaints/ajax/approveGoodsReturn';
		
		var http_request = false;
		
		if (window.XMLHttpRequest) 
		{ // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) 
			{
				// set type accordingly to anticipated content type
				//http_request.overrideMimeType('text/xml');
				http_request.overrideMimeType('text/html');
			}
		} 
		else if (window.ActiveXObject) 
		{ // IE
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
		
		if( http_request.responseText == "1" )
		{
			alert( RemoteTranslate('goods_return_approved_message') );
		}
		else
		{
			alert( RemoteTranslate('evaluation_goods_return_error_message') );
		}
		
		window.location.reload();
	}
	
	
	this.authoriseGoodsDisposal = function()
	{
		var notes = document.getElementById("authoriseGoodsReturn_notes").value;
		if( notes == "Type any comments here" )
		{
			notes = "";
		}
		else if( notes.length > 22 && notes.substr(0, 22) == "Type any comments here" )
		{
			notes = notes.substr( 22 );
		}
		
		var ajaxPOST = "complaintId=" + encodeURIComponent( complaintId ) + "&notes=" + encodeURIComponent( notes );
		
		var ajaxURL = '/apps/customerComplaints/ajax/approveGoodsDisposal';
		
		var http_request = false;
		
		if (window.XMLHttpRequest) 
		{ // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) 
			{
				// set type accordingly to anticipated content type
				//http_request.overrideMimeType('text/xml');
				http_request.overrideMimeType('text/html');
			}
		} 
		else if (window.ActiveXObject) 
		{ // IE
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
		
		if( http_request.responseText == "1" )
		{
			alert( RemoteTranslate('goods_disposal_approved_message') );
		}
		else
		{
			alert( RemoteTranslate('evaluation_goods_return_error_message') );
		}
		
		window.location.reload();
	}
	
	
	this.rejectGoodsReturn = function()
	{
		var notes = document.getElementById("authoriseGoodsReturn_notes").value;
		if( notes == "Type any comments here" )
		{
			notes = "";
		}
		else if( notes.length > 22 && notes.substr(0, 22) == "Type any comments here" )
		{
			notes = notes.substr( 22 );
		}
		
		var ajaxPOST = "complaintId=" + encodeURIComponent( complaintId ) + "&notes=" + encodeURIComponent( notes );
		
		var ajaxURL = '/apps/customerComplaints/ajax/rejectGoodsReturn';
		
		var http_request = false;
		
		if (window.XMLHttpRequest) 
		{ // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) 
			{
				// set type accordingly to anticipated content type
				//http_request.overrideMimeType('text/xml');
				http_request.overrideMimeType('text/html');
			}
		} 
		else if (window.ActiveXObject) 
		{ // IE
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
		
		if( http_request.responseText == "1" )
		{
			alert( RemoteTranslate('goods_return_rejected_message') );
		}
		else
		{
			alert( RemoteTranslate('goods_return_error_message') );
		}
		
		window.location.reload();
	}
	
	
	this.rejectGoodsDisposal = function()
	{
		var notes = document.getElementById("authoriseGoodsReturn_notes").value;
		if( notes == "Type any comments here" )
		{
			notes = "";
		}
		else if( notes.length > 22 && notes.substr(0, 22) == "Type any comments here" )
		{
			notes = notes.substr( 22 );
		}
		
		var ajaxPOST = "complaintId=" + encodeURIComponent( complaintId ) + "&notes=" + encodeURIComponent( notes );
		
		var ajaxURL = '/apps/customerComplaints/ajax/rejectGoodsDisposal';
		
		var http_request = false;
		
		if (window.XMLHttpRequest) 
		{ // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) 
			{
				// set type accordingly to anticipated content type
				//http_request.overrideMimeType('text/xml');
				http_request.overrideMimeType('text/html');
			}
		} 
		else if (window.ActiveXObject) 
		{ // IE
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
		
		if( http_request.responseText == "1" )
		{
			alert( RemoteTranslate('goods_disposal_rejected_message') );
		}
		else
		{
			alert( RemoteTranslate('goods_return_error_message') );
		}
		
		window.location.reload();
	}
	
	
	this.fixLightbox = function()
	{
		document.getElementById("authoriseGoodsReturn").style.backgroundImage = "URL('images/authoriseGoodsReturn_background.png')";
	}
	
	
	/**
	 *	Empties database with popups when we close the form
	 */
	this.unlockForm = function()
	{
		var complaintId = document.getElementById("complaintid").innerHTML;
		
		var ajaxURL = '/apps/customerComplaints/ajax/unlockForm?complaintId=' + encodeURI(complaintId) + '&form=' + 'evaluation';
		
		var ajaxRequest;  
		
		try
		{                
			ajaxRequest = new XMLHttpRequest(); 
		} 
		catch (e)
		{	       
			try
			{                 
				ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");         
			} 
			catch (e) 
			{                
				try
				{                        
					ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");                 
				} 
				catch (e)
				{                         
				 	// Something went wrong                       
				 	window.location = "unsupported.html";                        
					return false;                
				}         
			} 
		}  
		ajaxRequest.open("GET", ajaxURL, true); 
		ajaxRequest.send(null);  
	}
}

var evaluation = new Evaluation();

if( !readonly )
{
	evaluation.addWarnings();
	window.onbeforeunload = evaluation.unlockForm;
}
else
{
	evaluation.fixLightbox();
}