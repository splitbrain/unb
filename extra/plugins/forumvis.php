<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Manage forum visibilities based on user group membership');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.devel.20060801', 'version');
#UnbPluginMeta('UnbHookForumVisConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookForumVisConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		// No web configuration available
	}

	if ($data['request'] == 'handleform')
	{
		// No web configuration available
		$data['result'] = true;
	}

	return true;
}

// Hook function to add a new control panel category
//
function UnbHookForumVisAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_forumvis.cpcategory',
			'link' => UnbLink('@cp', 'cat=forumvis', true),
			'cpcat' => 'forumvis');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookForumVisCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'forumvis' &&
	    UnbCheckRights('is_admin'))
	{
		$edit_group = intval($_REQUEST['edit']);
		if ($edit_group <= 0) $edit_group = 0;

		// Remember if there was an error while processing a form. If there was one,
		// show the previously input form data again instead of reading them from the
		// database.
		$procerror = false;

		if ($_POST['action'] == 'edit' ||
		    $_GET['action'] == 'edit' && UnbUrlCheckKey())
		{
			// A form has been submitted. Process the input data now.
			$groupid = intval($_REQUEST['groupid']);
			$error = false;

			if ($groupid > UNB_GROUP_MAX && is_array($_REQUEST['VisibleForum']))
			{
				global $forums;
				UnbGetForumsRec();

				// Save the current selection to retrieve it later again
				$vis_forums = array();
				$all_visible = true;
				foreach ($forums as $forumid)
				{
					$vis = in_array($forumid, $_REQUEST['VisibleForum']);
					if ($vis)
						$vis_forums[] = $forumid;
					else
						$all_visible = false;
				}
				if ($all_visible)
					unset($UNB['ConfigFile']['ForumVis-Group' . $groupid]);
				else
					$UNB['ConfigFile']['ForumVis-Group' . $groupid] = join('|', $vis_forums);
				if (!UnbRebuildConffile()) $error .= $UNB_T['error.write conffile'] . '<br />';

				$user = new IUser;
				$members = UnbGetGroupMembers($groupid);
				if ($members) foreach ($members as $userid)
				{
					$vis_forums = array();

					// Find all other groups of this user to set correct forum visibilities regarding all group memberships
					// This will include the group we're currently editing with its new forum visibilities just saved
					$usergroups = UnbGetUserGroups($userid);
					if ($usergroups) foreach ($usergroups as $usergroupid)
					{
						$vis_forums = array_merge($vis_forums, rc('ForumVis-Group' . $usergroupid, true));
					}

					// Now go update each forum for the user with the selection we've found
					foreach ($forums as $forumid)
					{
						if (count($vis_forums) > 0)
							$vis = in_array($forumid, $vis_forums);
						else
							$vis = true;
						$user->SetForumFlag($forumid, UNB_UFF_HIDE, !$vis, $userid);
					}
				}
			}

			if ($error)
			{
				$TP['errorMsg'] .= $error;
				$procerror = true;
				$edit_group = $groupid;
			}
			else
			{
				$_SESSION['UnbSavedSuccess'] = true;
				UnbForwardHTML(UnbLink('@this', 'cat=forumvis'));
			}
		}

		if ($_SESSION['UnbSavedSuccess'])
		{
			$_SESSION['UnbSavedSuccess'] = false;
			$TP['infoMsg'] .= $UNB_T['cp.settings saved'] . '<br />';
		}

		$TP['controlpanelMoreCats'][] = 'controlpanel_forumvis.html';

		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		// Read user groups from the database
		$groups = $UNB['Db']->FastQueryArray('GroupNames', '*', 'ID > ' . UNB_GROUP_MAX, 'Name');

		$TP['groupslist'] = array();
		$num = 1;
		if ($groups) foreach ($groups as $counter => $group)
		{
			$tpitem = array();
			$tpitem['num'] = $num++;
			$tpitem['ID'] = $group['ID'];
			$tpitem['Name'] = t2h($group['Name']);

			if ($group['ID'] == $edit_group)
			{
				$tpitem['actions'] = '<img ' . $UNB['Image']['editthread'] . ' />';
			}
			else
			{
				if (rc('ForumVis-Group' . $group['ID']) !== null)
					$img = 'edit';
				else
					$img = 'add';

				$tpitem['actions'] = '<a href="' . UnbLink('@this', 'cat=forumvis&edit=' . $group['ID'] . '#here', true) . '">' .
					'<img ' . $UNB['Image'][$img] . ' title="' . $UNB_T['_forumvis.' . $img] . '" />' .
					'</a>';
			}

			if ($group['ID'] == $edit_group)
			{
				$tpitem['styleclass'] .= ' editing';
				$TP['forumvisEditor'] = true;
				$TP['forumvisGroupID'] = $group['ID'];
				$TP['forumvisName'] = $procerror ? $_POST['Name'] : $group['Name'];
			}

			$TP['groupslist'][] = $tpitem;
		}
		else
		{
			$TP['forumvisEditorMsg'] = $UNB_T['_forumvis.no groups'];
		}

		if ($TP['forumvisEditor'])
		{
			global $forums;
			UnbGetForumsRec();
			$levels = UnbGetAllForumLevels();
			$vis_forums = rc('ForumVis-Group' . $edit_group, true);

			$TP['forumvisForums'] = array();
			foreach ($forums as $forumid)
			{
				$forum = new IForum($forumid);
				$level = $levels[$forumid];
				$lStr = str_repeat('    ', $level);

				if ($procerror)
					$sel = in_array($forumid, $_REQUEST['VisibleForum']);
				elseif (count($vis_forums) > 0)
					$sel = in_array($forumid, $vis_forums);
				else
					$sel = true;

				$TP['forumvisForums'][] = array(
					'id' => $forumid,
					'name' => t2h($lStr . $forum->GetName()),
					'selected' => $sel);
			}
		}
	}

	return true;
}

// Hook function to add my CSS file
//
function UnbHookForumVisPageAddcss(&$data)
{
	global $UNB;

	if ($UNB['ThisPage'] == '@cp' && $_REQUEST['cat'] == 'forumvis')
		$data[] = 'forumvis';

	return true;
}

function UnbHookForumVisCpUserSetgroup(&$data)
{
	// NOTE: This function should be called multiple times if multiple group memberships are altered for the
	//       same user at a time. This shouldn't be too tragic for normal use. Just to mention it somewhere.

	global $UNB;

	$userid = $data['userid'];
	$groupid = $data['groupid'];

	global $forums;
	UnbGetForumsRec();
	$user = new IUser;
	$vis_forums = array();

	// Find all other groups of this user to set correct forum visibilities regarding all group memberships
	// This will include the group we're currently editing with its new forum visibilities just saved
	$usergroups = UnbGetUserGroups($userid);
	if ($usergroups) foreach ($usergroups as $usergroupid)
	{
		$vis_forums = array_merge($vis_forums, rc('ForumVis-Group' . $usergroupid, true));
	}

	// Now go update each forum for the user with the selection we've found
	foreach ($forums as $forumid)
	{
		if (count($vis_forums) > 0)
			$vis = in_array($forumid, $vis_forums);
		else
			$vis = true;
		$user->SetForumFlag($forumid, UNB_UFF_HIDE, !$vis, $userid);
	}

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookForumVisAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookForumVisCPCategoryPage');
UnbRegisterHook('page.addcss', 'UnbHookForumVisPageAddcss');
UnbRegisterHook('cp.user.setgroup', 'UnbHookForumVisCpUserSetgroup');

?>