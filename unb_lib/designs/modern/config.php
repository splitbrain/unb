<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Design configuration file
//
// Design name: Modern
// Author:      Yves Goergen
// Last edit:   20071027

$UNB['Design']['name'][-1] = $UNB['Design']['name'][$current_design];

// Base pathnames
$p = dirname(__FILE__);
$u = $UNB['LibraryURL'] . 'designs/' . $UNB['Design']['CurrentDesign'];
$UNB['CssBasePath'] = $p . '/css/';   // used in templates, by {require-css}
$UNB['CssBaseURL'] = $u . '/css/';   // used in templates, by {require-css}
$UNB['JsBasePath'] = $p . '/js/';   // used in templates, by {require-js}
$UNB['JsBaseURL'] = $u . '/js/';   // used in templates, by {require-js}
$UNB['ImgBasePath'] = $p . '/img/';   // used in templates, by {imgpath}
$UNB['ImgBaseURL'] = $u . '/img/';   // used in templates, by {imgpath}

// Default CSS stylesheet, included in every page
$UNB['CssFile'] = $UNB['CssBaseURL'] . 'common.css.php';

// This will be used to separate forums in UnbShowPath() or the search result
$UNB['Design']['ForumSeparator'] = ' <b>&rsaquo;</b> ';
#$UNB['Design']['ForumSeparator'] = ' &nbsp;<b>&rsaquo;</b>&nbsp; ';
#$UNB['Design']['ForumSeparator'] = ', ';

// This will be used to display previous/next page links in page lists
$UNB['Design']['PrevPage'] = '<b>&lsaquo;</b>&nbsp;';
$UNB['Design']['NextPage'] = '&nbsp;<b>&rsaquo;</b>';

// Image definitions
$imgurl = $UNB['ImgBaseURL'];
$UNB['Image']['toplogo'] = 'src="' . $imgurl . 'logo_unb_dark.png" width="343" height="36"';
$UNB['Image']['icon'] = $imgurl . 'favicon.ico';

