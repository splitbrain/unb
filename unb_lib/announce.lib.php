<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// announce.lib.php
// Announcement Library, provides the IAnnounce interface

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('UNB_ANN_IMPORTANT', 1);   // Important announcement
define('UNB_ANN_RECURSIVE', 2);   // Recursive announcement (also display in sub-forums)
define('UNB_ANN_INTHREADS', 4);   // Also show announcement in threads

define('UNB_ANN_FOR_MASK', 24);   // The access mask is set by two bits (8|16)
define('UNB_ANN_FOR_ALL', 0);     // Display for all users
define('UNB_ANN_FOR_GUESTS', 8);  // Display for guests only
define('UNB_ANN_FOR_USERS', 16);  // Display for logged in users only
define('UNB_ANN_FOR_MODS', 24);   // Display for moderators only

// Represents an announcement and offers methods to operate on it
//
class IAnnounce
{

// -------------------- Public variables --------------------

// -------------------- Private variables --------------------

var $db;
var $finddb;

var $ID = 0;
var $Forum = 0;
var $Date = 0;
var $User = 0;
var $Subject = '';
var $Msg = '';
var $Options = 0;
	// 1	important announcement
	// 2	recursive announcement (also display in sub-forums)
	// 4	also show announcement in threads
	// 8+16(=24)  display for all (00=0) | guests only (01=8) | users only (10=16) | mods only (11=24)

// -------------------- Constructor --------------------

function __construct($id = 0)
{
	global $UNB;

	if (PHP5) eval('$this->db = clone $UNB["Db"];');
	else      $this->db = $UNB['Db'];

	if ($id !== 0) $this->Load($id);
}

function Reset()
{
	$this->ID = 0;
	$this->Forum = 0;
	$this->Date = 0;
	$this->User = 0;
	$this->Subject = '';
	$this->Msg = '';
	$this->Options = 0;
}

// -------------------- Find --------------------

// Find the first announcement that meets the criteria and load it into the
// object.
//
// in id = (int) Forum ID
// in newonly = (bool) Only find announcements that are unread by this user
// in inthread = (bool) We're in a thread page, so only return those
//                      announcements that shall be displayed there, too
//
// returns (bool) loading of the record succeeded
//
function Find($id, $newonly = false, $inthread = false)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (PHP5) eval('$this->finddb = clone $this->db;');
	else      $this->finddb = $this->db;

	$id = intval($id);
	if ($id == -1)
	{
		// This is admin_lock message!
		$newonly = false;
		$inthread = false;
	}

	// Find all parent forums for recursive announcements
	$parents = array();
	$forum = new IForum;
	$newid = $id;
	while ($newid > 0)
	{
		$forum->Load($newid);
		$newid = $forum->GetParent();
		array_push($parents, $newid);
	}
	$p_str = '';

