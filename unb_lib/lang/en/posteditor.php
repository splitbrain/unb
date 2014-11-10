<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : posteditor
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20081122
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

$mac = $UNB['Client']['os_class'] == 'mac';

$UNB_T['pe.compose post'] = 'Compose post';
$UNB_T['pe.compose announce'] = 'Compose announcement';
$UNB_T['pe.edit post'] = 'Edit post';
$UNB_T['pe.edit announce'] = 'Edit announcement';

$UNB_T['pe.optional'] = '(optional)';

$UNB_T['pe.format.bold'] = 'B';
$UNB_T['pe.format.bold.tip'] = 'Bold font';
$UNB_T['pe.format.italic'] = 'I';
$UNB_T['pe.format.italic.tip'] = 'Italic font';
$UNB_T['pe.format.underline'] = 'U';
$UNB_T['pe.format.underline.tip'] = 'Underline';
$UNB_T['pe.format.linethrough'] = 'S';
$UNB_T['pe.format.linethrough.tip'] = 'Line through';
$UNB_T['pe.format.monospace'] = 'M';
$UNB_T['pe.format.monospace.tip'] = 'Fixed width font';

$UNB_T['pe.format.quote'] = 'Quote';
$UNB_T['pe.format.quote.tip'] = 'Insert a quote (with Shift key: interrupt quote)';
$UNB_T['pe.format.link'] = 'Link';
$UNB_T['pe.format.link.tip'] = 'Insert a link (with Shift key: Move text cursor to target)';
$UNB_T['pe.format.image'] = 'Image';
$UNB_T['pe.format.image.tip'] = 'Insert an image';
$UNB_T['pe.format.code'] = 'Code';
$UNB_T['pe.format.code.tip'] = 'Insert a code block';

$UNB_T['pe.colour.red'] = 'Red';
$UNB_T['pe.colour.orange'] = 'Orange';
$UNB_T['pe.colour.green'] = 'Green';
$UNB_T['pe.colour.lightblue'] = 'Light blue';
$UNB_T['pe.colour.blue'] = 'Blue';
$UNB_T['pe.colour.violet'] = 'Violet';
$UNB_T['pe.colour.grey'] = 'Grey';

$UNB_T['pe.button.smilies'] = 'Smileys';
$UNB_T['pe.formatting help'] = 'Help';

$AltKey = $mac ? 'Ctrl' : 'Alt';
$UNB_T['pe.save post'] = 'Save post';
$UNB_T['pe.save post.key'] = 's';
$UNB_T['pe.save post.tip'] = $AltKey . '+S or Ctrl+Enter';
$UNB_T['pe.save announce'] = 'Save announcement';
$UNB_T['pe.save announce.key'] = 's';
$UNB_T['pe.save announce.tip'] = $AltKey . '+S or Ctrl+Enter';
$UNB_T['pe.reply'] = 'Post reply';
$UNB_T['pe.reply.key'] = 's';
$UNB_T['pe.reply.tip'] = $AltKey . '+S or Ctrl+Enter';
$UNB_T['pe.new announce'] = 'Create new announcement';
$UNB_T['pe.new announce.key'] = 's';
$UNB_T['pe.new announce.tip'] = $AltKey . '+S or Ctrl+Enter';
$UNB_T['pe.new topic'] = 'Create new topic';
$UNB_T['pe.new topic.key'] = 's';
$UNB_T['pe.new topic.tip'] = $AltKey . '+S or Ctrl+Enter';
$UNB_T['pe.preview'] = 'Preview';
$UNB_T['pe.preview.key'] = 'p';
$UNB_T['pe.preview.tip'] = $AltKey . '+P';

$UNB_T['pe.close'] = 'Close';
$UNB_T['pe.larger'] = 'Larger +';
$UNB_T['pe.smaller'] = 'Smaller –';
$UNB_T['pe.text length'] = 'Text length';
$UNB_T['pe.add poll'] = 'Add poll';
$UNB_T['pe.remove poll'] = 'Remove poll';
$UNB_T['pe.post options'] = 'Post options';
$UNB_T['pe.notify via'] = 'Notify via';
$UNB_T['pe.you watch this by x'] = '(You are already watching this thread by {x})';
$UNB_T['pe.no smilies'] = 'Disable smileys';
$UNB_T['pe.no special syntax'] = 'Disable simple formatting syntax';
$UNB_T['pe.thread is'] = 'Thread is';
$UNB_T['pe.remove attach'] = 'Remove file';
$UNB_T['pe.attach file'] = 'Attach file';
$UNB_T['pe.select file on save'] = 'You should first select the file when finally sending the post, because it cannot be saved otherwise.';
$UNB_T['pe.announce.important'] = 'Important announcement';
$UNB_T['pe.announce.recursive'] = 'Also show in subforums';
$UNB_T['pe.announce.show in threads'] = 'Also show in threads';
$UNB_T['pe.announce.display to'] = 'Display to';
$UNB_T['pe.announce.to.all'] = 'All users';
$UNB_T['pe.announce.to.guests'] = 'Guests';
$UNB_T['pe.announce.to.members'] = 'Members';
$UNB_T['pe.announce.to.moderators'] = 'Moderators';
$UNB_T['pe.announce.delete'] = 'Remove announcement';
$UNB_T['pe.post.delete'] = 'Remove post';
$UNB_T['pe.poll.question'] = 'Question';
$UNB_T['pe.poll.replies+sort'] = 'Here you can enter the replies, users can choose from, and their order.';
$UNB_T['pe.poll.replies'] = 'Here you can enter the replies, users can choose from.';
$UNB_T['pe.poll.timeout'] = 'After what time shall the poll end?';
$UNB_T['pe.poll.timeout.hours'] = 'hours';
$UNB_T['pe.poll.timeout.days'] = 'days';
$UNB_T['pe.poll.timeout~'] = '0 is unlimited time';
$UNB_T['pe.last posts in thread'] = 'The last posts in this thread &nbsp; <small>(newest first, maximum 10 posts)</small>';

$UNB_T['pe.no edit note'] = 'Don’t add an edit note';
$UNB_T['pe.remove edit note'] = 'Remove edit note';

$UNB_T['pe.quick reply to this post'] = 'Reply to this post';

$UNB_T['pe.alt chars~'] = 'No alternative characters available for your last input.';
$UNB_T['pe.shorten quote'] = 'Please shorten the quotation to the essential to improve readability.';
$UNB_T['pe.guest posting'] = 'You are not logged in and writing this post as a guest.';
$UNB_T['pe.warn edit other users post'] = 'You are editing another user’s post.';

?>