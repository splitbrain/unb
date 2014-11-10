<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// common_out.lib.php
// Common Library

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Global Count*() arrays, e.g. an entire Post-count list will be stored to an array from one DB query
$UNB['PostsPerForum'] = array();
$UNB['ThreadsPerForum'] = array();
$UNB['NewForums'] = array();
$UNB['PostsByUser'] = array();
$UNB['PostsByThread'] = array();

// Load other libraries
require_once(dirname(__FILE__) . '/post.lib.php');
require_once(dirname(__FILE__) . '/announce.lib.php');
require_once(dirname(__FILE__) . '/mail.lib.php');

// -------------------- HTML output --------------------

// List announcements for a Forum
//
// in forumid = (int) Forum ID
// in threadid = (int) Thread ID (> 0), if we're in a thread
// in page = (int) page number in the thread
// in readonly = (bool) don't show read/add/showall links (for display on search results pages)
//
function UnbListAnnounces($forumid, $threadid = 0, $page = 0, $readonly = false)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$forumid = intval($forumid);
	$threadid = intval($threadid);
	$page = intval($page);

	$announce = new IAnnounce;
	$newonly = ($_GET['allannounces'] != 1) && ($UNB['LoginUserID'] > 0);   // only show announcements not marked as read?
	$prevIsImportant = false;  // true from the first important announcement on
	$annlist = array();
	$count = 0;
	if ($announce->Find($forumid, $newonly, $threadid > 0)) do
	{
		$tpitem = UnbShowAnnounce($announce, false, ($threadid > 0 ? -$threadid : $forumid), $page, $readonly);

		if ($prevIsImportant &&
		    !$announce->IsImportant())
		{
			// This is the first announcement that is not important
			$prevIsImportant = false;
			$tpitem['importantDelimiter'] = true;
		}
		elseif ($announce->IsImportant())
		{
			$prevIsImportant = true;
		}

		$annlist[] = $tpitem;
		$count++;
	}
	while ($announce->FindNext());

	if ($count > 0)
	{
		$annlist[0]['firstitem'] = true;
		$annlist[$count - 1]['lastitem'] = true;
	}
	$TP['announcelist'] = $annlist;

	if (!$threadid &&
	    !$readonly &&
	    UnbCheckRights('editannounce', $forumid))
	{
		$TP['announcelistActionNewLink'] = UnbLink('@post', 'announce=-1&forum=' . $forumid, true);
	}
	if (!$threadid &&
	    !$readonly &&
	    $announce->Count($forumid, $threadid > 0) > $count)
	{
		$TP['announcelistActionShowAllLink'] = UnbLink('@main', array('id' => $forumid, 'allannounces' => true), true);
	}
}

// Writes <option> HTML code for all forums, recursively, into $output
// This belongs to UnbEditThread, but is also used by other functions
//
// in curr = (int) currently selected forum id
// in disable_cats = (bool) make category options disabled (not for selecting)
// in parent = (int) recursion variable
// in level = (int) recursion variable
// in nextAvailBlock = (bool) recursion variable
// in noWebLinks = (bool) skip web link "forums"
// in showhidden = (bool) include hidden forums in this search
//
function UnbListForumsRec($curr, $disable_cats = false, $parent = 0, $level = 0, $nextAvailBlock = true, $noWebLinks = false, $showhidden = false)
{
	global $output, $UNB;

	$forum = new IForum;

	// This flag determines how to draw the forums tree 'lines'.
	// Unicode produces a real tree structure, but needs the client to have a unicode font installed.
	// Non-unicode uses simple characters.
	$treeStyle = rc('forums_tree_style');

	if ($treeStyle == 'unicode')
	{
		$pad = '';
		for ($n = 0; $n < $level - 1; $n++) $pad .= ($nextAvailBlock ? '&#x2502; &nbsp; ' : '&nbsp;&nbsp; &nbsp; ');
		$pad2 = $pad;
		if ($level > 0)
		{
			$pad2 .= '&#x2514;&#x2500; ';
			$pad .= '&#x251C;&#x2500; ';
		}
		$s1 = '<span class="light">';
		$s2 = '</span>';
		$pad = $s1 . $pad . $s2;
		$pad2 = $s1 . $pad2 . $s2;
	}
	else if ($treeStyle == 'nolines')
	{
		$pad2 = $pad = str_repeat('&nbsp; &nbsp; ', $level);
	}
	else if ($treeStyle == 'dots')
	{
		$pad2 = $pad = str_repeat('&middot; &nbsp; ', $level);
	}
	else if ($treeStyle == 'hlines')
	{
		$pad2 = $pad = str_repeat('&mdash; ', $level);
	}
	else
	{
		$pad2 = $pad = str_repeat('| &nbsp; ', $level);
	}

	$forums = $forum->GetChildA($parent, $showhidden);
	if (!is_array($forums)) return;
	$count = sizeof($forums);
	for ($n = 0; $n < $count; $n++)
	{
		$forum->LoadFromRecord($forums[$n]);
		if ($noWebLinks && $forum->IsLink()) continue;

		$id = $forum->GetID();
		if (!UnbCheckRights('viewforum', $id))
		{
			$nextAvail = $n < $count - 1;
			continue;
		}

		$classname = 'forum';
		if ($forum->IsCategory()) $classname = 'category';

		$isCat = $forum->IsCategory();
		$name = $forum->GetName();
		$nextAvail = $n < $count - 1;

		$output .= '<option value="' . $id . '"' . ($curr == $id ? ' selected="selected"' : '') .
			' class="' . $classname . '"' .
			($disable_cats && $isCat ? ' disabled="disabled"' : '') .
			'>' . ($nextAvail ? $pad : $pad2) . t2h(' ' . $name) . '</option>';

		if ($forums[$n]['ChildCount'] > 0)
			UnbListForumsRec($curr, $disable_cats, $id, $level + 1, $nextAvail, $noWebLinks, $showhidden);
	}
}

// More abstract version to UnbListForumsRec
// Recursively reads all forum IDs from $parent on into the global $forums array.
//
// in parent = (int) forum id, recursion variable
// in level = (int) recursion variable
//
function UnbGetForumsRec($parent = 0, $level = 0)
{
	global $forums;

	// Clean parameters
	$parent = intval($parent);
	$level = intval($level);

	$forum = new IForum;
	if (!$level) $forums = array();

	if ($forum->GetChild($parent)) do
	{
		if (!UnbCheckRights('viewforum', $forum->GetID())) continue;
		array_push($forums, $forum->GetID());
		UnbGetForumsRec($forum->GetID(), $level + 1);
	}
	while ($forum->GetNextChild());
}

