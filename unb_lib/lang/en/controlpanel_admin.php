<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : controlpanel_admin
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20070420
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// ---------- Category names ----------
$UNB_T['cp.category.board configuration'] = 'Board configuration';
$UNB_T['cp.category.board settings'] = 'General settings';
$UNB_T['cp.category.board appearance'] = 'Board appearance';
$UNB_T['cp.category.security'] = 'Security';
$UNB_T['cp.category.plugins'] = 'Plug-ins';
$UNB_T['cp.category.more pages'] = 'More pages';

// ----- Board settings -----

$UNB_T['cp.forum title'] = 'Forum title';
$UNB_T['cp.forum title~'] = 'This title will be used in the page title and as sender of e-mails.';
$UNB_T['cp.home url'] = 'Home URL';
$UNB_T['cp.home url~'] = 'Home URL of the board installation (only the directory name, without PHP filename). This is auto-detected by the installer. You shouldn’t need to correct it.';
$UNB_T['cp.parent url'] = 'Parent URL';
$UNB_T['cp.parent url~'] = 'If set, this will be displayed as “Main page” as the first navigation line link.';
$UNB_T['cp.toplogo url'] = 'Top logo link target';
$UNB_T['cp.toplogo url~'] = 'If set, this will be the link target of the logo at the top of the page. The logo points to the forum index otherwise.';

$UNB_T['cp.database connection'] = 'Database connection';
$UNB_T['cp.db server'] = 'Database server';
$UNB_T['cp.db user'] = 'Username';
$UNB_T['cp.db pass'] = 'Password';
$UNB_T['cp.db name'] = 'Database name';
$UNB_T['cp.db prefix'] = 'Table name prefix';
$UNB_T['cp.db prefix~'] = 'This key is added to the standard table names. Use different keys to store more than one forum in a single database.';

$UNB_T['cp.smtp settings'] = 'SMTP settings';
$UNB_T['cp.smtp server'] = 'Mail server';
$UNB_T['cp.smtp sender'] = 'E-mail address';
$UNB_T['cp.smtp user'] = 'SMTP AUTH username';
$UNB_T['cp.smtp pass'] = 'SMTP AUTH password';
$UNB_T['cp.use php mail'] = 'Use PHP’s mail() function to send e-mails';
$UNB_T['cp.use php mail~'] = 'Using the mail() function doesn’t require SMTP configuration. If specified, the given e-mail address will be used as sender address. Using an SMTP connection is recommended because it’s more reliable and allows error logging.';

$UNB_T['cp.enable jabber'] = 'Enable Jabber notifications';
$UNB_T['cp.jabber settings'] = 'Jabber settings';
$UNB_T['cp.jabber server'] = 'Jabber server';
$UNB_T['cp.jabber server~'] = 'Hostname after the @ symbol, optional with “:port” (Default is port 5222), SSL is not supported';
$UNB_T['cp.jabber user'] = 'Username';
$UNB_T['cp.jabber user~'] = 'Only the login name before the @ symbol';
$UNB_T['cp.jabber pass'] = 'Password';

$UNB_T['cp.board language~'] = 'Selects the language to be used in the board. This selection can be overridden by the user’s browser preferences.';
$UNB_T['cp.board timezone~'] = 'Select the local timezone to be used for all time information in the board. This selection can be overridden by the user’s browser preferences.';

// ----- Board appearance -----

$UNB_T['cp.smilies set'] = 'Smileys set';
$UNB_T['cp.smilies set~'] = 'You can select one out of multiple smileys sets to display smileys in posts and other texts with graphics.';

$UNB_T['cp.board appearance.general'] = 'General appearance options';

