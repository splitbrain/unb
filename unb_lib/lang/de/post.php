<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : post
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20051222
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

$UNB_T['post.do reply'] = 'Antworten';
$UNB_T['post.do fast reply'] = 'Schnellantwort';
$UNB_T['post.do quote'] = 'Zitieren';
$UNB_T['post.do edit'] = 'Ändern';
$UNB_T['post.do delete'] = 'Entfernen';

$UNB_T['post.in reply to post'] = 'Antwort auf Beitrag';
$UNB_T['post.thread starter'] = 'Themenstarter';
$UNB_T['post.show profile'] = 'Profil anzeigen';
$UNB_T['post.send e-mail'] = 'Nachricht an Benutzer';
$UNB_T['post.mark unread'] = 'Ungelesen ab hier';
$UNB_T['post.link to this post'] = 'Link auf diesen Beitrag';
$UNB_T['post.show ip'] = 'IP anzeigen';
$UNB_T['post.attach~'] = 'Der Autor hat {n} Dateien an diesen Beitrag angehängt';
$UNB_T['post.attach~.num1'] = 'Der Autor hat eine Datei an diesen Beitrag angehängt';
$UNB_T['post.no profile'] = 'Kein Benutzerprofil vorhanden.';
$UNB_T['post.attach.open'] = 'Datei öffnen';
$UNB_T['post.attach.save~'] = 'Datei lokal speichern';
$UNB_T['post.attach.save'] = 'Speichern';
$UNB_T['post.attach.image'] = 'Bild';
$UNB_T['post.attach.image~'] = 'Bild anzeigen';
$UNB_T['post.attach.thumbnail'] = 'Thumbnail';
$UNB_T['post.edit reason'] = 'Begründung';
$UNB_T['post.is preview'] = 'Dieser Beitrag ist eine Vorschau und wurde noch nicht gespeichert.';
$UNB_T['post.new posts'] = 'Die folgenden Beiträge sind neu.';
$UNB_T['post.read by'] = 'Gelesen von';
$UNB_T['post.changed since last read'] = 'Der Beitrag wurde nach dem Lesen verändert';
$UNB_T['post.error.attach.no permission'] = 'Du hast keine Berechtigung, diese Datei zu öffnen.';
$UNB_T['post.do vote'] = 'Abstimmen';
$UNB_T['post.n previous edited'] = 'Warnung: {n} frühere Beiträge wurden bearbeitet!';
$UNB_T['post.n previous edited.num1'] = 'Warnung: Ein früherer Beitrag wurde bearbeitet!';
$UNB_T['post.in the forum'] = 'im Forum';
$UNB_T['post.changed n times'] = 'Dieser Beitrag wurde <b>{n}</b> mal verändert, zuletzt am {d} von {u}.';
$UNB_T['post.changed n times.num1'] = 'Dieser Beitrag wurde am {d} von {u} verändert.';
$UNB_T['post.changed automatically'] = 'Dieser Beitrag wurde automatisch verändert, um Darstellungsfehler zu vermeiden.';
$UNB_T['post.downloaded n times'] = '{n} mal heruntergeladen';
$UNB_T['post.downloaded n times.num0'] = 'noch nie heruntergeladen';
$UNB_T['post.downloaded n times.num1'] = 'einmal heruntergeladen';
$UNB_T['post.error.attach x not found'] = 'Die an diesen Beitrag angehängte Datei „{x}“ wurde nicht gefunden!';

$UNB_T['poll.vote details'] = '{count} Stimmen · {percent}%';
$UNB_T['poll.vote details.num1'] = 'Eine Stimme · {percent}%';
$UNB_T['poll.n votes'] = '<b>{n}</b> Stimmen';
$UNB_T['poll.n votes.num0'] = 'Noch keine Stimmen';
$UNB_T['poll.n votes.num1'] = '<b>Eine</b> Stimme';
$UNB_T['poll.cast your vote'] = 'Wähle eine der folgenden Antworten aus';
$UNB_T['poll.ended time'] = 'Die Umfrage wurde {time} beendet';   // {time} is a grammar-4-date (point of time past)
$UNB_T['poll.no timelimit'] = 'Die Umfrage hat keine Zeitbegrenzung';
$UNB_T['poll.ends time'] = 'Die Umfrage endet {time}, am {abstime}';   // {time} is a grammar-3-date (point of time future), {abstime} is an absolute date/time
$UNB_T['poll.users have voted'] = 'Folgende Benutzer haben bereits abgestimmt';
$UNB_T['poll.show results'] = 'Ergebnis anzeigen';
$UNB_T['poll.cancel vote'] = 'Stimme zurückziehen';
$UNB_T['poll.back to voting'] = 'Zurück zur Wahl';
$UNB_T['poll.show users'] = 'Benutzer anzeigen';