// List threads
//
// in where = (int) forum to list threads from
//            (string) SQL restriction of threads to show
// in page = (int) page number to display (-1: last page)
// in editthread = (int) thread ID to edit
// in get = (array) GET params to add to page links (i.e. search definition)
// in order = (string) SQL ORDER parameter
// in limit = (string) SQL LIMIT parameter
// in write = (bool) enable "write access" at all? this means:
//                   icons for thread editing. recommended to false on search queries etc
// in showhidden = (bool) show all hidden threads this time
//
function UnbListThreads($where, $page = 1, $editthread = 0, $get = null, $order = '', $limit = '', $write = true, $showhidden = false)
{
	global $output, $toplevel, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$page = intval($page);
	$editthread = intval($editthread);

	$threads_per_page = rc('threads_per_page');
	$mark_own_posts = rc('own_posts_in_threadlist');
	$advanced_counter = rc('advanced_thread_counter');
	$show_view_count = rc('count_thread_views');
	$TP['threadlistShowViewCount'] = $show_view_count;
	$TP['threadlist'] = array();

	if (is_int($where) && !UnbCheckRights('viewforum', $where)) return false;

	UnbCountThreadPosts();

	$forum = new IForum;
	$thread = new IThread;

	$user = new IUser;
	$post = new IPost;

	if (is_int($where))
	{
		$forumid = $where;
		$show_forum = false;
	}
	elseif (is_string($where))
	{
		$forumid = -1;
		$show_forum = true;   // is a string -> is a WHERE definition (over all Forums) -> display Thread's Forum
	}
	else
	{
		return false;   // input type not supported
	}
	$TP['threadlistShowForum'] = $show_forum;

	// ---------- Page selection ----------
	$thread_count = $thread->Count($where, $showhidden);
	$params = array(
		'id' => $forumid,
		'showhidden_t' => ($showhidden ? 1 : null));
	if (isset($get)) $params = array_merge($params, $get);
	$params = array_merge($params, array('##' => 'threadlist'));
	if ($limit == '')
		$TP['threadlistPages'] = UnbPageSelection($thread_count, $threads_per_page, $page, $params);
	else
		$TP['threadlistPages'] = '';

	if ($forumid > 0)
	{
		UnbSetRelLink('up', UnbLink('@this', 'id=' . $forum->GetParent($forumid) . $url_sh . $get));
	}

	// -------------------- Threads list --------------------
	$output = '';

	if ($editthread)
		$TP['threadlistEditformLink'] = UnbLink('@this', 'id=' . $toplevel, true);

	// ----- Look for threads on this page or accept $limit parameter -----
	$legend_icon_new = false;
	$legend_icon_hot = false;
	$legend_icon_important = false;
	$legend_icon_closed = false;
	$legend_icon_own = false;
	$legend_icon_moved = false;

	$start_num = 0;
	if ($limit == '')
	{
		if ($threads_per_page)
		{
			$start_num = ($page - 1) * $threads_per_page;
			$limit = $start_num . ',' . $threads_per_page;
		}
		else
			$limit = '';
	}
	$defaultOrder = false;
	if ($order == '')
	{
		$defaultOrder = true;
		$order = ($get == '' ? '(Options & ' . UNB_THREAD_IMPORTANT . ') DESC, ' : '') . 'LastPostDate DESC';
	}
	$threads = $thread->FindArray($where, $order, $limit, $showhidden);

	// read all threads' first posts' users into the cache
	$users = array();
	foreach($threads as $record)
		if (!in_array($record['User'], $users) && $record['User'] > 0 && !isset($UNB['UserCache'][$record['User']]))
			array_push($users, $record['User']);
	UnbBuildEntireUserCache($users);

	$a = array();
	foreach($threads as $record) array_push($a, $record['ID']);
	$user_reads = UnbReadUserReads($a);
	$last_posts = UnbGetLastPost($a);
	if ($mark_own_posts) $users_posts = UnbFindUsersPosts($a);
	if ($advanced_counter && $show_view_count) $count_userviews = UnbCountUserViews($a);
	if ($advanced_counter) $count_replyusers = UnbCountReplyUsers($a);

	$count = 0;                // number of displayed threads
	$countNormal = 0;          // number of displayed threads that are not important
	$prevIsOld = false;        // true from the first thread on that is older (LastPostDate) than the user's last login
	$prevIsImportant = false;  // true from the first important thread on
	$new_threads = 0;
	foreach($threads as $record)
	{
		$tpitem = array();
		$impclass = '';

		$thread->LoadFromRecord($record);   // load thread as if it came directly from the database record
		$tpitem['id'] = $thread->GetID();
		$tpitem['defaultOrder'] = $defaultOrder;
		$tpitem['num'] = $count;
		$tpitem['numAll'] = $start_num + $count;

		if (!UnbCheckRights('viewforum', $thread->GetForum(), $thread->GetID())) continue;

		// is this a 'moved' thread that expired?
		if ($thread->IsMovedExpired())
		{
			$thread->RemoveAllPosts();
			continue;
		}

		if (!$count)
		{
			// If the first thread in the list is not important (and thus sorted out of date),
			// remember we already have an 'old' thread from the beginning on
			if (!$thread->IsImportant())
				$prevIsOld = $thread->GetLastPostDate() < $UNB['LoginUser']->GetLastLogout();

			// Remember if the first thread displayed is important
			$firstIsImportant = $thread->IsImportant();
		}

		if ($forumid != -1 &&
		    $prevIsImportant &&
		    !$thread->IsImportant() &&
		    $defaultOrder)
		{
			// This is the first thread that is not important
			$prevIsImportant = false;
			$tpitem['importantDelimiter'] = true;
		}
		elseif ($thread->IsImportant())
		{
			$prevIsImportant = true;
			$impclass = 'important';
		}

		if (($countNormal || $firstIsImportant) &&            // not the first normal (=not important) thread displayed
		                                                      // OR first thread displayed was important
		                                                      //    (we need a distance then, too)
		    !$prevIsOld &&                                    // previous isn't already old
		    (!$thread->IsImportant() || $forumid == -1) &&    // this is not important OR importants are sorted in
		    $thread->GetLastPostDate() < $UNB['LoginUser']->GetLastLogout() /*&&   // this is an old thread
		    $defaultOrder*/)
		{
			// This is the first post that is older (LastPostDate) than the user's last login
			$prevIsOld = true;

			if (($countNormal || !$firstIsImportant) &&   // not the first normal thread displayed
			                                              // OR first thread was not important
			                                              // MEANS: if the first was important, we need a normal in between
			    $forumid == -1 &&                         // only show this space for search results, not forums
			    stristr($order, 'LastPostDate'))          // LastPostDate must play a role in sorting
			{
				// Only show the message/space if there was a normal post already OR the first one wasn't important
				$tpitem['oldDelimiter'] = true;
			}
		}

		//$lastpost = UnbGetLastPost("Thread=" . $thread->GetID());
		$lastpost = $last_posts[$thread->GetID()];
		//$lastread = $thread->GetLastRead();
		$lastread = $user_reads[$thread->GetID()]['LastRead'];

		$target = $thread->GetID();
		if ($thread->IsMoved()) $target = intval($thread->GetQuestion());

		// ----- ICON -----
		$img = 'thread';
		$title = $UNB_T['topic'];
		$jmp_new = '';

		$flag_moved = $thread->IsMoved();
		$flag_new = $lastpost['Date'] > $lastread && $UNB['LoginUserID'];
		$flag_own = $mark_own_posts && $UNB['LoginUserID'] > 0 && in_array($thread->GetID(), $users_posts);
		$flag_closed = $thread->IsClosed();
		$flag_imp = $thread->IsImportant();
		$flag_hot = (rc('hot_thread_posts') && UnbGetPostsByThread($thread->GetID()) > rc('hot_thread_posts') ||
		             rc('hot_thread_views') && $thread->GetViews() > rc('hot_thread_views'));

		if ($flag_moved) $flag_new = $flag_own = $flag_closed = $flag_imp = false;
		#if ($flag_imp) $flag_own = false;
		if ($flag_imp) $flag_hot = false;

		if ($flag_moved)
		{
			$img .= '_moved';
			$title = $UNB_T['img.moved thread'];
			$legend_icon_moved = true;
		}

		if ($flag_imp)
		{
			$title = $UNB_T['img.important thread'];
			$legend_icon_important = true;
		}

		if ($flag_closed)
		{
			$title = $UNB_T['img.closed thread'];
			$legend_icon_closed = true;
		}

		if ($flag_new)
		{
			$img .= '_new';
			$title .= ' (' . $UNB_T['img.new posts'] . ')';
			$new_threads++;
			$legend_icon_new = true;

			// direct link to 1st unread post, only if user has already read something there before
			if ($lastread)
			{
				$jmp_new = UnbMakePostLink($thread->FirstUnreadPost($lastread), 0, true) . '<img ' . $UNB['Image']['unread'] . ' title="' . $UNB_T['img.goto unread post'] . '" />&nbsp;';
			}
		}

		if ($flag_own)
		{
			$img .= '_own';
			$title .= ' (' . $UNB_T['img.own posts'] . ')';
			$legend_icon_own = true;
		}

		if ($flag_closed)
		{
			$img .= '_closed';
		}

		if ($flag_imp)
		{
			$img .= '_important';
		}
		else if ($flag_hot)
		{
			$img .= '_hot';
			//$title = $UNB_T['hot_thread'];
			$legend_icon_hot = true;
		}

		$tpitem['impclass'] = $impclass;
		$tpitem['editThis'] =
			($thread->GetID() == $editthread && UnbCheckRights('editpost', $thread->GetForum(), $thread->GetID(), 0));
		$tpitem['linkThreadIcon'] = UnbLink('@thread', 'id=' . $target, true);
		$tpitem['image'] = $UNB['Image'][$img];
		$tpitem['imageTitle'] = $title;

		// ----- SUBJECT -----
		$data = array(
			'thread' => &$thread,
			'threadid' => $thread->GetID(),
			'output' => '');
		UnbCallHook('threadlist.presubject', $data);
		$tpitem['addPreSubject'] = $data['output'];

		$tpitem['isMoved'] = $thread->IsMoved();
		$tpitem['isOwn'] = $flag_own;
		$tpitem['isClosed'] = $thread->IsClosed();
		$tpitem['isImportant'] = $thread->IsImportant();
		$tpitem['isHot'] = $flag_hot;
		if ($jmp_new)
			$tpitem['linkThread'] = $jmp_new;
		else
			$tpitem['linkThread'] = '<a href="' . UnbLink('@thread', 'id=' . $target, true) . '">';
		$tpitem['flagNew'] = $flag_new;
		$tpitem['subject'] = t2h($thread->GetSubject());

		if (rc('posts_per_page') > 0 && UnbGetPostsByThread($thread->GetID()) > rc('posts_per_page'))
		{
			$boundary = 3;

			$thread_page_count = ceil(UnbGetPostsByThread($thread->GetID()) / rc('posts_per_page'));
			$pagesStr = ' &nbsp; <img ' . $UNB['Image']['page'] . ' title="' . $UNB_T['select page'] . '" />';
			$state = 0;
			for ($m = 1; $m <= $thread_page_count; $m++)
			{
				// show pages if they were the only one replaced by '...'
				$med = ($m == $boundary + 1 && $m == $thread_page_count - ($boundary - 1) - 1);

				if ($m <= $boundary || $med || $m > $thread_page_count - $boundary)
				{
					$pagesStr .= '<a href="' . UnbLink('@thread', 'id=' . $thread->GetID() . '&page=' . $m, true) . '"> ' . $m . '</a>';
				}
				else
				{
					if ($state < 1) { $pagesStr .= ' ...'; $state = 1; }
				}
			}
			$tpitem['pages'] = $pagesStr;
		}
		if ($thread->IsMoved())
		{
			// Show the target forum where this thread was moved into
			$t_thread = new IThread;
			$target_name = '';
			if ($t_thread->Load($target))
			{
				$target_id = $t_thread->GetForum();
				$t_forum = new IForum;
				if ($t_forum->Load($target_id))
				{
					$target_name = $t_forum->GetName();
				}
			}
			if ($target_name)
			{
				$tpitem['movedForum'] = ' &nbsp; <small>(' . $UNB_T['forum'] . ': <a href="' . UnbLink('@main', 'id=' . $target_id, true) . '">' . t2h($target_name) . '</a>)</small> ';
			}
			else
			{
				$tpitem['movedForum'] = ' &nbsp; <small>(' . $UNB_T['error.invalid thread id'] . ')</small> ';
			}
		}

		if ($flag_new)
		{
			// Count number of unread posts
			$countnew = $post->Count('Date > ' . intval($lastread) . ' AND Thread = ' . $thread->GetID());
			$tpitem['unreadCount'] = ' &nbsp;<small style="white-space: nowrap;">(<b class="new">' . $countnew . '</b> ' . $UNB_T['unread'] . ')</small>';
		}

		if ($thread->IsImportant() && $thread->HasPoll() && $thread->GetVotes())
			$tpitem['importantPoll'] = ' &nbsp; <small><b>' . $UNB_T['important poll'] . '</b></small>';
		elseif ($thread->IsImportant())
			$tpitem['importantThread'] = ' &nbsp; <small><b>' . $UNB_T['important thread'] . '</b></small>';
		elseif ($thread->HasPoll() && $thread->GetVotes())
			$tpitem['poll'] = ' &nbsp; <small><b>' . $UNB_T['poll'] . '</b></small>';

		$i = 0;
		if ($thread->HasAttach())
		{
			$tpitem['attachImage'] = ($i++ ? '' : ' &nbsp;') . ' <img ' . $UNB['Image']['attach'] . ' />';
		}
		if (rc('show_bookmarked_thread') && $thread->IsWatched() & UNB_NOTIFY_BOOKMARK)
		{
			$tpitem['bookmarkImage'] = ($i++ ? '' : ' &nbsp;') . ' <img ' . $UNB['Image']['bookmark']. ' title="' . $UNB_T['bookmark'] . '" />';
		}

		if (UnbCheckRights('editpost', $thread->GetForum(), $thread->GetID(), 0) && $write)
			$tpitem['linkEdit'] = ' &nbsp; <a href="' .
				UnbLink(
					'@main',
					array(
						'id' => $forumid,
						'page' => $page,
						'editthread' => $thread->GetID(),
						'#' => 'here'),
					true) .
				'" class="admin"><img ' . $UNB['Image']['edit'] . ' title="' . $UNB_T['thread.edit'] . '" /></a>';

		if ($UNB['LoginUser']->GetThreadFlag($thread->GetID(), UNB_UFF_HIDE))
		{
			$tpitem['hiddenImage'] = ($i++ ? ' &nbsp;' : '') . ' <img ' . $UNB['Image']['hide'] . ' title="' . $UNB_T['thread.advanced.hiding'] . '" />';
		}
		if ($UNB['LoginUser']->GetThreadFlag($thread->GetID(), UNB_UFF_IGNORE))
		{
			$tpitem['ignoredImage'] = ($i++ ? ' &nbsp;' : '') . ' <img ' . $UNB['Image']['ignore'] . ' title="' . $UNB_T['thread.advanced.ignoring'] . '" />';
		}

		$tpitem['desc'] = t2h($thread->GetDesc());

		// ----- FORUM -----
		if ($show_forum)
		{
			$forum = new IForum($thread->GetForum());

			$tpitem['linkForum'] = UnbLink('@main', 'id=' . $thread->GetForum(), true);
			$tpitem['forumName'] = t2h($forum->GetName());
			if ($forum->GetParent() > 0)
			{
				// Include parent forum name for better readability in complex forum hierarchies with equally-named subforums
				$parentforum = new IForum($forum->GetParent());
				$tpitem['forumName'] = t2h($parentforum->GetName()) . ', ' . $tpitem['forumName'];
			}

			if ($UNB['LoginUser']->GetForumFlag($thread->GetForum(), UNB_UFF_HIDE))
			{
				$tpitem['forumHiddenImage'] = '<img ' . $UNB['Image']['hide'] . ' title="' . $UNB_T['forum.advanced.hiding'] . '" />';
			}
			if ($UNB['LoginUser']->GetForumFlag($thread->GetForum(), UNB_UFF_IGNORE))
			{
				$tpitem['forumIgnoredImage'] = '<img ' . $UNB['Image']['ignore'] . ' title="' . $UNB_T['forum.advanced.ignoring'] . '" />';
			}
		}

		if (!$thread->IsMoved())
		{
			// ----- AUTHOR, DATE -----
			if (rc('display_thread_startdate'))
			{
				#$tpitem['startDate'] = UnbFriendlyDate($thread->GetDate(), 1, 3);
				// Use this instead to display thread start dates in a relative form:
				$tpitem['startDate'] = UnbFriendlyDate($thread->GetDate(), 2, 3, true, 4);
			}
			if ($user->Load($thread->GetUser()))
				$tpitem['startUser'] = '<a href="' . UnbLink('@cp', 'id=' . $user->GetID(), true) . '">' . t2h(str_limit($user->GetName(), 20, true)) . '</a>';
			else
				$tpitem['startUser'] = t2h(str_limit($thread->GetUserName(), 20, true));

			// ----- VIEWS -----
			if ($show_view_count)
			{
				$tpitem['views'] = $thread->GetViews();
				if ($advanced_counter)
					$tpitem['viewsUsers'] = $count_userviews[$thread->GetID()];
			}

			// ----- REPLIES -----
			$replies = UnbGetPostsByThread($thread->GetID()) - 1;
			$tpitem['replies'] = $replies;
			if ($advanced_counter)
				$tpitem['repliesUsers'] = $count_replyusers[$thread->GetID()];

			// ----- LAST POST -----
			if ($lastpost && ($replies || !rc('display_thread_startdate')))
			{
				#$tpitem['lastpostDate'] = UnbFriendlyDate($lastpost['Date'], 1, 3);
				// Use this instead to display last post dates in a relative form:
				$tpitem['lastpostDate'] = UnbFriendlyDate($lastpost['Date'], 2, 3, true, 4);
				if (rc('display_thread_lastposter'))
				{
					if ($lastpost['User'] > 0)
					{
						if ($user->Load($lastpost['User']))
							$tpitem['lastpostAuthor'] .=
								'<a href="' . UnbLink('@cp', 'id=' . $lastpost['User'], true) . '">' .
								t2h(str_limit($lastpost['UserName'], 20, true)) . '</a>';
						else
							$tpitem['lastpostAuthor'] .= t2h(str_limit($lastpost['UserName'], 20, true));
					}
					else
					{
						$tpitem['lastpostAuthor'] .= t2h(str_limit($lastpost['UserName'], 20, true));
					}
				}
			}
			else
			{
				$tpitem['lastpostDate'] = '';
			}

			// ----- LAST POST ICON -----
			if ($lastpost && ($replies || !rc('display_thread_startdate')))
				$tpitem['lastpostLink'] = UnbMakePostLink($lastpost, -1, 2);
			else
				$item['lastpostLink'] = '';
		}

		if ($tpitem['editThis'])
		{
			$tpitem['formSubject'] = t2i($thread->GetSubject());
			$tpitem['formDesc'] = t2i($thread->GetDesc());

			$output = '';
			UnbListForumsRec($thread->GetForum(), true, 0, 0, true, /* no web links */ true);
			$TP['threadlistEditForumOptions'] = $output;

			$TP['threadlistEditCancelLink'] = UnbLink('@this', 'id=' . $toplevel, true);
			$TP['threadlistFormSureDelete'] = UnbSureDelete();

			$data = array(
				'thread' => &$thread,
				'threadid' => $thread->GetID(),
				'output' => '');
			UnbCallHook('threadlist.editfields', $data);
			$TP['threadlistEditAddFields'] = $data['output'];
		}

		$TP['threadlist'][] = $tpitem;
		$count++;
		if (!$thread->IsImportant()) $countNormal++;
	} // end foreach

	if (!$count)
	{
		// no threads found in this view
		if ($show_forum)
			$TP['threadlistNoneFoundMsg'] = $UNB_T['error.no threads found'];
		else
			$TP['threadlistNoneFoundMsg'] = $UNB_T['error.no threads in forum'];
	}
	if ($count > 0)
	{
		$TP['threadlist'][0]['firstitem'] = true;
		$TP['threadlist'][$count - 1]['lastitem'] = true;
	}

	// -------------------- THREAD ACTIONS --------------------
	$min_forum = 1;
	if (rc('allow_root_threads')) $min_forum = 0;

	if ($UNB['LoginUserID'] &&
	    $forumid >= $min_forum &&
	    $count &&
	    $new_threads)
	{
		$TP['threadlistActionMarkRead'] = '<a href="' .
			UnbLink(
				'@this',
				array(
					'id' => $forumid,
					'allthreadsread' => 1,
					'timestamp' => time(),
					'key' => UnbUrlGetKey()),
				true) .
			'"><img ' . $UNB['Image']['unread'] . ' /> ' . $UNB_T['mark all threads read'] . '</a>';
	}
	if (UnbCheckRights('addthread', $forumid) &&
	    //UnbCheckRights('writeforum', $forumid) &&
	    $forumid >= $min_forum)
	{
		$TP['threadlistActionNew'] = '<a href="' . UnbLink('@post', 'forum=' . $forumid, true) . '"><img ' . $UNB['Image']['write'] . ' /> ' . $UNB_T['thread.new'] . '</a>';
	}
	if ($UNB['LoginUserID'] &&
	    $forumid >= $min_forum)
	{
		$TP['threadlistActionAdvanced'] = true;
	}

	// ---------- advanced options area ----------
	if ($TP['threadlistActionAdvanced'])
	{
		if ($forumid >= $min_forum &&
		    in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
		{
			$mode = $forum->IsWatched($forumid);
			if ($UNB['LoginUser']->GetEMail() != '' ||
				$UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber'))
			{
				$TP['threadlistAdvNotification'] = '<div class="advanced_option">' . $UNB_T['notification'] . ':';
				if ($UNB['LoginUser']->GetEMail() != '')
				{
					if ($mode & UNB_NOTIFY_EMAIL)
						$TP['threadlistAdvNotification'] .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $forumid,
									'unwatch' => 1,
									'page' => $page,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap1'] . ' /> ' . $UNB_T['e-mail'] . '</a>';
					else
						$TP['threadlistAdvNotification'] .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $forumid,
									'watch' => 1,
									'page' => $page,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap0'] . ' /> ' . $UNB_T['e-mail'] . '</a>';
				}
				if ($UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber'))
				{
					if ($mode & UNB_NOTIFY_JABBER)
						$TP['threadlistAdvNotification'] .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $forumid,
									'unwatch' => 4,
									'page' => $page,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap1'] . ' /> ' . $UNB_T['jabber'] . '</a>';
					else
						$TP['threadlistAdvNotification'] .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $forumid,
									'watch' => 4,
									'page' => $page,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap0'] . ' /> ' . $UNB_T['jabber'] . '</a>';
				}
				$TP['threadlistAdvNotification'] .= '</div>';
			}
		}

		if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_IGNORE))
		{
			$TP['threadlistAdvIgnoreForum'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $forumid . '&unignoreforum=' . $forumid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['forum.advanced.unignore'] . '</a><div class="advanced_subtitle">' . $UNB_T['forum.advanced.ignore~'] . ' ' . $UNB_T['forum.advanced.ignoring'] . '</div></div>';
		}
		else
		{
			$TP['threadlistAdvIgnoreForum'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $forumid . '&ignoreforum=' . $forumid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['forum.advanced.ignore'] . '</a><div class="advanced_subtitle">' . $UNB_T['forum.advanced.ignore~'] . ' ' . $UNB_T['forum.advanced.not ignoring'] . '</div></div>';
		}
		if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_HIDE))
		{
			$TP['threadlistAdvHideForum'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $forumid . '&unhideforum=' . $forumid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['forum.advanced.unhide'] . '</a><div class="advanced_subtitle">' . $UNB_T['forum.advanced.hide~'] . ' ' . $UNB_T['forum.advanced.hiding'] . '</div></div>';
		}
		else
		{
			$TP['threadlistAdvHideForum'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $forumid . '&hideforum=' . $forumid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['forum.advanced.hide'] . '</a><div class="advanced_subtitle">' . $UNB_T['forum.advanced.hide~'] . ' ' . $UNB_T['forum.advanced.not hiding'] . '</div></div>';
		}

		$TP['threadlistAdvShowHiddenForums'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $toplevel . '&showhidden_f=1', true) . '">' . $UNB_T['show hidden forums'] . '</a><div class="advanced_subtitle">' . $UNB_T['show hidden forums~'] . '</div></div>';
		$TP['threadlistAdvShowHiddenThreads'] = '<div class="advanced_option"><a href="' . UnbLink('@this', 'id=' . $forumid . '&showhidden_t=1', true) . '">' . $UNB_T['show hidden threads'] . '</a><div class="advanced_subtitle">' . $UNB_T['show hidden threads~'] . '</div></div>';
	}

	// description of all icons used in the previous threads list
	if ($count > 0)
	{
		$out .= '<span class="nowrap"><img ' . $UNB['Image']['thread'] . ' style="vertical-align: middle;" /> ' . $UNB_T['iconslegend.thread'] . '</span> ';
		if ($legend_icon_new)
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_new'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.new posts'] . '</span> ';
		if ($legend_icon_hot)
			#$out .= '<img ' . $UNB['Image']['thread_hot'] . ' style="vertical-align: middle;" />';
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_new_hot'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.hot thread'] . '</span> ';
		if ($legend_icon_important)
			#$out .= '<img ' . $UNB['Image']['thread_important'] . ' style="vertical-align: middle;" />';
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_new_important'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.important thread'] . '</span> ';
		if ($legend_icon_closed)
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_closed'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.closed thread'] . '</span> ';
		if ($legend_icon_own)
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_own'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.own posts'] . '</span> ';
		if ($legend_icon_moved)
			$out .= '<span class="nowrap">&nbsp;<img ' . $UNB['Image']['thread_moved'] . ' style="vertical-align: middle;" /> ' . $UNB_T['img.moved thread'] . '</span> ';

		$TP['threadlistIconsLegend'] = $out;
	}

	return ($count > 0);   // were threads displayed?
}

