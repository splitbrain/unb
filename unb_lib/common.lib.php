<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// common.lib.php
// Public UNB library file

// Sample code to benchmark function
#$UNB['TP']['UnbLinkCount']++;
#$UNB['UnbLinkStart'] = debugGetMicrotime();
#$UNB['TP']['UnbLinkTime'] += debugGetMicrotime() - $UNB['UnbLinkStart'];

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('endl', "\n");   // New-line character, inspired by C++ STL

require_once(dirname(__FILE__) . '/version.def.php');

// Enable UTF-8 Unicode encoding
//
// Currently only ISO-8859-1 and UTF-8 are recommended, others may or may not work.
//
// Jabber connections always use UTF-8. If you specify another character set, the data needs to be
// converted to UTF-8. PHP can only convert from ISO-8859-1 and will fail to correctly convert from
// other sets.
//
// Be aware that all used language files and anything that contains text to be displayed in the
// browser must be converted to the specified character set if you change it from UTF-8.
//
// If you want to use ISO-8859-1 charset, you may want to remove the line '$unicode = false' from
// the t2h() function in this file. A t2h function with unicode parameter can be found in earlier
// UNB versions.
//
#$UNB['CharSet'] = 'ISO-8859-1';
$UNB['CharSet'] = 'UTF-8';

// You may want to change the board's configuration filename
// These are no real PHP files, the data ist just hidden from browsers inside php code tags
$conf_filename = 'board.conf.php';
$conf_tempname = 'board.conf.tmp.php';

// Prepend a relative pathname if set
if ($UNB['RelativeRoot'])
{
	$conf_filename = TrailingSlash($UNB['RelativeRoot']) . $conf_filename;
	$conf_tempname = TrailingSlash($UNB['RelativeRoot']) . $conf_tempname;
}

// Set a PHP session name (optional, for interoperability with other apps)
// If unset, a default name is generated from the prog_id config option.
#$UNB['SessionName'] = 'mysession';

// We don't want error messages to be displayed from now on
if (!defined('ERR_REPORT_SET')) error_reporting(E_ALL & ~E_NOTICE);
if (!defined('DISPLAY_ERR_SET')) @ini_set('display_errors', 0);

// PHP5 support
if (intval(phpversion()) >= 5)
{
	// The constant PHP5 is true if we're running in a PHP 5 environment.
	define('PHP5', 1);
	@ini_set('zend.ze1_compatibility_mode', 0);
}
else
{
	// [nodoc]
	define('PHP5', 0);
}

// -------------------- Global data initialisation --------------------

$perform_time_start = debugGetMicrotime();

// Unquote magic_quote'd data if needed
if (version_compare(PHP_VERSION, '5.3.0', '<'))
{
	set_magic_quotes_runtime(0);
	if (get_magic_quotes_gpc())
	{
		#$mq_old = array('\\\'', '\\"', '\\\\', '\\0');
		#$mq_new = array('\'',   '"',   '\\',   '');
		foreach ($_GET as $key => $value)
		{
			if (is_string($_GET[$key])) $_GET[$key] = stripslashes($value);
		}
		foreach ($_POST as $key => $value)
		{
			#if (!is_array($_POST[$key])) $_POST[$key] = str_replace($mq_old, $mq_new, $value);
			if (is_string($_POST[$key])) $_POST[$key] = stripslashes($value);
		}
		foreach ($_REQUEST as $key => $value)
		{
			if (is_string($_REQUEST[$key])) $_REQUEST[$key] = stripslashes($value);
		}
		foreach ($_COOKIE as $key => $value)
		{
			if (is_string($_COOKIE[$key])) $_COOKIE[$key] = stripslashes($value);
		}
		foreach ($_FILES as $key => $value)
		{
			$_FILES[$key]['name'] = stripslashes($value['name']);
		}
	}
}

// Remove \x00 from any passed variable for security reasons
foreach ($_GET as $key => $value)
{
	if (is_string($_GET[$key])) $_GET[$key] = str_replace("\x00", '', $value);
}
foreach ($_POST as $key => $value)
{
	if (is_string($_POST[$key])) $_POST[$key] = str_replace("\x00", '', $value);
}
foreach ($_REQUEST as $key => $value)
{
	if (is_string($_REQUEST[$key])) $_REQUEST[$key] = str_replace("\x00", '', $value);
}
foreach ($_COOKIE as $key => $value)
{
	if (is_string($_COOKIE[$key])) $_COOKIE[$key] = str_replace("\x00", '', $value);
}
foreach ($_FILES as $key => $value)
{
	$_FILES[$key]['name'] = str_replace("\x00", '', $value['name']);
}

// unregister_globals :) for more security (except for install/import scripts)
if (ini_get('register_globals') && !$UNB['Installing'])
{
	if (sizeof($_SESSION)) foreach (array_keys($_SESSION) as $key) unset($$key);
	if (sizeof($_GET)) foreach (array_keys($_GET) as $key) unset($$key);
	if (sizeof($_POST)) foreach (array_keys($_POST) as $key) unset($$key);
	if (sizeof($_COOKIE)) foreach (array_keys($_COOKIE) as $key) unset($$key);
	if (sizeof($_SERVER)) foreach (array_keys($_SERVER) as $key) unset($$key);
	if (is_array($_GET['GLOBALS'])) foreach (array_keys($_GET['GLOBALS']) as $key) unset($$key);
	if (is_array($_POST['GLOBALS'])) foreach (array_keys($_POST['GLOBALS']) as $key) unset($$key);
}

// Read the configuration text file
$cf = file($conf_filename);
if ($cf === false)
{
	die('<br /><b>UNB error:</b> The configuration file could not be read. Did you <a href="install.php">install</a> the board?');
}
$conf_utf8 = isUtf8($cf[0]);

$UNB['ConfigFile'] = array();
if (is_array($cf)) foreach ($cf as $line)
{
	$line = ltrim($line);
	if ($line == '') continue;
	if ($line[0] === '#' || trim($line) === '<?php' || trim($line) === '?>') continue;
	$pos = strpos($line, '=');
	$value = trim(substr($line, $pos + 1));
	if ($UNB['CharSet'] === 'UTF-8' && !$conf_utf8) $value = utf8_encode($value);
	if ($pos > 0) $UNB['ConfigFile'][rtrim(substr($line, 0, $pos))] = $value;
}
unset($cf);

if (UnbIsFlooded())
{
	sleep(rand(2, 10));
	die('<br /><b>UNB warning:</b> Too many connections from your IP, flood protection active. Please try again later.');
}
UnbCheckHeadersSent('after initialisation');

define('UNB_NOTIFY_EMAIL', 1);    // Notification via e-mail
#define('UNB_NOTIFY_ICQ', 2);     // unused
define('UNB_NOTIFY_JABBER', 4);   // Notification via Jabber IM
define('UNB_NOTIFY_MASK', 127);   // Bitmask of notification all methods

define('UNB_NOTIFY_BOOKMARK', 128);   // Bookmark the thread (stored with the notification flags)

if (!function_exists('array_fill'))
{
	// Implementation of array_fill function if PHP doesn't provide it
	//
	// This function is pre-defined from PHP 4.2.0 on
	// See the PHP manual for details.
	//
	function array_fill($start, $num, $value)
	{
		// Clean parameters
		$num = intval($num);

		$out = array();
		for ($n = 0; $n < $num; $n++)
		{
			$out[$n + $start] = $value;
		}
		return $out;
	}
}

if (!function_exists('html_entity_decode'))
{
	// Implementation of html_entity_decode function if PHP doesn't provide it
	//
	// This function is pre-defined from PHP 4.3.0 on
	// See the PHP manual for details.
	//
	function html_entity_decode($str)
	{
		// Clean parameters
		$str = strval($str);

		$str = str_replace('&quot;', '"', $str);
		$str = str_replace('&lt;', '<', $str);
		$str = str_replace('&gt;', '>', $str);
		$str = str_replace('&amp;', '&', $str);
		return $str;
	}
}

if (!defined('PHP_EOL'))
{
	// Available since PHP 4.3.10 and PHP 5.0.2
	//
	if (substr(strtoupper(PHP_OS), 0, 3) == 'WIN')
		define('PHP_EOL', "\r\n");
	else
		define('PHP_EOL', "\n");
}

if (!function_exists('stripos'))
{
	// Implementation of stripos function if PHP doesn't provide it
	//
	// This function is pre-defined from PHP 5.0 on
	// See the PHP manual for details.
	//
	function stripos($haystack, $needle, $offset = 0)
	{
		return strpos(strtolower($haystack), strtolower($needle), $offset);
	}
}

// ---------- BEGIN Read configuration settings ----------
$UNB['ProfileExtraNames'] = rc('extra_names', true);
$UNB['ProfileExtraCount'] = sizeof($UNB['ProfileExtraNames']);

// All pathnames need a trailing slash ('/') if they're not empty!
// Library Path, relative to forum.php. Will be used to include UNB PHP library files
$UNB['LibraryPath'] = TrailingSlash(rc('lib_path'));
// Path to user avatars and post attachments
$UNB['AvatarPath'] = $UNB['LibraryPath'] . 'upload/';
$UNB['PhotoPath'] = $UNB['LibraryPath'] . 'upload/';
$UNB['AttachPath'] = $UNB['LibraryPath'] . 'upload/';
// Where to write log files
$UNB['LogPath'] = TrailingSlash(rc('log_path'));

// Library URL. Will be used to refer to files located in lib_path to the browser
$UNB['LibraryURL'] = TrailingSlash(rc('lib_url'));
$UNB['AvatarURL'] = $UNB['LibraryURL'] . 'upload/';
$UNB['PhotoURL'] = $UNB['LibraryURL'] . 'upload/';

// show SQL queries? numeric values will be handled later after login
$show_sql_verb = rc('show_sql');
$UNB['ShowSql'] = !strcasecmp($show_sql_verb, 'on');

$UNB['CharSet'] = strtoupper($UNB['CharSet']);

$UNB['ACL'] = array();

// Automatic language scan
$UNB['AllLangs'] = array();
$UNB['AllLangNames'] = array();
$handle = opendir(dirname(__FILE__) . '/lang');
if ($handle !== false)
{
	while ($file = readdir($handle))
		if (preg_match('/^([A-Za-z0-9-_]{2,6})$/', $file, $m))
		{
			if (is_dir(dirname(__FILE__) . '/lang/' . $m[1]) &&
			    file_exists(dirname(__FILE__) . '/lang/' . $m[1] . '/name.txt'))
			{
				array_push($UNB['AllLangs'], $m[1]);
				$f = file(dirname(__FILE__) . '/lang/' . $m[1] . '/name.txt');
				if (!isUtf8($f[0])) $f[0] = utf8_encode($f[0]);
				$UNB['AllLangNames'][$m[1]] =  $f[0];
			}
		}
	closedir($handle);
	sort($UNB['AllLangs']);
}

// Select default language. This language code MUST be listed in the $UNB['AllLangs'] array.
$UNB['Lang'] = rc('def_lang');
if ($UNB['Lang'] == '' || !in_array($UNB['Lang'], $UNB['AllLangs']))
{
	if (in_array('en', $UNB['AllLangs']))
		$UNB['Lang'] = 'en';
	elseif (in_array($UNB['AllLangs'][0], $UNB['AllLangs']))
		$UNB['Lang'] = $UNB['AllLangs'][0];   // take first language we can find
	else
		die('<br /><b>UNB error:</b> No language installed.');
}

// Select default timezone
// offset = UTC + n/4 hours
// withdst = with daylight saving time enabled
$UNB['Timezone'] = array();
$UNB['Timezone']['local'] = intval(date('Z'));
$UNB['Timezone']['offset'] = 900 * rc('tz_offset');
$UNB['Timezone']['withdst'] = rc('tz_dst');
// Remember board's (i.e. user-independent) setting
$UNB['BoardTimezone']['offset'] = 900 * rc('tz_offset');
$UNB['BoardTimezone']['withdst'] = rc('tz_dst');

if (isset($_COOKIE['UnbTimezone']) && $_COOKIE['UnbTimezone'] != '' && is_numeric($_COOKIE['UnbTimezone']))
{
	$UNB['Timezone']['offset'] = 60 * intval($_COOKIE['UnbTimezone']);
	$UNB['Timezone']['withdst'] = 0;
}

// ---------- END Read configuration settings ----------

// User's LastForum special codes
#define('UNB_ULF_HELP', -1);
define('UNB_ULF_CONFIG', -2);    // Last seen in board configuration
define('UNB_ULF_PROFILE', -3);   // Last seen in user profile
define('UNB_ULF_SEARCH', -4);    // Last seen in search
define('UNB_ULF_STAT', -5);      // Last seen in statistics page
define('UNB_ULF_USERS', -6);     // Last seen in users list

// User forum/thread flags
define('UNB_UFF_COLLAPSE', 1);   // Collapse a category (don't show their subforums in the second level or deeper)
define('UNB_UFF_IGNORE', 2);     // Ignore new topics in a forum
define('UNB_UFF_HIDE', 4);       // Hide a forum from the forums list

// UserGroup IDs
define('UNB_GROUP_GUESTS', 1);   // Guests user group
define('UNB_GROUP_MEMBERS', 2);  // Validated members user group
define('UNB_GROUP_MODS', 3);     // Moderators user group
define('UNB_GROUP_ADMINS', 4);   // Administrators user group
define('UNB_GROUP_MAX', 4);      // Highest value of system user groups

// Include all UNB libraries needed to perform these early functions
require_once(dirname(__FILE__) . '/database.lib.php');
require_once(dirname(__FILE__) . '/stat.lib.php');
require_once(dirname(__FILE__) . '/session.lib.php');
require_once(dirname(__FILE__) . '/user.lib.php');
require_once(dirname(__FILE__) . '/clientinfo.lib.php');
require_once(dirname(__FILE__) . '/plugin.lib.php');
UnbCheckHeadersSent('after loading UNB libraries');

// PLUGINSCAN
// Automatic plugins scan
$UNB['PlugIns'] = array();
$handle = opendir(dirname(__FILE__) . '/plugins');
if ($handle !== false)
{
	while ($file = readdir($handle))
		if (preg_match('/^([A-Za-z0-9\-_]+)\.php$/', $file, $m))
		{
			// Load plug-in

			// Pre-load management
			$id = UnbBeginPlugin($file);
			$UNB['PlugIns'][$id] = array();
			UnbPluginMeta($file, 'file');
			UnbPluginMeta(0, 'active');

			// Include code file
			include(dirname(__FILE__) . '/plugins/' . $file);

			if ($UNB['PlugIns'][$id]['lang'])
			{
				// Make an array of all language codes specified by the plug-in
				$pluglang_a = explode(' ', $UNB['PlugIns'][$id]['lang']);
				foreach ($pluglang_a as $lang)
				{
					UnbPluginMeta($id . '.' . $lang . '.php', 'file');
				}
			}

			// Post-load management
			UnbEndPlugin();
		}
	closedir($handle);
	ksort($UNB['PlugIns']);
}
UnbCheckHeadersSent('after loading plugins');

// Cache arrays
// Every User/Thread/Forum Load() result is stored here for subsequent Load()s of the same record (saves DB queries)
$UNB['ForumCache'] = array();
$UNB['ThreadCache'] = array();
$UNB['UserCache'] = array();

// Open database
$UNB['Db'] = new IDatabase;
$UNB['Db']->server = rc('db_server');
$UNB['Db']->user = rc('db_user');
$pass = rc('db_pass');
if (!strncmp($pass, 'b64:', 4)) $pass = base64_decode(substr($pass, 4));
$UNB['Db']->password = $pass;
$UNB['Db']->dbname = rc('db_name');
$UNB['Db']->tblprefix = rc('db_prefix');
$UNB['Db']->useUTF8 = rc('db_utf8') !== null ? rc('db_utf8') : true;
UnbCallHookN('database.preopen');
if (!$UNB['NoDb']) $UNB['Db']->Open();   // This function won't return in case of an error
UnbCallHookN('database.postopen');
if (!$UNB['Installing'])
	$UNB['Db']->Forget();

// Determine Login Information
$UNB['LoginUserID'] = 0;
$UNB['LoginUserName'] = '';
$UNB['LoginUserGroups'] = array();

