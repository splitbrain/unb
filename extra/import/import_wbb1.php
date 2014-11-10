<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// import_wbb1.php
// Database import module
//
// TYPE: Woltlab Burning Board 1.x
// The TYPE: description will be used to list this module in the installation

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
$UNB['ThisPage'] = 'import_wbb1.php';

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
		case 1: return UNB_GROUP_ADMINS;   // admins -> admin
		case 2: return UNB_GROUP_MODS;   // ops -> mod
		case 3: return UNB_GROUP_MODS;   // mods -> mod
		case 4: return UNB_GROUP_MEMBERS;   // users -> user
		case 5: return UNB_GROUP_GUESTS;   // guests -> guest
		case 6: return UNB_GROUP_MEMBERS;   // vips -> user
	}
	return 1;
}

// [nodoc]
// convert html font sizes to pixel sizes (px)
//
function transl_bbc($txt)
{
	$txt = preg_replace('/\[size=1\]/i', '[size=9]', $txt);
	$txt = preg_replace('/\[size=2\]/i', '[size=12]', $txt);
	$txt = preg_replace('/\[size=3\]/i', '[size=13]', $txt);
	$txt = preg_replace('/\[size=4\]/i', '[size=16]', $txt);
	$txt = preg_replace('/\[size=5\]/i', '[size=21]', $txt);

	$txt = preg_replace('/\[email=/i', '[mail=', $txt);
	$txt = preg_replace('/\[\/email\]/i', '[/mail]', $txt);
	$txt = preg_replace('/\[(\/)?d\]/i', '[$1s]', $txt);
	$txt = preg_replace('/\[\/?bt\]/i', '', $txt);
	$txt = preg_replace('/\[\/?glow=?.*?\]/i', '', $txt);
	$txt = preg_replace('/\[quote\]\[i\]Original von (.*?)\[\/i\]/i', '[quote=$1]$2', $txt);

	$txt = str_replace('&nbsp;', ' ', $txt);
	$txt = str_replace('&nbsp', ' ', $txt);
	$txt = str_replace('&quot;', '"', $txt);
	$txt = str_replace('&acute;', '\'', $txt);
	$txt = str_replace('&amp;', '&', $txt);
	$txt = str_replace('&lt;', '<', $txt);
	$txt = str_replace('&gt;', '>', $txt);
	$txt = str_replace(' :D ', ' :-D ', $txt);
	$txt = str_replace(' :O ', ' :zzz: ', $txt);
	$txt = str_replace(' ?( ', ' :-( ', $txt);
	$txt = str_replace(' 8) ', ' :cool: ', $txt);
	$txt = str_replace(' ;( ', ' :\'( ', $txt);
	$txt = str_replace(' 8o ', ' 8-( ', $txt);
	$txt = str_replace(' X( ', ' :#: ', $txt);
	$txt = str_replace(' :P ', ' :-p ', $txt);
	$txt = str_replace(' :grr: ', ' :#: ', $txt);
	$txt = str_replace(' :clown: ', ' :*) ', $txt);
	$txt = str_replace(' :rollaugen: ', ' :rolleyes: ', $txt);

	return $txt;
}


