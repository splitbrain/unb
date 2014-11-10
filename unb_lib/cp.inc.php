<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// cp.inc.php
// Control Panel and User Profile

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

UnbRequireTxt('controlpanel');

// Import request variables
$userid = intval($_REQUEST['id']);
if ($userid <= 0) $userid = $UNB['LoginUserID'];
if ($userid < 0) $userid = 0;

$cat = strtolower(trim($_REQUEST['cat']));

// See if a date (day, month, year) is valid, taking care of leap years
// Valid years are from 1000 to 9999
//
// returns (bool) valid date
//
function UnbMyCheckDate($day, $month, $year)
{
	// Clean parameters
	$day = intval($day);
	$month = intval($month);
	$year = intval($year);

	if ($year < 1000 || $year > 9999) return false;
	if ($month < 1 || $month > 12) return false;
	if ($day < 1 || $day > 31) return false;
	switch ($month)
	{
		case 2:
			if ($day > 28 + ((!($year % 4) && $year % 100) || !($year % 400) ? 1 : 0)) return false;
			break;
		case 4:
		case 6:
		case 9:
		case 11:
			if ($day == 31) return false;
	}
	return true;
}

// Check if an avatar file has correct filetype
//
// returns (int) 1: no '.' found
//               2: invalid filename extension
//
function UnbIsValidAvatarName($filename)
{
	$x = strrpos($filename, '.');
	if ($x === false) return 1;
	$ext = substr($filename, $x);
	switch (strtolower($ext))
	{
		// update profile.php, EditProfile, Save, Avatar setting, on change
		case '.jpg':
		case '.jpeg':
		case '.gif':
		case '.png':
			return true;
	}
	return 2;
}

// Check if an avatar file has correct filesize
//
// in remote = (bool) this is a remote URL
//
// returns (int) 3: file too big
//               4: not a valid image file
//               5: too wide
//               6: too high
//
function UnbIsValidAvatarSize($filename, $remote = false)
{
	if (!$remote && filesize($filename) > UnbCheckRights('maxavatarsize')) return 3;   // file too long
	if ($remote && !ini_get('allow_url_fopen')) return true;   // cannot check remote files, so accept them
	$a = GetImageSize($filename);
	if ($a === false) return 4;   // not a valid image file
	if ($a[0] > UnbCheckRights('maxavatarwidth')) return 5;    // too wide
	if ($a[1] > UnbCheckRights('maxavatarheight')) return 6;    // too high
	return true;
}

// Check if a user photo file has correct filetype
//
// returns (int) 1: no '.' found
//               2: invalid filename extension
//
function UnbIsValidPhotoName($filename)
{
	return UnbIsValidAvatarName($filename);
}

// Check if a user photo file has correct filesize
//
// in remote = (bool) this is a remote URL
//
// returns (int) 3: file too big
//               4: not a valid image file
//               5: too wide
//               6: too high
//
function UnbIsValidPhotoSize($filename, $remote = false)
{
	if (!$remote && filesize($filename) > UnbCheckRights('maxphotosize')) return 3;   // file too long
	if ($remote && !ini_get('allow_url_fopen')) return true;   // cannot check remote files, so accept them
	$a = GetImageSize($filename);
	if ($a === false) return 4;   // not a valid image file
	if ($a[0] > UnbCheckRights('maxphotowidth')) return 5;    // too wide
	if ($a[1] > UnbCheckRights('maxphotoheight')) return 6;    // too high
	return true;
}

// Rescale an avatar file to smaller dimensions
//
// in filename = (string) File to rescale, may be the temporary upload path (TODO: test this on Linux!)
//
// returns (bool) true: success
//         (string) error message
//
function UnbRescaleAvatar($filename)
{
	$maxwidth = UnbCheckRights('maxavatarwidth');
	$maxheight = UnbCheckRights('maxavatarheight');
	return UnbRescaleImage($filename, $maxwidth, $maxheight);
}

// Rescale a user photo file to smaller dimensions
//
// in filename = (string) File to rescale, may be the temporary upload path (TODO: test this on Linux!)
//
// returns (bool) true: success
//         (string) error message
//
function UnbRescalePhoto($filename)
{
	$maxwidth = UnbCheckRights('maxphotowidth');
	$maxheight = UnbCheckRights('maxphotoheight');
	return UnbRescaleImage($filename, $maxwidth, $maxheight);
}

// Rescale an arbitrary image file to smaller dimensions
//
// in filename = (string) File to rescale, may be the temporary upload path (TODO: test this on Linux!)
// in maxwidth = (int) Maximum horizontal image width to that the image will be reduced if it exceeds it
// in maxheight = (int) Maximum vertical image height to that the image will be reduced if it exceeds it
//
// returns (bool) true: success
//         (string) error message
//
function UnbRescaleImage($filename, $maxwidth, $maxheight)
{
	// clean parameters
	$maxwidth = intval($maxwidth);
	$maxheight = intval($maxheight);
	if ($maxwidth <= 0 || $maxheight <= 0) return 'invalid parameters';

	$is = getimagesize($filename);
	switch ($is[2])
	{
		case 1: // GIF
			if (!(imagetypes() & IMG_GIF))
				return 'invalid image format';
			$src_img = imagecreatefromgif($filename);
			if ($src_img) $trans = imagecolortransparent($src_img);
			break;
		case 2: // JPEG
			$src_img = imagecreatefromjpeg($filename);
			break;
		case 3: // PNG
			$src_img = imagecreatefrompng($filename);
			break;
		default:
			return 'invalid image format';
	}
	if ($src_img == false) return 'cannot open image';
	$src_width = $is[0];
	$src_height = $is[1];
	$ar = $src_width / $src_height;   // source image aspect ratio

	$dest_width = $maxwidth;
	$dest_height = $dest_width / $ar;
	if ($dest_height > $maxheight)
	{
		$dest_height = $maxheight;
		$dest_width = $dest_height * $ar;
	}

	$dest_img = imagecreatetruecolor($dest_width, $dest_height);
	if (isset($trans))
	{
		// set transparent as background colour
		$col = imagecolorsforindex($src_img, $trans);
		#$col['red'] = $col['green'] = $col['blue'] = 200;
		$trans = imagecolorallocate($dest_img, $col['red'], $col['green'], $col['blue']);
		imagefill($dest_img, 0, 0, $trans);
		// transparency will be lost but that's okay for me... go and resize that damn image yourself.
	}
	imagealphablending($src_img, true);
	imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
	imagesavealpha($dest_img, true);

	switch ($is[2])
	{
		case 1: // GIF
			// TODO: error detection for all writing functions
			imagegif($dest_img, $filename);
			break;
		case 2: // JPEG
			imagejpeg($dest_img, $filename, /*quality*/ 80);
			break;
		case 3: // PNG
			imagepng($dest_img, $filename);
			break;
	}
	imagedestroy($src_img);
	imagedestroy($dest_img);
	return true;
}

// Return name of extra user profile field
//
// in n = (int) starts with 1, maximum is $extra_count
//
function UnbGetProfileExtraName($n)
{
	global $UNB;
	return $UNB['ProfileExtraNames'][$n - 1];
}

// How many extra columns are present in the users table
//
function UnbGetProfileExtraDBCols()
{
	global $UNB;

	$auser = $UNB['Db']->FastQuery('Users', '*', '', '', 1);
	$n = 1;
	while (isset($auser['Extra' . $n])) $n++;
	$n--;
	return $n;
}

// Set count of extra columns in the users table
// This may cause data loss!
//
// in newcols = (int) new number of columns (maximum 10 allowed)
//
function UnbSetProfileExtraDBCols($newcols)
{
	global $UNB;

	if (!is_numeric($newcols)) return false;
	if ($newcols < 0) return false;
	if ($newcols > 10) return false;   // this limit can easily be overridden here

	// Clean parameters
	$newcols = intval($newcols);

	$curr = UnbGetProfileExtraDBCols();
	if ($newcols == $curr) return true;
	elseif ($newcols < $curr)
	{
		while ($curr > $newcols) if (!$UNB['Db']->RemoveField('Users', 'Extra' . $curr--)) return false;
		return true;
	}
	else
	{
		while ($curr < $newcols) if (!$UNB['Db']->AddField('Users', 'Extra' . ++$curr, 'VARCHAR(255) NOT NULL')) return false;
		return true;
	}
}

// Unwatch many threads
//
// in where = (string) SQL WHERE definition
// in userid = (int) optional user id, default is current user
// in bookmark = (bool) bookmark mode. false: clear notifications
//                                     true: clear bookmarks
//
function UnbUnwatchThreads($where, $userid = false, $bookmark = false)
{
	global $UNB;

	// Clean parameters
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	$mask = $bookmark ? UNB_NOTIFY_BOOKMARK : UNB_NOTIFY_MASK;
	if (!$UNB['Db']->ChangeRecord('Mode = Mode & ~' . $mask, $where . ' AND User=' . $userid, 'ThreadWatch')) return false;
	return true;
}

// Unwatch many forums
//
// in where = (string) SQL WHERE definition
// in userid = (int) optional user id, default is current user
//
function UnbUnwatchForums($where, $userid = false)
{
	global $UNB;

	// Clean parameters
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	if (!$UNB['Db']->ChangeRecord('Mode = Mode & ~' . UNB_NOTIFY_MASK, $where . ' AND User=' . $userid, 'ForumWatch')) return false;
	#if (!$UNB['Db']->RemoveRecord('Mode = 0', 'ForumWatch')) return false;
	// This also removes other attributes...
	return true;
}

// Unfilter many threads
//
// in where = (string) SQL WHERE definition
// in userid = (int) optional user id, default is current user
//
function UnbUnfilterThreads($where, $userid = false)
{
	global $UNB;

	// Clean parameters
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	$mask = UNB_UFF_IGNORE | UNB_UFF_HIDE;
	if (!$UNB['Db']->ChangeRecord('Flags = Flags & ~' . $mask, $where . ' AND Forum = 0 AND User=' . $userid, 'UserForumFlags')) return false;
	return true;
}

// Unfilter many forums
//
// in where = (string) SQL WHERE definition
// in userid = (int) optional user id, default is current user
//
function UnbUnfilterForums($where, $userid = false)
{
	global $UNB;

	// Clean parameters
	$userid = intval($userid);

	if (!$userid) $userid = $UNB['LoginUserID'];
	if (!$userid) return false;

	$mask = UNB_UFF_IGNORE | UNB_UFF_HIDE;
	if (!$UNB['Db']->ChangeRecord('Flags = Flags & ~' . $mask, $where . ' AND Thread = 0 AND User=' . $userid, 'UserForumFlags')) return false;
	#if (!$UNB['Db']->RemoveRecord('Flags = 0', 'UserForumFlags')) return false;
	// This also removes other attributes... (?) - see above: UnbUnwatchForums()
	return true;
}

// END functions block

$error = '';

// -------------------- Edit user profile --------------------

