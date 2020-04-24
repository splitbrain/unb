<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// thread.lib.php
// Thread Library, provides the IThread interface

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('UNB_THREAD_CLOSED', 1);        // This thread is closed for further replies
define('UNB_THREAD_IMPORTANT', 2);     // This thread is marked "important"
define('UNB_THREAD_POLL', 4);          // This thread contains a poll
define('UNB_THREAD_MOVED', 8);         // This thread is a "moved" pointer to the actual thread
define('UNB_THREAD_ATTACHMENT', 16);   // At least one of the thread's posts has an attachment

// Represents a thread and offers methods to operate on it
//
class IThread
{

// -------------------- Public variables --------------------

// -------------------- Private variables --------------------

var $db;
var $finddb;

var $ID = 0;
var $Forum = 0;
var $LastPostDate = 0;
var $Date = 0;
var $User = 0;
var $UserName = '';
var $Subject = '';
var $Desc = '';
var $Views = 0;
var $Options = 0;
	//	1	closed
	//	2	important (sticky)
	//	4	poll
	//	8	moved
	//	16	has an attachment
var $Question = '';
	// for moved threads (options & 8): ID to new thread
var $PollTimeout = 0;
	// time to run the poll, in hours
	// for moved threads (options & 8): time to keep the note before it expires, in hours

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
	$this->Forum = 0;
	$this->LastPostDate = 0;
	$this->Date = 0;
	$this->User = 0;
	$this->UserName = '';
	$this->Subject = '';
	$this->Desc = '';
	$this->Views = 0;
	$this->Options = 0;
	$this->Question = '';
	$this->PollTimeout = 0;
}

// ------------------------------------------------------------ FIND

// Find first thread. Loads thread into this object
//
// NOTE: This function also finds "moved note" threads!
//       You may want to filter them out with $where = "NOT (Options & 8)".
//
// NOTE: This function ignores the thread ignoring flag.
//       Use FindArray if you don't want to find ignored threads.
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
//
// returns (bool) success
//
function Find($where = 0, $order = '', $limit = '')
{
	if (PHP5) eval('$this->finddb = clone $this->db;');
	else      $this->finddb = $this->db;

	if (is_int($where))
	{
		if ($where > 0)
			$where = 'Forum=' . $where;
		else
			$where = '';
	}
	elseif (!is_string($where)) return false;   // input type not supported

	$record = $this->finddb->FastQuery('Threads', '*', $where, $order, $limit);
	return $this->LoadFromRecord($record);
}

// Find next thread from previous search
//
// returns (bool) success
//
function FindNext()
{
	$record = $this->finddb->GetRecord();
	return $this->LoadFromRecord($record);
}

