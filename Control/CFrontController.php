<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FSession.php';
require_once __DIR__ . '/../View/ViewRenderer.php';

class CFrontController
{
    private const ALLOWED_ROUTES = [
        'GET' => [
            '/' => ['CHome', 'home', 'home'],
            '/ricerca/chef' => ['CRicerca', 'cercaOfferte', 'lista_chef'],
            '/ricerca/ghost-kitchen' => ['CRicerca', 'cercaOfferte', 'lista_ghost_kitchen'],
            '/login' => ['CAutenticazione', 'mostraLogin', 'login'],
            '/logout' => ['CAutenticazione', 'logout', null],
            '/disponibilita' => ['CGestioneDisponibilita', 'mostraDisponibilitaWeb', 'disponibilita'],
            '/richieste' => ['CGestioneRichieste', 'visualizzaRichiesteWeb', 'richieste'],
            '/dashboard' => ['CDashboardStatistiche', 'visualizzaDashboardWeb', 'dashboard'],
            '/moderazione' => ['CModerazione', 'visualizzaContenutiDaModerareWeb', 'moderazione'],
            '/certificazioni' => ['CValidazioneCertificazioni', 'visualizzaCertificazioniInAttesaWeb', 'certificazioni'],
            '/prenotazione/placeholder' => ['CHome', 'placeholder', 'placeholder'],
        ],
        'POST' => [
            '/login' => ['CAutenticazione', 'login', 'login'],
            '/disponibilita/chef' => ['CGestioneDisponibilita', 'aggiungiDisponibilitaChefWeb', 'richiesta_esito'],
            '/disponibilita/ghost-kitchen' => ['CGestioneDisponibilita', 'aggiungiDisponibilitaGhostKitchenWeb', 'richiesta_esito'],
        ],
    ];

    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = $this->normalizePath((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $query = $this->normalizeRequest($_GET);
        $post = $this->normalizeRequest($_POST);

        try {
            if ($method === 'GET' && $path === '/ricerca') {
                $this->redirect('/ricerca/chef');
                return;
            }

            if ($method === 'GET' && preg_match('#^/prenotazione/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneChef', 'mostraPrenotazioneChefWeb', 'prenotazione_chef', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/prenotazione/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneChef', 'confermaPrenotazioneChefWeb', 'prenotazione_chef', [(int) $matches[1], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/prenotazione/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneGhostKitchen', 'mostraPrenotazioneGhostKitchenWeb', 'prenotazione_ghost_kitchen', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/prenotazione/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneGhostKitchen', 'confermaPrenotazioneGhostKitchenWeb', 'prenotazione_ghost_kitchen', [(int) $matches[1], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/richieste/(chef|ghost-kitchen)/([1-9][0-9]*)/(accetta|rifiuta)$#', $path, $matches) === 1) {
                $tipoPrenotazione = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CGestioneRichieste', 'gestisciRichiestaWeb', 'richiesta_esito', [$tipoPrenotazione, (int) $matches[2], $matches[3], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/pagamento/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoPrenotazione = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CPagamento', 'mostraPagamentoWeb', 'pagamento', [$tipoPrenotazione, (int) $matches[2], $this->accessContext(), $query]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/pagamento/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoPrenotazione = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CPagamento', 'confermaPagamentoWeb', 'pagamento', [$tipoPrenotazione, (int) $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/cancellazione/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoPrenotazione = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CCancellazioneRimborso', 'mostraCancellazioneWeb', 'cancellazione', [$tipoPrenotazione, (int) $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/cancellazione/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoPrenotazione = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CCancellazioneRimborso', 'confermaCancellazioneWeb', 'cancellazione', [$tipoPrenotazione, (int) $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/recensione/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoTarget = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CRecensione', 'mostraRecensioneWeb', 'recensione', [$tipoTarget, (int) $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/recensione/(chef|ghost-kitchen)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoTarget = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CRecensione', 'pubblicaRecensioneWeb', 'recensione', [$tipoTarget, (int) $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/segnalazione/(utente|chef|ghost-kitchen|recensione|menu)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoTarget = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : $matches[1];
                $this->renderController('CSegnalazione', 'mostraSegnalazioneWeb', 'segnalazione', [$tipoTarget, (int) $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/segnalazione/(utente|chef|ghost-kitchen|recensione|menu)/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $tipoTarget = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : $matches[1];
                $this->renderController('CSegnalazione', 'inviaSegnalazioneWeb', 'segnalazione', [$tipoTarget, (int) $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/moderazione/segnalazione/([1-9][0-9]*)/prendi$#', $path, $matches) === 1) {
                $this->renderController('CModerazione', 'prendiInCaricoSegnalazioneWeb', 'richiesta_esito', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/moderazione/segnalazione/([1-9][0-9]*)/chiudi$#', $path, $matches) === 1) {
                $this->renderController('CModerazione', 'chiudiSegnalazioneWeb', 'richiesta_esito', [(int) $matches[1], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/moderazione/recensione/([1-9][0-9]*)/(nascondi|rimuovi|ripristina)$#', $path, $matches) === 1) {
                $this->renderController('CModerazione', 'moderaRecensioneWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/moderazione/profilo/([1-9][0-9]*)/(sospendi|banna|riattiva)$#', $path, $matches) === 1) {
                $this->renderController('CModerazione', 'moderaProfiloWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/certificazioni/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CValidazioneCertificazioni', 'visualizzaDettaglioCertificazioneWeb', 'certificazione_dettaglio', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/certificazioni/([1-9][0-9]*)/(approva|rifiuta)$#', $path, $matches) === 1) {
                $this->renderController('CValidazioneCertificazioni', 'aggiornaCertificazioneWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CDettaglioChef', 'visualizzaDettaglioChef', 'dettaglio_chef', [(int) $matches[1]]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CDettaglioGhostKitchen', 'visualizzaDettaglioGhostKitchen', 'dettaglio_ghost_kitchen', [(int) $matches[1]]);
                return;
            }

            if (!$this->routeExistsForAnyMethod($path)) {
                $this->renderError(404, 'Pagina non trovata', 'La pagina richiesta non esiste.');
                return;
            }

            $route = self::ALLOWED_ROUTES[$method][$path] ?? null;
            if ($route === null) {
                $this->renderError(405, 'Metodo non consentito', 'Il metodo HTTP usato non e valido per questa pagina.');
                return;
            }

            [$controller, $action, $template] = $route;
            $params = match ($path) {
                '/ricerca/chef' => [[
                    'localita' => $query['localita'] ?? '',
                    'tipologiaCucina' => $query['tipologiaCucina'] ?? '',
                    'budgetMax' => $query['budgetMax'] ?? 0,
                    'valutazioneMin' => $query['valutazioneMin'] ?? 0,
                    'tipoRisultato' => 'chef',
                ]],
                '/ricerca/ghost-kitchen' => [[
                    'localita' => $query['localita'] ?? '',
                    'tipologiaCucina' => '',
                    'budgetMax' => $query['budgetMax'] ?? 0,
                    'valutazioneMin' => $query['valutazioneMin'] ?? 0,
                    'tipoRisultato' => 'ghost_kitchen',
                ]],
                '/login' => $method === 'POST' ? [$post] : [],
                '/disponibilita' => [$this->accessContext(), $query],
                '/richieste' => [$this->accessContext()],
                '/dashboard' => [$this->accessContext(), $query],
                '/moderazione' => [$this->accessContext()],
                '/certificazioni' => [$this->accessContext()],
                '/disponibilita/chef' => [$this->accessContext(), $post],
                '/disponibilita/ghost-kitchen' => [$this->accessContext(), $post],
                default => [],
            };

            if ($path === '/logout') {
                $this->callController($controller, $action, []);
                $this->redirect('/');
                return;
            }

            $data = $this->callController($controller, $action, $params);
            if ($path === '/login' && $method === 'POST' && is_array($data) && ($data['successo'] ?? false) === true) {
                $this->redirect('/');
                return;
            }

            ViewRenderer::render((string) $template, is_array($data) ? $data : [], $this->sharedViewData());
        } catch (InvalidArgumentException $exception) {
            $this->renderError(404, 'Risorsa non valida', $exception->getMessage());
        } catch (Throwable $exception) {
            error_log(sprintf(
                '[CFrontController] %s: %s in %s:%d',
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));
            $this->renderError(500, 'Errore applicativo', 'Si e verificato un errore interno. Riprova piu tardi.');
        }
    }

    private function renderController(string $controller, string $action, string $template, array $params): void
    {
        $data = $this->callController($controller, $action, $params);
        if (is_array($data) && isset($data['errore'])) {
            $this->renderError(404, (string) $data['errore'], 'Controlla l identificativo nella URL.');
            return;
        }

        ViewRenderer::render($template, is_array($data) ? $data : [], $this->sharedViewData());
    }

    private function callController(string $className, string $actionName, array $params): mixed
    {
        $controllerFile = __DIR__ . '/' . $className . '.php';
        if (!is_file($controllerFile)) {
            throw new RuntimeException('Controller non trovato.');
        }

        require_once $controllerFile;
        if (!class_exists($className) || !method_exists($className, $actionName)) {
            throw new RuntimeException('Azione non disponibile.');
        }

        $controller = new $className();
        return $controller->$actionName(...$params);
    }

    private function normalizePath(string $path): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $path = '/' . trim($path, '/');

        if ($scriptDir !== '/' && $scriptDir !== '.' && str_starts_with($path, $scriptDir . '/')) {
            $path = substr($path, strlen($scriptDir));
        } elseif ($scriptDir !== '/' && $path === $scriptDir) {
            $path = '/';
        }

        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function normalizeRequest(array $input): array
    {
        $normalized = [];
        foreach ($input as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $normalized[$key] = is_array($value) ? '' : trim((string) $value);
        }

        return $normalized;
    }

    private function routeExistsForAnyMethod(string $path): bool
    {
        if (preg_match('#^/prenotazione/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/(pagamento|cancellazione|recensione)/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/segnalazione/(utente|chef|ghost-kitchen|recensione|menu)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/richieste/(chef|ghost-kitchen)/[1-9][0-9]*/(accetta|rifiuta)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/moderazione/segnalazione/[1-9][0-9]*/(prendi|chiudi)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/moderazione/recensione/[1-9][0-9]*/(nascondi|rimuovi|ripristina)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/moderazione/profilo/[1-9][0-9]*/(sospendi|banna|riattiva)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/certificazioni/[1-9][0-9]*(/(approva|rifiuta))?$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        foreach (self::ALLOWED_ROUTES as $routes) {
            if (array_key_exists($path, $routes)) {
                return true;
            }
        }

        return false;
    }

    private function accessContext(): array
    {
        FSession::start();

        return [
            'isLogged' => FSession::isLogged(),
            'idUtente' => FSession::getIdUtente(),
            'email' => FSession::getEmail(),
            'nome' => FSession::getNome(),
            'cognome' => FSession::getCognome(),
            'ruoli' => FSession::getRuoli(),
            'ruoloAttivo' => FSession::getRuoloAttivo(),
        ];
    }

    private function sharedViewData(): array
    {
        FSession::start();

        return [
            'baseUrl' => $this->baseUrl(),
            'utenteCorrente' => FSession::isLogged() ? [
                'nome' => FSession::getNome(),
                'cognome' => FSession::getCognome(),
                'ruolo' => FSession::getRuoloAttivo(),
            ] : null,
        ];
    }

    private function baseUrl(): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return $scriptDir === '/' || $scriptDir === '.' ? '' : rtrim($scriptDir, '/');
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $this->baseUrl() . $path);
        exit;
    }

    private function renderError(int $status, string $title, string $message): void
    {
        http_response_code($status);
        ViewRenderer::render('error', [
            'status' => $status,
            'title' => $title,
            'message' => $message,
        ], $this->sharedViewData());
    }
}
