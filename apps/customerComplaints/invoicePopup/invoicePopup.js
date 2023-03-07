/**************************************************************
 **************************************************************
 
	The Popup class is responsible for managing data 
	for invoices popup
 
 **************************************************************
 **************************************************************/

 /**
  * @author Daniel Gruszczyk
  * @date 24/01/2011
  */
function InvoicePopup( cId, iNo, rNo, pReadonly)
{
	/**
	 *	Variables needed for correct data handling/display
	 */
	this.complaintId = cId;
	this.invoiceNo = iNo;
	this.readonly = pReadonly;
	this.row = rNo;
	
	/**
	 *	Variables needed for Ajax requests
	 */
	this.http_request = null;
	this.ajaxURL = null;
	this.ajaxPOST = null;
	this.ajaxAction = null;
	this.ajaxResponse = null;
	
	/**
	 *	Object of UI class
	 */
	this.ui = new UI();
	
	/**
	 *	Closes the popup (asks user first)
	 */
	this.close = function()
	{
		if( this.readonly || confirm(RemoteTranslate('popup_close_warning')))
		{
			//***********************************
			//	Interface to parent page!!!
			parent.complaint.hidePopup();
			//***********************************
		}
	}
	
	/**
	 *	Forcing the close of the popup
	 */
	this.forceClose = function()
	{
		//***********************************
		//	Interface to parent page!!!
		parent.complaint.hidePopup();
		//***********************************
	}
	
	/**
	 *	Refreshes the table with invoices
	 */
	this.refresh = function()
	{
		//*****************************
		//	Reload content of iframe!!!
		self.location.reload();
		//*****************************
	}
	
	/**************************************************
	 *	SAVE FUNCTIONS
	 **************************************************/
	 
	/**
	 *	Saves the form
	 */
	this.save = function()
	{
		//if there are no rows selected to save
		//let user know, and stop saving operation
		if(this.selectedCount() == 0)
		{
			alert(RemoteTranslate('popup_select_one'));
			return;
		}
		
		//validate form
		if(this.validateForm() == true)
		{
			//if form is valid, get values to save
			this.save_prePOST();
			
			//call ajax here
			this.makePOSTRequest();
		}
		else
		{
			//if form is not valid, let user know
			alert(RemoteTranslate('popup_form_error'));
		}
	}
	
	/**
	 *	creates string with values to be passed to ajax file
	 *	adds complaintId, invoiceNo and then a 2d array with values to be saved
	 *	the array is in a form of a string, each row is separated from another by ;
	 *	and individual values in a row are separated by ,
	 */
	this.save_prePOST = function()
	{
		var values = "";
		
		//get all checkboxes on the page
		var checkboxes = document.getElementsByName("save_invoice");
		
		var totalInvoiceValueObj = document.getElementById("totalInvoiceValue");

		var totalInvoiceValue = totalInvoiceValueObj.value;
		
		//loop through all checkboxes
		for( var i=0; i<checkboxes.length; i++)
		{
			//again row number on form is number of checkbox in array plus 1
			var j = i+1;
			
			//get individual checkbox for the row
			var chkbox = checkboxes.item(i);
						
			//and if it is checked, add values from that row to the array
			if( chkbox.checked == true)
			{
				//invoicesId is unique id of a row in SAP->invoices table
				//that will be in array [0] index after exploding the string
				var field = document.getElementById("invoicesId_" + j);
				values += field.innerText + ",";
				
				//array[1]
				field = document.getElementById("batch_" + j);
				values += field.value + ",";
				
				//array[2]
				field = document.getElementById("deliveryQuantity_" + j);
				values += field.value + ",";
				
				//array[3]
				field = document.getElementById("uom_" + j);
				values += field.innerHTML + ",";
				
				//array[4]
				field = document.getElementById("netValueItem_" + j);
				values += field.value + ",";
				
				//array[5]
				field = document.getElementById("netValueItem_currency_" + j);
				values += field.innerHTML + ";";
			}
			else
			{
				field = document.getElementById("netValueItem_" + j);
			}			
		}
		
		//get rid of last comma
		values = values.substring(0, values.length -1);
		
		//put everything together and encode it
		this.ajaxPOST = "complaintId=" + encodeURI( this.complaintId ) +
			"&invoiceId=" + encodeURI( this.invoiceNo ) + 
			"&row=" + encodeURI( this.row ) + 
			"&totalInvoiceValue=" + totalInvoiceValue + 
			"&values=" + encodeURI( values );
			
		this.ajaxURL = '/apps/customerComplaints/invoicePopup/invoicePopupSave';
		
		this.ajaxAction = 'save';
	}
	
	/**************************************************
	 *	RESET FUNCTIONS
	 **************************************************/
	 
	/**
	 *	Resets the form
	 */
	this.reset = function()
	{
		//some warning text
		if(this.selectedCount() == 0)
		{	
			//if there are no checkboxes selected
			var warning = RemoteTranslate('popup_reset_all_warning');
		}
		else
		{
			//if there are some checkboxes selected
			var warning = RemoteTranslate('popup_reset_selected_warning');
		}
		
		//display confirm dialog and get users answer
		var answer = confirm( warning);
		
		if ( answer)
		{
			//only if user wish to continue
			if( this.selectedCount() == 0 )
			{
				//we either get POST values to reset all table
				this.resetAll_prePOST();
			}
			else
			{
				//or just selected rows
				this.resetSelected_prePOST();
			}
			
			//starting ajax
			this.makePOSTRequest();
		}
	}

	/**
	 *	that gets parameters for php file to reset all rows in table
	 */
	this.resetAll_prePOST = function()
	{
        var totalInvoiceValueObj = document.getElementById("totalInvoiceValue");

		var totalInvoiceValue = totalInvoiceValueObj.value;
        
		//all we need to pass here is complaintId and invoiceNo
		//so we encode it and put into one string
		this.ajaxPOST = "complaintId=" + encodeURI( this.complaintId ) +
			"&invoiceId=" + encodeURI( this.invoiceNo ) + "&totalInvoiceValue=" + totalInvoiceValue;
		
		this.ajaxURL = '/apps/customerComplaints/invoicePopup/invoicePopupReset';
			
		this.ajaxAction = 'reset';
	}

	/**
	 *	that gets param for php file to reset only certain rows in table
	 *	here, unlike in previous function, as well as passing invoiceNo and complaintId, 
	 *	we need to pass id's of each individual invoice row from SAP->invoices table
	 */
	this.resetSelected_prePOST = function()
	{
		var values = "";
        
        var totalInvoiceValueObj = document.getElementById("totalInvoiceValue");

		var totalInvoiceValue = totalInvoiceValueObj.value;
		
		//we get array of all checkboxes
		var checkboxes = document.getElementsByName("save_invoice");
		
		//now we check every checkbox
		for( var i=0; i<checkboxes.length; i++)
		{
			//row number in table is always checkBox number in array plus 1
			var j = i+1;
			
			//now we get individual checkbox from table
			var chkbox = checkboxes.item(i);
			
			//we check if it is checked
			if( chkbox.checked == true)
			{
				//if it is, we add id from invoices table to values to be reset
				values += document.getElementById("invoicesId_" + j).innerText + ",";
			}
		}
		
		//we get rid of last ',' 
		//(so in php file when we explode string, we wont have one empty cell at the end)
		values = values.substring(0, values.length -1);
		
		//now we encode and put together all values we want to pass to php file
		this.ajaxPOST = "complaintId=" + encodeURI( this.complaintId ) +
			"&invoiceId=" + encodeURI( this.invoiceNo ) +
			"&values=" + encodeURI(values) + 
            "&totalInvoiceValue=" + totalInvoiceValue;
			
		this.ajaxURL = '/apps/customerComplaints/invoicePopup/invoicePopupReset';
			
		this.ajaxAction = 'reset';
	}
	
	/**************************************************
	 *	DEBUG FUNCTIONS
	 **************************************************/
	 
	/**
	 *	Sends debug email
	 */
	this.debug = function()
	{
		alert(RemoteTranslate('popup_admin_error'));
		
		this.debug_prePOST();
		
		this.makePOSTRequest();
	}

	/**
	 *	Create POST string for debug function
	 */
	this.debug_prePOST = function()
	{
		var ajaxPOST_tmp = "complaintId=" + encodeURIComponent( this.complaintId ) +
			"&invoiceId=" + encodeURIComponent( this.invoiceNo ) +
			"&action=" + encodeURIComponent(this.ajaxAction) + 
			"&url=" + encodeURIComponent(this.ajaxURL) +
			"&extraMessage=" + encodeURIComponent(this.extraMessage) + 
			"&ajaxPost=" + encodeURIComponent(this.ajaxPOST) + 
			"&ajaxResponse=" + encodeURIComponent(this.ajaxResponse);
		
		this.ajaxPOST = ajaxPOST_tmp;
		
		this.ajaxURL = '/apps/customerComplaints/invoicePopup/invoicePopupDebug';
		
		this.ajaxAction = 'debug';
	}

	/**************************************************
	 *	UPDATE FUNCTIONS
	 **************************************************/
	 
	/**
	 *	Updating values
	 */
	this.updateComplaintValues = function( values )
	{
		var invoiceValue = values[1];
		var invoiceValueTotal = values[3];
		var invoiceCurrency = values[2];
		
		//****************************************************************************
		//	Interface to parent page!!!
		parent.complaint.invoiceValueChanged( this.row, invoiceValue, invoiceCurrency, invoiceValueTotal);
		//****************************************************************************
	}

	this.updateRowValue = function( rowNo )
	{
		var calc = parseFloat(document.getElementById( "calc_" + rowNo ).innerHTML);
		var quantity = parseFloat(document.getElementById( "deliveryQuantity_" + rowNo ).value);
		var newValue = calc * quantity;
		
		document.getElementById( "netValueItem_" + rowNo ).value = newValue.toFixed(2);
	}
	
	/**************************************************
	 *	VALIDATE FUNCTIONS
	 **************************************************/
	 
	 
	this.isNumber = function(o) 
	{
		return !isNaN(o-0); 
	} 
	 
	/**
	 *	validates all selected rows on the form
	 *	before it can be saved
	 */
	this.validateForm = function()
	{
		var valid = true;
		
		var checkboxes = document.getElementsByName("save_invoice");
		
		for( var i=0; i<checkboxes.length; i++)
		{
			var j = i+1;
			var chkbox = checkboxes.item(i);
			
			if( chkbox.checked == true)
			{
				var field = document.getElementById("batch_" + j);
				if( this.validate(field, j) == false)
				{
					valid = false;
				}
				
				field = document.getElementById("deliveryQuantity_" + j);
				if( this.validate(field, j) == false)
				{
					valid = false;
				}
				
				field = document.getElementById("netValueItem_" + j);
				if( this.validate(field, j) == false)
				{
					valid = false;
				}
			}
		}
		
		return valid;
	}
	
	/**
	 *	validate a filed in a given row
	 */
	this.validate = function(field, rowNo)
	{
		//get value of a field
		var value = field.value;
		
		var maxValue = parseFloat(document.getElementById( "maxValue_" + rowNo ).innerHTML);
		var maxQuantity = parseFloat(document.getElementById( "maxQuantity_" + rowNo ).innerHTML);
		
		//check waht field is that
		if( field.id == "batch_" + rowNo)
		{
			var regex = /[^\w\s\-_]/;
			
			var result = !regex.test(value);
		}
		else
		{
			//validate decimal (with optional 2 decimal places)
			var regex = /^(\d|[1-9]\d*)(\.(\d\d?))?$/;
			
			//test the value against regex
			var result = regex.test(value);
			
			if( result )
			{
				result = this.isNumber(value);
			}
			
			//checking max values if regex validation is correct
			if( result )
			{
				if( field.id == "deliveryQuantity_" + rowNo)
				{
					if( value > maxQuantity )
					{
						result = false;
					}
				}
				else if( field.id == "netValueItem_" + rowNo)
				{
					if( value > maxValue )
					{
						result = false;
					}
				}
			}
		}
		
		//depends on the validation results
		//check/uncheck the checkbox for saving
		//mark/unmark field as 'invalid'
		if( !result )
		{
			field.className = 'input_invalid';
			document.getElementById("save_invoice_" + rowNo).checked = false;
			return false;
		}
		else
		{
			field.className = '';
			document.getElementById("save_invoice_" + rowNo).checked = true;
			return true;
		}
	}
	
	/**************************************************
	 *	SUPPORT FUNCTIONS
	 **************************************************/
	 
	/**
	 *	checks how many checkboxes are selected
	 */
	this.selectedCount = function()
	{
		var count = 0;
		var checkboxes = document.getElementsByName("save_invoice");
		
		for( var i=0; i<checkboxes.length; i++)
		{
			var chkbox = checkboxes.item(i);
			
			if( chkbox.checked == true)
			{
				count++;
			}
		}
		
		return count;
	}

	/**************************************************
	 *	AJAX FUNCTIONS
	 **************************************************/
	 
	/**
	 *	this is an event listener, once we get data back from ajax call
	 *	this will be called to handle that data
	 */
	this.processResponse = function() 
	{
		if (this.http_request.readyState == 4) 
		{
			if (this.http_request.status == 200) 
			{
				//ajax result
				this.ajaxResponse = this.http_request.responseText;
				
				switch( this.ajaxAction)
				{
					case "save":
						var ajaxResponseArray = this.ajaxResponse.split("|");
						if( ajaxResponseArray[0] == "1")	//saved
						{
							alert(RemoteTranslate('popup_save_success'));
							this.updateComplaintValues( ajaxResponseArray );
							this.forceClose();
						}
						else
						{
							this.extraMessage = "wrong ajaxResponse for action";
							this.debug();
						}
						break;
					
					case "reset": //reset
						var ajaxResponseArray = this.ajaxResponse.split("|");
						if( ajaxResponseArray[0] == "1")
						{
							alert(RemoteTranslate('popup_reset_success'));
							this.updateComplaintValues( ajaxResponseArray );
							this.refresh();
						}
						else
						{
							this.extraMessage = "wrong ajaxResponse for action";
							this.debug();
						}
						break;
					
					case "debug":	//debug
						this.forceClose();
						break;
						
					default:
						this.extraMessage = "wrong ajaxAction set";
						this.debug();
						break;
				}
			}
			else 
			{
				this.extraMessage = "no ajax response";
				this.ajaxResponse = "no ajax response";
				this.debug();
			}
		}
	}
	
	/**
	 *	function to make post request, using php file with given url
	 *	and passing given parameters to it
	 */
	this.makePOSTRequest = function() 
	{
		this.http_request = false;
		
		if (window.XMLHttpRequest) 
		{ // Mozilla, Safari,...
			this.http_request = new XMLHttpRequest();
			if (this.http_request.overrideMimeType) 
			{
				// set type accordingly to anticipated content type
				//http_request.overrideMimeType('text/xml');
				this.http_request.overrideMimeType('text/html');
			}
		} 
		else if (window.ActiveXObject) 
		{ // IE
			try 
			{
				this.http_request = new ActiveXObject("Msxml2.XMLHTTP");
			} 
			catch (e) 
			{
				try 
				{
					this.http_request = new ActiveXObject("Microsoft.XMLHTTP");
				} 
				catch (e) 
				{}
			}
		}
		if (!this.http_request) 
		{
			alert('Cannot create XMLHTTP instance');
			return false;
		}

		this.http_request.open('POST', this.ajaxURL, false);
		this.http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		this.http_request.setRequestHeader("Content-length", this.ajaxPOST.length);
		this.http_request.setRequestHeader("Connection", "close");
		this.http_request.send(this.ajaxPOST);
		
		this.processResponse();
	}
}


