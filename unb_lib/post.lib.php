<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// post.lib.php
// Post Library, provides the IPost interface

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/thread.lib.php');

define('UNB_POST_NOSMILIES', 1);       // Disable conversion of smilies to graphics
define('UNB_POST_NOSPECIALABBC', 2);   // Disable special ABBC syntax like *bold* (see ABBC_SPECIAL)

// Represents a post and offers methods to operate on it
//
class IPost
{

// -------------------- Public variables --------------------

// -------------------- Private variables --------------------

var $db;
var $finddb;

var $ID = 0;
var $Thread = 0;
var $ReplyTo = 0;
var $Date = 0;
var $EditUser = 0;
var $EditDate = 0;
var $EditCount = 0;
var $EditReason = '';
var $User = 0;
var $UserName = '';
var $Subject = '';
var $Msg = '';
var $Options = 0;   // 1: NoSmileys, 2: NoSpecialABBC
var $AttachFile = '';
var $AttachFileName = '';
var $AttachDLCount = 0;
var $IP = '';
var $Hostname = '';
var $SpamRating = 0;

// -------------------- Constructor --------------------

function __construct($id = 0)
{
	global $UNB;

	if (PHP5) eval('$this->db = clone $UNB["Db"];');
	else      $this->db = $UNB['Db'];

	if ((is_int($id) || is_numeric($id)) && $id > 0) $this->Load($id);
	if (is_array($id)) $this->LoadFromRecord($id);
}

function Reset()
{
	$this->ID = 0;
	$this->Thread = 0;
	$this->ReplyTo = 0;
	$this->Date = 0;
	$this->EditUser = 0;
	$this->EditDate = 0;
	$this->EditCount = 0;
	$this->EditReason = '';
	$this->User = 0;
	$this->UserName = '';
	$this->Subject = '';
	$this->Msg = '';
	$this->Options = 0;
	$this->AttachFile = '';
	$this->AttachFileName = '';
	$this->AttachDLCount = 0;
	$this->IP = '';
	$this->Hostname = '';
	$this->SpamRating = 0;
}

// -------------------- Find --------------------

// Find first post. Loads post into this object
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
// in group = (string) SQL GROUP BY section
//
// returns (bool) success
//
function Find($where = '', $order = '', $limit = '', $group = '')
{
	if (PHP5) eval('$this->finddb = clone $this->db;');
	else      $this->finddb = $this->db;

	$record = $this->finddb->FastQuery('Posts', '*', $where, $order, $limit, $group);
	return $this->LoadFromRecord($record);
}

// Find next post from previous search
//
// returns (bool) success
//
function FindNext()
{
	$record = $this->finddb->GetRecord();
	return $this->LoadFromRecord($record);
}

// Count posts mathing the query
//
// in where = (string) SQL WHERE section. "" counts all posts
//
function Count($where = '')
{
	return $this->db->FastQuery1st('Posts', 'COUNT(ID)', $where);
}

// -------------------- Read access --------------------

// Load a post into this object
//
// in id = (int) post id to load
//
// returns (bool) success
//
function Load($id)
{
	// Clean parameters
	$id = intval($id);

	$record = $this->db->FastQuery('Posts', '*', 'ID=' . $id);
	return $this->LoadFromRecord($record);
}

// Load a post from a given record into this object.
// Can load results from array find functions.
//
// returns (bool) success
//
function LoadFromRecord($record)
{
	if ($record)
	{
		$this->ID = intval($record['ID']);
		$this->Thread = intval($record['Thread']);
		$this->ReplyTo = intval($record['ReplyTo']);
		$this->Date = intval($record['Date']);
		$this->EditUser = intval($record['EditUser']);
		$this->EditDate = intval($record['EditDate']);
		$this->EditCount = intval($record['EditCount']);
		$this->EditReason = strval($record['EditReason']);
		$this->User = intval($record['User']);
		$this->UserName = strval($record['UserName']);
		$this->Subject = strval($record['Subject']);
		$this->Msg = strval($record['Msg']);
		$this->Options = intval($record['Options']);
		$this->AttachFile = strval($record['AttachFile']);
		$this->AttachFileName = strval($record['AttachFileName']);
		$this->AttachDLCount = intval($record['AttachDLCount']);
		$this->IP = strval($record['IP']);
		$this->Hostname = strval($record['Hostname']);
		$this->SpamRating = intval($record['SpamRating']);
		return true;
	}
	else
	{
		$this->Reset();
		return false;
	}
}

// Get this object's post id
//
function GetID()
{
	return $this->ID;
}

// Get this object's thread id
//
function GetThread($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Thread;
}

// Get this object's reply-to post id
//
function GetReplyTo($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->ReplyTo;
}

// Get this object's post timestamp
//
function GetDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Date;
}

