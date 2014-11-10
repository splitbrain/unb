<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    controlpanel
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

/* -------------------- left-side categories -------------------- */

div.categories_outer_container
{
	float: left;
	width: 20%;   /* also update .page_container{margin-left} */
}
div.categories_container
{
	overflow: hidden;
	margin: 0;
	border: solid 1px #D0D0D0;
	padding: 1px;
}
div.categories
{
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk3.png);
<?php } ?>
	padding: 0;
}

div.categories a
{
	display: block;
	margin-top: 1px;
	padding: 3px 5px;
}
div.categories a.first
{
	margin-top: 0;
}
div.categories a.selected
{
	padding: 2px 4px;
	border: solid 1px #CCCCCC;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk9.png);
<?php } else { ?>
	background: #DDDDDD;
<?php } ?>
}
div.categories a:hover
{
	padding: 2px 4px;
	border: solid 1px #CCCCCC;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk3.png);
<?php } else { ?>
	background: #EEEEEE;
<?php } ?>
}
div.categories .text
{
	margin-top: 1px;
	padding: 0px 5px;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	text-align: center;
}
div.categories .newgroup
{
	display: block;
	margin-top: 1px;
	border-top: solid 1px #D0D0D0;
}

/* -------------------- right-side content -------------------- */

.page_outer_container
{
	margin-left: 22%;   /* also update div.categories_container{width} */
}
.page_container
{
	border: solid 1px #A5BFF1;
	padding: 1px;
}

.page_container h1
{
	margin: 0;
	font-size: 1em;
	font-weight: bold;
	color: black;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk13.png);
<?php } ?>
	padding: 2px 5px;
}

.page_content
{
	padding: 7px 5px;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bl9.png);
<?php } ?>
}

.page_content .option
{
	margin-top: 15px;
}
.page_content .option.first
{
	margin-top: 0;
}

.page_content .option h2
{
	clear: right;
	margin: 0;
	font-size: 1em;
	font-weight: bold;
	color: #404040;
	padding: 0;
	margin-bottom: 6px;
}
.page_content .option .box
{
	margin-bottom: 6px;
	text-align: justify;
}
.page_content .option .box.small,
.page_content .option td.small
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}
.page_content .option .box.indent
{
<?php if ($opera) { ?>
	margin-left: 24px;
<?php } else { ?>
	margin-left: 19px;
<?php } ?>
	margin-top: -4px;
}
.page_content .option td.indent
{
<?php if ($opera) { ?>
	padding-left: 24px;
<?php } else { ?>
	padding-left: 19px;
<?php } ?>
}

.page_content .option td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}
.page_content .option td.leftcol.topalign
{
	vertical-align: top;
	padding-top: 3px;
}

.page_container .buttons
{
	border-top: solid 1px #CCCCCC;
	padding: 6px;
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bl9.png);
<?php } ?>
	text-align: center;
}

h2.forumname
{
	font-weight: normal !important;
}

.signature_preview   /* sync with post.css:.post_signature */
{
	color: #808080;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	text-align: right;
	margin: 0px 0px 0px 5%;
	padding: 4px 0;
}

.page_content .plugin
{
	border-top: solid 1px #CCCCCC;
	padding: 3px 0;
}
.page_content .plugin.first
{
	border-top: none;
}
.page_content .plugin.oddcount
{
<?php if (!$ie || $_ie7) { ?>
	background: url(<?php echo $ImgPath ?>shade_bk2.png);
<?php } ?>
}
.page_content .plugin.disabled .name
{
	color: #404040;
	text-decoration: line-through;
}
.page_content .plugin .name
{
}
.page_content .plugin .actions
{
	float: right;
	margin-left: 10px;
}
.page_content .plugin .desc
{
	/*margin-left: 20px;*/
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #707070;
}
.page_content .plugin img.icon
{
	vertical-align: middle;
	margin: -1px 5px 1px 0;
}
.page_content .plugin .status_error
{
	margin-top: 2px;
}

.page_content table.plugininfo td
{
	vertical-align: top;
}

