<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// thread.inc.php
// Show Posts in a Thread

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/common_post.lib.php');

// -------------------- Import request variables --------------------

$threadid = intval($_REQUEST['id']);
if (isset($_GET['threadid'])) $threadid = intval($_GET['threadid']);   // for WBB1 compatibility
if ($threadid < 0) $threadid = 0;

$page = intval($_REQUEST['page']);
if ($page < -1 || $page == 0) $page = 1;

if (isset($_REQUEST['postid']) && $_REQUEST['postid'] > 0)
{
	// We only have a post ID as parameter:
	// Find the right thread and page to display

	$postid = intval($_REQUEST['postid']);
	$post = new IPost($postid);
	$threadid = $post->GetThread();

	$posts_per_page = rc('posts_per_page');
	if (!$posts_per_page)
		$page = 1;
	else
	{
		$date = $post->GetDate();
		$page = ceil($post->Count('Thread=' . $threadid . ' AND Date<=' . $date) / $posts_per_page);
	}
}

$error = '';

// ---------- Add watch method ----------
if (isset($_GET['watch']) &&
    $UNB['LoginUserID'] > 0 &&
    (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']) || $_GET['watch'] == UNB_NOTIFY_BOOKMARK) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread;
	if ($thread->Load($threadid))
	{
		$mode = intval($thread->IsWatched());
		$mode |= intval($_GET['watch']);
		$thread->SetWatched($mode);
	}
	UnbAddLog('watch_thread ' . $threadid . ' mode ' . $_GET['watch']);
	#UnbForwardHTML(UnbLink('@this', "id=$threadid&page=$page&nocount=1"));
}

// ---------- Remove watch method ----------
if (isset($_GET['unwatch']) &&
    $UNB['LoginUserID'] > 0 &&
    (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']) || $_GET['unwatch'] == UNB_NOTIFY_BOOKMARK) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread;
	if ($thread->Load($threadid))
	{
		$mode = intval($thread->IsWatched());
		$mode = $mode & ~intval($_GET['unwatch']);
		$thread->SetWatched($mode);
	}
	UnbAddLog('unwatch_thread ' . $threadid . ' mode ' . $_GET['unwatch']);
	if (intval($_GET['b2p']) > 0)   // go Back2Profile...
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2p']) . '&cat=watched'));
	}
	if (intval($_GET['b2b']) > 0)   // go Back2Bookmarks...
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2b']) . '&cat=bookmarks'));
	}
	/* else
	{
		UnbForwardHTML(UnbLink('@this', "id=$threadid&page=$page&nocount=1"));
	} */
}

