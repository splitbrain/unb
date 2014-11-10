<?php
// Unclassified Template Engine
// http://unclassified.de
// Copyright 2005 by Yves Goergen
//
// ute-runtime.lib.php
// Template runtime library functions

require_once(dirname(__FILE__) . '/ute-runtime.conf.php');

// Show a template and compile it if required
//
// in file = (string) template source filename
// in params = (array) set of parameters to use for template rendering
// uses $UTE
//
function UteShow($file, &$params)
{
	global $UTE;

	$sourceFile = $UTE['__sourcePath'] . '/' . $file;
	$cacheFile = $UTE['__cachePath'] . '/' . $file . '.php';

	// check if we have a current cache file
	if ((!$UTE['__fastMode'] &&
	     (!file_exists($cacheFile) ||
	      filemtime($sourceFile) > filemtime($cacheFile))) ||
	    $UTE['__noCache'])
	{
		// check the environment
		if (!is_dir($UTE['__sourcePath']))
		{
			if ($UTE['__haltOnFileError'])
				die('<b>UTE error:</b> template source directory does not exist or is no directory.<br />');
			return false;
		}
		if (!file_exists($sourceFile))
		{
			if ($UTE['__haltOnFileError'])
				die('<b>UTE error:</b> template source file "' . $file . '" does not exist<br />');
			return false;
		}
		if (!file_exists($UTE['__cachePath']) || !is_dir($UTE['__cachePath']))
		{
			if (!mkdir($UTE['__cachePath'], 0777))
			{
				if ($UTE['__haltOnFileError'])
					die('<b>UTE error:</b> cannot create template cache directory.<br />');
				return false;
			}
		}

		require_once(dirname(__FILE__) . '/ute-compiler.lib.php');

		// compile source file and store it into the cache directory
		$c = UteCompile(join('', file($sourceFile)));

		$fp = fopen($cacheFile, 'w');
		if ($fp === false) return false;
		if (fwrite($fp, $c) === false || fclose($fp) === false)
		{
			if ($UTE['__haltOnFileError'])
				die('<b>UTE error:</b> cannot write to template cache file of "' . $file . '"<br />');
			return false;
		}
	}

	// prepare parameters and include template code
	if (is_array($params))
	{
		$originalUTE = $UTE;
		$UTE = array_merge($UTE, $params);
	}

	if ($UTE['__haltOnFileError'] && !file_exists($cacheFile) && !is_readable($cacheFile))
		die('<b>UTE error:</b> cannot include template "' . $file . '", does not exist or is not readable<br />');
	$ret = include($cacheFile);
	if ($UTE['__haltOnFileError'] && !$ret)
		die('<b>UTE error:</b> error including template "' . $file . '"<br />');

	if (is_array($params))
	{
		$UTE = $originalUTE;
	}
	return true;
}

// Collect multiple templates to show them at a later time
//
// in file = (string) Template file
// in parameters = (array) Set of parameters to use for template rendering
// in key = (string) Key to access the template parameters later on
//
function UteRemember($file, &$params, $key = null)
{
	global $UTE;

	if (!isset($UTE['__tplCollection']) || !is_array($UTE['__tplCollection']))
		$UTE['__tplCollection'] = array();
	if (!isset($key)) $key = $file;

	$UTE['__tplCollection'][] = array(
		'key' => $key,
		'file' => $file,
		'params' => &$params);
}

// Change the parameters array for a previously remembered template
//
// in key = (string) Template key
// in param = (string) Parameter name
// in value = New parameter value
//
function UteChange($key, $param, $value)
{
	global $UTE;

	foreach ($UTE['__tplCollection'] as $thiskey => $tpl)
	{
		if ($thiskey === $key)
		{
			$UTE['__tplCollection'][$key]['params'][$param] = $value;
		}
	}
}

