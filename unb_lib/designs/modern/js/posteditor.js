// Unclassified NewsBoard
// Copyright 2003-9 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// posteditor.js
// Post editor JavaScript resources

// Event handler for keypress events on a textbox
//
// in where = (object) Textbox to handle the event for
//
// returns (bool) false: event was handled
//         null: event was not handled and should be passed on
//
function UnbTextKeydownHandler(where)
{
	if (!window.event) return true;
	var keycode = window.event.keyCode;

	if (keycode == 9)
	{
		if (document.selection.createRange().duplicate().text.length)
		{
			if (!window.event.shiftKey)
			{
				document.selection.createRange().duplicate().text =
					"\t" +
					document.selection.createRange().duplicate().text.replace(/\n/g, "\n\t") +
					"\n";
			}
			else
			{
				document.selection.createRange().duplicate().text =
					document.selection.createRange().duplicate().text.replace(/\n\t/g, "\n");
				document.selection.createRange().duplicate().text =
					document.selection.createRange().duplicate().text.replace(/^\t/g, "") +
					"\n";
			}
		}
		else
		{
			if (!window.event.shiftKey)
			{
				UnbInsertText("\t");
			}
		}
		return false;
	}
}

// Handle a text editor button
//
// in event = (object) Original event object
// in cmd = (string) Command name
// in arg1 = Used for additional data like colour, font or text size
//
function UnbEditorDoCmd(event, cmd, arg1)
{
	textbox.focus();   // TODO: make "textbox" more flexible, also in other functions
	switch (cmd)
	{
		case "bold":
			UnbEncloseText("[b]", "[/b]", 1); break;
		case "italic":
			UnbEncloseText("[i]", "[/i]", 1); break;
		case "underline":
			UnbEncloseText("[u]", "[/u]", 1); break;
		case "strike":
			UnbEncloseText("[s]", "[/s]", 1); break;
		case "mono":
			UnbEncloseText("[m]", "[/m]", 1); break;
		case "quote":
			if (event.shiftKey)
			{
				UnbInsertText("[/quote]\n\n\n\n[quote]\n");
				// If on Mozilla, move cursor to correct position
				if (textbox.selectionStart >= 0) textbox.selectionStart = textbox.selectionEnd -= 10;
			}
			else
			{
				UnbEncloseText("[quote]\n", "\n[/quote]", 1);
			}
			break;
		case "code":
			if (event.shiftKey)
			{
				UnbInsertText("[/code]\n\n\n\n[code]\n");
				// If on Mozilla, move cursor to correct position
				if (textbox.selectionStart >= 0) textbox.selectionStart = textbox.selectionEnd -= 10;
			}
			else
			{
				UnbEncloseText("[code]\n", "\n[/code]", 1);
			}
			break;
		case "url":
			if (event.shiftKey)
			{
				UnbEncloseText("[url=]", "[/url]", 0);
				// If on Mozilla, move cursor to correct position
				if (textbox.selectionStart >= 0) textbox.selectionStart = textbox.selectionEnd = textbox.selectionStart + 5;
			}
			else
			{
				UnbEncloseText("[url]", "[/url]", 1);
			}
			break;
		case "img":
			UnbEncloseText("[img]", "[/img]", 1); break;
		case "color":
			UnbEncloseText("[color=" + arg1 + "]", "[/color]", 1); break;
		case "font":
			UnbEncloseText("[font=" + arg1 + "]", "[/font]", 1); break;
		case "size":
			UnbEncloseText("[size=" + arg1 + "]", "[/size]", 1); break;

		// "not currently supported":
		case "undo":
			document.selection.createRange().execCommand("Undo");
		case "redo":
			document.selection.createRange().execCommand("Redo");
	}
}

// Enclose the currently selected text with an opening and closing text
//
// If there is no selection, the text is inserted or appended.
// If the current selection already includes the text, it is removed instead.
//
// in t_open = (string) Opening text
// in t_close = (string) Closing text
// in cursorpos = (int) 0: begin | 1: middle | 2: end
//                      (only relevant if no text is selected)
//
function UnbEncloseText(t_open, t_close, cursorpos)
{
	// TODO: get from clientinfo lib
	var is_ie = (navigator.appName == "Microsoft Internet Explorer");
	if (is_ie && document.selection && document.selection.createRange().duplicate().text.length)
	{
		// IE with selected text
		var seltext = document.selection.createRange().duplicate().text;
		if (seltext.substring(0, t_open.length) == t_open &&
			seltext.substring(seltext.length - t_close.length, seltext.length) == t_close)
		{
			// tags are already there, remove them
			document.selection.createRange().duplicate().text = seltext.substring(t_open.length, seltext.length - t_close.length);
		}
		else
		{
			document.selection.createRange().duplicate().text = t_open + seltext + t_close;
		}
	}
	else if (textbox.selectionEnd && (textbox.selectionEnd - textbox.selectionStart > 0))
	{
		// Mozilla with selected text
		var start_selection = textbox.selectionStart;
		var end_selection = textbox.selectionEnd;
		var new_endsel;
		var scroll_top = textbox.scrollTop;
		var scroll_left = textbox.scrollLeft;

		// fetch everything from start of text area to selection start
		var start = textbox.value.substring(0, start_selection);
		// fetch everything from start of selection to end of selection
		var seltext = textbox.value.substring(start_selection, end_selection);
		// fetch everything from end of selection to end of text area
		var end = textbox.value.substring(end_selection, textbox.textLength);

		if (seltext.substring(0, t_open.length) == t_open &&
			seltext.substring(seltext.length - t_close.length, seltext.length) == t_close)
		{
			// tags are already there, remove them
			seltext = seltext.substring(t_open.length, seltext.length - t_close.length);
			new_endsel = end_selection - t_open.length - t_close.length;
		}
		else
		{
			seltext = t_open + seltext + t_close;
			new_endsel = end_selection + t_open.length + t_close.length;
		}

		textbox.value = start + seltext + end;

		textbox.selectionStart = start_selection;
		textbox.selectionEnd = new_endsel;
		textbox.scrollTop = scroll_top;
		textbox.scrollLeft = scroll_left;
	}
	else
	{
		// no selection, insert opening/closing tags alone
		UnbInsertText(t_open + t_close);
		if (cursorpos <= 1) textbox.selectionEnd -= t_close.length;
		if (cursorpos <= 0) textbox.selectionEnd -= t_open.length;
	}
}

