<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\Service;

use Mpdf\Mpdf;

final class PdfExportService
{
    public function __construct(
        private readonly DocumentationhubService $documentationhubService
    ) {
    }

    public function renderPdfFromPath(string $relativePath): string
    {
        $html = $this->documentationhubService->getPageHtml($relativePath);
        if (empty($html)) {
            throw new \RuntimeException('Kein HTML-Content für Pfad gefunden: ' . $relativePath);
        }
        return $this->generatePdf($html, 'Documentation');
    }

    public function renderSourcePdf(string $source): string
    {
        $sources = $this->documentationhubService->getDocumentationSources();

        if (!isset($sources[$source])) {
            throw new \RuntimeException('Source nicht gefunden: ' . $source);
        }

        $sourceConfig = $sources[$source];
        $combinedHtml = '';

        $navigation = $this->documentationhubService->getNavigation();
        $sourcePages = [];

        foreach ($navigation as $navItem) {
            if (isset($navItem['source']) && $navItem['source'] === $source) {
                $this->collectSourcePages($navItem, $sourcePages);
            }
        }
        $isFirstPage = true;
        foreach ($sourcePages as $pagePath) {
            $pageHtml = $this->documentationhubService->getPageHtml($pagePath);
            if (!empty($pageHtml)) {
                if ($isFirstPage && str_contains($pagePath, 'index.md')) {
                    $combinedHtml .= '<div class="documentation-source" data-source="' . htmlspecialchars($source) . '">';
                    $combinedHtml .= '<h1 class="source-title">' . htmlspecialchars($sourceConfig['title']) . '</h1>';
                    $combinedHtml .= $pageHtml;
                    $combinedHtml .= '</div>';
                    $isFirstPage = false;
                } else {
                    $combinedHtml .= '<div class="page-break">';
                    $combinedHtml .= $pageHtml;
                    $combinedHtml .= '</div>';
                }
            }
        }

        if (empty($combinedHtml)) {
            throw new \RuntimeException('Kein HTML-Content für Source generiert: ' . $source);
        }

        return $this->generatePdf($combinedHtml, 'Kapitel: ' . $sourceConfig['title']);
    }

    /**
     * @param array<string, mixed> $navItem
     * @param array<int, string> $sourcePages
     */
    private function collectSourcePages(array $navItem, array &$sourcePages): void
    {
        if (isset($navItem['path']) && !empty(trim($navItem['path']))) {
            $sourcePages[] = $navItem['path'];
        }

        if (isset($navItem['pages']) && is_array($navItem['pages'])) {
            foreach ($navItem['pages'] as $child) {
                if (isset($child['path']) && !empty(trim($child['path']))) {
                    $this->collectSourcePages($child, $sourcePages);
                } else {
                    if (isset($child['pages'])) {
                        foreach ($child['pages'] as $subChild) {
                            $this->collectSourcePages($subChild, $sourcePages);
                        }
                    }
                }
            }
        }
    }

    public function generatePdf(string $html, string $title = 'Documentation'): string
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => sys_get_temp_dir(),
        ]);

        $mpdf->SetTitle($title);
        $mpdf->WriteHTML($this->getPdfStyles());
        $mpdf->WriteHTML($this->cleanHtmlForPdf($html));

        return $mpdf->Output('', 'S');
    }

    private function cleanHtmlForPdf(string $html): string
    {
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html) ?? $html;

        return $html;
    }

    private function getPdfStyles(): string
    {
        return '<style>
            body { 
                font-family: Arial, sans-serif; 
                font-size: 12px; 
                line-height: 1.4;
                color: #333;
            }
            h1, h2, h3, h4, h5, h6 { 
                color: #333; 
                margin-top: 20px;
                margin-bottom: 10px;
            }
            h1 { font-size: 18px; }
            h2 { font-size: 16px; }
            h3 { font-size: 14px; }
            pre, code { 
                background: #f5f5f5; 
                padding: 8px; 
                border-radius: 4px;
                font-family: monospace;
            }
            pre { 
                overflow-x: auto;
                white-space: pre-wrap;
            }
            blockquote {
                border-left: 4px solid #ddd;
                padding-left: 15px;
                margin-left: 0;
                color: #666;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .page-break {
                page-break-before: always;
                margin-top: 30px;
            }
            .page-break:first-child {
                page-break-before: auto;
                margin-top: 0;
            }
        </style>';
    }
}
