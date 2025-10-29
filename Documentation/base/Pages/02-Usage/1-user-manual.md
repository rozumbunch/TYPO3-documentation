# 01. User manual for integrator/developer

This chapter describes how to use the extension from from an integrator/developer  user point of view.

## How to understand the file structure?

## 1. Multi-language folder structure

If documentation translation into other languages is not required, you can place all files in the folder
`Documentation/base`.
If translation is required, use the language code abbreviations, for example `en` or `de`.

The backend user will see the documentation in the language selected in their settings, or the `base` content if their language is not found.

## 2. Folder structure and table of contents

In the root directory of each language folder, there is an `index.md` file — this is the main page of the documentation.
In the same place, there is a `Pages` folder that contains the remaining content.

```text
YouExtensionName(sitepackage)
├─ Documentation
│  └─ de
│  │  ├─ Pages
│  │  └─ index.md
```


- Use folders to organize related content
- Name folders with descriptive names
- Use consistent naming conventions

### Navigation Structure
Navigation is generated automatically based on:

* Folder structure: builds the category hierarchy
* File names: used as page titles
* First heading: overrides the file name as the title


### Best Practices

* Keep page titles concise
* Use descriptive folder names
* Maintain consistent formatting
* Test content in different languages

## Notes

> **Important**: If you want to place the content at the folder level, you need to create a file named `intro.md` inside that folder.
