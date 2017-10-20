// Copyright (c) 2006 Sébastien Gruhier (http://xilinus.com, http://itseb.com)
// 
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to
// permit persons to whom the Software is furnished to do so, subject to
// the following conditions:
// 
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
// LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
// OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//
// VERSION 0.70

var Window = Class.create();
Window.prototype = {
	// Constructor
	// Available parameters : className, title, minWidth, minHeight, maxWidth, maxHeight, width, height, top, left, resizable, zIndex, opacity, 
	//                        hideEffect, showEffect, showEffectOptions, hideEffectOptions, effectOptions, url, relativeTo, draggable, closable, parent
	initialize: function(id, parameters) {
		this.hasEffectLib = String.prototype.parseColor != null
		this.minWidth = parameters.minWidth || 100;
		this.minHeight = parameters.minHeight || 100;
		this.maxWidth = parameters.maxWidth;
		this.maxHeight = parameters.maxHeight;
		this.showEffect = parameters.showEffect || (this.hasEffectLib ? Effect.Appear : Element.show)
		this.hideEffect = parameters.hideEffect || (this.hasEffectLib ? Effect.Fade : Element.hide)
		
		this.showEffectOptions = parameters.showEffectOptions || parameters.effectOptions;
		this.hideEffectOptions = parameters.hideEffectOptions || parameters.effectOptions;
		this.draggable = parameters.draggable != null ? parameters.draggable : true;
		
		var resizable = parameters.resizable != null ? parameters.resizable : true;
		var closable = parameters.closable != null ? parameters.closable : true;
		var className = parameters.className != null ? parameters.className : "dialog";
		this.className = className;
		
		var parent = parameters.parent || document.getElementsByTagName("body").item(0);
			
		this.element = this.createWindow(id, className, parent, resizable, closable, parameters.title, parameters.url);
		this.isIFrame = parameters.url != null;
		
		// Bind event listener
    this.eventMouseDown = this.initDrag.bindAsEventListener(this);
  	this.eventMouseUp   = this.endDrag.bindAsEventListener(this);
  	this.eventMouseMove = this.updateDrag.bindAsEventListener(this);
  	this.eventKeyPress = this.keyPress.bindAsEventListener(this);

		this.topbar = $(this.element.id + "_top");
		this.bottombar = $(this.element.id + "_bottom");
		
		if (this.draggable)  {
  		Event.observe(this.topbar, "mousedown", this.eventMouseDown);
  		Event.observe(this.bottombar, "mousedown", this.eventMouseDown);
			Element.addClassName(this.bottombar, "bottom_draggable")
			Element.addClassName(this.topbar, "top_draggable")
    }		
    
		var offset = [0,0];
		if (resizable) {
			this.sizer = $(this.element.id + "_sizer");
    	Event.observe(this.sizer, "mousedown", this.eventMouseDown);
    }	
		var width = parseFloat(parameters.width) || 200;
		var height = parseFloat(parameters.height) || 200;

		if (parameters.left != null) {
			Element.setStyle(this.element,{left: parseFloat(parameters.left) + offset[0] + 'px'});
			this.useLeft = true;
		}

		if (parameters.right != null) {
			Element.setStyle(this.element,{right: parseFloat(parameters.right) + 'px'});
			this.useLeft = false;
		}

		if (parameters.top != null) {
			Element.setStyle(this.element,{top: parseFloat(parameters.top) + 'px'});
			this.useTop = true;
		}

		if (parameters.bottom != null) {
			Element.setStyle(this.element,{bottom: parseFloat(parameters.bottom) + 'px'});			
			this.useTop = false;
		}

		this.setSize(width, height);

		if (parameters.opacity)
			this.setOpacity(parameters.opacity);
		if (parameters.zIndex)
			this.setZIndex(parameters.zIndex)

		this.destroyOnClose = false;

    this._getWindowBorderSize()
		Windows.register(this);	    
  },
  
  _getWindowBorderSize: function() {
    // Hack to get real window border size!!
    var div = this._createHiddenDiv(this.className + "_n")
		this.heightN = Element.getDimensions(div).height;		
		div.parentNode.removeChild(div)

    var div = this._createHiddenDiv(this.className + "_s")
		this.heightS = Element.getDimensions(div).height;		
		div.parentNode.removeChild(div)

    var div = this._createHiddenDiv(this.className + "_e")
		this.widthE = Element.getDimensions(div).width;		
		div.parentNode.removeChild(div)

    var div = this._createHiddenDiv(this.className + "_w")
		this.widthW = Element.getDimensions(div).width;
		div.parentNode.removeChild(div)
  },
 
  _createHiddenDiv: function(className) {
    var objBody = document.getElementsByTagName("body").item(0);
    var win = document.createElement("div");
		win.setAttribute('id', this.element.id+ "_tmp");
		win.className = className;
		win.style.display = 'none'
		win.innerHTML = ''
		objBody.insertBefore(win, objBody.firstChild)   
		return win
  },
  
	// Destructor
 	destroy: function() {
		Windows.notify("onDestroy", this);
  	Event.stopObserving(this.topbar, "mousedown", this.eventMouseDown);
  	Event.stopObserving(this.bottombar, "mousedown", this.eventMouseDown);
		if (this.sizer)
    		Event.stopObserving(this.sizer, "mousedown", this.eventMouseDown);

		var objBody = document.getElementsByTagName("body").item(0);
		objBody.removeChild(this.element);

		Windows.unregister(this);	    
	},
  	
	// Sets window deleagte, should have functions: "canClose(window)" 
	setDelegate: function(delegate) {
		this.delegate = delegate
	},
	
	// Gets current window delegate
	getDelegate: function() {
		return this.delegate;
	},
	
	// Gets window content
	getContent: function () {
		return $(this.element.id + "_content");
	},
	
	// Sets the content with an element id
	setContent: function(id, autoresize, autoposition) {
		var d = null;
		var p = null;

		if (autoresize) 
			d = Element.getDimensions(id);
		if (autoposition) 
			p = Position.cumulativeOffset($(id));

		var content = this.getContent()
		content.appendChild($(id));
		
		if (autoresize) 
			this.setSize(d.width, d.height);
		if (autoposition) 
		  this.setLocation(p[1] - this.heightN, p[0] - this.widthW);	  
	},
	
	setCookie: function(name, expires, path, domain, secure) {
		name = name || this.element.id;
		this.cookie = [name, expires, path, domain, secure];
		
		// Get cookie
		var value = WindowUtilities.getCookie(name)
		// If exists
		if (value) {
			var values = value.split(',')
			var x = values[0].split(':')
			var y = values[1].split(':')

			this.setSize(parseFloat(values[2]), parseFloat(values[3]));

			Element.setStyle(this.element, x[0] == "l" ? {left: x[1]} : {right: x[1]});
			Element.setStyle(this.element, y[0] == "t" ? {top: y[1]} : {bottom: y[1]});
		}
	},
	
	// Gets window ID
	getId: function() {
		return this.element.id;
	},
	
	// Detroys itself when closing 
	setDestroyOnClose: function() {
		this.destroyOnClose = true;
	},
	
	// initDrag event
	initDrag: function(event) {
		// Get pointer X,Y
  	this.pointer = [Event.pointerX(event), Event.pointerY(event)];
		this.doResize = false;
		
		// Check if click on close button, 
		var closeButton = $(this.getId() + '_close');
		if (closeButton && Position.within(closeButton, this.pointer[0], this.pointer[1])) {
			return;
		}
		// Check if click on sizer
		if (this.sizer && Position.within(this.sizer, this.pointer[0], this.pointer[1])) {
			this.doResize = true;
    	this.widthOrg = this.width;
    	this.heightOrg = this.height;
    	this.bottomOrg = parseFloat(Element.getStyle(this.element, 'bottom'));
    	this.rightOrg = parseFloat(Element.getStyle(this.element, 'right'));
			Windows.notify("onStartResize", this);
		}
		else 
			Windows.notify("onStartMove", this);
		
		// Register global event to capture mouseUp and mouseMove
		Event.observe(document, "mouseup", this.eventMouseUp);
  	Event.observe(document, "mousemove", this.eventMouseMove);
		
		// Add an invisible div to keep catching mouse event over the iframe
		if (this.isIFrame) {
			var objBody = document.getElementsByTagName("body").item(0);
			var div = document.createElement("div");
			div.style.position = "absolute";
			div.style.top = this.heightN + "px";
			div.style.bottom = this.widthW + "px";
			div.style.zIndex = Windows.maxZIndex;			
			div.style.width = this.width + "px";
			div.style.height = this.height + "px";
			this.element.appendChild(div);
			this.tmpDiv = div;			
		}
		this.toFront();
      	Event.stop(event);
  	},

    // updateDrag event
  	updateDrag: function(event) {
	   	var pointer = [Event.pointerX(event), Event.pointerY(event)];    
  		var dx = pointer[0] - this.pointer[0];
  		var dy = pointer[1] - this.pointer[1];
  		
  		// Resize case, update width/height
  		if (this.doResize) {
  			var width = this.widthOrg
  			var height = this.heightOrg;
  			width += dx;
  			height += dy;
  			
  			this.setSize(width, height);
  			
        dx = this.width - this.widthOrg
        dy = this.height - this.heightOrg
  			
			  // Check if it's a right position, update it to keep upper-left corner at the same position
  			if (! this.useLeft) 
  				Element.setStyle(this.element,{right: (this.rightOrg -dx) + 'px'});
  			// Check if it's a bottom position, update it to keep upper-left corner at the same position
  			if (! this.useTop) 
  				Element.setStyle(this.element,{bottom: (this.bottomOrg -dy) + 'px'});
  		}
  		// Move case, update top/left
  		else {
  		  this.pointer = pointer;
    		
  			if (this.useLeft) {
    			var left = Element.getStyle(this.element, 'left');
  				left = parseFloat(left) + dx;
  				Element.setStyle(this.element,{left: left + 'px'});
  			}	else {
  				var right = Element.getStyle(this.element, 'right');
  				right = parseFloat(right) - dx;
  				Element.setStyle(this.element,{right: right + 'px'});
  			}
			
  			if (this.useTop) {
    			var top = Element.getStyle(this.element, 'top');
  				top = parseFloat(top) + dy;
  				Element.setStyle(this.element,{top: top + 'px'});
  			} else {
  				var bottom = Element.getStyle(this.element, 'bottom');
  				bottom = parseFloat(bottom) - dy;
  				Element.setStyle(this.element,{bottom: bottom + 'px'});
  			}
  		}
  		if (this.iefix) 
  			this._fixIEOverlapping(); 
      Event.stop(event);
  	},

	 // endDrag callback
 	endDrag: function(event) {
		if (this.doResize)
			Windows.notify("onEndResize", this);
		else
			Windows.notify("onEndMove", this);
		
		// Release event observing
		Event.stopObserving(document, "mouseup", this.eventMouseUp);
    Event.stopObserving(document, "mousemove", this.eventMouseMove);

		// Remove temporary div
		if (this.isIFrame) {
			this.tmpDiv.parentNode.removeChild(this.tmpDiv);
			this.tmpDiv = null;
		}
		// Store new location/size
		if (this.cookie) {
			var value = "";
			if (this.useLeft)
				value += "l:" + Element.getStyle(this.element.id, 'left')
			else
				value += "r:" + Element.getStyle(this.element.id, 'right')
			if (this.useTop)
				value += ",t:" + Element.getStyle(this.element.id, 'top')
			else
				value += ",b:" + Element.getStyle(this.element.id, 'bottom')
			value += "," + this.width;
			value += "," + this.height;
			WindowUtilities.setCookie(value, this.cookie)
		}
    Event.stop(event);
  },

	keyPress: function(event) {
		//Dialog.cancelCallback();
	},
	
	// Creates HTML window code
	createWindow: function(id, className, parent, resizable, closable, title, url) {
		win = document.createElement("div");
		win.setAttribute('id', id);
		win.className = "dialog";
	 	if (!title)
			title = "&nbsp;";

		var content;
		if (url)
			content= "<IFRAME id=\"" + id + "_content\" SRC=\"" + url + "\" > </IFRAME>";
		else
			content ="<DIV id=\"" + id + "_content\" class=\"" +className + "_content\"> </DIV>";

		win.innerHTML = "\
		<div class='"+ className +"_close' id='"+ id +"_close' onclick='Windows.close(\""+ id +"\")'> </div>\
		<table id='"+ id +"_header'>\
			<tr id='"+ id +"_row1'>\
				<td>\
					<table>\
						<tr>\
							<td id='"+ id +"_nw' class='"+ className +"_nw'><div class='"+ className +"_nw'> </td>\
							<td class='"+ className +"_n'  valign='middle'><div  id='"+ id +"_top' class='"+ className +"_title'>"+ title +"</div></td>\
							<td class='"+ className +"_ne'> <div class='"+ className +"_ne'></td>\
						</tr>\
					</table>\
				</td>\
			</tr>\
			<tr id='"+ id +"_row2'>\
				<td>\
					<table>\
						<tr>\
							<td class='"+ className +"_w'><div class='"+ className +"_w'> </div></td>\
							<td class='"+ className +"_content'>"+ content +"</td>\
							<td class='"+ className +"_e'><div class='"+ className +"_e'> </div></td>\
						</tr>\
					</table>\
				</td>\
			</tr>\
			<tr id='"+ id +"_row3'>\
				<td>\
					<table>\
						<tr>\
							<td class='"+ className +"_sw' id='"+ id +"_sw'><div class='"+ className +"_sw'> </td>\
							<td class='"+ className +"_s'><div  id='"+ id +"_bottom' class='"+ className +"_s'> </div></td>\
							<td class='"+ className +"_se'>"+ (resizable  ? "<div id='"+ id + "_sizer' class='"+ className +"_sizer'></div>" : "<div class='"+ className +"_se'></div>") +"</td>\
						</tr>\
					</table>\
				</td>\
			</tr>\
		</table>\
		";
		Element.hide(win);
		parent.insertBefore(win, parent.firstChild);
		
		if (!closable)
		  Element.hide(id +"_close")
    
		return win;
	},
	
	// Sets window location
	setLocation: function(top, left) {
		Element.setStyle(this.element,{top: top + 'px'});
		Element.setStyle(this.element,{left: left + 'px'});
	},
		
	// Sets window size
	setSize: function(width, height) {    
		// Check min and max size
		if (width < this.minWidth)
			width = this.minWidth;

		if (height < this.minHeight)
			height = this.minHeight;
			
		if (this.maxHeight && height > this.maxHeight)
			height = this.maxHeight;

		if (this.maxWidth && width > this.maxWidth)
			width = this.maxWidth;

		this.width = width;
		this.height = height;
		
		Element.setStyle(this.element,{width: width + 'px'});
		Element.setStyle(this.element,{height: height + 'px'});

		// Update content height
		var content = $(this.element.id + '_content')
		Element.setStyle(content,{height: height  + 'px'});
		Element.setStyle(content,{width: width  + 'px'});
	},
	
	// Brings window to front
	toFront: function() {
		windows = document.getElementsByClassName("dialog");
		var maxIndex= 0;
		for (var i = 0; i<windows.length; i++){
			if (maxIndex < parseFloat(windows[i].style.zIndex))
				maxIndex = windows[i].style.zIndex;
		}
		this.element.style.zIndex = parseFloat(maxIndex) +1;
	},
	
	// Displays window modal state or not
	show: function(modal) {
		if (modal) {
			WindowUtilities.disableScreen(this.className);
			this.modal = true;			
			this.setZIndex(Windows.maxZIndex + 20);
			Windows.unsetOverflow(this);
			Event.observe(document, "keypress", this.eventKeyPress);	      	
		}
			
		this.setSize(this.width, this.height);
		if (this.showEffectOptions )
			this.showEffect(this.element, this.showEffectOptions);	
		else
			this.showEffect(this.element);	
		this._checkIEOverlapping();
	},
	
	// Displays window modal state or not at the center of the page
	showCenter: function(modal) {
		this.setSize(this.width, this.height);
		this.center();
		
		this.show(modal);
	},
	
	center: function() {
		var arrayPageSize = WindowUtilities.getPageSize();
		var arrayPageScroll = WindowUtilities.getPageScroll();

		this.element.style.top = (arrayPageScroll[1] + ((arrayPageSize[3] - this.height) / 2) + 'px');
		this.element.style.left = (((arrayPageSize[0] - this.width) / 2) + 'px');	
	},
	
	// Hides window
	hide: function() {
		if (this.modal) {
			WindowUtilities.enableScreen();
			Windows.resetOverflow();
			Event.stopObserving(document, "keypress", this.eventKeyPress);
			
		}
		// To avoid bug on scrolling bar
		Element.setStyle(this.getContent(), {overflow: "hidden"});
	
		if (this.hideEffectOptions)
			this.hideEffect(this.element, this.hideEffectOptions);	
		else
			this.hideEffect(this.element);	
	 	if(this.iefix) 
			Element.hide(this.iefix);
		
	},

	_checkIEOverlapping: function() {
    if(!this.iefix && (navigator.appVersion.indexOf('MSIE')>0) && (navigator.userAgent.indexOf('Opera')<0) && (Element.getStyle(this.element, 'position')=='absolute')) {
        new Insertion.After(this.element.id, '<iframe id="' + this.element.id + '_iefix" '+ 'style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" ' + 'src="javascript:false;" frameborder="0" scrolling="no"></iframe>');
        this.iefix = $(this.element.id+'_iefix');
    }
    if(this.iefix) 
			setTimeout(this._fixIEOverlapping.bind(this), 50);
	},

	_fixIEOverlapping: function() {
	    Position.clone(this.element, this.iefix);
	    this.iefix.style.zIndex = this.element.style.zIndex - 1;
	    Element.show(this.iefix);
	},

	setOpacity: function(opacity) {
		if (Element.setOpacity)
			Element.setOpacity(this.element, opacity);
	},
	
	setZIndex: function(zindex) {
		Element.setStyle(this.element,{zIndex: zindex});
		Windows.updateZindex(zindex, this);
	}
};

