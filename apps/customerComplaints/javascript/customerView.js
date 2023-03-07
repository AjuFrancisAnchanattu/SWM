function View()
{
	this.formatForPrint = function()
	{
		document.getElementById("form").lastChild.firstChild.firstChild.lastChild.className = "printMe";
		document.getElementById("form").lastChild.firstChild.firstChild.lastChild.firstChild.nextSibling.nextSibling.className = "formTable printMe";
		document.getElementById("form").lastChild.firstChild.firstChild.lastChild.firstChild.nextSibling.nextSibling.removeAttribute("style");
		document.getElementById("form").lastChild.firstChild.firstChild.firstChild.className = "noPrint";
		document.body.firstChild.firstChild.className = document.body.firstChild.firstChild.className + " noPrint";
		document.body.firstChild.firstChild.nextSibling.className = document.body.firstChild.firstChild.nextSibling.className + " noPrint";
		
		//complaint:
		if(document.getElementById("groupedComplaintIdGroupGroup"))
		{
			var row = document.getElementById("groupedComplaintIdGroupGroup").lastChild;
			row.parentNode.removeChild( row );
		}
		
		//conclusion
		if(document.getElementById("sapReturnNoGroupGroup"))
		{
			var row = document.getElementById("sapReturnNoGroupGroup").lastChild;
			row.parentNode.removeChild( row );
		}
	}
}

var complaintId = document.getElementById("complaintId").complaintId;
var readonly = true;
var view = new View();

view.formatForPrint();