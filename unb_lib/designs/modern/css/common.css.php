<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    common
// Author:      Yves Goergen
// Last edit:   20080305

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

@import url("announcement.css.php");
@import url("controlpanel.css.php");
@import url("forum.css.php");
@import url("post.css.php");
@import url("register.css.php");
@import url("search.css.php");
@import url("stat.css.php");
@import url("thread.css.php");
@import url("userlist.css.php");
@import url("userprofile.css.php");


/* -------------------- GENERAL -------------------- */

html
{
	background: #FFFFFF url(../img/back_hlines.png);
	color: #000000;
	cursor: default;
}
body
{
<?php if ($ie && !$ie7) { ?>
	width: 750px;
<?php } else { ?>
	width: 95%;
	min-width: 350px;
	max-width: 800px;
<?php } ?>
	margin: 10px auto;
	padding: 0;
}

html,
body,
p,
div.p,
td,
li,
input,
textarea,
select
{
	font-size: <?php echo $UNB['Font']['size'] ?>;
	font-family: <?php echo $UNB['Font']['family'] ?>;
}
html,
body,
p,
div.p,
td,
li
{
	line-height: 130%;
}

p,
div.p
{
	margin-top: 12px;
	margin-bottom: 12px;
}

div.error,
div.info
{
	/*border: solid 1px #E8D0A0;*/
	border: solid 1px #E0B070;
	padding: 1px;
}
div.info
{
	/*border-color: #CAE8BA;*/
	border-color: #80E070;
}
div.error div,
div.info div
{
	font-weight: bold;
	color: #A02000;
	background: #F8E0B0;
	padding: 2px 4px 2px 4px;
	line-height: normal;
}
div.info div
{
	color: #107000;
	background-color: #DAF8CA;
}

a
{
	color: #0051F6;
	text-decoration: none;
}
a:hover,
a:focus
{
	color: #FF9000;
}
a img
{
	border: 0px;
}
.abbc a img
{
	border: 1px solid #0051F6;
}

small
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}

h1
{
	margin: 0;
	padding: 3px 0 1px 6px;
	font-size: 1em;
	font-weight: normal;
	color: #404040;
<?php if ($ie && !$_ie7) { ?>
	background: #EEEEEE;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>caption_back.png) center left no-repeat;
<?php } ?>
}

tr.hover:hover td
{
<?php if ($ie && !$_ie7) { ?>
	background: #EEEEEE;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk5.png);
<?php } ?>
}

.nowrap
{
	white-space: nowrap;
}

.img_trans_back
{
	border: solid 1px #DDDDDD;
	background: url(<?php echo $ImgPath ?>trans_back.png);
}

/* -------------------- PAGE HEAD -------------------- */

.head_logo
{
	padding-bottom: 4px;
}

.head_logincontrol
{
	text-align: right;
	padding-bottom: 6px;
}

ul.head_navigation
{
	list-style: none;
	border-bottom: 1px solid #CCCCCC;
	padding: 1px 6px 2px;   /* XHTML: set last value to 3px, HTML: 2px */
	margin: 0px 0px 12px;
}
ul.head_navigation li
{
	display: inline;
}
ul.head_navigation li a
{
<?php if ($ie && !$_ie7) { ?>
	background: #F0F0F0;
<?php } else { ?>
	/* navi link background with grey gradient: */
	background: url(<?php echo $ImgPath ?>navlink_back.png) bottom repeat-x;
	/* flat navi link background: */
	/*background: url(<?php echo $ImgPath ?>shade_bk6.png);*/
<?php } ?>
	border: solid 1px #CCCCCC;
	border-bottom: none;
	margin-right: 4px;
	padding: 2px 6px 3px;
	text-decoration: none;
	color: #0051F6;
	white-space: nowrap;
}
ul.head_navigation li a img
{
	margin-right: 2px;
}
ul.head_navigation li.active
{
}
ul.head_navigation li.active a
{
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFFF;
<?php } else { ?>
	/* active navi link background with white gradient: */
	background: url(<?php echo $ImgPath ?>navlink_active_back.png) bottom repeat-x;
	/* flat active navi link background: */
	/*background: url(<?php echo $ImgPath ?>shade_wh15.png);*/
<?php } ?>
	border-bottom: solid 1px #FFFFFF;
}
ul.head_navigation li a:hover
{
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_wh15.png);
<?php } ?>
	/* dark hover navi link background: */
	/*background: url(<?php echo $ImgPath ?>shade_bk13.png);
	border-bottom-color: #ECECEC;*/
	color: #0051F6;
}
ul.head_navigation li a:active,
ul.head_navigation li a:focus
{
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFFF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_wh15.png);
<?php } ?>
	border-bottom: solid 1px #FFFFFF;
	color: #0051F6;
}

/* -------------------- PAGE FOOT -------------------- */

.foot_container
{
	clear: both;
}
.foot_content
{
	margin-top: 12px;
	text-align: center;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	line-height: 1.3em;
	color: #707070;
	border-top: 1px solid #CCCCCC;
	padding-top: 2px;
}

.foot_logincontrol
{
	margin: 12px 0;
	text-align: right;
}

