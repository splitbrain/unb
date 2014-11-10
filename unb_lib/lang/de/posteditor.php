<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : posteditor
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20081122
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

$mac = $UNB['Client']['os_class'] == 'mac';

$UNB_T['pe.compose post'] = 'Beitrag schreiben';
$UNB_T['pe.compose announce'] = 'Ankündigung schreiben';
$UNB_T['pe.edit post'] = 'Beitrag bearbeiten';
$UNB_T['pe.edit announce'] = 'Ankündigung bearbeiten';

$UNB_T['pe.optional'] = '(optional)';

$UNB_T['pe.format.bold'] = 'F';
$UNB_T['pe.format.bold.tip'] = 'Fettdruck';
$UNB_T['pe.format.italic'] = 'K';
$UNB_T['pe.format.italic.tip'] = 'Kursivdruck';
$UNB_T['pe.format.underline'] = 'U';
$UNB_T['pe.format.underline.tip'] = 'Unterstreichen';
$UNB_T['pe.format.linethrough'] = 'D';
$UNB_T['pe.format.linethrough.tip'] = 'Durchstreichen';
$UNB_T['pe.format.monospace'] = 'M';
$UNB_T['pe.format.monospace.tip'] = 'Festbreitenschrift';

$UNB_T['pe.format.quote'] = 'Zitat';
$UNB_T['pe.format.quote.tip'] = 'Zitat einfügen (mit Umschalttaste: Zitat unterbrechen)';
$UNB_T['pe.format.link'] = 'Link';
$UNB_T['pe.format.link.tip'] = 'Verweis einfügen (mit Umschalttaste: Textcursor auf Ziel setzen)';
$UNB_T['pe.format.image'] = 'Bild';
$UNB_T['pe.format.image.tip'] = 'Bild einfügen';
$UNB_T['pe.format.code'] = 'Code';
$UNB_T['pe.format.code.tip'] = 'Quelltextbereich einfügen';

$UNB_T['pe.colour.red'] = 'Rot';
$UNB_T['pe.colour.orange'] = 'Orange';
$UNB_T['pe.colour.green'] = 'Grün';
$UNB_T['pe.colour.lightblue'] = 'Hellblau';
$UNB_T['pe.colour.blue'] = 'Blau';
$UNB_T['pe.colour.violet'] = 'Violett';
$UNB_T['pe.colour.grey'] = 'Grau';

$UNB_T['pe.button.smilies'] = 'Smileys';
$UNB_T['pe.formatting help'] = 'Hilfe';

$AltKey = $mac ? 'Strg' : 'Alt';
$UNB_T['pe.save post'] = 'Beitrag speichern';
$UNB_T['pe.save post.key'] = 's';
$UNB_T['pe.save post.tip'] = $AltKey . '+S oder Strg+Enter';
$UNB_T['pe.save announce'] = 'Ankündigung speichern';
$UNB_T['pe.save announce.key'] = 's';
$UNB_T['pe.save announce.tip'] = $AltKey . '+S oder Strg+Enter';
$UNB_T['pe.reply'] = 'Antwort absenden';
$UNB_T['pe.reply.key'] = 's';
$UNB_T['pe.reply.tip'] = $AltKey . '+S oder Strg+Enter';
$UNB_T['pe.new announce'] = 'Neue Ankündigung erstellen';
$UNB_T['pe.new announce.key'] = 's';
$UNB_T['pe.new announce.tip'] = $AltKey . '+S oder Strg+Enter';
$UNB_T['pe.new topic'] = 'Neues Thema erstellen';
$UNB_T['pe.new topic.key'] = 's';
$UNB_T['pe.new topic.tip'] = $AltKey . '+S oder Strg+Enter';
$UNB_T['pe.preview'] = 'Vorschau';
$UNB_T['pe.preview.key'] = 'v';
$UNB_T['pe.preview.tip'] = $AltKey . '+V';

$UNB_T['pe.close'] = 'Schließen';
$UNB_T['pe.larger'] = 'Größer +';
$UNB_T['pe.smaller'] = 'Kleiner –';
$UNB_T['pe.text length'] = 'Textlänge';
$UNB_T['pe.add poll'] = 'Umfrage erstellen';
$UNB_T['pe.remove poll'] = 'Umfrage löschen';
$UNB_T['pe.post options'] = 'Beitragsoptionen';
$UNB_T['pe.notify via'] = 'Benachrichtigung via';
$UNB_T['pe.you watch this by x'] = '(Du beobachtest diesen Thread bereits per {x})';
$UNB_T['pe.no smilies'] = 'Smileys deaktivieren';
$UNB_T['pe.no special syntax'] = 'Einfache Formatierung deaktivieren';
$UNB_T['pe.thread is'] = 'Thema ist';
$UNB_T['pe.remove attach'] = 'Datei entfernen';
$UNB_T['pe.attach file'] = 'Datei anhängen';
$UNB_T['pe.select file on save'] = 'Du solltest die Datei erst auswählen, bevor der Beitrag endgültig gesendet wird, da sie sonst nicht gespeichert werden kann.';
$UNB_T['pe.announce.important'] = 'Wichtige Ankündigung';
$UNB_T['pe.announce.recursive'] = 'Auch in Unterforen anzeigen';
$UNB_T['pe.announce.show in threads'] = 'Auch in Themen anzeigen';
$UNB_T['pe.announce.display to'] = 'Anzeigen für';
$UNB_T['pe.announce.to.all'] = 'Alle Benutzer';
$UNB_T['pe.announce.to.guests'] = 'Gäste';
$UNB_T['pe.announce.to.members'] = 'Mitglieder';
$UNB_T['pe.announce.to.moderators'] = 'Moderatoren';
$UNB_T['pe.announce.delete'] = 'Ankündigung löschen';
$UNB_T['pe.post.delete'] = 'Beitrag löschen';
$UNB_T['pe.poll.question'] = 'Fragestellung';
$UNB_T['pe.poll.replies+sort'] = 'Hier kannst du die Antworten und deren Sortierung festlegen, für die andere abstimmen können.';
$UNB_T['pe.poll.replies'] = 'Hier kannst du die Antworten festlegen, für die andere abstimmen können.';
$UNB_T['pe.poll.timeout'] = 'Nach welcher Zeit soll die Umfrage beendet werden?';
$UNB_T['pe.poll.timeout.hours'] = 'Stunden';
$UNB_T['pe.poll.timeout.days'] = 'Tage';
$UNB_T['pe.poll.timeout~'] = '0 bedeutet unbegrenzte Dauer';
$UNB_T['pe.last posts in thread'] = 'Die letzten Beiträge zu diesem Thema &nbsp; <small>(Neuester zuerst, maximal 10 Beiträge)</small>';

$UNB_T['pe.no edit note'] = 'Keine Änderungsnotiz anfügen';
$UNB_T['pe.remove edit note'] = 'Vorhandene Änderungsnotiz entfernen';

$UNB_T['pe.quick reply to this post'] = 'Auf diesen Beitrag antworten';

$UNB_T['pe.alt chars~'] = 'Keine Alternativzeichen für die letzte Eingabe vorhanden.';
$UNB_T['pe.shorten quote'] = 'Kürze bitte die Zitate auf das Wesentliche, um die Übersichtlichkeit zu verbessern.';
$UNB_T['pe.guest posting'] = 'Du bist nicht angemeldet und schreibst diesen Beitrag als Gast.';
$UNB_T['pe.warn edit other users post'] = 'Du bearbeitest den Beitrag eines anderen Benutzers.';

?>