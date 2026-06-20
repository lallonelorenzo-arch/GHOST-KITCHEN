<?php
declare(strict_types=1);

require_once __DIR__ . '/ViewHelpers.php';
require_once __DIR__ . '/../Foundation/FSession.php';

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
        // Il layout include il template specifico e produce una pagina HTML completa.
        ob_start();
        require __DIR__ . '/templates/layout.php';
        $html = (string) ob_get_clean();

        echo self::injectCsrfInputs($html);
    }

    // Protezione centralizzata: ogni form POST renderizzato riceve un token CSRF.
    private static function injectCsrfInputs(string $html): string
    {
        return (string) preg_replace_callback(
            '/<form\b(?=[^>]*\bmethod\s*=\s*(["\']?)post\1)[^>]*>/i',
            static function (array $matches): string {
                $formTag = $matches[0];
                $scope = stripos($formTag, '/prenotazione/chef/') !== false
                    ? 'chef_booking'
                    : 'web_form';
                $token = FSession::csrfToken($scope);

                return $formTag . "\n" . '            <input type="hidden" name="csrfToken" value="' . ViewHelpers::e($token) . '">';
            },
            $html
        );
    }
}
