<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// search.inc.php
// Search form

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

@header('Pragma: no-cache');
@header('Cache-Control: no-cache');
@header('Expires: ' . date('r', time()));   // now

require_once(dirname(__FILE__) . '/common_post.lib.php');

if ($_REQUEST['use_result'])
{
	$_REQUEST['Query'] = $_SESSION['search_Query'];
	$_REQUEST['ResultView'] = $_SESSION['search_ResultView'];
	$_REQUEST['Forum'] = $_SESSION['search_Forum'];
	$_REQUEST['InSubject'] = $_SESSION['search_InSubject'];
	$_REQUEST['InMessage'] = $_SESSION['search_InMessage'];
	$_REQUEST['InUser'] = $_SESSION['search_InUser'];
	$_REQUEST['Sort'] = $_SESSION['search_Sort'];
	$_REQUEST['DateFrom'] = $_SESSION['search_DateFrom'];
	$_REQUEST['DateUntil'] = $_SESSION['search_DateUntil'];
	$_REQUEST['Special'] = $_SESSION['search_Special'];
}

// -------------------- Import request variables --------------------

$page = intval($_GET['page']);
if ($page < 1) $page = 1;

$topforum = intval($_REQUEST['Forum']);

if ($_REQUEST['ResultView'] != 1) $_REQUEST['ResultView'] = 2;
if ($_REQUEST['Sort'] != 1) $_REQUEST['Sort'] = 2;

UnbRequireTxt('search');

$error = false;

$TP =& $UNB['TP'];

// -------------------- Prepare special search links output --------------------