$UNB_T['cp.login top'] = 'Login at the top of the page';
$UNB_T['cp.login top~'] = 'In narrower layouts it may make sense to put the login field to the bottom of the page.';
$UNB_T['cp.show online users'] = 'Show online users';
$UNB_T['cp.show online users~'] = 'These lists are located at the end of the forums overview and in each forum.';
$UNB_T['cp.foot db time'] = 'Page statistics in the footline';
$UNB_T['cp.foot db time~'] = 'Contains page generation duration and database queries count and time.';
$UNB_T['cp.gzip'] = 'Use GZip page compression';
$UNB_T['cp.gzip~'] = 'By compressing web pages for transfer to the browser, up to 90% of the network traffic can be saved. This greatly reduces download times over slow connections. Processor load can be disregarded here.';
$UNB_T['cp.gzip.off'] = 'Disabled';
$UNB_T['cp.gzip.on'] = 'Enabled';
$UNB_T['cp.gzip.auto'] = 'Automatically enabled';
$UNB_T['cp.mod_rewrite urls'] = 'Use short URLs';
$UNB_T['cp.mod_rewrite urls~'] = 'Use URLs like “/forum/3” or “/post/2345” instead of complex parameters. This option requires Apache’s mod_rewrite module and is automaticlly enabled by the setup when available.';
$UNB_T['cp.show goto forum'] = 'Show forums selection list';
$UNB_T['cp.show goto forum~'] = 'Shows a drop-down selection box with all forums to go to at the end of thread views.';
$UNB_T['cp.show search forum'] = 'Search forums/threads';
$UNB_T['cp.show search forum~'] = 'Shows a textbox at the end of threads lists or thread views to easily search in them.';
$UNB_T['cp.enable trace users'] = 'Enable trace users list';
$UNB_T['cp.enable trace users~'] = 'In this list, every user can see where other users are currently browsing the board.';
$UNB_T['cp.post preview send button'] = 'Add a Send button after post previews';
$UNB_T['cp.post preview send button~'] = 'Allows to submit posts easier after checking the preview.';
$UNB_T['cp.show last visit time'] = 'Show last visit time';
$UNB_T['cp.show last visit time~'] = 'This information can be found at the end of the forums overview page.';
$UNB_T['cp.forum tree style'] = 'Line style in the forums selection';
$UNB_T['cp.forum tree style.unicode'] = 'Unicode';
$UNB_T['cp.forum tree style.nolines'] = 'No lines';
$UNB_T['cp.forum tree style.dots'] = 'Dots';
$UNB_T['cp.forum tree style.hlines'] = 'Horizontal lines';
$UNB_T['cp.forum tree style~'] = 'Selects the appearance of the drop-down forums selection boxes. (see option “' . $UNB_T['cp.show goto forum'] . '”).';
$UNB_T['cp.display forum lastpost re'] = 'Add “Re:” to last posts’ topics in the forums list';
$UNB_T['cp.display forum lastpost re~'] = 'If the last post in a forum is a reply, the topic subject will be prepended the abbreviation “Re:” known from e-mail applications.';
$UNB_T['cp.show birthdays'] = 'Show birthdays';
$UNB_T['cp.show birthdays~'] = 'Show all users and their age at the end of the forums overview that have birthday on the current day.';
$UNB_T['cp.disable search highlighting'] = 'Disable highlighting search matches';
$UNB_T['cp.disable search highlighting~'] = 'When encountering display problems, you can disable the highlighting of search term matches.';
$UNB_T['cp.show forum rss link'] = 'Show RSS links';
$UNB_T['cp.show forum rss link~'] = 'Shows a link to the RSS newsfeed in the forums overview and threads.';
$UNB_T['cp.location link'] = 'Location map service URL';
$UNB_T['cp.location link~'] = 'Users’ locations can be linked with an internet map service. “%s” will be replaced with the given location.';

$UNB_T['cp.per page~'] = 'For the following options, a value of 0 shows all items on one page or disables the function.';
$UNB_T['cp.threads per page'] = 'Threads per page';
$UNB_T['cp.posts per page'] = 'Posts per page';
$UNB_T['cp.users per page'] = 'Users per page';
$UNB_T['cp.hot threads posts'] = '‘Hot Thread’ posts';
$UNB_T['cp.hot threads views'] = '‘Hot Thread’ views';

