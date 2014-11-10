<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// synclang.php
// Language synchronisation tool

// -------------------- BEGIN config area --------------------

// Path to the UNB libraries
$libpath = 'unb_lib/';

// Switch to UTF-8 output
$CHARSET = 'UTF-8';

// Both languages to compare (e.g. 'en' and 'de' or 'en_more' and 'de_more')
$lang1 = 'en';
$lang2 = 'pt';

// -------------------- END config area --------------------

// Text-to-HTML conversion
// Masks special HTML characters: & < >
// Unicode-Safe (optional: $unicode): "&#...;" will not be replaced since this is
//     the alternative description of Unicode characters
// if ($spaces), multiple subsequent " " will be masked in a way that no space character gets lost
//
function t2h($text, $spaces = true, $quotes = true, $nl2br = true, $unicode = false)
{
	$unicode = false;   // we use utf-8 instead

	if ($unicode) $text = preg_replace('/&(?!#x?[\dABCDEF]+?;)/i', '&amp;', $text);
	else $text = preg_replace('/&/', '&amp;', $text);
	$text = preg_replace('/</', '&lt;', $text);
	$text = preg_replace('/>/', '&gt;', $text);
	if ($quotes) $text = str_replace('"', '&quot;', $text);

	// multiple spaces and tabs
	if ($spaces)
	{
		$text = preg_replace('/\t/', '    ', $text);
		$text = preg_replace('/  /', '&nbsp; ', $text);
		$text = preg_replace('/  /', '&nbsp; ', $text);
	}

	if ($nl2br)
	{
		$text = nl2br($text);
		$text = preg_replace('~<br />~', '<br>', $text);
	}

	return $text;
}

header("Content-type: text/html; charset=$CHARSET");
echo "<html><head><title>Language synchronization tool</title>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=$CHARSET'>";
echo "<style type='text/css'>";
echo "td { border-bottom: solid 1px silver; border-right: solid 1px silver; }";
echo "td.odd { background-color: #F0F0FF; }";
echo "b.red { color: red; }";
echo "</style>";
echo "</head><body>";

// Read language arrays
require($libpath . 'lang/' . $lang1 . '.php');
$T1 = $UNB_T;
unset($UNB_T);

require($libpath . 'lang/' . $lang2 . '.php');
$T2 = $UNB_T;
unset($UNB_T);

echo "<table>";
echo "<tr><th>Key</th><th>1: $lang1</th><th>2: $lang2</th></tr>";
$odd = false;

// Find keys on both sides or left only
foreach ($T1 as $key => $val) if ($key != '')
{
	$odd = !$odd;

	$cn = $odd ? ' class=odd' : '';

	if (array_key_exists($key, $T2))
	{
		if ($T2[$key] == '')
		{
			echo "<tr><td$cn>" . t2h($key) . "</td><td$cn>" . t2h($T1[$key]) . "</td><td$cn><b class=red>-empty-</b></td></tr>";
		}
		else
		{
			echo "<tr><td$cn>" . t2h($key) . "</td><td$cn>" . t2h($T1[$key]) . "</td><td$cn>" . t2h($T2[$key]) . "</td></tr>";
		}
		unset($T2[$key]);
	}
	else
	{
		echo "<tr><td$cn>" . t2h($key) . "</td><td$cn>" . t2h($T1[$key]) . "</td><td$cn><b class=red>-missing-</b></td></tr>";
	}
}

// Find keys right only
foreach ($T2 as $key => $val) if ($key != '')
{
	$odd = !$odd;
	$cn = $odd ? ' class=odd' : '';

	echo "<tr><td$cn>" . t2h($key) . "</td><td$cn><b class=red>-missing-</b></td><td$cn>" . t2h($T2[$key]) . "</td></tr>";
}

echo "</table>";
echo "</body></html>";
?>
