<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// main.inc.php
// Forums and Threads list page

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/common_post.lib.php');
#if ($UNB['LoginUserID'] == 58) echo '<b>main begin ------ ' . perform_msec() . 'ms</b><br />';

// -------------------- Import request variables --------------------

$toplevel = intval($_GET['id']);
if ($toplevel < -1) $toplevel = 0;

$where = $_GET['where'];

$addparent = intval($_GET['addparent']);
if ($addparent < 0 || !isset($_GET['addparent'])) $addparent = -1;

$editforum = intval($_GET['editforum']);
if ($editforum <= 0) $editforum = -1;

$editthread = intval($_GET['editthread']);
if ($editthread < 0) $editthread = 0;

$page = intval($_GET['page']);
if ($page < 1) $page = 1;

$showhidden_f = (intval($_GET['showhidden_f']) != 0);
$showhidden_t = (intval($_GET['showhidden_t']) != 0);

// -------------------- Guests' language selection --------------------

if (isset($_GET['set_lang']))
{
	if ($_GET['set_lang'] != '')
	{
		if (!rc('no_cookies')) setcookie('UnbPrefLanguage', $_GET['set_lang'], time() + 3600 * 24 * 365);
	}
	else
	{
		if (!rc('no_cookies')) setcookie('UnbPrefLanguage');
		unset($UnbPrefLanguage);
	}
	$_SESSION['UnbPrefLanguage'] = $_GET['set_lang'];
	UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// -------------------- More actions --------------------

if (isset($_GET['collapse']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$forum = new IForum;
	$forum->SetCollapsed(0, intval($_GET['collapse']));
	UnbAddLog('collapse_forum ' . $_GET['collapse']);
	#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}
if (isset($_GET['expand']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$forum = new IForum;
	$forum->SetCollapsed(1, intval($_GET['expand']));
	UnbAddLog('expand_forum ' . $_GET['expand']);
	#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// Add watch method
if (isset($_GET['watch']) &&
    $UNB['LoginUserID'] > 0 &&
    (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']) || $_GET['watch'] == UNB_NOTIFY_BOOKMARK) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$forum = new IForum;
	if ($forum->Load($toplevel))
	{
		$mode = intval($forum->IsWatched());
		$mode |= intval($_GET['watch']);
		$forum->SetWatched($mode, 0, 0);   // not recursive
	}
	UnbAddLog('watch_forum ' . $toplevel . ' mode ' . $_GET['watch']);
}

// Remove watch method
if (isset($_GET['unwatch']) &&
    $UNB['LoginUserID'] > 0 &&
    (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']) || $_GET['unwatch'] == UNB_NOTIFY_BOOKMARK) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$forum = new IForum;
	if ($forum->Load($toplevel))
	{
		$mode = intval($forum->IsWatched());
		$mode = $mode & ~intval($_GET['unwatch']);
		$forum->SetWatched($mode, 0, 0);   // not recursive
	}
	UnbAddLog('unwatch_forum ' . $toplevel . ' mode ' . $_GET['unwatch']);
	if (intval($_GET['b2p']) > 0)   // go Back2Profile...
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2p']) . '&cat=watched'));
	}
}

// Mark a thread as unread from a specified post on
if ($_GET['threadunread'] > 0 &&
    isset($_GET['threadunread_time']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	#if (UnbCheckRights('viewforum', /*forum*/ 0, /*thread*/ 0))
	{
		$thread = new IThread;
		$thread->SetLastRead(intval($_GET['threadunread_time']), intval($_GET['threadunread']), /*force*/ true);

		UnbAddLog('set_last_read ' . $_GET['threadunread']);
		#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
	}
}

// Ignore forums
if ($UNB['LoginUserID'] &&
    isset($_GET['ignoreforum']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetForumFlag(intval($_GET['ignoreforum']), UNB_UFF_IGNORE, true);
	UnbAddLog('ignore_forum ' . $_GET['ignoreforum']);
	// TODO: pass such back-links in the server session instead of the URL
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}
if ($UNB['LoginUserID'] &&
    isset($_GET['unignoreforum']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetForumFlag(intval($_GET['unignoreforum']), UNB_UFF_IGNORE, false);
	UnbAddLog('unignore_forum ' . $_GET['unignoreforum']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}

// Hide forums
if ($UNB['LoginUserID'] &&
    isset($_GET['hideforum']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetForumFlag(intval($_GET['hideforum']), UNB_UFF_HIDE, true);
	UnbAddLog('hide_forum ' . $_GET['hideforum']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}
if ($UNB['LoginUserID'] &&
    isset($_GET['unhideforum']) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$UNB['LoginUser']->SetForumFlag(intval($_GET['unhideforum']), UNB_UFF_HIDE, false);
	UnbAddLog('unhide_forum ' . $_GET['unhideforum']);
	if (intval($_GET['b2cp']) > 0)   // go back to control panel
	{
		UnbForwardHTML(UnbLink('@cp', 'id=' . intval($_GET['b2cp']) . '&cat=watched'));
	}
}

$error = false;
$info = false;

// -------------------- Add a forum --------------------

if ($_POST['action'] == 'addforum' &&
    UnbCheckRights('addforum', $_POST['Parent']) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$sort = trim($_POST['Sort']);
	$name = trim($_POST['Name']);
	$desc = trim($_POST['Description']);
	$link = trim($_POST['Link']);

	$forum = new IForum;
	if ($sort == '' || !is_numeric($sort))
	{
		$sort = $UNB['Db']->FastQuery1st('Forums', 'MAX(`Sort`)', 'Parent=' . $_POST['Parent']) + 1;
	}

	// check data
	if ($name == '')
	{
		$error .= $UNB_T['error.no name given'] . '<br />';
	}

	if (!$error)
	{
		$flags = 0;
		if ($_POST['IsCategory']) $flags += 1;
		if (!$_POST['IsCategory'] && $_POST['IsLink']) $flags += 2;

		if (!$forum->Add($sort, $_POST['Parent'], $name, $flags, $desc, $link))
		{
			UnbAddLog('add_forum error');
			$error .= $UNB_T['error.forum not created'] . ' (' . t2h($forum->db->LastError()) . ')<br />';
		}
		else
		{
			UnbAddLog('add_forum ' . $forum->GetID() . ' ok');
			UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));   // $toplevel was: $_POST['Parent']
		}
	}
	unset($forum);
}

// -------------------- Edit a forum --------------------

if ($_POST['action'] == 'editforum' &&
    UnbCheckRights('editforum', $_POST['ID']) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$forum = new IForum(intval($_POST['ID']));
	$editforum = intval($_POST['ID']);

	if ($_POST['Remove'] == 1)
	{
		// First check whether there are posts in that forum and whether we're allowed to
		// remove them, too!
		$thread = new IThread;
		if ($thread->Find($editforum)) do
		{
			if (!UnbCheckRights('removepost', $editforum, $thread->GetID()))
			{
				$error .= $UNB_T['error.thread not deleted'] . ' (' . $UNB_T['error.access denied'] . ')' . '<br />';
				break;
			}
		}
		while ($thread->FindNext());

		if (!$error)
		{
			// recursively delete the forum with any related contents
			// (threads, posts, polls, category states, votes, notifications)
			if (!$forum->Remove($_POST['ID']))
			{
				UnbAddLog('remove_forum ' . $_POST['ID'] . ' error');
				$error .= $UNB_T['error.forum not deleted'] . ' (' . t2h($forum->db->LastError()) . ')<br />';
			}
			else
			{
				UnbAddLog('remove_forum ' . $_POST['ID'] . ' ok');
				UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
			}
		}
	}
	else
	{
		$name = trim($_POST['Name']);
		$desc = trim($_POST['Description']);
		$link = trim($_POST['Link']);

		// check data
		if ($name == '')
		{
			$error .= $UNB_T['error.no name given'] . '<br />';
		}
		if (!$_POST['IsCategory'] && $_POST['IsLink'] && $link == '')
		{
			$error .= $UNB_T['error.no link given'] . '<br />';
		}

		// check for loops
		$forum2 = new IForum($_POST['Parent']);
		while (true)
		{
			if ($forum2->GetID() == $_POST['ID'])
			{
				$error .= $UNB_T['error.cannot move forum into itself'] . '<br />';
				break;
			}
			if ($forum2->GetParent() == 0)
			{
				break;
			}
			$forum2 = new IForum($forum2->GetParent());
		}

		if (!$error)
		{
			$flags = 0;
			if ($_POST['IsCategory']) $flags += 1;
			if (!$_POST['IsCategory'] && $_POST['IsLink']) $flags += 2;

			if (!$forum->Change($_POST['ID'], $_POST['Sort'], $_POST['Parent'], $name, $flags, $desc, $link))
			{
				UnbAddLog('edit_forum ' . $_POST['ID'] . ' error');
				$error .= $UNB_T['error.forum not changed'] . ' (' . t2h($forum->db->LastError()) . ')<br />';
			}
			else
			{
				UnbAddLog('edit_forum ' . $_POST['ID'] . ' ok');
				UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
			}
		}
	}

	unset($forum);
}

// -------------------- Edit a thread --------------------

if ($_POST['action'] == 'editthread' &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$thread = new IThread($_POST['ID']);
	if (isset($_POST['Forum']))
		$forumid = trim($_POST['Forum']);
	else
		$forumid = $thread->GetForum();

	if (UnbCheckRights('editpost', $forumid, $thread->GetID(), 0))
	{
		if ($_POST['Remove'] == 1)
		{
			if (!$thread->RemoveAllPosts($_POST['ID']))
			{
				UnbAddLog('remove_thread ' . $_POST['ID'] . ' error');
				$error .= $UNB_T['error.thread not deleted'] . ' (' . t2h($thread->db->LastError()) . ')<br />';
			}
			else
			{
				UnbAddLog('remove_thread ' . $_POST['ID'] . ' ok');
				UnbCallHook('thread.removed', $_POST['ID']);
				UnbForwardHTML(UnbLink('@this', 'id=' . $forumid));
			}
		}
		else
		{
			$subject = trim($_POST['Subject']);
			$desc = trim($_POST['Desc']);

			// check data
			if ($subject == '')
			{
				$error .= $UNB_T['error.no subject given'] . '<br />';
			}

			$forum = new IForum;
			// TODO: allow root threads here
			if ($forumid < 1 || !$forum->Load($forumid) || $forum->IsLink())
			{
				$error .= $UNB_T['error.invalid forum id'] . '<br />';
			}

			if (!$error)
			{
				$opt = $thread->GetOptions();
				if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID())) $opt = ($opt & ~UNB_THREAD_CLOSED) | ($_POST['CloseThread'] ? 1 : 0) * UNB_THREAD_CLOSED;
				if (UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID())) $opt = ($opt & ~UNB_THREAD_IMPORTANT) | ($_POST['ImportantThread'] ? 1 : 0) * UNB_THREAD_IMPORTANT;

				// Backup Forum ID
				$oldforumid = $thread->GetForum();

				$data = array(
					'thread' => &$thread,
					'threadid' => $thread->GetID());
				UnbCallHook('threadlist.handleeditfields', $data);

				// see if we want to move the thread
				if ($thread->GetForum() != $forumid && $_POST['MovedLink'] == 1)
				{
					$days = 14;
					if (rc('moved_thread_note_timeout')) $days = intval(rc('moved_thread_note_timeout'));
					// The moved note should be visible that amount of time from TODAY on, not the last
					// post date, as it is calculated in IThread.IsMovedExpired() which is used to remove
					// expired threads in UnbListThreads().
					//$days += unixtojd() - unixtojd($thread->GetLastPostDate());
					$days += round((time() - $thread->GetLastPostDate()) / 86400);

					$thread2 = new IThread;
					$thread2->Add($thread->GetForum(), $thread->GetUserName(), $subject, $opt | UNB_THREAD_MOVED, $thread->GetDate());
					$thread2->SetQuestion($thread->GetID(), $days);   // show "moved" link for 14 days (configurable time)
					$thread2->SetLastPostDate($thread->GetLastPostDate());
				}

				if (!$thread->Change(0, $forumid, -1, -1, $subject, $opt))
				{
					UnbAddLog('edit_thread ' . $thread->GetID() . ' error');
					$error .= $UNB_T['error.thread not changed'] . ' (' . t2h($thread->db->LastError()) . ')<br />';
				}
				else
				{
					$thread->SetDesc($desc);

					UnbAddLog('edit_thread ' . $thread->GetID() . ' ok');
					// Forward to old forum page, not where the thread was moved to
					UnbForwardHTML(UnbLink('@this', 'id=' . $oldforumid));
				}
			}
		}
	}

	unset($thread);
}

// -------------------- Again, more actions --------------------

// Mark all forums read
if ($_GET['allforumsread'] == 1 &&
    $UNB['LoginUserID'] &&
    UnbUrlCheckKey() &&
    $_GET['timestamp'] <= time() &&                   // timestamp must not be in the future
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$t = $_GET['timestamp'] or $t = time();
	$twa = $UNB['Db']->FastQuery1stArray('ThreadWatch', 'LastRead, Thread', 'User=' . $UNB['LoginUserID'], '', '', '', 'Thread');
	$ta = $UNB['Db']->FastQueryArray('Threads', 'Forum, ID, LastPostDate', 'NOT (Options & ' . UNB_THREAD_MOVED . ')', '', '', '', 'ID');

	$autoignore = $UNB['LoginUser']->GetFlags() & UNB_USER_AUTOIGNORE;

	foreach ($ta as $id => $thread)
	{
		if ($thread['LastPostDate'] > $twa[$id] &&             // thread's LastPostDate > user's LastRead
		    UnbCheckRights('viewforum', $thread['Forum'], $id))
		{
			// mark this thread read until the given timestamp
			if (isset($twa[$id]))
			{
				// ThreadWatch row already exists, needs to be updated
				$UNB['Db']->ChangeRecord(
					array('LastRead' => $t),
					'Thread=' . $id . ' AND User=' . $UNB['LoginUserID'],
					'ThreadWatch');
			}
			elseif ($thread['Date'] <= $t)
			{
				// ThreadWatch row doesn't exist, insert it
				#$t = 2147483647;   // set "infinite" timestamp, maximum positive int32 value, 0x7FFFFFFF, 2^31 - 1
				$UNB['Db']->AddRecord(
					array('LastRead' => $t,
						'Thread' => $id,
						'User' => $UNB['LoginUserID']),
					'ThreadWatch');

				// The user has not yet viewed this topic -> ignore it for future posts
				if ($autoignore)
					$UNB['LoginUser']->SetThreadFlag($thread['ID'], UNB_UFF_IGNORE, true);
			}
		}
	}

	UnbAddLog("all_forums_read");
	UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// Mark all threads read
if ($_GET['allthreadsread'] == 1 &&
    $UNB['LoginUserID'] &&
    UnbUrlCheckKey() &&
    $_GET['timestamp'] <= time() &&                    // timestamp must not be in the future
    UnbCheckRights('viewforum', $toplevel) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$t = $_GET['timestamp'] or $t = time();
	$twa = $UNB['Db']->FastQuery1stArray('ThreadWatch', 'LastRead, Thread', 'User=' . $UNB['LoginUserID'], '', '', '', 'Thread');
	$ta = $UNB['Db']->FastQueryArray('Threads', 'Forum, ID, LastPostDate', "Forum=$toplevel AND NOT (Options & " . UNB_THREAD_MOVED . ")", '', '', '', 'ID');

	$autoignore = $UNB['LoginUser']->GetFlags() & UNB_USER_AUTOIGNORE;

	foreach ($ta as $id => $thread)
	{
		if ($thread['LastPostDate'] > $twa[$id] && UnbCheckRights('viewforum', $thread['Forum'], $id))
		{
			// mark this thread read until the given timestamp
			if ($twa[$id] > 0)
			{
				// ThreadWatch row already exists, needs to be updated
				$UNB['Db']->ChangeRecord(
					array('LastRead' => $t),
					'Thread=' . $id . ' AND User=' . $UNB['LoginUserID'],
					'ThreadWatch');
			}
			elseif ($thread['Date'] <= $t)
			{
				// ThreadWatch row doesn't exist, insert it
				#$t = 2147483647;   // set "infinite" timestamp, maximum positive int32 value, 0x7FFFFFFF, 2^31 - 1
				$UNB['Db']->AddRecord(
					array('LastRead' => $t,
						'Thread' => $id,
						'User' => $UNB['LoginUserID']),
					'ThreadWatch');

				// The user has not yet viewed this topic -> ignore it for future posts
				if ($autoignore)
					$UNB['LoginUser']->SetThreadFlag($thread['ID'], UNB_UFF_IGNORE, true);
			}
		}
	}

	UnbAddLog('all_threads_read ' . $toplevel);
	UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// The following 3 functions are the same as in thread.inc.php!

// Mark an announcement read
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
	#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// Mark an announcement unread
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
	#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
}

// Mark an announcement unread for all users
if ($_GET['announceallunread'] > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce($_GET['announceallunread']);

	if (UnbCheckRights('editannounce', $announce->GetForum()))
	{
		$announce->RemoveAllReads($_GET['announceallunread']);

		UnbAddLog('announce_all_unread ' . $_GET['announceallunread']);
		#UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel));
	}
}

// Send a new password request link to a User
if (intval($_GET['mkpass']) > 0 &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$userid = intval($_GET['mkpass']);
	$user = new IUser;
	if (!$user->Load($userid)) die($UNB_T['error.invalid user']);

	// Generate key, see register.php
	mt_srand((double) microtime() * 1000000);
	$key = md5($userid . mt_rand(100000, 99999999) . time());
	$user->SetValidateKey($key);

	if (UnbNotifyUser($userid, 1, 'mail.mkpass1.subject', array(), 'mail.mkpass1.body',
		array(
			'{url}' => TrailingSlash(rc('home_url')) . UnbLink('@main', 'mkpass2=' . $userid . '&key=' . $key, false, /*sid*/ false)
		)))
	{
		UnbAddLog('request_new_password for ' . $userid . ' ok');
		UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel . '&info=1'));
	}
	else
	{
		UnbAddLog('request_new_password for ' . $userid . ' error');
		UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel . '&err=5'));
	}
}

// Generate a new password for a user
if (intval($_GET['mkpass2']) > 0 &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$userid = intval($_GET['mkpass2']);
	$key = $_GET['key'];
	$user = new IUser;
	if (!$user->Load($userid)) die($UNB_T['error.invalid user']);

	if ($key == $user->GetValidateKey())
	{
		// Generate new password
		mt_srand((double) microtime() * 1000000);
		$newpass = '';
		for ($n = 0; $n < 10; $n++)
		{
			$s = mt_rand(0, 61);
			if ($s <= 9) $s += 0x30;
			elseif ($s <= 35) $s += 0x41 - 10;
			elseif ($s <= 61) $s += 0x61 - 36;

			$newpass .= chr($s);
		}

		$user->SetPassword($newpass);
		$user->SetValidateKey('');

		UnbAddLog('make_new_password for ' . $userid . ' ok');

		if (UnbNotifyUser($userid, 1, 'mail.mkpass2.subject', array(), 'mail.mkpass2.body', array('{password}' => $newpass)))
		{
			UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel . '&info=2'));
		}
		else
		{
			UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel . '&err=5'));
		}
	}
	else
	{
		UnbAddLog('make_new_password for ' . $userid . ' error');
		UnbForwardHTML(UnbLink('@this', 'id=' . $toplevel . '&err=6'));
	}
}

