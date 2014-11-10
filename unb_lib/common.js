// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// common.js
// JavaScript resources

// Dummy function for <a href="javascript:..."> links with an onclick handler
//
function nothing()
{
}

// Open a new popup window
//
// in url = (string) URL to open
// in w = (int) Width in pixels
// in h = (int) Height in pixels
//
function UnbPopup(url, w, h)
{
	window.open(url, "", "width=" + w + ", height=" + h + ", resizable=yes, scrollbars=yes");
}

// Go to a given URL after a confirmation message box
//
// in url = (string) URL to go to if the user clicked "Yes"
//
function UnbGoDelete(url)
{
	if (confirm(STRING_DELETE)) document.location.href = url;
}

// Get an element by its ID
//
// in id = (string) object ID
//
function getel(id)
{
	return document.getElementById(id);
}

// Disable an object in 10ms
//
// in id = (string) object ID
//
function disableObj(id)
{
	id = "document.getElementById('" + id + "')";
	window.setTimeout("if (" + id + ") { " + id + ".blur(); " + id + ".disabled = true; }", 10);
}

// Open a new window with the forum start page and another design selected
//
// The design selection is read directly from the HTML form element.
//
function UnbPreviewDesign()
{
	var url = DESIGN_URL.replace(/___/, document.getElementById('DesignSelection').value);
	window.open(url, '_blank');
	//document.location.href = url;
}

// Toggle visibility of an object
//
// in id = (string) Object's ID
// in state = (bool) true/false: Force to show/hide it [optional]
//
function toggleVisId(id, state)
{
	return toggleVisObj(document.getElementById(id), state);
}

// Toggle visibility of an object
//
// in obj = (object) Object
// in state = (bool) true/false: Force to show/hide it [optional]
//
function toggleVisObj(obj, state)
{
	if (obj == null) return;
	if (state == null)
	{
		if (obj.style.display == "") newstate = "none";
		else                         newstate = "";
	}
	else
	{
		if (state) newstate = "";
		else       newstate = "none";
	}
	obj.style.display = newstate;
	return (newstate == "" ? 1 : -1);
}

// Toggle enabled state of an object
//
// in id = (string) Object's ID
// in state = (bool) true/false: Force to enable/disable it [optional]
//
function toggleEnabledId(id, state)
{
	toggleEnabledObj(document.getElementById(id), state);
}

// Toggle enabled state of an object
//
// in obj = (object) Object
// in state = (bool) true/false: Force to enable/disable it [optional]
//
function toggleEnabledObj(obj, state)
{
	if (obj == null) return;
	if (state == null)
	{
		if (obj.disabled) newstate = false;
		else              newstate = true;
	}
	else
	{
		newstate = !state;
	}
	obj.disabled = newstate;
}

// Scroll the window down by the height of an item
//
// in id = (string) Object's ID
//
function scrollDownId(id)
{
	scrollDownObj(document.getElementById(id));
}

// Scroll the window down by the height of an item
//
// in obj = (object) Object
//
function scrollDownObj(obj)
{
	if (obj == null) return;
	window.scrollBy(0, obj.offsetHeight);
}

// Scroll the window up by the height of an item
//
// in id = (string) Object's ID
//
function scrollUpId(id)
{
	scrollUpObj(document.getElementById(id));
}

// Scroll the window up by the height of an item
//
// in obj = (object) Object
//
function scrollUpObj(obj)
{
	if (obj == null) return;
	window.scrollBy(0, -obj.offsetHeight);
}

// Toggle visibility of an object and scroll the page up and down
//
// in id = (string) Object's ID
//
function toggleVisIdScroll(id)
{
	var obj = document.getElementById(id);
	if (obj == null) return;

	if (obj.style.display == "")
	{
		window.scrollBy(0, -obj.offsetHeight);
		newstate = "none";
		obj.style.display = newstate;
	}
	else
	{
		newstate = "";
		obj.style.display = newstate;
		window.scrollBy(0, obj.offsetHeight);
	}
	return (newstate == "" ? 1 : -1);
}

// Set an image's source attribute depending on a condition
//
// This condition can be the return value of toggleVisId, e.g.
//
// in id = (string) Image object's ID
// in cond = (cool) Condition
// in trueimg = (string) True case image source
// in falseimg = (string) False case image source
//
function setImageId(id, cond, trueimg, falseimg)
{
	setImageObj(document.getElementById(id), cond, trueimg, falseimg);
}

