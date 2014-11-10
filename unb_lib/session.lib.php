<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// session.lib.php
// Session Library
// Widely taken from the Website Management System WMS, http://software.unclassified.de/wms
//
// provides functions to start, terminate and check a user login session

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

$UNB['SessionInactive'] = 30;   # kill session on ... minutes idle time (automatic re-login if cookie is present)

// disable trans-sid use
@ini_set('session.use_trans_sid', '0');
@ini_set('url_rewriter.tags', '');

// Uncomment this to NEVER use cookies for session ID.
// Otherwise they're only used when activated in the browser
// (PHP will determine this automatically by using them on the first access and
// check whether they're returned by the browser)
//
#@ini_set('session.use_cookies', '0');

// Check a user password
//
// Supports multiple password hash methods.
//
// in hashed = (string) hash value of the password to check
// in stored = (string) stored hash value of the user password, this is the "correct answer"
//
// returns (bool) password is correct
//
function UnbCheckUserPassword($hashed, $stored)
{
	// update old hashes
	if (strlen($stored) == 32)
	{
		$stored = '{md5}' . $stored;
	}
	else if (strlen($stored) == 34)
	{
		$stored = '{kmd5}' . $stored;
	}
	else if (strlen($stored) == 40)   // not used but also here for compatibility
	{
		$stored = '{sha1}' . $stored;
	}
	else if ($stored{0} != '{')
	{
		$stored = '{plain}' . $stored;
	}

	// compare passwords based on used hash method
	if (substr($stored, 0, 5) == '{md5}')
	{
		// input password must be an MD5 hash
		$stored = substr($stored, 5);   // remove identifier
		return strtolower($hashed) == strtolower($stored);
	}
	else if (substr($stored, 0, 6) == '{kmd5}')
	{
		// input password must be an MD5 hash
		$stored = substr($stored, 6);   // remove identifier
		#$key = substr($stored, 0, 2);
		#return $key . strtolower(md5($key . $hashed)) == $stored;

		// retrieve key
		$key = substr($stored, 16, 2);
		// build keyed hash
		$hash1 = strtolower(md5($key . $hashed));
		$hash2 = substr($hash1, 0, 16) . $key . substr($hash1, 16);
		return $hash2 == $stored;
	}
	else if (substr($stored, 0, 6) == '{sha1}')   // TODO: untested!
	{
		// input password must be an SHA1 hash
		$stored = substr($stored, 6);   // remove identifier
		return strtolower($hashed) == strtolower($stored);
	}
	else if (substr($stored, 0, 7) == '{plain}')   // TODO: untested!
	{
		// input password must be an MD5 hash
		$stored = substr($stored, 7);   // remove identifier
		return strtolower($hashed) == strtolower(md5($stored));
	}
	return false;
}

// Create a new user password
//
// Supports multiple password hash methods.
//
// in pass = (string) plaintext password to convert to hash value
// in inclmethod = (bool) include method name into output
//
// returns (string) password hash value
//
function UnbCreateUserPassword($pass, $inclmethod = false)
{
	// Select hash method from: md5, kmd5, sha1, plain
	$method = 'kmd5';

	// generate standard single MD5 hash
	if ($method == 'md5')
	{
		return ($inclmethod ? '{md5}' : '') . strtolower(md5($pass));
	}

	// generate keyed MD5 hash
	if ($method == 'kmd5')
	{
		// generate key
		mt_srand((double) microtime() * 1000000);
		for ($key = '', $c = 0; $c < 2; $c++)
		{
			$num = rand(0, 10+26+26);
			if ($num < 10) $num += 0x31;   // 0...9
			elseif ($num < 10+26) $num += 0x41 - 10;   // A...Z
			elseif ($num < 10+26+26) $num += 0x61 - 10-26;   // a...z
			$key .= chr($num);
		}
		// build keyed hash
		$hash1 = md5($key . md5($pass));
		$hash2 = substr($hash1, 0, 16) . $key . substr($hash1, 16);
		return ($inclmethod ? '{kmd5}' : '') . $hash2;
		#return ($inclmethod ? '{kmd5}' : '') . $key . strtolower(md5($key . md5($pass)));
	}

	// generate standard single SHA1 hash
	if ($method == 'sha1')   // TODO: untested!
	{
		return ($inclmethod ? '{sha1}' : '') . strtolower(sha1($pass));
	}

	// store plaintext password
	if ($method == 'plain')   // TODO: untested!
	{
		return ($inclmethod ? '{plain}' : '') . $pass;
	}
}

