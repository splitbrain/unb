<?php
// Unclassified NewsBoard
// Copyright 2003-8 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : controlpanel
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20080804
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// ---------- Category names ----------
$UNB_T['cp.category.summary'] = 'Summary';
$UNB_T['cp.category.account'] = 'Account data';
$UNB_T['cp.category.user settings'] = 'User settings';
$UNB_T['cp.category.appearance'] = 'Appearance';
$UNB_T['cp.category.post options'] = 'Post options';
$UNB_T['cp.category.watched topics'] = 'Watched topics';
$UNB_T['cp.category.bookmarks'] = 'Bookmarks';
$UNB_T['cp.category.topic filter'] = 'Topic filters';

$UNB_T['cp.show profile'] = 'Show profile';

$UNB_T['cp.currently editing user x'] = 'You are currently viewing {x}’s profile.';
$UNB_T['cp.settings saved'] = 'Your settings have been saved.';

// ---------- Summary category ----------
$UNB_T['cp.summary'] = 'Summary';
$UNB_T['cp.remove user'] = 'Remove user';
$UNB_T['cp.find posts'] = 'Search';

// ---------- Account category ----------
$UNB_T['cp.account and profile'] = 'Account data and user profile';
$UNB_T['cp.username~'] = 'This name will be your login name and the author name of your posts.';
$UNB_T['cp.username~noedit'] = 'You cannot change this name.';
$UNB_T['cp.groups~'] = 'A user can be member of one or more groups to receive further rights.';
$UNB_T['cp.e-mail~'] = 'This address is not displayed publically and will be used for notifications, messages from other users and to reset your password.';
$UNB_T['cp.e-mail~need password'] = 'You must enter the current password to change this address.';
$UNB_T['cp.validated e-mail~'] = 'The following e-mail address has been validated and is used for notification messages:';
$UNB_T['cp.password~'] = 'The password is required to login to the board. You should keep it safe and not tell it anybody.';
$UNB_T['cp.password~need password'] = 'You must enter the current password to change the password.';
$UNB_T['cp.current password'] = 'Current password';
$UNB_T['cp.new password'] = 'New password';
$UNB_T['cp.repeat new password'] = 'Repeat new password';
$UNB_T['cp.user title~'] = 'This title will be displayed next to your user name with your posts.';

$UNB_T['cp.contact info'] = 'Contact information';
$UNB_T['cp.contact info~'] = 'Here you can enter all addresses that others can reach you at. Every contact type must only be used once. More lines appear after saving.';
$UNB_T['cp.contact.select'] = 'Select';

$UNB_T['cp.personal info'] = 'Personal information';
$UNB_T['cp.birthdate.day'] = 'Day';
$UNB_T['cp.birthdate.month'] = 'Month';
$UNB_T['cp.birthdate.year'] = 'Year';
$UNB_T['cp.description'] = 'Description';
$UNB_T['cp.description~'] = 'This description will be displayed in the user profile. Here you can enter personal interests or other things about you.';

$UNB_T['cp.additional info'] = 'Additional information';

// ---------- Appearance category ----------
$UNB_T['cp.design'] = 'Design';
$UNB_T['cp.design~'] = 'You can select from multiple designs to alter the board’s appearance.';
$UNB_T['cp.design preview~'] = 'Opens the board with the selected design in a new window.';
$UNB_T['cp.language~'] = 'Selects the language to be used in the board. “Automatic” uses your browser preferences and the board admin’s default to find an appropriate language.';
$UNB_T['cp.date and time'] = 'Date and time';
$UNB_T['cp.date format'] = 'Date format';
$UNB_T['cp.date format~'] = 'This controls how dates are displayed. You can specify any date format described in the PHP manual about the <a href="' . UnbLink('http://php.net/date', null, true, /*sid*/false, /*derefer*/ true) . '">date()</a> function. This does not affect time display. You should not try to add time information here, the board will do this automatically.';   // FOR TRANSLATORS: Please use a localised version of the PHP manual if one is available, otherwise the English version at: http://php.net/date
$UNB_T['cp.timezone'] = 'Timezone';
$UNB_T['cp.timezone~'] = 'Select your local timezone so that all times in this board can be displayed correctly.';
$UNB_T['cp.use dst'] = 'Use daylight savings time';
$UNB_T['cp.use dst~'] = 'Automatically uses daylight savings time offset if the server supports this.';
$UNB_T['cp.force dst'] = 'Force daylight savings time';
$UNB_T['cp.force dst~'] = 'Uses daylight savings time offset without automatic detection.';
$UNB_T['cp.current time'] = 'Current time';