// -------------------- Login error messages --------------------

if ($_GET['err'] == 1) $error .= $UNB_T['error.login failed'] . ': ' . $UNB_T['error.no username given'] . '<br />';
if ($_GET['err'] == 2) $error .= $UNB_T['error.login failed'] . ': ' . $UNB_T['error.unknown username'] . '<br />';
if ($_GET['err'] == 3) $error .= $UNB_T['error.login failed'] . ': ' . $UNB_T['error.wrong password'] .
	' <a href="' . UnbLink('@this', 'mkpass=' . intval($_GET['userid']) . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['login.get new password'] . '</a><br />';
if ($_GET['err'] == 5) $error .= $UNB_T['error.e-mail not sent'] . '<br />';
if ($_GET['err'] == 6) $error .= $UNB_T['error.invalid key'] . '<br />';

if ($_GET['info'] == 1) $info .= $UNB_T['mkpass.key sent'] . '<br />';
if ($_GET['info'] == 2) $info .= $UNB_T['mkpass.password sent'] . '<br />';


// Count posts per forum, threads per forum and "is forum new?" for ALL forums
//
function CountForums()
{
	global $UNB;

	$UNB['PostsPerForum'] = $UNB['Db']->FastQuery1stArray(
		/*table*/ array(
			array('', 'Posts', 'p', ''),
			array('INNER', 'Threads', 't', 't.ID = p.Thread')),
		/*fields*/ 'COUNT(*), t.Forum',
		/*where*/ '',
		/*order*/ '',
		/*limit*/ '',
		/*group*/ 't.Forum',
		/*key*/ 'Forum');

	$UNB['ThreadsPerForum'] = $UNB['Db']->FastQuery1stArray(
		/*table*/ 'Threads',
		/*fields*/ 'COUNT(*), Forum',
		/*where*/ 'NOT (Options & ' . UNB_THREAD_MOVED . ')',
		/*order*/ '',
		/*limit*/ '',
		/*group*/ 'Forum',
		/*key*/ 'Forum');

	// the $UNB['NewForums'] array is used by IForum::IsNew()
	$UNB['NewForums'] = array();
	if ($UNB['LoginUserID'])
	{
		$UNB['NewForums'] = $UNB['Db']->FastQuery1stArray(
			/*table*/ array(
				array('', 'Threads', 't', ''),
				array('LEFT', 'ThreadWatch', 'tw', 't.ID = tw.Thread AND tw.User = ' . $UNB['LoginUserID']),
				// UserForumFlags, only linked via forum ID
				array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
				// UserForumFlags, only linked via thread ID
				array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID')),
			/*fields*/ 'COUNT(*), t.Forum',
			/*where*/ '(tw.LastRead < t.LastPostDate OR tw.LastRead IS NULL) AND ' .
				'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND ' .
				// Entire forum must not be hidden
				'(uff_f.Flags IS NULL OR NOT (uff_f.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . ')) AND' .
				// Particular thread must not be hidden
				'(uff_t.Flags IS NULL OR NOT (uff_t.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . '))',
			/*order*/ '',
			/*limit*/ '',
			/*group*/ 't.Forum',
			/*key*/ 1);
	}
}

