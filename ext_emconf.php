<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Documentation Hub',
    'description' => 'Backend documentation with markdown, directory structure, and PDF export. Displays an entry in the top help menu.',
    'category' => 'be',
    'author' => 'Rozumbunch',
    'author_email' => 'contact@rozumbunch.de',
    'state' => 'stable',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Rozumbunch\\Documentationhub\\' => 'Classes/',
        ],
    ],
];