$UNB_T['cp.extra names'] = 'Additional user profile fields';
$UNB_T['cp.extra names~'] = 'These names will be used for additional text fields in user profiles. Enter them all separated by a “|” character.';
$UNB_T['cp.extra names.n db cols'] = 'Currently there are {n} database columns present. When you enter less fields, all additional columns and their content will be removed! More columns (up to 10) are created when necessary.';
$UNB_T['cp.extra names.n db cols.num0'] = 'Currently there is no database column present. More columns (up to 10) are created when necessary.';
$UNB_T['cp.extra names.n db cols.num1'] = 'Currently there is one database column present. When you enter less fields, all additional columns and their content will be removed! More columns (up to 10) are created when necessary.';

$UNB_T['cp.board appearance.posts'] = 'Posts';

$UNB_T['cp.new topic link in thread'] = 'Show “New topic” link in the thread view';
$UNB_T['cp.new topic link in thread~'] = '';
$UNB_T['cp.post attach inline maxsize'] = 'Maximum filesize for inline attachments';
$UNB_T['cp.post attach inline maxsize.unit'] = 'bytes';
$UNB_T['cp.post attach inline maxsize~'] = '';
$UNB_T['cp.post attach inline maxwidth'] = 'Maximum picture width for inline attachments';
$UNB_T['cp.post attach inline maxwidth.unit'] = 'pixels';
$UNB_T['cp.post attach inline maxwidth~'] = '';
$UNB_T['cp.post attach inline maxheight'] = 'Maximum picture height for inline attachments';
$UNB_T['cp.post attach inline maxheight.unit'] = 'pixels';
$UNB_T['cp.post attach inline maxheight~'] = '';
$UNB_T['cp.post show textlength'] = 'Count current text length in the post editor';
$UNB_T['cp.post show textlength~'] = '';
$UNB_T['cp.max poll options'] = 'Number of possible options for polls';
$UNB_T['cp.max poll options~'] = '';

$UNB_T['cp.board appearance.threads forums'] = 'Threads and forums';

$UNB_T['cp.own posts in threadlist'] = 'Mark threads with own posts';
$UNB_T['cp.own posts in threadlist~'] = 'Indicates threads with a special icon that contain posts of the logged in user.';
$UNB_T['cp.show bookmarked thread'] = 'Mark bookmarked threads';
$UNB_T['cp.show bookmarked thread~'] = 'Indicates threads with a special icon that are bookmarked by the logged in user.';
$UNB_T['cp.display thread startdate'] = 'Display thread start date';
$UNB_T['cp.display thread startdate~'] = 'Shows when a thread was started.';
$UNB_T['cp.advanced thread counter'] = 'Advanced counters in threads lists';
$UNB_T['cp.advanced thread counter~'] = 'Also counts the number of users that have read a thread or posted to it.';
$UNB_T['cp.count thread views'] = 'Count thread views';
$UNB_T['cp.count thread views~'] = 'Shows how often a thread was viewed.';
$UNB_T['cp.display thread lastposter'] = 'Display a thread’s last post’s author';
$UNB_T['cp.display thread lastposter~'] = 'Shows who has written the last post of a thread.';
$UNB_T['cp.count forum threads posts'] = 'Count threads and posts for forums';
$UNB_T['cp.count forum threads posts~'] = 'Shows in the forums list, how many threads and posts the forum contains.';
$UNB_T['cp.display forum lastpost'] = 'Display forum’s last post';
$UNB_T['cp.display forum lastpost~'] = 'Shows in the forums list, when the last post was added and who was the author.';

$UNB_T['cp.board appearance.users'] = 'Users list';

$UNB_T['cp.ulist regdate'] = 'Show registration date in the users list';
$UNB_T['cp.ulist regdate~'] = '';
$UNB_T['cp.ulist location'] = 'Show location in the users list';
$UNB_T['cp.ulist location~'] = '';
$UNB_T['cp.ulist posts'] = 'Show posts count in the users list';
$UNB_T['cp.ulist posts~'] = '';
$UNB_T['cp.ulist lastpost'] = 'Show last post in the users list';
$UNB_T['cp.ulist lastpost~'] = '';

$UNB_T['cp.board appearance.timings'] = 'Timings';

