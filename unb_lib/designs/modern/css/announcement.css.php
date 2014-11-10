<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    announcement
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.all_announcements_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}

.announcement_container
{
	clear: both;
	border: solid 1px #DED680;
	margin: 12px auto 4px;
	padding: 1px;
}
.announcement_container.first
{
	margin-top: 1px;
}
.announcement_container.important
{
	border: solid 1px #F69088;
}

.announcement_content
{
	padding: 3px 5px 4px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFE8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_ye13.png);
<?php } ?>
}
.announcement_content.important
{
<?php if ($ie && !$_ie7) { ?>
	background: #FFEDED;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_re9.png);
<?php } ?>
}

.announcement_content.thread_separator
{
	padding-top: 0;
	padding-bottom: 0;
}
.announcement_content.important.announcement_separator
{
}
.announcement_content.announcement_separator div
{
	border-top: solid 1px #DED680;
}
.announcement_content.important.announcement_separator div
{
	border-top: solid 1px #F69088;
}

.announcement_content .announcement_icon
{
	height: 25px;
	margin-bottom: -25px;
}
.announcement_content .announcement_actions
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #606060;
	float: right;
}
.announcement_content .announcement_data
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-left: 25px;
	color: #606060;
}
.announcement_content .announcement_subject
{
	margin-left: 25px;
	margin-top: 5px;
	padding-bottom: 2px;
	border-bottom: solid 1px #D8D8D8;
	margin-bottom: 5px;
}
.announcement_content .announcement_body
{
	margin-left: 25px;
	margin-top: 5px;
	text-align: justify;
}

.announcement_infobar
{
	border-bottom: solid 1px #D8D8D8;
	background: #F8F8C8;
	padding: 3px 5px;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}

.all_announcement_actions_bottom
{
	color: #404040;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	text-align: right;
	margin-bottom: 15px;
}
.all_announcement_actions_bottom.alone
{
	margin-top: 8px;
}

