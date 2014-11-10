<?php
// Unclassified Template Engine
// http://unclassified.de
// Copyright 2005 by Yves Goergen
//
// ute-runtime.conf.php
// Template runtime library configuration

// This array is always first initialised by the runtime library.
// The compiler library is always loaded afterwards, so we can safely set this to an empty array here.
$UTE = array();

// ---------- LOADER OPTIONS ----------

// Source files and cache directories.
// The cache directory will be created if it doesn't exist.
//
$UTE['__sourcePath'] = dirname(__FILE__) . '/designs/' . $UNB['Design']['CurrentDesign'] . '/tpl/';
$UTE['__cachePath'] = $UTE['__sourcePath'] . 'cache/';

// Fast mode assumes the template is already compiled and current and does not
// check the source file nor tries to compile it. Recommended to false, only
// set it to true to gain a little more performance, and be sure you know what
// you're doing!
//
$UTE['__fastMode'] = false;

// Always compile the source file. Disables fastMode. The compiled file will
// still be written to the cache path as it can only be included this way.
// Recommended to false, setting it to true causes unnecessary load.
//
$UTE['__noCache'] = false;

// Character set. Used for string manipulation functions. Only "ISO-8859-1"
// and "UTF-8" are supported.
//
$UTE['__characterSet'] = $UNB['CharSet'];

// Exit the programme execution with an appropriate error message in case the
// template cache file cannot be written.
//
$UTE['__haltOnFileError'] = true;


// ---------- RUNTIME OPTIONS ----------

?>