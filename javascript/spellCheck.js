// Designed by Jason Matthews c.11/12/2008
// Enhanced by David Pickwell c.18/12/2008
// Version 2!

// Spell Check Javascript

function openSpellCheck(field)
{	
	var spellCheckString = URLEncode(document.getElementById(field).value);
	
	winname = window.open('/controls/spellCheck/spellCheck.php?string=' + spellCheckString + '&fieldName=' + field,'WIN','width=400,height=380,left=210,top=210,resizable=yes,scrollbars=yes,status=yes,location=yes');
	
	return winname;
	
}

function finishSpellCheck(finalString, field)
{
	
	window.opener.document.getElementById(field).value = URLDecode(finalString);
	
	window.close();
}


function URLEncode(plaintext)
{
	// The Javascript escape and unescape functions do not correspond
	// with what browsers actually do...
	var SAFECHARS = "0123456789" +					// Numeric
							"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
							"abcdefghijklmnopqrstuvwxyz" +
							"-_.!~*'()";					// RFC2396 Mark characters
	
	var HEX = "0123456789ABCDEF";
	
	var encoded = "";
	
	for (var i = 0; i < plaintext.length; i++ )
	{
		var ch = plaintext.charAt(i);

		if (ch == " ")
		{
			encoded += "+";				// x-www-urlencoded, rather than %20
		} 
		else if (ch == "+")
		{
			encoded += "@_@"; // Yes, it's a face. Get over it!
		}
		else if (SAFECHARS.indexOf(ch) != -1)
		{
			encoded += ch;
		}
		else
		{
			var charCode = ch.charCodeAt(0);

			if (charCode > 255)
			{
				alert( "Unicode Character '" 
				+ ch 
				+ "' cannot be encoded using standard URL encoding.\n" +
				"(URL encoding only supports 8-bit characters.)\n" +
				"A space (+) will be substituted." );
				encoded += "+";
			} 
			else 
			{
				encoded += "%";
				encoded += HEX.charAt((charCode >> 4) & 0xF);
				encoded += HEX.charAt(charCode & 0xF);
			}
		}
	} // for
	
	return encoded;
}

function URLDecode(finalString)
{
	// Replace + with ' '
	// Replace %xx with equivalent character
	// Put [ERROR] in output if %xx is invalid.
	var HEXCHARS = "0123456789ABCDEFabcdef"; 
	var encoded = finalString;
	var plaintext = "";
	var i = 0;
	while (i < encoded.length) 
	{
		var ch = encoded.charAt(i);
		if (ch == "+") 
		{
			plaintext += " ";
			i++;
		} 
		else if (ch == "%") 
		{
			if (i < (encoded.length-2) && (HEXCHARS.indexOf(encoded.charAt(i+1)) != -1) && (HEXCHARS.indexOf(encoded.charAt(i+2)) != -1) ) 
			{
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} 
			else
			{
				alert( 'Bad escape combination near ...' + encoded.substr(i) );
				plaintext += "%[ERROR]";
				i++;
			}
		} 
		else
		{
		plaintext += ch;
		i++;
		}
	} // while
	
	return plaintext;
}