// Find all threads
//
// NOTE: This function also finds "moved note" threads!
//       You may want to filter them out with $where = "NOT (Options & 8)"
//
// See also: UnbFindThreadsArray function
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
// in showhidden = (bool) include hidden threads in this search
//
// returns (array(array)) all thread record rows
//
function FindArray($where = '', $order = '', $limit = '', $showhidden = false)
{
	global $UNB;

	$inForum = false;

	$from = array(
		array('', 'Threads', 't', ''),
		array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
		array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID'));

	if (is_int($where))
	{
		$inForum = true;
		$where = 't.Forum=' . $where;
	}
	elseif (!is_string($where)) return false;   // input type not supported

	if (!$showhidden)
	{
		if (!$inForum) $where .= ($where ? ' AND ' : '') . "(uff_f.Flags IS NULL OR NOT (uff_f.Flags & " . UNB_UFF_HIDE . "))";
		$where .= ($where ? ' AND ' : '') . "(uff_t.Flags IS NULL OR NOT (uff_t.Flags & " . UNB_UFF_HIDE . "))";
	}

	$a = array();
	if ($record = $this->db->FastQuery($from, 't.*', $where, $order, $limit)) do
	{
		array_push($a, $record);
	}
	while ($record = $this->db->GetRecord());
	return $a;
}

// Find the first unread post in a/this thread
//
// in LastRead = (int) user's LastRead timestamp for this thread
//
// returns (array) thread record row
//
function FirstUnreadPost($LastRead, $id = 0)
{
	if (!$id) $id = $this->ID;

	return $this->db->FastQuery('Posts', '*', 'Thread=' . $id . ' AND Date>' . $LastRead, 'Date', '1');
}

// Count threads mathing the query
//
// in where = (string) SQL WHERE section. "" counts all threads
//            (int) Forum ID
// in showhidden = (bool) include hidden threads in this search
//
function Count($where = '', $showhidden = false)
{
	global $UNB;

	$inForum = false;

	$from = array(
		array('', 'Threads', 't', ''),
		array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
		array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID'));

	if (is_int($where))
	{
		$inForum = true;
		$where = 't.Forum=' . $where;
	}
	elseif (!is_string($where)) return false;   // input type not supported

	if (!$showhidden)
	{
		if (!$inForum) $where .= ($where ? ' AND ' : '') . "(uff_f.Flags IS NULL OR NOT (uff_f.Flags & " . UNB_UFF_HIDE . "))";
		$where .= ($where ? ' AND ' : '') . "(uff_t.Flags IS NULL OR NOT (uff_t.Flags & " . UNB_UFF_HIDE . "))";
	}

	return intval($this->db->FastQuery1st($from, 'COUNT(*)', $where));
}

// -------------------- Read access --------------------

// Load a thread into this object. Uses the global thread records cache.
//
// in id = (int) thread id to load
//
// returns (bool) success
//
function Load($id)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (array_key_exists($id, $UNB['ThreadCache']))
	{
		$record = $UNB['ThreadCache'][$id];
		$add = false;
	}
	else
	{
		$record = $this->db->FastQuery('Threads', '*', 'ID=' . $id);
		$add = true;
	}

	return $this->LoadFromRecord($record, $add);
}

// Load a thread from a given record into this object.
// Can load results from array find functions.
//
// in record = (array) thread record row
// in add = (bool) update the global thread records cache
//
// returns (bool) success
//
function LoadFromRecord($record, $add = false)
{
	global $UNB;

	if ($record)
	{
		$this->ID = intval($record['ID']);
		$this->Forum = intval($record['Forum']);
		$this->LastPostDate = intval($record['LastPostDate']);
		$this->Date = intval($record['Date']);
		$this->User = intval($record['User']);
		$this->UserName = strval($record['UserName']);
		$this->Subject = strval($record['Subject']);
		$this->Desc = strval($record['Desc']);
		$this->Views = intval($record['Views']);
		$this->Options = intval($record['Options']);
		$this->Question = strval($record['Question']);
		$this->PollTimeout = intval($record['PollTimeout']);

		if ($add) $UNB['ThreadCache'][$this->ID] = $record;

		return true;
	}
	else
	{
		$this->Reset();
		return false;
	}
}

// Get this object's thread id
//
function GetID()
{
	return $this->ID;
}

// Get this object's forum id
//
function GetForum($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Forum;
}

// Get this object's last post timestamp
//
function GetLastPostDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->LastPostDate;
}

// Get this object's timestamp
//
function GetDate($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Date;
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

// Get this object's subject
//
function GetSubject($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Subject;
}

// Get this object's description (subtitle)
//
function GetDesc($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Desc;
}

// Get this object's views (hits) count
//
function GetViews($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Views;
}

// Get this object's options/flags
//
function GetOptions($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Options;
}

// Get this object's poll question
//
function GetQuestion($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Question;
}

// Get this object's poll timeout timestamp
//
function GetPollTimeout($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->PollTimeout;
}

// Get this object's closed flag
//
function IsClosed($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_CLOSED) ? true : false;
}

// Get this object's important flag
//
function IsImportant($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_IMPORTANT) ? true : false;
}

// Get this object's poll flag
//
function HasPoll($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_POLL) ? true : false;
}

// Has the poll already ended?
//
function IsPollEnded($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->PollTimeout && $this->Date + $this->PollTimeout * 3600 <= time()) ? true : false;
}

// Get this object's moved flag
//
function IsMoved($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_MOVED) ? true : false;
}

// Is this a moved notice thread and has it expired?
//
function IsMovedExpired($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_MOVED && $this->LastPostDate + $this->PollTimeout * 86400 <= time()) ? true : false;
}

// Get this object's attachment flag
//
function HasAttach($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return ($this->Options & UNB_THREAD_ATTACHMENT) ? true : false;
}

// -------------------- Write access --------------------

