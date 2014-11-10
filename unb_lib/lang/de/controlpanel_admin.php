<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : controlpanel_admin
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20070420
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// ---------- Category names ----------
$UNB_T['cp.category.board configuration'] = 'Board-Konfiguration';
$UNB_T['cp.category.board settings'] = 'Allg. Einstellungen';
$UNB_T['cp.category.board appearance'] = 'Anzeigeoptionen';
$UNB_T['cp.category.security'] = 'Sicherheit';
$UNB_T['cp.category.plugins'] = 'Plug-Ins';
$UNB_T['cp.category.more pages'] = 'Weitere Seiten';
#$UNB_T['cp.category.forums editor'] = 'Foreneditor';
#$UNB_T['cp.category.acl editor'] = 'ACL-Editor';
#$UNB_T['cp.category.groups editor'] = 'Gruppeneditor';
#$UNB_T['cp.category.avatars overview'] = 'Avatar-Übersicht';
#$UNB_T['cp.category.inactive users'] = 'Inaktive Benutzer';
#$UNB_T['cp.category.log viewer'] = 'Log anzeigen';
#$UNB_T['cp.category.check database'] = 'Datenbank prüfen';
#$UNB_T['cp.category.export database'] = 'Datenbank sichern';

// ----- Board settings -----

$UNB_T['cp.forum title'] = 'Name des Forums';
$UNB_T['cp.forum title~'] = 'Dieser Titel wird u.a. als Seitentitel und als Absendername für E-Mails verwendet.';
$UNB_T['cp.home url'] = 'URL des Forums';
$UNB_T['cp.home url~'] = 'Basis-Adresse des Forums (nur der Verzeichnisname, ohne den PHP-Dateinamen). Dieser Wert wird bei der Installation automatisch ermittelt und muss normalerweise nicht geändert werden.';
$UNB_T['cp.parent url'] = 'URL der Hauptseite';
$UNB_T['cp.parent url~'] = 'Falls angegeben, wird der Link „Hauptseite“ in der Navigationszeile eingefügt.';
$UNB_T['cp.toplogo url'] = 'Linkziel des Logos';
$UNB_T['cp.toplogo url~'] = 'Falls angegeben, wird diese Adresse als Linkziel des Foren-Logos verwendet. Ansonsten zeigt das Logo auf die Foren-Startseite.';

$UNB_T['cp.database connection'] = 'Datenbankverbindung';
$UNB_T['cp.db server'] = 'Datenbankserver';
$UNB_T['cp.db user'] = 'Benutzername';
$UNB_T['cp.db pass'] = 'Kennwort';
$UNB_T['cp.db name'] = 'Datenbankname';
$UNB_T['cp.db prefix'] = 'Präfix der Tabellennamen';
$UNB_T['cp.db prefix~'] = 'Diese Zeichenfolge wird den Standard-Tabellennamen vorangestellt. Verwende unterschiedliche Schlüssel, um mehr als ein Forum in einer einzigen Datenbank zu speichern.';

$UNB_T['cp.smtp settings'] = 'SMTP-Einstellungen';
$UNB_T['cp.smtp server'] = 'Mailserver';
$UNB_T['cp.smtp sender'] = 'E-Mail-Adresse';
$UNB_T['cp.smtp user'] = 'Benutzername für SMTP AUTH';
$UNB_T['cp.smtp pass'] = 'Kennwort für SMTP AUTH';
$UNB_T['cp.use php mail'] = 'PHP mail()-Funktion zum Senden von E-Mails verwenden';
$UNB_T['cp.use php mail~'] = 'Die Verwendung der mail()-Funktion erfordert keine Konfiguration der SMTP-Daten. Als Absenderadresse wird die angegebene E-Mail-Adresse verwendet, falls sie angegeben wurde. Die Verwendung einer SMTP-Verbindung wird empfohlen, da sie zuverlässiger funktioniert und die Auswertung von Fehlern zulässt.';

$UNB_T['cp.enable jabber'] = 'Jabber-Benachrichtigungen aktivieren';
$UNB_T['cp.jabber settings'] = 'Jabber-Einstellungen';
$UNB_T['cp.jabber server'] = 'Jabberserver';
$UNB_T['cp.jabber server~'] = 'Hostname nach dem @-Zeichen, optional mit „:port“ (Vorgabe ist Port 5222), SSL wird nicht unterstützt';
$UNB_T['cp.jabber user'] = 'Benutzername';
$UNB_T['cp.jabber user~'] = 'Nur der Login-Name vor dem @-Zeichen';
$UNB_T['cp.jabber pass'] = 'Kennwort';