$UNB_T['cp.poll current days'] = '“Current polls” finds polls from the last';
$UNB_T['cp.poll current days.unit'] = 'days';
$UNB_T['cp.poll current days~'] = 'The special search “Current polls” will find all polls that have been started within this time and that have not ended yet.';
$UNB_T['cp.quote with date'] = 'Store date with quotes of posts older than';
$UNB_T['cp.quote with date.unit'] = 'days';
$UNB_T['cp.quote with date~'] = 'When a quoted post is older than this time, its original timestamp will be added to the post.';
$UNB_T['cp.no edit note grace time'] = 'No edit note when editing within';
$UNB_T['cp.no edit note grace time.unit'] = 'minutes';
$UNB_T['cp.no edit note grace time~'] = 'When a post is edited within this time after adding it and no other user has already read it, no edit note will be added to the post.';
$UNB_T['cp.moved thread note timeout'] = 'Remove notes about moved threads after';
$UNB_T['cp.moved thread note timeout.unit'] = 'days';
$UNB_T['cp.moved thread note timeout~'] = 'After this time, a dummy thread that redirects to a moved thread wil be removed again.';
$UNB_T['cp.online users reload interval'] = 'Reload list of online users every';
$UNB_T['cp.online users reload interval.unit'] = 'milliseconds';
$UNB_T['cp.online users reload interval~'] = 'The list of online users automatically reloads after this time.';
$UNB_T['cp.user online timeout'] = 'Consider users ‘logged out’ (offline) after';
$UNB_T['cp.user online timeout.unit'] = 'seconds';
$UNB_T['cp.user online timeout~'] = 'When the last activity of a user is longer than this time ago, the user will be considered ‘logged out’ (offline).';

// ----- Security -----

$UNB_T['cp.security.user accounts'] = 'User accounts';
$UNB_T['cp.security.avatars and photos'] = 'Avatars and photos';
$UNB_T['cp.security.posts and topics'] = 'Posts and topics';
$UNB_T['cp.security.advanced'] = 'Advanced settings';

$UNB_T['cp.new user validation'] = 'New users validation';
$UNB_T['cp.new user validation.disabled'] = 'Temporarily disabled';
$UNB_T['cp.new user validation.immediate'] = 'Validate immediately';
$UNB_T['cp.new user validation.email'] = 'Check e-mail address';
$UNB_T['cp.new user validation.manual'] = 'Manually by administrator';
$UNB_T['cp.new user validation~'] = 'A registration can be validated before the new user will be added to the Members group. The simplest form is to send an activation key to the user’s e-mail address to check that address. Registration of new users can also be disabled entirely.';
$UNB_T['cp.disallowed usernames'] = 'Disallowed usernames';
$UNB_T['cp.disallowed usernames~'] = 'Registration with one of these usernames or e-mail addresses is not possible. These character strings must not appear in the user’s username or e-mail address. Case is ignored. Enter all values separated by a “|” character.';
$UNB_T['cp.disallowed emails'] = 'Disallowed e-mail addresses';
$UNB_T['cp.allowed email domains'] = 'Allowed e-mail domains';
$UNB_T['cp.allowed email domains~'] = 'If this list is not empty, registration is only possible with e-mail addresses from these domains. Case is ignored. Enter all values separated by a “|” character.';
$UNB_T['cp.disallow email reuse'] = 'E-mail addresses must not be re-used';
$UNB_T['cp.disallow email reuse~'] = 'Registration with an e-mail address that is already used by another user is not possible.';
$UNB_T['cp.username minlength'] = 'Minimum length of usernames';
$UNB_T['cp.username minlength.unit'] = 'characters';
$UNB_T['cp.username minlength~'] = '';
$UNB_T['cp.username maxlength'] = 'Maximum length of usernames';
$UNB_T['cp.username maxlength.unit'] = 'characters';
$UNB_T['cp.username maxlength~'] = '';
$UNB_T['cp.usertitle maxlength'] = 'Maximum length of user titles';
$UNB_T['cp.usertitle maxlength.unit'] = 'characters';
$UNB_T['cp.usertitle maxlength~'] = '';
$UNB_T['cp.password minlength'] = 'Minimum length of passwords';
$UNB_T['cp.password minlength.unit'] = 'characters';
$UNB_T['cp.password minlength~'] = '';
$UNB_T['cp.password not username'] = 'Disallow username as password';
$UNB_T['cp.password not username~'] = 'A password must not be the same as the username. Case is ignored.';
$UNB_T['cp.password need number'] = 'Password needs numbers';
$UNB_T['cp.password need number~'] = 'A password must contain at least one number (0-9).';
$UNB_T['cp.password need special'] = 'Password needs special character';
$UNB_T['cp.password need special~'] = 'A password must contain at least one character that is no number (0-9) and no letter (A-Z, a-z). This includes i.e. punctuation marks or accented characters.';