// Add a new forum to the database
// After adding, all saved values are instantly accessible in this object
//
// in Forum = (int) forum id to create the thread in
// in UserName = (string) author user name
// in Subject = (string) thread subject
// in Options = (int) thread flags/options, see UNB_THREAD_* constants at the top of this file
// in date = (int) timestamp
//
// returns (bool) success
//
function Add($Forum, $UserName, $Subject, $Options, $date = 0)
{
	global $UNB;

	// Clean parameters
	$Forum = intval($Forum);
	$UserName = trim(strval($UserName));
	$Subject = trim(strval($Subject));
	$Options = intval($Options);
	$date = intval($date);

	$max = $this->db->FastQuery1st('Threads', 'MAX(ID)');
	if (!$date) $date = time();

	$this->ID = $max + 1;
	$this->Forum = $Forum;
	$this->LastPostDate = time();
	$this->Date = $date;
	$this->User = $UNB['LoginUserID'];
	$this->UserName = $UserName;
	$this->Subject = $Subject;
	$this->Views = 0;
	$this->Options = $Options;
	$this->Question = '';
	$this->PollTimeout = 0;

	$ok = $this->db->AddRecord(array(
			'ID' => $this->ID,
			'Forum' => $this->Forum,
			'Date' => $this->Date,
			'User' => $this->User,
			'UserName' => $this->UserName,
			'Subject' => $this->Subject,
			'Views' => $this->Views,
			'Options' => $this->Options,
			'Question' => $this->Question
		), 'Threads');

	// update statistics table, no error detection here
	if ($ok) UnbUpdateStat('NewThreads', 1);
	return $ok;
}

// Update a thread in the database
// If $id = 0 the currently loaded thread will be changed and updated in the database,
// else currently loaded thread won't be changed and data will be written to another thread in the database.
// $User and $UserName are only updated if $User != -1.
// $Options is only updated if it's != -1.
//
// returns (bool) success
//
function Change($id, $Forum, $User, $UserName, $Subject, $Options = -1)
{
	// Clean parameters
	$id = intval($id);
	$Forum = intval($Forum);
	$User = intval($User);
	$UserName = trim(strval($UserName));
	$Subject = trim(strval($Subject));
	$Options = intval($Options);

	if (!$id)
	{
		$id = $this->ID;
		$this->Forum = $Forum;
		if ($User != -1)
		{
			$this->User = $User;
			$this->UserName = $UserName;
		}
		$this->Subject = $Subject;
		if ($Options != -1) $this->Options = $Options;
	}
	$arr = array('Forum' => $Forum,
		'Subject' => $Subject);
	if ($User != -1)
		$arr = array_merge($arr, array('User' => $User, 'UserName' => $UserName));
	if ($Options != -1)
		$arr = array_merge($arr, array('Options' => $this->Options));

	return $this->db->ChangeRecord($arr, 'ID=' . $id, 'Threads');
}

// Set author user for this thread
//
// in User = (int) new author user id
// in UserName = (string) new author user name
//
// returns (bool) success
//
function SetUser($User = false, $UserName = false)
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
		$this->UserName = trim($UserName);
		$arr['UserName'] = trim($UserName);
	}
	if (!$arr) return true;

	return $this->db->ChangeRecord($arr, 'ID=' . $this->ID, 'Threads');
}

// Set subject for this thread
//
// in Subject = (string) new thread subject
// in id = (int) thread id, 0: currently loaded thread
//
// returns (bool) success
//
function SetSubject($Subject, $id = 0)
{
	if (trim($Subject) == '') $Subject = '---';

	// Clean parameters
	$Subject = trim(strval($Subject));

	if (!$id)
	{
		$id = $this->ID;
		$this->Subject = $Subject;
	}
	return $this->db->ChangeRecord(array('Subject' => $Subject), 'ID=' . $id, 'Threads');
}

// Set description (subtitle) for this thread
//
// in Desc = (string) new thread description
// in id = (int) thread id, 0: currently loaded thread
//
// returns (bool) success
//
function SetDesc($Desc, $id = 0)
{
	// Clean parameters
	$Desc = trim(strval($Desc));

	if (!$id)
	{
		$id = $this->ID;
		$this->Desc = $Desc;
	}
	return $this->db->ChangeRecord(array('Desc' => $Desc), 'ID=' . $id, 'Threads');
}

