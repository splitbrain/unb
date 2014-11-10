<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// config.php
// Smilies definition file
//
// This file is included from the ABBC configuration,
// before abbc.user.php is processed.

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Following information is necessary for a smiley:
//   code    the smiley's code
//   img     image file to be displayed
//   nocase  case-insensitive code [true|false]
//   align   how to align the smiley <img/> [top|middle|bottom|(empty)]

// Note: For maximum performance with smileys, you should sort the smileys
//       in order of usage (Define the most used smilie first).

// Note: The definition order in this array is the same as the smilies
//       will appear in the post editor.


// Changes in smilies-pack by smile@frozen.pl December 19, 2004

$ABBC['Smilies'] = array(

array(
'code'   => ':-)',
'img'    => 'smile.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-D',
'img'    => 'bigsmile.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':laugh:',
'img'    => 'hihi.gif',
'width'  => 21,
'height' => 16,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ';-)',
'img'    => 'ok.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-p',
'img'    => 'pff.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':tongue:',
'img'    => 'tongue.gif',
'width'  => 24,
'height' => 19,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-S',
'img'    => 'disgusted.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-(',
'img'    => 'sorrow.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),


array(
'code'   => ':angry:',
'img'    => 'angry.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':\'(',
'img'    => 'cry.gif',
'width'  => 20,
'height' => 14,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':cool:',
'img'    => 'cool.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => '8-(',
'img'    => 'shocked.gif',
'width'  => 22,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':lick:',
'img'    => 'lick.gif',
'width'  => 23,
'height' => 18,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':dontcare:',
'img'    => 'dontcare.gif',
'width'  => 22,
'height' => 18,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':fight:',
'img'    => 'fight.gif',
'width'  => 21,
'height' => 19,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':delight:',
'img'    => 'delight.gif',
'width'  => 17,
'height' => 19,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':cu:',
'img'    => 'cu.gif',
'width'  => 26,
'height' => 19,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':sleep:',
'img'    => 'sleep.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':*',
'img'    => 'kiss.gif',
'width'  => 37,
'height' => 17,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':heart:',
'img'    => 'heart.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':five:',
'img'    => 'five.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':winner:',
'img'    => 'winner.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':loser:',
'img'    => 'loser.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':beer:',
'img'    => 'beer.gif',
'width'  => 17,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':10t:',
'img'    => '10ton.gif',
'width'  => 21,
'height' => 16,
'nocase' => false,
'align'  => 'middle'
),

// 25 smilies up to now
// hidden double smilies

array(
'code'   => ';)',
'img'    => 'ok.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':)',
'img'    => 'smile.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':(',
'img'    => 'sorrow.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':D',
'img'    => 'bigsmile.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':p',
'img'    => 'pff.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':\'-(',
'img'    => 'cry.gif',
'width'  => 20,
'height' => 14,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => '8(',
'img'    => 'shocked.gif',
'width'  => 22,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
)
);

?>