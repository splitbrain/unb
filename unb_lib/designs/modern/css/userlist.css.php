<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    userlist
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.users_filter_container
{
	clear: both;
	border: solid 1px #DED680;
	margin: 1px auto 4px;
	padding: 1px;
}

.users_filter_content
{
	padding: 3px 5px 4px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFE8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_ye9.png);
<?php } ?>
}

.users_container
{
	clear: both;
	border: solid 1px #A0E0A0;
	margin: 1px auto 4px;
	padding: 1px;
}

.users_content
{
	/*padding: 3px 5px 4px 5px;*/
	padding: 0px;
<?php if ($ie && !$_ie7) { ?>
	background: #EDFFED;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_gn7.png);
<?php } ?>
}

.users_content tr.head td
{
	padding: 2px 5px;
<?php if ($ie && !$_ie7) { ?>
	background: #E8E8E8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk9.png);
<?php } ?>
}
.users_content tr.data td
{
	padding: 2px 5px;
	border-bottom: solid 1px #E8E8E8;
	vertical-align: top;
}
.users_content tr.data.last td
{
	border-bottom: none;
}
.users_content tr.edit > td
{
	padding: 2px 5px;
	vertical-align: top;
}
.users_content tr.edit td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}
.users_content tr.edit td.rightcol
{
	text-align: right;
	width: 80px;
	padding-left: 20px;
	vertical-align: bottom;
}

.users_actions_top
{
	margin-top: 12px;
	margin-bottom: 4px;
	text-align: right;
}
.users_actions_bottom
{
	margin-top: 4px;
	margin-bottom: 12px;
	text-align: right;
}

.vcard_outer_container
{
	float: left;
	width: 330px;
	margin-top: 1px;
	margin-right: 20px;
	margin-bottom: 20px;
}
.vcard_container
{
	border: solid 1px #D0D0D0;
	padding: 1px;
	margin: 0;
}
.vcard_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	height: 130px;
	padding: 3px 5px;
}
.vcard_content img.avatar
{
	float: right;
}
.vcard_content .username
{
	font-size: 1.35em;
	line-height: 1.1em;
}
.vcard_content .username small
{
	font-size: 0.6em;
}
.vcard_content .title
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-bottom: 10px;
}
.vcard_content .contacts
{
	margin-top: 4px;
	margin-bottom: 4px;
}
.vcard_content td.leftcol
{
	width: 70px;
	padding-right: 10px;
	white-space: nowrap;
	vertical-align: top;
	color: #707070;
}
