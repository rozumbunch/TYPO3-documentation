<?php

declare(strict_types=1);

namespace Rozumbunch\Documentationhub\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rozumbunch\Documentationhub\Service\DocumentationhubService;
use Rozumbunch\Documentationhub\Service\PdfExportService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class DocumentationhubModuleController implements ControllerInterface
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly DocumentationhubService $documentationhubService,
        private readonly PdfExportService $pdfExportService
    ) {
    }

    public function processRequest(RequestInterface $request): ResponseInterface
    {
        $actionName = $request->getControllerActionName();
        $methodName = $actionName . 'Action';

        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException('Action method not found: ' . $methodName);
        }

        return $this->$methodName($request);
    }

    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        $isAjaxRequest = $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';

        if ($isAjaxRequest) {
            $queryParams = $request->getQueryParams();
            $page = (string)($queryParams['page'] ?? 'index.md');

            try {
                $htmlContent = $this->documentationhubService->getPageHtml($page);
            } catch (\Exception $e) {
                $htmlContent = $this->documentationhubService->getIndexHtml();
            }

            return new JsonResponse([
                'success' => true,
                'content' => $htmlContent,
                'path' => $page
            ]);
        }

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $queryParams = $request->getQueryParams();
        $page = (string)($queryParams['page'] ?? 'index.md');

        $navigation = $this->documentationhubService->getNavigation();

        try {
            $htmlContent = $this->documentationhubService->getPageHtml($page);
            $currentPath = $page;
        } catch (\Exception $e) {
            $htmlContent = $this->documentationhubService->getIndexHtml();
            $currentPath = 'index.md';
        }

        $moduleTemplate->assignMultiple([
            'navigation' => $navigation,
            'htmlContent' => $htmlContent,
            'currentPath' => $currentPath
        ]);

        return $moduleTemplate->renderResponse('Module/Index');
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function loadPageAction(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $path = (string)($queryParams['path'] ?? '');

        if (empty($path)) {
            return new JsonResponse(['error' => LocalizationUtility::translate('error.noPathSpecified', 'documentationhub')], 400);
        }

        try {
            $htmlContent = $this->documentationhubService->getPageHtml($path);

            return new JsonResponse([
                'success' => true,
                'content' => $htmlContent,
                'path' => $path
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => LocalizationUtility::translate('error.loadingPage', 'documentationhub') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportiert eine einzelne Seite als PDF
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function exportPagePdfAction(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $path = (string)($queryParams['path'] ?? 'index.md');

        try {
            $pdfContent = $this->pdfExportService->renderPdfFromPath($path);
            $filename = $this->sanitizeFilename($path) . '.pdf';

            return $this->createPdfResponse($pdfContent, $filename);
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Export a complete source (Source) as PDF
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function exportSourcePdfAction(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $source = (string)($queryParams['source'] ?? 'documentationhub');

        try {
            $pdfContent = $this->pdfExportService->renderSourcePdf($source);
            $filename = $this->sanitizeFilename($source) . '.pdf';

            return $this->createPdfResponse($pdfContent, $filename);
        } catch (\Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    /**
     * Creates a PDF-Response with the corresponding HTTP headers
     *
     * @param string $pdfContent The PDF content as a string
     * @param string $filename The filename for the download
     * @return ResponseInterface
     */
    private function createPdfResponse(string $pdfContent, string $filename): ResponseInterface
    {
        $response = new Response();
        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response = $response->withHeader('Content-Length', (string)strlen($pdfContent));

        $response->getBody()->write($pdfContent);
        return $response;
    }

    /**
     * Creates a HTML-error-Response for PDF-export-errors
     *
     * @param string $errorMessage The error message
     * @return ResponseInterface
     */
    private function createErrorResponse(string $errorMessage): ResponseInterface
    {
        $errorTitle = LocalizationUtility::translate('pdf.exportError.title', 'documentationhub') ?? 'Export Error';
        $errorLabel = LocalizationUtility::translate('pdf.exportError.label', 'documentationhub') ?? 'Error';

        $errorHtml = '<!DOCTYPE html><html><head><title>' . htmlspecialchars($errorTitle) . '</title></head><body>';
        $errorHtml .= '<h1>' . htmlspecialchars($errorTitle) . '</h1>';
        $errorHtml .= '<p><strong>' . htmlspecialchars($errorLabel) . '</strong> ' . htmlspecialchars($errorMessage) . '</p>';
        $errorHtml .= '</body></html>';

        $response = new Response();
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->getBody()->write($errorHtml);
        return $response;
    }

    /**
     * Sanitizes a filename for safe use
     *
     * @param string $filename The filename to sanitize
     * @return string The sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename) ?? $filename;
        $filename = preg_replace('/_+/', '_', $filename) ?? $filename;
        return trim($filename, '_');
    }
}
