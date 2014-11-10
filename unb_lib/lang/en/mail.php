<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : mail (contains all texts mailed/sent out to users)
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20081122
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// Registration mails
$UNB_T['register.mail.validation.subject'] = 'Validation link';
$UNB_T['register.mail.validation.body'] = "Hello {rcpt-name},\n\nyou need to validate your e-mail address in order to use your new user account. Please click on the following link or copy it into the address bar of your web browser:\n\n{url}\n\nNote: Please only click on this link if you have registered on this forum, to avoid misuse.";

$UNB_T['register.mail.manual.subject'] = 'Validation request';
$UNB_T['register.mail.manual.body'] = "Hello {rcpt-name},\n\na new user has to registered to the board. Here’s a link to his profile:\n\n{url}";

$UNB_T['register.mail.newuser.subject'] = 'New user in the forum';
$UNB_T['register.mail.newuser.body'] = $UNB_T['register.mail.manual.body'];

// Password mails
$UNB_T['mail.mkpass1.subject'] = 'New password request';
$UNB_T['mail.mkpass1.body'] = "Hello {rcpt-name},\n\nyou have requested a new password for your user account. For security reasons, you must first follow this link to authenticate yourself. Then your new password will be e-mailed to you.\n\n{url}\n\nIf you didn’t request a new password, you can ignore this e-mail. At this time, your password wasn’t changed yet, of course.";

$UNB_T['mail.mkpass2.subject'] = 'New password';
$UNB_T['mail.mkpass2.body'] = "Hello {rcpt-name},\n\nyou have requested a new password for your user account.\n\nThe new password is “{password}” (without quotes) and is valid immediately.\n\nYou can now log into the Forum with your username and this password. You should then change the password in your user control panel.";

// ThreadWatch notifications
$UNB_T['mail.threadwatchnotify.subject'] = 'New post to: {subject}';
$UNB_T['mail.threadwatchnotify.body'] = "Hello {rcpt-name},\n{poster} has posted a reply in the thread “{subject}” in the forum “{forum}” you are watching.\nYou can read the posting with the following link:\n\n{url}\n\nYou won’t receive further notifications on this thread until you visited it again.\nTo unwatch this thread, uncheck the function on the given page.";

$UNB_T['mail.threadwatchnotify-jabber.subject'] = 'New post to: {subject}';
$UNB_T['mail.threadwatchnotify-jabber.body'] = "Hello {rcpt-name},\n{poster} has posted a reply in the thread “{subject}” in the forum “{forum}”:\n{url}";

// ForumWatch notifications
$UNB_T['mail.forumwatchnotify.subject'] = 'New topic in: {forum}';
$UNB_T['mail.forumwatchnotify.body'] = "Hello {rcpt-name},\n{poster} has started the topic “{subject}”{desc} in the forum “{forum}”.\nYou can read the first posting with the following link:\n\n{url}\n\nTo unwatch this forum, uncheck the function on the forum page.";

$UNB_T['mail.forumwatchnotify-jabber.subject'] = 'New topic in: {forum}';
$UNB_T['mail.forumwatchnotify-jabber.body'] = "Hello {rcpt-name},\n{poster} has started the topic “{subject}”{desc} in the forum “{forum}”:\n{url}";

// User message mails
$UNB_T['mail.usermail.subject'] = 'Message from {name}';
$UNB_T['mail.usermail.body1'] = "Hello {rcpt-name},\n{poster} has sent you the following message over the board:\n\n-----\n{msg}\n-----\n\nTo reply {poster}, you may either reply to this e-mail or write directly to {email}.\nIf you don’t want to disclose your mail address, you can also reply over this link:\n{url}";
$UNB_T['mail.usermail.body2'] = "Hello {rcpt-name},\n{poster} has sent you the following message over the board:\n\n-----\n{msg}\n-----\n\nTo reply {poster}, please use this link:\n{url}\nWARNING: Direct replies to this e-mail are delivered to the board administrator!";

?>