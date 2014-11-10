<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// users.inc.php
// User list

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// -------------------- Import request variables --------------------

$adduser = ($_GET['adduser'] == 1);

$sort = $_GET['sort'];
switch ($sort)
{
	case 'id': break;
	case 'name': break;
	case 'regdate': break;
	case 'location': break;
	case 'posts': break;
	default:
		$sort = rc('default_users_sort');
		if (!$sort) $sort = 'posts';
}

$page = $_GET['page'];
if (!is_numeric($page) || $page < 1) $page = 1;

$team = ($_GET['team'] == 1);
$online = ($_GET['online'] == 1);
if ($team && $online) $team = false;   // allow only one of them to be true

$first = $_GET['first'];
if (strlen($first) != 1 || ($first < 'A' || $first > 'Z') && $first != '-') $first = false;

$selgroup = intval($_GET['showgroup']);   // very simple variable get here, more checks are performed where needed
$invgroup = ($_GET['invgroup'] == 1 ? 1 : 0);

$error = false;
$donotcount = false;

// -------------------- Add new user manually from the userlist --------------------

if ($_POST['action'] == 'adduser' &&
    UnbCheckRights('adduser') &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$user = new IUser;

	$name = trim($_POST['Name']);
	$password = trim($_POST['Password']);
	$email = trim($_POST['EMail']);

	if ($name == '' || $password == '' || $email == '')
	{
		$error .= $UNB_T['users.adduser.error.form not complete'] . '<br />';
	}

	// user name valid?
	if (strpos($name, "\xC2\xA0") !== false)   // UTF-8 for \xA0 is \xC2A0
	{
		$error .= $UNB_T['error.username disallowed'] . '<br />';
	}
	else if (is_numeric($name))
	{
		$error .= $UNB_T['error.username disallowed'] . '<br />';
	}
	if (rc('username_minlength') &&
		strlen($name) < rc('username_minlength'))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{min}', rc('username_minlength'), $UNB_T['cp.error.username too short']) . '<br />';
	}
	if (rc('username_maxlength') &&
		strlen($name) > min(rc('username_maxlength'), 40))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{max}', rc('username_maxlength'), $UNB_T['cp.error.username too long']) . '<br />';
	}

	// username available?
	if ($user->FindByName($name))
	{
		$error .= $UNB_T['error.username assigned'] . '<br />';
	}

	$secure = UnbIsSecurePassword($password, $name);
	UnbRequireTxt('controlpanel');   // for below error messages
	switch ($secure)
	{
		case 0: break;
		case 1: $error .= str_replace('{n}', rc('pass_minlength'), $UNB_T['cp.error.password too short']) . '<br />'; break;
		case 2: $error .= $UNB_T['cp.error.password is username'] . '<br />'; break;
		case 3: $error .= $UNB_T['cp.error.password need number'] . '<br />'; break;
		case 4: $error .= $UNB_T['cp.error.password need special'] . '<br />'; break;
		default: $error .= $UNB_T['cp.error.password generic'] . '<br />'; break;
	}

	// e-mail vaild?
	if (!is_mailaddr($email))
	{
		$error .= $UNB_T['error.invalid email'] . '<br />';
	}

	if (!$error)
	{
		$user = new IUser;
		if (!$user->Add($_POST['Name'], $_POST['Password'], $_POST['EMail']))
		{
			$error .= $UNB_T['users.error.user not created'] . ' (' . t2h($user->db->LastError()) . ')<br />';
		}
		else
		{
			$user->SetValidatedEMail($_POST['EMail']);
			UnbAddLog('add_user ' . $_POST['Name']);
		}
		unset($user);
	}
}

