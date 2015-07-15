<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// post.inc.php
// Post editing form

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/common_post.lib.php');

UnbRequireTxt('post');
UnbRequireTxt('posteditor');

$maxpolloptions = 10;
if (rc('max_poll_options')) $maxpolloptions = intval(rc('max_poll_options'));

$min_forum = 1;
if (rc('allow_root_threads')) $min_forum = 0;

/*
echo '<small><pre>GET=';
print_r($_GET);
echo '<br />POST=';
print_r($_POST);
echo '</pre></small>';
*/

// -------------------- Import request variables --------------------

$forumid = $_REQUEST['forum'];
if (!is_numeric($forumid) || $forumid < 0) $forumid = 0;

$threadid = $_REQUEST['thread'];
if (!is_numeric($threadid) || $threadid < 0) $threadid = 0;
$threadid = intval($threadid);

$postid = $_REQUEST['id'];
if (!is_numeric($postid) || $postid < 0) $postid = 0;
$postid = intval($postid);

$replyto = $_REQUEST['replyto'];
if (!is_numeric($replyto) || $replyto < 0) $replyto = 0;
$replyto = intval($replyto);

$announceid = $_REQUEST['announce'];
if (!is_numeric($announceid) || $announceid < -1) $announceid = 0;
$announceid = intval($announceid);

$page = $_REQUEST['page'];
if (!is_numeric($page) || $page < -1 || $page == 0) $page = 1;
$page = intval($page);

// We have 2 "save" buttons
if (isset($_POST['save2']) && !isset($_POST['save'])) $_POST['save'] = $_POST['save2'];

$thread = new IThread;
if ($postid)
{
	// Post ID defined -> get thread + forum ID from it
	$post = new IPost($postid);
	$threadid = $post->GetThread();
	$thread->Load($threadid);
	$forumid = $thread->GetForum();
}
else if ($threadid)
{
	// Thread ID defined -> get forum ID from it
	$thread->Load($threadid);
	$forumid = $thread->GetForum();
}
else if ($forumid < $min_forum && !$announceid)
{
	// No forum ID defined -> we have nothing
	die('Invalid parameters.');
}
if ($announceid && !$forumid)
{
	$threadid = 0;
	$announce = new IAnnounce($announceid);
	$forumid = $announce->GetForum();
}

// Check if a post attachment file has correct filetype
//
// in filename = (string) filename
//
function is_valid_attach_name($filename)
{
	if (!rc('attach_exts')) return true;   // if no limitation is defined, accept all names

	$x = strrpos($filename, '.');
	if ($x === false) return false;   // filename needs to contain a . for analysis and renaming
	$ext = substr($filename, $x + 1);
	if (in_array(strtolower($ext), rc('attach_exts', true))) return true;   // valid filename
	return false;   // enforce above filetypes
}

// Check if a post attachment file has correct filesize
//
// in filename = (string) filename
// in thread = (IThread) object to add attachment to (important to evaluate ACL rules correctly)
//
function is_valid_attach_size($filename, &$thread)
{
	if (filesize($filename) <= 0)
		return false;   // file is empty
	if (filesize($filename) > UnbCheckRights('maxattachsize', $thread->GetForum(), $thread->GetID()))
		return false;   // file too long
	return true;
}

// Send out ForumWatch/ThreadWatch notifications to all users who want one
//
// in forum = IForum object
// in thread = IThread object
// in port = IPost object
// in page = (int) page number the new post is on
// in username = (string) new post's username
//
// send thread watch notifications, if forum->ID = 0, send forum watch notifications else.
//
function SendWatchNotification(&$forum, &$thread, &$post, $page, $username)
{
	global $UNB;

	// Clean parameters
	$page = intval($page);

	$threadid = $thread->GetID();
	$t_subject = $thread->GetSubject();
	$t_desc = $thread->GetDesc();
	if ($t_desc != '') $t_desc = ' (' . $t_desc . ')';
	$postid = $post->GetID();

	$forum2 = new IForum($thread->GetForum());
	$forum_name = $forum2->GetName();

	if ($UNB['LoginUserID']) $poster = $UNB['LoginUserName'];
	else                     $poster = $username;

	$url = UnbMakePostLink($post, 0, 3);
	//TrailingSlash(rc('home_url')) . UnbLink('@thread', 'id=' . $threadid . ($page > 1 ? '&page=' . $page : '') . '#p' . $postid, false, /*sid*/ false);

	if (!$forum->GetID())
	{
		UnbAddLog('debug: swn: ThreadWatch processing');
		// ThreadWatch's
		$mail_users = $UNB['Db']->FastQuery1stArray(
			'ThreadWatch',
			'User',
			'Thread=' . $threadid . ' AND Mode & ' . UNB_NOTIFY_EMAIL . ' AND LastNotify<=LastRead AND User!=' . $UNB['LoginUserID']);
		$jabber_users = rc('enable_jabber') ?
			$jabber_users = $UNB['Db']->FastQuery1stArray(
				'ThreadWatch',
				'User',
				'Thread=' . $threadid . ' AND Mode & ' . UNB_NOTIFY_JABBER . ' AND LastNotify<=LastRead AND User!=' . $UNB['LoginUserID']) :
			false;
		if (!$mail_users && !$jabber_users) return true;

		if ($mail_users)
		{
			$msg_key = 'mail.threadwatchnotify.body';
			$msg_data = array(
				'{subject}' => $t_subject,
				'{poster}' => $poster,
				'{forum}' => $forum_name,
				'{url}' => $url
				);
			$subject_key = 'mail.threadwatchnotify.subject';
			$subject_data = array(
				'{subject}' => $t_subject
				);

			$start = debugGetMicrotime();
			$successfulUserIds = UnbNotifyUser($mail_users, UNB_NOTIFY_EMAIL, $subject_key, $subject_data, $msg_key, $msg_data);
			$end = debugGetMicrotime();
			UnbAddLog('email_notify for thread ' . $threadid . ' (' . count($successfulUserIds) . ' users) in ' . round(($end - $start) * 1000) . ' msec');

			foreach ($successfulUserIds as $userid)
			{
				$thread->SetLastNotify($post->GetDate(), 0, $userid);
			}
		}
		if ($jabber_users)
		{
			$msg_key = 'mail.threadwatchnotify-jabber.body';
			$msg_data = array(
				'{subject}' => $t_subject,
				'{poster}' => $poster,
				'{forum}' => $forum_name,
				'{url}' => $url
				);
			$subject_key = 'mail.threadwatchnotify-jabber.subject';
			$subject_data = array(
				'{subject}' => $t_subject
				);

			$start = debugGetMicrotime();
			$successfulUserIds = UnbNotifyUser($jabber_users, UNB_NOTIFY_JABBER, $subject_key, $subject_data, $msg_key, $msg_data);
			$end = debugGetMicrotime();
			UnbAddLog('jabber_notify for thread ' . $threadid . ' (' . count($successfulUserIds) . ' users) in ' . round(($end - $start) * 1000) . ' msec');

			foreach ($successfulUserIds as $userid)
			{
				$thread->SetLastNotify($post->GetDate(), 0, $userid);
			}
		}
	}
	else
	{
		$forumid = $forum->GetID();
		UnbAddLog('debug: swn: ForumWatch processing for forum ' . $forumid);

		// ForumWatch's
		/*$mail_users = $UNB['Db']->FastQuery1stArray('ForumWatch', 'User', "Forum=$forumid and Mode & 1 and LastNotify<=LastRead and User!=$UNB['LoginUserID']");
		$jabber_users = rc('enable_jabber') ?
			$jabber_users = $UNB['Db']->FastQuery1stArray('ForumWatch', 'User', "Forum=$forumid and Mode & 4 and LastNotify<=LastRead and User!=$UNB['LoginUserID']") :
			false;*/

		$forumList = array($forumid);
		$f = $forum;
		while (true)
		{
			$parentId = $f->GetParent();
			if ($parentId <= 0) break;
			$forumList[] = $parentId;
			$f = new IForum($parentId);
		}

		$mail_users = $UNB['Db']->FastQuery1stArray('ForumWatch', 'User', 'Forum in (' . join(',', $forumList) . ') AND Mode & ' . UNB_NOTIFY_EMAIL . ' AND User!=' . $UNB['LoginUserID']);
		$jabber_users = rc('enable_jabber') ?
			$jabber_users = $UNB['Db']->FastQuery1stArray('ForumWatch', 'User', 'Forum in (' . join(',', $forumList) . ') AND Mode & ' . UNB_NOTIFY_JABBER . ' AND User!=' . $UNB['LoginUserID']) :
			false;
		if (!$mail_users && !$jabber_users) return true;

		if ($mail_users)
		{
			$msg_key = 'mail.forumwatchnotify.body';
			$msg_data = array(
				'{subject}' => $t_subject,
				'{desc}' => $t_desc,
				'{poster}' => $poster,
				'{forum}' => $forum_name,
				'{url}' => $url
				);
			$subject_key = 'mail.forumwatchnotify.subject';
			$subject_data = array(
				'{subject}' => $t_subject,
				'{forum}' => $forum_name
				);

			$start = debugGetMicrotime();
			$successfulUserIds = UnbNotifyUser($mail_users, UNB_NOTIFY_EMAIL, $subject_key, $subject_data, $msg_key, $msg_data);
			$end = debugGetMicrotime();
			UnbAddLog('email_notify for forum ' . $forumid . ' (' . count($successfulUserIds) . ' users) in ' . round(($end - $start) * 1000) . ' msec');

			foreach ($successfulUserIds as $userid)
			{
				$forum->SetLastNotify($post->GetDate(), 0, $userid);
			}
		}
		if ($jabber_users)
		{
			$msg_key = 'mail.forumwatchnotify-jabber.body';
			$msg_data = array(
				'{subject}' => $t_subject,
				'{desc}' => $t_desc,
				'{poster}' => $poster,
				'{forum}' => $forum_name,
				'{url}' => $url
				);
			$subject_key = 'mail.forumwatchnotify-jabber.subject';
			$subject_data = array(
				'{subject}' => $t_subject,
				'{forum}' => $forum_name
				);

			$start = debugGetMicrotime();
			$successfulUserIds = UnbNotifyUser($jabber_users, UNB_NOTIFY_JABBER, $subject_key, $subject_data, $msg_key, $msg_data);
			$end = debugGetMicrotime();
			UnbAddLog('jabber_notify for forum ' . $forumid . ' (' . count($successfulUserIds) . ' users) in ' . round(($end - $start) * 1000) . ' msec');

			foreach ($successfulUserIds as $userid)
			{
				$forum->SetLastNotify($post->GetDate(), 0, $userid);
			}
		}
	}
}

