<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('View the board\'s activity log');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.3', 'version');
UnbPluginMeta('unb.devel.20051101', 'version');

if (!UnbPluginEnabled()) return;

// Hook function to add a new control panel category
//
function UnbHookLogViewerAddCPCategory(&$data)
{
	if (UnbCheckRights('is_admin'))
	{
		$data[] = array(
			'title' => '_logviewer.cpcategory',
			'link' => UnbLink('@cp', 'cat=logviewer', true),
			'cpcat' => 'logviewer');
	}

	return true;
}

// Hook function to show the control panel category
//
function UnbHookLogViewerCPCategoryPage(&$data)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if ($data['cat'] == 'logviewer' &&
	    UnbCheckRights('is_admin'))
	{
		$TP['controlpanelMoreCats'][] = 'controlpanel_generic.html';
		$TP['GenericTitle'] = '_logviewer.cpcategory';

		$date = $_REQUEST['logdate'];
		if (!preg_match('_^\d{1,4}-\d{1,2}-\d{1,2}$_', $date)) $date = date("Y-m-d");

		UnbAddLog('view log of ' . $date);

		$showdate = $_REQUEST['showdate'] == 1;
		$showtime = $_REQUEST['showtime'] == 1;
		$showuserid = $_REQUEST['showuserid'] == 1;
		$showusername = $_REQUEST['showusername'] == 1;
		$showip = $_REQUEST['showip'] == 1;
		$showaction = $_REQUEST['showaction'] == 1;
		$showbrowser = $_REQUEST['showbrowser'] == 1;
		$showbver = $_REQUEST['showbver'] == 1;
		$showos = $_REQUEST['showos'] == 1;
		$showlang = $_REQUEST['showlang'] == 1;
		$showsess = $_REQUEST['showsess'] == 1;

		if (!$_REQUEST['update'] &&
			!$showdate &&
			!$showtime &&
			!$showuserid &&
			!$showusername &&
			!$showip &&
			!$showaction &&
			!$showbrowser &&
			!$showbver &&
			!$showos &&
			!$showlang &&
			!$showsess)
		{
			$showtime = true;
			$showusername = true;
			$showaction = true;
		}
		if (!$_REQUEST['update'] &&
		    !isset($_REQUEST['time']))
		{
			$_REQUEST['time'] = '>' . date('H:i', time() - 2 * 3600);
		}

		ob_start();
		echo '<form action="' . UnbLink('@this', null, true) . '" method="get">';
		echo UnbFormSessionId();
		echo '<input type="hidden" name="req" value="cp" />';
		echo '<input type="hidden" name="cat" value="logviewer" />';
		echo '<input type="hidden" name="update" value="1" />';

		echo '<div class="p">';
		echo $UNB_T['_logviewer.log date'] . ': <input type="text" name="logdate" size="12" style="width: 7em;" value="' . t2i($date) . '" /> ';
		echo '<input type="submit" class="defaultbutton" value="' . $UNB_T['_logviewer.update'] . '" /> ';

		$checked = $showdate ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showdate" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.date'] . '</label> ';
		$checked = $showtime ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showtime" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.time'] . '</label> ';
		$checked = $showuserid ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showuserid" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.user id'] . '</label> ';
		$checked = $showusername ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showusername" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.username'] . '</label> ';
		$checked = $showip ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showip" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.ip'] . '</label> ';
		$checked = $showaction ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showaction" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.action'] . '</label> ';
		$checked = $showbrowser ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showbrowser" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.browser'] . '</label> ';
		$checked = $showbver ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showbver" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.version'] . '</label> ';
		$checked = $showos ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showos" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.os'] . '</label> ';
		$checked = $showlang ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showlang" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.lang'] . '</label> ';
		$checked = $showsess ? 'checked="checked"' : '';
		echo '<label style="white-space: nowrap;"><input type="checkbox" name="showsess" value="1" ' . $checked . ' />' . $UNB_T['_logviewer.session'] . '</label> ';

		echo '</div>';

		echo '<table cellspacing="0" cellpadding="0" border="0" width="100%">';

		echo '<tr valign="top">';
		if ($showdate) echo '<td>' . $UNB_T['_logviewer.date'] . '</td>';
		if ($showtime) echo '<td>' . $UNB_T['_logviewer.time'] . '</td>';
		if ($showuserid) echo '<td>' . $UNB_T['_logviewer.user id'] . '</td>';
		if ($showusername) echo '<td>' . $UNB_T['_logviewer.username'] . '</td>';
		if ($showip) echo '<td>' . $UNB_T['_logviewer.ip'] . '</td>';
		if ($showaction) echo '<td>' . $UNB_T['_logviewer.action'] . '</td>';
		if ($showbrowser) echo '<td>' . $UNB_T['_logviewer.browser'] . '</td>';
		if ($showbver) echo '<td>' . $UNB_T['_logviewer.version'] . '</td>';
		if ($showos) echo '<td>' . $UNB_T['_logviewer.os'] . '</td>';
		if ($showlang) echo '<td>' . $UNB_T['_logviewer.lang'] . '</td>';
		if ($showsess) echo '<td>' . $UNB_T['_logviewer.session'] . '</td>';
		echo '</tr>';

		echo '<tr valign="top">';
		if ($showdate) echo '<td><input type="text" name="date" size="10" style="width: 6em;" value="' . t2i($_REQUEST['date']) . '" /></td>';
		if ($showtime) echo '<td><input type="text" name="time" size="8" style="width: 5em;" value="' . t2i($_REQUEST['time']) . '" /></td>';
		if ($showuserid) echo '<td><input type="text" name="userid" size="4" style="width: 3em;" value="' . t2i($_REQUEST['userid']) . '" /></td>';
		if ($showusername) echo '<td><input type="text" name="username" size="10" style="width: 5em;" value="' . t2i($_REQUEST['username']) . '" /></td>';
		if ($showip) echo '<td><input type="text" name="ip" size="12" style="width: 7em;" value="' . t2i($_REQUEST['ip']) . '" /></td>';
		if ($showaction) echo '<td><input type="text" name="action" size="30" style="width: 14em;" value="' . t2i($_REQUEST['action']) . '" /></td>';
		if ($showbrowser) echo '<td><input type="text" name="browser" size="5" style="width: 3em;" value="' . t2i($_REQUEST['browser']) . '" /></td>';
		if ($showbver) echo '<td><input type="text" name="bver" size="3" style="width: 2em;" value="' . t2i($_REQUEST['bver']) . '" /></td>';
		if ($showos) echo '<td><input type="text" name="os" size="3" style="width: 3em;" value="' . t2i($_REQUEST['os']) . '" /></td>';
		if ($showlang) echo '<td><input type="text" name="lang" size="7" style="width: 4em;" value="' . t2i($_REQUEST['lang']) . '" /></td>';
		if ($showsess) echo '<td><input type="text" name="sess" size="20" style="width: 15em;" value="' . t2i($_REQUEST['sess']) . '" /></td>';
		echo '</tr>';

		$filters = array();
		if ($showdate) if ($_REQUEST['date'] != '') array_push($filters, UnbLogViewerMakeFilter('date', $_REQUEST['date']));
		if ($showtime) if ($_REQUEST['time'] != '') array_push($filters, UnbLogViewerMakeFilter('time', $_REQUEST['time']));
		if ($showuserid) if ($_REQUEST['userid'] != '') array_push($filters, UnbLogViewerMakeFilter('userid', $_REQUEST['userid']));
		if ($showusername) if ($_REQUEST['username'] != '') array_push($filters, UnbLogViewerMakeFilter('username', $_REQUEST['username']));
		if ($showip) if ($_REQUEST['ip'] != '') array_push($filters, UnbLogViewerMakeFilter('ip', $_REQUEST['ip']));
		if ($showaction) if ($_REQUEST['action'] != '') array_push($filters, UnbLogViewerMakeFilter('action', $_REQUEST['action']));
		if ($showbrowser) if ($_REQUEST['browser'] != '') array_push($filters, UnbLogViewerMakeFilter('browser', $_REQUEST['browser']));
		if ($showbver) if ($_REQUEST['bver'] != '') array_push($filters, UnbLogViewerMakeFilter('bver', $_REQUEST['bver']));
		if ($showos) if ($_REQUEST['os'] != '') array_push($filters, UnbLogViewerMakeFilter('os', $_REQUEST['os']));
		if ($showlang) if ($_REQUEST['lang'] != '') array_push($filters, UnbLogViewerMakeFilter('lang', $_REQUEST['lang']));
		if ($showsess) if ($_REQUEST['sess'] != '') array_push($filters, UnbLogViewerMakeFilter('sess', $_REQUEST['sess']));

		$userids = array();
		$usernames = array();
		$ips = array();
		$browsers = array();
		$bvers = array();
		$oss = array();
		$langs = array();
		$sesss = array();

		$php430 = phpversion() >= '4.3.0';
		$fp = @fopen($UNB['LogPath'] . "board-$date.log", 'r');
		if ($fp) while (!feof($fp))
		{
			if ($php430)
				$fields = fgetcsv($fp, 1024, ' ', '"');
			else
			{
				$fields = explode_quoted(' ', trim(fgets($fp, 1024)));
			}
			list($entry['date'],
				$entry['time'],
				$entry['userid'],
				$entry['username'],
				$entry['ip'],
				$entry['action'],
				$entry['browser'],
				$entry['bver'],
				$entry['os'],
				$entry['lang'],
				$entry['sess']) = $fields;
			if (!$entry['date']) continue;

			$ok = true;
			foreach ($filters as $filter)
			{
				if ($filter['op'] === 4)
				{
					if (!($entry[$filter['field']] < $filter['value'])) $ok = false;
				}
				elseif ($filter['op'] === 5)
				{
					if (!($entry[$filter['field']] > $filter['value'])) $ok = false;
				}
				elseif ($filter['op'] === 1)
				{
					if (!($entry[$filter['field']] <= $filter['value'])) $ok = false;
				}
				elseif ($filter['op'] === 2)
				{
					if (!($entry[$filter['field']] >= $filter['value'])) $ok = false;
				}
				elseif ($filter['op'] === 6)
				{
					if (!($entry[$filter['field']] === $filter['value'] ||
						!strcasecmp($entry[$filter['field']], $filter['value']))) $ok = false;
				}
				elseif ($filter['op'] === 3)
				{
					if (!($entry[$filter['field']] != $filter['value'])) $ok = false;
				}
				elseif ($filter['op'] === 7)
				{
					if (!($entry[$filter['field']] === $filter['value'] ||
						  stristr($entry[$filter['field']], $filter['value']))) $ok = false;
				}
				elseif ($filter['op'] === 8)
				{
					if (strncasecmp($entry[$filter['field']], $filter['value'], strlen($filter['value']))) $ok = false;
				}
			}
			if (!$ok && $filters) continue;

			if (!in_array($entry['userid'], $userids)) array_push($userids, $entry['userid']);
			$userid_style = UnbLogViewerGetStyle(array_search($entry['userid'], $userids, true));
			if (!in_array($entry['username'], $usernames)) array_push($usernames, $entry['username']);
			$username_style = UnbLogViewerGetStyle(array_search($entry['username'], $usernames, true));
			if (!in_array($entry['ip'], $ips)) array_push($ips, $entry['ip']);
			$ip_style = UnbLogViewerGetStyle(array_search($entry['ip'], $ips, true));
			if (!in_array($entry['browser'], $browsers)) array_push($browsers, $entry['browser']);
			$browser_style = UnbLogViewerGetStyle(array_search($entry['browser'], $browsers, true));
			if (!in_array($entry['bver'], $bvers)) array_push($bvers, $entry['bver']);
			$bver_style = UnbLogViewerGetStyle(array_search($entry['bver'], $bvers, true));
			if (!in_array($entry['os'], $oss)) array_push($oss, $entry['os']);
			$os_style = UnbLogViewerGetStyle(array_search($entry['os'], $oss, true));
			if (!in_array($entry['lang'], $langs)) array_push($langs, $entry['lang']);
			$lang_style = UnbLogViewerGetStyle(array_search($entry['lang'], $langs, true));
			if (!in_array($entry['sess'], $sesss)) array_push($sesss, $entry['sess']);
			$sess_style = UnbLogViewerGetStyle(array_search($entry['sess'], $sesss, true));

			echo '<tr valign="top" class="hover">';
			if ($showdate) echo '<td>' . $entry['date'] . '</td>';
			if ($showtime) echo '<td>' . $entry['time'] . '</td>';
			if ($showuserid) echo '<td style="' . $userid_style . '">' . $entry['userid'] . '</td>';
			if ($showusername)
			{
				$username = $entry['username'];
				if ($entry['userid'] != 0)
					echo '<td><a style="' . $username_style . '" href="' . UnbLink('@cp', 'id=' . $entry['userid'], true) . '">' . t2h($username) . '</a></td>';
				else
					echo '<td style="' . $username_style . '">' . t2h($username) . '</td>';
			}
			if ($showip) echo '<td style="' . $ip_style . '">' . $entry['ip'] . '</td>';
			if ($showaction)
			{
				// TODO,FIXME: Links do not work, $ is replaced by %24 in urlencode. Would need to spend a lot
				//             more code in this but I'm not willing to do it before the log is moved to the
				//             database which brings a lot better structured format with it.
				$action = t2h(stripslashes($entry['action']));
				#$action = $entry['action'];
	/*			$action = preg_replace("/^view_forum (\d+)/",
					'view_forum <a href="' . UnbLink('@main', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^view_thread (\d+)/",
					'view_thread <a href="' . UnbLink('@thread', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^add_post (\d+) to (\d+) ok/e",
					'\'<i>add_post</i> \' . UnbMakePostLink(intval(\'$1\'),0,true) . \'$1</a> to <a href="\' . UnbLink($UNB[\'Module\'][\'thread\'], \'id=$2\', true) . \'">$2</a> ok\'', $action);
				$action = preg_replace("/^preview_post in (\d+)/",
					'view_thread <a href="' . UnbLink('@thread', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^add_announce to (\d+) ok/",
					'<i>add_announce</i> to <a href="' . UnbLink('@main', 'id=$1', true) . '">$1</a> ok', $action);
				$action = preg_replace("/^view_profile (\d+)/",
					'view_profile <a href="' . UnbLink('@cp', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^edit_profile (\d+)/",
					'<i>edit_profile</i> <a href="' . UnbLink('@cp', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^email_user (\d+)/",
					'email_user <a href="' . UnbLink('@cp', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^add_user (\d+)/",
					'<i>add_user</i> <a href="' . UnbLink('@cp', 'id=$1', true) . '">$1</a>', $action);
				$action = preg_replace("/^goto_url (.+)/e",
					'\'goto_url <a href="\' . UnbLink(stripslashes(\'$1\'), null, true, /*sid*-/ false, /*derefer*-/ true) . \'">\' . t2h(stripslashes(\'$1\')) . \'</a>\'', $action);
				$action = preg_replace("/^add_forum (\d+) ok/",
					'<i>add_forum</i> <a href="' . UnbLink('@main', 'id=$1', true) . '">$1</a> ok', $action);
				$action = preg_replace("/^(remove_user|remove_post|remove_forum|announce_read|announce_unread|all_threads_read|all_forums_read)/",
					'<i>$1</i>', $action);*/

				echo '<td>' . $action . '</td>';
			}
			if ($showbrowser) echo '<td style="' . $browser_style . '">' . $entry['browser'] . '</td>';
			if ($showbver) echo '<td style="' . $bver_style . '">' . $entry['bver'] . '</td>';
			if ($showos) echo '<td style="' . $os_style . '">' . $entry['os'] . '</td>';
			if ($showlang) echo '<td style="' . $lang_style . '">' . $entry['lang'] . '</td>';
			if ($showsess) echo '<td style="' . $sess_style . '">' . $entry['sess'] . '</td>';
			echo '</tr>';
		}

		echo '</table>';
		echo '</form>';

		$out = ob_get_contents();
		ob_end_clean();

		$TP['GenericContent'] = $out;
	}

	return true;
}

