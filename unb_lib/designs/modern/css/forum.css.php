<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    forum
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

/* -------------------- forums container -------------------- */

.all_forums_section
{
	margin-top: 12px;
}
.all_forums_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}

.forums_sublevel
{
	border-left: solid 1px #E8E8E8;
	padding-left: 20px;
}

.forum_container
{
	clear: both;
	border: solid 1px #A5BFF1;
	margin: 12px auto 1px;
	padding: 1px;
}
.forum_container.first
{
	margin-top: 1px;
}
.forum_container.forum_category
{
	border: none;
	padding: 0px;
}

.forum_content
{
	padding: 5px 5px 5px 5px;
}
.forum_content.forum_forum
{
<?php if ($ie && !$_ie7) { ?>
	background: #EDEDFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
	min-height: 30px;
}
.forum_content.forum_weblink
{
<?php if ($ie && !$_ie7) { ?>
	background: #EDEDFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
	min-height: 30px;
}
.forum_content.forum_category
{
<?php if ($ie && !$_ie7) { ?>
	background: #DDDDDD;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk13.png);
<?php } ?>
	padding-right: 7px;
}
.forum_content.forum_category.inner
{
	/*margin-top: 12px;*/
}
.forum_content.forum_message
{
<?php if ($ie && !$_ie7) { ?>
	background: #D3D3FF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl17.png);
<?php } ?>
}
.forum_content.forum_edit
{
<?php if ($ie && !$_ie7) { ?>
	background: #EDEDFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
	padding-left: 55px;
}
.forum_container.forum_category .forum_content.forum_edit
{
<?php if ($ie && !$_ie7) { ?>
	background: #DDDDDD;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk13.png);
<?php } ?>
	padding-left: 25px;
}
.forum_content.forum_separator
{
	padding-top: 0;
<?php if ($ie && !$_ie7) { ?>
	background: #EDEDFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
	padding-bottom: 0;
}
.forum_content.forum_separator div
{
	border-top: solid 1px #A5BFF1;
}

.forum_content.forum_forum .forum_icon,
.forum_content.forum_weblink .forum_icon,
.forum_content.forum_category .forum_icon
{
	height: 50px;
	margin-bottom: -50px;
}
.forum_content.forum_forum .forum_data,
.forum_content.forum_weblink .forum_data,
.forum_content.forum_category .forum_data
{
	margin-left: 50px;
	margin-top: 2px;
}
/*.forum_content.forum_forum .forum_counter,*/
.forum_content.forum_weblink .forum_counter,
.forum_content.forum_category .forum_counter
{
	margin-left: 50px;
	text-align: right;
	padding-top: 3px;
	color: #707070;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}
.forum_content.forum_category .forum_icon
{
	height: 15px;
	margin-bottom: -20px;
	padding-top: 5px;
	line-height: 1px;   /* icons are displayed too deep in firefox otherwise */
}
.forum_content.forum_category .forum_data
{
	margin-left: 20px;
}
.forum_content.forum_category .forum_counter
{
	margin-left: 20px;
}

.forum_content.forum_forum .forum_data .name,
.forum_content.forum_weblink .forum_data .name,
.forum_content.forum_category .forum_data .name
{
	font-weight: bold;
	font-size: 1.1em;
}
.forum_content.forum_forum .forum_data .info,
.forum_content.forum_weblink .forum_data .info,
.forum_content.forum_category .forum_data .info
{
	float: right;
}
.forum_content.forum_forum .forum_data .new,
.forum_content.forum_category .forum_data .new
{
	font-weight: bold;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #FF8000;
}
/*.forum_content.forum_forum .forum_data .desc,*/
.forum_content.forum_weblink .forum_data .desc,
.forum_content.forum_category .forum_data .desc
{
	color: #404040;
	width: 75%;
}

.forum_edit table td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}

.all_forum_actions_top
{
	color: #404040;
	text-align: right;
}
.all_forum_actions_bottom
{
	margin-top: 3px;
	color: #404040;
	text-align: right;
}

/* forum counter data table */
.forum_content.forum_forum .forum_counter
{
	margin-left: 50px;
}
.forum_content.forum_forum .forum_counter table
{
	width: 100%;
}
.forum_content.forum_forum .forum_counter table td
{
	padding-top: 3px;
	color: #707070;
	vertical-align: bottom;
}
.forum_content.forum_forum .forum_counter table td.desc
{
	color: #404040;
	width: 49%;
	padding-right: 10px;
	vertical-align: top;
}
.forum_content.forum_forum .forum_counter table td.threadcount
{
	width: 11%;
	text-align: center;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}
.forum_content.forum_forum .forum_counter table td.postcount
{
	width: 12%;
	text-align: center;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}
.forum_content.forum_forum .forum_counter table td.lastpost
{
	width: 28%;
	text-align: right;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}

