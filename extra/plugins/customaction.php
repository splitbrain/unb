<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Add a custom action to the ACL system');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050914', 'version');
#UnbPluginMeta('UnbHookCustomActionConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookCustomActionConfig(&$data)
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

// Hook function to check access for a custom action.
// Set $data['grant'] to indicate access or a numeric limit.
//
function UnbHookCustomActionCheckAccess(&$data)
{
	global $UNB;
	$action = $data['action'];
	$forum = $data['forum'];
	$thread = $data['thread'];
	$user = $data['user'];
	$date = $data['date'];
	$isLastPost = $data['isLastPost'];
	$read_only = $data['read_only'];

	switch ($data['action'])
	{
		// This is the right place to add your custom actions. You need to map an action name
		// to a numeric ID that is stored in the database. You should use numbers above 100.
		// To check a global action, add PHP code like this:
		case 'myglobalaction':
			$data['grant'] = $UNB['ACL'][101][0];
			break;

		// To check an action that depends on the current forum, thread or other parameters,
		// add PHP code like this:
		case 'myotheraction':
			if ($read_only)
			{
				$data['grant'] = false;
				break;
			}
			if (isset($UNB['ACL'][102][-$thread]))
			{
				$data['grant'] = $UNB['ACL'][102][-$thread];
				break;
			}
			$data['grant'] = $UNB['ACL'][102][$forum];
			break;

		// Thread IDs are stored as negative numbers, forum IDs are positive numbers.
		// Numeric grants can be implemented similar to the above numeric cases like 'maxattachsize'.
		//
		// To prevent access if the board is in global read-only mode, add the $read_only
		// lines as above.
	}
	return true;
}

// Hook function to determine whether a custom action is a numeric value.
// Set $data['numeric'] = true, if the numeric action ID in $data['action'] describes
// a numeric action limit.
//
function UnbHookCustomActionNumeric(&$data)
{
	switch ($data['action'])
	{
		case 101:
			$data['numeric'] = false;
			break;
		case 102:
			$data['numeric'] = false;
			break;
	}
	return true;
}

// Hook function to determine whether a custom action is forum- or thread-specific.
// Set $data['specific'] = true, if the numeric action ID in $data['action'] describes
// a forum- or thread-specific action.
//
function UnbHookCustomActionSpecific(&$data)
{
	switch ($data['action'])
	{
		case 101:
			$data['specific'] = false;
			break;
		case 102:
			$data['specific'] = true;
			break;
	}
	return true;
}

// Register hook functions
UnbRegisterHook('acl.customaction', 'UnbHookCustomActionCheckAccess');
UnbRegisterHook('acl.customaction.specific', 'UnbHookCustomActionSpecific');
UnbRegisterHook('acl.customaction.numeric', 'UnbHookCustomActionNumeric');

?>