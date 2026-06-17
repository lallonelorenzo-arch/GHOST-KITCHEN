# Report integrazione View

## File UI/Figma riutilizzati

- `UI_GhostKitchen/src/app/components/pages/Home.tsx`
- `UI_GhostKitchen/src/app/components/pages/SearchChefs.tsx`
- `UI_GhostKitchen/src/app/components/pages/GhostKitchens.tsx`
- `UI_GhostKitchen/src/app/components/pages/ChefDetail.tsx`
- `UI_GhostKitchen/src/app/components/pages/GhostKitchenDetail.tsx`
- `UI_GhostKitchen/src/app/components/Navbar.tsx`
- `UI_GhostKitchen/src/styles/theme.css`

L'export originale non e stato eliminato. La prima integrazione PHP riusa struttura visuale, palette, hero, card, form e immagini remote, sostituendo i mock principali con dati reali.

## File creati

- `index.php`
- `.htaccess`
- `Control/CHome.php`
- `Control/CAutenticazione.php`
- `View/ViewHelpers.php`
- `View/ViewRenderer.php`
- `View/templates/layout.php`
- `View/templates/home.php`
- `View/templates/lista_chef.php`
- `View/templates/lista_ghost_kitchen.php`
- `View/templates/dettaglio_chef.php`
- `View/templates/dettaglio_ghost_kitchen.php`
- `View/templates/login.php`
- `View/templates/error.php`
- `View/templates/placeholder.php`
- `View/templates/partials/chef_card.php`
- `View/templates/partials/ghost_kitchen_card.php`
- `public/assets/css/app.css`
- `public/assets/js/app.js`
- `public/assets/img/`
- `public/assets/fonts/`

## File modificati

- `Control/CFrontController.php`
- `Control/CHome.php`
- `.htaccess`

## URL implementate

- `GET /`
- `GET /ricerca` -> redirect a `/ricerca/chef`
- `GET /ricerca/chef`
- `GET /ricerca/ghost-kitchen`
- `GET /chef/{id}`
- `GET /ghost-kitchen/{id}`
- `GET /login`
- `POST /login`
- `GET /logout`
- `GET /prenotazione/placeholder`

Se il progetto viene aperto da XAMPP come sottocartella, gli stessi URL sono sotto `/GHOST-KITCHEN`, ad esempio `/GHOST-KITCHEN/ricerca/chef`.

## Correzioni post-verifica

- Rimosso `RewriteBase /GHOST-KITCHEN/` da `.htaccess`: la riscrittura ora resta compatibile anche se la cartella XAMPP cambia nome.
- Mantenuta la regola che esclude file e cartelle reali dalla riscrittura, quindi CSS, JS e immagini sotto `public/assets` continuano a essere serviti direttamente.
- Rimosso `CFrontController::dispatch()`: non risultava usato dai test o dal codice applicativo e avrebbe lasciato un ingresso pubblico alternativo al routing whitelist.
- Il catch generale del FrontController ora scrive il dettaglio tecnico in `error_log()` e mostra all'utente un messaggio generico.
- La route `GET /prenotazione/placeholder` ora chiama `CHome::placeholder()` invece di `CHome::home()`.
- Verificata la coerenza delle route dichiarate: ogni route ha controller, metodo e template/redirect esistente.
- Rimossa la pagina generica `View/templates/ricerca.php`: non era presente nella UI Figma e duplicava "Trova Chef" e "Ghost Kitchen".
- Rimosso il link "Ricerca" dalla navbar; `GET /ricerca` resta solo come redirect di compatibilita verso `/ricerca/chef`.
- Migliorate le card e i dettagli con rating a stelle, prezzo `&euro;` e metratura `m2` formattata in modo piu vicino all'export UI.

## Control collegati

- `CHome::home()`
- `CHome::placeholder()`
- `CRicerca::cercaOfferte()`
- `CDettaglioChef::visualizzaDettaglioChef()`
- `CDettaglioGhostKitchen::visualizzaDettaglioGhostKitchen()`
- `CAutenticazione::mostraLogin()`
- `CAutenticazione::login()`
- `CAutenticazione::logout()`

## Dati reali mostrati dal DB

- Liste chef da `FPersistentManager::cercaChef()`.
- Liste ghost kitchen da `FPersistentManager::cercaGhostKitchen()`.
- Dettaglio chef da `loadChef()`, menu, piatti, certificazioni e media principale.
- Dettaglio ghost kitchen da `loadGhostKitchen()`, attrezzature, disponibilita e media.
- Login via `FPersistentManager::login()` e sessione Foundation.

## Parti ancora hardcoded

- Immagini fallback Unsplash quando non esiste media nel DB.
- Link prenotazione e contatto puntano a placeholder controllato o login.
- Recensioni non mostrate nelle nuove View, da collegare in fase successiva.
- Dashboard/profile/booking React non ancora trasformate in View PHP.

## Problemi trovati

- L'export Figma era una SPA React con mock hardcoded; servirla direttamente avrebbe bypassato ECFV.
- Gli URL originali `/chefs` e `/ghost-kitchens` sono stati mappati agli URL richiesti `/ricerca/chef` e `/ricerca/ghost-kitchen`.
- Non risultavano asset immagine locali applicativi da spostare, quindi sono state create cartelle pubbliche ordinate e riusati fallback remoti.

## Sicurezza minima

- Output variabile escapato con `htmlspecialchars` tramite `ViewHelpers::e()`.
- ID URL accettati solo numerici positivi.
- Routing chiuso su whitelist, senza chiamate arbitrarie a classi/metodi.
- View senza SQL, senza `$_GET`, senza `$_POST`, senza `$_SESSION`.
- 404 per route/id non validi.
- 405 per metodo HTTP non consentito.

## Checklist test manuali

- [ ] `GET /` carica home con layout Figma.
- [ ] CSS e immagini si caricano correttamente.
- [ ] `GET /ricerca` reindirizza a `/ricerca/chef`.
- [ ] `GET /ricerca/chef` mostra lista chef reale o messaggio coerente.
- [ ] `GET /ricerca/ghost-kitchen` mostra lista ghost kitchen reale o messaggio coerente.
- [ ] `GET /chef/{id valido}` mostra dettaglio reale.
- [ ] `GET /chef/{id inesistente}` mostra 404 o messaggio coerente.
- [ ] `GET /ghost-kitchen/{id valido}` mostra dettaglio reale.
- [ ] `GET /ghost-kitchen/{id inesistente}` mostra 404 o messaggio coerente.
- [ ] `GET /login` mostra form con layout coerente.
- [ ] `POST /login` gestisce credenziali corrette/errate.
- [ ] `GET /logout` pulisce sessione.

## Prossimi passi

- Collegare flussi prenotazione reali alle View.
- Aggiungere recensioni reali nelle pagine dettaglio.
- Sostituire fallback Unsplash con media locali caricati/popolati nel DB.
- Trasformare dashboard/profile/booking seguendo lo stesso pattern.
