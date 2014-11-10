<?php
// AdvancedBBCode 1.2
// http://software.unclassified.de/abbc
// Copyright 2003-5 by Yves Goergen
//
// Main Parser Module
// You should not need to change anything in here,
// use abbc.conf.php for configuration

// Relevant lines for the maximum parameter count: find MAXPARAM

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

define('ABBC_ALL', -1);           // All tag groups
define('ABBC_NONE', 0);           // No tag groups
define('ABBC_MINIMUM', 1);        // Minimal transformation of line-breaks and HTML control characters
define('ABBC_SIMPLE', 2);         // Simple text formatting like bold, italic etc.
define('ABBC_CODE', 4);           // [code] block
define('ABBC_QUOTE', 8);          // [quote] block
define('ABBC_FONT', 16);          // Font style tags, font family, size etc.
define('ABBC_URL', 32);           // Link tags
define('ABBC_IMG', 64);           // Image tags
define('ABBC_LIST', 128);         // Lists
define('ABBC_SPECIAL', 256);      // Special syntax like *bold* etc.
define('ABBC_DONTINT', 512);      // "Don't interpret" tags
define('ABBC_PARAGRAPH', 1024);   // Paragraph transformation (don't use this)
define('ABBC_CUSTOM', 2048);      // Custom extensions
define('ABBC_SMILIES', 4096);     // Convert smilies to graphics
define('ABBC_HTML', 8192);        // [html] tag
define('ABBC_TABLE', 16384);      // Table tags

// internal version number, do not change this
$ABBC['Version'] = '1.2-20051221(unb)';

require(dirname(__FILE__) . '/abbc.conf.php');

require_once(dirname(__FILE__) . '/geshi.lib.php');

// UNB-specific
UnbCallHookN('abbc.userconfig');


// Initialize some variables for a faster processing at a later time
//
function AbbcInit()
{
	global $ABBC;

	// Special Character that need to be masked for use in reg-exps
	// the '/' at the end is the default delimiting character!
	// nothing should be placed before the '\' replacing or its '\' will be doubled!
	$ABBC['sc'] = array('\\', '^', '$', '.', '[', ']', '|', '(', ')', '?', '*', '+', '{', '}', '/');
	$ABBC['sc2'] = array();
	foreach ($ABBC['sc'] as $c) $ABBC['sc2'][] = '\\' . $c;

	$ABBC['scan'] = array();

	// prepare tag configuration
	$ABBC['MaxTagLength'] = 0;
	foreach ($ABBC['Tags'] as $key => $value)
	{
		$ABBC['Tags'][$key]['level'] = 0;
		$ABBC['Tags'][$key]['start'] = array();

		// get the longest tag and stop looking for a '=' or ']' after $max_taglen characters
		// add 1 for the ending tag's preceeding '/'
		if (strlen($key) + 1 > $ABBC['MaxTagLength']) $ABBC['MaxTagLength'] = strlen($key) + 1;
	}

	// prepare smileys
	$ABBC['SmilieStarts'] = '';
	$ABBC['SmilieCount'] = sizeof($ABBC['Smilies']);
	for ($n = 0; $n < $ABBC['SmilieCount']; $n++)
	{
		// get the first character of this smiley and store it, it it isn't already there
		$start = $ABBC['Smilies'][$n]['code']{0};
		if (strpos($ABBC['SmilieStarts'], $start) === false) $ABBC['SmilieStarts'] .= $start;

		$ABBC['Smilies'][$n]['code_len'] = strlen($ABBC['Smilies'][$n]['code']);

		if ($ABBC['Smilies'][$n]['code_len'] > $ABBC['MaxSmilieLength']) $ABBC['MaxSmilieLength'] = $ABBC['Smilies'][$n]['code_len'];
	}

	// Set initial nice quotes
	// You can change them after including this file
	$ABBC['Config']['use_nicequotes'] = true;
	$ABBC['Config']['nicequote_ls'] = '&lsquo;';
	$ABBC['Config']['nicequote_rs'] = '&rsquo;';
	$ABBC['Config']['nicequote_ld'] = '&ldquo;';
	$ABBC['Config']['nicequote_rd'] = '&rdquo;';

	$ABBC['HighlightWords'] = null;
}

AbbcInit();

// this is used for debug output purposes.
// if you don't use debug output, you can delete this function.
//
/*function t2h($text)
{
	$text = str_replace('&', '&amp;', $text);
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	$text = str_replace("\n", '\\n', $text);
	$text = str_replace("\r", '\\r', $text);
	return $text;
}*/


