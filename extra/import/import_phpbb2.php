<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// import_phpbb2.php
// Database import module
//
// TYPE: phpBB 2.0.x
// The TYPE: description will be used to list this module in the installation

// See http://phpbb.de/doku/doku2.php for phpBB database specs

// Disable this file in productive environment!
if (file_exists('lock.conf')) die('The board setup is locked. Remove the file lock.conf to unlock it.');

// Debug mode string. Anything with a + in front of it will be enabled
// See forum.php for details.
$DEBUG = '+htmltype +showerrors';

// Prepare debug mode string
$DEBUG = ' ' . strtolower($DEBUG) . ' ';

define('UNB_RUNNING', 1);       // [nodoc]
define('ERR_REPORT_SET', 1);    // [nodoc]
define('DISPLAY_ERR_SET', 1);   // [nodoc]
error_reporting(E_ALL & ~E_NOTICE);

$UNB['LibraryPath'] = 'unb_lib/';

// Import request variables
$step = intval($_REQUEST['step']);
if (!$step) $step = 1;

$UNB['Installing'] = true;
require_once($UNB['LibraryPath'] . 'common.lib.php');
$UNB['ThisPage'] = 'import_phpbb2.php';

require_once($UNB['LibraryPath'] . 'common_out.lib.php');
UnbRequireTxt('install');
UnbRequireCss('register');   // this stylesheet also includes installation styles

@set_time_limit(0);

// [nodoc]
// Import data from other sources into the UNB database.
// Translate character sets and encodings as required.
//
function UnbImportEncode($str)
{
	// return $str unchanged if import data is already utf-8
	// create custom code here if import-data is not iso-8859-1 or -15

	// convert euro sign (iso-8859-15: 0x80 -> unicode: 0x20AC)
	return
		preg_replace(
			'/&#(\\d+);/e',
			'code2utf($1)',
			utf8_encode(
				str_replace(
					"\x80",
					'&#8364;',
					$str)));
}

// [nodoc]
function group_to_status($group)
{
	// you may need to change this to your group definitions (see groups table)
	//
	switch ($group)
	{
		case 2: return UNB_GROUP_ADMINS;   // admins -> admin
	}
	return 1;
}

// [nodoc]
// convert html font sizes to pixel sizes (px)
//
function transl_bbc($txt, $smilies = true)
{
	// remove all those funny numbers from the BBCode tags

	#$txt = preg_replace('/(\[\/?(?:b|i|u|code|img|list|\*)):[^\]]+\]/i', '$1]', $txt);
	$txt = preg_replace('/(\[\/?(?:b|i|u|code|img|color|size|list|\*)(?:=[^:]*)?):[^\]]+\]/i', '$1]', $txt);
	$txt = preg_replace('/(\[\/?(?:quote)):[^\]=]+((?:=[^\]]*)?\])/i', '$1$2', $txt);

	$txt = preg_replace('/\[email=/i', '[mail=', $txt);
	$txt = preg_replace('/\[email\]/i', '[mail]', $txt);
	$txt = preg_replace('/\[\/email\]/i', '[/mail]', $txt);

	if ($smilies)
	{
		$txt = str_replace('&amp;', '&', $txt);
		$txt = str_replace('&lt;', '<', $txt);
		$txt = str_replace('&gt;', '>', $txt);
		$txt = str_replace('&nbsp;', ' ', $txt);
		$txt = str_replace('&nbsp', ' ', $txt);

		$txt = str_replace(' :D ', ' :-D ', $txt);
		$txt = str_replace(' :oops: ', ' :red: ', $txt);
		$txt = str_replace(' 8) ', ' :cool: ', $txt);
		$txt = str_replace(' :cry: ', ' :\'( ', $txt);
		$txt = str_replace(' :shock: ', ' 8-( ', $txt);
		$txt = str_replace(' :? ', ' 8-( ', $txt);
		$txt = str_replace(' :roll: ', ' :rolleyes: ', $txt);
		$txt = str_replace(' :wink: ', ' ;-) ', $txt);
		$txt = str_replace(' :| ', ' :-/ ', $txt);
		$txt = str_replace(' :P ', ' :-p ', $txt);
	}
	return $txt;
}

