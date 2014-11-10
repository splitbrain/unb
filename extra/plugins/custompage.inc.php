<?php
// Custompage demo plug-in for UNB

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

$info = false;
$error = false;

// Include CSS stylesheet
#UnbRequireCss('custompage');

// Begin page
UnbBeginHTML($UNB_T['_custompage.pagetitle']);

// Make template parameters more easily accessible
$TP =& $UNB['TP'];

// Don't index this page for search engines
#$TP['headNoIndex'] = true;

// Show information and error messages if there are any
if ($info)
{
	$TP['infoMsg'] .= $info . '<br />';
}
if ($error)
{
	$TP['errorMsg'] .= $error . '<br />';
	UnbErrorLog($error);
}

// Use a template to show this page. All parameters for this template are passed
// in the $TP array, as usual.
#UteRemember('custompage.html', $TP);

// Do not use a template to show this page. Therefore the _head template is not
// remembered for later output but directly printed out now. Afterwards we can
// print out our page with normal echo/print commands.
UteShowAll();

// Show some text
echo '<p>Hello world! I am the custompage demo plug-in.</p>';

echo '<div class="p"><b>Recent threads from all forums:</b></div>';
echo '<div class="p">';
$threads = UnbFindThreadsObjects(null, null, 10);
foreach ($threads as $thread)
{
	$forum =& new IForum($thread->GetForum());
	echo '<div>' . UnbDate('m-d H:i', $thread->GetLastPostDate()) . ', ' .
		'<a href="' . UnbLink('@thread', 'id=' . $thread->GetID(), true) . '">' .
		t2h(str_limit($thread->GetSubject(), 65)) . '</a> <small>(in: ' .
		t2h(str_limit($forum->GetName(), 65)) . ')</small></div>';
}
echo '</div>';

$forumid = 26;
echo '<div class="p"><b>Recent news from forum no. ' . $forumid . ':</b></div>';
$threads = UnbFindThreadsObjects('t.Forum = ' . $forumid, 'Date', 4);
foreach ($threads as $thread)
{
	$post =& new IPost;
	$post->Find('Thread = ' . $thread->GetID(), 'Date', 1);
	echo '<div class="p" style="border: solid 1px gray; padding: 4px;">' . UnbDate('Y-m-d H:i', $thread->GetDate()) . ', ' .
		'<a href="' . UnbLink('@thread', 'id=' . $thread->GetID(), true) . '">' .
		t2h(str_limit($thread->GetSubject(), 65)) . '</a>:<br />' .
		AbbcProc($post->GetMsg()) . '</div>';
}


// Update page hits statistics
UnbUpdateStat('PageHits', 1);

// Finish the page. This uses a template again which is printed out at last.
UnbEndHTML();
?>