// Clean text from too many smilies, repeating characters...
//
// in,out text = (string) post content
//
// returns (int) calculated spam index, the higher the more junk
//
function SpamRate(&$text)
{
	#return 0;   // disable function

	global $ABBC;

	$spamrating = 0;

	// look for character repetitions
	$text0 = $text;   // make a backup
	$text = preg_replace("/(([!\?\.]){30})\\2*/", "$1", $text);   // more than 30x ! ? .
	$text = preg_replace("/((\r\n){5})\\2*/", "$1", $text);      // more than 5x new-line

	if ($text != $text0) $spamrating = 1;

	/*
	// look for too many smilies
	$ABBC['Scan'] = array();
	AbbcProc($text);

	$imgs = $ABBC['Scan']['smile'] + $ABBC['Scan']['img'];
	$len = $ABBC['Scan']['len'];
	if ($imgs > 20) $spamrating = 1;
	if ($imgs > 3 && $len > 50) if ($imgs / $len / 0.04 > 1) $spamrating = 1;
	//echo "imgs=$imgs, len=$len, spamrating=$spamrating, arith=" . ($imgs / $len / 0.04);
	*/

	return $spamrating;
}

// Perform spam rating
$_POST['Msg0'] = $_POST['Msg'];   // make a backup

$spamrating = SpamRate($_POST['Msg']);

$IsPreview = ($_POST['preview'] != '' || $_POST['AddPoll'] != '' || $_POST['RemovePoll'] != '');

$error = false;

// -------------------- Action for post adding --------------------

if ($_POST['action'] == 'add' &&
    $IsPreview &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
    // don't use $IsPreview to not display the post when adding/removing a poll
{
	if (trim($_POST['Msg']) == '')
	{
		$error .= $UNB_T['post.error.no text'] . '<br />';
	}

	if (!rc('allow_invalid_abbc') && AbbcCheck(trim($_POST['Msg'])))
	{
		$error .= $UNB_T['post.error.abbc syntax error'] . '<br />';
		$_POST['preview'] = 'xx';   // just to show the preview
	}

	if ($_POST['Poll'] == 1 && $_POST['PollTimeout'] < 0)
	{
		$error .= $UNB_T['post.error.invalid poll timeout'] . '<br />';
	}
}

if ($_POST['action'] == 'add' &&
    //$_POST['save'] != '' &&
    !$IsPreview &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$post = new IPost;
	#$thread = new IThread;
	$forum = new IForum;
	$user = new IUser;

	if (!$UNB['LoginUserID'] && trim($_POST['UserName']) == '')
	{
		$error .= $UNB_T['post.error.guests need name'] . '<br />';
	}

	if (!$UNB['LoginUserID'] &&
	    rc('username_minlength') &&
		strlen($_POST['UserName']) < rc('username_minlength'))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{min}', rc('username_minlength'), $UNB_T['cp.error.username too short']) . '<br />';
	}
	if (!$UNB['LoginUserID'] &&
	    rc('username_maxlength') &&
		strlen($_POST['UserName']) > min(rc('username_maxlength'), 40))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{max}', rc('username_maxlength'), $UNB_T['cp.error.username too long']) . '<br />';
	}

	if (!$UNB['LoginUserID'] &&
	    rc('use_veriword') &&
	    !UnbCaptcha::CheckWord($_POST['veriword']))
	{
		$error .= $UNB_T['vericode.error.invalid key'] . '<br />';
	}

	if (!$UNB['LoginUserID'] && $user->FindByName(trim($_POST['UserName'])))
	{
		$error .= $UNB_T['error.username assigned'] . '<br />';
	}

	if (trim($_POST['Msg']) == '')
	{
		$error .= $UNB_T['post.error.no text'] . '<br />';
	}

	if (strlen(trim($_POST['Msg'])) > rc('max_post_len'))
	{
		$error .= str_replace('{max}', rc('max_post_len'), $UNB_T['post.error.too long']) . '<br />';
		$_POST['preview'] = 'xx';   // just to show the preview
	}

	if (!rc('allow_invalid_abbc') && AbbcCheck(trim($_POST['Msg'])))
	{
		$error .= $UNB_T['post.error.abbc syntax error'] . '<br />';
		$_POST['preview'] = 'xx';   // just to show the preview
	}

	if ($_POST['Poll'] == 1 && trim($_POST['Question']) == '')
	{
		$error .= $UNB_T['post.error.poll.no question'] . '<br />';
	}

	if ($_POST['Poll'] == 1 && $_POST['PollTimeout'] < 0)
	{
		$error .= $UNB_T['post.error.invalid poll timeout'] . '<br />';
	}

	$optionsCount = 0;
	for ($n = 1; $n <= $maxpolloptions; $n++)
	{
		if (trim($_POST['Poll' . $n]) != '') $optionsCount++;
	}
	if ($_POST['Poll'] == 1 && $optionsCount < 2)
	{
		$error .= $UNB_T['post.error.too few poll options'] . '<br />';
	}

	$name = basename($_FILES['AttachFile']['name']);
	if ($name != '' &&
	    (filesize($_FILES['AttachFile']['tmp_name']) > UnbCheckRights('maxattachsize', $thread->GetForum(), $threadid) ||
	     !is_valid_attach_name($name) ||
	     !is_valid_attach_size($_FILES['AttachFile']['tmp_name'], $thread)))
	{
		$error .= $UNB_T['post.error.invalid attach'] . '<br />';
	}

	if (!$error && $threadid != 0)
	{
		$newthread = false;
		$thread->Load($threadid);

		if (!UnbCheckRights('writeforum', $thread->GetForum(), $thread->GetID()))
		{
			$error .= $UNB_T['error.access denied'] . '<br />';
		}
		if (!$error && $thread->IsClosed() && !UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()))
		{
			$error .= $UNB_T['post.error.thread closed'] . '<br />';
		}
		if ($_POST['Poll'] == 1 && !UnbCheckRights('createpoll', $thread->GetForum(), $thread->GetID()))
		{
			$error .= $UNB_T['post.error.poll.not allowed'] . '<br />';
		}
	}

	// Allow plug-ins to reject the post
	$data = array(
		'error' => '',
		'subject' => $_POST['Subject'],
		'body' => $_POST['Msg'],
		'forumid' => $forumid,
		'userid' => $UNB['LoginUserID']);
	UnbCallHook('post.verifyaccept', $data);
	if ($data['error']) $error .= $data['error'];

	if (!$error && $threadid == 0)
	{
		// we first have to create a new thread
		// see if we have a subject given...
		if (!UnbCheckRights('addthread', $forumid))
		{
			$error .= $UNB_T['error.access denied'] . '<br />';
		}
		elseif (trim($_POST['Subject']) == '')
		{
			$error .= $UNB_T['post.error.no subject'] . '<br />';
		}
		elseif (rc('topic_subject_minlength') &&
		        strlen(trim($_POST['Subject'])) < rc('topic_subject_minlength'))
		{
			$error .= str_replace('{min}', rc('topic_subject_minlength'), $UNB_T['post.error.subject too short']) . '<br />';
		}
		elseif (rc('topic_subject_maxlength') &&
		        strlen(trim($_POST['Subject'])) > min(rc('topic_subject_maxlength'), 150))
		{
			$error .= str_replace('{max}', rc('topic_subject_maxlength'), $UNB_T['post.error.subject too long']) . '<br />';
		}
		else
		{
			if (!$thread->Add($forumid, trim($_POST['UserName']), trim($_POST['Subject']), $opt))
			{
				$error .= $UNB_T['post.error.thread not created'] . ' (' . t2h($thread->db->LastError()) . ')<br />';
			}
			else
			{
				$thread->SetDesc(trim($_POST['Desc']));

				UnbCallHook('post.postthreadcreate', $thread);

				$threadid = $thread->GetID();
				$newthread = true;
			}
		}
	}

	if (!$error)
	{
		// now thread exists, create post
		$opt0 = $opt = $thread->GetOptions();
		if ($_POST['SetClosed'])
		{
			if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()))
				$opt = ($opt & ~UNB_THREAD_CLOSED) | ($_POST['CloseThread'] ? 1 : 0) * UNB_THREAD_CLOSED;
		}
		if ($_POST['SetImportant'])
		{
			if (UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID()))
				$opt = ($opt & ~UNB_THREAD_IMPORTANT) | ($_POST['ImportantThread'] ? 1 : 0) * UNB_THREAD_IMPORTANT;
		}
		if ($_POST['SetPoll'])
		{
			if (UnbCheckRights('createpoll', $thread->GetForum(), $thread->GetID()) && $newthread)
				$opt = ($opt & ~UNB_THREAD_POLL) | ($_POST['Poll'] ? 1 : 0) * UNB_THREAD_POLL;
		}
		if ($opt != $opt0)
			$thread->SetOptions($opt);

		$opt = 0;
		$opt += ($_POST['NoSmileys'] ? 1 : 0) * UNB_POST_NOSMILIES;
		$opt += ($_POST['NoSpecialABBC'] ? 1 : 0) * UNB_POST_NOSPECIALABBC;

		if (!$post->Add($threadid, $replyto, trim($_POST['UserName']), trim($_POST['Subject']), ltrimln(rtrim($_POST['Msg'])), $opt, $spamrating))
		{
			UnbAddLog('add_post to ' . $threadid . ' error');
			$error .= $UNB_T['post.error.post not created'] . ' (' . t2h($post->db->LastError()) . ')<br />';
		}
		else
		{
			// copy and link the attachment file
			$name = basename($_FILES['AttachFile']['name']);
			$name2 = "post_" . $post->GetID();

			if ($name != '')
			{
				if (!file_exists($UNB['AttachPath'])) mkdir($UNB['AttachPath']);
				if (!move_uploaded_file($_FILES['AttachFile']['tmp_name'], $UNB['AttachPath'] . $name2))
				{
					$ok = false;
					$error .= $UNB_T['post.error.attach not saved'] . '<br />';
				}
				else
				{
					@chmod($UNB['AttachPath'] . $name2, 0644);   // prevent access problems by the webserver
					if (!$post->SetAttachFile($name2, $name)) $ok = false;
				}
			}

			// as we've come so far ;) we can create the poll definitions
			$timeout_hrs = intval($_POST['PollTimeout']);
			if ($_POST['PollTimeoutUnit'] == 'days') $timeout_hrs *= 24;
			if ($_POST['Poll'] == 1 && !$thread->SetQuestion($_POST['Question'], $timeout_hrs))
			{
				// oops, an error? now?!
				$error .= $UNB_T['post.error.poll not created'] . ' (0) (' . t2h($thread->db->LastError()) . ')<br />';
			}
			if (!$error && $_POST['Poll'] == 1 && $_POST['PollDef'] == 1)
			{
				for ($n = 1; $n <= $maxpolloptions; $n++)
				{
					if (trim($_POST['Poll' . $n]) != '')
					{
						if (!$thread->CreateVoteOption(0, trim($_POST['Poll' . $n]), $n))
						{
							// oops, an error? now?!
							$error .= $UNB_T['post.error.poll not created'] . ' (' . $n . ') (' . t2h($thread->db->LastError()) . ')<br />';
						}
					}
				}
			}

			if ($error)
			{
				// obviously, the poll couldn't be created. try to roll back things...
				if (!$post->Remove())
					$error .= $UNB_T['post.error.post not deleted2'] . '<br />';
			}
			else
			{
				// get the page our new post is on inside its thread
				$page = ceil($post->Count('Thread=' . $threadid) / rc('posts_per_page'));

				// now is a good time to send out notifications
				if (!$newthread)
					SendWatchNotification($forum, $thread, $post, $page, trim($_POST['UserName']));
				else
				{
					$forum->Load($forumid);
					SendWatchNotification($forum, $thread, $post, $page, trim($_POST['UserName']));
				}

				// add ThreadWatch if selected
				if (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))   // only to validated mail addresses
				{
					$mode = $thread->IsWatched();
					if ($_POST['SetNotify'])
					{
						$mode = ($mode & ~UNB_NOTIFY_EMAIL) | ($_POST['NotifyEMail'] ? 1 : 0) * UNB_NOTIFY_EMAIL;
						if (rc('enable_jabber'))
							$mode = ($mode & ~UNB_NOTIFY_JABBER) | ($_POST['NotifyJabber'] ? 1 : 0) * UNB_NOTIFY_JABBER;
					}
					else   // use default notification if the web form said nothing about it
					{
						if (($mode & UNB_NOTIFY_MASK) == 0)
						{
							$mode |= $UNB['LoginUser']->GetDefaultNotify();
						}
					}
					$thread->SetWatched($mode, $threadid);
				}

				UnbAddLog('add_post ' . $post->GetID() . ' to ' . $threadid . ' ok');
				UnbForwardHTML(
					UnbLink('@thread',
					array(
						'postid' => $post->GetID(),
						'nocount' => true)));
			}
		}

		if ($error)
		{
			// something bad happened after (creating) the thread,
			// so if we created a new thread, it's better to remove it again
			if ($newthread)
				if (!$thread->Remove())
					$error .= $UNB_T['post.error.thread not deleted2'] . '<br />';
		}
	}

	unset($post);
}

