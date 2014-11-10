<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// mime.lib.php
// MIME decoding functions
// Widely taken from the Website Management System WMS, http://software.unclassified.de/wms

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Complement to PHP's quoted_printable_decode()
//
// NOTE: Quoted-printable encoding as done here is NOT BINARY SAFE! use base64_encode() instead.
//
// in str = (string) data to encode
// in level = (int) supports multiple levels of encoding characters:
//   1: \x01-\x08 \x0A-\x1F \x3D \x7F-\xFF
//   2: \x01-\x08 \x0A-\x1F SPC->_ \x21-\x24 \x3C-\x40 \x5B-\x60 \x7B-\xFF
//   3: \x01-\x08 \x0A-\x1F SPC->_ \x21-\x2F \x3A-\x40 \x5B-\x60 \x7B-\xFF
// in splitlines = (bool) split long lines at 76 characters (default)
//
// 1) performs a simple encoding suitable for most text body applications
// 2) is needed for 'encoded-word's, header values or some more special applications
// 3) only lets digits (0...9) and letters (A...Z, a...z) in plain text
//
function my_quoted_printable_encode($str, $level = 1, $splitlines = true)
{
	// strip CR
	$str = preg_replace("~[\r]*~", "", $str);

	// encode characters according to selected level
	switch ($level)
	{
		case 3:
			$str = preg_replace("~([\x01-\x08\x0B-\x0C\x0E-\x1F\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\xFF])~e", "sprintf('=%02X', ord('\\1'))", $str);
			$str = preg_replace("~[\x20]~", "_", $str);
			break;
		case 2:
			$str = preg_replace("~([\x01-\x08\x0B-\x0C\x0E-\x1F\x21-\x24\x3C-\x40\x5B-\x60\x7B-\xFF])~e", "sprintf('=%02X', ord('\\1'))", $str);
			$str = preg_replace("~[\x20]~", "_", $str);
			break;
		default:
			$str = preg_replace("~([\x01-\x08\x0B-\x0C\x0E-\x1F\x3D\x7F-\xFF])~e", "sprintf('=%02X', ord('\\1'))", $str);
	}

	// encode blanks and tabs at the end of lines
	$str = preg_replace("~([\x09\x20])\n~e", "sprintf('=%02X\n', ord('\\1'))", $str);

	$parts = explode("\n", $str);

	if ($splitlines)
	{
		// split string
		$lines = count($parts);
		for ($i = 0; $i < $lines; $i++)
		{
			// if longer than 76, add a line break
			if (strlen($parts[$i]) > 76)
				$parts[$i] = preg_replace("~((.){73,76}((=[0-9A-Fa-f]{2})|([^=]{0,3})))~", "\\1=\n", $parts[$i]);
		}
	}

	return (implode("\r\n", $parts));
}

// Add PHP's decode function by optional "_"-to-space conversion
//
function quoted_printable_decode2($str, $spaceconv = true)
{
	if ($spaceconv) $str = str_replace("_", " ", $str);
	$str = quoted_printable_decode($str);
	return $str;
}

// (Recursively) remove " chars at beginning and end if present
//
function unquote($str, $rec = false)
{
	if (substr($str, 0, 1) == "\"" && substr($str, strlen($str) - 1, 1) == "\"")
	{
		$str = substr($str, 1, strlen($str) - 2);
		if ($rec) $str = unquote($str, $rec);
	}
	return $str;
}

// Find the value of a header line like "Date: <...>"
//
// respects rfc822 "folded" (multiline) notation (rfc822/3.1.1)
// automatically recognizes mime 'encoded-word's and decodes them
//
// pass it the headers as array split on \n and the key (i.e. "Date", w/o ":") you want the value to
//
// returns (string) requested value | false on error
//
function MimeGetHeaderValue($hdr, $key)
{
	$len = strlen($key);
	$value = false;

	foreach($hdr as $line)
	{
		if (!strcasecmp(substr($line, 0, $len + 1), $key . ":"))
		{
			# found key -> read value
			#
			$value = substr($line, $len + 2);
		}
		elseif ($value && (substr($line, 0, 1) == " " || substr($line, 0, 1) == "\t"))
		{
			# append this to value
			#
			$value .= $line;
		}
		else
		{
			# found another header line -> return if we have our value
			#
			if ($value) break;
		}
	}
	if ($value)
	{
		#return $value;

		#$words = explode(" ", str_replace("\t", " ", $value));
		$words = array();
		$data = "";
		for ($n = 0, $instr = false; $n < strlen($value); $n++)
		{
			if (($value[$n] == "\"") && ($value[$n - 1] != "\\")) $instr = !$instr;
			if ((($value[$n] == " ") || ($value[$n] == "\t")) && !$instr)
			{
				array_push($words, $data);
				$data = "";
			}
			else
				$data .= $value[$n];
		}
		array_push($words, $data);

		$dec_words = array();
		foreach ($words as $word)
		{
			array_push($dec_words, MimeDecodeWord($word));
		}
		return trim(join(" ", $dec_words));
	}

	# key not found
	#
	return false;
}

