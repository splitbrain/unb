<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// common_post.inc.php
// Common Library: HTML output functions for posts and announcements

if (!defined('UNB_RUNNING')) die('Not a UNB environment in ' . basename(__FILE__));

// Check if a post attachment file may be displayed inline as an image
//
// in filename = (string) real filename on disk
// in userfilename = (string) original filename
//
function UnbDisplayPostImage($filename, $userfilename)
{
	global $UNB;

	if ($UNB['LoginUserID'] > 0 && $UNB['LoginUser']->GetFlags() & UNB_USER_HIDEINLINEIMGS)
		return false;   // user doesn't want to see inline post images

	$x = strrpos($userfilename, '.');
	if ($x === false) return false;   // filename needs to contain a . for analysis and renaming
	$ext = substr($userfilename, $x + 1);
	switch (strtolower($ext))
	{
		case 'jpg':
		case 'jpeg':
		case 'gif':
		case 'png':
			// get the size in pixels
			if (filesize($filename) > rc('post_attach_inline_maxsize')) return false;   // file too long
			$a = GetImageSize($filename);
			if ($a === false) return false;   // not a valid image file
			if ($a[0] > rc('post_attach_inline_maxwidth')) return false;    // too wide
			if ($a[1] > rc('post_attach_inline_maxheight')) return false;    // too high

			// image has passed all tests, and may now be displayed
			return $a;
	}
	return false;
}