$UNB_T['cp.board language~'] = 'Legt die Sprache fest, in der die Forenoberfläche angezeigt wird. Diese Auswahl kann durch die Browsereinstellung des Benutzers aufgehoben werden.';
$UNB_T['cp.board timezone~'] = 'Wähle die lokale Zeitzone aus, in der Zeitangaben im Forum dargestellt werden sollen. Diese Auswahl kann durch die Browsereinstellung des Benutzers aufgehoben werden.';

// ----- Board appearance -----

$UNB_T['cp.smilies set'] = 'Smileys-Sammlung';
$UNB_T['cp.smilies set~'] = 'Es stehen mehrere Smileys-Sammlungen zur Verfügung, aus denen du eine wählen kannst, um Smileys in Beiträgen und anderen Texten durch Grafiken darzustellen.';

$UNB_T['cp.board appearance.general'] = 'Allgemeine Anzeigeoptionen';

$UNB_T['cp.login top'] = 'Anmeldefeld am Seitenanfang anzeigen';
$UNB_T['cp.login top~'] = 'Bei schmalen Webseiten ist es sinnvoll, das Anmeldefeld am Seitenende anzuzeigen.';
$UNB_T['cp.show online users'] = 'Momentan aktive Benutzer anzeigen';
$UNB_T['cp.show online users~'] = 'Diese Listen befinden sich am Ende der Forenübersicht und in den einzelnen Foren.';
$UNB_T['cp.foot db time'] = 'Seitenstatistik in der Fußzeile anzeigen';
$UNB_T['cp.foot db time~'] = 'Enthält Dauer des Seitenaufbaus sowie Anzahl und Dauer der Datenbankabfragen.';
$UNB_T['cp.gzip'] = 'GZip-Seitenkomprimierung verwenden';
$UNB_T['cp.gzip~'] = 'Durch die Komprimierung der Webseiten für die Übertragung zum Browser wird bis zu 90% des Datenverkehrs eingespart, außerdem beschleunigt das den Seitenaufbau bei langsamen Internetverbindungen enorm. Die Rechenlast ist dabei zu vernachlässigen.';
$UNB_T['cp.gzip.off'] = 'Deaktivieren';
$UNB_T['cp.gzip.on'] = 'Aktivieren';
$UNB_T['cp.gzip.auto'] = 'Automatisch aktivieren';
$UNB_T['cp.mod_rewrite urls'] = 'Kurze URLs verwenden';
$UNB_T['cp.mod_rewrite urls~'] = 'Verwendet URLs der Form „/forum/3“ oder „/post/2345“ statt komplexen Parametern. Diese Option erfordert das mod_rewrite-Modul im Apache-Webserver und wird bei der Installation automatisch aktiviert, falls möglich.';
$UNB_T['cp.show goto forum'] = 'Foren-Auswahlliste anzeigen';
$UNB_T['cp.show goto forum~'] = 'Zeigt am Ende von Themenansichten eine Auswahlbox aller Foren an, um schnell in ein anderes Forum zu wechseln.';
$UNB_T['cp.show search forum'] = 'Schnellsuche in Foren/Threads';
$UNB_T['cp.show search forum~'] = 'Zeigt am Ende von Themenlisten oder der Themenansicht ein Textfeld zur schnellen Suche in diesem Forum bzw. Thema an.';
$UNB_T['cp.enable trace users'] = 'Liste der angemeldeten Benutzer aktivieren';
$UNB_T['cp.enable trace users~'] = 'In dieser Liste kann jeder mitverfolgen, welcher Benutzer sich gerade in welchem Bereich des Forums aufhält.';
$UNB_T['cp.post preview send button'] = 'Senden-Schaltfläche direkt unter der Beitragsvorschau anzeigen';
$UNB_T['cp.post preview send button~'] = 'Ermöglicht ein einfacheres Absenden von Beiträgen nach dem Prüfen in der Vorschau.';
$UNB_T['cp.show last visit time'] = 'Zeit des letzten Besuchs anzeigen';
$UNB_T['cp.show last visit time~'] = 'Diese Angabe ist am Ende der Forenübersicht zu finden.';
$UNB_T['cp.forum tree style'] = 'Linienstil in der Forenauswahl';
$UNB_T['cp.forum tree style.unicode'] = 'Unicode';
$UNB_T['cp.forum tree style.nolines'] = 'Keine Linien';
$UNB_T['cp.forum tree style.dots'] = 'Punkte';
$UNB_T['cp.forum tree style.hlines'] = 'Horizontale Striche';
$UNB_T['cp.forum tree style~'] = 'Ändert das Aussehen der aufklappbaren Forenauswahl (siehe Option „' . $UNB_T['cp.show goto forum'] . '“).';
$UNB_T['cp.display forum lastpost re'] = 'In der Forenliste „Re:“ beim letzten Beitrag anfügen';
$UNB_T['cp.display forum lastpost re~'] = 'Falls der letzte Beitrag in einem Forum eine Antwort in einem Thema ist, wird dem Themenbetreff die in E-Mails gebräuchliche Abkürzung „Re:“ vorangestellt.';
$UNB_T['cp.show birthdays'] = 'Geburtstage anzeigen';
$UNB_T['cp.show birthdays~'] = 'Alle Benutzer und ihr Alter am Ende der Forenübersicht auflisten, die am heutigen Tag Geburtstag haben.';
$UNB_T['cp.disable search highlighting'] = 'Hervorheben von Suchtreffern deaktivieren';
$UNB_T['cp.disable search highlighting~'] = 'Die Hervorhebung von Suchtreffern kann bei Darstellungsproblemen deaktiviert werden.';
$UNB_T['cp.show forum rss link'] = 'RSS-Links anzeigen';
$UNB_T['cp.show forum rss link~'] = 'Zeigt in der Forenübersicht und einzelnen Themen Links zum entsprechenden RSS-Newsfeed an.';
$UNB_T['cp.location link'] = 'Kartendienst-URL für Wohnorte';
$UNB_T['cp.location link~'] = 'Wohnorte in Benutzerprofilen werden automatisch mit diesem Web-Kartendienst verlinkt. „%s“ wird mit dem angegebenen Wohnort ersetzt.';

