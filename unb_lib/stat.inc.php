<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// stat.inc.php
// Show some board statistics

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// -------------------- Import request variables --------------------

$page = intval($_GET['page']);
if ($page < -1 || $page == 0) $page = 1;

// -------------------- Initialise data --------------------

$pre = $UNB['Db']->tblprefix;

$forum = new IForum;
$thread = new IThread;
$post = new IPost;
$user = new IUser;

// Sum up all table sizes of MY tables
//
// returns (int) table size sum
//
function my_tables_size()
{
	global $UNB;

	$size = 0;
	$size += $UNB['Db']->GetTableSize('ACL');
	$size += $UNB['Db']->GetTableSize('AnnounceRead');
	$size += $UNB['Db']->GetTableSize('Announces');
	$size += $UNB['Db']->GetTableSize('ForumWatch');
	$size += $UNB['Db']->GetTableSize('Forums');
	$size += $UNB['Db']->GetTableSize('GroupMembers');
	$size += $UNB['Db']->GetTableSize('GroupNames');
	$size += $UNB['Db']->GetTableSize('Guests');
	$size += $UNB['Db']->GetTableSize('PollUsers');
	$size += $UNB['Db']->GetTableSize('PollVotes');
	$size += $UNB['Db']->GetTableSize('Posts');
	$size += $UNB['Db']->GetTableSize('Stat');
	$size += $UNB['Db']->GetTableSize('ThreadWatch');
	$size += $UNB['Db']->GetTableSize('Threads');
	$size += $UNB['Db']->GetTableSize('UserForumFlags');
	$size += $UNB['Db']->GetTableSize('Users');

	return $size;
}

// Calculate the entire recursive directory size on the disk
//
// in mycwd = (string) directory name to scan
//
// returns (int) size
//
function get_dir_size($mycwd = '')
{
	$handle = opendir($mycwd);
	if ($handle === false) return 0;

	$size = 0;
	while ($file = readdir($handle))
	{
		if ($file{0} != '.' && !is_dir($mycwd . $file))
		{
			$size += filesize($mycwd . $file);
		}
	}
	closedir($handle);

	return $size;
}

// Show some diagrams
//
// in page = (int) page number
//
function ShowStatDiagrams($page)
{
	global $HTMLHEAD, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	$factor = 1;
	$width1 = 110 * $factor;
	$width2 = $width1;
	$width3 = 110 * $factor;
	$width4 = 70 * $factor;
	$width5 = 60 * $factor;

	$days_per_page = 14;

	// ---------- Page selection ----------
	$day_count = $UNB['Db']->FastQuery1st('Stat', 'COUNT(*)');
	$page_str = UnbPageSelection($day_count, $days_per_page, $page);

	$max = $UNB['Db']->FastQuery('Stat', 'MAX(NewThreads), MAX(NewPosts), MAX(OnlineUsers + OnlineGuests), MAX(PageHits), MAX(NewUsers)');

	if ($days_per_page)
		$limit = (($page - 1) * $days_per_page) . ',' . $days_per_page;
	else
		$limit = '';
	$a = $UNB['Db']->FastQueryArray('Stat', '*', '', 'Date DESC', $limit);
	$TP['statlist'] = array();
	foreach ($a as $b)
	{
		$b['Date'] = intval($b['Date']) + 12 * 3600;   // add 12 hours for correct rounding

		if (date('w', $b['Date']) == 0 || date('w', $b['Date']) == 6)
		{
			$b1 = '<b>';
			$b2 = '</b>';
		}
		else
		{
			$b1 = '';
			$b2 = '';
		}
		$tpitem = array();
		$tpitem['time'] = UnbFormatTime($b['Date'], 1, ', ', /*tz_done*/ true) . ', ' . $b1 . UnbDate('D', $b['Date']) . $b2;

		if ($b['NewThreads'] > 0 && $max[0])
		{
			$tpitem['widthNT'] = round($b['NewThreads'] / $max[0] * $width1);
			$tpitem['widthNTRel'] = round($b['NewThreads'] / $max[0] * 100);
		}
		$tpitem['NewThreads'] = $b['NewThreads'];

		if ($b['NewPosts'] > 0 && $max[1])
		{
			$tpitem['widthNP'] = round($b['NewPosts'] / $max[1] * $width2);
			$tpitem['widthNPRel'] = round($b['NewPosts'] / $max[1] * 100);
		}
		$tpitem['NewPosts'] = $b['NewPosts'];

		if (($b['OnlineUsers'] > 0 || $b['OnlineGuests'] > 0) && $max[2])
		{
			$tpitem['widthOU'] = round($b['OnlineUsers'] / $max[2] * $width3);
			$tpitem['widthOURel'] = round($b['OnlineUsers'] / $max[2] * 100);
			$tpitem['widthOG'] = round($b['OnlineGuests'] / $max[2] * $width3);
			$tpitem['widthOGRel'] = round($b['OnlineGuests'] / $max[2] * 100);

			// Test statistics dual graph:
			#$tpitem['widthOURel'] += $tpitem['widthOGRel'];
			#$tpitem['widthOGRel'] = 0;
		}
		$tpitem['OnlineUsers'] = $b['OnlineUsers'];
		$tpitem['OnlineGuests'] = $b['OnlineGuests'];

		if ($b['PageHits'] > 0 && $max[3])
		{
			$tpitem['widthPH'] = round($b['PageHits'] / $max[3] * $width4);
			$tpitem['widthPHRel'] = round($b['PageHits'] / $max[3] * 100);
		}
		$tpitem['PageHits'] = $b['PageHits'];

		if ($b['NewUsers'] > 0 && $max[4])
		{
			$tpitem['widthNU'] = round($b['NewUsers'] / $max[4] * $width5);
			$tpitem['widthNURel'] = round($b['NewUsers'] / $max[4] * 100);
		}
		$tpitem['NewUsers'] = $b['NewUsers'];

		$TP['statlist'][] = $tpitem;
	}

	if (sizeof($TP['statlist']) > 0)
	{
		$TP['statlist'][0]['firstitem'] = true;
		$TP['statlist'][sizeof($TP['statlist']) - 1]['lastitem'] = true;
	}

	$TP['statPages'] = $page_str ? $page_str : '';
}

