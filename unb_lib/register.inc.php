<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// register.inc.php
// New User Registration

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

UnbRequireTxt('register');

// List of disallowed UserNames and Mail addresses for Registration
// These string must not appear entirely or partly in requested UserNames or EMail Addresses at registration
// Comparison is done case-insensitive
$disallowed_usernames = rc('disallowed_usernames', true);
$disallowed_emails = rc('disallowed_emails', true);
$allowed_email_domains = rc('allowed_email_domains', true);

$error = false;

// "Registration step 2": Check user data and add user
//
if ($_POST['action'] == 'step2' &&
    rc('new_user_validation') > 0 &&
    (!rc('admin_lock') || UnbCheckRights('is_admin')))
{
	$user = new IUser;

	// import request variables
	$name = trim($_POST['Name']);
	$password = trim($_POST['Password']);
	$password2 = trim($_POST['Password2']);
	$email = trim($_POST['EMail']);

	// check data
	if ($name == '' || $password == '' || $email == '')
	{
		$error .= $UNB_T['users.adduser.error.form not complete'] . '<br />';
	}

	require_once($UNB['LibraryPath'] . 'captcha.class.php');
	if (rc('use_veriword') &&
	    !UnbCaptcha::CheckWord($_POST['veriword']))
	{
		$error .= $UNB_T['vericode.error.invalid key'] . '<br />';
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
	else
	{
		foreach ($disallowed_usernames as $d_name)
		{
			if (!strcasecmp($name, $d_name) ||      // entire match
				(substr($d_name, 0, 1) == '*' ?
					stristr($name, substr($d_name, 1)) :       // partial match
					false))
			{
				$error .= $UNB_T['error.username disallowed'] . '<br />';
				break;
			}
		}
	}
	if (rc('username_minlength') &&
	    strlen($name) < rc('username_minlength'))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{min}', rc('username_minlength'), $UNB_T['cp.error.username too short']) . '<br />';
	}
	if (rc('username_maxlength') &&
		strlen($_POST['Name']) > min(rc('username_maxlength'), 40))
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= str_replace('{max}', rc('username_maxlength'), $UNB_T['cp.error.username too long']) . '<br />';
	}

	// username available?
	if ($user->FindByName($name))
	{
		$error .= $UNB_T['error.username assigned'] . '<br />';
	}

	// both passwords equal?
	if ($password != $password2)
	{
		UnbRequireTxt('controlpanel');   // for below error messages
		$error .= $UNB_T['cp.error.passwords dont match'] . '<br />';
	}

	// password insecure?
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
	else
	{
		foreach ($disallowed_emails as $d_mail)
		{
			if (!strcasecmp($email, $d_mail) ||      // entire match
				(substr($d_mail, 0, 1) == '*' ?
					stristr($email, substr($d_mail, 1)) :       // partial match
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
				if (preg_match("/@$domain$/i", $email)) $n++;
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
		if ($user->FindByEMail($email))
		{
			$error .= $UNB_T['error.email assigned'] . '<br />';
		}
	}

	// Allow plug-ins to reject the post
	$data = array(
		'error' => '',
		'username' => $name,
		'email' => $email);
	UnbCallHook('user.verifyregister', $data);
	if ($data['error']) $error .= $data['error'];

	if (!$error)
	{
		if (!$user->Add($name, $password, $email))
		{
			$error .= $UNB_T['users.error.user not created'] . '<br />';
			UnbAddLog('register_user ' . $name . ' error');
		}
		else
		{
			UnbAddLog('register_user ' . $name . ' (' . $user->GetID() . ') ok');
			$next_smtperror = false;

			if (isset($_POST['timezone']) && $_POST['timezone'] != '' && is_numeric($_POST['timezone']))
			{
				$tz = intval(intval($_POST['timezone']) / 15);
				$dst = 0;   // NOTE: JavaScript cannot detect if DST is in effect
				$user->SetTimezone($tz, $dst);
			}

			// check validation...
			switch (rc('new_user_validation'))
			{
				case 1:  // immediately
					UnbAddLog('debug: register: set user group');
					UnbSetUserGroups($user->GetID(), array(UNB_GROUP_MEMBERS));
					break;
				case 2:  // link via E-Mail
					UnbAddLog('debug: register: send email');
					if (!SendValidationMail($user, true))
					{
						$next_smtperror = true;
					}
					break;
				case 3:  // manually by mod/admin
					// set key to non-empty for the statistics to work correctly
					// but don't let the user validate automatically with this key
					$user->SetValidateKey('*');
					$admins = UnbGetGroupMembers(UNB_GROUP_ADMINS);   // Send the notification to all Admins for now
					$c = UnbNotifyUser(
						$admins,
						UNB_NOTIFY_EMAIL,
						'register.mail.manual.subject',
						array(),
						'register.mail.manual.body',
						array(
							'{url}' => TrailingSlash(rc('home_url')) .
								UnbLink('@cp', 'id=' . $user->GetID() . '&cat=summary', false, /*sid*/ false)));
					if (!$c)
					{
						$next_smtperror = true;
					}
					break;
				case 4:  // recommendation by a user
					// TODO: hmm, how shall this work?
					//       -> see Feature #12 ([User registration] User registration by recommendation)
					break;
			}

			// Only when there wasn't an e-mail sent to all admins anyway... (see right above)
			if (rc('notify_admin_on_new_user') && rc('new_user_validation') != 3)
			{
				// Send the notification to all Admins informing about the new user
				$admins = UnbGetGroupMembers(UNB_GROUP_ADMINS);
				$c = UnbNotifyUser(
					$admins,
					UNB_NOTIFY_EMAIL,
					'register.mail.newuser.subject',
					array(),
					'register.mail.newuser.body',
					array(
						'{url}' => TrailingSlash(rc('home_url')) .
							UnbLink('@cp', 'id=' . $user->GetID() . '&cat=summary', false, /*sid*/ false)));
			}

			UnbForwardHTML(UnbLink('@this', 'id=' . $user->GetID() . ($next_smtperror ? '&smtperror=1' : '')));
			// TODO: store parameters like this 'smtperror' in the session instead of passing it in the URL.
			//       the next page will check for this flag, show the warning and reset the flag again.
			//       reloading the other page will make the message disappear.
		}
	}
}

// -------------------- BEGIN page --------------------

$TP =& $UNB['TP'];
$TP['headNoIndex'] = true;

UnbBeginHTML($UNB_T['register.title']);

if (rc('new_user_validation') == 0)
{
	$TP['errorMsg'] .= $UNB_T['register.temporary disabled'] . '<br />';
}
elseif (isset($_GET['id']))
{
	if ($_REQUEST['smtperror'] == 1) $error .= $UNB_T['error.smtp'] . '<br />';

	if ($error)
	{
		$TP['errorMsg'] .= $error;
		UnbErrorLog($error);
	}

	$user = new IUser($_GET['id']);

	if ($_GET['validate'] == 1)
	{
		// ----- Validate user with e-mail link -----

		if ($_GET['key'] == $user->GetValidateKey())
		{
			// TODO: should we check '!sizeof(UnbGetUserGroups($user->GetID()))' here?
			UnbAddLog('debug: register: validate, set user group');

			// Add 'Members' group membership
			$groups = UnbGetUserGroups($user->GetID());
			if (!in_array(UNB_GROUP_MEMBERS, $groups)) $groups[] = UNB_GROUP_MEMBERS;
			if (UnbSetUserGroups($user->GetID(), $groups))
			{
				$TP['infoMsg'] .= $UNB_T['register.account validated'] . '<br />';
				$TP['registerValidated'] = true;
				$user->SetValidateKey('');

				$user->SetValidatedEMail($user->GetEMail());
			}
			else
			{
				$TP['errorMsg'] .= $UNB_T['register.error.validation failed'] . ' (' . t2h($user->db->LastError()) . ')<br />';
			}
		}
		else
		{
			$TP['errorMsg'] .= $UNB_T['register.error.validation failed2'] . '<br />';
		}
	}
	elseif ($_GET['resend'] == 1)
	{
		UnbAddLog('debug: register: re-send email');
		if (!SendValidationMail($user))
		{
			$error = $UNB_T['error.smtp'] . '<br />';
			$TP['errorMsg'] .= $error;
			UnbErrorLog($error);
		}
		else
		{
			$TP['registerValidationResent'] = true;
		}
	}
	else
	{
		// ----- Show success and welcome message -----

		$TP['registerWelcome'] = true;

		if (in_array(UNB_GROUP_MEMBERS, UnbGetUserGroups($user->GetID())))
		{
			$TP['registerValidated'] = true;
		}
		else
		{
			switch (rc('new_user_validation'))
			{
			case 2:
				$TP['registerValidationReason'] = 2;
				break;
			case 3:
				$TP['registerValidationReason'] = 3;
				break;
			}
		}
	}
}
else
{
	// ----- Show initial registration <form> -----

	if ($error)
	{
		$TP['errorMsg'] .= $error;
		UnbErrorLog($error);
	}

	if ($UNB['LoginUserID']) $UNB['LoginUser']->SetLastActivity(0);

	$TP['registerFormLink'] = UnbLink('@this', null, true);

	// VeriWord
	if (rc('use_veriword'))
	{
		$TP['registerVeriWordLink'] = UnbLink('@veriword', array('prog_id' => rc('prog_id')), true);
	}

	$TP['headLoginControl'] = false;
	$TP['footLoginControl'] = false;
}

UteRemember('register.html', $TP);

UnbUpdateStat('PageHits', 1);

UnbEndHTML();
?>