// Create a session. Called at login time
//
// in UserID = (int) user id to log in
// in HashedPassword = (string) hash value of the password to check
// in StoredPassword = (string) stored hash value of the user password, this is the "correct answer"
// in AutoReLogin = (bool) set cookies to auto-login the user on the next visit
//
// returns (bool) session created successfully, login error otherwise
//
function UnbCreateSession($UserID, $HashedPassword, $StoredPassword, $AutoReLogin = true)
{
	global $UNB;

	// Clean parameters
	$UserID = intval($UserID);

	// Start the PHP session if none is started yet
	if (!session_id())
	{
		$sessionname = (isset($UNB['SessionName']) ? $UNB['SessionName'] : rc('prog_id') . 'sess');

		// check session ID for invalid characters
		$sid = $_REQUEST[$sessionname];
		if (!isset($sid))
			$sid = $_COOKIE[$sessionname];
		if (!preg_match('/[a-f0-9-,]{0,42}/i', $sid)) die('<b>UNB error:</b> invalid session ID');

		@ini_set('session.cookie_path', UnbGetCookiePath());

		session_name($sessionname);
		session_start();

		// Internet Explorer SSL fix
		@header('Pragma: ');
		@header('Cache-Control: cache');
	}

	if ($HashedPassword == '') return false;

	if ($_SESSION['UnbInvalidLogins'] >= 10)
	{
		sleep(rand(3, 6));
		// silently fail login on more than 10 wrong passwords
		return false;
	}

	$passed = UnbCheckUserPassword($HashedPassword, $StoredPassword);
	if (!$passed)
	{
		sleep(rand(3, 6));
		if (!isset($_SESSION['UnbInvalidLogins']) || $_SESSION['UnbInvalidLogins'] < 1)
		{
			$_SESSION['UnbInvalidLogins'] = 1;
		}
		else
		{
			$_SESSION['UnbInvalidLogins']++;
		}
		$_SESSION['UnbAuthed'] = false;

		if ($AutoReLogin && !rc('no_cookies'))
		{
			#if (preg_match('_^[^/]+://[^/]+(/.*)$_', rc('home_url'), $m))
			#	$path = $m[1];
			#else
			#	$path = '/';
			@setcookie('UnbUser-' . rc('prog_id'), false, 1, UnbGetCookiePath());
		}
		return false;
	}

	$_SESSION['UnbAuthed'] = true;
	$_SESSION['UnbLoginTime'] = time();
	$_SESSION['UnbAccessTime'] = time();
	$_SESSION['UnbInvalidLogins'] = 0;
	$_SESSION['UnbProgId'] = rc('prog_id');
	$_SESSION['UnbIpAddr'] = ip2long($_SERVER['REMOTE_ADDR']);
	$_SESSION['UnbUserId'] = intval($UserID);

	if ($AutoReLogin && !rc('no_cookies'))
	{
		$cookie = $UserID . ' ' . $HashedPassword;
		#if (preg_match('_^[^/]+://[^/]+(/.*)$_', rc('home_url'), $m))
		#	$path = $m[1];
		#else
		#	$path = '/';
		@setcookie('UnbUser-' . rc('prog_id'), $cookie, time() + 3600 * 24 * 365, UnbGetCookiePath());
	}
	return true;
}

