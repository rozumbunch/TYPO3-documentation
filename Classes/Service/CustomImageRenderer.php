<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\Service;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Custom Image Renderer für TYPO3-spezifische Bildpfade
 */
final class CustomImageRenderer implements NodeRendererInterface
{
    public function __construct()
    {
    }

    public function render(\League\CommonMark\Node\Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (!$node instanceof Image) {
            throw new \InvalidArgumentException('Expected Image node');
        }

        $url = $node->getUrl();
        $alt = $node->getTitle() ?? '';

        $processedUrl = $this->processImageUrl($url);

        $attributes = [
            'src' => $processedUrl,
            'alt' => $alt,
            'class' => 'rb-doc__image'
        ];

        if ($node->getTitle()) {
            $attributes['title'] = $node->getTitle();
        }

        return new HtmlElement('img', $attributes, '', true);
    }

    /**
     * Verarbeitet verschiedene Bildpfad-Formate für TYPO3
     */
    private function processImageUrl(string $url): string
    {

        if (str_starts_with($url, 'EXT:')) {
            $extensionKey = $this->extractExtensionKeyFromExtPath($url);
            if ($extensionKey && $this->extensionExists($extensionKey)) {
                return $this->resolveExtPath($url);
            }
        }
        return $url;
    }

    /**
     * Löst TYPO3 EXT-Pfade auf
     */
    private function resolveExtPath(string $extPath): string
    {

        $path = substr($extPath, 4);
        $parts = explode('/', $path, 2);

        if (count($parts) >= 2) {
            $extensionKey = $parts[0];
            $relativePath = $parts[1];

            $fullPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . $relativePath;

            return $this->convertToWebPath($fullPath);
        }

        return $extPath;
    }

    /**
     * Extrahiert den Extension-Key aus einem EXT: Pfad
     */
    private function extractExtensionKeyFromExtPath(string $extPath): ?string
    {
        if (!str_starts_with($extPath, 'EXT:')) {
            return null;
        }

        $path = substr($extPath, 4); // Entferne "EXT:"
        $parts = explode('/', $path, 2);

        return $parts[0] ?? null;
    }

    /**
     * Prüft ob eine Extension existiert
     */
    private function extensionExists(string $extensionKey): bool
    {
        try {
            $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
            return is_dir($extPath);
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Konvertiert einen Dateisystem-Pfad zu einem TYPO3-verarbeiteten Web-Pfad
     */
    private function convertToWebPath(string $filePath): string
    {
        // Prüfe ob die Datei existiert
        if (!file_exists($filePath)) {
            return $filePath;
        }

        // Verwende TYPO3s PathUtility für die korrekte Pfad-Konvertierung
        try {
            return \TYPO3\CMS\Core\Utility\PathUtility::getAbsoluteWebPath($filePath);
        } catch (\Exception $e) {
            // Fallback: Manuelle Konvertierung
            $publicPath = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT');
            if (str_starts_with($filePath, $publicPath)) {
                $relativePath = substr($filePath, strlen($publicPath));
                return ltrim($relativePath, '/');
            }
        }

        return $filePath;
    }
}