// Client IP must stay in the same subnet
// A value of 24 means a subnet mask of 0xFFFFFF00 (255.255.255.0)
// 32 forces the exact same IP; 0 disables the check
$bits = intval(rc('session_ip_netmask'));
if ($bits < 0 || $bits > 32) $bits = 24;
$UNB['SessionNetMask'] = 0xFFFFFFFF << (32 - $bits);

$UNB['LoginUser'] = new IUser;

UnbCallHookN('session.prelogin');

if (UnbCheckSession())
{
	// current session is OK
	$UNB['LoginUserID'] = intval($_SESSION['UnbUserId']);
	// Load the user from the database
	if ($UNB['LoginUser']->Load($UNB['LoginUserID']))
	{
		$UNB['LoginUserName'] = $UNB['LoginUser']->GetName();
	}
	else
	{
		// User could not be loaded. If it's an externally authorised session,
		// here's the chance to initially copy the profile from that external
		// source in an appropriate plugin.
		$data = array('userid' => $UNB['LoginUserID']);
		UnbCallHook('session.newuser', $data);
		// Try again
		if ($UNB['LoginUser']->Load($UNB['LoginUserID']))
		{
			$UNB['LoginUserName'] = $UNB['LoginUser']->GetName();
		}
		else
		{
			// No chance. Remain a guest.
			$UNB['LoginUserID'] = 0;
			$UNB['LoginUserName'] = '';
			$UNB['LoginUserGroups'] = array();
		}
	}
}
if (!$UNB['LoginUserID'] && !rc('no_cookies'))
{
	// try to login with cookie information
	list($cookie_uid, $cookie_pwd) = explode(' ', $_COOKIE['UnbUser-' . rc('prog_id')]);
	if (is_numeric($cookie_uid) && $cookie_uid > 0)
	{
		if ($UNB['LoginUser']->Load($cookie_uid))
		{
			if (UnbCreateSession($cookie_uid, $cookie_pwd, $UNB['LoginUser']->GetPassword(), $UNB['LoginUser']->GetFlags() & 32))
			{
				$UNB['LoginUserID'] = $UNB['LoginUser']->GetID();
				$UNB['LoginUserName'] = $UNB['LoginUser']->GetName();

				$UNB['LoginUser']->SetLastLogin();
			}
			else
			{
				$UNB['LoginUser'] = new IUser;
			}
		}
	}
}
if (!$UNB['LoginUserID'] && !$UNB['Installing'])
{
	// still not logged in -> must be a guest
	// check all saved session IDs for their timeout (24h)
	$UNB['Db']->RemoveRecord('LastActivity<' . (time() - 86400), 'Guests');

	// check this session ID's status
	$lastact = $UNB['Db']->FastQuery1st('Guests', 'LastActivity', 'Session="' . session_id() . '"');
	if (!$lastact)
	{
		$name = $UNB['Client']['is_browser'] ? '' : '_not_a_browser_';

		// session id is NEW, so add it to the list
		$UNB['Db']->AddRecord(array('Session' => session_id(), 'LastActivity' => time(), 'UserName' => $name), 'Guests');
		// update Stat table
		UnbUpdateUserStat();

		// TODO: for debug only
#		if ($UNB['Client']['is_browser'])
#			UnbAddLog('stat;guest;name=' . $name . ';ua=' . $UNB['Client']['ua']);
	}
	else
	{
		// session id is OLD, so update the time in the list
		$UNB['Db']->ChangeRecord(array('LastActivity' => time()), 'Session="' . session_id() . '"', 'Guests');
	}
}

$UNB['DefaultLang'] = $UNB['Lang'];

// Evaluate Accept-Language header and guests' session/cookie language selection
$lang_arr = explode(',', $UNB['Client']['lang']);
if (!$UNB['LoginUserID'])
{
	if (!rc('no_cookies'))
		$lang_arr = array_merge(array($_SESSION['UnbPrefLanguage'], $_COOKIE['UnbPrefLanguage']), $lang_arr);
	else
		$lang_arr = array_merge(array($_SESSION['UnbPrefLanguage']), $lang_arr);
}
$setlang = false;
foreach ($lang_arr as $a)
{
	foreach ($UNB['AllLangs'] as $lang)
	{
		if (!$setlang && !strncasecmp($a, $lang, strlen($lang)))
		{
			$UNB['Lang'] = $lang;
			$setlang = true;
		}
	}
}

// Debug setting
if (!strcasecmp($show_sql_verb, 'on')) $UNB['ShowSql'] = true;
elseif (is_numeric($show_sql_verb) && $show_sql_verb > 0) $UNB['ShowSql'] = ($UNB['LoginUserID'] == $show_sql_verb);
else $UNB['ShowSql'] = false;

if ($UNB['ShowSql']) $UNB['ContentType'] = 'text/html';

if ($UNB['LoginUserID'])
{
	$lang = $UNB['LoginUser']->GetLanguage();
	if ($lang != '' && in_array($lang, $UNB['AllLangs'])) $UNB['Lang'] = $lang;

	$timezone = $UNB['LoginUser']->GetTimezone();
	if ($timezone != 99)
	{
		$UNB['Timezone']['offset'] = $timezone * 900;
		$timezoneds = $UNB['LoginUser']->GetTimezoneDS();
		if ($timezoneds != -1) $UNB['Timezone']['withdst'] = $timezoneds;
	}

	// Find all groups which current user is member of
	$UNB['LoginUserGroups'] = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $UNB['LoginUserID'], '`Group`')
	or $UNB['LoginUserGroups'] = array();
	// Remove all group memberships if not in Members(2) or Admins(4) group
	if (sizeof($UNB['LoginUserGroups']) &&
	    !in_array(UNB_GROUP_MEMBERS, $UNB['LoginUserGroups']))
	{
		if (in_array(UNB_GROUP_ADMINS, $UNB['LoginUserGroups']))
		{
			$UNB['LoginUserGroups'][] = UNB_GROUP_MEMBERS;
		}
		else
		{
			$UNB['LoginUserGroups'] = array();
		}
	}
}

// Include language file
if (!$UNB['Lang']) $UNB['Lang'] = 'en';   // TODO: what causes this error?
$UNB['Lang'] = preg_replace('/[^a-z0-9-_]/i', '', $UNB['Lang']);
UnbRequireTxt('common');
UnbCheckHeadersSent('after loading "common" translation file');

if ($UNB['LoginUserID'])
{
	$dateformat = $UNB['LoginUser']->GetDateFormat();
	if ($dateformat != '') $UNB_T['dateformat.short'] = $dateformat;
}

// PLUGINLANG
// Load additional language files for plug-ins
if (is_array($UNB['PlugIns'])) foreach ($UNB['PlugIns'] as $id => $plugin)
{
	if ($plugin['lang'])
	{
		// Make an array of all language codes specified by the plug-in
		$pluglang_a = explode(' ', $plugin['lang']);
		$done = false;
		if (!$done /*&& in_array($UNB['Lang'], $pluglang_a)*/)
		{
			if (file_exists(dirname(__FILE__) . '/plugins/' . $id . '.' . $UNB['Lang'] . '.php'))
			{
				// Currently selected board language is supported by plug-in
				include(dirname(__FILE__) . '/plugins/' . $id . '.' . $UNB['Lang'] . '.php');
				$done = true;
			}
		}
		if (!$done /*&& in_array($UNB['DefaultLang'], $pluglang_a)*/)
		{
			if (file_exists(dirname(__FILE__) . '/plugins/' . $id . '.' . $UNB['DefaultLang'] . '.php'))
			{
				// Board default language is supported by plug-in
				include(dirname(__FILE__) . '/plugins/' . $id . '.' . $UNB['DefaultLang'] . '.php');
				$done = true;
			}
		}
		if (!$done /*&& in_array('en', $pluglang_a)*/)
		{
			if (file_exists(dirname(__FILE__) . '/plugins/' . $id . '.en.php'))
			{
				// Board default language is supported by plug-in
				include(dirname(__FILE__) . '/plugins/' . $id . '.en.php');
				$done = true;
			}
		}
		if (!$done)
		{
			if (file_exists(dirname(__FILE__) . '/plugins/' . $id . '.' . $pluglang_a[0] . '.php'))
			{
				// Languages could not be matched -> use first specified plug-in language
				include(dirname(__FILE__) . '/plugins/' . $id . '.' . $pluglang_a[0] . '.php');
				$done = true;
			}
		}
		if (!$done)
		{
			UnbErrorLog('Fatal error: Could not include any translation file for plugins/' . t2h($id) . '.<br />');
		}
	}
}
UnbCheckHeadersSent('after loading plugin translations');

// Select the smilies set to be used. Must be set before abbc.lib.php ic included.
$ABBC['Config']['smileset'] = rc('smileset');

require_once(dirname(__FILE__) . '/abbc.lib.php');

switch (rc('nice_quotes_style'))
{
/*	case 1:   // default (en)
		$ABBC['Config']['use_nicequotes'] = true;
		$ABBC['Config']['nicequote_ls'] = '&lsquo;';
		$ABBC['Config']['nicequote_rs'] = '&rsquo;';
		$ABBC['Config']['nicequote_ld'] = '&ldquo;';
		$ABBC['Config']['nicequote_rd'] = '&rdquo;';
		break;
	case 2:   // begin bottom (de)
		$ABBC['Config']['use_nicequotes'] = true;
		$ABBC['Config']['nicequote_ls'] = '&sbquo;';
		$ABBC['Config']['nicequote_rs'] = '&lsquo;';
		$ABBC['Config']['nicequote_ld'] = '&bdquo;';
		$ABBC['Config']['nicequote_rd'] = '&ldquo;';
		break;
	case 3:   // angle quotes (fr)
		$ABBC['Config']['use_nicequotes'] = true;
		$ABBC['Config']['nicequote_ls'] = '&lsaquo;';
		$ABBC['Config']['nicequote_rs'] = '&rsaquo;';
		$ABBC['Config']['nicequote_ld'] = '&laquo;';
		$ABBC['Config']['nicequote_rd'] = '&raquo;';
		break;
	case 4:   // inversed angle quotes
		$ABBC['Config']['use_nicequotes'] = true;
		$ABBC['Config']['nicequote_ls'] = '&rsaquo;';
		$ABBC['Config']['nicequote_rs'] = '&lsaquo;';
		$ABBC['Config']['nicequote_ld'] = '&raquo;';
		$ABBC['Config']['nicequote_rd'] = '&laquo;';
		break;*/
	case 0:
	default:
		$ABBC['Config']['use_nicequotes'] = false;
}

// NOTE,FIXME: nice quotes are currently limited to 0 or 1 due to display errors
// TODO:
// * closing de-quote is wrong AFAIK, should be changed to en opening
// * '(' must be recognised as whitespace before opening quotes. other characters, too?
// * only replace opening+closing quotes together, otherwise 'abc will be changed into an opening quote...
// * Quotes eat away \'s
// * Nice quotes should be moved to the translation file since they're language-dependant

// Design definition
// Needs database to be opened for config setting and logged in user to be determined for user's private design setting
$UNB['Design'] = array();
$UNB['Image'] = array();
$UNB['TP']['UNBImage'] =& $UNB['Image'];

// Load other libraries
require_once(dirname(__FILE__) . '/forum.lib.php');
require_once(dirname(__FILE__) . '/thread.lib.php');

if (!$UNB['Installing'])
	UnbReadACL();

// Find all available designs
$UNB['DesignList'] = array();
$handle = opendir(dirname(__FILE__) . '/designs');
if ($handle === false)
{
	die('UNB error: cannot find my designs');
}
else
{
	while ($file = readdir($handle))
		if (preg_match('/^([A-Za-z0-9\-]+)$/', $file, $m))
		{
			$basepath = dirname(__FILE__) . '/designs/' . $file . '/';

			// Check design files
			$haveName = file_exists($basepath . 'name.txt') &&
				is_file($basepath . 'name.txt') &&
				is_readable($basepath . 'name.txt');
			$haveConfig = file_exists($basepath . 'config.php') &&
				is_file($basepath . 'config.php') &&
				is_readable($basepath . 'config.php');

			if ($haveName)
			{
				$name = file($basepath . 'name.txt');
				if (is_array($name)) $name = $name[0];
				if (!isUtf8($name) && $UNB['CharSet'] == 'UTF-8') $name = utf8_encode($name);
				$name = trim($name);
				if ($name == '') $haveName = false;
			}

			if ($haveName && $haveConfig)
			{
				// Looks like a usable design
				$d = array();
				$d['name'] = $file;
				$d['title'] = $name;
				$UNB['DesignList'][$file] = $d;
			}
		}
	closedir($handle);
	ksort($UNB['DesignList']);
}

// Determine current design from board config and user profile
$current_design = 'modern';
if (array_key_exists(rc('design'), $UNB['DesignList']))
	$current_design = rc('design');
if ($UNB['LoginUserID'] &&
    (!rc('nouserdesign') || UnbCheckRights('is_admin')) &&
    $UNB['LoginUser']->GetDesign() != '' &&
    array_key_exists($UNB['LoginUser']->GetDesign(), $UNB['DesignList']))
	$current_design = $UNB['LoginUser']->GetDesign();
if (isset($_REQUEST['setdesign']) &&
    (!rc('nouserdesign') || UnbCheckRights('is_admin')) &&
    trim($_REQUEST['setdesign']) != '' &&
    array_key_exists(trim($_REQUEST['setdesign']), $UNB['DesignList']))
	$current_design = trim($_REQUEST['setdesign']);

// Switch back to Text-Only design for some browsers
if ($UNB['Client']['browser'] === 'nn' && $UNB['Client']['b_ver'] < 6) $useTextOnly = true;
if ($UNB['Client']['browser'] === 'text') $useTextOnly = true;
if ($useTextOnly &&
    array_key_exists('textonly', $UNB['DesignList']))
{
	$current_design = 'textonly';
	$UNB['TextOnly'] = true;
}

// Load currently selected design
$UNB['Design']['CurrentDesign'] = $current_design;
require(dirname(__FILE__) . '/designs/' . $current_design . '/config.php');
UnbCheckHeadersSent('after loading design');

// Now that we know what design is selected, we can include the template runtime
// that needs to know the design path to build its template files path.
require_once(dirname(__FILE__) . '/ute-runtime.lib.php');

// ADMIN-LOCK CHECK
if (rc('admin_lock') && !$UNB['Installing'] && !UnbCheckRights('is_admin'))
{
	// When the board is locked, only administrators can log in. Other login requests
	// will be denied "gracefully", that is the session is not fully destroyed and the
	// auto-re-login cookies are not removed so that a later automatic login is still
	// possible when the board is unlocked again.
	UnbLogoutNoTermSession();
	$UNB['LoginUserID'] = 0;
	$UNB['LoginUserName'] = '';
	$UNB['LoginUserGroups'] = array();
}

UnbCallHookN('session.postlogin');


// ==================== Generic-purpose functions ====================

// Time measuring functions
function debugGetMicrotime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}
function debugPerformMsec()
{
	global $perform_time_start;
	return round((debugGetMicrotime() - $perform_time_start) * 1000, 1);
}
function debugPerformCputime()
{
	if (!function_exists('getrusage')) return 0;   // only available in unix/linux, not in windows
	$a = @getrusage();
	return round((float)$a['ru_utime.tv_sec'] * 1000 + (float)$a['ru_utime.tv_usec'] / 1000, 1);
}

