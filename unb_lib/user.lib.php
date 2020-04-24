<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// user.lib.php
// User Library, provides the IUser interface

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('UNB_USER_USERREADPOST', 8);        // (Admin only) Display all notifications and UserRead data
define('UNB_USER_FASTREPLY', 16);          // Enable FastReply
define('UNB_USER_AUTOLOGIN', 32);          // Auto re-login (with Cookies)
#define('UNB_USER_POSTCOPY', 64);          // Include a copy of the post on notification (not yet implemented)
define('UNB_USER_AUTOIGNORE', 128);        // Automatically ignore any unread new topics
#define('UNB_USER_NEWLAYOUT', 256);
define('UNB_USER_HALFSIZEAVATARS', 512);   // Half size avatars if they're more than 50% of globally allowed maximum height
define('UNB_USER_HIDEAVATARS', 1024);      // Hide users' avatars in posts
define('UNB_USER_HIDESIGS', 2048);         // Hide users' signatures in posts
define('UNB_USER_HIDEINLINEIMGS', 4096);   // Hide inline post attachments (images)

// Represents a user and offers methods to operate on it
//
class IUser
{

// -------------------- Public variables --------------------

// -------------------- Private variables --------------------

var $db;
var $finddb;
var $user_online_timeout;
var $ForumFlags = false;
var $ThreadFlags = false;

var $ID = 0;
var $Name = '';
var $Password = '';
var $RegDate = 0;
var $ValidatedEMail = '';
var $DefaultNotify = 0;
var $EMail = '';
var $ICQ = '';
var $AIM = '';
var $YIM = '';
var $MSN = '';
var $Jabber = '';
var $LastActivity = 0;
var $LastForum = 0;
var $LastLogin = 0;
var $LastLogout = 0;
var $Signature = '';
var $BirthDay = 0;
var $BirthMonth = 0;
var $BirthYear = 0;
var $About = '';
var $Title = '';
var $Location = '';
var $Homepage = '';
var $Gender = '';
var $Avatar = '';
var $AvatarX = 0;
var $AvatarY = 0;
var $Photo = '';
var $Extra;
var $ValidateKey = '';
var $Design = '';
var $Flags = 0;
	//    1: unused
	//    2: read-line [old]
	//    4: "hide code/quote" [old]
	//    8: (admin) display all notifications and UserRead data
	//   16: FastReply
	//   32: Auto Re-Login (with Cookies)
	//   64: include a copy of the post on notification
	//  128: Auto-ignore unread topics
	//  256: NEWLAYOUT
	//  512: Half Size Avatars
	// 1024: Hide avatars
	// 2048: Hide signatures
var $Timezone = 0;
	// +/- offset (hours) * 4 (-48...+48)
	// 99: board default
var $TimezoneDS = -1;
	// daylight saving time? (-1 board default, 0 false, 1 auto, 2 force)
var $EditControls = 0;
	// 0: board default
var $ThreadsPerPage = 0;
var $ThreadSort = 0;
var $ThreadTime = 0;
var $Language = '';
	// ISO code (de, en, fr...)
var $DateFormat = '';

// -------------------- Constructor --------------------

function __construct($id = 0)
{
	global $UNB;

	if (PHP5) eval('$this->db = clone $UNB["Db"];');
	else      $this->db = $UNB['Db'];

	if ($UNB['ProfileExtraCount'] > 0)
		$this->Extra = array_fill(1, $UNB['ProfileExtraCount'], '');
	else
		$this->Extra = array();

	// compare with users.inc.php:ListOnlineUsers() and main.inc.php:ShowOnlineUsers()
	$this->user_online_timeout = 300;
	if (rc('user_online_timeout')) $this->user_online_timeout = rc('user_online_timeout');

	if ($id !== 0) $this->Load($id);
}

function Reset()
{
	$this->ID = 0;
	$this->Name = '';
	$this->Password = '';
	$this->RegDate = 0;
	$this->ValidatedEMail = '';
	$this->DefaultNotify = 0;
	$this->EMail = '';
	$this->ICQ = '';
	$this->AIM = '';
	$this->YIM = '';
	$this->MSN = '';
	$this->Jabber = '';
	$this->LastActivity = 0;
	$this->LastForum = 0;
	$this->LastLogin = 0;
	$this->LastLogout = 0;
	$this->Signature = '';
	$this->BirthDay = 0;
	$this->BirthMonth = 0;
	$this->BirthYear = 0;
	$this->About = '';
	$this->Title = '';
	$this->Location = '';
	$this->Homepage = '';
	$this->Gender = '';
	$this->Avatar = '';
	$this->AvatarX = 0;
	$this->AvatarY = 0;
	$this->Photo = '';
	$this->Extra = array();
	$this->ValidateKey = '';
	$this->Design = '';
	$this->Flags = 0;
	$this->Timezone = 0;
	$this->TimezoneDS = 0;
	$this->EditControls = 0;
	$this->ThreadsPerPage = 0;
	$this->ThreadSort = 0;
	$this->ThreadTime = 0;
	$this->Language = '';
	$this->DateFormat = '';
}

// -------------------- Find --------------------

// Query a list of some/all users
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
//
// returns (bool) success
//
function GetList($where = '', $order = '', $limit = '')
{
	if (PHP5) eval('$this->finddb = clone $this->db;');
	else      $this->finddb = $this->db;

	$record = $this->finddb->FastQuery('Users', '*', $where, $order, $limit);
	return $this->LoadFromRecord($record);
}

// Find next user from previous search
//
// returns (bool) success
//
function GetNext()
{
	$record = $this->finddb->GetRecord();
	return $this->LoadFromRecord($record);
}

// Get an array(ID) of UserIDs matching the query
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
//
// returns (array(array)) all user record rows
//
function GetListArray($where = '', $order = '', $limit = '')
{
	return $this->db->FastQuery1stArray('Users', 'ID', $where, $order, $limit);
}

// Find a user by name
//
// returns (int) user id or 0
//
function FindByName($name)
{
	$name = UnbDbEncode($name);
	if ($this->GetList("Name LIKE '" . $name . "'"))
	{
		return $this->GetID();
	}
	elseif (is_numeric($name) && $this->GetList('ID=' . $name))
	{
		return $this->GetID();
	}
	return 0;
}

// Find a user by e-mail
// NOTE: The returned user may not be unique. This is mainly good for e-mail address reuse checking
//
// returns (int) user id or 0
//
function FindByEMail($email)
{
	$email = UnbDbEncode($email);
	if ($this->GetList("EMail LIKE '" . $email . "'"))
	{
		return $this->GetID();
	}
	return 0;
}

// Count users
//
// in where = (string) SQL WHERE section
//
function Count($where = '')
{
	return $this->db->FastQuery1st('Users', 'count(*)', $where);
}

// Like Count(), but filters by UserGroups, too
//
// in where = (string) SQL WHERE section
// in group = (int) group id the user must be member of
// in invgroup = (bool) user must not be member of above group id (inverse behaviour)
//
function CountWithGroup($where = '', $group = 0, $invgroup = false)
{
	// Clean parameters
	$group = intval($group);
	if (!$group) return $this->Count($where);

	$table = array(array('', 'Users', 'u', ''));

	if ($group == -1)
	{
		$table[] = array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User');
		$where .= ($where ? ' AND ' : '') . 'gm.Group IS ' . ($invgroup ? 'NOT ' : '') . 'NULL ';
	}
	elseif ($group > 0 && !$invgroup)
	{
		$table[] = array('INNER', 'GroupMembers', 'gm', 'u.ID = gm.User AND gm.Group = ' . $group);
	}
	elseif ($group > 0 && $invgroup)
	{
		$table[] = array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User AND gm.Group = ' . $group);
		$where .= ($where ? ' AND ' : '') . 'gm.Group IS NULL';
	}

	$a = $this->db->FastQuery1st($table, 'COUNT(DISTINCT u.ID)', $where);
	if ($a === false) $a = 0;
	return $a;
}

// -------------------- Read access --------------------

// Load a user into this object. Uses the global user records cache.
//
// in id = (int) user id to load
//
// returns (bool) success
//
function Load($id)
{
	// Clean parameters
	$id = intval($id);
	if (!$id)
	{
		$this->Reset();
		return false;
	}
	global $UNB;

	if (array_key_exists($id, $UNB['UserCache']))
	{
		$record = $UNB['UserCache'][$id];
		$add = false;
	}
	else
	{
		$record = $this->db->FastQuery('Users', '*', 'ID=' . $id);
		$add = true;
	}

	return $this->LoadFromRecord($record, $add);
}

// Load a user from a given record into this object.
// Can load results from array find functions.
//
// in record = (array) user record row
// in add = (bool) update the global user records cache
//
// returns (bool) success
//
function LoadFromRecord($record, $add = false)
{
	global $UNB;

	if ($record)
	{
		$this->ID = intval($record['ID']);
		$this->Name = strval($record['Name']);
		$this->Password = strval($record['Password']);
		$this->RegDate = intval($record['RegDate']);
		$this->ValidatedEMail = strval($record['ValidatedEMail']);
		$this->DefaultNotify = intval($record['DefaultNotify']);
		$this->EMail = strval($record['EMail']);
		$this->ICQ = strval($record['ICQ']);
		$this->AIM = strval($record['AIM']);
		$this->YIM = strval($record['YIM']);
		$this->MSN = strval($record['MSN']);
		$this->Jabber = strval($record['Jabber']);
		$this->LastActivity = intval($record['LastActivity']);
		$this->LastForum = intval($record['LastForum']);
		$this->LastLogin = intval($record['LastLogin']);
		$this->LastLogout = intval($record['LastLogout']);
		$this->Signature = strval($record['Signature']);
		$this->BirthDay = intval($record['BirthDay']);
		$this->BirthMonth = intval($record['BirthMonth']);
		$this->BirthYear = intval($record['BirthYear']);
		$this->About = strval($record['About']);
		$this->Title = strval($record['Title']);
		$this->Location = strval($record['Location']);
		$this->Homepage = strval($record['Homepage']);
		$this->Gender = strval($record['Gender']);
		$this->Avatar = strval($record['Avatar']);
		$this->AvatarX = strval($record['AvatarX']);
		$this->AvatarY = strval($record['AvatarY']);
		$this->Photo = strval($record['Photo']);
		for ($n = 1; $n <= $UNB['ProfileExtraCount']; $n++)
			$this->Extra[$n] = strval($record['Extra' . $n]);
		$this->ValidateKey = strval($record['ValidateKey']);
		$this->Design = strval($record['Design']);
		$this->Flags = intval($record['Flags']);
		$this->Timezone = intval($record['Timezone']);
		$this->TimezoneDS = intval($record['TimezoneDS']);
		$this->EditControls = intval($record['EditControls']);
		$this->ThreadsPerPage = intval($record['ThreadsPerPage']);
		$this->ThreadSort = intval($record['ThreadSort']);
		$this->ThreadTime = intval($record['ThreadTime']);
		$this->Language = strval($record['Language']);
		$this->DateFormat = strval($record['DateFormat']);

		if ($add) $UNB['UserCache'][$this->ID] = $record;

		return true;
	}
	else
	{
		$this->Reset();
		return false;
	}
}

// Get this object's user id
//
function GetID()
{
	return $this->ID;
}

// Get this object's user name
//
function GetName($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Name;
}

// Get this object's hashed password
//
function GetPassword($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Password;
}

// Get this object's registration timestamp
//
function GetRegDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->RegDate;
}