if ($_POST['action'] == 'edit' &&
    !$_POST['Preview'] &&
    in_array($cat, array('summary', 'account', 'appearance', 'postoptions', 'watched', 'bookmarks', 'filters')) &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$user = new IUser;

	if (!UnbCheckRights('editprofile', 0, 0, $userid))
	{
		UnbAddLog('edit_profile ' . $userid . ' no_access');
		$error .= $UNB_T['error.access denied'] . '<br />';
	}

	if (!$error && $_POST['RemoveSubscriptions'])
	{
		$unwatchThreads = array();
		$unwatchForums = array();
		foreach ($_POST as $name => $value)
		{
			if (preg_match('/^unwatch_(thread|forum)_(\d+)$/', $name, $m))
			{
				if ($m[1] == 'thread') $unwatchThreads[] = intval($m[2]);
				elseif ($m[1] == 'forum') $unwatchForums[] = intval($m[2]);
			}
		}

		if ($_POST['RemoveOlderThan'] == 1 &&
		    $_REQUEST['NumOfDays'] != '' && is_numeric($_REQUEST['NumOfDays']))
		{
			$days = intval($_REQUEST['NumOfDays']);
			$ids = $UNB['Db']->FastQuery1stArray('Threads', 'ID', 'LastPostDate < ' . (time() - $days * 3600 * 24));
			if (is_array($ids))
				$unwatchThreads = array_merge(
					$unwatchThreads,
					$ids);
		}

		if (sizeof($unwatchThreads)) UnbUnwatchThreads('Thread in (' . join(',', $unwatchThreads) . ')', $userid);
		if (sizeof($unwatchForums)) UnbUnwatchForums('Forum in (' . join(',', $unwatchForums) . ')', $userid);
		UnbAddLog('remove_subscriptions for ' . $userid . ' ok');
		UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat));
	}

	if (!$error && $_POST['RemoveBookmarks'])
	{
		$unbookmarkThreads = array();
		foreach ($_POST as $name => $value)
		{
			if (preg_match('/^unbookmark_thread_(\d+)$/', $name, $m))
			{
				$unbookmarkThreads[] = intval($m[1]);
			}
		}

		if ($_POST['RemoveOlderThan'] == 1 &&
		    $_REQUEST['NumOfDays'] != '' && is_numeric($_REQUEST['NumOfDays']))
		{
			$days = intval($_REQUEST['NumOfDays']);
			$ids = $UNB['Db']->FastQuery1stArray('Threads', 'ID', 'LastPostDate < ' . (time() - $days * 3600 * 24));
			if (is_array($ids))
				$unbookmarkThreads = array_merge(
					$unbookmarkThreads,
					$ids);
		}

		if (sizeof($unbookmarkThreads)) UnbUnwatchThreads('Thread in (' . join(',', $unbookmarkThreads) . ')', $userid, /*bookmark*/ true);
		UnbAddLog('remove_bookmarks for ' . $userid . ' ok');
		UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat));
	}

	if (!$error && $_POST['RemoveFilters'])
	{
		$unfilterThreads = array();
		$unfilterForums = array();
		foreach ($_POST as $name => $value)
		{
			if (preg_match('/^unfilter_(thread|forum)_(\d+)$/', $name, $m))
			{
				if ($m[1] == 'thread') $unfilterThreads[] = intval($m[2]);
				elseif ($m[1] == 'forum') $unfilterForums[] = intval($m[2]);
			}
		}

		if ($_POST['RemoveOlderThan'] == 1 &&
		    $_REQUEST['NumOfDays'] != '' && is_numeric($_REQUEST['NumOfDays']))
		{
			$days = intval($_REQUEST['NumOfDays']);
			$ids = $UNB['Db']->FastQuery1stArray('Threads', 'ID', 'LastPostDate < ' . (time() - $days * 3600 * 24));
			if (is_array($ids))
				$unfilterThreads = array_merge(
					$unfilterThreads,
					$ids);
		}

		if (sizeof($unfilterThreads)) UnbUnfilterThreads('Thread in (' . join(',', $unfilterThreads) . ')', $userid);
		if (sizeof($unfilterForums)) UnbUnfilterForums('Forum in (' . join(',', $unfilterForums) . ')', $userid);
		UnbAddLog('remove_filters for ' . $userid . ' ok');
		UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat));
	}

	if ($_POST['Remove'])
	{
		if (UnbCheckRights('removeuser'))
		{
			if ($user->Remove($userid))
			{
				UnbAddLog('remove_user ' . $userid . ' ok');
				UnbForwardHTML(UnbLink('@users'));
			}
			else
			{
				UnbAddLog('remove_user ' . $userid . ' error');
				$error .= $UNB_T['cp.error.user not deleted'] . '<br />';
			}
		}
		else
		{
			UnbAddLog('remove_user ' . $userid . ' no_access');
			$error .= $UNB_T['error.access denied'] . '<br />';
		}
	}

	$user->Load($userid);

	if (isset($_POST['NewPassword']) &&
		$_POST['NewPassword'] != '' &&
		$_POST['NewPassword'] != $_POST['NewPasswordCfm'])
	{
		$error .= $UNB_T['cp.error.passwords dont match'] . '<br />';
	}

	if ($_POST['NewPassword'] != '')
	{
		$secure = UnbIsSecurePassword($_POST['NewPassword'], $user->GetName());
		switch ($secure)
		{
			case 0: break;
			case 1: $error .= str_replace('{n}', rc('pass_minlength'), $UNB_T['cp.error.password too short']) . '<br />'; break;
			case 2: $error .= $UNB_T['cp.error.password is username'] . '<br />'; break;
			case 3: $error .= $UNB_T['cp.error.password need number'] . '<br />'; break;
			case 4: $error .= $UNB_T['cp.error.password need special'] . '<br />'; break;
			default: $error .= $UNB_T['cp.error.password generic'] . '<br />'; break;
		}
	}

	if ($_POST['BirthYear'] > 0 && $_POST['BirthYear'] < 100) $_POST['BirthYear'] += 1900;

	if (!UnbMyCheckDate($_POST['BirthDay'], $_POST['BirthMonth'], $_POST['BirthYear']) &&
	    ($_POST['BirthDay'] != '' || $_POST['BirthYear'] != ''))
	{
		$error .= $UNB_T['cp.error.invalid birthdate'] . '<br />';
	}

	if (strlen($_POST['Signature']) > rc('max_sig_len'))
	{
		$error .= str_replace('{max}', rc('max_sig_len'), $UNB_T['cp.error.signature too long']) . '<br />';
	}

	if (!$error)
	{
		$ok = false;
		do
		{
			if (isset($_POST['Username']) &&
			    $_POST['Username'] != $user->GetName() &&
			    UnbCheckRights('renameuser'))
			{
				$newUserName = trim($_POST['Username']);
				if (strpos($newUserName, "\xC2\xA0") !== false)   // UTF-8 for \xA0 is \xC2A0
				{
					$error .= $UNB_T['error.username disallowed'] . '<br />';
				}
				else if (is_numeric($newUserName))
				{
					$error .= $UNB_T['error.username disallowed'] . '<br />';
				}
				if (rc('username_minlength') &&
					strlen($newUserName) < rc('username_minlength'))
				{
					$error .= str_replace('{min}', rc('username_minlength'), $UNB_T['cp.error.username too short']) . '<br />';
					break;
				}
				if (rc('username_maxlength') &&
					strlen($newUserName) > min(rc('username_maxlength'), 40))
				{
					$error .= str_replace('{max}', rc('username_maxlength'), $UNB_T['cp.error.username too long']) . '<br />';
					break;
				}
				// username available?
				$tmpUser = new IUser;
				if ($tmpUser->FindByName($newUserName))
				{
					if ($tmpUser->GetID() != $user->GetID())
					{
						$error .= $UNB_T['error.username assigned'] . '<br />';
						break;
					}
				}

				if (!$user->SetName($newUserName)) break;
				UnbAddLog('change_name for ' . $userid . ' to ' . $newUserName);
			}

			// TODO: http://newsboard.unclassified.de/task/7
			if ($_POST['SetUserGroups'] == 1 &&
			    UnbCheckRights('setusergroups'))
			{
				$allgroups = UnbGetGroupNames();
				$groups = array();
				foreach ($allgroups as $id => $name)
					if ($id != UNB_GROUP_GUESTS)
						if ($_POST['UserGroup' . $id] == 1)
							$groups[] = $id;
				if ($groups != UnbGetUserGroups($userid))
				{
					// an admin can't kick himself out of the admin group!
					if ($userid == $UNB['LoginUserID'] &&
						in_array(UNB_GROUP_ADMINS, $UNB['LoginUserGroups']) &&
						!in_array(UNB_GROUP_ADMINS, $groups))
					{
						$groups[] = UNB_GROUP_ADMINS;
					}

					// if the user should be gmod/admin but taken out of the Members group, put him in again
					if (!in_array(UNB_GROUP_MEMBERS, $groups) &&
						(in_array(UNB_GROUP_MODS, $groups) ||
						 in_array(UNB_GROUP_ADMINS, $groups)))
					{
						$groups[] = UNB_GROUP_MEMBERS;
					}

					// put admins out of the gmods group
					if (in_array(UNB_GROUP_ADMINS, $groups) &&
						in_array(UNB_GROUP_MODS, $groups))
					{
						$key = array_search(UNB_GROUP_MODS, $groups);
						$groups[$key] = 0;   // invalidate the gmod group entry
					}

					if (!UnbSetUserGroups($userid, $groups)) break;

					// Call hook function for all groups to determine leaving/joining a group
					$all_groups = UnbGetGroupNames();
					foreach ($all_groups as $groupid => $group_name)
					{
						$member = in_array($groupid, $groups);

						$data = array(
							'userid' => $userid,
							'groupid' => $groupid,
							'member' => $member);
						UnbCallHook('cp.user.setgroup', $data);
					}
					UnbAddLog('change_groups for ' . $userid . ' to ' . join(', ', $groups));
				}
			}
			elseif ($_POST['SetPublicGroups'] == 1)
			{
				$allgroups = UnbGetGroupNames(false, true);
				$groups = array();
				foreach ($allgroups as $id => $name)
					if ($_POST['UserGroup' . $id] == 1)
						$groups[] = $id;
				if ($groups != UnbGetUserGroups($userid, true))
				{
					foreach ($allgroups as $id => $name)
					{
						$member = $_POST['UserGroup' . $id] == 1;
						if ($member)
							UnbAddUserToGroup($userid, $id);
						else
							UnbRemoveUserFromGroup($userid, $id);

						$data = array(
							'userid' => $userid,
							'groupid' => $id,
							'member' => $member);
						UnbCallHook('cp.user.setgroup', $data);
					}
					UnbAddLog('change_public_groups for ' . $userid . ' to ' . join(', ', $groups));
				}
			}
			if (isset($_POST['NewPassword']) &&
			    $_POST['NewPassword'] != '' &&
			    $_POST['NewPassword'] == $_POST['NewPasswordCfm'])
			{
				if (!UnbCheckUserPassword(md5(trim($_POST['CurrentPassword'])), $user->GetPassword()) &&
				    !UnbCheckRights('is_admin'))
				{
					$error .= $UNB_T['cp.error.invalid password cp'] . '<br />';
					break;
				}

				if (!$user->SetPassword($_POST['NewPassword'])) break;
				UnbAddLog('change_password for ' . $userid);
			}

			if (isset($_POST['EMail']) &&
			    $_POST['EMail'] != $user->GetEMail())
			{
				// e-mail valid?
				if (!is_mailaddr($_POST['EMail']))
				{
					$error .= $UNB_T['error.invalid email'] . '<br />';
				}
				else
				{
					// check for disallowed data
					$disallowed_emails = rc('disallowed_emails', true);
					$allowed_email_domains = rc('allowed_email_domains', true);

					foreach ($disallowed_emails as $d_mail)
					{
						if (!strcasecmp($_POST['EMail'], $d_mail) ||      // entire match
							(substr($d_mail, 0, 1) == '*' ?
								stristr($_POST['EMail'], $d_mail) :       // partial match
								false))
						{
							$error .= $UNB_T['error.email disallowed'] . '<br />';
							break;
						}
					}

					if (sizeof($allowed_email_domains) > 0)
					{
						$n = 0;
						foreach ($allowed_email_domains as $domain)
						{
							if (preg_match("/@$domain$/i", $_POST['EMail'])) $n++;
						}
						if (!$n)
						{
							$error .= $UNB_T['error.email disallowed'] . '<br />';
						}
					}
				}

				// e-mail address available?
				if (rc('disallow_email_reuse'))
				{
					$user2 = new IUser;
					if ($user2->FindByEMail($_POST['EMail']))
					{
						$error .= $UNB_T['error.email assigned'] . '<br />';
					}
				}

				if (!$error &&
				    !UnbCheckUserPassword(md5(trim($_POST['CurrentPassword'])), $user->GetPassword()) &&
				    !UnbCheckRights('is_admin'))
				{
					$error .= $UNB_T['cp.error.invalid password cp'] . '<br />';
				}

				if ($error) break;   // Stop here on previous error

				if (!$user->SetEMail($_POST['EMail'])) break;

				if (rc('new_user_validation') == 1)   // immediate validation
				{
					$user->SetValidatedEMail($_POST['EMail']);
				}
				else   // e-mail confirmation or other option
				{
					// Send validation e-mail to confirm new address
					if (!SendValidationMail($user, true))
					{
						$error .= $UNB_T['error.smtp'] . '<br />';
					}
				}
			}

			if (isset($_POST['SetVCard']) &&
			    $_POST['SetVCard'] == 1)
			{
				$i = 0;
				$e = false;   // no error
				while (!$e && isset($_POST['VCardType' . ++$i]))
				{
					$type = $_POST['VCardType' . $i];
					$data = $_POST['VCardData' . $i];

					if ($type == 'web')
					{
						if (strpos($data, '://') === false && $data != '')
							$data = 'http://' . $data;   // add http:// if missing
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetHomepage())
							if (!$user->SetHomepage($data)) $e = true;   // set error
					}
					elseif ($type == 'jabber')
					{
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetJabber())
							if (!$user->SetIM(null, null, null, null, $data)) $e = true;   // set error
					}
					elseif ($type == 'icq')
					{
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetICQ())
							if (!$user->SetIM($data, null, null, null, null)) $e = true;   // set error
					}
					elseif ($type == 'msn')
					{
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetMSN())
							if (!$user->SetIM(null, null, null, $data, null)) $e = true;   // set error
					}
					elseif ($type == 'yahoo')
					{
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetYIM())
							if (!$user->SetIM(null, null, $data, null, null)) $e = true;   // set error
					}
					elseif ($type == 'aol')
					{
						if (strlen(trim($data)) < 3) $data = '';   // require at least 3 characters
						if ($data != $user->GetAIM())
							if (!$user->SetIM(null, $data, null, null, null)) $e = true;   // set error
					}
				}
				if ($e) break;   // break on error
			}

			if (isset($_POST['Title']) &&
			    $_POST['Title'] != $user->GetTitle())
			{
				if (rc('usertitle_maxlength') &&
				    strlen($_POST['Title']) > rc('usertitle_maxlength'))
				{
					$error .= str_replace('{max}', rc('usertitle_maxlength'), $UNB_T['cp.error.user title too long']);
					break;
				}
				if (!$user->SetTitle($_POST['Title'])) break;
			}
			if (isset($_POST['SetGender']) &&
			    $_POST['SetGender'] == 1 &&
			    $_POST['Gender'] != $user->GetGender() &&
			    !$user->SetGender($_POST['Gender'])) break;
			if (isset($_POST['BirthDay']) &&
			    !$user->SetBirthDate($_POST['BirthDay'], $_POST['BirthMonth'], $_POST['BirthYear'])) break;
			if (isset($_POST['Location']) &&
			    $_POST['Location'] != $user->GetLocation() &&
			    !$user->SetLocation($_POST['Location'])) break;
			if (isset($_POST['Description']) &&
			    $_POST['Description'] != $user->GetAbout() &&
			    !$user->SetAbout($_POST['Description'])) break;

			for ($n = 1; $n <= $UNB['ProfileExtraCount']; $n++)
			{
				if (isset($_POST['Extra' . $n]) &&
				    $_POST['Extra' . $n] != $user->GetExtra($n) &&
				    !$user->SetExtra($n, $_POST['Extra' . $n])) break;
			}

			if (isset($_POST['SetNotify']))
			{
				$DefaultNotify = ($_POST['NotifyEMail']                         ? 1 : 0) * UNB_NOTIFY_EMAIL +
				                 ($_POST['NotifyJabber'] && rc('enable_jabber') ? 1 : 0) * UNB_NOTIFY_JABBER;
				if ($DefaultNotify != $user->GetDefaultNotify() &&
				    !$user->SetDefaultNotify($DefaultNotify)) break;
			}
			if (isset($_POST['Signature']) &&
			    $_POST['Signature'] != $user->GetSignature() &&
			    !$user->SetSignature($_POST['Signature'])) break;
			if (isset($_POST['Design']) &&
			    $_POST['Design'] != $user->GetDesign() &&
			    !$user->SetDesign($_POST['Design'])) break;

			$Flags = $user->GetFlags();
			if ($_POST['SetHalfSizeAvatars'])
			{
				$Flags = ($Flags & ~UNB_USER_HALFSIZEAVATARS) | ($_POST['HalfSizeAvatars'] ? UNB_USER_HALFSIZEAVATARS : 0);
			}
/*			if ($_POST['Set'])
			{
				if (UnbCheckRights('is_admin'))
					$Flags = ($Flags & ~UNB_USER_USERREADPOST) | ($_POST['ShowUserReads'] ? UNB_USER_USERREADPOST : 0);
			}*/
			if ($_POST['SetFastReply'])
			{
				$Flags = ($Flags & ~UNB_USER_FASTREPLY) | ($_POST['FastReply'] ? UNB_USER_FASTREPLY : 0);
			}
			if ($_POST['SetAutoReLogin'])
			{
				if (!rc('no_cookies'))
					$Flags = ($Flags & ~UNB_USER_AUTOLOGIN) | ($_POST['AutoReLogin'] ? UNB_USER_AUTOLOGIN : 0);
			}
			if ($_POST['SetAutoIgnoreTopics'])
			{
				$Flags = ($Flags & ~UNB_USER_AUTOIGNORE) | ($_POST['AutoIgnoreTopics'] ? UNB_USER_AUTOIGNORE : 0);
			}
			if ($_POST['SetHideAvatars'])
			{
				$Flags = ($Flags & ~UNB_USER_HIDEAVATARS) | ($_POST['HideAvatars'] ? UNB_USER_HIDEAVATARS : 0);
			}
			if ($_POST['SetHideSigs'])
			{
				$Flags = ($Flags & ~UNB_USER_HIDESIGS) | ($_POST['HideSigs'] ? UNB_USER_HIDESIGS : 0);
			}
			if ($_POST['SetHideInlineImages'])
			{
				$Flags = ($Flags & ~UNB_USER_HIDEINLINEIMGS) | ($_POST['HideInlineImages'] ? UNB_USER_HIDEINLINEIMGS : 0);
			}
			if ($Flags != $user->GetFlags() &&
				!$user->SetFlags($Flags)) break;

			if (isset($_POST['Language']) &&
			    $_POST['Language'] != $user->GetLanguage() &&
			    !$user->SetLanguage($_POST['Language'])) break;
			if (isset($_POST['Timezone']) &&
			    !$user->SetTimezone($_POST['Timezone'], $_POST['TimezoneDS'])) break;
			if (isset($_POST['DateFormat']) &&
			    $_POST['DateFormat'] != $user->GetDateFormat() &&
			    !$user->SetDateFormat($_POST['DateFormat'])) break;

			$ok = true;
		}
		while (false);

		if ($ok)
		{
			if (UnbCheckRights('changeavatar'))
			{
				$errnum = 0;
				if (isset($_POST['avatar']) && $_POST['avatar'] == 0 && !$user->SetAvatar(''))
				{
					$ok = false;
					$error .= $UNB_T['cp.error.avatar not deleted'] . '<br />';
				}
				if ($_POST['avatar'] == 3 && rc('allow_remote_avatars'))
				{
					if (!$user->SetAvatar(''))
					{
						$ok = false;
						$error .= $UNB_T['cp.error.avatar not deleted'] . '<br />';
					}
					else
					{
						$user->SetAvatar('gravatar');
					}
				}
				if ($_POST['avatar'] == 2 && rc('allow_remote_avatars'))
				{
					if (($errnum = UnbIsValidAvatarSize($_POST['avatarurl'], true)) !== true)
					{
						$ok = false;
					}
					else
					{
						if (!$user->SetAvatar(''))
						{
							$ok = false;
							$error .= $UNB_T['cp.error.avatar not deleted'] . '<br />';
						}
						else
						{
							$user->SetAvatar($_POST['avatarurl']);
						}
					}
				}
				$e = '';
				if ($_POST['avatar'] == 1)
				{
					$name = basename($_FILES['avatarfile']['name']);
					$x = strrpos($name, '.');
					if (($errnum = UnbIsValidAvatarName($name)) !== true)
					{
						$ok = false;
					}
					elseif (($errnum = UnbIsValidAvatarSize($_FILES['avatarfile']['tmp_name'])) !== true)
					{
						if ($errnum == 3 || $errnum == 5 || $errnum == 6)
						{
							// Uploaded avatar image is too big in dimensions, we can scale it down
							if (($e = UnbRescaleAvatar($_FILES['avatarfile']['tmp_name'])) !== true)
							{
								$errornum += 4;
								$ok = false;
							}
						}
						else
						{
							$ok = false;
						}
					}
					if ($ok)
					{
						$ext = substr($name, $x);
						$name = 'avatar_' . $userid . $ext;

						if (!file_exists($UNB['AvatarPath'])) mkdir($UNB['AvatarPath']);
						if (!$user->SetAvatar(''))
						{
							$ok = false;
							$error .= $UNB_T['cp.error.avatar not deleted'] . '<br />';
						}
						else
						{
							if (!move_uploaded_file($_FILES['avatarfile']['tmp_name'], $UNB['AvatarPath'] . $name))
							{
								$ok = false;
								$error .= $UNB_T['cp.error.avatar not saved'] . ' (1)<br />';
							}
							else
							{
								@chmod($UNB['AvatarPath'] . $name, 0666);   // prevent access problems by the webserver
								if (!$user->SetAvatar($name))
								{
									$ok = false;
									$error .= $UNB_T['cp.error.avatar not saved'] . ' (2)<br />';
								}
							}
						}
					}
				}
				if ($errnum)
				{
					switch ($errnum)
					{
						case 1: $a = $UNB_T['cp.error.no dot in filename']; break;
						case 2: $a = $UNB_T['cp.error.invalid file ext']; break;
						case 3: $a = str_replace('{n}', UnbCheckRights('maxavatarsize'), $UNB_T['cp.error.file too big']); break;
						case 4: $a = $UNB_T['cp.error.unknown file format']; break;
						case 5: $a = str_replace('{n}', rc('avatar_x'), $UNB_T['cp.error.image too wide']); break;
						case 6: $a = str_replace('{n}', rc('avatar_y'), $UNB_T['cp.error.image too high']); break;
						case 7: $a = str_replace('{n}', UnbCheckRights('maxavatarsize'), $UNB_T['cp.error.file too big not resized']) . ' (' . $e . ')'; break;
						case 9: $a = str_replace('{n}', rc('avatar_x'), $UNB_T['cp.error.image too wide not resized']) . ' (' . $e . ')'; break;
						case 10: $a = str_replace('{n}', rc('avatar_y'), $UNB_T['cp.error.image too high not resized']) . ' (' . $e . ')'; break;
						default: $a = $UNB_T['cp.error.unknown error'] . ': ' . $errnum;
					}
					$error .= $UNB_T['cp.error.invalid avatar file'] . ' (' . $a . ')<br />';
				}
			}  // UnbCheckRights changeavatar

			$errnum = 0;
			if (isset($_POST['photo']) && $_POST['photo'] == 0) if (!$user->SetPhoto(''))
			{
				$ok = false;
				$error .= $UNB_T['cp.error.photo not deleted'] . '<br />';
			}
			if ($_POST['photo'] == 2 && rc('allow_remote_avatars'))
			{
				if (($errnum = UnbIsValidPhotoSize($_POST['photourl'], true)) !== true)
				{
					$ok = false;
				}
				else
				{
					if (!$user->SetPhoto(''))
					{
						$ok = false;
						$error .= $UNB_T['cp.error.photo not deleted'] . '<br />';
					}
					else
					{
						$user->SetPhoto($_POST['photourl']);
					}
				}
			}
			$e = '';
			if ($_POST['photo'] == 1)
			{
				$name = basename($_FILES['photofile']['name']);
				$x = strrpos($name, '.');
				if (($errnum = UnbIsValidPhotoName($name)) !== true)
				{
					$ok = false;
				}
				elseif (($errnum = UnbIsValidPhotoSize($_FILES['photofile']['tmp_name'])) !== true)
				{
					if ($errnum == 3 || $errnum == 5 || $errnum == 6)
					{
						// Uploaded photo image is too big in dimensions, we can scale it down
						if (($e = UnbRescalePhoto($_FILES['photofile']['tmp_name'])) !== true)
						{
							$errornum += 4;
							$ok = false;
						}
					}
					else
					{
						$ok = false;
					}
				}
				if ($ok)
				{
					$ext = substr($name, $x);
					$name = 'photo_' . $userid . $ext;

					if (!file_exists($UNB['PhotoPath'])) mkdir($UNB['PhotoPath']);
					if (!$user->SetPhoto(''))
					{
						$ok = false;
						$error .= $UNB_T['cp.error.photo not deleted'] . '<br />';
					}
					else
					{
						if (!move_uploaded_file($_FILES['photofile']['tmp_name'], $UNB['PhotoPath'] . $name))
						{
							$ok = false;
							$error .= $UNB_T['cp.error.photo not saved'] . ' (1)<br />';
						}
						else
						{
							@chmod($UNB['PhotoPath'] . $name, 0666);   // prevent access problems by the webserver
							if (!$user->SetPhoto($name))
							{
								$ok = false;
								$error .= $UNB_T['cp.error.photo not saved'] . ' (2)<br />';
							}
						}
					}
				}
			}
			if ($errnum)
			{
				switch ($errnum)
				{
					case 1: $a = $UNB_T['cp.error.no dot in filename']; break;
					case 2: $a = $UNB_T['cp.error.invalid file ext']; break;
					case 3: $a = str_replace('{n}', UnbCheckRights('maxphotosize'), $UNB_T['cp.error.file too big']); break;
					case 4: $a = $UNB_T['cp.error.unknown file format']; break;
					case 5: $a = str_replace('{n}', UnbCheckRights('maxphotowidth'), $UNB_T['cp.error.image too wide']); break;
					case 6: $a = str_replace('{n}', UnbCheckRights('maxphotoheight'), $UNB_T['cp.error.image too high']); break;
					case 7: $a = str_replace('{n}', UnbCheckRights('maxphotosize'), $UNB_T['cp.error.file too big not resized']) . ' (' . $e . ')'; break;
					case 9: $a = str_replace('{n}', UnbCheckRights('maxphotowidth'), $UNB_T['cp.error.image too wide not resized']) . ' (' . $e . ')'; break;
					case 10: $a = str_replace('{n}', UnbCheckRights('maxphotoheight'), $UNB_T['cp.error.image too high not resized']) . ' (' . $e . ')'; break;
					default: $a = $UNB_T['err_unknown_error'] . ': ' . $errnum;
				}
				$error .= $UNB_T['cp.error.invalid photo file'] . ' (' . $a . ')<br />';
			}
		}

		if ($ok)
		{
			UnbAddLog('edit_profile ' . $userid . ' category ' . $cat . ' ok');
			UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat . '&saved=1'));
		}
		else
		{
			UnbAddLog('edit_profile ' . $userid . ' category ' . $cat . ' error');
			$error .= $UNB_T['cp.error.one item not saved'];
			if ($user->db->LastError() != '') $error .= ' (' . t2h($user->db->LastError()) . ')';
			$error .=  '<br />';
		}

		unset($user);
	}
}