$UNB_T['cp.per page~'] = 'Bei den folgenden Einstellungen zeigt ein Wert von 0 alle Einträge auf einer Seite an bzw. deaktiviert die Funktion.';
$UNB_T['cp.threads per page'] = 'Themen pro Seite';
$UNB_T['cp.posts per page'] = 'Beiträge pro Seite';
$UNB_T['cp.users per page'] = 'Benutzer pro Seite';
$UNB_T['cp.hot threads posts'] = 'Beiträge für einen ‚Hot Thread‘';
$UNB_T['cp.hot threads views'] = 'Aufrufe für einen ‚Hot Thread‘';

$UNB_T['cp.extra names'] = 'Zusätzliche Benutzerprofil-Felder';
$UNB_T['cp.extra names~'] = 'Diese Bezeichnungen werden für weitere Textfelder im Benutzerprofil verwendet. Gib alle Bezeichnungen durch ein „|“-Zeichen getrennt ein.';
$UNB_T['cp.extra names.n db cols'] = 'Momentan sind {n} Spalten in der Datenbank vorhanden. Wenn du weniger Felder eingibst, werden die zusätzlichen Spalten entfernt und deren Daten gehen verloren! Bei Bedarf werden weitere Spalten (bis zu 10) angelegt.';
$UNB_T['cp.extra names.n db cols.num0'] = 'Momentan ist keine Spalte in der Datenbank vorhanden. Bei Bedarf werden weitere Spalten (bis zu 10) angelegt.';
$UNB_T['cp.extra names.n db cols.num1'] = 'Momentan ist eine Spalte in der Datenbank vorhanden. Wenn du weniger Felder eingibst, werden die zusätzlichen Spalten entfernt und deren Daten gehen verloren! Bei Bedarf werden weitere Spalten (bis zu 10) angelegt.';

$UNB_T['cp.board appearance.posts'] = 'Beiträge';