// Terminate a session. Called at logout time
//
function UnbTermSession()
{
	global $UNB;

	// Start the PHP session if none is started yet
	if (!session_id())
	{
		$sessionname = (isset($UNB['SessionName']) ? $UNB['SessionName'] : rc('prog_id') . 'sess');

		// check session ID for invalid characters
		$sid = $_REQUEST[$sessionname];
		if (!isset($sid))
			$sid = $_COOKIE[$sessionname];
		if (!preg_match('/[a-f0-9-,]{0,42}/i', $sid)) die('<b>UNB error:</b> invalid session ID');

		@ini_set('session.cookie_path', UnbGetCookiePath());

		session_name($sessionname);
		session_start();

		// Internet Explorer SSL fix
		@header('Pragma: ');
		@header('Cache-Control: cache');
	}

	$_SESSION['UnbAuthed'] = false;
	$_SESSION['UnbUserId'] = 0;
	$_SESSION = array();
	session_destroy();

	if (!rc('no_cookies'))
	{
		#if (preg_match('_^[^/]+://[^/]+(/.*)$_', rc('home_url'), $m))
		#	$path = $m[1];
		#else
		#	$path = '/';
		@setcookie('UnbUser-' . rc('prog_id'), false, 1, UnbGetCookiePath());
	}
}

// Logout a user without terminating the session. Used for admin_lock effective logout.
//
function UnbLogoutNoTermSession()
{
	global $UNB;

	// Start the PHP session if none is started yet
	if (!session_id())
	{
		$sessionname = (isset($UNB['SessionName']) ? $UNB['SessionName'] : rc('prog_id') . 'sess');

		// check session ID for invalid characters
		$sid = $_REQUEST[$sessionname];
		if (!isset($sid))
			$sid = $_COOKIE[$sessionname];
		if (!preg_match('/[a-f0-9-,]{0,42}/i', $sid)) die('<b>UNB error:</b> invalid session ID');

		@ini_set('session.cookie_path', UnbGetCookiePath());

		session_name($sessionname);
		session_start();

		// Internet Explorer SSL fix
		@header('Pragma: ');
		@header('Cache-Control: cache');
	}

	$_SESSION['UnbAuthed'] = false;
	$_SESSION['UnbUserId'] = 0;
}

// Check a present session. Called on every page access to verify a past login in this session
//
// returns (bool) session verified successfully
//
function UnbCheckSession()
{
	global $UNB;

	// Start the PHP session if none is started yet
	if (!session_id())
	{
		$sessionname = (isset($UNB['SessionName']) ? $UNB['SessionName'] : rc('prog_id') . 'sess');

		// check session ID for invalid characters
		$sid = $_REQUEST[$sessionname];
		if (!isset($sid))
			$sid = $_COOKIE[$sessionname];
		if (!preg_match('/[a-f0-9]{0,42}/i', $sid)) die('<b>UNB error:</b> invalid session ID');

		@ini_set('session.cookie_path', UnbGetCookiePath());

		session_name($sessionname);
		if (trim($sid) == '') session_id(strtolower(md5(microtime())));   // generate new session id if it's empty
		session_start();

		// Internet Explorer SSL fix
		@header('Pragma: ');
		@header('Cache-Control: cache');
	}

	if ($_SESSION['UnbProgId'] != rc('prog_id'))
	{
		return false;
	}
	if (!$_SESSION['UnbAuthed'] || (time() - $_SESSION['UnbAccessTime'] > $UNB['SessionInactive'] * 60))
	{
		return false;
	}

	if (!isset($UNB['SessionNetMask'])) $UNB['SessionNetMask'] = 0xFFFFFF00;
	if ((ip2long($_SERVER['REMOTE_ADDR']) & $UNB['SessionNetMask']) != ($_SESSION['UnbIpAddr'] & $UNB['SessionNetMask']))
	{
		return false;
	}

	$_SESSION['UnbAccessTime'] = time();
	return true;
}

function UnbGetCookiePath()
{
	if (strlen(rc('cookie_path')))
		$cookiePath = rc('cookie_path');
	else   // Use the URL path of the calling script as cookie path.
		$cookiePath = dirname($_SERVER['PHP_SELF']);
	// Ensure a trailing slash (required for HTTP)
	if (substr($cookiePath, -1) != '/')
		$cookiePath .= '/';
	return $cookiePath;
}

// ---------- SHA1 function ----------