// -------------------- Change system configuration --------------------

if ($_POST['action'] == 'edit' &&
    !$_POST['Preview'] &&
    in_array($cat, array('boardconf', 'boardapp', 'security', 'plugins')) &&
    UnbCheckRights('is_admin'))
{
	# ----- board settings -----

	if (isset($_POST['ForumTitle']))
		$UNB['ConfigFile']['forum_title'] = $_POST['ForumTitle'];
	if (isset($_POST['HomeURL']))
		$UNB['ConfigFile']['home_url'] = TrailingSlash($_POST['HomeURL']);
	if (isset($_POST['ParentURL']))
		$UNB['ConfigFile']['parent_url'] = $_POST['ParentURL'];
	if (isset($_POST['ToplogoURL']))
		$UNB['ConfigFile']['toplogo_url'] = $_POST['ToplogoURL'];
	if (isset($_POST['DbServer']))
		$UNB['ConfigFile']['db_server'] = $_POST['DbServer'];
	if (isset($_POST['DbUser']))
		$UNB['ConfigFile']['db_user'] = $_POST['DbUser'];
	if (isset($_POST['DbPass']))
		if (($_POST['DbPass'] == '' || trim($_POST['DbPass']) != ''))
			$UNB['ConfigFile']['db_pass'] = $_POST['DbPass'] ? 'b64:' . base64_encode($_POST['DbPass']) : '';
	if (isset($_POST['DbName']))
		$UNB['ConfigFile']['db_name'] = $_POST['DbName'];
	if (isset($_POST['DbPrefix']))
		$UNB['ConfigFile']['db_prefix'] = $_POST['DbPrefix'];
	if (isset($_POST['SmtpServer']))
		$UNB['ConfigFile']['smtp_server'] = $_POST['SmtpServer'];
	if (isset($_POST['SmtpSender']))
		$UNB['ConfigFile']['smtp_sender'] = $_POST['SmtpSender'];
	if (isset($_POST['SmtpUser']))
		$UNB['ConfigFile']['smtp_user'] = $_POST['SmtpUser'];
	if (isset($_POST['SmtpPass']))
		if (($_POST['SmtpPass'] == '' || trim($_POST['SmtpPass']) != ''))
			$UNB['ConfigFile']['smtp_pass'] = $_POST['SmtpPass'] ? 'b64:' . base64_encode($_POST['SmtpPass']) : '';
	if (isset($_POST['SetUsePHPMail']))
		$UNB['ConfigFile']['use_php_mail'] = $_POST['UsePHPMail'] ? 1 : 0;
	if (isset($_POST['JabberServer']))
		$UNB['ConfigFile']['jabber_server'] = $_POST['JabberServer'];
	if (isset($_POST['JabberUser']))
		$UNB['ConfigFile']['jabber_user'] = $_POST['JabberUser'];
	if (isset($_POST['JabberPass']))
		if (($_POST['JabberPass'] == '' || trim($_POST['JabberPass']) != ''))
			$UNB['ConfigFile']['jabber_pass'] = $_POST['JabberPass'] ? 'b64:' . base64_encode($_POST['JabberPass']) : '';
	if (isset($_POST['SetEnableJabber']))
		$UNB['ConfigFile']['enable_jabber'] = $_POST['EnableJabber'] ? 1 : 0;

	if (isset($_POST['Language']))
		$UNB['ConfigFile']['def_lang'] = $_POST['Language'];
	if (isset($_POST['Timezone']))
		$UNB['ConfigFile']['tz_offset'] = $_POST['Timezone'];
	if (isset($_POST['SetTimezoneDS']))
		$UNB['ConfigFile']['tz_dst'] = $_POST['TimezoneDS'];

	# ----- board appearance -----

	if (isset($_POST['Design']))
		$UNB['ConfigFile']['design'] = $_POST['Design'];
	if (isset($_POST['SmileSet']))
		$UNB['ConfigFile']['smileset'] = $_POST['SmileSet'];

	if (isset($_POST['SetLoginTop']))
		$UNB['ConfigFile']['login_top'] = $_POST['LoginTop'] ? 1 : 0;
	if (isset($_POST['SetShowOnlineUsers']))
		$UNB['ConfigFile']['show_online_users'] = $_POST['ShowOnlineUsers'] ? 1 : 0;
	if (isset($_POST['SetFootDbTime']))
		$UNB['ConfigFile']['foot_db_time'] = $_POST['FootDbTime'] ? 1 : 0;
	if (isset($_POST['GZip']))
		$UNB['ConfigFile']['gzip'] = $_POST['GZip'];
	if (isset($_POST['SetModRewriteUrls']))
		$UNB['ConfigFile']['mod_rewrite_urls'] = $_POST['ModRewriteUrls'] ? 1 : 0;
	if (isset($_POST['SetShowGotoForum']))
		$UNB['ConfigFile']['show_goto_forum'] = $_POST['ShowGotoForum'] ? 1 : 0;
	if (isset($_POST['SetShowSearchForum']))
		$UNB['ConfigFile']['show_search_forum'] = $_POST['ShowSearchForum'] ? 1 : 0;
	if (isset($_POST['SetEnableTraceUsers']))
		$UNB['ConfigFile']['enable_trace_users'] = $_POST['EnableTraceUsers'] ? 1 : 0;
	if (isset($_POST['SetPostPreviewSendButton']))
		$UNB['ConfigFile']['post_preview_send_button'] = $_POST['PostPreviewSendButton'] ? 1 : 0;
	if (isset($_POST['SetShowLastVisitTime']))
		$UNB['ConfigFile']['show_last_visit_time'] = $_POST['ShowLastVisitTime'] ? 1 : 0;
	if (isset($_POST['ForumsTreeStyle']))
		$UNB['ConfigFile']['forums_tree_style'] = $_POST['ForumsTreeStyle'];
	if (isset($_POST['SetDisplayForumLastpostRe']))
		$UNB['ConfigFile']['display_forum_lastpost_re'] = $_POST['DisplayForumLastpostRe'] ? 1 : 0;
	if (isset($_POST['SetShowBirthdays']))
		$UNB['ConfigFile']['show_birthdays'] = $_POST['ShowBirthdays'] ? 1 : 0;
	if (isset($_POST['SetDisableSearchHighlighting']))
		$UNB['ConfigFile']['disable_search_highlighting'] = $_POST['DisableSearchHighlighting'] ? 1 : 0;
	if (isset($_POST['SetShowForumRssLink']))
		$UNB['ConfigFile']['show_forum_rss_link'] = $_POST['ShowForumRssLink'] ? 1 : 0;
	if (isset($_POST['LocationLink']))
		$UNB['ConfigFile']['location_link'] = $_POST['LocationLink'];
	if (isset($_POST['ExtraNames']))
	{
		$n = sizeof(explode('|', $_POST['ExtraNames']));
		if ($n > 10)
		{
			$error .= $UNB_T['cp.error.too many extra fields'] . '<br />';
		}
		elseif (!UnbSetProfileExtraDBCols($n))
		{
			$error .= $UNB_T['cp.error.db setextracols'] . t2h($UNB['Db']->LastError()) . '<br />';
		}
		else
		{
			$l = array_map('trim', explode('|', $_POST['ExtraNames']));
			$UNB['ConfigFile']['extra_names'] = join(' | ', $l);
		}
	}

	if (isset($_POST['ThreadsPerPage']))
		$UNB['ConfigFile']['threads_per_page'] = intval($_POST['ThreadsPerPage']);
	if (isset($_POST['PostsPerPage']))
		$UNB['ConfigFile']['posts_per_page'] = intval($_POST['PostsPerPage']);
	if (isset($_POST['UsersPerPage']))
		$UNB['ConfigFile']['users_per_page'] = intval($_POST['UsersPerPage']);
	if (isset($_POST['HotThreadsPosts']))
		$UNB['ConfigFile']['hot_thread_posts'] = intval($_POST['HotThreadsPosts']);
	if (isset($_POST['HotThreadsViews']))
		$UNB['ConfigFile']['hot_thread_views'] = intval($_POST['HotThreadsViews']);

	if (isset($_POST['SetNewTopicLinkInThread']))
		$UNB['ConfigFile']['new_topic_link_in_thread'] = $_POST['NewTopicLinkInThread'] ? 1 : 0;
	if (isset($_POST['PostAttachInlineMaxsize']))
		$UNB['ConfigFile']['post_attach_inline_maxsize'] = intval($_POST['PostAttachInlineMaxsize']);
	if (isset($_POST['PostAttachInlineMaxwidth']))
		$UNB['ConfigFile']['post_attach_inline_maxwidth'] = intval($_POST['PostAttachInlineMaxwidth']);
	if (isset($_POST['PostAttachInlineMaxheight']))
		$UNB['ConfigFile']['post_attach_inline_maxheight'] = intval($_POST['PostAttachInlineMaxheight']);
	if (isset($_POST['SetPostShowTextlength']))
		$UNB['ConfigFile']['post_show_textlength'] = $_POST['PostShowTextlength'] ? 1 : 0;
	if (isset($_POST['MaxPollOptions']))
		$UNB['ConfigFile']['max_poll_options'] = intval($_POST['MaxPollOptions']);

	if (isset($_POST['SetOwnPostsInThreadlist']))
		$UNB['ConfigFile']['own_posts_in_threadlist'] = $_POST['OwnPostsInThreadlist'] ? 1 : 0;
	if (isset($_POST['SetShowBookmarkedThread']))
		$UNB['ConfigFile']['show_bookmarked_thread'] = $_POST['ShowBookmarkedThread'] ? 1 : 0;
	if (isset($_POST['SetDisplayThreadStartdate']))
		$UNB['ConfigFile']['display_thread_startdate'] = $_POST['DisplayThreadStartdate'] ? 1 : 0;
	if (isset($_POST['SetAdvancedThreadCounter']))
		$UNB['ConfigFile']['advanced_thread_counter'] = $_POST['AdvancedThreadCounter'] ? 1 : 0;
	if (isset($_POST['SetCountThreadViews']))
		$UNB['ConfigFile']['count_thread_views'] = $_POST['CountThreadViews'] ? 1 : 0;
	if (isset($_POST['SetDisplayThreadLastposter']))
		$UNB['ConfigFile']['display_thread_lastposter'] = $_POST['DisplayThreadLastposter'] ? 1 : 0;
	if (isset($_POST['SetCountForumThreadsPosts']))
		$UNB['ConfigFile']['count_forum_threads_posts'] = $_POST['CountForumThreadsPosts'] ? 1 : 0;
	if (isset($_POST['SetDisplayForumLastpost']))
		$UNB['ConfigFile']['display_forum_lastpost'] = $_POST['DisplayForumLastpost'] ? 1 : 0;

	if (isset($_POST['SetUlistRegdate']))
		$UNB['ConfigFile']['ulist_regdate'] = $_POST['UlistRegdate'] ? 1 : 0;
	if (isset($_POST['SetUlistLocation']))
		$UNB['ConfigFile']['ulist_location'] = $_POST['UlistLocation'] ? 1 : 0;
	if (isset($_POST['SetUlistPosts']))
		$UNB['ConfigFile']['ulist_posts'] = $_POST['UlistPosts'] ? 1 : 0;
	if (isset($_POST['SetUlistLastpost']))
		$UNB['ConfigFile']['ulist_lastpost'] = $_POST['UlistLastpost'] ? 1 : 0;

	if (isset($_POST['PollCurrentDays']))
		$UNB['ConfigFile']['poll_current_days'] = intval($_POST['PollCurrentDays']);
	if (isset($_POST['QuoteWithDate']))
		$UNB['ConfigFile']['quote_with_date'] = intval($_POST['QuoteWithDate']);
	if (isset($_POST['NoEditNoteGraceTime']))
		$UNB['ConfigFile']['no_edit_note_grace_time'] = intval($_POST['NoEditNoteGraceTime']);
	if (isset($_POST['MovedThreadNoteTimeout']))
		$UNB['ConfigFile']['moved_thread_note_timeout'] = intval($_POST['MovedThreadNoteTimeout']);
	if (isset($_POST['OnlineUsersReloadInterval']))
		$UNB['ConfigFile']['online_users_reload_interval'] = intval($_POST['OnlineUsersReloadInterval']);
	if (isset($_POST['UserOnlineTimeout']))
		$UNB['ConfigFile']['user_online_timeout'] = intval($_POST['UserOnlineTimeout']);

	# ----- security -----

	if (isset($_POST['NewUserValidation']))
		$UNB['ConfigFile']['new_user_validation'] = intval($_POST['NewUserValidation']);
	if (isset($_POST['DisallowedUsernames']))
		$UNB['ConfigFile']['disallowed_usernames'] = $_POST['DisallowedUsernames'];
	if (isset($_POST['DisallowedEmails']))
		$UNB['ConfigFile']['disallowed_emails'] = $_POST['DisallowedEmails'];
	if (isset($_POST['AllowedEmailDomains']))
		$UNB['ConfigFile']['allowed_email_domains'] = $_POST['AllowedEmailDomains'];
	if (isset($_POST['SetDisallowEmailReuse']))
		$UNB['ConfigFile']['disallow_email_reuse'] = $_POST['DisallowEmailReuse'] ? 1 : 0;
	if (isset($_POST['UsernameMinlength']))
		$UNB['ConfigFile']['username_minlength'] = intval($_POST['UsernameMinlength']);
	if (isset($_POST['UsernameMaxlength']))
		$UNB['ConfigFile']['username_maxlength'] = intval($_POST['UsernameMaxlength']);
	if (isset($_POST['UsertitleMaxlength']))
		$UNB['ConfigFile']['usertitle_maxlength'] = intval($_POST['UsertitleMaxlength']);
	if (isset($_POST['PassMinlength']))
		$UNB['ConfigFile']['pass_minlength'] = intval($_POST['PassMinlength']);
	if (isset($_POST['SetPassNotusername']))
		$UNB['ConfigFile']['pass_notusername'] = $_POST['PassNotusername'] ? 1 : 0;
	if (isset($_POST['SetPassNeednumber']))
		$UNB['ConfigFile']['pass_neednumber'] = $_POST['PassNeednumber'] ? 1 : 0;
	if (isset($_POST['SetPassNeedspecial']))
		$UNB['ConfigFile']['pass_needspecial'] = $_POST['PassNeedspecial'] ? 1 : 0;

	if (isset($_POST['SetAvatarsEnabled']))
		$UNB['ConfigFile']['avatars_enabled'] = $_POST['AvatarsEnabled'] ? 1 : 0;
	if (isset($_POST['SetAllowRemoteAvatars']))
		$UNB['ConfigFile']['allow_remote_avatars'] = $_POST['AllowRemoteAvatars'] ? 1 : 0;
	if (isset($_POST['AvatarX']))
		$UNB['ConfigFile']['avatar_x'] = intval($_POST['AvatarX']);
	if (isset($_POST['AvatarY']))
		$UNB['ConfigFile']['avatar_y'] = intval($_POST['AvatarY']);
	if (isset($_POST['AvatarBytes']))
		$UNB['ConfigFile']['avatar_bytes'] = intval($_POST['AvatarBytes']);
	if (isset($_POST['SetPhotosEnabled']))
		$UNB['ConfigFile']['photos_enabled'] = $_POST['PhotosEnabled'] ? 1 : 0;
	if (isset($_POST['PhotoX']))
		$UNB['ConfigFile']['photo_x'] = intval($_POST['PhotoX']);
	if (isset($_POST['PhotoY']))
		$UNB['ConfigFile']['photo_y'] = intval($_POST['PhotoY']);
	if (isset($_POST['PhotoBytes']))
		$UNB['ConfigFile']['photo_bytes'] = intval($_POST['PhotoBytes']);

	if (isset($_POST['MaxPostLen']))
		$UNB['ConfigFile']['max_post_len'] = intval($_POST['MaxPostLen']);
	if (isset($_POST['MaxSigLen']))
		$UNB['ConfigFile']['max_sig_len'] = intval($_POST['MaxSigLen']);
	if (isset($_POST['AttachBytes']))
		$UNB['ConfigFile']['attach_bytes'] = intval($_POST['AttachBytes']);
	if (isset($_POST['AttachExts']))
		$UNB['ConfigFile']['attach_exts'] = $_POST['AttachExts'];
	if (isset($_POST['TopicSubjectMinlength']))
		$UNB['ConfigFile']['topic_subject_minlength'] = intval($_POST['TopicSubjectMinlength']);
	if (isset($_POST['TopicSubjectMaxlength']))
		$UNB['ConfigFile']['topic_subject_maxlength'] = intval($_POST['TopicSubjectMaxlength']);
	if (isset($_POST['SetAbbcSigNoFont']))
		$UNB['ConfigFile']['abbc_sig_no_font'] = $_POST['AbbcSigNoFont'] ? 1 : 0;
	if (isset($_POST['SetAbbcSigNoUrl']))
		$UNB['ConfigFile']['abbc_sig_no_url'] = $_POST['AbbcSigNoUrl'] ? 1 : 0;
	if (isset($_POST['SetAbbcSigNoImg']))
		$UNB['ConfigFile']['abbc_sig_no_img'] = $_POST['AbbcSigNoImg'] ? 1 : 0;
	if (isset($_POST['SetAbbcSigNoSmilies']))
		$UNB['ConfigFile']['abbc_sig_no_smilies'] = $_POST['AbbcSigNoSmilies'] ? 1 : 0;
	if (isset($_POST['SetNoCookies']))
		$UNB['ConfigFile']['no_cookies'] = $_POST['NoCookies'] ? 1 : 0;
	if (isset($_POST['SessionIpNetmask']))
		$UNB['ConfigFile']['session_ip_netmask'] = intval($_POST['SessionIpNetmask']);
	if (isset($_POST['SetUseVeriword']))
		$UNB['ConfigFile']['use_veriword'] = $_POST['UseVeriword'] ? 1 : 0;
	if (isset($_POST['SetAutoBanFloodIp']))
		$UNB['ConfigFile']['auto_ban_flood_ip'] = $_POST['AutoBanFloodIp'] ? 1 : 0;
	if (isset($_POST['AutoBanFloodIpPeriod']))
		$UNB['ConfigFile']['auto_ban_flood_ip_period'] = intval($_POST['AutoBanFloodIpPeriod']);
	if (isset($_POST['AutoBanFloodIpThreshold']))
		$UNB['ConfigFile']['auto_ban_flood_ip_threshold'] = intval($_POST['AutoBanFloodIpThreshold']);
	if (isset($_POST['SetAdminLock']))
		$UNB['ConfigFile']['admin_lock'] = $_POST['AdminLock'] ? 1 : 0;
	if (isset($_POST['AdminLockMessage']))
	{
		$announce = new IAnnounce;
		if ($announce->Find(-1)) $announce->Remove();
		if (trim($_POST['AdminLockMessage']) != '')
		{
			$announce->Add(-1, '', ltrimln($_POST['AdminLockMessage']), 0);
		}
	}
	if (isset($_POST['SetReadOnly']))
		$UNB['ConfigFile']['read_only'] = $_POST['ReadOnly'] ? 1 : 0;
	if (isset($_POST['SetEnableVersioncheck']))
		$UNB['ConfigFile']['enable_versioncheck'] = $_POST['EnableVersioncheck'] ? 1 : 0;

	if (!$error && !UnbRebuildConffile()) $error .= $UNB_T['error.write conffile'] . '<br />';

	if (!$error)
	{
		UnbAddLog('change_config category ' . $cat);
		UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat . '&saved=1'));
	}
}

