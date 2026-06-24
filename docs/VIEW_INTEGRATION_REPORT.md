# Report integrazione View

## Stato finale

La UI finale e' integrata in template PHP e non dipende da una SPA React separata. I template usano dati reali preparati dai Control e recuperati tramite Foundation/FPersistentManager.

## File principali

- `index.php`
- `.htaccess`
- `Control/CFrontController.php`
- `View/ViewRenderer.php`
- `View/ViewHelpers.php`
- `View/templates/layout.php`
- `View/templates/home.php`
- `View/templates/lista_chef.php`
- `View/templates/lista_ghost_kitchen.php`
- `View/templates/dettaglio_chef.php`
- `View/templates/dettaglio_ghost_kitchen.php`
- `View/templates/dashboard.php`
- `View/templates/dashboard_chef.php`
- `View/templates/dashboard_gestore.php`
- `public/assets/css/app.css`
- `public/assets/js/app.js`

## Route principali collegate

- `GET /`
- `GET /ricerca/chef`
- `GET /ricerca/ghost-kitchen`
- `GET /chef/{id}`
- `GET /ghost-kitchen/{id}`
- `GET /login`
- `POST /login`
- `GET /logout`
- `GET /profilo`
- `GET /prenotazioni`
- `GET /dashboard`
- `GET /moderazione`
- `GET /utenti`
- `GET /certificazioni`
- `GET /mie-certificazioni`

## Dati reali mostrati dal DB

- Liste chef da `FPersistentManager::cercaChef()`.
- Liste ghost kitchen da `FPersistentManager::cercaGhostKitchen()`.
- Dettaglio chef con menu, piatti, certificazioni, media, recensioni e disponibilita.
- Dettaglio ghost kitchen con attrezzature, disponibilita, media e recensioni.
- Dashboard chef, gestore e admin con dati applicativi.
- Profilo utente, prenotazioni, pagamenti, recensioni, segnalazioni e certificazioni.

## Sicurezza e separazione responsabilita

- Output variabile escapato con `htmlspecialchars` tramite `ViewHelpers::e()`.
- ID URL accettati solo numerici positivi.
- Routing chiuso su whitelist, senza chiamate arbitrarie a classi/metodi.
- View senza SQL, senza `$_GET`, senza `$_POST`, senza `$_SESSION`.
- 404 per route/id non validi.
- 405 per metodo HTTP non consentito.
- Form POST protetti da token CSRF.