function UnbLogViewerGetStyle($num)
{
	switch ($num)
	{
		case  0: return 'color:#E00000;';
		case  1: return 'color:#00A000;';
		case  2: return 'color:#0000E0;';
		case  3: return 'color:#B0A000;';
		case  4: return 'color:#00A0F0;';
		case  5: return 'color:#C000C0;';
		case  6: return 'color:#FF5757;';
		case  7: return 'color:#00D600;';
		case  8: return 'color:#494DFF;';
		case  9: return 'color:#F300F6;';
		case 10: return 'color:#A40000;';
		case 11: return 'color:#007800;';
		case 12: return 'color:#00049C;';
		case 13: return 'color:#726900;';
		case 14: return 'color:#0075B2;';
		case 15: return 'color:#800082;';
		case 16: return 'color:#808080;';
		default: return 'color:#000000;';
	}
}

function UnbLogViewerMakeFilter($field, $str)
{
	if (substr($str, 0, 2) === '<=') return array('field' => $field, 'op' => 1, 'value' => substr($str, 2));
	if (substr($str, 0, 2) === '>=') return array('field' => $field, 'op' => 2, 'value' => substr($str, 2));
	if (substr($str, 0, 2) === '!=') return array('field' => $field, 'op' => 3, 'value' => substr($str, 2));
	if (substr($str, 0, 1) === '<') return array('field' => $field, 'op' => 4, 'value' => substr($str, 1));
	if (substr($str, 0, 1) === '>') return array('field' => $field, 'op' => 5, 'value' => substr($str, 1));
	if (substr($str, 0, 1) === '=') return array('field' => $field, 'op' => 6, 'value' => substr($str, 1));
	if (substr($str, 0, 1) === '*') return array('field' => $field, 'op' => 7, 'value' => substr($str, 1));
	if (substr($str, 0, 1) === '^') return array('field' => $field, 'op' => 8, 'value' => substr($str, 1));
	else return array('field' => $field, 'op' => 7, 'value' => $str);
}

// Register hook functions
UnbRegisterHook('cp.addcategory', 'UnbHookLogViewerAddCPCategory');
UnbRegisterHook('cp.categorypage', 'UnbHookLogViewerCPCategoryPage');

?>