// Show a post, for thread.php (Thread view), post.php (Post preview and Latest post review) and search.php (Search results)
//
// Expects the <table> framework already to be started and to be closed after last post
// Expects UnbCountUserPosts() to be called before showing posts
//
// in/out tpitem = (array) Template Parameter Item. Stores all data used for displaying the post
// in post = IPost object set to the post to be shown
// in style = (int) 0|1: alternating css class for background
// in page = (int) currently displayed page number
// in writeaccess = (bool) show quote/reply controls
// in preview = (bool) preview mode? -> no edit/reply... controls
// in show_thread = (bool) search mode (display thread link)
// in update_mark = (bool) display 'updated' marker: this post has beed edited after read by the user
// in hightlight = array of words to highlight, from search i.e.
//
function UnbShowPost(&$tpitem, &$post, &$thread, $style = 0, $page = 1, $writeaccess = true, $preview = false, $show_thread = false, $update_mark = false, $highlight = false)
{
	global $ABBC, $UNB, $UNB_T;

	// Clean parameters
	$style = intval($style);
	$page = intval($page);

	$user = new IUser;
	$threadid = $thread->GetID();

	$tpitem['id'] = $post->GetID();

	UnbRequireTxt('post');

	// ----- USER NAME -----
	$user->Reset();
	if ($post->GetUser() > 0)
	{
		if ($user->Load($post->GetUser()))
		{
			$tpitem['authorName'] = t2h($user->GetName());
			$tpitem['authorOnlineImg'] = UnbGetUserOnlineImg($user->GetOnline());
			if ($user->GetGender() != '')
				$tpitem['authorGenderImg'] = UnbGetGenderImage($user->GetGender());
			if ($post->GetUser() == $thread->GetUser())
				$tpitem['authorThreadStarter'] = true;
		}
		else
			$tpitem['authorName'] = '(' . t2h($post->GetUser()) . ')';
	}
	else
		$tpitem['authorName'] = t2h($post->GetUserName());

	// ----- SUBJECT -----
	if ($post->GetSubject() != '')
	{
		$html = $post->GetSubject();
		UnbCallHook('post.subject', $html);
		if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
		{
			$html = preg_replace('/(' . join('|', array_map('regsafe', $highlight)) . ')/i', "\x01\$1\x02", $html);
		}
		$html = t2h($html, true, true, false, 2);
		if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
		{
			$html = str_replace("\x01", '<span class="highlight">', $html);
			$html = str_replace("\x02", '</span>', $html);
		}
		$tpitem['subject'] = $html;
	}

	// ----- DATE/TIME -----
	if ($update_mark) $tpitem['editedAfterViewed'] = true;
	$tpitem['date'] = UnbFriendlyDate($post->GetDate(), 1, 3);

	// Copy link to this post into clipboard. Only available in a thread view
	if ($post->GetID())
	{
		$tpitem['hereLink'] = UnbLink(
			'@thread',
			array(
				'postid' => $post->GetID()/*,
				'#' => 'p' . $post->GetID()*/),   // page scrolling is done via JavaScript; produces nicer URL
			true);
	}

	if ($UNB['ThisPage'] == '@thread')
	{
		if ($UNB['LoginUserID'])
		{
			$newDate = $post->GetDate() - 1;
			// Special timestamp for the first post of a thread
			if ($page == 1 && $post->GetDate() == $thread->GetDate() && $post->GetUser() == $thread->GetUser())
				$newDate = 0;

			$tpitem['unreadLink'] = UnbLink(
					'@main',
					array(
						'id' => $thread->GetForum(),
						'threadunread' => $thread->GetID(),
						'threadunread_time' => $newDate,
						'key' => UnbUrlGetKey()),
					true);
		}
	}

	// ----- CONTACT ICONS -----
	if ($user->GetEMail() != '' && UnbCheckRights('sendemail'))
	{
		$a = '<img ' . $UNB['Image']['email'] . ' title="' . $UNB_T['e-mail'] . '" /> ' . $UNB_T['e-mail'];
		$tpitem['emailLink'] = UnbLink('@cp', 'id=' . $user->GetID() . '&action=email', true);
	}

	// ----- PREVIEW | POST ACTIONS -----
	if ($preview)
	{
		$tpitem['isPreview'] = true;
	}
	else
	{
		if (UnbCheckRights('showip', $thread->GetForum(), $thread->GetID()))
		{
			$tpitem['ipLink'] = UnbLink('@showip', 'id=' . $post->GetID(), true);
		}
		$isLastPost = $thread->GetLastPostDate() == $post->GetDate();
		if (UnbCheckRights('removepost', $thread->GetForum(), $thread->GetID(), $user->GetID(), $post->GetDate(), $isLastPost) &&
		    $writeaccess &&
		    (!$thread->IsClosed() || UnbCheckRights('closethread')))
		{
			$tpitem['deleteLink'] =
				t2i(
					'javascript:UnbGoDelete("' .
					UnbLink(
						'@post',
						array(
							'id' => $post->GetID(),
							'page' => $page,
							'delete' => 'yes',
							'key' => UnbUrlGetKey())) .
					'")');
		}
		if (UnbCheckRights('editpost', $thread->GetForum(), $thread->GetID(), $user->GetID(), $post->GetDate()) &&
		    $writeaccess &&
		    (!$thread->IsClosed() || UnbCheckRights('closethread')))
		{
			$tpitem['editLink'] = UnbLink('@post', 'id=' . $post->GetID() . '&page=' . $page, true);
		}
		if (UnbCheckRights('writeforum', $thread->GetForum()) &&
		    $writeaccess &&
		    (!$thread->IsClosed() || UnbCheckRights('closethread')))
		{
			$tpitem['quoteLink'] = UnbLink('@post', 'thread=' . $threadid . '&quote=' . $post->GetID(), true);
		}
		if (UnbCheckRights('writeforum', $thread->GetForum()) &&
		    $writeaccess &&
		    (!$thread->IsClosed() || UnbCheckRights('closethread')))
		{
			$tpitem['replyLink'] = UnbLink('@post', 'thread=' . $threadid . '&replyto=' . $post->GetID(), true);
			$tpitem['useFastReply'] = ($UNB['LoginUser']->GetFlags() & UNB_USER_FASTREPLY) ? 1 : 0;
		}
	}

	// ----- AVATAR -----
	if ($post->GetUser() > 0 && rc('avatars_enabled') && !$UNB['TextOnly'] && (!$UNB['LoginUserID'] || !($UNB['LoginUser']->GetFlags() & UNB_USER_HIDEAVATARS)))
	{
		if ($user->Load($post->GetUser()))
		{
			// show avatar if set
			if ($user->GetAvatar() != '')
			{
				$tpitem['avatarUrl'] = t2i(UnbAvatarUrl($user));
				$tpitem['avatarSize'] = UnbAvatarSize($user);
			}
		}
	}

	// ----- USER INFO -----
	if ($post->GetUser() > 0)
	{
		if ($user->Load($post->GetUser()))
		{
			$tpitem['authorStatus'] = UnbGetUserStatusText($user->GetID(), '%s', true, false);
			$tpitem['authorTitle'] = t2h($user->GetTitle());
			$tpitem['authorMemberSince'] = UnbFriendlyDate($user->GetRegDate(), 2, 1, true, 1);

			$tpitem['authorPostCount'] = '';
			$posts = UnbGetPostsByUser($user->GetID());
			if ($posts > 0)
				$tpitem['authorPostCount'] .= '<a href="' .
					UnbLink(
						'@search',
						array(
							'nodef' => 1,
							'Query' => $user->GetID(),
							'ResultView' => 2,
							'InUser' => 1,
							'Sort' => 2),
						true) .
					'">';
			$tpitem['authorPostCount'] .= $posts;
			if ($posts > 0)
				$tpitem['authorPostCount'] .= '</a> ' . UteTranslateNum('posts', $posts);

			$tpitem['authorLocation'] = t2h($user->GetLocation());
			$tpitem['profileLink'] = UnbLink('@cp', 'id=' . $user->GetID(), true);

			$groups = UnbGetUserGroupNames($user->GetID());
			if (is_array($groups)) $tpitem['authorGroups'] = t2h(join(', ', $groups));
		}
	}
	elseif ($post->GetUser() == 0)
	{
		$tpitem['authorStatus'] = UnbGetUserStatusText(0, '%s', true, true);
	}
	elseif ($post->GetUser() == -1)
	{
		$tpitem['authorStatus'] = UnbGetUserStatusText(-1, '%s', true, true);
	}

	if ($show_thread)
	{
		// this is a search result list, display forum/thread information with the post
		$forumid = $thread->GetForum();
		$forum = new IForum($forumid);

		$html = $thread->GetSubject();
		if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
		{
			$html = preg_replace('/(' . join('|', array_map('regsafe', $highlight)) . ')/i', "\x01\$1\x02", $html);
		}
		$html = t2h($html, true, true, false, 2);
		if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
		{
			$html = str_replace("\x01", '<span class="highlight">', $html);
			$html = str_replace("\x02", '</span>', $html);
		}
		$tpitem['threadLink'] = UnbLink('@thread', 'id=' . $threadid, true);
		$tpitem['threadSubject'] = $html;

		if ($thread->GetDesc())
		{
			$html = $thread->GetDesc();
			if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
			{
				$html = preg_replace('/(' . join('|', array_map('regsafe', $highlight)) . ')/i', "\x01\$1\x02", $html);
			}
			$html = t2h($html, true, true, false, 2);
			if (is_array($highlight) && count($highlight) > 0 && !rc('disable_search_highlighting'))
			{
				$html = str_replace("\x01", '<span class="highlight">', $html);
				$html = str_replace("\x02", '</span>', $html);
			}
			$tpitem['threadDescription'] = $html;
		}

		$f = '';
		do
		{
			if ($f != '') $f = $UNB['Design']['ForumSeparator'] . $f;
			$f = '<a href="' . UnbLink('@main', 'id=' . $forum->GetID(), true) . '">' . t2h($forum->GetName()) . '</a>' . $f;
		}
		while ($forum->Load($forum->GetParent()));
		$tpitem['forumLinks'] = $f;
	}

	// ----- POST MESSAGE, SIGNATURE -----

	$subsets0 = $ABBC['Config']['subsets'];
	if ($post->GetOptions() & UNB_POST_NOSMILIES || $UNB['TextOnly']) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;
	if ($post->GetOptions() & UNB_POST_NOSPECIALABBC) $ABBC['Config']['subsets'] &= ~ABBC_SPECIAL;
	$html = $post->GetMsg();

	$a = array(
		'user' => &$user,
		'post' => &$post,
		'thread' => &$thread,
		'message' => &$html);
	UnbCallHook('post.preparse', $a);

	// store word highlighting in the ABBC configuration array
	if (!rc('disable_search_highlighting')) $ABBC['HighlightWords'] = $highlight;

	$html = AbbcProc($html);

	$ABBC['HighlightWords'] = null;

	$a = array(
		'user' => &$user,
		'post' => &$post,
		'thread' => &$thread,
		'message' => &$html);
	UnbCallHook('post.postparse', $a);

	$tpitem['postBody'] = $html;
	$ABBC['Config']['subsets'] = $subsets0;

	if ($post->GetUser() > 0 && $user->GetSignature() != '' && (!$UNB['LoginUserID'] || !($UNB['LoginUser']->GetFlags() & UNB_USER_HIDESIGS)))
	{
		// limit ABBC subset for signatures
		$subsets0 = $ABBC['Config']['subsets'];
		$ABBC['Config']['subsets'] &= ~(ABBC_CODE | ABBC_QUOTE | ABBC_LIST);   // no code/quotes/lists at all
		if (rc('abbc_sig_no_font')) $ABBC['Config']['subsets'] &= ~ABBC_FONT;
		if (rc('abbc_sig_no_url')) $ABBC['Config']['subsets'] &= ~ABBC_URL;
		if (rc('abbc_sig_no_img')) $ABBC['Config']['subsets'] &= ~ABBC_IMG;
		if (rc('abbc_sig_no_smilies')) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;
		if ($UNB['TextOnly']) $ABBC['Config']['subsets'] &= ~ABBC_SMILIES;   // no smilies for text-only mode

		$html = $user->GetSignature();

		$a = array(
			'user' => &$user,
			'post' => &$post,
			'thread' => &$thread,
			'signature' => &$html);
		UnbCallHook('post.signature.preparse', $a);

		$x = $ABBC['Config']['output_div'];
		$ABBC['Config']['output_div'] = 0;
		$html = AbbcProc($html);
		$ABBC['Config']['output_div'] = $x;

		$a = array(
			'user' => &$user,
			'post' => &$post,
			'thread' => &$thread,
			'signature' => &$html);
		UnbCallHook('post.signature.postparse', $a);

		$tpitem['signature'] = $html;

		// restore default subset
		$ABBC['Config']['subsets'] = $subsets0;
	}

	// ----- EDIT NOTE, ATTACHMENTS -----

	if ($post->GetEditCount() || $post->GetSpamRating() || $post->GetAttachFile() != '')
	{
		if ($post->GetEditCount())
		{
			if ($user->Load($post->GetEditUser()))
				$username = UnbMakeUserLink($user->GetID(), $user->GetName());
			else
				$username = $UNB_T['unknown user'];

			$tpitem['editReason'] = t2h($post->GetEditReason());

			$tpitem['editNote'] = str_replace(
				array('{n}', '{d}', '{u}'),
				array(
					$post->GetEditCount(),
					UnbFriendlyDate($post->GetEditDate(), 0, 3),
					$username),
				UteTranslateNum('post.changed n times', $post->GetEditCount()));
		}

		if ($post->GetSpamRating())
		{
			$tpitem['spamwarning'] = $UNB_T['post.changed automatically'];
		}

		$tpitem['attachlist'] = array();
		$attach = array();
		if ($post->GetAttachFile() != '')
		{
			if (file_exists($UNB['AttachPath'] . $post->GetAttachFile()))
			{
				if (UnbCheckRights('downloadattach', $thread->GetForum(), $thread->GetID()))
				{
					// get type of attachment
					if ($size = UnbDisplayPostImage($UNB['AttachPath'] . $post->GetAttachFile(), $post->GetAttachFileName()))
					{
						// display the image
						$attach['image'] = true;
						$attach['inlineImage'] = true;
						$attach['filename'] = t2h($post->GetAttachFileName());
						$attach['linkOpen'] = UnbLink('@thread', 'download=' . $post->GetID() . '&inline=1&key=' . UnbUrlGetKey(), true);
						$attach['linkSave'] = UnbLink('@thread', 'download=' . $post->GetID() . '&key=' . UnbUrlGetKey(), true);
						$attach['size'] = format_number(filesize($UNB['AttachPath'] . $post->GetAttachFile()), 1, 1024, ' ') . $UNB_T['bytes'];
						$attach['imageSize'] = $size[3];
					}
					else
					{
						// show link for download
						$attach['filename'] = t2h($post->GetAttachFileName());
						$attach['linkOpen'] = UnbLink('@thread', 'download=' . $post->GetID() . '&inline=1&key=' . UnbUrlGetKey(), true);
						$attach['linkSave'] = UnbLink('@thread', 'download=' . $post->GetID() . '&key=' . UnbUrlGetKey(), true);
						$attach['size'] = format_number(filesize($UNB['AttachPath'] . $post->GetAttachFile()), 1, 1024, ' ') . $UNB_T['bytes'];
						$attach['views'] = str_replace('{n}', $post->GetAttachDLCount(), UteTranslateNum('post.downloaded n times', $post->GetAttachDLCount()));
					}
				}
				else
				{
					$attach['noopen'] = true;
					$attach['filename'] = t2h($post->GetAttachFileName());
					$attach['size'] = format_number(filesize($UNB['AttachPath'] . $post->GetAttachFile()), 1, 1024, ' ') . $UNB_T['bytes'];
					$attach['views'] = str_replace('{n}', $post->GetAttachDLCount(), UteTranslateNum('post.downloaded n times', $post->GetAttachDLCount()));
				}
			}
			else
			{
				$attach['error'] = str_replace('{x}', t2h($post->GetAttachFileName()), $UNB_T['post.error.attach x not found']);
			}
			$tpitem['attachlist'][] = $attach;
		}
	}
}