// -------------------- Action for post editing --------------------

if ($_POST['action'] == 'edit' &&
    $IsPreview &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
    // don't use $IsPreview to not display the post when adding/removing a poll
{
	if (trim($_POST['Msg']) == '')
	{
		$error .= $UNB_T['post.error.no text'] . '<br />';
	}

	if (!rc('allow_invalid_abbc') && AbbcCheck(trim($_POST['Msg'])))
	{
		$error .= $UNB_T['post.error.abbc syntax error'] . '<br />';
		$_POST['preview'] = 'xx';   // just to show the preview
	}

	if ($_POST['Poll'] == 1 && $_POST['PollTimeout'] < 0)
	{
		$error .= $UNB_T['post.error.invalid poll timeout'] . '<br />';
	}
}

if (($_POST['action'] == 'edit' &&
     //$_POST['save'] != '' ||
     !$IsPreview ||
     ($_GET['delete'] == 'yes' && UnbUrlCheckKey())) &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$post = new IPost($postid);
	$thread = new IThread($post->GetThread());

	if ($_POST['Poll'] == 1 && trim($_POST['Question']) == '')
	{
		$error .= $UNB_T['post.error.poll.no question'] . '<br />';
	}

	$optionsCount = 0;
	for ($n = 1; $n <= $maxpolloptions; $n++)
	{
		if (trim($_POST['Poll' . $n]) != '') $optionsCount++;
	}
	if ($_POST['Poll'] == 1 && $optionsCount < 2)
	{
		$error .= $UNB_T['post.error.too few poll options'] . '<br />';
	}

	if (strlen(trim($_POST['Msg'])) > rc('max_post_len'))
	{
		$error .= str_replace('{max}', rc('max_post_len'), $UNB_T['post.error.too long']) . '<br />';
	}

	if (!$error && !UnbCheckRights('editpost', $thread->GetForum(), $thread->GetID(), $post->GetUser(), $post->GetDate()))
	{
		$error .= $UNB_T['error.access denied'] . '<br />';
	}
	else
	{
		if ($_POST['Remove'] == 1 ||
		    $_GET['delete'] == 'yes' && UnbUrlCheckKey())
		{
			if (!UnbCheckRights('removepost', $thread->GetForum(), $thread->GetID(), $post->GetUser(), $post->GetDate(), $thread->GetLastPostDate() == $post->GetDate() /* isLastPost */))
			{
				UnbAddLog('edit_post ' . $postid . ' no_access');
				$error .= $UNB_T['error.access denied'] . '<br />';
			}
			else
			{
				$threadid = $thread->GetID();
				switch ($post->Remove())
				{
					case 1:  // there are remaining posts in this thread
						UnbAddLog('remove_post ' . $postid . ' ok');
						UnbForwardHTML(UnbLink('@thread', 'id=' . $post->GetThread() . '&page=' . $page . '&nocount=1'));
					case 2:  // this was the last post in this thread and the thread was removed
						UnbAddLog('remove_post ' . $postid . ' ok');
						UnbCallHook('post.removedlastpost', $threadid);
						UnbForwardHTML(UnbLink('@main', 'id=' . $thread->GetForum() . '&nocount=1'));
				}

				$error .= $UNB_T['post.error.post not deleted'] . '<br />';
			}
		}
		else   // edit, not delete
		{
			if (trim($_POST['Msg']) == '')
			{
				$error .= $UNB_T['post.error.no text'] . '<br />';
			}

			if (!$post->GetUser() &&
				rc('username_minlength') &&
				strlen($_POST['UserName']) < rc('username_minlength'))
			{
				UnbRequireTxt('controlpanel');   // for below error messages
				$error .= str_replace('{min}', rc('username_minlength'), $UNB_T['cp.error.username too short']) . '<br />';
			}
			if (!$post->GetUser() &&
				rc('username_maxlength') &&
				strlen($_POST['UserName']) > min(rc('username_maxlength'), 40))
			{
				UnbRequireTxt('controlpanel');   // for below error messages
				$error .= str_replace('{max}', rc('username_maxlength'), $UNB_T['cp.error.username too long']) . '<br />';
			}

			if (!rc('allow_invalid_abbc') && AbbcCheck(trim($_POST['Msg'])))
			{
				$error .= $UNB_T['post.error.abbc syntax error'] . '<br />';
				$_POST['preview'] = 'xx';
			}

			$name = basename($_FILES['AttachFile']['name']);
			if ($name != '' &&
				(filesize($_FILES['AttachFile']['tmp_name']) > UnbCheckRights('maxattachsize', $thread->GetForum(), $threadid) ||
				 !is_valid_attach_name($name) ||
				 !is_valid_attach_size($_FILES['AttachFile']['tmp_name'], $thread)))
			{
				$error .= $UNB_T['post.error.invalid attach'] . '<br />';
			}

			if (!$error)
			{
				$thread = new IThread($post->GetThread());

				$opt = $thread->GetOptions();
				if ($_POST['SetClosed'])
				{
					if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()))
						$opt = ($opt & ~UNB_THREAD_CLOSED) | ($_POST['CloseThread'] ? 1 : 0) * UNB_THREAD_CLOSED;
				}
				if ($_POST['SetImportant'])
				{
					if (UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID()))
						$opt = ($opt & ~UNB_THREAD_IMPORTANT) | ($_POST['ImportantThread'] ? 1 : 0) * UNB_THREAD_IMPORTANT;
				}
				$thread->SetOptions($opt);

				if ($_POST['NoEditNote'] == 1 && UnbCheckRights('noeditnote', $thread->GetForum(), $thread->GetID()))
					$edituser = 0;
				else
					$edituser = $UNB['LoginUserID'];

				if ($_POST['RemoveEditNote'] == 1 && UnbCheckRights('removeeditnote', $thread->GetForum(), $thread->GetID()))
					$edituser = -1;

				$editreason = $_POST['EditReason'];

				$opt = ($_POST['NoSmileys'] ? 1 : 0) * UNB_POST_NOSMILIES;
				$opt += ($_POST['NoSpecialABBC'] ? 1 : 0) * UNB_POST_NOSPECIALABBC;

				$prevPostSubject = $post->GetSubject();   // need it for later thread renaming

				if (!$post->Change($postid, trim($_POST['UserName']), trim($_POST['Subject']), ltrimln(rtrim($_POST['Msg'])), $opt, $edituser, $editreason, $spamrating))
				{
					UnbAddLog('edit_post ' . $postid . ' error');
					$error .= $UNB_T['post.error.post not created'] . '<br />';
				}
				else
				{
					UnbCallHook('post.postedit', $postid);

					$first_post = $UNB['Db']->FastQuery1st('Posts', 'COUNT(*)', 'Date<' . $post->GetDate() . ' AND Thread=' . $post->GetThread()) == 0;
					$only_post = $UNB['Db']->FastQuery1st('Posts', 'COUNT(*)', 'Thread=' . $post->GetThread()) <= 1;

					// Change the post's user ID
					if (UnbCheckRights('is_admin') && isset($_POST['NewUser']) && is_numeric($_POST['NewUser']))
						$post->SetUser(intval($_POST['NewUser']));

					// Move this post to another thread
					$newThread = new IThread;
					$moved_post = false;
					if (!$only_post &&
					    isset($_POST['NewThread']) &&
					    is_numeric($_POST['NewThread']) &&
					    intval($_POST['NewThread']) > 0 &&
					    intval($_POST['NewThread']) != $thread->GetID() &&
					    $newThread->Load(intval($_POST['NewThread'])) &&
						UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()) &&
						UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID()))
					{
						$post->SetThreadArray(intval($_POST['NewThread']));

						// The old ReplyTo would be invalid, so remove it
						$post->SetReplyTo(0);

						// Update all posts' ReplyTo refrencing to this post
						$repost = new IPost;
						if ($repost->Find('ReplyTo=' . $post->GetID())) do
						{
							$repost->SetReplyTo($post->GetReplyTo());
						}
						while ($repost->FindNext());

						// Update LastPostDate values of both threads
						$thread->SetLastPostDate();
						$thread->SetLastPostDate(false, intval($_POST['NewThread']));

						// Update Attachment flags of both threads
						// (originally from post.lib.php:SetAttachFile())
						foreach (array($thread, $x = new IThread(intval($_POST['NewThread']))) as $th)
						{
							$opt = $th->GetOptions();
							if ($th->db->FastQuery1st('Posts', 'COUNT(*)',
								'Thread=' . $th->GetID() . " AND AttachFile<>''") == 0)
							{
								$opt &= ~UNB_THREAD_ATTACHMENT;
							}
							else
							{
								$opt |= UNB_THREAD_ATTACHMENT;
							}
							$th->SetOptions($opt);   // no error detection here. DB check would find them later then
						}

						$moved_post = true;
					}

					// rename thread's subject if this is the 1st post and it matches the post's previous subject
					if ($first_post &&
					    !$moved_post &&
					    $thread->GetSubject() == $prevPostSubject)
					{
						$thread->SetSubject(trim($_POST['Subject']));
						if (isset($_POST['Desc'])) $thread->SetDesc(trim($_POST['Desc']));

						if (UnbCheckRights('is_admin') && isset($_POST['NewUser']) && is_numeric($_POST['NewUser']))
							$thread->SetUser(intval($_POST['NewUser']), $_POST['UserName']);

						if ($thread->HasPoll() && UnbCheckRights('editpoll', $thread->GetForum, $thread->GetID(), 0) ||
							!$thread->HasPoll() && UnbCheckRights('createpoll', $thread->GetForum(), $thread->GetID()))
						{
							if ($_POST['Poll'] == 1)
							{
								if (trim($_POST['Question']) == '')
								{
									$error .= $UNB_T['post.error.poll.no question'] . '<br />';
								}

								if (!$error && !($thread->GetOptions() & UNB_THREAD_POLL))
								{
									if (!$thread->SetOptions($thread->GetOptions() | UNB_THREAD_POLL))
									{
										$error .= $UNB_T['post.error.poll not created'] . '<br />';
									}
								}

								$settitle = UnbCheckRights('editpoll', $thread->GetForum, $thread->GetID(), $thread->CountVotes());

								if (!$error)
								{
									$timeout_hrs = max(0, intval($_POST['PollTimeout']));   // restrict to positive integers
									if ($_POST['PollTimeoutUnit'] == 'days') $timeout_hrs *= 24;
									$settimeout = isset($_POST['PollTimeout']);
									if (!$thread->SetQuestion(trim($_POST['Question']), $timeout_hrs, $settitle, $settimeout))
									{
										$error .= $UNB_T['post.error.poll details not saved'] . '<br />';
									}
								}

								if (!$error)
								{
									for ($n = 1; $n <= $maxpolloptions; $n++)
									{
										$sort = $_POST['Sort' . $n];
										if (!$sort) $sort = $n;   // auto-assign position if none given

										if ($_POST['ID' . $n] != '')
										{
											// this is to be edited or deleted
											if (!$thread->ChangeVoteOption($_POST['ID' . $n], trim($_POST['Poll' . $n]), $sort, $settitle))
											{
												// oops, an error? now?!
												$error .= str_replace('{n}', $n, $UNB_T['post.error.poll.change reply n']) . '<br />';
											}
										}
										elseif (trim($_POST['Poll' . $n]) != '')
										{
											// no ID given yet, create new option
											if (!$thread->CreateVoteOption(0, trim($_POST['Poll' . $n]), $sort))
											{
												// oops, an error? now?!
												$error .= str_replace('{n}', $n, $UNB_T['post.error.poll.create reply n']) . '<br />';
											}
										}
									}
								}

								if ($error) UnbAddLog('edit_poll ' . $threadid . ' error');
							}
							else   // $_POST[Poll] = 0
							{
								if ($thread->HasPoll())
								{
									if (UnbCheckRights('editpoll', $thread->GetForum, $thread->GetID(), $thread->CountVotes()))
									{
										if (!$thread->SetOptions($thread->GetOptions & ~UNB_THREAD_POLL))
										{
											$error .= $UNB_T['post.error.poll not deleted'] . '<br />';
										}
										if (!$error && !$thread->RemoveAllVotes())
										{
											$error .= $UNB_T['post.error.votes not deleted'] . '<br />';
										}
									}
									else
									{
										$error .= $UNB_T['post.error.access denied delete poll'] . '<br />';
									}
								}
							}
						}
					}

					// copy and link the attachment file or remove it
					$name = basename($_FILES['AttachFile']['name']);
					if ($_POST['RemoveAttach'] == 1)
					{
						$post->SetAttachFile('');
					}
					elseif ($name != '')   // validity checks have already been passed
					{
						$name2 = 'post_' . $post->GetID();

						if (!file_exists($UNB['AttachPath'])) mkdir($UNB['AttachPath']);
						if (!move_uploaded_file($_FILES['AttachFile']['tmp_name'], $UNB['AttachPath'] . $name2))
						{
							$error .= $UNB_T['post.error.attach not saved'] . '<br />';
						}
						else
						{
							@chmod($UNB['AttachPath'] . $name2, 0644);   // prevent access problems by the webserver
							if (!$post->SetAttachFile($name2, $name)) $ok = false;
						}
					}

					if ($error) UnbErrorLog($error);   // there's no place to display an error now

					// display success
					UnbAddLog('edit_post ' . $postid . ' ok');
					UnbForwardHTML(UnbLink(
						'@thread',
						array(
							'postid' => $post->GetID(),
							'nocount' => true)));
				}
			}
		}
		unset($post);
	}
}

