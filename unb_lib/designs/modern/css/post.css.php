<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design CSS definition file
//
// Design name: Modern
// CSS file:    post
// Author:      Yves Goergen
// Last edit:   20060624

require(dirname(dirname(__FILE__)) . '/cssconfig.php');
?>

a .flatbutton
{
	padding: 2px 4px;
}
a:hover .flatbutton
{
	border: solid 1px #5580DD;
	background: #E8EEFB;
	padding: 1px 3px;
}
a:hover .flatbutton:active
{
	border: solid 1px #5580DD;
	background: #AAC0EE;
	padding: 1px 3px;
}

img.smilie
{
	vertical-align: middle;
}

/* -------------------- post container -------------------- */

.all_posts_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}

.post_readline
{
	margin: 6px 0;
	border-top: solid 2px #A00000;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	line-height: 1em;
	padding-top: 1px;
	color: #505050;
	text-align: center;
}

.post_container
{
	clear: both;
	border: solid 1px #A0E0A0;
	margin: 8px auto 4px;
	padding: 1px;
}
.post_container.oddcount
{
	border: solid 1px #A5BFF1;
}
.post_container.first
{
	margin-top: 1px;
}
.post_container.last
{
	margin-bottom: 4px;
}

.post_threadinfo
{
	border-bottom: solid 1px #D8D8D8;
	background: #F8F8F8;
	padding: 3px 5px;
}

.post_header
{
	background: #E0E0E0 url(<?php echo $ImgPath ?>posthead_back.png) repeat-x;
	padding: 3px 5px;
	vertical-align: bottom;
}

.post_avatar
{
	float: right;
	margin: 0px 0px 5px 10px;
}

.post_actions
{
	float: right;
	line-height: 2.4em;
	color: #808080;
}
.post_actions a
{
}

.post_headinfo
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #808080;
}

.post_username
{
	font-size: 1.6em;
	line-height: 1.2em;
	color: darkblue;
	margin-right: 4px;
	cursor: pointer;
}

.post_userinfo
{
	/*margin-right: 10px;*/
}
.post_date
{
	margin-left: 10px;
	margin-right: 10px;
	white-space: nowrap;
}
.post_num
{
	white-space: nowrap;
}

.post_moreinfo
{
	background: #E4E4E4 url(<?php echo $ImgPath ?>vcard_half.png) no-repeat 3px 6px;
	padding: 3px 5px 3px 21px;
	color: #404040;
}
.post_moreactions
{
	margin-top: 3px;
	border-top: solid 1px #F4F4F4;
	padding-top: 2px;
	text-align: right;
}

.post_users_read
{
	margin-top: 3px;
	border-top: solid 1px #F4F4F4;
	padding-top: 2px;
	text-align: right;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	line-height: 1.1em;
	color: #606060;
}

.post_infobar
{
	border-bottom: solid 1px #D8D8D8;
	background: #F8F8C8;
	padding: 3px 5px;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
}

.post_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F2FFF2;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_gn13.png);
<?php } ?>
	padding: 5px 5px 3px;
	text-align: justify;
}
.post_container.oddcount .post_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F2F2FF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
}
.abbc div.code
{
	text-align: left;
}

.post_subject
{
	padding-bottom: 2px;
	border-bottom: solid 1px #D8D8D8;
	margin-bottom: 8px;
}
.post_body
{
}
.post_signature   /* sync with controlpanel.css:.signature_preview */
{
	clear: right;
	color: #808080;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	text-align: right;
	margin: 13px 0px 0px 25%;
	padding: 2px 0px;
}

.post_attach
{
	margin: 8px 0px 0px 0px;
	padding: 2px 0px;
}
.post_attach_head
{
	color: #808080;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-bottom: 2px;
}
.post_attach_file
{
	margin-top: 1px;
}
.post_attach_image img
{
	margin: 2px 0 2px 20px;
}
.post_attach_thumbnail img
{
	margin: 2px 0 2px 20px;
	border: solid 1px transparent;
	cursor: pointer;
}
.post_attach_thumbnail img:hover
{
	border: solid 1px darkblue;
}

.post_notes
{
	text-align: right;
	color: #808080;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-top: 8px;
	border-top: solid 1px #D8D8D8;
	padding-top: 2px;
}

.post_threadinfo .highlight,
.post_subject .highlight,
.post_body .highlight
{
	background-color: #F8F870;
}

/* -------------------- polls -------------------- */

.poll_container
{
	clear: both;
	border: solid 1px #A5BFF1;
	margin: 12px auto;
	padding: 1px;
}

.poll_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F2F2FF;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bl13.png);
<?php } ?>
	padding: 0px 5px 3px 5px;
}

.poll_question
{
	padding-top: 10px;
	padding-bottom: 2px;
	border-bottom: solid 1px #D8D8D8;
	margin-bottom: 8px;
}

.poll_options
{
}
.poll_option
{
}

.poll_results
{
	/*margin-left: -5px;*/
	padding-right: 2px;   /* because of .poll_graph,border-right:2px */
	line-height: 19px;
}
.poll_graph
{
	background: #B0E0F0;
	border-right: solid 2px #90C0D0;
	height: 18px;
	margin-top: 1px;
	margin-bottom: -19px;
}
.poll_graph.myvote
{
	background: #B0F0B0;
	border-right: solid 2px #90D090;
}
.poll_optiontext
{
	/*margin-left: 5px;*/
	margin-left: 3px;
	margin-right: 10px;
}
.poll_usersvoted
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-left: 25px;   /* incl. .poll_optiontext,margin-left */
}