// List users
//
// in sort = (string) Sort criterium (see top of this file)
// in adduser = (bool) Show Add User form
// in page = (int) 1+: Page number of list
// in first = (char) filter by first char of name
//
function ListUsers($sort, $adduser, $page, $first)
{
	global $HTMLHEAD, $invgroup, $selgroup, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$adduser = intval($adduser);
	$page = intval($page);

	$users_per_page = intval(rc('users_per_page'));

	if (!UnbCheckRights('adduser')) $adduser = false;

	if ($first == '-')
		$where = "NOT (LEFT(Name,1) BETWEEN 'A' AND 'Z') AND NOT (LEFT(Name,1) BETWEEN 'a' AND 'z')";
	elseif ($first)
		$where = 'Name LIKE \'' . UnbDbEncode($first) . '%\'';
	else
		$where = '';

	if (trim($_REQUEST['username']) != '')
	{
		$where = 'Name LIKE \'%' . UnbDbEncode(trim($_REQUEST['username'])) . '%\'';
		$username_str = '&username=' . urlencode(trim($_REQUEST['username']));
	}

	$selgroup_str = '';
	if ($selgroup) $selgroup_str = '&showgroup=' . $selgroup;
	if ($invgroup) $selgroup_str .= '&invgroup=1';

	$selgroup_arr = array();
	if ($selgroup) $selgroup_arr['showgroup'] = $selgroup;
	if ($invgroup) $selgroup_arr['invgroup'] = 1;

	// Filter by 1st character
	$f = '';
	for ($a = ord('A'); $a <= ord('Z'); $a++)
	{
		if ($first == chr($a))
			$f .= '&nbsp;' . chr($a) . '&nbsp;';
		else
			$f .= '<a href="' . UnbLink('@this', 'sort=' . $sort . '&first=' . chr($a) . $selgroup_str, true) . '">&nbsp;' . chr($a) . '&nbsp;</a>';
	}
	if ($first == '-')
		$f .= '&nbsp;#&nbsp;';
	else
		$f .= '<a href="' . UnbLink('@this', 'sort=' . $sort . '&first=-' . $selgroup_str, true) . '">&nbsp;#&nbsp;</a>';
	$f .= '<a href="' . UnbLink('@this', 'sort=' . $sort . '&first=' . $selgroup_str, true) . '">&nbsp;' . $UNB_T['users.filter.all'] . '&nbsp;</a>';
	$TP['userlistFilter'] = $f;

	// Usergroup selection
	// NOTE: Disabled the access restriction.
	// See http://newsboard.unclassified.de/forum/thread/731 for details.
#	if (UnbCheckRights('setusergroups'))
#	{
		$groupnames = UnbGetGroupNames(false);
		$selgroup = intval($_GET['showgroup']);
		if ($selgroup > 0 && !array_key_exists($selgroup, $groupnames)) $selgroup = 0;

		$g = '';
		$g .= '<form id="form" action="' . UnbLink('@this', null, true, /*sid*/ false, /*derefer*/ false, /*short*/ false) . '" method="get">' . endl;
		$g .= UnbFormSessionId() . endl;
		$g .= '<input type="hidden" name="req" value="users" />' . endl;
		$g .= '<input type="hidden" name="sort" value="' . $sort . '" />' . endl;
		$g .= '<input type="hidden" name="first" value="' . $first . '" />' . endl;
		$g .= '<input type="hidden" name="username" value="' . trim($_REQUEST['username']) . '" />' . endl;
		$g .= $UNB_T['users.show usergroup'] . ': <select name="showgroup" onchange="this.form.submit();">' . endl;
		$g .= '<option value="0"' . ($selgroup == 0 ? ' selected="selected"' : '') . '>(' . $UNB_T['users.filter.all'] . ')</option>' . endl;
		$g .= '<option value="-1"' . ($selgroup == -1 ? ' selected="selected"' : '') . '>(' . $UNB_T['users.filter.no group'] . ')</option>' . endl;
		foreach ($groupnames as $id => $name) if ($id != UNB_GROUP_GUESTS)
		{
			$g .= '<option value="' . $id . '"' . ($selgroup == $id ? ' selected="selected"' : '') . '>' . $groupnames[$id] . '</option>' . endl;
		}
		$g .= '</select> &nbsp;' . endl;
		$g .= '<label><input type="checkbox" name="invgroup" value="1"' . ($invgroup && $selgroup ? ' checked="checked"' : '') . (!$selgroup ? ' disabled="disabled"' : '') . ' onclick="this.form.submit();" />' . $UNB_T['users.invert selection'] . '</label>';
		$g .= '<noscript><input type="submit" value="' . $UNB_T['users.do filter'] . '" /></noscript>' . endl;
		$g .= '</form>' . endl;
		$TP['userlistGroupSel'] = $g;

		$un = '';
		$un .= '<form id="form" action="' . UnbLink('@this', null, true, /*sid*/ false, /*derefer*/ false, /*short*/ false) . '" method="get">' . endl;
		$un .= UnbFormSessionId() . endl;
		$un .= '<input type="hidden" name="req" value="users" />' . endl;
		$un .= '<input type="hidden" name="sort" value="' . $sort . '" />' . endl;
		$un .= '<input type="hidden" name="showgroup" value="' . $selgroup . '" />' . endl;
		$un .= $UNB_T['username'] . ': <input type="text" name="username" value="' . t2i($_REQUEST['username']) . '" size="20" style="width: 15em;" />' . endl;
		$un .= '<input type="submit" value="' . $UNB_T['users.do search'] . '" />' . endl;
		$un .= '</form>' . endl;
		$TP['userlistFindUsername'] = $un;
#	}
#	else
#	{
#		$selgroup = 0;
#	}

	// Sorting
	$order = 'Name';
	switch ($sort)
	{
		case 'name':
			$order = 'Name';
			break;
		case 'id':
			$order = 'ID';
			break;
		case 'regdate':
			$order = 'RegDate';
			break;
		case 'location':
			// Always sort locations first, none last, also if no Unicode support present and some characters
			// are sorted after "z".
			$order = 'CASE WHEN LENGTH(Location) > 0 THEN CONCAT(\'a\', Location) ELSE \'z\' END, Name';
			break;
		case 'posts':
			$order = 'posts DESC, RegDate';
			break;
	}

	$TP['userlistSort'] = $sort;
	$TP['userlistAdd'] = $adduser;

	$TP['userlistLinkSortName'] = UnbLink('@this', 'sort=name&first=' . $first . $selgroup_str . $username_str, true);
	$TP['userlistLinkSortRegdate'] = UnbLink('@this', 'sort=regdate&first=' . $first . $selgroup_str . $username_str, true);
	$TP['userlistLinkSortPosts'] = UnbLink('@this', 'sort=posts&first=' . $first . $selgroup_str . $username_str, true);

	$user = new IUser;

	// ---------- Page selection ----------
	$user_count = $user->CountWithGroup($where, $selgroup, $invgroup);
	$TP['userlistCount'] = $user_count;
	$params = array(
		'sort' => ($sort != 'posts' ? $sort : null),
		'first' => ($first ? $first : null));
	$params = array_merge($params, $selgroup_arr);
	$TP['userlistPageStr'] = UnbPageSelection($user_count, $users_per_page, $page, $params);

	// -------------------- User actions --------------------
	$TP['userlistTotalCount'] = intval($user_count);
	if (UnbCheckRights('adduser'))
	{
		$TP['userlistActionNew'] = '<a href="' . UnbLink('@this', 'adduser=1&first=' . $first . $selgroup_str . $username_str . '#newuser', true) . '"><img ' . $UNB['Image']['add'] . ' /> ' . $UNB_T['users.new'] . '</a>';
	}

	if (!$user_count && !$adduser)
	{
		return false;
	}

	// ----- Show table header -----
	$TP['userlistShowRegdate'] = $show_regdate = rc('ulist_regdate');
	$TP['userlistShowLocation'] = $show_location = rc('ulist_location');
	$TP['userlistShowPosts'] = $show_posts = rc('ulist_posts');
	$TP['userlistShowLastpost'] = $show_lastpost = rc('ulist_lastpost');
	$TP['userlistShowAny'] = $show_any = $show_regdate || $show_contact || $show_location || $show_posts || $show_lastpost;

	$show_cnt = 1;
	if ($show_regdate) $show_cnt++;
	if ($show_location) $show_cnt++;
	if ($show_posts) $show_cnt++;
	if ($show_lastpost) $show_cnt++;
	$TP['userlistColCount'] = $show_cnt;

	if ($show_posts) UnbCountUserPosts();

	if ($adduser)
	{
		$TP['userlistAddFormLink'] = UnbLink('@this', null, true);
		$TP['userlistAddCancelLink'] = UnbLink('@this', null, true);
	}

	$rightmost = 'lastpost';
	if (!$show_lastpost && $rightmost == 'lastpost') $rightmost = 'posts';
	if (!$show_posts && $rightmost == 'posts') $rightmost = 'location';
	if (!$show_location && $rightmost == 'location') $rightmost = 'contact';
	if (!$show_contact && $rightmost == 'contact') $rightmost = 'regdate';
	if (!$show_regdate && $rightmost == 'regdate') $rightmost = '';
	$TP['userlistRightmost'] = $rightmost;

	if ($users_per_page)
		$limit = (($page - 1) * $users_per_page) . ',' . $users_per_page;
	else
		$limit = '';

	$table = array(array('', 'Users', 'u', ''));
	if ($selgroup == -1)
	{
		$table[] = array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User');
		$where .= ($where ? ' AND ' : '') . 'gm.Group IS' . ($invgroup ? ' NOT' : '') . ' NULL';
	}
	elseif ($selgroup > 0 && !$invgroup)
	{
		$table[] = array('INNER', 'GroupMembers', 'gm', 'u.ID = gm.User AND gm.Group = ' . $selgroup);
	}
	elseif ($selgroup > 0 && $invgroup)
	{
		$table[] = array('LEFT', 'GroupMembers', 'gm', 'u.ID = gm.User AND gm.Group = ' . $selgroup);
		$where .= ($where ? ' AND ' : '') . 'gm.Group IS NULL';
	}
	$table[] = array('LEFT', 'Posts', 'p', 'u.ID = p.User');

	// ----- Show table contents -----
	$users = $UNB['Db']->FastQueryArray(
		/*table*/ $table,
		/*fields*/ 'u.*, COUNT(p.ID) AS posts',
		/*where*/ $where,
		/*order*/ $order,
		/*limit*/ $limit,
		/*group*/ 'u.ID');

	$count = 0;
	$TP['userlist'] = array();
	if ($users) foreach ($users as $user_rec)
	{
		$user->LoadFromRecord($user_rec);

		$tpitem = array();

		$tpitem['link'] = UnbMakeUserLink($user->GetID(), $user->GetName(), true);
		$tpitem['onlineImg'] = UnbGetUserOnlineImg($user->GetOnline());
		if ($user->GetGender() != '')
			$tpitem['genderImg'] = ' ' . UnbGetGenderImage($user->GetGender());
		if ($user->GetPhoto() != '')
			$tpitem['hasPhoto'] = ' <img ' . $UNB['Image']['photo'] . ' title="' . $UNB_T['user photo'] . '" />';
		$tpitem['statusText'] = UnbGetUserStatusText($user->GetID(), '(%s)', true, true);
		if (UnbCheckRights('editprofile'))
			$tpitem['editLink'] = UnbLink('@cp', 'id=' . $user->GetID() . '&cat=summary', true);

		if ($show_regdate)
		{
			$tpitem['regDate'] = UnbFormatTime($user->GetRegDate(), 1);
		}

		if ($show_contact)
		{
			if ($user->GetEMail() != '')
			{
				$tpitem['email'] = $user->GetEMail();
				$tpitem['emailLink'] = UnbLink('@cp', 'id=' . $user->GetID() . '&action=email', true);
			}

			if ($user->GetJabber() != '')
			{
				$tpitem['jabber'] = UnbMaskMail(t2i($user->GetJabber()));
				$tpitem['jabberLink'] = 'xmpp:' . UnbMaskMail(t2i($user->GetJabber()));
			}

			if ($user->GetICQ() != '')
			{
				$tpitem['icq'] = t2i($user->GetICQ());
				$tpitem['icqLink'] = UnbLink('http://web.icq.com/wwp/' . $user->GetICQ(), null, true, /*sid*/ false, /*derefer*/ true);
			}

			if ($user->GetAIM() != '')
			{
				$tpitem['aim'] = t2i($user->GetAIM());
			}

			if ($user->GetYIM() != '')
			{
				$tpitem['yim'] = t2i($user->GetYIM());
			}

			if ($user->GetMSN() != '')
			{
				$tpitem['msn'] = UnbMaskMail(t2i($user->GetMSN()));
			}

			if ($user->GetHomepage() != '')
			{
				$tpitem['homepageLink'] = UnbLink($user->GetHomepage(), null, true, /*sid*/ false, /*derefer*/ true);
			}
		}

		if ($show_location)
		{
			$tpitem['location'] = '';
			if (rc('location_link'))
				$tpitem['location'] .= '<a href="' . UnbLink(str_replace('%s', $user->GetLocation(), rc('location_link')), null, true, /*sid*/ false, /*derefer*/ true) . '">';
			$tpitem['location'] .= t2h($user->GetLocation());
			if (rc('location_link'))
				$tpitem['location'] .= '</a>';
		}

		if ($show_posts)
		{
			$posts = UnbGetPostsByUser($user->GetID());
			$tpitem['posts'] = $posts;
			if ($posts > 0)
				$tpitem['postsLink'] = UnbLink('@search', 'nodef=1&Query=' . $user->GetID() . '&ResultView=2&InUser=1&Sort=2', true);
		}

		if ($show_lastpost)
		{
			$record = UnbGetLastPost('User=' . $user->GetID());
			if ($record)
			{
				$tpitem['lastpost'] = UnbMakePostLink($record, 0, true) . UnbFriendlyDate($record['Date'], 1, 3) . '</a>';
			}
		}

		$count++;
		$TP['userlist'][] = $tpitem;
	} // foreach $users

	if (sizeof($TP['userlist']) > 0)
	{
		$TP['userlist'][0]['firstitem'] = true;
		$TP['userlist'][sizeof($TP['userlist']) - 1]['lastitem'] = true;
	}
}

