<?php
// Unclassified NewsBoard
// Copyright 2003-8 by Yves Goergen
// Homepage: http://newsboard.unclassified.de
// See docs/license.txt for distribution/license details
//
// Additional language resource file
//
// Language : German (de)
// Part     : controlpanel
// Encoding : UTF-8 (without BOM) (auto-detect: °°°°°)
// Author   : Yves Goergen <contact@unclassified.de>
// Last edit: 20080804
// Reference: none (primary translation)
//
// See this web page for information on how to edit this file:
// http://newsboard.unclassified.de/devel/docs/translating

// ---------- Category names ----------
$UNB_T['cp.category.summary'] = 'Zusammenfassung';
$UNB_T['cp.category.account'] = 'Accountdaten';
$UNB_T['cp.category.user settings'] = 'Benutzer-Einstellungen';
$UNB_T['cp.category.appearance'] = 'Anzeigeoptionen';
$UNB_T['cp.category.post options'] = 'Beitragsoptionen';
$UNB_T['cp.category.watched topics'] = 'Beobachtete Themen';
$UNB_T['cp.category.bookmarks'] = 'Lesezeichen';
$UNB_T['cp.category.topic filter'] = 'Themenfilter';

$UNB_T['cp.show profile'] = 'Profil anzeigen';

$UNB_T['cp.currently editing user x'] = 'Du befindest dich gerade im Profil von {x}.';
$UNB_T['cp.settings saved'] = 'Die Einstellungen wurden gespeichert.';

// ---------- Summary category ----------
$UNB_T['cp.summary'] = 'Zusammenfassung';
$UNB_T['cp.remove user'] = 'Benutzer löschen';
$UNB_T['cp.find posts'] = 'Suchen';

// ---------- Account category ----------
$UNB_T['cp.account and profile'] = 'Accountdaten und Benutzerprofil';
$UNB_T['cp.username~'] = 'Dieser Name dient gleichzeitig als Loginname und wird als Autorname bei jedem Beitrag angezeigt.';
$UNB_T['cp.username~noedit'] = 'Du kannst diesen Namen nicht ändern.';
$UNB_T['cp.groups~'] = 'Ein Benutzer kann Mitglied einer oder mehrerer Gruppen sein, um weitere Rechte zu erlangen.';
$UNB_T['cp.e-mail~'] = 'Diese Adresse wird nicht öffentlich angezeigt und für Benachrichtigungen, Mitteilungen von anderen Benutzern und zum Zurücksetzen des Kennworts verwendet.';
$UNB_T['cp.e-mail~need password'] = 'Zum Ändern der Adresse muss das aktuelle Kennwort eingegeben werden.';
$UNB_T['cp.validated e-mail~'] = 'Folgende E-Mail-Adresse wurde geprüft und wird für Benachrichtigungen verwendet:';
$UNB_T['cp.password~'] = 'Das Kennwort wird zum Anmelden im Forum benötigt. Du solltest es geheim halten und niemandem verraten.';
$UNB_T['cp.password~need password'] = 'Zum Ändern des Kennworts muss das aktuelle Kennwort eingegeben werden.';
$UNB_T['cp.current password'] = 'Aktuelles Kennwort';
$UNB_T['cp.new password'] = 'Neues Kennwort';
$UNB_T['cp.repeat new password'] = 'Wiederhole neues Kennwort';
$UNB_T['cp.user title~'] = 'Dieser Titel wird in deinen Beiträgen neben deinem Benutzernamen angezeigt.';

$UNB_T['cp.contact info'] = 'Kontaktinformationen';
$UNB_T['cp.contact info~'] = 'Hier kannst du alle Adressen angeben, unter denen man dich erreichen kann. Jeder Kontakttyp darf nur einmal angegeben werden. Weitere Zeilen erscheinen nach dem Speichern.';
$UNB_T['cp.contact.select'] = 'Auswahl';