.poll_status
{
	margin-top: 7px;
}

/* -------------------- post formatting -------------------- */

.abbc .quote,
.abbc .quote .quote,
.abbc .quote .quote .quote
{
	border: none;
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	color: #404040;
	margin: 2px 0 4px 0;
	padding: 0;
}
.abbc .quote .quote_inner
{
	background: url(<?php echo $ImgPath ?>quote_e90.png) no-repeat 0px 0px;
	padding: 7px 5px 5px 20px;
}
.abbc .quote .qname
{
	font-style: italic !important;
	color: #0050A0 !important;
	margin: 0 !important;
}
.abbc .quote .quote,
.abbc .quote .quote .quote
{
	color: #606060;
}

/* abbc error highlighting: unclosed open tag */
/* abbc error highlighting: unopened close tag */
.abbc .eop,
.abbc .ecl
{
	padding-bottom: 2px;
	background: url(<?php echo $ImgPath ?>spelling.png) bottom repeat-x;
}

.abbc .code
{
	padding: 0;
}
.abbc .code .lnnr   /* line number */   /* currently unused */
{
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	color: #808080;
	float: left;
	clear: left;
	width: 20px;
	padding-right: 3px;
	border-right: dotted 1px #CCCCCC;
	margin-right: 3px;
	text-align: right;
}
.abbc .code .cont   /* content */
{
	font-family: Andale Mono, monospace;
}
.abbc .code .cont.even
{
<?php if ($ie && !$_ie7) { ?>
	background: #EEEEEE;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk2.png);
<?php } ?>
}
.abbc .code .cont.odd
{
}

.abbc .code ol.nolinenos   /* hide line numbers - does not work this way */
{
	list-style-type: none;
	padding-left: 0;
}


/* -------------------- reply container -------------------- */

.reply_container
{
	border: solid 1px #F1BE6D;
	margin: 6px auto;
	padding: 1px;
}

.reply_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	padding: 3px 3px;
}

.reply_close_link
{
	float: right;
	font-size: <?php echo $UNB['Font']['smallsize'] ?>;
	margin-left: 8px;
}

textarea.reply_message
{
<?php if ($ie && !$ie7) { ?>
	width: 733px;
<?php } else { ?>
	width: 100%;
<?php } ?>
	height: 120px;
}

.reply_controls
{
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	padding: 3px 3px;
}

/* -------------------- post list actions -------------------- */

.all_post_actions_top
{
	color: #404040;
	text-align: right;
	margin-bottom: 3px;
}
.all_post_actions_top.searchresult
{
	margin-bottom: -15px;
}
.all_post_actions_bottom
{
	color: #404040;
	text-align: right;
}

/* -------------------- post editor -------------------- */

.all_editor_container
{
	clear: both;
	width: 100%;
	overflow: hidden;
}

.editor_container
{
	border: solid 1px #F1BE6D;
	margin: 1px auto 12px;
	padding: 1px;
}

.editor_caption
{
<?php if ($ie && !$_ie7) { ?>
	background: #E8E8E8;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk9.png);
<?php } ?>
	padding: 1px 3px;
}
.editor_content
{
<?php if ($ie && !$_ie7) { ?>
	background: #F4F4F4;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk4.png);
<?php } ?>
	padding: 3px 3px;
}

.editor_head
{
	padding-right: 2px;
}

.editor_head table td
{
	vertical-align: middle;
}
.editor_head table td.leftcol
{
	width: 100px;
	padding-right: 10px;
	white-space: nowrap;
}

.editor_formatting
{
	margin-top: 15px;
}
.editor_formatting_more
{
	margin-top: 5px;
}
.editor_formatting_more .smiliebutton
{
	cursor: pointer;
}

.editor_message
{
}

.editor_controls
{
	margin-top: 1px;
}

.editor_options
{
}
.editor_editoptions
{
	margin-top: 8px;
	border-top: dotted 1px #B0B0B0;
	padding-top: 8px;
}
.editor_attach
{
	margin-top: 8px;
	border-top: dotted 1px #B0B0B0;
	padding-top: 8px;
}

.editor_poll
{
	margin-top: 8px;
	border-top: dotted 1px #B0B0B0;
	padding-top: 8px;
}

input[type="button"].colourbutton,
input[type="button"].colourbutton:hover,
input[type="button"].colourbutton:focus
{
<?php if (!$ie || $ie7) { ?>
	background-image: url(<?php echo $ImgPath ?>button_color_fade.png);
	background-repeat: repeat-x;
<?php } ?>
}

a.altchar
{
	font-size: 1.3em;
	line-height: 140%;
	padding: 1px 2px 0;
<?php if ($ie && !$_ie7) { ?>
	background: #EEEEEE;
<?php } else { ?>
	background: url(<?php echo $ImgPath ?>shade_bk6.png);
<?php } ?>
}
span.altchar
{
	font-size: 1.3em;
	line-height: 140%;
}

#textlength.warning
{
	color: #FF3000;
	font-weight: bold;
}