$more_search_links = '<table cellspacing="0" cellpadding="0" width="100%"><tr style="vertical-align: top;">
	<td width="50%">
		<a href="' . UnbLink('@search', 'nodef=1&Special=new&ResultView=1', true) . '">' . $UNB_T['search.current topics since last login'] . '</a><br />
		&nbsp; &nbsp; <a href="' . UnbLink('@search', 'nodef=1&Special=new&ShowHidden=1&ResultView=1', true) . '">' . $UNB_T['search.all current topics since last login'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Special=unread&ResultView=1', true) . '">' . $UNB_T['search.unread topics'] . '</a><br />
		&nbsp; &nbsp; <a href="' . UnbLink('@search', 'nodef=1&Special=unread&ShowHidden=1&ResultView=1', true) . '">' . $UNB_T['search.all unread topics'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Special=opentopics&ResultView=1', true) . '">' . $UNB_T['search.open topics'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Special=currentpolls&ResultView=1', true) . '">' . $UNB_T['search.current polls'] . '</a> -
		<a href="' . UnbLink('@search', 'nodef=1&Special=newvotes&ResultView=1', true) . '">' . $UNB_T['search.new votes'] . '</a><br />
		' . $UNB_T['search.current topics of last-'] . '<a href="' . UnbLink('@search', 'nodef=1&DateFrom=' . (time() - 1 * 86400) . '&ResultView=1&Sort=2&title=1', true) . '">&nbsp;1&nbsp;</a><a href="' . UnbLink('@search', 'nodef=1&DateFrom=' . (time() - 3 * 86400) . '&ResultView=1&Sort=2&title=3', true) . '">&nbsp;3&nbsp;</a><a href="' . UnbLink('@search', 'nodef=1&DateFrom=' . (time() - 7 * 86400) . '&ResultView=1&Sort=2&title=7', true) . '">&nbsp;7&nbsp;</a>' . $UNB_T['search.-days'] . '
	</td>
	<td width="50%">
		<a href="' . UnbLink('@search', 'nodef=1&Special=mytopics&ResultView=1', true) . '">' . $UNB_T['search.my topics'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Query=' . $UNB['LoginUserID'] . '&ResultView=2&InUser=1&Sort=2', true) . '">' . $UNB_T['search.my posts'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Query=' . $UNB['LoginUserID'] . '&ResultView=1&InUser=1&Sort=2', true) . '">' . $UNB_T['search.my posted'] . '</a><br />
		<a href="' . UnbLink('@search', 'nodef=1&Special=recentlyviewed&ResultView=1', true) . '">' . $UNB_T['search.recently viewed'] . '</a>
	</td>
</tr></table>' . endl;

// -------------------- BEGIN page --------------------

UnbBeginHTML($UNB_T['search']);

$TP['headNoIndex'] = true;

if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity(UNB_ULF_SEARCH);
else UnbSetGuestLastForum(UNB_ULF_SEARCH);

if ($_GET['nodef'] != 1 && $_GET['where'] == '')
{
	// ----- Show search definition form -----
	UnbAddLog('search_form');

	if (!isset($_REQUEST['Query']))
	{
		$_REQUEST['InMessage'] = 1;
		$_REQUEST['InSubject'] = 1;
	}

	$TP['searchFormUrl'] = UnbLink('@this', null, true, /*sid*/ false, /*derefer*/ false, /*short*/ false);

	$output = '';
	UnbListForumsRec($topforum, false, 0, 0, true, /*noWebLinks*/ true);
	$TP['searchForumsOptions'] = $output;

	// ----- Special search links (advanced options area) -----
	if (!isset($_REQUEST['Query']))
	{
		$TP['searchAdvancedLinks'] = $more_search_links;
	}
}

if ($_REQUEST['Query'] != '' || $_REQUEST['DateFrom'] != '' || $_REQUEST['DateUntil'] != '' || $_REQUEST['where'] != '' || $_REQUEST['Special'] != '')
{
	// ----- There seems to be some work to do -----

	if ($_GET['nodef'] != 1 && $_GET['where'] == '')
	{
		UnbAddLog('search_query ' . $_REQUEST['Query']);
	}

	$post = new IPost;
	$thread = new IThread;

	$query = '';        // SQL query
	$title = '';        // title to display
	$get = null;        // GET parameters to pass other pages of the list
	$markread = false;  // Offer 'mark all forums as read' link
	$viewed_order = '';

	/*if ($_REQUEST['where'] != '')
	{
		// query is already composed
		$query = $_REQUEST['where'];
		$get = $_REQUEST['where'];
		// WARNING: Possible SQL injection vulnerability?
		// This code is obsolete anyway (and thus disabled)
	}
	else*/
	if ($_REQUEST['Special'] == 'new')
	{
		if ($UNB['LoginUserID'])
		{
			if (intval($_REQUEST['ShowHidden']))
				$mask = 0;
			else
				$mask = UNB_UFF_IGNORE | UNB_UFF_HIDE;

			// search unread Threads
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					// UserForumFlags, only linked via forum ID
					array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
					// UserForumFlags, only linked via thread ID
					array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID')),
				/*fields*/ 't.ID',
				/*where*/ 't.LastPostDate >= ' . $UNB['LoginUser']->GetLastLogout() . ' AND ' .
					'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND ' .
					// Entire forum must not be hidden
					'(uff_f.Flags IS NULL OR NOT (uff_f.Flags & ' . $mask . ')) AND' .
					// Particular thread must not be hidden
					'(uff_t.Flags IS NULL OR NOT (uff_t.Flags & ' . $mask . '))');

			if ($ids)
			{
				$ids_str = join(',', $ids);
				$query .= 't.ID IN (' . $ids_str . ')';
			}
			else
			{
				$query .= '0';   // force query to fail
			}

			$title = $UNB_T['search.current topics since last login'];
			#$get = "nodef=1&Special=new&ResultView=1";
			$markread = true;
		}
		else
		{
			$error .= $UNB_T['error.only for members'] . '<br />';
		}
	}

	elseif ($_REQUEST['Special'] == 'unread')
	{
		if ($UNB['LoginUserID'])
		{
			if (intval($_REQUEST['ShowHidden']))
				$mask = 0;
			else
				$mask = UNB_UFF_IGNORE | UNB_UFF_HIDE;

			// search unread Threads
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'ThreadWatch', 'tw', 't.ID = tw.Thread AND tw.User = ' . $UNB['LoginUserID']),
					// UserForumFlags, only linked via forum ID
					array('LEFT', 'UserForumFlags', 'uff_f', 'uff_f.User = ' . $UNB['LoginUserID'] . ' AND uff_f.Forum = t.Forum AND uff_f.Thread = 0'),
					// UserForumFlags, only linked via thread ID
					array('LEFT', 'UserForumFlags', 'uff_t', 'uff_t.User = ' . $UNB['LoginUserID'] . ' AND uff_t.Forum = 0 AND uff_t.Thread = t.ID')),
				/*fields*/ 't.ID',
				/*where*/ '(tw.LastRead < t.LastPostDate OR tw.LastRead IS NULL) AND ' .
					'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND ' .
					// Entire forum must not be hidden
					'(uff_f.Flags IS NULL OR NOT (uff_f.Flags & ' . $mask . ')) AND' .
					// Particular thread must not be hidden
					'(uff_t.Flags IS NULL OR NOT (uff_t.Flags & ' . $mask . '))');

			if ($ids)
			{
				$ids_str = join(',', $ids);
				$query .= 't.ID IN (' . $ids_str . ')';
			}
			else
			{
				$query .= '0';   // force query to fail
			}

			$title = $UNB_T['search.unread topics'];
			#$get = "nodef=1&Special=unread&ResultView=1";
			$markread = true;
		}
		else
		{
			$error .= $UNB_T['error.only for members'] . '<br />';
		}
	}
	elseif ($_REQUEST['Special'] == 'currentpolls')
	{
		// search current polls
		$query = 't.Options & ' . UNB_THREAD_POLL . ' AND t.Question != "" AND NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND t.Date >= ' . (time() - 3600 * 24 * rc('poll_current_days')) . ' AND (' . time() . ' < (t.Date + t.PollTimeout * 3600) OR t.PollTimeout = 0)';
		// last _ days, see config + poll has not ended yet

		$title = $UNB_T['search.current polls'];
		#$get = "nodef=1&Special=currentpolls&ResultView=1";
	}
	elseif ($_REQUEST['Special'] == 'opentopics')
	{
		// search open topics
		$query = 'NOT (t.Options & ' . UNB_THREAD_CLOSED . ') AND NOT (t.Options & ' . UNB_THREAD_MOVED . ')';

		$title = $UNB_T['search.open topics'];
		#$get = "nodef=1&Special=opentopics&ResultView=1";
	}
	elseif ($_REQUEST['Special'] == 'newvotes')
	{
		if ($UNB['LoginUserID'])
		{
			// search polls with new votes the user has not yet seen (LastVoted > LastViewed)
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'ThreadWatch', 'tw', 't.ID = tw.Thread AND tw.User = ' . $UNB['LoginUserID'])),
				/*fields*/ 't.ID',
				/*where*/ 't.LastVoted > tw.LastViewed OR tw.LastViewed IS NULL')
			or $ids = array();

			$ids_str = join(',', $ids);
			if ($ids_str) $query .= 't.ID IN (' . $ids_str . ')';
			else          $query .= '0';   // force query to fail

			$title = $UNB_T['search.new votes'];
			#$get = "nodef=1&Special=newvotes&ResultView=1";
			$markread = true;
		}
		else
		{
			$error .= $UNB_T['error.only for members'] . '<br />';
		}
	}
	elseif ($_REQUEST['Special'] == 'mytopics')
	{
		if ($UNB['LoginUserID'])
		{
			// search topics that I have started
			$query = 'NOT (t.Options & ' . UNB_THREAD_MOVED . ') AND t.User = ' . $UNB['LoginUserID'];

			$title = $UNB_T['search.my topics'];
			#$get = "nodef=1&Special=mytopics&ResultView=1";
		}
		else
		{
			$error .= $UNB_T['error.only for members'] . '<br />';
		}
	}
	elseif ($_REQUEST['Special'] == 'recentlyviewed')
	{
		if ($UNB['LoginUserID'])
		{
			// search topics that I have recently viewed
			$ids = $UNB['Db']->FastQuery1stArray('ThreadWatch', 'Thread', 'User=' . $UNB['LoginUserID'] . ' AND LastViewed>0', 'LastViewed DESC', '40');
			if (is_array($ids))
			{
				$ids_str = join(',', $ids);
				if ($ids_str) $query .= 't.ID IN (' . $ids_str . ')';
				else          $query .= '0';   // force query to fail

				$viewed_order = 'CASE t.ID ';
				$n = 0;
				foreach ($ids as $id)
				{
					$viewed_order .= 'WHEN ' . $id . ' THEN ' . $n++ . ' ';
				}
				$viewed_order .= 'END';

				$_REQUEST['Sort'] = 1;
			}
			else
			{
				$query .= '1=0';
			}

			$title = $UNB_T['search.recently viewed'];
			#$get = "nodef=1&Special=recentlyviewed&ResultView=1";
		}
		else
		{
			$error .= $UNB_T['error.only for members'] . '<br />';
		}
	}
	else
	{
		// do normal query
		if ($_REQUEST['InSubject'] || $_REQUEST['InMessage'])
		{
			$highlight = array();
			$words = explode_quoted(' ', $_REQUEST['Query']);

			$n = 0;
			// NOTE: possible improvement:
			//       remove all words from the search term that are included in other words
			//       because they won't change the resultset anyway
			foreach ($words as $word)
			{
				if ($word != '')
				{
					$not = false;

					// link every segment correctly
					if ($query != '') $query .= ' ';
					if ($n) $query .= 'AND ';

					// process + and - word modifiers
					$in_subject = '';
					$in_message = '';
					if ($word{0} == '-')
					{
						$not = true;
						$in_subject .= 'NOT ';
						$in_message .= 'NOT ';
						$word = substr($word, 1);
					}

					// case-insensitive search
					$in_subject .= '(p.Subject LIKE \'%' . UnbDbEncode($word, true) . '%\' OR ' .
						't.Desc LIKE \'%' . UnbDbEncode($word, true) . '%\' AND p.Date = t.Date)';
						// find the term in the post's subject or in the thread's subtitle (only if it's the thread's first post)
						// TODO: find a more reliable way to select the first post of a thread than the date (which may differ)
					$in_message .= '(p.Msg LIKE \'%' . UnbDbEncode($word, true) . '%\')';
					// this doesn't work:
					#$pre = $UNB['Db']->tblprefix;
					#$in_subject .= "(SOUNDEX({$pre}Posts.Subject) = SOUNDEX(\"" . UnbDbEncode($word) . '"))';
					#$in_message .= "(SOUNDEX({$pre}Posts.Msg) = SOUNDEX(\"" . UnbDbEncode($word) . '"))';

					$query .= '(';
					if ($_REQUEST['InSubject']) $query .= $in_subject;
					if ($_REQUEST['InSubject'] && $_REQUEST['InMessage']) $query .= ($not ? ' AND ' : ' OR ');
					if ($_REQUEST['InMessage']) $query .= $in_message;
					$query .= ')';

					if (!$not) $highlight[] = $word;

					$n++;
				}
			}

			// sort highlight words descending (by length) so that we always
			// highlight the longest possible part of the text
			rsort($highlight);
		}

		$in_user = '';
		$username = '';
		if ($_REQUEST['InUser'])
		{
			// find any UserID containing this word -> only single-word search here! (Query string is interpreted as 1 word)
			$user = new IUser;
			if (is_numeric($_REQUEST['Query']))
			{
				if ($user->Load(intval($_REQUEST['Query'])))
				{
					$in_user = '(p.User = ' . $user->GetID() . ')';
					$username = $user->GetName();
				}
			}
			elseif (substr($_REQUEST['Query'], 0, 1) == '*')
			{
				$a = $user->GetListArray("Name LIKE \"%" . UnbDbEncode(substr($_REQUEST['Query'], 1), true) . "%\"");
				if (is_array($a) && sizeof($a) > 0)
				{
					$in_user = '(p.User IN (' . join(', ', $a) . ') OR p.UserName LIKE \'%' . UnbDbEncode(substr($_REQUEST['Query'], 1), true) . '%\')';
					$username = $_REQUEST['Query'];
				}
			}
			else
			{
				$id = $user->FindByName($_REQUEST['Query']);
				if ($id > 0)
				{
					$in_user = '(p.User = ' . $id . ' OR p.UserName LIKE \'' . UnbDbEncode($_REQUEST['Query'], true) . '\')';
					$username = $_REQUEST['Query'];
				}
			}

			if ($in_user != '')
			{
				if ($query != '') $query .= ' AND ';
				$query .= $in_user;
				$title = $UNB_T['search.all posts by'] . ' ' . t2h($username);
			}
			else
			{
				$title = $UNB_T['search.all posts by'] . ' ' . $_REQUEST['Query'];
				if ($query != '') $query .= ' AND ';
				$query .= 'p.ID = 0';   // make the query fail
			}
		}

		if ($_REQUEST['DateFrom'] != '')
		{
			if ($query != '') $query .= ' AND ';

			$query .= '(p.Date >= ';

			$a = explode('.', $_REQUEST['DateFrom']);
			if (sizeof($a) == 1)
				$query .= intval($a[0]);   // if there's no . it must be a unix timestamp
			else
				$query .= mktime(0, 0, 0, $a[1], $a[0], $a[2]);

			$query .= ')';

			if (intval($_REQUEST['title']) > 0) $title = $UNB_T['search.current topics of last-'] . $_REQUEST['title'] . $UNB_T['search.-days'];
		}

		if ($_REQUEST['DateUntil'] != '')
		{
			if ($query != '') $query .= ' AND ';

			$query .= '(p.Date < ';

			$a = explode('.', $_REQUEST['DateUntil']);
			if (sizeof($a) == 1)
				$query .= intval($a[0]);   // if there's no . it must be a unix timestamp
			else
				$query .= mktime(0, 0, 0, $a[1], $a[0], $a[2]);

			$query .= ')';
		}

		if ($topforum > 0)
		{
			// Find all forums from selected on
			UnbGetForumsRec($topforum);
			array_push($forums, $topforum);
			if ($query != '') $query .= ' AND ';
			$query .= 't.Forum IN (' . join(', ', $forums) . ')';
		}
		if ($topforum < 0)
		{
			// Restrict search to one thread
			if ($query != '') $query .= ' AND ';
			$query .= 't.ID = ' . -$topforum;
		}
	}

	if (!$error)
	{
		$record = $UNB['Db']->FastQuery(
			/*table*/ array(
				array('', 'Posts', 'p', ''),
				array('LEFT', 'Threads', 't', 'p.Thread = t.ID')),
			/*fields*/ $_REQUEST['ResultView'] == 1 ?
				't.ID, t.Forum' :
				'p.ID, t.ID, t.Forum',
			/*where*/ $query,
			/*order*/ '',
			/*limit*/ '',
			/*group*/ $_REQUEST['ResultView'] == 1 ? 't.ID' : '');

		$ids = array();
		if ($record) do
		{
			if ($_REQUEST['ResultView'] == 1 /*threads*/ && UnbCheckRights('viewforum', $record[1], $record[0]))
				array_push($ids, $record[0]);
			elseif ($_REQUEST['ResultView'] == 2 /*posts*/ && UnbCheckRights('viewforum', $record[2], $record[1]))
				array_push($ids, $record[0]);
		}
		while ($record = $UNB['Db']->GetRecord());

		$ids_str = join(',', $ids);

		$_SESSION['search_Query'] = $_REQUEST['Query'];
		$_SESSION['search_ResultView'] = $_REQUEST['ResultView'];
		$_SESSION['search_Forum'] = $_REQUEST['Forum'];
		$_SESSION['search_InSubject'] = $_REQUEST['InSubject'];
		$_SESSION['search_InMessage'] = $_REQUEST['InMessage'];
		$_SESSION['search_InUser'] = $_REQUEST['InUser'];
		$_SESSION['search_Sort'] = $_REQUEST['Sort'];
		$_SESSION['search_DateFrom'] = $_REQUEST['DateFrom'];
		$_SESSION['search_DateUntil'] = $_REQUEST['DateUntil'];
		$_SESSION['search_Special'] = $_REQUEST['Special'];

		$_SESSION['search_ids'] = $ids_str;
		$_SESSION['search_idcount'] = sizeof($ids);
		$_SESSION['search_markread'] = $markread;
		$_SESSION['search_title'] = $title;
		$_SESSION['search_highlight'] = $highlight;
		$_SESSION['search_viewed_order'] = $viewed_order;

		$_REQUEST['use_result'] = 1;
	}
}