// -------------------- BEGIN page --------------------

UnbBeginHTML($UNB_T['statistics']);

$TP =& $UNB['TP'];
$TP['headNoIndex'] = true;

if (!UnbCheckRights('showstat'))
{
	$TP['errorMsg'] .= $UNB_T['error.access denied'] . '<br />';
	UnbErrorLog('Access denied');
}
else
{
	UnbUpdateStat('PageHits', 1);

	// Collect overall statistic values
	$forum_count = $forum->Count('NOT (Flags & ' . (UNB_FORUM_CATEGORY | UNB_FORUM_WEBLINK) . ')');
	$thread_count = $thread->Count();
	$post_count = $post->Count();
	$post_guests = $post->Count('User = 0');
	$poll_count = $thread->Count('Options & ' . UNB_THREAD_POLL);
	$poll_votes = $UNB['Db']->FastQuery1st('PollUsers', 'COUNT(*)');
	$user_count = $user->Count();

	// This only counts users that have registered and are not validated yet.
	// It won't count users that are not member of the Members usergroup for some other reason (banned etc.)
	$not_valid_count = $UNB['Db']->FastQuery1st(
		/*table*/ array(
			array('', 'Users', 'u', ''),
			array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User')),
		/*fields*/ 'COUNT(*)',
		/*where*/ 'u.ValidateKey != \'\' AND gm.User IS NULL')
	or $not_valid_count = 0;

	$database_size = my_tables_size();

	// Don't count the same folder 3 times!
	$upload_folder_size = get_dir_size($UNB['AvatarPath']);
	if ($UNB['PhotoPath'] != $UNB['AvatarPath']) $upload_folder_size += get_dir_size($UNB['PhotoPath']);
	if ($UNB['AttachPath'] != $UNB['PhotoPath'] && $UNB['AttachPath'] != $UNB['AvatarPath']) $upload_folder_size += get_dir_size($UNB['AttachPath']);

	// Display actual page content
	UnbAddLog('view_stat');
	if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity(UNB_ULF_STAT);
	else UnbSetGuestLastForum(UNB_ULF_STAT);

	ShowStatDiagrams($page);

	$TP['statForumCount'] = format_number($forum_count);
	$TP['statUserCount'] = format_number($user_count);
	$TP['statThreadCount'] = format_number($thread_count);
	$TP['statThreadsPerForum'] = ($forum_count > 0 ? format_number($thread_count / $forum_count, 1) : 0);
	$TP['statNotValidCount'] = format_number($not_valid_count);
	$TP['statPostCount'] = format_number($post_count);
	$TP['statPostsPerThread'] = ($thread_count > 0 ? format_number($post_count / $thread_count, 1) : 0);
	$TP['statPostsPerUser'] = ($user_count > 0 ? format_number($post_count / $user_count, 1) : 0);
	$TP['statPollCount'] = format_number($poll_count);
	$TP['statVotesPerPoll'] = ($poll_count > 0 ? format_number($poll_votes / $poll_count, 1) : 0);
	$TP['statPostGuests'] = format_number($post_guests);
	$TP['statPostGuestsPercent'] = ($post_count > 0 ? format_number($post_guests / $post_count * 100, 1) : 0);
	$TP['statDatabaseSize'] = format_number($database_size, 1, 1024, ' ');
	$TP['statUploadSize'] = format_number($upload_folder_size, 1, 1024, ' ');

	UnbListThreads('LastPostDate >= ' . (time() - 14 * 24 * 3600) . ' AND NOT (Options & 8)', 1, 0, null, 'Views DESC', '10', false);

	UteRemember('stat.html', $TP);
}

// PageHits is counted up on the top of this page!

UnbEndHTML();
?>
