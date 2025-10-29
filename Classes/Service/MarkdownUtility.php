<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\Service;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Utility class for Markdown processing
 */
final class MarkdownUtility
{
    private MarkdownConverter $converter;
    private ?string $currentFilePath = null;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        $environment->addRenderer(Image::class, new CustomImageRenderer());

        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Converts Markdown text to HTML
     */
    public function convertToHtml(string $markdown): string
    {
        if (empty(trim($markdown))) {
            return '<p>' . LocalizationUtility::translate('markdown.noContent', 'documentationhub') . '</p>';
        }

        return $this->converter->convert($markdown)->getContent();
    }

    /**
     * Converts Markdown file to HTML
     */
    public function convertFileToHtml(string $filePath): string
    {
        if (!is_file($filePath)) {
            return '<h1>' . LocalizationUtility::translate('markdown.fileNotFound.title', 'documentationhub') . '</h1><p>' . LocalizationUtility::translate('markdown.fileNotFound.message', 'documentationhub') . '</p>';
        }

        // Setze den aktuellen Dateipfad für die Bildverarbeitung
        $this->currentFilePath = $filePath;

        $markdown = file_get_contents($filePath);
        if ($markdown === false) {
            return '<h1>' . LocalizationUtility::translate('markdown.readError.title', 'documentationhub') . '</h1><p>' . LocalizationUtility::translate('markdown.readError.message', 'documentationhub') . '</p>';
        }

        $result = $this->convertToHtml($markdown);

        // Reset den aktuellen Dateipfad
        $this->currentFilePath = null;

        return $result;
    }

    /**
     * Gibt den aktuellen Dateipfad zurück
     */
    public function getCurrentFilePath(): ?string
    {
        return $this->currentFilePath;
    }
}
