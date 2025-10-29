# 03. Creating Content

Learn the basics of using the documentation extension.

## Adding New Pages

1. Create a new `.md` file in your documentation directory
2. Use standard Markdown syntax
3. The first heading (`#`) becomes the page title


## Basic Syntax

### Heading

# H1
## H2
### H3

### Bold

**bold text**

### Italic

*italicized text*

### Blockquote

> blockquote

### Ordered List

1. First item
2. Second item
3. Third item

### Unordered List

- First item
- Second item
- Third item

### Code

`code`

### Horizontal Rule

---

### Link

[Markdown Guide](https://www.markdownguide.org)

### Image

There are 3 options to add an image.
1. Recommended: store images in the extension’s Public folder
![Cool logos](EXT:documentationhub/Resources/Public/Documentation/Data/logo.png)
2. Intarnal, if images are located in a public folder, e.g., `/fileadmin/documentation/images/`
```
![Cool logos](/fileadmin/documentation/logo.svg)
```
3. External linking is not recommended.
```
![Cool logos](https://www.mydomain.com/somefilepath/image.jpg)  
```

### Table

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


### Strikethrough

~~The world is flat.~~

### Task List

- [x] Write the press release
- [ ] Update the website
- [ ] Contact the media