// Get number of posts in a forum
//
// Reads value from global array, so expects CountForums() to be called before
//
function GetPostsInForum($forumid)
{
	global $UNB;
	if (isset($UNB['PostsPerForum'][$forumid]))
		return $UNB['PostsPerForum'][$forumid];
	else
		return 0;
}

// Get number of threads in a forum
//
// Reads value from global array, so expects CountForums() to be called before
//
function GetThreadsInForum($forumid)
{
	global $UNB;
	if (isset($UNB['ThreadsPerForum'][$forumid]))
		return $UNB['ThreadsPerForum'][$forumid];
	else
		return 0;
}

// Get last posts of multiple forums
//
// in forumids_array = (array) List of forum IDs. Is unset, return value contains ALL forums (may be more efficient)
//
// returns (array(ID => array(ID, Date, User, UserName, Thread, Subject, ThreadDate))) last posts in the selected forums
//
function GetLastPostInForumA($forumids_array = 0)
{
	global $UNB;

	// Clean parameters
	if (is_array($forumids_array)) foreach ($forumids_array as $k => $v) $forumids_array[$k] = intval($v);

	if (is_array($forumids_array))
	{
		$forumids = join(',', $forumids_array);
	}
	else
	{
		$forumids = false;
	}

	$dates = $UNB['Db']->FastQuery1stArray('Threads', 'MAX(LastPostDate)',
		'NOT (Options & ' . UNB_THREAD_MOVED . ')' . ($forumids ? " AND Forum IN ($forumids)" : ''),
		'', '', 'Forum');
	if (!$dates) return array();   // no threads found in this timespan

	$record = $UNB['Db']->FastQuery(
		/*table*/ array(
			array('', 'Threads', 't', ''),
			array('LEFT', 'Posts', 'p', 't.ID = p.Thread AND p.Date = t.LastPostDate')),
		/*fields*/ 'p.ID AS PostID, p.Date, p.Thread, p.User, p.UserName, t.Forum, t.Subject, t.Date AS ThreadDate',
		/*where*/ 't.LastPostDate IN (' . join(', ', $dates) . ')',
		/*order*/ 'p.Date DESC');

	$arr = array();
	$user = new IUser;

	if ($record) do
	{
		if (!array_key_exists($record['Forum'], $arr))
		{
			$arr[$record['Forum']] = array('ID' => $record['PostID'],
										   'Date' => $record['Date'],
										   'User' => $record['User'],
										   'UserName' => $record['UserName'],
										   'Thread' => $record['Thread'],
										   'Subject' => $record['Subject'],
										   'ThreadDate' => $record['ThreadDate']
										   );
		}
	}
	while ($record = $UNB['Db']->GetRecord());

	$users = array();
	foreach ($arr as $entry) array_push($users, $entry['User']);

	if (sizeof($users))
	{
		UnbBuildEntireUserCache($users);   // TODO: remove with database denormalisation [#168]

		foreach ($arr as $key => $entry)
		{
			if ($user->Load($entry['User'])) $arr[$key]['UserName'] = $user->GetName();
		}
	}

	return $arr;
}

