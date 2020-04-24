<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// forum.lib.php
// Forum Library, provides the IForum interface

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/thread.lib.php');

define('UNB_FORUM_CATEGORY', 1);   // Forum is a category
define('UNB_FORUM_WEBLINK', 2);    // Forum is a web link

// Represents a forum and offers methods to operate on it
//
class IForum
{

// -------------------- Public variables --------------------

// -------------------- Private variables --------------------

var $db;
var $finddb;

var $ID = 0;
var $Sort = 0;
var $Parent = 0;
var $Name = '';
var $Flags = 0;
	//	1	Category
	//	2	Web-Link
var $Description = '';
var $Link = '';

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
	$this->Sort = 0;
	$this->Parent = 0;
	$this->Name = '';
	$this->Flags = 0;
	$this->Description = '';
	$this->Link = '';
}

// -------------------- Find --------------------

// Load the first subforum
//
// in id = (int) parent forum id
//
// returns (bool) success
//
function GetChild($id)
{
	// Clean parameters
	$id = intval($id);

	if (PHP5) eval('$this->finddb = clone $this->db;');
	else      $this->finddb = $this->db;

	$record = $this->finddb->FastQuery('Forums', '*', 'Parent=' . $id, 'Sort');
	return $this->LoadFromRecord($record);
}

// Load the next subforum from a previous GetChild call
//
// returns (bool) success
//
function GetNextChild()
{
	$record = $this->finddb->GetRecord();
	return $this->LoadFromRecord($record);
}

// Get all subforums
//
// in id = (int) parent forum id
// in showhidden = (bool) include hidden forums in this search
//
// returns array(forumRecord, ...) of all subforums
// The returned forum records contain all forum columns, the ChildCount and optionally the Hidden flag
//
function GetChildA($id, $showhidden = false)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	$table = array(
		array('', 'Forums', 'f1', ''),
		array('LEFT', 'Forums', 'f2', 'f2.Parent = f1.ID'));
	if (!$showhidden)
		$table[] = array('LEFT', 'UserForumFlags', 'uff', 'uff.User = ' . $UNB['LoginUserID'] . ' AND uff.Forum = f1.ID');

	$fields = 'f1.*, COUNT(f2.ID) AS ChildCount';
	if (!$showhidden)
		$fields .= ', (uff.Flags & ' . UNB_UFF_HIDE . ') AS Hidden';

	$where = 'f1.Parent=' . $id;
	if (!$showhidden)
		$where .= ' AND (uff.Flags IS NULL OR NOT (uff.Flags & ' . UNB_UFF_HIDE . '))';

	return $this->db->FastQueryArray(
		/*table*/ $table,
		/*fields*/ $fields,
		/*where*/ $where,
		/*order*/ 'f1.Parent, Sort',
		/*limit*/ '',
		/*group*/ 'f1.ID');

	#return $this->db->FastQueryArray('Forums', '*', "Parent=$id", 'Sort');
}

// Get a list of forums matching the query
//
// in where = (string) SQL WHERE section
// in order = (string) SQL ORDER BY section
// in limit = (string) SQL LIMIT section
//
// returns array(forumID) of all found forums
//
function GetListArray($where = '', $order = '', $limit = '')
{
	return $this->db->FastQuery1stArray('Forums', 'ID', $where, $order, $limit);
}

// Count forums mathing the query
//
// in where = (string) SQL WHERE section. "" counts all forums
//
function Count($where = '')
{
	return $this->db->FastQuery1st('Forums', 'COUNT(*)', $where);
}

// -------------------- Read access --------------------

// Load a forum into this object. Uses the global forum records cache.
//
// in id = (int) forum id to load
//
// returns (bool) success
//
function Load($id)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (array_key_exists($id, $UNB['ForumCache']))
	{
		$record = $UNB['ForumCache'][$id];
		$add = false;
	}
	else
	{
		$record = $this->db->FastQuery('Forums', '*', 'ID=' . $id);
		$add = true;
	}

	return $this->LoadFromRecord($record, $add);
}

// Load a forum from a given record into this object.
// Can load results from array find functions.
//
// in record = (array) forum record row
// in add = (bool) update the global forum records cache
//
// returns (bool) success
//
function LoadFromRecord($record, $add = false)
{
	global $UNB;

	if ($record)
	{
		$this->ID = intval($record['ID']);
		$this->Sort = intval($record['Sort']);
		$this->Parent = intval($record['Parent']);
		$this->Name = strval($record['Name']);
		$this->Flags = intval($record['Flags']);
		$this->Description = strval($record['Description']);
		$this->Link = strval($record['Link']);

		if ($add) $UNB['ForumCache'][$this->ID] = $record;

		return true;
	}
	else
	{
		$this->Reset();
		return false;
	}
}

