# UC4-UC7 Implementation Report

## File creati
- `View/templates/prenotazione_chef.php`
- `View/templates/prenotazione_ghost_kitchen.php`
- `View/templates/disponibilita.php`
- `View/templates/richieste.php`
- `View/templates/richiesta_esito.php`
- `docs/UC4_UC7_IMPLEMENTATION_REPORT.md`

## File modificati
- `Control/CFrontController.php`
- `Control/CPrenotazioneChef.php`
- `Control/CPrenotazioneGhostKitchen.php`
- `Control/CGestioneDisponibilita.php`
- `Control/CGestioneRichieste.php`
- `public/assets/css/app.css` solo append finale in sezione `UC4-UC7`

## Route aggiunte
- `GET /prenotazione/chef/{idChef}`
- `POST /prenotazione/chef/{idChef}`
- `GET /prenotazione/ghost-kitchen/{idGhostKitchen}`
- `POST /prenotazione/ghost-kitchen/{idGhostKitchen}`
- `GET /disponibilita`
- `POST /disponibilita/chef`
- `POST /disponibilita/ghost-kitchen`
- `GET /richieste`
- `POST /richieste/chef/{idPrenotazione}/accetta`
- `POST /richieste/chef/{idPrenotazione}/rifiuta`
- `POST /richieste/ghost-kitchen/{idPrenotazione}/accetta`
- `POST /richieste/ghost-kitchen/{idPrenotazione}/rifiuta`

## Control usati
- `CPrenotazioneChef`
- `CPrenotazioneGhostKitchen`
- `CGestioneDisponibilita`
- `CGestioneRichieste`

## Foundation chiamate
Attraverso `FPersistentManager` vengono usati caricamenti e persistenze per chef, ghost kitchen, menu, disponibilita, prenotazioni e richieste.

## Cosa funziona
- Le pagine GET UC4-UC7 renderizzano senza toccare le View UC1-UC3.
- Le pagine di prenotazione mostrano dati reali da DB.
- Le POST creano prenotazioni o disponibilita quando l'utente ha ruolo coerente.
- La gestione richieste aggiorna lo stato delle prenotazioni in attesa.
- Le View non leggono `$_GET`, `$_POST` o `$_SESSION`.

## Placeholder controllati
- Se l'utente non e loggato, le pagine mostrano un messaggio controllato e link al login.
- Per la disponibilita ghost kitchen serve indicare `idGhostKitchen`; il collegamento automatico gestore -> ghost kitchen resta da completare.
- I bottoni dalle pagine UC1-UC3 verranno collegati successivamente alle nuove route.

## Test eseguiti
- `php -l` sui file PHP creati/modificati: OK.
- GET web sulle route principali via XAMPP/curl:
  - `/prenotazione/chef/5` -> HTTP 200
  - `/prenotazione/ghost-kitchen/1` -> HTTP 200
  - `/disponibilita` -> HTTP 200
  - `/richieste` -> HTTP 200
- Controllato che le pagine renderizzino i rispettivi titoli e non una pagina errore.

## Rischi/conflitti evitati
- Non sono state modificate le View UC1-UC3.
- Non sono state modificate Entity, Foundation o database.
- Il CSS esistente non e stato riscritto; e stata aggiunta solo una sezione finale `UC4-UC7`.