// List postings (and poll, if available)
//
// in where = (int) thread to list posts from
//            (string) SQL restriction of posts to show
// in page = (int) page number to display
// in get = (array) GET params to add to page links (i.e. search definition)
// in write = (bool) enable "write access" at all? this means:
//                   icons for thread editing. recommended to false on search queries etc.
// in hightlight = array of words to highlight, from search i.e.
// in post_count = (int) number of posts to display (optional; saves the count query,
//                       good for search when we already have the count)
//
function UnbListPosts($where, $page = 1, $get = null, $order = 'Date ASC', $write = true, $highlight = false, $post_count = false)
{
	global $ABBC, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	UnbRequireTxt('post');

	// Clean parameters
	$page = intval($page);

	$posts_per_page = intval(rc('posts_per_page'));
	$writeaccess = false;

	if (is_int($where))
	{
		$threadid = $where;
		$where = 'Thread=' . $where;
		$show_thread = false;
	}
	elseif (is_string($where))
	{
		$threadid = -1;
		$show_thread = true;   // is a string -> is a WHERE definition (over all Threads) -> display Post's Thread
	}
	else
	{
		return false;   // input type not supported
	}

	// Use search engine highlight terms if available and none are specified
	if (!$highlight && !rc('disable_search_highlighting'))
	{
		$ref = &$UNB['Client']['referer'];
		if (preg_match('_http://(www\.)?google\._i', $ref))
		{
			$url = parse_url($ref);
			parse_str($url['query'], $params);
			$highlight = explode_quoted(' ', $params['q']);
			rsort($highlight);   // longest term first for greedy matching
		}
	}

	$thread = new IThread;
	$user = new IUser;
	$post = new IPost;
	if ($threadid > 0)
	{
		$thread->Load($threadid);
		$writeaccess = UnbCheckRights('writeforum', $thread->GetForum(), $threadid);

		if ($_GET['nocount'] != 1)
		{
			// Don't count thread views when the thread starter visits it
			if ($UNB['LoginUserID'] == 0 ||
			    $UNB['LoginUserID'] != 0 && $UNB['LoginUserID'] != $thread->GetUser())
			{
				$thread->IncViews();
			}
		}
	}

	UnbCountUserPosts();

	// ---------- Page selection ----------
	if (!$post_count)
		$post_count = $post->Count($where);
	$params = array(
		'id' => $threadid,
		'nocount' => true);
	if (isset($get)) $params = array_merge($params, $get);
	$params = array_merge($params, array('##' => 'postlist'));
	$TP['postlistPages'] = UnbPageSelection($post_count, $posts_per_page, $page, $params);

	if ($threadid > 0)
	{
		UnbSetRelLink('up', UnbLink('@main', 'id=' . $thread->GetForum()));
	}

	// -------------------- POLL --------------------
	if ($threadid > 0 && $thread->HasPoll() && $thread->GetVotes())
	{
		$polldata = array();

		$polldata['question'] = t2h($thread->GetQuestion());

		$UserVoteNum = $thread->HasUserVoted();
		if (!$UNB['LoginUserID'] ||
			$_GET['showvotes'] ||
			$UserVoteNum ||
			$thread->IsPollEnded() ||
			($thread->IsClosed() && !UnbCheckRights('closethread')) ||
			!UnbCheckRights('allowvoting', $thread->GetForum(), $threadid))
		{
			// ----- SHOW VOTES -----
			$polldata['myVoteId'] = $UserVoteNum;

			$space = ($UNB['Client']['b_class'] == 'ie') ? '&nbsp; ' : '&#x2004;&#x2005;';

			$votes = $thread->GetVotes();
			if ($votes)
			{
				$max = 0;
				$sum = 0;
				foreach ($votes as $vote)
				{
					if ($vote['Votes'] > $max) $max = $vote['Votes'];
					$sum += $vote['Votes'];
				}
				$polldata['maxcount'] = $max;
				$polldata['sumcount'] = $sum;

				$polldata['votes'] = array();
				foreach ($votes as $vote)
				{
					$percent = $sum > 0 ? round($vote['Votes'] / $sum * 100, 0) : 0;
					$relpercent = $max > 0 ? round($vote['Votes'] / $max * 100, 0) : 0;

					$tpitem = array();
					$tpitem['count'] = $vote['Votes'];
					$tpitem['percent'] = $percent;
					$tpitem['percentPadded'] = $percent < 10 ? $space . $percent : $percent;
					$tpitem['relpercent'] = $relpercent;
					$tpitem['text'] = AbbcProc($vote['Title']);
					$tpitem['voteId'] = $vote['ID'];
					$tpitem['myVote'] = $vote['ID'] == $UserVoteNum;

					$polldata['votes'][] = $tpitem;
				}
			}

			if ($UNB['LoginUserID'] && !$thread->IsPollEnded())
			{
				if ($UserVoteNum > 0)
					$polldata['unvoteLink'] = UnbLink(
						'@this',
						array(
							'id' => $threadid,
							'unvote' => true,
							'nocount' => true,
							'key' => UnbUrlGetKey()),
						true);
				else
					$polldata['voteLink'] = UnbLink(
						'@this',
						array(
							'id' => $threadid,
							'nocount' => true),
						true);
			}
			if (UnbCheckRights('viewpollusers', $thread->GetForum(), $thread->GetID()) && $sum > 0 && $_GET['pollusers'] != 1)
			{
				$polldata['showUsersLink'] = UnbLink(
					'@this',
					array(
						'id' => $threadid,
						'showvotes' => true,
						'pollusers' => true,
						'nocount' => true),
					true);
			}

			// ----- Display usernames -----
			if ($_GET['pollusers'] == 1 && UnbCheckRights('viewpollusers', $thread->GetForum(), $thread->GetID()))
			{
				$pollusers = array();
				$arr = $thread->GetUsersVoted();
				foreach ($arr as $a)
				{
					$tpitem = array();
					$tpitem['userName'] = t2h($a[1]);
					$tpitem['userId'] = $a[0];
					$tpitem['userLink'] = UnbLink('@cp', 'id=' . $a[0], true);
					$tpitem['voteId'] = $a[2];

					$pollusers[] = $tpitem;
				}
				$polldata['users'] = $pollusers;
			}
		}
		else
		{
			// ----- SHOW VOTE OPTIONS -----
			$polldata['formLink'] = UnbLink('@this', 'id=' . $threadid, true);

			$votes = $thread->GetVotes();
			if ($votes)
			{
				$sum = 0;
				foreach ($votes as $vote)
				{
					$sum += $vote['Votes'];
				}
				$polldata['sumcount'] = $sum;
			}

			$polldata['options'] = array();
			$votes = $thread->GetVotes();
			if ($votes)
			{
				foreach ($votes as $vote)
				{
					$tpitem = array();
					$tpitem['voteId'] = $vote['ID'];
					$tpitem['voteLink'] = UnbLink(
						'@this',
						array(
							'id' => $threadid,
							'Vote' => $vote['ID'],
							'nocount' => true,
							'key' => UnbUrlGetKey()),
						true);
					$tpitem['text'] = AbbcProc($vote['Title']);

					$polldata['options'][] = $tpitem;
				}
			}

			$polldata['resultsLink'] = UnbLink(
				'@this',
				array(
					'id' => $threadid,
					'page' => $page,
					'showvotes' => true,
					'nocount' => true),
				true);
		}

		$endTime = $thread->GetDate() + $thread->GetPollTimeout() * 3600;
		if ($thread->IsPollEnded())
		{
			$polldata['ended'] = true;
			$polldata['endTime'] = UnbFriendlyDate($endTime, 3, 1, true, 4);
		}
		elseif (!$thread->GetPollTimeout())
		{
			$polldata['unlimited'] = true;
			$polldata['endTime'] = 0;
		}
		else
		{
			$polldata['endTime'] = UnbFriendlyDate($endTime, 3, 1, true, 3);
			$polldata['endTimeAbs'] = UnbFormatTime($endTime, 3);
		}

		$TP['postlistPoll'] = $polldata;
	}

	// -------------------- POSTS --------------------
	$count = 0;
	$start_num = 0;
	$lastdate = 0;
	$lastread = 0;
	$lastpostid = 0;
	$show_edit_warning = 0;
	if ($posts_per_page)
	{
		$start_num = ($page - 1) * $posts_per_page;
		$limit = $start_num . ',' . ($posts_per_page + 1);
	}
	else
		$limit = '';
	$TP['postlist'] = array();
	if ($post->Find($where, $order, $limit))
	{
		$show_readline = false;

		if ($UNB['LoginUserID'] > 0 && $threadid > 0)
		{
			// what have I read last?
			$lastread = intval($UNB['Db']->FastQuery1st('ThreadWatch', 'LastRead', 'Thread=' . $threadid . ' AND User=' . $UNB['LoginUserID']));

			// have already read posts been edited after I read them?
			$post2 = new IPost;
			$show_edit_warning = $post2->Count('Thread = ' . $threadid . ' AND Date <= ' . $lastread . ' AND EditDate > ' . $lastread . ' AND EditUser <> ' . $UNB['LoginUserID']);
		}

		$replyIdMap = array();

		do
		{
			if (!UnbCheckRights('viewforum', $thread->GetForum())) continue;

			$tpitem = array();
			$tpitem['num'] = $count + 1;
			$tpitem['numAll'] = $start_num + $count + 1;
			$lastpostid = $post->GetID();

			// TODO,FIXME: This only works to reference posts on the same page
			// Remember each post's count number in this thread
			$replyIdMap[$post->GetID()] = $tpitem['numAll'];
			// Map the current post's replyToId to the remembered count number
			$replyToId = $post->GetReplyTo();
			if ($replyToId)
			{
				$tpitem['replyToId'] = $replyToId;
				$tpitem['replyToLink'] = UnbLink('@thread', array('postid' => $replyToId), true);
				$tpitem['replyToNum'] = $replyIdMap[$replyToId];
				if (!$tpitem['replyToNum']) $tpitem['replyToNum'] = 'ID ' . $replyToId;
			}
			// Don't show the replyTo post number if it's the previous one
			if ($tpitem['replyToNum'] == $tpitem['numAll'] - 1) $tpitem['replyToNum'] = 0;
			if (is_numeric($tpitem['replyToNum']) && $tpitem['replyToNum'] > 0)
				$tpitem['replyToNum'] = '#' . $tpitem['replyToNum'];

			if ($show_thread)
			{
				$thread->Load($post->GetThread());
			}

			if ($count)
			{
				if ($show_readline) $tpitem['readLine'] = true;
				if ($show_edit_warning && $show_readline) $tpitem['editWarning'] = $show_edit_warning;
				if ($show_readline) $show_edit_warning = 0;   // warning has been displayed
			}
			$show_readline = false;

			$lastdate = $post->GetDate();
			UnbShowPost(
				$tpitem,
				$post,
				$thread,
				$count % 2,
				$page,
				$writeaccess && $write,
				false,
				$show_thread,
				$threadid > 0 &&                          // we are in a thread at all
					$post->GetDate() <= $lastread &&      // post is from before last read
					$post->GetEditDate() > $lastread && $post->GetEditUser() != $UNB['LoginUserID'],
														  // post was edited after last read by another user
				$highlight);                              // words to highlight

			$thispostdate = $post->GetDate();
			$found_next = $post->FindNext();
			if ($found_next) $nextpostdate = $post->GetDate();
			else             $nextpostdate = 0;

			if ($threadid > 0)
			{
				// The following information is thread related, so don't show it for search results...

				if (UnbCheckRights('is_admin') && $UNB['LoginUser']->GetFlags() & UNB_USER_USERREADPOST)
				{
					// who has read until here?
					$record = $UNB['Db']->FastQuery(
						/*table*/ array(
							array('', 'ThreadWatch', 'tw', ''),
							array('', 'Users', 'u', '')),
						/*fields*/ 'u.Name, tw.LastRead',
						/*where*/ 'tw.Thread = ' . $threadid . ' ' .
							'AND tw.LastRead >= ' . $thispostdate . ' ' .
							($nextpostdate ? 'AND tw.LastRead < ' . $nextpostdate . ' ' : '') .
							'AND tw.User = u.ID',
						/*order*/ 'Name');

					$i = 0;
					$tpitem['usersRead'] = '';
					if ($record) do
					{
						$tpitem['usersRead'] .= ($i ? ', ' : '') .
							'<span title="' . t2h(strip_tags(UnbFriendlyDate($record[1], 1, 3))) . '">' . t2h($record[0]) . '</span>';
						$i++;
					}
					while ($record = $UNB['Db']->GetRecord());
				}

				if ($lastread)
				{
					// have I read until here?
					$show_readline = ($lastread >= $thispostdate) && ($lastread < $nextpostdate);
				}
			}
			$count++;
			$TP['postlist'][] = $tpitem;
		}
		while ($found_next && $count < $posts_per_page);
	}
	if ($count > 0)
	{
		$TP['postlist'][0]['firstitem'] = true;
		$TP['postlist'][$count - 1]['lastitem'] = true;
	}

	if ($show_edit_warning)
	{
		// Recalculate this setting because we have to check it for an ending timespan now, too
		// Change to above: (Date <= $lastdate) instead of (Date <= $lastread)
		//                  The edited post must be on this page or before!
		$show_edit_warning = $post2->Count('Thread = ' . $threadid . ' AND Date <= ' . $lastdate . ' AND EditDate > ' . $lastread . ' AND EditUser <> ' . $UNB['LoginUserID']);

		if /*still*/ ($show_edit_warning)
		{
			// previous post has been edited; and warning has not been displayed yet
			$TP['postlistEditWarning'] = $show_edit_warning;
		}
	}

	// -------------------- THREAD ACTIONS --------------------
	if ($threadid > 0)
	{
		if (rc('new_topic_link_in_thread') &&
		    UnbCheckRights('addthread', $thread->GetForum())/* &&
		    UnbCheckRights('writeforum', $thread->GetForum())*/)
		{
			$TP['postlistActionNewThread'] = '<a href="' . UnbLink('@post', 'forum=' . $thread->GetForum(), true) . '"><img ' . $UNB['Image']['write'] . ' /> ' . $UNB_T['thread.new'] . '</a>';
		}
		if ($writeaccess &&
		    (!$thread->IsClosed() || UnbCheckRights('closethread')) &&
		    $page == $UNB['TP']['LastPageCount'])
		{
			$TP['postlistActionReply'] = '<a href="' . UnbLink('@post', 'thread=' . $threadid . '&replyto=' . $lastpostid, true) . '"><img ' . $UNB['Image']['write'] . ' /> ' . $UNB_T['post.do reply'] . '</a>';
		}
	}
	if ($UNB['LoginUserID'] &&
	    $threadid > 0)
	{
		$TP['postlistActionAdvanced'] = true;
	}

	// advanced options area
	if ($TP['postlistActionAdvanced'])
	{
		$TP['postlistAdvanced'] = array();
		$mode = $thread->IsWatched();
		if (in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
		{
			if ($UNB['LoginUser']->GetEMail() != '' ||
				$UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber'))
			{
				$out = $UNB_T['notification'] . ':';
				if ($UNB['LoginUser']->GetEMail() != '')
				{
					if ($mode & UNB_NOTIFY_EMAIL)
						$out .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $threadid,
									'unwatch' => 1,
									'page' => $page,
									'nocount' => true,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap1'] . ' /> ' . $UNB_T['e-mail'] . '</a>';
					else
						$out .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $threadid,
									'watch' => 1,
									'page' => $page,
									'nocount' => true,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap0'] . ' /> ' . $UNB_T['e-mail'] . '</a>';
				}
				if ($UNB['LoginUser']->GetJabber() != '' && rc('enable_jabber'))
				{
					if ($mode & UNB_NOTIFY_JABBER)
						$out .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $threadid,
									'unwatch' => 4,
									'page' => $page,
									'nocount' => true,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap1'] . ' /> ' . $UNB_T['jabber'] . '</a>';
					else
						$out .= ' <a href="' .
							UnbLink(
								'@this',
								array(
									'id' => $threadid,
									'watch' => 4,
									'page' => $page,
									'nocount' => true,
									'key' => UnbUrlGetKey()),
								true) .
							'"><img ' . $UNB['Image']['tap0'] . ' /> ' . $UNB_T['jabber'] . '</a>';
				}
				$TP['postlistAdvanced'][] = array('option' => $out);
			}
		}
		if ($post_count > 1 &&
		    UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()) &&
		    UnbCheckRights('importantthread', $thread->GetForum(), $thread->GetID()))
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&split=1', true) . '"><img ' . $UNB['Image']['split'] . ' title="' . $UNB_T['thread.split'] . '" /> ' . $UNB_T['thread.split'] . '</a>'
				);
		}
		if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()) && !$thread->IsClosed())
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&close=1&page=' . $page . '&nocount=1&key=' . UnbUrlGetKey(), true) . '"><img ' . $UNB['Image']['lock'] . ' title="' . $UNB_T['thread.close'] . '" /> ' . $UNB_T['thread.close'] . '</a>'
				);
		}
		if ($thread->IsClosed())
		{
			$out = $UNB_T['thread.is closed'];
			if (UnbCheckRights('closethread', $thread->GetForum(), $thread->GetID()))
				$out .= ' (<a href="' . UnbLink('@this', 'id=' . $threadid . '&close=0&page=' . $page . '&nocount=1&key=' . UnbUrlGetKey(), true) . '"><img ' . $UNB['Image']['unlock'] . ' title="' . $UNB_T['thread.reopen~'] . '" /> ' . $UNB_T['thread.reopen'] . '</a>)';
			$TP['postlistAdvanced'][] = array('option' => $out);
		}
		$mode = $thread->IsWatched();
		if ($mode & UNB_NOTIFY_BOOKMARK)
		{
			$out = '<a href="' . UnbLink('@this', 'id=' . $threadid . '&unwatch=128&page=' . $page . '&nocount=1&key=' . UnbUrlGetKey(), true) . '">' . '<img ' . $UNB['Image']['bookmark']. ' /> ' . $UNB_T['thread.remove bookmark'] . '</a>';
		}
		else
		{
			$out = '<a href="' . UnbLink('@this', 'id=' . $threadid . '&watch=128&page=' . $page . '&nocount=1&key=' . UnbUrlGetKey(), true) . '">' . '<img ' . $UNB['Image']['nobookmark']. ' /> ' . $UNB_T['thread.add bookmark'] . '</a>';
		}
		$out .= ' | <a href="' . UnbLink('@cp', 'cat=bookmarks', true) . '">' . $UNB_T['thread.show bookmarks'] . '</a>';
		$TP['postlistAdvanced'][] = array('option' => $out);

		if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_IGNORE))
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&unignorethread=' . $threadid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['thread.advanced.unignore'] . '</a>',
				'newgroup' => true,
				'subtitle' => $UNB_T['ignore_thread_note'] . ' ' . $UNB_T['thread.advanced.ignoring']
				);
		}
		else
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&ignorethread=' . $threadid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['thread.advanced.ignore'] . '</a>',
				'newgroup' => true,
				'subtitle' => $UNB_T['thread.advanced.ignore~'] . ' ' . $UNB_T['thread.advanced.not ignoring']
				);
		}
		if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_HIDE))
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&unhidethread=' . $threadid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['thread.advanced.unhide'] . '</a>',
				'subtitle' => $UNB_T['hide_thread_note'] . ' ' . $UNB_T['thread.advanced.hiding']
				);
		}
		else
		{
			$TP['postlistAdvanced'][] = array(
				'option' => '<a href="' . UnbLink('@this', 'id=' . $threadid . '&hidethread=' . $threadid . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['thread.advanced.hide'] . '</a>',
				'subtitle' => $UNB_T['thread.advanced.hide~'] . ' ' . $UNB_T['thread.advanced.not hiding']
				);
		}
		if (UnbCheckRights('is_admin'))
		{
			// Count number of users ignoring this thread
			$cnt = $UNB['Db']->FastQuery1st('UserForumFlags', 'COUNT(User)', 'Thread = ' . $threadid . ' AND Flags & 2');

			$TP['postlistAdvanced'][] = array(
				'option' => UteTranslateNum('n users ignoring topic', $cnt, 'n', $cnt) . ' <a href="' . UnbLink('@this', 'id=' . $threadid . '&unignorethreadallusers=1&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['unignore for all users'] . '</a>'
				);
		}
	}

	// -------------------- FAST REPLY --------------------
	if ($threadid > 0)
	{
		$TP['posteditorFormLink'] = UnbLink('@post', null, true);
		$TP['posteditorFormKey'] = UnbUrlGetKey();
		$TP['posteditorForumId'] = $thread->GetForum();
		$TP['posteditorThreadId'] = $threadid;
		if (!$UNB['LoginUserID'] && rc('use_veriword'))
		{
			$TP['posteditorVericodeLink'] = UnbLink('@veriword', array('prog_id' => rc('prog_id')), true);
		}
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
		$TP['posteditorMaxLength'] = rc('max_post_len');
	}

	// -------------------- SET LAST READ TIME --------------------
	if ($threadid > 0 && $page < $UNB['TP']['LastPageCount'])
	{
		// Not yet read all pages of this thread -> only mark as read what was actually displayed
		$thread->SetLastRead($lastdate);
	}
	elseif ($threadid > 0 && $page == $UNB['TP']['LastPageCount'])
	{
		// Thread entirely read, set current date
		$thread->SetLastRead(time());
	}

	// -------------------- SET LAST VIEWED TIME --------------------
	if ($threadid > 0)
	{
		$thread->SetLastViewed(time());
	}

	return $count;   // Return number of listed posts
}


