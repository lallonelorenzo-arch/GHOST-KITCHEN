<?php
declare(strict_types=1);

require_once __DIR__ . '/ViewHelpers.php';

class ViewRenderer
{
    public static function render(string $template, array $data = [], array $shared = []): void
    {
        $templateFile = __DIR__ . '/templates/' . $template . '.php';
        if (!is_file($templateFile)) {
            throw new RuntimeException('Template View non trovato.');
        }

        $GLOBALS['view_base_url'] = (string) ($shared['baseUrl'] ?? '');
        $viewData = array_merge($shared, $data);
        extract($viewData, EXTR_SKIP);

        $contentTemplate = $templateFile;
        require __DIR__ . '/templates/layout.php';
    }
}
