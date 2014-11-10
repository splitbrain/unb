<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// UNB RSS script
// Generate a newsfeed of threads

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// -------------------- BEGIN configuration --------------------

$forumid = intval($_REQUEST['forum']);   // Forum to read from. Cannot be 0 if type=1
$limit = 30;                             // Limit number of news items
$type = intval($_REQUEST['type']);       // 1 - List threads in given forum, ORDER BY thread start date DESC
                                         // 2 - List all posts, ORDER BY last post date DESC

$format = 'RSS2.0';   // valid: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated), OPML, ATOM, ATOM0.3
#$cache_time = 300;   // cache files 5min
$cache_time = 3600;   // cache files 1h
#$cache_time = 0;     // don't use caching (for debugging)

// Uncomment this line to use ATOM feeds instead of RSS 2.0:
//$format = 'ATOM';

// -------------------- END configuration --------------------

if (!$type) $type = 2;
if ($type == 1 && !$forumid) die('No forum ID specified.');

if (rc('admin_lock')) die('Board is locked.');

$ABBC['Config']['derefer'] = '';
$ABBC['Config']['target'] = '';

// Include UNB's public library
// This must be the same path as in the forum.php file which should point to
// your board's installation library path.
$UNB['ContentType'] = '';   // This custom Content-type header will be sent by common.lib, if not empty
#require_once(dirname(__FILE__) . '/thread.lib.php');
#require_once(dirname(__FILE__) . '/post.lib.php');
#require_once(dirname(__FILE__) . '/user.lib.php');
require_once(dirname(__FILE__) . '/feedcreator.lib.php');

// NOTE: The RSS feed must use guest access to not allow access to non-public data.
$UNB['LoginUserID'] = 0;
UnbReadACL();

$ABBC['Config']['subsets'] = ABBC_MINIMUM | ABBC_SIMPLE | ABBC_QUOTE | ABBC_FONT | ABBC_URL | ABBC_IMG | ABBC_LIST | ABBC_SPECIAL | ABBC_DONTINT;
$ABBC['Config']['output_div'] = false;

$ABBC['Config']['subsets'] |= ABBC_CODE;
$ABBC['Tags']['code']['htmlopen0']  = "<tt>";
$ABBC['Tags']['code']['htmlcont0']  = "$1";
$ABBC['Tags']['code']['htmlclose0'] = "</tt>";
$ABBC['Tags']['code']['htmlopen1']  = "<tt>";
$ABBC['Tags']['code']['htmlcont1']  = "$2";
$ABBC['Tags']['code']['htmlclose1'] = "</tt>";

$ABBC['Config']['subsets'] |= ABBC_SMILIES;
$ABBC['Config']['smileurl'] = TrailingSlash(rc('home_url')) . $ABBC['Config']['smileurl'];

// FeedCreator: your local timezone, set to '' to disable or for GMT
define('TIME_ZONE', date('O'));
// TODO: Copy from UNB config
//       why do we need this information?

$filename = '';

if ($type == 1)
	$filename = strtolower(str_replace('.', '', $format)) . '.' . $forumid . '.xml';
if ($type == 2)
	$filename = strtolower(str_replace('.', '', $format)) . '.allposts.xml';

$filename = dirname(__FILE__) . '/rsscache/' . $filename;

$rss = new UniversalFeedCreator();
if ($cache_time) $rss->useCached($format, $filename, $cache_time);   // use cached version?
$rss->title = rc('forum_title') . ' News';
$rss->description = '';

//optional
$rss->descriptionTruncSize = 500;
$rss->descriptionHtmlSyndicated = true;

$rss->link = rc('home_url');

$thread = new IThread;
$post = new IPost;
$user = new IUser;

$order = '';
if ($type == 1)
{
	$order = 'Date DESC';
	$found = $thread->Find('Forum = ' . $forumid . ' AND NOT (Options & ' . UNB_THREAD_MOVED . ')', $order, $limit);
}
if ($type == 2)
{
	$order = 'Date DESC';
	$where = '';
	#if ($forumid) $where = 'Forum = ' . $forumid;
	$found = $post->Find($where, $order, $limit);
}

if ($found) do
{
	if ($type == 1)
	{
		if (!UnbCheckRights('viewforum', $thread->GetForum(), $thread->GetID()))
		{
			if ($thread->FindNext()) continue; else break;
		}

		$post->Find('Thread = ' . $thread->GetID(), 'Date', 1);
		$url = TrailingSlash(rc('home_url')) . UnbLink('@thread', 'id=' . $thread->GetID(), false, /*sid*/ false);
		$title = $thread->GetSubject();
	}
	if ($type == 2)
	{
		$thread->Load($post->GetThread());
		if (!UnbCheckRights('viewforum', $thread->GetForum(), $thread->GetID()))
		{
			$foundmore = $post->FindNext();
			continue;
		}
		$url = UnbMakePostLink($post, 0, 3);

		if ($post->GetDate() == $thread->GetDate())
		{
			$title = $thread->GetSubject();
			if ($thread->GetDesc())
				$title .= ' (' . $thread->GetDesc() . ')';
		}
		else if ($post->GetSubject() != '')
		{
			$title = $post->GetSubject() . ' (' . $UNB_T['topic'] . ': ' . $thread->GetSubject() . ')';
		}
		else
		{
			$title = 'Re: ' . $thread->GetSubject();
		}
	}

	$item = new FeedItem();
	$item->title = $title;
	$item->link = $url;
	$item->description = AbbcProc($post->GetMsg());

	//optional
	$item->descriptionTruncSize = 500;
	$item->descriptionHtmlSyndicated = true;

	$item->date = $post->GetDate();

	if ($post->GetUser() > 0)
	{
		$user->Load($post->GetUser());
		$username = $user->GetName();
	}
	else
	{
		$username = $post->GetUserName();
	}
	$item->author = $username;
	# ANDI
	#$item->guid = TrailingSlash(rc('home_url')) . $thread->GetID();
	$item->guid = $url;
	#if ($type == 2)
	#	$item->guid .= '#' . $post->GetID();

	$rss->addItem($item);

	if ($type == 1) $foundmore = $thread->FindNext();
	if ($type == 2) $foundmore = $post->FindNext();
}
while ($foundmore);

$rss->saveFeed($format, $filename);

?>