// Get this object's e-mail address used for registration
//
function GetValidatedEMail($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ValidatedEMail;
}

// Get this object's default notification method
//
function GetDefaultNotify($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->DefaultNotify;
}

// Get this object's current e-mail address
//
function GetEMail($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EMail;
}

// Get this object's ICQ number
//
function GetICQ($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ICQ;
}

// Get this object's AIM id
//
function GetAIM($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AIM;
}

// Get this object's YIM id
//
function GetYIM($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->YIM;
}

// Get this object's MSN id
//
function GetMSN($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->MSN;
}

// Get this object's Jabber id
//
function GetJabber($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Jabber;
}

// Get this object's last activity timestamp
//
function GetLastActivity($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->LastActivity;
}

// Get this object's last forum id
//
function GetLastForum($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->LastForum;
}

// Get this object's last login timestmp
//
function GetLastLogin($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->LastLogin;
}

// Get this object's last logout timestamp
//
function GetLastLogout($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;

	$time = $this->LastLogout;
	if (!$time) $time = $this->LastLogin;
	if (!$time) $time = time();

	return $time;
}

// Get this object's post signature
//
function GetSignature($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Signature;
}

// Get this object's birthdate day
//
function GetBirthDay($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->BirthDay;
}

// Get this object's birthdate month
//
function GetBirthMonth($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->BirthMonth;
}

