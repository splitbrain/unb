<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Manage access control rules');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050914', 'version');
#UnbPluginMeta('UnbHookACLEditorConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookACLEditorConfig(&$data)
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
function UnbHookACLEditorAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_acleditor.cpcategory',
			'link' => UnbLink('@cp', 'cat=acleditor', true),
			'cpcat' => 'acleditor');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookACLEditorCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'acleditor' &&
	    UnbCheckRights('is_admin'))
	{
		$edit_acl = intval($_REQUEST['edit']);
		if ($edit_acl <= 0) $edit_acl = 0;
		$add_acl = $_GET['add'] == 1;

		// Remember if there was an error while processing a form. If there was one,
		// show the previously input form data again instead of reading them from the
		// database.
		$procerror = false;

		if ($_POST['action'] == 'edit' ||
		    $_GET['action'] == 'edit' && UnbUrlCheckKey())
		{
			// A form has been submitted. Process the input data now.
			$ruleid = intval($_REQUEST['ruleid']);
			$error = false;

			if ($_REQUEST['remove'] != '')
			{
				if (!$UNB['Db']->RemoveRecord("ID=$ruleid", 'ACL'))
				{
					$error .= $UNB_T['_acleditor.error.could not remove'] . '<br />';
				}
			}
			else
			{
				if ($_POST['UserID'] && $_POST['GroupID'])
				{
					$error .= $UNB_T['_acleditor.error.either user or group'] . '<br />';
				}

				$user = new IUser;
				$userid = $user->FindByName($_POST['UserID']);
				if ($userid == 0 &&
				    (!is_numeric($_POST['UserID']) && trim($_POST['UserID']) != '' ||
				     is_numeric($_POST['UserID']) && $_POST['UserID'] != 0))
				{
					$error .= $UNB_T['error.invalid user'] . '<br />';
				}

				if ($_POST['ThreadID'] && $_POST['ForumID'])
				{
					$error .= $UNB_T['_acleditor.error.either thread or forum'] . '<br />';
				}

				if (!$_POST['ActionID'])
				{
					$error .= $UNB_T['_acleditor.error.no action selected'] . '<br />';
				}

				$isForumSpecific = ($_POST['ActionID'] >= 30);
				$a = array();
				$a['action'] = $_POST['ActionID'];
				$a['specific'] = $isForumSpecific;
				UnbCallHook('acl.customaction.specific', $a);
				$isForumSpecific = $a['specific'];

				if (!$error)
				{
					$isNumeric = (20 <= $_POST['ActionID'] && $_POST['ActionID'] <= 29 ||
								  60 <= $_POST['ActionID'] && $_POST['ActionID'] <= 79);
					$a = array();
					$a['action'] = $_POST['ActionID'];
					$a['numeric'] = $isNumeric;
					UnbCallHook('acl.customaction.numeric', $a);
					$isNumeric = $a['numeric'];

					$grant_value = $isNumeric ? intval($_POST['GrantNum']) : intval($_POST['GrantCheck']);

					$fields = array(
						'User' => $userid,
						'Group' => intval($_POST['GroupID']),
						'Thread' => $isForumSpecific ? intval($_POST['ThreadID']) : 0,
						'Forum' => $isForumSpecific ? intval($_POST['ForumID']) : 0,
						'Action' => intval($_POST['ActionID']),
						'Grant' => $grant_value,
						'Enabled' => ($_POST['Enabled'] ? 1 : 0)
						);

					if ($ruleid > 0)
					{
						if (!$UNB['Db']->ChangeRecord($fields, "ID=$ruleid", 'ACL'))
						{
							$error .= $UNB_T['_acleditor.error.could not save'] . '<br />';
						}
					}
					else
					{
						$max = intval($UNB['Db']->FastQuery1st('ACL', 'MAX(ID)'));
						$fields['ID'] = $max + 1;
						if (!$UNB['Db']->AddRecord($fields, 'ACL'))
						{
							$error .= $UNB_T['_acleditor.error.could not add'] . '<br />';
						}
					}
				}
			}

			if ($error)
			{
				$TP['errorMsg'] .= $error;
				$procerror = true;
				$edit_acl = $ruleid;
				if (!$edit_acl) $add_acl = true;
			}
			else
			{
				$_SESSION['UnbSavedSuccess'] = true;
				UnbForwardHTML(UnbLink('@this', 'cat=acleditor'));
			}
		}

		if ($_SESSION['UnbSavedSuccess'])
		{
			$_SESSION['UnbSavedSuccess'] = false;
			$TP['infoMsg'] .= $UNB_T['cp.settings saved'] . '<br />';
		}

		$TP['controlpanelMoreCats'][] = 'controlpanel_acleditor.html';

		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$group_names = UnbGetGroupNames();

		// Read access rules from the database
		$acls = $UNB['Db']->FastQueryArray('ACL', '*', '', 'User DESC, `Group` DESC, Thread DESC' . $UNB['ForumOrder'] . ', Action');
		$user = new IUser;
		$thread = new IThread;
		$forum = new IForum;
		$prev_section = '';
		$curr_section = '';
		$prev_subsection = '';
		$curr_subsection = '';
		$more_detailed = array();

		$section_count = 0;

		$TP['acllist'] = array();
		if ($acls) foreach ($acls as $counter => $acl)
		{
			$tpitem = array();

			// group sections (user/group) by background colour
			$curr_section = $acl['User'] . ' ' . $acl['Group'];
			#if ($curr_section != $prev_section) $section_count++;

			// group sub-sections (+forum/thread) by horizontal lines
			$curr_subsection = $acl['User'] . ' ' . $acl['Group'] . ' ' . $acl['Thread'] . ' ' . $acl['Forum'];
			if ($curr_subsection != $prev_subsection)
			{
				$tpitem['styleclass'] .= ' line';
				if ($counter == 0)
					$tpitem['styleclass'] .= ' first';

				/*$rowspan = 1;
				while ($counter + $rowspan - 1 < sizeof($acls) &&
				       $curr_subsection == $acls[$counter + $rowspan - 1]['User'] . ' ' .
				                           $acls[$counter + $rowspan - 1]['Group'] . ' ' .
				                           $acls[$counter + $rowspan - 1]['Thread'] . ' ' .
				                           $acls[$counter + $rowspan - 1]['Forum'])
					$rowspan++;
				$tpitem['rowspan'] = $rowspan - 1;*/
			}

			// display disabled lines in a lighter color
			if (!$acl['Enabled']) $tpitem['styleclass'] .= ' disabled';

			if ($curr_section != $prev_section)
			{
				if ($acl['User'] != 0)
				{
					if ($user->Load($acl['User']))
						$tpitem['userdata'] = '<small>' . $UNB_T['_acleditor.user'] . '</small> ' . UnbMakeUserLink($acl['User'],
							t2h($user->GetName()),
							false, false, /*t2h*/ false);
					else
						$tpitem['userdata'] = '<small>' . $UNB_T['_acleditor.user'] . '</small> ' . $acl['User'] . ' <i>(' . $UNB_T['_acleditor.unknown user'] . ')</i>';
				}
				elseif ($acl['Group'] != 0)
				{
					if (isset($group_names[$acl['Group']]))
						$tpitem['userdata'] = '<small>' . $UNB_T['_acleditor.group'] . '</small> ' . t2h($group_names[$acl['Group']]);
					else
						$tpitem['userdata'] = '<small>' . $UNB_T['_acleditor.group'] . '</small> ' . $acl['Group'] . ' <i>(' . $UNB_T['_acleditor.unknown group'] . ')</i>';
				}
				else
				{
					$tpitem['userdata'] = '<small>' . $UNB_T['_acleditor.all users'] . '</small>';
				}
			}

			// only relevant for non-global rights
			$isForumSpecific = ($acl['Action'] >= 30);
			$a = array();
			$a['action'] = $acl['Action'];
			$a['specific'] = $isForumSpecific;
			UnbCallHook('acl.customaction.specific', $a);
			$isForumSpecific = $a['specific'];
			if ($curr_subsection != $prev_subsection)
			{
				if ($isForumSpecific)
				{
					if ($acl['Thread'] != 0)
					{
						if ($thread->Load($acl['Thread']))
							$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.topic'] . '</small> ' . t2h($thread->GetSubject());
						else
							$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.topic'] . '</small> ' . $acl['Thread'] . ' <i>(' . $UNB_T['_acleditor.unknown thread'] . ')</i>';
					}
					elseif ($acl['Forum'] != 0)
					{
						if ($forum->Load($acl['Forum']))
							$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.forum'] . '</small> ' . t2h($forum->GetName());
						else
							$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.forum'] . '</small> ' . $acl['Forum'] . ' <i>(' . $UNB_T['_acleditor.unknown forum'] . ')</i>';
					}
					else
					{
						$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.all forums'] . '</small>';
					}
				}
				else
				{
					$tpitem['forumdata'] = '<small>' . $UNB_T['_acleditor.all forums'] . '</small>';
				}
			}

			$isNumeric = (20 <= $acl['Action'] && $acl['Action'] <= 29 ||
						  60 <= $acl['Action'] && $acl['Action'] <= 79);
			$a = array();
			$a['action'] = $acl['Action'];
			$a['numeric'] = $isNumeric;
			UnbCallHook('acl.customaction.numeric', $a);
			$isNumeric = $a['numeric'];

			if (isset($UNB_T['_acleditor.action.' . $acl['Action']]))
				$tpitem['actiondata'] = $UNB_T['_acleditor.action.' . $acl['Action']] . ($isForumSpecific ? ' &#185;' : '') . ($isNumeric ? ' &#178;' : '');
			else
				$tpitem['actiondata'] = $acl['Action'] . ' <i>(' . $UNB_T['_acleditor.unknown action'] . ')</i>';

			if ($isNumeric)
				$tpitem['grantdata'] = $acl['Grant'] . ' ' . $UNB_T['_acleditor.action.' . $acl['Action'] . '.unit'];
			else
			{
				$tpitem['grantdata'] = $acl['Grant'] ? '<span class="yes">' . $UNB_T['_acleditor.allow'] . '</span>' : '<span class="no">' . $UNB_T['_acleditor.deny'] . '</span>';

				$tpitem['styleclass'] .= $acl['Grant'] ? ' allowed' : ' denied';
			}

			if ($acl['ID'] == $edit_acl)
			{
				$tpitem['actions'] = '<img ' . $UNB['Image']['editthread'] . ' />';
			}
			else
			{
				if ($acl['User'] == 0 && $acl['Group'] == UNB_GROUP_MEMBERS && $acl['Thread'] == 0 && $acl['Forum'] == 0 && $acl['Action'] == 82 ||
					$acl['User'] == 0 && $acl['Group'] == UNB_GROUP_MODS && $acl['Thread'] == 0 && $acl['Forum'] == 0 && $acl['Action'] == 83 ||
					$acl['User'] == 0 && $acl['Group'] == UNB_GROUP_ADMINS && $acl['Thread'] == 0 && $acl['Forum'] == 0 && $acl['Action'] == 84)
				{
				}
				else
				{
					$tpitem['actions'] = '<a href="' . UnbLink('@this', 'cat=acleditor&edit=' . $acl['ID'] . '#here', true) . '">' .
						'<img ' . $UNB['Image']['edit'] . ' title="' . $UNB_T['_acleditor.edit'] . '" />' .
						'</a> &nbsp;' .
						'<a href="javascript:UnbGoDelete(\'' .
							UnbLink(
								'@this',
								array(
									'cat' => 'acleditor',
									'ruleid' => $acl['ID'],
									'action' => 'edit',
									'remove' => true,
									'key' => UnbUrlGetKey()),
								true) .
							'\')">' .
						'<img ' . $UNB['Image']['delete'] . ' title="' . $UNB_T['_acleditor.remove'] . '" />' .
						'</a>';
				}
			}

			if ($acl['ID'] == $edit_acl)
			{
				$tpitem['styleclass'] .= ' editing';
				$TP['acleditorEditor'] = true;
				$TP['acleditorRuleID'] = $acl['ID'];
				$TP['acleditorUserID'] = $procerror ? $_POST['UserID'] : $acl['User'];
				$TP['acleditorGroupID'] = $procerror ? $_POST['GroupID'] : $acl['Group'];
				$TP['acleditorThreadID'] = $procerror ? $_POST['ThreadID'] : $acl['Thread'];
				$TP['acleditorForumID'] = $procerror ? $_POST['ForumID'] : $acl['Forum'];
				$TP['acleditorActionID'] = $procerror ? $_POST['ActionID'] : $acl['Action'];
				$TP['acleditorGrantNum'] = $procerror ? $_POST['GrantNum'] : $acl['Grant'];
				$TP['acleditorGrantCheck'] = $procerror ? $_POST['GrantCheck'] : $acl['Grant'];
				$TP['acleditorEnabled'] = $procerror ? $_POST['Enabled'] : $acl['Enabled'];
				$TP['acleditorMayRemove'] = true;
			}

			if (!$edit_acl && !$add_acl)
			{
				$TP['acleditorAddLink'] = UnbLink(
					'@this',
					array(
						'cat' => 'acleditor',
						'add' => true,
						'#' => 'here'),
					true);
			}

			$tpitem['styleclass'] = ltrim($tpitem['styleclass']);

			$prev_section = $curr_section;
			$prev_subsection = $curr_subsection;
			$TP['acllist'][] = $tpitem;
		}

		if ($add_acl == 1)
		{
			$TP['acleditorEditor'] = true;
			$TP['acleditorRuleID'] = 0;
			$TP['acleditorUserID'] = $procerror ? $_POST['UserID'] : 0;
			$TP['acleditorGroupID'] = $procerror ? $_POST['GroupID'] : 0;
			$TP['acleditorThreadID'] = $procerror ? $_POST['ThreadID'] : 0;
			$TP['acleditorForumID'] = $procerror ? $_POST['ForumID'] : 0;
			$TP['acleditorActionID'] = $procerror ? $_POST['ActionID'] : 0;
			$TP['acleditorGrantNum'] = $procerror ? $_POST['GrantNum'] : '';
			$TP['acleditorGrantCheck'] = $procerror ? $_POST['GrantCheck'] : 1;
			$TP['acleditorEnabled'] = $procerror ? $_POST['Enabled'] : 1;
		}

		if ($TP['acleditorEditor'])
		{
			$TP['acleditorGroups'] = array();
			foreach ($group_names as $id => $title)
				$TP['acleditorGroups'][] = array('id' => $id, 'title' => $title);

			global $output;
			$output = '';
			UnbListForumsRec($TP['acleditorForumID'], false, 0, 0, true, /*noWebLinks*/ true);
			$TP['acleditorForums'] = $output;

			$TP['acleditorNumericIDs'] = array();
			$TP['acleditorSpecificIDs'] = array();
			$TP['acleditorActions'] = array();
			for ($id = 1; $id < 200; $id++)
			{
				if (isset($UNB_T['_acleditor.action.' . $id]))
				{
					$isForumSpecific = ($id >= 30);
					$a = array();
					$a['action'] = $id;
					$a['specific'] = $isForumSpecific;
					UnbCallHook('acl.customaction.specific', $a);
					$isForumSpecific = $a['specific'];

					$isNumeric = (20 <= $id && $id <= 29 ||
								  60 <= $id && $id <= 79);
					$a = array();
					$a['action'] = $id;
					$a['numeric'] = $isNumeric;
					UnbCallHook('acl.customaction.numeric', $a);
					$isNumeric = $a['numeric'];

					$title = $UNB_T['_acleditor.action.' . $id] .
						($isForumSpecific ? ' &#185;' : '') .
						($isNumeric ? ' &#178; [' . $UNB_T['_acleditor.action.' . $id . '.unit'] . ']' : '');

					$styleclass = '';
					$thisattr = ($isForumSpecific ? 1 : 0) + ($isNumeric ? 2 : 0);
					if ($thisattr != $prevattr && sizeof($TP['acleditorActions']) || $previd < 100 && $id >= 100)
						$styleclass = 'new_group';
					$prevattr = $thisattr;
					$previd = $id;

					$TP['acleditorActions'][] = array('id' => $id, 'title' => $title, 'styleclass' => $styleclass);

					if ($isNumeric) $TP['acleditorNumericIDs'][] = $id;
					if ($isForumSpecific) $TP['acleditorSpecificIDs'][] = $id;
				}
			}
			$TP['acleditorNumericIDs'] = join(',', $TP['acleditorNumericIDs']);
			$TP['acleditorSpecificIDs'] = join(',', $TP['acleditorSpecificIDs']);

			$TP['controlpanelSureDelete'] = UnbSureDelete(1);
		}
	}

	return true;
}

// Hook function to add my CSS file
//
function UnbHookACLEditorPageAddcss(&$data)
{
	global $UNB;

	if ($UNB['ThisPage'] == '@cp' && $_REQUEST['cat'] == 'acleditor')
		$data[] = 'acleditor';

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookACLEditorAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookACLEditorCPCategoryPage');
UnbRegisterHook('page.addcss', 'UnbHookACLEditorPageAddcss');

?>