// Get this object's forum id
//
function GetID()
{
	return $this->ID;
}

// Get this object's forum sort index
//
function GetSort($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Sort;
}

// Get this object's parent forum id
//
function GetParent($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Parent;
}

// Get this object's forum name
//
function GetName($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Name;
}

// Get this object's category flag
//
function IsCategory($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Flags & UNB_FORUM_CATEGORY;
}

// Get this object's weblink flag
//
function IsLink($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Flags & UNB_FORUM_WEBLINK;
}

// Get this object's forum description
//
function GetDescription($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Description;
}

// Get this object's forum weblink target
//
function GetLink($id = 0)
{
	if ($id != 0 && !$this->Load($id)) return false;
	return $this->Link;
}

// -------------------- Write access --------------------

// Add a new forum to the database
//
// in Sort = (int) sort index
// in Parent = (int) parent forum id
// in Name = (string) forum name
// in Flags = (int) forum flags/options, see UNB_FORUM_* constants at the top of this file
// in Description = (string) forum description (subtitle)
// in Link = (string) weblink target
//
// returns (bool) success
//
function Add($Sort, $Parent, $Name, $Flags, $Description, $Link = '')
{
	// Clean parameters
	$Sort = intval($Sort);
	$Parent = intval($Parent);
	$Name = trim(strval($Name));
	$Flags = intval($Flags);
	$Description = trim(strval($Description));
	$Link = trim(strval($Link));

	$max = intval($this->db->FastQuery1st('Forums', 'MAX(ID)'));

	$this->ID = $max + 1;
	$this->Sort = $Sort;
	$this->Parent = $Parent;
	$this->Name = $Name;
	$this->Flags = $Flags;
	$this->Description = $Description;
	$this->Link = $Link;

	return $this->db->AddRecord(array(
			'ID' => $this->ID,
			'Sort' => $this->Sort,
			'Parent' => $this->Parent,
			'Name' => $this->Name,
			'Flags' => $this->Flags,
			'Description' => $this->Description,
			'Link' => $this->Link
		), 'Forums');
}

// Set the forum's sort index
//
// in newsort = (int) new sort index
// in updateall = (bool) update all other forums' sort index accordingly
//
// returns (bool) success
//
function SetSort($newsort, $updateall = false)
{
	// Clean parameters
	$newsort = intval($newsort);

	$oldsort = $this->Sort;
	$newsort = intval($newsort);

	if ($oldsort < $newsort)
	{
		$pre = $this->db->tblprefix;
		if ($updateall) $this->db->ChangeRecord('Sort = Sort - 1', 'Parent = ' . $this->Parent . ' AND Sort > ' . $oldsort . ' AND Sort <= ' . $newsort, 'Forums');
		return $this->db->ChangeRecord('Sort = ' . $newsort, 'ID = ' . $this->ID, 'Forums');
	}
	else
	{
		$pre = $this->db->tblprefix;
		if ($updateall) $this->db->ChangeRecord('Sort = Sort + 1', 'Parent = ' . $this->Parent . ' AND Sort < ' . $oldsort . ' AND Sort >= ' . $newsort, 'Forums');
		return $this->db->ChangeRecord('Sort = ' . $newsort, 'ID = ' . $this->ID, 'Forums');
	}
}

// Set the forum's parent forum id
//
// in Parent = (int) new parent forum id
// in id = (int) forum to modify, null: currently loaded forum
//
// returns (bool) success
//
function SetParent($Parent = null, $id = null)
{
	global $UNB;

	// Clean parameters
	if (isset($Parent)) $Parent = intval($Parent);
	if (isset($id)) $id = intval($id);

	$arr = array();

	// Collect changes
	if (isset($Parent))
	{
		if (!isset($id)) $this->Parent = $Parent;
		$arr['Parent'] = $Parent;
	}

	if (!$arr) return true;
	if (!isset($id)) $id = $this->ID;
	unset($UNB['ForumCache'][$id]);
	return $this->db->ChangeRecord($arr, 'ID=' . $id, 'Forums');
}

