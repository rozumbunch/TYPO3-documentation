<?php

return [
    'help_DocumentationMod' => [
        'parent' => 'help',
        'position' => [],
        'access' => 'user',
        'path' => '/module/documentationhub',
        'iconIdentifier' => 'extension-documentationhub',
        'labels' => 'LLL:EXT:documentationhub/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Documentationhub',
        'controllerActions' => [
            \Rozumbunch\Documentationhub\Controller\DocumentationhubModuleController::class => [
                'index', 'tree', 'content', 'exportPdf', 'exportPagePdf', 'exportSourcePdf', 'debug', 'debugTemplate'
            ],
        ],
    ],
];