// List forums (recursive)
//
// in parent = (int) toplevel forum id for this call
// in level = (int) 0,1,2: table/recursion level, for indenting
// in addparent = (int) forum id to show Add <form> in
// in editforum = (int) forum id to show Edit <form> for
// in showhidden = (bool) include hidden forums in this list
//
function ListForums($parent = 0, $level = 0, $addparent = -1, $editforum = -1, $showhidden = false)
{
	global $forums_count, $lastposts, $output, $toplevel, $UNB, $UNB_T;
	$TP =& $UNB['TP'];
	$levelList = array();   // This list will collect all forum items on this level

	// Maximum level of forums visible in a list
	$maxVisibleLevel = 2;

	// Clean parameters
	$parent = intval($parent);
	$level = intval($level);
	$addparent = intval($addparent);
	$editforum = intval($editforum);

	if (!$level)
	{
		$TP['forumlistTopForum'] = $toplevel;
		$TP['forumlistLevel'] = 0;
		$TP['forumlistAddID'] = -1;
	}

	if ($parent > 0 && !UnbCheckRights('viewforum', $parent)) return false;

	$forum = new IForum;
	$childs = $forum->GetChildA($parent, $showhidden);
	if (!$childs && !$level && !$parent)
	{
		// check if the main tables exist
		if (!$UNB['Db']->ListTableCols('Forums') ||
		    !$UNB['Db']->ListTableCols('Threads') ||
		    !$UNB['Db']->ListTableCols('Posts') ||
		    !$UNB['Db']->ListTableCols('Users'))
		{
			// No tables found! This is not a valid UNB database
			// TODO: translate this
			$TP['forumlistErrorMsg'] = '<b>UNB error:</b> No tables were found in this database. You need to run the <a href="install.php">installation script</a> first!' . '<br />';
		}
		else
		{
			// display a warning message on the overview page, if no forums can be displayed
			$TP['forumlistErrorMsg'] = $UNB_T['head.error.no forums'] . '<br />';
		}
		if ($addparent == -1 && (!UnbCheckRights('addforum', $parent) || $parent > 0))
			return false;
	}

	$show_threads_posts = rc('count_forum_threads_posts');
	$show_lastpost = rc('display_forum_lastpost');
	$use_re = rc('display_forum_lastpost_re');

	if (!$level && ($editforum != -1 || $addparent != -1))
		$TP['forumlistEditformLink'] = UnbLink('@this', 'id=' . $toplevel . '&i=1', true);

	$thread = new IThread;
	$post = new IPost;
	$user = new IUser;

	if (!$level)
	{
		$forums_count = 0;

		UnbBuildEntireForumCache();
		CountForums();
		if ($show_lastpost) $lastposts = GetLastPostInForumA();   // get ALL LastPosts at once, saves much time on database

		$TP['forumlistShowThreadsPosts'] = $show_threads_posts;
		$TP['forumlistShowLastpost'] = $show_lastpost;

		$TP['forumlistEditCancelLink'] = UnbLink('@this', 'id=' . $toplevel, true);
		$TP['forumlistFormSureDelete'] = UnbSureDelete();
	}

	if ($childs)
	{
		if ($level >= $maxVisibleLevel)
		{
			// We have child forums, but they are on a too deep level to be listed

			// Check visibility (access-related)
			$visible = false;
			foreach ($childs as $child)
			{
				$forum->LoadFromRecord($childs[0]);
				if (UnbCheckRights('viewforum', $forum->GetID())) $visible = true;
			}
			if ($visible)
			{
				$item = array();
				$item['type'] = 'toodeep';
				$item['id'] = -1;
				$cols = 1;
				if ($show_threads_posts) $cols += 2;
				if ($show_lastpost) $cols += 2;
				$item['cols'] = $cols;
				$out = '';
				foreach ($childs as $child)
				{
					$forum->LoadFromRecord($child);
					if (UnbCheckRights('viewforum', $forum->GetID()) /*&& !$forum->IsLink()*/)
					{
						if ($forum->IsLink())
							$href = UnbLink($forum->GetLink(), null, true);
						else
							$href = UnbLink('@this', 'id=' . $forum->GetID(), true);

						$out .= ($out ? ', ' : '') . '<a href="' . $href . '">' . t2h($forum->GetName()) . '</a>';
					}
				}
				$item['message'] = $out;
				$levelList[] = $item;
			}
		}
		else
		{
			// this is done once at level 0
			#$forumids = array();
			#foreach ($childs as $child) array_push($forumids, $child['ID']);
			#$lastposts = GetLastPostInForumA($forumids);

			$lastType = '';
			$re = '';
			foreach ($childs as $child)
			{
				$forum->LoadFromRecord($child);

				if (UnbCheckRights('viewforum', $forum->GetID()))
				{
					$p = isset($_POST['action']);   // POST form?

					$item = array();
					$item['id'] = $forum->GetID();
					$item['parentId'] = $forum->GetParent();
					$item['lastType'] = $lastType;
					$item['sort'] = $p ? intval($_POST['Sort']) : $forum->GetSort();
					$forums_count++;

					if ($forum->IsLink())
					{
						// ----- WEB LINK -----
						$item['type'] = 'link';
						$item['isLink'] = true;

						// Is this a typo? (See lines right below.) Disabling it for now...
						#if ($forum->GetID() == $editforum && UnbCheckRights('editforum', $forum->GetID()))
						#	$item['thisEdit'] = true;

						if ($forum->GetID() == $editforum && UnbCheckRights('editforum', $forum->GetID()))
						{
							$item['editThis'] = true;
							$item['threadCount'] = 0;
						}

						// icon
						$item['link'] = UnbLink($forum->GetLink(), null, true, /*sid*/ false, /*derefer*/ true);
						$item['formLink'] = $p ? t2i($_POST['Link']) : t2i($forum->GetLink());

						// name
						$item['name'] = t2h($forum->GetName());
						$item['formName'] = $p ? t2i($_POST['Name']) : t2i($forum->GetName());
						// edit link
						if (UnbCheckRights('editforum', $forum->GetID()))
							$item['editLink'] = UnbLink('@this', 'id=' . $toplevel . '&editforum=' . $forum->GetID() . '#here', true);
						// description in the second line
						$item['desc'] = AbbcProc($forum->GetDescription());
						$item['formDesc'] = $p ? t2i($_POST['Description']) : t2i($forum->GetDescription());

						if ($editforum == $forum->GetID())
						{
							$output = '';
							$sel = $p ? $_POST['Parent'] : $forum->GetParent();
							UnbListForumsRec($sel, false, 0, 0, true, /*noWebLinks*/ true);
							$item['parentForumsOptions'] = $output;
						}

						$lastType = 'link';
					}
					elseif ($forum->IsCategory())
					{
						// ----- CATEGORY -----
						$item['type'] = 'category';
						$item['forumsCount'] = $forums_count;
						$item['state'] = $forum->IsCollapsed();
						$item['isCategory'] = true;

						$img = ($item['state'] ? 'arrow_down' : 'arrow_right');
						$action = ($item['state'] ? 'collapse' : 'expand');
						$title = ($item['state'] ? $UNB_T['category.hide'] : $UNB_T['category.show']);

						if ($forum->GetID() == $editforum && UnbCheckRights('editforum', $forum->GetID()))
						{
							$item['editThis'] = true;
							$item['threadCount'] = 0;
						}

						// icon
						if ($UNB['LoginUserID'])
							$item['iconLink'] = UnbLink('@this', 'id=' . $toplevel . '&' . $action . '=' . $forum->GetID() . '&key=' . UnbUrlGetKey(), true);
						$item['iconImage'] = $UNB['Image'][$img];
						$item['iconTitle'] = $title;

						// name
						$item['link'] = UnbLink('@this', 'id=' . $forum->GetID(), true);
						$item['name'] = t2h($forum->GetName());
						$item['formName'] = $p ? t2i($_POST['Name']) : t2i($forum->GetName());
						$item['isNew'] = $forum->IsNew();
						if ($level == 0 && UnbCheckRights('addforum', $forum->GetID()))
							$item['addLink'] = UnbLink('@this', 'id=' . $toplevel . '&addparent=' . $forum->GetID() . '#here', true);
						if (UnbCheckRights('editforum', $forum->GetID()))
							$item['editLink'] = UnbLink('@this', 'id=' . $toplevel . '&editforum=' . $forum->GetID() . '#here', true);

						// ignore/hidden state
						if ($UNB['LoginUser']->GetForumFlag($forum->GetID(), UNB_UFF_HIDE))
							$item['hiddenImage'] = '<img ' . $UNB['Image']['hide'] . ' title="' . $UNB_T['forum.advanced.hiding'] . '" />';
						if ($UNB['LoginUser']->GetForumFlag($forum->GetID(), UNB_UFF_IGNORE))
							$item['ignoredImage'] = '<img ' . $UNB['Image']['ignore'] . ' title="' . $UNB_T['forum.advanced.ignoring'] . '" />';

						// description in second line
						$item['desc'] = AbbcProc($forum->GetDescription());
						$item['formDesc'] = $p ? t2i($_POST['Description']) : t2i($forum->GetDescription());

						if ($editforum == $forum->GetID())
						{
							$output = '';
							$sel = $p ? $_POST['Parent'] : $forum->GetParent();
							UnbListForumsRec($sel, false, 0, 0, true, /*noWebLinks*/ true);
							$item['parentForumsOptions'] = $output;
						}

						// child forums
						if ($item['state'] && ($level <= $maxVisibleLevel + 1) && $child['ChildCount'] > 0 ||
						    $forum->GetID() == $addparent && UnbCheckRights('addforum', $forum->GetID()))
							$item['subforums'] = ListForums($forum->GetID(), $level + 1, $addparent, $editforum, $showhidden);

						$lastType = 'cat';
					}
					else
					{
						// ----- NORMAL FORUM -----
						$item['type'] = 'forum';
						$item['forumsCount'] = $forums_count;

						$img = 'forum';
						$title = $UNB_T['forum'];
						if ($forum->IsNew())
						{
							$img .= '_new';
							$title = $UNB_T['new posts'];
						}
						$item['iconImage'] = $UNB['Image'][$img];
						$item['iconTitle'] = $title;

						// category spearator line
						if ($forums_count > 1 && !$level && $lastType == 'cat')
							$item['catSeparator'] = true;

						if ($forum->GetID() == $editforum && UnbCheckRights('editforum', $forum->GetID()))
						{
							$item['editThis'] = true;
							// This is a ragular forum that may contain threads.
							// To prevent these threads from disappearing in a category or web link,
							// we now count them and if there really are any, this forum cannot be changed
							// to be a category or web link.
							// This variable is constantly 0 if the forum is already a category/link, obviously.
							// Though it might still happen that threads are hidden in them, there must be
							// a way to uncheck these options to correct it again.
							// And this variable only needs to be counted when we're editing this forum to
							// save resources.
							$forumThreads = new IThread();
							$item['threadCount'] = $forumThreads->Count(intval($forum->GetID()));
						}

						// icon
						$item['link'] = UnbLink('@this', 'id=' . $forum->GetID(), true);

						// name
						$item['name'] = t2h($forum->GetName());
						$item['formName'] = $p ? t2i($_POST['Name']) : t2i($forum->GetName());
						// note about new posts in this forum
						$item['isNew'] = $forum->IsNew();
						// add link
						if ($level == 0 && UnbCheckRights('addforum', $forum->GetID()))
							$item['addLink'] = UnbLink('@this', 'id=' . $toplevel . '&addparent=' . $forum->GetID() . '#here', true);
						// edit link
						if (UnbCheckRights('editforum', $forum->GetID()))
							$item['editLink'] = UnbLink('@this', 'id=' . $toplevel . '&editforum=' . $forum->GetID() . '#here', true);
						// icon that informs about missing write access
						if (!UnbCheckRights('writeforum', $forum->GetID()))
							$item['readOnly'] = true;

						// ignore/hidden state
						if ($UNB['LoginUser']->GetForumFlag($forum->GetID(), UNB_UFF_HIDE))
							$item['hiddenImage'] = '<img ' . $UNB['Image']['hide'] . ' title="' . $UNB_T['forum.advanced.hiding'] . '" />';
						if ($UNB['LoginUser']->GetForumFlag($forum->GetID(), UNB_UFF_IGNORE))
							$item['ignoredImage'] = ' <img ' . $UNB['Image']['ignore'] . ' title="' . $UNB_T['forum.advanced.ignoring'] . '" />';

						// description in second line
						$item['desc'] = AbbcProc($forum->GetDescription());
						$item['formDesc'] = $p ? t2i($_POST['Description']) : t2i($forum->GetDescription());

						if ($show_threads_posts || $forum->GetID() == $editforum && UnbCheckRights('editforum', $forum->GetID()))
						{
							$item['threads'] = GetThreadsInForum($forum->GetID());
						}
						if ($show_threads_posts)
						{
							$item['posts'] = GetPostsInForum($forum->GetID());
						}

						if ($editforum == $forum->GetID())
						{
							$output = '';
							$sel = $p ? $_POST['Parent'] : $forum->GetParent();
							UnbListForumsRec($sel, false, 0, 0, true, /*noWebLinks*/ true);
							$item['parentForumsOptions'] = $output;
						}

						if ($show_lastpost)
						{
							// last post in forum
							$arr = $lastposts[$forum->GetID()];
							if ($arr)
							{
								$item['lastpostDate'] = UnbFriendlyDate($arr['Date'], 2, 3, true, 4);
								if (rc('display_thread_lastposter'))
								{
									if ($arr['User'] > 0)
										$item['lastpostAuthor'] = '<a href="' . UnbLink('@cp', 'id=' . $arr['User'], true) . '">' . t2h(str_limit($arr['UserName'], 30, true)) . '</a>';
									else
										$item['lastpostAuthor'] = t2h(str_limit($arr['UserName'], 30, true));
								}
								if ($use_re) $re = ($arr['Date'] != $arr['ThreadDate']) ? 'Re: ' : '';
								$item['lastpostSubject'] = $re . t2h(str_limit($arr['Subject'], 38, true));
								if ($arr)
									$item['lastpostLink'] = UnbMakePostLink($arr, -1, 2);
								else
									$item['lastpostLink'] = '';
							}
						}

						// list child forums
						if (($level <= $maxVisibleLevel + 1) && $child['ChildCount'] > 0 ||
						    $forum->GetID() == $addparent && UnbCheckRights('addforum', $forum->GetID()))
							$item['subforums'] = ListForums($forum->GetID(), $level + 1, $addparent, $editforum, $showhidden);

						$lastType = 'forum';

						// include threads inside this forum
						$threadsInForum = rc('threads_in_forum');
						if ($threadsInForum > 0)
						{
							UnbListThreads($forum->GetID(), 1, 0, null, /*order*/ 'LastPostDate DESC', /*limit*/ $threadsInForum);
							$item['threadlist'] = $TP['threadlist'];
						}
					}
					$levelList[] = $item;
				} // if access
			} // foreach $childs
		}
	} // if $childs

	if ($parent == $addparent && UnbCheckRights('addforum', $parent))
	{
		$TP['forumlistAddID'] = $parent;
	}

	if (!$level)
	{
		$TP['forumlistCount'] = $forums_count;

		if ($forums_count > 0)
		{
			$levelList[0]['firstitem'] = true;
			$levelList[count($levelList) - 1]['lastitem'] = true;
		}

		$new_forums = 0;
		if (!$parent && $forums_count)
		{
			if ($UNB['LoginUserID'])
			{
				$threads = $UNB['Db']->FastQuery1stArray(
					/*table*/ array(
						array('', 'Threads', 't', ''),
						// UserForumFlags, only linked via forum ID
						array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
						// UserForumFlags, only linked via thread ID
						array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID')),
					/*fields*/ 't.ID, t.Forum',
					/*where*/ 't.LastPostDate >= ' . $UNB['LoginUser']->GetLastLogout() . ' AND ' .
						'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND ' .
						// Entire forum must not be hidden or ignored
						'(uff_f.Flags IS NULL OR NOT (uff_f.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . ')) AND' .
						// Particular thread must not be hidden or ignored
						'(uff_t.Flags IS NULL OR NOT (uff_t.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . '))');

				$count = 0;
				if ($threads) foreach ($threads as $record)
				{
					if (UnbCheckRights('viewforum', $record['Forum'], $record['ID'])) $count++;
				}
				if ($count)
				{
					$TP['TopicsSinceLastLoginLink'] = UnbLink('@search', 'nodef=1&Special=new&ResultView=1', true);
					$TP['NewTopicsCount'] = $count;
					$new_forums += $count;
				}
			}

			// Find unread threads
			if ($UNB['LoginUserID'])
			{
				$threads = $UNB['Db']->FastQueryArray(
					/*table*/ array(
						array('', 'Threads', 't', ''),
						array('LEFT', 'ThreadWatch', 'tw', 't.ID = tw.Thread AND tw.User = ' . $UNB['LoginUserID']),
						// UserForumFlags, only linked via forum ID
						array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
						// UserForumFlags, only linked via thread ID
						array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID')),
					/*fields*/ 't.ID, t.Forum',
					/*where*/ '(tw.LastRead < t.LastPostDate OR tw.LastRead IS NULL) AND ' .
						'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND ' .
						// Entire forum must not be hidden or ignored
						'(uff_f.Flags IS NULL OR NOT (uff_f.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . ')) AND' .
						// Particular thread must not be hidden or ignored
						'(uff_t.Flags IS NULL OR NOT (uff_t.Flags & ' . (UNB_UFF_IGNORE | UNB_UFF_HIDE) . '))');

				$count = 0;
				if ($threads) foreach ($threads as $record)
				{
					if (UnbCheckRights('viewforum', $record['Forum'], $record['ID'])) $count++;
				}
				if ($count)
				{
					$TP['UnreadTopicsLink'] = UnbLink('@search', 'nodef=1&Special=unread&ResultView=1', true);
					$TP['UnreadTopicsCount'] = $count;
					$new_forums += $count;
				}
			}

			// Find current polls
			$threads = $UNB['Db']->FastQueryArray('Threads', 'ID, Forum',
				'(Options & ' . UNB_THREAD_POLL . ') AND Question != "" AND NOT (Options & ' . UNB_THREAD_MOVED . ') AND Date >= ' . (time() - 3600 * 24 * rc('poll_current_days')) . " AND (" . time() . " < (Date + PollTimeout * 3600) OR PollTimeout = 0)");
			$count = 0;
			if (is_array($threads)) foreach ($threads as $record)
			{
				if (UnbCheckRights('viewforum', $record['Forum'])) $count++;
			}
			if ($count)
			{
				$TP['CurrentPollsLink'] = UnbLink('@search', 'nodef=1&Special=currentpolls&ResultView=1', true);
				$TP['CurrentPollsCount'] = $count;
				#'<img ' . $UNB['Image']['votes'] . ' /> '
				#str_replace('%n', $count, UteTranslateNum('n current polls', $count))
			}

			// Find bookmarks
			/*if ($UNB['LoginUserID'])
			{
				//$count = sizeof($UNB['LoginUser']->GetThreadWatchs(0, '&128'));
				$count = $UNB['Db']->FastQuery1st('ThreadWatch', 'COUNT(*)', "User=$UNB[LoginUserID] AND Mode&128");
				if ($count)
				{
					$actions_top .= ($actions_top ? ' &nbsp; &nbsp; ' : '') .
						'<a href="' . UnbLink('@cp', 'cat=bookmarks', true) . '">' .
						'<img ' . $UNB['Image']['bookmark'] . ' /> ' .
						($count == 1 ? $UNB_T['1_bookmark'] : str_replace('%n', $count, $UNB_T['n_bookmarks'])) .
						'</a>';
				}
			}*/
		}

		if ($UNB['LoginUserID'] &&
		    ($forums_count || $toplevel))   // would exit below
		{
			$TP['forumlistActionAdvanced'] = true;
		}
		if ($UNB['LoginUserID'] &&
		    $new_forums &&
		    $forums_count)
		{
			$TP['forumlistActionMarkReadLink'] = UnbLink('@this', 'id=' . $parent . '&allforumsread=1&timestamp=' . time() . '&key=' . UnbUrlGetKey(), true);
		}
		if (UnbCheckRights('addforum', $toplevel))
		{
			$TP['forumlistActionAddForumLink'] = UnbLink('@this', 'id=' . $toplevel . '&addparent=' . $toplevel . '#here', true);
		}

		if (!$forums_count && !$toplevel)
			return false;

		// advanced options area
		if ($TP['forumlistActionAdvanced'])
		{
			$TP['forumlistActionAdvanced'] = array();
			if ($toplevel > 0)
			{
				$forum = new IForum($toplevel);
				if (!$forum->IsCategory())
				{
					if ($UNB['LoginUser']->GetForumFlag($toplevel, UNB_UFF_IGNORE))
					{
						$TP['forumlistActionAdvanced'][] = array(
							'link' => UnbLink('@this', 'id=' . $toplevel . '&unignoreforum=' . $toplevel . '&key=' . UnbUrlGetKey(), true),
							'title' => $UNB_T['forum.advanced.unignore'],
							'subtitle' => $UNB_T['forum.advanced.ignore~'] . ' ' . $UNB_T['forum.advanced.ignoring']);
					}
					else
					{
						$TP['forumlistActionAdvanced'][] = array(
							'link' => UnbLink('@this', 'id=' . $toplevel . '&ignoreforum=' . $toplevel . '&key=' . UnbUrlGetKey(), true),
							'title' => $UNB_T['forum.advanced.ignore'],
							'subtitle' => $UNB_T['forum.advanced.ignore~'] . ' ' . $UNB_T['forum.advanced.not ignoring']);
					}
				}
				if ($UNB['LoginUser']->GetForumFlag($toplevel, UNB_UFF_HIDE))
				{
					$TP['forumlistActionAdvanced'][] = array(
						'link' => UnbLink('@this', 'id=' . $toplevel . '&unhideforum=' . $toplevel . '&key=' . UnbUrlGetKey(), true),
						'title' => $UNB_T['forum.advanced.unhide'],
						'subtitle' => $UNB_T['forum.advanced.hide~'] . ' ' . $UNB_T['forum.advanced.hiding']);
				}
				else
				{
					$TP['forumlistActionAdvanced'][] = array(
						'link' => UnbLink('@this', 'id=' . $toplevel . '&hideforum=' . $toplevel . '&key=' . UnbUrlGetKey(), true),
						'title' => $UNB_T['forum.advanced.hide'],
						'subtitle' => $UNB_T['forum.advanced.hide~'] . ' ' . $UNB_T['forum.advanced.not hiding']);
				}
			}

			$TP['forumlistActionAdvanced'][] = array(
				'link' => UnbLink('@this', array('id' => $toplevel, 'showhidden_f' => true), true),
				'title' => $UNB_T['show hidden forums'],
				'subtitle' => $UNB_T['show hidden forums~']);
		}
	}

	if ($level > 0)
	{
		return $levelList;
	}
	$TP['forumlist'] = $levelList;
	return true;
}

