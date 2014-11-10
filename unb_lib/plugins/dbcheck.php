<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Check database consistency');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.3', 'version');
UnbPluginMeta('unb.devel.20051101', 'version');

if (!UnbPluginEnabled()) return;

// Hook function to add a new control panel category
//
function UnbHookDbCheckAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_dbcheck.cpcategory',
			'link' => UnbLink('@cp', 'cat=dbcheck&key=' . UnbUrlGetKey(), true),
			'cpcat' => 'dbcheck');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookDbCheckCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'dbcheck' &&
	    UnbCheckRights('is_admin') &&
	    UnbUrlCheckKey())
	{
		$TP['controlpanelMoreCats'][] = 'controlpanel_generic.html';
		$TP['GenericTitle'] = '_dbcheck.cpcategory';

		$action = $_REQUEST['action'];
		if (!$action) $action = 'check';
		$pre = $UNB['Db']->tblprefix;
		$errors = false;
		@set_time_limit(300);

		UnbAddLog('check database: ' . $action);

		ob_start();
		if ($action == 'check')
		{
			echo '<div class="p">Running entire database check...</div>';
			echo '<div>';

			// ACL.Forum IN [0, Forums.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ACL', 'a', ''),
					array('LEFT', 'Forums', 'f', 'a.Forum = f.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'a.Forum <> 0 AND f.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'ACL ' . $rec['ID'] . ' has an invalid forum reference to ' . $rec['Forum'] . '<br />';
				$errors = true;
			}

			// ACL.Thread IN [0, Threads.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ACL', 'a', ''),
					array('LEFT', 'Threads', 't', 'a.Thread = t.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'a.Thread <> 0 AND t.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'ACL ' . $rec['ID'] . ' has an invalid topic reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// ACL.Group IN [0, GroupNames.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ACL', 'a', ''),
					array('LEFT', 'GroupNames', 'gn', 'a.Group = gn.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'a.Group <> 0 AND gn.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'ACL ' . $rec['ID'] . ' has an invalid user group reference to ' . $rec['Group'] . '<br />';
				$errors = true;
			}

			// ACL.User IN [0, Users.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ACL', 'a', ''),
					array('LEFT', 'Users', 'u', 'a.User = u.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'a.User <> 0 AND u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'ACL ' . $rec['ID'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// ForumWatch.Forum IN Forums.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ForumWatch', 'fw', ''),
					array('LEFT', 'Forums', 'f', 'fw.Forum = f.ID')),
				/*fields*/ 'fw.*',
				/*where*/ 'f.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Forum notification for user ' . $rec['User'] . ' and forum ' . $rec['Forum'] . ' has an invalid forum reference to ' . $rec['Forum'] . '<br />';
				$errors = true;
			}

			// ForumWatch.User in Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ForumWatch', 'fw', ''),
					array('LEFT', 'Users', 'u', 'fw.User = u.ID')),
				/*fields*/ 'fw.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Forum notification for user ' . $rec['User'] . ' and forum ' . $rec['Forum'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// Forums.Parent IN [0, Forums.ID] -- no cycles allowed
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Forums', 'f1', ''),
					array('LEFT', 'Forums', 'f2', 'f1.Parent = f2.ID')),
				/*fields*/ 'f1.*',
				/*where*/ 'f2.Parent <> 0 AND f2.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['ID'], true) . '">Forum ' . $rec['ID'] . '</a> has an invalid parent reference to ' . $rec['Parent'] . '<br />';
				$errors = true;
			}
			// TODO: check for cycles here

			// Forums.Name NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Forums',
				/*fields*/ '*',
				/*where*/ 'TRIM(Name) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['ID'], true) . '">Forum ' . $rec['ID'] . '</a> has an empty name<br />';
				$errors = true;
			}

			// IF Forums.Flags & UNB_FORUM_WEBLINK THEN Forums.Link NOT EMPTY
			// IF NOT Forums.Flags & UNB_FORUM_WEBLINK THEN Forums.Link EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Forums',
				/*fields*/ '*',
				/*where*/ 'Flags & ' . UNB_FORUM_WEBLINK . ' AND TRIM(Link) = \'\' OR ' .
					'NOT (Flags & ' . UNB_FORUM_WEBLINK . ') AND TRIM(Link) <> \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['ID'], true) . '">Forum ' . $rec['ID'] . '</a> has an invalid web link setting (link set and no target specified or vice-versa)<br />';
				$errors = true;
			}

			// GroupMembers.Group IN GroupNames.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'GroupMembers', 'gm', ''),
					array('LEFT', 'GroupNames', 'gn', 'gm.Group = gn.ID')),
				/*fields*/ 'gm.*',
				/*where*/ 'gn.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Group membership for user ' . $rec['User'] . ' and group ' . $rec['Group'] . ' has an invalid group reference to ' . $rec['Group'] . '<br />';
				$errors = true;
			}

			// GroupMembers.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'GroupMembers', 'gm', ''),
					array('LEFT', 'Users', 'u', 'gm.User = u.ID')),
				/*fields*/ 'gm.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Group membership for user ' . $rec['User'] . ' and group ' . $rec['Group'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// GroupNames.Name NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'GroupNames',
				/*fields*/ '*',
				/*where*/ 'TRIM(Name) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Group ' . $rec['ID'] . ' has an empty name<br />';
				$errors = true;
			}

			// AnnounceRead.Announce IN Announces.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'AnnounceRead', 'ar', ''),
					array('LEFT', 'Announces', 'a', 'ar.Announce = a.ID')),
				/*fields*/ 'ar.*',
				/*where*/ 'a.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'AnnounceRead for user ' . $rec['User'] . ' and announcement ' . $rec['Announce'] . ' has an invalid announcement reference to ' . $rec['Announce'] . '<br />';
				$errors = true;
			}

			// AnnounceRead.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'AnnounceRead', 'ar', ''),
					array('LEFT', 'Users', 'u', 'ar.User = u.ID')),
				/*fields*/ 'ar.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'AnnounceRead for user ' . $rec['User'] . ' and announcement ' . $rec['Msg'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// Announces.Forum IN [-1, 0, Forums.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Announces', 'a', ''),
					array('LEFT', 'Forums', 'f', 'a.Forum = f.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'a.Forum <> -1 AND a.Forum <> 0 AND f.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Announcement ' . $rec['ID'] . ' has an invalid forum reference to ' . $rec['Forum'] . '<br />';
				$errors = true;
			}

			// Announces.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Announces', 'a', ''),
					array('LEFT', 'Users', 'u', 'a.User = u.ID')),
				/*fields*/ 'a.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['Forum'], true) . '">Announcement ' . $rec['ID'] . '</a> has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// Announces.Msg NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Announces',
				/*fields*/ '*',
				/*where*/ 'TRIM(Msg) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['Forum'], true) . '">Announcement ' . $rec['ID'] . '</a> has an empty content<br />';
				$errors = true;
			}

			// PollUsers.Thread IN Threads.ID WHERE Threads.Options & UNB_THREAD_POLL
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'PollUsers', 'pu', ''),
					array('LEFT', 'Threads', 't', 'pu.Thread = t.ID')),
				/*fields*/ 'pu.*',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_POLL . ') OR t.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'User vote for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid thread reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// PollUsers.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'PollUsers', 'pu', ''),
					array('LEFT', 'Users', 'u', 'pu.User = u.ID')),
				/*fields*/ 'pu.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'User vote for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// PollUsers.VoteNum IN [0, PollVotes.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'PollUsers', 'pu', ''),
					array('LEFT', 'PollVotes', 'pv', 'pu.VoteNum = pv.ID')),
				/*fields*/ 'pu.*',
				/*where*/ 'pu.VoteNum <> 0 AND pv.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'User vote for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid vote reference to ' . $rec['VoteNum'] . '<br />';
				$errors = true;
			}

			// PollVotes.Thread IN Threads.ID WHERE Threads.Options & UNB_THREAD_POLL
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'PollVotes', 'pv', ''),
					array('LEFT', 'Threads', 't', 'pv.Thread = t.ID')),
				/*fields*/ 'pv.*',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_POLL . ') OR t.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Vote ' . $rec['ID'] . ' has an invalid thread reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// PollVotes.Title NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'PollVotes',
				/*fields*/ '*',
				/*where*/ 'TRIM(Title) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['Thread'], true) . '">Vote ' . $rec['ID'] . '</a> has an empty title<br />';
				$errors = true;
			}

			// Posts.Thread IN Threads.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Posts', 'p', ''),
					array('LEFT', 'Threads', 't', 'p.Thread = t.ID')),
				/*fields*/ 'p.*',
				/*where*/ 't.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Post ' . $rec['ID'] . ' has an invalid thread reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// Posts.ReplyTo IN [0, Posts.ID WHERE Thread = this:Posts.Thread] AND NOT this:Posts.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Posts', 'p1', ''),
					array('LEFT', 'Posts', 'p2', 'p1.ReplyTo = p2.ID AND p1.Thread = p2.Thread AND p1.ID <> p2.ID')),
				/*fields*/ 'p1.*',
				/*where*/ 'p1.ReplyTo <> 0 AND p2.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an invalid reply-to reference to ' . $rec['ReplyTo'] . ' [<a href="' . UnbLink('@this', 'cat=dbcheck&action=repair_replyto&key=' . UnbUrlGetKey(), true) . '">repair</a>]<br />';
				$errors = true;
			}

			// Posts.EditUser IN [-1, 0, Users.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Posts', 'p', ''),
					array('LEFT', 'Users', 'u', 'p.EditUser = u.ID')),
				/*fields*/ 'p.*',
				/*where*/ 'p.EditUser <> -1 AND p.EditUser <> 0 AND u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an invalid edit user reference to ' . $rec['EditUser'] . '<br />';
				$errors = true;
			}

			// Posts.EditDate > this:Posts.Date
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Posts',
				/*fields*/ '*',
				/*where*/ 'EditDate <> 0 AND EditDate < Date');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an invalid edit date (less than post date)<br />';
				$errors = true;
			}

			// IF Posts.EditCount = 0 THEN Posts.EditDate = 0 AND Posts.EditUser = 0
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Posts',
				/*fields*/ '*',
				/*where*/ 'EditCount = 0 AND (EditDate <> 0 OR EditUser <> 0)');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has invalid edit date or user (but was never edited)<br />';
				$errors = true;
			}

			// IF Posts.AttachFile = '' THEN Posts.AttachFileName = '' AND Posts.AttachDLCount = 0
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Posts',
				/*fields*/ '*',
				/*where*/ 'AttachFile = \'\' AND (AttachFileName <> \'\' OR AttachDLCount <> 0)');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has inconsistent attachment information (no attachment is assigned) [<a href="' . UnbLink('@this', 'cat=dbcheck&action=repair_attachfile&key=' . UnbUrlGetKey(), true) . '">repair</a>]<br />';
				$errors = true;
			}

			// Posts.User IN [-1, 0, Users.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Posts', 'p', ''),
					array('LEFT', 'Users', 'u', 'p.User = u.ID')),
				/*fields*/ 'p.*',
				/*where*/ 'p.User <> -1 AND p.User <> 0 AND u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// IF Posts.User <= 0 THEN Posts.UserName NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Posts',
				/*fields*/ '*',
				/*where*/ 'User <= 0 AND TRIM(UserName) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an empty username and no user ID<br />';
				$errors = true;
			}

			// Posts.Msg NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Posts',
				/*fields*/ '*',
				/*where*/ 'TRIM(Msg) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo UnbMakePostLink(intval($rec['ID']), 0, true) . 'Post ' . $rec['ID'] . '</a> has an empty content<br />';
				$errors = true;
			}

			// ThreadWatch.Thread IN Threads.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ThreadWatch', 'tw', ''),
					array('LEFT', 'Threads', 't', 'tw.Thread = t.ID')),
				/*fields*/ 'tw.*',
				/*where*/ 't.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Thread notification for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid thread reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// ThreadWatch.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'ThreadWatch', 'tw', ''),
					array('LEFT', 'Users', 'u', 'tw.User = u.ID')),
				/*fields*/ 'tw.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'Thread notification for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// Threads.Forum IN Forums.ID WHERE NOT (Forums.Flags & UNB_FOURM_CATEGORY OR Forums.Flags & UNB_FORUM_WEBLINK)
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Forums', 'f', 't.Forum = f.ID AND NOT (f.Flags & ' . UNB_FORUM_CATEGORY . ') AND NOT (f.Flags & ' . UNB_FORUM_WEBLINK . ')')),
				/*fields*/ 't.*',
				/*where*/ 'f.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an invalid forum reference to ' . $rec['Forum'] . '<br />';
				$errors = true;
			}
			// TODO: allow root threads here

			// Threads.Subject NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Threads',
				/*fields*/ '*',
				/*where*/ 'TRIM(Subject) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an empty subject<br />';
				$errors = true;
			}

			// Threads.User IN [-1, 0, Users.ID]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Users', 'u', 't.User = u.ID')),
				/*fields*/ 't.*',
				/*where*/ 't.User <> -1 AND t.User <> 0 AND u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// IF Threads.User <= 0 THEN Threads.UserName NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Threads',
				/*fields*/ '*',
				/*where*/ 'User <= 0 AND TRIM(UserName) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an empty username and no user ID<br />';
				$errors = true;
			}

			// IF Threads.Options & UNB_THREAD_POLL THEN Threads.Question NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Threads',
				/*fields*/ '*',
				/*where*/ '(Options & ' . UNB_THREAD_POLL . ') AND TRIM(Question) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> is a poll and has an empty question<br />';
				$errors = true;
			}

			// Moved threads must point to a valid thread other than themselves
			// IF Threads.Options & UNB_THREAD_MOVED THEN Threads.Question IN Threads.ID AND NOT this:Threads.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't1', ''),
					array('LEFT', 'Threads', 't2', 't1.Question = t2.ID')),
				/*fields*/ 't1.*',
				/*where*/ '(t1.Options & ' . UNB_THREAD_MOVED . ') AND (t1.Question = t1.ID OR t2.ID IS NULL)');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@main', 'id=' . $rec['Forum'], true) . '">Thread ' . $rec['ID'] . '</a> is a moved note and has an invalid target thread reference to ' . $rec['Question'] . '<br />';
				$errors = true;
			}

			// UserForumFlags.Forum IN (0, Forums.ID)
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'UserForumFlags', 'uff', ''),
					array('LEFT', 'Forums', 'f', 'uff.Forum = f.ID')),
				/*fields*/ 'uff.*',
				/*where*/ 'uff.Forum <> 0 AND f.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'UserForumFlags for user ' . $rec['User'] . ' and forum ' . $rec['Forum'] . ' has an invalid forum reference to ' . $rec['Forum'] . '<br />';
				$errors = true;
			}

			// UserForumFlags.Thread IN (0, Threads.ID)
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'UserForumFlags', 'uff', ''),
					array('LEFT', 'Threads', 't', 'uff.Thread = t.ID')),
				/*fields*/ 'uff.*',
				/*where*/ 'uff.Thread <> 0 AND t.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'UserForumFlags for user ' . $rec['User'] . ' and thread ' . $rec['Thread'] . ' has an invalid thread reference to ' . $rec['Thread'] . '<br />';
				$errors = true;
			}

			// UserForumFlags.User IN Users.ID
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'UserForumFlags', 'uff', ''),
					array('LEFT', 'Users', 'u', 'uff.User = u.ID')),
				/*fields*/ 'uff.*',
				/*where*/ 'u.ID IS NULL');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo 'UserForumFlags for user ' . $rec['User'] . ' and forum ' . $rec['Forum'] . ' has an invalid user reference to ' . $rec['User'] . '<br />';
				$errors = true;
			}

			// Users.Name NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Users',
				/*fields*/ '*',
				/*where*/ 'TRIM(Name) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@cp', 'id=' . $rec['ID'], true) . '">User ' . $rec['ID'] . '</a> has an empty name<br />';
				$errors = true;
			}

			// Users.Password NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Users',
				/*fields*/ '*',
				/*where*/ 'TRIM(Password) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@cp', 'id=' . $rec['ID'], true) . '">User ' . $rec['ID'] . '</a> has an empty password<br />';
				$errors = true;
			}

			// Users.ValidatedEMail NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Users',
				/*fields*/ '*',
				/*where*/ 'TRIM(ValidatedEMail) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@cp', 'id=' . $rec['ID'], true) . '">User ' . $rec['ID'] . '</a> has an empty registration e-mail address<br />';
				$errors = true;
			}

			// Users.EMail NOT EMPTY
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ 'Users',
				/*fields*/ '*',
				/*where*/ 'TRIM(EMail) = \'\'');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@cp', 'id=' . $rec['ID'], true) . '">User ' . $rec['ID'] . '</a> has an empty e-mail address<br />';
				$errors = true;
			}

			// All Threads [that are not moved] must have at least one post
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Posts', 'p', 't.ID = p.Thread')),
				/*fields*/ 't.*, COUNT(p.ID) AS PostsCount',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ 'PostsCount = 0');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has no posts<br />';
				$errors = true;
			}

			// All polls must have at least one vote [reply option]
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'PollVotes', 'pv', 't.ID = pv.Thread')),
				/*fields*/ 't.*, COUNT(pv.ID) AS VotesCount',
				/*where*/ 't.Options & ' . UNB_THREAD_POLL . ' AND NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ 'VotesCount = 0');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> is a poll and has no PollVotes (reply options)<br />';
				$errors = true;
			}

			// All Threads.LastPostDate must be the Posts.Date of the last post in this thread
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Posts', 'p', 't.ID = p.Thread')),
				/*fields*/ 't.*, MAX(p.Date) AS MaxDate',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ 'LastPostDate <> MaxDate');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an invalid LastPostDate value [<a href="' . UnbLink('@this', 'cat=dbcheck&action=repair_lastpostdate&key=' . UnbUrlGetKey(), true) . '">repair</a>]<br />';
				$errors = true;
			}

			// All threads of which posts have an attachment must have flag UNB_THREAD_ATTACHMENT, others must not
			$records = $UNB['Db']->FastQueryArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Posts', 'p', 't.ID = p.Thread')),
				/*fields*/ 't.*, IF(MAX(LENGTH(p.AttachFile)), ' . UNB_THREAD_ATTACHMENT . ', 0) AS MaxLenAttach',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ '(Options & ' . UNB_THREAD_ATTACHMENT . ') <> MaxLenAttach');
			echo t2h($UNB['Db']->LastError());
			if ($records) foreach ($records as $rec)
			{
				echo '<a href="' . UnbLink('@thread', 'id=' . $rec['ID'], true) . '">Thread ' . $rec['ID'] . '</a> has an invalid attachment flag value [<a href="' . UnbLink('@this', 'cat=dbcheck&action=repair_threadattach&key=' . UnbUrlGetKey(), true) . '">repair</a>]<br />';
				$errors = true;
			}

			echo '</div>';
			echo '<div class="p">Check finished.';
			if (!$errors) echo ' <b>No errors were found.</b>';
			echo '</div>';
		}  // if action == 'check'

		if ($action == 'repair_lastpostdate' &&
			UnbUrlCheckKey())
		{
			echo '<div class="p">Repairing threads\' LastPostDate...</div>';

			$thread = new IThread;

			// Set correct LastPostDate for all threads
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Posts', 'p', 't.ID = p.Thread')),
				/*fields*/ 't.ID, MAX(p.Date) AS MaxDate, t.LastPostDate',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ 'LastPostDate <> MaxDate');
			echo '<div>' . t2h($UNB['Db']->LastError()) . '</div>';

			foreach ($ids as $id)
			{
				echo '<div>- ID ' . $id . '</div>';
				$record = UnbGetLastPost(intval($id));
				if (!$thread->SetLastPostDate($record['Date'], $id))
				{
					echo '<div>';
					echo t2h($thread->db->LastError());
					echo '</div>';
				}
			}

			echo '<div class="p">Repair finished. [<a href="' . UnbLink('@this', 'cat=dbcheck&action=check&key=' . UnbUrlGetKey(), true) . '">check again</a>]</div>';
		}

		if ($action == 'repair_threadattach' &&
			UnbUrlCheckKey())
		{
			echo '<div class="p">Repairing threads\' attachment flag...</div>';

			$thread = new IThread;
			$post = new IPost;

			// Add thread-has-attachment flags
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Threads', 't', ''),
					array('LEFT', 'Posts', 'p', 't.ID = p.Thread')),
				/*fields*/ 't.*, IF(MAX(LENGTH(p.AttachFile)), ' . UNB_THREAD_ATTACHMENT . ', 0) AS MaxLenAttach',
				/*where*/ 'NOT (t.Options & ' . UNB_THREAD_MOVED . ')',
				/*order*/ '',
				/*limit*/ '',
				/*group*/ 't.ID',
				/*key*/ false,
				/*having*/ '(Options & ' . UNB_THREAD_ATTACHMENT . ') <> MaxLenAttach');
			echo '<div>' . t2h($UNB['Db']->LastError()) . '</div>';

			foreach ($ids as $id)
			{
				echo '<div>- ID ' . $id . '</div>';
				$found = $post->Find('Thread=' . $id . " AND AttachFile<>''", '', 1);

				$opt = $thread->GetOptions($id);
				$opt = ($opt & ~UNB_THREAD_ATTACHMENT) | (($found ? 1 : 0) * UNB_THREAD_ATTACHMENT);
				if (!$thread->SetOptions($opt, $id))
				{
					echo '<div>';
					echo t2h($thread->db->LastError());
					echo '</div>';
				}
			}

			echo '<div class="p">Repair finished. [<a href="' . UnbLink('@this', 'cat=dbcheck&action=check&key=' . UnbUrlGetKey(), true) . '">check again</a>]</div>';
		}

		if ($action == 'repair_attachfile' &&
			UnbUrlCheckKey())
		{
			echo '<div class="p">Repairing posts\' attachment information...</div>';

			// Remove additional attachment information where no attachment is assigned
			if (!$UNB['Db']->ChangeRecord(
				array('AttachFileName' => '', 'AttachDLCount' => 0),
				'AttachFile = \'\' AND (AttachFileName <> \'\' OR AttachDLCount <> 0)',
				'Posts'))
			{
				echo '<div>' . t2h($UNB['Db']->LastError()) . '</div>';
			}
			echo '<div>- modified ' . t2h($UNB['Db']->AffectedRows()) . ' rows</div>';

			echo '<div class="p">Repair finished. [<a href="' . UnbLink('@this', 'cat=dbcheck&action=check&key=' . UnbUrlGetKey(), true) . '">check again</a>]</div>';
		}

		if ($action == 'repair_replyto' &&
			UnbUrlCheckKey())
		{
			echo '<div class="p">Repairing posts\' reply-to reference...</div>';

			// Set invalid ReplyTo references to 0
			$ids = $UNB['Db']->FastQuery1stArray(
				/*table*/ array(
					array('', 'Posts', 'p1', ''),
					array('LEFT', 'Posts', 'p2', 'p1.ReplyTo = p2.ID AND p1.Thread = p2.Thread AND p1.ID <> p2.ID')),
				/*fields*/ 'p1.ID',
				/*where*/ 'p1.ReplyTo <> 0 AND p2.ID IS NULL');
			echo '<div>' . t2h($UNB['Db']->LastError()) . '</div>';

			foreach ($ids as $id)
			{
				echo '<div>- ID ' . $id . '</div>';
				if (!$UNB['Db']->ChangeRecord(
					'ReplyTo = 0',
					'ID = ' . $id,
					'Posts'))
				{
					echo '<div>';
					echo t2h($UNB['Db']->LastError());
					echo '</div>';
				}
			}

			echo '<div class="p">Repair finished. [<a href="' . UnbLink('@this', 'cat=dbcheck&action=check&key=' . UnbUrlGetKey(), true) . '">check again</a>]</div>';
		}

		$out = ob_get_contents();
		ob_end_clean();

		$TP['GenericContent'] = $out;
	}

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookDbCheckAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookDbCheckCPCategoryPage');

?>