// Get this object's birthdate year
//
function GetBirthYear($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->BirthYear;
}

// Get this object's short user self-description
//
function GetAbout($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->About;
}

// Get this object's user title
//
function GetTitle($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Title;
}

// Get this object's user location
//
function GetLocation($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Location;
}

// Get this object's homepage URL
//
function GetHomepage($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Homepage;
}

// Get this object's gender
//
function GetGender($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Gender;
}

// Get this object's avatar image filename/URL
//
function GetAvatar($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Avatar;
}

// Get this object's avatar image width
//
function GetAvatarX($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AvatarX;
}

// Get this object's avatar image height
//
function GetAvatarY($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AvatarY;
}

// Get this object's user photo filename/URL
//
function GetPhoto($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Photo;
}

// Get this object's extra profile field value
//
function GetExtra($n, $id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Extra[$n];
}

// Get this object's validate key
//
function GetValidateKey($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ValidateKey;
}

// Get this object's currently selected board design
//
function GetDesign($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Design;
}

// Get this object's flags/options
//
function GetFlags($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Flags;
}

// Get this object's timezone
//
function GetTimezone($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Timezone;
}

// Get this object's "respect DST setting" flag
//
function GetTimezoneDS($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->TimezoneDS;
}

// Get this object's BBCode buttons configuration
//
function GetEditControls($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EditControls;
}

// Get this object's threads per page setting
//
function GetThreadsPerPage($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ThreadsPerPage;
}

// Get this object's default thread sort
//
function GetThreadSort($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ThreadSort;
}

// Get this object's default thread time filter
//
function GetThreadTime($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ThreadTime;
}

// Get this object's currently selected language
//
function GetLanguage($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Language;
}

// Get this object's date format
//
function GetDateFormat($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->DateFormat;
}

// Get this object's birthdate timestamp
//
// FIXME: what happens to years before 1970 on Windows and before 19xx otherwise?
//
function GetBirthdate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return mktime(0, 0, 0, $this->BirthMonth, $this->BirthDay, $this->BirthYear);
}

// Get this object's online status (active within the last 5min)
//
function GetOnline($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return (time() - $this->LastActivity <= $this->user_online_timeout);    // user did something within the last n seconds
}

// Get this object's verbose gender
//
function GetGenderVerbose($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return UnbGetGenderVerbose($this->Gender);
}

// Get this object's remote avatar flag
//
function AvatarFromURL($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return (strpos($this->Avatar, ':') || $this->Avatar[0] == '/');
}

// Get this object's remote photo flag
//
function PhotoFromURL($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return (strpos($this->Photo, ':') || $this->Photo[0] == '/');
}

// -------------------- Write access --------------------

// Add a new user to the database
//
// in Name = (string) user name
// in Password = (string) plaintext password
// in EMail = (string) registration e-mail address
//
// returns (bool) success
//
function Add($Name, $Password, $EMail)
{
	global $UNB;

	// Clean parameters
	$Name = trim(strval($Name));
	$Password = trim(strval($Password));
	$EMail = trim(strval($EMail));

	$max = intval($this->db->FastQuery1st('Users', 'MAX(ID)'));

	$this->ID = $max + 1;
	$this->Name = $Name;
	$this->Password = UnbCreateUserPassword($Password);
	$this->RegDate = time();
	$this->ValidatedEMail = '';
	$this->DefaultNotify = 0;
	$this->EMail = $EMail;
	$this->ICQ = '';
	$this->AIM = '';
	$this->YIM = '';
	$this->MSN = '';
	$this->Jabber = '';
	$this->LastActivity = 0;
	$this->LastForum = 0;
	$this->LastLogin = 0;
	$this->LastLogout = 0;
	$this->Signature = '';
	$this->BirthDay = 0;
	$this->BirthMonth = 0;
	$this->BirthYear = 0;
	$this->About = '';
	$this->Title = '';
	$this->Location = '';
	$this->Homepage = '';
	$this->Gender = '';
	$this->Avatar = 'gravatar'; //ANDI
	$this->Photo = '';
	if ($UNB['ProfileExtraCount'] > 0)
		$this->Extra = array_fill(1, $UNB['ProfileExtraCount'], '');
	else
		$this->Extra = array();
	$this->ValidateKey = '';
	$this->Design = '';
	// See if another design than the board's default was selected
	if ($UNB['Design']['CurrentDesign'] != rc('design'))
		$this->Design = $UNB['Design']['CurrentDesign'];
	$this->Flags = UNB_USER_AUTOLOGIN;
	$this->Timezone = 99;
	$this->TimezoneDS = -1;
	$this->EditControls = 0;
	$this->ThreadsPerPage = -1;
	$this->ThreadSort = -1;
	$this->ThreadTime = -1;
	$this->Language = $UNB['Lang'];
	$this->DateFormat = '';

	$add = array(
		'ID' => $this->ID,
		'Name' => $this->Name,
		'Password' => $this->Password,
		'RegDate' => $this->RegDate,
		'ValidatedEMail' => $this->ValidatedEMail,
		'DefaultNotify' => $this->DefaultNotify,
		'EMail' => $this->EMail,
		'ICQ' => $this->ICQ,
		'AIM' => $this->AIM,
		'YIM' => $this->YIM,
		'MSN' => $this->MSN,
		'Jabber' => $this->Jabber,
		'LastActivity' => $this->LastActivity,
		'LastForum' => $this->LastForum,
		'Signature' => $this->Signature,
		'BirthDay' => $this->BirthDay,
		'BirthMonth' => $this->BirthMonth,
		'BirthYear' => $this->BirthYear,
		'About' => $this->About,
		'Title' => $this->Title,
		'Location' => $this->Location,
		'Homepage' => $this->Homepage,
		'Gender' => $this->Gender,
		'Avatar' => $this->Avatar,
		'Photo' => $this->Photo,
		'ValidateKey' => $this->ValidateKey,
		'Design' => $this->Design,
		'Flags' => $this->Flags,
		'Timezone' => $this->Timezone,
		'TimezoneDS' => $this->TimezoneDS,
		'EditControls' => $this->EditControls,
		'ThreadsPerPage' => $this->ThreadsPerPage,
		'ThreadSort' => $this->ThreadSort,
		'ThreadTime' => $this->ThreadTime,
		'Language' => $this->Language,
		'DateFormat' => $this->DateFormat);

	for ($n = 1; $n <= $UNB['ProfileExtraCount']; $n++)
		$add['Extra' . $n] = $this->Extra[$n];

	$ok = $this->db->AddRecord($add, 'Users');

	// update statistics table, no error detection here
	if ($ok) UnbUpdateStat('NewUsers', 1);
	return $ok;
}