// List users that were online in the last 5 mins
//
// in forumid = (int) -1: list users that have been online today. no forum restriction here
//                    0: list users that have been online in the last 5 mins (configurable value)
//                    > 0: restrict the meaning of "being online" to a single forum id
//
function ShowOnlineUsers($forumid)
{
	global $UNB, $UNB_T;
	if ($forumid < -1 || !UnbCheckRights('showonlineusers')) return false;

	// Clean parameters
	$forumid = intval($forumid);

	// compare with users.inc.php:ListOnlineUsers() and user.lib.php:IUser()
	$user_online_timeout = 300;
	if (rc('user_online_timeout')) $user_online_timeout = rc('user_online_timeout');

	if ($forumid == -1)
	{
		$last_0h = getdate(UnbConvertTimezone(time()));
		$last_0h = mktime(0, 0, 0, $last_0h['mon'], $last_0h['mday'], $last_0h['year']);
		$last_0h = UnbConvertTimezone($last_0h, true);
		$where = "LastActivity > " . $last_0h;
	}
	elseif ($forumid == 0)
		$where = 'LastActivity > ' . (time() - $user_online_timeout);
	else
		$where = 'LastActivity > ' . (time() - $user_online_timeout) . ' AND LastForum = ' . $forumid;

	$user = new IUser;

	$n = 0;
	$userlist = ': ';
	if ($user->GetList($where, 'Name')) do
	{
		$userlist .= ($n++ ? ', ' : '') .
			UnbMakeUserLink($user->GetID(), $user->GetName());
	}
	while ($user->GetNext());

	if ($forumid == -1)
		$begin = str_replace('{n}', $n, UteTranslateNum('n users online today', $n));
	elseif ($forumid == 0)
		$begin = str_replace('{n}', $n, UteTranslateNum('n users online now', $n));
	else
		$begin = str_replace('{n}', $n, UteTranslateNum('n users in forum', $n));

	$guests = 0;
	if ($forumid == -1)
	{
		$guests = $UNB['Db']->FastQuery1st('Guests', 'COUNT(*)', 'LastActivity > ' . $last_0h . ' AND UserName <> \'_not_a_browser_\'');
	}
	elseif ($forumid == 0)
	{
		$guests = $UNB['Db']->FastQuery1st('Guests', 'COUNT(*)', 'LastActivity > ' . (time() - $user_online_timeout) . ' AND UserName <> \'_not_a_browser_\'');
	}
	else
	{
		$guests = $UNB['Db']->FastQuery1st('Guests', 'COUNT(*)', 'LastActivity > ' . (time() - $user_online_timeout) . ' AND LastForum = ' . $forumid . ' AND UserName <> \'_not_a_browser_\'');
	}
	if ($guests)
		$userlist .= ' ' . str_replace('{n}', $guests, UteTranslateNum('and n guests', $guests));

	return $begin . $userlist;
}

