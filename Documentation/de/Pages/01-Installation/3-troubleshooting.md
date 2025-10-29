# 03. Fehlerbehebung

Häufige Probleme und ihre Lösungen bei der Installation.

## Installationsprobleme

### Composer-Installation schlägt fehl

**Problem**: Composer kann die Extension nicht installieren.

**Lösung**:
1. Überprüfen Sie Ihre PHP-Version: `php -v`
2. Aktualisieren Sie Composer: `composer self-update`
3. Leeren Sie den Composer-Cache: `composer clear-cache`

### Berechtigungsfehler

**Problem**: Dateiberechtigungsfehler bei der Installation.

**Lösung**:
1. Überprüfen Sie die Dateiberechtigungen: `ls -la`
2. Setzen Sie die korrekten Berechtigungen: `chmod -R 755`
3. Stellen Sie sicher, dass der Webserver Schreibzugriff hat

## Konfigurationsprobleme

### Modul nicht sichtbar

**Problem**: Dokumentationsmodul im Backend nicht sichtbar.

**Lösung**:
1. Überprüfen Sie die Benutzerberechtigungen
2. Stellen Sie sicher, dass die Extension aktiviert ist
3. Leeren Sie die TYPO3-Caches
