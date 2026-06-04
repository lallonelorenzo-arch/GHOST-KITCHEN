# Piano integrazione View

## UI esistente

- Export Figma/frontend: `UI_GhostKitchen/`.
- Stack export: Vite + React + TypeScript + Tailwind/shadcn.
- Entry point originale: `UI_GhostKitchen/index.html` -> `/src/main.tsx`.
- Componenti principali:
  - `src/app/components/Navbar.tsx`
  - `src/app/components/pages/Home.tsx`
  - `src/app/components/pages/SearchChefs.tsx`
  - `src/app/components/pages/GhostKitchens.tsx`
  - `src/app/components/pages/ChefDetail.tsx`
  - `src/app/components/pages/GhostKitchenDetail.tsx`
  - pagine secondarie: booking, dashboard, profile, not found.
- CSS sorgente:
  - `src/styles/index.css`
  - `src/styles/theme.css`
  - `src/styles/tailwind.css`
  - `src/styles/fonts.css`
- Asset: l'export usa soprattutto URL remoti Unsplash e componenti React; non risultano immagini locali applicative fuori da `node_modules`.

## Schermate presenti

- Home con hero, chef in evidenza, categorie cucina, sezione ghost kitchen, come funziona.
- Lista/ricerca chef con filtri.
- Lista/ricerca ghost kitchen con filtri.
- Dettaglio chef con bio, menu, recensioni/mock, box prenotazione.
- Dettaglio ghost kitchen con descrizione, attrezzature, disponibilita/mock, box prenotazione.
- Navbar con stato utente mock.
- Pagine dashboard/profile/booking solo da collegare in una fase successiva.

## Parti hardcoded nel frontend

- Liste `mockChefs` e `mockKitchens`.
- Utente corrente nella navbar.
- Link React `/chefs`, `/ghost-kitchens`, `/booking/...`.
- Immagini remote Unsplash.
- Recensioni, lingue, regole, fasce prezzo e disponibilita in dettaglio.
- Testi descrittivi generici.

## Form, bottoni e link da collegare

- Ricerca chef -> `GET /ricerca/chef`.
- Ricerca ghost kitchen -> `GET /ricerca/ghost-kitchen`.
- Dettaglio chef -> `GET /chef/{id}`.
- Dettaglio ghost kitchen -> `GET /ghost-kitchen/{id}`.
- Login -> `GET /login` e `POST /login`.
- Logout -> `GET /logout`.
- Prenotazione/contatto: placeholder controllato, da completare con UC prenotazione.

## Dati dal database

- Chef: `FPersistentManager::cercaChef()`, `loadChef()`, menu, piatti, certificazioni, media.
- Ghost kitchen: `FPersistentManager::cercaGhostKitchen()`, `loadGhostKitchen()`, attrezzature, disponibilita, media.
- Login: `FPersistentManager::login()` e `FSession`.
- Navbar: dati utente preparati dal FrontController tramite `FSession`, non letti dalle View.

## View PHP/template da creare

- `View/templates/layout.php`
- `View/templates/home.php`
- `View/templates/ricerca.php`
- `View/templates/lista_chef.php`
- `View/templates/lista_ghost_kitchen.php`
- `View/templates/dettaglio_chef.php`
- `View/templates/dettaglio_ghost_kitchen.php`
- `View/templates/login.php`
- `View/templates/error.php`
- partial riusabili per card chef/ghost kitchen.

## Asset statici

- Creare `public/assets/css/app.css` con palette e layout derivati dall'export Figma.
- Creare `public/assets/js/app.js` per piccole interazioni.
- Tenere `UI_GhostKitchen/` intatto come sorgente/reference Figma.
- `.htaccess` deve lasciare passare file reali sotto `public/assets`.

## Routing minimo

- `GET /`
- `GET /ricerca`
- `GET /ricerca/chef`
- `GET /ricerca/ghost-kitchen`
- `GET /chef/{id}`
- `GET /ghost-kitchen/{id}`
- `GET /login`
- `POST /login`
- `GET /logout`

## Vincoli architetturali

- Nessun SQL nelle View.
- Nessun accesso diretto a `$_SESSION` nelle View.
- Nessun `$_GET`/`$_POST` nelle View.
- Control e FrontController normalizzano input e passano dati pronti.
- Entity, Foundation e DB non vanno modificati salvo bug reale.