$UNB_T['cp.personal info'] = 'Persönliche Informationen';
$UNB_T['cp.birthdate.day'] = 'Tag';
$UNB_T['cp.birthdate.month'] = 'Monat';
$UNB_T['cp.birthdate.year'] = 'Jahr';
$UNB_T['cp.description'] = 'Beschreibung';
$UNB_T['cp.description~'] = 'Diese Beschreibung wird im Benutzerprofil angezeigt. Du kannst hier persönliche Interessen oder andere Dinge über dich notieren.';

$UNB_T['cp.additional info'] = 'Zusätzliche Angaben';

// ---------- Appearance category ----------
$UNB_T['cp.design'] = 'Design';
$UNB_T['cp.design~'] = 'Es stehen mehrere Designs zur Verfügung, aus denen du eines wählen kannst, um das Aussehen der Foren-Oberfläche zu ändern.';
$UNB_T['cp.design preview~'] = 'Öffnet das Forum mit dem ausgewählten Design in einem neuen Fenster.';
$UNB_T['cp.language~'] = 'Legt die Sprache fest, in der die Forenoberfläche angezeigt wird. „Automatisch“ berücksichtigt die Browsereinstellung und die Vorgabe des Administrators, um eine geeignete Sprache zu finden.';
$UNB_T['cp.date and time'] = 'Datum und Uhrzeit';
$UNB_T['cp.date format'] = 'Datumsformat';
$UNB_T['cp.date format~'] = 'Diese Angabe legt die Darstellung von Datumsangaben fest. Du kannst alle Formate verwenden, die im PHP-Handbuch zur Funktion <a href="' . UnbLink('http://de.php.net/manual/de/function.date.php', null, true, /*sid*/false, /*derefer*/ true) . '">date()</a> beschrieben sind. Die Zeitangaben werden davon nicht beeinflusst. Du solltest keine Zeitinformationen verwenden, das Forum fügt die Uhrzeit automatisch hinzu.';   // FOR TRANSLATORS: Please use a localised version of the PHP manual if one is available, otherwise the English version at: http://php.net/date
$UNB_T['cp.timezone'] = 'Zeitzone';
$UNB_T['cp.timezone~'] = 'Wähle deine lokale Zeitzone aus, damit die Zeitangaben im Forum korrekt berechnet werden.';
$UNB_T['cp.use dst'] = 'Sommerzeit berücksichtigen';
$UNB_T['cp.use dst~'] = 'Aktiviert automatisch die Sommerzeit, sofern der Server das unterstützt.';
$UNB_T['cp.force dst'] = 'Sommerzeit aktivieren';
$UNB_T['cp.force dst~'] = 'Aktiviert die Sommerzeit ohne automatische Umstellung.';
$UNB_T['cp.current time'] = 'Aktuelle Zeit';

$UNB_T['cp.more options'] = 'Weitere Einstellungen';
$UNB_T['cp.small avatars'] = 'Kleine Avatare';
$UNB_T['cp.small avatars~'] = 'Benutzeravatare, die größer als die Hälfte der maximalen Größe sind, werden um die Hälfte verkleinert angezeigt.';
$UNB_T['cp.fast reply'] = 'Schnellantwort';
$UNB_T['cp.fast reply~'] = 'Zeigt ein einfaches Antwortfeld in der Themenansicht an, um schnell Antworten abzusenden.';
$UNB_T['cp.auto login'] = 'Automatischer Login';
$UNB_T['cp.auto login~'] = 'Speichert ein Cookie mit den Zugangsdaten im Browser, um dich bei späteren Besuchen automatisch anzumelden.';
$UNB_T['cp.auto ignore'] = 'Themen automatisch ignorieren';
$UNB_T['cp.auto ignore~'] = 'Ignoriert automatisch alle ungesehenen Themen beim Markieren aller Themen als ‚gelesen‘. Alle Themen, die du bis dahin noch überhaupt nicht gelesen hast, werden danach nicht mehr in der Liste der neuen bzw. ungelesenen Themen angezeigt.';
$UNB_T['cp.hide avatars'] = 'Avatare ausblenden';
$UNB_T['cp.hide avatars~'] = 'Zeigt in der Themenansicht keine Benutzeravatare an.';
$UNB_T['cp.hide signatures'] = 'Signaturen ausblenden';
$UNB_T['cp.hide signatures~'] = 'Zeigt in der Themenansicht keine Beitragssignaturen an.';
$UNB_T['cp.hide inline image'] = 'Bildanlagen ausblenden';
$UNB_T['cp.hide inline image~'] = 'Zeigt in der Themenansicht keine an Beiträge angehängten Bilder an, sondern bietet sie wie alle anderen Anlagen zum Herunterladen an.';