if ($_REQUEST['use_result'] && !$error)
{
	$ids_str = $_SESSION['search_ids'];
	$idcount = $_SESSION['search_idcount'];
	$markread = $_SESSION['search_markread'];
	$title = $_SESSION['search_title'];
	$highlight = $_SESSION['search_highlight'];
	$viewed_order = $_SESSION['search_viewed_order'];

	if ($ids_str != '') $query = 'ID IN (' . $ids_str . ')';
	else                $query = '0';   // force search to fail

	/*if ($get == '')
		$get = (isset($_GET['nodef']) ? "nodef=" . $_GET['nodef'] . "&" : "") .
			(isset($_GET['title']) ? "title=" . $_GET['title'] . "&" : "") .
			"Query=" . $_REQUEST['Query'] . "&InSubject=" . $_REQUEST['InSubject'] . "&InMessage=" . $_REQUEST['InMessage'] . "&InUser=" . $_REQUEST['InUser'] .
			"&ResultView=" . $_REQUEST['ResultView'] . "&Case=" . $_REQUEST['Case'] . "&DateFrom=" . $_REQUEST['DateFrom'] . "&DateUntil=" . $_REQUEST['DateUntil'] . "&Sort=" . $_REQUEST['Sort'] . "&Special=" . $_REQUEST['Special'];*/

	$get = array(
		'use_result' => 1,
		'nodef' => ($_GET['nodef'] == 1 ? 1 : null));

	$TP['searchShowResult'] = true;
	$TP['searchResultsCount'] = $idcount;

	if ($title != '')
	{
		$TP['searchTitle'] = $title;
		UnbAddLog('search_title ' . $title);
	}

	// Show main page announcements on "new posts" and "unread posts" search result pages
	if ($_SESSION['search_Special'] == 'new' || $_SESSION['search_Special'] == 'unread')
	{
		UnbListAnnounces(0, 0, 0, /*readonly*/ true);
	}

	$found = false;
	if ($_REQUEST['ResultView'] == 1)
	{
		// list Threads
		$TP['searchShowThreads'] = true;
		$order = 'LastPostDate';
		if ($_REQUEST['Special'] == 'mytopics') $order = 'Date';
		if ($_REQUEST['Special'] == 'recentlyviewed') $order = $viewed_order;

		$found = UnbListThreads($query . ' AND NOT (Options & ' . UNB_THREAD_MOVED . ')', $page, 0, $get,
			$order . ($_REQUEST['Sort'] == 1 ? '' : ' DESC'),
			'', false, $_REQUEST['ShowHidden'] == 1);
		$markread &= $found;
	}
	else
	{
		// list Posts
		$TP['searchShowPosts'] = true;
		$found = UnbListPosts($query, $page, $get,
			/*order*/ 'Date' . ($_REQUEST['Sort'] == 1 ? '' : ' DESC'),
			false, $highlight, $idcount);
	}

	// Special search links (advanced options area)
	$TP['searchActionSpecial'] = true;
	if ($markread)
	{
		$TP['searchActionMarkRead'] = '<a href="' . UnbLink('@main', 'id=' . $parent . '&allforumsread=1&timestamp=' . time() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['mark all forums read'] . '</a>';
	}
	$TP['searchAdvancedLinksBottom'] = $more_search_links;

	$TP['jumpForumBox'] = UnbJumpForumBox($toplevel);
}

$TP['errorMsg'] .= $error;

UteRemember('search.html', $TP);

UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