// Error messages
$UNB_T['post.error.no text'] = 'Es wurde kein Text eingegeben.';
$UNB_T['post.error.too long'] = 'Der Beitrag ist zu lang. Es sind maximal {max} Zeichen zulässig.';
$UNB_T['post.error.subject too short'] = 'Der Betreff ist zu kurz. Es sind mindestens {min} Zeichen erforderlich.';
$UNB_T['post.error.subject too long'] = 'Der Betreff ist zu lang. Es sind maximal {max} Zeichen zulässig.';
$UNB_T['post.error.abbc syntax error'] = 'Fehler bei der Verwendung der Formatierungscodes.';
$UNB_T['post.error.invalid poll timeout'] = 'Die Zeitbegrenzung der Umfrage ist ungültig.';
$UNB_T['post.error.too few poll options'] = 'Für eine Umfrage müssen mindestens zwei Antworten angegeben werden.';
$UNB_T['post.error.guests need name'] = 'Gäste müssen einen Autornamen eingeben.';
$UNB_T['post.error.poll.no question'] = 'Für eine Umfrage muss eine Fragestellung angegeben werden.';
$UNB_T['post.error.invalid attach'] = 'Ungültige Attachment-Datei.';
$UNB_T['post.error.thread closed'] = 'Dieses Thema ist geschlossen, es können keine weiteren Beiträge geschrieben werden.';
$UNB_T['post.error.poll.not allowed'] = 'Du darfst keine Umfrage erstellen.';
$UNB_T['post.error.no subject'] = 'Zum Erstellen eines neuen Themas muss ein Betreff eingegeben werden.';
$UNB_T['post.error.thread not created'] = 'Thema konnte nicht angelegt werden.';
$UNB_T['post.error.post not created'] = 'Beitrag konnte nicht angelegt werden.';
$UNB_T['post.error.attach not saved'] = 'Hochgeladene Datei konnte nicht gespeichert werden.';
$UNB_T['post.error.poll not created'] = 'Die Umfrage konnte nicht vollständig erstellt werden';
$UNB_T['post.error.post not deleted2'] = 'Der Beitrag konnte nicht wieder entfernt werden.';
$UNB_T['post.error.thread not deleted2'] = 'Das Thema konnte nicht wieder entfernt werden.';
$UNB_T['post.error.post not deleted'] = 'Beitrag konnte nicht gelöscht werden.';
$UNB_T['post.error.poll details not saved'] = 'Umfragedetails konnten nicht gespeichert werden.';
$UNB_T['post.error.poll.change reply n'] = 'Die {n}. Antwort der Umfrage konnte nicht aktualisiert werden.';
$UNB_T['post.error.poll.create reply n'] = 'Die {n}. Antwort der Umfrage konnte nicht erstellt werden.';
$UNB_T['post.error.poll not deleted'] = 'Umfrage konnte nicht entfernt werden.';
$UNB_T['post.error.votes not deleted'] = 'Stimmen dieser Umfrage konnten nicht entfernt werden.';
$UNB_T['post.error.access denied delete poll'] = 'Zugriff verweigert: Umfrage löschen';
$UNB_T['post.error.announce not saved'] = 'Ankündigung konnte nicht gespeichert werden.';
$UNB_T['post.error.announce not deleted'] = 'Ankündigung konnte nicht gelöscht werden.';
$UNB_T['post.error.invalid thread for post'] = 'Hier kann kein Beitrag geschrieben werden oder Beitrag wurde nicht gefunden.';

?>