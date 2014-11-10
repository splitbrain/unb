var UnbScrollOffset = 0;

// Remember we have to scroll y pixels more up when jumping to a specific post
// because there is some important note displayed above.
//
function UnbPostScrollUp(y)
{
	UnbScrollOffset -= y;
}

/*function toggleMoreInfo(id, force)
{
	var ret = toggleVisId("post_" + id + "_more", force);

	//unforce = null;
	//if (force != null) unforce = !force;
	//toggleVisId("post_" + id + "_head_plus", unforce);
	//toggleVisId("post_" + id + "_head_minus", force);
	return ret;
}*/

function toggleReplyCont(id, force)
{
	toggleVisId("reply_" + id + "_cont", force);
}

function replyTo(postid)
{
	if (postid == current_reply_postid) return;

	// hide currently displayed reply container
	toggleReplyCont(current_reply_postid, 0);

	// update form field
	if (postid > 0)
		document.getElementById("reply_to_postid").value = document.getElementById("post_id_" + postid).value;

	// move reply form to new reply container:
	// remove any present non-HTMLDiv child
/*	var length = document.getElementById("reply_" + postid + "_cont").childNodes.length;
	var info = "";
	for (var n = 0; n < length; n++)
	{
		var node = document.getElementById("reply_" + postid + "_cont").childNodes[n];
		info += "node " + node.nodeName + "\n";
		if (node.nodeType > 1)
		{
			info += "removed node " + node.nodeName + "\n";
			document.getElementById("reply_" + postid + "_cont").removeChild(node);
		}
	}
*/
	// remove child from where it is
	var replyForm = document.getElementById("reply_" + current_reply_postid + "_cont").removeChild(
		document.getElementById("reply_form"));

	// insert reply form to new reply container
	document.getElementById("reply_" + postid + "_cont").appendChild(
		replyForm);

	current_reply_postid = postid;

	if (postid > 0)
	{
		// show new reply container
		toggleReplyCont(postid, 1);

		// scroll reply box into view and focus it
		var y = document.getElementById("reply_" + postid + "_cont").offsetTop
			- window.innerHeight
			+ document.getElementById("reply_" + postid + "_cont").offsetHeight
			+ 30;
		if (y < 0) y = 0;
		window.scrollTo(0, y);
		document.getElementById("reply_message").focus();
	}
	//alert(info);
}

var current_reply_postid = 0;

// Togle the display of line numbers in [code] blocks
//
function toggleLineNos()
{
	return;   // does not work this way (see CSS)

//	var tags = document.getElementsByTagName("ol");
//	for (var i in tags)
//	{
//		tags[i].className = "nolinenos";
//	}
}

