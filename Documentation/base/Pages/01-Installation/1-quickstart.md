# Install and Configuration


1. Install the extension via Composer

```bash
composer require rozumbunch/documentationhub
```
2. Configure the sources

Each documentation source can be configured with:
- Path: Location of the documentation files
- Title: Display name in the navigation
- Icon: Icon for the source
- Color: Theme color for the source

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
3. Add your documentation files


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


4. Make the backend module accessible

Grant access via “Backend usergroup”:

- Give the group access to the module.

![Access module](EXT:documentationhub/Resources/Public/Documentation/Data/access-modul.png)

- Select which documentation sources are available to users of this group.

![Select accessible sources for usergroup](EXT:documentationhub/Resources/Public/Documentation/Data/access-to-soerce-usergroup.png)