// Set user name
//
// in Name = (string) new user name
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetName($Name, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Name = trim(strval($Name));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Name = $Name;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Name' => $Name), 'ID=' . $id, 'Users');
}

// Set user password hash
//
// in Password = (string) new user password hash
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetPassword($Password, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Password = trim(strval($Password));
	$id = intval($id);

	$Password = UnbCreateUserPassword($Password);   // Pass cleartext password here!
	if (!$id)
	{
		$id = $this->ID;
		$this->Password = $Password;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Password' => $Password), 'ID=' . $id, 'Users');
}

// Set default notification method
//
// in DefaultNotify = (string) new default notification method, see UNB_NOTIFY_* constants
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetDefaultNotify($DefaultNotify, $id = 0)
{
	global $UNB;

	// Clean parameters
	$DefaultNotify = intval($DefaultNotify);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->DefaultNotify = intval($DefaultNotify);
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('DefaultNotify' => $DefaultNotify), 'ID=' . $id, 'Users');
}

// Set e-mail address
//
// in EMail = (string) new e-mail address
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetEMail($EMail, $id = 0)
{
	global $UNB;

	// Clean parameters
	$EMail = trim(strval($EMail));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->EMail = $EMail;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('EMail' => $EMail), 'ID=' . $id, 'Users');
}

// Set validated e-mail address
//
// in EMail = (string) new e-mail address
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetValidatedEMail($EMail, $id = 0)
{
	global $UNB;

	// Clean parameters
	$EMail = trim(strval($EMail));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->ValidatedEMail = $EMail;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('ValidatedEMail' => $EMail), 'ID=' . $id, 'Users');
}

// Set IM contact IDs
//
// in ICQ = (string) new ICQ id
// in AIM = (string) new AIM id
// in YIM = (string) new YIM id
// in MSN = (string) new MSN id
// in Jabber = (string) new Jabber id
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetIM($ICQ = null, $AIM = null, $YIM = null, $MSN = null, $Jabber = null, $id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);
	if (isset($ICQ)) $ICQ = trim($ICQ);
	if (isset($AIM)) $AIM = trim($AIM);
	if (isset($YIM)) $YIM = trim($YIM);
	if (isset($MSN)) $MSN = trim($MSN);
	if (isset($Jabber)) $Jabber = trim($Jabber);

	if (!$id)
	{
		$id = $this->ID;
		if (isset($ICQ)) $this->ICQ = $ICQ;
		if (isset($AIM)) $this->AIM = $AIM;
		if (isset($YIM)) $this->YIM = $YIM;
		if (isset($MSN)) $this->MSN = $MSN;
		if (isset($Jabber)) $this->Jabber = $Jabber;
	}
	unset($UNB['UserCache'][$id]);
	$a = array();
	if (isset($ICQ)) $a['ICQ'] = $ICQ;
	if (isset($AIM)) $a['AIM'] = $AIM;
	if (isset($YIM)) $a['YIM'] = $YIM;
	if (isset($MSN)) $a['MSN'] = $MSN;
	if (isset($Jabber)) $a['Jabber'] = $Jabber;
	if (!$a) return true;   // nothing to do
	return $this->db->ChangeRecord($a, 'ID=' . $id, 'Users');
}

// Set last activity forum and timestamp
//
// in forumid = (int) new forum id
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetLastActivity($forumid, $id = 0)
{
	global $UNB;

	// Clean parameters
	$forumid = intval($forumid);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->LastActivity = time();
		$this->LastForum = intval($forumid);
	}

	// generally we don't need to remove the cache in this case, except for who-is-where list
	if ($UNB['ThisPage'] == '@users') unset($UNB['UserCache'][$id]);

	return $this->db->ChangeRecord(array('LastActivity' => time(), 'LastForum' => $forumid), 'ID=' . $id, 'Users');
}

// Set last login timestamp
//
// returns (bool) success
//
function SetLastLogin()
{
	global $UNB;

	$id = $this->ID;
	$this->LastLogout = $this->LastActivity;   // attention: update this BEFORE setting new LastActivity value!
	$this->LastLogin = time();

	unset($UNB['UserCache'][$id]);
	$ok = $this->db->ChangeRecord(array('LastLogin' => $this->LastLogin, 'LastLogout' => $this->LastLogout), 'ID=' . $id, 'Users');

	// update statistics table, no error detection here
	$this->SetLastActivity(0);   // set Active time now, so that Users will be counted correctly for UnbUpdateStat
	if ($ok) UnbUpdateUserStat();
	return $ok;
}

