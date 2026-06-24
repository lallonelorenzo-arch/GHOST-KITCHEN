# Verifica View + FrontController

## Ambiente

- PHP version: `PHP 8.2.12 (cli)`.
- Server HTTP rilevato: Apache/XAMPP raggiungibile su `http://localhost/GHOST-KITCHEN`.
- Database usato dal codice: MySQL `GhostKitchen`, configurato in `Foundation/FConnectionDB.php`.
- Cartella progetto: `C:\xampp\htdocs\GHOST-KITCHEN`.
- Sottocartella web testata: `/GHOST-KITCHEN`.

## Struttura verificata

- `index.php` presente nella root progetto; carica `Control/CFrontController.php`, istanzia `CFrontController` e chiama `handle()`.
- `.htaccess` presente nella root; non contiene `RewriteBase` hardcoded.
- `Control/CFrontController.php` presente.
- Control coinvolti dal routing:
  - `CHome`
  - `CRicerca`
  - `CDettaglioChef`
  - `CDettaglioGhostKitchen`
  - `CAutenticazione`
- View/template coinvolti:
  - `View/templates/layout.php`
  - `home.php`
  - `lista_chef.php`
  - `lista_ghost_kitchen.php`
  - `dettaglio_chef.php`
  - `dettaglio_ghost_kitchen.php`
  - `login.php`
  - `error.php`
  - `partials/chef_card.php`
  - `partials/ghost_kitchen_card.php`
- Asset presenti:
  - `public/assets/css/app.css`
  - `public/assets/js/app.js`
  - cartelle `public/assets/img` e `public/assets/fonts`.
- `UI_GhostKitchen` presente come sorgente/reference Figma; la UI PHP non viene servita passando direttamente dalla SPA React.
- Report precedenti presenti:
  - `docs/VIEW_INTEGRATION_PLAN.md`
  - `docs/VIEW_INTEGRATION_REPORT.md`

## Sintassi PHP

Comando eseguito:

```powershell
Get-ChildItem -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '\\.git|node_modules' } | ForEach-Object { php -l $_.FullName }
```

Esito:

- Tutti i file PHP di `index.php`, `Control`, `Entity`, `Foundation`, `test` e `View` risultano senza errori sintattici.
- Nessun file con errore `php -l`.

Esito finale sintassi: PASS.

## .htaccess

Contenuto verificato:

```apache
Options -Indexes
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

RewriteRule ^ index.php [L]
```

Esito:

- Richieste dinamiche inviate a `index.php`: PASS.
- File/cartelle reali non riscritti: PASS.
- CSS servito direttamente da `public/assets/css/app.css`: PASS.
- Nessun `RewriteBase /GHOST-KITCHEN/` hardcoded: PASS.
- Compatibile con sottocartella XAMPP: PASS, verificato su `/GHOST-KITCHEN/ricerca`.

## index.php

Verifiche:

- Punto unico di ingresso: PASS.
- Carica `CFrontController`: PASS.
- Istanzia `CFrontController`: PASS.
- Non contiene SQL: PASS.
- Non contiene logica business: PASS.
- Non stampa debug sensibile: PASS.
- Error handling demandato a `CFrontController`: PASS.

Nota minore: contiene un commento descrittivo inline, non bloccante.

## CFrontController

Verifiche:

- Legge `REQUEST_METHOD`: PASS.
- Legge `REQUEST_URI`: PASS.
- Normalizza il path considerando sottocartella XAMPP via `SCRIPT_NAME`: PASS.
- Normalizza `$_GET` e `$_POST` nel FrontController: PASS.
- Usa whitelist `ALLOWED_ROUTES`: PASS.
- Non permette chiamate arbitrarie controller/metodo: PASS.
- ID dinamici validati con regex numerica positiva: PASS.
- 404 route inesistenti: PASS.
- 405 metodo non consentito previsto dal codice: PASS.
- Nessun SQL: PASS.
- Nessuna logica dominio significativa: PASS.
- Catch generale non mostra messaggi tecnici all'utente: PASS.
- Dettaglio tecnico mandato a `error_log()`: PASS.
- Metodo legacy `dispatch()`: non presente. PASS.