if (!function_exists('sha1') && function_exists('mhash'))
{
	// [nodoc]
	// Source: http://www.php.net/manual/en/function.sha1.php#37442
	//         (comment on 15-Nov-2003 06:06 by labarks)
	// Extended by raw_output parameter from PHP5
	//
	// Use mhash SHA1 if available
	function sha1($hash_source, $raw_output = false)
	{
		$hash = mhash(MHASH_SHA1, $hash_source);
		if (!$raw_output) $hash = bin2hex($hash);
		return $hash;
	}
}
else if (!function_exists('sha1'))
{
	// [nodoc]
	// Source: http://www.php.net/manual/en/function.sha1.php#47609
	//         (comment on 22-Nov-2004 09:43 by sinatosk at gmail dot com)
	//
	// PHP implementation of the Secure Hash Algorithm (SHA-1)
	// Date modified: 1st October 2004 20:09 GMT
	//
	// This code is available under the GNU Lesser General Public License:
	// http://www.gnu.org/licenses/lgpl.txt
	//
	// Based on the PHP implementation by Marcus Campbell
	// http://www.tecknik.net/sha-1/
	//
	// This is a slightly modified version by me Jerome Clarke (sinatosk@gmail.com)
	// because I feel more comfortable with this
	function sha1_str2blks_SHA1($str)
	{
		$strlen_str = strlen($str);
		$nblk = (($strlen_str + 8) >> 6) + 1;
		for ($i = 0; $i < $nblk * 16; $i++)
			$blks[$i] = 0;
		for ($i = 0; $i < $strlen_str; $i++)
			$blks[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8);
		$blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);
		$blks[$nblk * 16 - 1] = $strlen_str * 8;
		return $blks;
	}

	// [nodoc]
	function sha1_safe_add($x, $y)
	{
		$lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
		$msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);
		return ($msw << 16) | ($lsw & 0xFFFF);
	}

	// [nodoc]
	function sha1_rol($num, $cnt)
	{
		return ($num << $cnt) | sha1_zeroFill($num, 32 - $cnt);
	}

	// [nodoc]
	function sha1_zeroFill($a, $b)
	{
		$bin = decbin($a);
		$strlen_bin = strlen($bin);
		$bin = $strlen_bin < $b ? 0 : substr($bin, 0, $strlen_bin - $b);
		for ($i = 0; $i < $b; $i++)
			$bin = '0' . $bin;
		return bindec($bin);
	}

	// [nodoc]
	function sha1_ft($t, $b, $c, $d)
	{
		if ($t < 20) return ($b & $c) | ((~$b) & $d);
		if ($t < 40) return $b ^ $c ^ $d;
		if ($t < 60) return ($b & $c) | ($b & $d) | ($c & $d);
		return $b ^ $c ^ $d;
	}

	// [nodoc]
	function sha1_kt($t)
	{
		if ($t < 20) return 1518500249;
		if ($t < 40) return 1859775393;
		if ($t < 60) return -1894007588;
		return -899497514;
	}

	// Implementation of SHA1 function, if it's not provided by PHP
	//
	// This function is pre-defined from PHP 4.3.0 on
	// See the PHP manual for details.
	//
	function sha1($str, $raw_output = false)
	{
		if ($raw_output === true) return pack('H*', sha1($str, false));

		$x = sha1_str2blks_SHA1($str);
		$a =  1732584193;
		$b = -271733879;
		$c = -1732584194;
		$d =  271733878;
		$e = -1009589776;
		$x_count = count($x);

		for ($i = 0; $i < $x_count; $i += 16)
		{
			$olda = $a;
			$oldb = $b;
			$oldc = $c;
			$oldd = $d;
			$olde = $e;

			for ($j = 0; $j < 80; $j++)
			{
				$w[$j] = ($j < 16) ? $x[$i + $j] : sha1_rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);

				$t = sha1_safe_add(sha1_safe_add(sha1_rol($a, 5), sha1_ft($j, $b, $c, $d)), sha1_safe_add(sha1_safe_add($e, $w[$j]), sha1_kt($j)));
				$e = $d;
				$d = $c;
				$c = sha1_rol($b, 30);
				$b = $a;
				$a = $t;
			}

			$a = sha1_safe_add($a, $olda);
			$b = sha1_safe_add($b, $oldb);
			$c = sha1_safe_add($c, $oldc);
			$d = sha1_safe_add($d, $oldd);
			$e = sha1_safe_add($e, $olde);
		}
		return sprintf('%08x%08x%08x%08x%08x', $a, $b, $c, $d, $e);
	}
} // function_exists sha1

?>