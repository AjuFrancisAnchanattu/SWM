function getIcon( iconName )
{
	return "http://" + window.location.hostname + "/images/dTree/" + iconName + ".png"
};

function toggleLog()
{
	if( document.getElementById("toggleLogImage").myDisplay == "none" )
	{
		var showStyle = "";
		var imageIcon = getIcon("minus");
		document.getElementById("toggleLogImage").src = getIcon("minus_white");
		document.getElementById("toggleLogImage").myDisplay = "";
	}
	else
	{
		var showStyle = "none";
		var imageIcon = getIcon("plus");
		document.getElementById("toggleLogImage").src = getIcon("plus_white");
		document.getElementById("toggleLogImage").myDisplay = "none"
	}
	
	var allLogs = document.getElementById("logTable").getElementsByTagName("tr");
	
	for (var i =0; i<allLogs.length; i++)
	{
		if( allLogs[i].myId )
		{
			document.getElementById( "logComment_" + allLogs[i].myId ).style.display = showStyle;
			document.getElementById( "logImage_" + allLogs[i].myId ).src = imageIcon;
		}
	}
}

function toggleDetails( contentId , imageId )
{
	var contentDOM = document.getElementById( contentId );
	var imageDOM = document.getElementById( imageId );
	
	imageDOM.src = (contentDOM.style.display == "none") ? getIcon("minus") : getIcon("plus");
	contentDOM.style.display = (contentDOM.style.display == "none") ? "" : "none";
}

function confirmDelete(id) 
{  
	var answer = confirm( RemoteTranslate('delete_complaint_warning') )
	
	if (answer)
	{
		window.location = "delete?complaintId=" + id;
	}
}