// Windows containers, register all page windows
var Windows = {
  windows: [],
  observers: [],

  maxZIndex: 0,

  addObserver: function(observer) {
    this.observers.push(observer);
  },
  
  removeObserver: function(observer) {  
    this.observers = this.observers.reject( function(o) { return o==observer });
  },
  
  notify: function(eventName, win) {  //  onStartResize(), onEndResize(), onStartMove(), onEndMove(), onClose(), onDestroy()
    this.observers.each( function(o) {if(o[eventName]) o[eventName](eventName, win);});
  },

  // Gets window from its id
  getWindow: function(id) {
	  return this.windows.detect(function(d) { return d.getId() ==id });
  },

  // Registers a new window (called by Windows constructor)
  register: function(win) {
    this.windows.push(win);
  },
  
  // Unregisters a window (called by Windows destructor)
  unregister: function(win) {
    this.windows = this.windows.reject(function(d) { return d==win });
  }, 

  // Closes a window with its id
  close: function(id) {
  	win = this.getWindow(id);
  	// Asks delegate if exists
  	if (win.getDelegate() && ! win.getDelegate().canClose(win)) 
  		return;
	
      if (win) {
  			this.notify("onClose", win);
  			win.hide();
    		if (win.destroyOnClose)  
    			win.destroy();
  	}
  },

  unsetOverflow: function(except) {		
  	this.windows.each(function(d) { d.oldOverflow = Element.getStyle(d.getContent(), "overflow") || "auto" ; Element.setStyle(d.getContent(), {overflow: "hidden"}) });
  	if (except && except.oldOverflow)
  		Element.setStyle(except.getContent(), {overflow: except.oldOverflow});
  },

  resetOverflow: function() {
	  this.windows.each(function(d) { if (d.oldOverflow) Element.setStyle(d.getContent(), {overflow: d.oldOverflow}) });
  },

  updateZindex: function(zindex, win) {
  	if (zindex > this.maxZIndex)
  		this.maxZIndex = zindex;
    }
};

