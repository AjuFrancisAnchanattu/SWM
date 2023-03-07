function updateInvoicesAutocomplete( text, li )
{
	var customerNumber = li.firstChild.innerHTML;
	
	if( !isNaN( customerNumber ) )
	{
		var row = 0;
		
		while( typeof window[ "invoiceAutocomplete_" + row ] != "undefined" )
		{
			var url = window[ "invoiceAutocomplete_" + row ].url;
			var tmp = url.split("&");
			url = tmp[0];
			
			window[ "invoiceAutocomplete_" + row ].url = url + "&customerNo=" + customerNumber;
			
			row++;
		}
	}
	
}

function setInvoicesAutocompleteURLs()
{
	var customerNumber = document.getElementById("sapCustomerNo").value;
	
	if( !isNaN( customerNumber ) && customerNumber != "" )
	{
		var row = 0;
		
		while( typeof window[ "invoiceAutocomplete_" + row ] != "undefined" )
		{
			var url = window[ "invoiceAutocomplete_" + row ].url;
			var tmp = url.split("&");
			url = tmp[0];
			
			window[ "invoiceAutocomplete_" + row ].url = url + "&customerNo=" + customerNumber;
			
			row++;
		}
	}
}