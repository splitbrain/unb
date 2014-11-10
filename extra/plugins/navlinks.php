<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Add navigation links to every page');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050914', 'version');
#UnbPluginMeta('UnbHookNavLinksConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookNavLinksConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		// No web configuration available
	}

	if ($data['request'] == 'handleform')
	{
		// No web configuration available
		$data['result'] = true;
	}

	return true;
}

// Hook function to generate the links
//
function UnbHookNav(&$data)
{
	global $UNB_T;

	$data[] = array(
		'link' => UnbLink('url', null, true),
		'title' => $UNB_T['_navlinks.link1']);

	$data[] = array(
		'link' => UnbLink('url', 'param=value', true),
		'title' => $UNB_T['_navlinks.link2']);

	return true;
}

// Register hook functions
UnbRegisterHook('page.navigation.postlinks', 'UnbHookNav');

#UnbUnregisterHook('page.navigation.postlinks');

?>