// -------------------- Edit plug-in configuration --------------------

if ($_POST['action'] == 'pluginconfig' &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$name = trim($_REQUEST['pluginname']);
	if (array_key_exists($name, $UNB['PlugIns']))
	{
		$plug = $UNB['PlugIns'][$name];
		$confighandler = $plug['config'];

		// Default setting: enable this plugin?
		if (isset($_POST['SetEnableThisPlugin']))
		{
			// update the list of DISABLED plug-ins
			$newlist = rc('disable_plugins', true);
			if ($_POST['EnableThisPlugin'] && in_array($name, $newlist))
			{
				// is to enable
				$key = array_search($name, $newlist);
				if (isset($key) && $key !== false) $newlist[$key] = null;
			}
			if (!$_POST['EnableThisPlugin'] && !in_array($name, $newlist))
			{
				// is to disable
				$newlist[] = $name;
			}
			$a = '';
			foreach ($newlist as $n)
			{
				if ($n) $a .= ($a ? ' | ' : '') . trim($n);
			}
			$UNB['ConfigFile']['disable_plugins'] = $a;
		}

		// now call the plug-in-specific config form handler, if present
		if (function_exists($confighandler))
		{
			$result = null;
			$errormsg = null;

			$data = array(
				'request' => 'handleform',
				'result' => &$result,
				'errormsg' => &$errormsg);
			$confighandler($data);
			if (!$result) $error .= $errormsg;
		}

		// write back the configuration file, the plug-in doesn't have to do this
		if (!$error && !UnbRebuildConffile()) $error .= $UNB_T['error.write conffile'] . '<br />';

		if (!$error)
		{
			UnbAddLog('config_plugin ' . $name);
			UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&cat=' . $cat . '&saved=1'));
		}
	}
}

// -------------------- Send e-mail to user --------------------

if ($_POST['action'] == 'sendemail' &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	if (!UnbCheckRights('sendemail'))
	{
		$error .= $UNB_T['error.access denied'] . '<br />';
	}

	if (($_POST['id'] == '' || trim($_POST['Msg']) == ''))
	{
		$error .= $UNB_T['cp.error.form not complete'] . '<br />';
	}

	$user = new IUser;
	if (!$user->Load($userid))
	{
		$error .= $UNB_T['error.invalid user'] . '<br />';
	}

	if ($user->GetEMail() == '' ||
	    (!in_array(UNB_GROUP_MEMBERS, UnbGetUserGroups($userid)) && !UnbCheckRights('is_admin')))
	{
		$error .= $UNB_T['cp.error.user has no email'] . '<br />';
	}

	if (!$error)
	{
		$subject_key = 'mail.usermail.subject';
		$subject_data = array('{name}' => $UNB['LoginUserName']);

		$msg_data = array(
			'{name}' => $user->GetName(),
			'{poster}' => $UNB['LoginUserName'],
			'{msg}' => trim($_POST['Msg']),
			'{email}' => $UNB['LoginUser']->GetEMail(),
			'{url}' => TrailingSlash(rc('home_url')) . UnbLink('@cp', 'id=' . $UNB['LoginUserID'] . '&action=email', false, /*sid*/ false)
			);

		if ($_POST['reply_email'] == 1)
		{
			$msg_key = 'mail.usermail.body1';
			$from = $UNB['LoginUser']->GetEMail();
		}
		else
		{
			$msg_key = 'mail.usermail.body2';
			$from = false;
		}

		if (UnbNotifyUser($userid, 1, $subject_key, $subject_data, $msg_key, $msg_data, $from))
		{
			UnbAddLog("From: $UNB[LoginUserName] ($UNB[LoginUserID])" . PHP_EOL .
				"To: " . $user->GetName() . " ($userid)" . PHP_EOL .
				"Date: " . date("D, d.m.Y, H:i:s") . PHP_EOL .
				($_POST['reply_email'] == 1 ? "Reply-to: $from" . PHP_EOL : "") .
				PHP_EOL .
				trim($_POST['Msg']),
				true);
			UnbAddLog('email_user ' . $userid . ' ok');
			UnbForwardHTML(UnbLink('@this', 'id=' . $userid . '&action=emailsuccess'));
		}
		else
		{
			UnbAddLog('email_user ' . $userid . ' error');
			$error .= $UNB_T['cp.error.message not sent'] . '<br />';
			#if ($mail_error != '') $error .= t2h(rtrim($mail_error), 1, 1, 1) . '<br />';
		}

		unset($user);
	}
}

// Calculate a User's age from his birthday [for use in ShowProfile]
//
// returns (int) user's age in full years
//
function UnbAgeFromBirthday($day, $month, $year)
{
	$age = date('Y') - $year;

	if ($month < date('m')) return $age;
	if ($month > date('m')) return $age - 1;
	if ($day <= date('d')) return $age;
	return $age - 1;
}

