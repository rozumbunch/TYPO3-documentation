<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\TCA\ItemsProcFunc;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class DocumentationSourcesItemsProcFunc
{
    /**
     * Fills the multiselect options with available documentation sources
     *
     * @param array<string, mixed> $config
     */
    public function getDocumentationSources(array &$config): void
    {
        $documentationSources = $this->getAllDocumentationSources();
        
        foreach ($documentationSources as $sourceKey => $source) {
            $config['items'][] = [
                $source['title'],
                $sourceKey,
                $source['icon'] ?? '📄'
            ];
        }
    }

    /**
     * Loads all available documentation sources from all extensions
     *
     * @return array<string, array<string, mixed>>
     */
    private function getAllDocumentationSources(): array
    {
        $sources = [];
        
        $extensions = ExtensionManagementUtility::getLoadedExtensionListArray();
        
        foreach ($extensions as $extensionKey) {
            $configFile = ExtensionManagementUtility::extPath($extensionKey) . 'Configuration/SourcesForDocumentation.php';
            
            if (file_exists($configFile)) {
                $extensionConfig = include $configFile;
                
                if (is_array($extensionConfig)) {
                    foreach ($extensionConfig as $sourceKey => $sourceConfig) {
                        $fullSourceKey = $sourceKey;
                        
                        $sources[$fullSourceKey] = array_merge($sourceConfig, [
                            'extension' => $extensionKey,
                            'source_key' => $sourceKey
                        ]);
                    }
                }
            }
        }
                
        return $sources;
    }
}
