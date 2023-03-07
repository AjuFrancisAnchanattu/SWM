function chart_byCategory()
{
	this.chart_ByCategory = function(args)
	{
		var argStr = "chartName=chart_ByCategory&amp;args=" + args;
		var ajaxRequest = getAjax();

		// Create a function that will receive data sent from the server
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4)
			{
				var chartXML = ajaxRequest.responseText;
				showChart_OneColumn( chartXML, "chartDiv_ccChart_ByCategory", "Pie2D" );
			}
		};
		
		//send request to ajax
		ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
		ajaxRequest.send(null);
	}
	
	this.chart_ByCategory_Value = function(args)
	{
		var argStr = "chartName=chart_ByCategory_Value&amp;args=" + args;
		var ajaxRequest = getAjax();

		// Create a function that will receive data sent from the server
		ajaxRequest.onreadystatechange = function(){
			if(ajaxRequest.readyState == 4)
			{
				var chartXML = ajaxRequest.responseText;
				showChart_OneColumn( chartXML, "chartDiv_ccChart_ByCategory_Value", "Pie2D" );
			}
		};
		
		//send request to ajax
		ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
		ajaxRequest.send(null);
	}
	
	this.changeMonth = function()
	{
		var e = document.getElementById("dropdown_byCategory_dates"); 
		var period = e.options[e.selectedIndex].value;
		var text = e.options[e.selectedIndex].text;
		
		//alert( text + ": " + period);
		
		this.chart_ByCategory(period);
		this.chart_ByCategory_Value(period);
	}
}