<?php
if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Define plug-in meta-data
UnbPluginMeta('Filter specific words from posts, signatures and announcements');
UnbPluginMeta('Yves Goergen <contact@unclassified.de>', 'author');
UnbPluginMeta('de en', 'lang');
UnbPluginMeta('unb.stable.1.6 unb.stable.1.6.99', 'version');
UnbPluginMeta('unb.testing.1.6.rc.1', 'version');
UnbPluginMeta('unb.devel.20050914', 'version');
UnbPluginMeta('UnbHookWordFilterConfig', 'config');

if (!UnbPluginEnabled()) return;

function UnbHookWordFilterConfig(&$data)
{
	global $UNB;

	if ($data['request'] == 'fields')
	{
		$field = array();
		$field['fieldtype'] = 'text';
		$field['fieldname'] = 'FilterWords';
		$field['fieldvalue'] = rc('filter_words');
		$field['fieldlabel'] = '_wordfilter.words to filter';
		$field['fielddesc'] = '_wordfilter.words to filter~';
		$field['fieldsize'] = 40;
		$field['fieldlength'] = 1000;
		$data['fields'][] = $field;
	}

	if ($data['request'] == 'handleform')
	{
		if (isset($_POST['FilterWords']))
		{
			$l = array_map('trim', explode('|', $_POST['FilterWords']));
			$UNB['ConfigFile']['filter_words'] = join(' | ', $l);
		}
		$data['result'] = true;
	}

	return true;
}

// Hook function to scan the messages
//
function UnbHookWordfilter(&$data)
{
	// automatically find correct data parameter field
	if (is_string($data)) $msg =& $data;
	elseif (isset($data['message'])) $msg =& $data['message'];
	elseif (isset($data['signature'])) $msg =& $data['signature'];
	elseif (isset($data['out_utitle'])) $msg =& $data['out_utitle'];
	else return true;

	global $UNB;

	$bad = array();
	$good = array();
	foreach ($UNB['_wordfilter_words'] as $word)   // $word must be a plaintext word, no regexp
	{
		$b = '/';
		$g = '';
		$len = strlen($word);
		$n = 1;
		for ($pos = 0; $pos < $len; $pos++)
		{
			if ($pos === 0)   // keep first character
			{
				$ch = regsafe($word{$pos});
				$b .= '(' . $ch . ')(\s*\[.*?\]\s*)?';
				$g .= '\\' . $n++ . '\\' . $n++;
			}
			elseif ($pos === $len - 1)   // keep last character
			{
				$ch = regsafe($word{$pos});
				$b .= '(' . $ch . ')';
				$g .= '\\' . $n++;
			}
			else   // replace anything in between
			{
				$ch = regsafe($word{$pos});
				$b .= $ch . '(\s*\[.*?\]\s*)?';
				$g .= $UNB['_wordfilter_repl'] . '\\' . $n++;
			}
		}
		$b .= '/is';

		$bad[] = $b;
		$good[] = $g;
	}

	$msg = preg_replace($bad, $good, $msg);
	return true;
}

// Register hook functions
UnbRegisterHook('post.preparse', 'UnbHookWordfilter');
UnbRegisterHook('post.signature.preparse', 'UnbHookWordfilter');
UnbRegisterHook('announce.preparse', 'UnbHookWordfilter');
UnbRegisterHook('post.userinfo', 'UnbHookWordfilter');
UnbRegisterHook('post.subject', 'UnbHookWordfilter');

// Initialise variables
// words to filter out
$UNB['_wordfilter_words'] = rc('filter_words', true);
// character to replace these words with
$UNB['_wordfilter_repl'] = code2utf(0x2588);   // 0x2588 box; 0x25CF circle

?>