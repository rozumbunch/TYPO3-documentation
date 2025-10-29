<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTCAcolumns('be_groups', [
    'documentation_sources' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:documentationhub/Resources/Private/Language/locallang_db.xlf:be_groups.documentation_sources',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'enableMultiSelectFilterTextfield' => true,
            'items' => [], 
            'itemsProcFunc' => \Rozumbunch\DocumentationHub\TCA\ItemsProcFunc\DocumentationSourcesItemsProcFunc::class . '->getDocumentationSources',
            'size' => 8,
            'minitems' => 0,
            'maxitems' => 999,
            'autoSizeMax' => 10,
            'fieldControl' => [
                'addRecord' => [
                    'disabled' => true,
                ],
                'editPopup' => [
                    'disabled' => true,
                ],
            ],
            'fieldWizards' => [
                'recordsOverview' => [
                    'disabled' => true,
                ],
            ],
        ],
    ],
]);


ExtensionManagementUtility::addToAllTCAtypes(
    'be_groups',
    '--div--;LLL:EXT:documentationhub/Resources/Private/Language/locallang_db.xlf:be_groups.tab.documentation,documentation_sources',
    '',
    'after:description'
);