// Display a dropdown box to jump to another forum
//
// in forumid = (int) currently selected forum id
//
function UnbJumpForumBox($forumid)
{
	global $output, $UNB, $UNB_T;

	// Clean parameters
	$forumid = intval($forumid);

	$out = '';
	$out = '<form id="jumpforum" action="' . UnbLink('@main', null, true, /*sid*/ false, /*derefer*/ false, /*short*/ false) . '" method="get">';
	$out .= UnbFormSessionId();
	$out .= $UNB_T['goto forum'] . ' ';
	// REQUEST-PARAMETER (see common.lib:UnbLink() for more details)
	// REQUEST-NAME (see common.lib:UnbLink() for more details)
	$out .= '<input type="hidden" name="req" value="main" />';
	$out .= '<select class="text" name="id" onchange="this.form.submit();">';
	$out .= '<option value="0" class="forum"' . ($forumid == 0 ? ' selected="selected"' : '') . '>' . $UNB_T['overview'] . '</option>';

	$output = '';
	UnbListForumsRec($forumid, false, 0, 0, true, /*noWebLinks*/ true);
	$out .= $output;

	$out .= '</select>';
	$out .= ' <input type="submit" class="button" value="' . $UNB_T['go'] . '" /><br />';
	$out .= '</form>' . endl;
	return $out;
}

