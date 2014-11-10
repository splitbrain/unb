<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    search
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.search_container
{
	clear: both;
	border: solid 1px #DED680;
	margin: 1px auto 4px;
	padding: 1px;
}

.search_content
{
	padding: 3px 5px 4px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFE8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_ye9.png);
<?php } ?>
}

.search_content td
{
	padding: 3px 0;
}

.search_actions
{
	text-align: right;
	margin-bottom: 12px;
}

