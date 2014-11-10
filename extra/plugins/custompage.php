<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Add a new page to the board environment');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.3', 'version');
UnbPluginMeta('unb.devel.20051014', 'version');
#UnbPluginMeta('UnbHookCustomPageConfig', 'config');

// This is a demo plug-in. It shows how custom pages can be added to the board
// at the example of a "custompage". If you want to use it to add your own page,
// you should make a copy of this plug-in, name it by your new page and update
// everything like "custompage" in it to the new page name, i.e. "downloadpage"
// or "imprintpage". You can also modify the plug-in so that it adds more than
// one page at a time. For more information on the hook function parameters
// refer to the online documentation:
// http://newsboard.unclassified.de/devel/docs/plugins#hooks

if (!UnbPluginEnabled()) return;

/*function UnbHookCustomPageConfig(&$data)
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
}*/

// Hook function to handle the page request
//
// This defines a PHP file to be included from forum.php (the base file) the same
// way other pages are included. You can freely design this included file, the
// demo makes the page look like a normal board page. You can show and process
// web forms on that page. If you want to link to other pages or sites, remember
// to use the UnbLink function. You can use the "@custompage" shortcut as its
// first parameter (rename to your new name).
//
function UnbHookCustomPageHandleRequest(&$data)
{
	global $UNB;

	if ($data['request'] == 'custompage')
	{
		$data['include'] = $UNB['LibraryPath'] . 'plugins/custompage.inc.php';
		$data['handled'] = true;
	}

	return true;
}

// Hook function to add the navigation bar link
//
function UnbHookCustomPageAddNavLink(&$data)
{
	global $UNB, $UNB_T;

	$mylink = array(
		'link' => UnbLink('@custompage', null, true),
		'title' => $UNB_T['_custompage.linktitle'],
		'active' => $UNB['ThisPage'] == '@custompage');

	// You can use the array items 'image' and 'imageUrl' to set an icon before
	// the navigation link. See the UnbBeginHTML function for more details.
	// This image must currently reside in the design's img/ directory.

	// Add new link at the end of the links array
	$data[] = $mylink;

	// If you want to add your link to the left of the navigation line, you
	// must add it to the beginning of the links array. This can be done like
	// the following example: (instead of the line above!)
	// $data = array_merge(array($mylink), $data);

	return true;
}

// Hook function to handle the link shortcut
//
// This defines an URL shortcut that can be used with UnbLink. It must be the
// same as the 'request' parameter for this page.
//
function UnbHookCustomPageLinkShortcut(&$data)
{
	if ($data['url'] == '@custompage')
	{
		$data['url'] = rc('baseurl');
		$data['request'] = 'custompage';
	}

	return true;
}

// Hook function to create a short URL
//
// Lets you create a short URL for this page that can be accessed like
// http://hostname/unb/custompage instead of
// http://hostname/unb/forum.php?req=custompage.
//
// New short URLs must be added to the .htaccess file manually! There’s a
// commented template for this.
//
function UnbHookCustomPageLinkShortURL(&$data)
{
	if ($data['params']['req'] == 'custompage')
	{
		$data['link'] = 'custompage';
		$data['params']['req'] = null;
		$data['handled'] = true;
	}

	return true;
}

// Hook function to add my CSS file
//
// Lets you add your own CSS file for the new page. Add your CSS filename to
// the passed $data array. The CSS file must currently reside with the other
// CSS files in the current design's directory.
//
function UnbHookCustomPageAddcss(&$data)
{
	global $UNB;

	#if ($UNB['ThisPage'] == '@custompage') $data[] = 'custompage';

	return true;
}

// Register hook functions
UnbRegisterHook('page.custom.handlerequest', 'UnbHookCustomPageHandleRequest');
UnbRegisterHook('page.navigation.postlinks', 'UnbHookCustomPageAddNavLink');
UnbRegisterHook('link.shortcut.custom', 'UnbHookCustomPageLinkShortcut');
UnbRegisterHook('link.shorturl.custom', 'UnbHookCustomPageLinkShortURL');
UnbRegisterHook('page.addcss', 'UnbHookCustomPageAddcss');

?>