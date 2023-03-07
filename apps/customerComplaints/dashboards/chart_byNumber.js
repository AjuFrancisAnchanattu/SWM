function chart_byNumber()
{
	this.chart_type = null;
	this.chart_period = null;
	this.chart_site = null;
	
	this.chart_xml_byMonth_customer = null;
	this.chart_xml_byMonth_supplier = null;
	
	this.chart_xml_bySite = null;
	this.chart_xml_bySiteMonth = null;
	
	this.displayLoad = function()
	{
		document.getElementById("chartDiv_ccChart_openClosed").style.display = "none";
		document.getElementById("chartDiv_ccChart_openClosed_loading").style.display = "block";
	}
	
	this.hideLoad = function()
	{
		document.getElementById("chartDiv_ccChart_openClosed").style.display = "block";
		document.getElementById("chartDiv_ccChart_openClosed_loading").style.display = "none";
	}
	
	this.toggleChartValues = function()
	{
		switch( this.chart_type )
		{
			case "chart_ByNumber_ByMonth_Customer":
				this.chart_ByNumber_ByMonth_Customer();
				break;
			case "chart_ByNumber_ByMonth_Supplier":
				this.chart_ByNumber_ByMonth_Supplier();
				break;
			case "chart_ByNumber_BySite":
				this.chart_ByNumber_BySite(this.chart_period);
				break;
			case "chart_ByNumber_ByMonth_ForSite":
				this.chart_ByNumber_ByMonth_ForSite(this.chart_site);
				break;
		}
	}
	
	this.toggleValuesOnXML = function(chartXML)
	{
		if( document.getElementById("chart_byNumber_data_toggle_check").checked )
		{
			return chartXML.replace( "showValues='0'", "showValues='1'");
		}
		else
		{
			return chartXML.replace( "showValues='1'", "showValues='0'");
		}
	}
	

	
	this.chart_ByNumber_ByMonth_Supplier = function()
	{
		this.chart_type = "chart_ByNumber_ByMonth_Supplier";
		
		if( this.chart_xml_byMonth_supplier == null )
		{
			var argStr = "chartName=" + this.chart_type;
			var ajaxRequest = getAjax();

			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByNumber.display_chart_ByNumber_ByMonth_Supplier( ajaxRequest.responseText );
					chartByNumber.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_chart_ByNumber_ByMonth_Supplier( this.chart_xml_byMonth_supplier );
		}
	}
	
	this.display_chart_ByNumber_ByMonth_Supplier = function( ChartXML )
	{
		this.chart_xml_byMonth_supplier = this.toggleValuesOnXML( ChartXML );
		
		showPowerChart( this.chart_xml_byMonth_supplier, "chartDiv_ccChart_openClosed", "MultiAxisLine" );
		create_Exporter( "exportDiv_ccChart_openClosed" , "ccChart_openClosed_Exporter" );
		
		document.getElementById("byNumber_back_button").style.display = "none";
		document.getElementById("byNumber_back_button_2").style.display = "none";
		document.getElementById("byNumber_info").style.display = "none";
		document.getElementById("byNumber_radio").style.display = "inline";
	}
	
	
	
	this.chart_ByNumber_ByMonth_Customer = function()
	{
		this.chart_type = "chart_ByNumber_ByMonth_Customer";
		
		if( this.chart_xml_byMonth_customer == null )
		{
			var argStr = "chartName=" + this.chart_type;
			var ajaxRequest = getAjax();

			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByNumber.display_chart_ByNumber_ByMonth_Customer( ajaxRequest.responseText );
					chartByNumber.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_chart_ByNumber_ByMonth_Customer( this.chart_xml_byMonth_customer );
		}
	}
	
	this.display_chart_ByNumber_ByMonth_Customer = function( ChartXML )
	{
		this.chart_xml_byMonth_customer = this.toggleValuesOnXML( ChartXML );
		
		showPowerChart( this.chart_xml_byMonth_customer, "chartDiv_ccChart_openClosed", "MultiAxisLine" );
		create_Exporter( "exportDiv_ccChart_openClosed" , "ccChart_openClosed_Exporter" );
		
		document.getElementById("byNumber_back_button").style.display = "none";
		document.getElementById("byNumber_back_button_2").style.display = "none";
		document.getElementById("byNumber_info").style.display = "inline";
		document.getElementById("byNumber_radio").style.display = "inline";
	}
	
	
	
	this.chart_ByNumber_BySite = function(args)
	{
		this.chart_type = "chart_ByNumber_BySite";
		
		if( this.chart_xml_bySite == null || this.chart_period != args )
		{
			this.chart_period = args;
			
			var argStr = "chartName=" + this.chart_type + "&amp;args=" + this.chart_period;
			var ajaxRequest = getAjax();

			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByNumber.display_chart_ByNumber_BySite( ajaxRequest.responseText );
					chartByNumber.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_chart_ByNumber_BySite( this.chart_xml_bySite );
		}
	}
	
	this.display_chart_ByNumber_BySite = function( ChartXML )
	{
		this.chart_xml_bySite = this.toggleValuesOnXML(ChartXML);
		
		showChart( this.chart_xml_bySite, "chartDiv_ccChart_openClosed", "ScrollColumn2D" );
		create_Exporter( "exportDiv_ccChart_openClosed" , "ccChart_openClosed_Exporter" );
		
		document.getElementById("byNumber_back_button").style.display = "inline";
		document.getElementById("byNumber_back_button_2").style.display = "none";
		document.getElementById("byNumber_info").style.display = "none";
		document.getElementById("byNumber_radio").style.display = "none";
	}
	
	
	
	this.chart_ByNumber_ByMonth_ForSite = function(args)
	{
		this.chart_type = "chart_ByNumber_ByMonth_ForSite";
		
		if( this.chart_xml_bySiteMonth == null || this.chart_site != args )
		{
			this.chart_site = args;
			
			var argStr = "chartName=" + this.chart_type + "&amp;args=" + this.chart_site;
			var ajaxRequest = getAjax();

			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByNumber.display_chart_ByNumber_ByMonth_ForSite( ajaxRequest.responseText );
					chartByNumber.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_chart_ByNumber_ByMonth_ForSite( this.chart_xml_bySiteMonth );
		}
	}
	
	this.display_chart_ByNumber_ByMonth_ForSite = function( ChartXML )
	{
		this.chart_xml_bySiteMonth = this.toggleValuesOnXML(ChartXML);
		
		showPowerChart( this.chart_xml_bySiteMonth, "chartDiv_ccChart_openClosed", "MultiAxisLine" );
		create_Exporter( "exportDiv_ccChart_openClosed" , "ccChart_openClosed_Exporter" );
		
		document.getElementById("byNumber_back_button").style.display = "none";
		document.getElementById("byNumber_back_button_2").style.display = "inline";
		document.getElementById("byNumber_info").style.display = "none";
		document.getElementById("byNumber_radio").style.display = "none";
	}
}