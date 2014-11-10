<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS configuration file
//
// Design name: Modern
// Author:      Yves Goergen
// Last edit:   20070117

// We don't want E_NOTICE output here!
error_reporting(E_ALL & ~E_NOTICE);

header('Content-Type: text/css');
header('Expires: ' . date('r', time() + 86400));   // Cache CSS files for 1 day

$UNB = array();

// Browser detection
require(dirname(dirname(dirname(__FILE__))) . '/clientinfo.lib.php');
$ie = $UNB['Client']['b_class'] == 'ie';
$ie7 = $UNB['Client']['b_class'] == 'ie' && $UNB['Client']['b_ver'] >= '7';
$konq = $UNB['Client']['browser'] == 'konq';
$opera = $UNB['Client']['b_class'] == 'opera';
$gecko = $UNB['Client']['b_class'] == 'gecko';
$mac = $UNB['Client']['os_class'] == 'mac';
$fx15 = $UNB['Client']['browser'] == 'fx' && ($UNB['Client']['b_ver'] == '1.0+' || $UNB['Client']['b_ver'] >= '1.4');
$_ie7 = $ie7;

// Path to image files, relative to CSS files
$ImgPath = '../img/';

// Common font style definitions

// UNB 1.6 defaults:
$UNB['Font']['family'] = 'Verdana, Arial, helvetica, sans-serif';
$UNB['Font']['size'] = '13px';   // 10pt
$UNB['Font']['smallsize'] = '11px';   // 8pt, 0.85em

// Include user design definitions
@include(dirname(__FILE__) . '/cssconfig.user.php');
@include(dirname(dirname(__FILE__)) . '/cssconfig.user.php');
?>