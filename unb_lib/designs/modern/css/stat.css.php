<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    stat
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.stat_container
{
	clear: both;
	border: solid 1px #DED680;
	margin: 1px auto 4px;
	padding: 1px;
}

.stat_content
{
	/*padding: 3px 5px 4px 5px;*/
	padding: 0px;
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFE8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_ye9.png);
<?php } ?>
}

.stat_content tr.head td
{
	padding: 2px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #E8E8E8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk9.png);
<?php } ?>
}
.stat_content tr.data td
{
	padding: 2px 5px;
}
.stat_content table.graphs tr.data td
{
	border-bottom: solid 1px #E8E8E8;
	vertical-align: top;
}
.stat_content table.graphs tr.data.last td
{
	border-bottom: none;
}

.stat_actions
{
	text-align: right;
	margin-bottom: 12px;
}

.stat_graph
{
	background: #B0E0F0;
	border-right: solid 2px #90C0D0;
	height: 18px;
	margin-bottom: -18px;
}
.stat_multigraph
{
	width: 100%;
	height: 18px;
	margin-bottom: -18px;
}
.stat_subgraph1
{
	height: 18px;
	background: #B0E0F0;
	border-right: solid 2px #90C0D0;
	float: left;
	margin-bottom: -17px;
}
.stat_subgraph2
{
	height: 18px;
	background: #C6F3BD;
	border-right: solid 2px #A9D8A0;
	float: left;
	margin-bottom: -18px;
}
.stat_graphtext
{
	text-align: right;
	margin-right: 3px;
}