// Insert text into a textbox at the current cursor position
//
// in what = (string) Text to insert
// in replace = (int) replace characters before cursor?
//
function UnbInsertText(what, replace)
{
	if (replace == null) replace = 0;

	if (textbox.createTextRange)
	{
		textbox.focus();
		document.selection.createRange().duplicate().text = what;
		textbox.focus();
	}
	else if (textbox.selectionStart >= 0)
	{
		// Mozilla without selected text
		var start_selection = textbox.selectionStart;
		var end_selection = textbox.selectionEnd;
		var scroll_top = textbox.scrollTop;
		var scroll_left = textbox.scrollLeft;

		// fetch everything from start of text area to selection start
		var start = textbox.value.substring(0, start_selection - replace);
		// fetch everything from end of selection to end of text area
		var end = textbox.value.substring(end_selection, textbox.textLength);

		textbox.value = start + what + end;

		textbox.selectionStart = textbox.selectionEnd = start_selection - replace + what.length;
		textbox.focus();
		textbox.scrollTop = scroll_top;
		textbox.scrollLeft = scroll_left;
	}
	else
	{
		textbox.value += what;
		textbox.focus();
	}
}

var UnbUpdateTimeout = null;

// Check the current length of a textbox
//
// in where = (object) Textbox object
// in max = (int) Maximum allowed length
//
// returns (bool) true: limit not reached; false: text length exceeded limit
//
function UnbCheckLength(where, max)
{
	if (!where) return;

	// Update the current post length asynchronously
	if (UnbUpdateTimeout) window.clearTimeout(UnbUpdateTimeout);
	UnbUpdateTimeout = window.setTimeout("UnbUpdateLength(" + where.value.length + ", " + max + ")", 100);

	if (max <= 0 || where.value.length <= max) return true;
	where.value = where.value.substr(0, max);
	return false;
}

// Update the length counter in the post editor
//
// The counter will be highlighted when more than 95% of the allowed text
// length is used.
//
// in len = (int) current text length
// in max = (int) maximum allowed length
//
function UnbUpdateLength(len, max)
{
	var textlength = document.getElementById("textlength");
	if (textlength)
	{
		textlength.firstChild.nodeValue = len;
		if (max > 0 && len >= max* 0.95)
			textlength.className = "warning";
		else
			textlength.className = "";
	}
}

function UnbTextKeyup(e, where)
{
	var keycode = e.which;
	keycode = UnbGetCharBeforeCursor(where);
	var alts = unicodeAlternatives(keycode);
	if (alts == "")
	{
		// See what this character is an alternative for
		for (i = 0x20; i <= 0x7F; i++)
		{
			alts = unicodeAlternatives(i);
			if (alts.indexOf(String.fromCharCode(keycode)) != -1) break;
		}
		if (i > 0x7F) alts = "";
	}
	UnbShowChars(alts);
}

// Get the character before the current cursor position in a text field
//
// in obj = (object) Text field object
//
// returns (int) character code
//
function UnbGetCharBeforeCursor(obj)
{
	if (obj == null) return;
	if (obj.selectionStart > 0)
	{
		return obj.value.charCodeAt(obj.selectionStart - 1);
	}
	return 0;
}

// Show the list of alternative characters in the post editor
//
// in chars = (string) list of alternative characters
//
function UnbShowChars(chars)
{
	var child;
	while (child = getel("altchars").firstChild)
	{
		getel("altchars").removeChild(child);
	}

	for (var pos = 0; pos < chars.length; pos++)
	{
		if (pos < 10)
		{
			var num = document.createTextNode(pos + ":");
			var smallNum = document.createElement("small");
			smallNum.appendChild(num);
			getel("altchars").appendChild(smallNum);
		}

		var x = chars.substr(pos, 1);
		var aTag = document.createElement("a");
		var aText = document.createTextNode(x);
		aTag.appendChild(aText);
		aTag.setAttribute("href", "javascript:nothing()");
		aTag.setAttribute("class", "altchar");
		if (pos < 10)
			aTag.setAttribute("accesskey", pos);
		x = x.replace(/"/, "\\\"");
		aTag.setAttribute("onclick", "UnbInsertText(\"" + x + "\", 1)");
		getel("altchars").appendChild(aTag);

		var space = document.createTextNode(" ");
		getel("altchars").appendChild(space);
	}

	if (chars.length == 0)
	{
		var aTag = document.createElement("span");
		var aText = document.createTextNode(" ");
		aTag.appendChild(aText);
		aTag.setAttribute("class", "altchar");
		getel("altchars").appendChild(aTag);

		aTag = document.createElement("small");
		aText = document.createTextNode(ALTCHARS_NOTE);
		aTag.appendChild(aText);
		getel("altchars").appendChild(aTag);
	}
}