// ---------- Split current thread ----------
$splitmode = false;
if (isset($_REQUEST['split']) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread($threadid);
	if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()) &&
	    UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID()))
	{
		if ($_REQUEST['split'] == 1)
		{
			$splitmode = true;
		}
		if ($_POST['split'] == 2 &&
		    $_POST['forumid'] >= 0)
		{
			if ($_POST['Subject'] == '')
			{
				UnbRequireTxt('post');   // for below error message
				$error .= $UNB_T['post.error.no subject'] . '<br />';
			}
			elseif (rc('topic_subject_minlength') &&
			        strlen(trim($_POST['Subject'])) < rc('topic_subject_minlength'))
			{
				UnbRequireTxt('post');   // for below error message
				$error .= str_replace('{min}', rc('topic_subject_minlength'), $UNB_T['post.error.subject too short']) . '<br />';
			}
			elseif (rc('topic_subject_maxlength') &&
			        strlen(trim($_POST['Subject'])) > min(rc('topic_subject_maxlength'), 150))
			{
				UnbRequireTxt('post');   // for below error message
				$error .= str_replace('{max}', rc('topic_subject_maxlength'), $UNB_T['post.error.subject too long']) . '<br />';
			}
			elseif (!is_array($_POST['PostID']) || !sizeof($_POST['PostID']))
			{
				// error: no post selected to split off
				// (just do nothing)
			}
			else
			{
				// Actually split the thread
				$post = new IPost;

				// Load the first post
				$post->Load($_POST['PostID'][0]);

				$newthread = new IThread;
				if ($newthread->Add($_POST['newforumid'], '', $_POST['Subject'], $opt, $post->GetDate()))
				{
					$newthreadid = $newthread->GetID();

					// close new thread if the original one is also closed
					if ($thread->IsClosed()) $newthread->SetOptions($newthread->GetOptions() | 1);

					// Set some more of the new thread's parameters
					$newthread->SetUser($post->GetUser(), $post->GetUserName());
					$newthread->SetDesc($_POST['Desc']);

					// move all selected posts to the new thread
					$post->SetThreadArray($newthreadid, $_POST['PostID']);

					if ($post->Find('Thread=' . $newthreadid . ' AND AttachFile!=""', '', '1'))
					{
						// Add attachment marker if one post has an attachment
						$newthread->SetOptions(UNB_THREAD_ATTACHMENT);
					}
					if (!$post->Find('Thread=' . $threadid . ' AND AttachFile!=""', '', '1'))
					{
						// Remove attachment marker from old thread
						$thread->SetOptions($thread->GetOptions() & ~UNB_THREAD_ATTACHMENT);
					}

					// Load the last post
					$post->Load($_POST['PostID'][sizeof($_POST['PostID']) - 1]);
					$newthread->SetLastPostDate($post->GetDate());

					// Load the last post of the old thread and update LastPostDate
					$post->Find('Thread=' . $threadid, 'Date DESC', '1');
					$thread->SetLastPostDate($post->GetDate());

					// copy all ThreadWatchs to new thread (user read + notifications)
					// TODO: database abstraction [#163]: use database class methods!
					$pre = $UNB['Db']->tblprefix;
					$UNB['Db']->Exec("INSERT INTO {$pre}ThreadWatch " .
						"SELECT $newthreadid AS Thread, User, Mode, LastRead, LastNotify, LastViewed " .
						"FROM {$pre}ThreadWatch " .
						"WHERE Thread=$threadid");

					// forward to the newly created thread
					UnbAddLog('split_thread ' . $threadid . ' to ' . $newthreadid);
					UnbForwardHTML(UnbLink('@this', 'id=' . $newthreadid));
				}
				else
				{
					UnbErrorLog("error creating new thread for splitting of $threadid");
				}
			}
		}
	}
}

// ---------- Close current thread ----------
if (isset($_GET['close']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread($threadid);
	if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()))
	{
		$thread->SetOptions(($thread->GetOptions() & ~UNB_THREAD_CLOSED) | ($_GET['close'] == 1 ? 1 : 0) * UNB_THREAD_CLOSED);
		if ($_GET['close'] == 1)
			UnbAddLog('close_thread ' . $threadid);
		else
			UnbAddLog('unclose_thread ' . $threadid);
		UnbForwardHTML(UnbLink('@this', 'id=' . $threadid . '&page=' . $page));
	}
	unset($thread);
}

// ---------- Add a vote ----------
if ($_POST['action'] == 'vote' &&
    $_POST['Vote'] > 0 &&
    /*$_REQUEST['Vote'] > 0 &&
    UnbUrlCheckKey() &&*/
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread;
	$thread->AddVote($threadid, $_REQUEST['Vote']);

	UnbAddLog('add_vote ' . $_REQUEST['Vote'] . ' for ' . $threadid);
	UnbForwardHTML(UnbLink('@this', 'id=' . $threadid . '&page=' . $page));
}

// ---------- Take user's vote for this thread back (if possible) ----------
if ($_GET['unvote'] == 1 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread;
	$thread->RemoveVote($threadid);

	UnbAddLog('remove_vote for ' . $threadid);
	UnbForwardHTML(UnbLink('@this', 'id=' . $threadid . '&page=' . $page));
}

