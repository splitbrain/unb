<?php
// Unclassified NewsBoard
// Copyright 2003-5 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : install
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20051028
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// Installation
$UNB_T['installation'] = 'Installation';
$UNB_T['inst.db server'] = 'Datenbankserver';
$UNB_T['inst.db user'] = 'Benutzername';
$UNB_T['inst.db pass'] = 'Kennwort';
$UNB_T['inst.db name'] = 'Datenbankname';
$UNB_T['inst.db prefix'] = 'Präfix der Tabellennamen';
$UNB_T['inst.db prefix~'] = 'Diese Zeichenfolge wird den Standard-Tabellennamen vorangestellt. Verwende unterschiedliche Schlüssel, um mehr als ein Forum in einer einzigen Datenbank zu speichern.';

$UNB_T['inst.welcome'] = 'Willkommen zur UNB-Installation!';
$UNB_T['inst.assistant'] = 'Dieser Assistent wird das Unclassified NewsBoard, Version ' . $UNB['Version'] . ' ins aktuelle Verzeichnis installieren. Das bedeutet hauptsächlich die Einrichtung der Datenbank, die Dateien sind ja bereits entpackt. Bitte wähle jetzt die richtige Sprache aus, oder du musst später die Bezeichnungen der Standard-Benutzergruppen ändern.';
$UNB_T['inst.warn nowrite'] = 'Achtung: Das Schreiben der Konfigurationsdatei ist nicht möglich.<br />Du solltest das Problem beheben, indem du die Zugriffsrechte folgender Verzeichnisse im FTP-Programm änderst (CHMOD):<br />Hauptverzeichnis des Forums, board.conf.php (falls vorhanden), unb_lib/logs/, unb_lib/upload/, unb_lib/rsscache/, unb_lib/designs/*/tpl/cache/.';
$UNB_T['inst.type'] = 'Art der Installation';
$UNB_T['inst.type.new'] = 'Neue Installation';
$UNB_T['inst.type.new~'] = 'Bei einer neuen Installation werden die Tabellen in der Datenbank neu angelegt. Es werden keine alten Einträge oder Dateien übernommen oder gespeichert. Alle Daten, die in gleichnamigen Tabellen gespeichert sind, gehen verloren, falls sie bereits existieren.';
$UNB_T['inst.type.update'] = 'Eine ältere UNB-Version aktualisieren';
$UNB_T['inst.type.update~'] = 'Aktualisieren bedeutet die Konvertierung der Datenbank von einer früheren Unclassified NewsBoard-Version. Die Installation muss mit den Datenbank-Einstellungen erfolgen, mit denen die Daten zu finden sind. Alle nötigen Änderungen an der Datenbank werden automatisch durchgeführt.';
$UNB_T['inst.type.import'] = 'Aus anderem Newsboard importieren';
$UNB_T['inst.type.import~'] = 'Beim Importieren werden alle kompatiblen Daten eines anderen Newsboards in eine neue UNB-Installation kopiert. Abhängig vom Newsboard können nicht alle Daten korrekt übertragen werden. Du solltest deshalb zuerst alle Zugriffsrechte prüfen.';
$UNB_T['inst.type.uninstall'] = 'Board Deinstallieren';
$UNB_T['inst.type.uninstall~'] = 'Alle Datenbank-Tabellen des derzeit installierten UNB entfernen. Alle gespeicherten Daten gehen dabei verloren. Du musst die Dateien (Programm, Designs, Benutzer-Uploads) anschließend selbst entfernen.';
$UNB_T['inst.board title'] = 'Name des Forums';
$UNB_T['inst.board title~'] = 'Dieser Titel wird u.a. als Seitentitel und als Absendername für E-Mails verwendet.';
$UNB_T['inst.continue'] = 'Installation fortsetzen';

$UNB_T['inst.create tables'] = 'Erstelle Datenbank-Tabellen...';
$UNB_T['inst.grp guests'] = 'Gäste';
$UNB_T['inst.grp users'] = 'Mitglieder';
$UNB_T['inst.grp gmods'] = 'Globale Moderatoren';
$UNB_T['inst.grp admins'] = 'Administratoren';
$UNB_T['inst.login'] = 'Anmeldung als Benutzer „Admin“ mit Kennwort „admin“...';
$UNB_T['inst.done'] = 'Fertig.';
$UNB_T['inst.db complete'] = 'Datenbank-Setup abgeschlossen.';
$UNB_T['inst.complete~'] = 'Du solltest zuerst deinen Benutzernamen und dein Kennwort ändern. Um die Installation abzuschließen, gehe auf die Konfigurationsseite und richte die Einstellungen ein. Dann kannst du die Foren anlegen, die du deinen Benutzern anbieten willst, und die Zugriffsrechte vergeben. Zum Schluss musst du noch die administrative Board-Sperre in der Konfiguration aufheben.';
$UNB_T['inst.upgrade~'] = 'Um sicherzustellen, dass die Datenbank in einem einwandfreien Zustand ist, solltest du auf jeden Fall vom Admin Control Panel aus die Datenbank prüfen und eventuelle Fehler reparieren!';
$UNB_T['inst.lock'] = 'Die Installationsprogramme sind jetzt gesperrt und können nicht aufgerufen werden, so lange die Datei „lock.conf“ in diesem Verzeichnis existiert. Trotzdem solltest du die Installations-Dateien („install.php“ und „import_*.php“) aus dem Verzeichnis löschen, um Missbrauch zu verhindern.';
$UNB_T['inst.go cp'] = 'Weiter zum Control Panel';
$UNB_T['inst.go overview'] = 'Weiter zur Foren-Übersicht';

$UNB_T['inst.check db layout'] = 'Prüfe aktuelles Datenbank-Layout...';
$UNB_T['inst.check db integrity'] = 'Prüfe Datenbank-Integrität...';

$UNB_T['inst.import~'] = 'Wähle das Newsboard-System aus dem die Daten übernommen werden sollen. Um hier weitere Konverter hinzuzufügen, müssen diese „import_*.php“ heißen und im gleichen Verzeichnis wie „install.php“ liegen.';
$UNB_T['inst.import.not found'] = 'Keine Import-Module gefunden.';

$UNB_T['inst.error.upgrade fatal error'] = 'Beim Aktualisieren der Datenbank ist ein Fehler aufgetreten, nach dem das Programm nicht fortgesetzt werden konnte. Die Datenbank ist jetzt wahrscheinlich inkonsistent und sollte durch eine Sicherungskopie ersetzt werden. Notiere bitte die folgende Fehlermeldung und sende sie an die UNB-Entwickler. Eine ausführlichere Fehlerbeschreibung befindet sich im Fehlerlog des Forums.';

// Uninstallation
$UNB_T['uninstallation'] = 'Deinstallation';
$UNB_T['uninst.sure'] = 'Sicher alle Tabellen aus der Datenbank löschen!';
$UNB_T['uninst~'] = 'Das betrifft nur Daten, die in der Datenbank gespeichert sind. Es werden keine Dateien gelöscht.';
$UNB_T['uninst.summary'] = 'Entferne diese Datenbank-Tabellen:';
$UNB_T['uninst.remove table'] = 'Entferne Tabelle';
$UNB_T['uninst.complete'] = 'Deinstallation abgeschlossen.';

?>