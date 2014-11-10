<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    acleditor
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

table.acltable td
{
	vertical-align: top;
	padding: 2px 5px 2px 0;
}
table.acltable td.grant
{
	text-align: center;
}
table.acltable td.editactions
{
	white-space: nowrap;
	padding-right: 0;
	text-align: right;
}

table.acltable tr.userrow td
{
	padding: 0;
	vertical-align: bottom;
}
table.acltable tr.userrow td div
{
	margin-top: 7px;
	padding-top: 1px;
	font-style: italic;
	color: #003080;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk5.png);
<?php } ?>
}

table.acltable tr.line td
{
	border-top: solid 1px #D8D8D8;
}
table.acltable tr.first td
{
	/*border-top: 0;*/
}
table.acltable tr.editing td
{
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_or13.png);
<?php } ?>
}
table.acltable tr.disabled.allowed td.action,
table.acltable tr.disabled.allowed td.grant,
table.acltable tr.disabled.denied td.action,
table.acltable tr.disabled.denied td.grant,
table.acltable tr.disabled td.action,
table.acltable tr.disabled td.grant
{
	color: #909090;
	text-decoration: line-through;
}

table.acltable tr.allowed td.action,
table.aclediting .allowed
{
	color: #00A000;
}
table.acltable tr.allowed td.grant
{
	/*font-weight: bold;*/
	color: #00A000;
}
table.acltable tr.denied td.action,
table.aclediting .denied
{
	color: #C00000;
}
table.acltable tr.denied td.grant
{
	/*font-weight: bold;*/
	color: #C00000;
}

table.aclediting tr.margin td
{
	padding-top: 8px;
}
table.aclediting tr.first td,
table.aclediting tr.margin td
{
	vertical-align: top;
}
table.aclediting td.middle
{
	text-align: center;
	padding-left: 10px;
	padding-right: 10px;
}

#ActionID option.new_group
{
	border-top: solid 1px #D0D0D0;
}