// Set post signature
//
// in Signature = (string) new post signature
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetSignature($Signature, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Signature = trim(strval($Signature));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Signature = $Signature;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Signature' => $Signature), 'ID=' . $id, 'Users');
}

// Set birthdate
//
// in BirthDay = (int) new birthdate day
// in BirthMonth = (int) new birthdate month
// in BirthYear = (int) new birthdate year
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetBirthDate($BirthDay, $BirthMonth, $BirthYear, $id = 0)
{
	global $UNB;

	// Clean parameters
	$BirthDay = intval($BirthDay);
	$BirthMonth = intval($BirthMonth);
	$BirthYear = intval($BirthYear);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->BirthDay = $BirthDay;
		$this->BirthMonth = $BirthMonth;
		$this->BirthYear = $BirthYear;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('BirthDay' => $BirthDay, 'BirthMonth' => $BirthMonth, 'BirthYear' => $BirthYear), 'ID=' . $id, 'Users');
}

// Set description
//
// in About = (string) new user description text
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetAbout($About, $id = 0)
{
	global $UNB;

	// Clean parameters
	$About = trim(strval($About));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->About = $About;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('About' => $About), 'ID=' . $id, 'Users');
}

// Set user title
//
// in Title = (string) new user title
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetTitle($Title, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Title = trim(strval($Title));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Title = $Title;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Title' => $Title), 'ID=' . $id, 'Users');
}

// Set homepage URL
//
// in Homepage = (string) new homepage URL
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetHomepage($Homepage, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Homepage = trim(strval($Homepage));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Homepage = $Homepage;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Homepage' => $Homepage), 'ID=' . $id, 'Users');
}

// Set location
//
// in Location = (string) new location
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetLocation($Location, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Location = trim(strval($Location));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Location = $Location;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Location' => $Location), 'ID=' . $id, 'Users');
}

// Set gender
//
// in Gender = (string) new gender: '', 'm' or 'f'
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetGender($Gender, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Gender = trim(strval($Gender));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Gender = $Gender;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Gender' => $Gender), 'ID=' . $id, 'Users');
}

// Set avatar image and its dimensions
//
// in Avatar = (string) new avatar image
//
// returns (bool) success
//
function SetAvatar($Avatar)
{
	global $UNB;

	if ($Avatar == '' && $this->Avatar == '') return true;

	$id = $this->ID;
	if ($Avatar == '')
	{
		// remove image file from disk, too (if it exists, anyway)
		if (file_exists($UNB['AvatarPath'] . $this->Avatar))
			if (!unlink($UNB['AvatarPath'] . $this->Avatar)) return false;

		$newx = $newy = 0;
	}

	$this->Avatar = $Avatar;
	unset($UNB['UserCache'][$id]);

	if ($Avatar != '' && $Avatar != 'gravatar')
	{
		if (!strpos($Avatar, ':') ||
		    ini_get('allow_url_fopen'))
		{
			$is = getimagesize((strpos($Avatar, ':') ? '' : $UNB['AvatarPath']) . $Avatar);
			if (is_array($is))
			{
				$newx = $is[0];
				$newy = $is[1];
			}
			else
			{
				// cannot check file, set 0 size
				$newx = $newy = 0;
			}
		}
		else
		{
			// cannot check remote file, set 0 size
			$newx = $newy = 0;
		}
	}

	return $this->db->ChangeRecord(array('Avatar' => $Avatar, 'AvatarX' => $newx, 'AvatarY' => $newy), 'ID=' . $id, 'Users');
}

// Set photo image
//
// in Photo = (string) new photo image
//
// returns (bool) success
//
function SetPhoto($Photo)
{
	global $UNB;

	if ($Photo == '' && $this->Photo == '') return true;

	$id = $this->ID;
	if ($Photo == '')
	{
		// remove image file from disk, too (if it exists, anyway)
		if (file_exists($UNB['PhotoPath'] . $this->Photo))
			if (!unlink($UNB['PhotoPath'] . $this->Photo)) return false;
	}

	$this->Photo = $Photo;
	unset($UNB['UserCache'][$id]);

	return $this->db->ChangeRecord(array('Photo' => $Photo), 'ID=' . $id, 'Users');
}

// Set extra profile field value
//
// in n = (int) field number, > 0
// in Extra = (string) new field value
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetExtra($n, $Extra, $id = 0)
{
	global $UNB;

	// Clean parameters
	$n = intval($n);
	$Extra = trim(strval($Extra));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Extra[$n] = $Extra;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Extra' . $n => $Extra), 'ID=' . $id, 'Users');
}

// Set validate key
//
// in ValidateKey = (string) new validate key
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetValidateKey($ValidateKey, $id = 0)
{
	global $UNB;

	// Clean parameters
	$ValidateKey = trim(strval($ValidateKey));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->ValidateKey = $ValidateKey;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('ValidateKey' => $ValidateKey), 'ID=' . $id, 'Users');
}

// Set selected design
//
// in Design = (string) new design name
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetDesign($Design, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Design = trim($Design);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Design = $Design;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Design' => $Design), 'ID=' . $id, 'Users');
}

// Set flags/options
//
// in Flags = (string) new flags/options
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetFlags($Flags, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Flags = intval($Flags);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Flags = $Flags;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Flags' => $Flags), 'ID=' . $id, 'Users');
}

// Set timezone
//
// in Timezone = (int) new timezone, -4=UTC-0100, 0=UTC, 1=UTC+0015, 4=UTC+0100
// in TimezoneDS = (int) respect DST
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetTimezone($Timezone, $TimezoneDS, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Timezone = intval($Timezone);
	$TimezoneDS = intval($TimezoneDS);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Timezone = $Timezone;
		$this->TimezoneDS = $TimezoneDS;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Timezone' => $Timezone, 'TimezoneDS' => $TimezoneDS), 'ID=' . $id, 'Users');
}