// Show User Profile for userid
//
function ShowProfile($userid)
{
	global $ABBC, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$userid = intval($userid);

	UnbCountUserPosts();

	$thread = new IThread;
	$forum = new IForum;

	$user = new IUser;
	if (!$user->Load($userid)) return false;

	$TP['userprofileShowProfile'] = true;

	// ---------- 1ST BLOCK ----------
	if ($user->GetPhoto() != '' && rc('photos_enabled'))
	{
		$TP['userprofilePhotoUrl'] = t2i(UnbPhotoUrl($user));
	}
	$TP['userprofileOnlineImage'] = UnbGetUserOnlineImg($user->GetOnline());
	$TP['userprofileName'] = t2h($user->GetName());
	if (UnbCheckRights('editprofile', 0, 0, $userid))
		$TP['userprofileEditLink'] = UnbLink('@this', 'id=' . $userid . '&cat=summary', true);

	// ---------- 2ND BLOCK ----------
	// REG DATE, E-MAIL/MESSENGER
	$TP['userprofileRegDate'] = UnbFriendlyDate($user->GetRegDate(), 1);

	$contacts = array();
	if ($user->GetEMail() != '')
	{
		$contacts[] = array(
			'type' => 'email',
			'title' => $UNB_T['e-mail'],
			'image' => '<img ' . $UNB['Image']['email'] . ' />',
			'link' => UnbLink('@this', 'id=' . $userid . '&action=email', true),
			'value' => str_replace('{x}', t2h($user->GetName()), $UNB_T['send mail to x']));
	}
	if ($user->GetJabber() != '')
	{
		$contacts[] = array(
			'type' => 'jabber',
			'title' => $UNB_T['jabber'],
			'image' => '<img ' . $UNB['Image']['jabber'] . ' />',
			'link' => 'xmpp:' . UnbMaskMail(t2i($user->GetJabber())),
			'value' => UnbMaskMail(t2h($user->GetJabber())));
	}
	if ($user->GetICQ() != '')
	{
		$contacts[] = array(
			'type' => 'icq',
			'title' => $UNB_T['icq'],
			'image' => '<img ' . $UNB['Image']['icq'] . ' />',
			'link' => UnbLink('http://web.icq.com/wwp/' . $user->GetICQ(), null, true, /*sid*/ false, /*derefer*/ true),
			'value' => t2h($user->GetICQ()));
	}
	if ($user->GetAIM() != '')
	{
		$contacts[] = array(
			'type' => 'aol',
			'title' => $UNB_T['aim'],
			'image' => '<img ' . $UNB['Image']['aim'] . ' />',
			'link' => '',
			'value' => t2h($user->GetAIM()));
	}
	if ($user->GetYIM() != '')
	{
		$contacts[] = array(
			'type' => 'yahoo',
			'title' => $UNB_T['yim'],
			'image' => '<img ' . $UNB['Image']['yim'] . ' />',
			'link' => '',
			'value' => t2h($user->GetYIM()));
	}
	if ($user->GetMSN() != '')
	{
		$contacts[] = array(
			'type' => 'msn',
			'title' => $UNB_T['msn'],
			'image' => '<img ' . $UNB['Image']['msn'] . ' />',
			'link' => 'mailto:' . UnbMaskMail(t2h($user->GetMSN())),
			'value' => UnbMaskMail(t2h($user->GetMSN())));
	}
	$TP['userprofileContacts'] = $contacts;

	// POSTS
	$post_count = UnbGetPostsByUser($userid);
	$day_count = (time() - $user->GetRegDate()) / 3600 / 24;

	$post = new IPost;
	if ($post->Find('User=' . $userid, 'Date ASC', 1))
	{
		$date0 = $post->GetDate();
		$post->Find('User=' . $userid, 'Date DESC', 1);
		$date1 = $post->GetDate();
		$day_count_eff = ($date1 - $date0) / 3600 / 24;
	}
	else
		$day_count_eff = 0;

	if ($post_count)
	{
		$out = $post_count . ' &nbsp; <small>(';

		if ($post_count / $day_count >= 1)
			$out .= format_number($post_count / $day_count, 1) . ' ' . $UNB_T['per day'];
		else
			$out .= format_number($post_count / $day_count * 7, 1) . ' ' . $UNB_T['per week'];

		// only show effective value if it's more than 1/3 greater then real value
		if ($day_count_eff &&
		    abs(1 - ($post_count / $day_count_eff) / ($post_count / $day_count)) > 0.3)
		{
			$out .= ', ' . $UNB_T['effective'] . ' ';
			if ($post_count / $day_count_eff >= 1)
				$out .= format_number($post_count / $day_count_eff, 1) . ' ' . $UNB_T['per day'];
			else
				$out .= format_number($post_count / $day_count_eff * 7, 1) . ' ' . $UNB_T['per week'];
		}
		$out .= ')</small>';

		$out .= ' &nbsp; <img ' . $UNB['Image']['search'] . ' /> ';
		$out .= '<a href="' . UnbLink('@search', 'nodef=1&Query=' . $userid . '&ResultView=2&InUser=1&Sort=2', true) . '">' . $UNB_T['profile.find posts'] . '</a>';

		$TP['userprofilePostCount'] = $out;
	}
	else
	{
		$TP['userprofilePostCount'] = $UNB_T['none'];
	}

	// LAST ACTIVITY
	if ($user->GetLastActivity())
	{
		$out = UnbFormatTime($user->GetLastActivity(), 3);

		if ($user->GetLastLogin())
		{
			$out .= ' &nbsp; <small>(' . $UNB_T['online'] . ' ' . UnbFriendlyDate($user->GetLastActivity() - $user->GetLastLogin(), 3, 6, false, 2) . ')</small>';
		}

		if ($user->GetLastForum() == 0)
		{
			$out .= '<br />' . $UNB_T['in.overview'];
		}
		elseif ($user->GetLastForum() < 0) switch ($user->GetLastForum())
		{
			case UNB_ULF_CONFIG: $out .= '<br />' . $UNB_T['in.config']; break;
			case UNB_ULF_PROFILE: $out .= '<br />' . $UNB_T['in.user profile']; break;
			case UNB_ULF_SEARCH: $out .= '<br />' . $UNB_T['in.search']; break;
			case UNB_ULF_STAT: $out .= '<br />' . $UNB_T['in.statistics']; break;
			case UNB_ULF_USERS: $out .= '<br />' . $UNB_T['in.userlist']; break;
		}
		elseif ($forum->Load($user->GetLastForum()))
		{
			$out .= '<br />' . $UNB_T['in.forum'] . ' <a href="' . UnbLink('@main', 'id=' . $forum->GetID(), true) . '">' . t2h($forum->GetName()) . '</a>';
		}
		$TP['userprofileLastActivity'] = $out;
	}
	else
	{
		$TP['userprofileLastActivity'] = $UNB_T['never'];
	}

	// LAST POST
	$record = UnbGetLastPost('User=' . $userid);
	if ($record)
	{
		$thread->Load($record['Thread']);
		$forum->Load($thread->GetForum());

		$out = UnbFriendlyDate($record['Date'], 1, 3) . ' &nbsp; ' . UnbMakePostLink($record) . '<br />';
		$out .= $UNB_T['in.thread'] . ' <a href="' . UnbLink('@thread', 'id=' . $record['Thread'], true) . '">' . t2h($thread->GetSubject()) . '</a><br />';
		$out .= $UNB_T['in.forum'] . ' <a href="' . UnbLink('@main', 'id=' . $forum->GetID(), true) . '">' . t2h($forum->GetName()) . '</a>';
		$TP['userprofileLastPost'] = $out;
	}
	else
	{
		$TP['userprofileLastPost'] = '';
	}

	// ---------- 3RD BLOCK ----------
	// HOMEPAGE, GROUPS
	if ($user->GetHomepage() != '')
	{
		$wbr = '&#x200B;';   // the new XHTML way with Unicode - not supported by Internet Explorer...
		if ($UNB['Client']['b_class'] == 'ie')
			$wbr = '<wbr />';   // the old HTML way

		$TP['userprofileHomepage'] = '<a href="' . UnbLink($user->GetHomepage(), null, true, /*sid*/ false, /*derefer*/ true) . '">' . str_replace('/', '/' . $wbr, t2h($user->GetHomepage())) . '</a>';
	}
	else
		$TP['userprofileHomepage'] = '';

	$groups = UnbGetUserGroupNames($userid);
	$out = '';
	if ($groups) foreach ($groups as $g) $out .= t2h($g) . '<br />';
	else $out .= '(' . $UNB_T['none'] . ')';
	$TP['userprofileGroups'] = $out;

	// LOCATION
	if ($user->GetLocation())
	{
		$out = '';
		if (rc('location_link'))
			$out .= '<a href="' . UnbLink(str_replace('%s', $user->GetLocation(), rc('location_link')), null, true, /*sid*/ false, /*derefer*/ true) . '">';
		$out .= t2h($user->GetLocation());
		if (rc('location_link'))
			$out .= '</a>';
		$TP['userprofileLocation'] = $out;
	}

	// BIRTHDAY, TITLE
	if ($user->GetBirthDay() > 0)
	{
		$btime = array('year' => $user->GetBirthYear(),
			'month' => $user->GetBirthMonth(),
			'day' => $user->GetBirthDay());
		$out = UnbDate($UNB_T['dateformat.short'], $btime);
		$years = UnbAgeFromBirthday($user->GetBirthDay(), $user->GetBirthMonth(), $user->GetBirthYear());
		$out .= ' &nbsp; <small>(' . $years . ' ' . UteTranslateNum('years', $years) . ')</small>';
		$TP['userprofileBirthdate'] = $out;
	}
	else
		$TP['userprofileBirthdate'] = '';

	$TP['userprofileTitle'] = t2h($user->GetTitle());

	// GENDER
	$out = '';
	$im = UnbGetGenderImage($user->GetGender());
	if ($im != '') $out .= $im . '&nbsp; ';
	$out .= t2h($user->GetGenderVerbose());
	$TP['userprofileGender'] = $out;

	// ABOUT, AVATAR
	$TP['userprofileAbout'] = t2h($user->GetAbout(), true, true, true);

	if ($user->GetAvatar() != '' && rc('avatars_enabled'))
	{
		$TP['userprofileAvatarUrl'] = t2i(UnbAvatarUrl($user));
		$TP['userprofileAvatarSize'] = UnbAvatarSize($user, false);
	}

	// EXTRA, SIGNATURE
	if ($UNB['ProfileExtraCount'] || $user->GetSignature())
	{
		if ($UNB['ProfileExtraCount'] > 0)
		{
			$extra = array();
			$count = 0;
			for ($n = 1; $n <= $UNB['ProfileExtraCount']; $n++)
			{
				$extra[] = array(
					'name' => UnbGetProfileExtraName($n),
					'value' => t2h($user->GetExtra($n)));
				if ($user->GetExtra($n)) $count++;
			}
			$TP['userprofileExtra'] = $extra;
			$TP['userprofileExtraCount'] = $count;
		}
		else
		{
			$TP['userprofileExtra'] = '';
		}

		// limit ABBC subset for signatures
		$subsets0 = $ABBC['Config']['subsets'];
		$ABBC['Config']['subsets'] &= ~(ABBC_CODE | ABBC_QUOTE | ABBC_LIST);   // no code/quotes/lists at all
		if (rc('abbc_sig_no_font')) $ABBC['Config']['subsets'] &= ~ABBC_FONT;
		if (rc('abbc_sig_no_url')) $ABBC['Config']['subsets'] &= ~ABBC_URL;
		if (rc('abbc_sig_no_img')) $ABBC['Config']['subsets'] &= ~ABBC_IMG;
		if (rc('abbc_sig_no_smilies')) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;
		if ($UNB['TextOnly']) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;   // no smilies for text-only mode

		if ($user->GetSignature())
			$TP['userprofileSignature'] = AbbcProc($user->GetSignature());

		// restore default subset
		$ABBC['Config']['subsets'] = $subsets0;
	}

	return true;
}

// Show form to send an e-mail to a user
//
function EMailForm($userid)
{
	global $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	$TP['headNoIndex'] = true;

	if (!UnbCheckRights('sendemail'))
	{
		$TP['errorMsg'] .= $UNB_T['error.access denied'] . '<br />';
		return false;
	}

	// Clean parameters
	$userid = intval($userid);

	$user = new IUser;
	if (!$user->Load($userid)) return false;

	if ($user->GetEMail() == '' ||
	    (!in_array(UNB_GROUP_MEMBERS, UnbGetUserGroups($userid)) && !UnbCheckRights('is_admin')))
	{
		$TP['errorMsg'] .= $UNB_T['cp.error.user has no email'] . '<br />';
		return false;
	}

	$TP['userprofileShowEMail'] = true;

	$TP['userprofileFormLink'] = UnbLink('@this', 'id=' . $userid, true);
	$TP['userprofileUserId'] = $userid;
	$TP['userprofileRcptName'] = UnbMakeUserLink($user->GetID(), $user->GetName());
	$TP['userprofileRcptOnlineImg'] = UnbGetUserOnlineImg($user->GetOnline());
	$TP['userprofileMsgInput'] = t2h($_POST['Msg']);

	return true;
}

