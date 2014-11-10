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
'code'   => ':angry:',
'img'    => 'angry.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':D',
'img'    => 'biggrin.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':confused:',
'img'    => 'confused.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => '8-O',
'img'    => 'eek.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':(',
'img'    => 'frown.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':mad:',
'img'    => 'mad.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':rolleyes:',
'img'    => 'rolleyes.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':)',
'img'    => 'smile.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => '=)',
'img'    => 'tongue.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ';)',
'img'    => 'wink.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

// 10 smilies...

array(
'code'   => ':-D',
'img'    => 'biggrin.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':-(',
'img'    => 'frown.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':-)',
'img'    => 'smile.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ';-)',
'img'    => 'wink.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
)
);

?>