$UNB_T['cp.new topic link in thread'] = '„Neues Thema“-Link in der Themenansicht anzeigen';
$UNB_T['cp.new topic link in thread~'] = '';
$UNB_T['cp.post attach inline maxsize'] = 'Maximale Dateigröße für eingebettete Attachments';
$UNB_T['cp.post attach inline maxsize.unit'] = 'Bytes';
$UNB_T['cp.post attach inline maxsize~'] = '';
$UNB_T['cp.post attach inline maxwidth'] = 'Maximale Bildbreite für eingebettete Attachments';
$UNB_T['cp.post attach inline maxwidth.unit'] = 'Pixel';
$UNB_T['cp.post attach inline maxwidth~'] = '';
$UNB_T['cp.post attach inline maxheight'] = 'Maximale Bildhöhe für eingebettete Attachments';
$UNB_T['cp.post attach inline maxheight.unit'] = 'Pixel';
$UNB_T['cp.post attach inline maxheight~'] = '';
$UNB_T['cp.post show textlength'] = 'Aktuelle Textlänge im Beitragseditor zählen';
$UNB_T['cp.post show textlength~'] = '';
$UNB_T['cp.max poll options'] = 'Anzahl verfügbarer Antworten in Umfragen';
$UNB_T['cp.max poll options~'] = '';

$UNB_T['cp.board appearance.threads forums'] = 'Themen und Foren';

$UNB_T['cp.own posts in threadlist'] = 'Themen mit eigenen Beiträgen markieren';
$UNB_T['cp.own posts in threadlist~'] = 'Hebt Themen in der Liste mit einem speziellen Symbol hervor, die Beiträge des angemeldeten Benutzers enthalten.';
$UNB_T['cp.show bookmarked thread'] = 'Lesezeichen in der Themenliste markieren';
$UNB_T['cp.show bookmarked thread~'] = 'Hebt Themen in der Liste mit einem speziellen Symbol hervor, die der angemeldete Benutzer als persönliches Lesezeichen gespeichert hat.';
$UNB_T['cp.display thread startdate'] = 'Startzeit der Themen in der Themenliste anzeigen';
$UNB_T['cp.display thread startdate~'] = 'Zeigt in der Liste an, wann ein Thema gestartet wurde.';
$UNB_T['cp.advanced thread counter'] = 'Zusätzliche Zähler in der Themenliste';
$UNB_T['cp.advanced thread counter~'] = 'Zählt zusätzlich die Anzahl der Benutzer, die ein Thema gelesen oder darin geschrieben haben.';
$UNB_T['cp.count thread views'] = 'Themenaufrufe anzeigen';
$UNB_T['cp.count thread views~'] = 'Zeigt in der Liste an, wie oft ein Thema abgerufen wurde.';
$UNB_T['cp.display thread lastposter'] = 'Autor des letzten Beitrags in der Themenliste anzeigen';
$UNB_T['cp.display thread lastposter~'] = 'Zeigt in der Liste an, wer den letzten Beitrag in einem Thema verfasst hat.';
$UNB_T['cp.count forum threads posts'] = 'Themen und Beiträge in der Forenliste zählen';
$UNB_T['cp.count forum threads posts~'] = 'Zeigt in der Forenliste an, wie viele Themen und Beiträge ein Forum enthält.';
$UNB_T['cp.display forum lastpost'] = 'Letzten Beitrag in der Forenliste anzeigen';
$UNB_T['cp.display forum lastpost~'] = 'Zeigt in der Forenliste den Verfasser und den Zeitpunkt des letzten Beitrags in einem Forum an.';

$UNB_T['cp.board appearance.users'] = 'Benutzerliste';

$UNB_T['cp.ulist regdate'] = 'Registrierungsdatum in der Benutzerliste anzeigen';
$UNB_T['cp.ulist regdate~'] = '';
$UNB_T['cp.ulist location'] = 'Wohnort in der Benutzerliste anzeigen';
$UNB_T['cp.ulist location~'] = '';
$UNB_T['cp.ulist posts'] = 'Anzahl der Beiträge in der Benutzerliste anzeigen';
$UNB_T['cp.ulist posts~'] = '';
$UNB_T['cp.ulist lastpost'] = 'Letzten Beitrag in der Benutzerliste anzeigen';
$UNB_T['cp.ulist lastpost~'] = '';

$UNB_T['cp.board appearance.timings'] = 'Zeitangaben';