// Display user control panel
//
function CPForm($userid, $cat = 1)
{
	global $ABBC, $error, $UNB, $UNB_T;
	$TP =& $UNB['TP'];

	// Clean parameters
	$userid = intval($userid);

	$TP['headNoIndex'] = true;

	$user = new IUser($userid);

	// Default category titles
	$titles = array(
		'summary' => 'cp.category.summary',
		'profile' => '',   // show profile
		-1 => 'cp.category.user settings',
		'account' => 'cp.category.account',
		'appearance' => 'cp.category.appearance',
		'postoptions' => 'cp.category.post options',
		'watched' => 'cp.category.watched topics',
		'bookmarks' => 'cp.category.bookmarks',
		'filters' => 'cp.category.topic filter');

	// Don't show "post options" if it would be empty
	if (rc('max_sig_len') <= 0 && $user->GetSignature() == '' &&
	    !(rc('avatars_enabled') && UnbCheckRights('maxavatarsize')) &&
	    !(rc('photos_enabled') && UnbCheckRights('maxphotosize')))
		$titles['postoptions'] = null;

	// Don't show "watched topics" for unvalidated members
	if (!in_array(UNB_GROUP_MEMBERS, UnbGetUserGroups($userid))) $titles['watched'] = null;

	$newgroups = array(-1, 'watched');

	// Additional category titles for administrators
	$admintitles = array(
		-2 => 'cp.category.board configuration',
		'boardconf' => 'cp.category.board settings',
		'boardapp' => 'cp.category.board appearance',
		'security' => 'cp.category.security',
		'plugins' => 'cp.category.plugins');

	$adminnewgroups = array(-2);

	// Fill out template parameters
	$TP['controlpanelCategories'] = array();
	$x = 0;
	$separator = false;
	foreach ($titles as $key => $value)
	{
		if (in_array($key, $newgroups)) $separator = true;

		if (isset($value))
		{
			$tpitem = array();
			if ($key == 'profile')
			{
				$tpitem['TitleKey'] = 'cp.show profile';
				$tpitem['Link'] = UnbLink('@this', 'id=' . $userid, true);
			}
			else
			{
				$tpitem['TitleKey'] = $value;
				if (intval($key) >= 0) $tpitem['Link'] = UnbLink('@this', 'id=' . $userid . '&cat=' . $key, true);
				$tpitem['Selected'] = $cat == $key;
			}
			$tpitem['First'] = !$x++;
			$tpitem['NewGroup'] = $separator;
			$TP['controlpanelCategories'][] = $tpitem;

			$separator = false;
		}
	}

	if (UnbCheckRights('is_admin'))
	{
		UnbRequireTxt('controlpanel_admin');

		$x = 0;
		foreach ($admintitles as $key => $value)
		{
			$tpitem = array();
			$tpitem['TitleKey'] = $value;
			if (intval($key) >= 0) $tpitem['Link'] = UnbLink('@this', 'id=' . $userid . '&cat=' . $key, true);
			$tpitem['Selected'] = $cat == $key;
			$tpitem['NewGroup'] = in_array($key, $adminnewgroups);
			$TP['controlpanelCategories'][] = $tpitem;
		}
	}

	$data = array();
	UnbCallHook('cp.addcategory', $data);
	$c = 0;
	if (sizeof($data)) foreach ($data as $newcat)
	{
		if (!$c++)
		{
			$tpitem = array();
			$tpitem['TitleKey'] = 'cp.category.more pages';
			$tpitem['NewGroup'] = true;
			$TP['controlpanelCategories'][] = $tpitem;
		}

		$tpitem = array();
		$tpitem['TitleKey'] = $newcat['title'];
		$tpitem['Link'] = $newcat['link'];
		$tpitem['Selected'] = $cat == $newcat['cpcat'];
		$TP['controlpanelCategories'][] = $tpitem;
	}

	$TP['controlpanelSelectedCat'] = $cat;
	$TP['controlpanelUserID'] = $userid;

	UnbCountUserPosts();

	$p = $error || $_POST['Preview'];

	// ---------- Summary ----------
	if ($cat == 'summary')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);
		$TP['controlpanelUsername'] = t2h($user->GetName());
		if (UnbCheckRights('removeuser'))
		{
			$TP['controlpanelMayRemoveUser'] = true;
			$TP['controlpanelSureDelete'] = UnbSureDelete(1);
		}

		$groups = UnbGetUserGroupNames($userid);
		if ($groups)
			$TP['controlpanelUserGroups'] = t2h(join(', ', $groups));
		else
			$TP['controlpanelUserGroups'] = $UNB_T['none'];

		$TP['controlpanelRegDate'] = UnbFriendlyDate($user->GetRegDate(), 1, 3);

		$post_count = UnbGetPostsByUser($userid);
		if ($post_count)
		{
			$TP['controlpanelPosts'] = $post_count . ' &nbsp; <img ' . $UNB['Image']['search'] . ' /> <a href="' . UnbLink('@search', 'nodef=1&Query=' . $userid . '&ResultView=2&InUser=1&Sort=2', true) . '">' . $UNB_T['cp.find posts'] . '</a>';
		}
		else
		{
			$TP['controlpanelPosts'] = $UNB_T['none'];
		}
		$post = new IPost;
		$allposts = $post->Count();
		$TP['controlpanelPostsPercent'] = ($allposts ? format_number($post_count / $allposts * 100, 1) : 0);
	}
	// summary

	// ---------- Account ----------
	if ($cat == 'account')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);
		$TP['controlpanelIsAdmin'] = UnbCheckRights('is_admin');

		// Username
		$TP['controlpanelUsername'] = ($p ? t2h($_POST['Username']) : t2h($user->GetName()));
		$TP['controlpanelUsernameEdit'] = UnbCheckRights('renameuser');
		$TP['controlpanelUsernameInput'] = ($p ? t2i($_POST['Username']) : t2i($user->GetName()));

		// Group memberships
		$groups = UnbGetGroupNames();
		if (UnbCheckRights('setusergroups'))
		{
			$TP['controlpanelSetGroups'] = true;
			if ($p)
			{
				$usergroups = array();
				foreach ($groups as $id => $name) if ($_POST['UserGroup' . $id] == 1) $usergroups[] = $id;
			}
			else
				$usergroups = UnbGetUserGroups($userid);
			$TP['controlpanelGroups'] = array();
			if ($groups) foreach ($groups as $id => $name) if ($id != UNB_GROUP_GUESTS)
				$TP['controlpanelGroups'][] = array(
					'id' => $id,
					'name' => t2h($name),
					'select' => in_array($id, $usergroups));
		}
		else
		{
			$usergroups = UnbGetUserGroups($userid);
			$usergroupnames = array();
			if ($usergroups) foreach ($usergroups as $g)
				$usergroupnames[] = t2h($groups[$g]);
			natcasesort($usergroupnames);
			$TP['controlpanelGroups'] = join(', ', $usergroupnames);

			// Public groups
			if (UnbGetUserGroups($userid))
			{
				// Only allow setting of public groups if we're in the Members group
				// If this is the case, we're in any group at all, if not, in none, so counting groups is good enough here
				$groups = UnbGetGroupNames(false, true);
				if ($p)
				{
					$usergroups = array();
					foreach ($groups as $id => $name) if ($_POST['UserGroup' . $id] == 1) $usergroups[] = $id;
				}
				else
					$usergroups = UnbGetUserGroups($userid, true);
				$TP['controlpanelPublicGroups'] = array();
				if ($groups) foreach ($groups as $id => $name)
					$TP['controlpanelPublicGroups'][] = array(
						'id' => $id,
						'name' => t2h($name),
						'select' => in_array($id, $usergroups));
			}

			// TODO (User groups management, Task #229)
			/*if ($UNB['LoginUserID'] == $userid &&
			    UnbCheckRights('manageowngroups'))
			{
				echo '<img ' . $UNB['Image']['edit'] . ' /> <a href="">' . $UNB_T['change'] . '</a>';
			}*/
		}

		$TP['controlpanelEMailInput'] = ($p ? t2i($_POST['EMail']) : t2i($user->GetEMail()));
		#if ($TP['controlpanelIsAdmin'])
		$TP['controlpanelValidatedEMail'] = t2h($user->GetValidatedEMail());
		$TP['controlpanelTitleInput'] = ($p ? t2h($_POST['Title']) : t2h($user->GetTitle()));

		$TP['controlpanelContactTypes'] = array(
			array(
				'key' => 'web',
				'title' => $UNB_T['homepage']),
			array(
				'key' => 'jabber',
				'title' => $UNB_T['jabber']),
			array(
				'key' => 'icq',
				'title' => $UNB_T['icq']),
			array(
				'key' => 'msn',
				'title' => $UNB_T['msn']),
			array(
				'key' => 'yahoo',
				'title' => $UNB_T['yim']),
			array(
				'key' => 'aol',
				'title' => $UNB_T['aim']));   // t2i() all titles!
		$TP['controlpanelContacts'] = array();
		$i = 0;
		if ($p)
		{
			while (isset($_POST['VCardType' . ++$i]))
			{
				$TP['controlpanelContacts'][] = array(
					'num' => $i,
					'type' => $_POST['VCardType' . $i],
					'data' => $_POST['VCardData' . $i]);
			}
		}
		else
		{
			if ($user->GetJabber())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'jabber',
					'data' => t2i($user->GetJabber()));
			if ($user->GetICQ())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'icq',
					'data' => t2i($user->GetICQ()));
			if ($user->GetMSN())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'msn',
					'data' => t2i($user->GetMSN()));
			if ($user->GetYIM())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'yahoo',
					'data' => t2i($user->GetYIM()));
			if ($user->GetAIM())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'aol',
					'data' => t2i($user->GetAIM()));
			if ($user->GetHomepage())
				$TP['controlpanelContacts'][] = array(
					'num' => ++$i,
					'type' => 'web',
					'data' => t2i($user->GetHomepage()));
		}

		$TP['controlpanelGender'] = ($p ? $_POST['Gender'] : $user->GetGender());

		$x = ($p ? $_POST['BirthDay'] : $user->GetBirthDay());
		$y = ($p ? $_POST['BirthMonth'] : $user->GetBirthMonth());
		$z = ($p ? $_POST['BirthYear'] : $user->GetBirthYear());
		$TP['controlpanelBirthDay'] = $x > 0 ? $x : '';
		$TP['controlpanelBirthMonth'] = $y > 0 ? $y : '';
		$TP['controlpanelBirthYear'] = $z > 0 ? $z : '';

		$TP['controlpanelLocationInput'] = ($p ? t2i($_POST['Location']) : t2i($user->GetLocation()));

		$TP['controlpanelDescriptionInput'] = ($p ? t2h($_POST['About']) : t2h($user->GetAbout()));

		$TP['controlpanelExtras'] = array();
		if ($UNB['ProfileExtraCount'] > 0)
		{
			for ($i = 1; $i <= $UNB['ProfileExtraCount']; $i++)
			{
				$TP['controlpanelExtras'][] = array(
					'num' => $i,
					'name' => t2h(UnbGetProfileExtraName($i)),
					'value' => ($p ? t2i($_POST['Extra' . $i]) : t2i($user->GetExtra($i))));
			}
		}

	}
	// account

	// ---------- Appearance options ----------
	if ($cat == 'appearance')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);
		$TP['controlpanelIsAdmin'] = UnbCheckRights('is_admin');

		$TP['controlpanelDesigns'] = array();
		$current = $UNB['Design']['CurrentDesign'];
		$tpitem = array();
		$tpitem['name'] = '';
		$tpitem['title'] = $UNB_T['design.default'] . ' (' .
			t2h($UNB['DesignList'][$current]['title']) . ')';
		$tpitem['selected'] = $p && $_POST['Design'] == '' || !$p && $user->GetDesign() == '';
		$TP['controlpanelDesigns'][] = $tpitem;
		if (!rc('nouserdesign') || UnbCheckRights('is_admin'))
		{
			foreach ($UNB['DesignList'] as $name => $data)
			{
				$tpitem = array();
				$tpitem['name'] = $name;
				$tpitem['title'] = t2h($data['title']);
				$tpitem['selected'] = $p && $_POST['Design'] == $name || !$p && $user->GetDesign() == $name;
				$TP['controlpanelDesigns'][] = $tpitem;
			}
		}

		$TP['controlpanelLangs'] = array();
		$curr = $user->GetLanguage();

		$tpitem = array();
		$tpitem['name'] = '';
		$tpitem['img'] = '';
		$tpitem['title'] = $UNB_T['language.default'];
		$tpitem['selected'] = $curr == '';
		$TP['controlpanelLangs'][] = $tpitem;

		foreach ($UNB['AllLangs'] as $lang)
		{
			$tpitem = array();
			$tpitem['name'] = $lang;
			if ($UNB['Client']['b_class'] == 'gecko')
			{
				$tpitem['img'] = $UNB['LibraryURL'] . 'lang/' . $lang . '/flag.png';
				$tpitem['nameTitle'] = $lang;
			}
			$tpitem['title'] = t2h($UNB['AllLangNames'][$lang]);
			$tpitem['selected'] = $curr == $lang;
			$TP['controlpanelLangs'][] = $tpitem;
		}

		$TP['controlpanelDateFormat'] = t2i($user->GetDateFormat());
		$TP['controlpanelDateFormatPreview'] = UnbFormatTime(null, 1 | 2 | 4);
		// New selection list design:
		$TP['controlpanelDateFormats'] = array(
			array('value' => '', 'title' => $UNB_T['timezone.default'], 'selected' => $user->GetDateFormat() == ''),
			array('value' => 'Y-m-d', 'title' => '2006-11-30 (ISO)', 'selected' => $user->GetDateFormat() == 'Y-m-d'),
			array('value' => 'd.m.Y', 'title' => '30.11.2006', 'selected' => $user->GetDateFormat() == 'd.m.Y'),
			array('value' => 'd/m/Y', 'title' => '30/11/2006', 'selected' => $user->GetDateFormat() == 'd/m/Y'),
			array('value' => 'd-m-Y', 'title' => '30-11-2006', 'selected' => $user->GetDateFormat() == 'd-m-Y'),
			array('value' => 'm-d-Y', 'title' => '11-30-2006', 'selected' => $user->GetDateFormat() == 'm-d-Y'),
			array('value' => 'm/d/Y', 'title' => '11/30/2006', 'selected' => $user->GetDateFormat() == 'm/d/Y'),
			array('value' => 'd M Y', 'title' => '30 Nov 2006', 'selected' => $user->GetDateFormat() == 'd M Y'),
			array('value' => 'M d Y', 'title' => 'Nov 30 2006', 'selected' => $user->GetDateFormat() == 'M d Y')
		);

		$offset = $UNB['Timezone']['offset'];
		if ($UNB['Timezone']['withdst']) $offset += (date('I') ? 3600 : 0);
		$tz = ($offset >= 0 ? '+' : '-');
		$offset = abs($offset) / 60;
		$tz .= str_pad(intval($offset / 60), 2, '0', STR_PAD_LEFT) . ':';
		$tz .= str_pad($offset % 60, 2, '0', STR_PAD_LEFT);
		$TP['controlpanelDateFormatTZ'] = $tz;

		$TP['controlpanelTimezones'] = array();
		$curr = $user->GetTimezone();

		$tpitem = array();
		$tpitem['name'] = 99;
		$tpitem['title'] = $UNB_T['timezone.default'];
		$tpitem['selected'] = $curr == 99;
		$TP['controlpanelTimezones'][] = $tpitem;

		$tz_a = array(-12, -11, -10, -9, -9.5, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0, 1, 2, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 8, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 14);
		foreach ($tz_a as $tz)
		{
			$tz4 = $tz * 4;

			$tz_hr = str_pad(abs(intval($tz)), 2, '0', STR_PAD_LEFT);
			$tz_min = (abs($tz) * 60) % 60;

			$tzn = ($tz >= 0 ? '+' : '-') . $tz_hr;
			$tzn .= ':' . str_pad($tz_min, 2, '0', STR_PAD_LEFT);
			#$tzn .=  ' ' . $UNB_T['hours'];

			$tpitem = array();
			$tpitem['name'] = $tz4;
			$tpitem['title'] = 'UTC ' . $tzn;
			$tpitem['selected'] = $curr == $tz4;
			$TP['controlpanelTimezones'][] = $tpitem;
		}

		$curr = $user->GetTimezoneDS();
		if ($curr == -1) $curr = $UNB['Timezone']['withdst'];
		$TP['controlpanelTimezoneDS'] = $curr == 1;
		$TP['controlpanelTimezoneDSForce'] = $curr == 2;   // TODO

		$flags = $user->GetFlags();
		$TP['controlpanelHalfSizeAvatars'] = $flags & UNB_USER_HALFSIZEAVATARS;
		if (UnbCheckRights('is_admin'))
		{
			$x = $flags & UNB_USER_USERREADPOST;
			//echo '<input type="checkbox" class="radio" name="ShowUserReads" id="ShowUserReads" value="1"' . ($x ? ' checked="checked"' : '') . ' /><label for="ShowUserReads">' . $UNB_T['show_user_reads_option'] . '</label>';
		}
		$TP['controlpanelFastReply'] = $flags & UNB_USER_FASTREPLY;
		if (!rc('no_cookies'))
			$TP['controlpanelAutoReLogin'] = $flags & UNB_USER_AUTOLOGIN;
		$TP['controlpanelAutoIgnore'] = $flags & UNB_USER_AUTOIGNORE;
		$TP['controlpanelHideAvatars'] = $flags & UNB_USER_HIDEAVATARS;
		$TP['controlpanelHideSigs'] = $flags & UNB_USER_HIDESIGS;
		$TP['controlpanelHideInlineImages'] = $flags & UNB_USER_HIDEINLINEIMGS;
	}
	// appearance options

	// ---------- Post options ----------
	if ($cat == 'postoptions')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);
		$TP['controlpanelIsAdmin'] = UnbCheckRights('is_admin');

		// This page is only visible when one of the following blocks would be
		// displayed. When new items are added on this page, you may need to
		// update the conditions at the beginning of this function.

		if (rc('max_sig_len') > 0 || $user->GetSignature() != '')
		{
			$TP['controlpanelEnableSignature'] = true;
			$TP['controlpanelSignatureMaxlength'] = rc('max_sig_len');

			// limit ABBC subset for signatures
			$subsets0 = $ABBC['Config']['subsets'];
			$ABBC['Config']['subsets'] &= ~(ABBC_CODE | ABBC_QUOTE | ABBC_LIST);   // no code/quotes/lists at all
			if (rc('abbc_sig_no_font')) $ABBC['Config']['subsets'] &= ~ABBC_FONT;
			if (rc('abbc_sig_no_url')) $ABBC['Config']['subsets'] &= ~ABBC_URL;
			if (rc('abbc_sig_no_img')) $ABBC['Config']['subsets'] &= ~ABBC_IMG;
			if (rc('abbc_sig_no_smilies')) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;
			if ($UNB['TextOnly']) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;   // no smilies for text-only mode

			$TP['controlpanelSignaturePreview'] = AbbcProc($p ? $_POST['Signature'] : $user->GetSignature());
			$TP['controlpanelIsPreview'] = $p;
			if (($p ? $_POST['Signature'] : $user->GetSignature()) == '')
				$TP['controlpanelSignatureIsEmpty'] = true;

			// restore default subset
			$ABBC['Config']['subsets'] = $subsets0;

			$TP['controlpanelSignatureInput'] = $p ? t2h($_POST['Signature']) : t2h($user->GetSignature());
		}

		if (rc('avatars_enabled') && UnbCheckRights('maxavatarsize'))
		{
			$TP['controlpanelEnableAvatar'] = true;

			$TP['controlpanelMaxAvatarWidth'] = UnbCheckRights('maxavatarwidth');
			$TP['controlpanelMaxAvatarHeight'] = UnbCheckRights('maxavatarheight');
			$TP['controlpanelMaxAvatarSize'] = format_number(UnbCheckRights('maxavatarsize'), 1, 1024, ' ') . $UNB_T['bytes'];

			$filename = UnbAvatarFile($user);
			if (file_exists($filename))
			{
				$TP['controlpanelCurrAvatarWidth'] = $user->GetAvatarX();
				$TP['controlpanelCurrAvatarHeight'] = $user->GetAvatarY();
				$TP['controlpanelCurrAvatarSize'] = format_number(filesize($filename), 1, 1024, ' ') . $UNB_T['bytes'];
			}

			$TP['controlpanelAvatarRemote'] = t2i($p ? $_POST['avatarurl'] :
				($user->AvatarFromURL() ? $user->GetAvatar() : ''));

			if ($user->GetAvatar() != '')
			{
				$TP['controlpanelCurrAvatarLink'] = t2i(UnbAvatarUrl($user));
				$TP['controlpanelCurrAvatarImagesize'] = UnbAvatarSize($user, false);
			}
		}

		if (rc('photos_enabled') && UnbCheckRights('maxphotosize'))
		{
			$TP['controlpanelEnablePhoto'] = true;

			$TP['controlpanelMaxPhotoWidth'] = UnbCheckRights('maxphotowidth');
			$TP['controlpanelMaxPhotoHeight'] = UnbCheckRights('maxphotoheight');
			$TP['controlpanelMaxPhotoSize'] = format_number(UnbCheckRights('maxphotosize'), 1, 1024, ' ') . $UNB_T['bytes'];

			$filename = UnbPhotoFile($user);
			if (file_exists($filename))
			{
				$is = getimagesize($filename);
				$TP['controlpanelCurrPhotoWidth'] = $is[0];
				$TP['controlpanelCurrPhotoHeight'] = $is[1];
				$TP['controlpanelCurrPhotoSize'] = format_number(filesize($filename), 1, 1024, ' ') . $UNB_T['bytes'];
			}

			$TP['controlpanelPhotoRemote'] = t2i($p ? $_POST['photourl'] :
				($user->photoFromURL() ? $user->Getphoto() : ''));

			if ($user->GetPhoto() != '')
			{
				$TP['controlpanelCurrPhotoLink'] = t2i(UnbPhotoUrl($user));
			}
		}

	}
	// post options

	// ---------- Watched Topics ----------
	if ($cat == 'watched' &&
	    in_array(UNB_GROUP_MEMBERS, UnbGetUserGroups($userid)))
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$TP['controlpanelNotifyEMail'] = ($p ? $_POST['NotifyEMail'] : $user->GetDefaultNotify() & UNB_NOTIFY_EMAIL);
		if (rc('enable_jabber'))
			$TP['controlpanelNotifyJabber'] = ($p ? $_POST['NotifyJabber'] : $user->GetDefaultNotify() & UNB_NOTIFY_JABBER);

		$TP['controlpanelWatchs'] = array();

		$tw = $user->GetForumWatchs(0, '&' . UNB_NOTIFY_MASK);
		if ($tw) foreach($tw as $key => $a)
		{
			$forumid = intval($a['Forum']);
			$tpitem['forumLink'] = UnbLink('@main', 'id=' . $forumid, true);
			$tpitem['forum'] = t2h($a['Name']);
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_HIDE, $userid))
				$tpitem['forumHidden'] = true;
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_IGNORE, $userid))
				$tpitem['forumIgnored'] = true;

			if ($a['Mode'] & UNB_NOTIFY_EMAIL)
				$tpitem['notifyEMail'] = true;
			if ($a['Mode'] & UNB_NOTIFY_JABBER && rc('enable_jabber'))
				$tpitem['notifyJabber'] = true;

			$tpitem['unwatchName'] = 'unwatch_forum_' . $forumid;
			$tpitem['unwatchLink'] = UnbLink(
				'@main',
				array(
					'id' => $forumid,
					'unwatch' => UNB_NOTIFY_MASK,
					'b2p' => $userid,
					'key' => UnbUrlGetKey()),
				true);
			// <img {$UNBImage.delete} title="{tr "watch_no_longer"}" />

			$TP['controlpanelWatchs'][] = $tpitem;
		}

		$tw = $user->GetThreadWatchs(0, '&' . UNB_NOTIFY_MASK);
		if ($tw) foreach($tw as $key => $a)
		{
			$tpitem = array();
			$tpitem['newForum'] = !sizeof($TP['controlpanelWatchs']) || $a['Forum'] != $tw[$key - 1]['Forum'];

			$threadid = intval($a['Thread']);
			$forumid = intval($a['Forum']);
			$tpitem['forumLink'] = UnbLink('@main', 'id=' . $forumid, true);
			$tpitem['forum'] = t2h($a['Name']);
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_HIDE, $userid))
				$tpitem['forumHidden'] = true;
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_IGNORE, $userid))
				$tpitem['forumIgnored'] = true;

			$isNew = $a['LastRead'] < $a['LastPostDate'];
			if ($isNew)
			{
				$thread = new IThread($threadid);
				$tpitem['threadLink'] = UnbMakePostLink($thread->FirstUnreadPost($a['LastRead']), 0, 2);
				$tpitem['newPosts'] = true;
			}
			else
			{
				$tpitem['threadLink'] = UnbLink('@thread', 'id=' . $threadid, true);
			}
			$tpitem['thread'] = t2h($a['Subject']);
			$n = 0;
			if (rc('show_bookmarked_thread') && $a['Mode'] & UNB_NOTIFY_BOOKMARK)
				$tpitem['threadBookmarked'] = true;
			if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_HIDE, $userid))
				$tpitem['threadHidden'] = true;
			if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_IGNORE, $userid))
				$tpitem['threadIgnored'] = true;

			$tpitem['lastPost'] = UnbFriendlyDate($a['LastPostDate'], 1, 3);

			if ($a['Mode'] & UNB_NOTIFY_EMAIL)
				$tpitem['notifyEMail'] = true;
			if ($a['Mode'] & UNB_NOTIFY_JABBER && rc('enable_jabber'))
				$tpitem['notifyJabber'] = true;

			$tpitem['unwatchName'] = 'unwatch_thread_' . $threadid;
			$tpitem['unwatchLink'] = UnbLink(
				'@thread',
				array(
					'id' => $threadid,
					'unwatch' => UNB_NOTIFY_MASK,
					'b2p' => $userid,
					'key' => UnbUrlGetKey()),
				true);
			// <img {$UNBImage.delete} title="{tr "watch_no_longer"}" />

			$TP['controlpanelWatchs'][] = $tpitem;
		}
	}
	// watched topics

	// ---------- Bookmarks ----------
	if ($cat == 'bookmarks')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$TP['controlpanelBookmarks'] = array();

		$tw = $user->GetThreadWatchs(0, '&' . UNB_NOTIFY_BOOKMARK);
		if ($tw) foreach($tw as $key => $a)
		{
			if (!($a['Mode'] & UNB_NOTIFY_BOOKMARK)) continue;   // only show favorites here [double check]

			$tpitem = array();
			$tpitem['newForum'] = !sizeof($TP['controlpanelBookmarks']) || $a['Forum'] != $tw[$key - 1]['Forum'];

			$threadid = intval($a['Thread']);
			$forumid = intval($a['Forum']);
			$tpitem['forumLink'] = UnbLink('@main', 'id=' . $forumid, true);
			$tpitem['forum'] = t2h($a['Name']);
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_HIDE, $userid))
				$tpitem['forumHidden'] = true;
			if ($UNB['LoginUser']->GetForumFlag($forumid, UNB_UFF_IGNORE, $userid))
				$tpitem['forumIgnored'] = true;

			$isNew = $a['LastRead'] < $a['LastPostDate'];
			if ($isNew)
			{
				$thread = new IThread($threadid);
				$tpitem['threadLink'] = UnbMakePostLink($thread->FirstUnreadPost($a['LastRead']), 0, 2);
				$tpitem['newPosts'] = true;
			}
			else
			{
				$tpitem['threadLink'] = UnbLink('@thread', 'id=' . $threadid, true);
			}
			$tpitem['thread'] = t2h($a['Subject']);
			if (rc('show_bookmarked_thread') && $a['Mode'] & UNB_NOTIFY_BOOKMARK)
				$tpitem['threadBookmarked'] = true;
			if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_HIDE, $userid))
				$tpitem['threadHidden'] = true;
			if ($UNB['LoginUser']->GetThreadFlag($threadid, UNB_UFF_IGNORE, $userid))
				$tpitem['threadIgnored'] = true;

			$tpitem['lastPost'] = UnbFriendlyDate($a['LastPostDate'], 1, 3);

			if ($a['Mode'] & UNB_NOTIFY_EMAIL)
				$tpitem['notifyEMail'] = true;
			if ($a['Mode'] & UNB_NOTIFY_JABBER && rc('enable_jabber'))
				$tpitem['notifyJabber'] = true;

			$tpitem['unbookmarkName'] = 'unbookmark_thread_' . $threadid;
			$tpitem['unbookmarkLink'] = UnbLink(
				'@thread',
				array(
					'id' => $threadid,
					'unwatch' => UNB_NOTIFY_BOOKMARK,
					'b2b' => $userid,
					'key' => UnbUrlGetKey()),
				true);
			// <img {$UNBImage.delete} title="{tr "remove_bookmark"}" />

			$TP['controlpanelBookmarks'][] = $tpitem;
		}
	}
	// bookmarks

	// ---------- Topic filters ----------
	if ($cat == 'filters')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$TP['controlpanelFilters'] = array();

		$allrecords = $UNB['LoginUser']->GetAllForumThreadFlags($userid);
		// only show ignored or hidden elements here
		$records = array();
		foreach ($allrecords as $rec)
			if ($rec['Flags'] & (UNB_UFF_IGNORE | UNB_UFF_HIDE)) $records[] = $rec;

		$thread = new IThread;
		$forum = new IForum;
		$prevForum = -1;
		if ($records) foreach($records as $key => $a)
		{
			$tpitem = array();

			$flags = intval($a['Flags']);
			$forumid = intval($a['Forum']);
			$threadid = intval($a['Thread']);

			if ($threadid) $forumid = $a['TForum'];
			$tpitem['newForum'] = !sizeof($TP['controlpanelFilters']) || $forumid != $prevForum;
			$prevForum = $forumid;

			// TODO: allow root topics
			if (!$forumid) continue;   // something went wrong now

			$tpitem['forumLink'] = UnbLink('@main', 'id=' . $forumid, true);
			$tpitem['forum'] = $a['Name'] ? t2h($a['Name']) : t2h($a['Name2']);

			if ($threadid)
			{
				$tpitem['threadLink'] = UnbLink('@thread', 'id=' . $threadid, true);
				$tpitem['thread'] = t2h($a['Subject']);
				$tpitem['lastPost'] = UnbFriendlyDate($a['LastPostDate'], 1, 3);
			}

			if ($flags & UNB_UFF_HIDE)
			{
				$tpitem['hidden'] = true;
				if (!$threadid)
				{
					$tpitem['unfilterName'] = 'unfilter_forum_' . $forumid;
					// <img {$UNBImage.hide} title="{tr "forum.advanced.hiding"}" />
					$tpitem['unhideLink'] = UnbLink(
						'@main',
						array(
							'id' => $forumid,
							'unhideforum' => $forumid,
							'b2cp' => $userid,
							'key' => UnbUrlGetKey()),
						true);
					// <img {$UNBImage.delete} title="{tr "forum.advanced.unhide"}" />
				}
				else
				{
					$tpitem['unfilterName'] = 'unfilter_thread_' . $threadid;
					// <img {$UNBImage.hide} title="{tr "thread.advanced.hiding"}" />
					$tpitem['unhideLink'] = UnbLink(
						'@thread',
						array(
							'id' => $threadid,
							'unhidethread' => $threadid,
							'b2cp' => $userid,
							'key' => UnbUrlGetKey()),
						true);
					// <img {$UNBImage.delete} title="{tr "thread.advanced.unhide"}" />
				}
			}
			if ($flags & UNB_UFF_IGNORE)
			{
				$tpitem['ignored'] = true;
				if (!$threadid)
				{
					$tpitem['unfilterName'] = 'unfilter_forum_' . $forumid;
					// <img {$UNBImage.ignore} title="{tr "forum.advanced.ignoring"}" />
					$tpitem['unignoreLink'] = UnbLink(
						'@main',
						array(
							'id' => $forumid,
							'unignoreforum' => $forumid,
							'b2cp' => $userid,
							'key' => UnbUrlGetKey()),
						true);
					// <img {$UNBImage.delete} title="{tr "forum.advanced.unignore"}" />
				}
				else
				{
					$tpitem['unfilterName'] = 'unfilter_thread_' . $threadid;
					// <img {$UNBImage.ignore} title="{tr "thread.advanced.ignoring"}" />
					$tpitem['unignoreLink'] = UnbLink(
						'@thread',
						array(
							'id' => $threadid,
							'unignorethread' => $threadid,
							'b2cp' => $userid,
							'key' => UnbUrlGetKey()),
						true);
					// <img {$UNBImage.delete} title="{tr "thread.advanced.unignore"}" />
				}
			}

			$TP['controlpanelFilters'][] = $tpitem;
		}
	}
	// topic filters

	// ---------- Board settings ----------
	if (UnbCheckRights('is_admin') && $cat == 'boardconf')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$TP['controlpanelForumTitle'] = $p ? t2i($_POST['ForumTitle']) : t2i(rc('forum_title'));
		$TP['controlpanelHomeURL'] = $p ? t2i($_POST['HomeURL']) : t2i(rc('home_url'));
		$TP['controlpanelParentURL'] = $p ? t2i($_POST['ParentURL']) : t2i(rc('parent_url'));
		$TP['controlpanelToplogoURL'] = $p ? t2i($_POST['ToplogoURL']) : t2i(rc('toplogo_url'));

		$TP['controlpanelDbServer'] = $p ? t2i($_POST['DbServer']) : t2i(rc('db_server'));
		$TP['controlpanelDbUser'] = $p ? t2i($_POST['DbUser']) : t2i(rc('db_user'));
		$TP['controlpanelDbPass'] = $p ? t2i($_POST['DbPass']) : (rc('db_pass') ? '          ' : '');   // don't give away the actual password
		$TP['controlpanelDbName'] = $p ? t2i($_POST['DbName']) : t2i(rc('db_name'));
		$TP['controlpanelDbPrefix'] = $p ? t2i($_POST['DbPrefix']) : t2i(rc('db_prefix'));

		$TP['controlpanelSmtpServer'] = $p ? t2i($_POST['SmtpServer']) : t2i(rc('smtp_server'));
		$TP['controlpanelSmtpSender'] = $p ? t2i($_POST['SmtpSender']) : t2i(rc('smtp_sender'));
		$TP['controlpanelSmtpUser'] = $p ? t2i($_POST['SmtpUser']) : t2i(rc('smtp_user'));
		$TP['controlpanelSmtpPass'] = $p ? t2i($_POST['SmtpPass']) : (rc('smtp_pass') ? '          ' : '');   // don't give away the actual password
		$TP['controlpanelUsePHPMail'] = $p ? $_POST['UsePHPMail'] : rc('use_php_mail');

		$TP['controlpanelJabberServer'] = $p ? t2i($_POST['JabberServer']) : t2i(rc('jabber_server'));
		$TP['controlpanelJabberUser'] = $p ? t2i($_POST['JabberUser']) : t2i(rc('jabber_user'));
		$TP['controlpanelJabberPass'] = $p ? t2i($_POST['JabberPass']) : (rc('jabber_pass') ? '          ' : '');   // don't give away the actual password
		$TP['controlpanelEnableJabber'] = $p ? $_POST['EnableJabber'] : rc('enable_jabber');

		$TP['controlpanelLangs'] = array();
		$curr = rc('def_lang');

		foreach ($UNB['AllLangs'] as $lang)
		{
			$tpitem = array();
			$tpitem['name'] = $lang;
			$tpitem['img'] = $UNB['LibraryURL'] . 'lang/' . $lang . '/flag.png';
			$tpitem['title'] = t2h($UNB['AllLangNames'][$lang]);
			$tpitem['selected'] = $curr == $lang;
			$TP['controlpanelLangs'][] = $tpitem;
		}

		$TP['controlpanelTimezones'] = array();
		$curr = rc('tz_offset');

		$tz_a = array(-12, -11, -10, -9, -9.5, -8, -7, -6, -5, -4, -3.5, -3, -2, -1, 0, 1, 2, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 8, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 14);
		foreach ($tz_a as $tz)
		{
			$tz4 = $tz * 4;

			$tz_hr = str_pad(abs(intval($tz)), 2, '0', STR_PAD_LEFT);
			$tz_min = (abs($tz) * 60) % 60;

			$tzn = ($tz >= 0 ? '+' : '-') . $tz_hr;
			$tzn .= ':' . str_pad($tz_min, 2, '0', STR_PAD_LEFT);
			#$tzn .=  ' ' . $UNB_T['hours'];

			$tpitem = array();
			$tpitem['name'] = $tz4;
			$tpitem['title'] = 'UTC ' . $tzn;
			$tpitem['selected'] = $curr == $tz4;
			$TP['controlpanelTimezones'][] = $tpitem;
		}

		$curr = rc('tz_dst');
		$TP['controlpanelTimezoneDS'] = $curr == 1;
		$TP['controlpanelTimezoneDSForce'] = $curr == 2;   // TODO

		$TP['controlpanelDateFormatPreview'] = UnbFormatTime(null, 1 | 2 | 4);

		$offset = $UNB['Timezone']['offset'];
		if ($UNB['Timezone']['withdst']) $offset += (date('I') ? 3600 : 0);
		$tz = ($offset >= 0 ? '+' : '-');
		$offset = abs($offset) / 60;
		$tz .= str_pad(intval($offset / 60), 2, '0', STR_PAD_LEFT) . ':';
		$tz .= str_pad($offset % 60, 2, '0', STR_PAD_LEFT);
		$TP['controlpanelDateFormatTZ'] = $tz;
	}
	// Board settings

	// ---------- Board appearance ----------
	if (UnbCheckRights('is_admin') && $cat == 'boardapp')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		$TP['controlpanelDesigns'] = array();
		foreach ($UNB['DesignList'] as $name => $data)
		{
			$tpitem = array();
			$tpitem['name'] = $name;
			$tpitem['title'] = t2h($data['title']);
			$tpitem['selected'] = $p && $_POST['Design'] == $name || !$p && rc('design') == $name;
			$TP['controlpanelDesigns'][] = $tpitem;
		}

		$AllSmileSets = array();
		$handle = opendir(dirname(__FILE__) . '/designs/_smile');
		if ($handle !== false)
		{
			while ($file = readdir($handle))
				if (preg_match('/^([A-Za-z0-9-_]+)$/', $file, $m))
				{
					if (is_dir(dirname(__FILE__) . '/designs/_smile/' . $m[1]) &&
						file_exists(dirname(__FILE__) . '/designs/_smile/' . $m[1] . '/config.php'))
					{
						array_push($AllSmileSets, $m[1]);
					}
				}
			closedir($handle);
			sort($AllSmileSets);
		}

		$TP['controlpanelSmileSets'] = array();
		foreach ($AllSmileSets as $name)
		{
			$tpitem = array();
			$tpitem['name'] = $name;
			$tpitem['title'] = t2h($name);
			$tpitem['selected'] = $p && $_POST['SmileSet'] == $name || !$p && rc('smileset') == $name;
			$TP['controlpanelSmileSets'][] = $tpitem;
		}

		// -- general --
		$TP['controlpanelLoginTop'] = $p ? t2i($_POST['LoginTop']) : t2i(rc('login_top'));
		$TP['controlpanelShowOnlineUsers'] = $p ? t2i($_POST['ShowOnlineUsers']) : t2i(rc('show_online_users'));
		$TP['controlpanelFootDbTime'] = $p ? t2i($_POST['FootDbTime']) : t2i(rc('foot_db_time'));
		$TP['controlpanelGZip'] = $p ? t2i($_POST['GZip']) : t2i(rc('gzip'));   # on|off|auto
		$TP['controlpanelModRewriteUrls'] = $p ? t2i($_POST['ModRewriteUrls']) : t2i(rc('mod_rewrite_urls'));
		$TP['controlpanelShowGotoForum'] = $p ? t2i($_POST['ShowGotoForum']) : t2i(rc('show_goto_forum'));
		$TP['controlpanelShowSearchForum'] = $p ? t2i($_POST['ShowSearchForum']) : t2i(rc('show_search_forum'));
		$TP['controlpanelEnableTraceUsers'] = $p ? t2i($_POST['EnableTraceUsers']) : t2i(rc('enable_trace_users'));
		$TP['controlpanelPostPreviewSendButton'] = $p ? t2i($_POST['PostPreviewSendButton']) : t2i(rc('post_preview_send_button'));
		$TP['controlpanelShowLastVisitTime'] = $p ? t2i($_POST['ShowLastVisitTime']) : t2i(rc('show_last_visit_time'));
		$TP['controlpanelDisplayForumLastpostRe'] = $p ? t2i($_POST['DisplayForumLastpostRe']) : t2i(rc('display_forum_lastpost_re'));
		$TP['controlpanelShowBirthdays'] = $p ? t2i($_POST['ShowBirthdays']) : t2i(rc('show_birthdays'));
		$TP['controlpanelDisableSearchHighlighting'] = $p ? t2i($_POST['DisableSearchHighlighting']) : t2i(rc('disable_search_highlighting'));
		$TP['controlpanelShowForumRssLink'] = $p ? t2i($_POST['ShowForumRssLink']) : t2i(rc('show_forum_rss_link'));
		$TP['controlpanelLocationLink'] = $p ? t2i($_POST['LocationLink']) : t2i(rc('location_link'));   # text

		$TP['controlpanelThreadsPerPage'] = $p ? t2i($_POST['ThreadsPerPage']) : t2i(rc('threads_per_page'));
		$TP['controlpanelPostsPerPage'] = $p ? t2i($_POST['PostsPerPage']) : t2i(rc('posts_per_page'));
		$TP['controlpanelUsersPerPage'] = $p ? t2i($_POST['UsersPerPage']) : t2i(rc('users_per_page'));
		$TP['controlpanelHotThreadsPosts'] = $p ? t2i($_POST['HotThreadsPosts']) : t2i(rc('hot_thread_posts'));
		$TP['controlpanelHotThreadsViews'] = $p ? t2i($_POST['HotThreadsViews']) : t2i(rc('hot_thread_views'));

		$TP['controlpanelExtraNames'] = $p ? t2i($_POST['ExtraNames']) : t2i(rc('extra_names'));   # text
		$TP['controlpanelExtraCount'] = sizeof(explode('|', rc('extra_names')));

		$TP['controlpanelForumsTreeStyles'] = array();
		$tpitem = array();
		$tpitem['name'] = 'unicode';
		$tpitem['title'] = $UNB_T['cp.forum tree style.unicode'];
		$tpitem['selected'] = $p && $_POST['controlpanelForumsTreeStyle'] == $tpitem['name'] || !$p && rc('forums_tree_style') == $tpitem['name'];
		$TP['controlpanelForumsTreeStyles'][] = $tpitem;
		$tpitem = array();
		$tpitem['name'] = 'nolines';
		$tpitem['title'] = $UNB_T['cp.forum tree style.nolines'];
		$tpitem['selected'] = $p && $_POST['controlpanelForumsTreeStyle'] == $tpitem['name'] || !$p && rc('forums_tree_style') == $tpitem['name'];
		$TP['controlpanelForumsTreeStyles'][] = $tpitem;
		$tpitem = array();
		$tpitem['name'] = 'dots';
		$tpitem['title'] = $UNB_T['cp.forum tree style.dots'];
		$tpitem['selected'] = $p && $_POST['controlpanelForumsTreeStyle'] == $tpitem['name'] || !$p && rc('forums_tree_style') == $tpitem['name'];
		$TP['controlpanelForumsTreeStyles'][] = $tpitem;
		$tpitem = array();
		$tpitem['name'] = 'hlines';
		$tpitem['title'] = $UNB_T['cp.forum tree style.hlines'];
		$tpitem['selected'] = $p && $_POST['controlpanelForumsTreeStyle'] == $tpitem['name'] || !$p && rc('forums_tree_style') == $tpitem['name'];
		$TP['controlpanelForumsTreeStyles'][] = $tpitem;

		// -- posts display --
		$TP['controlpanelNewTopicLinkInThread'] = $p ? t2i($_POST['NewTopicLinkInThread']) : t2i(rc('new_topic_link_in_thread'));
		$TP['controlpanelPostAttachInlineMaxsize'] = $p ? t2i($_POST['PostAttachInlineMaxsize']) : t2i(rc('post_attach_inline_maxsize'));
		$TP['controlpanelPostAttachInlineMaxwidth'] = $p ? t2i($_POST['PostAttachInlineMaxwidth']) : t2i(rc('post_attach_inline_maxwidth'));
		$TP['controlpanelPostAttachInlineMaxheight'] = $p ? t2i($_POST['PostAttachInlineMaxheight']) : t2i(rc('post_attach_inline_maxheight'));
		$TP['controlpanelPostShowTextlength'] = $p ? t2i($_POST['PostShowTextlength']) : t2i(rc('post_show_textlength'));
		$TP['controlpanelMaxPollOptions'] = $p ? t2i($_POST['MaxPollOptions']) : t2i(rc('max_poll_options'));

		// -- threads/forums list --
		$TP['controlpanelOwnPostsInThreadlist'] = $p ? t2i($_POST['OwnPostsInThreadlist']) : t2i(rc('own_posts_in_threadlist'));
		$TP['controlpanelShowBookmarkedThread'] = $p ? t2i($_POST['ShowBookmarkedThread']) : t2i(rc('show_bookmarked_thread'));
		$TP['controlpanelDisplayThreadStartdate'] = $p ? t2i($_POST['DisplayThreadStartdate']) : t2i(rc('display_thread_startdate'));
		$TP['controlpanelAdvancedThreadCounter'] = $p ? t2i($_POST['AdvancedThreadCounter']) : t2i(rc('advanced_thread_counter'));
		$TP['controlpanelCountThreadViews'] = $p ? t2i($_POST['CountThreadViews']) : t2i(rc('count_thread_views'));
		$TP['controlpanelDisplayThreadLastposter'] = $p ? t2i($_POST['DisplayThreadLastposter']) : t2i(rc('display_thread_lastposter'));
		$TP['controlpanelCountForumThreadsPosts'] = $p ? t2i($_POST['CountForumThreadsPosts']) : t2i(rc('count_forum_threads_posts'));
		$TP['controlpanelDisplayForumLastpost'] = $p ? t2i($_POST['DisplayForumLastpost']) : t2i(rc('display_forum_lastpost'));

		// -- users list --
		$TP['controlpanelUlistRegdate'] = $p ? t2i($_POST['UlistRegdate']) : t2i(rc('ulist_regdate'));
		$TP['controlpanelUlistLocation'] = $p ? t2i($_POST['UlistLocation']) : t2i(rc('ulist_location'));
		$TP['controlpanelUlistPosts'] = $p ? t2i($_POST['UlistPosts']) : t2i(rc('ulist_posts'));
		$TP['controlpanelUlistLastpost'] = $p ? t2i($_POST['UlistLastpost']) : t2i(rc('ulist_lastpost'));

		// -- timings --
		$TP['controlpanelPollCurrentDays'] = $p ? t2i($_POST['PollCurrentDays']) : t2i(rc('poll_current_days'));
		$TP['controlpanelQuoteWithDate'] = $p ? t2i($_POST['QuoteWithDate']) : t2i(rc('quote_with_date'));
		$TP['controlpanelNoEditNoteGraceTime'] = $p ? t2i($_POST['NoEditNoteGraceTime']) : t2i(rc('no_edit_note_grace_time'));
		$TP['controlpanelMovedThreadNoteTimeout'] = $p ? t2i($_POST['MovedThreadNoteTimeout']) : t2i(rc('moved_thread_note_timeout'));
		$TP['controlpanelOnlineUsersReloadInterval'] = $p ? t2i($_POST['OnlineUsersReloadInterval']) : t2i(rc('online_users_reload_interval'));
		$TP['controlpanelUserOnlineTimeout'] = $p ? t2i($_POST['UserOnlineTimeout']) : t2i(rc('user_online_timeout'));

	}
	// Board appearance

	// ---------- Security ----------
	if (UnbCheckRights('is_admin') && $cat == 'security')
	{
		$TP['controlpanelFormLink'] = UnbLink('@this', null, true);

		// -- user accounts --
		$TP['controlpanelNewUserValidation'] = $p ? t2i($_POST['NewUserValidation']) : t2i(rc('new_user_validation'));   // 0|1|2|3
		$TP['controlpanelDisallowedUsernames'] = $p ? t2i($_POST['DisallowedUsernames']) : t2i(rc('disallowed_usernames'));   // list
		$TP['controlpanelDisallowedEmails'] = $p ? t2i($_POST['DisallowedEmails']) : t2i(rc('disallowed_emails'));   // list
		$TP['controlpanelAllowedEmailDomains'] = $p ? t2i($_POST['AllowedEmailDomains']) : t2i(rc('allowed_email_domains'));   // list
		$TP['controlpanelDisallowEmailReuse'] = $p ? t2i($_POST['DisallowEmailReuse']) : t2i(rc('disallow_email_reuse'));
		$TP['controlpanelUsernameMinlength'] = $p ? t2i($_POST['UsernameMinlength']) : t2i(rc('username_minlength'));   // number
		$TP['controlpanelUsernameMaxlength'] = $p ? t2i($_POST['UsernameMaxlength']) : t2i(rc('username_maxlength'));   // number
		$TP['controlpanelUsertitleMaxlength'] = $p ? t2i($_POST['UsertitleMaxlength']) : t2i(rc('usertitle_maxlength'));   // number
		$TP['controlpanelPassMinlength'] = $p ? t2i($_POST['PassMinlength']) : t2i(rc('pass_minlength'));   // number
		$TP['controlpanelPassNotusername'] = $p ? t2i($_POST['PassNotusername']) : t2i(rc('pass_notusername'));
		$TP['controlpanelPassNeednumber'] = $p ? t2i($_POST['PassNeednumber']) : t2i(rc('pass_neednumber'));
		$TP['controlpanelPassNeedspecial'] = $p ? t2i($_POST['PassNeedspecial']) : t2i(rc('pass_needspecial'));

		// -- avatars and photos --
		$TP['controlpanelAvatarsEnabled'] = $p ? t2i($_POST['AvatarsEnabled']) : t2i(rc('avatars_enabled'));
		$TP['controlpanelAllowRemoteAvatars'] = $p ? t2i($_POST['AllowRemoteAvatars']) : t2i(rc('allow_remote_avatars'));
		$TP['controlpanelAvatarX'] = $p ? t2i($_POST['AvatarX']) : t2i(rc('avatar_x'));   // number
		$TP['controlpanelAvatarY'] = $p ? t2i($_POST['AvatarY']) : t2i(rc('avatar_y'));   // number
		$TP['controlpanelAvatarBytes'] = $p ? t2i($_POST['AvatarBytes']) : t2i(rc('avatar_bytes'));   // number
		$TP['controlpanelPhotosEnabled'] = $p ? t2i($_POST['PhotosEnabled']) : t2i(rc('photos_enabled'));
		$TP['controlpanelPhotoX'] = $p ? t2i($_POST['PhotoX']) : t2i(rc('photo_x'));   // number
		$TP['controlpanelPhotoY'] = $p ? t2i($_POST['PhotoY']) : t2i(rc('photo_y'));   // number
		$TP['controlpanelPhotoBytes'] = $p ? t2i($_POST['PhotoBytes']) : t2i(rc('photo_bytes'));   // number

		// -- posts and topics --
		$TP['controlpanelMaxPostLen'] = $p ? t2i($_POST['MaxPostLen']) : t2i(rc('max_post_len'));   // number
		$TP['controlpanelMaxSigLen'] = $p ? t2i($_POST['MaxSigLen']) : t2i(rc('max_sig_len'));   // number
		$TP['controlpanelAttachBytes'] = $p ? t2i($_POST['AttachBytes']) : t2i(rc('attach_bytes'));   // number
		$TP['controlpanelAttachExts'] = $p ? t2i($_POST['AttachExts']) : t2i(rc('attach_exts'));   // list
		$TP['controlpanelTopicSubjectMinlength'] = $p ? t2i($_POST['TopicSubjectMinlength']) : t2i(rc('topic_subject_minlength'));   // number
		$TP['controlpanelTopicSubjectMaxlength'] = $p ? t2i($_POST['TopicSubjectMaxlength']) : t2i(rc('topic_subject_maxlength'));   // number

		# limit ABBC subset for signatures
		$TP['controlpanelAbbcSigNoFont'] = $p ? t2i($_POST['AbbcSigNoFont']) : t2i(rc('abbc_sig_no_font'));
		$TP['controlpanelAbbcSigNoUrl'] = $p ? t2i($_POST['AbbcSigNoUrl']) : t2i(rc('abbc_sig_no_url'));
		$TP['controlpanelAbbcSigNoImg'] = $p ? t2i($_POST['AbbcSigNoImg']) : t2i(rc('abbc_sig_no_img'));
		$TP['controlpanelAbbcSigNoSmilies'] = $p ? t2i($_POST['AbbcSigNoSmilies']) : t2i(rc('abbc_sig_no_smilies'));

		// -- advanced --
		$TP['controlpanelNoCookies'] = $p ? t2i($_POST['NoCookies']) : t2i(rc('no_cookies'));
		$TP['controlpanelSessionIpNetmask'] = $p ? t2i($_POST['SessionIpNetmask']) : t2i(rc('session_ip_netmask'));   // number (0..32)
		$TP['controlpanelUseVeriword'] = $p ? t2i($_POST['UseVeriword']) : t2i(rc('use_veriword'));
		$TP['controlpanelAutoBanFloodIp'] = $p ? t2i($_POST['AutoBanFloodIp']) : t2i(rc('auto_ban_flood_ip'));
		$TP['controlpanelAutoBanFloodIpPeriod'] = $p ? t2i($_POST['AutoBanFloodIpPeriod']) : t2i(rc('auto_ban_flood_ip_period'));   // number [s]
		$TP['controlpanelAutoBanFloodIpThreshold'] = $p ? t2i($_POST['AutoBanFloodIpThreshold']) : t2i(rc('auto_ban_flood_ip_threshold'));   // number
		$TP['controlpanelAdminLock'] = $p ? t2i($_POST['AdminLock']) : t2i(rc('admin_lock'));
		$announce =& new IAnnounce;
		if ($announce->Find(-1))
			$MaintenanceMsg = $announce->GetMsg();
		else
			$MaintenanceMsg = '';
		$TP['controlpanelAdminLockMessage'] = $p ? t2i($_POST['AdminLockMessage']) : t2h($MaintenanceMsg);
		$TP['controlpanelReadOnly'] = $p ? t2i($_POST['ReadOnly']) : t2i(rc('read_only'));
		$TP['controlpanelEnableVersioncheck'] = $p ? t2i($_POST['EnableVersioncheck']) : t2i(rc('enable_versioncheck'));

	}
	// Security

	// ---------- Plug-ins ----------
	if (UnbCheckRights('is_admin') && $cat == 'plugins')
	{
		if ($_REQUEST['page'] == 'info')
		{
			$TP['controlpanelPluginsPagetype'] = 'info';

			$name = trim($_REQUEST['pluginname']);
			if (array_key_exists($name, $UNB['PlugIns']))
			{
				$plug = $UNB['PlugIns'][$name];

				$TP['controlpanelPluginName'] = $name;
				if ($plug['status'] == 'ok')
					$TP['controlpanelPluginStatus'] = $UNB_T['cp.plugin.status.ok'];
				elseif ($plug['status'] == 'disabled')
					$TP['controlpanelPluginStatus'] = $UNB_T['cp.plugin.status.disabled'];
				elseif ($plug['status'] == 'wrongversion')
					$TP['controlpanelPluginStatus'] = $UNB_T['cp.plugin.status.wrongversion'];
				else
					$TP['controlpanelPluginStatus'] = $UNB_T['cp.plugin.status.error'] . ': ' . t2h($plug['status'], true, true, true);
				$TP['controlpanelPluginDesc'] = t2h($plug['desc'], true, true, true);
				$TP['controlpanelPluginAuthor'] = t2h($plug['author'], true, true, true);
				$lang = join(', ', array_map('trim', explode(' ', $plug['lang'])));
				$TP['controlpanelPluginLang'] = t2h($lang, true, true, true);
				$version = '';
				$lines = explode(endl, $plug['version']);
				foreach ($lines as $line)
				{
					list($minver, $maxver) = explode(' ', trim($line));
					if ($minver)
						$version .= $minver;
					if ($maxver)
						$version .= ' - ' . $maxver;
					else
						$version .= ' ' . $UNB_T['cp.pluginfo.or newer'];
					$version .= endl;
				}
				$TP['controlpanelPluginVersion'] = t2h($version, true, true, true);
			}
		}
		elseif ($_REQUEST['page'] == 'config')
		{
			$TP['controlpanelPluginsPagetype'] = 'config';

			$name = trim($_REQUEST['pluginname']);
			$TP['controlpanelEnableThisPlugin'] = !in_array($name, rc('disable_plugins', true));

			if (array_key_exists($name, $UNB['PlugIns']))
			{
				$plug = $UNB['PlugIns'][$name];
				$confighandler = $plug['config'];

				$TP['controlpanelPluginName'] = $name;
				if (function_exists($confighandler))
				{
					$TP['controlpanelPluginConfigFields'] = array();
					$data = array(
						'request' => 'fields',
						'fields' => &$TP['controlpanelPluginConfigFields']);
					$confighandler($data);
				}

			}
		}
		else
		{
			$TP['controlpanelPluginsPagetype'] = 'list';

			// list all plug-ins
			$TP['controlpanelPlugins'] = array();
			$num = 1;
			foreach ($UNB['PlugIns'] as $name => $plug)
			{
				$tpitem = array();
				$tpitem['num'] = $num++;
				$tpitem['name'] = $name;
				$tpitem['desc'] = t2h($plug['desc'], true, true, true);
				$tpitem['status'] = $plug['status'];

				if ($plug['status'] == 'ok')
					$tpitem['statusTitle'] = '';
				elseif ($plug['status'] == 'disabled')
					$tpitem['statusTitle'] = $UNB_T['cp.plugin.status.disabled'];
				elseif ($plug['status'] == 'wrongversion')
					$tpitem['statusTitle'] = $UNB_T['cp.plugin.status.wrongversion'];
				else
					$tpitem['statusTitle'] = $UNB_T['cp.plugin.status.error'];

				$tpitem['infoLink'] = UnbLink('@this', 'cat=plugins&page=info&pluginname=' . $name, true);
				$tpitem['configLink'] = UnbLink('@this', 'cat=plugins&page=config&pluginname=' . $name, true);

				$TP['controlpanelPlugins'][] = $tpitem;
			}
			$TP['controlpanelPlugins'][0]['firstitem'] = true;
			$TP['controlpanelPlugins'][sizeof($TP['controlpanelPlugins']) - 1]['lastitem'] = true;
		}
	}
	// Plug-ins

	// handle plug-in pages
	$TP['controlpanelMoreCats'] = array();
	$data = array(
		'cat' => $cat);
	UnbCallHook('cp.categorypage', $data);
}