// Set the forum's category flag [deprecated]
//
// in Cat = (bool) new category flag
//
// returns (bool) success
//
function SetCategory($Cat)
{
	global $UNB;

	$this->Flags = ($this->Flags & ~UNB_FORUM_CATEGORY) | ($Cat ? UNB_FORUM_CATEGORY : 0);

	unset($UNB['ForumCache'][$this->ID]);
	return $this->db->ChangeRecord(array('Flags' => $this->Flags), 'ID=' . $this->ID, 'Forums');
}

// Set the forum's name
//
// in Name = (string) new name
// in id = (int) forum to modify, null: currently loaded forum
//
// returns (bool) success
//
function SetName($Name = null, $id = null)
{
	global $UNB;

	// Clean parameters
	if (isset($Name)) $Name = strval($Name);
	if (isset($id)) $id = intval($id);

	$arr = array();

	// Collect changes
	if (isset($Name))
	{
		if (!isset($id)) $this->Name = $Name;
		$arr['Name'] = $Name;
	}

	if (!$arr) return true;
	if (!isset($id)) $id = $this->ID;
	unset($UNB['ForumCache'][$id]);
	return $this->db->ChangeRecord($arr, 'ID=' . $id, 'Forums');
}

// Update a forum in the database
//
// in ID = (int) forum id to update
// in Sort = (int) new sort index
// in Parent = (int) new parent forum id
// in Name = (string) new forum name
// in Flags = (int) new forum flags/options, see UNB_FORUM_* constants at the top of this file
// in Description = (string) new forum description (subtitle)
// in Link = (string) new weblink target
//
// returns (bool) success
//
function Change($ID, $Sort, $Parent, $Name, $Flags, $Description, $Link = '')
{
	global $UNB;

	// Clean parameters
	$ID = intval($ID);
	$Sort = intval($Sort);
	$Parent = intval($Parent);
	$Name = trim(strval($Name));
	$Flags = intval($Flags);
	$Description = trim(strval($Description));
	$Link = trim(strval($Link));

	if (!$this->Load($ID)) return false;

	$this->Sort = $Sort;
	$this->Parent = $Parent;
	$this->Name = $Name;
	$this->Flags = $Flags;
	$this->Description = $Description;
	$this->Link = $Link;

	unset($UNB['ForumCache'][$ID]);
	return $this->db->ChangeRecord(array(
			'Sort' => $this->Sort,
			'Parent' => $this->Parent,
			'Name' => $this->Name,
			'Flags' => $this->Flags,
			'Description' => $this->Description,
			'Link' => $this->Link
		), 'ID=' . $ID, 'Forums');
}

// Remove a forum with all subforums and related content
// (threads, posts, polls, category states, votes, notifications)
//
// in id = (int,*) forum id to remove. 0 for currently loaded forum
//
// returns (bool) success
//
function Remove($id = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	$id = intval($id);   // important for thread.FindArray
	if ($id <= 0) return false;

	// move all child forums one level up!
	$myParent = $this->GetParent($id);
	$subforum = new IForum;
	if ($forums = $this->GetChildA($id)) foreach ($forums as $rec)
	{
		#if (!$this->Remove($rec['ID'])) return false;
		if (!$subforum->SetParent($myParent, $rec['ID'])) return false;
	}

	// remove all threads in this forum
	$thread = new IThread;
	if ($threads = $thread->FindArray($id)) foreach ($threads as $rec)
	{
		if (!$thread->RemoveAllPosts($rec['ID'])) return false;
	}

	$UNB['LoginUser']->RemoveAllForumThreadFlags($id);

	unset($UNB['ForumCache'][$id]);
	return $this->db->RemoveRecord('ID=' . $id, 'Forums');
}

// -------------------- Category collapsed state --------------------

// Set category collapsed state for current user [deprecated]
//
// in State = (int) 0: Collapsed, 1: Expanded
// in id = (int) forum id, 0: currently loaded forum
//
function SetCollapsed($State, $id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$State = intval($State);
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return $UNB['LoginUser']->SetForumFlag($id, UNB_UFF_COLLAPSE, $State == 0);
}

// Get category collapsed state for current user [deprecated]
//
// in id = (int) forum id, 0: currently loaded forum
//
function IsCollapsed($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return 1;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return !$UNB['LoginUser']->GetForumFlag($id, UNB_UFF_COLLAPSE);
}