// Set options/flags for this thread
//
// in Options = (int) new thread options
// in id = (int) thread id, 0: currently loaded thread
//
// returns (bool) success
//
function SetOptions($Options, $id = 0)
{
	// Clean parameters
	$Options = intval($Options);

	if (!$id)
	{
		$id = $this->ID;
		$this->Options = $Options;
	}
	return $this->db->ChangeRecord(array('Options' => $Options), 'ID=' . $id, 'Threads');
}

// Increase number of views of a thread
//
// in id = (int) thread id, 0: currently loaded thread
// in count = (int) number to increase counter by
//
// returns (bool) success
//
function IncViews($id = 0, $count = 1)
{
	// Clean parameters
	$id = intval($id);
	$count = intval($count);

	if (!$id)
	{
		$id = $this->ID;
		$this->Views += $count;
	}
	$pre = $this->db->tblprefix;
	return $this->db->ChangeRecord('`Views` = `Views` + ' . $count, 'ID = ' . $id, 'Threads');
}

// Set last post timestamp for this thread
//
// in LastPostDate = (int) new thread's last post timestamp, false: auto-determine value
// in id = (int) thread id, 0: currently loaded thread
//
// returns (bool) success
//
function SetLastPostDate($LastPostDate = false, $id = 0)
{
	if (!$id)
	{
		$id = $this->ID;
	}

	if ($LastPostDate === false)
	{
		// Load the last post of the old thread and update LastPostDate
		$post = new IPost;
		$post->Find('Thread=' . $id, 'Date DESC', '1');
		$LastPostDate = $post->GetDate();
	}

	// Clean parameters
	$LastPostDate = intval($LastPostDate);

	if (!$id)
	{
		$this->LastPostDate = $LastPostDate;
	}
	return $this->db->ChangeRecord(array('LastPostDate' => $LastPostDate), 'ID=' . $id, 'Threads');
}

// Remove a thread
// NOTE: All posts in this thread must already be removed or
//       they won't be accessible anymore!
//
// in id = (int) thread id to remove, 0 for currently loaded thread
//
// returns (bool) success
//
function Remove($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;

	// note: there's no error detection on these operations
	$this->RemoveAllThreadWatchs($id);
	$this->RemoveAllVotes($id);
	$UNB['LoginUser']->RemoveAllForumThreadFlags(null, $id);

	$ok = $this->db->RemoveRecord('ID=' . $id, 'Threads');

	// update statistics table, no error detection here
	if ($ok) UnbUpdateStat('NewThreads', -1);
	return $ok;
}

// Extended Remove
// Delete all posts in (this) thread and implicitly subsequently removes thread entry
//
// in id = (int) thread id to remove, 0 for currently loaded thread
//
// returns (bool) success
//
function RemoveAllPosts($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	if ($id <= 0) return false;

	// remove all posts in (this) thread, without counting remaining posts every time
	$post = new IPost;
	if ($post->Find('Thread=' . $id)) do
	{
		if (!$post->Remove(0, false)) return false;
	}
	while ($post->FindNext());

	// remove thread 'manually' now
	return $this->Remove($id);
}

// -------------------- Poll functions --------------------

