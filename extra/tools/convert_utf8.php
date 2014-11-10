<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// convert_utf8.php
// Convert ISO-8859-1 encoded database information to UTF-8
// HTML unicode characters' presentation and the Euro symbol (0x80) are also converted

// Disable this file in productive environment!
if (file_exists('lock.conf')) die('The board setup is locked. Remove the file lock.conf to unlock it.');

$INSTALLING = true;
if (!defined('PUBLIC_LIB')) require_once('unb_lib/public.lib.php');
$ME = 'convert_utf8.php';

require_once($libpath . 'common.lib.php');
require($libpath . 'lang/' . $LANG . '_more.php');
UnbReadACL();

function code2utf($num)
{
	if ($num < 128) return chr($num);
	if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
	if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
	return '';
}

function encode($str)
{
	// convert euro sign (iso-8859-15: 0x80 -> unicode: 0x20AC)

	return
		preg_replace(
			'/&#(\\d+);/e',
			'code2utf($1)',
			utf8_encode(
				str_replace(
					"\x80",
					'&#8364;',
					$str)));
}


UnbBeginHTML('Convert data to UTF-8');

@set_time_limit(0);

echo '<div class="p">Convert data to UTF-8:<br />';

echo 'Forums...<br />';
$x = $db->FastQueryArray('Forums');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['Name'] = encode($a['Name']);
	$a['Description'] = encode($a['Description']);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo 'Messages...<br />';
$x = $db->FastQueryArray('Messages');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['Subject'] = encode($a['Subject']);
	$a['Msg'] = encode($a['Msg']);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo 'PollVotes...<br />';
$x = $db->FastQueryArray('PollVotes');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['Title'] = encode($a['Title']);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo 'Posts...<br />';
$x = $db->FastQueryArray('Posts');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['UserName'] = encode($a['UserName']);
	$a['Subject'] = encode($a['Subject']);
	$a['Msg'] = encode($a['Msg']);
	$a['AttachFileName'] = encode($a['AttachFileName']);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo 'Threads...<br />';
$x = $db->FastQueryArray('Threads');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['Subject'] = encode($a['Subject']);
	$a['Desc'] = encode($a['Desc']);
	$a['UserName'] = encode($a['UserName']);
	$a['Question'] = encode($a['Question']);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo 'Users...<br />';
$x = $db->FastQueryArray('Users');
if ($x) foreach ($x as $a)
{
	foreach ($a as $key => $value) if (is_numeric($key)) unset($a[$key]);
	$a['Name'] = encode($a['Name']);
	$a['ICQ'] = encode($a['ICQ']);
	$a['AIM'] = encode($a['AIM']);
	$a['YIM'] = encode($a['YIM']);
	$a['MSN'] = encode($a['MSN']);
	$a['Signature'] = encode($a['Signature']);
	$a['About'] = encode($a['About']);
	$a['Title'] = encode($a['Title']);
	$a['Location'] = encode($a['Location']);
	for ($n = 1; $n <= $extra_count; $n++)
		$a['Extra' . $n] = encode($a['Extra' . $n]);
	$db->ChangeRecord($a, "ID=$a[ID]");
}

echo '<b>' . $UNB_T['inst_done'] . '</b></div>';

touch('lock.conf');
echo '<div class="p">' . $UNB_T['inst_lock'] . '</div>';

echo '<div class="p"><a href="' . $UNB['Module']['main'] . sid(2) . '">' . $UNB_T['inst_go_overview'] . '</a></div>';
UnbEndHTML();
?>