 /**
  * @author Daniel Gruszczyk
  * @date 24/01/2011
  */

function calculate()
{
	var value = document.getElementById("value").value;
	var curFrom = getDropdownCurrencyValue();
	
	if( validate() )
	{
		var Parent = document.getElementById("calculations");
		while(Parent.hasChildNodes())
		{
			Parent.removeChild(Parent.firstChild);
		}
		
		var e = document.getElementById("currency");
	
		for( i=0 ; i<e.options.length ; i++ )
		{
			var curTo = e.options[i].value;
			var curText = e.options[i].text;
			var convertedValue = convertCurrency( value, curFrom, curTo);
			
			if( convertedValue == value)
			{
				addCalculation( convertedValue, curText, true);
			}
			else
			{
				addCalculation( convertedValue, curText, false);
			}
		}
	}
}

function addCalculation( value, currency, bold)
{
	var tbl = document.getElementById("calculations");
	var lastRow = tbl.rows.length;
	
	var row = tbl.insertRow( lastRow);
	//row.onclick = function(){setInput(value, currency);};
	//row.onmouseover = function(){row.className="hover";};
	//row.onmouseout = function(){row.className = "";};
	
	var cellValue = row.insertCell(0);
	cellValue.style.paddingRight = "20px";
	var cellCurrency = row.insertCell(1);
	
	var txtValue = document.createTextNode( value );
	var txtCurrency = document.createTextNode( currency );
	
	if(bold)
	{
		cellValue.style.color = "#005CB8";
		cellCurrency.style.color = "#005CB8";
	}
	
	cellValue.appendChild( txtValue);
	cellCurrency.appendChild( txtCurrency);
}

function setInput(value, currency)
{
	setDropdownCurrencyValue(currency);
	document.getElementById("value").value = value;
	
	calculate();
}

function convertCurrency( value, currencyFrom, currencyTo)
{
	if( currencyFrom == currencyTo)
	{
		return value;
	}
	else
	{
		//convert to GBP
		value = value * currencyFrom;
		
		//convert to final currency
		value = value / currencyTo;
		
		return value.toFixed(2);
	}
}

function getDropdownCurrencyValue()
{
	var e = document.getElementById("currency");
	
	return e.options[e.selectedIndex].value;
}

function setDropdownCurrencyValue(currency)
{
	var e = document.getElementById("currency");
	
	for( i=0 ; i<e.options.length ; i++ )
	{
		if( e.options[i].text == currency)
		{
			e.selectedIndex = i;
		}
	}
}

function validate()
{
	var value = document.getElementById("value").value;
	
	var regex = /^(\d|[1-9]\d*)(\.(\d\d?))?$/;
		
	//test the value against regex
	var result = regex.test(value);
	
	//depends on the validation results
	//check/uncheck the checkbox for saving
	//mark/unmark field as 'invalid'
	if( !result )
	{
		document.getElementById("error").style.display = "inline";
		return false;
	}
	else
	{
		document.getElementById("error").style.display = "none";
		return true;
	}
}