// -------------------- Action for announcement adding --------------------

if ($_POST['action'] == 'addannounce' &&
    //$_POST['save'] != '' &&
    !$IsPreview &&
    UnbUrlCheckKey() &&
    UnbCheckRights('editannounce', $forumid) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce;

	if (trim($_POST['Msg']) == '')
	{
		$error .= $UNB_T['post.error.no text'] . '<br />';
	}

	if (!$error)
	{
		$opt = 0;
		$opt = ($opt & ~UNB_ANN_IMPORTANT) | ($_POST['ImportantAnnounce'] ? 1 : 0) * UNB_ANN_IMPORTANT;
		$opt = ($opt & ~UNB_ANN_RECURSIVE) | ($_POST['RecursiveAnnounce'] ? 1 : 0) * UNB_ANN_RECURSIVE;
		$opt = ($opt & ~UNB_ANN_INTHREADS) | ($_POST['ShowInThreads'] ? 1 : 0) * UNB_ANN_INTHREADS;
		$opt = ($opt & ~UNB_ANN_FOR_MASK) | ($_POST['Access'] & UNB_ANN_FOR_MASK);
		if (!$announce->Add($forumid, $_POST['Subject'], ltrimln(rtrim($_POST['Msg'])), $opt))
		{
			UnbAddLog('add_announce to ' . $forumid . ' error');
			$error .= $UNB_T['post.error.announce not saved'] . ' (' . t2h($announce->db->LastError()) . ')<br />';
		}
		else
		{
			UnbAddLog('add_announce to ' . $forumid . ' ok');
			UnbForwardHTML(UnbLink('@main', 'id=' . $announce->GetForum()));
		}
	}

	unset($announce);
}

