<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    groupseditor
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

table.groupstable td
{
	vertical-align: top;
	padding: 2px 5px 2px 0;
}
table.groupstable td.showinteam,
table.groupstable td.publicgroup
{
	text-align: center;
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
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_or13.png);
<?php } ?>
}
table.groupstable tr.oddcount td
{
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk2.png);
<?php } ?>
}

img.hidden
{
	visibility: hidden;
}