$UNB_T['cp.avatars enabled'] = 'Enable avatars';
$UNB_T['cp.avatars enabled~'] = 'Allows users to use avatars. These small pictures will be displayed besides each of their posts to make it easier to find them. Users can upload an avatar in their settings, this file will be managed by the board.';
$UNB_T['cp.allow remote avatar'] = 'Allow remote avatars';
$UNB_T['cp.allow remote avatar~'] = 'Allows users to provide their own URL for an avatar. This way no file will be uploaded to the board. To ensure the size limitation, the current image dimensions will be stored and kept for displaying the avatar later.';
$UNB_T['cp.maximum avatar size'] = 'Maximum avatar size';
$UNB_T['cp.maximum avatar size~'] = 'When a user uploads an avatar, it will automatically be scaled down. Avatars from remote addresses cannot be scaled down. The value “0” disables the use of avatars.';
$UNB_T['cp.photos enabled'] = 'Enable user photos';
$UNB_T['cp.photos enabled~'] = 'Allows users to include a photo of themselves in their profile.';
$UNB_T['cp.maximum photo size'] = 'Maximum photo size';

$UNB_T['cp.maximum post length'] = 'Maximum post length';
$UNB_T['cp.maximum post length.unit'] = 'characters';
$UNB_T['cp.maximum post length~'] = '';
$UNB_T['cp.maximum signature length'] = 'Maximum signature length';
$UNB_T['cp.maximum signature length.unit'] = 'characters';
$UNB_T['cp.maximum signature length~'] = 'Defines the maximum allowed length for post signatures. This shouldn’t be too long because long signatures take much space next to posts. The value “0” disables the use of signatures.';
$UNB_T['cp.maximum attachment size'] = 'Maximum attachment size';
$UNB_T['cp.maximum attachment size.unit'] = 'bytes';
$UNB_T['cp.maximum attachment size~'] = 'Defines the maximum allowed file size for post attachments. This setting can be reduced by the local PHP configuration and changed by further access rules.';
$UNB_T['cp.attachment extensions'] = 'Allowed attachment extensions';
$UNB_T['cp.attachment extensions~'] = 'If this list is not empty, only post attachments with one of these filename extensions will be accepted. Case is ignored. Enter all values separated by a “|” character.';
$UNB_T['cp.minimum topic subject length'] = 'Minimum topic subject length';
$UNB_T['cp.minimum topic subject length.unit'] = 'characters';
$UNB_T['cp.minimum topic subject length~'] = 'Defines the minimum length for topic subjects. Shorter subjects will be rejected.';
$UNB_T['cp.maximum topic subject length'] = 'Maximum topic subject length';
$UNB_T['cp.maximum topic subject length.unit'] = 'characters';
$UNB_T['cp.maximum topic subject length~'] = 'Defines the maximum length for topic subjects. Longer subjects will be rejected.';
$UNB_T['cp.abbc signature no font'] = 'No font formatting in signatures';
$UNB_T['cp.abbc signature no font~'] = 'Disallows the use of font formatting tags in post signatures. This prevents amongst others changing the font size or colour.';
$UNB_T['cp.abbc signature no url'] = 'No links in signatures';
$UNB_T['cp.abbc signature no url~'] = 'Disallows the use of link tags in post signatures. When set, users cannot set links in their signatures. This is not recommended.';
$UNB_T['cp.abbc signature no img'] = 'No images in signatures';
$UNB_T['cp.abbc signature no img~'] = 'Disallows the use of image tags in post signatures. This prevents users to include very large images in their signature.';
$UNB_T['cp.abbc signature no smilies'] = 'No smileys in signatures';
$UNB_T['cp.abbc signature no smilies~'] = 'Disallows the use of smileys in post signatures. If one is found, it will not be replaced by its associated picture.';