$UNB_T['cp.poll current days'] = '„Aktuelle Umfragen“ findet Umfragen der letzten';
$UNB_T['cp.poll current days.unit'] = 'Tage';
$UNB_T['cp.poll current days~'] = 'Die Suchfunktion „Aktuelle Umfragen“ wird alle Umfragen anzeigen, die innerhalb dieser Zeit gestartet und seitdem noch nicht beendet wurden.';
$UNB_T['cp.quote with date'] = 'Datum bei Zitaten speichern für Originalbeiträge älter als';
$UNB_T['cp.quote with date.unit'] = 'Tage';
$UNB_T['cp.quote with date~'] = 'Wenn der zitierte Beitrag länger als diese Zeit zurückliegt, wird beim Zitieren automatisch die Zeit des zitierten Beitrags eingefügt.';
$UNB_T['cp.no edit note grace time'] = 'Keinen Änderungshinweis bei Bearbeitung eines Beitrags innerhalb';
$UNB_T['cp.no edit note grace time.unit'] = 'Minuten';
$UNB_T['cp.no edit note grace time~'] = 'Wird ein Beitrag innerhalb dieser Zeit nach dem Verfassen bearbeitet und hat diesen Beitrag noch kein anderer Benutzer gelesen, so wird dem Beitrag kein Änderungshinweis hinzugefügt.';
$UNB_T['cp.moved thread note timeout'] = 'Hinweis für verschobene Themen entfernen nach';
$UNB_T['cp.moved thread note timeout.unit'] = 'Tagen';
$UNB_T['cp.moved thread note timeout~'] = 'Die Weiterleitung, die für verschobene Themen eingerichtet werden kann, wird nach dieser Zeit automatisch wieder entfernt.';
$UNB_T['cp.online users reload interval'] = 'Liste der angemeldeten Benutzer neu laden nach';
$UNB_T['cp.online users reload interval.unit'] = 'Millisekunden';
$UNB_T['cp.online users reload interval~'] = 'Die Liste der angemeldeten Benutzer wird nach dieser Zeit automatisch aktualisiert.';
$UNB_T['cp.user online timeout'] = 'Benutzer als ‚abgemeldet‘ betrachten nach';
$UNB_T['cp.user online timeout.unit'] = 'Sekunden';
$UNB_T['cp.user online timeout~'] = 'Liegt die letzte Aktivität eines Benutzers länger als diese Zeit zurück, wird er im Forum nicht mehr als ‚angemeldet‘ geführt.';

// ----- Security -----

$UNB_T['cp.security.user accounts'] = 'Benutzerkonten';
$UNB_T['cp.security.avatars and photos'] = 'Avatare und Fotos';
$UNB_T['cp.security.posts and topics'] = 'Beiträge und Themen';
$UNB_T['cp.security.advanced'] = 'Weitere Einstellungen';