// ---------- Post options category ----------
$UNB_T['cp.post signature'] = 'Beitragssignatur';
$UNB_T['cp.post signature~'] = 'Diese Unterschrift wird an jeden deiner Beiträge angehängt. Du kannst darin z.B. auf aktuell interessante Webseiten verweisen oder einen persönlichen Spruch eintragen. Die Signatur sollte nicht zu lang sein, da sie sonst viel Platz in der Beitragsansicht verbraucht. Die maximale Länge beträgt {length} Zeichen. Einige Formatierungsmöglichkeiten können hier u.U. deaktiviert sein.';
$UNB_T['cp.current signature'] = 'Aktuelle Signatur';
$UNB_T['cp.no current signature'] = 'Keine Signatur gespeichert.';
$UNB_T['cp.post signature preview~'] = 'Die Vorschau betrifft nur die Anzeige der Signatur. Bilddateien werden nicht gespeichert.';

$UNB_T['cp.avatar'] = 'Avatar';
$UNB_T['cp.avatar~'] = 'Diese kleine Grafik wird neben jedem deiner Beiträge angezeigt, um diese schneller erkennen zu können. Die Grafik sollte klein aber erkennbar sein. Die maximale Größe beträgt {maxwidth}&times;{maxheight} Pixel und {maxsize}.';
$UNB_T['cp.avatar~tip'] = '<b>Tipp:</b> Wenn das Bild nach einer Änderung nicht aktualisiert wurde, liegt das möglicherweise am Browsercache. Lade dann zuerst einmal die Seite neu.';
$UNB_T['cp.no avatar set'] = 'Kein Avatar gespeichert.';
$UNB_T['cp.upload new avatar'] = 'Neuen Avatar hochladen';
$UNB_T['cp.load avatar from url'] = 'Avatar von URL anzeigen';
$UNB_T['cp.use gravatar'] = 'Meinen Gravatar verwenden';
$UNB_T['cp.use gravatar~'] = '(Was ist ein <a href="' . UnbLink('http://www.gravatar.com/', null, true, /*sid*/false, /*derefer*/ true) . '">Gravatar</a>?)';
$UNB_T['cp.remove avatar'] = 'Vorhandenen Avatar löschen';
$UNB_T['cp.current avatar size'] = 'Der aktuelle Avatar hat eine Größe von {width}&times;{height} Pixel und {size}.';

$UNB_T['cp.user photo'] = 'Benutzerfoto';
$UNB_T['cp.user photo~'] = 'Dieses Bild wird in deinem Benutzerprofil angezeigt. Es sollte ein echtes und möglichst aktuelles Foto von dir sein. Andere Bilder sind evtl. unerwünscht. Die maximale Größe beträgt {maxwidth}&times;{maxheight} Pixel und {maxsize}.';
$UNB_T['cp.user photo~tip'] = '<b>Tipp:</b> Wenn das Bild nach einer Änderung nicht aktualisiert wurde, liegt das möglicherweise am Browsercache. Lade dann zuerst einmal die Seite neu.';
$UNB_T['cp.no photo set'] = 'Kein Benutzerfoto gespeichert.';
$UNB_T['cp.upload new photo'] = 'Neues Foto hochladen';
$UNB_T['cp.load photo from url'] = 'Foto von URL anzeigen';
$UNB_T['cp.remove photo'] = 'Vorhandenes Foto löschen';
$UNB_T['cp.current photo size'] = 'Das aktuelle Foto hat eine Größe von {width}&times;{height} Pixel und {size}.';

