<?php
// AdvancedBBCode 1.2
// http://software.unclassified.de/abbc
// Copyright 2003-9 by Yves Goergen
//
// abbc.css.php
// ABBC CSS default stylesheet
// These definitions only provide a basic formatting of ABBC output and may be
// overridden by other designs' stylesheets.

// [nodoc]
// This is the only stand-alone file in this directory.
// (It will not be included through forum.php)
define('UNB_RUNNING', 1);

// We don't want E_NOTICE output here!
error_reporting(E_ALL & ~E_NOTICE);

require('abbc.conf.php');
header('Content-Type: text/css');
header('Expires: ' . date('r', time() + 86400));   // 1 day

?>
/* base font */
.abbc, .abbc .p, .abbc li
{
	/*font: 11px/16px Verdana,Arial,sans-serif;*/
}
/* ABBC_PARAGRAPH option */
.abbc .p
{
	margin: 10px 0px;
}

/* display thinner border around linked images */
.abbc a img
{
	border: solid 1px #0040FF;
}

/* do some smilies positioning */
.abbc img.smilie
{
	position: relative;
	top: -1px;
	margin: 0px 1px;
}

/* do some lists fine-tuning */
.abbc ul
{
	margin-top: 0px;
	margin-bottom: 0px;
	margin-left: 0px;
	padding-left: 20px;
}
.abbc li
{
	padding-top: 1px;
	padding-bottom: 1px;
}

/* quotations, 1st level */
.abbc .quote
{
	border: solid 1px #C0C0C0;
	padding: 4px 5px;
	margin: 3px 20px 3px 20px;
	/*color: #000090;*/
	color: #305030;
	background: #E4E4E4;
}
/* quotations 2nd level, etc. */
.abbc .quote .quote
{
	border: solid 1px #B8B8B8;
	/*color: #800000;*/
	color: #407040;
	background: #DCDCDC;
}
.abbc .quote .quote .quote
{
	border: solid 1px #B0B0B0;
	/*color: #007000;*/
	color: #509050;
	background: #D4D4D4;
}
/* quotation's original author name, displayed above quote block */
.abbc .qname
{
	margin: 0px 21px;
	font-style: italic;
	color: #606060;
	margin-bottom: 4px;
}
/* same, 2nd level */
.abbc .quote .qname
{
	color: #606060;
}
.abbc .quote .quote .qname
{
	color: #606060;
}

/* fixed-width font and code block font */
.abbc tt,
.abbc div.code,
.abbc .code_odd_cont,
.abbc .code_even_cont
{
	font-family: Andale Mono,Courier New,monospace;
}
.abbc tt
{
	color: #009000;
}
/* source code blocks */
.abbc div.code
{
	/*color: #900000;*/
	margin: 3px 0 6px 0;
}
/* each table cell for line-numbered source code */
.abbc .code_odd_cont,
.abbc .code_even_cont
{
	padding: 1px 0;
	margin: 0px;
}
/* code block line numbers */
.abbc .code_odd_lnnr,
.abbc .code_even_lnnr
{
	text-align: right;
	padding-right: 2px;
}

/* abbc error highlighting: unclosed open tag */
.abbc .eop
{
	background-color: #FFFF55;
}
/* abbc error highlighting: unopened close tag */
.abbc .ecl
{
	background-color: #99FF99;
}

<?php if ($ABBC['Config']['custom_a']) { ?>
/* if we use custom <a/> colours, here they are */
.abbc a
{
	color: #0040FF;
	text-decoration: none;
}
.abbc a:hover
{
	color: #0040FF;
	text-decoration: none;
}
<?php } ?>