// Get this object's last edit user id
//
function GetEditUser($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EditUser;
}

// Get this object's last edit timestamp
//
function GetEditDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EditDate;
}

// Get this object's edit count value
//
function GetEditCount($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EditCount;
}

// Get this object's edit reason
//
function GetEditReason($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->EditReason;
}

// Get this object's author user id
//
function GetUser($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->User;
}

// Get this object's author user name
//
function GetUserName($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->UserName;
}

// Get this object's post subject
//
function GetSubject($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Subject;
}

// Get this object's post content
//
function GetMsg($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Msg;
}

// Get this object's post options/flags
//
function GetOptions($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Options;
}

// Get this object's attached filename (as stored here)
//
function GetAttachFile($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AttachFile;
}

// Get this object's attachment display name (original filename)
//
function GetAttachFileName($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AttachFileName;
}

// Get this object's attachment download count
//
function GetAttachDLCount($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->AttachDLCount;
}

// Get this object's sender IP address
//
function GetIP($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->IP;
}

// Get this object's sender hostname
//
function GetHostname($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Hostname;
}

// Get this object's post spam rating index
//
function GetSpamRating($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->SpamRating;
}

// -------------------- Write access --------------------

// Add a new post to the database
//
// in Thread = (int) thread id to create the post in
// in ReplyTo = (int) reply-to post id
// in UserName = (string) user name, for guests that are not logged it
// in Subject = (string) post subject
// in Msg = (string) post content
// in Options = (int) post options/flags, see UNB_POST_* constants at the top of this file
// in SpamRating = (int) calculated post spam rating index
//
// returns (bool) success
//
function Add($Thread, $ReplyTo, $UserName, $Subject, $Msg, $Options, $SpamRating)
{
	global $UNB;

	// Clean parameters
	$Thread = intval($Thread);
	$ReplyTo = intval($ReplyTo);
	$UserName = trim(strval($UserName));
	$Subject = trim(strval($Subject));
	$Options = intval($Options);
	$SpamRating = intval($SpamRating);

	$max = intval($this->db->FastQuery1st('Posts', 'MAX(ID)'));

	$this->ID = $max + 1;
	$this->Thread = $Thread;
	$this->ReplyTo = $ReplyTo;
	$this->Date = time();
	$this->EditUser = 0;
	$this->EditDate = 0;
	$this->EditCount = 0;
	$this->EditReason = '';
	$this->User = $UNB['LoginUserID'];
	$this->UserName = $UserName;
	$this->Subject = $Subject;
	$this->Msg = $Msg;
	$this->Options = $Options;
	$this->AttachFile = '';
	$this->AttachFileName = '';
	$this->AttachDLCount = 0;
	$this->IP = $_SERVER['REMOTE_ADDR'];
	$this->Hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$this->SpamRating = $SpamRating;

	$ok = $this->db->AddRecord(array(
			'ID' => $this->ID,
			'Thread' => $this->Thread,
			'ReplyTo' => $this->ReplyTo,
			'Date' => $this->Date,
			'EditUser' => $this->EditUser,
			'EditDate' => $this->EditDate,
			'EditCount' => $this->EditCount,
			'EditReason' => $this->EditReason,
			'User' => $this->User,
			'UserName' => $this->UserName,
			'Subject' => $this->Subject,
			'Msg' => $this->Msg,
			'Options' => $this->Options,
			'AttachFile' => $this->AttachFile,
			'AttachFileName' => $this->AttachFileName,
			'AttachDLCount' => $this->AttachDLCount,
			'IP' => $this->IP,
			'Hostname' => $this->Hostname,
			'SpamRating' => $this->SpamRating
		), 'Posts');

	// update statistics table, no error detection here
	if ($ok)
	{
		UnbUpdateStat('NewPosts', 1);
		// update LastPostDate for correct display order
		$thread = new IThread;
		$thread->SetLastPostDate($this->Date, $this->Thread);
	}
	return $ok;
}

