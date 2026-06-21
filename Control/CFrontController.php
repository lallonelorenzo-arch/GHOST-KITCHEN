<?php
declare(strict_types=1);

require_once __DIR__ . '/../Foundation/FSession.php';
require_once __DIR__ . '/../Foundation/FPersistentManager.php';
require_once __DIR__ . '/../View/ViewRenderer.php';

class CFrontController
{
    // Route statiche: URL esatti associati a controller, metodo e template.
    // Le route con parametri numerici sono gestite piu sotto con regex dedicate.
    private const ALLOWED_ROUTES = [
        'GET' => [
            '/' => ['CHome', 'home', 'home'],
            '/ricerca/chef' => ['CRicerca', 'cercaOfferte', 'lista_chef'],
            '/ricerca/ghost-kitchen' => ['CRicerca', 'cercaOfferte', 'lista_ghost_kitchen'],
            '/login' => ['CAutenticazione', 'mostraLogin', 'login'],
            '/registrazione' => ['CRegistrazione', 'mostraRegistrazione', 'registrazione'],
            '/profilo' => ['CAutenticazione', 'profilo', 'profilo'],
            '/logout' => ['CAutenticazione', 'logout', null],
            '/prenotazioni' => ['CPrenotazioniUtente', 'visualizzaPrenotazioniWeb', 'prenotazioni'],
            '/mie-recensioni' => ['CRecensioni', 'visualizzaMieRecensioniWeb', 'recensioni'],
            '/dashboard' => ['CDashboardStatistiche', 'visualizzaDashboardWeb', 'dashboard'],
            '/recensioni' => ['CRecensioni', 'visualizzaTutteRecensioniWeb', 'recensioni'],
            '/moderazione' => ['CModerazione', 'visualizzaContenutiDaModerareWeb', 'moderazione'],
            '/utenti' => ['CAdminUtenti', 'visualizzaUtentiWeb', 'utenti'],
            '/certificazioni' => ['CValidazioneCertificazioni', 'visualizzaCertificazioniInAttesaWeb', 'certificazioni'],
            '/mie-certificazioni' => ['CCertificazioniChef', 'visualizzaMieCertificazioniWeb', 'mie_certificazioni'],
        ],
        'POST' => [
            '/login' => ['CAutenticazione', 'login', 'login'],
            '/registrazione' => ['CRegistrazione', 'registra', 'registrazione'],
            '/profilo' => ['CAutenticazione', 'aggiornaProfilo', 'richiesta_esito'],
            '/disponibilita/chef' => ['CGestioneDisponibilita', 'aggiungiDisponibilitaChefWeb', 'richiesta_esito'],
            '/disponibilita/ghost-kitchen' => ['CGestioneDisponibilita', 'aggiungiDisponibilitaGhostKitchenWeb', 'richiesta_esito'],
            '/mie-certificazioni' => ['CCertificazioniChef', 'caricaCertificazioneWeb', 'richiesta_esito'],
            '/dashboard/chef/profilo' => ['CContenutiChef', 'aggiornaProfiloWeb', 'richiesta_esito'],
            '/dashboard/chef/media' => ['CContenutiChef', 'gestisciMediaWeb', 'richiesta_esito'],
            '/dashboard/chef/menu' => ['CContenutiChef', 'gestisciMenuWeb', 'richiesta_esito'],
            '/dashboard/chef/piatto' => ['CContenutiChef', 'gestisciPiattoWeb', 'richiesta_esito'],
            '/dashboard/gestore/ghost-kitchen' => ['CGestioneGhostKitchen', 'gestisciGhostKitchenWeb', 'richiesta_esito'],
            '/dashboard/gestore/media' => ['CGestioneGhostKitchen', 'gestisciMediaWeb', 'richiesta_esito'],
            '/dashboard/gestore/attrezzatura' => ['CGestioneGhostKitchen', 'gestisciAttrezzaturaWeb', 'richiesta_esito'],
        ],
    ];