// Tell whether current user has already voted at a poll
//
// in id = (int) thread id, 0 for currently loaded thread
//
// return false: not voted
//        true: has already voted, but the option is unknown
//        (int) >0: vote option ID
//
function HasUserVoted($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$res = $this->db->FastQuery1st('PollUsers', 'VoteNum', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($res === false) return false;
	if ($res == 0) return true;
	return intval($res);
}

// Get an array of vote options (possible answers) for a poll
//
// in id = (int) thread id, 0 for currently loaded thread
//
function GetVotes($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return ($this->db->FastQueryArray('PollVotes', '*', 'Thread=' . $id, 'Sort'));
}

// Count votes for a poll
//
// in id = (int) thread id, 0 for currently loaded thread
//
function CountVotes($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return ($this->db->FastQuery1st('PollUsers', 'COUNT(*)', 'Thread=' . $id));
}

// Get an array of users, that have already voted for a poll
//
// in id = (int) thread id, 0 for currently loaded thread
//
// returns array(array(userid, username, option))
//
function GetUsersVoted($id = 0)
{
	// Clean parameters
	$id = intval($id);
	if (!$id) $id = $this->ID;

	if (PHP5) eval('$db = clone $this->db;');
	else      $db = $this->db;

	return $db->FastQueryArray(
		/*table*/ array(
			array('', 'PollUsers', 'pu', ''),
			array('', 'Users', 'u', '')),
		/*fields*/ 'pu.User, u.Name, pu.VoteNum',
		/*where*/ 'pu.User = u.ID AND pu.Thread = ' . $id,
		/*order*/ 'u.Name');
}

// Add current user's vote
//
// in id = (int) thread id, 0 for currently loaded thread
// in voteid = (int) vote id to add
//
function AddVote($id, $voteid)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);
	$voteid = intval($voteid);
	if (!$id) $id = $this->ID;

	if (!$this->db->FastQuery1st('PollUsers', 'COUNT(*)', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']))
	{
		$this->db->AddRecord(array('Thread' => $id, 'User' => $UNB['LoginUserID'], 'VoteNum' => $voteid), 'PollUsers');
	}
	else
		return false;

	// Update LastVoted column
	$this->db->ChangeRecord(array('LastVoted' => time()), 'ID=' . $id, 'Threads');

	return $this->db->ChangeRecord('`Votes` = `Votes` + 1', 'ID = ' . $voteid, 'PollVotes');
}

// Remove current user's vote
//
// in id = (int) thread id, 0 for currently loaded thread
//
function RemoveVote($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);
	if (!$id) $id = $this->ID;

	if ($voteid = $this->db->FastQuery1st('PollUsers', 'VoteNum', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']))
	{
		$this->db->RemoveRecord('Thread=' . $id . ' AND User=' . $UNB['LoginUserID'], 'PollUsers');
	}
	else
		return false;

	// Update LastVoted column
	$this->db->ChangeRecord(array('LastVoted' => time()), 'ID=' . $id, 'Threads');

	return $this->db->ChangeRecord('`Votes` = `Votes` - 1', 'ID = ' . $voteid, 'PollVotes');
}

// Create a new voting option (for this or another thread)
//
// in id = (int) thread id, 0 for currently loaded thread
// in title = (string) answer text
// in sort = (int) sort index
//
function CreateVoteOption($id = 0, $title, $sort)
{
	// Clean parameters
	$id = intval($id);
	if (!$id) $id = $this->ID;

	$optionid = $this->db->FastQuery1st('PollVotes', 'MAX(ID)') + 1;
	return $this->db->AddRecord(array('ID' => $optionid, 'Thread' => $id, 'Sort' => $sort, 'Title' => $title, 'Votes' => 0), 'PollVotes');
}

// Change a voting option
//
// in vodeid = (int) vote option id
// in title = (string) answer text
// in sort = (int) sort index
// in settitle = (bool) set new answer text
// in setsort = (bool) set new sort index
//
function ChangeVoteOption($voteid, $title, $sort, $settitle = true, $setsort = true)
{
	if (!$settitle && !$setsort) return true;

	// Clean parameters
	$voteid = intval($voteid);
	$title = trim(strval($title));
	$sort = intval($sort);

	$set = array();
	if ($settitle) $set['Title'] = $title;
	if ($setsort) $set['Sort'] = $sort;

	if ($settitle && $title == '')
		return $this->db->RemoveRecord('ID=' . $voteid, 'PollVotes') &&
		       $this->db->RemoveRecord('VoteNum=' . $voteid, 'PollUsers');
	else
		return $this->db->ChangeRecord($set, 'ID=' . $voteid, 'PollVotes');
}

// Set the poll question
//
// in Question = (string) question text
//               For moved thread notes: (int) target thread id
// in PollTimeout = (int) time to run the poll, in hours
//                  For moved thread notes: (int) time to keep the note before it expires, in hours
// in setquestion = (bool) set new question text
// in settimeout = (bool) set new timeout timestamp
// in id = (int) thread id, 0 for currently loaded thread
//
function SetQuestion($Question, $PollTimeout, $setquestion = true, $settimeout = true, $id = 0)
{
	if (!$setquestion && !$settimeout) return true;

	// Clean parameters
	$Question = trim(strval($Question));
	$PollTimeout = intval($PollTimeout);

	if (!$id)
	{
		$id = $this->ID;
		if ($setquestion) $this->Question = $Question;
		if ($settimeout) $this->PollTimeout = $PollTimeout;
	}

	$set = array();
	if ($setquestion) $set['Question'] = $Question;
	if ($settimeout) $set['PollTimeout'] = $PollTimeout;

	return $this->db->ChangeRecord($set, 'ID=' . $id, 'Threads');
}

// Remove any PollVotes data for (this) thread
//
// in id = (int) thread id, 0 for currently loaded thread
//
function RemoveAllVotes($id = 0)
{
	// Clean parameters
	$id = intval($id);
	if (!$id) $id = $this->ID;

	$ok = true;
	if (!$this->db->RemoveRecord('Thread=' . $id, 'PollVotes')) $ok = false;
	if (!$this->db->RemoveRecord('Thread=' . $id, 'PollUsers')) $ok = false;
	return $ok;
}

// Remove any PollVotes data for (current) user
//
// in id = (int) user id, 0 for current user
//
function RemoveAllUserVotes($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (!$UNB['LoginUserID']) return false;
	if (!$id) $id = $UNB['LoginUserID'];

	return $this->db->RemoveRecord('User=' . $id, 'PollUsers');
}

// -------------------- Thread watching --------------------

// Set watch mode for current user
//
// in Mode = (int) combination of flags. see UNB_NOTIFY_* constants in common.lib.php
// in id = (int) thread id, 0 for currently loaded thread
//
// returns (bool) success
//
function SetWatched($Mode = 1, $id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);
	if (!$id) $id = $this->ID;
	$Mode = intval($Mode);

	$currentmode = $this->db->FastQuery1st('ThreadWatch', 'Mode', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($currentmode === false && $Mode)
	{
		return $this->db->AddRecord(array('Thread' => $id, 'User' => $UNB['LoginUserID'], 'Mode' => $Mode), 'ThreadWatch');
	}
	else
	{
		if ($currentmode != $Mode)
		{
			return $this->db->ChangeRecord(array('Mode' => $Mode), 'User=' . $UNB['LoginUserID'] . ' AND Thread=' . $id, 'ThreadWatch');
		}
		else
		{
			return true;
		}
	}
}

// Get watch mode for current user
//
// in id = (int) thread id, 0: currently loaded thread
//
// returns (int) watch mode flags, see UNB_NOTIFY_* constants in common.lib.php
//
function IsWatched($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$mode = $this->db->FastQuery1st('ThreadWatch', 'Mode', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($mode === false) return false;
	return intval($mode);
}

// Get number of users that have a ThreadWatch line for this thread
//
// in id = (int) thread id, 0: currently loaded thread
//
function CountUsers($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return ($this->db->FastQuery1st('ThreadWatch', 'COUNT(*)', 'Thread=' . $id));
}

// Remove any ThreadWatch data for (this) thread
//
// in id = (int) thread id, 0: currently loaded thread
//
function RemoveAllThreadWatchs($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return $this->db->RemoveRecord('Thread=' . $id, 'ThreadWatch');
}

// Remove any ThreadWatch data for (current) user
//
// in id = (int) user id, 0: current user
//
function RemoveAllUserWatchs($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;
	if (!$id) $id = $UNB['LoginUserID'];

	// Clean parameters
	$id = intval($id);

	return $this->db->RemoveRecord('User=' . $id, 'ThreadWatch');
}

// -------------------- User read thread --------------------

// Set last-read time for current user
//
// in Date = (int) timestamp
// in id = (int) thread id, 0: currently loaded thread
// in force = (bool) also store an older timestamp than it is already stored
//
function SetLastRead($Date, $id = 0, $force = false)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$Date = intval($Date);
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$current = $this->db->FastQuery1st('ThreadWatch', 'LastRead', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($current === false)           // we haven't read this topic yet
	{
		return $this->db->AddRecord(array('Thread' => $id, 'User' => $UNB['LoginUserID'], 'LastRead' => $Date), 'ThreadWatch');
	}
	elseif ($current < $Date ||       // we read this topic further now than the last time
	        $force)                   // force update also if it's an earlier time (i.e. for marking as "unread")
	{
		return $this->db->ChangeRecord(array('LastRead' => $Date), 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID'], 'ThreadWatch');
	}
}

// Set last-viewed time for current user
//
// in Date = (int) timestamp
// in id = (int) thread id, 0: currently loaded thread
//
function SetLastViewed($Date, $id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$Date = intval($Date);
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$current = $this->db->FastQuery1st('ThreadWatch', 'LastViewed', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($current === false)
	{
		return $this->db->AddRecord(array('Thread' => $id, 'User' => $UNB['LoginUserID'], 'LastViewed' => $Date), 'ThreadWatch');
	}
	elseif ($current < $Date ||
	        $force)
	{
		return $this->db->ChangeRecord(array('LastViewed' => $Date), 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID'], 'ThreadWatch');
	}
}

// Get last read time for current user
//
// in id = (int) thread id, 0: currently loaded thread
//
function GetLastRead($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return 1;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$db = $this->db;
	return $db->FastQuery1st('ThreadWatch', 'LastRead', 'Thread=' . $id . ' AND User=' . $UNB['LoginUserID']);
}

// -------------------- User notify timestamp --------------------

// Set last-notify time for current user
//
// in Date = (int) timestamp
// in id = (int) thread id, 0: currently loaded thread
// in userid = (int) user id, 0: current user
//
function SetLastNotify($Date, $id = 0, $userid = 0)
{
	global $UNB;
	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	// Clean parameters
	$Date = intval($Date);
	$id = intval($id);
	$userid = intval($userid);

	if (!$id) $id = $this->ID;
	$current = $this->db->FastQuery1st('ThreadWatch', 'LastNotify', 'Thread=' . $id . ' AND User=' . $userid);
	if ($current === false)
	{
		return $this->db->AddRecord(array('Thread' => $id, 'User' => $userid, 'LastNotify' => $Date), 'ThreadWatch');
	}
	elseif ($current < $Date)
	{
		return $this->db->ChangeRecord(array('LastNotify' => $Date), 'Thread=' . $id . ' AND User=' . $userid, 'ThreadWatch');
	}
}

// Get last notify time for current user
//
// in id = (int) thread id, 0: currently loaded thread
// in userid = (int) user id, 0: current user
//
function GetLastNotify($id = 0, $userid = 0)
{
	global $UNB;
	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return 1;

	// Clean parameters
	$id = intval($id);
	$userid = intval($userid);

	if (!$id) $id = $this->ID;
	$db = $this->db;
	return $db->FastQuery1st('ThreadWatch', 'LastNotify', 'Thread=' . $id . ' AND User=' . $userid);
}

}  // class

// -------------------- Global functions --------------------

// Read threads into an array, regarding access rights
//
// Similar to IThread.FindArray but this respects access rights. If some of the
// first n threads cannot be accessed, some more are fetched from the database,
// until we have the specified number of threads. This function also filters out
// 'moved' type special threads.
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section. If unset, "LastPostDate DESC" is applied
// in limit = (int) number of threads to return. If unset, the first 10 threads are returned
// in showhidden = (bool) include hidden threads in this search
//
// returns (array(array)) all thread record rows
//
function UnbFindThreadsArray($where = null, $order = null, $limit = null, $showhidden = null)
{
	if (!isset($where)) $where = '';
	if (!isset($order)) $order = 'LastPostDate DESC';
	if (!isset($limit)) $limit = 10;
	if (!isset($showhidden)) $showhidden = false;

	if ($where != '') $where = ' AND (' . $where . ')';

	// Fetch $limit topics
	$iter = 0;
	$thread = new IThread;
	$out = array();
	while (sizeof($out) < $limit &&
	       ($res = $thread->FindArray(
	                 'NOT (Options & ' . UNB_THREAD_MOVED . ')' . $where,
	                 $order,
	                 ($iter * $limit) . ',' . $limit,
	                 $showhidden)))
	{
		foreach ($res as $t)
		{
			// Skip thread if no access
			if (!UnbCheckRights('viewforum', $t['Forum'], $t['ID'])) continue;

			$out[] = $t;
			if (sizeof($out) >= $limit) break;
		}
		$iter++;
	}

	return $out;
}

// Same as UnbFindThreadsArray but returns an array of IThread objects instead.
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (int) number of threads to return
// in showhidden = (bool) include hidden threads in this search
//
// returns (array(IThread)) all thread objects
//
function UnbFindThreadsObjects($where = null, $order = null, $limit = null, $showhidden = null)
{
	$ta = UnbFindThreadsArray($where, $order, $limit, $showhidden);
	$out = array();
	foreach ($ta as $t)
	{
		$thread = new IThread;
		$thread->LoadFromRecord($t);
		$out[] = $thread;
	}
	return $out;
}

?>
