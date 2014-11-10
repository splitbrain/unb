<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    threadattributes
// Author:      Yves Goergen
// Last edit:   20060117

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

span.threadattributetag
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	font-family: <?php echo $UNB['Font']['family'] ?>;
	padding: 0px 3px;
	margin-right: 8px;

	border-top: solid 1px #E0E0E0;
	border-left: solid 1px #E0E0E0;
	border-right: solid 1px #808080;
	border-bottom: solid 1px #808080;
}

span.threadattributetag.green
{
	color: #006000;
	border-top: solid 1px #E0FFE0;
	border-left: solid 1px #E0FFE0;
	border-right: solid 1px #70E070;
	border-bottom: solid 1px #70E070;
	background: #C0FFC0;
}

span.threadattributetag.yellow
{
	color: #606000;
	border-top: solid 1px #FFFFE0;
	border-left: solid 1px #FFFFE0;
	border-right: solid 1px #E0E070;
	border-bottom: solid 1px #E0E070;
	background: #FFFFC0;
}

span.threadattributetag.red
{
	color: #600000;
	border-top: solid 1px #FFE0E0;
	border-left: solid 1px #FFE0E0;
	border-right: solid 1px #E07070;
	border-bottom: solid 1px #E07070;
	background: #FFC0C0;
}

span.threadattributetag.grey
{
	color: #404040;
	border-top: solid 1px #E0E0E0;
	border-left: solid 1px #E0E0E0;
	border-right: solid 1px #808080;
	border-bottom: solid 1px #808080;
	background: #D0D0D0;
}