var Dialog = {
 	win: null,

	confirm: function(message, parameters) {
		var okLabel = parameters && parameters.okLabel ? parameters.okLabel : "Ok";
		var cancelLabel = parameters && parameters.cancelLabel ? parameters.cancelLabel : "Cancel";
		var windowParam = parameters  ? parameters.windowParameters : {};
		windowParam.className = windowParam.className || "alert";

		var content = "\
			<div class='" + windowParam.className + "_message'>" + message  + "</div>\
				<div class='" + windowParam.className + "_buttons'>\
					<input type='button' value='" + okLabel + "' onclick='Dialog.okCallback()'/>\
					<input type='button' value='" + cancelLabel + "' onclick='Dialog.cancelCallback()'/>\
				</div>\
		";
		return this.openDialog(content, parameters)
	},
	
	alert: function(message, parameters) {
		var okLabel = parameters && parameters.okLabel ? parameters.okLabel : "Ok";

		var windowParam = parameters ? parameters.windowParameters : {};
		windowParam.className = windowParam.className || "alert";

		var content = "\
			<div class='" + windowParam.className + "_message'>" + message  + "</div>\
				<div class='" + windowParam.className + "_buttons'>\
					<input type='button' value='" + okLabel + "' onclick='Dialog.okCallback()'/>\
				</div>\
		";
		return this.openDialog(content, parameters)
	},
	
	openDialog: function(content, parameters) {
		// remove old dialog
		if (this.win) 
			this.win.destroy();

		var windowParam = parameters ? parameters.windowParameters : {};
		windowParam.resizable = windowParam.resizable || false;
		windowParam.effectOptions = {duration: 1};

		this.win = new Window('modal_dialog', windowParam);
		this.win.getContent().innerHTML = content;
		this.win.showCenter(true);	
		
		this.win.cancelCallback = parameters.cancel;
		this.win.okCallback = parameters.ok;
		
		this.eventResize = this.recenter.bindAsEventListener(this);
  	Event.observe(window, "resize", this.eventResize);
  	Event.observe(window, "scroll", this.eventResize);

		return this.win;		
	},
	
	okCallback: function() {
		this.win.hide();
		Event.stopObserving(window, "resize", this.eventResize);
		Event.stopObserving(window, "scroll", this.eventResize);
      	
		if (this.win.okCallback)
			this.win.okCallback(this.win);
	},

	cancelCallback: function() {
		this.win.hide();
		Event.stopObserving(window, "resize", this.eventResize);
		Event.stopObserving(window, "scroll", this.eventResize);
		
		if (this.win.cancelCallback)
			this.win.cancelCallback(win);
	},

	recenter: function(event) {
		var arrayPageSize = WindowUtilities.getPageSize();

		// set height of Overlay to take up whole page and show
		$('overlay_modal').style.height = (arrayPageSize[1] + 'px');
		
		this.win.center();
	}
}
/*
	Based on Lightbox JS: Fullsize Image Overlays 
	by Lokesh Dhakar - http://www.huddletogether.com

	For more information on this script, visit:
	http://huddletogether.com/projects/lightbox/

	Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
	(basically, do anything you want, just leave my name and link)
*/