// Parse and convert ABBC meta-text to HTML
//
// NOTE: Plaintext conversion is currently untested and may be broken. It will
//       be checked as soon as it's used in this application.
//
// in text = (string) ABBC code to convert
// in check = (bool) Perform a syntax check only. If true, a (bool) is returned
//                   that indicates whether the ABBC code is correct or not.
// in totext = (bool) Convert to plaintext instead of HTML
//
// returns (string) HTML or plaintext or (bool) correct code or not
//
function AbbcProc($text, $check = false, $totext = false)
{
	// Clean parameters
	$text = strval($text);

	// debug messages: to activate debug output, remove the comment right below
	$dbg = false;
	#$dbg = ' ';

	// word border characters, only used for SPECIAL syntax and SMILIEs
	// I haven't checked whether it causes problems when changing this
	$wb = " \t.,!\(\)?+\-\n\r";

	global $ABBC;

	$abbc_scan = &$ABBC['Scan'];
	$abbc_tagstack = &$ABBC['TagStack'];
	$abbc_tags = &$ABBC['Tags'];
	$abbc_smilies = &$ABBC['Smilies'];
	$abbc_smilie_count = &$ABBC['SmilieCount'];
	$abbc_max_taglen = &$ABBC['MaxTagLength'];
	$abbc_max_smilielen = &$ABBC['MaxSmilieLength'];
	$abbc_smilie_starts = &$ABBC['SmilieStarts'];

	$minimum = ($ABBC['Config']['subsets'] & ABBC_MINIMUM);

	if ($minimum)
	{
		$text = str_replace("\r", '', $text);

		// remove any lower ASCII control character except HT (0x09) and LF (0x0A)
		$text = preg_replace('_[\x00-\x08\x0B-\x1F]_', "\xEF\xBF\xBD", $text);
	}

	// add a new-line at the beginning and ensure there's one at the end
	// some reg-exps need this to match the first character
	$text = "\r" . $text;
	/*if (substr($text, strlen($text) - 1) != "\r")*/ $text .= "\r";   // Note: this condition cannot be true, all \r are removed

	// 1ST PASS MAIN LOOP

	$abbc_tagstack = array();
	$error_closed = 0;
	$doproc = true;   // status variable for don't-process-content tags
	$arr_lquo = array(' ', "\r", "\n", "\t");
	$tp = '';

	// just to make them a bit shorter...
	$max_taglen = $abbc_max_taglen;
	$smilie_starts = $abbc_smilie_starts;
	$use_special = $ABBC['Config']['subsets'] & ABBC_SPECIAL;

	// collect all characters that may begin a word to highlight
	$highlight_starts = '';
	$abbc_max_highlightlen = 0;
	$abbc_highlight_words = array();
	if (true &&
	    is_array($ABBC['HighlightWords']))
		foreach ($ABBC['HighlightWords'] as $word)
		{
			// Copy all words from the highlight array into our working version that don't
			// contain "[" followed by a valid tagname or its prefix because this breaks bbcode tags!
			$word_ok = true;
#			foreach ($abbc_tags as $tag)
#				if ($tag['nocase'] &&
#				    stripos($word, )
#				    )
			// TODO:
			// To be precise, we'd need to filter out any word from highlighting that includes
			// a "[" directly followed by a prefix of a valid tagname or an entire tagname,
			// directly followed by a "=" or "]". If it doesn't contain a "[" or the following
			// tagname is not valid or too long, it wouldn't be parsed as a bbcode tag anyway
			// an can safely be used for highlighting. But determining this is very expensive
			// so we restrict this to all words with no "[" at all for now.
			if (strpos($word, '[') !== false) $word_ok = false;
			if ($word_ok) $abbc_highlight_words[] = $word;
		}
	foreach ($abbc_highlight_words as $word) if ($word != '')
	{
		if (strpos($highlight_starts, $word{0}) === false) $highlight_starts .= strtoupper($word{0}) . strtolower($word{0});
		if (strlen($word) > $abbc_max_highlightlen) $abbc_max_highlightlen = strlen($word);
	}

	// build auto-URL regular expressions
	if (!$totext && $ABBC['Config']['find_urls'])
	{
		$url_starts = 'FHMWXfhmwx';   // list of characters that an auto-URL can start with

		$beforeclass = '\t\r\n !"\'(),.:;<=>?|';   // character class of symbols before an URL
		$protocols = 'http|https|ftp|ftps|mailto|xmpp';   // list of recognised URL protocols (can be extended)
		$userclass = '%\-.0-9a-z~';   // character class for usernames
		$passclass = '!#$%&()*+\-./0-9=?a-z^\_~';   // character class for passwords
		$pathclass = '!%()+,\-./0-9;=@a-z\]\_~';   // character class for pathnames
		$queryclass = '!#$%&()+,\-./0-9;=?@a-z\]\_~';   // character class for pathnames
		$anchorclass = '!%()+,\-./0-9;=@a-z\]\_~';   // character class for page anchors
		$afterclass = '\t\r\n !"\'():;<>|';   // character class of symbols after an URL
		$after2 = ',[\t\r\n ]|\.[\t\r\n ]|\?[\t\r\n ]';   // alternative list of (multiple) symbols after an URL

		// NOTE: There is a possible problem with auto-lists and auto-URLs:
		// When list entries start with a "*" and have a URL at the end of the line (no trailing whitespace),
		// the automatically inserted [/li] is seen as part of the URL and then included in the link tag.
		// So now, all "[" characters are forbidden in URLs. If this is required, use the [url] tag.
		// This character has before been included in $pathclass, $queryclass and $anchorclass.

		$hostname = '(?:[\-0-9a-z\_]+\.)*[\-0-9a-z\_]+';   // regexp part for hostnames

		// combined regexp to recognise full URLs with protocol
		$urlreg = "_^[$beforeclass](($protocols):(//)?([$userclass]*(:[$passclass]*)?@)?$hostname(/[$pathclass]*?)?(\?[$queryclass]+?)?(#[$anchorclass]*?)?)([$afterclass]|$after2|\$)_i";

		// combined regexp to recognise www links without protocol
		$wwwreg = "_^[$beforeclass](www[0-9]*\.$hostname(/[$pathclass]*)?(\?[$queryclass]+)?(#[$anchorclass]*?)?)([$afterclass]|$after2|\$)_i";
	}

	if (!$totext && $use_special)
	{
		$beforeclass = '\t\r\n !"\'(),.:;<=>?|';   // character class of symbols before an URL
		#$notbefore = "(?![$beforeclass])";   // this asserts that no [before]-thing may occur now
		$notbefore = "(?:[^$beforeclass])";   // this asserts that no [before]-thing may occur now
		$afterclass = '\t\r\n !"\'():;<=>|';   // character class of symbols after an URL
		$after2 = ',[\t\r\n ]|\.[\t\r\n ]|\?[\t\r\n ]';   // alternative list of (multiple) symbols after an URL
		$notafter = "(?![$afterclass]|$after2)";   // this asserts that no [after]-thing may occur now

		$boldreg = "_^([$beforeclass]?)(\*$notafter(?![*])([^*\n]|\*$notafter)*$notbefore\*)([$afterclass]|$after2|\$)_i";   // auto-format: *bold*
		$italicreg = "_^([$beforeclass]?)(/$notafter(?![/*])([^/\n])*$notbefore/)([$afterclass]|$after2|\$)_i";   // auto-format: /italic/
		$ulinereg = "_^([$beforeclass]?)(\_$notafter(?![\_])([^\_\n]|\_$notafter)*$notbefore\_)([$afterclass]|$after2|\$)_i";   // auto-format: _underline_
		$hlinereg = "_^(.?[\r\n]*)([ \t]*----+[ \t]*)([\r\n]|$)_is";   // auto-format: ---- (horiz. line)
	}

	$abbc_scan['len'] = strlen(preg_replace("/\[.*?\]/", '', $text));

	$length = strlen($text);
	for ($pos = 0; $pos < strlen($text); $pos++)   // use explicit strlen() here, because the length will change!
	{
		$prevtp = $tp_bak;
		$tp = $text{$pos};
		$tp_bak = $tp;

		if ($ABBC['Config']['subsets'] & ABBC_SMILIES && $doproc && !$totext)
		{
			// could be a smiley?
			$could_be_smilie = @strpos($smilie_starts, $tp);
			if ($could_be_smilie !== false)
			{
				// not impossible -> we have to check them all
				$smilietext = substr($text, $pos, $abbc_max_smilielen);
				for ($n = 0; $n < $abbc_smilie_count; $n++)
				{
					if ($abbc_smilies[$n]['nocase'] === true &&
					    strncasecmp($smilietext, $abbc_smilies[$n]['code'], $abbc_smilies[$n]['code_len']) === 0 ||
					    $abbc_smilies[$n]['nocase'] === false &&
					    strncmp($smilietext, $abbc_smilies[$n]['code'], $abbc_smilies[$n]['code_len']) === 0)
					{
						// found a smilie -> translate it into an <img> and leave the loop
						$smilie = $abbc_smilies[$n];

						if ($dbg) $dbg .= 'smilie! pos:' . $pos . ', code:' . $smilie['code'] . ', text:' . t2h(trim(substr($text, 0, $pos) . '*' . substr($text, $pos))) . "<br />";

						$endpos = $pos + strlen($smilie['code']) - 1;

						$pos_backup = $pos;
						$diff = AbbcExpandSelectionSmilie($text, $pos, $endpos, $wb);

						// this is the entire tag
						$entiretag = substr($text, $pos, $endpos - $pos + 1);

						// this is the actual replacement process
						$len_before = $endpos - $pos + 1;
						if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_before . ']<br />';
						$newentiretag = AbbcRegReplaceSmilie($entiretag, $n, $wb);
						if ($newentiretag !== $entiretag)
						{
							// smilie was recognised at all (correct white-space outside)
							$entiretag = $newentiretag;
							$len_after = strlen($entiretag);
							if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_after . ']<br />';

							// replace this tag by result of reg-exp replacement
							$text = substr_replace($text, $entiretag, $pos, $len_before);

							// update new cursor position and total length
							$length += $len_after - $len_before;
							$pos += $len_after - 2;
							$tp = '';   // don't process any character in this loop step again

							if ($dbg) $dbg .= 'smilie done -- pos:' . $pos . ', code:' . $smilie['code'] . ', text:' . t2h(trim(substr($text, 0, $pos) . '*' . substr($text, $pos))) . "<br /><br />";

							$abbc_scan['smile']++;
							$abbc_scan['len'] -= strlen($smilie['code']);
							break;
						}
						else
						{
							$pos = $pos_backup;
						}
					}
				}
			}
		}

		if ($doproc && !$totext && $ABBC['Config']['find_urls'])
		{
			// could be an URL?
			$could_be_url = @strpos($url_starts, $tp);
			if ($could_be_url !== false && $abbc_tagstack[sizeof($abbc_tagstack) - 1] != 'url')
			{
				// not impossible -> we have to check the word completely
				$subtext = $prevtp . substr($text, $pos);

				$m1 = $m2 = null;
				if (preg_match($urlreg, $subtext, $m1) ||
				    preg_match($wwwreg, $subtext, $m2))
				{
					if ($m1)
						$entiretag = $m1[1];
					else
						$entiretag = $m2[1];

					$urllen = strlen($entiretag);
					// this is the actual replacement process
#					if (!strcasecmp($m[2], 'mailto'))
#						$tag = 'mail';
#					else
#						$tag = 'url';
# TODO: also check for tags in tagstack above to prevent endless loop
					$entiretag = '[url]' . $entiretag . '[/url]';
					$text = substr_replace($text, $entiretag, $pos, $urllen);

					// update new cursor position and total length
					$length += 11;
					$tp = '[';
				}
			}
		}

		if ($doproc && !$totext && $use_special)
		{
			$entiretag = false;
			$toffset = 0;
			$addtag = false;
			$replacetag = false;

			// could be a formatting keyword? -> check the word completely
			if ($tp === '*' && $abbc_tagstack[sizeof($abbc_tagstack) - 1] != 'b')
			{
				$subtext = $prevtp . substr($text, $pos);
				if (preg_match($boldreg, $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'b';
				}
			}
			elseif ($tp === '/' && $abbc_tagstack[sizeof($abbc_tagstack) - 1] != 'i')
			{
				$subtext = $prevtp . substr($text, $pos);
				if (preg_match($italicreg, $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'i';
				}
			}
			elseif ($tp === '_' && $abbc_tagstack[sizeof($abbc_tagstack) - 1] != 'u')
			{
				$subtext = $prevtp . substr($text, $pos);
				if (preg_match($ulinereg, $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'u';
				}
			}
			elseif (($tp === "\r" || $tp === "\n"))
			{
				$subtext = $prevtp . substr($text, $pos);
				if (preg_match($hlinereg, $subtext, $m))
				{
					// there's an arbitrary number of characters _before_ the line so we need
					// to move our replacement a bit to the right. -1 because the regexp gets
					// one additional character in front (the previous one in the text).
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$replacetag = '[line=#808080]';
				}
			}

			if ($entiretag !== false)
			{
				$tlen = strlen($entiretag);
				// this is the actual replacement process
				if ($addtag !== false)
					$entiretag = '[' . $addtag . ']' . $entiretag . '[/' . $addtag . ']';
				elseif ($replacetag !== false)
					$entiretag = $replacetag;
				$text = substr_replace($text, $entiretag, $pos + $toffset, $tlen);

				// update new cursor position and total length
				if ($addtag !== false)
					$length += 2 * strlen($addtag) + 5;
				elseif ($replacetag !== false)
					$length += strlen($replacetag) - $tlen;
				$tp = '[';
			}
		}

		if ($doproc && !$totext && ($ABBC['Config']['subsets'] & ABBC_LIST))
		{
			$entiretag = false;
			$toffset = 0;
			$addtag = false;
			$replacetag = false;
			$tlenextra = 0;

			if ($tp === '*')
			{
				$subtext = ($prevtp ? $prevtp : "\n") . substr($text, $pos);
				if (preg_match('_^([\r\n])\* (.*?)[\r\n]_', $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'li';
					$tlenextra = 2;   // we skip 2 characters in our text
				}
				elseif (preg_match('_^([\r\n])\*\* (.*?)[\r\n]_', $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'li2';   #array('li', 'li');
					$tlenextra = 3;   // we skip 3 characters in our text
				}
				elseif (preg_match('_^([\r\n])\*\*\* (.*?)[\r\n]_', $subtext, $m))
				{
					$toffset = strlen($m[1]) - 1;
					$entiretag = $m[2];
					$addtag = 'li3';   #array('li', 'li');
					$tlenextra = 4;   // we skip 3 characters in our text
				}
			}

			if ($entiretag !== false)
			{
				$tlen = strlen($entiretag);
				// this is the actual replacement process
				if ($addtag !== false)
				{
					if (is_string($addtag))
					{
						$addtag_open = '[' . $addtag . ']';
						$addtag_close = '[/' . $addtag . ']';
					}
					elseif (is_array($addtag))
					{
						$addtag_open = '';
						$addtag_close = '';
						foreach ($addtag as $t)
						{
							$addtag_open .= '[' . $t . ']';
							$addtag_close = '[/' . $t . ']' . $addtag_close;
						}
					}
					$entiretag = $addtag_open . $entiretag . $addtag_close;
				}
				elseif ($replacetag !== false)
					$entiretag = $replacetag;
				$text = substr_replace($text, $entiretag, $pos + $toffset, $tlen + $tlenextra);

				// update new cursor position and total length
				if ($addtag !== false)
					$length += strlen($addtag_open) + strlen($addtag_close) - $tlenextra;
				elseif ($replacetag !== false)
					$length += strlen($replacetag) - $tlen;
				$tp = '[';
			}
		}

		if ($doproc && !$totext && sizeof($abbc_highlight_words))
		{
			// could be a word to highlight?
			$could_be_highlight = @strpos($highlight_starts, $tp);
			if ($could_be_highlight !== false)
			{
				// not impossible -> we have to check the word completely
				$subtext = substr($text, $pos, $abbc_max_highlightlen);
				foreach ($abbc_highlight_words as $word)
				{
					if (strlen($word) == 0) continue;

					if (strncasecmp($subtext, $word, strlen($word)) === 0)
					{
						// we have found a word. now highlight it

						// this is the entire word
						$entiretag = substr($subtext, 0, strlen($word));

						$entiretag = str_replace('&', '&amp;', $entiretag);
						$entiretag = str_replace('<', '&lt;', $entiretag);
						$entiretag = str_replace('>', '&gt;', $entiretag);
						if ($entiretag{0} === ' ')
							$entiretag = '&nbsp;' . substr($entiretag, 1);
						$add_len = strlen($entiretag) - strlen($word);

						// this is the actual replacement process
						$entiretag = '<span class="highlight">' . $entiretag . '</span>';
						$text = substr_replace($text, $entiretag, $pos, strlen($word));

						// update new cursor position and total length
						$length += 31 + $add_len;
						$pos += strlen($word) + 31 + $add_len - 1;
						$tp = '';   // don't process any character in this loop step again

						break;
					}
				}
			}
		}

		if ($minimum && !$totext)
		{
			if ($tp === '&')
			{
				$text = substr_replace($text, '&amp;', $pos, 1);
				$pos += 4;
				$length += 4;
				continue;
			}
			elseif ($tp === '<')
			{
				$text = substr_replace($text, '&lt;', $pos, 1);
				$pos += 3;
				$length += 3;
				continue;
			}
			elseif ($tp === '>')
			{
				$text = substr_replace($text, '&gt;', $pos, 1);
				$pos += 3;
				$length += 3;
				continue;
			}
			elseif ($tp === '[' && $text{$pos - 1} === '\\' && $doproc)
			{
				$text = substr_replace($text, '', $pos - 1, 1);
				$pos -= 1;
				$length -= 1;
				continue;
			}
			elseif ($tp === '\'' &&
			        $use_special &&
			        in_array($text{$pos - 1}, $arr_lquo) &&
			        $doproc &&
			        $ABBC['Config']['use_nicequotes'])
			{
				$text = substr_replace($text, $ABBC['Config']['nicequote_ls'], $pos, 1);
				$pos += strlen($ABBC['Config']['nicequote_ls']) - 1;
				$length += strlen($ABBC['Config']['nicequote_ls']) - 1;
				continue;
			}
			elseif ($tp === '\'' &&
			        $use_special &&
			        $doproc &&
			        $ABBC['Config']['use_nicequotes'])
			{
				$text = substr_replace($text, $ABBC['Config']['nicequote_rs'], $pos, 1);
				$pos += strlen($ABBC['Config']['nicequote_rs']) - 1;
				$length += strlen($ABBC['Config']['nicequote_rs']) - 1;
				continue;
			}
			elseif ($tp === '"' &&
			        $use_special &&
			        in_array($text{$pos - 1}, $arr_lquo) &&
			        $doproc &&
			        $ABBC['Config']['use_nicequotes'])
			{
				$text = substr_replace($text, $ABBC['Config']['nicequote_ld'], $pos, 1);
				$pos += strlen($ABBC['Config']['nicequote_ld']) - 1;
				$length += strlen($ABBC['Config']['nicequote_ld']) - 1;
				continue;
			}
			elseif ($tp === '"' &&
			        $use_special &&
			        $doproc &&
			        $ABBC['Config']['use_nicequotes'])
			{
				$text = substr_replace($text, $ABBC['Config']['nicequote_rd'], $pos, 1);
				$pos += strlen($ABBC['Config']['nicequote_rd']) - 1;
				$length += strlen($ABBC['Config']['nicequote_rd']) - 1;
				continue;
			}
		}
		if ($tp === '[' && $text{$pos - 1} !== '\\' && $minimum)
		{
			$endpos = $pos + 1;
			while (($text{$endpos} !== ']' || $text{$endpos - 1} === '\\') &&
			       $text{$endpos} !== '=' &&
			       $endpos - $pos - 1 <= $max_taglen)
				$endpos++;
			$thistag = substr($text, $pos + 1, $endpos - ($pos + 1));
			if ($endpos - $pos - 1 > $max_taglen)
			{
				// we flew out of the search loop as there was no end in a reasonable distance
				// so this can't be a valid tag
				// Note: it's "-1" instead of "+1" because we have to include the "[" and ("=" or "]") in the counting
				if ($dbg) $dbg .= 'no tag:' . $thistag . '<br />';

				continue;
			}
			// now we have the tagname separated

			// find tag's end to jump to it when we're finished here...
			$stored_pos = $endpos;
			while (($text{$stored_pos} !== ']' || $text{$stored_pos - 1} === '\\') &&
			       $text{$stored_pos} !== "\n" &&
			       $stored_pos < $length)
				$stored_pos++;
			// if there was a new-line, this can't be a serious tag... let's just ignore it!
			if ($text{$stored_pos} === "\n") continue;

			// check for closing tag
			if ($thistag{0} === '/')
			{
				if ($text{$endpos} === '=') continue;   // closing tags have no parameters

				$closingtag = true;
				$thistag = substr($thistag, 1);   // remove "/"
				if ($thistag === false) $thistag = '';   // we need a STRING for our tags index!
			}
			else
			{
				$closingtag = false;
			}

			// check for valid tagname
			if ($dbg) $dbg .= '++ tagstack:' . join('|', $abbc_tagstack) . '<br />';
			if ($thistag === '' || AbbcValidTagname($thistag))
			{
				// if needed subset is not enabled, skip this tag
				if ($ABBC['Config']['subsets'] & $abbc_tags[$thistag]['subset'])
				{
					if (!$closingtag && $doproc)
					{
						// current tag is OPENING

						if ($dbg) $dbg .= 'pos:' . $pos . ', thistag:' . $thistag . ', tagstack:' . join('|', $abbc_tagstack) . ', text:' . t2h(trim(substr($text, 0, $pos) . '*' . substr($text, $pos))) . '<br />';

						while (($text{$endpos} !== ']' || $text{$endpos - 1} === '\\') &&
						       $text{$endpos} !== "\n" &&
						       $endpos < $length)
							$endpos++;
						if ($text{$endpos} === "\n")
						{
							// oops, there was a new-line before the tag's end
							// so there must have been a syntax error
							// we'll ignore this 'tag' for now
							continue;
						}

						// check whether it has a closing tag, too
						if ($abbc_tags[$thistag]['openclose'])
						{
							// we expect a closing tag so we just do some stack stuff this time...

							// check if it may be nested
							if ($abbc_tags[$thistag]['nested'] || $abbc_tags[$thistag]['level'] === 0)
							{
								// store current position and update tag's current nesting layer
								array_push($abbc_tags[$thistag]['start'], $pos);
								$abbc_tags[$thistag]['level']++;
								if ($dbg) $dbg .= 'new level:' . $abbc_tags[$thistag]['level'] . '<br />';

								// correct nesting check: put this tag on top of the 'global' stack
								array_push($abbc_tagstack, $thistag);

								// check if we should process its content
								if (!$abbc_tags[$thistag]['proccont'])
								{
									// NO, save this to the status variable
									// we first continue processing any tags when a closing tag matched the topmost tagstack element,
									// that's when we found the closing tag to this one.
									$doproc = false;
								}
							}
							else
							{
								if ($dbg) $dbg .= '<b>Warning: trying to nest a tag which isn\'t allowed to...</b><br />';
							}
						}
						else
						{
							// this tag has no closing tag, so we have to process it NOW:

							$diff = AbbcExpandSelection($text, $pos, $endpos);

							// this is the entire tag
							$entiretag = substr($text, $pos, $endpos - $pos + 1);

							// this is the actual replacement process
							$len_before = $endpos - $pos + 1;
							if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_before . ']<br />';
							$entiretag = AbbcRegReplaceTag($entiretag, $thistag, $totext);
							$len_after = strlen($entiretag) + $diff;
							if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_after . ']<br />';

							// update new cursor position and total length
							$stored_pos += $len_after - $len_before;
							if (!$abbc_tags[$thistag]['htmlblock']) $stored_pos -= $diff;
							$length += $len_after - $len_before;

							// replace this tag by result of reg-exp replacement
							$text = substr_replace($text, $entiretag, $pos, $len_before);
						}
					}
					elseif ($closingtag)
					{
						// current tag is CLOSING
						if ($dbg) $dbg .= 'closing:' . $thistag . ', level:' . $abbc_tags[$thistag]['level'] . '<br />';

						// check if tag was opened at all
						if ($abbc_tags[$thistag]['level'] > 0)
						{
							$corr_tag = array_pop($abbc_tagstack);

							if ($dbg) $dbg .= 'pos:' . $pos . ', thistag:/' . $thistag . ', tagstack:' . join('|', $abbc_tagstack) . ', text:' . t2h(trim(substr($text, 0, $pos) . '*' . substr($text, $pos))) . '<br />';

							if ($dbg) $dbg .= '++ corr_tag = ' . $corr_tag . '<br />';
							// correct nesting check: is this tag the next one to be closed?
							if ($thistag === $corr_tag)
							{
								// process entire tag area with reg-exps
								$startpos = array_pop($abbc_tags[$thistag]['start']);
								if ($dbg) $dbg .= 'startpos:' . $startpos . '<br />';
								$abbc_tags[$thistag]['level']--;

								$diff = AbbcExpandSelection($text, $startpos, $endpos);

								// this is the entire tag w/ its contents
								$entiretag = substr($text, $startpos, $endpos - $startpos + 1);

								// this is the actual replacement process
								$len_before = $endpos - $startpos + 1;
								if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_before . ']<br />';
								$entiretag = AbbcRegReplaceTag($entiretag, $thistag, $totext);
								$len_after = strlen($entiretag) + $diff;
								if ($dbg) $dbg .= t2h($entiretag) . ' [len:' . strlen($entiretag) . ',' . $len_after . ']<br />';

								// update new cursor position and total length
								$stored_pos += $len_after - $len_before;
								if (!$abbc_tags[$thistag]['htmlblock']) $stored_pos -= $diff;
								$length += $len_after - $len_before;

								// replace this tag by result of reg-exp replacement
								$text = substr_replace($text, $entiretag, $startpos, $len_before);

								// Set the new $tp for the next run to what we have just changed
								$tp_bak = substr($entiretag, -1);

								// if we were in don't-process mode, switch back again
								$doproc = true;
							}
							elseif ($doproc && !$totext)
							{
								// NO, the tag that's to be closed wasn't opened on this level.
								// So we mark it as 'bad' and that's all.
								// The corresponding opening tag (if present) will remain opened and appear in
								// the remaining tagstack, where we can mark it afterwards.

								if ($dbg) $dbg .= '<b>ERROR: ' . $thistag . ' is closed instead of ' . $corr_tag . ' at position ' . $pos . ' -- ' . join('|', $abbc_tagstack) . '</b><br />';

								// first, let's push the correct tag back onto the stack so that the correct closing tag
								// will be recognized!
								array_push($abbc_tagstack, $corr_tag);

								$error_closed++;

								// highlight incorrectly nested closing tag
								$newtext = '<span class="ecl">' . substr($text, $pos, $endpos - $pos + 1) . '</span>';
								$text = substr_replace(
									$text,
									$newtext,
									$pos,
									$endpos - $pos + 1);
								$stored_pos += 25;   // and we 'skip' some more characters now...
								$length += 25;
							}
							else
							{
								// push the correct tag back onto the stack so that the correct closing tag
								// will be recognized -- in any case, even !$doproc
								array_push($abbc_tagstack, $corr_tag);
							}
						}
						elseif ($doproc && !$totext)
						{
							if ($dbg) $dbg .= '<b>Warning: trying to close a tag (' . $thistag . ') with level 0...</b><br />';

							// highlight incorrectly nested closing tag
							$newtext = '<span class="ecl">' . t2h(substr($text, $pos, $endpos - $pos + 1)) . '</span>';
							$text = substr_replace(
								$text,
								$newtext,
								$pos,
								$endpos - $pos + 1 /*length*/);
							$error_closed++;

							// and we 'skip' some more characters now...
							$stored_pos += strlen($newtext) - ($endpos - $pos + 1);
							#$length += 25;
							$length = strlen($text);
						}
					}
				} // end: subset check
			}
			else
			{
				if ($dbg) $dbg .= 'Warning: ' . $thistag . ' is not a valid BBCode tag!<br />';

				// don't skip over this, there may be data to process 'inside' this 'tag'
				$stored_pos = $pos;
			}

			// ok, we finished this tag, let's jump to its end end continue after it
			$pos = $stored_pos;

			if ($dbg) $dbg .= 'finish -- pos:' . $pos . ', thistag:' . ($closingtag ? '/' : '') . $thistag . ', tagstack:' . join('|', $abbc_tagstack) . ', text:' . t2h(trim(substr($text, 0, $pos) . '*' . substr($text, $pos))) . '<br /><br />';
		}

		// check if we're at the end of $text and there are still open tags left
		// add them here and they will be processed normally
		if ($ABBC['Config']['auto_close_tags'] && $pos == strlen($text) - 1 && sizeof($abbc_tagstack) > 0)
		{
			$close_tag = '[/' . $abbc_tagstack[sizeof($abbc_tagstack) - 1] . ']';
			$text .= $close_tag;
			$length += strlen($close_tag);
		}

	} // for each in text loop

	// This should be the same - having the $length counting ACTIVATED!
	// But it isn't. Don't know why... so I'm not using it any more
	#echo $length . '|' . strlen($text) . '<br />';

	if ($check)
	{
		// clean up remeining opened tags...
		foreach ($abbc_tags as $key => $value)
		{
			$abbc_tags[$key]['level'] = 0;
			$abbc_tags[$key]['start'] = array();
		}
		return sizeof($abbc_tagstack) + $error_closed;
	}

	if (sizeof($abbc_tagstack) && $minimum && !$totext)
	{
		if ($dbg) $dbg .= '<b>remaining tagstack: ' . join('|', $abbc_tagstack) . '</b><br />';

		// highlight incorrectly closed, remaining opening tags
		while ($tag = array_pop($abbc_tagstack))
		{
			$abbc_tags[$tag]['level']--;
			$startpos = array_pop($abbc_tags[$tag]['start']);
			$taglen = strpos($text, ']', $startpos) - $startpos + 1;

			$text = substr_replace(
				$text,
				'<span class="eop">' . t2h(substr($text, $startpos, $taglen)) . '</span>',
				$startpos,
				$taglen);

			// Since we go through the tagstack from right to left, and elements were added to be the same
			// order as their appearance in $text, we don't change anything that would affect something after
			// it. So there's no need to adjust any following start positions of other tags :-)
		}
	}
	// clean up remeining opened tags...
	foreach ($abbc_tags as $key => $value)
	{
		$abbc_tags[$key]['level'] = 0;
		$abbc_tags[$key]['start'] = array();
	}

	//--------------------------

	if ($minimum && !$totext)
	{
		$text = str_replace("\n", "<br />\n", $text);
		$text = str_replace("\r", "\n", $text);   // Line breaks from [code] blocks: don't add <br/> outside <li/>
	}

	// remove the added \r's if they are still there
	if ($text{0} == "\r") $text = substr($text, 1);
	if ($text{strlen($text) - 1} == "\r") $text = substr($text, 0, strlen($text) - 1);

	if ($minimum && !$totext)
	{
		// these are the new-lines that shouldn't be changed into <br />
		$text = str_replace("\r", "\n", $text);

		// multiple spaces and tabs
		$text = str_replace("\t", '    ', $text);
		$text = str_replace("\n ", "\n&nbsp;", $text);
		#$text = str_replace('> ', '>&nbsp;', $text);
			// NOTE: This line broke testforum/910,2#9361 and I removed it.
			//       Multiple subsequent smilies seem to work fine, still.
		$text = str_replace('  ', '&nbsp; ', $text);
		$text = str_replace('  ', '&nbsp; ', $text);
	}

	// paragraph translation
	if ($ABBC['Config']['subsets'] & ABBC_PARAGRAPH && !$totext)
	{
		while (substr($text, strlen($text) - 5) == "<br />\n") $text = substr($text, 0, strlen($text) - 5);
		$text = str_replace("\n<br />\n<br />", "\n</div>\n<div class=\"p\"><br />", $text);
		$text = str_replace('<div class="quote">', '<div class="quote"><div class="p">', $text);
		$text = str_replace("\n<br />", "\n</div>\n<div class=\"p\">", $text);
		$text = str_replace('</div><br />', "</div>\n</div>\n<div class=\"p\">", $text);
		$text = str_replace("<br />\n</div>", "\n</div>", $text);
		$text = "<div class=\"p\">\n" . $text . "</div>\n";
		$text = str_replace("<div class=\"p\">\n</div>", '</div>&nbsp;<div class="p">', $text);

		// undo \n masking of [code] blocks
		$text = str_replace('&#x0A;', "<br />\n", $text);
	}

	if ($minimum && !$totext)
	{
		// multiple spaces and tabs - tidy up for img tags
		$text = str_replace('>&nbsp; <', '> &nbsp;<', $text);
	}

	if ($minimum && !$totext)
	{
		if ($ABBC['Config']['output_div'] == 1) $text = '<div class="abbc">' . $text . '</div>';
		if ($ABBC['Config']['output_div'] == 2) $text = '<span class="abbc">' . $text . '</span>';
	}

	if ($dbg) echo '<div class="abbc">' . $dbg . "</div><br />\r\n\r\n";

	return $text;

}   // function abbc($text)


// Check whether the given ABBC code is syntactically correct.
//
// This is an alias function of AbbcProc provided for convenience.
//
function AbbcCheck($text)
{
	return AbbcProc($text, true);
}


// Add syntax highlighting to a string
//
// in text = (string) Text to highlight
// in incode = (string) Source code language name
//
// returns (string) colourful HTML
//
function AbbcSyntaxHighlight($text, $incode = false)
{
	global $ABBC;

	// Syntax Highlighting using the GeSHi class
	$lang = '';
	if ($incode) $lang = $incode;
	if ($lang == 'html') $lang = 'html4strict';
	if ($lang == 'js') $lang = 'javascript';

#	if ($lang == 'html4strict') $lang = '';   // Doesn't work right now
#	if ($lang == 'xml') $lang = '';   // Doesn't work right now

	if ($lang != '')
	{
		$text = str_replace('&#x24;', '$', $text);

		$geshi = new GeSHi(AbbcH2T($text), $lang);
		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
		$geshi->set_line_style('', '');
		$geshi->set_line_class('cont even', 'cont odd');
		#$geshi->start_line_numbers_at(1);
		#$geshi->enable_classes();
		#$geshi->get_stylesheet(false);
		$geshi->set_tab_width(4);
		$geshi->set_link_styles(GESHI_LINK, 'border-bottom: dotted 1px blue;');
		$geshi->set_link_styles(GESHI_HOVER, '');
		$geshi->set_link_styles(GESHI_ACTIVE, '');
		$geshi->set_link_styles(GESHI_VISITED, 'border-bottom: dotted 1px blue;');

		$geshi->set_overall_class('');
		$geshi->set_overall_style('');
		$geshi->set_code_style('');
		$geshi->set_keyword_group_style(1, 'color: blue;');
		$geshi->set_keyword_group_style(2, 'color: blue;');
		$geshi->set_keyword_group_style(3, 'color: darkblue;');
		$geshi->set_keyword_group_style(4, 'color: blue;');
		$geshi->set_comments_style(1, 'color: gray;');
		$geshi->set_comments_style(2, 'color: gray;');
		$geshi->set_comments_style('MULTI', 'color: gray;');
		$geshi->set_escape_characters_style('color: #800080;');
		$geshi->set_symbols_style('color: red;');
		$geshi->set_strings_style('color: darkcyan;');
		$geshi->set_numbers_style('color: darkcyan;');
		$geshi->set_methods_style(1, 'color: black;');
		$geshi->set_methods_style(2, 'color: black;');
		if (in_array($lang, array('perl', 'php')))
			$geshi->set_regexps_style(0, 'color: black;');   // make $-variables black

		// TODO: this should really be handled by the GeSHi library
		if ($geshi->error != GESHI_ERROR_NO_SUCH_LANG)
		{
			$text = $geshi->parse_code();
			$text = str_replace("\n", "\r", $text);
			$text = str_replace('<br />', '', $text);
		}
		else
		{
			// For unrecognised languages, use default processing for now
			$lines = explode(endl, $text);
			foreach ($lines as $no => $line)
			{
				$cls = $no % 2 == 0 ? 'cont even' : 'cont odd';
				$lines[$no] = '<div class="' . $cls . '">' . rtrim($line) . '</div>';
			}
			$text = join("\r", $lines);
		}
	}
	elseif ($ABBC['Config']['subsets'] & ABBC_PARAGRAPH)
	{
		$text = str_replace("\n", '&#x0A;', $text);
	}
	return $text;
}


// Actual reg-exp replacement of an area containing only a single level of a
// BBCode tag
//
// in text = (string) entire text with surrounding tags. ex: "[b]bold text[/b]"
// in tagname = (string) tag ID to process here. ex: "b"
// in totext = (bool) true: process for text/plain output
//                    false: normal HTML output
//
// returns (string) tag's output as HTML code or plaintext
//
function AbbcRegReplaceTag($text, $tagname, $totext = false)
{
	global $ABBC;
	$thistag = &$ABBC['Tags'][$tagname];

	$openclose = $thistag['openclose'];

	// prepare tagname for use in reg-exps:
	// Special Character that need to be masked for use in reg-exps
	$tagreg = str_replace($ABBC['sc'], $ABBC['sc2'], $tagname);

	// now the reg-exps for the bbcode parameters are formed
	// at the moment only 0 to 2 parameters are allowed
	// [This is relevant for the maximum parameter count. MAXPARAM]
	$params0 = '';
	$params1 = '=([^\\]]*)';
	$params2 = '=([^\\]:]*):([^\\]]*)';
	$params3 = '=([^\\]:]*):([^\\]:]*):([^\\]]*)';

	// TODO: Allow \] to be part of the tag parameter, i.e. for:
	//       [quote=[tag\]name] Quote [/quote]
	//       See: http://newsboard.unclassified.de/forum/thread/859
	//$params1 = '=(.*?)(?<!\\\\)\\]';

	// case-sensitivity reg-exp modifier is set
	if ($thistag['nocase']) $nocase = 'i'; else $nocase = '';
	$nocase .= 's';   // for PCRE_DOTALL

	// reg-exps for opening and closing tags are set
	// [This is relevant for the maximum parameter count. MAXPARAM]
	$regopen[0] = "\[$tagreg$params0\]";
	$regopen[1] = "\[$tagreg$params1\]";
	$regopen[2] = "\[$tagreg$params2\]";
	$regopen[3] = "\[$tagreg$params3\]";
	$regclose = $openclose ? "\[\/$tagreg\]" : '';
	$between = $openclose ? '(.*)' : '';

	// Note: instead of .* we could do the following:
	//       ungreedy matching (.*?) and embed the entire reg-exp into ^...$ so that the entire string MUST be used.
	//       this is for the case we have an (already marked) WRONG closing tag in a deeper level. of course, we can't
	//       let this be used for premature end of our pattern!
	// But:  this won't work when using AbbcExpandSelection() which adds some spaces and new-lines around our tags...

	// remove new-lines around the tags where needed
	if ($thistag['htmlblock'])
	{
		if ($openclose)
		{
			for ($n = $thistag['maxparam']; $n >= $thistag['minparam']; $n--)
			{
				$text = preg_replace("/($regopen[$n])[ \t]*?\n/$nocase", '$1', $text);   // remove new-line after beginning of block
			}
			$text = preg_replace("/($regclose)[ \t]*?\n/$nocase", '$1', $text);   // remove new-line after end of block
		}
		else
		{
			for ($n = $thistag['maxparam']; $n >= $thistag['minparam']; $n--)
			{
				$text = preg_replace("/\n[ \t]*?($regopen[$n])[ \t]*?\n/$nocase", '$1', $text);   // remove new-line around block
			}
		}
	}

	// echo'ing a variable that contains $name would echo the contents of the php varialbe $name. that's pretty uncool.
	// so we replace the $ character by its HTML entitiy for a moment:
	#$text = str_replace('$', '&#x24;', $text);
	// TODO,FIXME: Big problem: URLs containing the $ character will be converted into HTML by UnbLink()
	//             so this HTML code will not be the same afterwards. The URL is modified and broken then!
	//             But this is not an easy thing to fix... :(
	#$text = str_replace('$', '\$', $text);

	// now we compose the actually used reg-exp and html translation
	for ($n = $thistag['maxparam']; $n >= $thistag['minparam']; $n--)
	{
		$mod = $nocase;

		// check for PHP code
		if (!$totext)
		{
			$htmlopen = $thistag['htmlopen' . $n];
			if ($htmlopen !== '' && $htmlopen{0} === '~')
			{
				$mod .= 'e';
				$htmlopen = substr($htmlopen, 1);
			}

			$html = $htmlopen . ($openclose ? $thistag['htmlcont' . $n] . $thistag['htmlclose' . $n] : '');
		}
		else  // totext
		{
			$html = $thistag['textcont' . $n];
			if ($thistag['htmlblock']) $html = "\n" . $html . "\n";
		}

		$reg = "/^(\s*?)$regopen[$n]$between$regclose(\s*?)$/$mod";

		if ($html !== '')
		{
			// since we add a capturing parenthesis, we have to change all back-reference numbers
			// BUT only, if $html wasn't empty anyway, like for the [rem] tag
			// [This is relevant for the maximum parameter count. MAXPARAM]
			$html = str_replace('$4', '$5', $html);
			$html = str_replace('$3', '$4', $html);
			$html = str_replace('$2', '$3', $html);
			$html = str_replace('$1', '$2', $html);

			$maxref = 2;
			#while (strpos($html, '$' . $maxref) !== false) $maxref++;
			for ($m = 2; $m <= 5; $m++)
				if (strpos($html, '$' . $m) !== false) $maxref = $m + 1;

			if (strpos($mod, 'e') === false)
			{
				// no PHP code, just add the $'s
				$html = '$1' . $html . '$' . $maxref;
			}
			else
			{
				// PHP code: put them in quotes
				$html = '\'$1\'.' . $html . '.\'$' . $maxref . '\'';
			}

			$html .= $thistag['htmlblock'] ? "\r" : '';
		}

		// now it's time to actually perform the translation!
		#echo "<p><pre>" . t2h($reg) . "</pre></p>";   // debug
		#echo "<p><pre>" . t2h($html) . "</pre></p>";   // debug

		// does the text match this regexp? if not, we have a different parameters count.
		if (preg_match($reg, $text, $m))
		{
			if (!$maxref) $maxref = 3;

			// see if the pre-last capturing pattern is empty. this would be the actual tag content.
			if ($openclose && $thistag['omitempty'] && trim($m[$maxref - 1]) === '')
			{
				// tag content is empty, omit the entire tag.
				// we do this by building a new to-replace-by variable that only contains the surrounding spaces
				if (strpos($mod, 'e') === false)
				{
					// no PHP code, just add the $'s
					$html = '$1' . '$' . $maxref;
				}
				else
				{
					// PHP code: put them in quotes
					$html = '\'$1$' . $maxref . '\'';
				}
			}

			$text = preg_replace($reg, $html, $text);
		}
	}

	// Change the $ characters back to normal:
	#$text = str_replace('&#x24;', '$', $text);

	if ($tagname == 'img') $ABBC['Scan']['img']++;

	return $text;
}

// Actual reg-exp replacement of an area containing a smilie
//
// in text = (string) smilie code incl surrounding characters
// in n = (int) smilie number
// in wb = (string) set of accepted whitespace characters
//
// returns (string) smilie's HTML code
//
function AbbcRegReplaceSmilie($text, $n, $wb = '')
{
	global $ABBC;
	$abbc_smilies = &$ABBC['Smilies'];

	$codereg = $code = $abbc_smilies[$n]['code'];

	// prepare smilie code for use in reg-exps
	// Special Character that need to be masked for use in reg-exps
	$codereg = str_replace($ABBC['sc'], $ABBC['sc2'], $codereg);

	// case-sensitivity reg-exp modifier is set
	if ($abbc_smilies[$n]['nocase']) $nocase = 'i'; else $nocase = '';
	$nocase .= 's';   // for PCRE_DOTALL

	// if there is an alignment or size set for this smilie, then use it!
	$style = '';
	if ($abbc_smilies[$n]['align'] != '') $style .= 'vertical-align:' . $abbc_smilies[$n]['align'] . ';';
	if ($abbc_smilies[$n]['width'] != '') $style .= 'width:' . $abbc_smilies[$n]['width'] . 'px;';
	if ($abbc_smilies[$n]['height'] != '') $style .= 'height:' . $abbc_smilies[$n]['height'] . 'px;';
	if ($style != '') $style = ' style="' . htmlspecialchars($style) . '"';

	// now we compose the actually used reg-exp and html translation
	if ($wb != '')
		$reg = "/^([$wb>\\]]+?)$codereg([$wb\\[]+?)$/$nocase";
	else
		$reg = "/^$codereg$/$nocase";

	if ($abbc_smilies[$n]['img'] == '')
		$html = '$1$2';
	elseif ($text != '')
	{
		$html = '$1<img src="' . $ABBC['Config']['smileurl'] . $abbc_smilies[$n]['img'] . '" title="' . htmlspecialchars($abbc_smilies[$n]['code']) . '" alt="' . htmlspecialchars($abbc_smilies[$n]['code']) . '"' . $style . ' class="smilie" />$2';
	}
	else
	{
		// we can return the entire <img> tag if there's no text to process (like for the smilie tag [:])
		return '<img src="' . $ABBC['Config']['smileurl'] . $abbc_smilies[$n]['img'] . '" title="' . htmlspecialchars($abbc_smilies[$n]['code']) . '" alt="' . htmlspecialchars($abbc_smilies[$n]['code']) . '"' . $style . ' class="smilie" />';
	}

	if (!($ABBC['Config']['subsets'] & ABBC_MINIMUM))
	{
		// someone didn't want us to make <img> html code but rather a bb-code smilie tag
		// so why not...
		$html = '$1[img]' . $ABBC['Config']['smileurl'] . $abbc_smilies[$n]['img'] . '[/img]$2';
	}

	// now it's time to actually perform the translation!
	// echo'ing a variable that contains $name would echo the contents of the php varialbe $name. that's pretty uncool
	$text = str_replace('$', '&#x24;', $text);
	$text = preg_replace($reg, $html, $text);
	$text = str_replace('&#x24;', '$', $text);

	return $text;
}

// Looks around a 'selected' [tag](...[/tag]) for (spaces and) NEW-LINES and
// expands the selection to include them.
//
// Expansion stops before first non-space && non-new-line char or after first
// new-line-char. For performance, strlen($text) can be given in $length, but
// that's optional.
//
// in text = (string)
// in/out startpos = (int)
// in/out endpos = (int)
// in length = (int) [optional]
//
// returns (int) difference between old and new selection length
//
function AbbcExpandSelection(&$text, &$startpos, &$endpos, $length = -1)
{
	// go backward from the beginning on
	while ($startpos > 0 &&
	       ($text{$startpos - 1} == ' ' ||
	        $text{$startpos - 1} == "\t" ||
	        $text{$startpos - 1} == "\n"))
	{
		$startpos--;
	}

	// go forward from the end on
	if ($length == -1) $length = strlen($text);
	$end_diff = 0;
	while ($endpos < $length &&
	       ($text{$endpos + 1} == ' ' ||
	        $text{$endpos + 1} == "\t" ||
	        $text{$endpos + 1} == "\n"))
	{
		$endpos++;
		$end_diff++;
	}

	return $end_diff;
}

// Almost the same as AbbcExpandSelection(), but used for smilies that may have
// some other characters around them...
//
// in text = (string)
// in/out startpos = (int)
// in/out endpos = (int)
// in wb = (string) set of accepted whitespace characters
// in length = (int) [optional]
//
// returns (int) difference between old and new selection length
//
function AbbcExpandSelectionSmilie(&$text, &$startpos, &$endpos, $wb, $length = -1)
{
	// there are some "\" that are necessary for reg-exps but mislead the strpos function!
	$wb2 = stripslashes($wb);

	// go backward from the beginning on
	while ($startpos > 0 &&
	       (strpos($wb2, $text{$startpos - 1}) !== false ||
	        $text{$startpos - 1} == '>' ||
	        $text{$startpos - 1} == ']'))
	{
		$startpos--;
	}

	// go forward from the end on
	if ($length == -1) $length = strlen($text);
	$end_diff = 0;
	while ($endpos < $length - 1 &&
	       (strpos($wb2, $text{$endpos + 1}) !== false ||
	        $text{$endpos + 1} == '['))
	{
		$endpos++;
		$end_diff++;

		// If we encountered a line break, it will surely suffice for a smiley.
		// Running over that line break may cause other syntax elements to malfunction,
		// like e.g. a simple "----" horizontal line.
		if ($text{$endpos} === "\n" || $text{$endpos} === "\r") break;
	}

	return $end_diff;
}

// Currently unused
// Display a single smilie
//
/*function AbbcDispSmilie($smilietext)
{
	global $ABBC;

	for ($n = 0; $n < $ABBC['SmilieCount']; $n++)
	{
		if (!strncmp($smilietext, $ABBC['Smilies'][$n]['code'], $ABBC['Smilies'][$n]['code_len']))
		{
			// found a smilie -> translate it into an <img>
			return AbbcRegReplaceSmilie('', $n);
		}
	}

	// this doesn't seem to be a known smilie code -> just display it as-is
	return $smilietext;
}
*/

// Check if given tagname exists in the tags array
//
// Takes care of 'nocase' configuration for each tag
//
// in tagname = (string) tag name, like "b" or "quote"
//
// returns (bool)
//
function AbbcValidTagname(&$tagname)
{
	global $ABBC;

	if (array_key_exists($tagname, $ABBC['Tags'])) return true;

	foreach ($ABBC['Tags'] as $key => $value)
	{
		#echo "case-checking for tagname '$tagname' against '$key'<br />";
		if ($ABBC['Tags'][$key]['nocase'] && !strcasecmp($key, $tagname))
		{
			// change tagname in the calling context so that we have the correct key for further access on this tag's data
			$tagname = $key;
			return true;
		}
	}

	return false;
}

// Check if given tagname exists on the tagstack
//
// Takes care of 'nocase' configuration for each tag
//
// in tagname = (string) tag name, like "b" or "quote"
//
// returns (bool)
//
function AbbcOnTagstack($tagname)
{
	global $ABBC;

	if (in_array($tagname, $ABBC['TagStack'])) return true;

	foreach ($ABBC['TagStack'] as $key => $value)
	{
		if ($ABBC['Tags'][$key]['nocase'] && !strcasecmp($key, $tagname))
		{
			return true;
		}
	}

	return false;
}

// Inversion of t2h() function. Convert HTML entities back to plaintext.
//
// in text = (string)
//
// returns (string)
//
function AbbcH2T($text)
{
	// This function is taken from WMS/common.lib/h2t()

	$text = str_replace('&lt;', '<', $text);
	$text = str_replace('&gt;', '>', $text);
	$text = str_replace('&quot;', '"', $text);
	$text = str_replace('&nbsp;', ' ', $text);
	$text = str_replace('&amp;', '&', $text);
	return $text;
}

// Quote things for HTML attributes
//
function abbcq($str)
{
	return t2i(stripslashes($str));
}

// stripslashes() replacement
//
function abbcs($str, $mode = 2)
{
	if ($mode & 1) $str = str_replace('\\\'', '\'', $str);   // for abbcs("$1") use in reg exps
	if ($mode & 2) $str = str_replace('\\"', '"', $str);     // for abbcs('$1') use in reg exps (default)
	return $str;
}

// Return ABBC's CSS definition code
//
function AbbcCss()
{
	global $UNB;
	return '@import url(' . $UNB['LibraryURL'] . 'abbc.css.php);';
}

// Currently unused
// convert user data into another format
// e.g. smilies into bb-tags
// or the other way round (for editing a (converted) stored posting with friendly smilie presentation)
//
/*function AbbcConvert($in, $action)
{
	global $ABBC;

	switch ($action)
	{
		case 1:
			// convert smilies into their bb-code tags for faster processing later

			// backup and change subsets to allow smilie recognition.
			// only activate ABBC_SMILIES, this tells the reg-exp-replace function
			// to make smilie tags instead of <img> html code.
			$prev_subsets = $ABBC['Config']['subsets'];
			$ABBC['Config']['subsets'] = ABBC_SMILIES;
			$out = AbbcProc($in);

			// restore previous subsets
			$ABBC['Config']['subsets'] = $prev_subsets;

			return $out;

		case 2:
			// convert smilie bb-code tags back into their friendly-code for re-editing by the user

			// this feature is not yet implemented
			return "";
	}

	// nothing to do here
	return false;
}
*/

?>