$UNB_T['cp.no cookies'] = 'Don’t use browser cookies';
$UNB_T['cp.no cookies~'] = 'The board will not set any browser cookies.';
$UNB_T['cp.session ip netmask'] = 'IP address mask for sessions';
$UNB_T['cp.session ip netmask.unit'] = 'bits';
$UNB_T['cp.session ip netmask~'] = 'To increase the users’ security, their IP address is checked during their session. If it differs from the previous request within this address mask, the session is terminated and the user is logged out. The value 24 corresponds the address mask 255.255.255.0.';
$UNB_T['cp.use veriword'] = 'Graphical verification codes';
$UNB_T['cp.use veriword~'] = 'Use images that the user needs to type the content of into a text field to perform certain actions like registration or submitting posts. This is only required for not logged in users and serves as a protection from automated robots that fill out these forms and misuse them for other purposes. This technique is also known as <a href="' . UnbLink('http://en.wikipedia.org/wiki/Captcha', null, true, /*sid*/false, /*derefer*/ true) . '">CAPTCHA</a>. This feature requires the PHP extension <a href="' . UnbLink('http://www.boutell.com/gd/', null, true, /*sid*/false, /*derefer*/ true) . '">GD-Lib</a> and is automaticlly enabled by the setup when available.';
$UNB_T['cp.autoban flood ip'] = 'Auto-ban IPs';
$UNB_T['cp.autoban flood ip~'] = 'Automatically blocks IP addresses for a while that load pages too fast in sequence. This serves as a protection from denial of service attacks that slow down the web server a lot with many automated page requests.';
$UNB_T['cp.autoban.on more than'] = 'Block IP at more than';
$UNB_T['cp.autoban.requests in'] = 'requests in';
$UNB_T['cp.autoban.seconds'] = 'seconds';
$UNB_T['cp.admin lock'] = 'Administrative board lock';
$UNB_T['cp.admin lock~'] = 'When this lock is active, only administrators can log in and use the board. All other users and guests only see a note that the board is currently locked.';
$UNB_T['cp.admin lock message'] = 'You can optionally add your own message to the locking note. Formatting codes can be used here.';
$UNB_T['cp.read only'] = 'Read-only mode';
$UNB_T['cp.read only~'] = 'Disables any action that would change the state of the board. In read-only mode, no users can be registered, no posts can be added or edited, no forums can be edited etc. This setting can be useful when the board should remain readable during maintenance work.';
$UNB_T['cp.enable version check'] = 'Enable version check';
$UNB_T['cp.enable version check~'] = 'Checks in regular intervals if the currently installed board version is up-to-date and if important updates are available. This feature requires an internet connection to the <a href="' . UnbLink('http://newsboard.unclassified.de/', null, true, /*sid*/false, /*derefer*/ true) . '">board website</a>. In this check, no personal or security-relevant information will be transmitted.';

// ----- Plug-ins -----

$UNB_T['cp.plugins list'] = 'Installed plug-ins';
$UNB_T['cp.plugin info on x'] = 'Information on the plug-in “{x}”';
$UNB_T['cp.plugin config of x'] = 'Plug-in configuration of “{x}”';
$UNB_T['cp.plugin.config'] = 'Settings';
$UNB_T['cp.plugin.info'] = 'Info';
$UNB_T['cp.plugin.status.ok'] = 'OK';
$UNB_T['cp.plugin.status.disabled'] = 'Disabled';
$UNB_T['cp.plugin.status.wrongversion'] = 'Wrong version';
$UNB_T['cp.plugin.status.error'] = 'Error';
$UNB_T['cp.enable this plugin'] = 'Enable this plug-in';
$UNB_T['cp.enable this plugin~'] = '';

$UNB_T['cp.pluginfo.status'] = 'Status';
$UNB_T['cp.pluginfo.description'] = 'Description';
$UNB_T['cp.pluginfo.author'] = 'Author';
$UNB_T['cp.pluginfo.languages'] = 'Languages';
$UNB_T['cp.pluginfo.version'] = 'Version compatibility';
$UNB_T['cp.pluginfo.or newer'] = 'or newer';

?>