// Set the post's attachment file
//
// in AttachFile = (string) local filename, "" deletes file from disk
// in AttachFileName = (string) original (diaplay) name
//
// returns (bool) success
//
function SetAttachFile($AttachFile, $AttachFileName = '')
{
	global $UNB;

	// clean parameters
	$AttachFile = trim(strval($AttachFile));
	$AttachFileName = trim(strval($AttachFileName));

	if ($AttachFile == '' && $this->AttachFile == '') return true;   // all done if nothing to add where nothing is
	if ($AttachFile != '' && $AttachFileName == '') return false;   // don't allow empty screen name for real files

	$id = $this->ID;
	if ($AttachFile == '')
	{
		// Empty the screen name
		$AttachFileName = '';

		// remove image file from disk, too (if it exists at all)
		if (is_file($UNB['AttachPath'] . $this->AttachFile))
			if (!unlink($UNB['AttachPath'] . $this->AttachFile)) return false;
	}
	$this->AttachFile = $AttachFile;
	$this->AttachFileName = $AttachFileName;
	$this->AttachDLCount = 0;

	if (!$this->db->ChangeRecord(array(
			'AttachFile' => $this->AttachFile,
			'AttachFileName' => $this->AttachFileName,
			'AttachDLCount' => 0
		), 'ID=' . $id, 'Posts'))
		return false;

	// HACK: We're going to read a thread that was modified but is still in the cache - unmodified!
	// It's a cache problem, but it's very complicated to fix without dropping the entire cache. Damn it.
	$UNB['ThreadCache'] = array();

	// Update thread's HasAttach flag
	$thread = new IThread;
	if (!$thread->Load($this->Thread))
		return true;   // cannot open post's thread... huh? DB check will find this later then
	if ($thread->HasAttach() && $AttachFile != '')
		return true;   // nothing to do here, flag is already set

	$opt = $thread->GetOptions();
	if ($AttachFile != '')
	{
		// new attachment, add the flag
		$opt |= UNB_THREAD_ATTACHMENT;
	}
	else
	{
		// attachment removed - are there still others?
		// test all other posts in this thread for attachments
		if ($this->db->FastQuery1st('Posts', 'COUNT(*)', 'Thread=' . $this->Thread . " AND AttachFile<>''") == 0)
		{
			// last attachment removed, clear the flag
			$opt &= ~UNB_THREAD_ATTACHMENT;
		}
	}
	$thread->SetOptions($opt);   // no error detection here. DB check would find them later then

	return true;
}

// Increase this post's attachment download counter
//
// returns (bool) success
//
function IncDLCount()
{
	global $UNB;

	$this->AttachDLCount++;

	$pre = $UNB['Db']->tblprefix;
	return $UNB['Db']->ChangeRecord('AttachDLCount = AttachDLCount + 1', 'ID = ' . $this->ID, 'Posts');
}

// Set author user id
//
// in User = (int) new author user id. will be ignored if false
// in UserName = (string) new author user name. will be ignored if false
// in EditUser = (int) new edit user id. will be ignored if false
//
// returns (bool) success
//
function SetUser($User = false, $UserName = false, $EditUser = false)
{
	$arr = array();

	if ($User !== false)
	{
		$this->User = $User;
		$arr['User'] = $User;
	}
	if ($UserName !== false)
	{
		#if (isset($arr['User'])) $UserName = '';   // Set UserName to '' if a User ID is given!
		$this->UserName = $UserName;
		$arr['UserName'] = $UserName;
	}
	if ($EditUser !== false)
	{
		$this->EditUser = $EditUser;
		$arr['EditUser'] = $EditUser;
	}
	if (!$arr) return true;

	return $this->db->ChangeRecord($arr, 'ID=' . $this->ID, 'Posts');
}

// Set reply-to post id
//
// in ReplyTo = (int) new reply-to post id
//
// returns (bool) success
//
function SetReplyTo($ReplyTo = false)
{
	$arr = array();

	// Clean parameters
	$ReplyTo = intval($ReplyTo);

	if ($ReplyTo !== false)
	{
		$this->ReplyTo = $ReplyTo;
		$arr['ReplyTo'] = $ReplyTo;
	}
	if (!$arr) return true;

	return $this->db->ChangeRecord($arr, 'ID=' . $this->ID, 'Posts');
}

// Move a set of posts to another thread
//
// in Thread = (int) new thread id to move posts into
// in PostIDs = (array) post ids to move. false: only move currently loaded post
//
// returns (bool) success
//
function SetThreadArray($Thread, $PostIDs = false)
{
	if ($PostIDs === false) $PostIDs = array($this->ID);
	if (!is_array($PostIDs)) return false;

	// Clean parameters
	$Thread = intval($Thread);
	foreach ($PostIDs as $k => $v) $PostIDs[$k] = intval($v);

	$arr['Thread'] = $Thread;
	$IDs = join(',', $PostIDs);

	return $this->db->ChangeRecord($arr, 'ID IN (' . $IDs . ')', 'Posts');
}

