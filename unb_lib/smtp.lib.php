<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// smtp.lib.php
// SMTP connection class
// Widely taken from the Website Management System WMS, http://software.unclassified.de/wms

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// initially from: http://www.phpbuilder.com/snippet/detail.php?type=snippet&id=35 (version: 1.3.2 (2001-09-15))
// completely re-designed to remove all mime and error-code crap that i've done much better myself i believe
// added SMTP AUTH support

// Handles an SMTP server connection
//
class smtp_class
{

var $error = '';

var $server = "";
var $sock = false;
var $readlength = 1024;

var $from = "";
var $to = array();

// Connect to SMTP server
//
function connect($server, $user = '', $pass = '', $timeout = 10)
{
	if (!strlen($server))
	{
		$this->error = 'no server specified';
		return false;
	}

	$this->server = $server;

	if (strpos($server, ':') !== false)
	{
		list($server, $port) = explode(':', $server);
	}
	else
	{
		$port = 25;
	}

	if (!$this->sock = fsockopen($server, $port, $errno, $errstr, $timeout))
	{
		$this->error = 'couldn\'t open socket';
		return false;
	}
	$a = $this->get_feedback_line();
	if (!$this->is_ok($a))
	{
		$this->error = 'no positive feedback received';
		return false;
	}

	// first, say hello
	$line = "EHLO " . $_SERVER['SERVER_NAME'];
	fputs($this->sock, "$line\r\n");
	$a = $this->get_feedback_line();
	if (!$this->is_ok($a))
	{
		$this->error = $line . "\n" . $a;
		return false;
	}

	// find out capabilities for authentication
	$auth_md5 = preg_match('/AUTH[ =].*?CRAM-MD5/i', $a);
	$auth_login = preg_match('/AUTH[ =].*?LOGIN/i', $a);

	if ($user != '')
	{
		if (!$auth_login && !$auth_md5)
		{
			$this->error = "No authentication method (LOGIN or CRAM-MD5).\n";
			return false;
			// TODO: try them anyway... only return false, if they really don't work
		}

		// login
		if ($auth_md5)
		{
			$line = "AUTH CRAM-MD5";
			fputs($this->sock, "$line\r\n");
			$a = $this->get_feedback_line();
			if (substr($a, 0, 3) != '334')
			{
				$this->error = $line . "\n" . $a;
				return false;
			}

			$a = trim(substr($a, 4));   // cut away response code
			$a = base64_decode($a);   // decode challenge
			$a = $this->hmac($pass, $a);   // generate keyed MD5 hash over challenge
			$code = $user . ' ' . $a;   // assemble username and digest
			$code = base64_encode($code);

			fputs($this->sock, $code . "\r\n");
			$a = $this->get_feedback_line();
			if (substr($a, 0, 3) != '235')
			{
				$this->error = $a;
				return false;
			}
		}

		else if ($auth_login)
		{
			$line = "AUTH LOGIN";
			fputs($this->sock, "$line\r\n");
			$a = $this->get_feedback_line();
			if (trim($a) != '334 VXNlcm5hbWU6')   // base64_encode('Username:')
			{
				$this->error = $line . "\n" . $a;
				return false;
			}
			fputs($this->sock, base64_encode($user) . "\r\n");
			$a = $this->get_feedback_line();
			if (trim($a) != '334 UGFzc3dvcmQ6')   // base64_encode('Password:')
			{
				$this->error = $a;
				return false;
			}
			fputs($this->sock, base64_encode($pass) . "\r\n");
			$a = $this->get_feedback_line();
			if (substr($a, 0, 3) != '235')
			{
				$this->error = $a;
				return false;
			}
		}
	}

	$this->error = '';
	return true;
}

// Set Mail From: value
//
function set_from($from)
{
	if (!strlen($from)) return false;
	if (strlen($from) >= 128) return false;
	$this->from = $from;
	return true;
}

// Set Rcpt To: value
//
function add_to($to)
{
	if (!strlen($to)) return false;
	if (strlen($to) >= 129) return false;
	$this->to[] = $to;
	return true;
}

// Send out the e-mail data
//
function sendmail($data)
{
	if (!$this->sock) return false;
	if (!strlen($this->from)) return false;
	if (!sizeof($this->to)) return false;
	if (!strlen($data)) return false;

	// Convert "bare LF" into CR LF (qmail needs this)
	$data = preg_replace('_(?<!\r)\n_', "\r\n", $data);
	// process $data to mask "CR.CR" codes
	$data = str_replace("\r\n.", "\r\n..", $data);

	// demo mode...
	//
	#echo "<div class="p">" . join("<br />", $this->to) . "</div>";
	#echo "<div class="p"><pre>" . htmlspecialchars($data) . "</pre></div>";
	#return true;

	$head[] = "MAIL FROM:<" . $this->from . ">";
	foreach ($this->to as $value)
	{
		$head[] = "RCPT TO:<$value>";
	}
	$head[] = 'DATA';

	foreach ($head as $line)
	{
		fputs($this->sock, $line . "\r\n");
		$a = $this->get_feedback_line();
		if (!$this->is_ok($a))
		{
			$this->error = $line . "\n" . $a;
			return false;
		}
	}

	fputs($this->sock, "$data\r\n.\r\n");
	$a = $this->get_feedback_line();
	if (!$this->is_ok($a))
	{
		$this->error = "DATA\n" . $a;
		return false;
	}
	$this->reset_data();
	return true;
}

// Reset internal object state
//
function reset_data()
{
	$this->error = '';
	$this->from = '';
	$this->to = array();
	return true;
}

// Wait for an answer from the server
//
// returns (bool) positive answer, negative otherwise
//
function get_feedback()
{
	if (!$response = fgets($this->sock, $this->readlength)) return false;
	return $this->is_ok($response);
}

// Read server answer
//
// returns (string) reply data. Multi-line replies are merged
//
function get_feedback_line()
{
	$allresp = '';
	do
	{
		$response = fgets($this->sock, $this->readlength);
		if (!$response) return false;
		$allresp .= $response;
	}
	while ($response{3} == '-');
	return $allresp;
}

/*function get_feedback_line()
{
	if (!$response = fgets($this->sock, $this->readlength)) return false;
	return $response;
}*/

// Checks a reply code
//
// in input = (int) SMTP server reply code
//
// returns (bool) code means a positive answer
//
function is_ok($input)
{
	// extract the return code from the SMTP server, and make sure it's a positive reply

	if (!ereg("((^[0-9])([0-9]*))", $input, $regs)) return false;

	switch ($regs[1])
	{
		case '220':
		case '221':
		case '235':
		case '250':
		case '251':
		case '334':
		case '354': return true; break;
		default:    return false;
	}
}

// Say goodbye and close SMTP server connection
//
function end()
{
	if (!$this->sock) return false;

	fputs($this->sock, "QUIT\r\n");

	if ($this->get_feedback())
	{
		fclose($this->sock);
		return true;
	}
	return false;
}

// RFC 2104 HMAC implementation for php.
// Creates an md5 HMAC.
// Eliminates the need to install mhash to compute a HMAC
// Hacked by Lance Rushing
//
// This code was contributed to the PHP manual for mhash() on 28nov2002
// No further parameter information given.
//
function hmac($key, $data)
{
	$b = 64;   // byte length for md5
	if (strlen($key) > $b)
	{
		$key = pack("H*",md5($key));
	}
	$key  = str_pad($key, $b, chr(0x00));
	$ipad = str_pad('', $b, chr(0x36));
	$opad = str_pad('', $b, chr(0x5c));
	$k_ipad = $key ^ $ipad;
	$k_opad = $key ^ $opad;

	return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
}

}  # class smtp_class

?>