$UNB_T['cp.new user validation'] = 'Prüfung neuer Benutzer';
$UNB_T['cp.new user validation.disabled'] = 'Vorübergehend deaktivieren';
$UNB_T['cp.new user validation.immediate'] = 'Sofort freischalten';
$UNB_T['cp.new user validation.email'] = 'Prüfung der E-Mail-Adresse';
$UNB_T['cp.new user validation.manual'] = 'Manuell durch Administrator';
$UNB_T['cp.new user validation~'] = 'Bevor neue Benutzer in die Gruppe der Mitglieder aufgenommen werden, kann die Registrierung geprüft werden. Die einfachste Form besteht darin, dem Benutzer eine E-Mail mit dem Aktivierungsschlüssel zu senden, und so die angegebene E-Mail-Adresse auf Funktion zu prüfen. Die Registrierung neuer Benutzer kann auch komplett deaktiviert werden.';
$UNB_T['cp.disallowed usernames'] = 'Unzulässige Benutzernamen';
$UNB_T['cp.disallowed usernames~'] = 'Eine Registrierung mit einem dieser Benutzernamen bzw. einer dieser E-Mail-Adressen ist nicht möglich. Diese Zeichenfolgen dürfen nicht im angegebenen Benutzernamen bzw. der E-Mail-Adresse vorkommen. Groß-/Kleinschreibung wird ignoriert. Gib alle Werte durch ein „|“-Zeichen getrennt ein.';
$UNB_T['cp.disallowed emails'] = 'Unzulässige E-Mail-Adressen';
$UNB_T['cp.allowed email domains'] = 'Genehmigte E-Mail-Domains';
$UNB_T['cp.allowed email domains~'] = 'Wenn die Liste nicht leer ist, ist eine Registrierung nur mit E-Mail-Adressen dieser Domains möglich. Groß-/Kleinschreibung wird ignoriert. Gib alle Werte durch ein „|“-Zeichen getrennt ein.';
$UNB_T['cp.disallow email reuse'] = 'E-Mail-Adressen dürfen nicht wiederverwendet werden';
$UNB_T['cp.disallow email reuse~'] = 'Eine Registrierung mit einer E-Mail-Adresse, die bereits ein anderer Benutzer verwendet, ist nicht möglich.';
$UNB_T['cp.username minlength'] = 'Mindestlänge für Benutzernamen';
$UNB_T['cp.username minlength.unit'] = 'Zeichen';
$UNB_T['cp.username minlength~'] = '';
$UNB_T['cp.username maxlength'] = 'Höchstlänge für Benutzernamen';
$UNB_T['cp.username maxlength.unit'] = 'Zeichen';
$UNB_T['cp.username maxlength~'] = '';
$UNB_T['cp.usertitle maxlength'] = 'Höchstlänge für Benutzertitel';
$UNB_T['cp.usertitle maxlength.unit'] = 'Zeichen';
$UNB_T['cp.usertitle maxlength~'] = '';
$UNB_T['cp.password minlength'] = 'Mindestlänge für Kennwörter';
$UNB_T['cp.password minlength.unit'] = 'Zeichen';
$UNB_T['cp.password minlength~'] = '';
$UNB_T['cp.password not username'] = 'Benutzername als Kennwort verbieten';
$UNB_T['cp.password not username~'] = 'Ein Kennwort darf nicht gleich dem Benutzernamen sein. Groß-/Kleinschreibung wird ignoriert.';
$UNB_T['cp.password need number'] = 'Kennwort muss Zahlen enthalten';
$UNB_T['cp.password need number~'] = 'Ein Kennwort muss mindestens eine Zahl (0-9) enthalten.';
$UNB_T['cp.password need special'] = 'Kennwort muss Sonderzeichen enthalten';
$UNB_T['cp.password need special~'] = 'Ein Kennwort muss mindestens ein Zeichen enthalten, das keine Zahl (0-9) und kein Buchstabe (A-Z, a-z) ist. Hierzu zählen z.B. Satzzeichen oder Umlaute.';

$UNB_T['cp.avatars enabled'] = 'Avatare verwenden';
$UNB_T['cp.avatars enabled~'] = 'Ermöglicht Benutzern, Avatare zu verwenden. Diese kleinen Grafiken werden neben jedem Beitrag des jeweiligen Benutzers angezeigt und können ein Foto des Autors darstellen oder einfach der schnelleren Zuordnung des Beitrags zum Autor dienen. Benutzer können einen Avatar in ihren Einstellungen hochladen, diese Datei wird vom Forum verwaltet.';
$UNB_T['cp.allow remote avatar'] = 'Avatare von anderen Webadressen laden';
$UNB_T['cp.allow remote avatar~'] = 'Erlaubt es Benutzern, eigene Avatare von anderen Webadressen anzuzeigen. Es wird dabei keine Datei ins Forum hochgeladen, stattdessen gibt der Benutzer eine URL zum Bild an. Um die Größenbeschränkung zu wahren, wird die aktuelle Bildgröße gespeichert und für die Anzeige der Grafik festgehalten.';
$UNB_T['cp.maximum avatar size'] = 'Maximale Avatargröße';
$UNB_T['cp.maximum avatar size~'] = 'Lädt ein Benutzer eine größere Grafik hoch, wird diese automatisch verkleinert. Avatare von anderen Webadressen können nicht verkleinert werden. Ein Wert von „0“ deaktiviert die Verwendung von Avataren.';
$UNB_T['cp.photos enabled'] = 'Benutzerfotos verwenden';
$UNB_T['cp.photos enabled~'] = 'Ermöglicht Benutzern, ein Foto von sich in ihr Benutzerprofil einzubinden.';
$UNB_T['cp.maximum photo size'] = 'Maximale Fotogröße';