// Set BBCode toolbar configuration
//
// in EditControls = (int) new configuration bitmask
// in id = (int) user id, 0: currently loaded user
//
// returns (bool) success
//
function SetEditControls($EditControls, $id = 0)
{
	global $UNB;

	// Clean parameters
	$EditControls = intval($EditControls);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->EditControls = $EditControls;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('EditControls' => $EditControls), 'ID=' . $id, 'Users');
}

// Set default threads per page
//
function SetThreadsPerPage($ThreadsPerPage, $id = 0)
{
	global $UNB;

	// Clean parameters
	$ThreadsPerPage = intval($ThreadsPerPage);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->ThreadsPerPage = $ThreadsPerPage;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('ThreadsPerPage' => $ThreadsPerPage), 'ID=' . $id, 'Users');
}

// Set default thread list sorting
//
function SetThreadSort($ThreadSort, $id = 0)
{
	global $UNB;

	// Clean parameters
	$ThreadSort = intval($ThreadSort);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->ThreadSort = $ThreadSort;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('ThreadSort' => $ThreadSort), 'ID=' . $id, 'Users');
}

// Set default thread list time filter
//
function SetThreadTime($ThreadTime, $id = 0)
{
	global $UNB;

	// Clean parameters
	$ThreadTime = intval($ThreadTime);
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->ThreadTime = $ThreadTime;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('ThreadTime' => $ThreadTime), 'ID=' . $id, 'Users');
}

// Set user's language
//
function SetLanguage($Language, $id = 0)
{
	global $UNB;

	// Clean parameters
	$Language = trim(strval($Language));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->Language = $Language;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('Language' => $Language), 'ID=' . $id, 'Users');
}

// Set custom date format
//
function SetDateFormat($DateFormat, $id = 0)
{
	global $UNB;

	// Clean parameters
	$DateFormat = trim(strval($DateFormat));
	$id = intval($id);

	if (!$id)
	{
		$id = $this->ID;
		$this->DateFormat = $DateFormat;
	}
	unset($UNB['UserCache'][$id]);
	return $this->db->ChangeRecord(array('DateFormat' => $DateFormat), 'ID=' . $id, 'Users');
}

// Remove a user
// Any posts of this user will get the user name copied in statically and
// will be marked as by a former user.
// Cleans up all other tables and removes any user-related data.
//
// in id = (int) user id to remove, 0: currently loaded user
//
// returns (bool) success
//
function Remove($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if ($id) if (!$this->Load($id)) return false;
	$id = $this->ID;

	// a previous Load() could have failed
	if (!$id) return false;

	// noone may delete himself!
	if ($id == $LoginUserID) return false;

	// remove avatar & photo image file
	if ($this->Avatar != '' && file_exists($UNB['AvatarPath'] . $this->Avatar))
		if (!unlink($UNB['AvatarPath'] . $this->Avatar)) return false;
	if ($this->Photo != '' && file_exists($UNB['PhotoPath'] . $this->Photo))
		if (!unlink($UNB['PhotoPath'] . $this->Photo)) return false;

	// remove any records from some tables relating to (this) user
	// UserForumFlags
	$this->RemoveAllForumThreadFlags(null, null, $id);
	// ThreadWatch
	$thread = new IThread;
	$thread->RemoveAllUserWatchs($id);
	// ForumWatch
	$forum = new IForum;
	$forum->RemoveAllUserWatchs($id);
	// PollUsers
	$thread->RemoveAllUserVotes($id);
	// AnnounceRead
	$announce = new IAnnounce;
	$announce->RemoveAllUserReads($id);
	// GroupMembers
	UnbSetUserGroups($id, array());
	// ACL
	$this->db->RemoveRecord('User=' . $id, 'ACL');

	// update posts created by this user
	$post = new IPost;
	if ($post->Find('User=' . $id)) do
	{
		if (!$post->SetUser(-1, $this->Name)) echo 'error setting post user/name<br />';
	}
	while ($post->FindNext());

	// update posts changed by this user
	if ($post->Find('EditUser=' . $id)) do
	{
		if (!$post->SetUser(false, false, 0)) echo 'error setting post edituser<br />';
	}
	while ($post->FindNext());

	// update threads created by this user
	$thread = new IThread;
	if ($thread->Find('User=' . $id)) do
	{
		if (!$thread->SetUser(-1, $this->Name)) echo 'error setting thread user/name<br />';
	}
	while ($thread->FindNext());

	unset($UNB['UserCache'][$id]);
	$ok = $this->db->RemoveRecord('ID=' . $id, 'Users');

	// update statistics table, no error detection here
	if ($ok) UnbUpdateStat('NewUsers', -1);
	return $ok;
}

// Sort all forums by parent relation and sort index.
//
// The resulting array is a flat list of all forums in the exact order as they
// would be displayed on the forums overview page.
//
// in parent = (int) current forum parent to scan
// in,out forder_a = (array) all forums we have collected by now
// in forums_tbl = (array) flat Forums database table listing
//
function build_forder($parent, &$forder_a, $forums_tbl)
{
	foreach ($forums_tbl as $f)
	{
		if ($f['Parent'] == $parent)
		{
			//if (in_array($f['ID'], $forder_a)) continue;   // Loop found! [disabled for performance] TODO: enable it?
			array_push($forder_a, $f['ID']);
			$this->build_forder($f['ID'], $forder_a, $forums_tbl);
		}
	}
}