/**************************************************************
 **************************************************************
 
	The UI class is responsible for all changes to popup GUI
 
 **************************************************************
 **************************************************************/
function UI()
{
	/**
	 *	to select/unselect 'Select All' checkbox
	 *	when any of checkboxes on the list is changed
	 */
	this.checkIfAllSelected = function()
	{
		//get array of all checkboxes
		var checkboxes = document.getElementsByName("save_invoice");
		
		//loop through them all
		for(var i=0; i < checkboxes.length;i++)
		{
			//get individual checkbox
			var chkbox = checkboxes.item(i);
			
			//check if it is checked
			if(chkbox.checked == true)
			{
				//if it is, check 'Check All' checkbox
				document.getElementById("save_invoice_all").checked = true;
				
				//and continue with checking next checkbox
				continue;
			}
			else
			{
				//if we find that a checkbox is not checked,
				//we uncheck 'Check All' checkbox
				document.getElementById("save_invoice_all").checked = false;
				
				//we don't need to check the rest of them, 
				//as we already know that not all are selected
				return;
			}
		}
	}

	/**
	 *	selects/unselects all checkboxes
	 */
	this.toggleSelectAll = function()
	{
		//get all checkboxes
		var checkboxes = document.getElementsByName("save_invoice");

		//loop through them all
		for(var i=0; i < checkboxes.length;i++)
		{
			//get individual checkbox
			var chkbox = checkboxes.item(i);
			
			//now if 'Check All' is checked
			if(document.getElementById("save_invoice_all").checked == true)
			{
				//check individual checkbox
				chkbox.checked = true;
			}
			else
			{
				//or uncheck it, if 'Check All' is not selected
				chkbox.checked = false;
			}
		}
	}
}