<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// mime-create.lib.php
// MIME encoding functions
// Widely taken from the Website Management System WMS, http://software.unclassified.de/wms

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

require_once(dirname(__FILE__) . '/mime.lib.php');

// Handles a MIME-encoded e-mail
//
class mime_create_class
{

// -------------------- Internal varialbes --------------------

var $smtp_from = '';
var $smtp_to = array();

var $hdr_from = '';
var $hdr_to = array();
var $hdr_cc = array();
var $hdr_bcc = array();   # do we need this one?
var $bcc_self = false;
var $hdr_reply = false;

var $hdr_date = 0;
var $hdr_subject = '';
var $text_present = false;
var $html_present = false;

var $headers = array();   # one element for each header key+value. \n indicates line break, spaces are autom. inserted when needed

# following are arrays with
# - ctype: Content-Type
# - cenc: Content-Transfer-Encoding
# - cdisp: Content-Disposition
# - fname: cdisp.filename parameter
# - cid: Content-ID (if used anytime)
# - data: actual data for this block
#
var $body_msg = array();
var $body_msg_text = array();
var $body_msg_html = array();
var $body_attach = array();   # this one is an array^2 because multiple attachments are allowed

var $body = '';   # here our entire e-mail is assembled into within the build() function

// Set a header value in the original (passed) header array
//
function set_header($key, $value)
{
	$keylen = strlen($key);

	$found = 0;
	for ($n = 0; $n < sizeof($this->headers); $n++)
	{
		if (!strcasecmp(substr($this->headers[$n], 0, $keylen + 1), $key . ':'))
		{
			// key found -> update/remove value
			if (strlen($value))
			{
				$this->headers[$n] = $key . ': ' . $value;
			}
			else
			{
				$this->headers = array_merge(array_slice($this->headers, 0, $n - 1), array_slice($this->headers, $n + 1));
			}
			$found++;
		}
	}

	if (!$found && strlen($value))
	{
		array_push($this->headers, $key . ': ' . $value);
	}
}

// Set sender of this e-mail
//
function set_from($addr, $name = '', $charset = 'ISO-8859-1')
{
	$this->smtp_from = $addr;
	if ($name != '')
		$this->hdr_from = MimeEncodeWord($name, true, false, $charset) . ' <' . $addr . '>';
	else
		$this->hdr_from = $addr;
	return true;
}

// Set reply address of this e-mail
//
function set_reply($addr)
{
	$this->hdr_reply = $addr;
	return true;
}

// Add a receipient to the e-mail
//
function add_to($addr, $name = '')
{
	# check for this address
	#
	foreach ($this->smtp_to as $to)
	{
		if (!strcasecmp($to, $addr)) return false;   # because we already have this address
	}

	array_push($this->smtp_to, $addr);
	array_push($this->hdr_to, array('addr' => $addr, 'name' => $name));
	return true;
}

// Add a receipient to the e-mail
//
function add_cc($addr, $name = '')
{
	# check for this address
	#
	foreach ($this->smtp_to as $to)
	{
		if (!strcasecmp($to, $addr)) return false;   # because we already have this address
	}

	array_push($this->smtp_to, $addr);
	array_push($this->hdr_cc, array('addr' => $addr, 'name' => $name));
	return true;
}

// Add a receipient to the e-mail
//
function add_bcc($addr, $name = '')
{
	# check for this address
	#
	foreach ($this->smtp_to as $to)
	{
		if (!strcasecmp($to, $addr)) return false;   # because we already have this address
	}

	array_push($this->smtp_to, $addr);
	array_push($this->hdr_bcc, array('addr' => $addr, 'name' => $name));
	return true;
}

function reset_rcpts()
{
	$this->hdr_to = array();
	$this->hdr_cc = array();
	$this->hdr_bcc = array();
	$this->smtp_to = array();
}

// Set subject of this e-mail
//
function set_subject($subject, $charset = 'ISO-8859-1')
{
	$this->hdr_subject = MimeEncodeWord($subject, false, false, $charset);
	return true;
}

// Set the plain text body of this e-mail
//
function set_msg_text($text, $charset = 'ISO-8859-1')
{
	$this->body_msg_text['ctype'] = "text/plain; charset=\"$charset\"";
	$this->body_msg_text['cenc'] = '';   # no encoding yet
	$this->body_msg_text['data'] = $text;
	$this->text_present = true;
	return true;
}

// Set the html body of this e-mail
//
function set_msg_html($html, $charset = 'ISO-8859-1')
{
	$this->body_msg_html['ctype'] = "text/html; charset=\"$charset\"";
	$this->body_msg_html['cenc'] = '';   # no encoding yet
	$this->body_msg_html['data'] = $html;
	$this->html_present = true;
	return true;
}

// Add an attachment to the e-mail
// $file is the name of the file to be added, $ctype is its content-type
//
// UNB export: $ctype must be set... it won't be recognized (set to unknown)
//
function add_attach($file, $ctype = '')
{
	# TODO: find most efficient encoding. auto-select base64 for certain filesize or above to save memory and time

	$fname = basename($file);
	$cenc = 'base64';

	if (substr($file, 0, 1) == '/') $file = $_SERVER['DOCUMENT_ROOT'] . $file;
	$f = fopen($file, 'r');
	if (!$f) return false;   # couldn't open this file
	$data = fread($f, filesize($file));
	fclose($f);
	$data = chunk_split(base64_encode($data));

	# find content-type for this attachment
	#if (!strcasecmp(substr($fname, strlen($fname) - 5, 5), '.html') $ctype = 'text/html';
	$ext = array_pop(explode('.', $fname));
	//if ($ctype == '') $ctype = UnbGetMimetype($ext);   // UNB modification: we have no filetype table here
	if ($ctype == '') $ctype = 'unknown';

	# make valid filename
	$fname = trim($fname);
	$fname = preg_replace('/"|\*|\/|:|<|>|\?|\\\|\|/', '_', $fname);

	array_push($this->body_attach, array('ctype' => $ctype, 'cenc' => $cenc, 'cdisp' => 'attachment', 'fname' => $fname, 'data' => $data));

	return true;
}

// Build the message part of the mail body
//
function build_msg()
{
	# prepare text and html data as needed
	# and remove any trailing newline
	#
	if ($this->text_present)
	{
		$enc = MimeEncodeIfRequired($this->body_msg_text['data']);
		$this->body_msg_text['data'] = rtrim($enc['data']);
		switch ($enc['cenc'])
		{
			case 'Q': $this->body_msg_text['cenc'] = 'quoted-printable'; break;
			case 'B': $this->body_msg_text['cenc'] = 'base64'; break;
			default: $this->body_msg_text['cenc'] = '7bit';
		}
	}
	if ($this->html_present)
	{
		$enc = MimeEncodeIfRequired($this->body_msg_html['data']);
		$this->body_msg_html['data'] = rtrim($enc['data']);
		switch ($enc['cenc'])
		{
			case 'Q': $this->body_msg_html['cenc'] = 'quoted-printable'; break;
			case 'B': $this->body_msg_html['cenc'] = 'base64'; break;
			default: $this->body_msg_html['cenc'] = '7bit';
		}
	}

	if ($this->text_present && $this->html_present)
	{
		$this->body_msg['ctype'] = "multipart/alternative;\r\n\tboundary=\"----=_NextPart_body_message\"";
		$this->body_msg['cenc'] = '';   # no encoding here
		$this->body_msg['multipart'] = true;

		$this->body_msg['data']  = "------=_NextPart_body_message\r\n";
		$this->body_msg['data'] .= $this->get_headers_as_text($this->body_msg_text);
		$this->body_msg['data'] .= "\r\n";
		$this->body_msg['data'] .= $this->body_msg_text['data'] . "\r\n\r\n";

		$this->body_msg['data'] .= "------=_NextPart_body_message\r\n";
		$this->body_msg['data'] .= $this->get_headers_as_text($this->body_msg_html);
		$this->body_msg['data'] .= "\r\n";
		$this->body_msg['data'] .= $this->body_msg_html['data'] . "\r\n\r\n";

		$this->body_msg['data'] .= "------=_NextPart_body_message--\r\n\r\n";
	}
	elseif ($this->text_present)
	{
		$this->body_msg['ctype'] = $this->body_msg_text['ctype'];
		$this->body_msg['cenc'] = $this->body_msg_text['cenc'];
		$this->body_msg['multipart'] = false;
		$this->body_msg['data'] = $this->body_msg_text['data'];
	}
	elseif ($this->html_present)
	{
		$this->body_msg['ctype'] = $this->body_msg_html['ctype'];
		$this->body_msg['cenc'] = $this->body_msg_html['cenc'];
		$this->body_msg['multipart'] = false;
		$this->body_msg['data'] = $this->body_msg_html['data'];
	}
	else
	{
		# no content specified up to now, maybe some attachments will follow
		$this->body_msg['ctype'] = '';
		$this->body_msg['cenc'] = '';
		$this->body_msg['data'] = '';
	}

}

// Build the attachments part of the mail body
//
function build_attach()
{
	for ($n = 0; $n < sizeof($this->body_attach); $n++)
	{
		if (($this->body_attach[$n]['fname'] != '') && ($this->body_attach[$n]['cdisp'] != ''))
		{
			$this->body_attach[$n]['cdisp'] .= '; filename="' . $this->body_attach[$n]['fname'] . '"';
		}
	}
}

// Build the entire e-mail
// If you already have a mail source code, you can specify it here and
// only the from/to.../subject/date headers will be added.
//
function build($emlsource = '')
{
	$this->set_header('From', $this->hdr_from);

	if ($this->hdr_reply) $this->set_header('Reply-to', $this->hdr_reply);

	# build To: header line
	#
	$to_line = '';
	foreach ($this->hdr_to as $to)
	{
		if ($to_line != '') $to_line .= ",\r\n\t";

		if ($to['name'] != '')
		{
			$to_line .= MimeEncodeWord($to['name'], true) . ' ';
		}

		$to_line .= '<' . $to['addr'] . '>';
	}
	$this->set_header('To', $to_line);

	# build CC: header line
	#
	$cc_line = '';
	foreach ($this->hdr_cc as $cc)
	{
		if ($cc_line != '') $cc_line .= ",\r\n\t";

		if ($cc['name'] != '')
		{
			$cc_line .= MimeEncodeWord($cc['name'], true) . ' ';
		}

		$cc_line .= '<' . $cc['addr'] . '>';
	}
	$this->set_header('CC', $cc_line);

	$this->hdr_date = time();
	$this->set_header('Date', date('r', $this->hdr_date));

	$this->set_header('Subject', $this->hdr_subject);
	// TODO: maybe we should limit line length to 72 or something
	//       see http://de2.php.net/manual/en/function.mail.php#27997 function encode()

	if ($emlsource != '')
	{
		# we have a source code

		$this->body = join("\r\n", $this->headers) . "\r\n" . $emlsource;
		return true;
	}
	elseif ($this->body_msg['ctype'] != '' && sizeof($this->body_attach))
	{
		# we have a message and at least one attachment

		$this->set_header('Content-Type', "multipart/mixed;\r\n\tboundary=\"----=_NextPart_body_all\"");

		$this->body  = "This is a multi-part message in MIME format.\r\n\r\n";

		# add message part(s)
		#
		$this->body .= "------=_NextPart_body_all\r\n";
		$this->body .= 'Content-Type: ' . $this->body_msg['ctype'] . "\r\n";
		if ($this->body_msg['cenc'] != '')
			$this->body .= 'Content-Transfer-Encoding: ' . $this->body_msg['cenc'] . "\r\n";
		$this->body .= "\r\n";
		$this->body .= $this->body_msg['data'];
		$this->body .= "\r\n\r\n";

		# add attachment(s)
		#
		foreach ($this->body_attach as $attach)
		{
			$this->body .= "------=_NextPart_body_all\r\n";
			$this->body .= $this->get_headers_as_text($attach);
			$this->body .= "\r\n";
			$this->body .= $attach['data'];
			$this->body .= "\r\n\r\n";

			# TODO: if we consume too much memory, we can free each $attach here (but then can't build again...)
		}

		$this->body .= "------=_NextPart_body_all--\r\n";
	}
	elseif ($this->body_msg['ctype'] != '' && !sizeof($this->body_attach))
	{
		# we have a message and NO attachment

		$this->set_header('Content-Type', $this->body_msg['ctype']);
		if ($this->body_msg['cenc'] != '')
			$this->set_header('Content-Transfer-Encoding', $this->body_msg['cenc']);

		$this->body  = '';

		if ($this->body_msg['multipart'])
			$this->body .= "This is a multi-part message in MIME format.\r\n\r\n";

		# add message data
		#
		$this->body .= $this->body_msg['data'];
		$this->body .= "\r\n\r\n";
	}
	elseif ($this->body_msg['ctype'] == '' && sizeof($this->body_attach))
	{
		# we have NO message and at least one attachment

		if (sizeof($this->body_attach) == 1)
		{
			$attach = $this->body_attach[0];

			if ($attach['ctype'] != '')
				set_header('Content-Type', $attach['ctype']);
			if ($attach['cenc'] != '')
				set_header('Content-Transfer-Encoding', $attach['cenc']);
			if ($attach['cdisp'] != '')
				set_header('Content-Disposition', $attach['cdisp']);
			if ($attach['cid'] != '')
				set_header('Content-ID', $attach['cid']);

			$this->body  = $attach['data'];
			$this->body .= "\r\n\r\n";
		}
		elseif (sizeof($this->body_attach) > 1)
		{
			$this->set_header('Content-Type', "multipart/mixed;\r\n\tboundary=\"----=_NextPart_body_all\"");

			$this->body  = "This is a multi-part message in MIME format.\r\n\r\n";

			# add attachment(s)
			#
			foreach ($this->body_attach as $attach)
			{
				$this->body .= "------=_NextPart_body_all\r\n";
				$this->body .= $this->get_headers_as_text($attach);
				$this->body .= "\r\n";
				$this->body .= $attach['data'];
				$this->body .= "\r\n\r\n";

				# TODO: if we consume too much memory, we can free each $attach here (but then can't build again...)
			}

			$this->body .= "------=_NextPart_body_all--\r\n";
		}
	}
	else
	{
		# no content specified up to now, maybe some attachments will follow
	}

	$this->set_header('MIME-Version', '1.0');
	$this->set_header('X-Mailer', 'WMS-based (software.unclassified.de/wms)');

	# now it's time to put it all together...
	#
	$this->body = join("\r\n", $this->headers) . "\r\n\r\n" . $this->body;

	return true;
}

// Convert ctype, cenc... array elements into readable text-header form
//
function get_headers_as_text($block)
{
	if (!is_array($block)) return false;

	$out = '';

	if ($block['ctype'] != '')
		$out .= 'Content-Type: ' . $block['ctype'] . "\r\n";
	if ($block['cenc'] != '')
		$out .= 'Content-Transfer-Encoding: ' . $block['cenc'] . "\r\n";
	if ($block['cdisp'] != '')
		$out .= 'Content-Disposition: ' . $block['cdisp'] . "\r\n";
	if ($block['cid'] != '')
		$out .= 'Content-ID: ' . $block['cid'] . "\r\n";

	return $out;
}

}  // class mime_create_class

?>