<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Manage user groups');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050924', 'version');
#UnbPluginMeta('UnbHookGroupsEditorConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookGroupsEditorConfig(&$data)
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
function UnbHookGroupsEditorAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_groupseditor.cpcategory',
			'link' => UnbLink('@cp', 'cat=groupseditor', true),
			'cpcat' => 'groupseditor');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookGroupsEditorCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'groupseditor' &&
	    UnbCheckRights('is_admin'))
	{
		$edit_group = intval($_REQUEST['edit']);
		if ($edit_group <= 0) $edit_group = 0;
		$add_group = $_GET['add'] == 1;

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

			if ($_REQUEST['remove'] != '')
			{
				if ($groupid > 3)
				{
					if (!$UNB['Db']->RemoveRecord("`Group`=$groupid", 'GroupMembers') ||
						!$UNB['Db']->RemoveRecord("ID=$groupid", 'GroupNames'))
					{
						$error .= $UNB_T['_groupseditor.error.could not remove'] . '<br />';
					}
				}
				else
				{
					$error .= $UNB_T['_groupseditor.error.default groups protected'] . '<br />';
				}
			}
			else
			{
				if (trim($_POST['Name']) == '')
				{
					$error .= $UNB_T['_groupseditor.error.empty name'] . '<br />';
				}

				if (!$error)
				{
					switch ($groupid)
					{
						case UNB_GROUP_GUESTS:
						case UNB_GROUP_MEMBERS: $showinteam = 0; $publicgroup = 0; break;
						case UNB_GROUP_MODS:
						case UNB_GROUP_ADMINS: $showinteam = 1; $publicgroup = 0; break;
						default:
							$showinteam = $_POST['ShowInTeam'] == 1 ? 1 : 0;
							$publicgroup = $_POST['PublicGroup'] == 1 ? 1 : 0;
					}

					$fields = array(
						'Name' => trim($_POST['Name']),
						'ShowInTeam' => $showinteam,
						'PublicGroup' => $publicgroup);

					if ($groupid > 0)
					{
						if (!$UNB['Db']->ChangeRecord($fields, "ID=$groupid", 'GroupNames'))
						{
							$error .= $UNB_T['_groupseditor.error.could not save'] . '<br />';
						}
						elseif ($groupid > UNB_GROUP_MAX)   // cannot bulk edit membership of default groups
						// TODO: allow this or not?
						// NOTE: if yes, pay attention about kicking out yourself out of the admin group.
						//       see <cp/change groups> code for that
						{
							if ($_POST['SetGroupMembers'])
							{
								$members = $_POST['GroupMembers'];
								if (!isset($_POST['GroupMembers'])) $members = array();
								if (UnbSetGroupMembers($groupid, $members))
									UnbAddLog('change_members for group ' . $groupid . ' to ' . join(', ', $members));
							}
						}
					}
					else
					{
						$max = intval($UNB['Db']->FastQuery1st('GroupNames', 'max(ID)'));
						$fields['ID'] = $max + 1;
						if (!$UNB['Db']->AddRecord($fields, 'GroupNames'))
						{
							$error .= $UNB_T['_groupseditor.error.could not add'] . '<br />';
						}
						elseif ($_POST['SetGroupMembers'])
						{
							$members = $_POST['GroupMembers'];
							if (!isset($_POST['GroupMembers'])) $members = array();
							if (UnbSetGroupMembers($fields['ID'], $members))
								UnbAddLog('change_members for new group ' . $fields['ID'] . ' to ' . join(', ', $members));
						}
					}
				}
			}

			if ($error)
			{
				$TP['errorMsg'] .= $error;
				$procerror = true;
				$edit_group = $groupid;
				if (!$edit_group) $add_group = true;
			}
			else
			{
				$_SESSION['UnbSavedSuccess'] = true;
				UnbForwardHTML(UnbLink('@this', 'cat=groupseditor'));
			}
		}

		if ($_SESSION['UnbSavedSuccess'])
		{
			$_SESSION['UnbSavedSuccess'] = false;
			$TP['infoMsg'] .= $UNB_T['cp.settings saved'] . '<br />';
		}

		$TP['controlpanelMoreCats'][] = 'controlpanel_groupseditor.html';

		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		// Read user groups from the database
		$groups = $UNB['Db']->FastQueryArray('GroupNames', '*', '', 'IF(ID<=' . UNB_GROUP_MAX . ', ID, CONCAT("' . (UNB_GROUP_MAX + 1) . '", LCASE(Name)))');

		$TP['groupslist'] = array();
		$num = 1;
		if ($groups) foreach ($groups as $counter => $group)
		{
			$tpitem = array();
			$tpitem['num'] = $num++;
			$tpitem['ID'] = $group['ID'];
			$tpitem['Name'] = t2h($group['Name']);
			$tpitem['MembersCount'] = sizeof(UnbGetGroupMembers($group['ID']));
			$tpitem['ShowInTeam'] = $group['ShowInTeam'];
			$tpitem['PublicGroup'] = $group['PublicGroup'];

			if ($group['ID'] == $edit_group)
			{
				$tpitem['actions'] = '<img ' . $UNB['Image']['editthread'] . ' />';
			}
			else
			{
				$tpitem['actions'] = '<a href="' . UnbLink('@this', 'cat=groupseditor&edit=' . $group['ID'] . '#here', true) . '">' .
					'<img ' . $UNB['Image']['edit'] . ' title="' . $UNB_T['_groupseditor.edit'] . '" />' .
					'</a>';
				if ($group['ID'] > UNB_GROUP_MAX)
					$tpitem['actions'] .= ' &nbsp;' .
					'<a href="javascript:UnbGoDelete(\'' .
						UnbLink(
							'@this',
							array(
								'cat' => 'groupseditor',
								'groupid' => $group['ID'],
								'action' => 'edit',
								'remove' => true,
								'key' => UnbUrlGetKey()),
							true) .
						'\')">' .
					'<img ' . $UNB['Image']['delete'] . ' title="' . $UNB_T['_groupseditor.remove'] . '" />' .
					'</a>';
				else
					$tpitem['actions'] .= ' &nbsp;' .
					'<img ' . $UNB['Image']['delete'] . ' class="hidden" />';
			}

			if ($group['ID'] == $edit_group)
			{
				$tpitem['styleclass'] .= ' editing';
				$TP['groupseditorEditor'] = true;
				$TP['groupseditorGroupID'] = $group['ID'];
				$TP['groupseditorName'] = $procerror ? $_POST['Name'] : $group['Name'];
				$TP['groupseditorShowInTeam'] = $procerror ? $_POST['ShowInTeam'] : $group['ShowInTeam'];
				$TP['groupseditorPublicGroup'] = $procerror ? $_POST['PublicGroup'] : $group['PublicGroup'];
				if ($group['ID'] > UNB_GROUP_MAX)
				{
					$TP['groupseditorMayShowInTeam'] = true;
					$TP['groupseditorMayPublicGroup'] = true;
					$TP['groupseditorMayRemove'] = true;
				}
			}

			if (!$edit_group && !$add_group)
			{
				$TP['groupseditorAddLink'] = UnbLink(
					'@this',
					array(
						'cat' => 'groupseditor',
						'add' => true,
						'#' => 'here'),
					true);
			}

			$TP['groupslist'][] = $tpitem;
		}

		if ($add_group == 1)
		{
			$TP['groupseditorEditor'] = true;
			$TP['groupseditorGroupID'] = 0;
			$TP['groupseditorName'] = $procerror ? $_POST['Name'] : '';
			$TP['groupseditorShowInTeam'] = $procerror ? $_POST['ShowInTeam'] : 0;
			$TP['groupseditorMayShowInTeam'] = true;
			$TP['groupseditorMayPublicGroup'] = true;
		}

		if ($TP['groupseditorEditor'])
		{
			$users = UnbGetUserNames();
			if ($edit_group > UNB_GROUP_MAX)   // See restriction above
			{
				$TP['groupseditorMaySetUsers'] = true;
				$groupmembers = UnbGetGroupMembers($edit_group);
				$TP['groupseditorUsers'] = array();
				foreach ($users as $id => $name)
					if (in_array($id, $groupmembers))
						$TP['groupseditorUsers'][] = array(
							'id' => $id,
							'selected' => true,
							'name' => t2h($name));
				foreach ($users as $id => $name)
					if (!in_array($id, $groupmembers))
						$TP['groupseditorUsers'][] = array(
							'id' => $id,
							'selected' => false,
							'name' => t2h($name));
			}

			$TP['controlpanelSureDelete'] = UnbSureDelete(1);
		}
	}

	return true;
}

// Hook function to add my CSS file
//
function UnbHookGroupsEditorPageAddcss(&$data)
{
	global $UNB;

	if ($UNB['ThisPage'] == '@cp' && $_REQUEST['cat'] == 'groupseditor')
		$data[] = 'groupseditor';

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookGroupsEditorAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookGroupsEditorCPCategoryPage');
UnbRegisterHook('page.addcss', 'UnbHookGroupsEditorPageAddcss');

?>