$UNB_T['cp.maximum post length'] = 'Maximale Beitragslänge';
$UNB_T['cp.maximum post length.unit'] = 'Zeichen';
$UNB_T['cp.maximum post length~'] = '';
$UNB_T['cp.maximum signature length'] = 'Maximale Signaturlänge';
$UNB_T['cp.maximum signature length.unit'] = 'Zeichen';
$UNB_T['cp.maximum signature length~'] = 'Gibt die maximal zulässige Länge für Signaturen an. Dieser Wert sollte nicht zu groß sein, da lange Signaturen viel Platz neben den Beiträgen verbrauchen. Ein Wert von „0“ deaktiviert die Verwendung von Signaturen.';
$UNB_T['cp.maximum attachment size'] = 'Maximale Attachmentgröße';
$UNB_T['cp.maximum attachment size.unit'] = 'Bytes';
$UNB_T['cp.maximum attachment size~'] = 'Gibt die maximale Dateigröße für Beitrags-Attachments an. Diese Einstellung kann durch die lokale PHP-Konfiguration reduziert sowie durch weitere Zugriffsregeln verändert werden.';
$UNB_T['cp.attachment extensions'] = 'Zulässige Dateierweiterungen für Attachments';
$UNB_T['cp.attachment extensions~'] = 'Wenn die Liste nicht leer ist, werden nur Beitrags-Attachments mit einer dieser Dateierweiterungen akzeptiert. Groß-/Kleinschreibung wird ignoriert. Gib alle Werte durch ein „|“-Zeichen getrennt ein.';
$UNB_T['cp.minimum topic subject length'] = 'Minimale Länge des Themenbetreffs';
$UNB_T['cp.minimum topic subject length.unit'] = 'Zeichen';
$UNB_T['cp.minimum topic subject length~'] = 'Gibt die Mindestlänge für einen Themenbetreff an. Kürzere Betreffe werden nicht akzeptiert.';
$UNB_T['cp.maximum topic subject length'] = 'Maximale Länge des Themenbetreffs';
$UNB_T['cp.maximum topic subject length.unit'] = 'Zeichen';
$UNB_T['cp.maximum topic subject length~'] = 'Gibt die Höchstlänge für einen Themenbetreff an. Längere Betreffe werden nicht akzeptiert.';
$UNB_T['cp.abbc signature no font'] = 'Keine Schriftformatierung in Signaturen';
$UNB_T['cp.abbc signature no font~'] = 'Verbietet die Verwendung von Formatierungstags zur Schriftformatierung in Signaturen. Das verhindert u.a. die Änderung der Schriftgröße oder Farbe.';
$UNB_T['cp.abbc signature no url'] = 'Keine Links in Signaturen';
$UNB_T['cp.abbc signature no url~'] = 'Verbietet die Verwendung von Linktags in Signaturen. Dadurch können keine Links mehr gesetzt werden. Diese Option wird i.A. nicht empfohlen.';
$UNB_T['cp.abbc signature no img'] = 'Keine Grafiken in Signaturen';
$UNB_T['cp.abbc signature no img~'] = 'Verbietet die Verwendung von Grafiken in Signaturen. Das verhindert, dass zu große Bilder sehr viel Platz neben den Beiträgen verbrauchen.';
$UNB_T['cp.abbc signature no smilies'] = 'Keine Smileys in Signaturen';
$UNB_T['cp.abbc signature no smilies~'] = 'Verbietet die Verwendung von Smileys in Signaturen. Smileys werden dann nicht durch ihre Grafiken ersetzt.';

