<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    forumvis
// Author:      Yves Goergen
// Last edit:   20060810

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

table.groupstable td
{
	vertical-align: top;
	padding: 2px 5px 2px 0;
}
table.groupstable td.editactions
{
	white-space: nowrap;
	padding-right: 0;
	text-align: right;
}

table.groupstable tr.first td
{
}
table.groupstable tr.editing td,
table.groupstable tr.oddcount.editing td
{
<?php if (!$ie || $ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_or13.png);
<?php } ?>
}
table.groupstable tr.oddcount td
{
<?php if (!$ie || $ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk2.png);
<?php } ?>
}
