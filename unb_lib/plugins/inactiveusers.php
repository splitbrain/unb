<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Find inactive users and remove them');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.3', 'version');
UnbPluginMeta('unb.devel.20051101', 'version');

if (!UnbPluginEnabled()) return;

// Hook function to add a new control panel category
//
function UnbHookInactiveUsersAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_inactiveusers.cpcategory',
			'link' => UnbLink('@cp', 'cat=inactiveusers', true),
			'cpcat' => 'inactiveusers');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookInactiveUsersCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'inactiveusers' &&
	    UnbCheckRights('is_admin'))
	{
		$TP['controlpanelMoreCats'][] = 'controlpanel_generic.html';
		$TP['GenericTitle'] = '_inactiveusers.cpcategory';

		ob_start();

		$user = new IUser;

		if ($_POST['action'] == 'RemoveUsers' && $_POST['SureRemove'] == 1)
		{
			UnbAddLog('remove inactive users');
			// Remove all selected users
			echo '<div class="p">' . $UNB_T['_inactiveusers.removing selected users'] . '<br /><br />';
			foreach ($_REQUEST['uid'] as $uid)
			{
				$user->Load($uid);
				$name = $user->GetName();
				if (!$user->Remove())
					echo UteTranslate('_inactiveusers.error.user id,name not removed', 'id', $uid, 'name', $name) . '<br />';
				else
					echo UteTranslate('_inactiveusers.removed user id,name', 'id', $uid, 'name', $name) . '<br />';
			}
			echo '</div>';
		}

		// Create my own database object so that some function calls won't take away my result
		// and thus 'steal' all users after the 1st one!
		if (PHP5) eval('$mydb = clone $UNB["Db"];');
		else      $mydb = $UNB['Db'];

		// Find users without posts
		$posts = $mydb->FastQuery1stArray(
			/*table*/ array(
				array('', 'Users', 'u', ''),
				array('LEFT', 'Posts', 'p', 'u.ID = p.User')),
			/*fields*/ 'u.ID AS uid, COUNT(p.User) AS cnt',
			/*where*/ '',
			/*order*/ '',
			/*limit*/ '',
			/*group*/ 'uid',
			/*key*/ false,
			/*having*/ 'cnt = 0');

		if (sizeof($posts))
			$noposts = 'OR (IF(LastActivity, LastActivity, RegDate) < ' . (time() - 180 * 24 * 3600) . ' AND ID IN (' . join(',', $posts) . ')) ';
		else
			$noposts = '';

		// Find users by
		// ... no group AND RegDate older than 60d (2mon)
		// ... no post AND LastActivity older than 180d (6mon)
		// ... others: LastActivity older than 720d (2yr)
		$record = $mydb->FastQuery(
			/*table*/ array(
				array('', 'Users', 'u', ''),
				array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User')),
			/*fields*/ 'u.ID, gm.Group',
			/*where*/ '(`Group` IS NULL AND RegDate < ' . (time() - 60 * 24 * 3600) . ') ' .
				$noposts .
				'OR (`Group` = 1 AND LastActivity < ' . (time() - 720 * 24 * 3600) . ')',
			/*order*/ 'IF(u.LastActivity, u.LastActivity, u.RegDate) ASC',
			/*limit*/ '',
			/*group*/ 'u.ID',
			/*key*/ false,
			/*having*/ '');

		if ($record)
		{
			echo '<form action="' . UnbLink('@this', 'cat=inactiveusers', true) . '" method="post">';
			echo '<input type="hidden" name="action" value="RemoveUsers" />';

			echo '<table cellspacing="0" cellpadding="0" width="100%">';
			echo '<tr valign="top">';
			echo '<td width="15%">' . $UNB_T['username'] . '</td>';
			echo '<td width="20%">' . $UNB_T['registered on'] . '</td>';
			echo '<td width="20%">' . $UNB_T['users.last activity'] . '</td>';
			echo '<td width="15%" align="center">' . $UNB_T['_inactiveusers.validated member'] . '?</td>';
			echo '<td width="15%" align="center">' . $UNB_T['posts'] . '</td>';
			echo '<td width="15%" align="center">' . $UNB_T['cp.remove user'] . '?</td>';
			echo '</tr>';

			UnbCountUserPosts();
			$view = 0;

			$group_names = UnbGetGroupNames();

			do
			{
				$user->Load($record['ID']);

				echo '<tr valign="top" class="hover">';

				echo '<td>';
				echo UnbMakeUserLink($user->GetID(),
					t2h($user->GetName()),
					true, false, false);
				echo '</td>';

				echo '<td>';
				echo UnbFriendlyDate($user->GetRegDate(), 2, 1, true, 4);
				echo '</td>';

				echo '<td>';
				if (!$user->GetLastActivity())
					echo $UNB_T['never'];
				else
					echo UnbFriendlyDate($user->GetLastActivity(), 2, 1, true, 4);
				echo '</td>';

				echo '<td align="center">';
				if (!$record['Group'])
					echo '<b>' . $UNB_T['_inactiveusers.no'] . '</b>';
				else
					echo $UNB_T['_inactiveusers.yes'];
				echo '</td>';

				echo '<td align="center">';
				echo UnbGetPostsByUser($user->GetID());
				echo '</td>';

				echo '<td align="center">';
				echo '<input type="checkbox" name="uid[]" value="' . $user->GetID() . '" />';
				echo '</td>';

				echo '</tr>';

				$count++;
			}
			while ($record = $mydb->GetRecord());
			$view = $count;

			echo '</table>';

			echo '<div class="p">(' . $view . ' ' . UteTranslateNum('users', $view) . ')</div>';

			echo '<div class="p">';
			echo '<label><input type="checkbox" name="SureRemove" value="1" ' . UnbSureDelete() . ' />' . $UNB_T['_inactiveusers.sure remove users'] . '</label> &nbsp; &nbsp; ';
			echo '<input type="submit" class="defaultbutton" name="Delete" value="' . $UNB_T['delete'] . '" />';
			echo '</div>';

			echo '</form>';
		}
		else
		{
			echo '<div class="p">' . $UNB_T['_inactiveusers.no inactive users'] . '</div>';
		}

		$out = ob_get_contents();
		ob_end_clean();

		$TP['GenericContent'] = $out;
	}

	return true;
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookInactiveUsersAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookInactiveUsersCPCategoryPage');

?>