$UNB_T['cp.no cookies'] = 'Keine Browser-Cookies setzen';
$UNB_T['cp.no cookies~'] = 'Unterbindet das Setzen von Cookies im Board.';
$UNB_T['cp.session ip netmask'] = 'IP-Adressmaske für Sitzungen';
$UNB_T['cp.session ip netmask.unit'] = 'Bit';
$UNB_T['cp.session ip netmask~'] = 'Um die Sicherheit der Anwender zu erhöhen, wird die IP-Adresse während der Sitzung überprüft. Weicht sie innerhalb dieser Adressmaske vom vorherigen Aufruf ab, wird die Sitzung beendet und der Benutzer abgemeldet. Der Wert 24 Bit entspricht z.B. der Adressmaske 255.255.255.0.';
$UNB_T['cp.use veriword'] = 'Grafische Prüfcodes verwenden';
$UNB_T['cp.use veriword~'] = 'Verwendet Grafiken, deren Inhalt vom Benutzer abgetippt werden muss, um bestimmte Aktionen wie z.B. die Registrierung oder das Verfassen von Beiträgen durchzuführen. Dies ist nur für nicht angemeldete Benutzer notwendig und dient dem Schutz für automatisierten Robotern, die diese Formulare selbstständig ausfüllen und sie dadurch für fremde Zwecke missbrauchen können. Das Verfahren ist auch als <a href="' . UnbLink('http://de.wikipedia.org/wiki/Captcha', null, true, /*sid*/false, /*derefer*/ true) . '">CAPTCHA</a> bekannt. Diese Funktion erfordert die PHP-Erweiterung <a href="' . UnbLink('http://www.boutell.com/gd/', null, true, /*sid*/false, /*derefer*/ true) . '">GD-Lib</a> und wird bei der Installation automatisch aktiviert, falls möglich.';
$UNB_T['cp.autoban flood ip'] = 'IPs automatisch blockieren';
$UNB_T['cp.autoban flood ip~'] = 'Sperrt automatisch IP-Adressen für einige Zeit, die in zu schneller Folge Seiten im Forum abrufen. Dies dient dem Schutz vor Denial-of-Service-Angriffen, bei denen der Webserver durch automatisiertes, sehr häufiges Abrufen von Seiten stark verlangsamt wird.';
$UNB_T['cp.autoban.on more than'] = 'IP blockieren bei mehr als';
$UNB_T['cp.autoban.requests in'] = 'Zugriffen in';
$UNB_T['cp.autoban.seconds'] = 'Sekunden';
$UNB_T['cp.admin lock'] = 'Administrative Boardsperre';
$UNB_T['cp.admin lock~'] = 'Mit dieser Sperre wird die Anmeldung und Benutzung des Boards nur Administratoren erlaubt. Alle anderen Benutzer und Gäste sehen nur den Hinweis, dass das Board vorübergehend gesperrt ist.';
$UNB_T['cp.admin lock message'] = 'Optional kann dem Sperrhinweis auch eine eigene Nachricht hinzugefügt werden. Formatierungscodes können hier verwendet werden.';
$UNB_T['cp.read only'] = 'Nur-Lesen-Modus';
$UNB_T['cp.read only~'] = 'Verweigert jede Aktion, die den Zustand des Forums verändern würde. Im Nur-Lesen-Modus können keine Benutzer registriert, keine Beiträge verfasst oder bearbeitet, keine Foren verändert werden usw. Diese Einstellung kann z.B. nützlich sein, wenn das Forum während Wartungsarbeiten noch lesbar bleiben soll.';
$UNB_T['cp.enable version check'] = 'Versionsprüfung aktivieren';
$UNB_T['cp.enable version check~'] = 'Prüft in regelmäßigen Abständen, ob die aktuell installierte Boardversion noch aktuell ist und ob wichtige Updates verfügbar sind. Dafür ist eine Internetverbindung zur <a href="' . UnbLink('http://newsboard.unclassified.de/', null, true, /*sid*/false, /*derefer*/ true) . '">Board-Webseite</a> notwendig. Bei dieser Prüfung werden keine persönlichen oder sicherheitsrelevanten Daten übertragen.';

// ----- Plug-ins -----

$UNB_T['cp.plugins list'] = 'Vorhandene Plug-Ins';
$UNB_T['cp.plugin info on x'] = 'Informationen zum Plug-In „{x}“';
$UNB_T['cp.plugin config of x'] = 'Plug-In-Konfiguration von „{x}“';
$UNB_T['cp.plugin.config'] = 'Einstellungen';
$UNB_T['cp.plugin.info'] = 'Info';
$UNB_T['cp.plugin.status.ok'] = 'OK';
$UNB_T['cp.plugin.status.disabled'] = 'Deaktiviert';
$UNB_T['cp.plugin.status.wrongversion'] = 'Falsche Version';
$UNB_T['cp.plugin.status.error'] = 'Fehler';
$UNB_T['cp.enable this plugin'] = 'Dieses Plug-In aktivieren';
$UNB_T['cp.enable this plugin~'] = '';

$UNB_T['cp.pluginfo.status'] = 'Status';
$UNB_T['cp.pluginfo.description'] = 'Beschreibung';
$UNB_T['cp.pluginfo.author'] = 'Autor';
$UNB_T['cp.pluginfo.languages'] = 'Sprachen';
$UNB_T['cp.pluginfo.version'] = 'Versionskompatibilität';
$UNB_T['cp.pluginfo.or newer'] = 'oder neuer';

?>