<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : mail (contains all texts mailed/sent out to users)
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20081122
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// Registration mails
$UNB_T['register.mail.validation.subject'] = 'Bestätigungslink';
$UNB_T['register.mail.validation.body'] = "Hallo {rcpt-name},\n\num deinen Benutzeraccount im Forum verwenden zu können, muss erst deine E-Mail-Adresse geprüft werden. Dazu klickst du entweder auf folgenden Link oder kopierst ihn in die Adresszeile deines Webbrowsers:\n\n{url}\n\nHinweis: Bitte klicke nur auf diesen Link, wenn du auch die Registrierung in diesem Forum durchgeführt hast, um Missbrauch zu vermeiden.";

$UNB_T['register.mail.manual.subject'] = 'Registrierungs-Anforderung';
$UNB_T['register.mail.manual.body'] = "Hallo {rcpt-name},\n\nein neuer Benutzer hat sich im Forum registriert. Hier ist der Link zu seinem Profil:\n\n{url}";

$UNB_T['register.mail.newuser.subject'] = 'Neuer Benutzer im Forum';
$UNB_T['register.mail.newuser.body'] = $UNB_T['register.mail.manual.body'];

// Password mails
$UNB_T['mail.mkpass1.subject'] = 'Anforderung neues Kennwort';
$UNB_T['mail.mkpass1.body'] = "Hallo {rcpt-name},\n\ndu hast ein neues Kennwort für deinen Benutzeraccount angefordert. Aus Sicherheitsgründen musst du zuerst diesem Link folgen, um dich zu authorisieren. Danach wird dir das neue Kennwort wieder per E-Mail zugesendet.\n\n{url}\n\nWenn du kein neues Kennwort angefordert hast, kannst du diese E-Mail einfach ignorieren. Zu diesem Zeitpunkt wurde dein Kennwort natürlich noch nicht verändert.";

$UNB_T['mail.mkpass2.subject'] = 'Neues Kennwort';
$UNB_T['mail.mkpass2.body'] = "Hallo {rcpt-name},\n\ndu hast ein neues Kennwort für deinen Benutzeraccount angefordert.\n\nDas neue Kennwort lautet „{password}“ (ohne Anführungszeichen) und ist ab sofort gültig.\n\nDu kannst dich jetzt mit deinem Benutzernamen und diesem Kennwort anmelden und das Kennwort bei Bedarf in deinem Profil ändern.";

// ThreadWatch notifications
$UNB_T['mail.threadwatchnotify.subject'] = 'Neuer Beitrag zu: {subject}';
$UNB_T['mail.threadwatchnotify.body'] = "Hallo {rcpt-name},\n{poster} hat einen Beitrag zum Thema „{subject}“ im Forum „{forum}“ geschrieben, das du beobachtest.\nMit folgendem Link kannst du den Beitrag lesen:\n\n{url}\n\nDu wirst keine weiteren Benachrichtigungen zu diesem Thema erhalten, bis du die neuen Beiträge gelesen hast.\nUm das Thema nicht weiter zu beobachten, kannst du die Funktion auf der angegebenen Seite deaktivieren.";

$UNB_T['mail.threadwatchnotify-jabber.subject'] = 'Neuer Beitrag zu: {subject}';
$UNB_T['mail.threadwatchnotify-jabber.body'] = "Hallo {rcpt-name},\n{poster} hat einen Beitrag zum Thema „{subject}“ im Forum „{forum}“ geschrieben:\n{url}";

// ForumWatch notifications
$UNB_T['mail.forumwatchnotify.subject'] = 'Neues Thema in: {forum}';
$UNB_T['mail.forumwatchnotify.body'] = "Hallo {rcpt-name},\n{poster} hat das Thema „{subject}“{desc} im Forum „{forum}“ eröffnet.\nMit folgendem Link kannst du den ersten Beitrag lesen:\n\n{url}\n\nDu wirst keine weiteren Benachrichtigungen zu diesem Forum erhalten, bis du dieses Forum wieder besucht hast.\nUm das Forum nicht weiter zu beobachten, kannst du die Funktion auf der Forum-Seite deaktivieren.";

$UNB_T['mail.forumwatchnotify-jabber.subject'] = 'Neues Thema in: {forum}';
$UNB_T['mail.forumwatchnotify-jabber.body'] = "Hallo {rcpt-name},\n{poster} hat das Thema „{subject}“{desc} im Forum „{forum}“ eröffnet:\n{url}";

// User message mails
$UNB_T['mail.usermail.subject'] = 'Nachricht von {name}';
$UNB_T['mail.usermail.body1'] = "Hallo {rcpt-name},\n{poster} hat dir folgende Nachricht über das Forum gesendet:\n\n-----\n{msg}\n-----\n\nUm {poster} zu antworten, kannst du entweder auf diese E-Mail antworten oder direkt an {email} schreiben.\nWenn du deine eigene Mailadresse nicht preisgeben willst, kannst du auch über diesen Link antworten:\n{url}";
$UNB_T['mail.usermail.body2'] = "Hallo {rcpt-name},\n{poster} hat dir folgende Nachricht über das Forum gesendet:\n\n-----\n{msg}\n-----\n\nUm {poster} zu antworten, benutze bitte diesen Link:\n{url}\nACHTUNG: Direkte Antworten auf diese E-Mail werden an den Board-Administrator gesendet!";

?>