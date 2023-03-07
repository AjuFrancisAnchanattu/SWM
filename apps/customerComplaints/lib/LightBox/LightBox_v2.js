var LightBox =
{
	//we will keep all separate lightboxes
	//as 'name' => 'arrtibutes' pairs
	
	//only one function to add a new lightBox
	//after lightbox has been added, its functions/properties will be accessible
	//through Lightbox.'name'...
	add : function( lbName, lbArgs )
	{
		//create first time (only if it does not exist yet!)
		if( typeof LightBox[lbName] == "undefined" )
		{
			this[lbName] = (function()
			{
				//********************************************************************************
				//The actual LightBox object
				var lbTMP =
				{
					/****************
					 *	VARIABLES	*
					 ****************/
					
					//GUI
					_closeClick : false,
					_shadow : false,
					_wrapper : false,
					_container : false,
					_title : false,
					_border : 
					{
						top : false,
						top_right : false,
						top_left : false,
						bottom : false,
						bottom_left : false,
						bottom_right : false,
						left : false,
						right : false
					},
					
					//other
					_name : lbName,
					_args : 
					{
						blockBelow : false,
						left : 0,
						right: false,
						top : 0,
						bottom : false,
						position: "absolute",
						draggable : false,
						width : 100,
						height : 100,
						closable : true,
						background : "#FFFFFF",
						url : false,
						post : false
					},
					
					_borderSize : 15,
					_closeButtonSize : 26,
					_closeButtonOffset : 5,
				
					/****************
					 *	FUNCTIONS	*
					 ****************/
					 
					/**
					 *	Creates an element which will be used as a close button
					 */
					_createCloseButton : function()
					{
						if( !lbTMP._closeClick )
						{
							lbTMP._closeClick = document.createElement("img");
							lbTMP._closeClick.id = "lightBox_" + lbTMP._name + "_close";
							lbTMP._closeClick.style.zIndex = "9999";
							lbTMP._closeClick.style.width = lbTMP._closeButtonSize + "px";
							lbTMP._closeClick.style.height = lbTMP._closeButtonSize + "px";
							lbTMP._closeClick.style.padding = "0;";
							//lbTMP._closeClick.style.marginTop = "-" + lbTMP._closeButtonOffset + "px";
							//lbTMP._closeClick.style.marginRight = "-" + lbTMP._closeButtonOffset + "px";
							lbTMP._closeClick.style.position = "absolute";
							lbTMP._closeClick.style.right = "0px";
							lbTMP._closeClick.style.top = "0px";
							lbTMP._closeClick.src = "lib/LightBox/img/close.png";
						}
					},
					
					/**
					 *	Creates a nice border around the container
					 */
					_createBorder : function()
					{
						//border - correners
						if( !lbTMP._border.top_left )
						{
							lbTMP._border.top_left = document.createElement("div");
							lbTMP._border.top_left.style.width = lbTMP._borderSize + "px";
							lbTMP._border.top_left.style.height = lbTMP._borderSize + "px";
							lbTMP._border.top_left.style.position = "absolute";
							lbTMP._border.top_left.style.left = "0px";
							lbTMP._border.top_left.style.top = "0px";
							lbTMP._border.top_left.style.backgroundImage = "URL('lib/LightBox/img/border-T-L.png')";
						}
						if( !lbTMP._border.top_right )
						{
							lbTMP._border.top_right = document.createElement("div");
							lbTMP._border.top_right.style.width = lbTMP._borderSize + "px";
							lbTMP._border.top_right.style.height = lbTMP._borderSize + "px";
							lbTMP._border.top_right.style.position = "absolute";
							lbTMP._border.top_right.style.right = "0px";
							lbTMP._border.top_right.style.top = "0px";
							lbTMP._border.top_right.style.backgroundImage = "URL('lib/LightBox/img/border-T-R.png')";
						}
						if( !lbTMP._border.bottom_left )
						{
							lbTMP._border.bottom_left = document.createElement("div");
							lbTMP._border.bottom_left.style.width = lbTMP._borderSize + "px";
							lbTMP._border.bottom_left.style.height = lbTMP._borderSize + "px";
							lbTMP._border.bottom_left.style.position = "absolute";
							lbTMP._border.bottom_left.style.left = "0px";
							lbTMP._border.bottom_left.style.bottom = "0px";
							lbTMP._border.bottom_left.style.backgroundImage = "URL('lib/LightBox/img/border-B-L.png')";
						}
						if( !lbTMP._border.bottom_right )
						{
							lbTMP._border.bottom_right = document.createElement("div");
							lbTMP._border.bottom_right.style.width = lbTMP._borderSize + "px";
							lbTMP._border.bottom_right.style.height = lbTMP._borderSize + "px";
							lbTMP._border.bottom_right.style.position = "absolute";
							lbTMP._border.bottom_right.style.right = "0px";
							lbTMP._border.bottom_right.style.bottom = "0px";
							lbTMP._border.bottom_right.style.backgroundImage = "URL('lib/LightBox/img/border-B-R.png')";
						}
						
						//border - sides
						if( !lbTMP._border.top )
						{
							lbTMP._border.top = document.createElement("div");
							lbTMP._border.top.style.width = lbTMP._args.width + "px";
							lbTMP._border.top.style.height = lbTMP._borderSize + "px";
							lbTMP._border.top.style.position = "absolute";
							lbTMP._border.top.style.left = lbTMP._borderSize + "px";
							lbTMP._border.top.style.top = "0px";
							lbTMP._border.top.style.backgroundImage = "URL('lib/LightBox/img/border-T.png')";
						}
						if( !lbTMP._border.bottom )
						{
							lbTMP._border.bottom = document.createElement("div");
							lbTMP._border.bottom.style.width = lbTMP._args.width + "px";
							lbTMP._border.bottom.style.height = lbTMP._borderSize + "px";
							lbTMP._border.bottom.style.position = "absolute";
							lbTMP._border.bottom.style.left = lbTMP._borderSize + "px";
							lbTMP._border.bottom.style.bottom = "0px";
							lbTMP._border.bottom.style.backgroundImage = "URL('lib/LightBox/img/border-B.png')";
						}
						if( !lbTMP._border.left )
						{
							lbTMP._border.left = document.createElement("div");
							lbTMP._border.left.style.width = lbTMP._borderSize + "px";
							lbTMP._border.left.style.height = lbTMP._args.height + "px";
							lbTMP._border.left.style.position = "absolute";
							lbTMP._border.left.style.left = "0px";
							lbTMP._border.left.style.top = lbTMP._borderSize + "px";
							lbTMP._border.left.style.backgroundImage = "URL('lib/LightBox/img/border-L.png')";
						}
						if( !lbTMP._border.right )
						{
							lbTMP._border.right = document.createElement("div");
							lbTMP._border.right.style.width = lbTMP._borderSize + "px";
							lbTMP._border.right.style.height = lbTMP._args.height + "px";
							lbTMP._border.right.style.position = "absolute";
							lbTMP._border.right.style.right = "0px";
							lbTMP._border.right.style.top = lbTMP._borderSize + "px";
							lbTMP._border.right.style.backgroundImage = "URL('lib/LightBox/img/border-R.png')";
						}
					},
					
					/**
					 *	Creates a wrapper around container and border
					 */
					_createWrapper : function()
					{
						if( !lbTMP._wrapper )
						{
							//the actual lightbox
							lbTMP._wrapper = document.createElement("div");
							lbTMP._wrapper.id = "lightBox_" + lbTMP._name + "_wrapper";
							lbTMP._wrapper.style.zIndex = "9998";
							
							//calculate total dimension of the lightbox:
							//container+borders+title
							// -X
							if( lbTMP._args.right )
							{	//lightbox should stretch to given dimension from right
								lbTMP._wrapper.style.right = lbTMP._args.right;
							}
							else
							{	//lightbox is wide x px
								var x = lbTMP._args.width + (lbTMP._borderSize * 2);
								lbTMP._wrapper.style.width = x + "px";
							}
							
							//-Y
							if( lbTMP._args.bottom )
							{	//lightBox should stretch to given position from bottom of the page
								lbTMP._wrapper.style.right = lbTMP._args.bottom;
							}
							else
							{	//lightBox is x px heigh
								var y = lbTMP._args.height + (lbTMP._borderSize * 2);
								lbTMP._wrapper.style.height = y + "px";
							}
							
							lbTMP._wrapper.style.overFlow= "hidden";
							
							lbTMP._wrapper.style.border = "none";
							
							lbTMP._wrapper.style.position = lbTMP._args.position;
							lbTMP._wrapper.style.left = lbTMP._args.left + "px";
							lbTMP._wrapper.style.top = lbTMP._args.top + "px";
						}
					},
					
					/**
					 *	Creates the content body of a lightbox
					 */
					_createContainer : function()
					{
						if( !lbTMP._container )
						{
							//the actual lightbox
							lbTMP._container = document.createElement("iframe");
							lbTMP._container.id = "lightBox_" + lbTMP._name + "_content";
							
							lbTMP._container.style.width = lbTMP._args.width + "px";
							lbTMP._container.style.height = lbTMP._args.height + "px";
							lbTMP._container.scrolling= "no";
							
							lbTMP._container.frameBorder = "0";
							
							lbTMP._container.style.position = "absolute";
							
							lbTMP._container.style.left = lbTMP._borderSize + "px";
							lbTMP._container.style.top = lbTMP._borderSize + "px";
							
							if( lbTMP._args.right )
							{
								lbTMP._container.style.right = lbTMP._borderSize + "px";
							}
							if( lbTMP._args.bottom )
							{
								lbTMP._container.style.bottom = lbTMP._borderSize + "px";
							}
							
							var url;
							if( lbTMP._args.url )
							{
								url = lbTMP._args.url;
								
								if( lbTMP._args.post )
								{
									url += "?" + lbTMP._args.post;
								}
							}
							lbTMP._container.src = url;
						}
					},
					
					/**
					 *	Creates a 'shadow' blocking the page under the lightbox
					 */
					_createShadow : function()
					{
						if( !lbTMP._shadow )
						{
							lbTMP._shadow = document.createElement("div");
							lbTMP._shadow.id = "lightBox_" + lbTMP._name + "_shadow";
							lbTMP._shadow.style.zIndex = "9999";
							lbTMP._shadow.style.position = "fixed";
							lbTMP._shadow.style.top = "0px";
							lbTMP._shadow.style.left = "0px";
							lbTMP._shadow.style.bottom = "0px";
							lbTMP._shadow.style.right = "0px";
							lbTMP._shadow.style.backgroundImage = "URL('lib/LightBox/img/lightBox_shadow_background.png')";
						}
					},
					
					/**
					 *	Creates the lightbox and displays it on a page
					 */
					_createAndShow : function()
					{
						//close button
						if( lbTMP._args.closable )
						{
							lbTMP._createCloseButton();
						}
						
						//create border
						lbTMP._createBorder();
						
						//main content
						lbTMP._createContainer();
						
						//wrapper
						lbTMP._createWrapper();
						
						//shadow
						if( lbTMP._args.blockBelow )
						{
							lbTMP._createShadow();
						}
						
						//append everything
						if( lbTMP._args.blockBelow )
						{
							if( lbTMP._args.closable )
							{
								lbTMP._wrapper.appendChild( lbTMP._closeClick );
							}
							
							//border - correners
							lbTMP._wrapper.appendChild( lbTMP._border.top_left );
							lbTMP._wrapper.appendChild( lbTMP._border.top_right );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom_left );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom_right );
							
							//border - sides
							lbTMP._wrapper.appendChild( lbTMP._border.top );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom );
							lbTMP._wrapper.appendChild( lbTMP._border.left );
							lbTMP._wrapper.appendChild( lbTMP._border.right );
							
							lbTMP._wrapper.appendChild( lbTMP._container );
							lbTMP._shadow.appendChild( lbTMP._wrapper );
							document.body.appendChild( lbTMP._shadow );
						}
						else
						{
							if( lbTMP._args.closable )
							{
								lbTMP._wrapper.appendChild( lbTMP._closeClick );
							}
							
							//border - correners
							lbTMP._wrapper.appendChild( lbTMP._border.top_left );
							lbTMP._wrapper.appendChild( lbTMP._border.top_right );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom_left );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom_right );
							
							//border - sides
							lbTMP._wrapper.appendChild( lbTMP._border.top );
							lbTMP._wrapper.appendChild( lbTMP._border.bottom );
							lbTMP._wrapper.appendChild( lbTMP._border.left );
							lbTMP._wrapper.appendChild( lbTMP._border.right );
							
							lbTMP._wrapper.appendChild( lbTMP._container );
							document.body.appendChild( lbTMP._wrapper );
						}
						
						//adding some listeners
						lbTMP._addListeners();
					},
				
					_addListeners : function()
					{
						if( lbTMP._args.closable )
						{
							var closeClick = document.getElementById("lightBox_" + lbTMP._name + "_close");
							closeClick.onclick = function(){ hidePopup( lbTMP._name ) };
							closeClick.onmousedown = function(){ e = window.event; e.cancelBubble = true; return; };
							closeClick.onmouseover = function(){ document.body.style.cursor = 'pointer'; return; };
							closeClick.onmouseout = function(){ document.body.style.cursor = 'default'; return; };
						}
						
						if( lbTMP._args.draggable )
						{
							var wrapper = document.getElementById("lightBox_" + lbTMP._name + "_wrapper");
							wrapper.attachEvent('onmousedown', function(){startdrag( lbTMP._name )} );
						}
					},
					
					/**
					 *	Sets X and Y coordinates for lightbox, 
					 *	only if it should be displayed around dom element
					 *
					 *	!!!This will work correctly ONLY if position: "absolute" is set.
					 *	!!!With any other positioning the effect is unpredictable.
					 */
					_set_DOM_XY : function( domElement, domXY)
					{
						//get reference to dom element on the page
						e = document.getElementById(domElement);
						
						//calculate position of the dom element
						var domX = 0;     
						var domY = 0;     
						while( e && !isNaN( e.offsetLeft ) && !isNaN( e.offsetTop ) )
						{
							domX += e.offsetLeft - e.scrollLeft;
							domY += e.offsetTop - e.scrollTop; 
							e = e.offsetParent;
						}
						e = document.getElementById(domElement);
						domX += e.offsetWidth;
						domY += e.offsetHeight;
						
						//calculate position of mouse cursor
						var mX = 0;
						var mY = 0;
						e = window.event;
						if (e.pageX || e.pageY) 	{
							posx = e.pageX;
							posy = e.pageY;
						}
						else if (e.clientX || e.clientY) 	{
							mX = e.clientX + document.body.scrollLeft
								+ document.documentElement.scrollLeft;
							mY = e.clientY + document.body.scrollTop
								+ document.documentElement.scrollTop;
						}
						
						//set X and Y depends where around DOM object 
						//we want to display the lightbox
						switch( domXY )
						{
							//display lightbox on the right of DOM object
							//Y is set to Y position of cursor
							case "X":
								lbTMP._args.left = domX;
								lbTMP._args.top = mY;
								lbTMP.reset();
								break;
							
							//display lightbox under the DOM element
							//X coordinates are set to mouse position
							case "Y":
								lbTMP._args.left = mX;
								lbTMP._args.top = domY;
								lbTMP.reset();
								break;
							
							//display the lightbox on bottom-right correner of the DOM element
							//best used for small icons etc?
							case "XY":
								lbTMP._args.left = domX;
								lbTMP._args.top = domY;
								break;
						}
					},
				
					/**
					 *	sets the position of a popup to the middle of the screen
					 */
					_set_Middle_XY : function()
					{
						var myWidth = 0, myHeight = 0;
						/*if( typeof( window.innerWidth ) == 'number' )
						{
							//Non-IE
							myWidth = window.innerWidth;
							myHeight = window.innerHeight;
						}
						else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
						{*/
							//IE 6+ in 'standards compliant mode'
							myWidth = document.documentElement.clientWidth;
							myHeight = document.documentElement.clientHeight;
						/*}
						else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
						{
							//IE 4 compatible
							myWidth = document.body.clientWidth;
							myHeight = document.body.clientHeight;
						}*/
						
						lbTMP._args.left = (myWidth / 2) - (lbTMP._args.width /2);
						lbTMP._args.top = (myHeight / 2) - (lbTMP._args.height /2);
						lbTMP.reset();
					},
				
					/**
					 *	Resets all styling (forces all elements to be recreated)
					 */
					reset : function()
					{
						//after changing styling we have to recreate all elements!
						lbTMP._closeClick = false;
						lbTMP._shadow = false;
						lbTMP._wrapper = false;
						lbTMP._container = false;
						lbTMP._title = false;
						lbTMP._border.top = false;
						lbTMP._border.top_right = false;
						lbTMP._border.top_left = false;
						lbTMP._border.bottom = false;
						lbTMP._border.bottom_left = false;
						lbTMP._border.bottom_right = false;
						lbTMP._border.left = false;
						lbTMP._border.right = false;
					},
				
					/**
					 *	Sets new styling
					 */
					setArgs : function( newArgs )
					{
						for( var key in newArgs)
						{
							if( typeof lbTMP._args[key] != "undefined" )
							{
								lbTMP._args[key] = newArgs[key];
							}
						}
						
						lbTMP.reset();
					},
					
					/**
					 *	Sets an url of a page to be shown in the lightbox
					 */
					setURL : function( url )
					{
						lbTMP._args.url = url;
					},
					
					/**
					 *	Sets any arguments to be sent to a page if needed
					 */
					setPOST : function( post )
					{
						lbTMP._args.post = post;
					},
					
					/**
					 *	Debug! shows all styling values
					 */
					showDebug : function()
					{
						var myStr = "";
		
						for( var key in lbTMP._args)
						{
							myStr += key + "= " + lbTMP._args[key] + "\n";
						}
						
						alert( myStr);
					},
					
					/**
					 *	Shows the lightbox
					 *	This is to stop from reloading the lightbox if it is already displayed...
					 */
					show : function()
					{
						if( !document.getElementById("lightBox_" + lbTMP._name + "_content") )
						{
							lbTMP._createAndShow();
						}
					},
					
					/**
					 *	Shows the lightbox at position which depends on DOM-tree element
					 */
					show_DOM : function( domElement, domXY)
					{
						if( !document.getElementById("lightBox_" + lbTMP._name + "_content") )
						{
							lbTMP._set_DOM_XY( domElement, domXY);
						
							lbTMP._createAndShow();
						}
					},
					
					/**
					 *	Shows the lightbox in the middle of the screen
					 */
					showMiddle : function()
					{
						if( !document.getElementById("lightBox_" + lbTMP._name + "_content") )
						{
							lbTMP._set_Middle_XY();
							
							lbTMP._createAndShow();
						}
					},
					
					/**
					 *	Hides the lightbox when it is not needed
					 */
					hide : function()
					{
						if( lbTMP._args.blockBelow )
						{
							var id = 'lightBox_' + lbTMP._name + '_shadow';
						}
						else
						{
							var id = 'lightBox_' + lbTMP._name + '_wrapper';
						}
						
						if( document.getElementById( id ) )
						{
							document.body.removeChild( document.getElementById( id ) );
						}
					}
				}
				//End of the actual LightBox object
				//********************************************************************************
				
				/**
				 *	Outside the object, sets initial arguments
				 */
				if( typeof lbArgs != "undefined" )
				{
					lbTMP.setArgs( lbArgs );
				}
				
				return lbTMP;
			})();
		}
	}
}