/* -------------------- OVERVIEW PAGE -------------------- */

.path
{
	margin-bottom: 12px;
}
.path .sep
{
	color: #707070;
}
.path .thread
{
	margin-top: 4px;
}
.path .thread .subject
{
	font-size: 1.5em;
	line-height: 140%;
}
.path .thread a
{
	color: #202020;
}
.path .thread a:hover
{
	color: #FF9000;
}
.path .desc
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}

.overview_actions_top
{
	text-align: right;
}

.jumpandsearchbox
{
	clear: both;
	margin-top: 12px;
}

.searchbox
{
	float: right;
}
.searchbox input[type="text"]
{
	width: 140px;
}
.jumpforumbox
{
}

/* -------------------- JUMP FORUM SELECT BOX -------------------- */

option.forum
{
	color: #000000;
	background: #FFFFFF;
}
option.category
{
	color: #000000;
	background: #EEEEEE;
}
option span.light  /* used for lighter hierarchy lines in the dropdown tree */
{
	color: silver;
}

/* -------------------- ADVANCED OPTIONS BOX -------------------- */

.advanced_options_container
{
	clear: both;
	border: solid 1px #CCCCCC;
	margin: 1px auto 0px;
	padding: 1px;
}
.advanced_options_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F8F8F8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk3.png);
<?php } ?>
	padding: 3px 5px 4px 5px;
}
.advanced_option
{
	margin: 2px 0;
}
.advanced_option.new_group
{
	margin-top: 12px;
}
.advanced_subtitle
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
	margin-left: 20px;
}

/* -------------------- SPLIT THREADS -------------------- */

table.thread_split_data td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}
.thread_split_post .head
{
	border: solid 1px #A0E0A0;
	padding: 1px;
	margin-bottom: 3px;
}
.thread_split_post .head > div
{
<?php if ($ie && !$_ie7) { ?>
	background: #F0F8F0;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_gn7.png);
<?php } ?>
	padding: 2px 4px;
}
.thread_split_post .checkbox
{
	float: right;
}
.thread_split_post .subject
{
	padding-bottom: 2px;
	border-bottom: solid 1px #D8D8D8;
	margin-bottom: 8px;
}
.thread_split_post .body
{
	margin: 8px 0 15px;
}

/* -------------------- FORMS -------------------- */

form
{
	display: inline;
	margin: 0px;
}

/* when updating textarea/textfield border width or padding, also update div.outerText{area,field}100pc */
input[type="text"],
input[type="password"],
textarea
{
	border: solid 1px #C0C0C0;
<?php if ($ie && !$_ie7) { ?>
	background: #FFFFFF;
<?php } else { ?>
	background: #FFFFFF url(<?php echo $ImgPath ?>textbox-back.png) repeat-x;
<?php } ?>
	color: #000000;
	padding: 1px 0px 1px 2px;

	<?php if ($gecko || $opera) { ?>
	margin-top: 2px;
	<?php } ?>

	<?php if ($ie && !$ie7 || $konq) { ?>
	scrollbar-arrow-color: #666666;
	scrollbar-base-color: #FFFFFF;
	scrollbar-darkshadow-color: #CCCCCC;
	scrollbar-face-color: #E8E8E8;
	scrollbar-highlight-color: #F0F0F0;
	scrollbar-shadow-color: #E0E0E0;
	scrollbar-track-color: #FFFFFF;
	<?php } ?>
}
textarea
{
	padding: 0px 0px 0px 2px;
}
input[type="text"]:hover,
input[type="text"]:focus,
input[type="password"]:hover,
input[type="password"]:focus,
textarea:hover,
textarea:focus
{
	border-color: #5580DD;
}

/* bottom-only borders for text fields */
input[type="text"],
input[type="password"]
{
/*	border-top: 0;
	border-left: 0;
	border-right: 0;
	padding-left: 1px;*/
}

/* textareas that have width:100% need this outer padding which is
   the sum of horizontal (left+right) border widths and paddings */
div.outerTextarea100pc
{
	padding-right: 4px;
}
div.outerTextarea100pc textarea
{
	margin-right: -4px;
<?php if (!$ie || $ie7) { ?>
	width: 100%;
<?php } ?>
}
/* textfields that have width:100% need this outer padding which is
   the sum of horizontal (left+right) border widths and paddings */
div.outerTextfield100pc
{
	padding-right: 4px;
}
div.outerTextfield100pc input[type="text"],
div.outerTextfield100pc input[type="password"]
{
	margin-right: -4px;
	width: 100%;
}

input[type="text"][disabled],
input[type="password"][disabled],
textarea[disabled]
{
	border: solid 1px #C0C0C0;
	color: GrayText;
}

input[type="checkbox"],
input[type="radio"]
{
	<?php if ($opera) { ?>
	margin: 0px 4px 1px 0;
	<?php } else { ?>
	margin: 1px 6px 1px 0;
	<?php } ?>
	padding: 0;
	cursor: pointer;
}
/* the label element doesn't see if there's a disabled input element within */
/*input[type="checkbox"][disabled],
input[type="radio"][disabled]
{
	cursor: default;
}*/
input[type="radio"]
{
	vertical-align: middle;
}
label
{
	cursor: pointer;
}

