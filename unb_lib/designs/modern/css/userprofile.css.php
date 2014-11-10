<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    userprofile
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

.profile_username
{
	font-size: 1.5em;
}

table.profile_personaldata
{
	margin-top: 1px;
}
table.profile_personaldata td.photocol
{
	width: 5%;
	padding-right: 15px;
	vertical-align: top;
}
table.profile_personaldata td.leftcol,
table.profile_forumdata td.leftcol,
table.profile_postdata td.sigleftcol,
table.profile_postdata td.avatarleftcol,
table.profile_extradata td.leftcol
{
	width: 110px;
	padding-right: 10px;
	padding-top: 1px;
	padding-bottom: 1px;
	white-space: nowrap;
	vertical-align: top;
	color: #707070;
}
table.profile_personaldata td.rightcol,
table.profile_forumdata td.rightcol,
table.profile_postdata td.sigrightcol,
table.profile_postdata td.avatarrightcol,
table.profile_extradata td.rightcol
{
	padding-top: 1px;
	padding-bottom: 1px;
	vertical-align: top;
}
table.profile_forumdata td.groupscol1
{
	width: 10%;
	padding-left: 10px;
	padding-top: 1px;
	padding-bottom: 1px;
	white-space: nowrap;
	vertical-align: top;
	color: #707070;
}
table.profile_forumdata td.groupscol2
{
	padding-left: 10px;
	padding-top: 1px;
	padding-bottom: 1px;
	white-space: nowrap;
	vertical-align: top;
}

table.profile_postdata td.avatarleftcol
{
	width: 1%;
	padding-left: 25px;
}
table.profile_postdata td.avatarrightcol
{
	width: 1%;
}

/* -------------------- e-mail form -------------------- */

.all_profile_email_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}

.profile_email_container
{
	border: solid 1px #F1BE6D;
	margin: 1px auto 12px;
	padding: 1px;
}
.profile_email_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	padding: 3px 3px;
}