// List users' birthdays
//
function ShowBirthdays()
{
	if (!UnbCheckRights('showprofile', 0, 0, /*user*/ -1)) return false;

	global $UNB, $UNB_T;

	$user = new IUser;

	$out = '';
	$n = 0;
	if ($user->GetList('BirthDay=' . date('d') . ' AND BirthMonth=' . date('m'), 'Name')) do
	{
		if (!$n) $out .= '<img ' . $UNB['Image']['birthday'] . ' /> ' . $UNB_T['birthday users'] . ': ';
		if ($n > 0) $out .= ', ';
		$out .= UnbMakeUserLink($user->GetID(), $user->GetName());
		$x = intval(date('Y')) - $user->GetBirthYear();
		$out .= ' (' . $x . ')';
		$n++;
	}
	while ($user->GetNext());

	return $out;
}

$forum = new IForum($toplevel);

$TP =& $UNB['TP'];

// if user doesn't have access to this forum (or it doesn't exist) STOP here
if ($toplevel > 0 && !UnbCheckRights('viewforum', $toplevel) ||
    $forum->IsLink())
{
	$TP['errorMsg'] .= $UNB_T['forum.error.invalid forum'] . '<br />';
	UnbBeginHTML();
	UnbEndHTML();
	UteShowAll();
	exit();
}