// Show an announcement
// For main.inc (Forum list), post.inc (Announcement preview) and thread.inc (inside threads)
//
// Expects the <table> framework already to be started and to be closed after last announcement
//
// in announce = IAnnounce object set to the announcement to be shown
// in preview = (bool) preview mode? -> no read/unread... controls
// in id = (int) > 0 if the announcement is displayed in a forum | < 0 is a thread
// in page = (int) page number in thread
// in readonly = (bool) don't show read/add/showall links (for display on search results pages)
//
function UnbShowAnnounce(&$announce, $preview = false, $id = 0, $page = 0, $readonly = false)
{
	global $UNB, $UNB_T;
	$out = array();

	// Clean parameters
	$id = intval($id);
	$page = intval($page);

	$forumid = $announce->GetForum();
	$user = new IUser($announce->GetUser());

	$out['important'] = $announce->GetOptions() & UNB_ANN_IMPORTANT;
	$out['date'] = UnbFriendlyDate($announce->GetDate(), 1, 3);
	$out['author'] = UnbMakeUserLink($user->GetID(), $user->GetName());
	$out['subject'] = t2h($announce->GetSubject());

	switch ($announce->GetOptAccess())
	{
		case UNB_ANN_FOR_GUESTS:
			$out['access'] = $UNB_T['announcement.only to guests'];
			break;
		case UNB_ANN_FOR_USERS:
			$out['access'] = $UNB_T['announcement.only to members'];
			break;
		case UNB_ANN_FOR_MODS:
			$out['access'] = $UNB_T['announcement.only to moderators'];
			break;
	}

	if ($preview)
	{
		$out['isPreview'] = true;
	}
	elseif (!$readonly)
	{
		if (UnbCheckRights('editannounce', $forumid))
		{
			$out['editLink'] = UnbLink('@post', 'announce=' . $announce->GetID(), true);
		}
		if ($UNB['LoginUserID'])
		{
			if (!$announce->IsRead())
				if ($id > 0)
					$out['readLink'] = '<a href="' . UnbLink('@this', 'id=' . $id . '&announceread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.mark read'] . '</a>';
				else
					$out['readLink'] = '<a href="' . UnbLink('@this', 'id=' . -$id . '&page=' . $page . '&nocount=1&announceread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.mark read'] . '</a>';
			else
				if ($id > 0)
					$out['readLink'] = '<a href="' . UnbLink('@this', 'id=' . $id . '&announceunread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.mark unread'] . '</a>';
				else
					$out['readLink'] = '<a href="' . UnbLink('@this', 'id=' . -$id . '&page=' . $page . '&nocount=1&announceunread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.mark unread'] . '</a>';
		}
		if (UnbCheckRights('editannounce', $announce->GetForum()))
		{
			if ($id > 0)
				$out['unreadForAllLink'] = '<a href="' . UnbLink('@this', 'id=' . $id . '&announceallunread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.unread for all'] . '</a>';
			else
				$out['unreadForAllLink'] = '<a href="' . UnbLink('@this', 'id=' . -$id . '&page=' . $page . '&nocount=1&announceallunread=' . $announce->GetID() . '&key=' . UnbUrlGetKey(), true) . '">' . $UNB_T['announcement.unread for all'] . '</a>';

			$out['readUsersCount'] = $announce->ReadCount();
		}
	}

	$html = $announce->GetMsg();

	$a = array(
		'announce' => &$announce,
		'message' => &$html);
	UnbCallHook('announce.preparse', $a);

	$html = AbbcProc($html);

	$a = array(
		'announce' => &$announce,
		'message' => &$html);
	UnbCallHook('announce.postparse', $a);

	$out['message'] = $html;

	return $out;
}

?>