    public function handle(): void
    {   // ?? -> usa $_SERVER[..] se esiste e non è null, altrimenti..
        // 1. Normalizzazione richiesta: metodo, path, query string e body POST.
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); //$_SERVER superglobale php che ha info sulla richiesta HTTP
        $path = $this->normalizePath((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $query = $this->normalizeRequest($_GET);
        $post = $this->normalizeRequest($_POST);

        try {
            // 2. Sicurezza trasversale: CSRF e controllo permessi prima del dispatch.
            if (!$this->isCsrfValid($method, $path, $post)) {
                $this->renderError(403, 'Sessione non valida', 'Ricarica la pagina e riprova.');
                return;
            }

            $this->synchronizeActiveRole($query);
            $accessContext = $this->accessContext();
            if (!$this->isPathAllowed($path, $method, $accessContext)) {
                $this->renderError(403, 'Accesso non consentito', 'Non hai permessi per questa sezione.');
                return;
            }

            // 3. Redirect di comodo: URL brevi che puntano alla sezione corretta.
            if ($method === 'GET' && $path === '/ricerca') {
                $this->redirect('/ricerca/chef');
                return;
            }

            if ($method === 'GET' && in_array($path, ['/disponibilita', '/richieste'], true)) {
                $dashboardRedirect = $this->professionalDashboardRedirect($path, $accessContext);
                if ($dashboardRedirect !== null) {
                    $this->redirect($dashboardRedirect);
                    return;
                }
            }

            // 4. Route dinamiche: URL con id o azione, validati tramite regex.
            if ($method === 'GET' && preg_match('#^/prenotazione/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->redirect('/chef/' . (int) $matches[1]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/prenotazione/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneChef', 'confermaPrenotazioneChefWizardWeb', 'richiesta_esito', [(int) $matches[1], $this->accessContext(), $post]); //[..] array da passare al metodo controller
                return; //il return serve a fermare l'esecuz del metodo corrente, poiché nel FrontController ci sono tante regole di routing una dopo l'altra
            }

            if ($method === 'GET' && preg_match('#^/prenotazione/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneGhostKitchen', 'mostraPrenotazioneGhostKitchenWeb', 'prenotazione_ghost_kitchen', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/prenotazione/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CPrenotazioneGhostKitchen', 'confermaPrenotazioneGhostKitchenWeb', 'prenotazione_ghost_kitchen', [(int) $matches[1], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/disponibilita/(chef|ghost-kitchen)/([1-9][0-9]*)/(blocca|libera)$#', $path, $matches) === 1) {
                $tipoOwner = $matches[1] === 'ghost-kitchen' ? 'ghost_kitchen' : 'chef';
                $this->renderController('CGestioneDisponibilita', 'aggiornaStatoDisponibilitaWeb', 'richiesta_esito', [$tipoOwner, (int) $matches[2], $matches[3], $this->accessContext()]);
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
                $this->renderController('CModerazione', 'moderaRecensioneWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/moderazione/profilo/([1-9][0-9]*)/(sospendi|banna|riattiva)$#', $path, $matches) === 1) {
                $this->renderController('CModerazione', 'moderaProfiloWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/utenti/utente/([1-9][0-9]*)/(sospendi|banna|riattiva)$#', $path, $matches) === 1) {
                $this->renderController('CAdminUtenti', 'aggiornaStatoUtenteWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/utenti/ghost-kitchen/([1-9][0-9]*)/(attiva|sospendi|non-disponibile)$#', $path, $matches) === 1) {
                $this->renderController('CAdminUtenti', 'aggiornaStatoGhostKitchenWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/utenti/gestore/([1-9][0-9]*)/(approva|rifiuta|sospendi-verifica|rimetti-in-attesa)$#', $path, $matches) === 1) {
                $this->renderController('CAdminUtenti', 'aggiornaVerificaGestoreWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext()]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/certificazioni/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CValidazioneCertificazioni', 'visualizzaDettaglioCertificazioneWeb', 'certificazione_dettaglio', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'POST' && preg_match('#^/certificazioni/([1-9][0-9]*)/(approva|rifiuta|in-attesa)$#', $path, $matches) === 1) {
                $this->renderController('CValidazioneCertificazioni', 'aggiornaCertificazioneWeb', 'richiesta_esito', [(int) $matches[1], $matches[2], $this->accessContext(), $post]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/chef/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CDettaglioChef', 'visualizzaDettaglioChef', 'dettaglio_chef', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/ghost-kitchen/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CDettaglioGhostKitchen', 'visualizzaDettaglioGhostKitchen', 'dettaglio_ghost_kitchen', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            if ($method === 'GET' && preg_match('#^/utente/([1-9][0-9]*)$#', $path, $matches) === 1) {
                $this->renderController('CProfiloUtente', 'visualizzaProfiloClienteWeb', 'utente_profilo', [(int) $matches[1], $this->accessContext()]);
                return;
            }

            // 5. Route statiche: risoluzione dalla whitelist ALLOWED_ROUTES.
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
                '/registrazione' => $method === 'POST' ? [$post, $_FILES] : [],
                '/profilo' => $method === 'POST' ? [$accessContext, $post, $_FILES] : [$accessContext, $query],
                '/prenotazioni' => [$accessContext],
                '/mie-recensioni' => [$accessContext, $query],
                '/dashboard' => [$accessContext, $query],
                '/recensioni' => [$accessContext, $query],
                '/moderazione' => [$accessContext],
                '/utenti' => [$accessContext, $query],
                '/certificazioni' => [$accessContext],
                '/mie-certificazioni' => $method === 'POST' ? [$accessContext, $post, $_FILES] : [$accessContext],
                '/disponibilita/chef' => [$accessContext, $post],
                '/disponibilita/ghost-kitchen' => [$accessContext, $post],
                '/dashboard/chef/profilo' => [$accessContext, $post],
                '/dashboard/chef/media' => [$accessContext, $post, $_FILES],
                '/dashboard/chef/menu' => [$accessContext, $post],
                '/dashboard/chef/piatto' => [$accessContext, $post],
                '/dashboard/gestore/ghost-kitchen' => [$accessContext, $post],
                '/dashboard/gestore/media' => [$accessContext, $post, $_FILES],
                '/dashboard/gestore/attrezzatura' => [$accessContext, $post],
                default => [],
            };

            // 6. Casi speciali: logout e dashboard differenziata per ruolo.
            if ($path === '/logout') {
                $this->callController($controller, $action, []);
                $this->redirect('/');
                return;
            }

            if ($path === '/dashboard' && !in_array('admin', $accessContext['ruoli'] ?? [], true) && !in_array('amministratore', $accessContext['ruoli'] ?? [], true)) {
                $ruoliDashboard = $accessContext['ruoli'] ?? [];
                $ruoloDashboard = strtolower(trim((string) ($query['ruolo'] ?? ($accessContext['ruoloAttivo'] ?? ''))));
                if ($ruoloDashboard === 'gestore' && in_array('gestore', $ruoliDashboard, true)) {
                    $data = $this->callController('CDashboardGestore', 'visualizzaDashboardWeb', [$accessContext, $query]);
                    ViewRenderer::render('dashboard_gestore', is_array($data) ? $data : [], $this->sharedViewData());
                    return;
                }
                if (in_array('chef', $ruoliDashboard, true)) {
                    $data = $this->callController('CDashboardChef', 'visualizzaDashboardWeb', [$accessContext, $query]);
                    ViewRenderer::render('dashboard_chef', is_array($data) ? $data : [], $this->sharedViewData());
                    return;
                }
                if (in_array('gestore', $ruoliDashboard, true)) {
                    $data = $this->callController('CDashboardGestore', 'visualizzaDashboardWeb', [$accessContext, $query]);
                    ViewRenderer::render('dashboard_gestore', is_array($data) ? $data : [], $this->sharedViewData());
                    return;
                }
            }

            // 7. Dispatch finale: controller -> dati -> template.
            $data = $this->callController($controller, $action, $params);
            if ($path === '/login' && $method === 'POST' && is_array($data) && ($data['successo'] ?? false) === true) {
                $this->redirect($this->postLoginRedirectPath());
                return;
            }

            if ($path === '/registrazione' && $method === 'POST' && is_array($data) && ($data['successo'] ?? false) === true) {
                ViewRenderer::render('richiesta_esito', $data, $this->sharedViewData());
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

    // Esegue un controller dinamico e renderizza subito il template indicato.
    private function renderController(string $controller, string $action, string $template, array $params): void
    {
        $data = $this->callController($controller, $action, $params);
        if (is_array($data) && isset($data['errore'])) {
            $this->renderError(404, (string) $data['errore'], 'Controlla l identificativo nella URL.');
            return;
        }

        ViewRenderer::render($template, is_array($data) ? $data : [], $this->sharedViewData());
    }

    // Caricamento controllato: solo classi presenti in Control/ e metodi previsti dal routing.
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

    // Tutti i POST devono avere token valido; la prenotazione chef usa uno scope dedicato.
    private function isCsrfValid(string $method, string $path, array $post): bool
    {
        if ($method !== 'POST') {
            return true;
        }

        $scope = preg_match('#^/prenotazione/chef/[1-9][0-9]*$#', $path) === 1
            ? 'chef_booking'
            : 'web_form';

        return FSession::verifyCsrfToken($scope, (string) ($post['csrfToken'] ?? ''));
    }

    // Rimuove la sottocartella XAMPP dal path, cosi /GHOST-KITCHEN/login diventa /login.
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

    // Uniforma GET/POST in stringhe trim, lasciando ai controller la validazione di dominio.
    private function normalizeRequest(array $input): array
    {
        $normalized = [];
        foreach ($input as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = array_map(static fn (mixed $item): string => trim((string) $item), $value);
            } else {
                $normalized[$key] = trim((string) $value);
            }
        }

        return $normalized;
    }

    // Serve a distinguere 404 da 405 anche per route dinamiche non presenti in ALLOWED_ROUTES.
    private function routeExistsForAnyMethod(string $path): bool
    {
        if (preg_match('#^/prenotazione/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/(pagamento|recensione)/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/segnalazione/(utente|chef|ghost-kitchen|recensione|menu)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/richieste/(chef|ghost-kitchen)/[1-9][0-9]*/(accetta|rifiuta)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/disponibilita/(chef|ghost-kitchen)/[1-9][0-9]*/(blocca|libera)$#', $path) === 1) {
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

        if (preg_match('#^/utenti/utente/[1-9][0-9]*/(sospendi|banna|riattiva)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/utenti/ghost-kitchen/[1-9][0-9]*/(attiva|sospendi|non-disponibile)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/utenti/gestore/[1-9][0-9]*/(approva|rifiuta|sospendi-verifica|rimetti-in-attesa)$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/certificazioni/[1-9][0-9]*(/(approva|rifiuta|in-attesa))?$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/(chef|ghost-kitchen)/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        if (preg_match('#^/utente/[1-9][0-9]*$#', $path) === 1) {
            return true;
        }

        foreach (self::ALLOWED_ROUTES as $routes) {
            if (array_key_exists($path, $routes)) {
                return true;
            }
        }

        return false;
    }

    // Dopo il login porta ogni ruolo nella sezione piu utile per la demo.
    private function postLoginRedirectPath(): string
    {
        $ruoli = FSession::getRuoli();
        if (in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true)) {
            return '/dashboard';
        }

        if (in_array('chef', $ruoli, true)) {
            return '/dashboard?ruolo=chef';
        }

        if (in_array('gestore', $ruoli, true)) {
            return '/dashboard?ruolo=gestore';
        }

        return '/';
    }

    // Dati dell'utente corrente usati dai controller per autorizzazioni e precompilazioni.
    private function accessContext(): array
    {
        FSession::start();

        return [
            'isLogged' => FSession::isLogged(),
            'idUtente' => FSession::getIdUtente(),
            'email' => FSession::getEmail(),
            'nome' => FSession::getNome(),
            'cognome' => FSession::getCognome(),
            'telefono' => $this->currentUtente()?->getTelefono() ?? '',
            'localita' => $this->currentUtente()?->getLocalita() ?? '',
            'via' => $this->currentUtente()?->getVia() ?? '',
            'indirizzo' => $this->currentUtente()?->getIndirizzo() ?? '',
            'citta' => $this->currentUtente()?->getCitta() ?? '',
            'provincia' => $this->currentUtente()?->getProvincia() ?? '',
            'numeroCivico' => $this->currentUtente()?->getNumeroCivico() ?? '',
            'biografia' => $this->currentUtente()?->getBiografia() ?? '',
            'fotoProfilo' => $this->currentUtente()?->getFotoProfilo() ?? '',
            'ruoli' => FSession::getRuoli(),
            'ruoloAttivo' => FSession::getRuoloAttivo(),
        ];
    }

    // Dati comuni a tutte le View: base URL, path corrente, utente e badge richieste.
    private function sharedViewData(): array
    {
        FSession::start();
        $ruoli = FSession::getRuoli();
        $richiesteInAttesa = 0;
        if (FSession::isLogged() && in_array('chef', $ruoli, true) && FSession::getIdUtente() !== null) {
            $richiesteInAttesa = count(FPersistentManager::loadRichiestePrenotazioneChef((int) FSession::getIdUtente()));
        }
        if (FSession::isLogged() && in_array('gestore', $ruoli, true) && FSession::getIdUtente() !== null) {
            $richiesteInAttesa += count(FPersistentManager::loadRichiestePrenotazioneGhostKitchenByGestore((int) FSession::getIdUtente()));
        }

        return [
            'baseUrl' => $this->baseUrl(),
            'currentPath' => $this->currentPath(),
            'utenteCorrente' => FSession::isLogged() ? [
                'idUtente' => FSession::getIdUtente(),
                'nome' => FSession::getNome(),
                'cognome' => FSession::getCognome(),
                'email' => FSession::getEmail(),
                'fotoProfilo' => $this->currentUtente()?->getFotoProfilo() ?? FSession::getFotoProfilo(),
                'ruoli' => $ruoli,
                'ruolo' => FSession::getRuoloAttivo(),
                'richiesteInAttesa' => $richiesteInAttesa,
            ] : null,
        ];
    }

    // Carica sempre i dati aggiornati dal DB invece di fidarsi solo della sessione.
    private function currentUtente(): ?EUtente //restituisce EUtente o null
    {
        $idUtente = FSession::getIdUtente();
        return $idUtente !== null ? FPersistentManager::loadUtente($idUtente) : null;
    }  //      condizione ? val se vero : val se falso (operatore ternario)

    // Controllo accessi centralizzato per aree admin, chef, gestore e utente loggato.
    private function isPathAllowed(string $path, string $method, array $accesso): bool
    {
        $ruoli = $accesso['ruoli'] ?? [];
        $isLogged = ($accesso['isLogged'] ?? false) === true;
        $isAdmin = in_array('admin', $ruoli, true) || in_array('amministratore', $ruoli, true);
        $isChef = in_array('chef', $ruoli, true);
        $isGestore = in_array('gestore', $ruoli, true);

        if ($path === '/dashboard') {
            return $isAdmin || $isChef || $isGestore;
        }

        if (in_array($path, ['/moderazione', '/utenti', '/certificazioni', '/recensioni'], true)) {
            return $isAdmin;
        }

        if ($path === '/mie-certificazioni') {
            return $isChef;
        }

        if (str_starts_with($path, '/dashboard/chef/')) {
            return $isChef;
        }

        if (str_starts_with($path, '/dashboard/gestore/')) {
            return $isGestore;
        }

        if ($path === '/prenotazioni') {
            return $isLogged;
        }

        if ($path === '/mie-recensioni') {
            return $isLogged;
        }

        if ($path === '/disponibilita') {
            return $isChef || $isGestore;
        }

        if ($path === '/richieste') {
            return $isChef || $isGestore;
        }

        if ($method === 'POST' && $path === '/disponibilita/chef') {
            return $isChef;
        }

        if ($method === 'POST' && $path === '/disponibilita/ghost-kitchen') {
            return $isGestore;
        }

        if (preg_match('#^/disponibilita/(chef|ghost-kitchen)/[1-9][0-9]*/(blocca|libera)$#', $path, $matches) === 1) {
            return $matches[1] === 'chef' ? $isChef : $isGestore;
        }

        if (preg_match('#^/moderazione/#', $path) === 1 || preg_match('#^/utenti/#', $path) === 1 || preg_match('#^/certificazioni/[1-9][0-9]*(/(approva|rifiuta|in-attesa))?$#', $path) === 1) {
            return $isAdmin;
        }

        if (preg_match('#^/richieste/(chef|ghost-kitchen)/[1-9][0-9]*/(accetta|rifiuta)$#', $path, $matches) === 1) {
            return $matches[1] === 'chef' ? $isChef : $isGestore;
        }

        return true;
    }

    // Path normalizzato della richiesta corrente, usato per navbar e stati attivi.
    private function currentPath(): string
    {
        $path = $this->normalizePath((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        return $path === '' ? '/' : $path;
    }

    // Per utenti multi-ruolo mantiene in sessione il ruolo professionale selezionato.
    private function synchronizeActiveRole(array $query): void
    {
        FSession::start();
        $ruolo = strtolower(trim((string) ($query['ruolo'] ?? '')));
        if (in_array($ruolo, ['chef', 'gestore'], true)) {
            FSession::setRuoloAttivo($ruolo);
        }
    }

    // /disponibilita e /richieste sono alias verso la dashboard con tab corretta.
    private function professionalDashboardRedirect(string $path, array $accesso): ?string
    {
        $ruoli = $accesso['ruoli'] ?? [];
        $ruolo = (string) ($accesso['ruoloAttivo'] ?? '');
        if (!in_array($ruolo, ['chef', 'gestore'], true)) {
            $ruolo = in_array('chef', $ruoli, true) ? 'chef' : (in_array('gestore', $ruoli, true) ? 'gestore' : '');
        }
        if ($ruolo === '' || !in_array($ruolo, $ruoli, true)) {
            return null;
        }

        $tab = match ($path) {
            '/disponibilita' => 'disponibilita',
            '/richieste' => 'richieste',
            default => 'panoramica',
        };
        return '/dashboard?ruolo=' . $ruolo . '&tab=' . $tab;
    }

    // Base URL compatibile con progetto in sottocartella XAMPP o document root.
    private function baseUrl(): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return $scriptDir === '/' || $scriptDir === '.' ? '' : rtrim($scriptDir, '/');
    }

    // Redirect interni sempre relativi alla base URL calcolata.
    private function redirect(string $path): void
    {
        header('Location: ' . $this->baseUrl() . $path);
        exit;
    }

    // Errori utente controllati: codice HTTP corretto e template dedicato.
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
