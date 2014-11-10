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

$ABBC['Smilies'] = array(

array(
'code'   => ':cool:',
'img'    => 'smiley-cool.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':\'(',
'img'    => 'smiley-cry.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':red:',
'img'    => 'smiley-embarassed.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':foot:',
'img'    => 'smiley-foot-in-mouth.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-(',
'img'    => 'smiley-frown.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => 'O:-)',
'img'    => 'smiley-innocent.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-*',
'img'    => 'smiley-kiss.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-D',
'img'    => 'smiley-laughing.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-#',
'img'    => 'smiley-sealed.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-)',
'img'    => 'smiley-smile.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-O',
'img'    => 'smiley-surprised.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-p',
'img'    => 'smiley-tongue-out.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-?',
'img'    => 'smiley-undecided.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ';-)',
'img'    => 'smiley-wink.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => 'X-O',
'img'    => 'smiley-yell.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle'
),

// 15 smilies...

array(
'code'   => ':)',
'img'    => 'smiley-smile.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ';)',
'img'    => 'smiley-wink.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':(',
'img'    => 'smiley-frown.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':\'-(',
'img'    => 'smiley-cry.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':D',
'img'    => 'smiley-laughing.png',
'width'  => 18,
'height' => 18,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
)
);

?>