// ---------- Watched topics category ----------
$UNB_T['cp.watched topics settings'] = 'Einstellungen zu beobachteten Themen';
$UNB_T['cp.default notification'] = 'Standardbenachrichtigung';
$UNB_T['cp.default notification~'] = 'Bei jedem Thema, das du anfängst oder in dem du antwortest, wird automatisch diese Benachrichtigung aktiviert.';

$UNB_T['cp.currently watched topics'] = 'Aktive Benachrichtigungen';
$UNB_T['cp.currently watched topics~'] = 'Diese Liste enthält alle Foren und Themen, für die du eine Benachrichtigung über neue Themen bzw. Beiträge erhältst.';
$UNB_T['cp.n notifications set'] = '{n} Benachrichtigungen aktiviert.';
$UNB_T['cp.no notifications set'] = 'Keine Benachrichtigungen aktiviert.';
$UNB_T['cp.entire forum'] = 'Gesamtes Forum';
$UNB_T['cp.notify.remove older than.1'] = 'Benachrichtigungen älter als';
$UNB_T['cp.notify.remove older than.2'] = 'Tage entfernen';
$UNB_T['cp.remove selected subscriptions'] = 'Ausgewählte Benachrichtigungen entfernen';

// ---------- Bookmarks category ----------
$UNB_T['cp.bookmarks'] = 'Lesezeichen';
$UNB_T['cp.bookmarks~'] = 'Diese Liste enthält alle gespeicherten Lesezeichen.';
$UNB_T['cp.n bookmarks set'] = '{n} Lesezeichen gespeichert.';
$UNB_T['cp.no bookmarks set'] = 'Keine Lesezeichen gespeichert.';
$UNB_T['cp.bookmark.remove older than.1'] = 'Lesezeichen älter als';
$UNB_T['cp.bookmark.remove older than.2'] = 'Tage entfernen';
$UNB_T['cp.remove selected bookmarks'] = 'Ausgewählte Lesezeichen entfernen';

// ---------- Topic filter category ----------
$UNB_T['cp.topic filters'] = 'Themenfilter';
$UNB_T['cp.topic filter~'] = 'Diese Liste enthält alle gesetzten Themenfilter.';
$UNB_T['cp.n filters set'] = '{n} Filter gespeichert.';
$UNB_T['cp.no filters set'] = 'Keine Filter gespeichert.';
$UNB_T['cp.filter.remove older than.1'] = 'Filter älter als';
$UNB_T['cp.filter.remove older than.2'] = 'Tage entfernen';
$UNB_T['cp.remove selected filters'] = 'Ausgewählte Filter entfernen';

