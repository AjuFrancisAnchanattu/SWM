function WarningMessage( _message, _id )
{
	var message = _message;
	var id = _id;
	
	this.generateWarningRow = function()
	{
		var oNewTr = document.createElement("tr");
		oNewTr.id = id;
		oNewTr.style.display = "none";
		var oNewTd = document.createElement("td");
		oNewTd.colSpan = "2";
		oNewTd.style.border = "1px #FF3838 solid";
		oNewTd.style.backgroundColor = "#FFD8D8";
		oNewTd.style.textAlign = "center";
		var oText = document.createTextNode( message );
		oNewTd.appendChild(oText);
		oNewTr.appendChild(oNewTd);
		
		return oNewTr;
	}
		
	this.show = function()
		{
			document.getElementById( id ).style.display = "";
		};
	
	this.hide = function()
		{
			document.getElementById( id ).style.display = "none";
		};
}