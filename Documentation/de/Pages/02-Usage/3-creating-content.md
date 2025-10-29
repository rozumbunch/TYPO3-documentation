# 03. Inhalte erstellen

Lerne die Grundlagen der Nutzung des Dokumentation Extension.

## Neue Seiten hinzufügen

1. Erstelle eine neue `.md` -Datei in deinem Dokumentationsverzeichnis
2. Verwende die Standard-Markdown-Syntax
3. Die erste Überschrift (`#`) wird zum Seitentitel


## Einfache Syntax

### Überschrift

# H1
## H2
### H3

### Fett

**bold text**

### Italic

*italicized text*

### Blockzitat

> blockquote

### Geordnete Liste

1. First item
2. Second item
3. Third item

###  Ungeordnete Liste

- First item
- Second item
- Third item

### Code Inline

`code`

### Horizontale Linie

---

### Link

[Markdown Guide](https://www.markdownguide.org)

### Bild

Es gibt 3 Möglichkeiten, ein Bild einzubinden.
1. Empfohlen: Bilder im Public-Ordner der Extension speichern
![Cool logos](EXT:documentationhub/Resources/Public/Documentation/Data/logo.png)
2. Intern, wenn sich die Bilder in einem öffentlichen Ordner befinden, z. B. in , `/fileadmin/documentation/images/`
```
![Cool logos](/fileadmin/documentation/logo.svg)
```
3. Externe Verlinkung wird nicht empfohlen.
```
![Cool logos](https://www.mydomain.com/somefilepath/image.jpg)  
```

### Tabelle

| Syntax | Description |
| ----------- | ----------- |
| Header | Title |
| Paragraph | Text |

### Fenced Code Block

```
{
  "firstName": "John",
  "lastName": "Smith",
  "age": 25
}
```


### Durchgestrichen

~~The world is flat.~~

### Aufgabe Liste

- [x] Write the press release
- [ ] Update the website
- [ ] Contact the media