// Show board operators team
//
function ShowTeam()
{
	// List all users of a specified user group
	//
	function ListUsersByGroup($gid, $gname)
	{
		global $UNB, $UNB_T;
		$TP =& $UNB['TP'];

		$members = UnbGetGroupMembers($gid);
		if (!$members) return false;
		$mstr = join(',', $members);

		$tpteam = array();
		$tpteam['groupname'] = t2h($gname);
		$tpteam['users'] = array();

		$user = new IUser;
		if ($user->GetList('ID IN (' . $mstr . ')', 'Name')) do
		{
			$tpitem = array();

			if ($user->GetAvatar() != '' && rc('avatars_enabled'))
			{
				$tpitem['avatarUrl'] = t2i(UnbAvatarUrl($user));
				$tpitem['avatarSize'] = UnbAvatarSize($user, false);
			}

			$tpitem['link'] = UnbMakeUserLink($user->GetID(), $user->GetName());
			$tpitem['onlineImg'] = UnbGetUserOnlineImg($user->GetOnline());
			if ($user->GetGender() != '')
				$tpitem['genderImg'] = ' ' . UnbGetGenderImage($user->GetGender());
			if ($user->GetPhoto() != '')
				$tpitem['hasPhoto'] = ' <img ' . $UNB['Image']['photo'] . ' title="' . $UNB_T['user photo'] . '" />';
			$tpitem['status'] = UnbGetUserStatusText($user->GetID(), ' <small>(%s)</small>', true, true);
			$tpitem['memberSince'] = UnbFriendlyDate($user->GetRegDate(), 2, 1, true, 1);
			$tpitem['title'] = t2h($user->GetTitle());

			if ($user->GetEMail() != '')
				$tpitem['emailLink'] = UnbMakeUserLink($user->GetID(), '<img ' . $UNB['Image']['email'] . ' title="' . $UNB_T['e-mail'] . '" />', false, true, false) . ' &nbsp; ';

			if ($user->GetJabber() != '')
				$tpitem['jabberLink'] = '<a href="xmpp:' . UnbMaskMail(t2i($user->GetJabber())) . '"><img ' . $UNB['Image']['jabber'] . ' title="' . $UNB_T['jabber'] . ': ' . UnbMaskMail(t2i($user->GetJabber())) . '" /></a> &nbsp; ';

			if ($user->GetICQ() != '')
				$tpitem['icqLink'] = '<a href="' . UnbLink('http://web.icq.com/wwp/' . $user->GetICQ(), null, true, /*sid*/ false, /*derefer*/ true) . '"><img ' . $UNB['Image']['icq'] . ' title="' . $UNB_T['icq'] . ': ' . t2i($user->GetICQ()) . '" /></a> &nbsp; ';

			if ($user->GetAIM() != '')
				$tpitem['aimLink'] = '<img ' . $UNB['Image']['aim'] . ' title="' . $UNB_T['aim'] . ': ' . t2i($user->GetAIM()) . '" /> &nbsp; ';

			if ($user->GetYIM() != '')
				$tpitem['yimLink'] = '<img ' . $UNB['Image']['yim'] . ' title="' . $UNB_T['yim'] . ': ' . t2i($user->GetYIM()) . '" /> &nbsp; ';

			if ($user->GetMSN() != '')
				$tpitem['msnLink'] = '<img ' . $UNB['Image']['msn'] . ' title="' . $UNB_T['msn'] . ': ' . UnbMaskMail(t2i($user->GetMSN())) . '" /> &nbsp; ';

			if ($user->GetHomepage() != '')
				$tpitem['websiteLink'] = '<a href="' . UnbLink($user->GetHomepage(), null, true, /*sid*/ false, /*derefer*/ true) . '"><img ' . $UNB['Image']['homepage'] . ' title="' . $UNB_T['homepage'] . '" /></a> &nbsp; ';

			$tpitem['location'] = $user->GetLocation();

			$posts = UnbGetPostsByUser($user->GetID());
			$tpitem['posts'] = '';
			if ($posts > 0)
				$tpitem['posts'] .= '<a href="' . UnbLink('@search', 'nodef=1&Query=' . $user->GetID() . '&ResultView=2&InUser=1&Sort=2', true) . '">';
			$tpitem['posts'] .= $posts;
			if ($posts > 0)
				$tpitem['posts'] .= '</a>';

			$record = UnbGetLastPost('User=' . $user->GetID());
			if ($record)
			{
				$tpitem['lastPostLink'] = UnbMakePostLink($record);
				$tpitem['lastPostDate'] = UnbFriendlyDate($record['Date'], 1, 3);
			}

			$tpteam['users'][] = $tpitem;
		}
		while ($user->GetNext());

		if (sizeof($tpteam['users']) > 0)
		{
			$tpteam['users'][0]['firstitem'] = true;
			$tpteam['users'][sizeof($tpteam['users']) - 1]['lastitem'] = true;
		}

		$TP['userlistTeams'][] = $tpteam;
	}

	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	UnbCountUserPosts();
	$groupnames = UnbGetGroupNames(true);

	$selgroup = intval($_GET['showgroup']);
	if (!array_key_exists($selgroup, $groupnames) || $selgroup <= UNB_GROUP_MAX) $selgroup = 0;

	if (UnbCheckRights('showuserlist') && sizeof($groupnames) > 2)
	{
		$f = '<div class="p"><form id="form" action="' . UnbLink('@this', null, true, /*sid*/ false, /*derefer*/ false, /*short*/ false) . '" method="get">' . endl;
		$f .= UnbFormSessionId() . endl;
		$f .= '<input type="hidden" name="req" value="users" />' . endl;
		$f .= '<input type="hidden" name="team" value="1" />' . endl;
		$f .= $UNB_T['users.show usergroup'] . ': <select name="showgroup" onchange="this.form.submit();">' . endl;
		$f .= '<option value="0"' . (!$selgroup ? ' selected="selected"' : '') . '>' . $groupnames[UNB_GROUP_ADMINS] . ' ' . $UNB_T['and'] . ' ' . $groupnames[UNB_GROUP_MODS] . '</option>' . endl;
		foreach ($groupnames as $id => $name)
		{
			if ($id > UNB_GROUP_MAX)
				$f .= '<option value="' . $id . '"' . ($selgroup == $id ? ' selected="selected"' : '') . '>' . $groupnames[$id] . '</option>' . endl;
		}
		$f .= '</select>' . endl;
		$f .= '<noscript> <input type="submit" value="' . $UNB_T['select'] . '" /></noscript>' . endl;
		$f .= '</form></div>' . endl;

		$TP['userlistTeamSelectGroup'] = $f;
	}
	else
	{
		$selgroup = 0;   // if user has no rights to view the userlist, i.e. a guest, then only display admin/gmods groups
	}

	$TP['userlistTeams'] = array();

	if (!$selgroup)
	{
		if ($groupnames[UNB_GROUP_MODS] == '' || $groupnames[UNB_GROUP_ADMINS] == '') $groupnames = UnbGetGroupNames(false);
		ListUsersByGroup(UNB_GROUP_ADMINS, $groupnames[UNB_GROUP_ADMINS]);
		ListUsersByGroup(UNB_GROUP_MODS, $groupnames[UNB_GROUP_MODS]);
	}
	else
	{
		ListUsersByGroup($selgroup, $groupnames[$selgroup]);
	}

}