// Show all previously remembered templates
//
function UteShowAll()
{
	global $UTE;

	if (!isset($UTE['__tplCollection']) || !is_array($UTE['__tplCollection'])) return;

	foreach ($UTE['__tplCollection'] as $tpl)
	{
		UteShow($tpl['file'], $tpl['params']);
	}
	$UTE['__tplCollection'] = null;
}

// Internal function to include sub-templates
//
// in file = (string) single template file to include
//           (array(string)) multiple template files to include in sequence
//
function UteInclude($file, $saveEnv = false)
{
	global $UTE;

	if ($saveEnv)
		$originalUTE = $UTE;

	if (is_string($file)) $file = array($file);

	if (is_array($file)) foreach ($file as $f)
	{
		$params = false;
		UteShow($f, $params);
	}

	if ($saveEnv)
		$UTE = $originalUTE;

	return '';
}

// Save the entire UTE environment to the stack
//
function UtePushEnv()
{
	global $UTE, $UTEEnvStack;
	if (!isset($UTEEnvStack)) $UTEEnvStack = array();
	array_push($UTEEnvStack, $UTE);
	return '';
}

// Restore the entire UTE environment from the stack
//
function UtePopEnv()
{
	global $UTE, $UTEEnvStack;
	if (!is_array($UTEEnvStack)) return '__error:PopEnv:nostack__';
	$UTE = array_pop($UTEEnvStack);
	return '';
}

// Translate some text with parameters
//
// This function is required at runtime for the template execution only.
//
// in key = (string) key of the text part to be translated
// in ... = (string, string) key and value of parameters
//
// returns (string) translated text
// uses $UNB_T
// see UteTranslateNum
//
function UteTranslate($key)
{
	global $UNB_T;

	$args = func_get_args();
	$s = $UNB_T[$key];
	for ($pos = 1; $pos + 1 < sizeof($args); $pos += 2)
	{
		$s = str_replace('{' . $args[$pos] . '}', $args[$pos + 1], $s);
	}
	return $s;
}

// Translate some text with parameters and specific numerus
//
// This function is required at runtime for the template execution only.
//
// Translated texts can have a specific numerus either by a constant number or
// by a number modulo n. So special translations can be defined for a count of
// 0, 1 or 2 etc. and for all counts of 0, 1 etc. modulo 10 which is 0, 10, 20,
// 30 resp. 1, 11, 21, 31 and so on. The moduli currently supported, in their
// order of matching, are 1, 100 and 10. Adding more can decrease performance.
//
// Example: To match the text key "posts" to several numbers, write this:
//          0 -- 'posts.num0'
//          1 -- 'posts.num1'
//          2 -- 'posts.num2'
//          1, 11, 21... -- 'posts.num1%10'
//          5, 15, 25... -- 'posts.num5%10'
//          0, 100, 200... -- 'posts.num0%100'
//          20, 120, 220... -- 'posts.num20%100'
//
// in key = (string) key of the text part to be translated
// in num = (int) numerus to use for translation
// in ... = (string, string) key and value of parameters
//
// returns (string) translated text
// uses $UNB_T
// see UteTranslate
//
function UteTranslateNum($key, $num)
{
	global $UNB_T;

	$args = func_get_args();

	if (isset($UNB_T[$key . '.num' . $num]))
		$s = $UNB_T[$key . '.num' . $num];
	elseif (isset($UNB_T[$key . '.num' . ($num % 100) . '%100']))
		$s = $UNB_T[$key . '.num' . ($num % 100) . '%100'];
	elseif (isset($UNB_T[$key . '.num' . ($num % 10) . '%10']))
		$s = $UNB_T[$key . '.num' . ($num % 10) . '%10'];
	else
		$s = $UNB_T[$key];
	for ($pos = 2; $pos + 1 < sizeof($args); $pos += 2)
	{
		$s = str_replace('{' . $args[$pos] . '}', $args[$pos + 1], $s);
	}
	return $s;
}

