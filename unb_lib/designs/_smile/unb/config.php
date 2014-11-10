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
'code'   => ':-)',
'img'    => 'smile.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ';-)',
'img'    => 'wink.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':cheesy:',
'img'    => 'cheesy.png',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':-D',
'img'    => 'grin.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-p',
'img'    => 'razz.png',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => '^_^',   // from IPB
'img'    => 'happy.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':blush:',
'img'    => 'blush.png',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':cool:',
'img'    => 'cool.png',
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
'code'   => ':*)',
'img'    => 'clown.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':huh:',   // from IPB
'img'    => 'huh.png',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-/',
'img'    => 'sceptic.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => '<_<',   // from IPB
'img'    => 'annoyed.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':-(',
'img'    => 'sad.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':\'(',
'img'    => 'cry.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':#:',
'img'    => 'angry.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':scared:',
'img'    => 'scared.gif',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => '8-(',
'img'    => 'shocked.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle'
),

array(
'code'   => ':nuts:',
'img'    => 'nuts.gif',
'width'  => 22,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

array(
'code'   => ':-O',
'img'    => 'yawn.gif',
'width'  => 16,
'height' => 15,
'nocase' => true,
'align'  => 'middle'
),

// ---------- 20 smilies ----------

array(
'code'   => ':)',
'img'    => 'smile.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ';)',
'img'    => 'wink.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':(',
'img'    => 'sad.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':D',
'img'    => 'grin.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':o)',
'img'    => 'clown.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':/',
'img'    => 'sceptic.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ';(',   // slang compatibility
'img'    => 'cry.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':\'-(',
'img'    => 'cry.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':flash:',
'img'    => 'flash.gif',
'width'  => 25,
'height' => 25,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':kotz:',   // still in German!
'img'    => 'kotz.gif',
'width'  => 64,
'height' => 32,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':-|',
'img'    => 'indifferent.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':-I',
'img'    => 'indifferent.png',
'width'  => 15,
'height' => 15,
'nocase' => false,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':ohr:',   // still in German!
'img'    => 'listen.png',
'width'  => 31,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':motz:',   // still in German!
'img'    => 'motz.gif',
'width'  => 31,
'height' => 20,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':finger:',   // still in German!
'img'    => 'finger.gif',
'width'  => 25,
'height' => 19,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':heart:',
'img'    => 'heart.png',
'width'  => 17,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':listen:',
'img'    => 'listen.png',
'width'  => 31,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':wall:',
'img'    => 'wall.gif',
'width'  => 25,
'height' => 20,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':moody:',
'img'    => 'moody.gif',
'width'  => 15,
'height' => 16,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':zzz:',
'img'    => 'sleep.gif',
'width'  => 15,
'height' => 24,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':mellow:',   // IPB compatibility
'img'    => 'indifferent.png',
'width'  => 15,
'height' => 15,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
),

array(
'code'   => ':gun:',
'img'    => 'gun.png',
'width'  => 36,
'height' => 16,
'nocase' => true,
'align'  => 'middle',
'hidden' => true
)

);

?>