function hidePopup( name )
{
	e = window.event;
	e.cancelBubble = true;
	
	LightBox[ name ].hide();
	
	document.body.style.cursor = 'default'
	
	return false;
}

//mouse down on dragged DIV element
function startdrag( name )
{
	domClose = "lightBox_" + name + "_close";
	tClose = document.getElementById( domClose );
	window.document.draggedClose_click = tClose.onclick;
	window.document.draggedClose_onmousedown = tClose.onmousedown;
	window.document.draggedClose_onmouseover = tClose.onmouseover;
	window.document.draggedClose_onmouseout = tClose.onmouseout;
	
	domElement = "lightBox_" + name + "_wrapper";
	t = document.getElementById( domElement );
	e = window.event;
	window.document.draggedStyle = t.style.cssText;
	window.document.draggetContent = t.innerHTML;
	
	t.style.border = "#000000 2px solid";
	t.style.textAlign = "center";
	t.style.verticalAlign = "middle";
	t.style.backgroundImage = "URL('lib/LightBox/img/lightBox_shadow_background.png')";
	t.innerHTML = "<div style='font-size: 1.3em; font-weight: bold; color: red; padding: 10px; position: relative; top: 40%;'><i>" + RemoteTranslate("drag_lightbox") + "</i></div>";
	
	if( window.document.draged )
	{
		stopdrag();
		return false;
	}
	
	if (e.preventDefault) e.preventDefault(); //line for IE compatibility
	e.cancelBubble = true;
	window.document.onmousemoveOld = window.document.onmousemove;
	window.document.onmouseupOld = window.document.onmouseup;
	window.document.onmousemove = dodrag;
	window.document.onmouseup = stopdrag;
	window.document.draged = t;
	window.document.draggedClose = domClose;
	t.dragX = e.clientX;
	t.dragY = e.clientY;
	return false;
}