// [nodoc]
function transl_lang($lang)
{
	switch ($lang)
	{
		case 'german': return 'de';
	}
	return '';
}


if ($step == 1)
{
	UnbBeginHTML($UNB_T['installation']);
	UteShowAll();

	echo '<h1>' . $UNB_T['installation'] . '</h1>';

	echo '<form action="' . UnbLink('@this', 'step=2', true) . '" method="post">';
	echo '<div class="p">Please enter the database name and table prefix of the phpBB\'s tables. This database must be located on the primary database server and be accessible with the same username and password.</div>';

	echo '<div class="p"><table cellspacing="0" cellpadding="0" class="installation">';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db name'] . ':</td><td><input type="text" name="dbname" value="' . t2i($UNB['Db']->dbname) . '" size="20" style="width: 20em;" /></td></tr>';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db prefix'] . ':</td><td><input type="text" name="tblprefix" value="phpbb_" size="20" style="width: 20em;" /><div class="subtitle">Example: for table names like "phpbb_posts", enter "phpbb_"</div></td></tr>';
	echo '<tr><td class="leftcol">Path to avatar files:</td><td><input type="text" name="avpath" value="&lt;phpbb_root&gt;/images/avatars" size="40" style="width: 30em;" /></td></tr>';
	echo '</table></div>';

	echo '<div class="p"><b>Note:</b> The import process is split up into several steps that are linked with HTML forwards. Each step may take a considerable amount of time. Please don\'t stop the browser, reload the page or click on any of the links as long as the page is still loading or the installation will fail. (You can restart the entire thing again then.)</div>';

	echo '<div class="p"><input type="submit" class="button" value="' . $UNB_T['inst.continue'] . '" /></div>';
	echo '</form>';

	UnbEndHTML();
	UteShowAll();
}

// Save form variables from step 1 into session variables to keep them for later steps
if ($step == 2)
{
	$_SESSION['import_dbname'] = $_REQUEST['dbname'];
	$_SESSION['import_tblprefix'] = $_REQUEST['tblprefix'];
	$av_in_path = $_REQUEST['avpath'];   // path to current avatar files (with "/")
	if (substr($av_in_path, strlen($av_in_path) - 1) != '/') $av_in_path .= '/';
	$_SESSION['import_avpath'] = $av_in_path;
}

// Initialisation for all operative steps
if ($step >= 2)
{
	// Open source database
	$db0 = new IDatabase;
	$db0->server = $UNB['Db']->server;
	$db0->user = $UNB['Db']->user;
	$db0->password = $UNB['Db']->password;
	$db0->dbname = $_SESSION['import_dbname'];
	$db0->tblprefix = $_SESSION['import_tblprefix'];
	$db0->Open() or die('source database open error: ' . t2h($db0->LastError()));

	$av_in_path = $_SESSION['import_avpath'];   // path to current avatar files (with "/")
}

