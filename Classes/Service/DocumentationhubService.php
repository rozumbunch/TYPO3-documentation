<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class DocumentationhubService
{
    /** @var array<string, array<string, mixed>> */
    private array $documentationSources;
    private MarkdownUtility $markdownUtility;
    private const DEFAULT_LANG = 'base';

    public function __construct(MarkdownUtility $markdownUtility)
    {
        $this->markdownUtility = $markdownUtility;
        $this->loadDocumentationSources();
    }

    /**
     * Returns all configured documentation sources
     */
    /** @return array<string, array<string, mixed>> */
    public function getDocumentationSources(): array
    {
        return $this->documentationSources;
    }

    /**
     * Loads documentation sources from configuration based on user groups
     */
    private function loadDocumentationSources(): void
    {
        $allSources = $this->getAllDocumentationSources();
        $this->documentationSources = $this->filterSourcesByUserGroups($allSources);
    }

    /**
     * Returns the combined HTML content of all index.md files
     */
    public function getIndexHtml(): string
    {
        $sources = $this->documentationSources;
        $combinedHtml = '';
        $foundAny = false;
        $userLanguage = $this->getUserLanguage();

        foreach ($sources as $sourceKey => $source) {
            $localizedSourcePath = $this->getLocalizedSourcePath($source['path'], $userLanguage);
            $indexPath = $localizedSourcePath . 'index.md';
            if (is_file($indexPath)) {
                $html = $this->markdownUtility->convertFileToHtml($indexPath);

                $combinedHtml .= '<div class="documentation-source" data-source="' . htmlspecialchars($sourceKey) . '">';
                $combinedHtml .= '<h1 class="source-title">' . htmlspecialchars($source['title']) . '</h1>';
                $combinedHtml .= $html;
                $combinedHtml .= '</div>';

                $combinedHtml .= '<hr class="source-separator">';

                $foundAny = true;
            }
        }

        if (!$foundAny) {
            $title = LocalizationUtility::translate('error.noDocumentationFound.title', 'documentationhub') ?? 'No Documentation Found';
            $message = LocalizationUtility::translate('error.noDocumentationFound.message', 'documentationhub') ?? 'No documentation files were found.';
            return sprintf('<h1>%s</h1><p>%s</p>', htmlspecialchars($title), htmlspecialchars($message));
        }

        $combinedHtml = rtrim($combinedHtml, '<hr class="source-separator">');

        return $combinedHtml;
    }

    /**
     * Creates the navigation structure from all enabled sources
     */
    /** @return array<int, array<string, mixed>> */
    public function getNavigation(): array
    {
        $navigation = [];
        $sources = $this->documentationSources;
        $userLanguage = $this->getUserLanguage();

        foreach ($sources as $sourceKey => $source) {
            $localizedSourcePath = $this->getLocalizedSourcePath($source['path'], $userLanguage);

            if (!is_dir($localizedSourcePath)) {
                continue;
            }

            $sourceGroup = [
                'title' => $source['title'],
                'path' => $sourceKey . '::index.md',
                'type' => 'source',
                'level' => 1,
                'source' => $sourceKey,
                'icon' => $source['icon'] ?? '📚',
                'color' => $source['color'] ?? '#6c757d',
                'expanded' => true,
                'pages' => []
            ];

            $indexPath = $localizedSourcePath . 'index.md';
            if (!is_file($indexPath)) {
                continue;
            }

            $pagesDir = $localizedSourcePath . 'Pages/';

            if (is_dir($pagesDir)) {
                $items = scandir($pagesDir);
                if ($items === false) {
                    continue;
                }

                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $itemPath = $pagesDir . '/' . $item;
                    if (is_dir($itemPath)) {
                        $category = $this->buildCategoryNavigation($sourceKey, $item, $itemPath);
                        $sourceGroup['pages'][] = $category;
                    }
                }
            }

            $navigation[] = $sourceGroup;
        }

        return $navigation;
    }

    /**
     * Gets the localized source path based on user language
     */
    private function getLocalizedSourcePath(string $basePath, string $userLanguage): string
    {
        $baseDir = GeneralUtility::getFileAbsFileName($basePath);
        $userPath = $baseDir . $userLanguage . '/';
        $basePath = $baseDir . self::DEFAULT_LANG . '/';

        if (is_dir($userPath)) {
            return $userPath;
        }

        if (is_dir($basePath)) {
            return $basePath;
        }

        return '';
    }

    /**
     * Builds navigation for a category (folder)
     */
    /** @return array<string, mixed> */
    private function buildCategoryNavigation(string $sourceKey, string $folderName, string $folderPath): array
    {
        $category = [
            'title' => $folderName,
            'path' => '',
            'type' => 'category',
            'level' => 1,
            'source' => $sourceKey,
            'icon' => '📁',
            'color' => '#6c757d',
            'expanded' => true,
            'pages' => []
        ];

        $introPath = $folderPath . '/intro.md';
        if (is_file($introPath)) {
            $title = $this->extractTitleFromFile($introPath);
            $category['title'] = $title;
            $category['path'] = $sourceKey . '::Pages/' . $folderName . '/intro.md';
            $category['type'] = 'category-linked';
        }

        $files = scandir($folderPath);
        if ($files === false) {
            return $category;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === 'intro.md') {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
                $fullFilePath = $folderPath . '/' . $file;
                $title = $this->extractTitleFromFile($fullFilePath);
                $category['pages'][] = [
                    'title' => $title,
                    'path' => $sourceKey . '::Pages/' . $folderName . '/' . $file,
                    'type' => 'page',
                    'level' => 2,
                    'source' => $sourceKey,
                    'icon' => '📄',
                    'color' => '#6c757d'
                ];
            }
        }

        usort($category['pages'], function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });

        return $category;
    }

    /**
     * Loads the HTML content of a specific file
     */
    public function getPageHtml(string $path): string
    {


        if (str_contains($path, '::')) {
            [$sourceKey, $relativePath] = explode('::', $path, 2);

            if (isset($this->documentationSources[$sourceKey]) && $this->documentationSources[$sourceKey]['enabled']) {
                $source = $this->documentationSources[$sourceKey];
                $userLanguage = $this->getUserLanguage();

                $localizedSourcePath = $this->getLocalizedSourcePath($source['path'], $userLanguage);
                $fullPath = $localizedSourcePath . $relativePath;

                if (is_file($fullPath)) {
                    return $this->markdownUtility->convertFileToHtml($fullPath);
                }
            }
        }

        $sources = $this->documentationSources;
        $userLanguage = $this->getUserLanguage();
        foreach ($sources as $source) {
            $localizedSourcePath = $this->getLocalizedSourcePath($source['path'], $userLanguage);
            $fullPath = $localizedSourcePath . $path;
            if (is_file($fullPath)) {
                return $this->markdownUtility->convertFileToHtml($fullPath);
            }
        }

        $title = LocalizationUtility::translate('error.pageNotFound.title', 'documentationhub') ?? 'Page Not Found';
        $message = LocalizationUtility::translate('error.pageNotFound.message', 'documentationhub') ?? 'The requested page could not be found.';

        return sprintf('<h1>%s</h1><p>%s</p>', htmlspecialchars($title), htmlspecialchars($message));
    }

    /**
     * Extracts the title from a Markdown file
     */
    private function extractTitleFromFile(string $filePath): string
    {
        if (!is_file($filePath)) {
            return LocalizationUtility::translate('default.unknown', 'documentationhub') ?? 'Unknown';
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return LocalizationUtility::translate('default.unknown', 'documentationhub') ?? 'Unknown';
        }

        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '# ')) {
                return trim(substr($line, 2));
            }
        }

        return pathinfo($filePath, PATHINFO_FILENAME);
    }

    /**
     * Loads all available documentation sources from all extensions
     */
    /** @return array<string, array<string, mixed>> */
    private function getAllDocumentationSources(): array
    {
        $sources = [];

        $extensions = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
        foreach ($extensions as $extensionKey) {
            $configFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'Configuration/SourcesForDocumentation.php';

            if (file_exists($configFile)) {
                $extensionConfig = include $configFile;

                if (is_array($extensionConfig)) {
                    foreach ($extensionConfig as $sourceKey => $sourceConfig) {
                        $fullSourceKey = $sourceKey;
                        $sources[$fullSourceKey] = array_merge($sourceConfig, [
                            'extension' => $extensionKey,
                            'source_key' => $sourceKey
                        ]);

                        $sources[$fullSourceKey] = $this->applyTranslationsToSource($sources[$fullSourceKey], $extensionKey);
                    }
                }
            }
        }

        return $sources;
    }

    /**
     * Filters documentation sources based on user groups
     * Maintains the order from getAllDocumentationSources()
     *
     * @param array<string, array<string, mixed>> $allSources
     * @return array<string, array<string, mixed>>
     */
    private function filterSourcesByUserGroups(array $allSources): array
    {
        $sourcesByUserGroups = $this->getSourcesByUserGroups();
        $filteredSources = [];
        $hasGroupSpecificSources = false;

        foreach ($sourcesByUserGroups as $sourceKey) {
            if (isset($allSources[$sourceKey])) {
                $filteredSources[$sourceKey] = $allSources[$sourceKey];
                $hasGroupSpecificSources = true;
            }
        }

        if (!$hasGroupSpecificSources) {
            $fallbackSource = [
                'documentation' => [
                    'title' => LocalizationUtility::translate('documentation.title', 'documentationhub'),
                    'path' => 'EXT:documentationhub/Documentation/',
                    'enabled' => true,
                    'icon' => '📚',
                    'color' => '#007bff',
                    'description' => LocalizationUtility::translate('documentation.description', 'documentationhub')
                ]
            ];
            return $fallbackSource;
        }

        return $filteredSources;
    }

    /**
     * Returns documentation sources configured for user groups
     */
    /** @return array<int, string> */
    private function getSourcesByUserGroups(): array
    {
        $sourcesByUserGroups = [];

        if (isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->user) {
            $beUser = $GLOBALS['BE_USER'];
            $user = $beUser->user;
            if (isset($user['usergroup'])) {
                $groupUids = explode(',', $user['usergroup']);
                foreach ($groupUids as $groupUid) {
                    $groupUid = (int)trim($groupUid);
                    if ($groupUid > 0) {
                        if (isset($beUser->userGroups[$groupUid])) {
                            $groupData = $beUser->userGroups[$groupUid];
                            if (!empty($groupData['documentation_sources'])) {
                                $groupSources = explode(',', $groupData['documentation_sources']);
                                foreach ($groupSources as $source) {
                                    $sourcesByUserGroups[] = $source;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $sourcesByUserGroups;
    }

    /**
     * Applies translations to a documentation source
     *
     * @param array<string, mixed> $sourceConfig
     * @return array<string, mixed>
     */
    private function applyTranslationsToSource(array $sourceConfig, string $extensionKey): array
    {
        // Check if title and description are translation keys
        if (isset($sourceConfig['title']) && str_starts_with($sourceConfig['title'], 'LLL:')) {
            $sourceConfig['title'] = LocalizationUtility::translate(
                $this->extractTranslationKey($sourceConfig['title']),
                $extensionKey
            ) ?: $sourceConfig['title'];
        }

        if (isset($sourceConfig['description']) && str_starts_with($sourceConfig['description'], 'LLL:')) {
            $sourceConfig['description'] = LocalizationUtility::translate(
                $this->extractTranslationKey($sourceConfig['description']),
                $extensionKey
            ) ?: $sourceConfig['description'];
        }

        return $sourceConfig;
    }

    /**
     * Extracts the translation key from an LLL string
     */
    private function extractTranslationKey(string $lllString): string
    {
        // Format: LLL:EXT:extension/Resources/Private/Language/locallang.xlf:key
        $parts = explode(':', $lllString);
        return end($parts);
    }

    /**
     * Gets the user's preferred language from TYPO3 backend settings
     */
    private function getUserLanguage(): string
    {
        if (isset($GLOBALS['BE_USER']) && $GLOBALS['BE_USER']->user) {
            $beUser = $GLOBALS['BE_USER'];
            $user = $beUser->user;

            if (!empty($user['lang'])) {
                return $user['lang'];
            }
        }

        return self::DEFAULT_LANG;
    }
}
