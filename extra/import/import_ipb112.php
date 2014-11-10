<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// import_ipb11.php
// Database import module
//
// TYPE: Invision Power Board 1.1.2
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
$UNB['ThisPage'] = 'import_ipb112.php';

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
function my_strtotime($data)
{
	global $INFO;
	$format = $INFO['clock_long'];   // default: 'M j Y, h:i A' = 'Jul 31 2004, 09:13 PM'

	$mon_length = array(0, 7, 8, 5, 5, 3, 4, 4, 6, 9, 7, 8, 8);

	$pm = false;
	$h12 = false;
	$sec = 0;
	$min = 0;
	$hour = 0;
	$day = 0;
	$month = 0;
	$year = 0;

	for ($fpos = 0, $dpos = 0; $fpos < strlen($format) && $dpos < strlen($data); $fpos++)
	{
		switch ($format{$fpos})
		{
			case 'a':
				$pm = substr($data, $dpos, 2) == 'pm';
				$dpos += 2;
				break;
			case 'A':
				$pm = substr($data, $dpos, 2) == 'PM';
				$dpos += 2;
				break;
			case 'B':
				$dpos += 3;
				break;
			case 'c':
				$dpos += 25;   // TODO: parse this one
				break;
			case 'd':
				$day = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'D':
				$dpos += 3;
				break;
			case 'F':
				$month = array_search(substr($data, $dpos, 3), array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'));
				$dpos += $mon_length[$month];
				break;
			case 'g':
				$hour = intval(substr($data, $dpos, 2));
				$h12 = true;
				$dpos += ($hour < 10 ? 1 : 2);
				break;
			case 'G':
				$hour = intval(substr($data, $dpos, 2));
				$dpos += ($hour < 10 ? 1 : 2);
				break;
			case 'h':
				$hour = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'H':
				$hour = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'i':
				$min = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'I':
				$dpos++;
				break;
			case 'j':
				$day = intval(substr($data, $dpos, 2));
				$dpos += ($day < 10 ? 1 : 2);
				break;
			case 'l':
				while ($dpos < strlen($data) && preg_match('/[a-z]/i', $data{$dpos})) $dpos++;
				break;
			case 'L':
				$dpos++;
				break;
			case 'm':
				$month = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'M':
				$month = array_search(substr($data, $dpos, 3), array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'));
				$dpos += 3;
				break;
			case 'n':
				$month = intval(substr($data, $dpos, 2));
				$dpos += ($month < 10 ? 1 : 2);
				break;
			case 'O':
				$dpos += 5;
				break;
			case 'r':
				// TODO: Parse RFC 2822 formatted date, ex: Thu, 21 Dec 2000 16:01:07 +0200
				break;
			case 's':
				$sec = intval(substr($data, $dpos, 2));
				$dpos += 2;
				break;
			case 'S':
				$dpos += 2;
				break;
			case 't':
				$dpos += 2;
				break;
			case 'T':
				$dpos += 3;
				break;
			case 'U':
				// TODO: Parse UNIX seconds
				break;
			case 'w':
				$dpos++;
				break;
			case 'W':
				// TODO: Skip 1 or 2 characters? (This is the ISO week number of year)
				break;
			case 'Y':
				$year = intval(substr($data, $dpos, 4));
				$dpos += 4;
				break;
			case 'y':
				$year = intval(substr($data, $dpos, 2));
				if ($year < 80) $year += 1900; else $year += 2000;
				$dpos += 2;
				break;
			case 'z':
				// TODO: Skip 1 to 3 characters? (This is the day of the year)
				break;
			case 'Z':
				// TODO: Skip 1 to 6 characters? (This is the timezone offset in seconds)
				break;

			default:
				$dpos++;
		}
	}

	if ($h12) $hour += ($pm ? 12 : 0);

	return mktime($hour, $min, $sec, $month, $day, $year);
}

// [nodoc]
// convert BBCode and pt font sizes to pixel sizes (px)
//
function transl_bbc($txt, $smilies = true)
{
	$txt = str_replace("\r", '', $txt);
	$txt = str_replace("\n", '', $txt);

	$txt = str_replace('<br>', "\n", $txt);
	$txt = str_replace('<b>', '[b]', $txt);
	$txt = str_replace('</b>', '[/b]', $txt);
	$txt = str_replace('<i>', '[i]', $txt);
	$txt = str_replace('</i>', '[/i]', $txt);
	$txt = str_replace('<u>', '[u]', $txt);
	$txt = str_replace('</u>', '[/u]', $txt);

	$txt = str_replace('<ul>', '[list]', $txt);
	$txt = str_replace('</ul>', '[/list]', $txt);
	$txt = str_replace('<li>', '[*]', $txt);

	$txt = preg_replace('/<span style=\'font-family:(.*?);?\'>/i', '[font=$1]', $txt);
	$txt = preg_replace('/<span style=\'font-size:(.*?)pt;?.*?\'>/i', '[size=$1]', $txt);
	$txt = preg_replace('/<span style=\'color:(.*?);?\'>/i', '[color=$1]', $txt);
	$txt = preg_replace('/<\/?span[^>]*>/i', "\x01", $txt);

	// Convert pt to px
	$txt = str_replace('[size=21]', '[size=28]', $txt);
	$txt = str_replace('[size=17]', '[size=23]', $txt);
	$txt = str_replace('[size=16]', '[size=21]', $txt);
	$txt = str_replace('[size=15]', '[size=20]', $txt);
	$txt = str_replace('[size=14]', '[size=19]', $txt);
	$txt = str_replace('[size=13]', '[size=17]', $txt);
	$txt = str_replace('[size=12]', '[size=16]', $txt);
	$txt = str_replace('[size=11]', '[size=15]', $txt);
	$txt = str_replace('[size=10]', '[size=13]', $txt);
	$txt = str_replace('[size=9]', '[size=12]', $txt);
	$txt = str_replace('[size=8]', '[size=11]', $txt);
	$txt = str_replace('[size=7]', '[size=9]', $txt);
	$txt = str_replace('[size=6]', '[size=8]', $txt);
	$txt = str_replace('[size=5]', '[size=7]', $txt);

	// resolve all closing </span>s now
	$stack = array();
	for ($pos = 0; $pos < strlen($txt); $pos++)
	{
		if (substr($txt, $pos, 6) == '[font=') array_push($stack, '[/font]');
		elseif (substr($txt, $pos, 6) == '[size=') array_push($stack, '[/size]');
		elseif (substr($txt, $pos, 7) == '[color=') array_push($stack, '[/color]');
		elseif (substr($txt, $pos, 1) == "\x01") $txt = substr($txt, 0, $pos) . array_pop($stack) . substr($txt, $pos + 1);
	}

	$txt = preg_replace('/<\/?table[^>]*>/i', '', $txt);
	$txt = preg_replace('/<\/?tr[^>]*>/i', '', $txt);
	$txt = preg_replace('/<\/?td[^>]*>/i', '', $txt);

	$txt = preg_replace('/<!--QuoteBegin-->.*?<!--QuoteEBegin-->/i', '[quote]', $txt);
	$txt = preg_replace('/<!--QuoteBegin--(.*?)\+(.*?)-->.*?<!--QuoteEBegin-->/ie', '"[quote=$1:" . my_strtotime("$2") . "]"', $txt);
	$txt = preg_replace('/<!--QuoteEnd-->.*?<!--QuoteEEnd-->/i', '[/quote]', $txt);

	$txt = preg_replace('/<!--c1-->.*?<!--ec1-->/i', '[code]', $txt);
	$txt = preg_replace('/<!--c2-->.*?<!--ec2-->/i', '[/code]', $txt);

	$txt = preg_replace('/<!--emo&(.*?)-->.*<!--endemo-->/i', '$1', $txt);

	$txt = preg_replace('/<a href=\'(.*?)\'(?: target=\'_blank\')?>(.*?)<\/a>/i', '[url=$1]$2[/url]', $txt);
	$txt = preg_replace('/<a href=\'mailto:(.*?)\'>(.*?)<\/a>/i', '[mail=$1]$2[/mail]', $txt);
	$txt = preg_replace('/<img src=\'(.*?)\'[^>]*>/i', '[img]$1[/img]', $txt);

	$txt = str_replace('&gt;', '>', $txt);
	$txt = str_replace('&lt;', '<', $txt);
	$txt = str_replace('&nbsp;', ' ', $txt);
	$txt = str_replace('&quot;', '"', $txt);
	$txt = preg_replace('/&#(\\d+);/e', 'chr(intval("$1"))', $txt);
		// NOTE: Unicode characters would get lost here, but they're not displayed correctly on IPB anyway.
	$txt = str_replace('&amp;', '&', $txt);

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

	return trim($txt);
}

// [nodoc]
function transl_lang($lang)
{
	return $lang;
}


if ($step == 1)
{
	UnbBeginHTML($UNB_T['installation']);
	UteShowAll();

	echo '<h1>' . $UNB_T['installation'] . '</h1>';

	echo '<form action="' . UnbLink('@this', 'step=2', true) . '" method="post">';
	echo '<div class="p">Please enter the database name and table prefix of the IPB\'s tables. This database must be located on the primary database server and be accessible with the same username and password.</div>';

	echo '<div class="p"><table cellspacing="0" cellpadding="0" class="installation">';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db name'] . ':</td><td><input type="text" name="dbname" value="' . t2i($UNB['Db']->dbname) . '" size="20" style="width: 20em;" /></td></tr>';
	echo '<tr><td class="leftcol">' . $UNB_T['inst.db prefix'] . ':</td><td><input type="text" name="tblprefix" value="ibf_" size="20" style="width: 20em;" /><div class="subtitle">Example: for table names like "ibf_posts", enter "ibf_"</div></td></tr>';
	echo '<tr><td class="leftcol">Path to board root:</td><td><input type="text" name="rootpath" value="" size="40" style="width: 30em;" /></td></tr>';
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
	$rootpath = TrailingSlash($_REQUEST['rootpath']);   // path to current avatar files (with "/")
	$_SESSION['rootpath'] = $rootpath;
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

	$av_in_path = trim($_SESSION['import_avpath']);   // path to current avatar files (with "/")
	$rootpath = trim($_SESSION['rootpath']);
	if (preg_match('_^http:|^ftp:_i', $rootpath)) die('<b>UNB error:</b> Root path must not be a remote URL.');

	include($rootpath . 'conf_global.php');
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
	$fmax = $db0->FastQuery1st('forums', 'MAX(id)');

	$UNB['Db']->SetTable('Forums');
	$arr = $db0->FastQueryArray('categories');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['id'] + $fmax + 1,
							 'Sort' => $a['position'],
							 'Parent' => 0,
							 'Name' => UnbImportEncode(transl_bbc($a['name'])),
							 'Description' => UnbImportEncode(transl_bbc($a['description'])),
							 'Flags' => 1
			)) or die('[forums] add error: ' . $UNB['Db']->LastError());
	}

	$arr = $db0->FastQueryArray('forums');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('ID' => $a['id'],
							 'Sort' => $a['position'],
							 'Parent' => $a['category'] + $fmax + 1,
							 'Name' => UnbImportEncode(transl_bbc($a['name'])),
							 'Description' => UnbImportEncode(transl_bbc($a['description'])),
							 'Flags' => 0
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
		$vote = ($a['poll_state'] != '0');
		if ($vote)
		{
			$vrec = $db0->FastQuery('polls', '*', 'tid=' . $a['tid']);
			$question = $vrec['poll_question'];
		}

		$options = 0;
		if ($a['state'] == 'closed') $options |= 1;   // closed
		if ($a['moved_to']) $options |= 8;   // moved
		if ($a['pinned']) $options |= 2;   // important
		if ($vote) $options |= 4;   // poll

		if ($a['moved_to'])   // moved
		{
			$question = $a['moved_to'];
			$timeout = time() + 14 * 24 * 3600;   // show "moved" link for 14 days
		}

		$UNB['Db']->AddRecord(array('ID' => $a['tid'],
							 'Forum' => $a['forum_id'],
							 'LastPostDate' => 0,  // we do it ourselves later
							 'Subject' => UnbImportEncode(transl_bbc($a['title'])),
							 'Desc' => UnbImportEncode(transl_bbc($a['description'])),
							 'Date' => $a['start_date'],
							 'User' => max(0, $a['starter_id']),   // must be >= 0
							 'UserName' => UnbImportEncode($a['starter_name']),
							 'Views' => $a['views'],
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
		$afile = '';
		if ($a['attach_id'] != '')
		{
			$ext = substr($a['attach_file'], strrpos($a['attach_file'], '.'));
			$afile = $UNB['LibraryPath'] . 'upload/post_' . $a['pid'] /*. $ext*/;
			// NOTE: upload_dir must be relative to rootpath!
			if (!copy($rootpath . TrailingSlash($INFO['upload_dir']) . $a['attach_id'], $afile))
			{
				echo '<b>Warning:</b> Couldn\'t copy post attachment file ' . $a['attach_id'] . '<br />';
			}
		}

		$UNB['Db']->AddRecord(array('ID' => $a['pid'],
							 'Thread' => $a['topic_id'],
							 'ReplyTo' => 0,
							 'Date' => $a['post_date'],
							 'EditUser' => 0,
							 'EditDate' => $a['edit_time'],
							 'EditCount' => ($a['edit_time'] > 0 ? 1 : 0),
							 'User' => $a['author_id'],
							 'UserName' => UnbImportEncode($a['author_name']),
							 'Subject' => UnbImportEncode(transl_bbc($text['post_title'])),
							 'Msg' => UnbImportEncode(transl_bbc($a['post'], $a['use_emo'])),
							 'Options' => ($a['use_emo'] ? 0 : 1),
							 'IP' => $a['ip_address'],
							 'AttachDLCount' => $a['attach_hits'],
							 'AttachFile' => $afile,
							 'AttachFileName' => UnbImportEncode($a['attach_file']),
							 'Hostname' => '',
							 'SpamRating' => 0
			)) or die('[posts] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=4'));
}

if ($step == 4)
{
	// ------------------------------------------------------------ USERS

	include($rootpath . 'conf_global.php');

	$arr = $db0->FastQueryArray('members');
	if ($arr) foreach($arr as $a)
	{
		if ($a['id'] <= 0) continue;

		$avatar = '';
		if ($a['avatar'] == 'noavatar')
		{
		}
		else if (!strncmp($a['avatar'], 'http:', 5))
		{
			$avatar = $a['avatar'];
		}
		else if (!strncmp($a['avatar'], 'upload:', 7))
		{
			$avatar = substr($a['avatar'], 7);
			if (!copy($rootpath . TrailingSlash($INFO['upload_dir']) . $avatar, $UNB['AvatarPath'] . $avatar))
			{
				echo '<b>Warning:</b> Couldn\'t copy user avatar file ' . $avatar . '<br />';
				$avatar = '';
			}
		}
		else
		{
			$ext = substr($a['avatar'], strrpos($a['avatar'], '.'));
			$avatar = 'avatar_' . $a['id'] . $ext;
			if (!copy($rootpath . TrailingSlash($INFO['html_dir']) . 'avatars/' . $a['avatar'], $UNB['AvatarPath'] . $avatar))
			{
				echo '<b>Warning:</b> Couldn\'t copy user avatar file ' . $a['avatar'] . '<br />';
				$avatar = '';
			}
		}

		$UNB['Db']->AddRecord(array('ID' => $a['id'],
							 'Name' => UnbImportEncode($a['name']),
							 'Password' => $a['password'],
							 'RegDate' => $a['joined'],
							 'RegEMail' => '[' . $a['ip_address'] . ']',
							 'DefaultNotify' => ($a['auto_track'] ? 1 : 0),
							 'EMail' => $a['email'],
							 'ICQ' => UnbImportEncode($a['icq_number']),
							 'AIM' => UnbImportEncode($a['aim_name']),
							 'YIM' => UnbImportEncode($a['yahoo']),
							 'MSN' => UnbImportEncode($a['msnname']),
							 'LastActivity' => $a['last_activity'],
							 'LastForum' => 0,
							 'Signature' => UnbImportEncode(transl_bbc($a['signature'])),
							 'BirthDay' => $a['bday_day'],
							 'BirthMonth' => $a['bday_month'],
							 'BirthYear' => $a['bday_year'],
							 'About' => UnbImportEncode($a['interests']),
							 'Location' => UnbImportEncode($a['location']),
							 'Homepage' => $a['website'],
							 'Avatar' => $avatar,
							 'ValidateKey' => $a['validate_key'],
							 'Flags' => UNB_USER_AUTOLOGIN | ($a['email_full'] ? UNB_USER_POSTCOPY : 0) | ($a['view_avs'] ? 0 : UNB_USER_HIDEAVATARS) | ($a['view_sigs'] ? 0 : UNB_USER_HIDESIGS),
							 'Language' => transl_lang($a['language']),
							 'Timezone' => ($a['time_offset'] == 0 ? 99 : $a['time_offset'] * 4),
							 'TimezoneDS' => $a['dst_in_use']
			), 'Users') or die('[users] add error: ' . $UNB['Db']->LastError());

		$UNB['Db']->AddRecord(array('User' => $a['id'],
							 'Group' => UNB_GROUP_MEMBERS
			), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
		if ($a['mgroup'] == $INFO['admin_group'])
		{
			$UNB['Db']->AddRecord(array('User' => $a['id'],
								 'Group' => UNB_GROUP_ADMINS
				), 'GroupMembers') or die('[group members] add error: ' . $UNB['Db']->LastError());
		}
	}

	// ------------------------------------------------------------ POLL VOTES

	$i = 1;
	$UNB['Db']->SetTable('PollVotes');
	$arr = $db0->FastQueryArray('polls');
	if ($arr) foreach($arr as $a)
	{
		// Each IPB poll contains all vote options, they must be stored in different records now
		$choices = unserialize($a['choices']);

		foreach ($choices as $choice)
		{
			$UNB['Db']->AddRecord(array('ID' => $i++,
								 'Thread' => $a['tid'],
								 'Sort' => $choice[0],
								 'Title' => UnbImportEncode(transl_bbc($choice[1])),
								 'Votes' => $choice[2]
				)) or die('[poll votes] add error: ' . $UNB['Db']->LastError());
		}
	}

	// ------------------------------------------------------------ POLL USERS

	$UNB['Db']->SetTable('PollUsers');
	$arr = $db0->FastQueryArray('voters');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Thread' => $a['tid'],
							 'User' => intval($a['member_id']),
							 'VoteNum' => 0
			)) or die('[poll users] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ THREADWATCH

	$UNB['Db']->SetTable('ThreadWatch');
	$arr = $db0->FastQueryArray('tracker');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Thread' => $a['topic_id'],
							 'User' => intval($a['member_id']),
							 'Mode' => 1   // always notify via e-mail
			)) or die('[thread watch] add error: ' . $UNB['Db']->LastError());
	}

	// ------------------------------------------------------------ FORUMWATCH

	$UNB['Db']->SetTable('ForumWatch');
	$arr = $db0->FastQueryArray('forum_tracker');
	if ($arr) foreach($arr as $a)
	{
		$UNB['Db']->AddRecord(array('Forum' => $a['forum_id'],
							 'User' => intval($a['member_id']),
							 'Mode' => 1   // always notify via e-mail
			)) or die('[thread watch] add error: ' . $UNB['Db']->LastError());
	}

	UnbForwardHTML(UnbLink('@this', 'step=5'));
}

if ($step == 5)
{
	// ------------------------------------------------------------ CONFIG

	$UNB['ConfigFile']['forum_title'] = $INFO['boardname'];

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