$UNB_T['cp.timezone.-48'] /* -1200 */ = 'Internationale Datumsgrenze (Westen)';
$UNB_T['cp.timezone.-44'] /* -1100 */ = 'Midway-Inseln, Samoa';
$UNB_T['cp.timezone.-40'] /* -1000 */ = 'Cook-Inseln, Hawaii (HST)';
$UNB_T['cp.timezone.-38'] /* -0930 */ = 'Französisch-Polynesien (teilweise)';
$UNB_T['cp.timezone.-36'] /* -0900 */ = 'Alaska (AKST)';
$UNB_T['cp.timezone.-32'] /* -0800 */ = 'Los Angeles, Seattle, Tijuana, Vancouver (PST)';
$UNB_T['cp.timezone.-28'] /* -0700 */ = 'Arizona, Calgary, Chihuahua, Salt Lake City (MST)';
$UNB_T['cp.timezone.-24'] /* -0600 */ = 'Chicago, Mexico City, Zentralamerika (CST)';
$UNB_T['cp.timezone.-20'] /* -0500 */ = 'Atlanta, Lima, New York, Toronto (EST)';
$UNB_T['cp.timezone.-16'] /* -0400 */ = 'Caracas, Santiago (AST)';
$UNB_T['cp.timezone.-14'] /* -0330 */ = 'Neufundland (NST)';
$UNB_T['cp.timezone.-12'] /* -0300 */ = 'Brasilien, Buenos Aires, Grönland';
$UNB_T['cp.timezone.-8']  /* -0200 */ = 'Mittelatlantik';
$UNB_T['cp.timezone.-4']  /* -0100 */ = 'Azoren, Kapverdische Inseln';
$UNB_T['cp.timezone.0']   /* +0000 */ = 'Casablanca, Dublin, London (WEZ)';
$UNB_T['cp.timezone.4']   /* +0100 */ = 'Berlin, Paris, Rom, Warschau (MEZ)';
$UNB_T['cp.timezone.8']   /* +0200 */ = 'Athen, Istanbul, Jerusalem, Kairo, Kiew (EET)';
$UNB_T['cp.timezone.12']  /* +0300 */ = 'Bagdad, Moskau, Nairobi (MSK)';
$UNB_T['cp.timezone.14']  /* +0330 */ = 'Teheran (IRT)';
$UNB_T['cp.timezone.16']  /* +0400 */ = 'Abu Dhabi';
$UNB_T['cp.timezone.18']  /* +0430 */ = 'Kabul';
$UNB_T['cp.timezone.20']  /* +0500 */ = 'Islamabad, Karatschi';
$UNB_T['cp.timezone.22']  /* +0530 */ = 'Neu-Delhi (IST)';
$UNB_T['cp.timezone.23']  /* +0545 */ = 'Katmandu';
$UNB_T['cp.timezone.24']  /* +0600 */ = 'Nowosibirsk';
$UNB_T['cp.timezone.26']  /* +0630 */ = 'Rangun';
$UNB_T['cp.timezone.28']  /* +0700 */ = 'Bankok, Jakarta (ICT)';
$UNB_T['cp.timezone.32']  /* +0800 */ = 'Kuala Lumpur, Peking, Perth, Singapur, Teipeh (CST)';
$UNB_T['cp.timezone.35']  /* +0845 */ = 'Australien (teilweise)';
$UNB_T['cp.timezone.36']  /* +0900 */ = 'Osaka, Tokio, Seoul';
$UNB_T['cp.timezone.38']  /* +0930 */ = 'Adelaide, Darwin (ACST)';
$UNB_T['cp.timezone.40']  /* +1000 */ = 'Canberra, Melbourne, Sydney, Wladiwostok (AEST)';
$UNB_T['cp.timezone.42']  /* +1030 */ = 'Lord Howe Island';
$UNB_T['cp.timezone.44']  /* +1100 */ = 'Salomonen, Neukaledonien';
$UNB_T['cp.timezone.46']  /* +1130 */ = 'Norfolk-Insel';
$UNB_T['cp.timezone.48']  /* +1200 */ = 'Auckland, Fidschi, Marshall-Inseln';
$UNB_T['cp.timezone.51']  /* +1245 */ = 'Neuseeland (teilweise)';
$UNB_T['cp.timezone.52']  /* +1300 */ = 'Nuku\'alofa';
$UNB_T['cp.timezone.56']  /* +1400 */ = 'Weihnachtsinsel';