// ---------- Download an attached file ----------
if ($_GET['download'] > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$post = new IPost;
	if (!$post->Load($_GET['download'])) die($UNB_T['error.invalid post id']);

	$thread = new IThread;
	if (!$thread->Load($post->GetThread())) die($UNB_T['error.invalid thread']);
	if (!UnbCheckRights('downloadattach', $thread->GetForum(), $thread->GetID()))
	{
		UnbRequireTxt('post');
		die($UNB_T['post.error.attach.no permission']);
	}

	if (!file_exists($UNB['AttachPath'] . $post->GetAttachFile())) die($UNB_T['error.file not found']);
	UnbAddLog('download_post_attach ' . $post->GetID() . ' (' . $post->GetAttachFileName() . ')');

	$post->IncDLCount();

	$type = ($_GET['inline']) ? 'Inline' : 'Attachment';
	header('Content-Type: ' . UnbGetMimetype($post->GetAttachFileName()));
	header('Content-Disposition: ' . $type . '; filename="' . $post->GetAttachFileName() . '"');

	readfile($UNB['AttachPath'] . $post->GetAttachFile());
	exit();
}

// The following 3 functions are the same as in main.inc.php!

// ---------- Mark an announcement read ----------
if ($_GET['announceread'] > 0 &&
    $UNB['LoginUserID'] > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce;
	if ($announce->Load($_GET['announceread']))
	{
		$announce->SetRead();
	}
	UnbAddLog('announce_read ' . $_GET['announceread']);
	#UnbForwardHTML(UnbLink('@this', "id=$toplevel"));
}

// ---------- Mark an announcement unread ----------
if ($_GET['announceunread'] > 0 &&
    $UNB['LoginUserID'] > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce;
	if ($announce->Load($_GET['announceunread']))
	{
		$announce->SetRead(false);
	}
	UnbAddLog('announce_unread ' . $_GET['announceunread']);
	#UnbForwardHTML(UnbLink('@this', "id=$toplevel"));
}

// ---------- Mark an announcement unread for all users ----------
if ($_GET['announceallunread'] > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce;
	$announce->Load($_GET['announceallunread']);

	if (UnbCheckRights('editannounce', $announce->GetForum()))
	{
		$announce->RemoveAllReads($_GET['announceallunread']);

		UnbAddLog('announce_all_unread ' . $_GET['announceallunread']);
		#UnbForwardHTML(UnbLink('@this', "id=$toplevel"));
	}
}