// Get all ThreadWatchs for (this) user
//
// in id = (int) user id, 0: currently loaded user
// in cond = (string) condition expression the watch mode must match. Example: "& 2", "< 4".
//                    This is used to complete an SQL expression on the Mode column.
//
// returns (array(array(thread id,
//                      watch mode,
//                      last post date,
//                      forum id,
//                      forum name,
//                      thread subject,
//                      last read timestamp)))
//
function GetThreadWatchs($id = 0, $cond = '')
{
	// Clean parameters
	$id = intval($id);

	// Determine global order of forums
	$forums_tbl = $this->db->FastQueryArray('Forums', 'ID, Parent', '', 'Sort, Name');
	if ($forums_tbl === false || $forums_tbl == array()) return array();

	$forder_a = array();
	$this->build_forder(0, $forder_a, $forums_tbl);

	$forder = "CASE f.ID ";
	foreach ($forder_a as $pos => $fid)
	{
		$forder .= 'WHEN ' . $fid . ' THEN ' . $pos . ' ';
	}
	$forder .= 'END';

	if ($cond == '') $cond = '> 0';
	$id = intval($id);
	if (!$id) $id = $this->ID;
	$record = $this->db->FastQuery(
		/*table*/ array(
			array('', 'Threads', 't', ''),
			array('LEFT', 'ThreadWatch', 'tw', 'tw.Thread = t.ID'),
			array('LEFT', 'Forums', 'f', 't.Forum = f.ID')),
		/*fields*/ 'tw.Thread, tw.Mode, t.LastPostDate, t.Forum, t.Subject, tw.LastRead, f.Name',
		/*where*/ 'tw.User = ' . $id . ' AND tw.Mode ' . $cond,
		/*order*/ $forder . ', t.LastPostDate DESC');

	$arr = array();
	if ($record) do
	{
		$record['Mode'] = intval($record['Mode']);
		array_push($arr, $record);
	}
	while ($record = $this->db->GetRecord());
	return $arr;

	//return $this->db->FastQueryArray("ThreadWatch", "Thread, Mode", "User=$id and Mode>0", "Thread");
}

// Get all ForumWatchs for (this) user
//
// in id = (int) user id, 0: currently loaded user
// in cond = (string) condition expression the watch mode must match. Example: "& 2", "< 4".
//                    This is used to complete an SQL expression on the Mode column.
//
// returns (array(array(forum id,
//                      forum name,
//                      watch mode)))
//
function GetForumWatchs($id = 0, $cond = '')
{
	// Clean parameters
	$id = intval($id);

	// Determine global order of forums
	$forums_tbl = $this->db->FastQueryArray('Forums', 'ID, Parent', '', 'Sort, Name');
	if ($forums_tbl === false || $forums_tbl == array()) return array();

	$forder_a = array();
	$this->build_forder(0, $forder_a, $forums_tbl);

	$forder = "CASE f.ID ";
	foreach ($forder_a as $pos => $fid)
	{
		$forder .= 'WHEN ' . $fid . ' THEN ' . $pos . ' ';
	}
	$forder .= 'END';

	if ($cond == '') $cond = '> 0';
	$id = intval($id);
	if (!$id) $id = $this->ID;
	$record = $this->db->FastQuery(
		/*table*/ array(
			array('', 'Forums', 'f', ''),
			array('LEFT', 'ForumWatch', 'fw', 'fw.Forum = f.ID')),
		/*fields*/ 'fw.Forum, fw.Mode, f.Name',
		/*where*/ 'fw.User = ' . $id . ' AND fw.Mode ' . $cond,
		/*order*/ $forder);

	$arr = array();
	if ($record) do
	{
		$record['Mode'] = intval($record['Mode']);
		array_push($arr, $record);
	}
	while ($record = $this->db->GetRecord());
	return $arr;

	//return $this->db->FastQueryArray("ForumWatch", "Forum, Mode", "User=$id and Mode>0", "Forum");
}

// -------------------- Forum/thread flags --------------------

// Set the new state of a forum/thread flag for a user.
// Doesn't use the global cache but runs a new database query for each call.
// Considered for internal use only.
//
// in forumid = (int) Forum ID
// in threadid = (int) Thread ID. Only forum OR thread ID must be set!
// in mask = (int) Bitmask of flags. Only one bit must be set!
// in set = (bool) new bit state
// in id = (int) User ID. 0: currently loaded user
//
// returns (bool) success
//
function SetForumThreadFlag($forumid, $threadid, $mask, $set, $id = 0)
{
	// Clean parameters
	$forumid = intval($forumid);
	$threadid = intval($threadid);
	$mask = intval($mask);
	$id = intval($id);

	// Work on this user by default
	if ($id == 0) $id = $this->ID;

	// Read current flags from database
	$flags = $this->db->FastQuery1st('UserForumFlags', 'Flags', 'Forum=' . $forumid . ' AND Thread=' . $threadid . ' AND User=' . $id);
	$newrecord = $flags === false;
	if ($newrecord) $flags = 0;

	// Perform requested bit operation
	if ($flags & $mask && $set) return true;   // No change
	elseif (!($flags & $mask) && !$set) return true;   // No change
	elseif ($set) $flags |= $mask;   // Set bit
	elseif (!$set) $flags &= ~$mask;   // Clear bit

	// Write new flags back to the database
	if ($newrecord)
	{
		if (!$this->db->AddRecord(array(
					'Forum' => $forumid,
					'Thread' => $threadid,
					'User' => $id,
					'Flags' => $flags),
				'UserForumFlags'))
			return false;
	}
	elseif ($flags !== 0)
	{
		if (!$this->db->ChangeRecord(array('Flags' => $flags),
				'Forum=' . $forumid . ' AND Thread=' . $threadid . ' AND User=' . $id,
				'UserForumFlags'))
			return false;
	}
	else   // $flags == 0
	{
		if (!$this->db->RemoveRecord(
				'Forum=' . $forumid . ' AND Thread=' . $threadid . ' AND User=' . $id,
				'UserForumFlags'))
			return false;
	}

	return true;
}

// Get the current state of a forum/thread flag for a user.
// Doesn't use the global cache but runs a new database query for each call.
// Considered for internal use only.
//
// in forumid = (int) Forum ID
// in threadid = (int) Thread ID. Only forum OR thread ID must be set!
// in mask = (int) Bitmask of flags. Only one bit must be set!
// in id = (int) User ID. 0: currently loaded user
//
// returns (int) non-zero value if flag is set, 0 otherwise.
//
function GetForumThreadFlag($forumid, $threadid, $mask, $id = 0)
{
	// Clean parameters
	$forumid = intval($forumid);
	$threadid = intval($threadid);
	$mask = intval($mask);
	$id = intval($id);

	// Work on this user by default
	if ($id == 0) $id = $this->ID;

	// Read current flags from database
	$flags = $this->db->FastQuery1st('UserForumFlags', 'Flags', 'Forum=' . $forumid . ' AND Thread=' . $threadid . ' AND User=' . $id);
	if ($flags === false) $flags = 0;

	return $flags & $mask;
}

