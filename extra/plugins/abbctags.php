<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Add more ABBC code tags');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.2', 'version');
UnbPluginMeta('unb.devel.20051012', 'version');

if (!UnbPluginEnabled()) return;

// Hook function to add the tags
//
function UnbHookAddABBCTags(&$data)
{
	global $ABBC, $UNB_T;

	$ABBC['Tags']['ot'] = array(
	'htmlopen0'  => '<div class="offtopic"><b><i>' . $UNB_T['_abbctags.off topic'] . ':</i></b><br />',
	'htmlcont0'  => '$1',
	'htmlclose0' => '</div>',
	'textcont0'  => '    ' . $UNB_T['_abbctags.off topic'] . ': $1',
	'htmlblock'  => true,
	'minparam'   => 0,
	'maxparam'   => 0,
	'openclose'  => true,
	'nocase'     => true,
	'nested'     => true,
	'proccont'   => true,
	'subset'     => ABBC_CUSTOM
	);

	$ABBC['Tags']['center'] = array(
	'htmlopen0'  => '<div style="text-align: center;">',
	'htmlcont0'  => '$1',
	'htmlclose0' => '</div>',
	'textcont0'  => '    $1',
	'htmlblock'  => true,
	'minparam'   => 0,
	'maxparam'   => 0,
	'openclose'  => true,
	'nocase'     => true,
	'nested'     => true,
	'proccont'   => true,
	'subset'     => ABBC_FONT
	);

	$ABBC['Tags']['spell'] = array(
	'htmlopen0'  => '<span class="spelling">',
	'htmlcont0'  => '$1',
	'htmlclose0' => '</span>',
	'textcont0'  => '$1',
	'htmlopen1'  => "~'<span class=\"spelling\" title=\"'.abbcq('$1').'\">'.",
	'htmlcont1'  => "abbcs('$2').",
	'htmlclose1' => "'</span>'",
	'textcont1'  => '$2 [$1]',
	'htmlblock'  => false,
	'minparam'   => 0,
	'maxparam'   => 1,
	'openclose'  => true,
	'nocase'     => true,
	'nested'     => false,
	'proccont'   => true,
	'subset'     => ABBC_CUSTOM
	);

	$ABBC['Tags']['box'] = array(
	'htmlopen0'  => '<div style="margin: 5px 20px; border: solid 2px gray; padding: 4px 8px;">',
	'htmlcont0'  => '$1',
	'htmlclose0' => '</div>',
	'textcont0'  => '    $1',
	'htmlopen1'  => "~'<div style=\"margin: 5px 20px; border: solid 2px '.abbcq('$1').'; padding: 4px 8px;\">'.",
	'htmlcont1'  => "abbcs('$2').",
	'htmlclose1' => "'</div>'",
	'textcont1'  => '    $2',
	'htmlopen2'  => "~'<div style=\"margin: 5px 20px; border: solid '.abbcq('$2').'px '.abbcq('$1').'; padding: 4px 8px;\">'.",
	'htmlcont2'  => "abbcs('$3').",
	'htmlclose2' => "'</div>'",
	'textcont2'  => '    $3',
	'htmlopen3'  => "~'<div style=\"margin: 5px 20px; border: '.abbcq('$3').' '.abbcq('$2').'px '.abbcq('$1').'; padding: 4px 8px;\">'.",
	'htmlcont3'  => "abbcs('$4').",
	'htmlclose3' => "'</div>'",
	'textcont3'  => '    $4',
	'htmlblock'  => true,
	'minparam'   => 0,
	'maxparam'   => 3,
	'openclose'  => true,
	'nocase'     => true,
	'nested'     => true,
	'proccont'   => true,
	'subset'     => ABBC_FONT
	);

	$ABBC['Tags']['mod'] = array(
	'htmlopen0'  => '<div class="moderation"><b><i>' . $UNB_T['_abbctags.moderation'] . ':</i></b><br />',
	'htmlcont0'  => '$1',
	'htmlclose0' => '</div>',
	'textcont0'  => '    ' . $UNB_T['_abbctags.moderation'] . ': $1',
	'htmlblock'  => true,
	'minparam'   => 0,
	'maxparam'   => 0,
	'openclose'  => true,
	'nocase'     => true,
	'nested'     => true,
	'proccont'   => true,
	'subset'     => ABBC_CUSTOM
	);

	return true;
}

// Register hook functions
UnbRegisterHook('abbc.userconfig', 'UnbHookAddABBCTags');

?>