/**
 * @author Daniel Gruszczyk
 * @date 24/01/2011
 */

 
//*****************************************************
//***	CHANGE TO FALSE BEFORE MOVING TO LIVE!!!	***
//*****************************************************
/***/				var DEV = false;					//*
//*****************************************************
//*****************************************************


if( !document.getElementById("complaintValueBase1") )
{
	var valuesReadonly = true;
}
				
//create new lightBox
LightBox.add( "invoice_popup", 
				{ 	
				blockBelow : true,	//blocking everything below
				width : 750,
				height : 500,
				border : "special",
				draggable : false,
				closable : false
				} 
			);
 
/**************************************************************
 **************************************************************
 
	The Complaint class responsible for all user-side
	scripting on complaint form
 
 **************************************************************
 **************************************************************/ 
 
var complaint = 
{
	addWarnings : function()
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
	},
	
	toggleSaveSubmit : function()
	{
		if(document.getElementById("submitStatus0").checked)
		{
			document.getElementById("submitGroupGroup").getElementsByTagName("input")[0].value = RemoteTranslate('save');
		}
		else
		{
			document.getElementById("submitGroupGroup").getElementsByTagName("input")[0].value = RemoteTranslate('submit');
		}
	},
	
	/*****************************************
	 *	invoice values updates
	 *****************************************/
	invoiceValueChanged : function( row, invoiceValue, currency, invoiceValueTotal)
	{
		this.invoice.update_currency( currency );
		
		this.invoice.update_invoiceValue( row, invoiceValue, invoiceValueTotal );
		
		this.invoice.update_totalInvoicesComplaintValue();
		
		this.invoice.update_totalInvoicesValue();
		
		this.invoice.update_complaintValue();
		
		this.invoice.toggleFieldsVisibility();
	},
	 
	additionalCostsChanged : function()
	{
		this.invoice.update_additionalCostsValue();
		
		this.invoice.update_complaintValue();
	},
	
	/*****************************************
	 *	non-invoice values updates
	 *****************************************/
	nonInvoiceCostsChanged : function()
	{
		this.nonInvoice.update();
	},
	
	/*****************************************
	 *	General update values logic
	 *****************************************/
	complaintValueBaseChanged : function()
	{
		this.clearComplaintValue();
		
		this.invoiceBased = document.getElementById("complaintValueBase1").checked;
		
		if( this.invoiceBased )
		{
			this.invoice.toggleFieldsVisibility();
			this.invoice.update_complaintValue();
		}
		else
		{
			this.nonInvoice.update();
			document.getElementById("creditNoteRequestedRow").style.display = "";
		}
	},
	
	updateComplaintValue : function( value, currency)
	{
		if( typeof( value ) != "undefined" && 
			value !="" && 
			!isNaN( value ) && 
			typeof( currency ) != "undefined" && 
			currency != "" && 
			currency != "N/A" )
		{
			document.getElementById("complaintValue").value = toFloat(value);
			document.getElementById("complaintCurrency").value = currency_value(currency);
			
			document.getElementById("complaintValueShow").getElementsByTagName("p")[0].innerHTML = toFloat(value) + " " + currency_text(currency);
		}
		else
		{
			this.clearComplaintValue();
		}
	},
	
	clearComplaintValue : function()
	{
		document.getElementById("complaintValue").value = "";
		document.getElementById("complaintCurrency").value = "";
		
		document.getElementById("complaintValueShow").getElementsByTagName("p")[0].innerHTML = "N/A";
	},
	
	/**
	 *	Creates and opens a popup for given invoice
	 */
	openPopup : function( id )
	{
		var row = id.split("|")[0];
				
		if( !readonly && !valuesReadonly)
		{
			var sapCustomerNo = document.getElementById("sapCustomerNo").value;
			var invoiceNo = document.getElementById( row + "|invoiceNo" ).value;
			var complaintId = document.getElementById("complaintid").innerHTML;
			
			if( isNaN( sapCustomerNo ) || sapCustomerNo == "" || /\s/.test(sapCustomerNo) )
			{
				alert(RemoteTranslate('choose_customer_first'));
				return false;
			}
			
			if(invoiceNo == "" || isNaN( invoiceNo ) || /\s/.test(invoiceNo))
			{
				alert(RemoteTranslate('enter_invoice_number'));
				return false;
			}
			
			if( !this.invoiceIsForCustomer(invoiceNo, sapCustomerNo) )
			{
				alert(RemoteTranslate('invoice_not_for_customer'));
				return false;
			}
			
			if( this.invoiceAlreadyAdded(row,invoiceNo) )
			{
				alert(RemoteTranslate('invoice_already_on_complaint'));
				return false;
			}
			
			if( this.invoice.currency == "N/A" || this.invoice.rowsNo == 1)
			{
				var currency = "NA";
			}
			else
			{
				var currency = currency_text( this.invoice.currency );
			}
			var post = "complaintId=" + complaintId + "&invoiceNo=" + invoiceNo + "&rowNo=" + row + "&currency=" + currency + "&readonly=false&customerNo=" + sapCustomerNo;
		}
		else
		{
			var complaintId = document.getElementById("complaintid").innerHTML;
		
			var invoiceNo = document.getElementById( row + "|invoiceNoReadOnly" ).value;
			
			var post = "complaintId=" + complaintId + "&invoiceNo=" + invoiceNo + "&rowNo=" + row + "&readonly=true";
		}
		
		if( DEV )
		{
			var server = "scapanetdev";
		}
		else
		{
			var server = "scapanet";
		}
		//set address of page to display in lightBox					
		LightBox.invoice_popup.setURL( "http://" + server + "/apps/customerComplaints/invoicePopup/invoicePopup");
		
		LightBox.invoice_popup.setPOST( post );
		
		//set icon to display/hide icon on event
		LightBox.invoice_popup.showMiddle();
			
		return false;
	},
	
	/**
	 *	Closes the popup and displays an error message if needed
	 */
	hidePopup : function( error )
	{
		parent.LightBox.invoice_popup.hide();
		
		if( typeof error != "undefined" && error != "" )
		{
			setTimeout(function(){alert( error );}, 100 );
		}
	},
	
	/**
	 *	Empties database with popups when we close the form
	 */
	unlockForm : function()
	{
		var complaintId = document.getElementById("complaintid").innerHTML;
		
		var ajaxURL = '/apps/customerComplaints/ajax/unlockForm?complaintId=' + encodeURI(complaintId) + '&form=' + 'complaint';
		
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
	},
	
	invoiceAlreadyAdded : function(row, invoiceNo)
	{
		for( var i=0; i<this.invoice.rowsNo; i++ )
		{
			if( i != row )
			{
				var rowInvoiceNo = document.getElementById( i + "|invoiceNo" ).value;
				
				if( rowInvoiceNo == invoiceNo )
				{
					return true;
				}
			}
		}
		
		return false;
	},
	
	/**
	 *	Checks if an invoice belongs to a customer
	 */
	invoiceIsForCustomer : function(invoiceNo, customerNo)
	{
		var ajaxURL = '/apps/customerComplaints/ajax/checkInvoice?invoiceNo=' + invoiceNo + '&customerNo=' + customerNo;
		
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
		
		ajaxRequest.open('GET', ajaxURL, false);
		ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxRequest.setRequestHeader("Connection", "close");
		ajaxRequest.send(null);
		
		var response = ajaxRequest.responseText;
		
		if( response == 1 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/******************************************************
 *	This is executed only if form is in full edit mode!
 ******************************************************/
if( !readonly )
{
	complaint.addWarnings();
	
	/**
	 *	adding a listener to 'unlock' the form when user navigates away
	 */
	window.onbeforeunload = complaint.unlockForm;
	
	/**
	 *	Remove the big border under invoice number multigroup
	 */
	document.getElementById( "sapInvoiceNoGroupGroup" ).lastChild.firstChild.style.borderBottom = "#efefef 2px solid";
	
	/**
	 *	Rename 'Add' button for invoices
	 */
	document.getElementById( "sapInvoiceNoGroupGroup" ).lastChild.firstChild.firstChild.firstChild.value = RemoteTranslate('add_new_invoice');
	
	/**
	 *	Remove the big border under grouped complaint multigroup
	 */
	document.getElementById( "groupedComplaintIdGroupGroup" ).lastChild.firstChild.style.borderBottom = "#efefef 2px solid";
	
	/**
	 *	Rename 'Add' button for grouped complaints
	 */
	document.getElementById( "groupedComplaintIdGroupGroup" ).lastChild.firstChild.firstChild.firstChild.value = RemoteTranslate('add_new_grouped_complaint');
	
	if( !valuesReadonly )
	{
		/**
		 *	parsing and formatting string to float with 2 decimal places
		 */
		function toFloat( value )
		{
			return parseFloat(value).toFixed(2);
		}

		/**
		 *	gets a value from a given field
		 *	if field is empty or does not exist or value in the field is not a number
		 *	returns 0.0
		 */
		function getValue( fieldId )
		{
			if( document.getElementById( fieldId ) && document.getElementById( fieldId ).value != "" && !isNaN(document.getElementById( fieldId ).value) )
				return parseFloat( document.getElementById( fieldId ).value );
			else
				return 0.00;
		}

		/**
		 *	Gets currency from a given field
		 */
		function getCurrency( fieldId )
		{
			return currency_value( document.getElementById( fieldId ).value );
		}

		/**
		 *	Creates an array of currencies and their integer values
		 */
		var currencyArray = (
				function()
				{
					var e = document.getElementById("nonInvoiceCosts_measurement");
					var currencyArray = {};
					for( var i = 0; i < e.options.length; i++ )
					{
						currencyArray[ e.options[ i ].value ] = e.options[ i ].text;
						currencyArray[ e.options[ i ].text ] = e.options[ i ].value;
					}
					
					return currencyArray;
				}
			)();
		
		/**
		 *	returns an integer value of a currency
		 */
		function currency_value( currency )
		{
			if( typeof currency == "undefined" || 
				currency == "" || 
				currency == "N/A" || 
				typeof currencyArray[ currency ] == "undefined" )
			{
				return "N/A";
			}
			
			if( isNaN( currency ) )
			{
				return currencyArray[ currency ];
			}
			else
			{
				return currency;
			}
		}

		/**
		 *	returns a display text of a given currency
		 */
		function currency_text( currency )
		{
			if( typeof currency == "undefined" || 
				currency == "" || 
				currency == "N/A" || 
				typeof currencyArray[ currency ] == "undefined")
			{
				return "";
			}
			
			if( isNaN( currency ) )
			{
				return currency;
			}
			else
			{
				return currencyArray[ currency ];
			}
		}
		
		/**
		 *	Check if initially (on load of the form) complaint is invoice or non-invoice based
		 */
		complaint.invoiceBased = document.getElementById("complaintValueBase1").checked;
		
		/**
		 *	Adds invoice based controlling object to the complaint
		 */
		complaint.invoice = 
		{
			_field_rowShow : "|invoiceValueShow",
			_field_rowTotalShow : "|invoiceValueTotalShow",
			_field_rowValue : "|invoiceValue",			
			_field_rowValueTotal : "|invoiceValueTotal",	
			_field_additionalCosts : "additionalCosts",
			_field_totalInvoicesShow : "totalInvoicesValueShow",
			_field_totalInvoicesShowComplaint : "totalInvoicesValueShowComplaint",
			_field_totalInvoicesValue : "totalInvoicesValue",
			_field_totalInvoicesValueComplaint : "totalInvoicesValueComplaint",
			
			creditNoteRequested : true,
			currency : "N/A",
			value : 0.00,
			totalInvoicesValueComplaint : 0.00,
			totalInvoicesValue : 0.00,
			additionalCostsValue : 0.00,
			rows : { 0 : 0.00 },
			rowsTotal : {0 : 0.00 },
			rowsNo : 1,
			
			update_currency : function( newCurrency )
			{
				this.currency = currency_value(newCurrency);
			},
			
			update_invoiceValue : function( row, value, valueTotal )
			{
				if( value == "" || typeof value == "undefined" || isNaN( value ) )
				{
					this.rows[ row ] = toFloat( 0.00 );
					this.rowsTotal[ row ] = toFloat( 0.00 );
				}
				else
				{
					this.rows[ row ] = toFloat( value );
					this.rowsTotal[ row ] = toFloat( valueTotal );
				}
				document.getElementById( row + this._field_rowValue ).value = this.rows[ row ];
				document.getElementById( row + this._field_rowValueTotal ).value = this.rowsTotal[ row ];
				
				var show = document.getElementById( row + this._field_rowShow ).getElementsByTagName("p")[0];				
				show.innerHTML = toFloat(this.rows[ row ]) + " " + currency_text( this.currency );
				
				var totalShow = document.getElementById( row + this._field_rowTotalShow ).getElementsByTagName("p")[0];				
				totalShow.innerHTML = toFloat(this.rowsTotal[ row ]) + " " + currency_text( this.currency );
			},
			
			update_totalInvoicesComplaintValue : function()
			{
				var total = 0.00;
				
				for( var i = 0; i < this.rowsNo; i++)
				{
					total = parseFloat(total) + parseFloat(this.rows[ i ]);
				}
				
				if( isNaN( total ) )
				{
					this.totalInvoicesValueComplaint = toFloat(0.00);
				}
				else
				{
					this.totalInvoicesValueComplaint = toFloat( total );
				}
				
				if( this.totalInvoicesValueComplaint == 0.00 )
				{
					document.getElementById(this._field_totalInvoicesValueComplaint).value = "";
				}
				else
				{
					document.getElementById(this._field_totalInvoicesValueComplaint).value = this.totalInvoicesValueComplaint;
				}
				
				var show = document.getElementById(this._field_totalInvoicesShowComplaint).getElementsByTagName("p")[0];
				
				show.innerHTML = toFloat(this.totalInvoicesValueComplaint) + " " + currency_text( this.currency );
			},
			
			update_totalInvoicesValue : function()
			{
				var total = 0.00;
				
				for( var i = 0; i < this.rowsNo; i++)
				{
					total = parseFloat(total) + parseFloat(this.rowsTotal[ i ]);
				}
				
				if( isNaN( total ) )
				{
					this.totalInvoicesValue = toFloat(0.00);
				}
				else
				{
					this.totalInvoicesValue = toFloat( total );
				}
				
				if( this.totalInvoicesValue == 0.00 )
				{
					document.getElementById(this._field_totalInvoicesValue).value = "";
				}
				else
				{
					document.getElementById(this._field_totalInvoicesValue).value = this.totalInvoicesValue;
				}
				
				var show = document.getElementById(this._field_totalInvoicesShow).getElementsByTagName("p")[0];
				
				show.innerHTML = toFloat(this.totalInvoicesValue) + " " + currency_text( this.currency );
			},
			
			update_additionalCostsValue : function()
			{
				this.additionalCostsValue = getValue( this._field_additionalCosts );
			},
			
			update_complaintValue : function()
			{
				if( this.currency == "N/A" )
				{
					this.value = toFloat(0.00);
				}
				else
				{
					this.value = toFloat( parseFloat( this.totalInvoicesValueComplaint ) + parseFloat( this.additionalCostsValue ) );
				}
				
				complaint.updateComplaintValue( this.value, this.currency );
			},
			
			toggleFieldsVisibility : function()
			{
				if( this.currency == "N/A" )
				{
					document.getElementById(this._field_additionalCosts + "Row").style.display = "none";
					document.getElementById(this._field_additionalCosts + "CommentRow").style.display = "none";
					document.getElementById(this._field_totalInvoicesShow + "Row").style.display = "none";
					
					document.getElementById(this._field_additionalCosts).value = "";
					document.getElementById(this._field_additionalCosts + "Comment").value = "";
					
					document.getElementById("creditNoteRequestedRow").style.display = "none";
				}
				else
				{
					document.getElementById("additionalCostsRow").style.display = "";
					document.getElementById("additionalCostsCommentRow").style.display = "";
					document.getElementById("totalInvoicesValueShowRow").style.display = "";
					document.getElementById("totalInvoicesValueShowComplaintRow").style.display = "";
					
					document.getElementById("creditNoteRequestedRow").style.display = "";
				}
			},
			
			countRows : function()
			{
				var row = 0;
				while( document.getElementById( row + this._field_rowValue))
				{
					row++;
				}
				
				return row;
			},
			
			load : function()
			{
				this.update_currency( getCurrency("complaintCurrency") );
			
				this.rowsNo = this.countRows();
				
				for( var row = 0; row < this.rowsNo; row++)
				{
					this.update_invoiceValue( row, getValue( row + this._field_rowValue), getValue( row + this._field_rowValueTotal));
				}
				
				this.update_totalInvoicesComplaintValue();
				
				this.update_totalInvoicesValue();
				
				this.update_additionalCostsValue();
				
				this.update_complaintValue();
				
				this.toggleFieldsVisibility();
			}
		};
		
		complaint.nonInvoice = 
		{
			_field_currency : "nonInvoiceCosts_measurement",
			_field_value : "nonInvoiceCosts_quantity",
				
			creditNoteRequested : true,
			currency : "N/A",
			value : 0.00,
			
			update : function()
			{
				this.currency = currency_value( getCurrency( this._field_currency ) );
				this.value = getValue( this._field_value );
				
				complaint.updateComplaintValue( this.value, this.currency );
			},
			
			load : function()
			{
				this.update();
			}
		};
		
		if( complaint.invoiceBased )
		{
			complaint.invoice.load();
		}
		else
		{
			complaint.nonInvoice.load();
		}
	}
	else
	{
		/**
		 *	Removes 'Remove' and 'Add' buttons from multigroup
		 */
		var maxRow = multiGroupRowCount();
				
		//loop through all rows in multigroup
		for( var row = 0; row < maxRow; row++)
		{
			//thats TD where the button is
			var myTd = document.getElementById( row + "|invoiceValueShowRow").nextSibling.nextSibling.getElementsByTagName("td")[0];

			//thats the button to remove
			var myButton = myTd.getElementsByTagName("input")[0];
			
			//and removal operation
			myTd.removeChild( myButton);
		}
		
		//removing the whole TD with button 'Add'
		document.getElementById( maxRow + "|invoiceValueShowRow").nextSibling.nextSibling.getElementsByTagName("td")[0].innerHTML = "";
	}
	
	setInvoicesAutocompleteURLs();
}

function openCurrencyPopup( fieldId)
{
	var div = document.getElementById( fieldId ).lastChild.firstChild;
	
	var txtValue = div.getElementsByTagName("p")[0].innerHTML;
	if( txtValue.split(" ").length > 1 )
	{
		var value = txtValue.split(" ")[0];
		var currency = txtValue.split(" ")[1].split("<")[0];
			
		LightBox.complaint_currencyCalculator.hide();
		
		//set address of page to display in lightBox					
		LightBox.complaint_currencyCalculator.setURL( "http://scapanetdev/apps/customerComplaints/lib/currencyConverter/currencyConverter");
		LightBox.complaint_currencyCalculator.setPOST( "currency=" + currency + "&amp;exchangeRatesType=budget&amp;value=" + value );
		
		LightBox.complaint_currencyCalculator.reset();
		
		//set icon to display/hide icon on event
		LightBox.complaint_currencyCalculator.show_DOM(fieldId , "Y");
	}
}

function addCurrencyPopup( fieldId)
{
	if( document.getElementById(fieldId) )
	{
		var div = document.getElementById(fieldId).lastChild.firstChild;
		
		var newImg = document.createElement("img");
		newImg.className = "currencyPopupLink noPrint";
		newImg.src = "../../images/famIcons/calculator.png";
		newImg.alt = RemoteTranslate('currency_calculator');		
		
		var newLabel = document.createElement("label");
		newLabel.className = "currencyPopupLinkLabel noPrint";
		newLabel.innerHTML = RemoteTranslate('currency_calculator');
				
		newImg.onclick = function() { openCurrencyPopup( fieldId ); };
		newLabel.onclick = function() { openCurrencyPopup( fieldId ); };			
		
		div.insertBefore( newLabel, div.getElementsByTagName("p")[0] );
		div.insertBefore( newImg, div.getElementsByTagName("label")[0] );
	}
}

function multiGroupRowCount()
{
	var row = 1;
	
	while( document.getElementById( row + "|invoiceValueShowRow"))
	{
		row++;
	}
	
	row --;
	
	return row;
}

function addCalculatorLinks()
{
	var lightBoxStyle = 
	{ 	
		blockBelow : false,	//not blocking anything below
		width: 225,
		height: 215,
		border : "special",
		draggable : true
	};
	
	LightBox.add( "complaint_currencyCalculator", lightBoxStyle );
	
	var maxRow = multiGroupRowCount();
	if( maxRow > 0 )
	{
		for( var i = 0; i <= maxRow ; i++ )
		{
			addCurrencyPopup( i + "|invoiceValueShowRow");
			addCurrencyPopup( i + "|invoiceValueTotalShowRow");
		}
	}
	
	addCurrencyPopup( "totalInvoicesValueShowRow" );
	addCurrencyPopup( "totalInvoicesValueShowComplaintRow" );
	
	if( readonly || valuesReadonly)
	{
		addCurrencyPopup( "additionalCostsRow" );
	}
	addCurrencyPopup( "complaintValueShowRow" );
}

addCalculatorLinks();