// Find a sub-value (parameter) inside a header's value, i.e.
// Content-Type: multipart/mixed; boundary=<...>
//
// pass it the header value as returned by MimeGetHeaderValue and the parameter name (i.e. "boundary") you want the value to
// returns string value or false
//
// giving a $name of "" returns the first element of the ";"-splitted array,
// i.e. the Content-Type definition itself w/o following parameters
//
// setting $unq = true automatically unquotes parameter value. this is not done recursively
//
function MimeGetHeaderParam($value, $name, $unq = false)
{
	# split entire header value on ";" and trim parts
	#
	$parts = explode(";", $value);
	for ($n = 0; $n < sizeof($parts); $n++)
	{
		$parts[$n] = trim($parts[$n]);
	}

	# user wants 1st element?
	#
	if ($name == "")
	{
		$value = trim($parts[0]);
		if ($unq) $value = unquote($value);
		return $value;
	}

	# search parameter name from 2nd element on
	#
	for ($n = 1; $n < sizeof($parts); $n++)
	{
		if (!strcasecmp(substr($parts[$n], 0, strlen($name) + 1), $name . "="))
		{
			$value = trim(substr($parts[$n], strlen($name) + 1));
			if ($unq) $value = unquote($value);
			return $value;
		}
	}

	# parameter not found
	#
	return false;
}

// Decode a MIME "encoded word" like in "Subject: =?...?=" (RFC 2047)
//
// pass it the entire encoded string (from "=?" to "?=" inclusive)
// returns decoded value
//
function MimeDecodeWord($enc)
{
	# cut off spaces, \n etc. since we can't handle it with our reg exp's
	$enc = trim($enc);

	if (preg_match("/^=\?([^\?]+)\?([^\?])\?([^\?]+)\?=(\s+=\?.*)?$/", $enc, $m))
	{
		$charset = $m[1];
		$encoding = $m[2];
		$data = $m[3];
		$more = $m[4];

		# decode data up to here
		#
		if (!strcasecmp($encoding, "b"))
		{
			$dec = base64_decode($data);
		}
		elseif (!strcasecmp($encoding, "q"))
		{
			$dec = quoted_printable_decode2($data);
		}
		else
		{
			$dec = $data;
		}

		# more data blocks present?
		#
		while ($more)
		{
			preg_match("/=\?([^\?]+)\?([^\?])\?([^\?]+)\?=(\s+=\?.*)?/", $more, $m);
			$charset = $m[1];
			$encoding = $m[2];
			$data = $m[3];
			$more = $m[4];

			# continue decoding data
			#
			if (!strcasecmp($encoding, "b"))
			{
				$dec .= base64_decode($data);
			}
			elseif (!strcasecmp($encoding, "q"))
			{
				$dec .= quoted_printable_decode2($data);
			}
			else
			{
				$dec .= $data;
			}
		}

		return $dec;
	}
	else
	{
		# string is not mime encoded -> return it as-is
		#
		return $enc;
	}
}

// Generate a MIME 'encoded word' for use in mail headers (RFC 2047)
//
// checks which encoding (quoted-printable/base64) is more efficient in this case and
// returns either the plain string or an 'encoded word'
//
// since my_quoted_printable_encode is used, this function is NOT BINARY SAFE!
// set ($force_b64 = true) to reliably encode binary data here
//
// ($quote_plain = true) encloses plain (unchanged) text in "" chars
//
function MimeEncodeWord($str, $quote_plain = false, $force_b64 = false, $charset = 'ISO-8859-1')
{
	# scan for characters that may not be allowed on some systems and though must be encoded
	#
	if (preg_match("/[\x01-\x1F\x22\x27\x3C-\x3F\x7F-\xFF]/i", $str))
	{
		# encode string
		#
		$enc_qp = my_quoted_printable_encode($str, 2, false);
		$enc_b64 = base64_encode($str);

		# find smaller encoding but prefer qp up to +20%
		#
		if (strlen($enc_qp) > strlen($enc_b64) * 1.2)
		{
			$enc_data = $enc_b64;
			$enc_type = 'B';
		}
		else
		{
			$enc_data = $enc_qp;
			$enc_type = 'Q';
		}

		# build entire 'encoded word'
		#
		$enc_word = '=?' . $charset . '?' . $enc_type . '?' . $enc_data . '?=';
	}
	else
	{
		if ($quote_plain)
		{
			$enc_word = '"' . $str . '"';
		}
		else
		{
			$enc_word = $str;
		}
	}

	return $enc_word;
}

// Similar to MimeEncodeWord()
//
// checks whether any encoding is needed and which one (quoted-printable/base64) is more efficient in this case and
// returns either the plain string or encoded data as array('cenc' => "B"|"Q"|"", 'data')
//
// since my_quoted_printable_encode is used, this function is NOT BINARY SAFE!
// set ($force_b64 = true) to reliably encode binary data here
//
// any multi-line options are enabled in this function
//
function MimeEncodeIfRequired($str, $force_b64 = false)
{
	$out = array();

	# scan for characters that may not be allowed on some systems and though must be encoded
	#
	if (preg_match("/[\x01-\x1F\x22\x27\x3C-\x3F\x7F-\xFF]/i", $str))
	{
		# encode string
		#
		$enc_qp = my_quoted_printable_encode($str);
		$enc_b64 = chunk_split(base64_encode($str));

		# find smaller encoding but prefer qp up to +20%
		#
		if (strlen($enc_qp) > strlen($enc_b64) * 1.2)
		{
			$out['cenc'] = "B";
			$out['data'] = $enc_b64;
		}
		else
		{
			$out['cenc'] = "Q";
			$out['data'] = $enc_qp;
		}
	}
	else
	{
		$out['cenc'] = "";
		$out['data'] = $str;
	}

	return $out;
}

?>