$UNB_T['cp.more options'] = 'More options';
$UNB_T['cp.small avatars'] = 'Small avatars';
$UNB_T['cp.small avatars~'] = 'User avatars that are bigger than half of the allowed maximum will be displayed half their size.';
$UNB_T['cp.fast reply'] = 'Fast reply';
$UNB_T['cp.fast reply~'] = 'Uses a simple reply field in the thread view page to quickly send a reply.';
$UNB_T['cp.auto login'] = 'Automatic login';
$UNB_T['cp.auto login~'] = 'Stores a cookie containing your login data in your browser to automatically log you in again later.';
$UNB_T['cp.auto ignore'] = 'Automatic topic ignoring';
$UNB_T['cp.auto ignore~'] = 'Automatically ignores all unseen topics when marking all threads ‘read’. All topics that you haven’t read at all by then will no longer be listed in new or unread topics.';
$UNB_T['cp.hide avatars'] = 'Hide avatars';
$UNB_T['cp.hide avatars~'] = 'Doesn’t show user avatars in the thread view.';
$UNB_T['cp.hide signatures'] = 'Hide signatures';
$UNB_T['cp.hide signatures~'] = 'Doesn’t show post signatures in the thread view.';
$UNB_T['cp.hide inline image'] = 'Hide image attachments';
$UNB_T['cp.hide inline image~'] = 'Doesn’t show attached images inline with posts but gives you a download link as for all other attachments.';

// ---------- Post options category ----------
$UNB_T['cp.post signature'] = 'Post signature';
$UNB_T['cp.post signature~'] = 'This signature will be appended to each of your posts. You can refer to currently interesting websites or insert a personal slogan here. The signature shouldn’t be too long because it will take much space in the thread view then. The maximum length is {length} characters. Some formatting codes may be disabled here.';
$UNB_T['cp.current signature'] = 'Current signature';
$UNB_T['cp.no current signature'] = 'No signature set.';
$UNB_T['cp.post signature preview~'] = 'The preview only affects the signature. Pictures will not be saved.';

$UNB_T['cp.avatar'] = 'Avatar';
$UNB_T['cp.avatar~'] = 'This small picture will be displayed besides each of your posts to make it easier to find them. The picture should be small but distinctive. The maximum size is {maxwidth}&times;{maxheight} pixels and {maxsize}.';
$UNB_T['cp.avatar~tip'] = '<b>Tip:</b> If the picture was not updated after saving it, this may be because of your browser cache. Please reload the page first.';
$UNB_T['cp.no avatar set'] = 'No avatar set.';
$UNB_T['cp.upload new avatar'] = 'Upload new avatar';
$UNB_T['cp.load avatar from url'] = 'Load avatar from URL';
$UNB_T['cp.use gravatar'] = 'Use my gravatar';
$UNB_T['cp.use gravatar~'] = '(What is a <a href="' . UnbLink('http://www.gravatar.com/', null, true, /*sid*/false, /*derefer*/ true) . '">gravatar</a>?)';
$UNB_T['cp.remove avatar'] = 'Remove present avatar';
$UNB_T['cp.current avatar size'] = 'The current avatar is {width}&times;{height} pixels and {size}.';

$UNB_T['cp.user photo'] = 'User photo';
$UNB_T['cp.user photo~'] = 'This picture will be displayed in your user profile. It should be a real and preferably current photo of yourself. Other pictures are possibly unwanted here. The maximum size is {maxwidth}&times;{maxheight} pixels and {maxsize}.';
$UNB_T['cp.user photo~tip'] = '<b>Tip:</b> If the picture was not updated after saving it, this may be because of your browser cache. Please reload the page first.';
$UNB_T['cp.no photo set'] = 'No user photo set.';
$UNB_T['cp.upload new photo'] = 'Upload new photo';
$UNB_T['cp.load photo from url'] = 'Load photo from URL';
$UNB_T['cp.remove photo'] = 'Remove present photo';
$UNB_T['cp.current photo size'] = 'The current photo is {width}&times;{height} pixels and {size}.';

// ---------- Watched topics category ----------
$UNB_T['cp.watched topics settings'] = 'Watched topics settings';
$UNB_T['cp.default notification'] = 'Default notification';
$UNB_T['cp.default notification~'] = 'This notification will be enabled for each topic that you start or that you reply to.';