var isIE = navigator.appVersion.match(/MSIE/) == "MSIE";

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
var WindowUtilities = {
 	getPageScroll :function() {
		var yScroll;

		if (self.pageYOffset) {
			yScroll = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
			yScroll = document.documentElement.scrollTop;
		} else if (document.body) {// all other Explorers
			yScroll = document.body.scrollTop;
		}

		arrayPageScroll = new Array('',yScroll) 
		return arrayPageScroll;
	},

	// getPageSize()
	// Returns array with page width, height and window width, height
	// Core code from - quirksmode.org
	// Edit for Firefox by pHaez
	getPageSize: function(){
		var xScroll, yScroll;
	
		if (window.innerHeight && window.scrollMaxY) {	
			xScroll = document.body.scrollWidth;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}
	
		var windowWidth, windowHeight;
		if (self.innerHeight) {	// all except Explorer
			windowWidth = self.innerWidth;
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}	
	
		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else { 
			pageHeight = yScroll;
		}

		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){	
			pageWidth = windowWidth;
		} else {
			pageWidth = xScroll;
		}

		arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
		return arrayPageSize;
	},

 	disableScreen: function(className) {
		WindowUtilities.initLightbox(className);
		var objBody = document.getElementsByTagName("body").item(0);

		// prep objects
	 	var objOverlay = $('overlay_modal');

		var arrayPageSize = WindowUtilities.getPageSize();

		// Hide select boxes as they will 'peek' through the image in IE
		if (isIE) {
			selects = document.getElementsByTagName("select");
		    for (var i = 0; i != selects.length; i++) {
		    	selects[i].style.visibility = "hidden";
		    }
		}	
	
		// set height of Overlay to take up whole page and show
		objOverlay.style.height = (arrayPageSize[1] + 'px');
		objOverlay.style.display = 'block';	
	},

 	enableScreen: function() {
	 	var objOverlay = $('overlay_modal');
		if (objOverlay) {
			// hide lightbox and overlay
			objOverlay.style.display = 'none';

			// make select boxes visible
			if (isIE) {
				selects = document.getElementsByTagName("select");
			    for (var i = 0; i != selects.length; i++) {
					selects[i].style.visibility = "visible";
				}
			}
			objOverlay.parentNode.removeChild(objOverlay);
		}
	},

	// initLightbox()
	// Function runs on window load, going through link tags looking for rel="lightbox".
	// These links receive onclick events that enable the lightbox display for their targets.
	// The function also inserts html markup at the top of the page which will be used as a
	// container for the overlay pattern and the inline image.
	initLightbox: function(className) {
		// Already done, just update zIndex
		if ($('overlay_modal')) {
			Element.setStyle('overlay_modal', {zIndex: Windows.maxZIndex + 10});
		}
		// create overlay div and hardcode some functional styles (aesthetic styles are in CSS file)
		else {
			var objBody = document.getElementsByTagName("body").item(0);
			var objOverlay = document.createElement("div");
			objOverlay.setAttribute('id', 'overlay_modal');
			objOverlay.className = "overlay_" + className
			objOverlay.style.display = 'none';
			objOverlay.style.position = 'absolute';
			objOverlay.style.top = '0';
			objOverlay.style.left = '0';
			objOverlay.style.zIndex = Windows.maxZIndex + 10;
		 	objOverlay.style.width = '100%';

			objBody.insertBefore(objOverlay, objBody.firstChild);
		}
	},
	
	setCookie: function(value, parameters) {
    document.cookie= parameters[0] + "=" + escape(value) +
      ((parameters[1]) ? "; expires=" + parameters[1].toGMTString() : "") +
      ((parameters[2]) ? "; path=" + parameters[2] : "") +
      ((parameters[3]) ? "; domain=" + parameters[3] : "") +
      ((parameters[4]) ? "; secure" : "");
  },

  getCookie: function(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
      begin = dc.indexOf(prefix);
      if (begin != 0) return null;
    } else {
      begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1) {
      end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
  }
}


