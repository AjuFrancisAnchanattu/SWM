function appendCalcIcon()
{
	var summary_snapin_value = summary_snapin_txtValue.split(" ")[0];
	var summary_snapin_currency = summary_snapin_txtValue.split(" ")[1];
		
	//create new lightBox
	var lightBoxStyle = 
		{ 	
			blockBelow : false,	//not blocking anything below
			width: 225,
			height: 215,
			border : "special",
			draggable : true
		};
	LightBox.add( "summarySnapin_currencyCalculator", lightBoxStyle );
	
	//set address of page to display in lightBox					
	LightBox.summarySnapin_currencyCalculator.setURL( "http://scapanetdev/apps/customerComplaints/lib/currencyConverter/currencyConverter");
	LightBox.summarySnapin_currencyCalculator.setPOST( "currency=" + summary_snapin_currency + "&amp;exchangeRatesType=budget&amp;value=" + summary_snapin_value );
	
	
	//set icon to display/hide icon on event
	document.getElementById("currencyCalculatorImg").onclick = function() {LightBox.summarySnapin_currencyCalculator.reset();LightBox.summarySnapin_currencyCalculator.show_DOM("currencyCalculatorImg", "XY");};
}


function toggleQuickSummary( summaryPart )
{
	var summary_img = document.getElementById("quickSummary_" + summaryPart + "_img");
	var summary_content = document.getElementById("quickSummary_" + summaryPart + "_content");
	
	if(summary_content.style.display == "")
	{
		summary_img.src = "../../images/dTree/plus.png";
		summary_content.style.display = "none";
		
		createCookie("summary_" + summaryPart, 0, 600);
	}
	else
	{
		summary_img.src = "../../images/dTree/minus.png";
		summary_content.style.display = "";
		
		createCookie("summary_" + summaryPart, 1, 600);
	}
}

function toggleMaterialDetails( materialNo )
{
	var _img = document.getElementById( materialNo + "_img");
	var _content = document.getElementById( materialNo + "_content");
		
	if(_content.style.display == "")
	{
		_img.src = "../../images/dTree/plus.png";
		_content.style.display = "none";
		
	}
	else
	{
		_img.src = "../../images/dTree/minus.png";
		_content.style.display = "";
		
	}
}

function createCookie(name,value,days) 
{
	if (days) 
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires=" + date.toGMTString();
	}
	else 
	{
		var expires = "";
	}
	document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) 
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i != ca.length;i++) 
	{
		var c = ca[i];
		while (c.charAt(0) == ' ')
		{
			c = c.substring(1,c.length);
		}
		
		if (c.indexOf(nameEQ) == 0) 
		{
			return c.substring(nameEQ.length,c.length);
		}
	}
	return null;
}

function eraseCookie(name) 
{
	createCookie(name,"",-1);
}