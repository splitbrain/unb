<?php
// clientinfo.lib.php
// Collect data from http variables of the web server
//
// Writes any data into the $CLIENT array:
//   referer = full referer URL
//   ref_mydomain = true: referer is my domain
//   ip = remote IP address
//   lang = comma separated lang codes string, sorted by priority descending
//   browser = ie, nn, opera, moz, fx, konq, safari, cam, icab, text, search, bot, download, php
//   b_ver = browser version, if defined
//   b_class = ie, opera, gecko, khtml
//   os = win95, win98, winme, winnt4, win2k, winxp, winserver, winnt, win, linux, unix, mac[osx#], os2, sun
//   os_class = win, unix, mac, os2
//   is_browser = true: real browser

$UNB['Client'] = collectUserAgentData();

function collectUserAgentData()
{
	// Initialise data
	$raw_ua = $_SERVER['HTTP_USER_AGENT'];
	$info = array();
	$info['ip'] = $_SERVER['REMOTE_ADDR'];
	$info['ua'] = $raw_ua;
	$info['browser'] = '';
	$info['b_ver'] = '';
	$info['b_class'] = '';
	$info['os'] = '';
	$info['os_class'] = '';
	$info['username'] = '';
	$info['is_browser'] = true;

	// Read referer info
	$info['referer'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
	$info['ref_mydomain'] = false;
	$ref = $info['referer'];
	if (preg_match('_^https?://(.*?)(:\d+)?/_i', $ref, $m))
	{
		$ref = $m[1];   // find domain from referer
		$me = $_SERVER['SERVER_NAME'];   // find our own server name
		if (!strcasecmp(substr($me, 0, 4), 'www.')) $me = substr($me, 4);   // remove www. prefix
		if (!strcasecmp(substr($ref, 0, 4), 'www.')) $ref = substr($ref, 4);
		if (!strcasecmp($me, $ref)) $info['ref_mydomain'] = true;
	}

	// Find out languages in correct order
	$raw_lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$lang_code = array();
	$lang_prio = array();
	foreach ($raw_lang as $lang)   // for each language
	{
		$lang = explode(';', $lang);
		array_push($lang_code, $lang[0]);   // store the code
		$lang = explode('=', $lang[1]);
		if (!$lang[0]) $lang = array('', 1);   // default priority to 1
		array_push($lang_prio, $lang[1]);   // store the priority
	}
	array_multisort($lang_prio, SORT_DESC, SORT_NUMERIC, $lang_code);   // build correct order
	$info['lang'] = str_replace(' ', '', join(',', $lang_code));

	// Determine browser and version
	$names = array(
		'Slurp' => 'search',
		'ZyBorg' => 'search',
		'Ask Jeeves' => 'search',
		'Opera' => 'opera',
		'iCab' => 'icab',
		'MSIE' => 'ie',
		'Firebird' => 'fx',
		'Firefox' => 'fx',
		'Minefield' => 'fx',
		'Thunderbird' => 'fx',
		'Galeon' => 'galeon',
		'Chrome' => 'chrome',
		'Safari' => 'safari',
		'Konqueror' => 'konq',
		'Camino' => 'cam',
		'Gecko' => 'moz',
		'Mozilla' => 'nn',
		'Netscape' => 'nn',
		'Lynx' => 'text',
		'ELinks' => 'text',
		'Links' => 'text',
		'w3m' => 'text',
		'Googlebot' => 'search',
		'Mediapartners-Google' => 'search',
		'msnbot' => 'search',
		'ConveraMultiMediaCrawler' => 'search',
		'Spider' => 'search',
		'Scooter' => 'search',
		'WebCrawler' => 'search',
		'crawler' => 'search',
		'WiseCrawler' => 'search',
		'LinkWalker' => 'search',
		'Nutch' => 'search',
		'robot' => 'bot',
		'Indy Library' => 'bot',
		'Jetbot' => 'bot',
		'psbot' => 'bot',
		'ia_archiver' => 'bot',
		'Python-urllib' => 'bot',
		'Gigabot' => 'bot',
		'Firefly' => 'bot',
		'ichiro' => 'bot',
		'lwp-trivial' => 'bot',
		'LWP::Simple' => 'bot',
		'bot' => 'bot',
		'Wget' => 'download',
		'DA 4' => 'download',
		'NetAnts' => 'download',
		'Mass Downloader' => 'download',
		'GetRight' => 'download',
		'PHP' => 'php',
		'Java' => 'java'
		);
	foreach ($names as $ua => $name)
	{
		if (stristr($raw_ua, $ua))
		{
			$info['browser'] = $name;
			if ($ua == 'Gecko') $ua = 'rv:';
			elseif ($ua == 'Netscape') $ua .= '[0-9]?[ /]';
			else $ua .= '[ /]';
			$ua = str_replace('_', '\\_', $ua);
			if (preg_match('_' . $ua . '([0-9.]+)_', $raw_ua, $m)) $info['b_ver'] = $m[1];
			// Safari version numbers:
			// http://developer.apple.com/internet/safari/uamatrix.html (that list is more precise)
			if ($name == 'safari')
			{
				if ($info['b_ver'] >= '412') { $info['b_ver'] = '2.0'; $info['os'] = 'macosx4'; $info['os_class'] = 'mac'; }
				elseif ($info['b_ver'] >= '312') { $info['b_ver'] = '1.3'; $info['os'] = 'macosx3'; $info['os_class'] = 'mac'; }
				elseif ($info['b_ver'] >= '125.7') { $info['b_ver'] = '1.2'; $info['os'] = 'macosx3'; $info['os_class'] = 'mac'; }
				elseif ($info['b_ver'] >= '100') { $info['b_ver'] = '1.1'; $info['os'] = 'macosx3'; $info['os_class'] = 'mac'; }
				elseif ($info['b_ver'] >= '85.5') { $info['b_ver'] = '1.0'; $info['os'] = 'macosx2'; $info['os_class'] = 'mac'; }
				else $info['b_ver'] = '0.x';
			}
			break;
		}
	}

	// Determine operating system
	if ($info['os'] == '')
	{
		if (stristr($raw_ua, 'Windows 95')) { $info['os'] = 'win95'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Win95')) { $info['os'] = 'win95'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows 98')) { $info['os'] = 'win98'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Win98')) { $info['os'] = 'win98'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows ME')) { $info['os'] = 'winme'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 4')) { $info['os'] = 'winnt4'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 5.0')) { $info['os'] = 'win2k'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 5.1')) { $info['os'] = 'winxp'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 5.2')) { $info['os'] = 'winserver'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 6.0')) { $info['os'] = 'winvista'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT 6.1')) { $info['os'] = 'win7'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'WinNT')) { $info['os'] = 'winnt'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows-NT')) { $info['os'] = 'winnt'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Windows NT')) { $info['os'] = 'winnt'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Win')) { $info['os'] = 'win'; $info['os_class'] = 'win'; }
		elseif (stristr($raw_ua, 'Linux')) { $info['os'] = 'linux'; $info['os_class'] = 'unix'; }
		elseif (stristr($raw_ua, 'Unix')) { $info['os'] = 'unix'; $info['os_class'] = 'unix'; }
		elseif (stristr($raw_ua, 'Mac')) { $info['os'] = 'mac'; $info['os_class'] = 'mac'; }
		elseif (stristr($raw_ua, 'SunOS')) { $info['os'] = 'sun'; $info['os_class'] = 'unix'; }
		elseif (stristr($raw_ua, 'OS/2')) { $info['os'] = 'os2'; $info['os_class'] = 'os2'; }
	}

	// See if it's a bot or a real browser
	if ($info['browser'] == 'search' ||
		$info['browser'] == 'bot' ||
		$info['browser'] == 'download' ||
		$info['browser'] == 'php' ||
		$info['browser'] == 'java')
	{
		$info['is_browser'] = false;
	}

	// Group browsers in classes for easier matching
	$classes = array(
		'ie' => array('ie'),
		'opera' => array('opera'),
		'gecko' => array('moz', 'fx', 'cam'),
		'webkit' => array('chrome', 'konq', 'safari')
		);
	foreach ($classes as $c => $browsers)
	{
		if (in_array($info['browser'], $browsers))
		{
			$info['b_class'] = $c;
			break;
		}
	}

	// Find out authentication data
	// NOTE: This only works officially with PHP-module but it seems to to a
	//       good job with Apache2 and PHP-cgi 4.3 on Linux, too. It doesn't
	//       work on Windows with Apache2 and any PHP version.
	$info['auth_user'] = $_SERVER['PHP_AUTH_USER'];
	$info['auth_pass'] = $_SERVER['PHP_AUTH_PW'];
	// --- Demo code to ask for authorisation:
	// header('WWW-Authenticate: Basic realm="Restricted page"');
	// header('HTTP/1.1 401 Unauthorized');
	// echo "Authentication required<br><br>";
	// exit();

	return $info;
}

?>
