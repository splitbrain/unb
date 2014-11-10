<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// random.php
// Create random forums, threads, posts and users for mass data testing

// Disable this file in productive environment!
if (file_exists('lock.conf')) die('The board setup is locked. Remove the file lock.conf to unlock it.');

$libpath = 'unb_lib/';

if (!defined('PUBLIC_LIB')) require_once($libpath . 'public.lib.php');
$ME = 'random.php';

require_once($libpath . 'common.lib.php');
UnbReadACL();

set_time_limit(120);

// seed with microseconds since last "whole" second
mt_srand((double) microtime() * 1000000);
$randval = mt_rand();

$c_users = 0;
$c_forums = 0;
$c_threads = 0;
$c_posts = 0;

$BOUNDS = array();
$BOUNDS['user_min'] = 150;
$BOUNDS['user_max'] = 250;
$BOUNDS['forum_min'] = 3;
$BOUNDS['forum_max'] = 6;
$BOUNDS['subforum_chance'] = 30;
$BOUNDS['cat_chance'] = 40;
$BOUNDS['thread_min'] = 0;
$BOUNDS['thread_max'] = 10;
$BOUNDS['post_min'] = 1;
$BOUNDS['post_max'] = 5;


function chance($percent)
{
	return mt_rand(0, 100) <= $percent;
}

function numgen($min, $max)
{
	return mt_rand($min, $max);
}

function chargen()
{
	$asc = mt_rand(0, 10+26+(26*4));

	if ($asc < 10) return chr($asc + 0x30);   // 0...9
	$asc -= 10;
	if ($asc < 26) return chr($asc + 0x41);   // A...Z
	for ($n = 0; $n < 4; $n++)
	{
		$asc -= 26;
		if ($asc < 26) return chr($asc + 0x61);   // a...z
	}
	return '';
}

function punctgen()
{
	$asc = mt_rand(0, 7);

	switch ($asc)
	{
		case 0: return '!';
		case 1: return ',';
		case 2: return '-';
		case 3: return '.';
		case 4: return '/';
		case 5: return ':';
		case 6: return ';';
		case 7: return '?';
	}
	return '';
}

function wordgen($min, $max, $nl = false)
{
	$str = '';
	$count = mt_rand($min, $max);
	while ($count--)
	{
		$len = mt_rand(2, 10);
		while ($len--)
			$str .= chargen();
		if (chance(10)) $str .= punctgen();
		if ($nl && chance(5)) $str .= "\n";
		elseif ($count > 1) $str .= ' ';
	}
	return $str;
}

function abbcgen($min, $max)
{
	return wordgen($min, $max, true);
}


UnbBeginHTML('Random Data Generator');

echo '<div class="p"><b>Random Data Generator</b></div>';

function addusers()
{
	global $max_user, $extra_count, $db, $c_users, $BOUNDS;

	$user = new IUser;
	$count = numgen($BOUNDS['user_min'], $BOUNDS['user_max']);
	while ($count--)
	{
		$max = intval($db->FastQuery1st("Users", "max(ID)"));

		$user->ID = $max + 1;
		$user->Name = trim(substr(wordgen(1, 2), 0, 18));
		$user->Password = md5(wordgen(1, 1));
		$user->RegDate = numgen(time() - 5 * 365 * 24 * 3600, time());
		$user->RegEMail = wordgen(1, 1) . '@' . wordgen(1, 1) . '.fake';
		$user->DefaultNotify = 0;
		$user->EMail = trim($EMail);
		$user->LastActivity = numgen($user->RegDate, time());
		$user->Signature = chance(30) ? wordgen(3, 20, true) : '';
		$user->BirthDay = numgen(1, 28);
		$user->BirthMonth = numgen(1, 12);
		$user->BirthYear = numgen(1900, 2003);
		$user->Title = chance(20) ? wordgen(1, 2) : '';
		$user->Location = chance(40) ? wordgen(1, 2) : '';
		$user->Gender = chance(60) ? (chance(70) ? 'm' : 'f') : '';
		if ($extra_count > 0)
			$user->Extra = array_fill(1, $extra_count, '');
		else
			$user->Extra = array();

		$add = array(
			'ID' => $user->ID,
			'Name' => $user->Name,
			'Password' => $user->Password,
			'RegDate' => $user->RegDate,
			'RegEMail' => $user->RegEMail,
			'DefaultNotify' => $user->DefaultNotify,
			'EMail' => $user->EMail,
			'ICQ' => $user->ICQ,
			'AIM' => $user->AIM,
			'YIM' => $user->YIM,
			'MSN' => $user->MSN,
			'Jabber' => $user->Jabber,
			'LastActivity' => $user->LastActivity,
			'LastForum' => $user->LastForum,
			'Signature' => $user->Signature,
			'BirthDay' => $user->BirthDay,
			'BirthMonth' => $user->BirthMonth,
			'BirthYear' => $user->BirthYear,
			'About' => $user->About,
			'Title' => $user->Title,
			'Location' => $user->Location,
			'Homepage' => $user->Homepage,
			'Gender' => $user->Gender,
			'Avatar' => $user->Avatar,
			'Photo' => $user->Photo,
			'ActivateKey' => $user->ActivateKey,
			'Design' => $user->Design,
			'ThreadIcons' => $user->ThreadIcons,
			'Flags' => $user->Flags,
			'Timezone' => $user->Timezone,
			'TimezoneDS' => $user->TimezoneDS,
			'EditControls' => $user->EditControls,
			'ThreadsPerPage' => $user->ThreadsPerPage,
			'ThreadSort' => $user->ThreadSort,
			'ThreadTime' => $user->ThreadTime,
			'Language' => $user->Language,
			'DateFormat' => $user->DateFormat);

		for ($n = 1; $n <= $extra_count; $n++)
			$add['Extra' . $n] = $user->Extra[$n];

		$ok = $db->AddRecord($add, 'Users');

		// update statistics table, no error detection here
		if ($ok)
		{
			UnbUpdateStat('NewUsers', 1);
			$c_users++;

			$max_user = max($user->ID, $max_user);
		}
		if (!$ok) return false;
	}
	return true;
}