// Error messages
$UNB_T['cp.error.user not deleted'] = 'Benutzer konnte nicht gelöscht werden.';
$UNB_T['cp.error.passwords dont match'] = 'Das Kennwort und dessen Wiederholung unterscheiden sich.';
$UNB_T['cp.error.password too short'] = 'Das Kennwort ist zu kurz. Es sind mindestens {n} Zeichen erforderlich.';
$UNB_T['cp.error.password is username'] = 'Das Kennwort darf nicht gleich dem Benutzernamen sein.';
$UNB_T['cp.error.password need number'] = 'Das Kennwort muss mindestens eine Zahl enthalten.';
$UNB_T['cp.error.password need special'] = 'Das Kennwort muss mindestens ein Sonderzeichen enthalten.';
$UNB_T['cp.error.password generic'] = 'Das Kennwort ist aus einem unbekannten Grund nicht sicher.';
$UNB_T['cp.error.username too short'] = 'Der Benutzername ist zu kurz. Es sind mindestens {min} Zeichen erforderlich.';
$UNB_T['cp.error.username too long'] = 'Der Benutzername ist zu lang. Es sind maximal {max} Zeichen zulässig.';
$UNB_T['cp.error.user title too long'] = 'Der Benutzertitel ist zu lang. Es sind maximal {max} Zeichen zulässig.';
$UNB_T['cp.error.invalid birthdate'] = 'Das Geburtsdatum ist ungültig.';
$UNB_T['cp.error.signature too long'] = 'Die Signatur ist zu lang. Es sind maximal {max} Zeichen zulässig.';
$UNB_T['cp.error.invalid password cp'] = 'Falsches Kennwort. Du kannst die E-Mail-Adresse oder das Kennwort nicht ändern.';
$UNB_T['cp.error.avatar not deleted'] = 'Avatar konnte nicht gelöscht werden.';
$UNB_T['cp.error.no dot in filename'] = 'Kein „.“ im Dateinamen';
$UNB_T['cp.error.invalid file ext'] = 'Unzulässige Dateierweiterung, zulässig: jpg, jpeg, gif, png';
$UNB_T['cp.error.file too big'] = 'Datei zu groß, Maximum {n} Bytes';
$UNB_T['cp.error.unknown file format'] = 'Dateiformat nicht erkannt';
$UNB_T['cp.error.image too wide'] = 'Bild zu breit, Maximum {n} Pixel';
$UNB_T['cp.error.image too high'] = 'Bild zu hoch, Maximum {n} Pixel';
$UNB_T['cp.error.file too big not resized'] = 'Datei zu groß, Maximum {n} Bytes, Verkleinern fehlgeschlagen';
$UNB_T['cp.error.image too wide not resized'] = 'Bild zu breit, Maximum {n} Pixel, Verkleinern fehlgeschlagen';
$UNB_T['cp.error.image too high not resized'] = 'Bild zu hoch, Maximum {n} Pixel, Verkleinern fehlgeschlagen';
$UNB_T['cp.error.unknown error'] = 'Unbekannter Fehler';
$UNB_T['cp.error.invalid avatar file'] = 'Ungültige Avatar-Datei';
$UNB_T['cp.error.avatar not saved'] = 'Hochgeladener Avatar konnte nicht gespeichert werden.';
$UNB_T['cp.error.photo not deleted'] = 'Foto konnte nicht gelöscht werden.';
$UNB_T['cp.error.invalid photo file'] = 'Ungültige Foto-Datei';
$UNB_T['cp.error.photo not saved'] = 'Hochgeladenes Foto konnte nicht gespeichert werden.';
$UNB_T['cp.error.one item not saved'] = 'Mindestens eine der Angaben konnte nicht gespeichert werden.';
$UNB_T['cp.error.form not complete'] = 'Das Formular wurde nicht vollständig ausgefüllt.';
$UNB_T['cp.error.message not sent'] = 'Die Nachricht konnte nicht gesendet werden.';
$UNB_T['cp.error.user has no email'] = 'Der Benutzer hat keine E-Mail-Adresse angegeben oder ist noch kein geprüftes Mitglied.';

$UNB_T['cp.error.too many extra fields'] = 'Zu viele Extra-Felder angegeben. Es sind maximal 10 zulässig.';
$UNB_T['cp.error.db setextracols'] = 'Datenbankfehler beim Ändern der Extra-Spalten';

?>