UnbAddLog('view_forum ' . $toplevel . ' (' . $forum->GetName() . ')');
if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity($toplevel);
else UnbSetGuestLastForum($toplevel);

if ($error) UnbErrorLog($error);

// -------------------- Begin page --------------------

if ($toplevel == 0)
	UnbSetRelLink('alternate', UnbLink('@rss'), 'All posts RSS feed', 'application/rss+xml');
if ($toplevel > 0)
	UnbSetRelLink('alternate', UnbLink('@rss', 'type=1&forum=' . $toplevel), 'All threads of this forum RSS feed', 'application/rss+xml');

UnbBeginHTML($forum->GetName());

$TP['errorMsg'] .= $error;
$TP['infoMsg'] .= $info;

$TP['path'] = UnbShowPath($toplevel);
if (!$toplevel &&
    $UNB['LoginUserID'] &&
    $UNB['LoginUser']->GetValidateKey() != '' &&
    $UNB['LoginUser']->GetValidateKey() != '*' &&   // require manual validation
    !sizeof($UNB['LoginUserGroups']))
{
	$TP['resendValidationLink'] = UnbLink('@register', 'id=' . $UNB['LoginUserID'] . '&resend=1', true);
}

if ($where == '')
{
	if ($UNB['LoginUser']->GetForumFlag($toplevel, UNB_UFF_HIDE)) $TP['thisForumIsHidden'] = true;

	UnbListAnnounces($toplevel);

	$forums_found = ListForums($toplevel, 0, $addparent, $editforum, $showhidden_f);

	if (($toplevel > 0 || rc('allow_root_threads')) && !$forum->IsCategory())
	{
		$TP['mainShowThreadlist'] = true;
		UnbListThreads(intval($toplevel), $page, $editthread, null, '', '', true, $showhidden_t);
	}
}