// Show a search textbox
//
// in forumid = (int, > 0) restrict search to this forum id
//              (int, < 0) thread id = -forumid (as implemented in search.inc.php)
//
function UnbSearchTextbox($forumid)
{
	global $UNB, $UNB_T;

	// Clean parameters
	$forumid = intval($forumid);

	$out = '';
	$out .= '<form action="' . UnbLink('@search', null, true, /*sid*/ false) . '" method="get" style="display:inline;">';
	$out .= UnbFormSessionId();
	// REQUEST-PARAMETER (see common.lib:UnbLink() for more details)
	// REQUEST-NAME (see common.lib:UnbLink() for more details)
	$out .= '<input type="hidden" name="req" value="search" />';
	$out .= '<img ' . $UNB['Image']['search'] . ' /> ';
	$out .= '<input type="text" class="text" name="Query" size="20" />';
	$out .= '<input type="hidden" name="InSubject" value="1" />';
	$out .= '<input type="hidden" name="InMessage" value="1" />';
	$out .= '<input type="hidden" name="ResultView" value="2" />';
	$out .= '<input type="hidden" name="Forum" value="' . $forumid . '" />';
	$out .= ' <input type="submit" class="button" value="' . $UNB_T['search'] . '" /><br />';
	$out .= '</form>' . endl;
	return $out;
}

// Show a language selection drop-down list
// Designed for use on the overview page and in the installer.
//
// uses (int) $toplevel
//
function UnbSelectLangBox()
{
	global $toplevel, $UNB, $UNB_T;

	$out = '';
	$out .= '<div style="text-align: right;">' . endl;
	$out .= '<form id="SetLangForm" action="' . UnbLink('@this', null, true, /*sid*/ false) . '" method="get">' . endl;
	if (isset($toplevel)) $out .= '<input type="hidden" name="id" value="' . $toplevel . '" />' . endl;   // for overview page
	$out .= UnbFormSessionId() . endl;
	$out .= $UNB_T['select language'] . ': ';
	$out .= '<select name="set_lang" onchange="document.getElementById(\'SetLangForm\').submit();">' . endl;
	$out .= '<option value="">' . $UNB_T['language.default'] . '</option>' . endl;
	foreach ($UNB['AllLangs'] as $lang)
	{
		$title = t2h($UNB['AllLangNames'][$lang]);
		if ($UNB['Client']['b_class'] == 'gecko')
		{
			$title = '<img src="' . $UNB['LibraryURL'] . 'lang/' . $lang . '/flag.png" alt="" style="float: right; margin-top: 1px;" />' . $title . ' <small>(' . t2h($lang) . ')</small>';
		}
		$sel = $lang == $UNB['Lang'] ? ' selected="selected"' : '';
		$out .= '<option value="' . $lang . '"' . $sel . ' style="clear: right;">' . $title . '</option>' . endl;
	}
	$out .= '</select>' . endl;

	$out .= '<noscript> <input type="submit" value="' . $UNB_T['select'] . '" /></noscript>' . endl;

	$out .= '</form>' . endl;
	$out .= '</div>' . endl;
	return $out;
}

// Return JavaScript code to collapse (hide) a <div/> by its ID
//
function JSCollapseID($id)
{
	// Clean parameters
	$id = str_replace('"', '', $id);

	$out  = '<script type="text/javascript">//<![CDATA[' . endl;
	$out .= 'toggleVisId("' . $id . '", false);' . endl;
	$out .= '//]]></script>';
	return $out;
}

// Format a user's gender verbose, with the currently selected language
//
function UnbGetGenderVerbose($gender)
{
	global $UNB_T;

	if ($gender == 'm') return $UNB_T['male'];
	if ($gender == 'f') return $UNB_T['female'];
	return '';
}

// Get the gender info image
//
function UnbGetGenderImage($gender)
{
	global $UNB;

	switch ($gender)
	{
		case 'm': $img = 'male'; break;
		case 'f': $img = 'female'; break;
		default: return '';
	}

	if ($UNB['TextOnly'])
		return '<span title="' . t2h(UnbGetGenderVerbose($gender)) . '">[' . $gender . ']</span>';
	else
		return '<img ' . $UNB['Image'][$img] . ' title="' . t2h(UnbGetGenderVerbose($gender)) . '" />';
}

// Return the descriptive User Status
//
// in userid = (int) user id to get status text for
// in mask = (string) mask to insert status description into. %s will be replaced with the info
// in notnormal = (bool) true: only return not "user" text | false: return any text, also "user"
// in short = (bool) use short form of text
// in numerus = (int) how many things should be described? use singular/plural/other forms of the word
//
// returns (string) $mask parameter with %s replaced by the status text
//
function UnbGetUserStatusText($userid, $mask = '%s', $notnormal = false, $short = false, $numerus = 1)
{
	global $UNB_T;

	if ($userid == -1) return str_replace('%s', $UNB_T['former member'], $mask);

	// Read user's groups
	if ($userid > 0) $groups = UnbGetUserGroups($userid);
	else             $groups = array();

	// Find appropriate group/status
	if (!$userid) $status = 0;
	elseif (in_array(UNB_GROUP_ADMINS, $groups)) $status = 3;
	elseif (in_array(UNB_GROUP_MODS, $groups)) $status = 2;
	elseif (in_array(UNB_GROUP_MEMBERS, $groups)) $status = 1;
	else $status = -1;

	if ($status == 0) $text = UteTranslateNum('guests', $numerus);
	elseif ($status == 1 && !$notnormal) UteTranslateNum('members', $numerus);
	elseif ($status == 2) $text = ($short ? $UNB_T['mod.short'] : UteTranslateNum('moderators', $numerus));
	elseif ($status == 3) $text = ($short ? $UNB_T['admin.short'] : UteTranslateNum('administrators', $numerus));
	elseif (!$notnormal) $text = $UNB_T['unknown user'];
	else $text = '';

	if ($text != '') $text = str_replace('%s', $text, $mask);
	return $text;
}

// Build a JavaScript confirmation message for Delete? checkboxes
//
// in type = (int) type of input control. 0: checkbox [default], 1: pushbutton
//
function UnbSureDelete($type = 0)
{
	global $UNB_T;

	if ($type == 0)
		return 'onclick="if (this.checked) if (!confirm(\'' . str_replace("'", "\'", t2i($UNB_T['sure delete'])) . '\')) this.checked = false;"';
	if ($type == 1)
		return 'onclick="return confirm(\'' . str_replace("'", "\'", t2i($UNB_T['sure delete'])) . '\')"';
}

// Build <img> telling whether a user is online
//
// in online = (bool) true: online image, false: offline image (caller must determine the correct value)
//
function UnbGetUserOnlineImg($online)
{
	global $UNB, $UNB_T;

	if ($UNB['TextOnly'])
		return $online ? '<span title="' . $UNB_T['online'] . '">[on]</span>' : '<span title="' . $UNB_T['offline'] . '">[off]</span>';
	else
		return $online ? '<img ' . $UNB['Image']['online'] . ' title="' . $UNB_T['online'] . '" />' : '<img ' . $UNB['Image']['offline'] . ' title="' . $UNB_T['offline'] . '" />';
}

// Get the full URL of a user avatar image
//
// in user = IUser object
//
function UnbAvatarUrl($user)
{
	global $UNB;
	if (!is_object($user)) return '';
	if (!$user->GetAvatar()) return '';
	if ($user->GetAvatar() == 'gravatar')
		return 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5(trim($user->GetEMail())) .
			'&size=' . min(intval(rc('avatar_x')), intval(rc('avatar_y'))) . '&rating=PG';
	return ($user->AvatarFromURL() ? '' : $UNB['AvatarURL']) . $user->GetAvatar();
}

// Get the full filename of a user avatar image
//
// in user = IUser object
//
function UnbAvatarFile($user)
{
	global $UNB;
	if (!is_object($user)) return '';
	if (!$user->GetAvatar()) return '';
	if ($user->GetAvatar() == 'gravatar') return '';
	return ($user->AvatarFromURL() ? '' : $UNB['AvatarPath']) . $user->GetAvatar();
}

// Get the dimensions of a user avatar image
//
// in user = IUser object
// in halfsize = (bool) true: allow halfsized avatars, false: always show correct image size
//
function UnbAvatarSize(&$user, $halfsize = true)
{
	global $UNB;
	if (!is_object($user)) return '';
	if (!$user->GetAvatar()) return '';
	if ($user->GetAvatar() != 'gravatar')
	{
		$x = $user->GetAvatarX();
		$y = $user->GetAvatarY();
	}
	else
	{
		$x = $y = min(intval(rc('avatar_x')), intval(rc('avatar_y')));
	}
	if (!$x || !$y) return '';

	if ($UNB['LoginUserID'] &&
		$halfsize &&
		($UNB['LoginUser']->GetFlags() & UNB_USER_HALFSIZEAVATARS) &&
		($y > rc('avatar_y') / 2))
	{
		$div = 2;
	}
	else
	{
		$div = 1;
	}
	return ' width="' . intval($x / $div) . '" height="' . intval($y / $div) . '"';
}

// Get the full URL of a user photo image
//
// in user = IUser object
//
function UnbPhotoUrl($user)
{
	global $UNB;
	if (!is_object($user)) return '';
	if (!$user->GetPhoto()) return '';
	return ($user->PhotoFromURL() ? '' : $UNB['PhotoURL']) . $user->GetPhoto();
}

// Get the full filename of a user photo image
//
// in user = IUser object
//
function UnbPhotoFile($user)
{
	global $UNB;
	if (!is_object($user)) return '';
	if (!$user->GetPhoto()) return '';
	return ($user->PhotoFromURL() ? '' : $UNB['PhotoPath']) . $user->GetPhoto();
}

// -------------------- General page layout --------------------