$UNB_T['cp.currently watched topics'] = 'Active notifications';
$UNB_T['cp.currently watched topics~'] = 'This list contains all forums and topics that you receive notifications about new topics resp. posts for.';
$UNB_T['cp.n notifications set'] = '{n} notifications set.';
$UNB_T['cp.no notifications set'] = 'No notifications set.';
$UNB_T['cp.entire forum'] = 'Entire forum';
$UNB_T['cp.notify.remove older than.1'] = 'Remove notifications older than';
$UNB_T['cp.notify.remove older than.2'] = 'days';
$UNB_T['cp.remove selected subscriptions'] = 'Remove selected notifications';

// ---------- Bookmarks category ----------
$UNB_T['cp.bookmarks'] = 'Bookmarks';
$UNB_T['cp.bookmarks~'] = 'This list contains all stored bookmarks.';
$UNB_T['cp.n bookmarks set'] = '{n} bookmarks set.';
$UNB_T['cp.no bookmarks set'] = 'No bookmarks set.';
$UNB_T['cp.bookmark.remove older than.1'] = 'Remove bookmarks older than';
$UNB_T['cp.bookmark.remove older than.2'] = 'days';
$UNB_T['cp.remove selected bookmarks'] = 'Remove selected bookmarks';

// ---------- Topic filter category ----------
$UNB_T['cp.topic filters'] = 'Topic filters';
$UNB_T['cp.topic filter~'] = 'This list contains all set topic filters.';
$UNB_T['cp.n filters set'] = '{n} filters set.';
$UNB_T['cp.no filters set'] = 'No filters set.';
$UNB_T['cp.filter.remove older than.1'] = 'Remove filters older than';
$UNB_T['cp.filter.remove older than.2'] = 'days';
$UNB_T['cp.remove selected filters'] = 'Remove selected filters';

$UNB_T['cp.timezone.-48'] /* -1200 */ = 'International Date Line (West)';
$UNB_T['cp.timezone.-44'] /* -1100 */ = 'Midway-islands, Samoa';
$UNB_T['cp.timezone.-40'] /* -1000 */ = 'Cook islands, Hawaii (HST)';
$UNB_T['cp.timezone.-38'] /* -0930 */ = 'French Polynesia (partial)';
$UNB_T['cp.timezone.-36'] /* -0900 */ = 'Alaska (AKST)';
$UNB_T['cp.timezone.-32'] /* -0800 */ = 'Los Angeles, Seattle, Tijuana, Vancouver (PST)';
$UNB_T['cp.timezone.-28'] /* -0700 */ = 'Arizona, Calgary, Chihuahua, Salt Lake City (MST)';
$UNB_T['cp.timezone.-24'] /* -0600 */ = 'Chicago, Mexico City, Central America (CST)';
$UNB_T['cp.timezone.-20'] /* -0500 */ = 'Atlanta, Lima, New York, Toronto (EST)';
$UNB_T['cp.timezone.-16'] /* -0400 */ = 'Caracas, Santiago (AST)';
$UNB_T['cp.timezone.-14'] /* -0330 */ = 'Newfoundland (NST)';
$UNB_T['cp.timezone.-12'] /* -0300 */ = 'Brasilia, Buenos Aires, Greenland';
$UNB_T['cp.timezone.-8']  /* -0200 */ = 'Mid-Atlantic';
$UNB_T['cp.timezone.-4']  /* -0100 */ = 'Azores, Cape Verd';
$UNB_T['cp.timezone.0']   /* +0000 */ = 'Casablanca, Dublin, London (WET)';
$UNB_T['cp.timezone.4']   /* +0100 */ = 'Berlin, Paris, Rome, Warsaw (CET)';
$UNB_T['cp.timezone.8']   /* +0200 */ = 'Athene, Istanboel, Jeruzalem, Cairo, Kiew (EET)';
$UNB_T['cp.timezone.12']  /* +0300 */ = 'Bagdad, Moskou, Nairobi (MSK)';
$UNB_T['cp.timezone.14']  /* +0330 */ = 'Teheran (IRT)';
$UNB_T['cp.timezone.16']  /* +0400 */ = 'Abu Dhabi';
$UNB_T['cp.timezone.18']  /* +0430 */ = 'Kabul';
$UNB_T['cp.timezone.20']  /* +0500 */ = 'Islamabad, Karachi';
$UNB_T['cp.timezone.22']  /* +0530 */ = 'New Delhi (IST)';
$UNB_T['cp.timezone.23']  /* +0545 */ = 'Kathmandu';
$UNB_T['cp.timezone.24']  /* +0600 */ = 'Novosibirsk';
$UNB_T['cp.timezone.26']  /* +0630 */ = 'Rangoon';
$UNB_T['cp.timezone.28']  /* +0700 */ = 'Bankok, Jakarta (ICT)';
$UNB_T['cp.timezone.32']  /* +0800 */ = 'Kuala Lumpur, Peking, Perth, Singapore, Taipei (CST)';
$UNB_T['cp.timezone.35']  /* +0845 */ = 'Australia (partial)';
$UNB_T['cp.timezone.36']  /* +0900 */ = 'Osaka, Tokyo, Seoul';
$UNB_T['cp.timezone.38']  /* +0930 */ = 'Adelaide, Darwin (ACST)';
$UNB_T['cp.timezone.40']  /* +1000 */ = 'Canberra, Melbourne, Sydney, Vladivostok (AEST)';
$UNB_T['cp.timezone.42']  /* +1030 */ = 'Lord Howe Island';
$UNB_T['cp.timezone.44']  /* +1100 */ = 'Solomon Islands, New-Caledonie';
$UNB_T['cp.timezone.46']  /* +1130 */ = 'Norfolk island';
$UNB_T['cp.timezone.48']  /* +1200 */ = 'Auckland, Fiji, Marshall Islands';
$UNB_T['cp.timezone.51']  /* +1245 */ = 'New Zealand (partial)';
$UNB_T['cp.timezone.52']  /* +1300 */ = 'Nuku\'alofa';
$UNB_T['cp.timezone.56']  /* +1400 */ = 'Christmas island';

