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
'code'   => ':mellow:',
'img'    => 'mellow.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':huh:',
'img'    => 'huh.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => '^_^',
'img'    => 'happy.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':o',
'img'    => 'ohmy.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ';)',
'img'    => 'wink.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':P',
'img'    => 'tongue.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':D',
'img'    => 'biggrin.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':lol:',
'img'    => 'laugh.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => 'B)',
'img'    => 'cool.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':rolleyes:',
'img'    => 'rolleyes.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => '-_-',
'img'    => 'sleep.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => '<_<',
'img'    => 'dry.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':)',
'img'    => 'smile.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':wub:',
'img'    => 'wub.gif',
'width'  => 22,
'height' => 29,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':angry:',
'img'    => 'mad.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':(',
'img'    => 'sad.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':unsure:',
'img'    => 'unsure.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':wacko:',
'img'    => 'wacko.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':blink:',
'img'    => 'blink.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':ph34r:',
'img'    => 'ph34r.gif',
'width'  => 20,
'height' => 20,
'nocase' => false,
'align'  => 'middle'
)
);

?>