// Are there new (unread) posts in (this) forum for this user
//
// in id = (int) forum id, 0: this forum
//
// returns (bool) new posts
//
// uses $UNB['NewForums'] from main.inc.php:CountForums()
//
function IsNew($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;

	if (true || $this->IsCategory())   // enable this loop for all forums, not only categories
	{
		// categories scan through all subforums
		if ($UNB['Subforums'][$id]) foreach ($UNB['Subforums'][$id] as $forum) if (UnbCheckRights('viewforum', $forum))
		{
			if ($UNB['NewForums'][$forum] > 0) return true;
		}
	}

	// no category
	if (UnbCheckRights('viewforum', $id)) return ($UNB['NewForums'][$id] > 0);

	return false;
}

// -------------------- Forum watching --------------------

// Set watch mode for current user
//
// in Mode = (int) combination of flags. see UNB_NOTIFY_* constants in common.lib.php
// in id = (int) forum id, 0: currently loaded forum
// in Flags = (int) 1: recursive [not implemented yet]
//
// returns (bool) success
//
function SetWatched($Mode = 1, $id = 0, $Flags = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$Mode = intval($Mode);
	$id = intval($id);
	$Flags = intval($Flags);

	if (!$id) $id = $this->ID;
	$current_a = $this->db->FastQuery('ForumWatch', 'Mode, Flags', 'Forum=' . $id . ' AND User=' . $UNB['LoginUserID']);
	if ($current_a === false && $Mode)
	{
		return $this->db->AddRecord(array('Forum' => $id, 'User' => $UNB['LoginUserID'], 'Mode' => $Mode, 'Flags' => $Flags), 'ForumWatch');
	}
	else
	{
		if ($current_a['Mode'] != $Mode || $current_a['Flags'] != $Flags)
		{
			return $this->db->ChangeRecord(array('Mode' => $Mode, 'Flags' => $Flags), 'User=' . $UNB['LoginUserID'] . ' AND Forum=' . $id, 'ForumWatch');
		}
		else
		{
			return true;
		}
	}
}

// Get watch mode for current user
//
// in id = (int) forum id, 0: currently loaded forum
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
	return ($this->db->FastQuery1st('ForumWatch', 'Mode', 'Forum=' . $id . ' AND User=' . $UNB['LoginUserID']));
}

// Get number of users that have a ForumWatch line for this forum
//
// in id = (int) forum id, 0: currently loaded forum
//
function CountUsers($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return ($this->db->FastQuery1st('ForumWatch', 'COUNT(*)', 'Forum=' . $id));
}

// Remove any ForumWatch data for (this) forum
//
// in id = (int) forum id, 0: currently loaded forum
//
function RemoveAllForumWatchs($id = 0)
{
	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $this->ID;
	return $this->db->RemoveRecord('Forum=' . $id, 'ForumWatch');
}

// Remove any ForumWatch data for (current) user
//
// in id = (int) forum id, 0: current user
//
function RemoveAllUserWatchs($id = 0)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return false;

	// Clean parameters
	$id = intval($id);

	if (!$id) $id = $UNB['LoginUserID'];
	return $this->db->RemoveRecord('User=' . $id, 'ForumWatch');
}

// -------------------- User notify timestamp --------------------

// Set last-notify time for current user
//
// in Date = (int) timestamp
// in id = (int) forum id, 0: currently loaded forum
// in userid = (int) user id, 0: currently logged in user
//
// returns (bool) success
//
function SetLastNotify($Date, $id = 0, $userid = 0)
{
	global $UNB;

	// Clean parameters
	$Date = intval($Date);
	$id = intval($id);
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	if (!$id) $id = $this->ID;
	$current = $this->db->FastQuery1st('ForumWatch', 'LastNotify', 'Forum=' . $id . ' AND User=' . $userid);
	if ($current === false)
	{
		return $this->db->AddRecord(array('Forum' => $id, 'User' => $userid, 'LastNotify' => $Date), 'ForumWatch');
	}
	elseif ($current < $Date)
	{
		return $this->db->ChangeRecord(array('LastNotify' => $Date), 'Forum=' . $id . ' AND User=' . $userid, 'ForumWatch');
	}
}

// Get last notify time for current user
//
// in id = (int) forum id, 0: currently loaded forum
// in userid = (int) user id, 0: currently logged in user
//
// returns (int) timestamp
//
function GetLastNotify($id = 0, $userid = 0)
{
	global $UNB;

	// Clean parameters
	$id = intval($id);
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return 1;

	if (!$id) $id = $this->ID;
	$db = $this->db;
	return $db->FastQuery1st('ForumWatch', 'LastNotify', 'Forum=' . $id . ' AND User=' . $userid);
}

}  // class

?>
