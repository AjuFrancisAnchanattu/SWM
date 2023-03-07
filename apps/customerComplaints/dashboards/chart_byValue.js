function chart_byValue()
{
	this.chart_period = null;
	this.chart_site = null;
	this.chart_type = null;
	this.chart_xml_byMonth = null;
	this.chart_xml_bySite = null;
	this.chart_xml_bySiteMonth = null;
	
	this.displayLoad = function()
	{
		document.getElementById("chartDiv_ccChart_ByValue").style.display = "none";
		document.getElementById("chartDiv_ccChart_ByValue_loading").style.display = "block";
	}
	
	this.hideLoad = function()
	{
		document.getElementById("chartDiv_ccChart_ByValue").style.display = "block";
		document.getElementById("chartDiv_ccChart_ByValue_loading").style.display = "none";
	}
	
	this.toggleChartValues = function()
	{
		switch( this.chart_type )
		{
			case "chart_ByValue_ByMonth":
				this.chart_xml_byMonth = this.toggleValuesOnXML(this.chart_xml_byMonth);
				this.chart_ByValue_ByMonth();
				break;
			case "chart_ByValue_BySite":
				this.chart_xml_bySite = this.toggleValuesOnXML(this.chart_xml_bySite);
				this.chart_ByValue_BySite(this.chart_period);
				break;
			case "chart_ByValue_ByMonth_ForSite":
				this.chart_xml_bySiteMonth = this.toggleValuesOnXML(this.chart_xml_bySiteMonth);
				this.chart_ByValue_ByMonth_ForSite(this.chart_site);
				break;
		}
	}
	
	this.toggleValuesOnXML = function(chartXML)
	{
		if( document.getElementById("chart_data_toggle_check").checked )
		{
			return chartXML.replace( "showValues='0'", "showValues='1'");
		}
		else
		{
			return chartXML.replace( "showValues='1'", "showValues='0'");
		}
	}

	
	
	this.chart_ByValue_ByMonth = function()
	{
		this.chart_type = "chart_ByValue_ByMonth";
		
		if( this.chart_xml_byMonth == null )
		{
			var argStr = "chartName=chart_ByValue_ByMonth";
			var ajaxRequest = getAjax();
		
			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByValue.display_hart_ByValue_ByMonth( ajaxRequest.responseText );
					chartByValue.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_hart_ByValue_ByMonth( this.chart_xml_byMonth );
		}
	}

	this.display_hart_ByValue_ByMonth = function(ChartXML)
	{
		this.chart_xml_byMonth = this.toggleValuesOnXML(ChartXML);
			
		showPowerChart(this.chart_xml_byMonth,"chartDiv_ccChart_ByValue", "MultiAxisLine");
		create_Exporter( "exportDiv_ccChart_ByValue" , "ccChart_ByValue_Exporter" );
		
		document.getElementById("drilldown_back_button").style.display = "none";
		document.getElementById("drilldown_back_button_2").style.display = "none";
		document.getElementById("drilldown_info").style.display = "block";
	}
	
	
	
	this.chart_ByValue_BySite = function(args)
	{
		this.chart_type = "chart_ByValue_BySite";
		
		if( this.chart_period != args )
		{
			this.chart_period = args;
			var argStr = "chartName=chart_ByValue_BySite&amp;args="+this.chart_period;
			var ajaxRequest = getAjax();
			
			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByValue.display_Chart_ByValue_BySite( ajaxRequest.responseText );
					chartByValue.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_Chart_ByValue_BySite( this.chart_xml_bySite );
		}
	}

	this.display_Chart_ByValue_BySite = function(ChartXML)
	{
		this.chart_xml_bySite = this.toggleValuesOnXML(ChartXML);
					
		showChart(this.chart_xml_bySite, "chartDiv_ccChart_ByValue", "ScrollColumn2D");
		create_Exporter( "exportDiv_ccChart_ByValue" , "ccChart_ByValue_Exporter" );
		
		document.getElementById("drilldown_back_button").style.display = "block";
		document.getElementById("drilldown_back_button_2").style.display = "none";
		document.getElementById("drilldown_info").style.display = "none";
	}
	
	
	
	this.chart_ByValue_ByMonth_ForSite = function(args)
	{
		this.chart_type = "chart_ByValue_ByMonth_ForSite";
		
		if( this.chart_site != args )
		{
			this.chart_site = args;
			var argStr = "chartName=chart_ByValue_ByMonth_ForSite&amp;args="+this.chart_site;
			var ajaxRequest = getAjax();
			
			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4)
				{
					chartByValue.display_Chart_ByValue_ByMonth_ForSite(ajaxRequest.responseText);
					chartByValue.hideLoad();
				}
			};
			
			//send request to ajax
			ajaxRequest.open("GET", "/apps/customerComplaints/ajax/getDashboard?" + argStr, true);
			ajaxRequest.send(null);
			this.displayLoad();
		}
		else
		{
			this.display_Chart_ByValue_ByMonth_ForSite(this.chart_xml_bySiteMonth);
		}
	}
	
	this.display_Chart_ByValue_ByMonth_ForSite = function(ChartXML)
	{
		this.chart_xml_bySiteMonth = this.toggleValuesOnXML(ChartXML);
			
		showPowerChart(this.chart_xml_bySiteMonth, "chartDiv_ccChart_ByValue", "MultiAxisLine");
		create_Exporter( "exportDiv_ccChart_ByValue" , "ccChart_ByValue_Exporter" );
		
		document.getElementById("drilldown_back_button").style.display = "none";
		document.getElementById("drilldown_back_button_2").style.display = "block";
		document.getElementById("drilldown_info").style.display = "none";
	}
}