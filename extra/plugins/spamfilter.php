<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Try to detect automatic spam from posts and reject it');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6.3 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.devel.20060716', 'version');
#UnbPluginMeta('UnbHookSpamFilterConfig', 'config');

if (!UnbPluginEnabled()) return;

// Hook function to scan a new post
//
function UnbHookSpamFilter(&$data)
{
	global $UNB, $UNB_T;

	$s = &$data['body'];
	$uid = $data['userid'];

	$HTML_tag_ratio = round(UnbHookSpamFilterHTMLLength($s) / strlen($s) * 100);
	$BBCode_tag_ratio = round(UnbHookSpamFilterBBCodeLength($s) / strlen($s) * 100);
	$Link_count = UnbHookSpamFilterLinkCount($s);
	$Links_per_char = round(UnbHookSpamFilterLinkCount($s) / strlen($s) * 1000);

	$debug = 'HTML_tag_ratio=' . $HTML_tag_ratio . '%, ' .
		'BBCode_tag_ratio=' . $BBCode_tag_ratio . '%, ' .
		'Link_count=' . $Link_count . ', ' .
		'Links_per_character=' . $Links_per_char . '&#x2030;';

	// TODO: Use SURBL <http://www.surbl.org/>
	// TODO: Use a list of block words and assign a score to each, just like in SpamAssassin

	$is_spam = false;
	if (!$uid && ($HTML_tag_ratio > 15 || $BBCode_tag_ratio > 30 || $Links_per_char > 7)) $is_spam = true;
	if ($uid && ($HTML_tag_ratio > 60 || $BBCode_tag_ratio > 50 || $Links_per_char > 30)) $is_spam = true;

	if ($is_spam) $data['error'] = $UNB_T['_spamfilter.error message'] /*. ' (' . $debug . ')'*/;

	// TODO: Add an error log entry with the posting (maybe truncated) and this debug information

	return true;
}

// private functions
function UnbHookSpamFilterHTMLLength($a)
{
	$length = 0;
	while (preg_match('_^.*?(<\s*/?\s*([a-z0-9]{1,20})(?:\s*[^>]{1,100})?>)(.*)$_is', $a, $m))
	{
		$tag = $m[1];
		$tagname = strtolower($m[2]);
		$rest = $m[3];

		$length += strlen($tag);
		$a = $rest;
	}

	return $length;
}

function UnbHookSpamFilterBBCodeLength($a)
{
	$length = 0;
	while (preg_match('_^.*?(\[\s*/?\s*([a-z0-9]{1,20})(?:\s*[^\]]{1,100})?\])(.*)$_is', $a, $m))
	{
		$tag = $m[1];
		$tagname = strtolower($m[2]);
		$rest = $m[3];

		$length += strlen($tag);
		$a = $rest;
	}

	return $length;
}

function UnbHookSpamFilterLinkCount($a)
{
	$count = 0;
	while (preg_match('~^.*?(https?://(?!www.)[a-z0-9\-]+|www.[a-z0-9\-]+|ftps?://[a-z0-9\-_.]+|mailto:[a-z0-9\-_.]+)(.*)$~is', $a, $m))
	{
		$rest = $m[2];

		$count++;
		$a = $rest;
	}

	return $count;
}

// Register hook functions
UnbRegisterHook('post.verifyaccept', 'UnbHookSpamFilter');

?>