// If show_goto_forum or show_search_forum is enabled and we're not on an empty overview page
if ((rc('show_goto_forum') || rc('show_search_forum')) && !(!$forums_found && !$toplevel))
{
	if (rc('show_goto_forum')) $TP['jumpForumBox'] = UnbJumpForumBox($toplevel);
	if (rc('show_search_forum')) $TP['searchForumTextbox'] = UnbSearchTextbox($toplevel);
}

if ($toplevel > 0 && rc('show_online_users'))
{
	$TP['onlineUsersThisForum'] = ShowOnlineUsers($toplevel);   // online in this forum
}
if ($toplevel == 0 && $where == '')
{
	if (rc('show_online_users'))
	{
		#echo '<br />';
		if (rc('show_birthdays')) $TP['birthdays'] = ShowBirthdays();
		$TP['onlineUsersNow'] = ShowOnlineUsers(0);   // online now
		$TP['onlineUsersToday'] = ShowOnlineUsers(-1);   // online today
	}

	if ($UNB['LoginUserID'] && rc('show_last_visit_time'))
		$TP['lastVisit'] = $UNB_T['last visit'] . ': ' . UnbFormatTime($UNB['LoginUser']->GetLastLogout(), 7);
}

// Guests' language selection
if (!$toplevel && !$UNB['LoginUserID'] && sizeof($UNB['AllLangs']) > 1)
{
	$TP['selectLanguage'] = UnbSelectLangBox();
}

$data = null;
UnbCallHook('forumlist.preforumlist', $data);
$TP['forumlistPreForumlist'] = $data;

UteRemember('main.html', $TP);

UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