select,
select[disabled],
select[disabled]:hover
{
	border: 1px solid #C0C0C0 !important;
	background: #FAFAFA !important;
	padding: 0px 0px !important;
	cursor: pointer;
}
select:hover,
select:focus
{
	border: 1px solid #5580DD !important;
}
table
{
	empty-cells: show;
}
option[selected]
{
	font-style: italic;
}
option:hover small
{
	color: white;
}

input[type="button"],
input[type="submit"],
input[type="button"][disabled]:hover,
input[type="submit"][disabled]:hover,
input[type="button"][disabled]:active,
input[type="submit"][disabled]:active,
input[type="button"][disabled]:focus,
input[type="submit"][disabled]:focus
input[type="button"][disabled].defaultbutton:hover,
input[type="submit"][disabled].defaultbutton:hover,
input[type="button"][disabled].defaultbutton:active,
input[type="submit"][disabled].defaultbutton:active,
input[type="button"][disabled].defaultbutton:focus,
input[type="submit"][disabled].defaultbutton:focus
{
	border: solid 1px #C0C0C0;
	background: #E7E7E7 url(<?php echo $ImgPath ?>button_back.png) repeat-x;
<?php if ($opera) { ?>
	padding: 1px 6px;
<?php } else { ?>
	padding: 0px 3px;
<?php } ?>
	cursor: pointer;
}
input[type="button"]:hover,
input[type="submit"]:hover,
input[type="button"]:focus,
input[type="submit"]:focus,
input[type="button"].defaultbutton:hover,
input[type="submit"].defaultbutton:hover,
input[type="button"].defaultbutton:focus,
input[type="submit"].defaultbutton:focus
{
	border: solid 1px #5580DD;
	background: #D7DBF7 url(<?php echo $ImgPath ?>button_back_hover.png) repeat-x;
}
input[type="button"]:active,
input[type="submit"]:active,
input[type="button"].defaultbutton:active,
input[type="submit"].defaultbutton:active,
input[type="button"].selected,
input[type="submit"].selected
{
	border: solid 1px #5580DD;
	background: #D7DBF7 url(<?php echo $ImgPath ?>button_back_pushed.png) repeat-x;
}
input[type="button"][disabled],
input[type="submit"][disabled],
input[type="button"][disabled].defaultbutton,
input[type="submit"][disabled].defaultbutton
{
	border: solid 1px #C0C0C0;
	color: #808080;
}
input[type="button"].defaultbutton,
input[type="submit"].defaultbutton
{
	border: solid 1px #606060;
}
/* Attention, CSS3 ahead! */
input[style*="monospace"]
{
	padding-top: 1px;
}

input[type="file"]
{
	<?php if ($gecko) { ?>
	/*-moz-appearance: textbox !important;
	border: 1px solid ThreeDShadow !important;
	-moz-border-left-colors: #B4B4B4 white !important;
	-moz-border-top-colors: #B4B4B4 white !important;
	-moz-border-right-colors: #B4B4B4 white !important;
	-moz-border-bottom-colors: #B4B4B4 white !important;
	background: #FAFAFA !important;*/
	<?php } ?>

	<?php if ($opera) { ?>
	border: 0;
	<?php } ?>
}

/*
input[type="checkbox"]
{
	border: 1px solid #B4B4B4 !important;

	<?php if ($gecko) { ?>
	-moz-appearance: none !important;
	-moz-border-left-colors: transparent #B4B4B4 !important;
	-moz-border-top-colors: transparent #B4B4B4 !important;
	-moz-border-right-colors: transparent #B4B4B4 !important;
	-moz-border-bottom-colors: transparent #B4B4B4 !important;
	width: 16px;
	height: 16px;
	<?php } ?>
}
input[type="checkbox"]:hover,
input[type="checkbox"]:focus
{
	border: 1px solid #5580DD !important;

	<?php if ($gecko) { ?>
	-moz-border-left-colors: transparent #5580DD !important;
	-moz-border-top-colors: transparent #5580DD !important;
	-moz-border-right-colors: transparent #5580DD !important;
	-moz-border-bottom-colors: transparent #5580DD !important;
	<?php } ?>
}
input[type="radio"]
{
	border: 1px solid #B4B4B4 !important;

	<?php if ($gecko) { ?>
	-moz-appearance: none !important;
	-moz-border-left-colors: transparent #B4B4B4 !important;
	-moz-border-top-colors: transparent #B4B4B4 !important;
	-moz-border-right-colors: transparent #B4B4B4 !important;
	-moz-border-bottom-colors: transparent #B4B4B4 !important;
	width: 16px;
	height: 16px;
	<?php } ?>
}
*/

input.transparent
{
	border: 0px;
	background: transparent;
	color: #202020;
	margin-bottom: 1px;
}
input.transparent:hover,
input.transparent:focus
{
	border: 0px;
}

<?php
// Include user CSS definitions
@include('../css.user.php');
@include('../../css.user.php');
?>