## Route verificate

| URL | Metodo HTTP | Controller | Metodo | Template | Esito codice | Esito runtime | Note |
|---|---|---|---|---|---|---|---|
| `/` | GET | `CHome` | `home` | `home.php` | PASS | PASS 200 | Mostra home con dati DB e fallback immagini. |
| `/ricerca` | GET | `CFrontController` | redirect | redirect | PASS | PASS 200 dopo redirect | Redirect a `/ricerca/chef`; pagina generica rimossa per coerenza UI Figma. |
| `/ricerca/chef` | GET | `CRicerca` | `cercaOfferte` | `lista_chef.php` | PASS | PASS 200 | Bug runtime corretto: conteggio salvato prima del loop e variabile elemento separata. |
| `/ricerca/ghost-kitchen` | GET | `CRicerca` | `cercaOfferte` | `lista_ghost_kitchen.php` | PASS | PASS 200 | Bug runtime corretto: conteggio salvato prima del loop e variabile elemento separata. |
| `/chef/{id}` | GET | `CDettaglioChef` | `visualizzaDettaglioChef` | `dettaglio_chef.php` | PASS | PASS 200 con `/chef/5` | Usa dati reali, menu, piatti, certificazioni, media. |
| `/chef/{id inesistente}` | GET | `CDettaglioChef` | `visualizzaDettaglioChef` | `error.php` | PASS | PASS 404 con `/chef/999999` | Errore controllato. |
| `/ghost-kitchen/{id}` | GET | `CDettaglioGhostKitchen` | `visualizzaDettaglioGhostKitchen` | `dettaglio_ghost_kitchen.php` | PASS | PASS 200 con `/ghost-kitchen/1` | Usa dati reali, attrezzature, disponibilita, media. |
| `/ghost-kitchen/{id inesistente}` | GET | `CDettaglioGhostKitchen` | `visualizzaDettaglioGhostKitchen` | `error.php` | PASS | PASS 404 con `/ghost-kitchen/999999` | Errore controllato. |
| `/login` | GET | `CAutenticazione` | `mostraLogin` | `login.php` | PASS | PASS 200 | Form POST presente. |
| `/login` | POST | `CAutenticazione` | `login` | `login.php` / redirect | PASS | PASS | Credenziali errate mostrano errore; credenziali valide caricano home. |
| `/logout` | GET | `CAutenticazione` | `logout` | redirect | PASS | PASS 200 dopo redirect | Pulisce sessione e torna alla home. |
| `/route-inesistente` | GET | nessuno | nessuno | `error.php` | PASS | PASS 404 | Route inesistente gestita. |

## Comandi runtime eseguiti

```powershell
Invoke-WebRequest http://localhost/GHOST-KITCHEN/ricerca
Invoke-WebRequest http://localhost/GHOST-KITCHEN/public/assets/css/app.css
Invoke-WebRequest http://localhost/GHOST-KITCHEN/login
Invoke-WebRequest http://localhost/GHOST-KITCHEN/
Invoke-WebRequest http://localhost/GHOST-KITCHEN/ricerca/chef
Invoke-WebRequest http://localhost/GHOST-KITCHEN/ricerca/ghost-kitchen
Invoke-WebRequest http://localhost/GHOST-KITCHEN/chef/5
Invoke-WebRequest http://localhost/GHOST-KITCHEN/chef/999999
Invoke-WebRequest http://localhost/GHOST-KITCHEN/ghost-kitchen/1
Invoke-WebRequest http://localhost/GHOST-KITCHEN/ghost-kitchen/999999
Invoke-WebRequest http://localhost/GHOST-KITCHEN/route-inesistente
Invoke-WebRequest -Method Post http://localhost/GHOST-KITCHEN/login
```

Risultati rilevanti:

