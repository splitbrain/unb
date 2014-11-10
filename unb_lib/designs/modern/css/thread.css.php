<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    thread
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

/* -------------------- threads container -------------------- */

.all_threads_section
{
	margin-top: 12px;
}
.all_threads_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}
.all_threads_in_forum
{
	margin-top: 12px;
}

.thread_container
{
	clear: both;
	border: solid 1px #A0E0A0;
	margin: 12px auto 4px;
	padding: 1px;
}
.thread_container.first
{
	margin-top: 1px;
}
.thread_container.important
{
	border: solid 1px #F2C388;
}

.thread_content
{
	padding: 5px 5px 5px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #EDFFED;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_gn13.png);
<?php } ?>
	min-height: 22px;   /* moved note threads consist of a single line only and the icon gets too low then */
}
.thread_content.important
{
<?php if ($ie && !$_ie7) { ?>
	background: #FFF4E8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_or13.png);
<?php } ?>
}

.thread_content.thread_separator
{
	padding-top: 0;
	padding-bottom: 0;
	min-height: 0;
}
.thread_content.important.thread_separator
{
}
.thread_content.thread_separator div
{
	border-top: solid 1px #A0E0A0;
}
.thread_content.important.thread_separator div
{
	border-top: solid 1px #F2C388;
}
.thread_content.thread_edit
{
	padding-left: 30px;
}

.thread_content .thread_icon
{
	height: 25px;
	margin-bottom: -25px;
}
.thread_content .thread_data
{
	margin-left: 25px;
}
.thread_content .thread_forum
{
	margin-left: 25px;
}
.thread_content .thread_counter
{
	margin-left: 25px;
	padding-top: 0;
}
/* forum counter data table */
.thread_content .thread_counter table
{
	margin-left: auto;
	/*width: 75%;*/
	width: 100%;
}
.thread_content .thread_counter table td
{
	padding-top: 3px;
	color: #707070;
	vertical-align: bottom;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}
.thread_content .thread_counter table td.desc
{
	width: 30%;
	vertical-align: top;
}
.thread_content .thread_counter table td.viewcount
{
	/*width: 17%;*/
	width: 12%;
	text-align: center;
}
.thread_content .thread_counter table td.replycount
{
	/*width: 17%;*/
	width: 12%;
	text-align: center;
}
.thread_content .thread_counter table td.topicstart
{
	/*width: 34%;*/
	width: 22%;
	text-align: right;
}
.thread_content .thread_counter table td.lastpost
{
	/*width: 32%;*/
	width: 24%;
	text-align: right;
	padding-left: 5px;
}

.thread_content .thread_data .name
{
}
.thread_content .thread_data .info
{
	float: right;
}
.thread_content .thread_data .new
{
	font-weight: bold;
	/*font-size: <?php echo $UNB['Font']['smallsize'] ?>;*/
	color: #FF8000;
}
.thread_content .thread_data .desc
{
	color: #404040;
	width: 75%;
}

.thread_content.thread_edit table td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}

.all_thread_actions_top
{
	margin-bottom: -15px;
	color: #404040;
	text-align: right;
}
.all_thread_actions_bottom
{
	color: #404040;
	text-align: right;
}

.thread_between_note
{
	margin-top: 8px;
	margin-bottom: -4px;   /* there's already 12px top margin of the following thread_container */
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}