// Error messages
$UNB_T['cp.error.user not deleted'] = 'User could not be deleted.';
$UNB_T['cp.error.passwords dont match'] = 'Both passwords are different.';
$UNB_T['cp.error.password too short'] = 'Your password is too short. It must be at least {n} characters.';
$UNB_T['cp.error.password is username'] = 'The password must not be equal to the user name.';
$UNB_T['cp.error.password need number'] = 'The password must contain at least a number.';
$UNB_T['cp.error.password need special'] = 'The password must contain at least a special character.';
$UNB_T['cp.error.password generic'] = 'The password is for some unspecified reason considered unsafe.';
$UNB_T['cp.error.username too short'] = 'Your username is too short. It must be at least {min} characters.';
$UNB_T['cp.error.username too long'] = 'Your username is too long. It must not be longer than {max} characters.';
$UNB_T['cp.error.user title too long'] = 'Your user title is too long. It must not be longer than {max} characters.';
$UNB_T['cp.error.invalid birthdate'] = 'Your birthdate is invalid.';
$UNB_T['cp.error.signature too long'] = 'Your signature is too long. It must not be longer than {max} characters.';
$UNB_T['cp.error.invalid password cp'] = 'Invalid password. You cannot change current e-mail address or password.';
$UNB_T['cp.error.avatar not deleted'] = 'Avatar could not be deleted.';
$UNB_T['cp.error.no dot in filename'] = 'No “.” in the filename';
$UNB_T['cp.error.invalid file ext'] = 'Invalid file extension, allowed: jpg, jpeg, gif, png';
$UNB_T['cp.error.file too big'] = 'File too big, maximum {n} bytes';
$UNB_T['cp.error.unknown file format'] = 'File format not recognised';
$UNB_T['cp.error.image too wide'] = 'Image is too wide, maximum {n} pixels';
$UNB_T['cp.error.image too high'] = 'Image is too high, maximum {n} pixels';
$UNB_T['cp.error.file too big not resized'] = 'File too big, maximum {n} bytes, scaling down failed';
$UNB_T['cp.error.image too wide not resized'] = 'Image is too wide, maximum {n} pixels, scaling down failed';
$UNB_T['cp.error.image too high not resized'] = 'Image is too high, maximum {n} pixels, scaling down failed';
$UNB_T['cp.error.unknown error'] = 'Unknown error';
$UNB_T['cp.error.invalid avatar file'] = 'Invalid avatar file';
$UNB_T['cp.error.avatar not saved'] = 'Uploaded avatar could not be saved.';
$UNB_T['cp.error.photo not deleted'] = 'Photo could not be deleted.';
$UNB_T['cp.error.invalid photo file'] = 'Invalid photo file';
$UNB_T['cp.error.photo not saved'] = 'Uploaded photo could not be saved.';
$UNB_T['cp.error.one item not saved'] = 'At least one of the fields could not be saved.';
$UNB_T['cp.error.form not complete'] = 'The form is not completed.';
$UNB_T['cp.error.message not sent'] = 'The message could not be sent.';
$UNB_T['cp.error.user has no email'] = 'The user has not specified an e-mail address or is no validated member yet.';

$UNB_T['cp.error.too many extra fields'] = 'Too many extra fields defined. Only 10 are allowed.';
$UNB_T['cp.error.db setextracols'] = 'Database error while altering the extra columns';

?>