// Set an image's source attribute depending on a condition
//
// This condition can be the return value of toggleVisId, e.g.
//
// in obj = (object) Image object
// in cond = (cool) Condition
// in trueimg = (string) True case image source
// in falseimg = (string) False case image source
//
function setImageObj(obj, cond, trueimg, falseimg)
{
	if (obj == null) return;
	obj.src = cond ? trueimg : falseimg;
}

// Vertically resize an element by its ID
//
// in id = (string) Object ID
// in cy = (int) Height offset
// in negative = (bool) Rezize to the top instead of the bottom. This is achieved by scrolling the window accordingly.
//
function resizeBoxId(id, cy, negative)
{
	resizeBoxObj(document.getElementById(id), cy, negative);
}

// Vertically resize an element
//
// in obj = (string) Element object
// in cy = (int) Height offset
// in negative = (bool) Rezize to the top instead of the bottom. This is achieved by scrolling the window accordingly.
//
function resizeBoxObj(obj, cy, negative)
{
	if (obj == null) return;
	//var newCy = obj.offsetHeight + cy;
	var newCy = obj.clientHeight + cy;
	var minCy = 0;
	var maxCy = 1000;
	if (obj.tagName.toLowerCase() == "textarea") minCy = 60;
	if (newCy < minCy) newCy = minCy;
	if (newCy > maxCy) newCy = maxCy;
	var diffY = newCy - obj.clientHeight;
	obj.style.height = newCy + "px";
	
	// scroll page in the opposite offset direction to make the box resize at the top
	if (negative == true)
	{
		window.scrollBy(0, diffY);
	}
}

// Toggle selection state of a button element
//
function SelectButton(obj)
{
	var cn = " " + obj.className + " ";
	var isSel = false;
	if (cn.indexOf("selected") == -1)
	{
		// object is NOT selected
		cn += "selected ";
		isSel = true;
	}
	else
	{
		// object is selected
		cn = cn.replace(/ selected /, " ");
	}
	obj.className = cn;
	return isSel;
}

// Get local offset from GMT in minutes
//
function fetchTimezone()
{
	var localclock = new Date();
	return -localclock.getTimezoneOffset();
}

// store user timezone as cookie if it's different from the current default timezone
if (!NOCOOKIES && fetchTimezone() != TIMEZONE)
{
	document.cookie = 'UnbTimezone=' + fetchTimezone();
}


// ---------- Global enhanced keyboard controls support ----------

var globalKeyHandlers = new Array();

// Register a key handler function
//
// in keycode = (int) Keycode to listen on
// in ascii = (int) ASCII value to listen on. Either keycode or ASCII code must
//                  be specified
// in flags = (int) Key flags to listen on. Combination of:
//                  1: Alt key
//                  2: Control key
//                  4: Shift key
// in funcname = (string) Key handler function name
// in funcparam = (string) Parameter to pass to the key handler function
//
function UnbGlobalRegisterKeyHandler(keycode, ascii, flags, funcname, funcparam)
{
	var newarr = new Array();
	newarr["keycode"] = keycode;
	newarr["ascii"] = ascii;
	newarr["flags"] = flags;
	newarr["funcname"] = funcname;
	newarr["funcparam"] = funcparam;
	globalKeyHandlers.push(newarr);
}

// Keypress handler
//
function UnbGlobalKeyDispatcher(e)
{
	var myFlags;
	var item;

	for (var i in globalKeyHandlers)
	{
		item = globalKeyHandlers[i];

		myFlags = 0;
		if (e.altKey) myFlags |= 1;
		if (e.ctrlKey) myFlags |= 2;
		if (e.shiftKey) myFlags |= 4;

		if ((item["keycode"] && item["keycode"] == e.keyCode ||
		     item["ascii"] && item["ascii"] == e.which) &&
		    item["flags"] == myFlags)
		{
			return item["funcname"](e, item["funcparam"]);
		}
	}
	//alert("unknown key pressed: keycode = " + e.keyCode + " which = " + e.which);
}

// enable keypress dispatcher (not for IE)
if (navigator.appName != "Microsoft Internet Explorer")
{
	window.captureEvents(Event.KEYPRESS);
	window.onkeypress = UnbGlobalKeyDispatcher;
}

// ---------- IE PNG hack ----------

