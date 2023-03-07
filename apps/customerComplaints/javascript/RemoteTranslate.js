/**
 * @author Daniel Gruszczyk
 * @date 25/01/2011
 */
 
 var RemoteTranslate = (function()
 {
	var phrases = {};
	
	return( function( phrase )
			{
				if( typeof phrases[ phrase ] == "undefined" )
				{
					var ajaxPOST = "phrase=" + encodeURI( phrase );
					
					var ajaxURL = '/apps/customerComplaints/ajax/RemoteTranslate';
					
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
					
					phrases[ phrase ] = decodeURIComponent(http_request.responseText);
				}
				
				return phrases[ phrase ];
			}
		);
 })();