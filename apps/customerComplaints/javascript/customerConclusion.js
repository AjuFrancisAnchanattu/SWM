 /**
  * @author Daniel Gruszczyk
  * @date 24/01/2011
  */

function Conclusion() 
{
	//create new lightBox
	var lightBoxStyle = 
		{ 	
			blockBelow : false,	//not blocking anything below
			width: 225,
			height: 215,
			border : "special",
			draggable : true
		};
	
	LightBox.add( "conclusion_currencyCalculator", lightBoxStyle );
	
	this.formatFields = function()
	{
		this.format( "invoicesValueRORow" );
		
		this.format( "invoicesValueComplaintRORow" );
		
		this.format( "additionalCostsRORow" );
		
		this.format( "creditNoteValueRow" );
	}
	
	this.format = function( fieldRow )
	{
		if( document.getElementById( fieldRow ) )
		{
			var id = fieldRow.slice(0, -3);
			document.getElementById( fieldRow ).getElementsByTagName("td")[1].getElementsByTagName("div")[0].id = id;
			this.addPopupLink( id );
		}
	}
	
	this.addPopupLink = function( fieldId )
	{
		var div = document.getElementById(fieldId);
			
		var newImg = document.createElement("img");
		newImg.className = "currencyPopupLink noPrint";
		newImg.src = "../../images/famIcons/calculator.png";
		newImg.alt = RemoteTranslate('currency_calculator');		
		
		var newLabel = document.createElement("label");
		newLabel.className = "currencyPopupLinkLabel noPrint";
		newLabel.innerHTML = RemoteTranslate('currency_calculator');
				
		newImg.onclick = function() { conclusion.openCurrencyPopup( fieldId ); };
		newLabel.onclick = function() { conclusion.openCurrencyPopup( fieldId ); };			
		
		div.insertBefore( newLabel, div.getElementsByTagName("p")[0] );
		div.insertBefore( newImg, div.getElementsByTagName("label")[0] );
	}
	
	this.openCurrencyPopup = function( fieldId )
	{
		var div = document.getElementById( fieldId );
			
		var txtValue = div.getElementsByTagName("p")[0].innerHTML;
		var value = txtValue.split(" ")[0];
		var currency = txtValue.split(" ")[1].split("<")[0];
			
		LightBox.conclusion_currencyCalculator.hide();
		
		//set address of page to display in lightBox					
		LightBox.conclusion_currencyCalculator.setURL( "http://scapanetdev/apps/customerComplaints/lib/currencyConverter/currencyConverter");
		LightBox.conclusion_currencyCalculator.setPOST( "currency=" + currency + "&amp;exchangeRatesType=budget&amp;value=" + value );
		
		LightBox.conclusion_currencyCalculator.reset();
		
		//set icon to display/hide icon on event
		LightBox.conclusion_currencyCalculator.show_DOM(fieldId, "XY");
	}
	
	
	this.removeAddBorder = function()
	{
		document.getElementById("sapReturnNoGroupGroup").lastChild.getElementsByTagName("td")[0].style.border= "none";
	}
	
	
	this.rollbackApproval = function($complaintId)
	{
		if (confirm(RemoteTranslate('conclusion_rollback_approval_warning')))
		{
			window.location = 'edit?complaintId=' + $complaintId + '&amp;stage=conclusion&amp;approvalRollback=true';
		}
	}
	
	
	/**
	 *	Empties database with popups when we close the form
	 */
	this.unlockForm = function()
	{
		var complaintId = document.getElementById("complaintid").innerHTML;
		
		var ajaxURL = '/apps/customerComplaints/ajax/unlockForm?complaintId=' + encodeURI(complaintId) + '&form=' + 'conclusion';
		
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

	this.addUnlockEvent = function()
	{
		window.onbeforeunload = this.unlockForm;
	}
	
	this.addWarnings = function()
	{
		var attachmentWarning = new WarningMessage( RemoteTranslate("attach_warning") , "attachmentWarning" );
		document.getElementById("attachmentRow").insertBefore( attachmentWarning.generateWarningRow() );
		myAttachment = document.getElementsByName("attachmentUpload")[0].onchange = attachmentWarning.show;
	}
	
	this.onloadFunction = function()
	{
		// Fix Dependencies
		if (document.getElementById("returnsReceived0") != null)
		{
			if (document.getElementById("returnsReceived0").checked == true)
			{
				document.getElementById("returnsReceived0").click();
			}
		}
		
		// Make measurement dropdown wider
		if (document.getElementById("returnQuantityReceived_measurement") != null)
		{
			document.getElementById("returnQuantityReceived_quantity").style.width = "124px";
			document.getElementById("returnQuantityReceived_measurement").style.width = "118px";
		}
	}
}

var conclusion = new Conclusion();

conclusion.formatFields();

if( !readonly )
{
	conclusion.removeAddBorder();
	conclusion.addUnlockEvent();
	conclusion.addWarnings();
}

document.onload = conclusion.onloadFunction();