- `/ricerca`: 200 dopo redirect a `/ricerca/chef`.
- CSS `public/assets/css/app.css`: 200.
- `/login`: 200.
- `/`: 200.
- `/ricerca/chef`: 200 dopo correzione template.
- `/ricerca/ghost-kitchen`: 200 dopo correzione template.
- `/chef/5`: 200.
- `/chef/999999`: 404.
- `/ghost-kitchen/1`: 200.
- `/ghost-kitchen/999999`: 404.
- `/route-inesistente`: 404.
- `POST /login` con credenziali errate: 200 con errore controllato.
- `POST /login` con `marco.rinaldi@gk.it` / `Password123!`: 200 dopo redirect/home.

## Verifica architetturale ECFV

Corretto:

- Il flusso passa da `Browser -> index.php -> CFrontController -> Control -> Foundation -> DB -> Entity -> View -> HTML`.
- Le View non fanno query SQL.
- Il FrontController non fa query SQL.
- I Control coinvolti chiamano `FPersistentManager`/Foundation.
- Le View ricevono dati preparati dai Control.
- La sessione viene letta nel FrontController tramite `FSession`, non direttamente nelle View.
- `UI_GhostKitchen` resta reference Figma e non sorgente applicativa principale.
- La navbar espone solo "Trova Chef" e "Ghost Kitchen", coerente con l'export Figma.

Rischi/violazioni residue:

- Alcune immagini sono fallback hardcoded Unsplash; non sono fonte dati principale, ma sono ancora asset placeholder.

## Dati reali dal DB

Verificato:

- `CRicerca::cercaOfferte()` usa `FPersistentManager::cercaChef()` per `/ricerca/chef`.
- `CRicerca::cercaOfferte()` usa `FPersistentManager::cercaGhostKitchen()` per `/ricerca/ghost-kitchen`.
- `CDettaglioChef::visualizzaDettaglioChef()` usa `loadChef()`, media, menu, piatti e certificazioni.
- `CDettaglioGhostKitchen::visualizzaDettaglioGhostKitchen()` usa `loadGhostKitchen()`, attrezzature, disponibilita e media.
- Test CLI `php test\test_FConnection.php`: `Connessione OK`.
- Test CLI `php test\test_FChef.php`: chef ID 5 caricato e ricerca giapponese restituisce chef reale.

Fallback/hardcoded:

- Immagini fallback in `home.php`, `dettaglio_chef.php`, `dettaglio_ghost_kitchen.php`, `chef_card.php`, `ghost_kitchen_card.php`.
- Testi statici descrittivi e placeholder prenotazione.

## Sessione/Login

- `GET /login` mostra form: PASS.
- Form login usa `method="post"`: PASS.
- `POST /login` chiama `CAutenticazione::login()`: PASS.
- Login usa `FPersistentManager::login()` e `FSession`: PASS.
- View non leggono `$_SESSION`: PASS.
- Credenziali errate: PASS, errore controllato.
- Credenziali valide testate: `marco.rinaldi@gk.it` / `Password123!`, esito home dopo login: PASS.
- `GET /logout` chiama `FSession::logout()` e redirect home: PASS.
- Nota CLI: simulazioni CLI del FrontController mostrano warning `session_start()` su `C:\xampp\tmp` per permessi; via HTTP XAMPP le pagine testate rispondono correttamente. Non considero questo un blocco HTTP, ma e un rischio per test CLI diretti.

## Asset/UI Figma

- `public/assets/css/app.css` esiste: PASS.
- `public/assets/js/app.js` esiste: PASS.
- Link asset nel layout generato:
  - `/GHOST-KITCHEN/public/assets/css/app.css`
  - `/GHOST-KITCHEN/public/assets/js/app.js`
- CSS raggiungibile via HTTP: PASS 200.
- JS presente, non testato separatamente via HTTP ma path generato coerente.
- Cartelle `img` e `fonts` presenti ma vuote: OK per ora.
- `UI_GhostKitchen` non viene usata come entrypoint utente: PASS.

## Sicurezza

