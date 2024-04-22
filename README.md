# Metadata Monitoring Skript für laut.fm Stationen

Das Metadata Monitoring Skript ist ein automatisiertes Skript zur Überwachung der Metadaten von laut.fm Stationen. Das Skript prüft regelmäßig, ob das letzte Titelupdate länger als 30 Minuten zurückliegt. Falls dies der Fall ist oder der aktuelle Track nicht mehr als live angezeigt wird, wird die Verbindung automatisch getrennt und eine E-Mail mit Details an die hinterlegte E-Mail-Adresse geschickt.

## Installation

Um das Metadata Monitoring Skript zu verwenden, folge diesen Schritten:

1. Lade die Dateien `live_check.php` und `config.php` auf deinen Server oder Webspace hoch.
2. Öffne die Datei `config.php` und passe die benötigten Daten an
   
   Beispiel:
	```
	$stations = [
	  [
	    "name"      =>  "STATIONSNAME",
	    "password"  =>  "XXXX-XXXX-XXXX-XXXX",
	    "mail"      =>  "mail@example.com",
	  ],
	];
	
	```

4. Stelle sicher, dass die Dateien `live_check.php` und `config.php` im selben Verzeichnis liegen. Es wird empfohlen, dafür einen eigenen Ordner wie z. B. "monitoring" anzulegen.
5. Konfiguriere einen Cronjob, der die Datei `live_check.php` regelmäßig aufruft.<br>
   Beachte, dass das Skript nur Requests in den Minuten 0, 15, 30 und 45 jeder Stunde akzeptiert.

## Voraussetzungen

Das Metadata Monitoring Skript erfordert:

- Einen Server oder Webspace
- PHP 7 oder höher
- Die cURL-Erweiterung für PHP
- Einen SMTP-Server für die PHP-Mail-Funktion

## Funktionsweise

Die Datei `live_check.php` führt folgende Aufgaben aus:

1. Sie ruft in den Minuten 0, 15, 30 und 45 Minuten die öffentliche API von laut.fm ab, um die Metadaten vom aktuellen Titel aller konfigurierten Stationen zu erhalten.
2. Es wird überprüft, ob das letzte Titelupdate älter als 30 Minuten ist und ob der letzte Eintrag live gespielt wurde.
3. Wenn die Metadaten älter als 30 Minuten sind oder der letzte Eintrag nicht live gespielt wurde, wird eine Zwangstrennung der Verbindung durchgeführt.
4. Nachdem die Verbindung getrennt wurde, wird eine E-Mail versendet, die über den Vorgang der Trennung und dessen Details informiert.

## Hinweis

Stelle sicher, dass die Konfigurationsdatei (`config.php`) korrekt eingestellt ist und die Live-Passwörter von allen Stationen aktuell sind. Außerdem achte darauf, dass der Cronjob korrekt eingerichtet ist, um die regelmäßige Ausführung des Skripts sicherzustellen.