if ($error) UnbErrorLog($error);

// -------------------- Begin page --------------------

UnbBeginHTML($UNB_T['user cp']);

$TP =& $UNB['TP'];

$TP['errorMsg'] .= $error;

if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity(UNB_ULF_PROFILE);
else UnbSetGuestLastForum(UNB_ULF_PROFILE);

$user = new IUser;
if (!$user->Load($userid))
{
	$TP['errorMsg'] .= $UNB_T['error.invalid user'] . '<br />';
	$TP['headNoIndex'] = true;
}
else
{
	if ($_REQUEST['action'] == 'email' || $_REQUEST['action'] == 'sendemail')
	{
		EMailForm($userid);
		UteRemember('userprofile.html', $TP);
	}
	elseif ($_REQUEST['action'] == 'emailsuccess')
	{
		$TP['headNoIndex'] = true;
		$TP['infoMsg'] .= str_replace('{x}', t2h($user->GetName()), $UNB_T['send email.sent to x']) . '<br />';
	}
	else
	{
		if (!$cat)
		{
			UnbAddLog('view_profile ' . $userid);
			if (!UnbCheckRights('showprofile', 0, 0, $userid))
			{
				UnbErrorLog($UNB_T['error.access denied']);
				$TP['errorMsg'] .= $UNB_T['error.access denied'] . '<br />';
				$TP['headNoIndex'] = true;
			}
			else
			{
				ShowProfile($userid);
				UteRemember('userprofile.html', $TP);
			}
		}
		else
		{
			UnbAddLog('user_cp ' . $userid . ' category ' . $cat);
			if (!UnbCheckRights('editprofile', 0, 0, $userid))
			{
				UnbErrorLog($UNB_T['error.access denied']);
				$TP['errorMsg'] .= $UNB_T['error.access denied'] . '<br />';
				$TP['headNoIndex'] = true;
			}
			else
			{
				if ($userid != $UNB['LoginUserID'])
					$TP['infoMsg'] .= str_replace('{x}', t2h($user->GetName()), $UNB_T['cp.currently editing user x']) . '<br />';

				if ($_REQUEST['saved'])
					$TP['infoMsg'] .= $UNB_T['cp.settings saved'] . '<br />';

				CPForm($userid, $cat);
				UteRemember('controlpanel.html', $TP);
			}
		}
	}
}

UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