	// Get access mask
	if ($id == -1)
		$access = '';   // no access mask for admin_lock message!
	elseif (!in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_GUESTS . ')';   // guests only
	elseif (in_array(UNB_GROUP_ADMINS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_GUESTS . ',' . UNB_ANN_FOR_USERS . ',' . UNB_ANN_FOR_MODS . ')';   // admins must see all announcements to be able to edit them!
	elseif (in_array(UNB_GROUP_MODS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_USERS . ',' . UNB_ANN_FOR_MODS . ')';   // mods|users only
	else   // implies in_array(1, LoginUserGroups)
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_USERS . ')';   // users only

	if ($newonly)
	{
		if ($parents)
			$p_str = ' OR (a.Forum IN (' . join(', ', $parents) . ') AND a.Options & ' . UNB_ANN_RECURSIVE . ')';

		$record = $this->finddb->FastQuery(
			/*table*/ array(
				array('', 'Announces', 'a', ''),
				array('LEFT', 'AnnounceRead', 'ar', 'a.ID = ar.Announce AND ar.User = ' . $UNB['LoginUserID'])),
			/*fields*/ 'a.*',
			/*where*/ '(a.Forum = ' . $id . $p_str . ') AND ar.Announce IS NULL ' .
				($inthread ? 'AND a.Options & ' . UNB_ANN_INTHREADS : '') .
				' AND ' . $access,
			/*order*/ 'a.Options & ' . UNB_ANN_IMPORTANT . ' DESC, a.Date DESC',
			/*limit*/ '',
			/*group*/ '');
	}
	else
	{
		if ($parents)
			$p_str = ' OR (Forum IN (' . join(', ', $parents) . ') AND Options & ' . UNB_ANN_RECURSIVE . ')';

		$record = $this->finddb->FastQuery('Announces', '*',
			'(Forum=' . $id . $p_str . ')' .
				($inthread ? ' AND Options & ' . UNB_ANN_INTHREADS : '') .
				($access ? ' AND ' . $access : ''),
			'Options & ' . UNB_ANN_IMPORTANT . ' DESC, Date DESC');
	}

	return $this->LoadFromRecord($record);
}

// Finds the next announcement after a previous Find() call and load it into
// the object.
//
// returns (bool) loading of the record succeeded
//
function FindNext()
{
	$record = $this->finddb->GetRecord();
	return $this->LoadFromRecord($record);
}

// Count the number of announcements meeting the criteria.
// This function uses similar code as Find().
//
// in forumid = (int) Forum ID
// in inthread = (bool) We're in a thread page, so only count those
//                      announcements that shall be displayed there, too
//
// returns (int) number of found records
//         (bool) false: database error
//
function Count($forumid, $inthread = false)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	// Find all parent forums for recursive announcements
	$parents = array();
	$forum = new IForum;
	$newid = $forumid;
	while ($newid)
	{
		$forum->Load($newid);
		$newid = $forum->GetParent();
		array_push($parents, $newid);
	}
	if ($parents)
		$p_str = ' OR (Forum IN (' . join(',', $parents) . ') AND Options & ' . UNB_ANN_RECURSIVE . ')';
	else
		$p_str = '';

	// Get access mask
	if ($forumid == -1)
		$access = '';   // no access mask for admin_lock message!
	elseif (!in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_GUESTS . ')';   // guests only
	elseif (in_array(UNB_GROUP_ADMINS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_GUESTS . ',' . UNB_ANN_FOR_USERS . ',' . UNB_ANN_FOR_MODS . ')';   // admins must see all announcements to be able to edit them!
	elseif (in_array(UNB_GROUP_MODS, $UNB['LoginUserGroups']))
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_USERS . ',' . UNB_ANN_FOR_MODS . ')';   // mods|users only
	else   // implies in_array(1, LoginUserGroups)
		$access = 'Options & ' . UNB_ANN_FOR_MASK . ' IN (0,' . UNB_ANN_FOR_USERS . ')';   // users only

	return $this->finddb->FastQuery1st('Announces', 'COUNT(*)',
		'(Forum=' . $forumid . $p_str . ')' .
			($inthread ? ' AND Options & ' . UNB_ANN_INTHREADS : '') .
			($access ? ' AND ' . $access : ''));
}

// -------------------- Read access --------------------

// Read announcement ID id from the database and load it into the object.
//
// returns (bool) load succeeded
//
function Load($id)
{
	// Clean parameters
	$id = intval($id);

	$record = $this->db->FastQuery('Announces', '*', 'ID=' . $id);
	return $this->LoadFromRecord($record);
}

// Same as Load() but read data from a given recordset instead of the database.
//
// returns (bool) load succeeded
//
function LoadFromRecord($record)
{
	if ($record)
	{
		$this->ID = intval($record['ID']);
		$this->Forum = intval($record['Forum']);
		$this->Date = intval($record['Date']);
		$this->User = intval($record['User']);
		$this->Subject = strval(trim($record['Subject']));
		$this->Msg = strval($record['Msg']);
		$this->Options = intval($record['Options']);
		return true;
	}
	else
	{
		$this->Reset();
		return false;
	}
}

function GetID()
{
	return $this->ID;
}

function GetForum($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Forum;
}

function GetDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Date;
}

function GetUser($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->User;
}

function GetSubject($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Subject;
}

function GetMsg($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Msg;
}

function GetOptions($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Options;
}

function IsImportant($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Options & UNB_ANN_IMPORTANT;
}

function GetOptAccess($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_ANN_FOR_MASK);
}

// -------------------- Write access --------------------

// Add an announcement
// Stores the new data incl the new announcement ID in the object
//
// in Forum = (int) Forum ID
// in Subject = (string)
// in Msg = (string) Message text
// in Options = (int) Combination of UNB_ANN_*
//
// returns (bool) operation succeeded
//
function Add($Forum, $Subject, $Msg, $Options)
{
	global $UNB;

	// Clean parameters
	$Forum = intval($Forum);
	$Subject = trim(strval($Subject));
	$Options = intval($Options);

	$max = intval($this->db->FastQuery1st('Announces', 'MAX(ID)'));

	$this->ID = $max + 1;
	$this->Forum = intval($Forum);
	$this->Date = time();
	$this->User = $UNB['LoginUserID'];
	$this->Subject = trim($Subject);
	$this->Msg = $Msg;
	$this->Options = intval($Options);

	return $this->db->AddRecord(array(
			'ID' => $this->ID,
			'Forum' => $this->Forum,
			'Date' => $this->Date,
			'User' => $this->User,
			'Subject' => $this->Subject,
			'Msg' => $this->Msg,
			'Options' => $this->Options
		), 'Announces');
}

// Alter an announcement
// Stores the new data in the object
//
// in ID = (int) Announcement ID
// in Forum = (int) Forum ID
// in Subject = (string)
// in Msg = (string) Message text
// in Options = (int) Combination of UNB_ANN_*
//
// returns (bool) operation succeeded
//
function Change($ID, $Forum, $Subject, $Msg, $Options)
{
	// Clean parameters
	$ID = intval($ID);
	$Forum = intval($Forum);
	$Subject = trim(strval($Subject));
	$Options = intval($Options);

	if (!$this->Load($ID)) return false;

	if ($Forum != -1) $this->Forum = intval($Forum);
	$this->Subject = trim($Subject);
	$this->Msg = $Msg;
	$this->Options = trim($Options);

	return $this->db->ChangeRecord(array(
			'Forum' => $this->Forum,
			'Date' => $this->Date,
			'User' => $this->User,
			'Subject' => $this->Subject,
			'Msg' => $this->Msg,
			'Options' => $this->Options
		), 'ID=' . $ID, 'Announces');
}

// Set the announcement's author user id
//
// in User = (int) new user id
// in id = (int) announcement to modify, null: currently loaded announcement
//
// returns (bool) success
//
function SetUser($User = null, $id = null)
{
	// Clean parameters
	if (isset($User)) $User = intval($User);
	if (isset($id)) $id = intval($id);

	$arr = array();

	// Collect changes
	if (isset($User))
	{
		if (!isset($id)) $this->User = $User;
		$arr['User'] = $User;
	}

	if (!$arr) return true;
	if (!isset($id)) $id = $this->ID;
	return $this->db->ChangeRecord($arr, 'ID=' . $id, 'Announces');
}

// Remove an announcement
//
// in id = (int) Announcement ID
//
// returns (bool) operation succeeded
//
function Remove($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;

	$this->RemoveAllReads($id);

	return $this->db->RemoveRecord('ID=' . $id, 'Announces');
}

// -------------------- Read/unread functions --------------------

// Mark this announcement read/unread for the current user
//
// in read = (bool) true: read; false: unread
//
// returns (bool) operation succeeded
//
function SetRead($read = true)
{
	global $UNB;

	if ($this->ID <= 0 || $UNB['LoginUserID'] <= 0) return false;

	if ($read)
	{
		if ($this->db->FastQuery1st('AnnounceRead', 'COUNT(*)', 'Announce=' . $this->ID . ' AND User=' . $UNB['LoginUserID']) == 0)
		{
			return $this->db->AddRecord(array('Announce' => $this->ID, 'User' => $UNB['LoginUserID']), 'AnnounceRead');
		}
		else
			return true;
	}
	else
	{
		if ($this->db->FastQuery1st('AnnounceRead', 'COUNT(*)', 'Announce=' . $this->ID . ' AND User=' . $UNB['LoginUserID']) > 0)
		{
			return $this->db->RemoveRecord('Announce=' . $this->ID . ' AND User=' . $UNB['LoginUserID'], 'AnnounceRead');
		}
		else
			return true;
	}
}

// Determine whether an announcement is marked read by the current user
//
// in id = (int) Announcement ID
//
// returns (bool)
//
function IsRead($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$db = $this->db;    // copy db-object or we'd collide with other running queries   // FIXME: PHP5 compliant?
	return ($db->FastQuery1st('AnnounceRead', 'COUNT(*)', 'Announce=' . $id . ' AND User=' . $UNB['LoginUserID']) > 0);
}

// Count all users that have marked an announcement read
//
// in id = (int) Announcement ID; this announcement if 0
//
// returns (int)
//
function ReadCount($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$db = $this->db;    // copy db-object or we'd collide with other running queries   // FIXME: PHP5 compliant?
	return $db->FastQuery1st('AnnounceRead', 'COUNT(*)', 'Announce=' . $id);
}

// Remove any read status relating to an announcement
//
// in id = (int) Announcement ID; this announcement if 0
//
function RemoveAllReads($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return $this->db->RemoveRecord('Announce=' . $id, 'AnnounceRead');
}

// Remove any read status relating to a user
//
// in id = (int) User ID; current user if 0
//
function RemoveAllUserReads($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $UNB['LoginUserID'];
	if (!$id) return false;
	return $this->db->RemoveRecord('User=' . $id, 'AnnounceRead');
}

}  // class

?>