// Insert a variable's value
//
// Uses the $UTE array or the local foreach loop variable
//
// in name = (string) variable name
//                    if name is empty, go to top level (i.e. outside the loop) and use key1 as actual name
// in key1 = (string) first-level array item key
// in key2 = (string) second-level array item key
//
function UteVariable($name, $key1 = false, $key2 = false)
{
	global $UTE;

	// are we inside a foreach loop?
	if (sizeof($UTE['__loops_rt']) > 0 && $UTE['__loops_rt'][sizeof($UTE['__loops_rt']) - 1] == '')
	{
		if ($name === '')
		{
			if ($key2 !== false) return $UTE[$key1][$key2];
			return $UTE[$key1];
		}

		$loopName = '__UTE_LOOPVAR' . sizeof($UTE['__loops_rt']);
		if ($key2 !== false && is_array($GLOBALS[$loopName][$name])) return $GLOBALS[$loopName][$name][$key1][$key2];
		if ($key1 !== false) return $GLOBALS[$loopName][$name][$key1];
		return $GLOBALS[$loopName][$name];
	}

	// only allow secondary array use if this is really an array
	if ($key2 !== false && is_array($UTE[$name])) return $UTE[$name][$key1][$key2];
	if ($key1 !== false) return $UTE[$name][$key1];
	return $UTE[$name];
}

// UTF-8-aware string length counting
//
function UteStrlen($str)
{
	global $UTE;

	if ($UTE['__characterSet'] === 'ISO-8859-1') return strlen($str);

	if ($UTE['__characterSet'] === 'UTF-8')
	{
		// PHP 5 optimisation
		if (PHP_VERSION >= 5 && function_exists('iconv_strlen'))
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

	return 0;
}

// UTF-8-aware string extraction method
// Works like standard substr() and cuts off incomplete multibyte characters from the end
//
function UteSubstr($str, $start, $len = false)
{
	global $UTE;

	if ($UTE['__characterSet'] === 'ISO-8859-1') return substr($str, $start, ($len === false ? NULL : $len));

	if ($UTE['__characterSet'] === 'UTF-8')
	{
		// PHP 5 optimisation
		if (PHP_VERSION >= 5 && function_exists('iconv_substr'))
			return iconv_substr($str, $start, ($len === false ? NULL : $len), 'UTF-8');

		if ($start < 0) $start += UteStrlen($str);

		$pos = 0;     // current ascii string index
		$index = 0;   // current multibyte symbol index
		$s = false;   // where to start copy then
		$l = 0;       // number of bytes to copy
		$slen = strlen($str);
		while ($pos < $slen)
		{
			if ($s !== false) $l += $bytes;   // if we're in-index, add number of bytes of previous symbol
			if ($index == $start) $s = $pos;   // this may be our starting symbol index
			if ($len !== false && $index - $start + 1 == $len) break;   // stop condition

			$value = ord($str[$pos]);
			if ($value > 127)
				if     ($value >= 224 && $value <= 239) $bytes = 3;
				elseif ($value >= 240 && $value <= 247) $bytes = 4;
				else   $bytes = 2;  /* 192...223 */
			else $bytes = 1;

			$index++;
			$pos += $bytes;
		}
		if ($s === false) return '';
		return substr($str, $s, $l);
	}

	return '';
}

// Limit a string to a given length and add '...' if it was truncated
//
function UteStrlimit($str, $len, $end)
{
	if (UteStrlen($str) <= $len) return $str;
	return UteSubstr($str, 0, $len - UteStrlen($end)) . $end;
}

// Convert a Unicode index to a UTF-8 string
//
function UteCodeUTF($num)
{
	global $UTE;

	if ($UTE['__characterSet'] != 'UTF-8')
	{
		if ($num < 256) return chr($num);
		return '&#' . $num . ';';
	}

	if ($num < 128) return chr($num);
	if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	return '';
}

?>