<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    register (also includes installation styles)
// Author:      Yves Goergen
// Last edit:   20050826

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.register_form td.leftcol
{
	width: 110px;
	padding-top: 3px;
	padding-right: 10px;
	white-space: nowrap;
	vertical-align: top;
}

table.installation td.leftcol
{
	width: 110px;
	padding-right: 10px;
	white-space: nowrap;
	vertical-align: top;
	padding-top: 3px;
}

table.installation td .subtitle,
table.installation td .subtitle_indent
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}
table.installation td .subtitle_indent
{
	margin-left: 19px;
}