function addposts($threadid)
{
	global $max_user, $c_posts, $db, $last_date, $BOUNDS;

	$post = new IPost;
	$thread = new IThread;
	$user = new IUser;
	$effcount = 0;
	$count = numgen($BOUNDS['post_min'], $BOUNDS['post_max']);
	while ($count--)
	{
		$user->Load(numgen(1, $max_user));
		$thread->Load($threadid);

		$max = intval($db->FastQuery1st("Posts", "max(ID)"));

		$post->ID = $max + 1;
		$post->Thread = $threadid;
		$post->Date = numgen(max($thread->GetDate(), $user->GetRegDate()), time());
		$post->User = $user->GetID();
		$post->UserName = '';
		$post->Subject = wordgen(0, 4);
		$post->Msg = abbcgen(1, 300);
		$post->Options = 0;
		$post->IP = $_SERVER['REMOTE_ADDR'];
		$post->Hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);

		$ok = $db->AddRecord(array(
				'ID' => $post->ID,
				'Thread' => $post->Thread,
				'ReplyTo' => $post->ReplyTo,
				'Date' => $post->Date,
				'EditUser' => $post->EditUser,
				'EditDate' => $post->EditDate,
				'EditCount' => $post->EditCount,
				'User' => $post->User,
				'UserName' => $post->UserName,
				'Subject' => $post->Subject,
				'Msg' => $post->Msg,
				'Options' => $post->Options,
				'AttachFile' => $post->AttachFile,
				'AttachFileName' => $post->AttachFileName,
				'AttachDLCount' => $post->AttachDLCount,
				'IP' => $post->IP,
				'Hostname' => $post->Hostname,
				'SpamRating' => $post->SpamRating
			), "Posts");

		// update statistics table, no error detection here
		if ($ok)
		{
			UnbUpdateStat("NewPosts", 1);
			$c_posts++;
			$effcount++;
			$last_date = max($last_date, $post->Date);
		}
		else return false;
	}
	return $effcount;
}

function addthreads($forumid)
{
	global $max_user, $c_threads, $BOUNDS, $last_date;

	$thread = new IThread;
	$user = new IUser;
	$count = numgen($BOUNDS['thread_min'], $BOUNDS['thread_max']);
	while ($count--)
	{
		$user->Load(numgen(1, $max_user));
		$time = numgen($user->GetRegDate(), time());
		if (!$thread->Add($forumid, '', wordgen(1, 5), 0, $time)) return false;
		$c_threads++;

		$last_date = 0;
		$post_count = addposts($thread->GetID());
		$thread->SetLastPostDate($last_date);
		$thread->IncViews(0, numgen($post_count, $post_count + 600));
	}
	return true;
}

function addforums($id, $level = 0)
{
	global $c_forums, $BOUNDS;

	if ($level > 5) return true;
	$forum = new IForum;
	$count = numgen($BOUNDS['forum_min'], $BOUNDS['forum_max']);
	$sort = 0;
	while ($count--)
	{
		$cat = !$id && chance($BOUNDS['cat_chance']);
		if (!$forum->Add($sort++, $id, wordgen(1, 3), $cat, wordgen(0, 10))) return false;
		$c_forums++;
		if (!$cat) addthreads($forum->GetID());
		if ($cat || chance($BOUNDS['subforum_chance']))
			if (!addforums($forum->GetID(), $level + 1)) return false;
	}
	return true;
}

// clear all tables
$db->RemoveRecord('', 'Forums');
$db->RemoveRecord('User > 1', 'GroupMembers');
$db->RemoveRecord('', 'MessageRead');
$db->RemoveRecord('', 'Messages');
$db->RemoveRecord('', 'PollUsers');
$db->RemoveRecord('', 'PollVotes');
$db->RemoveRecord('', 'Posts');
$db->RemoveRecord('', 'Stat');
$db->RemoveRecord('', 'ThreadWatch');
$db->RemoveRecord('', 'Threads');
$db->RemoveRecord('', 'UserCategoryState');
$db->RemoveRecord('ID > 1', 'Users');

if (!addusers()) echo '<div class="error">Error while creating users...</div>';
if (!addforums(0)) echo '<div class="error">Error while creating forums...</div>';

echo "<div class=\"p\">Finished. Created $c_users users, $c_forums forums, $c_threads threads and $c_posts posts.</div>";

UnbEndHTML();
?>