// debug performance measuring and output
//
// text = -1: reset | false: start | 1: pause | 2/text: end/text
//
function debugMeasureTime($text = false)
{
	global $perform_time_start_tmp, $perform_time_tmp, $perform_count_tmp;
	global $UNB, $perform_dbtime_start_tmp, $perform_dbtime_tmp;

	if ($text === -1)
	{
		$perform_time_start_tmp = $perform_count_tmp = $perform_time_tmp = 0;
		$perform_dbtime_start_tmp = $perform_dbtime_tmp = 0;
		echo '<b>dp</b>: (reset)<br />';
	}
	else if ($text === false)
	{
		if (!isset($perform_time_start_tmp)) $perform_count_tmp = $perform_time_tmp = 0;
		if (!isset($perform_dbtime_start_tmp)) $perform_dbtime_tmp = 0;
		$perform_count_tmp++;
		$perform_time_start_tmp = debugGetMicrotime();
		if ($UNB['Db']) $perform_dbtime_start_tmp = $UNB['Db']->GetTime();
		echo '<b>dp</b>: (start)<br />';
	}
	else if ($text === 1)
	{
		$perform_time_tmp += debugGetMicrotime() - $perform_time_start_tmp;
		if ($UNB['Db']) $perform_dbtime_tmp += $UNB['Db']->GetTime() - $perform_dbtime_start_tmp;
		$x = round((debugGetMicrotime() - $perform_time_start_tmp) * 1000, 2);
		if ($UNB['Db']) $y = round(($UNB['Db']->GetTime() - $perform_dbtime_start_tmp), 2);
		echo '<b>dp</b>: ' . $x . ' - ' . $y . ' = ' . ($x - $y) . ' ms (pause)<br />';
		$perform_time_start_tmp = 0;
		$perform_dbtime_start_tmp = 0;
	}
	else
	{
		if ($perform_time_start_tmp) $perform_time_tmp += debugGetMicrotime() - $perform_time_start_tmp;
		if ($UNB['Db']) if ($perform_dbtime_start_tmp) $perform_dbtime_tmp += $UNB['Db']->GetTime() - $perform_dbtime_start_tmp;
		$x = round($perform_time_tmp * 1000, 1);
		$y = round($perform_dbtime_tmp, 1);
		echo '<b>dp</b>: ' . $x . ' - ' . $y . ' = ' . ($x - $y) . ' ms' .
			($perform_count_tmp > 1 ? " ($perform_count_tmp x " . round($x / $perform_count_tmp, 3) . " ms)" : '') .
			(is_string($text) ? ' - ' . $text : '') . ' (end)<br />';
		$perform_time_tmp = 0;
		$perform_count_tmp = 0;
		$perform_dbtime_tmp = 0;
	}
}

// ==================== Generic data processing functions ====================

// Scan a line for the UTF-8 marker and remove it
//
// in,out line = (string) first line of the file
//
// returns (bool) file is UTF-8-encoded
//
function isUtf8(&$line)
{
	if (substr($line, 0, 3) == "\xEF\xBB\xBF")
	{
		$line = substr($line, 3);   // remove UTF-8 marker
		return true;
	}
	return false;
}

// Add or remove a trailing slash to pathnames
//
// in path = (string) pathname
// in add = (bool) true: add a slash if it's not there
//                 false: remove a slash if it is there
//
// returns (string) modified pathname
//
function TrailingSlash($path, $add = true)
{
	// Clean parameters
	$path = trim(strval($path));

	if ($path == '') return '';
	if (substr($path, strlen($path) - 1, 1) != '/' && $add) $path .= '/';
	if (substr($path, strlen($path) - 1, 1) == '/' && !$add) $path = substr($path, 0, strlen($path) - 1);
	return $path;
}