| Controllo | Esito | Note |
|---|---|---|
| Niente SQL nelle View | PASS | `rg` non trova `SELECT/INSERT/UPDATE/DELETE` in View. |
| Niente SQL nel FrontController | PASS | Nessun SQL trovato. |
| Nessuna chiamata arbitraria controller/metodo | PASS | Routing whitelist; `dispatch()` assente. |
| ID URL solo numerici positivi | PASS | Regex `/[1-9][0-9]*/`. |
| Output dinamico escapato | PASS prevalente | Uso `ViewHelpers::e()`. |
| Nessuno stack trace visibile all'utente | PASS | 500 mostra messaggio generico. |
| Dettagli tecnici in log | PASS | `error_log()` nel catch generale. |
| Nessun path traversal | PASS | Route chiuse e template scelti internamente. |
| Nessun debug sensibile | PASS | Nessun dump nelle View/FrontController. |
| Form login POST | PASS | `login.php`. |
| Route inesistente 404 | PASS | Test HTTP 404. |
| Metodo errato 405 | PASS codice | Non eseguito via HTTP, ma previsto dal routing. |

## Problemi trovati

### 1. Liste ricerca in errore 500

- Gravita: bloccante, corretto.
- File coinvolti:
  - `View/templates/lista_chef.php`
  - `View/templates/lista_ghost_kitchen.php`
- Descrizione:
  - In `lista_chef.php`, il template usa `$chef` sia come array lista sia come variabile del singolo elemento:
    - `foreach ($chef as $chef)`
    - poi `count($chef)` alla riga successiva.
  - Dopo il `foreach`, `$chef` non e piu array ma oggetto `EChef`; `count($chef)` genera `TypeError`.
  - Stesso problema in `lista_ghost_kitchen.php` con `$ghostKitchen`.
- Evidenza iniziale:
  - `GET /ricerca/chef`: HTTP 500.
  - `GET /ricerca/ghost-kitchen`: HTTP 500.
  - Log CLI del FrontController: `TypeError: count(): Argument #1 ($value) must be of type Countable|array, EChef given`.
- Correzione applicata:
  - In `lista_chef.php`, aggiunto `$numeroChef = count($chef);` e usato `foreach ($chef as $chefItem)`.
  - In `lista_ghost_kitchen.php`, aggiunto `$numeroGhostKitchen = count($ghostKitchen);` e usato `foreach ($ghostKitchen as $ghostKitchenItem)`.
- Verifica dopo correzione:
  - `GET /ricerca/chef`: HTTP 200.
  - `GET /ricerca/ghost-kitchen`: HTTP 200.

### 2. Warning sessione nelle simulazioni CLI

- Gravita: minore.
- File coinvolto:
  - `Foundation/FSession.php`
- Descrizione:
  - Simulando il FrontController da CLI, PHP prova ad aprire sessioni in `C:\xampp\tmp` e produce warning di permessi.
  - Le prove HTTP su Apache/XAMPP non sono state bloccate da questo problema.
- Correzione minima proposta:
  - Nessuna modifica richiesta ora a Foundation.
  - Per test CLI, configurare `session.save_path` verso una cartella scrivibile o lanciare sotto ambiente con permessi corretti.

### 3. Fallback immagini ancora hardcoded

- Gravita: minore.
- File coinvolti:
  - `View/templates/home.php`
  - `View/templates/dettaglio_chef.php`
  - `View/templates/dettaglio_ghost_kitchen.php`
  - `View/templates/partials/chef_card.php`
  - `View/templates/partials/ghost_kitchen_card.php`
- Descrizione:
  - Le immagini principali usano media reali se presenti in dettaglio, ma le card e alcune sezioni usano fallback Unsplash.
- Correzione minima proposta:
  - Le liste/card usano fallback grafici quando non e' disponibile un media dedicato.

## Esito finale

Valutazione: **APPROVATO PER PROSEGUIRE**

Motivo: il flusso ECFV e l'architettura sono impostati correttamente e, dopo la correzione minima ai template lista, tutte le route minime verificate rispondono con esito atteso:

- `GET /` -> 200
- `GET /ricerca` -> 200 dopo redirect a `/ricerca/chef`
- `GET /ricerca/chef` -> 200
- `GET /ricerca/ghost-kitchen` -> 200
- `GET /chef/5` -> 200
- `GET /chef/999999` -> 404
- `GET /ghost-kitchen/1` -> 200
- `GET /ghost-kitchen/999999` -> 404
- `GET /login` -> 200
- `GET /route-inesistente` -> 404