// Update a post in the database
//
// in id = (int) post id to update
// in UserName = (string) author user name, for guests that are not logged in
// in Subject = (string) post subject
// in Msg = (string) post content
// in Options = (int) new post options/flags, see UNB_POST_* constants at the top of this file
// in EditUser = (int) last edit user id
// in EditReason = (string) reason why the post was editied
// in SpamRating = (int) new spam rating index
//
// returns (bool) success
//
function Change($id, $UserName, $Subject, $Msg, $Options, $EditUser = 0, $EditReason = '', $SpamRating = 0)
{
	if (!$this->Load($id)) return false;

	global $UNB;

	// Clean parameters
	$id = intval($id);
	$UserName = trim(strval($UserName));
	$Subject = trim(strval($Subject));
	$Options = intval($Options);
	$EditUser = intval($EditUser);
	$EditReason = trim(strval($EditReason));
	$SpamRating = intval($SpamRating);

	$this->UserName = $UserName;
	$this->Subject = $Subject;
	$this->Msg = $Msg;
	$this->Options = $Options;
	$this->SpamRating = $SpamRating;
	if ($EditUser == -1)
	{
		$this->EditUser = 0;
		$this->EditDate = 0;
		$this->EditCount = 0;
		$this->EditReason = '';
	}
	elseif ($EditUser > 0)
	{
		// we should add an edit note, but we drop it, if no user has read this post yet
		$lasttime = max($this->EditDate, $this->Date);
		$cnt = $UNB['Db']->FastQuery1st(
			'ThreadWatch',
			'COUNT(*)',
			'Thread = ' . $this->Thread . ' AND LastRead >= ' . $lasttime . ' AND User <> ' . $this->User);
		$tdiff = time() - $lasttime;

		// (task #66)
		// Don't add the edit note if no user has read the post and creation or last edit was less than _ minutes ago.
		// Set the config option to 0 if a note shall be added in every case.
		// +Always add a note if the post is edited by another user than the author
		if ($cnt > 0 ||
		    $tdiff >= intval(rc('no_edit_note_grace_time')) * 60 ||
		    $EditUser != $this->User)
		{
			$this->EditUser = $EditUser;
			$this->EditDate = time();
			$this->EditCount++;
		}
		$this->EditReason = $EditReason;   // always store edit reason, but maybe not always show it
	}

	$arr = array('Subject' => $this->Subject,
		'Msg' => $this->Msg,
		'Options' => $this->Options,
		'SpamRating' => $this->SpamRating);
	if ($this->User <= 0)
		$arr['UserName'] = $UserName;
	if ($EditUser != 0)
		$arr = array_merge($arr, array('EditUser' => $this->EditUser,
			'EditDate' => $this->EditDate,
			'EditCount' => $this->EditCount,
			'EditReason' => $this->EditReason));

	return $this->db->ChangeRecord($arr, 'ID=' . $id, 'Posts');
}

// Remove a post
//
// in id = (int) post id to remove. 0 for currently loaded post
// in auto_del_thread = (bool) also remove the thread if this was the last post in it
//
// returns (bool) success
//
function Remove($id = 0, $auto_del_thread = true)
{
	// Clean parameters
	$id = intval($id);

	if ($id != 0) if (!$this->Load($id)) return false;
	if (!$id) $id = $this->ID;
	$r = 1;

	// Posts that refer to this post get their reference set to this post's reference
	$post = new IPost;
	if ($post->Find('ReplyTo=' . $id)) do
	{
		$post->SetReplyTo($this->ReplyTo);
	}
	while ($post->FindNext());

	// if this was the only post in this thread, also remove the thread
	// lazy '&&' evaluation! only count posts it we should delete the thread then, too
	if ($auto_del_thread && $this->Count('Thread=' . $this->Thread) <= 1)
	{
		$thread = new IThread;
		$thread->Remove($this->Thread);
		UnbCallHook('thread.removed', $this->Thread);
		$r = 2;
	}

	// unlink attachment file
	$this->SetAttachFile('');

	if (!$this->db->RemoveRecord('ID=' . $id, 'Posts'))
		return false;
	else
	{
		// update statistics table, no error detection here
		UnbUpdateStat('NewPosts', -1);

		// update LastPostDate for correct display order
		$thread = new IThread;
		$thread->SetLastPostDate(false, $this->Thread);

		return $r;
	}
}

}  // class

?>