// Show a little redirection page.
// The PHP script will be terminated with this function call!
//
// in url = (string) Address to redirect to. Must not contain " characters
//
function UnbForwardHTML($url)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if (!rc('no_forward'))
	{
		// make absolute URL because this doesn't care about the <base href> attribute
		if (!preg_match('_^([a-z]+):_i', $url) && rc('home_url'))
		{
			$baseUrl = TrailingSlash(rc('home_url'));
			$baseUrl = preg_replace('_^https:_', 'http:', $baseUrl);
			if ($_SERVER['HTTPS'] == 'on')
				$baseUrl = preg_replace('_^http:_', 'https:', $baseUrl);
			$url = $baseUrl . $url;
		}
		// Problems with duplicate Status header for PHP/FastCGI
		#header('Status: 303');   // HTTP/303 "See other"
		header('HTTP/1.1 303 See other');
		header('Location: ' . $url);
	}

	// Content type setting
	if (!isset($UNB['ContentType']))
	{
		// Determine browser capabilities with HTTP Accept header
		$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
		foreach ($accept as $h => $v) { $e = explode(';', $v); $accept[$h] = trim($e[0]); }

		if (in_array('application/xhtml+xml', $accept)) $UNB['ContentType'] = 'application/xhtml+xml';
		elseif (in_array('text/xml', $accept)) $UNB['ContentType'] = 'text/xml';
		elseif (in_array('text/html', $accept)) $UNB['ContentType'] = 'text/html';
		else $UNB['ContentType'] = 'text/html';

		global $DEBUG;
		if (strstr($DEBUG, ' +htmltype ')) $UNB['ContentType'] = 'text/html';
	}

	// Send character set header
	if ($UNB['ContentType'] != '')
	{
		UnbCheckHeadersSent('before sending Content-Type header');
		header('Content-type: ' . $UNB['ContentType'] . '; charset=' . $UNB['CharSet']);
	}

	$UNB['ContentTypeXML'] = strstr($UNB['ContentType'], 'xml') != false;
	if ($UNB['ContentTypeXML'])
		$TP['headXML'] = '<?xml version="1.0" encoding="' . $UNB['CharSet'] . '"?>' . endl;
	$TP['headDocType'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . endl;
	$TP['headLang'] = $UNB['Lang'];
	$TP['headTitle'] = 'Redirect';
	$baseUrl = TrailingSlash(rc('home_url'));
	$baseUrl = preg_replace('_^https:_', 'http:', $baseUrl);
	if ($_SERVER['HTTPS'] == 'on')
		$baseUrl = preg_replace('_^http:_', 'https:', $baseUrl);
	if (rc('home_url')) $TP['headBase'] = '<base href="' . $baseUrl . '" />' . endl;
	if (function_exists('AbbcCss'))
	{
		$TP['headCSS'] .= '<style type="text/css">/*<![CDATA[*/' . endl;
		$TP['headCSS'] .= AbbcCss() . endl;
		$TP['headCSS'] .= '/*]]>*/</style>' . endl;
	}
	if ($UNB['CssFile']) $TP['headCSS'] .= '<link rel="stylesheet" href="' . $UNB['CssFile'] . '" type="text/css" />' . endl;
	// Add all other CSS files here, see note in UnbAdditionalPageRefs() function
	$cssFiles = array();
	UnbCallHook('page.addcss', $cssFiles);
	foreach ($cssFiles as $cssFile)
		$TP['headCSS'] .= '<link rel="stylesheet" href="' . $UNB['CssBaseURL'] . $cssFile . '.css.php" type="text/css" />' . endl;

	$a = '';
	if (!rc('no_forward')) $a .= '<meta http-equiv="refresh" content="1; URL=' . t2i($url) . '" />';
	UnbCallHook('page.htmlhead', $a);
	$TP['headCustom'] = $a;

	$TP['headSimple'] = true;
	$TP['headNoIndex'] = true;

	$TP['url'] = $url;
	$TP['autoForward'] = !rc('no_forward');
	$TP['urlJs'] = str_replace('"', '\\\\\\"', $url);

	UteRemember('_forward.html', $TP);
	UteShowAll();
	exit();
}

// Set related link in the HTML page header
//
// <link rel="$rel" href="$href" title="$title" type="$type" />
// All links are stored in a global array and will be added into the HTML output
// near the programme termination. This gives all lists and controls the chance
// to add their links in the middle of the page.
//
function UnbSetRelLink($rel, $href, $title = '', $type = '')
{
	global $UNB;
	if (!isset($UNB['PageRelLinks'])) $UNB['PageRelLinks'] = array();
	$UNB['PageRelLinks'][$rel] = array('href' => $href, 'title' => $title, 'type' => $type);
}

// Begin a general HTML page
//
// in title = (string) <title> content (HTML encoding will be done inside this function)
// in css = (string) additional CSS definitions
// in simple = (bool) don't show header and navigation, for small help pages i.e.
//
function UnbBeginHTML($title = '', $simple = false)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	UnbCallHookN('page.httpheader');

	// initialise HTML tab order counter
	$GLOBALS['UnbTplTabIndex'] = 0;

	// Activate GZip Output Buffer if it's not active yet
	if (!ini_get('zlib.output_compression'))
	{
		switch (rc('gzip'))
		{
			case 'on':
				ob_start('ob_gzhandler');
				break;
			case 'auto':
				if (function_exists('ob_get_level'))
					if (!ob_get_level()) ob_start('ob_gzhandler');
				break;
		}
	}

	// ----- BEGIN page -----

	// title is left in its original form for use in the frame page

	// TODO: pass <meta>, <link> tags and other information (template parameters?) to the frame script
	eval($UNB['FrameBeginPage']);

	if ($title != '') $title .= ' - ';
	if ($UNB['Installing']) $title .= 'Unclassified NewsBoard';
	else                    $title .= rc('forum_title');
	if (rc('indep_page') || $simple || $UNB['Installing'])
	{
		$TP['headHTML'] = true;

		// Content type setting
		if (!isset($UNB['ContentType']))
		{
			// Determine browser capabilities with HTTP Accept header
			$accept = explode(',', $_SERVER['HTTP_ACCEPT']);
			foreach ($accept as $h => $v) { $e = explode(';', $v); $accept[$h] = trim($e[0]); }

			if (in_array('application/xhtml+xml', $accept)) $UNB['ContentType'] = 'application/xhtml+xml';
			elseif (in_array('text/xml', $accept)) $UNB['ContentType'] = 'text/xml';
			elseif (in_array('text/html', $accept)) $UNB['ContentType'] = 'text/html';
			else $UNB['ContentType'] = 'text/html';

			global $DEBUG;
			if (strstr($DEBUG, ' +htmltype ')) $UNB['ContentType'] = 'text/html';
		}

		// Send character set header
		if ($UNB['ContentType'] != '')
		{
			UnbCheckHeadersSent('before sending Content-Type header');
			header('Content-type: ' . $UNB['ContentType'] . '; charset=' . $UNB['CharSet']);
		}

		$UNB['ContentTypeXML'] = strstr($UNB['ContentType'], 'xml') != false;
		if ($UNB['ContentTypeXML'])
			$TP['headXML'] = '<?xml version="1.0" encoding="' . $UNB['CharSet'] . '"?>' . endl;
		$TP['headDocType'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . endl;
		//$TP['headDocType'] = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . endl;
		$TP['headLang'] = $UNB['Lang'];
		$TP['headTitle'] = t2h($title);
		$baseUrl = TrailingSlash(rc('home_url'));
		$baseUrl = preg_replace('_^https:_', 'http:', $baseUrl);
		if ($_SERVER['HTTPS'] == 'on')
			$baseUrl = preg_replace('_^http:_', 'https:', $baseUrl);
		if (rc('home_url')) $TP['headBase'] = '<base href="' . $baseUrl . '" />' . endl;
		if (function_exists('AbbcCss'))
		{
			$TP['headCSS'] .= '<style type="text/css">/*<![CDATA[*/' . endl;
			$TP['headCSS'] .= AbbcCss() . endl;
			$TP['headCSS'] .= '/*]]>*/</style>' . endl;
		}
		if ($UNB['CssFile']) $TP['headCSS'] .= '<link rel="stylesheet" href="' . $UNB['CssFile'] . '" type="text/css" />' . endl;
		// Add all other CSS files here, see note in UnbAdditionalPageRefs() function
		$cssFiles = array();
		//$cssFiles = array('announcement', 'controlpanel', 'forum', 'post', 'register', 'search', 'stat', 'thread', 'userlist', 'userprofile');
		UnbCallHook('page.addcss', $cssFiles);
		foreach ($cssFiles as $cssFile)
			$TP['headCSS'] .= '<link rel="stylesheet" href="' . $UNB['CssBaseURL'] . $cssFile . '.css.php" type="text/css" />' . endl;

		$a = '';
		UnbCallHook('page.htmlhead', $a);
		$TP['headCustom'] = $a;

		$TP['headSimple'] = $simple;
	}

	$jscode = 'var STRING_DELETE=\'' . str_replace("'", "\\'", $UNB_T['sure delete']) . '\';' . endl;
	$jscode .= 'var DESIGN_URL = "' . UnbLink('@main', 'setdesign=___') . '";' . endl;
	$offset = $UNB['Timezone']['offset'] / 60;
	if ($UNB['Timezone']['withdst']) $offset += (date('I') ? 60 : 0);
	$jscode .= 'var TIMEZONE = "' . $offset . '";' . endl;
	$jscode .= 'var NOCOOKIES = ' . (rc('no_cookies') ? 'true' : 'false') . ';' . endl;
	$TP['headJsCode'] = $jscode;
	$TP['headLibraryURL'] = $UNB['LibraryURL'];
	$TP['headCssBaseURL'] = $UNB['CssBaseURL'];
	$TP['headJsBaseURL'] = $UNB['JsBaseURL'];
	$TP['headImgBaseURL'] = $UNB['ImgBaseURL'];

	$TP['LoginUserID'] = $UNB['LoginUserID'];
	$TP['LoginUserName'] = $UNB['LoginUserName'];

	if (!$simple)
	{
		$nav_line = rc('nav_line');
		$show_login = rc('show_login');
		$login_top = rc('login_top');

		if ($show_login && !$UNB['Installing'])
			$UNB['loginControlData'] = UnbLoginControl();

		// ----- BEGIN head -----
		if (rc('toplogo_url'))
			$TP['headLogoLink'] = UnbLink(rc('toplogo_url'), null, true, /*sid*/ false, /*derefer*/ true);
		else
			$TP['headLogoLink'] = UnbLink('@main', null, true);
		$TP['headLogoAlt'] = t2i(rc('forum_title'));
		// ----- END head -----

		// ----- BEGIN navi -----
		if (($nav_line || $show_login && $login_top) && !$UNB['Installing'])
		{
			if ($show_login && $login_top && !$UNB['Installing'])
				$TP['headLoginControl'] = $UNB['loginControlData'];

			if ($nav_line && !$UNB['Installing'])
			{
				$data = array();
				UnbCallHook('page.navigation.prelinks', $data);
				foreach ($data as $link)
				{
					$links[] = array(
						'link' => $link['link'],
						'title' => $link['title']);
				}

				$links = array();
				if (rc('parent_url') != '')
					$links[] = array(
						'link' => UnbLink(rc('parent_url'), null, true, /*sid*/ false, /*derefer*/ true),
						'image' => '<img ' . $UNB['Image']['back'] . ' />',
						'imageUrl' => 'back.png',
						'title' => $UNB_T['main site']);
					#echo '<td class="sep"><span class="sep"></span></td>';
				$links[] = array(
					'link' => UnbLink('@main', null, true),
					'image' => '<img ' . $UNB['Image']['nav_overview'] . ' />',
					'imageUrl' => 'nav_overview.png',
					'title' => '<b>' . $UNB_T['forum'] . '</b>',
					'active' => $UNB['ThisPage'] == '@main' ||
						$UNB['ThisPage'] == '@thread' ||
						$UNB['ThisPage'] == '@post');
				$links[] = array(
					'link' => UnbLink('@search', null, true),
					'image' => '<img ' . $UNB['Image']['nav_search'] . ' />',
					'imageUrl' => 'nav_search.png',
					'title' => $UNB_T['search'],
					'active' => $UNB['ThisPage'] == '@search');
				if ($UNB['LoginUserID'])
					$links[] = array(
						'link' => UnbLink('@cp', 'cat=summary', true),
						'image' => '<img ' . $UNB['Image']['nav_settings'] . ' />',
						'imageUrl' => 'nav_settings.png',
						'title' => $UNB_T['user cp'],
						'active' => $UNB['ThisPage'] == '@cp' && $_REQUEST['cat'] != '');
				$links[] = array(
					'link' => UnbLink('@users', null, true),
					'image' => '<img ' . $UNB['Image']['nav_users'] . ' />',
					'imageUrl' => 'nav_users.png',
					'title' => $UNB_T['members'],
					'active' => $UNB['ThisPage'] == '@users' ||
						$UNB['ThisPage'] == '@cp' && $_REQUEST['cat'] == '');
				if (UnbCheckRights('showstat'))
					$links[] = array(
						'link' => UnbLink('@stat', null, true),
						'image' => '<img ' . $UNB['Image']['nav_stat'] . ' />',
						'imageUrl' => 'nav_stat.png',
						'title' => $UNB_T['statistics'],
						'active' => $UNB['ThisPage'] == '@stat');

				UnbCallHook('page.navigation.postlinks', $links);

				$TP['headnaviLinks'] = $links;
			}

		} // if nav_line || login_top
		// ----- END navi -----

		$data = '';
		UnbCallHook('page.prelogo', $data);
		$TP['headAddPreLogoCode'] = $data;
		$data = '';
		UnbCallHook('page.postlogo', $data);
		$TP['headAddPostLogoCode'] = $data;
		$data = '';
		UnbCallHook('page.postnavi', $data);
		$TP['headAddPostNaviCode'] = $data;
	} // if !simple

	$data = '';
	UnbCallHook('page.prelogo.simple', $data);
	$TP['headAddPreLogoCodeSimple'] = $data;
	$data = '';
	UnbCallHook('page.postnavi.simple', $data);
	$TP['headAddPostNaviCodeSimple'] = $data;
	$data = '';
	UnbCallHook('page.postwarnings', $data);
	$TP['headAddPostWarningsCode'] = $data;

	// VERSIONCHECK
	if (UnbCheckRights('is_admin') &&
	    !$UNB['Installing'] &&
	    ((rc('enable_versioncheck') &&
	      rc('last_versioncheck') < time() - 43200) ||            // last check was more than 1/2 day ago
	     (rc('enable_versioncheck') &&
	      rc('versioncheck_newavail') == $UNB['VersionTime'])))   // OR: we know there is a newer version
	{
		// Check for new board versions and get details
		$update_url = 'http://newsboard.unclassified.de/version/check';
		$download_url = 'http://newsboard.unclassified.de/download';

		if (preg_match('_^http://(.*?)(/.*)?$_i', $update_url, $m))
		{
			$host = $m[1];
			$url = $m[2];
			if (!$url) $url = '/';
		}
		else
		{
			$TP['errorMsg'] .= 'Version check error: Cannot parse version check URL.<br />';
			$host = '';
		}

		$lines = array();
		if ($host)
		{
			// Download the file
			$sock = fsockopen($host, 80, $errno, $errstr, 2);
			if ($sock !== false)
			{
				$toSend = "GET " . $url . " HTTP/1.0\r\n";
				$toSend .= "Host: " . $host . "\r\n";
				$toSend .= "User-Agent: Unclassified NewsBoard\r\n";
				$toSend .= "Accept-Language: $UNB[DefaultLang]\r\n";
				$toSend .= "Connection: close\r\n";
				$toSend .= "Referer: " . TrailingSlash(rc('home_url')) . rc('baseurl') . "\r\n";
				$toSend .= "Cookie: " . 'unbver=' . urlencode($UNB['Version']) . '; phpver=' . urlencode(phpversion()) . '; os=' . urlencode(str_replace(' ', '-', php_uname('s') . ' ' . php_uname('r'))) . "\r\n";
				$toSend .= "\r\n";
				fwrite($sock, $toSend);
				$copy = false;
				while (($a = fgets($sock, 1024)) !== false && $a !== NULL)   // older PHP makes NULL here and need the explicit length
				{
					if ($copy) $lines[] = $a;
					if (trim($a) == '') $copy = true;
				}
				fclose($sock);
			}
		}

		// file() method needs allow_url_fopen=on and waits 20 seconds if host is not available
		#$lines = file($update_url);
		if (trim($lines[0]) == 'UnclassifiedNewsBoardVersionFile1')
		{
			// this is a valid version file
			$ver = array();
			foreach ($lines as $line)
			{
				$a = explode(';', $line);
				$ver[trim($a[0])] = trim($a[1]);
				$vertime[trim($a[0])] = trim($a[2]);
			}
			$newAvailable = false;

			// is this a development version running?
			if (UnbVersionGetBranch($UNB['Version']) == 'devel')
			{
				// only take devel/testing versions for upgrading, no stable/patches
				if ($vertime['devel'] >= $vertime['testing'])
				{
					$v = $ver['devel'];
					$t = $vertime['devel'];
				}
				else
				{
					$v = $ver['testing'];
					$t = $vertime['testing'];
				}
				$newAvailable = $t > $UNB['VersionTime'];   // compare versions by time
			}
			elseif (UnbVersionGetBranch($UNB['Version']) == 'testing')
			{
				// only take stable/testing versions for upgrading, no devels/patches
				if (UnbCompareVersions($ver['testing'], $ver['stable'], true) > 0)
				{
					$v = $ver['testing'];
					$t = $vertime['testing'];
				}
				else
				{
					$v = $ver['stable'];
					$t = $vertime['stable'];
				}
				$newAvailable = (UnbCompareVersions($v, $UNB['Version']) > 0);   // compare versions by counter
			}
			elseif (UnbVersionGetBranch($UNB['Version']) == 'stable')
			{
				// only take stable/patch versions for upgrading, no devels
				if (UnbCompareVersions($ver['patch'], $ver['stable']) > 0)
				{
					$v = $ver['patch'];
					$t = $vertime['patch'];
				}
				else
				{
					$v = $ver['stable'];
					$t = $vertime['stable'];
				}
				$newAvailable = (UnbCompareVersions($v, $UNB['Version']) > 0);   // compare versions by counter
			}

			if ($newAvailable)
			{
				// TODO: Format as local date
				$date = substr($t, 0, 4) . '-' . substr($t, 4, 2) . '-' . substr($t, 6, 2);
				if (strlen($t) > 8) $date .= ', ' . substr($t, 9, 2) . ':' . substr($t, 11, 2);

				$msg = $UNB_T['head.new version available'];
				$msg = str_replace('{v}', t2h(UnbVersionTitle($v)), $msg);
				$msg = str_replace('{d}', t2h($date), $msg);
				$msg = str_replace('{u}', UnbLink($download_url, null, true, /*sid*/ false, /*derefer*/ true), $msg);
				$TP['infoMsg'] .= $msg . '<br />';

				$UNB['ConfigFile']['versioncheck_newavail'] = $UNB['VersionTime'];
			}
		}

		$UNB['ConfigFile']['last_versioncheck'] = time();
		UnbRebuildConffile();
	}

	// NEED AN UPGRADE?
	if (UnbCheckRights('is_admin') &&
	    rc('last_upgrade_version_time') < $UNB['VersionUpgrade'] &&
	    !$UNB['Installing'])
	{
		$TP['infoMsg'] .= str_replace('{u}', UnbLink('install.php', null, true), $UNB_T['head.need to upgrade']) . '<br />';
	}

	// ADMIN-LOCK CHECK
	if (rc('admin_lock') && !$UNB['Installing'])
	{
		if (!UnbCheckRights('is_admin'))
		{
			$TP['errorMsg'] .= $UNB_T['head.board locked'];
			$announce = new IAnnounce;
			if ($announce->Find(-1))
			{
				// Show maintenance message
				$GLOBALS['ABBC']['Config']['output_div'] = 2;
				$TP['errorMsg'] .= '<br /><br />' . AbbcProc($announce->GetMsg());
			}
			UteRemember('_head.html', $TP);

			UnbEndHTML();
			UteShowAll();
			exit();
		}
		else
		{
			$TP['infoMsg'] .= str_replace(
					'{u}',
					UnbLink('@cp', 'cat=security#admin_lock', true),
					$UNB_T['head.board locked note']) .
				'<br />';
		}
	}

	UteRemember('_head.html', $TP);

	// ----- BEGIN content ----- (after this function call)
}

