# 01. Benutzerhandbuch für Integrator/Entwickler

Dieses Kapitel beschreibt die Verwendung der Erweiterung aus Sicht eines Integrator-/Entwickler-Benutzers.

## Wie ist die Dateistruktur zu verstehen?

## 1. Mehrsprachige Ordnerstruktur

Wenn keine Übersetzung der Dokumentation in andere Sprachen erforderlich ist, können Sie alle Dateien im Ordner
`Documentation/base` ablegen.
Wenn eine Übersetzung erforderlich ist, verwenden Sie Sprachcode-Abkürzungen, zum Beispiel `en` oder `de`.

Der Backend-Benutzer sieht die Dokumentation in der Sprache, die in seinen Einstellungen ausgewählt ist, oder den Inhalt aus `base`, wenn seine Sprache nicht gefunden wird.

## 2. Ordnerstruktur und Inhaltsverzeichnis

Im Stammverzeichnis jedes Sprachordners befindet sich eine Datei `index.md` dies ist die Hauptseite der Dokumentation.
Am selben Ort befindet sich der Ordner `Pages`, der den übrigen Inhalt enthält.

```text
YouExtensionName(sitepackage)
├─ Documentation
│  └─ de
│  │  ├─ Pages
│  │  └─ index.md
```

- Verwenden Sie Ordner, um verwandte Inhalte zu organisieren
- Benennen Sie Ordner mit aussagekräftigen Namen
- Nutzen Sie konsistente Namenskonventionen

### Navigationsstruktur
Die Navigation wird automatisch generiert basierend auf:

* Ordnerstruktur: bildet die Kategorien-Hierarchie
* Dateinamen: dienen als Seitentitel
* Erste Überschrift: überschreibt den Dateinamen als Titel


### Best Practices

* Halten Sie Seitentitel prägnant
* Verwenden Sie aussagekräftige Ordnernamen
* Bewahren Sie konsistente Formatierung
* Testen Sie Inhalte in verschiedenen Sprachen

## Hinweise

> **Wichtig**: Wenn Sie Inhalte auf Ordner-Ebene platzieren möchten, müssen Sie in diesem Ordner eine Datei mit dem Namen `intro.md` erstellen.