// List currently online users
//
function ListOnlineUsers()
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	if (!rc('enable_trace_users'))
	{
		$TP['userlistOnlineError'] = $UNB_T['users.error.trace users disabled'];
		return;
	}

	$user = new IUser;
	$forum = new IForum;

	// compare with users.lib.php:IUser() and main.inc.php:ShowOnlineUsers()
	$user_online_timeout = 300;
	if (rc('user_online_timeout')) $user_online_timeout = rc('user_online_timeout');

	$users = $user->GetListArray('LastActivity >= ' . (time() - $user_online_timeout * 2), 'LastActivity DESC');
	if (is_array($users))
		$cu = sizeof($users);   // count users
	else
		$cu = 0;

	$guests = $UNB['Db']->FastQueryArray('Guests', '*', 'LastActivity >= ' . (time() - $user_online_timeout * 2), 'LastActivity DESC');
	if (is_array($guests))
		$cg = sizeof($guests);   // count guests
	else
		$cg = 0;

	$TP['userlistOnline'] = array();
	// pu=pos users, pg=pos guests
	for ($pu = 0, $pg = 0; $pu < $cu || $pg < $cg; )
	{
		$tpitem = array();

		// who comes next, user or guest?
		//
		$user->Load($users[$pu]);
		if ($user->GetLastActivity() >= $guests[$pg]['LastActivity'])
		{
			// user
			$tpitem['name'] = UnbMakeUserLink($user->GetID(), $user->GetName(), true);
			$tpitem['onlineImg'] = UnbGetUserOnlineImg($user->GetOnline());
			if ($user->GetGender() != '')
				$tpitem['genderImg'] = ' ' . UnbGetGenderImage($user->GetGender());
			$tpitem['status'] = UnbGetUserStatusText($user->GetID(), ' <small>(%s)</small>', true, true);

			$lastactivity = $user->GetLastActivity();
			$lastforum = $user->GetLastForum();

			$pu++;
		}
		else
		{
			// guest
			if ($guests[$pg]['UserName'] == '_not_a_browser_')
			{
				$pg++;
				continue;
			}

			$tpitem['name'] = str_limit(strtoupper($guests[$pg]['Session']), 6);
			if ($guests[$pg]['UserName'])
				$tpitem['name'] .= ': ' . t2h(str_limit($guests[$pg]['UserName'], 20));
			$tpitem['status'] = UnbGetUserStatusText(0, ' <small>(%s)</small>', false, true);

			$lastactivity = $guests[$pg]['LastActivity'];
			$lastforum = $guests[$pg]['LastForum'];

			$pg++;
		}

		if ($lastforum == 0)
		{
			$tpitem['where'] = $UNB_T['in.overview'];
		}
		elseif ($lastforum > 0)
		{
			if ($forum->Load($lastforum))
			{
				$tpitem['where'] = $UNB_T['in.forum'] . ' ' . '<a href="' . UnbLink('@main', 'id=' . $forum->GetID(), true) . '">' . t2h($forum->GetName()) . '</a>';
			}
			else
			{
				$tpitem['where'] = $UNB_T['in.forum'] . ' ' . $lastforum;
			}
		}
		else
		{
			if ($lastforum == UNB_ULF_CONFIG) $tpitem['where'] = $UNB_T['in.config'];
			elseif ($lastforum == UNB_ULF_PROFILE) $tpitem['where'] = $UNB_T['in.user profile'];
			elseif ($lastforum == UNB_ULF_SEARCH) $tpitem['where'] = $UNB_T['in.search'];
			elseif ($lastforum == UNB_ULF_STAT) $tpitem['where'] = $UNB_T['in.statistics'];
			elseif ($lastforum == UNB_ULF_USERS) $tpitem['where'] = $UNB_T['in.userlist'];
			else $tpitem['where'] = '[' . $lastforum . ']';
		}

		$secs = time() - $lastactivity;
		$mins = intval($secs / 60);
		$secs %= 60;
		if ($secs) $secs .= ' ' . UteTranslateNum('seconds', $secs);
		else $secs = '';
		if ($mins) $mins .= ' ' . UteTranslateNum('minutes', $mins);
		else $mins = '';
		if ($secs != '' && $mins != '') $mins .= ' ';
		if ($secs == '' && $mins == '') $secs = '0 ' . $UNB_T['seconds'];
		$tpitem['when'] = ' &nbsp; <small>' . str_replace('{x}', $mins . $secs, $UNB_T['x ago']) . '</small>';

		$TP['userlistOnline'][] = $tpitem;
	}

	if (sizeof($TP['userlistOnline']) > 0)
	{
		$TP['userlistOnline'][0]['firstitem'] = true;
		$TP['userlistOnline'][sizeof($TP['userlistOnline']) - 1]['lastitem'] = true;
	}

	$interval = rc('online_users_reload_interval');
	if ($interval > 1000)
	{
		$TP['userlistOnlineReloadUrl'] = UnbLink('@users', 'online=1');
		$TP['userlistOnlineReloadInterval'] = $interval;
	}
}