if ($step == 2)
{
	// First we have to create all necessary tables.

	// Keep this in sync with the install.php main installer!
	// DO NOT ADD ANY RECORDS HERE (except for some ACL things),
	// because this will lead to duplicate key errors!

	$UNB['Db']->RemoveTable('Forums') or die('remove error: ' . $UNB['Db']->LastError());
	$UNB['Db']->CreateTable('Forums',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Sort` INT, ' .
					 '`Parent` INT UNSIGNED, ' .
					 '`Name` VARCHAR(100), ' .
					 '`Flags` INT, ' .
					 '`Description` TEXT, ' .
					 '`Link` VARCHAR(255), ' .
					 'PRIMARY KEY (ID)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('Threads') or die('remove error');
	$UNB['Db']->CreateTable('Threads',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Forum` INT UNSIGNED, ' .
					 '`LastPostDate` INT, ' .
					 '`Subject` VARCHAR(150), ' .
					 '`Desc` VARCHAR(150), ' .
					 '`Date` INT, ' .
					 '`User` INT UNSIGNED, ' .
					 '`UserName` VARCHAR(40), ' .
					 '`Views` INT UNSIGNED, ' .
					 '`Options` INT UNSIGNED, ' .
					 '`Question` VARCHAR(150), ' .
					 '`PollTimeout` INT, ' .
					 '`LastVoted` INT, ' .
					 'PRIMARY KEY (ID),' .
					 'KEY LastPostDate (LastPostDate)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('Posts') or die('remove error');
	$UNB['Db']->CreateTable('Posts',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Thread` INT UNSIGNED, ' .
					 '`ReplyTo` INT UNSIGNED, ' .
					 '`Date` INT, ' .
					 '`EditUser` INT UNSIGNED, ' .
					 '`EditDate` INT, ' .
					 '`EditCount` SMALLINT UNSIGNED, ' .
					 '`EditReason` VARCHAR(255), ' .
					 '`User` INT UNSIGNED, ' .
					 '`UserName` VARCHAR(40), ' .
					 '`Subject` VARCHAR(150), ' .
					 '`Msg` TEXT, ' .
					 '`Options` INT UNSIGNED, ' .
					 '`AttachFile` VARCHAR(40), ' .
					 '`AttachFileName` VARCHAR(100), ' .
					 '`AttachDLCount` INT UNSIGNED, ' .
					 '`IP` VARCHAR(15), ' .
					 '`Hostname` VARCHAR(150), ' .
					 '`SpamRating` INT, ' .
					 'PRIMARY KEY (ID), ' .
					 'KEY Thread (Thread), ' .
					 'KEY Date (Date), ' .
					 'KEY User (User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$extra = '';
	$UNB['Db']->RemoveTable('Users') or die('remove error');
	$UNB['Db']->CreateTable('Users',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Name` VARCHAR(40), ' .
					 '`Password` VARCHAR(200), ' .
					 '`RegDate` INT, ' .
					 '`RegEMail` VARCHAR(100), ' .
					 '`DefaultNotify` TINYINT UNSIGNED, ' .
					 '`EMail` VARCHAR(100), ' .
					 '`Jabber` VARCHAR(50), ' .
					 '`ICQ` VARCHAR(15), ' .
					 '`AIM` VARCHAR(30), ' .
					 '`YIM` VARCHAR(30), ' .
					 '`MSN` VARCHAR(50), ' .
					 '`LastActivity` INT, ' .
					 '`LastForum` INT, ' .
					 '`LastLogin` INT, ' .
					 '`LastLogout` INT, ' .
					 '`Signature` TEXT, ' .
					 '`BirthDay` TINYINT UNSIGNED, ' .
					 '`BirthMonth` TINYINT UNSIGNED, ' .
					 '`BirthYear` SMALLINT UNSIGNED, ' .
					 '`About` TEXT, ' .
					 '`Title` VARCHAR(40), ' .
					 '`Location` VARCHAR(50), ' .
					 '`Homepage` VARCHAR(150), ' .
					 '`Gender` CHAR(1), ' .
					 '`Avatar` VARCHAR(150), ' .
					 '`AvatarX` SMALLINT, ' .
					 '`AvatarY` SMALLINT, ' .
					 '`Photo` VARCHAR(150), ' .
					 $extra .
					 '`ValidateKey` VARCHAR(50), ' .
					 '`RecommendedBy` INT UNSIGNED, ' .
					 '`Design` VARCHAR(50), ' .
					 '`Flags` INT UNSIGNED, ' .
					 '`Timezone` TINYINT DEFAULT 99, ' .
					 '`TimezoneDS` TINYINT DEFAULT -1, ' .
					 '`EditControls` INT UNSIGNED, ' .
					 '`ThreadsPerPage` SMALLINT DEFAULT -1, ' .
					 '`ThreadSort` SMALLINT DEFAULT -1, ' .
					 '`ThreadTime` SMALLINT DEFAULT -1, ' .
					 '`Language` VARCHAR(6), ' .
					 '`DateFormat` VARCHAR(20), ' .
					 'PRIMARY KEY (ID),' .
					 'KEY LastActivity (LastActivity)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('PollVotes') or die('remove error');
	$UNB['Db']->CreateTable('PollVotes',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Thread` INT UNSIGNED, ' .
					 '`Sort` TINYINT, ' .
					 '`Title` VARCHAR(100), ' .
					 '`Votes` INT UNSIGNED, ' .
					 'PRIMARY KEY (ID)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('PollUsers') or die('remove error');
	$UNB['Db']->CreateTable('PollUsers',
					 '`Thread` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`VoteNum` INT UNSIGNED, ' .
					 'PRIMARY KEY (Thread, User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('Announces') or die('remove error');
	$UNB['Db']->CreateTable('Announces',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Forum` INT, ' .
					 '`Date` INT, ' .
					 '`User` INT UNSIGNED, ' .
					 '`Subject` VARCHAR(150), ' .
					 '`Msg` TEXT, ' .
					 '`Options` INT UNSIGNED, ' .
					 'PRIMARY KEY (ID)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('AnnounceRead') or die('remove error');
	$UNB['Db']->CreateTable('AnnounceRead',
					 '`Announce` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 'PRIMARY KEY (Announce, User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('ThreadWatch') or die('remove error');
	$UNB['Db']->CreateTable('ThreadWatch',
					 '`Thread` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Mode` TINYINT UNSIGNED, ' .
					 '`LastRead` INT NOT NULL DEFAULT 0, ' .
					 '`LastNotify` INT NOT NULL DEFAULT 0, ' .
					 '`LastViewed` INT NOT NULL DEFAULT 0, ' .
					 'PRIMARY KEY (Thread, User), ' .
					 'KEY Thread (Thread), ' .
					 'KEY User (User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('ForumWatch') or die('remove error');
	$UNB['Db']->CreateTable('ForumWatch',
					 '`Forum` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Mode` TINYINT UNSIGNED, ' .
					 '`Flags` TINYINT UNSIGNED, ' .
					 '`LastVisited` INT NOT NULL DEFAULT 0, ' .
					 '`LastNotify` INT NOT NULL DEFAULT 0, ' .
					 'PRIMARY KEY (Forum, User), ' .
					 'KEY Forum (Forum), ' .
					 'KEY User (User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('UserForumFlags') or die('remove error');
	$UNB['Db']->CreateTable('UserForumFlags',
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Forum` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Thread` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Flags` TINYINT UNSIGNED, ' .
					 'PRIMARY KEY (User, Forum, Thread)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('Guests') or die('remove error');
	$UNB['Db']->CreateTable('Guests',
					 '`Session` VARCHAR(80), ' .
					 '`LastActivity` INT, ' .
					 '`LastForum` INT, ' .
					 '`UserName` VARCHAR(40)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('Stat') or die('remove error');
	$UNB['Db']->CreateTable('Stat',
					 '`Date` INT NOT NULL DEFAULT 0, ' .
					 '`NewThreads` INT, ' .
					 '`NewPosts` INT, ' .
					 '`OnlineUsers` INT, ' .
					 '`OnlineGuests` INT, ' .
					 '`PageHits` INT, ' .
					 '`NewUsers` INT, ' .
					 'PRIMARY KEY (Date)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('ACL') or die('remove error');
	$UNB['Db']->CreateTable('ACL',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED, ' .
					 '`Group` INT UNSIGNED, ' .
					 '`Action` SMALLINT UNSIGNED, ' .
					 '`Thread` INT UNSIGNED, ' .
					 '`Forum` INT UNSIGNED, ' .
					 '`Grant` INT, ' .
					 '`Enabled` BOOL, ' .
					 'PRIMARY KEY (ID), ' .
					 'UNIQUE INDEX (User, `Group`, Action, Thread, Forum)'
		) or die('create error: ' . $UNB['Db']->LastError());

	$UNB['Db']->SetTable('ACL');
	$UNB['Db']->AddRecord(array('ID' => 1,
						 'User' => 0,
						 'Group' => UNB_GROUP_GUESTS,
						 'Action' => 33,   // viewforum
						 'Thread' => 0,
						 'Forum' => 0,
						 'Grant' => 1,
						 'Enabled' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => 2,
						 'User' => 0,
						 'Group' => UNB_GROUP_MEMBERS,
						 'Action' => 82,   // user rights
						 'Thread' => 0,
						 'Forum' => 0,
						 'Grant' => 1,
						 'Enabled' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => 3,
						 'User' => 0,
						 'Group' => UNB_GROUP_MODS,
						 'Action' => 83,   // mod rights
						 'Thread' => 0,
						 'Forum' => 0,
						 'Grant' => 1,
						 'Enabled' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => 4,
						 'User' => 0,
						 'Group' => UNB_GROUP_ADMINS,
						 'Action' => 84,   // admin rights
						 'Thread' => 0,
						 'Forum' => 0,
						 'Grant' => 1,
						 'Enabled' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('GroupMembers') or die('remove error');
	$UNB['Db']->CreateTable('GroupMembers',
					 '`Group` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`User` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 'PRIMARY KEY (`Group`, User)'
		) or die('create error: ' . $UNB['Db']->LastError());
	$UNB['Db']->RemoveTable('GroupNames') or die('remove error');
	$UNB['Db']->CreateTable('GroupNames',
					 '`ID` INT UNSIGNED NOT NULL DEFAULT 0, ' .
					 '`Name` VARCHAR(50), ' .
					 '`ShowInTeam` TINYINT(1), ' .
					 'PRIMARY KEY (ID)'
		) or die('create error: ' . $UNB['Db']->LastError());

	$UNB['Db']->SetTable('GroupNames');
	$UNB['Db']->AddRecord(array('ID' => UNB_GROUP_GUESTS,
						 'Name' => $UNB_T['inst.grp guests'],
						 'ShowInTeam' => 0
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => UNB_GROUP_MEMBERS,
						 'Name' => $UNB_T['inst.grp users'],
						 'ShowInTeam' => 0
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => UNB_GROUP_MODS,
						 'Name' => $UNB_T['inst.grp gmods'],
						 'ShowInTeam' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());
	$UNB['Db']->AddRecord(array('ID' => UNB_GROUP_ADMINS,
						 'Name' => $UNB_T['inst.grp admins'],
						 'ShowInTeam' => 1
		)) or die('add error: ' . $UNB['Db']->LastError());

	// ------------------------------------------------------------ FORUMS

	// categroies and forums IDs cannot be the same here!

	// so we append the cat IDs after the forums IDs number space
	$fmax = $db0->FastQuery1st('forums', 'MAX(forum_id)');

	$UNB['Db']->SetTable('Forums');
	$arr = $db0->FastQueryArray('categories');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['cat_id'] + $fmax + 1,
							 'Sort' => $a['cat_order'],
							 'Parent' => 0,
							 'Name' => UnbImportEncode($a['cat_title']),
							 'Flags' => 1
			)) or die('[forums] add error: ' . $UNB['Db']->LastError());
	}

	$arr = $db0->FastQueryArray('forums');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['forum_id'],
							 'Sort' => $a['forum_order'],
							 'Parent' => $a['cat_id'] + $fmax + 1,
							 'Name' => UnbImportEncode($a['forum_name']),
							 'Flags' => 0,
							 'Description' => UnbImportEncode(transl_bbc($a['forum_desc']))
			)) or die('[forums] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ THREADS

	$UNB['Db']->SetTable('Threads');
	$arr = $db0->FastQueryArray('topics');
	if ($arr) foreach($arr as $a)
	{
		$timeout = 0;
		$question = '';

		// is this a poll?
		// TODO: use an SQL JOIN for this
		$vote = $db0->FastQuery('vote_desc', '*', 'topic_id=' . $a['topic_id']);
		if ($vote)
		{
			if ($vote['vote_length'])   // number of seconds, 0 = infinite
				$timeout = $a['topic_time'] + $vote['vote_length'];
			$question = $vote['vote_text'];
		}

		$options = 0;
		if ($a['topic_status'] & 1) $options |= 1;   // closed
		if ($a['topic_status'] & 2) $options |= 8;   // moved
		if ($a['topic_type'] & 1) $options |= 2;   // important
		if ($a['topic_type'] & 2) $options |= 2;   // actually... announcement... TODO
		if ($vote) $options |= 4;   // poll

		if ($a['topic_status'] & 2)   // moved
		{
			$question = $a['topic_moved_id'];
			$timeout = time() + 14 * 24 * 3600;   // show "moved" link for 14 days
		}

		$UNB['Db']->AddRecord(array('ID' => $a['topic_id'],
							 'Forum' => $a['forum_id'],
							 'LastPostDate' => 0,  #$a['timelastreply'],  // this is not always correct, we do it ourselves later
							 'Subject' => UnbImportEncode(transl_bbc($a['topic_title'])),
							 'Date' => $a['topic_time'],
							 'User' => max(0, $a['topic_poster']),   // must be >= 0
							 'UserName' => '',
							 'Views' => $a['topic_views'],
							 'Options' => $options,
							 'Question' => UnbImportEncode($question),
							 'PollTimeout' => $timeout
			)) or die('[threads] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=3'));
}

if ($step == 3)
{
	// ------------------------------------------------------------ POSTS

	$UNB['Db']->SetTable('Posts');
	$arr = $db0->FastQueryArray('posts');
	if ($arr) foreach($arr as $a)
	{
		$text = $db0->FastQuery('posts_text', '*', 'post_id=' . $a['post_id']);
		// TODO: use an SQL JOIN for this

		$ip_a = preg_match('/^(..)(..)(..)(..)$/', $a['ip'], $m);
		$ip = chr(hexdec($m[1])) . '.' . chr(hexdec($m[2])) . '.' . chr(hexdec($m[3])) . '.' . chr(hexdec($m[4]));

		$UNB['Db']->AddRecord(array('ID' => $a['post_id'],
							 'Thread' => $a['topic_id'],
							 'ReplyTo' => 0,
							 'Date' => $a['post_time'],
							 'EditUser' => 0,
							 'EditDate' => $a['post_edit_time'],
							 'EditCount' => $a['post_edit_count'],
							 'User' => $a['poster_id'],
							 'UserName' => UnbImportEncode($a['post_username']),
							 'Subject' => UnbImportEncode(transl_bbc($text['post_subject'])),
							 'Msg' => UnbImportEncode(transl_bbc($text['post_text'], $a['enable_smilies'])),
							 'Options' => ($a['enable_smilies'] ? 0 : 1),
							 'IP' => $ip,
							 'Hostname' => '',
							 'SpamRating' => 0
			)) or die('[posts] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=4'));
}

if ($step == 4)
{
	// ------------------------------------------------------------ USERS

	// [nodoc]
	function avatar_file($oldname, $user)
	{
		if (!$oldname) return '';   // no avatar set for this user

		global $UNB, $av_in_path;

		preg_match('/\.([^.]+)$/', $oldname, $m);

		$name0 = $oldname;
		$name1 = 'avatar_' . $user . '.' . $m[1];

		@copy($av_in_path . $name0, $UNB['AvatarPath'] . $name1);

		return $name1;
	}

	$arr = $db0->FastQueryArray('users');
	if ($arr) foreach($arr as $a)
	{
		if ($a['user_id'] < 0) continue;

		$UNB['Db']->AddRecord(array('ID' => $a['user_id'],
							 'Name' => UnbImportEncode($a['username']),
							 'Password' => $a['user_password'],
							 'RegDate' => $a['user_regdate'],
							 'RegEMail' => $a['user_email'],
							 'DefaultNotify' => ($a['user_notify'] ? 1 : 0),
							 'EMail' => $a['user_email'],
							 'ICQ' => UnbImportEncode($a['user_icq']),
							 'AIM' => UnbImportEncode($a['user_aim']),
							 'YIM' => UnbImportEncode($a['user_yim']),
							 'MSN' => UnbImportEncode($a['user_msnm']),
							 'LastActivity' => $a['user_lastvisit'],
							 'LastForum' => 0,
							 'Signature' => UnbImportEncode(transl_bbc($a['user_sig'])),
							 'Location' => UnbImportEncode($a['user_from']),
							 'Homepage' => $a['user_website'],
							 'Avatar' =>
							 	($a['user_avatar_type'] == 1 ?
							 		avatar_file($a['user_avatar'], $a['user_id']) :   // local file
							 		$a['user_avatar']),                               // remote URL or no avatar
							 'ValidateKey' => '',
							 'Flags' => UNB_USER_AUTOLOGIN,
							 'Language' => transl_lang($a['user_lang']),
							 'Timezone' => $a['user_timezone'] * 4
			), 'Users') or die('[users] add error: ' . $UNB['Db']->LastError());

		if ($a['user_active'])
		{
			$group = $db0->FastQuery('user_group', '*', 'user_id=' . $a['user_id']);
			$status = group_to_status($group['group_id']);
			if ($status > 0)
				$UNB['Db']->AddRecord(array('User' => $a['user_id'],
									 'Group' => UNB_GROUP_MEMBERS
					), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
			if ($status > 1)
				$UNB['Db']->AddRecord(array('User' => $a['user_id'],
									 'Group' => $status
					), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
		}
	}

	// ------------------------------------------------------------ POLL VOTES

	$i = 1;
	$UNB['Db']->SetTable('PollVotes');
	$arr = $db0->FastQueryArray('vote_results');
	if ($arr) foreach($arr as $a)
	{
		$vote = $db0->FastQuery('vote_desc', '*', 'vote_id=' . $a['vote_id']);
		$UNB['Db']->AddRecord(array('ID' => $i++,
							 'Thread' => $vote['topic_id'],
							 'Sort' => 0,
							 'Title' => UnbImportEncode($a['vote_option_text']),
							 'Votes' => $a['vote_result']
			)) or die('[poll votes] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ POLL USERS

	$UNB['Db']->SetTable('PollUsers');
	$arr = $db0->FastQueryArray('vote_voters');
	if ($arr) foreach($arr as $a)
	{
		$vote = $db0->FastQuery('vote_desc', '*', 'vote_id=' . $a['vote_id']);
		$UNB['Db']->AddRecord(array('Thread' => $vote['topic_id'],
							 'User' => $a['vote_user_id'],
							 'VoteNum' => 0
			)) or die('[poll users] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ THREADWATCH

	$UNB['Db']->SetTable('ThreadWatch');
	$arr = $db0->FastQueryArray('topics_watch');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Thread' => $a['topic_id'],
							 'User' => $a['user_id'],
							 'Mode' => 1   // always notify via e-mail
			)) or die('[thread watch] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=5'));
}

if ($step == 5)
{
	// ------------------------------------------------------------ CONFIG

	$UNB['ConfigFile']['forum_title'] = $db0->FastQuery1st('config', 'config_value', 'config_name="sitename"');

	$server_name = $db0->FastQuery1st('config', 'config_value', 'config_name="server_name"');

	/*
	$server_port = $db0->FastQuery1st('config', 'config_value', 'config_name="server_port"');
	$script_path = $db0->FastQuery1st('config', 'config_value', 'config_name="script_path"');
	$http = ($server_port == 443 ? 'https://' : 'http://');
	if ($server_port != 80 && $server_port != 443)
		$server_port = ':' . $server_port;
	else
		$server_port = '';
	$home_url = $http . $server_name . $server_port . $script_path;
	$UNB['ConfigFile']['home_url'] = $home_url;
	*/

	$UNB['ConfigFile']['smtp_server'] = $db0->FastQuery1st('config', 'config_value', 'config_name="smtp_host"');
	$UNB['ConfigFile']['smtp_sender'] = $db0->FastQuery1st('config', 'config_value', 'config_name="board_email"');
	$UNB['ConfigFile']['smtp_user'] = $db0->FastQuery1st('config', 'config_value', 'config_name="smtp_username"');
	$UNB['ConfigFile']['smtp_pass'] = $db0->FastQuery1st('config', 'config_value', 'config_name="smtp_password"');

	$UNB['ConfigFile']['new_user_validation'] = intval($db0->FastQuery1st('config', 'config_value', 'config_name="require_activation"')) + 1;

	$UNB['ConfigFile']['threads_per_page'] = $db0->FastQuery1st('config', 'config_value', 'config_name="topics_per_page"');
	$UNB['ConfigFile']['posts_per_page'] = $db0->FastQuery1st('config', 'config_value', 'config_name="posts_per_page"');
	$UNB['ConfigFile']['hot_thread_posts'] = $db0->FastQuery1st('config', 'config_value', 'config_name="hot_threshold"');

	$av_remote = $db0->FastQuery1st('config', 'config_value', 'config_name="allow_avatar_remote"');

	$av_upload = $db0->FastQuery1st('config', 'config_value', 'config_name="allow_avatar_upload"');
	$av_size = $db0->FastQuery1st('config', 'config_value', 'config_name="avatar_filesize"');

	if (!$av_remote && (!$av_upload || !$av_size)) $avatars = 0;
	else $avatars = 1;
	$UNB['ConfigFile']['avatars_enabled'] = $avatars;
	$UNB['ConfigFile']['avatar_x'] = $db0->FastQuery1st('config', 'config_value', 'config_name="avatar_max_width"');
	$UNB['ConfigFile']['avatar_y'] =	$db0->FastQuery1st('config', 'config_value', 'config_name="avatar_max_height"');
	$UNB['ConfigFile']['avatar_bytes'] =	$av_size;

	if ($db0->FastQuery1st('config', 'config_value', 'config_name="allow_sig"'))
		$UNB['ConfigFile']['max_sig_len'] = $db0->FastQuery1st('config', 'config_value', 'config_name="max_sig_chars"');
	else
		$UNB['ConfigFile']['max_sig_len'] = 0;

	$UNB['ConfigFile']['tz_offset'] = $db0->FastQuery1st('config', 'config_value', 'config_name="board_timezone"') * 4;
	#$UNB['ConfigFile']['prog_id'] = $db0->FastQuery1st('config', 'config_value', 'config_name="cookie_name"');

	if (!UnbRebuildConffile()) die($UNB_T['error.write conffile']);

	UnbForwardHTML(UnbLink('@this', 'step=6'));
}

if ($step == 6)
{
	UnbBeginHTML($UNB_T['installation']);
	UteShowAll();

	echo '<h1>' . $UNB_T['installation'] . '</h1>';

	echo '<div class="p">';
	echo 'Conversion finished. ' . $UNB_T['inst.check db integrity'] . '<br />';

	// Set correct LastPostDate for all threads
	$thread = new IThread;
	$arr = $UNB['Db']->FastQueryArray('Threads');
	if ($arr) foreach($arr as $a)
	{
		$record = UnbGetLastPost(intval($a['ID']));
		$thread->SetLastPostDate($record['Date'], $a['ID']);
	}

	// Add thread-has-attachment flags
	$thread = new IThread;
	$thread2 = new IThread;
	$post = new IPost;
	if ($thread->Find()) do
	{
		$found = $post->Find('Thread=' . $thread->GetID() . " AND AttachFile<>''", '', 1);

		$opt = $thread->GetOptions();
		$opt = ($opt & ~16) | (($found ? 1 : 0) * 16);
		$thread2->SetOptions($opt, $thread->GetID());   // no error detection here
	}
	while ($thread->FindNext());

	// Store correct avatar image dimensions
	$user = new IUser;
	if ($user->GetList()) do
	{
		if ($user->GetAvatar() != '')
		{
			$is = @getimagesize((strpos($user->GetAvatar(), ':') ? '' : $UNB['AvatarPath']) . $user->GetAvatar());
			if (is_array($is))
			{
				$newx = $is[0];
				$newy = $is[1];
				if (!$UNB['Db']->ChangeRecord(array('AvatarX' => $newx, 'AvatarY' => $newy), 'ID=' . $user->GetID(), 'Users'))
				{
					echo 'Warning: Database error: ' . t2h($UNB['Db']->LastError()) . '<br />';
				}
			}
		}
	}
	while ($user->GetNext());

	echo '<b>' . $UNB_T['inst.done'] . '</b></div>';

	echo '<div class="p">Import process finished. Not all information from the board software may have been copied, please check the new board configuration and all access rights (user groups, forum access rights, ACL). It\'s recommended to fully check the database integrity again from the Administrator\'s Control Panel and correct possible discrepancies.</div>';

	echo '<div class="p">' . $UNB_T['inst.upgrade note'] . '</div>';

	touch('lock.conf');
	echo '<div class="p">' . $UNB_T['inst.lock'] . '</div>';

	echo '<div class="p"><a href="' . UnbLink('@main', null, true) . '">' . $UNB_T['inst.go overview'] . '</a></div>';
	UnbEndHTML();
	UteShowAll();
}

?>