// Builds links to other forum and web pages in a unified way
//
// NOTE: How to change the 'req' request parameter: Search all files for the
// REQUEST-PARAMETER tag and replace occurences of the string 'req' with your
// new parameter name.
//
// NOTE: How to change the page request keys: Search all files for the
// REQUEST-NAME tag and replace occurences of the request key string (not the
// ones with the @ in front) with your new key name.
//
// in url = (string) URL to other website
// in params = (array(name => value)) all GET parameters
//             (string) short simple parameters (this is only an ugly hack because I'm too lazy sometimes... ;))
//                      example: "page=4&code=0"
// in html = (bool) html-quote the link
// in sid = (bool) include session ID if required. forced false if $derefer
// in derefer = (bool) use dereferer
// in allowshort = (bool) allow shortened URLs. should be false for GET forms
// in complete = (bool) auto-complete URL by adding http:// if not present
// in parse = (bool) parse URL for parameters
//
// returns (string) correctly built URL
//
function UnbLink($url, $params = null, $html = false, $sid = true, $derefer = false, $allowshort = true, $complete = false, $parse = true)
{
	global $UNB;

	// Clean parameters
	$url = trim(strval($url));
	if (!isset($params)) $params = array();
	if (is_string($params))
	{
		// Handle anchor link in params
		if ($pos = strpos($params, '#'))
		{
			$anchor = substr($params, $pos + 1);
			$params = substr($params, 0, $pos);
		}

		parse_str($params, $params2);
		$params = $params2;
	}

	// Handle anchor link in URL
	if ($pos = strpos($url, '#'))
	{
		$anchor = substr($url, $pos + 1);
		$url = substr($url, 0, $pos);
	}

	// Rewrite certain links for search engines
	if (!$UNB['Client']['is_browser'])
	{
		// Convert direct post links to thread[+page] links
		if ($url == '@thread' && $params['postid'] > 0)
		{
			$post = new IPost(intval($params['postid']));
			$params['id'] = $post->GetThread();
			if (rc('posts_per_page') > 0)
				$params['page'] = ceil($post->Count('Thread=' . $post->GetThread() . ' AND Date<=' . $post->GetDate()) / rc('posts_per_page'));
			$params['postid'] = null;
		}
	}

	do
	{
		// loop this parameter splitting as long as the URL was rewritten
		// to allow URL parameters to be included in the basefile URL
		$rewritten = false;

		// Scan URL and separate additional parameters
		if ($parse && ($pos = strpos($url, '?')) !== false)
		{
			$qs = substr($url, $pos + 1);
			$params2 = array();
			$a = explode('&', $qs);
			foreach ($a as $e)
			{
				list($k, $v) = explode('=', $e, 2);
				if ($v === null) $v = false;
				$params2[$k] = $v;
			}
			//parse_str($qs, $params2);
			$params = array_merge($params2, $params);
			$url = substr($url, 0, $pos);
		}

		// Replace shortcuts by our own pages
		if ($url[0] === '@')
		{
			$baseurl = rc('baseurl');
			if ($url === '@this') $url = $UNB['ThisPage'];
			$req = null;
			// REQUEST-NAME (see common.lib:UnbLink() for more details)
			switch ($url)
			{
				case '@cp': $url = $baseurl; $req = 'cp'; break;
				case '@derefer': $url = $baseurl; $req = 'derefer'; break;
				case '@main': $url = $baseurl; $req = 'main'; break;
				case '@post': $url = $baseurl; $req = 'post'; break;
				case '@register': $url = $baseurl; $req = 'register'; break;
				case '@rss': $url = $baseurl; $req = 'rss'; break;
				case '@search': $url = $baseurl; $req = 'search'; break;
				case '@setuser': $url = $baseurl; $req = 'setuser'; break;
				case '@showip': $url = $baseurl; $req = 'showip'; break;
				case '@stat': $url = $baseurl; $req = 'stat'; break;
				case '@thread': $url = $baseurl; $req = 'thread'; break;
				case '@users': $url = $baseurl; $req = 'users'; break;
				case '@veriword': $url = $baseurl; $req = 'veriword'; break;
				default:
					$data = array('url' => &$url, 'request' => &$req, 'params' => &$params);
					UnbCallHook('link.shortcut.custom', $data);
			}
			if ($url[0] === '@')
			{
				UnbErrorLog('Invalid page reference in UnbLink: url=' . $url);
				$url = $baseurl; $req = '';
			}
			if (isset($req))
			{
				// Prepend the req parameter rather than append it
				// REQUEST-PARAMETER (see common.lib:UnbLink() for more details)
				$params = array_merge(array('req' => $req), $params);
			}
			$rewritten = true;
			$parse = true;   // the URL has changed and parameter parsing may be required now
		}
	}
	while ($rewritten);

	$link = $url;

	$pcount = 0;
	$sep = '&';

	// Find special cases for short URLs
	// NOTE: This depends on the UNB modules definition!
	// REQUEST-PARAMETER (see common.lib:UnbLink() for more details)
	// REQUEST-NAME (see common.lib:UnbLink() for more details)
	if ($allowshort && rc('mod_rewrite_urls') && $url == rc('baseurl'))
	{
		if ($params['req'] == 'main' && $params['id'] == 0)
		{
			$link = rc('url_overview');
			if (!strlen($link))
				$link = 'forum';
			$params['req'] = null;
			$params['id'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'main' && $params['id'] > 0)
		{
			//$link = 'f.' . $params['id'];
			$link = 'forum/' . $params['id'];

			if (rc('named_urls'))
			{
				// Include forum name in URL - SEO
				$forum = new IForum(intval($params['id']));
				$link .= UnbTextToURL($forum->GetName());
			}

			$params['req'] = null;
			$params['id'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'thread' && $params['id'] > 0 && ($params['page'] > 1 || $params['page'] == -1))
		{
			$link = 'thread/' . $params['id'] . ',' . $params['page'];

			if (rc('named_urls'))
			{
				// Include thread subject in URL - SEO
				$thread = new IThread(intval($params['id']));
				$link .= UnbTextToURL($thread->GetSubject());
			}

			$params['req'] = null;
			$params['id'] = null;
			$params['page'] = null;

			$pcount = 2;
			$sep = ';';
		}
		else if ($params['req'] == 'thread' && $params['id'] > 0)
		{
			//$link = $params['id'];
			$link = 'thread/' . $params['id'];

			if (rc('named_urls'))
			{
				// Include thread subject in URL - SEO
				$thread = new IThread(intval($params['id']));
				$link .= UnbTextToURL($thread->GetSubject());
			}

			$params['req'] = null;
			$params['id'] = null;
			if ($params['page'] == 1) $params['page'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'thread' && $params['postid'] > 0)
		{
			//$link = 'p.' . $params['postid'];
			$link = 'post/' . $params['postid'];
			//$params['#'] = 'p' . $params['postid'];
			$params['req'] = null;
			$params['postid'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'cp' && $params['cat'] != '' && ($params['id'] == 0 || $params['id'] == $UNB['LoginUserID']))
		{
			$link = 'cp';
			$params['req'] = null;
			$params['id'] = null;
			if (isset($params['saved'])) $params['saved'] = $params['saved'] == 1;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'cp' && $params['id'] > 0)
		{
			//$link = 'u.' . $params['id'];
			$link = 'user/' . $params['id'];
			$params['req'] = null;
			$params['id'] = null;
			if (isset($params['saved'])) $params['saved'] = $params['saved'] == 1;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'users')
		{
			$link = 'users';
			$params['req'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'search' && $params['Special'] == 'new')
		{
			$link = 'new';
			$params['req'] = null;
			$params['nodef'] = null;
			$params['Special'] = null;
			$params['ResultView'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'search' && $params['Special'] == 'unread')
		{
			$link = 'unread';
			$params['req'] = null;
			$params['nodef'] = null;
			$params['Special'] = null;
			$params['ResultView'] = null;
			if (isset($params['ShowHidden'])) $params['ShowHidden'] = $params['ShowHidden'] == 1;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'search' && $params['Special'] == 'mytopics')
		{
			$link = 'mytopics';
			$params['req'] = null;
			$params['nodef'] = null;
			$params['Special'] = null;
			$params['ResultView'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'search')
		{
			$link = 'search';
			$params['req'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else if ($params['req'] == 'stat')
		{
			$link = 'stat';
			$params['req'] = null;

			$pcount = 1;
			$sep = ';';
		}
		else
		{
			$data = array('params' => &$params, 'link' => &$link, 'handled' => false);
			UnbCallHook('link.shorturl.custom', $data);
			if ($data['handled'])
			{
				$pcount = 1;
				$sep = ';';
			}
		}
	}

	if ($derefer) $sid = false;   // That makes no sense

	// Include custom per-session design
	if ($sid &&
	    $UNB['Client']['is_browser'] &&
	    isset($_REQUEST['setdesign']))
	{
		$params['setdesign'] = $_REQUEST['setdesign'];
	}

	// Include PHP session ID
	// NOTE: This must be the very last parameter!
	if ($sid &&
	    SID &&
	    $UNB['Client']['is_browser'] &&
	    session_name() &&
	    !$_COOKIE['UnbUser-' . rc('prog_id')])   // If we have the auto-login cookie, session cookies will also be saved -> no need for the URL parameter
	{
		$params[session_name()] = session_id();
	}

	// Remove certain parameters for search engines
	if (!$UNB['Client']['is_browser'])
	{
		$params['nocount'] = null;   // TODO: remove when 'nocount' parameter is stored in the session
	}

	// Build link
	// Add GET parameters
	foreach ($params as $n => $v)
	{
		if ($n !== '#' && $v !== null)
		{
			if ($sep === ';' && $n === session_name())
				$sep = ';?';

			if ($v === true)
				$v = ($sep === ';') ? '' : '=1';
			elseif ($v === false)
				$v = '';
			else
				$v = '=' . urlencode($v);

			$link .= ($pcount++ === 0 ? '?' : $sep) . urlencode($n) . $v;
		}
	}

	// Add page anchor
	if ($anchor) $params['#'] = $anchor;
	if ($params['#'] != '')
	{
		$link .= '#' . $params['#'];
	}

	// Auto-complete URL
	if ($complete && (!preg_match('_^[a-z]+:_i', $link) || preg_match('_^javascript:_i', $link)))
	{
		$link = 'http://' . $link;
	}

	// Use dereferer only if the session ID is in the current page's URL
	// and we're linking out of our own domain+path
	if ($derefer && $_GET[session_name()] != '' && substr($link, 0, strlen(rc('home_url'))) != rc('home_url'))
	{
		$link = UnbLink(
			'@derefer',
			array('url' => $link),
			/*html, done once in this level*/ false,
			/*sid*/ false,
			/*derefer*/ false,
			$allowshort);
	}

	// HTML-quote link output
	if ($html) $link = htmlspecialchars($link);

	return $link;
}

// Build session ID for GET <form>s
//
function UnbFormSessionId()
{
	global $UNB;
	$out = '';

	// Include PHP session ID
	if (SID &&
	    $UNB['Client']['is_browser'] &&
	    session_name())
	{
		$out .= '<input type="hidden" name="' . t2i(session_name()) . '" value="' . t2i(session_id()) . '" />';
	}

	// Include custom per-session design
	if (isset($_REQUEST['setdesign']))
	{
		$out .= '<input type="hidden" name="setdesign" value="' . t2i($_REQUEST['setdesign']) . '" />';
	}

	return $out;
}

// Generate a 'unique' URL parameter key to secure most GET-requested operations
// from anonymous calls like [img]s in posts.
//
// see UnbUrlCheckKey()
//
function UnbUrlGetKey()
{
	return sprintf('%08X', crc32(session_id()));
}

// Checks the provided URL GET key parameter.
//
// see UnbUrlGetKey()
//
function UnbUrlCheckKey()
{
	// CSRF countermeasure:
	// Everywhere this function is called, a key is to be checked that is intended to verify that
	// the HTTP request is genuinely intended by the session user and not some kind of
	// image-request attack. CSRF is similar and thus can be transparently handled here, too.
	// To do so, the HTTP Referer header is checked whether it comes from one of this domain's
	// web pages. Since most browsers send this for every request (including XmlHttpRequest), there
	// is little chance for an attacker to use others' browsers for his attack. Of course the
	// referer can only be regarded when it's set. Some network or privacy filters may remove that
	// header on the network level and make it impossible to detect such an attack.
	//
	global $UNB;
	if ($UNB['Client']['referer'] !== '' && !$UNB['Client']['ref_mydomain']) return false;

	return $_REQUEST['key'] == sprintf('%08X', crc32(session_id()));
}

// Convert a given string to make it URL-safe (SEO-like)
//
// in str = (string) Text to convert. Will replace spaces and other characters by "-"
//
// returns (string) converted string
//
function UnbTextToURL($str)
{
	$str = '-' . substr(UnbToANSI($str), 0, 50);
	$str = preg_replace('_[^a-z0-9]_i', '-', $str);
	$str = preg_replace('_--+_', '-', $str);
	$str = preg_replace('_-$_', '', substr($str, 0, 50));
	return $str;
}

// Convert a given string with national special characters to its ANSI representation
//
// in str = (string) Text to convert
//
// returns (string) converted string
//
function UnbToANSI($str)
{
	// Use this function to determine a UTF-8 sequence:
	#$s = utf8_encode(".");   // <-- put character in there
	#for ($i = 0; $i < strlen($s); $i++) echo "\\x" . strtoupper(dechex(ord($s{$i})));

	$r1 = array(
		"\xC3\x84",   // Auml
		"\xC3\x96",   // Ouml
		"\xC3\x9C",   // Uuml
		"\xC3\xA4",   // auml
		"\xC3\xA9",   // eacute
		"\xC3\xA8",   // egrave
		"\xC3\xB6",   // ouml
		"\xC3\xBC",   // uuml
		"\xC3\x9F",   // szlig
		);
	$r2 = array(
		"Ae",   // Auml
		"Oe",   // Ouml
		"Ue",   // Uuml
		"ae",   // auml
		"e",    // eacute
		"e",    // egrave
		"oe",   // ouml
		"ue",   // uuml
		"ss",   // szlig
		);

	$str = str_replace($r1, $r2, $str);
	return $str;
}

// Text-to-HTML conversion
//
// Masks special HTML characters: & < >
// Unicode-Safe (optional: $unicode): "&#...;" will not be replaced since this is
//     the alternative description for Unicode characters, by browsers handling Unicode characters
//     with an ANSI page encoding
//
// in text = (string) text to encode
// in spaces = (bool) mask multiple subsequent " " in a way that (almost) no space character gets lost
//                    (with "  " -> "&nbsp; ")
// in quotes = (bool) replace double quotes with &quot;
// in nl2br = (bool) replace \n line breaks with <br />
// in keepascii = (int) don't replace ASCII codes from 1 to n. can be 7 at most.
//
function t2h($text, $spaces = true, $quotes = true, $nl2br = false, $keepascii = 0)
{
	// Clean parameters
	$text = strval($text);

	$text = preg_replace('/&/', '&amp;', $text);
	$text = preg_replace('/</', '&lt;', $text);
	$text = preg_replace('/>/', '&gt;', $text);
	if ($quotes)
	{
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace('\'', '&#39;', $text);
	}

	// multiple spaces and tabs
	if ($spaces)
	{
		$text = preg_replace('/\t/', '    ', $text);
		$text = preg_replace('/  /', '&nbsp; ', $text);
		$text = preg_replace('/  /', '&nbsp; ', $text);
	}

	if ($nl2br)
	{
		$text = nl2br($text);
	}

	// remove any lower ASCII control character except HT (0x09) and LF (0x0A)
	$text = preg_replace('_[\x00\x0' . ($keepascii + 1) . '-\x08\x0B-\x0C\x0E-\x1F]_', "\xEF\xBF\xBD", $text);

	return $text;
}

// Text to HTML-Input-value conversion
//
// Change " into &quot; (and other HTML entity replacements)
// Good for use with <input value="...">
//
// This also removes any line breaks which must not occur in HTML tag attributes.
// removing line breaks especially fixes an XML error for the ABBC parser that would
// replace \n by <br/> in the end, leading to a <br/> tag inside the attribute which
// is invalid XML.
//
function t2i($str)
{
	$str = str_replace(array("\r", "\n"), array('', ''), $str);
	return t2h($str, false);   // no spaces conversion here!
}

// Reverse function of t2h, convert HTML code to plaintext
//
function h2t($text)
{
	// Clean parameters
	$text = strval($text);

	$text = str_replace('&lt;', '<', $text);
	$text = str_replace('&gt;', '>', $text);
	$text = str_replace('&quot;', '"', $text);
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&amp;', '&', $text);
	return $text;
}

// Don't allow "javascript:" to appear at the beginning of a string
// used in image URL checks
//
function nojs($str)
{
	$a = $str;
	$a = preg_replace('/(?:&#0*([0-9]+);)/e', 'chr($1)', $a);
	$a = preg_replace('/(?:&#x([0-9A-Fa-f]+);)/e', 'chr(hexdec(\'$1\'))', $a);
	$a = preg_replace('/[\x00-\x20]/', '', $a);
	if (preg_match('/^javascript:/i', $a)) return '';
	return $str;
}

// UTF-8-aware string length counting
//
function utf8_strlen($str)
{
	// Clean parameters
	$str = strval($str);

	// PHP 5 optimisation
	if (PHP5 && function_exists('iconv_strlen'))
		return iconv_strlen($str, 'UTF-8');

	$count = 0;
	$len = strlen($str);
	for ($i = 0; $i < $len; $i++)
	{
		$value = ord($str[$i]);
		if ($value > 127)
			if     ($value >= 224 && $value <= 239) $i += 2;
			elseif ($value >= 240 && $value <= 247) $i += 3;
			else   $i++;  /* 192...223 */
		$count++;
	}
	return $count;
}

// UTF-8-aware string extraction method
//
function utf8_substr($str, $mbstart, $mblen = null)
{
	// Clean parameters
	$str = strval($str);

	// PHP 5 optimisation
#	if (PHP5 && function_exists('iconv_substr'))
#		return iconv_substr($str, $mbstart, $mblen, 'UTF-8');
	// TODO,FIXME: iconv_substr returns false when it isn't supposed to. can't use that function

	$pos = 0;         // current ascii string index
	$mbpos = 0;       // current multibyte symbol index
	$start = false;   // where to start copy then
	$len = 0;         // number of bytes to copy
	while ($pos < strlen($str))
	{
		$value = ord($str[$pos]);
		if ($value > 127)
			if     ($value >= 224 && $value <= 239) $bytes = 3;
			elseif ($value >= 240 && $value <= 247) $bytes = 4;
			else   $bytes = 2;  /* 192...223 */
		else $bytes = 1;

		if ($mbpos == $mbstart) $start = $pos;   // this may be our starting symbol index
		if ($start !== false) $len += $bytes;   // if we're in-index, add number of bytes of this symbol
		if (isset($mblen) && $mbpos - $mbstart + 1 == $mblen) break;   // stop condition

		$mbpos++;
		$pos += $bytes;
	}
	if ($start === false) return '';
	return substr($str, $start, $len);
}

// NOTE: we can also use iconv_strpos() and iconv_strrpos() if required. (Available in PHP5)

// Convert a Unicode character index to UTF-8 multi-byte encoding
//
// in num = (int) character index
//
// returns (string) UTF-8 representation
//
function code2utf($num)
{
	// Clean parameters
	$num = intval($num);

	if ($num < 128) return chr($num);
	if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	return '';
}

// Limit a string to a given length and add '...' if it was truncated
//
// in multibyte = (bool) use utf8-aware string split functions. only if current character set is UTF-8
//
function str_limit($str, $len, $multibyte = false, $html = false)
{
	global $UNB;

	// Clean parameters
	$str = strval($str);

	$ellipsis = ($UNB['CharSet'] === 'UTF-8' ? code2utf(0x2026) : chr(133));   // use special ellipsis character
	#$ellipsis = '...';   // use simple dots

	if ($multibyte && $UNB['CharSet'] === 'UTF-8')
	{
		if (utf8_strlen($str) <= $len) return $str;
		if (!$html) return utf8_substr($str, 0, $len - 1) . $ellipsis;
		$insth = 0;   // in something (= HTML tag: 1 or entity: 2)
		$tags = array();
		for ($pos = 0; $insth || $pos < $len - 1; $pos++)
		{
			$c = utf8_substr($str, $pos, 1);
			if ($c == '<')
			{
				if (utf8_substr($str, $pos + 1, 1) == '/')   // tag is closing
					array_pop($tags);
				else if (preg_match('_^<([a-z]+?)[ >]_', utf8_substr($str, $pos), $m))
					$tags[] = $m[1];
				$insth = 1;
			}
			if ($c == '>' && $insth == 1)
			{
				$insth = 0;
				if (utf8_substr($str, $pos - 1, 1) == '/')   // tag was closing itself
					array_pop($tags);
			}
			if ($c == '&') $insth = 2;
			if ($c == ';' && $insth == 2) $insth = 0;
		}
		$str = utf8_substr($str, 0, $pos);
		// all opened HTML tags must also be closed again
		while ($tags)
			$str .= '</' . array_pop($tags) . '>';
		return $str . $ellipsis;
	}
	else
	{
		if (strlen($str) <= $len) return $str;
		if (!$html) return substr($str, 0, $len - 1) . $ellipsis;
		$insth = 0;   // in something (= HTML tag: 1 or entity: 2)
		$tags = array();
		for ($pos = 0; $insth || $pos < $len - 1; $pos++)
		{
			$c = $str[$pos];
			if ($c == '<')
			{
				if ($str[$pos + 1] == '/')   // tag is closing
					array_pop($tags);
				else if (preg_match('_^<([a-z]+?)[ >]_', substr($str, $pos), $m))
					$tags[] = $m[1];
				$insth = 1;
			}
			if ($c == '>' && $insth == 1)
			{
				$insth = 0;
				if (substr($str, $pos - 1, 1) == '/')   // tag was closing itself
					array_pop($tags);
			}
			if ($c == '&') $insth = 2;
			if ($c == ';' && $insth == 2) $insth = 0;
		}
		$str = substr($str, 0, $pos);
		// all opened HTML tags must also be closed again
		while ($tags)
			$str .= '</' . array_pop($tags) . '>';
		return $str . $ellipsis;
	}
}

// UTF-8-aware version of str_limit
//
function str_limit_utf8($str, $len, $html = false)
{
	return str_limit($str, $len, true, $html);
}

// Limit a URL to a given length
//
// Add '...' to query/path/filename parts if they were truncated.
// Expects HTML-encoded input, ready for use in the ABBC parser on already parsed tag contents.
//
// in url = (string) URL to limit in its length
// in max = (int) maximum length in visible characters
//
function UnbLimitUrl($url, $max)
{
	// Clean parameters
	$url = strval($url);
	$max = intval($max);

	// see if there are tags in the string. if there are, increase the maximum length by their length
	$max += strlen($url) - strlen(strip_tags($url));
	// TODO: actually we shoudn't increase the maximum length if tags are there but instead
	//       count the separate parts with tags filtered out there. this makes a nicer picture
	//       of URLs because it will be limited what is actually displayed. but this makes
	//       some problems and is not easy to implement.

	$qpos = strpos($url, '?');
	if ($qpos === false)
	{
		$path = $url;
		$query = '';
	}
	else
	{
		$path = substr($url, 0, $qpos);
		$query = substr($url, $qpos);
	}

	if (!preg_match('~^(.+?:)(//.+?)(/.+)?(/.+)?$~', $path, $m)) return $url;

	// shorten query part
	$len = strlen($m[1] . $m[2] . $m[3] . $m[4] . $query);
	if ($len > $max) $query = str_limit_utf8($query, max($max - ($len - strlen($query) + 1), 8), /*html*/ true);

	// shorten path part
	$len = strlen($m[1] . $m[2] . $m[3] . $m[4] . $query);
	if ($len > $max) $m[3] = str_limit_utf8($m[3], max($max - ($len - strlen($m[3]) + 1), 10), /*html*/ true);

	// shorten filename part
	$len = strlen($m[1] . $m[2] . $m[3] . $m[4] . $query);
	if ($len > $max) $m[4] = str_limit_utf8($m[4], max($max - ($len - strlen($m[4]) + 1), 10), /*html*/ true);

	return $m[1] . $m[2] . $m[3] . $m[4] . $query;
}

// Remove empty lines from beginning of string
//
// Differs from trim() in that it only removes entire lines, not the spaces from the line
//
function ltrimln($str)
{
	// Clean parameters
	$str = strval($str);

	return preg_replace('_^([ \t]*\r?\n)*_', '', $str);
}

// Is it a valid e-mail address?
//
function is_mailaddr($email)
{
	// Clean parameters
	$email = strval($email);

	return preg_match('/^[a-z0-9.!#$%&\'*+\-\/=?^_`{|}~]*[a-z0-9!#$%&\'*+\-\/=?^_`{|}~]@([a-z0-9\-]+\.)+[a-z0-9\-]{2,6}$/i', $email);

	#list($local, $domain) = explode('@', $email);
	#
	#$pattern_local = '^([0-9a-z]*([-|_]?[0-9a-z]+)*)(([-|_]?)\.([-|_]?)[0-9a-z]*([-|_]?[0-9a-z]+)+)*([-|_]?)$';
	#$pattern_domain = '^([0-9a-z]+([-]?[0-9a-z]+)*)(([-]?)\.([-]?)[0-9a-z]*([-]?[0-9a-z]+)+)*\.[a-z]{2,4}$';
	#
	#$match_local = eregi($pattern_local, $local);
	#$match_domain = eregi($pattern_domain, $domain);
	#
	#return ($match_local && $match_domain);
}

// Format a number
//
// in x = (any numeric) number for format
// in decimals = (int) maximum number of decimals | false
// in range_factor = (int) 1000 | 1024...
// in sep = (string) separator between number and range sign (will always be added). " " (space) is often used
// in force_dec = (bool) force decimal part in its full length: also add something like '.00'
//
function format_number($x, $decimals = false, $range_factor = 0, $sep = '', $force_dec = false)
{
	if ($x === '') return '';

	// Clean up parameters
	$x = floatval($x);

	global $UNB_T;

	$decimal = $UNB_T['num_decimal'];
	$thousand = $UNB_T['num_thousands'];
	$exp_names = array(-4 => 'p', -3 => 'n', -2 => "\xC2\xB5" /* mu in utf8 */, -1 => 'm', 0 => '', 1 => 'k', 2 => 'M', 3 => 'G', 4 => 'T', 5 => 'P');

	// adjust range, if requested
	$exp = 0;
	if ($range_factor && $x)
	{
		if ($range_factor != 1024)
		{
			// 1024 is likely to adjust bytes' range. don't go below 1 in that case...
			// else:
			while ($x < 0.0012 * $range_factor)
			{
				$x *= $range_factor;
				$exp--;
			}
		}
		while ($x > 1.2 * $range_factor)
		{
			$x /= (float) $range_factor;
			$exp++;
		}
	}
	$exp = $exp_names[$exp];

	// limit decimals
	if ($decimals > 0) $x = round($x, $decimals);

	// split up number into integer and decimal parts
	list($int, $dec) = explode('.', $x);

	// insert thousands separators
	$a = '';
	for ($n = 0; $n < strlen($int); $n++)
	{
		if (($n % 3 == 0) && ($n > 0) && ($n < strlen($int))) $a = $thousand . $a;
		$a = substr($int, strlen($int) - 1 - $n, 1) . $a;
	}
	if ($a == '') $a = '0';

	// re-combine with correct decimal separator
	if ($decimals === false)
	{
		if ($dec != '') $a .= $decimal . $dec;
	}
	elseif ($decimals > 0)
	{
		if ($force_dec)
		{
			if (strlen($dec) < $decimals) $dec = str_pad($dec, $decimals, '0');
			if (strlen($dec)) $a .= $decimal . $dec;
		}
		else
		{
			if (strlen($dec) && intval($dec)) $a .= $decimal . $dec;
		}
	}

	if ($sep != '') $a .= $sep;
	if ($exp != '') $a .= $exp;
	return $a;
}

// Same as PHP function explode(), but also groups by quotes
//
// TODO: synchronise with /proj/ute copy of this function (especially the 'keep' parameter)
// TODO: needs to be extended to recognise "-strings inside the text (like shell parameters)
//       for search queries like -"block words" or user:"user name"
//
// in mask_bs = (bool) take case of \ characters, and ignore \" f.ex. [currently ignored to true]
//
function explode_quoted($sep, $str, $mask_bs = true)
{
	$out = array();
	$len = strlen($str);
	$instr = false;   // are we currently inside a string?
	$pos = 0;         // current starting position
	$startpos = 0;    // remember last part beginning
	$pos_s = -1;      // position of ' ' (space) / separator
	$pos_q = -1;      // position of '"' (quote)

	while ($pos < $len)
	{
		if ($pos_s < $pos)   // only search if last result is before current position
			if (($pos_s = strpos($str, $sep, $pos)) === false) $pos_s = $len;
				// set pointer to end of string, if symbol was not found
		if ($pos_q < $pos)
			if (($pos_q = strpos($str, '"', $pos)) === false) $pos_q = $len;

		$minpos = min($pos_s, $pos_q);   // find the nearest interesting symbol

		if ($instr === false && $minpos === $pos_s)   // found a space/separator first (not inside a string)
		{
			$out[] = str_replace(
				'\\"',
				'"',
				substr($str, $startpos, $minpos - $startpos));
			$startpos = $pos = $minpos + 1;
		}
		elseif ($minpos === $pos_q)   // found a quote first
		{
			if ($minpos === $startpos && $instr === false)   // first symbol of this part AND not inside a string (must be so)
			{
				$instr = true;
				$startpos = $pos = $minpos + 1;   // jump over beginning "
			}
			elseif ($instr === true && $str[$minpos - 1] !== '\\' && ($str[$minpos + 1] === $sep
			                                                          || $minpos === $len - 1))
				// inside a string AND previous symbol is not \ AND next symbol is space/separator
				//                                                  OR no more characters (last symbol)
			{
				$instr = false;
				$out[] = str_replace(
					'\\"',
					'"',
					substr($str, $startpos, $minpos - $startpos));
				$startpos = $pos = $minpos + 2;   // jump over quote + separator
			}
			/*
			elseif ($instr === false && $str{$minpos - 1} === '\\')
				// not inside a string AND previous symbol is \
			*/
			else   // found something of no interest
			{
				$pos = $minpos + 1;
			}
		}
		else   // found something of no interest
		{
			$pos = $minpos + 1;
		}
	}

	// Something left?
	if ($startpos < $pos)
	{
		$out[] = str_replace(
			'\\"',
			'"',
			substr($str, $startpos, $pos - $startpos));
	}

	return $out;
}

// Mask mail addresses so that spam harvesters won't find it (so easily)
//
function UnbMaskMail($str)
{
	// Clean parameters
	$str = strval($str);

	$str = str_replace('@', '&#x0040;', $str);
	$str = str_replace('.', '&#x002E;', $str);
	$str = str_replace('_', '&#x005F;', $str);
	return $str;
}

// Convert "10M" in 10.485.760 (10 MB)...
//
function UnbUnverbSize($str)
{
	// Clean parameters
	$str = strval($str);

	switch (strtolower(substr(trim($str), -1)))
	{
		case 'k': return intval($str) * 1024;
		case 'm': return intval($str) * 1048576;
		case 'g': return intval($str) * 1073741824;
	}
	return intval($str);
}

// Get the MIME content-type of a filename
//
function UnbGetMimetype($file)
{
	$a = explode('.', $file);
	$ext = array_pop($a);
	switch (strtolower($ext))
	{
		case 'php':
		case 'js':
		case 'txt': return 'text/plain';
		case 'htm':
		case 'html': return 'text/html';
		case 'gif': return 'image/gif';
		case 'jpg': return 'image/jpeg';
		case 'png': return 'image/png';
		case 'zip': return 'application/x-zip-compressed';
		case 'rar': return 'application/x-rar-compressed';
		case 'gz': return 'application/x-gzip';
		case 'tar': return 'application/x-tar';
		case 'exe':
		case 'com': return 'application/x-msdownload';
		case 'pdf': return 'application/pdf';
	}

	$a = array(
		'file' => $file,
		'ext' => $ext,
		'type' => '');
	UnbCallHook('resolvemimetype', $a);
	if ($a['type']) return $a['type'];

	return 'unknown';
}

// make a string safe for using it in regular expressions
//
// TODO: remove after moving search term highlighting into abbc parser
//
function regsafe($str)
{
	// Clean parameters
	$str = strval($str);

	return preg_replace('/([\/\\\\\\^\\$.\\[\\]|()?*+{}])/', '\\\\$1', $str);
}

// ==================== UNB-related functions ====================

// Write back configuration file from modified $UNB['ConfigFile'] array
//
// returns (bool) success
//
function UnbRebuildConffile()
{
	global $conf_filename, $conf_tempname, $UNB;

	$UNB['ConfigFile']['last_saved_with_version'] = $UNB['Version'];

	$nf = $UNB['ConfigFile'];              // new file (array)
	UnbCallHook('cf.rebuild', $nf);
	$out = '';                    // new file (string)
	$cf = file($conf_filename);   // current config file (array)
	if (!is_array($cf)) return false;
	$conf_utf8 = isUtf8($cf[0]);

	foreach ($cf as $line)
	{
		if (trim($line) === '<?php' || trim($line) === '?>') continue;   // skip PHP code tags
		if (substr(ltrim($line), 0, 1) === '#')   // copy comment lines
		{
			$out .= rtrim($line) . "\n";
			continue;
		}
		$pos = strpos($line, '=');
		if ($pos === false)
		{
			$out .= rtrim($line) . "\n";   // there's no key=value in this line
		}
		else
		{
			$key = trim(substr($line, 0, $pos));
			if (isset($nf[$key]))   // this key is still present
			{
				$value = $nf[$key];
				if ($UNB['CharSet'] === 'UTF-8' && !$conf_utf8) $value = utf8_decode($value);
				$out .= $key . ' = ' . trim($value) . "\n";   // write line with new value
				unset($nf[$key]);   // remove this key from input array
			}
		}
	}
	if (sizeof($nf))   // are there new keys left that didn't appear in the current config file?
	{
		foreach ($nf as $key => $value)
		{
			if ($UNB['CharSet'] === 'UTF-8' && !$conf_utf8) $value = utf8_decode($value);
			$out .= $key . ' = ' . $value . "\n";
		}
	}

	$out = "<?php\n" . $out . "?>\n";
	if ($conf_utf8) $out = "\xEF\xBB\xBF" . $out;

	$usetemp = true;
	$fp = fopen($conf_tempname, 'w+t');
	if ($fp === false)
	{
		// We couldn't create the temp config file, let's try to write the config file directly
		$usetemp = false;
		$fp = fopen($conf_filename, 'w+t');
		if ($fp === false) return false;   // still doesn't work
	}
	if (!fwrite($fp, $out)) return false;
	if (!fclose($fp)) return false;

	if ($usetemp && !@rename($conf_tempname, $conf_filename))
	{
		// Windows compatibility (?): try again, but first delete target file for rename()
		if (!unlink($conf_filename)) return false;
		if (!rename($conf_tempname, $conf_filename)) return false;
	}
	chmod($conf_filename, 0666);   // make it world-writable, for buggy server environments
	return true;
}

// Read a value from the configuration file
//
// in key = (string) data key
// in array = (bool) parse value as |-separated array
//
// returns (string) normal config value
//         (array) array-parsed value
//
function rc($key, $array = false)
{
	global $UNB;
	$value = $UNB['ConfigFile'][$key];
	if (!$array) return $value;
	if ($value === null || $value === '') return array();
	return array_map('trim', explode('|', $value));
}

// Write a user's action to the board log
//
// in action = (string) log line to write
// in email = (bool) true: this is an e-mail (write into separate file), false: it's not
//
function UnbAddLog($action, $email = false)
{
	global $UNB;

	$now = UnbConvertTimezone(time(), false, true);   // Use board's timezone setting for the log

	if ($email)
	{
		$fp = fopen($UNB['LogPath'] . 'email-' . date('Y-m-d-H-i-s', $now) . '.log', 'w');
		fwrite($fp, $action);
		fclose($fp);
	}
	else
	{
		$name = $UNB['LoginUserName'];
		if ($name == '') $name = '-';

		$bver = $UNB['Client']['b_ver'];
		if ($bver == '') $bver = '-';

		$sid = session_id();

		$action = str_replace('"', '\\"', trim($action));
		if ($action == '') return;

		/*$UNB['Db']->AddRecord(array(
			'Date' => time(),
			'UserID' => $UNB['LoginUserID'],
			'UserName' => $name,
			'IP' => $_SERVER['REMOTE_ADDR'],
			'Action' => $action,
			'Browser' => $UNB['Client']['browser'],
			'BVer' => $bver,
			'OS' => $UNB['Client']['os'],
			'Lang' => $UNB['Client']['lang'],
			'Session' => $sid
			), 'Log');*/

		$action = date('Y-m-d H:i:s', $now) . " $UNB[LoginUserID] \"$name\" " . $_SERVER['REMOTE_ADDR'] . " \"$action\" {$UNB[Client][browser]} $bver {$UNB[Client][os]} {$UNB[Client][lang]} $sid" . PHP_EOL;

		$cnt = 5;
		do
		{
			if ($cnt < 5) if (function_exists('usleep')) usleep(50000);   // 50ms delay on collision
			$fp = fopen($UNB['LogPath'] . 'board-' . date('Y-m-d', $now) . '.log', 'a');
		}
		while ($fp === false && --$cnt);
		if ($fp === false && !$cnt) return false;

		#$fp = fopen($UNB['LogPath'] . 'board-' . date('Y-m-d', $now) . '.log', 'a');
		if ($fp === false) die('Unable to open log file.');
		fwrite($fp, $action);
		fclose($fp);
	}
}

// Append an error message to the error logfile
//
// in error = (string) error message to write to the log
//
function UnbErrorLog($error)
{
	global $UNB;

	$error = trim($error);
	if ($error == '') return;

	if (function_exists('debug_backtrace'))
	{
		$show_args = true;   // include all function call arguments into the backtrace

		$bt = debug_backtrace();
		$error .= endl . 'Backtrace:' . endl;
		foreach ($bt as $a)
		{
			$error .= '* ' . basename(dirname(dirname($a['file']))) . '/' . basename(dirname($a['file'])) . '/' . basename($a['file']) . ' : ' .
				$a['line'] . ' : ' . $a['class'] . $a['type'] . $a['function'] . '(';
			if ($show_args)
			{
				$cnt = 0;
				if (is_array($a['args'])) foreach ($a['args'] as $arg)
				{
					if ($cnt++) $error .= ', ';
					if (is_array($arg))
					{
						$error .= 'array(' . sizeof($arg) . ')';
					}
					if (is_bool($arg) || is_double($arg) || is_float($arg) || is_bool($arg) || is_int($arg))
					{
						$error .= $arg;
					}
					if (is_string($arg))
					{
						$error .= '"' . str_limit($arg, 40) . '"';
					}
					if (is_null($arg))
					{
						$error .= 'null';
					}
					if (is_resource($arg))
					{
						$error .= 'resource(' . get_resource_type($arg) . ')';
					}
					if (is_object($arg))
					{
						#$error .= gettype($arg);
						ob_start();
						var_dump($arg);
						if (($pos = strpos($cont = ob_get_contents(), '#')) !== false)
							$objtype = substr($cont, 0, $pos);
						else
							$objtype = 'object';
						ob_end_clean();
						$error .= $objtype;
					}
				}
			}
			else
			{
				$error .= sizeof($a['args']) . ' args';
			}
			$error .= ')' . endl;
		}
	}

	$page = $UNB[ThisPage];
	if (!$page)
	{
		$page = $_SERVER['PHP_SELF'];
		$page = basename(dirname(dirname($page))) . '/' . basename(dirname($page)) . '/' . basename($page);
	}

	$error = str_replace("\r", '', $error);
	$error = str_replace('<br />', "\n", $error);
	$error = str_replace("\n", PHP_EOL, $error);
	$error = trim($error) . PHP_EOL;
	$error = "--------------------" . PHP_EOL . date('d.m.Y H:i:s') . " - User $UNB[LoginUserID] $UNB[LoginUserName] - $page - " . $_SERVER['REMOTE_ADDR'] . PHP_EOL . $error;

	$cnt = 5;
	do
	{
		if ($cnt < 5) if (function_exists('usleep')) usleep(50000);   // 50ms delay on collision
		$fp = fopen($UNB['LogPath'] . 'error-' . date('Y-m') . '.log', 'a');
	}
	while ($fp === false && --$cnt);
	if ($fp === false && !$cnt) return false;

	#$fp = fopen($UNB['LogPath'] . 'error.log', 'a');
	fwrite($fp, $error);
	fclose($fp);
}

// Specialised date() function
//
// * Knows all of UNB's languages
// * Can also accept an array for the date:
//   (year, month, day, hour, min, sec) -- but then some format codes may not be available
//
function UnbDate($fmt, $time = false)
{
	global $UNB_T;

	$len = strlen($fmt);
	$out = '';

	if ($time === false) $time = time();
	$arr = is_array($time);

	for ($n = 0; $n < $len; $n++)
	{
		$c = $fmt[$n];
		switch ($c)
		{
			case '\\':
				$n++;
				$out .= $fmt[$n];
				break;
			case 'a':
				if ($arr)
					$out .= $time['hour'] < 12 ? 'am' : 'pm';
				else
					$out .= @date($c, $time);
				break;
			case 'A':
				if ($arr)
					$out .= $time['hour'] < 12 ? 'AM' : 'PM';
				else
					$out .= @date($c, $time);
				break;
			case 'B':
				if ($arr)
					$out .= '?';
				else
					$out .= @date($c, $time);
				break;
			case 'd':
				if ($arr)
					$out .= $time['day'] < 10 ? '0' . $time['day'] : $time['day'];
				else
					$out .= @date($c, $time);
				break;
			case 'D':
				$time2 = $time;
				if ($arr)
				{
					if ($time['year'] > 1970)
						$time2 = gmmktime(0, 0, 0, $time['month'], $time['day'], $time['year']);
					else
						$out .= '?';
				}
				if (!is_array($time2))   // now check again
				{
					$day = @date('w', $time2);
					if ($day == 0) $day = 7;
					$out .= $UNB_T['shortday' . intval($day)];
				}
				break;
			case 'F':
				if ($arr)
					$mon = $time['month'];
				else
					$mon = @date('m', $time);
				$out .= $UNB_T['longmonth' . intval($mon)];
				break;
			case 'g':
				if ($arr)
					$out .= ($time['hour'] + 11) % 12 + 1;
				else
					$out .= @date($c, $time);
				break;
			case 'G':
				if ($arr)
					$out .= $time['hour'];
				else
					$out .= @date($c, $time);
				break;
			case 'h':
				if ($arr)
				{
					$hr = ($time['hour'] + 11) % 12 + 1;
					$out .= $hr < 10 ? '0' . $hr : $hr;
				}
				else
					$out .= @date($c, $time);
				break;
			case 'H':
				if ($arr)
					$out .= $time['hour'] < 10 ? '0' . $time['hour'] : $time['hour'];
				else
					$out .= @date($c, $time);
				break;
			case 'i':
				if ($arr)
					$out .= $time['min'] < 10 ? '0' . $time['min'] : $time['min'];
				else
					$out .= @date($c, $time);
				break;
			case 'I':
				if ($arr)
					$out .= '?';
				else
					$out .= @date($c, $time);
				break;
			case 'j':
				if ($arr)
					$out .= $time['day'];
				else
					$out .= @date($c, $time);
				break;
			case 'l':
				if ($arr)
					$out .= '?';
				else
					$day = @date('w', $time);
					if ($day == 0) $day = 7;
					$out .= $UNB_T['longday' . intval($day)];
				break;
			case 'L':
				if ($arr)
				{
					if ($time['year'] % 400 == 0) $out .= '1';
					elseif ($time['year'] % 100 == 0) $out .= '0';
					elseif ($time['year'] % 4 == 0) $out .= '1';
					else $out .= '0';
				}
				else
					$out .= @date($c, $time);
				break;
			case 'm':
				if ($arr)
					$out .= $time['month'] < 10 ? '0' . $time['month'] : $time['month'];
				else
					$out .= @date($c, $time);
				break;
			case 'M':
				if ($arr)
					$mon = $time['month'];
				else
					$mon = @date('m', $time);
				$out .= $UNB_T['shortmonth' . intval($mon)];
				break;
			case 'n':
				if ($arr)
					$out .= $time['month'];
				else
					$out .= @date($c, $time);
				break;
			case 'O':
			case 'r':
				if ($arr)
					$out .= '?';
				else
					$out .= @date($c, $time);
				break;
			case 's':
				if ($arr)
					$out .= $time['sec'] < 10 ? '0' . $time['sec'] : $time['sec'];
				else
					$out .= @date($c, $time);
				break;
			case 'S':
				if ($arr)
					switch ($time['day'])
					{
						case 1:
						case 21:
						case 31:
							$out .= 'st'; break;
						case 2:
						case 22:
							$out .= 'nd'; break;
						case 3:
						case 23:
							$out .= 'rd'; break;
						default:
							$out .= 'th';
					}
				else
					$out .= @date($c, $time);
				break;
			case 't':
				if ($arr)
					switch ($time['month'])
					{
						case 1:
						case 3:
						case 5:
						case 7:
						case 8:
						case 10:
						case 12:
							$out .= '31'; break;
						case 4:
						case 6:
						case 9:
						case 11:
							$out .= '30'; break;
						case 2:
							$out .= UnbDate('L', $time) ? '29' : '28'; break;
					}
				else
					$out .= @date($c, $time);
				break;
			case 'T':
				$out .= @date($c);
				break;
			case 'U':
				if ($arr)
					$out .= '?';
				else
					$out .= $time;
				break;
			case 'w':
			case 'W':
				if ($arr)
					$out .= '?';
				else
					$out .= @date($c, $time);
				break;
			case 'Y':
				if ($arr)
					$out .= $time['year'];
				else
					$out .= @date($c, $time);
				break;
			case 'y':
				if ($arr)
					$out .= $time['year'] % 100;
				else
					$out .= @date($c, $time);
				break;
			case 'z':
			case 'Z':
				if ($arr)
					$out .= '?';
				else
					$out .= @date($c, $time);
				break;
			default:
				$out .= $c;
		}
	}
	return $out;
}

// Convert a timestamp from local time to user's timezone
//
// in time = (int) timestamp
// in back = (bool) convert from user's timezone to local (otherwise reverse)
// in board = (bool) use board's (i.e. user-independent) timezone configuration
//
function UnbConvertTimezone($time, $back = false, $board = false)
{
	global $UNB;

	if ($board)
	{
		$offset = $UNB['BoardTimezone']['offset'];
		if ($UNB['BoardTimezone']['withdst'])
			$offset += (date('I', $time) ? 3600 : 0);
	}
	else
	{
		$offset = $UNB['Timezone']['offset'];
		if ($UNB['Timezone']['withdst'])
			$offset += (date('I', $time) ? 3600 : 0);
	}
	if ($back)
		return $time + $UNB['Timezone']['local'] - $offset;
	else
		return $time - $UNB['Timezone']['local'] + $offset;
}

// Format a date/timestamp
//
// in opt = (int) 1: date, 2: time, 4: seconds, 8: weekday
// in sep = (string) separator between date and time
// in tz_done = (bool) true: timezone offset is already considered, false: timezone will be converted
//
// returns (string) formatted date/time
//
function UnbFormatTime($date = null, $opt = 1, $sep = ', ', $tz_done = false)
{
	global $UNB_T;

	if (!isset($date)) $date = time();
	if (!is_numeric($date) || !$date) return '';

	$str = '';

	if (!$tz_done) $date = UnbConvertTimezone($date);

	if ($opt & 1)
	{
		if ($opt & 8)
		{
			$str .= UnbDate('D', $date) . $sep;
		}
		$str .= UnbDate($UNB_T['dateformat.short'], $date);
	}
	if ($opt & 2)
	{
		if ($opt & 1) $str .= $sep;
		if ($opt & 4)
		{
			$str .= UnbDate($UNB_T['timeformat.long'], $date);
		}
		else
		{
			$str .= UnbDate($UNB_T['timeformat.short'], $date);
		}
	}

	return $str;
}

// Format a date/time more user-friendly using fuzzy words and such
//
// in date = (int) timestamp
// in level = (int) 1: only today/yesterday, 2+: longer time intervals, 3: seconds/mins/hours
// in opt = see UnbFormatTime function above
// in tonow = (bool) true: compare time to now, false: $date is already a time difference (in seconds)
// in grammar = (int) 1: timespan past-now
//                    2: timespan present
//                    3: point of time in future
//                    4: point of time in past
//
// returns (string) formatted date/time
//
function UnbFriendlyDate($date = null, $level = 1, $opt = 1, $tonow = true, $grammar = 1)
{
	global $UNB_T;

	if (!isset($date)) $date = time();

	$date = UnbConvertTimezone($date);

	if (!$level) return UnbFormatTime($date, $opt, ', ', true);

	$diff = ($tonow ? abs(UnbConvertTimezone(time()) - $date) : $date);

	if ($level == 3)
	{
		if ($diff <= 40)
			return $UNB_T['time' . $grammar . '.some seconds'];
		if ($diff <= 100)
			return $UNB_T['time' . $grammar . '.one minute'];
		if ($diff <= 12 * 60)
			return $UNB_T['time' . $grammar . '.some minutes'];
		if ($diff <= 25 * 60)
			return $UNB_T['time' . $grammar . '.one quarter of an hour'];
		if ($diff <= 45 * 60)
			return $UNB_T['time' . $grammar . '.half an hour'];
		if ($diff <= 2 * 3600)
			return $UNB_T['time' . $grammar . '.two hours'];
		if ($diff <= 20 * 3600)
			return $UNB_T['time' . $grammar . '.some hours'];
		if ($diff <= 32 * 3600)
			return $UNB_T['time' . $grammar . '.one day'];
	}

	$last_0h = getdate(UnbConvertTimezone(time()));
	$last_0h = mktime(0, 0, 0, $last_0h['mon'], $last_0h['mday'], $last_0h['year']);

	if ($level == 1)
	{
		if ($date >= $last_0h)
			return '<b>' . $UNB_T['today'] . '</b>' . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');
		if ($date >= $last_0h - 3600 * 24)
			return $UNB_T['yesterday'] . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');

		return UnbFormatTime($date, $opt, ', ', true);
	}

	if ($grammar == 1)
	{
		if ($date >= $last_0h)
			return '<b>' . $UNB_T['since today'] . '</b>' . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');
		if ($date >= $last_0h - 3600 * 24)
			return $UNB_T['since yesterday'] . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');
	}
	elseif ($grammar == 4)
	{
		if ($date >= $last_0h)
			return '<b>' . $UNB_T['today'] . '</b>' . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');
		if ($date >= $last_0h - 3600 * 24)
			return $UNB_T['yesterday'] . ($opt & ~1 ? ', ' . UnbFormatTime($date, $opt & ~1, ', ', true) : '');
	}

	$diff /= 3600 * 24;    // reduce seconds to days
	if ($diff <= 5)
		return $UNB_T['time' . $grammar . '.some days'];
	if ($diff <= 10)
		return $UNB_T['time' . $grammar . '.one week'];
	if ($diff <= 19)
		return $UNB_T['time' . $grammar . '.two weeks'];
	if ($diff <= 45)
		return $UNB_T['time' . $grammar . '.one month'];
	if ($diff <= 75)
		return $UNB_T['time' . $grammar . '.two months'];
	if ($diff <= 105)
		return $UNB_T['time' . $grammar . '.three months'];

	return str_replace('%s', UnbDate($UNB_T['dateformat.m/y'], $date), $UNB_T['time' . $grammar . '.absolute']);
}

// Include a translation part file.
//
// This function is usually called from a template with {require-txt "name"}.
//
// in file = (string) Translation part file basename
//
function UnbRequireTxt($file, $lang = null)
{
	global $UNB, $UNB_T;

	$once = !isset($lang);   // always include file if language is set. important for sending out e-mails
	if (!isset($lang)) $lang = $UNB['Lang'];

	$lang = preg_replace('/[^a-z0-9-_]/i', '', $lang);
	if (file_exists(dirname(__FILE__) . '/lang/' . $lang . '/' . $file . '.php'))
	{
		if ($once)
			require_once(dirname(__FILE__) . '/lang/' . $lang . '/' . $file . '.php');
		else
			require(dirname(__FILE__) . '/lang/' . $lang . '/' . $file . '.php');
	}
	elseif (file_exists(dirname(__FILE__) . '/lang/' . $UNB['DefaultLang'] . '/' . $file . '.php'))
	{
		if ($once)
			require_once(dirname(__FILE__) . '/lang/' . $UNB['DefaultLang'] . '/' . $file . '.php');
		else
			require(dirname(__FILE__) . '/lang/' . $UNB['DefaultLang'] . '/' . $file . '.php');
	}
	else
		UnbErrorLog('Fatal error: Could not include translation file ' . t2h($lang) . '/' . t2h($file) . '.<br />');
}

// ==================== Forum functions ====================

// Read ALL forums into the global array (saves many DB queries at large amount of data in the board)
//
function UnbBuildEntireForumCache()
{
	global $UNB;

	$UNB['ForumCache'] = $UNB['Db']->FastQueryArray('Forums', '*', '', 'Parent, Sort', '', '', 'ID');
	if (!is_array($UNB['ForumCache'])) $UNB['ForumCache'] = array();
}

// Calculate the nesting level of each forum
//
// TODO: This is a bit of a complex function...
//
function UnbGetAllForumLevels()
{
	global $UNB;
	$forums_tbl = $UNB['Db']->FastQueryArray('Forums', 'ID, Parent', '', 'Parent, ID', '', '', /*key*/ 'ID');
	if ($forums_tbl === false) return array();

	// correct forum nesting levels are necessary for parsing all ACL rules in the correct order
	$levels = array();
	$levels[-1] = array(0);
	$curr_level = -1;
	do
	{
		// proceed with next level
		$curr_level++;
		$levels[$curr_level] = array();
		// scan all forums
		foreach ($forums_tbl as $id => $forum)
		{
			// if this forum's parent is in direct superior level, add this forum to current level
			if (in_array($forum['Parent'], $levels[$curr_level - 1]))
				array_push($levels[$curr_level], $id);
		}
	}
	while (sizeof($levels[$curr_level]) > 0);
	#echo '<pre>levels='; var_dump($levels); echo '</pre>';

	// rearrange array(level -> array(forums))
	// into      array(forum -> level)
	$out = array();
	foreach ($levels as $level => $forums)
	{
		foreach ($forums as $forum)
		{
			$out[intval($forum)] = $level;
		}
	}
	#echo '<pre>forum_levels='; var_dump($out); echo '</pre>';

	// build forum ID resolve table
	// this array contains all subforums (in all levels) for any forum

	// initialise list
	$UNB['Subforums'] = array();
	foreach ($forums_tbl as $forum)
	{
		if (!is_array($UNB['Subforums'][$forum['Parent']])) $UNB['Subforums'][$forum['Parent']] = array();
		array_push($UNB['Subforums'][$forum['Parent']], intval($forum['ID']));
	}
	#echo '<pre>subforums=<br />'; var_dump($UNB['Subforums']); echo '</pre>';

	// foreach subforums as parent -> s | except parent = 0
	//     foreach s as id
	//         if id not in subforums[parent(parent)]
	//             push subforums[parent(parent)] <- id
	//             changed
	// while changed

	// complete list (loop-safe!)
	do
	{
		$changed = false;
		foreach ($UNB['Subforums'] as $parent => $s) if ($parent > 0)
		{
			foreach ($s as $id)
			{
				$grandparent = intval($forums_tbl[$parent]['Parent']);
				#echo '<div>(parent=' . $parent . ' grandparent=' . $grandparent . ') is ' . $id . ' in subforums[' . $grandparent . ']?</div>';
				if (!in_array($id, $UNB['Subforums'][$grandparent]))
				{
					#echo '<div>push: subforums[' . $grandparent . '] &lt;- ' . $id . '</div>';
					array_push($UNB['Subforums'][$grandparent], $id);
					$changed = true;
				}
			}
		}
	}
	while ($changed);
	#echo '<pre>subforums=<br />'; var_dump($UNB['Subforums']); echo '</pre>';

	return $out;
}

// ==================== Thread functions ====================

// Count posts per thread for ALL threads
//
// Store data into $UNB['PostsByThread'][thread id] = (int) posts
//
function UnbCountThreadPosts()
{
	global $UNB;

	$UNB['PostsByThread'] = $UNB['Db']->FastQuery1stArray('Posts', 'COUNT(*), Thread', '', '',	'', 'Thread', 'Thread')
	or $UNB['PostsByThread'] = array();
}

// Return number of posts in a thread
//
// Reads value from global array, so expects UnbCountThreadPosts() to be called before
//
function UnbGetPostsByThread($threadid)
{
	global $UNB;
	if (isset($UNB['PostsByThread'][$threadid]))
		return $UNB['PostsByThread'][$threadid];
	else
		return 0;
}

// Count users who have read in threads for a set of threads in one step
//
// in threadids = array((int) user id)
//
// returns array(thread id => count)
//
function UnbCountUserViews($threadids)
{
	global $UNB;
	if (!is_array($threadids)) return array();
	if (!sizeof($threadids)) return array();

	// Clean parameters
	foreach ($threadids as $k => $v) $threadids[$k] = intval($v);

	$a = array();
	$ids = join(',', $threadids);
	if ($record = $UNB['Db']->FastQuery('ThreadWatch', 'Thread, count(*) AS cnt', 'Thread IN (' . $ids . ')', '', '', /*group*/ 'Thread')) do
	{
		$a[$record['Thread']] = $record['cnt'];
	}
	while ($record = $UNB['Db']->GetRecord());

	return $a;
}

// Count distinct users who have posted in a thread for a set of threads in one step
//
// in threadids = array((int) user id)
//
// returns array(thread id => count)
//
function UnbCountReplyUsers($threadids)
{
	global $UNB;
	if (!is_array($threadids)) return array();
	if (!sizeof($threadids)) return array();

	// Clean parameters
	foreach ($threadids as $k => $v) $threadids[$k] = intval($v);

	$a = array();
	$ids = join(',', $threadids);
	if ($record = $UNB['Db']->FastQuery('Posts', 'Thread, count(distinct User) as cnt', 'Thread in (' . $ids . ')', '', '', /*group*/ 'Thread')) do
	{
		$a[$record['Thread']] = $record['cnt'];
	}
	while ($record = $UNB['Db']->GetRecord());

	return $a;
}

// ==================== Post functions ====================

// Get number of posts for a user
//
// Reads value from global array, so expects UnbCountUserPosts() to be called before
//
function UnbGetPostsByUser($userid)
{
	global $UNB;
	if (isset($UNB['PostsByUser'][$userid])) return $UNB['PostsByUser'][$userid];
	return 0;
}

// Get one or more last posts depending on given parameter
//
// in where = (int) thread ID
//            (array) array of thread IDs [multi-record output]
//            (string) SQL WHERE section (e.g. "User=...")
// in withAccess = (bool) Respect access rights, return first accessible post.
//                        Only valid for where = (string), ignored otherwise
//
// Pay attention to correct string/integer type! You may need to use intval() or strval()
//
// returns (array) post record row
//         (array(array)) multiple post record rows [multi-record output]
//
function UnbGetLastPost($where, $withAccess = true)
{
	global $UNB;

	if (is_int($where))
	{
		// single record version
		return $UNB['Db']->FastQuery('Posts', '*', 'Thread=' . $where, 'Date desc', 1);
	}
	elseif (is_array($where))
	{
		// multi-record version
		if (!sizeof($where)) return false;

		$ids = join(', ', $where);
		$record = $UNB['Db']->FastQuery(
			/*table*/ array(
				array('', 'Threads', 't', ''),
				array('LEFT', 'Posts', 'p', 'p.Date = t.LastPostDate AND p.Thread = t.ID'),
				array('LEFT', 'Users', 'u', 'p.User = u.ID')),
			/*fields*/ 'p.*, u.Name AS UserName2',
			/*where*/ 'p.Thread IN (' . $ids . ')');
		$a = array();
		if ($record) do {
			if (!$record['UserName']) $record['UserName'] = $record['UserName2'];
			$a[$record['Thread']] = $record;
		}
		while ($record = $UNB['Db']->GetRecord());

		return $a;
	}
	elseif (is_string($where))
	{
		// single record version, with WHERE definition
		$limit = 0;
		do
		{
			$rec = $UNB['Db']->FastQuery('Posts', '*', $where, 'Date desc', $limit . ',1');
			$limit++;
			if ($rec !== false)
			{
				$post = new IPost($rec);
				$thread = new IThread($post->GetThread());
			}
		}
		while ($withAccess && $rec !== false && !UnbCheckRights('viewforum', $thread->GetForum(), $post->GetThread()));
		return $rec;
	}

	// input type not supported
	return false;
}

// Count posts per user for ALL users
//
// Writes data into $UNB['PostsByUser'] = array(user id => posts count)
//
function UnbCountUserPosts()
{
	global $UNB;

	$UNB['PostsByUser'] = $UNB['Db']->FastQuery1stArray('Posts', 'COUNT(*), User', '', '', '', 'User', 'User')
	or $UNB['PostsByUser'] = array();
}

// ==================== User functions ====================

// Find all threads that contain any posts of the current user
//
// in threads = (array(int)) thread ids to search among
//
// returns (array(int)) thread ids found
//
function UnbFindUsersPosts($threads)
{
	global $UNB;

	if (!is_array($threads)) return false;
	if (!$threads) return false;

	// Clean parameters
	foreach ($threads as $k => $v) $threads[$k] = intval($v);

	$list = join(',', $threads);

	$a = $UNB['Db']->FastQuery1stArray('Posts', 'Thread', 'Thread IN (' . $list . ') AND User=' . $UNB['LoginUserID']);
	if (!is_array($a)) $a = array();
	return $a;
}

// Read all User's LastRead values for a set of threads in one step
//
// in threadids = array((int) user id)
//
// returns array(user id => LastRead)
//
function UnbReadUserReads($threadids = false)
{
	global $UNB;
	if (!$UNB['LoginUserID']) return 1;

	// Clean parameters
	if (is_array($threadids)) foreach ($threadids as $k => $v) $threadids[$k] = intval($v);

	// do not read the entire table!
	if (is_array($threadids) && sizeof($threadids))
		$where = 'Thread IN (' . join(',', $threadids) . ') AND ';
	else
		return false;

	$a = array();
	if ($record = $UNB['Db']->FastQuery('ThreadWatch', 'Thread, LastRead', $where . 'User=' . $UNB['LoginUserID'])) do
	{
		$a[$record['Thread']] = $record;
	}
	while ($record = $UNB['Db']->GetRecord());

	return $a;
}

// Read ALL users into the global array (saves many DB queries at large amount of data in the board)
//
// in users = (array(int)) user ids to read into the cache (optional, read all users otherwise)
//
function UnbBuildEntireUserCache($users = false)
{
	global $UNB;

	// Clean parameters
	if (is_array($users)) foreach ($users as $k => $v) $users[$k] = intval($v);

	if (!$users) return false;   // Huh? Not read entire table?
	if ($users) $where = 'ID IN (' . join(',', $users) . ')'; else $where = '';

	$UNB['UserCache'] = $UNB['Db']->FastQueryArray('Users', '*', $where, 'ID', '', '', 'ID');
	if (!is_array($UNB['UserCache'])) $UNB['UserCache'] = array();
}

// Change current forum/activity ID for this guest at later time
//
function UnbSetGuestLastForum($id)
{
	global $UNB;
	if ($UNB['LoginUserID']) return;

	// Clean parameters
	$id = intval($id);

	// hmm, it's not a good idea to update the time twice here, but is better than not.... :/
	return $UNB['Db']->ChangeRecord(array('LastActivity' => time(), 'LastForum' => $id), 'Session=\'' . session_id() . '\'', 'Guests');
}

// Get the names of all groups the current user is a member of
//
function UnbGetUserGroupNames($userid = false)
{
	global $UNB;

	if ($userid === false || $userid == $UNB['LoginUserID'])
		$groups = $UNB['LoginUserGroups'];
	else
		$groups = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $userid, '`Group`');

	if (!$groups) return array();
	return $UNB['Db']->FastQuery1stArray('GroupNames', 'Name', 'ID IN (' . join(',', $groups) . ')', 'Name');
}

// Get all group IDs the current user is a member of
//
function UnbGetUserGroups($userid = false, $publicOnly = false)
{
	global $UNB;

	if ($publicOnly)
	{
		$g = $UNB['Db']->FastQuery1stArray(
			array(
				array('', 'GroupMembers', 'gm', ''),
				array('left', 'GroupNames', 'gn', 'gm.`Group` = gn.ID')
				),
			'`Group`', 'User=' . $userid . ' AND PublicGroup=1', '`Group`');
		if ($g === false) $g = array();
		return $g;
	}

	if ($userid === false || $userid == $UNB['LoginUserID'])
		return $UNB['LoginUserGroups'];
	else
	{
		$g = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $userid, '`Group`');
		if ($g === false) $g = array();
		return $g;
	}
}

// Get the names of all known groups, as array(ID => Name)
//
// in only_team_visible = (bool) only return groups with ShowInTeam=1
//
function UnbGetGroupNames($only_team_visible = false, $publicOnly = false)
{
	global $UNB;

	$cond = ($only_team_visible ? 'ShowInTeam = 1' : '');
	if ($publicOnly)
		$cond .= ($cond ? ' AND ' : '') . 'PublicGroup = 1';

	$arr = $UNB['Db']->FastQueryArray('GroupNames', 'ID, Name', $cond, 'ID');
	$groups = array();
	if ($arr) foreach ($arr as $rec) $groups[$rec['ID']] = $rec['Name'];

	return $groups;
}

// Get the names of all users, as array(ID => Name)
//
function UnbGetUserNames()
{
	global $UNB;

	return $UNB['Db']->FastQuery1stArray('Users', 'Name, ID', '', 'Name', '', '', 'ID');
}

// Add a user to a group
//
// returns (bool) success, where false means chaos...
//
function UnbAddUserToGroup($userid, $groupid)
{
	global $UNB;

	$count = $UNB['Db']->FastQuery1st('GroupMembers', 'count(*)', 'User=' . $userid . ' AND `Group`=' . $groupid);
	if ($count == 0)
		if (!$UNB['Db']->AddRecord(array('User' => $userid, 'Group' => $groupid), 'GroupMembers')) return false;

	if ($userid == $UNB['LoginUserID'])
	{
		// we cannot tell how the current user is affected by this change, so we recalculate LoginUserGroups
		$UNB['LoginUserGroups'] = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $UNB['LoginUserID'], '`Group`')
		or $UNB['LoginUserGroups'] = array();
	}
	return true;
}

// Remove a user from a group
//
// returns (bool) success, where false means chaos...
//
function UnbRemoveUserFromGroup($userid, $groupid)
{
	global $UNB;

	if (!$UNB['Db']->RemoveRecord('User=' . $userid . ' AND `Group`=' . $groupid, 'GroupMembers')) return false;

	if ($userid == $UNB['LoginUserID'])
	{
		// we cannot tell how the current user is affected by this change, so we recalculate LoginUserGroups
		$UNB['LoginUserGroups'] = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $UNB['LoginUserID'], '`Group`')
		or $UNB['LoginUserGroups'] = array();
	}
	return true;
}

// Set all groups the current user should be a member of
//
// in userid = (int) user id
// in groups = (array(int)) group ids
//
// returns (bool) success, where false means chaos...
//
function UnbSetUserGroups($userid, $groups)
{
	global $UNB;

	// Clean parameters
	$userid = intval($userid);
	if (!is_array($groups)) return false;

	if (!$UNB['Db']->RemoveRecord('User=' . $userid, 'GroupMembers')) return false;
	$ok = true;
	$added = array();
	foreach ($groups as $g) if ($g > 0)
	{
		if (in_array($g, $added)) continue;   // don't add groups twice
		if (!$UNB['Db']->AddRecord(array('User' => $userid, 'Group' => $g), 'GroupMembers')) $ok = false;
		$added[] = $g;
	}
	if (!$ok) return false;

	if ($userid == $UNB['LoginUserID']) $UNB['LoginUserGroups'] = $groups;
	return true;
}

// Set all members of a group
//
// TODO: this could be more efficient
//
// in groupid = (int) group ID
// in members = (array(int)) user IDs
//
// returns (bool) success, where false means chaos...
//
function UnbSetGroupMembers($groupid, $members)
{
	global $UNB;

	// Clean parameters
	$groupid = intval($groupid);
	if (!is_array($members)) return false;

	if (!$UNB['Db']->RemoveRecord('`Group`=' . $groupid, 'GroupMembers')) return false;
	$ok = true;
	$added = array();
	foreach ($members as $u) if ($u > 0)
	{
		if (in_array($u, $added)) continue;   // don't add users twice
		if (!$UNB['Db']->AddRecord(array('User' => $u, 'Group' => $groupid), 'GroupMembers')) $ok = false;
		$added[] = $u;
	}

	// we cannot tell if the current user is affected by this change, so we recalculate LoginUserGroups
	$UNB['LoginUserGroups'] = $UNB['Db']->FastQuery1stArray('GroupMembers', '`Group`', 'User=' . $UNB['LoginUserID'], '`Group`');
	return $ok;
}

// Get all user IDs of a group
//
function UnbGetGroupMembers($groupid)
{
	global $UNB;

	// Clean parameters
	$groupid = intval($groupid);

	$g = $UNB['Db']->FastQuery1stArray('GroupMembers', 'User', '`Group`=' . $groupid);
	if ($g === false) $g = array();
	return $g;
}

// ==================== Security-related functions ====================

// IP flood protection
//
// returns (bool) Client IP made too many requests in time
//
function UnbIsFlooded()
{
	if (!rc('auto_ban_flood_ip')) return false;

	// configuration
	$fname = TrailingSlash(rc('log_path')) . 'ip.log';
	$period = rc('auto_ban_flood_ip_period');
	$threshold = rc('auto_ban_flood_ip_threshold');

	// read current logfile
	if (file_exists($fname))
		$lines = file($fname);
	else
		$lines = array();

	// process data and write new logfile
	$begin = time() - $period;
	$myip = $_SERVER['REMOTE_ADDR'];
	$count = 0;
	$out = '';
	if ($lines) foreach ($lines as $line)
	{
		list($ip, $ts) = explode(' ', trim($line));
		if ($ts < $begin) continue;   // remove old items
		if ($ip === $myip) $count++;   // count requests from this IP
		$out .= $line;
	}
	$out .= $myip . ' ' . time() . endl;
	if (!($fp = fopen($fname, 'w'))) return false;
	fwrite($fp, $out);
	fclose($fp);

	return $count >= $threshold;
}

// Read all ACL rules from the database and bring them in the correct order
//
// Structure of the ACL array:
//   ACL = array(actionID => array(forumID/threadID => Grant))
//
function UnbReadACL()
{
	global $UNB;

	$forum = new IForum;
	$UNB['ACL'] = array();

	$flevels = UnbGetAllForumLevels();
	if (sizeof($flevels))
	{
		$UNB['ForumOrder'] = ', CASE Forum ';
		$n = 0;
		foreach ($flevels as $fid => $level)
		{
			$UNB['ForumOrder'] .= 'WHEN ' . $fid . ' THEN ' . $level . ' ';
		}
		$UNB['ForumOrder'] .= 'END DESC';
	}
	else
	{
		$UNB['ForumOrder'] = '';
	}

	// Find all groups which current user is member of
	if ($UNB['LoginUserID'] > 0)
	{
		// We have a user ID
		if (sizeof($UNB['LoginUserGroups']) > 0)
		{
			// User is in one or more groups
			// Find any rule where (no group or userid set = all users) or (groups or userid match)
			$groups_str = '(`Group`=0 AND User=0) OR `Group` IN (' . join(',', $UNB['LoginUserGroups']) . ') OR User=' . $UNB['LoginUserID'];
		}
		else
		{
			// No group memberships
			// Find any rule where (no group or userid set = all users) or (group is 1 and userid is 0 = guests) or (userid matches)
			$groups_str = '(`Group`=0 AND User=0) OR (`Group`=' . UNB_GROUP_GUESTS . ' AND User=0) OR User=' . $UNB['LoginUserID'];
		}
	}
	else
	{
		// This is a guest
		// Find any rule where (no group or userid set = all users) or (group is 1 and userid is 0 = guests)
		$groups_str = '(`Group`=0 AND User=0) OR (`Group`=' . UNB_GROUP_GUESTS . ' AND User=0)';
	}

	// Find all relevant ACL rules
	$record = $UNB['Db']->FastQuery('ACL', 'User, `Group`, Action, Forum, Thread, `Grant`',
		'(' . $groups_str . ') ' .
		'AND Enabled',
		'User DESC, `Group` DESC, Thread DESC' . $UNB['ForumOrder'] . ', Action');

	if ($record !== false) do
	{
		// Read all values from the record
		$r_action = intval($record['Action']);
		$r_user = intval($record['User']);
		$r_group = intval($record['Group']);
		$r_forum = intval($record['Forum']);
		$r_thread = intval($record['Thread']);
		$r_grant = intval($record['Grant']);

		#echo "(acl-raw) action $r_action user $r_user group $r_group forum $r_forum thread $r_thread grant $r_grant<br />";

		// Resolve grouped access rights immediately
		$r = $r_grant;

		if ($r_action == 81)   // guest posters
			$actions = array(33=>$r, 34=>$r, 35=>$r);
		elseif ($r_action == 82)   // users
			$actions = array(6=>$r, 7=>$r, 8=>$r, 9=>$r, 10=>$r, 12=>$r, 33=>$r, 34=>$r, 35=>$r, 36=>$r, 47=>$r, 48=>$r);
		elseif ($r_action == 83)   // moderators (incremental)
			$actions = array(38=>$r, 39=>$r, 40=>$r, 42=>$r, 43=>$r, 44=>$r, 46=>$r);
		elseif ($r_action == 84)   // administrators
			$actions = array(1=>$r, 2=>$r, 3=>$r, 4=>$r, 5=>$r, 6=>$r, 7=>$r, 8=>$r, 9=>$r, 10=>$r, 11=>$r, 12=>$r,
				/*21=>100, 22=>500, 23=>600, 24=>400,*/
				31=>$r, 32=>$r, 33=>$r, 34=>$r, 35=>$r, 36=>$r, 37=>$r, 38=>$r, 39=>$r, 40=>$r, 41=>$r, 42=>$r, 43=>$r, 44=>$r, 45=>$r, 46=>$r, 47=>$r, 48=>$r, 49=>$r,
				/*61=>10240,*/ 62=>-1);
		else
			$actions = array($r_action=>$r);

		// Resolve forums to all successors
		if ($r_thread != 0)
			$forums = array(-$r_thread);
		else if (is_array($UNB['Subforums'][$r_forum]))
			$forums = array_merge($UNB['Subforums'][$r_forum], array($r_forum));
		else
			$forums = array($r_forum);

		// Build ACL array
		foreach ($actions as $action => $grant)
		{
			// enforce the multilevel array structure of $UNB['ACL']
			if (!array_key_exists($action, $UNB['ACL'])) $UNB['ACL'][$action] = array();

			if ($action < 30)
			{
				// store actions for forum=0 only (they're globally defined)
				if (!$r_forum && !$r_thread)
					if (!array_key_exists(0, $UNB['ACL'][$action]))
						$UNB['ACL'][$action][0] = $grant;
			}
			else
			{
				// store per-forum rules for per-forum/thread actions
				foreach ($forums as $forum)
				{
					if (!array_key_exists($forum, $UNB['ACL'][$action])) $UNB['ACL'][$action][$forum] = $grant;
				}
			}
		}
	}
	while ($record = $UNB['Db']->GetRecord());

	// debug: display granted access rights
	#echo '<pre>ACL=<br />'; print_r($UNB['ACL']); echo '</pre>';

	return true;
}


// Check if current user has the right to perform a particular action
//
// in action = (string) Right's name
// in forum = (int) forum the action applies to
// in thread = (int) thread the action applies to
// in user = (int) user id (for special use)
// in date = (int) timestamp (for special use)
// in isLastPost = (bool) is this the last post in a thread? (for special use)
//
// returns (bool) access granted or not
//
function UnbCheckRights($action, $forum = 0, $thread = 0, $user = 0, $date = 0, $isLastPost = false)
{
	global $UNB;
	$read_only = rc('read_only');

	// Clean parameters
	$action = trim(strval($action));
	$forum = intval($forum);
	$thread = intval($thread);
	$user = intval($user);
	$date = intval($date);

	switch ($action)
	{
		case 'is_admin':
			return $UNB['ACL'][1][0];

		case 'adduser':
			return $UNB['ACL'][2][0] && !$read_only;

		case 'removeuser':
			return $UNB['ACL'][3][0] && !$read_only;

		case 'renameuser':
			return $UNB['ACL'][4][0] && !$read_only;

		case 'editprofile':
			if ($UNB['ACL'][1][0]) $read_only = false;   // Admins must be able to get into the CP
			return ($UNB['ACL'][5][0] || ($UNB['LoginUserID'] == $user && $UNB['LoginUserID'] > 0)) && !$read_only;

		case 'changeavatar':
			return $UNB['ACL'][6][0];

		case 'sendemail':
			return $UNB['ACL'][7][0];

		case 'showuserlist':
			return $UNB['ACL'][8][0];

		case 'showonlineusers':
			return $UNB['ACL'][9][0];

		case 'showprofile':
			if ($user == $UNB['LoginUserID']) return true;
			return $UNB['ACL'][10][0];

		case 'setusergroups':
			return $UNB['ACL'][11][0];

		case 'showstat':
			return $UNB['ACL'][12][0];

		case 'maxavatarsize':
			if (isset($UNB['ACL'][21][0])) return $UNB['ACL'][21][0] * 1024;
			return rc('avatar_bytes');

		case 'maxavatarwidth':
			return rc('avatar_x');

		case 'maxavatarheight':
			return rc('avatar_y');

		case 'maxphotosize':
			if (isset($UNB['ACL'][22][0])) return $UNB['ACL'][22][0] * 1024;
			return rc('photo_bytes');

		case 'maxphotowidth':
			if (isset($UNB['ACL'][23][0])) return $UNB['ACL'][23][0];
			return rc('photo_x');

		case 'maxphotoheight':
			if (isset($UNB['ACL'][24][0])) return $UNB['ACL'][24][0];
			return rc('photo_y');

		case 'addforum':
			return $UNB['ACL'][31][$forum] && !$read_only;

		case 'editforum':
			return $UNB['ACL'][32][$forum] && !$read_only;

		case 'viewforum':
			if ($thread > 0 && isset($UNB['ACL'][33][-$thread])) return $UNB['ACL'][33][-$thread];
			return $UNB['ACL'][33][$forum];

		case 'writeforum':
			if ($read_only) return false;
			if ($thread > 0 && isset($UNB['ACL'][34][-$thread])) return $UNB['ACL'][34][-$thread];
			return $UNB['ACL'][34][$forum];

		case 'addthread':
			return $UNB['ACL'][35][$forum] && !$read_only;

		case 'createpoll':
			if ($read_only) return false;
			if (isset($UNB['ACL'][36][-$thread])) return $UNB['ACL'][36][-$thread];
			return $UNB['ACL'][36][$forum];

		case 'editannounce':
			return $UNB['ACL'][37][$forum] && !$read_only;

		case 'closethread':
			if ($read_only) return false;
			if (isset($UNB['ACL'][38][-$thread])) return $UNB['ACL'][38][-$thread];
			return $UNB['ACL'][38][$forum];

		case 'importantthread':
			if ($read_only) return false;
			if (isset($UNB['ACL'][39][-$thread])) return $UNB['ACL'][39][-$thread];
			return $UNB['ACL'][39][$forum];

		case 'editpoll':
			// needs 'editpost' right, too
			// user: sum of votes already given
			if ($read_only) return false;
			if (!$user) return true;
			if ($UNB['ACL'][1][0]) return true;   // Admins may change everything...
			if (isset($UNB['ACL'][40][-$thread])) return $UNB['ACL'][40][-$thread];
			return $UNB['ACL'][40][$forum];

		case 'viewpollusers':
			if ($read_only) return false;
			if (isset($UNB['ACL'][41][-$thread])) return $UNB['ACL'][41][-$thread];
			return $UNB['ACL'][41][$forum];

		case 'editpost':
			if ($read_only) return false;

			// user's own posts
			if ($user === $UNB['LoginUserID'] && $UNB['LoginUserID'] > 0)
			{
				$maxtime = UnbCheckRights('postedittime', $forum, $thread);
				if ($maxtime == -1) return true;                   // user can ALWAYS edit his own posts
				if (time() - $date < $maxtime * 60) return true;   // user can edit his own posts within maximum time
			}

			if (isset($UNB['ACL'][42][-$thread])) return $UNB['ACL'][42][-$thread];
			return $UNB['ACL'][42][$forum];

		case 'removepost':
			// needs 'editpost' right, too
			// $user is already set properly...
			if ($read_only) return false;

			// user's own posts (only last post in thread and you have editpost right, too)
			if ($user === $UNB['LoginUserID'] &&
			    $UNB['LoginUserID'] > 0 &&
			    $isLastPost &&
			    UnbCheckRights('editpost', $forum, $thread, $user, $date))
			{
				if ($UNB['ACL'][49][-$thread]) return true;
				if ($UNB['ACL'][49][$forum]) return true;
			}

			if (isset($UNB['ACL'][43][-$thread])) return $UNB['ACL'][43][-$thread];
			return $UNB['ACL'][43][$forum];

		case 'noeditnote':
			if (isset($UNB['ACL'][44][-$thread])) return $UNB['ACL'][44][-$thread];
			return $UNB['ACL'][44][$forum];

		case 'removeeditnote':
			if (isset($UNB['ACL'][45][-$thread])) return $UNB['ACL'][45][-$thread];
			return $UNB['ACL'][45][$forum];

		case 'showip':
			if (isset($UNB['ACL'][46][-$thread])) return $UNB['ACL'][46][-$thread];
			return $UNB['ACL'][46][$forum];

		case 'downloadattach':
			if (isset($UNB['ACL'][47][-$thread])) return $UNB['ACL'][47][-$thread];
			return $UNB['ACL'][47][$forum];

		case 'allowvoting':
			if (isset($UNB['ACL'][48][-$thread])) return $UNB['ACL'][48][-$thread];
			return $UNB['ACL'][48][$forum];

		case 'removeownpost':
			if (isset($UNB['ACL'][49][-$thread])) return $UNB['ACL'][49][-$thread];
			return $UNB['ACL'][49][$forum];

		case 'maxattachsize':
			if (isset($UNB['ACL'][61][-$thread])) return $UNB['ACL'][61][-$thread] * 1024;
			if (isset($UNB['ACL'][61][$forum])) return $UNB['ACL'][61][$forum] * 1024;
			return rc('attach_bytes');

		case 'postedittime':
			if (isset($UNB['ACL'][62][-$thread])) return $UNB['ACL'][62][-$thread];
			if (isset($UNB['ACL'][62][$forum])) return $UNB['ACL'][62][$forum];
			return -1;   // no time restriction
	}

	$a = array(
		'action' => $action,
		'forum' => $forum,
		'thread' => $thread,
		'user' => $user,
		'date' => $date,
		'isLastPost' => $isLastPost,
		'read_only' => $read_only,
		'grant' => false);
	UnbCallHook('acl.customaction', $a);

	return $a['grant'];
}


// Is the given password considered to be secure?
//
// in pass = (string) chosen password
// in username = (string) username to evaluate the password for
//
// returns (int) 0: ok
//               1: too short
//               2: equal username
//               3: need number
//               4: need special character
//
function UnbIsSecurePassword($pass, $username)
{
	// Clean parameters
	$pass = strval($pass);
	$username = strval($username);

	if (rc('pass_minlength') > 0 && strlen($pass) < rc('pass_minlength')) return 1;
	if (rc('pass_notusername') && !strcasecmp($pass, $username)) return 2;
	if (rc('pass_neednumber') && !preg_match('/[0-9]/', $pass)) return 3;
	if (rc('pass_needspecial') && !preg_match('/[!-\/:-@[-`{-\xFF]/', $pass)) return 4;
	return 0;
}

function UnbCheckHeadersSent($loc = '')
{
	if (rc('headers_sent_ok')) return;
	if (headers_sent($h_file, $h_line))
	{
		$h_file = str_replace('\\', '/', $h_file);
		$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
		$h_file = htmlspecialchars(preg_replace('~^' . $doc_root . '~', '~', $h_file));
		die("<p><b>Error: HTTP headers have already been sent!</b> Caused by output from file <tt>$h_file</tt> at line $h_line." .
			($loc ? " The error was detected $loc." : "") .
			"</p>" .
			"<p><i>Description:</i> <a href=\"http://en.wikipedia.org/wiki/Header_%28information_technology%29\">HTTP headers</a> are used to give the web browser information on the content type or how to handle it. These headers are immediately sent to the browser as soon as text content is generated by the PHP script. At this place, the application expected the headers not to be sent yet and needs to add more headers but cannot do so because somewhere else output was generated. This likely happens when custom page elements have been inserted before this code or empty lines or <a href=\"http://en.wikipedia.org/wiki/Byte_Order_Mark\">UTF BOMs</a> are present before the opening &lt;?php tag. Please check the above code line and remove the output from there to resolve the error.</p>");
	}
}

// Compare two version identifiers
//
// Version identifiers look like these:
//   unb.stable.1.6
//   unb.stable.1.6.1
//   unb.stable.1.6.patch.1
//   unb.stable.1.6.rc.1
//   unb.stable.1.6.beta.1
//   unb.stable.1.6.alpha.1
//   unb.stable.1.6.preview.1
//   unb.devel.20050914
// EBNF definition:
//   <version-id> ::= <name> "." [ <branch> "." ]* <release>
//   <name>       ::= non-numeric non-empty string
//   <branch>     ::= non-numeric non-empty string
//   <release>    ::= <subrelease> [ "." <subrelease> ]*
//   <subrelease> ::= "preview" | "alpha" | "beta" | "rc" | numeric non-empty string | "patch"
//
// in v1 = (string) version 1
// in v2 = (string) version 2
//
// returns (int) -1: v1 < v2, 0: v1 = v2, 1: v1 > v2, false: not comparable
//
function UnbCompareVersions($v1, $v2, $ignoreBranch = false)
{
	// build version arrays
/*	$va1_ = explode('.', strtolower($v1));
	$va1 = array();
	foreach ($va1_ as $part)
	{
		$subparts = explode('-', $part);
		foreach ($subparts as $subpart)
			$va1[] = trim($subpart);
	}
	$va2_ = explode('.', strtolower($v2));
	$va2 = array();
	foreach ($va2_ as $part)
	{
		$subparts = explode('-', $part);
		foreach ($subparts as $subpart)
			$va2[] = trim($subpart);
	}*/

	if ($ignoreBranch)
	{
		$v1 = UnbVersionGetRelease($v1);
		$v2 = UnbVersionGetRelease($v2);
	}

	$va1 = array_map('trim', explode('.', strtolower($v1)));
	$va2 = array_map('trim', explode('.', strtolower($v2)));

	// pad both arrays with zeros at the end
	while (sizeof($va1) < sizeof($va2))
		$va1[] = '0';
	while (sizeof($va2) < sizeof($va1))
		$va2[] = '0';

	// part-wise compare both arrays
	$mode = 0;   // name/branch mode (either equal or not comparable)
	if ($ignoreBranch)
	{
		$mode = 1;
	}

	for ($n = 0; $n < sizeof($va1); $n++)
	{
		$p1 = $va1[$n];
		$p2 = $va2[$n];

		if ($mode == 0)
		{
			if (is_numeric($p1) xor is_numeric($p2)) return false;   // not comparable
			if (is_numeric($p1) && is_numeric($p2))
			{
				$mode = 1;   // version number mode
			}
			else
			{
				if ($p1 != $p2) return false;   // not comparable
				continue;
			}
		}
		if ($mode == 1)
		{
			// possible parts now: "preview" < "alpha" < "beta" < "rc" < [number] < "patch"
			if ($p1 == 'preview')
			{
				if ($p2 == 'preview') continue;
				if ($p2 == 'alpha') return -1;
				if ($p2 == 'beta') return -1;
				if ($p2 == 'rc') return -1;
				if (is_numeric($p2)) return -1;
				if ($p2 == 'patch') return -1;
			}
			if ($p1 == 'alpha')
			{
				if ($p2 == 'preview') return 1;
				if ($p2 == 'alpha') continue;
				if ($p2 == 'beta') return -1;
				if ($p2 == 'rc') return -1;
				if (is_numeric($p2)) return -1;
				if ($p2 == 'patch') return -1;
			}
			if ($p1 == 'beta')
			{
				if ($p2 == 'preview') return 1;
				if ($p2 == 'alpha') return 1;
				if ($p2 == 'beta') continue;
				if ($p2 == 'rc') return -1;
				if (is_numeric($p2)) return -1;
				if ($p2 == 'patch') return -1;
			}
			if ($p1 == 'rc')
			{
				if ($p2 == 'preview') return 1;
				if ($p2 == 'alpha') return 1;
				if ($p2 == 'beta') return 1;
				if ($p2 == 'rc') continue;
				if (is_numeric($p2)) return -1;
				if ($p2 == 'patch') return -1;
			}
			if (is_numeric($p1))
			{
				if ($p2 == 'preview') return 1;
				if ($p2 == 'alpha') return 1;
				if ($p2 == 'beta') return 1;
				if ($p2 == 'rc') return 1;
				if (is_numeric($p2))
				{
					if ($p1 < $p2) return -1;   // version1 < version2
					if ($p1 > $p2) return 1;   // version1 > version2
					continue;
				}
				if ($p2 == 'patch') return -1;
			}
			if ($p1 == 'patch')
			{
				if ($p2 == 'preview') return 1;
				if ($p2 == 'alpha') return 1;
				if ($p2 == 'beta') return 1;
				if ($p2 == 'rc') return 1;
				if (is_numeric($p2)) return 1;
				if ($p2 == 'patch') continue;
			}
		}
	}
	return 0;   // no difference by the end
}

// Convert a version identifier into a readable name
//
function UnbVersionTitle($vid)
{
	$va = array_map('trim', explode('.', strtolower($vid)));

	$title = '';
	$mode = 0;   // name/branch mode
	foreach ($va as $part)
	{
		if ($mode == 0)
		{
			if (is_numeric($part))
			{
				$mode = 1;   // version number mode
			}
		}
		if ($mode == 1)
		{
			if (is_numeric($part))
			{
				$title .= ($title ? '.' : '') . $part;
				continue;
			}
			if ($part == 'preview')
			{
				$title .= ($title ? ' ' : '') . 'Preview';
				$mode = 2;
				continue;
			}
			if ($part == 'alpha')
			{
				$title .= ($title ? ' ' : '') . 'Alpha';
				$mode = 2;
				continue;
			}
			if ($part == 'beta')
			{
				$title .= ($title ? ' ' : '') . 'Beta';
				$mode = 2;
				continue;
			}
			if ($part == 'rc')
			{
				$title .= ($title ? ' ' : '') . 'RC';
				$mode = 2;
				continue;
			}
			if ($part == 'patch')
			{
				$title .= ($title ? ' ' : '') . 'Patch';
				$mode = 2;
				continue;
			}
			$title .= ($title ? '.' : '') . '[' . $part . ']';
			continue;
		}
		if ($mode == 2)   // subversion mode
		{
			if (is_numeric($part))
			{
				$title .= ($title ? ' ' : '') . $part;
				$mode = 1;
				continue;
			}
			$title .= ($title ? ' ' : '') . '[' . $part . ']';
			continue;
		}
	}

	// development version detection
	if ($va[1] == 'devel') $title = $title . '-dev';

	return $title;
}

// Find the product name of the version identifier
//
function UnbVersionGetName($vid)
{
	$va = array_map('trim', explode('.', strtolower($vid)));
	if (!is_numeric($va[0])) return $va[0];
	return false;
}

// Find the branch name of the version identifier
//
function UnbVersionGetBranch($vid)
{
	$va = array_map('trim', explode('.', strtolower($vid)));
	$branch = array();
	for ($n = 1; $n < sizeof($va) && !is_numeric($va[$n]); $n++)   // begin at second part
		$branch[] = $va[$n];
	if (sizeof($branch)) return join('.', $branch);
	return false;
}

// Find the version number of the version identifier
//
function UnbVersionGetRelease($vid)
{
	$va = array_map('trim', explode('.', strtolower($vid)));
	for ($n = 0; $n < sizeof($va) && !is_numeric($va[$n]); $n++);   // skip name+branch
	$release = array();
	for (; $n < sizeof($va); $n++)
		$release[] = $va[$n];
	if (sizeof($release)) return join('.', $release);
	return false;
}

?>