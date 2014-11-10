<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// plugin.lib.php
// Plug-in functions

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Register a new plug-in hook function
//
// in id = (string) hook ID to register the function for
// in fname = (string) function name. The function must accept one parameter BY REFERENCE
// in prio = (int) priority of the function. All function for one hook will be called in ascending priority order
//
function UnbRegisterHook($id, $fname, $prio = 0)
{
	// Clean parameters
	$id = trim(strval($id));
	if (!$id) return false;
	$fname = trim(strval($fname));
	if (!$fname) return false;
	$prio = intval($prio);

	// Check environment
	global $UNB;
	if (!isset($UNB['Hook'])) $UNB['Hook'] = array();
	if (!is_array($UNB['Hook'][$id])) $UNB['Hook'][$id] = array();
	$UNB['Hook'][$id][] = array($prio, $fname);

	// Obtain a list of columns [code from PHP manual for array_multisort()]
	$a_prio = array();
	foreach ($UNB['Hook'][$id] as $key => $row)
		$a_prio[$key] = $row[0];

	// Sort all hooks with prio ascending
	// Add $data as the last parameter, to sort by the common key
	array_multisort($a_prio, SORT_ASC, $UNB['Hook'][$id]);

	UnbPluginMeta(1, 'active');

	return true;
}

// Unregister a plug-in hook function
//
// in id = (string) hook ID to unregister the function for
// in fname = (string) function name. Optional, if unspecified, all functions for that hook are unregistered
//
function UnbUnregisterHook($id, $fname = false)
{
	// Clean parameters
	$id = trim(strval($id));
	if (!$id) return false;

	// Check environment
	global $UNB;
	if (!isset($UNB['Hook'])) return false;
	if (!is_array($UNB['Hook'][$id])) return false;

	$count = 0;
	foreach ($UNB['Hook'][$id] as $key => $hook)
	{
		if ($hook[0] == $fname || !$fname)
		{
			unset($UNB['Hook'][$id][$key]);
			$count++;
		}
	}
	return $count > 0;
}

// Show debug output of current hook registration
//
// in id = (string) hook ID. "": show all hooks
//
function UnbDebugHooks($id = false)
{
	global $UNB;
	echo '<pre>';
	if ($id)
		print_r($UNB['Hook'][$id]);
	else
		print_r($UNB['Hook']);
	echo '</pre>';
}

// Call all functions registered for a hook ID
//
// in id = (string) hook ID
// in,out data = data to be processed by the hook functions.
//               Passed by reference, so modified data will be changed in-place
//
function UnbCallHook($id, &$data)
{
	// Clean parameters
	$id = trim(strval($id));
	if (!$id) return;

	global $UNB;
	$UNB['CurrentHook'] = $id;
	if ($UNB['Hook'][$id])
		foreach ($UNB['Hook'][$id] as $fname)
			if (!$fname[1]($data)) break;   // Break the hook chain if a function returns FALSE
	$UNB['CurrentHook'] = '';
}

// Call all functions registered for a hook ID with no data passed.
// Since the hook functions will require a parameter to be passed, a local empty string is used.
//
// in id = (string) hook ID
//
function UnbCallHookN($id)
{
	$data = '';
	UnbCallHook($id, $data);
}

// Returns the currently processed hook ID.
// Can be called from hook handling functions that are assigned to multiple hooks.
//
function UnbCurrentHook()
{
	global $UNB;
	return $UNB['CurrentHook'];
}

// Temporary store the current plug-in's name for further function calls.
// This function must be called by a plug-in script for internal management.
//
function UnbBeginPlugin($file)
{
	$file = trim(basename($file, '.php'));
	if (!$file) return false;

	global $temp_plugin_filename;
	$temp_plugin_filename = $file;
	return $file;
}

// This function must be called by a plug-in script for internal management.
//
function UnbEndPlugin()
{
	global $temp_plugin_filename;
	$temp_plugin_filename = '';
}

// Store meta data of a plug-in
//
// in desc = (string) value to store
// in key = (string) key to store value into, default is "desc"
//
// Common keys are:
//   desc:    General description of the plug-in
//   author:  Author's name and e-mail
//   lang:    Space-separated list of supported languages
//   version: Required UNB version to run this plug-in, format (BNF): <min version> " " <max version>
//            Multiple ranges can be defined.
//   file:    Additional files used by the plug-in that don't have the same basename
//
// If more then one value is assigned to a key, all values are concatenated in a separate line.
//
function UnbPluginMeta($desc, $key = 'desc')
{
	global $temp_plugin_filename, $UNB;
	if (!$temp_plugin_filename) return false;
	$file = $temp_plugin_filename;

	// Clean parameters
	$desc = strval($desc);
	$key = trim(strval($key));

	if ($key != 'active' &&
	    isset($UNB['PlugIns'][$file][$key]))
	{
		$UNB['PlugIns'][$file][$key] .= endl . $desc;
	}
	else
	{
		$UNB['PlugIns'][$file][$key] = $desc;
	}

	return true;
}

// Called by a plug-in to determine whether it's enabled.
// A plug-in is generally enabled when it's there, but it can be disabled manually by
// a board configuration option (disable_plugins) or version conflicts. Also improperly
// coded plug-ins (i.e. if the description information is missing) may be disabled.
//
function UnbPluginEnabled()
{
	global $temp_plugin_filename, $UNB;
	if (!$temp_plugin_filename) return false;
	$file = $temp_plugin_filename;

	$status = 'ok';
	$enabled = true;

	if ($enabled)
	{
		if (!$UNB['PlugIns'][$file]['desc'])
		{
			$enabled = false;
			$status = 'malformed:nodesc';
		}
	}

	if ($enabled)
	{
		if (in_array($file, rc('disable_plugins', true)))
		{
			$enabled = false;
			$status = 'disabled';
		}
	}

	if ($enabled)
	{
		// Version compatibility check
		$lines = explode(endl, $UNB['PlugIns'][$file]['version']);
		$found_version = false;
		foreach ($lines as $line)
		{
			list($min, $max) = explode(' ', trim($line));
			if ((!$min ||
			     UnbCompareVersions($min, $UNB['Version']) === 0 ||
			     UnbCompareVersions($min, $UNB['Version']) === -1) &&
			    (!$max ||
			     UnbCompareVersions($max, $UNB['Version']) === 0 ||
			     UnbCompareVersions($max, $UNB['Version']) === 1))
				$found_version = true;
		}
		if (!$found_version)
		{
			$enabled = false;
			$status = 'wrongversion';
		}
	}

	$UNB['PlugIns'][$file]['status'] = $status;

	return $enabled;
}

?>