// ---------- Ignore threads ----------
if ($UNB['LoginUserID'] &&
    isset($_GET['ignorethread']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetThreadFlag(intval($_GET['ignorethread']), UNB_UFF_IGNORE, true);
	UnbAddLog('ignore_thread ' . $_GET['ignorethread']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}
if ($UNB['LoginUserID'] &&
    isset($_GET['unignorethread']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetThreadFlag(intval($_GET['unignorethread']), UNB_UFF_IGNORE, false);
	UnbAddLog('unignore_thread ' . $_GET['unignorethread']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}

// ---------- Unignore thread for all users ----------
if ($UNB['LoginUserID'] &&
    $_GET['unignorethreadallusers'] &&
    UnbUrlCheckKey() &&
    UnbCheckRights('is_admin'))
{
	$UNB['Db']->ChangeRecord(
		/*fields*/ array(
			'Flags' => array(1, 'Flags & ~' . UNB_UFF_IGNORE)),
		/*where*/ 'Flags & ' . UNB_UFF_IGNORE . ' AND Thread = ' . $threadid,
		/*table*/ 'UserForumFlags');
	UnbAddLog('unignore_thread_for_all_users ' . $threadid);
}

// ---------- Hide threads ----------
if ($UNB['LoginUserID'] &&
    isset($_GET['hidethread']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetThreadFlag(intval($_GET['hidethread']), UNB_UFF_HIDE, true);
	UnbAddLog('hide_thread ' . $_GET['hidethread']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}
if ($UNB['LoginUserID'] &&
    isset($_GET['unhidethread']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetThreadFlag(intval($_GET['unhidethread']), UNB_UFF_HIDE, false);
	UnbAddLog('unhide_thread ' . $_GET['unhidethread']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}

$thread = new IThread;

$TP =& $UNB['TP'];

// If user doesn't have access to this forum (or it doesn't exist) STOP here
$ok = true;
if (!$thread->Load($threadid)) $ok = false;
if (!UnbCheckRights('viewforum', $thread->GetForum(), $threadid)) $ok = false;
if ($thread->IsMoved()) $ok = false;

if (!$ok)
{
	$TP['errorMsg'] .= $UNB_T['error.invalid thread'] . '<br />';
	UnbBeginHTML($UNB_T['thread view']);
	UnbEndHTML();
	UteShowAll();
	exit();
}

// -------------------- BEGIN page --------------------

if ($thread->GetForum() > 0)
	UnbSetRelLink('alternate', UnbLink('@rss', 'type=1&forum=' . $thread->GetForum()), 'All threads of this forum RSS feed', 'application/rss+xml');

UnbBeginHTML($thread->GetSubject());

$TP['errorMsg'] .= $error;

$TP['path'] = UnbShowPath($thread->GetForum(), $thread->GetSubject(), $thread->GetDesc(), $threadid, true, $thread->IsImportant(), $thread->IsWatched() & UNB_NOTIFY_BOOKMARK);
if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity($thread->GetForum());
else UnbSetGuestLastForum($thread->GetForum());

if (!$splitmode)
{
	if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_HIDE))
	{
		$TP['warnHiddenThread'] = true;
	}

	UnbListAnnounces($thread->GetForum(), $threadid, $page);

	UnbAddLog('view_thread ' . $threadid . ' page ' . $page . ($_GET['nocount'] == 1 ? ' nocount' : '') . ' (' . $thread->GetSubject() . ')');
	UnbListPosts($threadid, $page);

	if (rc('show_goto_forum'))
		$TP['jumpForumBox'] = UnbJumpForumBox($thread->GetForum());
	else
		$TP['jumpForumBox'] = UnbShowPath($thread->GetForum(), $thread->GetSubject(), $thread->GetDesc(), $threadid, false, $thread->IsImportant(), $thread->IsWatched() & UNB_NOTIFY_BOOKMARK);
	if (rc('show_search_forum')) $TP['searchForumTextbox'] = UnbSearchTextbox(-$threadid);
}
else   // $splitmode == true
{
	// List posts of this thread in a shortened way to select them for splitting
	$post = new IPost;
	$user = new IUser;

	$newThreadSubject = '[' . $thread->GetSubject() . ']';
	$displayLimitLength = 400;

	$TP['threadSplitFormLink'] = UnbLink('@this', null, true);
	$TP['threadSplitThreadId'] = $threadid;
	$TP['threadSplitForumId'] = $thread->GetForum();
	$TP['threadSplitDisplayLimitLength'] = $displayLimitLength;
	$TP['threadSplitSubject'] = t2i($newThreadSubject);
	$TP['threadSplitDescription'] = '';

	$output = '';
	UnbListForumsRec($thread->GetForum(), true, 0, 0, true, /*noWebLinks*/ true);
	$TP['threadSplitForums'] = $output;

	$TP['postlist'] = array();
	$count = 0;
	$ABBC['Config']['auto_close_tags'] = true;
	$abbc_backup = $ABBC['Config']['subsets'];
	$ABBC['Config']['subsets'] &= ~ABBC_LIST;   // Work-around to make old auto-lists code XHTML compatible
	// TODO: remove this with the new auto-lists ABBC code

	if ($post->Find('Thread=' . $threadid, 'Date')) do
	{
		$tpitem = array();

		$tpitem['num'] = $count + 1;
		$tpitem['date'] = UnbFriendlyDate($post->GetDate(), 1, 3);
		$userid = $post->GetUser();
		if ($user->Load($userid))
		{
			$tpitem['user'] = t2h($user->GetName());
			$tpitem['status'] = UnbGetUserStatusText($userid, ' <small>(%s)</small>', true, false);
		}
		else
			$tpitem['user'] = t2h($post->GetUserName());
		$tpitem['postlink'] = UnbMakePostLink($post, 0, 2);
		$tpitem['postid'] = $post->GetID();
		$tpitem['subject'] = t2h($post->GetSubject());
		$tpitem['body'] = AbbcProc(str_limit($post->GetMsg(), $displayLimitLength));

		$found_next = $post->FindNext();
		$count++;
		$TP['postlist'][] = $tpitem;
	}
	while ($found_next);

	$ABBC['Config']['auto_close_tags'] = false;
	$ABBC['Config']['subsets'] = $abbc_backup;
}

UteRemember('threadview.html', $TP);

UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