//move the DIV   
function dodrag(e)
{
	if (!e) e = event; //line for IE compatibility
	t = window.document.draged;
	t.style.left = (t.offsetLeft + e.clientX - t.dragX)+"px";
	t.style.top = (t.offsetTop + e.clientY - t.dragY)+"px";
	window.document.draggedTop = t.style.top;
	window.document.draggedLeft = t.style.left;
	t.dragX = e.clientX;
	t.dragY = e.clientY;
	return false;
}

//restore event-handlers   
function stopdrag()
{
	t = window.document.draged;
	t.style.cssText = window.document.draggedStyle;
	t.style.top = window.document.draggedTop;
	t.style.left = window.document.draggedLeft;
	t.innerHTML = window.document.draggetContent;
	
	tCloseId = window.document.draggedClose;
	tClose = document.getElementById( tCloseId );
	tClose.onclick = window.document.draggedClose_click;
	tClose.onmousedown = window.document.draggedClose_onmousedown;
	tClose.onmouseover = window.document.draggedClose_onmouseover;
	tClose.onmouseout = window.document.draggedClose_onmouseout;
	
	window.document.onmousemove = window.document.onmousemoveOld;
	window.document.onmouseup = window.document.onmouseupOld;
	window.document.draged = false;
	
	return false;
}