if ($error) UnbErrorLog($error);

// -------------------- BEGIN page --------------------

UnbBeginHTML($UNB_T['users.userlist']);

$TP =& $UNB['TP'];

$TP['errorMsg'] .= $error;

if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity(UNB_ULF_USERS);
else UnbSetGuestLastForum(UNB_ULF_USERS);

if (!UnbCheckRights('showuserlist'))
{
	$TP['userlistType'] = 'team';
	UnbAddLog('show_team');
	ShowTeam();
}
else
{
	if ($selgroup) $selgroup_str = '&showgroup=' . $selgroup; else $selgroup_str = '';
	if ($invgroup) $selgroup_str .= '&invgroup=1';

	$TP['userlistLinkUsers'] = UnbLink('@users', null, true);
	$TP['userlistLinkTeam'] = UnbLink('@users', 'team=1', true);
	$TP['userlistEnableTraceUsers'] = rc('enable_trace_users');
	$TP['userlistLinkOnline'] = UnbLink('@users', 'online=1', true);

	if ($team)
	{
		$TP['userlistType'] = 'team';
		UnbAddLog('show_team');
		ShowTeam();
	}
	elseif ($online)
	{
		$TP['userlistType'] = 'online';
		UnbAddLog('show_online_users');
		if (!UnbCheckRights('showonlineusers'))
		{
			UnbErrorLog('Access denied');
			$TP['userlistOnlineError'] = $UNB_T['error.access denied'];
		}
		else
		{
			ListOnlineUsers();
			$donotcount = false;
		}
	}
	else
	{
		$TP['userlistType'] = 'users';
		UnbAddLog('show_users by ' . $sort . ' page ' . $page . ' first ' . $first);
		ListUsers($sort, $adduser, $page, $first);
	}
}

UteRemember('userlist.html', $TP);

// Do not count page hits for periodic who-is-online list updates
if (!$donotcount) UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