// -------------------- Action for announcement editing --------------------

if ($_POST['action'] == 'editannounce' &&
    //$_POST['save'] != '' &&
    !$IsPreview &&
    UnbUrlCheckKey() &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$announce = new IAnnounce($announceid);

	if (UnbCheckRights('editannounce', $announce->GetForum()))
	{
		if ($_POST['Remove'] == 1)
		{
			if ($announce->Remove($announceid))
			{
				UnbAddLog('remove_announce ' . $announceid . ' ok');
				UnbForwardHTML(UnbLink('@main', 'id=' . $announce->GetForum()));
			}

			UnbAddLog('remove_announce ' . $announceid . ' error');
			$error .= $UNB_T['post.error.announce not deleted'] . ' (' . t2h($announce->db->LastError()) . ')<br />';
		}
		else
		{
			if (trim($_POST['Msg']) == '')
			{
				$error .= $UNB_T['post.error.no text'] . '<br />';
			}

			if (!$error)
			{
				$opt = $announce->GetOptions();
				$opt = ($opt & ~UNB_ANN_IMPORTANT) | ($_POST['ImportantAnnounce'] ? 1 : 0) * UNB_ANN_IMPORTANT;
				$opt = ($opt & ~UNB_ANN_RECURSIVE) | ($_POST['RecursiveAnnounce'] ? 1 : 0) * UNB_ANN_RECURSIVE;
				$opt = ($opt & ~UNB_ANN_INTHREADS) | ($_POST['ShowInThreads'] ? 1 : 0) * UNB_ANN_INTHREADS;
				$opt = ($opt & ~UNB_ANN_FOR_MASK) | ($_POST['Access'] & UNB_ANN_FOR_MASK);
				if (!$announce->Change($announceid, -1, $_POST['Subject'], ltrimln(rtrim($_POST['Msg'])), $opt))
				{
					UnbAddLog('edit_announce ' . $announceid . ' error');
					$error .= $UNB_T['post.error.announce not saved'] . ' (' . t2h($announce->db->LastError()) . ')<br />';
				}
				else
				{
					if (UnbCheckRights('is_admin') && isset($_POST['NewUser']) && is_numeric($_POST['NewUser']))
						$announce->SetUser(intval($_POST['NewUser']));

					UnbAddLog('edit_announce ' . $announceid . ' ok');
					UnbForwardHTML(UnbLink('@main', 'id=' . $announce->GetForum()));
				}
			}
		}
	}
	unset($announce);
}

// -------------------- Post edit form --------------------

