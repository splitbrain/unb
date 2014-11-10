<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : English (en)
// Part     : post
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20070420
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

$UNB_T['post.do reply'] = 'Reply';
$UNB_T['post.do fast reply'] = 'Fast reply';
$UNB_T['post.do quote'] = 'Quote';
$UNB_T['post.do edit'] = 'Edit';
$UNB_T['post.do delete'] = 'Remove';

$UNB_T['post.in reply to post'] = 'In reply to post';
$UNB_T['post.thread starter'] = 'Thread starter';
$UNB_T['post.show profile'] = 'Show profile';
$UNB_T['post.send e-mail'] = 'Send e-mail';
$UNB_T['post.mark unread'] = 'Unread from here';
$UNB_T['post.link to this post'] = 'Link to this post';
$UNB_T['post.show ip'] = 'Show IP';
$UNB_T['post.attach~'] = 'The author has attached {n} files to this post';
$UNB_T['post.attach~.num1'] = 'The author has attached one file to this post';
$UNB_T['post.no profile'] = 'No profile available.';
$UNB_T['post.attach.open'] = 'Open file';
$UNB_T['post.attach.save~'] = 'Save file locally';
$UNB_T['post.attach.save'] = 'Save';
$UNB_T['post.attach.image'] = 'Image';
$UNB_T['post.attach.image~'] = 'Show image';
$UNB_T['post.attach.thumbnail'] = 'Thumbnail';
$UNB_T['post.edit reason'] = 'Edit reason';
$UNB_T['post.is preview'] = 'This post is a preview and was not saved yet.';
$UNB_T['post.new posts'] = 'The following posts are new.';
$UNB_T['post.read by'] = 'Read by';
$UNB_T['post.changed since last read'] = 'This post was edited after you read it';
$UNB_T['post.error.attach.no permission'] = 'You have no permission to open this file.';
$UNB_T['post.do vote'] = 'Vote';
$UNB_T['post.n previous edited'] = 'Warning: {n} previous posts have been edited!';
$UNB_T['post.n previous edited.num1'] = 'Warning: One previous post has been edited!';
$UNB_T['post.in the forum'] = 'in the forum';
$UNB_T['post.changed n times'] = 'This post was edited <b>{n}</b> times, last on {d} by {u}.';
$UNB_T['post.changed n times.num1'] = 'This post was edited on {d} by {u}.';
$UNB_T['post.changed automatically'] = 'This post was edited automatically to correct display problems.';
$UNB_T['post.downloaded n times'] = 'downloaded {n} times';
$UNB_T['post.downloaded n times.num0'] = 'never downloaded';
$UNB_T['post.downloaded n times.num1'] = 'downloaded once';
$UNB_T['post.error.attach x not found'] = 'The file “{x}” attached to this post was not found!';

$UNB_T['poll.vote details'] = '{count} votes · {percent}%';
$UNB_T['poll.vote details.num1'] = 'One vote · {percent}%';
$UNB_T['poll.n votes'] = '<b>{n}</b> votes';
$UNB_T['poll.n votes.num0'] = 'No votes yet';
$UNB_T['poll.n votes.num1'] = '<b>One</b> vote';
$UNB_T['poll.cast your vote'] = 'Select from the following options';
$UNB_T['poll.ended time'] = 'This poll has ended {time}';   // {time} is a grammar-4-date (point of time past)
$UNB_T['poll.no timelimit'] = 'This poll has no time limit';
$UNB_T['poll.ends time'] = 'This poll will end {time}, on {abstime}';   // {time} is a grammar-3-date (point of time future), {abstime} is an absolute date/time
$UNB_T['poll.users have voted'] = 'These users have already voted';
$UNB_T['poll.show results'] = 'Show results';
$UNB_T['poll.cancel vote'] = 'Cancel my vote';
$UNB_T['poll.back to voting'] = 'Back to voting';
$UNB_T['poll.show users'] = 'Show users';

// Error messages
$UNB_T['post.error.no text'] = 'No text entered.';
$UNB_T['post.error.too long'] = 'The post is too long. There are up to {max} characters allowed.';
$UNB_T['post.error.subject too short'] = 'The subject is too short. There are {min} characters required.';
$UNB_T['post.error.subject too long'] = 'The subject is too long. There are up to {max} characters allowed.';
$UNB_T['post.error.abbc syntax error'] = 'Error in the use of formatting codes.';
$UNB_T['post.error.invalid poll timeout'] = 'The poll time limit is invalid.';
$UNB_T['post.error.too few poll options'] = 'You need to specify at least two replies for a poll.';
$UNB_T['post.error.guests need name'] = 'Guests need to enter an author name.';
$UNB_T['post.error.poll.no question'] = 'Polls require a question.';
$UNB_T['post.error.invalid attach'] = 'Invalid attachment file.';
$UNB_T['post.error.thread closed'] = 'This thread is closed, no further posts can be added..';
$UNB_T['post.error.poll.not allowed'] = 'You’re not allowed to create polls.';
$UNB_T['post.error.no subject'] = 'New threads require a subject.';
$UNB_T['post.error.thread not created'] = 'Thread could not be created.';
$UNB_T['post.error.post not created'] = 'Post could not be created.';
$UNB_T['post.error.attach not saved'] = 'The uploaded attachment file could not be saved.';
$UNB_T['post.error.poll not created'] = 'Poll could not be created.';
$UNB_T['post.error.post not deleted2'] = 'The post could not be removed again.';
$UNB_T['post.error.thread not deleted2'] = 'The thread could not be removed again.';
$UNB_T['post.error.post not deleted'] = 'Post could not be removed.';
$UNB_T['post.error.poll details not saved'] = 'Poll details could not be saved.';
$UNB_T['post.error.poll.change reply n'] = 'The {n}. reply of the poll could not be updated.';
$UNB_T['post.error.poll.create reply n'] = 'The {n}. reply of the poll could not be created.';
$UNB_T['post.error.poll not deleted'] = 'Poll could not be removed.';
$UNB_T['post.error.votes not deleted'] = 'Some votes of this poll could not be removed.';
$UNB_T['post.error.access denied delete poll'] = 'Access denied: Remove poll';
$UNB_T['post.error.announce not saved'] = 'Announcement could not be saved.';
$UNB_T['post.error.announce not deleted'] = 'Announcement could not be removed.';
$UNB_T['post.error.invalid thread for post'] = 'You cannot add posts here or the post was not found.';

?>