// Get all forum & thread flags for a user. Used for initially building the cache.
//
// in id = (int) User ID. 0: currently loaded user
//
// returns array(array(Forum, Thread, Flags)) of all forum/thread flags for the user. Empty array() on error.
//
function GetAllForumThreadFlags($id = 0)
{
	// Clean parameters
	$id = intval($id);

	// Work on this user by default
	if ($id == 0) $id = $this->ID;

	// Determine global order of forums
	$forums_tbl = $this->db->FastQueryArray('Forums', 'ID, Parent', '', 'Sort, Name');
	if ($forums_tbl === false || $forums_tbl == array()) return array();

	$forder_a = array();
	$this->build_forder(0, $forder_a, $forums_tbl);

	$forder = "CASE t.Forum ";
	foreach ($forder_a as $pos => $fid)
	{
		$forder .= 'WHEN ' . $fid . ' THEN ' . $pos . ' ';
	}
	$forder .= 'END';

	// Read current flags from database
	$flags = $this->db->FastQueryArray(
		/*table*/ array(
			array('', 'UserForumFlags', 'uff', ''),
			array('LEFT', 'Threads', 't', 'uff.Thread = t.ID'),
			array('LEFT', 'Forums', 'f', 't.Forum = f.ID'),
			array('LEFT', 'Forums', 'f2', 'uff.Forum = f2.ID')),
		/*fields*/ 'uff.Forum, uff.Thread, uff.Flags, t.Forum as TForum, t.LastPostDate, t.Subject, f.Name, f2.Name AS Name2',
		/*where*/ 'uff.User=' . $id,
		/*order*/ $forder . ', t.LastPostDate DESC');
	if ($flags === false) return array();

	return $flags;
}

// Remove all flag entries matching the given IDs
// Each false value will be ignored.
// All valid values will be matched with AND so you can remove a flag for a single (user AND forum).
//
// returns (bool) success
//
function RemoveAllForumThreadFlags($forumid = null, $threadid = null, $userid = null)
{
	$where = '';
	if (isset($forumid)) $where .= ($where ? ' AND ' : '') . 'Forum=' . intval($forumid);
	if (isset($threadid)) $where .= ($where ? ' AND ' : '') . 'Thread=' . intval($threadid);
	if (isset($userid)) $where .= ($where ? ' AND ' : '') . 'User=' . intval($userid);
	if (!$where) return true;   // No action always succeeds

	return $this->db->RemoveRecord($where, 'UserForumFlags');
}

// ----- Simple functions for forums or threads only -----

// Set a single forum flag for a user
// Uses global cache to reduce the number of database queries
//
// Parameters see above
//
function SetForumFlag($forumid, $mask, $set, $id = 0)
{
	// Clean parameters
	$forumid = intval($forumid);
	$mask = intval($mask);
	$id = intval($id);

	if (($id === 0 || $id == $this->ID) && is_array($this->ForumFlags))
	    $this->ForumFlags[$forumid] = ($this->ForumFlags[$forumid] & ~$mask) | ($set ? -1 & $mask : 0);

	return $this->SetForumThreadFlag($forumid, 0, $mask, $set, $id);
}

// Get a single forum flag for a user
// Uses global cache to reduce the number of database queries
//
// Parameters see above
//
function GetForumFlag($forumid, $mask, $id = 0)
{
	// Clean parameters
	$forumid = intval($forumid);
	$mask = intval($mask);
	$id = intval($id);

	if ($id === 0 || $id == $this->ID)
	{
		if (!is_array($this->ForumFlags))
		{
			$this->ThreadFlags = array();
			$this->ForumFlags = array();
			$flags = $this->GetAllForumThreadFlags();
			foreach ($flags as $record)
			{
				if ($record['Forum']) $this->ForumFlags[intval($record['Forum'])] = intval($record['Flags']);
				elseif ($record['Thread']) $this->ThreadFlags[intval($record['Thread'])] = intval($record['Flags']);
			}
		}
		return $this->ForumFlags[$forumid] & $mask;
	}

	return $this->GetForumThreadFlag($forumid, 0, $mask, $id);
}

// Set a single thread flag for a user
// Uses global cache to reduce the number of database queries
//
// Parameters see above
//
function SetThreadFlag($threadid, $mask, $set, $id = 0)
{
	// Clean parameters
	$threadid = intval($threadid);
	$mask = intval($mask);
	$id = intval($id);

	if (($id === 0 || $id == $this->ID) && is_array($this->ThreadFlags))
	    $this->ThreadFlags[$threadid] = ($this->ThreadFlags[$threadid] & ~$mask) | ($set ? -1 & $mask : 0);

	return $this->SetForumThreadFlag(0, $threadid, $mask, $set, $id);
}

// Get a single thread flag for a user
// Uses global cache to reduce the number of database queries
//
// Parameters see above
//
function GetThreadFlag($threadid, $mask, $id = 0)
{
	// Clean parameters
	$threadid = intval($threadid);
	$mask = intval($mask);
	$id = intval($id);

	if ($id === 0 || $id == $this->ID)
	{
		if (!is_array($this->ThreadFlags))
		{
			$this->ThreadFlags = array();
			$this->ForumFlags = array();
			$flags = $this->GetAllForumThreadFlags();
			foreach ($flags as $record)
			{
				if ($record['Forum']) $this->ForumFlags[intval($record['Forum'])] = intval($record['Flags']);
				elseif ($record['Thread']) $this->ThreadFlags[intval($record['Thread'])] = intval($record['Flags']);
			}
		}
		return $this->ThreadFlags[$threadid] & $mask;
	}

	return $this->GetForumThreadFlag(0, $threadid, $mask, $id);
}

}  // class

?>