if ($step == 1)
{
	UnbBeginHTML($UNB_T['installation']);
	UteShowAll();

	echo '<h1>' . $UNB_T['installation'] . '</h1>';

	echo '<form action="' . UnbLink('@this', 'step=2', true) . '" method="post">';
	echo '<div class="p">Please enter the database name and table prefix of the wBB\'s tables. This database must be located on the primary database server and be accessible with the same username and password.</div>';

	echo '<div class="p"><table cellspacing="0" cellpadding="0" class="installation">';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db name'] . ':</td><td><input type="text" name="dbname" value="' . t2i($UNB['Db']->dbname) . '" size="20" style="width: 20em;" /></td></tr>';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db prefix'] . ':</td><td><input type="text" name="tblprefix" value="" size="20" style="width: 20em;" /><div class="subtitle">Example: for table names like "bb_test_posts", enter "bb_test_"</div></td></tr>';
	echo '<tr><td class="leftcol">Path to avatar files:</td><td><input type="text" name="avpath" value="" size="40" style="width: 30em;" /></td></tr>';
	echo '</table></div>';

	echo '<div class="p"><b>Note:</b> The import process is split up into several steps that are linked with HTML forwards. Each step may take a considerable amount of time. Please don\'t stop the browser, reload the page or click on any of the links as long as the page is still loading or the installation will fail. (You can restart the entire thing again then.)</div>';

	echo '<div class="p"><input type="submit" class="button" value="Continue" /></div>';
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

	$UNB['Db']->SetTable('Forums');
	$arr = $db0->FastQueryArray('boards');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['boardid'],
							 'Sort' => $a['sort'],
							 'Parent' => $a['boardparentid'],
							 'Name' => UnbImportEncode(transl_bbc($a['boardname'])),
							 'Flags' => $a['isboard'] ? 0 : 1,
							 'Description' => UnbImportEncode(transl_bbc($a['descriptiontext']))
			)) or die('[forums] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ THREADS

	$UNB['Db']->SetTable('Threads');
	$arr = $db0->FastQueryArray('threads');
	if ($arr) foreach($arr as $a)
	{
		if (!$a['ptimeout'])   // number of days, 0 = infinite
			$timeout = 0;
		else
			$timeout = $a['starttime'] + $a['ptimeout'] * 24 * 3600;

		$UNB['Db']->AddRecord(array('ID' => $a['threadid'],
							 'Forum' => $a['boardparentid'],
							 'LastPostDate' => 0,  #$a['timelastreply'],  // this is not always correct, we do it ourselves later
							 'Subject' => UnbImportEncode(transl_bbc($a['threadname'])),
							 'Date' => $a['starttime'],
							 'User' => $a['authorid'],
							 'UserName' => '',
							 'Views' => $a['views'],
							 'Options' => ($a['flags'] & 1) * 1 + $a['important'] * 2 + ($a['pquestion'] != '' ? 1 : 0) * 4,
										  // closed (1)            important (2)         poll (4)
							 'Question' => UnbImportEncode($a['pquestion']),
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
		$UNB['Db']->AddRecord(array('ID' => $a['postid'],
							 'Thread' => $a['threadparentid'],
							 'ReplyTo' => 0,
							 'Date' => $a['posttime'],
							 'EditUser' => $a['editorid'],
							 'EditDate' => $a['edittime'],
							 'EditCount' => ($a['edittime'] > 0) ? 1 : 0,
							 'User' => $a['userid'],
							 'UserName' => '',
							 'Subject' => UnbImportEncode(transl_bbc($a['posttopic'])),
							 'Msg' => UnbImportEncode(transl_bbc($a['message'])),
							 'Options' => $a['disable_smilies'] * 1,
							 'IP' => $a['ip'],
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
	function month_val($month)
	{
		switch ($month)
		{
			case '': return 0;
			case 'Januar': return 1;
			case 'Februar': return 2;
			case "M\xE4rz": return 3;   // &auml;
			case 'April': return 4;
			case 'Mai': return 5;
			case 'Juni': return 6;
			case 'Juli': return 7;
			case 'August': return 8;
			case 'September': return 9;
			case 'Oktober': return 10;
			case 'November': return 11;
			case 'Dezember': return 12;
		}
		return 0;
	}

	// [nodoc]
	function gender_to_char($gender)
	{
		switch ($gender)
		{
			case 0: return '';
			case 1: return 'm';
			case 2: return 'f';
		}
	}

	// [nodoc]
	function avatar_file($id, $user)
	{
		if (!$id) return '';   // no avatar set for this user

		global $db0, $UNB, $av_in_path;

		$record = $db0->FastQuery("avatars", "*", "id=$id");

		$name0 = 'avatar-' . $id . '.' . $record['extension'];
		$name1 = 'avatar_' . $user . '.' . $record['extension'];

		@copy($av_in_path . $name0, $UNB['AvatarPath'] . $name1);

		return $name1;
	}

	$arr = $db0->FastQueryArray('user_table');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['userid'],
							 'Name' => UnbImportEncode($a['username']),
							 'Password' => $a['userpassword'],
							 'RegDate' => $a['regdate'],
							 'RegEMail' => $a['regemail'],
							 'DefaultNotify' => 0,
							 'EMail' => $a['useremail'],
							 'ICQ' => UnbImportEncode($a['usericq']),
							 'AIM' => UnbImportEncode($a['aim']),
							 'YIM' => UnbImportEncode($a['yim']),
							 'LastActivity' => $a['lastactivity'],
							 'LastForum' => 0,
							 'Signature' => UnbImportEncode(transl_bbc($a['signatur'])),
							 'BirthDay' => $a['age_d'],
							 'BirthMonth' => month_val($a['age_m']),
							 'BirthYear' => $a['age_y'],
							 'About' => UnbImportEncode($a['usertext']),
		                     'Title' => '',
							 'Location' => UnbImportEncode($a['location']),
							 'Homepage' => $a['userhp'],
							 'Gender' => gender_to_char($a['gender']),
							 'Avatar' => avatar_file($a['avatarid'], $a['userid']),
							 'ValidateKey' => '',
							 'RecommendedBy' => '',
							 'Design' => -1,
							 'ThreadIcons' => 0,
							 'Flags' => UNB_USER_AUTOLOGIN
			), 'Users') or die('[users] add error: ' . $UNB['Db']->LastError());

		if (!$a['blocked'])
		{
			$status = group_to_status($a['groupid']);
			if ($status > 0)
				$UNB['Db']->AddRecord(array('User' => $a['userid'],
									 'Group' => UNB_GROUP_MEMBERS
					), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
			if ($status > 1)
				$UNB['Db']->AddRecord(array('User' => $a['userid'],
									 'Group' => $status
					), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
		}
	}

	// ------------------------------------------------------------ POLL VOTES

	$UNB['Db']->SetTable('PollVotes');
	$arr = $db0->FastQueryArray('poll');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['id'],
							 'Thread' => $a['threadid'],
							 'Sort' => 0,
							 'Title' => UnbImportEncode($a['field']),
							 'Votes' => $a['votes']
			)) or die('[poll votes] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ POLL USERS

	$UNB['Db']->SetTable('PollUsers');
	$arr = $db0->FastQueryArray('vote');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Thread' => $a['threadid'],
							 'User' => $a['userid'],
							 'VoteNum' => 0
			)) or die('[poll users] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ ANNOUNCES

	$UNB['Db']->SetTable('Announces');
	$arr = $db0->FastQueryArray('announcements');
	if ($arr) foreach($arr as $a)
	{
		if ($a['endtime'] <= time()) continue;

		$UNB['Db']->AddRecord(array('ID' => $a['announcementid'],
							 'Forum' => $a['boardid'],
							 'Date' => $a['starttime'],
							 'User' => $a['userid'],
							 'Subject' => UnbImportEncode(transl_bbc($a['topic'])),
							 'Msg' => UnbImportEncode(transl_bbc($a['message']))
			)) or die('[announces] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ STAT

	$UNB['Db']->SetTable('Stat');
	$arr = $db0->FastQueryArray('stat');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Date' => $a['time'],
							 'NewThreads' => $a['newthreads'],
							 'NewPosts' => $a['newposts'],
							 'OnlineUsers' => $a['memberson'],
							 'OnlineGuests' => 0,
							 'NewUsers' => $a['newregs']
			)) or die('[stat] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=5'));
}

if ($step == 5)
{
	// ------------------------------------------------------------ CONFIG

	$a = $db0->FastQuery('config');
	$UNB['ConfigFile']['forum_title'] = $a['master_board_name'];
	$UNB['ConfigFile']['home_url'] = $a['php_path'];
	$UNB['ConfigFile']['smtp_server'] = 'localhost';
	$UNB['ConfigFile']['smtp_sender'] = $a['master_email'];
	$UNB['ConfigFile']['threads_per_page'] = $a['tproseite'];
	$UNB['ConfigFile']['posts_per_page'] = $a['eproseite'];
	$UNB['ConfigFile']['avatars_enabled'] = $a['avatars'];
	$UNB['ConfigFile']['avatar_x'] = $a['avatar_width'];
	$UNB['ConfigFile']['avatar_y'] =	$a['avatar_height'];
	$UNB['ConfigFile']['avatar_bytes'] =	$a['avatar_size'];

	if (!UnbRebuildConffile()) die($UNB_T['error.write conffile']);

	UnbForwardHTML(UnbLink('@this', 'step=6'));
}

if ($step == 6)
{
	UnbBeginHTML($UNB_T['installation']);
	UteShowAll();

	echo '<h1>' . $UNB_T['installation'] . '</h1>';

	echo '<div class="p">';
	echo 'Conversion finished. Checking database integrity...<br />';

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