// Post edit <form>
//
// in forumid = (int) forum id to create new thread in
// in threadid = (int) thread id to create reply in
// in postid = (int) post id to edit
// in announceid = (int) announcement id to edit
// in thread = IThread object
//
function PostForm($forumid, $threadid, $postid, $announceid, &$thread)
{
	global $ABBC, $error, $IsPreview, $maxpolloptions, $page, $spamrating, $special_char_list, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$forumid = intval($forumid);
	$threadid = intval($threadid);
	$postid = intval($postid);
	$announceid = intval($announceid);

	$p = ($IsPreview || $error);   // take data from (P)revious form posting
	$m = ($announceid != 0);   // we're here to edit a (M)essage (now: announcement), no Post

	if (!$m)
		$post = new IPost;
	else
		$announce = new IAnnounce;

	if ($_REQUEST['quote'] > 0 && !$p)
		$TP['posteditorShortenQuoteNote'] = true;

	if ($UNB['LoginUserID'] == 0 && !$p)
		$TP['posteditorGuestPostingNote'] = true;

	$TP['posteditorFormLink'] = UnbLink('@this', null, true);
	$TP['posteditorFormKey'] = UnbUrlGetKey();

	// ---------- BEGIN preview ----------

	if ($_POST['preview'] != '')   // don't use $IsPreview to not display the post when adding/removing a poll
	{
		if (!$m)
		{
			// this is a POST
			if ($postid)
			{
				$post->Load($postid);
				if ($_POST['RemoveEditNote'] == 1)
				{
					$post->EditCount = 0;
				}
				elseif ($_POST['NoEditNote'] != 1)
				{
					$post->EditCount++;
					$post->EditUser = $UNB['LoginUserID'];
					$post->EditDate = time();
				}
				if ($post->GetUser() <= 0) $post->UserName = $_POST['UserName'];
				$post->Subject = $_POST['Subject'];
				$post->Msg = $_POST['Msg'];
				$post->Options = ($_POST['NoSmileys'] ? 1 : 0) * UNB_POST_NOSMILIES +
					($_POST['NoSpecialABBC'] ? 1 : 0) * UNB_POST_NOSPECIALABBC;
				if ($_POST['RemoveAttach'] == 1) $post->AttachFile = '';
				$post->SpamRating = $spamrating;
			}
			else
			{
				$post->LoadFromRecord(array(
						'ID' => 0,
						'Date' => time(),
						'User' => $UNB['LoginUserID'],
						'UserName' => $_POST['UserName'],
						'Subject' => $_POST['Subject'],
						'Msg' => $_POST['Msg'],
						'Options' => ($_POST['NoSmileys'] ? 1 : 0) * UNB_POST_NOSMILIES +
							($_POST['NoSpecialABBC'] ? 1 : 0) * UNB_POST_NOSPECIALABBC,
						'EditCount' => 0,
						'SpamRating' => $spamrating
					));
			}

			UnbCountUserPosts();

			$TP['posteditorPreviewPost'] = array();
			$tpitem = array();
			$tpitem['num'] = 1;
			UnbShowPost($tpitem, $post, $thread, 0, 0, 0, true);
			$TP['posteditorPreviewPost'][] = $tpitem;
		}
		else
		{
			// this is an ANNOUNCEMENT
			if ($announceid > 0)
			{
				// Announcement is already saved, just edit it -> we have a User
				$announce->Load($announceid);
				$announce->Subject = $_POST['Subject'];
				$announce->Msg = $_POST['Msg'];

				$opt = $announce->GetOptions();
				$opt = ($opt & ~UNB_ANN_IMPORTANT) | ($_POST['ImportantAnnounce'] ? 1 : 0) * UNB_ANN_IMPORTANT;
				$announce->Options = $opt;
			}
			else
			{
				// Announcement is about to be created -> fill in the required fields for display
				$opt = 0;
				$opt = ($opt & ~UNB_ANN_IMPORTANT) | ($_POST['ImportantAnnounce'] ? 1 : 0) * UNB_ANN_IMPORTANT;
				$opt = ($opt & ~UNB_ANN_RECURSIVE) | ($_POST['RecursiveAnnounce'] ? 1 : 0) * UNB_ANN_RECURSIVE;
				$opt = ($opt & ~UNB_ANN_INTHREADS) | ($_POST['ShowInThreads'] ? 1 : 0) * UNB_ANN_INTHREADS;
				$opt = ($opt & ~UNB_ANN_FOR_MASK) | ($_POST['Access'] & UNB_ANN_FOR_MASK);

				$announce->LoadFromRecord(array(
						'ID' => 0,
						'Forum' => $forumid,
						'Date' => time(),
						'User' => $UNB['LoginUserID'],
						'Subject' => $_POST['Subject'],
						'Msg' => $_POST['Msg'],
						'Options' => $opt
					));
			}

			$TP['posteditorPreviewAnnounce'] = array();
			$TP['posteditorPreviewAnnounce'][] = UnbShowAnnounce($announce, true);
		}

		$TP['posteditorPreviewSubmitButton'] = rc('post_preview_send_button');
	}

	// ---------- END preview ----------

	$user = new IUser;

	if ($postid > 0 && !$m)   // EDIT POSTS
	{
		$TP['posteditorMode'] = 'editpost';
		$TP['posteditorHavePost'] = true;

		$post->Load($postid);
		$TP['posteditorPostId'] = $postid;
		$TP['posteditorThreadId'] = $post->GetThread();

		$first_post = $UNB['Db']->FastQuery1st('Posts', 'COUNT(*)', 'Date<' . $post->GetDate() . ' AND Thread=' . $post->GetThread()) == 0;
		$only_post = $UNB['Db']->FastQuery1st('Posts', 'COUNT(*)', 'Thread=' . $post->GetThread()) <= 1;

		// Save current 'have a poll' status in a hidden input field and
		// update its value from previous 'Poll' or 'NoPoll' button click
		if ($p)
			$x = $_POST['Poll'] ? 1 : 0;
		else
			$x = $thread->HasPoll() ? 1 : 0;
		if ($_POST['AddPoll'] != '') $x = 1;
		if ($_POST['RemovePoll'] != '') $x = 0;
		$_POST['Poll'] = $x;
		$TP['posteditorPoll'] = $x;

		if ($post->GetUser() == 0)
		{
			$username = ($p ? $_POST['UserName'] : trim($post->GetUserName()));
			$usernameinput = '<input type="text" name="UserName" size="20"  maxlength="40" value="' . t2i($username) . '" tabindex="' . ++$GLOBALS['UnbTplTabIndex'] . '" style="width: 20em;" />';
		}

		// Change the user of this post
		if (UnbCheckRights('is_admin'))
		{
			if (!$usernameinput)
			{
				$user->Load($post->GetUser());
				$usernameinput = t2h($user->GetName());
			}
			$usernameinput .= ' - ID: <input type="text" name="NewUser" size="5" maxlength="10" value="' .
				($p ? t2i($_POST['NewUser']) : $post->GetUser()) .
				'" style="width: 5em;" />';
		}
		// Move this post to another thread
		if (!$only_post &&
		    UnbCheckRights('closethread', $forumid, $thread->GetID()) &&
		    UnbCheckRights('importantthread', $forumid, $thread->GetID()))
		{
			if (!$usernameinput)
			{
				$user->Load($post->GetUser());
				$usernameinput = t2h($user->GetName());
			}
			$usernameinput .= ' - ' . $UNB_T['thread'] . ': <input type="text" name="NewThread" size="5" maxlength="10" value="' .
				($p ? t2i($_POST['NewThread']) : $thread->GetID()) .
				'" style="width: 5em;" />';
		}

		// Editing another user's post? Show a warning then
		if ($post->GetUser() != $UNB['LoginUserID'])
		{
			$TP['posteditorWarnEditOtherUsersPost'] = true;
		}

		$subject = ($p ? trim($_POST['Subject']) : $post->GetSubject());
		$desc = ($p ? trim($_POST['Desc']) : $thread->GetDesc());
		$msg = ($p ? $_POST['Msg0'] : $post->GetMsg());
	}
	elseif (!$m)   // NEW POSTS
	{
		$TP['posteditorMode'] = 'newpost';
		$TP['posteditorHavePost'] = true;

		$TP['posteditorForumId'] = $forumid;

		$first_post = ($threadid == 0);

		// Save current 'have a poll' status in a hidden input field and
		// update its value from previous 'Poll' or 'NoPoll' button click
		if ($p)
			$x = $_POST['Poll'] ? 1 : 0;
		else
			$x = $thread->HasPoll() ? 1 : 0;
		if ($_POST['AddPoll'] != '') $x = 1;
		if ($_POST['RemovePoll'] != '') $x = 0;
		$_POST['Poll'] = $x;
		$TP['posteditorPoll'] = $x;

		if (!$UNB['LoginUserID'])
		{
			$usernameinput = '<input type="text" class="text" name="UserName" size="20" maxlength="40" value="' . t2i($_POST['UserName']) . '" tabindex="' . ++$GLOBALS['UnbTplTabIndex'] . '" style="width: 20em;" />' . endl;
		}

		$subject = ($p ? trim($_POST['Subject']) : '');
		$desc = ($p ? trim($_POST['Desc']) : '');
		$msg = ($p ? $_POST['Msg0'] : '');

		if ($_REQUEST['quote'] > 0)
		{
			$post->Load($_REQUEST['quote']);
			if ($post->GetUser() > 0)
			{
				$user->Load($post->GetUser());
				$username = $user->GetName();
			}
			else
			{
				$username = $post->GetUserName();
			}
			$TP['posteditorThreadId'] = $post->GetThread();
			$TP['posteditorQuotePostId'] = intval($_REQUEST['quote']);

			// add timestamp to quotation if it's older than x days
			if (time() - $post->GetDate() > rc('quote_with_date') * 24 * 3600)
				$msg = '[quote=' . $username . ':' . $post->GetDate() . "]\n" . $post->GetMsg() . "\n[/quote]\n\n";
			else
				$msg = '[quote=' . $username . "]\n" . $post->GetMsg() . "\n[/quote]\n\n";
		}
		else
		{
			$TP['posteditorThreadId'] = $threadid;
			$TP['posteditorQuotePostId'] = (isset($_REQUEST['replyto']) ? intval($_REQUEST['replyto']) : 0);
		}
	}
	else   // this means $m; ANNOUNCEMENTS
	{
		if ($announceid > 0)   // EDIT
		{
			$TP['posteditorMode'] = 'editannounce';
			$TP['posteditorHavePost'] = false;

			$TP['posteditorAnnounceId'] = $announceid;
			$announce->Load($announceid);
			$subject = ($p ? trim($_POST['Subject']) : $announce->GetSubject());
			$msg = ($p ? trim($_POST['Msg0']) : $announce->GetMsg());
			$newuserid = ($p ? $_POST['NewUser'] : $announce->GetUser());
		}
		else   // NEW
		{
			$TP['posteditorMode'] = 'newannounce';
			$TP['posteditorHavePost'] = false;

			$TP['posteditorForumId'] = $forumid;
			$subject = ($p ? trim($_POST['Subject']) : '');
			$msg = ($p ? trim($_POST['Msg0']) : '');
			$newuserid = ($p ? $_POST['NewUser'] : '');
		}

		if ($announceid > 0 && UnbCheckRights('is_admin'))
			$usernameinput = t2h($UNB['LoginUserName']) . ' - ID: <input type="text" class="text" name="NewUser" size="5" maxlength="10" value="' . t2i($newuserid) . '" style="width: 5em;" />';
	}

	// ----- Meta-data -----
	$TP['posteditorUsernameInput'] = $usernameinput;
	$TP['posteditorFirstPost'] = $first_post;
	$TP['posteditorSubjectOptional'] = !$first_post;
	$TP['posteditorSubjectInput'] = t2h($subject, false);
	if (!$m && $first_post)
	{
		$TP['posteditorDescriptionInput'] = t2h($desc, false);
	}
	if (!$UNB['LoginUserID'] && rc('use_veriword'))
	{
		$TP['posteditorVericodeLink'] = UnbLink('@veriword', array('prog_id' => rc('prog_id')), true);
	}

	// ----- BBCode -----
	$TP['posteditorFormattingHelpLink'] = t2i('javascript:UnbPopup("' . UnbLink('http://newsboard.unclassified.de/docs/usage/textformatting', null, true) . '", 760, 550);');

	// ----- Smilies -----
	if (is_array($ABBC['Smilies']))
	{
		$arr = array();
		foreach ($ABBC['Smilies'] as $index => $smilie)
		{
			if (!$smilie['hidden'])
			{
				$tpitem = array();
				$tpitem['code'] = t2h($smilie['code']);
				$tpitem['codeJs'] = t2h(str_replace('\'', '\\x27', $smilie['code']), false);
				$tpitem['image'] = AbbcRegReplaceSmilie('', $index);

				$arr[] = $tpitem;
			}
		}
		$TP['posteditorSmilies'] = $arr;
	}

	// ----- Message -----
	$TP['posteditorHeight'] = 250;
	if (strlen($msg) > 1000)
		$TP['posteditorHeight'] = 450;   // increase textarea height for long posts
	$TP['posteditorMaxLength'] = rc('max_post_len');
	$TP['posteditorMaxLengthFmt'] = format_number(rc('max_post_len'));
	$TP['posteditorMsgInput'] = t2h($msg, false);

	// ----- Submit/preview controls -----
	if (!$m && $first_post && UnbCheckRights('createpoll', $forumid, $threadid))
	{
		$TP['posteditorAllowPoll'] = true;
		$TP['posteditorHavePoll'] = ($p ? $_POST['Poll'] : $thread->HasPoll());
	}

	// ----- Options -----
	$out_options = '';
	if (!$m)
	{
		// OPTIONS FOR NEW POSTS
		if (!$postid &&
		    in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
		{
			// is user already watching this thread?
			$realmode = $mode = $thread->IsWatched($threadid) & UNB_NOTIFY_MASK;
			if (!$mode) $mode = $UNB['LoginUser']->GetDefaultNotify();

			$out_options .= '<input type="hidden" name="SetNotify" value="1" />';
			if ($UNB['LoginUser']->GetEMail() != '' ||
				($UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber')) ||
				$mode)
			{
				if ($UNB['LoginUser']->GetEMail() != '' ||
					($UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber')))
				{
					$out_options .= $UNB_T['pe.notify via'] . ' ';

					if ($UNB['LoginUser']->GetEMail() != '')
					{
						if ($p && $_POST['SetNotify'])
							$chk = ($_POST['NotifyEMail'] == 1) ? ' checked="checked"' : '';
						else
							$chk = ($mode & UNB_NOTIFY_EMAIL) ? ' checked="checked"' : '';
						$out_options .= ' <label><input type="checkbox" name="NotifyEMail" value="1"' . $chk . ' />' . $UNB_T['e-mail'] . '</label>';
					}
					if ($UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber'))
					{
						if ($p && $_POST['SetNotify'])
							$chk = ($_POST['NotifyJabber'] == 1) ? ' checked="checked"' : '';
						else
							$chk = ($mode & UNB_NOTIFY_JABBER) ? ' checked="checked"' : '';
						$out_options .= ' <label><input type="checkbox" name="NotifyJabber" value="1"' . $chk . ' />' . $UNB_T['jabber'] . '</label>';
					}
				}

				if ($realmode)
				{
					$x = false;
					if ($realmode & UNB_NOTIFY_EMAIL)
					{
						$x .= '<b>' . $UNB_T['e-mail'] . '</b>';
					}
					if ($realmode & UNB_NOTIFY_JABBER && rc('enable_jabber'))
					{
						$x .= ($x ? ', ' : '') .
							'<b>' . $UNB_T['jabber'] . '</b>';
					}
					if ($x) $out_options .= ' <small>' . str_replace('{x}', $x, $UNB_T['pe.you watch this by x']) . '</small>';
				}
				$out_options .= '<br />';
			}
		}

		// Checkbox was replaced by AddPoll and RemovePoll buttons
		/*if ($first_post && UnbCheckRights('createpoll', $forumid, $thread->GetID()))
		{
			if ($p)
				$chk = ($_POST['Poll'] == 1) ? ' checked="checked"' : '';
			else
				$chk = $thread->HasPoll() ? ' checked="checked"' : '';
			$out_options .= '<label><input type="checkbox" name="x-Poll" value="1"' . $chk . ' />' . $UNB_T['poll'] . '</label> <small>' . $UNB_T['update_with_preview'] . '</small><br />';
		}*/

		if ($p)
			$chk = ($_POST['NoSmileys'] == 1) ? ' checked="checked"' : '';
		else
			if ($postid)
				$chk = ($post->GetOptions() & UNB_POST_NOSMILIES) ? ' checked="checked"' : '';
			else
				$chk = '';
		$out_options .= '<label><input type="checkbox" name="NoSmileys" value="1"' . $chk . ' />' . $UNB_T['pe.no smilies'] . '</label> &middot; ';

		if ($p)
			$chk = ($_POST['NoSpecialABBC'] == 1) ? ' checked="checked"' : '';
		else
			if ($postid)
				$chk = ($post->GetOptions() & UNB_POST_NOSPECIALABBC) ? ' checked="checked"' : '';
			else
				$chk = '';
		$out_options .= '<label><input type="checkbox" name="NoSpecialABBC" value="1"' . $chk . ' />' . $UNB_T['pe.no special syntax'] . '</label><br />';

		if (UnbCheckRights('closethread', $forumid, $thread->GetID())
			|| UnbCheckRights('importantthread', $forumid, $thread->GetID()))
		{
			$out_options .= '<input type="hidden" name="SetImportant" value="1" />';
			$out_options .= '<input type="hidden" name="SetClosed" value="1" />';
			$out_options .= $UNB_T['pe.thread is'];

			if (UnbCheckRights('closethread', $forumid, $thread->GetID()))
			{
				if ($p && $_POST['SetClosed'])
					$chk = ($_POST['CloseThread'] == 1) ? ' checked="checked"' : '';
				else
					$chk = ($thread->GetOptions() & UNB_THREAD_CLOSED) ? ' checked="checked"' : '';
				$out_options .= ' <label><input type="checkbox" name="CloseThread" value="1"' . $chk . ' />' . $UNB_T['closed'] . '</label>';
			}

			if (UnbCheckRights('importantthread', $forumid, $thread->GetID()))
			{
				if ($p && $_POST['SetImportant'])
					$chk = ($_POST['ImportantThread'] == 1) ? ' checked="checked"' : '';
				else
					$chk = ($thread->GetOptions() & UNB_THREAD_IMPORTANT) ? ' checked="checked"' : '';
				$out_options .= ' <label><input type="checkbox" name="ImportantThread" value="1"' . $chk . ' />' . $UNB_T['important'] . '</label>';
			}

			$out_options .= '<br />';
		}
	}

	if ($m)
	{
		if ($p)
			$chk = ($_POST['ImportantAnnounce'] == 1) ? ' checked="checked"' : '';
		else
			$chk = ($announceid > 0 ? $announce->GetOptions() & UNB_ANN_IMPORTANT : 0) ? ' checked="checked"' : '';
		$out_options .= '<label><input type="checkbox" name="ImportantAnnounce" value="1"' . $chk . ' />' . $UNB_T['pe.announce.important'] . '</label><br />';

		if ($p)
			$chk = ($_POST['RecursiveAnnounce'] == 1) ? ' checked="checked"' : '';
		else
			$chk = ($announceid > 0 ? $announce->GetOptions() & UNB_ANN_RECURSIVE : 0) ? ' checked="checked"' : '';
		$out_options .= '<label><input type="checkbox" name="RecursiveAnnounce" value="1"' . $chk . ' />' . $UNB_T['pe.announce.recursive'] . '</label><br />';

		if ($p)
			$chk = ($_POST['ShowInThreads'] == 1) ? ' checked="checked"' : '';
		else
			$chk = ($announceid > 0 ? $announce->GetOptions() & UNB_ANN_INTHREADS : 0) ? ' checked="checked"' : '';
		$out_options .= '<label><input type="checkbox" name="ShowInThreads" value="1"' . $chk . ' />' . $UNB_T['pe.announce.show in threads'] . '</label><br />';

		if ($p)
			$val = $_POST['Access'];
		else
			$val = ($announceid > 0 ? $announce->GetOptAccess() : 0);
		$out_options .= $UNB_T['pe.announce.display for'] . ' ';
		$out_options .= '<label><input type="radio" name="Access" value="' . UNB_ANN_FOR_ALL . '"' . ($val == UNB_ANN_FOR_ALL ? ' checked="checked"' : '') . ' />' . $UNB_T['pe.announce.to.all'] . '</label> ';
		$out_options .= '<label><input type="radio" name="Access" value="' . UNB_ANN_FOR_GUESTS . '"' . ($val == UNB_ANN_FOR_GUESTS ? ' checked="checked"' : '') . ' />' . $UNB_T['pe.announce.to.guests'] . '</label> ';
		$out_options .= '<label><input type="radio" name="Access" value="' . UNB_ANN_FOR_USERS . '"' . ($val == UNB_ANN_FOR_USERS ? ' checked="checked"' : '') . ' />' . $UNB_T['pe.announce.to.members'] . '</label> ';
		$out_options .= '<label><input type="radio" name="Access" value="' . UNB_ANN_FOR_MODS . '"' . ($val == UNB_ANN_FOR_MODS ? ' checked="checked"' : '') . ' />' . $UNB_T['pe.announce.to.moderators'] . '</label><br />';
	}

	if (!$m && $postid > 0 && UnbCheckRights('removepost', $forumid, $thread->GetID(), $post->GetUser(), $post->GetDate(), $thread->GetLastPostDate() == $post->GetDate() /* isLastPost */)
		|| $m && $announceid > 0 && UnbCheckRights('editannounce', $announce->GetForum()))
	{
		if ($p)
			$chk = ($_POST['Remove'] == 1) ? ' checked="checked"' : '';
		else
			$chk = '';

		$what = ($m ? $UNB_T['pe.announce.delete'] : $UNB_T['pe.post.delete']);

		$out_options .= '<label><input type="checkbox" name="Remove" value="1"' . $chk . ' ' . UnbSureDelete() . ' />' . $what . '</label><br />';
	}

	$data = array('thread' => &$thread, 'posteditorOptions' => '', 'announcement' => &$m);
	UnbCallHook('posteditor.postoptions', $data);
	$out_options .= $data['posteditorOptions'];

	$TP['posteditorOptions'] = $out_options;

	// OPTIONS FOR POST EDITING: Edit Notes + Reason
	$out_editoptions = '';
	if (!$m)
	{
		if ($postid > 0)
		{
			if (UnbCheckRights('noeditnote', $forumid, $thread->GetID()))
			{
				if ($p)
					$chk = ($_POST['NoEditNote'] == 1) ? ' checked="checked"' : '';
				else
					$chk = '';
				$out_editoptions .= '<label><input type="checkbox" name="NoEditNote" value="1"' . $chk . ' />' . $UNB_T['pe.no edit note'] . '</label>';

				if (UnbCheckRights('removeeditnote', $forumid, $thread->GetID()))
				{
					if ($p)
						$chk = ($_POST['RemoveEditNote'] == 1) ? ' checked="checked"' : '';
					else
						$chk = '';
					$out_editoptions .= ' &middot; <label><input type="checkbox" name="RemoveEditNote" value="1"' . $chk . ' />' . $UNB_T['pe.remove edit note'] . '</label>';
				}
				$out_editoptions .= '<br />';
			}

			$reason = ($p ? $_POST['EditReason'] : $post->GetEditReason());
			$out_editoptions .= $UNB_T['post.edit reason'] . ': <input type="text" name="EditReason" value="' . t2i($reason) . '" size="20" style="width: 70%;" /><br />';
		}
	}

	$TP['posteditorEditOptions'] = $out_editoptions;

	// ----- File attachment -----
	if (!$m)
	{
		if ($postid > 0 && $post->GetAttachFile() != '')
		{
			$TP['posteditorAttachedFiles'] = array();
			$tpitem = array();

			$tpitem['name'] = t2h($post->GetAttachFileName());
			$tpitem['size'] = filesize($UNB['AttachPath'] . $post->GetAttachFile());
			$tpitem['downloads'] = $post->GetAttachDLCount();

			$TP['posteditorAttachedFiles'][] = $tpitem;
		}
		elseif (UnbCheckRights('maxattachsize', $forumid, $threadid) > 0 &&
			UnbCheckRights('downloadattach', $forumid, $threadid))
		{
			// maximum upload file size if minimum of applied ACL and PHP conf setting
			$maxsize = UnbCheckRights('maxattachsize', $forumid, $threadid);
			$phpsize = UnbUnverbSize(ini_get('upload_max_filesize'));
			$maxsize = min($phpsize, $maxsize);

			$TP['posteditorMaxAttachSize'] = $maxsize;
			$TP['posteditorMaxAttachSizeFmt'] = format_number($maxsize, 1, 1024, ' ') . 'B';

			//echo '<input type=hidden name='MAX_FILE_SIZE' value='" .
			//	UnbCheckRights('maxattachsize', $forumid, $threadid) . "' />';
			// NOTE: this directive causes PHP to throw a warning before programme execution
			//       that I couldn't prevent. moreover doesn't browser support seem to prove
			//       the need for this feature.
			// TODO: only use if PHP start-up warnings (?) are disabled
			// TODO: test again with current browsers
		}
	}

	// ----- Poll definition -----
	if ($first_post && $p && $_POST['Poll'] ||
		$first_post && !$p && $thread->HasPoll())
	{
		$settitle = !$threadid || UnbCheckRights('editpoll', $forumid, $thread->GetID(), $thread->CountVotes());
		$TP['posteditorPollSetTitle'] = $settitle;
		$TP['posteditorPollTitleInput'] = t2i($p ? trim($_POST['Question']) : $thread->GetQuestion());

		$TP['posteditorPollOptions'] = array();
		$votes = $thread->GetVotes();
		for ($n = 1; $n <= $maxpolloptions; $n++)
		{
			if ($votes[$n - 1]['Title'] == '' && !$settitle) break;   // don't show empty, non-editable lines

			$tpitem = array();
			$tpitem['num'] = $n;
			$tpitem['voteId'] = $votes[$n - 1]['ID'];
			$tpitem['textInput'] = ($p ? t2i(trim($_POST["Poll$n"])) : t2i($votes[$n - 1]['Title']));
			$tpitem['sortInput'] = ($p ? trim($_POST["Sort$n"]) : $votes[$n - 1]['Sort']);
			if ($threadid && $votes[$n - 1]['Title'] != '')
				$tpitem['voteCount'] = $votes[$n - 1]['Votes'];

			$TP['posteditorPollOptions'][] = $tpitem;
		}

		if ($p && isset($_POST['PollTimeout']))
		{
			$TP['posteditorPollDuration'] = t2i(trim($_POST['PollTimeout']));
			$TP['posteditorPollUnitDays'] = $_POST['PollTimeoutUnit'] == 'days';
			$TP['posteditorPollUnitHours'] = $_POST['PollTimeoutUnit'] == 'hours';
		}
		elseif ($thread->HasPoll() && $thread->GetPollTimeout())
		{
			$hours = $thread->GetPollTimeout();   // in hours
			if ($hours % 24 == 0)   // can express in entire days
			{
				$TP['posteditorPollDuration'] = $hours / 24;
				$TP['posteditorPollUnitDays'] = true;
			}
			else   // keep hours
			{
				$TP['posteditorPollDuration'] = $hours;
				$TP['posteditorPollUnitHours'] = true;
			}
		}
		else
		{
			$TP['posteditorPollDuration'] = 0;   // infinite
			$TP['posteditorPollUnitDays'] = true;   // pre-set unit: days
		}
	}

	// ----- Show latest posts -----
	if (!$postid && !$m)
	{
		UnbCountUserPosts();

		$TP['postlistSimple'] = true;   // Only display posts, no actions, warnings and other stuff
		$TP['postlist'] = array();
		$c = 2;   // begin with 2, there could already be a preview post with #1 above and IDs need to be unique
		if ($post->Find('Thread=' . $threadid, 'Date desc', '10')) do
		{
			$tpitem = array();
			$tpitem['num'] = $c++;
			UnbShowPost($tpitem, $post, $thread, $c % 2, 1, false);
			$TP['postlist'][] = $tpitem;
		}
		while ($post->FindNext());
	}
}

if ($postid > 0 && !$announceid)
	$pageTitle = $UNB_T['pe.edit post'];
elseif (!$announceid)
	$pageTitle = $UNB_T['pe.compose post'];
else
	if ($announceid > 0)
		$pageTitle = $UNB_T['pe.edit announce'];
	else
		$pageTitle = $UNB_T['pe.compose announce'];

$TP =& $UNB['TP'];

// Check read access to the selected forum/thread
if (!$announceid &&
    !UnbCheckRights('viewforum', $forumid, $threadid))
{
	UnbBeginHTML($pageTitle);
	$error .= $UNB_T['error.access denied'] . '<br />';
	$TP['errorMsg'] .= $error;
	$TP['headNoIndex'] = true;
	UnbErrorLog($error);
	UnbEndHTML();
	UteShowAll();
	exit();
}

if ($error)
{
	UnbErrorLog($error);
}

// -------------------- BEGIN page --------------------

UnbBeginHTML($pageTitle);

$TP['errorMsg'] .= $error;
$TP['headNoIndex'] = true;

if ($announceid || $forumid > 0 && !$threadid)
{
	if (!$announceid && $IsPreview)
	{
		$TP['path'] = UnbShowPath($forumid, $_POST['Subject'], $_POST['Desc'], /*threadid*/ 0, true);
	}
	else
	{
		$TP['path'] = UnbShowPath($forumid);
	}
	if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity($forumid);
	else UnbSetGuestLastForum($forumid);
}
elseif (!$announceid)
{
	$TP['path'] = UnbShowPath($forumid, $thread->GetSubject(), $thread->GetDesc(), $threadid, true);
	if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity($forumid);
	else UnbSetGuestLastForum($forumid);
}

if ($_POST['preview'] != '') UnbAddLog('preview_post in ' . $threadid);

if ($announceid != 0)
	PostForm($forumid, 0, 0, $announceid, $thread);
elseif ($forumid < $min_forum && !$threadid && !$postid)
	$TP['errorMsg'] .= $UNB_T['post.error.invalid thread for post'] . '<br />';
else
	PostForm($forumid, $threadid, $postid, 0, $thread);

UnbUpdateStat('PageHits', 1);

UteRemember('posteditor.html', $TP);

UnbEndHTML();
?>