// End general HTML page
//
// in simple = (bool) analogue to BeginHTML call. Show run+DB timing information
//
function UnbEndHTML()
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];
	$simple = ( isset($TP['headSimple']) ? $TP['headSimple'] : false );

	// ----- END content -----

	$data = '';
	UnbCallHook('page.prefootline', $data);
	if ($data) $TP['footAddPreFootCode'] = $data;

	if (!$simple && rc('show_login') && !rc('login_top') && !$UNB['Installing'])
	{
		if (!isset($TP['footLoginControl']))
			$TP['footLoginControl'] = $UNB['loginControlData'];
	}

	if (rc('foot_line'))
	{
		$TP['showFootline'] = true;

		// ----- COPYRIGHT LINK -----
		$TP['footVendorLink'] = UnbLink('http://newsboard.unclassified.de', null, true, /*sid*/ false, /*derefer*/ true);
		$TP['footVersionNumber'] = UnbVersionTitle($UNB['Version']);

		if (!$simple && rc('foot_db_time'))
		{
			$time = debugPerformMsec();
			$TP['footPageTime'] = format_number($time / 1000, 1, 1000, ' ') . 's';
			$TP['footPageTimeParse'] = format_number($UNB['ParseTime'] / 1000, 1, 1000, ' ') . 's';
			$TP['footPageTimeCombined'] = $TP['footPageTime'] . ' (' . format_number(($time - $UNB['ParseTime']) / 1000, 1, 1000, ' ') . 's)';
			$TP['footPageQueries'] = $UNB['Db']->GetCount();
			$TP['footPageQueriesTime'] = format_number($UNB['Db']->GetTime() / 1000, 1, 1000, ' ') . 's';
		}

		$offset = $UNB['Timezone']['offset'];
		if ($UNB['Timezone']['withdst']) $offset += (date('I') ? 3600 : 0);
		$tz = ($offset >= 0 ? '+' : '-');
		$offset = abs($offset) / 60;
		$tz .= str_pad(intval($offset / 60), 2, '0', STR_PAD_LEFT) . ':';
		$tz .= str_pad($offset % 60, 2, '0', STR_PAD_LEFT);
		$TP['footTimezone'] = $tz;

		$TP['footCurrentTime'] = UnbFormatTime(null, 1 | 2 | 4);

		$data = '';
		UnbCallHook('page.footline', $data);
		if ($data) $TP['footCustomText'] = $data;
	}

	if (!$simple)
	{
		$data = '';
		UnbCallHook('page.postfootline', $data);
		if ($data) $TP['footAddPostFootCode'] = $data;
	}

	$data = '';
	UnbCallHook('page.postfootline.simple', $data);
	if ($data) $TP['footAddPostFootCodeSimple'] = $data;

	UteRemember('_foot.html', $TP);

	#echo '<pre>PLUGINS='; print_r($UNB['PlugIns']); echo '</pre>';

	if (rc('indep_page') || $simple || $UNB['Installing'])
	{
		UnbSetRelLink('top', UnbLink('@main'), $UNB_T['overview']);

		$add = '';
		if (is_array($UNB['PageRelLinks'])) foreach ($UNB['PageRelLinks'] as $rel => $link)
		{
			$title = $link['title'];
			if ($title != '') $title = ' title="' . t2i($title) . '"';
			$type = $link['type'];
			if ($type != '') $type = ' type="' . t2i($type) . '"';
			$add .= '<link rel="' . t2i($rel) . '"' . $type . ' href="' . t2i($link['href']) . '"' . $title . ' />' . endl;
		}
		$TP['headAdd'] = $add;

		// Final chance for plug-ins to modify the page output
#		UnbCallHook('page.buffer.head', $HTMLHEAD);
#		UnbCallHook('page.buffer.body', $HTMLBODY);   // TODO
	}

	// ----- END page -----

	// database is left open so that frame page can access it
	if (!$simple) eval($UNB['FrameEndPage']);

	$UNB['Db']->Close();
}

// Output additional CSS resources that other templates have requested.
//
// This information can only be retrieved at display time of the foot template.
// This function is intended to be called from the foot template.
//
function UnbAdditionalPageRefs()
{
	// Temporarily disabled, because these CSS references were only known at
	// the end of the page, way outside HTML's <head/> section, and it doesn't
	// make a great difference to load all CSS files at the moment.
	return '';

	global $UNB;

	$add = '';
	if (is_array($UNB['RequiredCss']))
	{
		foreach ($UNB['RequiredCss'] as $file)
		{
			$add .= '<style type="text/css">@import url(' . $UNB['CssBaseURL'] . $file . '.css.php);</style>' . endl;
		}
	}
	return $add;
}

// Add a CSS file to the list of required CSS files.
//
// These files will be included when the HTML header is generated.
// This function is usually called from a template with {require-css "name"}.
//
// in file = (string) CSS file basename
//
function UnbRequireCss($file)
{
	global $UNB;

	if (!is_array($UNB['RequiredCss'])) $UNB['RequiredCss'] = array();
	if (!in_array($file, $UNB['RequiredCss'])) $UNB['RequiredCss'][] = $file;
}

// Add a JavaScript file to the list of required JavaScript files.
//
// This function is usually called from a template with {require-js "name"}.
//
// in file = (string) JavaScript file basename
//
function UnbRequireJs($file)
{
	global $UNB;

	if (!is_array($UNB['RequiredJs'])) $UNB['RequiredJs'] = array();
	if (!in_array($file, $UNB['RequiredJs']))
	{
		$UNB['RequiredJs'][] = $file;
		$ret = '<script type="text/javascript" src="' . $UNB['JsBaseURL'] . $file . '.js"></script>' . endl;
	}
	return $ret;
}

// -------------------- Some little page elements --------------------

