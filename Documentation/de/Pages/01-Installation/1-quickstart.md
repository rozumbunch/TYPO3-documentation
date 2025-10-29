# 01. Installation und Konfiguration


1. Erweiterung via Composer installieren

```bash
composer require rozumbunch/documentationhub
```
2. Quellen konfigurieren

Jede Dokumentationsquelle kann konfiguriert werden mit:
- Pfad: Speicherort der Dokumentationsdateien
- Titel: Anzeigename in der Navigation
- Icon: Icon für die Quelle
- Farbe: Themenfarbe für die Quelle


```
YourExtensionNam(sitepackage)
├─ Configuration
│  └─ SourcesForDocumentation.php


return [
    'sitepackage' => [
        'title' => 'Sitepackage Documentation',
        'path' => 'EXT:sitepackage/Documentation/',
        'enabled' => true,
        'icon' => '🏠',
        'color' => '#28a745',
        'description' => 'Documentation for the Sitepackage ...'
    ],
];

```
3. Fügen die Dokumentationsdateien hinzu


```text
YourExtensionNam(sitepackage)
├─ Documentation
│  └─ base
│  │  ├─ Pages
│  │    ├─ 01-Chapter
│  │       └─ intro.md
│  │       └─ 01-page.md
│  │       └─ 02-page.md
│  │    └─ 02-Chapter
│  │       └─ intro.md
│  │  └─ index.md
│  └─ de
│  │  ├─ Pages
│  │  └─ index.md

```


4. Backend-Modul zugänglich machen

Zugriff über „Backend-Benutzergruppe“ gewähren:

- Der Gruppe Zugriff auf das Modul geben.

![Access module](EXT:documentationhub/Resources/Public/Documentation/Data/access-modul.png)

- Auswählen, welche Dokumentationsquellen für Nutzer dieser Gruppe verfügbar sind.

![Select accessible sources for usergroup](EXT:documentationhub/Resources/Public/Documentation/Data/access-to-soerce-usergroup.png)