// Correctly handle PNG transparency in Win IE 5.5 or higher.
//
// This hack is only applied to a list of files defined by the selected design.
//
function UnbCorrectPNGforIE()
{
	var imgStyle;
	var files = alphaPNGfiles;

	for (var i = 0; i < document.images.length; i++)
	{
		var img = document.images[i];
		var doit = false;
		for (var n in files)
		{
			if (img.src.substring(img.src.length - files[n].length) == files[n])
			{
				doit = true;
				break;
			}
		}
		if (!doit)
		{
			if (img.src.substring(img.src.length - 4) == ".png" &&
			    img.src.match(/\/_smile\//))
				doit = true;
		}
		if (doit)
		{
			img.outerHTML = "<span" +
				((img.id) ? " id=\"" + img.id + "\"" : "") +
				((img.className) ? " class=\"" + img.className + "\"" : "") +
				((img.title) ? " title=\"" + img.title + "\"" : "") +
				" style=\"width:" + img.width + "px; height:" + img.height + "px; display:inline-block; line-height:1px; margin:0px; font-size:1px; padding:0px;" +
					((img.align == "left") ? " float:left;" : "") +
					((img.align == "right") ? " float:right;" : "") +
					((img.parentElement.href) ? " cursor:hand;" : "") +
					img.style.cssText + "; " +
					"filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + img.src + "', sizingMethod='scale');\"></span>";
			i--;
		}
	}
}

if (navigator.appName == "Microsoft Internet Explorer")
{
	// This function enables the Internet Explorer work-around to display alpha-
	// transparent PNG images with their correct transparency. This uses the
	// filenames of all images to convert provided by each design definition.
	// The function itself works fine and does its job in a reasonable time,
	// however it breaks clickable smilies in the post editor. They're still
	// displayed correctly but clicking them leads to unpredictable behaviour.
	// If you prefer working smilies selectbox over correct transparency, and
	// you're using a full-transparency smilies set, you should disable the
	// following line.
	//
	//window.attachEvent("onload", UnbCorrectPNGforIE);
}

// Scroll to URL anchor
//
function UnbScrollToAnchor(event)
{
	// disable a running timeout if we have loaded faster than that
	try
	{
		window.clearTimeout(globalScrollTimeout);
	}
	catch (ex)
	{
	}
	
	var add_offset = 0;
	try
	{
		add_offset += UnbScrollOffset;
	}
	catch (ex)
	{
	}
	
	var url = window.location.href;
	var id = "";
	var id_pos = url.lastIndexOf("#");
	if (id_pos > 0)
	{
		id = url.substring(id_pos + 1, url.length);
	}
	else
	{
		var m = url.match(/\/post\/([0-9]+)/);
		if (m != null)
			id = "p" + m[m.length - 1];
		else
		{
			m = url.match(/[?&]postid=([0-9]+)/);
			if (m != null)
				id = "p" + m[m.length - 1];
		}
	}
	if (id != "")
	{
		var obj = document.getElementById(id);
		if (obj != null)
		{
			var top = obj.offsetTop;
			while (obj = obj.offsetParent) top += obj.offsetTop;
			top -= 7;
			top += add_offset;
			window.scrollTo(0, top);
		}
	}
}

if (window.addEventListener != null)
	window.addEventListener("load", UnbScrollToAnchor, false);

// already scroll there after 1 second if the page hasn't loaded until then
var globalScrollTimeout = window.setTimeout("UnbScrollToAnchor();", 1000);

// Event handler that adds mouse hover statusbar information about HTML forms'
// submit buttons' target URL
//
// NOTE: There are several problems with this function:
//       * It doesn't correctly handle the base path on Firefox, but it does on Opera.
//       * It always shows the page's last form's action URL for all submit buttons on the page.
//         Maybe something from http://weblogs.asp.net/asmith/archive/2003/10/06/30744.aspx can help here.
// TODO,FIXME
//
function UnbAddFormStatus(event)
{
	var i;
	for (i = 0; i < document.forms.length; i++)
	{
		// For all forms in the document
		var f = document.forms[i];
		var url = f.getAttribute("action");
		if (url.indexOf("/") == 0)
			url = document.location.href.match(/^(.*?)\//)[1] + "/" + url;
		else if (url.indexOf("/") == -1)
			url = document.location.href.match(/^(.*)\//)[1] + "/" + url;

		var method = f.method.toUpperCase();

		var text = "";
		text += url;
		if (method == "POST") text += " (" + method + ")";
		
		var j;
		for (j = 0; j < f.elements.length; j++)
		{
			// For all elements in that form
			var e = f.elements[j];
			if (e.tagName == "input" && (e.type == "submit" || e.type == "image"))
			{
				// If submit button
				e.addEventListener("mouseover", function() {window.status = text;}, false);
				e.addEventListener("mouseout", function() {window.status = "";}, false);
				
				//alert("Added info: " + text + " for button: " + e.value);
			}
		}
	}
}

// Call handler function when the page has finished loading
//window.addEventListener("load", UnbAddFormStatus, false);