// Display the login form or current login status
//
function UnbLoginControl()
{
	global $UNB, $UNB_T;

	switch ($UNB['ThisPage'])
	{
		case '@main':
			$params = array(
				'module' => 'main',
				'id' => $_GET['id'],
				'page' => $_GET['page'],
				'key' => UnbUrlGetKey());
			break;
		case '@thread':
			$params = array(
				'module' => 'thread',
				'id' => $_GET['id'],
				'postid' => $_GET['postid'],
				'page' => $_GET['page'],
				'key' => UnbUrlGetKey());
			break;
		case '@stat':
			$params = array(
				'module' => 'stat',
				'page' => $_GET['page'],
				'key' => UnbUrlGetKey());
			break;
		case '@post':
			$params = array(
				'module' => 'post',
				'thread' => $_GET['thread'],
				'quote' => $_GET['quote'],
				'replyto' => $_GET['replyto'],
				'forum' => $_GET['forum'],
				'key' => UnbUrlGetKey());
			break;
		case '@search':
			$params = array(
				'module' => 'search',
				'special' => $_GET['Special'],
				'key' => UnbUrlGetKey());
			break;
		default:
			$params = array(
				'key' => UnbUrlGetKey());
	}

	UnbCallHook('page.logincontrol', $params);
	if (is_string($params))
		return $params;

	$out = '';
	if ($UNB['LoginUserID'])
	{
		$out .= $UNB_T['login.logged in as'] . ' <b>' . t2h($UNB['LoginUserName']) . '</b> (<a href="' . UnbLink('@setuser', $params, true) . '">' . $UNB_T['login.logout'] . '</a>)';
	}
	else
	{
		$text = $UNB_T['login.name id'];
		$text_js = str_replace('\'', '\\\'', $text);
		if (trim($_GET['name']) != '' && $_GET['err'] > 0)
		{
			$text = trim($_GET['name']);
			$text_js = '';
		}

		$out .= '<form action="' . UnbLink('@setuser', $params, true) . '" method="post">';
		$out .= $UNB_T['login.not logged in'] . ' <input type="text" class="text" name="LoginName" value="' . t2i($text) . '" size="10" maxlength="40" tabindex="' . ++$GLOBALS['UnbTplTabIndex'] . '" title="' . $UNB_T['login.name id~'] . '" onfocus="if (this.value==\'' . $text_js . '\') this.value=\'\';" onblur="if (this.value==\'\') this.value=\'' . $text_js . '\';" style="width: 7em;" /> ';
		$out .= '<input type="password" class="text" name="LoginPassword" value="****" size="10" maxlength="40" tabindex="' . ++$GLOBALS['UnbTplTabIndex'] . '" title="' . $UNB_T['login.password~'] . '" onfocus="if (this.value==\'****\') this.value=\'\';" onblur="if (this.value==\'\') this.value=\'****\';" style="width: 7em;" /> ';
		$out .= '<input type="submit" class="button" value="' . $UNB_T['login.login'] . '" tabindex="' . ++$GLOBALS['UnbTplTabIndex'] . '" />';
		#$out .= ' &middot; <a href="' . UnbLink('@main', 'mkpass=-1', true) . '">' . $UNB_T['login.lost password'] . '</a>';
		$out .= ' &middot; <a href="' . UnbLink('http://newsboard.unclassified.de/docs/usage/register#forgot-password', null, true, /*sid*/ false, /*derefer*/ true) . '">' . $UNB_T['login.lost password'] . '</a>';
		if (!rc('admin_lock'))
		{
			$out .= ' &middot; ';
			if (rc('new_user_validation') > 0)
			{
				$out .= '<a href="' . UnbLink('@register', null, true) . '">' . $UNB_T['login.register'] . '</a>';
			}
			else
			{
				// Registrations are currently disabled, replace the link with a static text note.
				$out .= '<em>' . $UNB_T['login.registration disabled'] . '</em>';
			}
		}
		$out .= '</form>';
	}
	return $out;
}

// Show the current forum/thread hierarchy (for navigation)
//
// in id = (int) forum ID
// in threadsubject = (string) thread's subject (and display thread subject)
// in desc = (string) forum/thread description
// in threadid = (int) thread ID (and make a link to there)
// in top = (bool) display this line at the top of the page (otherwise it's at the bottom, so show less info)
// in important = (bool) include an "Important" note for this thread
// in bookmarked = (bool) show the bookmark icon next to the thread subject if this feature is enabled
//
function UnbShowPath($id, $threadsubject = '', $desc = '', $threadid = 0, $top = false, $important = false, $bookmarked = false)
{
	global $ABBC, $UNB, $UNB_T;

	$data = array($id, $threadsubject, $desc, $threadid, $top, $important, $bookmarked);
	UnbCallHook('page.showpath', $data);
	if (is_string($data))
		return $data;

	$forum = new IForum();
	$path = '';
	$fdesc = false;   // forum description
	$n = 0;
	$sep = '<span class="sep">' . $UNB['Design']['ForumSeparator'] . '</span>';
	$count = 0;

	// ----- RSS ICON LINK -----
	if (rc('show_forum_rss_link') /*&& $UNB['ThisPage'] == '@main'*/)
	{
		if ($id == 0)
			$rsslink = '<a href="' . UnbLink('@rss', null, true, /*sid*/ false) . '"><img ' . $UNB['Image']['rss'] . ' title="' . $UNB_T['feed.rss20.all posts'] . '" /></a>';
		elseif ($id > 0)
			$rsslink = '<a href="' . UnbLink('@rss', 'type=1&forum=' . $id, true, /*sid*/ false) . '"><img ' . $UNB['Image']['rss'] . ' title="' . $UNB_T['feed.rss20.this forum'] . '" /></a>';
		$rsslink = ' ' . $rsslink;
	}

	while ($id > 0 && $forum->Load($id))
	{
		$path =
			(($path == '') ? '<b>' : '') .
			'<a href="' . UnbLink('@main', 'id=' . $id, true) . '">' . t2h($forum->GetName()) . '</a>' .
			(($path == '') ? '</b>' . $rsslink : '') .
			(($path == '') ? '' : $sep) .
			$path;
		$id = $forum->GetParent();
		if ($fdesc === false) $fdesc = $forum->GetDescription();
		$count++;
	}

	if (rc('path_with_overview'))
		$path = '<a href="' . UnbLink('@main', null, true) . '">' . (($path == '') ? '<b>' : '') . $UNB_T['overview'] . (($path == '') ? '</b>' : '') . '</a>' . (($path == '') ? $rsslink : $sep) . $path;
	else
		if ($path == '') $path = $UNB_T['overview'] . $rsslink . (($path == '') ? '' : $sep) . $path;

	$out = '<div class="path">';
	if ($threadsubject == '')
	{
		$out .= $UNB_T['forum'] . ': ' . $path;
		$x = $ABBC['Config']['output_div'];
		$ABBC['Config']['output_div'] = 2;
		if ($fdesc) $out .= '<div class="desc">' . AbbcProc($fdesc) . '</div>';
		$ABBC['Config']['output_div'] = $x;
	}
	elseif ($top)
	{
		#$threadid = 0;   // don't make the thread title a hyperlink
		if ($threadid > 0)
			$threadsubject = '<a href="' . UnbLink('@thread', 'id=' . $threadid, true) . '">' . t2h($threadsubject) . '</a>';
		else
			$threadsubject = t2h($threadsubject);

		$data = array(
			'thread' => null,
			'threadid' => $threadid,
			'output' => '');
		UnbCallHook('threadview.presubject', $data);
		$addPreSubject = $data['output'];
		$data['output'] = '';
		UnbCallHook('threadview.postsubject', $data);
		$addPostSubject = $data['output'];

		$out .= $UNB_T['forum'] . ': ' . $path . '<div class="thread">' . $addPreSubject . '<span class="subject">' . $threadsubject . '</span>' . $addPostSubject;
		if ($important)
			$out .= ' <small>(<b>' . $UNB_T['important thread'] . '</b>)</small>';
		if (rc('show_bookmarked_thread') && $bookmarked)
			$out .= ' <img ' . $UNB['Image']['bookmark']. ' title="' . $UNB_T['bookmark'] . '" />';
		$out .= '</div>';
		if ($desc != '')
			$out .= '<div class="desc">' . t2h($desc) . '</div>';
	}
	else
	{
		$out .= $UNB_T['forum'] . ': ' . $path;
	}

	$out .= '</div>';

	return $out;
}

// Make a link to a user's profile displaying the username
//
// in userid = (int) user ID (no link if 0)
// in name = (string) user name
// in bold = (bool) make <b> tags
// in email = (bool) point link to e-mail page
// in t2h = (bool) HTML-encode the name. set to false to include HTML code (like an image) to display
//
function UnbMakeUserLink($userid, $name, $bold = false, $email = false, $t2h = true)
{
	global $UNB;

	$link = '';
	if ($bold) $link .= '<b>';
	if ($userid > 0)
	{
		$link .= '<a href="' . UnbLink('@cp', 'id=' . $userid . ($email ? '&action=email' : ''), true) . '">';
		$link .= ($t2h ? t2h($name) : $name) . '</a>';
	}
	else
		$link .= ($t2h ? t2h($name) : $name);
	if ($bold) $link .= '</b>';

	return $link;
}

// Make a link to a specified post
//
// in post = (int) post id
//           (array(post id, thread id, timestamp, *))
//           (IPost object)
// in page = (int) page number the post is on, if already known or e.g. -1 (saves some database queries, again)
//                 (Note: since thread.inc takes a postid parameter, this information is not required)
// in customtag = false: create full link with image
//                true: only create the <a> tag. Caller must set the closing </a> tag.
//                2: only return the URL
//                3: return full URL without session ID (for RSS feed or notification messages)
//
function UnbMakePostLink($post, $page = 0, $customtag = false)
{
	global $UNB, $UNB_T;

	if (is_int($post))
	{
		$postid = $post;
		$threadid = 0;
		$date = 0;
	}
	elseif (is_array($post))
	{
		$postid = intval($post['ID']);
		$threadid = intval($post['Thread']);
		$date = intval($post['Date']);
	}
	elseif (is_object($post))
	{
		$postid = $post->GetID();
		$threadid = $post->GetThread();
		$date = $post->GetDate();
	}
	else
	{
		// neither int nor array nor object given
		return false;
	}

	if ($customtag === false)
	{
		$out = '<a href="' .
			UnbLink(
				'@thread',
				array(
					'postid' => $postid),
				true) .
			'">';
		$out .= '<img ' . $UNB['Image']['goto_post'] . ' title="' . $UNB_T['goto post'] . '" /></a>';
	}
	else if ($customtag === true)
	{
		$out = '<a href="' .
			UnbLink(
				'@thread',
				array(
					'postid' => $postid),
				true) .
			'">';
	}
	else if ($customtag === 2)
	{
		$out = UnbLink(
				'@thread',
			array(
				'postid' => $postid),
			true);
	}
	else if ($customtag === 3)
	{
		$out = TrailingSlash(rc('home_url')) . UnbLink(
				'@thread',
			array(
				'postid' => $postid),
			false,
			/*sid*/ false);
	}
	else
	{
		$out = '';
	}

	return $out;
}

// Build a list of pages for multi-page views
//
// in count = (int) total count of items in the list
// in perpage = (int) number of items to display on one page
// in,out page = (int) current page number
// in params = (array) link parameters for the links to other pages
//
// returns (string) HTML code with page selection links
//
// exports $UNB['TP']['LastPageCount'] (int) number of pages
//
function UnbPageSelection($count, $perpage, &$page, $params = null)
{
	global $UNB, $UNB_T;

	if (!$count) return '';

	if (!isset($params)) $params = array();

	if (!$perpage)
		$page_count = 1;
	elseif ($count > $perpage)
		$page_count = ceil($count / $perpage);
	else
		$page_count = 1;
	$UNB['TP']['LastPageCount'] = $page_count;

	if ($page > $page_count) $page = $page_count;
	if ($page == -1) $page = $page_count;
	$UNB['TP']['ThisPageCount'] = $page;

	if ($params['##'])
	{
		$targetanchor = $params['##'];
		$params['##'] = null;
	}

	$page_str = '';
	if ($page_count > 1)
	{
		$boundary = 3;   // pages to show at the boundaries (begin/end)
		$middle = 2;     // pages to show around current page in the middle

		$page_str .= $UNB_T['page'] . ': ';
		if ($page > 1)
		{
			$addparams = array('page' => $page - 1);
			if ($targetanchor)
				$addparams['#'] = $targetanchor . 'bottom';
			$page_str .= '<a href="' .
				UnbLink(
					'@this',
					array_merge(
						$params,
						$addparams),
					true) .
				'">' . $UNB['Design']['PrevPage'] . $UNB_T['prev page'] . '</a>&nbsp;';
		}
		$state = 0;
		for ($n = 1; $n <= $page_count; $n++)
		{
			// show pages if they were the only one replaced by '...'
			$med_left = ($n == $boundary + 1 && $n == $page - $middle - 1);
			$med_right = ($n == $page + $middle + 1 && $n == $page_count - ($boundary - 1) - 1);

			if ($n <= $boundary || $med_left ||  abs($page - $n) <= $middle || $med_right || $n > $page_count - $boundary)
			{
				if ($n == $page)
					$page_str .= '&nbsp;<b>' . $n . '</b>&nbsp;';
				else
					$page_str .= '<a href="' .
						UnbLink(
							'@this',
							array_merge(
								$params,
								array('page' => $n)),
							true) .
							'">&nbsp;' . $n . '&nbsp;</a>';
			}
			else
			{
				if ($n < $page && $state < 1) { $page_str .= '...'; $state = 1; }
				if ($n > $page && $state < 2) { $page_str .= '...'; $state = 2; }
			}
		}
		if ($page < $page_count)
		{
			$addparams = array('page' => $page + 1);
			if ($targetanchor)
				$addparams['#'] = $targetanchor . 'top';
			$page_str .= '<a href="' .
				UnbLink(
					'@this',
					array_merge(
						$params,
						$addparams),
					true) .
					'">&nbsp;' . $UNB_T['next page'] . $UNB['Design']['NextPage'] . '</a>';
		}
	}

	if ($page_count)
	{
		if ($page > 1)
		{
			UnbSetRelLink(
				'first',
				UnbLink(
					'@this',
					array_merge(
						$params,
						array('page' => 1))),
					$UNB_T['page'] . ' 1');
			UnbSetRelLink(
				'prev',
				UnbLink(
					'@this',
					array_merge(
						$params,
						array('page' => $page - 1))),
					$UNB_T['prev page~']);
		}
		if ($page < $page_count)
		{
			UnbSetRelLink(
				'last',
				UnbLink(
					'@this',
					array_merge(
						$params,
						array('page' => $page_count))),
					$UNB_T['page'] . ' ' . $page_count);
			UnbSetRelLink(
				'next',
				UnbLink(
					'@this',
					array_merge(
						$params,
						array('page' => $page + 1))),
					$UNB_T['next page~']);
		}
	}

	return $page_str;
}

?>