$UNB['Image']['add'] = 'src="' . $imgurl . 'add.png" width="12" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['aim'] = 'src="' . $imgurl . 'aim.png" width="11" height="14" alt=""';
$UNB['Image']['announce'] = 'src="' . $imgurl . 'announce.gif" width="12" height="12" alt=""';
$UNB['Image']['arrow_right'] = 'src="' . $imgurl . 'arrow_right.png" width="10" height="10" alt=""';
$UNB['Image']['arrow_down'] = 'src="' . $imgurl . 'arrow_down.png" width="10" height="10" alt=""';
$UNB['Image']['arrow_left'] = 'src="' . $imgurl . 'arrow_left.png" width="10" height="10" alt=""';
$UNB['Image']['arrow_up'] = 'src="' . $imgurl . 'arrow_up.png" width="10" height="10" alt=""';
$UNB['Image']['attach'] = 'src="' . $imgurl . 'attach.png" width="14" height="11" alt=""';
$UNB['Image']['back'] = 'src="' . $imgurl . 'back.png" width="13" height="11" alt=""';
$UNB['Image']['birthday'] = 'src="' . $imgurl . 'birthday.png" width="9" height="10" alt=""';
$UNB['Image']['bookmark'] = 'src="' . $imgurl . 'bookmark.png" width="12" height="12" alt=""';
$UNB['Image']['delete'] = 'src="' . $imgurl . 'delete.png" width="12" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['down'] = 'src="' . $imgurl . 'down_dark.png" width="7" height="7" alt=""';
$UNB['Image']['edit'] = 'src="' . $imgurl . 'edit.png" width="12" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['editthread'] = 'src="' . $imgurl . 'editthread.png" width="12" height="11" alt=""';
$UNB['Image']['email'] = 'src="' . $imgurl . 'email.png" width="14" height="14" alt=""';
$UNB['Image']['empty'] = 'src="' . $imgurl . 'empty.gif" alt=""';
$UNB['Image']['error'] = 'src="' . $imgurl . 'error.png" width="19" height="20" alt=""';
$UNB['Image']['female'] = 'src="' . $imgurl . 'female.png" width="9" height="11" alt=""';
$UNB['Image']['forum'] = 'src="' . $imgurl . 'forum.png" width="37" height="29" alt=""';
$UNB['Image']['forum_edit'] = 'src="' . $imgurl . 'forum_edit.gif" width="14" height="16" alt=""';
$UNB['Image']['forum_new'] = 'src="' . $imgurl . 'forum_new.png" width="37" height="29" alt=""';
$UNB['Image']['goto_post'] = 'src="' . $imgurl . 'goto_post.png" width="14" height="9" alt=""';
$UNB['Image']['help'] = 'src="' . $imgurl . 'help.png" width="10" height="11" alt=""';
$UNB['Image']['hide'] = 'src="' . $imgurl . 'hide.png" width="23" height="11" alt="" style="position:relative; top:1px;"';
$UNB['Image']['homepage'] = 'src="' . $imgurl . 'homepage.png" width="12" height="12" alt=""';
$UNB['Image']['host'] = 'src="' . $imgurl . 'host.png" width="13" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['icq'] = 'src="' . $imgurl . 'icq.png" width="16" height="15" alt=""';
$UNB['Image']['ignore'] = 'src="' . $imgurl . 'ignore.png" width="23" height="11" alt="" style="position:relative; top:1px;"';
$UNB['Image']['info'] = 'src="' . $imgurl . 'info.png" width="18" height="18" alt=""';
$UNB['Image']['jabber'] = 'src="' . $imgurl . 'jabber.png" width="10" height="15" alt=""';
$UNB['Image']['lock'] = 'src="' . $imgurl . 'lock.png" width="7" height="10" alt=""';
$UNB['Image']['male'] = 'src="' . $imgurl . 'male.png" width="9" height="11" alt=""';
$UNB['Image']['msn'] = 'src="' . $imgurl . 'msn.png" width="13" height="12" alt=""';
$UNB['Image']['nav_overview'] = 'src="' . $imgurl . 'nav_overview.png" width="16" height="16" alt="" style="vertical-align: middle;"';
$UNB['Image']['nav_search'] = 'src="' . $imgurl . 'nav_search.png" width="16" height="16" alt="" style="vertical-align: middle;"';
$UNB['Image']['nav_settings'] = 'src="' . $imgurl . 'nav_settings.png" width="16" height="16" alt="" style="vertical-align: middle;"';
$UNB['Image']['nav_users'] = 'src="' . $imgurl . 'nav_users.png" width="16" height="16" alt="" style="vertical-align: middle;"';
$UNB['Image']['nav_stat'] = 'src="' . $imgurl . 'nav_stat.png" width="16" height="16" alt="" style="vertical-align: middle;"';
$UNB['Image']['new'] = 'src="' . $imgurl . 'new.png" width="15" height="13" alt="" style="position:relative; top:1px;"';
$UNB['Image']['nowrite'] = 'src="' . $imgurl . 'nowrite.png" width="12" height="12" alt=""';
$UNB['Image']['nobookmark'] = 'src="' . $imgurl . 'nobookmark.png" width="12" height="12" alt=""';
$UNB['Image']['offline'] = 'src="' . $imgurl . 'offline.png" width="8" height="8" alt=""';
$UNB['Image']['online'] = 'src="' . $imgurl . 'online.png" width="8" height="8" alt=""';
$UNB['Image']['overview'] = 'src="' . $imgurl . 'overview.png" width="11" height="11" alt=""';
$UNB['Image']['page'] = 'src="' . $imgurl . 'page.png" width="10" height="10" alt=""';
$UNB['Image']['photo'] = 'src="' . $imgurl . 'photo.png" width="10" height="10" alt=""';
$UNB['Image']['quote'] = 'src="' . $imgurl . 'quote.png" width="14" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['rss'] = 'src="' . $imgurl . 'rss.png" width="20" height="12" alt="RSS" style="margin-bottom: -1px;"';
$UNB['Image']['search'] = 'src="' . $imgurl . 'search.png" width="11" height="11" alt=""';
$UNB['Image']['sort'] = 'src="' . $imgurl . 'sort.png" width="11" height="13" alt=""';
$UNB['Image']['split'] = 'src="' . $imgurl . 'split.png" width="8" height="10" alt=""';
$UNB['Image']['starter'] = 'src="' . $imgurl . 'starter.png" width="10" height="10" alt=""';
$UNB['Image']['stat'] = 'src="' . $imgurl . 'stat.png" width="11" height="11" alt=""';
$UNB['Image']['tap0'] = 'src="' . $imgurl . 'tap0.png" width="12" height="11" alt=""';
$UNB['Image']['tap1'] = 'src="' . $imgurl . 'tap1.png" width="12" height="11" alt=""';
$UNB['Image']['task'] = 'src="' . $imgurl . 'task.png" width="12" height="12" alt=""';
$UNB['Image']['task2'] = 'src="' . $imgurl . 'task2.png" width="12" height="12" alt=""';
$UNB['Image']['thread'] = 'src="' . $imgurl . 'thread.png" width="17" height="17" alt=""';
$UNB['Image']['thread_closed'] = 'src="' . $imgurl . 'thread_closed.png" width="17" height="17" alt=""';
$UNB['Image']['thread_closed_hot'] = 'src="' . $imgurl . 'thread_closed_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_closed_important'] = 'src="' . $imgurl . 'thread_closed_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_hot'] = 'src="' . $imgurl . 'thread_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_important'] = 'src="' . $imgurl . 'thread_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_moved'] = 'src="' . $imgurl . 'thread_moved.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new'] = 'src="' . $imgurl . 'thread_new.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_closed'] = 'src="' . $imgurl . 'thread_new_closed.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_closed_hot'] = 'src="' . $imgurl . 'thread_new_closed_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_closed_important'] = 'src="' . $imgurl . 'thread_new_closed_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_hot'] = 'src="' . $imgurl . 'thread_new_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_important'] = 'src="' . $imgurl . 'thread_new_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own'] = 'src="' . $imgurl . 'thread_new_own.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own_closed'] = 'src="' . $imgurl . 'thread_new_own_closed.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own_closed_hot'] = 'src="' . $imgurl . 'thread_new_own_closed_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own_closed_important'] = 'src="' . $imgurl . 'thread_new_own_closed_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own_hot'] = 'src="' . $imgurl . 'thread_new_own_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_new_own_important'] = 'src="' . $imgurl . 'thread_new_own_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own'] = 'src="' . $imgurl . 'thread_own.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own_closed'] = 'src="' . $imgurl . 'thread_own_closed.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own_closed_hot'] = 'src="' . $imgurl . 'thread_own_closed_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own_closed_important'] = 'src="' . $imgurl . 'thread_own_closed_important.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own_hot'] = 'src="' . $imgurl . 'thread_own_hot.png" width="17" height="17" alt=""';
$UNB['Image']['thread_own_important'] = 'src="' . $imgurl . 'thread_own_important.png" width="17" height="17" alt=""';
$UNB['Image']['unlock'] = 'src="' . $imgurl . 'unlock.png" width="12" height="10" alt=""';
$UNB['Image']['unread'] = 'src="' . $imgurl . 'unread.png" width="11" height="8" alt=""';
$UNB['Image']['up'] = 'src="' . $imgurl . 'up_dark.png" width="7" height="7" alt=""';
$UNB['Image']['user_cp'] = 'src="' . $imgurl . 'user_cp.png" width="12" height="11" alt=""';
$UNB['Image']['users'] = 'src="' . $imgurl . 'users.png" width="7" height="11" alt=""';
$UNB['Image']['votes'] = 'src="' . $imgurl . 'votes.png" width="12" height="11" alt=""';
$UNB['Image']['warning'] = 'src="' . $imgurl . 'warning.png" width="19" height="20" alt=""';
$UNB['Image']['weblink'] = 'src="' . $imgurl . 'weblink.png" width="37" height="29" alt=""';
$UNB['Image']['write'] = 'src="' . $imgurl . 'write.png" width="17" height="12" alt="" style="position:relative; top:1px;"';
$UNB['Image']['yim'] = 'src="' . $imgurl . 'yim.png" width="20" height="11" alt=""';

// Include user design definitions
@include(dirname(__FILE__) . '/config.user.php');
@include(dirname(dirname(__FILE__)) . '/config.user.php');
?>