<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Resolve additional MIME types for attached files and other downloads');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('mime.types', 'file');

if (!UnbPluginEnabled()) return;

// Hook function to scan the messages
//
function UnbHookResolveMIME(&$data)
{
	// Read Apache-style MIME type list file
	$lines = @file(dirname(__FILE__) . '/mime.types');
	if (is_array($lines)) foreach ($lines as $line)
	{
		$line = trim($line);
		if ($line[0] == '#') continue;
		if (!preg_match('_^(.*)\s+(.*)$_i', $line, $m)) continue;
		if ($m[2] == $data['ext'])
		{
			$data['type'] = $m[1];
			break;
		}
	}
	return true;
}

// Register hook functions
UnbRegisterHook('resolvemimetype', 'UnbHookResolveMIME');

?>
